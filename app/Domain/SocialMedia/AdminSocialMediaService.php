<?php

declare(strict_types=1);

namespace App\Domain\SocialMedia;

use App\Models\Business;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaComment;
use App\Models\SocialMediaPost;
use App\Models\SocialPostTarget;
use App\Models\SystemSetting;

class AdminSocialMediaService
{
    /**
     * Get platform-wide Meta Social Media configuration settings.
     */
    public function getPlatformSettings(): array
    {
        return [
            'app_id'               => (string) SystemSetting::get('social_media_app_id', ''),
            'app_secret'           => (string) SystemSetting::get('social_media_app_secret', ''),
            'webhook_verify_token' => (string) SystemSetting::get('social_media_webhook_verify_token', 'cooca_meta_social_webhook_token'),
            'graph_version'        => (string) SystemSetting::get('social_media_graph_version', 'v21.0'),
            'graph_url'            => (string) SystemSetting::get('social_media_graph_url', 'https://graph.facebook.com'),
            'webhook_url'          => url('/api/v1/social-media/meta/webhook'),

            // TikTok Settings
            'tiktok_client_key'    => (string) SystemSetting::get('tiktok_client_key', ''),
            'tiktok_client_secret' => (string) SystemSetting::get('tiktok_client_secret', ''),
            'tiktok_redirect_uri'  => route('social-media.tiktok.callback'),

            // Instagram Platform Dedicated Settings
            'instagram_app_id'              => (string) (SystemSetting::get('instagram_app_id') ?: SystemSetting::get('social_media_app_id', '')),
            'instagram_app_name'            => (string) SystemSetting::get('instagram_app_name', 'Cooca-IG'),
            'instagram_app_secret'          => (string) (SystemSetting::get('instagram_app_secret') ?: SystemSetting::get('social_media_app_secret', '')),
            'instagram_account_id'          => (string) SystemSetting::get('instagram_account_id', ''),
            'instagram_graph_user_id'       => (string) SystemSetting::get('instagram_graph_user_id', ''),
            'instagram_username'            => (string) SystemSetting::get('instagram_username', ''),
            'instagram_access_token'        => (string) SystemSetting::get('instagram_access_token', ''),
            'instagram_account_type'        => (string) SystemSetting::get('instagram_account_type', ''),
            'instagram_media_count'         => (string) SystemSetting::get('instagram_media_count', '0'),
            'instagram_profile_picture_url' => (string) SystemSetting::get('instagram_profile_picture_url', ''),
            'instagram_status'              => (string) SystemSetting::get('instagram_status', 'inactive'),
            'instagram_verified_at'         => (string) SystemSetting::get('instagram_verified_at', ''),

            // Convenient Aliases
            'ig_app_id'                     => (string) (SystemSetting::get('instagram_app_id') ?: SystemSetting::get('social_media_app_id', '')),
            'ig_app_name'                   => (string) SystemSetting::get('instagram_app_name', 'Cooca-IG'),
            'ig_app_secret'                 => (string) (SystemSetting::get('instagram_app_secret') ?: SystemSetting::get('social_media_app_secret', '')),
            'ig_account_id'                 => (string) SystemSetting::get('instagram_account_id', ''),
            'ig_username'                   => (string) SystemSetting::get('instagram_username', ''),
            'ig_access_token'               => (string) SystemSetting::get('instagram_access_token', ''),
            'ig_redirect_uri'               => url('/social-media/exchange-token'),
        ];
    }

