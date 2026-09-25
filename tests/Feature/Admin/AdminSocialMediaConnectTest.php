<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Domain\SocialMedia\Clients\LinkedInClient;
use App\Domain\SocialMedia\Providers\LinkedInProvider;
use App\Domain\SocialMedia\SocialMediaManager;
use App\Models\Admin;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaPost;
use App\Models\SocialPostTarget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class AdminSocialMediaConnectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.linkedin.client_id', 'admin_mock_linkedin_client_id');
        Config::set('services.linkedin.client_secret', 'admin_mock_linkedin_client_secret');
        Config::set('services.linkedin.redirect_uri', 'https://cooca.id/social-media/linkedin/callback');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeAdmin(): Admin
    {
        return Admin::create([
            'name'     => 'Super Administrator',
            'email'    => 'admin@cooca.id',
            'password' => bcrypt('password123'),
        ]);
    }

    /**
     * Test unauthenticated guest cannot access admin social media connect routes.
     */
    public function test_guest_cannot_access_admin_social_media_connect(): void
    {
        $response = $this->get(route('admin.social-media.linkedin.connect'));
        $response->assertRedirect(route('admin.login'));

        $response2 = $this->get(route('admin.social-media.tiktok.connect'));
        $response2->assertRedirect(route('admin.login'));
    }

    /**
     * Test admin can view social media index with platform accounts cockpit.
     */
    public function test_admin_can_view_social_media_index(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.social-media.index'));

        $response->assertOk();
        $response->assertSee('Media Sosial Platform Admin Center');
        $response->assertSee('Status Koneksi Saluran Resmi Platform');
        $response->assertSee('LinkedIn Official');
        $response->assertSee('TikTok Official');
    }

    /**
     * Test admin can initiate LinkedIn OAuth authorization flow.
     */
    public function test_admin_can_initiate_linkedin_oauth(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.social-media.linkedin.connect'));

        $response->assertRedirect();
        $redirectUrl = (string) $response->headers->get('Location');
        $this->assertStringContainsString('https://www.linkedin.com/oauth/v2/authorization', $redirectUrl);
        $this->assertStringContainsString('client_id=admin_mock_linkedin_client_id', $redirectUrl);
        $this->assertStringContainsString('w_member_social', $redirectUrl);

        $this->assertTrue(session()->has('admin_linkedin_oauth_state'));
        $state = session()->get('admin_linkedin_oauth_state');
        $this->assertNotEmpty($state);
        $this->assertStringContainsString('state=' . $state, $redirectUrl);
    }

    /**
     * Test admin LinkedIn callback exchanges authorization code and saves official platform account.
     */
    public function test_admin_linkedin_callback_creates_platform_account(): void
    {
        $admin = $this->makeAdmin();

        $state = 'admin_state_secret_999';
        session()->put('admin_linkedin_oauth_state', $state);

        $mockClient = Mockery::mock(LinkedInClient::class);
        $mockClient->shouldReceive('exchangeCodeForToken')
            ->once()
            ->with('admin_auth_code_123', route('admin.social-media.linkedin.callback'))
            ->andReturn([
                'access_token' => 'admin_linkedin_token_live',
                'expires_in'   => 5184000,
                'scope'        => 'openid profile email w_member_social',
            ]);

        $mockClient->shouldReceive('getProfile')
            ->once()
            ->with('admin_linkedin_token_live')
            ->andReturn([
                'sub'     => 'li_platform_admin_urn',
                'name'    => 'Cooca Indonesia Official',
                'email'   => 'official@cooca.id',
                'picture' => 'https://media.licdn.com/dms/image/v2/cooca_official.jpg',
            ]);

        $this->app->instance(LinkedInClient::class, $mockClient);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.social-media.linkedin.callback', [
            'code'  => 'admin_auth_code_123',
            'state' => $state,
        ]));

        $response->assertRedirect(route('admin.social-media.index', ['tab' => 'posts']));
        $response->assertSessionHas('success');

        $account = SocialMediaAccount::where('is_platform', true)
            ->where('platform', 'linkedin')
            ->where('account_id', 'li_platform_admin_urn')
            ->first();

        $this->assertNotNull($account);
        $this->assertNull($account->business_id);
        $this->assertTrue($account->is_platform);
        $this->assertSame('Cooca Indonesia Official', $account->account_name);
        $this->assertSame('official@cooca.id', $account->username);
        $this->assertSame('active', $account->status);
        $this->assertTrue($account->isLinkedIn());
        $this->assertSame('admin_linkedin_token_live', $account->access_token);
    }

    /**
     * Test admin can publish an official post to connected LinkedIn channel.
     */
    public function test_admin_can_publish_post_to_linkedin(): void
    {
        $admin = $this->makeAdmin();

        $account = SocialMediaAccount::create([
            'business_id'      => null,
            'is_platform'      => true,
            'provider'         => 'linkedin',
            'platform'         => 'linkedin',
            'account_id'       => 'li_platform_admin_urn',
            'account_name'     => 'Cooca Indonesia Official',
            'username'         => 'official@cooca.id',
            'access_token'     => 'admin_linkedin_token_live',
            'status'           => 'active',
            'token_expires_at' => now()->addDays(60),
        ]);

        $mockClient = Mockery::mock(LinkedInClient::class);
        $mockClient->shouldReceive('isConfigured')->andReturn(true);
        $mockClient->shouldReceive('publishPost')
            ->once()
            ->with(
                'li_platform_admin_urn',
                'admin_linkedin_token_live',
                'Pengumuman resmi dari manajemen platform Cooca Indonesia.',
                Mockery::any()
            )
            ->andReturn([
                'id' => 'urn:li:ugcPost:admin_platform_987654',
            ]);

        $this->app->instance(LinkedInClient::class, $mockClient);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.social-media.posts.store'), [
            'platforms'   => ['linkedin'],
            'content'     => 'Pengumuman resmi dari manajemen platform Cooca Indonesia.',
            'media_type'  => 'text',
            'timing_mode' => 'now',
        ]);

        $response->assertRedirect(route('admin.social-media.index', ['tab' => 'posts']));
        $response->assertSessionHas('success');

        $post = SocialMediaPost::where('is_platform', true)->first();
        $this->assertNotNull($post);
        $this->assertSame('published', $post->status);

        $target = SocialPostTarget::where('social_media_post_id', $post->id)->first();
        $this->assertNotNull($target);
        $this->assertSame('linkedin', $target->channel);
        $this->assertSame('published', $target->status);
        $this->assertSame('urn:li:ugcPost:admin_platform_987654', $target->platform_post_id);
    }

    /**
     * Test admin can disconnect a platform official social media account.
     */
    public function test_admin_can_disconnect_platform_account(): void
    {
        $admin = $this->makeAdmin();

        $account = SocialMediaAccount::create([
            'business_id'      => null,
            'is_platform'      => true,
            'provider'         => 'linkedin',
            'platform'         => 'linkedin',
            'account_id'       => 'li_platform_to_delete',
            'account_name'     => 'Cooca Platform To Delete',
            'access_token'     => 'token_temp',
            'status'           => 'active',
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.social-media.accounts.disconnect', $account));

        $response->assertRedirect(route('admin.social-media.index', ['tab' => 'posts']));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('social_media_accounts', [
            'id' => $account->id,
        ]);
    }
}
