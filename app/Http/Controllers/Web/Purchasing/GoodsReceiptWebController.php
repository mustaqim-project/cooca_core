<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Purchasing;

use App\Domain\Inventory\StockService;
use App\Domain\Purchasing\SupplierInvoiceService;
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
use Illuminate\Validation\ValidationException;

final class GoodsReceiptWebController extends Controller
{
    public function __construct(
        private readonly StockService $stockService = new StockService,
        private readonly SupplierInvoiceService $supplierInvoiceService = new SupplierInvoiceService
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

        // Kunci unik untuk melacak jumlah terima: utamakan product, lalu material,
        // terakhir nama item bebas (non-inventori).
        $entityKey = static fn (array $i): string => ! empty($i['product_id'])
            ? 'p:' . $i['product_id']
            : (! empty($i['material_id'])
                ? 'm:' . $i['material_id']
                : 'f:' . ($i['item_name'] ?? ''));

        DB::transaction(function () use ($business, $purchaseOrder, $validated, $entityKey) {
            $purchaseOrder = PurchaseOrder::with('items')->lockForUpdate()->findOrFail($purchaseOrder->id);
            $receivedByKey = collect(
                GoodsReceiptItem::query()
                    ->whereHas('goodsReceipt', fn ($query) => $query->where('purchase_order_id', $purchaseOrder->id)->where('status', 'completed'))
                    ->get(['product_id', 'material_id', 'item_name', 'quantity'])
                    ->all()
            )->groupBy(fn (GoodsReceiptItem $i) => ! empty($i->product_id)
                ? 'p:' . $i->product_id
                : (! empty($i->material_id)
                    ? 'm:' . $i->material_id
                    : 'f:' . ($i->item_name ?? '')))
                ->map(fn ($rows) => (float) $rows->sum('quantity'));

            foreach ($validated['items'] as $item) {
                $source = $purchaseOrder->items->first(fn ($poItem) =>
                    (! empty($item['product_id']) && $poItem->product_id === $item['product_id'])
                    || (! empty($item['material_id']) && $poItem->material_id === $item['material_id'])
                    || (empty($item['product_id']) && empty($item['material_id']) && $poItem->item_name === ($item['item_name'] ?? null))
                );
                $quantity = (float) $item['quantity'];

                if (! $source && $quantity > 0) {
                    throw ValidationException::withMessages(['items' => 'Produk penerimaan tidak terdapat pada Purchase Order.']);
                }

                $remaining = $source
                    ? (float) $source->quantity - (float) ($receivedByKey[$entityKey($item)] ?? 0)
                    : 0.0;

                if ($source && $quantity > $remaining + 0.00005) {
                    throw ValidationException::withMessages(['items' => "Quantity penerimaan melebihi sisa PO untuk {$source->item_name}."]);
                }
            }
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
                    'product_id' => $item['product_id'] ?? null,
                    'material_id' => $item['material_id'] ?? null,
                    'item_name' => $item['item_name'] ?? null,
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                    'batch_number' => $item['batch_number'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                ]);

                // Catat mutasi stok hanya untuk inventori nyata (product/material).
                // Item bebas non-inventori (jasa / paket layanan) tidak menyentuh stok.
                if (! empty($item['product_id']) || ! empty($item['material_id'])) {
                    $this->stockService->recordMovement(
                        businessId: $business->id,
                        locationId: $validated['location_id'],
                        productId: $item['product_id'] ?? null,
                        materialId: $item['material_id'] ?? null,
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

                    // Sinkronkan MaterialPrice dengan harga beli aktual (GR) agar
                    // HPP produk terkait selalu mencerminkan harga beli terakhir,
                    // lalu propagasikan ke product.base_cost. Riwayat transaksi tetap immutable.
                    if (! empty($item['material_id']) && $cost > 0) {
                        \App\Models\MaterialPrice::create([
                            'business_id' => $business->id,
                            'material_id' => $item['material_id'],
                            'supplier_id' => $purchaseOrder->supplier_id,
                            'purchase_price' => $cost,
                            'shipping_cost' => 0,
                            'handling_cost' => 0,
                            'import_cost' => 0,
                            'discount_amount' => 0,
                            'purchase_unit_id' => \App\Models\Material::find($item['material_id'])?->unit_id,
                            'yield_percentage' => 100,
                            'waste_percentage' => 0,
                            'effective_date' => $validated['receipt_date'],
                            'notes' => "Auto dari Goods Receipt {$receiptNumber} (PO {$purchaseOrder->po_number})",
                        ]);

                        app(\App\Domain\Calculation\HppPropagationService::class)->refreshForMaterial($item['material_id']);
                    }
                }
            }

            // Update Purchase Order status
            $entityOfPoItem = static fn ($poItem): string => ! empty($poItem->product_id)
                ? 'p:' . $poItem->product_id
                : (! empty($poItem->material_id)
                    ? 'm:' . $poItem->material_id
                    : 'f:' . ($poItem->item_name ?? ''));

            $currentByKey = collect($validated['items'])
                ->groupBy(fn ($row) => $entityKey(is_array($row) ? $row : (array) $row))
                ->map(fn ($rows) => (float) $rows->sum('quantity'));

            $fullyReceived = $purchaseOrder->items->every(fn ($poItem) =>
                (float) ($receivedByKey[$entityOfPoItem($poItem)] ?? 0)
                    + (float) ($currentByKey[$entityOfPoItem($poItem)] ?? 0)
                    >= (float) $poItem->quantity - 0.00005
            );
            $purchaseOrder->update(['status' => $fullyReceived ? PurchaseOrder::STATUS_COMPLETED : PurchaseOrder::STATUS_PARTIALLY_INVOICED]);

            $this->supplierInvoiceService->createFromGoodsReceipt($goodsReceipt);
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
