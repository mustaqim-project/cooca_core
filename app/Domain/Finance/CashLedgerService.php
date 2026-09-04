<?php

declare(strict_types=1);

namespace App\Domain\Finance;

use App\Domain\Accounting\AutoJournalService;
use App\Models\Business;
use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class CashLedgerService
{
    public function __construct(private readonly AutoJournalService $journalService = new AutoJournalService) {}

    public function recordInflow(Business $business, float $amount, string $referenceType, string $referenceId, string $description, string $method = 'cash', ?string $userId = null): CashTransaction
    {
        return $this->record($business, CashTransaction::TYPE_IN, $amount, $referenceType, $referenceId, $description, $method, $userId);
    }

    public function recordOutflow(Business $business, float $amount, string $referenceType, string $referenceId, string $description, string $method = 'cash', ?string $userId = null): CashTransaction
    {
        return $this->record($business, CashTransaction::TYPE_OUT, $amount, $referenceType, $referenceId, $description, $method, $userId);
    }

    public function transfer(CashAccount $from, CashAccount $to, float $amount, string $description, ?string $userId = null): array
    {
        if ($amount <= 0 || $from->id === $to->id || $from->business_id !== $to->business_id) {
            throw new InvalidArgumentException('Transfer akun tidak valid.');
        }
        return DB::transaction(function () use ($from, $to, $amount, $description, $userId): array {
            $ids = [$from->id, $to->id]; sort($ids);
            $accounts = CashAccount::whereIn('id', $ids)->lockForUpdate()->get()->keyBy('id');
            $source = $accounts->get($from->id); $destination = $accounts->get($to->id);
            if (! $source || ! $destination || $source->business_id !== $destination->business_id || (float) $source->current_balance < $amount) {
                throw new InvalidArgumentException('Saldo sumber transfer tidak mencukupi.');
            }
            $referenceId = (string) str()->uuid();
            $source->current_balance -= $amount; $source->save();
            $destination->current_balance += $amount; $destination->save();
            $date = now()->toDateString();
            return [
                CashTransaction::create(['business_id' => $source->business_id, 'cash_account_id' => $source->id, 'type' => CashTransaction::TYPE_TRANSFER, 'amount' => $amount, 'balance_after' => $source->current_balance, 'reference_type' => 'cash_transfer', 'reference_id' => $referenceId, 'description' => $description, 'transaction_date' => $date, 'created_by' => $userId]),
                CashTransaction::create(['business_id' => $destination->business_id, 'cash_account_id' => $destination->id, 'type' => CashTransaction::TYPE_TRANSFER, 'amount' => $amount, 'balance_after' => $destination->current_balance, 'reference_type' => 'cash_transfer', 'reference_id' => $referenceId, 'description' => $description, 'transaction_date' => $date, 'created_by' => $userId]),
            ];
        });
    }

    public function accountFor(Business $business, string $method = 'cash'): CashAccount
    {
        $type = in_array($method, ['cash', 'petty_cash'], true) ? CashAccount::TYPE_CASH : (in_array($method, ['qris', 'e_wallet'], true) ? CashAccount::TYPE_EWALLET : CashAccount::TYPE_BANK);
        $name = match ($type) { CashAccount::TYPE_CASH => 'Kas Utama', CashAccount::TYPE_EWALLET => 'QRIS / E-Wallet', default => 'Bank Utama' };
        $code = $type === CashAccount::TYPE_CASH ? '1-1001' : '1-1002';
        $coa = $this->journalService->getAccount($business, $code);
        return CashAccount::firstOrCreate(['business_id' => $business->id, 'name' => $name], ['chart_of_account_id' => $coa?->id, 'type' => $type, 'current_balance' => 0, 'is_active' => true]);
    }

    private function record(Business $business, string $type, float $amount, string $referenceType, string $referenceId, string $description, string $method, ?string $userId): CashTransaction
    {
        if ($amount <= 0) throw new InvalidArgumentException('Nominal kas harus lebih besar dari nol.');
        return DB::transaction(function () use ($business, $type, $amount, $referenceType, $referenceId, $description, $method, $userId): CashTransaction {
            $existing = CashTransaction::where('business_id', $business->id)->where('type', $type)->where('reference_type', $referenceType)->where('reference_id', $referenceId)->first();
            if ($existing) return $existing;
            $account = $this->accountFor($business, $method);
            $account = CashAccount::whereKey($account->id)->lockForUpdate()->firstOrFail();
            $newBalance = (float) $account->current_balance + ($type === CashTransaction::TYPE_IN ? $amount : -$amount);
            if ($newBalance < -0.005) throw new InvalidArgumentException('Saldo kas/bank tidak mencukupi.');
            $account->update(['current_balance' => $newBalance]);
            return CashTransaction::create(['business_id' => $business->id, 'cash_account_id' => $account->id, 'type' => $type, 'amount' => $amount, 'balance_after' => $newBalance, 'reference_type' => $referenceType, 'reference_id' => $referenceId, 'description' => $description, 'transaction_date' => now()->toDateString(), 'created_by' => $userId]);
        });
    }
}
