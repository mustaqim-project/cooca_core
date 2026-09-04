<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan kolom snapshot harga beli yang immutable pada detail PO.
     * Snapshot ini diisi saat PO dibuat/ditetapkan dan TIDAK boleh berubah
     * ketika harga beli pada master product/supplier diperbarui di masa depan.
     */
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table): void {
            // Harga beli/unit yang disimpan pada saat PO dibuat (immutable snapshot).
            $table->decimal('purchase_price_snapshot', 15, 2)->default(0.00)->after('unit_price');

            // Nama supplier pada saat transaksi (melindungi histori bila nama supplier berubah).
            $table->string('supplier_name_snapshot', 255)->nullable()->after('item_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table): void {
            $table->dropColumn(['purchase_price_snapshot', 'supplier_name_snapshot']);
        });
    }
};