<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Domain\WhatsApp\AdminWhatsAppService;
use App\Domain\WhatsApp\Drivers\MetaWhatsAppCloudDriver;
use App\Models\Admin;
use App\Models\SystemSetting;
use App\Models\WhatsAppAdminSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppAdminMultiSessionTest extends TestCase
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

    public function test_admin_can_view_empty_state_when_no_sessions_exist(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.whatsapp.index', ['tab' => 'connection']));

        $response->assertOk();
        $response->assertSee('WhatsApp Admin Belum Terhubung');
        $response->assertSee('Mulai sesi baru untuk menampilkan kode QR dan menghubungkan nomor WhatsApp resmi Cooca.');
        $response->assertSee('Mulai & Tampilkan Kode QR');
        $response->assertSee('Pilihan Jalur OTP');
        $response->assertSee('Pilihan Jalur Siaran');
        $response->assertSee('Meta WhatsApp Cloud API (Resmi &amp; Sangat Stabil)', false);
        $response->assertSee('Scan QR Baileys (Server Lokal)', false);
    }

    public function test_admin_can_list_whatsapp_admin_sessions_via_json(): void
    {
        $admin = $this->makeAdmin();

        WhatsAppAdminSession::create([
            'session_id'   => 'admin_wa_1',
            'name'         => 'CS Support 1',
            'phone_number' => '6281234567891',
            'status'       => 'connected',
            'is_active'    => true,
        ]);

        WhatsAppAdminSession::create([
            'session_id'   => 'admin_wa_2',
            'name'         => 'CS Support 2',
            'phone_number' => '6281234567892',
            'status'       => 'disconnected',
            'is_active'    => false,
        ]);

        $response = $this->actingAs($admin, 'admin')->getJson(route('admin.whatsapp.sessions.index'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonCount(2, 'sessions');
    }

    public function test_admin_can_create_new_session_and_fetch_qr(): void
    {
        $admin = $this->makeAdmin();

        Http::fake([
            '*/session/start' => Http::response([
                'success' => true,
                'status'  => 'scan_qr',
                'qr'      => 'data:image/png;base64,FAKE_QR_IMAGE_DATA',
            ], 200),
            '*/session/qr*' => Http::response([
                'status' => 'scan_qr',
                'qr'     => 'data:image/png;base64,FAKE_QR_IMAGE_DATA',
            ], 200),
            '*/session/status*' => Http::response([
                'status' => 'scan_qr',
            ], 200),
        ]);

        $createResponse = $this->actingAs($admin, 'admin')->postJson(route('admin.whatsapp.sessions.store'), [
            'name' => 'CS WhatsApp Utama',
        ]);

        $createResponse->assertOk();
        $createResponse->assertJson([
            'success' => true,
        ]);

        $session = WhatsAppAdminSession::where('name', 'CS WhatsApp Utama')->first();
        $this->assertNotNull($session);
        $this->assertSame('scan_qr', $session->status);

        // Fetch QR
        $qrResponse = $this->actingAs($admin, 'admin')->getJson(route('admin.whatsapp.sessions.qr', $session->session_id));
        $qrResponse->assertOk();
        $qrResponse->assertJsonStructure(['qrDataUrl', 'status']);

        // Fetch Status
        $statusResponse = $this->actingAs($admin, 'admin')->getJson(route('admin.whatsapp.sessions.status', $session->session_id));
        $statusResponse->assertOk();
        $statusResponse->assertJsonStructure(['status']);
    }

    public function test_admin_can_toggle_session_pool_active_and_delete_session(): void
    {
        $admin = $this->makeAdmin();

        $session = WhatsAppAdminSession::create([
            'session_id'   => 'admin_wa_pool_toggle',
            'name'         => 'Nomor Testing',
            'phone_number' => '628999999999',
            'status'       => 'connected',
            'is_active'    => true,
        ]);

        // Toggle active -> false
        $toggleResp1 = $this->actingAs($admin, 'admin')->postJson(route('admin.whatsapp.sessions.toggle-active', $session->session_id));
        $toggleResp1->assertOk();
        $this->assertFalse($session->fresh()->is_active);

        // Toggle active -> true
        $toggleResp2 = $this->actingAs($admin, 'admin')->postJson(route('admin.whatsapp.sessions.toggle-active', $session->session_id));
        $toggleResp2->assertOk();
        $this->assertTrue($session->fresh()->is_active);

        // Delete session
        Http::fake([
            '*/session/logout' => Http::response(['success' => true], 200),
        ]);

        $deleteResp = $this->actingAs($admin, 'admin')->deleteJson(route('admin.whatsapp.sessions.destroy', $session->session_id));
        $deleteResp->assertOk();
        $this->assertDatabaseMissing('whatsapp_admin_sessions', [
            'session_id' => 'admin_wa_pool_toggle',
        ]);
    }

    public function test_baileys_random_pool_selects_from_active_connected_sessions(): void
    {
        SystemSetting::set('wa_otp_driver', 'baileys');
        SystemSetting::set('wa_otp_active', '1');

        WhatsAppAdminSession::create([
            'session_id'   => 'admin_pool_1',
            'name'         => 'Nomor Pool 1',
            'phone_number' => '628111111111',
            'status'       => 'connected',
            'is_active'    => true,
        ]);

        WhatsAppAdminSession::create([
            'session_id'   => 'admin_pool_2',
            'name'         => 'Nomor Pool 2',
            'phone_number' => '628222222222',
            'status'       => 'connected',
            'is_active'    => true,
        ]);

        // Disconnected session (should never be selected)
        WhatsAppAdminSession::create([
            'session_id'   => 'admin_pool_inactive',
            'name'         => 'Nomor Pool Inactive',
            'phone_number' => '628333333333',
            'status'       => 'disconnected',
            'is_active'    => false,
        ]);

        $usedSessionIds = [];

        Http::fake(function ($request) use (&$usedSessionIds) {
            if (str_contains($request->url(), '/send-message')) {
                $payload = json_decode($request->body(), true);
                $usedSessionIds[] = $payload['session'] ?? null;
                return Http::response(['success' => true, 'messageId' => 'msg_' . uniqid()], 200);
            }
            return Http::response(['status' => 'connected'], 200);
        });

        $service = app(AdminWhatsAppService::class);

        // Run 10 OTP sends
        for ($i = 0; $i < 10; $i++) {
            $service->sendOtp('081234567890', '123456');
        }

        $this->assertCount(10, $usedSessionIds);
        foreach ($usedSessionIds as $sid) {
            $this->assertContains($sid, ['admin_pool_1', 'admin_pool_2']);
            $this->assertNotSame('admin_pool_inactive', $sid);
        }
    }

    public function test_meta_cloud_api_is_direct_and_not_randomized(): void
    {
        SystemSetting::set('wa_otp_driver', 'meta_cloud');
        SystemSetting::set('wa_otp_active', '1');
        SystemSetting::set('meta_wa_token', 'EAAG_OFFICIAL_TOKEN');
        SystemSetting::set('meta_wa_phone_number_id', '555444333222111');
        SystemSetting::set('meta_wa_otp_template', 'cooca_otp_official');

        // Even if multiple Baileys sessions exist in DB
        WhatsAppAdminSession::create([
            'session_id'   => 'admin_baileys_a',
            'status'       => 'connected',
            'is_active'    => true,
        ]);

        WhatsAppAdminSession::create([
            'session_id'   => 'admin_baileys_b',
            'status'       => 'connected',
            'is_active'    => true,
        ]);

        $this->mock(MetaWhatsAppCloudDriver::class, function ($mock) {
            $mock->shouldReceive('sendOtp')
                ->once()
                ->with('081299998888', '778899', 'cooca_otp_official', 'EAAG_OFFICIAL_TOKEN', '555444333222111')
                ->andReturn([
                    'success'    => true,
                    'message_id' => 'wamid.HBgLTESTDIRECT==',
                ]);
        });

        $service = app(AdminWhatsAppService::class);
        $result = $service->sendOtp('081299998888', '778899');

        $this->assertTrue($result['success']);
    }
}
