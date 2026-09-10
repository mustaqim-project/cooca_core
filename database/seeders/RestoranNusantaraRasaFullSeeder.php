<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\BomHeader;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\CostModel;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\MaterialPrice;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\ModifierOptionMaterial;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderItemModifier;
use App\Models\PosOrderPayment;
use App\Models\PosShift;
use App\Models\PosTable;
use App\Models\PosTableSession;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class RestoranNusantaraRasaFullSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // ─────────────────────────────────────────────────────────────────
            // 1. BUSINESS PROFILE & LOGGED-IN USER ASSIGNMENT
            // ─────────────────────────────────────────────────────────────────
            $business = Business::where('slug', 'restoran-nusantara-rasa')->first();

            if (! $business) {
                $business = Business::create([
                    'id' => (string) Str::uuid(),
                    'name' => 'Restoran Nusantara Rasa',
                    'slug' => 'restoran-nusantara-rasa',
                    'currency' => 'IDR',
                    'currency_precision' => 0,
                    'phone' => '0812-3456-7890',
                    'email' => 'reservasi@nusantararasa.id',
                    'address' => 'Jl. Sabang No. 18, Menteng, Jakarta Pusat 10350',
                    'pos_show_product_images' => true,
                ]);
            } else {
                $business->update([
                    'phone' => '0812-3456-7890',
                    'email' => 'reservasi@nusantararasa.id',
                    'address' => 'Jl. Sabang No. 18, Menteng, Jakarta Pusat 10350',
                    'pos_show_product_images' => true,
                ]);
            }

            // Hubungkan semua user demo & resto ke bisnis ini sebagai Owner
            $usersToLink = User::whereIn('email', [
                'demo@cooca.id',
                'owner.resto@cooca.id',
                'bakery@cooca.id',
            ])->get();

            if ($usersToLink->isEmpty()) {
                $firstUser = User::first();
                if ($firstUser) {
                    $usersToLink = collect([$firstUser]);
                }
            }

            foreach ($usersToLink as $u) {
                BusinessMembership::firstOrCreate(
                    ['business_id' => $business->id, 'user_id' => $u->id],
                    ['id' => (string) Str::uuid(), 'role' => 'owner']
                );
            }

            $primaryOwner = $usersToLink->first() ?? User::first();

            // ─────────────────────────────────────────────────────────────────
            // 2. LOCATIONS / OUTLET & CENTRAL KITCHEN
            // ─────────────────────────────────────────────────────────────────
            $locOutlet = Location::updateOrCreate(
                ['business_id' => $business->id, 'code' => 'LOC-MNT'],
                [
                    'name' => 'Restoran Nusantara Rasa - Menteng',
                    'slug' => 'nusantara-rasa-menteng',
                    'type' => 'outlet',
                    'address' => 'Jl. Sabang No. 18, Menteng, Jakarta Pusat',
                    'phone' => '0812-3456-7890',
                    'is_primary' => true,
                    'is_active' => true,
                ]
            );

            $locKitchen = Location::updateOrCreate(
                ['business_id' => $business->id, 'code' => 'LOC-CK'],
                [
                    'name' => 'Central Kitchen & Gudang Bahan',
                    'slug' => 'central-kitchen-nusantara',
                    'type' => 'warehouse',
                    'address' => 'Jl. Kebon Sirih Barat No. 5, Jakarta Pusat',
                    'phone' => '0812-9876-5432',
                    'is_primary' => false,
                    'is_active' => true,
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // 3. UNITS & MATERIAL CATEGORIES
            // ─────────────────────────────────────────────────────────────────
            $units = [
                'kg' => Unit::firstOrCreate(['code' => 'kg'], ['name' => 'Kilogram', 'symbol' => 'kg', 'is_base' => true]),
                'g' => Unit::firstOrCreate(['code' => 'g'], ['name' => 'Gram', 'symbol' => 'g', 'is_base' => false]),
                'l' => Unit::firstOrCreate(['code' => 'liter'], ['name' => 'Liter', 'symbol' => 'L', 'is_base' => true]),
                'ml' => Unit::firstOrCreate(['code' => 'ml'], ['name' => 'Mililiter', 'symbol' => 'ml', 'is_base' => false]),
                'porsi' => Unit::firstOrCreate(['code' => 'porsi'], ['name' => 'Porsi', 'symbol' => 'porsi', 'is_base' => true]),
                'pcs' => Unit::firstOrCreate(['code' => 'pcs'], ['name' => 'Pieces / Butir', 'symbol' => 'pcs', 'is_base' => true]),
                'box' => Unit::firstOrCreate(['code' => 'box'], ['name' => 'Kotak / Box', 'symbol' => 'box', 'is_base' => false]),
                'gelas' => Unit::firstOrCreate(['code' => 'gelas'], ['name' => 'Gelas', 'symbol' => 'gls', 'is_base' => false]),
            ];

            $matCats = [
                'daging' => MaterialCategory::firstOrCreate(['business_id' => $business->id, 'slug' => 'daging-unggas'], ['name' => 'Daging & Unggas']),
                'seafood' => MaterialCategory::firstOrCreate(['business_id' => $business->id, 'slug' => 'seafood-ikan'], ['name' => 'Seafood & Ikan']),
                'bumbu' => MaterialCategory::firstOrCreate(['business_id' => $business->id, 'slug' => 'bumbu-rempah'], ['name' => 'Bumbu & Rempah Alami']),
                'sayur' => MaterialCategory::firstOrCreate(['business_id' => $business->id, 'slug' => 'sayur-buah'], ['name' => 'Sayur Mayur & Buah']),
                'sembako' => MaterialCategory::firstOrCreate(['business_id' => $business->id, 'slug' => 'sembako-beras'], ['name' => 'Sembako, Beras & Minyak']),
                'minuman' => MaterialCategory::firstOrCreate(['business_id' => $business->id, 'slug' => 'kopi-minuman'], ['name' => 'Kopi, Susu & Gula']),
                'kemasan' => MaterialCategory::firstOrCreate(['business_id' => $business->id, 'slug' => 'kemasan-fnb'], ['name' => 'Kemasan & Perlengkapan']),
            ];

            // ─────────────────────────────────────────────────────────────────
            // 4. SUPPLIERS
            // ─────────────────────────────────────────────────────────────────
            $suppliers = [
                'daging' => Supplier::updateOrCreate(
                    ['business_id' => $business->id, 'code' => 'SUP-DGG-01'],
                    [
                        'name' => 'CV Berkah Daging Nusantara',
                        'phone' => '0811-2233-4455',
                        'email' => 'sales@berkahdaging.co.id',
                        'address' => 'Pasar Induk Kramat Jati Blok D-12, Jakarta Timur',
                    ]
                ),
                'rempah' => Supplier::updateOrCreate(
                    ['business_id' => $business->id, 'code' => 'SUP-RMP-02'],
                    [
                        'name' => 'PT Sumber Rempah Rembang',
                        'phone' => '0812-8899-0011',
                        'email' => 'order@rempahrembang.com',
                        'address' => 'Kawasan Pergudangan Pluit Blok C, Jakarta Utara',
                    ]
                ),
                'sayur' => Supplier::updateOrCreate(
                    ['business_id' => $business->id, 'code' => 'SUP-SYR-03'],
                    [
                        'name' => 'Toko Tani Sayur Segar Cipanas',
                        'phone' => '0813-7766-5544',
                        'email' => 'sayurcipanas@gmail.com',
                        'address' => 'Jl. Raya Pacet Km 3, Cianjur',
                    ]
                ),
                'pangan' => Supplier::updateOrCreate(
                    ['business_id' => $business->id, 'code' => 'SUP-PNG-04'],
                    [
                        'name' => 'Distributor Pangan Sejahtera (Beras & Minyak)',
                        'phone' => '0815-3344-5566',
                        'email' => 'distribusi@pangansejahtera.id',
                        'address' => 'Jl. Daan Mogot Km 11, Jakarta Barat',
                    ]
                ),
                'kopi' => Supplier::updateOrCreate(
                    ['business_id' => $business->id, 'code' => 'SUP-KPI-05'],
                    [
                        'name' => 'CV Aroma Nusantara Coffee & Dairy',
                        'phone' => '0818-4455-6677',
                        'email' => 'aromanusantara@coffee.id',
                        'address' => 'Jl. Panglima Polim No. 42, Jakarta Selatan',
                    ]
                ),
            ];

            // ─────────────────────────────────────────────────────────────────
            // 5. MATERIALS & INVENTORY STOCKS
            // ─────────────────────────────────────────────────────────────────
            $materialSpecs = [
                ['code' => 'MAT-DGG-01', 'name' => 'Daging Sapi Gandik/Sengkel Segar', 'cat' => 'daging', 'unit' => 'kg', 'price' => 125000, 'sup' => 'daging', 'stock' => 45.0],
                ['code' => 'MAT-AYM-02', 'name' => 'Ayam Pejantan Utuh Segar', 'cat' => 'daging', 'unit' => 'pcs', 'price' => 36000, 'sup' => 'daging', 'stock' => 60.0],
                ['code' => 'MAT-GRM-03', 'name' => 'Ikan Gurame Hidup Segar', 'cat' => 'seafood', 'unit' => 'kg', 'price' => 46000, 'sup' => 'daging', 'stock' => 30.0],
                ['code' => 'MAT-BRS-04', 'name' => 'Beras Pandan Wangi Cianjur', 'cat' => 'sembako', 'unit' => 'kg', 'price' => 16000, 'sup' => 'pangan', 'stock' => 250.0],
                ['code' => 'MAT-SNT-05', 'name' => 'Santan Kelapa Murni Kental', 'cat' => 'sembako', 'unit' => 'liter', 'price' => 18000, 'sup' => 'pangan', 'stock' => 50.0],
                ['code' => 'MAT-BMR-06', 'name' => 'Bawang Merah Brebes Super', 'cat' => 'bumbu', 'unit' => 'kg', 'price' => 38000, 'sup' => 'rempah', 'stock' => 35.0],
                ['code' => 'MAT-BPT-07', 'name' => 'Bawang Putih Kating', 'cat' => 'bumbu', 'unit' => 'kg', 'price' => 36000, 'sup' => 'rempah', 'stock' => 30.0],
                ['code' => 'MAT-CBK-08', 'name' => 'Cabai Merah Keriting', 'cat' => 'bumbu', 'unit' => 'kg', 'price' => 42000, 'sup' => 'rempah', 'stock' => 25.0],
                ['code' => 'MAT-CBR-09', 'name' => 'Cabai Rawit Merah Domba', 'cat' => 'bumbu', 'unit' => 'kg', 'price' => 55000, 'sup' => 'rempah', 'stock' => 20.0],
                ['code' => 'MAT-RMP-10', 'name' => 'Bumbu Rempah Basah Lengkap', 'cat' => 'bumbu', 'unit' => 'kg', 'price' => 25000, 'sup' => 'rempah', 'stock' => 20.0],
                ['code' => 'MAT-GLA-11', 'name' => 'Gula Aren Organik Cair', 'cat' => 'minuman', 'unit' => 'liter', 'price' => 28000, 'sup' => 'kopi', 'stock' => 40.0],
                ['code' => 'MAT-KPI-12', 'name' => 'Biji Kopi Blend Arabika Gayo & Robusta', 'cat' => 'minuman', 'unit' => 'kg', 'price' => 120000, 'sup' => 'kopi', 'stock' => 25.0],
                ['code' => 'MAT-SSU-13', 'name' => 'Susu Segar Pasteurisasi Greenfields', 'cat' => 'minuman', 'unit' => 'liter', 'price' => 19000, 'sup' => 'kopi', 'stock' => 60.0],
                ['code' => 'MAT-CND-14', 'name' => 'Cendol Pandan Suji Alami', 'cat' => 'minuman', 'unit' => 'kg', 'price' => 15000, 'sup' => 'sayur', 'stock' => 25.0],
                ['code' => 'MAT-DRN-15', 'name' => 'Daging Durian Montong Beku', 'cat' => 'minuman', 'unit' => 'kg', 'price' => 78000, 'sup' => 'sayur', 'stock' => 15.0],
                ['code' => 'MAT-TMP-16', 'name' => 'Tempe Kedelai Murni Daun Pisang', 'cat' => 'sayur', 'unit' => 'pcs', 'price' => 5000, 'sup' => 'sayur', 'stock' => 50.0],
                ['code' => 'MAT-TLR-17', 'name' => 'Telur Ayam Negeri Segar', 'cat' => 'daging', 'unit' => 'kg', 'price' => 28000, 'sup' => 'daging', 'stock' => 40.0],
                ['code' => 'MAT-MYK-18', 'name' => 'Minyak Goreng Sawit Premium', 'cat' => 'sembako', 'unit' => 'liter', 'price' => 17000, 'sup' => 'pangan', 'stock' => 80.0],
                ['code' => 'MAT-KRP-19', 'name' => 'Kerupuk Udang Sidoarjo Mentah', 'cat' => 'sembako', 'unit' => 'kg', 'price' => 48000, 'sup' => 'pangan', 'stock' => 20.0],
                ['code' => 'MAT-KMS-20', 'name' => 'Box Bento Sekat & Paper Cup Set', 'cat' => 'kemasan', 'unit' => 'pcs', 'price' => 2000, 'sup' => 'pangan', 'stock' => 300.0],
            ];

            $materialMap = [];
            foreach ($materialSpecs as $ms) {
                $mat = Material::updateOrCreate(
                    ['business_id' => $business->id, 'code' => $ms['code']],
                    [
                        'name' => $ms['name'],
                        'slug' => Str::slug($ms['name']),
                        'category_id' => $matCats[$ms['cat']]->id,
                        'unit_id' => $units[$ms['unit']]->id,
                        'supplier_id' => $suppliers[$ms['sup']]->id,
                        'description' => "Bahan baku standar resto Nusantara Rasa ({$ms['name']})",
                        'discontinued_at' => null,
                    ]
                );

                MaterialPrice::updateOrCreate(
                    ['business_id' => $business->id, 'material_id' => $mat->id, 'sequence' => 1],
                    [
                        'supplier_id' => $suppliers[$ms['sup']]->id,
                        'purchase_unit_id' => $units[$ms['unit']]->id,
                        'purchase_price' => $ms['price'],
                        'effective_date' => Carbon::now()->subMonths(1),
                    ]
                );

                InventoryStock::updateOrCreate(
                    ['business_id' => $business->id, 'location_id' => $locOutlet->id, 'material_id' => $mat->id],
                    [
                        'quantity' => $ms['stock'],
                        'last_cost' => $ms['price'],
                        'avg_purchase_cost' => $ms['price'],
                    ]
                );

                $materialMap[$ms['code']] = $mat;
            }

            // ─────────────────────────────────────────────────────────────────
            // 6. PRODUCT CATEGORIES & MENU ITEMS
            // ─────────────────────────────────────────────────────────────────
            $prodCats = [
                'utama' => ProductCategory::updateOrCreate(['business_id' => $business->id, 'slug' => 'makanan-utama'], ['name' => 'Makanan Utama']),
                'camilan' => ProductCategory::updateOrCreate(['business_id' => $business->id, 'slug' => 'kudapan-camilan'], ['name' => 'Kudapan & Camilan']),
                'minuman' => ProductCategory::updateOrCreate(['business_id' => $business->id, 'slug' => 'minuman-tradisional'], ['name' => 'Minuman Nusantara']),
                'katering' => ProductCategory::updateOrCreate(['business_id' => $business->id, 'slug' => 'paket-katering'], ['name' => 'Paket Katering & Acara']),
            ];

            $productCatalog = [
                [
                    'code' => 'FNB-RND-001',
                    'name' => 'Rendang Daging Sapi Payakumbuh',
                    'cat' => 'utama',
                    'unit' => 'porsi',
                    'price' => 45000,
                    'cost' => 27500,
                    'image' => 'products/rendang.jpg',
                    'desc' => 'Daging sapi empuk dimasak 6 jam dengan santan murni dan rempah Minang pekat autentik.',
                    'recipe' => [
                        ['mat' => 'MAT-DGG-01', 'qty' => 0.15, 'unit' => 'kg'], // 150g daging
                        ['mat' => 'MAT-SNT-05', 'qty' => 0.20, 'unit' => 'liter'], // 200ml santan
                        ['mat' => 'MAT-BMR-06', 'qty' => 0.03, 'unit' => 'kg'],
                        ['mat' => 'MAT-BPT-07', 'qty' => 0.02, 'unit' => 'kg'],
                        ['mat' => 'MAT-CBK-08', 'qty' => 0.04, 'unit' => 'kg'],
                        ['mat' => 'MAT-RMP-10', 'qty' => 0.03, 'unit' => 'kg'],
                    ],
                ],
                [
                    'code' => 'FNB-SAT-002',
                    'name' => 'Sate Maranggi Purwakarta (10 Tusuk)',
                    'cat' => 'utama',
                    'unit' => 'porsi',
                    'price' => 38000,
                    'cost' => 21800,
                    'image' => 'products/satay.jpg',
                    'desc' => '10 tusuk sate sapi marinasi ketumbar dan gula aren, dibakar arang kelapa dengan sambal kecap tomat.',
                    'recipe' => [
                        ['mat' => 'MAT-DGG-01', 'qty' => 0.14, 'unit' => 'kg'],
                        ['mat' => 'MAT-GLA-11', 'qty' => 0.03, 'unit' => 'liter'],
                        ['mat' => 'MAT-BMR-06', 'qty' => 0.02, 'unit' => 'kg'],
                        ['mat' => 'MAT-CBR-09', 'qty' => 0.015, 'unit' => 'kg'],
                    ],
                ],
                [
                    'code' => 'FNB-AYM-003',
                    'name' => 'Ayam Bakar Madu Pedas Manis',
                    'cat' => 'utama',
                    'unit' => 'porsi',
                    'price' => 32000,
                    'cost' => 16400,
                    'image' => 'products/satay.jpg',
                    'desc' => '1/4 ayam pejantan ungkep rempah kuning, diolesi madu hutan dan cabai bakar disajikan dengan lalapan.',
                    'recipe' => [
                        ['mat' => 'MAT-AYM-02', 'qty' => 0.35, 'unit' => 'pcs'],
                        ['mat' => 'MAT-GLA-11', 'qty' => 0.02, 'unit' => 'liter'],
                        ['mat' => 'MAT-BMR-06', 'qty' => 0.02, 'unit' => 'kg'],
                        ['mat' => 'MAT-CBK-08', 'qty' => 0.02, 'unit' => 'kg'],
                    ],
                ],
                [
                    'code' => 'FNB-NAS-004',
                    'name' => 'Nasi Goreng Rempah Nusantara Spesial',
                    'cat' => 'utama',
                    'unit' => 'porsi',
                    'price' => 28000,
                    'cost' => 13500,
                    'image' => 'products/rendang.jpg',
                    'desc' => 'Nasi goreng harum rempah bawang khas Jawa, suwiran ayam gurih, telur mata sapi, acar segar dan kerupuk.',
                    'recipe' => [
                        ['mat' => 'MAT-BRS-04', 'qty' => 0.12, 'unit' => 'kg'],
                        ['mat' => 'MAT-AYM-02', 'qty' => 0.10, 'unit' => 'pcs'],
                        ['mat' => 'MAT-TLR-17', 'qty' => 0.06, 'unit' => 'kg'],
                        ['mat' => 'MAT-MYK-18', 'qty' => 0.03, 'unit' => 'liter'],
                        ['mat' => 'MAT-KRP-19', 'qty' => 0.02, 'unit' => 'kg'],
                    ],
                ],
                [
                    'code' => 'FNB-GRM-005',
                    'name' => 'Gurame Terbang Sambal Terasi Dadak',
                    'cat' => 'utama',
                    'unit' => 'porsi',
                    'price' => 58000,
                    'cost' => 32000,
                    'image' => 'products/satay.jpg',
                    'desc' => 'Ikan gurame segar 500g digoreng garing renyah berbentuk kipas, disajikan dengan sambal terasi segar.',
                    'recipe' => [
                        ['mat' => 'MAT-GRM-03', 'qty' => 0.50, 'unit' => 'kg'],
                        ['mat' => 'MAT-MYK-18', 'qty' => 0.15, 'unit' => 'liter'],
                        ['mat' => 'MAT-CBK-08', 'qty' => 0.03, 'unit' => 'kg'],
                        ['mat' => 'MAT-CBR-09', 'qty' => 0.02, 'unit' => 'kg'],
                    ],
                ],
                [
                    'code' => 'FNB-RWN-006',
                    'name' => 'Rawon Daging Sapi Kluwek Surabaya',
                    'cat' => 'utama',
                    'unit' => 'porsi',
                    'price' => 42000,
                    'cost' => 24500,
                    'image' => 'products/rendang.jpg',
                    'desc' => 'Sup daging sapi kuah hitam pekat kluwek khas Jawa Timur, tauge pendek segar, sambal dan telur asin.',
                    'recipe' => [
                        ['mat' => 'MAT-DGG-01', 'qty' => 0.13, 'unit' => 'kg'],
                        ['mat' => 'MAT-RMP-10', 'qty' => 0.04, 'unit' => 'kg'],
                        ['mat' => 'MAT-BMR-06', 'qty' => 0.02, 'unit' => 'kg'],
                        ['mat' => 'MAT-BPT-07', 'qty' => 0.02, 'unit' => 'kg'],
                    ],
                ],
                [
                    'code' => 'SNK-MDN-007',
                    'name' => 'Tempe Mendoan Gurih Sambal Kecap (4 Pcs)',
                    'cat' => 'camilan',
                    'unit' => 'porsi',
                    'price' => 16000,
                    'cost' => 6200,
                    'image' => 'products/satay.jpg',
                    'desc' => '4 lembar tempe tipis berbalut adonan tepung daun bawang, digoreng lembut dengan cocolan sambal kecap pedas.',
                    'recipe' => [
                        ['mat' => 'MAT-TMP-16', 'qty' => 2.0, 'unit' => 'pcs'],
                        ['mat' => 'MAT-MYK-18', 'qty' => 0.06, 'unit' => 'liter'],
                        ['mat' => 'MAT-CBR-09', 'qty' => 0.015, 'unit' => 'kg'],
                    ],
                ],
                [
                    'code' => 'BEV-CND-008',
                    'name' => 'Es Cendol Dawet Durian Montong',
                    'cat' => 'minuman',
                    'unit' => 'gelas',
                    'price' => 24000,
                    'cost' => 10800,
                    'image' => 'products/cendol.jpg',
                    'desc' => 'Cendol pandan kenyal, santan kelapa murni gurih, sirup gula aren kental dan topping daging durian montong harum.',
                    'recipe' => [
                        ['mat' => 'MAT-CND-14', 'qty' => 0.12, 'unit' => 'kg'],
                        ['mat' => 'MAT-SNT-05', 'qty' => 0.10, 'unit' => 'liter'],
                        ['mat' => 'MAT-GLA-11', 'qty' => 0.04, 'unit' => 'liter'],
                        ['mat' => 'MAT-DRN-15', 'qty' => 0.04, 'unit' => 'kg'],
                    ],
                ],
                [
                    'code' => 'BEV-KPI-009',
                    'name' => 'Es Kopi Susu Aren Nusantara',
                    'cat' => 'minuman',
                    'unit' => 'gelas',
                    'price' => 18000,
                    'cost' => 7200,
                    'image' => 'products/cendol.jpg',
                    'desc' => 'Double shot espresso blend Arabika Gayo & Robusta, susu segar murni dan sirup gula aren organik yang seimbang.',
                    'recipe' => [
                        ['mat' => 'MAT-KPI-12', 'qty' => 0.018, 'unit' => 'kg'],
                        ['mat' => 'MAT-SSU-13', 'qty' => 0.14, 'unit' => 'liter'],
                        ['mat' => 'MAT-GLA-11', 'qty' => 0.025, 'unit' => 'liter'],
                    ],
                ],
                [
                    'code' => 'BEV-TEH-010',
                    'name' => 'Es Teh Pandan Wangi Melati',
                    'cat' => 'minuman',
                    'unit' => 'gelas',
                    'price' => 8000,
                    'cost' => 2200,
                    'image' => 'products/cendol.jpg',
                    'desc' => 'Seduhan teh melati wangi dengan daun pandan segar rebusan alami yang menyegarkan dahaga.',
                    'recipe' => [
                        ['mat' => 'MAT-GLA-11', 'qty' => 0.02, 'unit' => 'liter'],
                    ],
                ],
                [
                    'code' => 'CAT-TPG-011',
                    'name' => 'Tumpeng Mini Nusantara Komplit',
                    'cat' => 'katering',
                    'unit' => 'box',
                    'price' => 45000,
                    'cost' => 26000,
                    'image' => 'products/tumpeng.jpg',
                    'desc' => 'Nasi kuning kerucut mini gurih, ayam lengkuas, sambal goreng kentang ati, tempe orek, telur dadar iris, perkedel.',
                    'recipe' => [
                        ['mat' => 'MAT-BRS-04', 'qty' => 0.15, 'unit' => 'kg'],
                        ['mat' => 'MAT-AYM-02', 'qty' => 0.25, 'unit' => 'pcs'],
                        ['mat' => 'MAT-SNT-05', 'qty' => 0.08, 'unit' => 'liter'],
                        ['mat' => 'MAT-KMS-20', 'qty' => 1.0, 'unit' => 'pcs'],
                    ],
                ],
            ];

            $productMap = [];
            foreach ($productCatalog as $pSpec) {
                $product = Product::updateOrCreate(
                    ['business_id' => $business->id, 'code' => $pSpec['code']],
                    [
                        'name' => $pSpec['name'],
                        'slug' => Str::slug($pSpec['name']),
                        'category_id' => $prodCats[$pSpec['cat']]->id,
                        'output_unit_id' => $units[$pSpec['unit']]->id,
                        'selling_price' => $pSpec['price'],
                        'base_cost' => $pSpec['cost'],
                        'min_stock' => 10,
                        'is_active' => true,
                        'description' => $pSpec['desc'],
                        'image_path' => $pSpec['image'],
                    ]
                );

                // Cost Model & Recipe BOM
                $costModel = CostModel::updateOrCreate(
                    ['business_id' => $business->id, 'product_id' => $product->id],
                    [
                        'name' => 'HPP ' . $product->name,
                        'slug' => 'hpp-' . Str::slug($product->name),
                        'method' => CostModel::METHOD_RECIPE_BOM,
                        'basis' => CostModel::BASIS_PLANNED,
                        'batch_size' => 1,
                        'batch_unit_id' => $units[$pSpec['unit']]->id,
                        'target_margin_pct' => 40.0,
                        'is_active' => true,
                    ]
                );

                $bomHeader = BomHeader::firstOrCreate(
                    ['cost_model_id' => $costModel->id],
                    [
                        'name' => 'Resep Utama ' . $product->name,
                        'type' => BomHeader::TYPE_RECIPE,
                        'output_quantity' => 1,
                        'output_unit_id' => $units[$pSpec['unit']]->id,
                        'yield_percentage' => 100,
                        'level' => 1,
                    ]
                );

                foreach ($pSpec['recipe'] as $rItem) {
                    if (isset($materialMap[$rItem['mat']])) {
                        $bomHeader->items()->updateOrCreate(
                            ['material_id' => $materialMap[$rItem['mat']]->id],
                            [
                                'unit_id' => $units[$rItem['unit']]->id,
                                'quantity' => $rItem['qty'],
                                'waste_percentage' => 2.0,
                            ]
                        );
                    }
                }

                $productMap[$pSpec['code']] = $product;
            }

            // ─────────────────────────────────────────────────────────────────
            // 7. F&B MODIFIERS & ADD-ONS (DENGAN KONSUMSI BAHAN BAKU)
            // ─────────────────────────────────────────────────────────────────
            // Group 1: Level Pedas
            $grpPedas = ModifierGroup::updateOrCreate(
                ['business_id' => $business->id, 'name' => 'Tingkat Kepedasan'],
                [
                    'description' => 'Pilih level cabai sesuai selera Anda',
                    'selection_type' => 'single',
                    'min_selection' => 1,
                    'max_selection' => 1,
                    'is_required' => true,
                    'sort_order' => 1,
                    'is_active' => true,
                ]
            );

            $optLevel0 = ModifierOption::updateOrCreate(['modifier_group_id' => $grpPedas->id, 'name' => 'Level 0: Tidak Pedas'], ['price_delta' => 0, 'affects_material' => false, 'sort_order' => 1, 'is_active' => true]);
            $optLevel1 = ModifierOption::updateOrCreate(['modifier_group_id' => $grpPedas->id, 'name' => 'Level 1: Sedang'], ['price_delta' => 0, 'affects_material' => false, 'sort_order' => 2, 'is_active' => true]);
            $optLevel2 = ModifierOption::updateOrCreate(['modifier_group_id' => $grpPedas->id, 'name' => 'Level 2: Pedas Gurih'], ['price_delta' => 0, 'affects_material' => false, 'sort_order' => 3, 'is_active' => true]);
            $optLevel3 = ModifierOption::updateOrCreate(['modifier_group_id' => $grpPedas->id, 'name' => 'Level 3: Pedas Nampol (Ekstra Cabai Rawit)'], ['price_delta' => 3000, 'affects_material' => true, 'sort_order' => 4, 'is_active' => true]);

            // Hubungkan bahan baku cabe rawit ke Level 3
            ModifierOptionMaterial::updateOrCreate(
                ['modifier_option_id' => $optLevel3->id, 'material_id' => $materialMap['MAT-CBR-09']->id],
                ['quantity' => 0.025, 'unit_id' => $units['kg']->id]
            );

            // Group 2: Pilihan Nasi & Karbo
            $grpNasi = ModifierGroup::updateOrCreate(
                ['business_id' => $business->id, 'name' => 'Pilihan Nasi'],
                [
                    'description' => 'Pilihan varian nasi hangat pendamping lauk',
                    'selection_type' => 'single',
                    'min_selection' => 1,
                    'max_selection' => 1,
                    'is_required' => true,
                    'sort_order' => 2,
                    'is_active' => true,
                ]
            );

            $optNasiPutih = ModifierOption::updateOrCreate(['modifier_group_id' => $grpNasi->id, 'name' => 'Nasi Putih Pandan Wangi Pulen'], ['price_delta' => 0, 'affects_material' => false, 'sort_order' => 1, 'is_active' => true]);
            $optNasiUduk = ModifierOption::updateOrCreate(['modifier_group_id' => $grpNasi->id, 'name' => 'Nasi Uduk Gurih Betawi'], ['price_delta' => 6000, 'affects_material' => true, 'sort_order' => 2, 'is_active' => true]);
            $optNasiKuning = ModifierOption::updateOrCreate(['modifier_group_id' => $grpNasi->id, 'name' => 'Nasi Kuning Wangi Daun Jeruk'], ['price_delta' => 6000, 'affects_material' => true, 'sort_order' => 3, 'is_active' => true]);

            ModifierOptionMaterial::updateOrCreate(
                ['modifier_option_id' => $optNasiUduk->id, 'material_id' => $materialMap['MAT-SNT-05']->id],
                ['quantity' => 0.05, 'unit_id' => $units['liter']->id]
            );

            // Group 3: Tambahan Topping & Sambal
            $grpTopping = ModifierGroup::updateOrCreate(
                ['business_id' => $business->id, 'name' => 'Tambahan Sambal & Topping'],
                [
                    'description' => 'Pelengkap nikmat khas Nusantara',
                    'selection_type' => 'multiple',
                    'min_selection' => 0,
                    'max_selection' => 5,
                    'is_required' => false,
                    'sort_order' => 3,
                    'is_active' => true,
                ]
            );

            $optTelur = ModifierOption::updateOrCreate(['modifier_group_id' => $grpTopping->id, 'name' => 'Telur Mata Sapi / Dadar'], ['price_delta' => 5000, 'affects_material' => true, 'sort_order' => 1, 'is_active' => true]);
            $optSambalMatah = ModifierOption::updateOrCreate(['modifier_group_id' => $grpTopping->id, 'name' => 'Sambal Matah Bali Segar'], ['price_delta' => 4000, 'affects_material' => true, 'sort_order' => 2, 'is_active' => true]);
            $optKerupuk = ModifierOption::updateOrCreate(['modifier_group_id' => $grpTopping->id, 'name' => 'Kerupuk Udang Renyah'], ['price_delta' => 4000, 'affects_material' => true, 'sort_order' => 3, 'is_active' => true]);

            ModifierOptionMaterial::updateOrCreate(
                ['modifier_option_id' => $optTelur->id, 'material_id' => $materialMap['MAT-TLR-17']->id],
                ['quantity' => 0.06, 'unit_id' => $units['kg']->id]
            );

            // Group 4: Es & Manis (Minuman)
            $grpDrink = ModifierGroup::updateOrCreate(
                ['business_id' => $business->id, 'name' => 'Preferensi Minuman'],
                [
                    'description' => 'Sesuaikan takaran es dan manis',
                    'selection_type' => 'single',
                    'min_selection' => 0,
                    'max_selection' => 1,
                    'is_required' => false,
                    'sort_order' => 4,
                    'is_active' => true,
                ]
            );

            ModifierOption::updateOrCreate(['modifier_group_id' => $grpDrink->id, 'name' => 'Normal Ice & Normal Sweet'], ['price_delta' => 0, 'affects_material' => false, 'sort_order' => 1, 'is_active' => true]);
            ModifierOption::updateOrCreate(['modifier_group_id' => $grpDrink->id, 'name' => 'Less Ice (Es Sedikit)'], ['price_delta' => 0, 'affects_material' => false, 'sort_order' => 2, 'is_active' => true]);
            ModifierOption::updateOrCreate(['modifier_group_id' => $grpDrink->id, 'name' => 'Less Sugar 50%'], ['price_delta' => 0, 'affects_material' => false, 'sort_order' => 3, 'is_active' => true]);
            $optXtraDurian = ModifierOption::updateOrCreate(['modifier_group_id' => $grpDrink->id, 'name' => 'Ekstra Durian Montong (+50g)'], ['price_delta' => 10000, 'affects_material' => true, 'sort_order' => 4, 'is_active' => true]);

            ModifierOptionMaterial::updateOrCreate(
                ['modifier_option_id' => $optXtraDurian->id, 'material_id' => $materialMap['MAT-DRN-15']->id],
                ['quantity' => 0.05, 'unit_id' => $units['kg']->id]
            );

            // Hubungkan Modifier Groups ke Produk
            $mainDishes = ['FNB-RND-001', 'FNB-SAT-002', 'FNB-AYM-003', 'FNB-NAS-004', 'FNB-GRM-005', 'FNB-RWN-006'];
            foreach ($mainDishes as $code) {
                if (isset($productMap[$code])) {
                    $productMap[$code]->modifierGroups()->syncWithoutDetaching([
                        $grpPedas->id => ['sort_order' => 1],
                        $grpNasi->id => ['sort_order' => 2],
                        $grpTopping->id => ['sort_order' => 3],
                    ]);
                }
            }

            $drinks = ['BEV-CND-008', 'BEV-KPI-009', 'BEV-TEH-010'];
            foreach ($drinks as $code) {
                if (isset($productMap[$code])) {
                    $productMap[$code]->modifierGroups()->syncWithoutDetaching([
                        $grpDrink->id => ['sort_order' => 1],
                    ]);
                }
            }

            // ─────────────────────────────────────────────────────────────────
            // 8. RESTO TABLES & QR CODE (14 MEJA LENGKAP)
            // ─────────────────────────────────────────────────────────────────
            $tableSpecs = [
                ['num' => 'Meja 01', 'name' => 'Indoor Cozy', 'cap' => 2, 'status' => PosTable::STATUS_AVAILABLE],
                ['num' => 'Meja 02', 'name' => 'Indoor Window View', 'cap' => 2, 'status' => PosTable::STATUS_AVAILABLE],
                ['num' => 'Meja 03', 'name' => 'Indoor Utama', 'cap' => 4, 'status' => PosTable::STATUS_ORDERING],
                ['num' => 'Meja 04', 'name' => 'Indoor Utama', 'cap' => 4, 'status' => PosTable::STATUS_AVAILABLE],
                ['num' => 'Meja 05', 'name' => 'Indoor Utama', 'cap' => 4, 'status' => PosTable::STATUS_AVAILABLE],
                ['num' => 'Meja 06', 'name' => 'Indoor Sofa Corner', 'cap' => 4, 'status' => PosTable::STATUS_AVAILABLE],
                ['num' => 'Meja 07', 'name' => 'Area Keluarga 1', 'cap' => 6, 'status' => PosTable::STATUS_PREPARING],
                ['num' => 'Meja 08', 'name' => 'Area Keluarga 2', 'cap' => 6, 'status' => PosTable::STATUS_AVAILABLE],
                ['num' => 'Meja 09', 'name' => 'Area Keluarga 3', 'cap' => 6, 'status' => PosTable::STATUS_AVAILABLE],
                ['num' => 'Meja 10', 'name' => 'Area Keluarga 4', 'cap' => 6, 'status' => PosTable::STATUS_AVAILABLE],
                ['num' => 'Meja VIP 1', 'name' => 'Ruang VIP Merbabu (AC & Smart TV)', 'cap' => 8, 'status' => PosTable::STATUS_SERVING],
                ['num' => 'Meja VIP 2', 'name' => 'Ruang VIP Rinjani (Meeting Room)', 'cap' => 12, 'status' => PosTable::STATUS_AVAILABLE],
                ['num' => 'Teras 01', 'name' => 'Outdoor Asri Garden', 'cap' => 4, 'status' => PosTable::STATUS_AVAILABLE],
                ['num' => 'Teras 02', 'name' => 'Outdoor Smoking Area', 'cap' => 4, 'status' => PosTable::STATUS_AVAILABLE],
            ];

            $tables = [];
            foreach ($tableSpecs as $ts) {
                $table = PosTable::updateOrCreate(
                    ['business_id' => $business->id, 'table_number' => $ts['num']],
                    [
                        'location_id' => $locOutlet->id,
                        'name' => $ts['name'],
                        'capacity' => $ts['cap'],
                        'status' => $ts['status'],
                        'qr_token' => Str::random(32),
                        'is_active' => true,
                    ]
                );
                $tables[$ts['num']] = $table;
            }

            // ─────────────────────────────────────────────────────────────────
            // 9. POS SHIFTS (ACTIVE & HISTORICAL)
            // ─────────────────────────────────────────────────────────────────
            $activeShift = PosShift::create([
                'business_id' => $business->id,
                'location_id' => $locOutlet->id,
                'user_id' => $primaryOwner->id,
                'opened_at' => Carbon::now()->startOfDay()->addHours(10),
                'opening_cash' => 500000,
                'status' => PosShift::STATUS_OPEN,
                'notes' => 'Shift Siang Kasir Utama Menteng',
            ]);

            // ─────────────────────────────────────────────────────────────────
            // 10. ACTIVE DINE-IN TABLE SESSIONS & LIVE KITCHEN ORDERS
            // ─────────────────────────────────────────────────────────────────
            // A. Meja 03 - Pesanan Baru Masuk (KDS Kolom 1: confirmed)
            $session03 = PosTableSession::create([
                'business_id' => $business->id,
                'pos_table_id' => $tables['Meja 03']->id,
                'session_number' => 'SES-' . date('Ymd') . '-03',
                'customer_name' => 'Ibu Dian Sastrowardoyo',
                'customer_phone' => '0812-9988-7766',
                'status' => PosTableSession::STATUS_ACTIVE,
                'opened_at' => Carbon::now()->subMinutes(12),
            ]);

            $orderKds1 = PosOrder::create([
                'business_id' => $business->id,
                'location_id' => $locOutlet->id,
                'pos_shift_id' => $activeShift->id,
                'user_id' => $primaryOwner->id,
                'order_number' => 'ORD-' . date('Ymd') . '-001',
                'order_date' => Carbon::now()->subMinutes(10),
                'status' => PosOrder::STATUS_CONFIRMED,
                'order_type' => PosOrder::ORDER_TYPE_DINE_IN,
                'order_source' => 'qr_customer',
                'pos_table_id' => $tables['Meja 03']->id,
                'pos_table_session_id' => $session03->id,
                'table_or_reference' => 'Meja 03',
                'customer_name_guest' => 'Ibu Dian Sastrowardoyo',
                'customer_phone_guest' => '0812-9988-7766',
                'subtotal' => 75000,
                'tax_amount' => 7500,
                'total_amount' => 82500,
                'paid_amount' => 0,
                'change_amount' => 0,
                'total_hpp_cost' => 41500,
                'total_gross_profit' => 33500,
                'notes' => 'Pesanan QR: Tolong rendangnya pilih yang empuk.',
            ]);

            $item1 = PosOrderItem::create([
                'pos_order_id' => $orderKds1->id,
                'product_id' => $productMap['FNB-RND-001']->id,
                'product_name' => $productMap['FNB-RND-001']->name,
                'product_code' => $productMap['FNB-RND-001']->code,
                'unit_price' => 45000,
                'unit_cost_hpp' => 27500,
                'quantity' => 1,
                'subtotal' => 45000,
                'total_price' => 51000,
                'total_hpp' => 29000,
                'notes' => 'Level 2, Nasi Uduk',
            ]);

            PosOrderItemModifier::create([
                'pos_order_item_id' => $item1->id,
                'modifier_group_id' => $grpPedas->id,
                'modifier_option_id' => $optLevel2->id,
                'modifier_group_name' => 'Tingkat Kepedasan',
                'modifier_option_name' => 'Level 2: Pedas Gurih',
                'unit_price' => 0,
                'quantity' => 1,
                'subtotal' => 0,
            ]);

            PosOrderItemModifier::create([
                'pos_order_item_id' => $item1->id,
                'modifier_group_id' => $grpNasi->id,
                'modifier_option_id' => $optNasiUduk->id,
                'modifier_group_name' => 'Pilihan Nasi',
                'modifier_option_name' => 'Nasi Uduk Gurih Betawi',
                'unit_price' => 6000,
                'quantity' => 1,
                'subtotal' => 6000,
            ]);

            PosOrderItem::create([
                'pos_order_id' => $orderKds1->id,
                'product_id' => $productMap['BEV-CND-008']->id,
                'product_name' => $productMap['BEV-CND-008']->name,
                'product_code' => $productMap['BEV-CND-008']->code,
                'unit_price' => 24000,
                'unit_cost_hpp' => 10800,
                'quantity' => 1,
                'subtotal' => 24000,
                'total_price' => 24000,
                'total_hpp' => 10800,
                'notes' => 'Duriannya banyakin ya',
            ]);

            // B. Meja 07 - Sedang Dimasak (KDS Kolom 2: preparing)
            $session07 = PosTableSession::create([
                'business_id' => $business->id,
                'pos_table_id' => $tables['Meja 07']->id,
                'session_number' => 'SES-' . date('Ymd') . '-07',
                'customer_name' => 'Bpk. Rahmat Hidayat (Rombongan 5 Org)',
                'customer_phone' => '0813-1122-3344',
                'status' => PosTableSession::STATUS_ACTIVE,
                'opened_at' => Carbon::now()->subMinutes(25),
            ]);

            $orderKds2 = PosOrder::create([
                'business_id' => $business->id,
                'location_id' => $locOutlet->id,
                'pos_shift_id' => $activeShift->id,
                'user_id' => $primaryOwner->id,
                'order_number' => 'ORD-' . date('Ymd') . '-002',
                'order_date' => Carbon::now()->subMinutes(20),
                'status' => PosOrder::STATUS_PREPARING,
                'order_type' => PosOrder::ORDER_TYPE_DINE_IN,
                'order_source' => 'pos_waiter',
                'pos_table_id' => $tables['Meja 07']->id,
                'pos_table_session_id' => $session07->id,
                'table_or_reference' => 'Meja 07',
                'customer_name_guest' => 'Bpk. Rahmat Hidayat',
                'customer_phone_guest' => '0813-1122-3344',
                'subtotal' => 162000,
                'tax_amount' => 16200,
                'total_amount' => 178200,
                'paid_amount' => 0,
                'change_amount' => 0,
                'total_hpp_cost' => 84800,
                'total_gross_profit' => 77200,
                'notes' => 'Gurame digoreng kering garing, sambal pisah.',
            ]);

            PosOrderItem::create([
                'pos_order_id' => $orderKds2->id,
                'product_id' => $productMap['FNB-GRM-005']->id,
                'product_name' => $productMap['FNB-GRM-005']->name,
                'product_code' => $productMap['FNB-GRM-005']->code,
                'unit_price' => 58000,
                'unit_cost_hpp' => 32000,
                'quantity' => 1,
                'subtotal' => 58000,
                'total_price' => 58000,
                'total_hpp' => 32000,
                'notes' => 'Garing banget',
            ]);

            PosOrderItem::create([
                'pos_order_id' => $orderKds2->id,
                'product_id' => $productMap['FNB-AYM-003']->id,
                'product_name' => $productMap['FNB-AYM-003']->name,
                'product_code' => $productMap['FNB-AYM-003']->code,
                'unit_price' => 32000,
                'unit_cost_hpp' => 16400,
                'quantity' => 2,
                'subtotal' => 64000,
                'total_price' => 64000,
                'total_hpp' => 32800,
                'notes' => 'Pedas manis madu',
            ]);

            PosOrderItem::create([
                'pos_order_id' => $orderKds2->id,
                'product_id' => $productMap['SNK-MDN-007']->id,
                'product_name' => $productMap['SNK-MDN-007']->name,
                'product_code' => $productMap['SNK-MDN-007']->code,
                'unit_price' => 16000,
                'unit_cost_hpp' => 6200,
                'quantity' => 1,
                'subtotal' => 16000,
                'total_price' => 16000,
                'total_hpp' => 6200,
            ]);

            PosOrderItem::create([
                'pos_order_id' => $orderKds2->id,
                'product_id' => $productMap['BEV-TEH-010']->id,
                'product_name' => $productMap['BEV-TEH-010']->name,
                'product_code' => $productMap['BEV-TEH-010']->code,
                'unit_price' => 8000,
                'unit_cost_hpp' => 2200,
                'quantity' => 3,
                'subtotal' => 24000,
                'total_price' => 24000,
                'total_hpp' => 6600,
                'notes' => 'Es batu dipisah',
            ]);

            // C. Meja VIP 1 - Siap Disajikan (KDS Kolom 3: ready)
            $sessionVip1 = PosTableSession::create([
                'business_id' => $business->id,
                'pos_table_id' => $tables['Meja VIP 1']->id,
                'session_number' => 'SES-' . date('Ymd') . '-VIP1',
                'customer_name' => 'PT Telkom Indonesia (Meeting Luncheon)',
                'customer_phone' => '0811-2233-4455',
                'status' => PosTableSession::STATUS_ACTIVE,
                'opened_at' => Carbon::now()->subMinutes(40),
            ]);

            $orderKds3 = PosOrder::create([
                'business_id' => $business->id,
                'location_id' => $locOutlet->id,
                'pos_shift_id' => $activeShift->id,
                'user_id' => $primaryOwner->id,
                'order_number' => 'ORD-' . date('Ymd') . '-003',
                'order_date' => Carbon::now()->subMinutes(35),
                'status' => PosOrder::STATUS_READY,
                'order_type' => PosOrder::ORDER_TYPE_DINE_IN,
                'order_source' => 'pos_waiter',
                'pos_table_id' => $tables['Meja VIP 1']->id,
                'pos_table_session_id' => $sessionVip1->id,
                'table_or_reference' => 'Meja VIP 1',
                'customer_name_guest' => 'PT Telkom Indonesia',
                'customer_phone_guest' => '0811-2233-4455',
                'subtotal' => 224000,
                'tax_amount' => 22400,
                'total_amount' => 246400,
                'paid_amount' => 0,
                'change_amount' => 0,
                'total_hpp_cost' => 116000,
                'total_gross_profit' => 108000,
                'notes' => 'VIP: Sajikan bersamaan dengan kopi susu aren.',
            ]);

            PosOrderItem::create([
                'pos_order_id' => $orderKds3->id,
                'product_id' => $productMap['FNB-SAT-002']->id,
                'product_name' => $productMap['FNB-SAT-002']->name,
                'product_code' => $productMap['FNB-SAT-002']->code,
                'unit_price' => 38000,
                'unit_cost_hpp' => 21800,
                'quantity' => 4,
                'subtotal' => 152000,
                'total_price' => 152000,
                'total_hpp' => 87200,
                'notes' => 'Daging empuk, bumbu manis gurih',
            ]);

            PosOrderItem::create([
                'pos_order_id' => $orderKds3->id,
                'product_id' => $productMap['BEV-KPI-009']->id,
                'product_name' => $productMap['BEV-KPI-009']->name,
                'product_code' => $productMap['BEV-KPI-009']->code,
                'unit_price' => 18000,
                'unit_cost_hpp' => 7200,
                'quantity' => 4,
                'subtotal' => 72000,
                'total_price' => 72000,
                'total_hpp' => 28800,
                'notes' => 'Less sugar semua',
            ]);

            // ─────────────────────────────────────────────────────────────────
            // 11. COMPLETED HISTORICAL POS TRANSACTIONS (UNTUK DASHBOARD & LAPORAN)
            // ─────────────────────────────────────────────────────────────────
            $customers = [
                Customer::updateOrCreate(
                    ['business_id' => $business->id, 'phone' => '0812-1111-2222'],
                    ['name' => 'Bpk. Ir. Hendra Gunawan', 'slug' => 'hendra-gunawan', 'email' => 'hendra.gunawan@telkom.id', 'billing_address' => 'Menteng Regency No. 4A']
                ),
                Customer::updateOrCreate(
                    ['business_id' => $business->id, 'phone' => '0813-3333-4444'],
                    ['name' => 'Ibu Ratna Sarumpaet', 'slug' => 'ratna-sarumpaet', 'email' => 'ratna.sarumpaet@gmail.com', 'billing_address' => 'Jl. Cikini Raya No. 12']
                ),
                Customer::updateOrCreate(
                    ['business_id' => $business->id, 'phone' => '0818-5555-6666'],
                    ['name' => 'Clarissa Anggraini', 'slug' => 'clarissa-anggraini', 'email' => 'clarissa@agency.co.id', 'billing_address' => 'Apartemen Menteng Park']
                ),
            ];

            $completedSales = [
                ['order_num' => 'ORD-' . date('Ymd') . '-010', 'hours_ago' => 2, 'cust' => 0, 'method' => 'qris', 'subtotal' => 138000, 'items' => [
                    ['code' => 'FNB-RND-001', 'qty' => 2, 'price' => 45000, 'cost' => 27500],
                    ['code' => 'BEV-CND-008', 'qty' => 2, 'price' => 24000, 'cost' => 10800],
                ]],
                ['order_num' => 'ORD-' . date('Ymd') . '-011', 'hours_ago' => 3, 'cust' => 1, 'method' => 'cash', 'subtotal' => 86000, 'items' => [
                    ['code' => 'FNB-RWN-006', 'qty' => 1, 'price' => 42000, 'cost' => 24500],
                    ['code' => 'SNK-MDN-007', 'qty' => 1, 'price' => 16000, 'cost' => 6200],
                    ['code' => 'BEV-NAS-004', 'qty' => 1, 'price' => 28000, 'cost' => 13500],
                ]],
                ['order_num' => 'ORD-' . date('Ymd') . '-012', 'hours_ago' => 4, 'cust' => 2, 'method' => 'bank_transfer', 'subtotal' => 190000, 'items' => [
                    ['code' => 'FNB-SAT-002', 'qty' => 3, 'price' => 38000, 'cost' => 21800],
                    ['code' => 'FNB-AYM-003', 'qty' => 2, 'price' => 32000, 'cost' => 16400],
                    ['code' => 'BEV-KPI-009', 'qty' => 2, 'price' => 18000, 'cost' => 7200],
                ]],
                ['order_num' => 'ORD-' . date('Ymd', strtotime('-1 day')) . '-020', 'hours_ago' => 26, 'cust' => 0, 'method' => 'qris', 'subtotal' => 270000, 'items' => [
                    ['code' => 'CAT-TPG-011', 'qty' => 6, 'price' => 45000, 'cost' => 26000],
                ]],
                ['order_num' => 'ORD-' . date('Ymd', strtotime('-2 days')) . '-030', 'hours_ago' => 50, 'cust' => 1, 'method' => 'cash', 'subtotal' => 116000, 'items' => [
                    ['code' => 'FNB-GRM-005', 'qty' => 2, 'price' => 58000, 'cost' => 32000],
                ]],
            ];

            foreach ($completedSales as $cs) {
                $subtotal = $cs['subtotal'];
                $tax = round($subtotal * 0.1);
                $total = $subtotal + $tax;
                $totCost = 0;

                $ord = PosOrder::create([
                    'business_id' => $business->id,
                    'location_id' => $locOutlet->id,
                    'pos_shift_id' => $activeShift->id,
                    'user_id' => $primaryOwner->id,
                    'customer_id' => $customers[$cs['cust']]->id,
                    'order_number' => $cs['order_num'],
                    'order_date' => Carbon::now()->subHours($cs['hours_ago']),
                    'status' => PosOrder::STATUS_COMPLETED,
                    'order_type' => PosOrder::ORDER_TYPE_DINE_IN,
                    'order_source' => 'pos_terminal',
                    'table_or_reference' => 'Kasir Utama',
                    'subtotal' => $subtotal,
                    'tax_amount' => $tax,
                    'total_amount' => $total,
                    'paid_amount' => $total,
                    'change_amount' => 0,
                    'total_hpp_cost' => 0,
                    'total_gross_profit' => 0,
                ]);

                foreach ($cs['items'] as $it) {
                    $prod = $productMap[$it['code']] ?? null;
                    if ($prod) {
                        $lineSub = $it['qty'] * $it['price'];
                        $lineCost = $it['qty'] * $it['cost'];
                        $totCost += $lineCost;

                        PosOrderItem::create([
                            'pos_order_id' => $ord->id,
                            'product_id' => $prod->id,
                            'product_name' => $prod->name,
                            'product_code' => $prod->code,
                            'unit_price' => $it['price'],
                            'unit_cost_hpp' => $it['cost'],
                            'quantity' => $it['qty'],
                            'subtotal' => $lineSub,
                            'total_price' => $lineSub,
                            'total_hpp' => $lineCost,
                        ]);
                    }
                }

                $ord->update([
                    'total_hpp_cost' => $totCost,
                    'total_gross_profit' => max(0, $subtotal - $totCost),
                ]);

                PosOrderPayment::create([
                    'pos_order_id' => $ord->id,
                    'payment_method' => $cs['method'],
                    'amount' => $total,
                    'net_amount' => $total,
                    'status' => PosOrderPayment::STATUS_COMPLETED,
                    'reference_number' => 'REF-' . strtoupper(Str::random(8)),
                ]);
            }

            // ─────────────────────────────────────────────────────────────────
            // 12. OPERATIONAL EXPENSES
            // ─────────────────────────────────────────────────────────────────
            $expenseList = [
                ['num' => 'EXP-2026-001', 'name' => 'Tagihan Listrik PLN & Air Restoran', 'cat' => 'Listrik, Air & Gas', 'amount' => 2450000, 'method' => 'bank_transfer', 'days_ago' => 2],
                ['num' => 'EXP-2026-002', 'name' => 'Isi Ulang Gas Elpiji 12kg (4 Tabung)', 'cat' => 'Listrik, Air & Gas', 'amount' => 880000, 'method' => 'cash', 'days_ago' => 1],
                ['num' => 'EXP-2026-003', 'name' => 'Es Batu Kristal Higienis & Galon Air', 'cat' => 'Operasional Toko', 'amount' => 320000, 'method' => 'cash', 'days_ago' => 0],
                ['num' => 'EXP-2026-004', 'name' => 'Perlengkapan Sanitasi & Sabun Cuci Food Grade', 'cat' => 'Bahan Habis Pakai', 'amount' => 185000, 'method' => 'cash', 'days_ago' => 3],
            ];

            foreach ($expenseList as $ex) {
                Expense::updateOrCreate(
                    ['business_id' => $business->id, 'expense_number' => $ex['num']],
                    [
                        'location_id' => $locOutlet->id,
                        'expense_date' => Carbon::now()->subDays($ex['days_ago'])->toDateString(),
                        'category' => $ex['cat'],
                        'amount' => $ex['amount'],
                        'payment_method' => $ex['method'],
                        'description' => $ex['name'],
                        'recorded_by' => $primaryOwner->id,
                    ]
                );
            }
        });
    }
}
