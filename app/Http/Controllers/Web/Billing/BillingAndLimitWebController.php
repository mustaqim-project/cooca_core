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

    /**
     * Delete an uploaded storage file by the business owner to reclaim cloud quota.
     */
    public function destroyStorageFile(Request $request, \App\Models\StorageFile $storageFile): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $business = Context::requireBusiness();
        $isOwner = Context::isOwner();
        $hasBillingPerm = Context::hasPermission('billing.manage');

        abort_unless($isOwner || $hasBillingPerm, 403, 'Hanya Owner atau pengelola billing yang dapat menghapus berkas penyimpanan.');

        // IDOR Tenant Guard: Must belong to current owner or current business
        $owner = $business->users()->wherePivot('role', 'owner')->first() ?? auth()->user();
        $isAuthorized = ($storageFile->business_id === $business->id) || ($storageFile->owner_id === $owner->id);
        abort_unless($isAuthorized, 403, 'Akses ditolak. Anda tidak memiliki izin atas berkas ini.');

        $fileName = $storageFile->file_name;
        $fileSizeMb = round($storageFile->file_size / 1048576, 2);

        // Delete physical file, clean references, and soft-delete DB record
        $this->storageTrackingService->deleteFile($storageFile->file_path, $storageFile->disk);

        // Clear quota and entitlement cache
        $this->entitlementService->clearUsageCache($business);

        $successMessage = "Berkas '{$fileName}' ({$fileSizeMb} MB) berhasil dihapus. Kapasitas penyimpanan Anda telah diperbarui.";

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
     * List all tracked files for the owner/business for the Storage File Manager modal.
     */
    public function listFiles(Request $request): \Illuminate\Http\JsonResponse
    {
        $business = Context::requireBusiness();
        $owner = $business->users()->wherePivot('role', 'owner')->first() ?? auth()->user();

        $search = trim((string) $request->query('q', ''));
        $category = (string) $request->query('category', 'all');

        $query = \App\Models\StorageFile::with(['business'])
            ->where('status', \App\Models\StorageFile::STATUS_ACTIVE)
            ->where('is_temporary', false)
            ->where('category', '!=', \App\Models\StorageFile::CATEGORY_SOCIAL_MEDIA)
            ->where('module', '!=', 'social_media')
            ->where(function ($q) use ($owner, $business) {
                $q->where('owner_id', $owner->id)
                  ->orWhere('business_id', $business->id);
            })
            ->orderByDesc('file_size');

        if ($search !== '') {
            $query->where('file_name', 'like', "%{$search}%");
        }

        if ($category !== 'all' && ! empty($category)) {
            $query->where('category', $category);
        }

        $paginator = $query->paginate(15);

        $items = collect($paginator->items())->map(function (\App\Models\StorageFile $file) {
            return [
                'id' => $file->id,
                'file_name' => $file->file_name,
                'file_path' => $file->file_path,
                'category' => $file->category,
                'category_label' => $file->category_label,
                'business_name' => $file->business?->name ?? 'Akun Owner',
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

