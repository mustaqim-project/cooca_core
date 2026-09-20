<?php

declare(strict_types=1);

namespace App\Domain\Accounting;

use App\Models\BankStatement;
use App\Models\BankStatementLine;
use App\Models\Business;
use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class BankReconciliationService
{
    /**
     * Import mutasi rekening koran dan jalankan auto-match instan.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function importStatement(
        Business $business,
        CashAccount $cashAccount,
        string $filename,
        Carbon $statementDate,
        array $rows,
        ?User $user = null
    ): BankStatement {
        return DB::transaction(function () use ($business, $cashAccount, $filename, $statementDate, $rows, $user) {
            $statement = BankStatement::create([
                'business_id' => $business->id,
                'cash_account_id' => $cashAccount->id,
                'filename' => $filename,
                'statement_date' => $statementDate->toDateString(),
                'status' => BankStatement::STATUS_IN_PROGRESS,
                'total_lines' => count($rows),
                'reconciled_lines' => 0,
                'imported_by' => $user?->id,
            ]);

            foreach ($rows as $row) {
                $type = strtolower((string) ($row['type'] ?? 'credit'));
                $amount = (float) ($row['amount'] ?? 0.0);
                $date = isset($row['date']) ? Carbon::parse($row['date'])->toDateString() : $statementDate->toDateString();

                BankStatementLine::create([
                    'bank_statement_id' => $statement->id,
                    'business_id' => $business->id,
                    'transaction_date' => $date,
                    'description' => (string) ($row['description'] ?? 'Mutasi Rekening Koran'),
                    'reference_number' => $row['reference_number'] ?? null,
                    'type' => $type === 'debit' ? BankStatementLine::TYPE_DEBIT : BankStatementLine::TYPE_CREDIT,
                    'amount' => abs($amount),
                    'balance' => isset($row['balance']) ? (float) $row['balance'] : null,
                    'status' => BankStatementLine::STATUS_UNMATCHED,
                ]);
            }

            $this->autoMatch($statement);

            return $statement->fresh(['lines', 'cashAccount']);
        });
    }

    /**
     * Mencocokkan mutasi bank secara otomatis dengan transaksi kas internal.
     */
    public function autoMatch(BankStatement $statement): int
    {
        $businessId = $statement->business_id;
        $cashAccountId = $statement->cash_account_id;
        $matchedCount = 0;

        $unmatchedLines = BankStatementLine::where('bank_statement_id', $statement->id)
            ->where('status', BankStatementLine::STATUS_UNMATCHED)
            ->get();

        foreach ($unmatchedLines as $line) {
            $lineDate = Carbon::parse($line->transaction_date);
            $startDate = $lineDate->copy()->subDays(3)->toDateString();
            $endDate = $lineDate->copy()->addDays(3)->toDateString();

            // Mutasi Kredit di bank = uang masuk (Inflow) di pembukuan
            // Mutasi Debit di bank = uang keluar (Outflow) di pembukuan
            $targetType = $line->type === BankStatementLine::TYPE_CREDIT ? 'in' : 'out';

            $candidate = CashTransaction::where('business_id', $businessId)
                ->where('cash_account_id', $cashAccountId)
                ->where('type', $targetType)
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->where(function ($q) use ($line) {
                    $q->whereBetween('amount', [$line->amount - 0.05, $line->amount + 0.05]);
                })
                ->first();

            if ($candidate) {
                $line->update([
                    'status' => BankStatementLine::STATUS_MATCHED,
                    'matched_transaction_type' => 'cash_transaction',
                    'matched_transaction_id' => $candidate->id,
                    'notes' => "Cocok otomatis dengan CashTransaction #{$candidate->id}",
                ]);
                $matchedCount++;
            }
        }

        return $matchedCount;
    }

    /**
     * Konfirmasi rekonsiliasi satu baris mutasi.
     */
    public function reconcileLine(BankStatementLine $line, ?User $user = null, ?string $notes = null): void
    {
        DB::transaction(function () use ($line, $user, $notes) {
            $line->update([
                'status' => BankStatementLine::STATUS_RECONCILED,
                'reconciled_at' => now(),
                'reconciled_by' => $user?->id,
                'notes' => $notes ?: $line->notes,
            ]);

            $statement = $line->statement;
            if ($statement) {
                $reconciledCount = $statement->lines()->where('status', BankStatementLine::STATUS_RECONCILED)->count();
                $statement->update([
                    'reconciled_lines' => $reconciledCount,
                    'status' => ($reconciledCount >= $statement->total_lines)
                        ? BankStatement::STATUS_COMPLETED
                        : BankStatement::STATUS_IN_PROGRESS,
                ]);
            }
        });
    }

    /**
     * Batalkan pencocokan atau rekonsiliasi.
     */
    public function unmatchLine(BankStatementLine $line): void
    {
        DB::transaction(function () use ($line) {
            $wasReconciled = $line->status === BankStatementLine::STATUS_RECONCILED;

            $line->update([
                'status' => BankStatementLine::STATUS_UNMATCHED,
                'matched_transaction_type' => null,
                'matched_transaction_id' => null,
                'reconciled_at' => null,
                'reconciled_by' => null,
            ]);

            if ($wasReconciled) {
                $statement = $line->statement;
                if ($statement) {
                    $reconciledCount = $statement->lines()->where('status', BankStatementLine::STATUS_RECONCILED)->count();
                    $statement->update([
                        'reconciled_lines' => $reconciledCount,
                        'status' => BankStatement::STATUS_IN_PROGRESS,
                    ]);
                }
            }
        });
    }
}
