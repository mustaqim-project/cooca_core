<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Purchasing;

use App\Domain\Accounting\AutoJournalService;
use App\Domain\Inventory\StockService;
use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class GoodsReceiptWebController extends Controller
{
    public function __construct(
        private readonly AutoJournalService $journalService = new AutoJournalService,
        private readonly StockService $stockService = new StockService
    ) {}

    /**
     * Show form to receive physical goods from a Purchase Order.
     */
    public function create(PurchaseOrder $purchaseOrder): View
    {
        $business = Context::requireBusiness();
        abort_unless($purchaseOrder->business_id === $business->id, 403);

        $locations = Location::where('business_id', $business->id)->where('is_active', true)->get();
        $purchaseOrder->load('items.product', 'supplier');

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

        $validated = $request->validate([
            'location_id' => ['required', 'exists:locations,id'],
            'receipt_number' => ['nullable', 'string', 'max:64'],
            'receipt_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.batch_number' => ['nullable', 'string', 'max:64'],
            'items.*.expiry_date' => ['nullable', 'date'],
        ]);

        DB::transaction(function () use ($business, $purchaseOrder, $validated) {
            $receiptNumber = $validated['receipt_number'] ?? ('GR-' . date('Ym') . '-' . rand(1000, 9999));

            $goodsReceipt = GoodsReceipt::create([
                'business_id' => $business->id,
                'location_id' => $validated['location_id'],
                'supplier_id' => $purchaseOrder->supplier_id,
                'purchase_order_id' => $purchaseOrder->id,
                'receipt_number' => $receiptNumber,
                'receipt_date' => $validated['receipt_date'],
                'status' => 'completed',
                'notes' => $validated['notes'] ?? null,
                'received_by' => auth()->id(),
            ]);

            $totalValue = 0.0;

            foreach ($validated['items'] as $item) {
                $qty = (float) $item['quantity'];
                if ($qty <= 0) {
                    continue;
                }
                $cost = (float) $item['unit_cost'];
                $lineTotal = $qty * $cost;
                $totalValue += $lineTotal;

                GoodsReceiptItem::create([
                    'goods_receipt_id' => $goodsReceipt->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                    'batch_number' => $item['batch_number'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                ]);

                // Record stock movement & update inventory stock
                $this->stockService->recordMovement(
                    businessId: $business->id,
                    locationId: $validated['location_id'],
                    productId: $item['product_id'],
                    movementType: StockMovement::TYPE_GOODS_RECEIPT,
                    quantityChange: $qty,
                    unitCost: $cost,
                    referenceId: $goodsReceipt->id,
                    referenceNumber: $receiptNumber,
                    batchNumber: $item['batch_number'] ?? null,
                    expiryDate: $item['expiry_date'] ?? null,
                    notes: "Penerimaan PO {$purchaseOrder->po_number}",
                    userId: auth()->id()
                );
            }

            // Update Purchase Order status
            $purchaseOrder->update(['status' => PurchaseOrder::STATUS_COMPLETED]);
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Penerimaan barang fisik berhasil dicatat dan stok gudang telah diperbarui.');
    }

    /**
     * Solo-Owner 1-Click Instant Stock-In (Beli Langsung ke Stok tanpa alur PO formal).
     */
    public function instantStockIn(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'location_id' => ['required', 'exists:locations,id'],
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($business, $validated) {
            $qty = (float) $validated['quantity'];
            $cost = (float) $validated['unit_cost'];

            $this->stockService->recordMovement(
                businessId: $business->id,
                locationId: $validated['location_id'],
                productId: $validated['product_id'],
                movementType: StockMovement::TYPE_GOODS_RECEIPT,
                quantityChange: $qty,
                unitCost: $cost,
                referenceId: null,
                referenceNumber: null,
                notes: $validated['notes'] ?? 'Beli langsung ke stok (Solo Mode)',
                userId: auth()->id()
            );
        });

        return back()->with('success', 'Pembelian langsung berhasil dicatat! Stok produk telah bertambah seketika.');
    }
}
