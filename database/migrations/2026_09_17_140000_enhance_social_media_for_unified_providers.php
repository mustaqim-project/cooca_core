<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Extend social_media_accounts for multiple providers (Meta, TikTok)
        Schema::table('social_media_accounts', function (Blueprint $table) {
            $table->string('provider', 30)->default('meta')->after('business_id')->comment('meta, tiktok');
            $table->text('refresh_token')->nullable()->after('access_token')->comment('Encrypted refresh token for TikTok OAuth');
            $table->timestamp('refresh_token_expires_at')->nullable()->after('token_expires_at');
            $table->json('scopes')->nullable()->after('metadata');

            $table->index(['business_id', 'provider', 'status'], 'sm_acc_biz_prov_stat_idx');
        });

        // 2. Create social_post_targets for multi-channel targeting and partial success
        Schema::create('social_post_targets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('social_media_post_id')->constrained('social_media_posts')->cascadeOnDelete();
            $table->foreignUuid('social_media_account_id')->constrained('social_media_accounts')->cascadeOnDelete();
            $table->string('provider', 30)->comment('meta, tiktok');
            $table->string('channel', 30)->comment('facebook, instagram, threads, tiktok');
            $table->string('content_type', 30)->default('feed')->comment('feed, photo, video, reel, story, carousel, text');
            $table->text('custom_caption')->nullable()->comment('Channel-specific caption override');
            $table->string('status', 30)->default('pending')->index()->comment('pending, processing, published, failed');
            $table->string('platform_post_id', 150)->nullable()->index();
            $table->text('error_message')->nullable();
            $table->json('metrics')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('retry_count')->default(0);
            $table->timestamps();

            $table->index(['social_media_post_id', 'status'], 'sm_targets_post_stat_idx');
            $table->index(['social_media_account_id', 'status'], 'sm_targets_acc_stat_idx');
        });

        // 3. Create social_post_media for ordered multi-media & Instagram Carousel (2-10 items)
        Schema::create('social_post_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('social_media_post_id')->constrained('social_media_posts')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(1)->comment('1-indexed sort order for carousel items');
            $table->string('media_type', 20)->default('image')->comment('image, video');
            $table->text('media_url')->comment('Public accessible media URL');
            $table->text('local_path')->nullable()->comment('Temporary local storage path to auto-purge');
            $table->unsignedBigInteger('file_size')->nullable()->comment('File size in bytes');
            $table->json('metadata')->nullable()->comment('width, height, duration, aspect_ratio');
            $table->timestamps();

            $table->index(['social_media_post_id', 'sort_order'], 'sm_media_post_sort_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_post_media');
        Schema::dropIfExists('social_post_targets');

        Schema::table('social_media_accounts', function (Blueprint $table) {
            $table->dropIndex('sm_acc_biz_prov_stat_idx');
            $table->dropColumn([
                'provider',
                'refresh_token',
                'refresh_token_expires_at',
                'scopes',
            ]);
        });
    }
};
