<?php

declare(strict_types=1);

namespace App\Domain\Inventory;

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

final class StockService
{
    /**
     * Get or create stock record for a product at a specific location.
     */
    public function getOrCreateStock(string $businessId, string $locationId, string $productId): InventoryStock
    {
        return InventoryStock::firstOrCreate(
            [
                'business_id' => $businessId,
                'location_id' => $locationId,
                'product_id' => $productId,
            ],
            [
                'quantity' => 0.0,
                'reserved_quantity' => 0.0,
                'last_cost' => 0.0,
            ]
        );
    }

    /**
     * Record a stock movement and update real-time stock balance.
     */
    public function recordMovement(
        string $businessId,
        string $locationId,
        string $productId,
        string $movementType,
        float $quantityChange,
        float $unitCost = 0.0,
        ?string $referenceId = null,
        ?string $referenceNumber = null,
        ?string $batchNumber = null,
        ?string $expiryDate = null,
        ?string $notes = null,
        ?string $userId = null
    ): StockMovement {
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
            $userId
        ) {
            $stock = $this->getOrCreateStock($businessId, $locationId, $productId);
            $newQuantity = (float) $stock->quantity + $quantityChange;

            $stock->quantity = $newQuantity;
            if ($unitCost > 0) {
                $stock->last_cost = $unitCost;
            }
            $stock->save();

            return StockMovement::create([
                'business_id' => $businessId,
                'location_id' => $locationId,
                'product_id' => $productId,
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
     * Deduct stock for a POS order item.
     */
    public function deductForPosSale(
        string $businessId,
        string $locationId,
        string $productId,
        float $quantity,
        float $unitCost,
        string $orderId,
        string $orderNumber,
        ?string $userId = null
    ): StockMovement {
        return $this->recordMovement(
            businessId: $businessId,
            locationId: $locationId,
            productId: $productId,
            movementType: StockMovement::TYPE_POS_SALE,
            quantityChange: -abs($quantity),
            unitCost: $unitCost,
            referenceId: $orderId,
            referenceNumber: $orderNumber,
            notes: "Penjualan Kasir POS #{$orderNumber}",
            userId: $userId
        );
    }

    /**
     * Restore stock for a POS order refund / return.
     */
    public function restoreForPosRefund(
        string $businessId,
        string $locationId,
        string $productId,
        float $quantity,
        float $unitCost,
        string $orderId,
        string $orderNumber,
        ?string $userId = null
    ): StockMovement {
        return $this->recordMovement(
            businessId: $businessId,
            locationId: $locationId,
            productId: $productId,
            movementType: StockMovement::TYPE_POS_REFUND,
            quantityChange: abs($quantity),
            unitCost: $unitCost,
            referenceId: $orderId,
            referenceNumber: $orderNumber,
            notes: "Pengembalian / Retur POS #{$orderNumber}",
            userId: $userId
        );
    }

    /**
     * Reconcile physical inventory count from Stock Opname.
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
                        userId: $reconciler->id
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
                    userId: $receiver->id
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
                    userId: $receiver->id
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
