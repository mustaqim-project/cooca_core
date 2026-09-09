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
        Schema::table('inventory_stocks', function (Blueprint $table): void {
            $table->uuid('product_id')->nullable()->change();
        });

        Schema::table('stock_movements', function (Blueprint $table): void {
            $table->uuid('product_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_stocks', function (Blueprint $table): void {
            $table->uuid('product_id')->nullable(false)->change();
        });

        Schema::table('stock_movements', function (Blueprint $table): void {
            $table->uuid('product_id')->nullable(false)->change();
        });
    }
};
