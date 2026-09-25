<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use App\Domain\Pos\PosOrderService;
use App\Domain\Pos\PosShiftService;
use App\Domain\Printer\PrinterManager;
use App\Models\Business;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderPayment;
use App\Models\PosPrinter;
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

final class PosMultiRegisterShiftAndStockFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $ownerA;
    private User $cashier1;
    private User $cashier2;
    private User $ownerB;

    private Business $businessA;
    private Business $businessB;

    private Location $outletJakarta;
    private Location $outletSurabaya;
    private Location $centralWarehouse;

    private PosRegister $registerPos01;
    private PosRegister $registerPos02;

    private PosPrinter $printerPos01;
    private PosPrinter $printerPos02;

    private Product $productKopi;
    private Product $productNasi;

    private PosShiftService $shiftService;
    private PosOrderService $orderService;
    private PrinterManager $printerManager;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->shiftService = new PosShiftService();
        $this->orderService = new PosOrderService();
        $this->printerManager = new PrinterManager();

        // 1. Users
        $this->ownerA = User::create([
            'name' => 'Owner Bisnis A',
            'email' => 'owner.a@example.com',
            'phone' => '081200000001',
            'password' => bcrypt('secret123'),
        ]);
        $this->ownerA->forceFill(['email_verified_at' => now()])->save();

        $this->cashier1 = User::create([
            'name' => 'Kasir 1 (Budi)',
            'email' => 'kasir1@example.com',
            'phone' => '081200000002',
            'password' => bcrypt('secret123'),
        ]);
        $this->cashier1->forceFill(['email_verified_at' => now()])->save();

        $this->cashier2 = User::create([
            'name' => 'Kasir 2 (Siti)',
            'email' => 'kasir2@example.com',
            'phone' => '081200000003',
            'password' => bcrypt('secret123'),
        ]);
        $this->cashier2->forceFill(['email_verified_at' => now()])->save();

        $this->ownerB = User::create([
            'name' => 'Owner Bisnis B',
            'email' => 'owner.b@example.com',
            'phone' => '081200000099',
            'password' => bcrypt('secret123'),
        ]);
        $this->ownerB->forceFill(['email_verified_at' => now()])->save();

        // 2. Businesses
        $this->businessA = Business::create([
            'name' => 'Kopi Nusantara Prima',
            'address' => 'Jl. Sudirman No. 10, Jakarta',
            'phone' => '0215550001',
        ]);
        $this->businessA->users()->attach($this->ownerA->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $this->businessA->users()->attach($this->cashier1->id, ['id' => (string) Str::uuid(), 'role' => 'cashier']);
        $this->businessA->users()->attach($this->cashier2->id, ['id' => (string) Str::uuid(), 'role' => 'cashier']);

        $this->businessB = Business::create([
            'name' => 'Resto Bintang Lima',
            'address' => 'Jl. Braga No. 20, Bandung',
            'phone' => '0229990002',
        ]);
        $this->businessB->users()->attach($this->ownerB->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);

        // 3. Locations (Outlets & Warehouses)
        $this->outletJakarta = Location::create([
            'business_id' => $this->businessA->id,
            'name' => 'Outlet Jakarta Selatan',
            'code' => 'OUT-JKT',
            'type' => 'outlet',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $this->outletSurabaya = Location::create([
            'business_id' => $this->businessA->id,
            'name' => 'Outlet Surabaya Gubeng',
            'code' => 'OUT-SBY',
            'type' => 'outlet',
            'is_active' => true,
        ]);

        $this->centralWarehouse = Location::create([
            'business_id' => $this->businessA->id,
            'name' => 'Gudang Pusat Logistik',
            'code' => 'GDG-PST',
            'type' => 'warehouse',
            'is_active' => true,
        ]);

        // 4. Hardware Printers
        $this->printerPos01 = PosPrinter::create([
            'business_id' => $this->businessA->id,
            'location_id' => $this->outletJakarta->id,
            'name' => 'Epson TM-T82 POS 01',
            'connection_type' => PosPrinter::TYPE_AGENT,
            'interface_address' => '192.168.1.101',
            'paper_width' => '80mm',
            'is_active' => true,
            'capabilities' => [PosPrinter::CAP_PRINT_TEXT, PosPrinter::CAP_CUT, PosPrinter::CAP_CASH_DRAWER],
            'assigned_usages' => [PosPrinter::USAGE_CASHIER_RECEIPT],
        ]);

        $this->printerPos02 = PosPrinter::create([
            'business_id' => $this->businessA->id,
            'location_id' => $this->outletJakarta->id,
            'name' => 'Epson TM-T82 POS 02',
            'connection_type' => PosPrinter::TYPE_AGENT,
            'interface_address' => '192.168.1.102',
            'paper_width' => '80mm',
            'is_active' => true,
            'capabilities' => [PosPrinter::CAP_PRINT_TEXT, PosPrinter::CAP_CUT, PosPrinter::CAP_CASH_DRAWER],
            'assigned_usages' => [PosPrinter::USAGE_CASHIER_RECEIPT],
        ]);

        // 5. POS Terminals / Registers
        $this->registerPos01 = PosRegister::create([
            'business_id' => $this->businessA->id,
            'location_id' => $this->outletJakarta->id,
            'name' => 'POS Terminal 01 (Depan)',
            'code' => 'REG-01',
            'device_identifier' => 'DEV-TERMINAL-001',
            'default_receipt_printer_id' => $this->printerPos01->id,
            'is_active' => true,
        ]);

        $this->registerPos02 = PosRegister::create([
            'business_id' => $this->businessA->id,
            'location_id' => $this->outletJakarta->id,
            'name' => 'POS Terminal 02 (Drive Thru)',
            'code' => 'REG-02',
            'device_identifier' => 'DEV-TERMINAL-002',
            'default_receipt_printer_id' => $this->printerPos02->id,
            'is_active' => true,
        ]);

        // 6. Products & Inventory Units
        $unit = Unit::create([
            'business_id' => $this->businessA->id,
            'name' => 'Cup',
            'code' => 'CUP',
            'symbol' => 'cup',
            'category' => 'quantity',
        ]);
        $cat = ProductCategory::create(['business_id' => $this->businessA->id, 'name' => 'Minuman']);

        $this->productKopi = Product::create([
            'business_id' => $this->businessA->id,
            'category_id' => $cat->id,
            'output_unit_id' => $unit->id,
            'name' => 'Kopi Susu Aren Spesial',
            'code' => 'KOP-001',
            'selling_price' => 25000,
            'base_cost' => 10000,
            'track_stock' => true,
            'is_active' => true,
        ]);

        $this->productNasi = Product::create([
            'business_id' => $this->businessA->id,
            'category_id' => $cat->id,
            'output_unit_id' => $unit->id,
            'name' => 'Nasi Goreng Kampung',
            'code' => 'NAS-001',
            'selling_price' => 35000,
            'base_cost' => 15000,
            'track_stock' => true,
            'is_active' => true,
        ]);

        // Initial Stocks per location
        InventoryStock::create([
            'business_id' => $this->businessA->id,
            'location_id' => $this->outletJakarta->id,
            'product_id' => $this->productKopi->id,
            'quantity' => 100,
        ]);
        InventoryStock::create([
            'business_id' => $this->businessA->id,
            'location_id' => $this->outletSurabaya->id,
            'product_id' => $this->productKopi->id,
            'quantity' => 50,
        ]);
        InventoryStock::create([
            'business_id' => $this->businessA->id,
            'location_id' => $this->centralWarehouse->id,
            'product_id' => $this->productKopi->id,
            'quantity' => 500,
        ]);
    }

    /**
     * Test 1: Multi-Cashier & Multi-Register Shift Open with Denominations and Concurrency Guard.
     */
    public function test_multi_cashier_open_shift_with_denominations_and_register_isolation(): void
    {
        Context::setBusiness($this->businessA);

        // Cashier 1 opens shift on Register 1 with denomination breakdown
        $shift1 = $this->shiftService->openShift(
            business: $this->businessA,
            user: $this->cashier1,
            openingCash: 0,
            posRegisterId: $this->registerPos01->id,
            locationId: $this->outletJakarta->id,
            notes: 'Buka kasir pagi',
            openingDenominations: [
                '100000' => 1,
                '50000' => 2,
                'coins' => 5000,
            ]
        );

        $this->assertNotNull($shift1);
        $this->assertEquals(PosShift::STATUS_OPEN, $shift1->status);
        $this->assertEquals(205000.0, (float) $shift1->opening_cash);
        $this->assertEquals($this->registerPos01->id, $shift1->pos_register_id);
        $this->assertEquals($this->cashier1->id, $shift1->user_id);
        $this->assertIsArray($shift1->opening_denominations);
        $this->assertEquals(1, $shift1->opening_denominations['100000']);

        // Guard: Attempting to open another shift on Register 1 returns existing active shift
        $duplicateShift = $this->shiftService->openShift(
            business: $this->businessA,
            user: $this->cashier1,
            openingCash: 500000,
            posRegisterId: $this->registerPos01->id
        );
        $this->assertEquals($shift1->id, $duplicateShift->id);

        // Cashier 2 opens shift on Register 2 simultaneously
        $shift2 = $this->shiftService->openShift(
            business: $this->businessA,
            user: $this->cashier2,
            openingCash: 150000,
            posRegisterId: $this->registerPos02->id,
            locationId: $this->outletJakarta->id,
            notes: 'Kasir Drive Thru',
            openingDenominations: [
                '50000' => 3,
            ]
        );

        $this->assertNotNull($shift2);
        $this->assertNotEquals($shift1->id, $shift2->id);
        $this->assertEquals($this->registerPos02->id, $shift2->pos_register_id);
        $this->assertEquals(150000.0, (float) $shift2->opening_cash);
    }

    /**
     * Test 2: Checkouts are bound to PosRegister and deduct inventory stock strictly at the register's location.
     */
    public function test_checkouts_are_tagged_with_register_and_deduct_isolated_location_stock(): void
    {
        Context::setBusiness($this->businessA);

        $shift1 = $this->shiftService->openShift(
            business: $this->businessA,
            user: $this->cashier1,
            openingCash: 100000,
            posRegisterId: $this->registerPos01->id,
            locationId: $this->outletJakarta->id
        );

        // Checkout 3 cups of Kopi on Register 01 at Outlet Jakarta
        $order = $this->orderService->checkout(
            business: $this->businessA,
            cashier: $this->cashier1,
            itemsData: [
                [
                    'product_id' => $this->productKopi->id,
                    'product_name' => $this->productKopi->name,
                    'unit_price' => 25000,
                    'quantity' => 3,
                ],
            ],
            paymentsData: [
                [
                    'payment_method' => PosOrderPayment::METHOD_CASH,
                    'amount' => 100000,
                ],
            ],
            attributes: [
                'pos_register_id' => $this->registerPos01->id,
                'location_id' => $this->outletJakarta->id,
                'order_type' => 'takeaway',
            ],
            shift: $shift1
        );

        $this->assertNotNull($order);
        $this->assertEquals($this->registerPos01->id, $order->pos_register_id);
        $this->assertEquals($this->outletJakarta->id, $order->location_id);
        $this->assertEquals(75000.0, (float) $order->total_amount);
        $this->assertEquals(25000.0, (float) $order->change_amount);

        // Verify stock isolation:
        // Jakarta Stock must be 100 - 3 = 97
        $jktStock = InventoryStock::where('business_id', $this->businessA->id)
            ->where('location_id', $this->outletJakarta->id)
            ->where('product_id', $this->productKopi->id)
            ->first();
        $this->assertEquals(97.0, (float) $jktStock->quantity);

        // Surabaya and Central Warehouse stocks remain completely untouched
        $sbyStock = InventoryStock::where('business_id', $this->businessA->id)
            ->where('location_id', $this->outletSurabaya->id)
            ->where('product_id', $this->productKopi->id)
            ->first();
        $this->assertEquals(50.0, (float) $sbyStock->quantity);

        $whStock = InventoryStock::where('business_id', $this->businessA->id)
            ->where('location_id', $this->centralWarehouse->id)
            ->where('product_id', $this->productKopi->id)
            ->first();
        $this->assertEquals(500.0, (float) $whStock->quantity);
    }

    /**
     * Test 3: Shift Closing with Blind Cash Count, Cash Movements, and Live Variance Calculation.
     */
    public function test_shift_close_with_blind_cash_count_and_variance_calculation(): void
    {
        Context::setBusiness($this->businessA);

        $shift = $this->shiftService->openShift(
            business: $this->businessA,
            user: $this->cashier1,
            openingCash: 100000,
            posRegisterId: $this->registerPos01->id,
            locationId: $this->outletJakarta->id
        );

        // Sale: 50,000 Cash
        $this->orderService->checkout(
            business: $this->businessA,
            cashier: $this->cashier1,
            itemsData: [
                [
                    'product_id' => $this->productKopi->id,
                    'product_name' => $this->productKopi->name,
                    'unit_price' => 25000,
                    'quantity' => 2,
                ],
            ],
            paymentsData: [
                [
                    'payment_method' => PosOrderPayment::METHOD_CASH,
                    'amount' => 50000,
                ],
            ],
            attributes: [
                'pos_register_id' => $this->registerPos01->id,
                'location_id' => $this->outletJakarta->id,
            ],
            shift: $shift
        );

        // Cash In: 20,000 (tambah modal receh)
        $this->shiftService->recordCashMovement(
            shift: $shift,
            user: $this->cashier1,
            type: 'cash_in',
            amount: 20000,
            reason: 'Tambah uang kembalian'
        );

        // Cash Out: 10,000 (beli kantong plastik)
        $this->shiftService->recordCashMovement(
            shift: $shift,
            user: $this->cashier1,
            type: 'cash_out',
            amount: 10000,
            reason: 'Beli plastik es'
        );

        // Summary Check: Expected Cash = 100k + 50k (sales) + 20k (in) - 10k (out) = 160,000
        $summary = $this->shiftService->getShiftSummary($shift);
        $this->assertEquals(160000.0, (float) $summary['expected_cash']);
        $this->assertEquals(50000.0, (float) $summary['cash_sales']);

        // Scenario A: Physical Count has 165,000 (Lebih +5,000)
        $closingDenoms = [
            '100000' => 1,
            '50000' => 1,
            '10000' => 1,
            '5000' => 1,
        ];

        $closedShift = $this->shiftService->closeShift(
            shift: $shift,
            actualCash: 0,
            notes: 'Shift selesai aman',
            closingDenominations: $closingDenoms,
            cashierNotes: 'Ada tip receh pelanggan tertinggal'
        );

        $this->assertEquals(PosShift::STATUS_CLOSED, $closedShift->status);
        $this->assertEquals(165000.0, (float) $closedShift->closing_cash_actual);
        $this->assertEquals(160000.0, (float) $closedShift->closing_cash_expected);
        $this->assertEquals(5000.0, (float) $closedShift->cash_difference);
        $this->assertTrue($closedShift->isOver());
        $this->assertFalse($closedShift->isShort());
        $this->assertFalse($closedShift->isBalanced());
        $this->assertEquals('+Rp 5.000 (Lebih / Over)', $closedShift->getVarianceLabel());
        $this->assertEquals('Ada tip receh pelanggan tertinggal', $closedShift->cashier_notes);
    }

    /**
     * Test 4: ESC/POS Thermal Printing resolves terminal's assigned default printer and produces binary payload.
     */
    public function test_shift_report_and_receipt_resolves_register_default_printer_and_formats_escpos(): void
    {
        Context::setBusiness($this->businessA);

        $shift = $this->shiftService->openShift(
            business: $this->businessA,
            user: $this->cashier1,
            openingCash: 100000,
            posRegisterId: $this->registerPos01->id,
            locationId: $this->outletJakarta->id
        );

        // Verify register's default receipt printer is resolved
        $resolvedPrinter = $this->printerManager->resolveCashierPrinter(
            $this->businessA,
            $this->outletJakarta->id,
            $this->registerPos01
        );
        $this->assertNotNull($resolvedPrinter);
        $this->assertEquals($this->printerPos01->id, $resolvedPrinter->id);

        // Print Cashier Shift Report
        $printResult = $this->printerManager->printCashierShift($shift);
        $this->assertTrue($printResult['success']);
        $this->assertNotEmpty($printResult['base64_payload']);
        $this->assertEquals($this->printerPos01->name, $printResult['printer_name']);

        $decodedBytes = base64_decode($printResult['base64_payload']);
        $this->assertStringContainsString('LAPORAN TUTUP KASIR / SHIFT', $decodedBytes);
        $this->assertStringContainsString('KOPI NUSANTARA PRIMA', $decodedBytes);
    }

    /**
     * Test 5: Web Controller endpoints for open, close, and print shift enforce tenant isolation.
     */
    public function test_shift_web_controllers_and_routes_with_tenant_isolation(): void
    {
        $this->actingAs($this->ownerA);
        Context::setBusiness($this->businessA);

        // 1. Open shift via Web Route
        $responseOpen = $this->postJson(route('pos.shifts.open'), [
            'opening_cash' => 200000,
            'pos_register_id' => $this->registerPos01->id,
            'location_id' => $this->outletJakarta->id,
            'notes' => 'Testing web open',
            'opening_denominations' => ['100000' => 2],
        ]);
        $responseOpen->assertStatus(200);
        $responseOpen->assertJson(['success' => true]);
        $shiftId = $responseOpen->json('shift.id');

        $shift = PosShift::find($shiftId);
        $this->assertNotNull($shift);

        // 2. Print shift via Web Route
        $responsePrint = $this->postJson(route('pos.shifts.print', $shift->id));
        $responsePrint->assertStatus(200);
        $responsePrint->assertJson(['success' => true]);

        // 3. Tenant Isolation Guard: User B cannot print or close User A's shift
        $this->actingAs($this->ownerB);
        Context::setBusiness($this->businessB);

        $responseHackerPrint = $this->postJson(route('pos.shifts.print', $shift->id));
        $this->assertContains($responseHackerPrint->status(), [403, 404]);

        $responseHackerClose = $this->postJson(route('pos.shifts.close', $shift->id), [
            'closing_cash_actual' => 100000,
        ]);
        $this->assertContains($responseHackerClose->status(), [403, 404]);
    }
}
