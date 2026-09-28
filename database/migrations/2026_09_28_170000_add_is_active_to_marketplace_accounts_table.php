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
        if (Schema::hasTable('marketplace_accounts') && ! Schema::hasColumn('marketplace_accounts', 'is_active')) {
            Schema::table('marketplace_accounts', function (Blueprint $table): void {
                $table->boolean('is_active')->default(true)->after('refresh_token_expires_at')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('marketplace_accounts') && Schema::hasColumn('marketplace_accounts', 'is_active')) {
            Schema::table('marketplace_accounts', function (Blueprint $table): void {
                $table->dropColumn('is_active');
            });
        }
    }
};
