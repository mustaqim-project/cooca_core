<?php

declare(strict_types=1);

namespace App\Domain\SocialMedia;

use App\Domain\SocialMedia\Clients\MetaSocialMediaClient;
use App\Domain\SocialMedia\SocialMediaManager;
use App\Models\Business;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaComment;
use App\Models\SocialMediaPost;
use App\Domain\Storage\StorageTrackingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SocialMediaService
{
    protected SocialMediaManager $socialMediaManager;

    public function __construct(
        protected MetaSocialMediaClient $client,
        ?SocialMediaManager $socialMediaManager = null
    ) {
        $this->socialMediaManager = $socialMediaManager ?? app(SocialMediaManager::class);
    }

    public function getClient(): MetaSocialMediaClient
    {
        return $this->client;
    }

    /**
     * Connect Facebook Pages and linked Instagram Business Accounts for a merchant tenant.
     *
     * @param  list<array<string, mixed>>  $pages
     * @return list<SocialMediaAccount>
     */
    public function connectPages(Business $business, array $pages): array
    {
        $connectedAccounts = [];

        DB::transaction(function () use ($business, $pages, &$connectedAccounts) {
            foreach ($pages as $page) {
                $pageId = (string) ($page['id'] ?? $page['page_id'] ?? '');
                $pageName = (string) ($page['name'] ?? $page['page_name'] ?? 'Facebook Page');
                $pageToken = (string) ($page['access_token'] ?? $page['page_access_token'] ?? '');
                $profilePic = (string) (data_get($page, 'picture.data.url') ?: ($page['profile_picture_url'] ?? ''));

                if (empty($pageId) || empty($pageToken)) {
                    continue;
                }

                // 1. Save or update Facebook Page Account
                $fbAccount = SocialMediaAccount::updateOrCreate(
                    [
                        'business_id' => $business->id,
                        'platform'    => 'facebook',
                        'account_id'  => $pageId,
                    ],
                    [
                        'account_name'        => $pageName,
                        'username'            => null,
                        'profile_picture_url' => $profilePic ?: null,
                        'access_token'        => $pageToken,
                        'token_type'          => 'page_token',
                        'token_expires_at'    => null, // Permanent Page Token
                        'status'              => 'active',
                        'metadata'            => [
                            'category' => $page['category'] ?? null,
                            'tasks'    => $page['tasks'] ?? [],
                        ],
                    ]
                );
                $connectedAccounts[] = $fbAccount;

                // 2. Check & save linked Instagram Business Account if available
                $igData = $page['instagram_business_account'] ?? $page['instagram'] ?? null;
                if (! empty($igData['id'])) {
                    $igId = (string) $igData['id'];
                    $igName = (string) ($igData['name'] ?? $pageName);
                    $igUsername = (string) ($igData['username'] ?? '');
                    $igPic = (string) ($igData['profile_picture_url'] ?? $profilePic);

                    $igAccount = SocialMediaAccount::updateOrCreate(
                        [
                            'business_id' => $business->id,
                            'platform'    => 'instagram',
                            'account_id'  => $igId,
                        ],
                        [
                            'account_name'        => $igName,
                            'username'            => $igUsername ? "@{$igUsername}" : null,
                            'profile_picture_url' => $igPic ?: null,
                            'access_token'        => $pageToken, // Uses parent Page Access Token
                            'token_type'          => 'page_token',
                            'token_expires_at'    => null,
                            'status'              => 'active',
                            'metadata'            => [
                                'parent_page_id' => $pageId,
                            ],
                        ]
                    );
                    $connectedAccounts[] = $igAccount;
                }
            }
        });

        return $connectedAccounts;
    }

    /**
     * Publish a post to its designated platform.
     */
    public function publishPost(Business $business, SocialMediaPost $post): SocialMediaPost
    {
        // Tenant Isolation Check
        if ($post->business_id !== $business->id) {
            throw new \InvalidArgumentException('Post does not belong to the active business context.');
        }

        $account = $post->account;
        if (! $account || ! $account->isConnected()) {
            $post->update([
                'status'        => 'failed',
                'error_message' => 'Akun media sosial tidak aktif atau belum terhubung.',
            ]);
            return $post;
        }

        $post->update(['status' => 'publishing']);

        try {
            $firstMediaUrl = ! empty($post->media_urls) ? $post->media_urls[0] : null;
            $mediaType = strtolower((string) ($post->media_type ?: 'text'));

            if ($post->platform === 'facebook') {
                if ($mediaType === 'video' || $mediaType === 'reels') {
                    if (empty($firstMediaUrl)) {
                        throw new \InvalidArgumentException('Video Facebook mewajibkan berkas video yang valid.');
                    }
                    $res = $this->client->publishFacebookVideo(
                        $account->account_id,
                        $account->access_token,
                        $post->content,
                        $firstMediaUrl
                    );
                    $platformPostId = (string) ($res['id'] ?? '');
                } else {
                    $res = $this->client->publishFacebookPost(
                        $account->account_id,
                        $account->access_token,
                        $post->content,
                        null,
                        $firstMediaUrl
                    );
                    $platformPostId = (string) ($res['id'] ?? $res['post_id'] ?? '');
                }
            } elseif ($post->platform === 'instagram') {
                if (empty($firstMediaUrl)) {
                    throw new \InvalidArgumentException('Instagram mewajibkan minimal 1 media (foto/video) untuk dipublikasikan.');
                }
                $igType = ($mediaType === 'reels' || $mediaType === 'video') ? 'REELS' : 'IMAGE';
                $res = $this->client->publishInstagramPost(
                    $account->account_id,
                    $account->access_token,
                    $post->content,
                    $firstMediaUrl,
                    $igType
                );
                $platformPostId = (string) ($res['id'] ?? '');
            } elseif ($post->platform === 'threads') {
                $threadsType = ($mediaType === 'video' || $mediaType === 'reels') ? 'VIDEO' : (! empty($firstMediaUrl) ? 'IMAGE' : 'TEXT');
                $res = $this->client->publishThreadsPost(
                    $account->account_id,
                    $account->access_token,
                    $post->content,
                    $firstMediaUrl,
                    $threadsType
                );
                $platformPostId = (string) ($res['id'] ?? '');
            } else {
                throw new \InvalidArgumentException("Platform [{$post->platform}] tidak didukung.");
            }

            // AUTO-DELETE: Purge temporary local files from Cooca server storage
            if (! empty($post->local_media_paths) && is_array($post->local_media_paths)) {
                foreach ($post->local_media_paths as $localPath) {
                    try {
                        Storage::disk('public')->delete($localPath);
                        Log::info('SocialMedia: Auto-deleted temporary media file from Cooca server', [
                            'post_id' => $post->id,
                            'path'    => $localPath,
                        ]);
                    } catch (\Throwable $e) {
                        Log::warning('SocialMedia: Failed to delete temporary media file', [
                            'post_id' => $post->id,
                            'path'    => $localPath,
                            'error'   => $e->getMessage(),
                        ]);
                    }
                }
            }

            $post->update([
                'status'            => 'published',
                'platform_post_id'  => $platformPostId,
                'local_media_paths' => null,
                'published_at'      => now(),
                'error_message'     => null,
            ]);
        } catch (\Throwable $e) {
            Log::error('SocialMediaService::publishPost error', [
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
     * Reply to a social media comment.
     */
    public function replyComment(Business $business, SocialMediaComment $comment, string $message): SocialMediaComment
    {
        if ($comment->business_id !== $business->id) {
            throw new \InvalidArgumentException('Comment does not belong to the active business context.');
        }

        $account = $comment->account;
        if (! $account || ! $account->isConnected()) {
            throw new \RuntimeException('Akun media sosial terkait tidak aktif.');
        }

        if (in_array(strtolower((string) $comment->platform), ['messenger', 'instagram', 'instagram_dm'], true) && ! empty($comment->from_id)) {
            $res = $this->client->replyComment(
                $comment->platform,
                $comment->platform_comment_id,
                $account->access_token,
                $message,
                $comment->from_id
            );
        } else {
            $res = $this->client->replyComment(
                $comment->platform,
                $comment->platform_comment_id,
                $account->access_token,
                $message
            );
        }

        $replyCommentId = (string) ($res['id'] ?? '');

        // Record child reply comment
        $replyRecord = SocialMediaComment::create([
            'business_id'             => $business->id,
            'social_media_account_id' => $account->id,
            'social_media_post_id'    => $comment->social_media_post_id,
            'platform'                => $comment->platform,
            'platform_comment_id'     => $replyCommentId ?: (string) \Illuminate\Support\Str::uuid(),
            'platform_post_id'        => $comment->platform_post_id,
            'parent_comment_id'       => $comment->platform_comment_id,
            'from_id'                 => $account->account_id,
            'from_name'               => $account->account_name,
            'message'                 => $message,
            'is_from_page'            => true,
            'status'                  => 'replied',
            'created_time'            => now(),
        ]);

        $comment->update(['status' => 'replied']);

        return $replyRecord;
    }

    /**
     * Sync performance metrics for a published post across all target channels.
     */
    public function syncPostMetrics(Business $business, SocialMediaPost $post): array
    {
        if ($post->business_id !== $business->id) {
            return $post->metrics ?? [];
        }

        $post->loadMissing(['targets.account', 'account']);
        $totalMetrics = [
            'impressions' => 0,
            'reach'       => 0,
            'likes'       => 0,
            'comments'    => 0,
            'shares'      => 0,
            'saved'       => 0,
        ];

        // 1. If post has multi-channel targets, sync each target independently
        if ($post->targets->isNotEmpty()) {
            foreach ($post->targets as $target) {
                if (! $target->platform_post_id || ! $target->account || ! $target->account->isConnected()) {
                    continue;
                }

                $provider = $this->socialMediaManager->getProviderForChannel($target->channel);
                try {
                    $targetMetrics = $provider->syncMetrics($target->account, $target->platform_post_id);
                    $target->update(['metrics' => $targetMetrics]);

                    foreach (['impressions', 'reach', 'likes', 'comments', 'shares', 'saved'] as $key) {
                        $totalMetrics[$key] += (int) ($targetMetrics[$key] ?? 0);
                    }
                } catch (\Throwable $e) {
                    Log::warning("Failed syncing target metrics for target {$target->id}: {$e->getMessage()}");
                }
            }
        } elseif ($post->platform_post_id && $post->account && $post->account->isConnected()) {
            // Fallback for single account posts (e.g. Meta)
            $provider = $this->socialMediaManager->getProviderForChannel($post->platform);
            try {
                $totalMetrics = $provider->syncMetrics($post->account, $post->platform_post_id);
            } catch (\Throwable $e) {
                Log::warning("Failed syncing post metrics for post {$post->id}: {$e->getMessage()}");
            }
        }

        $post->update(['metrics' => $totalMetrics]);

        return $totalMetrics;
    }

    /**
     * Sync organic metrics and profile information for a connected social media account.
     */
    public function syncAccountMetrics(Business $business, SocialMediaAccount $account): array
    {
        if ($account->business_id !== $business->id || ! $account->isConnected()) {
            return (array) data_get($account->metadata, 'metrics', []);
        }

        $channel = strtolower((string) $account->platform);
        $metrics = [];

        try {
            if ($channel === 'facebook') {
                $pageMetrics = $this->client->getFacebookPageMetrics($account->account_id, $account->access_token);
                $pageData = $pageMetrics['page'] ?? [];
                $metrics = [
                    'followers'     => (int) ($pageData['followers_count'] ?? $pageData['fan_count'] ?? 0),
                    'fans'          => (int) ($pageData['fan_count'] ?? 0),
                    'talking_about' => (int) ($pageData['talking_about_count'] ?? 0),
                    'category'      => (string) ($pageData['category'] ?? ($account->metadata['category'] ?? 'Facebook Page')),
                    'recent_posts'  => $pageMetrics['recent_posts'] ?? [],
                    'synced_at'     => now()->toIso8601String(),
                ];
            } elseif ($channel === 'instagram') {
                $igMetrics = $this->client->getInstagramAccountMetrics($account->account_id, $account->access_token);
                $profile = $igMetrics['profile'] ?? [];
                $metrics = [
                    'followers'          => (int) ($profile['followers_count'] ?? 0),
                    'following'          => (int) ($profile['follows_count'] ?? 0),
                    'media_count'        => (int) ($profile['media_count'] ?? 0),
                    'total_likes'        => (int) ($igMetrics['total_likes'] ?? 0),
                    'total_comments'     => (int) ($igMetrics['total_comments'] ?? 0),
                    'total_interactions' => (int) ($igMetrics['total_interactions'] ?? 0),
                    'engagement_rate'    => (float) ($igMetrics['engagement_rate'] ?? 0.0),
                    'reels_count'        => (int) ($igMetrics['reels_count'] ?? 0),
                    'feed_count'         => (int) ($igMetrics['feed_count'] ?? 0),
                    'quota_usage'        => (int) ($igMetrics['quota_usage'] ?? 0),
                    'recent_media'       => $igMetrics['recent_media'] ?? [],
                    'synced_at'          => now()->toIso8601String(),
                ];

                if (! empty($profile['username']) && empty($account->username)) {
                    $account->username = "@{$profile['username']}";
                }
                if (! empty($profile['profile_picture_url'])) {
                    $account->profile_picture_url = $profile['profile_picture_url'];
                }
            } elseif ($channel === 'tiktok') {
                $provider = $this->socialMediaManager->getProvider('tiktok');
                $creatorInfo = $provider->getCreatorInfo($account);

                // Fetch user stats (followers, likes, video count) from separate endpoint
                $userInfo = [];
                try {
                    $userInfo = $this->socialMediaManager->getTikTokProvider()->getClient()->getUserInfo((string) $account->access_token);
                } catch (\Throwable $e) {
                    Log::warning("TikTok getUserInfo failed for account {$account->id}: {$e->getMessage()}");
                }

                $metrics = [
                    'nickname'        => $creatorInfo['creator_nickname'] ?? $userInfo['display_name'] ?? $account->account_name,
                    'username'        => $creatorInfo['creator_username'] ?? $userInfo['username'] ?? $account->username,
                    'avatar_url'      => $creatorInfo['creator_avatar_url'] ?? $userInfo['avatar_url'] ?? $account->profile_picture_url,
                    'privacy_level'   => $creatorInfo['privacy_level_options'] ?? [],
                    'duet_disabled'   => (bool) ($creatorInfo['duet_disabled'] ?? false),
                    'stitch_disabled' => (bool) ($creatorInfo['stitch_disabled'] ?? false),
                    // Stats from user/info/ endpoint
                    'followers_count' => (int) ($userInfo['follower_count'] ?? 0),
                    'following_count' => (int) ($userInfo['following_count'] ?? 0),
                    'likes_count'     => (int) ($userInfo['likes_count'] ?? 0),
                    'video_count'     => (int) ($userInfo['video_count'] ?? 0),
                    'is_verified'     => (bool) ($userInfo['is_verified'] ?? false),
                    'synced_at'       => now()->toIso8601String(),
                ];

                if (! empty($metrics['avatar_url'])) {
                    $account->profile_picture_url = $metrics['avatar_url'];
                }
            } elseif ($channel === 'linkedin') {
                $provider = $this->socialMediaManager->getProvider('linkedin');
                $profile = $provider->getCreatorInfo($account);
                $metrics = [
                    'name'      => $profile['name'] ?? $account->account_name,
                    'email'     => $profile['email'] ?? $account->username,
                    'picture'   => $profile['picture'] ?? $account->profile_picture_url,
                    'sub'       => $profile['sub'] ?? $account->account_id,
                    'synced_at' => now()->toIso8601String(),
                ];

                if (! empty($metrics['picture'])) {
                    $account->profile_picture_url = $metrics['picture'];
                }
            }

            $currentMeta = $account->metadata ?? [];
            $currentMeta['metrics'] = $metrics;
            $currentMeta['metrics_synced_at'] = now()->toIso8601String();
            $account->metadata = $currentMeta;
            $account->save();
        } catch (\Throwable $e) {
            Log::warning("Failed syncing account metrics for account {$account->id} ({$channel}): {$e->getMessage()}");
        }

        return $metrics;
    }

    /**
     * Synchronize metrics for all active accounts of a business.
     */
    public function syncAllAccountMetrics(Business $business): array
    {
        $accounts = SocialMediaAccount::where('business_id', $business->id)
            ->where('status', 'active')
            ->get();

        $results = [];
        foreach ($accounts as $account) {
            $results[$account->id] = $this->syncAccountMetrics($business, $account);
        }

        return $results;
    }

    /**
     * Disconnect a social media account.
     */
    public function disconnectAccount(Business $business, string $accountId): bool
    {
        $account = SocialMediaAccount::where('business_id', $business->id)
            ->where('id', $accountId)
            ->first();

        if (! $account) {
            return false;
        }

        $account->update(['status' => 'disconnected']);

        return true;
    }

    /**
     * Get aggregate cockpit summary for a merchant.
     */
    public function getSummary(Business $business): array
    {
        $accounts = SocialMediaAccount::where('business_id', $business->id)
            ->where('status', 'active')
            ->get();

        $postsCount = SocialMediaPost::where('business_id', $business->id)
            ->where('status', 'published')
            ->count();

        $unreadComments = SocialMediaComment::where('business_id', $business->id)
            ->where('status', 'unread')
            ->count();

        return [
            'total_connected' => $accounts->count(),
            'has_facebook'    => $accounts->where('platform', 'facebook')->isNotEmpty(),
            'has_instagram'   => $accounts->where('platform', 'instagram')->isNotEmpty(),
            'has_threads'     => $accounts->where('platform', 'threads')->isNotEmpty(),
            'has_tiktok'      => $accounts->where('platform', 'tiktok')->isNotEmpty(),
            'has_linkedin'    => $accounts->where('platform', 'linkedin')->isNotEmpty(),
            'total_posts'     => $postsCount,
            'unread_comments' => $unreadComments,
        ];
    }

    /**
     * Purge temporary local media files and record quota reduction in StorageTrackingService.
     */
    public function purgePostLocalMedia(SocialMediaPost $post): void
    {
        $disk = Storage::disk('public');
        $trackingService = app(StorageTrackingService::class);

        // 1. Purge from post media relation
        $post->loadMissing('media');
        foreach ($post->media as $media) {
            if (! empty($media->local_path) && $disk->exists($media->local_path)) {
                try {
                    $trackingService->recordDeletion($media->local_path);
                    $disk->delete($media->local_path);
                    $media->update(['local_path' => null]);
                    Log::info("[SocialMediaService] Purged local media file: {$media->local_path}");
                } catch (\Throwable $e) {
                    Log::warning("[SocialMediaService] Could not delete local media: {$e->getMessage()}");
                }
            }
        }

        // 2. Purge from legacy local_media_paths
        if (! empty($post->local_media_paths) && is_array($post->local_media_paths)) {
            foreach ($post->local_media_paths as $localPath) {
                if (! empty($localPath) && $disk->exists($localPath)) {
                    try {
                        $trackingService->recordDeletion($localPath);
                        $disk->delete($localPath);
                        Log::info("[SocialMediaService] Purged legacy local path: {$localPath}");
                    } catch (\Throwable $e) {
                        Log::warning("[SocialMediaService] Could not delete legacy local path: {$e->getMessage()}");
                    }
                }
            }
            $post->update(['local_media_paths' => null]);
        }
    }

    /**
     * Synchronize incoming inbox messages and comments from connected Meta accounts (Facebook & Instagram).
     *
     * @return array{success: bool, message: string, synced_count: int, accounts_count: int}
     */
    public function syncMetaInbox(Business $business): array
    {
        $accounts = SocialMediaAccount::where('business_id', $business->id)
            ->where('status', 'active')
            ->whereIn('platform', ['facebook', 'instagram'])
            ->get();

        if ($accounts->isEmpty()) {
            return [
                'success'        => false,
                'message'        => 'Belum ada akun Facebook Page atau Instagram yang terhubung aktif. Silakan hubungkan akun di Saluran & Akun Media Sosial.',
                'synced_count'   => 0,
                'accounts_count' => 0,
            ];
        }

        $syncedCount = 0;

        foreach ($accounts as $account) {
            $token = (string) ($account->access_token ?? '');
            $accountId = (string) ($account->account_id ?? '');

            if (empty($token) || empty($accountId)) {
                continue;
            }

            if ($account->platform === 'facebook') {
                // 1. Sync Facebook Page Messenger Conversations
                try {
                    $conversations = $this->client->getPageConversations($accountId, $token);
                    foreach ($conversations as $conv) {
                        $convId = (string) ($conv['id'] ?? '');
                        $messages = (array) data_get($conv, 'messages.data', []);

                        if (! empty($messages)) {
                            foreach ($messages as $msg) {
                                $fromId = (string) data_get($msg, 'from.id');
                                $fromName = (string) (data_get($msg, 'from.name') ?: 'Pengguna Facebook');
                                $text = (string) ($msg['message'] ?? '');
                                $msgId = (string) ($msg['id'] ?? '');

                                if (! empty($msgId) && ! empty($text)) {
                                    $isFromPage = ($fromId === $accountId);
                                    SocialMediaComment::updateOrCreate(
                                        [
                                            'business_id'         => $business->id,
                                            'platform'            => 'messenger',
                                            'platform_comment_id' => $msgId,
                                        ],
                                        [
                                            'social_media_account_id' => $account->id,
                                            'social_media_post_id'    => null,
                                            'platform_post_id'        => $convId ?: 'post_general',
                                            'parent_comment_id'       => null,
                                            'from_id'                 => $fromId,
                                            'from_name'               => $isFromPage ? $account->account_name : $fromName,
                                            'message'                 => $text,
                                            'is_from_page'            => $isFromPage,
                                            'status'                  => $isFromPage ? 'replied' : 'unread',
                                            'created_time'            => isset($msg['created_time']) ? \Illuminate\Support\Carbon::parse($msg['created_time']) : now(),
                                        ]
                                    );
                                    $syncedCount++;
                                }
                            }
                        } elseif (! empty($conv['snippet'])) {
                            $sender = data_get($conv, 'senders.data.0', []);
                            $senderId = (string) ($sender['id'] ?? '');
                            $senderName = (string) ($sender['name'] ?? 'Pengguna Facebook');
                            $isFromPage = ($senderId === $accountId);
                            SocialMediaComment::updateOrCreate(
                                [
                                    'business_id'         => $business->id,
                                    'platform'            => 'messenger',
                                    'platform_comment_id' => 'fb_conv_' . $convId,
                                ],
                                [
                                    'social_media_account_id' => $account->id,
                                    'social_media_post_id'    => null,
                                    'platform_post_id'        => $convId ?: 'post_general',
                                    'parent_comment_id'       => null,
                                    'from_id'                 => $senderId ?: null,
                                    'from_name'               => $isFromPage ? $account->account_name : $senderName,
                                    'message'                 => $conv['snippet'],
                                    'is_from_page'            => $isFromPage,
                                    'status'                  => $isFromPage ? 'replied' : 'unread',
                                    'created_time'            => isset($conv['updated_time']) ? \Illuminate\Support\Carbon::parse($conv['updated_time']) : now(),
                                ]
                            );
                            $syncedCount++;
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning("[SocialMediaService] Sync FB conversations error: {$e->getMessage()}");
                }

                // 2. Sync Facebook Page Feed Comments
                try {
                    $posts = $this->client->getPageFeedComments($accountId, $token);
                    foreach ($posts as $post) {
                        $postId = (string) ($post['id'] ?? '');
                        $comments = (array) data_get($post, 'comments.data', []);
                        foreach ($comments as $comment) {
                            $fromId = (string) data_get($comment, 'from.id');
                            $fromName = (string) (data_get($comment, 'from.name') ?: 'Pengguna Facebook');
                            $text = (string) ($comment['message'] ?? '');
                            $commentId = (string) ($comment['id'] ?? '');

                            if (! empty($commentId) && ! empty($text)) {
                                $isFromPage = ($fromId === $accountId);
                                SocialMediaComment::updateOrCreate(
                                    [
                                        'business_id'         => $business->id,
                                        'platform'            => 'facebook',
                                        'platform_comment_id' => $commentId,
                                    ],
                                    [
                                        'social_media_account_id' => $account->id,
                                        'social_media_post_id'    => null,
                                        'platform_post_id'        => $postId ?: 'post_general',
                                        'parent_comment_id'       => null,
                                        'from_id'                 => $fromId,
                                        'from_name'               => $isFromPage ? $account->account_name : $fromName,
                                        'message'                 => $text,
                                        'is_from_page'            => $isFromPage,
                                        'status'                  => $isFromPage ? 'replied' : 'unread',
                                        'created_time'            => isset($comment['created_time']) ? \Illuminate\Support\Carbon::parse($comment['created_time']) : now(),
                                    ]
                                );
                                $syncedCount++;
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning("[SocialMediaService] Sync FB feed comments error: {$e->getMessage()}");
                }
            } elseif ($account->platform === 'instagram') {
                // 3. Sync Instagram Media Comments
                try {
                    $mediaList = $this->client->getInstagramMediaComments($accountId, $token);
                    foreach ($mediaList as $media) {
                        $mediaId = (string) ($media['id'] ?? '');
                        $comments = (array) data_get($media, 'comments.data', []);
                        foreach ($comments as $comment) {
                            $commentId = (string) ($comment['id'] ?? '');
                            $text = (string) ($comment['text'] ?? '');
                            $username = (string) ($comment['username'] ?? (data_get($comment, 'from.username') ?: 'Pengguna Instagram'));
                            $fromId = (string) (data_get($comment, 'from.id') ?: $commentId);

                            if (! empty($commentId) && ! empty($text)) {
                                $isFromPage = ($fromId === $accountId);
                                SocialMediaComment::updateOrCreate(
                                    [
                                        'business_id'         => $business->id,
                                        'platform'            => 'instagram_comments',
                                        'platform_comment_id' => $commentId,
                                    ],
                                    [
                                        'social_media_account_id' => $account->id,
                                        'social_media_post_id'    => null,
                                        'platform_post_id'        => $mediaId ?: 'post_general',
                                        'parent_comment_id'       => null,
                                        'from_id'                 => $fromId,
                                        'from_name'               => $isFromPage ? $account->account_name : ('@' . ltrim($username, '@')),
                                        'message'                 => $text,
                                        'is_from_page'            => $isFromPage,
                                        'status'                  => $isFromPage ? 'replied' : 'unread',
                                        'created_time'            => isset($comment['timestamp']) ? \Illuminate\Support\Carbon::parse($comment['timestamp']) : now(),
                                    ]
                                );
                                $syncedCount++;
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning("[SocialMediaService] Sync IG comments error: {$e->getMessage()}");
                }

                // 4. Sync Instagram Direct Conversations (jika akun Instagram Business mengizinkan)
                try {
                    $parentPageId = (string) ($account->metadata['parent_page_id'] ?? '');
                    $igConversations = $this->client->getInstagramConversations($accountId, $token, 15, $parentPageId ?: null);
                    foreach ($igConversations as $conv) {
                        $convId = (string) ($conv['id'] ?? '');
                        $messages = (array) data_get($conv, 'messages.data', []);
                        foreach ($messages as $msg) {
                            $fromId = (string) data_get($msg, 'from.id');
                            $fromUsername = (string) (data_get($msg, 'from.username') ?: (data_get($msg, 'from.name') ?: 'Pengguna Instagram'));
                            $text = (string) ($msg['message'] ?? '');
                            $msgId = (string) ($msg['id'] ?? '');

                            if (! empty($msgId) && ! empty($text)) {
                                $isFromPage = ($fromId === $accountId);
                                SocialMediaComment::updateOrCreate(
                                    [
                                        'business_id'         => $business->id,
                                        'platform'            => 'instagram',
                                        'platform_comment_id' => $msgId,
                                    ],
                                    [
                                        'social_media_account_id' => $account->id,
                                        'social_media_post_id'    => null,
                                        'platform_post_id'        => $convId ?: 'post_general',
                                        'parent_comment_id'       => null,
                                        'from_id'                 => $fromId,
                                        'from_name'               => $isFromPage ? $account->account_name : ('@' . ltrim($fromUsername, '@')),
                                        'message'                 => $text,
                                        'is_from_page'            => $isFromPage,
                                        'status'                  => $isFromPage ? 'replied' : 'unread',
                                        'created_time'            => isset($msg['created_time']) ? \Illuminate\Support\Carbon::parse($msg['created_time']) : now(),
                                    ]
                                );
                                $syncedCount++;
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning("[SocialMediaService] Sync IG direct conversations error: {$e->getMessage()}");
                }
            }
        }

        return [
            'success'        => true,
            'message'        => "Sinkronisasi berhasil. {$syncedCount} pesan & komentar dari Meta berhasil diperbarui ke inbox.",
            'synced_count'   => $syncedCount,
            'accounts_count' => $accounts->count(),
        ];
    }
}
