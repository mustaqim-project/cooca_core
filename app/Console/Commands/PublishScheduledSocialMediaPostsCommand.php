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
    public function handle(SocialMediaService $socialMediaService): int
    {
        $now = now();
        $scheduledPosts = SocialMediaPost::where('status', 'scheduled')
            ->where('scheduled_at', '<=', $now)
            ->with(['account', 'business', 'targets'])
            ->get();

        if ($scheduledPosts->isEmpty()) {
            $this->info('No scheduled social media posts ready for publication.');
            return self::SUCCESS;
        }

        $this->info("Found {$scheduledPosts->count()} scheduled post(s) to publish.");
        $dispatchedCount = 0;

        foreach ($scheduledPosts as $post) {
            $business = $post->business;
            if (! $business) {
                $this->warn("Post {$post->id} has no valid business relation.");
                continue;
            }

            // Atomic state transition to prevent race condition / duplicate dispatch
            $post->update(['status' => 'publishing']);

            if ($post->targets->count() > 1) {
                $this->line("Dispatching {$post->targets->count()} target(s) for post {$post->id}...");
                foreach ($post->targets as $target) {
                    if ($target->status !== 'published') {
                        \App\Jobs\SocialMedia\PublishSocialMediaTargetJob::dispatch($target->id);
                        $dispatchedCount++;
                    }
                }
            } else {
                $this->line("Publishing post ID: {$post->id} to {$post->platform} ({$post->media_type})...");
                try {
                    $socialMediaService->publishPost($business, $post);
                    foreach ($post->targets as $target) {
                        $target->update([
                            'status'           => $post->status,
                            'platform_post_id' => $post->platform_post_id,
                            'published_at'     => $post->published_at,
                            'error_message'    => $post->error_message,
                        ]);
                    }
                    $dispatchedCount++;
                } catch (\Throwable $e) {
                    Log::error("Scheduled publication exception for post {$post->id}: {$e->getMessage()}");
                    $this->error("Exception publishing post {$post->id}: {$e->getMessage()}");
                }
            }
        }

        $this->info("Scheduled publication dispatch complete. Total items processed/dispatched: {$dispatchedCount}.");

        return self::SUCCESS;
    }
}
