<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Billing\EntitlementService;
use App\Models\Admin;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SubscriptionPaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        Storage::fake('public');

        $this->user = User::create([
            'name' => 'Owner Tenant',
            'email' => 'tenant@cooca.id',
            'password' => 'password123',
        ]);

        $this->business = Business::create(['name' => 'Kopi Nusantara']);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->admin = Admin::create([
            'name' => 'Super Admin Cooca',
            'email' => 'admin@cooca.id',
            'password' => Hash::make('secret123'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);
    }

    public function test_tenant_can_view_checkout_page(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('billing.checkout', ['cycle' => 'monthly']));

        $response->assertStatus(200);
        $response->assertSee('Cooca UMKM');
        $response->assertSee('Pilih Metode Pembayaran');
        $response->assertSee('Bank BCA Transfer');
        $response->assertSee('QRIS');
    }

    public function test_tenant_can_create_subscription_payment_order_with_unique_code(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('billing.order.store'), [
            'cycle' => 'monthly',
            'payment_method' => 'bca',
        ]);

        $payment = SubscriptionPayment::where('business_id', $this->business->id)->first();
        $this->assertNotNull($payment);
        $this->assertTrue(str_starts_with($payment->order_number, 'SUB-') || str_starts_with($payment->order_number, 'PKG-'));
        $this->assertEquals(25000, $payment->amount);
        $this->assertGreaterThan(0, $payment->unique_code);
        $this->assertEquals($payment->amount + $payment->unique_code, $payment->total_payable);
        $this->assertEquals('pending', $payment->status);

        $response->assertRedirect(route('billing.payment.show', $payment));
    }

    public function test_tenant_can_upload_payment_proof_and_status_becomes_awaiting_approval(): void
    {
        $this->actingAs($this->user);

        $entitlementService = new EntitlementService();
        $payment = $entitlementService->createPaymentOrder(
            business: $this->business,
            user: $this->user,
            cycle: 'monthly',
            paymentMethod: 'bca'
        );

        $fakeFile = UploadedFile::fake()->image('struk_bca.jpg');

        $response = $this->post(route('billing.payment.upload', $payment), [
            'payment_proof' => $fakeFile,
            'sender_bank' => 'BCA',
            'sender_account_name' => 'Ahmad Dahlan',
            'sender_account_number' => '5420129381',
            'notes' => 'Transfer via BCA Mobile jam 08:30 WIB',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $payment->refresh();
        $this->assertEquals('awaiting_approval', $payment->status);
        $this->assertEquals('Ahmad Dahlan', $payment->sender_account_name);
        $this->assertNotNull($payment->payment_proof_path);
        $this->assertNotNull($payment->proof_uploaded_at);

        Storage::disk('public')->assertExists($payment->payment_proof_path);
    }

    public function test_admin_can_view_pending_subscriptions(): void
    {
        $entitlementService = new EntitlementService();
        $payment = $entitlementService->createPaymentOrder(
            business: $this->business,
            user: $this->user,
            cycle: 'annual',
            paymentMethod: 'qris'
        );

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.subscriptions.index'));

        $response->assertStatus(200);
        $response->assertSee($payment->order_number);
        $response->assertSee('Kopi Nusantara');
        $response->assertSee('QRIS');
    }

    public function test_admin_can_approve_payment_and_auto_activate_core_subscription(): void
    {
        $entitlementService = new EntitlementService();
        $payment = $entitlementService->createPaymentOrder(
            business: $this->business,
            user: $this->user,
            cycle: 'monthly',
            paymentMethod: 'bca'
        );

        $fakeFile = UploadedFile::fake()->image('struk.png');
        $entitlementService->submitPaymentProof($payment, $fakeFile, [
            'sender_account_name' => 'Ahmad Dahlan',
        ]);

        $this->assertEquals('awaiting_approval', $payment->status);

        // Admin approves
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.subscriptions.approve', $payment), [
                'admin_notes' => 'Mutasi BCA masuk sesuai nominal unik',
            ]);

        $response->assertRedirect(route('admin.subscriptions.show', $payment));
        $response->assertSessionHas('success');

        $payment->refresh();
        $this->assertEquals('approved', $payment->status);
        $this->assertEquals($this->admin->id, $payment->approved_by);
        $this->assertNotNull($payment->approved_at);

        // Verify business subscription is automatically Core with 0 free tokens (top-up only)!
        $sub = BusinessSubscription::where('business_id', $this->business->id)->first();
        $this->assertNotNull($sub);
        $this->assertEquals(BusinessSubscription::PLAN_CORE_MONTHLY, $sub->plan_code);
        $this->assertEquals('active', $sub->status);
        $this->assertEquals(0, $sub->ai_tokens_monthly_allowance);
        $this->assertTrue($sub->isCorePlan());
    }

    public function test_admin_can_reject_payment_with_reason(): void
    {
        $entitlementService = new EntitlementService();
        $payment = $entitlementService->createPaymentOrder(
            business: $this->business,
            user: $this->user,
            cycle: 'monthly',
            paymentMethod: 'bca'
        );

        $fakeFile = UploadedFile::fake()->image('struk_buram.png');
        $entitlementService->submitPaymentProof($payment, $fakeFile, [
            'sender_account_name' => 'Penipu',
        ]);

        // Admin rejects
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.subscriptions.reject', $payment), [
                'reason' => 'Foto struk buram dan dana belum masuk mutasi rekening BCA.',
            ]);

        $response->assertRedirect(route('admin.subscriptions.show', $payment));
        $response->assertSessionHas('warning');

        $payment->refresh();
        $this->assertEquals('rejected', $payment->status);
        $this->assertEquals('Foto struk buram dan dana belum masuk mutasi rekening BCA.', $payment->admin_notes);
        $this->assertNotNull($payment->rejected_at);

        // Business remains Free plan
        $sub = BusinessSubscription::where('business_id', $this->business->id)->first();
        $this->assertFalse($sub?->isCorePlan() ?? false);
    }

    public function test_approved_token_topup_is_fifo_and_expires_after_thirty_days(): void
    {
        $service = new EntitlementService();
        $first = $service->createTokenTopupOrder($this->business, $this->user);
        $service->submitPaymentProof($first, UploadedFile::fake()->image('first.png'));
        $service->approvePayment($first, $this->admin);

        $second = $service->createTokenTopupOrder($this->business, $this->user);
        $service->submitPaymentProof($second, UploadedFile::fake()->image('second.png'));
        $service->approvePayment($second, $this->admin);

        $this->assertTrue($service->deductAiTokens($this->business, 1_000_001, 'test', $this->user));
        $this->assertSame(0, (int) $this->business->aiTokenTopups()->where('payment_id', $first->id)->first()->remaining_tokens);
        $this->assertSame(999_999, (int) $this->business->aiTokenTopups()->where('payment_id', $second->id)->first()->remaining_tokens);

        Carbon::setTestNow(Carbon::now()->addDays(31));
        $this->assertFalse($service->canAccessAi($this->business));
        Carbon::setTestNow();
    }

    public function test_approved_storage_topup_adds_capacity_to_owner_across_businesses(): void
    {
        $service = new EntitlementService();
        $payment = $service->createStorageTopupOrder($this->business, $this->user);
        $service->submitPaymentProof($payment, UploadedFile::fake()->image('storage.png'));
        $service->approvePayment($payment, $this->admin);

        $summary = app(\App\Domain\Storage\OwnerStorageQuotaService::class)->getSummary($this->user);
        $this->assertSame(4.0, $summary['limit_gb']);
        $this->assertDatabaseHas('owner_storage_topups', [
            'owner_id' => $this->user->id,
            'payment_id' => $payment->id,
        ]);
    }

    public function test_tenant_can_view_and_download_subscription_invoice(): void
    {
        $service = new EntitlementService();
        $payment = $service->createPaymentOrder($this->business, $this->user, 'annual', SubscriptionPayment::METHOD_BCA);

        $response = $this->actingAs($this->user)->get(route('billing.payment.invoice', $payment));

        $response->assertOk();
        $response->assertSee('INVOICE TAGIHAN');
        $response->assertSee($payment->order_number);
        $response->assertSee($this->business->name);
        $response->assertSee('Download PDF Langsung');
    }
}
