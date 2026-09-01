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
        Schema::create('activities', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('cost_pool_id')->constrained('cost_pools')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('cost_driver_name'); // e.g. "Jumlah Inspeksi QC", "Jumlah Jam Setup"
            $table->decimal('total_activity_capacity', 18, 4); // Total batch / periode
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['business_id', 'slug']);
            $table->index(['business_id', 'cost_pool_id']);
        });

        Schema::create('cost_model_activities', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('cost_model_id')->constrained('cost_models')->cascadeOnDelete();
            $table->foreignUuid('activity_id')->constrained('activities')->cascadeOnDelete();
            $table->decimal('consumed_quantity', 18, 4);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['cost_model_id', 'activity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_model_activities');
        Schema::dropIfExists('activities');
    }
};
