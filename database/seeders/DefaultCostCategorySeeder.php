<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CostCategory;
use App\Models\CostComponent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class DefaultCostCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'code' => CostCategory::CODE_DIRECT_MATERIAL,
                'name' => 'Direct Material (Bahan Baku Langsung)',
                'description' => 'Bahan mentah atau komponen fisik yang langsung membentuk produk jadi.',
                'components' => [
                    [
                        'name' => 'Bahan Baku Pokok',
                        'slug' => 'raw-materials',
                        'behavior' => CostComponent::BEHAVIOR_VARIABLE,
                        'traceability' => CostComponent::TRACEABILITY_DIRECT,
                    ],
                    [
                        'name' => 'Kemasan Primer (Primary Packaging)',
                        'slug' => 'primary-packaging',
                        'behavior' => CostComponent::BEHAVIOR_VARIABLE,
                        'traceability' => CostComponent::TRACEABILITY_DIRECT,
                    ],
                ],
            ],
            [
                'code' => CostCategory::CODE_DIRECT_LABOR,
                'name' => 'Direct Labor (Tenaga Kerja Langsung)',
                'description' => 'Upah tenaga kerja yang terlibat langsung dalam proses produksi/pengerjaan produk.',
                'components' => [
                    [
                        'name' => 'Upah Tukang / Operator Produksi',
                        'slug' => 'direct-production-labor',
                        'behavior' => CostComponent::BEHAVIOR_VARIABLE,
                        'traceability' => CostComponent::TRACEABILITY_DIRECT,
                    ],
                ],
            ],
            [
                'code' => CostCategory::CODE_VARIABLE_OVERHEAD,
                'name' => 'Variable Overhead (Overhead Variabel)',
                'description' => 'Biaya overhead yang berubah sebanding dengan volume output/produksi.',
                'components' => [
                    [
                        'name' => 'Listrik & Gas Operasional Produksi',
                        'slug' => 'production-utilities-variable',
                        'behavior' => CostComponent::BEHAVIOR_VARIABLE,
                        'traceability' => CostComponent::TRACEABILITY_INDIRECT,
                    ],
                    [
                        'name' => 'Bahan Penolong (Consumables)',
                        'slug' => 'production-consumables',
                        'behavior' => CostComponent::BEHAVIOR_VARIABLE,
                        'traceability' => CostComponent::TRACEABILITY_INDIRECT,
                    ],
                ],
            ],
            [
                'code' => CostCategory::CODE_FIXED_OVERHEAD,
                'name' => 'Fixed Overhead (Overhead Tetap / Pabrikasi)',
                'description' => 'Biaya kapasitas dan fasilitas tetap yang dialokasikan ke output.',
                'components' => [
                    [
                        'name' => 'Penyusutan Mesin & Peralatan',
                        'slug' => 'equipment-depreciation',
                        'behavior' => CostComponent::BEHAVIOR_FIXED,
                        'traceability' => CostComponent::TRACEABILITY_INDIRECT,
                    ],
                    [
                        'name' => 'Sewa Tempat Produksi / Dapur / Workshop',
                        'slug' => 'facility-rent',
                        'behavior' => CostComponent::BEHAVIOR_FIXED,
                        'traceability' => CostComponent::TRACEABILITY_INDIRECT,
                    ],
                ],
            ],
            [
                'code' => CostCategory::CODE_OTHER,
                'name' => 'Other / Non-Production Costs',
                'description' => 'Biaya operasional pendukung, pemasaran, distribusi, atau komisi.',
                'components' => [
                    [
                        'name' => 'Biaya Pemasaran & Iklan (Opex Warning)',
                        'slug' => 'marketing-advertising',
                        'behavior' => CostComponent::BEHAVIOR_FIXED,
                        'traceability' => CostComponent::TRACEABILITY_INDIRECT,
                    ],
                ],
            ],
        ];

        foreach ($categories as $catData) {
            $category = CostCategory::firstOrCreate(
                ['business_id' => null, 'code' => $catData['code']],
                [
                    'id' => (string) Str::uuid(),
                    'name' => $catData['name'],
                    'description' => $catData['description'],
                ]
            );

            foreach ($catData['components'] as $compData) {
                CostComponent::firstOrCreate(
                    [
                        'business_id' => null,
                        'cost_category_id' => $category->id,
                        'slug' => $compData['slug'],
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'name' => $compData['name'],
                        'behavior' => $compData['behavior'],
                        'traceability' => $compData['traceability'],
                        'is_system' => true,
                    ]
                );
            }
        }
    }
}
