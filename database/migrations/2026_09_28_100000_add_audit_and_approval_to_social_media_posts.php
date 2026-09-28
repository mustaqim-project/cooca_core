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
        Schema::table('social_media_posts', function (Blueprint $table): void {
            if (! Schema::hasColumn('social_media_posts', 'user_id')) {
                $table->foreignUuid('user_id')->nullable()->after('business_id')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('social_media_posts', 'approval_status')) {
                $table->string('approval_status', 30)->default('approved')->index()->after('status')
                    ->comment('approved, pending_review, rejected');
            }

            if (! Schema::hasColumn('social_media_posts', 'reviewed_by')) {
                $table->foreignUuid('reviewed_by')->nullable()->after('approval_status')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('social_media_posts', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }

            if (! Schema::hasColumn('social_media_posts', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('reviewed_at');
            }

            if (! Schema::hasColumn('social_media_posts', 'risk_flags')) {
                $table->json('risk_flags')->nullable()->after('rejection_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('social_media_posts', function (Blueprint $table): void {
            if (Schema::hasColumn('social_media_posts', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }

            if (Schema::hasColumn('social_media_posts', 'reviewed_by')) {
                $table->dropForeign(['reviewed_by']);
                $table->dropColumn('reviewed_by');
            }

            $columnsToDrop = array_filter([
                'approval_status',
                'reviewed_at',
                'rejection_reason',
                'risk_flags',
            ], fn (string $col): bool => Schema::hasColumn('social_media_posts', $col));

            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
