<?php

declare(strict_types=1);

namespace App\Domain\Crm;

use App\Models\AuditLog;
use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\CustomerCreditTransaction;
use App\Models\CustomerPointHistory;
use App\Models\PosOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class LoyaltyService
{
    public const SPEND_PER_POINT = 10000.0; // Rp10.000 = 1 Point

    public const POINT_VALUE_RUPIAH = 100.0; // 1 Point = Rp100 discount

    /**
     * Calculate points earned for a purchase amount.
     */
    public function calculatePointsEarned(float $amount): int
    {
        if ($amount < self::SPEND_PER_POINT) {
            return 0;
        }
        return (int) floor($amount / self::SPEND_PER_POINT);
    }

    /**
     * Calculate Rupiah discount value for given points.
     */
    public function calculatePointsDiscount(int $points): float
    {
        return (float) ($points * self::POINT_VALUE_RUPIAH);
    }

    /**
     * Process points earning and tier update for a customer from an order.
     */
    public function awardPointsForOrder(Customer $customer, PosOrder $order): int
    {
        abort_unless($customer->business_id === $order->business_id, 403, 'Customer and order must belong to the same business.');

        $earned = $this->calculatePointsEarned((float) $order->total_amount);

        DB::transaction(function () use ($customer, $order, $earned) {
            $newBalance = (int) $customer->points_balance + $earned;
            $newTotalSpent = (float) $customer->total_spent + (float) $order->total_amount;
            $newOrderCount = (int) $customer->total_orders_count + 1;

            // Tier evaluation
            $tier = match (true) {
                $newTotalSpent >= 15000000 => 'platinum',
                $newTotalSpent >= 5000000 => 'gold',
                $newTotalSpent >= 1000000 => 'silver',
                default => 'bronze',
            };

            $customer->update([
                'points_balance' => $newBalance,
                'total_spent' => $newTotalSpent,
                'total_orders_count' => $newOrderCount,
                'membership_tier' => $tier,
            ]);

            if ($earned > 0) {
                CustomerPointHistory::create([
                    'business_id' => $customer->business_id,
                    'customer_id' => $customer->id,
                    'points_change' => $earned,
                    'type' => CustomerPointHistory::TYPE_POS_EARN,
                    'reference_id' => $order->id,
                    'balance_after' => $newBalance,
                    'notes' => "Perolehan poin dari transaksi POS #{$order->order_number}",
                ]);

                $order->update(['points_earned' => $earned]);
            }
        });

        return $earned;
    }

    /**
     * Redeem customer points on an order.
     */
    public function redeemPointsForOrder(Customer $customer, PosOrder $order, int $pointsToRedeem): float
    {
        abort_unless($customer->business_id === $order->business_id, 403, 'Customer and order must belong to the same business.');

        if ($pointsToRedeem <= 0 || (int) $customer->points_balance < $pointsToRedeem) {
            return 0.0;
        }

        $discount = $this->calculatePointsDiscount($pointsToRedeem);

        DB::transaction(function () use ($customer, $order, $pointsToRedeem, $discount) {
            $newBalance = (int) $customer->points_balance - $pointsToRedeem;

            $customer->update([
                'points_balance' => $newBalance,
            ]);

            CustomerPointHistory::create([
                'business_id' => $customer->business_id,
                'customer_id' => $customer->id,
                'points_change' => -$pointsToRedeem,
                'type' => CustomerPointHistory::TYPE_POS_REDEEM,
                'reference_id' => $order->id,
                'balance_after' => $newBalance,
                'notes' => "Penukaran {$pointsToRedeem} poin pada transaksi #{$order->order_number}",
            ]);

            $order->update([
                'points_redeemed' => $pointsToRedeem,
                'points_discount_amount' => $discount,
            ]);
        });

        return $discount;
    }

    /**
     * Record store credit charge (piutang) on POS order.
     */
    public function recordCustomerCreditCharge(Customer $customer, PosOrder $order, float $amount, ?User $user = null): void
    {
        abort_unless($customer->business_id === $order->business_id, 403, 'Customer and order must belong to the same business.');

        DB::transaction(function () use ($customer, $order, $amount, $user) {
            $newCredit = (float) $customer->current_credit_balance + $amount;

            $customer->update([
                'current_credit_balance' => $newCredit,
            ]);

            CustomerCreditTransaction::create([
                'business_id' => $customer->business_id,
                'customer_id' => $customer->id,
                'type' => CustomerCreditTransaction::TYPE_CHARGE,
                'amount' => $amount,
                'reference_type' => 'pos_order',
                'reference_id' => $order->id,
                'balance_after' => $newCredit,
                'notes' => "Belanja POS tempo (kredit) #{$order->order_number}",
                'created_by' => $user?->id,
            ]);
        });
    }

    /**
     * Record customer credit repayment (Anti-Lapping Shield & Ledger Integration).
     */
    public function recordCustomerCreditPayment(Customer $customer, float $amount, ?string $notes = null, ?User $user = null): CustomerCreditTransaction
    {
        if ($user && ! empty($user->active_business_id)) {
            abort_unless($customer->business_id === $user->active_business_id, 403, 'Customer and user active business must match.');
        }

        if ($amount > (float) $customer->current_credit_balance) {
            throw new \InvalidArgumentException('Jumlah pembayaran piutang (Rp ' . number_format($amount, 0, ',', '.') . ') tidak boleh melebihi sisa piutang aktif (Rp ' . number_format((float) $customer->current_credit_balance, 0, ',', '.') . ').');
        }

        return DB::transaction(function () use ($customer, $amount, $notes, $user) {
            $newCredit = max(0.0, (float) $customer->current_credit_balance - $amount);

            $customer->update([
                'current_credit_balance' => $newCredit,
            ]);

            $transaction = CustomerCreditTransaction::create([
                'business_id' => $customer->business_id,
                'customer_id' => $customer->id,
                'type' => CustomerCreditTransaction::TYPE_PAYMENT,
                'amount' => $amount,
                'reference_type' => 'manual_payment',
                'reference_id' => null,
                'balance_after' => $newCredit,
                'notes' => $notes ?? 'Pelunasan piutang pelanggan',
                'created_by' => $user?->id,
            ]);

            // Auto-Journal ke Buku Kas Aktif Tenant (Anti-Lapping & Ledger Sync)
            $cashAccount = CashAccount::where('business_id', $customer->business_id)->first();
            if (! $cashAccount) {
                $cashAccount = CashAccount::create([
                    'business_id' => $customer->business_id,
                    'name' => 'Kas Utama',
                    'type' => 'cash',
                    'current_balance' => 0,
                    'is_active' => true,
                ]);
            }
            $newCashBalance = (float) $cashAccount->current_balance + $amount;
            $cashAccount->update(['current_balance' => $newCashBalance]);

            CashTransaction::create([
                'business_id' => $customer->business_id,
                'cash_account_id' => $cashAccount->id,
                'type' => CashTransaction::TYPE_IN,
                'amount' => $amount,
                'balance_after' => $newCashBalance,
                'reference_type' => 'customer_credit_repayment',
                'reference_id' => $transaction->id,
                'description' => "Pelunasan piutang pelanggan: {$customer->name}" . ($notes ? " ({$notes})" : ''),
                'transaction_date' => now(),
                'created_by' => $user?->id,
            ]);

            // Pencatatan Audit Trail Immutable
            AuditLog::create([
                'business_id' => $customer->business_id,
                'user_id' => $user?->id,
                'auditable_type' => Customer::class,
                'auditable_id' => $customer->id,
                'action' => 'customer.credit_payment_recorded',
                'risk_level' => AuditLog::RISK_LOW,
                'notes' => "Pelunasan piutang pelanggan {$customer->name} sebesar Rp " . number_format($amount, 0, ',', '.'),
                'new_values' => [
                    'customer_id' => $customer->id,
                    'amount' => $amount,
                    'balance_after' => $newCredit,
                ],
                'created_at' => now(),
            ]);

            return $transaction;
        });
    }

    /**
     * Format a clean WhatsApp receipt message and generate a wa.me direct link.
     */
    public function generateWhatsAppReceiptUrl(PosOrder $order, ?string $phone = null): string
    {
        $targetPhone = $phone ?? $order->customer?->phone ?? '';
        $cleanPhone = preg_replace('/[^0-9]/', '', $targetPhone);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }

        $business    = $order->business;
        $bizName     = $business?->name ?? 'COOCA POS';
        $custName    = $order->customer?->name ?? $order->customer_name_guest ?? null;
        $cashierName = $order->user?->name ?? 'Kasir';
        $orderDate   = $order->order_date ? $order->order_date->format('d/m/Y H:i') : now()->format('d/m/Y H:i');
        $receiptUrl  = route('public.receipt', $order->id);
        $footerNote  = $business?->pos_receipt_footer_note ?? '';

        $template = $business?->pos_receipt_wa_template;
        if (! $template) {
            $session  = \App\Models\WhatsAppSession::where('business_id', $business?->id)->first();
            $template = $session?->receipt_template;
        }

        if (! empty(trim((string) $template))) {
            $replacements = [
                '{business_name}' => $bizName,
                '{customer_name}' => $custName ?? 'Pelanggan',
                '{order_number}'  => $order->order_number,
                '{date}'          => $orderDate,
                '{cashier_name}'  => $cashierName,
                '{receipt_link}'  => $receiptUrl,
                '{footer_note}'   => $footerNote,
            ];

            $text = strtr($template, $replacements);
            if (! str_contains($template, '{receipt_link}')) {
                $text .= "\n\nLihat Struk: " . $receiptUrl;
            }
        } else {
            $greeting = $custName ? "Halo Kak *{$custName}*! 🙏\n" : "Halo! 🙏\n";

            $text  = "🧾 *STRUK PEMBELIAN*\n";
            $text .= "*{$bizName}*\n\n";
            $text .= $greeting;
            $text .= "Terima kasih banyak telah berbelanja di *{$bizName}*.\n\n";
            $text .= "Berikut tautan e-struk transaksi Anda:\n";
            $text .= $receiptUrl . "\n\n";

            if ($footerNote) {
                $text .= $footerNote . "\n\n";
            }

            $text .= "Semoga hari Anda menyenangkan! ✨";
        }

        return 'https://wa.me/' . $cleanPhone . '?text=' . rawurlencode($text);
    }

    /**
     * Generate an official WhatsApp digital receipt URL for customer credit repayment (Anti-Lapping Shield).
     */
    public function generateCreditPaymentWhatsAppReceiptUrl(Customer $customer, CustomerCreditTransaction $transaction, ?string $phone = null): string
    {
        $targetPhone = $phone ?? $customer->phone ?? '';
        $cleanPhone = preg_replace('/[^0-9]/', '', (string) $targetPhone);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }

        $business = $customer->business;
        $bizName = $business?->name ?? 'COOCA';
        $custName = $customer->name;
        $date = $transaction->created_at ? $transaction->created_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i');
        $amountFormatted = number_format((float) $transaction->amount, 0, ',', '.');
        $balanceFormatted = number_format((float) $transaction->balance_after, 0, ',', '.');
        $notes = $transaction->notes ?? 'Pelunasan piutang';

        $text = "*BUKTI PEMBAYARAN PIUTANG*\n";
        $text .= "*{$bizName}*\n\n";
        $text .= "Yth. *{$custName}*,\n";
        $text .= "Pembayaran piutang Anda telah berhasil kami terima dan dibukukan secara resmi.\n\n";
        $text .= "Detail Pembayaran:\n";
        $text .= "• Tanggal: {$date}\n";
        $text .= "• Jumlah Dibayar: Rp {$amountFormatted}\n";
        $text .= "• Sisa Saldo Piutang: Rp {$balanceFormatted}\n";
        if (! empty($notes)) {
            $text .= "• Catatan: {$notes}\n";
        }
        $text .= "\nTerima kasih atas kerja sama dan kepercayaan Anda kepada *{$bizName}*.\n";
        $text .= "Bukti ini sah dan diterbitkan secara digital oleh sistem COOCA.";

        return 'https://wa.me/' . $cleanPhone . '?text=' . rawurlencode($text);
    }
}
