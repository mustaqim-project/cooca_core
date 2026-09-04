<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Billing\EntitlementService;
use App\Models\Admin;
use App\Models\Business;
use App\Models\PaymentAccount;
use App\Models\SubscriptionPayment;
use App\Models\SystemSetting;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminPaymentAccountManagementTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private Business $business;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        Storage::fake('public');

        $this->admin = Admin::create([
            'name' => 'Platform Admin',
            'email' => 'admin@cooca.id',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'name' => 'Tenant Owner',
            'email' => 'owner@warung.com',
            'password' => Hash::make('password123'),
        ]);

        $this->business = Business::create([
            'name' => 'Resto Sedap Rasa',
            'slug' => 'resto-sedap-rasa',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);
    }

    public function test_admin_can_view_payment_accounts_list(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.payment-accounts.index'));

        $response->assertStatus(200);
        $response->assertSee('CMS Rekening');
        $response->assertSee('Bank BCA Transfer');
    }

    public function test_admin_can_create_new_bank_payment_account(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.payment-accounts.store'), [
                'bank_code' => 'bni',
                'bank_name' => 'Bank Negara Indonesia (BNI)',
                'account_name' => 'PT Cooca Teknologi Indonesia',
                'account_number' => '0839-2819-291',
                'type' => 'bank_transfer',
                'instructions' => 'Transfer via BNI Mobile Banking',
                'icon' => 'credit-card',
                'color' => 'amber',
                'sort_order' => 5,
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.payment-accounts.index'));
        $response->assertSessionHas('success');

        $account = PaymentAccount::where('bank_code', 'bni')->first();
        $this->assertNotNull($account);
        $this->assertSame('Bank Negara Indonesia (BNI)', $account->bank_name);
        $this->assertTrue($account->is_active);
    }

    public function test_admin_can_create_qris_account_with_image_upload(): void
    {
        $fakeQr = UploadedFile::fake()->image('qris_official.png');

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.payment-accounts.store'), [
                'bank_code' => 'qris_gopay',
                'bank_name' => 'QRIS GoPay & OVO',
                'account_name' => 'COOCA RESTO PAY',
                'account_number' => 'NMID: ID9988776655',
                'type' => 'qris',
                'instructions' => 'Scan QRIS untuk pembayaran instan',
                'icon' => 'qr-code',
                'color' => 'emerald',
                'sort_order' => 1,
                'is_active' => '1',
                'qr_image' => $fakeQr,
            ]);

        $response->assertRedirect(route('admin.payment-accounts.index'));

        $account = PaymentAccount::where('bank_code', 'qris_gopay')->first();
        $this->assertNotNull($account);
        $this->assertNotNull($account->qr_image_path);
        $this->assertTrue($account->isQris());

        Storage::disk('public')->assertExists($account->qr_image_path);
    }

    public function test_admin_can_update_payment_account(): void
    {
        $account = PaymentAccount::create([
            'bank_code' => 'cimb',
            'bank_name' => 'Bank CIMB Niaga',
            'account_name' => 'PT Cooca',
            'account_number' => '1234-5678',
            'type' => 'bank_transfer',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->put(route('admin.payment-accounts.update', $account), [
                'bank_code' => 'cimb',
                'bank_name' => 'Bank CIMB Niaga Syariah',
                'account_name' => 'PT Cooca Teknologi',
                'account_number' => '9999-8888-77',
                'type' => 'bank_transfer',
                'instructions' => 'Transfer ke CIMB Syariah',
                'icon' => 'credit-card',
                'color' => 'purple',
                'sort_order' => 2,
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.payment-accounts.index'));
        $account->refresh();
        $this->assertSame('Bank CIMB Niaga Syariah', $account->bank_name);
        $this->assertSame('9999-8888-77', $account->account_number);
    }

    public function test_admin_can_toggle_payment_account_status(): void
    {
        $account = PaymentAccount::create([
            'bank_code' => 'bsi',
            'bank_name' => 'Bank Syariah Indonesia (BSI)',
            'account_name' => 'PT Cooca',
            'account_number' => '711223344',
            'type' => 'bank_transfer',
            'is_active' => true,
        ]);

        $this->assertTrue($account->is_active);

        // Toggle to inactive
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.payment-accounts.toggle-status', $account));

        $this->assertFalse($account->fresh()->is_active);

        // Toggle back to active
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.payment-accounts.toggle-status', $account));

        $this->assertTrue($account->fresh()->is_active);
    }

    public function test_admin_can_delete_payment_account(): void
    {
        $account = PaymentAccount::create([
            'bank_code' => 'danamon',
            'bank_name' => 'Bank Danamon',
            'account_name' => 'PT Cooca',
            'account_number' => '555-444-333',
            'type' => 'bank_transfer',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.payment-accounts.destroy', $account));

        $response->assertRedirect(route('admin.payment-accounts.index'));
        $this->assertNull(PaymentAccount::find($account->id));
    }

    public function test_admin_can_update_subscription_pricing(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.settings.update'), [
                'app_name' => 'Cooca UMKM Platform',
                'subscription_price_monthly' => 149000,
                'subscription_price_annual' => 1490000,
                'subscription_ai_tokens_monthly' => 15000000,
                'subscription_annual_discount_badge' => 'Diskon Spesial 2 Bulan',
            ]);

        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHas('success');

        $this->assertSame('149000', SystemSetting::get('subscription_price_monthly'));
        $this->assertSame('1490000', SystemSetting::get('subscription_price_annual'));
        $this->assertSame('15000000', SystemSetting::get('subscription_ai_tokens_monthly'));
        $this->assertSame('Diskon Spesial 2 Bulan', SystemSetting::get('subscription_annual_discount_badge'));

        $entitlementService = new EntitlementService();
        $this->assertEquals(149000.0, $entitlementService->getMonthlyPrice());
        $this->assertEquals(1490000.0, $entitlementService->getAnnualPrice());
        $this->assertEquals(15000000, $entitlementService->getCoreMonthlyAiTokens());
    }

    public function test_user_checkout_and_order_uses_dynamic_prices_and_rekening(): void
    {
        // Custom pricing
        SystemSetting::set('subscription_price_monthly', '199000', 'billing');
        SystemSetting::set('subscription_price_annual', '1990000', 'billing');

        // Create custom bank account
        PaymentAccount::create([
            'bank_code' => 'bca_utama',
            'bank_name' => 'BCA Virtual Account',
            'account_name' => 'PT Cooca Indonesia',
            'account_number' => '8800-1122-3344',
            'type' => 'bank_transfer',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($this->user);

        // View checkout
        $response = $this->get(route('billing.checkout', ['cycle' => 'monthly']));
        $response->assertStatus(200);
        $response->assertSee('199.000');
        $response->assertSee('BCA Virtual Account');

        // Create order
        $orderResponse = $this->post(route('billing.order.store'), [
            'cycle' => 'monthly',
            'payment_method' => 'bca_utama',
        ]);

        $payment = SubscriptionPayment::where('business_id', $this->business->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(199000, $payment->amount);
        $this->assertSame('bca_utama', $payment->payment_method);
        $this->assertGreaterThan(0, $payment->unique_code);

        $orderResponse->assertRedirect(route('billing.payment.show', $payment));

        // View payment page
        $payResponse = $this->get(route('billing.payment.show', $payment));
        $payResponse->assertStatus(200);
        $payResponse->assertSee('BCA Virtual Account');
        $payResponse->assertSee('8800-1122-3344');
    }
}
