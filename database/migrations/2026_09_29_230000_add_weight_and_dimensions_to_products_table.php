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
            if (! Schema::hasColumn('products', 'weight')) {
                $table->decimal('weight', 10, 2)->default(200.00)->after('min_stock')->comment('Berat produk dalam gram untuk kalkulasi ongkir');
            }
            if (! Schema::hasColumn('products', 'length')) {
                $table->decimal('length', 10, 2)->nullable()->after('weight')->comment('Panjang paket dalam cm');
            }
            if (! Schema::hasColumn('products', 'width')) {
                $table->decimal('width', 10, 2)->nullable()->after('length')->comment('Lebar paket dalam cm');
            }
            if (! Schema::hasColumn('products', 'height')) {
                $table->decimal('height', 10, 2)->nullable()->after('width')->comment('Tinggi paket dalam cm');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $columns = ['weight', 'length', 'width', 'height'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
