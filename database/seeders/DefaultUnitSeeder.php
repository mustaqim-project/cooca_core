<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Unit;
use App\Models\UnitConversion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class DefaultUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Units
        $units = [
            // Weight (Base: Gram)
            ['code' => 'g', 'name' => 'Gram', 'category' => Unit::CATEGORY_WEIGHT, 'is_base' => true, 'precision' => 2],
            ['code' => 'kg', 'name' => 'Kilogram', 'category' => Unit::CATEGORY_WEIGHT, 'is_base' => false, 'precision' => 3],
            ['code' => 'mg', 'name' => 'Milligram', 'category' => Unit::CATEGORY_WEIGHT, 'is_base' => false, 'precision' => 0],
            ['code' => 'ton', 'name' => 'Metric Ton', 'category' => Unit::CATEGORY_WEIGHT, 'is_base' => false, 'precision' => 3],

            // Volume (Base: Milliliter)
            ['code' => 'ml', 'name' => 'Milliliter', 'category' => Unit::CATEGORY_VOLUME, 'is_base' => true, 'precision' => 0],
            ['code' => 'l', 'name' => 'Liter', 'category' => Unit::CATEGORY_VOLUME, 'is_base' => false, 'precision' => 3],

            // Length (Base: Meter)
            ['code' => 'm', 'name' => 'Meter', 'category' => Unit::CATEGORY_LENGTH, 'is_base' => true, 'precision' => 2],
            ['code' => 'cm', 'name' => 'Centimeter', 'category' => Unit::CATEGORY_LENGTH, 'is_base' => false, 'precision' => 1],
            ['code' => 'mm', 'name' => 'Millimeter', 'category' => Unit::CATEGORY_LENGTH, 'is_base' => false, 'precision' => 0],

            // Quantity (Base: Pcs)
            ['code' => 'pcs', 'name' => 'Pieces / Butir / Buah', 'category' => Unit::CATEGORY_QUANTITY, 'is_base' => true, 'precision' => 0],
            ['code' => 'lusin', 'name' => 'Lusin (12 pcs)', 'category' => Unit::CATEGORY_QUANTITY, 'is_base' => false, 'precision' => 0],
            ['code' => 'gross', 'name' => 'Gross (144 pcs)', 'category' => Unit::CATEGORY_QUANTITY, 'is_base' => false, 'precision' => 0],
            ['code' => 'kodi', 'name' => 'Kodi (20 pcs)', 'category' => Unit::CATEGORY_QUANTITY, 'is_base' => false, 'precision' => 0],
            ['code' => 'rim', 'name' => 'Rim (500 lbr)', 'category' => Unit::CATEGORY_QUANTITY, 'is_base' => false, 'precision' => 0],

            // Time (Base: Jam)
            ['code' => 'jam', 'name' => 'Jam / Hour', 'category' => Unit::CATEGORY_TIME, 'is_base' => true, 'precision' => 2],
            ['code' => 'menit', 'name' => 'Menit / Minute', 'category' => Unit::CATEGORY_TIME, 'is_base' => false, 'precision' => 1],
            ['code' => 'detik', 'name' => 'Detik / Second', 'category' => Unit::CATEGORY_TIME, 'is_base' => false, 'precision' => 0],
            ['code' => 'hari', 'name' => 'Hari / Day', 'category' => Unit::CATEGORY_TIME, 'is_base' => false, 'precision' => 1],

            // Area (Base: Meter Persegi)
            ['code' => 'm2', 'name' => 'Meter Persegi (m²)', 'category' => Unit::CATEGORY_AREA, 'is_base' => true, 'precision' => 2],
            ['code' => 'cm2', 'name' => 'Centimeter Persegi (cm²)', 'category' => Unit::CATEGORY_AREA, 'is_base' => false, 'precision' => 0],
        ];

        $unitMap = [];
        foreach ($units as $u) {
            $unit = Unit::firstOrCreate(
                ['business_id' => null, 'code' => $u['code']],
                [
                    'id' => (string) Str::uuid(),
                    'name' => $u['name'],
                    'category' => $u['category'],
                    'is_base' => $u['is_base'],
                    'default_precision' => $u['precision'],
                ]
            );
            $unitMap[$u['code']] = $unit->id;
        }

        // 2. Seed Conversions
        $conversions = [
            // Weight
            ['from' => 'kg', 'to' => 'g', 'factor' => 1000.0],
            ['from' => 'g', 'to' => 'mg', 'factor' => 1000.0],
            ['from' => 'ton', 'to' => 'kg', 'factor' => 1000.0],

            // Volume
            ['from' => 'l', 'to' => 'ml', 'factor' => 1000.0],

            // Length
            ['from' => 'm', 'to' => 'cm', 'factor' => 100.0],
            ['from' => 'cm', 'to' => 'mm', 'factor' => 10.0],

            // Quantity
            ['from' => 'lusin', 'to' => 'pcs', 'factor' => 12.0],
            ['from' => 'gross', 'to' => 'pcs', 'factor' => 144.0],
            ['from' => 'kodi', 'to' => 'pcs', 'factor' => 20.0],
            ['from' => 'rim', 'to' => 'pcs', 'factor' => 500.0],

            // Time
            ['from' => 'jam', 'to' => 'menit', 'factor' => 60.0],
            ['from' => 'menit', 'to' => 'detik', 'factor' => 60.0],
            ['from' => 'hari', 'to' => 'jam', 'factor' => 24.0],

            // Area
            ['from' => 'm2', 'to' => 'cm2', 'factor' => 10000.0],
        ];

        foreach ($conversions as $c) {
            if (isset($unitMap[$c['from']], $unitMap[$c['to']])) {
                UnitConversion::firstOrCreate(
                    [
                        'business_id' => null,
                        'from_unit_id' => $unitMap[$c['from']],
                        'to_unit_id' => $unitMap[$c['to']],
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'factor' => $c['factor'],
                    ]
                );
            }
        }

        // 3. Seed Currencies
        $currencies = [
            ['code' => 'IDR', 'name' => 'Indonesian Rupiah', 'symbol' => 'Rp', 'is_base' => true, 'decimals' => 0],
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'is_base' => false, 'decimals' => 2],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'is_base' => false, 'decimals' => 2],
            ['code' => 'SGD', 'name' => 'Singapore Dollar', 'symbol' => 'S$', 'is_base' => false, 'decimals' => 2],
        ];

        foreach ($currencies as $curr) {
            Currency::firstOrCreate(
                ['code' => $curr['code']],
                [
                    'id' => (string) Str::uuid(),
                    'name' => $curr['name'],
                    'symbol' => $curr['symbol'],
                    'is_base' => $curr['is_base'],
                    'decimal_places' => $curr['decimals'],
                ]
            );
        }
    }
}
