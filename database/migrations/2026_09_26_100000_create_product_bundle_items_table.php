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
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'is_bundle')) {
                $table->boolean('is_bundle')->default(false)->after('is_preorder');
            }
        });

        if (! Schema::hasTable('product_bundle_items')) {
            Schema::create('product_bundle_items', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
                $table->foreignUuid('parent_product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignUuid('child_product_id')->constrained('products')->cascadeOnDelete();
                $table->decimal('quantity', 12, 4)->default(1.0000);
                $table->timestamps();

                $table->index(['business_id', 'parent_product_id']);
                $table->index(['business_id', 'child_product_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_bundle_items');

        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'is_bundle')) {
                $table->dropColumn('is_bundle');
            }
        });
    }
};
