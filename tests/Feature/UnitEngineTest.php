<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Material\UnitConversionService;
use App\Models\Business;
use App\Models\Material;
use App\Models\MaterialUnitConversion;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\UnitConversion;
use App\Models\User;
use App\Rules\SameUnitCategory;
use App\Support\Context;
use App\Support\RoundingService;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Tests\TestCase;

final class UnitEngineTest extends TestCase
{
    use RefreshDatabase;

    private UnitConversionService $conversionService;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);
        $this->conversionService = new UnitConversionService;
    }

    public function test_default_units_and_conversions_are_seeded(): void
    {
        $this->assertDatabaseHas('units', ['code' => 'kg', 'category' => Unit::CATEGORY_WEIGHT]);
        $this->assertDatabaseHas('units', ['code' => 'g', 'category' => Unit::CATEGORY_WEIGHT, 'is_base' => true]);
        $this->assertDatabaseHas('units', ['code' => 'ml', 'category' => Unit::CATEGORY_VOLUME, 'is_base' => true]);
        $this->assertDatabaseHas('currencies', ['code' => 'IDR', 'is_base' => true]);
    }

    public function test_direct_conversion_weight(): void
    {
        $kg = Unit::where('code', 'kg')->firstOrFail();
        $g = Unit::where('code', 'g')->firstOrFail();

        $result = $this->conversionService->convert(2.5, $kg, $g);
        $this->assertEqualsWithDelta(2500.0, $result, 0.0001);
    }

    public function test_inverse_conversion_weight(): void
    {
        $g = Unit::where('code', 'g')->firstOrFail();
        $kg = Unit::where('code', 'kg')->firstOrFail();

        $result = $this->conversionService->convert(500.0, $g, $kg);
        $this->assertEqualsWithDelta(0.5, $result, 0.0001);
    }

    public function test_chained_conversion_through_category_base_unit(): void
    {
        // 1 ton = 1000 kg, 1 kg = 1000 g. Therefore 2 ton = 2,000,000 g.
        $ton = Unit::where('code', 'ton')->firstOrFail();
        $g = Unit::where('code', 'g')->firstOrFail();

        $result = $this->conversionService->convert(2.0, $ton, $g);
        $this->assertEqualsWithDelta(2000000.0, $result, 0.0001);
    }

    public function test_volume_conversion(): void
    {
        $l = Unit::where('code', 'l')->firstOrFail();
        $ml = Unit::where('code', 'ml')->firstOrFail();

        $result = $this->conversionService->convert(1.75, $l, $ml);
        $this->assertEqualsWithDelta(1750.0, $result, 0.0001);
    }

    public function test_quantity_conversion(): void
    {
        $lusin = Unit::where('code', 'lusin')->firstOrFail();
        $pcs = Unit::where('code', 'pcs')->firstOrFail();

        $result = $this->conversionService->convert(3.0, $lusin, $pcs);
        $this->assertEqualsWithDelta(36.0, $result, 0.0001);
    }

    public function test_time_conversion(): void
    {
        $jam = Unit::where('code', 'jam')->firstOrFail();
        $menit = Unit::where('code', 'menit')->firstOrFail();

        $result = $this->conversionService->convert(1.5, $jam, $menit);
        $this->assertEqualsWithDelta(90.0, $result, 0.0001);
    }

    public function test_custom_unit_creation_and_custom_conversion(): void
    {
        $user = User::create(['name' => 'Owner', 'email' => 'custom_unit@example.com', 'password' => 'password123']);
        $business = Business::create(['name' => 'Pabrik Semen']);
        $business->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        Context::setBusiness($business);

        $kg = Unit::where('code', 'kg')->firstOrFail();
        $g = Unit::where('code', 'g')->firstOrFail();

        // Create custom unit 'sak'
        $sak = Unit::create([
            'business_id' => $business->id,
            'code' => 'sak',
            'name' => 'Sak Semen 50kg',
            'category' => Unit::CATEGORY_CUSTOM,
            'default_precision' => 0,
        ]);

        // 1 sak = 50 kg
        UnitConversion::create([
            'business_id' => $business->id,
            'from_unit_id' => $sak->id,
            'to_unit_id' => $kg->id,
            'factor' => 50.0,
        ]);

        // Convert 2 sak -> kg
        $kgResult = $this->conversionService->convert(2.0, $sak, $kg);
        $this->assertEqualsWithDelta(100.0, $kgResult, 0.0001);
    }

    public function test_material_specific_conversion_for_packaging_units_is_supported(): void
    {
        $business = Business::create(['name' => 'Batik Kecil']);
        $user = User::create(['name' => 'Owner', 'email' => 'packaging@example.com', 'password' => 'password123']);
        $business->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        Context::setBusiness($business);

        $box = Unit::create([
            'business_id' => $business->id,
            'code' => 'box',
            'name' => 'Kotak',
            'category' => Unit::CATEGORY_CUSTOM,
            'default_precision' => 0,
        ]);

        $pcs = Unit::create([
            'business_id' => $business->id,
            'code' => 'pcs',
            'name' => 'Pieces',
            'category' => Unit::CATEGORY_QUANTITY,
            'is_base' => true,
            'default_precision' => 0,
        ]);

        $supplier = Supplier::create([
            'business_id' => $business->id,
            'name' => 'Supplier Packaging',
            'slug' => 'supplier-packaging',
        ]);

        $material = Material::create([
            'business_id' => $business->id,
            'category_id' => null,
            'unit_id' => $box->id,
            'supplier_id' => $supplier->id,
            'code' => 'MAT-001',
            'name' => 'Telur',
            'slug' => 'telur',
        ]);

        MaterialUnitConversion::create([
            'business_id' => $business->id,
            'material_id' => $material->id,
            'supplier_id' => $supplier->id,
            'from_unit_id' => $box->id,
            'to_unit_id' => $pcs->id,
            'factor' => 12.0,
            'is_default' => true,
            'effective_from' => now()->toDateString(),
        ]);

        $result = $this->conversionService->convert(3.0, $box, $pcs, $material->id, $supplier->id);
        $this->assertEqualsWithDelta(36.0, $result, 0.0001);
    }

    public function test_cross_category_conversion_without_bridge_is_rejected(): void
    {
        $kg = Unit::where('code', 'kg')->firstOrFail();
        $liter = Unit::where('code', 'l')->firstOrFail();

        $this->expectException(InvalidArgumentException::class);
        $this->conversionService->convert(10.0, $kg, $liter);
    }

    public function test_rounding_service_respects_unit_precision(): void
    {
        $kg = Unit::where('code', 'kg')->firstOrFail(); // precision 3
        $pcs = Unit::where('code', 'pcs')->firstOrFail(); // precision 0

        $this->assertSame(1.235, RoundingService::roundQty(1.23456, $kg));
        $this->assertSame('1.235', RoundingService::formatQty(1.23456, $kg));

        $this->assertSame(5.0, RoundingService::roundQty(5.49, $pcs));
        $this->assertSame('5', RoundingService::formatQty(5.0, $pcs));
    }

    public function test_api_convert_endpoint_returns_accurate_results(): void
    {
        $kg = Unit::where('code', 'kg')->firstOrFail();
        $g = Unit::where('code', 'g')->firstOrFail();

        $response = $this->postJson('/api/v1/units/convert', [
            'qty' => 3.75,
            'from_unit_id' => $kg->id,
            'to_unit_id' => $g->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('formatted_qty', '3750.00');

        $this->assertEqualsWithDelta(3750.0, (float) $response->json('converted_qty'), 0.001);
    }

    public function test_system_default_units_cannot_be_deleted(): void
    {
        $user = User::create(['name' => 'Owner', 'email' => 'del_unit@example.com', 'password' => 'password123']);
        $business = Business::create(['name' => 'Toko Unit']);
        $business->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $user->update(['active_business_id' => $business->id]);

        $systemKg = Unit::where('code', 'kg')->whereNull('business_id')->firstOrFail();

        $response = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Business-Id', $business->id)
            ->deleteJson("/api/v1/units/{$systemKg->id}");

        $response->assertForbidden();
    }

    public function test_currencies_endpoint_returns_list(): void
    {
        $response = $this->getJson('/api/v1/currencies');

        $response->assertOk()
            ->assertJsonStructure(['currencies' => [['id', 'code', 'name', 'symbol', 'is_base']]]);
    }

    public function test_same_unit_category_validation_rule(): void
    {
        $kg = Unit::where('code', 'kg')->firstOrFail();
        $g = Unit::where('code', 'g')->firstOrFail();
        $liter = Unit::where('code', 'l')->firstOrFail();

        // Valid same category (weight -> weight)
        $validatorValid = Validator::make(
            ['unit_a' => $kg->id],
            ['unit_a' => [new SameUnitCategory($g->id)]]
        );
        $this->assertFalse($validatorValid->fails());

        // Invalid cross-category (weight -> volume)
        $validatorInvalid = Validator::make(
            ['unit_a' => $kg->id],
            ['unit_a' => [new SameUnitCategory($liter->id)]]
        );
        $this->assertTrue($validatorInvalid->fails());
    }
}
