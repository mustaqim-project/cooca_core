<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BusinessLandingPage;
use App\Models\Post;
use Illuminate\Support\Carbon;

final class SitemapService
{
    /**
     * Vertical solution definitions (synchronized with PublicSolutionController).
     */
    private const SOLUTIONS = [
        'kasir-warung' => 'Solusi Kasir Toko Kelontong & Warung Sembako',
        'kasir-cafe-kecil' => 'Solusi Kasir Kedai Kopi & Kafe Modern',
        'kasir-kios' => 'Solusi Kasir Konter HP & Kios Aksesoris',
        'kasir-laundry' => 'Solusi Kasir Laundry Kiloan & Satuan',
        'kasir-salon' => 'Solusi Kasir Salon Kecantikan & Spa',
        'kasir-barbershop' => 'Solusi Kasir Barbershop & Pangkas Rambut',
        'kasir-bengkel-kecil' => 'Solusi Kasir Bengkel Motor & Sparepart',
        'kasir-irt' => 'Solusi Industri Rumah Tangga & Produsen Snack',
    ];

    /**
     * Free lead capture template definitions (synchronized with PublicTemplateController).
     */
    private const TEMPLATES = [
        'pembukuan-warung-excel' => 'Template Pembukuan Warung & Toko Kelontong (Excel)',
        'laporan-keuangan-sederhana' => 'Template Laporan Keuangan Sederhana UMKM',
        'stok-opname-excel' => 'Template Stok Opname & Kartu Persediaan Barang',
        'invoice-sederhana' => 'Template Invoice & Nota Pembayaran Sederhana',
    ];

    /**
     * Business calculators.
     */
    private const CALCULATORS = [
        'hpp' => ['name' => 'Kalkulator HPP & Food Costing', 'path' => '/kalkulator/hpp', 'priority' => '0.9'],
        'bep' => ['name' => 'Kalkulator Break Even Point (BEP)', 'path' => '/kalkulator/bep', 'priority' => '0.9'],
        'harga-jual' => ['name' => 'Kalkulator Harga Jual & Margin Laba', 'path' => '/kalkulator/harga-jual', 'priority' => '0.9'],
        'laba-bersih' => ['name' => 'Kalkulator Laba Bersih & Arus Kas', 'path' => '/kalkulator/laba-bersih', 'priority' => '0.8'],
        'gaji-karyawan' => ['name' => 'Kalkulator Gaji & Upah Lembur Karyawan', 'path' => '/kalkulator/gaji-karyawan', 'priority' => '0.8'],
        'pph-final' => ['name' => 'Kalkulator Pajak PPh Final UMKM 0.5%', 'path' => '/kalkulator/pph-final', 'priority' => '0.8'],
        'omzet-harian' => ['name' => 'Kalkulator Target Omzet Harian', 'path' => '/kalkulator/omzet-harian', 'priority' => '0.8'],
        'simulasi-what-if' => ['name' => 'Simulator Skenario Bisnis What-If', 'path' => '/kalkulator/simulasi-what-if', 'priority' => '0.8'],
    ];

