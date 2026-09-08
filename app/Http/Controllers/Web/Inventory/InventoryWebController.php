<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Inventory;

use App\Domain\Inventory\StockService;
use App\Http\Controllers\Controller;
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
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class InventoryWebController extends Controller
{
    public function __construct(
        private readonly StockService $stockService = new StockService
    ) {}

    /**
     * Real-time stock list across outlets & warehouses with low-stock alerts.
     */
    public function stocks(Request $request): View
    {
        $business = Context::requireBusiness();

        $locations = Location::where('business_id', $business->id)->where('is_active', true)->get();
        $products = Product::where('business_id', $business->id)->where('is_active', true)->get();

        $query = InventoryStock::where('business_id', $business->id)
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

        $totalValuation = InventoryStock::where('business_id', $business->id)
            ->selectRaw('SUM(quantity * last_cost) as total_val')
            ->value('total_val') ?? 0.0;

        return view('app.inventory.stocks', compact('business', 'stocks', 'locations', 'products', 'lowStockCount', 'totalValuation'));
    }

    /**
     * Quick stock adjustment / initial balance setup.
     */
    public function quickAdjust(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $validated = $request->validate([
            'location_id' => ['required', 'string'],
            'product_id' => ['required', 'string'],
            'new_quantity' => ['required', 'numeric', 'min:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $stock = $this->stockService->getOrCreateStock(
            $business->id,
            $validated['location_id'],
            $validated['product_id']
        );

        $diff = (float) $validated['new_quantity'] - (float) $stock->quantity;
        $unitCost = (float) ($validated['unit_cost'] ?? $stock->last_cost ?? 0);

        if ($diff != 0) {
            $this->stockService->recordMovement(
                businessId: $business->id,
                locationId: $validated['location_id'],
                productId: $validated['product_id'],
                movementType: StockMovement::TYPE_ADJUSTMENT,
                quantityChange: $diff,
                unitCost: $unitCost,
                notes: $validated['notes'] ?? 'Penyesuaian stok cepat',
                userId: $user->id
            );
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Stok berhasil diperbarui.']);
        }

        return redirect()->back()->with('success', 'Stok berhasil disesuaikan!');
    }

    /**
     * Stock movements history (Kartu Stok).
     */
    public function movements(Request $request): View
    {
        $business = Context::requireBusiness();

        $locations = Location::where('business_id', $business->id)->where('is_active', true)->get();
        $products = Product::where('business_id', $business->id)->where('is_active', true)->get();

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
        $products = Product::where('business_id', $business->id)->where('is_active', true)->get();
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
            'location_id' => ['required', 'string', 'exists:locations,id'],
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
        $products = Product::where('business_id', $business->id)->where('is_active', true)->get();

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
            'source_location_id' => ['required', 'string', 'different:destination_location_id'],
            'destination_location_id' => ['required', 'string'],
            'transfer_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'string'],
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
        $user = auth()->user();
        $this->stockService->completeStockTransfer($transfer, $user);

        return redirect()->back()->with('success', "Transfer Stok #{$transfer->transfer_number} telah diterima & stok diperbarui.");
    }
}
