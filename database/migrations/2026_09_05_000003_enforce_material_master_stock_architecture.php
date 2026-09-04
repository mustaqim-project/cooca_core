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
        // 1. Add direct_material_id to products table
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'direct_material_id')) {
                $table->foreignUuid('direct_material_id')->nullable()->after('output_unit_id')->constrained('materials')->nullOnDelete();
                $table->index(['business_id', 'direct_material_id']);
            }
        });

        // 2. Add material_id to inventory_stocks table and make product_id nullable
        Schema::table('inventory_stocks', function (Blueprint $table): void {
            if (! Schema::hasColumn('inventory_stocks', 'material_id')) {
                $table->foreignUuid('material_id')->nullable()->after('location_id')->constrained('materials')->cascadeOnDelete();
                $table->index(['business_id', 'location_id', 'material_id']);
            }
        });

        // 3. Add material_id to stock_movements table
        Schema::table('stock_movements', function (Blueprint $table): void {
            if (! Schema::hasColumn('stock_movements', 'material_id')) {
                $table->foreignUuid('material_id')->nullable()->after('location_id')->constrained('materials')->cascadeOnDelete();
                $table->index(['business_id', 'location_id', 'material_id']);
            }
        });

        // 4. Add material_id to stock_opname_items table
        Schema::table('stock_opname_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('stock_opname_items', 'material_id')) {
                $table->foreignUuid('material_id')->nullable()->after('stock_opname_id')->constrained('materials')->cascadeOnDelete();
                $table->index('material_id');
            }
        });

        // 5. Add material_id to stock_adjustment_items table
        Schema::table('stock_adjustment_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('stock_adjustment_items', 'material_id')) {
                $table->foreignUuid('material_id')->nullable()->after('stock_adjustment_id')->constrained('materials')->cascadeOnDelete();
                $table->index('material_id');
            }
        });

        // 6. Add material_id to stock_transfer_items table (if exists)
        if (Schema::hasTable('stock_transfer_items')) {
            Schema::table('stock_transfer_items', function (Blueprint $table): void {
                if (! Schema::hasColumn('stock_transfer_items', 'material_id')) {
                    $table->foreignUuid('material_id')->nullable()->after('stock_transfer_id')->constrained('materials')->cascadeOnDelete();
                    $table->index('material_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'direct_material_id')) {
                $table->dropConstrainedForeignId('direct_material_id');
            }
        });

        Schema::table('inventory_stocks', function (Blueprint $table): void {
            if (Schema::hasColumn('inventory_stocks', 'material_id')) {
                $table->dropConstrainedForeignId('material_id');
            }
        });

        Schema::table('stock_movements', function (Blueprint $table): void {
            if (Schema::hasColumn('stock_movements', 'material_id')) {
                $table->dropConstrainedForeignId('material_id');
            }
        });

        Schema::table('stock_opname_items', function (Blueprint $table): void {
            if (Schema::hasColumn('stock_opname_items', 'material_id')) {
                $table->dropConstrainedForeignId('material_id');
            }
        });

        Schema::table('stock_adjustment_items', function (Blueprint $table): void {
            if (Schema::hasColumn('stock_adjustment_items', 'material_id')) {
                $table->dropConstrainedForeignId('material_id');
            }
        });

        if (Schema::hasTable('stock_transfer_items')) {
            Schema::table('stock_transfer_items', function (Blueprint $table): void {
                if (Schema::hasColumn('stock_transfer_items', 'material_id')) {
                    $table->dropConstrainedForeignId('material_id');
                }
            });
        }
    }
};
