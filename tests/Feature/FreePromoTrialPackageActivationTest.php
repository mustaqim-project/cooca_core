<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Billing\EntitlementService;
use App\Models\Admin;
use App\Models\AiTokenTopup;
use App\Models\BillingPackage;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class FreePromoTrialPackageActivationTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Promo Owner',
            'email' => 'promo-owner@test.local',
            'password' => Hash::make('password'),
        ]);

        $this->business = Business::create([
            'name' => 'Toko Promo Makmur',
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->admin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin-promo@test.local',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_create_free_promo_trial_subscription_package(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.billing-packages.store'), [
            'type' => BillingPackage::TYPE_SUBSCRIPTION,
            'name' => 'Promo 15 Hari Trial Pro',
            'price' => 0,
            'duration_days' => 15,
            'token_quantity' => 1000000,
            'description' => 'Promo Marketing Spesial Akses Bebas 15 Hari',
            'sort_order' => 1,
        ]);

        $response->assertRedirect(route('admin.billing-packages.index', BillingPackage::TYPE_SUBSCRIPTION));

        $this->assertDatabaseHas('billing_packages', [
            'type' => BillingPackage::TYPE_SUBSCRIPTION,
            'name' => 'Promo 15 Hari Trial Pro',
            'price' => 0,
            'duration_days' => 15,
            'is_active' => true,
        ]);
    }

    public function test_checkout_page_renders_free_promo_package_properly(): void
    {
        $package = BillingPackage::create([
            'type' => BillingPackage::TYPE_SUBSCRIPTION,
            'code' => 'promo-15-trial',
            'name' => 'Promo 15 Hari Trial Pro',
            'price' => 0,
            'duration_days' => 15,
            'token_quantity' => 500000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('billing.checkout', ['type' => 'subscription']));

        $response->assertOk();
        $response->assertSee('Promo 15 Hari Trial Pro');
        $response->assertSee('PROMO TRIAL GRATIS');
        $response->assertSee('Paket Bebas Biaya — Promo Trial Aktif Otomatis');
    }

    public function test_tenant_can_instantly_activate_free_trial_package_without_payment(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 9, 12, 0, 0));

        $package = BillingPackage::create([
            'type' => BillingPackage::TYPE_SUBSCRIPTION,
            'code' => 'promo-15-trial',
            'name' => 'Promo 15 Hari Trial Pro',
            'price' => 0,
            'duration_days' => 15,
            'token_quantity' => 2000000,
            'is_active' => true,
        ]);

        // Prior to order, business is on free plan
        $entitlementService = app(EntitlementService::class);
        $initialSub = $entitlementService->getSubscription($this->business);
        $this->assertFalse($initialSub->isCorePlan());

        // Place order for the free promo package
        $response = $this->actingAs($this->user)->post(route('billing.order.store'), [
            'order_type' => 'subscription',
            'package_id' => $package->id,
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        // Check payment record
        $payment = SubscriptionPayment::where('business_id', $this->business->id)->latest()->first();
        $this->assertNotNull($payment);
        $this->assertSame(SubscriptionPayment::STATUS_APPROVED, $payment->status);
        $this->assertSame(SubscriptionPayment::METHOD_FREE_PROMO, $payment->payment_method);
        $this->assertSame(0.0, (float) $payment->amount);
        $this->assertSame(0, $payment->unique_code);
        $this->assertSame(0.0, (float) $payment->total_payable);
        $this->assertSame(15, $payment->package_duration_days);
        $this->assertNotNull($payment->approved_at);

        // Check business subscription is now active with Core Pro for 15 days
        $updatedSub = $entitlementService->getSubscription($this->business);
        $this->assertTrue($updatedSub->isCorePlan());
        $this->assertSame(BusinessSubscription::STATUS_ACTIVE, $updatedSub->status);
        $this->assertNotNull($updatedSub->ends_at);
        $this->assertSame('2026-09-24', $updatedSub->ends_at->toDateString());

        // Check AI tokens were allocated from the promo package
        $topup = AiTokenTopup::where('business_id', $this->business->id)->first();
        $this->assertNotNull($topup);
        $this->assertSame(2000000, $topup->purchased_tokens);
        $this->assertSame(2000000, $topup->remaining_tokens);

        Carbon::setTestNow();
    }

    public function test_entitlement_service_create_package_order_auto_delegates_for_zero_price(): void
    {
        $package = BillingPackage::create([
            'type' => BillingPackage::TYPE_SUBSCRIPTION,
            'code' => 'free-promo-tester',
            'name' => 'Free 15 Days',
            'price' => 0.00,
            'duration_days' => 15,
            'is_active' => true,
        ]);

        $service = app(EntitlementService::class);
        $payment = $service->createPackageOrder($this->business, $this->user, $package);

        $this->assertSame(SubscriptionPayment::STATUS_APPROVED, $payment->status);
        $this->assertSame(SubscriptionPayment::METHOD_FREE_PROMO, $payment->payment_method);
        $this->assertSame(0.0, (float) $payment->amount);

        $sub = $service->getSubscription($this->business);
        $this->assertTrue($sub->isCorePlan());
    }
}
