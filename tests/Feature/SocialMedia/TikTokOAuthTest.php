<?php

declare(strict_types=1);

namespace Tests\Feature\SocialMedia;

use App\Domain\SocialMedia\Clients\TikTokClient;
use App\Domain\SocialMedia\Providers\TikTokProvider;
use App\Domain\SocialMedia\SocialMediaManager;
use App\Models\Business;
use App\Models\SocialMediaAccount;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class TikTokOAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.tiktok.client_key', 'mock_tiktok_client_key');
        Config::set('services.tiktok.client_secret', 'mock_tiktok_client_secret');
        Config::set('services.tiktok.redirect_uri', 'https://cooca.id/social-media/tiktok/callback');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeMerchant(string $bizName = 'Toko TikTok Berkah'): array
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
     * Test guest cannot initiate TikTok OAuth.
     */
    public function test_guest_cannot_initiate_tiktok_oauth(): void
    {
        $response = $this->get(route('social-media.tiktok.connect'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test merchant can initiate TikTok OAuth flow with CSRF state stored in session.
     */
    public function test_merchant_can_initiate_tiktok_oauth(): void
    {
        [$user, $business] = $this->makeMerchant();

        $response = $this->actingAs($user)->get(route('social-media.tiktok.connect'));

        $response->assertRedirect();
        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('https://www.tiktok.com/v2/auth/authorize/', $redirectUrl);
        $this->assertStringContainsString('client_key=mock_tiktok_client_key', $redirectUrl);
        $this->assertStringContainsString('video.publish', $redirectUrl);

        $this->assertTrue(session()->has('tiktok_oauth_state'));
        $state = session()->get('tiktok_oauth_state');
        $this->assertNotEmpty($state);
        $this->assertStringContainsString('state=' . $state, $redirectUrl);
    }

    /**
     * Test TikTok OAuth callback rejects missing or invalid state (CSRF mitigation).
     */
    public function test_tiktok_callback_rejects_invalid_csrf_state(): void
    {
        [$user, $business] = $this->makeMerchant();

        session()->put('tiktok_oauth_state', 'expected_secure_state_123');

        $response = $this->actingAs($user)->get(route('social-media.tiktok.callback', [
            'code'  => 'auth_code_xyz',
            'state' => 'forged_or_tampered_state',
        ]));

        $response->assertRedirect(route('social-media.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('social_media_accounts', 0);
    }

    /**
     * Test successful TikTok OAuth callback exchanges code and persists encrypted tokens.
     */
    public function test_tiktok_callback_successfully_exchanges_code_and_persists_account(): void
    {
        [$user, $business] = $this->makeMerchant();

        $state = 'valid_oauth_state_555';
        session()->put('tiktok_oauth_state', $state);

        $mockClient = Mockery::mock(TikTokClient::class);
        $mockClient->shouldReceive('exchangeCodeForToken')
            ->once()
            ->with('auth_code_valid_123', route('social-media.tiktok.callback'))
            ->andReturn([
                'open_id'                  => 'open_id_tiktok_999',
                'access_token'             => 'raw_access_token_tiktok_abc',
                'expires_in'               => 86400,
                'refresh_token'            => 'raw_refresh_token_tiktok_def',
                'refresh_expires_in'       => 31536000,
                'scope'                    => 'user.info.basic,video.publish,video.upload',
            ]);

        $mockClient->shouldReceive('getCreatorInfo')
            ->once()
            ->with('raw_access_token_tiktok_abc')
            ->andReturn([
                'creator_username'   => 'tokotiktok_official',
                'creator_nickname'   => 'Toko TikTok Berkah',
                'creator_avatar_url' => 'https://p16.tiktokcdn.com/avatar.jpg',
            ]);

        $provider = new TikTokProvider($mockClient);
        $this->app->instance(TikTokClient::class, $mockClient);
        $this->app->instance(TikTokProvider::class, $provider);

        $response = $this->actingAs($user)->get(route('social-media.tiktok.callback', [
            'code'  => 'auth_code_valid_123',
            'state' => $state,
        ]));

        $response->assertRedirect(route('social-media.index'));
        $response->assertSessionHas('success');

        $account = SocialMediaAccount::where('business_id', $business->id)
            ->where('platform', 'tiktok')
            ->first();

        $this->assertNotNull($account);
        $this->assertSame('tiktok', $account->provider);
        $this->assertSame('open_id_tiktok_999', $account->account_id);
        $this->assertSame('@tokotiktok_official', $account->username);
        $this->assertSame('Toko TikTok Berkah', $account->account_name);
        $this->assertSame('active', $account->status);

        // Verify tokens are stored encrypted in DB and decrypted by Eloquent cast
        $rawDbToken = \Illuminate\Support\Facades\DB::table('social_media_accounts')
            ->where('id', $account->id)
            ->value('access_token');
        $this->assertNotSame('raw_access_token_tiktok_abc', $rawDbToken);
        $this->assertSame('raw_access_token_tiktok_abc', $account->access_token);
        $this->assertSame('raw_refresh_token_tiktok_def', $account->refresh_token);
        $this->assertNotNull($account->token_expires_at);
        $this->assertNotNull($account->refresh_token_expires_at);
    }

    /**
     * Test TikTok token auto-refresh when near expiration.
     */
    public function test_tiktok_provider_refreshes_token_when_expired(): void
    {
        [$user, $business] = $this->makeMerchant();

        $account = SocialMediaAccount::create([
            'business_id'              => $business->id,
            'provider'                 => 'tiktok',
            'platform'                 => 'tiktok',
            'account_id'               => 'tiktok_acc_expired',
            'account_name'             => 'Expired TikTok Shop',
            'access_token'             => 'stale_access_token',
            'refresh_token'            => 'valid_refresh_token',
            'token_expires_at'         => now()->subMinute(), // expired
            'refresh_token_expires_at' => now()->addDays(30),
            'status'                   => 'active',
        ]);

        $mockClient = Mockery::mock(TikTokClient::class);
        $mockClient->shouldReceive('refreshToken')
            ->once()
            ->with('valid_refresh_token')
            ->andReturn([
                'open_id'            => 'tiktok_acc_expired',
                'access_token'       => 'new_fresh_access_token_888',
                'expires_in'         => 86400,
                'refresh_token'      => 'new_fresh_refresh_token_999',
                'refresh_expires_in' => 31536000,
            ]);

        $provider = new TikTokProvider($mockClient);
        $refreshedToken = $provider->checkAndRefreshToken($account);

        $this->assertSame('new_fresh_access_token_888', $refreshedToken);

        $account->refresh();
        $this->assertSame('new_fresh_access_token_888', $account->access_token);
        $this->assertSame('new_fresh_refresh_token_999', $account->refresh_token);
        $this->assertTrue($account->token_expires_at->isFuture());
    }

    /**
     * Test tenant isolation: Business A cannot disconnect Business B's TikTok account.
     */
    public function test_merchant_cannot_disconnect_another_business_tiktok_account(): void
    {
        [$userA, $businessA] = $this->makeMerchant('Toko A');
        [$userB, $businessB] = $this->makeMerchant('Toko B');

        $accountB = SocialMediaAccount::create([
            'business_id'         => $businessB->id,
            'provider'            => 'tiktok',
            'platform'            => 'tiktok',
            'account_id'          => 'tiktok_b_123',
            'account_name'        => 'TikTok Shop B',
            'access_token'        => 'token_b',
            'status'              => 'active',
        ]);

        // Attempting to disconnect account B while authenticated as User A
        $response = $this->actingAs($userA)->postJson(route('social-media.disconnect'), [
            'account_id' => $accountB->id,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => false,
            'message' => 'Akun tidak ditemukan.',
        ]);

        // Confirm account B remains active in database
        $this->assertDatabaseHas('social_media_accounts', [
            'id'     => $accountB->id,
            'status' => 'active',
        ]);
    }
}
