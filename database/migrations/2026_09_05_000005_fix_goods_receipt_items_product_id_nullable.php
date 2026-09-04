<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Pastikan goods_receipt_items.product_id nullable pada MySQL untuk mendukung
 * penerimaan item bebas (non-inventori) dan material.
 *
 * Migration ini idempotent: semua operasi DDL dibungkus pengecekan/try-catch agar
 * bisa dijalankan dari kondisi kolom NOT NULL maupun nullable, dan tidak pernah gagal.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // 1. Lepas FK product_id (jika masih ada).
        foreach ($this->productForeignKeys() as $fk) {
            try {
                DB::statement('ALTER TABLE `goods_receipt_items` DROP FOREIGN KEY `' . $fk . '`');
            } catch (Throwable) {
                // abaikan bila constraint sudah tidak ada (idempotent)
            }
        }

        // 2. Lepas index non-primary pada product_id (sisa lama).
        foreach ($this->productIndexes() as $idx) {
            try {
                DB::statement('ALTER TABLE `goods_receipt_items` DROP INDEX `' . $idx . '`');
            } catch (Throwable) {
                // abaikan bila index sudah tidak ada
            }
        }

        // 3. Jadikan kolom product_id nullable.
        DB::statement('ALTER TABLE `goods_receipt_items` MODIFY `product_id` CHAR(36) NULL DEFAULT NULL');

        // 4. Bangun ulang foreign key (nullable → ON DELETE SET NULL), bila belum ada.
        $exists = collect($this->productForeignKeys())->contains('goods_receipt_items_product_id_foreign');
        if (! $exists) {
            try {
                DB::statement(
                    'ALTER TABLE `goods_receipt_items`
                     ADD CONSTRAINT `goods_receipt_items_product_id_foreign`
                     FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL'
                );
            } catch (Throwable) {
                // idempotent: bila sudah ada / tidak dapat dibuat, biarkan.
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach ($this->productForeignKeys() as $fk) {
            try {
                DB::statement('ALTER TABLE `goods_receipt_items` DROP FOREIGN KEY `' . $fk . '`');
            } catch (Throwable) {
                // abaikan
            }
        }

        DB::statement('ALTER TABLE `goods_receipt_items` MODIFY `product_id` CHAR(36) NOT NULL');

        $exists = collect($this->productForeignKeys())->contains('goods_receipt_items_product_id_foreign');
        if (! $exists) {
            try {
                DB::statement(
                    'ALTER TABLE `goods_receipt_items`
                     ADD CONSTRAINT `goods_receipt_items_product_id_foreign`
                     FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE'
                );
            } catch (Throwable) {
                // abaikan
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function productForeignKeys(): array
    {
        return array_map(
            static fn (stdClass $r): string => $r->cname,
            DB::select(
                "SELECT constraint_name AS cname
                 FROM information_schema.key_column_usage
                 WHERE table_schema = DATABASE()
                   AND table_name = 'goods_receipt_items'
                   AND column_name = 'product_id'
                   AND referenced_table_name IS NOT NULL"
            )
        );
    }

    /**
     * @return array<int, string>
     */
    private function productIndexes(): array
    {
        return array_map(
            static fn (stdClass $r): string => $r->iname,
            DB::select(
                "SELECT index_name AS iname
                 FROM information_schema.statistics
                 WHERE table_schema = DATABASE()
                   AND table_name = 'goods_receipt_items'
                   AND column_name = 'product_id'
                   AND index_name <> 'PRIMARY'"
            )
        );
    }
};