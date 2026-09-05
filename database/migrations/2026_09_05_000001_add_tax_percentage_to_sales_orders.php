<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sales_orders', 'tax_percentage')) {
            Schema::table('sales_orders', function (Blueprint $table): void {
                $table->decimal('tax_percentage', 5, 2)->default(0)->after('discount_amount');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sales_orders', 'tax_percentage')) {
            Schema::table('sales_orders', function (Blueprint $table): void {
                $table->dropColumn('tax_percentage');
            });
        }
    }
};
