<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\InventoryStock;
use App\Models\JournalEntry;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosRegister;
use App\Models\PosShift;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PosTerminalFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;
    private Location $location;
    private Product $product;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name' => 'Kasir Utama',
            'email' => 'kasir@example.com',
            'password' => 'password123',
        ]);

        $this->business = Business::create([
            'name' => 'Kafe HPP Mantap',
            'pos_enable_tax' => true,
            'pos_tax_percent' => 10.0,
            'pos_enable_service_charge' => false,
            'pos_service_charge_percent' => 0.0,
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Sudirman',
            'type' => 'outlet',
            'is_active' => true,
        ]);

        $unit = Unit::create([
            'business_id' => $this->business->id,
            'code' => 'PCS',
            'name' => 'Pieces',
            'symbol' => 'pcs',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);

        $category = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Minuman Kopi',
            'slug' => 'minuman-kopi',
        ]);

        // Product with HPP Rp15.000 and Selling Price Rp35.000
        $this->product = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $category->id,
            'output_unit_id' => $unit->id,
            'code' => 'KOP-001',
            'name' => 'Kopi Susu Gula Aren',
            'slug' => 'kopi-susu-gula-aren',
            'base_cost' => 15000,
            'selling_price' => 35000,
            'is_active' => true,
        ]);

        // Initial inventory stock: 50 pcs
        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
            'last_cost' => 15000,
        ]);

        // Customer with Bronze membership
        $this->customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'membership_tier' => 'bronze',
            'points_balance' => 100, // 100 points = Rp10.000
        ]);
    }

    public function test_cashier_can_open_and_close_shift_with_reconciliation(): void
    {
        $this->actingAs($this->user);

        // 1. Buka shift kasir dengan modal awal Rp100.000
        $openResp = $this->postJson(route('pos.shifts.open'), [
            'location_id' => $this->location->id,
            'opening_cash' => 100000,
            'notes' => 'Shift Pagi',
        ]);

        $openResp->assertStatus(200);
        $shift = PosShift::where('user_id', $this->user->id)->where('status', 'open')->first();
        $this->assertNotNull($shift);
        $this->assertEquals(100000, $shift->opening_cash);

        // 2. Tambah kas masuk (misal Rp50.000 uang pecahan)
        $cashInResp = $this->postJson(route('pos.shifts.cash-movement', $shift->id), [
            'type' => 'cash_in',
            'amount' => 50000,
            'reason' => 'Tukar uang receh',
        ]);
        $cashInResp->assertStatus(200);

        // Expected cash should now be 100.000 + 50.000 = 150.000
        $summaryResp = $this->getJson(route('pos.shifts.summary', $shift->id));
        $summaryResp->assertStatus(200);
        $summaryResp->assertJsonPath('summary.expected_cash', 150000);

        // 3. Tutup shift dengan hitungan fisik Rp150.000 (selisih 0)
        $closeResp = $this->postJson(route('pos.shifts.close', $shift->id), [
            'closing_cash_actual' => 150000,
        ]);
        $closeResp->assertStatus(200);

        $shift->refresh();
        $this->assertEquals('closed', $shift->status);
        $this->assertEquals(0, $shift->cash_difference);
    }

    public function test_pos_checkout_calculates_hpp_decrements_stock_and_creates_journals(): void
    {
        $this->actingAs($this->user);

        // Buka shift terlebih dahulu
        $this->postJson(route('pos.shifts.open'), [
            'location_id' => $this->location->id,
            'opening_cash' => 100000,
        ]);

        // Checkout 2 cups of Kopi Susu
        // Subtotal = 2 * 35.000 = 70.000
        // PPN 10% = 7.000
        // Grand Total = 77.000
        // HPP = 2 * 15.000 = 30.000
        // Gross Profit = 70.000 - 30.000 = 40.000
        $payload = [
            'location_id' => $this->location->id,
            'customer_id' => $this->customer->id,
            'order_type' => 'dine_in',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'quantity' => 2,
                    'unit_price' => 35000,
                ],
            ],
            'payments' => [
                [
                    'payment_method' => 'cash',
                    'amount' => 100000, // Cash paid 100.000
                ],
            ],
        ];

        $resp = $this->postJson(route('pos.checkout'), $payload);

        $resp->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('order.total_amount', 77000)
            ->assertJsonPath('order.change_amount', 23000)
            ->assertJsonPath('order.total_hpp_cost', 30000)
            ->assertJsonPath('order.total_gross_profit', 40000);

        $order = PosOrder::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('completed', $order->status);

        // 1. Verify Inventory Stock decreased from 50 to 48
        $stock = InventoryStock::where('product_id', $this->product->id)->where('location_id', $this->location->id)->first();
        $this->assertEquals(48, $stock->quantity);

        // 2. Verify Customer loyalty points increased (+7 points for Rp77.000)
        $this->customer->refresh();
        $this->assertGreaterThan(100, $this->customer->points_balance);

        // 3. Verify Double-entry Automatic General Ledger Journal
        $journal = JournalEntry::where('reference_type', 'pos_order')->where('reference_id', $order->id)->first();
        $this->assertNotNull($journal);
        $this->assertEquals($journal->total_debit, $journal->total_credit);
        $this->assertGreaterThan(0, $journal->total_debit);
    }

    public function test_pos_checkout_records_percentage_discount_and_configured_tax(): void
    {
        $this->actingAs($this->user);

        $this->postJson(route('pos.shifts.open'), [
            'location_id' => $this->location->id,
            'opening_cash' => 100000,
        ]);

        $response = $this->postJson(route('pos.checkout'), [
            'location_id' => $this->location->id,
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'items' => [[
                'product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'quantity' => 2,
                'unit_price' => 35000,
            ]],
            'payments' => [[
                'payment_method' => 'cash',
                'amount' => 70000,
            ]],
        ]);

        $response->assertOk()->assertJsonPath('order.total_amount', 69300);

        $order = PosOrder::latest()->firstOrFail();
        $this->assertSame('percentage', $order->discount_type);
        $this->assertEquals(10, $order->discount_value);
        $this->assertEquals(7000, $order->discount_amount);
        $this->assertEquals(10, $order->tax_percentage);
        $this->assertEquals(6300, $order->tax_amount);
    }

    public function test_pos_order_can_be_voided_and_stock_is_restored(): void
    {
        $this->actingAs($this->user);

        // Buka shift kasir terlebih dahulu (wajib sebelum transaksi POS)
        $this->postJson(route('pos.shifts.open'), [
            'location_id' => $this->location->id,
            'opening_cash' => 100000,
        ]);

        // Create order
        $payload = [
            'location_id' => $this->location->id,
            'items' => [
                ['product_id' => $this->product->id, 'product_name' => $this->product->name, 'quantity' => 5, 'unit_price' => 35000],
            ],
            'payments' => [
                ['payment_method' => 'cash', 'amount' => 200000],
            ],
        ];

        $this->postJson(route('pos.checkout'), $payload);
        $order = PosOrder::latest()->first();

        // Stock was 50, now 45
        $stock = InventoryStock::where('product_id', $this->product->id)->where('location_id', $this->location->id)->first();
        $this->assertEquals(45, $stock->quantity);

        // Void the order
        $voidResp = $this->post(route('pos.orders.void', $order->id), [
            'reason' => 'Customer batalkan pesanan',
        ]);
        $voidResp->assertRedirect();

        $order->refresh();
        $this->assertEquals('voided', $order->status);

        // Stock should be restored back to 50
        $stock->refresh();
        $this->assertEquals(50, $stock->quantity);
    }

    public function test_inter_location_stock_transfer(): void
    {
        $this->actingAs($this->user);

        $destLocation = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Thamrin',
            'type' => 'outlet',
            'is_active' => true,
        ]);

        // Send 10 pcs from Sudirman to Thamrin
        $transferResp = $this->post(route('inventory.transfers.store'), [
            'source_location_id' => $this->location->id,
            'destination_location_id' => $destLocation->id,
            'transfer_date' => now()->toDateString(),
            'notes' => 'Pengiriman stok sore',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 10],
            ],
        ]);
        $transferResp->assertRedirect();

        // Find transfer
        $transfer = \App\Models\StockTransfer::latest()->first();
        $this->assertNotNull($transfer);
        $this->assertEquals('pending', $transfer->status);

        // Confirm receipt at destination
        $receiveResp = $this->post(route('inventory.transfers.receive', $transfer->id));
        $receiveResp->assertRedirect();

        $transfer->refresh();
        $this->assertEquals('received', $transfer->status);

        // Source stock should now be 50 - 10 = 40
        $sourceStock = InventoryStock::where('product_id', $this->product->id)->where('location_id', $this->location->id)->first();
        $this->assertEquals(40, $sourceStock->quantity);

        // Destination stock should now have 10
        $destStock = InventoryStock::where('product_id', $this->product->id)->where('location_id', $destLocation->id)->first();
        $this->assertNotNull($destStock);
        $this->assertEquals(10, $destStock->quantity);
    }

    public function test_stock_opname_reconciliation(): void
    {
        $this->actingAs($this->user);

        // Current stock is 50. Physical count finds 47 (loss of 3).
        $opnameResp = $this->post(route('inventory.opnames.store'), [
            'location_id' => $this->location->id,
            'opname_date' => now()->toDateString(),
            'notes' => 'Opname Mingguan',
            'items' => [
                ['product_id' => $this->product->id, 'physical_quantity' => 47],
            ],
        ]);
        $opnameResp->assertRedirect();

        $opname = \App\Models\StockOpname::latest()->first();
        $this->assertNotNull($opname);
        $this->assertEquals('in_progress', $opname->status);

        // Reconcile
        $recResp = $this->post(route('inventory.opnames.reconcile', $opname->id));
        $recResp->assertRedirect();

        $opname->refresh();
        $this->assertEquals('reconciled', $opname->status);

        // Stock in database must now be exactly 47
        $stock = InventoryStock::where('product_id', $this->product->id)->where('location_id', $this->location->id)->first();
        $this->assertEquals(47, $stock->quantity);
    }

    public function test_voucher_and_loyalty_point_redemption_on_checkout(): void
    {
        $this->actingAs($this->user);

        // Buka shift kasir terlebih dahulu (wajib sebelum transaksi POS)
        $this->postJson(route('pos.shifts.open'), [
            'location_id' => $this->location->id,
            'opening_cash' => 100000,
        ]);

        // Create a 10% voucher
        $voucher = \App\Models\Voucher::create([
            'business_id' => $this->business->id,
            'code' => 'HEMAT10',
            'name' => 'Diskon 10%',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'min_order_amount' => 10000,
            'is_active' => true,
        ]);

        $payload = [
            'location_id' => $this->location->id,
            'customer_id' => $this->customer->id,
            'voucher_code' => 'HEMAT10',
            'points_to_redeem' => 50, // 50 points = Rp5.000 discount
            'items' => [
                ['product_id' => $this->product->id, 'product_name' => $this->product->name, 'quantity' => 1, 'unit_price' => 50000],
            ],
            'payments' => [
                ['payment_method' => 'cash', 'amount' => 50000],
            ],
        ];

        $resp = $this->postJson(route('pos.checkout'), $payload);
        $resp->assertStatus(200);

        $order = PosOrder::latest()->first();
        $this->assertEquals('HEMAT10', $order->voucher_code);
        $this->assertEquals(5000, $order->voucher_discount_amount); // 10% of 50.000
        $this->assertEquals(5000, $order->points_discount_amount);  // 50 points * 100

        // Verify voucher used_count incremented
        $voucher->refresh();
        $this->assertEquals(1, $voucher->used_count);
    }

    public function test_pos_checkout_is_blocked_when_no_shift_is_open(): void
    {
        $this->actingAs($this->user);

        // Pastikan belum ada shift terbuka
        $this->assertNull(PosShift::where('user_id', $this->user->id)->where('status', 'open')->first());

        $payload = [
            'location_id' => $this->location->id,
            'items' => [
                ['product_id' => $this->product->id, 'product_name' => $this->product->name, 'quantity' => 1, 'unit_price' => 50000],
            ],
            'payments' => [
                ['payment_method' => 'cash', 'amount' => 50000],
            ],
        ];

        $resp = $this->postJson(route('pos.checkout'), $payload);

        // Transaksi harus ditolak selama shift belum dibuka
        $resp->assertStatus(403);
        $this->assertNull(PosOrder::where('business_id', $this->business->id)->first());
    }
}
