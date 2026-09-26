<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Location;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PurchasingAndInventoryNavigationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private Location $warehouse;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
        $this->seed(RbacSeeder::class);

        $this->owner = User::create([
            'name'              => 'Owner Purchasing Nav Test',
            'email'             => 'owner_procure_' . Str::random(6) . '@test.local',
            'phone'             => '62812' . rand(10000000, 99999999),
            'password'          => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name'             => 'Bisnis Pengadaan & Logistik Audit',
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

        $this->warehouse = Location::create([
            'business_id' => $this->business->id,
            'name'        => 'Gudang Pusat Logistik',
            'type'        => 'warehouse',
            'is_active'   => true,
        ]);
    }

    // ==========================================
    // 1. PURCHASING HUB TESTS (4 VIEWS)
    // ==========================================

    public function test_purchase_orders_index_renders_module_header_and_persistent_purchasing_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('purchase-orders.index'));

        $response->assertOk();
        $response->assertSee('Pesanan Pembelian (PO)');
        $this->assertPurchasingTabsPresent($response);
    }

    public function test_purchasing_bills_index_renders_module_header_and_persistent_purchasing_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('purchasing.bills.index'));

        $response->assertOk();
        $response->assertSee('Tagihan Vendor (Bills)');
        $this->assertPurchasingTabsPresent($response);
    }

    public function test_purchase_returns_index_renders_module_header_and_persistent_purchasing_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('purchase.returns.index'));

        $response->assertOk();
        $response->assertSee('Retur Pembelian');
        $this->assertPurchasingTabsPresent($response);
    }

    public function test_suppliers_index_renders_module_header_and_persistent_purchasing_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('suppliers.index'));

        $response->assertOk();
        $response->assertSee('Pemasok &amp; Vendor', false);
        $this->assertPurchasingTabsPresent($response);
    }

    // ==========================================
    // 2. INVENTORY HUB TESTS (3 VIEWS)
    // ==========================================

    public function test_inventory_stocks_renders_module_header_and_persistent_inventory_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('inventory.stocks'));

        $response->assertOk();
        $response->assertSee('Ringkasan Stok');
        $this->assertInventoryTabsPresent($response);
    }

    public function test_warehouse_index_renders_module_header_and_persistent_inventory_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('warehouse.index'));

        $response->assertOk();
        $response->assertSee('Lokasi Gudang');
        $this->assertInventoryTabsPresent($response);
    }

    public function test_warehouse_show_renders_module_header_and_persistent_inventory_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('warehouse.show', $this->warehouse));

        $response->assertOk();
        $response->assertSee('Gudang Pusat Logistik');
        $this->assertInventoryTabsPresent($response);
    }

    // ==========================================
    // HELPER ASSERTIONS
    // ==========================================

    private function assertPurchasingTabsPresent($response): void
    {
        $response->assertSee('Pesanan Pembelian (PO)');
        $response->assertSee(route('purchase-orders.index'));

        $response->assertSee('Tagihan Vendor (Bills)');
        $response->assertSee(route('purchasing.bills.index'));

        $response->assertSee('Retur Pembelian');
        $response->assertSee(route('purchase.returns.index'));

        $response->assertSee('Pemasok &amp; Vendor', false);
        $response->assertSee(route('suppliers.index'));
    }

    private function assertInventoryTabsPresent($response): void
    {
        $response->assertSee('Ringkasan Stok');
        $response->assertSee(route('inventory.stocks'));

        $response->assertSee('Lokasi Gudang');
        $response->assertSee(route('warehouse.index'));

        $response->assertSee('Transfer Stok');
        $response->assertSee(route('inventory.transfers.index'));

        $response->assertSee('Stok Opname');
        $response->assertSee(route('inventory.opnames.index'));

        $response->assertSee('Riwayat Mutasi');
        $response->assertSee(route('inventory.movements'));
    }
}
