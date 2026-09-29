<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\BomHeader;
use App\Models\BranchProductPrice;
use App\Models\Business;
use App\Models\BusinessLandingPage;
use App\Models\BusinessMembership;
use App\Models\CommercePaymentMethod;
use App\Models\CommerceShippingRule;
use App\Models\CommerceStoreSetting;
use App\Models\CostModel;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\InventoryStock;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Location;
use App\Models\MarketplaceAccount;
use App\Models\MarketplaceOrder;
use App\Models\MarketplaceProductMapping;
use App\Models\MarketplaceSyncLog;
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
use App\Models\PosRegister;
use App\Models\PosShift;
use App\Models\PosTable;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductChannelPrice;
use App\Models\Role;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class CafeKopiSenjaFullSeeder extends Seeder
{
    /**
     * Run the database seeds for Phase 5 (Coffee Shop & Cafe: fnb_cafe / artisan_brew).
     */
    public function run(): void
    {
        DB::transaction(function () {
            // ─────────────────────────────────────────────────────────────────
            // 1. BUSINESS PROFILE & TAX SETTINGS
            // ─────────────────────────────────────────────────────────────────
            $business = Business::where('slug', 'kopi-senja-utama')->first();

            $businessAttributes = [
                'name' => 'Kopi Senja Utama (Coffee Shop)',
                'slug' => 'kopi-senja-utama',
                'currency' => 'IDR',
                'currency_precision' => 0,
                'phone' => '0812-1111-0002',
                'email' => 'kontak@kopisenjautama.cooca.id',
                'address' => 'Jl. Ranggamalela No. 9, Dago, Bandung 40116',
                'tax_identification_number' => '01.892.443.2-429.000', // NPWP Resmi PT Senja Kopi Nusantara
                'pos_enable_tax' => true,
                'pos_tax_percent' => 10.0, // Pajak Daerah / PB1 10%
                'pos_enable_service_charge' => true,
                'pos_service_charge_percent' => 5.0, // Service Charge Cafe 5%
                'rounding_strategy' => Business::ROUNDING_ROUND_100,
                'pos_show_product_images' => true,
                'allow_negative_stock' => false,
            ];

            if (! $business) {
                $business = Business::create(array_merge([
                    'id' => (string) Str::uuid(),
                ], $businessAttributes));
            } else {
                $business->update($businessAttributes);
            }

            // ─────────────────────────────────────────────────────────────────
            // 2. TESTING ACCOUNTS (ALL ROLES: OWNER, MANAGER, CASHIER, BARISTA, FINANCE, CUSTOMER)
            // ─────────────────────────────────────────────────────────────────
            $defaultPassword = Hash::make('password123');

            $testUsers = [
                [
                    'email' => 'owner.cafe@cooca.id',
                    'name' => 'Fajar Nugraha',
                    'phone' => '0812-1111-0002',
                    'role' => 'owner',
                    'is_primary' => true,
                ],
                [
                    'email' => 'manager.cafe@cooca.id',
                    'name' => 'Rian Pratama',
                    'phone' => '0812-2233-4451',
                    'role' => 'manager',
                    'is_primary' => false,
                ],
                [
                    'email' => 'kasir.cafe@cooca.id',
                    'name' => 'Siti Rahma',
                    'phone' => '0812-3344-5562',
                    'role' => 'cashier',
                    'is_primary' => false,
                ],
                [
                    'email' => 'barista.cafe@cooca.id',
                    'name' => 'Aris Setiawan',
                    'phone' => '0812-4455-6673',
                    'role' => 'warehouse_staff',
                    'is_primary' => false,
                ],
                [
                    'email' => 'akuntan.cafe@cooca.id',
                    'name' => 'Nadia Utami',
                    'phone' => '0812-5566-7784',
                    'role' => 'accountant',
                    'is_primary' => false,
                ],
                [
                    'email' => 'customer.cafe@cooca.id',
                    'name' => 'Dimas Anggara',
                    'phone' => '0812-7788-9901',
                    'role' => 'staff',
                    'is_primary' => false,
                ],
            ];

            $createdUsers = [];
            foreach ($testUsers as $uData) {
                $user = User::updateOrCreate(
                    ['email' => $uData['email']],
                    [
                        'name' => $uData['name'],
                        'password' => $defaultPassword,
                        'phone' => $uData['phone'] ?? null,
                        'phone_verified_at' => now(),
                        'email_verified_at' => now(),
                        'active_business_id' => $business->id,
                    ]
                );

                // Assign membership in Kopi Senja Utama
                BusinessMembership::updateOrCreate(
                    ['business_id' => $business->id, 'user_id' => $user->id],
                    [
                        'role' => $uData['role'],
                    ]
                );

                $createdUsers[$uData['email']] = $user;
            }

            // Juga link akun demo & testing global agar bisa login langsung ke bisnis cafe ini
            $globalUsers = User::whereIn('email', ['demo@cooca.id', 'testing@cooca.id'])->get();
            foreach ($globalUsers as $gu) {
                BusinessMembership::firstOrCreate(
                    ['business_id' => $business->id, 'user_id' => $gu->id],
                    ['id' => (string) Str::uuid(), 'role' => 'owner']
                );
            }

            $primaryOwner = $createdUsers['owner.cafe@cooca.id'];
            $cashierUser = $createdUsers['kasir.cafe@cooca.id'];

            // ─────────────────────────────────────────────────────────────────
            // 3. MULTI-BRANCH LOCATIONS (PRIMARY OUTLET, EXPRESS BAR, CENTRAL WAREHOUSE)
            // ─────────────────────────────────────────────────────────────────
            $locDago = Location::updateOrCreate(
                ['business_id' => $business->id, 'code' => 'LOC-DAGO'],
                [
                    'name' => 'Coffee Bar & Roastery Dago',
                    'slug' => 'coffee-bar-dago',
                    'type' => 'outlet',
                    'address' => 'Jl. Ranggamalela No. 9, Dago, Bandung',
                    'phone' => '0812-1111-0002',
                    'is_primary' => true,
                    'is_active' => true,
                ]
            );

            $locBraga = Location::updateOrCreate(
                ['business_id' => $business->id, 'code' => 'LOC-BRG'],
                [
                    'name' => 'Express Coffee Bar Braga',
                    'slug' => 'express-coffee-bar-braga',
                    'type' => 'outlet',
                    'address' => 'Jl. Braga No. 45, Braga, Bandung',
                    'phone' => '0812-1111-0003',
                    'is_primary' => false,
                    'is_active' => true,
                ]
            );

            $locWarehouse = Location::updateOrCreate(
                ['business_id' => $business->id, 'code' => 'LOC-WH'],
                [
                    'name' => 'Central Roastery & Warehouse',
                    'slug' => 'central-roastery-warehouse',
                    'type' => 'warehouse',
                    'address' => 'Jl. Soekarno-Hatta No. 120, Bandung',
                    'phone' => '0812-1111-0004',
                    'is_primary' => false,
                    'is_active' => true,
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // 4. UNITS & RAW MATERIALS SUPPLIERS
            // ─────────────────────────────────────────────────────────────────
            $uKg = Unit::where('code', 'kg')->first();
            $uG = Unit::where('code', 'g')->first();
            $uL = Unit::where('code', 'l')->first();
            $uMl = Unit::where('code', 'ml')->first();
            $uPcs = Unit::where('code', 'pcs')->first();
            $uCup = Unit::where('code', 'cup')->first() ?? $uPcs;
            $uBox = Unit::where('code', 'box')->first() ?? $uPcs;

            $units = [
                'kg' => $uKg,
                'g' => $uG,
                'l' => $uL,
                'liter' => $uL,
                'ml' => $uMl,
                'pcs' => $uPcs,
                'cup' => $uCup,
                'box' => $uBox,
            ];

            $suppliers = [
                'gayo' => Supplier::updateOrCreate(
                    ['business_id' => $business->id, 'slug' => 'cv-gayo-mountain-coffee'],
                    [
                        'name' => 'CV Gayo Mountain Specialty Coffee',
                        'contact_person' => 'Teuku Fauzan',
                        'phone' => '0813-9900-1122',
                        'email' => 'order@gayomountain.co.id',
                        'address' => 'Takengon, Aceh Tengah',
                    ]
                ),
                'toraja' => Supplier::updateOrCreate(
                    ['business_id' => $business->id, 'slug' => 'pt-toraja-highland-roasters'],
                    [
                        'name' => 'PT Toraja Highland Roasters',
                        'contact_person' => 'Bongga Pasolang',
                        'phone' => '0811-4455-8899',
                        'email' => 'sales@torajahighland.com',
                        'address' => 'Makale, Tana Toraja, Sulawesi Selatan',
                    ]
                ),
                'dairy' => Supplier::updateOrCreate(
                    ['business_id' => $business->id, 'slug' => 'pt-greenfields-dairy-bandung'],
                    [
                        'name' => 'Distributor Greenfields & Plant Milk Bandung',
                        'contact_person' => 'Bambang Sudiro',
                        'phone' => '0812-7788-2233',
                        'email' => 'supply@dairybandung.co.id',
                        'address' => 'Kawasan Pergudangan Batununggal Blok B-4, Bandung',
                    ]
                ),
                'packaging' => Supplier::updateOrCreate(
                    ['business_id' => $business->id, 'slug' => 'cv-java-eco-cup-packaging'],
                    [
                        'name' => 'CV Java Eco Cup & Packaging',
                        'contact_person' => 'Iwan Darmawan',
                        'phone' => '0813-2211-7788',
                        'email' => 'info@javacup.co.id',
                        'address' => 'Jl. Industri Cimahi No. 18, Cimahi',
                    ]
                ),
            ];

            // ─────────────────────────────────────────────────────────────────
            // 5. MATERIAL CATEGORIES & RAW MATERIALS (HPP / BOM)
            // ─────────────────────────────────────────────────────────────────
            $matCats = [
                'beans' => MaterialCategory::firstOrCreate(['business_id' => $business->id, 'slug' => 'biji-kopi-green-roasted'], ['name' => 'Biji Kopi (Green & Roasted)']),
                'dairy' => MaterialCategory::firstOrCreate(['business_id' => $business->id, 'slug' => 'susu-syrup-beverage'], ['name' => 'Susu, Sirup & Bubuk']),
                'packaging' => MaterialCategory::firstOrCreate(['business_id' => $business->id, 'slug' => 'packaging-kemasan-kopi'], ['name' => 'Kemasan, Cup & Botol']),
                'pastry' => MaterialCategory::firstOrCreate(['business_id' => $business->id, 'slug' => 'bahan-pastry-bites'], ['name' => 'Bahan Baku Pastry']),
            ];

            $materialSpecs = [
                ['code' => 'CFE-MAT-BEANS-GAYO', 'name' => 'Biji Kopi Arabika Gayo Fullwash', 'cat' => 'beans', 'unit' => 'g', 'price' => 190.0, 'sup' => 'gayo', 'stock' => 12000.0],
                ['code' => 'CFE-MAT-BEANS-TORAJA', 'name' => 'Biji Kopi Arabika Toraja Sapan', 'cat' => 'beans', 'unit' => 'g', 'price' => 210.0, 'sup' => 'toraja', 'stock' => 10000.0],
                ['code' => 'CFE-MAT-BEANS-ROBUSTA', 'name' => 'Biji Kopi Robusta Temanggung Fine', 'cat' => 'beans', 'unit' => 'g', 'price' => 95.0, 'sup' => 'gayo', 'stock' => 8000.0],
                ['code' => 'CFE-MAT-MILK-FRESH', 'name' => 'Susu Segar Pasteurisasi Greenfields', 'cat' => 'dairy', 'unit' => 'ml', 'price' => 22.0, 'sup' => 'dairy', 'stock' => 35000.0],
                ['code' => 'CFE-MAT-MILK-OAT', 'name' => 'Oatly Barista Edition Oat Milk', 'cat' => 'dairy', 'unit' => 'ml', 'price' => 45.0, 'sup' => 'dairy', 'stock' => 18000.0],
                ['code' => 'CFE-MAT-SYRUP-AREN', 'name' => 'Sirup Gula Aren Murni Organik', 'cat' => 'dairy', 'unit' => 'ml', 'price' => 32.0, 'sup' => 'dairy', 'stock' => 15000.0],
                ['code' => 'CFE-MAT-MATCHA', 'name' => 'Bubuk Uji Matcha Ceremonial Grade', 'cat' => 'dairy', 'unit' => 'g', 'price' => 450.0, 'sup' => 'dairy', 'stock' => 2000.0],
                ['code' => 'CFE-MAT-BUTTER', 'name' => 'Mentega French Butter Elle & Vire', 'cat' => 'pastry', 'unit' => 'kg', 'price' => 210000.0, 'sup' => 'dairy', 'stock' => 25.0],
                ['code' => 'CFE-MAT-CUP-16OZ', 'name' => 'Cold Cup 16oz Sablon Kopi Senja + Straw Lid', 'cat' => 'packaging', 'unit' => 'pcs', 'price' => 950.0, 'sup' => 'packaging', 'stock' => 2500.0],
                ['code' => 'CFE-MAT-CUP-8OZ', 'name' => 'Hot Paper Cup 8oz Double Wall + Sipper Lid', 'cat' => 'packaging', 'unit' => 'pcs', 'price' => 850.0, 'sup' => 'packaging', 'stock' => 1800.0],
                ['code' => 'CFE-MAT-BOTTLE-1L', 'name' => 'Botol Kaca Kopi Senja 1 Liter + Segel Induksi', 'cat' => 'packaging', 'unit' => 'pcs', 'price' => 4500.0, 'sup' => 'packaging', 'stock' => 400.0],
                ['code' => 'CFE-MAT-POUCH-250G', 'name' => 'Pouch Biji Kopi 250g Valve & Zipper Premium', 'cat' => 'packaging', 'unit' => 'pcs', 'price' => 3200.0, 'sup' => 'packaging', 'stock' => 600.0],
            ];

            $materialMap = [];
            foreach ($materialSpecs as $ms) {
                $material = Material::updateOrCreate(
                    ['business_id' => $business->id, 'code' => $ms['code']],
                    [
                        'name' => $ms['name'],
                        'slug' => Str::slug($ms['name']),
                        'category_id' => $matCats[$ms['cat']]->id,
                        'unit_id' => $units[$ms['unit']]->id,
                        'supplier_id' => $suppliers[$ms['sup']]->id,
                        'description' => "Bahan baku {$ms['name']}",
                    ]
                );

                $material->prices()->updateOrCreate(
                    ['effective_date' => now()->toDateString()],
                    [
                        'business_id' => $business->id,
                        'supplier_id' => $suppliers[$ms['sup']]->id,
                        'purchase_unit_id' => $units[$ms['unit']]->id,
                        'purchase_price' => $ms['price'],
                        'yield_percentage' => 100.0,
                        'waste_percentage' => 1.5,
                    ]
                );

                // Stok di Outlet Dago
                InventoryStock::updateOrCreate(
                    [
                        'business_id' => $business->id,
                        'location_id' => $locDago->id,
                        'material_id' => $material->id,
                    ],
                    [
                        'quantity' => $ms['stock'] * 0.4,
                    ]
                );

                // Stok di Central Warehouse
                InventoryStock::updateOrCreate(
                    [
                        'business_id' => $business->id,
                        'location_id' => $locWarehouse->id,
                        'material_id' => $material->id,
                    ],
                    [
                        'quantity' => $ms['stock'] * 0.6,
                    ]
                );

                $materialMap[$ms['code']] = $material;
            }

            // ─────────────────────────────────────────────────────────────────
            // 6. PRODUCT CATEGORIES
            // ─────────────────────────────────────────────────────────────────
            $prodCats = [
                'espresso' => ProductCategory::firstOrCreate(
                    ['business_id' => $business->id, 'slug' => 'coffee-espresso'],
                    ['name' => 'Signature Coffee & Espresso']
                ),
                'manual_brew' => ProductCategory::firstOrCreate(
                    ['business_id' => $business->id, 'slug' => 'manual-brew-single-origin'],
                    ['name' => 'Manual Brew & Single Origin']
                ),
                'non_coffee' => ProductCategory::firstOrCreate(
                    ['business_id' => $business->id, 'slug' => 'non-coffee-tea'],
                    ['name' => 'Non-Coffee & Artisan Tea']
                ),
                'pastry' => ProductCategory::firstOrCreate(
                    ['business_id' => $business->id, 'slug' => 'artisan-pastry-bites'],
                    ['name' => 'Artisan Pastry & Bites']
                ),
                'packaged_beans' => ProductCategory::firstOrCreate(
                    ['business_id' => $business->id, 'slug' => 'biji-kopi-kemasan'],
                    ['name' => 'Biji Kopi Kemasan (Retail & B2B)']
                ),
                'rtd' => ProductCategory::firstOrCreate(
                    ['business_id' => $business->id, 'slug' => 'ready-to-drink-bottle'],
                    ['name' => 'Ready-To-Drink (1 Liter Bottle)']
                ),
                'merch' => ProductCategory::firstOrCreate(
                    ['business_id' => $business->id, 'slug' => 'merchandise-gear'],
                    ['name' => 'Merchandise & Alat Seduh']
                ),
            ];

            // ─────────────────────────────────────────────────────────────────
            // 7. COMPLETE PRODUCT CATALOG WITH MULTI-PRICE (POS CHANNELS, BRANCH & MP)
            // ─────────────────────────────────────────────────────────────────
            $productCatalog = [
                [
                    'code' => 'CFE-KOPI-AREN',
                    'name' => 'Es Kopi Susu Gula Aren Senja',
                    'cat' => 'espresso',
                    'unit' => 'cup',
                    'base_price' => 22000.0,
                    'cost' => 6850.0,
                    'desc' => 'Signature iced coffee Kopi Senja dengan espresso gayo kental, susu segar pasteurisasi, dan sirup gula aren asli organik.',
                    'channels' => [
                        ProductChannelPrice::CHANNEL_DINE_IN => 22000.0,
                        ProductChannelPrice::CHANNEL_TAKEAWAY => 23000.0,
                        ProductChannelPrice::CHANNEL_GOFOOD => 28000.0,
                        ProductChannelPrice::CHANNEL_GRABFOOD => 28000.0,
                        ProductChannelPrice::CHANNEL_SHOPEEFOOD => 27500.0,
                    ],
                    'branch_prices' => [
                        'LOC-BRG' => 25000.0, // Harga di outlet Braga lebih tinggi
                    ],
                    'recipe' => [
                        ['mat' => 'CFE-MAT-BEANS-GAYO', 'qty' => 18.0, 'unit' => 'g'],
                        ['mat' => 'CFE-MAT-MILK-FRESH', 'qty' => 120.0, 'unit' => 'ml'],
                        ['mat' => 'CFE-MAT-SYRUP-AREN', 'qty' => 25.0, 'unit' => 'ml'],
                        ['mat' => 'CFE-MAT-CUP-16OZ', 'qty' => 1.0, 'unit' => 'pcs'],
                    ],
                ],
                [
                    'code' => 'CFE-LATTE-HOT',
                    'name' => 'Hot Caffe Latte Single Origin',
                    'cat' => 'espresso',
                    'unit' => 'cup',
                    'base_price' => 28000.0,
                    'cost' => 7500.0,
                    'desc' => 'Espresso gayo double shot dengan micro-foam susu hangat yang lembut dan latte art indah di cangkir keramik.',
                    'channels' => [
                        ProductChannelPrice::CHANNEL_DINE_IN => 28000.0,
                        ProductChannelPrice::CHANNEL_TAKEAWAY => 29000.0,
                        ProductChannelPrice::CHANNEL_GOFOOD => 35000.0,
                        ProductChannelPrice::CHANNEL_GRABFOOD => 35000.0,
                        ProductChannelPrice::CHANNEL_SHOPEEFOOD => 34000.0,
                    ],
                    'branch_prices' => [
                        'LOC-BRG' => 32000.0,
                    ],
                    'recipe' => [
                        ['mat' => 'CFE-MAT-BEANS-GAYO', 'qty' => 20.0, 'unit' => 'g'],
                        ['mat' => 'CFE-MAT-MILK-FRESH', 'qty' => 180.0, 'unit' => 'ml'],
                        ['mat' => 'CFE-MAT-CUP-8OZ', 'qty' => 1.0, 'unit' => 'pcs'],
                    ],
                ],
                [
                    'code' => 'CFE-V60-GAYO',
                    'name' => 'Manual Brew V60 Arabika Gayo Natural',
                    'cat' => 'manual_brew',
                    'unit' => 'cup',
                    'base_price' => 32000.0,
                    'cost' => 4500.0,
                    'desc' => 'Seduhan manual filter V60 dengan biji kopi Gayo Natural process, aroma floral melati dan rasa asam buah persik segar.',
                    'channels' => [
                        ProductChannelPrice::CHANNEL_DINE_IN => 32000.0,
                        ProductChannelPrice::CHANNEL_TAKEAWAY => 34000.0,
                        ProductChannelPrice::CHANNEL_GOFOOD => 40000.0,
                        ProductChannelPrice::CHANNEL_GRABFOOD => 40000.0,
                        ProductChannelPrice::CHANNEL_SHOPEEFOOD => 39000.0,
                    ],
                    'branch_prices' => [
                        'LOC-BRG' => 36000.0,
                    ],
                    'recipe' => [
                        ['mat' => 'CFE-MAT-BEANS-GAYO', 'qty' => 15.0, 'unit' => 'g'],
                        ['mat' => 'CFE-MAT-CUP-8OZ', 'qty' => 1.0, 'unit' => 'pcs'],
                    ],
                ],
                [
                    'code' => 'CFE-MATCHA-OAT',
                    'name' => 'Artisan Uji Matcha Oat Latte',
                    'cat' => 'non_coffee',
                    'unit' => 'cup',
                    'base_price' => 34000.0,
                    'cost' => 9800.0,
                    'desc' => 'Bubuk matcha murni asal Uji, Kyoto yang dikocok sempurna dengan susu gandum Oatly Barista Edition, lembut dan creamy.',
                    'channels' => [
                        ProductChannelPrice::CHANNEL_DINE_IN => 34000.0,
                        ProductChannelPrice::CHANNEL_TAKEAWAY => 35000.0,
                        ProductChannelPrice::CHANNEL_GOFOOD => 42000.0,
                        ProductChannelPrice::CHANNEL_GRABFOOD => 42000.0,
                        ProductChannelPrice::CHANNEL_SHOPEEFOOD => 41000.0,
                    ],
                    'branch_prices' => [
                        'LOC-BRG' => 38000.0,
                    ],
                    'recipe' => [
                        ['mat' => 'CFE-MAT-MATCHA', 'qty' => 6.0, 'unit' => 'g'],
                        ['mat' => 'CFE-MAT-MILK-OAT', 'qty' => 150.0, 'unit' => 'ml'],
                        ['mat' => 'CFE-MAT-CUP-16OZ', 'qty' => 1.0, 'unit' => 'pcs'],
                    ],
                ],
                [
                    'code' => 'CFE-COLD-BREW',
                    'name' => 'Nitro Cold Brew Black Coffee',
                    'cat' => 'espresso',
                    'unit' => 'cup',
                    'base_price' => 30000.0,
                    'cost' => 5500.0,
                    'desc' => 'Kopi seduh dingin selama 18 jam dengan tekstur creamy berbusa halus seperti bir hitam tanpa alkohol.',
                    'channels' => [
                        ProductChannelPrice::CHANNEL_DINE_IN => 30000.0,
                        ProductChannelPrice::CHANNEL_TAKEAWAY => 32000.0,
                        ProductChannelPrice::CHANNEL_GOFOOD => 38000.0,
                        ProductChannelPrice::CHANNEL_GRABFOOD => 38000.0,
                        ProductChannelPrice::CHANNEL_SHOPEEFOOD => 37000.0,
                    ],
                    'branch_prices' => [
                        'LOC-BRG' => 34000.0,
                    ],
                    'recipe' => [
                        ['mat' => 'CFE-MAT-BEANS-TORAJA', 'qty' => 22.0, 'unit' => 'g'],
                        ['mat' => 'CFE-MAT-CUP-16OZ', 'qty' => 1.0, 'unit' => 'pcs'],
                    ],
                ],
                [
                    'code' => 'CFE-PST-CROISSANT',
                    'name' => 'Butter Croissant French Style',
                    'cat' => 'pastry',
                    'unit' => 'pcs',
                    'base_price' => 26000.0,
                    'cost' => 9500.0,
                    'desc' => 'Croissant renyah berlapis dengan aroma harum mentega murni Prancis, disajikan hangat setiap pagi.',
                    'channels' => [
                        ProductChannelPrice::CHANNEL_DINE_IN => 26000.0,
                        ProductChannelPrice::CHANNEL_TAKEAWAY => 26000.0,
                        ProductChannelPrice::CHANNEL_GOFOOD => 32000.0,
                        ProductChannelPrice::CHANNEL_GRABFOOD => 32000.0,
                        ProductChannelPrice::CHANNEL_SHOPEEFOOD => 31000.0,
                    ],
                    'branch_prices' => [
                        'LOC-BRG' => 28000.0,
                    ],
                    'recipe' => [
                        ['mat' => 'CFE-MAT-BUTTER', 'qty' => 0.04, 'unit' => 'kg'],
                    ],
                ],
                [
                    'code' => 'CFE-PST-CINNAMON',
                    'name' => 'Artisan Cinnamon Roll Cream Cheese',
                    'cat' => 'pastry',
                    'unit' => 'pcs',
                    'base_price' => 28000.0,
                    'cost' => 10200.0,
                    'desc' => 'Roti gulung kayu manis aromatik berbalut lelehan saus krim keju vanilla homemade.',
                    'channels' => [
                        ProductChannelPrice::CHANNEL_DINE_IN => 28000.0,
                        ProductChannelPrice::CHANNEL_TAKEAWAY => 28000.0,
                        ProductChannelPrice::CHANNEL_GOFOOD => 35000.0,
                        ProductChannelPrice::CHANNEL_GRABFOOD => 35000.0,
                        ProductChannelPrice::CHANNEL_SHOPEEFOOD => 34000.0,
                    ],
                    'branch_prices' => [
                        'LOC-BRG' => 30000.0,
                    ],
                    'recipe' => [
                        ['mat' => 'CFE-MAT-BUTTER', 'qty' => 0.035, 'unit' => 'kg'],
                    ],
                ],
                [
                    'code' => 'CFE-RTD-LITER',
                    'name' => 'Es Kopi Susu Senja 1 Liter (Family Bottle)',
                    'cat' => 'rtd',
                    'unit' => 'pcs',
                    'base_price' => 85000.0,
                    'cost' => 26000.0,
                    'desc' => 'Kemasan botol kaca 1 Liter siap saji untuk stok di rumah atau kantor. Tahan hingga 5 hari dalam kulkas.',
                    'channels' => [
                        ProductChannelPrice::CHANNEL_DINE_IN => 85000.0,
                        ProductChannelPrice::CHANNEL_TAKEAWAY => 85000.0,
                        ProductChannelPrice::CHANNEL_GOFOOD => 105000.0,
                        ProductChannelPrice::CHANNEL_GRABFOOD => 105000.0,
                        ProductChannelPrice::CHANNEL_SHOPEEFOOD => 102000.0,
                    ],
                    'branch_prices' => [
                        'LOC-BRG' => 88000.0,
                    ],
                    'recipe' => [
                        ['mat' => 'CFE-MAT-BEANS-GAYO', 'qty' => 70.0, 'unit' => 'g'],
                        ['mat' => 'CFE-MAT-MILK-FRESH', 'qty' => 500.0, 'unit' => 'ml'],
                        ['mat' => 'CFE-MAT-SYRUP-AREN', 'qty' => 100.0, 'unit' => 'ml'],
                        ['mat' => 'CFE-MAT-BOTTLE-1L', 'qty' => 1.0, 'unit' => 'pcs'],
                    ],
                ],
                [
                    'code' => 'CFE-BEAN-GAYO-250',
                    'name' => 'Biji Kopi Arabika Gayo Honey 250g',
                    'cat' => 'packaged_beans',
                    'unit' => 'pcs',
                    'base_price' => 95000.0,
                    'cost' => 48500.0,
                    'desc' => 'Roasted whole beans asal Aceh Gayo proses Honey. Notes: Wild Honey, Peach, Sweet Citrus. Medium Roast.',
                    'channels' => [
                        ProductChannelPrice::CHANNEL_DINE_IN => 95000.0,
                        ProductChannelPrice::CHANNEL_TAKEAWAY => 95000.0,
                        ProductChannelPrice::CHANNEL_GOFOOD => 110000.0,
                        ProductChannelPrice::CHANNEL_GRABFOOD => 110000.0,
                        ProductChannelPrice::CHANNEL_SHOPEEFOOD => 108000.0,
                    ],
                    'branch_prices' => [
                        'LOC-BRG' => 95000.0,
                    ],
                    'recipe' => [
                        ['mat' => 'CFE-MAT-BEANS-GAYO', 'qty' => 250.0, 'unit' => 'g'],
                        ['mat' => 'CFE-MAT-POUCH-250G', 'qty' => 1.0, 'unit' => 'pcs'],
                    ],
                ],
                [
                    'code' => 'CFE-BEAN-TORAJA-250',
                    'name' => 'Biji Kopi Arabika Toraja Sapan 250g',
                    'cat' => 'packaged_beans',
                    'unit' => 'pcs',
                    'base_price' => 98000.0,
                    'cost' => 52000.0,
                    'desc' => 'Roasted beans specialty asal Toraja Sapan ketinggian 1800 mdpl. Notes: Dark Chocolate, Spices, Herbal Tea.',
                    'channels' => [
                        ProductChannelPrice::CHANNEL_DINE_IN => 98000.0,
                        ProductChannelPrice::CHANNEL_TAKEAWAY => 98000.0,
                        ProductChannelPrice::CHANNEL_GOFOOD => 115000.0,
                        ProductChannelPrice::CHANNEL_GRABFOOD => 115000.0,
                        ProductChannelPrice::CHANNEL_SHOPEEFOOD => 112000.0,
                    ],
                    'branch_prices' => [
                        'LOC-BRG' => 98000.0,
                    ],
                    'recipe' => [
                        ['mat' => 'CFE-MAT-BEANS-TORAJA', 'qty' => 250.0, 'unit' => 'g'],
                        ['mat' => 'CFE-MAT-POUCH-250G', 'qty' => 1.0, 'unit' => 'pcs'],
                    ],
                ],
                [
                    'code' => 'CFE-DRIP-BAG',
                    'name' => 'Drip Bag Coffee Box (5 x 10g)',
                    'cat' => 'packaged_beans',
                    'unit' => 'box',
                    'base_price' => 55000.0,
                    'cost' => 22000.0,
                    'desc' => 'Kopi filter kantong gantung praktis tinggal seduh air panas. Pas untuk traveling atau seduhan cepat di kantor.',
                    'channels' => [
                        ProductChannelPrice::CHANNEL_DINE_IN => 55000.0,
                        ProductChannelPrice::CHANNEL_TAKEAWAY => 55000.0,
                        ProductChannelPrice::CHANNEL_GOFOOD => 68000.0,
                        ProductChannelPrice::CHANNEL_GRABFOOD => 68000.0,
                        ProductChannelPrice::CHANNEL_SHOPEEFOOD => 65000.0,
                    ],
                    'branch_prices' => [
                        'LOC-BRG' => 55000.0,
                    ],
                    'recipe' => [
                        ['mat' => 'CFE-MAT-BEANS-GAYO', 'qty' => 50.0, 'unit' => 'g'],
                    ],
                ],
                [
                    'code' => 'CFE-TUMBLER',
                    'name' => 'Kopi Senja Stainless Steel Tumbler 500ml',
                    'cat' => 'merch',
                    'unit' => 'pcs',
                    'base_price' => 165000.0,
                    'cost' => 85000.0,
                    'desc' => 'Tumbler termal vacuum insulated stainless steel grade 304. Menjaga suhu dingin 12 jam dan panas 6 jam.',
                    'channels' => [
                        ProductChannelPrice::CHANNEL_DINE_IN => 165000.0,
                        ProductChannelPrice::CHANNEL_TAKEAWAY => 165000.0,
                        ProductChannelPrice::CHANNEL_GOFOOD => 185000.0,
                        ProductChannelPrice::CHANNEL_GRABFOOD => 185000.0,
                        ProductChannelPrice::CHANNEL_SHOPEEFOOD => 180000.0,
                    ],
                    'branch_prices' => [
                        'LOC-BRG' => 165000.0,
                    ],
                    'recipe' => [],
                ],
            ];

            $productMap = [];
            foreach ($productCatalog as $pSpec) {
                $product = Product::withTrashed()->where('business_id', $business->id)->where('code', $pSpec['code'])->first();
                if ($product) {
                    if ($product->trashed()) {
                        $product->restore();
                    }
                    $product->update([
                        'name' => $pSpec['name'],
                        'slug' => Str::slug($pSpec['name']),
                        'category_id' => $prodCats[$pSpec['cat']]->id,
                        'output_unit_id' => $units[$pSpec['unit']]->id,
                        'selling_price' => $pSpec['base_price'],
                        'base_cost' => $pSpec['cost'],
                        'min_stock' => 10,
                        'is_active' => true,
                        'description' => $pSpec['desc'],
                    ]);
                } else {
                    $product = Product::create([
                        'business_id' => $business->id,
                        'code' => $pSpec['code'],
                        'name' => $pSpec['name'],
                        'slug' => Str::slug($pSpec['name']),
                        'category_id' => $prodCats[$pSpec['cat']]->id,
                        'output_unit_id' => $units[$pSpec['unit']]->id,
                        'selling_price' => $pSpec['base_price'],
                        'base_cost' => $pSpec['cost'],
                        'min_stock' => 10,
                        'is_active' => true,
                        'description' => $pSpec['desc'],
                    ]);
                }

                // 1. Simpan Multi-Harga Sales Channel (Dine In, Takeaway, GoFood, GrabFood, ShopeeFood)
                ProductChannelPrice::where('product_id', $product->id)->delete();
                foreach ($pSpec['channels'] as $channel => $price) {
                    ProductChannelPrice::create([
                        'id' => (string) Str::uuid(),
                        'business_id' => $business->id,
                        'product_id' => $product->id,
                        'channel' => $channel,
                        'price' => $price,
                    ]);
                }

                // 2. Simpan Multi-Harga Antar Cabang / Outlet (Braga vs Dago)
                BranchProductPrice::where('product_id', $product->id)->delete();
                if (isset($pSpec['branch_prices'])) {
                    foreach ($pSpec['branch_prices'] as $locCode => $brPrice) {
                        $targetLoc = $locCode === 'LOC-BRG' ? $locBraga : $locDago;
                        BranchProductPrice::create([
                            'id' => (string) Str::uuid(),
                            'business_id' => $business->id,
                            'location_id' => $targetLoc->id,
                            'product_id' => $product->id,
                            'price' => $brPrice,
                            'cost_price' => $pSpec['cost'],
                            'is_available' => true,
                        ]);
                    }
                }

                // 3. Formula Resep BOM & Cost Model
                if (! empty($pSpec['recipe'])) {
                    $costModel = CostModel::updateOrCreate(
                        ['business_id' => $business->id, 'product_id' => $product->id],
                        [
                            'name' => 'HPP ' . $product->name,
                            'slug' => 'hpp-' . Str::slug($product->name),
                            'method' => CostModel::METHOD_RECIPE_BOM,
                            'output_basis' => CostModel::BASIS_PLANNED,
                            'is_active' => true,
                        ]
                    );

                    $bomHeader = BomHeader::firstOrCreate(
                        ['cost_model_id' => $costModel->id],
                        [
                            'name' => 'Resep Standar ' . $product->name,
                            'type' => BomHeader::TYPE_RECIPE,
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
                                    'waste_percentage' => 1.5,
                                ]
                            );
                        }
                    }
                }

                $productMap[$pSpec['code']] = $product;
            }

            // ─────────────────────────────────────────────────────────────────
            // 8. MODIFIERS & ADD-ONS (MILK CHOICE, SIZE, SWEETNESS, ICE, GRIND)
            // ─────────────────────────────────────────────────────────────────
            // Group 1: Pilihan Susu
            $grpMilk = ModifierGroup::updateOrCreate(
                ['business_id' => $business->id, 'name' => 'Pilihan Susu (Dairy & Plant-Based)'],
                ['selection_type' => 'single', 'is_required' => false, 'min_selection' => 0, 'max_selection' => 1, 'sort_order' => 1, 'is_active' => true]
            );
            $optFreshMilk = ModifierOption::updateOrCreate(['modifier_group_id' => $grpMilk->id, 'name' => 'Fresh Milk Pasteurisasi'], ['price_delta' => 0, 'affects_material' => true, 'sort_order' => 1, 'is_active' => true]);
            $optOatMilk = ModifierOption::updateOrCreate(['modifier_group_id' => $grpMilk->id, 'name' => 'Oatly Barista Oat Milk'], ['price_delta' => 8000, 'affects_material' => true, 'sort_order' => 2, 'is_active' => true]);
            $optAlmondMilk = ModifierOption::updateOrCreate(['modifier_group_id' => $grpMilk->id, 'name' => 'Almond Milk Barista'], ['price_delta' => 8000, 'affects_material' => true, 'sort_order' => 3, 'is_active' => true]);

            // Link modifier oatmilk to Oatly material
            ModifierOptionMaterial::updateOrCreate(
                ['modifier_option_id' => $optOatMilk->id, 'material_id' => $materialMap['CFE-MAT-MILK-OAT']->id],
                ['quantity' => 150.0, 'unit_id' => $units['ml']->id]
            );

            // Group 2: Ukuran Gelas
            $grpSize = ModifierGroup::updateOrCreate(
                ['business_id' => $business->id, 'name' => 'Ukuran Gelas (Cup Size)'],
                ['selection_type' => 'single', 'is_required' => true, 'min_selection' => 1, 'max_selection' => 1, 'sort_order' => 2, 'is_active' => true]
            );
            $optRegular = ModifierOption::updateOrCreate(['modifier_group_id' => $grpSize->id, 'name' => 'Regular (12oz)'], ['price_delta' => 0, 'affects_material' => false, 'sort_order' => 1, 'is_active' => true]);
            $optLarge = ModifierOption::updateOrCreate(['modifier_group_id' => $grpSize->id, 'name' => 'Large (16oz)'], ['price_delta' => 6000, 'affects_material' => false, 'sort_order' => 2, 'is_active' => true]);

            // Group 3: Extra Shot Espresso
            $grpShot = ModifierGroup::updateOrCreate(
                ['business_id' => $business->id, 'name' => 'Ekstra Espresso Shot'],
                ['selection_type' => 'single', 'is_required' => false, 'min_selection' => 0, 'max_selection' => 1, 'sort_order' => 3, 'is_active' => true]
            );
            $optExtra1Shot = ModifierOption::updateOrCreate(['modifier_group_id' => $grpShot->id, 'name' => 'Ekstra 1 Shot Espresso Gayo'], ['price_delta' => 6000, 'affects_material' => true, 'sort_order' => 1, 'is_active' => true]);
            $optExtra2Shot = ModifierOption::updateOrCreate(['modifier_group_id' => $grpShot->id, 'name' => 'Ekstra 2 Shot Espresso Gayo'], ['price_delta' => 11000, 'affects_material' => true, 'sort_order' => 2, 'is_active' => true]);

            // Group 4: Sweetness Level
            $grpSweet = ModifierGroup::updateOrCreate(
                ['business_id' => $business->id, 'name' => 'Tingkat Kemanisan (Sweetness)'],
                ['selection_type' => 'single', 'is_required' => false, 'min_selection' => 0, 'max_selection' => 1, 'sort_order' => 4, 'is_active' => true]
            );
            ModifierOption::updateOrCreate(['modifier_group_id' => $grpSweet->id, 'name' => 'Normal Sweet (100%)'], ['price_delta' => 0, 'affects_material' => false, 'sort_order' => 1, 'is_active' => true]);
            ModifierOption::updateOrCreate(['modifier_group_id' => $grpSweet->id, 'name' => 'Less Sweet (50%)'], ['price_delta' => 0, 'affects_material' => false, 'sort_order' => 2, 'is_active' => true]);
            ModifierOption::updateOrCreate(['modifier_group_id' => $grpSweet->id, 'name' => 'No Sugar (0%)'], ['price_delta' => 0, 'affects_material' => false, 'sort_order' => 3, 'is_active' => true]);
            ModifierOption::updateOrCreate(['modifier_group_id' => $grpSweet->id, 'name' => 'Pemanis Alami Stevia'], ['price_delta' => 3000, 'affects_material' => false, 'sort_order' => 4, 'is_active' => true]);

            // Group 5: Grind Size for Packaged Beans
            $grpGrind = ModifierGroup::updateOrCreate(
                ['business_id' => $business->id, 'name' => 'Tingkat Gilingan Kopi (Grind Size)'],
                ['selection_type' => 'single', 'is_required' => true, 'min_selection' => 1, 'max_selection' => 1, 'sort_order' => 5, 'is_active' => true]
            );
            ModifierOption::updateOrCreate(['modifier_group_id' => $grpGrind->id, 'name' => 'Biji Utuh (Whole Beans)'], ['price_delta' => 0, 'affects_material' => false, 'sort_order' => 1, 'is_active' => true]);
            ModifierOption::updateOrCreate(['modifier_group_id' => $grpGrind->id, 'name' => 'Giling Kasar (Cold Brew & French Press)'], ['price_delta' => 0, 'affects_material' => false, 'sort_order' => 2, 'is_active' => true]);
            ModifierOption::updateOrCreate(['modifier_group_id' => $grpGrind->id, 'name' => 'Giling Medium (V60 & Aeropress)'], ['price_delta' => 0, 'affects_material' => false, 'sort_order' => 3, 'is_active' => true]);
            ModifierOption::updateOrCreate(['modifier_group_id' => $grpGrind->id, 'name' => 'Giling Halus (Espresso & Moka Pot)'], ['price_delta' => 0, 'affects_material' => false, 'sort_order' => 4, 'is_active' => true]);

            // Hubungkan modifier ke produk
            $linkModGroup = function (string $productId, string $groupId, int $sort) {
                $exists = DB::table('product_modifier_groups')
                    ->where('product_id', $productId)
                    ->where('modifier_group_id', $groupId)
                    ->exists();

                if (! $exists) {
                    DB::table('product_modifier_groups')->insert([
                        'id' => (string) Str::uuid(),
                        'product_id' => $productId,
                        'modifier_group_id' => $groupId,
                        'sort_order' => $sort,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            };

            $coffeeDrinkCodes = ['CFE-KOPI-AREN', 'CFE-LATTE-HOT', 'CFE-MATCHA-OAT', 'CFE-COLD-BREW'];
            foreach ($coffeeDrinkCodes as $cCode) {
                if (isset($productMap[$cCode])) {
                    $pId = $productMap[$cCode]->id;
                    $linkModGroup($pId, $grpMilk->id, 1);
                    $linkModGroup($pId, $grpSize->id, 2);
                    $linkModGroup($pId, $grpShot->id, 3);
                    $linkModGroup($pId, $grpSweet->id, 4);
                }
            }

            $beansCodes = ['CFE-BEAN-GAYO-250', 'CFE-BEAN-TORAJA-250'];
            foreach ($beansCodes as $bCode) {
                if (isset($productMap[$bCode])) {
                    $linkModGroup($productMap[$bCode]->id, $grpGrind->id, 1);
                }
            }

            // ─────────────────────────────────────────────────────────────────
            // 9. MARKETPLACE INTEGRATIONS & MULTI-CHANNEL PRICING (SHOPEE, TOKOPEDIA, TIKTOK SHOP)
            // ─────────────────────────────────────────────────────────────────
            $mpAccounts = [
                MarketplaceAccount::CHANNEL_SHOPEE => MarketplaceAccount::updateOrCreate(
                    [
                        'business_id' => $business->id,
                        'channel' => MarketplaceAccount::CHANNEL_SHOPEE,
                        'shop_id' => 'sp-senja-9912',
                    ],
                    [
                        'shop_name' => 'Kopi Senja Official Shop (Shopee Mall)',
                        'status' => MarketplaceAccount::STATUS_CONNECTED,
                        'is_active' => true,
                        'auto_sync_stock' => true,
                        'auto_sync_price' => true,
                        'stock_buffer' => 5,
                        'price_multiplier' => 1.15, // Mark-up 15% untuk komisi platform
                        'last_synced_at' => Carbon::now()->subMinutes(15),
                    ]
                ),
                MarketplaceAccount::CHANNEL_TOKOPEDIA => MarketplaceAccount::updateOrCreate(
                    [
                        'business_id' => $business->id,
                        'channel' => MarketplaceAccount::CHANNEL_TOKOPEDIA,
                        'shop_id' => 'tk-senja-8821',
                    ],
                    [
                        'shop_name' => 'Kopi Senja Roastery Bandung (Official Store)',
                        'status' => MarketplaceAccount::STATUS_CONNECTED,
                        'is_active' => true,
                        'auto_sync_stock' => true,
                        'auto_sync_price' => true,
                        'stock_buffer' => 5,
                        'price_multiplier' => 1.12, // Mark-up 12%
                        'last_synced_at' => Carbon::now()->subMinutes(20),
                    ]
                ),
                MarketplaceAccount::CHANNEL_TIKTOK => MarketplaceAccount::updateOrCreate(
                    [
                        'business_id' => $business->id,
                        'channel' => MarketplaceAccount::CHANNEL_TIKTOK,
                        'shop_id' => 'tt-senja-7731',
                    ],
                    [
                        'shop_name' => 'Kopi Senja Coffee & Brews (TikTok Shop)',
                        'status' => MarketplaceAccount::STATUS_CONNECTED,
                        'is_active' => true,
                        'auto_sync_stock' => true,
                        'auto_sync_price' => false, // Promo pricing live streaming manual
                        'stock_buffer' => 3,
                        'price_multiplier' => 1.10,
                        'last_synced_at' => Carbon::now()->subMinutes(30),
                    ]
                ),
            ];

            // Mapping Produk ke Marketplace dengan Multi-Harga Per Marketplace
            $mpProductConfigs = [
                'CFE-BEAN-GAYO-250' => [
                    'shopee' => ['price' => 109250.0, 'stock' => 50, 'ext_id' => 'SP-PRD-001', 'ext_sku' => 'SP-SKU-GAYO250'],
                    'tokopedia' => ['price' => 106400.0, 'stock' => 45, 'ext_id' => 'TK-PRD-001', 'ext_sku' => 'TK-SKU-GAYO250'],
                    'tiktok_shop' => ['price' => 99000.0, 'stock' => 30, 'ext_id' => 'TT-PRD-001', 'ext_sku' => 'TT-SKU-GAYO250'],
                ],
                'CFE-BEAN-TORAJA-250' => [
                    'shopee' => ['price' => 112700.0, 'stock' => 40, 'ext_id' => 'SP-PRD-002', 'ext_sku' => 'SP-SKU-TOR250'],
                    'tokopedia' => ['price' => 109760.0, 'stock' => 35, 'ext_id' => 'TK-PRD-002', 'ext_sku' => 'TK-SKU-TOR250'],
                    'tiktok_shop' => ['price' => 105000.0, 'stock' => 25, 'ext_id' => 'TT-PRD-002', 'ext_sku' => 'TT-SKU-TOR250'],
                ],
                'CFE-RTD-LITER' => [
                    'shopee' => ['price' => 97750.0, 'stock' => 20, 'ext_id' => 'SP-PRD-003', 'ext_sku' => 'SP-SKU-RTD1L'],
                    'tokopedia' => ['price' => 95200.0, 'stock' => 25, 'ext_id' => 'TK-PRD-003', 'ext_sku' => 'TK-SKU-RTD1L'],
                    'tiktok_shop' => ['price' => 89000.0, 'stock' => 20, 'ext_id' => 'TT-PRD-003', 'ext_sku' => 'TT-SKU-RTD1L'],
                ],
                'CFE-DRIP-BAG' => [
                    'shopee' => ['price' => 65000.0, 'stock' => 80, 'ext_id' => 'SP-PRD-004', 'ext_sku' => 'SP-SKU-DRIP5'],
                    'tokopedia' => ['price' => 62000.0, 'stock' => 70, 'ext_id' => 'TK-PRD-004', 'ext_sku' => 'TK-SKU-DRIP5'],
                    'tiktok_shop' => ['price' => 59000.0, 'stock' => 60, 'ext_id' => 'TT-PRD-004', 'ext_sku' => 'TT-SKU-DRIP5'],
                ],
                'CFE-TUMBLER' => [
                    'shopee' => ['price' => 189750.0, 'stock' => 15, 'ext_id' => 'SP-PRD-005', 'ext_sku' => 'SP-SKU-TMBLR'],
                    'tokopedia' => ['price' => 184800.0, 'stock' => 15, 'ext_id' => 'TK-PRD-005', 'ext_sku' => 'TK-SKU-TMBLR'],
                    'tiktok_shop' => ['price' => 175000.0, 'stock' => 10, 'ext_id' => 'TT-PRD-005', 'ext_sku' => 'TT-SKU-TMBLR'],
                ],
            ];

            foreach ($mpProductConfigs as $pCode => $chans) {
                if (! isset($productMap[$pCode])) {
                    continue;
                }
                $prod = $productMap[$pCode];

                foreach ($chans as $chanKey => $spec) {
                    $mpAcc = $mpAccounts[$chanKey];

                    MarketplaceProductMapping::updateOrCreate(
                        [
                            'business_id' => $business->id,
                            'product_id' => $prod->id,
                            'channel' => $chanKey,
                        ],
                        [
                            'marketplace_account_id' => $mpAcc->id,
                            'external_product_id' => $spec['ext_id'],
                            'external_sku_code' => $spec['ext_sku'],
                            'external_product_name' => $prod->name,
                            'channel_price' => $spec['price'],
                            'price_multiplier' => $mpAcc->price_multiplier,
                            'sync_price_auto' => $chanKey !== 'tiktok_shop',
                            'channel_stock' => $spec['stock'],
                            'stock_buffer' => $mpAcc->stock_buffer,
                            'sync_stock_auto' => true,
                            'is_active' => true,
                            'sync_status' => MarketplaceProductMapping::STATUS_SYNCED,
                            'last_price_synced_at' => Carbon::now()->subMinutes(25),
                            'last_stock_synced_at' => Carbon::now()->subMinutes(10),
                        ]
                    );
                }
            }

            // Pesanan Marketplace Realistis
            $mpOrdersData = [
                [
                    'channel' => MarketplaceAccount::CHANNEL_SHOPEE,
                    'external_order_id' => '260929SP88910',
                    'external_order_sn' => '260929SP88910SN',
                    'buyer_name' => 'Hendro Kusumo',
                    'buyer_phone' => '0812-9988-1122',
                    'total_amount' => 283500.0,
                    'channel_fee' => 17010.0, // 6% fee
                    'order_status' => 'DELIVERED',
                    'shipping_provider' => 'J&T Express',
                    'tracking_number' => 'JP9988221100',
                    'items_summary' => [
                        ['product' => 'Biji Kopi Arabika Gayo Honey 250g', 'qty' => 2, 'price' => 109250.0],
                        ['product' => 'Drip Bag Coffee Box (5 x 10g)', 'qty' => 1, 'price' => 65000.0],
                    ],
                    'placed_at' => Carbon::now()->subDays(2),
                    'synced_at' => Carbon::now()->subDays(2)->addMinutes(5),
                ],
                [
                    'channel' => MarketplaceAccount::CHANNEL_TOKOPEDIA,
                    'external_order_id' => 'INV/20260929/MPL/388912',
                    'external_order_sn' => 'TKP-388912-ORD',
                    'buyer_name' => 'Anita Wijaya',
                    'buyer_phone' => '0813-4455-6677',
                    'total_amount' => 290160.0,
                    'channel_fee' => 14508.0, // 5% fee
                    'order_status' => 'SHIPPED',
                    'shipping_provider' => 'SiCepat REG',
                    'tracking_number' => '004928172911',
                    'items_summary' => [
                        ['product' => 'Biji Kopi Arabika Toraja Sapan 250g', 'qty' => 1, 'price' => 109760.0],
                        ['product' => 'Kopi Senja Stainless Steel Tumbler 500ml', 'qty' => 1, 'price' => 184800.0],
                    ],
                    'placed_at' => Carbon::now()->subDay(),
                    'synced_at' => Carbon::now()->subDay()->addMinutes(4),
                ],
                [
                    'channel' => MarketplaceAccount::CHANNEL_TIKTOK,
                    'external_order_id' => '578891029188219',
                    'external_order_sn' => 'TT-LIVE-9188219',
                    'buyer_name' => 'Kevin Sanjaya',
                    'buyer_phone' => '0815-6677-8899',
                    'total_amount' => 188000.0,
                    'channel_fee' => 11280.0, // 6% fee
                    'order_status' => 'READY_TO_SHIP',
                    'shipping_provider' => 'Anteraja',
                    'tracking_number' => '100099887766',
                    'items_summary' => [
                        ['product' => 'Es Kopi Susu Senja 1 Liter (Family Bottle)', 'qty' => 1, 'price' => 89000.0],
                        ['product' => 'Biji Kopi Arabika Gayo Honey 250g', 'qty' => 1, 'price' => 99000.0],
                    ],
                    'placed_at' => Carbon::now()->subHours(4),
                    'synced_at' => Carbon::now()->subHours(4)->addMinutes(2),
                ],
            ];

            foreach ($mpOrdersData as $mpo) {
                MarketplaceOrder::updateOrCreate(
                    [
                        'business_id' => $business->id,
                        'channel' => $mpo['channel'],
                        'external_order_id' => $mpo['external_order_id'],
                    ],
                    array_merge($mpo, [
                        'marketplace_account_id' => $mpAccounts[$mpo['channel']]->id,
                    ])
                );
            }

            // ─────────────────────────────────────────────────────────────────
            // 10. POS TABLES (DINE-IN MEJA 01 - 10)
            // ─────────────────────────────────────────────────────────────────
            $tableSpecs = [
                ['num' => 'Meja 01', 'name' => 'Indoor Window View (Garden)', 'cap' => 2, 'status' => PosTable::STATUS_AVAILABLE],
                ['num' => 'Meja 02', 'name' => 'Indoor Window View (Street)', 'cap' => 2, 'status' => PosTable::STATUS_AVAILABLE],
                ['num' => 'Meja 03', 'name' => 'Bar Counter & Pour-over Station', 'cap' => 2, 'status' => PosTable::STATUS_AVAILABLE],
                ['num' => 'Meja 04', 'name' => 'Bar Counter Espressivo', 'cap' => 2, 'status' => PosTable::STATUS_AVAILABLE],
                ['num' => 'Meja 05', 'name' => 'Indoor Sofa Cozy Corner', 'cap' => 4, 'status' => PosTable::STATUS_AVAILABLE],
                ['num' => 'Meja 06', 'name' => 'Indoor Sofa Bohemian', 'cap' => 4, 'status' => PosTable::STATUS_AVAILABLE],
                ['num' => 'Meja 07', 'name' => 'Long Table Co-Working (Power Plug)', 'cap' => 6, 'status' => PosTable::STATUS_AVAILABLE],
                ['num' => 'Meja 08', 'name' => 'Meeting Room Private (Glass Door)', 'cap' => 8, 'status' => PosTable::STATUS_AVAILABLE],
                ['num' => 'Teras 01', 'name' => 'Outdoor Garden Smoking Area', 'cap' => 4, 'status' => PosTable::STATUS_AVAILABLE],
                ['num' => 'Teras 02', 'name' => 'Outdoor Balcony Dago Skyline', 'cap' => 4, 'status' => PosTable::STATUS_AVAILABLE],
            ];

            $tables = [];
            foreach ($tableSpecs as $ts) {
                $table = PosTable::updateOrCreate(
                    ['business_id' => $business->id, 'table_number' => $ts['num']],
                    [
                        'location_id' => $locDago->id,
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
            // 11. POS REGISTER & ACTIVE SHIFT
            // ─────────────────────────────────────────────────────────────────
            $posRegister = PosRegister::firstOrCreate(
                ['business_id' => $business->id, 'code' => 'REG-DAGO-01'],
                [
                    'location_id' => $locDago->id,
                    'name' => 'Kasir Utama Bar Dago',
                    'is_active' => true,
                ]
            );

            $posShift = PosShift::where('business_id', $business->id)
                ->where('location_id', $locDago->id)
                ->where('status', PosShift::STATUS_OPEN)
                ->first();

            if (! $posShift) {
                $posShift = PosShift::create([
                    'business_id' => $business->id,
                    'pos_register_id' => $posRegister->id,
                    'location_id' => $locDago->id,
                    'user_id' => $cashierUser->id,
                    'opened_at' => Carbon::now()->startOfDay()->addHours(8),
                    'opening_cash' => 500000.0, // Modal awal laci kasir Rp 500.000
                    'status' => PosShift::STATUS_OPEN,
                    'notes' => 'Shift Pagi - Siang Kasir Utama Coffee Bar Dago',
                ]);
            }

            // ─────────────────────────────────────────────────────────────────
            // 12. CUSTOMERS (B2C MEMBERS & B2B WHOLESALE CORPORATES)
            // ─────────────────────────────────────────────────────────────────
            $customerDimas = Customer::updateOrCreate(
                ['business_id' => $business->id, 'phone' => '0812-7788-9901'],
                [
                    'name' => 'Dimas Anggara',
                    'email' => 'customer.cafe@cooca.id',
                    'company_name' => 'Pribadi / Member Gold',
                    'membership_tier' => 'gold',
                    'points_balance' => 250,
                    'billing_address' => 'Jl. Dipatiukur No. 24, Bandung',
                    'shipping_address' => 'Jl. Dipatiukur No. 24, Bandung',
                    'credit_limit' => 500000.0,
                    'is_active' => true,
                ]
            );

            $customerB2BSolusi = Customer::updateOrCreate(
                ['business_id' => $business->id, 'email' => 'finance@solusidigital.id'],
                [
                    'name' => 'PT Solusi Digital Bandung',
                    'phone' => '022-7201928',
                    'company_name' => 'PT Solusi Digital Bandung',
                    'billing_address' => 'Gedung Menara Asia Lt. 4, Jl. Asia Afrika No. 12, Bandung',
                    'shipping_address' => 'Gedung Menara Asia Lt. 4, Jl. Asia Afrika No. 12, Bandung',
                    'tax_identification_number' => '02.345.678.9-429.000',
                    'credit_limit' => 15000000.0,
                    'is_active' => true,
                ]
            );

            $customerB2BCowork = Customer::updateOrCreate(
                ['business_id' => $business->id, 'email' => 'ops@ruangtemu.space'],
                [
                    'name' => 'Ruang Temu Co-Working & Cafe',
                    'phone' => '0813-8822-1100',
                    'company_name' => 'Ruang Temu Co-Working',
                    'billing_address' => 'Jl. Ir. H. Juanda No. 108, Dago, Bandung',
                    'shipping_address' => 'Jl. Ir. H. Juanda No. 108, Dago, Bandung',
                    'tax_identification_number' => '03.882.192.4-429.000',
                    'credit_limit' => 20000000.0,
                    'is_active' => true,
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // 13. POS ORDERS WITH COMPLETE TAXES & SERVICE CHARGE
            // ─────────────────────────────────────────────────────────────────
            $seedPosOrder = function (array $orderAttrs, array $itemsList) use ($business, $locDago, $posShift, $cashierUser) {
                $payments = $orderAttrs['payments'] ?? [];
                unset($orderAttrs['payments']);

                $order = PosOrder::updateOrCreate(
                    ['business_id' => $business->id, 'order_number' => $orderAttrs['order_number']],
                    array_merge([
                        'business_id' => $business->id,
                        'location_id' => $locDago->id,
                        'pos_shift_id' => $posShift->id,
                        'user_id' => $cashierUser->id,
                        'order_date' => Carbon::now()->toDateString(),
                        'created_at' => $orderAttrs['created_at'] ?? Carbon::now(),
                    ], $orderAttrs)
                );

                // Reset child items
                $oldItemIds = PosOrderItem::where('pos_order_id', $order->id)->pluck('id');
                PosOrderItemModifier::whereIn('pos_order_item_id', $oldItemIds)->delete();
                PosOrderItem::where('pos_order_id', $order->id)->delete();
                PosOrderPayment::where('pos_order_id', $order->id)->delete();

                foreach ($itemsList as $it) {
                    $item = PosOrderItem::create([
                        'pos_order_id' => $order->id,
                        'product_id' => $it['product']->id,
                        'product_name' => $it['product']->name,
                        'product_code' => $it['product']->code,
                        'unit_price' => $it['unit_price'],
                        'unit_cost_hpp' => $it['product']->base_cost ?? 5000.0,
                        'quantity' => $it['quantity'],
                        'subtotal' => $it['unit_price'] * $it['quantity'],
                        'discount_amount' => 0.0,
                        'total_price' => $it['unit_price'] * $it['quantity'],
                        'total_hpp' => ($it['product']->base_cost ?? 5000.0) * $it['quantity'],
                        'notes' => $it['notes'] ?? null,
                    ]);

                    if (! empty($it['modifiers'])) {
                        foreach ($it['modifiers'] as $mod) {
                            PosOrderItemModifier::create([
                                'pos_order_item_id' => $item->id,
                                'modifier_group_id' => $mod['group']->id,
                                'modifier_option_id' => $mod['option']->id,
                                'modifier_group_name' => $mod['group']->name,
                                'modifier_option_name' => $mod['option']->name,
                                'unit_price' => $mod['option']->price_delta,
                                'quantity' => 1,
                                'subtotal' => $mod['option']->price_delta,
                            ]);
                        }
                    }
                }

                if (! empty($payments)) {
                    foreach ($payments as $pay) {
                        PosOrderPayment::create([
                            'pos_order_id' => $order->id,
                            'payment_method' => $pay['method'],
                            'amount' => $pay['amount'],
                            'reference_number' => $pay['ref'] ?? null,
                            'fee_amount' => $pay['fee'] ?? 0.0,
                            'net_amount' => $pay['amount'] - ($pay['fee'] ?? 0.0),
                            'status' => 'paid',
                        ]);
                    }
                }

                return $order;
            };

            // Order 1: Dine-In Meja 02 (Pajak 10% + Service Charge 5%)
            $subtotal1 = (36000.0 * 2) + 26000.0; // 2x Es Kopi Susu Aren Large Oatmilk + 1x Butter Croissant = 98.000
            $sc1 = $subtotal1 * 0.05; // 4.900
            $tax1 = $subtotal1 * 0.10; // 9.800
            $total1 = $subtotal1 + $sc1 + $tax1; // 112.700

            $seedPosOrder([
                'order_number' => 'POS-DAGO-2026-001',
                'order_type' => 'dine_in',
                'sales_channel' => ProductChannelPrice::CHANNEL_DINE_IN,
                'pos_table_id' => $tables['Meja 02']->id,
                'table_or_reference' => 'Meja 02',
                'customer_id' => $customerDimas->id,
                'customer_name_guest' => 'Dimas Anggara (Gold)',
                'subtotal' => $subtotal1,
                'service_charge_percentage' => 5.0,
                'service_charge_amount' => $sc1,
                'tax_percentage' => 10.0,
                'tax_amount' => $tax1,
                'rounding_amount' => 0.0,
                'total_amount' => $total1,
                'paid_amount' => $total1,
                'change_amount' => 0.0,
                'status' => 'completed',
                'payments' => [
                    ['method' => 'qris', 'amount' => $total1, 'ref' => 'QRIS-BCA-981290'],
                ],
            ], [
                [
                    'product' => $productMap['CFE-KOPI-AREN'],
                    'quantity' => 2,
                    'unit_price' => 36000.0, // Base 22k + Large 6k + Oatmilk 8k
                    'notes' => 'Less sugar 50%, oatmilk',
                    'modifiers' => [
                        ['group' => $grpSize, 'option' => $optLarge],
                        ['group' => $grpMilk, 'option' => $optOatMilk],
                    ],
                ],
                [
                    'product' => $productMap['CFE-PST-CROISSANT'],
                    'quantity' => 1,
                    'unit_price' => 26000.0,
                    'notes' => 'Hangatkan di oven',
                ],
            ]);

            // Order 2: Dine-In Meja 05 (Pajak 10% + Service Charge 5%)
            $subtotal2 = 28000.0 + 32000.0 + 28000.0; // Hot Latte + V60 Gayo + Cinnamon Roll = 88.000
            $sc2 = $subtotal2 * 0.05; // 4.400
            $tax2 = $subtotal2 * 0.10; // 8.800
            $total2 = $subtotal2 + $sc2 + $tax2; // 101.200

            $seedPosOrder([
                'order_number' => 'POS-DAGO-2026-002',
                'order_type' => 'dine_in',
                'sales_channel' => ProductChannelPrice::CHANNEL_DINE_IN,
                'pos_table_id' => $tables['Meja 05']->id,
                'table_or_reference' => 'Meja 05',
                'customer_name_guest' => 'Sarah & Friends',
                'subtotal' => $subtotal2,
                'service_charge_percentage' => 5.0,
                'service_charge_amount' => $sc2,
                'tax_percentage' => 10.0,
                'tax_amount' => $tax2,
                'rounding_amount' => 0.0,
                'total_amount' => $total2,
                'paid_amount' => $total2,
                'change_amount' => 0.0,
                'status' => 'completed',
                'payments' => [
                    ['method' => 'edc_debit', 'amount' => $total2, 'ref' => 'MANDIRI-DEBIT-77821'],
                ],
            ], [
                ['product' => $productMap['CFE-LATTE-HOT'], 'quantity' => 1, 'unit_price' => 28000.0],
                ['product' => $productMap['CFE-V60-GAYO'], 'quantity' => 1, 'unit_price' => 32000.0],
                ['product' => $productMap['CFE-PST-CINNAMON'], 'quantity' => 1, 'unit_price' => 28000.0],
            ]);

            // Order 3: Takeaway Kasir (Pajak 10%, tanpa Service Charge)
            $subtotal3 = 85000.0 + 55000.0; // 1L Kopi Susu Senja + 1 Box Drip Bag = 140.000
            $tax3 = $subtotal3 * 0.10; // 14.000
            $total3 = $subtotal3 + $tax3; // 154.000

            $seedPosOrder([
                'order_number' => 'POS-DAGO-2026-003',
                'order_type' => 'takeaway',
                'sales_channel' => ProductChannelPrice::CHANNEL_TAKEAWAY,
                'table_or_reference' => 'Takeaway Kasir',
                'customer_name_guest' => 'Bapak Ronald',
                'subtotal' => $subtotal3,
                'service_charge_percentage' => 0.0,
                'service_charge_amount' => 0.0,
                'tax_percentage' => 10.0,
                'tax_amount' => $tax3,
                'rounding_amount' => 0.0,
                'total_amount' => $total3,
                'paid_amount' => 160000.0,
                'change_amount' => 6000.0,
                'status' => 'completed',
                'payments' => [
                    ['method' => 'cash', 'amount' => 160000.0, 'ref' => 'CASH-PAY-003'],
                ],
            ], [
                ['product' => $productMap['CFE-RTD-LITER'], 'quantity' => 1, 'unit_price' => 85000.0],
                ['product' => $productMap['CFE-DRIP-BAG'], 'quantity' => 1, 'unit_price' => 55000.0],
            ]);

            // Order 4: Online GoFood Integration (Multi-Harga GoFood + Pajak 10%)
            $subtotal4 = (28000.0 * 3) + 35000.0; // 3x Kopi Susu Aren (@28k) + 1x Cinnamon Roll (@35k) = 119.000
            $tax4 = $subtotal4 * 0.10; // 11.900
            $total4 = $subtotal4 + $tax4; // 130.900

            $seedPosOrder([
                'order_number' => 'POS-DAGO-2026-004',
                'order_type' => 'delivery',
                'sales_channel' => ProductChannelPrice::CHANNEL_GOFOOD,
                'external_order_ref' => 'GF-2026-78192',
                'customer_name_guest' => 'Driver GoFood (Bpk. Mulyadi)',
                'subtotal' => $subtotal4,
                'service_charge_percentage' => 0.0,
                'service_charge_amount' => 0.0,
                'tax_percentage' => 10.0,
                'tax_amount' => $tax4,
                'rounding_amount' => 0.0,
                'total_amount' => $total4,
                'paid_amount' => $total4,
                'change_amount' => 0.0,
                'status' => 'completed',
                'payments' => [
                    ['method' => 'transfer', 'amount' => $total4, 'ref' => 'GOPAY-SETTLE-88192'],
                ],
            ], [
                ['product' => $productMap['CFE-KOPI-AREN'], 'quantity' => 3, 'unit_price' => 28000.0],
                ['product' => $productMap['CFE-PST-CINNAMON'], 'quantity' => 1, 'unit_price' => 35000.0],
            ]);

            // Order 5: Online GrabFood Integration (Multi-Harga GrabFood + Pajak 10%)
            $subtotal5 = (42000.0 * 2) + (32000.0 * 2); // 2x Matcha Oat (@42k) + 2x Croissant (@32k) = 148.000
            $tax5 = $subtotal5 * 0.10; // 14.800
            $total5 = $subtotal5 + $tax5; // 162.800

            $seedPosOrder([
                'order_number' => 'POS-DAGO-2026-005',
                'order_type' => 'delivery',
                'sales_channel' => ProductChannelPrice::CHANNEL_GRABFOOD,
                'external_order_ref' => 'GB-2026-33921',
                'customer_name_guest' => 'Driver Grab (Bpk. Dani)',
                'subtotal' => $subtotal5,
                'service_charge_percentage' => 0.0,
                'service_charge_amount' => 0.0,
                'tax_percentage' => 10.0,
                'tax_amount' => $tax5,
                'rounding_amount' => 0.0,
                'total_amount' => $total5,
                'paid_amount' => $total5,
                'change_amount' => 0.0,
                'status' => 'completed',
                'payments' => [
                    ['method' => 'transfer', 'amount' => $total5, 'ref' => 'OVO-GRAB-44512'],
                ],
            ], [
                ['product' => $productMap['CFE-MATCHA-OAT'], 'quantity' => 2, 'unit_price' => 42000.0],
                ['product' => $productMap['CFE-PST-CROISSANT'], 'quantity' => 2, 'unit_price' => 32000.0],
            ]);

            // ─────────────────────────────────────────────────────────────────
            // 14. B2B WHOLESALE SALES ORDERS & INVOICES (WITH PPN 11% TAX)
            // ─────────────────────────────────────────────────────────────────
            // SO & Invoice 1: PT Solusi Digital Bandung (10kg Biji Kopi Gayo + 5kg Biji Kopi Toraja)
            $so1Subtotal = (150000.0 * 10) + (150000.0 * 5); // Grosir 150rb/kg x 15kg = 2.250.000
            $so1Tax = $so1Subtotal * 0.11; // PPN 11% = 247.500
            $so1Total = $so1Subtotal + $so1Tax; // 2.497.500

            $so1 = SalesOrder::updateOrCreate(
                ['business_id' => $business->id, 'so_number' => 'SO-2026-0091'],
                [
                    'customer_id' => $customerB2BSolusi->id,
                    'order_date' => Carbon::now()->subDays(5)->toDateString(),
                    'expected_delivery_date' => Carbon::now()->subDays(3)->toDateString(),
                    'subtotal' => $so1Subtotal,
                    'discount_amount' => 0.0,
                    'tax_percentage' => 11.0,
                    'tax_amount' => $so1Tax,
                    'total_amount' => $so1Total,
                    'status' => 'fulfilled',
                    'shipping_address' => $customerB2BSolusi->shipping_address,
                    'notes' => 'Pasokan rutin biji kopi pantry kantor PT Solusi Digital - Giling V60 & Whole Beans.',
                ]
            );

            SalesOrderItem::where('sales_order_id', $so1->id)->delete();
            SalesOrderItem::create([
                'sales_order_id' => $so1->id,
                'product_id' => $productMap['CFE-BEAN-GAYO-250']->id,
                'product_name' => 'Biji Kopi Arabika Gayo Wholesale Bulk (10 kg)',
                'unit_price' => 150000.0,
                'quantity' => 10.0,
                'fulfilled_quantity' => 10.0,
                'discount_amount' => 0.0,
                'subtotal' => 1500000.0,
            ]);
            SalesOrderItem::create([
                'sales_order_id' => $so1->id,
                'product_id' => $productMap['CFE-BEAN-TORAJA-250']->id,
                'product_name' => 'Biji Kopi Arabika Toraja Sapan Wholesale Bulk (5 kg)',
                'unit_price' => 150000.0,
                'quantity' => 5.0,
                'fulfilled_quantity' => 5.0,
                'discount_amount' => 0.0,
                'subtotal' => 750000.0,
            ]);

            $inv1 = Invoice::updateOrCreate(
                ['business_id' => $business->id, 'invoice_number' => 'INV-2026-0042'],
                [
                    'customer_id' => $customerB2BSolusi->id,
                    'sales_order_id' => $so1->id,
                    'location_id' => $locDago->id,
                    'invoice_date' => Carbon::now()->subDays(5)->toDateString(),
                    'due_date' => Carbon::now()->addDays(9)->toDateString(),
                    'status' => Invoice::STATUS_PAID,
                    'subtotal' => $so1Subtotal,
                    'discount_amount' => 0.0,
                    'tax_percentage' => 11.0,
                    'tax_amount' => $so1Tax,
                    'shipping_cost' => 0.0,
                    'total_amount' => $so1Total,
                    'paid_amount' => $so1Total,
                    'balance_due' => 0.0,
                    'payment_terms' => 'Net 14 Hari',
                    'notes' => 'Faktur Pajak PPN 11% - Pelunasan via Transfer Bank BCA.',
                    'created_by' => $primaryOwner->id,
                ]
            );

            InvoiceItem::where('invoice_id', $inv1->id)->delete();
            InvoiceItem::create([
                'invoice_id' => $inv1->id,
                'product_id' => $productMap['CFE-BEAN-GAYO-250']->id,
                'item_name' => 'Biji Kopi Arabika Gayo Wholesale Bulk (10 kg)',
                'quantity' => 10.0,
                'unit_id' => $units['kg']->id,
                'unit_price' => 150000.0,
                'unit_hpp' => 85000.0,
            ]);
            InvoiceItem::create([
                'invoice_id' => $inv1->id,
                'product_id' => $productMap['CFE-BEAN-TORAJA-250']->id,
                'item_name' => 'Biji Kopi Arabika Toraja Sapan Wholesale Bulk (5 kg)',
                'quantity' => 5.0,
                'unit_id' => $units['kg']->id,
                'unit_price' => 150000.0,
                'unit_hpp' => 90000.0,
            ]);

            // SO & Invoice 2: Ruang Temu Co-Working (15kg House Blend + Tumbler Custom)
            $so2Subtotal = (140000.0 * 15) + (150000.0 * 5); // 2.100.000 + 750.000 = 2.850.000
            $so2Tax = $so2Subtotal * 0.11; // 313.500
            $so2Total = $so2Subtotal + $so2Tax; // 3.163.500

            $so2 = SalesOrder::updateOrCreate(
                ['business_id' => $business->id, 'so_number' => 'SO-2026-0092'],
                [
                    'customer_id' => $customerB2BCowork->id,
                    'order_date' => Carbon::now()->subDays(2)->toDateString(),
                    'expected_delivery_date' => Carbon::now()->addDays(1)->toDateString(),
                    'subtotal' => $so2Subtotal,
                    'discount_amount' => 0.0,
                    'tax_percentage' => 11.0,
                    'tax_amount' => $so2Tax,
                    'total_amount' => $so2Total,
                    'status' => 'confirmed',
                    'shipping_address' => $customerB2BCowork->shipping_address,
                    'notes' => 'Pasokan reguler kafe co-working Ruang Temu. Termin tempo 14 hari.',
                ]
            );

            SalesOrderItem::where('sales_order_id', $so2->id)->delete();
            SalesOrderItem::create([
                'sales_order_id' => $so2->id,
                'product_id' => $productMap['CFE-BEAN-GAYO-250']->id,
                'product_name' => 'Biji Kopi House Blend Espresso (15 kg)',
                'unit_price' => 140000.0,
                'quantity' => 15.0,
                'fulfilled_quantity' => 0.0,
                'discount_amount' => 0.0,
                'subtotal' => 2100000.0,
            ]);
            SalesOrderItem::create([
                'sales_order_id' => $so2->id,
                'product_id' => $productMap['CFE-TUMBLER']->id,
                'product_name' => 'Kopi Senja Stainless Steel Tumbler (5 pcs)',
                'unit_price' => 150000.0,
                'quantity' => 5.0,
                'fulfilled_quantity' => 0.0,
                'discount_amount' => 0.0,
                'subtotal' => 750000.0,
            ]);

            $inv2 = Invoice::updateOrCreate(
                ['business_id' => $business->id, 'invoice_number' => 'INV-2026-0043'],
                [
                    'customer_id' => $customerB2BCowork->id,
                    'sales_order_id' => $so2->id,
                    'location_id' => $locDago->id,
                    'invoice_date' => Carbon::now()->subDays(2)->toDateString(),
                    'due_date' => Carbon::now()->addDays(12)->toDateString(),
                    'status' => Invoice::STATUS_SENT,
                    'subtotal' => $so2Subtotal,
                    'discount_amount' => 0.0,
                    'tax_percentage' => 11.0,
                    'tax_amount' => $so2Tax,
                    'shipping_cost' => 0.0,
                    'total_amount' => $so2Total,
                    'paid_amount' => 0.0,
                    'balance_due' => $so2Total,
                    'payment_terms' => 'Net 14 Hari',
                    'notes' => 'Faktur tempo 14 hari. Mohon transfer ke rekening BCA 012-345-6789 a.n. PT Senja Kopi Nusantara.',
                    'created_by' => $primaryOwner->id,
                ]
            );

            InvoiceItem::where('invoice_id', $inv2->id)->delete();
            InvoiceItem::create([
                'invoice_id' => $inv2->id,
                'product_id' => $productMap['CFE-BEAN-GAYO-250']->id,
                'item_name' => 'Biji Kopi House Blend Espresso (15 kg)',
                'quantity' => 15.0,
                'unit_id' => $units['kg']->id,
                'unit_price' => 140000.0,
                'unit_hpp' => 80000.0,
            ]);
            InvoiceItem::create([
                'invoice_id' => $inv2->id,
                'product_id' => $productMap['CFE-TUMBLER']->id,
                'item_name' => 'Kopi Senja Stainless Steel Tumbler (5 pcs)',
                'quantity' => 5.0,
                'unit_id' => $units['pcs']->id,
                'unit_price' => 150000.0,
                'unit_hpp' => 85000.0,
            ]);

            // ─────────────────────────────────────────────────────────────────
            // 15. STOREFRONT & LANDING PAGE CONFIGURATION (ARTISAN_BREW THEME)
            // ─────────────────────────────────────────────────────────────────
            BusinessLandingPage::updateOrCreate(
                ['business_id' => $business->id],
                [
                    'is_published' => true,
                    'industry_preset' => 'fnb_cafe',
                    'theme_preset' => 'artisan_brew',
                    'theme_color' => '#92400E', // Warm amber / roasted coffee
                    'font_family' => 'playfair',
                    'dark_mode' => false, // Default light, customizable by owner in editor
                    'headline' => 'Specialty Coffee & Artisan Roastery',
                    'subheadline' => 'Biji kopi Nusantara pilihan yang disangrai dengan presisi tinggi dan diseduh oleh barista bersertifikasi.',
                    'announcement_badge' => 'Fresh Roasted Beans & Cozy Co-working Space',
                    'cta_primary_text' => 'Pesan Sekarang',
                    'cta_primary_url' => '#menu',
                    'cta_secondary_text' => 'Dine-In QR Order',
                    'cta_secondary_url' => '#qr-dinein',
                    'about_title' => 'Komitmen pada Setiap Tetes Kopi',
                    'about_story' => 'Kami bermitra langsung dengan kelompok tani kopi di Takengon Gayo dan Tana Toraja untuk menghadirkan biji kopi single origin dengan skor cupping 85+. Setiap cangkir adalah dedikasi rasa dan kerja keras petani lokal.',
                    'services_title' => 'Layanan & Menu Signature',
                    'services_subtitle' => 'Dari seduhan V60 lembut hingga es kopi susu aren kental dan biji kopi kemasan siap bawa pulang.',
                    'operational_hours' => [
                        'monday' => ['open' => '07:30', 'close' => '22:00'],
                        'tuesday' => ['open' => '07:30', 'close' => '22:00'],
                        'wednesday' => ['open' => '07:30', 'close' => '22:00'],
                        'thursday' => ['open' => '07:30', 'close' => '22:00'],
                        'friday' => ['open' => '07:30', 'close' => '23:00'],
                        'saturday' => ['open' => '07:00', 'close' => '23:00'],
                        'sunday' => ['open' => '07:00', 'close' => '22:00'],
                    ],
                    'section_visibility' => [
                        'hero' => true,
                        'tasting_notes' => true,
                        'catalog_filter' => true,
                        'featured_products' => true,
                        'services' => true,
                        'about' => true,
                        'gallery' => true,
                        'testimonials' => true,
                        'faq' => true,
                        'operational_hours' => true,
                        'contact' => true,
                        'qr_dinein' => true,
                    ],
                    'active_pages' => [
                        'home' => true,
                        'catalog' => true,
                        'about' => true,
                        'contact' => true,
                        'reservation' => true,
                        'articles' => true,
                    ],
                    'whatsapp_number' => '081211110002',
                    'whatsapp_welcome_message' => 'Halo Kopi Senja Utama, saya ingin bertanya tentang menu kopi, biji sangrai atau reservasi meja.',
                    'instagram_handle' => '@kopisenja.dago',
                    'tiktok_handle' => '@kopisenja.id',
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // 16. E-COMMERCE STOREFRONT SETTINGS & PAYMENT METHODS (QRIS ONLY)
            // ─────────────────────────────────────────────────────────────────
            CommerceStoreSetting::updateOrCreate(
                ['business_id' => $business->id],
                [
                    'is_storefront_enabled' => true,
                    'is_discoverable' => true,
                    'allow_pickup' => true,
                    'allow_delivery' => true,
                    'min_order_amount' => 15000.0,
                    'order_auto_cancel_minutes' => 60,
                    'lead_time_hours' => 1,
                    'announcement_text' => 'Biji Kopi Fresh Roasted & Menu Signature siap diantar ke rumah atau ambil di outlet.',
                    'order_notes_placeholder' => 'Contoh: Jangan terlalu manis, extra ice batu, atau titipkan di pos security.',
                    'origin_contact_name' => 'Barista Kopi Senja Dago',
                    'origin_contact_phone' => '081211110002',
                    'origin_address' => $locDago->address,
                    'origin_location_id' => $locDago->id,
                ]
            );

            // Deactivate any non-QRIS payment methods for this business
            CommercePaymentMethod::where('business_id', $business->id)
                ->where('type', '!=', 'qris')
                ->update(['is_active' => false]);

            // Ensure QRIS Cooca Pay is the sole active payment method
            CommercePaymentMethod::updateOrCreate(
                ['business_id' => $business->id, 'type' => 'qris'],
                [
                    'bank_name' => 'QRIS Cooca Pay',
                    'account_holder' => $business->name,
                    'account_number' => 'NMID-ID102030405060',
                    'instructions' => 'Scan QRIS menggunakan aplikasi e-wallet atau mobile banking (GoPay, OVO, ShopeePay, DANA, BCA, Mandiri, BRI, dll). Terkonfirmasi instan.',
                    'is_active' => true,
                    'sort_order' => 1,
                ]
            );

            // Shipping rules for delivery
            CommerceShippingRule::updateOrCreate(
                ['business_id' => $business->id, 'name' => 'Kurir Instan Internal (Area Dago & Sekitarnya)'],
                [
                    'rule_type' => CommerceShippingRule::TYPE_FLAT,
                    'rate_amount' => 10000.0,
                    'min_order_for_free' => 150000.0,
                    'is_active' => true,
                    'sort_order' => 1,
                ]
            );

            CommerceShippingRule::updateOrCreate(
                ['business_id' => $business->id, 'name' => 'Kurir Reguler Seluruh Kota Bandung'],
                [
                    'rule_type' => CommerceShippingRule::TYPE_FLAT,
                    'rate_amount' => 18000.0,
                    'min_order_for_free' => 200000.0,
                    'is_active' => true,
                    'sort_order' => 2,
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // 17. REAL-TIME INVENTORY STOCK (OUTLET DAGO, BRAGA & WAREHOUSE)
            // ─────────────────────────────────────────────────────────────────
            foreach ($productMap as $code => $prod) {
                // Stock at Outlet Dago (Main Cafe & Roastery)
                $qtyDago = match (true) {
                    str_starts_with($code, 'CFE-BEAN') => 45.0,
                    str_starts_with($code, 'CFE-CB') => 60.0,
                    str_starts_with($code, 'CFE-TUMBLER') => 25.0,
                    str_starts_with($code, 'CFE-PO') => 30.0,
                    default => 100.0,
                };

                InventoryStock::updateOrCreate(
                    [
                        'business_id' => $business->id,
                        'location_id' => $locDago->id,
                        'product_id' => $prod->id,
                    ],
                    [
                        'material_id' => null,
                        'quantity' => $qtyDago,
                        'reserved_quantity' => 0.0,
                        'last_cost' => $prod->base_cost ?: 15000.0,
                        'avg_purchase_cost' => $prod->base_cost ?: 15000.0,
                    ]
                );

                // Stock at Outlet Braga (Branch Store)
                $qtyBraga = match (true) {
                    str_starts_with($code, 'CFE-BEAN') => 20.0,
                    str_starts_with($code, 'CFE-CB') => 35.0,
                    str_starts_with($code, 'CFE-TUMBLER') => 12.0,
                    str_starts_with($code, 'CFE-PO') => 15.0,
                    default => 50.0,
                };

                InventoryStock::updateOrCreate(
                    [
                        'business_id' => $business->id,
                        'location_id' => $locBraga->id,
                        'product_id' => $prod->id,
                    ],
                    [
                        'material_id' => null,
                        'quantity' => $qtyBraga,
                        'reserved_quantity' => 0.0,
                        'last_cost' => $prod->base_cost ?: 15000.0,
                        'avg_purchase_cost' => $prod->base_cost ?: 15000.0,
                    ]
                );

                // Stock at Central Roastery Warehouse
                $qtyWarehouse = match (true) {
                    str_starts_with($code, 'CFE-BEAN') => 250.0,
                    str_starts_with($code, 'CFE-CB') => 150.0,
                    str_starts_with($code, 'CFE-TUMBLER') => 100.0,
                    str_starts_with($code, 'CFE-PO') => 80.0,
                    default => 300.0,
                };

                InventoryStock::updateOrCreate(
                    [
                        'business_id' => $business->id,
                        'location_id' => $locWarehouse->id,
                        'product_id' => $prod->id,
                    ],
                    [
                        'material_id' => null,
                        'quantity' => $qtyWarehouse,
                        'reserved_quantity' => 0.0,
                        'last_cost' => $prod->base_cost ?: 15000.0,
                        'avg_purchase_cost' => $prod->base_cost ?: 15000.0,
                    ]
                );
            }
        });
    }
}
