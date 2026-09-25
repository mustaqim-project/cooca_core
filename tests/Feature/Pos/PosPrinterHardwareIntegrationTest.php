<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use App\Domain\Printer\CashDrawerService;
use App\Domain\Printer\EscposFormatter;
use App\Domain\Printer\KitchenRoutingService;
use App\Models\Business;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\PosPrinter;
use App\Models\PosPrintJob;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PosPrinterHardwareIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $businessA;
    private Business $businessB;
    private Location $locationA;
    private Location $locationB;
    private PosOrder $cashOrder;
    private PosOrder $transferOrder;
    private ProductCategory $foodCategory;
    private ProductCategory $drinkCategory;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        // 1. Setup User
        $this->owner = User::create([
            'name' => 'Owner Utama',
            'email' => 'owner.pos@example.com',
            'phone' => '081299990001',
            'password' => 'password123',
        ]);
        $this->owner->forceFill(['email_verified_at' => now()])->save();

        // 2. Setup Business A
        $this->businessA = Business::create([
            'name' => 'Resto Sedap Nusantara A',
            'address' => 'Jl. Merdeka No. 1, Jakarta',
            'phone' => '0215551234',
            'pos_receipt_footer_note' => 'Terima Kasih Atas Kunjungan Anda!',
        ]);
        $this->businessA->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $this->locationA = Location::create([
            'business_id' => $this->businessA->id,
            'name' => 'Outlet Jakarta Pusat',
            'code' => 'JKT-01',
            'is_active' => true,
        ]);

        // 3. Setup Business B (for Tenant Isolation testing)
        $this->businessB = Business::create([
            'name' => 'Kafe Kopi Nusantara B',
            'address' => 'Jl. Braga No. 5, Bandung',
            'phone' => '0229998888',
        ]);

        $this->locationB = Location::create([
            'business_id' => $this->businessB->id,
            'name' => 'Outlet Bandung',
            'code' => 'BDG-01',
            'is_active' => true,
        ]);

        // 4. Setup Categories & Products
        $this->foodCategory = ProductCategory::create([
            'business_id' => $this->businessA->id,
            'name' => 'Makanan Utama',
        ]);

        $this->drinkCategory = ProductCategory::create([
            'business_id' => $this->businessA->id,
            'name' => 'Minuman Segar',
        ]);

        $unit = Unit::create([
            'business_id' => $this->businessA->id,
            'name' => 'Porsi',
            'code' => 'PRS',
            'symbol' => 'prs',
            'category' => 'quantity',
        ]);

        $foodProduct = Product::create([
            'business_id' => $this->businessA->id,
            'category_id' => $this->foodCategory->id,
            'output_unit_id' => $unit->id,
            'name' => 'Nasi Goreng Spesial',
            'code' => 'FOOD-001',
            'selling_price' => 35000,
            'is_active' => true,
        ]);

        $drinkProduct = Product::create([
            'business_id' => $this->businessA->id,
            'category_id' => $this->drinkCategory->id,
            'output_unit_id' => $unit->id,
            'name' => 'Es Teh Manis',
            'code' => 'DRK-001',
            'selling_price' => 8000,
            'is_active' => true,
        ]);

        // 5. Setup Cash Order (Paid)
        $this->cashOrder = PosOrder::create([
            'business_id' => $this->businessA->id,
            'location_id' => $this->locationA->id,
            'user_id' => $this->owner->id,
            'order_number' => 'ORD-2026-001',
            'order_date' => now()->toDateString(),
            'subtotal' => 43000,
            'total_amount' => 43000,
            'paid_amount' => 50000,
            'change_amount' => 7000,
            'status' => PosOrder::STATUS_COMPLETED,
            'payment_channel' => 'cash',
            'print_count' => 1,
        ]);

        PosOrderItem::create([
            'pos_order_id' => $this->cashOrder->id,
            'product_id' => $foodProduct->id,
            'product_name' => 'Nasi Goreng Spesial',
            'quantity' => 1,
            'unit_price' => 35000,
            'subtotal' => 35000,
        ]);

        PosOrderItem::create([
            'pos_order_id' => $this->cashOrder->id,
            'product_id' => $drinkProduct->id,
            'product_name' => 'Es Teh Manis',
            'quantity' => 1,
            'unit_price' => 8000,
            'subtotal' => 8000,
        ]);

        PosOrderPayment::create([
            'pos_order_id' => $this->cashOrder->id,
            'payment_method' => 'cash',
            'amount' => 50000,
        ]);

        // 6. Setup Non-Cash Order (Transfer / QRIS)
        $this->transferOrder = PosOrder::create([
            'business_id' => $this->businessA->id,
            'location_id' => $this->locationA->id,
            'user_id' => $this->owner->id,
            'order_number' => 'ORD-2026-002',
            'order_date' => now()->toDateString(),
            'subtotal' => 35000,
            'total_amount' => 35000,
            'paid_amount' => 35000,
            'change_amount' => 0,
            'status' => PosOrder::STATUS_COMPLETED,
            'payment_channel' => 'transfer',
            'print_count' => 1,
        ]);

        PosOrderPayment::create([
            'pos_order_id' => $this->transferOrder->id,
            'payment_method' => 'transfer',
            'amount' => 35000,
        ]);
    }

    public function test_can_create_and_manage_printers_with_tenant_isolation(): void
    {
        $this->actingAs($this->owner);

        // 1. Create Cashier Printer via Web Controller
        $res = $this->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('pos.printers.store'), [
                'name' => 'Printer Kasir Utama JKT',
                'location_id' => $this->locationA->id,
                'connection_type' => 'lan',
                'interface_address' => '192.168.1.100',
                'port' => 9100,
                'paper_width' => '80mm',
                'character_set' => 'CP437',
                'assigned_usages' => [PosPrinter::USAGE_CASHIER_RECEIPT],
                'capabilities' => [
                    PosPrinter::CAP_PRINT_TEXT,
                    PosPrinter::CAP_CUT,
                    PosPrinter::CAP_CASH_DRAWER,
                    PosPrinter::CAP_BARCODE,
                    PosPrinter::CAP_QR_CODE,
                ],
                'is_default' => true,
                'is_active' => true,
            ]);

        $res->assertRedirect();
        $this->assertDatabaseHas('pos_printers', [
            'business_id' => $this->businessA->id,
            'name' => 'Printer Kasir Utama JKT',
            'interface_address' => '192.168.1.100',
            'paper_width' => '80mm',
            'is_default' => true,
        ]);

        $printer = PosPrinter::where('business_id', $this->businessA->id)->first();
        $this->assertNotNull($printer);
        $this->assertTrue($printer->hasCapability(PosPrinter::CAP_CASH_DRAWER));
        $this->assertTrue($printer->supportsUsage(PosPrinter::USAGE_CASHIER_RECEIPT));

        // 2. Tenant Isolation Check: Printer from Business B cannot be edited by User from Business A
        $printerB = PosPrinter::create([
            'business_id' => $this->businessB->id,
            'location_id' => $this->locationB->id,
            'name' => 'Printer Bisnis B',
            'connection_type' => 'lan',
            'interface_address' => '192.168.2.200',
            'port' => 9100,
            'paper_width' => '58mm',
            'assigned_usages' => [PosPrinter::USAGE_CASHIER_RECEIPT],
            'is_active' => true,
        ]);

        $updateRes = $this->withSession(['active_business_id' => $this->businessA->id])
            ->put(route('pos.printers.update', $printerB->id), [
                'name' => 'Hacked Name',
                'connection_type' => 'lan',
                'interface_address' => '192.168.2.201',
                'paper_width' => '58mm',
                'assigned_usages' => [PosPrinter::USAGE_CASHIER_RECEIPT],
            ]);

        $updateRes->assertStatus(404);
        $this->assertDatabaseMissing('pos_printers', [
            'id' => $printerB->id,
            'name' => 'Hacked Name',
        ]);
    }

    public function test_escpos_formatter_builds_valid_stream_for_58mm_and_80mm(): void
    {
        $formatter = new EscposFormatter();

        // 1. 80mm Printer Receipt Format
        $printer80 = new PosPrinter([
            'paper_width' => '80mm',
            'capabilities' => [PosPrinter::CAP_PRINT_TEXT, PosPrinter::CAP_CUT, PosPrinter::CAP_BARCODE, PosPrinter::CAP_QR_CODE],
        ]);

        $stream80 = $formatter->formatReceipt($this->cashOrder, $printer80, ['is_reprint' => false]);
        $this->assertNotEmpty($stream80);
        $this->assertStringContainsString('ORD-2026-001', $stream80);
        $this->assertStringContainsString('Nasi Goreng Spesial', $stream80);
        $this->assertStringContainsString('Es Teh Manis', $stream80);

        // 2. 58mm Printer Receipt Format
        $printer58 = new PosPrinter([
            'paper_width' => '58mm',
            'capabilities' => [PosPrinter::CAP_PRINT_TEXT, PosPrinter::CAP_CUT],
        ]);

        $stream58 = $formatter->formatReceipt($this->cashOrder, $printer58, ['is_reprint' => true, 'reprint_count' => 2]);
        $this->assertNotEmpty($stream58);
        $this->assertStringContainsString('SALINAN', $stream58);

        // 3. Test Drawer Kick Stream
        $drawerStream = $formatter->formatCashDrawerPulse('pin2');
        $this->assertEquals("\x1B\x70\x00\x19\xFA", $drawerStream);
    }

    public function test_cash_drawer_safety_rules_and_anti_fraud(): void
    {
        $cashDrawerService = new CashDrawerService();

        $printer = PosPrinter::create([
            'business_id' => $this->businessA->id,
            'location_id' => $this->locationA->id,
            'name' => 'Kasir Utama',
            'connection_type' => 'lan',
            'interface_address' => '127.0.0.1',
            'port' => 9999, // offline socket for simulation
            'paper_width' => '80mm',
            'assigned_usages' => [PosPrinter::USAGE_CASHIER_RECEIPT],
            'capabilities' => [PosPrinter::CAP_CASH_DRAWER, PosPrinter::CAP_PRINT_TEXT],
            'is_active' => true,
        ]);

        // Rule A: Completed Cash Order SHOULD allow cash drawer open
        $this->assertTrue($cashDrawerService->shouldOpenForOrder($this->cashOrder, $printer, false));

        // Rule B: Reprint of Cash Order MUST NOT pop drawer
        $this->assertFalse($cashDrawerService->shouldOpenForOrder($this->cashOrder, $printer, true));

        // Rule C: Non-Cash Order (Transfer) MUST NOT auto pop drawer
        $this->assertFalse($cashDrawerService->shouldOpenForOrder($this->transferOrder, $printer, false));

        // Rule D: Unpaid or Pending Order MUST NOT pop drawer
        $unpaidOrder = clone $this->cashOrder;
        $unpaidOrder->status = PosOrder::STATUS_PENDING;
        $unpaidOrder->paid_amount = 0;
        $this->assertFalse($cashDrawerService->shouldOpenForOrder($unpaidOrder, $printer, false));
    }

    public function test_kitchen_routing_service_routes_items_by_category(): void
    {
        // 1. Kitchen Printer (Handles Food)
        $kitchenPrinter = PosPrinter::create([
            'business_id' => $this->businessA->id,
            'location_id' => $this->locationA->id,
            'name' => 'Printer Dapur Panas',
            'connection_type' => 'lan',
            'interface_address' => '192.168.1.101',
            'paper_width' => '80mm',
            'assigned_usages' => [PosPrinter::USAGE_KITCHEN_ORDER],
            'assigned_category_ids' => [$this->foodCategory->id],
            'is_active' => true,
        ]);

        // 2. Bar Printer (Handles Drinks)
        $barPrinter = PosPrinter::create([
            'business_id' => $this->businessA->id,
            'location_id' => $this->locationA->id,
            'name' => 'Printer Bar Minuman',
            'connection_type' => 'lan',
            'interface_address' => '192.168.1.102',
            'paper_width' => '80mm',
            'assigned_usages' => [PosPrinter::USAGE_BAR_ORDER],
            'assigned_category_ids' => [$this->drinkCategory->id],
            'is_active' => true,
        ]);

        $kitchenRoutingService = new KitchenRoutingService();
        $routed = $kitchenRoutingService->routeOrderItems($this->cashOrder, $this->businessA, $this->locationA->id);

        $this->assertArrayHasKey($kitchenPrinter->id, $routed);
        $this->assertArrayHasKey($barPrinter->id, $routed);

        $kitchenItems = $routed[$kitchenPrinter->id]['items'];
        $barItems = $routed[$barPrinter->id]['items'];

        $this->assertCount(1, $kitchenItems);
        $this->assertEquals('Nasi Goreng Spesial', $kitchenItems[0]->product_name);

        $this->assertCount(1, $barItems);
        $this->assertEquals('Es Teh Manis', $barItems[0]->product_name);
    }

    public function test_local_agent_api_endpoints_and_job_lifecycle(): void
    {
        $this->actingAs($this->owner, 'sanctum');

        $printer = PosPrinter::create([
            'business_id' => $this->businessA->id,
            'location_id' => $this->locationA->id,
            'name' => 'Printer Local Agent Bluetooth',
            'connection_type' => 'bluetooth',
            'interface_address' => 'POS-BT-01',
            'paper_width' => '58mm',
            'assigned_usages' => [PosPrinter::USAGE_CASHIER_RECEIPT],
            'is_active' => true,
        ]);

        // Create pending print job
        $job = PosPrintJob::create([
            'business_id' => $this->businessA->id,
            'location_id' => $this->locationA->id,
            'pos_printer_id' => $printer->id,
            'document_type' => PosPrintJob::TYPE_RECEIPT,
            'document_id' => (string) $this->cashOrder->id,
            'payload' => 'G0BiAAE...',
            'payload_format' => 'base64',
            'status' => PosPrintJob::STATUS_PENDING,
        ]);

        // 1. Agent Polls Pending Jobs
        $response = $this->withHeaders([
            'X-Business-Id' => (string) $this->businessA->id,
            'X-Location-Id' => (string) $this->locationA->id,
        ])->getJson('/api/v1/pos/agent/jobs');

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'jobs' => [
                '*' => ['id', 'document_type', 'payload_base64', 'printer'],
            ],
        ]);

        // 2. Agent Updates Job Status to Printed
        $statusResponse = $this->withHeaders([
            'X-Business-Id' => (string) $this->businessA->id,
        ])->postJson("/api/v1/pos/agent/jobs/{$job->id}/status", [
            'status' => 'printed',
        ]);

        $statusResponse->assertOk();
        $this->assertDatabaseHas('pos_print_jobs', [
            'id' => $job->id,
            'status' => PosPrintJob::STATUS_PRINTED,
        ]);

        // 3. Agent Syncs Local Device Status
        $syncResponse = $this->withHeaders([
            'X-Business-Id' => (string) $this->businessA->id,
        ])->postJson('/api/v1/pos/agent/sync-device', [
            'location_id' => (string) $this->locationA->id,
            'device_name' => 'Terminal POS Kasir 01 (Windows 11)',
            'agent_version' => '1.0.0',
            'status' => 'online',
        ]);

        $syncResponse->assertOk();
        $syncResponse->assertJson(['success' => true]);
    }
}
