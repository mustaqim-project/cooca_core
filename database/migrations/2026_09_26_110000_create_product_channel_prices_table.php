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
        Schema::table('pos_orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('pos_orders', 'sales_channel')) {
                $table->string('sales_channel', 30)->default('dine_in')->after('order_type');
                $table->index(['business_id', 'sales_channel']);
            }
            if (! Schema::hasColumn('pos_orders', 'external_order_ref')) {
                $table->string('external_order_ref', 100)->nullable()->after('sales_channel');
            }
        });

        if (! Schema::hasTable('product_channel_prices')) {
            Schema::create('product_channel_prices', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
                $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('channel', 30);
                $table->decimal('price', 15, 2)->default(0.00);
                $table->timestamps();

                $table->unique(['product_id', 'channel'], 'uk_product_channel');
                $table->index(['business_id', 'channel']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_channel_prices');

        Schema::table('pos_orders', function (Blueprint $table): void {
            if (Schema::hasColumn('pos_orders', 'external_order_ref')) {
                $table->dropColumn('external_order_ref');
            }
            if (Schema::hasColumn('pos_orders', 'sales_channel')) {
                $table->dropIndex(['business_id', 'sales_channel']);
                $table->dropColumn('sales_channel');
            }
        });
    }
};
