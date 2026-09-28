<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix: MySQL TIMESTAMP max = 2038-01-19. TikTok Shop API returns
 * refresh_expires_in of ~156 years, producing dates like 2182-06-01.
 * Change both token expiry columns from TIMESTAMP to DATETIME (max 9999-12-31).
 */
return new class extends Migration
{
    public function up(): void
    {
        // marketplace_accounts
        Schema::table('marketplace_accounts', function (Blueprint $table) {
            $table->dateTime('token_expires_at')->nullable()->change();
            $table->dateTime('refresh_token_expires_at')->nullable()->change();
        });

        // social_media_accounts (same issue potential)
        if (Schema::hasColumn('social_media_accounts', 'token_expires_at')) {
            Schema::table('social_media_accounts', function (Blueprint $table) {
                $table->dateTime('token_expires_at')->nullable()->change();
            });
        }
        if (Schema::hasColumn('social_media_accounts', 'refresh_token_expires_at')) {
            Schema::table('social_media_accounts', function (Blueprint $table) {
                $table->dateTime('refresh_token_expires_at')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('marketplace_accounts', function (Blueprint $table) {
            $table->timestamp('token_expires_at')->nullable()->change();
            $table->timestamp('refresh_token_expires_at')->nullable()->change();
        });
    }
};
