<?php

declare(strict_types=1);

namespace App\Domain\Purchasing;

use App\Domain\Calculation\HppPropagationService;
use App\Domain\Inventory\StockService;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Material;
use App\Models\MaterialPrice;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Support\Context;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

final class GoodsReceiptService
{
    public function __construct(
        private readonly StockService $stockService = new StockService,
        private readonly SupplierInvoiceService $supplierInvoiceService = new SupplierInvoiceService,
        private readonly HppPropagationService $hppPropagationService = new HppPropagationService,
    ) {}

    /**
     * Post a physical receipt against a confirmed or partially received PO.
     * The operation is atomic across receipt, stock, PO state, costing, and AP.
     *
     * @param array<string, mixed> $data
     */
    public function receive(PurchaseOrder $purchaseOrder, array $data, ?string $userId = null): GoodsReceipt
    {
        return DB::transaction(function () use ($purchaseOrder, $data, $userId): GoodsReceipt {
            $purchaseOrder = PurchaseOrder::with('items')->lockForUpdate()->findOrFail($purchaseOrder->id);
            if (! in_array($purchaseOrder->status, [
                PurchaseOrder::STATUS_CONFIRMED,
                PurchaseOrder::STATUS_PARTIALLY_INVOICED,
            ], true)) {
                throw new InvalidArgumentException("Purchase Order {$purchaseOrder->po_number} belum dapat diterima karena statusnya {$purchaseOrder->status}.");
            }

            $items = collect($data['items']);
            $key = static fn (array $item): string => ! empty($item['product_id'])
                ? 'p:' . $item['product_id']
                : (! empty($item['material_id']) ? 'm:' . $item['material_id'] : 'f:' . ($item['item_name'] ?? ''));

            $received = GoodsReceiptItem::query()
                ->whereHas('goodsReceipt', fn ($query) => $query
                    ->where('purchase_order_id', $purchaseOrder->id)
                    ->where('status', 'completed'))
                ->get(['product_id', 'material_id', 'item_name', 'quantity'])
                ->groupBy(fn (GoodsReceiptItem $item): string => $key($item->toArray()))
                ->map(fn (Collection $rows): float => (float) $rows->sum('quantity'));

            foreach ($items as $item) {
                $item = (array) $item;
                $itemKey = $key($item);
                $source = $purchaseOrder->items->first(fn ($poItem): bool =>
                    ($itemKey === 'p:' . $poItem->product_id && $poItem->product_id !== null)
                    || ($itemKey === 'm:' . $poItem->material_id && $poItem->material_id !== null)
                    || ($itemKey === 'f:' . ($poItem->item_name ?? '') && str_starts_with($itemKey, 'f:'))
                );
                $quantity = (float) ($item['quantity'] ?? 0);
                if (! $source && $quantity > 0) {
                    throw ValidationException::withMessages(['items' => 'Item penerimaan tidak terdapat pada Purchase Order.']);
                }
                $remaining = $source ? (float) $source->quantity - (float) ($received[$itemKey] ?? 0) : 0.0;
                if ($source && $quantity > $remaining + 0.00005) {
                    throw ValidationException::withMessages(['items' => "Quantity penerimaan melebihi sisa PO untuk {$source->item_name}."]);
                }
                if ($quantity < 0) {
                    throw ValidationException::withMessages(['items' => 'Quantity penerimaan tidak boleh negatif.']);
                }
            }

            $receiptNumber = $data['receipt_number'] ?? ('GR-' . now()->format('Ym') . '-' . random_int(1000, 9999));
            $receipt = GoodsReceipt::create([
                'business_id' => $purchaseOrder->business_id,
                'location_id' => $data['location_id'],
                'supplier_id' => $purchaseOrder->supplier_id,
                'purchase_order_id' => $purchaseOrder->id,
                'receipt_number' => $receiptNumber,
                'receipt_date' => $data['receipt_date'],
                'status' => 'completed',
                'notes' => $data['notes'] ?? null,
                'received_by' => $userId,
            ]);

            foreach ($items as $item) {
                $item = (array) $item;
                $quantity = (float) $item['quantity'];
                if ($quantity <= 0) {
                    continue;
                }
                $materialId = $item['material_id'] ?? null;
                $productId = $item['product_id'] ?? null;
                GoodsReceiptItem::create([
                    'goods_receipt_id' => $receipt->id,
                    'product_id' => $productId,
                    'material_id' => $materialId,
                    'item_name' => $item['item_name'] ?? null,
                    'quantity' => $quantity,
                    'unit_cost' => (float) $item['unit_cost'],
                    'batch_number' => $item['batch_number'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                ]);

                if (! $productId && ! $materialId) {
                    continue;
                }
                $this->stockService->recordMovement(
                    businessId: $purchaseOrder->business_id,
                    locationId: $data['location_id'],
                    productId: $productId,
                    materialId: $materialId,
                    movementType: StockMovement::TYPE_GOODS_RECEIPT,
                    quantityChange: $quantity,
                    unitCost: (float) $item['unit_cost'],
                    referenceId: $receipt->id,
                    referenceNumber: $receiptNumber,
                    batchNumber: $item['batch_number'] ?? null,
                    expiryDate: $item['expiry_date'] ?? null,
                    notes: "Penerimaan PO {$purchaseOrder->po_number}",
                    userId: $userId,
                );

                if ($materialId && (float) $item['unit_cost'] > 0) {
                    $material = Material::find($materialId);
                    MaterialPrice::create([
                        'business_id' => $purchaseOrder->business_id,
                        'material_id' => $materialId,
                        'supplier_id' => $purchaseOrder->supplier_id,
                        'purchase_price' => (float) $item['unit_cost'],
                        'shipping_cost' => 0,
                        'handling_cost' => 0,
                        'import_cost' => 0,
                        'discount_amount' => 0,
                        'purchase_unit_id' => $material?->unit_id,
                        'yield_percentage' => 100,
                        'waste_percentage' => 0,
                        'effective_date' => $data['receipt_date'],
                        'notes' => "Auto dari Goods Receipt {$receiptNumber}",
                    ]);
                    $this->hppPropagationService->refreshForMaterial($materialId);
                }
            }

            $current = $items->groupBy(fn ($item): string => $key((array) $item))
                ->map(fn (Collection $rows): float => (float) $rows->sum('quantity'));
            $fullyReceived = $purchaseOrder->items->every(function ($poItem) use ($received, $current, $key): bool {
                $itemKey = $poItem->product_id
                    ? 'p:' . $poItem->product_id
                    : ($poItem->material_id ? 'm:' . $poItem->material_id : 'f:' . ($poItem->item_name ?? ''));
                return (float) ($received[$itemKey] ?? 0) + (float) ($current[$itemKey] ?? 0)
                    >= (float) $poItem->quantity - 0.00005;
            });
            $purchaseOrder->update([
                'status' => $fullyReceived ? PurchaseOrder::STATUS_COMPLETED : PurchaseOrder::STATUS_PARTIALLY_INVOICED,
            ]);

            $this->supplierInvoiceService->createFromGoodsReceipt($receipt);
            return $receipt;
        });
    }
}
