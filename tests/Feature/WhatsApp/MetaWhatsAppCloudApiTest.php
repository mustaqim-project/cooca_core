<?php

declare(strict_types=1);

namespace Tests\Feature\WhatsApp;

use App\Domain\WhatsApp\CloudApi\Templates\InvoiceTemplate;
use App\Domain\WhatsApp\CloudApi\Templates\PosReceiptTemplate;
use App\Domain\WhatsApp\CloudApi\WhatsAppClient;
use App\Domain\WhatsApp\CloudApi\WhatsAppTemplateService;
use App\Domain\WhatsApp\CloudApi\WhatsAppWebhookService;
use App\Jobs\WhatsApp\ProcessWhatsAppIncomingMessageJob;
use App\Jobs\WhatsApp\ProcessWhatsAppMessageStatusJob;
use App\Jobs\WhatsApp\ProcessWhatsAppWebhookJob;
use App\Models\Business;
use App\Models\PosOrder;
use App\Models\User;
use App\Models\WhatsAppAccount;
use App\Models\WhatsAppMessageLog;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class MetaWhatsAppCloudApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.meta_whatsapp.app_id', '123456789012345');
        Config::set('services.meta_whatsapp.app_secret', 'test_meta_app_secret_12345');
        Config::set('services.meta_whatsapp.webhook_verify_token', 'test_verify_token_secure');
        Config::set('services.meta_whatsapp.version', 'v21.0');
    }

    private function createMerchant(): array
    {
        $user = User::factory()->create([
            'name'  => 'Owner Merchant',
            'email' => 'merchant@cooca.id',
        ]);

        $business = Business::create([
            'user_id'  => $user->id,
            'name'     => 'Kopi Nusantara',
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

    /**
     * Test 1: Migrasi tabel & Enkripsi otomatis Access Token pada Model WhatsAppAccount.
     */
    public function test_whatsapp_account_access_token_is_automatically_encrypted_in_database(): void
    {
        [, $business] = $this->createMerchant();

        $plainToken = 'EAABwzLixnjYBOZCx123456789LongLivedMetaTokenTest';

        $account = WhatsAppAccount::create([
            'business_id'              => $business->id,
            'waba_id'                  => '109876543210',
            'phone_number_id'          => '100012345678',
            'phone_number'             => '6281234567890',
            'display_phone_number'     => '+62 812-3456-7890',
            'verified_name'            => 'Kopi Nusantara',
            'quality_rating'           => 'GREEN',
            'access_token'             => $plainToken,
            'status'                   => 'active',
        ]);

        // 1. Ambil data mentah langsung dari DB (bypass Eloquent)
        $rawRow = DB::table('whatsapp_accounts')->where('id', $account->id)->first();

        $this->assertNotNull($rawRow);
        // Nilai mentah di DB tidak boleh berupa teks biasa
        $this->assertNotEquals($plainToken, $rawRow->access_token);
        // Nilai harus berupa ciphertext terenkripsi (Laravel default payload)
        $this->assertStringStartsWith('eyJ', $rawRow->access_token); // Base64 JSON payload of encrypted string

        // 2. Akses via Eloquent model harus otomatis terdekripsi
        $freshAccount = WhatsAppAccount::find($account->id);
        $this->assertEquals($plainToken, $freshAccount->access_token);
        $this->assertTrue($freshAccount->isActive());
        $this->assertTrue($freshAccount->isQualityGood());

        // 3. Relasi hasOne pada Business
        $this->assertNotNull($business->whatsAppAccount);
        $this->assertEquals($account->id, $business->whatsAppAccount->id);
    }

    /**
     * Test 2: Webhook challenge handshake (GET /api/v1/wa/meta/webhook).
     */
    public function test_webhook_verification_handshake_succeeds_with_valid_token(): void
    {
        $challenge = 'random_challenge_code_987654';

        $response = $this->get('/api/v1/wa/meta/webhook?' . http_build_query([
            'hub_mode'         => 'subscribe',
            'hub_verify_token' => 'test_verify_token_secure',
            'hub_challenge'    => $challenge,
        ]));

        $response->assertStatus(200);
        $this->assertEquals($challenge, $response->getContent());
    }

    public function test_webhook_verification_handshake_fails_with_invalid_token(): void
    {
        $response = $this->get('/api/v1/wa/meta/webhook?' . http_build_query([
            'hub_mode'         => 'subscribe',
            'hub_verify_token' => 'wrong_token',
            'hub_challenge'    => '123456',
        ]));

        $response->assertStatus(403);
    }

    /**
     * Test 3: Validasi signature X-Hub-Signature-256 pada Webhook POST.
     */
    public function test_webhook_post_rejects_invalid_signature(): void
    {
        $payload = ['object' => 'whatsapp_business_account', 'entry' => []];
        $rawJson = json_encode($payload);

        $response = $this->call(
            method: 'POST',
            uri: '/api/v1/wa/meta/webhook',
            parameters: [],
            cookies: [],
            files: [],
            server: [
                'CONTENT_TYPE'           => 'application/json',
                'HTTP_X_HUB_SIGNATURE_256' => 'sha256=invalid_signature_hash',
            ],
            content: $rawJson
        );

        $response->assertStatus(403);
        $response->assertJson(['error' => 'Invalid webhook signature']);
    }

    public function test_webhook_post_accepts_valid_signature_and_dispatches_job(): void
    {
        Queue::fake();

        $payload = [
            'object' => 'whatsapp_business_account',
            'entry'  => [
                [
                    'id'      => '109876543210',
                    'changes' => [
                        [
                            'field' => 'messages',
                            'value' => [
                                'messaging_product' => 'whatsapp',
                                'metadata'          => [
                                    'display_phone_number' => '6281234567890',
                                    'phone_number_id'      => '100012345678',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $rawJson = (string) json_encode($payload);
        $signature = 'sha256=' . hash_hmac('sha256', $rawJson, 'test_meta_app_secret_12345');

        $response = $this->call(
            method: 'POST',
            uri: '/api/v1/wa/meta/webhook',
            parameters: [],
            cookies: [],
            files: [],
            server: [
                'CONTENT_TYPE'             => 'application/json',
                'HTTP_X_HUB_SIGNATURE_256' => $signature,
            ],
            content: $rawJson
        );

        $response->assertStatus(200);
        $response->assertJson(['status' => 'EVENT_RECEIVED']);

        Queue::assertPushed(ProcessWhatsAppWebhookJob::class, function ($job) use ($payload) {
            return $job->payload === $payload;
        });
    }

    /**
     * Test 4: Webhook Service mengidentifikasi merchant berdasarkan phone_number_id dan mendispatch event.
     */
    public function test_webhook_service_routes_events_to_correct_tenant(): void
    {
        Queue::fake();

        [, $business] = $this->createMerchant();

        $account = WhatsAppAccount::create([
            'business_id'     => $business->id,
            'waba_id'         => '109876543210',
            'phone_number_id' => '100012345678',
            'phone_number'    => '6281234567890',
            'access_token'    => 'mock_token',
            'status'          => 'active',
        ]);

        $payload = [
            'object' => 'whatsapp_business_account',
            'entry'  => [
                [
                    'id'      => '109876543210',
                    'changes' => [
                        [
                            'field' => 'messages',
                            'value' => [
                                'messaging_product' => 'whatsapp',
                                'metadata'          => [
                                    'display_phone_number' => '6281234567890',
                                    'phone_number_id'      => '100012345678',
                                ],
                                'contacts'          => [
                                    [
                                        'profile' => ['name' => 'Budi Santoso'],
                                        'wa_id'   => '62855512345',
                                    ],
                                ],
                                'messages'          => [
                                    [
                                        'from'      => '62855512345',
                                        'id'        => 'wamid.HBgLMjA=',
                                        'timestamp' => '1726550000',
                                        'type'      => 'text',
                                        'text'      => ['body' => 'Halo, apakah kafe buka hari ini?'],
                                    ],
                                ],
                                'statuses'          => [
                                    [
                                        'id'           => 'wamid.HBgLMjB=',
                                        'recipient_id' => '62855512345',
                                        'status'       => 'delivered',
                                        'timestamp'    => '1726550010',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        /** @var WhatsAppWebhookService $service */
        $service = app(WhatsAppWebhookService::class);
        $dispatchResult = $service->parseAndDispatch($payload);

        $this->assertEquals(1, $dispatchResult['messages_dispatched']);
        $this->assertEquals(1, $dispatchResult['statuses_dispatched']);
        $this->assertEquals(0, $dispatchResult['unmapped_accounts']);

        // Assert job masuk queue dengan tenant ID yang sesuai
        Queue::assertPushed(ProcessWhatsAppIncomingMessageJob::class, function ($job) use ($business, $account) {
            return $job->businessId === $business->id && $job->account->id === $account->id;
        });

        Queue::assertPushed(ProcessWhatsAppMessageStatusJob::class, function ($job) use ($business, $account) {
            return $job->businessId === $business->id && $job->account->id === $account->id;
        });
    }

    /**
     * Test 5: Onboarding Embedded Signup menukar code otorisasi dan menyimpan data merchant.
     */
    public function test_onboarding_exchange_code_creates_tenant_whatsapp_account(): void
    {
        [$user, $business] = $this->createMerchant();

        $authCode = 'AQD897_valid_authorization_code_from_meta_sdk';

        // Mock Graph API Meta
        Http::fake([
            'https://graph.facebook.com/v21.0/oauth/access_token' => Http::response([
                'access_token' => 'EAABwzLixnjYBA_permanent_user_token_mock',
                'token_type'   => 'Bearer',
                'expires_in'   => 5184000,
            ], 200),
            'https://graph.facebook.com/v21.0/109876543210/phone_numbers*' => Http::response([
                'data' => [
                    [
                        'id'                       => '100098765432',
                        'display_phone_number'     => '+62 821-9988-7766',
                        'verified_name'            => 'Kopi Nusantara Official',
                        'quality_rating'           => 'GREEN',
                        'code_verification_status' => 'VERIFIED',
                        'messaging_limit_tier'     => 'TIER_1K',
                    ],
                ],
            ], 200),
            'https://graph.facebook.com/v21.0/109876543210/subscribed_apps' => Http::response([
                'success' => true,
            ], 200),
        ]);

        $response = $this->actingAs($user)->postJson(route('whatsapp.meta.exchange-code'), [
            'code'            => $authCode,
            'waba_id'         => '109876543210',
            'phone_number_id' => '100098765432',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'account' => [
                'verified_name'        => 'Kopi Nusantara Official',
                'display_phone_number' => '+62 821-9988-7766',
                'quality_rating'       => 'GREEN',
                'messaging_limit_tier' => 'TIER_1K',
                'status'               => 'active',
            ],
        ]);

        // Verifikasi di database terisolasi
        $account = WhatsAppAccount::where('business_id', $business->id)->first();
        $this->assertNotNull($account);
        $this->assertEquals('109876543210', $account->waba_id);
        $this->assertEquals('100098765432', $account->phone_number_id);
        $this->assertEquals('6282199887766', $account->phone_number);
        $this->assertEquals('EAABwzLixnjYBA_permanent_user_token_mock', $account->access_token);
        $this->assertNotNull($account->token_expires_at);
    }

    /**
     * Test 6: Message Templates - Pengiriman Struk POS Resmi Meta.
     */
    public function test_send_pos_receipt_template_success(): void
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

        $order = PosOrder::create([
            'business_id'          => $business->id,
            'user_id'              => $user->id,
            'order_number'         => 'POS-2026-0099',
            'customer_name_guest'  => 'Ibu Fatimah',
            'customer_phone_guest' => '081299887766',
            'total_amount'         => 85000,
            'final_amount'         => 85000,
            'payment_status'       => 'PAID',
            'order_status'         => 'COMPLETED',
            'order_date'           => now(),
        ]);

        Http::fake([
            'https://graph.facebook.com/v21.0/100012345678/messages' => Http::response([
                'messaging_product' => 'whatsapp',
                'contacts'          => [['input' => '6281299887766', 'wa_id' => '6281299887766']],
                'messages'          => [['id' => 'wamid.HBgMUmVjZWlwdE1vY2s=']],
            ], 200),
        ]);

        /** @var WhatsAppTemplateService $templateService */
        $templateService = app(WhatsAppTemplateService::class);
        $result = $templateService->sendPosReceipt($order);

        $this->assertTrue($result['success']);
        $this->assertEquals('wamid.HBgMUmVjZWlwdE1vY2s=', $result['message_id']);

        // Verifikasi log tercatat di tabel whatsapp_message_logs
        $log = WhatsAppMessageLog::where('business_id', $business->id)
            ->where('order_id', $order->id)
            ->where('type', 'receipt')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('6281299887766', $log->recipient_phone);
        $this->assertEquals('sent', $log->status);
    }

    /**
     * Test 7: Message Templates - Pengiriman Invoice Resmi Meta.
     */
    public function test_send_invoice_template_success(): void
    {
        [, $business] = $this->createMerchant();

        WhatsAppAccount::create([
            'business_id'     => $business->id,
            'waba_id'         => '109876543210',
            'phone_number_id' => '100012345678',
            'phone_number'    => '6281234567890',
            'access_token'    => 'test_token',
            'status'          => 'active',
        ]);

        Http::fake([
            'https://graph.facebook.com/v21.0/100012345678/messages' => Http::response([
                'messaging_product' => 'whatsapp',
                'contacts'          => [['input' => '6281311223344', 'wa_id' => '6281311223344']],
                'messages'          => [['id' => 'wamid.HBgMSW52b2ljZU1vY2s=']],
            ], 200),
        ]);

        /** @var WhatsAppTemplateService $templateService */
        $templateService = app(WhatsAppTemplateService::class);
        $result = $templateService->sendInvoice(
            business: $business,
            recipientPhone: '081311223344',
            customerName: 'PT Maju Bersama',
            invoiceNumber: 'INV-2026-0042',
            dueDate: now()->addDays(7),
            totalAmount: 2500000,
            invoicePdfUrl: 'https://cooca.id/docs/inv-0042.pdf',
            invoiceUrlSuffix: 'inv-0042'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('wamid.HBgMSW52b2ljZU1vY2s=', $result['message_id']);

        $log = WhatsAppMessageLog::where('business_id', $business->id)
            ->where('type', 'invoice')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('6281311223344', $log->recipient_phone);
        $this->assertEquals('sent', $log->status);
    }
}
