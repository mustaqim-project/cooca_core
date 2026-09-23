<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostCluster;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ImportUmkmArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'blog:import-articles {--force : Force overwrite existing posts}';

    /**
     * The console command description.
     */
    protected $description = 'Import 100 UMKM educational articles from database/data/articles into the posts table';

    /**
     * Curated category visual metadata and images.
     */
    private const CATEGORY_CONFIG = [
        'Legalitas' => [
            'color' => '#007AFF',
            'icon' => 'shield-check',
            'description' => 'Panduan izin usaha, NIB, OSS, PIRT, sertifikasi Halal, dan kepatuhan hukum UMKM',
            'images' => [
                'https://images.unsplash.com/photo-1450133064473-71024230f91b?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=1200&q=80',
            ],
        ],
        'Modal' => [
            'color' => '#34C759',
            'icon' => 'coins',
            'description' => 'Akses permodalan, KUR perbankan, bantuan pemerintah, crowdfunding, dan strategi budgeting',
            'images' => [
                'https://images.unsplash.com/photo-1579621970563-ebec7560ff3e?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1559526324-4b87b5e36e44?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1565514020179-026b92b84bb6?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1526304640581-d334cdbbf45e?auto=format&fit=crop&w=1200&q=80',
            ],
        ],
        'Operasional' => [
            'color' => '#FF9500',
            'icon' => 'settings',
            'description' => 'SOP harian warung, shift karyawan, manajemen stok gudang, supplier, dan efisiensi kasir',
            'images' => [
                'https://images.unsplash.com/photo-1556740738-b6a63e27c4df?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1578916171728-46686eac8d58?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=1200&q=80',
            ],
        ],
        'Marketing' => [
            'color' => '#AF52DE',
            'icon' => 'trending-up',
            'description' => 'Strategi video TikTok, reels Instagram, optimasi Google Maps, closing WhatsApp, dan ulasan pelanggan',
            'images' => [
                'https://images.unsplash.com/photo-1611162617474-5b21e879e113?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1533750349088-cd871a92f312?auto=format&fit=crop&w=1200&q=80',
            ],
        ],
        'Keuangan' => [
            'color' => '#00C4D8',
            'icon' => 'calculator',
            'description' => 'Kalkulasi HPP, Food Cost kuliner, BEP impas, pembukuan kas, PPh Final 0.5%, dan pemisahan rekening',
            'images' => [
                'https://images.unsplash.com/photo-1554224154-26032ffc0d07?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1556742049-0a67c5574f73?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1544377193-33dcf4d68fb5?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1200&q=80',
            ],
        ],
        'Scale-up' => [
            'color' => '#5856D6',
            'icon' => 'rocket',
            'description' => 'Strategi membuka cabang baru, delegasi wewenang owner, sistem multi-outlet, dan utilisasi AI',
            'images' => [
                'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1200&q=80',
            ],
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dir = database_path('data/articles');
        if (!is_dir($dir)) {
            $dir = resource_path('views/public/blog/articles');
        }
        if (!is_dir($dir)) {
            $this->error("Directory not found in database/data/articles or resources/views/public/blog/articles");
            return 1;
        }

        $files = glob($dir . '/*.md');
        if (empty($files) && is_dir(resource_path('views/public/blog/articles'))) {
            $files = glob(resource_path('views/public/blog/articles') . '/*.md');
        }
        sort($files);
        $totalFiles = count($files);

        if ($totalFiles === 0) {
            $this->warn("No markdown files found in {$dir}");
            return 0;
        }

        $this->info("Found {$totalFiles} article files in {$dir}. Starting import...");

        // 1. Ensure Standard Clusters Exist
        $clusterTutorial = PostCluster::firstOrCreate(
            ['code' => 'tutorial'],
            [
                'name' => 'Cluster K - Tutorial Cara',
                'slug' => 'tutorial',
                'description' => 'Panduan teknis langkah demi langkah operasional bisnis, POS kasir, dan resep usaha',
                'icon' => 'wrench',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $clusterEdukasi = PostCluster::firstOrCreate(
            ['code' => 'edukasi'],
            [
                'name' => 'Cluster O - Edukasi Topikal',
                'slug' => 'edukasi',
                'description' => 'Wawasan bisnis, manajemen keuangan, permodalan, legalitas, dan scale-up UMKM',
                'icon' => 'graduation-cap',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        // 2. Ensure Categories Exist with High-Quality Metadata
        $categoryMap = [];
        $order = 1;
        foreach (self::CATEGORY_CONFIG as $catName => $meta) {
            $cat = PostCategory::firstOrCreate(
                ['slug' => Str::slug($catName)],
                [
                    'name' => $catName,
                    'description' => $meta['description'],
                    'color' => $meta['color'],
                    'icon' => $meta['icon'],
                    'is_active' => true,
                    'sort_order' => $order++,
                ]
            );
            $categoryMap[$catName] = $cat;
        }

        // 3. Process Each File
        $importedCount = 0;
        $now = now();

        $this->output->progressStart($totalFiles);

        foreach ($files as $idx => $filePath) {
            $content = file_get_contents($filePath);
            $parsed = $this->parseArticleContent($content, $filePath);

            if (empty($parsed['title']) || empty($parsed['slug'])) {
                $this->warn("Skipping invalid file: " . basename($filePath));
                $this->output->progressAdvance();
                continue;
            }

            // Determine cluster
            $isTutorial = str_contains($parsed['cluster_raw'] ?? '', 'Cluster K') || str_contains($parsed['cluster_raw'] ?? '', 'Tutorial');
            $clusterCode = $isTutorial ? 'tutorial' : 'edukasi';
            $clusterId = $isTutorial ? $clusterTutorial->id : $clusterEdukasi->id;

            // Determine category
            $catName = $parsed['category'] ?? 'Umum';
            $categoryObj = $categoryMap[$catName] ?? null;
            if (!$categoryObj) {
                $categoryObj = PostCategory::firstOrCreate(
                    ['slug' => Str::slug($catName)],
                    [
                        'name' => $catName,
                        'description' => 'Artikel edukasi seputar ' . $catName,
                        'color' => '#007AFF',
                        'icon' => 'folder',
                        'is_active' => true,
                        'sort_order' => 99,
                    ]
                );
                $categoryMap[$catName] = $categoryObj;
            }

            // Pick cover image (if not specified in markdown, pick curated category image)
            $coverImage = $parsed['cover_image'] ?? null;
            if (empty($coverImage)) {
                $imgPool = self::CATEGORY_CONFIG[$catName]['images'] ?? [
                    'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1200&q=80'
                ];
                $coverImage = $imgPool[$idx % count($imgPool)];
            }

            // Publication date spread over the past 100 days
            $publishedAt = (clone $now)->subDays($totalFiles - $idx)->setHour(8 + ($idx % 12))->setMinute(($idx * 7) % 60);

            // Starting organic views
            $viewsCount = 180 + (($idx * 17) % 650);

            Post::updateOrCreate(
                ['slug' => $parsed['slug']],
                [
                    'title' => $parsed['title'],
                    'slug' => $parsed['slug'],
                    'cluster' => $clusterCode,
                    'cluster_id' => $clusterId,
                    'category' => $catName,
                    'category_id' => $categoryObj->id,
                    'excerpt' => $parsed['excerpt'] ?? '',
                    'content' => $parsed['content'] ?? '',
                    'cover_image' => $coverImage,
                    'meta_title' => $parsed['meta_title'] ?? $parsed['title'],
                    'meta_description' => $parsed['meta_description'] ?? $parsed['excerpt'] ?? '',
                    'author_name' => $parsed['author'] ?? 'Tim Edukasi COOCA',
                    'views_count' => $viewsCount,
                    'is_published' => true,
                    'published_at' => $publishedAt,
                ]
            );

            $importedCount++;
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->info("Successfully imported {$importedCount} / {$totalFiles} articles into the database.");

        return 0;
    }

    /**
     * Parse structured markdown article.
     */
    private function parseArticleContent(string $content, string $filename): array
    {
        $data = [];

        // Title
        if (preg_match('/\*\*Judul Artikel.*?\*\*\s*\r?\n+([^\r\n]+)/u', $content, $m)) {
            $data['title'] = trim($m[1]);
        }

        // Cluster
        if (preg_match('/\*\*Cluster Konten.*?\*\*\s*\r?\n+([^\r\n]+)/u', $content, $m)) {
            $data['cluster_raw'] = trim($m[1]);
        }

        // Category
        if (preg_match('/\*\*Kategori.*?\*\*\s*\r?\n+([^\r\n]+)/u', $content, $m)) {
            $data['category'] = trim($m[1]);
        }

        // Author
        if (preg_match('/\*\*Nama Penulis.*?\*\*\s*\r?\n+([^\r\n]+)/u', $content, $m)) {
            $data['author'] = trim($m[1]);
        }

        // Cover Image
        if (preg_match('/\*\*URL Cover Image.*?\*\*\s*\r?\n+([^\r\n]+)/u', $content, $m)) {
            $val = trim($m[1]);
            if (!str_starts_with($val, '##') && !str_starts_with($val, '**') && !empty($val)) {
                $data['cover_image'] = $val;
            }
        }

        // Excerpt
        if (preg_match('/## Ringkasan \/ Excerpt\s*\r?\n+(.*?)(?=\r?\n## Konten Lengkap)/us', $content, $m)) {
            $data['excerpt'] = trim($m[1]);
        }

        // Content
        if (preg_match('/## Konten Lengkap.*?\r?\n+(.*?)(?=\r?\n## Pengaturan SEO|$)/us', $content, $m)) {
            $data['content'] = trim($m[1]);
        }

        // Meta Title
        if (preg_match('/\*\*Meta Title.*?\*\*\s*\r?\n+([^\r\n]+)/u', $content, $m)) {
            $data['meta_title'] = trim($m[1]);
        }

        // Meta Description
        if (preg_match('/\*\*Meta Description.*?\*\*\s*\r?\n+([^\r\n]+)/u', $content, $m)) {
            $data['meta_description'] = trim($m[1]);
        }

        // Meta Keyword
        if (preg_match('/\*\*Meta Keyword.*?\*\*\s*\r?\n+([^\r\n]+)/u', $content, $m)) {
            $data['meta_keyword'] = trim($m[1]);
        }

        // Slug derived from filename (remove 001- prefix and .md suffix)
        $base = basename($filename, '.md');
        $data['slug'] = preg_replace('/^\d+-/', '', $base);

        return $data;
    }
}
