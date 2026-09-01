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
        Schema::create('pos_orders', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignUuid('pos_shift_id')->nullable()->constrained('pos_shifts')->nullOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('order_number', 64);
            $table->date('order_date');
            $table->string('status', 30)->default('completed'); // draft_held, completed, voided, refunded, partial_refund
            $table->string('order_type', 30)->default('takeaway'); // dine_in, takeaway, delivery
            $table->string('table_or_reference', 100)->nullable();
            $table->string('customer_name_guest', 150)->nullable();
            
            // Financial Amounts
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->string('discount_type', 20)->default('fixed'); // fixed, percentage
            $table->decimal('discount_value', 15, 2)->default(0.00);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->string('voucher_code', 64)->nullable();
            $table->decimal('voucher_discount_amount', 15, 2)->default(0.00);
            $table->decimal('tax_percentage', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('service_charge_percentage', 5, 2)->default(0.00);
            $table->decimal('service_charge_amount', 15, 2)->default(0.00);
            $table->decimal('rounding_amount', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->decimal('paid_amount', 15, 2)->default(0.00);
            $table->decimal('change_amount', 15, 2)->default(0.00);

            // Integrated HPP & Profit
            $table->decimal('total_hpp_cost', 15, 2)->default(0.00);
            $table->decimal('total_gross_profit', 15, 2)->default(0.00);

            // Loyalty Points
            $table->integer('points_earned')->default(0);
            $table->integer('points_redeemed')->default(0);
            $table->decimal('points_discount_amount', 15, 2)->default(0.00);

            // Hold & Resume
            $table->string('hold_label', 150)->nullable();
            $table->timestamp('held_at')->nullable();

            // Void / Refund / Approvals
            $table->text('void_reason')->nullable();
            $table->foreignUuid('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();

            $table->text('refund_reason')->nullable();
            $table->foreignUuid('refunded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('refunded_at')->nullable();

            $table->foreignUuid('supervisor_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('supervisor_approved_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['business_id', 'order_number']);
            $table->index(['business_id', 'status']);
            $table->index(['business_id', 'order_date']);
            $table->index(['business_id', 'pos_shift_id']);
            $table->index(['business_id', 'location_id']);
            $table->index(['business_id', 'customer_id']);
        });

        Schema::create('pos_order_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('pos_order_id')->constrained('pos_orders')->cascadeOnDelete();
            $table->foreignUuid('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name', 255);
            $table->string('product_code', 100)->nullable();
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('unit_cost_hpp', 15, 2)->default(0.00);
            $table->decimal('quantity', 15, 4)->default(1.0000);
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('total_price', 15, 2)->default(0.00);
            $table->decimal('total_hpp', 15, 2)->default(0.00);
            $table->string('batch_number', 100)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index(['pos_order_id', 'product_id']);
        });

        Schema::create('pos_order_payments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('pos_order_id')->constrained('pos_orders')->cascadeOnDelete();
            $table->string('payment_method', 30); // cash, qris, transfer, edc_debit, edc_credit, customer_credit, loyalty_points
            $table->decimal('amount', 15, 2);
            $table->string('reference_number', 100)->nullable();
            $table->decimal('fee_amount', 15, 2)->default(0.00);
            $table->decimal('net_amount', 15, 2)->default(0.00);
            $table->string('status', 20)->default('paid'); // paid, refunded
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['pos_order_id', 'payment_method']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_order_payments');
        Schema::dropIfExists('pos_order_items');
        Schema::dropIfExists('pos_orders');
    }
};
