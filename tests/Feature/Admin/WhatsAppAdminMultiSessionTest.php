<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Domain\WhatsApp\AdminWhatsAppService;
use App\Models\Admin;
use App\Models\Business;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\WhatsAppAccount;
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

    public function test_admin_can_view_platform_parent_setup(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.whatsapp.index', ['tab' => 'parent_setup']));

        $response->assertOk();
        $response->assertSee('Bot WhatsApp Platform Cooca');
        $response->assertSee('Kredensial Bot Induk Platform Meta');
        $response->assertSee('Meta System User Permanent Access Token');
    }

    public function test_admin_can_view_merchant_monitoring_tab(): void
    {
        $admin = $this->makeAdmin();

        $owner = User::factory()->create();
        $business = Business::create([
            'user_id' => $owner->id,
            'name'    => 'Kedai Kopi Nusantara',
            'slug'    => 'kedai-kopi-nusantara',
        ]);

        WhatsAppAccount::create([
            'business_id'          => $business->id,
            'waba_id'              => '109988776655443',
            'phone_number_id'      => '101122334455667',
            'phone_number'         => '6281234567890',
            'display_phone_number' => '+62 812-3456-7890',
            'verified_name'        => 'Kedai Kopi Nusantara',
            'access_token'         => 'EAAG_TEST_MERCHANT_TOKEN',
            'quality_rating'       => 'GREEN',
            'messaging_limit_tier' => 'TIER_1K',
            'status'               => 'CONNECTED',
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.whatsapp.index', ['tab' => 'merchants']));

        $response->assertOk();
        $response->assertSee('Monitoring Akun WhatsApp Merchant');
        $response->assertSee('Kedai Kopi Nusantara');
        $response->assertSee('+62 812-3456-7890');
    }

    public function test_admin_can_update_platform_credentials(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.whatsapp.config'), [
            'meta_token'           => 'EAAG_PLATFORM_SYSTEM_USER_TOKEN_NEW',
            'meta_phone_number_id' => '109988776655000',
            'meta_waba_id'         => '209988776655000',
            'meta_otp_template'    => 'auth_otp_v2',
            'otp_active'           => '1',
            'blast_active'         => '1',
        ]);

        $response->assertRedirect();
        $this->assertSame('EAAG_PLATFORM_SYSTEM_USER_TOKEN_NEW', SystemSetting::get('meta_wa_token'));
        $this->assertSame('109988776655000', SystemSetting::get('meta_wa_phone_number_id'));
        $this->assertSame('209988776655000', SystemSetting::get('meta_wa_waba_id'));
        $this->assertSame('auth_otp_v2', SystemSetting::get('meta_wa_otp_template'));
    }

    public function test_admin_service_sends_otp_via_meta_cloud_api(): void
    {
        SystemSetting::set('meta_wa_token', 'EAAG_PLATFORM_OFFICIAL_TOKEN');
        SystemSetting::set('meta_wa_phone_number_id', '998877665544332');
        SystemSetting::set('meta_wa_otp_template', 'cooca_otp_official');
        SystemSetting::set('wa_otp_active', '1');

        Http::fake([
            'https://graph.facebook.com/v21.0/998877665544332/messages' => Http::response([
                'messaging_product' => 'whatsapp',
                'contacts'          => [['input' => '6281299998888', 'wa_id' => '6281299998888']],
                'messages'          => [['id' => 'wamid.HBgLTESTDIRECTOTP==']],
            ], 200),
        ]);

        $service = app(AdminWhatsAppService::class);
        $result  = $service->sendOtp('081299998888', '778899');

        $this->assertTrue($result['success']);
        $this->assertSame('wamid.HBgLTESTDIRECTOTP==', $result['message_id']);
    }

    public function test_admin_service_broadcast_uses_meta_cloud_api(): void
    {
        SystemSetting::set('meta_wa_token', 'EAAG_PLATFORM_OFFICIAL_TOKEN');
        SystemSetting::set('meta_wa_phone_number_id', '998877665544332');
        SystemSetting::set('wa_blast_active', '1');

        Http::fake([
            'https://graph.facebook.com/v21.0/998877665544332/messages' => Http::response([
                'messaging_product' => 'whatsapp',
                'contacts'          => [['input' => '6281299998888', 'wa_id' => '6281299998888']],
                'messages'          => [['id' => 'wamid.HBgLTESTBLAST==']],
            ], 200),
        ]);

        $service = app(AdminWhatsAppService::class);
        $result  = $service->sendMessage('081299998888', 'Pemberitahuan Sistem COOCA');

        $this->assertTrue($result['success']);
        $this->assertSame('wamid.HBgLTESTBLAST==', $result['message_id']);
    }
}
