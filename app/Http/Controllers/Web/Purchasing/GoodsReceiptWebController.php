<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Purchasing;

use App\Domain\Inventory\StockService;
use App\Domain\Purchasing\GoodsReceiptService;
use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\Location;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class GoodsReceiptWebController extends Controller
{
    public function __construct(
        private readonly StockService $stockService = new StockService,
        private readonly GoodsReceiptService $goodsReceiptService = new GoodsReceiptService
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

        $validated = $request->validate([
            'location_id' => ['required', 'exists:locations,id'],
            'receipt_number' => ['nullable', 'string', 'max:64'],
            'receipt_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.material_id' => ['nullable', 'exists:materials,id'],
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
                userId: request()->user()?->id
            );
        });

        return back()->with('success', 'Pembelian langsung berhasil dicatat! Stok produk telah bertambah seketika.');
    }
}
