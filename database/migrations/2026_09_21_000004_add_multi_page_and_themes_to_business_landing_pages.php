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
        Schema::table('business_landing_pages', function (Blueprint $table): void {
            if (! Schema::hasColumn('business_landing_pages', 'theme_preset')) {
                $table->string('theme_preset', 50)->default('artisan_brew')->after('industry_preset');
            }
            if (! Schema::hasColumn('business_landing_pages', 'active_pages')) {
                $table->json('active_pages')->nullable()->after('theme_preset');
            }
            if (! Schema::hasColumn('business_landing_pages', 'custom_labels')) {
                $table->json('custom_labels')->nullable()->after('active_pages');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_landing_pages', function (Blueprint $table): void {
            $columns = [];
            if (Schema::hasColumn('business_landing_pages', 'theme_preset')) {
                $columns[] = 'theme_preset';
            }
            if (Schema::hasColumn('business_landing_pages', 'active_pages')) {
                $columns[] = 'active_pages';
            }
            if (Schema::hasColumn('business_landing_pages', 'custom_labels')) {
                $columns[] = 'custom_labels';
            }
            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
