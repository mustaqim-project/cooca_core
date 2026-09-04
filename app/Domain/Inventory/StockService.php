<?php

declare(strict_types=1);

namespace App\Domain\Inventory;

use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Models\Business;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\StockTransfer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class StockService
{
    /**
     * Get or create stock record for a material (or legacy product) at a specific location with optional row-level pessimistic locking.
     * Follows Rule 01 & 21: Material is the Master Stock and Single Source of Truth.
     */
    public function getOrCreateStock(
        string $businessId,
        string $locationId,
        ?string $productId = null,
        bool $lock = false,
        ?string $materialId = null
    ): InventoryStock {
        // Resolve material if product has direct material
        if (!$materialId && $productId) {
            $product = Product::withoutGlobalScopes()->find($productId);
            if ($product?->direct_material_id) {
                $materialId = $product->direct_material_id;
            }
        }

        $query = InventoryStock::withoutGlobalScopes()
            ->where('business_id', $businessId)
            ->where('location_id', $locationId);

        if ($materialId) {
            $query->where('material_id', $materialId);
        } elseif ($productId) {
            $query->where('product_id', $productId);
        } else {
            throw new InvalidArgumentException('Minimal material_id atau product_id harus ditentukan.');
        }

        if ($lock) {
            $query->lockForUpdate();
        }

        $stock = $query->first();

        if (! $stock) {
            $stock = InventoryStock::create([
                'business_id' => $businessId,
                'location_id' => $locationId,
                'material_id' => $materialId,
                'product_id' => $productId,
                'quantity' => 0.0,
                'reserved_quantity' => 0.0,
                'last_cost' => 0.0,
                'avg_purchase_cost' => 0.0,
            ]);

            if ($lock) {
                $stock = InventoryStock::withoutGlobalScopes()->where('id', $stock->id)->lockForUpdate()->first();
            }
        }

        return $stock;
    }

    /**
     * Record a stock movement and update real-time stock balance atomically.
     * Enforces concurrency safety via pessimistic locking and validates negative stock if disallowed.
     *
     * @throws InsufficientStockException
     */
    public function recordMovement(
        string $businessId,
        string $locationId,
        ?string $productId = null,
        string $movementType = StockMovement::TYPE_INITIAL,
        float $quantityChange = 0.0,
        float $unitCost = 0.0,
        ?string $referenceId = null,
        ?string $referenceNumber = null,
        ?string $batchNumber = null,
        ?string $expiryDate = null,
        ?string $notes = null,
        ?string $userId = null,
        ?string $materialId = null
    ): StockMovement {
        if ($unitCost < 0) {
            throw new InvalidArgumentException('Unit cost tidak boleh negatif.');
        }

        return DB::transaction(function () use (
            $businessId,
            $locationId,
            $productId,
            $movementType,
            $quantityChange,
            $unitCost,
            $referenceId,
            $referenceNumber,
            $batchNumber,
            $expiryDate,
            $notes,
            $userId,
            $materialId
        ) {
            // Pessimistic locking to prevent race conditions
            $stock = $this->getOrCreateStock(
                businessId: $businessId,
                locationId: $locationId,
                productId: $productId,
                lock: true,
                materialId: $materialId
            );

            $oldQuantity = (float) $stock->quantity;
            $oldAverageCost = (float) $stock->avg_purchase_cost;
            $newQuantity = (float) $stock->quantity + $quantityChange;

            // Enforce negative stock prevention if configured by the business (Rule 17)
            if ($newQuantity < 0 && $quantityChange < 0) {
                $business = Business::find($businessId);
                $allowNegative = (bool) ($business?->allow_negative_stock ?? false);

                if (! $allowNegative) {
                    $itemLabel = 'Item ID: ' . ($materialId ?? $productId ?? '');
                    if ($materialId) {
                        $mat = \App\Models\Material::find($materialId);
                        if ($mat) $itemLabel = "Bahan Baku: {$mat->name} ({$mat->code})";
                    } elseif ($productId) {
                        $prod = Product::find($productId);
                        if ($prod) $itemLabel = "Produk: {$prod->name} ({$prod->code})";
                    }

                    $location = Location::find($locationId);

                    throw new InsufficientStockException(
                        productName: $itemLabel,
                        availableStock: (float) $stock->quantity,
                        requestedQuantity: abs($quantityChange),
                        locationName: $location?->name ?? ''
                    );
                }
            }

            $stock->quantity = $newQuantity;
            if ($unitCost > 0) {
                $stock->last_cost = $unitCost;
            }
            if ($movementType === StockMovement::TYPE_GOODS_RECEIPT && $quantityChange > 0 && $unitCost >= 0) {
                $oldValue = max(0.0, $oldQuantity) * $oldAverageCost;
                $receivedValue = $quantityChange * $unitCost;
                $stock->avg_purchase_cost = $newQuantity > 0
                    ? ($oldValue + $receivedValue) / $newQuantity
                    : 0.0;
            }
            $stock->save();

            return StockMovement::create([
                'business_id' => $businessId,
                'location_id' => $locationId,
                'material_id' => $materialId ?? $stock->material_id,
                'product_id' => $productId ?? $stock->product_id,
                'movement_type' => $movementType,
                'reference_id' => $referenceId,
                'reference_number' => $referenceNumber,
                'quantity_change' => $quantityChange,
                'balance_after' => $newQuantity,
                'unit_cost' => $unitCost,
                'total_cost' => abs($quantityChange) * $unitCost,
                'batch_number' => $batchNumber,
                'expiry_date' => $expiryDate,
                'notes' => $notes,
                'created_by' => $userId,
            ]);
        });
    }

    /**
     * Deduct constituent material stock for a product sale.
     * Follows Rule 12, 13, 14, 15:
     * - Resolves Direct Material or Recipe/BOM proportions
     * - NO double deduction
     * - Deducts each material atomically
     *
     * @return array<int, StockMovement>
     */
    public function deductForProductSale(
        string $businessId,
        string $locationId,
        Product|string $product,
        float $productQuantity,
        float $unitCost,
        string $orderId,
        string $orderNumber,
        ?string $userId = null,
        string $movementType = StockMovement::TYPE_POS_SALE,
        ?string $notes = null
    ): array {
        $productModel = is_string($product) ? Product::find($product) : $product;
        if (! $productModel) {
            return [];
        }

        $deductions = $productModel->getMaterialDeductions($productQuantity);
        $movements = [];

        if (!empty($deductions)) {
            foreach ($deductions as $d) {
                $matId = $d['material_id'];
                $matQty = (float) $d['quantity'];

                $movements[] = $this->recordMovement(
                    businessId: $businessId,
                    locationId: $locationId,
                    productId: $productModel->id,
                    movementType: $movementType,
                    quantityChange: -abs($matQty),
                    unitCost: $unitCost,
                    referenceId: $orderId,
                    referenceNumber: $orderNumber,
                    notes: $notes ?? "Penjualan Produk {$productModel->name} #{$orderNumber}",
                    userId: $userId,
                    materialId: $matId
                );
            }
            return $movements;
        }

        // Fallback for legacy items without linked materials
        if ($productModel->stocks()->where('location_id', $locationId)->exists()) {
            $movements[] = $this->recordMovement(
                businessId: $businessId,
                locationId: $locationId,
                productId: $productModel->id,
                movementType: $movementType,
                quantityChange: -abs($productQuantity),
                unitCost: $unitCost,
                referenceId: $orderId,
                referenceNumber: $orderNumber,
                notes: $notes ?? "Penjualan Produk {$productModel->name} #{$orderNumber}",
                userId: $userId
            );
        }

        return $movements;
    }

    /**
     * Deduct stock for a POS order item.
     */
    public function deductForPosSale(
        string $businessId,
        string $locationId,
        Product|string|null $product = null,
        float $quantity = 0.0,
        float $unitCost = 0.0,
        string $orderId = '',
        string $orderNumber = '',
        ?string $userId = null,
        ?string $productId = null
    ): array {
        $effectiveProduct = $product ?? $productId;
        return $this->deductForProductSale(
            businessId: $businessId,
            locationId: $locationId,
            product: $effectiveProduct,
            productQuantity: $quantity,
            unitCost: $unitCost,
            orderId: $orderId,
            orderNumber: $orderNumber,
            userId: $userId,
            movementType: StockMovement::TYPE_POS_SALE,
            notes: "Penjualan Kasir POS #{$orderNumber}"
        );
    }

    /**
     * Restore stock for a POS order refund / return.
     */
    public function restoreForPosRefund(
        string $businessId,
        string $locationId,
        Product|string|null $product = null,
        float $quantity = 0.0,
        float $unitCost = 0.0,
        string $orderId = '',
        string $orderNumber = '',
        ?string $userId = null,
        ?string $productId = null
    ): array {
        $effectiveProduct = $product ?? $productId;
        $productModel = is_string($effectiveProduct) ? Product::find($effectiveProduct) : $effectiveProduct;
        if (! $productModel) return [];

        $deductions = $productModel->getMaterialDeductions($quantity);
        $movements = [];

        if (!empty($deductions)) {
            foreach ($deductions as $d) {
                $movements[] = $this->recordMovement(
                    businessId: $businessId,
                    locationId: $locationId,
                    productId: $productModel->id,
                    movementType: StockMovement::TYPE_POS_REFUND,
                    quantityChange: abs((float) $d['quantity']),
                    unitCost: $unitCost,
                    referenceId: $orderId,
                    referenceNumber: $orderNumber,
                    notes: "Pengembalian / Retur POS #{$orderNumber}",
                    userId: $userId,
                    materialId: $d['material_id']
                );
            }
            return $movements;
        }

        $movements[] = $this->recordMovement(
            businessId: $businessId,
            locationId: $locationId,
            productId: $productModel->id,
            movementType: StockMovement::TYPE_POS_REFUND,
            quantityChange: abs($quantity),
            unitCost: $unitCost,
            referenceId: $orderId,
            referenceNumber: $orderNumber,
            notes: "Pengembalian / Retur POS #{$orderNumber}",
            userId: $userId
        );

        return $movements;
    }

    /**
     * Deduct stock for an issued/released Sales Invoice.
     * Centralized gateway for B2B commercial sales.
     */
    public function deductForInvoiceSale(
        string $businessId,
        string $locationId,
        Product|string|null $product = null,
        float $quantity = 0.0,
        float $unitCost = 0.0,
        string $invoiceId = '',
        string $invoiceNumber = '',
        ?string $userId = null,
        ?string $productId = null
    ): StockMovement {
        $effectiveProduct = $product ?? $productId;
        $movements = $this->deductForProductSale(
            businessId: $businessId,
            locationId: $locationId,
            product: $effectiveProduct,
            productQuantity: $quantity,
            unitCost: $unitCost,
            orderId: $invoiceId,
            orderNumber: $invoiceNumber,
            userId: $userId,
            movementType: StockMovement::TYPE_INVOICE_SALE,
            notes: "Faktur Penjualan #{$invoiceNumber}"
        );

        return $movements[0] ?? new StockMovement();
    }

    /**
     * Restore stock for a returned / cancelled Sales Invoice.
     */
    public function restoreForInvoiceReturn(
        string $businessId,
        string $locationId,
        Product|string|null $product = null,
        float $quantity = 0.0,
        float $unitCost = 0.0,
        string $invoiceId = '',
        string $invoiceNumber = '',
        ?string $userId = null,
        ?string $productId = null
    ): array {
        $effectiveProduct = $product ?? $productId;
        return $this->restoreForPosRefund(
            businessId: $businessId,
            locationId: $locationId,
            product: $effectiveProduct,
            quantity: $quantity,
            unitCost: $unitCost,
            orderId: $invoiceId,
            orderNumber: $invoiceNumber,
            userId: $userId
        );
    }

    public function restoreForSalesReturn(
        string $businessId,
        string $locationId,
        Product|string|null $product = null,
        float $quantity = 0.0,
        float $unitCost = 0.0,
        string $returnId = '',
        string $returnNumber = '',
        ?string $userId = null,
        ?string $productId = null
    ): array {
        $effectiveProduct = $product ?? $productId;
        return $this->restoreForPosRefund(
            businessId: $businessId,
            locationId: $locationId,
            product: $effectiveProduct,
            quantity: $quantity,
            unitCost: $unitCost,
            orderId: $returnId,
            orderNumber: $returnNumber,
            userId: $userId
        );
    }

    public function deductForPurchaseReturn(
        string $businessId,
        string $locationId,
        string $productId,
        float $quantity,
        float $unitCost,
        string $returnId,
        string $returnNumber,
        ?string $userId = null,
        ?string $materialId = null
    ): StockMovement {
        return $this->recordMovement(
            businessId: $businessId,
            locationId: $locationId,
            productId: $productId,
            movementType: StockMovement::TYPE_PURCHASE_RETURN,
            quantityChange: -abs($quantity),
            unitCost: $unitCost,
            referenceId: $returnId,
            referenceNumber: $returnNumber,
            notes: "Retur Pembelian #{$returnNumber}",
            userId: $userId,
            materialId: $materialId
        );
    }

    /**
     * Reconcile physical inventory count from Stock Opname.
     * Follows Rule 18, 19, 20: Stock Opname operates on Material and generates Stock Movement.
     */
    public function reconcileStockOpname(StockOpname $opname, User $reconciler): void
    {
        DB::transaction(function () use ($opname, $reconciler) {
            foreach ($opname->items as $item) {
                $diff = (float) $item->difference_quantity;
                if ($diff != 0.0) {
                    $this->recordMovement(
                        businessId: $opname->business_id,
                        locationId: $opname->location_id,
                        productId: $item->product_id,
                        movementType: StockMovement::TYPE_OPNAME,
                        quantityChange: $diff,
                        unitCost: (float) $item->unit_cost,
                        referenceId: $opname->id,
                        referenceNumber: $opname->opname_number,
                        notes: "Rekonsiliasi Stock Opname #{$opname->opname_number}",
                        userId: $reconciler->id,
                        materialId: $item->material_id
                    );
                }
            }

            $opname->update([
                'status' => StockOpname::STATUS_RECONCILED,
                'reconciled_by' => $reconciler->id,
                'reconciled_at' => now(),
            ]);
        });
    }

    /**
     * Transfer stock between two locations.
     */
    public function completeStockTransfer(StockTransfer $transfer, User $receiver): void
    {
        DB::transaction(function () use ($transfer, $receiver) {
            foreach ($transfer->items as $item) {
                $qty = (float) $item->quantity;
                $cost = (float) $item->unit_cost;

                // Out from source
                $this->recordMovement(
                    businessId: $transfer->business_id,
                    locationId: $transfer->source_location_id,
                    productId: $item->product_id,
                    movementType: StockMovement::TYPE_TRANSFER_OUT,
                    quantityChange: -$qty,
                    unitCost: $cost,
                    referenceId: $transfer->id,
                    referenceNumber: $transfer->transfer_number,
                    notes: "Transfer Keluar ke " . ($transfer->destinationLocation->name ?? 'Tujuan'),
                    userId: $receiver->id,
                    materialId: $item->material_id ?? null
                );

                // In to destination
                $this->recordMovement(
                    businessId: $transfer->business_id,
                    locationId: $transfer->destination_location_id,
                    productId: $item->product_id,
                    movementType: StockMovement::TYPE_TRANSFER_IN,
                    quantityChange: $qty,
                    unitCost: $cost,
                    referenceId: $transfer->id,
                    referenceNumber: $transfer->transfer_number,
                    notes: "Transfer Masuk dari " . ($transfer->sourceLocation->name ?? 'Asal'),
                    userId: $receiver->id,
                    materialId: $item->material_id ?? null
                );
            }

            $transfer->update([
                'status' => StockTransfer::STATUS_RECEIVED,
                'received_by' => $receiver->id,
                'received_at' => now(),
            ]);
        });
    }
}
