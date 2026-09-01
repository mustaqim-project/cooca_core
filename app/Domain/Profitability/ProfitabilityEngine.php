<?php

declare(strict_types=1);

namespace App\Domain\Profitability;

use InvalidArgumentException;

final class ProfitabilityEngine
{
    /**
     * Calculate Gross Profit nominal (§25 Blueprint).
     * Formula: Revenue - COGS (HPP).
     */
    public function grossProfit(float $revenue, float $cogs): float
    {
        return $revenue - $cogs;
    }

    /**
     * Calculate Gross Margin Percentage (§25 Blueprint).
     * Formula: (Gross Profit / Revenue) * 100.
     */
    public function grossMarginPercentage(float $revenue, float $cogs): float
    {
        if ($revenue <= 0.0) {
            return 0.0;
        }

        return (($revenue - $cogs) / $revenue) * 100.0;
    }

    /**
     * Calculate Unit Contribution Margin (§25 Blueprint).
     * Formula: Selling Price - Variable Cost per Unit.
     */
    public function contributionMargin(float $sellingPrice, float $variableCostPerUnit): float
    {
        return $sellingPrice - $variableCostPerUnit;
    }

    /**
     * Calculate Contribution Margin Ratio (%).
     * Formula: (Contribution Margin / Selling Price) * 100.
     */
    public function contributionMarginRatio(float $sellingPrice, float $variableCostPerUnit): float
    {
        if ($sellingPrice <= 0.0) {
            return 0.0;
        }

        return (($sellingPrice - $variableCostPerUnit) / $sellingPrice) * 100.0;
    }

    /**
     * Analyze complete product profitability summary.
     *
     * @return array{
     *     selling_price: float,
     *     hpp_per_unit: float,
     *     variable_cost_per_unit: float,
     *     units_sold: float,
     *     total_revenue: float,
     *     total_cogs: float,
     *     gross_profit: float,
     *     gross_margin_percentage: float,
     *     unit_contribution_margin: float,
     *     contribution_margin_ratio: float,
     *     total_contribution_margin: float
     * }
     */
    public function analyze(float $sellingPrice, float $hppPerUnit, float $variableCostPerUnit, float $unitsSold = 1.0): array
    {
        if ($unitsSold < 0.0) {
            throw new InvalidArgumentException('Units sold cannot be negative.');
        }

        $totalRevenue = $sellingPrice * $unitsSold;
        $totalCogs = $hppPerUnit * $unitsSold;
        $grossProfit = $this->grossProfit($totalRevenue, $totalCogs);
        $grossMargin = $this->grossMarginPercentage($totalRevenue, $totalCogs);
        $unitCm = $this->contributionMargin($sellingPrice, $variableCostPerUnit);
        $cmRatio = $this->contributionMarginRatio($sellingPrice, $variableCostPerUnit);
        $totalCm = $unitCm * $unitsSold;

        return [
            'selling_price' => $sellingPrice,
            'hpp_per_unit' => $hppPerUnit,
            'variable_cost_per_unit' => $variableCostPerUnit,
            'units_sold' => $unitsSold,
            'total_revenue' => $totalRevenue,
            'total_cogs' => $totalCogs,
            'gross_profit' => $grossProfit,
            'gross_margin_percentage' => $grossMargin,
            'unit_contribution_margin' => $unitCm,
            'contribution_margin_ratio' => $cmRatio,
            'total_contribution_margin' => $totalCm,
        ];
    }
}
