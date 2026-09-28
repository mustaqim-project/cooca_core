<?php

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
        Schema::table('marketplace_product_mappings', function (Blueprint $table) {
            if (! Schema::hasColumn('marketplace_product_mappings', 'price_multiplier')) {
                $table->decimal('price_multiplier', 5, 2)->nullable()->default(1.00)->after('channel_price');
            }
            if (! Schema::hasColumn('marketplace_product_mappings', 'custom_stock')) {
                $table->integer('custom_stock')->nullable()->after('channel_stock');
            }
            if (! Schema::hasColumn('marketplace_product_mappings', 'stock_buffer')) {
                $table->integer('stock_buffer')->default(0)->after('custom_stock');
            }
            if (! Schema::hasColumn('marketplace_product_mappings', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('sync_stock_auto');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketplace_product_mappings', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['price_multiplier', 'custom_stock', 'stock_buffer', 'is_active'] as $col) {
                if (Schema::hasColumn('marketplace_product_mappings', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
