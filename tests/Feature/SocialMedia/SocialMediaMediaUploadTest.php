<?php

declare(strict_types=1);

namespace Tests\Feature\SocialMedia;

use App\Domain\SocialMedia\Clients\MetaSocialMediaClient;
use App\Domain\SocialMedia\SocialMediaService;
use App\Models\Business;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaPost;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class SocialMediaMediaUploadTest extends TestCase
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
     * Test photo upload is published to Facebook and automatically deleted from Cooca server storage.
     */
    public function test_merchant_can_upload_photo_and_file_is_deleted_after_publish(): void
    {
        Storage::fake('public');
        [$user, $business] = $this->makeMerchant();

        $account = SocialMediaAccount::create([
            'business_id'  => $business->id,
            'platform'     => 'facebook',
            'account_id'   => 'fb_page_1001',
            'account_name' => 'Kedai Kopi FB Page',
            'access_token' => 'page_access_token_123',
            'status'       => 'active',
        ]);

        $mockClient = Mockery::mock(MetaSocialMediaClient::class);
        $mockClient->shouldReceive('publishFacebookPost')
            ->once()
            ->withArgs(function ($pageId, $token, $content, $link, $imageUrl) {
                return $pageId === 'fb_page_1001'
                    && $token === 'page_access_token_123'
                    && str_contains($content, 'Foto Menu Baru')
                    && $link === null
                    && is_string($imageUrl)
                    && str_contains($imageUrl, '.jpg');
            })
            ->andReturn(['id' => 'fb_page_post_photo_999']);

        $service = new SocialMediaService($mockClient);
        $this->app->instance(SocialMediaService::class, $service);

        $fakePhoto = UploadedFile::fake()->image('menu_baru.jpg', 1080, 1080);

        $response = $this->actingAs($user)
            ->post(route('social-media.posts.store'), [
                'social_media_account_id' => $account->id,
                'content'                 => 'Foto Menu Baru Spesial Hari Ini!',
                'media_format'            => 'photo',
                'media_file'              => $fakePhoto,
            ]);

        $response->assertRedirect(route('social-media.posts.index'));
        $response->assertSessionHas('success');

        $post = SocialMediaPost::where('business_id', $business->id)->latest()->first();
        $this->assertNotNull($post);
        $this->assertEquals('published', $post->status);
        $this->assertEquals('image', $post->media_type);
        $this->assertEquals('fb_page_post_photo_999', $post->platform_post_id);

        // Assert local_media_paths was reset to null after auto-delete
        $this->assertNull($post->local_media_paths);

        // Verify disk is completely empty (file was purged from storage)
        $filesOnDisk = Storage::disk('public')->allFiles("social-media/temp/{$business->id}");
        $this->assertEmpty($filesOnDisk, 'Temporary photo file was not deleted from server storage.');
    }

    /**
     * Test feed video upload is published to Facebook and automatically deleted from Cooca server storage.
     */
    public function test_merchant_can_upload_video_and_file_is_deleted_after_publish(): void
    {
        Storage::fake('public');
        [$user, $business] = $this->makeMerchant();

        $account = SocialMediaAccount::create([
            'business_id'  => $business->id,
            'platform'     => 'facebook',
            'account_id'   => 'fb_page_1002',
            'account_name' => 'Kedai Kopi FB Video Page',
            'access_token' => 'page_access_token_video',
            'status'       => 'active',
        ]);

        $mockClient = Mockery::mock(MetaSocialMediaClient::class);
        $mockClient->shouldReceive('publishFacebookVideo')
            ->once()
            ->withArgs(function ($pageId, $token, $content, $videoUrl, $title = null) {
                return $pageId === 'fb_page_1002'
                    && $token === 'page_access_token_video'
                    && str_contains($content, 'Video Proses Roasting')
                    && str_contains($videoUrl, '.mp4');
            })
            ->andReturn(['id' => 'fb_video_item_777']);

        $service = new SocialMediaService($mockClient);
        $this->app->instance(SocialMediaService::class, $service);

        $fakeVideo = UploadedFile::fake()->create('roasting.mp4', 5000, 'video/mp4');

        $response = $this->actingAs($user)
            ->post(route('social-media.posts.store'), [
                'social_media_account_id' => $account->id,
                'content'                 => 'Video Proses Roasting Kopi Segar Kami!',
                'media_format'            => 'video',
                'media_file'              => $fakeVideo,
            ]);

        $response->assertRedirect(route('social-media.posts.index'));
        $response->assertSessionHas('success');

        $post = SocialMediaPost::where('business_id', $business->id)->latest()->first();
        $this->assertNotNull($post);
        $this->assertEquals('published', $post->status);
        $this->assertEquals('video', $post->media_type);
        $this->assertEquals('fb_video_item_777', $post->platform_post_id);
        $this->assertNull($post->local_media_paths);

        // Verify disk has no remaining files
        $filesOnDisk = Storage::disk('public')->allFiles("social-media/temp/{$business->id}");
        $this->assertEmpty($filesOnDisk, 'Temporary video file was not deleted from server storage.');
    }

    /**
     * Test Instagram Reels upload and auto-deletion after publish.
     */
    public function test_merchant_can_upload_instagram_reels_and_file_is_deleted_after_publish(): void
    {
        Storage::fake('public');
        [$user, $business] = $this->makeMerchant();

        $account = SocialMediaAccount::create([
            'business_id'  => $business->id,
            'platform'     => 'instagram',
            'account_id'   => 'ig_user_reels_888',
            'account_name' => 'kedaikopi.reels',
            'access_token' => 'ig_access_token_reels',
            'status'       => 'active',
        ]);

        $mockClient = Mockery::mock(MetaSocialMediaClient::class);
        $mockClient->shouldReceive('publishInstagramPost')
            ->once()
            ->withArgs(function ($igUserId, $token, $content, $mediaUrl, $mediaType) {
                return $igUserId === 'ig_user_reels_888'
                    && $token === 'ig_access_token_reels'
                    && str_contains($content, 'Reels barista latte art')
                    && str_contains($mediaUrl, '.mp4')
                    && $mediaType === 'REELS';
            })
            ->andReturn(['id' => 'ig_reels_post_666']);

        $service = new SocialMediaService($mockClient);
        $this->app->instance(SocialMediaService::class, $service);

        $fakeReels = UploadedFile::fake()->create('latte_art.mp4', 8000, 'video/mp4');

        $response = $this->actingAs($user)
            ->post(route('social-media.posts.store'), [
                'social_media_account_id' => $account->id,
                'content'                 => 'Reels barista latte art tutorial hari ini!',
                'media_format'            => 'reels',
                'media_file'              => $fakeReels,
            ]);

        $response->assertRedirect(route('social-media.posts.index'));
        $response->assertSessionHas('success');

        $post = SocialMediaPost::where('business_id', $business->id)->latest()->first();
        $this->assertNotNull($post);
        $this->assertEquals('published', $post->status);
        $this->assertEquals('reels', $post->media_type);
        $this->assertEquals('ig_reels_post_666', $post->platform_post_id);
        $this->assertNull($post->local_media_paths);

        $filesOnDisk = Storage::disk('public')->allFiles("social-media/temp/{$business->id}");
        $this->assertEmpty($filesOnDisk, 'Temporary Reels file was not deleted from server storage.');
    }

    /**
     * Test scheduled post retains temporary file until scheduled cron command executes, then deletes it.
     */
    public function test_scheduled_post_retains_uploaded_file_until_scheduler_command_publishes_and_deletes_it(): void
    {
        Storage::fake('public');
        [$user, $business] = $this->makeMerchant();

        $account = SocialMediaAccount::create([
            'business_id'  => $business->id,
            'platform'     => 'facebook',
            'account_id'   => 'fb_page_scheduled',
            'account_name' => 'Kedai Kopi Scheduled FB',
            'access_token' => 'page_access_token_sched',
            'status'       => 'active',
        ]);

        $fakePhoto = UploadedFile::fake()->image('promo_weekend.jpg', 1200, 630);

        // 1. Post with future schedule time
        $scheduledTime = now()->addHours(2);

        $response = $this->actingAs($user)
            ->post(route('social-media.posts.store'), [
                'social_media_account_id' => $account->id,
                'content'                 => 'Promo Weekend Seru Segera Datang!',
                'media_format'            => 'photo',
                'media_file'              => $fakePhoto,
                'scheduled_at'            => $scheduledTime->format('Y-m-d H:i:s'),
            ]);

        $response->assertRedirect(route('social-media.posts.index'));
        $response->assertSessionHas('success');

        $post = SocialMediaPost::where('business_id', $business->id)->latest()->first();
        $this->assertNotNull($post);
        $this->assertEquals('scheduled', $post->status);
        $this->assertNotNull($post->local_media_paths);
        $this->assertCount(1, $post->local_media_paths);

        $storedFilePath = $post->local_media_paths[0];

        // 2. Assert file exists on disk while scheduled
        Storage::disk('public')->assertExists($storedFilePath);

        // 3. Now simulate scheduled time arrived (post is due)
        $post->update(['scheduled_at' => now()->subMinutes(2)]);

        $mockClient = Mockery::mock(MetaSocialMediaClient::class);
        $mockClient->shouldReceive('publishFacebookPost')
            ->once()
            ->withArgs(function ($pageId, $token, $content, $link, $imageUrl) {
                return $pageId === 'fb_page_scheduled'
                    && $token === 'page_access_token_sched'
                    && str_contains($content, 'Promo Weekend')
                    && $link === null
                    && is_string($imageUrl);
            })
            ->andReturn(['id' => 'fb_post_scheduled_123']);

        $service = new SocialMediaService($mockClient);
        $this->app->instance(SocialMediaService::class, $service);

        // 4. Run the scheduler command
        $this->artisan('social-media:publish-scheduled')
            ->assertExitCode(0);

        // 5. Assert post is now published and file is deleted
        $post->refresh();
        $this->assertEquals('published', $post->status);
        $this->assertEquals('fb_post_scheduled_123', $post->platform_post_id);
        $this->assertNull($post->local_media_paths);

        // 6. Assert file has been purged from disk
        Storage::disk('public')->assertMissing($storedFilePath);
    }

    /**
     * Test file is retained in storage if publishing fails so merchant does not lose their media.
     */
    public function test_file_is_retained_if_publishing_fails(): void
    {
        Storage::fake('public');
        [$user, $business] = $this->makeMerchant();

        $account = SocialMediaAccount::create([
            'business_id'  => $business->id,
            'platform'     => 'facebook',
            'account_id'   => 'fb_page_error',
            'account_name' => 'Kedai Kopi FB Error',
            'access_token' => 'page_access_token_err',
            'status'       => 'active',
        ]);

        $mockClient = Mockery::mock(MetaSocialMediaClient::class);
        $mockClient->shouldReceive('publishFacebookPost')
            ->once()
            ->andThrow(new \RuntimeException('Meta API rate limit exceeded (#17)'));

        $service = new SocialMediaService($mockClient);
        $this->app->instance(SocialMediaService::class, $service);

        $fakePhoto = UploadedFile::fake()->image('failed_upload.jpg', 600, 600);

        $response = $this->actingAs($user)
            ->post(route('social-media.posts.store'), [
                'social_media_account_id' => $account->id,
                'content'                 => 'Postingan yang gagal publish sementara',
                'media_format'            => 'photo',
                'media_file'              => $fakePhoto,
            ]);

        $response->assertRedirect(route('social-media.posts.index'));
        $response->assertSessionHas('error');

        $post = SocialMediaPost::where('business_id', $business->id)->latest()->first();
        $this->assertNotNull($post);
        $this->assertEquals('failed', $post->status);
        $this->assertStringContainsString('rate limit', $post->error_message);

        // Local media paths and file must be preserved for retrying
        $this->assertNotNull($post->local_media_paths);
        $storedFilePath = $post->local_media_paths[0];
        Storage::disk('public')->assertExists($storedFilePath);
    }
}
