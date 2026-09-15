<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthViewsAppleHigTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name' => 'Pak Budi Hartono',
            'email' => 'budi.hartono@example.com',
            'phone' => '6281234567890',
            'password' => bcrypt('password123'),
        ]);

        $this->business = Business::create([
            'name' => 'Toko Sembako Berkah',
            'slug' => 'toko-sembako-berkah',
            'phone' => '6281234567890',
            'currency' => 'IDR',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
    }

    public function test_login_view_renders_with_apple_hig_and_anti_zoom_inputs(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Masuk ke Cooca');
        $response->assertSee('Masuk ke Dashboard');
        $response->assertSee('text-[16px]');
        $response->assertSee('min-h-[50px]');
        $response->assertSee('name="email"', false);
        $response->assertSee('name="password"', false);
    }

    public function test_register_view_renders_with_apple_hig_and_bento_templates(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('Daftarkan Bisnis Anda');
        $response->assertSee('Buat Akun');
        $response->assertSee('text-[16px]');
        $response->assertSee('name="business_name"', false);
        $response->assertSee('name="phone"', false);
    }

    public function test_forgot_password_view_renders_with_apple_hig_and_reassurance_card(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertStatus(200);
        $response->assertSee('Atur Ulang Kata Sandi');
        $response->assertSee('Kirim Tautan Pemulihan Kata Sandi');
        $response->assertSee('text-[16px]');
        $response->assertSee('min-h-[50px]');
        $response->assertSee('name="email"', false);
    }

    public function test_reset_password_view_renders_with_apple_hig(): void
    {
        $response = $this->get(route('password.reset', ['token' => 'dummy-token-12345', 'email' => 'budi.hartono@example.com']));

        $response->assertStatus(200);
        $response->assertSee('Buat Kata Sandi Baru');
        $response->assertSee('Simpan Kata Sandi Baru');
        $response->assertSee('text-[16px]');
        $response->assertSee('name="password_confirmation"', false);
    }

    public function test_account_recovery_create_view_renders_with_apple_hig_bento(): void
    {
        $response = $this->get(route('account-recovery.create'));

        $response->assertStatus(200);
        $response->assertSee('Pemulihan Akses Akun');
        $response->assertSee('1. Jenis Kendala Verifikasi');
        $response->assertSee('5. Berkas Bukti Otentik Kepemilikan Akun');
        $response->assertSee('Ajukan Permohonan Pemulihan Akses');
        $response->assertSee('text-[16px]');
    }

    public function test_account_recovery_status_view_renders_with_anti_zoom(): void
    {
        $response = $this->get(route('account-recovery.check'));

        $response->assertStatus(200);
        $response->assertSee('Status Pemulihan Akun');
        $response->assertSee('Nomor Tiket Pemulihan');
        $response->assertSee('Cari Status Permohonan');
        $response->assertSee('text-[16px]');
    }

    public function test_select_business_view_renders_with_bento_cards_and_modal(): void
    {
        $response = $this->actingAs($this->user, 'web')->get(route('businesses.select'));

        $response->assertStatus(200);
        $response->assertSee('Pilih Workspace Bisnis');
        $response->assertSee('Toko Sembako Berkah');
        $response->assertSee('Tambah Bisnis Baru');
        $response->assertSee('text-[16px]');
    }

    public function test_verify_email_view_renders_with_apple_card_and_user_badge(): void
    {
        $response = $this->actingAs($this->user, 'web')->get(route('verification.notice'));

        $response->assertStatus(200);
        $response->assertSee('Verifikasi Email Anda');
        $response->assertSee('budi.hartono@example.com');
        $response->assertSee('Kirim Ulang Tautan Verifikasi');
        $response->assertSee('min-h-[50px]');
    }

    public function test_complete_profile_view_renders_with_apple_hig_and_anti_zoom(): void
    {
        $incompleteUser = User::create([
            'name' => 'Budi Belum Lengkap',
            'email' => 'incomplete.profile@example.com',
            'phone' => null, // Empty phone triggers onboarding
            'password' => bcrypt('password123'),
        ]);

        $incompleteBiz = Business::create([
            'name' => 'Usaha Saya Belum Lengkap',
            'slug' => 'usaha-saya-belum-lengkap',
            'phone' => null,
            'currency' => 'IDR',
            'is_active' => true,
        ]);

        $incompleteBiz->users()->attach($incompleteUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $incompleteUser->update(['active_business_id' => $incompleteBiz->id]);

        $response = $this->actingAs($incompleteUser, 'web')->get(route('profile.complete'));

        $response->assertStatus(200);
        $response->assertSee('Lengkapi Profil Bisnis');
        $response->assertSee('Simpan & Buka Workspace Bisnis', false);
        $response->assertSee('text-[16px]');
        $response->assertSee('min-h-[50px]');
        $response->assertSee('Budi Belum Lengkap');
    }

    public function test_otp_view_renders_with_apple_hig_and_reassurance(): void
    {
        $response = $this->actingAs($this->user, 'web')
            ->withSession([
                'auth_wa_otp_challenge' => [
                    'user_id' => $this->user->id,
                    'phone' => '6281234567890',
                    'otp_hash' => bcrypt('123456'),
                    'expires_at' => now()->addMinutes(10)->timestamp,
                    'attempts' => 0,
                    'last_sent_at' => now()->timestamp,
                ],
            ])
            ->get(route('auth.otp'));

        $response->assertStatus(200);
        $response->assertSee('Verifikasi Keamanan');
        $response->assertSee('Verifikasi &amp; Masuk ke Dashboard', false);
        $response->assertSee('min-h-[50px]');
    }

    public function test_register_otp_view_renders_with_apple_hig(): void
    {
        $response = $this->withSession([
            'pending_registration' => [
                'name' => 'Ibu Siti Baru',
                'email' => 'siti.baru.unik@bakery.com',
                'phone' => '6289991234567', // Unique phone not in DB
                'password' => bcrypt('secret123'),
                'business_name' => 'Bakery Enak Unik',
                'template_code' => null,
                'otp_hash' => bcrypt('123456'),
                'expires_at' => now()->addMinutes(10)->timestamp,
                'attempts' => 0,
                'last_sent_at' => now()->timestamp,
            ],
        ])->get(route('register.verify'));

        $response->assertStatus(200);
        $response->assertSee('Verifikasi WhatsApp');
        $response->assertSee('Verifikasi &amp; Buka Akun', false);
        $response->assertSee('min-h-[50px]');
    }
}
