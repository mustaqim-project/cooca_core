<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use App\Domain\Accounting\AutoJournalService;
use App\Domain\Finance\StoreEdcTerminalService;
use App\Domain\Pos\PosOrderService;
use App\Domain\Pos\PosShiftService;
use App\Models\Business;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderPayment;
use App\Models\PosRegister;
use App\Models\PosShift;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StoreEdcTerminal;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PosMultiPaymentSplitTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $cashier;
    private Business $business;
    private Location $location;
    private PosRegister $register;
    private PosShift $shift;
    private Product $productA;
    private Product $productB;
    private StoreEdcTerminal $edcBca;
    private StoreEdcTerminal $edcMandiri;

    private PosOrderService $orderService;
    private AutoJournalService $journalService;
    private StoreEdcTerminalService $edcService;
    private PosShiftService $shiftService;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->orderService = app(PosOrderService::class);
        $this->journalService = app(AutoJournalService::class);
        $this->edcService = app(StoreEdcTerminalService::class);
        $this->shiftService = app(PosShiftService::class);

        // 1. Setup Business & Owner
        $this->business = Business::create([
            'id' => (string) Str::uuid(),
            'name' => 'Resto & Cafe Cooca Mega',
            'legal_entity_name' => 'PT Cooca Retail Sejahtera',
            'owner_id' => (string) Str::uuid(),
            'email' => 'owner@coocaresto.com',
            'currency' => 'IDR',
            'pos_enable_tax' => true,
            'pos_tax_percent' => 11.0,
            'pos_enable_service_charge' => false,
        ]);

        $this->owner = User::create([
            'id' => $this->business->owner_id,
            'name' => 'Budi Setiawan',
            'email' => 'owner@coocaresto.com',
            'password' => bcrypt('secret123'),
        ]);

        $this->cashier = User::create([
            'id' => (string) Str::uuid(),
            'name' => 'Siti Kasir Pagi',
            'email' => 'siti@coocaresto.com',
            'password' => bcrypt('secret123'),
        ]);

        Context::setBusiness($this->business);
        $this->actingAs($this->owner);

        // 2. Setup Location & Register
        $this->location = Location::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Cabang Mall Grand Indonesia',
            'code' => 'LOC-GI',
            'is_active' => true,
        ]);

        $this->register = PosRegister::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'name' => 'Kasir Utama 01',
            'code' => 'REG-01',
            'is_active' => true,
        ]);

        // 3. Setup Open Shift
        $this->shift = $this->shiftService->openShift(
            business: $this->business,
            user: $this->cashier,
            locationId: $this->location->id,
            posRegisterId: $this->register->id,
            openingCash: 200000.0,
            notes: 'Shift Pagi GI'
        );

        // 4. Setup Category, Unit & Products
        $category = ProductCategory::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Food & Beverages',
            'slug' => 'fnb',
        ]);

        $unit = Unit::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Porsi',
            'code' => 'prs',
            'symbol' => 'prs',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);

        $this->productA = Product::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'category_id' => $category->id,
            'output_unit_id' => $unit->id,
            'name' => 'Steak Wagyu Meltique 200g',
            'code' => 'STK-001',
            'selling_price' => 150000.0,
            'base_cost' => 80000.0,
            'track_stock' => true,
            'type' => Product::TYPE_GOODS,
            'is_active' => true,
        ]);

        $this->productB = Product::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'category_id' => $category->id,
            'output_unit_id' => $unit->id,
            'name' => 'Signature Artisan Iced Latte',
            'code' => 'COF-002',
            'selling_price' => 35000.0,
            'base_cost' => 12000.0,
            'track_stock' => true,
            'type' => Product::TYPE_GOODS,
            'is_active' => true,
        ]);

        \App\Models\InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->productA->id,
            'quantity' => 100,
        ]);

        \App\Models\InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->productB->id,
            'quantity' => 100,
        ]);

        // 5. Setup EDC Terminals via Service
        $this->edcBca = $this->edcService->registerTerminal($this->business, [
            'location_id' => $this->location->id,
            'terminal_name' => 'EDC BCA Kasir 1',
            'bank_name' => 'BCA',
            'terminal_id_tid' => 'BCA-GI-88991',
            'merchant_id_mid' => 'MID-BCA-00192',
            'is_active' => true,
        ]);

        $this->edcMandiri = $this->edcService->registerTerminal($this->business, [
            'location_id' => $this->location->id,
            'terminal_name' => 'EDC Mandiri Mobile',
            'bank_name' => 'MANDIRI',
            'terminal_id_tid' => 'MND-GI-44210',
            'merchant_id_mid' => 'MID-MND-99401',
            'is_active' => true,
        ]);
    }

    public function test_can_create_and_scope_store_edc_terminals_by_location(): void
    {
        $terminals = $this->edcService->getTerminalsForBusiness($this->business, $this->location->id);
        $this->assertCount(2, $terminals);
        $this->assertEquals('BCA-GI-88991', $terminals->first()->terminal_id_tid);

        // Other location should return empty
        $otherLoc = Location::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Cabang Surabaya Barat',
            'code' => 'LOC-SBY',
            'is_active' => true,
        ]);

        $sbyTerminals = $this->edcService->getTerminalsForBusiness($this->business, $otherLoc->id);
        $this->assertCount(0, $sbyTerminals);
    }

    public function test_can_checkout_pos_with_single_edc_debit_payment_and_attach_terminal_id(): void
    {
        // Total: Steak (150k) + Latte (35k) = 185k + Tax 11% (20,350) = 205,350
        $items = [
            [
                'product_id' => $this->productA->id,
                'quantity' => 1,
            ],
            [
                'product_id' => $this->productB->id,
                'quantity' => 1,
            ],
        ];

        $payments = [
            [
                'payment_method' => PosOrderPayment::METHOD_EDC_DEBIT,
                'store_edc_terminal_id' => $this->edcBca->id,
                'amount' => 205350.0,
                'reference_number' => 'APPR-BCA-998811',
            ],
        ];

        $order = $this->orderService->checkout(
            business: $this->business,
            cashier: $this->cashier,
            itemsData: $items,
            paymentsData: $payments,
            attributes: [
                'location_id' => $this->location->id,
                'pos_register_id' => $this->register->id,
            ]
        );

        $this->assertInstanceOf(PosOrder::class, $order);
        $this->assertEquals(205350.0, (float) $order->total_amount);
        $this->assertEquals(PosOrder::STATUS_COMPLETED, $order->status);

        // Check Payment Record
        $this->assertCount(1, $order->payments);
        $payment = $order->payments->first();
        $this->assertEquals(PosOrderPayment::METHOD_EDC_DEBIT, $payment->payment_method);
        $this->assertEquals($this->edcBca->id, $payment->store_edc_terminal_id);
        $this->assertEquals('APPR-BCA-998811', $payment->reference_number);
        $this->assertNotNull($payment->edcTerminal);
        $this->assertEquals('EDC BCA Kasir 1', $payment->edcTerminal->terminal_name);
    }

    public function test_can_checkout_pos_with_compound_split_payments(): void
    {
        // 2x Steak Wagyu (300,000) + Tax 11% (33,000) = 333,000
        $items = [
            [
                'product_id' => $this->productA->id,
                'quantity' => 2,
            ],
        ];

        // Split:
        // 1. Cash: 100,000
        // 2. EDC BCA Debit: 150,000 (TID: BCA-GI-88991)
        // 3. QRIS Cooca Pay: 83,000
        // Total = 333,000
        $payments = [
            [
                'payment_method' => PosOrderPayment::METHOD_CASH,
                'amount' => 100000.0,
            ],
            [
                'payment_method' => PosOrderPayment::METHOD_EDC_DEBIT,
                'store_edc_terminal_id' => $this->edcBca->id,
                'amount' => 150000.0,
                'reference_number' => 'REF-EDC-001',
            ],
            [
                'payment_method' => PosOrderPayment::METHOD_QRIS,
                'amount' => 83000.0,
                'reference_number' => 'QRIS-COOCA-7729',
            ],
        ];

        $order = $this->orderService->checkout(
            business: $this->business,
            cashier: $this->cashier,
            itemsData: $items,
            paymentsData: $payments,
            attributes: [
                'location_id' => $this->location->id,
                'pos_register_id' => $this->register->id,
            ]
        );

        $this->assertInstanceOf(PosOrder::class, $order);
        $this->assertEquals(333000.0, (float) $order->total_amount);
        $this->assertEquals(333000.0, (float) $order->paid_amount);
        $this->assertEquals(0.0, (float) $order->change_amount);
        $this->assertCount(3, $order->payments);

        // Verify that relations load properly
        $order->load('payments.edcTerminal');
        $edcPayment = $order->payments->firstWhere('payment_method', PosOrderPayment::METHOD_EDC_DEBIT);
        $this->assertNotNull($edcPayment);
        $this->assertEquals('EDC BCA Kasir 1', $edcPayment->edcTerminal->terminal_name);
        $this->assertEquals('BCA', $edcPayment->edcTerminal->bank_name);
    }

    public function test_split_payment_with_cash_overpayment_calculates_change_and_balances_journal(): void
    {
        // 1x Steak (150,000) + Tax 11% (16,500) = 166,500
        $items = [
            [
                'product_id' => $this->productA->id,
                'quantity' => 1,
            ],
        ];

        // Customer pays:
        // 1. EDC Mandiri: 100,000 (TID: MND-GI-44210)
        // 2. Cash: 100,000 (Gives 100k cash, total tendered = 200,000, change = 33,500)
        $payments = [
            [
                'payment_method' => PosOrderPayment::METHOD_EDC_CREDIT,
                'store_edc_terminal_id' => $this->edcMandiri->id,
                'amount' => 100000.0,
                'reference_number' => 'MND-CC-8821',
            ],
            [
                'payment_method' => PosOrderPayment::METHOD_CASH,
                'amount' => 100000.0,
            ],
        ];

        $order = $this->orderService->checkout(
            business: $this->business,
            cashier: $this->cashier,
            itemsData: $items,
            paymentsData: $payments,
            attributes: [
                'location_id' => $this->location->id,
                'pos_register_id' => $this->register->id,
            ]
        );

        // Verify Auto Journal created by checkout
        $journal = JournalEntry::where('business_id', $this->business->id)
            ->where('reference_type', 'pos_order')
            ->where('reference_id', $order->id)
            ->first();

        $this->assertInstanceOf(JournalEntry::class, $journal);
        $this->assertEquals((float) $journal->total_debit, (float) $journal->total_credit);
        $this->assertEquals(246500.0, (float) $journal->total_debit);
        $this->assertEquals(246500.0, (float) $journal->total_credit);

        // Net Kas physical cash debit should be 100,000 - 33,500 = 66,500
        // EDC Mandiri debit should be 100,000
        // COGS (HPP) debit = 80,000
        // Total Debits = 66,500 + 100,000 + 80,000 = 246,500
        // Revenue credit = 150,000
        // PPN Tax credit = 16,500
        // Inventory credit = 80,000
        // Total Credits = 150,000 + 16,500 + 80,000 = 246,500

        $debitLines = $journal->lines()->where('type', JournalEntryLine::TYPE_DEBIT)->get();
        $creditLines = $journal->lines()->where('type', JournalEntryLine::TYPE_CREDIT)->get();

        $this->assertEquals(246500.0, (float) $debitLines->sum('amount'));
        $this->assertEquals(246500.0, (float) $creditLines->sum('amount'));

        // Check EDC Line Account code 1-1008
        $edcLine = $debitLines->first(function ($l) {
            return str_contains($l->notes, 'EDC Kredit') || $l->account->code === '1-1008';
        });
        $this->assertNotNull($edcLine);
        $this->assertEquals(100000.0, (float) $edcLine->amount);
    }

    public function test_receipt_renders_split_payment_breakdown_and_edc_details(): void
    {
        $items = [
            [
                'product_id' => $this->productB->id,
                'quantity' => 2,
            ],
        ];

        // 2x Latte = 70,000 + Tax 11% (7,700) = 77,700
        $payments = [
            [
                'payment_method' => PosOrderPayment::METHOD_CASH,
                'amount' => 50000.0,
            ],
            [
                'payment_method' => PosOrderPayment::METHOD_EDC_DEBIT,
                'store_edc_terminal_id' => $this->edcBca->id,
                'amount' => 27700.0,
                'reference_number' => 'APPR-7700',
            ],
        ];

        $order = $this->orderService->checkout(
            business: $this->business,
            cashier: $this->cashier,
            itemsData: $items,
            paymentsData: $payments,
            attributes: [
                'location_id' => $this->location->id,
                'pos_register_id' => $this->register->id,
            ]
        );

        $order->load(['business', 'location', 'items', 'payments.edcTerminal', 'customer', 'user']);

        $rendered = view('app.pos.receipt', [
            'order' => $order,
            'business' => $this->business,
            'paperWidth' => 58,
            'currencySymbol' => 'Rp',
            'showTax' => true,
            'showLogo' => false,
            'footerNote' => 'Terima kasih atas kunjungan Anda!',
        ])->render();

        $this->assertStringContainsString('MULTI-PAYMENT (SPLIT)', $rendered);
        $this->assertStringContainsString('Tunai (Cash)', $rendered);
        $this->assertStringContainsString('EDC Debit', $rendered);
        $this->assertStringContainsString('BCA-GI-88991', $rendered);
        $this->assertStringContainsString('APPR-7700', $rendered);
    }
}
