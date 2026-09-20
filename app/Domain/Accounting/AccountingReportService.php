<?php

declare(strict_types=1);

namespace App\Domain\Accounting;

use App\Models\Business;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class AccountingReportService
{
    public function __construct(
        private readonly AutoJournalService $journalService = new AutoJournalService
    ) {}

    /**
     * Laporan Neraca Keuangan resmi (Balance Sheet - SAK EMKM).
     *
     * @return array<string, mixed>
     */
    public function getBalanceSheet(Business $business, ?Carbon $asOfDate = null): array
    {
        $asOfDate = $asOfDate ? $asOfDate->copy()->endOfDay() : now()->endOfDay();
        $this->journalService->ensureStandardAccounts($business);

        $accounts = ChartOfAccount::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        // Ambil akumulasi saldo mutasi sampai dengan tanggal cut-off
        $cutoff = $asOfDate->toDateString() . ' 23:59:59';
        $lineSums = JournalEntryLine::whereHas('entry', function ($q) use ($business, $cutoff) {
            $q->where('business_id', $business->id)
                ->where('entry_date', '<=', $cutoff);
        })
            ->select('account_id', 'type', DB::raw('SUM(amount) as total'))
            ->groupBy('account_id', 'type')
            ->get()
            ->groupBy('account_id');

        $accountBalances = [];
        foreach ($accounts as $account) {
            $sums = $lineSums->get($account->id);
            $debit = 0.0;
            $credit = 0.0;
            if ($sums) {
                foreach ($sums as $s) {
                    if ($s->type === JournalEntryLine::TYPE_DEBIT) {
                        $debit += (float) $s->total;
                    } elseif ($s->type === JournalEntryLine::TYPE_CREDIT) {
                        $credit += (float) $s->total;
                    }
                }
            }

            $balance = $account->normal_balance === 'debit'
                ? ($debit - $credit)
                : ($credit - $debit);

            $accountBalances[$account->id] = [
                'account' => $account,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $balance,
            ];
        }

        // Klasifikasi Akun
        $currentAssets = [];
        $nonCurrentAssets = [];
        $currentLiabilities = [];
        $longTermLiabilities = [];
        $equityAccounts = [];

        $totalRevenue = 0.0;
        $totalCogs = 0.0;
        $totalExpenses = 0.0;

        foreach ($accountBalances as $item) {
            /** @var ChartOfAccount $acc */
            $acc = $item['account'];
            $bal = (float) $item['balance'];

            switch ($acc->type) {
                case ChartOfAccount::TYPE_ASSET:
                    // Akun kas, bank, piutang, persediaan atau kode 1-1xxx diklasifikasikan sebagai Aset Lancar
                    if (str_starts_with($acc->code, '1-1') || str_contains(strtolower($acc->name), 'kas') || str_contains(strtolower($acc->name), 'bank') || str_contains(strtolower($acc->name), 'piutang') || str_contains(strtolower($acc->name), 'persediaan')) {
                        $currentAssets[] = ['code' => $acc->code, 'name' => $acc->name, 'balance' => $bal, 'id' => $acc->id];
                    } else {
                        $nonCurrentAssets[] = ['code' => $acc->code, 'name' => $acc->name, 'balance' => $bal, 'id' => $acc->id];
                    }
                    break;

                case ChartOfAccount::TYPE_LIABILITY:
                    if (str_starts_with($acc->code, '2-1') || str_starts_with($acc->code, '2-2') || ! str_starts_with($acc->code, '2-5')) {
                        $currentLiabilities[] = ['code' => $acc->code, 'name' => $acc->name, 'balance' => $bal, 'id' => $acc->id];
                    } else {
                        $longTermLiabilities[] = ['code' => $acc->code, 'name' => $acc->name, 'balance' => $bal, 'id' => $acc->id];
                    }
                    break;

                case ChartOfAccount::TYPE_EQUITY:
                    $equityAccounts[] = ['code' => $acc->code, 'name' => $acc->name, 'balance' => $bal, 'id' => $acc->id];
                    break;

                case ChartOfAccount::TYPE_REVENUE:
                    // Normal balance revenue adalah credit
                    $totalRevenue += ($item['credit'] - $item['debit']);
                    break;

                case ChartOfAccount::TYPE_COGS:
                    // Normal balance cogs adalah debit
                    $totalCogs += ($item['debit'] - $item['credit']);
                    break;

                case ChartOfAccount::TYPE_EXPENSE:
                    // Normal balance expense adalah debit
                    $totalExpenses += ($item['debit'] - $item['credit']);
                    break;
            }
        }

        $totalCurrentAssets = array_sum(array_column($currentAssets, 'balance'));
        $totalNonCurrentAssets = array_sum(array_column($nonCurrentAssets, 'balance'));
        $totalAssets = $totalCurrentAssets + $totalNonCurrentAssets;

        $totalCurrentLiabilities = array_sum(array_column($currentLiabilities, 'balance'));
        $totalLongTermLiabilities = array_sum(array_column($longTermLiabilities, 'balance'));
        $totalLiabilities = $totalCurrentLiabilities + $totalLongTermLiabilities;

        // Laba Bersih Periode Berjalan (Current Earnings)
        $currentEarnings = $totalRevenue - ($totalCogs + $totalExpenses);

        $totalEquityBase = array_sum(array_column($equityAccounts, 'balance'));
        $totalEquity = $totalEquityBase + $currentEarnings;

        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;
        $diff = round($totalAssets - $totalLiabilitiesAndEquity, 2);
        $isBalanced = abs($diff) < 0.05;

        return [
            'as_of_date' => $asOfDate->toDateString(),
            'formatted_date' => $asOfDate->translatedFormat('d F Y'),
            'assets' => [
                'current' => $currentAssets,
                'total_current' => $totalCurrentAssets,
                'non_current' => $nonCurrentAssets,
                'total_non_current' => $totalNonCurrentAssets,
                'total' => $totalAssets,
            ],
            'liabilities' => [
                'current' => $currentLiabilities,
                'total_current' => $totalCurrentLiabilities,
                'long_term' => $longTermLiabilities,
                'total_long_term' => $totalLongTermLiabilities,
                'total' => $totalLiabilities,
            ],
            'equity' => [
                'accounts' => $equityAccounts,
                'total_base' => $totalEquityBase,
                'current_earnings' => $currentEarnings,
                'total' => $totalEquity,
            ],
            'summary' => [
                'total_assets' => $totalAssets,
                'total_liabilities' => $totalLiabilities,
                'total_equity' => $totalEquity,
                'total_liabilities_and_equity' => $totalLiabilitiesAndEquity,
                'difference' => $diff,
                'is_balanced' => $isBalanced,
            ],
        ];
    }

    /**
     * Laporan Neraca Saldo (Trial Balance).
     *
     * @return array<string, mixed>
     */
    public function getTrialBalance(Business $business, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ? $startDate->copy()->startOfDay() : now()->startOfYear()->startOfDay();
        $endDate   = $endDate ? $endDate->copy()->endOfDay() : now()->endOfDay();
        $this->journalService->ensureStandardAccounts($business);

        $accounts = ChartOfAccount::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $start = $startDate->toDateString() . ' 00:00:00';
        $end = $endDate->toDateString() . ' 23:59:59';

        // 1. Mutasi sebelum startDate (Beginning balance)
        $beforeSums = JournalEntryLine::whereHas('entry', function ($q) use ($business, $start) {
            $q->where('business_id', $business->id)
                ->where('entry_date', '<', $start);
        })
            ->select('account_id', 'type', DB::raw('SUM(amount) as total'))
            ->groupBy('account_id', 'type')
            ->get()
            ->groupBy('account_id');

        // 2. Mutasi dalam periode (Period movement)
        $periodSums = JournalEntryLine::whereHas('entry', function ($q) use ($business, $start, $end) {
            $q->where('business_id', $business->id)
                ->whereBetween('entry_date', [$start, $end]);
        })
            ->select('account_id', 'type', DB::raw('SUM(amount) as total'))
            ->groupBy('account_id', 'type')
            ->get()
            ->groupBy('account_id');

        $rows = [];
        $totalBeginningDebit = 0.0;
        $totalBeginningCredit = 0.0;
        $totalMovementDebit = 0.0;
        $totalMovementCredit = 0.0;
        $totalEndingDebit = 0.0;
        $totalEndingCredit = 0.0;

        foreach ($accounts as $account) {
            // Awal
            $bDebit = 0.0;
            $bCredit = 0.0;
            if ($bSums = $beforeSums->get($account->id)) {
                foreach ($bSums as $s) {
                    if ($s->type === JournalEntryLine::TYPE_DEBIT) {
                        $bDebit += (float) $s->total;
                    } else {
                        $bCredit += (float) $s->total;
                    }
                }
            }
            $netBeginning = $bDebit - $bCredit;
            $beginningDebit = $netBeginning > 0 ? $netBeginning : 0.0;
            $beginningCredit = $netBeginning < 0 ? abs($netBeginning) : 0.0;

            // Periode
            $mDebit = 0.0;
            $mCredit = 0.0;
            if ($pSums = $periodSums->get($account->id)) {
                foreach ($pSums as $s) {
                    if ($s->type === JournalEntryLine::TYPE_DEBIT) {
                        $mDebit += (float) $s->total;
                    } else {
                        $mCredit += (float) $s->total;
                    }
                }
            }

            // Akhir
            $netEnding = ($bDebit + $mDebit) - ($bCredit + $mCredit);
            $endingDebit = $netEnding > 0 ? $netEnding : 0.0;
            $endingCredit = $netEnding < 0 ? abs($netEnding) : 0.0;

            // Sembunyikan jika tidak ada pergerakan sama sekali
            if ($beginningDebit == 0 && $beginningCredit == 0 && $mDebit == 0 && $mCredit == 0 && $endingDebit == 0 && $endingCredit == 0) {
                continue;
            }

            $rows[] = [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
                'type_label' => $account->getTypeLabel(),
                'beginning_debit' => $beginningDebit,
                'beginning_credit' => $beginningCredit,
                'movement_debit' => $mDebit,
                'movement_credit' => $mCredit,
                'ending_debit' => $endingDebit,
                'ending_credit' => $endingCredit,
            ];

            $totalBeginningDebit += $beginningDebit;
            $totalBeginningCredit += $beginningCredit;
            $totalMovementDebit += $mDebit;
            $totalMovementCredit += $mCredit;
            $totalEndingDebit += $endingDebit;
            $totalEndingCredit += $endingCredit;
        }

        $diff = round($totalEndingDebit - $totalEndingCredit, 2);
        $isBalanced = abs($diff) < 0.05;

        return [
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'formatted' => $startDate->translatedFormat('d M Y') . ' – ' . $endDate->translatedFormat('d M Y'),
            ],
            'rows' => $rows,
            'totals' => [
                'beginning_debit' => $totalBeginningDebit,
                'beginning_credit' => $totalBeginningCredit,
                'movement_debit' => $totalMovementDebit,
                'movement_credit' => $totalMovementCredit,
                'ending_debit' => $totalEndingDebit,
                'ending_credit' => $totalEndingCredit,
                'difference' => $diff,
                'is_balanced' => $isBalanced,
            ],
        ];
    }

    /**
     * Buku Besar Umum (General Ledger Drilldown) dengan Running Balance & Source Documents.
     *
     * @return array<string, mixed>
     */
    public function getGeneralLedger(Business $business, ?string $accountId = null, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ? $startDate->copy()->startOfDay() : now()->startOfMonth()->startOfDay();
        $endDate   = $endDate ? $endDate->copy()->endOfDay() : now()->endOfDay();
        $this->journalService->ensureStandardAccounts($business);

        $accounts = ChartOfAccount::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $selectedAccount = $accountId ? ($accounts->firstWhere('id', $accountId) ?? $accounts->firstWhere('code', $accountId)) : $accounts->first();
        if (! $selectedAccount) {
            $selectedAccount = $accounts->first();
        }

        if (! $selectedAccount) {
            return [
                'accounts' => [],
                'selected_account' => null,
                'beginning_balance' => 0.0,
                'lines' => [],
                'total_debit' => 0.0,
                'total_credit' => 0.0,
                'ending_balance' => 0.0,
            ];
        }

        $start = $startDate->toDateString() . ' 00:00:00';
        $end = $endDate->toDateString() . ' 23:59:59';

        // 1. Saldo Awal sebelum startDate
        $beforeLines = JournalEntryLine::whereHas('entry', function ($q) use ($business, $start) {
            $q->where('business_id', $business->id)
                ->where('entry_date', '<', $start);
        })
            ->where('account_id', $selectedAccount->id)
            ->select('type', DB::raw('SUM(amount) as total'))
            ->groupBy('type')
            ->pluck('total', 'type');

        $bDebit = (float) ($beforeLines['debit'] ?? 0.0);
        $bCredit = (float) ($beforeLines['credit'] ?? 0.0);

        $beginningBalance = $selectedAccount->normal_balance === 'debit'
            ? ($bDebit - $bCredit)
            : ($bCredit - $bDebit);

        // 2. Baris Mutasi Periode
        $lines = JournalEntryLine::where('journal_entry_lines.account_id', $selectedAccount->id)
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entries.business_id', $business->id)
            ->whereBetween('journal_entries.entry_date', [$start, $end])
            ->with(['entry.creator'])
            ->orderBy('journal_entries.entry_date')
            ->orderBy('journal_entries.created_at')
            ->select('journal_entry_lines.*')
            ->get();

        $runningBalance = $beginningBalance;
        $totalDebit = 0.0;
        $totalCredit = 0.0;
        $formattedLines = [];

        foreach ($lines as $line) {
            $entry = $line->entry;
            $debit = $line->type === JournalEntryLine::TYPE_DEBIT ? (float) $line->amount : 0.0;
            $credit = $line->type === JournalEntryLine::TYPE_CREDIT ? (float) $line->amount : 0.0;

            $totalDebit += $debit;
            $totalCredit += $credit;

            if ($selectedAccount->normal_balance === 'debit') {
                $runningBalance += ($debit - $credit);
            } else {
                $runningBalance += ($credit - $debit);
            }

            $sourceUrl = $this->resolveSourceDocumentUrl($entry);

            $formattedLines[] = [
                'id' => $line->id,
                'entry_id' => $entry->id,
                'entry_number' => $entry->entry_number,
                'entry_date' => $entry->entry_date->translatedFormat('d M Y'),
                'description' => $entry->description,
                'notes' => $line->notes,
                'reference_type' => $entry->reference_type,
                'reference_type_label' => strtoupper($entry->reference_type ?? 'Jurnal'),
                'reference_id' => $entry->reference_id,
                'source_url' => $sourceUrl,
                'debit' => $debit,
                'credit' => $credit,
                'running_balance' => $runningBalance,
            ];
        }

        return [
            'accounts' => $accounts,
            'selected_account' => $selectedAccount,
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'formatted' => $startDate->translatedFormat('d M Y') . ' – ' . $endDate->translatedFormat('d M Y'),
            ],
            'beginning_balance' => $beginningBalance,
            'lines' => $formattedLines,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'ending_balance' => $runningBalance,
        ];
    }

    private function resolveSourceDocumentUrl(JournalEntry $entry): ?string
    {
        if (! $entry->reference_id) {
            return null;
        }

        try {
            return match ($entry->reference_type) {
                JournalEntry::REF_POS_ORDER => \Illuminate\Support\Facades\Route::has('pos.orders.index') ? route('pos.orders.index', ['search' => $entry->reference_id]) : null,
                JournalEntry::REF_INVOICE => \Illuminate\Support\Facades\Route::has('invoices.show') ? route('invoices.show', $entry->reference_id) : null,
                JournalEntry::REF_EXPENSE => \Illuminate\Support\Facades\Route::has('finance.expenses.receipt') ? route('finance.expenses.receipt', $entry->reference_id) : null,
                JournalEntry::REF_PURCHASE_RETURN => \Illuminate\Support\Facades\Route::has('purchase.returns.show') ? route('purchase.returns.show', $entry->reference_id) : null,
                JournalEntry::REF_SALES_RETURN => \Illuminate\Support\Facades\Route::has('sales.returns.show') ? route('sales.returns.show', $entry->reference_id) : null,
                JournalEntry::REF_SETTLEMENT => \Illuminate\Support\Facades\Route::has('finance.settlements.show') ? route('finance.settlements.show', $entry->reference_id) : null,
                default => null,
            };
        } catch (\Throwable) {
            return null;
        }
    }
}
