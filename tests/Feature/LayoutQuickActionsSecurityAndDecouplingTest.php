<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Expense;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Material;
use App\Models\MaterialPrice;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class LayoutQuickActionsSecurityAndDecouplingTest extends TestCase
{
    use RefreshDatabase;

    private Business $businessA;
    private Business $businessB;
    private User $userA;
    private User $userB;
    private Location $locationA;
    private Unit $unitA;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        Cache::flush();

        $this->userA = User::create([
            'name' => 'Owner Bisnis A',
            'email' => 'owner_a@cooca.id',
            'password' => 'password123',
        ]);

        $this->businessA = Business::create([
            'name' => 'Kedai Kopi A',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'pos_supervisor_pin' => Hash::make('889900'),
        ]);

        $this->businessA->users()->attach($this->userA->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->userA->update(['active_business_id' => $this->businessA->id]);

        $this->locationA = Location::create([
            'business_id' => $this->businessA->id,
            'name' => 'Cabang Pusat A',
            'is_active' => true,
        ]);

        $this->unitA = Unit::create([
            'business_id' => $this->businessA->id,
            'code' => 'KG',
            'name' => 'Kilogram',
            'symbol' => 'kg',
            'category' => Unit::CATEGORY_WEIGHT,
        ]);

        // Setup Tenant B
        $this->userB = User::create([
            'name' => 'Owner Bisnis B',
            'email' => 'owner_b@cooca.id',
            'password' => 'password123',
        ]);

        $this->businessB = Business::create([
            'name' => 'Apotek Sehat B',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
        ]);

        $this->businessB->users()->attach($this->userB->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->userB->update(['active_business_id' => $this->businessB->id]);
    }

    public function test_quick_expense_idempotency_prevents_duplicate_transactions(): void
    {
        $idempotencyKey = 'test-idemp-exp-' . Str::uuid();

        $payload = [
            'name' => 'Beli Gas LPG 3kg',
            'amount' => 22000,
            'category' => 'Operasional Toko',
            'payment_method' => 'cash',
        ];

        // First Request
        $response1 = $this->actingAs($this->userA)
            ->withHeader('X-Idempotency-Key', $idempotencyKey)
            ->postJson(route('dashboard.quick-expense'), $payload);

        $response1->assertOk()
            ->assertJson(['success' => true]);

        $this->assertEquals(1, Expense::where('business_id', $this->businessA->id)->count());

        // Repeated Request with same Idempotency Key
        $response2 = $this->actingAs($this->userA)
            ->withHeader('X-Idempotency-Key', $idempotencyKey)
            ->postJson(route('dashboard.quick-expense'), $payload);

        $response2->assertOk()
            ->assertJson(['success' => true]);

        // Crucial Anti-Fraud Assertion: Still 1 expense in database, NO DUPLICATES!
        $this->assertEquals(1, Expense::where('business_id', $this->businessA->id)->count());
    }

    public function test_quick_expense_requires_supervisor_pin_when_amount_exceeds_threshold(): void
    {
        $largeAmount = 750000; // > 500,000 threshold

        // 1. Request without PIN should be rejected with 422 and requires_pin flag
        $responseNoPin = $this->actingAs($this->userA)
            ->postJson(route('dashboard.quick-expense'), [
                'name' => 'Service Mesin Kopi Espresso',
                'amount' => $largeAmount,
                'category' => 'Operasional Toko',
            ]);

        $responseNoPin->assertStatus(422)
            ->assertJson([
                'success' => false,
                'requires_pin' => true,
            ]);

        // 2. Request with invalid PIN should fail
        $responseWrongPin = $this->actingAs($this->userA)
            ->postJson(route('dashboard.quick-expense'), [
                'name' => 'Service Mesin Kopi Espresso',
                'amount' => $largeAmount,
                'supervisor_pin' => '000000',
            ]);

        $responseWrongPin->assertStatus(422)
            ->assertJson([
                'success' => false,
                'requires_pin' => true,
            ]);

        // 3. Request with valid PIN (889900) should succeed
        $responseValidPin = $this->actingAs($this->userA)
            ->postJson(route('dashboard.quick-expense'), [
                'name' => 'Service Mesin Kopi Espresso',
                'amount' => $largeAmount,
                'supervisor_pin' => '889900',
            ]);

        $responseValidPin->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('expenses', [
            'business_id' => $this->businessA->id,
            'amount' => $largeAmount,
            'description' => 'Service Mesin Kopi Espresso',
        ]);
    }

    public function test_quick_stock_in_idempotency_prevents_duplicate_stock_increment(): void
    {
        $material = Material::create([
            'business_id' => $this->businessA->id,
            'name' => 'Biji Kopi Arabika Gayo',
            'code' => 'MAT-GAYO',
            'unit_id' => $this->unitA->id,
            'is_active' => true,
        ]);

        MaterialPrice::create([
            'business_id' => $this->businessA->id,
            'material_id' => $material->id,
            'purchase_price' => 120000,
            'purchase_unit_id' => $this->unitA->id,
            'effective_date' => now()->toDateString(),
        ]);

        $idempotencyKey = 'test-idemp-stock-' . Str::uuid();

        $payload = [
            'material_id' => $material->id,
            'quantity' => 5,
            'unit_cost' => 120000,
            'supplier_name' => 'Petani Gayo',
        ];

        // 1st stock in request
        $res1 = $this->actingAs($this->userA)
            ->withHeader('X-Idempotency-Key', $idempotencyKey)
            ->postJson(route('dashboard.quick-stock-in'), $payload);

        $res1->assertOk()
            ->assertJson(['success' => true]);

        $stock = InventoryStock::where('business_id', $this->businessA->id)->first();
        $this->assertNotNull($stock);
        $this->assertEquals(5, (float) $stock->quantity);

        // 2nd repeated stock in request with same key
        $res2 = $this->actingAs($this->userA)
            ->withHeader('X-Idempotency-Key', $idempotencyKey)
            ->postJson(route('dashboard.quick-stock-in'), $payload);

        $res2->assertOk()
            ->assertJson(['success' => true]);

        // Stock quantity must remain 5, not 10!
        $stock->refresh();
        $this->assertEquals(5, (float) $stock->quantity);
    }

    public function test_quick_materials_list_is_strictly_tenant_isolated_and_cached(): void
    {
        // Material in Tenant A
        $matA = Material::create([
            'business_id' => $this->businessA->id,
            'name' => 'Susu Segar UHT A',
            'code' => 'MAT-SUSU-A',
            'unit_id' => $this->unitA->id,
            'is_active' => true,
        ]);

        $unitB = Unit::create([
            'business_id' => $this->businessB->id,
            'code' => 'TAB',
            'name' => 'Tablet',
            'symbol' => 'tab',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);

        // Material in Tenant B
        $matB = Material::create([
            'business_id' => $this->businessB->id,
            'name' => 'Obat Paracetamol B',
            'code' => 'MAT-OBAT-B',
            'unit_id' => $unitB->id,
            'is_active' => true,
        ]);

        // User A fetches materials
        $responseA = $this->actingAs($this->userA)
            ->getJson(route('dashboard.quick-materials-list'));

        $responseA->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonFragment(['name' => 'Susu Segar UHT A'])
            ->assertJsonMissing(['name' => 'Obat Paracetamol B']);

        // Verify cached
        $this->assertTrue(Cache::has("quick_materials_list_{$this->businessA->id}"));

        // User B fetches materials
        $responseB = $this->actingAs($this->userB)
            ->getJson(route('dashboard.quick-materials-list'));

        $responseB->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonFragment(['name' => 'Obat Paracetamol B'])
            ->assertJsonMissing(['name' => 'Susu Segar UHT A']);
    }

    public function test_quick_material_creation_invalidates_cache_and_enforces_idempotency(): void
    {
        $idempotencyKey = 'test-idemp-mat-' . Str::uuid();

        $payload = [
            'name' => 'Gula Aren Organik',
            'cost_per_unit' => 25000,
            'unit_id' => $this->unitA->id,
        ];

        // 1st request
        $res1 = $this->actingAs($this->userA)
            ->withHeader('X-Idempotency-Key', $idempotencyKey)
            ->postJson(route('dashboard.quick-material'), $payload);

        $res1->assertOk()
            ->assertJson(['success' => true]);

        $this->assertEquals(1, Material::where('business_id', $this->businessA->id)->where('name', 'Gula Aren Organik')->count());

        // 2nd repeated request with same key
        $res2 = $this->actingAs($this->userA)
            ->withHeader('X-Idempotency-Key', $idempotencyKey)
            ->postJson(route('dashboard.quick-material'), $payload);

        $res2->assertOk()
            ->assertJson(['success' => true]);

        // Still exactly 1 material
        $this->assertEquals(1, Material::where('business_id', $this->businessA->id)->where('name', 'Gula Aren Organik')->count());
    }
}
