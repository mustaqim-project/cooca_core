<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commerce_group_orders', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('host_customer_id')->constrained('global_customers')->cascadeOnDelete();
            $table->string('title');
            $table->string('share_token')->unique();
            $table->date('scheduled_date')->nullable();
            $table->string('scheduled_time_slot')->nullable();
            $table->string('status', 32)->default('open'); // open, locked, checked_out, cancelled
            $table->foreignUuid('commerce_order_id')->nullable()->constrained('commerce_orders')->nullOnDelete();
            $table->text('delivery_address')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'status']);
            $table->index('share_token');
        });

        Schema::create('commerce_group_order_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('group_order_id')->constrained('commerce_group_orders')->cascadeOnDelete();
            $table->foreignUuid('global_customer_id')->constrained('global_customers')->cascadeOnDelete();
            $table->string('member_name');
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->json('selected_modifiers')->nullable();
            $table->timestamps();

            $table->index('group_order_id');
            $table->index('global_customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_group_order_items');
        Schema::dropIfExists('commerce_group_orders');
    }
};
