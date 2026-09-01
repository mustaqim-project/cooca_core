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
        Schema::table('products', function (Blueprint $table): void {
            $table->decimal('base_cost', 15, 2)->default(0.00)->after('description');
            $table->decimal('selling_price', 15, 2)->default(0.00)->after('base_cost');
            $table->decimal('min_stock', 15, 2)->default(0.00)->after('selling_price');
            $table->boolean('is_active')->default(true)->after('min_stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['base_cost', 'selling_price', 'min_stock', 'is_active']);
        });
    }
};
