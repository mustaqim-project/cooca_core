<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\SystemSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => 'admin@cooca.id'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        SystemSetting::set('app_name', 'Cooca UMKM', 'general');
        SystemSetting::set('google_client_id', env('GOOGLE_CLIENT_ID', ''), 'google_api');
        SystemSetting::set('google_client_secret', env('GOOGLE_CLIENT_SECRET', ''), 'google_api', true);
        SystemSetting::set('google_redirect_uri', env('GOOGLE_REDIRECT_URI', rtrim(config('app.url', 'https://umkm.cooca.id'), '/').'/auth/google/callback'), 'google_api');
        SystemSetting::set('allow_google_login', '1', 'google_api');
    }
}
