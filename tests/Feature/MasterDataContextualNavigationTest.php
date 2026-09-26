<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Template\ModuleRegistry;
use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class MasterDataContextualNavigationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
        $this->seed(RbacSeeder::class);

        $this->owner = User::create([
            'name'              => 'Owner Contextual Nav Test',
            'email'             => 'owner_cnav_' . Str::random(6) . '@test.local',
            'phone'             => '62812' . rand(10000000, 99999999),
            'password'          => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name'             => 'Bisnis Contextual Nav Audit',
            'currency'         => 'IDR',
            'disabled_modules' => [],
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id'   => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
    }

    public function test_materials_index_renders_module_header_and_persistent_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('materials.index'));

        $response->assertOk();
        $response->assertSee('Katalog Bahan Baku & Pemasok');
        $response->assertSee('Katalog Bahan Baku');
        $response->assertSee('Kategori Bahan');
        $response->assertSee('Satuan Ukur (Units)');
        $response->assertSee(route('materials.index'));
        $response->assertSee(route('material-categories.index'));
    }

    public function test_material_categories_index_retains_materials_tabs_and_highlights_category_tab(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('material-categories.index'));

        $response->assertOk();
        $response->assertSee('Kategori Bahan Baku');
        $response->assertSee('Katalog Bahan Baku');
        $response->assertSee('Kategori Bahan');
        $response->assertSee('Satuan Ukur (Units)');
        $response->assertSee(route('materials.index'));
        $response->assertSee(route('material-categories.index'));
    }

    public function test_units_index_defaults_to_materials_context_and_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('units.index'));

        $response->assertOk();
        $response->assertSee('Satuan Ukur');
        $response->assertSee('Katalog Bahan Baku');
        $response->assertSee('Kategori Bahan');
        $response->assertSee('Satuan Ukur (Units)');
        $response->assertSee(route('materials.index'));
    }

    public function test_units_index_with_from_products_retains_products_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('units.index', ['from' => 'products']));

        $response->assertOk();
        $response->assertSee('Satuan Ukur');
        $response->assertSee('Katalog Produk');
        $response->assertSee('Kategori Produk');
        $response->assertSee('Satuan Ukur (Units)');
        $response->assertSee(route('products.index'));
        $response->assertSee(route('product-categories.index'));
    }

    public function test_products_index_renders_module_header_and_persistent_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('products.index'));

        $response->assertOk();
        $response->assertSee('Katalog Produk & Model HPP');
        $response->assertSee('Katalog Produk');
        $response->assertSee('Kategori Produk');
        $response->assertSee('Satuan Ukur (Units)');
        $response->assertSee(route('products.index'));
        $response->assertSee(route('product-categories.index'));
    }

    public function test_product_categories_index_retains_products_tabs_and_highlights_category_tab(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('product-categories.index'));

        $response->assertOk();
        $response->assertSee('Kategori Produk');
        $response->assertSee('Katalog Produk');
        $response->assertSee('Kategori Produk');
        $response->assertSee('Satuan Ukur (Units)');
        $response->assertSee(route('products.index'));
        $response->assertSee(route('product-categories.index'));
    }

    public function test_services_index_renders_products_hub_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('services.index'));

        $response->assertOk();
        $response->assertSee('Jasa & Layanan');
        $response->assertSee('Katalog Produk');
        $response->assertSee('Kategori Produk');
        $response->assertSee('Satuan Ukur (Units)');
    }
}
