<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Template\BusinessTemplateService;
use App\Models\Business;
use App\Models\BusinessLandingPage;
use App\Models\BusinessTypeTemplate;
use App\Models\CommerceOrder;
use App\Models\CommerceOrderBatch;
use App\Models\CommerceOrderItem;
use App\Models\CommercePaymentMethod;
use App\Models\CommercePaymentProof;
use App\Models\CommerceReservation;
use App\Models\CommerceShippingRule;
use App\Models\CommerceStoreSetting;
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
use App\Models\PosTable;
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

        $this->seedDemoCustomerAccount();

        $this->command?->info('✓ Sukses seeding 20 akun bisnis owner & akun demo customer portal!');
    }

    /**
     * Seed a single complete business showcase.
     *
     * @param array<string, mixed> $spec
     */
    private function seedSingleIndustry(array $spec): void
    {
        DB::transaction(function () use ($spec) {
            // 1. User Owner Industri
            $user = User::updateOrCreate(
                ['email' => $spec['user']['email']],
                [
                    'name' => $spec['user']['name'],
                    'phone' => $spec['business']['phone'] ?? '081234567890',
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                    'onboarding_completed' => true,
                    'onboarding_completed_at' => now(),
                    'onboarding_current_step' => 0,
                    'onboarding_version' => 1,
                ]
            );

            // 1b. Akun Testing Utama (Bisa Login & Switch ke SEMUA 20 Industri)
            $superTestingAccounts = [
                ['email' => 'testing@cooca.id', 'name' => 'Testing User (Semua Industri)', 'phone' => '081234567890'],
                ['email' => 'demo@cooca.id', 'name' => 'Demo User (Semua Industri)', 'phone' => '081234567891'],
            ];

            $testingUserModels = [];
            foreach ($superTestingAccounts as $acc) {
                $testingUserModels[] = User::updateOrCreate(
                    ['email' => $acc['email']],
                    [
                        'name' => $acc['name'],
                        'phone' => $acc['phone'],
                        'password' => Hash::make('password123'),
                        'email_verified_at' => now(),
                        'onboarding_completed' => true,
                        'onboarding_completed_at' => now(),
                        'onboarding_current_step' => 0,
                        'onboarding_version' => 1,
                    ]
                );
            }

            // 2. Business
            $bizData = $spec['business'];
            $template = BusinessTypeTemplate::where('code', $spec['template_code'])->first();
            $disabledModules = $template ? \App\Domain\Template\ModuleRegistry::getDisabledModulesForTemplate($template->code) : [];

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
                    'template_code' => $spec['template_code'],
                    'industry_category' => $template?->industry_category ?? explode('_', $spec['template_code'])[0],
                    'disabled_modules' => $disabledModules,
                    'is_active' => true,
                ]
            );

            // Hubungkan owner spesifik industri
            if (! $business->users()->where('users.id', $user->id)->exists()) {
                $business->users()->attach($user->id, [
                    'id' => (string) Str::uuid(),
                    'role' => 'owner',
                ]);
            }
            $user->update(['active_business_id' => $business->id]);

            // Hubungkan Akun Testing Utama sebagai owner ke SETIAP bisnis industri
            foreach ($testingUserModels as $tUser) {
                if (! $business->users()->where('users.id', $tUser->id)->exists()) {
                    $business->users()->attach($tUser->id, [
                        'id' => (string) Str::uuid(),
                        'role' => 'owner',
                    ]);
                }
                if (! $tUser->active_business_id) {
                    $tUser->update(['active_business_id' => $business->id]);
                }
            }

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

            // 10. Omnichannel Storefront, Rules & Orders
            $this->seedCommerceDataForIndustry($business, $location, $productMap, $customer, $spec['template_code']);
        });
    }

    /**
     * Seed complete Omnichannel Storefront & Commerce data for this industry showcase.
     *
     * @param array<string, Product> $productMap
     */
    private function seedCommerceDataForIndustry(
        Business $business,
        Location $location,
        array $productMap,
        ?Customer $customer,
        string $templateCode
    ): void {
        // 0. Ensure all materials have stock for smooth checkout
        $materials = \App\Models\Material::where('business_id', $business->id)->get();
        foreach ($materials as $mat) {
            InventoryStock::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'material_id' => $mat->id,
                ],
                [
                    'product_id' => null,
                    'quantity' => 150.0,
                    'reserved_quantity' => 0.0,
                    'last_cost' => (float) ($mat->buy_price ?? 10000),
                    'avg_purchase_cost' => (float) ($mat->buy_price ?? 10000),
                ]
            );
        }

        // 0. Resolve Industry Blueprint
        $blueprint = $this->getIndustryBlueprint($templateCode, $business);
        $landingSpec = $blueprint['landing'];
        $storeSpec = $blueprint['store'];

        $rawHours = $landingSpec['operational_hours'] ?? [];
        $normalizedHours = [];
        $dayTranslations = [
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
            'sunday' => 'Minggu',
        ];
        foreach ($rawHours as $k => $item) {
            if (isset($item['day'])) {
                $normalizedHours[] = $item;
            } elseif (is_array($item)) {
                $dayKey = is_string($k) ? strtolower($k) : '';
                $dayName = $dayTranslations[$dayKey] ?? (is_string($k) ? ucfirst($k) : 'Hari');
                $isOpen = !empty($item['is_open']) || (!empty($item['open']) && $item['open'] !== 'closed');
                $openTime = $item['open'] ?? '';
                $closeTime = $item['close'] ?? '';
                $hoursText = $isOpen && $openTime && $closeTime ? "{$openTime} - {$closeTime} WIB" : ($isOpen ? 'Buka' : 'Tutup');
                $normalizedHours[] = [
                    'day' => $dayName,
                    'hours' => $hoursText,
                    'is_open' => $isOpen,
                ];
            }
        }

        // 0.1 Business Landing Page (Tailored per Industry Archetype)
        BusinessLandingPage::updateOrCreate(
            ['business_id' => $business->id],
            [
                'headline' => $landingSpec['headline'],
                'subheadline' => $landingSpec['subheadline'],
                'is_published' => true,
                'show_pos_products' => true,
                'theme_color' => $landingSpec['theme_color'],
                'cta_primary_text' => $landingSpec['cta_primary_text'],
                'cta_secondary_text' => $landingSpec['cta_secondary_text'],
                'announcement_badge' => $landingSpec['announcement_badge'],
                'services_title' => $landingSpec['services_title'],
                'about_title' => $landingSpec['about_title'],
                'about_story' => $landingSpec['about_story'],
                'operational_hours' => $normalizedHours,
                'whatsapp_number' => $business->phone ?: '081234567890',
                'whatsapp_welcome_message' => $landingSpec['whatsapp_welcome_message'],
            ]
        );

        // 1. Storefront Settings (Strictly configured per industry capabilities)
        CommerceStoreSetting::updateOrCreate(
            ['business_id' => $business->id],
            [
                'is_storefront_enabled' => true,
                'is_discoverable' => true,
                'allow_pickup' => $storeSpec['allow_pickup'],
                'allow_delivery' => $storeSpec['allow_delivery'],
                'allow_request_order' => $storeSpec['allow_request_order'],
                'allow_scheduled_order' => $storeSpec['allow_scheduled_order'],
                'allow_customer_po' => $storeSpec['allow_customer_po'],
                'allow_reservation' => $storeSpec['allow_reservation'],
                'min_order_amount' => $storeSpec['min_order_amount'],
                'lead_time_hours' => $storeSpec['lead_time_hours'],
                'operating_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
                'available_slots' => $storeSpec['available_slots'],
                'cut_off_time' => '17:00',
                'max_capacity_per_slot' => 15,
                'daily_order_quota' => 50,
                'announcement_text' => $storeSpec['announcement_text'],
                'order_notes_placeholder' => $storeSpec['order_notes_placeholder'],
            ]
        );

        // 2. Payment Methods (Bank Transfer & QRIS)
        CommercePaymentMethod::updateOrCreate(
            ['business_id' => $business->id, 'type' => CommercePaymentMethod::TYPE_BANK_TRANSFER, 'bank_name' => 'BCA'],
            [
                'account_holder' => $business->name,
                'account_number' => $business->bank_account_number ?: '8800112233',
                'instructions' => 'Transfer tepat sesuai nominal tagihan. Bukti transfer diverifikasi otomatis oleh tim merchant.',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        CommercePaymentMethod::updateOrCreate(
            ['business_id' => $business->id, 'type' => CommercePaymentMethod::TYPE_QRIS],
            [
                'bank_name' => 'QRIS Cooca Pay',
                'account_holder' => $business->name,
                'account_number' => 'NMID-ID102030405060',
                'instructions' => 'Scan QRIS menggunakan aplikasi perbankan atau e-wallet (BCA, Mandiri, GoPay, OVO, ShopeePay, DANA).',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        // 3. Shipping Rules (Tailored to Industry Fulfillment Model)
        CommerceShippingRule::where('business_id', $business->id)->delete();
        if ($blueprint['shipping'] === 'service') {
            CommerceShippingRule::create([
                'business_id' => $business->id,
                'name' => 'Layanan di Lokasi / Datang Langsung',
                'rule_type' => CommerceShippingRule::TYPE_FLAT,
                'rate_amount' => 0,
                'min_order_for_free' => null,
                'is_active' => true,
                'sort_order' => 1,
            ]);
        } elseif ($blueprint['shipping'] === 'b2b') {
            CommerceShippingRule::create([
                'business_id' => $business->id,
                'name' => 'Armada Truk / Ekspedisi Kargo',
                'rule_type' => CommerceShippingRule::TYPE_FLAT,
                'rate_amount' => 75000,
                'min_order_for_free' => 2000000,
                'is_active' => true,
                'sort_order' => 1,
            ]);
            CommerceShippingRule::create([
                'business_id' => $business->id,
                'name' => 'Bebas Ongkir Kargo (PO > Rp 2.000.000)',
                'rule_type' => CommerceShippingRule::TYPE_FREE_THRESHOLD,
                'rate_amount' => 0,
                'min_order_for_free' => 2000000,
                'is_active' => true,
                'sort_order' => 2,
            ]);
        } else {
            CommerceShippingRule::create([
                'business_id' => $business->id,
                'name' => 'Kurir Toko / Pengantaran Langsung',
                'rule_type' => CommerceShippingRule::TYPE_FLAT,
                'rate_amount' => 15000,
                'min_order_for_free' => 150000,
                'is_active' => true,
                'sort_order' => 1,
            ]);
            CommerceShippingRule::create([
                'business_id' => $business->id,
                'name' => 'Gratis Ongkir (Belanja > Rp 150.000)',
                'rule_type' => CommerceShippingRule::TYPE_FREE_THRESHOLD,
                'rate_amount' => 0,
                'min_order_for_free' => 150000,
                'is_active' => true,
                'sort_order' => 2,
            ]);
        }

        // 4. POS Tables (strictly for Reservable businesses)
        $tableList = [];
        if (! empty($blueprint['tables']['supported'])) {
            $prefix = $blueprint['tables']['prefix'] ?? 'Meja #';
            $count = $blueprint['tables']['count'] ?? 5;
            for ($t = 1; $t <= $count; $t++) {
                $posTable = PosTable::firstOrCreate(
                    ['business_id' => $business->id, 'table_number' => 'T-0' . $t],
                    [
                        'name' => $prefix . $t,
                        'capacity' => $t * 2,
                        'status' => 'available',
                        'location_id' => $location->id,
                        'is_active' => true,
                    ]
                );
                $tableList[] = $posTable;
            }
        } else {
            // Clean up any old tables or reservations for non-reservable industries
            PosTable::where('business_id', $business->id)->delete();
            CommerceReservation::where('business_id', $business->id)->delete();
        }

        // 5. Sample Commerce Orders
        if (! empty($productMap)) {
            $firstProduct = reset($productMap);
            $orderQty = 2;
            $subtotal = (float) $firstProduct->selling_price * $orderQty;
            $shippingCost = $blueprint['shipping'] === 'service' ? 0 : 15000;
            $totalAmount = $subtotal + $shippingCost;

            if ($blueprint['orders']['seed_direct'] ?? true) {
                // Direct Checkout Order (Completed & Paid)
                $directOrder = CommerceOrder::firstOrCreate(
                    ['business_id' => $business->id, 'order_number' => 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(4))],
                    [
                        'location_id' => $location->id,
                        'customer_id' => $customer?->id,
                        'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
                        'status' => CommerceOrder::STATUS_COMPLETED,
                        'payment_status' => CommerceOrder::PAYMENT_PAID,
                        'fulfillment_type' => $blueprint['shipping'] === 'service' ? CommerceOrder::FULFILLMENT_PICKUP : CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY,
                        'customer_name' => $customer?->name ?? 'Pelanggan Online',
                        'customer_phone' => $customer?->phone ?? '081299887766',
                        'shipping_address' => $blueprint['shipping'] === 'service' ? 'Layanan di Outlet' : 'Jl. Anggrek No. 12, Kompleks Perumahan Sejahtera',
                        'subtotal' => $subtotal,
                        'shipping_cost' => $shippingCost,
                        'total_amount' => $totalAmount,
                        'tracking_token' => Str::random(64),
                        'notes' => 'Pesanan Direct Checkout showcase ' . $business->name,
                        'created_at' => Carbon::now()->subDays(1),
                    ]
                );

                CommerceOrderItem::firstOrCreate(
                    ['commerce_order_id' => $directOrder->id, 'product_id' => $firstProduct->id],
                    [
                        'product_name' => $firstProduct->name,
                        'product_type' => 'product',
                        'unit_price' => $firstProduct->selling_price,
                        'quantity' => $orderQty,
                        'subtotal' => $subtotal,
                    ]
                );
            }

            if ($blueprint['orders']['seed_po'] ?? false) {
                // B2B Customer PO Order with Multi-Drop Batches
                $poOrder = CommerceOrder::firstOrCreate(
                    ['business_id' => $business->id, 'customer_po_number' => 'PO-' . strtoupper(Str::random(6))],
                    [
                        'order_number' => 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                        'location_id' => $location->id,
                        'customer_id' => $customer?->id,
                        'order_type' => CommerceOrder::TYPE_CUSTOMER_PO,
                        'status' => CommerceOrder::STATUS_PROCESSING,
                        'payment_status' => CommerceOrder::PAYMENT_PAID,
                        'fulfillment_type' => CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY,
                        'customer_name' => 'Bpk. Hendra Gunawan',
                        'customer_phone' => '081233445566',
                        'company_name' => 'PT Mitra Niaga Gemilang',
                        'shipping_address' => 'Gudang Logistik Pusat, Jl. Daan Mogot KM 14, Jakarta Barat',
                        'subtotal' => $subtotal * 5,
                        'shipping_cost' => 0,
                        'total_amount' => $subtotal * 5,
                        'tracking_token' => Str::random(64),
                        'notes' => 'Customer PO B2B dengan pengiriman terjadwal multi-drop',
                    ]
                );

                CommerceOrderItem::firstOrCreate(
                    ['commerce_order_id' => $poOrder->id, 'product_id' => $firstProduct->id],
                    [
                        'product_name' => $firstProduct->name,
                        'product_type' => 'product',
                        'unit_price' => $firstProduct->selling_price,
                        'quantity' => $orderQty * 5,
                        'subtotal' => $subtotal * 5,
                    ]
                );

                // Batch Drops
                CommerceOrderBatch::firstOrCreate(
                    ['commerce_order_id' => $poOrder->id, 'batch_number' => 1],
                    [
                        'batch_code' => 'BATCH-01',
                        'scheduled_date' => Carbon::today()->toDateString(),
                        'status' => CommerceOrderBatch::STATUS_SHIPPED,
                        'shipping_address' => 'Gudang Logistik Pusat, Jl. Daan Mogot KM 14',
                        'quantity' => 4,
                        'tracking_number' => 'RESI-LOKAL-001',
                    ]
                );

                CommerceOrderBatch::firstOrCreate(
                    ['commerce_order_id' => $poOrder->id, 'batch_number' => 2],
                    [
                        'batch_code' => 'BATCH-02',
                        'scheduled_date' => Carbon::today()->addDays(5)->toDateString(),
                        'status' => CommerceOrderBatch::STATUS_SCHEDULED,
                        'shipping_address' => 'Ruko BSD Boulevard No. 8, Serpong',
                        'quantity' => 6,
                    ]
                );
            }
        }

        // 6. Sample Reservation (for F&B / Services with tables)
        if (! empty($tableList) && ($blueprint['store']['allow_reservation'] ?? false)) {
            $firstTable = $tableList[0];
            $resSlot = $blueprint['store']['available_slots'][0] ?? '19:00 - 21:00';
            CommerceReservation::firstOrCreate(
                ['business_id' => $business->id, 'reservation_code' => 'RSV-' . strtoupper(Str::random(6))],
                [
                    'pos_table_id' => $firstTable->id,
                    'customer_name' => 'Ibu Dian Safitri',
                    'customer_phone' => '081377889900',
                    'customer_email' => 'dian.safitri@example.com',
                    'reservation_date' => Carbon::today()->toDateString(),
                    'time_slot' => $resSlot,
                    'guest_count' => 4,
                    'status' => CommerceReservation::STATUS_CONFIRMED,
                    'notes' => 'Reservasi jadwal showcase untuk ' . $business->name,
                ]
            );
        }
    }

    /**
     * Get tailored industry blueprint for landing page, storefront settings, shipping, and tables.
     *
     * @return array<string, mixed>
     */
    private function getIndustryBlueprint(string $templateCode, Business $business): array
    {
        $blueprints = [
            'fnb_resto' => [
                'landing' => [
                    'headline' => 'Restoran Keluarga Cita Rasa Nusantara',
                    'subheadline' => 'Menyajikan hidangan tradisional otentik dengan bahan segar pilihan, ruang makan nyaman, dan pelayanan ramah keluarga.',
                    'theme_color' => '#D97706',
                    'cta_primary_text' => 'Reservasi',
                    'cta_secondary_text' => 'Menu',
                    'announcement_badge' => 'Dine-in & Reservasi Meja',
                    'whatsapp_welcome_message' => 'Halo Restoran ' . $business->name . ', saya ingin reservasi meja atau memesan hidangan keluarga.',
                    'services_title' => 'Menu Andalan & Paket Keluarga',
                    'about_title' => 'Cita Rasa Resep Warisan Nusantara',
                    'about_story' => 'Didirikan dengan komitmen menyajikan hidangan nusantara otentik tanpa kompromi rasa. Semua bumbu diolah dari rempah lokal berkualitas terbaik.',
                    'operational_hours' => [
                        'monday' => ['open' => '10:00', 'close' => '22:00'],
                        'tuesday' => ['open' => '10:00', 'close' => '22:00'],
                        'wednesday' => ['open' => '10:00', 'close' => '22:00'],
                        'thursday' => ['open' => '10:00', 'close' => '22:00'],
                        'friday' => ['open' => '10:00', 'close' => '22:00'],
                        'saturday' => ['open' => '09:00', 'close' => '22:30'],
                        'sunday' => ['open' => '09:00', 'close' => '22:30'],
                    ],
                ],
                'store' => [
                    'allow_pickup' => true,
                    'allow_delivery' => true,
                    'allow_request_order' => false,
                    'allow_scheduled_order' => true,
                    'allow_customer_po' => false,
                    'allow_reservation' => true,
                    'min_order_amount' => 25000,
                    'lead_time_hours' => 1,
                    'available_slots' => ['11:00 - 13:00', '13:00 - 15:00', '17:00 - 19:00', '19:00 - 21:00'],
                    'announcement_text' => 'Selamat datang di ' . $business->name . '. Melayani reservasi meja keluarga, ruang VIP & takeaway.',
                    'order_notes_placeholder' => 'Contoh: tingkat kepedasan sedang, pisahkan kuah, dll.',
                ],
                'tables' => ['supported' => true, 'prefix' => 'Meja #', 'count' => 5],
                'shipping' => 'fnb',
                'orders' => ['seed_direct' => true, 'seed_po' => false],
            ],

            'fnb_cafe' => [
                'landing' => [
                    'headline' => 'Specialty Coffee & Artisan Pastry',
                    'subheadline' => 'Ruang santai & co-working ramah dengan seduhan biji kopi Nusantara pilihan, koneksi cepat, dan camilan artisanal.',
                    'theme_color' => '#B45309',
                    'cta_primary_text' => 'Pesan',
                    'cta_secondary_text' => 'Reservasi',
                    'announcement_badge' => 'Fresh Roasted Beans & Cozy Space',
                    'whatsapp_welcome_message' => 'Halo ' . $business->name . ', saya ingin bertanya seputar menu kopi atau reservasi spot kerja.',
                    'services_title' => 'Signature Coffee & Bites',
                    'about_title' => 'Komitmen pada Setiap Tetes Kopi',
                    'about_story' => 'Kami bekerja langsung dengan petani kopi lokal dari Gayo, Kintamani, hingga Toraja untuk memastikan kualitas seduhan terbaik setiap hari.',
                    'operational_hours' => [
                        'monday' => ['open' => '08:00', 'close' => '22:00'],
                        'tuesday' => ['open' => '08:00', 'close' => '22:00'],
                        'wednesday' => ['open' => '08:00', 'close' => '22:00'],
                        'thursday' => ['open' => '08:00', 'close' => '22:00'],
                        'friday' => ['open' => '08:00', 'close' => '23:00'],
                        'saturday' => ['open' => '08:00', 'close' => '23:00'],
                        'sunday' => ['open' => '08:00', 'close' => '22:00'],
                    ],
                ],
                'store' => [
                    'allow_pickup' => true,
                    'allow_delivery' => true,
                    'allow_request_order' => false,
                    'allow_scheduled_order' => true,
                    'allow_customer_po' => false,
                    'allow_reservation' => true,
                    'min_order_amount' => 15000,
                    'lead_time_hours' => 1,
                    'available_slots' => ['09:00 - 12:00', '13:00 - 16:00', '16:00 - 19:00', '19:00 - 22:00'],
                    'announcement_text' => 'Biji kopi fresh roast hari ini siap diseduh. Booking meeting table atau pesan pickup praktis.',
                    'order_notes_placeholder' => 'Contoh: less sugar, oatmilk, es dipisah...',
                ],
                'tables' => ['supported' => true, 'prefix' => 'Meja #', 'count' => 5],
                'shipping' => 'fnb',
                'orders' => ['seed_direct' => true, 'seed_po' => false],
            ],

            'fnb_bakery' => [
                'landing' => [
                    'headline' => 'Roti, Kue & Pastry Hangat Setiap Pagi',
                    'subheadline' => 'Dibuat setiap hari dari mentega murni dan tepung premium tanpa bahan pengawet. Tersedia pesanan kustom snack box & kue ulang tahun.',
                    'theme_color' => '#EA580C',
                    'cta_primary_text' => 'Pesan',
                    'cta_secondary_text' => 'Custom Cake',
                    'announcement_badge' => 'Fresh From The Oven',
                    'whatsapp_welcome_message' => 'Halo ' . $business->name . ', saya ingin memesan kue ulang tahun kustom / snack box acara.',
                    'services_title' => 'Pilihan Roti & Pastry Favorit',
                    'about_title' => 'Kelezatan Roti Tanpa Kompromi',
                    'about_story' => 'Resep keluarga turun temurun yang dikombinasikan dengan teknik pastry modern untuk menghasilkan kelembutan sempurna.',
                    'operational_hours' => [
                        'monday' => ['open' => '06:30', 'close' => '21:00'],
                        'tuesday' => ['open' => '06:30', 'close' => '21:00'],
                        'wednesday' => ['open' => '06:30', 'close' => '21:00'],
                        'thursday' => ['open' => '06:30', 'close' => '21:00'],
                        'friday' => ['open' => '06:30', 'close' => '21:00'],
                        'saturday' => ['open' => '06:30', 'close' => '21:30'],
                        'sunday' => ['open' => '06:30', 'close' => '21:30'],
                    ],
                ],
                'store' => [
                    'allow_pickup' => true,
                    'allow_delivery' => true,
                    'allow_request_order' => true,
                    'allow_scheduled_order' => true,
                    'allow_customer_po' => true,
                    'allow_reservation' => false,
                    'min_order_amount' => 30000,
                    'lead_time_hours' => 6,
                    'available_slots' => ['Pagi (07:00 - 10:00)', 'Siang (11:00 - 14:00)', 'Sore (15:00 - 18:00)'],
                    'announcement_text' => 'Roti & pastry segar siap saji. Menerima pesanan kustom cake & snack box untuk kantor/keluarga.',
                    'order_notes_placeholder' => 'Contoh: tulisan ucapan pada kue, pilihan warna pita kemasan...',
                ],
                'tables' => ['supported' => false],
                'shipping' => 'standard',
                'orders' => ['seed_direct' => true, 'seed_po' => true],
            ],

            'fnb_katering' => [
                'landing' => [
                    'headline' => 'Solusi Katering Prasmanan, Nasi Box & Event',
                    'subheadline' => 'Menyajikan ribuan porsi hidangan lezat dan higienis bersertifikasi halal untuk pernikahan, seminar kantor, dan konsumsi harian.',
                    'theme_color' => '#16A34A',
                    'cta_primary_text' => 'Pesan',
                    'cta_secondary_text' => 'Request Menu',
                    'announcement_badge' => 'Katering Bergaransi Rasa & Higienis',
                    'whatsapp_welcome_message' => 'Halo ' . $business->name . ', saya ingin konsultasi paket katering dan penawaran harga untuk acara.',
                    'services_title' => 'Paket Katering & Prasmanan',
                    'about_title' => 'Mitra Terpercaya Setiap Acara Istimewa',
                    'about_story' => 'Berpengalaman lebih dari 8 tahun melayani katering korporat dan pesta pernikahan dengan standar sanitasi ketat dan ketepatan waktu.',
                    'operational_hours' => [
                        'monday' => ['open' => '07:00', 'close' => '20:00'],
                        'tuesday' => ['open' => '07:00', 'close' => '20:00'],
                        'wednesday' => ['open' => '07:00', 'close' => '20:00'],
                        'thursday' => ['open' => '07:00', 'close' => '20:00'],
                        'friday' => ['open' => '07:00', 'close' => '20:00'],
                        'saturday' => ['open' => '07:00', 'close' => '20:00'],
                        'sunday' => ['open' => '07:00', 'close' => '20:00'],
                    ],
                ],
                'store' => [
                    'allow_pickup' => true,
                    'allow_delivery' => true,
                    'allow_request_order' => true,
                    'allow_scheduled_order' => true,
                    'allow_customer_po' => true,
                    'allow_reservation' => false,
                    'min_order_amount' => 150000,
                    'lead_time_hours' => 24,
                    'available_slots' => ['Makan Pagi (06:30 - 08:30)', 'Makan Siang (11:00 - 13:00)', 'Makan Malam (17:30 - 19:30)'],
                    'announcement_text' => 'Katering prasmanan, bento box & konsumsi event. Booking tanggal acara Anda minimal H-3.',
                    'order_notes_placeholder' => 'Contoh: pantangan alergi, permintaan jenis wadah ramah lingkungan...',
                ],
                'tables' => ['supported' => false],
                'shipping' => 'standard',
                'orders' => ['seed_direct' => true, 'seed_po' => true],
            ],

            'fnb_cloud_kitchen' => [
                'landing' => [
                    'headline' => 'Menu Multi-Brand Siap Antar Cepat',
                    'subheadline' => 'Dapur modern terpusat menyajikan berbagai sajian favorit dengan kualitas terjamin, kemasan higienis, dan pengantaran kilat.',
                    'theme_color' => '#DC2626',
                    'cta_primary_text' => 'Pesan',
                    'cta_secondary_text' => 'WA',
                    'announcement_badge' => 'Pengantaran Cepat & Higienis',
                    'whatsapp_welcome_message' => 'Halo ' . $business->name . ', saya ingin memesan menu takeaway untuk pengantaran langsung.',
                    'services_title' => 'Menu Populer Siap Antar',
                    'about_title' => 'Efisien, Cepat, dan Lezat',
                    'about_story' => 'Konsep dapur terintegrasi dengan teknologi modern untuk menghadirkan makanan berkualitas dalam waktu pengantaran tercepat.',
                    'operational_hours' => [
                        'monday' => ['open' => '10:00', 'close' => '22:00'],
                        'tuesday' => ['open' => '10:00', 'close' => '22:00'],
                        'wednesday' => ['open' => '10:00', 'close' => '22:00'],
                        'thursday' => ['open' => '10:00', 'close' => '22:00'],
                        'friday' => ['open' => '10:00', 'close' => '23:00'],
                        'saturday' => ['open' => '10:00', 'close' => '23:00'],
                        'sunday' => ['open' => '10:00', 'close' => '22:00'],
                    ],
                ],
                'store' => [
                    'allow_pickup' => true,
                    'allow_delivery' => true,
                    'allow_request_order' => false,
                    'allow_scheduled_order' => true,
                    'allow_customer_po' => false,
                    'allow_reservation' => false,
                    'min_order_amount' => 20000,
                    'lead_time_hours' => 1,
                    'available_slots' => ['Siang (11:00 - 14:00)', 'Malam (17:00 - 21:00)'],
                    'announcement_text' => 'Pesanan siap saji hangat berkualitas resto, kemasan bersegel higienis & cepat sampai.',
                    'order_notes_placeholder' => 'Contoh: sambal dipisah, minta sendok garpu...',
                ],
                'tables' => ['supported' => false],
                'shipping' => 'fnb',
                'orders' => ['seed_direct' => true, 'seed_po' => false],
            ],

            'fnb_frozen_food' => [
                'landing' => [
                    'headline' => 'Stok Lauk Frozen Higienis & Siap Saji',
                    'subheadline' => 'Lauk siap goreng dan siap kukus dengan kemasan kedap udara higienis. Tanpa pengawet buatan, aman dan lezat untuk keluarga.',
                    'theme_color' => '#0284C7',
                    'cta_primary_text' => 'Pesan',
                    'cta_secondary_text' => 'Grosir',
                    'announcement_badge' => 'Beku Higienis & Thermal Packed',
                    'whatsapp_welcome_message' => 'Halo ' . $business->name . ', saya ingin memesan produk frozen food atau info kemitraan reseller.',
                    'services_title' => 'Produk Frozen Unggulan',
                    'about_title' => 'Solusi Praktis Dapur Keluarga Modern',
                    'about_story' => 'Diproses dengan teknik pembekuan cepat (blast freezing) untuk mengunci nutrisi, tekstur, dan rasa alami makanan.',
                    'operational_hours' => [
                        'monday' => ['open' => '08:00', 'close' => '19:00'],
                        'tuesday' => ['open' => '08:00', 'close' => '19:00'],
                        'wednesday' => ['open' => '08:00', 'close' => '19:00'],
                        'thursday' => ['open' => '08:00', 'close' => '19:00'],
                        'friday' => ['open' => '08:00', 'close' => '19:00'],
                        'saturday' => ['open' => '08:00', 'close' => '17:00'],
                        'sunday' => ['open' => '08:00', 'close' => '15:00'],
                    ],
                ],
                'store' => [
                    'allow_pickup' => true,
                    'allow_delivery' => true,
                    'allow_request_order' => true,
                    'allow_scheduled_order' => true,
                    'allow_customer_po' => true,
                    'allow_reservation' => false,
                    'min_order_amount' => 50000,
                    'lead_time_hours' => 2,
                    'available_slots' => ['Sesi Pagi (09:00 - 12:00)', 'Sesi Sore (14:00 - 17:00)'],
                    'announcement_text' => 'Stok lauk praktis keluarga beku higienis. Pengiriman dengan ice gel pack aman sampai tujuan.',
                    'order_notes_placeholder' => 'Contoh: kemas per paket 500g, butuh ice gel tambahan...',
                ],
                'tables' => ['supported' => false],
                'shipping' => 'standard',
                'orders' => ['seed_direct' => true, 'seed_po' => true],
            ],

            'retail_fmcg' => [
                'landing' => [
                    'headline' => 'Pusat Sembako & Kebutuhan Rumah Tangga Lengkap',
                    'subheadline' => 'Belanja sembako dan perlengkapan harian keluarga harga grosir terjangkau. Layanan antar kilat langsung ke pintu rumah Anda.',
                    'theme_color' => '#2563EB',
                    'cta_primary_text' => 'Pesan',
                    'cta_secondary_text' => 'WA',
                    'announcement_badge' => 'Harga Hemat & Stok Selalu Siap',
                    'whatsapp_welcome_message' => 'Halo Toko ' . $business->name . ', saya ingin memesan belanjaan sembako untuk dikirim ke rumah.',
                    'services_title' => 'Produk Sembako & Kebutuhan Pokok',
                    'about_title' => 'Sahabat Belanja Hemat Setiap Hari',
                    'about_story' => 'Menyediakan ribuan item kebutuhan harian rumah tangga dengan jaminan keaslian barang dan timbang ukur presisi.',
                    'operational_hours' => [
                        'monday' => ['open' => '07:00', 'close' => '21:00'],
                        'tuesday' => ['open' => '07:00', 'close' => '21:00'],
                        'wednesday' => ['open' => '07:00', 'close' => '21:00'],
                        'thursday' => ['open' => '07:00', 'close' => '21:00'],
                        'friday' => ['open' => '07:00', 'close' => '21:00'],
                        'saturday' => ['open' => '07:00', 'close' => '21:00'],
                        'sunday' => ['open' => '07:00', 'close' => '21:00'],
                    ],
                ],
                'store' => [
                    'allow_pickup' => true,
                    'allow_delivery' => true,
                    'allow_request_order' => false,
                    'allow_scheduled_order' => true,
                    'allow_customer_po' => false,
                    'allow_reservation' => false,
                    'min_order_amount' => 20000,
                    'lead_time_hours' => 1,
                    'available_slots' => ['Pagi (08:00 - 11:00)', 'Siang (13:00 - 16:00)', 'Sore (16:30 - 19:30)'],
                    'announcement_text' => 'Belanja sembako & kebutuhan dapur praktis tanpa antre. Pengantaran hari yang sama.',
                    'order_notes_placeholder' => 'Contoh: titip pesan ke kurir, taruh di teras bila tidak ada orang...',
                ],
                'tables' => ['supported' => false],
                'shipping' => 'standard',
                'orders' => ['seed_direct' => true, 'seed_po' => false],
            ],

            'retail_fashion' => [
                'landing' => [
                    'headline' => 'Koleksi Fashion Trendy & Busana Elegan',
                    'subheadline' => 'Pilihan pakaian berkualitas tinggi dengan potongan modern, material nyaman bernapas, dan desain eksklusif untuk gaya percaya diri Anda.',
                    'theme_color' => '#4F46E5',
                    'cta_primary_text' => 'Pesan',
                    'cta_secondary_text' => 'Koleksi',
                    'announcement_badge' => 'New Season Arrival',
                    'whatsapp_welcome_message' => 'Halo ' . $business->name . ', saya ingin bertanya ketersediaan ukuran dan warna busana ini.',
                    'services_title' => 'Katalog Busana Terlaris',
                    'about_title' => 'Gaya Elegan Tanpa Batas',
                    'about_story' => 'Menghadirkan kurasi mode terbaik dengan perpaduan kenyamanan bahan dan tren gaya busana kontemporer.',
                    'operational_hours' => [
                        'monday' => ['open' => '09:00', 'close' => '21:00'],
                        'tuesday' => ['open' => '09:00', 'close' => '21:00'],
                        'wednesday' => ['open' => '09:00', 'close' => '21:00'],
                        'thursday' => ['open' => '09:00', 'close' => '21:00'],
                        'friday' => ['open' => '09:00', 'close' => '21:00'],
                        'saturday' => ['open' => '09:00', 'close' => '21:30'],
                        'sunday' => ['open' => '09:00', 'close' => '21:00'],
                    ],
                ],
                'store' => [
                    'allow_pickup' => true,
                    'allow_delivery' => true,
                    'allow_request_order' => false,
                    'allow_scheduled_order' => false,
                    'allow_customer_po' => false,
                    'allow_reservation' => false,
                    'min_order_amount' => 50000,
                    'lead_time_hours' => 2,
                    'available_slots' => ['Pengiriman Reguler (10:00 - 14:00)', 'Pengiriman Sore (15:00 - 18:00)'],
                    'announcement_text' => 'Koleksi busana terbaru siap kirim ke seluruh Indonesia. Gratis ongkir untuk pembelian tertentu.',
                    'order_notes_placeholder' => 'Contoh: warna cadangan jika habis...',
                ],
                'tables' => ['supported' => false],
                'shipping' => 'standard',
                'orders' => ['seed_direct' => true, 'seed_po' => false],
            ],

            'retail_electronics' => [
                'landing' => [
                    'headline' => 'Gadget, Aksesoris & Elektronik Bergaransi Resmi',
                    'subheadline' => 'Solusi teknologi terpercaya dengan jaminan 100% original, pelayanan klaim garansi mudah, dan pengiriman aman terlindungi bubble wrap tebal.',
                    'theme_color' => '#0F172A',
                    'cta_primary_text' => 'Pesan',
                    'cta_secondary_text' => 'Konsultasi',
                    'announcement_badge' => '100% Produk Original Bergaransi',
                    'whatsapp_welcome_message' => 'Halo ' . $business->name . ', saya ingin konsultasi spesifikasi atau stok produk elektronik ini.',
                    'services_title' => 'Produk Gadget & Aksesoris Unggulan',
                    'about_title' => 'Destinasi Teknologi Terpercaya',
                    'about_story' => 'Bekerja sama dengan distributor resmi brand terkemuka untuk menghadirkan perangkat berkinerja tinggi dengan harga kompetitif.',
                    'operational_hours' => [
                        'monday' => ['open' => '09:30', 'close' => '20:30'],
                        'tuesday' => ['open' => '09:30', 'close' => '20:30'],
                        'wednesday' => ['open' => '09:30', 'close' => '20:30'],
                        'thursday' => ['open' => '09:30', 'close' => '20:30'],
                        'friday' => ['open' => '09:30', 'close' => '20:30'],
                        'saturday' => ['open' => '09:30', 'close' => '21:00'],
                        'sunday' => ['open' => '09:30', 'close' => '20:00'],
                    ],
                ],
                'store' => [
                    'allow_pickup' => true,
                    'allow_delivery' => true,
                    'allow_request_order' => false,
                    'allow_scheduled_order' => false,
                    'allow_customer_po' => false,
                    'allow_reservation' => false,
                    'min_order_amount' => 50000,
                    'lead_time_hours' => 2,
                    'available_slots' => ['Pengiriman Batch 1 (10:00 - 13:00)', 'Pengiriman Batch 2 (14:00 - 18:00)'],
                    'announcement_text' => 'Produk elektronik & gadget 100% original bergaransi resmi. Packing aman asuransi pengiriman.',
                    'order_notes_placeholder' => 'Contoh: packing kayu atau asuransi tambahan...',
                ],
                'tables' => ['supported' => false],
                'shipping' => 'standard',
                'orders' => ['seed_direct' => true, 'seed_po' => false],
            ],

            'retail_pharmacy' => [
                'landing' => [
                    'headline' => 'Apotek Resmi, Obat Asli & Konsultasi Apoteker',
                    'subheadline' => 'Melayani resep dokter, obat bebas berizin BPOM, suplemen kesehatan, dan alat medis lengkap dengan pendampingan apoteker berlisensi.',
                    'theme_color' => '#059669',
                    'cta_primary_text' => 'Pesan',
                    'cta_secondary_text' => 'Tanya Apoteker',
                    'announcement_badge' => 'Apoteker Berlisensi & Obat Resmi BPOM',
                    'whatsapp_welcome_message' => 'Halo Apotek ' . $business->name . ', saya ingin konsultasi resep atau cek ketersediaan obat.',
                    'services_title' => 'Kategori Obat, Vitamin & Alkes',
                    'about_title' => 'Dedikasi untuk Kesehatan Keluarga',
                    'about_story' => 'Menjaga integritas rantai pasok obat dengan suhu penyimpanan terstandar dan konsultasi pengobatan yang aman.',
                    'operational_hours' => [
                        'monday' => ['open' => '07:30', 'close' => '22:00'],
                        'tuesday' => ['open' => '07:30', 'close' => '22:00'],
                        'wednesday' => ['open' => '07:30', 'close' => '22:00'],
                        'thursday' => ['open' => '07:30', 'close' => '22:00'],
                        'friday' => ['open' => '07:30', 'close' => '22:00'],
                        'saturday' => ['open' => '07:30', 'close' => '22:00'],
                        'sunday' => ['open' => '08:00', 'close' => '21:00'],
                    ],
                ],
                'store' => [
                    'allow_pickup' => true,
                    'allow_delivery' => true,
                    'allow_request_order' => true,
                    'allow_scheduled_order' => true,
                    'allow_customer_po' => false,
                    'allow_reservation' => false,
                    'min_order_amount' => 15000,
                    'lead_time_hours' => 1,
                    'available_slots' => ['Pengantaran Kilat (1-2 Jam)', 'Pengantaran Terjadwal (14:00 - 17:00)'],
                    'announcement_text' => 'Apotek resmi berizin BPOM. Melayani tebus resep, obat bebas & konsultasi pemakaian obat.',
                    'order_notes_placeholder' => 'Contoh: cantumkan foto resep dokter atau keluhan...',
                ],
                'tables' => ['supported' => false],
                'shipping' => 'standard',
                'orders' => ['seed_direct' => true, 'seed_po' => false],
            ],

            'service_barbershop' => [
                'landing' => [
                    'headline' => 'Gentlemen Grooming & Haircut Berkelas',
                    'subheadline' => 'Pengalaman pangkas rambut pria profesional dengan capster berpengalaman, cuci rambut pijat relaksasi, dan produk penataan rambut terbaik.',
                    'theme_color' => '#1E293B',
                    'cta_primary_text' => 'Reservasi',
                    'cta_secondary_text' => 'Layanan',
                    'announcement_badge' => 'Slot Booking Tanpa Antre',
                    'whatsapp_welcome_message' => 'Halo ' . $business->name . ', saya ingin reservasi slot waktu potong rambut & grooming.',
                    'services_title' => 'Paket Potong Rambut & Grooming',
                    'about_title' => 'Seni Presisi Penampilan Pria',
                    'about_story' => 'Menghadirkan gaya rambut klasik hingga modern yang disesuaikan dengan kontur wajah dan kepribadian Anda.',
                    'operational_hours' => [
                        'monday' => ['open' => '10:00', 'close' => '21:00'],
                        'tuesday' => ['open' => '10:00', 'close' => '21:00'],
                        'wednesday' => ['open' => '10:00', 'close' => '21:00'],
                        'thursday' => ['open' => '10:00', 'close' => '21:00'],
                        'friday' => ['open' => '10:00', 'close' => '21:30'],
                        'saturday' => ['open' => '09:30', 'close' => '21:30'],
                        'sunday' => ['open' => '09:30', 'close' => '21:00'],
                    ],
                ],
                'store' => [
                    'allow_pickup' => false,
                    'allow_delivery' => false,
                    'allow_request_order' => false,
                    'allow_scheduled_order' => false,
                    'allow_customer_po' => false,
                    'allow_reservation' => true,
                    'min_order_amount' => 0,
                    'lead_time_hours' => 1,
                    'available_slots' => ['10:00 - 11:30', '11:30 - 13:00', '14:00 - 15:30', '16:00 - 17:30', '18:30 - 20:30'],
                    'announcement_text' => 'Potong rambut berkelas tanpa antre. Pilih capster favorit & reservasi waktu Anda sekarang.',
                    'order_notes_placeholder' => 'Contoh: request model fade, shave jenggot...',
                ],
                'tables' => ['supported' => true, 'prefix' => 'Kursi Pangkas #', 'count' => 4],
                'shipping' => 'service',
                'orders' => ['seed_direct' => true, 'seed_po' => false],
            ],

            'service_workshop' => [
                'landing' => [
                    'headline' => 'Bengkel Mobil Modern, Servis Presisi & Terpercaya',
                    'subheadline' => 'Perawatan berkala, tune up mesin injeksi, ganti oli, servis rem & diagnosa scanner komputer lengkap dengan teknisi berpengalaman.',
                    'theme_color' => '#C2410C',
                    'cta_primary_text' => 'Reservasi',
                    'cta_secondary_text' => 'Servis',
                    'announcement_badge' => 'Mekanik Bersertifikat & Sparepart Asli',
                    'whatsapp_welcome_message' => 'Halo Bengkel ' . $business->name . ', saya ingin konsultasi servis mobil atau booking bay servis.',
                    'services_title' => 'Layanan Servis & Perawatan Otomotif',
                    'about_title' => 'Transparan, Tepat, dan Bergaransi',
                    'about_story' => 'Mengutamakan keterbukaan biaya dan estimasi waktu pengerjaan dengan peralatan diagnostik mutakhir.',
                    'operational_hours' => [
                        'monday' => ['open' => '08:00', 'close' => '17:00'],
                        'tuesday' => ['open' => '08:00', 'close' => '17:00'],
                        'wednesday' => ['open' => '08:00', 'close' => '17:00'],
                        'thursday' => ['open' => '08:00', 'close' => '17:00'],
                        'friday' => ['open' => '08:00', 'close' => '17:00'],
                        'saturday' => ['open' => '08:00', 'close' => '17:00'],
                        'sunday' => ['open' => '09:00', 'close' => '15:00'],
                    ],
                ],
                'store' => [
                    'allow_pickup' => false,
                    'allow_delivery' => false,
                    'allow_request_order' => false,
                    'allow_scheduled_order' => false,
                    'allow_customer_po' => false,
                    'allow_reservation' => true,
                    'min_order_amount' => 0,
                    'lead_time_hours' => 2,
                    'available_slots' => ['08:30 - 10:30 (Pagi)', '10:30 - 12:30 (Siang)', '13:30 - 15:30 (Sore)', '15:30 - 17:00 (Sore)'],
                    'announcement_text' => 'Bengkel mobil modern dengan mekanik bersertifikat. Booking jadwal servis berkala Anda hari ini.',
                    'order_notes_placeholder' => 'Contoh: plat nomor mobil, tipe mobil, keluhan getar di rem...',
                ],
                'tables' => ['supported' => true, 'prefix' => 'Bay Servis #', 'count' => 4],
                'shipping' => 'service',
                'orders' => ['seed_direct' => true, 'seed_po' => false],
            ],

            'service_laundry' => [
                'landing' => [
                    'headline' => 'Layanan Laundry Kiloan & Satuan Bersih Higienis',
                    'subheadline' => 'Cuci setrika rapi wangi tahan lama dengan air terfilter dan deterjen ramah serat kain. Layanan antar-jemput gratis untuk area sekitar.',
                    'theme_color' => '#0284C7',
                    'cta_primary_text' => 'Pesan',
                    'cta_secondary_text' => 'Jemput Baju',
                    'announcement_badge' => 'Antar-Jemput Cucian Cepat',
                    'whatsapp_welcome_message' => 'Halo ' . $business->name . ', saya ingin request jadwal jemput cucian kiloan/satuan ke alamat saya.',
                    'services_title' => 'Daftar Paket Laundry & Cuci Satuan',
                    'about_title' => 'Pakaian Bersih, Rapi & Segar Setiap Hari',
                    'about_story' => 'Merawat setiap helai pakaian Anda dengan mesin cuci industri modern dan sistem pemisahan warna ketat.',
                    'operational_hours' => [
                        'monday' => ['open' => '07:00', 'close' => '21:00'],
                        'tuesday' => ['open' => '07:00', 'close' => '21:00'],
                        'wednesday' => ['open' => '07:00', 'close' => '21:00'],
                        'thursday' => ['open' => '07:00', 'close' => '21:00'],
                        'friday' => ['open' => '07:00', 'close' => '21:00'],
                        'saturday' => ['open' => '07:00', 'close' => '21:00'],
                        'sunday' => ['open' => '08:00', 'close' => '18:00'],
                    ],
                ],
                'store' => [
                    'allow_pickup' => true,
                    'allow_delivery' => true,
                    'allow_request_order' => true,
                    'allow_scheduled_order' => true,
                    'allow_customer_po' => true,
                    'allow_reservation' => false,
                    'min_order_amount' => 25000,
                    'lead_time_hours' => 2,
                    'available_slots' => ['Jemput Pagi (08:00 - 10:00)', 'Jemput Siang (13:00 - 15:00)', 'Jemput Sore (16:00 - 18:00)'],
                    'announcement_text' => 'Layanan laundry kiloan & satuan higienis. Hubungi kami untuk penjemputan gratis ke rumah Anda.',
                    'order_notes_placeholder' => 'Contoh: baju sutra harap cuci tangan, pisahkan selimut...',
                ],
                'tables' => ['supported' => false],
                'shipping' => 'standard',
                'orders' => ['seed_direct' => true, 'seed_po' => false],
            ],

            'service_salon_spa' => [
                'landing' => [
                    'headline' => 'Perawatan Kecantikan, Rambut & Spa Relaksasi',
                    'subheadline' => 'Tempat pelarian sempurna untuk relaksasi tubuh dan perawatan kecantikan holistik dengan terapis bersertifikat di suasana yang damai.',
                    'theme_color' => '#A21CAF',
                    'cta_primary_text' => 'Reservasi',
                    'cta_secondary_text' => 'Treatment',
                    'announcement_badge' => 'Oasis Relaksasi & Perawatan Holistik',
                    'whatsapp_welcome_message' => 'Halo Salon & Spa ' . $business->name . ', saya ingin reservasi jadwal creambath, facial, atau pijat tubuh.',
                    'services_title' => 'Menu Perawatan Tubuh & Wajah',
                    'about_title' => 'Kembalikan Kesegaran Alami Tubuh',
                    'about_story' => 'Menggunakan ramuan herbal tradisional dan produk perawatan wajah dermatologi modern untuk merawat kecantikan Anda seutuhnya.',
                    'operational_hours' => [
                        'monday' => ['open' => '09:00', 'close' => '20:00'],
                        'tuesday' => ['open' => '09:00', 'close' => '20:00'],
                        'wednesday' => ['open' => '09:00', 'close' => '20:00'],
                        'thursday' => ['open' => '09:00', 'close' => '20:00'],
                        'friday' => ['open' => '09:00', 'close' => '20:30'],
                        'saturday' => ['open' => '09:00', 'close' => '20:30'],
                        'sunday' => ['open' => '09:00', 'close' => '20:00'],
                    ],
                ],
                'store' => [
                    'allow_pickup' => false,
                    'allow_delivery' => false,
                    'allow_request_order' => false,
                    'allow_scheduled_order' => false,
                    'allow_customer_po' => false,
                    'allow_reservation' => true,
                    'min_order_amount' => 0,
                    'lead_time_hours' => 1,
                    'available_slots' => ['09:00 - 11:00', '11:00 - 13:00', '14:00 - 16:00', '16:00 - 18:00', '18:30 - 20:00'],
                    'announcement_text' => 'Manjakan diri Anda dengan spa & facial relaksasi. Reservasi jadwal kedatangan Anda secara instan.',
                    'order_notes_placeholder' => 'Contoh: request terapis wanita, kulit sensitif...',
                ],
                'tables' => ['supported' => true, 'prefix' => 'Ruang Treatment #', 'count' => 4],
                'shipping' => 'service',
                'orders' => ['seed_direct' => true, 'seed_po' => false],
            ],

            'service_clinic' => [
                'landing' => [
                    'headline' => 'Klinik Pratama & Perawatan Gigi Terpercaya',
                    'subheadline' => 'Pelayanan medis terpadu dengan dokter umum dan dokter gigi berizin. Ruang praktik higienis, ramah pasien, dan peralatan diagnostik modern.',
                    'theme_color' => '#0891B2',
                    'cta_primary_text' => 'Reservasi',
                    'cta_secondary_text' => 'Jadwal Dokter',
                    'announcement_badge' => 'Konsultasi Dokter & Antrean Terjadwal',
                    'whatsapp_welcome_message' => 'Halo Klinik ' . $business->name . ', saya ingin membuat janji temu konsultasi dokter / perawatan gigi.',
                    'services_title' => 'Layanan Medis & Poli Perawatan',
                    'about_title' => 'Pelayanan Kesehatan Profesional & Bersahabat',
                    'about_story' => 'Berkomitmen memberikan diagnosis yang tepat, pencegahan terarah, dan pengobatan optimal bagi seluruh anggota keluarga.',
                    'operational_hours' => [
                        'monday' => ['open' => '08:00', 'close' => '21:00'],
                        'tuesday' => ['open' => '08:00', 'close' => '21:00'],
                        'wednesday' => ['open' => '08:00', 'close' => '21:00'],
                        'thursday' => ['open' => '08:00', 'close' => '21:00'],
                        'friday' => ['open' => '08:00', 'close' => '21:00'],
                        'saturday' => ['open' => '08:00', 'close' => '20:00'],
                        'sunday' => ['open' => '09:00', 'close' => '16:00'],
                    ],
                ],
                'store' => [
                    'allow_pickup' => false,
                    'allow_delivery' => false,
                    'allow_request_order' => false,
                    'allow_scheduled_order' => false,
                    'allow_customer_po' => false,
                    'allow_reservation' => true,
                    'min_order_amount' => 0,
                    'lead_time_hours' => 1,
                    'available_slots' => ['Sesi Pagi (09:00 - 11:30)', 'Sesi Siang (13:00 - 15:30)', 'Sesi Malam (18:00 - 20:30)'],
                    'announcement_text' => 'Layanan konsultasi medis & gigi tanpa antrean panjang. Booking jadwal dokter Anda di sini.',
                    'order_notes_placeholder' => 'Contoh: keluhan sakit gigi geraham, kontrol rutin darah tinggi...',
                ],
                'tables' => ['supported' => true, 'prefix' => 'Ruang Poli #', 'count' => 3],
                'shipping' => 'service',
                'orders' => ['seed_direct' => true, 'seed_po' => false],
            ],

            'manufaktur_konveksi' => [
                'landing' => [
                    'headline' => 'Pabrik Konveksi B2B: Seragam Kerja, Polo & Jaket',
                    'subheadline' => 'Kapasitas produksi hingga 20.000 potong/bulan dengan mesin jahit modern, bordir komputer presisi, dan quality control ketat sebelum pengiriman.',
                    'theme_color' => '#4338CA',
                    'cta_primary_text' => 'Pesan',
                    'cta_secondary_text' => 'Ajukan PO',
                    'announcement_badge' => 'Vendor Pengadaan Seragam Korporat',
                    'whatsapp_welcome_message' => 'Halo Konveksi ' . $business->name . ', kami ingin konsultasi tender produksi seragam dan minta quotation.',
                    'services_title' => 'Lini Produksi Garmen & Seragam',
                    'about_title' => 'Mitra Produksi Pakaian Terstandar Industri',
                    'about_story' => 'Mendukung ratusan perusahaan, BUMN, dan komunitas di seluruh Indonesia dalam pengadaan busana kerja dengan bahan bersertifikasi.',
                    'operational_hours' => [
                        'monday' => ['open' => '08:00', 'close' => '17:00'],
                        'tuesday' => ['open' => '08:00', 'close' => '17:00'],
                        'wednesday' => ['open' => '08:00', 'close' => '17:00'],
                        'thursday' => ['open' => '08:00', 'close' => '17:00'],
                        'friday' => ['open' => '08:00', 'close' => '17:00'],
                        'saturday' => ['open' => '08:00', 'close' => '14:00'],
                        'sunday' => ['open' => '00:00', 'close' => '00:00'],
                    ],
                ],
                'store' => [
                    'allow_pickup' => true,
                    'allow_delivery' => true,
                    'allow_request_order' => true,
                    'allow_scheduled_order' => true,
                    'allow_customer_po' => true,
                    'allow_reservation' => false,
                    'min_order_amount' => 500000,
                    'lead_time_hours' => 72,
                    'available_slots' => ['Batch Produksi Minggu ke-1', 'Batch Produksi Minggu ke-2', 'Batch Pengiriman Akhir Bulan'],
                    'announcement_text' => 'Produsen seragam kantor & kaos event terpercaya. Menerima PO B2B dengan pengiriman bertahap multi-drop.',
                    'order_notes_placeholder' => 'Contoh: spesifikasi kain drill, ukuran bordir dada kiri, batas deadline acara...',
                ],
                'tables' => ['supported' => false],
                'shipping' => 'b2b',
                'orders' => ['seed_direct' => true, 'seed_po' => true],
            ],

            'manufaktur_percetakan' => [
                'landing' => [
                    'headline' => 'Percetakan Offset, Digital & Packaging UMKM',
                    'subheadline' => 'Solusi cetak kemasan produk, packaging corrugated box, stiker label kustom, brosur promosi, dan company profile dengan warna akurat.',
                    'theme_color' => '#0284C7',
                    'cta_primary_text' => 'Pesan',
                    'cta_secondary_text' => 'Cetak Kustom',
                    'announcement_badge' => 'Cetak Kemasan Presisi & Cepat',
                    'whatsapp_welcome_message' => 'Halo Percetakan ' . $business->name . ', saya ingin mencetak kemasan kustom / label dan mengirim file desain.',
                    'services_title' => 'Produk Kemasan & Advertising',
                    'about_title' => 'Ketepatan Warna & Ketajaman Cetak',
                    'about_story' => 'Didukung mesin offset 5 warna dan digital printing beresolusi tinggi untuk hasil cetak yang memikat calon pembeli produk Anda.',
                    'operational_hours' => [
                        'monday' => ['open' => '08:00', 'close' => '17:30'],
                        'tuesday' => ['open' => '08:00', 'close' => '17:30'],
                        'wednesday' => ['open' => '08:00', 'close' => '17:30'],
                        'thursday' => ['open' => '08:00', 'close' => '17:30'],
                        'friday' => ['open' => '08:00', 'close' => '17:30'],
                        'saturday' => ['open' => '08:00', 'close' => '15:00'],
                        'sunday' => ['open' => '00:00', 'close' => '00:00'],
                    ],
                ],
                'store' => [
                    'allow_pickup' => true,
                    'allow_delivery' => true,
                    'allow_request_order' => true,
                    'allow_scheduled_order' => true,
                    'allow_customer_po' => true,
                    'allow_reservation' => false,
                    'min_order_amount' => 100000,
                    'lead_time_hours' => 48,
                    'available_slots' => ['Slot Cetak Kilat (24 Jam)', 'Slot Cetak Reguler (3-5 Hari)'],
                    'announcement_text' => 'Pusat cetak packaging dus UMKM & promosi bisnis. Upload file desain dan cetak sesuai kebutuhan.',
                    'order_notes_placeholder' => 'Contoh: laminasi doff / glossy, ukuran pisau pon custom...',
                ],
                'tables' => ['supported' => false],
                'shipping' => 'b2b',
                'orders' => ['seed_direct' => true, 'seed_po' => true],
            ],

            'manufaktur_craft' => [
                'landing' => [
                    'headline' => 'Karya Kerajinan Tangan, Anyaman & Souvenir',
                    'subheadline' => 'Produk handmade artisanal dari serat alam, anyaman bambu, kayu, dan tembikar untuk dekorasi rumah, hampers elegan, dan souvenir pernikahan.',
                    'theme_color' => '#B45309',
                    'cta_primary_text' => 'Pesan',
                    'cta_secondary_text' => 'Kustom Souvenir',
                    'announcement_badge' => 'Artisan Handmade & Eco-friendly',
                    'whatsapp_welcome_message' => 'Halo ' . $business->name . ', saya ingin memesan souvenir kerajinan tangan custom untuk acara.',
                    'services_title' => 'Koleksi Kerajinan & Gift Set',
                    'about_title' => 'Warisan Seni & Nilai Budaya Nusantara',
                    'about_story' => 'Memberdayakan komunitas perajin lokal untuk menghasilkan cinderamata bernilai estetika tinggi dan ramah lingkungan.',
                    'operational_hours' => [
                        'monday' => ['open' => '08:30', 'close' => '17:00'],
                        'tuesday' => ['open' => '08:30', 'close' => '17:00'],
                        'wednesday' => ['open' => '08:30', 'close' => '17:00'],
                        'thursday' => ['open' => '08:30', 'close' => '17:00'],
                        'friday' => ['open' => '08:30', 'close' => '17:00'],
                        'saturday' => ['open' => '08:30', 'close' => '16:00'],
                        'sunday' => ['open' => '09:00', 'close' => '14:00'],
                    ],
                ],
                'store' => [
                    'allow_pickup' => true,
                    'allow_delivery' => true,
                    'allow_request_order' => true,
                    'allow_scheduled_order' => true,
                    'allow_customer_po' => true,
                    'allow_reservation' => false,
                    'min_order_amount' => 100000,
                    'lead_time_hours' => 48,
                    'available_slots' => ['Batch Pengiriman Awal Bulan', 'Batch Pengiriman Pertengahan Bulan'],
                    'announcement_text' => 'Kerajinan tangan otentik nusantara. Menerima pesanan kustom souvenir acara & hampers korporat.',
                    'order_notes_placeholder' => 'Contoh: grafir nama pada kayu, kartu ucapan khusus...',
                ],
                'tables' => ['supported' => false],
                'shipping' => 'standard',
                'orders' => ['seed_direct' => true, 'seed_po' => true],
            ],

            'manufaktur_furniture' => [
                'landing' => [
                    'headline' => 'Mebel Kayu Solid, Kitchen Set & Desain Interior',
                    'subheadline' => 'Pabrik furniture kayu jati dan mahoni pilihan. Menghadirkan meja, kursi, lemari, dan custom interior presisi untuk hunian, cafe, & kantor.',
                    'theme_color' => '#78350F',
                    'cta_primary_text' => 'Pesan',
                    'cta_secondary_text' => 'Konsultasi Desain',
                    'announcement_badge' => 'Kayu Pilihan Bergaransi Kokoh',
                    'whatsapp_welcome_message' => 'Halo ' . $business->name . ', saya ingin konsultasi pembuatan kitchen set / furniture custom.',
                    'services_title' => 'Produk Mebel & Layanan Kustom',
                    'about_title' => 'Kekuatan Konstruksi & Estetika Kayu Alami',
                    'about_story' => 'Didukung tukang kayu berpengalaman puluhan tahun dengan teknik sambungan pasak tradisional yang kokoh dan tahan puluhan tahun.',
                    'operational_hours' => [
                        'monday' => ['open' => '08:00', 'close' => '17:00'],
                        'tuesday' => ['open' => '08:00', 'close' => '17:00'],
                        'wednesday' => ['open' => '08:00', 'close' => '17:00'],
                        'thursday' => ['open' => '08:00', 'close' => '17:00'],
                        'friday' => ['open' => '08:00', 'close' => '17:00'],
                        'saturday' => ['open' => '08:00', 'close' => '15:00'],
                        'sunday' => ['open' => '00:00', 'close' => '00:00'],
                    ],
                ],
                'store' => [
                    'allow_pickup' => true,
                    'allow_delivery' => true,
                    'allow_request_order' => true,
                    'allow_scheduled_order' => true,
                    'allow_customer_po' => true,
                    'allow_reservation' => false,
                    'min_order_amount' => 500000,
                    'lead_time_hours' => 72,
                    'available_slots' => ['Pengiriman Armada Truk Khusus', 'Instalasi di Tempat Pelanggan'],
                    'announcement_text' => 'Mebel kayu solid tahan lama & custom interior. Free konsultasi desain dan survei ruangan.',
                    'order_notes_placeholder' => 'Contoh: dimensi ruangan, pilihan warna finishing melamine doff/gloss...',
                ],
                'tables' => ['supported' => false],
                'shipping' => 'b2b',
                'orders' => ['seed_direct' => true, 'seed_po' => true],
            ],

            'trading_distributor' => [
                'landing' => [
                    'headline' => 'Distributor Resmi Pasokan Grosir & Partai Besar',
                    'subheadline' => 'Pemasok tangan pertama terpercaya untuk komoditas pangan, sembako, dan bahan baku industri dengan harga distributor bersaing.',
                    'theme_color' => '#0F766E',
                    'cta_primary_text' => 'Pesan',
                    'cta_secondary_text' => 'Daftar B2B',
                    'announcement_badge' => 'Pasokan Teratur & Harga Grosir Pabrik',
                    'whatsapp_welcome_message' => 'Halo Distributor ' . $business->name . ', kami ingin meminta price list pasokan grosir rutin.',
                    'services_title' => 'Katalog Pasokan Komoditas & Grosir',
                    'about_title' => 'Rantai Pasok Tangguh & Terpercaya',
                    'about_story' => 'Menghubungkan produsen utama dengan ribuan peritel, horeka (hotel resto kafe), dan pabrik manufaktur di seluruh penjuru wilayah.',
                    'operational_hours' => [
                        'monday' => ['open' => '07:30', 'close' => '17:00'],
                        'tuesday' => ['open' => '07:30', 'close' => '17:00'],
                        'wednesday' => ['open' => '07:30', 'close' => '17:00'],
                        'thursday' => ['open' => '07:30', 'close' => '17:00'],
                        'friday' => ['open' => '07:30', 'close' => '17:00'],
                        'saturday' => ['open' => '07:30', 'close' => '15:00'],
                        'sunday' => ['open' => '00:00', 'close' => '00:00'],
                    ],
                ],
                'store' => [
                    'allow_pickup' => true,
                    'allow_delivery' => true,
                    'allow_request_order' => true,
                    'allow_scheduled_order' => true,
                    'allow_customer_po' => true,
                    'allow_reservation' => false,
                    'min_order_amount' => 1000000,
                    'lead_time_hours' => 24,
                    'available_slots' => ['Drop Pengiriman Pagi (08:00 - 12:00)', 'Drop Pengiriman Siang (13:00 - 17:00)'],
                    'announcement_text' => 'Pusat grosir pasokan resmi. Melayani PO rutin partai besar dengan faktur pajak & tempo terpercaya.',
                    'order_notes_placeholder' => 'Contoh: nomor PO internal kantor, jadwal bongkar muat gudang...',
                ],
                'tables' => ['supported' => false],
                'shipping' => 'b2b',
                'orders' => ['seed_direct' => true, 'seed_po' => true],
            ],
            'mfg_precision' => [
                'landing' => [
                    'headline' => 'Pabrik Injection Molding Plastik & CNC Presisi B2B',
                    'subheadline' => 'Spesialis fabrikasi komponen teknik, cetakan molding presisi, dan stamping logam berkualitas ekspor dengan toleransi mikro.',
                    'theme_color' => '#475569',
                    'cta_primary_text' => 'Pesan',
                    'cta_secondary_text' => 'Katalog Komponen',
                    'announcement_badge' => 'Toleransi Mikro & Sertifikasi Mutu ISO',
                    'whatsapp_welcome_message' => 'Halo ' . $business->name . ', kami ingin mengirimkan file CAD / gambar teknik untuk penawaran fabrikasi presisi.',
                    'services_title' => 'Komponen Teknik & Fabrikasi Presisi',
                    'about_title' => 'Akurasi Tinggi & Mesin CNC Modern',
                    'about_story' => 'Melayani kebutuhan komponen suku cadang otomotif, alat elektronik, dan peralatan industri dengan mesin injection mutakhir.',
                    'operational_hours' => [
                        'monday' => ['open' => '08:00', 'close' => '17:00'],
                        'tuesday' => ['open' => '08:00', 'close' => '17:00'],
                        'wednesday' => ['open' => '08:00', 'close' => '17:00'],
                        'thursday' => ['open' => '08:00', 'close' => '17:00'],
                        'friday' => ['open' => '08:00', 'close' => '17:00'],
                        'saturday' => ['open' => '08:00', 'close' => '15:00'],
                        'sunday' => ['open' => '00:00', 'close' => '00:00'],
                    ],
                ],
                'store' => [
                    'allow_pickup' => true,
                    'allow_delivery' => true,
                    'allow_request_order' => true,
                    'allow_scheduled_order' => true,
                    'allow_customer_po' => true,
                    'allow_reservation' => false,
                    'min_order_amount' => 1000000,
                    'lead_time_hours' => 72,
                    'available_slots' => ['Batch Pengiriman Truk Pabrik', 'Pengiriman Kargo Kontainer'],
                    'announcement_text' => 'Pabrik komponen teknik presisi B2B. Melayani pengadaan industri skala besar dengan sistem Customer PO & delivery bertahap.',
                    'order_notes_placeholder' => 'Contoh: toleransi dimensi +/- 0.02mm, nomor gambar teknik...',
                ],
                'tables' => ['supported' => false],
                'shipping' => 'b2b',
                'orders' => ['seed_direct' => true, 'seed_po' => true],
            ],

            'service_agency' => [
                'landing' => [
                    'headline' => 'Digital Agency: Software House, Web App & Desain UI/UX',
                    'subheadline' => 'Membantu transformasi digital bisnis Anda melalui pengembangan aplikasi modern, website berkecepatan tinggi, dan strategi branding digital.',
                    'theme_color' => '#2563EB',
                    'cta_primary_text' => 'Konsultasi',
                    'cta_secondary_text' => 'Portofolio',
                    'announcement_badge' => 'Solusi Digital Skalabel & Bergaransi',
                    'whatsapp_welcome_message' => 'Halo Tim ' . $business->name . ', kami ingin konsultasi proyek pengembangan web/aplikasi untuk perusahaan kami.',
                    'services_title' => 'Layanan Pengembangan Software & Desain',
                    'about_title' => 'Inovasi Teknologi Terdepan untuk Bisnis',
                    'about_story' => 'Didukung oleh engineer, arsitek sistem, dan UI/UX desainer berpengalaman dalam membangun produk digital kelas dunia.',
                    'operational_hours' => [
                        'monday' => ['open' => '09:00', 'close' => '18:00'],
                        'tuesday' => ['open' => '09:00', 'close' => '18:00'],
                        'wednesday' => ['open' => '09:00', 'close' => '18:00'],
                        'thursday' => ['open' => '09:00', 'close' => '18:00'],
                        'friday' => ['open' => '09:00', 'close' => '18:00'],
                        'saturday' => ['open' => '00:00', 'close' => '00:00'],
                        'sunday' => ['open' => '00:00', 'close' => '00:00'],
                    ],
                ],
                'store' => [
                    'allow_pickup' => false,
                    'allow_delivery' => false,
                    'allow_request_order' => true,
                    'allow_scheduled_order' => false,
                    'allow_customer_po' => true,
                    'allow_reservation' => true,
                    'min_order_amount' => 0,
                    'lead_time_hours' => 24,
                    'available_slots' => ['Sesi Pagi (10:00 - 12:00)', 'Sesi Siang (14:00 - 16:00)', 'Sesi Sore (16:30 - 18:00)'],
                    'announcement_text' => 'Solusi digital terpadu untuk bisnis Anda. Booking sesi konsultasi teknis atau ajukan scope of work proyek.',
                    'order_notes_placeholder' => 'Contoh: ringkasan kebutuhan proyek, target deadline rilis...',
                ],
                'tables' => ['supported' => true, 'prefix' => 'Ruang Konsultasi & Meeting #', 'count' => 3],
                'shipping' => 'service',
                'orders' => ['seed_direct' => true, 'seed_po' => true],
            ],

            'service_contractor' => [
                'landing' => [
                    'headline' => 'Jasa Kontraktor Bangunan, Renovasi Rumah & Desain Arsitektur',
                    'subheadline' => 'Mewujudkan hunian idaman dan bangunan komersial berkualitas kokoh dengan material terstandar SNI, gambar kerja presisi, dan pengawasan berkala.',
                    'theme_color' => '#D97706',
                    'cta_primary_text' => 'Survei Lokasi',
                    'cta_secondary_text' => 'RAB Proyek',
                    'announcement_badge' => 'Bergaransi Struktur & Tepat Waktu',
                    'whatsapp_welcome_message' => 'Halo Kontraktor ' . $business->name . ', saya ingin konsultasi rencana bangun/renovasi rumah dan jadwal survei gratis.',
                    'services_title' => 'Paket Bangun Baru & Pekerjaan Renovasi',
                    'about_title' => 'Konstruksi Kokoh & Anggaran Transparan',
                    'about_story' => 'Berpengalaman lebih dari 10 tahun menyelesaikan berbagai proyek residensial dan ruko komersial dengan laporan progres mingguan.',
                    'operational_hours' => [
                        'monday' => ['open' => '08:00', 'close' => '17:00'],
                        'tuesday' => ['open' => '08:00', 'close' => '17:00'],
                        'wednesday' => ['open' => '08:00', 'close' => '17:00'],
                        'thursday' => ['open' => '08:00', 'close' => '17:00'],
                        'friday' => ['open' => '08:00', 'close' => '17:00'],
                        'saturday' => ['open' => '08:00', 'close' => '17:00'],
                        'sunday' => ['open' => '09:00', 'close' => '15:00'],
                    ],
                ],
                'store' => [
                    'allow_pickup' => false,
                    'allow_delivery' => false,
                    'allow_request_order' => true,
                    'allow_scheduled_order' => false,
                    'allow_customer_po' => true,
                    'allow_reservation' => true,
                    'min_order_amount' => 0,
                    'lead_time_hours' => 24,
                    'available_slots' => ['Survei Pagi (09:00 - 12:00)', 'Survei Siang (13:30 - 16:30)'],
                    'announcement_text' => 'Jasa bangun & renovasi terpercaya. Booking jadwal survei lokasi & konsultasi estimasi RAB transparan.',
                    'order_notes_placeholder' => 'Contoh: luas lahan / bangunan, perkiraan lokasi survei...',
                ],
                'tables' => ['supported' => true, 'prefix' => 'Tim Estimator & Survei #', 'count' => 3],
                'shipping' => 'service',
                'orders' => ['seed_direct' => true, 'seed_po' => true],
            ],

            'service_event' => [
                'landing' => [
                    'headline' => 'Wedding Planner & Event Organizer Profesional',
                    'subheadline' => 'Perencanaan acara pernikahan intim hingga pesta megah dengan konsep tematik, tim pelaksana berpengalaman, dan koordinasi vendor sempurna.',
                    'theme_color' => '#BE185D',
                    'cta_primary_text' => 'Konsultasi',
                    'cta_secondary_text' => 'Paket Event',
                    'announcement_badge' => 'Momen Istimewa Penuh Kenangan Indah',
                    'whatsapp_welcome_message' => 'Halo ' . $business->name . ', kami ingin konsultasi paket wedding organizer untuk tanggal pernikahan kami.',
                    'services_title' => 'Paket Pernikahan & Manajemen Acara',
                    'about_title' => 'Dedikasi Penuh untuk Hari Bahagia Anda',
                    'about_story' => 'Mengatur setiap detail acara mulai dari rundown, koordinasi vendor busana, rias, katering, hingga dokumentasi profesional.',
                    'operational_hours' => [
                        'monday' => ['open' => '09:00', 'close' => '20:00'],
                        'tuesday' => ['open' => '09:00', 'close' => '20:00'],
                        'wednesday' => ['open' => '09:00', 'close' => '20:00'],
                        'thursday' => ['open' => '09:00', 'close' => '20:00'],
                        'friday' => ['open' => '09:00', 'close' => '20:00'],
                        'saturday' => ['open' => '09:00', 'close' => '21:00'],
                        'sunday' => ['open' => '09:00', 'close' => '21:00'],
                    ],
                ],
                'store' => [
                    'allow_pickup' => false,
                    'allow_delivery' => false,
                    'allow_request_order' => true,
                    'allow_scheduled_order' => false,
                    'allow_customer_po' => true,
                    'allow_reservation' => true,
                    'min_order_amount' => 0,
                    'lead_time_hours' => 24,
                    'available_slots' => ['Konsultasi Siang (13:00 - 15:00)', 'Konsultasi Sore (16:00 - 18:00)', 'Konsultasi Malam (19:00 - 21:00)'],
                    'announcement_text' => 'Wujudkan pernikahan impian Anda tanpa stres. Booking jadwal konsultasi konsep acara dengan wedding planner kami.',
                    'order_notes_placeholder' => 'Contoh: rencana tanggal acara, perkiraan jumlah tamu undangan...',
                ],
                'tables' => ['supported' => true, 'prefix' => 'Lounge Konsultasi Acara #', 'count' => 3],
                'shipping' => 'service',
                'orders' => ['seed_direct' => true, 'seed_po' => true],
            ],

            'agri_farming' => [
                'landing' => [
                    'headline' => 'Peternakan Ayam Broiler & Pasokan Unggas Segar',
                    'subheadline' => 'Penyedia ayam hidup dan karkas segar bersertifikat halal, dipelihara dengan pakan bernutrisi seimbang dan pemantauan biosekuriti ketat.',
                    'theme_color' => '#15803D',
                    'cta_primary_text' => 'Pesan',
                    'cta_secondary_text' => 'Pasokan Rutin',
                    'announcement_badge' => 'Unggas Sehat, Segar & Bersertifikat Halal',
                    'whatsapp_welcome_message' => 'Halo Peternakan ' . $business->name . ', kami ingin info harga panen ayam broiler hari ini dan pesanan pasokan rutin.',
                    'services_title' => 'Komoditas Unggas & Hasil Peternakan',
                    'about_title' => 'Peternakan Modern Berkelanjutan',
                    'about_story' => 'Menerapkan kandang closed-house dengan kontrol sirkulasi udara otomatis untuk memastikan kesehatan dan bobot panen ayam yang seragam.',
                    'operational_hours' => [
                        'monday' => ['open' => '06:00', 'close' => '17:00'],
                        'tuesday' => ['open' => '06:00', 'close' => '17:00'],
                        'wednesday' => ['open' => '06:00', 'close' => '17:00'],
                        'thursday' => ['open' => '06:00', 'close' => '17:00'],
                        'friday' => ['open' => '06:00', 'close' => '17:00'],
                        'saturday' => ['open' => '06:00', 'close' => '17:00'],
                        'sunday' => ['open' => '06:00', 'close' => '17:00'],
                    ],
                ],
                'store' => [
                    'allow_pickup' => true,
                    'allow_delivery' => true,
                    'allow_request_order' => true,
                    'allow_scheduled_order' => true,
                    'allow_customer_po' => true,
                    'allow_reservation' => false,
                    'min_order_amount' => 500000,
                    'lead_time_hours' => 24,
                    'available_slots' => ['Panen Pagi (05:00 - 08:00)', 'Panen Siang (12:00 - 15:00)', 'Panen Sore (16:00 - 19:00)'],
                    'announcement_text' => 'Pasokan ayam potong segar langsung dari kandang peternak. Melayani RPH, pasar grosir & restoran dengan pengiriman armada khusus.',
                    'order_notes_placeholder' => 'Contoh: rata-rata bobot hidup yang diminta (1.8 - 2.0 kg), jadwal timbang di kandang...',
                ],
                'tables' => ['supported' => false],
                'shipping' => 'b2b',
                'orders' => ['seed_direct' => true, 'seed_po' => true],
            ],
        ];

        // Aliases to guarantee 100% template_code matches across all 20 industries
        $blueprints['fnb_catering'] = $blueprints['fnb_katering'] ?? $blueprints['fnb_catering'];
        $blueprints['mfg_garment'] = $blueprints['manufaktur_konveksi'] ?? $blueprints['mfg_garment'];
        $blueprints['mfg_furniture'] = $blueprints['manufaktur_furniture'] ?? $blueprints['mfg_furniture'];
        $blueprints['mfg_craft'] = $blueprints['manufaktur_craft'] ?? $blueprints['mfg_craft'];
        $blueprints['mfg_printing'] = $blueprints['manufaktur_percetakan'] ?? $blueprints['mfg_printing'];
        $blueprints['retail_reseller'] = $blueprints['retail_fmcg'];

        return $blueprints[$templateCode] ?? $blueprints['retail_fmcg'];
    }

    /**
     * Seed a verified demonstration customer account with order history across various statuses.
     */
    private function seedDemoCustomerAccount(): void
    {
        $business = Business::where('slug', 'dapur-sedap-rasa')->first()
            ?? Business::first();

        if (! $business) {
            return;
        }

        $location = Location::where('business_id', $business->id)->first();
        $products = Product::where('business_id', $business->id)->take(3)->get();

        $demoCustomer = Customer::updateOrCreate(
            ['phone' => '081299887766'],
            [
                'business_id' => $business->id,
                'name' => 'Budi Santoso (Customer Demo)',
                'slug' => 'budi-santoso',
                'email' => 'customer@cooca.id',
                'password' => Hash::make('password123'),
                'points_balance' => 450,
                'membership_tier' => 'gold',
                'phone_verified_at' => now(),
                'email_verified_at' => now(),
                'shipping_address' => 'Jl. Gatot Subroto No. 45, RT 02 / RW 04, Menteng Dalam, Tebet, Jakarta Selatan 12870',
                'billing_address' => 'Jl. Gatot Subroto No. 45, RT 02 / RW 04, Menteng Dalam, Tebet, Jakarta Selatan 12870',
                'notes' => 'Akun Customer Testing Terverifikasi',
            ]
        );

        if ($products->isEmpty() || ! $location) {
            return;
        }

        $firstProduct = $products->first();

        // 1. Order: Pending Payment (Ready to test proof upload)
        $orderPending = CommerceOrder::updateOrCreate(
            ['order_number' => 'ORD-20260915-DEM1'],
            [
                'business_id' => $business->id,
                'location_id' => $location->id,
                'customer_id' => $demoCustomer->id,
                'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
                'status' => CommerceOrder::STATUS_PENDING_PAYMENT,
                'payment_status' => CommerceOrder::PAYMENT_UNPAID,
                'fulfillment_type' => CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY,
                'customer_name' => $demoCustomer->name,
                'customer_phone' => $demoCustomer->phone,
                'customer_email' => $demoCustomer->email,
                'shipping_address' => $demoCustomer->shipping_address,
                'subtotal' => (float) $firstProduct->selling_price * 2,
                'shipping_cost' => 15000,
                'total_amount' => ((float) $firstProduct->selling_price * 2) + 15000,
                'tracking_token' => Str::random(64),
                'notes' => 'Mohon dikirim sore hari sebelum jam 5',
                'created_at' => Carbon::now()->subHours(2),
            ]
        );
        CommerceOrderItem::firstOrCreate(
            ['commerce_order_id' => $orderPending->id, 'product_id' => $firstProduct->id],
            [
                'product_name' => $firstProduct->name,
                'quantity' => 2,
                'unit_price' => $firstProduct->selling_price,
                'subtotal' => (float) $firstProduct->selling_price * 2,
            ]
        );

        // 2. Order: Waiting Verification (With Uploaded Payment Proof)
        $orderVerif = CommerceOrder::updateOrCreate(
            ['order_number' => 'ORD-20260915-DEM2'],
            [
                'business_id' => $business->id,
                'location_id' => $location->id,
                'customer_id' => $demoCustomer->id,
                'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
                'status' => CommerceOrder::STATUS_PROOF_SUBMITTED,
                'payment_status' => CommerceOrder::PAYMENT_VERIFYING,
                'fulfillment_type' => CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY,
                'customer_name' => $demoCustomer->name,
                'customer_phone' => $demoCustomer->phone,
                'customer_email' => $demoCustomer->email,
                'shipping_address' => $demoCustomer->shipping_address,
                'subtotal' => (float) $firstProduct->selling_price,
                'shipping_cost' => 10000,
                'total_amount' => (float) $firstProduct->selling_price + 10000,
                'tracking_token' => Str::random(64),
                'notes' => 'Sudah transfer lewat BCA m-banking',
                'created_at' => Carbon::now()->subHours(6),
            ]
        );
        CommerceOrderItem::firstOrCreate(
            ['commerce_order_id' => $orderVerif->id, 'product_id' => $firstProduct->id],
            [
                'product_name' => $firstProduct->name,
                'quantity' => 1,
                'unit_price' => $firstProduct->selling_price,
                'subtotal' => (float) $firstProduct->selling_price,
            ]
        );
        CommercePaymentProof::firstOrCreate(
            ['commerce_order_id' => $orderVerif->id],
            [
                'business_id' => $business->id,
                'file_path' => 'commerce_proofs/sample_proof.jpg',
                'file_size_kb' => 142,
                'mime_type' => 'image/jpeg',
                'sender_bank' => 'Bank Central Asia (BCA)',
                'sender_account_name' => 'Budi Santoso',
                'status' => CommercePaymentProof::STATUS_PENDING,
            ]
        );

        // 3. Order: Processing (Payment Paid)
        $orderProcessing = CommerceOrder::updateOrCreate(
            ['order_number' => 'ORD-20260914-DEM3'],
            [
                'business_id' => $business->id,
                'location_id' => $location->id,
                'customer_id' => $demoCustomer->id,
                'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
                'status' => CommerceOrder::STATUS_PROCESSING,
                'payment_status' => CommerceOrder::PAYMENT_PAID,
                'fulfillment_type' => CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY,
                'customer_name' => $demoCustomer->name,
                'customer_phone' => $demoCustomer->phone,
                'customer_email' => $demoCustomer->email,
                'shipping_address' => $demoCustomer->shipping_address,
                'subtotal' => (float) $firstProduct->selling_price * 3,
                'shipping_cost' => 0,
                'total_amount' => (float) $firstProduct->selling_price * 3,
                'tracking_token' => Str::random(64),
                'notes' => 'Pesanan sedang disiapkan',
                'created_at' => Carbon::now()->subDays(1),
            ]
        );
        CommerceOrderItem::firstOrCreate(
            ['commerce_order_id' => $orderProcessing->id, 'product_id' => $firstProduct->id],
            [
                'product_name' => $firstProduct->name,
                'quantity' => 3,
                'unit_price' => $firstProduct->selling_price,
                'subtotal' => (float) $firstProduct->selling_price * 3,
            ]
        );

        // 4. Order: Completed
        $orderCompleted = CommerceOrder::updateOrCreate(
            ['order_number' => 'ORD-20260910-DEM4'],
            [
                'business_id' => $business->id,
                'location_id' => $location->id,
                'customer_id' => $demoCustomer->id,
                'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
                'status' => CommerceOrder::STATUS_COMPLETED,
                'payment_status' => CommerceOrder::PAYMENT_PAID,
                'fulfillment_type' => CommerceOrder::FULFILLMENT_PICKUP,
                'customer_name' => $demoCustomer->name,
                'customer_phone' => $demoCustomer->phone,
                'customer_email' => $demoCustomer->email,
                'shipping_address' => 'Ambil di Toko',
                'subtotal' => (float) $firstProduct->selling_price * 2,
                'shipping_cost' => 0,
                'total_amount' => (float) $firstProduct->selling_price * 2,
                'tracking_token' => Str::random(64),
                'notes' => 'Pesanan selesai diambil pelanggan',
                'created_at' => Carbon::now()->subDays(5),
            ]
        );
        CommerceOrderItem::firstOrCreate(
            ['commerce_order_id' => $orderCompleted->id, 'product_id' => $firstProduct->id],
            [
                'product_name' => $firstProduct->name,
                'quantity' => 2,
                'unit_price' => $firstProduct->selling_price,
                'subtotal' => (float) $firstProduct->selling_price * 2,
            ]
        );
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
