<?php

declare(strict_types=1);

namespace Tests\Feature\WhatsApp;

use App\Domain\WhatsApp\WhatsAppGatewayService;
use App\Models\AuditLog;
use App\Models\Business;
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
    }

    private function createMerchant(): array
    {
        $user = User::factory()->create([
            'name'  => 'Pemilik Toko',
            'email' => 'toko@cooca.id',
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

        $response = $this->actingAs($user)->get(route('whatsapp.index'));

        $response->assertOk();
        $response->assertSee('WhatsApp Gateway &amp; Otomasi Bisnis', false);
        $response->assertSee('Meta WhatsApp Cloud API Resmi');
        $response->assertSee('Pendaftaran Mandiri (Embedded Signup)');
        $response->assertSee('WhatsApp Inbox');
        $response->assertSee('WhatsApp Broadcast');
        $response->assertSee('Log Pesan');
    }

    public function test_merchant_can_view_whatsapp_dashboard_when_connected(): void
    {
        [$user, $business] = $this->createMerchant();

        WhatsAppAccount::create([
            'business_id'          => $business->id,
            'waba_id'              => '109876543210',
            'phone_number_id'      => '100012345678',
            'phone_number'         => '6281234567890',
            'display_phone_number' => '+62 812-3456-7890',
            'verified_name'        => 'Kopi Kenangan Sejahtera Official',
            'quality_rating'       => 'GREEN',
            'messaging_limit_tier' => 'TIER_1K',
            'access_token'         => 'EAABwzLixnjYBA_test_token',
            'status'               => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('whatsapp.index'));

        $response->assertOk();
        $response->assertSee('Kopi Kenangan Sejahtera Official');
        $response->assertSee('+62 812-3456-7890');
        $response->assertSee('TIER_1K');
        $response->assertSee('Putuskan Hubungan');
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

        $response = $this->actingAs($user)->get(route('whatsapp.logs.index'));

        $response->assertOk();
        $response->assertSee('Riwayat Komunikasi Keluar');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Struk POS');
        $response->assertSee('Terkirim');
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
            'display_phone_number' => '+62 812-3456-7890',
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
}

