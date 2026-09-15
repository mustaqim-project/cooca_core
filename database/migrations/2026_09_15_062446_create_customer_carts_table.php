<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_carts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('global_customer_id')->constrained('global_customers')->cascadeOnDelete();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            // One cart per customer per store
            $table->unique(['global_customer_id', 'business_id']);
            $table->index(['global_customer_id', 'business_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_carts');
    }
};
