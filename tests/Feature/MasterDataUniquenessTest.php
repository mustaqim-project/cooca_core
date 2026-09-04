<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Material;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * BUG-001: Manual create Material/Product tidak boleh menghasilkan duplicate code/SKU.
 * Database harus menjadi proteksi akhir (unique constraint).
 */
final class MasterDataUniquenessTest extends TestCase
{
    use RefreshDatabase;

    private Business $businessA;
    private Business $businessB;
    private User $user;
    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create(['name' => 'Owner', 'email' => 'owner@example.com', 'password' => 'secret123']);
        $this->businessA = Business::create(['name' => 'Bisnis A']);
        $this->businessB = Business::create(['name' => 'Bisnis B']);
        foreach ([$this->businessA, $this->businessB] as $biz) {
            $biz->users()->attach($this->user->id, ['id' => (string) Str::uuid(), 'role' => 'owner', 'is_active' => true]);
        }
        $this->user->update(['active_business_id' => $this->businessA->id]);

        $this->unit = Unit::create(['business_id' => $this->businessA->id, 'code' => 'PCS', 'name' => 'Pcs', 'symbol' => 'pcs', 'category' => 'quantity']);
        Unit::create(['business_id' => $this->businessB->id, 'code' => 'PCS', 'name' => 'Pcs', 'symbol' => 'pcs', 'category' => 'quantity']);
    }

    public function test_duplicate_material_code_is_rejected(): void
    {
        $this->actingAs($this->user);
        Context::setBusiness($this->businessA);
        session(['active_business_id' => $this->businessA->id]);

        $payload = [
            'name' => 'Panci 20cm',
            'sku' => 'MAT-001',
            'unit_id' => $this->unit->id,
            'purchase_price' => 80000,
            'yield_percentage' => 100,
            'waste_percentage' => 0,
        ];

        $this->post(route('materials.store'), $payload)->assertSessionHasNoErrors();

        // Duplikat harus ditolak oleh validasi unique + DB.
        $this->post(route('materials.store'), $payload)->assertSessionHasErrors('sku');

        $this->assertSame(1, Material::where('business_id', $this->businessA->id)->where('code', 'MAT-001')->count());
    }

    public function test_duplicate_product_code_is_rejected(): void
    {
        $this->actingAs($this->user);
        Context::setBusiness($this->businessA);
        session(['active_business_id' => $this->businessA->id]);

        $payload = [
            'name' => 'Panci Premium',
            'sku' => 'PRD-001',
            'output_unit_id' => $this->unit->id,
            'costing_method' => 'simple',
            'selling_price' => 150000,
        ];

        $this->post(route('products.store'), $payload)->assertSessionHasNoErrors();

        $this->post(route('products.store'), $payload)->assertSessionHasErrors('sku');

        $this->assertSame(1, Product::where('business_id', $this->businessA->id)->where('code', 'PRD-001')->count());
    }

    public function test_same_code_is_allowed_in_different_business(): void
    {
        $this->actingAs($this->user);

        Context::setBusiness($this->businessA);
        session(['active_business_id' => $this->businessA->id]);
        $this->post(route('materials.store'), [
            'name' => 'Bahan A', 'sku' => 'MAT-001',
            'unit_id' => $this->unit->id, 'purchase_price' => 10,
            'yield_percentage' => 100, 'waste_percentage' => 0,
        ])->assertSessionHasNoErrors();

        Context::setBusiness($this->businessB);
        session(['active_business_id' => $this->businessB->id]);
        $unitB = Unit::where('business_id', $this->businessB->id)->firstOrFail();
        $this->post(route('materials.store'), [
            'name' => 'Bahan A (B)', 'sku' => 'MAT-001',
            'unit_id' => $unitB->id, 'purchase_price' => 10,
            'yield_percentage' => 100, 'waste_percentage' => 0,
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, Material::withoutGlobalScopes()->where('business_id', $this->businessA->id)->where('code', 'MAT-001')->count());
        $this->assertSame(1, Material::withoutGlobalScopes()->where('business_id', $this->businessB->id)->where('code', 'MAT-001')->count());
    }

    public function test_database_unique_constraint_blocks_race_duplicate(): void
    {
        Context::setBusiness($this->businessA);

        Material::create(['business_id' => $this->businessA->id, 'code' => 'MAT-RACE', 'name' => 'Racel', 'unit_id' => $this->unit->id]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Material::create(['business_id' => $this->businessA->id, 'code' => 'MAT-RACE', 'name' => 'Race2', 'unit_id' => $this->unit->id]);
    }
}