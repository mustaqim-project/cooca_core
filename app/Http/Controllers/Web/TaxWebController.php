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
use App\Models\PayrollItem;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    /**
     * Export DJP e-Bupot 21/26 Format CSV.
     */
    public function exportEbupot(Request $request): StreamedResponse
    {
        $business = Context::requireBusiness();
        $year = (int) $request->query('year', date('Y'));
        $month = (int) $request->query('month', date('n'));

        $payrollItems = PayrollItem::where('business_id', $business->id)
            ->whereHas('payroll', function ($q) use ($year, $month) {
                $q->where('period_year', $year)
                  ->where('period_month', $month);
            })
            ->with(['user'])
            ->get();

        $filename = "ebupot_pph21_{$business->slug}_{$year}_{$month}.csv";

        return response()->streamDownload(function () use ($payrollItems, $business, $year, $month) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Microsoft Excel compatibility
            fputs($handle, "\xEF\xBB\xBF");

            // Standard Header e-Bupot 21/26
            fputcsv($handle, [
                'Masa Pajak',
                'Tahun Pajak',
                'NPWP/NIK Pemotong',
                'Nama Pemotong',
                'NPWP/NIK Penerima',
                'Nama Penerima Penghasilan',
                'Kode Objek Pajak',
                'Jumlah Penghasilan Bruto',
                'Tarif (%)',
                'Jumlah PPh Dipotong',
            ]);

            if ($payrollItems->isEmpty()) {
                // Example / template row
                fputcsv($handle, [
                    $month,
                    $year,
                    $business->tax_id ?? '0000000000000000',
                    $business->name,
                    '0000000000000000',
                    'Contoh Karyawan (Belum Ada Data Penggajian)',
                    '21-100-01',
                    '0',
                    '0.00',
                    '0',
                ]);
            } else {
                foreach ($payrollItems as $item) {
                    $taxCode = ($item->employment_type === 'daily_worker') ? '21-100-03' : '21-100-01';
                    $recipientId = $item->user?->nik ?? $item->user?->npwp ?? '0000000000000000';
                    $ratePercent = number_format(((float) $item->pph21_ter_rate) * 100, 2);

                    fputcsv($handle, [
                        $month,
                        $year,
                        $business->tax_id ?? '0000000000000000',
                        $business->name,
                        $recipientId,
                        $item->employee_name,
                        $taxCode,
                        (int) round((float) $item->gross_pay),
                        $ratePercent,
                        (int) round((float) $item->pph21_amount),
                    ]);
                }
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Export PPh Final UMKM 0.5% (PP 55/2022) Rekapitulasi Tahunan & Kode Billing.
     */
    public function exportPPhFinal(Request $request): StreamedResponse
    {
        $business = Context::requireBusiness();
        $year = (int) $request->query('year', date('Y'));
        $isIndividual = $request->query('taxpayer_type', 'individual') === 'individual';

        $summary = $this->pphFinalService->getYearlySummary($business, $year, $isIndividual);
        $filename = "rekap_pph_final_umkm_{$business->slug}_{$year}.csv";

        return response()->streamDownload(function () use ($summary, $business, $year, $isIndividual) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Masa Pajak',
                'Tahun Pajak',
                'Nama Usaha',
                'NPWP Usaha',
                'Jenis Wajib Pajak',
                'Peredaran Bruto (Omset Bulanan)',
                'Akumulasi Omset Tahunan',
                'Status Fasilitas Bebas Pajak (<500 Jt)',
                'Dasar Pengenaan Pajak (DPP)',
                'Tarif Pajak',
                'PPh Final 0.5% Terutang (Rp)',
                'Kode Akun Pajak (KAP)',
                'Kode Jenis Setor (KJS)',
            ]);

            foreach ($summary['monthly_breakdown'] as $m => $item) {
                fputcsv($handle, [
                    $m,
                    $year,
                    $business->name,
                    $business->tax_id ?? '0000000000000000',
                    $isIndividual ? 'Orang Pribadi (PP 55/2022)' : 'Badan Usaha (PT/CV)',
                    (int) round((float) $item['gross_revenue']),
                    (int) round((float) $item['cumulative_revenue']),
                    !empty($item['is_under_threshold']) ? 'Bebas Pajak (Fasilitas s.d 500 Jt)' : 'Dikenakan Pajak',
                    (int) round((float) $item['taxable_revenue']),
                    '0.5%',
                    (int) round((float) $item['tax_amount']),
                    '411128',
                    '420',
                ]);
            }

            // Total Row
            $totalRevenueYear = (float) ($summary['total_revenue_year'] ?? 0);
            $totalTaxYear = (float) ($summary['total_tax_year'] ?? 0);

            fputcsv($handle, [
                'TOTAL',
                $year,
                $business->name,
                $business->tax_id ?? '0000000000000000',
                '-',
                (int) round($totalRevenueYear),
                (int) round($totalRevenueYear),
                '-',
                (int) round($totalRevenueYear),
                '0.5%',
                (int) round($totalTaxYear),
                '411128',
                '420',
            ]);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }
}
