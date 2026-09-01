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
        Schema::create('overheads', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->decimal('amount', 18, 4);
            $table->string('period')->default('monthly'); // monthly, yearly, one_time
            $table->string('behavior')->default('fixed'); // fixed, variable
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['business_id', 'slug']);
            $table->index(['business_id', 'name']);
            $table->index(['business_id', 'period']);
        });

        Schema::create('cost_pools', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->decimal('manual_override_amount', 18, 4)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['business_id', 'slug']);
            $table->index(['business_id', 'name']);
        });

        Schema::create('cost_pool_overheads', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('cost_pool_id')->constrained('cost_pools')->cascadeOnDelete();
            $table->foreignUuid('overhead_id')->constrained('overheads')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['cost_pool_id', 'overhead_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_pool_overheads');
        Schema::dropIfExists('cost_pools');
        Schema::dropIfExists('overheads');
    }
};
