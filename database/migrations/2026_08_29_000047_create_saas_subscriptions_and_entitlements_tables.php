<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for SaaS subscriptions and token usages.
     */
    public function up(): void
    {
        Schema::create('business_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('plan_code', 32)->default('free'); // free, core_monthly, core_annual
            $table->decimal('price', 15, 2)->default(0);
            $table->string('status', 32)->default('active'); // active, past_due, expired, over_limit
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedBigInteger('ai_tokens_monthly_allowance')->default(0);
            $table->unsignedBigInteger('ai_tokens_remaining')->default(0);
            $table->date('last_token_reset_at')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'plan_code']);
            $table->index(['business_id', 'status']);
        });

        Schema::create('ai_token_usages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('model_name', 64)->default('gemini-2.5-flash');
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->unsignedInteger('total_tokens')->default(0);
            $table->string('intent', 64)->default('sales_analysis');
            $table->timestamps();

            $table->index(['business_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_token_usages');
        Schema::dropIfExists('business_subscriptions');
    }
};
