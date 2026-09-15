<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_store_settings')) {
            Schema::table('commerce_store_settings', function (Blueprint $table): void {
                if (! Schema::hasColumn('commerce_store_settings', 'allow_request_order')) {
                    $table->boolean('allow_request_order')->default(true)->after('allow_delivery');
                }
                if (! Schema::hasColumn('commerce_store_settings', 'allow_scheduled_order')) {
                    $table->boolean('allow_scheduled_order')->default(true)->after('allow_request_order');
                }
                if (! Schema::hasColumn('commerce_store_settings', 'allow_customer_po')) {
                    $table->boolean('allow_customer_po')->default(true)->after('allow_scheduled_order');
                }
                if (! Schema::hasColumn('commerce_store_settings', 'allow_reservation')) {
                    $table->boolean('allow_reservation')->default(true)->after('allow_customer_po');
                }
                if (! Schema::hasColumn('commerce_store_settings', 'daily_order_quota')) {
                    $table->unsignedInteger('daily_order_quota')->default(0)->after('max_capacity_per_slot');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('commerce_store_settings')) {
            Schema::table('commerce_store_settings', function (Blueprint $table): void {
                $columns = ['allow_request_order', 'allow_scheduled_order', 'allow_customer_po', 'allow_reservation', 'daily_order_quota'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('commerce_store_settings', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
