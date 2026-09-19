<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\SocialMedia\AdminSocialMediaService;
use App\Models\SocialMediaPost;
use Illuminate\Console\Command;

class RetrySocialMediaPostCommand extends Command
{
    protected $signature = 'social-media:retry-post {id?}';

    protected $description = 'Retry publishing a failed social media post';

    public function handle(AdminSocialMediaService $adminService): int
    {
        $id = $this->argument('id');
        $post = $id
            ? SocialMediaPost::find($id)
            : SocialMediaPost::where('status', 'failed')->latest()->first();

        if (! $post) {
            $this->error('No failed post found to retry.');
            return self::FAILURE;
        }

        $this->info("Retrying post {$post->id} (Platform: {$post->platform})...");

        // Clean up media URLs if they contain legacy /public/
        if (is_array($post->media_urls)) {
            $cleaned = array_map(function ($url) {
                return preg_replace('#^(https?://[^/]+)/public/#i', '$1/', (string) $url);
            }, $post->media_urls);
            $post->update(['media_urls' => $cleaned]);
        }

        $adminService->retryPlatformPost($post);

        $post->refresh();
        $this->info("Post status: {$post->status}");
        if ($post->status === 'published') {
            $this->info("SUCCESS! Platform Post ID: {$post->platform_post_id}");
            return self::SUCCESS;
        }

        $target = $post->targets()->latest()->first();
        $this->error("FAILED: " . ($target?->error_message ?? $post->error_message ?? 'Unknown error'));
        return self::FAILURE;
    }
}
