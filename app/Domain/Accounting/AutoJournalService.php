<?php

declare(strict_types=1);

namespace App\Domain\Accounting;

use App\Models\Business;
use App\Models\ChartOfAccount;
use App\Models\Expense;
use App\Models\GoodsReceipt;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\PosOrder;
use App\Models\PosOrderPayment;
use App\Models\PurchaseReturn as PurchaseReturnModel;
use App\Models\SalesReturn;
use App\Models\SupplierPayment;
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
            ['code' => '1-1005', 'name' => 'Clearing Gateway Pembayaran', 'type' => ChartOfAccount::TYPE_ASSET, 'normal_balance' => 'debit'],
            ['code' => '2-2001', 'name' => 'Hutang Usaha', 'type' => ChartOfAccount::TYPE_LIABILITY, 'normal_balance' => 'credit'],
            ['code' => '2-2002', 'name' => 'Hutang PPN Keluaran', 'type' => ChartOfAccount::TYPE_LIABILITY, 'normal_balance' => 'credit'],
            ['code' => '2-2003', 'name' => 'Hutang Service Charge', 'type' => ChartOfAccount::TYPE_LIABILITY, 'normal_balance' => 'credit'],
            ['code' => '4-4001', 'name' => 'Pendapatan Penjualan POS', 'type' => ChartOfAccount::TYPE_REVENUE, 'normal_balance' => 'credit'],
            ['code' => '4-4002', 'name' => 'Retur Penjualan', 'type' => ChartOfAccount::TYPE_REVENUE, 'normal_balance' => 'debit'],
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

    /**
     * Automatically generate double-entry journal when an Invoice is issued/released.
     *
     * Debit:  Piutang Pelanggan (1-1003) -> Total Tagihan
     * Debit:  Beban Diskon Penjualan (6-6001) -> Diskon (jika ada)
     * Debit:  Beban Pokok Penjualan / HPP (5-5001) -> Total HPP Modal
     * Kredit: Pendapatan Penjualan (4-4001) -> Subtotal Penjualan
     * Kredit: Hutang PPN Keluaran (2-2002) -> Pajak (jika ada)
     * Kredit: Persediaan Barang Dagang (1-1004) -> Total HPP Modal
     */
    public function recordInvoiceIssuedJournal(\App\Models\Invoice $invoice): ?JournalEntry
    {
        $business = $invoice->business;
        if (! $business) {
            return null;
        }

        $this->ensureStandardAccounts($business);

        $existing = JournalEntry::where('business_id', $business->id)
            ->where('reference_type', JournalEntry::REF_INVOICE)
            ->where('reference_id', $invoice->id)
            ->first();
        if ($existing) {
            return $existing;
        }

        $piutangAccount = $this->getAccount($business, '1-1003');
        $persediaanAccount = $this->getAccount($business, '1-1004');
        $revenueAccount = $this->getAccount($business, '4-4001');
        $hppAccount = $this->getAccount($business, '5-5001');
        $ppnAccount = $this->getAccount($business, '2-2002');
        $diskonAccount = $this->getAccount($business, '6-6001');

        if (! $piutangAccount || ! $revenueAccount) {
            return null;
        }

        return DB::transaction(function () use (
            $business,
            $invoice,
            $piutangAccount,
            $persediaanAccount,
            $revenueAccount,
            $hppAccount,
            $ppnAccount,
            $diskonAccount
        ) {
            $entry = JournalEntry::create([
                'business_id' => $business->id,
                'entry_number' => 'JRN-' . $invoice->invoice_number,
                'entry_date' => $invoice->invoice_date,
                'reference_type' => JournalEntry::REF_INVOICE,
                'reference_id' => $invoice->id,
                'description' => "Penerbitan Faktur Penjualan #{$invoice->invoice_number} ke " . ($invoice->customer?->name ?? 'Pelanggan'),
                'total_debit' => 0.0,
                'total_credit' => 0.0,
                'created_by' => $invoice->created_by,
            ]);

            $totalDebit = 0.0;
            $totalCredit = 0.0;

            // 1. Debit: Piutang Pelanggan (Total Tagihan Invoice)
            $totalAmount = (float) $invoice->total_amount;
            if ($totalAmount > 0) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $piutangAccount->id,
                    'type' => JournalEntryLine::TYPE_DEBIT,
                    'amount' => $totalAmount,
                    'notes' => "Piutang Faktur #{$invoice->invoice_number}",
                ]);
                $totalDebit += $totalAmount;
            }

            // 2. Debit: Diskon Penjualan (jika ada)
            $discount = (float) $invoice->discount_amount;
            if ($discount > 0 && $diskonAccount) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $diskonAccount->id,
                    'type' => JournalEntryLine::TYPE_DEBIT,
                    'amount' => $discount,
                    'notes' => "Diskon Faktur #{$invoice->invoice_number}",
                ]);
                $totalDebit += $discount;
            }

            // 3. Kredit: Pendapatan Penjualan (Subtotal)
            $subtotal = (float) $invoice->subtotal + (float) $invoice->shipping_cost;
            if ($subtotal > 0) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $revenueAccount->id,
                    'type' => JournalEntryLine::TYPE_CREDIT,
                    'amount' => $subtotal,
                    'notes' => "Pendapatan Penjualan Faktur #{$invoice->invoice_number}",
                ]);
                $totalCredit += $subtotal;
            }

            // 4. Kredit: Hutang PPN (jika ada)
            $tax = (float) $invoice->tax_amount;
            if ($tax > 0 && $ppnAccount) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $ppnAccount->id,
                    'type' => JournalEntryLine::TYPE_CREDIT,
                    'amount' => $tax,
                    'notes' => "Hutang PPN Faktur #{$invoice->invoice_number}",
                ]);
                $totalCredit += $tax;
            }

            // 5. HPP & Persediaan Barang Dagang
            $hpp = (float) $invoice->total_hpp_cost;
            if ($hpp > 0 && $hppAccount && $persediaanAccount) {
                // Debit HPP
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $hppAccount->id,
                    'type' => JournalEntryLine::TYPE_DEBIT,
                    'amount' => $hpp,
                    'notes' => "Beban Pokok Penjualan Faktur #{$invoice->invoice_number}",
                ]);
                $totalDebit += $hpp;

                // Kredit Persediaan Barang
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $persediaanAccount->id,
                    'type' => JournalEntryLine::TYPE_CREDIT,
                    'amount' => $hpp,
                    'notes' => "Pengurangan Persediaan Barang Faktur #{$invoice->invoice_number}",
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
     * Automatically generate double-entry journal when an Invoice payment is received.
     *
     * Debit:  Kas (1-1001) atau Bank (1-1002)
     * Kredit: Piutang Pelanggan (1-1003)
     */
    public function recordInvoicePaymentJournal(\App\Models\InvoicePayment $payment): ?JournalEntry
    {
        $invoice = $payment->invoice;
        $business = $payment->business ?? $invoice?->business;
        if (! $business || ! $invoice) {
            return null;
        }

        $this->ensureStandardAccounts($business);

        $kasAccount = $this->getAccount($business, '1-1001');
        $bankAccount = $this->getAccount($business, '1-1002');
        $piutangAccount = $this->getAccount($business, '1-1003');

        $debitAccount = match ($payment->payment_method) {
            'cash' => $kasAccount,
            default => $bankAccount,
        };

        if (! $debitAccount || ! $piutangAccount) {
            return null;
        }

        $amount = (float) $payment->amount;
        if ($amount <= 0) {
            return null;
        }

        return DB::transaction(function () use ($business, $payment, $invoice, $debitAccount, $piutangAccount, $amount) {
            $entry = JournalEntry::create([
                'business_id' => $business->id,
                'entry_number' => 'JRN-PAY-' . $payment->payment_number,
                'entry_date' => $payment->payment_date,
                'reference_type' => JournalEntry::REF_INVOICE_PAYMENT,
                'reference_id' => $payment->id,
                'description' => "Pelunasan Piutang Faktur #{$invoice->invoice_number} via " . strtoupper($payment->payment_method),
                'total_debit' => $amount,
                'total_credit' => $amount,
                'created_by' => $payment->created_by,
            ]);

            // Debit Kas/Bank
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $debitAccount->id,
                'type' => JournalEntryLine::TYPE_DEBIT,
                'amount' => $amount,
                'notes' => "Penerimaan Kas/Bank Pembayaran Faktur #{$invoice->invoice_number}",
            ]);

            // Kredit Piutang Pelanggan
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $piutangAccount->id,
                'type' => JournalEntryLine::TYPE_CREDIT,
                'amount' => $amount,
                'notes' => "Pengurangan Piutang Faktur #{$invoice->invoice_number}",
            ]);

            return $entry;
        });
    }

    public function recordGoodsReceiptJournal(GoodsReceipt $receipt): ?JournalEntry
    {
        $business = $receipt->business;
        if (! $business) {
            return null;
        }

        $existing = JournalEntry::where('business_id', $business->id)
            ->where('reference_type', JournalEntry::REF_GOODS_RECEIPT)
            ->where('reference_id', $receipt->id)
            ->first();
        if ($existing) {
            return $existing;
        }

        $this->ensureStandardAccounts($business);
        $inventoryAccount = $this->getAccount($business, '1-1004');
        $payableAccount = $this->getAccount($business, '2-2001');
        $amount = (float) $receipt->items()->selectRaw('COALESCE(SUM(quantity * unit_cost), 0) as total')->value('total');

        if (! $inventoryAccount || ! $payableAccount || $amount <= 0) {
            return null;
        }

        return DB::transaction(function () use ($business, $receipt, $inventoryAccount, $payableAccount, $amount) {
            $entry = JournalEntry::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'reference_type' => JournalEntry::REF_GOODS_RECEIPT,
                    'reference_id' => $receipt->id,
                ],
                [
                    'entry_number' => 'JRN-' . $receipt->receipt_number,
                    'entry_date' => $receipt->receipt_date,
                    'description' => "Penerimaan barang #{$receipt->receipt_number}",
                    'total_debit' => $amount,
                    'total_credit' => $amount,
                    'created_by' => $receipt->received_by,
                ]
            );

            if ($entry->wasRecentlyCreated) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $inventoryAccount->id,
                    'type' => JournalEntryLine::TYPE_DEBIT,
                    'amount' => $amount,
                    'notes' => "Penambahan persediaan dari GR #{$receipt->receipt_number}",
                ]);
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $payableAccount->id,
                    'type' => JournalEntryLine::TYPE_CREDIT,
                    'amount' => $amount,
                    'notes' => "Hutang supplier dari GR #{$receipt->receipt_number}",
                ]);
            }

            return $entry;
        });
    }

    public function recordSupplierPaymentJournal(SupplierPayment $payment): ?JournalEntry
    {
        $business = $payment->business;
        $invoice = $payment->supplierInvoice;
        if (! $business || ! $invoice) {
            return null;
        }

        $existing = JournalEntry::where('business_id', $business->id)
            ->where('reference_type', JournalEntry::REF_SUPPLIER_PAYMENT)
            ->where('reference_id', $payment->id)
            ->first();
        if ($existing) {
            return $existing;
        }

        $this->ensureStandardAccounts($business);
        $cashAccount = $this->getAccount($business, '1-1001');
        $bankAccount = $this->getAccount($business, '1-1002');
        $payableAccount = $this->getAccount($business, '2-2001');
        $debitAccount = $payment->payment_method === 'cash' ? $cashAccount : $bankAccount;
        $amount = (float) $payment->amount;

        if (! $debitAccount || ! $payableAccount || $amount <= 0) {
            return null;
        }

        return DB::transaction(function () use ($business, $payment, $invoice, $debitAccount, $payableAccount, $amount) {
            $entry = JournalEntry::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'reference_type' => JournalEntry::REF_SUPPLIER_PAYMENT,
                    'reference_id' => $payment->id,
                ],
                [
                    'entry_number' => 'JRN-PAY-' . $payment->payment_number,
                    'entry_date' => $payment->payment_date,
                    'description' => "Pembayaran hutang supplier #{$invoice->invoice_number}",
                    'total_debit' => $amount,
                    'total_credit' => $amount,
                    'created_by' => $payment->created_by,
                ]
            );

            if ($entry->wasRecentlyCreated) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $payableAccount->id,
                    'type' => JournalEntryLine::TYPE_DEBIT,
                    'amount' => $amount,
                    'notes' => "Pengurangan hutang supplier #{$invoice->invoice_number}",
                ]);
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $debitAccount->id,
                    'type' => JournalEntryLine::TYPE_CREDIT,
                    'amount' => $amount,
                    'notes' => 'Pembayaran melalui kas/bank',
                ]);
            }

            return $entry;
        });
    }

    public function recordSalesReturnJournal(SalesReturn $salesReturn): ?JournalEntry
    {
        $business = $salesReturn->business;
        if (! $business) return null;
        $existing = JournalEntry::where('business_id', $business->id)->where('reference_type', JournalEntry::REF_SALES_RETURN)->where('reference_id', $salesReturn->id)->first();
        if ($existing) return $existing;
        $this->ensureStandardAccounts($business);
        $returnAccount = $this->getAccount($business, '4-4002');
        $receivableAccount = $this->getAccount($business, '1-1003');
        $inventoryAccount = $this->getAccount($business, '1-1004');
        $hppAccount = $this->getAccount($business, '5-5001');
        if (! $returnAccount || ! $receivableAccount || ! $inventoryAccount || ! $hppAccount) return null;
        $salesAmount = (float) $salesReturn->total_amount;
        $hppAmount = (float) $salesReturn->items()->sum(DB::raw('quantity * unit_hpp'));
        return DB::transaction(function () use ($business, $salesReturn, $returnAccount, $receivableAccount, $inventoryAccount, $hppAccount, $salesAmount, $hppAmount) {
            $entry = JournalEntry::firstOrCreate([
                'business_id' => $business->id, 'reference_type' => JournalEntry::REF_SALES_RETURN, 'reference_id' => $salesReturn->id,
            ], [
                'entry_number' => 'JRN-' . $salesReturn->return_number, 'entry_date' => $salesReturn->return_date,
                'description' => "Retur penjualan #{$salesReturn->return_number}", 'total_debit' => $salesAmount + $hppAmount,
                'total_credit' => $salesAmount + $hppAmount, 'created_by' => $salesReturn->created_by,
            ]);
            if ($entry->wasRecentlyCreated) {
                JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $returnAccount->id, 'type' => JournalEntryLine::TYPE_DEBIT, 'amount' => $salesAmount, 'notes' => 'Pembalikan pendapatan penjualan']);
                JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $receivableAccount->id, 'type' => JournalEntryLine::TYPE_CREDIT, 'amount' => $salesAmount, 'notes' => 'Kredit piutang pelanggan']);
                if ($hppAmount > 0) {
                    JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $inventoryAccount->id, 'type' => JournalEntryLine::TYPE_DEBIT, 'amount' => $hppAmount, 'notes' => 'Persediaan barang kembali']);
                    JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $hppAccount->id, 'type' => JournalEntryLine::TYPE_CREDIT, 'amount' => $hppAmount, 'notes' => 'Pembalikan HPP']);
                }
            }
            return $entry;
        });
    }

    public function recordPosRefundJournal(SalesReturn $salesReturn): ?JournalEntry
    {
        $business = $salesReturn->business;
        if (! $business) return null;
        $existing = JournalEntry::where('business_id', $business->id)->where('reference_type', JournalEntry::REF_POS_REFUND)->where('reference_id', $salesReturn->id)->first();
        if ($existing) return $existing;
        $this->ensureStandardAccounts($business);
        $refundAccount = $this->getAccount($business, '4-4002');
        $cashAccount = $this->getAccount($business, '1-1001');
        $inventoryAccount = $this->getAccount($business, '1-1004');
        $hppAccount = $this->getAccount($business, '5-5001');
        $salesAmount = (float) $salesReturn->total_amount;
        $hppAmount = (float) $salesReturn->items()->sum(DB::raw('quantity * unit_hpp'));
        if (! $refundAccount || ! $cashAccount || ! $inventoryAccount || ! $hppAccount || $salesAmount <= 0) return null;
        return DB::transaction(function () use ($business, $salesReturn, $refundAccount, $cashAccount, $inventoryAccount, $hppAccount, $salesAmount, $hppAmount) {
            $entry = JournalEntry::firstOrCreate([
                'business_id' => $business->id, 'reference_type' => JournalEntry::REF_POS_REFUND, 'reference_id' => $salesReturn->id,
            ], ['entry_number' => 'JRN-' . $salesReturn->return_number, 'entry_date' => $salesReturn->return_date, 'description' => "Refund POS #{$salesReturn->return_number}", 'total_debit' => $salesAmount + $hppAmount, 'total_credit' => $salesAmount + $hppAmount, 'created_by' => $salesReturn->created_by]);
            if ($entry->wasRecentlyCreated) {
                JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $refundAccount->id, 'type' => JournalEntryLine::TYPE_DEBIT, 'amount' => $salesAmount, 'notes' => 'Pembalikan pendapatan POS']);
                JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $cashAccount->id, 'type' => JournalEntryLine::TYPE_CREDIT, 'amount' => $salesAmount, 'notes' => 'Pengembalian dana POS']);
                if ($hppAmount > 0) {
                    JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $inventoryAccount->id, 'type' => JournalEntryLine::TYPE_DEBIT, 'amount' => $hppAmount, 'notes' => 'Persediaan POS dikembalikan']);
                    JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $hppAccount->id, 'type' => JournalEntryLine::TYPE_CREDIT, 'amount' => $hppAmount, 'notes' => 'Pembalikan HPP POS']);
                }
            }
            return $entry;
        });
    }

    public function recordPurchaseReturnJournal(PurchaseReturnModel $purchaseReturn): ?JournalEntry
    {
        $business = $purchaseReturn->business;
        if (! $business) return null;
        $existing = JournalEntry::where('business_id', $business->id)->where('reference_type', JournalEntry::REF_PURCHASE_RETURN)->where('reference_id', $purchaseReturn->id)->first();
        if ($existing) return $existing;
        $this->ensureStandardAccounts($business);
        $payableAccount = $this->getAccount($business, '2-2001');
        $inventoryAccount = $this->getAccount($business, '1-1004');
        $amount = (float) $purchaseReturn->total_amount;
        if (! $payableAccount || ! $inventoryAccount || $amount <= 0) return null;
        return DB::transaction(function () use ($business, $purchaseReturn, $payableAccount, $inventoryAccount, $amount) {
            $entry = JournalEntry::firstOrCreate([
                'business_id' => $business->id, 'reference_type' => JournalEntry::REF_PURCHASE_RETURN, 'reference_id' => $purchaseReturn->id,
            ], [
                'entry_number' => 'JRN-' . $purchaseReturn->return_number, 'entry_date' => $purchaseReturn->return_date,
                'description' => "Retur pembelian #{$purchaseReturn->return_number}", 'total_debit' => $amount,
                'total_credit' => $amount, 'created_by' => $purchaseReturn->created_by,
            ]);
            if ($entry->wasRecentlyCreated) {
                JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $payableAccount->id, 'type' => JournalEntryLine::TYPE_DEBIT, 'amount' => $amount, 'notes' => 'Pengurangan hutang supplier']);
                JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $inventoryAccount->id, 'type' => JournalEntryLine::TYPE_CREDIT, 'amount' => $amount, 'notes' => 'Pengurangan persediaan retur']);
            }
            return $entry;
        });
    }
}
