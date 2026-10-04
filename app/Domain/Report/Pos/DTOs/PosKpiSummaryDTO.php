<?php

declare(strict_types=1);

namespace App\Domain\Report\Pos\DTOs;

final class PosKpiSummaryDTO
{
    public function __construct(
        public readonly float $grossSales,
        public readonly float $totalDiscount,
        public readonly float $orderDiscount,
        public readonly float $voucherDiscount,
        public readonly float $pointsDiscount,
        public readonly float $itemDiscount,
        public readonly float $grossRevenue,
        public readonly float $refundAmount,
        public readonly float $netSales,
        public readonly float $subtotal,
        public readonly float $taxAmount,
        public readonly float $serviceChargeAmount,
        public readonly float $roundingAmount,
        public readonly float $grandTotal,
        public readonly float $totalHpp,
        public readonly float $grossProfit,
        public readonly float $grossMarginPercent,
        public readonly int $totalOrders,
        public readonly float $totalItemsSold,
        public readonly float $averageOrderValue,
        public readonly float $averageItemsPerTransaction,
        public readonly float $averageSellingPrice,
        public readonly float $averageCostPrice,
        public readonly float $goodsRevenue,
        public readonly float $goodsQuantity,
        public readonly float $servicesRevenue,
        public readonly float $servicesQuantity,
        public readonly float $cashSales,
        public readonly float $nonCashSales,
        public readonly float $totalPayments,
        public readonly float $todayRevenue,
        public readonly int $todayOrders,
        public readonly ?float $prevNetSales = null,
        public readonly ?float $prevGrossProfit = null,
        public readonly ?int $prevOrders = null,
        public readonly ?float $salesGrowthPercent = null,
        public readonly ?float $profitGrowthPercent = null,
        public readonly ?float $ordersGrowthPercent = null
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'gross_sales' => $this->grossSales,
            'total_discount' => $this->totalDiscount,
            'order_discount' => $this->orderDiscount,
            'voucher_discount' => $this->voucherDiscount,
            'points_discount' => $this->pointsDiscount,
            'item_discount' => $this->itemDiscount,
            'gross_revenue' => $this->grossRevenue,
            'refund_amount' => $this->refundAmount,
            'net_sales' => $this->netSales,
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->taxAmount,
            'service_charge_amount' => $this->serviceChargeAmount,
            'rounding_amount' => $this->roundingAmount,
            'grand_total' => $this->grandTotal,
            'total_hpp' => $this->totalHpp,
            'gross_profit' => $this->grossProfit,
            'gross_margin_percent' => $this->grossMarginPercent,
            'total_orders' => $this->totalOrders,
            'total_items_sold' => $this->totalItemsSold,
            'average_order_value' => $this->averageOrderValue,
            'average_items_per_transaction' => $this->averageItemsPerTransaction,
            'average_selling_price' => $this->averageSellingPrice,
            'average_cost_price' => $this->averageCostPrice,
            'goods_revenue' => $this->goodsRevenue,
            'goods_quantity' => $this->goodsQuantity,
            'services_revenue' => $this->servicesRevenue,
            'services_quantity' => $this->servicesQuantity,
            'cash_sales' => $this->cashSales,
            'non_cash_sales' => $this->nonCashSales,
            'total_payments' => $this->totalPayments,
            'today_revenue' => $this->todayRevenue,
            'today_orders' => $this->todayOrders,
            'prev_net_sales' => $this->prevNetSales,
            'prev_gross_profit' => $this->prevGrossProfit,
            'prev_orders' => $this->prevOrders,
            'sales_growth_percent' => $this->salesGrowthPercent,
            'profit_growth_percent' => $this->profitGrowthPercent,
            'orders_growth_percent' => $this->ordersGrowthPercent,
        ];
    }
}
