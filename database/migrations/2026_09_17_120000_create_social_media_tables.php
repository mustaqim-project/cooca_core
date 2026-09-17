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
        Schema::create('social_media_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('platform', 30)->comment('facebook, instagram, threads');
            $table->string('account_id', 100)->index()->comment('Page ID, IG User ID, or Threads User ID from Meta');
            $table->string('account_name')->comment('Page or account name');
            $table->string('username', 100)->nullable()->comment('Handle or @username');
            $table->text('profile_picture_url')->nullable();
            $table->text('access_token')->comment('Encrypted permanent Page or long-lived Token');
            $table->string('token_type', 50)->default('page_token');
            $table->timestamp('token_expires_at')->nullable()->comment('Null for never-expiring permanent page tokens');
            $table->string('status', 30)->default('active')->index()->comment('active, disconnected, expired, error');
            $table->json('settings')->nullable();
            $table->json('metadata')->nullable()->comment('Raw Meta profile snapshot, followers, category, etc.');
            $table->timestamps();

            $table->unique(['business_id', 'platform', 'account_id'], 'sm_accounts_biz_plat_acc_unique');
            $table->index(['business_id', 'status']);
        });

        Schema::create('social_media_posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('social_media_account_id')->constrained('social_media_accounts')->cascadeOnDelete();
            $table->string('platform', 30)->comment('facebook, instagram, threads');
            $table->string('platform_post_id', 150)->nullable()->index()->comment('Meta FB Post ID / IG Media ID / Threads Post ID');
            $table->text('content')->comment('Caption or post message');
            $table->string('media_type', 30)->default('text')->comment('text, image, video, carousel');
            $table->json('media_urls')->nullable()->comment('Public accessible media URLs');
            $table->string('status', 30)->default('draft')->index()->comment('draft, scheduled, publishing, published, failed');
            $table->text('error_message')->nullable();
            $table->json('metrics')->nullable()->comment('impressions, reach, likes, comments, shares, saved');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'status']);
        });

        Schema::create('social_media_comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('social_media_account_id')->constrained('social_media_accounts')->cascadeOnDelete();
            $table->foreignUuid('social_media_post_id')->nullable()->constrained('social_media_posts')->nullOnDelete();
            $table->string('platform', 30)->comment('facebook, instagram, threads');
            $table->string('platform_comment_id', 150)->index();
            $table->string('platform_post_id', 150)->index();
            $table->string('parent_comment_id', 150)->nullable()->index();
            $table->string('from_id', 150)->nullable();
            $table->string('from_name')->nullable();
            $table->text('message');
            $table->boolean('is_from_page')->default(false);
            $table->string('status', 30)->default('unread')->index()->comment('unread, read, replied, hidden');
            $table->timestamp('created_time')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'status']);
            $table->index(['platform_post_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_media_comments');
        Schema::dropIfExists('social_media_posts');
        Schema::dropIfExists('social_media_accounts');
    }
};
