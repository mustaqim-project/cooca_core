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
        if (! Schema::hasColumn('commerce_orders', 'biteship_service_fee')) {
            Schema::table('commerce_orders', function (Blueprint $table): void {
                $table->decimal('biteship_service_fee', 15, 2)->default(0.00)->after('shipping_cost');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('commerce_orders', 'biteship_service_fee')) {
            Schema::table('commerce_orders', function (Blueprint $table): void {
                $table->dropColumn('biteship_service_fee');
            });
        }
    }
};
