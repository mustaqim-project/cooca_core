<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessLandingPage;
use App\Models\CommerceStoreSetting;
use App\Models\Location;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Services\Seo\SitemapService;
use App\Support\Context;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicBusinessDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    private Business $discoverableStore;
    private Business $hiddenStore;
    private Business $disabledStorefront;
    private Business $inactiveBusiness;
    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);
        $this->unit = Unit::first() ?? Unit::create(['name' => 'Pcs', 'symbol' => 'pcs', 'is_default' => true]);

        // 1. Discoverable active store (F&B / Cafe)
        $this->discoverableStore = Business::create([
            'name' => 'Kopi Senja Nusantara',
            'slug' => 'kopi-senja-nusantara',
            'description' => 'Kedai kopi artisan dengan biji kopi pilihan nusantara.',
            'address' => 'Jl. Senopati No. 45, Jakarta Selatan',
            'industry_category' => 'fnb',
            'template_code' => 'fnb_cafe',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        CommerceStoreSetting::create([
            'business_id' => $this->discoverableStore->id,
            'is_storefront_enabled' => true,
            'is_discoverable' => true,
            'allow_pickup' => true,
            'allow_delivery' => true,
            'allow_scheduled_order' => true,
            'allow_reservation' => true,
            'allow_customer_po' => false,
        ]);

        BusinessLandingPage::create([
            'business_id' => $this->discoverableStore->id,
            'headline' => 'Kopi Terbaik dari Tanah Nusantara',
            'subheadline' => 'Nikmati racikan espresso dan manual brew khas nusantara.',
            'is_published' => true,
        ]);

        $loc1 = Location::create([
            'business_id' => $this->discoverableStore->id,
            'name' => 'Outlet Senopati',
            'is_default' => true,
        ]);

        Product::create([
            'business_id' => $this->discoverableStore->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Caramel Macchiato Signature',
            'type' => 'goods',
            'selling_price' => 38000,
            'is_active' => true,
        ]);

        // 2. Active store with is_discoverable = false (Hidden from directory)
        $this->hiddenStore = Business::create([
            'name' => 'Klinik Privat Rahasia',
            'slug' => 'klinik-privat-rahasia',
            'description' => 'Klinik konsultasi privat khusus member.',
            'industry_category' => 'service',
            'template_code' => 'service_salon',
            'is_active' => true,
        ]);

        CommerceStoreSetting::create([
            'business_id' => $this->hiddenStore->id,
            'is_storefront_enabled' => true,
            'is_discoverable' => false,
        ]);

        // 3. Active store with is_storefront_enabled = false (Storefront disabled)
        $this->disabledStorefront = Business::create([
            'name' => 'Gudang Grosir Tertutup',
            'slug' => 'gudang-grosir-tertutup',
            'industry_category' => 'retail',
            'template_code' => 'retail_boutique',
            'is_active' => true,
        ]);

        CommerceStoreSetting::create([
            'business_id' => $this->disabledStorefront->id,
            'is_storefront_enabled' => false,
            'is_discoverable' => true,
        ]);

        // 4. Inactive business
        $this->inactiveBusiness = Business::create([
            'name' => 'Toko Nonaktif Gulung Tikar',
            'slug' => 'toko-nonaktif-gulung-tikar',
            'is_active' => false,
        ]);

        CommerceStoreSetting::create([
            'business_id' => $this->inactiveBusiness->id,
            'is_storefront_enabled' => true,
            'is_discoverable' => true,
        ]);
    }

    public function test_can_render_public_discovery_page_and_directory_alias(): void
    {
        $response1 = $this->get(route('public.discovery.index'));
        $response1->assertOk()
            ->assertSee('Jelajah')
            ->assertSee('Toko & Etalase')
            ->assertSee('Kopi Senja Nusantara');

        $response2 = $this->get(route('public.directory.index'));
        $response2->assertOk()
            ->assertSee('Kopi Senja Nusantara');
    }

    public function test_only_active_and_discoverable_stores_are_displayed(): void
    {
        $response = $this->get(route('public.discovery.index'));
        $response->assertOk();

        // Visible
        $response->assertSee('Kopi Senja Nusantara');

        // Hidden / Excluded
        $response->assertDontSee('Klinik Privat Rahasia');
        $response->assertDontSee('Gudang Grosir Tertutup');
        $response->assertDontSee('Toko Nonaktif Gulung Tikar');
    }

    public function test_can_search_by_store_name_and_product_name(): void
    {
        // 1. Search by store name
        $resStore = $this->get(route('public.discovery.index', ['q' => 'Kopi Senja']));
        $resStore->assertOk()
            ->assertSee('Kopi Senja Nusantara');

        // 2. Search by product name
        $resProduct = $this->get(route('public.discovery.index', ['q' => 'Caramel Macchiato']));
        $resProduct->assertOk()
            ->assertSee('Kopi Senja Nusantara');

        // 3. Search non-existent
        $resEmpty = $this->get(route('public.discovery.index', ['q' => 'BarangAntikLangka123']));
        $resEmpty->assertOk()
            ->assertSee('Toko Tidak Ditemukan')
            ->assertDontSee('Kopi Senja Nusantara');
    }

    public function test_can_filter_by_category_and_capabilities(): void
    {
        // 1. Filter by fnb category -> Match
        $resFnb = $this->get(route('public.discovery.index', ['kategori' => 'fnb']));
        $resFnb->assertOk()
            ->assertSee('Kopi Senja Nusantara');

        // 2. Filter by manufacture category -> No match
        $resMfg = $this->get(route('public.discovery.index', ['kategori' => 'manufacture']));
        $resMfg->assertOk()
            ->assertSee('Toko Tidak Ditemukan')
            ->assertDontSee('Kopi Senja Nusantara');

        // 3. Filter by pickup capability -> Match
        $resPickup = $this->get(route('public.discovery.index', ['fitur' => 'pickup']));
        $resPickup->assertOk()
            ->assertSee('Kopi Senja Nusantara');

        // 4. Filter by customer_po capability -> No match (Kopi Senja doesn't have allow_customer_po)
        $resPo = $this->get(route('public.discovery.index', ['fitur' => 'po']));
        $resPo->assertOk()
            ->assertSee('Toko Tidak Ditemukan');
    }

    public function test_sitemap_includes_discoverable_storefronts_and_discovery_hub(): void
    {
        $sitemapService = app(SitemapService::class);
        $publicUrls = $sitemapService->getPublicUrls();
        $urlsList = array_column($publicUrls, 'loc');

        // Verify /jelajah is included
        $this->assertTrue(
            collect($urlsList)->contains(fn ($u) => str_ends_with($u, '/jelajah')),
            'Sitemap must include /jelajah discovery URL.'
        );

        // Verify discoverable store is included
        $this->assertTrue(
            collect($urlsList)->contains(fn ($u) => str_contains($u, '/b/kopi-senja-nusantara')),
            'Sitemap must include discoverable store /b/kopi-senja-nusantara.'
        );

        // Verify hidden store is NOT in sitemap
        $this->assertFalse(
            collect($urlsList)->contains(fn ($u) => str_contains($u, '/b/klinik-privat-rahasia')),
            'Sitemap must NOT include non-discoverable store.'
        );
    }

    public function test_business_landing_renders_schema_org_local_business_json_ld(): void
    {
        $response = $this->get(route('public.business.landing', $this->discoverableStore->slug));
        $response->assertOk();

        // Check Schema.org JSON-LD tag exists
        $content = $response->getContent();
        $this->assertStringContainsString('application/ld+json', $content);
        $this->assertStringContainsString('https://schema.org', $content);
        $this->assertStringContainsString('Restaurant', $content);
        $this->assertStringContainsString('Kopi Senja Nusantara', $content);
        $this->assertStringContainsString('Jl. Senopati No. 45', $content);
    }
}
