<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('commerce_shipping_rules')) {
            Schema::create('commerce_shipping_rules', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
                $table->string('name', 100);
                $table->string('rule_type', 50)->default('flat'); // flat, distance_tier, free_threshold
                $table->decimal('min_distance_km', 6, 2)->nullable();
                $table->decimal('max_distance_km', 6, 2)->nullable();
                $table->decimal('rate_amount', 15, 2)->default(0.00);
                $table->decimal('min_order_for_free', 15, 2)->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index(['business_id', 'is_active'], 'csr_biz_active_idx');
            });
        }

        if (Schema::hasTable('commerce_orders') && ! Schema::hasColumn('commerce_orders', 'shipping_rule_id')) {
            Schema::table('commerce_orders', function (Blueprint $table): void {
                $table->foreignUuid('shipping_rule_id')
                    ->nullable()
                    ->after('payment_method_id')
                    ->constrained('commerce_shipping_rules')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('commerce_orders', function (Blueprint $table): void {
            $table->dropForeign(['shipping_rule_id']);
            $table->dropColumn('shipping_rule_id');
        });

        Schema::dropIfExists('commerce_shipping_rules');
    }
};
