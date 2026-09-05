<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Verifikasi prinsip utama snapshot harga:
 * "Master price adalah harga yang berlaku saat ini, sedangkan transaction
 * snapshot adalah harga historis yang benar-benar digunakan pada saat transaksi."
 */
final class SnapshotPriceReportingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;
    private Location $location;
    private Unit $unit;
    private Product $product;
    private Supplier $supplier;
    private Customer $customer;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);

        $this->user = User::create([
            'name' => 'Owner Snapshot',
            'email' => 'snapshot@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->business = Business::create([
            'name' => 'PT Snapshot Harga',
            'currency' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
            'pos_enable_tax' => false,
        ]);

        $this->business->users()->attach($this->user->id, ['id' => (string) Str::uuid(), 'role' => 'owner', 'role_id' => null]);
        $this->user->update(['active_business_id' => $this->business->id]);

        $this->token = $this->user->createToken('snapshot-test')->plainTextToken;

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Pusat',
            'type' => 'outlet',
            'is_active' => true,
        ]);

        $this->unit = Unit::where('code', 'pcs')->firstOrFail();

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Produk Snapshot',
            'code' => 'SNAP-001',
            'output_unit_id' => $this->unit->id,
            'base_cost' => 60000,
            'selling_price' => 100000,
            'is_active' => true,
        ]);

        $this->supplier = Supplier::create([
            'business_id' => $this->business->id,
            'name' => 'Supplier Snapshot Awal',
        ]);

        $this->customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Customer Snapshot',
            'is_active' => true,
        ]);

        app(\App\Domain\Inventory\StockService::class)->recordMovement(
            businessId: $this->business->id,
            locationId: $this->location->id,
            productId: $this->product->id,
            movementType: 'initial',
            quantityChange: 100,
            unitCost: 60000,
            notes: 'Initial stock setup for test'
        );
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->token,
            'X-Business-Id' => $this->business->id,
            'Accept' => 'application/json',
        ];
    }

    public function test_pos_transaction_snapshot_stays_immutable_when_master_price_changes(): void
    {
        // 1. Transaksi POS: qty 2 x Rp100.000, HPP Rp60.000
        $date = now()->toDateString();

        $this->withHeaders($this->authHeaders())
            ->postJson('/api/v1/pos/terminal/checkout', [
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'product_name' => $this->product->name,
                        'unit_price' => 100000,
                        'quantity' => 2,
                    ],
                ],
                'payments' => [
                    ['payment_method' => 'cash', 'amount' => 200000],
                ],
                'location_id' => $this->location->id,
            ])
            ->assertCreated();

        $order = PosOrder::where('business_id', $this->business->id)->firstOrFail();
        $item = $order->items()->firstOrFail();

        $this->assertEquals(100000, (float) $item->unit_price);
        $this->assertEquals(60000, (float) $item->unit_cost_hpp);
        $this->assertEquals(2, (float) $item->quantity);
        $this->assertEquals(200000, (float) $item->subtotal);
        $this->assertEquals(200000, (float) $item->total_price);
        $this->assertEquals(120000, (float) $item->total_hpp);

        // 2. Ubah harga master produk (modal 70.000, jual 110.000)
        $this->product->update(['base_cost' => 70000, 'selling_price' => 110000]);

        // 3. Snapshot transaksi lama TIDAK berubah
        $item->refresh();
        $this->assertEquals(100000, (float) $item->unit_price);
        $this->assertEquals(60000, (float) $item->unit_cost_hpp);
        $this->assertEquals(120000, (float) $item->total_hpp);

        // 4. Reporting penjualan tetap memakai snapshot lama
        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/pos/reports/sales-summary?start_date=' . $date . '&end_date=' . $date);


        $response->assertOk();
        $response->assertJsonPath('summary.average_selling_price', 100000);
        $response->assertJsonPath('summary.average_cost_price', 60000);
        $response->assertJsonPath('summary.total_sales', 200000);
        $response->assertJsonPath('summary.total_hpp', 120000);
        $response->assertJsonPath('summary.total_gross_profit', 80000);
        $response->assertJsonPath('summary.margin_percentage', 40);
        $response->assertJsonPath('summary.source', 'transaction_snapshot');
    }

    public function test_invoice_snapshot_stays_immutable_when_master_price_changes(): void
    {
        $date = now()->toDateString();

        $this->withHeaders($this->authHeaders())
            ->postJson('/api/v1/sales/invoices', [
                'customer_id' => $this->customer->id,
                'invoice_date' => $date,
                'status' => 'unpaid',
                'discount_type' => 'fixed',
                'discount_value' => 0,
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'item_name' => $this->product->name,
                        'quantity' => 2,
                        'unit_id' => $this->unit->id,
                        'unit_price' => 100000,
                        'unit_hpp' => 60000,
                    ],
                ],
            ])
            ->assertCreated();

        $invoice = Invoice::where('business_id', $this->business->id)->firstOrFail();
        app(\App\Domain\Commerce\InvoiceService::class)->confirmAndRelease($invoice, $this->location->id);
        $item = $invoice->items()->firstOrFail();

        $this->assertEquals(100000, (float) $item->unit_price);
        $this->assertEquals(60000, (float) $item->unit_hpp);
        $this->assertEquals(200000, (float) $item->subtotal);
        $this->assertEquals(120000, (float) $item->total_hpp);

        // Ubah harga master; snapshot tidak berubah
        $this->product->update(['base_cost' => 70000, 'selling_price' => 110000]);

        $item->refresh();
        $this->assertEquals(100000, (float) $item->unit_price);
        $this->assertEquals(60000, (float) $item->unit_hpp);
        $this->assertEquals(120000, (float) $item->total_hpp);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/reports/sales-summary?start_date=' . $date . '&end_date=' . $date);

        $response->assertOk();
        $response->assertJsonPath('summary.average_selling_price', 100000);
        $response->assertJsonPath('summary.average_cost_price', 60000);
        $response->assertJsonPath('summary.total_sales', 200000);
        $response->assertJsonPath('summary.total_hpp', 120000);
    }

    public function test_purchase_order_buy_price_snapshot_is_immutable(): void
    {
        $date = now()->toDateString();

        $this->withHeaders($this->authHeaders())
            ->postJson('/api/v1/purchasing/purchase-orders', [
                'po_type' => 'supplier',
                'supplier_id' => $this->supplier->id,
                'order_date' => $date,
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'item_name' => $this->product->name,
                        'quantity' => 10,
                        'unit_id' => $this->unit->id,
                        'unit_price' => 50000,
                    ],
                ],
            ])
            ->assertCreated();

        $po = PurchaseOrder::where('business_id', $this->business->id)->where('po_type', 'supplier')->firstOrFail();
        $poItem = $po->items()->firstOrFail();

        $this->assertEquals(50000, (float) $poItem->purchase_price_snapshot);
        $this->assertEquals(50000, (float) $poItem->unit_price);
        $this->assertEquals(500000, (float) $poItem->subtotal);
        $this->assertEquals('Supplier Snapshot Awal', $poItem->supplier_name_snapshot);

        // Ubah harga master produk & nama supplier
        $this->product->update(['base_cost' => 70000, 'selling_price' => 110000]);
        $this->supplier->update(['name' => 'Supplier Nama Baru']);

        // Snapshot PO tidak berubah
        $poItem->refresh();
        $this->assertEquals(50000, (float) $poItem->purchase_price_snapshot);
        $this->assertEquals(50000, (float) $poItem->unit_price);
        $this->assertEquals('Supplier Snapshot Awal', $poItem->supplier_name_snapshot);
    }

    public function test_purchase_report_computes_weighted_average_and_history_from_snapshots(): void
    {
        $date = now()->toDateString();

        // PO 1: 10 pcs x 50.000 = 500.000
        $this->withHeaders($this->authHeaders())
            ->postJson('/api/v1/purchasing/purchase-orders', [
                'po_type' => 'supplier',
                'supplier_id' => $this->supplier->id,
                'order_date' => $date,
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'item_name' => $this->product->name,
                        'quantity' => 10,
                        'unit_id' => $this->unit->id,
                        'unit_price' => 50000,
                    ],
                ],
            ])
            ->assertCreated();

        // PO 2: 20 pcs x 55.000 = 1.100.000
        $this->withHeaders($this->authHeaders())
            ->postJson('/api/v1/purchasing/purchase-orders', [
                'po_type' => 'supplier',
                'supplier_id' => $this->supplier->id,
                'order_date' => $date,
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'item_name' => $this->product->name,
                        'quantity' => 20,
                        'unit_id' => $this->unit->id,
                        'unit_price' => 55000,
                    ],
                ],
            ])
            ->assertCreated();

        // Konfirmasi kedua PO agar masuk reporting (draft tidak dihitung)
        PurchaseOrder::where('business_id', $this->business->id)->where('po_type', 'supplier')->get()
            ->each(function (PurchaseOrder $po): void {
                $po->update(['status' => PurchaseOrder::STATUS_CONFIRMED]);
            });

        // Ubah harga master, seharusnya tidak mempengaruhi reporting pembelian
        $this->product->update(['base_cost' => 90000, 'selling_price' => 150000]);

        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/purchasing/reports/summary?start_date=' . $monthStart . '&end_date=' . $monthEnd);

        $response->assertOk();
        $response->assertJsonPath('summary.total_quantity', 30);
        $response->assertJsonPath('summary.total_value', 1600000);
        $this->assertEqualsWithDelta(53333.33, (float) data_get($response->json(), 'summary.average_buy_price'), 0.01);
        $response->assertJsonPath('summary.min_buy_price', 50000);
        $response->assertJsonPath('summary.max_buy_price', 55000);
        $response->assertJsonPath('summary.total_transactions', 2);
        $response->assertJsonPath('summary.source', 'purchase_order_snapshot');

        // Breakdown per produk
        $this->assertEquals(53333.33, (float) data_get($response->json(), 'by_product.0.average_buy_price'));
        $this->assertEquals(30, (float) data_get($response->json(), 'by_product.0.total_quantity'));
        $this->assertEquals(1600000, (float) data_get($response->json(), 'by_product.0.total_value'));

        // Breakdown per supplier
        $this->assertEquals('Supplier Snapshot Awal', data_get($response->json(), 'by_supplier.0.supplier_name'));
        $this->assertEquals(1600000, (float) data_get($response->json(), 'by_supplier.0.total_value'));

        // Breakdown per periode
        $this->assertEquals(30, (float) data_get($response->json(), 'by_period.0.total_quantity'));
        $this->assertEquals(now()->format('Y-m'), data_get($response->json(), 'by_period.0.period'));

        // Riwayat perubahan harga beli (chronological, 2 entri)
        $historyResponse = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/purchasing/reports/history?start_date=' . $monthStart . '&end_date=' . $monthEnd);

        $historyResponse->assertOk();
        $this->assertCount(2, $historyResponse->json('history'));
        $this->assertEquals(50000, (float) data_get($historyResponse->json(), 'history.0.buy_price'));
        $this->assertEquals(55000, (float) data_get($historyResponse->json(), 'history.1.buy_price'));
    }

    public function test_dashboard_exposes_snapshot_based_summary_and_pl(): void
    {
        $date = now()->toDateString();

        $this->withHeaders($this->authHeaders())
            ->postJson('/api/v1/pos/terminal/checkout', [
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'product_name' => $this->product->name,
                        'unit_price' => 100000,
                        'quantity' => 2,
                    ],
                ],
                'payments' => [
                    ['payment_method' => 'cash', 'amount' => 200000],
                ],
                'location_id' => $this->location->id,
            ])
            ->assertCreated();

        $this->product->update(['base_cost' => 70000, 'selling_price' => 110000]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/dashboard?period=month');

        $response->assertOk();
        $response->assertJsonPath('summary.total_sales', 200000);
        $response->assertJsonPath('summary.total_hpp', 120000);
        $response->assertJsonPath('summary.average_selling_price', 100000);
        $response->assertJsonPath('summary.average_cost_price', 60000);
        $response->assertJsonPath('pl.total_cogs', 120000);
        $response->assertJsonPath('pl.total_revenue', 200000);
        $response->assertJsonPath('pl.source', 'transaction_snapshot');
    }
}