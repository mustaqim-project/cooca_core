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
        Schema::create('standard_costs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('product_cost_version_id')->constrained('product_cost_versions')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('standard_material_cost', 18, 4)->default(0);
            $table->decimal('standard_labor_cost', 18, 4)->default(0);
            $table->decimal('standard_machine_cost', 18, 4)->default(0);
            $table->decimal('standard_overhead_cost', 18, 4)->default(0);
            $table->decimal('standard_hpp', 18, 4)->default(0);
            $table->json('standard_snapshot');
            $table->date('effective_date')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'product_id']);
        });

        Schema::create('actual_costs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('period'); // e.g. 2026-01, Batch-001
            $table->decimal('actual_material_cost', 18, 4)->default(0);
            $table->decimal('actual_labor_cost', 18, 4)->default(0);
            $table->decimal('actual_machine_cost', 18, 4)->default(0);
            $table->decimal('actual_overhead_cost', 18, 4)->default(0);
            $table->decimal('actual_total_cost', 18, 4)->default(0);
            $table->decimal('actual_output_qty', 18, 4)->default(1);
            $table->decimal('actual_hpp_per_unit', 18, 4)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'product_id', 'period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actual_costs');
        Schema::dropIfExists('standard_costs');
    }
};
