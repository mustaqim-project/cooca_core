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
        Schema::create('labor_rates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('basis'); // hourly, daily, monthly, per_unit, per_project, per_task
            $table->decimal('rate_amount', 18, 4);
            $table->boolean('is_subcontractor')->default(false);
            $table->decimal('overtime_multiplier', 5, 2)->default(1.00);
            $table->unsignedSmallInteger('working_days_per_month')->default(22);
            $table->decimal('working_hours_per_day', 5, 2)->default(8.00);
            $table->decimal('utilization_rate', 5, 2)->default(80.00); // 80% default productive
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['business_id', 'slug']);
            $table->index(['business_id', 'name']);
            $table->index(['business_id', 'basis']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labor_rates');
    }
};
