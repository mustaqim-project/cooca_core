<?php

declare(strict_types=1);

namespace Tests\Feature\WhatsApp;

use App\Domain\WhatsApp\WhatsAppGatewayService;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\User;
use App\Models\WhatsAppAccount;
use App\Models\WhatsAppBroadcastCampaign;
use App\Models\WhatsAppMessageLog;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Tests\TestCase;

class MerchantWhatsAppWebFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RbacSeeder::class);

        Config::set('services.meta_whatsapp.app_id', '123456789012345');
        Config::set('services.meta_whatsapp.app_secret', 'test_meta_app_secret_12345');
        Config::set('services.meta_whatsapp.config_id', 'test_embedded_config_id_99');
        Config::set('services.meta_whatsapp.webhook_verify_token', 'test_verify_token_secure');

        app()->setLocale('id');
    }

    private function createMerchant(?string $email = null): array
    {
        $user = User::factory()->create([
            'name'  => 'Pemilik Toko',
            'email' => $email ?? ('toko_' . Str::random(8) . '@cooca.id'),
        ]);

        $business = Business::create([
            'user_id'  => $user->id,
            'name'     => 'Kopi Kenangan Sejahtera',
            'status'   => 'active',
            'currency' => 'IDR',
        ]);

        $business->users()->attach($user->id, [
            'id'   => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $user->update(['active_business_id' => $business->id]);
        Context::setBusiness($business);

        return [$user, $business];
    }

    public function test_unauthenticated_user_cannot_access_whatsapp_dashboard(): void
    {
        $response = $this->get(route('whatsapp.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_merchant_can_view_whatsapp_dashboard_when_disconnected(): void
    {
        [$user, $business] = $this->createMerchant();

        app()->setLocale('id');
        $response = $this->actingAs($user)->get(route('whatsapp.index'));

        $response->assertOk();
        $response->assertSee('WhatsApp Gateway &amp; Otomasi Bisnis', false);
        $response->assertSee('Meta WhatsApp Cloud API Resmi');
        $response->assertSee('Pendaftaran Mandiri (Embedded Signup)');
        $response->assertSee('WhatsApp Gateway');
        $response->assertSee('Siaran Pesan (Broadcast)');
        $response->assertSee('Log Pesan');

        // Verify English locale rendering
        app()->setLocale('en');
        $responseEn = $this->actingAs($user)->get(route('whatsapp.index'));
        $responseEn->assertOk();
        $responseEn->assertSee('WhatsApp Gateway &amp; Business Automation', false);
        $responseEn->assertSee('Official Meta WhatsApp Cloud API');
        $responseEn->assertSee('Self-Service Embedded Signup');
    }

    public function test_merchant_can_view_whatsapp_dashboard_when_connected(): void
    {
        [$user, $business] = $this->createMerchant();

        WhatsAppAccount::create([
            'business_id'          => $business->id,
            'waba_id'              => '109876543210',
            'phone_number_id'      => '100012345678',
            'phone_number'         => '6281234567890',
            'display_phone_number' => '+62 852-8786-4176',
            'verified_name'        => 'Kopi Kenangan Sejahtera Official',
            'quality_rating'       => 'GREEN',
            'messaging_limit_tier' => 'TIER_1K',
            'access_token'         => 'EAABwzLixnjYBA_test_token',
            'status'               => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('whatsapp.index'));

        $response->assertOk();
        $response->assertSee('Kopi Kenangan Sejahtera Official');
        $response->assertSee('+62 852-8786-4176');
        $response->assertSee('TIER_1K');
        $response->assertSee(__('whatsapp.disconnect_btn'));
    }

    public function test_merchant_can_view_whatsapp_logs_page(): void
    {
        [$user, $business] = $this->createMerchant();

        WhatsAppMessageLog::create([
            'business_id'     => $business->id,
            'type'            => 'receipt',
            'recipient_name'  => 'Budi Santoso',
            'recipient_phone' => '081234567890',
            'message'         => 'Terima kasih telah berbelanja di Kopi Kenangan Sejahtera.',
            'status'          => 'sent',
        ]);

        // 1. Indonesian View
        app()->setLocale('id');
        $response = $this->actingAs($user)->get(route('whatsapp.logs.index'));

        $response->assertOk();
        $response->assertSee('Riwayat Komunikasi Keluar');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Struk POS');
        $response->assertSee('Terkirim');
        $response->assertSee('Log Komunikasi WhatsApp');

        // 2. English View
        app()->setLocale('en');
        $responseEn = $this->actingAs($user)->get(route('whatsapp.logs.index'));

        $responseEn->assertOk();
        $responseEn->assertSee('Outgoing Communication History');
        $responseEn->assertSee('Budi Santoso');
        $responseEn->assertSee('POS Receipt');
        $responseEn->assertSee('Delivered');
        $responseEn->assertSee('WhatsApp Communication Logs');

        app()->setLocale('id');
    }

    public function test_merchant_can_view_broadcast_index_and_create(): void
    {
        [$user, $business] = $this->createMerchant();

        WhatsAppBroadcastCampaign::create([
            'business_id'      => $business->id,
            'title'            => 'Promo Diskon Kopi 50%',
            'message'          => 'Halo pelanggan setia, dapatkan diskon 50% hari ini!',
            'target_filter'    => 'all',
            'total_recipients' => 10,
            'total_sent'       => 10,
            'total_failed'     => 0,
            'status'           => 'completed',
        ]);

        $indexResponse = $this->actingAs($user)->get(route('whatsapp.broadcast.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Promo Diskon Kopi 50%');
        $indexResponse->assertSee('Buat Blast Promosi Baru');
        $indexResponse->assertSee('createModalOpen: false', false);

        // Akses route create dialihkan anggun ke index dengan parameter open_composer=1
        $createResponse = $this->actingAs($user)->get(route('whatsapp.broadcast.create'));
        $createResponse->assertRedirect(route('whatsapp.broadcast.index', ['open_composer' => 1]));

        // Akses langsung index dengan query open_composer=1 membuka Modal Sheet otomatis
        $composerResponse = $this->actingAs($user)->get(route('whatsapp.broadcast.index', ['open_composer' => 1]));
        $composerResponse->assertOk();
        $composerResponse->assertSee('createModalOpen: true', false);
        $composerResponse->assertSee('Buat Kampanye Blast Promosi');

        // Verify English locale rendering
        app()->setLocale('en');
        $indexResponseEn = $this->actingAs($user)->get(route('whatsapp.broadcast.index'));
        $indexResponseEn->assertOk();
        $indexResponseEn->assertSee('Promo Diskon Kopi 50%');
        $indexResponseEn->assertSee('Create New Broadcast');
        $indexResponseEn->assertSee('Broadcast Campaign History');
        app()->setLocale('id');
    }

    public function test_merchant_can_fetch_meta_onboarding_config(): void
    {
        [$user, $business] = $this->createMerchant();

        $response = $this->actingAs($user)->getJson(route('whatsapp.meta.config'));

        $response->assertOk();
        $response->assertJson([
            'app_id'    => '123456789012345',
            'config_id' => 'test_embedded_config_id_99',
            'version'   => 'v26.0',
        ]);
    }

    public function test_merchant_can_disconnect_meta_whatsapp_account(): void
    {
        [$user, $business] = $this->createMerchant();

        $account = WhatsAppAccount::create([
            'business_id'     => $business->id,
            'waba_id'         => '109876543210',
            'phone_number_id' => '100012345678',
            'phone_number'    => '6281234567890',
            'access_token'    => 'token_to_disconnect',
            'status'          => 'active',
        ]);

        $response = $this->actingAs($user)->postJson(route('whatsapp.meta.disconnect'));

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $account->refresh();
        $this->assertEquals('disconnected', $account->status);
    }

    public function test_merchant_cannot_view_another_business_broadcast_campaign(): void
    {
        [$userA, $businessA] = $this->createMerchant();

        // Create business B and user B
        $userB = User::factory()->create(['name' => 'Competitor', 'email' => 'other@cooca.id']);
        $businessB = Business::create([
            'user_id'  => $userB->id,
            'name'     => 'Competitor Cafe',
            'status'   => 'active',
            'currency' => 'IDR',
        ]);
        $businessB->users()->attach($userB->id, [
            'id'   => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $campaignB = WhatsAppBroadcastCampaign::create([
            'business_id'      => $businessB->id,
            'title'            => 'Secret Promo Business B',
            'message'          => 'Confidential message',
            'target_filter'    => 'vip',
            'total_recipients' => 5,
            'total_sent'       => 5,
            'status'           => 'completed',
        ]);

        // User A tries to view Campaign B
        $response = $this->actingAs($userA)->get(route('whatsapp.broadcast.show', $campaignB));

        // Must return 404 (IDOR shield)
        $response->assertNotFound();
    }

    public function test_merchant_can_store_broadcast_and_dispatches_async_job(): void
    {
        \Illuminate\Support\Facades\Queue::fake();

        [$user, $business] = $this->createMerchant();

        // Connect WhatsApp Account first
        WhatsAppAccount::create([
            'business_id'          => $business->id,
            'waba_id'              => '109876543210',
            'phone_number_id'      => '100012345678',
            'phone_number'         => '6281234567890',
            'display_phone_number' => '+62 852-8786-4176',
            'access_token'         => 'EAABwzLixnjYBA_test_token',
            'status'               => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('whatsapp.broadcast.store'), [
            'title'         => 'Flash Sale Kopi Susu',
            'message'       => 'Halo {nama}, nikmati diskon khusus hari ini di {bisnis}!',
            'target_filter' => 'all',
        ]);

        $campaign = WhatsAppBroadcastCampaign::where('business_id', $business->id)->first();
        $this->assertNotNull($campaign);
        $this->assertSame('Flash Sale Kopi Susu', $campaign->title);
        $this->assertSame('processing', $campaign->status);

        $response->assertRedirect(route('whatsapp.broadcast.show', $campaign));

        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\WhatsApp\SendWhatsAppBroadcastJob::class, function ($job) use ($business, $campaign) {
            return $job->businessId === (string) $business->id && $job->campaignId === (string) $campaign->id;
        });
    }

    public function test_merchant_can_estimate_recipients_with_valid_and_invalid_filters(): void
    {
        [$user, $business] = $this->createMerchant();

        \App\Models\Customer::create([
            'business_id'     => $business->id,
            'name'            => 'Customer Gold',
            'phone'           => '081234567891',
            'membership_tier' => 'gold',
            'is_active'       => true,
        ]);

        \App\Models\Customer::create([
            'business_id'     => $business->id,
            'name'            => 'Customer Silver',
            'phone'           => '081234567892',
            'membership_tier' => 'silver',
            'is_active'       => true,
        ]);

        // 1. Valid filter 'gold'
        $goldResponse = $this->actingAs($user)->getJson(route('whatsapp.broadcast.estimate', ['filter' => 'gold']));
        $goldResponse->assertOk();
        $goldResponse->assertJson(['count' => 1]);

        // 2. Valid filter 'all'
        $allResponse = $this->actingAs($user)->getJson(route('whatsapp.broadcast.estimate', ['filter' => 'all']));
        $allResponse->assertOk();
        $allResponse->assertJson(['count' => 2]);

        // 3. Invalid filter returns 422 Unprocessable Entity
        $invalidResponse = $this->actingAs($user)->getJson(route('whatsapp.broadcast.estimate', ['filter' => 'hack_tier']));
        $invalidResponse->assertStatus(422);
    }

    public function test_merchant_can_update_settings_for_first_time_creates_session(): void
    {
        [$user, $business] = $this->createMerchant();

        $this->assertDatabaseMissing('whatsapp_sessions', ['business_id' => $business->id]);

        $response = $this->actingAs($user)->post(route('whatsapp.settings'), [
            'auto_send_receipt' => 1,
            'receipt_template'  => 'Terima kasih atas kunjungannya!',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('whatsapp_sessions', [
            'business_id'       => $business->id,
            'auto_send_receipt' => 1,
            'receipt_template'  => 'Terima kasih atas kunjungannya!',
        ]);
    }

    public function test_merchant_can_fetch_broadcast_json_for_live_polling(): void
    {
        [$user, $business] = $this->createMerchant();

        $campaign = WhatsAppBroadcastCampaign::create([
            'business_id'      => $business->id,
            'title'            => 'Live Polling Test',
            'message'          => 'Test message',
            'target_filter'    => 'all',
            'total_recipients' => 50,
            'total_sent'       => 25,
            'total_failed'     => 1,
            'status'           => 'processing',
        ]);

        $response = $this->actingAs($user)->getJson(route('whatsapp.broadcast.show', $campaign));

        $response->assertOk();
        $response->assertJson([
            'id'               => $campaign->id,
            'status'           => 'processing',
            'total_recipients' => 50,
            'total_sent'       => 25,
            'total_failed'     => 1,
        ]);
    }

    public function test_guest_can_view_public_receipt_without_merchant_session(): void
    {
        [$user, $business] = $this->createMerchant();

        $order = PosOrder::create([
            'business_id'     => $business->id,
            'user_id'         => $user->id,
            'order_number'    => 'ORD-PUBLIC-001',
            'order_date'      => now(),
            'status'          => PosOrder::STATUS_COMPLETED,
            'total_amount'    => 75000,
            'final_amount'    => 75000,
            'paid_amount'     => 75000,
            'payment_status'  => 'paid',
        ]);

        // Unauthenticated guest opens public receipt link from WhatsApp
        $response = $this->get(route('public.receipt', $order->id));

        $response->assertOk();
        $response->assertSee($business->name);
        $response->assertSee('Struk Digital Transaksi');
        $response->assertSee('Cetak Struk');
        $response->assertDontSee('Terminal');

        // Test draft/held order is forbidden for public
        $draftOrder = PosOrder::create([
            'business_id'     => $business->id,
            'user_id'         => $user->id,
            'order_number'    => 'ORD-DRAFT-001',
            'order_date'      => now(),
            'status'          => PosOrder::STATUS_DRAFT_HELD,
            'total_amount'    => 50000,
            'final_amount'    => 50000,
        ]);

        $draftResponse = $this->get(route('public.receipt', $draftOrder->id));
        $draftResponse->assertNotFound();
    }

    public function test_broadcast_creation_rejects_ssrf_media_urls(): void
    {
        [$user, $business] = $this->createMerchant();

        WhatsAppAccount::create([
            'business_id'     => $business->id,
            'waba_id'         => '109876543210',
            'phone_number_id' => '100012345678',
            'phone_number'    => '6281234567890',
            'access_token'    => 'test_token',
            'status'          => 'active',
        ]);

        $invalidUrls = [
            'http://169.254.169.254/latest/meta-data',
            'https://127.0.0.1/evil.png',
            'https://localhost/image.png',
            'https://192.168.1.1/secret.jpg',
            'https://10.0.0.1/banner.png',
            'http://example.com/banner.png',
        ];

        foreach ($invalidUrls as $invalidUrl) {
            $response = $this->actingAs($user)->post(route('whatsapp.broadcast.store'), [
                'title'         => 'SSRF Test Campaign',
                'message'       => 'Test message with dangerous URL',
                'media_url'     => $invalidUrl,
                'target_filter' => 'all',
            ]);

            $response->assertSessionHasErrors('media_url');
        }
    }

    public function test_whatsapp_test_send_normalizes_phone_number_and_is_rate_limited(): void
    {
        [$user, $business] = $this->createMerchant();

        $gatewayMock = $this->mock(WhatsAppGatewayService::class);
        $gatewayMock->shouldReceive('sendMessage')
            ->andReturn(['success' => true]);

        // Test sending with 08... format -> should normalize to 628...
        $response = $this->actingAs($user)->postJson(route('whatsapp.test'), [
            'phone'   => '081234567890',
            'message' => 'Pesan uji coba gateway',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('whatsapp_message_logs', [
            'business_id'     => $business->id,
            'type'            => 'test',
            'recipient_phone' => '6281234567890',
        ]);

        // Send remaining 4 allowed requests within 1 minute
        for ($i = 0; $i < 4; $i++) {
            $this->actingAs($user)->postJson(route('whatsapp.test'), [
                'phone'   => '081234567890',
                'message' => "Pesan uji coba burst #{$i}",
            ])->assertOk();
        }

        // The 6th request within 1 minute must be rejected by throttle:5,1
        $throttledResponse = $this->actingAs($user)->postJson(route('whatsapp.test'), [
            'phone'   => '081234567890',
            'message' => 'Pesan ke-6 melebihi rate limit',
        ]);

        $throttledResponse->assertStatus(429);
    }

    public function test_send_order_receipt_creates_audit_log_when_phone_is_overridden(): void
    {
        [$user, $business] = $this->createMerchant();

        $customer = \App\Models\Customer::create([
            'business_id' => $business->id,
            'name'        => 'Pelanggan Setia',
            'phone'       => '081234567890',
            'is_active'   => true,
        ]);

        $order = PosOrder::create([
            'business_id'    => $business->id,
            'user_id'        => $user->id,
            'customer_id'    => $customer->id,
            'order_number'   => 'ORD-WA-OVERRIDE',
            'order_date'     => now(),
            'status'         => PosOrder::STATUS_COMPLETED,
            'total_amount'   => 50000,
            'final_amount'   => 50000,
            'paid_amount'    => 50000,
            'payment_status' => 'paid',
        ]);

        $gatewayMock = $this->mock(WhatsAppGatewayService::class);
        $gatewayMock->shouldReceive('sendReceipt')
            ->once()
            ->andReturn(true);

        // Kasir override nomor dari 081234567890 ke 089876543210
        $response = $this->actingAs($user)->postJson(route('whatsapp.orders.receipt', $order), [
            'phone' => '089876543210',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('audit_logs', [
            'business_id'    => $business->id,
            'action'         => 'receipt.phone_override',
            'auditable_type' => PosOrder::class,
            'auditable_id'   => $order->id,
        ]);
    }

    public function test_broadcast_creation_enforces_idempotency_key_lock(): void
    {
        [$user, $business] = $this->createMerchant();

        WhatsAppAccount::create([
            'business_id'     => $business->id,
            'waba_id'         => '109876543210',
            'phone_number_id' => '100012345678',
            'phone_number'    => '6281234567890',
            'access_token'    => 'test_token',
            'status'          => 'active',
        ]);

        \Illuminate\Support\Facades\Queue::fake();

        $payload = [
            'title'         => 'Flash Sale Kopi Merdeka',
            'message'       => 'Diskon 50% khusus hari ini untuk seluruh pelanggan setia!',
            'target_filter' => 'all',
        ];

        // Pengiriman pertama -> Berhasil
        $response1 = $this->actingAs($user)->post(route('whatsapp.broadcast.store'), $payload);
        $response1->assertRedirect();
        $response1->assertSessionHasNoErrors();

        // Pengiriman kedua dengan data yang sama (misal double-click kasir / koneksi lagging) -> Ditolak oleh Idempotency Lock
        $response2 = $this->actingAs($user)->post(route('whatsapp.broadcast.store'), $payload);
        $response2->assertSessionHasErrors('title');

        $this->assertEquals(1, WhatsAppBroadcastCampaign::where('title', 'Flash Sale Kopi Merdeka')->count());
    }

    public function test_pii_masking_in_logs_and_broadcast_detail_for_non_owner(): void
    {
        [$owner, $business] = $this->createMerchant();

        // Buat user staf admin (non-owner dengan akses modul)
        $adminStaff = User::factory()->create([
            'name'  => 'Staf Admin Toko',
            'email' => 'admin@cooca.id',
        ]);
        $business->users()->attach($adminStaff->id, [
            'id'   => (string) Str::uuid(),
            'role' => 'admin',
        ]);
        $adminStaff->update(['active_business_id' => $business->id]);

        // Buat log pesan dengan nomor lengkap
        WhatsAppMessageLog::create([
            'business_id'     => $business->id,
            'type'            => 'receipt',
            'recipient_phone' => '081234567890',
            'recipient_name'  => 'Budi Raharja',
            'message'         => 'Struk belanja Rp 50.000',
            'status'          => 'sent',
        ]);

        // Buat kampanye dan penerima broadcast
        $campaign = WhatsAppBroadcastCampaign::create([
            'business_id'      => $business->id,
            'title'            => 'Promo Member',
            'message'          => 'Pesan promo member',
            'target_filter'    => 'all',
            'status'           => 'completed',
            'total_recipients' => 1,
            'total_sent'       => 1,
        ]);

        \App\Models\WhatsAppBroadcastRecipient::create([
            'campaign_id'    => $campaign->id,
            'customer_name'  => 'Siti Aminah',
            'phone_number'   => '085712345678',
            'status'         => 'sent',
        ]);

        // 1. Staf Admin (non-owner) mengakses log: nomor harus disamarkan (0812••••7890)
        Context::setBusiness($business);
        $cashierLogsResponse = $this->actingAs($adminStaff)->get(route('whatsapp.logs.index'));
        $cashierLogsResponse->assertOk();
        $cashierLogsResponse->assertSee('0812••••7890');
        $cashierLogsResponse->assertDontSee('>081234567890<', false);

        // 2. Staf Admin (non-owner) mengakses detail broadcast: nomor harus disamarkan (0857••••5678)
        $cashierDetailResponse = $this->actingAs($adminStaff)->get(route('whatsapp.broadcast.show', $campaign));
        $cashierDetailResponse->assertOk();
        $cashierDetailResponse->assertSee('0857••••5678');
        $cashierDetailResponse->assertDontSee('>085712345678<', false);

        // 3. Pemilik (owner) mengakses log: nomor utuh terlihat (081234567890)
        $ownerLogsResponse = $this->actingAs($owner)->get(route('whatsapp.logs.index'));
        $ownerLogsResponse->assertOk();
        $ownerLogsResponse->assertSee('081234567890');
    }

    public function test_broadcast_composer_renders_context_aware_tags_and_pharmacy_warning(): void
    {
        [$user, $business] = $this->createMerchant();

        // 1. Apotek: Harus menampilkan peringatan Meta Health Policy & tag {no_resep}
        $business->update(['template_code' => 'retail_pharmacy']);
        Context::setBusiness($business->fresh());

        $pharmacyResponse = $this->actingAs($user)->get(route('whatsapp.broadcast.index', ['open_composer' => 1]));
        $pharmacyResponse->assertOk();
        $pharmacyResponse->assertSee('Peringatan Kebijakan Farmasi Meta &amp; BPOM', false);
        $pharmacyResponse->assertSee('{no_resep}');
        $pharmacyResponse->assertSee('Dilarang mempromosikan obat keras');

        // 2. Bengkel: Menampilkan tag {nopol} & {servis_terakhir}, TANPA peringatan farmasi
        $business->update(['template_code' => 'service_workshop']);
        Context::setBusiness($business->fresh());

        $workshopResponse = $this->actingAs($user)->get(route('whatsapp.broadcast.index', ['open_composer' => 1]));
        $workshopResponse->assertOk();
        $workshopResponse->assertSee('{nopol}');
        $workshopResponse->assertSee('{servis_terakhir}');
        $workshopResponse->assertDontSee('Peringatan Kebijakan Farmasi Meta');

        // 3. F&B: Menampilkan tag {meja} & {poin}
        $business->update(['template_code' => 'fnb_cafe']);
        Context::setBusiness($business->fresh());

        $fnbResponse = $this->actingAs($user)->get(route('whatsapp.broadcast.index', ['open_composer' => 1]));
        $fnbResponse->assertOk();
        $fnbResponse->assertSee('{meja}');
        $fnbResponse->assertSee('{poin}');

        // 4. Laundry: Menampilkan tag {no_rak} & {berat_kg}
        $business->update(['template_code' => 'service_laundry']);
        Context::setBusiness($business->fresh());

        $laundryResponse = $this->actingAs($user)->get(route('whatsapp.broadcast.index', ['open_composer' => 1]));
        $laundryResponse->assertOk();
        $laundryResponse->assertSee('{no_rak}');
        $laundryResponse->assertSee('{berat_kg}');

        // 5. Manufaktur: Menampilkan tag {no_spk} & {produk}
        $business->update(['template_code' => 'mfg_garment']);
        Context::setBusiness($business->fresh());

        $mfgResponse = $this->actingAs($user)->get(route('whatsapp.broadcast.index', ['open_composer' => 1]));
        $mfgResponse->assertOk();
        $mfgResponse->assertSee('{no_spk}');
        $mfgResponse->assertSee('{produk}');

        // 6. Kontraktor: Menampilkan tag {proyek} & {termin}
        $business->update(['template_code' => 'service_contractor']);
        Context::setBusiness($business->fresh());

        $contractorResponse = $this->actingAs($user)->get(route('whatsapp.broadcast.index', ['open_composer' => 1]));
        $contractorResponse->assertOk();
        $contractorResponse->assertSee('{proyek}');
        $contractorResponse->assertSee('{termin}');
    }

    public function test_broadcast_composer_renders_quiet_hours_banner_markup(): void
    {
        [$user, $business] = $this->createMerchant();

        $response = $this->actingAs($user)->get(route('whatsapp.broadcast.index', ['open_composer' => 1]));
        $response->assertOk();

        // Banner Quiet Hours harus ada di template Alpine.js
        $response->assertSee('Peringatan Jam Istirahat Pelanggan (Quiet Hours 21:00 &ndash; 08:00 WIB)', false);
        $response->assertSee('isQuietHours: false', false);
        $response->assertSee('report spam', false);
    }

    public function test_broadcast_show_and_receipt_enforce_string_uuid_idor_protection(): void
    {
        [$userA, $businessA] = $this->createMerchant();
        [$userB, $businessB] = $this->createMerchant();

        // Buat kampanye milik bisnis B
        $campaignB = WhatsAppBroadcastCampaign::create([
            'business_id'   => $businessB->id,
            'title'         => 'Promo Rahasia Bisnis B',
            'message'       => 'Pesan rahasia',
            'target_filter' => 'all',
            'status'        => 'completed',
        ]);

        // Merchant A mencoba mengakses detail kampanye bisnis B -> Wajib HTTP 404
        Context::setBusiness($businessA);
        $response = $this->actingAs($userA)->get(route('whatsapp.broadcast.show', $campaignB));
        $response->assertNotFound();
    }

    public function test_disconnect_requires_supervisor_pin_when_configured(): void
    {
        [$user, $business] = $this->createMerchant();
        $business->update([
            'pos_supervisor_pin' => \Illuminate\Support\Facades\Hash::make('8899'),
        ]);
        Context::setBusiness($business->fresh());

        // Disconnect tanpa PIN -> HTTP 422
        $response = $this->actingAs($user)->postJson(route('whatsapp.meta.disconnect'), []);
        $response->assertStatus(422);
        $response->assertJson(['pin_required' => true]);

        // Disconnect dengan PIN salah -> HTTP 422
        $responseWrong = $this->actingAs($user)->postJson(route('whatsapp.meta.disconnect'), ['pin' => '1234']);
        $responseWrong->assertStatus(422);

        // Disconnect dengan PIN benar -> HTTP 200
        $responseOk = $this->actingAs($user)->postJson(route('whatsapp.meta.disconnect'), ['pin' => '8899']);
        $responseOk->assertOk();
        $responseOk->assertJson(['success' => true]);
    }

    public function test_whatsapp_session_meta_access_token_is_encrypted_and_hidden(): void
    {
        [$user, $business] = $this->createMerchant();

        $rawToken = 'EAABwzLixnjYBA_secret_meta_cloud_token_999888';

        $session = \App\Models\WhatsAppSession::create([
            'business_id'          => $business->id,
            'session_id'           => 'cooca_' . $business->id,
            'provider'             => 'meta_cloud',
            'meta_access_token'    => $rawToken,
            'meta_phone_number_id' => '1000999888777',
            'is_active'            => true,
        ]);

        // 1. Verifikasi token terdekripsi dengan benar saat diakses via Eloquent getter
        $this->assertEquals($rawToken, $session->meta_access_token);

        // 2. Verifikasi ciphertext tersimpan di database mentah (bukan plaintext)
        $rawDbValue = \Illuminate\Support\Facades\DB::table('whatsapp_sessions')
            ->where('id', $session->id)
            ->value('meta_access_token');

        $this->assertNotEmpty($rawDbValue);
        $this->assertNotEquals($rawToken, $rawDbValue, 'Raw DB value must be encrypted and not match the plaintext token.');

        // 3. Verifikasi token disembunyikan (hidden) dari array dan JSON serialization
        $arrayData = $session->toArray();
        $this->assertArrayNotHasKey('meta_access_token', $arrayData, 'meta_access_token must not be exposed in toArray().');

        $jsonString = $session->toJson();
        $this->assertStringNotContainsString($rawToken, $jsonString, 'Plaintext token must never appear in JSON serialization.');
    }

    public function test_merchant_can_view_broadcast_detail_in_both_locales(): void
    {
        [$user, $business] = $this->createMerchant();

        $campaign = WhatsAppBroadcastCampaign::create([
            'business_id'      => $business->id,
            'title'            => 'Detail Promo Lebaran',
            'message'          => 'Halo pelanggan setia, nikmati diskon lebaran!',
            'target_filter'    => 'all',
            'total_recipients' => 20,
            'total_sent'       => 19,
            'total_failed'     => 1,
            'status'           => 'completed',
        ]);

        \App\Models\WhatsAppBroadcastRecipient::create([
            'campaign_id'    => $campaign->id,
            'customer_name'  => 'Ahmad Fauzi',
            'phone_number'   => '081234567890',
            'status'         => 'sent',
            'sent_at'        => now(),
        ]);

        // 1. Indonesian View
        app()->setLocale('id');
        $responseId = $this->actingAs($user)->get(route('whatsapp.broadcast.show', $campaign));
        $responseId->assertOk();
        $responseId->assertSee('Detail Promo Lebaran');
        $responseId->assertSee('Audit Log Penerima Pesan');
        $responseId->assertSee('Template Pesan Promosi Yang Dikirim');
        $responseId->assertSee('Ahmad Fauzi');

        // 2. English View
        app()->setLocale('en');
        $responseEn = $this->actingAs($user)->get(route('whatsapp.broadcast.show', $campaign));
        $responseEn->assertOk();
        $responseEn->assertSee('Detail Promo Lebaran');
        $responseEn->assertSee('Recipient Message Audit Log');
        $responseEn->assertSee('Promotional Message Template Sent');
        $responseEn->assertSee('Ahmad Fauzi');

        app()->setLocale('id');
    }

    public function test_merchant_can_estimate_and_target_broadcast_to_specific_outlet(): void
    {
        [$user, $business] = $this->createMerchant();

        // Connect WhatsApp
        WhatsAppAccount::create([
            'business_id'          => $business->id,
            'waba_id'              => '109876543210',
            'phone_number_id'      => '100012345678',
            'phone_number'         => '6281234567890',
            'display_phone_number' => '+62 852-8786-4176',
            'verified_name'        => 'Kopi Kenangan Sejahtera Official',
            'quality_rating'       => 'GREEN',
            'messaging_limit_tier' => 'TIER_1K',
            'access_token'         => 'EAABwzLixnjYBA_test_token',
            'status'               => 'active',
        ]);

        $locSenopati = Location::create([
            'business_id' => $business->id,
            'name'        => 'Outlet Senopati',
            'type'        => 'outlet',
            'is_active'   => true,
        ]);

        $locKemang = Location::create([
            'business_id' => $business->id,
            'name'        => 'Outlet Kemang',
            'type'        => 'outlet',
            'is_active'   => true,
        ]);

        $cust1 = Customer::create([
            'business_id' => $business->id,
            'name'        => 'Pelanggan Senopati 1',
            'phone'       => '081299990001',
            'is_active'   => true,
        ]);

        $cust2 = Customer::create([
            'business_id' => $business->id,
            'name'        => 'Pelanggan Senopati 2',
            'phone'       => '081299990002',
            'is_active'   => true,
        ]);

        $cust3 = Customer::create([
            'business_id' => $business->id,
            'name'        => 'Pelanggan Kemang',
            'phone'       => '081299990003',
            'is_active'   => true,
        ]);

        // Attach orders to locations
        PosOrder::create([
            'business_id'  => $business->id,
            'user_id'      => $user->id,
            'location_id'  => $locSenopati->id,
            'customer_id'  => $cust1->id,
            'order_number' => 'ORD-SENO-001',
            'order_date'   => now(),
            'status'       => 'completed',
        ]);

        PosOrder::create([
            'business_id'  => $business->id,
            'user_id'      => $user->id,
            'location_id'  => $locSenopati->id,
            'customer_id'  => $cust2->id,
            'order_number' => 'ORD-SENO-002',
            'order_date'   => now(),
            'status'       => 'completed',
        ]);

        PosOrder::create([
            'business_id'  => $business->id,
            'user_id'      => $user->id,
            'location_id'  => $locKemang->id,
            'customer_id'  => $cust3->id,
            'order_number' => 'ORD-KEM-001',
            'order_date'   => now(),
            'status'       => 'completed',
        ]);

        // 1. Estimate Senopati: must return count 2
        $responseEstSeno = $this->actingAs($user)->getJson(route('whatsapp.broadcast.estimate', [
            'filter' => "outlet:{$locSenopati->id}",
        ]));
        $responseEstSeno->assertOk();
        $responseEstSeno->assertJson(['count' => 2]);

        // 2. Estimate Kemang: must return count 1
        $responseEstKem = $this->actingAs($user)->getJson(route('whatsapp.broadcast.estimate', [
            'filter' => "outlet:{$locKemang->id}",
        ]));
        $responseEstKem->assertOk();
        $responseEstKem->assertJson(['count' => 1]);

        // 3. Dispatch broadcast to outlet Senopati
        $storeResponse = $this->actingAs($user)->post(route('whatsapp.broadcast.store'), [
            'title'         => 'Promo Khusus Outlet Senopati',
            'message'       => 'Halo {nama}, nikmati diskon khusus di Senopati!',
            'target_filter' => "outlet:{$locSenopati->id}",
        ]);

        $campaign = WhatsAppBroadcastCampaign::where('business_id', $business->id)
            ->where('title', 'Promo Khusus Outlet Senopati')
            ->first();

        $this->assertNotNull($campaign);
        $this->assertEquals("outlet:{$locSenopati->id}", $campaign->target_filter);
        $storeResponse->assertRedirect(route('whatsapp.broadcast.show', $campaign));
    }

    public function test_merchant_cannot_target_outlet_of_another_business(): void
    {
        [$userA, $businessA] = $this->createMerchant('merchant_a@cooca.id');
        [$userB, $businessB] = $this->createMerchant('merchant_b@cooca.id');

        // Connect WhatsApp for Merchant A
        WhatsAppAccount::create([
            'business_id'          => $businessA->id,
            'waba_id'              => '109876543211',
            'phone_number_id'      => '100012345679',
            'phone_number'         => '6281234567891',
            'display_phone_number' => '+62 812-3456-7891',
            'verified_name'        => 'Toko A Official',
            'quality_rating'       => 'GREEN',
            'messaging_limit_tier' => 'TIER_1K',
            'access_token'         => 'EAABwzLixnjYBA_test_token_a',
            'status'               => 'active',
        ]);

        $locB = Location::create([
            'business_id' => $businessB->id,
            'name'        => 'Outlet Milik Toko B',
            'type'        => 'outlet',
            'is_active'   => true,
        ]);

        // Merchant A attempts to estimate using Merchant B's outlet -> must fail validation
        $responseEstimate = $this->actingAs($userA)->getJson(route('whatsapp.broadcast.estimate', [
            'filter' => "outlet:{$locB->id}",
        ]));
        $responseEstimate->assertStatus(422);

        // Merchant A attempts to store campaign targeting Merchant B's outlet -> must fail validation
        $responseStore = $this->actingAs($userA)->post(route('whatsapp.broadcast.store'), [
            'title'         => 'Attacking Store B Outlet',
            'message'       => 'Invalid broadcast attempt',
            'target_filter' => "outlet:{$locB->id}",
        ]);
        $responseStore->assertSessionHasErrors(['target_filter']);
    }

    public function test_broadcast_ui_renders_multi_outlet_selector_and_detail_badge(): void
    {
        [$user, $business] = $this->createMerchant();

        $loc = Location::create([
            'business_id' => $business->id,
            'name'        => 'Outlet Menteng Premium',
            'type'        => 'outlet',
            'is_active'   => true,
        ]);

        $customer = Customer::create([
            'business_id' => $business->id,
            'name'        => 'Siti Menteng',
            'phone'       => '081277778888',
            'is_active'   => true,
        ]);

        PosOrder::create([
            'business_id'  => $business->id,
            'user_id'      => $user->id,
            'location_id'  => $loc->id,
            'customer_id'  => $customer->id,
            'order_number' => 'ORD-MNT-001',
            'order_date'   => now(),
            'status'       => 'completed',
        ]);

        // 1. Index page in Indonesian
        app()->setLocale('id');
        $responseIndexId = $this->actingAs($user)->get(route('whatsapp.broadcast.index'));
        $responseIndexId->assertOk();
        $responseIndexId->assertSee('Outlet Menteng Premium');
        $responseIndexId->assertSee('Berdasarkan Cabang / Outlet');

        // 2. Index page in English
        app()->setLocale('en');
        $responseIndexEn = $this->actingAs($user)->get(route('whatsapp.broadcast.index'));
        $responseIndexEn->assertOk();
        $responseIndexEn->assertSee('Outlet Menteng Premium');
        $responseIndexEn->assertSee('By Branch / Outlet');

        // 3. Campaign Detail badge rendering
        $campaign = WhatsAppBroadcastCampaign::create([
            'business_id'      => $business->id,
            'title'            => 'Promo Spesial Menteng',
            'message'          => 'Diskon 20% di Menteng',
            'target_filter'    => "outlet:{$loc->id}",
            'total_recipients' => 1,
            'total_sent'       => 1,
            'total_failed'     => 0,
            'status'           => 'completed',
        ]);

        app()->setLocale('id');
        $responseDetailId = $this->actingAs($user)->get(route('whatsapp.broadcast.show', $campaign));
        $responseDetailId->assertOk();
        $responseDetailId->assertSee('Cabang Outlet Menteng Premium');

        app()->setLocale('en');
        $responseDetailEn = $this->actingAs($user)->get(route('whatsapp.broadcast.show', $campaign));
        $responseDetailEn->assertOk();
        $responseDetailEn->assertSee('Outlet Menteng Premium Branch');

        app()->setLocale('id');
    }

    public function test_whatsapp_views_satisfy_mobile_touch_targets_and_wcag_a11y(): void
    {
        [$user, $business] = $this->createMerchant();

        // 1. WhatsApp Index View
        $responseIndex = $this->actingAs($user)->get(route('whatsapp.index'));
        $responseIndex->assertOk();
        $responseIndex->assertSee('min-h-[44px]', false);

        // 2. Broadcast Hub & Modal Sheet View
        $responseBroadcast = $this->actingAs($user)->get(route('whatsapp.broadcast.index'));
        $responseBroadcast->assertOk();
        $responseBroadcast->assertSee('role="dialog"', false);
        $responseBroadcast->assertSee('aria-modal="true"', false);
        $responseBroadcast->assertSee('aria-labelledby="broadcastModalTitle"', false);
        $responseBroadcast->assertSee('aria-label="' . __('whatsapp.close_btn') . '"', false);
        $responseBroadcast->assertSee('min-h-[44px]', false);

        // 3. Message Logs & Inspector View
        $responseLogs = $this->actingAs($user)->get(route('whatsapp.logs.index'));
        $responseLogs->assertOk();
        $responseLogs->assertSee('role="dialog"', false);
        $responseLogs->assertSee('aria-modal="true"', false);
        $responseLogs->assertSee('aria-labelledby="inspectorModalTitle"', false);
        $responseLogs->assertSee('aria-labelledby="pruneModalTitle"', false);
        $responseLogs->assertSee('min-h-[44px]', false);

        // 4. Broadcast Detail View
        $campaign = WhatsAppBroadcastCampaign::create([
            'business_id'      => $business->id,
            'title'            => 'Ergonomics Test Campaign',
            'message'          => 'Test message ergonomics',
            'target_filter'    => 'all',
            'total_recipients' => 5,
            'total_sent'       => 5,
            'total_failed'     => 0,
            'status'           => 'completed',
        ]);

        $responseDetail = $this->actingAs($user)->get(route('whatsapp.broadcast.show', $campaign));
        $responseDetail->assertOk();
        $responseDetail->assertSee('min-h-[44px]', false);
        $responseDetail->assertSee('min-w-[44px]', false);
    }
}


