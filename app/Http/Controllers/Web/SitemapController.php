<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Seo\SitemapService;
use Illuminate\Http\Response;
use Illuminate\View\View;

final class SitemapController extends Controller
{
    public function __construct(
        private readonly SitemapService $sitemapService
    ) {}

    /**
     * Render the dynamic XML sitemap for search engine crawlers.
     */
    public function xml(): Response
    {
        $xml = $this->sitemapService->generateXml();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'X-Robots-Tag' => 'noindex, follow',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Render the human-friendly HTML sitemap page.
     */
    public function html(): View
    {
        $groupedUrls = $this->sitemapService->getGroupedUrls();
        $totalUrls = count($this->sitemapService->getPublicUrls());

        return view('public.sitemap', [
            'groupedUrls' => $groupedUrls,
            'totalUrls' => $totalUrls,
        ]);
    }
}
