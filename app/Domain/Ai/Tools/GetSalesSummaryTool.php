<?php

declare(strict_types=1);

namespace App\Domain\Ai\Tools;

use App\Models\Business;
use App\Models\CommerceOrder;
use App\Models\Invoice;
use App\Models\PosOrder;
use App\Models\User;
use Carbon\Carbon;

final class GetSalesSummaryTool extends BaseAiTool
{
    public function getName(): string
    {
        return 'GetSalesSummary';
    }

    public function getDescription(): string
    {
        return 'Mengambil ringkasan data penjualan terpadu (POS, Faktur Invoice, dan Toko Online) untuk hari ini, 7 hari terakhir, dan 30 hari terakhir.';
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $today = Carbon::today();
        $sub7 = $today->copy()->subDays(7);
        $sub30 = $today->copy()->subDays(30);

        return [
            'today' => $this->aggregatePeriod($business, $today, $today),
            'last_7_days' => $this->aggregatePeriod($business, $sub7, $today),
            'last_30_days' => $this->aggregatePeriod($business, $sub30, $today),
        ];
    }

    /**
     * Aggregate sales across POS, Invoices, and CommerceOrders.
     *
     * @return array<string, mixed>
     */
    private function aggregatePeriod(Business $business, Carbon $from, Carbon $to): array
    {
        $fromStr = $from->toDateString();
        $toStr = $to->toDateString();

        // 1. POS Orders
        $posOrders = PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_COMPLETED)
            ->whereBetween('order_date', [$fromStr, $toStr])
            ->get();
        $posRevenue = (float) $posOrders->sum('total_amount');
        $posProfit = (float) $posOrders->sum('total_gross_profit');
        $posCount = $posOrders->count();

        // 2. Invoices (Accrual / Issued)
        $invoices = Invoice::where('business_id', $business->id)
            ->whereNotIn('status', [Invoice::STATUS_DRAFT, Invoice::STATUS_VOID])
            ->whereBetween('invoice_date', [$fromStr, $toStr])
            ->get();
        $invoiceRevenue = (float) $invoices->sum('total_amount');
        $invoicePaid = (float) $invoices->sum('paid_amount');
        $invoiceProfit = (float) $invoices->sum('total_gross_profit');
        $invoiceCount = $invoices->count();

        // 3. Online Store
        $onlineOrders = CommerceOrder::where('business_id', $business->id)
            ->whereIn('status', [
                CommerceOrder::STATUS_PAID,
                CommerceOrder::STATUS_PROCESSING,
                CommerceOrder::STATUS_READY,
                CommerceOrder::STATUS_FULFILLED,
                CommerceOrder::STATUS_COMPLETED,
            ])
            ->whereBetween('created_at', [$fromStr . ' 00:00:00', $toStr . ' 23:59:59'])
            ->get();
        $onlineRevenue = (float) $onlineOrders->sum('total_amount');
        $onlineCount = $onlineOrders->count();

        $totalRevenue = $posRevenue + $invoiceRevenue + $onlineRevenue;
        $totalGrossProfit = $posProfit + $invoiceProfit;
        $totalOrdersCount = $posCount + $invoiceCount + $onlineCount;
        $aov = $totalOrdersCount > 0 ? round($totalRevenue / $totalOrdersCount) : 0;
        $marginPct = $totalRevenue > 0 ? round(($totalGrossProfit / $totalRevenue) * 100, 1) : 0;

        return [
            'total_revenue' => $totalRevenue,
            'pos_revenue' => $posRevenue,
            'invoice_revenue' => $invoiceRevenue,
            'invoice_paid_revenue' => $invoicePaid,
            'online_revenue' => $onlineRevenue,
            'total_gross_profit' => $totalGrossProfit,
            'gross_margin_percent' => $marginPct,
            'orders_count' => $totalOrdersCount,
            'aov' => $aov,
        ];
    }
}
