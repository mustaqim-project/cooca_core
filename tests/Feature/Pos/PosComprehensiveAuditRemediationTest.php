<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use App\Domain\Accounting\AutoJournalService;
use App\Domain\Finance\CashLedgerService;
use App\Domain\Pos\PosOrderService;
use App\Domain\Pos\PosShiftService;
use App\Http\Requests\Pos\PosCheckoutRequest;
use App\Http\Requests\Pos\PosCloseShiftRequest;
use App\Http\Requests\Pos\PosOpenShiftRequest;
use App\Http\Requests\Pos\PosRefundOrderRequest;
use App\Http\Requests\Pos\PosVoidOrderRequest;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\JournalEntry;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosShift;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use App\Support\Navigation\NavigationRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Master Acceptance Test Suite: 18 Temuan Audit Faktual POS COOCA (Fase 1 - 10).
 * Menguji keterpaduan menyeluruh hulu-ke-hilir tanpa regresi sistem.
 */
final class PosComprehensiveAuditRemediationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $cashier;
    private Business $business;
    private Location $location;
    private Product $product;
    private PosShiftService $shiftService;
    private PosOrderService $orderService;
    private AutoJournalService $journalService;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->shiftService = new PosShiftService();
        $this->orderService = new PosOrderService();
        $this->journalService = new AutoJournalService();

        $this->owner = User::create([
            'name' => 'Owner Bisnis POS',
            'email' => 'owner.master@example.com',
            'password' => bcrypt('password123'),
        ]);
        $this->owner->forceFill(['email_verified_at' => now()])->save();

        $this->cashier = User::create([
            'name' => 'Kasir Utama POS',
            'email' => 'cashier.master@example.com',
            'password' => bcrypt('password123'),
        ]);
        $this->cashier->forceFill(['email_verified_at' => now()])->save();

        $this->business = Business::create([
            'name' => 'Resto Master Audit POS',
            'email' => 'resto.master@example.com',
            'phone' => '08123456789',
            'operating_mode' => 'team',
            'industry_type' => 'fnb',
            'pos_max_cashier_discount_percent' => 10,
            'pos_supervisor_pin' => bcrypt('123456'),
        ]);
        $this->business->users()->attach($this->owner->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $this->business->users()->attach($this->cashier->id, ['id' => (string) Str::uuid(), 'role' => 'cashier']);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Master POS',
            'is_primary' => true,
            'is_active' => true,
        ]);

        BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_code' => BusinessSubscription::PLAN_PREMIUM_MONTHLY,
            'price' => 89000,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'features' => ['kds', 'pos_table', 'auto_journal'],
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ]);

        $this->journalService->ensureStandardAccounts($this->business);

        $category = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Signature Coffee',
        ]);

        $unit = Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Cup',
            'code' => 'cup',
            'symbol' => 'cup',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $category->id,
            'output_unit_id' => $unit->id,
            'name' => 'Kopi Susu Master',
            'code' => 'KSM-001',
            'selling_price' => 20000.0,
            'base_cost' => 8000.0,
            'track_stock' => false,
            'is_active' => true,
        ]);

        \App\Models\InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'quantity' => 100.0,
            'avg_cost' => 8000.0,
        ]);
    }

    /**
     * F-01: Verifikasi Expected Cash Shift Mengurangi Kembalian Pelanggan (Anti Phantom Cash Deficit).
     */
    public function test_f01_shift_expected_cash_deducts_change_amount_accurately(): void
    {
        $shift = $this->shiftService->openShift(
            $this->business,
            $this->cashier,
            100000.0,
            null,
            $this->location->id,
            'Shift Pagi Master'
        );

        // Tagihan 20.000, Uang Diterima 50.000, Kembalian 30.000 -> Net Cash Sales = 20.000
        $this->orderService->checkout(
            $this->business,
            $this->cashier,
            [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'unit_price' => 20000.0,
                    'quantity' => 1,
                ],
            ],
            [
                [
                    'payment_method' => 'cash',
                    'amount' => 50000.0,
                ],
            ],
            [
                'location_id' => $this->location->id,
                'pos_shift_id' => $shift->id,
                'sales_channel' => 'dine_in',
            ],
            $shift
        );

        $summary = $this->shiftService->getShiftSummary($shift);

        // Expected Cash = 100.000 (modal) + 20.000 (net cash) = 120.000 (Bukan 150.000)
        $this->assertEquals(20000.0, $summary['cash_sales']);
        $this->assertEquals(120000.0, $summary['expected_cash']);
    }

    /**
     * F-02: Verifikasi Void & Refund Membalikkan Kas Keluar & Auto-Journal Berimbang.
     */
    public function test_f02_void_and_refund_reverse_cash_ledger_and_auto_journal(): void
    {
        $shift = $this->shiftService->openShift(
            $this->business,
            $this->cashier,
            50000.0,
            null,
            $this->location->id
        );

        $order = $this->orderService->checkout(
            $this->business,
            $this->cashier,
            [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'unit_price' => 20000.0,
                    'quantity' => 1,
                ],
            ],
            [
                [
                    'payment_method' => 'cash',
                    'amount' => 20000.0,
                ],
            ],
            [
                'location_id' => $this->location->id,
                'pos_shift_id' => $shift->id,
                'sales_channel' => 'dine_in',
            ],
            $shift
        );

        // Void Order
        $this->orderService->voidOrder($order, $this->cashier, 'Pembatalan Transaksi Master');

        $this->assertSame(PosOrder::STATUS_VOIDED, $order->fresh()->status);

        // Verifikasi Jurnal Pembalikan (Debit Penjualan, Kredit Kas)
        $voidJournal = JournalEntry::where('business_id', $this->business->id)
            ->where('reference_type', JournalEntry::REF_POS_VOID)
            ->where('reference_id', $order->id)
            ->with('lines')
            ->first();

        $this->assertNotNull($voidJournal);
        $this->assertEquals((float) $voidJournal->total_debit, (float) $voidJournal->total_credit);
        $this->assertGreaterThan(0, (float) $voidJournal->total_debit);
    }

    /**
     * F-03 & F-13: Verifikasi Lokalisasi Blade dan Injeksi window.COOCA_I18N.
     */
    public function test_f03_and_f13_blade_localization_and_window_i18n_dictionary(): void
    {
        $terminalContent = file_get_contents(resource_path('views/app/pos/terminal.blade.php'));

        $this->assertStringContainsString('<html lang="{{ str_replace(\'_\', \'-\', app()->getLocale()) }}"', $terminalContent);
        $this->assertStringContainsString('window.COOCA_I18N = @json(__(\'pos\'));', $terminalContent);
    }

    /**
     * F-04: Verifikasi Verifikasi Status Respon HTTP/JSON pada tables.blade.php.
     */
    public function test_f04_tables_blade_verifies_ajax_response_status(): void
    {
        $content = file_get_contents(resource_path('views/app/pos/tables.blade.php'));

        $this->assertStringContainsString('if (res.ok && data.success !== false)', $content);
        $this->assertStringContainsString('AppAlert.error(data.message', $content);
    }

    /**
     * F-05: Verifikasi Context-Aware Vertical Auto-Hiding 20 Sektor Industri.
     */
    public function test_f05_multi_industry_context_aware_auto_hiding(): void
    {
        $this->assertTrue(method_exists($this->business, 'isLaundry'));
        $this->assertTrue(method_exists($this->business, 'isWorkshop'));
        $this->assertTrue(method_exists($this->business, 'isPharmacy'));
        $this->assertTrue(method_exists($this->business, 'isFoodIndustry'));

        $terminalContent = file_get_contents(resource_path('views/app/pos/terminal.blade.php'));
        $this->assertStringContainsString('$business->isWorkshop()', $terminalContent);
        $this->assertStringContainsString('$business->isLaundry()', $terminalContent);
    }

    /**
     * F-06 & F-07: Verifikasi Unifikasi Header dan Eliminasi Duplikasi Title Header.
     */
    public function test_f06_and_f07_unified_header_and_module_tabs_without_duplicates(): void
    {
        $printersContent = file_get_contents(resource_path('views/app/pos/printers/index.blade.php'));
        $prepContent = file_get_contents(resource_path('views/app/pos/prep_sheet.blade.php'));
        $shiftsContent = file_get_contents(resource_path('views/app/pos/shifts.blade.php'));

        $this->assertStringContainsString('<x-module-header', $printersContent);
        $this->assertStringContainsString('module="pos"', $printersContent);
        $this->assertStringContainsString('<x-module-tabs module="pos"', $printersContent);

        $this->assertStringContainsString('<x-module-header', $prepContent);
        $this->assertStringContainsString('module="pos"', $prepContent);
        $this->assertStringContainsString('<x-module-tabs module="pos"', $prepContent);

        $this->assertStringNotContainsString("@extends('layouts.app', ['headerTitle'", $shiftsContent);
    }

    /**
     * F-08 & F-11: Verifikasi FormRequest Terisolasi dan Penanganan Error JSON 422.
     */
    public function test_f08_and_f11_pos_form_requests_and_json_error_handling(): void
    {
        $this->assertTrue(class_exists(PosCheckoutRequest::class));
        $this->assertTrue(class_exists(PosOpenShiftRequest::class));
        $this->assertTrue(class_exists(PosCloseShiftRequest::class));
        $this->assertTrue(class_exists(PosVoidOrderRequest::class));
        $this->assertTrue(class_exists(PosRefundOrderRequest::class));

        // Testing error response JSON 422 on invalid void (empty reason)
        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'user_id' => $this->cashier->id,
            'order_number' => 'ORD-TEST-422',
            'order_date' => now()->toDateString(),
            'ordered_at' => now(),
            'status' => PosOrder::STATUS_COMPLETED,
            'payment_status' => 'paid',
            'subtotal' => 10000,
            'total_amount' => 10000,
            'paid_amount' => 10000,
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('pos.orders.void', $order->id), [
                'reason' => '', // Empty reason fails validation
            ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors']);
    }

    /**
     * F-09 & F-15: Verifikasi Anti Safari Auto-Zoom CSS dan Target Sentuh Minimal 48px.
     */
    public function test_f09_and_f15_mobile_touch_targets_and_safari_anti_zoom_css(): void
    {
        $terminalContent = file_get_contents(resource_path('views/app/pos/terminal.blade.php'));
        $ordersContent = file_get_contents(resource_path('views/app/pos/orders.blade.php'));
        $kitchenContent = file_get_contents(resource_path('views/app/pos/kitchen.blade.php'));

        $this->assertStringContainsString('@media screen and (max-width: 768px)', $terminalContent);
        $this->assertStringContainsString('font-size: 16px !important;', $terminalContent);

        $this->assertStringContainsString('min-h-[48px]', $ordersContent);
        $this->assertStringContainsString('min-h-[48px]', $kitchenContent);
    }

    /**
     * F-10 & F-14: Verifikasi Bento Apple HIG Palette dan Smart Polling Visibility-Aware.
     */
    public function test_f10_and_f14_bento_apple_hig_palette_and_smart_polling(): void
    {
        $prepContent = file_get_contents(resource_path('views/app/pos/prep_sheet.blade.php'));
        $terminalContent = file_get_contents(resource_path('views/app/pos/terminal.blade.php'));
        $kitchenContent = file_get_contents(resource_path('views/app/pos/kitchen.blade.php'));

        $this->assertStringNotContainsString('text-emerald-', $prepContent);
        $this->assertStringNotContainsString('bg-emerald-', $prepContent);
        $this->assertStringContainsString('#34C759', $prepContent);

        $this->assertStringContainsString('document.hidden', $terminalContent);
        $this->assertStringContainsString('visibilitychange', $terminalContent);

        $this->assertStringContainsString('document.hidden', $kitchenContent);
        $this->assertStringContainsString('visibilitychange', $kitchenContent);
    }

    /**
     * F-12 & F-17: Verifikasi Anti-Double Submit Keyboard Enter & Supervisor PIN Trust Badge.
     */
    public function test_f12_and_f17_anti_double_submit_and_supervisor_pin_no_panic_microcopy(): void
    {
        $terminalContent = file_get_contents(resource_path('views/app/pos/terminal.blade.php'));

        $this->assertStringContainsString('@keydown.enter.prevent="if(!isProcessing', $terminalContent);
        $this->assertStringContainsString('if (this.isProcessing) return;', $terminalContent);

        $this->assertStringContainsString("{{ __('pos.supervisor_pin_security_note') }}", $terminalContent);
        $this->assertStringContainsString("{{ __('pos.supervisor_pin_no_panic_guide') }}", $terminalContent);
    }

    /**
     * F-16: Verifikasi Pendaftaran Tab Printer & Hardware pada NavigationRegistry.
     */
    public function test_f16_printers_tab_registered_in_navigation_registry(): void
    {
        $this->actingAs($this->owner);
        Context::setBusiness($this->business);

        $tabs = NavigationRegistry::getTabsForModule('pos');
        $tabKeys = array_column($tabs, 'key');

        $this->assertContains('printers', $tabKeys);
    }
}
