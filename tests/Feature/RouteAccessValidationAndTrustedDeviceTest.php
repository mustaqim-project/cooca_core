<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\WhatsApp\WhatsAppTrustedDeviceService;
use App\Models\AccountRecoveryRequest;
use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RouteAccessValidationAndTrustedDeviceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'owner@example.com',
            'phone' => '081234567890',
            'password' => bcrypt('Password123!'),
            'email_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'Kopi Mantap',
            'phone' => '081234567890',
            'currency' => 'IDR',
            'currency_precision' => 0,
            'rounding_strategy' => Business::ROUNDING_ROUND_100,
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'role' => 'owner',
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
    }

    public function test_authenticated_user_accessing_login_or_register_is_redirected_to_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get('/login');
        $response->assertRedirect(route('dashboard'));

        $responseRegister = $this->actingAs($this->user)->get('/register');
        $responseRegister->assertRedirect(route('dashboard'));
    }

    public function test_auth_otp_page_redirects_to_dashboard_if_already_verified(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['auth_wa_otp_verified_user_id' => $this->user->id])
            ->get('/auth/otp');

        $response->assertRedirect(route('dashboard'));
    }

    public function test_trusted_device_skips_wa_otp_upon_re_login(): void
    {
        $trustedDeviceService = app(WhatsAppTrustedDeviceService::class);
        $phone = '6281234567890';

        $payload = json_encode([
            'user_id' => $this->user->id,
            'phone_hash' => sha1($phone),
            'verified_at' => now()->timestamp,
            'expiry_days' => 60,
        ]);

        $response = $this->withCookie(WhatsAppTrustedDeviceService::COOKIE_NAME, $payload)
            ->post('/login', [
                'email' => 'owner@example.com',
                'password' => 'Password123!',
            ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->user);

        // Akses dashboard langsung lolos tanpa redirect ke /auth/otp
        $dashResponse = $this->withCookie(WhatsAppTrustedDeviceService::COOKIE_NAME, $payload)
            ->get('/dashboard');

        $dashResponse->assertOk();
    }

    public function test_complete_profile_redirects_to_dashboard_if_data_already_complete(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->user->id,
                'active_business_id' => $this->business->id,
            ])
            ->get('/complete-profile');

        $response->assertRedirect(route('dashboard'));
    }

    public function test_account_recovery_create_redirects_to_status_if_active_ticket_exists(): void
    {
        $recovery = AccountRecoveryRequest::create([
            'user_id' => $this->user->id,
            'ticket_number' => 'REC-TEST-12345',
            'applicant_name' => 'Owner Test',
            'business_name' => 'Kopi Mantap',
            'old_email' => 'owner@example.com',
            'new_email' => 'newowner@example.com',
            'new_phone' => '628999888777',
            'issue_type' => AccountRecoveryRequest::ISSUE_BOTH,
            'reason_description' => 'HP hilang dan nomor hangus',
            'identity_card_path' => 'recoveries/ktp.jpg',
            'business_proof_path' => 'recoveries/biz.jpg',
            'status' => AccountRecoveryRequest::STATUS_PENDING,
            'verification_notes' => 'Sedang dicek',
        ]);

        $response = $this->actingAs($this->user)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->user->id,
            ])
            ->get('/account-recovery/request');

        $response->assertRedirect(route('account-recovery.status', $recovery->ticket_number));
    }

    public function test_email_verify_notice_redirects_to_dashboard_if_already_verified(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->user->id,
            ])
            ->get('/email/verify');

        $response->assertRedirect(route('dashboard'));
    }
}
