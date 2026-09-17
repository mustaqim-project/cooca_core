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
            'version'   => 'v21.0',
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
}
