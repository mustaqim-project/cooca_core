<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Template\BusinessTemplateService;
use App\Models\Business;
use App\Models\BusinessTypeTemplate;
use App\Models\CostComponent;
use App\Models\CostModel;
use App\Models\LaborRate;
use App\Models\Machine;
use App\Models\Material;
use App\Models\Overhead;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds for demo & test users with complete sample businesses.
     */
    public function run(): void
    {
        $templateService = new BusinessTemplateService;

        // ─────────────────────────────────────────────────────────────
        // 1. Akun Demo Utama F&B (Restoran Nusantara)
        // ─────────────────────────────────────────────────────────────
        $user1 = User::updateOrCreate(
            ['email' => 'demo@cooca.id'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $biz1 = Business::updateOrCreate(
            ['slug' => 'restoran-nusantara-rasa'],
            [
                'name' => 'Restoran Nusantara Rasa',
                'currency' => 'IDR',
                'currency_precision' => 0,
                'rounding_strategy' => Business::ROUNDING_ROUND_100,
                'phone' => '0812-3456-7890',
                'email' => 'finance@nusantararasa.id',
                'address' => 'Jl. Sabang No. 18, Menteng, Jakarta Pusat',
                'tax_identification_number' => '01.345.678.9-021.000',
                'bank_name' => 'Bank Central Asia (BCA)',
                'bank_account_number' => '542-019-8821',
                'bank_account_holder' => 'PT Nusantara Rasa Sejahtera',
            ]
        );

        // Hubungkan membership
        $biz1->users()->syncWithoutDetaching([
            $user1->id => ['id' => (string) Str::uuid(), 'role' => 'owner'],
        ]);

        $user1->update(['active_business_id' => $biz1->id]);

        // Terapkan Template F&B Resto jika belum ada komponen
        $fnbTemplate = BusinessTypeTemplate::where('code', 'fnb_resto')->first();
        if ($fnbTemplate && CostComponent::where('business_id', $biz1->id)->count() === 0) {
            $templateService->apply($biz1, $fnbTemplate);
        }

        // Buat Data Sample F&B Lengkap
        $this->seedSampleFnbData($biz1);

        // ─────────────────────────────────────────────────────────────
        // 2. Akun Demo Manufaktur & Fashion (Konveksi Mandiri)
        // ─────────────────────────────────────────────────────────────
        $user2 = User::updateOrCreate(
            ['email' => 'fashion@cooca.id'],
            [
                'name' => 'Siti Aminah',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $biz2 = Business::updateOrCreate(
            ['slug' => 'konveksi-mandiri-kreasi'],
            [
                'name' => 'Konveksi Mandiri Kreasi',
                'currency' => 'IDR',
                'currency_precision' => 0,
                'rounding_strategy' => Business::ROUNDING_ROUND_500,
                'phone' => '0821-9876-5432',
                'email' => 'order@mandirikreasi.com',
                'address' => 'Kawasan Industri Rancaekek Blok B3, Bandung',
                'tax_identification_number' => '02.456.789.1-422.000',
                'bank_name' => 'Bank Mandiri',
                'bank_account_number' => '131-00-887766-5',
                'bank_account_holder' => 'CV Mandiri Kreasi Tekstil',
            ]
        );

        $biz2->users()->syncWithoutDetaching([
            $user2->id => ['id' => (string) Str::uuid(), 'role' => 'owner'],
        ]);

        $user2->update(['active_business_id' => $biz2->id]);

        $apparelTemplate = BusinessTypeTemplate::where('code', 'apparel_konveksi')->first();
        if ($apparelTemplate && CostComponent::where('business_id', $biz2->id)->count() === 0) {
            $templateService->apply($biz2, $apparelTemplate);
        }

        // ─────────────────────────────────────────────────────────────
        // 3. Akun Demo Bakery & Pastry (Roti Prima Delima)
        // ─────────────────────────────────────────────────────────────
        $user3 = User::updateOrCreate(
            ['email' => 'bakery@cooca.id'],
            [
                'name' => 'Chef Hendra',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $biz3 = Business::updateOrCreate(
            ['slug' => 'roti-prima-delima'],
            [
                'name' => 'Roti Prima Delima Bakery',
                'currency' => 'IDR',
                'currency_precision' => 0,
                'rounding_strategy' => Business::ROUNDING_ROUND_100,
                'phone' => '0813-7788-9900',
                'email' => 'halo@primadelima.com',
                'address' => 'Jl. Pandanaran No. 56, Semarang',
                'tax_identification_number' => '03.567.890.2-511.000',
                'bank_name' => 'Bank BNI',
                'bank_account_number' => '045-8899-771',
                'bank_account_holder' => 'Prima Delima Bakery Official',
            ]
        );

        $biz3->users()->syncWithoutDetaching([
            $user3->id => ['id' => (string) Str::uuid(), 'role' => 'owner'],
        ]);

        $user3->update(['active_business_id' => $biz3->id]);

        $bakeryTemplate = BusinessTypeTemplate::where('code', 'fnb_bakery')->first();
        if ($bakeryTemplate && CostComponent::where('business_id', $biz3->id)->count() === 0) {
            $templateService->apply($biz3, $bakeryTemplate);
        }
    }

    /**
     * Seed sample rich materials, labor rates, machines, overhead, products & BOM.
     */
    private function seedSampleFnbData(Business $biz): void
    {
        $kg = Unit::where('code', 'kg')->first();
        $g = Unit::where('code', 'g')->first();
        $pcs = Unit::where('code', 'pcs')->first();
        $ml = Unit::where('code', 'ml')->first();
        $l = Unit::where('code', 'l')->first();
        $portion = Unit::where('code', 'porsi')->first() ?? $pcs;
        $pack = Unit::where('code', 'pack')->first() ?? $pcs;
        $hour = Unit::where('code', 'jam')->first() ?? $pcs;

        if (! $kg || ! $portion) {
            return;
        }

        // ─────────────────────────────────────────────────────────────
        // 1. Kategori Produk Restoran
        // ─────────────────────────────────────────────────────────────
        $catMakanan = \App\Models\ProductCategory::updateOrCreate(
            ['business_id' => $biz->id, 'slug' => 'makanan-utama'],
            ['name' => 'Makanan Utama (Main Course)']
        );
        $catMinuman = \App\Models\ProductCategory::updateOrCreate(
            ['business_id' => $biz->id, 'slug' => 'minuman-beverage'],
            ['name' => 'Minuman & Kopi (Beverage)']
        );
        $catSnack = \App\Models\ProductCategory::updateOrCreate(
            ['business_id' => $biz->id, 'slug' => 'snack-dessert'],
            ['name' => 'Snack & Makanan Ringan']
        );
        $catJasa = \App\Models\ProductCategory::updateOrCreate(
            ['business_id' => $biz->id, 'slug' => 'jasa-sewa-fasilitas'],
            ['name' => 'Jasa, Sewa & Layanan Event']
        );

        // ─────────────────────────────────────────────────────────────
        // 2. Data Supplier Bahan Baku & Kemasan
        // ─────────────────────────────────────────────────────────────
        $supPasar = \App\Models\Supplier::updateOrCreate(
            ['business_id' => $biz->id, 'slug' => 'supplier-pasar-induk-segar'],
            ['name' => 'Supplier Sayur & Daging Segar Pasar Induk', 'contact_person' => 'Pak Haji Syarif', 'phone' => '081234567890']
        );
        $supKopi = \App\Models\Supplier::updateOrCreate(
            ['business_id' => $biz->id, 'slug' => 'roastery-kopi-nusantara'],
            ['name' => 'PT Roastery Kopi Nusantara', 'contact_person' => 'Bpk. Adit', 'phone' => '081898765432']
        );
        $supKemasan = \App\Models\Supplier::updateOrCreate(
            ['business_id' => $biz->id, 'slug' => 'pabrik-kemasan-pack-express'],
            ['name' => 'Pack Express Packaging & Printing', 'contact_person' => 'Ibu Maya', 'phone' => '081345678901']
        );

        // ─────────────────────────────────────────────────────────────
        // 3. Master Bahan Baku & Harga Efektif
        // ─────────────────────────────────────────────────────────────
        $materialsData = [
            ['slug' => 'beras-pandan-wangi', 'name' => 'Beras Pandan Wangi Premium', 'unit' => $kg, 'p_unit' => $kg, 'code' => 'MAT-BERAS-01', 'price' => 16500, 'yield' => 100, 'waste' => 2, 'sup' => $supPasar],
            ['slug' => 'daging-ayam-fillet', 'name' => 'Daging Ayam Fillet Segar', 'unit' => $kg, 'p_unit' => $kg, 'code' => 'MAT-AYAM-01', 'price' => 42000, 'yield' => 90, 'waste' => 5, 'sup' => $supPasar],
            ['slug' => 'daging-sapi-slice', 'name' => 'Daging Sapi Slice US Beef', 'unit' => $kg, 'p_unit' => $kg, 'code' => 'MAT-SAPI-01', 'price' => 110000, 'yield' => 95, 'waste' => 3, 'sup' => $supPasar],
            ['slug' => 'telur-ayam-negeri', 'name' => 'Telur Ayam Negeri Fresh', 'unit' => $kg, 'p_unit' => $kg, 'code' => 'MAT-TLR-01', 'price' => 28000, 'yield' => 92, 'waste' => 4, 'sup' => $supPasar],
            ['slug' => 'minyak-goreng-sawit', 'name' => 'Minyak Goreng Sawit', 'unit' => $l ?: $kg, 'p_unit' => $l ?: $kg, 'code' => 'MAT-MYK-01', 'price' => 18000, 'yield' => 98, 'waste' => 2, 'sup' => $supPasar],
            ['slug' => 'bumbu-rempah-nusantara', 'name' => 'Racikan Bumbu Rempah Spesial', 'unit' => $kg, 'p_unit' => $kg, 'code' => 'MAT-BMB-01', 'price' => 35000, 'yield' => 98, 'waste' => 2, 'sup' => $supPasar],
            ['slug' => 'mie-basah-telur', 'name' => 'Mie Telur Basah Premium', 'unit' => $kg, 'p_unit' => $kg, 'code' => 'MAT-MIE-01', 'price' => 19000, 'yield' => 100, 'waste' => 1, 'sup' => $supPasar],
            ['slug' => 'biji-kopi-arabika', 'name' => 'Biji Kopi Arabika House Blend', 'unit' => $kg, 'p_unit' => $kg, 'code' => 'MAT-KPI-01', 'price' => 145000, 'yield' => 98, 'waste' => 2, 'sup' => $supKopi],
            ['slug' => 'susu-fresh-milk', 'name' => 'Fresh Milk Pasteurisasi', 'unit' => $l ?: $kg, 'p_unit' => $l ?: $kg, 'code' => 'MAT-SMK-01', 'price' => 22000, 'yield' => 99, 'waste' => 1, 'sup' => $supPasar],
            ['slug' => 'gula-aren-cair', 'name' => 'Sirup Gula Aren Asli Organic', 'unit' => $kg, 'p_unit' => $kg, 'code' => 'MAT-GLA-01', 'price' => 32000, 'yield' => 98, 'waste' => 1, 'sup' => $supPasar],
            ['slug' => 'teh-melati-premium', 'name' => 'Daun Teh Melati Wangi', 'unit' => $kg, 'p_unit' => $kg, 'code' => 'MAT-TEH-01', 'price' => 45000, 'yield' => 95, 'waste' => 3, 'sup' => $supPasar],
            ['slug' => 'kemasan-paper-box-ecofriendly', 'name' => 'Paper Lunch Box Eco-Friendly', 'unit' => $pcs, 'p_unit' => $pcs, 'code' => 'MAT-BOX-01', 'price' => 1500, 'yield' => 100, 'waste' => 1, 'sup' => $supKemasan],
            ['slug' => 'cup-plastik-sablon', 'name' => 'Cup Plastik 16oz + Sablon Logo', 'unit' => $pcs, 'p_unit' => $pcs, 'code' => 'MAT-CUP-01', 'price' => 850, 'yield' => 100, 'waste' => 1, 'sup' => $supKemasan],
            ['slug' => 'kerupuk-kaleng-retail-jadi', 'name' => 'Kerupuk Ikan Kaleng Siap Jual (Retail)', 'unit' => $pack, 'p_unit' => $pack, 'code' => 'MAT-KRP-01', 'price' => 3000, 'yield' => 100, 'waste' => 0, 'sup' => $supPasar],
        ];

        $matMap = [];
        foreach ($materialsData as $m) {
            $mat = Material::updateOrCreate(
                ['business_id' => $biz->id, 'slug' => $m['slug']],
                [
                    'name' => $m['name'],
                    'unit_id' => $m['unit']->id,
                    'supplier_id' => $m['sup']->id,
                    'code' => $m['code'],
                ]
            );
            $mat->prices()->updateOrCreate(
                ['effective_date' => now()->toDateString()],
                [
                    'business_id' => $biz->id,
                    'purchase_unit_id' => $m['p_unit']->id,
                    'purchase_price' => $m['price'],
                    'yield_percentage' => $m['yield'],
                    'waste_percentage' => $m['waste'],
                ]
            );
            $matMap[$m['slug']] = $mat;
        }

        // ─────────────────────────────────────────────────────────────
        // 4. Struktur Tim Karyawan Restoran (Labor Rates)
        // ─────────────────────────────────────────────────────────────
        $laborChef = LaborRate::updateOrCreate(
            ['business_id' => $biz->id, 'slug' => 'koki-utama-head-chef'],
            [
                'name' => 'Koki Utama / Head Chef (Dapur Panas)',
                'basis' => 'monthly',
                'rate_amount' => 4500000,
                'working_days_per_month' => 25,
                'working_hours_per_day' => 8,
                'utilization_rate' => 85,
            ]
        );

        $laborCrew = LaborRate::updateOrCreate(
            ['business_id' => $biz->id, 'slug' => 'asisten-koki-kitchen-crew'],
            [
                'name' => 'Asisten Koki & Kitchen Crew (Prep & Cook)',
                'basis' => 'monthly',
                'rate_amount' => 3200000,
                'working_days_per_month' => 26,
                'working_hours_per_day' => 8,
                'utilization_rate' => 80,
            ]
        );

        $laborBarista = LaborRate::updateOrCreate(
            ['business_id' => $biz->id, 'slug' => 'barista-beverage-maker'],
            [
                'name' => 'Barista & Beverage Maker (Station Minuman)',
                'basis' => 'monthly',
                'rate_amount' => 3400000,
                'working_days_per_month' => 26,
                'working_hours_per_day' => 8,
                'utilization_rate' => 80,
            ]
        );

        $laborWaiter = LaborRate::updateOrCreate(
            ['business_id' => $biz->id, 'slug' => 'waiter-pramusaji-event'],
            [
                'name' => 'Pramusaji / Waiter & Staff Layanan Event',
                'basis' => 'monthly',
                'rate_amount' => 2900000,
                'working_days_per_month' => 26,
                'working_hours_per_day' => 8,
                'utilization_rate' => 75,
            ]
        );

        // ─────────────────────────────────────────────────────────────
        // 5. Mesin & Peralatan Produksi Restoran
        // ─────────────────────────────────────────────────────────────
        $macFryer = Machine::updateOrCreate(
            ['business_id' => $biz->id, 'slug' => 'deep-fryer-komersial'],
            [
                'name' => 'Deep Fryer Komersial Gas & Listrik',
                'purchase_price' => 12500000,
                'residual_value' => 1500000,
                'useful_life_hours' => 15000,
                'electricity_cost_per_hour' => 2600,
                'maintenance_cost_per_hour' => 1200,
            ]
        );

        $macEspresso = Machine::updateOrCreate(
            ['business_id' => $biz->id, 'slug' => 'mesin-espresso-2-group'],
            [
                'name' => 'Mesin Espresso Komersial 2 Group',
                'purchase_price' => 38000000,
                'residual_value' => 5000000,
                'useful_life_hours' => 25000,
                'electricity_cost_per_hour' => 3200,
                'maintenance_cost_per_hour' => 1500,
            ]
        );

        $macAc = Machine::updateOrCreate(
            ['business_id' => $biz->id, 'slug' => 'ac-proyektor-sound-vip'],
            [
                'name' => 'AC Inverter & Audio Proyektor Ruang VIP',
                'purchase_price' => 22000000,
                'residual_value' => 2000000,
                'useful_life_hours' => 20000,
                'electricity_cost_per_hour' => 4500,
                'maintenance_cost_per_hour' => 1000,
            ]
        );

        // ─────────────────────────────────────────────────────────────
        // 6. Beban Biaya Overhead Pabrikasi & Operasional (BOP)
        // ─────────────────────────────────────────────────────────────
        Overhead::updateOrCreate(
            ['business_id' => $biz->id, 'slug' => 'sewa-gedung-restoran'],
            [
                'name' => 'Sewa Tempat / Ruko Restoran',
                'amount' => 8500000,
                'period' => 'monthly',
                'behavior' => 'fixed',
            ]
        );

        Overhead::updateOrCreate(
            ['business_id' => $biz->id, 'slug' => 'listrik-gas-air-restoran'],
            [
                'name' => 'Listrik PLN, Gas Elpiji & Air PDAM',
                'amount' => 3500000,
                'period' => 'monthly',
                'behavior' => 'variable',
            ]
        );

        Overhead::updateOrCreate(
            ['business_id' => $biz->id, 'slug' => 'wifi-pos-software-kasir'],
            [
                'name' => 'Langganan Software Kasir POS & Internet WiFi',
                'amount' => 750000,
                'period' => 'monthly',
                'behavior' => 'fixed',
            ]
        );

        // ─────────────────────────────────────────────────────────────
        // 7. Produk Simulasi Restoran Lengkap (12 Produk)
        // Terdiri dari:
        // A. Produk Berbahan Baku (BOM Recipes)
        // B. Produk NON-Bahan Baku (Jasa Service & Retail Beli-Jual)
        // ─────────────────────────────────────────────────────────────
        $productsSeedList = [
            // --- GRUP A: Produk Dapur (Dengan Resep Bahan Baku / BOM) ---
            [
                'name' => 'Nasi Goreng Ayam Spesial',
                'slug' => 'nasi-goreng-ayam-spesial',
                'code' => 'FNB-NG-001',
                'category_id' => $catMakanan->id,
                'unit' => $portion,
                'method' => CostModel::METHOD_RECIPE_BOM,
                'labor' => ['rate' => $laborChef, 'hours' => 0.15], // 9 menit kerja
                'machine' => ['mac' => $macFryer, 'hours' => 0.1],
                'items' => [
                    ['mat' => 'beras-pandan-wangi', 'qty' => 150, 'unit' => $g, 'waste' => 2],
                    ['mat' => 'daging-ayam-fillet', 'qty' => 100, 'unit' => $g, 'waste' => 5],
                    ['mat' => 'telur-ayam-negeri', 'qty' => 60, 'unit' => $g, 'waste' => 2],
                    ['mat' => 'bumbu-rempah-nusantara', 'qty' => 25, 'unit' => $g, 'waste' => 0],
                    ['mat' => 'minyak-goreng-sawit', 'qty' => 20, 'unit' => $ml ?: $g, 'waste' => 0],
                    ['mat' => 'kemasan-paper-box-ecofriendly', 'qty' => 1, 'unit' => $pcs, 'waste' => 0],
                ],
            ],
            [
                'name' => 'Nasi Goreng Sapi Wagyu Slice',
                'slug' => 'nasi-goreng-sapi-wagyu-slice',
                'code' => 'FNB-NG-002',
                'category_id' => $catMakanan->id,
                'unit' => $portion,
                'method' => CostModel::METHOD_RECIPE_BOM,
                'labor' => ['rate' => $laborChef, 'hours' => 0.15],
                'machine' => ['mac' => $macFryer, 'hours' => 0.1],
                'items' => [
                    ['mat' => 'beras-pandan-wangi', 'qty' => 150, 'unit' => $g, 'waste' => 2],
                    ['mat' => 'daging-sapi-slice', 'qty' => 90, 'unit' => $g, 'waste' => 3],
                    ['mat' => 'telur-ayam-negeri', 'qty' => 60, 'unit' => $g, 'waste' => 2],
                    ['mat' => 'bumbu-rempah-nusantara', 'qty' => 30, 'unit' => $g, 'waste' => 0],
                    ['mat' => 'minyak-goreng-sawit', 'qty' => 20, 'unit' => $ml ?: $g, 'waste' => 0],
                    ['mat' => 'kemasan-paper-box-ecofriendly', 'qty' => 1, 'unit' => $pcs, 'waste' => 0],
                ],
            ],
            [
                'name' => 'Ayam Geprek Sambal Korek',
                'slug' => 'ayam-geprek-sambal-korek',
                'code' => 'FNB-AG-001',
                'category_id' => $catMakanan->id,
                'unit' => $portion,
                'method' => CostModel::METHOD_RECIPE_BOM,
                'labor' => ['rate' => $laborCrew, 'hours' => 0.2],
                'machine' => ['mac' => $macFryer, 'hours' => 0.15],
                'items' => [
                    ['mat' => 'daging-ayam-fillet', 'qty' => 160, 'unit' => $g, 'waste' => 4],
                    ['mat' => 'beras-pandan-wangi', 'qty' => 150, 'unit' => $g, 'waste' => 2],
                    ['mat' => 'minyak-goreng-sawit', 'qty' => 40, 'unit' => $ml ?: $g, 'waste' => 5],
                    ['mat' => 'bumbu-rempah-nusantara', 'qty' => 25, 'unit' => $g, 'waste' => 0],
                    ['mat' => 'kemasan-paper-box-ecofriendly', 'qty' => 1, 'unit' => $pcs, 'waste' => 0],
                ],
            ],
            [
                'name' => 'Mie Goreng Seafood Komplit',
                'slug' => 'mie-goreng-seafood-komplit',
                'code' => 'FNB-MIE-001',
                'category_id' => $catMakanan->id,
                'unit' => $portion,
                'method' => CostModel::METHOD_RECIPE_BOM,
                'labor' => ['rate' => $laborChef, 'hours' => 0.15],
                'machine' => ['mac' => $macFryer, 'hours' => 0.1],
                'items' => [
                    ['mat' => 'mie-basah-telur', 'qty' => 160, 'unit' => $g, 'waste' => 1],
                    ['mat' => 'daging-ayam-fillet', 'qty' => 60, 'unit' => $g, 'waste' => 3],
                    ['mat' => 'telur-ayam-negeri', 'qty' => 60, 'unit' => $g, 'waste' => 2],
                    ['mat' => 'bumbu-rempah-nusantara', 'qty' => 20, 'unit' => $g, 'waste' => 0],
                    ['mat' => 'minyak-goreng-sawit', 'qty' => 20, 'unit' => $ml ?: $g, 'waste' => 0],
                    ['mat' => 'kemasan-paper-box-ecofriendly', 'qty' => 1, 'unit' => $pcs, 'waste' => 0],
                ],
            ],
            [
                'name' => 'Beef Bowl Yakiniku Rice',
                'slug' => 'beef-bowl-yakiniku-rice',
                'code' => 'FNB-BEEF-001',
                'category_id' => $catMakanan->id,
                'unit' => $portion,
                'method' => CostModel::METHOD_RECIPE_BOM,
                'labor' => ['rate' => $laborChef, 'hours' => 0.15],
                'machine' => null,
                'items' => [
                    ['mat' => 'beras-pandan-wangi', 'qty' => 160, 'unit' => $g, 'waste' => 2],
                    ['mat' => 'daging-sapi-slice', 'qty' => 100, 'unit' => $g, 'waste' => 3],
                    ['mat' => 'bumbu-rempah-nusantara', 'qty' => 30, 'unit' => $g, 'waste' => 0],
                    ['mat' => 'kemasan-paper-box-ecofriendly', 'qty' => 1, 'unit' => $pcs, 'waste' => 0],
                ],
            ],
            [
                'name' => 'Kopi Susu Gula Aren 16oz',
                'slug' => 'kopi-susu-gula-aren-16oz',
                'code' => 'BEV-KPI-001',
                'category_id' => $catMinuman->id,
                'unit' => $pcs,
                'method' => CostModel::METHOD_RECIPE_BOM,
                'labor' => ['rate' => $laborBarista, 'hours' => 0.08],
                'machine' => ['mac' => $macEspresso, 'hours' => 0.05],
                'items' => [
                    ['mat' => 'biji-kopi-arabika', 'qty' => 18, 'unit' => $g, 'waste' => 2],
                    ['mat' => 'susu-fresh-milk', 'qty' => 120, 'unit' => $ml ?: $g, 'waste' => 1],
                    ['mat' => 'gula-aren-cair', 'qty' => 25, 'unit' => $g, 'waste' => 0],
                    ['mat' => 'cup-plastik-sablon', 'qty' => 1, 'unit' => $pcs, 'waste' => 0],
                ],
            ],
            [
                'name' => 'Caffe Latte Double Espresso',
                'slug' => 'caffe-latte-double-espresso',
                'code' => 'BEV-KPI-002',
                'category_id' => $catMinuman->id,
                'unit' => $pcs,
                'method' => CostModel::METHOD_RECIPE_BOM,
                'labor' => ['rate' => $laborBarista, 'hours' => 0.08],
                'machine' => ['mac' => $macEspresso, 'hours' => 0.05],
                'items' => [
                    ['mat' => 'biji-kopi-arabika', 'qty' => 20, 'unit' => $g, 'waste' => 2],
                    ['mat' => 'susu-fresh-milk', 'qty' => 180, 'unit' => $ml ?: $g, 'waste' => 1],
                    ['mat' => 'cup-plastik-sablon', 'qty' => 1, 'unit' => $pcs, 'waste' => 0],
                ],
            ],
            [
                'name' => 'Es Teh Melati Manis Nusantara',
                'slug' => 'es-teh-melati-manis-nusantara',
                'code' => 'BEV-TEH-001',
                'category_id' => $catMinuman->id,
                'unit' => $pcs,
                'method' => CostModel::METHOD_RECIPE_BOM,
                'labor' => ['rate' => $laborBarista, 'hours' => 0.05],
                'machine' => null,
                'items' => [
                    ['mat' => 'teh-melati-premium', 'qty' => 15, 'unit' => $g, 'waste' => 2],
                    ['mat' => 'gula-aren-cair', 'qty' => 20, 'unit' => $g, 'waste' => 0],
                    ['mat' => 'cup-plastik-sablon', 'qty' => 1, 'unit' => $pcs, 'waste' => 0],
                ],
            ],
            [
                'name' => 'Ayam Bakar Madu Pedas Manis',
                'slug' => 'ayam-bakar-madu-pedas-manis',
                'code' => 'FNB-AB-001',
                'category_id' => $catMakanan->id,
                'unit' => $portion,
                'method' => CostModel::METHOD_RECIPE_BOM,
                'labor' => ['rate' => $laborChef, 'hours' => 0.25],
                'machine' => ['mac' => $macFryer, 'hours' => 0.1],
                'items' => [
                    ['mat' => 'daging-ayam-fillet', 'qty' => 180, 'unit' => $g, 'waste' => 5],
                    ['mat' => 'beras-pandan-wangi', 'qty' => 150, 'unit' => $g, 'waste' => 2],
                    ['mat' => 'bumbu-rempah-nusantara', 'qty' => 35, 'unit' => $g, 'waste' => 0],
                    ['mat' => 'kemasan-paper-box-ecofriendly', 'qty' => 1, 'unit' => $pcs, 'waste' => 0],
                ],
            ],

            // --- GRUP B: Produk & Layanan NON-BAHAN BAKU (Tanpa Bahan Baku Mentah) ---
            [
                'name' => 'Sewa Ruang Meeting VIP & Karaoke (per Jam)',
                'slug' => 'sewa-ruang-meeting-vip-karaoke',
                'code' => 'SRV-VIP-001',
                'category_id' => $catJasa->id,
                'unit' => $hour,
                'method' => CostModel::METHOD_SERVICE, // Metode Jasa / Service
                'labor' => ['rate' => $laborWaiter, 'hours' => 1.0], // 1 jam pendampingan waiter
                'machine' => ['mac' => $macAc, 'hours' => 1.0], // 1 jam operasional AC & Sound VIP
                'items' => [], // Tanpa Bahan Baku!
            ],
            [
                'name' => 'Paket Jasa Waiter Prasmanan Catering (per Jam)',
                'slug' => 'jasa-waiter-catering-event',
                'code' => 'SRV-CAT-001',
                'category_id' => $catJasa->id,
                'unit' => $hour,
                'method' => CostModel::METHOD_SERVICE,
                'labor' => ['rate' => $laborWaiter, 'hours' => 1.0],
                'machine' => null,
                'items' => [], // Tanpa Bahan Baku! Murni Jasa Tenaga Kerja
            ],
            [
                'name' => 'Kerupuk Ikan Kaleng Siap Saji (Retail Toko)',
                'slug' => 'kerupuk-ikan-kaleng-retail',
                'code' => 'RET-KRP-001',
                'category_id' => $catSnack->id,
                'unit' => $pack,
                'method' => CostModel::METHOD_RETAIL, // Metode Beli-Jual (Retail/Trading)
                'labor' => null,
                'machine' => null,
                'items' => [
                    ['mat' => 'kerupuk-kaleng-retail-jadi', 'qty' => 1, 'unit' => $pack, 'waste' => 0],
                ],
            ],
        ];

        $engine = new \App\Domain\Calculation\CalculationEngine;
        $costingService = new \App\Domain\Calculation\CostingResultService;

        foreach ($productsSeedList as $pData) {
            $product = Product::updateOrCreate(
                ['business_id' => $biz->id, 'slug' => $pData['slug']],
                [
                    'name' => $pData['name'],
                    'code' => $pData['code'],
                    'category_id' => $pData['category_id'],
                    'output_unit_id' => $pData['unit']->id,
                    'business_type_hint' => 'fnb',
                ]
            );

            // Cost Model
            $costModel = CostModel::updateOrCreate(
                ['business_id' => $biz->id, 'product_id' => $product->id, 'slug' => 'hpp-' . $pData['slug']],
                [
                    'name' => 'Model HPP ' . $pData['name'],
                    'method' => $pData['method'],
                    'is_active' => true,
                ]
            );

            // Labor relation
            if (! empty($pData['labor']) && isset($pData['labor']['rate'])) {
                $costModel->labors()->updateOrCreate(
                    ['labor_rate_id' => $pData['labor']['rate']->id],
                    [
                        'quantity' => 1,
                        'regular_hours' => $pData['labor']['hours'],
                        'overtime_hours' => 0,
                    ]
                );
            }

            // Machine relation
            if (! empty($pData['machine']) && isset($pData['machine']['mac'])) {
                $costModel->machines()->updateOrCreate(
                    ['machine_id' => $pData['machine']['mac']->id],
                    [
                        'hours_used' => $pData['machine']['hours'],
                    ]
                );
            }

            // BOM Header (Jika memiliki items)
            if (! empty($pData['items'])) {
                $bomHeader = $costModel->bomHeaders()->firstOrCreate(
                    ['cost_model_id' => $costModel->id],
                    [
                        'name' => 'Resep & BOM ' . $pData['name'],
                        'type' => \App\Models\BomHeader::TYPE_RECIPE,
                        'level' => 1,
                    ]
                );

                // Attach BOM Items
                foreach ($pData['items'] as $item) {
                    if (isset($matMap[$item['mat']]) && $item['unit']) {
                        $bomHeader->items()->updateOrCreate(
                            ['material_id' => $matMap[$item['mat']]->id],
                            [
                                'quantity' => $item['qty'],
                                'unit_id' => $item['unit']->id,
                                'waste_percentage' => $item['waste'],
                            ]
                        );
                    }
                }
            }

            // Run initial calculation and persist to DB for instant reporting
            try {
                $costModel->load(['bomHeaders.items.material.prices', 'bomHeaders.items.unit', 'labors.laborRate', 'machines.machine', 'product.outputUnit']);
                $dto = $engine->calculate($costModel);
                $costingService->persist($costModel, $dto, 'seed_baseline');

                if ($dto && $dto->unitCost > 0) {
                    $unitHpp = round($dto->unitCost);
                    $product->update([
                        'base_cost' => $unitHpp,
                        'selling_price' => round($unitHpp * 1.55, -2), // 55% margin
                        'min_stock' => 15,
                        'is_active' => true,
                    ]);
                }
            } catch (\Throwable $e) {
                // Silently continue if fresh environment
            }
        }
    }
}
