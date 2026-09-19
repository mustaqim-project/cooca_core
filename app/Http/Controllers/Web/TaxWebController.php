<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Billing\EntitlementService;
use App\Domain\HRM\BPJSCalculationService;
use App\Domain\HRM\PayrollCalculationService;
use App\Domain\HRM\THRCalculationService;
use App\Domain\Tax\PPh21CalculationService;
use App\Domain\Tax\PPhFinalUMKMService;
use App\Domain\Tax\SalesTaxService;
use App\Http\Controllers\Controller;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class TaxWebController extends Controller
{
    public function __construct(
        private readonly PPh21CalculationService $pph21Service,
        private readonly PPhFinalUMKMService $pphFinalService,
        private readonly SalesTaxService $salesTaxService,
        private readonly BPJSCalculationService $bpjsService,
        private readonly THRCalculationService $thrService,
        private readonly PayrollCalculationService $payrollService,
        private readonly EntitlementService $entitlementService
    ) {}

    /**
     * Halaman Dashboard Pajak & Kepatuhan UMKM.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();
        $currentYear = (int) $request->query('year', date('Y'));
        $isIndividual = $request->query('taxpayer_type', 'individual') === 'individual';

        // Rekapitulasi Tahunan PPh Final UMKM 0.5% (PP 55/2022)
        $umkmSummary = $this->pphFinalService->getYearlySummary($business, $currentYear, $isIndividual);

        // Ringkasan Entitlement
        $usageSummary = $this->entitlementService->getUsageSummary($business);

        return view('app.tax.index', [
            'business' => $business,
            'currentYear' => $currentYear,
            'isIndividual' => $isIndividual,
            'umkmSummary' => $umkmSummary,
            'usageSummary' => $usageSummary,
            'canCalculatePPh21' => $this->entitlementService->canCalculatePPh21($business),
            'canCalculateBPJS' => $this->entitlementService->canCalculateBPJS($business),
            'canCalculateTHR' => $this->entitlementService->canCalculateTHR($business),
        ]);
    }

    /**
     * AJAX Endpoint: Simulasi PPh 21 TER (PP 58/2023) atau Rekonsiliasi Desember.
     */
    public function simulatePPh21(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'gross_wage' => 'required|numeric|min:0',
            'ptkp_status' => 'required|string|in:TK/0,TK/1,TK/2,TK/3,K/0,K/1,K/2,K/3',
            'calc_type' => 'required|string|in:monthly_ter,december,daily_worker',
            'cumulative_wage' => 'nullable|numeric|min:0',
            'annual_deductions' => 'nullable|numeric|min:0',
            'tax_paid_before' => 'nullable|numeric|min:0',
        ]);

        $gross = (float) $validated['gross_wage'];
        $ptkp = $validated['ptkp_status'];

        if ($validated['calc_type'] === 'monthly_ter') {
            $result = $this->pph21Service->calculateMonthlyTer($gross, $ptkp);
            return response()->json(['success' => true, 'data' => $result]);
        }

        if ($validated['calc_type'] === 'december') {
            $deductions = (float) ($validated['annual_deductions'] ?? 0.0);
            $taxPaid = (float) ($validated['tax_paid_before'] ?? 0.0);
            $result = $this->pph21Service->calculateDecemberReconciliation($gross, $deductions, $taxPaid, $ptkp);
            return response()->json(['success' => true, 'data' => $result]);
        }

        // Daily worker case
        $cumulative = (float) ($validated['cumulative_wage'] ?? $gross);
        $result = $this->pph21Service->calculateDailyWorker($gross, $cumulative);
        return response()->json(['success' => true, 'data' => $result]);
    }

    /**
     * AJAX Endpoint: Simulasi PPh Final UMKM 0.5% (PP 55/2022).
     */
    public function simulateUmkm(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'monthly_revenue' => 'required|numeric|min:0',
            'prior_cumulative' => 'required|numeric|min:0',
            'is_individual' => 'required|boolean',
        ]);

        $result = $this->pphFinalService->calculate(
            (float) $validated['monthly_revenue'],
            (float) $validated['prior_cumulative'],
            (bool) $validated['is_individual']
        );

        return response()->json(['success' => true, 'data' => $result]);
    }

    /**
     * AJAX Endpoint: Simulasi Pajak Transaksi (PB1 Restoran 10% / PPN 11% / 12%).
     */
    public function simulateSales(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'service_charge_rate' => 'nullable|numeric|min:0|max:1',
            'tax_type' => 'required|string|in:none,pb1,ppn_11,ppn_12',
            'is_inclusive' => 'required|boolean',
        ]);

        $result = $this->salesTaxService->calculate(
            (float) $validated['subtotal'],
            (float) ($validated['discount'] ?? 0.0),
            (float) ($validated['service_charge_rate'] ?? 0.0),
            $validated['tax_type'],
            (bool) $validated['is_inclusive']
        );

        return response()->json(['success' => true, 'data' => $result]);
    }

    /**
     * AJAX Endpoint: Simulasi Penggajian & THR Komprehensif (BPJS + THR + Pinjaman + Pajak).
     */
    public function simulatePayroll(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employment_type' => 'required|string|in:permanent,contract,daily_worker',
            'base_salary' => 'nullable|numeric|min:0',
            'daily_rate' => 'nullable|numeric|min:0',
            'days_worked' => 'nullable|integer|min:1',
            'fixed_allowances' => 'nullable|numeric|min:0',
            'variable_allowances' => 'nullable|numeric|min:0',
            'overtime_pay' => 'nullable|numeric|min:0',
            'commissions' => 'nullable|numeric|min:0',
            'loan_deduction' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'ptkp_status' => 'nullable|string|in:TK/0,TK/1,TK/2,TK/3,K/0,K/1,K/2,K/3',
            'bpjs_tk_enabled' => 'nullable|boolean',
            'bpjs_kes_enabled' => 'nullable|boolean',
            'include_thr' => 'nullable|boolean',
            'join_date' => 'nullable|date',
            'prior_cumulative' => 'nullable|numeric|min:0',
        ]);

        if ($validated['employment_type'] === 'daily_worker') {
            $result = $this->payrollService->calculateDailyWorkerPayroll(
                (float) ($validated['daily_rate'] ?? 150_000.0),
                (int) ($validated['days_worked'] ?? 25),
                (float) ($validated['overtime_pay'] ?? 0.0),
                (float) ($validated['commissions'] ?? 0.0),
                (float) ($validated['loan_deduction'] ?? 0.0),
                (float) ($validated['prior_cumulative'] ?? 0.0)
            );
            return response()->json(['success' => true, 'data' => $result]);
        }

        $result = $this->payrollService->calculateMonthlyPayroll(
            (float) ($validated['base_salary'] ?? 0.0),
            (float) ($validated['fixed_allowances'] ?? 0.0),
            (float) ($validated['variable_allowances'] ?? 0.0),
            (float) ($validated['overtime_pay'] ?? 0.0),
            (float) ($validated['commissions'] ?? 0.0),
            (float) ($validated['loan_deduction'] ?? 0.0),
            (float) ($validated['other_deductions'] ?? 0.0),
            $validated['ptkp_status'] ?? 'TK/0',
            (bool) ($validated['bpjs_tk_enabled'] ?? true),
            (bool) ($validated['bpjs_kes_enabled'] ?? true),
            BPJSCalculationService::JKK_RATE_VERY_LOW,
            (bool) ($validated['include_thr'] ?? false),
            $validated['join_date'] ?? null
        );

        return response()->json(['success' => true, 'data' => $result]);
    }
}
