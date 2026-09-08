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
            $table->string('gallery_title')->nullable()->after('gallery_images');
            $table->string('gallery_subtitle')->nullable()->after('gallery_title');
        });
    }

    public function down(): void
    {
        Schema::table('business_landing_pages', function (Blueprint $table): void {
            $table->dropColumn(['gallery_title', 'gallery_subtitle']);
        });
    }
};