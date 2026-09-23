<?php

declare(strict_types=1);

use App\Models\Post;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Post::count() === 0) {
            try {
                Artisan::call('blog:import-articles', ['--force' => true]);
                Log::info('Migration 2026_09_24_000001_seed_umkm_blog_articles: 100 articles imported successfully.');
            } catch (\Throwable $e) {
                Log::warning('Migration 2026_09_24_000001_seed_umkm_blog_articles failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally keep articles to avoid data loss
    }
};
