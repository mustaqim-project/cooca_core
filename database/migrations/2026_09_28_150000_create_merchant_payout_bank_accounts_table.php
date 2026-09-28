<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchant_payout_bank_accounts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('bank_code', 30);            // BCA, MANDIRI, BRI, BNI, JAGO, SEABANK, PERMATA, etc.
            $table->string('bank_name', 100);           // PT Bank Central Asia Tbk
            $table->string('account_number', 50);       // Nomor Rekening
            $table->string('account_holder_name', 150); // WAJIB IDENTIK DENGAN NAMA PEMILIK USAHA (OWNER)
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['business_id', 'bank_code', 'account_number'], 'mpba_biz_bank_acc_unique');
            $table->index(['business_id', 'is_primary', 'is_verified'], 'mpba_biz_prim_ver_idx');
            $table->index(['business_id', 'location_id'], 'mpba_biz_loc_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchant_payout_bank_accounts');
    }
};
