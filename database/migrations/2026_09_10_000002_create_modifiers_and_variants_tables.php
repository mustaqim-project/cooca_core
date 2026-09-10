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
        Schema::create('modifier_groups', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('description', 255)->nullable();
            $table->string('selection_type', 20)->default('single'); // single, multiple
            $table->unsignedInteger('min_selection')->default(0);
            $table->unsignedInteger('max_selection')->default(1);
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'is_active']);
        });

        Schema::create('modifier_options', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('modifier_group_id')->constrained('modifier_groups')->cascadeOnDelete();
            $table->string('name', 150);
            $table->decimal('price_delta', 15, 2)->default(0.00);
            $table->boolean('affects_material')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['modifier_group_id', 'is_active']);
        });

        Schema::create('product_modifier_groups', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignUuid('modifier_group_id')->constrained('modifier_groups')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'modifier_group_id']);
        });

        Schema::create('modifier_option_materials', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('modifier_option_id')->constrained('modifier_options')->cascadeOnDelete();
            $table->foreignUuid('material_id')->constrained('materials')->cascadeOnDelete();
            $table->decimal('quantity', 15, 4)->default(0.0000);
            $table->foreignUuid('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->timestamps();

            $table->index(['modifier_option_id', 'material_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modifier_option_materials');
        Schema::dropIfExists('product_modifier_groups');
        Schema::dropIfExists('modifier_options');
        Schema::dropIfExists('modifier_groups');
    }
};
