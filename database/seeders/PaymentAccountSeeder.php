<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PaymentAccount;
use Illuminate\Database\Seeder;

class PaymentAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (PaymentAccount::getDefaultAccounts() as $account) {
            PaymentAccount::firstOrCreate(
                ['bank_code' => $account['bank_code']],
                $account
            );
        }
    }
}