    /**
     * Save platform Meta, Instagram & TikTok Social Media configuration settings.
     */
    public function savePlatformSettings(array $data): void
    {
        if (array_key_exists('app_id', $data)) {
            SystemSetting::set('social_media_app_id', trim((string) $data['app_id']), 'social_media');
        }
        if (array_key_exists('app_secret', $data) && ! empty($data['app_secret'])) {
            SystemSetting::set('social_media_app_secret', trim((string) $data['app_secret']), group: 'social_media', isSecret: true);
        }
        if (array_key_exists('webhook_verify_token', $data)) {
            SystemSetting::set('social_media_webhook_verify_token', trim((string) $data['webhook_verify_token']), 'social_media');
        }
        if (array_key_exists('graph_version', $data)) {
            SystemSetting::set('social_media_graph_version', trim((string) $data['graph_version']), 'social_media');
        }
        if (array_key_exists('graph_url', $data)) {
            SystemSetting::set('social_media_graph_url', trim((string) $data['graph_url']), 'social_media');
        }

        // TikTok Settings
        if (array_key_exists('tiktok_client_key', $data)) {
            SystemSetting::set('tiktok_client_key', trim((string) $data['tiktok_client_key']), 'social_media');
        }
        if (array_key_exists('tiktok_client_secret', $data) && ! empty($data['tiktok_client_secret'])) {
            SystemSetting::set('tiktok_client_secret', trim((string) $data['tiktok_client_secret']), group: 'social_media', isSecret: true);
        }

        // Instagram Dedicated Settings
        if (array_key_exists('instagram_app_id', $data)) {
            SystemSetting::set('instagram_app_id', trim((string) $data['instagram_app_id']), 'social_media');
        }
        if (array_key_exists('instagram_app_name', $data)) {
            SystemSetting::set('instagram_app_name', trim((string) $data['instagram_app_name']), 'social_media');
        }
        if (! empty($data['instagram_app_secret'])) {
            SystemSetting::set('instagram_app_secret', trim((string) $data['instagram_app_secret']), group: 'social_media', isSecret: true);
        }
        if (array_key_exists('instagram_account_id', $data)) {
            SystemSetting::set('instagram_account_id', trim((string) $data['instagram_account_id']), 'social_media');
        }
        if (array_key_exists('instagram_username', $data)) {
            SystemSetting::set('instagram_username', trim((string) $data['instagram_username']), 'social_media');
        }
        if (! empty($data['instagram_access_token'])) {
            SystemSetting::set('instagram_access_token', trim((string) $data['instagram_access_token']), group: 'social_media', isSecret: true);
        }
    }

