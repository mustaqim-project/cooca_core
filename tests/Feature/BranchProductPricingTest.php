<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BranchProductPrice;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\Location;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BranchProductPricingTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private Product $product;
    private Location $outlet1;
    private Location $outlet2;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->owner = User::create([
            'name' => 'Juragan Premium',
            'email' => 'juragan.premium@cooca.id',
            'password' => 'password123',
            'email_verified_at' => now(),
            'phone' => '6281234567891',
        ]);

        $this->business = Business::create([
            'name' => 'Kopi Mantap Multi Cabang',
            'slug' => 'kopi-mantap-multi-cabang',
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $unit = Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Cup',
            'code' => 'CUP',
            'symbol' => 'cup',
            'category' => 'piece',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Kopi Susu Gula Aren',
            'code' => 'KOP-001',
            'selling_price' => 20000,
            'base_cost' => 8000,
            'output_unit_id' => $unit->id,
            'is_active' => true,
        ]);

        $this->outlet1 = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Cabang Tebet',
            'code' => 'TBT-01',
            'type' => 'outlet',
            'is_active' => true,
        ]);

        $this->outlet2 = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Cabang Mall Senayan',
            'code' => 'SNY-01',
            'type' => 'outlet',
            'is_active' => true,
        ]);
    }

    public function test_starter_tier_is_blocked_by_entitlement_middleware(): void
    {
        BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_code' => BusinessSubscription::PLAN_STANDARD_MONTHLY,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
        ]);

        $response = $this->actingAs($this->owner)
            ->getJson(route('products.branch_prices.index', $this->product->id));

        $response->assertStatus(403)
            ->assertJson([
                'code' => 'RESOURCE_LIMIT_EXCEEDED',
            ]);
    }

    public function test_premium_tier_can_view_and_save_branch_prices(): void
    {
        BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_code' => BusinessSubscription::PLAN_PREMIUM_MONTHLY,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
        ]);

        // 1. GET branch prices
        $getResponse = $this->actingAs($this->owner)
            ->getJson(route('products.branch_prices.index', $this->product->id));

        $getResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('product.id', $this->product->id)
            ->assertJsonCount(2, 'branches');

        // 2. POST update branch prices (Cabang Mall Senayan has higher price)
        $postResponse = $this->actingAs($this->owner)
            ->postJson(route('products.branch_prices.update', $this->product->id), [
                'prices' => [
                    [
                        'location_id' => $this->outlet2->id,
                        'price' => 28000,
                        'cost_price' => 9500,
                        'is_available' => true,
                        'reset' => false,
                    ],
                ],
            ]);

        $postResponse->assertOk()
            ->assertJsonPath('success', true);

        // Verify database state
        $this->assertDatabaseHas('branch_product_prices', [
            'business_id' => $this->business->id,
            'product_id' => $this->product->id,
            'location_id' => $this->outlet2->id,
            'price' => 28000,
            'cost_price' => 9500,
            'is_available' => true,
        ]);

        // Verify Tebet still has no override
        $this->assertDatabaseMissing('branch_product_prices', [
            'product_id' => $this->product->id,
            'location_id' => $this->outlet1->id,
        ]);

        // 3. Reset price override for Senayan
        $resetResponse = $this->actingAs($this->owner)
            ->postJson(route('products.branch_prices.update', $this->product->id), [
                'prices' => [
                    [
                        'location_id' => $this->outlet2->id,
                        'reset' => true,
                    ],
                ],
            ]);

        $resetResponse->assertOk();
        $this->assertDatabaseMissing('branch_product_prices', [
            'product_id' => $this->product->id,
            'location_id' => $this->outlet2->id,
        ]);
    }

    public function test_can_set_product_availability_without_custom_price(): void
    {
        BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_code' => BusinessSubscription::PLAN_PREMIUM_MONTHLY,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
        ]);

        // 1. Disable product at Outlet 2 (Senayan/Rest Area) without setting custom price
        $response = $this->actingAs($this->owner)
            ->postJson(route('products.branch_prices.update', $this->product->id), [
                'prices' => [
                    [
                        'location_id' => $this->outlet2->id,
                        'is_available' => false,
                        'price' => null,
                        'cost_price' => null,
                    ],
                ],
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        // Verify record exists with is_available = false and null price
        $this->assertDatabaseHas('branch_product_prices', [
            'business_id' => $this->business->id,
            'product_id' => $this->product->id,
            'location_id' => $this->outlet2->id,
            'is_available' => false,
            'price' => null,
        ]);

        // 2. Fetch via GET and verify metadata
        $getResponse = $this->actingAs($this->owner)
            ->getJson(route('products.branch_prices.index', $this->product->id));

        $getResponse->assertOk();
        $branches = collect($getResponse->json('branches'));
        $senayanBranch = $branches->firstWhere('location_id', $this->outlet2->id);

        $this->assertNotNull($senayanBranch);
        $this->assertFalse($senayanBranch['is_available']);
        $this->assertTrue($senayanBranch['has_override']);
        $this->assertFalse($senayanBranch['has_price_override']);
        // Price should still fallback to master price for display
        $this->assertEquals((float) $this->product->selling_price, (float) $senayanBranch['price']);
    }

    public function test_tenant_isolation_prevents_access_to_other_business_products(): void
    {
        BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_code' => BusinessSubscription::PLAN_PREMIUM_MONTHLY,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
        ]);

        $otherBusiness = Business::create([
            'name' => 'Bisnis Lain',
            'slug' => 'bisnis-lain',
        ]);

        $otherUnit = Unit::create([
            'business_id' => $otherBusiness->id,
            'name' => 'Pcs',
            'code' => 'PCS',
            'symbol' => 'pcs',
            'category' => 'piece',
            'is_active' => true,
        ]);

        $otherProduct = Product::create([
            'business_id' => $otherBusiness->id,
            'name' => 'Produk Luar',
            'code' => 'OUT-999',
            'selling_price' => 50000,
            'base_cost' => 20000,
            'output_unit_id' => $otherUnit->id,
        ]);

        // Attempting to access other business product must return 404
        $response = $this->actingAs($this->owner)
            ->getJson(route('products.branch_prices.index', $otherProduct->id));

        $response->assertNotFound();
    }
}
