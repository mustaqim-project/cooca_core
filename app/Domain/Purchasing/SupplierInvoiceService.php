<?php

declare(strict_types=1);

namespace App\Domain\Purchasing;

use App\Domain\Accounting\AutoJournalService;
use App\Domain\Finance\CashLedgerService;
use App\Models\GoodsReceipt;
use App\Models\SupplierInvoice;
use App\Models\SupplierPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class SupplierInvoiceService
{
    public function __construct(
        private readonly AutoJournalService $journalService = new AutoJournalService,
        private readonly CashLedgerService $cashLedgerService = new CashLedgerService,
    ) {}

    public function createFromGoodsReceipt(GoodsReceipt $receipt): SupplierInvoice
    {
        return DB::transaction(function () use ($receipt): SupplierInvoice {
            $receipt = GoodsReceipt::query()->with('supplier')->lockForUpdate()->findOrFail($receipt->id);
            if (! $receipt->supplier_id) {
                throw new InvalidArgumentException('Goods Receipt harus memiliki supplier.');
            }

            $existing = SupplierInvoice::where('business_id', $receipt->business_id)
                ->where('goods_receipt_id', $receipt->id)
                ->lockForUpdate()
                ->first();
            if ($existing) {
                return $existing;
            }

            $total = (float) $receipt->items()->selectRaw('COALESCE(SUM(quantity * unit_cost), 0) as total')->value('total');
            if ($total <= 0) {
                throw new InvalidArgumentException('Goods Receipt harus memiliki nilai penerimaan lebih besar dari nol.');
            }

            $invoice = SupplierInvoice::create([
                'business_id' => $receipt->business_id,
                'supplier_id' => $receipt->supplier_id,
                'goods_receipt_id' => $receipt->id,
                'purchase_order_id' => $receipt->purchase_order_id,
                'invoice_number' => 'AP-' . $receipt->receipt_number,
                'invoice_date' => $receipt->receipt_date,
                    'due_date' => $receipt->receipt_date->copy()->addDays(30),
                'total_amount' => $total,
                'paid_amount' => 0.0,
                'balance_due' => $total,
                'status' => SupplierInvoice::STATUS_UNPAID,
            ]);

            $this->journalService->recordGoodsReceiptJournal($receipt);

            return $invoice;
        });
    }

    /**
     * @param array<string, mixed> $paymentData
     */
    public function recordPayment(SupplierInvoice $invoice, array $paymentData): SupplierPayment
    {
        $amount = (float) ($paymentData['amount'] ?? 0);
        if ($amount <= 0) {
            throw new InvalidArgumentException('Nominal pembayaran harus lebih besar dari nol.');
        }

        return DB::transaction(function () use ($invoice, $paymentData, $amount): SupplierPayment {
            $invoice = SupplierInvoice::query()->lockForUpdate()->findOrFail($invoice->id);
            if ($invoice->status === SupplierInvoice::STATUS_VOID) {
                throw new InvalidArgumentException('Hutang supplier yang void tidak dapat dibayar.');
            }
            if ($amount > ((float) $invoice->balance_due + 0.005)) {
                throw new InvalidArgumentException('Pembayaran melebihi saldo hutang supplier.');
            }

            $payment = SupplierPayment::create([
                'business_id' => $invoice->business_id,
                'supplier_invoice_id' => $invoice->id,
                'payment_number' => $paymentData['payment_number'] ?? 'SP-' . now()->format('Ym') . '-' . Str::upper(Str::random(8)),
                'payment_date' => ! empty($paymentData['payment_date'])
                    ? Carbon::parse($paymentData['payment_date'])->toDateString()
                    : Carbon::today()->toDateString(),
                'amount' => $amount,
                'payment_method' => $paymentData['payment_method'] ?? 'bank_transfer',
                'reference_number' => $paymentData['reference_number'] ?? null,
                'notes' => $paymentData['notes'] ?? null,
                'created_by' => $paymentData['created_by'] ?? null,
            ]);

            $paidAmount = (float) $invoice->paid_amount + $amount;
            $balanceDue = max(0.0, (float) $invoice->total_amount - $paidAmount);
            $invoice->update([
                'paid_amount' => $paidAmount,
                'balance_due' => $balanceDue,
                'status' => $balanceDue <= 0.005
                    ? SupplierInvoice::STATUS_PAID
                    : SupplierInvoice::STATUS_PARTIAL,
            ]);

            $this->journalService->recordSupplierPaymentJournal($payment);
            $this->cashLedgerService->recordOutflow($invoice->business, $amount, 'supplier_payment', $payment->id, "Pembayaran hutang #{$invoice->invoice_number}", $payment->payment_method, $payment->created_by);

            return $payment;
        });
    }
}
