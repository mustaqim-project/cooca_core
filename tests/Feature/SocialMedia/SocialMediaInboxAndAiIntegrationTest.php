<?php

declare(strict_types=1);

namespace Tests\Feature\SocialMedia;

use App\Domain\Billing\EntitlementService;
use App\Models\AiProviderConfig;
use App\Models\Business;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaComment;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

final class SocialMediaInboxAndAiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->owner = User::create([
            'name'     => 'Merchant Owner',
            'email'    => 'merchant_owner@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->business = Business::create([
            'name' => 'Toko Busana Cantik',
            'slug' => 'toko-busana-cantik',
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id'        => Str::uuid(),
            'role'      => 'owner',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);

        // Aktifkan paket berbayar agar hak akses modul dan channel terbuka
        app(EntitlementService::class)->upgradeToCore($this->business, 'monthly');
    }

    public function test_inbox_view_displays_warning_banner_when_ai_is_not_configured(): void
    {
        // Pastikan belum ada AiProviderConfig
        $this->assertDatabaseEmpty('ai_provider_configs');

        $response = $this->actingAs($this->owner)
            ->get(route('social-media.inbox.index'));

        $response->assertStatus(200);
        $response->assertSee('AI Belum Dikonfigurasi');
        $response->assertSee('Asisten &amp; Balasan AI Inbox Belum Dapat Digunakan', false);
        $response->assertSee('Setting AI di Cooca AI');
        $response->assertSee(route('cooca-ai.providers'));
        $response->assertSee('Sinkronkan Data Meta');
    }

    public function test_inbox_view_displays_connected_badge_when_ai_is_configured(): void
    {
        // Buat AiProviderConfig aktif
        AiProviderConfig::create([
            'business_id' => $this->business->id,
            'provider'    => 'gemini',
            'api_key'     => 'mock-gemini-key',
            'model'       => 'gemini-2.5-flash',
            'is_active'   => true,
            'is_default'  => true,
            'status'      => 'connected',
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('social-media.inbox.index'));

        $response->assertStatus(200);
        $response->assertSee('Cooca AI Connected');
        $response->assertSee('GEMINI');
        $response->assertDontSee('Asisten &amp; Balasan AI Inbox Belum Dapat Digunakan', false);
    }

    public function test_generate_ai_reply_is_rejected_when_ai_is_not_configured(): void
    {
        // Pastikan tidak ada konfigurasi AI
        $this->assertDatabaseEmpty('ai_provider_configs');

        $response = $this->actingAs($this->owner)
            ->postJson(route('social-media.inbox.ai-reply'), [
                'message'       => 'Berapa harga baju gamis?',
                'channel'       => 'whatsapp',
                'customer_name' => 'Siti',
            ]);

        $response->assertStatus(422);
        $this->assertFalse($response->json('success'));
        $this->assertTrue($response->json('needs_config'));
        $this->assertStringContainsString('Fitur AI belum dapat digunakan', $response->json('error'));
        $this->assertSame(route('cooca-ai.providers'), $response->json('redirect_url'));
    }

    public function test_generate_ai_reply_succeeds_when_ai_is_configured(): void
    {
        AiProviderConfig::create([
            'business_id' => $this->business->id,
            'provider'    => 'gemini',
            'api_key'     => 'test-valid-key',
            'model'       => 'gemini-2.5-flash',
            'is_active'   => true,
            'is_default'  => true,
            'status'      => 'connected',
        ]);

        $unit = \App\Models\Unit::create([
            'business_id' => $this->business->id,
            'name'        => 'Pieces',
            'code'        => 'pcs',
            'category'    => 'quantity',
        ]);

        // Buat produk di database toko untuk grounding
        \App\Models\Product::create([
            'business_id'    => $this->business->id,
            'name'           => 'Gamis Syari Elegan',
            'selling_price'  => 175000,
            'stock_quantity' => 10,
            'output_unit_id' => $unit->id,
            'is_active'      => true,
        ]);

        $response = $this->actingAs($this->owner)
            ->postJson(route('social-media.inbox.ai-reply'), [
                'message'       => 'Halo, apakah Gamis Syari Elegan ready?',
                'channel'       => 'whatsapp',
                'customer_name' => 'Siti',
            ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertNotEmpty($response->json('reply'));
        $this->assertStringContainsString('Gamis Syari Elegan', $response->json('reply'));
    }

    public function test_sync_meta_inbox_reports_no_accounts_when_none_connected(): void
    {
        $response = $this->actingAs($this->owner)
            ->postJson(route('social-media.inbox.sync-meta'));

        $response->assertStatus(200);
        $this->assertFalse($response->json('success'));
        $this->assertSame(0, $response->json('accounts_count'));
        $this->assertStringContainsString('Belum ada akun Facebook Page atau Instagram yang terhubung', $response->json('message'));
    }

    public function test_sync_meta_inbox_fetches_and_persists_messages_from_meta(): void
    {
        // Hubungkan akun Facebook Page
        $fbAccount = SocialMediaAccount::create([
            'business_id'         => $this->business->id,
            'platform'            => 'facebook',
            'account_id'          => 'page_123456789',
            'account_name'        => 'Official Facebook Page',
            'access_token'        => 'page_token_secret_xyz',
            'token_type'          => 'page_token',
            'status'              => 'active',
        ]);

        // Mock respons Meta Graph API untuk percakapan Facebook
        Http::fake([
            'https://graph.facebook.com/*/page_123456789/conversations*' => Http::response([
                'data' => [
                    [
                        'id' => 'conv_999',
                        'updated_time' => '2026-10-08T04:00:00+0000',
                        'snippet' => 'Halo min, produk dress masih ada?',
                        'messages' => [
                            'data' => [
                                [
                                    'id' => 'msg_meta_001',
                                    'message' => 'Halo min, produk dress masih ada?',
                                    'created_time' => '2026-10-08T04:00:00+0000',
                                    'from' => [
                                        'id' => 'user_cust_888',
                                        'name' => 'Dewi Lestari',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
            'https://graph.facebook.com/*/page_123456789/feed*' => Http::response([
                'data' => [
                    [
                        'id' => 'post_777',
                        'message' => 'Koleksi baru telah hadir!',
                        'comments' => [
                            'data' => [
                                [
                                    'id' => 'comment_meta_002',
                                    'message' => 'Bisa COD ke Bandung kah?',
                                    'created_time' => '2026-10-08T04:05:00+0000',
                                    'from' => [
                                        'id' => 'user_cust_777',
                                        'name' => 'Rina Melati',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->owner)
            ->postJson(route('social-media.inbox.sync-meta'));

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertSame(1, $response->json('accounts_count'));
        $this->assertSame(2, $response->json('synced_count'));

        // Verifikasi tersimpan di tabel SocialMediaComment
        $this->assertDatabaseHas('social_media_comments', [
            'business_id'         => $this->business->id,
            'platform'            => 'messenger',
            'platform_comment_id' => 'msg_meta_001',
            'from_name'           => 'Dewi Lestari',
            'message'             => 'Halo min, produk dress masih ada?',
        ]);

        $this->assertDatabaseHas('social_media_comments', [
            'business_id'         => $this->business->id,
            'platform'            => 'facebook',
            'platform_comment_id' => 'comment_meta_002',
            'from_name'           => 'Rina Melati',
            'message'             => 'Bisa COD ke Bandung kah?',
        ]);
    }

    public function test_inbox_does_not_contain_hardcoded_dummy_data_when_empty(): void
    {
        $response = $this->actingAs($this->owner)
            ->get(route('social-media.inbox.index'));

        $response->assertStatus(200);

        // Pastikan TIDAK ADA data hardcore / dummy / demo
        $response->assertDontSee('Agung Mustaqim');
        $response->assertDontSee('alskhdljahsdljk');
        $response->assertDontSee('Rian Pratama');
        $response->assertDontSee('Siti Rahma');
        $response->assertDontSee('6285287864176');

        // Pastikan empty state profesional tampil
        $response->assertSee('Ruang Obrolan Omnichannel');
    }

    public function test_send_reply_persists_outgoing_message_and_updates_status(): void
    {
        $comment = SocialMediaComment::create([
            'business_id'         => $this->business->id,
            'platform'            => 'facebook',
            'platform_post_id'    => 'post_meta_777',
            'platform_comment_id' => 'cust_comm_111',
            'from_id'             => 'cust_999',
            'from_name'           => 'Budi Santoso',
            'message'             => 'Apakah barang ready?',
            'is_from_page'        => false,
            'status'              => 'unread',
            'created_time'        => now(),
        ]);

        $response = $this->actingAs($this->owner)
            ->postJson(route('social-media.inbox.send-reply'), [
                'channel'         => 'facebook_comments',
                'message'         => 'Halo Budi, barang ready dan siap kirim!',
                'comment_id'      => $comment->id,
                'conversation_id' => 'comm_' . $comment->id,
            ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));

        // Verifikasi balasan tersimpan di database dengan is_from_page = true
        $this->assertDatabaseHas('social_media_comments', [
            'business_id'       => $this->business->id,
            'parent_comment_id' => $comment->platform_comment_id,
            'is_from_page'      => true,
            'message'           => 'Halo Budi, barang ready dan siap kirim!',
            'status'            => 'replied',
        ]);

        // Verifikasi status komentar parent di-update menjadi replied
        $this->assertSame('replied', $comment->fresh()->status);
    }
}
