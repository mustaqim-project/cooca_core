<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Seo\SitemapService;
use Illuminate\Console\Command;

final class GenerateSitemapCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate {--path= : Path file output alternatif}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sitemap.xml statis ke folder public untuk performa SEO maksimal';

    /**
     * Execute the console command.
     */
    public function handle(SitemapService $sitemapService): int
    {
        $this->info('Memulai pembuatan sitemap XML untuk COOCA...');

        // 1. Main sitemap.xml (seluruh halaman publik)
        $customPath = $this->option('path');
        $targetFile = is_string($customPath) && $customPath !== ''
            ? $customPath
            : public_path('sitemap.xml');

        $savedMain = $sitemapService->saveToPublic($targetFile);
        $totalUrls = count($sitemapService->getPublicUrls());

        // 2. Sitemap khusus blog: sitemap_blog.xml (hub + semua artikel terbit)
        $savedBlog = $sitemapService->saveBlogToPublic();
        $totalBlogUrls = count($sitemapService->getBlogUrls());

        $this->info('✓ Berhasil membuat sitemap XML!');
        $this->table(
            ['Parameter', 'Nilai'],
            [
                ['Target File (Umum)', $savedMain],
                ['Total URL Terindeks (Umum)', (string) $totalUrls],
                ['Target File (Blog)', $savedBlog],
                ['Total URL Terindeks (Blog)', (string) $totalBlogUrls],
                ['Skema Indeks', 'INDEX, FOLLOW (Public Pages Only)'],
                ['Timestamp', now()->toDateTimeString()],
            ]
        );

        return self::SUCCESS;
    }
}
