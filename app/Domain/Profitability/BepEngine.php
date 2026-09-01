<?php

declare(strict_types=1);

namespace App\Domain\Profitability;

use InvalidArgumentException;

final class BepEngine
{
    /**
     * Calculate Break-Even Point in Units (§24 Blueprint).
     * Formula: Total Fixed Costs / (Selling Price - Variable Cost per Unit).
     */
    public function unitBep(float $totalFixedCost, float $sellingPrice, float $variableCostPerUnit): float
    {
        $unitContributionMargin = $sellingPrice - $variableCostPerUnit;

        if ($unitContributionMargin <= 0.0) {
            throw new InvalidArgumentException('Selling price must be greater than variable cost per unit to achieve break-even.');
        }

        return (float) ($totalFixedCost / $unitContributionMargin);
    }

    /**
     * Calculate Break-Even Point in Revenue (§24 Blueprint).
     * Formula: Total Fixed Costs / Contribution Margin Ratio.
     */
    public function revenueBep(float $totalFixedCost, float $sellingPrice, float $variableCostPerUnit): float
    {
        $unitContributionMargin = $sellingPrice - $variableCostPerUnit;

        if ($unitContributionMargin <= 0.0 || $sellingPrice <= 0.0) {
            throw new InvalidArgumentException('Selling price and contribution margin must be positive.');
        }

        $cmRatio = $unitContributionMargin / $sellingPrice;

        return (float) ($totalFixedCost / $cmRatio);
    }

    /**
     * Calculate Units & Revenue required to achieve a Target Profit (§24 Blueprint).
     *
     * @return array{
     *     target_units: float,
     *     target_revenue: float
     * }
     */
    public function targetProfitBep(float $totalFixedCost, float $targetProfit, float $sellingPrice, float $variableCostPerUnit): array
    {
        $unitCm = $sellingPrice - $variableCostPerUnit;
        if ($unitCm <= 0.0 || $sellingPrice <= 0.0) {
            throw new InvalidArgumentException('Contribution margin must be positive.');
        }

        $totalNeeded = $totalFixedCost + $targetProfit;
        $cmRatio = $unitCm / $sellingPrice;

        return [
            'target_units' => (float) ($totalNeeded / $unitCm),
            'target_revenue' => (float) ($totalNeeded / $cmRatio),
        ];
    }

    /**
     * Comprehensive BEP analysis.
     *
     * @return array{
     *     total_fixed_cost: float,
     *     selling_price: float,
     *     variable_cost_per_unit: float,
     *     unit_contribution_margin: float,
     *     contribution_margin_ratio: float,
     *     bep_units: float,
     *     bep_revenue: float,
     *     safety_margin_units?: float,
     *     safety_margin_revenue?: float
     * }
     */
    public function analyze(float $totalFixedCost, float $sellingPrice, float $variableCostPerUnit, ?float $expectedSalesUnits = null): array
    {
        $bepUnits = $this->unitBep($totalFixedCost, $sellingPrice, $variableCostPerUnit);
        $bepRevenue = $this->revenueBep($totalFixedCost, $sellingPrice, $variableCostPerUnit);
        $unitCm = $sellingPrice - $variableCostPerUnit;
        $cmRatio = ($unitCm / $sellingPrice) * 100.0;

        $result = [
            'total_fixed_cost' => $totalFixedCost,
            'selling_price' => $sellingPrice,
            'variable_cost_per_unit' => $variableCostPerUnit,
            'unit_contribution_margin' => $unitCm,
            'contribution_margin_ratio' => $cmRatio,
            'bep_units' => $bepUnits,
            'bep_revenue' => $bepRevenue,
        ];

        if ($expectedSalesUnits !== null) {
            $expectedRevenue = $expectedSalesUnits * $sellingPrice;
            $result['expected_sales_units'] = $expectedSalesUnits;
            $result['expected_sales_revenue'] = $expectedRevenue;
            $result['safety_margin_units'] = max(0.0, $expectedSalesUnits - $bepUnits);
            $result['safety_margin_revenue'] = max(0.0, $expectedRevenue - $bepRevenue);
        }

        return $result;
    }
}
