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
        Schema::create('whatsapp_message_templates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('waba_id', 64)->index()->comment('WhatsApp Business Account ID from Meta');
            $table->foreignUuid('business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $table->string('meta_template_id', 64)->nullable()->index()->comment('Meta Template ID');
            $table->string('name', 128)->comment('Template name in lowercase and underscore');
            $table->string('category', 32)->default('MARKETING')->comment('MARKETING, UTILITY, AUTHENTICATION');
            $table->string('language', 16)->default('id')->comment('Language code (e.g. id, en_US)');
            $table->string('status', 32)->default('PENDING')->index()->comment('APPROVED, PENDING, REJECTED, PAUSED, DISABLED');
            $table->json('components')->comment('JSON array of components: HEADER, BODY, FOOTER, BUTTONS');
            $table->text('rejected_reason')->nullable();
            $table->string('quality_score', 32)->nullable()->comment('GREEN, YELLOW, RED, UNKNOWN');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['waba_id', 'name', 'language'], 'waba_template_lang_unique');
            $table->index(['waba_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_message_templates');
    }
};
