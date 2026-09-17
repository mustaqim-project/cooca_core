<?php

declare(strict_types=1);

namespace Tests\Feature\SocialMedia;

use App\Jobs\SocialMedia\PublishSocialMediaTargetJob;
use App\Models\Business;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaPost;
use App\Models\SocialPostMedia;
use App\Models\SocialPostTarget;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class UnifiedPostingAndCarouselTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function makeMerchant(string $bizName = 'Omnichannel Store'): array
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

    private function createAccount(Business $business, string $platform, string $provider): SocialMediaAccount
    {
        return SocialMediaAccount::create([
            'business_id'  => $business->id,
            'provider'     => $provider,
            'platform'     => $platform,
            'account_id'   => 'acc_' . $platform . '_' . Str::random(8),
            'account_name' => ucfirst($platform) . ' Official',
            'access_token' => 'mock_token_' . $platform,
            'status'       => 'active',
        ]);
    }

    /**
     * Test multi-platform post creation creates targets and dispatches queue jobs.
     */
    public function test_multi_platform_posting_dispatches_target_jobs(): void
    {
        Queue::fake();

        [$user, $business] = $this->makeMerchant();

        $accFB = $this->createAccount($business, 'facebook', 'meta');
        $accIG = $this->createAccount($business, 'instagram', 'meta');
        $accTikTok = $this->createAccount($business, 'tiktok', 'tiktok');

        $response = $this->actingAs($user)->post(route('social-media.posts.store'), [
            'target_accounts' => [$accFB->id, $accIG->id, $accTikTok->id],
            'content'         => 'Koleksi busana muslim terbaru kini hadir! #fashion #muslim #hijab #umkm #cooca',
            'custom_captions' => [
                $accTikTok->id => 'POV: OOTD Ramadhan kamu makin kece #fashion #ootd #tiktok',
            ],
        ]);

        $response->assertRedirect(route('social-media.posts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseCount('social_media_posts', 1);
        $post = SocialMediaPost::first();
        $this->assertSame($business->id, $post->business_id);

        $this->assertDatabaseCount('social_post_targets', 3);

        // Verify target records
        $this->assertDatabaseHas('social_post_targets', [
            'social_media_post_id'    => $post->id,
            'social_media_account_id' => $accFB->id,
            'channel'                 => 'facebook',
            'provider'                => 'meta',
            'status'                  => 'pending',
        ]);

        $this->assertDatabaseHas('social_post_targets', [
            'social_media_post_id'    => $post->id,
            'social_media_account_id' => $accIG->id,
            'channel'                 => 'instagram',
            'provider'                => 'meta',
            'status'                  => 'pending',
        ]);

        $tiktokTarget = SocialPostTarget::where('social_media_account_id', $accTikTok->id)->first();
        $this->assertNotNull($tiktokTarget);
        $this->assertSame('POV: OOTD Ramadhan kamu makin kece #fashion #ootd #tiktok', $tiktokTarget->custom_caption);

        // Assert PublishSocialMediaTargetJob dispatched for each of the 3 targets
        Queue::assertPushed(PublishSocialMediaTargetJob::class, 3);
    }

    /**
     * Test carousel multi-file upload persists media items with sequential sort_order.
     */
    public function test_carousel_upload_persists_sort_order_and_sets_type(): void
    {
        Queue::fake();

        [$user, $business] = $this->makeMerchant();
        $accIG = $this->createAccount($business, 'instagram', 'meta');

        $file1 = UploadedFile::fake()->image('slide1.jpg', 1080, 1080);
        $file2 = UploadedFile::fake()->image('slide2.jpg', 1080, 1080);
        $file3 = UploadedFile::fake()->image('slide3.jpg', 1080, 1080);

        $response = $this->actingAs($user)->post(route('social-media.posts.store'), [
            'target_accounts' => [$accIG->id],
            'content'         => 'Tutorial memasak kopi espresso sempurna #kopi #barista #cooca',
            'media_files'     => [$file1, $file2, $file3],
            'scheduled_at'    => now()->addHour()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect(route('social-media.posts.index'));
        $response->assertSessionHas('success');

        $post = SocialMediaPost::first();
        $this->assertNotNull($post);
        $this->assertSame('carousel', $post->media_type);
        $this->assertSame('scheduled', $post->status);

        $mediaRecords = SocialPostMedia::where('social_media_post_id', $post->id)
            ->orderBy('sort_order')
            ->get();

        $this->assertCount(3, $mediaRecords);
        $this->assertSame(1, $mediaRecords[0]->sort_order);
        $this->assertSame(2, $mediaRecords[1]->sort_order);
        $this->assertSame(3, $mediaRecords[2]->sort_order);

        foreach ($mediaRecords as $m) {
            $this->assertSame('image', $m->media_type);
            $this->assertNotNull($m->local_path);
            Storage::disk('public')->assertExists($m->local_path);
        }
    }

    /**
     * Test strict 5-hashtag rejection halts post creation.
     */
    public function test_posting_fails_when_hashtags_exceed_five(): void
    {
        [$user, $business] = $this->makeMerchant();
        $accFB = $this->createAccount($business, 'facebook', 'meta');

        $response = $this->actingAs($user)->post(route('social-media.posts.store'), [
            'target_accounts' => [$accFB->id],
            'content'         => 'Postingan dengan 6 hashtag #satu #dua #tiga #empat #lima #enam',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseCount('social_media_posts', 0);
        $this->assertDatabaseCount('social_post_targets', 0);
    }

    /**
     * Test partial failure retry: retrying failed target dispatches job only for that target.
     */
    public function test_merchant_can_retry_failed_target(): void
    {
        Queue::fake();

        [$user, $business] = $this->makeMerchant();

        $accIG = $this->createAccount($business, 'instagram', 'meta');
        $accThreads = $this->createAccount($business, 'threads', 'meta');

        $post = SocialMediaPost::create([
            'business_id'             => $business->id,
            'social_media_account_id' => $accIG->id,
            'platform'                => 'instagram',
            'content'                 => 'Postingan dengan target ganda #bisnis',
            'status'                  => 'partial_failed',
        ]);

        // Target 1: Instagram succeeded
        $targetIG = SocialPostTarget::create([
            'social_media_post_id'    => $post->id,
            'social_media_account_id' => $accIG->id,
            'provider'                => 'meta',
            'channel'                 => 'instagram',
            'content_type'            => 'photo',
            'status'                  => 'published',
            'platform_post_id'        => 'ig_media_success_99',
            'retry_count'             => 0,
        ]);

        // Target 2: Threads failed
        $targetThreads = SocialPostTarget::create([
            'social_media_post_id'    => $post->id,
            'social_media_account_id' => $accThreads->id,
            'provider'                => 'meta',
            'channel'                 => 'threads',
            'content_type'            => 'text',
            'status'                  => 'failed',
            'error_message'           => 'Threads rate limit temporary error',
            'retry_count'             => 1,
        ]);

        // Merchant retries Threads target
        $response = $this->actingAs($user)->post(route('social-media.targets.retry', $targetThreads));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Confirm status changed back to pending
        $targetThreads->refresh();
        $this->assertSame('pending', $targetThreads->status);
        $this->assertNull($targetThreads->error_message);

        // Confirm PublishSocialMediaTargetJob dispatched only for Threads target
        Queue::assertPushed(PublishSocialMediaTargetJob::class, function ($job) use ($targetThreads) {
            return $job->targetId === $targetThreads->id;
        });

        // Target 1 must remain published
        $targetIG->refresh();
        $this->assertSame('published', $targetIG->status);
    }

    /**
     * Test tenant isolation: Merchant A cannot retry Merchant B's target.
     */
    public function test_merchant_cannot_retry_target_belonging_to_another_business(): void
    {
        [$userA, $businessA] = $this->makeMerchant('Toko A');
        [$userB, $businessB] = $this->makeMerchant('Toko B');

        $accB = $this->createAccount($businessB, 'facebook', 'meta');

        $postB = SocialMediaPost::create([
            'business_id'             => $businessB->id,
            'social_media_account_id' => $accB->id,
            'platform'                => 'facebook',
            'content'                 => 'Postingan Toko B',
            'status'                  => 'failed',
        ]);

        $targetB = SocialPostTarget::create([
            'social_media_post_id'    => $postB->id,
            'social_media_account_id' => $accB->id,
            'provider'                => 'meta',
            'channel'                 => 'facebook',
            'content_type'            => 'feed',
            'status'                  => 'failed',
            'retry_count'             => 1,
        ]);

        $response = $this->actingAs($userA)->post(route('social-media.targets.retry', $targetB));
        $response->assertForbidden();
    }

    /**
     * Test merchant can access calendar view.
     */
    public function test_merchant_can_view_social_media_calendar(): void
    {
        [$user, $business] = $this->makeMerchant();
        $accFB = $this->createAccount($business, 'facebook', 'meta');

        SocialMediaPost::create([
            'business_id'             => $business->id,
            'social_media_account_id' => $accFB->id,
            'platform'                => 'facebook',
            'content'                 => 'Postingan Kalender Terjadwal #agenda',
            'status'                  => 'scheduled',
            'scheduled_at'            => now()->addDays(2),
        ]);

        $response = $this->actingAs($user)->get(route('social-media.calendar'));
        $response->assertOk();
        $response->assertSee('Kalender Jadwal Konten');
        $response->assertSee('Postingan Kalender Terjadwal');
    }
}
