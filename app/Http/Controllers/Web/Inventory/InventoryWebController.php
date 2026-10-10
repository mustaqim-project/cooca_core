<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Inventory;

use App\Domain\Accounting\AutoJournalService;
use App\Domain\Inventory\StockService;
use App\Domain\System\AuditLogService;
use App\Http\Controllers\Controller;
use App\Models\ApprovalRule;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Material;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Support\Context;
use App\Support\DocumentNumberGenerator;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class InventoryWebController extends Controller
{
    public function __construct(
        private readonly StockService $stockService = new StockService,
        private readonly AutoJournalService $journalService = new AutoJournalService
    ) {}

    /**
     * Real-time stock list across outlets & warehouses with low-stock alerts.
     */
    public function stocks(Request $request): View
    {
        $business = Context::requireBusiness();

        $locations = Location::where('business_id', $business->id)->where('is_active', true)->get();
        $products = Product::goods()->where('business_id', $business->id)->where('is_active', true)->get();

        $query = InventoryStock::where('business_id', $business->id)
            ->whereHas('product', fn ($q) => $q->goods())
            ->with(['product.outputUnit', 'location']);

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->get('location_id'));
        }

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $stocks = $query->paginate(20)->withQueryString();

        // Calculate low stock warnings
        $lowStockCount = InventoryStock::where('business_id', $business->id)
            ->whereHas('product', function ($q) {
                $q->whereRaw('inventory_stocks.quantity <= products.min_stock');
            })
            ->count();

        $totalValuation = (float) (InventoryStock::where('inventory_stocks.business_id', $business->id)
            ->leftJoin('products', 'products.id', '=', 'inventory_stocks.product_id')
            ->selectRaw('SUM(inventory_stocks.quantity * COALESCE(NULLIF(inventory_stocks.last_cost, 0), products.base_cost, 0)) as total_val')
            ->value('total_val') ?? 0.0);

        return view('app.inventory.stocks', compact('business', 'stocks', 'locations', 'products', 'lowStockCount', 'totalValuation'));
    }

    /**
     * Quick stock adjustment / initial balance setup with Maker-Checker Guard (§FR-02, §FR-04).
     */
    public function quickAdjust(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $validated = $request->validate([
            'location_id' => ['required', 'uuid', Rule::exists('locations', 'id')->where('business_id', $business->id)],
            'product_id' => ['required', 'uuid', Rule::exists('products', 'id')->where('business_id', $business->id)],
            'new_quantity' => ['required', 'numeric', 'min:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'reason_code' => ['nullable', 'string', Rule::in(['damaged', 'expired', 'opname_variance', 'theft_loss', 'initial_balance', 'other'])],
            'notes' => ['nullable', 'string', 'max:500'],
            'supervisor_pin' => ['nullable', 'string', 'max:10'],
        ]);

        if (($validated['reason_code'] ?? null) === 'other') {
            $notesTrimmed = trim((string) ($validated['notes'] ?? ''));
            if (strlen($notesTrimmed) < 10) {
                throw ValidationException::withMessages([
                    'notes' => __('inventory.notes_other_min_length'),
                ]);
            }
        }

        $stock = $this->stockService->getOrCreateStock(
            $business->id,
            $validated['location_id'],
            $validated['product_id']
        );

        $diff = (float) $validated['new_quantity'] - (float) $stock->quantity;
        $unitCost = (float) ($validated['unit_cost'] ?? $stock->last_cost ?? 0);
        $diffValue = abs($diff * $unitCost);
        $diffQty = abs($diff);

        // Supervisor PIN Verification on Significant Shrinkage / Loss (§FR-02)
        if ($diff < 0 && ($diffValue > 100000 || $diffQty > 10)) {
            if (! $business->hasSupervisorPin()) {
                throw ValidationException::withMessages([
                    'supervisor_pin' => __('inventory.supervisor_pin_not_configured'),
                ]);
            }

            $pin = (string) $request->input('supervisor_pin', '');

            if ($pin === '' || ! $business->verifySupervisorPin($pin)) {
                throw ValidationException::withMessages([
                    'supervisor_pin' => __('inventory.supervisor_pin_shrinkage_required'),
                ]);
            }
        }

        if ($diff != 0) {
            $notes = $validated['notes'] ?? 'Penyesuaian stok cepat';
            if (! empty($validated['reason_code'])) {
                $notes = "[{$validated['reason_code']}] {$notes}";
            }

            $stockRule = ApprovalRule::where('business_id', $business->id)
                ->where('document_type', ApprovalRule::DOC_STOCK_ADJUSTMENT)
                ->where('is_active', true)
                ->orderBy('min_amount', 'asc')
                ->first();

            $nominalThreshold = $stockRule !== null ? (float) $stockRule->min_amount : 1000000.0;
            $allowedApproverRoles = ['owner', 'supervisor', 'admin'];
            if ($stockRule !== null && ! empty($stockRule->approver_role_level_1)) {
                $allowedApproverRoles[] = $stockRule->approver_role_level_1;
            }

            $isOwnerOrSupervisor = Context::isOwner() || in_array(Context::role(), $allowedApproverRoles, true);
            $pctReduction = (float) $stock->quantity > 0 ? (abs($diff) / (float) $stock->quantity) * 100 : 100;
            $isHighValue = $diff < 0 && ($diffValue >= $nominalThreshold || ($pctReduction > 20 && abs($diff) >= 5) || abs($diff) > 50);

            $adjustmentNumber = DocumentNumberGenerator::generate(
                prefix: 'ADJ',
                businessId: $business->id,
                table: 'stock_adjustments',
                column: 'adjustment_number'
            );

            // Maker-Checker Gate: High value stock loss by non-owner requires approval (§FR-02)
            if ($isHighValue && ! $isOwnerOrSupervisor) {
                $adjustment = StockAdjustment::create([
                    'business_id' => $business->id,
                    'location_id' => $validated['location_id'],
                    'adjustment_number' => $adjustmentNumber,
                    'adjustment_date' => now()->toDateString(),
                    'reason' => $validated['reason_code'] ?? 'opname_variance',
                    'status' => 'pending_approval',
                    'total_loss_cost' => $diffValue,
                    'notes' => $notes,
                    'created_by' => $user->id,
                ]);

                StockAdjustmentItem::create([
                    'stock_adjustment_id' => $adjustment->id,
                    'product_id' => $validated['product_id'],
                    'system_quantity' => (float) $stock->quantity,
                    'adjusted_quantity' => (float) $validated['new_quantity'],
                    'difference_quantity' => $diff,
                    'unit_cost' => $unitCost,
                    'total_cost' => $diffValue,
                    'notes' => $notes,
                ]);

                AuditLogService::log(
                    (string) $business->id,
                    'STOCK_ADJUSTMENT_PENDING_APPROVAL',
                    $adjustment,
                    null,
                    [
                        'product_id' => $validated['product_id'],
                        'diff_quantity' => $diff,
                        'loss_value' => $diffValue,
                        'user_id' => $user->id,
                    ]
                );

                $msg = __('inventory.adjustment_pending_approval', ['amount' => number_format($diffValue, 0, ',', '.')]);

                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => $msg,
                        'pending_approval' => true,
                        'adjustment_id' => $adjustment->id,
                    ]);
                }

                return redirect()->back()->with('warning', $msg);
            }

            // Standard or Pre-Approved Adjustment:
            $adjustment = StockAdjustment::create([
                'business_id' => $business->id,
                'location_id' => $validated['location_id'],
                'adjustment_number' => $adjustmentNumber,
                'adjustment_date' => now()->toDateString(),
                'reason' => $validated['reason_code'] ?? 'opname_variance',
                'status' => 'completed',
                'total_loss_cost' => $diff < 0 ? $diffValue : 0,
                'notes' => $notes,
                'created_by' => $user->id,
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            StockAdjustmentItem::create([
                'stock_adjustment_id' => $adjustment->id,
                'product_id' => $validated['product_id'],
                'system_quantity' => (float) $stock->quantity,
                'adjusted_quantity' => (float) $validated['new_quantity'],
                'difference_quantity' => $diff,
                'unit_cost' => $unitCost,
                'total_cost' => $diffValue,
                'notes' => $notes,
            ]);

            $movement = $this->stockService->recordMovement(
                businessId: $business->id,
                locationId: $validated['location_id'],
                productId: $validated['product_id'],
                movementType: StockMovement::TYPE_ADJUSTMENT,
                quantityChange: $diff,
                unitCost: $unitCost,
                notes: $notes,
                userId: $user->id
            );

            // Fase 2: Auto-Journal Integration for Stock Adjustment (§FR-04)
            if ($unitCost > 0) {
                $this->journalService->recordStockAdjustmentJournal(
                    business: $business,
                    movement: $movement,
                    quantityChange: $diff,
                    unitCost: $unitCost,
                    userId: $user->id
                );
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => __('inventory.adjustment_success')]);
        }

        return redirect()->back()->with('success', __('inventory.adjustment_success'));
    }

    /**
     * Approve a pending high-value stock adjustment (Maker-Checker approval).
     */
    public function approveAdjustment(StockAdjustment $adjustment): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $stockRule = ApprovalRule::where('business_id', $business->id)
            ->where('document_type', ApprovalRule::DOC_STOCK_ADJUSTMENT)
            ->where('is_active', true)
            ->orderBy('min_amount', 'asc')
            ->first();

        $allowedApproverRoles = ['owner', 'supervisor', 'admin'];
        if ($stockRule !== null && ! empty($stockRule->approver_role_level_1)) {
            $allowedApproverRoles[] = $stockRule->approver_role_level_1;
        }

        $isAuthorized = Context::isOwner() || in_array(Context::role(), $allowedApproverRoles, true) || Context::hasPermission('approvals.manage');
        if (! $isAuthorized) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('inventory.unauthorized_approval'),
                ], 403);
            }
            return redirect()->back()->with('error', __('inventory.unauthorized_approval'));
        }

        if ($adjustment->status !== 'pending_approval') {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('inventory.adjustment_not_found_or_processed'),
                ], 422);
            }
            return redirect()->back()->with('error', __('inventory.adjustment_not_found_or_processed'));
        }

        DB::transaction(function () use ($adjustment, $business, $user) {
            $adjustment->load('items');

            foreach ($adjustment->items as $item) {
                if ($item->difference_quantity != 0) {
                    $movement = $this->stockService->recordMovement(
                        businessId: $business->id,
                        locationId: $adjustment->location_id,
                        productId: $item->product_id,
                        movementType: StockMovement::TYPE_ADJUSTMENT,
                        quantityChange: (float) $item->difference_quantity,
                        unitCost: (float) $item->unit_cost,
                        notes: $item->notes ?: "Persetujuan penyesuaian stok #{$adjustment->adjustment_number}",
                        userId: $user->id
                    );

                    if ((float) $item->unit_cost > 0) {
                        $this->journalService->recordStockAdjustmentJournal(
                            business: $business,
                            movement: $movement,
                            quantityChange: (float) $item->difference_quantity,
                            unitCost: (float) $item->unit_cost,
                            userId: $user->id
                        );
                    }
                }
            }

            $adjustment->update([
                'status' => 'completed',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            AuditLogService::log(
                (string) $business->id,
                'STOCK_ADJUSTMENT_APPROVED',
                $adjustment,
                ['status' => 'pending_approval'],
                [
                    'status' => 'completed',
                    'approved_by' => $user->id,
                    'approved_at' => now()->toDateTimeString(),
                ]
            );
        });

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('inventory.adjustment_approved'),
            ]);
        }

        return redirect()->back()->with('success', __('inventory.adjustment_approved'));
    }

    /**
     * Reject a pending high-value stock adjustment.
     */
    public function rejectAdjustment(Request $request, StockAdjustment $adjustment): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $stockRule = ApprovalRule::where('business_id', $business->id)
            ->where('document_type', ApprovalRule::DOC_STOCK_ADJUSTMENT)
            ->where('is_active', true)
            ->orderBy('min_amount', 'asc')
            ->first();

        $allowedApproverRoles = ['owner', 'supervisor', 'admin'];
        if ($stockRule !== null && ! empty($stockRule->approver_role_level_1)) {
            $allowedApproverRoles[] = $stockRule->approver_role_level_1;
        }

        $isAuthorized = Context::isOwner() || in_array(Context::role(), $allowedApproverRoles, true) || Context::hasPermission('approvals.manage');
        if (! $isAuthorized) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('inventory.unauthorized_approval'),
                ], 403);
            }
            return redirect()->back()->with('error', __('inventory.unauthorized_approval'));
        }

        if ($adjustment->status !== 'pending_approval') {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('inventory.adjustment_not_found_or_processed'),
                ], 422);
            }
            return redirect()->back()->with('error', __('inventory.adjustment_not_found_or_processed'));
        }

        $reason = $request->input('reason', 'Ditolak oleh Owner/Supervisor');

        $adjustment->update([
            'status' => 'rejected',
            'notes' => trim($adjustment->notes . " [Ditolak: {$reason}]"),
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        AuditLogService::log(
            (string) $business->id,
            'STOCK_ADJUSTMENT_REJECTED',
            $adjustment,
            ['status' => 'pending_approval'],
            [
                'status' => 'rejected',
                'reason' => $reason,
                'rejected_by' => $user->id,
            ]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('inventory.adjustment_rejected'),
            ]);
        }

        return redirect()->back()->with('success', __('inventory.adjustment_rejected'));
    }

    /**
     * Stock movements history (Kartu Stok).
     */
    public function movements(Request $request): View
    {
        $business = Context::requireBusiness();

        $locations = Location::where('business_id', $business->id)->where('is_active', true)->get();
        $products = Product::goods()->where('business_id', $business->id)->where('is_active', true)->get();

        $query = StockMovement::where('business_id', $business->id)
            ->with(['product.outputUnit', 'location', 'creator'])
            ->latest('created_at');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->get('product_id'));
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->get('location_id'));
        }

        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->get('movement_type'));
        }

        $movements = $query->paginate(25)->withQueryString();

        return view('app.inventory.movements', compact('business', 'movements', 'locations', 'products'));
    }

    /**
     * Stock Opname index & reconciliation.
     */
    public function opnames(Request $request): View
    {
        $business = Context::requireBusiness();
        $locations = Location::where('business_id', $business->id)->where('is_active', true)->get();
        $products = Product::goods()->where('business_id', $business->id)->where('is_active', true)->get();
        $materials = Material::where('business_id', $business->id)->with('unit')->get();

        $opnames = StockOpname::where('business_id', $business->id)
            ->with(['location', 'conductor', 'reconciler', 'items.material.unit', 'items.product.outputUnit'])
            ->latest('opname_date')
            ->paginate(15);

        return view('app.inventory.opname', compact('business', 'opnames', 'locations', 'products', 'materials'));
    }

    /**
     * Store new Stock Opname.
     */
    public function storeOpname(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $validated = $request->validate([
            'location_id' => ['required', 'uuid', Rule::exists('locations', 'id')->where('business_id', $business->id)],
            'opname_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.material_id' => ['nullable', 'string'],
            'items.*.product_id' => ['nullable', 'string'],
            'items.*.physical_quantity' => ['required', 'numeric', 'min:0'],
        ]);

        $items = collect($validated['items'])->values();
        $validItems = $items->filter(function (array $item, int $index): bool {
            $hasMaterial = filled($item['material_id'] ?? null);
            $hasProduct = filled($item['product_id'] ?? null);

            if ($hasMaterial === $hasProduct) {
                throw ValidationException::withMessages([
                    "items.{$index}" => 'Pilih tepat satu bahan baku atau produk pada setiap baris.',
                ]);
            }

            return true;
        });

        if ($validItems->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Tambahkan minimal satu bahan baku atau produk untuk opname.',
            ]);
        }

        $locationExists = Location::where('business_id', $business->id)
            ->whereKey($validated['location_id'])
            ->exists();
        if (! $locationExists) {
            throw ValidationException::withMessages(['location_id' => 'Lokasi tidak ditemukan pada bisnis aktif.']);
        }

        $opnameNumber = DB::transaction(function () use ($business, $user, $validated, $validItems): string {
            $opnameNumber = DocumentNumberGenerator::generateStockOpnameNumber($business->id);
            $seenItems = [];
            $opname = StockOpname::create([
                'business_id' => $business->id,
                'location_id' => $validated['location_id'],
                'opname_number' => $opnameNumber,
                'opname_date' => $validated['opname_date'],
                'status' => StockOpname::STATUS_IN_PROGRESS,
                'notes' => $validated['notes'] ?? null,
                'conducted_by' => $user->id,
            ]);

            foreach ($validItems as $itemData) {
                $materialId = $itemData['material_id'] ?? null;
                $productId = $itemData['product_id'] ?? null;

                $masterExists = $materialId
                    ? Material::withoutGlobalScopes()->where('business_id', $business->id)->whereKey($materialId)->exists()
                    : Product::withoutGlobalScopes()->where('business_id', $business->id)->whereKey($productId)->exists();
                if (! $masterExists) {
                    throw ValidationException::withMessages(['items' => 'Salah satu item tidak ditemukan pada bisnis aktif.']);
                }

                $itemKey = ($materialId ? 'material:' . $materialId : 'product:' . $productId);
                if (isset($seenItems[$itemKey])) {
                    throw ValidationException::withMessages(['items' => 'Item yang sama hanya boleh dicatat satu kali dalam satu opname.']);
                }
                $seenItems[$itemKey] = true;

                $product = $productId
                    ? Product::withoutGlobalScopes()->where('business_id', $business->id)->find($productId)
                    : null;
                $stockMaterialId = $materialId ?: $product?->direct_material_id;
                $stockQuery = InventoryStock::withoutGlobalScopes()
                    ->where('business_id', $business->id)
                    ->where('location_id', $validated['location_id']);
                $stock = $stockQuery
                    ->when($stockMaterialId, fn ($query) => $query->where('material_id', $stockMaterialId))
                    ->when(! $stockMaterialId, fn ($query) => $query->where('product_id', $productId))
                    ->first();

                $sysQty = (float) ($stock?->quantity ?? 0.0);
                $physQty = (float) $itemData['physical_quantity'];
                $diff = $physQty - $sysQty;
                $unitCost = (float) ($stock?->last_cost ?? 0.0);

                if ($unitCost <= 0 && $materialId) {
                    $mat = Material::withoutGlobalScopes()->where('business_id', $business->id)->with('latestPrice')->find($materialId);
                    $unitCost = (float) ($mat?->latestPrice?->purchase_price ?? 0.0);
                }

                $opname->items()->create([
                    'material_id' => $materialId,
                    'product_id' => $productId,
                    'system_quantity' => $sysQty,
                    'physical_quantity' => $physQty,
                    'difference_quantity' => $diff,
                    'unit_cost' => $unitCost,
                    'total_difference_cost' => $diff * $unitCost,
                ]);
            }

            return $opnameNumber;
        });

        return redirect()->back()->with('success', "Stock Opname #{$opnameNumber} berhasil disimpan.");
    }

    /**
     * Reconcile Stock Opname (apply variances to live inventory).
     */
    public function reconcileOpname(StockOpname $opname): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($opname->business_id === $business->id, 404);

        $user = auth()->user();
        $this->stockService->reconcileStockOpname($opname, $user);

        return redirect()->back()->with('success', "Stock Opname #{$opname->opname_number} berhasil direkonsiliasi.");
    }

    /**
     * Stock Transfers list and creation.
     */
    public function transfers(Request $request): View
    {
        $business = Context::requireBusiness();
        $locations = Location::where('business_id', $business->id)->where('is_active', true)->get();
        $products = Product::goods()->where('business_id', $business->id)->where('is_active', true)->get();

        $transfers = StockTransfer::where('business_id', $business->id)
            ->with(['sourceLocation', 'destinationLocation', 'creator', 'receiver', 'items.product'])
            ->latest('transfer_date')
            ->paginate(15);

        return view('app.inventory.transfers', compact('business', 'transfers', 'locations', 'products'));
    }

    /**
     * Store new Stock Transfer.
     */
    public function storeTransfer(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $validated = $request->validate([
            'source_location_id' => ['required', 'uuid', 'different:destination_location_id', Rule::exists('locations', 'id')->where('business_id', $business->id)],
            'destination_location_id' => ['required', 'uuid', Rule::exists('locations', 'id')->where('business_id', $business->id)],
            'transfer_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', Rule::exists('products', 'id')->where('business_id', $business->id)],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
        ]);

        $transferNumber = 'TRF-' . date('Ymd') . '-' . rand(100, 999);

        $transfer = StockTransfer::create([
            'business_id' => $business->id,
            'source_location_id' => $validated['source_location_id'],
            'destination_location_id' => $validated['destination_location_id'],
            'transfer_number' => $transferNumber,
            'transfer_date' => $validated['transfer_date'],
            'status' => StockTransfer::STATUS_PENDING,
            'notes' => $validated['notes'] ?? null,
            'created_by' => $user->id,
            'sent_at' => now(),
        ]);

        foreach ($validated['items'] as $itemData) {
            $product = Product::find($itemData['product_id']);
            $cost = $product ? (float) $product->base_cost : 0.0;
            $qty = (float) $itemData['quantity'];

            StockTransferItem::create([
                'stock_transfer_id' => $transfer->id,
                'product_id' => $itemData['product_id'],
                'quantity' => $qty,
                'unit_cost' => $cost,
                'total_cost' => $qty * $cost,
            ]);
        }

        return redirect()->back()->with('success', "Transfer Stok #{$transferNumber} berhasil dibuat.");
    }

    /**
     * Receive / complete Stock Transfer.
     */
    public function receiveTransfer(StockTransfer $transfer): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($transfer->business_id === $business->id, 404);

        $user = auth()->user();
        $this->stockService->completeStockTransfer($transfer, $user);

        return redirect()->back()->with('success', "Transfer Stok #{$transfer->transfer_number} telah diterima & stok diperbarui.");
    }
}
