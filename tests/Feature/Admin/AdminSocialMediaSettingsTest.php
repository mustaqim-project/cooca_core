<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Domain\SocialMedia\Clients\MetaSocialMediaClient;
use App\Domain\SocialMedia\Clients\TikTokClient;
use App\Models\Admin;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSocialMediaSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): Admin
    {
        return Admin::factory()->create([
            'name'      => 'Super Admin Cooca',
            'email'     => 'admin@cooca.id',
            'password'  => Hash::make('password123'),
            'role'      => 'super_admin',
            'is_active' => true,
        ]);
    }

    /**
     * Test guest cannot access admin settings.
     */
    public function test_guest_cannot_access_admin_settings(): void
    {
        $response = $this->get(route('admin.settings.index', ['tab' => 'social']));
        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Test superadmin can view social media tab in admin/settings.
     */
    public function test_admin_can_view_social_media_settings_tab(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.settings.index', ['tab' => 'social']));

        $response->assertOk();
        $response->assertSee('Pusat Konfigurasi Media Sosial Terpadu (Meta &amp; TikTok)', false);
        $response->assertSee('Meta Platform (Facebook, Instagram &amp; Threads)', false);
        $response->assertSee('TikTok Developer Platform (Content Posting API)', false);
        $response->assertSee('TikTok Client Key');
    }

    /**
     * Test superadmin can update Meta and TikTok settings via admin/settings with zero .env dependency.
     */
    public function test_admin_can_save_social_media_credentials_to_database(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.settings.update'), [
            'active_tab'                        => 'social',
            'social_media_app_id'               => '998877665544332',
            'social_media_app_secret'           => 'meta_app_secret_db_123',
            'social_media_webhook_verify_token' => 'meta_webhook_token_db_456',
            'social_media_graph_version'        => 'v21.0',
            'tiktok_client_key'                 => 'tiktok_key_db_777',
            'tiktok_client_secret'              => 'tiktok_secret_db_888',
        ]);

        $response->assertRedirect(route('admin.settings.index', ['tab' => 'social']));
        $response->assertSessionHas('success');

        // 1. Verify saved in system_settings database table
        $this->assertSame('998877665544332', SystemSetting::get('social_media_app_id'));
        $this->assertSame('meta_app_secret_db_123', SystemSetting::get('social_media_app_secret'));
        $this->assertSame('meta_webhook_token_db_456', SystemSetting::get('social_media_webhook_verify_token'));
        $this->assertSame('v21.0', SystemSetting::get('social_media_graph_version'));

        $this->assertSame('tiktok_key_db_777', SystemSetting::get('tiktok_client_key'));
        $this->assertSame('tiktok_secret_db_888', SystemSetting::get('tiktok_client_secret'));

        // 2. Verify secrets marked with is_secret = true
        $this->assertTrue((bool) SystemSetting::where('key', 'social_media_app_secret')->value('is_secret'));
        $this->assertTrue((bool) SystemSetting::where('key', 'tiktok_client_secret')->value('is_secret'));

        // 3. Verify MetaSocialMediaClient and TikTokClient resolve directly from database without .env
        $metaClient = new MetaSocialMediaClient();
        $this->assertSame('998877665544332', $metaClient->getAppId());
        $this->assertSame('meta_app_secret_db_123', $metaClient->getAppSecret());
        $this->assertSame('v21.0', $metaClient->getGraphVersion());

        $tiktokClient = new TikTokClient();
        $this->assertSame('tiktok_key_db_777', $tiktokClient->getClientKey());
        $this->assertTrue($tiktokClient->isConfigured());
    }

    /**
     * Test admin can invoke the social media credentials diagnostic endpoint.
     */
    public function test_admin_can_test_social_media_credentials(): void
    {
        $admin = $this->makeAdmin();

        SystemSetting::set('social_media_app_id', 'test_app_id_123');
        SystemSetting::set('social_media_app_secret', 'test_app_secret_456');
        SystemSetting::set('tiktok_client_key', 'tiktok_key_valid_123');
        SystemSetting::set('tiktok_client_secret', 'tiktok_secret_valid_456789');

        $response = $this->actingAs($admin, 'admin')->postJson(route('admin.settings.test-social'));

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'meta'   => ['configured', 'app_id', 'status', 'message'],
                'tiktok' => ['configured', 'client_key', 'status', 'message'],
            ],
        ]);

        $this->assertTrue($response->json('data.meta.configured'));
        $this->assertTrue($response->json('data.tiktok.configured'));
        $this->assertSame('valid', $response->json('data.tiktok.status'));
    }

    /**
     * Test unauthenticated user cannot test social media credentials.
     */
    public function test_guest_cannot_test_social_media_credentials(): void
    {
        $response = $this->postJson(route('admin.settings.test-social'));
        $response->assertUnauthorized();
    }
}
