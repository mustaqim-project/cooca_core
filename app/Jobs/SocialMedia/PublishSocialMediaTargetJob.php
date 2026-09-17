<?php

declare(strict_types=1);

namespace App\Jobs\SocialMedia;

use App\Domain\SocialMedia\SocialMediaManager;
use App\Models\SocialMediaPost;
use App\Models\SocialPostTarget;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PublishSocialMediaTargetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    /**
     * @var array<int, int>
     */
    public array $backoff = [10, 30, 60];

    public function __construct(
        public string $targetId
    ) {}

    public function handle(SocialMediaManager $socialMediaManager): void
    {
        /** @var SocialPostTarget|null $target */
        $target = SocialPostTarget::with(['post.business', 'post.media', 'account'])
            ->find($this->targetId);

        if (! $target) {
            Log::warning("[PublishSocialMediaTargetJob] Target not found: {$this->targetId}");
            return;
        }

        // Idempotency: skip if already published
        if ($target->status === 'published') {
            Log::info("[PublishSocialMediaTargetJob] Target already published: {$this->targetId}");
            return;
        }

        $post = $target->post;
        if (! $post) {
            $target->update([
                'status'        => 'failed',
                'error_message' => 'Pos induk tidak ditemukan.',
            ]);
            return;
        }

        $account = $target->account;
        if (! $account || ! $account->isConnected()) {
            $target->update([
                'status'        => 'failed',
                'error_message' => 'Akun media sosial tidak aktif atau terputus.',
            ]);
            $this->updateParentPostStatus($post);
            return;
        }

        $target->update(['status' => 'processing']);

        // Prepare media items
        $mediaItems = [];
        if ($post->relationLoaded('media') && $post->media->isNotEmpty()) {
            foreach ($post->media as $media) {
                $mediaItems[] = [
                    'media_url'  => $media->media_url,
                    'media_type' => $media->media_type,
                    'local_path' => $media->local_path,
                    'sort_order' => $media->sort_order,
                ];
            }
        } elseif (! empty($post->media_urls)) {
            foreach ($post->media_urls as $idx => $url) {
                $mediaItems[] = [
                    'media_url'  => $url,
                    'media_type' => $post->media_type ?: 'image',
                    'local_path' => $post->local_media_paths[$idx] ?? null,
                    'sort_order' => $idx + 1,
                ];
            }
        }

        try {
            $provider = $socialMediaManager->getProvider($target->provider);
            $result = $provider->publish($account, $target, $mediaItems);

            $platformPostId = (string) ($result['id'] ?? $result['publish_id'] ?? $result['post_id'] ?? '');

            $target->update([
                'status'           => 'published',
                'platform_post_id' => $platformPostId ?: null,
                'error_message'    => null,
                'published_at'     => now(),
            ]);

            Log::info("[PublishSocialMediaTargetJob] Target {$target->id} successfully published to {$target->channel} via {$target->provider}", [
                'target_id'        => $target->id,
                'platform_post_id' => $platformPostId,
                'channel'          => $target->channel,
            ]);

            $this->updateParentPostStatus($post);
        } catch (\Throwable $e) {
            $target->increment('retry_count');
            $target->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            $this->updateParentPostStatus($post);

            Log::error("[PublishSocialMediaTargetJob] Failed publishing target {$target->id}: {$e->getMessage()}", [
                'target_id' => $target->id,
                'channel'   => $target->channel,
                'attempt'   => $this->attempts(),
            ]);

            throw $e;
        }
    }

    /**
     * Re-calculate parent post status based on all targets and trigger auto-purge if completed.
     */
    protected function updateParentPostStatus(SocialMediaPost $post): void
    {
        $post->load('targets', 'media');
        $targets = $post->targets;

        if ($targets->isEmpty()) {
            return;
        }

        $allPublished = $targets->every(fn (SocialPostTarget $t) => $t->status === 'published');
        $allFailed = $targets->every(fn (SocialPostTarget $t) => $t->status === 'failed');
        $anyFailed = $targets->contains(fn (SocialPostTarget $t) => $t->status === 'failed');
        $anyProcessing = $targets->contains(fn (SocialPostTarget $t) => in_array($t->status, ['pending', 'processing'], true));

        if ($allPublished) {
            $post->update([
                'status'        => 'published',
                'published_at'  => $post->published_at ?? now(),
                'error_message' => null,
            ]);

            // AUTO-PURGE: When all targets have published successfully, purge local files
            $this->purgeLocalMedia($post);
        } elseif ($allFailed && ! $anyProcessing) {
            $firstErr = $targets->first(fn (SocialPostTarget $t) => ! empty($t->error_message))?->error_message;
            $post->update([
                'status'        => 'failed',
                'error_message' => $firstErr ?: 'Seluruh target publikasi gagal.',
            ]);
        } elseif ($anyFailed && ! $anyProcessing) {
            $post->update([
                'status' => 'partially_failed',
            ]);
        } elseif ($anyProcessing) {
            $post->update([
                'status' => 'publishing',
            ]);
        }
    }

    /**
     * Purge temporary local media files from public storage.
     */
    protected function purgeLocalMedia(SocialMediaPost $post): void
    {
        $disk = Storage::disk('public');

        // 1. Purge from post media relation
        if ($post->relationLoaded('media')) {
            foreach ($post->media as $media) {
                if (! empty($media->local_path) && $disk->exists($media->local_path)) {
                    try {
                        $disk->delete($media->local_path);
                        $media->update(['local_path' => null]);
                        Log::info("[PublishSocialMediaTargetJob] Purged local media file: {$media->local_path}");
                    } catch (\Throwable $e) {
                        Log::warning("[PublishSocialMediaTargetJob] Could not delete local media: {$e->getMessage()}");
                    }
                }
            }
        }

        // 2. Purge from legacy local_media_paths
        if (! empty($post->local_media_paths) && is_array($post->local_media_paths)) {
            foreach ($post->local_media_paths as $localPath) {
                if (! empty($localPath) && $disk->exists($localPath)) {
                    try {
                        $disk->delete($localPath);
                        Log::info("[PublishSocialMediaTargetJob] Purged legacy local path: {$localPath}");
                    } catch (\Throwable $e) {
                        Log::warning("[PublishSocialMediaTargetJob] Could not delete legacy local path: {$e->getMessage()}");
                    }
                }
            }
            $post->update(['local_media_paths' => null]);
        }
    }

    /**
     * Handle job failure after exhausting all retry attempts.
     */
    public function failed(?\Throwable $exception): void
    {
        $target = SocialPostTarget::with('post')->find($this->targetId);
        if ($target) {
            $target->update([
                'status'        => 'failed',
                'error_message' => $exception ? $exception->getMessage() : 'Penerbitan konten gagal setelah beberapa kali percobaan.',
            ]);

            if ($target->post) {
                $this->updateParentPostStatus($target->post);
            }
        }
    }

    /**
     * @return list<string>
     */
    public function tags(): array
    {
        return ['social-media', 'target:' . $this->targetId];
    }
}
