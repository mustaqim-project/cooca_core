<?php

declare(strict_types=1);

namespace Tests\Feature\Commerce;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

final class BusinessLogoAndPdfPrintTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;
    private Customer $customer;
    private Product $product;
    private Unit $pcs;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        Storage::fake('public');
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);

        $this->user = User::create([
            'name' => 'Direktur Bisnis',
            'email' => 'direktur@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->business = Business::create([
            'name' => 'PT Manufaktur Mega Kreasi',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'phone' => '081299998888',
            'email' => 'finance@megakreasi.com',
            'address' => 'Gedung Cyber 2 Lt. 10 Jakarta Selatan',
            'tax_identification_number' => '01.234.567.8-999.000',
            'bank_name' => 'BCA',
            'bank_account_number' => '888-123-4567',
            'bank_account_holder' => 'PT Manufaktur Mega Kreasi',
        ]);

        $this->business->users()->attach($this->user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $this->user->update(['active_business_id' => $this->business->id]);

        $this->pcs = Unit::where('code', 'pcs')->firstOrFail();

        $this->customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'PT Mitra Sejati',
            'company_name' => 'PT Mitra Sejati Abadi',
            'payment_terms_days' => 30,
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Meja Rapat Kayu Jati',
            'output_unit_id' => $this->pcs->id,
            'costing_method' => 'simple',
            'base_cost' => 1500000,
            'selling_price' => 2500000,
        ]);
    }

    public function test_can_upload_business_logo_and_update_profile(): void
    {
        $fakeLogo = UploadedFile::fake()->image('logo-perusahaan.png', 300, 300);

        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->put('/settings', [
                'name' => 'PT Mega Kreasi Indonesia',
                'logo' => $fakeLogo,
                'phone' => '0812-1111-2222',
                'email' => 'admin@megakreasi.co.id',
                'address' => 'Jl. TB Simatupang No. 88 Jakarta',
                'tax_identification_number' => '99.888.777.6-555.000',
                'bank_name' => 'Bank Mandiri',
                'bank_account_number' => '137-00-987654-1',
                'bank_account_holder' => 'PT Mega Kreasi Indonesia',
                'currency_code' => 'IDR',
                'currency_symbol' => 'Rp',
                'rounding_strategy' => 'ROUND_100',
            ]);

        $response->assertRedirect();

        $this->business->refresh();
        $this->assertEquals('PT Mega Kreasi Indonesia', $this->business->name);
        $this->assertNotNull($this->business->logo_path);
        $this->assertNotNull($this->business->logo_url);
        $this->assertEquals('Bank Mandiri', $this->business->bank_name);
        Storage::disk('public')->assertExists($this->business->logo_path);
    }

    public function test_can_render_invoice_print_pdf_page_with_logo_and_bank_info(): void
    {
        // Upload a logo first
        $this->business->update([
            'logo_path' => 'business_logos/test-logo.png',
        ]);

        $invoice = Invoice::create([
            'business_id' => $this->business->id,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-202608-PDF01',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => Invoice::STATUS_SENT,
            'subtotal' => 2500000,
            'total_amount' => 2500000,
            'balance_due' => 2500000,
            'paid_amount' => 0,
        ]);

        $invoice->items()->create([
            'product_id' => $this->product->id,
            'item_name' => 'Meja Rapat Kayu Jati',
            'quantity' => 1,
            'unit_id' => $this->pcs->id,
            'unit_price' => 2500000,
            'unit_hpp' => 1500000,
            'subtotal' => 2500000,
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->get("/invoices/{$invoice->id}/print");

        $response->assertOk()
            ->assertSee('FAKTUR PENJUALAN')
            ->assertSee('INV-202608-PDF01')
            ->assertSee('PT Manufaktur Mega Kreasi')
            ->assertSee('Bank BCA')
            ->assertSee('888-123-4567')
            ->assertSee('test-logo.png');
    }

    public function test_can_render_purchase_order_print_pdf_page_with_logo(): void
    {
        $this->business->update([
            'logo_path' => 'business_logos/test-logo.png',
        ]);

        $po = PurchaseOrder::create([
            'business_id' => $this->business->id,
            'po_type' => 'customer',
            'po_number' => 'PO-202608-PDF01',
            'customer_id' => $this->customer->id,
            'order_date' => now(),
            'status' => PurchaseOrder::STATUS_CONFIRMED,
            'subtotal' => 2500000,
            'total_amount' => 2500000,
        ]);

        $po->items()->create([
            'product_id' => $this->product->id,
            'item_name' => 'Meja Rapat Kayu Jati',
            'quantity' => 1,
            'unit_id' => $this->pcs->id,
            'unit_price' => 2500000,
            'cost_price_snapshot' => 1500000,
            'subtotal' => 2500000,
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->get("/purchase-orders/{$po->id}/print");

        $response->assertOk()
            ->assertSee('CUSTOMER PURCHASE ORDER')
            ->assertSee('PO-202608-PDF01')
            ->assertSee('PT Manufaktur Mega Kreasi')
            ->assertSee('PT Mitra Sejati')
            ->assertSee('test-logo.png');
    }
}
