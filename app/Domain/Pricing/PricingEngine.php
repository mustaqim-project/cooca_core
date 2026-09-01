<?php

declare(strict_types=1);

namespace App\Domain\Pricing;

use App\Domain\Calculation\RoundingService;
use App\Models\Fee;
use InvalidArgumentException;

final class PricingEngine
{
    public function __construct(private readonly RoundingService $roundingService = new RoundingService) {}

    /**
     * Calculate selling price from Markup Percentage (§22 Blueprint).
     * Formula: Selling Price = HPP * (1 + Markup%).
     *
     * @return array{
     *     hpp: float,
     *     strategy: string,
     *     markup_percentage: float,
     *     selling_price: float,
     *     selling_price_rounded: float,
     *     gross_profit: float,
     *     margin_percentage: float
     * }
     */
    public function fromMarkup(float $hpp, float $markupPercentage): array
    {
        if ($hpp < 0.0) {
            throw new InvalidArgumentException('HPP must be non-negative.');
        }

        $sellingPrice = $hpp * (1.0 + ($markupPercentage / 100.0));
        $grossProfit = $sellingPrice - $hpp;
        $marginPercentage = $sellingPrice > 0.0 ? ($grossProfit / $sellingPrice) * 100.0 : 0.0;
        $rounded = $this->roundingService->apply($sellingPrice);

        return [
            'hpp' => $hpp,
            'strategy' => 'markup',
            'markup_percentage' => $markupPercentage,
            'selling_price' => $sellingPrice,
            'selling_price_rounded' => $rounded,
            'gross_profit' => $grossProfit,
            'margin_percentage' => $marginPercentage,
        ];
    }

    /**
     * Calculate selling price from Margin Percentage (§22 Blueprint).
     * Formula: Selling Price = HPP / (1 - Margin%).
     *
     * @return array{
     *     hpp: float,
     *     strategy: string,
     *     margin_percentage: float,
     *     selling_price: float,
     *     selling_price_rounded: float,
     *     gross_profit: float,
     *     markup_percentage: float
     * }
     */
    public function fromMargin(float $hpp, float $marginPercentage): array
    {
        if ($hpp < 0.0) {
            throw new InvalidArgumentException('HPP must be non-negative.');
        }

        if ($marginPercentage >= 100.0) {
            throw new InvalidArgumentException('Margin percentage cannot be 100% or greater.');
        }

        $sellingPrice = $hpp / (1.0 - ($marginPercentage / 100.0));
        $grossProfit = $sellingPrice - $hpp;
        $markupPercentage = $hpp > 0.0 ? ($grossProfit / $hpp) * 100.0 : 0.0;
        $rounded = $this->roundingService->apply($sellingPrice);

        return [
            'hpp' => $hpp,
            'strategy' => 'margin',
            'margin_percentage' => $marginPercentage,
            'selling_price' => $sellingPrice,
            'selling_price_rounded' => $rounded,
            'gross_profit' => $grossProfit,
            'markup_percentage' => $markupPercentage,
        ];
    }

    /**
     * Calculate selling price from Target Profit Nominal (§22 Blueprint).
     * Formula: Selling Price = HPP + Target Profit.
     *
     * @return array{
     *     hpp: float,
     *     strategy: string,
     *     target_profit_amount: float,
     *     selling_price: float,
     *     selling_price_rounded: float,
     *     gross_profit: float,
     *     margin_percentage: float,
     *     markup_percentage: float
     * }
     */
    public function fromTargetProfit(float $hpp, float $targetProfitAmount): array
    {
        if ($hpp < 0.0) {
            throw new InvalidArgumentException('HPP must be non-negative.');
        }

        $sellingPrice = $hpp + $targetProfitAmount;
        $marginPercentage = $sellingPrice > 0.0 ? ($targetProfitAmount / $sellingPrice) * 100.0 : 0.0;
        $markupPercentage = $hpp > 0.0 ? ($targetProfitAmount / $hpp) * 100.0 : 0.0;
        $rounded = $this->roundingService->apply($sellingPrice);

        return [
            'hpp' => $hpp,
            'strategy' => 'target_profit',
            'target_profit_amount' => $targetProfitAmount,
            'selling_price' => $sellingPrice,
            'selling_price_rounded' => $rounded,
            'gross_profit' => $targetProfitAmount,
            'margin_percentage' => $marginPercentage,
            'markup_percentage' => $markupPercentage,
        ];
    }

    /**
     * Calculate net revenue and net profit after selling price deductions (§21 Blueprint).
     *
     * @param  array<int, Fee>  $priceDeductionFees
     * @return array{
     *     selling_price: float,
     *     hpp: float,
     *     total_deductions: float,
     *     deductions_breakdown: array<int, mixed>,
     *     net_revenue: float,
     *     net_profit: float,
     *     net_margin_percentage: float
     * }
     */
    public function netRevenue(float $sellingPrice, float $hpp, array $priceDeductionFees = []): array
    {
        $totalDeductions = 0.0;
        $breakdown = [];

        foreach ($priceDeductionFees as $fee) {
            $deduction = 0.0;
            if ($fee->fee_type === Fee::FEE_TYPE_PERCENTAGE) {
                $deduction = ($fee->fee_value / 100.0) * $sellingPrice;
            } else {
                $deduction = $fee->fee_value;
            }

            $totalDeductions += $deduction;
            $breakdown[] = [
                'fee_id' => $fee->id,
                'name' => $fee->name,
                'type' => $fee->type,
                'fee_type' => $fee->fee_type,
                'fee_value' => $fee->fee_value,
                'deduction_amount' => $deduction,
            ];
        }

        $netRevenue = $sellingPrice - $totalDeductions;
        $netProfit = $netRevenue - $hpp;
        $netMarginPercentage = $netRevenue > 0.0 ? ($netProfit / $netRevenue) * 100.0 : 0.0;

        return [
            'selling_price' => $sellingPrice,
            'hpp' => $hpp,
            'total_deductions' => $totalDeductions,
            'deductions_breakdown' => $breakdown,
            'net_revenue' => $netRevenue,
            'net_profit' => $netProfit,
            'net_margin_percentage' => $netMarginPercentage,
        ];
    }
}
