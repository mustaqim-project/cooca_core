<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_invoices', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->foreignUuid('goods_receipt_id')->constrained('goods_receipts')->restrictOnDelete();
            $table->foreignUuid('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->string('invoice_number', 64);
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->decimal('paid_amount', 15, 2)->default(0.00);
            $table->decimal('balance_due', 15, 2)->default(0.00);
            $table->string('status', 20)->default('unpaid');
            $table->timestamps();

            $table->unique(['business_id', 'invoice_number']);
            $table->unique(['business_id', 'goods_receipt_id']);
            $table->index(['business_id', 'supplier_id', 'status']);
            $table->index(['business_id', 'due_date']);
        });

        Schema::create('supplier_payments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('supplier_invoice_id')->constrained('supplier_invoices')->restrictOnDelete();
            $table->string('payment_number', 64);
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 30)->default('bank_transfer');
            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['business_id', 'payment_number']);
            $table->index(['business_id', 'supplier_invoice_id', 'payment_date'], 'sp_biz_inv_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');
        Schema::dropIfExists('supplier_invoices');
    }
};
