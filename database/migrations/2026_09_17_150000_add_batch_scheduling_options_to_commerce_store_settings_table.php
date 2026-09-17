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
                if (! Schema::hasColumn('commerce_store_settings', 'allow_custom_date')) {
                    $table->boolean('allow_custom_date')->default(true)->after('allow_reservation');
                }
                if (! Schema::hasColumn('commerce_store_settings', 'quota_metric')) {
                    $table->string('quota_metric', 20)->default('orders')->after('daily_order_quota');
                }
                if (! Schema::hasColumn('commerce_store_settings', 'preorder_quota_unit')) {
                    $table->string('preorder_quota_unit', 30)->default('PCS')->after('quota_metric');
                }
                if (! Schema::hasColumn('commerce_store_settings', 'batch_dates_mode')) {
                    $table->string('batch_dates_mode', 30)->default('operating_days')->after('preorder_quota_unit');
                }
                if (! Schema::hasColumn('commerce_store_settings', 'custom_batch_dates')) {
                    $table->json('custom_batch_dates')->nullable()->after('batch_dates_mode');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('commerce_store_settings')) {
            Schema::table('commerce_store_settings', function (Blueprint $table): void {
                $columns = [
                    'allow_custom_date',
                    'quota_metric',
                    'preorder_quota_unit',
                    'batch_dates_mode',
                    'custom_batch_dates',
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('commerce_store_settings', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
