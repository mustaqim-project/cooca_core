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
        Schema::create('pricing_rules', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('cost_model_id')->nullable()->constrained('cost_models')->cascadeOnDelete();
            $table->foreignUuid('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->string('name');
            $table->string('strategy'); // markup, margin, target_profit, fixed_price, tiered
            $table->decimal('value', 18, 4)->default(0); // percentage or base amount
            $table->decimal('target_profit_amount', 18, 4)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'cost_model_id']);
            $table->index(['business_id', 'product_id']);
        });

        Schema::create('fees', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('type'); // cost, price_deduction (§21)
            $table->string('fee_type')->default('percentage'); // percentage, fixed
            $table->decimal('fee_value', 18, 4);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'slug']);
            $table->index(['business_id', 'type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fees');
        Schema::dropIfExists('pricing_rules');
    }
};
