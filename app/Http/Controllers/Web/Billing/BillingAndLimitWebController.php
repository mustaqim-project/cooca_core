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

        // Get storage details strictly isolated to current active business
        $owner          = $business->users()->wherePivot('role', 'owner')->first() ?? auth()->user();
        $storageDetails = $this->storageTrackingService->getStorageDetails($business, $owner);

        $monthlyPrice         = $this->entitlementService->getMonthlyPrice();
        $annualPrice          = $this->entitlementService->getAnnualPrice();
        $annualDiscountBadge  = SystemSetting::get('subscription_annual_discount_badge', 'Hemat 2 Bulan');
        $tierPrices           = EntitlementService::TIER_PRICES;

        return view('app.billing.limits', compact(
            'business',
            'usage',
            'storageDetails',
            'monthlyPrice',
            'annualPrice',
            'annualDiscountBadge',
            'tierPrices'
        ));
    }

    /**
     * Activate or simulate upgrade to Core Plan (Available for local/testing or authorized billing managers).
     */
    public function upgrade(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        // Simulasi upgrade hanya diizinkan di lingkungan lokal atau pengujian otomatis
        abort_unless(
            app()->environment('local', 'testing'),
            403,
            __('billing.upgrade_simulation_local_only')
        );

        $cycle = in_array($request->input('cycle'), ['monthly', 'annual'], true)
            ? $request->input('cycle')
            : 'monthly';

        $this->entitlementService->upgradeToCore($business, $cycle);

        $monthlyPriceFormatted = number_format($this->entitlementService->getMonthlyPrice(), 0, ',', '.');
        $annualPriceFormatted  = number_format($this->entitlementService->getAnnualPrice(), 0, ',', '.');
        $cycleName = $cycle === 'annual'
            ? "Core Annual (Rp{$annualPriceFormatted}/thn)"
            : "Core Monthly (Rp{$monthlyPriceFormatted}/bln)";

        return redirect()->route('billing.limits')
            ->with('success', __('billing.upgrade_success', ['cycle' => $cycleName]));
    }

    /**
     * Reconcile physical disk storage against storage_files table strictly for the active business.
     * Triggered by the "Recalculate Storage" button on the billing limits page.
     */
    public function recalculateStorage(): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless(Context::isOwner() || Context::hasPermission('billing.manage'), 403, __('billing.only_owner_recalculate_storage'));

        $owner    = $business->users()->wherePivot('role', 'owner')->first() ?? auth()->user();

        if (! $owner) {
            return redirect()->route('billing.limits')
                ->with('error', __('billing.owner_not_found'));
        }

        // Strictly recalculate storage for this active business
        $result = $this->storageTrackingService->recalculate($owner, $business);

        // Also bust the entitlement usage summary cache so the page reflects new data immediately
        $this->entitlementService->clearUsageCache($business);

        $bizMb = $result['business_used_mb'] ?? $result['total_used_mb'];
        $message = __('billing.storage_recalculated_success', [
            'business' => $business->name,
            'scanned'  => $result['scanned_files'],
            'added'    => $result['untracked_added'],
            'cleaned'  => $result['orphaned_cleaned'],
            'used'     => $bizMb,
            'limit'    => $result['limit_gb'],
        ]);

        return redirect()->route('billing.limits')->with('success', $message);
    }

    /**
     * Delete an uploaded storage file by the business owner to reclaim cloud quota.
     * Strictly isolated by business_id to prevent any cross-tenant IDOR access.
     */
    public function destroyStorageFile(Request $request, \App\Models\StorageFile $storageFile): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $business = Context::requireBusiness();
        $isOwner = Context::isOwner();
        $hasBillingPerm = Context::hasPermission('billing.manage');

        abort_unless($isOwner || $hasBillingPerm, 403, __('billing.only_owner_delete_storage'));

        // Strict Multi-Tenant Guard: Storage file MUST strictly belong to this active business_id
        abort_unless($storageFile->business_id === $business->id, 403, __('billing.storage_file_not_owned'));

        $fileName = $storageFile->file_name;
        $fileSizeMb = round($storageFile->file_size / 1048576, 2);

        // Delete physical file, clean references scoped strictly to this business, and soft-delete DB record
        $this->storageTrackingService->deleteFile(
            filePath: $storageFile->file_path,
            disk: $storageFile->disk,
            businessId: $business->id
        );

        // Clear quota and entitlement cache
        $this->entitlementService->clearUsageCache($business);

        $successMessage = __('billing.storage_file_deleted_named', [
            'name' => $fileName,
            'size' => $fileSizeMb,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'reclaimed_mb' => $fileSizeMb,
                'file_id' => $storageFile->id,
            ]);
        }

        return redirect()->route('billing.limits')->with('success', $successMessage);
    }

    /**
     * List all tracked files strictly isolated for the active business_id for the Storage File Manager modal.
     */
    public function listFiles(Request $request): \Illuminate\Http\JsonResponse
    {
        $business = Context::requireBusiness();

        $search = trim((string) $request->query('q', ''));
        $category = (string) $request->query('category', 'all');

        // Strict Tenant Isolation: Query strictly filtered by business_id
        $query = \App\Models\StorageFile::with(['business'])
            ->where('business_id', $business->id)
            ->where('status', \App\Models\StorageFile::STATUS_ACTIVE)
            ->where('is_temporary', false)
            ->where('category', '!=', \App\Models\StorageFile::CATEGORY_SOCIAL_MEDIA)
            ->where('module', '!=', 'social_media')
            ->orderByDesc('file_size');

        if ($search !== '') {
            $query->where('file_name', 'like', "%{$search}%");
        }

        if ($category !== 'all' && ! empty($category)) {
            $query->where('category', $category);
        }

        $paginator = $query->paginate(15);

        $items = collect($paginator->items())->map(function (\App\Models\StorageFile $file) use ($business) {
            return [
                'id' => $file->id,
                'file_name' => $file->file_name,
                'file_path' => $file->file_path,
                'category' => $file->category,
                'category_label' => $file->category_label,
                'business_name' => $file->business?->name ?? $business->name,
                'file_size' => (int) $file->file_size,
                'formatted_size' => $file->formatted_size,
                'uploaded_at' => $file->uploaded_at?->format('d M Y H:i') ?? $file->created_at?->format('d M Y H:i'),
                'delete_url' => route('billing.storage.files.destroy', $file->id),
            ];
        });

        return response()->json([
            'data' => $items,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
        ]);
    }
}

