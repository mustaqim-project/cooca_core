<?php

declare(strict_types=1);

namespace Tests\Feature\WhatsApp;

use App\Domain\WhatsApp\CloudApi\Templates\CoocaStandardTemplates;
use App\Domain\WhatsApp\WhatsAppGatewayService;
use App\Models\Admin;
use App\Models\Business;
use App\Models\Customer;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\WhatsAppAccount;
use App\Models\WhatsAppBroadcastCampaign;
use App\Models\WhatsAppMessageTemplate;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class MetaStandardTemplatesIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RbacSeeder::class);
        app()->setLocale('id');
    }

    private function makeAdmin(): Admin
    {
        return Admin::factory()->create([
            'name'      => 'Platform Admin',
            'email'     => 'admin@cooca.id',
            'password'  => Hash::make('password123'),
            'role'      => 'super_admin',
            'is_active' => true,
        ]);
    }

    private function createMerchant(): array
    {
        $user = User::factory()->create([
            'name'  => 'Pemilik Toko',
            'email' => 'toko_' . Str::random(8) . '@cooca.id',
        ]);

        $business = Business::create([
            'user_id'  => $user->id,
            'name'     => 'Toko Roti Makmur',
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

    private function setupMetaCredentials(): void
    {
        SystemSetting::set('meta_wa_token', 'EAAG_test_system_user_token_v26', 'whatsapp', true);
        SystemSetting::set('meta_wa_phone_number_id', '10987654321', 'whatsapp');
        SystemSetting::set('meta_wa_waba_id', '1546059137323420', 'whatsapp');
        SystemSetting::set('meta_wa_graph_version', 'v26.0', 'whatsapp');
    }

    public function test_catalog_defines_eleven_standard_templates_matching_meta_specs(): void
    {
        $catalog = CoocaStandardTemplates::all();

        $this->assertCount(11, $catalog);

        $expectedNames = [
            CoocaStandardTemplates::RECEIPT,
            CoocaStandardTemplates::INVOICE,
            CoocaStandardTemplates::ORDER_STATUS,
            CoocaStandardTemplates::PROMO_BROADCAST,
            CoocaStandardTemplates::CUSTOMER_WELCOME,
            CoocaStandardTemplates::PAYMENT_REMINDER,
            CoocaStandardTemplates::OTP,
            CoocaStandardTemplates::RESERVATION_REMINDER,
            CoocaStandardTemplates::MARKETPLACE_RECEIPT,
            CoocaStandardTemplates::SHIPPING_TRACKING,
            CoocaStandardTemplates::CART_REMINDER,
        ];

        foreach ($expectedNames as $name) {
            $this->assertArrayHasKey($name, $catalog);
            $tpl = $catalog[$name];

            // 1. Name validation
            $this->assertMatchesRegularExpression('/^[a-z0-9_]+$/', $tpl['name']);

            // 2. Category validation
            $this->assertContains($tpl['category'], ['UTILITY', 'MARKETING', 'AUTHENTICATION']);

            // 3. Language
            $this->assertEquals('id', $tpl['language']);

            // 4. Components structure
            $this->assertIsArray($tpl['components']);

            // 5. Marketing templates must contain opt-out in footer
            if ($tpl['category'] === 'MARKETING') {
                $footer = collect($tpl['components'])->firstWhere('type', 'FOOTER');
                $this->assertNotNull($footer, "Marketing template {$name} must have a FOOTER component");
                $this->assertStringContainsString('STOP', $footer['text']);
            }
        }
    }

    public function test_can_seed_standard_templates_locally_into_database(): void
    {
        $wabaId = '1546059137323420';
        $seededCount = CoocaStandardTemplates::seedLocalTemplates($wabaId);

        $this->assertEquals(11, $seededCount);
        $this->assertEquals(11, WhatsAppMessageTemplate::whereNull('business_id')->count());

        $promoTemplate = WhatsAppMessageTemplate::where('name', CoocaStandardTemplates::PROMO_BROADCAST)->first();
        $this->assertNotNull($promoTemplate);
        $this->assertEquals('MARKETING', $promoTemplate->category);
        $this->assertEquals('APPROVED', $promoTemplate->status);
        $this->assertNull($promoTemplate->business_id); // Global system template

        // Re-seeding must be idempotent
        $reseededCount = CoocaStandardTemplates::seedLocalTemplates($wabaId);
        $this->assertEquals(11, $reseededCount);
        $this->assertEquals(11, WhatsAppMessageTemplate::whereNull('business_id')->count());
    }

    public function test_admin_can_deploy_standard_templates_to_meta(): void
    {
        $admin = $this->makeAdmin();
        $this->setupMetaCredentials();

        Http::fake([
            'https://graph.facebook.com/v26.0/1546059137323420/message_templates' => Http::response([
                'id'     => 'meta_created_tpl_99',
                'status' => 'PENDING',
            ], 200),
        ]);

        $response = $this->actingAs($admin, 'admin')->postJson(route('admin.whatsapp.meta-templates.deploy-standards'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertGreaterThan(0, count($response->json('templates')));
    }

    public function test_admin_can_seed_standard_templates_via_endpoint(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->postJson(route('admin.whatsapp.meta-templates.seed-standards'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertEquals(11, WhatsAppMessageTemplate::whereNull('business_id')->count());
    }

    public function test_merchant_can_view_broadcast_with_approved_templates(): void
    {
        [$user, $business] = $this->createMerchant();
        CoocaStandardTemplates::seedLocalTemplates('1546059137323420');

        $response = $this->actingAs($user)
            ->withSession(['current_business_id' => $business->id])
            ->get(route('whatsapp.broadcast.index'));

        $response->assertOk();
        $response->assertViewHas('approvedTemplates');
        $response->assertSee('Template Resmi Meta (Anti-Blokir WABA)');
        $response->assertSee('cooca_promo_broadcast');
    }

    public function test_merchant_can_store_broadcast_with_meta_standard_template_parameters(): void
    {
        [$user, $business] = $this->createMerchant();

        // Setup active WhatsAppAccount for merchant
        WhatsAppAccount::create([
            'business_id'          => $business->id,
            'waba_id'              => '1546059137323420',
            'phone_number_id'      => '10987654321',
            'phone_number'         => '6281234567890',
            'display_phone_number' => '081234567890',
            'verified_name'        => 'Toko Roti Makmur',
            'access_token'         => 'EAAG_test_merchant_token',
            'status'               => 'active',
        ]);

        // Create customer recipient
        Customer::create([
            'business_id'     => $business->id,
            'name'            => 'Siti Rahma',
            'phone'           => '081299887766',
            'membership_tier' => 'gold',
            'is_active'       => true,
        ]);

        $payload = [
            'title'             => 'Promo Gajian 25% Anti Blokir',
            'message'           => 'Halo Siti Rahma! Ada promo gajian spesial dari Toko Roti Makmur.',
            'target_filter'     => 'all',
            'template_name'     => CoocaStandardTemplates::PROMO_BROADCAST,
            'template_language' => 'id',
            'template_params'   => [
                'offer'        => 'Diskon 25% Semua Roti',
                'voucher_code' => 'GAJIAN25',
                'valid_until'  => '31 Oktober 2026',
            ],
        ];

        $response = $this->actingAs($user)
            ->withSession(['current_business_id' => $business->id])
            ->post(route('whatsapp.broadcast.store'), $payload);

        $response->assertRedirect();

        $campaign = WhatsAppBroadcastCampaign::where('business_id', $business->id)->first();
        $this->assertNotNull($campaign);
        $this->assertEquals(CoocaStandardTemplates::PROMO_BROADCAST, $campaign->template_name);
        $this->assertEquals('id', $campaign->template_language);
        $this->assertEquals('GAJIAN25', $campaign->template_params['voucher_code']);
    }

    public function test_gateway_send_broadcast_enforces_meta_template_sending(): void
    {
        [$user, $business] = $this->createMerchant();

        WhatsAppAccount::create([
            'business_id'          => $business->id,
            'waba_id'              => '1546059137323420',
            'phone_number_id'      => '10987654321',
            'phone_number'         => '6281234567890',
            'display_phone_number' => '081234567890',
            'verified_name'        => 'Toko Roti Makmur',
            'access_token'         => 'EAAG_test_merchant_token',
            'status'               => 'active',
        ]);

        Customer::create([
            'business_id'     => $business->id,
            'name'            => 'Ahmad Dani',
            'phone'           => '081211223344',
            'membership_tier' => 'vip',
            'is_active'       => true,
        ]);

        $campaign = WhatsAppBroadcastCampaign::create([
            'business_id'       => $business->id,
            'title'             => 'Kampanye Template WABA',
            'message'           => 'Halo Ahmad Dani, ada promo spesial!',
            'target_filter'     => 'all',
            'template_name'     => CoocaStandardTemplates::PROMO_BROADCAST,
            'template_language' => 'id',
            'template_params'   => [
                'offer'        => 'Cashback 30%',
                'voucher_code' => 'CASHBACK30',
                'valid_until'  => '28 Februari 2026',
            ],
            'status'            => 'pending',
        ]);

        Http::fake([
            'https://graph.facebook.com/v26.0/10987654321/messages' => function ($request) {
                $body = $request->data();
                // Verify that payload sends official template type rather than raw text
                if (
                    ($body['type'] ?? '') === 'template'
                    && ($body['template']['name'] ?? '') === CoocaStandardTemplates::PROMO_BROADCAST
                ) {
                    return Http::response([
                        'messages' => [
                            ['id' => 'wamid.HBgLMTIzNDU2Nzg5MA=='],
                        ],
                    ], 200);
                }
                return Http::response(['error' => 'Invalid message type'], 400);
            },
        ]);

        $gateway = app(WhatsAppGatewayService::class);
        $gateway->sendBroadcast($business, $campaign);

        $campaign->refresh();
        $this->assertEquals('completed', $campaign->status);
        $this->assertEquals(1, $campaign->total_sent);
        $this->assertEquals(0, $campaign->total_failed);
    }
}
