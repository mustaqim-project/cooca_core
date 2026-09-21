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
        Schema::table('pos_orders', function (Blueprint $table) {
            // Bengkel Otomotif (Vehicle & SPK)
            if (! Schema::hasColumn('pos_orders', 'vehicle_license_plate')) {
                $table->string('vehicle_license_plate', 30)->nullable()->after('notes');
            }
            if (! Schema::hasColumn('pos_orders', 'vehicle_model')) {
                $table->string('vehicle_model', 100)->nullable()->after('vehicle_license_plate');
            }
            if (! Schema::hasColumn('pos_orders', 'vehicle_mileage')) {
                $table->unsignedInteger('vehicle_mileage')->nullable()->after('vehicle_model');
            }
            if (! Schema::hasColumn('pos_orders', 'technician_id')) {
                $table->foreignUuid('technician_id')->nullable()->after('vehicle_mileage')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('pos_orders', 'service_notes')) {
                $table->text('service_notes')->nullable()->after('technician_id');
            }

            // Laundry (Weight, Rack & Status Tracking)
            if (! Schema::hasColumn('pos_orders', 'laundry_weight_kg')) {
                $table->decimal('laundry_weight_kg', 8, 2)->nullable()->after('service_notes');
            }
            if (! Schema::hasColumn('pos_orders', 'rack_location')) {
                $table->string('rack_location', 50)->nullable()->after('laundry_weight_kg');
            }
            if (! Schema::hasColumn('pos_orders', 'estimated_completion_at')) {
                $table->dateTime('estimated_completion_at')->nullable()->after('rack_location');
            }
            if (! Schema::hasColumn('pos_orders', 'laundry_status')) {
                $table->string('laundry_status', 30)->nullable()->default(null)->after('estimated_completion_at');
            }
        });

        Schema::table('pos_order_items', function (Blueprint $table) {
            // Apotek & Klinik (Batch, Expired Date & Dosage)
            if (! Schema::hasColumn('pos_order_items', 'expired_date')) {
                $table->date('expired_date')->nullable()->after('batch_number');
            }
            if (! Schema::hasColumn('pos_order_items', 'dosage_instructions')) {
                $table->string('dosage_instructions', 255)->nullable()->after('expired_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            $columns = [
                'vehicle_license_plate',
                'vehicle_model',
                'vehicle_mileage',
                'technician_id',
                'service_notes',
                'laundry_weight_kg',
                'rack_location',
                'estimated_completion_at',
                'laundry_status',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('pos_orders', $column)) {
                    if ($column === 'technician_id') {
                        $table->dropForeign(['technician_id']);
                    }
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('pos_order_items', function (Blueprint $table) {
            $columns = ['expired_date', 'dosage_instructions'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('pos_order_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
