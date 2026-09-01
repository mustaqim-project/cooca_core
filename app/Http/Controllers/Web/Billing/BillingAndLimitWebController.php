<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Billing;

use App\Domain\Billing\EntitlementService;
use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
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
        $monthlyPrice = $this->entitlementService->getMonthlyPrice();
        $annualPrice = $this->entitlementService->getAnnualPrice();
        $annualDiscountBadge = SystemSetting::get('subscription_annual_discount_badge', 'Hemat 2 Bulan');

        return view('app.billing.limits', compact('business', 'usage', 'monthlyPrice', 'annualPrice', 'annualDiscountBadge'));
    }

    /**
     * Activate or simulate upgrade to Core Plan.
     */
    public function upgrade(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        $cycle = $request->input('cycle', 'monthly');

        $this->entitlementService->upgradeToCore($business, $cycle);

        $monthlyPriceFormatted = number_format($this->entitlementService->getMonthlyPrice(), 0, ',', '.');
        $annualPriceFormatted = number_format($this->entitlementService->getAnnualPrice(), 0, ',', '.');
        $cycleName = $cycle === 'annual' ? "Core Annual (Rp{$annualPriceFormatted}/thn)" : "Core Monthly (Rp{$monthlyPriceFormatted}/bln)";

        return redirect()->route('billing.limits')
            ->with('success', "Selamat! Bisnis Anda kini aktif pada paket {$cycleName}. Seluruh kuota transaksi, produk, dan token AI telah terbuka penuh.");
    }
}
