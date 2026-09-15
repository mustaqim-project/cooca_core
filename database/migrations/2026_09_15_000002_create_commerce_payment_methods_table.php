<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commerce_payment_methods', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('type', 32)->default('bank_transfer'); // bank_transfer, qris
            $table->string('bank_name', 100);
            $table->string('account_number', 64)->nullable();
            $table->string('account_holder', 150)->nullable();
            $table->string('qris_image_path', 500)->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['business_id', 'is_active', 'sort_order'], 'cpm_biz_active_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_payment_methods');
    }
};
