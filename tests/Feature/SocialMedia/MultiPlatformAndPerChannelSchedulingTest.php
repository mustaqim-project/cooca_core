<?php

declare(strict_types=1);

namespace Tests\Feature\SocialMedia;

use App\Jobs\SocialMedia\PublishSocialMediaTargetJob;
use App\Models\Admin;
use App\Models\Business;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaPost;
use App\Models\SocialPostTarget;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class MultiPlatformAndPerChannelSchedulingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function makeMerchant(string $bizName = 'Omnichannel Merchant'): array
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
            'business_id'           => $business->id,
            'platform'              => $platform,
            'provider'              => $provider,
            'account_id'            => 'acc_' . $platform . '_' . Str::random(8),
            'account_name'          => 'Store ' . ucfirst($platform),
            'account_username'      => 'store_' . $platform,
            'access_token'          => 'token_' . Str::random(32),
            'status'                => 'active',
            'webhook_verify_token'  => Str::random(16),
            'token_expires_at'      => now()->addDays(60),
        ]);
    }

    public function test_skenario_1_upload_kucing_multi_publish_langsung_ke_instagram_facebook_tiktok(): void
    {
        Queue::fake();

        [$user, $business] = $this->makeMerchant();
        $igAcc = $this->createAccount($business, 'instagram', 'meta');
        $fbAcc = $this->createAccount($business, 'facebook', 'meta');
        $ttAcc = $this->createAccount($business, 'tiktok', 'tiktok');

        $file = UploadedFile::fake()->image('kucing.jpg', 800, 800);

        $response = $this->actingAs($user)->post(route('social-media.posts.store'), [
            'target_accounts' => [$igAcc->id, $fbAcc->id, $ttAcc->id],
            'content'         => 'Foto kucing lucu Cooca #KucingLucu #CoocaERP',
            'media_file'      => $file,
            'schedule_mode'   => 'all_now',
        ]);

        $response->assertRedirect(route('social-media.posts.index'));
        $response->assertSessionHas('success');

        $post = SocialMediaPost::where('business_id', $business->id)->first();
        $this->assertNotNull($post);
        $this->assertEquals('image', $post->media_type);

        // 3 target dibuat
        $targets = SocialPostTarget::where('social_media_post_id', $post->id)->get();
        $this->assertCount(3, $targets);

        foreach ($targets as $target) {
            $this->assertEquals('pending', $target->status);
            $this->assertNull($target->scheduled_at);
        }

        Queue::assertPushed(PublishSocialMediaTargetJob::class, 3);
    }

    public function test_skenario_2_upload_kucing_dengan_jadwal_independen_per_saluran(): void
    {
        Queue::fake();

        Carbon::setTestNow(Carbon::parse('2026-09-18 08:00:00'));

        [$user, $business] = $this->makeMerchant();
        $igAcc = $this->createAccount($business, 'instagram', 'meta');
        $fbAcc = $this->createAccount($business, 'facebook', 'meta');
        $ttAcc = $this->createAccount($business, 'tiktok', 'tiktok');

        $file = UploadedFile::fake()->image('kucing.jpg', 800, 800);

        $response = $this->actingAs($user)->post(route('social-media.posts.store'), [
            'target_accounts'        => [$igAcc->id, $fbAcc->id, $ttAcc->id],
            'content'                => 'Foto kucing beda waktu tayang #Kucing #CoocaERP',
            'media_file'             => $file,
            'schedule_mode'          => 'per_channel',
            'channel_schedule_modes' => [
                $igAcc->id => 'now',
                $fbAcc->id => 'schedule',
                $ttAcc->id => 'schedule',
            ],
            'channel_scheduled_at'   => [
                $igAcc->id => null,
                $fbAcc->id => '2026-09-18 14:00:00', // nanti jam 2 siang
                $ttAcc->id => '2026-09-19 09:00:00', // besok pagi
            ],
        ]);

        $response->assertRedirect(route('social-media.posts.index'));
        $response->assertSessionHas('success');

        $post = SocialMediaPost::where('business_id', $business->id)->first();
        $this->assertNotNull($post);

        $igTarget = SocialPostTarget::where('social_media_post_id', $post->id)->where('social_media_account_id', $igAcc->id)->first();
        $fbTarget = SocialPostTarget::where('social_media_post_id', $post->id)->where('social_media_account_id', $fbAcc->id)->first();
        $ttTarget = SocialPostTarget::where('social_media_post_id', $post->id)->where('social_media_account_id', $ttAcc->id)->first();

        // IG langsung
        $this->assertEquals('pending', $igTarget->status);
        $this->assertNull($igTarget->scheduled_at);

        // FB jam 2 siang
        $this->assertEquals('scheduled', $fbTarget->status);
        $this->assertEquals('2026-09-18 14:00:00', $fbTarget->scheduled_at->format('Y-m-d H:i:s'));

        // TikTok besok
        $this->assertEquals('scheduled', $ttTarget->status);
        $this->assertEquals('2026-09-19 09:00:00', $ttTarget->scheduled_at->format('Y-m-d H:i:s'));

        // Hanya IG yang langsung di-push ke queue saat ini
        Queue::assertPushed(PublishSocialMediaTargetJob::class, 1);
        Queue::assertPushed(PublishSocialMediaTargetJob::class, function ($job) use ($igTarget) {
            return $job->targetId === $igTarget->id;
        });

        // Simulasi waktu bergulir ke jam 14:05 (waktu Facebook tiba, TikTok belum)
        Carbon::setTestNow(Carbon::parse('2026-09-18 14:05:00'));

        $this->artisan('social-media:publish-scheduled')
            ->expectsOutputToContain('scheduled target(s) ready to publish')
            ->assertSuccessful();

        // Facebook harus beralih ke pending dan di-dispatch
        $fbTarget->refresh();
        $this->assertEquals('pending', $fbTarget->status);

        // TikTok tetap scheduled
        $ttTarget->refresh();
        $this->assertEquals('scheduled', $ttTarget->status);

        Carbon::setTestNow();
    }

    public function test_skenario_3_upload_kambing_hanya_untuk_instagram_saja(): void
    {
        Queue::fake();

        [$user, $business] = $this->makeMerchant();
        $igAcc = $this->createAccount($business, 'instagram', 'meta');
        $fbAcc = $this->createAccount($business, 'facebook', 'meta');

        $file = UploadedFile::fake()->image('kambing.jpg', 800, 800);

        $response = $this->actingAs($user)->post(route('social-media.posts.store'), [
            'target_accounts' => [$igAcc->id], // Hanya Instagram
            'content'         => 'Foto kambing qurban pilihan #Kambing #CoocaERP',
            'media_file'      => $file,
            'schedule_mode'   => 'all_now',
        ]);

        $response->assertRedirect(route('social-media.posts.index'));

        $post = SocialMediaPost::where('business_id', $business->id)->first();
        $this->assertNotNull($post);

        // Tepat 1 target dibuat hanya untuk Instagram
        $targets = SocialPostTarget::where('social_media_post_id', $post->id)->get();
        $this->assertCount(1, $targets);
        $this->assertEquals($igAcc->id, $targets->first()->social_media_account_id);
        $this->assertEquals('instagram', $targets->first()->channel);
    }

    public function test_platform_admin_multi_channel_publish_dan_penjadwalan_cronjob(): void
    {
        $admin = Admin::create([
            'name'     => 'Super Admin Cooca',
            'email'    => 'admin-sched@cooca.id',
            'password' => bcrypt('password123'),
        ]);

        Carbon::setTestNow(Carbon::parse('2026-09-18 09:00:00'));

        $response = $this->actingAs($admin, 'admin')->post(route('admin.social-media.posts.store'), [
            'platforms'             => ['instagram', 'facebook', 'tiktok'],
            'content'               => 'Pengumuman Resmi Platform Cooca #CoocaERP',
            'media_url'             => 'https://cooca.id/banner.jpg',
            'timing_mode'           => 'per_channel',
            'platform_timing'       => [
                'instagram' => 'schedule',
                'facebook'  => 'schedule',
                'tiktok'    => 'schedule',
            ],
            'platform_scheduled_at' => [
                'instagram' => '2026-09-18 10:00:00',
                'facebook'  => '2026-09-18 14:00:00',
                'tiktok'    => '2026-09-19 09:00:00',
            ],
        ]);

        $response->assertRedirect(route('admin.social-media.index', ['tab' => 'posts']));
        $response->assertSessionHas('success');

        $post = SocialMediaPost::where('is_platform', true)->latest()->first();
        $this->assertNotNull($post);
        $this->assertEquals('scheduled', $post->status);

        $targets = SocialPostTarget::where('social_media_post_id', $post->id)->get();
        $this->assertCount(3, $targets);

        // Saat jam 10:05 (Waktu Instagram tiba)
        Carbon::setTestNow(Carbon::parse('2026-09-18 10:05:00'));

        $this->artisan('social-media:publish-scheduled')
            ->expectsOutputToContain('Publishing scheduled platform target')
            ->assertSuccessful();

        $igTarget = $targets->firstWhere('channel', 'instagram')->fresh();
        // Instagram target dieksekusi oleh artisan command
        $this->assertTrue(in_array($igTarget->status, ['published', 'failed'], true));

        // Facebook dan TikTok masih scheduled
        $fbTarget = $targets->firstWhere('channel', 'facebook')->fresh();
        $ttTarget = $targets->firstWhere('channel', 'tiktok')->fresh();
        $this->assertEquals('scheduled', $fbTarget->status);
        $this->assertEquals('scheduled', $ttTarget->status);

        Carbon::setTestNow();
    }
}
