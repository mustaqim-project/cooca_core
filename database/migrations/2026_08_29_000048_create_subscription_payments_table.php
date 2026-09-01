<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for subscription payments and proof verifications.
     */
    public function up(): void
    {
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('order_number', 32)->unique();
            $table->string('plan_code', 32)->default('core_monthly');
            $table->string('cycle', 16)->default('monthly'); // monthly, annual
            $table->decimal('amount', 15, 2)->default(129000);
            $table->unsignedSmallInteger('unique_code')->default(0);
            $table->decimal('total_payable', 15, 2)->default(129000);
            $table->string('payment_method', 32)->default('bca'); // bca, mandiri, bri, qris
            $table->string('status', 32)->default('pending'); // pending, awaiting_approval, approved, rejected, cancelled
            
            // Payment proof upload fields
            $table->string('payment_proof_path')->nullable();
            $table->string('sender_bank', 64)->nullable();
            $table->string('sender_account_name', 128)->nullable();
            $table->string('sender_account_number', 64)->nullable();
            $table->timestamp('proof_uploaded_at')->nullable();
            $table->text('notes')->nullable();
            
            // Admin verification fields
            $table->text('admin_notes')->nullable();
            $table->foreignUuid('approved_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            
            $table->timestamps();

            $table->index(['business_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};
