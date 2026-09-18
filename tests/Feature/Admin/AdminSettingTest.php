<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Domain\Payment\TripayService;
use App\Models\Admin;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminSettingTest extends TestCase
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

    public function test_superadmin_can_view_settings_hub_with_all_tabs(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.settings.index'));

        $response->assertOk();
        $response->assertSee('OAuth &amp; Sistem', false);
        $response->assertSee('Pembayaran (TriPay)', false);
        $response->assertSee('WhatsApp Cloud API', false);
        $response->assertSee('Media Sosial', false);
        $response->assertSee('SMTP Email', false);
    }

    public function test_superadmin_can_view_smtp_tab_directly_from_smtp_route(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.smtp.index'));

        $response->assertOk();
        $response->assertViewHas('defaultTab', 'smtp');
        $response->assertSee('Konfigurasi Server SMTP Terpusat');
    }

    public function test_superadmin_can_save_tripay_payment_gateway_settings(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.settings.update'), [
            'active_tab'           => 'payment',
            'tripay_merchant_code' => 'T38171_TEST',
            'tripay_api_key'       => 'DEV-TEST-KEY-1234567890',
            'tripay_private_key'   => 'SEC-TEST-PRIVATE-KEY-999',
            'tripay_is_production' => '0',
            'tripay_sandbox_url'   => 'https://tripay.co.id/api-sandbox/',
            'tripay_prod_url'      => 'https://tripay.co.id/api/',
        ]);

        $response->assertRedirect(route('admin.settings.index', ['tab' => 'payment']));
        $this->assertSame('T38171_TEST', SystemSetting::get('tripay_merchant_code'));
        $this->assertSame('DEV-TEST-KEY-1234567890', SystemSetting::get('tripay_api_key'));
        $this->assertSame('SEC-TEST-PRIVATE-KEY-999', SystemSetting::get('tripay_private_key'));
        $this->assertSame('0', SystemSetting::get('tripay_is_production'));
        $this->assertSame('https://tripay.co.id/api-sandbox/', SystemSetting::get('tripay_sandbox_url'));
        $this->assertSame('https://tripay.co.id/api/', SystemSetting::get('tripay_prod_url'));

        // Verify TripayService dynamically uses these database values
        $service = new TripayService();
        $this->assertSame('DEV-TEST-KEY-1234567890', $this->getServiceProperty($service, 'apiKey'));
        $this->assertSame('T38171_TEST', $this->getServiceProperty($service, 'merchantCode'));
        $this->assertFalse($this->getServiceProperty($service, 'isProduction'));
    }

    public function test_superadmin_can_save_meta_whatsapp_cloud_api_settings(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.settings.update'), [
            'active_tab'                   => 'whatsapp',
            'meta_wa_app_id'               => '1454871749894754',
            'meta_wa_app_secret'           => 'secret_meta_wa_123',
            'meta_wa_phone_number_id'      => '1311095538754578',
            'meta_wa_waba_id'              => '4663536093891174',
            'meta_wa_token'                => 'EAAUrMrnYomIBSt0WqjV9S853dQj5ZB8bLg86ynZBMQiMlsB...',
            'meta_wa_webhook_verify_token' => 'cooca_meta_wa_webhook_secret',
            'meta_wa_graph_version'        => 'v25.0',
            'meta_wa_graph_url'            => 'https://graph.facebook.com',
        ]);

        $response->assertRedirect(route('admin.settings.index', ['tab' => 'whatsapp']));
        $this->assertSame('1454871749894754', SystemSetting::get('meta_wa_app_id'));
        $this->assertSame('secret_meta_wa_123', SystemSetting::get('meta_wa_app_secret'));
        $this->assertSame('1311095538754578', SystemSetting::get('meta_wa_phone_number_id'));
        $this->assertSame('4663536093891174', SystemSetting::get('meta_wa_waba_id'));
        $this->assertSame('EAAUrMrnYomIBSt0WqjV9S853dQj5ZB8bLg86ynZBMQiMlsB...', SystemSetting::get('meta_wa_token'));
        $this->assertSame('cooca_meta_wa_webhook_secret', SystemSetting::get('meta_wa_webhook_verify_token'));
        $this->assertSame('v25.0', SystemSetting::get('meta_wa_graph_version'));
    }

    public function test_tripay_test_connection_endpoint_success(): void
    {
        $admin = $this->makeAdmin();
        SystemSetting::set('tripay_api_key', 'DEV-TEST-KEY-123456');
        SystemSetting::set('tripay_is_production', '0');

        Http::fake([
            'https://tripay.co.id/api-sandbox/payment/channel' => Http::response([
                'success' => true,
                'data'    => [
                    ['code' => 'QRIS', 'name' => 'QRIS Dinamis'],
                    ['code' => 'BCAVA', 'name' => 'BCA Virtual Account'],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($admin, 'admin')->postJson(route('admin.settings.test-tripay'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [
                'mode' => 'Sandbox',
                'channels_count' => 2,
            ],
        ]);
    }

    public function test_whatsapp_test_connection_endpoint_success(): void
    {
        $admin = $this->makeAdmin();
        SystemSetting::set('meta_wa_token', 'EAAG_VALID_TOKEN_123');
        SystemSetting::set('meta_wa_phone_number_id', '1311095538754578');
        SystemSetting::set('meta_wa_graph_version', 'v25.0');

        Http::fake([
            'https://graph.facebook.com/v25.0/1311095538754578*' => Http::response([
                'id'                   => '1311095538754578',
                'verified_name'        => 'Test Number',
                'display_phone_number' => '+1 555-184-6167',
                'quality_rating'       => 'GREEN',
            ], 200),
        ]);

        $response = $this->actingAs($admin, 'admin')->postJson(route('admin.settings.test-whatsapp'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => '1311095538754578',
                'verified_name' => 'Test Number',
                'quality_rating' => 'GREEN',
            ],
        ]);
    }

    public function test_superadmin_can_save_instagram_platform_settings(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.settings.update'), [
            'active_tab'             => 'social',
            'instagram_app_id'       => '1813131243044390',
            'instagram_app_name'     => 'Cooca-IG',
            'instagram_app_secret'   => 'e2147bd1f78dccafeea8b72a92ae3fa8',
            'instagram_account_id'   => '17841439846162016',
            'instagram_username'     => 'cooca.indonesia',
            'instagram_access_token' => 'IGAAZAxCIOsPiZABZAFky...',
        ]);

        $response->assertRedirect(route('admin.settings.index', ['tab' => 'social']));
        $this->assertSame('1813131243044390', SystemSetting::get('instagram_app_id'));
        $this->assertSame('Cooca-IG', SystemSetting::get('instagram_app_name'));
        $this->assertSame('e2147bd1f78dccafeea8b72a92ae3fa8', SystemSetting::get('instagram_app_secret'));
        $this->assertSame('17841439846162016', SystemSetting::get('instagram_account_id'));
        $this->assertSame('cooca.indonesia', SystemSetting::get('instagram_username'));
        $this->assertSame('IGAAZAxCIOsPiZABZAFky...', SystemSetting::get('instagram_access_token'));
    }

    public function test_instagram_test_connection_endpoint_success(): void
    {
        $admin = $this->makeAdmin();
        SystemSetting::set('instagram_access_token', 'IGAA_VALID_TOKEN_123');

        Http::fake([
            'https://graph.instagram.com/v21.0/me*' => Http::response([
                'id'                  => '28475871372070145',
                'username'            => 'cooca.indonesia',
                'account_type'        => 'MEDIA_CREATOR',
                'media_count'         => 11,
                'profile_picture_url' => 'https://instagram.example.com/pic.jpg',
            ], 200),
        ]);

        $response = $this->actingAs($admin, 'admin')->postJson(route('admin.settings.test-instagram'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [
                'id'           => '28475871372070145',
                'username'     => 'cooca.indonesia',
                'account_type' => 'MEDIA_CREATOR',
                'media_count'  => 11,
            ],
        ]);
    }

    public function test_social_media_test_all_includes_instagram(): void
    {
        $admin = $this->makeAdmin();
        SystemSetting::set('instagram_access_token', 'IGAA_VALID_TOKEN_123');

        Http::fake([
            'https://graph.instagram.com/v21.0/me*' => Http::response([
                'id'           => '28475871372070145',
                'username'     => 'cooca.indonesia',
                'account_type' => 'MEDIA_CREATOR',
                'media_count'  => 11,
            ], 200),
        ]);

        $response = $this->actingAs($admin, 'admin')->postJson(route('admin.settings.test-social'));

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'meta',
                'tiktok',
                'instagram',
            ],
        ]);
        $response->assertJson([
            'data' => [
                'instagram' => [
                    'status' => 'valid',
                ],
            ],
        ]);
    }

    private function getServiceProperty(object $object, string $property): mixed
    {
        $reflection = new \ReflectionClass($object);
        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);
        return $prop->getValue($object);
    }
}
