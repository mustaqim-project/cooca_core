<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AiActionProposal;
use App\Models\AiProviderConfig;
use App\Models\AiTask;
use App\Models\AiWorkHistory;
use App\Models\Attendance;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\InventoryStock;
use App\Models\Invoice;
use App\Models\Location;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\PosOrder;
use App\Models\PosRegister;
use App\Models\PosShift;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class AiPrestigeTestingAccountSeeder extends Seeder
{
    /**
     * Seed testing account with Prestige Subscription, active AI suite, and comprehensive data.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // ─────────────────────────────────────────────────────────────────
            // 1. UNIT PENGUKURAN (UNITS)
            // ─────────────────────────────────────────────────────────────────
            $uPcs = Unit::where('code', 'pcs')->first() ?? Unit::create(['code' => 'pcs', 'name' => 'Pieces', 'category' => 'quantity', 'is_base' => true, 'precision' => 0]);
            $uKg  = Unit::where('code', 'kg')->first() ?? $uPcs;
            $uG   = Unit::where('code', 'g')->first() ?? $uPcs;
            $uBtl = Unit::where('code', 'btl')->first() ?? $uPcs;
            $uCup = Unit::where('code', 'cup')->first() ?? $uPcs;

            // ─────────────────────────────────────────────────────────────────
            // 2. TESTING USERS (OWNER & STAFF)
            // ─────────────────────────────────────────────────────────────────
            $defaultPassword = Hash::make('password123');

            // Akun Utama Testing Prestige
            $ownerUser = User::updateOrCreate(
                ['email' => 'prestige.owner@cooca.id'],
                [
                    'name' => 'Hendrawan Wijaya (Prestige Owner)',
                    'phone' => '081299887766',
                    'password' => $defaultPassword,
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    'onboarding_completed' => true,
                    'onboarding_completed_at' => now(),
                ]
            );

            // Akun Alias yang mudah diingat
            $aliasUser = User::updateOrCreate(
                ['email' => 'ai.prestige@cooca.id'],
                [
                    'name' => 'AI Prestige Tester',
                    'phone' => '081299887767',
                    'password' => $defaultPassword,
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    'onboarding_completed' => true,
                    'onboarding_completed_at' => now(),
                ]
            );

            // Karyawan Kasir & Barista untuk data presensi dan shift
            $baristaUser = User::updateOrCreate(
                ['email' => 'barista.prestige@cooca.id'],
                [
                    'name' => 'Dimas Arya (Head Barista)',
                    'phone' => '081344556677',
                    'password' => $defaultPassword,
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    'onboarding_completed' => true,
                ]
            );

            $cashierUser = User::updateOrCreate(
                ['email' => 'kasir.prestige@cooca.id'],
                [
                    'name' => 'Nadia Safitri (Lead Cashier)',
                    'phone' => '081377889900',
                    'password' => $defaultPassword,
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    'onboarding_completed' => true,
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // 3. BISNIS TESTING UTAMA (PT Mahakarya Artisan Roastery)
            // ─────────────────────────────────────────────────────────────────
            $business = Business::where('slug', 'mahakarya-roastery-prestige')->first();

            $businessAttributes = [
                'name' => 'PT Mahakarya Artisan Roastery & Coffee',
                'slug' => 'mahakarya-roastery-prestige',
                'description' => 'Specialty coffee roastery, flagship cafe, and B2B coffee bean supplier across Indonesia.',
                'industry_category' => 'fnb_cafe',
                'template_code' => 'fnb_cafe',
                'business_scale' => Business::SCALE_CORPORATE,
                'currency' => 'IDR',
                'currency_precision' => 0,
                'rounding_strategy' => Business::ROUNDING_ROUND_100,
                'phone' => '0812-9988-7766',
                'email' => 'management@mahakaryacoffee.co.id',
                'address' => 'Jl. Senopati No. 88, Kebayoran Baru, Jakarta Selatan 12190',
                'tax_identification_number' => '02.456.789.1-013.000',
                'bank_name' => 'Bank Central Asia (BCA)',
                'bank_account_number' => '873-091-2281',
                'bank_account_holder' => 'PT Mahakarya Artisan Roastery',
                'is_active' => true,
                'pos_enable_tax' => true,
                'pos_tax_percent' => 11.0,
                'pos_enable_service_charge' => true,
                'pos_service_charge_percent' => 5.0,
                'pos_show_product_images' => true,
            ];

            if (! $business) {
                $business = Business::create(array_merge([
                    'id' => (string) Str::uuid(),
                ], $businessAttributes));
            } else {
                $business->update($businessAttributes);
            }

            // Sync Memberships
            $business->users()->syncWithoutDetaching([
                $ownerUser->id   => ['id' => (string) Str::uuid(), 'role' => 'owner'],
                $aliasUser->id   => ['id' => (string) Str::uuid(), 'role' => 'owner'],
                $baristaUser->id => ['id' => (string) Str::uuid(), 'role' => 'cashier'],
                $cashierUser->id => ['id' => (string) Str::uuid(), 'role' => 'cashier'],
            ]);

            $ownerUser->update(['active_business_id' => $business->id]);
            $aliasUser->update(['active_business_id' => $business->id]);
            $baristaUser->update(['active_business_id' => $business->id]);
            $cashierUser->update(['active_business_id' => $business->id]);

            // ─────────────────────────────────────────────────────────────────
            // 4. PRESTIGE SUBSCRIPTION (TIER PRESTIGE ANNUAL)
            // ─────────────────────────────────────────────────────────────────
            BusinessSubscription::updateOrCreate(
                ['business_id' => $business->id],
                [
                    'plan_code' => BusinessSubscription::PLAN_PRESTIGE_ANNUAL,
                    'price' => 1990000.00,
                    'status' => BusinessSubscription::STATUS_ACTIVE,
                    'starts_at' => now()->subDays(15),
                    'ends_at' => now()->addYear(),
                    'ai_tokens_monthly_allowance' => 100000,
                    'ai_tokens_remaining' => 98750,
                    'last_token_reset_at' => now()->startOfMonth(),
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // 5. LOKASI (HEADQUARTERS & WAREHOUSE)
            // ─────────────────────────────────────────────────────────────────
            $locStore = Location::updateOrCreate(
                ['business_id' => $business->id, 'code' => 'LOC-HQ-SENO'],
                [
                    'name' => 'Flagship Roastery & Cafe Senopati',
                    'slug' => 'flagship-roastery-cafe-senopati',
                    'type' => 'outlet',
                    'address' => 'Jl. Senopati No. 88, Kebayoran Baru, Jakarta Selatan',
                    'phone' => '0812-9988-7766',
                    'is_primary' => true,
                    'is_active' => true,
                ]
            );

            $locWarehouse = Location::updateOrCreate(
                ['business_id' => $business->id, 'code' => 'LOC-WH-KEBAY'],
                [
                    'name' => 'Sentra Sangrai & Gudang Logistik',
                    'slug' => 'sentra-sangrai-gudang-logistik',
                    'type' => 'warehouse',
                    'address' => 'Jl. Kebayoran Lama No. 42, Jakarta Selatan',
                    'phone' => '0812-9988-7767',
                    'is_primary' => false,
                    'is_active' => true,
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // 6. PEMASOK (SUPPLIERS)
            // ─────────────────────────────────────────────────────────────────
            $supGayo = Supplier::updateOrCreate(
                ['business_id' => $business->id, 'slug' => 'koperasi-petani-gayo-highland'],
                [
                    'name' => 'Koperasi Tani Kopi Gayo Highland',
                    'contact_person' => 'Tengku Zulkarnain',
                    'phone' => '0813-8899-0011',
                    'email' => 'supply@gayohighland.co.id',
                    'address' => 'Takengon, Aceh Tengah',
                ]
            );

            $supKemasan = Supplier::updateOrCreate(
                ['business_id' => $business->id, 'slug' => 'cv-eco-packaging-kraft'],
                [
                    'name' => 'CV Eco Packaging Kraft Nusantara',
                    'contact_person' => 'Surya Wijaya',
                    'phone' => '0812-3344-5588',
                    'email' => 'sales@ecopackaging.id',
                    'address' => 'Kawasan Industri Pulogadung, Jakarta Timur',
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // 7. BAHAN BAKU (RAW MATERIALS)
            // ─────────────────────────────────────────────────────────────────
            $matCat = MaterialCategory::firstOrCreate(
                ['business_id' => $business->id, 'slug' => 'biji-kopi-dan-bahan'],
                ['name' => 'Biji Kopi Green & Roasted']
            );

            $matGayo = Material::updateOrCreate(
                ['business_id' => $business->id, 'code' => 'MAT-BEANS-GAYO-RAW'],
                [
                    'category_id' => $matCat->id,
                    'supplier_id' => $supGayo->id,
                    'name' => 'Green Beans Arabika Gayo Grade 1 Specialty',
                    'slug' => 'green-beans-arabika-gayo-grade-1',
                    'unit_id' => $uKg->id,
                    'description' => 'Biji kopi mentah Arabika Gayo Takengon ketinggian 1500 mdpl.',
                ]
            );

            $matCup = Material::updateOrCreate(
                ['business_id' => $business->id, 'code' => 'MAT-CUP-KRAFT-16OZ'],
                [
                    'category_id' => $matCat->id,
                    'supplier_id' => $supKemasan->id,
                    'name' => 'Cup Kraft Biodegradable 16oz + Lid',
                    'slug' => 'cup-kraft-biodegradable-16oz',
                    'unit_id' => $uPcs->id,
                    'description' => 'Kemasan take-away ramah lingkungan tahan panas & dingin.',
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // 8. KATEGORI & PRODUK JADI (PRODUCTS CATALOG)
            // ─────────────────────────────────────────────────────────────────
            $catBeans = ProductCategory::firstOrCreate(
                ['business_id' => $business->id, 'slug' => 'specialty-coffee-beans'],
                ['name' => 'Specialty Coffee Beans (Biji Kopi)']
            );

            $catColdBrew = ProductCategory::firstOrCreate(
                ['business_id' => $business->id, 'slug' => 'cold-brew-beverages'],
                ['name' => 'Cold Brew & Beverages']
            );

            $catPastry = ProductCategory::firstOrCreate(
                ['business_id' => $business->id, 'slug' => 'artisan-pastry-bites'],
                ['name' => 'Artisan Pastry & Bites']
            );

            $catMerch = ProductCategory::firstOrCreate(
                ['business_id' => $business->id, 'slug' => 'roastery-merchandise'],
                ['name' => 'Merchandise & Roastery Apparel']
            );

            $productsData = [
                [
                    'code' => 'PRD-BEANS-GAYO-250',
                    'name' => 'Arabika Gayo Fullwash 250g (Roasted Beans)',
                    'category_id' => $catBeans->id,
                    'output_unit_id' => $uPcs->id,
                    'selling_price' => 95000.0,
                    'base_cost' => 42000.0,
                    'stock' => 120,
                    'min_stock' => 20,
                ],
                [
                    'code' => 'PRD-BEANS-TORAJA-250',
                    'name' => 'Arabika Toraja Sapan Reserve 250g',
                    'category_id' => $catBeans->id,
                    'output_unit_id' => $uPcs->id,
                    'selling_price' => 115000.0,
                    'base_cost' => 50000.0,
                    'stock' => 85,
                    'min_stock' => 15,
                ],
                [
                    'code' => 'PRD-BEANS-ESPRESSO-1KG',
                    'name' => 'Mahakarya House Blend Espresso 1kg',
                    'category_id' => $catBeans->id,
                    'output_unit_id' => $uPcs->id,
                    'selling_price' => 240000.0,
                    'base_cost' => 110000.0,
                    'stock' => 60,
                    'min_stock' => 10,
                ],
                [
                    'code' => 'PRD-COLD-BREW-330',
                    'name' => 'Signature Nitro Cold Brew Bottle 330ml',
                    'category_id' => $catColdBrew->id,
                    'output_unit_id' => $uBtl->id,
                    'selling_price' => 38000.0,
                    'base_cost' => 13500.0,
                    'stock' => 140,
                    'min_stock' => 25,
                ],
                [
                    'code' => 'PRD-LATTE-OAT-16',
                    'name' => 'Caffe Latte Barista Oatmilk 16oz',
                    'category_id' => $catColdBrew->id,
                    'output_unit_id' => $uCup->id,
                    'selling_price' => 45000.0,
                    'base_cost' => 16000.0,
                    'stock' => 200,
                    'min_stock' => 30,
                ],
                [
                    'code' => 'PRD-PASTRY-CROISSANT',
                    'name' => 'French Butter Croissant (Fresh Baked)',
                    'category_id' => $catPastry->id,
                    'output_unit_id' => $uPcs->id,
                    'selling_price' => 28000.0,
                    'base_cost' => 11000.0,
                    'stock' => 40,
                    'min_stock' => 10,
                ],
                [
                    'code' => 'PRD-PASTRY-CINNAMON',
                    'name' => 'Artisan Cinnamon Roll Cream Cheese',
                    'category_id' => $catPastry->id,
                    'output_unit_id' => $uPcs->id,
                    'selling_price' => 32000.0,
                    'base_cost' => 12500.0,
                    'stock' => 35,
                    'min_stock' => 10,
                ],
                [
                    'code' => 'PRD-MERCH-CERAMIC-MUG',
                    'name' => 'Mahakarya Ceramic Matte Mug Limited 350ml',
                    'category_id' => $catMerch->id,
                    'output_unit_id' => $uPcs->id,
                    'selling_price' => 145000.0,
                    'base_cost' => 65000.0,
                    'stock' => 4, // KONDISI STOK KRITIS (< 10) UNTUK MEMICU TOOL DETEKSI STOK MENIPIS
                    'min_stock' => 10,
                ],
                [
                    'code' => 'PRD-MANUAL-GEISHA',
                    'name' => 'Manual Brew V60 Panama Geisha Village Limited',
                    'category_id' => $catBeans->id,
                    'output_unit_id' => $uCup->id,
                    'selling_price' => 85000.0,
                    'base_cost' => 35000.0,
                    'stock' => 2, // KONDISI STOK KRITIS
                    'min_stock' => 10,
                ],
            ];

            $createdProducts = [];
            foreach ($productsData as $p) {
                $prod = Product::updateOrCreate(
                    ['business_id' => $business->id, 'code' => $p['code']],
                    [
                        'name' => $p['name'],
                        'slug' => Str::slug($p['name']),
                        'type' => Product::TYPE_GOODS,
                        'category_id' => $p['category_id'],
                        'output_unit_id' => $p['output_unit_id'],
                        'selling_price' => $p['selling_price'],
                        'base_cost' => $p['base_cost'],
                        'min_stock' => $p['min_stock'],
                        'is_active' => true,
                        'show_in_pos' => true,
                    ]
                );

                $createdProducts[$p['code']] = $prod;

                // Sync Inventory Stock
                InventoryStock::updateOrCreate(
                    [
                        'business_id' => $business->id,
                        'product_id' => $prod->id,
                        'location_id' => $locStore->id,
                    ],
                    [
                        'quantity' => $p['stock'],
                        'reserved_quantity' => 0,
                    ]
                );
            }

            // ─────────────────────────────────────────────────────────────────
            // 9. PELANGGAN (CUSTOMERS: B2B CORPORATE, VIP & MEMBER)
            // ─────────────────────────────────────────────────────────────────
            $custGrandIndo = Customer::updateOrCreate(
                ['business_id' => $business->id, 'name' => 'Hotel Grand Indonesia (B2B VIP)'],
                [
                    'code' => 'CUST-B2B-GI',
                    'company_name' => 'PT Grand Indonesia Hospitality',
                    'email' => 'purchasing@grand-indonesia.example.com',
                    'phone' => '0811-2233-4455',
                    'payment_terms_days' => 30,
                    'membership_tier' => 'vip',
                    'segment' => 'corporate',
                    'is_active' => true,
                ]
            );

            $custDigital = Customer::updateOrCreate(
                ['business_id' => $business->id, 'name' => 'PT Digital Kreasi Asia'],
                [
                    'code' => 'CUST-B2B-DKA',
                    'company_name' => 'PT Digital Kreasi Asia',
                    'email' => 'finance@digitalkreasi.example.com',
                    'phone' => '0812-5566-7788',
                    'payment_terms_days' => 14,
                    'membership_tier' => 'gold',
                    'segment' => 'corporate',
                    'is_active' => true,
                ]
            );

            $custRegular = Customer::updateOrCreate(
                ['business_id' => $business->id, 'name' => 'dr. Amanda Kusuma'],
                [
                    'code' => 'CUST-MEM-AMANDA',
                    'email' => 'amanda.k@example.com',
                    'phone' => '0813-9988-1122',
                    'membership_tier' => 'gold',
                    'segment' => 'retail',
                    'is_active' => true,
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // 10. HISTORIS PENJUALAN 30 HARI (POS ORDERS & INVOICES)
            // ─────────────────────────────────────────────────────────────────
            $today = Carbon::today();

            // POS Register & Shift
            $posRegister = PosRegister::firstOrCreate(
                ['business_id' => $business->id, 'location_id' => $locStore->id],
                ['name' => 'Register Bar Utama 01', 'is_active' => true]
            );

            $posShift = PosShift::firstOrCreate(
                ['business_id' => $business->id, 'pos_register_id' => $posRegister->id, 'status' => 'open'],
                [
                    'user_id' => $cashierUser->id,
                    'opening_cash' => 1000000.0,
                    'opened_at' => now()->startOfDay()->addHours(7),
                ]
            );

            // Seed order harian selama 30 hari untuk data grafik penjualan
            for ($daysAgo = 29; $daysAgo >= 0; $daysAgo--) {
                $orderDate = $today->copy()->subDays($daysAgo);
                $ordersCountOnDay = ($daysAgo % 7 === 0 || $daysAgo % 7 === 6) ? 8 : 4; // Akhir pekan lebih ramai

                for ($i = 1; $i <= $ordersCountOnDay; $i++) {
                    $orderNum = sprintf('POS-%s-%02d-%02d', $orderDate->format('ymd'), $daysAgo, $i);
                    $subtotal = 145000.0 + (($i * 15000) % 80000);
                    $cogs = $subtotal * 0.42;
                    $grossProfit = $subtotal - $cogs;

                    PosOrder::updateOrCreate(
                        ['business_id' => $business->id, 'order_number' => $orderNum],
                        [
                            'location_id' => $locStore->id,
                            'pos_register_id' => $posRegister->id,
                            'pos_shift_id' => $posShift->id,
                            'user_id' => $cashierUser->id,
                            'customer_id' => ($i % 2 === 0) ? $custRegular->id : null,
                            'order_date' => $orderDate->toDateString(),
                            'status' => PosOrder::STATUS_COMPLETED,
                            'order_type' => 'dine_in',
                            'subtotal' => $subtotal,
                            'tax_percentage' => 11.0,
                            'tax_amount' => $subtotal * 0.11,
                            'service_charge_percentage' => 5.0,
                            'service_charge_amount' => $subtotal * 0.05,
                            'total_amount' => $subtotal * 1.16,
                            'paid_amount' => $subtotal * 1.16,
                            'total_hpp_cost' => $cogs,
                            'total_gross_profit' => $grossProfit,
                            'created_at' => $orderDate->copy()->addHours(8 + ($i * 2)),
                        ]
                    );
                }
            }

            // Invoices B2B
            Invoice::updateOrCreate(
                ['business_id' => $business->id, 'invoice_number' => 'INV-2026-09-001'],
                [
                    'customer_id' => $custGrandIndo->id,
                    'location_id' => $locStore->id,
                    'invoice_date' => now()->subDays(12)->toDateString(),
                    'due_date' => now()->addDays(18)->toDateString(),
                    'status' => Invoice::STATUS_SENT,
                    'subtotal' => 12500000.0,
                    'total_amount' => 13875000.0,
                    'paid_amount' => 0.0,
                    'balance_due' => 13875000.0,
                    'total_hpp_cost' => 5800000.0,
                    'total_gross_profit' => 6700000.0,
                    'created_by' => $ownerUser->id,
                ]
            );

            Invoice::updateOrCreate(
                ['business_id' => $business->id, 'invoice_number' => 'INV-2026-09-002'],
                [
                    'customer_id' => $custDigital->id,
                    'location_id' => $locStore->id,
                    'invoice_date' => now()->subDays(5)->toDateString(),
                    'due_date' => now()->addDays(9)->toDateString(),
                    'status' => Invoice::STATUS_PAID,
                    'subtotal' => 4200000.0,
                    'total_amount' => 4662000.0,
                    'paid_amount' => 4662000.0,
                    'balance_due' => 0.0,
                    'total_hpp_cost' => 1900000.0,
                    'total_gross_profit' => 2300000.0,
                    'created_by' => $ownerUser->id,
                ]
            );

            // Beban Operasional Bulan Ini
            Expense::updateOrCreate(
                ['business_id' => $business->id, 'expense_number' => 'EXP-2026-10-001'],
                [
                    'description' => 'Sewa Gedung Roastery & Flagship Senopati',
                    'expense_date' => now()->startOfMonth()->toDateString(),
                    'amount' => 15000000.0,
                    'category' => 'rent',
                    'payment_method' => 'bank_transfer',
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // 11. PRESENSI KARYAWAN HARI INI (ATTENDANCES)
            // ─────────────────────────────────────────────────────────────────
            Attendance::updateOrCreate(
                ['business_id' => $business->id, 'user_id' => $baristaUser->id, 'date' => $today->toDateString()],
                [
                    'location_id' => $locStore->id,
                    'clock_in_at' => $today->copy()->setTime(6, 52),
                    'clock_in_status' => Attendance::CLOCK_IN_ON_TIME,
                    'late_minutes' => 0,
                    'work_duration_minutes' => 480,
                ]
            );

            Attendance::updateOrCreate(
                ['business_id' => $business->id, 'user_id' => $cashierUser->id, 'date' => $today->toDateString()],
                [
                    'location_id' => $locStore->id,
                    'clock_in_at' => $today->copy()->setTime(7, 12),
                    'clock_in_status' => Attendance::CLOCK_IN_LATE,
                    'late_minutes' => 12,
                    'work_duration_minutes' => 468,
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // 12. KONFIGURASI PENYEDIA AI (MULTI-PROVIDER BYOAI)
            // ─────────────────────────────────────────────────────────────────
            // Provider 1: Rule-Based Fallback Engine (100% Offline Default)
            AiProviderConfig::updateOrCreate(
                ['business_id' => $business->id, 'provider' => 'rule_based'],
                [
                    'model' => 'deterministic-business-rule-v1',
                    'api_key' => 'offline-fallback-deterministic-key',
                    'is_active' => true,
                    'is_default' => true,
                    'status' => 'connected',
                    'tested_at' => now(),
                ]
            );

            // Provider 2: Google Gemini (Siap Diaktifkan)
            AiProviderConfig::updateOrCreate(
                ['business_id' => $business->id, 'provider' => 'gemini'],
                [
                    'model' => 'gemini-1.5-flash',
                    'api_key' => 'AIzaSyDemoPrestigeEncryptedKeyForGemini2026',
                    'is_active' => true,
                    'is_default' => false,
                    'status' => 'connected',
                    'tested_at' => now(),
                ]
            );

            // Provider 3: OpenAI (Siap Diaktifkan)
            AiProviderConfig::updateOrCreate(
                ['business_id' => $business->id, 'provider' => 'openai'],
                [
                    'model' => 'gpt-4o-mini',
                    'api_key' => 'sk-proj-demoPrestigeOpenAiEncryptedKey2026',
                    'is_active' => true,
                    'is_default' => false,
                    'status' => 'connected',
                    'tested_at' => now(),
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // 13. PROPOSAL AKSI AI (UNTUK PENGUJIAN MAKER-CHECKER ACTION CENTER)
            // ─────────────────────────────────────────────────────────────────
            // Proposal 1: PENDING (High Risk) - Penerbitan Faktur B2B
            AiActionProposal::updateOrCreate(
                ['business_id' => $business->id, 'idempotency_key' => 'ACT-PRESTIGE-INV-PENDING-001'],
                [
                    'executive_role' => 'sales_director',
                    'department' => 'sales',
                    'agent' => 'sales',
                    'tool' => 'DraftInvoiceProposal',
                    'action_type' => 'create_invoice',
                    'risk_level' => AiActionProposal::RISK_HIGH,
                    'title' => 'Terbitkan Faktur B2B - Hotel Grand Indonesia (Pasokan 50kg House Blend)',
                    'description' => 'Menerbitkan faktur tempo 30 hari untuk 50 kg Mahakarya House Blend Espresso 1kg senilai Rp 12.000.000.',
                    'reason' => 'Perjanjian pasokan berkala bulan Oktober telah terkonfirmasi oleh pihak hotel.',
                    'payload' => [
                        'customer_id' => $custGrandIndo->id,
                        'customer_name' => $custGrandIndo->name,
                        'product_id' => $createdProducts['PRD-BEANS-ESPRESSO-1KG']->id,
                        'product_name' => $createdProducts['PRD-BEANS-ESPRESSO-1KG']->name,
                        'quantity' => 50,
                        'unit_price' => 240000,
                        'total_amount' => 12000000,
                        'payment_terms' => 'tempo_30_hari',
                    ],
                    'estimated_cost' => 12000000.0,
                    'status' => AiActionProposal::STATUS_PENDING,
                    'created_by' => $ownerUser->id,
                ]
            );

            // Proposal 2: PENDING (Medium Risk) - Kampanye Media Sosial
            AiActionProposal::updateOrCreate(
                ['business_id' => $business->id, 'idempotency_key' => 'ACT-PRESTIGE-SOC-PENDING-002'],
                [
                    'executive_role' => 'cmo',
                    'department' => 'marketing',
                    'agent' => 'social_media',
                    'tool' => 'DraftSocialPostProposal',
                    'action_type' => 'publish_social_post',
                    'risk_level' => AiActionProposal::RISK_MEDIUM,
                    'title' => 'Jadwalkan Postingan Instagram Feed: Batch Sangrai Baru Panama Geisha Village',
                    'description' => 'Mempublikasikan konten visual peluncuran edisi terbatas Panama Geisha Village di Instagram.',
                    'reason' => 'Stok biji kopi eksklusif baru saja selesai resting 14 hari dan siap disajikan kepada pelanggan VIP.',
                    'payload' => [
                        'platform' => 'instagram',
                        'scheduled_at' => now()->addDay()->setTime(10, 0)->toDateTimeString(),
                        'caption' => 'Sensasi floral melati dan aroma bergamot elegan kini hadir di cangkir Anda. Panama Geisha Village Limited Batch telah selesai resting dan siap dinikmati di Flagship Senopati. Hanya tersedia 50 sajian.',
                        'hashtags' => '#MahakaryaCoffee #PanamaGeisha #SpecialtyCoffeeJakarta #SenopatiRoastery',
                    ],
                    'estimated_cost' => 0.0,
                    'status' => AiActionProposal::STATUS_PENDING,
                    'created_by' => $ownerUser->id,
                ]
            );

            // Proposal 3: EXECUTED (High Risk) - Riwayat Sukses Eksekusi
            AiActionProposal::updateOrCreate(
                ['business_id' => $business->id, 'idempotency_key' => 'ACT-PRESTIGE-PO-EXECUTED-003'],
                [
                    'executive_role' => 'coo',
                    'department' => 'operations',
                    'agent' => 'purchasing',
                    'tool' => 'DraftPurchaseOrderProposal',
                    'action_type' => 'create_purchase_order',
                    'risk_level' => AiActionProposal::RISK_HIGH,
                    'title' => 'Terbitkan Purchase Order - Koperasi Tani Gayo Highland (Restok 200kg Green Beans)',
                    'description' => 'Pemesanan bahan baku biji kopi mentah sebelum periode panen raya berakhir.',
                    'reason' => 'Tingkat stok gudang sentra mendekati batas pengamanan stok (safety stock).',
                    'payload' => [
                        'supplier_id' => $supGayo->id,
                        'supplier_name' => $supGayo->name,
                        'quantity' => 200,
                        'unit_price' => 110000,
                        'total_amount' => 22000000,
                    ],
                    'estimated_cost' => 22000000.0,
                    'status' => AiActionProposal::STATUS_COMPLETED,
                    'approved_by' => $ownerUser->id,
                    'approved_at' => now()->subDays(3),
                    'executed_at' => now()->subDays(3),
                    'result' => [
                        'success' => true,
                        'po_number' => 'PO-2026-09-088',
                        'message' => 'Purchase order berhasil dicatat di sistem pergudangan.',
                    ],
                    'created_by' => $ownerUser->id,
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // 14. RIWAYAT AUDIT & TUGAS AI (AI TASKS & WORK HISTORY)
            // ─────────────────────────────────────────────────────────────────
            AiWorkHistory::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'session_title' => 'Evaluasi Kesehatan Bisnis Pagi Hari (Daily Executive Audit)',
                ],
                [
                    'executive_summary' => 'Evaluasi 30 hari omzet berjalan, rasio margin kotor rata-rata 58%, dan stabilitas rantai pasokan roastery.',
                    'participating_agents' => ['business', 'sales', 'inventory', 'finance'],
                    'insights_count' => 4,
                    'actions_count' => 2,
                    'approved_count' => 1,
                    'rejected_count' => 0,
                    'metadata' => [
                        'month_to_date_revenue' => 78500000,
                        'gross_profit_margin' => '58.2%',
                        'recommendation' => 'Pertahankan volume penjualan minuman dingin Nitro Cold Brew dan segera proses restok kemasan Cup 16oz.',
                    ],
                    'recorded_at' => now()->startOfDay()->addHours(7),
                ]
            );

            AiTask::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'input' => 'Analisis Tren Penjualan Minuman Dingin vs Biji Kopi Sangrai',
                ],
                [
                    'executive_role' => 'sales_director',
                    'department' => 'sales',
                    'agent' => 'sales',
                    'type' => 'chat',
                    'status' => 'completed',
                    'priority' => 'normal',
                    'result' => [
                        'text' => 'Penjualan Nitro Cold Brew menyumbang 34% transaksi harian, sedangkan Biji Kopi Sangrai 250g menyumbang margin nominal tertinggi.',
                    ],
                    'steps_count' => 2,
                    'tool_calls_count' => 2,
                    'started_at' => now()->subHours(2),
                    'completed_at' => now()->subHours(2)->addSeconds(3),
                ]
            );
        });
    }
}
