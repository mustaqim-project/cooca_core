<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Billing\EntitlementService;
use App\Domain\HRM\BPJSCalculationService;
use App\Domain\HRM\PayrollCalculationService;
use App\Domain\HRM\THRCalculationService;
use App\Domain\Tax\PPh21CalculationService;
use App\Domain\Tax\PPhFinalUMKMService;
use App\Domain\Tax\SalesTaxService;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxAndHRMComplianceTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name' => 'Owner SaaS Test',
            'email' => 'owner_saas_test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'phone' => '6281234567891',
        ]);

        $this->business = Business::create([
            'name' => 'Cooca Bakery Test',
            'slug' => 'cooca-bakery-test',
            'email' => 'bakery@example.com',
            'phone' => '6281234567892',
            'address' => 'Jl. Merdeka No. 1',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->user->id, [
            'role' => 'owner',
            'is_active' => true,
        ]);
    }
    public function test_bpjs_calculation_precision(): void
    {
        $service = new BPJSCalculationService();
        $calc = $service->calculate(10_000_000.0, true, true, BPJSCalculationService::JKK_RATE_VERY_LOW);

        // JHT: Company 3.7% = 370.000, Employee 2% = 200.000
        $this->assertEquals(370_000.0, $calc['bpjs_tk']['jht_company']);
        $this->assertEquals(200_000.0, $calc['bpjs_tk']['jht_employee']);

        // JKM: Company 0.3% = 30.000
        $this->assertEquals(30_000.0, $calc['bpjs_tk']['jkm_company']);

        // JKK: Company 0.24% = 24.000
        $this->assertEquals(24_000.0, $calc['bpjs_tk']['jkk_company']);

        // JP: Max Cap 10.042.300 -> 10jt di bawah cap -> Company 2% = 200.000, Employee 1% = 100.000
        $this->assertEquals(200_000.0, $calc['bpjs_tk']['jp_company']);
        $this->assertEquals(100_000.0, $calc['bpjs_tk']['jp_employee']);

        // BPJS Kes: Company 4% = 400.000, Employee 1% = 100.000
        $this->assertEquals(400_000.0, $calc['bpjs_kes']['kes_company']);
        $this->assertEquals(100_000.0, $calc['bpjs_kes']['kes_employee']);

        // Total
        $this->assertEquals(1_024_000.0, $calc['summary']['total_company_paid']);
        $this->assertEquals(400_000.0, $calc['summary']['total_employee_deduction']);
    }

    public function test_thr_prorata_based_on_join_date(): void
    {
        $service = new THRCalculationService();
        $cutoff = Carbon::parse('2026-04-10');

        // Case 1: Masa kerja >= 12 bulan (bergabung 15 bulan lalu) -> 1 bulan penuh
        $joinFull = Carbon::parse('2025-01-10');
        $calcFull = $service->calculate($joinFull, $cutoff, 6_000_000.0);
        $this->assertTrue($calcFull['is_eligible']);
        $this->assertTrue($calcFull['is_full_year']);
        $this->assertEquals(6_000_000.0, $calcFull['thr_amount']);

        // Case 2: Masa kerja 6 bulan (bergabung 2025-10-10) -> Prorata 6/12 * 6jt = 3jt
        $joinHalf = Carbon::parse('2025-10-10');
        $calcHalf = $service->calculate($joinHalf, $cutoff, 6_000_000.0);
        $this->assertTrue($calcHalf['is_eligible']);
        $this->assertFalse($calcHalf['is_full_year']);
        $this->assertEquals(3_000_000.0, $calcHalf['thr_amount']);

        // Case 3: Masa kerja < 1 bulan (bergabung 2026-03-25, selisih 16 hari) -> Rp 0
        $joinNew = Carbon::parse('2026-03-25');
        $calcNew = $service->calculate($joinNew, $cutoff, 6_000_000.0);
        $this->assertFalse($calcNew['is_eligible']);
        $this->assertEquals(0.0, $calcNew['thr_amount']);
    }

    public function test_pph21_ter_and_daily_worker_calculation(): void
    {
        $service = new PPh21CalculationService();

        // Kategori TER A (TK/0):
        // Upah 6.500.000 berada di range 6.300.000 - 6.750.000 -> Tarif 1.0% = 65.000
        $ter = $service->calculateMonthlyTer(6_500_000.0, 'TK/0');
        $this->assertEquals('A', $ter['ter_category']);
        $this->assertEquals(0.01, $ter['ter_rate']);
        $this->assertEquals(65_000.0, $ter['pph21_amount']);

        // Di bawah 5.400.000 (TK/0) -> 0%
        $terZero = $service->calculateMonthlyTer(5_000_000.0, 'TK/0');
        $this->assertEquals(0.0, $terZero['ter_rate']);
        $this->assertEquals(0.0, $terZero['pph21_amount']);

        // Daily Worker: Upah 300rb kumulatif 3jt -> Bebas Pajak
        $dwFree = $service->calculateDailyWorker(300_000.0, 3_000_000.0);
        $this->assertEquals(0.0, $dwFree['pph21_daily_amount']);

        // Daily Worker: Upah 600rb kumulatif 3jt -> (600rb - 450rb) * 0.5% = 750
        $dwTax = $service->calculateDailyWorker(600_000.0, 3_000_000.0);
        $this->assertEquals(750.0, $dwTax['pph21_daily_amount']);
    }

    public function test_pph_final_umkm_500_million_tax_free_threshold(): void
    {
        $service = new PPhFinalUMKMService();

        // 1. Orang Pribadi masih di bawah 500 juta -> Bebas Pajak
        $res1 = $service->calculate(300_000_000.0, 100_000_000.0, true);
        $this->assertTrue($res1['is_under_threshold']);
        $this->assertEquals(0.0, $res1['tax_amount']);
        $this->assertEquals(100_000_000.0, $res1['threshold_remaining']);

        // 2. Bulan transisi (sebelumnya 450jt, bulan ini 150jt -> kumulatif 600jt)
        // Yang kena pajak hanya 100jt * 0.5% = 500.000
        $res2 = $service->calculate(150_000_000.0, 450_000_000.0, true);
        $this->assertEquals(100_000_000.0, $res2['taxable_revenue']);
        $this->assertEquals(500_000.0, $res2['tax_amount']);

        // 3. Badan Usaha (PT/CV) -> Tanpa threshold bebas pajak
        $res3 = $service->calculate(100_000_000.0, 0.0, false);
        $this->assertEquals(100_000_000.0, $res3['taxable_revenue']);
        $this->assertEquals(500_000.0, $res3['tax_amount']);
    }

    public function test_entitlement_tier_limits_and_feature_gates(): void
    {
        $entitlement = app(EntitlementService::class);
        $business = $this->business;

        $sub = $entitlement->getSubscription($business);

        // Simulasi Tier Free
        $sub->update(['plan_code' => BusinessSubscription::PLAN_FREE, 'status' => BusinessSubscription::STATUS_ACTIVE]);
        $this->assertEquals(BusinessSubscription::TIER_FREE, $sub->fresh()->getTier());
        $this->assertEquals(10, $entitlement->getProductLimit($business));
        $this->assertEquals(3, $entitlement->getRecipeLimit($business));
        $this->assertFalse($entitlement->canCalculateBPJS($business));
        $this->assertFalse($entitlement->canCalculateTHR($business));
        $this->assertFalse($entitlement->canCalculatePPh21($business));

        // Simulasi Tier Premium
        $sub->update(['plan_code' => BusinessSubscription::PLAN_PREMIUM_MONTHLY, 'status' => BusinessSubscription::STATUS_ACTIVE]);
        $this->assertEquals(BusinessSubscription::TIER_PREMIUM, $sub->fresh()->getTier());
        $this->assertNull($entitlement->getProductLimit($business)); // Unlimited
        $this->assertTrue($entitlement->canCalculateBPJS($business));
        $this->assertTrue($entitlement->canCalculateTHR($business));
        $this->assertTrue($entitlement->canManageEmployeeLoans($business));
        $this->assertTrue($entitlement->canManageDailyWorkers($business));
        $this->assertFalse($entitlement->canCalculatePPh21($business)); // PPh 21 TER is Prestige tier

        // Simulasi Tier Prestige
        $sub->update(['plan_code' => BusinessSubscription::PLAN_PRESTIGE_MONTHLY, 'status' => BusinessSubscription::STATUS_ACTIVE]);
        $this->assertEquals(BusinessSubscription::TIER_PRESTIGE, $sub->fresh()->getTier());
        $this->assertTrue($entitlement->canCalculatePPh21($business));
        $this->assertTrue($entitlement->canAutomatePayrollWhatsApp($business));

        // Kembalikan status subscription
        $sub->update(['plan_code' => BusinessSubscription::PLAN_FREE]);
    }

    public function test_tax_dashboard_web_and_simulation_api(): void
    {
        $business = $this->business;
        $user = $this->user;

        $this->actingAs($user);
        Context::setBusiness($business);

        // 1. Test GET /tax
        $response = $this->get('/tax');
        $response->assertStatus(200);
        $response->assertSee('Tax Compliance Engine');
        $response->assertSee('PP 55/2022');

        // 2. Test AJAX POST /tax/simulate-umkm
        $simUmkm = $this->postJson('/tax/simulate-umkm', [
            'monthly_revenue' => 50_000_000,
            'prior_cumulative' => 200_000_000,
            'is_individual' => true,
        ]);
        $simUmkm->assertStatus(200)->assertJson(['success' => true]);

        // 3. Test AJAX POST /tax/simulate-pph21
        $simPph21 = $this->postJson('/tax/simulate-pph21', [
            'calc_type' => 'monthly_ter',
            'gross_wage' => 7_000_000,
            'ptkp_status' => 'TK/0',
        ]);
        $simPph21->assertStatus(200)->assertJson(['success' => true]);

        // 4. Test AJAX POST /tax/simulate-sales
        $simSales = $this->postJson('/tax/simulate-sales', [
            'subtotal' => 100_000,
            'discount' => 10_000,
            'service_charge_rate' => 0.05,
            'tax_type' => 'pb1',
            'is_inclusive' => false,
        ]);
        $simSales->assertStatus(200)->assertJson(['success' => true]);
    }
}
