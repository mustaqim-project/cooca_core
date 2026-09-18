<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\SocialMedia\SocialMediaService;
use App\Models\SocialMediaPost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PublishScheduledSocialMediaPostsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'social-media:publish-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish scheduled social media posts whose scheduled_at has arrived and purge temporary server files.';

    /**
     * Execute the console command.
     */
    public function handle(SocialMediaService $socialMediaService, ?\App\Domain\SocialMedia\AdminSocialMediaService $adminSocialMediaService = null): int
    {
        $adminSocialMediaService = $adminSocialMediaService ?? app(\App\Domain\SocialMedia\AdminSocialMediaService::class);
        $now = now();
        $dispatchedCount = 0;

        // 1. Process Due Individual Targets (SocialPostTarget scheduled_at <= now)
        $dueTargets = \App\Models\SocialPostTarget::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', $now)
            ->with(['post.business', 'post.media', 'account'])
            ->get();

        if ($dueTargets->isNotEmpty()) {
            $this->info("Found {$dueTargets->count()} scheduled target(s) ready to publish.");
            foreach ($dueTargets as $target) {
                $post = $target->post;
                if (! $post) {
                    $target->update(['status' => 'failed', 'error_message' => 'Pos induk tidak ditemukan.']);
                    continue;
                }

                if ($post->is_platform) {
                    $this->line("Publishing scheduled platform target {$target->id} ({$target->channel})...");
                    $adminSocialMediaService->executePlatformPublishTarget($target);
                    $dispatchedCount++;
                } else {
                    $this->line("Dispatching scheduled merchant target {$target->id} ({$target->channel})...");
                    $target->update(['status' => 'pending']);
                    \App\Jobs\SocialMedia\PublishSocialMediaTargetJob::dispatch($target->id);
                    $dispatchedCount++;
                }

                $post->syncStatusFromTargets();
            }
        }

        // 2. Process Due Posts (SocialMediaPost scheduled_at <= now)
        $scheduledPosts = SocialMediaPost::where('status', 'scheduled')
            ->where('scheduled_at', '<=', $now)
            ->with(['account', 'business', 'targets'])
            ->get();

        if ($scheduledPosts->isNotEmpty()) {
            $this->info("Found {$scheduledPosts->count()} scheduled post(s) ready to publish.");
            foreach ($scheduledPosts as $post) {
                // If post has targets that were not yet scheduled individually
                if ($post->targets->isNotEmpty()) {
                    foreach ($post->targets as $target) {
                        if ($target->status !== 'published') {
                            if ($target->scheduled_at === null || $target->scheduled_at->isPast()) {
                                if ($post->is_platform) {
                                    $adminSocialMediaService->executePlatformPublishTarget($target);
                                } else {
                                    $target->update(['status' => 'pending']);
                                    \App\Jobs\SocialMedia\PublishSocialMediaTargetJob::dispatch($target->id);
                                }
                                $dispatchedCount++;
                            }
                        }
                    }
                    $post->syncStatusFromTargets();
                } else {
                    // Fallback for posts without target rows
                    if ($post->is_platform) {
                        $this->line("Publishing scheduled platform post ID: {$post->id}...");
                        $adminSocialMediaService->executePlatformPublish($post);
                        $dispatchedCount++;
                    } else {
                        $business = $post->business;
                        if (! $business) {
                            $this->warn("Post {$post->id} has no valid business relation.");
                            continue;
                        }
                        $post->update(['status' => 'publishing']);
                        $this->line("Publishing post ID: {$post->id} to {$post->platform}...");
                        try {
                            $socialMediaService->publishPost($business, $post);
                            $dispatchedCount++;
                        } catch (\Throwable $e) {
                            Log::error("Scheduled publication exception for post {$post->id}: {$e->getMessage()}");
                            $this->error("Exception publishing post {$post->id}: {$e->getMessage()}");
                        }
                    }
                }
            }
        }

        if ($dispatchedCount === 0) {
            $this->info('No scheduled social media posts or targets ready for publication.');
        } else {
            $this->info("Scheduled publication processing complete. Total targets/posts processed: {$dispatchedCount}.");
        }

        return self::SUCCESS;
    }
}
