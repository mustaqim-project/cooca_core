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
        Schema::create('global_customer_addresses', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('global_customer_id')->constrained('global_customers')->cascadeOnDelete();
            $table->string('label', 50)->default('Rumah'); // Rumah, Kantor, Apartemen, Toko, Gudang, dll.
            $table->string('recipient_name', 150);
            $table->string('recipient_phone', 30);
            $table->text('full_address');
            $table->string('village', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('biteship_area_id', 100)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['global_customer_id', 'is_default'], 'idx_gca_customer_default');
            $table->index('biteship_area_id', 'idx_gca_biteship_area');
        });

        Schema::table('commerce_orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('commerce_orders', 'destination_latitude')) {
                $table->decimal('destination_latitude', 10, 7)->nullable()->after('destination_postal_code');
            }
            if (! Schema::hasColumn('commerce_orders', 'destination_longitude')) {
                $table->decimal('destination_longitude', 10, 7)->nullable()->after('destination_latitude');
            }
            if (! Schema::hasColumn('commerce_orders', 'destination_area_id')) {
                $table->string('destination_area_id', 100)->nullable()->after('destination_longitude');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_customer_addresses');

        Schema::table('commerce_orders', function (Blueprint $table): void {
            $columnsToDrop = [];
            if (Schema::hasColumn('commerce_orders', 'destination_latitude')) {
                $columnsToDrop[] = 'destination_latitude';
            }
            if (Schema::hasColumn('commerce_orders', 'destination_longitude')) {
                $columnsToDrop[] = 'destination_longitude';
            }
            if (Schema::hasColumn('commerce_orders', 'destination_area_id')) {
                $columnsToDrop[] = 'destination_area_id';
            }
            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
