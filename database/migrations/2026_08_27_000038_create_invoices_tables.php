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
        Schema::create('invoices', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignUuid('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->string('invoice_number', 100);
            $table->date('invoice_date');
            $table->date('due_date');
            $table->string('status', 30)->default('draft'); // draft, sent, unpaid, partially_paid, paid, overdue, void
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->string('discount_type', 20)->default('fixed'); // fixed, percentage
            $table->decimal('discount_value', 15, 2)->default(0.00);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('tax_percentage', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('shipping_cost', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->decimal('paid_amount', 15, 2)->default(0.00);
            $table->decimal('balance_due', 15, 2)->default(0.00);
            $table->decimal('total_hpp_cost', 15, 2)->default(0.00);
            $table->decimal('total_gross_profit', 15, 2)->default(0.00);
            $table->string('payment_terms', 100)->nullable(); // e.g. "Net 30", "COD", "DP 50%"
            $table->json('bank_details_snapshot')->nullable(); // Bank account details snapshot at invoice creation
            $table->text('notes')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['business_id', 'invoice_number']);
            $table->index(['business_id', 'status']);
            $table->index(['business_id', 'invoice_date']);
            $table->index(['business_id', 'due_date']);
            $table->index(['business_id', 'customer_id']);
        });

        Schema::create('invoice_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignUuid('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('item_name');
            $table->string('sku', 100)->nullable();
            $table->text('description')->nullable();
            $table->decimal('quantity', 15, 4)->default(1.0000);
            $table->foreignUuid('unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('unit_hpp', 15, 2)->default(0.00); // Snapshot HPP for gross profit tracking
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('total_hpp', 15, 2)->default(0.00);
            $table->decimal('gross_profit', 15, 2)->default(0.00);
            $table->timestamps();

            $table->index(['invoice_id', 'product_id']);
        });

        Schema::create('invoice_payments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->string('payment_number', 100);
            $table->date('payment_date');
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->string('payment_method', 50)->default('bank_transfer'); // bank_transfer, cash, qris, credit_card, cheque
            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->string('receipt_file_path', 255)->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['business_id', 'payment_number']);
            $table->index(['invoice_id', 'payment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
