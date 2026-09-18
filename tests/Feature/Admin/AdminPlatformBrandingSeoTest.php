<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPlatformBrandingSeoTest extends TestCase
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

    public function test_superadmin_can_view_branding_social_and_seo_tabs_in_settings(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.settings.index'));

        $response->assertOk();
        $response->assertSee('Branding &amp; Logo', false);
        $response->assertSee('Medsos Resmi CMS', false);
        $response->assertSee('SEO Komplit CMS', false);
        $response->assertSee('Identitas Visual &amp; Logo Platform', false);
        $response->assertSee('Kanal Media Sosial &amp; Komunitas Resmi', false);
        $response->assertSee('Simulasi Hasil Pencarian Google (SERP)', false);
    }

    public function test_superadmin_can_upload_and_reset_branding_logos_and_favicon(): void
    {
        Storage::fake('public');
        $admin = $this->makeAdmin();

        $logoLight = UploadedFile::fake()->image('logo_light.png', 400, 100);
        $logoDark  = UploadedFile::fake()->image('logo_dark.png', 400, 100);
        $favicon   = UploadedFile::fake()->image('favicon.png', 64, 64);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.settings.update'), [
            'active_tab'           => 'branding',
            'site_logo_light_file' => $logoLight,
            'site_logo_dark_file'  => $logoDark,
            'site_favicon_file'    => $favicon,
            'site_tagline'         => 'The Ultimate UMKM Operating System',
        ]);

        $response->assertRedirect(route('admin.settings.index', ['tab' => 'branding']));

        $savedLight = SystemSetting::get('site_logo_light');
        $savedDark  = SystemSetting::get('site_logo_dark');
        $savedFav   = SystemSetting::get('site_favicon');

        $this->assertNotEmpty($savedLight);
        $this->assertNotEmpty($savedDark);
        $this->assertNotEmpty($savedFav);
        $this->assertSame('The Ultimate UMKM Operating System', SystemSetting::get('site_tagline'));

        Storage::disk('public')->assertExists($savedLight);
        Storage::disk('public')->assertExists($savedDark);
        Storage::disk('public')->assertExists($savedFav);

        // Test Reset to Default
        $resetResponse = $this->actingAs($admin, 'admin')->post(route('admin.settings.update'), [
            'active_tab'        => 'branding',
            'reset_logo_light'  => '1',
            'reset_logo_dark'   => '1',
            'reset_favicon'     => '1',
        ]);

        $resetResponse->assertRedirect(route('admin.settings.index', ['tab' => 'branding']));
        $this->assertSame('', SystemSetting::get('site_logo_light'));
        $this->assertSame('', SystemSetting::get('site_logo_dark'));
        $this->assertSame('', SystemSetting::get('site_favicon'));
    }

    public function test_superadmin_can_configure_official_social_media_channels(): void
    {
        $admin = $this->makeAdmin();

        $payload = [
            'active_tab'              => 'social_links',
            'social_instagram_url'    => 'https://instagram.com/cooca.official',
            'social_instagram_handle' => '@cooca.official',
            'social_instagram_active' => '1',
            'social_facebook_url'     => 'https://facebook.com/cooca.official',
            'social_facebook_name'    => 'Cooca Official Facebook',
            'social_facebook_active'  => '1',
            'social_tiktok_url'       => 'https://tiktok.com/@cooca.official',
            'social_tiktok_handle'    => '@cooca.official',
            'social_tiktok_active'    => '1',
            'social_youtube_url'      => 'https://youtube.com/@cooca_official',
            'social_youtube_name'     => 'Cooca Official Channel',
            'social_youtube_active'   => '1',
            'social_twitter_url'      => 'https://x.com/cooca_official',
            'social_twitter_handle'   => '@cooca_official',
            'social_twitter_active'   => '0',
            'social_linkedin_url'     => 'https://linkedin.com/company/cooca-official',
            'social_linkedin_name'    => 'Cooca Official PT',
            'social_linkedin_active'  => '1',
            'social_whatsapp_url'     => 'https://wa.me/6281234567890',
            'social_whatsapp_number'  => '0812 3456 7890',
            'social_whatsapp_active'  => '1',
            'social_telegram_url'     => 'https://t.me/cooca_official',
            'social_telegram_name'    => 'Komunitas Cooca Official',
            'social_telegram_active'  => '1',
        ];

        $response = $this->actingAs($admin, 'admin')->post(route('admin.settings.update'), $payload);

        $response->assertRedirect(route('admin.settings.index', ['tab' => 'social_links']));

        $this->assertSame('https://instagram.com/cooca.official', SystemSetting::get('social_instagram_url'));
        $this->assertSame('@cooca.official', SystemSetting::get('social_instagram_handle'));
        $this->assertSame('1', SystemSetting::get('social_instagram_active'));

        $this->assertSame('0', SystemSetting::get('social_twitter_active'));
        $this->assertSame('https://wa.me/6281234567890', SystemSetting::get('social_whatsapp_url'));
        $this->assertSame('0812 3456 7890', SystemSetting::get('social_whatsapp_number'));
        $this->assertSame('Komunitas Cooca Official', SystemSetting::get('social_telegram_name'));
    }

    public function test_superadmin_can_save_complete_seo_cms_and_render_in_public_layout(): void
    {
        Storage::fake('public');
        $admin = $this->makeAdmin();

        $ogImage = UploadedFile::fake()->image('og_share.png', 1200, 630);

        $seoData = [
            'active_tab'              => 'seo',
            'seo_meta_title'          => 'Aplikasi Kasir POS & ERP UMKM Indonesia Terbaik',
            'seo_meta_description'    => 'Tingkatkan profit usaha dengan software kasir POS gratis dan pembukuan real-time.',
            'seo_meta_keywords'       => 'aplikasi kasir, software pos, software pembukuan',
            'seo_author'              => 'PT Cooca Digital Indonesia',
            'seo_robots'              => 'index, follow',
            'seo_canonical_url'       => 'https://cooca.id',
            'seo_og_title'            => 'Cooca POS & ERP UMKM #1',
            'seo_og_description'      => 'Solusi lengkap manajemen warung, toko, kafe, dan laundry.',
            'seo_og_image_file'       => $ogImage,
            'seo_twitter_card'        => 'summary_large_image',
            'seo_twitter_site'        => '@cooca_official',
            'seo_google_verification' => 'google-test-verification-code-xyz',
            'seo_bing_verification'   => 'bing-test-verification-code-123',
            'seo_google_analytics_id' => 'G-TESTGA4999',
            'seo_custom_head_scripts' => '<meta name="custom-test-tag" content="verified">',
        ];

        $response = $this->actingAs($admin, 'admin')->post(route('admin.settings.update'), $seoData);

        $response->assertRedirect(route('admin.settings.index', ['tab' => 'seo']));

        $this->assertSame('Aplikasi Kasir POS & ERP UMKM Indonesia Terbaik', SystemSetting::get('seo_meta_title'));
        $this->assertSame('PT Cooca Digital Indonesia', SystemSetting::get('seo_author'));
        $this->assertSame('google-test-verification-code-xyz', SystemSetting::get('seo_google_verification'));
        $this->assertSame('G-TESTGA4999', SystemSetting::get('seo_google_analytics_id'));

        $savedOg = SystemSetting::get('seo_og_image');
        $this->assertNotEmpty($savedOg);
        Storage::disk('public')->assertExists($savedOg);

        // Verify that public marketing landing page reflects these SEO tags
        $publicResponse = $this->get(route('landing'));
        $publicResponse->assertOk();
        $publicResponse->assertSee('Aplikasi Kasir POS & ERP UMKM Indonesia Terbaik');
        $publicResponse->assertSee('google-test-verification-code-xyz', false);
        $publicResponse->assertSee('bing-test-verification-code-123', false);
        $publicResponse->assertSee('G-TESTGA4999', false);
        $publicResponse->assertSee('<meta name="custom-test-tag" content="verified">', false);
    }
}
