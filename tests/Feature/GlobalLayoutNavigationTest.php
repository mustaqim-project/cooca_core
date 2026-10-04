<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use App\Support\Navigation\NavigationRegistry;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class GlobalLayoutNavigationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('id');
        Context::flush();

        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
        $this->seed(RbacSeeder::class);

        $this->owner = User::create([
            'name'              => 'Owner Layout Nav Test',
            'email'             => 'owner_layout_' . Str::random(6) . '@test.local',
            'phone'             => '62812' . rand(10000000, 99999999),
            'password'          => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name'             => 'Bisnis Global Layout Audit',
            'currency'         => 'IDR',
            'currency_code'    => 'IDR',
            'currency_symbol'  => 'Rp',
            'business_scale'   => Business::SCALE_CORPORATE,
            'disabled_modules' => [],
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id'   => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
    }

    public function test_global_layout_renders_sidebar_without_variable_collision(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('sidebar-nav');
        $response->assertSee('Dashboard Utama');
    }

    public function test_sidebar_opens_sales_group_when_visiting_sales_order(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('sales.orders.index'));

        $response->assertOk();
        $response->assertSee('salesOpen: true', false);
    }

    public function test_sidebar_opens_purchasing_group_when_visiting_purchase_order(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('purchase-orders.index'));

        $response->assertOk();
        $response->assertSee('purchasingOpen: true', false);
    }

    public function test_sidebar_opens_inventory_group_when_visiting_products_catalog(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('products.index'));

        $response->assertOk();
        $response->assertSee('inventoryOpen: true', false);
    }

    public function test_sidebar_opens_finance_group_when_visiting_cash_bank(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('finance.cash-bank.index'));

        $response->assertOk();
        $response->assertSee('financeOpen: true', false);
    }

    public function test_sidebar_opens_marketing_group_when_visiting_whatsapp(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('whatsapp.index'));

        $response->assertOk();
        $response->assertSee('marketingOpen: true', false);
    }

    public function test_sidebar_defines_pos_open_and_b2b_sales_open_in_alpine_state(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('posOpen:', false);
        $response->assertSee('b2bSalesOpen:', false);
    }

    public function test_sidebar_opens_b2b_sales_group_when_visiting_sales_order(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('sales.orders.index'));

        $response->assertOk();
        $response->assertSee('b2bSalesOpen: true', false);
    }
}
