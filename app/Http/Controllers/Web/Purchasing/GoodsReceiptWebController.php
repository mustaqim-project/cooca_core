<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Purchasing;

use App\Domain\Finance\CashLedgerService;
use App\Domain\Inventory\StockService;
use App\Domain\Purchasing\GoodsReceiptService;
use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\GoodsReceipt;
use App\Models\Location;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class GoodsReceiptWebController extends Controller
{
    public function __construct(
        private readonly StockService $stockService = new StockService,
        private readonly GoodsReceiptService $goodsReceiptService = new GoodsReceiptService,
        private readonly CashLedgerService $cashLedgerService = new CashLedgerService
    ) {}

    /**
     * Show form to receive physical goods from a Purchase Order.
     */
    public function create(PurchaseOrder $purchaseOrder): View
    {
        $business = Context::requireBusiness();
        abort_unless($purchaseOrder->business_id === $business->id, 403);

        // State machine: hanya PO yang sudah dikonfirmasi (atau sedang receiving parsial)
        // yang boleh diterima. Draft/Completed/Cancelled dilarang.
        abort_unless(
            in_array($purchaseOrder->status, [
                PurchaseOrder::STATUS_CONFIRMED,
                PurchaseOrder::STATUS_PARTIALLY_INVOICED,
            ], true),
            403,
            'Purchase Order belum dapat diterima karena statusnya belum dikonfirmasi.'
        );

        $locations = Location::where('business_id', $business->id)->where('is_active', true)->get();
        $purchaseOrder->load('items.product', 'items.material', 'supplier');

        // Calculate next receipt number
        $prefix = 'GR-' . date('Ym') . '-';
        $latest = GoodsReceipt::where('business_id', $business->id)
            ->where('receipt_number', 'LIKE', $prefix . '%')
            ->orderByDesc('receipt_number')
            ->value('receipt_number');
        $nextSeq = 1;
        if ($latest && preg_match('/-(\d+)$/', $latest, $m)) {
            $nextSeq = ((int) $m[1]) + 1;
        }
        $nextReceiptNumber = $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);

        return view('app.purchasing.receipts.create', compact('business', 'purchaseOrder', 'locations', 'nextReceiptNumber'));
    }

    /**
     * Store Goods Receipt from Purchase Order.
     */
    public function store(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($purchaseOrder->business_id === $business->id, 403);

        // State machine: hanya PO confirmed / partial yang bisa menerima barang.
        abort_unless(
            in_array($purchaseOrder->status, [
                PurchaseOrder::STATUS_CONFIRMED,
                PurchaseOrder::STATUS_PARTIALLY_INVOICED,
            ], true),
            403,
            'Purchase Order belum dapat diterima karena statusnya belum dikonfirmasi.'
        );

        $businessId = $business->id;

        $validated = $request->validate([
            'location_id' => ['required', Rule::exists('locations', 'id')->where('business_id', $businessId)],
            'receipt_number' => ['nullable', 'string', 'max:64'],
            'receipt_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', Rule::exists('products', 'id')->where('business_id', $businessId)],
            'items.*.material_id' => ['nullable', Rule::exists('materials', 'id')->where('business_id', $businessId)],
            'items.*.item_name' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.batch_number' => ['nullable', 'string', 'max:64'],
            'items.*.expiry_date' => ['nullable', 'date'],
        ]);

        $this->goodsReceiptService->receive($purchaseOrder, $validated, request()->user()?->id);

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Penerimaan barang fisik berhasil dicatat dan stok gudang telah diperbarui.');

    }

    /**
     * Solo-Owner 1-Click Instant Stock-In (Beli Langsung ke Stok tanpa alur PO formal).
     */
    public function instantStockIn(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        $businessId = $business->id;

        $validated = $request->validate([
            'location_id' => ['required', Rule::exists('locations', 'id')->where('business_id', $businessId)],
            'product_id' => ['nullable', 'required_without:material_id', Rule::exists('products', 'id')->where('business_id', $businessId)],
            'material_id' => ['nullable', 'required_without:product_id', Rule::exists('materials', 'id')->where('business_id', $businessId)],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'supplier_id' => ['nullable', Rule::exists('suppliers', 'id')->where('business_id', $businessId)],
            'cash_account_id' => ['nullable', Rule::exists('cash_accounts', 'id')->where('business_id', $businessId)],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($business, $validated) {
            $qty = (float) $validated['quantity'];
            $cost = (float) $validated['unit_cost'];
            $totalCost = $qty * $cost;
            $productId = $validated['product_id'] ?? null;
            $materialId = $validated['material_id'] ?? null;

            $this->stockService->recordMovement(
                businessId: $business->id,
                locationId: $validated['location_id'],
                productId: $productId,
                movementType: StockMovement::TYPE_GOODS_RECEIPT,
                quantityChange: $qty,
                unitCost: $cost,
                referenceId: null,
                referenceNumber: null,
                batchNumber: null,
                expiryDate: null,
                notes: $validated['notes'] ?? 'Beli langsung ke stok (Solo Mode)',
                userId: request()->user()?->id,
                materialId: $materialId
            );

            // Record cash outflow if cash account is selected
            if (!empty($validated['cash_account_id']) && $totalCost > 0) {
                $cashAccount = CashAccount::where('business_id', $business->id)->find($validated['cash_account_id']);
                if ($cashAccount) {
                    $this->cashLedgerService->recordOutflow(
                        business: $business,
                        amount: $totalCost,
                        referenceType: 'instant_stock_in',
                        referenceId: (string) ($productId ?? $materialId),
                        description: 'Pembelian langsung stok: ' . ($validated['notes'] ?? 'Belanja pasar/tunai'),
                        method: $cashAccount->type ?? 'cash',
                        userId: request()->user()?->id,
                        account: $cashAccount
                    );
                }
            }
        });

        return back()->with('success', __('purchasing.messages.stock_in_success'));
    }
}
