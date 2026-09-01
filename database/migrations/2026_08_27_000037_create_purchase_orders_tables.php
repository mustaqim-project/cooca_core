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
        Schema::create('purchase_orders', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('po_type', 30)->default('customer'); // 'customer' (Sales PO) or 'supplier' (Vendor PO)
            $table->string('po_number', 100);
            $table->string('reference_number', 100)->nullable();
            $table->foreignUuid('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignUuid('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->string('status', 30)->default('draft'); // draft, confirmed, partially_invoiced, fully_invoiced, completed, cancelled
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->string('discount_type', 20)->default('percentage'); // percentage, fixed
            $table->decimal('discount_value', 15, 2)->default(0.00);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('tax_percentage', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->text('terms_and_conditions')->nullable();
            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'po_number']);
            $table->index(['business_id', 'status']);
            $table->index(['business_id', 'order_date']);
            $table->index(['business_id', 'po_type']);
        });

        Schema::create('purchase_order_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->string('item_type', 30)->default('product'); // product, material, custom
            $table->foreignUuid('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignUuid('material_id')->nullable()->constrained('materials')->nullOnDelete();
            $table->string('item_name');
            $table->string('sku', 100)->nullable();
            $table->decimal('quantity', 15, 4)->default(1.0000);
            $table->decimal('invoiced_quantity', 15, 4)->default(0.0000);
            $table->foreignUuid('unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('cost_price_snapshot', 15, 2)->default(0.00);
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['purchase_order_id', 'product_id']);
            $table->index(['purchase_order_id', 'material_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
    }
};
