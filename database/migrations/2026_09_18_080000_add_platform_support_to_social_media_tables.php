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
        // 1. Extend social_media_accounts to support platform-level accounts
        Schema::table('social_media_accounts', function (Blueprint $table): void {
            $table->uuid('business_id')->nullable()->change();
            if (! Schema::hasColumn('social_media_accounts', 'is_platform')) {
                $table->boolean('is_platform')->default(false)->after('business_id')->index();
            }
        });

        // 2. Extend social_media_posts for platform / admin posts
        Schema::table('social_media_posts', function (Blueprint $table): void {
            $table->uuid('business_id')->nullable()->change();
            $table->uuid('social_media_account_id')->nullable()->change();
            if (! Schema::hasColumn('social_media_posts', 'admin_id')) {
                $table->foreignUuid('admin_id')->nullable()->after('business_id')->constrained('admins')->nullOnDelete();
            }
            if (! Schema::hasColumn('social_media_posts', 'is_platform')) {
                $table->boolean('is_platform')->default(false)->after('admin_id')->index();
            }
        });

        // 3. Extend social_media_comments for platform comments & replies
        Schema::table('social_media_comments', function (Blueprint $table): void {
            $table->uuid('business_id')->nullable()->change();
            $table->uuid('social_media_account_id')->nullable()->change();
            if (! Schema::hasColumn('social_media_comments', 'is_platform')) {
                $table->boolean('is_platform')->default(false)->after('business_id')->index();
            }
        });

        // 4. Extend social_post_targets to allow nullable social_media_account_id for platform posts
        Schema::table('social_post_targets', function (Blueprint $table): void {
            $table->uuid('social_media_account_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('social_post_targets', function (Blueprint $table): void {
            $table->uuid('social_media_account_id')->nullable(false)->change();
        });

        Schema::table('social_media_comments', function (Blueprint $table): void {
            $table->dropColumn('is_platform');
            $table->uuid('social_media_account_id')->nullable(false)->change();
            $table->uuid('business_id')->nullable(false)->change();
        });

        Schema::table('social_media_posts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('admin_id');
            $table->dropColumn('is_platform');
            $table->uuid('social_media_account_id')->nullable(false)->change();
            $table->uuid('business_id')->nullable(false)->change();
        });

        Schema::table('social_media_accounts', function (Blueprint $table): void {
            $table->dropColumn('is_platform');
            $table->uuid('business_id')->nullable(false)->change();
        });
    }
};
