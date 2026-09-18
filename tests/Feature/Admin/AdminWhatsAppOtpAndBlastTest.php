<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Business;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\WhatsAppAdminBlast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminWhatsAppOtpAndBlastTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): Admin
    {
        return Admin::factory()->create([
            'name'      => 'Super Administrator',
            'email'     => 'superadmin@cooca.id',
            'password'  => Hash::make('password123'),
            'role'      => 'super_admin',
            'is_active' => true,
        ]);
    }

    private function makeBusinessWithOwner(string $phone = '081234567890'): array
    {
        $user = User::factory()->create(['phone' => $phone]);
        $business = Business::create([
            'user_id'  => $user->id,
            'name'     => 'Bengkel Motor Berkah',
            'status'   => 'active',
            'currency' => 'IDR',
            'phone'    => $phone,
        ]);
        $business->users()->attach($user->id, ['role' => 'owner']);

        return [$business, $user];
    }

    public function test_admin_can_send_whatsapp_otp_via_ajax(): void
    {
        $admin = $this->makeAdmin();

        SystemSetting::set('wa_otp_active', '1', 'whatsapp');
        SystemSetting::set('meta_wa_token', 'EAAG_mock_token_secret', 'whatsapp', true);
        SystemSetting::set('meta_wa_phone_number_id', '10987654321', 'whatsapp');
        SystemSetting::set('meta_wa_otp_template', 'cooca_otp', 'whatsapp');

        Http::fake([
            'https://graph.facebook.com/v21.0/10987654321/messages' => Http::response([
                'messages' => [['id' => 'wamid.HBgLMTIzNDU2Nzg5MA==']],
            ], 200),
        ]);

        $payload = [
            'phone'    => '081234567890',
            'otp_code' => '849201',
        ];

        $response = $this->actingAs($admin, 'admin')->postJson(route('admin.whatsapp.send-otp'), $payload);

        $response->assertOk();
        $response->assertJson([
            'success'  => true,
            'otp_code' => '849201',
        ]);
    }

    public function test_admin_send_otp_generates_random_code_if_omitted(): void
    {
        $admin = $this->makeAdmin();

        SystemSetting::set('wa_otp_active', '1', 'whatsapp');
        SystemSetting::set('meta_wa_token', 'EAAG_mock_token_secret', 'whatsapp', true);
        SystemSetting::set('meta_wa_phone_number_id', '10987654321', 'whatsapp');

        Http::fake([
            'https://graph.facebook.com/v21.0/10987654321/messages' => Http::response([
                'messages' => [['id' => 'wamid.HBgLMTIzNDU2Nzg5MA==']],
            ], 200),
        ]);

        $payload = [
            'phone' => '081298765432',
        ];

        $response = $this->actingAs($admin, 'admin')->postJson(route('admin.whatsapp.send-otp'), $payload);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'otp_code',
            'phone',
            'message',
        ]);

        $otpCode = $response->json('otp_code');
        $this->assertEquals(6, strlen((string) $otpCode));
    }

    public function test_admin_send_otp_fails_validation_without_phone(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->postJson(route('admin.whatsapp.send-otp'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['phone']);
    }

    public function test_admin_send_otp_fails_when_channel_is_disabled(): void
    {
        $admin = $this->makeAdmin();

        SystemSetting::set('wa_otp_active', '0', 'whatsapp');

        $payload = [
            'phone'    => '081234567890',
            'otp_code' => '123456',
        ];

        $response = $this->actingAs($admin, 'admin')->postJson(route('admin.whatsapp.send-otp'), $payload);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'error'   => 'Kanal WhatsApp OTP dinonaktifkan oleh administrator.',
        ]);
    }

    public function test_admin_can_create_and_dispatch_blast(): void
    {
        $admin = $this->makeAdmin();
        $this->makeBusinessWithOwner('081234567890');

        SystemSetting::set('wa_blast_active', '1', 'whatsapp');
        SystemSetting::set('meta_wa_token', 'EAAG_mock_token_secret', 'whatsapp', true);
        SystemSetting::set('meta_wa_phone_number_id', '10987654321', 'whatsapp');

        Http::fake([
            'https://graph.facebook.com/v21.0/10987654321/messages' => Http::response([
                'messages' => [['id' => 'wamid.blast123']],
            ], 200),
        ]);

        $payload = [
            'title'         => 'Siaran Promosi Ramadan Cooca',
            'message'       => 'Halo {owner}, nikmati diskon 50% upgrade ke paket tahunan Cooca!',
            'target_filter' => 'all_owners',
        ];

        $response = $this->actingAs($admin, 'admin')->post(route('admin.whatsapp.blasts.store'), $payload);

        $blast = WhatsAppAdminBlast::latest()->first();
        $this->assertNotNull($blast);
        $this->assertEquals('Siaran Promosi Ramadan Cooca', $blast->title);

        $response->assertRedirect(route('admin.whatsapp.blasts.show', $blast));
        $this->assertDatabaseHas('whatsapp_admin_blasts', [
            'id'     => $blast->id,
            'status' => 'completed',
        ]);
    }
}
