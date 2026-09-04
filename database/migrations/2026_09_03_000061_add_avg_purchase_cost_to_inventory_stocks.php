<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_stocks', function (Blueprint $table): void {
            $table->decimal('avg_purchase_cost', 15, 2)->default(0.00)->after('last_cost');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_stocks', function (Blueprint $table): void {
            $table->dropColumn('avg_purchase_cost');
        });
    }
};
