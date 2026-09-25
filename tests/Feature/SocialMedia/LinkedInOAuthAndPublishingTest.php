<?php

declare(strict_types=1);

namespace Tests\Feature\SocialMedia;

use App\Domain\SocialMedia\Clients\LinkedInClient;
use App\Domain\SocialMedia\Providers\LinkedInProvider;
use App\Domain\SocialMedia\SocialMediaManager;
use App\Jobs\SocialMedia\PublishSocialMediaTargetJob;
use App\Models\Business;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaPost;
use App\Models\SocialPostTarget;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class LinkedInOAuthAndPublishingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.linkedin.client_id', 'mock_linkedin_client_id');
        Config::set('services.linkedin.client_secret', 'mock_linkedin_client_secret');
        Config::set('services.linkedin.redirect_uri', 'https://cooca.id/social-media/linkedin/callback');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeMerchant(string $bizName = 'Toko LinkedIn Sukses'): array
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
     * Test guest cannot initiate LinkedIn OAuth.
     */
    public function test_guest_cannot_initiate_linkedin_oauth(): void
    {
        $response = $this->get(route('social-media.linkedin.connect'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test merchant can initiate LinkedIn OAuth flow with CSRF state stored in session.
     */
    public function test_merchant_can_initiate_linkedin_oauth(): void
    {
        [$user, $business] = $this->makeMerchant();

        $response = $this->actingAs($user)->get(route('social-media.linkedin.connect'));

        $response->assertRedirect();
        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('https://www.linkedin.com/oauth/v2/authorization', $redirectUrl);
        $this->assertStringContainsString('client_id=mock_linkedin_client_id', $redirectUrl);
        $this->assertStringContainsString('w_member_social', $redirectUrl);
        $this->assertStringContainsString('openid', $redirectUrl);

        $this->assertTrue(session()->has('linkedin_oauth_state'));
        $state = session()->get('linkedin_oauth_state');
        $this->assertNotEmpty($state);
        $this->assertStringContainsString('state=' . $state, $redirectUrl);
    }

    /**
     * Test LinkedIn OAuth callback rejects missing or invalid state (CSRF mitigation).
     */
    public function test_linkedin_callback_rejects_invalid_csrf_state(): void
    {
        [$user, $business] = $this->makeMerchant();

        session()->put('linkedin_oauth_state', 'expected_secure_state_123');

        $response = $this->actingAs($user)->get(route('social-media.linkedin.callback', [
            'code'  => 'mock_authorization_code',
            'state' => 'forged_csrf_token',
        ]));

        $response->assertRedirect(route('social-media.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('social_media_accounts', 0);
    }

    /**
     * Test LinkedIn OAuth callback handles user cancellation.
     */
    public function test_linkedin_callback_handles_user_cancellation(): void
    {
        [$user, $business] = $this->makeMerchant();

        session()->put('linkedin_oauth_state', 'valid_state_123');

        $response = $this->actingAs($user)->get(route('social-media.linkedin.callback', [
            'error'             => 'user_cancelled_login',
            'error_description' => 'User cancelled OAuth login',
            'state'             => 'valid_state_123',
        ]));

        $response->assertRedirect(route('social-media.index'));
        $response->assertSessionHas('error');
    }

    /**
     * Test LinkedIn OAuth callback successfully exchanges authorization code and saves account.
     */
    public function test_linkedin_callback_exchanges_code_and_creates_account(): void
    {
        [$user, $business] = $this->makeMerchant();

        $state = 'session_state_xyz_123';
        session()->put('linkedin_oauth_state', $state);

        $mockClient = Mockery::mock(LinkedInClient::class);
        $mockClient->shouldReceive('exchangeCodeForToken')
            ->once()
            ->with('valid_auth_code', route('social-media.linkedin.callback'))
            ->andReturn([
                'access_token' => 'mock_linkedin_access_token_123',
                'expires_in'   => 5184000, // 60 days
                'scope'        => 'openid profile email w_member_social',
            ]);

        $mockClient->shouldReceive('getProfile')
            ->once()
            ->with('mock_linkedin_access_token_123')
            ->andReturn([
                'sub'     => 'li_person_abc999',
                'name'    => 'Budi Pratama LinkedIn',
                'email'   => 'budi.linkedin@cooca.id',
                'picture' => 'https://media.licdn.com/dms/image/v2/user_pic.jpg',
            ]);

        $provider = new LinkedInProvider($mockClient);
        $this->app->instance(LinkedInClient::class, $mockClient);
        $this->app->instance(LinkedInProvider::class, $provider);

        $response = $this->actingAs($user)->get(route('social-media.linkedin.callback', [
            'code'  => 'valid_auth_code',
            'state' => $state,
        ]));

        $response->assertRedirect(route('social-media.index'));
        $response->assertSessionHas('success');

        $account = SocialMediaAccount::where('business_id', $business->id)
            ->where('platform', 'linkedin')
            ->where('account_id', 'li_person_abc999')
            ->first();

        $this->assertNotNull($account);
        $this->assertSame('Budi Pratama LinkedIn', $account->account_name);
        $this->assertSame('budi.linkedin@cooca.id', $account->username);
        $this->assertSame('active', $account->status);
        $this->assertTrue($account->isLinkedIn());
        $this->assertSame('mock_linkedin_access_token_123', $account->access_token);
    }

    /**
     * Test publishing a direct UGC text post to LinkedIn.
     */
    public function test_linkedin_direct_post_publishing(): void
    {
        [$user, $business] = $this->makeMerchant();

        $account = SocialMediaAccount::create([
            'business_id'      => $business->id,
            'provider'         => 'linkedin',
            'platform'         => 'linkedin',
            'account_id'       => 'li_person_author_123',
            'account_name'     => 'Cooca Corporate LinkedIn',
            'username'         => 'cooca.corporate',
            'access_token'     => 'mock_author_token',
            'status'           => 'active',
            'token_expires_at' => now()->addDays(60),
        ]);

        $mockClient = Mockery::mock(LinkedInClient::class);
        $mockClient->shouldReceive('isConfigured')->andReturn(true);
        $mockClient->shouldReceive('publishPost')
            ->once()
            ->with(
                'li_person_author_123',
                'mock_author_token',
                'Halo rekan profesional LinkedIn! #bisnis #umkm',
                Mockery::any()
            )
            ->andReturn([
                'id' => 'urn:li:ugcPost:7123456789012345678',
            ]);

        $provider = new LinkedInProvider($mockClient);
        $this->app->instance(LinkedInClient::class, $mockClient);
        $this->app->instance(LinkedInProvider::class, $provider);

        $response = $this->actingAs($user)->post(route('social-media.posts.store'), [
            'target_accounts' => [$account->id],
            'media_format'    => 'text',
            'content'         => 'Halo rekan profesional LinkedIn! #bisnis #umkm',
            'schedule_mode'   => 'all_now',
        ]);

        $response->assertRedirect(route('social-media.posts.index'));
        $response->assertSessionHas('success');

        $post = SocialMediaPost::where('business_id', $business->id)->first();
        $this->assertNotNull($post);

        $target = SocialPostTarget::where('social_media_post_id', $post->id)->first();
        $this->assertNotNull($target);
        $this->assertSame('linkedin', $target->channel);
        $this->assertSame('published', $target->status);
        $this->assertSame('urn:li:ugcPost:7123456789012345678', $target->platform_post_id);
        $this->assertNotNull($target->published_at);
    }

    /**
     * Test scheduled post creation and background execution for LinkedIn.
     */
    public function test_linkedin_scheduled_post_cron_execution(): void
    {
        [$user, $business] = $this->makeMerchant();

        $account = SocialMediaAccount::create([
            'business_id'      => $business->id,
            'provider'         => 'linkedin',
            'platform'         => 'linkedin',
            'account_id'       => 'li_person_author_scheduled',
            'account_name'     => 'Cooca Scheduled LinkedIn',
            'username'         => 'cooca.scheduled',
            'access_token'     => 'mock_scheduled_token',
            'status'           => 'active',
            'token_expires_at' => now()->addDays(60),
        ]);

        $scheduledTime = Carbon::now()->addHours(2)->format('Y-m-d\TH:i');

        $response = $this->actingAs($user)->post(route('social-media.posts.store'), [
            'target_accounts'      => [$account->id],
            'media_format'         => 'text',
            'content'              => 'Konten terjadwal untuk jaringan LinkedIn. #jadwal #cooca',
            'schedule_mode'        => 'all_same',
            'scheduled_at'         => $scheduledTime,
        ]);

        $response->assertRedirect(route('social-media.posts.index'));

        $post = SocialMediaPost::where('business_id', $business->id)->first();
        $this->assertNotNull($post);
        $this->assertSame('scheduled', $post->status);

        $target = SocialPostTarget::where('social_media_post_id', $post->id)->first();
        $this->assertNotNull($target);
        $this->assertSame('scheduled', $target->status);
        $this->assertNull($target->published_at);

        // Now simulate cron triggering PublishSocialMediaTargetJob
        $mockClient = Mockery::mock(LinkedInClient::class);
        $mockClient->shouldReceive('isConfigured')->andReturn(true);
        $mockClient->shouldReceive('publishPost')
            ->once()
            ->with(
                'li_person_author_scheduled',
                'mock_scheduled_token',
                'Konten terjadwal untuk jaringan LinkedIn. #jadwal #cooca',
                Mockery::any()
            )
            ->andReturn([
                'id' => 'urn:li:ugcPost:9876543210987654321',
            ]);

        $provider = new LinkedInProvider($mockClient);
        $this->app->instance(LinkedInClient::class, $mockClient);
        $this->app->instance(LinkedInProvider::class, $provider);

        // Run the Job
        $job = new PublishSocialMediaTargetJob($target->id);
        $job->handle(app(SocialMediaManager::class));

        $target->refresh();
        $this->assertSame('published', $target->status);
        $this->assertSame('urn:li:ugcPost:9876543210987654321', $target->platform_post_id);
        $this->assertNotNull($target->published_at);

        $post->refresh();
        $this->assertSame('published', $post->status);
    }

    /**
     * Test tenant isolation prevents cross-business LinkedIn posting.
     */
    public function test_tenant_isolation_prevents_unauthorized_linkedin_posting(): void
    {
        [$userA, $businessA] = $this->makeMerchant('Toko Tenant A');
        [$userB, $businessB] = $this->makeMerchant('Toko Tenant B');

        $accountB = SocialMediaAccount::create([
            'business_id'      => $businessB->id,
            'provider'         => 'linkedin',
            'platform'         => 'linkedin',
            'account_id'       => 'li_tenant_b',
            'account_name'     => 'LinkedIn Toko B',
            'access_token'     => 'token_b',
            'status'           => 'active',
        ]);

        // User A tries to post to User B's LinkedIn account
        $response = $this->actingAs($userA)->post(route('social-media.posts.store'), [
            'target_accounts' => [$accountB->id],
            'media_format'    => 'text',
            'content'         => 'Percobaan posting lintas tenant ilegal',
            'schedule_mode'   => 'all_now',
        ]);

        $response->assertNotFound();
        // Post targets should not create target for Account B under Business A
        $this->assertDatabaseMissing('social_post_targets', [
            'social_media_account_id' => $accountB->id,
        ]);
    }
}

