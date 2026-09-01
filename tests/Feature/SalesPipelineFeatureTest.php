<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Sales\SalesPipelineService;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SalesPipelineFeatureTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private Customer $customer;
    private Product $product;
    private SalesPipelineService $pipelineService;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name' => 'Owner Bakery',
            'email' => 'owner_bakery@example.com',
            'password' => 'password123',
        ]);

        $this->business = Business::create([
            'name' => 'Cooca Bakery',
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'PT Katering Berkah',
            'phone' => '081299988877',
        ]);

        $unit = Unit::create([
            'business_id' => $this->business->id,
            'code' => 'PCS',
            'name' => 'Pieces',
            'symbol' => 'pcs',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);

        $cat = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Roti & Pastry',
            'slug' => 'roti-pastry',
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $cat->id,
            'output_unit_id' => $unit->id,
            'code' => 'ROT-01',
            'name' => 'Roti Manis Cokelat',
            'selling_price' => 15000,
            'base_cost' => 6500,
            'is_active' => true,
        ]);

        $this->pipelineService = new SalesPipelineService();
    }

    public function test_can_create_quotation(): void
    {
        $this->actingAs($this->user);
        session(['active_business_id' => $this->business->id]);

        $quotation = $this->pipelineService->createQuotation($this->business, [
            'customer_id' => $this->customer->id,
            'date' => '2026-08-29',
            'discount_amount' => 5000,
            'tax_amount' => 0,
            'notes' => 'Penawaran 100 pcs roti manis',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'unit_price' => 15000,
                    'quantity' => 10,
                    'discount_amount' => 0,
                ],
            ],
        ]);

        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'business_id' => $this->business->id,
            'total_amount' => 145000,
            'status' => Quotation::STATUS_SENT,
        ]);

        $this->assertDatabaseHas('quotation_items', [
            'quotation_id' => $quotation->id,
            'product_name' => 'Roti Manis Cokelat',
            'quantity' => 10,
        ]);
    }

    public function test_can_convert_quotation_to_sales_order_in_one_click(): void
    {
        $this->actingAs($this->user);
        session(['active_business_id' => $this->business->id]);

        $quotation = $this->pipelineService->createQuotation($this->business, [
            'customer_id' => $this->customer->id,
            'date' => '2026-08-29',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'unit_price' => 15000,
                    'quantity' => 20,
                ],
            ],
        ]);

        $salesOrder = $this->pipelineService->convertQuotationToSalesOrder($quotation);

        $this->assertDatabaseHas('sales_orders', [
            'id' => $salesOrder->id,
            'quotation_id' => $quotation->id,
            'total_amount' => 300000,
            'status' => SalesOrder::STATUS_CONFIRMED,
        ]);

        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'status' => Quotation::STATUS_ACCEPTED,
        ]);

        $this->assertCount(1, $salesOrder->items);
        $this->assertEquals('Roti Manis Cokelat', $salesOrder->items->first()->product_name);
    }

    public function test_can_generate_invoice_from_sales_order(): void
    {
        $this->actingAs($this->user);
        session(['active_business_id' => $this->business->id]);

        $salesOrder = $this->pipelineService->createSalesOrder($this->business, [
            'customer_id' => $this->customer->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'unit_price' => 15000,
                    'quantity' => 10,
                ],
            ],
        ]);

        $invoice = $this->pipelineService->generateInvoiceFromSalesOrder($salesOrder);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'sales_order_id' => $salesOrder->id,
            'total_amount' => 150000,
            'total_hpp_cost' => 65000,
            'total_gross_profit' => 85000,
        ]);

        $salesOrder->refresh();
        $this->assertEquals(SalesOrder::STATUS_FULFILLED, $salesOrder->status);
    }

    public function test_quotations_and_orders_web_interface_is_accessible(): void
    {
        $this->actingAs($this->user);
        session(['active_business_id' => $this->business->id]);

        $responseIndex = $this->get(route('sales.quotations.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Penawaran Harga');

        $responseCreate = $this->get(route('sales.quotations.create'));
        $responseCreate->assertStatus(200);
        $responseCreate->assertSee('Buat Penawaran Harga Baru');

        $responseOrders = $this->get(route('sales.orders.index'));
        $responseOrders->assertStatus(200);
        $responseOrders->assertSee('Pesanan Penjualan');
    }
}
