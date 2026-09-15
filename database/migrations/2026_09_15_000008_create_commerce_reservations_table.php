<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('commerce_reservations');

        Schema::create('commerce_reservations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('commerce_order_id')->nullable()->constrained('commerce_orders')->nullOnDelete();
            $table->foreignUuid('pos_table_id')->nullable()->constrained('pos_tables')->nullOnDelete();
            $table->foreignUuid('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('reservation_code', 50)->unique();
            $table->string('customer_name', 150);
            $table->string('customer_phone', 50);
            $table->string('customer_email', 150)->nullable();
            $table->date('reservation_date');
            $table->string('time_slot', 50);
            $table->unsignedInteger('guest_count')->default(1);
            $table->string('status', 32)->default('pending_confirmation'); // pending_confirmation, confirmed, seated, completed, cancelled, no_show
            $table->text('notes')->nullable();
            $table->string('cancellation_reason', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'reservation_date'], 'cr_biz_date_idx');
            $table->index(['business_id', 'status'], 'cr_biz_status_idx');
            $table->index(['pos_table_id', 'reservation_date'], 'cr_table_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_reservations');
    }
};
