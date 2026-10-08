<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_wishlists', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('global_customer_id');
            $table->uuid('product_id');
            $table->timestamps();

            $table->foreign('global_customer_id')
                ->references('id')
                ->on('global_customers')
                ->cascadeOnDelete();

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();

            $table->unique(['global_customer_id', 'product_id'], 'uq_customer_product_wishlist');
            $table->index('global_customer_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_wishlists');
    }
};