    /**
     * Compile all public indexable URLs with metadata.
     *
     * @return array<int, array{
     *     loc: string,
     *     lastmod: string,
     *     changefreq: string,
     *     priority: string,
     *     category: string,
     *     title: string
     * }>
     */
    public function getPublicUrls(): array
    {
        $baseUrl = rtrim((string) config('app.url', 'https://cooca.id'), '/');
        $nowDate = Carbon::now()->toIso8601String();
        $urls = [];

        // 1. Homepage & Core Hubs
        $urls[] = [
            'loc' => $baseUrl . '/',
            'lastmod' => $nowDate,
            'changefreq' => 'daily',
            'priority' => '1.0',
            'category' => 'Halaman Utama',
            'title' => 'Beranda Cooca UMKM',
        ];

        $urls[] = [
            'loc' => $baseUrl . '/kalkulator',
            'lastmod' => $nowDate,
            'changefreq' => 'weekly',
            'priority' => '0.9',
            'category' => 'Kalkulator Bisnis',
            'title' => 'Pusat Kalkulator & Simulasi Keuangan UMKM',
        ];

        $urls[] = [
            'loc' => $baseUrl . '/template-pembukuan-gratis',
            'lastmod' => $nowDate,
            'changefreq' => 'weekly',
            'priority' => '0.9',
            'category' => 'Template Gratis',
            'title' => 'Download Template Pembukuan & Excel Gratis',
        ];

        $urls[] = [
            'loc' => $baseUrl . '/blog',
            'lastmod' => $nowDate,
            'changefreq' => 'daily',
            'priority' => '0.9',
            'category' => 'Edukasi & Blog',
            'title' => 'Pusat Panduan & Edukasi Bisnis UMKM',
        ];

        $urls[] = [
            'loc' => $baseUrl . '/kontak',
            'lastmod' => $nowDate,
            'changefreq' => 'monthly',
            'priority' => '0.7',
            'category' => 'Informasi',
            'title' => 'Hubungi Tim Cooca UMKM',
        ];

        $urls[] = [
            'loc' => $baseUrl . '/sitemap',
            'lastmod' => $nowDate,
            'changefreq' => 'weekly',
            'priority' => '0.6',
            'category' => 'Informasi',
            'title' => 'Peta Situs (HTML Sitemap)',
        ];

        // 2. Kalkulator Spesifik
        foreach (self::CALCULATORS as $calc) {
            $urls[] = [
                'loc' => $baseUrl . $calc['path'],
                'lastmod' => $nowDate,
                'changefreq' => 'weekly',
                'priority' => $calc['priority'],
                'category' => 'Kalkulator Bisnis',
                'title' => $calc['name'],
            ];
        }

        // 3. Solusi Industri Vertikal
        foreach (self::SOLUTIONS as $slug => $title) {
            $urls[] = [
                'loc' => $baseUrl . '/solusi/' . $slug,
                'lastmod' => $nowDate,
                'changefreq' => 'weekly',
                'priority' => '0.9',
                'category' => 'Solusi Industri',
                'title' => $title,
            ];
        }

        // 4. Template Excel & Resource Downloads
        foreach (self::TEMPLATES as $slug => $title) {
            $urls[] = [
                'loc' => $baseUrl . '/template/' . $slug,
                'lastmod' => $nowDate,
                'changefreq' => 'monthly',
                'priority' => '0.8',
                'category' => 'Template Gratis',
                'title' => $title,
            ];
        }

        // 5. Dynamic Blog Articles (Post Model)
        try {
            $posts = Post::published()->orderByDesc('published_at')->get();
            foreach ($posts as $post) {
                $postDate = ($post->updated_at ?? $post->published_at ?? Carbon::now())->toIso8601String();
                $urls[] = [
                    'loc' => $baseUrl . '/blog/' . $post->slug,
                    'lastmod' => $postDate,
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                    'category' => 'Artikel & Tutorial',
                    'title' => (string) $post->title,
                ];
            }
        } catch (\Throwable) {
            // Graceful fallback if database unavailable during static boot
        }

        // 6. Public Business Single-Page Landings (/b/{slug})
        try {
            $businessLandings = BusinessLandingPage::where('is_published', true)
                ->whereHas('business', fn ($q) => $q->where('is_active', true))
                ->with('business')
                ->get();

            foreach ($businessLandings as $landing) {
                if ($landing->business && $landing->business->slug) {
                    $landingDate = ($landing->updated_at ?? Carbon::now())->toIso8601String();
                    $urls[] = [
                        'loc' => $baseUrl . '/b/' . $landing->business->slug,
                        'lastmod' => $landingDate,
                        'changefreq' => 'weekly',
                        'priority' => '0.7',
                        'category' => 'Profil Bisnis UMKM',
                        'title' => (string) ($landing->headline ?: $landing->business->name),
                    ];
                }
            }
        } catch (\Throwable) {
            // Graceful fallback
        }

        return $urls;
    }

    /**
     * Generate standard XML sitemap content.
     */
    public function generateXml(): string
    {
        $urls = $this->getPublicUrls();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $xml .= '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
        $xml .= '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";

        foreach ($urls as $item) {
            $loc = htmlspecialchars($item['loc'], ENT_XML1, 'UTF-8');
            $lastmod = htmlspecialchars($item['lastmod'], ENT_XML1, 'UTF-8');
            $changefreq = htmlspecialchars($item['changefreq'], ENT_XML1, 'UTF-8');
            $priority = htmlspecialchars($item['priority'], ENT_XML1, 'UTF-8');

            $xml .= "    <url>\n";
            $xml .= "        <loc>{$loc}</loc>\n";
            $xml .= "        <lastmod>{$lastmod}</lastmod>\n";
            $xml .= "        <changefreq>{$changefreq}</changefreq>\n";
            $xml .= "        <priority>{$priority}</priority>\n";
            $xml .= "    </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Save generated XML to public folder.
     */
    public function saveToPublic(?string $path = null): string
    {
        $targetPath = $path ?? public_path('sitemap.xml');
        $xml = $this->generateXml();
        file_put_contents($targetPath, $xml);

        return $targetPath;
    }

    /**
     * Group URLs by category for rendering in an HTML Sitemap page.
     *
     * @return array<string, array<int, array{loc: string, title: string, priority: string}>>
     */
    public function getGroupedUrls(): array
    {
        $all = $this->getPublicUrls();
        $grouped = [];

        foreach ($all as $item) {
            $cat = $item['category'];
            $grouped[$cat][] = [
                'loc' => $item['loc'],
                'title' => $item['title'],
                'priority' => $item['priority'],
            ];
        }

        return $grouped;
    }
}
