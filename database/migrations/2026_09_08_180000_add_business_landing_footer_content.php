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
            $table->text('footer_description')->nullable()->after('og_image_url');
            $table->string('footer_navigation_title')->nullable()->after('footer_description');
            $table->string('footer_services_title')->nullable()->after('footer_navigation_title');
            $table->string('footer_contact_title')->nullable()->after('footer_services_title');
            $table->string('footer_cta_text')->nullable()->after('footer_contact_title');
            $table->string('footer_copyright')->nullable()->after('footer_cta_text');
        });
    }

    public function down(): void
    {
        Schema::table('business_landing_pages', function (Blueprint $table): void {
            $table->dropColumn([
                'footer_description',
                'footer_navigation_title',
                'footer_services_title',
                'footer_contact_title',
                'footer_cta_text',
                'footer_copyright',
            ]);
        });
    }
};