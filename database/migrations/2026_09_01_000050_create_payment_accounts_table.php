<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for platform payment and bank accounts (CMS Rekening).
     */
    public function up(): void
    {
        Schema::create('payment_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('bank_code', 32)->unique(); // bca, mandiri, bri, bni, bsi, qris, etc.
            $table->string('bank_name', 128); // e.g. Bank Central Asia (BCA)
            $table->string('account_name', 128); // e.g. PT Cooca Teknologi Indonesia
            $table->string('account_number', 64); // e.g. 8735-0812-999
            $table->string('type', 32)->default('bank_transfer'); // bank_transfer, qris, e_wallet
            $table->text('instructions')->nullable();
            $table->string('qr_image_path')->nullable();
            $table->string('icon', 64)->default('credit-card');
            $table->string('color', 32)->default('indigo');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_accounts');
    }
};
