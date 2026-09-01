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
        Schema::create('bom_headers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('cost_model_id')->constrained('cost_models')->cascadeOnDelete();
            $table->string('type')->default('recipe'); // recipe, bom
            $table->unsignedInteger('level')->default(1);
            $table->string('name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('cost_model_id');
        });

        Schema::create('bom_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('bom_header_id')->constrained('bom_headers')->cascadeOnDelete();
            $table->foreignUuid('material_id')->nullable()->constrained('materials')->cascadeOnDelete();
            $table->foreignUuid('sub_bom_header_id')->nullable()->constrained('bom_headers')->cascadeOnDelete();
            $table->foreignUuid('unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('quantity', 18, 4);
            $table->decimal('waste_percentage', 8, 4)->default(0.0000);
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['bom_header_id', 'sort_order']);
            $table->index('material_id');
            $table->index('sub_bom_header_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_items');
        Schema::dropIfExists('bom_headers');
    }
};
