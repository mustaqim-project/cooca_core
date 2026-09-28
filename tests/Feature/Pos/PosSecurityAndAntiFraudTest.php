<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use App\Domain\Printer\PrinterIpValidator;
use App\Models\Business;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosPrinter;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PosSecurityAndAntiFraudTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $cashier;
    private Business $business;
    private Location $location;
    private \App\Models\Unit $unit;
    private PosPrinter $printer;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(RbacSeeder::class);

        $ownerRole = Role::where('slug', 'owner')->firstOrFail();
        $cashierRole = Role::where('slug', 'cashier')->firstOrFail();

        $this->owner = User::create([
            'name' => 'Owner Bisnis',
            'email' => 'owner.security@example.com',
            'phone' => '081299990002',
            'password' => 'password123',
        ]);
        $this->owner->forceFill(['email_verified_at' => now()])->save();

        $this->cashier = User::create([
            'name' => 'Kasir Shift 1',
            'email' => 'cashier.security@example.com',
            'phone' => '081299990003',
            'password' => 'password123',
        ]);
        $this->cashier->forceFill(['email_verified_at' => now()])->save();

        $this->business = Business::create([
            'name' => 'Restoran Anti Fraud Nusantara',
            'address' => 'Jl. Sudirman No. 1, Jakarta',
            'phone' => '0215559999',
            'pos_require_pin_for_void' => true,
            'pos_require_pin_for_refund' => true,
            'allow_negative_stock' => true,
            'pos_supervisor_pin' => null, // Default: PIN not set
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole->id,
        ]);

        $this->business->users()->attach($this->cashier->id, [
            'id' => (string) Str::uuid(),
            'role' => 'cashier',
            'role_id' => $cashierRole->id,
        ]);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Utama',
            'code' => 'OUT-01',
            'is_active' => true,
        ]);

        $this->unit = \App\Models\Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Porsi',
            'code' => 'PORSI',
            'category' => \App\Models\Unit::CATEGORY_QUANTITY,
            'is_base' => true,
        ]);

        $this->printer = PosPrinter::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'name' => 'Printer Kasir Utama',
            'connection_type' => PosPrinter::TYPE_LAN,
            'interface_address' => '192.168.1.200',
            'port' => 9100,
            'paper_width' => '80mm',
            'capabilities' => [PosPrinter::CAP_PRINT_TEXT, PosPrinter::CAP_CASH_DRAWER],
            'assigned_usages' => [PosPrinter::USAGE_CASHIER_RECEIPT],
            'is_active' => true,
        ]);
    }

    public function test_printer_ip_validator_blocks_ssrf_and_cloud_metadata(): void
    {
        $this->assertFalse(PrinterIpValidator::isSafe('127.0.0.1'), '127.0.0.1 loopback must be blocked');
        $this->assertFalse(PrinterIpValidator::isSafe('localhost'), 'localhost must be blocked');
        $this->assertFalse(PrinterIpValidator::isSafe('::1'), 'IPv6 ::1 must be blocked');
        $this->assertFalse(PrinterIpValidator::isSafe('169.254.169.254'), 'AWS/GCP metadata endpoint must be blocked');
        $this->assertFalse(PrinterIpValidator::isSafe('169.254.10.20'), '169.254.x.x link-local range must be blocked');
        $this->assertFalse(PrinterIpValidator::isSafe('0.0.0.0'), '0.0.0.0 must be blocked');
        $this->assertFalse(PrinterIpValidator::isSafe('255.255.255.255'), 'Broadcast must be blocked');
        $this->assertFalse(PrinterIpValidator::isSafe('metadata.google.internal'), 'GCP internal metadata domain must be blocked');
        $this->assertFalse(PrinterIpValidator::isSafe(''), 'Empty address must be blocked');
    }

    public function test_printer_ip_validator_allows_valid_lan_ip_and_hostnames(): void
    {
        $this->assertTrue(PrinterIpValidator::isSafe('192.168.1.100'), 'Standard 192.168.x.x LAN IP must be allowed');
        $this->assertTrue(PrinterIpValidator::isSafe('10.0.0.50'), 'Standard 10.x.x.x private LAN IP must be allowed');
        $this->assertTrue(PrinterIpValidator::isSafe('172.16.0.10'), 'Standard 172.16.x.x private LAN IP must be allowed');
        $this->assertTrue(PrinterIpValidator::isSafe('pos-printer.local'), 'Valid LAN hostname must be allowed');
        $this->assertTrue(PrinterIpValidator::isSafe('epson-kitchen.lan'), 'Valid .lan hostname must be allowed');
    }

    public function test_printer_store_rejects_ssrf_interface_address_for_lan_printer(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('pos.printers.store'), [
                'name' => 'Hacker Printer',
                'location_id' => $this->location->id,
                'connection_type' => 'lan',
                'interface_address' => '169.254.169.254',
                'port' => 9100,
                'paper_width' => '80mm',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['interface_address']);
        $this->assertDatabaseMissing('pos_printers', ['name' => 'Hacker Printer']);
    }

    public function test_printer_store_accepts_valid_lan_ip(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('pos.printers.store'), [
                'name' => 'Printer Dapur Valid',
                'location_id' => $this->location->id,
                'connection_type' => 'lan',
                'interface_address' => '192.168.1.150',
                'port' => 9100,
                'paper_width' => '80mm',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('pos_printers', [
            'name' => 'Printer Dapur Valid',
            'interface_address' => '192.168.1.150',
        ]);
    }

    public function test_printer_update_rejects_ssrf_interface_address(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->putJson(route('pos.printers.update', $this->printer->id), [
                'name' => 'Printer Kasir Diubah',
                'location_id' => $this->location->id,
                'connection_type' => 'lan',
                'interface_address' => '127.0.0.1',
                'port' => 9100,
                'paper_width' => '80mm',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['interface_address']);
        $this->assertDatabaseMissing('pos_printers', ['interface_address' => '127.0.0.1']);
    }

    public function test_manual_drawer_pop_rejects_when_supervisor_pin_is_unset(): void
    {
        $this->assertNull($this->business->pos_supervisor_pin);

        $response = $this->actingAs($this->cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('pos.cash-drawer.manual-pop'), [
                'printer_id' => $this->printer->id,
                'supervisor_pin' => '1234', // Insecure legacy PIN should fail!
                'reason' => 'Buka laci tanpa izin',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'PIN Supervisor belum diatur oleh pemilik bisnis. Silakan atur PIN di Pengaturan Bisnis terlebih dahulu.',
        ]);
    }

    public function test_manual_drawer_pop_authorizes_with_valid_pin_and_rejects_invalid_pin(): void
    {
        // Set PIN 889900 in business
        $this->business->update([
            'pos_supervisor_pin' => Hash::make('889900'),
        ]);

        // Wrong PIN 1234
        $responseWrong = $this->actingAs($this->cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('pos.cash-drawer.manual-pop'), [
                'printer_id' => $this->printer->id,
                'supervisor_pin' => '1234',
                'reason' => 'Buka laci',
            ]);

        $responseWrong->assertStatus(422);
        $responseWrong->assertJsonFragment([
            'success' => false,
            'message' => 'PIN Supervisor salah. Otorisasi pembukaan laci kas ditolak.',
        ]);

        // Correct PIN 889900
        $responseCorrect = $this->actingAs($this->cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('pos.cash-drawer.manual-pop'), [
                'printer_id' => $this->printer->id,
                'supervisor_pin' => '889900',
                'reason' => 'Buka laci tukar uang receh',
            ]);

        // Connection to LAN printer might be offline in test sandbox, but domain authorization passed
        $responseCorrect->assertStatus(200);
    }

    public function test_void_and_refund_reject_when_supervisor_pin_is_unset_and_no_fallback_1234(): void
    {
        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'order_number' => 'ORD-SEC-001',
            'order_date' => now()->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 50000,
            'total_amount' => 50000,
            'paid_amount' => 50000,
        ]);

        // When supervisor PIN is null, attempting void with PIN 1234 must fail
        $response = $this->actingAs($this->cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('pos.orders.void', $order->id), [
                'pin' => '1234',
                'reason' => 'Kasir coba void tanpa izin',
            ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'PIN Supervisor salah atau otorisasi tidak valid.',
        ]);
        $this->assertEquals(PosOrder::STATUS_COMPLETED, $order->fresh()->status);
    }

    public function test_checkout_enforces_server_side_pricing_and_ignores_tampered_unit_price(): void
    {
        $product = \App\Models\Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Bebek Goreng Sambal Ijo',
            'code' => 'BEBEK-01',
            'type' => \App\Models\Product::TYPE_GOODS,
            'selling_price' => 75000,
            'base_cost' => 35000,
            'is_active' => true,
        ]);

        $shift = \App\Models\PosShift::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'shift_number' => 'SFT-TEST-001',
            'opening_cash' => 100000,
            'opened_at' => now(),
            'status' => \App\Models\PosShift::STATUS_OPEN,
        ]);

        // Hacker attempts to send unit_price = 1000 instead of 75000
        $response = $this->actingAs($this->cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('pos.checkout'), [
                'location_id' => $this->location->id,
                'items' => [
                    [
                        'product_id' => $product->id,
                        'product_name' => 'Bebek Goreng Sambal Ijo',
                        'unit_price' => 1000, // Tampered price
                        'quantity' => 2,
                    ],
                ],
                'payments' => [
                    [
                        'payment_method' => 'cash',
                        'amount' => 150000,
                    ],
                ],
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $createdOrder = PosOrder::where('business_id', $this->business->id)->latest()->first();
        $this->assertNotNull($createdOrder);

        // Server MUST calculate 2 * 75.000 = 150.000, NOT 2.000!
        $this->assertEquals(150000.0, (float) $createdOrder->subtotal);
        $this->assertEquals(150000.0, (float) $createdOrder->total_amount);

        $orderItem = $createdOrder->items()->first();
        $this->assertNotNull($orderItem);
        $this->assertEquals(75000.0, (float) $orderItem->unit_price);
        $this->assertEquals(150000.0, (float) $orderItem->total_price);
    }

    public function test_checkout_enforces_channel_pricing_on_online_delivery_channel(): void
    {
        $product = \App\Models\Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Paket Ayam Geprek',
            'code' => 'AYAM-01',
            'type' => \App\Models\Product::TYPE_GOODS,
            'selling_price' => 30000, // Dine in price
            'base_cost' => 15000,
            'is_active' => true,
        ]);

        \App\Models\ProductChannelPrice::create([
            'business_id' => $this->business->id,
            'product_id' => $product->id,
            'channel' => 'gofood',
            'price' => 38000, // Higher GoFood price
        ]);

        $shift = \App\Models\PosShift::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'shift_number' => 'SFT-TEST-002',
            'opening_cash' => 100000,
            'opened_at' => now(),
            'status' => \App\Models\PosShift::STATUS_OPEN,
        ]);

        // Checkout GoFood channel with fake client unit_price 10.000
        $response = $this->actingAs($this->cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('pos.checkout'), [
                'location_id' => $this->location->id,
                'sales_channel' => 'gofood',
                'external_order_ref' => 'GF-99881',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'product_name' => 'Paket Ayam Geprek',
                        'unit_price' => 10000, // Tampered price
                        'quantity' => 3,
                    ],
                ],
                'payments' => [
                    [
                        'payment_method' => 'qris',
                        'amount' => 114000,
                    ],
                ],
            ]);

        $response->assertStatus(200);

        $createdOrder = PosOrder::where('business_id', $this->business->id)->latest()->first();
        // Server MUST calculate 3 * 38.000 = 114.000
        $this->assertEquals(114000.0, (float) $createdOrder->subtotal);
        $this->assertEquals(114000.0, (float) $createdOrder->total_amount);
        $this->assertEquals(38000.0, (float) $createdOrder->items()->first()->unit_price);
    }

    public function test_pos_validate_voucher_endpoint_validates_quota_min_spend_and_calculates_discount(): void
    {
        $voucher = \App\Models\Voucher::create([
            'business_id' => $this->business->id,
            'code' => 'PROMO50',
            'name' => 'Diskon 50% Grand Opening',
            'discount_type' => \App\Models\Voucher::TYPE_PERCENTAGE,
            'discount_value' => 50,
            'min_order_amount' => 50000,
            'max_discount_amount' => 30000,
            'usage_limit' => 1,
            'used_count' => 0,
            'is_active' => true,
        ]);

        // 1. Minimum spend not met (subtotal 40.000 < min 50.000)
        $resMinSpend = $this->actingAs($this->cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('pos.validate-voucher'), [
                'code' => 'PROMO50',
                'subtotal' => 40000,
            ]);
        $resMinSpend->assertStatus(422);
        $resMinSpend->assertJsonFragment(['valid' => false]);

        // 2. Wrong code
        $resWrong = $this->actingAs($this->cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('pos.validate-voucher'), [
                'code' => 'INVALID_CODE',
                'subtotal' => 100000,
            ]);
        $resWrong->assertStatus(422);
        $resWrong->assertJsonFragment(['valid' => false]);

        // 3. Valid voucher with subtotal 100.000 (50% = 50.000 capped at max_discount 30.000)
        $resSuccess = $this->actingAs($this->cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('pos.validate-voucher'), [
                'code' => 'PROMO50',
                'subtotal' => 100000,
            ]);
        $resSuccess->assertStatus(200);
        $resSuccess->assertJson([
            'success' => true,
            'valid' => true,
            'discount_amount' => 30000,
        ]);

        // 4. Quota exceeded
        $voucher->update(['used_count' => 1]);
        $resQuota = $this->actingAs($this->cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('pos.validate-voucher'), [
                'code' => 'PROMO50',
                'subtotal' => 100000,
            ]);
        $resQuota->assertStatus(422);
        $resQuota->assertJsonFragment(['message' => 'Kuota penggunaan voucher ini sudah habis.']);
    }

    public function test_blind_cash_count_masks_expected_cash_from_cashier_summary_api(): void
    {
        $shift = \App\Models\PosShift::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'shift_number' => 'SFT-TEST-003',
            'opening_cash' => 200000,
            'opened_at' => now(),
            'status' => \App\Models\PosShift::STATUS_OPEN,
        ]);

        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'pos_shift_id' => $shift->id,
            'user_id' => $this->cashier->id,
            'order_number' => 'ORD-TEST-003',
            'order_date' => now()->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 150000,
            'total_amount' => 150000,
            'paid_amount' => 150000,
        ]);

        \App\Models\PosOrderPayment::create([
            'pos_order_id' => $order->id,
            'payment_method' => 'cash',
            'amount' => 150000,
        ]);

        // Regular cashier request -> expected_cash and cash_difference MUST be masked as NULL
        $cashierRes = $this->actingAs($this->cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('pos.shifts.summary', $shift->id));

        $cashierRes->assertStatus(200);
        $cashierRes->assertJson([
            'success' => true,
            'is_blind_count' => true,
            'summary' => [
                'expected_cash' => null,
                'cash_difference' => null,
            ],
        ]);

        // Owner request -> expected_cash MUST be visible (200.000 + 150.000 = 350.000)
        $ownerRes = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('pos.shifts.summary', $shift->id));

        $ownerRes->assertStatus(200);
        $ownerRes->assertJson([
            'success' => true,
            'is_blind_count' => false,
            'summary' => [
                'expected_cash' => 350000,
            ],
        ]);
    }

    public function test_qr_order_automatically_creates_or_links_customer_and_sets_customer_id_on_order(): void
    {
        $table = \App\Models\PosTable::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'table_number' => '05',
            'name' => 'Meja 5',
            'capacity' => 4,
            'status' => \App\Models\PosTable::STATUS_AVAILABLE,
            'qr_token' => 'qr-test-token-fase4-001',
            'is_active' => true,
        ]);

        $product = \App\Models\Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Nasi Goreng Spesial',
            'code' => 'PRD-NASGOR-001',
            'type' => \App\Models\Product::TYPE_GOODS,
            'selling_price' => 35000,
            'base_cost' => 15000,
            'is_active' => true,
            'show_in_pos' => true,
        ]);

        $payload = [
            'customer_name' => 'Siti Nurhaliza',
            'customer_phone' => '081298765432',
            'payment_mode' => 'pay_at_cashier',
            'notes' => 'Tolong pedas sedang',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'notes' => 'Tidak pakai daun bawang',
                ],
            ],
        ];

        $res = $this->postJson(route('public.qr.order', $table->qr_token), $payload);

        $res->assertStatus(200);
        $res->assertJson([
            'success' => true,
            'order' => [
                'customer_name' => 'Siti Nurhaliza',
            ],
        ]);

        // Assert Customer created in CRM database
        $this->assertDatabaseHas('customers', [
            'business_id' => $this->business->id,
            'name' => 'Siti Nurhaliza',
            'phone' => '081298765432',
        ]);

        $customer = \App\Models\Customer::where('business_id', $this->business->id)
            ->where('phone', '081298765432')
            ->firstOrFail();

        // Assert PosOrder has customer_id linked
        $order = PosOrder::where('business_id', $this->business->id)
            ->where('pos_table_id', $table->id)
            ->firstOrFail();

        $this->assertSame($customer->id, $order->customer_id);
        $this->assertSame('Siti Nurhaliza', $order->customer->name);
        $this->assertSame('081298765432', $order->customer->phone);
    }

    public function test_qr_order_payment_and_completion_updates_customer_crm_lifetime_stats_and_loyalty(): void
    {
        $table = \App\Models\PosTable::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'table_number' => '06',
            'name' => 'Meja 6',
            'capacity' => 4,
            'status' => \App\Models\PosTable::STATUS_AVAILABLE,
            'qr_token' => 'qr-test-token-fase4-002',
            'is_active' => true,
        ]);

        $product = \App\Models\Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Kopi Latte Gula Aren',
            'code' => 'PRD-LATTE-001',
            'type' => \App\Models\Product::TYPE_GOODS,
            'selling_price' => 25000,
            'base_cost' => 8000,
            'is_active' => true,
            'show_in_pos' => true,
        ]);

        $orderService = app(\App\Domain\Pos\PosOrderService::class);

        $order = $orderService->createQrOrder(
            table: $table,
            customerName: 'Ahmad Dahlan',
            customerPhone: '085712345678',
            itemsData: [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'notes' => 'Less sugar',
                ],
            ],
            orderNotes: 'Dine in santai'
        );

        $this->assertNotNull($order->customer_id);

        $customer = \App\Models\Customer::findOrFail($order->customer_id);
        $this->assertSame(0, (int) $customer->total_orders_count);
        $this->assertSame(0, (int) $customer->total_spent);

        // Mark as paid via QRIS/Gateway with confirmed status
        $order->update([
            'status' => PosOrder::STATUS_CONFIRMED,
            'paid_amount' => 50000,
        ]);

        // Complete the QR order as paid
        $completedOrder = $orderService->completePaidQrOrder($order, $this->cashier);

        $this->assertSame(PosOrder::STATUS_COMPLETED, $completedOrder->status);

        $customer->refresh();
        $this->assertSame(1, (int) $customer->total_orders_count);
        $this->assertEquals(50000, (float) $customer->total_spent);
        $this->assertGreaterThanOrEqual(0, (int) $customer->points_balance);
    }
}

