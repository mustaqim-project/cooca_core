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
        if (Schema::hasTable('material_unit_conversions')) {
            return;
        }

        Schema::create('material_unit_conversions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('material_id')->constrained('materials')->cascadeOnDelete();
            $table->foreignUuid('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignUuid('from_unit_id')->constrained('units')->cascadeOnDelete();
            $table->foreignUuid('to_unit_id')->constrained('units')->cascadeOnDelete();
            $table->decimal('factor', 18, 8);
            $table->boolean('is_default')->default(false);
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'material_id'], 'mat_unit_conv_biz_mat_idx');
            $table->unique(['business_id', 'material_id', 'supplier_id', 'from_unit_id', 'to_unit_id'], 'material_unit_conversions_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_unit_conversions');
    }
};
