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
        if (Schema::hasTable('commerce_product_reviews')) {
            return;
        }

        Schema::create('commerce_product_reviews', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('business_id')->index();
            $table->uuid('product_id')->index();
            $table->uuid('commerce_order_id')->index();
            $table->uuid('commerce_order_item_id')->nullable()->index();
            $table->uuid('global_customer_id')->index();
            $table->unsignedTinyInteger('rating')->default(5); // 1-5 stars
            $table->text('review_text')->nullable();
            $table->json('images_payload')->nullable(); // optional review photos
            $table->boolean('is_verified_purchase')->default(true);
            $table->text('seller_reply')->nullable();
            $table->timestamp('seller_replied_at')->nullable();
            $table->boolean('is_published')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('business_id')->references('id')->on('businesses')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('commerce_order_id')->references('id')->on('commerce_orders')->cascadeOnDelete();
            $table->foreign('global_customer_id')->references('id')->on('global_customers')->cascadeOnDelete();

            $table->unique(['commerce_order_id', 'product_id', 'global_customer_id'], 'order_product_customer_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commerce_product_reviews');
    }
};
