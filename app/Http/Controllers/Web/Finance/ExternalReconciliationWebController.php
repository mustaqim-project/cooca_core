<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Finance;

use App\Domain\Finance\ExternalReconciliationService;
use App\Http\Controllers\Controller;
use App\Models\ExternalAccountReconciliation;
use App\Models\Location;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Web controller for External Account Reconciliation ("Lembar Rekonsiliasi")
 * and the "Where The Money Lives" Liquidity Dashboard.
 */
final class ExternalReconciliationWebController extends Controller
{
    public function __construct(
        private readonly ExternalReconciliationService $service = new ExternalReconciliationService
    ) {}

    /**
     * Main page: liquidity dashboard + reconciliation list.
     */
    public function index(Request $request): View|JsonResponse
    {
        $business = Context::business();
        $period = $request->get('period', Carbon::now()->format('Y-m'));
        $locationId = $request->get('location_id');

        $dashboard = $this->service->buildLiquidityDashboard($business, $period);
        $reconciliations = $this->service->getReconciliationsForPeriod($business, $period, $locationId);

        $locations = Location::where('business_id', $business->id)->where('is_active', true)->orderBy('name')->get();
        $channelOptions = ExternalAccountReconciliation::channelOptions();

        if ($request->wantsJson()) {
            return response()->json([
                'success'         => true,
                'dashboard'       => $dashboard,
                'reconciliations' => $reconciliations,
            ]);
        }

        return view('app.finance.external-reconciliation.index', compact(
            'business',
            'period',
            'dashboard',
            'reconciliations',
            'locations',
            'channelOptions',
            'locationId',
        ));
    }

    /**
     * Store / update a reconciliation entry.
     */
    public function store(Request $request): JsonResponse
    {
        $business = Context::business();

        $validated = $request->validate([
            'channel_type'        => 'required|string|max:30',
            'channel_label'       => 'required|string|max:100',
            'period'              => 'required|date_format:Y-m',
            'opening_balance'     => 'required|numeric|min:0',
            'total_inflow'        => 'required|numeric|min:0',
            'total_disbursement'  => 'required|numeric|min:0',
            'closing_balance'     => 'required|numeric|min:0',
            'location_id'         => 'nullable|uuid|exists:locations,id',
            'notes'               => 'nullable|string|max:500',
        ]);

        $recon = $this->service->upsertReconciliation(
            business: $business,
            channelType: $validated['channel_type'],
            channelLabel: $validated['channel_label'],
            period: $validated['period'],
            openingBalance: (float) $validated['opening_balance'],
            totalInflow: (float) $validated['total_inflow'],
            totalDisbursement: (float) $validated['total_disbursement'],
            closingBalance: (float) $validated['closing_balance'],
            locationId: $validated['location_id'] ?? null,
            notes: $validated['notes'] ?? null,
            userId: $request->user()?->id,
        );

        return response()->json([
            'success'        => true,
            'reconciliation' => $recon,
            'has_discrepancy' => $recon->hasDiscrepancy(),
            'variance'       => $recon->variance,
            'message'        => $recon->hasDiscrepancy()
                ? "⚠️ Selisih terdeteksi: Rp " . number_format(abs($recon->variance), 0, ',', '.') . " (" . ($recon->variance > 0 ? 'surplus' : 'defisit') . ")"
                : "✅ Rekonsiliasi seimbang, tidak ada selisih.",
        ], 201);
    }

    /**
     * Get the liquidity dashboard data as JSON.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $business = Context::business();
        $period = $request->get('period', Carbon::now()->format('Y-m'));

        return response()->json([
            'success'   => true,
            'dashboard' => $this->service->buildLiquidityDashboard($business, $period),
        ]);
    }

    /**
     * Get discrepancy summary across all periods.
     */
    public function discrepancies(Request $request): JsonResponse
    {
        $business = Context::business();
        $summary = $this->service->getDiscrepancySummary($business);

        return response()->json([
            'success'      => true,
            'discrepancies' => $summary,
        ]);
    }
}
