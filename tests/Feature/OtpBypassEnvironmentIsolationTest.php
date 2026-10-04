<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class OtpBypassEnvironmentIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(\Database\Seeders\DefaultUnitSeeder::class);
        $this->seed(\Database\Seeders\DefaultCostCategorySeeder::class);
        $this->seed(\Database\Seeders\RbacSeeder::class);
        $this->seed(\Database\Seeders\BusinessTemplateSeeder::class);
    }

    protected function tearDown(): void
    {
        $this->app->detectEnvironment(fn () => 'testing');
        $this->app['env'] = 'testing';
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        parent::tearDown();
    }

    public function test_register_otp_page_shows_bypass_alert_in_local_and_testing(): void
    {
        $pending = [
            'name' => 'Testing Reg User',
            'email' => 'testing.reg@security.test',
            'phone' => '6289911223344',
            'business_name' => 'Testing Biz Alpha',
            'template_code' => 'retail_reseller',
            'disabled_modules' => [],
            'custom_modules' => false,
            'password' => Hash::make('rahasia123'),
            'otp_hash' => Hash::make('654321'),
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
            'last_sent_at' => now()->timestamp,
        ];

        $response = $this->withSession(['pending_registration' => $pending])
            ->get(route('register.verify'));

        $response->assertStatus(200);
        $response->assertSee('Bypass / Pengujian:');
        $response->assertSee('123456');
    }

    public function test_register_otp_page_hides_bypass_alert_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $pending = [
            'name' => 'Production Reg User',
            'email' => 'production.reg@security.test',
            'phone' => '6289911223355',
            'business_name' => 'Production Biz Beta',
            'template_code' => 'retail_reseller',
            'disabled_modules' => [],
            'custom_modules' => false,
            'password' => Hash::make('rahasia123'),
            'otp_hash' => Hash::make('654321'),
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
            'last_sent_at' => now()->timestamp,
        ];

        $response = $this->withSession(['pending_registration' => $pending])
            ->get(route('register.verify'));

        $response->assertStatus(200);
        $response->assertDontSee('Bypass / Pengujian:');
        $response->assertDontSee('untuk verifikasi instan.');
    }

    public function test_registration_otp_fails_with_bypass_code_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $csrfToken = 'test-token-123';
        $pending = [
            'name' => 'Prod Test User',
            'email' => 'prod.bypass.test@cooca.id',
            'phone' => '6281298765431',
            'business_name' => 'Prod Biz Security Test',
            'business_scale' => 'umkm',
            'template_code' => 'retail_reseller',
            'disabled_modules' => [],
            'custom_modules' => false,
            'password' => Hash::make('rahasia123'),
            'otp_hash' => Hash::make('654321'), // Real OTP is 654321
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
            'last_sent_at' => now()->timestamp,
        ];

        // Attempting master bypass code 123456 in production must fail
        $response = $this->from(route('register.verify'))
            ->withSession(['_token' => $csrfToken, 'pending_registration' => $pending])
            ->post(route('register.verify.submit'), [
                '_token' => $csrfToken,
                'otp' => '123456',
            ]);

        $response->assertRedirect(route('register.verify'));
        $response->assertSessionHasErrors(['otp']);
        $this->assertDatabaseMissing('users', ['email' => 'prod.bypass.test@cooca.id']);
    }

    public function test_registration_otp_succeeds_with_bypass_code_in_local_or_testing(): void
    {
        $this->app->detectEnvironment(fn () => 'testing');

        $pending = [
            'name' => 'Local Test User',
            'email' => 'local.bypass.test@cooca.id',
            'phone' => '6281298765432',
            'business_name' => 'Local Biz Test',
            'business_scale' => 'umkm',
            'template_code' => 'retail_reseller',
            'disabled_modules' => [],
            'custom_modules' => false,
            'password' => Hash::make('rahasia123'),
            'otp_hash' => Hash::make('654321'), // Real OTP is 654321
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
            'last_sent_at' => now()->timestamp,
        ];

        // Master bypass code 123456 in testing/local environment must succeed
        $response = $this->withSession(['pending_registration' => $pending])
            ->post(route('register.verify.submit'), [
                'otp' => '123456',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['email' => 'local.bypass.test@cooca.id']);
    }

    public function test_auth_login_otp_view_environment_gating(): void
    {
        $business = Business::create([
            'name' => 'Auth OTP Test Biz',
            'status' => 'active',
            'business_scale' => 'umkm',
            'template_code' => 'retail_reseller',
        ]);

        $user = User::create([
            'name' => 'Auth OTP User',
            'email' => 'auth.otp.user@cooca.test',
            'password' => Hash::make('password123'),
            'phone' => '6281234455667',
            'phone_verified_at' => null,
            'active_business_id' => $business->id,
        ]);
        $business->users()->attach($user->id, ['role' => 'owner']);

        $challenge = [
            'user_id' => $user->id,
            'phone' => $user->phone,
            'otp_hash' => Hash::make('654321'),
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
            'last_sent_at' => now()->timestamp,
        ];

        // In testing/local: alert visible
        $this->app->detectEnvironment(fn () => 'testing');
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        $resLocal = $this->actingAs($user)->withSession(['auth_wa_otp_challenge' => $challenge])
            ->get(route('auth.otp'));
        $resLocal->assertStatus(200);
        $resLocal->assertSee('Bypass / Pengujian:');

        // In production: alert hidden
        $this->app->detectEnvironment(fn () => 'production');
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        $resProd = $this->actingAs($user)->withSession(['auth_wa_otp_challenge' => $challenge])
            ->get(route('auth.otp'));
        $resProd->assertStatus(200);
        $resProd->assertDontSee('Bypass / Pengujian:');
    }

    public function test_auth_login_otp_fails_with_bypass_code_in_production(): void
    {
        $business = Business::create([
            'name' => 'Auth Prod Biz',
            'status' => 'active',
            'business_scale' => 'umkm',
            'template_code' => 'retail_reseller',
        ]);

        $user = User::create([
            'name' => 'Auth Prod User',
            'email' => 'auth.prod.user@cooca.test',
            'password' => Hash::make('password123'),
            'phone' => '6281234455668',
            'phone_verified_at' => null,
            'active_business_id' => $business->id,
        ]);
        $business->users()->attach($user->id, ['role' => 'owner']);

        $this->app->detectEnvironment(fn () => 'production');

        $csrfToken = 'csrf-prod-token';
        $challenge = [
            'user_id' => $user->id,
            'phone' => $user->phone,
            'otp_hash' => Hash::make('654321'),
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
            'last_sent_at' => now()->timestamp,
        ];

        $response = $this->actingAs($user)
            ->from(route('auth.otp'))
            ->withSession([
                '_token' => $csrfToken,
                'auth_wa_otp_challenge' => $challenge,
            ])
            ->post(route('auth.otp.verify'), [
                '_token' => $csrfToken,
                'otp' => '123456',
            ]);

        $response->assertRedirect(route('auth.otp'));
        $response->assertSessionHasErrors(['otp']);
        $this->assertNull(session('auth_wa_otp_verified_user_id'));
    }

    public function test_email_verification_bypass_button_and_param_in_production(): void
    {
        $business = Business::create([
            'name' => 'Email Prod Biz',
            'status' => 'active',
            'business_scale' => 'umkm',
            'template_code' => 'retail_reseller',
        ]);

        $user = User::create([
            'name' => 'Unverified User',
            'email' => 'unverified.prod@cooca.test',
            'password' => Hash::make('password123'),
            'email_verified_at' => null,
            'active_business_id' => $business->id,
        ]);
        $business->users()->attach($user->id, ['role' => 'owner']);

        // 1. In production, view must NOT show bypass button
        $this->app->detectEnvironment(fn () => 'production');
        \Illuminate\Support\Facades\Artisan::call('view:clear');

        $viewRes = $this->actingAs($user)->get(route('verification.notice'));
        $viewRes->assertStatus(200);
        $viewRes->assertDontSee('Bypass / Verifikasi Email Instan');

        // 2. In production, GET ?bypass=1 must NOT mark user as verified
        $bypassAttemptRes = $this->actingAs($user)->get(route('verification.notice', ['bypass' => 1]));
        $bypassAttemptRes->assertStatus(200); // Renders verification notice page instead of redirecting with success
        $this->assertNull($user->fresh()->email_verified_at);

        // 3. In testing/local, view shows bypass button and ?bypass=1 marks verified
        $this->app->detectEnvironment(fn () => 'testing');
        \Illuminate\Support\Facades\Artisan::call('view:clear');

        $localViewRes = $this->actingAs($user)->get(route('verification.notice'));
        $localViewRes->assertStatus(200);
        $localViewRes->assertSee('Bypass / Verifikasi Email Instan');

        $localBypassRes = $this->actingAs($user)->get(route('verification.notice', ['bypass' => 1]));
        $localBypassRes->assertRedirect(route('dashboard'));
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}

