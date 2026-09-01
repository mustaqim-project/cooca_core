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
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('onboarding_completed')->default(false)->after('active_business_id');
            $table->timestamp('onboarding_completed_at')->nullable()->after('onboarding_completed');
            $table->unsignedSmallInteger('onboarding_current_step')->default(1)->after('onboarding_completed_at');
            $table->unsignedSmallInteger('onboarding_version')->default(1)->after('onboarding_current_step');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'onboarding_completed',
                'onboarding_completed_at',
                'onboarding_current_step',
                'onboarding_version',
            ]);
        });
    }
};
