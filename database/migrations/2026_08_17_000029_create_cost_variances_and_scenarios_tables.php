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
        Schema::create('cost_variances', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('standard_cost_id')->nullable()->constrained('standard_costs')->nullOnDelete();
            $table->foreignUuid('actual_cost_id')->nullable()->constrained('actual_costs')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('variance_type'); // material_price, material_quantity, labor_rate, labor_efficiency, overhead_spending, yield, waste, total
            $table->string('variance_nature')->default('neutral'); // favorable, unfavorable, neutral
            $table->decimal('amount', 18, 4);
            $table->decimal('percentage', 10, 4)->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'product_id', 'variance_type']);
        });

        Schema::create('pricing_scenarios', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('cost_model_id')->constrained('cost_models')->cascadeOnDelete();
            $table->string('name');
            $table->json('scenario_input');
            $table->json('scenario_result');
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['business_id', 'cost_model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_scenarios');
        Schema::dropIfExists('cost_variances');
    }
};
