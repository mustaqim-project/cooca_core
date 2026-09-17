<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessLandingPage;
use App\Models\BusinessMembership;
use App\Models\CommerceOrder;
use App\Models\CommerceOrderItem;
use App\Models\CommercePaymentMethod;
use App\Models\CommerceReservation;
use App\Models\CommerceShippingRule;
use App\Models\CommerceStoreSetting;
use App\Models\Customer;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\PosRegister;
use App\Models\PosTable;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class DapurSedapRasaSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CustomerSeeder::class);

        DB::transaction(function (): void {
            // ─────────────────────────────────────────────────────────────────
            // 1. BUSINESS PROFILE & USER MEMBERSHIP
            // ─────────────────────────────────────────────────────────────────
            $business = Business::where('slug', 'dapur-sedap-rasa')->first();

            $businessData = [
                'name' => 'Dapur Sedap Rasa',
                'slug' => 'dapur-sedap-rasa',
                'currency' => 'IDR',
                'currency_precision' => 0,
                'rounding_strategy' => Business::ROUNDING_ROUND_100,
                'phone' => '0812-9876-5432',
                'email' => 'halo@dapursedaprasa.com',
                'address' => 'Jl. Boulevard Kelapa Gading Blok M-12, Kelapa Gading Barat, Jakarta Utara 14240',
                'description' => 'Restoran Kuliner Nusantara Autentik & Spesialis Katering Nasi Box, Bento Meeting, Tumpeng Mini, dan Prasmanan Acara.',
                'industry_category' => 'fnb',
                'template_code' => 'fnb_resto',
                'pos_show_product_images' => true,
                'allow_negative_stock' => true,
                'is_active' => true,
            ];

            if (! $business) {
                $business = Business::create([
                    'id' => (string) Str::uuid(),
                    ...$businessData,
                ]);
            } else {
                $business->update($businessData);
            }

            // Link Demo and Resto Owner Users
            $usersToLink = User::whereIn('email', [
                'owner.resto@cooca.id',
                'demo@cooca.id',
                'testing@cooca.id',
            ])->get();

            if ($usersToLink->isEmpty()) {
                $fallbackUser = User::first();
                if ($fallbackUser) {
                    $usersToLink = collect([$fallbackUser]);
                } else {
                    $createdUser = User::create([
                        'id' => (string) Str::uuid(),
                        'name' => 'Budi Raharjo',
                        'email' => 'owner.resto@cooca.id',
                        'phone' => '0812-9876-5432',
                        'password' => Hash::make('password123'),
                        'email_verified_at' => now(),
                    ]);
                    $usersToLink = collect([$createdUser]);
                }
            }

            foreach ($usersToLink as $u) {
                BusinessMembership::firstOrCreate(
                    ['business_id' => $business->id, 'user_id' => $u->id],
                    ['id' => (string) Str::uuid(), 'role' => 'owner']
                );

                if (in_array($u->email, ['owner.resto@cooca.id', 'demo@cooca.id'], true)) {
                    $u->update(['active_business_id' => $business->id]);
                }
            }

            $primaryOwner = $usersToLink->first() ?? User::first();

            // ─────────────────────────────────────────────────────────────────
            // 2. LOCATIONS / OUTLETS & CENTRAL KITCHEN
            // ─────────────────────────────────────────────────────────────────
            $locOutlet = Location::updateOrCreate(
                ['business_id' => $business->id, 'code' => 'DSR-KGD-01'],
                [
                    'name' => 'Dapur Sedap Rasa - Resto & Katering Kelapa Gading',
                    'slug' => 'dapur-sedap-rasa-kelapa-gading',
                    'type' => 'outlet',
                    'address' => 'Jl. Boulevard Kelapa Gading Blok M-12, Jakarta Utara',
                    'phone' => '0812-9876-5432',
                    'is_primary' => true,
                    'is_active' => true,
                ]
            );

            $locKitchen = Location::updateOrCreate(
                ['business_id' => $business->id, 'code' => 'DSR-CK-01'],
                [
                    'name' => 'Central Kitchen Katering & Gudang Bahan',
                    'slug' => 'central-kitchen-dapur-sedap-rasa',
                    'type' => 'warehouse',
                    'address' => 'Jl. Gading Putih Raya No. 8, Kelapa Gading, Jakarta Utara',
                    'phone' => '0812-9876-5433',
                    'is_primary' => false,
                    'is_active' => true,
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // 3. POS REGISTER & TABLES (DINE-IN & OFFLINE ORDER SUPPORT)
            // ─────────────────────────────────────────────────────────────────
            $posRegister = PosRegister::updateOrCreate(
                ['business_id' => $business->id, 'code' => 'REG-01'],
                [
                    'location_id' => $locOutlet->id,
                    'name' => 'Kasir Utama Resto & PO Katering',
                    'is_active' => true,
                ]
            );

            $tableLayout = [
                ['number' => '01', 'name' => 'Meja 01 (Indoor AC)', 'capacity' => 2],
                ['number' => '02', 'name' => 'Meja 02 (Indoor AC)', 'capacity' => 4],
                ['number' => '03', 'name' => 'Meja 03 (Indoor AC)', 'capacity' => 4],
                ['number' => '04', 'name' => 'Meja 04 (Indoor AC)', 'capacity' => 6],
                ['number' => '05', 'name' => 'Meja 05 (Indoor AC)', 'capacity' => 6],
                ['number' => '06', 'name' => 'Meja 06 (Indoor AC)', 'capacity' => 4],
                ['number' => '07', 'name' => 'Meja 07 (Indoor AC)', 'capacity' => 4],
                ['number' => '08', 'name' => 'Meja 08 (Indoor AC)', 'capacity' => 8],
                ['number' => 'T-01', 'name' => 'Meja Teras 01 (Outdoor)', 'capacity' => 4],
                ['number' => 'T-02', 'name' => 'Meja Teras 02 (Outdoor)', 'capacity' => 4],
                ['number' => 'VIP-01', 'name' => 'Ruang VIP Semeru (Meeting & Jamuan)', 'capacity' => 12],
                ['number' => 'VIP-02', 'name' => 'Ruang VIP Rinjani (Family Gathering)', 'capacity' => 16],
            ];

            foreach ($tableLayout as $t) {
                $existingTable = PosTable::where('business_id', $business->id)->where('table_number', $t['number'])->first();
                PosTable::updateOrCreate(
                    ['business_id' => $business->id, 'table_number' => $t['number']],
                    [
                        'location_id' => $locOutlet->id,
                        'name' => $t['name'],
                        'capacity' => $t['capacity'],
                        'status' => PosTable::STATUS_AVAILABLE,
                        'is_active' => true,
                        'qr_token' => $existingTable?->qr_token ?: Str::random(32),
                    ]
                );
            }

            // ─────────────────────────────────────────────────────────────────
            // 4. COMMERCE STOREFRONT SETTINGS (OFFLINE & ONLINE PO CAPABILITY)
            // ─────────────────────────────────────────────────────────────────
            CommerceStoreSetting::updateOrCreate(
                ['business_id' => $business->id],
                [
                    'is_storefront_enabled' => true,
                    'is_discoverable' => true,
                    'allow_pickup' => true,            // Customer ambil pesanan sendiri ke resto (offline order / takeaway)
                    'allow_delivery' => true,          // Kurir toko / pengantaran katering
                    'allow_scheduled_order' => true,   // Pesanan makan siang terjadwal / katering harian
                    'allow_request_order' => true,     // Pesanan khusus custom katering & prasmanan
                    'allow_customer_po' => true,       // Customer Purchase Order untuk korporat & acara
                    'allow_reservation' => true,       // Reservasi meja santap langsung & ruang VIP
                    'allow_custom_date' => false,      // Strict batch pengiriman saja
                    'batch_dates_mode' => 'operating_days',
                    'operating_days' => ['friday'],    // Batch pengiriman hari Jumat
                    'min_order_amount' => 20000,
                    'order_auto_cancel_minutes' => 60,
                    'lead_time_hours' => 1,
                    'available_slots' => [
                        '09:00 - 11:00',
                        '11:00 - 13:00',
                        '13:00 - 15:00',
                        '15:00 - 17:00',
                        '17:00 - 19:00',
                        '19:00 - 21:00',
                    ],
                    'cut_off_time' => '20:00:00',
                    'max_capacity_per_slot' => 30,
                    'daily_order_quota' => 150,
                    'quota_metric' => 'quantity',
                    'preorder_quota_unit' => 'PCS',
                    'announcement_text' => 'Melayani Santap Langsung (Dine-in), Takeaway, serta Pre-Order Katering Nasi Box, Tumpeng & Prasmanan.',
                    'order_notes_placeholder' => 'Misal: Level pedas sedang, pisahkan kuah, kirim jam 11:30 WIB, cantumkan kartu ucapan syukuran.',
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // 5. COMMERCE PAYMENT METHODS (TRANSFER BANK & QRIS)
            // ─────────────────────────────────────────────────────────────────
            CommercePaymentMethod::updateOrCreate(
                ['business_id' => $business->id, 'type' => CommercePaymentMethod::TYPE_BANK_TRANSFER, 'bank_name' => 'BCA'],
                [
                    'account_number' => '8800-1122-33',
                    'account_holder' => 'PT Dapur Sedap Rasa',
                    'instructions' => 'Transfer tepat sesuai nominal tagihan ke rekening BCA kami. Konfirmasi pembayaran diverifikasi otomatis oleh tim kasir.',
                    'is_active' => true,
                    'sort_order' => 1,
                ]
            );

            CommercePaymentMethod::updateOrCreate(
                ['business_id' => $business->id, 'type' => CommercePaymentMethod::TYPE_BANK_TRANSFER, 'bank_name' => 'Mandiri'],
                [
                    'account_number' => '123-00-9876543-2',
                    'account_holder' => 'PT Dapur Sedap Rasa',
                    'instructions' => 'Transfer via Mandiri Livin atau ATM ke nomor rekening di atas. Simpan bukti transfer untuk verifikasi pesanan.',
                    'is_active' => true,
                    'sort_order' => 2,
                ]
            );

            CommercePaymentMethod::updateOrCreate(
                ['business_id' => $business->id, 'type' => CommercePaymentMethod::TYPE_QRIS],
                [
                    'bank_name' => 'QRIS Dapur Sedap Rasa',
                    'account_number' => 'NMID-ID102030405060',
                    'account_holder' => 'Dapur Sedap Rasa',
                    'instructions' => 'Scan QRIS menggunakan BCA, Mandiri, GoPay, OVO, ShopeePay, atau DANA untuk pembayaran instan.',
                    'is_active' => true,
                    'sort_order' => 3,
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // 6. COMMERCE SHIPPING RULES
            // ─────────────────────────────────────────────────────────────────
            CommerceShippingRule::where('business_id', $business->id)->delete();

            CommerceShippingRule::create([
                'business_id' => $business->id,
                'name' => 'Ambil Sendiri di Resto (Pickup)',
                'rule_type' => CommerceShippingRule::TYPE_FLAT,
                'rate_amount' => 0,
                'min_order_for_free' => null,
                'is_active' => true,
                'sort_order' => 1,
            ]);

            CommerceShippingRule::create([
                'business_id' => $business->id,
                'name' => 'Kurir Toko / Pengantaran Katering (Area Kelapa Gading & Sekitarnya)',
                'rule_type' => CommerceShippingRule::TYPE_FLAT,
                'rate_amount' => 15000,
                'min_order_for_free' => 150000,
                'is_active' => true,
                'sort_order' => 2,
            ]);

            CommerceShippingRule::create([
                'business_id' => $business->id,
                'name' => 'Armada Khusus Katering Hajatan (Jabodetabek)',
                'rule_type' => CommerceShippingRule::TYPE_FLAT,
                'rate_amount' => 45000,
                'min_order_for_free' => 1000000,
                'is_active' => true,
                'sort_order' => 3,
            ]);

            // ─────────────────────────────────────────────────────────────────
            // 7. UNITS & PRODUCT CATEGORIES
            // ─────────────────────────────────────────────────────────────────
            $porsi = Unit::firstOrCreate(
                ['code' => 'porsi'],
                ['name' => 'Porsi', 'category' => 'count', 'is_base' => true]
            );

            $box = Unit::firstOrCreate(
                ['code' => 'box'],
                ['name' => 'Box', 'category' => 'count', 'is_base' => true]
            );

            $cup = Unit::firstOrCreate(
                ['code' => 'cup'],
                ['name' => 'Cup / Gelas', 'category' => 'count', 'is_base' => true]
            );

            $pax = Unit::firstOrCreate(
                ['code' => 'pax'],
                ['name' => 'Pax / Orang', 'category' => 'count', 'is_base' => true]
            );

            $catDineIn = ProductCategory::updateOrCreate(
                ['business_id' => $business->id, 'slug' => 'menu-harian-offline-online'],
                ['name' => 'Menu Santap Langsung & Harian', 'description' => 'Hidangan lezat siap saji untuk makan di tempat, takeaway, maupun pesan antar harian.']
            );

            $catNasiBox = ProductCategory::updateOrCreate(
                ['business_id' => $business->id, 'slug' => 'katering-nasi-box'],
                ['name' => 'Katering Nasi Box & Bento Rapat', 'description' => 'Paket makanan higienis lengkap dalam lunch box ramah lingkungan untuk seminar, rapat, dan event kantor.']
            );

            $catTumpeng = ProductCategory::updateOrCreate(
                ['business_id' => $business->id, 'slug' => 'tumpeng-syukuran'],
                ['name' => 'Tumpeng Mini & Syukuran', 'description' => 'Nasi kuning wangi gurih dengan aneka lauk tradisional komplit untuk perayaan ulang tahun dan syukuran.']
            );

            $catPrasmanan = ProductCategory::updateOrCreate(
                ['business_id' => $business->id, 'slug' => 'prasmanan-acara'],
                ['name' => 'Paket Prasmanan & Buffet Acara', 'description' => 'Layanan katering prasmanan komplit dengan meja pemanas, peralatan makan, dan staf penyaji profesional.']
            );

            $catMinuman = ProductCategory::updateOrCreate(
                ['business_id' => $business->id, 'slug' => 'minuman-segar'],
                ['name' => 'Minuman Segar Tradisional', 'description' => 'Es cendol dawet, es kopi susu gula aren, dan sari buah segar pelepas dahaga.']
            );

            $catSnack = ProductCategory::updateOrCreate(
                ['business_id' => $business->id, 'slug' => 'kudapan-snack-box'],
                ['name' => 'Kudapan & Snack Box', 'description' => 'Gorengan renyah, kue tradisional, dan paket snack box coffee break.']
            );

            // ─────────────────────────────────────────────────────────────────
            // 8. PRODUCTS CATALOG (OFFLINE POS & PRE-ORDER ONLINE)
            // ─────────────────────────────────────────────────────────────────
            // Clean up legacy generic showcase placeholder products if present
            Product::where('business_id', $business->id)
                ->whereIn('code', [
                    'RET-SLS-01',
                    'SRV-HRS-01',
                    'RST-AYAM-BAKAR',
                    'RST-NASI-RENDANG',
                    'PRD-STD-01',
                    'PRD-PRM-01',
                ])
                ->delete();

            $productsData = [
                // ── A. Menu Reguler Harian (Langsung Order Offline & Online) ──
                [
                    'code' => 'DSR-AYM-01',
                    'name' => 'Nasi Ayam Bakar Bumbu Madu',
                    'slug' => 'nasi-ayam-bakar-bumbu-madu',
                    'category_id' => $catDineIn->id,
                    'output_unit_id' => $porsi->id,
                    'base_cost' => 17000,
                    'selling_price' => 32000,
                    'min_stock' => 20,
                    'is_preorder' => false,
                    'preorder_mode' => Product::PREORDER_MODE_SCHEDULE,
                    'preorder_lead_days' => 0,
                    'show_in_pos' => true,
                    'show_in_website' => true,
                    'show_in_sales_order' => true,
                    'show_price_on_web' => true,
                    'description' => 'Paha/dada ayam pejantan ungkep rempah kuning, diolesi madu murni dan dibakar arang kelapa harum. Disajikan lengkap dengan nasi hangat, sambal terasi matang, dan lalapan segar.',
                    'image_path' => 'products/satay.jpg',
                    'initial_stock' => 80,
                ],
                [
                    'code' => 'DSR-RND-02',
                    'name' => 'Paket Nasi Rendang Daging Sapi Payakumbuh',
                    'slug' => 'paket-nasi-rendang-daging-sapi-payakumbuh',
                    'category_id' => $catDineIn->id,
                    'output_unit_id' => $porsi->id,
                    'base_cost' => 22000,
                    'selling_price' => 38000,
                    'min_stock' => 15,
                    'is_preorder' => false,
                    'preorder_mode' => Product::PREORDER_MODE_SCHEDULE,
                    'preorder_lead_days' => 0,
                    'show_in_pos' => true,
                    'show_in_website' => true,
                    'show_in_sales_order' => true,
                    'show_price_on_web' => true,
                    'description' => 'Daging sapi pilihan dimasak perlahan 6 jam dengan santan kental murni dan racikan rempah Minang pekat hingga empuk dan meresap sempurna ke serat daging.',
                    'image_path' => 'products/rendang.jpg',
                    'initial_stock' => 60,
                ],
                [
                    'code' => 'DSR-RWN-03',
                    'name' => 'Rawon Daging Sapi Surabaya Sambal Terasi',
                    'slug' => 'rawon-daging-sapi-surabaya-sambal-terasi',
                    'category_id' => $catDineIn->id,
                    'output_unit_id' => $porsi->id,
                    'base_cost' => 24000,
                    'selling_price' => 40000,
                    'min_stock' => 15,
                    'is_preorder' => false,
                    'preorder_mode' => Product::PREORDER_MODE_SCHEDULE,
                    'preorder_lead_days' => 0,
                    'show_in_pos' => true,
                    'show_in_website' => true,
                    'show_in_sales_order' => true,
                    'show_price_on_web' => true,
                    'description' => 'Sup daging sapi kuah hitam kluwek khas Jawa Timur yang gurih mantap, disajikan dengan tauge pendek, telur asin, sambal terasi, dan kerupuk udang.',
                    'image_path' => 'products/rendang.jpg',
                    'initial_stock' => 50,
                ],
                [
                    'code' => 'DSR-NAS-04',
                    'name' => 'Nasi Goreng Kampung Spesial Telur Ceplok',
                    'slug' => 'nasi-goreng-kampung-spesial-telur-ceplok',
                    'category_id' => $catDineIn->id,
                    'output_unit_id' => $porsi->id,
                    'base_cost' => 14000,
                    'selling_price' => 28000,
                    'min_stock' => 25,
                    'is_preorder' => false,
                    'preorder_mode' => Product::PREORDER_MODE_SCHEDULE,
                    'preorder_lead_days' => 0,
                    'show_in_pos' => true,
                    'show_in_website' => true,
                    'show_in_sales_order' => true,
                    'show_price_on_web' => true,
                    'description' => 'Nasi goreng racikan bumbu bawang dan cabai rawit pedas gurih, suwiran ayam, bakso sapi, telur mata sapi, acar timun segar, dan kerupuk renyah.',
                    'image_path' => 'products/satay.jpg',
                    'initial_stock' => 90,
                ],
                [
                    'code' => 'DSR-SAT-05',
                    'name' => 'Sate Ayam Bumbu Kacang Madura (10 Tusuk)',
                    'slug' => 'sate-ayam-bumbu-kacang-madura',
                    'category_id' => $catDineIn->id,
                    'output_unit_id' => $porsi->id,
                    'base_cost' => 16000,
                    'selling_price' => 30000,
                    'min_stock' => 20,
                    'is_preorder' => false,
                    'preorder_mode' => Product::PREORDER_MODE_SCHEDULE,
                    'preorder_lead_days' => 0,
                    'show_in_pos' => true,
                    'show_in_website' => true,
                    'show_in_sales_order' => true,
                    'show_price_on_web' => true,
                    'description' => '10 tusuk sate daging ayam empuk juicy dibakar arang, disiram kuah bumbu kacang gurih kental, kecap manis, irisan bawang merah, dan cabai rawit.',
                    'image_path' => 'products/satay.jpg',
                    'initial_stock' => 70,
                ],
                [
                    'code' => 'DSR-MDN-06',
                    'name' => 'Tempe Mendoan Gurih Sambal Kecap Rawit',
                    'slug' => 'tempe-mendoan-gurih-sambal-kecap-rawit',
                    'category_id' => $catSnack->id,
                    'output_unit_id' => $porsi->id,
                    'base_cost' => 6000,
                    'selling_price' => 16000,
                    'min_stock' => 20,
                    'is_preorder' => false,
                    'preorder_mode' => Product::PREORDER_MODE_SCHEDULE,
                    'preorder_lead_days' => 0,
                    'show_in_pos' => true,
                    'show_in_website' => true,
                    'show_in_sales_order' => true,
                    'show_price_on_web' => true,
                    'description' => '4 lembar tempe tipis khas Banyumas berbalut adonan tepung rempah daun bawang harum, digoreng setengah matang lembut, dicocol sambal kecap pedas segar.',
                    'image_path' => 'products/satay.jpg',
                    'initial_stock' => 100,
                ],
                [
                    'code' => 'DSR-CND-07',
                    'name' => 'Es Cendol Dawet Gula Aren Nangka',
                    'slug' => 'es-cendol-dawet-gula-aren-nangka',
                    'category_id' => $catMinuman->id,
                    'output_unit_id' => $cup->id,
                    'base_cost' => 8000,
                    'selling_price' => 18000,
                    'min_stock' => 30,
                    'is_preorder' => false,
                    'preorder_mode' => Product::PREORDER_MODE_SCHEDULE,
                    'preorder_lead_days' => 0,
                    'show_in_pos' => true,
                    'show_in_website' => true,
                    'show_in_sales_order' => true,
                    'show_price_on_web' => true,
                    'description' => 'Cendol kenyal pandan wangi murni, santan kelapa perah segar, sirup gula aren kental legit, dan potongan buah nangka harum manis.',
                    'image_path' => 'products/cendol.jpg',
                    'initial_stock' => 120,
                ],
                [
                    'code' => 'DSR-KPI-08',
                    'name' => 'Es Kopi Susu Gula Aren Kampung',
                    'slug' => 'es-kopi-susu-gula-aren-kampung',
                    'category_id' => $catMinuman->id,
                    'output_unit_id' => $cup->id,
                    'base_cost' => 7000,
                    'selling_price' => 18000,
                    'min_stock' => 30,
                    'is_preorder' => false,
                    'preorder_mode' => Product::PREORDER_MODE_SCHEDULE,
                    'preorder_lead_days' => 0,
                    'show_in_pos' => true,
                    'show_in_website' => true,
                    'show_in_sales_order' => true,
                    'show_price_on_web' => true,
                    'description' => 'Espresso blend Arabika Gayo & Robusta Flores, susu segar creamy, dan pemanis gula aren cair murni tanpa pengawet.',
                    'image_path' => 'products/cendol.jpg',
                    'initial_stock' => 100,
                ],
                [
                    'code' => 'DSR-JRK-09',
                    'name' => 'Es Jeruk Peras Segar Murni',
                    'slug' => 'es-jeruk-peras-segar-murni',
                    'category_id' => $catMinuman->id,
                    'output_unit_id' => $cup->id,
                    'base_cost' => 5000,
                    'selling_price' => 12000,
                    'min_stock' => 30,
                    'is_preorder' => false,
                    'preorder_mode' => Product::PREORDER_MODE_SCHEDULE,
                    'preorder_lead_days' => 0,
                    'show_in_pos' => true,
                    'show_in_website' => true,
                    'show_in_sales_order' => true,
                    'show_price_on_web' => true,
                    'description' => 'Perasan jeruk segar asli dengan gula tebu cair alami dan es batu dingin menyegarkan.',
                    'image_path' => 'products/cendol.jpg',
                    'initial_stock' => 150,
                ],

                // ── B. Menu Katering Pre-Order (PO Online & Sales Order) ──
                [
                    'code' => 'DSR-CAT-01',
                    'name' => 'Paket Bento Meeting Nasi Liwet Ayam Lengkuas',
                    'slug' => 'paket-bento-meeting-nasi-liwet-ayam-lengkuas',
                    'category_id' => $catNasiBox->id,
                    'output_unit_id' => $box->id,
                    'base_cost' => 20000,
                    'selling_price' => 35000,
                    'min_stock' => 10,
                    'is_preorder' => true,
                    'preorder_mode' => Product::PREORDER_MODE_SCHEDULE,
                    'preorder_lead_days' => 1,
                    'show_in_pos' => true,
                    'show_in_website' => true,
                    'show_in_sales_order' => true,
                    'show_price_on_web' => true,
                    'description' => 'Nasi liwet harum teri medan & daun salam, ayam goreng lengkuas gurih, tahu tempe bacem, telur balado, sambal terasi, dan kerupuk dalam kemasan box bento sekat eksklusif.',
                    'image_path' => 'products/rendang.jpg',
                    'initial_stock' => 200,
                ],
                [
                    'code' => 'DSR-CAT-02',
                    'name' => 'Nasi Box Katering Komplit Rendang & Telur Balado',
                    'slug' => 'nasi-box-katering-komplit-rendang-telur-balado',
                    'category_id' => $catNasiBox->id,
                    'output_unit_id' => $box->id,
                    'base_cost' => 25000,
                    'selling_price' => 42000,
                    'min_stock' => 10,
                    'is_preorder' => true,
                    'preorder_mode' => Product::PREORDER_MODE_SCHEDULE,
                    'preorder_lead_days' => 1,
                    'show_in_pos' => true,
                    'show_in_website' => true,
                    'show_in_sales_order' => true,
                    'show_price_on_web' => true,
                    'description' => 'Paket katering favorit rapat direksi: Nasi putih pulen, rendang daging sapi empuk, telur balado, perkedel kentang, tumis buncis jagung manis, sambal ijo, dan pisang segar.',
                    'image_path' => 'products/rendang.jpg',
                    'initial_stock' => 200,
                ],
                [
                    'code' => 'DSR-TPG-03',
                    'name' => 'Nasi Tumpeng Mini Nusantara Kuning Komplit',
                    'slug' => 'nasi-tumpeng-mini-nusantara-kuning-komplit',
                    'category_id' => $catTumpeng->id,
                    'output_unit_id' => $box->id,
                    'base_cost' => 26000,
                    'selling_price' => 45000,
                    'min_stock' => 5,
                    'is_preorder' => true,
                    'preorder_mode' => Product::PREORDER_MODE_SCHEDULE,
                    'preorder_lead_days' => 1,
                    'show_in_pos' => true,
                    'show_in_website' => true,
                    'show_in_sales_order' => true,
                    'show_price_on_web' => true,
                    'description' => 'Nasi kuning kerucut mini gurih santan, ayam goreng suwir lengkuas, orek tempe manis, sambal goreng kentang ati, perkedel kentang, telur dadar iris, dan sambal bajak dalam wadah mika tumpeng estetik.',
                    'image_path' => 'products/tumpeng.jpg',
                    'initial_stock' => 150,
                ],
                [
                    'code' => 'DSR-TPG-04',
                    'name' => 'Tumpeng Besar Syukuran 20 Porsi Komplit Hias',
                    'slug' => 'tumpeng-besar-syukuran-20-porsi-komplit-hias',
                    'category_id' => $catTumpeng->id,
                    'output_unit_id' => $porsi->id,
                    'base_cost' => 500000,
                    'selling_price' => 850000,
                    'min_stock' => 2,
                    'is_preorder' => true,
                    'preorder_mode' => Product::PREORDER_MODE_SCHEDULE,
                    'preorder_lead_days' => 2,
                    'show_in_pos' => true,
                    'show_in_website' => true,
                    'show_in_sales_order' => true,
                    'show_price_on_web' => true,
                    'description' => 'Tumpeng tampah bambu diameter 60cm untuk 20 orang. Nasi kuning bertingkat dihias garnish sayur cantik, dilengkapi 7 lauk pendamping komplit dan ucapan perayaan akrilik khusus.',
                    'image_path' => 'products/tumpeng.jpg',
                    'initial_stock' => 20,
                ],
                [
                    'code' => 'DSR-SNK-05',
                    'name' => 'Paket Snack Box Rapat Kantor (3 Kue + Air Mineral)',
                    'slug' => 'paket-snack-box-rapat-kantor',
                    'category_id' => $catSnack->id,
                    'output_unit_id' => $box->id,
                    'base_cost' => 10000,
                    'selling_price' => 18000,
                    'min_stock' => 15,
                    'is_preorder' => true,
                    'preorder_mode' => Product::PREORDER_MODE_SCHEDULE,
                    'preorder_lead_days' => 1,
                    'show_in_pos' => true,
                    'show_in_website' => true,
                    'show_in_sales_order' => true,
                    'show_price_on_web' => true,
                    'description' => 'Paket coffee break praktis: Lemper ayam bakar, risoles ragout sayur renyah, bolu gulung keju lembut, tisu higienis, dan air mineral cup.',
                    'image_path' => 'products/satay.jpg',
                    'initial_stock' => 300,
                ],
                [
                    'code' => 'DSR-PRS-06',
                    'name' => 'Paket Katering Prasmanan Nusantara (Min 30 Pax)',
                    'slug' => 'paket-katering-prasmanan-nusantara',
                    'category_id' => $catPrasmanan->id,
                    'output_unit_id' => $pax->id,
                    'base_cost' => 45000,
                    'selling_price' => 75000,
                    'min_stock' => 1,
                    'is_preorder' => true,
                    'preorder_mode' => Product::PREORDER_MODE_SCHEDULE,
                    'preorder_lead_days' => 2,
                    'show_in_pos' => true,
                    'show_in_website' => true,
                    'show_in_sales_order' => true,
                    'show_price_on_web' => true,
                    'description' => 'Buffet komplit per porsi: Nasi putih & liwet, olahan sapi (Rendang/Gulai), olahan ayam (Bakar Madu/Goreng Kremes), sup rawon/soto, sayuran cah, kerupuk, buah potong, dan es cendol dawet. Termasuk perlengkapan makan & chafing dish.',
                    'image_path' => 'products/rendang.jpg',
                    'initial_stock' => 500,
                ],

                // ── C. Item Tambahan Kasir Offline Saja (Internal POS) ──
                [
                    'code' => 'DSR-POS-01',
                    'name' => 'Nasi Putih Tambahan Porsi',
                    'slug' => 'nasi-putih-tambahan-porsi',
                    'category_id' => $catDineIn->id,
                    'output_unit_id' => $porsi->id,
                    'base_cost' => 2500,
                    'selling_price' => 6000,
                    'min_stock' => 50,
                    'is_preorder' => false,
                    'preorder_mode' => Product::PREORDER_MODE_SCHEDULE,
                    'preorder_lead_days' => 0,
                    'show_in_pos' => true,
                    'show_in_website' => false, // Khusus kasir offline
                    'show_in_sales_order' => true,
                    'show_price_on_web' => false,
                    'description' => 'Satu porsi nasi putih pulen hangat beras pandan wangi.',
                    'image_path' => 'products/rendang.jpg',
                    'initial_stock' => 200,
                ],
                [
                    'code' => 'DSR-POS-02',
                    'name' => 'Ekstra Sambal Bawang Ulek',
                    'slug' => 'ekstra-sambal-bawang-ulek',
                    'category_id' => $catDineIn->id,
                    'output_unit_id' => $porsi->id,
                    'base_cost' => 1500,
                    'selling_price' => 4000,
                    'min_stock' => 50,
                    'is_preorder' => false,
                    'preorder_mode' => Product::PREORDER_MODE_SCHEDULE,
                    'preorder_lead_days' => 0,
                    'show_in_pos' => true,
                    'show_in_website' => false, // Khusus kasir offline
                    'show_in_sales_order' => true,
                    'show_price_on_web' => false,
                    'description' => 'Satu mangkuk kecil sambal bawang pedas nendang ulekan segar.',
                    'image_path' => 'products/satay.jpg',
                    'initial_stock' => 100,
                ],
                [
                    'code' => 'DSR-POS-03',
                    'name' => 'Kerupuk Udang Renyah',
                    'slug' => 'kerupuk-udang-renyah',
                    'category_id' => $catSnack->id,
                    'output_unit_id' => $porsi->id,
                    'base_cost' => 1000,
                    'selling_price' => 3000,
                    'min_stock' => 50,
                    'is_preorder' => false,
                    'preorder_mode' => Product::PREORDER_MODE_SCHEDULE,
                    'preorder_lead_days' => 0,
                    'show_in_pos' => true,
                    'show_in_website' => false, // Khusus kasir offline
                    'show_in_sales_order' => true,
                    'show_price_on_web' => false,
                    'description' => 'Satu bungkus kerupuk udang gurih renyah.',
                    'image_path' => 'products/satay.jpg',
                    'initial_stock' => 150,
                ],
            ];

            foreach ($productsData as $pData) {
                $product = Product::updateOrCreate(
                    ['business_id' => $business->id, 'code' => $pData['code']],
                    [
                        'name' => $pData['name'],
                        'slug' => $pData['slug'],
                        'category_id' => $pData['category_id'],
                        'output_unit_id' => $pData['output_unit_id'],
                        'type' => Product::TYPE_GOODS,
                        'base_cost' => $pData['base_cost'],
                        'selling_price' => $pData['selling_price'],
                        'min_stock' => $pData['min_stock'],
                        'is_active' => true,
                        'is_preorder' => $pData['is_preorder'],
                        'preorder_mode' => $pData['preorder_mode'],
                        'preorder_lead_days' => $pData['preorder_lead_days'],
                        'show_in_pos' => $pData['show_in_pos'],
                        'show_in_website' => $pData['show_in_website'],
                        'show_in_sales_order' => $pData['show_in_sales_order'],
                        'show_price_on_web' => $pData['show_price_on_web'],
                        'description' => $pData['description'],
                        'image_path' => $pData['image_path'],
                    ]
                );

                // Initial inventory stock at primary outlet
                InventoryStock::updateOrCreate(
                    ['business_id' => $business->id, 'product_id' => $product->id, 'location_id' => $locOutlet->id],
                    [
                        'quantity' => $pData['initial_stock'],
                        'reserved_quantity' => 0,
                        'last_cost' => $pData['base_cost'],
                        'avg_purchase_cost' => $pData['base_cost'],
                    ]
                );
            }

            // ─────────────────────────────────────────────────────────────────
            // 9. BUSINESS LANDING PAGE CMS (APPLE HIG BENTO STOREFRONT)
            // ─────────────────────────────────────────────────────────────────
            BusinessLandingPage::updateOrCreate(
                ['business_id' => $business->id],
                [
                    'is_published' => true,
                    'industry_preset' => 'resto',
                    'theme_color' => '#E11D48',
                    'font_family' => 'Plus Jakarta Sans',
                    'dark_mode' => false,

                    // Hero Content
                    'headline' => 'Cita Rasa Nusantara Autentik, Kehangatan Tradisi di Meja Anda',
                    'subheadline' => 'Nikmati kelezatan masakan rempah autentik langsung di resto kami, atau jadwalkan Pre-Order Katering Nasi Box, Bento Rapat, Tumpeng Mini, dan Prasmanan hajatan di Kelapa Gading, Jakarta Utara.',
                    'announcement_badge' => 'Buka Setiap Hari 09.00 - 22.00 • Melayani Santap Langsung di Resto & PO Katering Online',
                    'hero_image_url' => '/storage/landing_pages/about_restaurant.jpg',
                    'logo_url' => null,
                    'cta_primary_text' => 'Pesan',
                    'cta_primary_url' => '#produk',
                    'cta_secondary_text' => 'Reservasi',
                    'cta_secondary_url' => '#reservasi',

                    // About Content
                    'about_title' => 'Dapur Tradisi dengan Dedikasi Rasa Tanpa Kompromi',
                    'about_story' => "Berawal dari kecintaan keluarga pada kekayaan rempah Nusantara, Dapur Sedap Rasa hadir menyajikan masakan tradisional autentik berkualitas tinggi. Setiap hidangan dimasak dari bahan baku segar harian dengan takaran bumbu rempah asli tanpa perisa buatan.\n\nKami melayani makan santai bersama keluarga di outlet Kelapa Gading serta menyediakan solusi katering terpercaya untuk kebutuhan seminar kantor, makan siang karyawan, syukuran, pernikahan, hingga perayaan hari besar dengan garansi rasa konsisten dan pengiriman tepat waktu.",
                    'about_image_url' => '/storage/landing_pages/about_restaurant.jpg',

                    // 4 Pillars of Excellence
                    'values' => [
                        [
                            'icon' => 'shield-check',
                            'title' => '100% Halal & Bahan Alami Segar',
                            'description' => 'Daging sapi, ayam, sayur mayur, dan bumbu dapur dipasok segar setiap pagi dengan standar kebersihan tinggi dan bersertifikat halal.',
                        ],
                        [
                            'icon' => 'flame',
                            'title' => 'Racikan Rempah Murni Nusantara',
                            'description' => 'Dimasak dengan teknik tradisional dan rempah asli tanpa kompromi, menghasilkan aroma wangi dan rasa bumbu medok khas Indonesia.',
                        ],
                        [
                            'icon' => 'clock',
                            'title' => 'Dimasak Fresh Sesuai Jadwal',
                            'description' => 'Baik santap di meja resto maupun katering ratusan box, seluruh masakan disiapkan fresh menjelang jam konsumsi agar tetap hangat maksimal.',
                        ],
                        [
                            'icon' => 'truck',
                            'title' => 'Armada Pengiriman Katering Tepat Waktu',
                            'description' => 'Didukung armada kurir khusus katering berpengalaman yang menjamin pesanan tiba rapi dan tepat waktu ke seluruh area Jakarta & sekitarnya.',
                        ],
                    ],

                    // Operational Hours
                    'operational_hours' => [
                        ['day' => 'Senin', 'hours' => '09:00 - 22:00 WIB', 'is_open' => true],
                        ['day' => 'Selasa', 'hours' => '09:00 - 22:00 WIB', 'is_open' => true],
                        ['day' => 'Rabu', 'hours' => '09:00 - 22:00 WIB', 'is_open' => true],
                        ['day' => 'Kamis', 'hours' => '09:00 - 22:00 WIB', 'is_open' => true],
                        ['day' => 'Jumat', 'hours' => '09:00 - 22:30 WIB', 'is_open' => true],
                        ['day' => 'Sabtu', 'hours' => '08:30 - 22:30 WIB', 'is_open' => true],
                        ['day' => 'Minggu', 'hours' => '08:30 - 22:00 WIB', 'is_open' => true],
                    ],

                    // POS Products Display
                    'show_pos_products' => true,
                    'services_title' => 'Layanan Resto & Katering Unggulan',
                    'services_subtitle' => 'Solusi lengkap santap nikmat di restoran, pesanan harian takeaway, hingga katering acara berskala besar.',

                    // Custom Services
                    'custom_services' => [
                        [
                            'title' => 'Santap di Resto (Dine-In) & Meja VIP',
                            'description' => 'Area makan ber-AC yang nyaman, alunan musik lembut, dan ruang VIP khusus untuk rapat bisnis maupun jamuan keluarga besar.',
                            'price' => 'Mulai Rp 28.000',
                            'badge' => 'Dine-In',
                            'image_url' => '/storage/landing_pages/about_restaurant.jpg',
                        ],
                        [
                            'title' => 'Nasi Box & Bento Rapat Kantor (PO Online)',
                            'description' => 'Paket makanan higienis dalam box sekat ramah lingkungan untuk seminar, rapat direksi, pelatihan, dan makan siang staf.',
                            'price' => 'Mulai Rp 35.000 / box',
                            'badge' => 'PO Online',
                            'image_url' => '/storage/products/rendang.jpg',
                        ],
                        [
                            'title' => 'Tumpeng Mini Syukuran & Ulang Tahun',
                            'description' => 'Nasi kuning wangi dengan 7 macam lauk tradisional komplit dan hiasan estetik untuk momen perayaan berkesan.',
                            'price' => 'Mulai Rp 45.000 / pax',
                            'badge' => 'Spesial',
                            'image_url' => '/storage/products/tumpeng.jpg',
                        ],
                        [
                            'title' => 'Paket Katering Prasmanan & Gathering',
                            'description' => 'Layanan buffet lengkap dengan meja pemanas, peralatan makan stainless, dan staf penyaji profesional untuk pernikahan dan pesta.',
                            'price' => 'Mulai Rp 75.000 / pax',
                            'badge' => 'Event Besar',
                            'image_url' => '/storage/gallery/tumpeng.jpg',
                        ],
                        [
                            'title' => 'Coffee & Minuman Segar Tradisional',
                            'description' => 'Seduhan Kopi Susu Gula Aren, Es Cendol Dawet Durian, dan perasan jeruk murni pelepas dahaga yang manis pas.',
                            'price' => 'Mulai Rp 12.000',
                            'badge' => 'Minuman',
                            'image_url' => '/storage/products/cendol.jpg',
                        ],
                        [
                            'title' => 'Snack Box Rapat & Kudapan Nusantara',
                            'description' => 'Paket coffee break kantor dengan aneka kue basah tradisional pilihan, tempe mendoan hangat, dan air mineral higienis.',
                            'price' => 'Mulai Rp 18.000 / box',
                            'badge' => 'Coffee Break',
                            'image_url' => '/storage/products/satay.jpg',
                        ],
                    ],

                    // Gallery Images
                    'gallery_title' => 'Galeri Sajian & Suasana Restoran',
                    'gallery_subtitle' => 'Dokumentasi kehangatan jamuan para tamu dan hidangan lezat yang kami olah dengan sepenuh hati.',
                    'gallery_images' => [
                        [
                            'url' => '/storage/gallery/rendang.jpg',
                            'caption' => 'Rendang Daging Sapi Payakumbuh dimasak perlahan dengan santan murni dan bumbu rempah pekat.',
                        ],
                        [
                            'url' => '/storage/gallery/satay.jpg',
                            'caption' => 'Sate Ayam Bumbu Kacang Madura empuk dibakar arang kelapa dengan aroma harum menggugah selera.',
                        ],
                        [
                            'url' => '/storage/gallery/cendol.jpg',
                            'caption' => 'Es Cendol Dawet Gula Aren segar dengan santan kelapa gurih dan aroma nangka manis alami.',
                        ],
                        [
                            'url' => '/storage/gallery/tumpeng.jpg',
                            'caption' => 'Nasi Tumpeng Mini Nusantara dengan nasi kuning gurih dan 7 lauk pendamping tradisional komplit.',
                        ],
                    ],

                    'section_visibility' => [
                        'hero' => true,
                        'about' => true,
                        'products' => true,
                        'services' => true,
                        'gallery' => true,
                        'testimonials' => true,
                        'faq' => true,
                        'contact' => true,
                        'footer' => true,
                        'footer_brand' => true,
                        'footer_navigation' => true,
                        'footer_services' => true,
                        'footer_contact' => true,
                    ],

                    // Customer Testimonials
                    'testimonials' => [
                        [
                            'name' => 'Ir. Bambang Wijaya',
                            'role' => 'Head of HR PT Telkom Landmark Tower',
                            'quote' => 'Kami sudah rutin Pre-Order Nasi Box Bento untuk agenda rapat direksi dan pelatihan bulanan. Makanan selalu datang hangat, tepat waktu, dan rasanya konsisten enak.',
                            'rating' => 5,
                        ],
                        [
                            'name' => 'Rina Marlina & Suami',
                            'role' => 'Pelanggan Dine-in & Syukuran Keluarga',
                            'quote' => 'Makan di tempat sangat nyaman, ruang VIP Semeru dingin dan pas untuk jamuan keluarga. Rawon dan ayam bakar madunya juara banget bumbunya!',
                            'rating' => 5,
                        ],
                        [
                            'name' => 'Ferry Gunawan',
                            'role' => 'Event Organizer Wedding & Gathering',
                            'quote' => 'Sangat puas pesan Tumpeng Besar dan katering prasmanan untuk acara peresmian kantor klien kami di Sunter. Penataan estetik dan rasa makanan dipuji seluruh tamu.',
                            'rating' => 5,
                        ],
                        [
                            'name' => 'Dr. Jessica Kurnia',
                            'role' => 'Food Enthusiast & Dokter Nutrisi',
                            'quote' => 'Masakan Nusantara yang bersih, higienis, dan tanpa perisa buatan berlebihan. Pilihan tepat untuk santap harian maupun katering sehat keluarga.',
                            'rating' => 5,
                        ],
                    ],

                    // FAQs
                    'faqs' => [
                        [
                            'question' => 'Apakah makanan dan minuman di Dapur Sedap Rasa halal?',
                            'answer' => 'Ya, 100% halal. Seluruh bahan baku daging sapi, ayam, dan rempah bumbu kami bersertifikasi halal dan diproses higienis tanpa bahan non-halal.',
                        ],
                        [
                            'question' => 'Apakah saya bisa langsung datang dan makan di tempat (Dine-in)?',
                            'answer' => 'Tentu bisa. Kami buka setiap hari mulai pukul 09.00 hingga 22.00 WIB. Anda dapat langsung datang untuk santap di tempat, take-away, atau melakukan reservasi meja terlebih dahulu.',
                        ],
                        [
                            'question' => 'Bagaimana cara melakukan Pre-Order (PO) Katering Nasi Box & Tumpeng online?',
                            'answer' => 'Anda dapat memilih menu bertanda "Pre-Order" di halaman etalase website ini, menentukan tanggal dan jam pengantaran, lalu melakukan checkout. Tim kami akan mengonfirmasi jadwal pesanan secara otomatis.',
                        ],
                        [
                            'question' => 'Berapa batas minimal pemesanan untuk Nasi Box dan Tumpeng?',
                            'answer' => 'Untuk pesanan regular santap harian mulai Rp 20.000. Untuk pesanan Katering Nasi Box minimal 10 box, dan Tumpeng Mini minimal 5 porsi dengan lead-time pemesanan H-1.',
                        ],
                        [
                            'question' => 'Metode pembayaran apa saja yang didukung?',
                            'answer' => 'Kami menerima Pembayaran Tunai di kasir restoran, QRIS (BCA, Mandiri, GoPay, OVO, ShopeePay, DANA), serta Transfer Bank (BCA & Mandiri) untuk pesanan katering korporat.',
                        ],
                        [
                            'question' => 'Apakah melayani pengiriman katering ke luar Kelapa Gading?',
                            'answer' => 'Ya, kami melayani pengiriman ke seluruh wilayah Jakarta, Bekasi, Depok, dan Tangerang menggunakan kurir khusus katering berpendingin/berpemanas agar makanan tiba dalam kondisi prima.',
                        ],
                    ],

                    // Key Business Statistics
                    'stats' => [
                        'clients' => '30.000+',
                        'clients_label' => 'Porsi Katering & Tamu Terlayani',
                        'experience' => '9+ Tahun',
                        'experience_label' => 'Melestarikan Resep Nusantara',
                        'rating' => '4.9 ★',
                        'rating_label' => 'Rating Kepuasan Google (1.500+ Ulasan)',
                    ],

                    // Social Links
                    'social_links' => [
                        'instagram' => 'https://instagram.com/dapursedaprasa.id',
                        'tiktok' => 'https://tiktok.com/@dapursedaprasa.id',
                        'facebook' => 'https://facebook.com/dapursedaprasa.id',
                        'youtube' => 'https://youtube.com/@dapursedaprasa.id',
                        'shopee' => 'https://shopee.co.id/dapursedaprasa',
                        'tokopedia' => 'https://tokopedia.com/dapursedaprasa',
                        'gofood' => 'https://gofood.link/dapursedaprasa',
                        'grabfood' => 'https://grab.onelink.me/dapursedaprasa',
                    ],

                    // Contact & Location Details
                    'whatsapp_number' => '0812-9876-5432',
                    'whatsapp_welcome_message' => 'Halo Dapur Sedap Rasa, saya ingin pesan makanan / reservasi meja / tanya paket katering.',
                    'custom_phone' => '0812-9876-5432',
                    'custom_email' => 'halo@dapursedaprasa.com',
                    'custom_address' => 'Jl. Boulevard Kelapa Gading Blok M-12, Jakarta Utara 14240',
                    'google_maps_embed_url' => 'https://www.google.com/maps?q=Kelapa+Gading,+Jakarta+Utara&output=embed',

                    // Footer Content
                    'footer_description' => 'Dapur Sedap Rasa menyajikan hidangan autentik khas Nusantara dengan bumbu rempah asli dan bahan segar. Pilihan utama untuk makan bersama keluarga, santap harian, serta katering acara terpercaya.',
                    'footer_navigation_title' => 'Navigasi',
                    'footer_services_title' => 'Menu & Katering',
                    'footer_contact_title' => 'Kontak & Reservasi',
                    'footer_cta_text' => 'Pesan via WhatsApp',
                    'footer_copyright' => '© ' . date('Y') . ' Dapur Sedap Rasa. Hak Cipta Dilindungi. Powered by COOCA.',

                    // SEO Metadata
                    'meta_title' => 'Dapur Sedap Rasa | Restoran Nusantara & Katering Nasi Box Kelapa Gading',
                    'meta_description' => 'Restoran masakan Nusantara autentik di Kelapa Gading, Jakarta Utara. Melayani santap di tempat, takeaway, Pre-Order katering Nasi Box, Tumpeng Mini, dan Prasmanan hajatan.',
                    'meta_keywords' => 'dapur sedap rasa, katering kelapa gading, restoran nusantara jakarta utara, nasi box bento rapat, tumpeng mini syukuran, ayam bakar bumbu madu, rawon surabaya',
                    'og_image_url' => '/storage/landing_pages/about_restaurant.jpg',
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // 9.1 STOREFRONT SETTINGS & PAYMENT METHODS (SESUAI GAMBAR USER)
            // ─────────────────────────────────────────────────────────────────
            CommerceStoreSetting::updateOrCreate(
                ['business_id' => $business->id],
                [
                    'is_storefront_enabled' => true,
                    'is_discoverable' => true,
                    'allow_pickup' => true,
                    'allow_delivery' => true,
                    'allow_request_order' => true,
                    'allow_scheduled_order' => true,
                    'allow_customer_po' => true,
                    'allow_reservation' => true,
                    'allow_custom_date' => false, // STRICT BATCH ONLY
                    'lead_time_hours' => 1,
                    'cut_off_time' => '20:00:00',
                    'daily_order_quota' => 150,
                    'quota_metric' => 'quantity', // PCS / kuantitas item
                    'preorder_quota_unit' => 'PCS',
                    'batch_dates_mode' => 'operating_days',
                    'operating_days' => ['friday'],
                    'available_slots' => ['09:00 - 11:00'],
                    'min_order_amount' => 20000,
                    'order_auto_cancel_minutes' => 60,
                ]
            );

            // Payment Methods: BCA, Mandiri, QRIS Dapur Sedap Rasa
            CommercePaymentMethod::updateOrCreate(
                ['business_id' => $business->id, 'type' => 'bank_transfer', 'bank_name' => 'BCA'],
                [
                    'account_number' => '8800-1122-33',
                    'account_holder' => 'PT Dapur Sedap Rasa',
                    'instructions' => 'Transfer tepat sesuai nominal tagihan ke rekening BCA kami. Konfirmasi pembayaran diverifikasi otomatis oleh tim kasir.',
                    'is_active' => true,
                    'sort_order' => 1,
                ]
            );

            CommercePaymentMethod::updateOrCreate(
                ['business_id' => $business->id, 'type' => 'bank_transfer', 'bank_name' => 'Mandiri'],
                [
                    'account_number' => '123-00-9876543-2',
                    'account_holder' => 'PT Dapur Sedap Rasa',
                    'instructions' => 'Transfer tepat sesuai nominal tagihan ke rekening Bank Mandiri kami.',
                    'is_active' => true,
                    'sort_order' => 2,
                ]
            );

            CommercePaymentMethod::updateOrCreate(
                ['business_id' => $business->id, 'type' => 'qris', 'bank_name' => 'QRIS Dapur Sedap Rasa'],
                [
                    'account_number' => 'NMID-ID102030405060',
                    'account_holder' => 'Dapur Sedap Rasa',
                    'instructions' => 'Scan QRIS dengan aplikasi pembayaran digital (GoPay, OVO, ShopeePay, BCA/Mandiri Mobile).',
                    'is_active' => true,
                    'sort_order' => 3,
                ]
            );

            CommerceShippingRule::updateOrCreate(
                ['business_id' => $business->id, 'name' => 'Kurir Toko Kelapa Gading'],
                [
                    'rule_type' => 'flat',
                    'rate_amount' => 15000,
                    'min_order_for_free' => 150000,
                    'is_active' => true,
                    'sort_order' => 1,
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // 10. DEMONSTRATION CUSTOMER & SAMPLE TRANSACTIONS (OFFLINE & ONLINE PO)
            // ─────────────────────────────────────────────────────────────────
            $demoCustomer = Customer::updateOrCreate(
                ['phone' => '081299887766'],
                [
                    'business_id' => $business->id,
                    'name' => 'Budi Santoso (Customer Demo)',
                    'slug' => 'budi-santoso',
                    'email' => 'customer@cooca.id',
                    'password' => Hash::make('password123'),
                    'points_balance' => 350,
                    'membership_tier' => 'gold',
                    'phone_verified_at' => now(),
                    'email_verified_at' => now(),
                    'shipping_address' => 'Jl. Boulevard Raya Blok PA-19 No. 5, Kelapa Gading, Jakarta Utara 14240',
                    'billing_address' => 'Jl. Boulevard Raya Blok PA-19 No. 5, Kelapa Gading, Jakarta Utara 14240',
                ]
            );

            // 10.1 Sample Offline POS Order (Dine-in at Meja 03)
            $table03 = PosTable::where('business_id', $business->id)->where('table_number', '03')->first();
            $pAyam = Product::where('business_id', $business->id)->where('code', 'DSR-AYM-01')->first();
            $pCendol = Product::where('business_id', $business->id)->where('code', 'DSR-CND-07')->first();

            if ($pAyam && $pCendol) {
                $posOrder = PosOrder::updateOrCreate(
                    ['business_id' => $business->id, 'order_number' => 'POS-DSR-' . date('Ymd') . '-001'],
                    [
                        'location_id' => $locOutlet->id,
                        'user_id' => $primaryOwner->id,
                        'customer_id' => $demoCustomer->id,
                        'order_date' => Carbon::today(),
                        'status' => PosOrder::STATUS_COMPLETED,
                        'order_type' => 'dine_in',
                        'order_source' => PosOrder::SOURCE_POS,
                        'pos_table_id' => $table03?->id,
                        'subtotal' => 50000,
                        'tax_amount' => 0,
                        'discount_amount' => 0,
                        'total_amount' => 50000,
                    ]
                );

                PosOrderItem::updateOrCreate(
                    ['pos_order_id' => $posOrder->id, 'product_id' => $pAyam->id],
                    [
                        'product_name' => $pAyam->name,
                        'product_code' => $pAyam->code,
                        'unit_price' => 32000,
                        'unit_cost_hpp' => 17000,
                        'quantity' => 1,
                        'subtotal' => 32000,
                        'discount_amount' => 0,
                        'total_price' => 32000,
                        'total_hpp' => 17000,
                    ]
                );

                PosOrderItem::updateOrCreate(
                    ['pos_order_id' => $posOrder->id, 'product_id' => $pCendol->id],
                    [
                        'product_name' => $pCendol->name,
                        'product_code' => $pCendol->code,
                        'unit_price' => 18000,
                        'unit_cost_hpp' => 8000,
                        'quantity' => 1,
                        'subtotal' => 18000,
                        'discount_amount' => 0,
                        'total_price' => 18000,
                        'total_hpp' => 8000,
                    ]
                );

                PosOrderPayment::updateOrCreate(
                    ['pos_order_id' => $posOrder->id, 'payment_method' => PosOrderPayment::METHOD_CASH],
                    [
                        'amount' => 50000,
                        'net_amount' => 50000,
                        'fee_amount' => 0,
                        'status' => 'paid',
                        'notes' => 'Pembayaran tunai di meja kasir.',
                    ]
                );
            }

            // 10.2 Sample Online PO Order (Pre-Order Katering Bento Meeting)
            $pBento = Product::where('business_id', $business->id)->where('code', 'DSR-CAT-01')->first();
            $payMethodBca = CommercePaymentMethod::where('business_id', $business->id)->where('bank_name', 'BCA')->first();
            $shipRuleCourier = CommerceShippingRule::where('business_id', $business->id)->where('name', 'like', '%Kurir Toko%')->first();

            if ($pBento) {
                $scheduledDeliveryDate = Carbon::tomorrow()->toDateString();
                $poOrder = CommerceOrder::updateOrCreate(
                    ['business_id' => $business->id, 'order_number' => 'PO-DSR-' . date('Ymd') . '-002'],
                    [
                        'location_id' => $locOutlet->id,
                        'customer_id' => $demoCustomer->id,
                        'payment_method_id' => $payMethodBca?->id,
                        'shipping_rule_id' => $shipRuleCourier?->id,
                        'tracking_token' => Str::random(32),
                        'order_type' => CommerceOrder::TYPE_CUSTOMER_PO,
                        'fulfillment_type' => CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY,
                        'status' => CommerceOrder::STATUS_PROCESSING,
                        'payment_status' => CommerceOrder::PAYMENT_PAID,
                        'customer_po_number' => 'PO-TELKOM-2026/09/01',
                        'company_name' => 'PT Telkom Indonesia (Divisi Regional II)',
                        'customer_name' => 'Budi Santoso',
                        'customer_phone' => '081299887766',
                        'customer_email' => 'customer@cooca.id',
                        'shipping_address' => 'Telkom Landmark Tower Lt. 18, Jl. Jend. Gatot Subroto Kav. 52, Jakarta Selatan',
                        'scheduled_date' => $scheduledDeliveryDate,
                        'scheduled_time_slot' => '11:00 - 13:00',
                        'subtotal' => 875000, // 25 box * Rp 35.000
                        'shipping_cost' => 0, // Free ongkir (> Rp 150.000)
                        'discount_amount' => 0,
                        'total_amount' => 875000,
                        'notes' => 'Harap diantar tepat pukul 11:15 WIB sebelum break makan siang rapat direksi. Box dilabeli rapi.',
                    ]
                );

                CommerceOrderItem::updateOrCreate(
                    ['commerce_order_id' => $poOrder->id, 'product_id' => $pBento->id],
                    [
                        'product_name' => $pBento->name,
                        'product_type' => 'goods',
                        'unit_price' => 35000,
                        'quantity' => 25,
                        'subtotal' => 875000,
                        'notes' => '15 Paha, 10 Dada. Sambal terasi dipisah sachet.',
                    ]
                );
            }

            // 10.3 Sample Reservation (Meja VIP untuk Dinner Acara)
            $tableVip = PosTable::where('business_id', $business->id)->where('table_number', 'VIP-01')->first();
            CommerceReservation::updateOrCreate(
                ['business_id' => $business->id, 'reservation_code' => 'RSV-DSR-' . date('Ymd') . '-001'],
                [
                    'pos_table_id' => $tableVip?->id,
                    'customer_name' => 'Budi Santoso',
                    'customer_phone' => '081299887766',
                    'customer_email' => 'customer@cooca.id',
                    'reservation_date' => Carbon::tomorrow()->toDateString(),
                    'time_slot' => '19:00 - 21:00',
                    'guest_count' => 10,
                    'status' => CommerceReservation::STATUS_CONFIRMED,
                    'notes' => 'Jamuan santai anniversary kantor. Siapkan proyektor jika memungkinkan.',
                ]
            );
        });
    }
}
