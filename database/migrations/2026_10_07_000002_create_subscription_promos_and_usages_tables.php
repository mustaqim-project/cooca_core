<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for SaaS subscription promos, voucher tracking, and payment discount snapshots.
     */
    public function up(): void
    {
        Schema::create('subscription_promos', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 32)->unique();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->string('discount_type', 20)->default('percentage'); // 'percentage', 'fixed'
            $table->decimal('discount_value', 15, 2);
            $table->decimal('max_discount_amount', 15, 2)->nullable();
            $table->decimal('min_order_amount', 15, 2)->default(0);
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->unsignedInteger('usage_per_business_limit')->default(1);
            $table->json('applicable_tiers')->nullable(); // e.g. ["standard", "premium", "prestige"] or null = all
            $table->json('applicable_cycles')->nullable(); // e.g. ["monthly", "annual"] or null = all
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['code', 'is_active']);
            $table->index(['is_active', 'valid_from', 'valid_until']);
        });

        Schema::create('subscription_promo_usages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('promo_id')->constrained('subscription_promos')->cascadeOnDelete();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('subscription_payment_id')->nullable()->constrained('subscription_payments')->nullOnDelete();
            $table->string('order_number', 32)->nullable();
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('final_paid_amount', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['promo_id', 'business_id']);
            $table->index(['business_id', 'created_at']);
        });

        Schema::table('subscription_payments', function (Blueprint $table): void {
            $table->foreignUuid('promo_id')->nullable()->after('billing_package_id')->constrained('subscription_promos')->nullOnDelete();
            $table->string('promo_code', 32)->nullable()->after('promo_id');
            $table->decimal('discount_amount', 15, 2)->default(0)->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table): void {
            $table->dropForeign(['promo_id']);
            $table->dropColumn(['promo_id', 'promo_code', 'discount_amount']);
        });

        Schema::dropIfExists('subscription_promo_usages');
        Schema::dropIfExists('subscription_promos');
    }
};
