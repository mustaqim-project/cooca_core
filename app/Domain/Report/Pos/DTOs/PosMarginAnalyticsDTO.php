<?php

declare(strict_types=1);

namespace App\Domain\Report\Pos\DTOs;

use Illuminate\Support\Collection;

final class PosMarginAnalyticsDTO
{
    /**
     * @param Collection<int, array{
     *     product_id: string,
     *     product_name: string,
     *     product_code: string,
     *     category_name: string,
     *     quantity_sold: float,
     *     gross_revenue: float,
     *     total_cogs: float,
     *     gross_profit: float,
     *     margin_percent: float
     * }> $topProfitableProducts
     * @param Collection<int, array{
     *     product_id: string,
     *     product_name: string,
     *     product_code: string,
     *     category_name: string,
     *     quantity_sold: float,
     *     gross_revenue: float,
     *     total_cogs: float,
     *     gross_profit: float,
     *     margin_percent: float
     * }> $highestMarginProducts
     * @param Collection<int, array{
     *     product_id: string,
     *     product_name: string,
     *     product_code: string,
     *     category_name: string,
     *     quantity_sold: float,
     *     gross_revenue: float,
     *     total_cogs: float,
     *     loss_amount: float,
     *     margin_percent: float
     * }> $lossLeaderProducts
     * @param Collection<int, array{
     *     product_id: string,
     *     product_name: string,
     *     product_code: string,
     *     category_name: string,
     *     quantity_sold: float,
     *     gross_revenue: float,
     *     unit_price: float
     * }> $zeroCogsWarningProducts
     * @param array{
     *     high_margin: array{label: string, count: int, revenue: float, profit: float, share_percent: float},
     *     medium_margin: array{label: string, count: int, revenue: float, profit: float, share_percent: float},
     *     low_margin: array{label: string, count: int, revenue: float, profit: float, share_percent: float},
     *     negative_margin: array{label: string, count: int, revenue: float, profit: float, share_percent: float}
     * } $marginTiers
     * @param Collection<int, array{
     *     category_id: ?string,
     *     category_name: string,
     *     total_revenue: float,
     *     total_cogs: float,
     *     gross_profit: float,
     *     margin_percent: float,
     *     profit_contribution_percent: float
     * }> $categoryProfitability
     */
    public function __construct(
        public readonly float $overallGrossMarginPct,
        public readonly float $totalGrossProfit,
        public readonly float $totalCogs,
        public readonly float $totalNetSales,
        public readonly int $zeroCogsItemsCount,
        public readonly float $zeroCogsRevenue,
        public readonly Collection $topProfitableProducts,
        public readonly Collection $highestMarginProducts,
        public readonly Collection $lossLeaderProducts,
        public readonly Collection $zeroCogsWarningProducts,
        public readonly array $marginTiers,
        public readonly Collection $categoryProfitability
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'overall_gross_margin_pct' => $this->overallGrossMarginPct,
            'total_gross_profit' => $this->totalGrossProfit,
            'total_cogs' => $this->totalCogs,
            'total_net_sales' => $this->totalNetSales,
            'zero_cogs_items_count' => $this->zeroCogsItemsCount,
            'zero_cogs_revenue' => $this->zeroCogsRevenue,
            'top_profitable_products' => $this->topProfitableProducts->toArray(),
            'highest_margin_products' => $this->highestMarginProducts->toArray(),
            'loss_leader_products' => $this->lossLeaderProducts->toArray(),
            'zero_cogs_warning_products' => $this->zeroCogsWarningProducts->toArray(),
            'margin_tiers' => $this->marginTiers,
            'category_profitability' => $this->categoryProfitability->toArray(),
        ];
    }
}
