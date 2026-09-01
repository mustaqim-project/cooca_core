<?php

declare(strict_types=1);

namespace App\Domain\Accounting;

use App\Models\Business;
use App\Models\ChartOfAccount;
use App\Models\Expense;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\PosOrder;
use App\Models\PosOrderPayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class AutoJournalService
{
    /**
     * Ensure standard Chart of Accounts exists for this business.
     */
    public function ensureStandardAccounts(Business $business): void
    {
        $standards = [
            ['code' => '1-1001', 'name' => 'Kas Kasir', 'type' => ChartOfAccount::TYPE_ASSET, 'normal_balance' => 'debit'],
            ['code' => '1-1002', 'name' => 'Bank / QRIS Settlement', 'type' => ChartOfAccount::TYPE_ASSET, 'normal_balance' => 'debit'],
            ['code' => '1-1003', 'name' => 'Piutang Pelanggan', 'type' => ChartOfAccount::TYPE_ASSET, 'normal_balance' => 'debit'],
            ['code' => '1-1004', 'name' => 'Persediaan Barang Dagang', 'type' => ChartOfAccount::TYPE_ASSET, 'normal_balance' => 'debit'],
            ['code' => '2-2001', 'name' => 'Hutang Usaha', 'type' => ChartOfAccount::TYPE_LIABILITY, 'normal_balance' => 'credit'],
            ['code' => '2-2002', 'name' => 'Hutang PPN Keluaran', 'type' => ChartOfAccount::TYPE_LIABILITY, 'normal_balance' => 'credit'],
            ['code' => '2-2003', 'name' => 'Hutang Service Charge', 'type' => ChartOfAccount::TYPE_LIABILITY, 'normal_balance' => 'credit'],
            ['code' => '4-4001', 'name' => 'Pendapatan Penjualan POS', 'type' => ChartOfAccount::TYPE_REVENUE, 'normal_balance' => 'credit'],
            ['code' => '5-5001', 'name' => 'Beban Pokok Penjualan (HPP)', 'type' => ChartOfAccount::TYPE_COGS, 'normal_balance' => 'debit'],
            ['code' => '6-6001', 'name' => 'Beban Diskon Penjualan', 'type' => ChartOfAccount::TYPE_EXPENSE, 'normal_balance' => 'debit'],
            ['code' => '6-6002', 'name' => 'Beban Operasional Toko', 'type' => ChartOfAccount::TYPE_EXPENSE, 'normal_balance' => 'debit'],
        ];

        foreach ($standards as $std) {
            ChartOfAccount::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'code' => $std['code'],
                ],
                [
                    'name' => $std['name'],
                    'type' => $std['type'],
                    'normal_balance' => $std['normal_balance'],
                    'is_system' => true,
                    'is_active' => true,
                ]
            );
        }
    }

    public function getAccount(Business $business, string $code): ?ChartOfAccount
    {
        $this->ensureStandardAccounts($business);
        return ChartOfAccount::where('business_id', $business->id)
            ->where('code', $code)
            ->first();
    }

    /**
     * Automatically generate double-entry journal for a completed POS Order.
     */
    public function recordPosSaleJournal(PosOrder $order): ?JournalEntry
    {
        $business = $order->business;
        if (! $business) {
            return null;
        }

        $this->ensureStandardAccounts($business);

        $kasAccount = $this->getAccount($business, '1-1001');
        $bankAccount = $this->getAccount($business, '1-1002');
        $piutangAccount = $this->getAccount($business, '1-1003');
        $persediaanAccount = $this->getAccount($business, '1-1004');
        $ppnAccount = $this->getAccount($business, '2-2002');
        $serviceAccount = $this->getAccount($business, '2-2003');
        $revenueAccount = $this->getAccount($business, '4-4001');
        $hppAccount = $this->getAccount($business, '5-5001');
        $diskonAccount = $this->getAccount($business, '6-6001');

        if (! $kasAccount || ! $revenueAccount || ! $persediaanAccount || ! $hppAccount) {
            return null;
        }

        return DB::transaction(function () use (
            $business,
            $order,
            $kasAccount,
            $bankAccount,
            $piutangAccount,
            $persediaanAccount,
            $ppnAccount,
            $serviceAccount,
            $revenueAccount,
            $hppAccount,
            $diskonAccount
        ) {
            $entry = JournalEntry::create([
                'business_id' => $business->id,
                'entry_number' => 'JRN-' . $order->order_number,
                'entry_date' => $order->order_date,
                'reference_type' => JournalEntry::REF_POS_ORDER,
                'reference_id' => $order->id,
                'description' => "Penjualan POS #{$order->order_number}",
                'total_debit' => 0.0,
                'total_credit' => 0.0,
                'created_by' => $order->user_id,
            ]);

            $totalDebit = 0.0;
            $totalCredit = 0.0;

            // 1. Debits: Payments (Cash, Bank/QRIS, Piutang)
            $remainingChange = (float) $order->change_amount;
            foreach ($order->payments as $payment) {
                $payMethod = $payment->payment_method;
                $payAmount = (float) $payment->amount;

                if ($payMethod === PosOrderPayment::METHOD_CASH && $remainingChange > 0) {
                    $deduct = min($payAmount, $remainingChange);
                    $payAmount -= $deduct;
                    $remainingChange -= $deduct;
                }

                if ($payAmount <= 0) {
                    continue;
                }

                $targetAccount = match ($payMethod) {
                    PosOrderPayment::METHOD_CASH => $kasAccount,
                    PosOrderPayment::METHOD_CUSTOMER_CREDIT => $piutangAccount,
                    default => $bankAccount,
                };

                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $targetAccount->id,
                    'type' => JournalEntryLine::TYPE_DEBIT,
                    'amount' => $payAmount,
                    'notes' => "Penerimaan " . ucfirst($payMethod),
                ]);
                $totalDebit += $payAmount;
            }

            // 2. Debit: Discount (if any)
            $totalDiscount = (float) $order->discount_amount + (float) $order->voucher_discount_amount + (float) $order->points_discount_amount;
            if ($totalDiscount > 0 && $diskonAccount) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $diskonAccount->id,
                    'type' => JournalEntryLine::TYPE_DEBIT,
                    'amount' => $totalDiscount,
                    'notes' => "Beban Diskon & Voucher POS",
                ]);
                $totalDebit += $totalDiscount;
            }

            // 3. Credit: Revenue (Gross Subtotal)
            $subtotal = (float) $order->subtotal;
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $revenueAccount->id,
                'type' => JournalEntryLine::TYPE_CREDIT,
                'amount' => $subtotal,
                'notes' => "Pendapatan Penjualan Kasir",
            ]);
            $totalCredit += $subtotal;

            // 4. Credit: PPN Tax (if any)
            $tax = (float) $order->tax_amount;
            if ($tax > 0 && $ppnAccount) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $ppnAccount->id,
                    'type' => JournalEntryLine::TYPE_CREDIT,
                    'amount' => $tax,
                    'notes' => "Hutang PPN Keluaran",
                ]);
                $totalCredit += $tax;
            }

            // 5. Credit: Service Charge (if any)
            $service = (float) $order->service_charge_amount;
            if ($service > 0 && $serviceAccount) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $serviceAccount->id,
                    'type' => JournalEntryLine::TYPE_CREDIT,
                    'amount' => $service,
                    'notes' => "Hutang Service Charge",
                ]);
                $totalCredit += $service;
            }

            // 6. COGS / HPP & Inventory Movement
            $hpp = (float) $order->total_hpp_cost;
            if ($hpp > 0) {
                // Debit: Beban Pokok Penjualan (HPP)
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $hppAccount->id,
                    'type' => JournalEntryLine::TYPE_DEBIT,
                    'amount' => $hpp,
                    'notes' => "Beban Pokok Penjualan (HPP)",
                ]);
                $totalDebit += $hpp;

                // Credit: Persediaan Barang Dagang
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $persediaanAccount->id,
                    'type' => JournalEntryLine::TYPE_CREDIT,
                    'amount' => $hpp,
                    'notes' => "Pengurangan Persediaan Barang",
                ]);
                $totalCredit += $hpp;
            }

            $entry->update([
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
            ]);

            return $entry;
        });
    }

    /**
     * Record journal entry for an operational expense.
     */
    public function recordExpenseJournal(Expense $expense, User $recorder): ?JournalEntry
    {
        $business = $expense->business;
        if (! $business) {
            return null;
        }

        $this->ensureStandardAccounts($business);

        $kasAccount = $this->getAccount($business, '1-1001');
        $bankAccount = $this->getAccount($business, '1-1002');
        $expenseAccount = $expense->account_id ? ChartOfAccount::find($expense->account_id) : $this->getAccount($business, '6-6002');

        $creditAccount = match ($expense->payment_method) {
            'cash', 'petty_cash' => $kasAccount,
            default => $bankAccount,
        };

        if (! $expenseAccount || ! $creditAccount) {
            return null;
        }

        $amount = (float) $expense->amount;

        return DB::transaction(function () use ($business, $expense, $expenseAccount, $creditAccount, $amount, $recorder) {
            $entry = JournalEntry::create([
                'business_id' => $business->id,
                'entry_number' => 'JRN-' . $expense->expense_number,
                'entry_date' => $expense->expense_date,
                'reference_type' => JournalEntry::REF_EXPENSE,
                'reference_id' => $expense->id,
                'description' => "Pengeluaran Biaya: {$expense->category} - " . ($expense->description ?? ''),
                'total_debit' => $amount,
                'total_credit' => $amount,
                'created_by' => $recorder->id,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $expenseAccount->id,
                'type' => JournalEntryLine::TYPE_DEBIT,
                'amount' => $amount,
                'notes' => "Beban Operasional: {$expense->category}",
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $creditAccount->id,
                'type' => JournalEntryLine::TYPE_CREDIT,
                'amount' => $amount,
                'notes' => "Pembayaran Kas/Bank",
            ]);

            return $entry;
        });
    }
}
