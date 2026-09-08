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
            if (! Schema::hasColumn('business_landing_pages', 'section_visibility')) {
                $table->json('section_visibility')->nullable()->after('gallery_images');
            }
        });

        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'image_path')) {
                $table->string('image_path', 500)->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('business_landing_pages', function (Blueprint $table): void {
            if (Schema::hasColumn('business_landing_pages', 'section_visibility')) {
                $table->dropColumn('section_visibility');
            }
        });

        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'image_path')) {
                $table->dropColumn('image_path');
            }
        });
    }
};
