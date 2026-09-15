<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commerce_orders', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignUuid('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignUuid('payment_method_id')->nullable()->constrained('commerce_payment_methods')->nullOnDelete();
            $table->string('order_number', 64)->unique();
            $table->string('tracking_token', 64)->unique();
            $table->string('order_type', 32)->default('direct_checkout'); // direct_checkout, request_order, scheduled_order, customer_po, po_batch, reservation
            $table->string('fulfillment_type', 32)->default('pickup'); // pickup, merchant_delivery, courier_manual, dine_in, service_on_site
            $table->string('status', 32)->default('pending_payment'); // draft, pending_review, pending_payment, proof_submitted, payment_rejected, paid, processing, ready, fulfilled, completed, cancelled, expired
            $table->string('payment_status', 32)->default('unpaid'); // unpaid, verifying, paid, failed, refunded
            $table->string('customer_name', 150);
            $table->string('customer_phone', 50);
            $table->string('customer_email', 150)->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('shipping_notes', 255)->nullable();
            $table->date('scheduled_date')->nullable();
            $table->string('scheduled_time_slot', 50)->nullable();
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('shipping_cost', 15, 2)->default(0.00);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->dateTime('reserved_until')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->string('rejection_reason', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'status'], 'co_biz_status_idx');
            $table->index(['business_id', 'order_type'], 'co_biz_type_idx');
            $table->index(['business_id', 'created_at'], 'co_biz_created_idx');
        });

        Schema::create('commerce_order_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('commerce_order_id')->constrained('commerce_orders')->cascadeOnDelete();
            $table->foreignUuid('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name', 200);
            $table->string('product_type', 20)->default('goods'); // goods, service
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('quantity', 15, 4)->default(1.0000);
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->string('notes', 255)->nullable();
            $table->json('modifiers_snapshot')->nullable();
            $table->timestamps();

            $table->index('commerce_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_order_items');
        Schema::dropIfExists('commerce_orders');
    }
};
