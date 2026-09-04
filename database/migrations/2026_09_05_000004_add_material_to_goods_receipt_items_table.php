<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dukung arsitektur "Material is Master Stock": goods_receipt_items boleh dicatat
     * untuk material (material_id) maupun item non-inventori bebas (item_name),
     * sehingga product_id menjadi nullable.
     */
    public function up(): void
    {
        Schema::table('goods_receipt_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('goods_receipt_items', 'material_id')) {
                $table->foreignUuid('material_id')->nullable()->after('product_id')->constrained('materials')->cascadeOnDelete();
                $table->index('material_id');
            }

            if (! Schema::hasColumn('goods_receipt_items', 'item_name')) {
                $table->string('item_name', 255)->nullable()->after('material_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipt_items', function (Blueprint $table): void {
            if (Schema::hasColumn('goods_receipt_items', 'item_name')) {
                $table->dropColumn('item_name');
            }

            if (Schema::hasColumn('goods_receipt_items', 'material_id')) {
                $table->dropForeign(['material_id']);
                $table->dropIndex(['material_id']);
                $table->dropColumn('material_id');
            }

            $table->uuid('product_id')->nullable(false)->change();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->index('product_id');
        });
    }
};