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
        Schema::create('cost_model_labors', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('cost_model_id')->constrained('cost_models')->cascadeOnDelete();
            $table->foreignUuid('labor_rate_id')->constrained('labor_rates')->cascadeOnDelete();
            $table->decimal('quantity', 18, 4); // hours, days, units, etc.
            $table->decimal('regular_hours', 18, 4)->nullable();
            $table->decimal('overtime_hours', 18, 4)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['cost_model_id', 'labor_rate_id']);
        });

        Schema::create('cost_model_machines', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('cost_model_id')->constrained('cost_models')->cascadeOnDelete();
            $table->foreignUuid('machine_id')->constrained('machines')->cascadeOnDelete();
            $table->decimal('hours_used', 18, 4);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['cost_model_id', 'machine_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_model_machines');
        Schema::dropIfExists('cost_model_labors');
    }
};
