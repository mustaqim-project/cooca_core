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
        Schema::create('whatsapp_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('business_id')->unique()->constrained('businesses')->cascadeOnDelete();
            $table->string('waba_id')->index()->comment('WhatsApp Business Account ID from Meta');
            $table->string('phone_number_id')->unique()->index()->comment('Meta Phone Number ID for API endpoints and Webhook routing');
            $table->string('phone_number')->index()->comment('Normalized phone number in E.164 format without plus');
            $table->string('display_phone_number')->nullable()->comment('Formatted phone number displayed to customers');
            $table->string('verified_name')->nullable()->comment('Business display name approved by Meta');
            $table->string('code_verification_status')->nullable()->comment('Status like VERIFIED, EXPIRED, NOT_VERIFIED');
            $table->string('quality_rating')->default('UNKNOWN')->comment('GREEN, YELLOW, RED, UNKNOWN');
            $table->string('messaging_limit_tier')->default('TIER_50')->comment('TIER_50, TIER_250, TIER_1K, TIER_10K, TIER_100K, TIER_UNLIMITED');
            $table->text('access_token')->comment('Encrypted System User or Merchant Access Token for Meta Graph API');
            $table->string('token_type')->default('Bearer');
            $table->timestamp('token_expires_at')->nullable()->comment('Null for permanent system user tokens');
            $table->string('status')->default('active')->index()->comment('active, disconnected, suspended, expired');
            $table->timestamp('webhook_verified_at')->nullable();
            $table->json('settings')->nullable()->comment('Tenant preferences: auto_send_receipt, notification triggers, etc.');
            $table->json('metadata')->nullable()->comment('Raw Meta profile snapshot, business details');
            $table->timestamps();

            $table->index(['business_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_accounts');
    }
};
