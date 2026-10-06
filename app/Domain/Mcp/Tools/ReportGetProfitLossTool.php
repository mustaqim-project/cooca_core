<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Domain\Report\FinancialReportService;
use App\Models\Business;
use App\Models\User;
use Carbon\Carbon;
use InvalidArgumentException;

final class ReportGetProfitLossTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'report_get_profit_loss';
    }

    public function getDescription(): string
    {
        return 'Menghitung ringkasan laba rugi bisnis: total omzet, HPP (COGS), laba kotor, rincian biaya operasional, dan laba bersih (Net Profit).';
    }

    public function getRequiredAbility(): string
    {
        return 'mcp:reports:read';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'start_date' => [
                    'type' => 'string',
                    'format' => 'date',
                    'description' => 'Tanggal awal periode analisis (YYYY-MM-DD).',
                ],
                'end_date' => [
                    'type' => 'string',
                    'format' => 'date',
                    'description' => 'Tanggal akhir periode analisis (YYYY-MM-DD).',
                ],
            ],
            'required' => ['start_date', 'end_date'],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        if (empty($arguments['start_date']) || empty($arguments['end_date'])) {
            throw new InvalidArgumentException('start_date dan end_date wajib diisi.');
        }

        $startDate = Carbon::parse((string) $arguments['start_date'])->startOfDay();
        $endDate = Carbon::parse((string) $arguments['end_date'])->endOfDay();

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        $reportService = app(FinancialReportService::class);
        $statement = $reportService->getIncomeStatement($business, $startDate, $endDate);

        $grossRevenue = (float) ($statement['total_revenue'] ?? $statement['revenue'] ?? 0);
        $cogs = (float) ($statement['total_cogs'] ?? $statement['cogs'] ?? 0);
        $grossProfit = (float) ($statement['gross_profit'] ?? ($grossRevenue - $cogs));
        $totalExpenses = (float) ($statement['total_expenses'] ?? 0);
        $netProfit = (float) ($statement['net_profit'] ?? ($grossProfit - $totalExpenses));

        $netMargin = $grossRevenue > 0 ? round(($netProfit / $grossRevenue) * 100, 2) : 0;

        return [
            'status' => 'success',
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'financial_summary' => [
                'gross_revenue' => $grossRevenue,
                'formatted_gross_revenue' => 'Rp ' . number_format($grossRevenue, 0, ',', '.'),
                'cogs' => $cogs,
                'formatted_cogs' => 'Rp ' . number_format($cogs, 0, ',', '.'),
                'gross_profit' => $grossProfit,
                'formatted_gross_profit' => 'Rp ' . number_format($grossProfit, 0, ',', '.'),
                'total_expenses' => $totalExpenses,
                'formatted_total_expenses' => 'Rp ' . number_format($totalExpenses, 0, ',', '.'),
                'net_profit' => $netProfit,
                'formatted_net_profit' => 'Rp ' . number_format($netProfit, 0, ',', '.'),
                'net_profit_margin_percent' => "{$netMargin}%",
            ],
            'raw_breakdown' => $statement,
        ];
    }
}
