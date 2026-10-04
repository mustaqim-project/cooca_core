<?php

declare(strict_types=1);

namespace App\Domain\Report\Pos\DTOs;

use Illuminate\Support\Collection;

final class PosDiscountAnalyticsDTO
{
    /**
     * @param Collection<int, array{
     *     cashier_id: string,
     *     cashier_name: string,
     *     total_orders: int,
     *     discounted_orders_count: int,
     *     total_discount_given: float,
     *     discount_to_sales_ratio: float,
     *     manual_discount_amount: float
     * }> $cashierDiscountRankings
     * @param Collection<int, array{
     *     product_id: string,
     *     product_name: string,
     *     product_code: string,
     *     gross_amount: float,
     *     total_discount: float,
     *     discount_percent_of_gross: float,
     *     quantity_discounted: float
     * }> $topDiscountedProducts
     * @param Collection<int, array{
     *     voucher_code: string,
     *     usage_count: int,
     *     total_discount_amount: float,
     *     total_sales_generated: float
     * }> $voucherUsageSummary
     */
    public function __construct(
        public readonly float $totalGrossSales,
        public readonly float $totalDiscountAmount,
        public readonly float $overallDiscountRatePct,
        public readonly float $itemDiscountAmount,
        public readonly float $orderDiscountAmount,
        public readonly float $voucherDiscountAmount,
        public readonly float $pointsDiscountAmount,
        public readonly Collection $cashierDiscountRankings,
        public readonly Collection $topDiscountedProducts,
        public readonly Collection $voucherUsageSummary
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'total_gross_sales' => $this->totalGrossSales,
            'total_discount_amount' => $this->totalDiscountAmount,
            'overall_discount_rate_pct' => $this->overallDiscountRatePct,
            'item_discount_amount' => $this->itemDiscountAmount,
            'order_discount_amount' => $this->orderDiscountAmount,
            'voucher_discount_amount' => $this->voucherDiscountAmount,
            'points_discount_amount' => $this->pointsDiscountAmount,
            'cashier_discount_rankings' => $this->cashierDiscountRankings->toArray(),
            'top_discounted_products' => $this->topDiscountedProducts->toArray(),
            'voucher_usage_summary' => $this->voucherUsageSummary->toArray(),
        ];
    }
}
