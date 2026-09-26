<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Location;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class SalesAndCrmNavigationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private Location $location;
    private Customer $customer;
    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
        $this->seed(RbacSeeder::class);

        $this->owner = User::create([
            'name'              => 'Owner Sales & CRM Nav Test',
            'email'             => 'owner_sales_' . Str::random(6) . '@test.local',
            'phone'             => '62812' . rand(10000000, 99999999),
            'password'          => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name'             => 'Bisnis B2B Sales & CRM Audit',
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

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name'        => 'Kantor Utama & Hub Distribusi',
            'type'        => 'office',
            'is_active'   => true,
        ]);

        $this->customer = Customer::create([
            'business_id'   => $this->business->id,
            'code'          => 'CUST-001',
            'name'          => 'PT Mitra Niaga Nusantara',
            'company_name'  => 'Mitra Niaga Group',
            'email'         => 'procurement@mitraniaga.local',
            'phone'         => '6281234567890',
        ]);

        $this->invoice = Invoice::create([
            'business_id'    => $this->business->id,
            'customer_id'    => $this->customer->id,
            'location_id'    => $this->location->id,
            'invoice_number' => 'INV-2026-B2B-001',
            'invoice_date'   => now(),
            'due_date'       => now()->addDays(30),
            'status'         => Invoice::STATUS_UNPAID,
            'subtotal'       => 15000000,
            'total_amount'   => 15000000,
            'balance_due'    => 15000000,
            'paid_amount'    => 0,
            'created_by'     => $this->owner->id,
        ]);
    }

    // ==========================================
    // 1. B2B SALES HUB TESTS (5 VIEWS)
    // ==========================================

    public function test_sales_orders_index_renders_module_header_and_persistent_sales_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('sales.orders.index'));

        $response->assertOk();
        $response->assertSee('Pesanan Penjualan (SO)');
        $this->assertSalesTabsPresent($response);
    }

    public function test_sales_quotations_index_renders_module_header_and_persistent_sales_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('sales.quotations.index'));

        $response->assertOk();
        $response->assertSee('Surat Penawaran');
        $this->assertSalesTabsPresent($response);
    }

    public function test_invoices_index_renders_module_header_and_persistent_sales_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('invoices.index'));

        $response->assertOk();
        $response->assertSee('Faktur Penjualan');
        $this->assertSalesTabsPresent($response);
    }

    public function test_invoices_show_renders_module_header_and_persistent_sales_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('invoices.show', $this->invoice->id));

        $response->assertOk();
        $response->assertSee($this->invoice->invoice_number);
        $this->assertSalesTabsPresent($response);
    }

    public function test_sales_returns_index_renders_module_header_and_persistent_sales_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('sales.returns.index'));

        $response->assertOk();
        $response->assertSee('Retur Penjualan');
        $this->assertSalesTabsPresent($response);
    }

    // ==========================================
    // 2. CRM & CUSTOMERS HUB TESTS (3 VIEWS)
    // ==========================================

    public function test_customers_index_renders_module_header_and_persistent_crm_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('customers.index'));

        $response->assertOk();
        $response->assertSee('Buku Pelanggan');
        $this->assertCrmTabsPresent($response);
    }

    public function test_crm_members_index_renders_module_header_and_persistent_crm_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('crm.members.index'));

        $response->assertOk();
        $response->assertSee('Member &amp; Tingkatan', false);
        $this->assertCrmTabsPresent($response);
    }

    public function test_crm_vouchers_index_renders_module_header_and_persistent_crm_tabs(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('crm.vouchers.index'));

        $response->assertOk();
        $response->assertSee('Voucher Promo');
        $this->assertCrmTabsPresent($response);
    }

    // ==========================================
    // 3. STOREFRONT HUB NAVIGATION & BREADCRUMB
    // ==========================================

    public function test_storefront_settings_renders_apple_hig_breadcrumb_and_navigation(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('storefront.settings.index'));

        $response->assertOk();
        $response->assertSee('aria-label="Breadcrumb"', false);
        $response->assertSee('Toko Online');
        $response->assertSee('Pengaturan Etalase');
    }

    // ==========================================
    // HELPER ASSERTIONS
    // ==========================================

    private function assertSalesTabsPresent($response): void
    {
        $response->assertSee('Pesanan Penjualan (SO)');
        $response->assertSee('Surat Penawaran');
        $response->assertSee('Faktur Penjualan');
        $response->assertSee('Retur Penjualan');
        $response->assertSee(route('sales.orders.index'));
        $response->assertSee(route('sales.quotations.index'));
        $response->assertSee(route('invoices.index'));
        $response->assertSee(route('sales.returns.index'));
    }

    private function assertCrmTabsPresent($response): void
    {
        $response->assertSee('Buku Pelanggan');
        $response->assertSee('Member &amp; Tingkatan', false);
        $response->assertSee('Voucher Promo');
        $response->assertSee(route('customers.index'));
        $response->assertSee(route('crm.members.index'));
        $response->assertSee(route('crm.vouchers.index'));
    }
}
