<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_account_reconciliations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('location_id')->nullable()->constrained('locations')->nullOnDelete();

            $table->string('channel_type', 30)->comment('edc_bca, edc_bri, gopay, ovo, dana, shopee, tokopedia, grab, etc.');
            $table->string('channel_label', 100)->comment('Display label, e.g. "EDC BCA - TID 12345"');

            $table->string('period', 7)->comment('YYYY-MM format, e.g. 2026-09');
            $table->float('opening_balance')->default(0)->comment('Saldo awal di awal periode (diisi manual oleh merchant)');
            $table->float('total_inflow')->default(0)->comment('Total penerimaan dari channel selama periode');
            $table->float('total_disbursement')->default(0)->comment('Total pencairan/settlement dari provider ke bank merchant');
            $table->float('closing_balance')->default(0)->comment('Saldo akhir di akhir periode (diisi manual oleh merchant)');

            $table->float('expected_closing')->default(0)->comment('System-calculated: opening + inflow - disbursement');
            $table->float('variance')->default(0)->comment('System-calculated: closing - expected_closing');

            $table->string('status', 20)->default('draft')->comment('draft | submitted | reviewed | discrepancy');
            $table->text('notes')->nullable();

            $table->foreignUuid('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignUuid('reviewed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->unique(['business_id', 'location_id', 'channel_type', 'period'], 'uniq_ext_recon_period');
            $table->index(['business_id', 'period', 'status'], 'idx_ext_recon_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_account_reconciliations');
    }
};
