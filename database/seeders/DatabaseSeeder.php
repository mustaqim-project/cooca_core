<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with core defaults, templates, admin, and demo users.
     */
    public function run(): void
    {
        $this->call([
            DefaultUnitSeeder::class,
            DefaultCostCategorySeeder::class,
            RbacSeeder::class,
            BusinessTemplateSeeder::class,
            AdminSeeder::class,
            UserSeeder::class,
            CommerceSeeder::class,
        ]);
    }
}
