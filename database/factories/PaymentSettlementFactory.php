<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Business;
use App\Models\PaymentSettlement;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PaymentSettlement>
 */
final class PaymentSettlementFactory extends Factory
{
    protected $model = PaymentSettlement::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gross = $this->faker->randomFloat(2, 50000, 5000000);
        $fee = round($gross * 0.007 + 750, 2);

        return [
            'id'                => Str::uuid()->toString(),
            'business_id'       => Business::factory(),
            'settlement_number' => 'SETTLE-' . Carbon::today()->format('Ymd') . '-' . strtoupper(Str::random(5)),
            'settlement_date'   => Carbon::today()->toDateString(),
            'payment_channel'   => $this->faker->randomElement(['qris', 'cooca_pay', 'tripay']),
            'gross_amount'      => $gross,
            'fee_amount'        => $fee,
            'net_amount'        => round($gross - $fee, 2),
            'destination_bank'  => 'BCA - 1234567890 (a.n Test)',
            'status'            => PaymentSettlement::STATUS_PENDING,
            'notes'             => $this->faker->optional()->sentence(),
            'payout_mode'       => PaymentSettlement::PAYOUT_MODE_MANUAL,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status'         => PaymentSettlement::STATUS_COMPLETED,
            'transferred_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status'           => PaymentSettlement::STATUS_REJECTED,
            'rejection_reason' => 'Rekening tidak sesuai.',
        ]);
    }

    public function autoPayoutH1(): static
    {
        return $this->state(fn () => [
            'payout_mode'        => PaymentSettlement::PAYOUT_MODE_AUTO_H1,
            'scheduled_payout_at' => Carbon::tomorrow('Asia/Jakarta')->setHour(9)->utc(),
        ]);
    }
}
