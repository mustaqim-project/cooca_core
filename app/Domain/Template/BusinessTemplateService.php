<?php

declare(strict_types=1);

namespace App\Domain\Template;

use App\Models\Business;
use App\Models\BusinessTypeTemplate;
use App\Models\CostCategory;
use App\Models\CostComponent;
use App\Models\CostModel;
use App\Models\LaborRate;
use App\Models\Machine;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class BusinessTemplateService
{
    /**
     * Apply an industry template to a business tenant (§35 & §92 Blueprint).
     * Auto generates rich master data tailored to the industry:
     * - Cost Categories & Cost Components
     * - Custom Units (if applicable)
     * - Product Categories (Kategori Produk Jadi)
     * - Material Categories (Kategori Bahan Baku)
     * - Labor Rates (Tenaga Kerja Langsung)
     * - Machines & Equipment (Mesin & Aset)
     * - Materials with Pricing & Yield/Waste
     * - Products (Both WITH BOM raw materials and WITHOUT BOM pure services/retail)
     * Note: Supplier is NOT auto-generated per user requirement.
     *
     * @return array{
     *     categories_created: int,
     *     components_created: int
     * }
     */
    public function apply(Business $business, BusinessTypeTemplate $template): array
    {
        return DB::transaction(function () use ($business, $template): array {
            $categoriesCount = 0;
            $componentsCount = 0;

            // 1. Cost Components & Categories
            $components = $template->default_cost_components;

            foreach ($components as $item) {
                $categoryName = (string) ($item['category'] ?? 'Direct Material');
                $code = Str::slug($categoryName, '_');

                /** @var CostCategory $cat */
                $cat = CostCategory::firstOrCreate(
                    [
                        'business_id' => $business->id,
                        'code' => $code,
                    ],
                    [
                        'name' => $categoryName,
                        'description' => "Default category from template {$template->name}",
                    ]
                );

                if ($cat->wasRecentlyCreated) {
                    $categoriesCount++;
                }

                $componentName = (string) $item['name'];

                $component = CostComponent::firstOrCreate(
                    [
                        'business_id' => $business->id,
                        'cost_category_id' => $cat->id,
                        'name' => $componentName,
                    ],
                    [
                        'slug' => Str::slug($componentName),
                        'behavior' => (string) ($item['behavior'] ?? CostComponent::BEHAVIOR_VARIABLE),
                        'traceability' => (string) ($item['traceability'] ?? CostComponent::TRACEABILITY_DIRECT),
                        'is_system' => false,
                    ]
                );

                if ($component->wasRecentlyCreated) {
                    $componentsCount++;
                }
            }

            // 2. Auto Generate Tailored Master Data for the Industry Template
            $this->seedIndustryMasterData($business, $template);

            return [
                'categories_created' => $categoriesCount,
                'components_created' => $componentsCount,
            ];
        });
    }

    /**
     * Seed realistic units, categories, materials, labor, machines, and products
     * tailored to the chosen industry template (excluding suppliers).
     */
    private function seedIndustryMasterData(Business $business, BusinessTypeTemplate $template): void
    {
        $code = $template->code;

        // Common base units
        $kg = Unit::where('code', 'kg')->first();
        $g = Unit::where('code', 'g')->first();
        $pcs = Unit::where('code', 'pcs')->first();
        $m = Unit::where('code', 'm')->first();
        $cm = Unit::where('code', 'cm')->first();
        $l = Unit::where('code', 'l')->first();
        $ml = Unit::where('code', 'ml')->first();

        // ─────────────────────────────────────────────────────────────
        // A. Custom Units per Industry
        // ─────────────────────────────────────────────────────────────
        $portion = Unit::firstOrCreate(
            ['code' => 'porsi'],
            ['name' => 'Porsi Sajian', 'category' => 'quantity', 'is_base' => false, 'default_precision' => 0]
        );
        $pack = Unit::firstOrCreate(
            ['code' => 'pack'],
            ['name' => 'Pack / Bungkus', 'category' => 'quantity', 'is_base' => false, 'default_precision' => 0]
        );
        $cup = Unit::firstOrCreate(
            ['code' => 'cup'],
            ['name' => 'Cup Gelas', 'category' => 'quantity', 'is_base' => false, 'default_precision' => 0]
        );
        $hour = Unit::firstOrCreate(
            ['code' => 'jam'],
            ['name' => 'Jam Layanan (Hour)', 'category' => 'time', 'is_base' => false, 'default_precision' => 1]
        );
        $project = Unit::firstOrCreate(
            ['code' => 'proyek'],
            ['name' => 'Paket Proyek (Project)', 'category' => 'custom', 'is_base' => false, 'default_precision' => 0]
        );
        $yard = Unit::firstOrCreate(
            ['code' => 'yard'],
            ['name' => 'Yard Kain', 'category' => 'length', 'is_base' => false, 'default_precision' => 2]
        );

        // ─────────────────────────────────────────────────────────────
        // B. Define Industry Data Definitions
        // ─────────────────────────────────────────────────────────────
        $definitions = $this->getIndustryDefinitions($template, [
            'kg' => $kg, 'g' => $g, 'pcs' => $pcs, 'm' => $m, 'cm' => $cm,
            'l' => $l, 'ml' => $ml, 'portion' => $portion, 'pack' => $pack,
            'cup' => $cup, 'hour' => $hour, 'project' => $project, 'yard' => $yard,
        ]);

        // 1. Create Product Categories
        $prodCatMap = [];
        foreach ($definitions['product_categories'] as $pCat) {
            $cat = ProductCategory::updateOrCreate(
                ['business_id' => $business->id, 'slug' => Str::slug($pCat['name'])],
                ['name' => $pCat['name'], 'description' => $pCat['description'] ?? null]
            );
            $prodCatMap[$pCat['key']] = $cat;
        }

        // 2. Create Material Categories
        $matCatMap = [];
        foreach ($definitions['material_categories'] as $mCat) {
            $cat = MaterialCategory::updateOrCreate(
                ['business_id' => $business->id, 'slug' => Str::slug($mCat['name'])],
                ['name' => $mCat['name'], 'description' => $mCat['description'] ?? null]
            );
            $matCatMap[$mCat['key']] = $cat;
        }

        // 3. Create Labor Rates
        $laborMap = [];
        foreach ($definitions['labor_rates'] as $lData) {
            $lr = LaborRate::updateOrCreate(
                ['business_id' => $business->id, 'slug' => Str::slug($lData['name'])],
                [
                    'name' => $lData['name'],
                    'basis' => $lData['basis'] ?? 'monthly',
                    'rate_amount' => $lData['rate_amount'],
                    'working_days_per_month' => $lData['days'] ?? 25,
                    'working_hours_per_day' => $lData['hours'] ?? 8,
                    'utilization_rate' => $lData['utilization'] ?? 80,
                    'is_subcontractor' => $lData['is_subcontractor'] ?? false,
                ]
            );
            $laborMap[$lData['key']] = $lr;
        }

        // 4. Create Machines / Equipment
        $machineMap = [];
        foreach ($definitions['machines'] as $mData) {
            $m = Machine::updateOrCreate(
                ['business_id' => $business->id, 'slug' => Str::slug($mData['name'])],
                [
                    'name' => $mData['name'],
                    'purchase_price' => $mData['price'],
                    'residual_value' => $mData['salvage'] ?? 0,
                    'useful_life_hours' => $mData['life_hours'],
                    'maintenance_cost_per_hour' => $mData['maintenance_hourly'] ?? 0,
                    'electricity_cost_per_hour' => ($mData['power_kw'] ?? 0) * 1500,
                ]
            );
            $machineMap[$mData['key']] = $m;
        }

        // 5. Create Materials & Price History (No Supplier assigned per requirement)
        $materialMap = [];
        foreach ($definitions['materials'] as $mItem) {
            $mat = Material::updateOrCreate(
                ['business_id' => $business->id, 'slug' => Str::slug($mItem['name'])],
                [
                    'name' => $mItem['name'],
                    'code' => $mItem['code'] ?? 'MAT-' . strtoupper(Str::random(5)),
                    'unit_id' => $mItem['unit']->id,
                    'category_id' => isset($mItem['category_key'], $matCatMap[$mItem['category_key']]) ? $matCatMap[$mItem['category_key']]->id : null,
                    'supplier_id' => null, // Explicitly no supplier
                ]
            );

            $mat->prices()->updateOrCreate(
                ['effective_date' => now()->toDateString()],
                [
                    'business_id' => $business->id,
                    'purchase_unit_id' => $mItem['unit']->id,
                    'purchase_price' => $mItem['price'],
                    'yield_percentage' => $mItem['yield'] ?? 100,
                    'waste_percentage' => $mItem['waste'] ?? 0,
                ]
            );

            $materialMap[$mItem['key']] = $mat;
        }

        // 6. Create Products (With and Without BOM) & Run Costing
        $engine = new \App\Domain\Calculation\CalculationEngine;
        $costingService = new \App\Domain\Calculation\CostingResultService;

        foreach ($definitions['products'] as $pData) {
            $product = Product::updateOrCreate(
                ['business_id' => $business->id, 'slug' => Str::slug($pData['name'])],
                [
                    'name' => $pData['name'],
                    'code' => $pData['code'] ?? 'PRD-' . strtoupper(Str::random(5)),
                    'category_id' => isset($pData['category_key'], $prodCatMap[$pData['category_key']]) ? $prodCatMap[$pData['category_key']]->id : null,
                    'output_unit_id' => $pData['unit']->id,
                    'business_type_hint' => $template->industry_category,
                    'selling_price' => (float) ($pData['selling_price'] ?? 35000),
                    'base_cost' => (float) ($pData['base_cost'] ?? 15000),
                ]
            );

            $costModel = CostModel::updateOrCreate(
                ['business_id' => $business->id, 'product_id' => $product->id, 'slug' => 'hpp-' . Str::slug($pData['name'])],
                [
                    'name' => 'Model HPP ' . $pData['name'],
                    'method' => $pData['method'],
                    'is_active' => true,
                ]
            );

            // Attach Labor if present
            if (! empty($pData['labor_key']) && isset($laborMap[$pData['labor_key']])) {
                $costModel->labors()->updateOrCreate(
                    ['labor_rate_id' => $laborMap[$pData['labor_key']]->id],
                    [
                        'quantity' => 1,
                        'regular_hours' => $pData['labor_hours'] ?? 0.1,
                        'overtime_hours' => 0,
                    ]
                );
            }

            // Attach Machine if present
            if (! empty($pData['machine_key']) && isset($machineMap[$pData['machine_key']])) {
                $costModel->machines()->updateOrCreate(
                    ['machine_id' => $machineMap[$pData['machine_key']]->id],
                    [
                        'hours_used' => $pData['machine_hours'] ?? 0.1,
                    ]
                );
            }

            // Attach BOM Items if has raw materials
            if (! empty($pData['items'])) {
                $bomHeader = $costModel->bomHeaders()->firstOrCreate(
                    ['cost_model_id' => $costModel->id],
                    [
                        'name' => 'Resep & BOM ' . $pData['name'],
                        'type' => \App\Models\BomHeader::TYPE_RECIPE,
                        'level' => 1,
                    ]
                );

                foreach ($pData['items'] as $item) {
                    if (isset($materialMap[$item['mat_key']])) {
                        $matObj = $materialMap[$item['mat_key']];
                        $bomHeader->items()->updateOrCreate(
                            ['material_id' => $matObj->id],
                            [
                                'quantity' => $item['qty'],
                                'unit_id' => $item['unit']->id,
                                'waste_percentage' => $item['waste'] ?? 0,
                            ]
                        );
                    }
                }
            }

            // Calculate and persist initial HPP baseline
            try {
                $costModel->load(['bomHeaders.items.material.prices', 'bomHeaders.items.unit', 'labors.laborRate', 'machines.machine', 'product.outputUnit']);
                $dto = $engine->calculate($costModel);
                $costingService->persist($costModel, $dto, 'template_seed');
                if ($dto->hppPerUnit > 0) {
                    $product->update(['base_cost' => round($dto->hppPerUnit, 2)]);
                }
            } catch (\Throwable) {
                // Silently continue
            }
        }
    }

    /**
     * Define comprehensive industry-tailored data for categories, labor, machines, materials, and products.
     *
     * @param  array<string, Unit>  $u
     * @return array<string, mixed>
     */
    private function getIndustryDefinitions(BusinessTypeTemplate $template, array $u): array
    {
        $code = $template->code;

        // Default generic manufacturing/FNB structure as robust fallback
        $productCategories = [
            ['key' => 'main', 'name' => 'Produk Utama / Menu Utama', 'description' => "Kategori produk utama {$template->name}"],
            ['key' => 'secondary', 'name' => 'Produk Pelengkap & Tambahan', 'description' => 'Produk sampingan atau pelengkap'],
            ['key' => 'service', 'name' => 'Jasa Layanan & Konsultasi', 'description' => 'Layanan jasa tanpa bahan baku fisik'],
            ['key' => 'retail', 'name' => 'Barang Jadi Retail / Dagang', 'description' => 'Produk beli-jual siap edar'],
        ];

        $materialCategories = [
            ['key' => 'raw', 'name' => 'Bahan Baku Pokok', 'description' => 'Bahan mentah utama produksi'],
            ['key' => 'addon', 'name' => 'Bahan Pembantu & Bumbu', 'description' => 'Bahan pelengkap dan pengolah'],
            ['key' => 'pack', 'name' => 'Kemasan & Wadah (Packaging)', 'description' => 'Packaging dan kemasan akhir'],
        ];

        $laborRates = [
            ['key' => 'operator', 'name' => 'Tenaga Produksi / Koki Utama', 'rate_amount' => 4200000, 'days' => 25, 'hours' => 8, 'utilization' => 80],
            ['key' => 'assistant', 'name' => 'Staff Pembantu / Kitchen Prep', 'rate_amount' => 3000000, 'days' => 25, 'hours' => 8, 'utilization' => 80],
            ['key' => 'service_staff', 'name' => 'Staff Pelayan / Teknisi Layanan', 'rate_amount' => 2800000, 'days' => 25, 'hours' => 8, 'utilization' => 75],
        ];

        $machines = [
            ['key' => 'primary_mac', 'name' => 'Mesin Produksi Utama', 'price' => 15000000, 'life_hours' => 10000, 'power_kw' => 1.5, 'maintenance_hourly' => 1200],
            ['key' => 'facility_ac', 'name' => 'Peralatan & AC Ruang Kerja', 'price' => 8000000, 'life_hours' => 12000, 'power_kw' => 1.2, 'maintenance_hourly' => 800],
        ];

        $materials = [
            ['key' => 'mat1', 'name' => 'Bahan Mentah Utama A', 'code' => 'MAT-RAW-01', 'category_key' => 'raw', 'unit' => $u['kg'] ?: $u['pcs'], 'price' => 35000, 'yield' => 95, 'waste' => 3],
            ['key' => 'mat2', 'name' => 'Bahan Pendukung B', 'code' => 'MAT-ADD-01', 'category_key' => 'addon', 'unit' => $u['kg'] ?: $u['pcs'], 'price' => 22000, 'yield' => 98, 'waste' => 2],
            ['key' => 'mat_pack', 'name' => 'Kemasan Box Standar', 'code' => 'MAT-PCK-01', 'category_key' => 'pack', 'unit' => $u['pcs'], 'price' => 1200, 'yield' => 100, 'waste' => 1],
            ['key' => 'mat_retail', 'name' => 'Barang Dagang Retail Jadi', 'code' => 'MAT-RET-01', 'category_key' => 'raw', 'unit' => $u['pack'], 'price' => 8500, 'yield' => 100, 'waste' => 0],
        ];

        $products = [
            // Product 1: With BOM Raw Materials
            [
                'name' => 'Paket Produk Standar ' . $template->name,
                'code' => 'PRD-STD-01',
                'category_key' => 'main',
                'unit' => $u['portion'] ?: $u['pcs'],
                'method' => CostModel::METHOD_RECIPE_BOM,
                'labor_key' => 'operator',
                'labor_hours' => 0.15,
                'machine_key' => 'primary_mac',
                'machine_hours' => 0.1,
                'items' => [
                    ['mat_key' => 'mat1', 'qty' => 0.2, 'unit' => $u['kg'] ?: $u['pcs'], 'waste' => 2],
                    ['mat_key' => 'mat2', 'qty' => 0.05, 'unit' => $u['kg'] ?: $u['pcs'], 'waste' => 1],
                    ['mat_key' => 'mat_pack', 'qty' => 1, 'unit' => $u['pcs'], 'waste' => 0],
                ],
            ],
            // Product 2: With BOM Raw Materials (Premium)
            [
                'name' => 'Paket Spesial / Premium ' . $template->name,
                'code' => 'PRD-PRM-01',
                'category_key' => 'secondary',
                'unit' => $u['portion'] ?: $u['pcs'],
                'method' => CostModel::METHOD_RECIPE_BOM,
                'labor_key' => 'operator',
                'labor_hours' => 0.2,
                'machine_key' => 'primary_mac',
                'machine_hours' => 0.15,
                'items' => [
                    ['mat_key' => 'mat1', 'qty' => 0.35, 'unit' => $u['kg'] ?: $u['pcs'], 'waste' => 3],
                    ['mat_key' => 'mat2', 'qty' => 0.1, 'unit' => $u['kg'] ?: $u['pcs'], 'waste' => 2],
                    ['mat_key' => 'mat_pack', 'qty' => 1, 'unit' => $u['pcs'], 'waste' => 0],
                ],
            ],
            // Product 3: WITHOUT RAW MATERIALS (Pure Service / Konsultasi / Layanan)
            [
                'name' => 'Jasa Layanan / Konsultasi ' . $template->name . ' (per Jam)',
                'code' => 'SRV-HRS-01',
                'category_key' => 'service',
                'unit' => $u['hour'],
                'method' => CostModel::METHOD_SERVICE,
                'labor_key' => 'service_staff',
                'labor_hours' => 1.0,
                'machine_key' => 'facility_ac',
                'machine_hours' => 1.0,
                'items' => [], // Tanpa Bahan Baku!
            ],
            // Product 4: WITHOUT RAW MATERIALS (Retail Dagang Beli-Jual)
            [
                'name' => 'Barang Dagangan Retail Pelengkap',
                'code' => 'RET-SLS-01',
                'category_key' => 'retail',
                'unit' => $u['pack'],
                'method' => CostModel::METHOD_RETAIL,
                'labor_key' => null,
                'machine_key' => null,
                'items' => [
                    ['mat_key' => 'mat_retail', 'qty' => 1, 'unit' => $u['pack'], 'waste' => 0],
                ],
            ],
        ];

        return [
            'product_categories' => $productCategories,
            'material_categories' => $materialCategories,
            'labor_rates' => $laborRates,
            'machines' => $machines,
            'materials' => $materials,
            'products' => $products,
        ];
    }
}

