<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessLandingPage;
use App\Models\CommercePaymentMethod;
use App\Models\CommerceShippingRule;
use App\Models\CommerceStoreSetting;
use App\Models\PosTable;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicStorefrontMultiPageThemeTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private BusinessLandingPage $landingPage;
    private Product $product;
    private ProductCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create([
            'name' => 'Owner Kedai Kopi',
            'email' => 'owner.kopi@cooca.id',
            'phone' => '081234567890',
            'email_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'Kopi Artisan Nusantara',
            'slug' => 'kopi-artisan-nusantara',
            'is_active' => true,
            'currency' => 'IDR',
            'email' => 'kopi@cooca.id',
            'phone' => '081234567890',
            'address' => 'Jl. Malioboro No. 10, Yogyakarta',
        ]);

        $this->business->users()->attach($this->owner->id, ['role' => 'owner']);
        $this->owner->update(['active_business_id' => $this->business->id]);

        $this->landingPage = BusinessLandingPage::create([
            'business_id' => $this->business->id,
            'headline' => 'Kopi Spesialis Sangrai Nusantara',
            'subheadline' => 'Kopi arabika asli nusantara dipanggang segar dengan metode artisan.',
            'is_published' => true,
            'theme_preset' => 'artisan_brew',
            'whatsapp_number' => '081234567890',
            'active_pages' => [
                'home' => true,
                'catalog' => true,
                'about' => true,
                'reservation' => false, // Disabled to test auto-hide
                'contact' => true,
                'blog' => true,
                'order_tracking' => true,
            ],
            'custom_labels' => [
                'catalog' => 'Katalog Biji Kopi',
                'about' => 'Cerita Roastery',
            ],
        ]);

        CommerceStoreSetting::create([
            'business_id' => $this->business->id,
            'is_storefront_enabled' => true,
            'is_discoverable' => true,
            'allow_pickup' => true,
            'allow_delivery' => true,
            'allow_scheduled_order' => true,
        ]);

        $this->category = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Single Origin',
            'slug' => 'single-origin',
        ]);

        $unit = \App\Models\Unit::firstOrCreate(
            ['code' => 'pack'],
            ['name' => 'Pack', 'category' => 'count', 'is_base' => true]
        );

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $this->category->id,
            'output_unit_id' => $unit->id,
            'name' => 'Gayo Natural Anaerob 200g',
            'slug' => 'gayo-natural-anaerob-200g',
            'description' => 'Biji kopi pilihan Gayo Aceh dengan proses natural fermentasi anaerobik beraroma buah.',
            'selling_price' => 95000,
            'is_active' => true,
            'show_in_website' => true,
            'show_price_on_web' => true,
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
            'name' => 'Kurir Instan Kota',
            'rate' => 15000,
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    public function test_homepage_renders_with_authentic_theme_tokens_and_fonts(): void
    {
        $response = $this->get('/' . $this->business->slug);

        $response->assertStatus(200);
        $response->assertSee('Kopi Artisan Nusantara');
        $response->assertSee('Kopi Spesialis Sangrai Nusantara');

        // Verify Theme Tokens CSS for 'artisan_brew'
        $response->assertSee('--theme-primary: #8B5A2B', false);
        $response->assertSee('Playfair Display');
        $response->assertSee('Gayo Natural Anaerob 200g');

        // Verify SEO Canonical and OpenGraph
        $response->assertSee('<link rel="canonical" href="' . $this->business->public_url . '">', false);
        $response->assertSee('<meta property="og:url" content="' . $this->business->public_url . '">', false);
    }

    public function test_catalog_page_renders_products_and_respects_filtering(): void
    {
        $response = $this->get('/' . $this->business->slug . '/katalog');

        $response->assertStatus(200);
        $response->assertSee('Katalog Produk & Layanan');
        $response->assertSee('Gayo Natural Anaerob 200g');
        $response->assertSee('Single Origin');

        // Test search filter
        $searchResponse = $this->get('/' . $this->business->slug . '/katalog?q=Gayo');
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Gayo Natural Anaerob 200g');

        $emptyResponse = $this->get('/' . $this->business->slug . '/katalog?q=BukanKopi');
        $emptyResponse->assertStatus(200);
        $emptyResponse->assertSee('Tidak ada produk ditemukan');
    }

    public function test_product_detail_page_renders_with_rich_opengraph_meta_tags(): void
    {
        $pdpUrl = '/' . $this->business->slug . '/produk/' . $this->product->slug;
        $response = $this->get($pdpUrl);

        $response->assertStatus(200);
        $response->assertSee('Gayo Natural Anaerob 200g');
        $response->assertSee('Rp 95.000');
        $response->assertSee('Beli Sekarang');
        $response->assertSee('Tambah ke Keranjang');
        $response->assertSee('WhatsApp');

        // Verify OpenGraph Tags for Social Sharing & WhatsApp previews (§PRD-07 §2.A.3)
        $response->assertSee('<meta property="og:title" content="Gayo Natural Anaerob 200g - Kopi Artisan Nusantara">', false);
        $response->assertSee('<meta property="product:price:amount" content="95000">', false);
        $response->assertSee('<meta property="product:price:currency" content="IDR">', false);
        $response->assertSee('<link rel="canonical" href="' . url($pdpUrl) . '">', false);
    }

    public function test_checkout_page_renders_standalone_two_column_view_without_modals(): void
    {
        $response = $this->get('/' . $this->business->slug . '/checkout');

        $response->assertStatus(200);
        $response->assertSee('Checkout Pesanan');
        $response->assertSee('Metode Penerimaan Pesanan');
        $response->assertSee('Informasi Pembeli & Pengiriman');
        $response->assertSee('Pilihan Metode Pembayaran');
        $response->assertSee('Rincian Belanja');
        $response->assertSee('QRIS Realtime');
    }

    public function test_auto_hide_navigation_only_renders_active_pages_and_hides_disabled(): void
    {
        $response = $this->get('/' . $this->business->slug);

        $response->assertStatus(200);

        // Active pages must be visible with custom labels
        $response->assertSee('Katalog Biji Kopi'); // Custom label for catalog
        $response->assertSee('Cerita Roastery');   // Custom label for about
        $response->assertSee('Kontak & Cabang');

        // Inactive page (reservation is disabled in setUp) MUST NOT be rendered in navigation
        $response->assertDontSee('Reservasi & Booking');
    }

    public function test_visiting_disabled_page_url_redirects_gracefully_to_home(): void
    {
        // Reservation is disabled for this business
        $response = $this->get('/' . $this->business->slug . '/reservasi');

        // Must redirect to home URL
        $response->assertRedirect('/' . $this->business->slug);
    }

    public function test_merchant_can_switch_to_any_of_20_authentic_themes(): void
    {
        // Switch to 'apex_velocity' (Bengkel & Otomotif)
        $this->actingAs($this->owner);

        $response = $this->put(route('landing-page.update'), [
            'theme_preset' => 'apex_velocity',
            'theme_color' => '#EA580C',
            'active_pages' => [
                'home' => true,
                'catalog' => true,
                'about' => true,
                'reservation' => true,
                'contact' => true,
                'blog' => false,
                'order_tracking' => true,
            ],
            'custom_labels' => [
                'catalog' => 'Katalog Sparepart & Ban',
            ],
        ]);

        $response->assertRedirect();

        $this->landingPage->refresh();
        $this->assertSame('apex_velocity', $this->landingPage->theme_preset);
        $this->assertTrue($this->landingPage->isPageActive('reservation'));
        $this->assertSame('Katalog Sparepart & Ban', $this->landingPage->getNavLabel('catalog', 'Katalog'));

        // Public page now reflects Apex Velocity theme
        $publicResponse = $this->get('/' . $this->business->slug);
        $publicResponse->assertStatus(200);
        $publicResponse->assertSee('--theme-primary: #EA580C', false);
        $publicResponse->assertSee('Chakra Petch');
        $publicResponse->assertSee('Katalog Sparepart & Ban');
    }

    public function test_legacy_b_slug_aliases_remain_accessible_without_breaking(): void
    {
        $response = $this->get('/b/' . $this->business->slug);
        $response->assertStatus(200);
        $response->assertSee('Kopi Artisan Nusantara');

        $catalogResponse = $this->get('/b/' . $this->business->slug . '/katalog');
        $catalogResponse->assertStatus(200);
        $catalogResponse->assertSee('Katalog Produk & Layanan');
    }
}
