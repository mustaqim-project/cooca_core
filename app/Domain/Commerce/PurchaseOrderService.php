<?php

declare(strict_types=1);

namespace App\Domain\Commerce;

use App\Models\Business;
use App\Models\Material;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Carbon\Carbon;
use InvalidArgumentException;

final class PurchaseOrderService
{
    public function __construct(
        private readonly InvoiceNumberGenerator $numberGenerator = new InvoiceNumberGenerator
    ) {}

    /**
     * Create a new purchase order with items.
     *
     * @param  array<string, mixed>  $poData
     * @param  array<int, array{
     *     item_type?: string,
     *     product_id?: string|null,
     *     material_id?: string|null,
     *     item_name?: string|null,
     *     sku?: string|null,
     *     quantity: float|int,
     *     unit_id: string,
     *     unit_price: float|int,
     *     notes?: string|null
     * }>  $itemsData
     */
    public function createPurchaseOrder(Business $business, array $poData, array $itemsData): PurchaseOrder
    {
        if (empty($itemsData)) {
            throw new InvalidArgumentException('Purchase order must contain at least one item.');
        }

        $type = $poData['po_type'] ?? PurchaseOrder::TYPE_CUSTOMER;
        $poNumber = $poData['po_number'] ?? $this->numberGenerator->generatePoNumber($business, $type);
        $orderDate = ! empty($poData['order_date']) ? Carbon::parse($poData['order_date']) : Carbon::today();

        /** @var PurchaseOrder $po */
        $po = PurchaseOrder::create([
            'business_id' => $business->id,
            'po_type' => $type,
            'po_number' => $poNumber,
            'reference_number' => $poData['reference_number'] ?? null,
            'customer_id' => $poData['customer_id'] ?? null,
            'supplier_id' => $poData['supplier_id'] ?? null,
            'order_date' => $orderDate->toDateString(),
            'expected_delivery_date' => ! empty($poData['expected_delivery_date']) ? Carbon::parse($poData['expected_delivery_date'])->toDateString() : null,
            'status' => $poData['status'] ?? PurchaseOrder::STATUS_DRAFT,
            'discount_type' => $poData['discount_type'] ?? 'percentage',
            'discount_value' => (float) ($poData['discount_value'] ?? 0.0),
            'tax_percentage' => (float) ($poData['tax_percentage'] ?? 0.0),
            'terms_and_conditions' => $poData['terms_and_conditions'] ?? null,
            'notes' => $poData['notes'] ?? null,
            'created_by' => $poData['created_by'] ?? null,
        ]);

        foreach ($itemsData as $item) {
            $itemType = $item['item_type'] ?? ($type === PurchaseOrder::TYPE_SUPPLIER ? 'material' : 'product');
            $productId = $item['product_id'] ?? null;
            $materialId = $item['material_id'] ?? null;

            $product = $productId ? Product::find($productId) : null;
            $material = $materialId ? Material::find($materialId) : null;

            $name = $item['item_name'] ?? ($product?->name ?? ($material?->name ?? 'Item'));
            $sku = $item['sku'] ?? ($product?->code ?? ($material?->code ?? null));
            $unitId = $item['unit_id'] ?? ($product?->output_unit_id ?? ($material?->unit_id));
            $unitPrice = (float) $item['unit_price'];

            $costSnapshot = 0.0;
            if ($product) {
                $costSnapshot = (float) ($product->base_cost ?? 0.0);
            } elseif ($material) {
                $costSnapshot = (float) ($material->effective_cost ?? 0.0);
            }

            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'item_type' => $itemType,
                'product_id' => $productId,
                'material_id' => $materialId,
                'item_name' => $name,
                'sku' => $sku,
                'quantity' => (float) $item['quantity'],
                'unit_id' => $unitId,
                'unit_price' => $unitPrice,
                'cost_price_snapshot' => $costSnapshot,
                'notes' => $item['notes'] ?? null,
            ]);
        }

        $po->recalculateTotals();

        return $po->fresh(['items', 'customer', 'supplier']);
    }

    /**
     * Confirm a draft PO.
     */
    public function confirm(PurchaseOrder $po): PurchaseOrder
    {
        $po->update(['status' => PurchaseOrder::STATUS_CONFIRMED]);

        return $po;
    }

    /**
     * Cancel a PO.
     */
    public function cancel(PurchaseOrder $po): PurchaseOrder
    {
        $po->update(['status' => PurchaseOrder::STATUS_CANCELLED]);

        return $po;
    }
}
