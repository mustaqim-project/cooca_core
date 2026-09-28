<?php

declare(strict_types=1);

namespace Tests\Feature\SocialMedia;

use App\Domain\SocialMedia\SocialMediaService;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaPost;
use App\Models\SocialPostTarget;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class SocialMediaHardeningAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.meta_social.app_id', '987654321012345');
        Config::set('services.meta_social.app_secret', 'secret_meta_social_98765');
        Config::set('services.meta_social.webhook_verify_token', 'token_social_verify_test');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Helper to create a business and an owner user.
     */
    private function createMerchant(string $bizName = 'Toko Kopi Sejahtera'): array
    {
        $user = User::factory()->create([
            'name'              => 'Owner ' . $bizName,
            'email'             => Str::slug($bizName) . '@cooca.id',
            'email_verified_at' => now(),
        ]);

        $business = Business::create([
            'user_id'             => $user->id,
            'name'                => $bizName,
            'status'              => 'active',
            'currency'            => 'IDR',
            'bank_name'           => 'BCA',
            'bank_account_number' => '5432109876',
            'bank_account_holder' => 'Owner ' . $bizName,
        ]);

        BusinessMembership::create([
            'id'          => (string) Str::uuid(),
            'business_id' => $business->id,
            'user_id'     => $user->id,
            'role'        => 'owner',
        ]);

        $user->update(['active_business_id' => $business->id]);
        Context::setBusiness($business);

        return [$user, $business];
    }

    /**
     * Helper to create an active social media account for a business.
     */
    private function createAccount(Business $business, string $platform = 'facebook'): SocialMediaAccount
    {
        return SocialMediaAccount::create([
            'business_id'              => $business->id,
            'platform'                 => $platform,
            'provider'                 => $platform === 'tiktok' ? 'tiktok' : ($platform === 'linkedin' ? 'linkedin' : 'meta'),
            'account_id'               => 'acc_' . Str::random(10),
            'account_name'             => ucfirst($platform) . ' Official Store',
            'account_type'             => 'page',
            'access_token'             => 'token_' . Str::random(20),
            'status'                   => 'active',
            'token_expires_at'         => now()->addDays(60),
            'refresh_token_expires_at' => now()->addYear(),
        ]);
    }

    /**
     * Test 1.1: Anti-SSRF - Rejects insecure http:// scheme.
     */
    public function test_anti_ssrf_rejects_insecure_http_url(): void
    {
        [$owner, $business] = $this->createMerchant();
        $account = $this->createAccount($business, 'facebook');

        $payload = [
            'target_accounts' => [$account->id],
            'content'         => 'Promo Akhir Bulan Diskon 50%!',
            'media_url'       => 'http://example.com/insecure.jpg',
        ];

        $response = $this->actingAs($owner)->post(route('social-media.posts.store'), $payload);

        $response->assertSessionHas('error', 'URL media wajib menggunakan protokol aman https://');
        $this->assertDatabaseMissing('social_media_posts', [
            'business_id' => $business->id,
        ]);
    }

    /**
     * Test 1.1: Anti-SSRF - Rejects loopback addresses and localhost.
     */
    public function test_anti_ssrf_rejects_loopback_and_local_ip(): void
    {
        [$owner, $business] = $this->createMerchant();
        $account = $this->createAccount($business, 'facebook');

        // Test localhost
        $response1 = $this->actingAs($owner)->post(route('social-media.posts.store'), [
            'target_accounts' => [$account->id],
            'content'         => 'Test Konten 1',
            'media_url'       => 'https://localhost/exploit.jpg',
        ]);
        $response1->assertSessionHas('error');

        // Test 127.0.0.1
        $response2 = $this->actingAs($owner)->post(route('social-media.posts.store'), [
            'target_accounts' => [$account->id],
            'content'         => 'Test Konten 2',
            'media_url'       => 'https://127.0.0.1/exploit.jpg',
        ]);
        $response2->assertSessionHas('error');
    }

    /**
     * Test 1.1: Anti-SSRF - Rejects cloud metadata link-local IP (169.254.169.254).
     */
    public function test_anti_ssrf_rejects_cloud_metadata_service(): void
    {
        [$owner, $business] = $this->createMerchant();
        $account = $this->createAccount($business, 'facebook');

        $response = $this->actingAs($owner)->post(route('social-media.posts.store'), [
            'target_accounts' => [$account->id],
            'content'         => 'Eksploitasi Cloud Metadata',
            'media_url'       => 'https://169.254.169.254/latest/meta-data',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('social_media_posts', [
            'business_id' => $business->id,
        ]);
    }

    /**
     * Test 1.2: DOM XSS - Verify blade view does not contain raw addslashes in prepareReply.
     */
    public function test_inbox_blade_uses_safe_js_escaping_and_no_inner_html_injection(): void
    {
        $inboxViewPath = resource_path('views/app/social_media/inbox.blade.php');
        $content = file_get_contents($inboxViewPath);

        // Verify @js is used instead of raw addslashes
        $this->assertStringContainsString('@js($c->id)', $content);
        $this->assertStringNotContainsString('prepareReply(\'{{ $c->id }}\'', $content);

        // Verify innerHTML is removed from badge replacement
        $this->assertStringNotContainsString('badge.innerHTML =', $content);
        $this->assertStringContainsString('badge.replaceChildren()', $content);
    }

    /**
     * Test 1.3: Rate Limiting middleware attached on sensitive routes.
     */
    public function test_rate_limiting_middleware_configured_on_routes(): void
    {
        $routes = app('router')->getRoutes();

        $replyRoute = $routes->getByName('social-media.comments.reply');
        $this->assertNotNull($replyRoute);
        $this->assertContains('throttle:15,1', $replyRoute->gatherMiddleware());

        $syncRoute = $routes->getByName('social-media.insights.sync');
        $this->assertNotNull($syncRoute);
        $this->assertContains('throttle:10,1', $syncRoute->gatherMiddleware());
    }

    /**
     * Test 2.1: RBAC - User without social_media.view permission is blocked from accessing cockpit.
     */
    public function test_user_without_social_media_view_permission_is_blocked(): void
    {
        [$owner, $business] = $this->createMerchant();

        // Create a staff user with role without social_media permissions
        $staff = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $role = Role::create([
            'business_id' => $business->id,
            'name'        => 'Staff Gudang',
            'slug'        => 'warehouse_staff',
            'description' => 'Akses pergudangan saja',
        ]);

        BusinessMembership::create([
            'id'             => (string) Str::uuid(),
            'business_id'    => $business->id,
            'user_id'        => $staff->id,
            'role'           => 'warehouse_staff',
            'custom_role_id' => $role->id,
        ]);

        $staff->update(['active_business_id' => $business->id]);

        $response = $this->actingAs($staff)->get(route('social-media.index'));
        $this->assertTrue(in_array($response->status(), [302, 403], true));
    }

    /**
     * Test 2.1 & 2.2: Maker-Checker - Post created by owner is approved immediately.
     */
    public function test_post_created_by_owner_is_approved_with_audit_trail(): void
    {
        Queue::fake();

        [$owner, $business] = $this->createMerchant();
        $account = $this->createAccount($business, 'facebook');

        $mockService = Mockery::mock(SocialMediaService::class);
        $mockService->shouldReceive('publishPost')->once()->andReturnUsing(function ($b, $p) {
            $p->update([
                'status'           => 'published',
                'platform_post_id' => 'fb_post_9999',
                'published_at'     => now(),
            ]);
            return $p;
        });
        $this->app->instance(SocialMediaService::class, $mockService);

        $payload = [
            'target_accounts' => [$account->id],
            'content'         => 'Postingan Resmi Toko Diskon Awal Pekan #promo',
        ];

        $response = $this->actingAs($owner)->post(route('social-media.posts.store'), $payload);

        $response->assertRedirect(route('social-media.posts.index'));

        $this->assertDatabaseHas('social_media_posts', [
            'business_id'     => $business->id,
            'user_id'         => $owner->id,
            'approval_status' => 'approved',
            'reviewed_by'     => $owner->id,
        ]);

        $post = SocialMediaPost::where('business_id', $business->id)->latest()->first();
        $this->assertTrue($post->isApproved());
        $this->assertEquals($owner->id, $post->author->id);
    }

    /**
     * Test 2.3: Maker-Checker - Staff non-owner submission requires approval.
     */
    public function test_post_created_by_staff_triggers_maker_checker_pending_review(): void
    {
        Queue::fake();

        [$owner, $business] = $this->createMerchant();
        $account = $this->createAccount($business, 'facebook');

        // Create social_media permissions
        $viewPerm = Permission::firstOrCreate(
            ['slug' => 'social_media.view'],
            ['name' => 'Lihat Media Sosial', 'group' => 'social_media']
        );
        $managePerm = Permission::firstOrCreate(
            ['slug' => 'social_media.manage'],
            ['name' => 'Kelola Media Sosial', 'group' => 'social_media']
        );

        $role = Role::create([
            'business_id' => $business->id,
            'name'        => 'Marketing Staff',
            'slug'        => 'marketing_staff',
            'description' => 'Staff pengunggah konten',
        ]);
        $role->permissions()->attach([$viewPerm->id, $managePerm->id]);

        $staff = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        BusinessMembership::create([
            'id'             => (string) Str::uuid(),
            'business_id'    => $business->id,
            'user_id'        => $staff->id,
            'role'           => 'marketing_staff',
            'custom_role_id' => $role->id,
        ]);

        $staff->update(['active_business_id' => $business->id]);

        $payload = [
            'target_accounts' => [$account->id],
            'content'         => 'Konten draf dari staf promosi #diskon',
        ];

        $response = $this->actingAs($staff)->post(route('social-media.posts.store'), $payload);

        $response->assertRedirect(route('social-media.posts.index'));
        $response->assertSessionHas('warning');

        $this->assertDatabaseHas('social_media_posts', [
            'business_id'     => $business->id,
            'user_id'         => $staff->id,
            'approval_status' => 'pending_review',
            'status'          => 'pending_review',
            'reviewed_by'     => null,
        ]);

        $post = SocialMediaPost::where('business_id', $business->id)->latest()->first();
        $this->assertTrue($post->isPendingReview());
        $this->assertEquals('pending_review', $post->targets->first()->status);
    }

    /**
     * Test 2.3: Anti-Fraud Heuristic - Post with unregistered bank account triggers pending_review and risk flag.
     */
    public function test_post_with_unregistered_bank_account_is_flagged_with_risk(): void
    {
        [$owner, $business] = $this->createMerchant();
        $account = $this->createAccount($business, 'facebook');

        // Post with rogue bank account number (not 5432109876)
        $payload = [
            'target_accounts' => [$account->id],
            'content'         => 'Silakan transfer DP ke BCA 1234567890 an Penipu untuk amankan promo!',
        ];

        $response = $this->actingAs($owner)->post(route('social-media.posts.store'), $payload);

        $response->assertRedirect(route('social-media.posts.index'));
        $response->assertSessionHas('warning');

        $post = SocialMediaPost::where('business_id', $business->id)->latest()->first();
        $this->assertNotNull($post);
        $this->assertEquals('pending_review', $post->approval_status);
        $this->assertNotNull($post->risk_flags);
        $this->assertContains('unregistered_bank_account_detected', $post->risk_flags);
    }

    /**
     * Test 2.3: Maker-Checker Approval Flow - Owner approves pending post.
     */
    public function test_owner_can_approve_pending_post_and_dispatch(): void
    {
        Queue::fake();

        [$owner, $business] = $this->createMerchant();
        $account = $this->createAccount($business, 'facebook');

        $post = SocialMediaPost::create([
            'business_id'             => $business->id,
            'social_media_account_id' => $account->id,
            'platform'                => 'facebook',
            'content'                 => 'Postingan Menunggu Persetujuan',
            'status'                  => 'pending_review',
            'approval_status'         => 'pending_review',
        ]);

        $target = SocialPostTarget::create([
            'social_media_post_id'    => $post->id,
            'social_media_account_id' => $account->id,
            'provider'                => 'meta',
            'channel'                 => 'facebook',
            'content_type'            => 'feed',
            'status'                  => 'pending_review',
        ]);

        $mockService = Mockery::mock(SocialMediaService::class);
        $mockService->shouldReceive('publishPost')->once()->andReturnUsing(function ($b, $p) {
            $p->update([
                'status'           => 'published',
                'platform_post_id' => 'fb_post_approved_100',
                'published_at'     => now(),
            ]);
            return $p;
        });
        $this->app->instance(SocialMediaService::class, $mockService);

        $response = $this->actingAs($owner)->post(route('social-media.posts.approve', $post));

        $response->assertRedirect(route('social-media.posts.index'));
        $response->assertSessionHas('success');

        $post->refresh();
        $this->assertEquals('approved', $post->approval_status);
        $this->assertEquals($owner->id, $post->reviewed_by);
        $this->assertNotNull($post->reviewed_at);
        $this->assertEquals('published', $post->status);
    }

    /**
     * Test 2.3: Maker-Checker Rejection Flow - Owner rejects pending post with reason.
     */
    public function test_owner_can_reject_pending_post_with_reason(): void
    {
        [$owner, $business] = $this->createMerchant();
        $account = $this->createAccount($business, 'facebook');

        $post = SocialMediaPost::create([
            'business_id'             => $business->id,
            'social_media_account_id' => $account->id,
            'platform'                => 'facebook',
            'content'                 => 'Postingan yang tidak disetujui',
            'status'                  => 'pending_review',
            'approval_status'         => 'pending_review',
        ]);

        $target = SocialPostTarget::create([
            'social_media_post_id'    => $post->id,
            'social_media_account_id' => $account->id,
            'provider'                => 'meta',
            'channel'                 => 'facebook',
            'content_type'            => 'feed',
            'status'                  => 'pending_review',
        ]);

        $response = $this->actingAs($owner)->post(route('social-media.posts.reject', $post), [
            'reason' => 'Materi visual tidak sesuai standar branding toko.',
        ]);

        $response->assertRedirect(route('social-media.posts.index'));
        $response->assertSessionHas('info');

        $post->refresh();
        $this->assertEquals('rejected', $post->approval_status);
        $this->assertEquals('rejected', $post->status);
        $this->assertEquals('Materi visual tidak sesuai standar branding toko.', $post->rejection_reason);
        $this->assertEquals('cancelled', $target->fresh()->status);
    }

    /**
     * Test 5.1.1: Anti-SSRF - Rejects RFC1918 private IPv4 ranges (10.x, 172.16.x, 192.168.x).
     */
    public function test_anti_ssrf_rejects_rfc1918_private_ip_ranges(): void
    {
        [$owner, $business] = $this->createMerchant();
        $account = $this->createAccount($business, 'facebook');

        $privateIps = [
            'https://10.0.0.1/internal-image.png',
            'https://172.16.0.10/asset.jpg',
            'https://192.168.1.1/router-backup.png',
        ];

        foreach ($privateIps as $ipUrl) {
            $response = $this->actingAs($owner)->post(route('social-media.posts.store'), [
                'target_accounts' => [$account->id],
                'content'         => 'Test SSRF Private IP Range',
                'media_url'       => $ipUrl,
            ]);

            $response->assertSessionHas('error');
        }

        $this->assertDatabaseMissing('social_media_posts', [
            'business_id' => $business->id,
            'content'     => 'Test SSRF Private IP Range',
        ]);
    }

    /**
     * Test 5.1.2: Post with registered official bank account is not flagged with risk.
     */
    public function test_post_with_registered_business_bank_account_is_not_flagged_with_risk(): void
    {
        [$owner, $business] = $this->createMerchant();
        $account = $this->createAccount($business, 'facebook');

        // Business bank account is 5432109876 (from createMerchant)
        $payload = [
            'target_accounts' => [$account->id],
            'content'         => 'Pembayaran resmi toko melalui transfer rekening BCA 5432109876 an Owner Toko Kopi Sejahtera.',
        ];

        $response = $this->actingAs($owner)->post(route('social-media.posts.store'), $payload);

        $response->assertRedirect(route('social-media.posts.index'));

        $post = SocialMediaPost::where('business_id', $business->id)->latest()->first();
        $this->assertNotNull($post);
        $this->assertEquals('approved', $post->approval_status);
        $this->assertTrue(empty($post->risk_flags));
    }

    /**
     * Test 5.1.3: Multi-tenant isolation - Tenant B cannot approve, reject, or view Tenant A post.
     */
    public function test_multi_tenant_isolation_prevents_tenant_b_from_approving_or_rejecting_tenant_a_post(): void
    {
        [$ownerA, $businessA] = $this->createMerchant('Merchant Alfa');
        [$ownerB, $businessB] = $this->createMerchant('Merchant Bravo');

        $accountA = $this->createAccount($businessA, 'facebook');

        $postA = SocialMediaPost::create([
            'business_id'             => $businessA->id,
            'social_media_account_id' => $accountA->id,
            'platform'                => 'facebook',
            'content'                 => 'Postingan Khusus Merchant Alfa',
            'status'                  => 'pending_review',
            'approval_status'         => 'pending_review',
        ]);

        // Tenant B attempts to approve Post A
        $responseApprove = $this->actingAs($ownerB)->post(route('social-media.posts.approve', $postA));
        $responseApprove->assertStatus(404);

        // Tenant B attempts to reject Post A
        $responseReject = $this->actingAs($ownerB)->post(route('social-media.posts.reject', $postA), [
            'reason' => 'Eksploitasi IDOR / BOLA antar tenant.',
        ]);
        $responseReject->assertStatus(404);

        // Verify Post A remains untouched
        $postA->refresh();
        $this->assertEquals('pending_review', $postA->approval_status);
        $this->assertNull($postA->reviewed_by);
    }

    /**
     * Test 5.1.4: RBAC & Maker-Checker - Staff without manage permission cannot approve or reject posts.
     */
    public function test_staff_without_manage_permission_cannot_approve_or_reject_posts(): void
    {
        [$owner, $business] = $this->createMerchant();
        $account = $this->createAccount($business, 'facebook');

        $post = SocialMediaPost::create([
            'business_id'             => $business->id,
            'social_media_account_id' => $account->id,
            'platform'                => 'facebook',
            'content'                 => 'Postingan Menunggu Approval Manager',
            'status'                  => 'pending_review',
            'approval_status'         => 'pending_review',
        ]);

        $cashier = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $cashierRole = Role::create([
            'business_id' => $business->id,
            'name'        => 'Kasir',
            'slug'        => 'cashier',
            'description' => 'Akses kasir POS saja',
        ]);

        BusinessMembership::create([
            'id'             => (string) Str::uuid(),
            'business_id'    => $business->id,
            'user_id'        => $cashier->id,
            'role'           => 'cashier',
            'custom_role_id' => $cashierRole->id,
        ]);

        $cashier->update(['active_business_id' => $business->id]);

        // Cashier attempts to approve post
        $responseApprove = $this->actingAs($cashier)->post(route('social-media.posts.approve', $post));
        $this->assertTrue(in_array($responseApprove->status(), [302, 403], true));

        // Cashier attempts to reject post
        $responseReject = $this->actingAs($cashier)->post(route('social-media.posts.reject', $post), [
            'reason' => 'Unauthorized rejection',
        ]);
        $this->assertTrue(in_array($responseReject->status(), [302, 403], true));

        $post->refresh();
        $this->assertEquals('pending_review', $post->approval_status);
    }

    /**
     * Test 5.1.5: Blade view renders Bento XXL, guardrails, video inspector, and AppAlert.
     */
    public function test_posts_index_view_renders_bento_xxl_guardrails_and_app_alert(): void
    {
        [$owner, $business] = $this->createMerchant();
        $account = $this->createAccount($business, 'facebook');

        // Create a post in pending_review so the maker-checker action buttons with AppAlert render
        SocialMediaPost::create([
            'business_id'             => $business->id,
            'social_media_account_id' => $account->id,
            'platform'                => 'facebook',
            'content'                 => 'Postingan Menunggu Review untuk UI Test',
            'status'                  => 'pending_review',
            'approval_status'         => 'pending_review',
        ]);

        $response = $this->actingAs($owner)->get(route('social-media.posts.index'));
        $response->assertStatus(200);

        // Assert Bento XXL modal sheet container class exists
        $response->assertSee('max-w-5xl xl:max-w-6xl', false);

        // Assert Video aspect ratio inspector properties in Alpine component
        $response->assertSee('videoRatio', false);
        $response->assertSee('isLandscapeVideo', false);

        // Assert Quiet hours check
        $response->assertSee('isQuietHours', false);

        // Assert AppAlert submission handlers
        $response->assertSee('AppAlert.confirmSubmit', false);
    }

    /**
     * Test 5.1.6: Inbox and Insights views render cleanly without browser native alerts.
     */
    public function test_inbox_and_insights_views_render_without_native_dialogs(): void
    {
        [$owner, $business] = $this->createMerchant();

        // Inbox View
        $inboxResponse = $this->actingAs($owner)->get(route('social-media.inbox.index'));
        $inboxResponse->assertStatus(200);
        $inboxResponse->assertSee('AppAlert.success', false);
        $inboxResponse->assertDontSee('alert(', false);
        $inboxResponse->assertDontSee('confirm(', false);

        // Insights View
        $insightsResponse = $this->actingAs($owner)->get(route('social-media.insights.index'));
        $insightsResponse->assertStatus(200);
        $insightsResponse->assertSee('AppAlert.success', false);
        $insightsResponse->assertDontSee('alert(', false);
        $insightsResponse->assertDontSee('confirm(', false);
    }
}
