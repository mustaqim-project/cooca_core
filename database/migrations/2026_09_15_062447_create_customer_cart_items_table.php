<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_cart_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('cart_id')->constrained('customer_carts')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 15, 2)->default(0); // snapshot saat add
            $table->text('notes')->nullable();
            $table->json('selected_modifiers')->nullable(); // untuk POS modifier
            $table->timestamps();

            $table->index('cart_id');
            $table->unique(['cart_id', 'product_id']); // 1 product per cart, update qty
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_cart_items');
    }
};
