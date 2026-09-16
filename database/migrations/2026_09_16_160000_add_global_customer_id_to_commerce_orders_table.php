<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_orders') && ! Schema::hasColumn('commerce_orders', 'global_customer_id')) {
            Schema::table('commerce_orders', function (Blueprint $table): void {
                $table->foreignUuid('global_customer_id')
                    ->nullable()
                    ->after('customer_id')
                    ->constrained('global_customers')
                    ->nullOnDelete();

                $table->index(['global_customer_id', 'business_id'], 'co_global_cust_biz_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('commerce_orders') && Schema::hasColumn('commerce_orders', 'global_customer_id')) {
            Schema::table('commerce_orders', function (Blueprint $table): void {
                $table->dropForeign(['global_customer_id']);
                $table->dropIndex('co_global_cust_biz_idx');
                $table->dropColumn('global_customer_id');
            });
        }
    }
};
