<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_order_payments', function (Blueprint $table): void {
            $table->foreignUuid('store_edc_terminal_id')->nullable()->after('payment_method')->constrained('store_edc_terminals')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pos_order_payments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('store_edc_terminal_id');
        });
    }
};
