<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_edc_terminals', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('bank_name', 50);            // BCA, Mandiri, BRI, BNI, CIMB, Permata, dll.
            $table->string('terminal_name', 100);       // Contoh: EDC BCA Kasir 1 Jakarta
            $table->string('terminal_id_tid', 50);      // Nomor Fisik TID Mesin EDC
            $table->string('merchant_id_mid', 50)->nullable(); // Nomor MID Merchant
            $table->decimal('mdr_debit_percent', 5, 2)->default(0.15);  // Biaya MDR Kartu Debit (%)
            $table->decimal('mdr_credit_percent', 5, 2)->default(1.50); // Biaya MDR Kartu Kredit (%)
            $table->string('settlement_account_info', 150)->nullable(); // Info Rekening Penerima Settlement Bank
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['business_id', 'terminal_id_tid'], 'set_biz_tid_unique');
            $table->index(['business_id', 'location_id', 'is_active'], 'set_biz_loc_act_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_edc_terminals');
    }
};
