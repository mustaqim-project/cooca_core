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
use Illuminate\Support\Str;
use Tests\TestCase;

final class PurchaseOrderTest extends TestCase
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
            'name' => 'Commercial Officer',
            'email' => 'commerce@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->business = Business::create([
            'name' => 'PT Manufaktur Komersial',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
        ]);

        $this->business->users()->attach($this->user->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $this->user->update(['active_business_id' => $this->business->id]);

        $this->pcs = Unit::where('code', 'pcs')->firstOrFail();

        $this->customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Klien Prioritas',
            'company_name' => 'PT Mega Korporat',
            'payment_terms_days' => 30,
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Produk A Standard',
            'output_unit_id' => $this->pcs->id,
            'costing_method' => 'simple',
            'base_cost' => 50000,
            'selling_price' => 75000,
        ]);
    }

    public function test_can_create_customer_purchase_order_with_calculated_totals(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->post('/purchase-orders', [
                'po_type' => 'customer',
                'customer_id' => $this->customer->id,
                'order_date' => '2026-08-27',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'tax_percentage' => 11,
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'item_name' => 'Produk A Standard',
                        'quantity' => 10,
                        'unit_id' => $this->pcs->id,
                        'unit_price' => 75000,
                    ],
                ],
            ]);

        $response->assertRedirect();

        $po = PurchaseOrder::where('customer_id', $this->customer->id)->firstOrFail();
        $this->assertEquals(750000, (float) $po->subtotal); // 10 * 75,000
        $this->assertEquals(75000, (float) $po->discount_amount); // 10% of 750k
        // Taxable = 675,000 * 11% = 74,250
        $this->assertEquals(74250, (float) $po->tax_amount);
        // Grand Total = 675,000 + 74,250 = 749,250
        $this->assertEquals(749250, (float) $po->total_amount);
    }

    public function test_can_confirm_purchase_order(): void
    {
        $po = PurchaseOrder::create([
            'business_id' => $this->business->id,
            'po_type' => 'customer',
            'po_number' => 'PO-TEST-001',
            'customer_id' => $this->customer->id,
            'order_date' => now(),
            'status' => PurchaseOrder::STATUS_DRAFT,
            'subtotal' => 100000,
            'total_amount' => 100000,
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->post("/purchase-orders/{$po->id}/confirm");

        $response->assertRedirect();
        $this->assertEquals(PurchaseOrder::STATUS_CONFIRMED, $po->fresh()->status);
    }

    public function test_can_generate_invoice_from_customer_purchase_order(): void
    {
        $po = PurchaseOrder::create([
            'business_id' => $this->business->id,
            'po_type' => 'customer',
            'po_number' => 'PO-202608-0001',
            'customer_id' => $this->customer->id,
            'order_date' => now(),
            'status' => PurchaseOrder::STATUS_CONFIRMED,
            'subtotal' => 150000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 150000,
        ]);

        $po->items()->create([
            'product_id' => $this->product->id,
            'item_name' => 'Produk A Standard',
            'quantity' => 2,
            'unit_id' => $this->pcs->id,
            'unit_price' => 75000,
            'cost_price_snapshot' => 50000,
            'subtotal' => 150000,
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->post("/purchase-orders/{$po->id}/generate-invoice");

        $response->assertRedirect();

        // PO should be marked as fully_invoiced
        $this->assertEquals(PurchaseOrder::STATUS_FULLY_INVOICED, $po->fresh()->status);

        // Invoice should be created with correct customer and amounts
        $invoice = Invoice::where('purchase_order_id', $po->id)->firstOrFail();
        $this->assertEquals($this->customer->id, $invoice->customer_id);
        $this->assertEquals(150000, (float) $invoice->total_amount);
        $this->assertEquals(100000, (float) $invoice->total_hpp_cost); // 2 * 50k
        $this->assertEquals(50000, (float) $invoice->total_gross_profit); // 150k - 100k
        $this->assertCount(1, $invoice->items);
    }
}
