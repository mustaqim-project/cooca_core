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
        // 1. Expand locations table
        Schema::table('locations', function (Blueprint $table): void {
            if (! Schema::hasColumn('locations', 'type')) {
                $table->string('type', 30)->default('outlet')->after('slug'); // outlet, warehouse, central_kitchen
            }
            if (! Schema::hasColumn('locations', 'code')) {
                $table->string('code', 50)->nullable()->after('type');
            }
            if (! Schema::hasColumn('locations', 'phone')) {
                $table->string('phone', 50)->nullable()->after('address');
            }
        });

        // 2. Inventory Stocks (Real-time stock balance per outlet/warehouse)
        Schema::create('inventory_stocks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('location_id')->constrained('locations')->cascadeOnDelete();
            $table->foreignUuid('material_id')->nullable()->constrained('materials')->cascadeOnDelete();
            $table->foreignUuid('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 15, 4)->default(0.0000);
            $table->decimal('reserved_quantity', 15, 4)->default(0.0000);
            $table->decimal('last_cost', 15, 2)->default(0.00);
            $table->timestamps();

            $table->index(['business_id', 'location_id', 'material_id']);
            $table->index(['business_id', 'location_id', 'product_id']);
            $table->index(['business_id', 'product_id']);
        });

        // 3. Stock Movements (Kartu Stok / Audit Trail of every unit change)
        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('location_id')->constrained('locations')->cascadeOnDelete();
            $table->foreignUuid('material_id')->nullable()->constrained('materials')->cascadeOnDelete();
            $table->foreignUuid('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->string('movement_type', 30); // pos_sale, pos_refund, opname, adjustment, transfer_in, transfer_out, goods_receipt, goods_issue, initial
            $table->string('reference_id', 64)->nullable();
            $table->string('reference_number', 100)->nullable();
            $table->decimal('quantity_change', 15, 4); // positive for in, negative for out
            $table->decimal('balance_after', 15, 4)->default(0.0000);
            $table->decimal('unit_cost', 15, 2)->default(0.00);
            $table->decimal('total_cost', 15, 2)->default(0.00);
            $table->string('batch_number', 100)->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('notes', 255)->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['business_id', 'location_id', 'product_id'], 'sm_biz_loc_prod_idx');
            $table->index(['business_id', 'movement_type']);
            $table->index(['business_id', 'created_at']);
        });

        // 4. Stock Adjustments (Damage, Loss, Expiry)
        Schema::create('stock_adjustments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('location_id')->constrained('locations')->cascadeOnDelete();
            $table->string('adjustment_number', 64);
            $table->date('adjustment_date');
            $table->string('reason', 50); // damaged, expired, lost, correction, other
            $table->string('status', 20)->default('approved'); // draft, approved, rejected
            $table->decimal('total_loss_cost', 15, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'adjustment_number']);
            $table->index(['business_id', 'location_id']);
        });

        Schema::create('stock_adjustment_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('stock_adjustment_id')->constrained('stock_adjustments')->cascadeOnDelete();
            $table->foreignUuid('material_id')->nullable()->constrained('materials')->cascadeOnDelete();
            $table->foreignUuid('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->decimal('system_quantity', 15, 4)->default(0.0000);
            $table->decimal('adjusted_quantity', 15, 4)->default(0.0000);
            $table->decimal('difference_quantity', 15, 4)->default(0.0000);
            $table->decimal('unit_cost', 15, 2)->default(0.00);
            $table->decimal('total_cost', 15, 2)->default(0.00);
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });

        // 5. Stock Opnames (Physical Stock Count & Variance)
        Schema::create('stock_opnames', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('location_id')->constrained('locations')->cascadeOnDelete();
            $table->string('opname_number', 64);
            $table->date('opname_date');
            $table->string('status', 20)->default('in_progress'); // draft, in_progress, completed, reconciled
            $table->text('notes')->nullable();
            $table->foreignUuid('conducted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('reconciled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reconciled_at')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'opname_number']);
            $table->index(['business_id', 'location_id']);
        });

        Schema::create('stock_opname_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('stock_opname_id')->constrained('stock_opnames')->cascadeOnDelete();
            $table->foreignUuid('material_id')->nullable()->constrained('materials')->cascadeOnDelete();
            $table->foreignUuid('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->decimal('system_quantity', 15, 4)->default(0.0000);
            $table->decimal('physical_quantity', 15, 4)->default(0.0000);
            $table->decimal('difference_quantity', 15, 4)->default(0.0000);
            $table->decimal('unit_cost', 15, 2)->default(0.00);
            $table->decimal('total_difference_cost', 15, 2)->default(0.00);
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });

        // 6. Stock Transfers (Outlet to Warehouse or Outlet to Outlet)
        Schema::create('stock_transfers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('source_location_id')->constrained('locations')->cascadeOnDelete();
            $table->foreignUuid('destination_location_id')->constrained('locations')->cascadeOnDelete();
            $table->string('transfer_number', 64);
            $table->date('transfer_date');
            $table->string('status', 20)->default('pending'); // pending, in_transit, received, cancelled
            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'transfer_number']);
            $table->index(['business_id', 'source_location_id']);
            $table->index(['business_id', 'destination_location_id']);
        });

        Schema::create('stock_transfer_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('stock_transfer_id')->constrained('stock_transfers')->cascadeOnDelete();
            $table->foreignUuid('material_id')->nullable()->constrained('materials')->cascadeOnDelete();
            $table->foreignUuid('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 15, 4)->default(1.0000);
            $table->decimal('unit_cost', 15, 2)->default(0.00);
            $table->decimal('total_cost', 15, 2)->default(0.00);
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });

        // 7. Goods Receipts (Incoming items) & Goods Issues (Internal consumption)
        Schema::create('goods_receipts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('location_id')->constrained('locations')->cascadeOnDelete();
            $table->foreignUuid('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignUuid('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->string('receipt_number', 64);
            $table->date('receipt_date');
            $table->string('status', 20)->default('received');
            $table->text('notes')->nullable();
            $table->foreignUuid('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['business_id', 'receipt_number']);
        });

        Schema::create('goods_receipt_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('goods_receipt_id')->constrained('goods_receipts')->cascadeOnDelete();
            $table->foreignUuid('material_id')->nullable()->constrained('materials')->cascadeOnDelete();
            $table->foreignUuid('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->string('item_name', 255)->nullable();
            $table->decimal('quantity', 15, 4)->default(1.0000);
            $table->decimal('unit_cost', 15, 2)->default(0.00);
            $table->string('batch_number', 100)->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('goods_issues', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('location_id')->constrained('locations')->cascadeOnDelete();
            $table->string('issue_number', 64);
            $table->date('issue_date');
            $table->string('recipient_or_department', 100)->nullable();
            $table->string('reason', 255);
            $table->decimal('total_cost', 15, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['business_id', 'issue_number']);
        });

        Schema::create('goods_issue_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('goods_issue_id')->constrained('goods_issues')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 15, 4)->default(1.0000);
            $table->decimal('unit_cost', 15, 2)->default(0.00);
            $table->decimal('total_cost', 15, 2)->default(0.00);
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });

        // 8. Batches & Serials
        Schema::create('product_batches', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('location_id')->constrained('locations')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('batch_number', 100);
            $table->date('expiry_date')->nullable();
            $table->decimal('quantity', 15, 4)->default(0.0000);
            $table->decimal('unit_cost', 15, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['business_id', 'product_id', 'batch_number']);
        });

        Schema::create('product_serials', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('location_id')->constrained('locations')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('serial_number', 100);
            $table->string('status', 30)->default('in_stock'); // in_stock, sold, transferred, damaged
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'product_id', 'serial_number'], 'biz_prod_serial_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_serials');
        Schema::dropIfExists('product_batches');
        Schema::dropIfExists('goods_issue_items');
        Schema::dropIfExists('goods_issues');
        Schema::dropIfExists('goods_receipt_items');
        Schema::dropIfExists('goods_receipts');
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('stock_opname_items');
        Schema::dropIfExists('stock_opnames');
        Schema::dropIfExists('stock_adjustment_items');
        Schema::dropIfExists('stock_adjustments');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('inventory_stocks');

        Schema::table('locations', function (Blueprint $table): void {
            if (Schema::hasColumn('locations', 'phone')) {
                $table->dropColumn('phone');
            }
            if (Schema::hasColumn('locations', 'code')) {
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('locations', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
