<?php

declare(strict_types=1);

namespace App\Domain\Material;

use App\Models\MaterialPrice;
use App\Models\Unit;
use InvalidArgumentException;

final class MaterialCostService
{
    public function __construct(private readonly UnitConversionService $unitConversionService = new UnitConversionService) {}

    /**
     * Calculate effective acquisition cost (Total Landed Cost).
     * Formula: Purchase Price + Shipping + Handling + Import - Discount.
     */
    public function calculateEffectiveAcquisitionCost(MaterialPrice $price): float
    {
        return (float) (
            $price->purchase_price
            + $price->shipping_cost
            + $price->handling_cost
            + $price->import_cost
            - $price->discount_amount
        );
    }

    /**
     * Calculate net usable quantity after applying yield % and waste %.
     *
     * @throws InvalidArgumentException
     */
    public function calculateUsableQuantity(float $purchaseQty, float $yieldPercentage = 100.0, float $wastePercentage = 0.0): float
    {
        if ($yieldPercentage <= 0.0) {
            throw new InvalidArgumentException('Yield percentage must be greater than 0.');
        }

        if ($wastePercentage < 0.0 || $wastePercentage >= 100.0) {
            throw new InvalidArgumentException('Waste percentage must be between 0% and 99.99%.');
        }

        $usableAfterYield = $purchaseQty * ($yieldPercentage / 100.0);
        $netUsable = $usableAfterYield * (1.0 - ($wastePercentage / 100.0));

        return max(0.0, $netUsable);
    }

    /**
     * Calculate effective unit cost taking into account landed cost, yield %, waste % and target unit.
     */
    public function calculateEffectiveUnitCost(MaterialPrice $price, ?Unit $targetUnit = null): float
    {
        $totalCost = $this->calculateEffectiveAcquisitionCost($price);
        $netUsablePerPurchaseUnit = $this->calculateUsableQuantity(1.0, (float) $price->yield_percentage, (float) $price->waste_percentage);

        if ($netUsablePerPurchaseUnit <= 0.0) {
            throw new InvalidArgumentException('Net usable quantity resulted in zero or invalid amount.');
        }

        $costPerPurchaseUnit = $totalCost / $netUsablePerPurchaseUnit;

        if ($targetUnit === null || $targetUnit->id === $price->purchase_unit_id) {
            return $costPerPurchaseUnit;
        }

        // Convert target unit to purchase unit
        // E.g. target unit = gram, purchase unit = kg -> 1 gram = 0.001 kg -> cost = costPerKg * 0.001
        $ratio = $this->unitConversionService->convert(1.0, $targetUnit, $price->purchaseUnit);

        return $costPerPurchaseUnit * $ratio;
    }

    /**
     * Get a detailed mathematical breakdown of material acquisition cost.
     *
     * @return array<string, mixed>
     */
    public function getMaterialCostBreakdown(MaterialPrice $price, ?Unit $targetUnit = null): array
    {
        $target = $targetUnit ?? $price->purchaseUnit;
        $totalAcquisitionCost = $this->calculateEffectiveAcquisitionCost($price);
        $effectiveUnitCost = $this->calculateEffectiveUnitCost($price, $target);

        return [
            'purchase_price' => (float) $price->purchase_price,
            'shipping_cost' => (float) $price->shipping_cost,
            'handling_cost' => (float) $price->handling_cost,
            'import_cost' => (float) $price->import_cost,
            'discount_amount' => (float) $price->discount_amount,
            'total_acquisition_cost' => $totalAcquisitionCost,
            'yield_percentage' => (float) $price->yield_percentage,
            'waste_percentage' => (float) $price->waste_percentage,
            'purchase_unit' => [
                'id' => $price->purchaseUnit?->id,
                'code' => $price->purchaseUnit?->code,
            ],
            'target_unit' => [
                'id' => $target?->id,
                'code' => $target?->code,
            ],
            'effective_cost_per_target_unit' => $effectiveUnitCost,
        ];
    }
}
