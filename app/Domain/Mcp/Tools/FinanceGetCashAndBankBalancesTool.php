<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\Business;
use App\Models\CashAccount;
use App\Models\User;

final class FinanceGetCashAndBankBalancesTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'finance_get_cash_and_bank_balances';
    }

    public function getDescription(): string
    {
        return 'Melihat saldo terkini seluruh akun kas toko, petty cash kasir, dan rekening bank aktif milik bisnis.';
    }

    public function getRequiredAbility(): string
    {
        return 'mcp:finance:read';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'location_id' => [
                    'type' => 'string',
                    'description' => 'Optional: Filter saldo kas untuk cabang atau outlet tertentu.',
                ],
            ],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $query = CashAccount::where('business_id', $business->id)
            ->where('is_active', true);

        if (! empty($arguments['location_id'])) {
            $query->where('location_id', (string) $arguments['location_id']);
        }

        $accounts = $query->orderBy('name')->get();

        $accountList = [];
        $totalLiquidity = 0.0;

        foreach ($accounts as $acc) {
            $balance = (float) $acc->current_balance;
            $totalLiquidity += $balance;

            $accountList[] = [
                'id' => $acc->id,
                'name' => $acc->name,
                'account_number' => $acc->account_number,
                'bank_name' => $acc->bank_name,
                'type' => $acc->type ?? 'cash',
                'current_balance' => $balance,
                'formatted_balance' => 'Rp ' . number_format($balance, 0, ',', '.'),
            ];
        }

        return [
            'status' => 'success',
            'total_liquidity' => $totalLiquidity,
            'formatted_total_liquidity' => 'Rp ' . number_format($totalLiquidity, 0, ',', '.'),
            'accounts_count' => count($accountList),
            'accounts' => $accountList,
        ];
    }
}
