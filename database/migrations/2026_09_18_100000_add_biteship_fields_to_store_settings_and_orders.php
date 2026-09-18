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
        Schema::table('commerce_store_settings', function (Blueprint $table): void {
            $table->string('origin_contact_name', 150)->nullable()->after('announcement_text');
            $table->string('origin_contact_phone', 30)->nullable()->after('origin_contact_name');
            $table->text('origin_address')->nullable()->after('origin_contact_phone');
            $table->string('origin_postal_code', 10)->nullable()->after('origin_address');
            $table->decimal('origin_latitude', 10, 7)->nullable()->after('origin_postal_code');
            $table->decimal('origin_longitude', 10, 7)->nullable()->after('origin_latitude');
            $table->string('origin_area_id', 100)->nullable()->after('origin_longitude');
            $table->json('biteship_enabled_couriers')->nullable()->after('origin_area_id');
        });

        Schema::table('commerce_orders', function (Blueprint $table): void {
            $table->string('destination_postal_code', 10)->nullable()->after('shipping_address');
            $table->string('shipping_courier_code', 50)->nullable()->after('shipping_cost');
            $table->string('shipping_courier_service', 50)->nullable()->after('shipping_courier_code');
            $table->string('shipping_courier_name', 100)->nullable()->after('shipping_courier_service');
            $table->string('biteship_order_id', 100)->nullable()->after('shipping_courier_name');
            $table->string('shipping_waybill_id', 100)->nullable()->after('biteship_order_id');
            $table->string('shipping_tracking_url', 255)->nullable()->after('shipping_waybill_id');
            $table->string('shipping_status', 50)->nullable()->after('shipping_tracking_url');
            $table->json('shipping_payload')->nullable()->after('shipping_status');

            $table->index(['biteship_order_id'], 'commerce_orders_biteship_order_id_idx');
            $table->index(['shipping_waybill_id'], 'commerce_orders_shipping_waybill_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commerce_store_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'origin_contact_name',
                'origin_contact_phone',
                'origin_address',
                'origin_postal_code',
                'origin_latitude',
                'origin_longitude',
                'origin_area_id',
                'biteship_enabled_couriers',
            ]);
        });

        Schema::table('commerce_orders', function (Blueprint $table): void {
            $table->dropIndex('commerce_orders_biteship_order_id_idx');
            $table->dropIndex('commerce_orders_shipping_waybill_id_idx');

            $table->dropColumn([
                'destination_postal_code',
                'shipping_courier_code',
                'shipping_courier_service',
                'shipping_courier_name',
                'biteship_order_id',
                'shipping_waybill_id',
                'shipping_tracking_url',
                'shipping_status',
                'shipping_payload',
            ]);
        });
    }
};
