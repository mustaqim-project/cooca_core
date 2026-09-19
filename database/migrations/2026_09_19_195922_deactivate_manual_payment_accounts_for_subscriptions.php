<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Deactivate manual bank transfer accounts (bca, mandiri, bri) to enforce TriPay-only checkout
        \Illuminate\Support\Facades\DB::table('payment_accounts')
            ->whereIn('bank_code', ['bca', 'mandiri', 'bri'])
            ->update(['is_active' => false]);

        // Ensure default TriPay accounts exist and are active
        foreach (\App\Models\PaymentAccount::getDefaultAccounts() as $account) {
            \App\Models\PaymentAccount::updateOrCreate(
                ['bank_code' => $account['bank_code']],
                $account
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('payment_accounts')
            ->whereIn('bank_code', ['bca', 'mandiri', 'bri'])
            ->update(['is_active' => true]);
    }
};
