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
        // 1. Marketplace Accounts (Multi-Tenant Connected Stores)
        Schema::create('marketplace_accounts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained()->cascadeOnDelete();
            $table->string('channel', 50)->index(); // shopee, tiktok_shop, tokopedia
            $table->string('shop_id', 100);
            $table->string('shop_name', 255);
            $table->string('status', 50)->default('connected'); // connected, disconnected, expired, error
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->timestamp('refresh_token_expires_at')->nullable();
            $table->boolean('auto_sync_stock')->default(true);
            $table->boolean('auto_sync_price')->default(false);
            $table->integer('stock_buffer')->default(0);
            $table->decimal('price_multiplier', 5, 2)->default(1.00);
            $table->json('settings')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['business_id', 'channel', 'shop_id']);
        });

        // 2. Marketplace Product Mappings & Per-Channel Pricing
        Schema::create('marketplace_product_mappings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('marketplace_account_id')->nullable()->constrained('marketplace_accounts')->nullOnDelete();
            $table->string('channel', 50)->index();
            $table->string('external_product_id', 100)->nullable()->index();
            $table->string('external_sku_id', 100)->nullable();
            $table->string('external_sku_code', 100)->nullable();
            $table->string('external_product_name', 255)->nullable();
            $table->decimal('channel_price', 15, 2)->nullable();
            $table->decimal('price_multiplier', 5, 2)->nullable()->default(1.00);
            $table->boolean('sync_price_auto')->default(true);
            $table->integer('channel_stock')->nullable();
            $table->integer('custom_stock')->nullable();
            $table->integer('stock_buffer')->default(0);
            $table->boolean('sync_stock_auto')->default(true);
            $table->boolean('is_active')->default(true);
            $table->string('sync_status', 50)->default('synced'); // synced, pending, failed, disabled
            $table->timestamp('last_price_synced_at')->nullable();
            $table->timestamp('last_stock_synced_at')->nullable();
            $table->text('last_sync_error')->nullable();
            $table->json('raw_metadata')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'product_id', 'channel'], 'mp_mapping_business_prod_channel_unique');
        });

        // 3. Marketplace Orders
        Schema::create('marketplace_orders', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('marketplace_account_id')->constrained()->cascadeOnDelete();
            $table->string('channel', 50)->index();
            $table->string('external_order_id', 100)->index();
            $table->string('external_order_sn', 100)->nullable();
            $table->string('buyer_name', 255)->nullable();
            $table->string('buyer_phone', 50)->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('channel_fee', 15, 2)->default(0);
            $table->string('order_status', 50)->default('UNPAID');
            $table->json('items_summary')->nullable();
            $table->string('shipping_provider', 100)->nullable();
            $table->string('tracking_number', 100)->nullable();
            $table->uuid('synced_order_id')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('placed_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'channel', 'external_order_id'], 'mp_orders_unique');
        });

        // 4. Marketplace Sync & Webhook Logs
        Schema::create('marketplace_sync_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained()->cascadeOnDelete();
            $table->uuid('marketplace_account_id')->nullable();
            $table->string('channel', 50)->index();
            $table->string('entity_type', 50); // product, price, stock, order, webhook, auth
            $table->string('entity_id', 100)->nullable();
            $table->string('action', 50); // push_price, push_stock, pull_order, webhook_event, token_refresh
            $table->string('status', 50)->default('success'); // success, failed, warning
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['business_id', 'channel', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_sync_logs');
        Schema::dropIfExists('marketplace_orders');
        Schema::dropIfExists('marketplace_product_mappings');
        Schema::dropIfExists('marketplace_accounts');
    }
};
