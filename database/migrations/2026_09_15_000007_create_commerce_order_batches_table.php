<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commerce_orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('commerce_orders', 'company_name')) {
                $table->string('company_name', 150)->nullable()->after('customer_name');
            }
            if (! Schema::hasColumn('commerce_orders', 'customer_po_number')) {
                $table->string('customer_po_number', 100)->nullable()->after('order_number');
            }
        });

        Schema::create('commerce_order_batches', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('commerce_order_id')->constrained('commerce_orders')->cascadeOnDelete();
            $table->unsignedInteger('batch_number')->default(1);
            $table->string('batch_code', 50)->nullable();
            $table->date('scheduled_date');
            $table->string('scheduled_time_slot', 50)->nullable();
            $table->decimal('quantity', 15, 4)->default(1.0000);
            $table->string('status', 32)->default('scheduled'); // scheduled, in_preparation, shipped, delivered, cancelled
            $table->text('shipping_address')->nullable();
            $table->string('tracking_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['commerce_order_id', 'batch_number'], 'cob_order_batch_idx');
            $table->index(['status'], 'cob_status_idx');
            $table->index(['scheduled_date'], 'cob_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_order_batches');

        Schema::table('commerce_orders', function (Blueprint $table): void {
            if (Schema::hasColumn('commerce_orders', 'customer_po_number')) {
                $table->dropColumn('customer_po_number');
            }
            if (Schema::hasColumn('commerce_orders', 'company_name')) {
                $table->dropColumn('company_name');
            }
        });
    }
};
