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
            $table->string('origin_location_id', 100)->nullable()->after('origin_area_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commerce_store_settings', function (Blueprint $table): void {
            $table->dropColumn('origin_location_id');
        });
    }
};
