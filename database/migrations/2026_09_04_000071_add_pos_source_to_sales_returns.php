<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_returns', function (Blueprint $table): void {
            $table->foreignUuid('pos_order_id')->nullable()->after('invoice_id')->constrained('pos_orders')->restrictOnDelete();
            $table->dropForeign(['invoice_id']);
            $table->foreignUuid('invoice_id')->nullable()->change();
            $table->foreign('invoice_id')->references('id')->on('invoices')->restrictOnDelete();
            $table->index(['business_id', 'pos_order_id', 'status'], 'sr_pos_status_idx');
        });
        Schema::table('sales_return_items', function (Blueprint $table): void {
            $table->dropForeign(['invoice_item_id']);
            $table->foreignUuid('invoice_item_id')->nullable()->change();
            $table->foreign('invoice_item_id')->references('id')->on('invoice_items')->restrictOnDelete();
            $table->foreignUuid('pos_order_item_id')->nullable()->after('invoice_item_id')->constrained('pos_order_items')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales_return_items', function (Blueprint $table): void {
            $table->dropForeign(['pos_order_item_id']);
            $table->dropColumn('pos_order_item_id');
            $table->dropForeign(['invoice_item_id']);
            $table->foreignUuid('invoice_item_id')->nullable(false)->change();
            $table->foreign('invoice_item_id')->references('id')->on('invoice_items')->restrictOnDelete();
        });
        Schema::table('sales_returns', function (Blueprint $table): void {
            $table->dropIndex('sr_pos_status_idx');
            $table->dropForeign(['pos_order_id']);
            $table->dropColumn('pos_order_id');
            $table->dropForeign(['invoice_id']);
            $table->foreignUuid('invoice_id')->nullable(false)->change();
            $table->foreign('invoice_id')->references('id')->on('invoices')->restrictOnDelete();
        });
    }
};
