<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPromo;
use App\Models\SubscriptionPromoUsage;
use App\Models\User;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionPromoSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
    }

    public function test_admin_can_view_promos_index(): void
    {
        $admin = Admin::factory()->create([
            'email' => 'admin@cooca.test',
        ]);

        SubscriptionPromo::create([
            'code' => 'TEST30',
            'name' => 'Diskon Uji 30%',
            'discount_type' => SubscriptionPromo::TYPE_PERCENTAGE,
            'discount_value' => 30,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.promos.index'));

        $response->assertOk();
        $response->assertSee('TEST30');
        $response->assertSee('Diskon Uji 30%');
    }

    public function test_admin_can_create_new_promo(): void
    {
        $admin = Admin::factory()->create([
            'email' => 'admin@cooca.test',
        ]);

        $payload = [
            'code' => 'KILAT50',
            'name' => 'Flash Sale 50%',
            'description' => 'Diskon kilat khusus bulan ini',
            'discount_type' => 'percentage',
            'discount_value' => 50,
            'max_discount_amount' => 100000,
            'min_order_amount' => 50000,
            'usage_limit' => 50,
            'usage_per_business_limit' => 1,
            'is_active' => 1,
        ];

        $response = $this->actingAs($admin, 'admin')->post(route('admin.promos.store'), $payload);

        $response->assertRedirect(route('admin.promos.index'));
        $this->assertDatabaseHas('subscription_promos', [
            'code' => 'KILAT50',
            'discount_type' => 'percentage',
            'discount_value' => 50,
        ]);
    }

    public function test_admin_can_toggle_promo_status(): void
    {
        $admin = Admin::factory()->create([
            'email' => 'admin@cooca.test',
        ]);

        $promo = SubscriptionPromo::create([
            'code' => 'TOGGLEME',
            'name' => 'Promo Uji Toggle',
            'discount_type' => SubscriptionPromo::TYPE_FIXED,
            'discount_value' => 25000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')->from(route('admin.promos.index'))->post(route('admin.promos.toggle', $promo));

        $response->assertRedirect(route('admin.promos.index'));
        $this->assertFalse((bool) $promo->fresh()->is_active);

        // Toggle back to active
        $this->actingAs($admin, 'admin')->from(route('admin.promos.index'))->post(route('admin.promos.toggle', $promo));
        $this->assertTrue((bool) $promo->fresh()->is_active);
    }

    public function test_admin_can_delete_promo(): void
    {
        $admin = Admin::factory()->create([
            'email' => 'admin@cooca.test',
        ]);

        $promo = SubscriptionPromo::create([
            'code' => 'DELETEME',
            'name' => 'Promo Hapus',
            'discount_type' => SubscriptionPromo::TYPE_FIXED,
            'discount_value' => 10000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')->delete(route('admin.promos.destroy', $promo));

        $response->assertRedirect(route('admin.promos.index'));
        $this->assertDatabaseMissing('subscription_promos', [
            'id' => $promo->id,
        ]);
    }

    public function test_validate_promo_endpoint_returns_discount_details(): void
    {
        $user = User::factory()->create();
        $business = Business::create([
            'name' => 'Bisnis Validasi Promo',
            'slug' => 'bisnis-validasi-promo',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);
        $business->users()->attach($user->id, ['role' => 'owner']);

        SubscriptionPromo::create([
            'code' => 'HEMAT20',
            'name' => 'Hemat 20%',
            'discount_type' => SubscriptionPromo::TYPE_PERCENTAGE,
            'discount_value' => 20,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['active_business_id' => $business->id])
            ->postJson(route('billing.promo.validate'), [
                'code' => 'hemat20',
                'tier' => BusinessSubscription::TIER_PREMIUM,
                'cycle' => 'monthly',
                'order_type' => 'subscription',
            ]);

        $response->assertOk();
        $response->assertJson([
            'valid' => true,
            'promo' => [
                'code' => 'HEMAT20',
                'discount_type' => 'percentage',
                'discount_value' => 20.0,
            ],
        ]);
        $this->assertGreaterThan(0, $response->json('discount_amount'));
    }

    public function test_validate_promo_fails_for_inactive_or_invalid_code(): void
    {
        $user = User::factory()->create();
        $business = Business::create([
            'name' => 'Bisnis Invalid Promo',
            'slug' => 'bisnis-invalid-promo',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);
        $business->users()->attach($user->id, ['role' => 'owner']);

        SubscriptionPromo::create([
            'code' => 'NONAKTIF',
            'name' => 'Promo Mati',
            'discount_type' => SubscriptionPromo::TYPE_FIXED,
            'discount_value' => 10000,
            'is_active' => false,
        ]);

        // Non-existent code
        $respNotFound = $this->actingAs($user)
            ->withSession(['active_business_id' => $business->id])
            ->postJson(route('billing.promo.validate'), [
                'code' => 'TIDAKADA',
            ]);
        $respNotFound->assertStatus(404);

        // Inactive code
        $respInactive = $this->actingAs($user)
            ->withSession(['active_business_id' => $business->id])
            ->postJson(route('billing.promo.validate'), [
                'code' => 'NONAKTIF',
            ]);
        $respInactive->assertStatus(422);
    }

    public function test_checkout_with_100_percent_promo_activates_immediately_and_records_usage(): void
    {
        $user = User::factory()->create();
        $business = Business::create([
            'name' => 'Bisnis Promo Gratis',
            'slug' => 'bisnis-promo-gratis',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);
        $business->users()->attach($user->id, ['role' => 'owner']);

        $promo = SubscriptionPromo::create([
            'code' => 'FREE100',
            'name' => 'Diskon 100% Khusus',
            'discount_type' => SubscriptionPromo::TYPE_PERCENTAGE,
            'discount_value' => 100,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['active_business_id' => $business->id])
            ->post(route('billing.order.store'), [
                'order_type' => 'subscription',
                'tier' => BusinessSubscription::TIER_PREMIUM,
                'cycle' => 'monthly',
                'payment_method' => SubscriptionPayment::METHOD_FREE_PROMO,
                'promo_code' => 'FREE100',
            ]);

        $response->assertRedirect();

        // Verify promo usage was recorded
        $this->assertDatabaseHas('subscription_promo_usages', [
            'promo_id' => $promo->id,
            'business_id' => $business->id,
        ]);

        // Verify promo counter incremented
        $this->assertEquals(1, $promo->fresh()->used_count);

        // Verify payment record
        $payment = SubscriptionPayment::where('business_id', $business->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(SubscriptionPayment::STATUS_APPROVED, $payment->status);
        $this->assertEquals('FREE100', $payment->promo_code);
        $this->assertEquals(0.0, (float) $payment->final_amount);
        $this->assertGreaterThan(0.0, (float) $payment->discount_amount);
        $this->assertEquals($payment->amount, $payment->discount_amount);
    }
}
