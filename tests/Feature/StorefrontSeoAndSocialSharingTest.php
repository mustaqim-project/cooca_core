<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessLandingPage;
use App\Models\CommerceOrder;
use App\Models\CommercePaymentMethod;
use App\Models\CommerceShippingRule;
use App\Models\CommerceStoreSetting;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class StorefrontSeoAndSocialSharingTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private BusinessLandingPage $landingPage;
    private Product $product;
    private Post $article;
    private CommerceOrder $order;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create([
            'name' => 'Owner Merchant',
            'email' => 'owner@cooca-test.com',
            'phone' => '081234567890',
        ]);

        $this->business = Business::create([
            'name' => 'Kedai Kopi Artisan Sejahtera',
            'slug' => 'kedai-kopi-artisan',
            'is_active' => true,
            'currency' => 'IDR',
            'email' => 'kopi@sejahtera.com',
            'phone' => '081234567890',
            'address' => 'Jl. Pahlawan No. 45, Bandung',
            'logo_url' => 'https://cooca.id/storage/business-logos/sample-logo.png',
        ]);

        $this->business->users()->attach($user->id, ['role' => 'owner']);

        $this->landingPage = BusinessLandingPage::create([
            'business_id' => $this->business->id,
            'headline' => 'Kopi Artisan Terbaik dari Petani Lokal',
            'subheadline' => 'Nikmati kopi pilihan dengan aroma istimewa langsung disangrai dengan standar kualitas tinggi untuk Anda.',
            'meta_title' => 'Kedai Kopi Artisan Sejahtera - Kopi Asli Indonesia',
            'meta_description' => 'Kunjungi kedai kami atau pesan online biji kopi sangrai fresh kualitas ekspor langsung dari petani lokal terpercaya.',
            'is_published' => true,
            'theme_preset' => 'artisan_brew',
            'og_image_url' => 'https://cooca.id/assets/og/store-preview.jpg',
            'active_pages' => [
                'home' => true,
                'catalog' => true,
                'about' => true,
                'reservation' => true,
                'contact' => true,
                'blog' => true,
                'order_tracking' => true,
            ],
        ]);

        CommerceStoreSetting::create([
            'business_id' => $this->business->id,
            'is_storefront_enabled' => true,
            'is_discoverable' => true,
            'allow_pickup' => true,
            'allow_delivery' => true,
        ]);

        $category = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Biji Kopi Sangrai',
            'slug' => 'biji-kopi-sangrai',
        ]);

        $unit = \App\Models\Unit::firstOrCreate(
            ['code' => 'pack'],
            ['name' => 'Pack', 'category' => 'count', 'is_base' => true]
        );

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $category->id,
            'output_unit_id' => $unit->id,
            'name' => 'Biji Kopi Arabika Kerinci 250g',
            'slug' => 'biji-kopi-arabika-kerinci-250g',
            'description' => 'Biji kopi arabika varietas Kerinci dengan notes fruity madu yang lembut, dipanggang fresh setiap minggu.',
            'selling_price' => 85000,
            'image_path' => 'https://cooca.id/storage/products/kerinci-250g.jpg',
            'is_active' => true,
            'show_in_website' => true,
            'show_price_on_web' => true,
        ]);

        $this->article = Post::create([
            'title' => 'Rahasia Menyeduh Manual Brew Kopi Arabika',
            'slug' => 'rahasia-menyeduh-manual-brew-kopi-arabika',
            'cluster' => 'tutorial',
            'category' => 'Tips & Trik',
            'excerpt' => 'Panduan praktis perbandingan rasio seduh dan suhu air terbaik untuk mendapatkan ekstraksi kopi yang manis dan seimbang.',
            'content' => 'Langkah-langkah menyeduh kopi manual brew V60 dengan suhu air 92 derajat celcius...',
            'author_name' => 'Barista Utama',
            'cover_image' => 'https://cooca.id/storage/articles/v60-brew.jpg',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->order = CommerceOrder::create([
            'business_id' => $this->business->id,
            'order_number' => 'ORD-ARTISAN-9901',
            'customer_name' => 'Rina Gunawan',
            'customer_phone' => '081299887766',
            'customer_email' => 'rina@gmail.com',
            'fulfillment_type' => 'delivery',
            'total_amount' => 85000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'tracking_token' => 'TRK-TOKEN-9901',
        ]);

        CommercePaymentMethod::create([
            'business_id' => $this->business->id,
            'bank_name' => 'QRIS Realtime',
            'type' => 'qris',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        CommerceShippingRule::create([
            'business_id' => $this->business->id,
            'name' => 'Kurir Instan',
            'rate' => 10000,
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    public function test_storefront_home_page_has_complete_social_preview_metadata(): void
    {
        $response = $this->get('/' . $this->business->slug);
        $response->assertOk();

        $content = $response->getContent();

        // 1. Verify Unique Title & Description
        $this->assertStringContainsString('<title>', $content);
        $this->assertStringContainsString('Kedai Kopi Artisan Sejahtera', $content);
        $this->assertMatchesRegularExpression('/<meta name="description" content="[^"]+">/', $content);

        // 2. Verify Canonical and OG URL
        $expectedCanonical = url('/' . $this->business->slug);
        $this->assertStringContainsString('<link rel="canonical" href="' . $expectedCanonical . '">', $content);
        $this->assertStringContainsString('<meta property="og:url" content="' . $expectedCanonical . '">', $content);

        // 3. Verify OpenGraph tags
        $this->assertStringContainsString('<meta property="og:type" content="website">', $content);
        $this->assertStringContainsString('<meta property="og:title" content="', $content);
        $this->assertStringContainsString('<meta property="og:description" content="', $content);
        $this->assertStringContainsString('<meta property="og:image" content="https://cooca.id/assets/og/store-preview.jpg">', $content);
        $this->assertStringContainsString('<meta property="og:image:width" content="1200">', $content);
        $this->assertStringContainsString('<meta property="og:image:height" content="630">', $content);

        // 4. Verify Twitter Cards
        $this->assertStringContainsString('<meta name="twitter:card" content="summary_large_image">', $content);
        $this->assertStringContainsString('<meta name="twitter:title" content="', $content);
        $this->assertStringContainsString('<meta name="twitter:image" content="https://cooca.id/assets/og/store-preview.jpg">', $content);

        // 5. Verify No Duplicate Meta
        $this->assertSame(1, substr_count($content, '<title>'));
        $this->assertSame(1, substr_count($content, '<meta name="description"'));
        $this->assertSame(1, substr_count($content, '<link rel="canonical"'));
    }

    public function test_catalog_page_has_page_specific_metadata(): void
    {
        $response = $this->get('/' . $this->business->slug . '/katalog');
        $response->assertOk();

        $content = $response->getContent();
        $this->assertStringContainsString('Katalog Produk &amp; Layanan | Kedai Kopi Artisan Sejahtera', $content);
        $this->assertStringContainsString('<link rel="canonical" href="' . url('/' . $this->business->slug . '/katalog') . '">', $content);
        $this->assertStringContainsString('schema.org', $content);
        $this->assertStringContainsString('CollectionPage', $content);
        $this->assertSame(1, substr_count($content, '<title>'));
        $this->assertSame(1, substr_count($content, '<meta name="description"'));
    }

    public function test_product_detail_page_has_pdp_specific_opengraph_and_price_metadata(): void
    {
        $response = $this->get('/' . $this->business->slug . '/produk/' . $this->product->slug);
        $response->assertOk();

        $content = $response->getContent();
        $this->assertStringContainsString('Biji Kopi Arabika Kerinci 250g | Kedai Kopi Artisan Sejahtera', $content);
        $this->assertStringContainsString('<meta property="og:type" content="product">', $content);
        $this->assertStringContainsString('<meta property="product:price:amount" content="85000">', $content);
        $this->assertStringContainsString('<meta property="product:price:currency" content="IDR">', $content);
        $this->assertStringContainsString('https://cooca.id/storage/products/kerinci-250g.jpg', $content);
        $this->assertStringContainsString('schema.org', $content);
        $this->assertStringContainsString('"@type": "Product"', $content);
        $this->assertSame(1, substr_count($content, '<title>'));
    }

    public function test_checkout_page_has_specific_metadata(): void
    {
        $response = $this->get('/' . $this->business->slug . '/checkout');
        $response->assertOk();

        $content = $response->getContent();
        $this->assertStringContainsString('Checkout Pesanan | Kedai Kopi Artisan Sejahtera', $content);
        $this->assertStringContainsString('<link rel="canonical" href="' . url('/' . $this->business->slug . '/checkout') . '">', $content);
        $this->assertSame(1, substr_count($content, '<title>'));
    }

    public function test_about_page_has_about_page_schema_and_metadata(): void
    {
        $response = $this->get('/' . $this->business->slug . '/tentang-kami');
        $response->assertOk();

        $content = $response->getContent();
        $this->assertStringContainsString('Tentang Kami | Kedai Kopi Artisan Sejahtera', $content);
        $this->assertStringContainsString('<link rel="canonical" href="' . url('/' . $this->business->slug . '/tentang-kami') . '">', $content);
        $this->assertStringContainsString('"@type": "AboutPage"', $content);
        $this->assertSame(1, substr_count($content, '<title>'));
    }

    public function test_contact_page_has_contact_page_schema_and_metadata(): void
    {
        $response = $this->get('/' . $this->business->slug . '/kontak');
        $response->assertOk();

        $content = $response->getContent();
        $this->assertStringContainsString('Kontak &amp; Lokasi Cabang | Kedai Kopi Artisan Sejahtera', $content);
        $this->assertStringContainsString('<link rel="canonical" href="' . url('/' . $this->business->slug . '/kontak') . '">', $content);
        $this->assertStringContainsString('"@type": "ContactPage"', $content);
        $this->assertSame(1, substr_count($content, '<title>'));
    }

    public function test_articles_index_has_blog_schema_and_metadata(): void
    {
        $response = $this->get('/' . $this->business->slug . '/artikel');
        $response->assertOk();

        $content = $response->getContent();
        $this->assertStringContainsString('Artikel, Tips &amp; Wawasan | Kedai Kopi Artisan Sejahtera', $content);
        $this->assertStringContainsString('<link rel="canonical" href="' . url('/' . $this->business->slug . '/artikel') . '">', $content);
        $this->assertStringContainsString('"@type": "Blog"', $content);
        $this->assertSame(1, substr_count($content, '<title>'));
    }

    public function test_article_detail_page_has_article_schema_and_article_type(): void
    {
        $response = $this->get('/' . $this->business->slug . '/artikel/' . $this->article->slug);
        $response->assertOk();

        $content = $response->getContent();
        $this->assertStringContainsString('Rahasia Menyeduh Manual Brew Kopi Arabika | Kedai Kopi Artisan Sejahtera', $content);
        $this->assertStringContainsString('<meta property="og:type" content="article">', $content);
        $this->assertStringContainsString('https://cooca.id/storage/articles/v60-brew.jpg', $content);
        $this->assertStringContainsString('"@type": "Article"', $content);
        $this->assertSame(1, substr_count($content, '<title>'));
    }

    public function test_reservation_page_has_webpage_schema_and_metadata(): void
    {
        $response = $this->get('/' . $this->business->slug . '/reservasi');
        $response->assertOk();
        $response->assertViewIs('public.storefront.reservation');

        $content = $response->getContent();
        $this->assertStringContainsString('Reservasi Meja &amp; Layanan | Kedai Kopi Artisan Sejahtera', $content);
        $this->assertStringContainsString('<link rel="canonical" href="' . url('/' . $this->business->slug . '/reservasi') . '">', $content);
        $this->assertStringContainsString('"@type": "WebPage"', $content);
        $this->assertSame(1, substr_count($content, '<title>'));
    }

    public function test_order_tracking_page_has_standalone_social_preview_metadata(): void
    {
        $response = $this->get('/' . $this->business->slug . '/order/' . $this->order->tracking_token);
        $response->assertOk();

        $content = $response->getContent();
        $this->assertStringContainsString('Status Pesanan #ORD-ARTISAN-9901 - Kedai Kopi Artisan Sejahtera', $content);
        $this->assertStringContainsString('<meta property="og:type" content="website">', $content);
        $this->assertStringContainsString('<meta property="og:image:width" content="1200">', $content);
        $this->assertStringContainsString('<meta property="og:image:height" content="630">', $content);
        $this->assertStringContainsString('<meta name="twitter:card" content="summary_large_image">', $content);
        $this->assertSame(1, substr_count($content, '<title>'));
    }

    public function test_business_landing_page_renders_with_enhanced_metadata(): void
    {
        // Business landing view via public controller or directly
        $response = $this->get('/' . $this->business->slug);
        $response->assertOk();

        $content = $response->getContent();
        $this->assertStringContainsString('<meta property="og:image:width" content="1200">', $content);
        $this->assertStringContainsString('<meta property="og:image:height" content="630">', $content);
        $this->assertStringContainsString('<meta name="twitter:card" content="summary_large_image">', $content);
    }
}
