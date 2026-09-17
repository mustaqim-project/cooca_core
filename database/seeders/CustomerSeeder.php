<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\GlobalCustomer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'email' => 'mandiri@cooca.id',
                'phone' => '081234567890',
                'name' => 'Ahmad Pratama (Mandiri)',
                'shipping_address' => 'Plaza Mandiri Lantai 8, Jl. Jend. Gatot Subroto Kav. 36-38, Jakarta Selatan',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ],
            [
                'email' => 'bca@cooca.id',
                'phone' => '081987654321',
                'name' => 'Budi Wicaksono (BCA)',
                'shipping_address' => 'Menara BCA Lantai 12, Grand Indonesia, Jl. M.H. Thamrin No. 1, Jakarta Pusat',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ],
            [
                'email' => 'customer@cooca.id',
                'phone' => '081122334455',
                'name' => 'Pelanggan Setia Cooca',
                'shipping_address' => 'Jl. Boulevard Raya Blok QA No. 5, Kelapa Gading, Jakarta Utara',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ],
        ];

        foreach ($customers as $data) {
            GlobalCustomer::updateOrCreate(
                ['email' => $data['email']],
                $data
            );
        }
    }
}