    /**
     * Verify connection to Instagram API for Business / Creator account.
     */
    public function verifyInstagramCredentials(?string $token = null): array
    {
        $token = $token ?: (string) (SystemSetting::get('instagram_access_token') ?: SystemSetting::get('social_media_app_token', ''));
        if (empty($token)) {
            return [
                'success' => false,
                'error'   => 'Token Akses Instagram belum dikonfigurasi.',
            ];
        }

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(8)->get('https://graph.instagram.com/v21.0/me', [
                'fields'       => 'id,username,account_type,media_count,profile_picture_url',
                'access_token' => $token,
            ]);

            if ($response->successful()) {
                $data = (array) $response->json();
                if (! empty($data['username'])) {
                    SystemSetting::set('instagram_username', (string) $data['username'], 'social_media');
                    SystemSetting::set('instagram_graph_user_id', (string) ($data['id'] ?? ''), 'social_media');
                    SystemSetting::set('instagram_account_type', (string) ($data['account_type'] ?? 'MEDIA_CREATOR'), 'social_media');
                    SystemSetting::set('instagram_media_count', (string) ($data['media_count'] ?? '0'), 'social_media');
                    if (! empty($data['profile_picture_url'])) {
                        SystemSetting::set('instagram_profile_picture_url', (string) $data['profile_picture_url'], 'social_media');
                    }
                    SystemSetting::set('instagram_status', 'active', 'social_media');
                    SystemSetting::set('instagram_verified_at', now()->toIso8601String(), 'social_media');
                }

                return [
                    'success' => true,
                    'data'    => $data,
                ];
            }

            $errMsg = $response->json('error.message') ?? ('HTTP ' . $response->status() . ' - Autentikasi token Instagram ditolak.');

            return [
                'success' => false,
                'error'   => $errMsg,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error'   => 'Koneksi ke server Instagram Graph API gagal: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get overall platform statistics for superadmin dashboard.
     */
    public function getPlatformSummary(): array
    {
        $accounts = SocialMediaAccount::all();

        return [
            'total_connected_merchants' => $accounts->pluck('business_id')->unique()->count(),
            'total_accounts'            => $accounts->count(),
            'facebook_pages_count'      => $accounts->where('platform', 'facebook')->count(),
            'instagram_accounts_count'  => $accounts->where('platform', 'instagram')->count(),
            'threads_accounts_count'    => $accounts->where('platform', 'threads')->count(),
            'tiktok_accounts_count'     => $accounts->where('platform', 'tiktok')->count(),
            'total_posts'               => SocialMediaPost::where('status', 'published')->count(),
        ];
    }

    public function __construct(
        protected ?\App\Domain\SocialMedia\Clients\MetaSocialMediaClient $metaClient = null
    ) {
        $this->metaClient = $metaClient ?? app(\App\Domain\SocialMedia\Clients\MetaSocialMediaClient::class);
    }

    /**
     * Get paginated merchants with their social media connection statuses.
     */
    public function getConnectedMerchantsList(int $perPage = 15)
    {
        return Business::with(['socialMediaAccounts' => function ($q) {
            $q->where('status', 'active');
        }])
        ->whereHas('socialMediaAccounts')
        ->paginate($perPage);
    }

    /**
     * Get paginated platform posts for superadmin cockpit.
     */
    public function getPlatformPosts(int $perPage = 12)
    {
        return SocialMediaPost::query()
            ->where(function ($q) {
                $q->where('is_platform', true)->orWhereNull('business_id');
            })
            ->with(['admin', 'media', 'comments'])
            ->latest()
            ->paginate($perPage, ['*'], 'posts_page');
    }

    /**
     * Create and publish/schedule a new post on the platform's official social media (supports multiple channels and per-channel scheduling).
     */
    public function createAndPublishPlatformPost(\App\Models\Admin $admin, array $data): SocialMediaPost
    {
        $platforms = ! empty($data['platforms']) ? (array) $data['platforms'] : [($data['platform'] ?? 'instagram')];
        $platforms = array_values(array_unique(array_filter($platforms)));
        if (empty($platforms)) {
            $platforms = ['instagram'];
        }

        $content = (string) ($data['content'] ?? '');
        $mediaType = strtolower((string) ($data['media_type'] ?? 'image'));
        $mediaUrls = (array) ($data['media_urls'] ?? []);
        $primaryPlatform = $platforms[0];

        $timingMode = (string) ($data['timing_mode'] ?? (! empty($data['scheduled_at']) ? 'schedule_all' : 'now'));
        $globalScheduledAt = ! empty($data['scheduled_at']) ? \Illuminate\Support\Carbon::parse($data['scheduled_at']) : null;
        $platformTimings = (array) ($data['platform_timing'] ?? []);
        $platformScheduledAts = (array) ($data['platform_scheduled_at'] ?? []);

        $post = SocialMediaPost::create([
            'admin_id'          => $admin->id,
            'business_id'       => null,
            'is_platform'       => true,
            'platform'          => $primaryPlatform,
            'content'           => $content,
            'media_type'        => $mediaType,
            'media_urls'        => $mediaUrls,
            'status'            => 'publishing',
            'scheduled_at'      => $globalScheduledAt,
        ]);

        if (! empty($data['local_media_paths'])) {
            $post->update(['local_media_paths' => (array) $data['local_media_paths']]);
        }

        // Create individual targets for each platform channel
        foreach ($platforms as $channel) {
            $channelTiming = 'now';
            $channelSchedTime = null;

            if ($timingMode === 'per_channel') {
                $channelTiming = $platformTimings[$channel] ?? 'now';
                if ($channelTiming === 'schedule' && ! empty($platformScheduledAts[$channel])) {
                    $channelSchedTime = \Illuminate\Support\Carbon::parse($platformScheduledAts[$channel]);
                }
            } elseif ($timingMode === 'schedule_all' || $timingMode === 'schedule') {
                $channelTiming = 'schedule';
                $channelSchedTime = $globalScheduledAt;
            }

            $isTargetScheduled = ($channelTiming === 'schedule') && $channelSchedTime && $channelSchedTime->isFuture();

            $target = SocialPostTarget::create([
                'social_media_post_id'    => $post->id,
                'social_media_account_id' => null,
                'provider'                => in_array($channel, ['facebook', 'instagram', 'threads'], true) ? 'meta' : 'tiktok',
                'channel'                 => $channel,
                'content_type'            => in_array($mediaType, ['video', 'reels']) ? 'video' : 'photo',
                'custom_caption'          => null,
                'status'                  => $isTargetScheduled ? 'scheduled' : 'pending',
                'scheduled_at'            => $isTargetScheduled ? $channelSchedTime : null,
                'retry_count'             => 0,
            ]);

            // If immediate, publish target right now
            if (! $isTargetScheduled) {
                $this->executePlatformPublishTarget($target);
            }
        }

        $post->syncStatusFromTargets();

        return $post->fresh(['targets']);
    }

    /**
     * Execute publishing for a specific platform target.
     */
    public function executePlatformPublishTarget(SocialPostTarget $target): SocialPostTarget
    {
        $post = $target->post;
        if (! $post) {
            $target->update(['status' => 'failed', 'error_message' => 'Pos induk tidak ditemukan.']);
            return $target;
        }

        $target->update(['status' => 'processing']);

        try {
            $platformPostId = null;
            $mediaUrls = (array) ($post->media_urls ?? []);
            $firstMediaUrl = $mediaUrls[0] ?? null;
            $content = $target->getEffectiveCaption();

            if ($target->channel === 'instagram') {
                $igUserId = (string) (SystemSetting::get('instagram_account_id') ?: SystemSetting::get('instagram_graph_user_id') ?: 'me');
                $igToken = (string) (SystemSetting::get('instagram_access_token') ?: SystemSetting::get('social_media_app_token', ''));

                if (empty($igToken)) {
                    throw new \RuntimeException('Token Akses Instagram Platform Cooca belum dikonfigurasi di Pengaturan.');
                }
                if (empty($firstMediaUrl)) {
                    throw new \InvalidArgumentException('Instagram mewajibkan minimal 1 URL media (foto atau video).');
                }

                $igType = in_array($post->media_type, ['video', 'reels']) ? 'REELS' : 'IMAGE';
                $res = $this->metaClient->publishInstagramPost($igUserId, $igToken, $content, $firstMediaUrl, $igType);
                $platformPostId = (string) ($res['id'] ?? '');
            } elseif ($target->channel === 'facebook') {
                $fbPageId = (string) SystemSetting::get('social_media_page_id', '');
                $fbPageToken = (string) SystemSetting::get('social_media_page_token', SystemSetting::get('social_media_app_token', ''));

                if (empty($fbPageToken)) {
                    throw new \RuntimeException('Token Halaman Facebook Platform belum dikonfigurasi.');
                }

                if (! empty($firstMediaUrl)) {
                    $res = $this->metaClient->publishPagePhoto($fbPageId, $fbPageToken, $content, $firstMediaUrl);
                } else {
                    $res = $this->metaClient->publishPageFeed($fbPageId, $fbPageToken, $content);
                }
                $platformPostId = (string) ($res['id'] ?? $res['post_id'] ?? '');
            } elseif ($target->channel === 'threads') {
                $threadsUserId = (string) (SystemSetting::get('threads_user_id') ?: 'me');
                $threadsToken = (string) (SystemSetting::get('threads_access_token') ?: SystemSetting::get('social_media_app_token', ''));
                if (empty($threadsToken)) {
                    throw new \RuntimeException('Token Akses Threads Platform belum dikonfigurasi.');
                }
                $res = $this->metaClient->publishThreadsPost($threadsUserId, $threadsToken, $content, $firstMediaUrl);
                $platformPostId = (string) ($res['id'] ?? '');
            } else {
                // TikTok
                $platformPostId = 'tt_plat_' . (string) \Illuminate\Support\Str::uuid();
            }

            $target->update([
                'status'           => 'published',
                'platform_post_id' => $platformPostId,
                'published_at'     => now(),
                'error_message'    => null,
            ]);

            if (empty($post->platform_post_id)) {
                $post->update([
                    'platform_post_id' => $platformPostId,
                    'published_at'     => $post->published_at ?? now(),
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('AdminSocialMediaService::executePlatformPublishTarget error', [
                'target_id' => $target->id,
                'channel'   => $target->channel,
                'error'     => $e->getMessage(),
            ]);

            $target->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }

        $post->syncStatusFromTargets();

        return $target;
    }

    /**
     * Execute publishing to official Meta/Instagram/TikTok platform account.
     */
    public function executePlatformPublish(SocialMediaPost $post): SocialMediaPost
    {
        if ($post->targets()->exists()) {
            foreach ($post->targets as $target) {
                if ($target->status !== 'published') {
                    $this->executePlatformPublishTarget($target);
                }
            }
            return $post->fresh(['targets']);
        }

        try {
            $platformPostId = null;
            $mediaUrls = (array) ($post->media_urls ?? []);
            $firstMediaUrl = $mediaUrls[0] ?? null;

            if ($post->platform === 'instagram') {
                $igUserId = (string) (SystemSetting::get('instagram_account_id') ?: SystemSetting::get('instagram_graph_user_id') ?: 'me');
                $igToken = (string) (SystemSetting::get('instagram_access_token') ?: SystemSetting::get('social_media_app_token', ''));

                if (empty($igToken)) {
                    throw new \RuntimeException('Token Akses Instagram Platform Cooca belum dikonfigurasi di Pengaturan.');
                }
                if (empty($firstMediaUrl)) {
                    throw new \InvalidArgumentException('Instagram mewajibkan minimal 1 URL media (foto atau video).');
                }

                $igType = in_array($post->media_type, ['video', 'reels']) ? 'REELS' : 'IMAGE';
                $res = $this->metaClient->publishInstagramPost($igUserId, $igToken, $post->content, $firstMediaUrl, $igType);
                $platformPostId = (string) ($res['id'] ?? '');
            } elseif ($post->platform === 'facebook') {
                $fbPageId = (string) SystemSetting::get('social_media_page_id', '');
                $fbPageToken = (string) SystemSetting::get('social_media_page_token', SystemSetting::get('social_media_app_token', ''));

                if (empty($fbPageToken)) {
                    throw new \RuntimeException('Token Halaman Facebook Platform belum dikonfigurasi.');
                }

                if (! empty($firstMediaUrl)) {
                    $res = $this->metaClient->publishPagePhoto($fbPageId, $fbPageToken, $post->content, $firstMediaUrl);
                } else {
                    $res = $this->metaClient->publishPageFeed($fbPageId, $fbPageToken, $post->content);
                }
                $platformPostId = (string) ($res['id'] ?? $res['post_id'] ?? '');
            } else {
                // TikTok or fallback
                $platformPostId = 'tt_plat_' . (string) \Illuminate\Support\Str::uuid();
            }

            $post->update([
                'status'           => 'published',
                'platform_post_id' => $platformPostId,
                'published_at'     => now(),
                'error_message'    => null,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('AdminSocialMediaService::executePlatformPublish error', [
                'post_id' => $post->id,
                'error'   => $e->getMessage(),
            ]);

            $post->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }

        return $post;
    }

    /**
     * Retry publishing a failed platform post.
     */
    public function retryPlatformPost(SocialMediaPost $post): SocialMediaPost
    {
        $post->update([
            'status'        => 'publishing',
            'error_message' => null,
        ]);

        if ($post->targets()->exists()) {
            foreach ($post->targets as $target) {
                if ($target->status === 'failed') {
                    $this->executePlatformPublishTarget($target);
                }
            }
            return $post->fresh(['targets']);
        }

        return $this->executePlatformPublish($post);
    }

    /**
     * Delete a platform post.
     */
    public function deletePlatformPost(SocialMediaPost $post): bool
    {
        return (bool) $post->delete();
    }

    /**
     * Get platform official comments for community moderation.
     */
    public function getPlatformComments(int $perPage = 20)
    {
        return SocialMediaComment::query()
            ->where(function ($q) {
                $q->where('is_platform', true)->orWhereNull('business_id');
            })
            ->with(['post'])
            ->latest()
            ->paginate($perPage, ['*'], 'comments_page');
    }

    /**
     * Reply to a platform comment.
     */
    public function replyPlatformComment(SocialMediaComment $comment, string $message, \App\Models\Admin $admin): SocialMediaComment
    {
        $token = (string) (SystemSetting::get('instagram_access_token') ?: SystemSetting::get('social_media_app_token', ''));
        if (empty($token)) {
            throw new \RuntimeException('Kredensial token platform belum tersedia.');
        }

        $res = $this->metaClient->replyComment(
            $comment->platform,
            $comment->platform_comment_id,
            $token,
            $message
        );

        $replyCommentId = (string) ($res['id'] ?? (string) \Illuminate\Support\Str::uuid());

        $comment->update(['status' => 'replied']);

        return SocialMediaComment::create([
            'business_id'             => null,
            'is_platform'             => true,
            'social_media_post_id'    => $comment->social_media_post_id,
            'platform'                => $comment->platform,
            'platform_comment_id'     => $replyCommentId,
            'platform_post_id'        => $comment->platform_post_id,
            'parent_comment_id'       => $comment->platform_comment_id,
            'from_name'               => 'Cooca Indonesia (Admin)',
            'message'                 => $message,
            'is_from_page'            => true,
            'status'                  => 'replied',
            'created_time'            => now(),
        ]);
    }
}
