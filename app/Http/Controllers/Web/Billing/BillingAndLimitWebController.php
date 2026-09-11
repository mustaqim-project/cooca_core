<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Billing;

use App\Domain\Billing\EntitlementService;
use App\Domain\Storage\StorageTrackingService;
use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class BillingAndLimitWebController extends Controller
{
    public function __construct(
        private readonly EntitlementService $entitlementService = new EntitlementService,
        private readonly StorageTrackingService $storageTrackingService = new StorageTrackingService
    ) {}

    /**
     * Display usage limits, resource consumption progress bars, and plan details.
     */
    public function index(): View
    {
        $business = Context::requireBusiness();
        $usage    = $this->entitlementService->getUsageSummary($business);

        // Get owner for storage details
        $owner          = $business->users()->wherePivot('role', 'owner')->first();
        $storageDetails = $owner
            ? $this->storageTrackingService->getStorageDetails($owner)
            : null;

        $monthlyPrice         = $this->entitlementService->getMonthlyPrice();
        $annualPrice          = $this->entitlementService->getAnnualPrice();
        $annualDiscountBadge  = SystemSetting::get('subscription_annual_discount_badge', 'Hemat 2 Bulan');

        return view('app.billing.limits', compact(
            'business',
            'usage',
            'storageDetails',
            'monthlyPrice',
            'annualPrice',
            'annualDiscountBadge'
        ));
    }

    /**
     * Activate or simulate upgrade to Core Plan.
     */
    public function upgrade(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        $cycle    = $request->input('cycle', 'monthly');

        $this->entitlementService->upgradeToCore($business, $cycle);

        $monthlyPriceFormatted = number_format($this->entitlementService->getMonthlyPrice(), 0, ',', '.');
        $annualPriceFormatted  = number_format($this->entitlementService->getAnnualPrice(), 0, ',', '.');
        $cycleName = $cycle === 'annual'
            ? "Core Annual (Rp{$annualPriceFormatted}/thn)"
            : "Core Monthly (Rp{$monthlyPriceFormatted}/bln)";

        return redirect()->route('billing.limits')
            ->with('success', "Selamat! Bisnis Anda kini aktif pada paket {$cycleName}. Seluruh kuota transaksi, produk, dan token AI telah terbuka penuh.");
    }

    /**
     * Reconcile physical disk storage against storage_files table for the current owner.
     * Triggered by the "Recalculate Storage" button on the billing limits page.
     */
    public function recalculateStorage(): RedirectResponse
    {
        $business = Context::requireBusiness();
        $owner    = $business->users()->wherePivot('role', 'owner')->first();

        if (! $owner) {
            return redirect()->route('billing.limits')
                ->with('error', 'Owner akun tidak ditemukan. Tidak dapat menghitung ulang storage.');
        }

        $result = $this->storageTrackingService->recalculate($owner);

        // Also bust the entitlement usage summary cache so the page reflects new data immediately
        $this->entitlementService->clearUsageCache($business);

        $message = "Kalkulasi storage selesai. "
            . "File dipindai: {$result['scanned_files']} | "
            . "Baru ditambah: {$result['untracked_added']} | "
            . "Orphan dibersihkan: {$result['orphaned_cleaned']} | "
            . "Total digunakan: {$result['total_used_mb']} MB / {$result['limit_gb']} GB.";

        return redirect()->route('billing.limits')->with('success', $message);
    }
}
