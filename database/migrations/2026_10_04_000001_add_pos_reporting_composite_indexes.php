<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan composite indexes khusus query pelaporan POS skala besar (<150ms).
     */
    public function up(): void
    {
        Schema::table('pos_orders', function (Blueprint $table): void {
            $table->index(['business_id', 'status', 'order_date'], 'pos_orders_biz_status_date_idx');
            $table->index(['business_id', 'location_id', 'order_date'], 'pos_orders_biz_loc_date_idx');
            $table->index(['business_id', 'user_id', 'order_date'], 'pos_orders_biz_user_date_idx');
            $table->index(['business_id', 'pos_shift_id', 'status'], 'pos_orders_biz_shift_status_idx');
            $table->index(['business_id', 'customer_id', 'order_date'], 'pos_orders_biz_cust_date_idx');
            $table->index(['business_id', 'order_type', 'order_date'], 'pos_orders_biz_type_date_idx');
            $table->index(['business_id', 'sales_channel', 'order_date'], 'pos_orders_biz_channel_date_idx');
        });

        Schema::table('pos_order_items', function (Blueprint $table): void {
            $table->index(['pos_order_id', 'product_id'], 'pos_order_items_order_prod_idx');
        });

        Schema::table('pos_order_payments', function (Blueprint $table): void {
            $table->index(['pos_order_id', 'payment_method'], 'pos_order_payments_order_method_idx');
        });

        Schema::table('pos_shifts', function (Blueprint $table): void {
            $table->index(['business_id', 'status', 'opened_at'], 'pos_shifts_biz_status_opened_idx');
            $table->index(['business_id', 'user_id', 'opened_at'], 'pos_shifts_biz_user_opened_idx');
        });

        Schema::table('sales_returns', function (Blueprint $table): void {
            $table->index(['business_id', 'status', 'return_date'], 'sales_returns_biz_status_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_orders', function (Blueprint $table): void {
            $table->dropIndex('pos_orders_biz_status_date_idx');
            $table->dropIndex('pos_orders_biz_loc_date_idx');
            $table->dropIndex('pos_orders_biz_user_date_idx');
            $table->dropIndex('pos_orders_biz_shift_status_idx');
            $table->dropIndex('pos_orders_biz_cust_date_idx');
            $table->dropIndex('pos_orders_biz_type_date_idx');
            $table->dropIndex('pos_orders_biz_channel_date_idx');
        });

        Schema::table('pos_order_items', function (Blueprint $table): void {
            $table->dropIndex('pos_order_items_order_prod_idx');
        });

        Schema::table('pos_order_payments', function (Blueprint $table): void {
            $table->dropIndex('pos_order_payments_order_method_idx');
        });

        Schema::table('pos_shifts', function (Blueprint $table): void {
            $table->dropIndex('pos_shifts_biz_status_opened_idx');
            $table->dropIndex('pos_shifts_biz_user_opened_idx');
        });

        Schema::table('sales_returns', function (Blueprint $table): void {
            $table->dropIndex('sales_returns_biz_status_date_idx');
        });
    }
};
