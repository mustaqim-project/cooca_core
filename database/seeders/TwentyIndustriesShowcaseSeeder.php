<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Template\BusinessTemplateService;
use App\Models\Business;
use App\Models\BusinessTypeTemplate;
use App\Models\CostCategory;
use App\Models\CostComponent;
use App\Models\CostModel;
use App\Models\Customer;
use App\Models\InventoryStock;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LaborRate;
use App\Models\Location;
use App\Models\Machine;
use App\Models\Material;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\PosRegister;
use App\Models\PosShift;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class TwentyIndustriesShowcaseSeeder extends Seeder
{
    private BusinessTemplateService $templateService;

    public function __construct()
    {
        $this->templateService = new BusinessTemplateService;
    }

    public function run(): void
    {
        $industries = $this->getIndustryShowcaseDefinitions();

        foreach ($industries as $index => $spec) {
            $this->command?->info(sprintf('[%d/20] Seeding akun & master data: %s (%s)', $index + 1, $spec['business']['name'], $spec['user']['email']));
            $this->seedSingleIndustry($spec);
        }

        $this->command?->info('✓ Sukses seeding 20 akun bisnis owner super lengkap!');
    }

    /**
     * Seed a single complete business showcase.
     *
     * @param array<string, mixed> $spec
     */
    private function seedSingleIndustry(array $spec): void
    {
        DB::transaction(function () use ($spec) {
            // 1. User Owner
            $user = User::updateOrCreate(
                ['email' => $spec['user']['email']],
                [
                    'name' => $spec['user']['name'],
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]
            );

            // 2. Business
            $bizData = $spec['business'];
            $business = Business::updateOrCreate(
                ['slug' => $bizData['slug']],
                [
                    'name' => $bizData['name'],
                    'currency' => 'IDR',
                    'currency_precision' => 0,
                    'rounding_strategy' => $bizData['rounding_strategy'] ?? Business::ROUNDING_ROUND_100,
                    'allow_negative_stock' => $bizData['allow_negative_stock'] ?? false,
                    'phone' => $bizData['phone'],
                    'email' => $spec['user']['email'],
                    'address' => $bizData['address'],
                    'tax_identification_number' => $bizData['npwp'] ?? '01.234.567.8-001.000',
                    'bank_name' => $bizData['bank_name'] ?? 'Bank Central Asia (BCA)',
                    'bank_account_number' => $bizData['bank_account_number'] ?? '8800112233',
                    'bank_account_holder' => $bizData['name'],
                    'pos_enable_tax' => $bizData['pos_tax'] ?? false,
                    'pos_tax_percent' => $bizData['pos_tax_percent'] ?? 0,
                    'is_active' => true,
                ]
            );

            // Attach owner role
            $business->users()->syncWithoutDetaching([
                $user->id => ['id' => (string) Str::uuid(), 'role' => 'owner'],
            ]);
            $user->update(['active_business_id' => $business->id]);

            // 3. Primary Location
            $locationName = $spec['location_name'] ?? 'Outlet & Gudang Utama';
            $location = Location::updateOrCreate(
                ['business_id' => $business->id, 'slug' => Str::slug($locationName)],
                [
                    'name' => $locationName,
                    'code' => 'LOC-01',
                    'type' => 'outlet',
                    'is_primary' => true,
                    'is_active' => true,
                    'address' => $bizData['address'],
                ]
            );

            // 4. Apply BusinessTypeTemplate (Components & Categories)
            $template = BusinessTypeTemplate::where('code', $spec['template_code'])->first();
            if ($template && CostComponent::where('business_id', $business->id)->count() === 0) {
                $this->templateService->apply($business, $template);
            }

            // 5. Supplier
            $supplier = null;
            if (! empty($spec['supplier'])) {
                $supplier = Supplier::updateOrCreate(
                    ['business_id' => $business->id, 'slug' => Str::slug($spec['supplier']['name'])],
                    [
                        'name' => $spec['supplier']['name'],
                        'contact_person' => $spec['supplier']['contact'],
                        'phone' => $spec['supplier']['phone'],
                        'email' => Str::slug($spec['supplier']['name']) . '@supplier.test',
                        'address' => 'Pusat Grosir & Pergudangan Terpadu',
                        'notes' => 'Termin Pembayaran: Net 30',
                    ]
                );
            }

            // Units lookup helper
            $units = $this->resolveUnits();

            // 6. Materials, Prices & Initial Stock
            $materialMap = [];
            foreach ($spec['materials'] as $mSpec) {
                $unitObj = $units[$mSpec['unit_code']] ?? $units['pcs'];
                $mat = Material::updateOrCreate(
                    ['business_id' => $business->id, 'code' => $mSpec['code']],
                    [
                        'name' => $mSpec['name'],
                        'slug' => Str::slug($mSpec['name']),
                        'unit_id' => $unitObj->id,
                        'supplier_id' => $supplier?->id,
                        'description' => "Material baku untuk {$business->name}",
                    ]
                );

                $mat->prices()->updateOrCreate(
                    ['effective_date' => now()->toDateString()],
                    [
                        'business_id' => $business->id,
                        'supplier_id' => $supplier?->id,
                        'purchase_unit_id' => $unitObj->id,
                        'purchase_price' => $mSpec['price'],
                        'yield_percentage' => $mSpec['yield'] ?? 100,
                        'waste_percentage' => $mSpec['waste'] ?? 0,
                    ]
                );

                // Initial Stock
                $initialQty = (float) ($mSpec['initial_stock'] ?? 100);
                $unitCost = (float) $mSpec['price'];
                if ($initialQty > 0) {
                    $invStock = InventoryStock::updateOrCreate(
                        [
                            'business_id' => $business->id,
                            'location_id' => $location->id,
                            'material_id' => $mat->id,
                        ],
                        [
                            'product_id' => null,
                            'quantity' => $initialQty,
                            'reserved_quantity' => 0,
                            'last_cost' => $unitCost,
                            'avg_purchase_cost' => $unitCost,
                        ]
                    );

                    StockMovement::firstOrCreate(
                        [
                            'business_id' => $business->id,
                            'material_id' => $mat->id,
                            'movement_type' => StockMovement::TYPE_INITIAL,
                        ],
                        [
                            'location_id' => $location->id,
                            'product_id' => null,
                            'quantity_change' => $initialQty,
                            'balance_after' => $initialQty,
                            'unit_cost' => $unitCost,
                            'total_cost' => $initialQty * $unitCost,
                            'reference_number' => 'INIT-' . $mat->code,
                            'notes' => 'Saldo Awal Showcase Tutorial',
                            'created_by' => $user->id,
                        ]
                    );
                }

                $materialMap[$mSpec['code']] = $mat;
            }

            // 7. Products, BOMs & Pricing
            $productMap = [];
            foreach ($spec['products'] as $pSpec) {
                $outUnit = $units[$pSpec['output_unit']] ?? $units['pcs'];
                $directMatId = ! empty($pSpec['direct_material_code']) && isset($materialMap[$pSpec['direct_material_code']])
                    ? $materialMap[$pSpec['direct_material_code']]->id
                    : null;

                $product = Product::updateOrCreate(
                    ['business_id' => $business->id, 'code' => $pSpec['code']],
                    [
                        'name' => $pSpec['name'],
                        'slug' => Str::slug($pSpec['name']),
                        'output_unit_id' => $outUnit->id,
                        'direct_material_id' => $directMatId,
                        'selling_price' => $pSpec['selling_price'],
                        'base_cost' => $pSpec['base_cost'] ?? 0,
                        'business_type_hint' => $pSpec['business_type_hint'] ?? $spec['template_code'],
                        'description' => $pSpec['description'] ?? "Katalog produk resmi {$business->name}",
                        'is_active' => true,
                    ]
                );

                // Cost Model & BOM Recipe
                $costModel = CostModel::updateOrCreate(
                    ['business_id' => $business->id, 'product_id' => $product->id, 'slug' => 'hpp-' . Str::slug($pSpec['name'])],
                    [
                        'name' => 'HPP ' . $pSpec['name'],
                        'method' => $pSpec['costing_method'] ?? CostModel::METHOD_RECIPE_BOM,
                        'is_active' => true,
                    ]
                );

                // Recipe items
                $totalHppCalc = 0.0;
                if (! empty($pSpec['recipe_items'])) {
                    $bomHeader = $costModel->bomHeaders()->firstOrCreate(
                        ['cost_model_id' => $costModel->id],
                        [
                            'name' => 'Resep / Formula ' . $pSpec['name'],
                            'type' => \App\Models\BomHeader::TYPE_RECIPE,
                            'level' => 1,
                        ]
                    );

                    foreach ($pSpec['recipe_items'] as $rItem) {
                        if (isset($materialMap[$rItem['material_code']])) {
                            $rMat = $materialMap[$rItem['material_code']];
                            $rUnit = $units[$rItem['unit_code']] ?? $units['pcs'];
                            $qty = (float) $rItem['quantity'];
                            $waste = (float) ($rItem['waste_pct'] ?? 0);

                            $bomHeader->items()->updateOrCreate(
                                ['material_id' => $rMat->id],
                                [
                                    'unit_id' => $rUnit->id,
                                    'quantity' => $qty,
                                    'waste_percentage' => $waste,
                                ]
                            );

                            $matPrice = (float) ($rMat->latestPrice?->purchase_price ?? 0);
                            $totalHppCalc += ($qty * (1 + $waste / 100) * $matPrice);
                        }
                    }
                } elseif ($directMatId) {
                    $dMat = Material::find($directMatId);
                    $totalHppCalc = (float) ($dMat?->latestPrice?->purchase_price ?? 0);
                }

                // If calculated HPP > 0, set base_cost
                if ($totalHppCalc > 0 && empty($pSpec['base_cost'])) {
                    $product->update(['base_cost' => round($totalHppCalc, 2)]);
                } elseif (! empty($pSpec['base_cost'])) {
                    $product->update(['base_cost' => (float) $pSpec['base_cost']]);
                }

                $productMap[$pSpec['code']] = $product;
            }

            // 8. Sample Customer
            $customer = null;
            if (! empty($spec['customer'])) {
                $customer = Customer::updateOrCreate(
                    ['business_id' => $business->id, 'phone' => $spec['customer']['phone']],
                    [
                        'name' => $spec['customer']['name'],
                        'slug' => Str::slug($spec['customer']['name']),
                        'email' => Str::slug($spec['customer']['name']) . '@customer.test',
                        'company_name' => $spec['customer']['company'] ?? null,
                        'billing_address' => 'Jl. Boulevard Raya No. 88',
                        'shipping_address' => 'Jl. Boulevard Raya No. 88',
                    ]
                );
            }

            // 9. Sample Completed Transaction (POS / Invoice)
            if (! empty($spec['sample_sale']) && ! empty($productMap)) {
                $saleSpec = $spec['sample_sale'];
                $firstProduct = $productMap[array_key_first($productMap)];
                $saleQty = (float) ($saleSpec['quantity'] ?? 2);
                $unitPrice = (float) $firstProduct->selling_price;
                $unitHpp = (float) $firstProduct->base_cost;
                $subtotal = $saleQty * $unitPrice;
                $totalHpp = $saleQty * $unitHpp;
                $grossProfit = max(0.0, $subtotal - $totalHpp);

                if (($saleSpec['type'] ?? 'pos') === 'pos') {
                    // Create shift & order
                    $register = PosRegister::firstOrCreate(
                        ['business_id' => $business->id, 'location_id' => $location->id],
                        ['name' => 'Kasir Utama #1', 'code' => 'REG-01', 'is_active' => true]
                    );

                    $shift = PosShift::create([
                        'business_id' => $business->id,
                        'location_id' => $location->id,
                        'pos_register_id' => $register->id,
                        'user_id' => $user->id,
                        'opened_at' => Carbon::now()->subHours(4),
                        'closed_at' => Carbon::now(),
                        'opening_cash' => 200000,
                        'closing_cash_actual' => 200000 + $subtotal,
                        'closing_cash_expected' => 200000 + $subtotal,
                        'cash_difference' => 0,
                        'total_cash_sales' => $subtotal,
                        'total_non_cash_sales' => 0,
                        'status' => PosShift::STATUS_CLOSED,
                    ]);

                    $order = PosOrder::create([
                        'business_id' => $business->id,
                        'location_id' => $location->id,
                        'pos_shift_id' => $shift->id,
                        'user_id' => $user->id,
                        'customer_id' => $customer?->id,
                        'order_number' => 'POS-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                        'order_date' => Carbon::today()->toDateString(),
                        'status' => PosOrder::STATUS_COMPLETED,
                        'order_type' => 'dine_in',
                        'customer_name_guest' => $customer?->name ?? 'Pelanggan Umum',
                        'subtotal' => $subtotal,
                        'total_amount' => $subtotal,
                        'paid_amount' => $subtotal,
                        'change_amount' => 0,
                        'total_hpp_cost' => $totalHpp,
                        'total_gross_profit' => $grossProfit,
                        'notes' => 'Transaksi Penjualan Sampel Showcase',
                    ]);

                    PosOrderItem::create([
                        'pos_order_id' => $order->id,
                        'product_id' => $firstProduct->id,
                        'product_name' => $firstProduct->name,
                        'product_code' => $firstProduct->code,
                        'unit_price' => $unitPrice,
                        'unit_cost_hpp' => $unitHpp,
                        'quantity' => $saleQty,
                        'subtotal' => $subtotal,
                        'total_price' => $subtotal,
                        'total_hpp' => $totalHpp,
                    ]);

                    PosOrderPayment::create([
                        'pos_order_id' => $order->id,
                        'payment_method' => 'cash',
                        'amount' => $subtotal,
                        'net_amount' => $subtotal,
                        'status' => 'paid',
                    ]);
                } else {
                    // Invoice
                    $invNumber = 'INV-' . date('Ym') . '-' . strtoupper(Str::random(4));
                    $invoice = Invoice::create([
                        'business_id' => $business->id,
                        'customer_id' => $customer?->id ?? Customer::firstOrCreate(['business_id' => $business->id, 'name' => 'Klien Perusahaan'])->id,
                        'location_id' => $location->id,
                        'invoice_number' => $invNumber,
                        'invoice_date' => Carbon::today()->toDateString(),
                        'due_date' => Carbon::today()->addDays(14)->toDateString(),
                        'status' => Invoice::STATUS_PAID,
                        'subtotal' => $subtotal,
                        'total_amount' => $subtotal,
                        'paid_amount' => $subtotal,
                        'balance_due' => 0,
                        'total_hpp_cost' => $totalHpp,
                        'total_gross_profit' => $grossProfit,
                        'payment_terms' => 'Net 14',
                        'notes' => 'Faktur Penjualan Resmi Sampel Showcase',
                    ]);

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $firstProduct->id,
                        'item_name' => $firstProduct->name,
                        'sku' => $firstProduct->code,
                        'quantity' => $saleQty,
                        'unit_id' => $firstProduct->output_unit_id,
                        'unit_price' => $unitPrice,
                        'unit_hpp' => $unitHpp,
                        'subtotal' => $subtotal,
                        'total_hpp' => $totalHpp,
                        'gross_profit' => $grossProfit,
                    ]);
                }
            }
        });
    }

    /**
     * Resolve cached units map.
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

        // Ensure missing essential custom units
        $needed = [
            'pcs' => ['name' => 'Pieces / Buah', 'category' => 'quantity'],
            'kg' => ['name' => 'Kilogram', 'category' => 'weight'],
            'g' => ['name' => 'Gram', 'category' => 'weight'],
            'l' => ['name' => 'Liter', 'category' => 'volume'],
            'ml' => ['name' => 'Mililiter', 'category' => 'volume'],
            'm' => ['name' => 'Meter', 'category' => 'length'],
            'cm' => ['name' => 'Centimeter', 'category' => 'length'],
            'm2' => ['name' => 'Meter Persegi', 'category' => 'area'],
            'porsi' => ['name' => 'Porsi', 'category' => 'quantity'],
            'pack' => ['name' => 'Pack / Bungkus', 'category' => 'quantity'],
            'box' => ['name' => 'Box / Dus', 'category' => 'quantity'],
            'rim' => ['name' => 'Rim (500 lbr)', 'category' => 'quantity'],
            'jam' => ['name' => 'Jam (Hours)', 'category' => 'time'],
            'sak' => ['name' => 'Sak / Karung', 'category' => 'quantity'],
            'roll' => ['name' => 'Roll Gulungan', 'category' => 'length'],
            'proyek' => ['name' => 'Paket Proyek', 'category' => 'custom'],
        ];

        foreach ($needed as $code => $data) {
            if (! isset($map[$code])) {
                $map[$code] = Unit::firstOrCreate(['code' => $code], [
                    'name' => $data['name'],
                    'category' => $data['category'],
                    'is_base' => false,
                ]);
            }
        }

        return $map;
    }

    /**
     * Get rich specifications for all 20 industries.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getIndustryShowcaseDefinitions(): array
    {
        return [
            // ─────────────────────────────────────────────────────────────
            // 1. F&B - Restoran / Rumah Makan
            // ─────────────────────────────────────────────────────────────
            [
                'template_code' => 'fnb_resto',
                'user' => ['name' => 'Budi Raharjo', 'email' => 'owner.resto@cooca.id'],
                'business' => [
                    'name' => 'Restoran Dapur Sedap Rasa',
                    'slug' => 'dapur-sedap-rasa',
                    'phone' => '0812-1111-0001',
                    'address' => 'Jl. Boulevard Kelapa Gading Blok M-12, Jakarta Utara',
                    'rounding_strategy' => Business::ROUNDING_ROUND_100,
                    'allow_negative_stock' => false,
                ],
                'location_name' => 'Resto Cabang Utama',
                'supplier' => ['name' => 'Pasar Induk Daging & Sayur Segar', 'contact' => 'H. Syarif', 'phone' => '0813-0001-0001'],
                'materials' => [
                    ['code' => 'RST-MAT-BERAS', 'name' => 'Beras Pandan Wangi', 'unit_code' => 'kg', 'price' => 16000, 'yield' => 100, 'waste' => 2, 'initial_stock' => 200],
                    ['code' => 'RST-MAT-SAPI', 'name' => 'Daging Sapi Rendang', 'unit_code' => 'kg', 'price' => 125000, 'yield' => 92, 'waste' => 5, 'initial_stock' => 50],
                    ['code' => 'RST-MAT-AYAM', 'name' => 'Daging Ayam Kampung', 'unit_code' => 'kg', 'price' => 45000, 'yield' => 95, 'waste' => 3, 'initial_stock' => 80],
                    ['code' => 'RST-MAT-BUMBU', 'name' => 'Bumbu Rempah Masak Padang', 'unit_code' => 'kg', 'price' => 35000, 'yield' => 98, 'waste' => 2, 'initial_stock' => 40],
                    ['code' => 'RST-MAT-KOTAK', 'name' => 'Kotak Nasi Bento Eco', 'unit_code' => 'pcs', 'price' => 1800, 'yield' => 100, 'waste' => 1, 'initial_stock' => 500],
                ],
                'products' => [
                    [
                        'code' => 'RST-NASI-RENDANG',
                        'name' => 'Paket Nasi Rendang Daging Sapi',
                        'output_unit' => 'porsi',
                        'selling_price' => 38000,
                        'costing_method' => CostModel::METHOD_RECIPE_BOM,
                        'recipe_items' => [
                            ['material_code' => 'RST-MAT-BERAS', 'quantity' => 0.15, 'unit_code' => 'kg', 'waste_pct' => 2],
                            ['material_code' => 'RST-MAT-SAPI', 'quantity' => 0.12, 'unit_code' => 'kg', 'waste_pct' => 4],
                            ['material_code' => 'RST-MAT-BUMBU', 'quantity' => 0.05, 'unit_code' => 'kg', 'waste_pct' => 1],
                            ['material_code' => 'RST-MAT-KOTAK', 'quantity' => 1, 'unit_code' => 'pcs', 'waste_pct' => 0],
                        ],
                    ],
                    [
                        'code' => 'RST-AYAM-BAKAR',
                        'name' => 'Nasi Ayam Bakar Bumbu Madu',
                        'output_unit' => 'porsi',
                        'selling_price' => 32000,
                        'costing_method' => CostModel::METHOD_RECIPE_BOM,
                        'recipe_items' => [
                            ['material_code' => 'RST-MAT-BERAS', 'quantity' => 0.15, 'unit_code' => 'kg', 'waste_pct' => 2],
                            ['material_code' => 'RST-MAT-AYAM', 'quantity' => 0.25, 'unit_code' => 'kg', 'waste_pct' => 3],
                            ['material_code' => 'RST-MAT-BUMBU', 'quantity' => 0.04, 'unit_code' => 'kg', 'waste_pct' => 1],
                            ['material_code' => 'RST-MAT-KOTAK', 'quantity' => 1, 'unit_code' => 'pcs', 'waste_pct' => 0],
                        ],
                    ],
                ],
                'customer' => ['name' => 'PT Makmur Jaya Abadi', 'phone' => '0811-9988-1111', 'company' => 'PT Makmur Jaya Abadi'],
                'sample_sale' => ['type' => 'pos', 'quantity' => 4],
            ],

            // ─────────────────────────────────────────────────────────────
            // 2. F&B - Coffee Shop & Cafe
            // ─────────────────────────────────────────────────────────────
            [
                'template_code' => 'fnb_cafe',
                'user' => ['name' => 'Fajar Nugraha', 'email' => 'owner.cafe@cooca.id'],
                'business' => [
                    'name' => 'Kopi Senja Utama (Coffee Shop)',
                    'slug' => 'kopi-senja-utama',
                    'phone' => '0812-1111-0002',
                    'address' => 'Jl. Ranggamalela No. 9, Dago, Bandung',
                    'rounding_strategy' => Business::ROUNDING_ROUND_100,
                    'allow_negative_stock' => false,
                ],
                'location_name' => 'Coffee Bar Dago',
                'supplier' => ['name' => 'Artisan Roastery Indonesia', 'contact' => 'Aditya', 'phone' => '0813-0001-0002'],
                'materials' => [
                    ['code' => 'CFE-MAT-BEANS', 'name' => 'Biji Kopi Arabika Gayo Fullwash', 'unit_code' => 'g', 'price' => 180, 'yield' => 98, 'waste' => 2, 'initial_stock' => 5000],
                    ['code' => 'CFE-MAT-MILK', 'name' => 'Susu UHT Fresh Pasteurisasi', 'unit_code' => 'ml', 'price' => 22, 'yield' => 99, 'waste' => 1, 'initial_stock' => 20000],
                    ['code' => 'CFE-MAT-SYRUP', 'name' => 'Sirup Gula Aren Asli', 'unit_code' => 'ml', 'price' => 35, 'yield' => 98, 'waste' => 1, 'initial_stock' => 10000],
                    ['code' => 'CFE-MAT-CUP', 'name' => 'Cup Plastik 16oz Sablon + Lid', 'unit_code' => 'pcs', 'price' => 850, 'yield' => 100, 'waste' => 1, 'initial_stock' => 1000],
                ],
                'products' => [
                    [
                        'code' => 'CFE-KOPI-AREN',
                        'name' => 'Es Kopi Susu Gula Aren Senja',
                        'output_unit' => 'cup',
                        'selling_price' => 22000,
                        'costing_method' => CostModel::METHOD_RECIPE_BOM,
                        'recipe_items' => [
                            ['material_code' => 'CFE-MAT-BEANS', 'quantity' => 18, 'unit_code' => 'g', 'waste_pct' => 2],
                            ['material_code' => 'CFE-MAT-MILK', 'quantity' => 120, 'unit_code' => 'ml', 'waste_pct' => 1],
                            ['material_code' => 'CFE-MAT-SYRUP', 'quantity' => 25, 'unit_code' => 'ml', 'waste_pct' => 1],
                            ['material_code' => 'CFE-MAT-CUP', 'quantity' => 1, 'unit_code' => 'pcs', 'waste_pct' => 0],
                        ],
                    ],
                    [
                        'code' => 'CFE-LATTE-HOT',
                        'name' => 'Hot Caffe Latte Single Origin',
                        'output_unit' => 'cup',
                        'selling_price' => 28000,
                        'costing_method' => CostModel::METHOD_RECIPE_BOM,
                        'recipe_items' => [
                            ['material_code' => 'CFE-MAT-BEANS', 'quantity' => 20, 'unit_code' => 'g', 'waste_pct' => 2],
                            ['material_code' => 'CFE-MAT-MILK', 'quantity' => 180, 'unit_code' => 'ml', 'waste_pct' => 1],
                            ['material_code' => 'CFE-MAT-CUP', 'quantity' => 1, 'unit_code' => 'pcs', 'waste_pct' => 0],
                        ],
                    ],
                ],
                'customer' => ['name' => 'Dimas Anggara', 'phone' => '0812-7788-9901'],
                'sample_sale' => ['type' => 'pos', 'quantity' => 3],
            ],

            // ─────────────────────────────────────────────────────────────
            // 3. F&B - Bakery & Cake Shop
            // ─────────────────────────────────────────────────────────────
            [
                'template_code' => 'fnb_bakery',
                'user' => ['name' => 'Hendra Delima', 'email' => 'owner.bakery@cooca.id'],
                'business' => [
                    'name' => 'Mahkota Roti & Pastry',
                    'slug' => 'mahkota-roti-pastry',
                    'phone' => '0812-1111-0003',
                    'address' => 'Jl. Pandanaran No. 56, Semarang',
                    'rounding_strategy' => Business::ROUNDING_ROUND_100,
                    'allow_negative_stock' => false,
                ],
                'location_name' => 'Dapur Bakery & Toko',
                'supplier' => ['name' => 'Bahan Roti & Pastry Prima', 'contact' => 'Toko Loyang', 'phone' => '0813-0001-0003'],
                'materials' => [
                    ['code' => 'BKR-MAT-TEPUNG', 'name' => 'Tepung Terigu Protein Tinggi Cakra', 'unit_code' => 'kg', 'price' => 14000, 'yield' => 100, 'waste' => 1, 'initial_stock' => 300],
                    ['code' => 'BKR-MAT-BUTTER', 'name' => 'Butter Mentega Wijsman Premium', 'unit_code' => 'kg', 'price' => 240000, 'yield' => 100, 'waste' => 2, 'initial_stock' => 25],
                    ['code' => 'BKR-MAT-COKLAT', 'name' => 'Cokelat Compound Batang', 'unit_code' => 'kg', 'price' => 65000, 'yield' => 98, 'waste' => 2, 'initial_stock' => 40],
                    ['code' => 'BKR-MAT-TELUR', 'name' => 'Telur Ayam Segar', 'unit_code' => 'kg', 'price' => 28000, 'yield' => 90, 'waste' => 4, 'initial_stock' => 60],
                    ['code' => 'BKR-MAT-BOX', 'name' => 'Kotak Roti Laminasi Gold', 'unit_code' => 'pcs', 'price' => 2500, 'yield' => 100, 'waste' => 1, 'initial_stock' => 400],
                ],
                'products' => [
                    [
                        'code' => 'BKR-ROTI-SOBEK',
                        'name' => 'Roti Sobek Cokelat Lumer Special',
                        'output_unit' => 'pcs',
                        'selling_price' => 28000,
                        'costing_method' => CostModel::METHOD_RECIPE_BOM,
                        'recipe_items' => [
                            ['material_code' => 'BKR-MAT-TEPUNG', 'quantity' => 0.25, 'unit_code' => 'kg', 'waste_pct' => 1],
                            ['material_code' => 'BKR-MAT-BUTTER', 'quantity' => 0.04, 'unit_code' => 'kg', 'waste_pct' => 2],
                            ['material_code' => 'BKR-MAT-COKLAT', 'quantity' => 0.08, 'unit_code' => 'kg', 'waste_pct' => 1],
                            ['material_code' => 'BKR-MAT-TELUR', 'quantity' => 0.05, 'unit_code' => 'kg', 'waste_pct' => 2],
                            ['material_code' => 'BKR-MAT-BOX', 'quantity' => 1, 'unit_code' => 'pcs', 'waste_pct' => 0],
                        ],
                    ],
                ],
                'customer' => ['name' => 'Ibu Ratna Dewi', 'phone' => '0813-2233-4455'],
                'sample_sale' => ['type' => 'pos', 'quantity' => 5],
            ],

            // ─────────────────────────────────────────────────────────────
            // 4. F&B - Cloud Kitchen & Delivery Only
            // ─────────────────────────────────────────────────────────────
            [
                'template_code' => 'fnb_cloud_kitchen',
                'user' => ['name' => 'Kevin Sanjaya', 'email' => 'owner.cloudkitchen@cooca.id'],
                'business' => [
                    'name' => 'Ghost Box Culinary (Cloud Kitchen)',
                    'slug' => 'ghost-box-culinary',
                    'phone' => '0812-1111-0004',
                    'address' => 'Jl. Tebet Barat Dalam Raya No. 45, Jakarta Selatan',
                    'rounding_strategy' => Business::ROUNDING_ROUND_100,
                    'allow_negative_stock' => false,
                ],
                'location_name' => 'Kitchen Pod Tebet',
                'supplier' => ['name' => 'Sentra Unggas & Ayam Fillet', 'contact' => 'Pak Joko', 'phone' => '0813-0001-0004'],
                'materials' => [
                    ['code' => 'CLD-MAT-AYAM', 'name' => 'Daging Ayam Fillet Paha', 'unit_code' => 'kg', 'price' => 48000, 'yield' => 95, 'waste' => 3, 'initial_stock' => 120],
                    ['code' => 'CLD-MAT-SAUS', 'name' => 'Saus Barbeque & Teriyaki', 'unit_code' => 'kg', 'price' => 40000, 'yield' => 98, 'waste' => 2, 'initial_stock' => 30],
                    ['code' => 'CLD-MAT-BERAS', 'name' => 'Beras Jepang Organik', 'unit_code' => 'kg', 'price' => 22000, 'yield' => 100, 'waste' => 1, 'initial_stock' => 100],
                    ['code' => 'CLD-MAT-BOWL', 'name' => 'Paper Rice Bowl Sealed 650ml', 'unit_code' => 'pcs', 'price' => 1600, 'yield' => 100, 'waste' => 1, 'initial_stock' => 600],
                ],
                'products' => [
                    [
                        'code' => 'CLD-BOWL-TERIYAKI',
                        'name' => 'Japanese Chicken Teriyaki Rice Bowl',
                        'output_unit' => 'porsi',
                        'selling_price' => 35000,
                        'costing_method' => CostModel::METHOD_RECIPE_BOM,
                        'recipe_items' => [
                            ['material_code' => 'CLD-MAT-BERAS', 'quantity' => 0.15, 'unit_code' => 'kg', 'waste_pct' => 1],
                            ['material_code' => 'CLD-MAT-AYAM', 'quantity' => 0.16, 'unit_code' => 'kg', 'waste_pct' => 2],
                            ['material_code' => 'CLD-MAT-SAUS', 'quantity' => 0.05, 'unit_code' => 'kg', 'waste_pct' => 1],
                            ['material_code' => 'CLD-MAT-BOWL', 'quantity' => 1, 'unit_code' => 'pcs', 'waste_pct' => 0],
                        ],
                    ],
                ],
                'customer' => ['name' => 'Andi Wijaya (GoFood/Grab)', 'phone' => '0812-4455-6677'],
                'sample_sale' => ['type' => 'pos', 'quantity' => 6],
            ],

            // ─────────────────────────────────────────────────────────────
            // 5. F&B - Catering & Prasmanan
            // ─────────────────────────────────────────────────────────────
            [
                'template_code' => 'fnb_catering',
                'user' => ['name' => 'Hj. Nurul Aini', 'email' => 'owner.catering@cooca.id'],
                'business' => [
                    'name' => 'Berkah Prasmanan Catering',
                    'slug' => 'berkah-prasmanan-catering',
                    'phone' => '0812-1111-0005',
                    'address' => 'Jl. Raya Pajajaran No. 78, Bogor',
                    'rounding_strategy' => Business::ROUNDING_ROUND_500,
                    'allow_negative_stock' => false,
                ],
                'location_name' => 'Central Kitchen Bogor',
                'supplier' => ['name' => 'Grosir Daging & Sembako Berkah', 'contact' => 'H. Maman', 'phone' => '0813-0001-0005'],
                'materials' => [
                    ['code' => 'CTR-MAT-SAPI', 'name' => 'Daging Sapi Rolade Pilihan', 'unit_code' => 'kg', 'price' => 130000, 'yield' => 95, 'waste' => 3, 'initial_stock' => 100],
                    ['code' => 'CTR-MAT-AYAM', 'name' => 'Ayam Fillet Suwir Madu', 'unit_code' => 'kg', 'price' => 45000, 'yield' => 95, 'waste' => 2, 'initial_stock' => 150],
                    ['code' => 'CTR-MAT-SOP', 'name' => 'Sayur Kimlo & Bakso Ikan', 'unit_code' => 'kg', 'price' => 35000, 'yield' => 90, 'waste' => 5, 'initial_stock' => 80],
                ],
                'products' => [
                    [
                        'code' => 'CTR-PAKET-BUFFET',
                        'name' => 'Paket Prasmanan Royal VIP (per Porsi)',
                        'output_unit' => 'porsi',
                        'selling_price' => 85000,
                        'costing_method' => CostModel::METHOD_JOB,
                        'base_cost' => 48000,
                        'recipe_items' => [
                            ['material_code' => 'CTR-MAT-SAPI', 'quantity' => 0.15, 'unit_code' => 'kg', 'waste_pct' => 2],
                            ['material_code' => 'CTR-MAT-AYAM', 'quantity' => 0.15, 'unit_code' => 'kg', 'waste_pct' => 2],
                            ['material_code' => 'CTR-MAT-SOP', 'quantity' => 0.2, 'unit_code' => 'kg', 'waste_pct' => 3],
                        ],
                    ],
                ],
                'customer' => ['name' => 'Keluarga Bpk. Hartono (Wedding)', 'phone' => '0811-3322-1100', 'company' => 'Pernikahan Hartono & Cindy'],
                'sample_sale' => ['type' => 'invoice', 'quantity' => 250],
            ],

            // ─────────────────────────────────────────────────────────────
            // 6. F&B - Frozen Food Manufacturing
            // ─────────────────────────────────────────────────────────────
            [
                'template_code' => 'fnb_frozen_food',
                'user' => ['name' => 'Anton Wijaya', 'email' => 'owner.frozenfood@cooca.id'],
                'business' => [
                    'name' => 'Salju Nusantara Frozen Food',
                    'slug' => 'salju-nusantara-frozen-food',
                    'phone' => '0812-1111-0006',
                    'address' => 'Kawasan Industri Candi Blok C-18, Semarang',
                    'rounding_strategy' => Business::ROUNDING_ROUND_100,
                    'allow_negative_stock' => false,
                ],
                'location_name' => 'Pabrik & Cold Storage',
                'supplier' => ['name' => 'Abattoir Daging Beku Indonesia', 'contact' => 'Pak Rudi', 'phone' => '0813-0001-0006'],
                'materials' => [
                    ['code' => 'FRZ-MAT-DAGING', 'name' => 'Daging Sapi Giling Australia', 'unit_code' => 'kg', 'price' => 95000, 'yield' => 98, 'waste' => 2, 'initial_stock' => 500],
                    ['code' => 'FRZ-MAT-TAPIOKA', 'name' => 'Tepung Tapioka Khusus Bakso', 'unit_code' => 'kg', 'price' => 12000, 'yield' => 100, 'waste' => 1, 'initial_stock' => 300],
                    ['code' => 'FRZ-MAT-VACUUM', 'name' => 'Plastik Vacuum Nylon 500g', 'unit_code' => 'pcs', 'price' => 950, 'yield' => 100, 'waste' => 1, 'initial_stock' => 2000],
                ],
                'products' => [
                    [
                        'code' => 'FRZ-BAKSO-SUPER',
                        'name' => 'Bakso Sapi Urat Super Vacuum Pack 500g',
                        'output_unit' => 'pack',
                        'selling_price' => 58000,
                        'costing_method' => CostModel::METHOD_PROCESS,
                        'recipe_items' => [
                            ['material_code' => 'FRZ-MAT-DAGING', 'quantity' => 0.38, 'unit_code' => 'kg', 'waste_pct' => 2],
                            ['material_code' => 'FRZ-MAT-TAPIOKA', 'quantity' => 0.12, 'unit_code' => 'kg', 'waste_pct' => 1],
                            ['material_code' => 'FRZ-MAT-VACUUM', 'quantity' => 1, 'unit_code' => 'pcs', 'waste_pct' => 0],
                        ],
                    ],
                ],
                'customer' => ['name' => 'Supermarket Swalayan Rejeki', 'phone' => '0812-9900-1122', 'company' => 'PT Swalayan Rejeki Utama'],
                'sample_sale' => ['type' => 'invoice', 'quantity' => 100],
            ],

            // ─────────────────────────────────────────────────────────────
            // 7. Manufaktur - Konveksi & Garment
            // ─────────────────────────────────────────────────────────────
            [
                'template_code' => 'mfg_garment',
                'user' => ['name' => 'Siti Aminah', 'email' => 'owner.garment@cooca.id'],
                'business' => [
                    'name' => 'Citra Busana Konveksi',
                    'slug' => 'citra-busana-konveksi',
                    'phone' => '0812-1111-0007',
                    'address' => 'Kawasan Industri Tekstil Rancaekek Blok B3, Bandung',
                    'rounding_strategy' => Business::ROUNDING_ROUND_500,
                    'allow_negative_stock' => false,
                ],
                'location_name' => 'Workshop Jahit & Sablon',
                'supplier' => ['name' => 'Pabrik Kain Mulia Tekstil', 'contact' => 'Ibu Linawati', 'phone' => '0813-0001-0007'],
                'materials' => [
                    ['code' => 'GRM-MAT-COMBED', 'name' => 'Kain Cotton Combed 30s Hitam', 'unit_code' => 'kg', 'price' => 118000, 'yield' => 95, 'waste' => 5, 'initial_stock' => 150],
                    ['code' => 'GRM-MAT-BENANG', 'name' => 'Benang Jahit Spun Polyester', 'unit_code' => 'pcs', 'price' => 12000, 'yield' => 100, 'waste' => 1, 'initial_stock' => 100],
                    ['code' => 'GRM-MAT-RIB', 'name' => 'Rib Leher Cotton Combed', 'unit_code' => 'kg', 'price' => 125000, 'yield' => 98, 'waste' => 2, 'initial_stock' => 20],
                    ['code' => 'GRM-MAT-PLASTIK', 'name' => 'Plastik OPP Bening Packing Klip', 'unit_code' => 'pcs', 'price' => 350, 'yield' => 100, 'waste' => 1, 'initial_stock' => 1500],
                ],
                'products' => [
                    [
                        'code' => 'GRM-KAOS-COMBED30S',
                        'name' => 'Kaos Polos Cotton Combed 30s Premium',
                        'output_unit' => 'pcs',
                        'selling_price' => 45000,
                        'costing_method' => CostModel::METHOD_RECIPE_BOM,
                        'recipe_items' => [
                            ['material_code' => 'GRM-MAT-COMBED', 'quantity' => 0.22, 'unit_code' => 'kg', 'waste_pct' => 4],
                            ['material_code' => 'GRM-MAT-RIB', 'quantity' => 0.02, 'unit_code' => 'kg', 'waste_pct' => 1],
                            ['material_code' => 'GRM-MAT-PLASTIK', 'quantity' => 1, 'unit_code' => 'pcs', 'waste_pct' => 0],
                        ],
                    ],
                ],
                'customer' => ['name' => 'Distro Clothing Jakarta', 'phone' => '0818-7766-5544', 'company' => 'CV Urban Vibe Apparel'],
                'sample_sale' => ['type' => 'invoice', 'quantity' => 200],
            ],

            // ─────────────────────────────────────────────────────────────
            // 8. Manufaktur - Presisi & Plastik Logam
            // ─────────────────────────────────────────────────────────────
            [
                'template_code' => 'mfg_precision',
                'user' => ['name' => 'Ir. Bambang S.', 'email' => 'owner.precision@cooca.id'],
                'business' => [
                    'name' => 'Presisi Baja & Plastik Teknik',
                    'slug' => 'presisi-baja-plastik',
                    'phone' => '0812-1111-0008',
                    'address' => 'Kawasan Industri Jababeka V Blok G-12, Cikarang',
                    'rounding_strategy' => Business::ROUNDING_ROUND_1000,
                    'allow_negative_stock' => false,
                ],
                'location_name' => 'Pabrik Injection & CNC',
                'supplier' => ['name' => 'PT Polimer Prima Petrokimia', 'contact' => 'Bpk. Tan', 'phone' => '0813-0001-0008'],
                'materials' => [
                    ['code' => 'PRC-MAT-RESIN', 'name' => 'Biji Plastik Polypropylene (PP) Murni', 'unit_code' => 'kg', 'price' => 24000, 'yield' => 97, 'waste' => 3, 'initial_stock' => 1000],
                    ['code' => 'PRC-MAT-PLAT', 'name' => 'Plat Baja SPCC Tebal 1.2mm', 'unit_code' => 'kg', 'price' => 18500, 'yield' => 95, 'waste' => 5, 'initial_stock' => 800],
                ],
                'products' => [
                    [
                        'code' => 'PRC-CASING-ELEC',
                        'name' => 'Casing Plastik Komponen Elektronik Box A-1',
                        'output_unit' => 'pcs',
                        'selling_price' => 18000,
                        'costing_method' => CostModel::METHOD_PROCESS,
                        'recipe_items' => [
                            ['material_code' => 'PRC-MAT-RESIN', 'quantity' => 0.25, 'unit_code' => 'kg', 'waste_pct' => 2],
                        ],
                    ],
                ],
                'customer' => ['name' => 'PT Astra Mandiri Parts', 'phone' => '0811-1234-5678', 'company' => 'PT Astra Mandiri Parts'],
                'sample_sale' => ['type' => 'invoice', 'quantity' => 500],
            ],

            // ─────────────────────────────────────────────────────────────
            // 9. Manufaktur - Furniture & Woodworking
            // ─────────────────────────────────────────────────────────────
            [
                'template_code' => 'mfg_furniture',
                'user' => ['name' => 'Joko Santoso', 'email' => 'owner.furniture@cooca.id'],
                'business' => [
                    'name' => 'Jati Makmur Woodworking',
                    'slug' => 'jati-makmur-woodworking',
                    'phone' => '0812-1111-0009',
                    'address' => 'Jl. Tahunan - Batealit KM 3, Jepara',
                    'rounding_strategy' => Business::ROUNDING_ROUND_1000,
                    'allow_negative_stock' => false,
                ],
                'location_name' => 'Workshop Kayu & Finishing',
                'supplier' => ['name' => 'Perhutani Wood Supplier Jepara', 'contact' => 'Bpk. Warsito', 'phone' => '0813-0001-0009'],
                'materials' => [
                    ['code' => 'FRN-MAT-JATI', 'name' => 'Kayu Jati Solid Papan Olahan', 'unit_code' => 'm', 'price' => 185000, 'yield' => 90, 'waste' => 10, 'initial_stock' => 100],
                    ['code' => 'FRN-MAT-CAT', 'name' => 'Cat Melamine Finishing Varnish Duco', 'unit_code' => 'l', 'price' => 75000, 'yield' => 95, 'waste' => 5, 'initial_stock' => 40],
                    ['code' => 'FRN-MAT-AMPLAS', 'name' => 'Amplas Kayu & Lem Epoksi', 'unit_code' => 'pcs', 'price' => 15000, 'yield' => 100, 'waste' => 0, 'initial_stock' => 80],
                ],
                'products' => [
                    [
                        'code' => 'FRN-MEJA-JATI',
                        'name' => 'Meja Makan Jati Minimalis 6 Kursi',
                        'output_unit' => 'pcs',
                        'selling_price' => 4850000,
                        'costing_method' => CostModel::METHOD_JOB,
                        'base_cost' => 2750000,
                        'recipe_items' => [
                            ['material_code' => 'FRN-MAT-JATI', 'quantity' => 12, 'unit_code' => 'm', 'waste_pct' => 5],
                            ['material_code' => 'FRN-MAT-CAT', 'quantity' => 3, 'unit_code' => 'l', 'waste_pct' => 2],
                        ],
                    ],
                ],
                'customer' => ['name' => 'Ibu Maya Sofia', 'phone' => '0812-8899-7766'],
                'sample_sale' => ['type' => 'invoice', 'quantity' => 1],
            ],

            // ─────────────────────────────────────────────────────────────
            // 10. Kreatif - Kerajinan Tangan (Handmade Craft)
            // ─────────────────────────────────────────────────────────────
            [
                'template_code' => 'mfg_craft',
                'user' => ['name' => 'Dewi Lestari', 'email' => 'owner.craft@cooca.id'],
                'business' => [
                    'name' => 'Lentera Seni Kreasi Craft',
                    'slug' => 'lentera-seni-kreasi',
                    'phone' => '0812-1111-0010',
                    'address' => 'Jl. Tirtodipuran No. 34, Mantrijeron, Yogyakarta',
                    'rounding_strategy' => Business::ROUNDING_ROUND_100,
                    'allow_negative_stock' => false,
                ],
                'location_name' => 'Galeri & Workshop Kerajinan',
                'supplier' => ['name' => 'Penyamak Kulit Nabati Magetan', 'contact' => 'Mbak Ayu', 'phone' => '0813-0001-0010'],
                'materials' => [
                    ['code' => 'CRF-MAT-KULIT', 'name' => 'Kulit Sapi Asli Vegetable Tanned (sqft)', 'unit_code' => 'm2', 'price' => 350000, 'yield' => 92, 'waste' => 8, 'initial_stock' => 50],
                    ['code' => 'CRF-MAT-BENANG', 'name' => 'Benang Lilin Wax Thread Jahit Tangan', 'unit_code' => 'pcs', 'price' => 25000, 'yield' => 100, 'waste' => 2, 'initial_stock' => 40],
                    ['code' => 'CRF-MAT-BOX', 'name' => 'Hardbox Eksklusif Pita Emas', 'unit_code' => 'pcs', 'price' => 8500, 'yield' => 100, 'waste' => 0, 'initial_stock' => 200],
                ],
                'products' => [
                    [
                        'code' => 'CRF-DOMPET-KULIT',
                        'name' => 'Dompet Bifold Kulit Asli Handmade',
                        'output_unit' => 'pcs',
                        'selling_price' => 245000,
                        'costing_method' => CostModel::METHOD_SIMPLE,
                        'recipe_items' => [
                            ['material_code' => 'CRF-MAT-KULIT', 'quantity' => 0.12, 'unit_code' => 'm2', 'waste_pct' => 5],
                            ['material_code' => 'CRF-MAT-BOX', 'quantity' => 1, 'unit_code' => 'pcs', 'waste_pct' => 0],
                        ],
                    ],
                ],
                'customer' => ['name' => 'Galeri Suvenir Bali', 'phone' => '0819-3344-5566'],
                'sample_sale' => ['type' => 'pos', 'quantity' => 10],
            ],

            // ─────────────────────────────────────────────────────────────
            // 11. Manufaktur - Percetakan & Digital Printing
            // ─────────────────────────────────────────────────────────────
            [
                'template_code' => 'mfg_printing',
                'user' => ['name' => 'Eko Prasetyo', 'email' => 'owner.printing@cooca.id'],
                'business' => [
                    'name' => 'Cahaya Offset & Digital Printing',
                    'slug' => 'cahaya-offset-printing',
                    'phone' => '0812-1111-0011',
                    'address' => 'Jl. Percetakan Negara No. 102, Jakarta Pusat',
                    'rounding_strategy' => Business::ROUNDING_ROUND_500,
                    'allow_negative_stock' => false,
                ],
                'location_name' => 'Gudang Mesin Offset & Digital',
                'supplier' => ['name' => 'Distributor Kertas Surya Palet', 'contact' => 'Bpk. Chandra', 'phone' => '0813-0001-0011'],
                'materials' => [
                    ['code' => 'PRN-MAT-ARTPAPER', 'name' => 'Kertas Art Paper 260gsm Rim Plano', 'unit_code' => 'rim', 'price' => 245000, 'yield' => 98, 'waste' => 2, 'initial_stock' => 50],
                    ['code' => 'PRN-MAT-BANNER', 'name' => 'Bahan Spanduk Flexi Korea 340g (m2)', 'unit_code' => 'm2', 'price' => 14000, 'yield' => 95, 'waste' => 5, 'initial_stock' => 300],
                    ['code' => 'PRN-MAT-TINTA', 'name' => 'Tinta Eco Solvent CMYK 1 Liter', 'unit_code' => 'l', 'price' => 320000, 'yield' => 100, 'waste' => 1, 'initial_stock' => 20],
                ],
                'products' => [
                    [
                        'code' => 'PRN-BANNER-METER',
                        'name' => 'Cetak Banner Spanduk Flexi Hi-Res (per m2)',
                        'output_unit' => 'm2',
                        'selling_price' => 28000,
                        'costing_method' => CostModel::METHOD_PROCESS,
                        'recipe_items' => [
                            ['material_code' => 'PRN-MAT-BANNER', 'quantity' => 1.05, 'unit_code' => 'm2', 'waste_pct' => 2],
                            ['material_code' => 'PRN-MAT-TINTA', 'quantity' => 0.015, 'unit_code' => 'l', 'waste_pct' => 1],
                        ],
                    ],
                ],
                'customer' => ['name' => 'Partai / Caleg Sukses', 'phone' => '0812-4455-8899', 'company' => 'Pilkada Media Center'],
                'sample_sale' => ['type' => 'invoice', 'quantity' => 150],
            ],

            // ─────────────────────────────────────────────────────────────
            // 12. Retail - Reseller & Toko Retail
            // ─────────────────────────────────────────────────────────────
            [
                'template_code' => 'retail_reseller',
                'user' => ['name' => 'Gunawan Alim', 'email' => 'owner.reseller@cooca.id'],
                'business' => [
                    'name' => 'Toko Serba Jaya Retail Mart',
                    'slug' => 'serba-jaya-retail-mart',
                    'phone' => '0812-1111-0012',
                    'address' => 'Pasar Tanah Abang Blok A Lt. Basement No. 22, Jakarta',
                    'rounding_strategy' => Business::ROUNDING_ROUND_100,
                    'allow_negative_stock' => true,
                ],
                'location_name' => 'Toko & Gudang Grosir',
                'supplier' => ['name' => 'Indo Grosir & Sembako Makmur', 'contact' => 'Koh Apin', 'phone' => '0813-0001-0012'],
                'materials' => [
                    ['code' => 'RTL-MAT-BERAS5KG', 'name' => 'Beras Premium 5kg Kemasan Pabrik', 'unit_code' => 'pack', 'price' => 72000, 'yield' => 100, 'waste' => 0, 'initial_stock' => 100],
                    ['code' => 'RTL-MAT-MINYAK2L', 'name' => 'Minyak Goreng Pouch 2 Liter', 'unit_code' => 'pack', 'price' => 33500, 'yield' => 100, 'waste' => 0, 'initial_stock' => 150],
                    ['code' => 'RTL-MAT-MIEINSTAN', 'name' => 'Mie Instan Goreng (Karton isi 40)', 'unit_code' => 'box', 'price' => 112000, 'yield' => 100, 'waste' => 0, 'initial_stock' => 80],
                ],
                'products' => [
                    [
                        'code' => 'RTL-PROD-BERAS5KG',
                        'name' => 'Beras Premium 5kg Siap Jual',
                        'output_unit' => 'pack',
                        'selling_price' => 79000,
                        'base_cost' => 72000,
                        'direct_material_code' => 'RTL-MAT-BERAS5KG',
                        'costing_method' => CostModel::METHOD_RETAIL,
                    ],
                    [
                        'code' => 'RTL-PROD-MINYAK2L',
                        'name' => 'Minyak Goreng Pouch 2 Liter',
                        'output_unit' => 'pack',
                        'selling_price' => 37000,
                        'base_cost' => 33500,
                        'direct_material_code' => 'RTL-MAT-MINYAK2L',
                        'costing_method' => CostModel::METHOD_RETAIL,
                    ],
                ],
                'customer' => ['name' => 'Warung Bu Siti', 'phone' => '0812-7711-2233'],
                'sample_sale' => ['type' => 'pos', 'quantity' => 10],
            ],

            // ─────────────────────────────────────────────────────────────
            // 13. Retail - Apotek & Toko Obat
            // ─────────────────────────────────────────────────────────────
            [
                'template_code' => 'retail_pharmacy',
                'user' => ['name' => 'apt. Sarah Nadia', 'email' => 'owner.pharmacy@cooca.id'],
                'business' => [
                    'name' => 'Apotek Sehat Medika',
                    'slug' => 'apotek-sehat-medika',
                    'phone' => '0812-1111-0013',
                    'address' => 'Jl. Cinere Raya No. 15, Depok',
                    'rounding_strategy' => Business::ROUNDING_ROUND_100,
                    'allow_negative_stock' => false,
                ],
                'location_name' => 'Apotek & Rak Obat',
                'supplier' => ['name' => 'PT Kimia Farma Trading & PBF', 'contact' => 'Bpk. Irwan', 'phone' => '0813-0001-0013'],
                'materials' => [
                    ['code' => 'FAR-MAT-PCT', 'name' => 'Paracetamol 500mg (Strip 10 tab)', 'unit_code' => 'pack', 'price' => 4500, 'yield' => 100, 'waste' => 0, 'initial_stock' => 500],
                    ['code' => 'FAR-MAT-VITC', 'name' => 'Vitamin C 500mg Botol isi 30', 'unit_code' => 'pack', 'price' => 38000, 'yield' => 100, 'waste' => 0, 'initial_stock' => 120],
                    ['code' => 'FAR-MAT-MASKER', 'name' => 'Masker Medis 3-Ply Box isi 50', 'unit_code' => 'box', 'price' => 22000, 'yield' => 100, 'waste' => 0, 'initial_stock' => 150],
                ],
                'products' => [
                    [
                        'code' => 'FAR-PROD-PCT',
                        'name' => 'Paracetamol 500mg Kaplet (Strip isi 10)',
                        'output_unit' => 'pack',
                        'selling_price' => 7500,
                        'base_cost' => 4500,
                        'direct_material_code' => 'FAR-MAT-PCT',
                        'costing_method' => CostModel::METHOD_RETAIL,
                    ],
                    [
                        'code' => 'FAR-PROD-VITC',
                        'name' => 'Vitamin C 500mg Botol isi 30 Tab',
                        'output_unit' => 'pack',
                        'selling_price' => 48000,
                        'base_cost' => 38000,
                        'direct_material_code' => 'FAR-MAT-VITC',
                        'costing_method' => CostModel::METHOD_RETAIL,
                    ],
                ],
                'customer' => ['name' => 'Pasien dr. Wijaya', 'phone' => '0812-9988-7711'],
                'sample_sale' => ['type' => 'pos', 'quantity' => 2],
            ],

            // ─────────────────────────────────────────────────────────────
            // 14. Jasa - Digital Creative Agency & IT
            // ─────────────────────────────────────────────────────────────
            [
                'template_code' => 'service_agency',
                'user' => ['name' => 'Rian Pratama', 'email' => 'owner.agency@cooca.id'],
                'business' => [
                    'name' => 'PixelByte Digital Studio',
                    'slug' => 'pixelbyte-digital-studio',
                    'phone' => '0812-1111-0014',
                    'address' => 'Wisma Co-Working Lt. 4, Kuningan, Jakarta Selatan',
                    'rounding_strategy' => Business::ROUNDING_ROUND_1000,
                    'allow_negative_stock' => true,
                ],
                'location_name' => 'Studio & Remote Lab',
                'supplier' => ['name' => 'AWS & Cloud Infrastructure Vendor', 'contact' => 'DevOps Team', 'phone' => '0813-0001-0014'],
                'materials' => [
                    ['code' => 'IT-MAT-SERVER', 'name' => 'Cloud VPS Server 4vCPU 8GB per Bulan', 'unit_code' => 'pcs', 'price' => 650000, 'yield' => 100, 'waste' => 0, 'initial_stock' => 10],
                    ['code' => 'IT-MAT-DOMAIN', 'name' => 'Registrasi Domain .com / .id', 'unit_code' => 'pcs', 'price' => 180000, 'yield' => 100, 'waste' => 0, 'initial_stock' => 20],
                ],
                'products' => [
                    [
                        'code' => 'IT-WEB-COMPANY',
                        'name' => 'Jasa Pembuatan Website Company Profile Premium',
                        'output_unit' => 'proyek',
                        'selling_price' => 12500000,
                        'base_cost' => 3800000,
                        'costing_method' => CostModel::METHOD_SERVICE,
                        'business_type_hint' => 'service',
                    ],
                    [
                        'code' => 'IT-UIUX-DESIGN',
                        'name' => 'Jasa UI/UX Design Figma Mobile App (10 Screen)',
                        'output_unit' => 'proyek',
                        'selling_price' => 7500000,
                        'base_cost' => 2200000,
                        'costing_method' => CostModel::METHOD_SERVICE,
                        'business_type_hint' => 'service',
                    ],
                ],
                'customer' => ['name' => 'PT Surya Mega Solusi', 'phone' => '0811-9876-5432', 'company' => 'PT Surya Mega Solusi'],
                'sample_sale' => ['type' => 'invoice', 'quantity' => 1],
            ],

            // ─────────────────────────────────────────────────────────────
            // 15. Jasa - Bengkel Mobil & Motor
            // ─────────────────────────────────────────────────────────────
            [
                'template_code' => 'service_workshop',
                'user' => ['name' => 'Asep Sunandar', 'email' => 'owner.workshop@cooca.id'],
                'business' => [
                    'name' => 'Garasi Motor Jaya Sentosa',
                    'slug' => 'garasi-motor-jaya',
                    'phone' => '0812-1111-0015',
                    'address' => 'Jl. Soekarno Hatta No. 230, Bandung',
                    'rounding_strategy' => Business::ROUNDING_ROUND_500,
                    'allow_negative_stock' => false,
                ],
                'location_name' => 'Pit Service & Sparepart',
                'supplier' => ['name' => 'Astra Otoparts Distributor', 'contact' => 'Pak Dedi', 'phone' => '0813-0001-0015'],
                'materials' => [
                    ['code' => 'WS-MAT-OLI', 'name' => 'Oli Mesin Matic 10W-40 0.8L', 'unit_code' => 'pcs', 'price' => 45000, 'yield' => 100, 'waste' => 0, 'initial_stock' => 100],
                    ['code' => 'WS-MAT-KAMPAS', 'name' => 'Kampas Rem Cakram Depan Original', 'unit_code' => 'pcs', 'price' => 38000, 'yield' => 100, 'waste' => 0, 'initial_stock' => 40],
                    ['code' => 'WS-MAT-BUSI', 'name' => 'Busi Standard Iridium', 'unit_code' => 'pcs', 'price' => 22000, 'yield' => 100, 'waste' => 0, 'initial_stock' => 60],
                ],
                'products' => [
                    [
                        'code' => 'WS-PAKET-SERVIS',
                        'name' => 'Paket Servis Ringan + Ganti Oli Matic',
                        'output_unit' => 'pcs',
                        'selling_price' => 85000,
                        'costing_method' => CostModel::METHOD_JOB,
                        'base_cost' => 52000,
                        'recipe_items' => [
                            ['material_code' => 'WS-MAT-OLI', 'quantity' => 1, 'unit_code' => 'pcs', 'waste_pct' => 0],
                        ],
                    ],
                ],
                'customer' => ['name' => 'Mas Doni (Honda Vario 160)', 'phone' => '0812-3344-9988'],
                'sample_sale' => ['type' => 'pos', 'quantity' => 1],
            ],

            // ─────────────────────────────────────────────────────────────
            // 16. Jasa - Barbershop & Salon Kecantikan
            // ─────────────────────────────────────────────────────────────
            [
                'template_code' => 'service_barbershop',
                'user' => ['name' => 'Ilham Ramadhan', 'email' => 'owner.barbershop@cooca.id'],
                'business' => [
                    'name' => 'Gentlemen Cuts Barbershop',
                    'slug' => 'gentlemen-cuts-barbershop',
                    'phone' => '0812-1111-0016',
                    'address' => 'Jl. Margonda Raya No. 412, Depok',
                    'rounding_strategy' => Business::ROUNDING_ROUND_100,
                    'allow_negative_stock' => false,
                ],
                'location_name' => 'Barber Lounge Margonda',
                'supplier' => ['name' => 'Grosir Kosmetik & Perlengkapan Salon', 'contact' => 'Mbak Maya', 'phone' => '0813-0001-0016'],
                'materials' => [
                    ['code' => 'BRB-MAT-POMADE', 'name' => 'Pomade Waterbased Clay 100g', 'unit_code' => 'g', 'price' => 650, 'yield' => 100, 'waste' => 2, 'initial_stock' => 3000],
                    ['code' => 'BRB-MAT-SHAMPOO', 'name' => 'Shampoo Cooling Menthol Salon Galon', 'unit_code' => 'ml', 'price' => 35, 'yield' => 98, 'waste' => 2, 'initial_stock' => 10000],
                    ['code' => 'BRB-MAT-NECKPAPER', 'name' => 'Kertas Leher (Neck Paper Roll)', 'unit_code' => 'pcs', 'price' => 250, 'yield' => 100, 'waste' => 0, 'initial_stock' => 1000],
                ],
                'products' => [
                    [
                        'code' => 'BRB-GENTLEMAN-CUT',
                        'name' => 'Gentleman Haircut + Wash + Head Massage + Styling',
                        'output_unit' => 'pcs',
                        'selling_price' => 50000,
                        'costing_method' => CostModel::METHOD_SERVICE,
                        'base_cost' => 18000,
                        'recipe_items' => [
                            ['material_code' => 'BRB-MAT-SHAMPOO', 'quantity' => 25, 'unit_code' => 'ml', 'waste_pct' => 0],
                            ['material_code' => 'BRB-MAT-POMADE', 'quantity' => 10, 'unit_code' => 'g', 'waste_pct' => 0],
                            ['material_code' => 'BRB-MAT-NECKPAPER', 'quantity' => 1, 'unit_code' => 'pcs', 'waste_pct' => 0],
                        ],
                    ],
                ],
                'customer' => ['name' => 'Rizky Fadilah', 'phone' => '0813-9988-1122'],
                'sample_sale' => ['type' => 'pos', 'quantity' => 2],
            ],

            // ─────────────────────────────────────────────────────────────
            // 17. Jasa - Laundry Kiloan & Satuan
            // ─────────────────────────────────────────────────────────────
            [
                'template_code' => 'service_laundry',
                'user' => ['name' => 'Rina Wulandari', 'email' => 'owner.laundry@cooca.id'],
                'business' => [
                    'name' => 'Kilau Bersih Kiloan & Satuan',
                    'slug' => 'kilau-bersih-laundry',
                    'phone' => '0812-1111-0017',
                    'address' => 'Jl. Kaliurang KM 5.5 No. 18, Sleman, Yogyakarta',
                    'rounding_strategy' => Business::ROUNDING_ROUND_100,
                    'allow_negative_stock' => false,
                ],
                'location_name' => 'Outlet Cuci & Setrika Uap',
                'supplier' => ['name' => 'Pabrik Kimia Laundry Bersih Jaya', 'contact' => 'Bpk. Taufik', 'phone' => '0813-0001-0017'],
                'materials' => [
                    ['code' => 'LND-MAT-DETERGEN', 'name' => 'Detergen Cair Konsentrat 5 Liter', 'unit_code' => 'ml', 'price' => 15, 'yield' => 99, 'waste' => 1, 'initial_stock' => 50000],
                    ['code' => 'LND-MAT-PARFUM', 'name' => 'Parfum Laundry Premium Lily', 'unit_code' => 'ml', 'price' => 35, 'yield' => 100, 'waste' => 0, 'initial_stock' => 20000],
                    ['code' => 'LND-MAT-PLASTIK', 'name' => 'Plastik Jinjing Laundry Roll Kiloan', 'unit_code' => 'pcs', 'price' => 450, 'yield' => 100, 'waste' => 1, 'initial_stock' => 1000],
                ],
                'products' => [
                    [
                        'code' => 'LND-CUCI-SETRIKA',
                        'name' => 'Cuci + Kering + Setrika Uap Wangi (per Kg)',
                        'output_unit' => 'kg',
                        'selling_price' => 8000,
                        'costing_method' => CostModel::METHOD_PER_UNIT,
                        'recipe_items' => [
                            ['material_code' => 'LND-MAT-DETERGEN', 'quantity' => 35, 'unit_code' => 'ml', 'waste_pct' => 1],
                            ['material_code' => 'LND-MAT-PARFUM', 'quantity' => 15, 'unit_code' => 'ml', 'waste_pct' => 0],
                            ['material_code' => 'LND-MAT-PLASTIK', 'quantity' => 1, 'unit_code' => 'pcs', 'waste_pct' => 0],
                        ],
                    ],
                ],
                'customer' => ['name' => 'Mbak Anisa (Kost Putri)', 'phone' => '0812-5544-3322'],
                'sample_sale' => ['type' => 'pos', 'quantity' => 7],
            ],

            // ─────────────────────────────────────────────────────────────
            // 18. Jasa - Kontraktor & Renovasi Bangunan
            // ─────────────────────────────────────────────────────────────
            [
                'template_code' => 'service_contractor',
                'user' => ['name' => 'Ir. Dedi Suryadi', 'email' => 'owner.contractor@cooca.id'],
                'business' => [
                    'name' => 'Karya Megah Bangunan',
                    'slug' => 'karya-megah-bangunan',
                    'phone' => '0812-1111-0018',
                    'address' => 'Jl. Gedebage Raya No. 89, Bandung',
                    'rounding_strategy' => Business::ROUNDING_ROUND_1000,
                    'allow_negative_stock' => false,
                ],
                'location_name' => 'Pangkalan Material & Logistik',
                'supplier' => ['name' => 'Toko Besi & Bahan Bangunan Sentral', 'contact' => 'H. Endang', 'phone' => '0813-0001-0018'],
                'materials' => [
                    ['code' => 'BLD-MAT-SEMEN', 'name' => 'Semen Portland Komposit 40kg', 'unit_code' => 'sak', 'price' => 58000, 'yield' => 100, 'waste' => 2, 'initial_stock' => 200],
                    ['code' => 'BLD-MAT-PASIR', 'name' => 'Pasir Pasang Merapi (m3)', 'unit_code' => 'm', 'price' => 280000, 'yield' => 95, 'waste' => 5, 'initial_stock' => 50],
                    ['code' => 'BLD-MAT-HEBEL', 'name' => 'Bata Ringan Hebel 7.5cm (m3)', 'unit_code' => 'm', 'price' => 620000, 'yield' => 98, 'waste' => 2, 'initial_stock' => 40],
                ],
                'products' => [
                    [
                        'code' => 'BLD-DINDING-HEBEL',
                        'name' => 'Pekerjaan Pasang Dinding Hebel + Plester Aci (per m2)',
                        'output_unit' => 'm2',
                        'selling_price' => 165000,
                        'costing_method' => CostModel::METHOD_JOB,
                        'base_cost' => 98000,
                        'recipe_items' => [
                            ['material_code' => 'BLD-MAT-SEMEN', 'quantity' => 0.25, 'unit_code' => 'sak', 'waste_pct' => 2],
                        ],
                    ],
                ],
                'customer' => ['name' => 'Bpk. Hendrawan (Renovasi Ruko)', 'phone' => '0811-9876-1234'],
                'sample_sale' => ['type' => 'invoice', 'quantity' => 60],
            ],

            // ─────────────────────────────────────────────────────────────
            // 19. Jasa - Event Organizer & Wedding Organizer
            // ─────────────────────────────────────────────────────────────
            [
                'template_code' => 'service_event',
                'user' => ['name' => 'Clarissa Putri', 'email' => 'owner.event@cooca.id'],
                'business' => [
                    'name' => 'Mahligai Indah Wedding & Event',
                    'slug' => 'mahligai-indah-wedding',
                    'phone' => '0812-1111-0019',
                    'address' => 'Ruko Golden Boulevard Blok W-05, BSD City, Tangerang',
                    'rounding_strategy' => Business::ROUNDING_ROUND_1000,
                    'allow_negative_stock' => true,
                ],
                'location_name' => 'Studio Wedding & Galeri Dekor',
                'supplier' => ['name' => 'Pasar Bunga Rawa Belong & Rental Rigging', 'contact' => 'Pak Joko Bunga', 'phone' => '0813-0001-0019'],
                'materials' => [
                    ['code' => 'EVT-MAT-BUNGA', 'name' => 'Bunga Mawar & Lily Fresh Import', 'unit_code' => 'pack', 'price' => 125000, 'yield' => 95, 'waste' => 5, 'initial_stock' => 40],
                    ['code' => 'EVT-MAT-KAIN', 'name' => 'Kain Backdrop Tulle Brokat (m)', 'unit_code' => 'm', 'price' => 45000, 'yield' => 100, 'waste' => 1, 'initial_stock' => 100],
                ],
                'products' => [
                    [
                        'code' => 'EVT-WO-SILVER',
                        'name' => 'Paket Wedding Organizer Silver (All-In D-Day 6 Crew)',
                        'output_unit' => 'proyek',
                        'selling_price' => 16500000,
                        'base_cost' => 6500000,
                        'costing_method' => CostModel::METHOD_JOB,
                        'business_type_hint' => 'service',
                    ],
                ],
                'customer' => ['name' => 'Pasangan Rio & Amanda', 'phone' => '0812-8877-2211', 'company' => 'Pernikahan Rio & Amanda'],
                'sample_sale' => ['type' => 'invoice', 'quantity' => 1],
            ],

            // ─────────────────────────────────────────────────────────────
            // 20. Agribisnis - Peternakan & Pertanian
            // ─────────────────────────────────────────────────────────────
            [
                'template_code' => 'agri_farming',
                'user' => ['name' => 'H. Mulyono', 'email' => 'owner.farming@cooca.id'],
                'business' => [
                    'name' => 'Nusantara Farm & Peternakan',
                    'slug' => 'nusantara-farm-peternakan',
                    'phone' => '0812-1111-0020',
                    'address' => 'Desa Cikarageman, Setu, Bekasi',
                    'rounding_strategy' => Business::ROUNDING_ROUND_500,
                    'allow_negative_stock' => false,
                ],
                'location_name' => 'Kandang Utama & Silo Pakan',
                'supplier' => ['name' => 'Charoen Pokphand Pakan Ternak', 'contact' => 'Bpk. Hartono', 'phone' => '0813-0001-0020'],
                'materials' => [
                    ['code' => 'AGR-MAT-DOC', 'name' => 'Bibit Anak Ayam (DOC) Broiler Grade A', 'unit_code' => 'pcs', 'price' => 7500, 'yield' => 96, 'waste' => 4, 'initial_stock' => 2000],
                    ['code' => 'AGR-MAT-PAKAN', 'name' => 'Pakan Konsentrat Broiler BR-1 Karung 50kg', 'unit_code' => 'sak', 'price' => 485000, 'yield' => 100, 'waste' => 1, 'initial_stock' => 80],
                    ['code' => 'AGR-MAT-VAKSIN', 'name' => 'Vaksin & Vitamin Unggas Komplit', 'unit_code' => 'pack', 'price' => 95000, 'yield' => 100, 'waste' => 0, 'initial_stock' => 30],
                ],
                'products' => [
                    [
                        'code' => 'AGR-AYAM-PANEN',
                        'name' => 'Ayam Broiler Hidup Siap Potong (Bobot 1.8 - 2.0 kg)',
                        'output_unit' => 'kg',
                        'selling_price' => 24000,
                        'costing_method' => CostModel::METHOD_PROCESS,
                        'base_cost' => 17500,
                        'recipe_items' => [
                            ['material_code' => 'AGR-MAT-DOC', 'quantity' => 0.55, 'unit_code' => 'pcs', 'waste_pct' => 4],
                            ['material_code' => 'AGR-MAT-PAKAN', 'quantity' => 0.035, 'unit_code' => 'sak', 'waste_pct' => 1],
                        ],
                    ],
                ],
                'customer' => ['name' => 'RPH Rumah Potong Ayam Mitra', 'phone' => '0813-1122-3344', 'company' => 'CV Mitra Daging Unggas'],
                'sample_sale' => ['type' => 'invoice', 'quantity' => 500],
            ],
        ];
    }
}
