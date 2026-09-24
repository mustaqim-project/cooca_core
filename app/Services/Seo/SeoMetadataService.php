<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Domain\Storage\AdminStorage;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

final class SeoMetadataService
{
    /**
     * Default global fallback Open Graph image (1200x630, absolute HTTPS).
     */
    public const DEFAULT_OG_IMAGE = 'assets/seo/cooca-og-default.jpg';
    public const DEFAULT_OG_WIDTH = 1200;
    public const DEFAULT_OG_HEIGHT = 630;

    /**
     * Resolve the canonical, absolute HTTPS URL for the current request or specified path.
     */
    public static function resolveCanonicalUrl(?string $url = null): string
    {
        // 1. If explicit URL provided or set in view section
        if ($url === null && View::hasSection('canonical')) {
            $url = trim((string) View::yieldContent('canonical'));
        }

        // 2. If still empty, use current request URL without query parameters
        if (empty($url)) {
            $url = Request::url();
        }

        // 3. Clean Markdown formatting if accidentally passed like [https://...](https://...)
        $url = self::stripMarkdownLinks($url);

        // 4. Clean HTML entities
        $url = html_entity_decode($url, ENT_QUOTES, 'UTF-8');

        // 5. Ensure absolute URL
        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            $url = url($url);
        }

        // 6. Force HTTPS in production or if app.url is HTTPS
        $appUrl = (string) config('app.url');
        if (str_starts_with($appUrl, 'https://') && str_starts_with($url, 'http://')) {
            $url = 'https://' . substr($url, 7);
        }

        // 7. Strip trailing slash unless it's just the root domain
        $parts = parse_url($url);
        $path = $parts['path'] ?? '/';
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
            $scheme = $parts['scheme'] ?? 'https';
            $host = $parts['host'] ?? 'cooca.id';
            $port = isset($parts['port']) ? ':' . $parts['port'] : '';
            $url = "{$scheme}://{$host}{$port}{$path}";
        }

        return $url;
    }

    /**
     * Resolve comprehensive Open Graph image metadata.
     *
     * @return array{
     *     url: string,
     *     secure_url: string,
     *     type: string,
     *     width: int,
     *     height: int,
     *     alt: string
     * }
     */
    public static function resolveOgImage(
        ?string $image = null,
        ?string $alt = null,
        ?string $fallback = null
    ): array {
        // Priority 1: Passed parameter
        $raw = $image;

        // Priority 2: Blade view section 'og_image'
        if (empty($raw) && View::hasSection('og_image')) {
            $raw = trim((string) View::yieldContent('og_image'));
        }

        // Priority 3: Explicit fallback passed (e.g. from featured image or product image)
        if (empty($raw) && !empty($fallback)) {
            $raw = $fallback;
        }

        // Priority 4: System Setting for SEO OG Image
        if (empty($raw)) {
            $settingImg = SystemSetting::get('seo_og_image');
            if (!empty($settingImg)) {
                $raw = AdminStorage::publicUrl($settingImg);
            }
        }

        // Priority 5: Global default COOCA OG image (1200x630)
        if (empty($raw)) {
            $raw = asset(self::DEFAULT_OG_IMAGE);
        }

        // Clean Markdown if any
        $raw = self::stripMarkdownLinks($raw);
        $raw = html_entity_decode($raw, ENT_QUOTES, 'UTF-8');

        // Convert relative URL to absolute URL
        if (!str_starts_with($raw, 'http://') && !str_starts_with($raw, 'https://')) {
            $raw = url($raw);
        }

        // Force HTTPS if app.url is HTTPS
        $appUrl = (string) config('app.url');
        if (str_starts_with($appUrl, 'https://') && str_starts_with($raw, 'http://')) {
            $raw = 'https://' . substr($raw, 7);
        }

        // Determine MIME type
        $lower = strtolower($raw);
        $mime = 'image/jpeg';
        if (str_ends_with($lower, '.png')) {
            $mime = 'image/png';
        } elseif (str_ends_with($lower, '.webp')) {
            $mime = 'image/webp';
        } elseif (str_ends_with($lower, '.gif')) {
            $mime = 'image/gif';
        }

        $cleanAlt = self::cleanText($alt ?: (View::hasSection('title') ? (string) View::yieldContent('title') : 'COOCA Business Operating System & Omnichannel ERP'), 100);

        return [
            'url'        => $raw,
            'secure_url' => $raw,
            'type'       => $mime,
            'width'      => self::DEFAULT_OG_WIDTH,
            'height'     => self::DEFAULT_OG_HEIGHT,
            'alt'        => $cleanAlt,
        ];
    }

    /**
     * Clean and safely format plain text for meta description and OG description.
     */
    public static function cleanText(?string $text, int $limit = 160): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        // Strip Markdown links or brackets
        $text = self::stripMarkdownLinks($text);

        // Strip HTML tags
        $text = strip_tags($text);

        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

        // Normalize multiple whitespaces, newlines, and carriage returns to a single space
        $text = (string) preg_replace('/\s+/u', ' ', $text);
        $text = trim($text);

        // Word-boundary aware truncation
        return Str::limit($text, $limit);
    }

    /**
     * Clean title string, avoiding double escaping, nulls, and weird brackets.
     */
    public static function cleanTitle(?string $title): string
    {
        if (empty($title)) {
            $title = SystemSetting::get('seo_meta_title', 'COOCA — Business Operating System & Omnichannel ERP');
        }

        $title = self::stripMarkdownLinks($title);
        $title = strip_tags($title);
        $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
        $title = (string) preg_replace('/\s+/u', ' ', $title);

        return trim($title);
    }

    /**
     * Resolve robots directive (index, follow vs noindex, follow).
     */
    public static function resolveRobots(?string $robots = null, bool $noindex = false): string
    {
        if ($noindex || View::hasSection('noindex')) {
            return 'noindex, follow';
        }

        if ($robots !== null) {
            return $robots;
        }

        if (View::hasSection('robots')) {
            return trim((string) View::yieldContent('robots'));
        }

        // Automatically set noindex on faceted search or filter queries to protect crawl budget
        $req = request();
        if ($req->is('marketplace/cari') && ($req->filled('q') || $req->filled('category') || $req->filled('city') || $req->filled('sort'))) {
            return 'noindex, follow';
        }

        if ($req->is('*/checkout') || $req->is('*/order/*') || $req->is('admin*') || $req->is('dashboard*')) {
            return 'noindex, follow';
        }

        return SystemSetting::get('seo_robots', 'index, follow');
    }

    /**
     * Strip Markdown link syntax [anchor](url) -> url or anchor.
     */
    private static function stripMarkdownLinks(string $value): string
    {
        // Replace [anchor](url) with url if it's an image or link URL
        if (preg_match('/^\[(.*?)\]\((https?:\/\/[^\)]+)\)$/i', trim($value), $matches)) {
            return $matches[2];
        }

        // Replace any inline [text](url) with text
        return (string) preg_replace('/\[(.*?)\]\((https?:\/\/[^\)]+)\)/i', '$1', $value);
    }
}
