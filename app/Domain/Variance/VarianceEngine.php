<?php

declare(strict_types=1);

namespace App\Domain\Variance;

use App\Models\CostVariance;

final class VarianceEngine
{
    /**
     * Material Price Variance (§29 Blueprint).
     * Formula: (Actual Price - Standard Price) * Actual Quantity.
     *
     * @return array{amount: float, percentage: float, nature: string}
     */
    public function materialPriceVariance(float $actualPrice, float $standardPrice, float $actualQuantity): array
    {
        $diff = ($actualPrice - $standardPrice) * $actualQuantity;
        $stdTotal = $standardPrice * $actualQuantity;
        $pct = $stdTotal > 0 ? ($diff / $stdTotal) * 100.0 : 0.0;

        return [
            'amount' => $diff,
            'percentage' => $pct,
            'nature' => $diff > 0 ? CostVariance::NATURE_UNFAVORABLE : ($diff < 0 ? CostVariance::NATURE_FAVORABLE : CostVariance::NATURE_NEUTRAL),
        ];
    }

    /**
     * Material Quantity / Usage Variance (§29 Blueprint).
     * Formula: (Actual Quantity - Standard Quantity) * Standard Price.
     *
     * @return array{amount: float, percentage: float, nature: string}
     */
    public function materialQuantityVariance(float $actualQuantity, float $standardQuantity, float $standardPrice): array
    {
        $diff = ($actualQuantity - $standardQuantity) * $standardPrice;
        $stdTotal = $standardQuantity * $standardPrice;
        $pct = $stdTotal > 0 ? ($diff / $stdTotal) * 100.0 : 0.0;

        return [
            'amount' => $diff,
            'percentage' => $pct,
            'nature' => $diff > 0 ? CostVariance::NATURE_UNFAVORABLE : ($diff < 0 ? CostVariance::NATURE_FAVORABLE : CostVariance::NATURE_NEUTRAL),
        ];
    }

    /**
     * Labor Rate Variance (§29 Blueprint).
     * Formula: (Actual Rate - Standard Rate) * Actual Hours.
     *
     * @return array{amount: float, percentage: float, nature: string}
     */
    public function laborRateVariance(float $actualRate, float $standardRate, float $actualHours): array
    {
        $diff = ($actualRate - $standardRate) * $actualHours;
        $stdTotal = $standardRate * $actualHours;
        $pct = $stdTotal > 0 ? ($diff / $stdTotal) * 100.0 : 0.0;

        return [
            'amount' => $diff,
            'percentage' => $pct,
            'nature' => $diff > 0 ? CostVariance::NATURE_UNFAVORABLE : ($diff < 0 ? CostVariance::NATURE_FAVORABLE : CostVariance::NATURE_NEUTRAL),
        ];
    }

    /**
     * Labor Efficiency / Hours Variance (§29 Blueprint).
     * Formula: (Actual Hours - Standard Hours) * Standard Rate.
     *
     * @return array{amount: float, percentage: float, nature: string}
     */
    public function laborEfficiencyVariance(float $actualHours, float $standardHours, float $standardRate): array
    {
        $diff = ($actualHours - $standardHours) * $standardRate;
        $stdTotal = $standardHours * $standardRate;
        $pct = $stdTotal > 0 ? ($diff / $stdTotal) * 100.0 : 0.0;

        return [
            'amount' => $diff,
            'percentage' => $pct,
            'nature' => $diff > 0 ? CostVariance::NATURE_UNFAVORABLE : ($diff < 0 ? CostVariance::NATURE_FAVORABLE : CostVariance::NATURE_NEUTRAL),
        ];
    }

    /**
     * Overhead Spending Variance.
     * Formula: Actual Overhead - Standard Overhead.
     *
     * @return array{amount: float, percentage: float, nature: string}
     */
    public function overheadSpendingVariance(float $actualOverhead, float $standardOverhead): array
    {
        $diff = $actualOverhead - $standardOverhead;
        $pct = $standardOverhead > 0 ? ($diff / $standardOverhead) * 100.0 : 0.0;

        return [
            'amount' => $diff,
            'percentage' => $pct,
            'nature' => $diff > 0 ? CostVariance::NATURE_UNFAVORABLE : ($diff < 0 ? CostVariance::NATURE_FAVORABLE : CostVariance::NATURE_NEUTRAL),
        ];
    }

    /**
     * Rank root-cause variance contributors by absolute magnitude descending (§87).
     *
     * @param  array<string, array{amount: float, percentage: float, nature: string}>  $variances
     * @return array<int, array{name: string, amount: float, percentage: float, nature: string}>
     */
    public function rankContributors(array $variances): array
    {
        $list = [];
        foreach ($variances as $name => $data) {
            $list[] = [
                'name' => $name,
                'amount' => $data['amount'],
                'percentage' => $data['percentage'],
                'nature' => $data['nature'],
                'abs_amount' => abs($data['amount']),
            ];
        }

        usort($list, fn ($a, $b) => $b['abs_amount'] <=> $a['abs_amount']);

        return array_map(function ($item) {
            unset($item['abs_amount']);

            return $item;
        }, $list);
    }
}
