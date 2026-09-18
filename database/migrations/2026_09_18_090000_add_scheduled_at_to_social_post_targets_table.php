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
        Schema::table('social_post_targets', function (Blueprint $table): void {
            if (! Schema::hasColumn('social_post_targets', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->after('published_at');
                $table->index(['status', 'scheduled_at'], 'sm_targets_stat_sched_idx');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('social_post_targets', function (Blueprint $table): void {
            if (Schema::hasColumn('social_post_targets', 'scheduled_at')) {
                $table->dropIndex('sm_targets_stat_sched_idx');
                $table->dropColumn('scheduled_at');
            }
        });
    }
};
