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
        Schema::create('cost_drivers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('type'); // per_unit, revenue_pct, labor_hour, machine_hour, production_hour, area, weight, volume, abc, custom
            $table->text('custom_formula')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['business_id', 'slug']);
            $table->index(['business_id', 'name']);
            $table->index(['business_id', 'type']);
        });

        Schema::create('allocation_rules', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('cost_pool_id')->constrained('cost_pools')->cascadeOnDelete();
            $table->foreignUuid('cost_driver_id')->constrained('cost_drivers')->cascadeOnDelete();
            $table->foreignUuid('cost_model_id')->nullable()->constrained('cost_models')->nullOnDelete();
            $table->foreignUuid('target_category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->decimal('total_driver_capacity', 18, 4)->nullable();
            $table->string('name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'cost_pool_id']);
            $table->index(['business_id', 'cost_model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allocation_rules');
        Schema::dropIfExists('cost_drivers');
    }
};
