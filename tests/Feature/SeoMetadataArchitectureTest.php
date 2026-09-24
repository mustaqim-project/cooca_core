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
     * Test that the default OG image asset and official brand logos exist with standard dimensions.
     */
    public function test_default_og_image_and_brand_logos_exist_with_proper_dimensions(): void
    {
        // 1. Social Graph Image (1200x630)
        $ogPath = public_path('assets/seo/cooca-og-default.jpg');
        $this->assertFileExists($ogPath);
        $ogSize = getimagesize($ogPath);
        $this->assertNotFalse($ogSize, 'OG image should be a valid image');
        $this->assertSame(1200, $ogSize[0], 'OG image width must be 1200px');
        $this->assertSame(630, $ogSize[1], 'OG image height must be 630px');

        // 2. Square Logo 1:1 (1024x1024)
        $squareLogoPath = public_path('assets/image/cooca-logo-square.png');
        $this->assertFileExists($squareLogoPath);
        $squareSize = getimagesize($squareLogoPath);
        $this->assertNotFalse($squareSize, 'Square logo should be a valid image');
        $this->assertSame(1024, $squareSize[0], 'Square logo width must be 1024px');
        $this->assertSame(1024, $squareSize[1], 'Square logo height must be 1024px');
        $this->assertSame($squareSize[0], $squareSize[1], 'Square logo must have 1:1 aspect ratio');

        // 3. Landscape Logo (1024x337)
        $landscapeLogoPath = public_path('assets/image/cooca-logo-landscape.png');
        $this->assertFileExists($landscapeLogoPath);
        $landscapeSize = getimagesize($landscapeLogoPath);
        $this->assertNotFalse($landscapeSize, 'Landscape logo should be a valid image');
        $this->assertGreaterThan($landscapeSize[1], $landscapeSize[0], 'Landscape logo width must be wider than height');
    }

    /**
     * Test blog post detail strictly uses the blog's cover image when available.
     */
    public function test_blog_post_detail_strictly_uses_blog_cover_image(): void
    {
        $user = \App\Models\User::factory()->create();
        $expectedCover = 'https://cooca.id/storage/blog/omzet-meledak-2026.jpg';

        $post = \App\Models\Post::create([
            'user_id' => $user->id,
            'title' => 'Strategi Meningkatkan Omzet Bisnis F&B',
            'slug' => 'strategi-meningkatkan-omzet-bisnis-fnb',
            'excerpt' => 'Langkah praktis menaikkan omzet warung kopi dan resto.',
            'content' => '<p>Konten lengkap strategi omzet.</p>',
            'cover_image' => $expectedCover,
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_name' => 'Tim Pakar Bisnis COOCA',
            'category' => 'Strategi Penjualan',
        ]);

        $response = $this->get(route('blog.show', $post->slug));

        $response->assertStatus(200);
        $response->assertSee('<meta property="og:type" content="article">', false);
        $response->assertSee('<meta property="og:image" content="' . $expectedCover . '">', false);
        $response->assertSee('<meta name="twitter:image" content="' . $expectedCover . '">', false);
        $response->assertSee('"image": [\n        "' . $expectedCover . '"\n    ]', false);
    }

    /**
     * Test blog post detail falls back to the default social graph image when no cover image exists.
     */
    public function test_blog_post_without_cover_image_falls_back_to_default_social_graph(): void
    {
        $user = \App\Models\User::factory()->create();

        $post = \App\Models\Post::create([
            'user_id' => $user->id,
            'title' => 'Tips Rekonsiliasi Kas Toko',
            'slug' => 'tips-rekonsiliasi-kas-toko',
            'excerpt' => 'Panduan rekonsiliasi kas harian.',
            'content' => '<p>Konten rekonsiliasi.</p>',
            'cover_image' => null,
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_name' => 'Tim Akuntansi COOCA',
            'category' => 'Keuangan',
        ]);

        $response = $this->get(route('blog.show', $post->slug));

        $response->assertStatus(200);
        $response->assertSee('<meta property="og:image" content="' . asset('assets/seo/cooca-og-default.jpg') . '">', false);
        $response->assertSee('<meta name="twitter:image" content="' . asset('assets/seo/cooca-og-default.jpg') . '">', false);
    }

    /**
     * Test storefront product detail strictly uses the product's image for social sharing.
     */
    public function test_product_detail_strictly_uses_product_image(): void
    {
        $business = \App\Models\Business::create([
            'name' => 'Kedai Kopi Arabika Mantap',
            'slug' => 'kedai-kopi-arabika-mantap',
            'is_active' => true,
            'currency' => 'IDR',
        ]);

        \App\Models\BusinessLandingPage::create([
            'business_id' => $business->id,
            'headline' => 'Kopi Pilihan Nusantara',
            'is_published' => true,
            'og_image_url' => 'https://cooca.id/assets/og/store-fallback.jpg',
        ]);

        \App\Models\CommerceStoreSetting::create([
            'business_id' => $business->id,
            'is_storefront_enabled' => true,
            'is_discoverable' => true,
        ]);

        $unit = \App\Models\Unit::firstOrCreate(
            ['code' => 'pack'],
            ['name' => 'Pack', 'category' => 'count', 'is_base' => true]
        );

        $expectedProductImg = 'https://cooca.id/storage/products/arabika-gayo-specialty.jpg';

        $product = \App\Models\Product::create([
            'business_id' => $business->id,
            'output_unit_id' => $unit->id,
            'name' => 'Biji Kopi Arabika Gayo 200g',
            'slug' => 'biji-kopi-arabika-gayo-200g',
            'description' => 'Biji kopi pilihan single origin Gayo Aceh dengan cita rasa floral dan citrus.',
            'selling_price' => 95000,
            'image_path' => $expectedProductImg,
            'is_active' => true,
            'show_in_website' => true,
        ]);

        $response = $this->get('/' . $business->slug . '/produk/' . $product->slug);

        $response->assertStatus(200);
        $response->assertSee('<meta property="og:image" content="' . $expectedProductImg . '">', false);
        $response->assertSee('<meta name="twitter:image" content="' . $expectedProductImg . '">', false);
        $response->assertSee('"image": ["' . $expectedProductImg . '"]', false);
    }
}

