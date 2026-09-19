<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Billing\EntitlementService;
use App\Models\BranchProductPrice;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\Location;
use App\Models\PosTable;
use App\Models\Product;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TierLimitsAndQuotasTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private EntitlementService $entitlementService;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->entitlementService = new EntitlementService();

        $this->owner = User::create([
            'name' => 'Juragan UMKM',
            'email' => 'juragan@cooca.id',
            'password' => 'password123',
            'email_verified_at' => now(),
            'phone' => '6281234567891',
        ]);

        $this->business = Business::create([
            'name' => 'Warung Kopi Mantap',
            'slug' => 'warung-kopi-mantap',
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);
    }

    public function test_business_count_limit_per_owner_across_tiers(): void
    {
        // 1. Free/Standard tier: max 1 business per owner
        $this->assertSame(1, $this->entitlementService->getBusinessLimit($this->business));
        $this->assertFalse($this->entitlementService->canCreateBusiness($this->owner));

        // 2. Upgrade to Premium tier: max 3 businesses per owner
        BusinessSubscription::updateOrCreate(
            ['business_id' => $this->business->id],
            [
                'plan_code' => BusinessSubscription::PLAN_PREMIUM_MONTHLY,
                'status' => BusinessSubscription::STATUS_ACTIVE,
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
            ]
        );
        $this->business->unsetRelation('subscription');

        $this->assertSame(3, $this->entitlementService->getBusinessLimit($this->business));
        $this->assertTrue($this->entitlementService->canCreateBusiness($this->owner));

        // Create 2 more businesses for this owner
        $b2 = Business::create(['name' => 'Cabang Kopi 2', 'slug' => 'cabang-kopi-2']);
        $b2->users()->attach($this->owner->id, ['id' => (string) Str::uuid(), 'role' => 'owner', 'is_active' => true]);

        $b3 = Business::create(['name' => 'Cabang Kopi 3', 'slug' => 'cabang-kopi-3']);
        $b3->users()->attach($this->owner->id, ['id' => (string) Str::uuid(), 'role' => 'owner', 'is_active' => true]);

        // Now owner has 3 businesses: cannot create 4th on Premium
        $this->assertFalse($this->entitlementService->canCreateBusiness($this->owner));

        // 3. Upgrade to Prestige tier: unlimited businesses
        BusinessSubscription::updateOrCreate(
            ['business_id' => $this->business->id],
            [
                'plan_code' => BusinessSubscription::PLAN_PRESTIGE_MONTHLY,
            ]
        );
        $this->business->unsetRelation('subscription');

        $this->assertNull($this->entitlementService->getBusinessLimit($this->business));
        $this->assertTrue($this->entitlementService->canCreateBusiness($this->owner));
    }

    public function test_pos_table_limits_enforced_strictly_by_tier(): void
    {
        // 1. Free plan: 0 tables allowed
        $this->assertSame(0, $this->entitlementService->getTableLimit($this->business));
        $this->assertFalse($this->entitlementService->canCreateTable($this->business));

        // 2. Standard plan: 5 tables allowed
        BusinessSubscription::updateOrCreate(
            ['business_id' => $this->business->id],
            [
                'plan_code' => BusinessSubscription::PLAN_STANDARD_MONTHLY,
                'status' => BusinessSubscription::STATUS_ACTIVE,
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
            ]
        );
        $this->business->unsetRelation('subscription');

        $this->assertSame(5, $this->entitlementService->getTableLimit($this->business));
        $this->assertTrue($this->entitlementService->canCreateTable($this->business));

        // Create 5 tables
        for ($i = 1; $i <= 5; $i++) {
            PosTable::create([
                'business_id' => $this->business->id,
                'name' => "Meja 0{$i}",
                'table_number' => "M0{$i}",
                'capacity' => 4,
                'status' => 'available',
            ]);
        }

        // 6th table must be blocked
        $this->assertFalse($this->entitlementService->canCreateTable($this->business));

        // 3. Premium tier: unlimited tables
        BusinessSubscription::updateOrCreate(
            ['business_id' => $this->business->id],
            [
                'plan_code' => BusinessSubscription::PLAN_PREMIUM_MONTHLY,
            ]
        );
        $this->business->unsetRelation('subscription');

        $this->assertNull($this->entitlementService->getTableLimit($this->business));
        $this->assertTrue($this->entitlementService->canCreateTable($this->business));
    }

    public function test_branch_specific_pricing_and_fallback_mechanism(): void
    {
        $location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Mall Grand Indonesia',
            'code' => 'OUT-MALL-GI',
            'type' => 'outlet',
            'is_active' => true,
        ]);

        $unit = \App\Models\Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Cup',
            'code' => 'CUP',
            'symbol' => 'cup',
            'category' => 'piece',
            'is_active' => true,
        ]);

        $product = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $unit->id,
            'name' => 'Kopi Susu Gula Aren',
            'code' => 'KOP-001',
            'selling_price' => 18000.0,
            'base_cost' => 8000.0,
            'is_active' => true,
        ]);

        // Base price is 18.000
        $this->assertEquals(18000.0, $product->selling_price);

        // Create branch-specific price of 22.000 for Mall location
        $branchPrice = BranchProductPrice::create([
            'business_id' => $this->business->id,
            'product_id' => $product->id,
            'location_id' => $location->id,
            'price' => 22000.0,
            'cost_price' => 9000.0,
            'is_available' => true,
        ]);

        $this->assertDatabaseHas('branch_product_prices', [
            'id' => $branchPrice->id,
            'price' => 22000.0,
        ]);

        // Branch price resolves for mall outlet
        $lookupPrice = BranchProductPrice::where('product_id', $product->id)
            ->where('location_id', $location->id)
            ->where('is_available', true)
            ->first()?->price ?? $product->selling_price;

        $this->assertEquals(22000.0, $lookupPrice);

        // Different location resolves to fallback base price
        $otherLocationId = (string) Str::uuid();
        $otherPrice = BranchProductPrice::where('product_id', $product->id)
            ->where('location_id', $otherLocationId)
            ->where('is_available', true)
            ->first()?->price ?? $product->selling_price;

        $this->assertEquals(18000.0, $otherPrice);
    }

    public function test_grace_period_keeps_cashier_operational(): void
    {
        // Subscription expired 1 day ago -> Past Due (Grace Period Days 1-3)
        $sub = $this->business->subscription()->create([
            'id' => (string) Str::uuid(),
            'plan_code' => BusinessSubscription::PLAN_PREMIUM_MONTHLY,
            'status' => BusinessSubscription::STATUS_PAST_DUE,
            'starts_at' => now()->subMonth()->subDay(),
            'ends_at' => now()->subDay(),
            'grace_period_ends_at' => now()->addDays(2),
        ]);

        $this->assertTrue($sub->isPastDue());
        $this->assertTrue($sub->isOperational(), 'Cashier POS must remain operational during 3-day grace period!');

        // Day 4+ -> Expired
        $sub->update([
            'status' => BusinessSubscription::STATUS_EXPIRED,
            'grace_period_ends_at' => now()->subDay(),
        ]);

        $this->assertFalse($sub->isPastDue());
        $this->assertFalse($sub->isOperational());
    }

    public function test_checkout_standard_premium_prestige_prices_without_unique_code_overhead(): void
    {
        $this->actingAs($this->owner);

        // 1. Standard monthly checkout: Rp 29.000
        $this->post(route('billing.order.store'), [
            'cycle' => 'monthly',
            'tier' => 'standard',
            'payment_method' => 'qris',
        ]);

        $payment1 = SubscriptionPayment::where('business_id', $this->business->id)
            ->where('payment_method', 'qris')
            ->first();
        $this->assertNotNull($payment1);
        $this->assertEquals(29000, $payment1->amount);
        $this->assertEquals(0, $payment1->unique_code, 'QRIS Gateway has 0 unique code overhead');
        $this->assertEquals(29000, $payment1->total_payable);

        // 2. Premium annual checkout: Rp 890.000
        $this->post(route('billing.order.store'), [
            'cycle' => 'annual',
            'tier' => 'premium',
            'payment_method' => 'bca_va',
        ]);

        $payment2 = SubscriptionPayment::where('business_id', $this->business->id)
            ->where('payment_method', 'bca_va')
            ->first();
        $this->assertNotNull($payment2);
        $this->assertEquals(890000, $payment2->amount);
        $this->assertEquals(0, $payment2->unique_code, 'Virtual Account has 0 unique code overhead');
        $this->assertEquals(890000, $payment2->total_payable);

        // 3. Prestige monthly checkout: Rp 199.000
        $this->post(route('billing.order.store'), [
            'cycle' => 'monthly',
            'tier' => 'prestige',
            'payment_method' => 'mandiri_va',
        ]);

        $payment3 = SubscriptionPayment::where('business_id', $this->business->id)
            ->where('payment_method', 'mandiri_va')
            ->first();
        $this->assertNotNull($payment3);
        $this->assertEquals(199000, $payment3->amount);
        $this->assertEquals(0, $payment3->unique_code);
        $this->assertEquals(199000, $payment3->total_payable);
    }
}
