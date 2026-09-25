<?php

declare(strict_types=1);

namespace Tests\Feature\WhatsApp;

use App\Models\Business;
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
        $response->assertSee('Koneksi Gateway');
        $response->assertSee('Blast Promosi');
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

        $createResponse = $this->actingAs($user)->get(route('whatsapp.broadcast.create'));
        $createResponse->assertOk();
        $createResponse->assertSee('Buat Kampanye Blast Baru');
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
}
