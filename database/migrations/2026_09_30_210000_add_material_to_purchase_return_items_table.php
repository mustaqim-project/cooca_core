<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add material_id column if not exists
        Schema::table('purchase_return_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('purchase_return_items', 'material_id')) {
                $table->foreignUuid('material_id')
                    ->nullable()
                    ->after('product_id')
                    ->constrained('materials')
                    ->nullOnDelete();
                $table->index('material_id');
            }
        });

        // 2. Make product_id nullable
        if (DB::getDriverName() === 'mysql') {
            // Drop foreign key if exists
            $fks = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'purchase_return_items'
                  AND COLUMN_NAME = 'product_id'
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            ");

            foreach ($fks as $fk) {
                try {
                    DB::statement('ALTER TABLE `purchase_return_items` DROP FOREIGN KEY `' . $fk->CONSTRAINT_NAME . '`');
                } catch (Throwable) {
                    // Ignore if already dropped
                }
            }

            // Modify product_id column to NULL
            DB::statement('ALTER TABLE `purchase_return_items` MODIFY `product_id` CHAR(36) NULL DEFAULT NULL');

            // Re-add foreign key with null on delete
            try {
                DB::statement(
                    'ALTER TABLE `purchase_return_items`
                     ADD CONSTRAINT `purchase_return_items_product_id_foreign`
                     FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL'
                );
            } catch (Throwable) {
                // Ignore if already added
            }
        } else {
            Schema::table('purchase_return_items', function (Blueprint $table): void {
                $table->uuid('product_id')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_return_items', function (Blueprint $table): void {
            if (Schema::hasColumn('purchase_return_items', 'material_id')) {
                $table->dropForeign(['material_id']);
                $table->dropIndex(['material_id']);
                $table->dropColumn('material_id');
            }
        });
    }
};
