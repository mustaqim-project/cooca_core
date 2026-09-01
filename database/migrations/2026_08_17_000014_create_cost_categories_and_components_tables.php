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
        Schema::create('cost_categories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->nullable()->constrained('businesses')->cascadeOnDelete();
            $table->string('code', 50); // direct_material, direct_labor, variable_overhead, fixed_overhead, other
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'code']);
        });

        Schema::create('cost_components', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->nullable()->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('cost_category_id')->constrained('cost_categories')->cascadeOnDelete();
            $table->string('slug');
            $table->string('name');
            $table->string('behavior'); // fixed, variable, semi_variable
            $table->string('traceability'); // direct, indirect
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->index(['business_id', 'slug']);
            $table->index(['business_id', 'cost_category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_components');
        Schema::dropIfExists('cost_categories');
    }
};
