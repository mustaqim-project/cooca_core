<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Billing;

use App\Domain\Billing\EntitlementService;
use App\Http\Controllers\Controller;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class BillingAndLimitWebController extends Controller
{
    public function __construct(
        private readonly EntitlementService $entitlementService = new EntitlementService
    ) {}

    /**
     * Display usage limits, resource consumption progress bars, and plan details.
     */
    public function index(): View
    {
        $business = Context::requireBusiness();
        $usage = $this->entitlementService->getUsageSummary($business);

        return view('app.billing.limits', compact('business', 'usage'));
    }

    /**
     * Activate or simulate upgrade to Core Plan.
     */
    public function upgrade(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        $cycle = $request->input('cycle', 'monthly');

        $this->entitlementService->upgradeToCore($business, $cycle);

        $cycleName = $cycle === 'annual' ? 'Core Annual (Rp1.290.000/thn)' : 'Core Monthly (Rp129.000/bln)';

        return redirect()->route('billing.limits')
            ->with('success', "Selamat! Bisnis Anda kini aktif pada paket {$cycleName}. Seluruh kuota transaksi, produk, dan 10 Juta token AI telah terbuka penuh.");
    }
}
