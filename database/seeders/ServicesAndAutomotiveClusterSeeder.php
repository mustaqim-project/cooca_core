<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessTypeTemplate;
use App\Models\CommerceReservation;
use App\Models\CostComponent;
use App\Models\Customer;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\PosShift;
use App\Models\PosTable;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class ServicesAndAutomotiveClusterSeeder extends Seeder
{
    /**
     * Run the database seeds for the 6 Service, Automotive & Creative Cluster Businesses.
     */
    public function run(): void
    {
        $units = $this->resolveUnits();

        $businessesData = [
            // ─────────────────────────────────────────────────────────────
            // 1. Bengkel & Sparepart Motor/Mobil (service_workshop)
            // ─────────────────────────────────────────────────────────────
            [
                'template_code' => 'service_workshop',
                'slug'          => 'garasi-otomotif-prima',
                'name'          => 'Garasi Otomotif Prima',
                'phone'         => '0812-3456-7801',
                'email'         => 'kontak@garasiotomotif.id',
                'address'       => 'Jl. Raya Otomotif No. 88, BSD City, Tangerang',
                'owner' => [
                    'name'     => 'Hendra Wijaya',
                    'email'    => 'bengkel.owner@cooca.id',
                    'password' => 'password123',
                ],
                'staff' => [
                    [
                        'name'     => 'Agus Pratama',
                        'email'    => 'mekanik.agus@cooca.id',
                        'password' => 'password123',
                        'role'     => 'staff',
                    ],
                    [
                        'name'     => 'Doni Setiawan',
                        'email'    => 'mekanik.doni@cooca.id',
                        'password' => 'password123',
                        'role'     => 'staff',
                    ],
                ],
                'location_name' => 'Garasi Otomotif Prima - Bengkel Pusat',
                'categories' => [
                    'Jasa Servis & Perawatan',
                    'Sparepart & Oli Mesin',
                ],
                'services' => [
                    [
                        'code'          => 'SRV-WS-001',
                        'name'          => 'Jasa Servis Ringan & Tune Up Injeksi',
                        'selling_price' => 75000,
                        'base_cost'     => 25000,
                        'unit'          => 'paket',
                        'category'      => 'Jasa Servis & Perawatan',
                        'description'   => 'Pembersihan throttle body, cek busi, scanner OBD2, dan general check-up 24 titik.',
                    ],
                    [
                        'code'          => 'SRV-WS-002',
                        'name'          => 'Jasa Ganti Oli Mesin & Filter',
                        'selling_price' => 25000,
                        'base_cost'     => 5000,
                        'unit'          => 'sesi',
                        'category'      => 'Jasa Servis & Perawatan',
                        'description'   => 'Ongkos kuras dan ganti oli mesin serta penggantian filter oli.',
                    ],
                    [
                        'code'          => 'SRV-WS-003',
                        'name'          => 'Jasa Servis Rem Depan & Belakang',
                        'selling_price' => 60000,
                        'base_cost'     => 20000,
                        'unit'          => 'sesi',
                        'category'      => 'Jasa Servis & Perawatan',
                        'description'   => 'Pembersihan tromol/kaliper, amplas kampas rem, dan bleeding minyak rem.',
                    ],
                    [
                        'code'          => 'SRV-WS-004',
                        'name'          => 'Jasa Spooring 3D & Balancing 4 Roda',
                        'selling_price' => 180000,
                        'base_cost'     => 45000,
                        'unit'          => 'paket',
                        'category'      => 'Jasa Servis & Perawatan',
                        'description'   => 'Penyelarasan sudut roda computerized 3D dan balancing timbal 4 roda.',
                    ],
                    [
                        'code'          => 'SRV-WS-005',
                        'name'          => 'Jasa Kuras Oli Transmisi Matic (Flushing)',
                        'selling_price' => 120000,
                        'base_cost'     => 30000,
                        'unit'          => 'sesi',
                        'category'      => 'Jasa Servis & Perawatan',
                        'description'   => 'Flushing oli transmisi otomatis menggunakan mesin ATF changer.',
                    ],
                ],
                'goods' => [
                    [
                        'code'          => 'PART-OIL-01',
                        'name'          => 'Oli Mesin Shell Helix HX7 10W-40 4L',
                        'selling_price' => 360000,
                        'base_cost'     => 280000,
                        'unit'          => 'pcs',
                        'stock'         => 25,
                        'category'      => 'Sparepart & Oli Mesin',
                    ],
                    [
                        'code'          => 'PART-BRK-01',
                        'name'          => 'Kampas Rem Depan Bendix Innova/Avanza',
                        'selling_price' => 220000,
                        'base_cost'     => 160000,
                        'unit'          => 'pcs',
                        'stock'         => 15,
                        'category'      => 'Sparepart & Oli Mesin',
                    ],
                    [
                        'code'          => 'PART-FLT-01',
                        'name'          => 'Filter Oli Denso Original',
                        'selling_price' => 45000,
                        'base_cost'     => 28000,
                        'unit'          => 'pcs',
                        'stock'         => 40,
                        'category'      => 'Sparepart & Oli Mesin',
                    ],
                    [
                        'code'          => 'PART-SPK-01',
                        'name'          => 'Busi NGK Iridium CPR9EAIX-9',
                        'selling_price' => 95000,
                        'base_cost'     => 65000,
                        'unit'          => 'pcs',
                        'stock'         => 30,
                        'category'      => 'Sparepart & Oli Mesin',
                    ],
                    [
                        'code'          => 'PART-BAT-01',
                        'name'          => 'Aki Kering GS Astra MF 12V 45Ah',
                        'selling_price' => 850000,
                        'base_cost'     => 680000,
                        'unit'          => 'pcs',
                        'stock'         => 8,
                        'category'      => 'Sparepart & Oli Mesin',
                    ],
                ],
                'sample_orders' => [
                    [
                        'vehicle_license_plate' => 'B 1988 XYZ',
                        'vehicle_model'         => 'Toyota Innova Zenix 2023',
                        'vehicle_mileage'       => 18450,
                        'staff_idx'             => 0, // Agus Pratama
                        'service_notes'         => 'Servis berkala 20rb km, ganti oli mesin & filter, cek rem depan agak berdecit.',
                        'customer_name'         => 'Pak Robert Gunawan',
                        'items' => [
                            ['code' => 'SRV-WS-001', 'qty' => 1],
                            ['code' => 'PART-OIL-01', 'qty' => 1],
                            ['code' => 'PART-FLT-01', 'qty' => 1],
                        ],
                    ],
                    [
                        'vehicle_license_plate' => 'B 3456 KLG',
                        'vehicle_model'         => 'Honda Vario 160',
                        'vehicle_mileage'       => 9200,
                        'staff_idx'             => 1, // Doni Setiawan
                        'service_notes'         => 'Tune up tarikan berat, ganti busi iridium dan ganti oli mesin.',
                        'customer_name'         => 'Fandi Ahmad',
                        'items' => [
                            ['code' => 'SRV-WS-001', 'qty' => 1],
                            ['code' => 'PART-SPK-01', 'qty' => 1],
                        ],
                    ],
                ],
                'reservations' => [
                    [
                        'code'           => 'RSV-WS-001',
                        'customer_name'  => 'Budi Hartono',
                        'customer_phone' => '0812-9988-7766',
                        'customer_email' => 'budi.hartono@email.test',
                        'date'           => Carbon::tomorrow()->toDateString(),
                        'time_slot'      => '09:00',
                        'guest_count'    => 1,
                        'notes'          => 'Booking Servis: Toyota Fortuner (B 1234 ABC) - Ganti kampas rem & ganti oli mesin.',
                        'status'         => CommerceReservation::STATUS_CONFIRMED,
                    ],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // 2. Cuci Mobil & Detailing (service_autodetailing)
            // ─────────────────────────────────────────────────────────────
            [
                'template_code' => 'service_autodetailing',
                'slug'          => 'kilau-auto-detailing',
                'name'          => 'Kilau Auto Detailing & Carwash',
                'phone'         => '0812-3456-7802',
                'email'         => 'care@kilauautodetailing.id',
                'address'       => 'Jl. Boulevard Gading Serpong No. 12, Tangerang',
                'owner' => [
                    'name'     => 'Rian Santoso',
                    'email'    => 'detailing.owner@cooca.id',
                    'password' => 'password123',
                ],
                'staff' => [
                    [
                        'name'     => 'Bayu Saputra',
                        'email'    => 'detailer.bayu@cooca.id',
                        'password' => 'password123',
                        'role'     => 'staff',
                    ],
                ],
                'location_name' => 'Kilau Auto Detailing - Workshop & Wash Bay',
                'categories' => [
                    'Paket Cuci & Hydro',
                    'Full Detailing & Coating',
                ],
                'services' => [
                    [
                        'code'          => 'SRV-DT-001',
                        'name'          => 'Paket Cuci Hidrolik + Wax Carnauba',
                        'selling_price' => 60000,
                        'base_cost'     => 15000,
                        'unit'          => 'sesi',
                        'category'      => 'Paket Cuci & Hydro',
                        'description'   => 'Cuci kolong hidrolik, snow wash pH netral, semir ban & aplikasi wax carnauba.',
                    ],
                    [
                        'code'          => 'SRV-DT-002',
                        'name'          => 'Full Interior Detailing & Fogging Antibakteri',
                        'selling_price' => 350000,
                        'base_cost'     => 80000,
                        'unit'          => 'paket',
                        'category'      => 'Full Detailing & Coating',
                        'description'   => 'Deep cleaning jok, plafon, karpet dasar, dashboard & sterilisasi ozone fogging.',
                    ],
                    [
                        'code'          => 'SRV-DT-003',
                        'name'          => 'Paket Nano Ceramic Coating 9H (2 Layer)',
                        'selling_price' => 2500000,
                        'base_cost'     => 600000,
                        'unit'          => 'paket',
                        'category'      => 'Full Detailing & Coating',
                        'description'   => 'Paint correction 3-step, 2 layer nano ceramic 9H, water repellent & garansi 2 tahun.',
                    ],
                    [
                        'code'          => 'SRV-DT-004',
                        'name'          => 'Poles Kaca & Anti Jamur Windshield',
                        'selling_price' => 175000,
                        'base_cost'     => 35000,
                        'unit'          => 'sesi',
                        'category'      => 'Paket Cuci & Hydro',
                        'description'   => 'Pembersihan jamur kerak air pada seluruh kaca mobil + coating repellent.',
                    ],
                ],
                'goods' => [
                    [
                        'code'          => 'PART-MF-01',
                        'name'          => 'Lap Microfiber Edgeless 500 GSM',
                        'selling_price' => 35000,
                        'base_cost'     => 18000,
                        'unit'          => 'pcs',
                        'stock'         => 50,
                        'category'      => 'Paket Cuci & Hydro',
                    ],
                ],
                'sample_orders' => [
                    [
                        'vehicle_license_plate' => 'D 4321 ABC',
                        'vehicle_model'         => 'Honda HR-V 2022',
                        'vehicle_mileage'       => 12000,
                        'staff_idx'             => 0, // Bayu Saputra
                        'service_notes'         => 'Paket Cuci Hidrolik + Wax Carnauba dan Poles Kaca Depan.',
                        'customer_name'         => 'Ibu Cindy Claudia',
                        'items' => [
                            ['code' => 'SRV-DT-001', 'qty' => 1],
                            ['code' => 'SRV-DT-004', 'qty' => 1],
                        ],
                    ],
                ],
                'reservations' => [
                    [
                        'code'           => 'RSV-DT-001',
                        'customer_name'  => 'Iwan Falsafah',
                        'customer_phone' => '0813-1122-3344',
                        'customer_email' => 'iwan@email.test',
                        'date'           => Carbon::today()->addDays(2)->toDateString(),
                        'time_slot'      => '10:00',
                        'guest_count'    => 1,
                        'notes'          => 'Booking Ceramic Coating: Mitsubishi Pajero Sport (F 8888 BOS) - Full Package.',
                        'status'         => CommerceReservation::STATUS_CONFIRMED,
                    ],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // 3. Salon & Barbershop (service_barbershop)
            // ─────────────────────────────────────────────────────────────
            [
                'template_code' => 'service_barbershop',
                'slug'          => 'gentleman-cut-barbershop',
                'name'          => 'Gentleman Cut Barbershop & Spa',
                'phone'         => '0812-3456-7803',
                'email'         => 'info@gentlemancut.id',
                'address'       => 'Jl. Senopati Raya No. 45, Kebayoran Baru, Jakarta Selatan',
                'owner' => [
                    'name'     => 'Fajar Nugraha',
                    'email'    => 'barbershop.owner@cooca.id',
                    'password' => 'password123',
                ],
                'staff' => [
                    [
                        'name'     => 'Budi Setiawan (The Blade)',
                        'email'    => 'kapster.budi@cooca.id',
                        'password' => 'password123',
                        'role'     => 'staff',
                    ],
                    [
                        'name'     => 'Yoga Pratama',
                        'email'    => 'kapster.yoga@cooca.id',
                        'password' => 'password123',
                        'role'     => 'staff',
                    ],
                ],
                'location_name' => 'Gentleman Cut - Studio Senopati',
                'categories' => [
                    'Haircut & Styling',
                    'Grooming & Treatment',
                    'Produk Perawatan Rambut',
                ],
                'services' => [
                    [
                        'code'          => 'SRV-BS-001',
                        'name'          => 'Signature Haircut, Wash & Hot Towel',
                        'selling_price' => 70000,
                        'base_cost'     => 20000,
                        'unit'          => 'sesi',
                        'category'      => 'Haircut & Styling',
                        'description'   => 'Konsultasi gaya rambut, potong rambut presisi, keramas relaksasi, dan handuk hangat beraroma terapi.',
                    ],
                    [
                        'code'          => 'SRV-BS-002',
                        'name'          => 'Traditional Shaving & Beard Trimming',
                        'selling_price' => 45000,
                        'base_cost'     => 10000,
                        'unit'          => 'sesi',
                        'category'      => 'Grooming & Treatment',
                        'description'   => 'Cukur jenggot/kumis tradisional dengan pisau silet steril dan aftershave soothing lotion.',
                    ],
                    [
                        'code'          => 'SRV-BS-003',
                        'name'          => 'Hair Spa Creambath & Head Massage',
                        'selling_price' => 90000,
                        'base_cost'     => 25000,
                        'unit'          => 'sesi',
                        'category'      => 'Grooming & Treatment',
                        'description'   => 'Perawatan nutrisi akar rambut creambath ginseng/aloe vera disertai pijat leher dan bahu.',
                    ],
                    [
                        'code'          => 'SRV-BS-004',
                        'name'          => 'Premium Hair Coloring (Ash / Blonde)',
                        'selling_price' => 250000,
                        'base_cost'     => 80000,
                        'unit'          => 'paket',
                        'category'      => 'Haircut & Styling',
                        'description'   => 'Pewarnaan rambut tren pria dengan teknik bleaching aman tanpa merusak kutikula rambut.',
                    ],
                ],
                'goods' => [
                    [
                        'code'          => 'PART-POM-01',
                        'name'          => 'Pomade Water Based Strong Hold 100g',
                        'selling_price' => 95000,
                        'base_cost'     => 55000,
                        'unit'          => 'pcs',
                        'stock'         => 35,
                        'category'      => 'Produk Perawatan Rambut',
                    ],
                    [
                        'code'          => 'PART-TNC-01',
                        'name'          => 'Hair Tonic Ginseng Cooling 150ml',
                        'selling_price' => 65000,
                        'base_cost'     => 38000,
                        'unit'          => 'pcs',
                        'stock'         => 25,
                        'category'      => 'Produk Perawatan Rambut',
                    ],
                ],
                'sample_orders' => [
                    [
                        'staff_idx'     => 0, // Budi
                        'service_notes' => 'Potong taper fade samping tipis + beli pomade water based.',
                        'customer_name' => 'Dimas Arya',
                        'items' => [
                            ['code' => 'SRV-BS-001', 'qty' => 1],
                            ['code' => 'PART-POM-01', 'qty' => 1],
                        ],
                    ],
                ],
                'reservations' => [
                    [
                        'code'           => 'RSV-BS-001',
                        'customer_name'  => 'Reza Rahadian',
                        'customer_phone' => '0811-2233-4455',
                        'customer_email' => 'reza@actor.test',
                        'date'           => Carbon::today()->toDateString(),
                        'time_slot'      => '14:00',
                        'guest_count'    => 1,
                        'notes'          => 'Signature Haircut & Beard Shaving - Request Barber Budi.',
                        'status'         => CommerceReservation::STATUS_CONFIRMED,
                    ],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // 4. Laundry Kiloan & Satuan (service_laundry)
            // ─────────────────────────────────────────────────────────────
            [
                'template_code' => 'service_laundry',
                'slug'          => 'freshclean-laundry',
                'name'          => 'FreshClean Laundry Kiloan & Dry Clean',
                'phone'         => '0812-3456-7804',
                'email'         => 'order@freshcleanlaundry.id',
                'address'       => 'Jl. Tebet Timur Dalam No. 33, Jakarta Selatan',
                'owner' => [
                    'name'     => 'Dewi Lestari',
                    'email'    => 'laundry.owner@cooca.id',
                    'password' => 'password123',
                ],
                'staff' => [
                    [
                        'name'     => 'Siti Rahma',
                        'email'    => 'operator.siti@cooca.id',
                        'password' => 'password123',
                        'role'     => 'staff',
                    ],
                ],
                'location_name' => 'FreshClean Laundry - Outlet Tebet',
                'categories' => [
                    'Laundry Kiloan',
                    'Cuci Satuan & Dry Clean',
                ],
                'services' => [
                    [
                        'code'          => 'SRV-LD-001',
                        'name'          => 'Cuci Setrika Reguler 2 Hari (Kg)',
                        'selling_price' => 8000,
                        'base_cost'     => 2500,
                        'unit'          => 'kg',
                        'category'      => 'Laundry Kiloan',
                        'description'   => 'Pencucian higienis dengan pemisahan warna, pelembut premium & setrika uap rapi.',
                    ],
                    [
                        'code'          => 'SRV-LD-002',
                        'name'          => 'Cuci Kering Kilat Express 6 Jam (Kg)',
                        'selling_price' => 15000,
                        'base_cost'     => 4500,
                        'unit'          => 'kg',
                        'category'      => 'Laundry Kiloan',
                        'description'   => 'Layanan cuci kilat prioritas selesai dalam 6 jam dengan pengeringan suhu terkontrol.',
                    ],
                    [
                        'code'          => 'SRV-LD-003',
                        'name'          => 'Dry Clean Jas Lengkap / Blazer (Stel)',
                        'selling_price' => 55000,
                        'base_cost'     => 15000,
                        'unit'          => 'paket',
                        'category'      => 'Cuci Satuan & Dry Clean',
                        'description'   => 'Dry cleaning profesional tanpa air untuk menjaga struktur kain wol dan jas formal.',
                    ],
                    [
                        'code'          => 'SRV-LD-004',
                        'name'          => 'Cuci Bedcover King Size (Pcs)',
                        'selling_price' => 35000,
                        'base_cost'     => 10000,
                        'unit'          => 'pcs',
                        'category'      => 'Cuci Satuan & Dry Clean',
                        'description'   => 'Pencucian bedcover tebal dengan mesin kapasitas besar dan pengeringan anti bau apek.',
                    ],
                    [
                        'code'          => 'SRV-LD-005',
                        'name'          => 'Cuci Sepatu Sneakers / Canvas',
                        'selling_price' => 40000,
                        'base_cost'     => 12000,
                        'unit'          => 'pasang',
                        'category'      => 'Cuci Satuan & Dry Clean',
                        'description'   => 'Deep clean sepatu luar-dalam, unyellowing midsole dan disinfektan anti bakteri.',
                    ],
                ],
                'goods' => [],
                'sample_orders' => [
                    [
                        'laundry_weight_kg'       => 6.50,
                        'rack_location'           => 'Rak B-04',
                        'laundry_status'          => 'ironing',
                        'estimated_completion_at' => Carbon::now()->addDay()->toDateTimeString(),
                        'staff_idx'               => 0, // Siti Rahma
                        'service_notes'           => 'Pakaian sehari-hari campur, parfum sakura wangi tahan lama.',
                        'customer_name'           => 'Ibu Maya Indah',
                        'items' => [
                            ['code' => 'SRV-LD-001', 'qty' => 6.5],
                        ],
                    ],
                    [
                        'laundry_weight_kg'       => 4.00,
                        'rack_location'           => 'Loker 12',
                        'laundry_status'          => 'ready',
                        'estimated_completion_at' => Carbon::now()->subHours(2)->toDateTimeString(),
                        'staff_idx'               => 0,
                        'service_notes'           => 'Cuci kilat express + 1 bedcover king size.',
                        'customer_name'           => 'Pak Anton Wijaya',
                        'items' => [
                            ['code' => 'SRV-LD-002', 'qty' => 4],
                            ['code' => 'SRV-LD-004', 'qty' => 1],
                        ],
                    ],
                ],
                'reservations' => [
                    [
                        'code'           => 'RSV-LD-001',
                        'customer_name'  => 'Ibu Siska Permata',
                        'customer_phone' => '0815-6677-8899',
                        'customer_email' => 'siska@email.test',
                        'date'           => Carbon::today()->toDateString(),
                        'time_slot'      => '16:00',
                        'guest_count'    => 1,
                        'notes'          => 'Request Jemput Cucian ke Rumah: Jl. Kemang Timur No. 12 (Estimasi 10 kg).',
                        'status'         => CommerceReservation::STATUS_CONFIRMED,
                    ],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // 5. Event Organizer & Wedding Planner (service_event)
            // ─────────────────────────────────────────────────────────────
            [
                'template_code' => 'service_event',
                'slug'          => 'vow-vision-event-organizer',
                'name'          => 'Vow & Vision Event & Wedding Organizer',
                'phone'         => '0812-3456-7805',
                'email'         => 'hello@vowandvision.id',
                'address'       => 'Jl. Cik Ditiro No. 20, Menteng, Jakarta Pusat',
                'owner' => [
                    'name'     => 'Clarissa Utama',
                    'email'    => 'event.owner@cooca.id',
                    'password' => 'password123',
                ],
                'staff' => [
                    [
                        'name'     => 'Anisa Putri',
                        'email'    => 'planner.anisa@cooca.id',
                        'password' => 'password123',
                        'role'     => 'staff',
                    ],
                ],
                'location_name' => 'Vow & Vision - Studio & Wedding Gallery',
                'categories' => [
                    'Paket Pernikahan (Wedding)',
                    'Corporate Event & Talent',
                ],
                'services' => [
                    [
                        'code'          => 'SRV-EV-001',
                        'name'          => 'Paket Wedding Silver (All-In 500 Pax)',
                        'selling_price' => 45000000,
                        'base_cost'     => 32000000,
                        'unit'          => 'proyek',
                        'category'      => 'Paket Pernikahan (Wedding)',
                        'description'   => 'Dekorasi pelaminan 12m, catering 500 pax, rias & busana pengantin, WO 8 crew, sound & lighting.',
                    ],
                    [
                        'code'          => 'SRV-EV-002',
                        'name'          => 'Paket Wedding Intimate Gold (200 Pax)',
                        'selling_price' => 30000000,
                        'base_cost'     => 20000000,
                        'unit'          => 'proyek',
                        'category'      => 'Paket Pernikahan (Wedding)',
                        'description'   => 'Konsep intimate wedding modern elegan, live acoustic band, master of ceremony & full wedding timeline.',
                    ],
                    [
                        'code'          => 'SRV-EV-003',
                        'name'          => 'Jasa MC Profesional & Sound System 5000W',
                        'selling_price' => 4500000,
                        'base_cost'     => 2200000,
                        'unit'          => 'sesi',
                        'category'      => 'Corporate Event & Talent',
                        'description'   => 'MC dwibahasa (ID/EN) berpengalaman dan paket audio sound system 5000 watt lengkap operator.',
                    ],
                    [
                        'code'          => 'SRV-EV-004',
                        'name'          => 'Jasa Foto & Cinematic Video 2 Kamera',
                        'selling_price' => 6000000,
                        'base_cost'     => 3000000,
                        'unit'          => 'proyek',
                        'category'      => 'Corporate Event & Talent',
                        'description'   => 'Dokumentasi full day foto unlimited, video highlight teaser 1 menit & video cinematic 5 menit 4K.',
                    ],
                ],
                'goods' => [],
                'sample_orders' => [
                    [
                        'staff_idx'     => 0, // Anisa Putri
                        'service_notes' => 'Acara Annual Gala Dinner PT Nusantara Medika 2026 - Paket Sound System + MC.',
                        'customer_name' => 'PT Nusantara Medika',
                        'items' => [
                            ['code' => 'SRV-EV-003', 'qty' => 1],
                            ['code' => 'SRV-EV-004', 'qty' => 1],
                        ],
                    ],
                ],
                'reservations' => [
                    [
                        'code'           => 'RSV-EV-001',
                        'customer_name'  => 'Kevin & Melisa',
                        'customer_phone' => '0818-0011-2233',
                        'customer_email' => 'kevin.melisa@wedding.test',
                        'date'           => Carbon::today()->addMonths(2)->toDateString(),
                        'time_slot'      => '18:00',
                        'guest_count'    => 500,
                        'notes'          => 'Wedding Reception: Grand Ballroom Kempinski - Paket Wedding Silver All-In.',
                        'status'         => CommerceReservation::STATUS_CONFIRMED,
                    ],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // 6. Creative Agency & Jasa Digital (service_agency)
            // ─────────────────────────────────────────────────────────────
            [
                'template_code' => 'service_agency',
                'slug'          => 'nexus-digital-creative',
                'name'          => 'Nexus Digital & Creative Studio',
                'phone'         => '0812-3456-7806',
                'email'         => 'project@nexusstudio.id',
                'address'       => 'Kawasan SCBD Lot 8, Senayan, Jakarta Selatan',
                'owner' => [
                    'name'     => 'Aditya Surya',
                    'email'    => 'agency.owner@cooca.id',
                    'password' => 'password123',
                ],
                'staff' => [
                    [
                        'name'     => 'Kenzo Tanaka',
                        'email'    => 'designer.kenzo@cooca.id',
                        'password' => 'password123',
                        'role'     => 'staff',
                    ],
                ],
                'location_name' => 'Nexus Creative Hub - SCBD',
                'categories' => [
                    'Branding & Desain',
                    'Website & Web Application',
                    'Social Media & Video',
                ],
                'services' => [
                    [
                        'code'          => 'SRV-AG-001',
                        'name'          => 'Brand Identity System & Logo Guidelines',
                        'selling_price' => 7500000,
                        'base_cost'     => 2500000,
                        'unit'          => 'proyek',
                        'category'      => 'Branding & Desain',
                        'description'   => 'Konsep logo visual, filosofi warna, typography system, brand deck guideline 40 halaman & file vektor master.',
                    ],
                    [
                        'code'          => 'SRV-AG-002',
                        'name'          => 'Pembuatan Website Company Profile Responsive',
                        'selling_price' => 12000000,
                        'base_cost'     => 4000000,
                        'unit'          => 'proyek',
                        'category'      => 'Website & Web Application',
                        'description'   => 'Desain UI/UX modern Apple HIG, kecepatan loading ultra-cepat, CMS admin mandiri & SEO optimized.',
                    ],
                    [
                        'code'          => 'SRV-AG-003',
                        'name'          => 'Monthly Social Media Management (15 Post + 8 Reels)',
                        'selling_price' => 5500000,
                        'base_cost'     => 2000000,
                        'unit'          => 'bulan',
                        'category'      => 'Social Media & Video',
                        'description'   => 'Perencanaan konten kalender bulanan, copywriting pilar, desain grafis feed & produksi video reels/tiktok.',
                    ],
                    [
                        'code'          => 'SRV-AG-004',
                        'name'          => 'Jasa Video Commercial Ads & Motion Graphic',
                        'selling_price' => 8500000,
                        'base_cost'     => 3200000,
                        'unit'          => 'proyek',
                        'category'      => 'Social Media & Video',
                        'description'   => 'Storyboard kreatif, shooting 4K, voice over profesional, 2D/3D motion graphic & sound design.',
                    ],
                ],
                'goods' => [],
                'sample_orders' => [
                    [
                        'staff_idx'     => 0, // Kenzo Tanaka
                        'service_notes' => 'Proyek Pembuatan Brand Identity & Website Startup Maju Terus.',
                        'customer_name' => 'PT Maju Terus Nusantara',
                        'items' => [
                            ['code' => 'SRV-AG-001', 'qty' => 1],
                            ['code' => 'SRV-AG-002', 'qty' => 1],
                        ],
                    ],
                ],
                'reservations' => [
                    [
                        'code'           => 'RSV-AG-001',
                        'customer_name'  => 'Ibu Stephanie (PT Sumber Pangan)',
                        'customer_phone' => '0812-7788-9900',
                        'customer_email' => 'stephanie@sumberpangan.test',
                        'date'           => Carbon::today()->addDay()->toDateString(),
                        'time_slot'      => '14:00',
                        'guest_count'    => 3,
                        'notes'          => 'Sesi Konsultasi & Briefing Redesain Brand Identity Ekspor via Online Zoom.',
                        'status'         => CommerceReservation::STATUS_CONFIRMED,
                    ],
                ],
            ],
        ];

        foreach ($businessesData as $bData) {
            DB::transaction(function () use ($bData, $units): void {
                // 1. Create or update Business
                $business = Business::updateOrCreate(
                    ['slug' => $bData['slug']],
                    [
                        'name'               => $bData['name'],
                        'template_code'      => $bData['template_code'],
                        'industry_category'  => $bData['template_code'],
                        'currency'           => 'IDR',
                        'currency_precision' => 0,
                        'rounding_strategy'  => Business::ROUNDING_ROUND_100,
                        'phone'              => $bData['phone'],
                        'email'              => $bData['email'],
                        'address'            => $bData['address'],
                    ]
                );

                // 2. Create Owner User & attach
                $ownerUser = User::updateOrCreate(
                    ['email' => $bData['owner']['email']],
                    [
                        'name'              => $bData['owner']['name'],
                        'password'          => Hash::make($bData['owner']['password']),
                        'email_verified_at' => now(),
                    ]
                );

                $business->users()->syncWithoutDetaching([
                    $ownerUser->id => ['id' => (string) Str::uuid(), 'role' => 'owner'],
                ]);
                $ownerUser->update(['active_business_id' => $business->id]);

                // 3. Create Staff / Mechanics / Operators
                $createdStaffUsers = [];
                foreach ($bData['staff'] as $staffData) {
                    $staffUser = User::updateOrCreate(
                        ['email' => $staffData['email']],
                        [
                            'name'              => $staffData['name'],
                            'password'          => Hash::make($staffData['password']),
                            'email_verified_at' => now(),
                        ]
                    );

                    $business->users()->syncWithoutDetaching([
                        $staffUser->id => ['id' => (string) Str::uuid(), 'role' => $staffData['role'] ?? 'staff'],
                    ]);
                    $staffUser->update(['active_business_id' => $business->id]);
                    $createdStaffUsers[] = $staffUser;
                }

                // 4. Create Location (Branch/Warehouse)
                $location = Location::updateOrCreate(
                    ['business_id' => $business->id, 'slug' => Str::slug($bData['location_name'])],
                    [
                        'name'       => $bData['location_name'],
                        'type'       => 'outlet',
                        'is_active'  => true,
                        'address'    => $bData['address'],
                        'phone'      => $bData['phone'],
                        'is_primary' => true,
                    ]
                );

                // 5. Create Product Categories
                $catMap = [];
                foreach ($bData['categories'] as $catName) {
                    $cat = ProductCategory::updateOrCreate(
                        ['business_id' => $business->id, 'slug' => Str::slug($catName)],
                        ['name' => $catName, 'description' => "Kategori {$catName} untuk {$business->name}"]
                    );
                    $catMap[$catName] = $cat;
                }

                // 6. Create Services (type = 'service')
                $productMap = [];
                foreach ($bData['services'] as $sSpec) {
                    $catId = isset($catMap[$sSpec['category']]) ? $catMap[$sSpec['category']]->id : null;
                    $unitObj = $units[$sSpec['unit']] ?? $units['paket'] ?? $units['pcs'];

                    $product = Product::updateOrCreate(
                        ['business_id' => $business->id, 'code' => $sSpec['code']],
                        [
                            'type'                => Product::TYPE_SERVICE,
                            'name'                => $sSpec['name'],
                            'slug'                => Str::slug($sSpec['name']),
                            'category_id'         => $catId,
                            'output_unit_id'      => $unitObj->id,
                            'selling_price'       => $sSpec['selling_price'],
                            'base_cost'           => $sSpec['base_cost'],
                            'business_type_hint'  => $bData['template_code'],
                            'description'         => $sSpec['description'] ?? "Layanan {$sSpec['name']}",
                            'show_in_pos'         => true,
                            'show_in_sales_order' => true,
                            'show_in_website'     => true,
                            'show_price_on_web'   => true,
                            'is_active'           => true,
                        ]
                    );
                    $productMap[$sSpec['code']] = $product;
                }

                // 7. Create Physical Goods / Spareparts (type = 'goods' with inventory)
                foreach ($bData['goods'] as $gSpec) {
                    $catId = isset($catMap[$gSpec['category']]) ? $catMap[$gSpec['category']]->id : null;
                    $unitObj = $units[$gSpec['unit']] ?? $units['pcs'];

                    $product = Product::updateOrCreate(
                        ['business_id' => $business->id, 'code' => $gSpec['code']],
                        [
                            'type'                => Product::TYPE_GOODS,
                            'name'                => $gSpec['name'],
                            'slug'                => Str::slug($gSpec['name']),
                            'category_id'         => $catId,
                            'output_unit_id'      => $unitObj->id,
                            'selling_price'       => $gSpec['selling_price'],
                            'base_cost'           => $gSpec['base_cost'],
                            'business_type_hint'  => $bData['template_code'],
                            'description'         => "Barang fisik & suku cadang {$gSpec['name']}",
                            'show_in_pos'         => true,
                            'show_in_sales_order' => true,
                            'show_in_website'     => true,
                            'show_price_on_web'   => true,
                            'is_active'           => true,
                        ]
                    );
                    $productMap[$gSpec['code']] = $product;

                    // Initial Stock for goods
                    $stockQty = (float) ($gSpec['stock'] ?? 10);
                    if ($stockQty > 0) {
                        InventoryStock::updateOrCreate(
                            [
                                'business_id' => $business->id,
                                'location_id' => $location->id,
                                'product_id'  => $product->id,
                            ],
                            [
                                'material_id'       => null,
                                'quantity'          => $stockQty,
                                'reserved_quantity' => 0,
                                'last_cost'         => $gSpec['base_cost'],
                                'avg_purchase_cost' => $gSpec['base_cost'],
                            ]
                        );

                        StockMovement::updateOrCreate(
                            [
                                'business_id'      => $business->id,
                                'product_id'       => $product->id,
                                'reference_number' => 'INIT-' . $product->code,
                            ],
                            [
                                'location_id'     => $location->id,
                                'material_id'     => null,
                                'movement_type'   => StockMovement::TYPE_INITIAL,
                                'quantity_change' => $stockQty,
                                'balance_after'   => $stockQty,
                                'unit_cost'       => $gSpec['base_cost'],
                                'total_cost'      => $stockQty * $gSpec['base_cost'],
                                'notes'           => 'Saldo Awal Suku Cadang & Barang',
                                'created_by'      => $ownerUser->id,
                            ]
                        );
                    }
                }

                // 8. Create Sample Closed POS Shift & Sample Orders with SPK / Nopol / Timbangan
                if (! empty($bData['sample_orders'])) {
                    $shift = PosShift::create([
                        'business_id'           => $business->id,
                        'location_id'           => $location->id,
                        'user_id'               => $ownerUser->id,
                        'opened_at'             => Carbon::yesterday()->setHour(8)->setMinute(0),
                        'closed_at'             => Carbon::yesterday()->setHour(18)->setMinute(0),
                        'opening_cash'          => 300000,
                        'closing_cash_actual'   => 1500000,
                        'closing_cash_expected' => 1500000,
                        'cash_difference'       => 0,
                        'total_cash_sales'      => 1200000,
                        'total_non_cash_sales'  => 0,
                        'status'                => PosShift::STATUS_CLOSED,
                    ]);

                    foreach ($bData['sample_orders'] as $idx => $orderSpec) {
                        $customer = Customer::firstOrCreate(
                            ['business_id' => $business->id, 'name' => $orderSpec['customer_name'] ?? 'Pelanggan Jasa'],
                            [
                                'phone'            => '0812-' . rand(1000, 9999) . '-' . rand(1000, 9999),
                                'shipping_address' => 'Jakarta / Tangerang Area',
                            ]
                        );

                        $staffUser = isset($orderSpec['staff_idx']) && isset($createdStaffUsers[$orderSpec['staff_idx']])
                            ? $createdStaffUsers[$orderSpec['staff_idx']]
                            : $ownerUser;

                        $subtotal = 0.0;
                        $totalHpp = 0.0;
                        $orderItemsToCreate = [];

                        foreach ($orderSpec['items'] as $itemSpec) {
                            $prod = $productMap[$itemSpec['code']] ?? null;
                            if (! $prod) {
                                continue;
                            }
                            $qty = (float) $itemSpec['qty'];
                            $price = (float) $prod->selling_price;
                            $hpp = (float) $prod->base_cost;
                            $lineSubtotal = $price * $qty;
                            $lineHpp = $hpp * $qty;

                            $subtotal += $lineSubtotal;
                            $totalHpp += $lineHpp;

                            $orderItemsToCreate[] = [
                                'product_id'    => $prod->id,
                                'product_name'  => $prod->name,
                                'product_code'  => $prod->code,
                                'unit_price'    => $price,
                                'unit_cost_hpp' => $hpp,
                                'quantity'      => $qty,
                                'subtotal'      => $lineSubtotal,
                                'total_price'   => $lineSubtotal,
                                'total_hpp'     => $lineHpp,
                            ];
                        }

                        $order = PosOrder::create([
                            'business_id'             => $business->id,
                            'location_id'             => $location->id,
                            'pos_shift_id'            => $shift->id,
                            'user_id'                 => $staffUser->id,
                            'customer_id'             => $customer->id,
                            'order_number'            => 'SPK-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                            'order_date'              => Carbon::yesterday()->toDateString(),
                            'status'                  => PosOrder::STATUS_COMPLETED,
                            'order_type'              => 'takeaway',
                            'customer_name_guest'     => $customer->name,
                            'subtotal'                => $subtotal,
                            'total_amount'            => $subtotal,
                            'paid_amount'             => $subtotal,
                            'change_amount'           => 0,
                            'total_hpp_cost'          => $totalHpp,
                            'total_gross_profit'      => $subtotal - $totalHpp,
                            'notes'                   => $orderSpec['service_notes'] ?? 'Pengerjaan Servis & Layanan Jasa',
                            // Service & Automotive specific fields:
                            'vehicle_license_plate'   => $orderSpec['vehicle_license_plate'] ?? null,
                            'vehicle_model'           => $orderSpec['vehicle_model'] ?? null,
                            'vehicle_mileage'         => $orderSpec['vehicle_mileage'] ?? null,
                            'technician_id'           => $staffUser->id,
                            'service_notes'           => $orderSpec['service_notes'] ?? null,
                            'laundry_weight_kg'       => $orderSpec['laundry_weight_kg'] ?? null,
                            'rack_location'           => $orderSpec['rack_location'] ?? null,
                            'estimated_completion_at' => $orderSpec['estimated_completion_at'] ?? null,
                            'laundry_status'          => $orderSpec['laundry_status'] ?? null,
                        ]);

                        foreach ($orderItemsToCreate as $itemData) {
                            $itemData['pos_order_id'] = $order->id;
                            PosOrderItem::create($itemData);
                        }

                        PosOrderPayment::create([
                            'pos_order_id'   => $order->id,
                            'payment_method' => 'cash',
                            'amount'         => $subtotal,
                            'net_amount'     => $subtotal,
                            'status'         => 'paid',
                        ]);
                    }
                }

                // 9. Create Sample Reservations / Booking Slots
                if (! empty($bData['reservations'])) {
                    foreach ($bData['reservations'] as $rSpec) {
                        CommerceReservation::create([
                            'business_id'      => $business->id,
                            'reservation_code' => $rSpec['code'] ?? ('RSV-' . strtoupper(Str::random(6))),
                            'customer_name'    => $rSpec['customer_name'],
                            'customer_phone'   => $rSpec['customer_phone'],
                            'customer_email'   => $rSpec['customer_email'],
                            'reservation_date' => $rSpec['date'],
                            'time_slot'        => $rSpec['time_slot'],
                            'guest_count'      => $rSpec['guest_count'] ?? 1,
                            'status'           => $rSpec['status'] ?? CommerceReservation::STATUS_CONFIRMED,
                            'notes'            => $rSpec['notes'] ?? 'Reservasi antrean layanan jasa',
                        ]);
                    }
                }
            });
        }
    }

    /**
     * Resolve default core units by their codes.
     *
     * @return array<string, Unit>
     */
    private function resolveUnits(): array
    {
        $all = Unit::all();
        $map = [];
        foreach ($all as $u) {
            $map[$u->code] = $u;
        }

        $needed = [
            'pcs'    => ['name' => 'Pieces / Buah', 'category' => 'quantity'],
            'kg'     => ['name' => 'Kilogram', 'category' => 'weight'],
            'paket'  => ['name' => 'Paket Layanan', 'category' => 'custom'],
            'sesi'   => ['name' => 'Sesi Layanan', 'category' => 'time'],
            'proyek' => ['name' => 'Proyek / Kontrak', 'category' => 'custom'],
            'bulan'  => ['name' => 'Bulan Layanan', 'category' => 'time'],
            'pasang' => ['name' => 'Pasang', 'category' => 'quantity'],
            'stel'   => ['name' => 'Stel Pakaian', 'category' => 'quantity'],
        ];

        foreach ($needed as $code => $data) {
            if (! isset($map[$code])) {
                $map[$code] = Unit::firstOrCreate(['code' => $code], [
                    'name'     => $data['name'],
                    'category' => $data['category'],
                    'is_base'  => false,
                ]);
            }
        }

        return $map;
    }
}
