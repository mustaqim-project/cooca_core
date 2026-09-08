<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_landing_pages', function (Blueprint $table): void {
            if (! Schema::hasColumn('business_landing_pages', 'logo_url')) {
                $table->string('logo_url', 500)->nullable()->after('hero_image_url');
            }
            if (! Schema::hasColumn('business_landing_pages', 'custom_email')) {
                $table->string('custom_email')->nullable()->after('custom_phone');
            }
            if (! Schema::hasColumn('business_landing_pages', 'stats')) {
                $table->json('stats')->nullable()->after('testimonials');
            }
            if (! Schema::hasColumn('business_landing_pages', 'social_links')) {
                $table->json('social_links')->nullable()->after('stats');
            }
        });
    }

    public function down(): void
    {
        Schema::table('business_landing_pages', function (Blueprint $table): void {
            foreach (['logo_url', 'custom_email', 'stats', 'social_links'] as $column) {
                if (Schema::hasColumn('business_landing_pages', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
