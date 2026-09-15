<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AccountRecoveryRequest;
use App\Models\Admin;
use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AccountRecoveryFlowTest extends TestCase
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
        Storage::fake('local');

        $this->admin = Admin::create([
            'name' => 'Super Admin Platform',
            'email' => 'admin@cooca.id',
            'password' => 'secret123',
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'name' => 'Hendro Santoso',
            'email' => 'hendro.lama@gmail.com',
            'phone' => '6281234567890',
            'password' => 'password123',
        ]);

        $this->business = Business::create([
            'name' => 'Kopi Hendro Jaya',
            'slug' => 'kopi-hendro-jaya',
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

    public function test_user_can_view_account_recovery_form(): void
    {
        $response = $this->get(route('account-recovery.create'));
        $response->assertStatus(200);
        $response->assertSee('Pemulihan Akses Akun');
        $response->assertSee('Jenis Kendala Verifikasi');
    }

    public function test_owner_can_submit_recovery_request_with_documents(): void
    {
        $idCard = UploadedFile::fake()->image('ktp.jpg', 800, 600);
        $bizProof = UploadedFile::fake()->create('nib.pdf', 500, 'application/pdf');
        $selfie = UploadedFile::fake()->image('selfie.jpg', 600, 600);

        $response = $this->post(route('account-recovery.store'), [
            'applicant_name' => 'Hendro Santoso',
            'business_name' => 'Kopi Hendro Jaya',
            'old_email' => 'hendro.lama@gmail.com',
            'old_phone' => '081234567890',
            'new_email' => 'hendro.baru@gmail.com',
            'new_phone' => '081987654321',
            'issue_type' => 'both',
            'reason_description' => 'HP saya terjatuh ke sungai dan email lama terkunci karena 2FA di nomor HP lama.',
            'identity_card' => $idCard,
            'business_proof' => $bizProof,
            'selfie_proof' => $selfie,
        ]);

        $recovery = AccountRecoveryRequest::first();
        $this->assertNotNull($recovery);
        $this->assertSame('Hendro Santoso', $recovery->applicant_name);
        $this->assertSame('hendro.baru@gmail.com', $recovery->new_email);
        $this->assertSame('6281987654321', $recovery->new_phone);
        $this->assertSame(AccountRecoveryRequest::STATUS_PENDING, $recovery->status);

        $response->assertRedirect(route('account-recovery.status', $recovery->ticket_number));
        Storage::disk('local')->assertExists($recovery->identity_card_path);
        Storage::disk('local')->assertExists($recovery->business_proof_path);
        Storage::disk('local')->assertExists($recovery->selfie_proof_path);
    }

    public function test_admin_can_view_and_approve_recovery_request(): void
    {
        $recovery = AccountRecoveryRequest::create([
            'ticket_number' => 'REC-202609-TEST01',
            'user_id' => $this->user->id,
            'business_id' => $this->business->id,
            'business_name' => 'Kopi Hendro Jaya',
            'applicant_name' => 'Hendro Santoso',
            'old_email' => 'hendro.lama@gmail.com',
            'old_phone' => '6281234567890',
            'new_email' => 'hendro.resmi@gmail.com',
            'new_phone' => '628999888777',
            'issue_type' => 'phone_lost',
            'reason_description' => 'HP hilang dicuri saat perjalanan keluar kota.',
            'identity_card_path' => 'recoveries/test/ktp.jpg',
            'business_proof_path' => 'recoveries/test/nib.pdf',
            'status' => AccountRecoveryRequest::STATUS_PENDING,
        ]);

        // Admin checks listing
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.account-recoveries.index'));
        $response->assertStatus(200);
        $response->assertSee('REC-202609-TEST01');

        // Admin checks detail
        $detailResponse = $this->actingAs($this->admin, 'admin')->get(route('admin.account-recoveries.show', $recovery));
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('hendro.resmi@gmail.com');

        // Admin approves
        $approveResponse = $this->actingAs($this->admin, 'admin')->post(route('admin.account-recoveries.approve', $recovery), [
            'admin_notes' => 'KTP dan dokumen NIB telah diverifikasi sesuai.',
        ]);

        $approveResponse->assertRedirect(route('admin.account-recoveries.show', $recovery));
        $approveResponse->assertSessionHas('success');

        $recovery->refresh();
        $this->assertSame(AccountRecoveryRequest::STATUS_APPROVED, $recovery->status);
        $this->assertSame($this->admin->id, $recovery->approved_by);
        $this->assertNotNull($recovery->approved_at);

        // User credentials must be updated
        $this->user->refresh();
        $this->assertSame('hendro.resmi@gmail.com', $this->user->email);
        $this->assertSame('628999888777', $this->user->phone);
        $this->assertNotNull($this->user->email_verified_at);
    }

    public function test_admin_can_reject_recovery_request(): void
    {
        $recovery = AccountRecoveryRequest::create([
            'ticket_number' => 'REC-202609-REJECT',
            'user_id' => $this->user->id,
            'business_id' => $this->business->id,
            'business_name' => 'Kopi Hendro Jaya',
            'applicant_name' => 'Hendro Santoso',
            'old_email' => 'hendro.lama@gmail.com',
            'old_phone' => '6281234567890',
            'new_email' => 'fake.email@gmail.com',
            'new_phone' => '628111222333',
            'issue_type' => 'both',
            'reason_description' => 'HP hilang dan butuh akses secepatnya.',
            'identity_card_path' => 'recoveries/test/ktp.jpg',
            'business_proof_path' => 'recoveries/test/nib.pdf',
            'status' => AccountRecoveryRequest::STATUS_PENDING,
        ]);

        $rejectResponse = $this->actingAs($this->admin, 'admin')->post(route('admin.account-recoveries.reject', $recovery), [
            'rejection_reason' => 'Foto KTP buram dan tidak terbaca jelas.',
        ]);

        $rejectResponse->assertRedirect(route('admin.account-recoveries.show', $recovery));

        $recovery->refresh();
        $this->assertSame(AccountRecoveryRequest::STATUS_REJECTED, $recovery->status);
        $this->assertSame('Foto KTP buram dan tidak terbaca jelas.', $recovery->rejection_reason);

        // User credentials must remain unchanged
        $this->user->refresh();
        $this->assertSame('hendro.lama@gmail.com', $this->user->email);
    }

    public function test_admin_can_securely_stream_private_document_and_guest_is_forbidden(): void
    {
        Storage::disk('local')->put('recoveries/test/ktp.jpg', 'fake-image-content');

        $recovery = AccountRecoveryRequest::create([
            'ticket_number' => 'REC-202609-DOC01',
            'business_name' => 'Kopi Hendro Jaya',
            'applicant_name' => 'Hendro Santoso',
            'old_email' => 'hendro.lama@gmail.com',
            'new_email' => 'hendro.resmi@gmail.com',
            'new_phone' => '628999888777',
            'issue_type' => 'phone_lost',
            'reason_description' => 'Dokumen test streaming',
            'identity_card_path' => 'recoveries/test/ktp.jpg',
            'business_proof_path' => 'recoveries/test/nib.pdf',
            'status' => AccountRecoveryRequest::STATUS_PENDING,
        ]);

        // Guest is redirected or forbidden
        $guestResponse = $this->get(route('admin.account-recoveries.document', ['recovery' => $recovery, 'type' => 'identity']));
        $guestResponse->assertRedirect(route('admin.login'));

        // Admin can stream document
        $adminResponse = $this->actingAs($this->admin, 'admin')->get(route('admin.account-recoveries.document', ['recovery' => $recovery, 'type' => 'identity']));
        $adminResponse->assertStatus(200);
    }

    public function test_user_can_submit_recovery_with_flexible_phone_and_own_email(): void
    {
        Storage::fake('local');

        $idCard = UploadedFile::fake()->create('ktp.webp', 300, 'image/webp');
        $bizProof = UploadedFile::fake()->create('nib.pdf', 500, 'application/pdf');

        // Phone without 0/62 prefix (e.g. 81234567890) and keeping same email if recovering own account
        $response = $this->post(route('account-recovery.store'), [
            'applicant_name' => 'Budi Setiawan',
            'business_name' => 'Toko Budi Barokah',
            'old_email' => $this->user->email,
            'old_phone' => '08123456789',
            'new_email' => $this->user->email, // keep same email because phone_lost
            'new_phone' => '81234567890',      // phone starting with 8
            'issue_type' => 'phone_lost',
            'reason_description' => 'HP hilang',
            'identity_card' => $idCard,
            'business_proof' => $bizProof,
        ]);

        $recovery = AccountRecoveryRequest::where('applicant_name', 'Budi Setiawan')->first();
        $this->assertNotNull($recovery);
        $this->assertSame('6281234567890', $recovery->new_phone);
        $this->assertSame($this->user->email, $recovery->new_email);
        $response->assertRedirect(route('account-recovery.status', $recovery->ticket_number));
    }

    public function test_otp_page_automatically_syncs_to_new_phone_after_account_recovery_approval(): void
    {
        // 1. Simulasikan user dengan nomor lama memiliki sesi challenge OTP aktif
        $this->actingAs($this->user);

        $oldPhone = '6281234567890';
        $newPhone = '6282114468467';

        session()->put('auth_wa_otp_challenge', [
            'user_id' => $this->user->id,
            'phone' => $oldPhone,
            'otp_hash' => 'dummy_hash',
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
            'last_sent_at' => now()->timestamp,
            'delivery_error' => 'OTP gagal dikirim. Coba lagi.',
        ]);

        // 2. Buat pengajuan pemulihan akun yang disetujui Admin
        $recovery = AccountRecoveryRequest::create([
            'ticket_number' => 'REC-202609-SYNC01',
            'user_id' => $this->user->id,
            'business_id' => $this->business->id,
            'business_name' => 'Kopi Hendro Jaya',
            'applicant_name' => 'Hendro Santoso',
            'old_email' => 'hendro.lama@gmail.com',
            'new_email' => 'hendro.baru@gmail.com',
            'old_phone' => $oldPhone,
            'new_phone' => $newPhone,
            'issue_type' => 'both',
            'reason_description' => 'HP hilang dan butuh ganti nomor',
            'identity_card_path' => 'recoveries/fake.jpg',
            'business_proof_path' => 'recoveries/fake.pdf',
            'status' => AccountRecoveryRequest::STATUS_APPROVED,
            'approved_by' => $this->admin->id,
            'approved_at' => now(),
        ]);

        // Update user di DB sesuai persetujuan Admin
        $this->user->update([
            'email' => 'hendro.baru@gmail.com',
            'phone' => $newPhone,
        ]);

        // 3. Kunjungi halaman /auth/otp
        $response = $this->get(route('auth.otp'));
        $response->assertStatus(200);

        // Pastikan nomor yang ditampilkan adalah nomor baru (bukan nomor lama)
        $response->assertSee('6282******467');
        $response->assertDontSee('6281******890');

        // Pastikan informasi status persetujuan pemulihan akun ditampilkan
        $response->assertSee('Pemulihan Akun Berhasil Disetujui');
        $response->assertSee($newPhone);

        // Pastikan sesi challenge otomatis diperbarui ke nomor baru
        $challenge = session('auth_wa_otp_challenge');
        $this->assertSame($newPhone, $challenge['phone']);
    }
}

