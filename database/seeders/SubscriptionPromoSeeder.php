<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SubscriptionPromo;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

final class SubscriptionPromoSeeder extends Seeder
{
    /**
     * Seed best promotional discount schemes for SaaS subscriptions.
     */
    public function run(): void
    {
        $promos = [
            [
                'code' => 'PELOPOR30',
                'name' => 'Promo Pelopor UMKM 30%',
                'description' => 'Diskon 30% spesial pembukaan untuk langganan pertama UMKM di platform Cooca.',
                'discount_type' => SubscriptionPromo::TYPE_PERCENTAGE,
                'discount_value' => 30.0,
                'max_discount_amount' => 100000.0,
                'min_order_amount' => 0.0,
                'valid_from' => Carbon::now()->subDay()->toDateString(),
                'valid_until' => Carbon::now()->addMonths(6)->toDateString(),
                'usage_limit' => 200,
                'used_count' => 0,
                'usage_per_business_limit' => 1,
                'applicable_tiers' => ['standard', 'premium', 'prestige'],
                'applicable_cycles' => ['monthly', 'annual'],
                'is_active' => true,
            ],
            [
                'code' => 'TAHUNANHEMAT',
                'name' => 'Ekstra Diskon Komitmen Tahunan',
                'description' => 'Potongan harga flat Rp 50.000 ekstra untuk semua paket langganan 1 tahun (Annual Commitment).',
                'discount_type' => SubscriptionPromo::TYPE_FIXED,
                'discount_value' => 50000.0,
                'max_discount_amount' => null,
                'min_order_amount' => 250000.0,
                'valid_from' => Carbon::now()->subDay()->toDateString(),
                'valid_until' => Carbon::now()->addYear()->toDateString(),
                'usage_limit' => 500,
                'used_count' => 0,
                'usage_per_business_limit' => 1,
                'applicable_tiers' => ['standard', 'premium', 'prestige'],
                'applicable_cycles' => ['annual'],
                'is_active' => true,
            ],
            [
                'code' => 'UPGRADEPREMIUM',
                'name' => 'Booster Upgrade Premium 25%',
                'description' => 'Diskon 25% untuk percepatan ekspansi bisnis multi-cabang ke paket Premium atau Prestige.',
                'discount_type' => SubscriptionPromo::TYPE_PERCENTAGE,
                'discount_value' => 25.0,
                'max_discount_amount' => 150000.0,
                'min_order_amount' => 80000.0,
                'valid_from' => Carbon::now()->subDay()->toDateString(),
                'valid_until' => Carbon::now()->addMonths(6)->toDateString(),
                'usage_limit' => 300,
                'used_count' => 0,
                'usage_per_business_limit' => 1,
                'applicable_tiers' => ['premium', 'prestige'],
                'applicable_cycles' => ['monthly', 'annual'],
                'is_active' => true,
            ],
            [
                'code' => 'KOMUNITASUMKM',
                'name' => 'Voucher Komunitas Mitra Usaha',
                'description' => 'Potongan subsidi flat Rp 20.000 langsung tanpa batas minimum transaksi bagi anggota paguyuban UMKM.',
                'discount_type' => SubscriptionPromo::TYPE_FIXED,
                'discount_value' => 20000.0,
                'max_discount_amount' => null,
                'min_order_amount' => 0.0,
                'valid_from' => Carbon::now()->subDay()->toDateString(),
                'valid_until' => Carbon::now()->addYear()->toDateString(),
                'usage_limit' => 1000,
                'used_count' => 0,
                'usage_per_business_limit' => 2,
                'applicable_tiers' => ['standard', 'premium', 'prestige'],
                'applicable_cycles' => ['monthly', 'annual'],
                'is_active' => true,
            ],
            [
                'code' => 'FLASHDEAL50',
                'name' => 'Flash Sale Cooca 50%',
                'description' => 'Diskon kilat 50% kuota terbatas 50 transaksi pertama untuk pemilik usaha rintisan.',
                'discount_type' => SubscriptionPromo::TYPE_PERCENTAGE,
                'discount_value' => 50.0,
                'max_discount_amount' => 75000.0,
                'min_order_amount' => 0.0,
                'valid_from' => Carbon::now()->subDay()->toDateString(),
                'valid_until' => Carbon::now()->addMonths(3)->toDateString(),
                'usage_limit' => 50,
                'used_count' => 0,
                'usage_per_business_limit' => 1,
                'applicable_tiers' => ['standard', 'premium'],
                'applicable_cycles' => ['monthly'],
                'is_active' => true,
            ],
            [
                'code' => 'COOCAGRATIS1BLN',
                'name' => 'Voucher Bebas Biaya 100% (1 Bulan Standard)',
                'description' => 'Spesial kemitraan & pengujian: 100% Bebas Biaya langganan paket Standard 1 Bulan tanpa kartu kredit.',
                'discount_type' => SubscriptionPromo::TYPE_FIXED,
                'discount_value' => 29000.0,
                'max_discount_amount' => null,
                'min_order_amount' => 0.0,
                'valid_from' => Carbon::now()->subDay()->toDateString(),
                'valid_until' => Carbon::now()->addYear()->toDateString(),
                'usage_limit' => 50,
                'used_count' => 0,
                'usage_per_business_limit' => 1,
                'applicable_tiers' => ['standard'],
                'applicable_cycles' => ['monthly'],
                'is_active' => true,
            ],
        ];

        foreach ($promos as $promoData) {
            SubscriptionPromo::updateOrCreate(
                ['code' => $promoData['code']],
                $promoData
            );
        }
    }
}
