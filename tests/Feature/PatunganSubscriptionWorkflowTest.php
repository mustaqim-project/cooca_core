<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Billing\EntitlementService;
use App\Models\Admin;
use App\Models\BillingPackage;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\PaymentAccount;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PatunganSubscriptionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private Admin $admin;
    private EntitlementService $entitlementService;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        Storage::fake('public');

        $this->entitlementService = new EntitlementService();

        $this->user = User::create([
            'name' => 'Juragan UMKM',
            'email' => 'juragan@cooca.id',
            'password' => 'password123',
            'email_verified_at' => now(),
            'phone' => '6281234567888',
            'phone_verified_at' => now(),
        ]);

        $this->business = Business::create(['name' => 'Dapur Nusantara']);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->admin = Admin::create([
            'name' => 'Admin Billing',
            'email' => 'admin.billing@cooca.id',
            'password' => Hash::make('secret123'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        PaymentAccount::create([
            'bank_name' => 'Bank BCA',
            'bank_code' => 'bca',
            'account_number' => '1234567890',
            'account_name' => 'Cooca ID',
            'type' => 'bank_transfer',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    public function test_pricing_is_sourced_from_billing_packages_subscription_catalog(): void
    {
        $this->assertEquals(25000, $this->entitlementService->getMonthlyPrice());
        $this->assertEquals(250000, $this->entitlementService->getAnnualPrice());

        // Update package price in catalog to verify dynamic retrieval
        BillingPackage::where('code', 'core-monthly')->update(['price' => 30000]);
        $this->assertEquals(30000, $this->entitlementService->getMonthlyPrice());
    }

    public function test_tenant_can_view_patungan_checkout_page(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/patungan');
        $response->assertStatus(200);
        $response->assertSee('Standard Plan');
        $response->assertSee('29.000');
        $response->assertSee('290.000');
        $response->assertDontSee('10 Juta Token AI');
    }

    public function test_order_creation_calculates_exact_total_without_unique_code(): void
    {
        $this->actingAs($this->user);

        $monthlyPkg = BillingPackage::where('code', 'core-monthly')->firstOrFail();

        $response = $this->post(route('billing.order.store'), [
            'package_id' => $monthlyPkg->id,
            'payment_method' => 'bca_va',
        ]);

        $payment = SubscriptionPayment::where('business_id', $this->business->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(29000, $payment->amount);
        $this->assertEquals(0, $payment->unique_code);
        $this->assertEquals($payment->amount, $payment->total_payable);
        $this->assertEquals('pending', $payment->status);

        $response->assertRedirect(route('billing.payment.show', $payment));
    }

    public function test_proof_upload_is_disabled_and_redirects(): void
    {
        $this->actingAs($this->user);

        $monthlyPkg = BillingPackage::where('code', 'core-monthly')->firstOrFail();
        $payment = $this->entitlementService->createPackageOrder($this->business, $this->user, $monthlyPkg, 'bca_va');

        $fakeProof = UploadedFile::fake()->image('bukti_transfer.jpg');

        $response = $this->post(route('billing.payment.upload', $payment), [
            'payment_proof' => $fakeProof,
            'sender_bank' => 'BCA',
            'sender_account_name' => 'Budi Santoso',
            'sender_account_number' => '1234567890',
            'notes' => 'Patungan bulan ini',
        ]);

        $response->assertRedirect(route('billing.payment.show', $payment));
        $response->assertSessionHas('info');

        $payment->refresh();
        $this->assertEquals('pending', $payment->status);

        // Verify subscription is STILL FREE (not activated!)
        $sub = $this->entitlementService->getSubscription($this->business);
        $this->assertFalse($sub->isCorePlan());
    }

    public function test_admin_approving_payment_activates_subscription_with_zero_free_tokens(): void
    {
        $monthlyPkg = BillingPackage::where('code', 'core-monthly')->firstOrFail();
        $payment = $this->entitlementService->createPackageOrder($this->business, $this->user, $monthlyPkg, 'bca');

        $this->entitlementService->submitPaymentProof($payment, UploadedFile::fake()->image('proof.png'), [
            'sender_account_name' => 'Budi Santoso',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.subscriptions.approve', $payment), [
                'admin_notes' => 'Pembayaran Patungan Terverifikasi',
            ]);

        $response->assertRedirect(route('admin.subscriptions.show', $payment));

        $payment->refresh();
        $this->assertEquals('approved', $payment->status);
        $this->assertEquals($this->admin->id, $payment->approved_by);

        // Subscription is active Core plan
        $sub = $this->business->subscription;
        $this->assertNotNull($sub);
        $this->assertTrue($sub->isCorePlan());
        $this->assertEquals(BusinessSubscription::STATUS_ACTIVE, $sub->status);
        // Free token allowance must be ZERO (tokens are top-up only)
        $this->assertEquals(0, $sub->ai_tokens_monthly_allowance);
        $this->assertEquals(0, $sub->ai_tokens_remaining);
        $this->assertFalse($this->entitlementService->canAccessAi($this->business));

        // Entitlements unlocked
        $this->assertTrue($this->entitlementService->canImportData($this->business));
        $this->assertTrue($this->entitlementService->canExportData($this->business));
    }

    public function test_ai_is_only_accessible_after_token_topup(): void
    {
        // Business on Core plan
        $this->entitlementService->upgradeToCore($this->business);
        $this->assertFalse($this->entitlementService->canAccessAi($this->business));

        // Top up 1 Million tokens
        $topup = $this->entitlementService->createTokenTopupOrder($this->business, $this->user);
        $this->entitlementService->submitPaymentProof($topup, UploadedFile::fake()->image('topup.png'));
        $this->entitlementService->approvePayment($topup, $this->admin);

        // Now AI is accessible
        $this->assertTrue($this->entitlementService->canAccessAi($this->business));
        $this->assertTrue($this->entitlementService->deductAiTokens($this->business, 500, 'test_prompt', $this->user));
    }

    public function test_admin_rejection_stores_reason_and_keeps_subscription_inactive(): void
    {
        $monthlyPkg = BillingPackage::where('code', 'core-monthly')->firstOrFail();
        $payment = $this->entitlementService->createPackageOrder($this->business, $this->user, $monthlyPkg, 'bca');

        $this->entitlementService->submitPaymentProof($payment, UploadedFile::fake()->image('fake.png'), [
            'sender_account_name' => 'Fulan',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.subscriptions.reject', $payment), [
                'reason' => 'Nominal transfer tidak sesuai dengan kode unik.',
            ]);

        $response->assertRedirect(route('admin.subscriptions.show', $payment));

        $payment->refresh();
        $this->assertEquals('rejected', $payment->status);
        $this->assertEquals('Nominal transfer tidak sesuai dengan kode unik.', $payment->admin_notes);

        $sub = $this->entitlementService->getSubscription($this->business);
        $this->assertFalse($sub->isCorePlan());
    }
}
