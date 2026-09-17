<?php

declare(strict_types=1);

namespace App\Domain\SocialMedia;

use App\Domain\SocialMedia\Clients\MetaSocialMediaClient;
use App\Models\Business;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaComment;
use App\Models\SocialMediaPost;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SocialMediaService
{
    public function __construct(
        protected MetaSocialMediaClient $client
    ) {}

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

        $res = $this->client->replyComment(
            $comment->platform,
            $comment->platform_comment_id,
            $account->access_token,
            $message
        );

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
     * Sync performance metrics for a published post.
     */
    public function syncPostMetrics(Business $business, SocialMediaPost $post): array
    {
        if ($post->business_id !== $business->id || ! $post->platform_post_id) {
            return $post->metrics ?? [];
        }

        $account = $post->account;
        if (! $account || ! $account->isConnected()) {
            return $post->metrics ?? [];
        }

        $metrics = $this->client->getPostInsights(
            $post->platform,
            $post->platform_post_id,
            $account->access_token
        );

        $post->update(['metrics' => $metrics]);

        return $metrics;
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
            'total_posts'     => $postsCount,
            'unread_comments' => $unreadComments,
        ];
    }
}
