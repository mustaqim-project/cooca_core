<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table): void {
            $table->string('payment_type', 24)->default('subscription')->after('user_id');
            $table->unsignedBigInteger('topup_quantity')->nullable()->after('total_payable');
            $table->unsignedBigInteger('topup_storage_bytes')->nullable()->after('topup_quantity');
            $table->index(['payment_type', 'status']);
        });

        Schema::create('ai_token_topups', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('payment_id')->unique()->constrained('subscription_payments')->cascadeOnDelete();
            $table->unsignedBigInteger('purchased_tokens');
            $table->unsignedBigInteger('remaining_tokens');
            $table->timestamp('purchased_at');
            $table->timestamp('expires_at');
            $table->timestamps();
            $table->index(['business_id', 'expires_at', 'remaining_tokens']);
        });

        Schema::create('owner_storage_topups', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('payment_id')->unique()->constrained('subscription_payments')->cascadeOnDelete();
            $table->unsignedBigInteger('storage_bytes');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->index('owner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_storage_topups');
        Schema::dropIfExists('ai_token_topups');
        Schema::table('subscription_payments', function (Blueprint $table): void {
            $table->dropIndex(['payment_type', 'status']);
            $table->dropColumn(['payment_type', 'topup_quantity', 'topup_storage_bytes']);
        });
    }
};
