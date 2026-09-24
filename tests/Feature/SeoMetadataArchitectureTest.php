<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SeoMetadataArchitectureTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Test the default marketing landing page SEO and Social Graph architecture.
     */
    public function test_landing_page_renders_complete_seo_and_social_metadata(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Core Meta Tags
        $response->assertSee('<title>COOCA — Business Operating System &amp; Omnichannel ERP</title>', false);
        $response->assertSee('<meta name="description" content="Platform terintegrasi kasir POS, akuntansi riil, stok resep bahan baku, WhatsApp otomatis, dan toko online untuk UMKM Indonesia.">', false);
        $response->assertSee('<link rel="canonical" href="' . url('/') . '">', false);
        $response->assertSee('<meta name="robots" content="index, follow">', false);

        // Open Graph Metadata
        $response->assertSee('<meta property="og:type" content="website">', false);
        $response->assertSee('<meta property="og:site_name" content="Cooca">', false);
        $response->assertSee('<meta property="og:locale" content="id_ID">', false);
        $response->assertSee('<meta property="og:url" content="' . url('/') . '">', false);
        $response->assertSee('<meta property="og:title" content="COOCA — Business Operating System &amp; Omnichannel ERP">', false);
        $response->assertSee('<meta property="og:image:width" content="1200">', false);
        $response->assertSee('<meta property="og:image:height" content="630">', false);
        $response->assertSee('<meta property="og:image" content="' . asset('assets/seo/cooca-og-default.jpg') . '">', false);

        // Twitter / X Card
        $response->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
        $response->assertSee('<meta name="twitter:title" content="COOCA — Business Operating System &amp; Omnichannel ERP">', false);
        $response->assertSee('<meta name="twitter:image" content="' . asset('assets/seo/cooca-og-default.jpg') . '">', false);

        // Structured Data Schema.org
        $response->assertSee('"@type": "SoftwareApplication"', false);
        $response->assertSee('"@type": "Organization"', false);
    }

    /**
     * Test subpages correctly inherit and override canonical, title, and social graph.
     */
    public function test_pricing_page_renders_proper_canonical_and_titles(): void
    {
        $response = $this->get('/pricing');

        $response->assertStatus(200);

        $response->assertSee('<title>Daftar Harga &amp; Paket Transparan Tanpa Biaya Tersembunyi | COOCA</title>', false);
        $response->assertSee('<link rel="canonical" href="' . url('/pricing') . '">', false);
        $response->assertSee('<meta property="og:url" content="' . url('/pricing') . '">', false);
        $response->assertSee('<meta property="og:title" content="Daftar Harga &amp; Paket Transparan | COOCA">', false);
        $response->assertSee('<meta name="twitter:title" content="Daftar Harga &amp; Paket Transparan | COOCA">', false);
    }

    /**
     * Test why-cooca page renders proper metadata and breadcrumb schema.
     */
    public function test_why_cooca_page_renders_proper_metadata(): void
    {
        $response = $this->get('/business-operating-system/why-cooca');

        $response->assertStatus(200);

        $response->assertSee('<title>Mengapa Memilih COOCA: Solusi Bisnis Terintegrasi vs Terpisah | COOCA</title>', false);
        $response->assertSee('<link rel="canonical" href="' . url('/business-operating-system/why-cooca') . '">', false);
        $response->assertSee('<meta property="og:type" content="website">', false);
    }

    /**
     * Test filtered search queries on marketplace add noindex, follow to avoid duplicate content penalties.
     */
    public function test_marketplace_search_adds_noindex_when_filters_present(): void
    {
        $response = $this->get(route('marketplace.search', ['q' => 'kopi']));

        $response->assertStatus(200);
        $response->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    /**
     * Test that the default OG image asset exists and has 1200x630 dimension.
     */
    public function test_default_og_image_file_exists_and_has_standard_dimensions(): void
    {
        $imagePath = public_path('assets/seo/cooca-og-default.jpg');

        $this->assertFileExists($imagePath);

        $size = getimagesize($imagePath);
        $this->assertNotFalse($size, 'File should be a valid image');
        $this->assertSame(1200, $size[0], 'OG image width must be 1200px');
        $this->assertSame(630, $size[1], 'OG image height must be 630px');
    }

    /**
     * Test blog post detail renders article Open Graph tags and Schema.org Article JSON-LD.
     */
    public function test_blog_post_detail_renders_article_metadata_and_json_ld(): void
    {
        $user = \App\Models\User::factory()->create();

        $post = \App\Models\Post::create([
            'user_id' => $user->id,
            'title' => 'Panduan Lengkap Manajemen Stok UMKM',
            'slug' => 'panduan-lengkap-manajemen-stok-umkm',
            'excerpt' => 'Pelajari cara mengelola stok bahan baku dan produk jadi agar tidak basi.',
            'content' => '<p>Konten artikel lengkap tentang manajemen stok ritel dan F&B.</p>',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_name' => 'Tim Pakar Bisnis COOCA',
            'category' => 'Manajemen Stok',
        ]);

        $response = $this->get(route('blog.show', $post->slug));

        $response->assertStatus(200);
        $response->assertSee('<meta property="og:type" content="article">', false);
        $response->assertSee('<meta property="article:author" content="Tim Pakar Bisnis COOCA">', false);
        $response->assertSee('<link rel="canonical" href="' . route('blog.show', $post->slug) . '">', false);
        $response->assertSee('"@type": "Article"', false);
    }
}

