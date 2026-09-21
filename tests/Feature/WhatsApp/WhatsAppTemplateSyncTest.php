<?php

declare(strict_types=1);

namespace Tests\Feature\WhatsApp;

use App\Models\Admin;
use App\Models\SystemSetting;
use App\Models\WhatsAppMessageTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppTemplateSyncTest extends TestCase
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

    private function setupMetaCredentials(): void
    {
        SystemSetting::set('meta_wa_token', 'EAAG_test_system_user_token_v26', 'whatsapp', true);
        SystemSetting::set('meta_wa_phone_number_id', '10987654321', 'whatsapp');
        SystemSetting::set('meta_wa_waba_id', '1546059137323420', 'whatsapp');
        SystemSetting::set('meta_wa_graph_version', 'v26.0', 'whatsapp');
    }

    public function test_admin_can_view_whatsapp_templates_tab_with_meta_catalog(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.whatsapp.index', ['tab' => 'templates']));

        $response->assertOk();
        $response->assertViewHas('metaTemplates');
        $response->assertSee('Katalog Template WhatsApp Business');
        $response->assertSee('Meta Graph API v26.0');
    }

    public function test_admin_can_sync_templates_from_meta_graph_api_v26(): void
    {
        $admin = $this->makeAdmin();
        $this->setupMetaCredentials();

        Http::fake([
            'https://graph.facebook.com/v26.0/1546059137323420/message_templates*' => Http::response([
                'data' => [
                    [
                        'id'         => 'meta_tpl_001',
                        'name'       => 'order_confirmation_v1',
                        'category'   => 'UTILITY',
                        'language'   => 'id',
                        'status'     => 'APPROVED',
                        'components' => [
                            [
                                'type' => 'HEADER',
                                'format' => 'TEXT',
                                'text' => 'Konfirmasi Pesanan',
                            ],
                            [
                                'type' => 'BODY',
                                'text' => 'Halo {{1}}, pesanan #{{2}} Anda telah berhasil dibuat.',
                            ],
                            [
                                'type' => 'FOOTER',
                                'text' => 'Terima kasih telah berbelanja di Cooca.',
                            ],
                        ],
                    ],
                    [
                        'id'         => 'meta_tpl_002',
                        'name'       => 'flash_sale_promo',
                        'category'   => 'MARKETING',
                        'language'   => 'id',
                        'status'     => 'PENDING',
                        'components' => [
                            [
                                'type' => 'BODY',
                                'text' => 'Dapatkan diskon hingga {{1}}% untuk semua produk!',
                            ],
                        ],
                    ],
                ],
                'paging' => [
                    'cursors' => [
                        'before' => 'QVFIUk9x...',
                        'after'  => 'QVFIUk9x...',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($admin, 'admin')->postJson(route('admin.whatsapp.meta-templates.sync'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'count'   => 2,
        ]);

        $this->assertDatabaseHas('whatsapp_message_templates', [
            'waba_id'          => '1546059137323420',
            'meta_template_id' => 'meta_tpl_001',
            'name'             => 'order_confirmation_v1',
            'category'         => 'UTILITY',
            'status'           => 'APPROVED',
        ]);

        $this->assertDatabaseHas('whatsapp_message_templates', [
            'waba_id'          => '1546059137323420',
            'meta_template_id' => 'meta_tpl_002',
            'name'             => 'flash_sale_promo',
            'category'         => 'MARKETING',
            'status'           => 'PENDING',
        ]);
    }

    public function test_admin_can_create_new_template_and_submit_to_meta_v26(): void
    {
        $admin = $this->makeAdmin();
        $this->setupMetaCredentials();

        Http::fake([
            'https://graph.facebook.com/v26.0/1546059137323420/message_templates' => Http::response([
                'id'       => 'meta_new_tpl_789',
                'status'   => 'PENDING',
                'category' => 'UTILITY',
            ], 200),
        ]);

        $payload = [
            'name'        => 'reminder_tagihan_pelanggan',
            'category'    => 'UTILITY',
            'language'    => 'id',
            'header_text' => 'Pemberitahuan Tagihan',
            'body_text'   => 'Halo {{1}}, tagihan paket {{2}} sebesar Rp {{3}} akan jatuh tempo besok.',
            'footer_text' => 'Balas STOP jika tidak ingin menerima pesan ini',
        ];

        $response = $this->actingAs($admin, 'admin')->postJson(route('admin.whatsapp.meta-templates.create'), $payload);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('whatsapp_message_templates', [
            'waba_id'          => '1546059137323420',
            'meta_template_id' => 'meta_new_tpl_789',
            'name'             => 'reminder_tagihan_pelanggan',
            'category'         => 'UTILITY',
            'language'         => 'id',
            'status'           => 'PENDING',
        ]);
    }

    public function test_create_template_validates_required_fields_and_format(): void
    {
        $admin = $this->makeAdmin();
        $this->setupMetaCredentials();

        $response = $this->actingAs($admin, 'admin')->postJson(route('admin.whatsapp.meta-templates.create'), [
            'name'      => 'INVALID NAME WITH UPPERCASE AND SPACES',
            'category'  => 'UNKNOWN_CAT',
            'body_text' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'category', 'body_text']);
    }

    public function test_admin_can_delete_template_from_meta_and_local_database(): void
    {
        $admin = $this->makeAdmin();
        $this->setupMetaCredentials();

        $template = WhatsAppMessageTemplate::create([
            'waba_id'          => '1546059137323420',
            'meta_template_id' => 'meta_tpl_to_delete',
            'name'             => 'old_deprecated_template',
            'category'         => 'UTILITY',
            'language'         => 'id',
            'status'           => 'APPROVED',
            'components'       => [
                ['type' => 'BODY', 'text' => 'Pesan lama'],
            ],
            'synced_at'        => now(),
        ]);

        Http::fake([
            'https://graph.facebook.com/v26.0/1546059137323420/message_templates*' => Http::response([
                'success' => true,
            ], 200),
        ]);

        $response = $this->actingAs($admin, 'admin')->deleteJson(route('admin.whatsapp.meta-templates.delete', $template->id));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseMissing('whatsapp_message_templates', [
            'id' => $template->id,
        ]);
    }
}
