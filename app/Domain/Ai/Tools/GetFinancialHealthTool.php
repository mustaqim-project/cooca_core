<?php

declare(strict_types=1);

namespace App\Domain\Ai\Tools;

use App\Domain\Report\FinancialReportService;
use App\Models\Business;
use App\Models\Invoice;
use App\Models\User;
use Carbon\Carbon;

final class GetFinancialHealthTool extends BaseAiTool
{
    public function __construct(
        private readonly FinancialReportService $financialReportService = new FinancialReportService()
    ) {}

    public function getName(): string
    {
        return 'GetFinancialHealth';
    }

    public function getDescription(): string
    {
        return 'Mengevaluasi kesehatan keuangan bisnis (total omzet POS dan faktur, pengeluaran kas, saldo kas berjalan, margin laba kotor, dan laba bersih resmi).';
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $today = Carbon::today();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfDay = $today->copy()->endOfDay();

        // 1. Ambil Laporan Laba Rugi Resmi (Single Source of Truth ERP)
        $incomeStatement = $this->financialReportService->getIncomeStatement($business, $startOfMonth, $endOfDay);

        // 2. Data Piutang & Kas Masuk Aktual (Cash Basis)
        $unpaidInvoices = (float) Invoice::where('business_id', $business->id)
            ->whereIn('status', [Invoice::STATUS_UNPAID, Invoice::STATUS_SENT, Invoice::STATUS_PARTIALLY_PAID, Invoice::STATUS_OVERDUE])
            ->sum('balance_due');

        $paidInvoicesThisMonth = (float) Invoice::where('business_id', $business->id)
            ->whereIn('status', [Invoice::STATUS_PAID, Invoice::STATUS_PARTIALLY_PAID])
            ->whereDate('invoice_date', '>=', $startOfMonth)
            ->sum('paid_amount');

        $posCashSalesThisMonth = (float) ($incomeStatement['revenues']['pos_net_sales'] ?? 0);
        $cashCollectedThisMonth = $posCashSalesThisMonth + $paidInvoicesThisMonth;

        return [
            'period' => $startOfMonth->translatedFormat('F Y'),
            'accounting_basis' => 'Standar Akuntansi Akrual (POS + Faktur B2B + Beban Operasional)',
            'month_to_date_revenue' => (float) ($incomeStatement['revenues']['net_sales'] ?? 0),
            'pos_revenue' => (float) ($incomeStatement['revenues']['pos_net_sales'] ?? 0),
            'invoice_revenue' => (float) ($incomeStatement['revenues']['invoice_net_sales'] ?? 0),
            'online_revenue' => (float) ($incomeStatement['revenues']['online_net_sales'] ?? 0),
            'month_to_date_cogs' => (float) ($incomeStatement['cogs']['total_cogs'] ?? 0),
            'month_to_date_gross_profit' => (float) ($incomeStatement['gross_profit']['amount'] ?? 0),
            'gross_margin_percent' => (float) ($incomeStatement['gross_profit']['margin'] ?? 0),
            'month_to_date_expenses' => (float) ($incomeStatement['expenses']['total'] ?? 0),
            'expense_categories' => $incomeStatement['expenses']['by_category'] ?? [],
            'estimated_operating_profit' => (float) ($incomeStatement['net_profit']['amount'] ?? 0),
            'operating_margin_percent' => (float) ($incomeStatement['net_profit']['margin'] ?? 0),
            'unpaid_receivables_amount' => $unpaidInvoices,
            'cash_basis_collected_revenue' => $cashCollectedThisMonth,
        ];
    }
}
