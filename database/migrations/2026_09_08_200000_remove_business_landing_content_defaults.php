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
            $table->string('industry_preset')->nullable()->default(null)->change();
            $table->string('theme_color')->nullable()->default(null)->change();
            $table->string('cta_primary_text')->nullable()->default(null)->change();
            $table->string('cta_secondary_text')->nullable()->default(null)->change();
            $table->boolean('show_pos_products')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('business_landing_pages', function (Blueprint $table): void {
            $table->string('industry_preset')->default('retail')->change();
            $table->string('theme_color')->default('#10B981')->change();
            $table->string('cta_primary_text')->default('Hubungi Kami via WhatsApp')->change();
            $table->string('cta_secondary_text')->default('Lihat Katalog & Layanan')->change();
            $table->boolean('show_pos_products')->default(true)->change();
        });
    }
};
