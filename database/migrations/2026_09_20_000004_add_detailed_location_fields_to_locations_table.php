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
        Schema::table('locations', function (Blueprint $table): void {
            if (! Schema::hasColumn('locations', 'province')) {
                $table->string('province', 100)->nullable()->after('address');
            }
            if (! Schema::hasColumn('locations', 'city')) {
                $table->string('city', 100)->nullable()->after('province');
            }
            if (! Schema::hasColumn('locations', 'district')) {
                $table->string('district', 100)->nullable()->after('city');
            }
            if (! Schema::hasColumn('locations', 'village')) {
                $table->string('village', 100)->nullable()->after('district');
            }
            if (! Schema::hasColumn('locations', 'postal_code')) {
                $table->string('postal_code', 10)->nullable()->after('village');
            }
            if (! Schema::hasColumn('locations', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('postal_code');
            }
            if (! Schema::hasColumn('locations', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
            if (! Schema::hasColumn('locations', 'biteship_area_id')) {
                $table->string('biteship_area_id', 100)->nullable()->after('longitude');
            }
            if (! Schema::hasColumn('locations', 'is_online_fulfillment')) {
                $table->boolean('is_online_fulfillment')->default(true)->after('is_active');
            }
            if (! Schema::hasColumn('locations', 'allow_storefront_pickup')) {
                $table->boolean('allow_storefront_pickup')->default(true)->after('is_online_fulfillment');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table): void {
            $columns = [
                'province',
                'city',
                'district',
                'village',
                'postal_code',
                'latitude',
                'longitude',
                'biteship_area_id',
                'is_online_fulfillment',
                'allow_storefront_pickup',
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('locations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
