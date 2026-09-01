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
        Schema::create('costing_runs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('cost_model_id')->constrained('cost_models')->cascadeOnDelete();
            $table->foreignUuid('triggered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('run_type')->default('manual'); // manual, scheduled, api
            $table->string('status')->default('completed'); // running, completed, failed
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'cost_model_id']);
        });

        Schema::create('costing_results', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('costing_run_id')->constrained('costing_runs')->cascadeOnDelete();
            $table->decimal('total_material_cost', 18, 4)->default(0);
            $table->decimal('total_labor_cost', 18, 4)->default(0);
            $table->decimal('total_machine_cost', 18, 4)->default(0);
            $table->decimal('total_overhead_cost', 18, 4)->default(0);
            $table->decimal('total_hpp', 18, 4)->default(0);
            $table->decimal('hpp_per_unit', 18, 4)->default(0);
            $table->foreignUuid('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->json('breakdown_snapshot')->nullable();
            $table->timestamps();

            $table->index('costing_run_id');
        });

        Schema::create('costing_result_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('costing_result_id')->constrained('costing_results')->cascadeOnDelete();
            $table->string('item_type'); // material, labor, machine, overhead, formula
            $table->string('item_name');
            $table->decimal('amount', 18, 4);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index('costing_result_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('costing_result_items');
        Schema::dropIfExists('costing_results');
        Schema::dropIfExists('costing_runs');
    }
};
