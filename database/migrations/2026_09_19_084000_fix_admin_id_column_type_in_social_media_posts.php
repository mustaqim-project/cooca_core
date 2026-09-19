<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('social_media_posts', 'admin_id')) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE social_media_posts MODIFY COLUMN admin_id CHAR(36) NULL');
            } else {
                Schema::table('social_media_posts', function (Blueprint $table): void {
                    $table->uuid('admin_id')->nullable()->change();
                });
            }
        } else {
            Schema::table('social_media_posts', function (Blueprint $table): void {
                $table->uuid('admin_id')->nullable()->after('business_id')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('social_media_posts', 'admin_id')) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE social_media_posts MODIFY COLUMN admin_id BIGINT UNSIGNED NULL');
            }
        }
    }
};
