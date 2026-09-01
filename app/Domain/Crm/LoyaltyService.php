<?php

declare(strict_types=1);

namespace App\Domain\Crm;

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
        $earned = $this->calculatePointsEarned((float) $order->total_amount);
        if ($earned <= 0) {
            return 0;
        }

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
        });

        return $earned;
    }

    /**
     * Redeem customer points on an order.
     */
    public function redeemPointsForOrder(Customer $customer, PosOrder $order, int $pointsToRedeem): float
    {
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
     * Record customer credit repayment.
     */
    public function recordCustomerCreditPayment(Customer $customer, float $amount, ?string $notes = null, ?User $user = null): void
    {
        DB::transaction(function () use ($customer, $amount, $notes, $user) {
            $newCredit = max(0.0, (float) $customer->current_credit_balance - $amount);

            $customer->update([
                'current_credit_balance' => $newCredit,
            ]);

            CustomerCreditTransaction::create([
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

        $bizName = $order->business?->name ?? 'Si-Cost POS';
        $currencySymbol = $order->business?->currency_symbol ?? 'Rp';

        $text = "*STRUK PEMBELIAN DIGITAL*\n";
        $text .= "{$bizName}\n";
        $text .= "--------------------------------\n";
        $text .= "No. Struk : #{$order->order_number}\n";
        $text .= "Tanggal   : {$order->order_date->format('d/m/Y')}\n";
        $text .= "Kasir     : " . ($order->user?->name ?? 'Kasir') . "\n";
        if ($order->customer) {
            $text .= "Pelanggan : {$order->customer->name} ({$order->customer->membership_tier})\n";
        }
        $text .= "--------------------------------\n";

        foreach ($order->items as $item) {
            $qty = rtrim(rtrim((string) $item->quantity, '0'), '.');
            $lineSubtotal = number_format($item->total_price, 0, ',', '.');
            $unitPrice = number_format($item->unit_price, 0, ',', '.');
            $text .= "{$item->product_name}\n";
            $text .= "  {$qty} x {$currencySymbol}{$unitPrice} = {$currencySymbol}{$lineSubtotal}\n";
        }

        $text .= "--------------------------------\n";
        $text .= "Subtotal   : {$currencySymbol}" . number_format($order->subtotal, 0, ',', '.') . "\n";

        if ($order->discount_amount > 0 || $order->voucher_discount_amount > 0) {
            $disc = $order->discount_amount + $order->voucher_discount_amount;
            $text .= "Diskon     : -{$currencySymbol}" . number_format($disc, 0, ',', '.') . "\n";
        }

        if ($order->tax_amount > 0) {
            $text .= "Pajak PPN  : {$currencySymbol}" . number_format($order->tax_amount, 0, ',', '.') . "\n";
        }

        if ($order->service_charge_amount > 0) {
            $text .= "Service    : {$currencySymbol}" . number_format($order->service_charge_amount, 0, ',', '.') . "\n";
        }

        $text .= "*TOTAL     : {$currencySymbol}" . number_format($order->total_amount, 0, ',', '.') . "*\n";
        $text .= "Bayar      : {$currencySymbol}" . number_format($order->paid_amount, 0, ',', '.') . "\n";
        $text .= "Kembalian  : {$currencySymbol}" . number_format($order->change_amount, 0, ',', '.') . "\n";

        if ($order->points_earned > 0) {
            $text .= "Poin Baru  : +{$order->points_earned} Poin\n";
        }

        $text .= "--------------------------------\n";
        $text .= "Terima kasih atas kunjungan Anda!\n";

        return 'https://wa.me/' . $cleanPhone . '?text=' . rawurlencode($text);
    }
}
