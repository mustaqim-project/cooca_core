<?php

declare(strict_types=1);

namespace Tests\Feature\SocialMedia;

use App\Domain\SocialMedia\Clients\MetaSocialMediaClient;
use App\Domain\SocialMedia\SocialMediaService;
use App\Models\Admin;
use App\Models\Business;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaComment;
use App\Models\SocialMediaPost;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class SocialMediaFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Configure default mock settings
        Config::set('services.meta_social.app_id', '987654321012345');
        Config::set('services.meta_social.app_secret', 'secret_meta_social_98765');
        Config::set('services.meta_social.webhook_verify_token', 'token_social_verify_test');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeAdmin(array $override = []): Admin
    {
        return Admin::factory()->create(array_merge([
            'name'      => 'Super Admin Cooca',
            'email'     => 'admin@cooca.id',
            'password'  => Hash::make('password123'),
            'role'      => 'super_admin',
            'is_active' => true,
        ], $override));
    }

    private function makeMerchant(string $bizName = 'Kedai Kopi Nusantara'): array
    {
        $user = User::factory()->create([
            'name'              => 'Owner ' . $bizName,
            'email'             => Str::slug($bizName) . '@cooca.id',
            'email_verified_at' => now(),
        ]);

        $business = Business::create([
            'user_id'  => $user->id,
            'name'     => $bizName,
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
     * Test admin authentication guard.
     */
    public function test_guest_cannot_access_admin_social_media(): void
    {
        $response = $this->get(route('admin.social-media.index'));

        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Test admin panel view and saving configuration.
     */
    public function test_admin_can_view_social_media_panel_and_save_config(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.social-media.index'));
        $response->assertOk();
        $response->assertSee('Media Sosial Platform Admin Center', false);
        $response->assertSee('Panduan Meta App Review', false);

        // Update Meta App configuration
        $configPayload = [
            'app_id'               => '112233445566778',
            'app_secret'           => 'super_secret_app_key_meta',
            'webhook_verify_token' => 'custom_token_webhook_123',
            'graph_version'        => 'v21.0',
            'graph_url'            => 'https://graph.facebook.com',
        ];

        $postRes = $this->actingAs($admin, 'admin')
            ->post(route('admin.social-media.config'), $configPayload);

        $postRes->assertRedirect(route('admin.social-media.index', ['tab' => 'settings']));
        $postRes->assertSessionHas('success');

        $this->assertDatabaseHas('system_settings', [
            'key' => 'social_media_app_id',
        ]);
    }

    /**
     * Test merchant authentication guard.
     */
    public function test_guest_cannot_access_merchant_social_media(): void
    {
        $response = $this->get(route('social-media.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test merchant can view social media cockpit.
     */
    public function test_merchant_can_view_social_media_cockpit(): void
    {
        [$user, $business] = $this->makeMerchant();

        $response = $this->actingAs($user)->get(route('social-media.index'));

        $response->assertOk();
        $response->assertSee('Pengelolaan Media Sosial &amp; Konten', false);
        $response->assertSee('Akun Media Sosial Toko', false);
        $response->assertSee('Hubungkan Akun Media Sosial (Metode 1-Klik)');
    }

    /**
     * Test merchant exchanging OAuth token and connecting Facebook & Instagram assets.
     */
    public function test_merchant_can_exchange_oauth_token_and_connect_pages(): void
    {
        [$user, $business] = $this->makeMerchant();

        // Mock Meta Client
        $mockClient = Mockery::mock(MetaSocialMediaClient::class);
        $mockClient->shouldReceive('exchangeTokenForLongLived')
            ->with('short_lived_user_token_abc')
            ->once()
            ->andReturn([
                'access_token' => 'long_lived_user_token_xyz',
                'token_type'   => 'bearer',
                'expires_in'   => 5184000,
            ]);

        $mockClient->shouldReceive('getManageablePages')
            ->with('long_lived_user_token_xyz')
            ->once()
            ->andReturn([
                [
                    'id'           => '100100100',
                    'name'         => 'Kedai Kopi Fanpage',
                    'access_token' => 'page_access_token_permanent_1',
                    'category'     => 'Coffee Shop',
                    'instagram_business_account' => [
                        'id'       => '178414000000001',
                        'username' => 'kedaikopi.id',
                        'name'     => 'Kedai Kopi Nusantara Official',
                    ],
                ],
            ]);

        // Bind mock to SocialMediaService
        $service = new SocialMediaService($mockClient);
        $this->app->instance(SocialMediaService::class, $service);

        $response = $this->actingAs($user)
            ->postJson(route('social-media.exchange-token'), [
                'access_token' => 'short_lived_user_token_abc',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'count'   => 2, // 1 FB Page + 1 IG Account
        ]);

        // Assert database records created with tenant isolation
        $this->assertDatabaseHas('social_media_accounts', [
            'business_id'  => $business->id,
            'platform'     => 'facebook',
            'account_id'   => '100100100',
            'account_name' => 'Kedai Kopi Fanpage',
            'status'       => 'active',
        ]);

        $this->assertDatabaseHas('social_media_accounts', [
            'business_id'  => $business->id,
            'platform'     => 'instagram',
            'account_id'   => '178414000000001',
            'account_name' => 'Kedai Kopi Nusantara Official',
            'status'       => 'active',
        ]);
    }

    /**
     * Test merchant publishing a new post immediately.
     */
    public function test_merchant_can_create_and_publish_post(): void
    {
        [$user, $business] = $this->makeMerchant();

        $account = SocialMediaAccount::create([
            'business_id'  => $business->id,
            'platform'     => 'facebook',
            'account_id'   => 'fb_page_12345',
            'account_name' => 'Kedai Kopi FB',
            'access_token' => 'valid_token_sample',
            'status'       => 'active',
        ]);

        $mockClient = Mockery::mock(MetaSocialMediaClient::class);
        $mockClient->shouldReceive('publishFacebookPost')
            ->once()
            ->with(
                'fb_page_12345',
                'valid_token_sample',
                'Promo Spesial Kopi Kenangan diskon 50% hari ini!',
                null,
                null
            )
            ->andReturn(['id' => 'fb_page_12345_post_9999']);

        $service = new SocialMediaService($mockClient);
        $this->app->instance(SocialMediaService::class, $service);

        $response = $this->actingAs($user)
            ->post(route('social-media.posts.store'), [
                'social_media_account_id' => $account->id,
                'content'                 => 'Promo Spesial Kopi Kenangan diskon 50% hari ini!',
            ]);

        $response->assertRedirect(route('social-media.posts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('social_media_posts', [
            'business_id'             => $business->id,
            'social_media_account_id' => $account->id,
            'status'                  => 'published',
            'platform_post_id'        => 'fb_page_12345_post_9999',
        ]);
    }

    /**
     * Test merchant scheduling a post for future release.
     */
    public function test_merchant_can_schedule_post(): void
    {
        [$user, $business] = $this->makeMerchant();

        $account = SocialMediaAccount::create([
            'business_id'  => $business->id,
            'platform'     => 'facebook',
            'account_id'   => 'fb_page_12345',
            'account_name' => 'Kedai Kopi FB',
            'access_token' => 'valid_token_sample',
            'status'       => 'active',
        ]);

        $scheduleTime = now()->addDays(2)->format('Y-m-d H:i:s');

        $response = $this->actingAs($user)
            ->post(route('social-media.posts.store'), [
                'social_media_account_id' => $account->id,
                'content'                 => 'Postingan terjadwal promo akhir pekan',
                'scheduled_at'            => $scheduleTime,
            ]);

        $response->assertRedirect(route('social-media.posts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('social_media_posts', [
            'business_id' => $business->id,
            'status'      => 'scheduled',
        ]);
    }

    /**
     * Test centralized Webhook verification challenge (GET).
     */
    public function test_central_webhook_verification_challenge(): void
    {
        // Successful challenge with correct verify token
        $response = $this->get('/api/v1/social-media/meta/webhook?' . http_build_query([
            'hub_mode'         => 'subscribe',
            'hub_verify_token' => 'token_social_verify_test',
            'hub_challenge'    => '9876543210',
        ]));

        $response->assertOk();
        $this->assertEquals('9876543210', $response->getContent());

        // Failed challenge with wrong verify token
        $failResponse = $this->get('/api/v1/social-media/meta/webhook?' . http_build_query([
            'hub_mode'         => 'subscribe',
            'hub_verify_token' => 'invalid_token_xyz',
            'hub_challenge'    => '9876543210',
        ]));

        $failResponse->assertForbidden();
    }

    /**
     * Test centralized Webhook incoming comment event routed to the right merchant asset.
     */
    public function test_central_webhook_incoming_comment_event_routes_to_correct_merchant(): void
    {
        [$userA, $businessA] = $this->makeMerchant('Toko A');
        [$userB, $businessB] = $this->makeMerchant('Toko B');

        $accA = SocialMediaAccount::create([
            'business_id'  => $businessA->id,
            'platform'     => 'facebook',
            'account_id'   => 'page_asset_A_111',
            'account_name' => 'Toko A FB',
            'access_token' => 'token_A',
            'status'       => 'active',
        ]);

        $accB = SocialMediaAccount::create([
            'business_id'  => $businessB->id,
            'platform'     => 'facebook',
            'account_id'   => 'page_asset_B_222',
            'account_name' => 'Toko B FB',
            'access_token' => 'token_B',
            'status'       => 'active',
        ]);

        $payload = [
            'object' => 'page',
            'entry'  => [
                [
                    'id'      => 'page_asset_A_111',
                    'time'    => time(),
                    'changes' => [
                        [
                            'field' => 'feed',
                            'value' => [
                                'item'         => 'comment',
                                'comment_id'   => 'cmt_fb_998877',
                                'post_id'      => 'post_123_456',
                                'sender_id'    => 'user_buyer_1',
                                'sender_name'  => 'Budi Santoso',
                                'message'      => 'Halo kak, produk ini masih ready stock?',
                                'created_time' => time(),
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $rawBody = json_encode($payload);
        $signature = 'sha256=' . hash_hmac('sha256', $rawBody, 'secret_meta_social_98765');

        $response = $this->call(
            'POST',
            '/api/v1/social-media/meta/webhook',
            [],
            [],
            [],
            [
                'CONTENT_TYPE'             => 'application/json',
                'HTTP_X_HUB_SIGNATURE_256' => $signature,
            ],
            $rawBody
        );

        $response->assertOk();
        $response->assertJson(['status' => 'EVENT_RECEIVED']);

        // Verify comment is saved and correctly routed to Business A (NOT Business B)
        $this->assertDatabaseHas('social_media_comments', [
            'business_id'             => $businessA->id,
            'social_media_account_id' => $accA->id,
            'platform_comment_id'     => 'cmt_fb_998877',
            'from_name'               => 'Budi Santoso',
            'status'                  => 'unread',
        ]);

        $this->assertDatabaseMissing('social_media_comments', [
            'business_id'         => $businessB->id,
            'platform_comment_id' => 'cmt_fb_998877',
        ]);
    }

    /**
     * Test merchant replying to customer comment.
     */
    public function test_merchant_can_reply_to_comment(): void
    {
        [$user, $business] = $this->makeMerchant();

        $account = SocialMediaAccount::create([
            'business_id'  => $business->id,
            'platform'     => 'facebook',
            'account_id'   => 'page_asset_A_111',
            'account_name' => 'Toko A FB',
            'access_token' => 'token_A',
            'status'       => 'active',
        ]);

        $comment = SocialMediaComment::create([
            'business_id'             => $business->id,
            'social_media_account_id' => $account->id,
            'platform'                => 'facebook',
            'platform_comment_id'     => 'cmt_123456',
            'platform_post_id'        => 'post_test',
            'from_id'                 => 'buyer_1',
            'from_name'               => 'Ahmad',
            'message'                 => 'Bisa kirim hari ini?',
            'status'                  => 'unread',
        ]);

        $mockClient = Mockery::mock(MetaSocialMediaClient::class);
        $mockClient->shouldReceive('replyComment')
            ->once()
            ->with('facebook', 'cmt_123456', 'token_A', 'Halo Ahmad, bisa dikirim hari ini ya!')
            ->andReturn(['id' => 'cmt_reply_7788']);

        $service = new SocialMediaService($mockClient);
        $this->app->instance(SocialMediaService::class, $service);

        $response = $this->actingAs($user)
            ->postJson(route('social-media.comments.reply', $comment->id), [
                'message' => 'Halo Ahmad, bisa dikirim hari ini ya!',
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        // Assert original comment is now replied
        $this->assertEquals('replied', $comment->fresh()->status);

        // Assert outgoing reply comment record is created
        $this->assertDatabaseHas('social_media_comments', [
            'business_id'         => $business->id,
            'platform_comment_id' => 'cmt_reply_7788',
            'is_from_page'        => true,
        ]);
    }

    /**
     * Test strict tenant isolation between merchants.
     */
    public function test_strict_tenant_isolation(): void
    {
        [$userA, $businessA] = $this->makeMerchant('Merchant Alpha');
        [$userB, $businessB] = $this->makeMerchant('Merchant Beta');

        $accountA = SocialMediaAccount::create([
            'business_id'  => $businessA->id,
            'platform'     => 'facebook',
            'account_id'   => 'asset_alpha_1',
            'account_name' => 'Alpha Official',
            'access_token' => 'token_alpha',
            'status'       => 'active',
        ]);

        $postA = SocialMediaPost::create([
            'business_id'             => $businessA->id,
            'social_media_account_id' => $accountA->id,
            'platform'                => 'facebook',
            'content'                 => 'Postingan Rahasia Merchant Alpha',
            'status'                  => 'published',
        ]);

        // Merchant B tries to create post referencing Merchant A's account
        $response = $this->actingAs($userB)
            ->post(route('social-media.posts.store'), [
                'social_media_account_id' => $accountA->id,
                'content'                 => 'Mencoba membajak akun Alpha!',
            ]);

        $response->assertStatus(404);

        // Merchant B views posts page -> should NOT see Alpha's post
        $postsRes = $this->actingAs($userB)->get(route('social-media.posts.index'));
        $postsRes->assertOk();
        $postsRes->assertDontSee('Postingan Rahasia Merchant Alpha');

        // Merchant B tries to disconnect Alpha's account -> should fail
        $disconnectRes = $this->actingAs($userB)->postJson(route('social-media.disconnect'), [
            'account_id' => $accountA->id,
        ]);
        $disconnectRes->assertJson(['success' => false]);
        $this->assertEquals('active', $accountA->fresh()->status);
    }
}
