<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\CommerceStoreSetting;
use App\Models\Product;
use App\Models\Unit;
use App\Support\Context;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicMarketplaceSearchTest extends TestCase
{
    use RefreshDatabase;

    private Business $discoverableBusiness;
    private Business $hiddenBusiness;
    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);

        // Discoverable Business
        $this->discoverableBusiness = Business::create([
            'name' => 'Kopi Nusantara',
            'slug' => 'kopi-nusantara',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
            'industry_category' => 'fnb',
        ]);

        CommerceStoreSetting::create([
            'business_id' => $this->discoverableBusiness->id,
            'is_storefront_enabled' => true,
            'is_discoverable' => true,
            'allow_pickup' => true,
        ]);

        // Hidden Business (Storefront disabled / not discoverable)
        $this->hiddenBusiness = Business::create([
            'name' => 'Toko Rahasia',
            'slug' => 'toko-rahasia',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
            'industry_category' => 'fnb',
        ]);

        CommerceStoreSetting::create([
            'business_id' => $this->hiddenBusiness->id,
            'is_storefront_enabled' => false,
            'is_discoverable' => false,
        ]);

        $this->unit = Unit::firstOrCreate(
            ['code' => 'pcs'],
            ['name' => 'Pcs', 'category' => 'count', 'is_base' => true]
        );
    }

    public function test_landing_page_renders_marketplace_search_form_and_category_links(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('marketplace/cari', false);
        $response->assertSee('name="q"', false);
        $response->assertSee('Cari produk atau bisnis...', false);
        $response->assertSee('kategori=fnb', false);
        $response->assertSee('kategori=retail', false);
        $response->assertSee('kategori=service', false);
    }

    public function test_search_returns_products_with_show_in_website_true(): void
    {
        $visibleProduct = Product::create([
            'business_id' => $this->discoverableBusiness->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Kopi Susu Gula Aren Spesial',
            'selling_price' => 25000,
            'is_active' => true,
            'show_in_website' => true,
        ]);

        $response = $this->get('/marketplace/cari?q=Kopi');

        $response->assertStatus(200);
        $response->assertSee('Kopi Susu Gula Aren Spesial');
    }

    public function test_search_strictly_excludes_products_with_show_in_website_false_even_if_keyword_matches(): void
    {
        $hiddenProduct = Product::create([
            'business_id' => $this->discoverableBusiness->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Kopi Rahasia Internal Kasir',
            'selling_price' => 15000,
            'is_active' => true,
            'show_in_website' => false,
        ]);

        $response = $this->get('/marketplace/cari?q=Kopi');

        $response->assertStatus(200);
        $response->assertDontSee('Kopi Rahasia Internal Kasir');
    }

    public function test_search_strictly_excludes_inactive_products(): void
    {
        $inactiveProduct = Product::create([
            'business_id' => $this->discoverableBusiness->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Kopi Robusta Nonaktif',
            'selling_price' => 20000,
            'is_active' => false,
            'show_in_website' => true,
        ]);

        $response = $this->get('/marketplace/cari?q=Kopi');

        $response->assertStatus(200);
        $response->assertDontSee('Kopi Robusta Nonaktif');
    }

    public function test_search_strictly_excludes_products_from_non_discoverable_stores(): void
    {
        $hiddenStoreProduct = Product::create([
            'business_id' => $this->hiddenBusiness->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Kopi Dari Toko Tertutup',
            'selling_price' => 30000,
            'is_active' => true,
            'show_in_website' => true,
        ]);

        $response = $this->get('/marketplace/cari?q=Kopi');

        $response->assertStatus(200);
        $response->assertDontSee('Kopi Dari Toko Tertutup');
    }

    public function test_ajax_search_returns_json_and_strictly_respects_show_in_website(): void
    {
        $visibleProduct = Product::create([
            'business_id' => $this->discoverableBusiness->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Kopi Arabika Single Origin',
            'selling_price' => 35000,
            'is_active' => true,
            'show_in_website' => true,
        ]);

        $hiddenProduct = Product::create([
            'business_id' => $this->discoverableBusiness->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Kopi Internal Gudang',
            'selling_price' => 10000,
            'is_active' => true,
            'show_in_website' => false,
        ]);

        $response = $this->getJson('/marketplace/cari?q=Kopi');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data);

        $names = collect($data)->pluck('name')->all();
        $this->assertContains('Kopi Arabika Single Origin', $names);
        $this->assertNotContains('Kopi Internal Gudang', $names);
    }

    public function test_landing_page_preview_cards_only_display_show_in_website_true_products(): void
    {
        $visibleProduct = Product::create([
            'business_id' => $this->discoverableBusiness->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Kopi Preview Tampil',
            'selling_price' => 28000,
            'is_active' => true,
            'show_in_website' => true,
        ]);

        $hiddenProduct = Product::create([
            'business_id' => $this->discoverableBusiness->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Kopi Preview Rahasia',
            'selling_price' => 12000,
            'is_active' => true,
            'show_in_website' => false,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Kopi Preview Tampil');
        $response->assertDontSee('Kopi Preview Rahasia');
    }
}
