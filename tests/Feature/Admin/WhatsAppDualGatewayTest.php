<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Domain\WhatsApp\AdminWhatsAppService;
use App\Domain\WhatsApp\Drivers\MetaWhatsAppCloudDriver;
use App\Domain\WhatsApp\WhatsAppGatewayService;
use App\Models\Admin;
use App\Models\Business;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\WhatsAppSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WhatsAppDualGatewayTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): Admin
    {
        return Admin::factory()->create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@cooca.id',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);
    }

    private function makeOwnerWithBusiness(): array
    {
        $user = User::factory()->create([
            'name' => 'Owner Coffee',
            'email' => 'owner@cooca.id',
            'phone' => '081234567890',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $business = Business::create([
            'user_id' => $user->id,
            'name' => 'Kopi Sejahtera',
            'status' => 'active',
            'currency' => 'IDR',
        ]);

        $business->users()->attach($user->id, [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'role' => 'owner',
        ]);

        $user->update(['active_business_id' => $business->id]);
        \App\Support\Context::setBusiness($business);

        return [$user, $business];
    }

    public function test_admin_view_shows_platform_parent_setup_configuration(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.whatsapp.index', ['tab' => 'parent_setup']));

        $response->assertOk();
        $response->assertSee('Bot WhatsApp Platform Cooca');
        $response->assertSee('Kredensial Bot Induk Platform Meta');
        $response->assertSee('Meta System User Permanent Access Token');
    }

    public function test_admin_can_update_dual_gateway_configuration(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.whatsapp.config'), [
            'otp_active'           => '1',
            'blast_active'         => '1',
            'meta_token'           => 'EAAG_TEST_TOKEN_12345',
            'meta_phone_number_id' => '104928374619283',
            'meta_waba_id'         => '109283746501928',
            'meta_otp_template'    => 'cooca_otp_official',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertSame('meta_cloud', SystemSetting::get('wa_otp_driver'));
        $this->assertSame('meta_cloud', SystemSetting::get('wa_blast_driver'));
        $this->assertSame('1', SystemSetting::get('wa_otp_active'));
        $this->assertSame('1', SystemSetting::get('wa_blast_active'));
        $this->assertSame('104928374619283', SystemSetting::get('meta_wa_phone_number_id'));
    }

    public function test_admin_service_routes_otp_to_meta_cloud_when_selected(): void
    {
        SystemSetting::set('wa_otp_driver', 'meta_cloud');
        SystemSetting::set('wa_otp_active', '1');
        SystemSetting::set('meta_wa_token', 'EAAG_TEST_TOKEN');
        SystemSetting::set('meta_wa_phone_number_id', '10987654321');
        SystemSetting::set('meta_wa_otp_template', 'my_otp_template');

        \Illuminate\Support\Facades\Http::fake([
            'https://graph.facebook.com/v21.0/10987654321/messages' => \Illuminate\Support\Facades\Http::response([
                'messaging_product' => 'whatsapp',
                'contacts'          => [['input' => '081234567890', 'wa_id' => '6281234567890']],
                'messages'          => [['id' => 'wamid.HBgLM...==']],
            ], 200),
        ]);

        $adminWa = app(AdminWhatsAppService::class);
        $result = $adminWa->sendOtp('081234567890', '654321');

        $this->assertTrue($result['success']);
    }

    public function test_admin_service_skips_otp_when_channel_is_disabled(): void
    {
        SystemSetting::set('wa_otp_driver', 'disabled');
        SystemSetting::set('wa_otp_active', '0');

        $adminWa = app(AdminWhatsAppService::class);
        $result = $adminWa->sendOtp('081234567890', '654321');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('dinonaktifkan', $result['error']);
    }

    public function test_owner_can_switch_provider_to_meta_cloud_and_save_credentials(): void
    {
        [$user, $business] = $this->makeOwnerWithBusiness();

        $response = $this->actingAs($user, 'web')
            ->withSession(['active_business_id' => $business->id])
            ->post(route('whatsapp.settings'), [
                'provider'             => 'meta_cloud',
                'is_active'            => '1',
                'meta_access_token'    => 'EAAG_OWNER_TOKEN_999',
                'meta_phone_number_id' => '888777666',
                'meta_waba_id'         => '111222333',
                'auto_send_receipt'    => '1',
                'receipt_template'     => 'Terima kasih telah berbelanja di {business_name}!',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $session = WhatsAppSession::where('business_id', $business->id)->first();
        $this->assertNotNull($session);
        $this->assertSame('meta_cloud', $session->provider);
        $this->assertTrue($session->is_active);
        $this->assertSame('888777666', $session->meta_phone_number_id);
        $this->assertSame('connected', $session->status);
    }

    public function test_owner_can_toggle_whatsapp_inactive(): void
    {
        [$user, $business] = $this->makeOwnerWithBusiness();

        WhatsAppSession::create([
            'business_id' => $business->id,
            'session_id'  => 'biz_test_toggle',
            'provider'    => 'meta_cloud',
            'is_active'   => true,
            'status'      => 'connected',
        ]);

        $response = $this->actingAs($user, 'web')
            ->withSession(['active_business_id' => $business->id])
            ->post(route('whatsapp.settings'), [
                'provider'  => 'meta_cloud',
                'is_active' => '0',
            ]);

        $response->assertRedirect();

        $session = WhatsAppSession::where('business_id', $business->id)->first();
        $this->assertFalse($session->is_active);

        // Gateway should reject messages when inactive
        $gateway = app(WhatsAppGatewayService::class);
        $result = $gateway->sendMessage($business, '081234567890', 'Halo tester');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('dinonaktifkan', $result['error']);
    }

    public function test_gateway_routes_to_meta_driver_when_business_provider_is_meta_cloud(): void
    {
        [$user, $business] = $this->makeOwnerWithBusiness();

        WhatsAppSession::create([
            'business_id'          => $business->id,
            'session_id'           => 'biz_meta_test',
            'provider'             => 'meta_cloud',
            'is_active'            => true,
            'status'               => 'connected',
            'meta_access_token'    => 'EAAG_LIVE_TOKEN',
            'meta_phone_number_id' => '9988776655',
        ]);

        $this->mock(MetaWhatsAppCloudDriver::class, function ($mock) {
            $mock->shouldReceive('sendTextMessage')
                ->once()
                ->with('081234567890', 'Pesan via Meta', 'EAAG_LIVE_TOKEN', '9988776655')
                ->andReturn([
                    'success'    => true,
                    'message_id' => 'wamid.META_TEST_123',
                ]);
        });

        $gateway = app(WhatsAppGatewayService::class);
        $result = $gateway->sendMessage($business, '081234567890', 'Pesan via Meta');

        $this->assertTrue($result['success']);
    }

    public function test_admin_can_verify_meta_credentials_via_ajax(): void
    {
        $admin = $this->makeAdmin();

        $this->mock(MetaWhatsAppCloudDriver::class, function ($mock) {
            $mock->shouldReceive('verifyCredentials')
                ->once()
                ->with('EAAG_ADMIN_TEST', '123456789')
                ->andReturn([
                    'success' => true,
                    'data'    => [
                        'id'                   => '123456789',
                        'verified_name'        => 'Cooca Official HQ',
                        'display_phone_number' => '+62 823-3749-9577',
                        'quality_rating'       => 'GREEN',
                    ],
                ]);
        });

        $response = $this->actingAs($admin, 'admin')->postJson(route('admin.whatsapp.verify-meta'), [
            'token'           => 'EAAG_ADMIN_TEST',
            'phone_number_id' => '123456789',
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.verified_name', 'Cooca Official HQ');
    }

    public function test_owner_can_verify_meta_credentials_via_ajax(): void
    {
        [$user, $business] = $this->makeOwnerWithBusiness();

        $this->mock(MetaWhatsAppCloudDriver::class, function ($mock) {
            $mock->shouldReceive('verifyCredentials')
                ->once()
                ->with('EAAG_OWNER_TOKEN', '987654321')
                ->andReturn([
                    'success' => true,
                    'data'    => [
                        'id'                   => '987654321',
                        'verified_name'        => 'Kopi Sejahtera Official',
                        'display_phone_number' => '+62 812-3456-7890',
                        'quality_rating'       => 'GREEN',
                    ],
                ]);
        });

        $response = $this->actingAs($user, 'web')
            ->withSession(['active_business_id' => $business->id])
            ->postJson(route('whatsapp.verify-meta'), [
                'token'           => 'EAAG_OWNER_TOKEN',
                'phone_number_id' => '987654321',
            ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.verified_name', 'Kopi Sejahtera Official');
    }

    public function test_owner_receipt_update_does_not_overwrite_meta_credentials_or_provider(): void
    {
        [$user, $business] = $this->makeOwnerWithBusiness();

        WhatsAppSession::create([
            'business_id'          => $business->id,
            'session_id'           => 'biz_keep_meta',
            'provider'             => 'meta_cloud',
            'is_active'            => true,
            'status'               => 'connected',
            'meta_access_token'    => 'SECRET_META_TOKEN',
            'meta_phone_number_id' => '5544332211',
            'meta_waba_id'         => '99887711',
            'auto_send_receipt'    => false,
        ]);

        // Submit only receipt update
        $response = $this->actingAs($user, 'web')
            ->withSession(['active_business_id' => $business->id])
            ->post(route('whatsapp.settings'), [
                'auto_send_receipt' => '1',
                'receipt_template'  => 'Catatan struk toko kopi',
            ]);

        $response->assertRedirect();

        $session = WhatsAppSession::where('business_id', $business->id)->first();
        $this->assertSame('meta_cloud', $session->provider, 'Provider must not be reset to baileys');
        $this->assertSame('SECRET_META_TOKEN', $session->meta_access_token, 'Token must not be erased');
        $this->assertSame('5544332211', $session->meta_phone_number_id, 'Phone number ID must not be erased');
        $this->assertTrue($session->auto_send_receipt, 'Auto send receipt must be updated to true');
        $this->assertSame('Catatan struk toko kopi', $session->receipt_template);
    }
}
