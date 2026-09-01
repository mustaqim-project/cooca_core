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
        Schema::create('cost_models', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('method'); // simple, per_unit, recipe_bom, job, process, abc, service, retail, custom
            $table->string('output_basis')->default('planned'); // planned, actual, sellable
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'product_id', 'slug']);
            $table->index(['business_id', 'product_id']);
        });

        Schema::create('cost_model_components', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('cost_model_id')->constrained('cost_models')->cascadeOnDelete();
            $table->foreignUuid('cost_component_id')->constrained('cost_components')->cascadeOnDelete();
            $table->boolean('is_included_in_hpp')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['cost_model_id', 'cost_component_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_model_components');
        Schema::dropIfExists('cost_models');
    }
};
