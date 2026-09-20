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
            $table->boolean('popup_enabled')->default(false)->after('custom_labels');
            $table->string('popup_title', 255)->nullable()->after('popup_enabled');
            $table->string('popup_badge', 50)->nullable()->after('popup_title');
            $table->text('popup_content')->nullable()->after('popup_badge');
            $table->string('popup_image_path', 500)->nullable()->after('popup_content');
            $table->string('popup_cta_text', 100)->nullable()->after('popup_image_path');
            $table->string('popup_cta_url', 500)->nullable()->after('popup_cta_text');
            $table->string('popup_frequency', 32)->default('once_per_session')->after('popup_cta_url');
            $table->timestamp('popup_starts_at')->nullable()->after('popup_frequency');
            $table->timestamp('popup_ends_at')->nullable()->after('popup_starts_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_landing_pages', function (Blueprint $table): void {
            $table->dropColumn([
                'popup_enabled',
                'popup_title',
                'popup_badge',
                'popup_content',
                'popup_image_path',
                'popup_cta_text',
                'popup_cta_url',
                'popup_frequency',
                'popup_starts_at',
                'popup_ends_at',
            ]);
        });
    }
};
