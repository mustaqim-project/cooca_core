<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_returns', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('invoice_id')->constrained('invoices')->restrictOnDelete();
            $table->foreignUuid('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignUuid('location_id')->constrained('locations')->restrictOnDelete();
            $table->string('return_number', 64);
            $table->date('return_date');
            $table->string('reason', 255);
            $table->string('status', 20)->default('draft');
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->string('refund_method', 20)->default('credit_note');
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignUuid('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'return_number']);
            $table->index(['business_id', 'invoice_id', 'status']);
        });

        Schema::create('sales_return_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('sales_return_id')->constrained('sales_returns')->cascadeOnDelete();
            $table->foreignUuid('invoice_item_id')->constrained('invoice_items')->restrictOnDelete();
            $table->foreignUuid('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('item_name', 255);
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('unit_hpp', 15, 2)->default(0.00);
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['sales_return_id', 'invoice_item_id'], 'sr_item_source_unique');
        });

        Schema::create('purchase_returns', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('goods_receipt_id')->constrained('goods_receipts')->restrictOnDelete();
            $table->foreignUuid('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->foreignUuid('supplier_invoice_id')->nullable()->constrained('supplier_invoices')->nullOnDelete();
            $table->foreignUuid('location_id')->constrained('locations')->restrictOnDelete();
            $table->string('return_number', 64);
            $table->date('return_date');
            $table->string('reason', 255);
            $table->string('status', 20)->default('draft');
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->string('debit_note_number', 64)->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignUuid('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'return_number']);
            $table->index(['business_id', 'goods_receipt_id', 'status']);
        });

        Schema::create('purchase_return_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('purchase_return_id')->constrained('purchase_returns')->cascadeOnDelete();
            $table->foreignUuid('goods_receipt_item_id')->constrained('goods_receipt_items')->restrictOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            $table->string('item_name', 255);
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_cost', 15, 2)->default(0.00);
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['purchase_return_id', 'goods_receipt_item_id'], 'pr_item_source_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_return_items');
        Schema::dropIfExists('purchase_returns');
        Schema::dropIfExists('sales_return_items');
        Schema::dropIfExists('sales_returns');
    }
};
