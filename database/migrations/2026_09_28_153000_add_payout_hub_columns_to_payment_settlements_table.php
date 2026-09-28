<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_settlements', function (Blueprint $table) {
            $table->foreignUuid('payout_bank_account_id')
                ->nullable()
                ->after('destination_bank')
                ->constrained('merchant_payout_bank_accounts')
                ->nullOnDelete();

            $table->string('payout_mode', 20)
                ->default('manual')
                ->after('payout_bank_account_id')
                ->comment('manual | auto_h1');

            $table->timestamp('scheduled_payout_at')
                ->nullable()
                ->after('payout_mode')
                ->comment('Waktu terjadwal untuk auto-payout H+1 (09:00 WIB)');

            $table->index(['business_id', 'payout_mode', 'status'], 'idx_payout_hub_queue');
        });
    }

    public function down(): void
    {
        Schema::table('payment_settlements', function (Blueprint $table) {
            $table->dropIndex('idx_payout_hub_queue');
            $table->dropConstrainedForeignId('payout_bank_account_id');
            $table->dropColumn(['payout_mode', 'scheduled_payout_at']);
        });
    }
};
