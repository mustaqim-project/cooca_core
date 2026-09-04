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
            $table->dropForeign(['customer_id']);
            $table->foreignUuid('customer_id')->nullable()->change();
            $table->foreign('customer_id')->references('id')->on('customers')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales_returns', function (Blueprint $table): void {
            $table->dropForeign(['customer_id']);
            $table->foreignUuid('customer_id')->nullable(false)->change();
            $table->foreign('customer_id')->references('id')->on('customers')->restrictOnDelete();
        });
    }
};
