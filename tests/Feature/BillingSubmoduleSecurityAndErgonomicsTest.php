<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Billing\EntitlementService;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\StorageFile;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BillingSubmoduleSecurityAndErgonomicsTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $cashier;
    private Business $businessA;
    private Business $businessB;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->owner = User::create([
            'name' => 'Owner Utama',
            'email' => 'owner@cooca.id',
            'password' => 'password123',
            'email_verified_at' => now(),
            'phone' => '6281234567890',
        ]);

        $this->cashier = User::create([
            'name' => 'Kasir Toko',
            'email' => 'kasir@cooca.id',
            'password' => 'password123',
            'email_verified_at' => now(),
            'phone' => '6281234567899',
        ]);

        $this->businessA = Business::create([
            'name' => 'Bisnis Utama A',
            'slug' => 'bisnis-utama-a',
            'industry_category' => 'retail',
        ]);

        $this->businessB = Business::create([
            'name' => 'Bisnis Eksternal B',
            'slug' => 'bisnis-eksternal-b',
            'industry_category' => 'fnb_resto',
        ]);

        // Attach owner to Business A
        $this->businessA->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        // Attach cashier to Business A (only POS permissions, no billing)
        $this->businessA->users()->attach($this->cashier->id, [
            'id' => (string) Str::uuid(),
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $this->businessA->id]);
        $this->cashier->update(['active_business_id' => $this->businessA->id]);
    }

    public function test_cashier_without_billing_permission_cannot_access_billing_routes(): void
    {
        $this->actingAs($this->cashier)
            ->withSession(['active_business_id' => $this->businessA->id]);

        // Web requests redirect to portal with an error notice
        $this->get(route('billing.limits'))->assertRedirect(route('portal'));
        $this->get(route('billing.checkout'))->assertRedirect(route('portal'));
        $this->get(route('billing.history'))->assertRedirect(route('portal'));

        // JSON requests return 403 Forbidden with standard security payload
        $this->getJson(route('billing.limits'))->assertStatus(403);
    }

    public function test_owner_can_access_billing_routes(): void
    {
        $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->businessA->id]);

        $this->get(route('billing.limits'))->assertStatus(200);
        $this->get(route('billing.checkout'))->assertStatus(200);
        $this->get(route('billing.history'))->assertStatus(200);
    }

    public function test_local_and_testing_process_freedom_for_upgrade_simulation(): void
    {
        $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->businessA->id]);

        // In testing/local environment, upgradeToCore simulation succeeds freely
        $response = $this->post(route('billing.upgrade'), [
            'cycle' => 'annual',
        ]);

        $response->assertRedirect(route('billing.limits'));
        $sub = BusinessSubscription::where('business_id', $this->businessA->id)->first();
        $this->assertNotNull($sub);
        $this->assertTrue($sub->isCorePlan());
    }

    public function test_dynamic_industry_auto_hiding_in_limits_view(): void
    {
        // 1. Retail business with pos_dinein disabled should NOT display Meja Kasir
        $this->businessA->update([
            'template_code' => 'retail_grocery',
            'industry_category' => 'retail',
            'disabled_modules' => ['pos_dinein'],
        ]);
        Context::flush();

        $responseRetail = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->get(route('billing.limits'));
        $responseRetail->assertStatus(200);
        $responseRetail->assertDontSee(__('billing.resource_tables'));

        // 2. F&B Resto business with pos_dinein enabled should display Meja Kasir
        $this->businessA->update([
            'template_code' => 'fnb_resto',
            'industry_category' => 'fnb_resto',
            'disabled_modules' => [],
        ]);
        Context::flush();

        $responseFnb = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->get(route('billing.limits'));
        $responseFnb->assertStatus(200);
        $responseFnb->assertSee(__('billing.resource_tables'));
    }

    public function test_storage_file_deletion_is_isolated_to_tenant(): void
    {
        $fileA = StorageFile::create([
            'business_id' => $this->businessA->id,
            'owner_id' => $this->owner->id,
            'user_id' => $this->owner->id,
            'disk' => 'public',
            'file_path' => 'tenants/bisnis-a/image.jpg',
            'file_name' => 'image.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'category' => 'product_image',
            'module' => 'products',
            'status' => StorageFile::STATUS_ACTIVE,
        ]);

        $otherOwner = User::create([
            'name' => 'Owner Lain',
            'email' => 'other@cooca.id',
            'password' => 'password123',
            'email_verified_at' => now(),
            'phone' => '6281234567888',
        ]);

        $this->businessB->users()->attach($otherOwner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $otherOwner->update(['active_business_id' => $this->businessB->id]);

        // Attempt by other owner on business B to delete File of Business A must fail with 403 (strict tenant isolation)
        $this->actingAs($otherOwner)
            ->withSession(['active_business_id' => $this->businessB->id]);

        $response = $this->delete(route('billing.storage.files.destroy', $fileA->id));
        $response->assertStatus(403);

        $this->assertDatabaseHas('storage_files', ['id' => $fileA->id]);
    }

    public function test_navigation_registry_contains_billing_submodules(): void
    {
        $all = \App\Support\Navigation\NavigationRegistry::all();
        $this->assertArrayHasKey('billing', $all);
        $tabs = $all['billing']['tabs'];
        $this->assertNotEmpty($tabs);

        $keys = array_column($tabs, 'key');
        $this->assertContains('limits', $keys);
        $this->assertContains('checkout', $keys);
        $this->assertContains('history', $keys);

        // When acting as owner, getTabsForModule returns the accessible tabs
        $this->actingAs($this->owner)->withSession(['active_business_id' => $this->businessA->id]);
        $this->get(route('billing.limits'))->assertStatus(200);
        $ownerTabs = \App\Support\Navigation\NavigationRegistry::getTabsForModule('billing');
        $this->assertNotEmpty($ownerTabs);
    }

    public function test_billing_views_render_module_header_and_tabs(): void
    {
        $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->businessA->id]);

        $responseLimits = $this->get(route('billing.limits'));
        $responseLimits->assertStatus(200);
        $responseLimits->assertSee(route('billing.history'));
        $responseLimits->assertSee(route('billing.checkout'));

        $responseCheckout = $this->get(route('billing.checkout'));
        $responseCheckout->assertStatus(200);
        $responseCheckout->assertSee(route('billing.limits'));
        $responseCheckout->assertSee(route('billing.history'));

        $responseHistory = $this->get(route('billing.history'));
        $responseHistory->assertStatus(200);
        $responseHistory->assertSee(route('billing.limits'));
        $responseHistory->assertSee(route('billing.checkout'));
    }

    public function test_billing_history_server_side_filtering(): void
    {
        $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->businessA->id]);

        // Create 2 payments with different status
        \App\Models\SubscriptionPayment::create([
            'business_id' => $this->businessA->id,
            'user_id' => $this->owner->id,
            'order_number' => 'ORD-TEST-PENDING',
            'package_name' => 'Paket Standard Bulanan',
            'cycle' => 'monthly',
            'amount' => 29000,
            'total_payable' => 29000,
            'status' => \App\Models\SubscriptionPayment::STATUS_PENDING,
            'payment_method' => 'qris',
        ]);

        \App\Models\SubscriptionPayment::create([
            'business_id' => $this->businessA->id,
            'user_id' => $this->owner->id,
            'order_number' => 'ORD-TEST-APPROVED',
            'package_name' => 'Paket Standard Tahunan',
            'cycle' => 'annual',
            'amount' => 290000,
            'total_payable' => 290000,
            'status' => \App\Models\SubscriptionPayment::STATUS_APPROVED,
            'payment_method' => 'qris',
        ]);

        // Filter by status=approved
        $resApproved = $this->get(route('billing.history', ['status' => 'approved']));
        $resApproved->assertStatus(200);
        $resApproved->assertSee('ORD-TEST-APPROVED');
        $resApproved->assertDontSee('ORD-TEST-PENDING');

        // Filter by status=pending
        $resPending = $this->get(route('billing.history', ['status' => 'pending']));
        $resPending->assertStatus(200);
        $resPending->assertSee('ORD-TEST-PENDING');
        $resPending->assertDontSee('ORD-TEST-APPROVED');
    }

    public function test_tenant_auto_journaling_and_notification_on_subscription_approval(): void
    {
        // Setup cash account for business A
        $cashAccount = \App\Models\CashAccount::create([
            'business_id' => $this->businessA->id,
            'name' => 'Kas Operasional Utama',
            'account_number' => '1001-CASH',
            'current_balance' => 1000000,
            'is_active' => true,
        ]);

        $payment = \App\Models\SubscriptionPayment::create([
            'business_id' => $this->businessA->id,
            'user_id' => $this->owner->id,
            'order_number' => 'ORD-AUTO-JOURNAL-01',
            'package_name' => 'Cooca Standard',
            'cycle' => 'monthly',
            'amount' => 29000,
            'total_payable' => 29000,
            'status' => \App\Models\SubscriptionPayment::STATUS_PENDING,
            'payment_method' => 'qris',
        ]);

        $entitlementService = new EntitlementService();
        $entitlementService->approvePayment($payment);

        $payment->refresh();
        $this->assertEquals(\App\Models\SubscriptionPayment::STATUS_APPROVED, $payment->status);

        // Assert CashTransaction was created automatically for the tenant
        $this->assertDatabaseHas('cash_transactions', [
            'business_id' => $this->businessA->id,
            'cash_account_id' => $cashAccount->id,
            'type' => 'out',
            'amount' => 29000,
        ]);

        // Assert AuditLog was created
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->businessA->id,
            'action' => 'subscription.activated',
        ]);
    }
}
