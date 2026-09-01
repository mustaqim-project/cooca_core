<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Material\MaterialCostService;
use App\Domain\Material\UnitConversionService;
use App\Models\Business;
use App\Models\Material;
use App\Models\MaterialPrice;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class MaterialEngineTest extends TestCase
{
    use RefreshDatabase;

    private MaterialCostService $costService;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);
        $this->costService = new MaterialCostService(new UnitConversionService);
    }

    public function test_supplier_and_material_crud_with_slug_and_tenant_isolation(): void
    {
        $userA = User::create(['name' => 'User A', 'email' => 'usera_mat@example.com', 'password' => 'password123']);
        $bizA = Business::create(['name' => 'Business Resto A']);
        $bizA->users()->attach($userA->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $userA->update(['active_business_id' => $bizA->id]);

        $userB = User::create(['name' => 'User B', 'email' => 'userb_mat@example.com', 'password' => 'password123']);
        $bizB = Business::create(['name' => 'Business Resto B']);
        $bizB->users()->attach($userB->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $userB->update(['active_business_id' => $bizB->id]);

        $kg = Unit::where('code', 'kg')->firstOrFail();

        // User A creates supplier & material
        $supA = $this->actingAs($userA, 'sanctum')
            ->withHeader('X-Business-Id', $bizA->id)
            ->postJson('/api/v1/suppliers', [
                'name' => 'Supplier Beras Jaya',
                'contact_person' => 'Pak Budi',
            ])
            ->assertCreated()
            ->json('supplier.id');

        $matA = $this->actingAs($userA, 'sanctum')
            ->withHeader('X-Business-Id', $bizA->id)
            ->postJson('/api/v1/materials', [
                'name' => 'Beras Pandan Wangi',
                'unit_id' => $kg->id,
                'supplier_id' => $supA,
            ])
            ->assertCreated()
            ->json('material.id');

        // User B cannot see User A's material in list
        $responseB = $this->actingAs($userB, 'sanctum')
            ->withHeader('X-Business-Id', $bizB->id)
            ->getJson('/api/v1/materials');

        $responseB->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_material_historical_prices_are_retained_without_overwrite(): void
    {
        $user = User::create(['name' => 'Owner', 'email' => 'price_hist@example.com', 'password' => 'password123']);
        $biz = Business::create(['name' => 'Kafe Kopi']);
        $biz->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $user->update(['active_business_id' => $biz->id]);
        Context::setBusiness($biz);

        $kg = Unit::where('code', 'kg')->firstOrFail();

        $material = Material::create([
            'business_id' => $biz->id,
            'name' => 'Biji Kopi Arabika',
            'unit_id' => $kg->id,
        ]);

        // Price 1 (Januari)
        $this->actingAs($user, 'sanctum')
            ->withHeader('X-Business-Id', $biz->id)
            ->postJson("/api/v1/materials/{$material->slug}/prices", [
                'purchase_price' => 120000,
                'purchase_unit_id' => $kg->id,
                'effective_date' => '2026-01-01',
            ])
            ->assertCreated();

        // Price 2 (Februari - Naiknya harga)
        $this->actingAs($user, 'sanctum')
            ->withHeader('X-Business-Id', $biz->id)
            ->postJson("/api/v1/materials/{$material->slug}/prices", [
                'purchase_price' => 135000,
                'purchase_unit_id' => $kg->id,
                'effective_date' => '2026-02-01',
            ])
            ->assertCreated();

        // Verify both price records exist in database
        $this->assertCount(2, $material->fresh()->prices);
        $this->assertSame(135000.0, (float) $material->fresh()->latestPrice->purchase_price);
    }

    public function test_effective_acquisition_cost_calculation(): void
    {
        $kg = Unit::where('code', 'kg')->firstOrFail();

        $price = new MaterialPrice([
            'purchase_price' => 100000.0,
            'shipping_cost' => 10000.0,
            'handling_cost' => 5000.0,
            'import_cost' => 5000.0,
            'discount_amount' => 10000.0,
            'purchase_unit_id' => $kg->id,
            'yield_percentage' => 100.0,
            'waste_percentage' => 0.0,
        ]);
        $price->setRelation('purchaseUnit', $kg);

        // 100k + 10k + 5k + 5k - 10k = 110k
        $totalAcquisition = $this->costService->calculateEffectiveAcquisitionCost($price);
        $this->assertEqualsWithDelta(110000.0, $totalAcquisition, 0.01);
    }

    public function test_effective_unit_cost_with_yield_and_unit_normalization(): void
    {
        $kg = Unit::where('code', 'kg')->firstOrFail();
        $g = Unit::where('code', 'g')->firstOrFail();

        // 10 kg dibeli dengan total landed 110,000, yield 80% (8 kg usable)
        $price = new MaterialPrice([
            'purchase_price' => 100000.0,
            'shipping_cost' => 10000.0,
            'purchase_unit_id' => $kg->id,
            'yield_percentage' => 80.0, // 80%
            'waste_percentage' => 0.0,
        ]);
        $price->setRelation('purchaseUnit', $kg);

        // Effective Cost per KG = 110,000 / 0.8 = 137,500 / kg
        $costPerKg = $this->costService->calculateEffectiveUnitCost($price, $kg);
        $this->assertEqualsWithDelta(137500.0, $costPerKg, 0.01);

        // Normalized to gram = 137,500 * 0.001 = 137.5 / gram
        $costPerGram = $this->costService->calculateEffectiveUnitCost($price, $g);
        $this->assertEqualsWithDelta(137.5, $costPerGram, 0.001);
    }

    public function test_combined_yield_and_waste_calculation(): void
    {
        // Purchase 10 kg, yield 90% -> 9 kg, waste 5% -> 9 * 0.95 = 8.55 kg usable
        $netUsable = $this->costService->calculateUsableQuantity(10.0, 90.0, 5.0);
        $this->assertEqualsWithDelta(8.55, $netUsable, 0.001);
    }

    public function test_yield_over_100_requires_explicit_flag(): void
    {
        $user = User::create(['name' => 'Owner', 'email' => 'yield_test@example.com', 'password' => 'password123']);
        $biz = Business::create(['name' => 'Bakery Mengembang']);
        $biz->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $user->update(['active_business_id' => $biz->id]);
        Context::setBusiness($biz);

        $kg = Unit::where('code', 'kg')->firstOrFail();
        $material = Material::create(['business_id' => $biz->id, 'name' => 'Adonan Roti', 'unit_id' => $kg->id]);

        // Attempt without flag -> 422
        $this->actingAs($user, 'sanctum')
            ->withHeader('X-Business-Id', $biz->id)
            ->postJson("/api/v1/materials/{$material->slug}/prices", [
                'purchase_price' => 50000,
                'purchase_unit_id' => $kg->id,
                'yield_percentage' => 120.0,
                'effective_date' => '2026-03-01',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('error_code', 'YIELD_OVER_100_CONFIRMATION_REQUIRED');

        // With flag -> 201 Created
        $this->actingAs($user, 'sanctum')
            ->withHeader('X-Business-Id', $biz->id)
            ->postJson("/api/v1/materials/{$material->slug}/prices", [
                'purchase_price' => 50000,
                'purchase_unit_id' => $kg->id,
                'yield_percentage' => 120.0,
                'allow_yield_over_100' => true,
                'effective_date' => '2026-03-01',
            ])
            ->assertCreated();
    }

    public function test_discontinued_material_state_handling(): void
    {
        $kg = Unit::where('code', 'kg')->firstOrFail();
        $biz = Business::create(['name' => 'Test Discontinued']);

        $material = Material::create([
            'business_id' => $biz->id,
            'name' => 'Bahan Langka',
            'unit_id' => $kg->id,
            'discontinued_at' => now()->subDay(),
        ]);

        $this->assertTrue($material->isDiscontinued());
    }

    public function test_effective_cost_api_endpoint(): void
    {
        $user = User::create(['name' => 'Owner', 'email' => 'api_cost@example.com', 'password' => 'password123']);
        $biz = Business::create(['name' => 'Resto API']);
        $biz->users()->attach($user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $user->update(['active_business_id' => $biz->id]);
        Context::setBusiness($biz);

        $kg = Unit::where('code', 'kg')->firstOrFail();
        $g = Unit::where('code', 'g')->firstOrFail();

        $material = Material::create(['business_id' => $biz->id, 'name' => 'Daging Sapi Segar', 'unit_id' => $kg->id]);

        MaterialPrice::create([
            'business_id' => $biz->id,
            'material_id' => $material->id,
            'purchase_price' => 150000,
            'shipping_cost' => 10000,
            'purchase_unit_id' => $kg->id,
            'yield_percentage' => 100.0,
            'effective_date' => '2026-01-01',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Business-Id', $biz->id)
            ->getJson("/api/v1/materials/{$material->slug}/effective-cost?target_unit_id={$g->id}");

        $response->assertOk();

        $this->assertEqualsWithDelta(160000.0, (float) $response->json('effective_cost.total_acquisition_cost'), 0.01);
        $this->assertEqualsWithDelta(160.0, (float) $response->json('effective_cost.effective_cost_per_target_unit'), 0.01);
    }
}
