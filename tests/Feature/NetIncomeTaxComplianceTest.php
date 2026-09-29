<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Tax\NetIncomeTaxService;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NetIncomeTaxComplianceTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private NetIncomeTaxService $taxService;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->owner = User::create([
            'name' => 'Owner Pajak Fiskal',
            'email' => 'fiskal.owner@cooca.id',
            'password' => 'password123',
            'email_verified_at' => now(),
            'phone' => '6281122334455',
        ]);

        $this->business = Business::create([
            'name' => 'PT Sinergi Berkah Abadi',
            'slug' => 'pt-sinergi-berkah-abadi',
            'tax_id' => '0987654321098765',
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_code' => BusinessSubscription::PLAN_PREMIUM_MONTHLY,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
        ]);

        $this->taxService = new NetIncomeTaxService();
    }

    public function test_corporate_tax_calculation_under_4_point_8_billion_article_31e(): void
    {
        // Omzet Rp 1 Miliar, HPP Rp 600 Juta, Beban Operasional Rp 200 Juta
        // Laba Bersih = 1M - 600jt - 200jt = Rp 200 Juta
        // PPh Badan Pasal 31E: Tarif 11% (50% dari 22%) x 200 Juta = Rp 22 Juta
        $result = $this->taxService->calculate(
            grossRevenue: 1_000_000_000.0,
            cogs: 600_000_000.0,
            operatingExpenses: 200_000_000.0,
            isCorporate: true
        );

        $this->assertEquals(1_000_000_000.0, $result['gross_revenue']);
        $this->assertEquals(600_000_000.0, $result['cogs']);
        $this->assertEquals(400_000_000.0, $result['gross_profit']);
        $this->assertEquals(40.0, $result['gross_margin_percent']);
        $this->assertEquals(200_000_000.0, $result['operating_expenses']);
        $this->assertEquals(200_000_000.0, $result['net_operating_income']);
        $this->assertEquals(20.0, $result['net_margin_percent']);
        $this->assertEquals(22_000_000.0, $result['tax_amount']);
        $this->assertEquals(178_000_000.0, $result['net_profit_after_tax']);
        $this->assertFalse($result['tax_details']['is_loss']);
        $this->assertEquals(0.11, $result['tax_details']['facility_rate']);
    }

    public function test_corporate_fiscal_loss_zero_tax(): void
    {
        // Usaha Rugi: Omzet Rp 500 Juta, HPP Rp 400 Juta, Beban Rp 150 Juta -> Rugi Rp 50 Juta
        // Pajak PPh terutang harus Rp 0 (tidak terutang PPh)
        $result = $this->taxService->calculate(
            grossRevenue: 500_000_000.0,
            cogs: 400_000_000.0,
            operatingExpenses: 150_000_000.0,
            isCorporate: true
        );

        $this->assertEquals(-50_000_000.0, $result['net_operating_income']);
        $this->assertEquals(0.0, $result['tax_amount']);
        $this->assertTrue($result['tax_details']['is_loss']);
        $this->assertEquals('net_income', $result['comparison']['recommended_scheme']);
    }

    public function test_individual_tax_progressive_rates_under_uu_hpp(): void
    {
        // Orang Pribadi: Omzet Rp 500 Jt, HPP Rp 200 Jt, Beban Rp 100 Jt -> Laba Bersih = Rp 200 Juta
        // Status PTKP TK/0 = Rp 54 Juta
        // PKP = 200 Jt - 54 Jt = Rp 146 Juta
        // Layer 1: 60 Jt x 5% = Rp 3 Juta
        // Layer 2: (146 Jt - 60 Jt = 86 Jt) x 15% = Rp 12.9 Juta
        // Total PPh = 3 Jt + 12.9 Jt = Rp 15.9 Juta
        $result = $this->taxService->calculate(
            grossRevenue: 500_000_000.0,
            cogs: 200_000_000.0,
            operatingExpenses: 100_000_000.0,
            isCorporate: false,
            ptkpStatus: 'TK/0'
        );

        $this->assertEquals(200_000_000.0, $result['net_operating_income']);
        $this->assertEquals(54_000_000.0, $result['ptkp_amount']);
        $this->assertEquals(146_000_000.0, $result['taxable_income']);
        $this->assertEquals(15_900_000.0, $result['tax_amount']);
        $this->assertEquals(184_100_000.0, $result['net_profit_after_tax']);
        $this->assertCount(2, $result['tax_details']['brackets']);
    }

    public function test_individual_nppn_norma_calculation(): void
    {
        // Omzet Rp 600 Jt, Norma 20% -> Penghasilan Neto = Rp 120 Jt
        // PTKP K/1 = Rp 63 Jt
        // PKP = 120 Jt - 63 Jt = Rp 57 Jt
        // Pajak: 57 Jt x 5% = Rp 2.85 Juta
        $result = $this->taxService->calculate(
            grossRevenue: 600_000_000.0,
            cogs: 0.0,
            operatingExpenses: 0.0,
            isCorporate: false,
            ptkpStatus: 'K/1',
            nppnRate: 0.20
        );

        $this->assertEquals(120_000_000.0, $result['tax_details']['net_norma']);
        $this->assertEquals(63_000_000.0, $result['ptkp_amount']);
        $this->assertEquals(57_000_000.0, $result['taxable_income']);
        $this->assertEquals(2_850_000.0, $result['tax_amount']);
    }

    public function test_tax_dashboard_web_view_and_simulation(): void
    {
        $this->actingAs($this->owner);
        Context::setBusiness($this->business);

        // 1. Test GET /tax dengan net income data
        $response = $this->get(route('tax.index', ['year' => 2026, 'taxpayer_type' => 'individual', 'ptkp_status' => 'TK/0']));
        $response->assertOk();
        $response->assertSee('Tax Compliance Engine');
        $response->assertSee('Pendapatan Bersih Penjualan');
        $response->assertSee('Laba Bersih Operasional');
        $response->assertSee('PPh Terutang Laba Bersih');

        // 2. Test POST /tax/simulate-net-income
        $simResponse = $this->postJson(route('tax.simulate.net_income'), [
            'gross_revenue' => 100_000_000,
            'cogs' => 50_000_000,
            'operating_expenses' => 20_000_000,
            'is_corporate' => true,
        ]);

        $simResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.gross_profit', 50000000)
            ->assertJsonPath('data.net_operating_income', 30000000)
            ->assertJsonPath('data.tax_amount', 3300000); // 11% x 30jt = 3.3jt
    }

    public function test_tax_dashboard_tabs_deep_linking_and_ui_rendering(): void
    {
        $this->actingAs($this->owner);
        Context::setBusiness($this->business);

        $tabs = ['net_income', 'umkm', 'pph21', 'payroll', 'sales'];

        foreach ($tabs as $tab) {
            $response = $this->get(route('tax.index', ['tab' => $tab]));
            $response->assertOk();
            $response->assertSee('activeSimTab', false);
            $response->assertSee('Tax Compliance Engine');
        }
    }

    public function test_export_net_income_tax_csv(): void
    {
        $this->actingAs($this->owner);
        Context::setBusiness($this->business);

        $customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'PT Mitra Niaga',
            'phone' => '081234567890',
        ]);

        Invoice::create([
            'business_id' => $this->business->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-2026-NET-01',
            'invoice_date' => '2026-09-10',
            'due_date' => '2026-09-15',
            'status' => Invoice::STATUS_PAID,
            'subtotal' => 80000000,
            'total_amount' => 80000000,
        ]);

        $response = $this->get(route('tax.export.net_income', [
            'year' => 2026,
            'taxpayer_type' => 'corporate',
        ]));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('rekap_pajak_laba_bersih', (string) $response->headers->get('content-disposition'));

        ob_start();
        $response->sendContent();
        $content = (string) ob_get_clean();

        $this->assertStringContainsString('Masa Pajak', $content);
        $this->assertStringContainsString('Pendapatan Bersih / Omzet (Rp)', $content);
        $this->assertStringContainsString('Biaya Modal / HPP COGS (Rp)', $content);
        $this->assertStringContainsString('Laba Bersih Operasional (Rp)', $content);
        $this->assertStringContainsString('PPh Terutang Laba Bersih (Rp)', $content);
        $this->assertStringContainsString('TOTAL / KONSOLIDASI TAHUNAN', $content);
    }
}
