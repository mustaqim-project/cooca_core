<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\InventoryStock;
use App\Models\Invoice;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardOverviewTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private Unit $unit;
    private Location $location;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name' => 'Pak Hendra',
            'email' => 'hendra@cooca.id',
            'password' => 'password123',
        ]);

        $this->business = Business::create([
            'name' => 'Resto Sedap Rasa',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->unit = Unit::create([
            'business_id' => $this->business->id,
            'code' => 'PCS',
            'name' => 'Pieces',
            'symbol' => 'pcs',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Utama',
            'is_active' => true,
        ]);
    }

    public function test_dashboard_renders_with_360_overview_pillars_and_kpis(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);

        // Header & Cockpit
        $response->assertSee('Ringkasan Bisnis &amp; Cockpit Operasional', false);
        $response->assertSee('Cockpit 360°');
        $response->assertSee('Terminal Kasir POS');
        $response->assertSee('Kalkulator HPP');

        // 4 Command Pillars
        $response->assertSee('Omzet Hari Ini');
        $response->assertSee('Estimasi Laba Bersih (MTD)');
        $response->assertSee('Valuasi Aset Stok');
        $response->assertSee('Piutang Belum Lunas');

        // 7 Days Trend & AI Advisor
        $response->assertSee('Tren Penjualan 7 Hari Terakhir');
        $response->assertSee('Cooca AI Business Advisor');

        // Recent tables & calculator
        $response->assertSee('Transaksi Kasir Terbaru');
        $response->assertSee('Katalog & Margin Produk', false);
        $response->assertSee('id="tour-quick-calc"', false);
    }

    public function test_dashboard_reflects_pos_orders_and_invoices(): void
    {
        // Create a completed POS order
        PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->user->id,
            'order_number' => 'ORD-20260829-001',
            'order_date' => Carbon::today(),
            'status' => PosOrder::STATUS_COMPLETED,
            'order_type' => 'dine_in',
            'subtotal' => 50000,
            'total_amount' => 50000,
            'paid_amount' => 50000,
            'total_hpp_cost' => 25000,
            'total_gross_profit' => 25000,
        ]);

        $customer = \App\Models\Customer::create([
            'business_id' => $this->business->id,
            'name' => 'PT Makmur Jaya',
            'email' => 'makmur@example.com',
            'code' => 'CUST-001',
        ]);

        // Create an unpaid invoice
        Invoice::create([
            'business_id' => $this->business->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-202608-001',
            'invoice_date' => Carbon::today(),
            'due_date' => Carbon::today()->addDays(7),
            'status' => Invoice::STATUS_UNPAID,
            'subtotal' => 150000,
            'total_amount' => 150000,
            'paid_amount' => 0,
            'balance_due' => 150000,
            'total_hpp_cost' => 80000,
            'total_gross_profit' => 70000,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('ORD-20260829-001');
        $response->assertSee('50.000');
        $response->assertSee('150.000');
    }

    public function test_dashboard_alerts_on_thin_margin_and_low_stock(): void
    {
        // Create a product with thin margin (< 25%)
        $prod = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Air Mineral Botol',
            'sku' => 'AIR-001',
            'output_unit_id' => $this->unit->id,
            'base_cost' => 4500,
            'selling_price' => 5000, // 10% margin
            'is_active' => true,
        ]);

        // Create low stock item
        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $prod->id,
            'quantity' => 2, // Low stock <= 5
            'last_cost' => 4500,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Margin Tipis Terdeteksi');
        $response->assertSee('Peringatan Stok Rendah');
        $response->assertSee('1 Stok Menipis');
    }

    public function test_products_index_page_renders_successfully(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertSee('Katalog Produk');
        $response->assertSee('Tambah Produk Baru');
    }

    public function test_quick_expense_ajax_endpoint_records_expense_and_updates_stats(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('dashboard.quick-expense'), [
                'name' => 'Beli Gas Elpiji 3kg',
                'amount' => 22000,
                'category' => 'Listrik, Air & Gas',
                'payment_method' => 'cash',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('expense.name', 'Beli Gas Elpiji 3kg');

        $this->assertDatabaseHas('expenses', [
            'business_id' => $this->business->id,
            'description' => 'Beli Gas Elpiji 3kg',
            'amount' => 22000,
        ]);
    }

    public function test_quick_stock_in_ajax_endpoint_increases_inventory_and_records_expense(): void
    {
        $material = \App\Models\Material::create([
            'business_id' => $this->business->id,
            'name' => 'Kopi Robusta Lampung',
            'cost_per_unit' => 80000,
            'unit_id' => $this->unit->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('dashboard.quick-stock-in'), [
                'material_id' => $material->id,
                'quantity' => 5,
                'unit_cost' => 85000,
                'supplier_name' => 'Toko Kopi Sejahtera',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('inventory_stocks', [
            'business_id' => $this->business->id,
            'quantity' => 5,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'business_id' => $this->business->id,
            'movement_type' => 'goods_receipt',
            'quantity_change' => 5,
        ]);
    }

    public function test_quick_material_ajax_endpoint_creates_material(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('dashboard.quick-material'), [
                'name' => 'Gula Pasir Kristal',
                'cost_per_unit' => 17000,
                'unit_id' => $this->unit->id,
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('material.name', 'Gula Pasir Kristal');

        $this->assertDatabaseHas('materials', [
            'business_id' => $this->business->id,
            'name' => 'Gula Pasir Kristal',
        ]);

        $this->assertDatabaseHas('material_prices', [
            'business_id' => $this->business->id,
            'purchase_price' => 17000,
        ]);
    }

    public function test_quick_stats_ajax_endpoint_returns_json_overview(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson(route('dashboard.quick-stats'));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'stats' => [
                    'today_sales',
                    'month_sales',
                    'month_expenses',
                    'month_net_profit_est',
                    'total_stock_valuation',
                    'unpaid_invoices_amount',
                ],
                'sevenDaysTrend',
            ]);
    }
}
