<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_settlement_allocations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('payment_settlement_id')->constrained('payment_settlements')->cascadeOnDelete();
            $table->string('payment_type', 40);
            $table->string('payment_id', 64);
            $table->decimal('amount', 15, 2);
            $table->timestamps();
            $table->unique(['payment_settlement_id', 'payment_type', 'payment_id'], 'psa_settlement_payment_unique');
            $table->unique(['business_id', 'payment_type', 'payment_id'], 'psa_payment_once_unique');
            $table->index(['business_id', 'payment_settlement_id'], 'psa_biz_settlement_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_settlement_allocations');
    }
};
