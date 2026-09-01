<?php

declare(strict_types=1);

namespace Tests\Feature\Commerce;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class InvoiceTest extends TestCase
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
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);

        $this->user = User::create([
            'name' => 'Finance Staff',
            'email' => 'finance@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->business = Business::create([
            'name' => 'PT Manufaktur Penjualan',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
        ]);

        $this->business->users()->attach($this->user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $this->user->update(['active_business_id' => $this->business->id]);

        $this->pcs = Unit::where('code', 'pcs')->firstOrFail();

        $this->customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Klien Utama',
            'company_name' => 'PT Pelanggan Sejahtera',
            'payment_terms_days' => 30,
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Kursi Ergonomis Kantor',
            'output_unit_id' => $this->pcs->id,
            'costing_method' => 'simple',
            'base_cost' => 600000,
            'selling_price' => 1000000,
        ]);
    }

    public function test_can_create_invoice_with_accurate_hpp_snapshot_and_profit(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->post('/invoices', [
                'customer_id' => $this->customer->id,
                'invoice_date' => '2026-08-27',
                'due_date' => '2026-09-26',
                'discount_type' => 'fixed',
                'discount_value' => 100000,
                'tax_percentage' => 11,
                'shipping_cost' => 50000,
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'item_name' => 'Kursi Ergonomis Kantor',
                        'quantity' => 2,
                        'unit_id' => $this->pcs->id,
                        'unit_price' => 1000000,
                        'unit_hpp' => 600000,
                    ],
                ],
            ]);

        $response->assertRedirect();

        $invoice = Invoice::where('customer_id', $this->customer->id)->firstOrFail();
        $this->assertEquals(2000000, (float) $invoice->subtotal); // 2 * 1,000,000
        $this->assertEquals(1200000, (float) $invoice->total_hpp_cost); // 2 * 600,000
        $this->assertEquals(800000, (float) $invoice->total_gross_profit); // 2,000,000 - 1,200,000
        $this->assertEquals(100000, (float) $invoice->discount_amount);
        // Taxable = 1,900,000 * 11% = 209,000
        $this->assertEquals(209000, (float) $invoice->tax_amount);
        // Grand Total = 1,900,000 + 209,000 + 50,000 = 2,159,000
        $this->assertEquals(2159000, (float) $invoice->total_amount);
        $this->assertEquals(2159000, (float) $invoice->balance_due);
    }

    public function test_can_record_payment_and_auto_close_invoice(): void
    {
        $invoice = Invoice::create([
            'business_id' => $this->business->id,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-TEST-001',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => Invoice::STATUS_UNPAID,
            'subtotal' => 1000000,
            'total_amount' => 1000000,
            'balance_due' => 1000000,
            'paid_amount' => 0,
        ]);

        // 1. First payment: 400,000 (Partially Paid)
        $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->post("/invoices/{$invoice->id}/payments", [
                'amount' => 400000,
                'payment_date' => '2026-08-27',
                'payment_method' => 'bank_transfer',
                'reference_number' => 'REF-001',
            ])
            ->assertRedirect();

        $invoice->refresh();
        $this->assertEquals(400000, (float) $invoice->paid_amount);
        $this->assertEquals(600000, (float) $invoice->balance_due);
        $this->assertEquals(Invoice::STATUS_PARTIALLY_PAID, $invoice->status);

        // 2. Second payment: 600,000 (Full Settlement -> Paid)
        $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->post("/invoices/{$invoice->id}/payments", [
                'amount' => 600000,
                'payment_date' => '2026-08-27',
                'payment_method' => 'bank_transfer',
                'reference_number' => 'REF-002',
            ])
            ->assertRedirect();

        $invoice->refresh();
        $this->assertEquals(1000000, (float) $invoice->paid_amount);
        $this->assertEquals(0, (float) $invoice->balance_due);
        $this->assertEquals(Invoice::STATUS_PAID, $invoice->status);
    }

    public function test_can_view_invoice_print_page(): void
    {
        $invoice = Invoice::create([
            'business_id' => $this->business->id,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-202608-0099',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => Invoice::STATUS_SENT,
            'subtotal' => 500000,
            'total_amount' => 500000,
            'balance_due' => 500000,
            'paid_amount' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->get("/invoices/{$invoice->id}/print");

        $response->assertOk()
            ->assertSee('FAKTUR PENJUALAN')
            ->assertSee('INV-202608-0099');
    }

    public function test_can_export_invoices_to_csv(): void
    {
        Invoice::create([
            'business_id' => $this->business->id,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-EXPORT-001',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => Invoice::STATUS_SENT,
            'subtotal' => 500000,
            'total_amount' => 500000,
            'balance_due' => 500000,
            'paid_amount' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->get('/invoices/export-excel');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
    }
}
