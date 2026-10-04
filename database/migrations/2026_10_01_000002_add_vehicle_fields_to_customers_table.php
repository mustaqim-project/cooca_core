<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            if (! Schema::hasColumn('customers', 'vehicle_license_plate')) {
                $table->string('vehicle_license_plate', 30)->nullable()->after('company_name');
            }
            if (! Schema::hasColumn('customers', 'vehicle_model')) {
                $table->string('vehicle_model', 100)->nullable()->after('vehicle_license_plate');
            }
            if (! Schema::hasColumn('customers', 'vehicle_mileage')) {
                $table->unsignedInteger('vehicle_mileage')->nullable()->after('vehicle_model');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            $cols = [];
            if (Schema::hasColumn('customers', 'vehicle_license_plate')) {
                $cols[] = 'vehicle_license_plate';
            }
            if (Schema::hasColumn('customers', 'vehicle_model')) {
                $cols[] = 'vehicle_model';
            }
            if (Schema::hasColumn('customers', 'vehicle_mileage')) {
                $cols[] = 'vehicle_mileage';
            }
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
