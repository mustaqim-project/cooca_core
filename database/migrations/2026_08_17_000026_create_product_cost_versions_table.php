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
        Schema::create('product_cost_versions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignUuid('cost_model_id')->constrained('cost_models')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('version_label')->nullable();
            $table->string('status')->default('draft'); // draft, in_review, approved, published, archived
            $table->decimal('total_hpp', 18, 4)->default(0);
            $table->decimal('hpp_per_unit', 18, 4)->default(0);
            $table->json('hpp_snapshot');
            $table->json('formula_definition_snapshot')->nullable();
            $table->json('status_history')->nullable();
            $table->timestamp('effective_from')->nullable();
            $table->timestamp('effective_to')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['product_id', 'version_number']);
            $table->index(['business_id', 'product_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_cost_versions');
    }
};
