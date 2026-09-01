<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LayoutSidebarNavbarPlanTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@cooca.id',
            'password' => 'password123',
        ]);

        $this->business = Business::create([
            'name' => 'Kopi Mantap Jiwa',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'rounding_strategy' => 'ROUND_100',
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);
    }

    public function test_sidebar_renders_clean_logical_groups(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);

        // Check streamlined consolidated modules
        $response->assertSee('Kasir & Penjualan', false);
        $response->assertSee('Produk & Inventori', false);
        $response->assertSee('Keuangan & Analitik', false);
        $response->assertSee('Pengaturan Toko & Tim', false);

        // Check key menu routes & tour IDs
        $response->assertSee('id="tour-nav-dashboard"', false);
        $response->assertSee('id="tour-nav-calculator"', false);
        $response->assertSee('id="tour-nav-products"', false);
        $response->assertSee('id="tour-nav-materials"', false);
        $response->assertSee('id="tour-nav-labor-machines"', false);
        $response->assertSee('id="tour-nav-simulator"', false);
        $response->assertSee('id="tour-nav-profitability"', false);
        $response->assertSee('id="tour-nav-reports"', false);
        $response->assertSee('id="tour-nav-settings"', false);

        // Check Mobile Bottom Navigation App Bar
        $response->assertSee('Aksi Cepat Instan');
        $response->assertSee('Home');
        $response->assertSee('Kasir');
        $response->assertSee('Menu');
    }

    public function test_navbar_renders_free_plan_tracker_with_usage_progress(): void
    {
        $unit = Unit::create([
            'business_id' => $this->business->id,
            'code' => 'PCS',
            'name' => 'Pieces',
            'symbol' => 'pcs',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);

        // Create 2 products
        Product::create([
            'business_id' => $this->business->id,
            'name' => 'Espresso Single',
            'sku' => 'ESP-001',
            'output_unit_id' => $unit->id,
            'base_cost' => 3000,
            'selling_price' => 10000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Free Plan');
        $response->assertSee('Upgrade');
        $response->assertSee('Katalog Produk:');
        $response->assertSee('/ 50');
        $response->assertSee('Resep / BOM:');
        $response->assertSee('/ 20');
        $response->assertSee('Faktur Bulan Ini:');
        $response->assertSee('/ 10');
        $response->assertSee('Tingkatkan ke Cooca Core');
    }

    public function test_navbar_renders_core_plan_when_subscribed(): void
    {
        $entitlement = app(\App\Domain\Billing\EntitlementService::class);
        $entitlement->upgradeToCore($this->business, 'monthly');

        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Cooca Core');
        $response->assertSee('Cooca Core License');
        $response->assertSee('Unlimited (Tanpa Batas)');
        $response->assertSee('Sisa Kuota Token AI:');
    }
}
