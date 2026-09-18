<?php

declare(strict_types=1);

namespace App\Domain\Finance;

use App\Domain\Accounting\AutoJournalService;
use App\Models\Business;
use App\Models\CashTransaction;
use App\Models\ChartOfAccount;
use App\Models\CommerceOrder;
use App\Models\InvoicePayment;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\PaymentSettlement;
use App\Models\PaymentSettlementAllocation;
use App\Models\PosOrder;
use App\Models\PosOrderPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class PaymentSettlementService
{
    public function __construct(
        private readonly CashLedgerService $cashLedger = new CashLedgerService,
        private readonly AutoJournalService $journalService = new AutoJournalService,
    ) {}

    /**
     * @param array<string, mixed> $data
     * @param array<int, array{payment_type: string, payment_id: string, amount: float|int}> $allocations
     */
    public function reconcile(Business $business, array $data, array $allocations, ?string $userId = null, bool $immediateComplete = true): PaymentSettlement
    {
        return DB::transaction(function () use ($business, $data, $allocations, $userId, $immediateComplete): PaymentSettlement {
            $settlementNumber = (string) ($data['settlement_number'] ?? '');
            if ($settlementNumber === '') throw new InvalidArgumentException('Nomor settlement wajib diisi.');

            $settlement = PaymentSettlement::where('business_id', $business->id)
                ->where('settlement_number', $settlementNumber)->lockForUpdate()->first();
            if ($settlement?->status === PaymentSettlement::STATUS_COMPLETED) return $settlement->load('allocations');

            $gross = round((float) ($data['gross_amount'] ?? 0), 2);
            $fee = round((float) ($data['fee_amount'] ?? 0), 2);
            $net = round((float) ($data['net_amount'] ?? ($gross - $fee)), 2);
            if ($gross <= 0 || $fee < 0 || abs($net - ($gross - $fee)) > 0.01) throw new InvalidArgumentException('Gross, fee, dan net settlement tidak valid.');
            if ($settlement && $settlement->status !== PaymentSettlement::STATUS_PENDING) throw new InvalidArgumentException('Settlement tidak dapat diproses.');

            $resolved = [];
            $allocationGross = 0.0;
            foreach ($allocations as $allocation) {
                $type = (string) ($allocation['payment_type'] ?? '');
                $paymentId = (string) ($allocation['payment_id'] ?? '');
                $amount = round((float) ($allocation['amount'] ?? 0), 2);
                if ($amount <= 0) throw new InvalidArgumentException('Nominal allocation harus lebih besar dari nol.');
                $payment = match ($type) {
                    'pos_order_payment' => PosOrderPayment::whereKey($paymentId)->whereHas('order', fn ($query) => $query->where('business_id', $business->id))->lockForUpdate()->first(),
                    'invoice_payment' => InvoicePayment::whereKey($paymentId)->where('business_id', $business->id)->lockForUpdate()->first(),
                    'commerce_order' => CommerceOrder::whereKey($paymentId)->where('business_id', $business->id)->where('payment_gateway', CommerceOrder::GATEWAY_TRIPAY)->lockForUpdate()->first(),
                    default => null,
                };
                if (! $payment
                    || ($payment instanceof PosOrderPayment && $payment->payment_method === PosOrderPayment::METHOD_CASH)
                    || ($payment instanceof PosOrderPayment && $payment->status !== 'paid')
                    || ($payment instanceof CommerceOrder && ! $payment->isPaid())
                ) {
                    throw new InvalidArgumentException('Pembayaran gateway tidak ditemukan atau tidak eligible.');
                }
                $alreadyAllocated = PaymentSettlementAllocation::where('business_id', $business->id)->where('payment_type', $type)->where('payment_id', $paymentId)->exists();
                if ($alreadyAllocated) throw new InvalidArgumentException('Pembayaran sudah direkonsiliasi.');
                $maxAllocable = $payment instanceof CommerceOrder ? (float) $payment->total_amount : (float) $payment->amount;
                if ($amount > $maxAllocable + 0.005) throw new InvalidArgumentException('Allocation melebihi nominal pembayaran.');
                $resolved[] = [$type, $paymentId, $amount];
                $allocationGross += $amount;
            }
            if (abs(round($allocationGross, 2) - $gross) > 0.01) throw new InvalidArgumentException('Total allocation tidak sama dengan gross settlement.');

            $settlement ??= PaymentSettlement::create([
                'business_id' => $business->id,
                'settlement_number' => $settlementNumber,
                'settlement_date' => $data['settlement_date'] ?? Carbon::today()->toDateString(),
                'payment_channel' => $data['payment_channel'] ?? 'other',
                'gross_amount' => $gross,
                'fee_amount' => $fee,
                'net_amount' => $net,
                'destination_bank' => $data['destination_bank'] ?? null,
                'status' => PaymentSettlement::STATUS_PENDING,
                'notes' => $data['notes'] ?? null,
                'reconciled_by' => $userId,
            ]);

            foreach ($resolved as [$type, $paymentId, $amount]) {
                PaymentSettlementAllocation::create(['business_id' => $business->id, 'payment_settlement_id' => $settlement->id, 'payment_type' => $type, 'payment_id' => $paymentId, 'amount' => $amount]);
            }

            if ($immediateComplete) {
                $method = strtolower((string) ($settlement->payment_channel ?? '')) === 'qris' ? 'qris' : 'bank_transfer';
                $this->cashLedger->recordInflow($business, $net, 'settlement', $settlement->id, "Settlement {$settlement->settlement_number}", $method, $userId);
                $this->recordJournal($business, $settlement, $userId);
                $settlement->update(['status' => PaymentSettlement::STATUS_COMPLETED, 'reconciled_by' => $userId]);
            }

            return $settlement->fresh('allocations');
        });
    }

    /**
     * Approve and complete a payout by Admin COOCA, recording payment proof image, ledger inflow, and auto-journal.
     */
    public function completePayout(PaymentSettlement $settlement, ?string $adminId = null, ?string $proofImagePath = null, ?string $adminNotes = null): PaymentSettlement
    {
        return DB::transaction(function () use ($settlement, $adminId, $proofImagePath, $adminNotes): PaymentSettlement {
            $business = $settlement->business ?? Business::findOrFail($settlement->business_id);

            // Record cash inflow & double-entry journal if not already recorded
            $method = strtolower((string) ($settlement->payment_channel ?? '')) === 'qris' ? 'qris' : 'bank_transfer';
            $merchantUserId = $settlement->reconciled_by ?: $business->users()->first()?->id;
            $this->cashLedger->recordInflow($business, $settlement->net_amount, 'settlement', $settlement->id, "Settlement {$settlement->settlement_number}", $method, $merchantUserId);
            $this->recordJournal($business, $settlement, $merchantUserId);

            $settlement->update([
                'status'           => PaymentSettlement::STATUS_COMPLETED,
                'proof_image_path' => $proofImagePath ?? $settlement->proof_image_path,
                'admin_id'         => $adminId,
                'transferred_at'   => now(),
                'admin_notes'      => $adminNotes ?? $settlement->admin_notes,
            ]);

            return $settlement->fresh(['allocations', 'admin', 'reconciler']);
        });
    }

    /**
     * Reject payout request by Admin COOCA and release allocations back to unsettled pool.
     */
    public function rejectPayout(PaymentSettlement $settlement, ?string $adminId = null, string $reason = ''): PaymentSettlement
    {
        return DB::transaction(function () use ($settlement, $adminId, $reason): PaymentSettlement {
            // Delete allocations so transactions are unlocked for merchant re-request
            $settlement->allocations()->delete();

            $settlement->update([
                'status'           => PaymentSettlement::STATUS_REJECTED,
                'admin_id'         => $adminId,
                'rejection_reason' => $reason,
            ]);

            return $settlement->fresh(['admin', 'reconciler']);
        });
    }

    private function recordJournal(Business $business, PaymentSettlement $settlement, ?string $userId): void
    {
        if (JournalEntry::where('business_id', $business->id)->where('reference_type', JournalEntry::REF_SETTLEMENT)->where('reference_id', $settlement->id)->exists()) return;
        $bank = $this->journalService->getAccount($business, '1-1002');
        $fee = ChartOfAccount::firstOrCreate(['business_id' => $business->id, 'code' => '6-6003'], ['name' => 'Beban Administrasi Gateway', 'type' => ChartOfAccount::TYPE_EXPENSE, 'normal_balance' => 'debit', 'is_system' => true, 'is_active' => true]);
        $clearing = $this->journalService->getAccount($business, '1-1005');
        if (! $bank || ! $clearing) throw new InvalidArgumentException('Akun settlement belum tersedia.');
        $entry = JournalEntry::create(['business_id' => $business->id, 'entry_number' => 'JRN-' . $settlement->settlement_number, 'entry_date' => $settlement->settlement_date, 'reference_type' => JournalEntry::REF_SETTLEMENT, 'reference_id' => $settlement->id, 'description' => "Rekonsiliasi settlement {$settlement->settlement_number}", 'total_debit' => $settlement->net_amount + $settlement->fee_amount, 'total_credit' => $settlement->gross_amount, 'created_by' => $userId]);
        JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $bank->id, 'type' => JournalEntryLine::TYPE_DEBIT, 'amount' => $settlement->net_amount, 'notes' => 'Dana settlement masuk ke bank']);
        if ($settlement->fee_amount > 0) JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $fee->id, 'type' => JournalEntryLine::TYPE_DEBIT, 'amount' => $settlement->fee_amount, 'notes' => 'Fee gateway']);
        JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $clearing->id, 'type' => JournalEntryLine::TYPE_CREDIT, 'amount' => $settlement->gross_amount, 'notes' => 'Pelepasan saldo clearing gateway']);
    }

    /**
     * Get all unsettled/pending gateway payments eligible for reconciliation.
     *
     * @return array<string, mixed>
     */
    public function getUnsettledPayments(Business $business): array
    {
        $allocatedPosPaymentIds = PaymentSettlementAllocation::where('business_id', $business->id)
            ->where('payment_type', 'pos_order_payment')
            ->pluck('payment_id')
            ->all();

        $allocatedCommerceOrderIds = PaymentSettlementAllocation::where('business_id', $business->id)
            ->where('payment_type', 'commerce_order')
            ->pluck('payment_id')
            ->all();

        // 1. POS QRIS / Gateway Payments
        $posPayments = PosOrderPayment::whereHas('order', function ($q) use ($business) {
            $q->where('business_id', $business->id)
                ->whereNotIn('status', [PosOrder::STATUS_VOIDED, PosOrder::STATUS_DRAFT_HELD, PosOrder::STATUS_REJECTED]);
        })
            ->whereNotIn('payment_method', [PosOrderPayment::METHOD_CASH])
            ->where('status', 'paid')
            ->whereNotIn('id', $allocatedPosPaymentIds)
            ->with(['order:id,order_number,order_source,order_date,customer_name_guest,payment_channel,gateway_reference,gateway_fee'])
            ->latest('created_at')
            ->get()
            ->map(function ($p) {
                return [
                    'payment_type' => 'pos_order_payment',
                    'payment_id' => $p->id,
                    'order_number' => $p->order?->order_number ?? '-',
                    'source' => 'POS / QR Meja',
                    'customer' => $p->order?->customer_name_guest ?: 'Pelanggan Meja/Kasir',
                    'date' => $p->created_at?->format('d M Y H:i'),
                    'channel' => $p->order?->payment_channel ?: strtoupper((string) $p->payment_method),
                    'reference' => $p->reference_number ?: ($p->order?->gateway_reference ?? '-'),
                    'gross_amount' => (float) $p->amount,
                    'fee_amount' => (float) ($p->fee_amount > 0 ? $p->fee_amount : ($p->order?->gateway_fee ?? 0)),
                    'net_amount' => (float) ($p->net_amount > 0 ? $p->net_amount : max(0.0, (float) $p->amount - (float) ($p->order?->gateway_fee ?? 0))),
                ];
            });

        // 2. Toko Online (CommerceOrder) Gateway Payments
        $onlineOrders = CommerceOrder::where('business_id', $business->id)
            ->where('payment_gateway', CommerceOrder::GATEWAY_TRIPAY)
            ->whereIn('status', [
                CommerceOrder::STATUS_PAID,
                CommerceOrder::STATUS_PROCESSING,
                CommerceOrder::STATUS_READY,
                CommerceOrder::STATUS_FULFILLED,
                CommerceOrder::STATUS_COMPLETED,
            ])
            ->whereNotIn('id', $allocatedCommerceOrderIds)
            ->latest('created_at')
            ->get()
            ->map(function ($o) {
                return [
                    'payment_type' => 'commerce_order',
                    'payment_id' => $o->id,
                    'order_number' => $o->order_number,
                    'source' => 'Toko Online',
                    'customer' => $o->customer_name ?: 'Pelanggan Storefront',
                    'date' => $o->created_at?->format('d M Y H:i'),
                    'channel' => $o->payment_channel ?: 'TriPay Gateway',
                    'reference' => $o->gateway_reference ?: '-',
                    'gross_amount' => (float) $o->total_amount,
                    'fee_amount' => (float) ($o->gateway_fee ?? 0),
                    'net_amount' => max(0.0, (float) $o->total_amount - (float) ($o->gateway_fee ?? 0)),
                ];
            });

        $items = $posPayments->concat($onlineOrders)->values();
        $totalGross = (float) $items->sum('gross_amount');
        $totalFee = (float) $items->sum('fee_amount');
        $totalNet = (float) $items->sum('net_amount');

        return [
            'items' => $items,
            'summary' => [
                'count' => $items->count(),
                'total_gross' => $totalGross,
                'total_fee' => $totalFee,
                'total_net' => $totalNet,
            ],
        ];
    }
}
