<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AccountRecoveryRequest;
use App\Models\Admin;
use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminAuthAndRecoveryAppleHigTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private User $user;
    private Business $business;
    private AccountRecoveryRequest $recovery;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->admin = Admin::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@cooca.id',
            'password' => bcrypt('adminpassword123'),
        ]);

        $this->user = User::create([
            'name' => 'Pak Hendra Gunawan',
            'email' => 'hendra.gunawan@example.com',
            'phone' => '6281234567890',
            'password' => bcrypt('password123'),
        ]);

        $this->business = Business::create([
            'name' => 'Kopi Kenangan Senja',
            'slug' => 'kopi-kenangan-senja',
            'phone' => '6281234567890',
            'currency' => 'IDR',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);

        $this->recovery = AccountRecoveryRequest::create([
            'ticket_number' => 'REC-202609-TEST',
            'user_id' => $this->user->id,
            'business_id' => $this->business->id,
            'business_name' => $this->business->name,
            'applicant_name' => $this->user->name,
            'old_email' => $this->user->email,
            'old_phone' => $this->user->phone,
            'new_email' => 'hendra.baru@example.com',
            'new_phone' => '6282199998888',
            'issue_type' => AccountRecoveryRequest::ISSUE_PHONE_LOST,
            'reason_description' => 'HP hilang saat perjalanan dinas luar kota.',
            'identity_card_path' => 'account_recoveries/test_ktp.jpg',
            'business_proof_path' => 'account_recoveries/test_nib.pdf',
            'selfie_proof_path' => 'account_recoveries/test_selfie.jpg',
            'status' => AccountRecoveryRequest::STATUS_PENDING,
            'ip_address' => '127.0.0.1',
        ]);
    }

    public function test_admin_login_view_renders_with_apple_hig_and_anti_zoom(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertStatus(200);
        $response->assertSee('Admin Console');
        $response->assertSee('Masuk ke Admin Console');
        $response->assertSee('text-[16px]');
        $response->assertSee('min-h-[50px]');
        $response->assertSee('name="email"', false);
        $response->assertSee('name="password"', false);
        $response->assertSee('name="remember"', false);
    }

    public function test_admin_forgot_password_view_renders_with_apple_hig(): void
    {
        $response = $this->get(route('admin.password.request'));

        $response->assertStatus(200);
        $response->assertSee('Atur Ulang Sandi');
        $response->assertSee('Kirim Tautan Pemulihan Kata Sandi');
        $response->assertSee('text-[16px]');
        $response->assertSee('min-h-[50px]');
        $response->assertSee('name="email"', false);
    }

    public function test_admin_reset_password_view_renders_with_apple_hig_and_dual_toggle(): void
    {
        $response = $this->get(route('admin.password.reset', ['token' => 'dummy-admin-token-12345', 'email' => 'superadmin@cooca.id']));

        $response->assertStatus(200);
        $response->assertSee('Kata Sandi Baru');
        $response->assertSee('Simpan Kata Sandi Baru &amp; Masuk', false);
        $response->assertSee('text-[16px]');
        $response->assertSee('min-h-[50px]');
        $response->assertSee('name="password"', false);
        $response->assertSee('name="password_confirmation"', false);
    }

    public function test_admin_account_recoveries_index_renders_with_bento_tiles_and_anti_zoom(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.account-recoveries.index'));

        $response->assertStatus(200);
        $response->assertSee('Pusat Verifikasi Pemulihan Akun');
        $response->assertSee('Menunggu Review');
        $response->assertSee('Total Pengajuan');
        $response->assertSee('Disetujui Resmi');
        $response->assertSee('Permohonan Ditolak');
        $response->assertSee('REC-202609-TEST');
        $response->assertSee('Kopi Kenangan Senja');
        $response->assertSee('Periksa Berkas');
        $response->assertSee('text-[16px]');
    }

    public function test_admin_account_recoveries_show_renders_with_bento_inspection_desk(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.account-recoveries.show', $this->recovery));

        $response->assertStatus(200);
        $response->assertSee('REC-202609-TEST');
        $response->assertSee('Komparasi Data Akun Terdaftar vs Kontak Baru');
        $response->assertSee('Meja Uji Bukti Otentik Kepemilikan Akun');
        $response->assertSee('1. Foto KTP Asli');
        $response->assertSee('2. Dokumen Usaha');
        $response->assertSee('3. Selfie dengan KTP');
        $response->assertSee('Tindakan Administrator');
        $response->assertSee('Setujui &amp; Perbarui Akses Akun', false);
        $response->assertSee('Tolak Permohonan');
        $response->assertSee('hendra.baru@example.com');
        $response->assertSee('6282199998888');
    }
}
