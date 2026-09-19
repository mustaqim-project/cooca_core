<?php

declare(strict_types=1);

use App\Models\BillingPackage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $packages = [
            [
                'type' => BillingPackage::TYPE_SUBSCRIPTION,
                'code' => 'standard-monthly',
                'name' => 'Standard Bulanan',
                'description' => 'Paket Standard 30 hari untuk UMKM pemula (100 produk, 20 resep, 1.000 transaksi kasir POS).',
                'price' => 29000,
                'duration_days' => 30,
                'token_quantity' => null,
                'storage_bytes' => null,
                'token_expiry_days' => null,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'type' => BillingPackage::TYPE_SUBSCRIPTION,
                'code' => 'standard-annual',
                'name' => 'Standard Tahunan',
                'description' => 'Paket Standard 365 hari hemat 2 bulan (100 produk, 20 resep, 1.000 transaksi kasir POS).',
                'price' => 290000,
                'duration_days' => 365,
                'token_quantity' => null,
                'storage_bytes' => null,
                'token_expiry_days' => null,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'type' => BillingPackage::TYPE_SUBSCRIPTION,
                'code' => 'premium-monthly',
                'name' => 'Premium Bulanan',
                'description' => 'Paket Premium 30 hari (Produk, Resep & Kasir Unlimited, 5 Lokasi, 10 Staf, KDS, Transfer Stok, 200 WA).',
                'price' => 89000,
                'duration_days' => 30,
                'token_quantity' => null,
                'storage_bytes' => null,
                'token_expiry_days' => null,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'type' => BillingPackage::TYPE_SUBSCRIPTION,
                'code' => 'premium-annual',
                'name' => 'Premium Tahunan',
                'description' => 'Paket Premium 365 hari hemat 2 bulan (Produk, Resep & Kasir Unlimited, 5 Lokasi, 10 Staf, KDS, Transfer Stok, 200 WA).',
                'price' => 890000,
                'duration_days' => 365,
                'token_quantity' => null,
                'storage_bytes' => null,
                'token_expiry_days' => null,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'type' => BillingPackage::TYPE_SUBSCRIPTION,
                'code' => 'prestige-monthly',
                'name' => 'Prestige Bulanan',
                'description' => 'Paket Prestige 30 hari Enterprise UMKM (Bisnis & Lokasi Unlimited, PPh 21 TER, Auto Slip Gaji WA, 1.000 WA).',
                'price' => 199000,
                'duration_days' => 30,
                'token_quantity' => null,
                'storage_bytes' => null,
                'token_expiry_days' => null,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'type' => BillingPackage::TYPE_SUBSCRIPTION,
                'code' => 'prestige-annual',
                'name' => 'Prestige Tahunan',
                'description' => 'Paket Prestige 365 hari hemat 2 bulan Enterprise UMKM (Bisnis & Lokasi Unlimited, PPh 21 TER, Auto Slip Gaji WA, 1.000 WA).',
                'price' => 1990000,
                'duration_days' => 365,
                'token_quantity' => null,
                'storage_bytes' => null,
                'token_expiry_days' => null,
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($packages as $pkg) {
            $existing = DB::table('billing_packages')->where('type', $pkg['type'])->where('code', $pkg['code'])->first();
            if ($existing) {
                DB::table('billing_packages')->where('id', $existing->id)->update(array_merge($pkg, ['updated_at' => $now]));
            } else {
                DB::table('billing_packages')->insert(array_merge($pkg, [
                    'id' => (string) Str::uuid(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }
    }

    public function down(): void
    {
        DB::table('billing_packages')->whereIn('code', [
            'standard-monthly',
            'standard-annual',
            'premium-monthly',
            'premium-annual',
            'prestige-monthly',
            'prestige-annual',
        ])->delete();
    }
};
