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
        if (! Schema::hasColumn('commerce_orders', 'destination_postal_code')) {
            Schema::table('commerce_orders', function (Blueprint $table): void {
                $table->string('destination_postal_code', 10)->nullable()->after('shipping_address');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('commerce_orders', 'destination_postal_code')) {
            Schema::table('commerce_orders', function (Blueprint $table): void {
                $table->dropColumn('destination_postal_code');
            });
        }
    }
};
