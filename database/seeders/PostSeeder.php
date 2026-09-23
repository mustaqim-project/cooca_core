<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostCluster;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Call the dedicated artisan import command to load all 100 articles from database/data/articles
        Artisan::call('blog:import-articles');
    }
}
