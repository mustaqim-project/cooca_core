<?php

declare(strict_types=1);

namespace Tests\Feature\SocialMedia;

use App\Domain\SocialMedia\Contracts\SocialMediaProviderInterface;
use App\Domain\SocialMedia\SocialMediaManager;
use App\Jobs\SocialMedia\PublishSocialMediaTargetJob;
use App\Models\Business;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaPost;
use App\Models\SocialPostMedia;
use App\Models\SocialPostTarget;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class SocialMediaQueueJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeMerchant(string $bizName = 'Queue Test Merchant'): array
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
     * Test job idempotency: already published target is skipped without re-calling provider.
     */
    public function test_job_skips_already_published_target(): void
    {
        [$user, $business] = $this->makeMerchant();

        $account = SocialMediaAccount::create([
            'business_id'  => $business->id,
            'provider'     => 'tiktok',
            'platform'     => 'tiktok',
            'account_id'   => 'tt_acc_id_111',
            'account_name' => 'TikTok Account',
            'access_token' => 'token_valid',
            'status'       => 'active',
        ]);

        $post = SocialMediaPost::create([
            'business_id'             => $business->id,
            'social_media_account_id' => $account->id,
            'platform'                => 'tiktok',
            'content'                 => 'Postingan TikTok Idempotent #cooca',
            'status'                  => 'published',
        ]);

        $target = SocialPostTarget::create([
            'social_media_post_id'    => $post->id,
            'social_media_account_id' => $account->id,
            'provider'                => 'tiktok',
            'channel'                 => 'tiktok',
            'content_type'            => 'video',
            'status'                  => 'published',
            'platform_post_id'        => 'existing_tt_post_999',
            'published_at'            => now(),
        ]);

        // Manager should never be called because target is already published
        $mockManager = Mockery::mock(SocialMediaManager::class);
        $mockManager->shouldNotReceive('getProvider');
        $this->app->instance(SocialMediaManager::class, $mockManager);

        $job = new PublishSocialMediaTargetJob($target->id);
        $job->handle($mockManager);

        $target->refresh();
        $this->assertSame('published', $target->status);
        $this->assertSame('existing_tt_post_999', $target->platform_post_id);
    }

    /**
     * Test job successfully publishes target and purges temporary files when all targets are done.
     */
    public function test_job_publishes_target_and_purges_temporary_media(): void
    {
        [$user, $business] = $this->makeMerchant();

        // 1. Create temporary files in fake storage
        $filePath1 = "social-media/temp/{$business->id}/image1.jpg";
        $filePath2 = "social-media/temp/{$business->id}/image2.jpg";
        Storage::disk('public')->put($filePath1, 'dummy_content_1');
        Storage::disk('public')->put($filePath2, 'dummy_content_2');

        $this->assertTrue(Storage::disk('public')->exists($filePath1));
        $this->assertTrue(Storage::disk('public')->exists($filePath2));

        $account = SocialMediaAccount::create([
            'business_id'  => $business->id,
            'provider'     => 'tiktok',
            'platform'     => 'tiktok',
            'account_id'   => 'tt_acc_id_222',
            'account_name' => 'TikTok Account 2',
            'access_token' => 'token_valid_2',
            'status'       => 'active',
        ]);

        $post = SocialMediaPost::create([
            'business_id'             => $business->id,
            'social_media_account_id' => $account->id,
            'platform'                => 'tiktok',
            'content'                 => 'Postingan Foto TikTok #ramadhan',
            'status'                  => 'publishing',
            'local_media_paths'       => [$filePath1, $filePath2],
        ]);

        $media1 = SocialPostMedia::create([
            'social_media_post_id' => $post->id,
            'sort_order'           => 1,
            'media_type'           => 'image',
            'media_url'            => 'https://example.com/1.jpg',
            'local_path'           => $filePath1,
        ]);

        $media2 = SocialPostMedia::create([
            'social_media_post_id' => $post->id,
            'sort_order'           => 2,
            'media_type'           => 'image',
            'media_url'            => 'https://example.com/2.jpg',
            'local_path'           => $filePath2,
        ]);

        $target = SocialPostTarget::create([
            'social_media_post_id'    => $post->id,
            'social_media_account_id' => $account->id,
            'provider'                => 'tiktok',
            'channel'                 => 'tiktok',
            'content_type'            => 'photo',
            'status'                  => 'pending',
        ]);

        // Mock Provider to simulate successful publishing
        $mockProvider = Mockery::mock(SocialMediaProviderInterface::class);
        $mockProvider->shouldReceive('publish')
            ->once()
            ->andReturn([
                'id'         => 'tt_pub_success_123',
                'publish_id' => 'tt_publish_job_456',
                'status'     => 'SUCCESS',
            ]);

        $mockManager = Mockery::mock(SocialMediaManager::class);
        $mockManager->shouldReceive('getProvider')
            ->with('tiktok')
            ->once()
            ->andReturn($mockProvider);

        $this->app->instance(SocialMediaManager::class, $mockManager);

        $job = new PublishSocialMediaTargetJob($target->id);
        $job->handle($mockManager);

        // Verify target is published
        $target->refresh();
        $this->assertSame('published', $target->status);
        $this->assertSame('tt_pub_success_123', $target->platform_post_id);
        $this->assertNotNull($target->published_at);

        // Verify parent post is updated to published
        $post->refresh();
        $this->assertSame('published', $post->status);

        // Verify temporary files have been auto-purged from storage
        $this->assertFalse(Storage::disk('public')->exists($filePath1));
        $this->assertFalse(Storage::disk('public')->exists($filePath2));
    }

    /**
     * Test job handles provider failure, increments retry count, and records error message.
     */
    public function test_job_handles_provider_failure(): void
    {
        [$user, $business] = $this->makeMerchant();

        $account = SocialMediaAccount::create([
            'business_id'  => $business->id,
            'provider'     => 'meta',
            'platform'     => 'facebook',
            'account_id'   => 'fb_acc_id_333',
            'account_name' => 'FB Page',
            'access_token' => 'token_fb',
            'status'       => 'active',
        ]);

        $post = SocialMediaPost::create([
            'business_id'             => $business->id,
            'social_media_account_id' => $account->id,
            'platform'                => 'facebook',
            'content'                 => 'Postingan Facebook Gagal #test',
            'status'                  => 'publishing',
        ]);

        $target = SocialPostTarget::create([
            'social_media_post_id'    => $post->id,
            'social_media_account_id' => $account->id,
            'provider'                => 'meta',
            'channel'                 => 'facebook',
            'content_type'            => 'feed',
            'status'                  => 'pending',
            'retry_count'             => 0,
        ]);

        $mockProvider = Mockery::mock(SocialMediaProviderInterface::class);
        $mockProvider->shouldReceive('publish')
            ->once()
            ->andThrow(new \RuntimeException('Meta API rate limit reached (Code: 429)'));

        $mockManager = Mockery::mock(SocialMediaManager::class);
        $mockManager->shouldReceive('getProvider')
            ->with('meta')
            ->once()
            ->andReturn($mockProvider);

        $this->app->instance(SocialMediaManager::class, $mockManager);

        $job = new PublishSocialMediaTargetJob($target->id);

        try {
            $job->handle($mockManager);
        } catch (\RuntimeException $e) {
            // Re-thrown for queue retry backoff
        }

        $target->refresh();
        $this->assertSame('failed', $target->status);
        $this->assertSame(1, $target->retry_count);
        $this->assertStringContainsString('Meta API rate limit reached', $target->error_message);

        $post->refresh();
        $this->assertSame('failed', $post->status);
    }
}
