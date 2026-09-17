<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Domain\WhatsApp\AdminWhatsAppService;
use App\Models\Admin;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\User;
use App\Models\WhatsAppAdminBlast;
use App\Models\WhatsAppSubscriptionReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminWhatsAppFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(array $override = []): Admin
    {
        return Admin::factory()->create(array_merge([
            'name' => 'Super Administrator',
            'email' => 'superadmin@cooca.id',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'is_active' => true,
        ], $override));
    }

    private function makeTenant(): array
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'phone' => '081298765432',
        ]);

        $business = Business::create([
            'user_id' => $user->id,
            'name' => 'Kopi Sejahtera UMKM',
            'status' => 'active',
            'currency' => 'IDR',
        ]);

        return [$user, $business];
    }

    public function test_unauthenticated_user_cannot_access_admin_whatsapp(): void
    {
        $response = $this->get(route('admin.whatsapp.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_whatsapp_index_with_bento_tiles(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.whatsapp.index'));

        $response->assertOk();
        $response->assertSee('WhatsApp Platform Admin Center');
        $response->assertSee('Bot Platform');
        $response->assertSee('Merchant WABA');
        $response->assertSee('Pengingat Tagihan');
        $response->assertSee('Keberhasilan Kirim');
        $response->assertSee('Pengaturan Platform Meta');
        $response->assertSee('Monitoring Merchant');
        $response->assertSee('Pengingat Langganan');
        $response->assertSee('Siaran Platform');
        $response->assertSee('Template Notifikasi');
    }

    public function test_admin_can_navigate_tabs_via_query_param(): void
    {
        $admin = $this->makeAdmin();

        foreach (['parent_setup', 'merchants', 'reminders', 'blast', 'templates'] as $tab) {
            $response = $this->actingAs($admin, 'admin')->get(route('admin.whatsapp.index', ['tab' => $tab]));
            $response->assertOk();
        }
    }

    public function test_admin_can_check_status_via_ajax(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->getJson(route('admin.whatsapp.status'));

        $response->assertOk();
        $response->assertJsonStructure(['status']);
    }

    public function test_admin_can_get_qr_via_ajax(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->getJson(route('admin.whatsapp.qr'));

        $response->assertOk();
    }

    public function test_admin_test_send_validates_input(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->postJson(route('admin.whatsapp.test'), [
                'phone' => '',
                'message' => '',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['phone', 'message']);
    }

    public function test_admin_can_update_reminder_templates(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->from(route('admin.whatsapp.index', ['tab' => 'templates']))
            ->post(route('admin.whatsapp.reminders.templates'), [
                'template_h7' => 'Halo {owner}, paket {paket} bisnis {bisnis} akan berakhir dalam 7 hari.',
                'template_h3' => 'Halo {owner}, paket {paket} akan berakhir 3 hari lagi. Bayar di {link_bayar}',
                'template_h1' => 'Peringatan: Besok paket {paket} akan berakhir!',
                'template_h0' => 'Hari ini paket {paket} Anda telah jatuh tempo.',
            ]);

        $response->assertRedirect(route('admin.whatsapp.index', ['tab' => 'templates']));
        $response->assertSessionHas('success');
    }

    public function test_admin_can_store_blast_and_view_detail(): void
    {
        $admin = $this->makeAdmin();
        [$user, $business] = $this->makeTenant();

        $this->mock(AdminWhatsAppService::class, function ($mock) {
            $mock->shouldReceive('sendAdminBlast')->once()->andReturnUsing(function ($blast) {
                $blast->update([
                    'total_recipients' => 1,
                    'total_sent' => 1,
                    'total_failed' => 0,
                    'status' => 'completed',
                ]);
            });
        });

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.whatsapp.blasts.store'), [
                'title' => 'Pengumuman Update Cooca v2.5',
                'target_filter' => 'all_owners',
                'message' => 'Halo {owner}, Cooca kini hadir dengan antarmuka Apple HIG Bento Grid!',
                'media_url' => 'https://example.com/banner.jpg',
            ]);

        $blast = WhatsAppAdminBlast::where('title', 'Pengumuman Update Cooca v2.5')->first();
        $this->assertNotNull($blast);

        $response->assertRedirect(route('admin.whatsapp.blasts.show', $blast));

        // Test viewing blast detail page
        $showResponse = $this->actingAs($admin, 'admin')->get(route('admin.whatsapp.blasts.show', $blast));
        $showResponse->assertOk();
        $showResponse->assertSee('Detail Broadcast');
        $showResponse->assertSee('Pengumuman Update Cooca v2.5');
        $showResponse->assertSee('Tampilan Pesan WhatsApp (Chat Mockup)');
        $showResponse->assertSee('Daftar Status Pengiriman Per Bisnis Owner');
    }

    public function test_admin_whatsapp_page_displays_tech_provider_fields(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.whatsapp.index', ['tab' => 'parent_setup']));

        $response->assertOk();
        $response->assertSee('Meta App ID (META_WA_APP_ID)');
        $response->assertSee('Meta App Secret (META_WA_APP_SECRET)');
        $response->assertSee('Embedded Signup Config ID (META_WA_CONFIG_ID)');
        $response->assertSee('Webhook Verify Token (META_WA_WEBHOOK_VERIFY_TOKEN)');
        $response->assertSee('Graph API Version (META_WA_GRAPH_VERSION)');
        $response->assertSee('Graph API Base URL (META_WA_GRAPH_URL)');
        $response->assertSee('Webhook Callback URL (Meta Webhook Endpoint)');
        $response->assertSee('api/v1/wa/meta/webhook');
        $response->assertSee('Meta System User Permanent Access Token (META_WA_TOKEN)');
    }

    public function test_admin_can_save_and_retrieve_meta_platform_and_gateway_settings(): void
    {
        $admin = $this->makeAdmin();

        $payload = [
            'otp_active'                => '1',
            'blast_active'              => '1',
            'meta_app_id'               => '109876543210987',
            'meta_app_secret'           => 'secret_xyz_1234567890abcdef',
            'meta_webhook_verify_token' => 'custom_webhook_secret_token_99',
            'meta_config_id'            => '876543210987654',
            'meta_graph_version'        => 'v21.0',
            'meta_graph_url'            => 'https://graph.facebook.com',
            'meta_token'                => 'EAAG_dummy_test_platform_token_123',
            'meta_phone_number_id'      => '104523984712398',
            'meta_waba_id'              => '109283746501928',
            'meta_otp_template'         => 'cooca_otp',
        ];

        $response = $this->actingAs($admin, 'admin')
            ->from(route('admin.whatsapp.index', ['tab' => 'parent_setup']))
            ->post(route('admin.whatsapp.config'), $payload);

        $response->assertRedirect(route('admin.whatsapp.index', ['tab' => 'parent_setup']));
        $response->assertSessionHas('success');

        // Verify stored settings in database
        $this->assertEquals('109876543210987', \App\Models\SystemSetting::get('meta_wa_app_id'));
        $this->assertEquals('secret_xyz_1234567890abcdef', \App\Models\SystemSetting::get('meta_wa_app_secret'));
        $this->assertEquals('custom_webhook_secret_token_99', \App\Models\SystemSetting::get('meta_wa_webhook_verify_token'));
        $this->assertEquals('876543210987654', \App\Models\SystemSetting::get('meta_wa_config_id'));
        $this->assertEquals('v21.0', \App\Models\SystemSetting::get('meta_wa_graph_version'));
        $this->assertEquals('https://graph.facebook.com', \App\Models\SystemSetting::get('meta_wa_graph_url'));
        $this->assertEquals('EAAG_dummy_test_platform_token_123', \App\Models\SystemSetting::get('meta_wa_token'));
        $this->assertEquals('104523984712398', \App\Models\SystemSetting::get('meta_wa_phone_number_id'));
        $this->assertEquals('109283746501928', \App\Models\SystemSetting::get('meta_wa_waba_id'));
        $this->assertEquals('cooca_otp', \App\Models\SystemSetting::get('meta_wa_otp_template'));

        // Verify page now reflects the saved settings
        $viewResponse = $this->actingAs($admin, 'admin')->get(route('admin.whatsapp.index', ['tab' => 'parent_setup']));
        $viewResponse->assertOk();
        $viewResponse->assertSee('109876543210987');
        $viewResponse->assertSee('custom_webhook_secret_token_99');
        $viewResponse->assertSee('876543210987654');
    }
}
