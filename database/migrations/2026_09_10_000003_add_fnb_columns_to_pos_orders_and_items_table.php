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
        Schema::table('pos_orders', function (Blueprint $table): void {
            $table->string('order_source', 30)->default('pos')->after('order_type');
            $table->foreignUuid('pos_table_id')->nullable()->after('order_source')->constrained('pos_tables')->nullOnDelete();
            $table->foreignUuid('pos_table_session_id')->nullable()->after('pos_table_id')->constrained('pos_table_sessions')->nullOnDelete();
            $table->string('customer_phone_guest', 50)->nullable()->after('customer_name_guest');
            $table->string('rejection_reason', 255)->nullable()->after('void_reason');
            $table->foreignUuid('rejected_by')->nullable()->after('rejection_reason')->constrained('users')->nullOnDelete();
            $table->dateTime('rejected_at')->nullable()->after('rejected_by');

            $table->index(['business_id', 'order_source', 'status']);
            $table->index(['pos_table_session_id', 'status']);
        });

        Schema::create('pos_order_item_modifiers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('pos_order_item_id')->constrained('pos_order_items')->cascadeOnDelete();
            $table->foreignUuid('modifier_group_id')->nullable()->constrained('modifier_groups')->nullOnDelete();
            $table->foreignUuid('modifier_option_id')->nullable()->constrained('modifier_options')->nullOnDelete();
            $table->string('modifier_group_name', 150);
            $table->string('modifier_option_name', 150);
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('quantity', 15, 4)->default(1.0000);
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->json('material_snapshot')->nullable();
            $table->timestamps();

            $table->index('pos_order_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_order_item_modifiers');

        Schema::table('pos_orders', function (Blueprint $table): void {
            $table->dropForeign(['pos_table_id']);
            $table->dropForeign(['pos_table_session_id']);
            $table->dropForeign(['rejected_by']);

            $table->dropIndex(['business_id', 'order_source', 'status']);
            $table->dropIndex(['pos_table_session_id', 'status']);

            $table->dropColumn([
                'order_source',
                'pos_table_id',
                'pos_table_session_id',
                'customer_phone_guest',
                'rejection_reason',
                'rejected_by',
                'rejected_at',
            ]);
        });
    }
};
