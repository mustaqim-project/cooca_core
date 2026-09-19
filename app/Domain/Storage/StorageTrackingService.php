<?php

declare(strict_types=1);

namespace App\Domain\Storage;

use App\Models\Business;
use App\Models\StorageFile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class StorageTrackingService
{
    public function __construct(
        private readonly OwnerStorageQuotaService $ownerQuotaService = new OwnerStorageQuotaService
    ) {}

    /**
     * Track a newly stored file into storage_files table.
     * Prevents double-counting by keying uniquely on [disk, file_path].
     */
    public function recordUpload(
        UploadedFile|string $file,
        string $filePath,
        string $category,
        string $module,
        User $owner,
        ?Business $business = null,
        ?User $uploader = null,
        string $disk = 'public',
        ?string $fileName = null,
        ?string $mimeType = null,
        ?int $fileSize = null,
        bool $isTemporary = false,
        ?array $metadata = null
    ): StorageFile {
        if ($file instanceof UploadedFile) {
            $fileName = $fileName ?? $file->getClientOriginalName();
            $mimeType = $mimeType ?? ($file->getClientMimeType() ?: $file->getMimeType());
            $fileSize = $fileSize ?? (int) $file->getSize();
        }

        if ($fileSize === null || $fileSize <= 0) {
            if (Storage::disk($disk)->exists($filePath)) {
                $fileSize = (int) Storage::disk($disk)->size($filePath);
            } else {
                $fileSize = 0;
            }
        }

        if ($fileName === null) {
            $fileName = basename($filePath);
        }

        if ($mimeType === null && Storage::disk($disk)->exists($filePath)) {
            $mimeType = Storage::disk($disk)->mimeType($filePath) ?: 'application/octet-stream';
        }

        $storageFile = StorageFile::withTrashed()->updateOrCreate(
            [
                'disk' => $disk,
                'file_path' => $filePath,
            ],
            [
                'owner_id' => $owner->id,
                'business_id' => $business?->id,
                'user_id' => $uploader?->id ?? auth()->id() ?? $owner->id,
                'file_name' => $fileName,
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
                'category' => $category,
                'module' => $module,
                'is_temporary' => $isTemporary,
                'status' => StorageFile::STATUS_ACTIVE,
                'metadata' => $metadata,
                'uploaded_at' => Carbon::now(),
                'deleted_at' => null,
            ]
        );

        $this->ownerQuotaService->clearSummaryCache($owner);

        return $storageFile;
    }

    /**
     * Mark a file as deleted when deleted from storage disk.
     * Enforces strict multi-tenant isolation by business_id when provided.
     */
    public function recordDeletion(string $filePath, string $disk = 'public', ?string $businessId = null): ?StorageFile
    {
        $query = StorageFile::where('disk', $disk)
            ->where('file_path', $filePath);

        if ($businessId !== null) {
            $query->where('business_id', $businessId);
        }

        $storageFile = $query->first();

        if ($storageFile) {
            $storageFile->update([
                'status' => StorageFile::STATUS_DELETED,
                'deleted_at' => Carbon::now(),
            ]);

            if ($storageFile->owner) {
                $this->ownerQuotaService->clearSummaryCache($storageFile->owner);
            }
        }

        return $storageFile;
    }

    /**
     * Physically delete file from disk and mark as deleted in database.
     * Enforces strict multi-tenant isolation by business_id when provided.
     */
    public function deleteFile(string $filePath, string $disk = 'public', ?string $businessId = null): bool
    {
        if (Storage::disk($disk)->exists($filePath)) {
            Storage::disk($disk)->delete($filePath);
        }

        $storageFile = $this->recordDeletion($filePath, $disk, $businessId);
        if ($storageFile) {
            $this->cleanReferencingModels($filePath, (string) $storageFile->category, $storageFile->business_id ?? $businessId);
        }

        return true;
    }

    /**
     * Clean up any model foreign keys / file references when a file is deleted.
     * Enforces strict multi-tenant isolation by business_id.
     */
    public function cleanReferencingModels(string $filePath, string $category, ?string $businessId = null): void
    {
        switch ($category) {
            case StorageFile::CATEGORY_PRODUCT_IMAGE:
                $query = \App\Models\Product::where('image_path', $filePath);
                if ($businessId !== null) {
                    $query->where('business_id', $businessId);
                }
                $query->update(['image_path' => null]);
                break;

            case StorageFile::CATEGORY_BUSINESS_LOGO:
                $query = \App\Models\Business::where('logo_path', $filePath);
                if ($businessId !== null) {
                    $query->where('id', $businessId);
                }
                $query->update(['logo_path' => null]);
                break;

            case StorageFile::CATEGORY_QRIS:
                $query = \App\Models\CommercePaymentMethod::where('qris_image_path', $filePath);
                if ($businessId !== null) {
                    $query->where('business_id', $businessId);
                }
                $query->update(['qris_image_path' => null]);
                break;

            case StorageFile::CATEGORY_EXPENSE_RECEIPT:
                $query = \App\Models\Expense::where('receipt_image_path', $filePath);
                if ($businessId !== null) {
                    $query->where('business_id', $businessId);
                }
                $query->update(['receipt_image_path' => null]);
                break;

            case StorageFile::CATEGORY_COMMUNITY_IMAGE:
                $query = \App\Models\CommunityPost::where('image_path', $filePath);
                if ($businessId !== null) {
                    $query->where('business_id', $businessId);
                }
                $query->update(['image_path' => null]);
                break;

            case StorageFile::CATEGORY_FEEDBACK_ATTACHMENT:
                $query = \App\Models\BugReport::where('attachment_path', $filePath);
                if ($businessId !== null) {
                    $query->where('business_id', $businessId);
                }
                $query->update(['attachment_path' => null]);
                break;

            case StorageFile::CATEGORY_OWNER_AVATAR:
                \App\Models\User::where('avatar', $filePath)->update(['avatar' => null]);
                break;

            case StorageFile::CATEGORY_LANDING_PAGE_IMAGE:
                $landingQuery = \App\Models\BusinessLandingPage::where(function ($q) use ($filePath) {
                    $q->where('logo_image', 'like', "%{$filePath}%")
                      ->orWhere('hero_image', 'like', "%{$filePath}%")
                      ->orWhere('about_image', 'like', "%{$filePath}%")
                      ->orWhere('og_image', 'like', "%{$filePath}%");
                });
                if ($businessId !== null) {
                    $landingQuery->where('business_id', $businessId);
                }
                $pages = $landingQuery->get();

                foreach ($pages as $page) {
                    $updates = [];
                    if (str_contains((string) $page->logo_image, $filePath)) $updates['logo_image'] = null;
                    if (str_contains((string) $page->hero_image, $filePath)) $updates['hero_image'] = null;
                    if (str_contains((string) $page->about_image, $filePath)) $updates['about_image'] = null;
                    if (str_contains((string) $page->og_image, $filePath)) $updates['og_image'] = null;
                    if (! empty($updates)) {
                        $page->update($updates);
                    }
                }
                break;
        }
    }

    /**
     * Check if an owner has sufficient quota remaining to upload additional bytes.
     */
    public function canUpload(User $owner, int $additionalBytes = 0): bool
    {
        return $this->ownerQuotaService->canUpload($owner, $additionalBytes);
    }

    /**
     * Validate upload quota before storing files; throws ValidationException if exceeded.
     */
    public function assertCanUpload(User $owner, int $additionalBytes = 0, string $attribute = 'file'): void
    {
        if (! $this->canUpload($owner, $additionalBytes)) {
            $summary = $this->ownerQuotaService->getSummary($owner);
            $limitGb = $summary['limit_gb'];
            $usedMb = $summary['used_mb'];
            $requiredMb = round($additionalBytes / 1048576, 2);

            throw ValidationException::withMessages([
                $attribute => "Kapasitas penyimpanan cloud Owner tidak mencukupi (Kuota: {$limitGb} GB, Terpakai: {$usedMb} MB, Dibutuhkan: +{$requiredMb} MB). Silakan upgrade kapasitas storage Anda di menu Billing.",
            ]);
        }
    }

    /**
     * Reconcile physical storage on disk with storage_files table:
     * 1. Detect untracked physical files and create DB records with actual file sizes.
     * 2. Detect orphaned DB records (physical file missing) and mark them deleted.
     * 3. Sync byte count differences.
     * When $targetBusiness is provided, scans and reconciles strictly for that business.
     */
    public function recalculate(User $owner, ?Business $targetBusiness = null): array
    {
        $owner->loadMissing('businesses');
        $businesses = $targetBusiness ? collect([$targetBusiness]) : $owner->businesses;
        $trackedCount = 0;
        $untrackedAdded = 0;
        $orphanedCleaned = 0;

        $activePathsOnDisk = [];

        foreach ($businesses as $business) {
            $slug = TenantStorage::slugForBusiness($business);
            $dirsToScan = array_filter([
                "businesses/{$business->id}",
                $slug ? "bisnis/{$slug}" : null,
                "qris/{$business->id}",
            ]);

            foreach ($dirsToScan as $dir) {
                if (Storage::disk('public')->exists($dir)) {
                    $files = Storage::disk('public')->allFiles($dir);
                    foreach ($files as $file) {
                        if (str_contains($file, 'social-media')) {
                            continue;
                        }
                        $activePathsOnDisk[] = $file;
                        $size = (int) Storage::disk('public')->size($file);
                        $category = $this->detectCategoryFromPath($file);
                        $module = $this->detectModuleFromCategory($category);

                        $existing = StorageFile::withTrashed()
                            ->where('disk', 'public')
                            ->where('file_path', $file)
                            ->first();

                        if (! $existing) {
                            StorageFile::create([
                                'owner_id' => $owner->id,
                                'business_id' => $business->id,
                                'user_id' => $owner->id,
                                'file_name' => basename($file),
                                'file_path' => $file,
                                'disk' => 'public',
                                'mime_type' => Storage::disk('public')->mimeType($file) ?: null,
                                'file_size' => $size,
                                'category' => $category,
                                'module' => $module,
                                'is_temporary' => false,
                                'status' => StorageFile::STATUS_ACTIVE,
                                'uploaded_at' => Carbon::createFromTimestamp(Storage::disk('public')->lastModified($file)),
                            ]);
                            $untrackedAdded++;
                        } else {
                            $needsUpdate = false;
                            $updateData = [];

                            if ($existing->file_size !== $size) {
                                $updateData['file_size'] = $size;
                                $needsUpdate = true;
                            }
                            if ($existing->status !== StorageFile::STATUS_ACTIVE || $existing->deleted_at !== null) {
                                $updateData['status'] = StorageFile::STATUS_ACTIVE;
                                $updateData['deleted_at'] = null;
                                $needsUpdate = true;
                            }
                            if ($existing->owner_id !== $owner->id) {
                                $updateData['owner_id'] = $owner->id;
                                $needsUpdate = true;
                            }
                            if ($existing->business_id !== $business->id) {
                                $updateData['business_id'] = $business->id;
                                $needsUpdate = true;
                            }

                            if ($needsUpdate) {
                                $existing->update($updateData);
                            }
                            $trackedCount++;
                        }
                    }
                }
            }

            // Check business logo specifically if outside scanned directories
            if ($business->logo_path && ! in_array($business->logo_path, $activePathsOnDisk, true)) {
                if (Storage::disk('public')->exists($business->logo_path)) {
                    $activePathsOnDisk[] = $business->logo_path;
                    $size = (int) Storage::disk('public')->size($business->logo_path);
                    $this->recordUpload(
                        file: $business->logo_path,
                        filePath: $business->logo_path,
                        category: StorageFile::CATEGORY_BUSINESS_LOGO,
                        module: 'settings',
                        owner: $owner,
                        business: $business,
                        fileSize: $size
                    );
                    $trackedCount++;
                }
            }
        }

        // Check user avatar only if full owner recalculation (not scoped to single business)
        if (! $targetBusiness && $owner->avatar && ! str_starts_with($owner->avatar, 'http') && Storage::disk('public')->exists($owner->avatar)) {
            $activePathsOnDisk[] = $owner->avatar;
            $size = (int) Storage::disk('public')->size($owner->avatar);
            $this->recordUpload(
                file: $owner->avatar,
                filePath: $owner->avatar,
                category: StorageFile::CATEGORY_OWNER_AVATAR,
                module: 'profile',
                owner: $owner,
                fileSize: $size
            );
            $trackedCount++;
        }

        // Detect orphaned records for this owner / business (files in DB that don't exist physically)
        $ownerFilesQuery = StorageFile::where('owner_id', $owner->id)
            ->where('status', StorageFile::STATUS_ACTIVE)
            ->whereNull('deleted_at');

        if ($targetBusiness) {
            $ownerFilesQuery->where('business_id', $targetBusiness->id);
        }

        $ownerFiles = $ownerFilesQuery->get();

        foreach ($ownerFiles as $file) {
            if (! Storage::disk($file->disk)->exists($file->file_path)) {
                $file->update([
                    'status' => StorageFile::STATUS_DELETED,
                    'deleted_at' => Carbon::now(),
                ]);
                $orphanedCleaned++;
            }
        }

        $this->ownerQuotaService->clearSummaryCache($owner);
        $summary = $this->ownerQuotaService->getSummary($owner, true);

        $businessUsedBytes = $targetBusiness
            ? (int) StorageFile::where('business_id', $targetBusiness->id)
                ->where('status', StorageFile::STATUS_ACTIVE)
                ->where('is_temporary', false)
                ->where('category', '!=', StorageFile::CATEGORY_SOCIAL_MEDIA)
                ->where('module', '!=', 'social_media')
                ->sum('file_size')
            : (int) $summary['used_bytes'];

        return [
            'owner_id' => $owner->id,
            'owner_name' => $owner->name,
            'target_business_id' => $targetBusiness?->id,
            'scanned_files' => count($activePathsOnDisk),
            'untracked_added' => $untrackedAdded,
            'orphaned_cleaned' => $orphanedCleaned,
            'total_used_bytes' => $summary['used_bytes'],
            'total_used_mb' => $summary['used_mb'],
            'business_used_bytes' => $businessUsedBytes,
            'business_used_mb' => round($businessUsedBytes / 1048576, 2),
            'limit_gb' => $summary['limit_gb'],
            'percentage' => $summary['percentage'],
        ];
    }

    /**
     * Clean up files and recalculate owner quota when a business is deleted.
     */
    public function handleBusinessDeleted(Business $business): void
    {
        $owner = $this->ownerQuotaService->ownerForBusiness($business);

        $businessDir = "businesses/{$business->id}";
        if (Storage::disk('public')->exists($businessDir)) {
            Storage::disk('public')->deleteDirectory($businessDir);
        }

        $slug = TenantStorage::slugForBusiness($business);
        if ($slug) {
            $slugPublicDir = "bisnis/{$slug}";
            if (Storage::disk('public')->exists($slugPublicDir)) {
                Storage::disk('public')->deleteDirectory($slugPublicDir);
            }

            $slugPrivateDir = "private/bisnis/{$slug}";
            if (Storage::disk('local')->exists($slugPrivateDir)) {
                Storage::disk('local')->deleteDirectory($slugPrivateDir);
            }
        }

        if ($business->logo_path && Storage::disk('public')->exists($business->logo_path)) {
            Storage::disk('public')->delete($business->logo_path);
        }

        StorageFile::where('business_id', $business->id)
            ->update([
                'status' => StorageFile::STATUS_DELETED,
                'deleted_at' => Carbon::now(),
            ]);

        if ($owner) {
            $this->ownerQuotaService->clearSummaryCache($owner);
        }
    }

    /**
     * Get detailed breakdown of storage for UI presentation.
     * Enforces strict multi-tenant isolation by business_id when business is provided.
     */
    public function getStorageDetails(Business|User $subject, Business|User|null $secondary = null): array
    {
        if ($subject instanceof Business) {
            $business = $subject;
            $owner = ($secondary instanceof User)
                ? $secondary
                : ($business->users()->wherePivot('role', 'owner')->first() ?? $business->owner ?? auth()->user());
        } else {
            $owner = $subject;
            $business = ($secondary instanceof Business) ? $secondary : null;
        }

        $summary = $owner ? $this->ownerQuotaService->getSummary($owner) : [
            'limit_bytes' => OwnerStorageQuotaService::DEFAULT_LIMIT_BYTES,
            'limit_gb' => 3,
            'used_bytes' => 0,
            'used_mb' => 0,
            'percentage' => 0,
            'is_over_limit' => false,
        ];

        $limitBytes = (int) $summary['limit_bytes'];
        $businessId = $business?->id;

        // 1. Calculate usage strictly for this active business
        $businessFilesQuery = StorageFile::where('status', StorageFile::STATUS_ACTIVE)
            ->where('is_temporary', false)
            ->where('category', '!=', StorageFile::CATEGORY_SOCIAL_MEDIA)
            ->where('module', '!=', 'social_media');

        if ($businessId) {
            $businessFilesQuery->where('business_id', $businessId);
        } elseif ($owner) {
            $businessFilesQuery->where('owner_id', $owner->id);
        }

        $businessUsedBytes = (int) (clone $businessFilesQuery)->sum('file_size');
        $businessFilesCount = (int) (clone $businessFilesQuery)->count();

        // 2. Breakdown by Category strictly isolated to this active business
        $categoryQuery = StorageFile::where('status', StorageFile::STATUS_ACTIVE)
            ->where('is_temporary', false)
            ->where('category', '!=', StorageFile::CATEGORY_SOCIAL_MEDIA)
            ->where('module', '!=', 'social_media');

        if ($businessId) {
            $categoryQuery->where('business_id', $businessId);
        } elseif ($owner) {
            $categoryQuery->where('owner_id', $owner->id);
        }

        $categories = $categoryQuery
            ->select('category', DB::raw('SUM(file_size) as total_bytes'), DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->get()
            ->map(function ($row) use ($limitBytes, $businessUsedBytes) {
                $cBytes = (int) $row->total_bytes;
                $pct = $businessUsedBytes > 0 ? round(($cBytes / $businessUsedBytes) * 100, 1) : 0;
                return [
                    'category' => $row->category,
                    'label' => StorageFile::CATEGORIES[$row->category] ?? ucfirst(str_replace('_', ' ', (string) $row->category)),
                    'used_bytes' => $cBytes,
                    'used_mb' => round($cBytes / 1048576, 2),
                    'files_count' => (int) $row->count,
                    'percentage' => $pct,
                ];
            })
            ->values()
            ->all();

        // 3. Top Largest Files (max 10) strictly isolated to this active business_id
        $largestFilesQuery = StorageFile::with(['business'])
            ->where('status', StorageFile::STATUS_ACTIVE)
            ->where('is_temporary', false)
            ->where('category', '!=', StorageFile::CATEGORY_SOCIAL_MEDIA)
            ->where('module', '!=', 'social_media');

        if ($businessId) {
            $largestFilesQuery->where('business_id', $businessId);
        } elseif ($owner) {
            $largestFilesQuery->where('owner_id', $owner->id);
        }

        $largestFiles = $largestFilesQuery
            ->orderByDesc('file_size')
            ->limit(10)
            ->get()
            ->map(function (StorageFile $file) use ($business) {
                return [
                    'id' => $file->id,
                    'file_name' => $file->file_name,
                    'file_path' => $file->file_path,
                    'category' => $file->category,
                    'category_label' => $file->category_label,
                    'business_name' => $file->business?->name ?? $business?->name ?? 'Bisnis Ini',
                    'file_size' => (int) $file->file_size,
                    'formatted_size' => $file->formatted_size,
                    'uploaded_at' => $file->uploaded_at?->format('d M Y H:i') ?? $file->created_at?->format('d M Y H:i'),
                ];
            })
            ->all();

        // 4. Breakdown by Business (for multi-tenant owner overview)
        $businessStats = [];
        if ($owner) {
            $owner->loadMissing('businesses');
            foreach ($owner->businesses as $b) {
                $bBytes = (int) StorageFile::where('business_id', $b->id)
                    ->where('status', StorageFile::STATUS_ACTIVE)
                    ->where('is_temporary', false)
                    ->where('category', '!=', StorageFile::CATEGORY_SOCIAL_MEDIA)
                    ->where('module', '!=', 'social_media')
                    ->sum('file_size');

                $bCount = (int) StorageFile::where('business_id', $b->id)
                    ->where('status', StorageFile::STATUS_ACTIVE)
                    ->where('is_temporary', false)
                    ->where('category', '!=', StorageFile::CATEGORY_SOCIAL_MEDIA)
                    ->where('module', '!=', 'social_media')
                    ->count();

                $businessStats[] = [
                    'id' => $b->id,
                    'name' => $b->name,
                    'is_current' => $businessId && $b->id === $businessId,
                    'used_bytes' => $bBytes,
                    'used_mb' => round($bBytes / 1048576, 2),
                    'files_count' => $bCount,
                    'percentage' => $limitBytes > 0 ? round(($bBytes / $limitBytes) * 100, 1) : 0,
                ];
            }
        }

        $overallUsedBytes = (int) ($summary['used_bytes'] ?? $businessUsedBytes);
        $remainingBytes = max(0, $limitBytes - $overallUsedBytes);

        return [
            'limit_bytes' => $limitBytes,
            'limit_gb' => $summary['limit_gb'],
            'used_bytes' => $overallUsedBytes,
            'used_mb' => $summary['used_mb'],
            'used_gb' => round($overallUsedBytes / 1073741824, 2),
            'business_used_bytes' => $businessUsedBytes,
            'business_used_mb' => round($businessUsedBytes / 1048576, 2),
            'remaining_bytes' => $remainingBytes,
            'remaining_mb' => round($remainingBytes / 1048576, 2),
            'remaining_gb' => round($remainingBytes / 1073741824, 2),
            'percentage' => $summary['percentage'] ?? ($limitBytes > 0 ? round(($overallUsedBytes / $limitBytes) * 100, 1) : 0),
            'is_over_limit' => $summary['is_over_limit'] ?? ($overallUsedBytes > $limitBytes),
            'total_files_count' => $businessFilesCount,
            'overall_files_count' => $owner ? (int) StorageFile::where('owner_id', $owner->id)->where('status', StorageFile::STATUS_ACTIVE)->where('is_temporary', false)->where('category', '!=', StorageFile::CATEGORY_SOCIAL_MEDIA)->where('module', '!=', 'social_media')->count() : $businessFilesCount,
            'business_breakdown' => $businessStats,
            'category_breakdown' => $categories,
            'largest_files' => $largestFiles,
            'active_business_id' => $businessId,
            'active_business_name' => $business?->name,
        ];
    }

    private function detectCategoryFromPath(string $path): string
    {
        if (str_contains($path, '/logo/')) return StorageFile::CATEGORY_BUSINESS_LOGO;
        if (str_contains($path, '/products/')) return StorageFile::CATEGORY_PRODUCT_IMAGE;
        if (str_contains($path, '/landing/')) return StorageFile::CATEGORY_LANDING_PAGE_IMAGE;
        if (str_contains($path, '/community/')) return StorageFile::CATEGORY_COMMUNITY_IMAGE;
        if (str_contains($path, '/qris/')) return StorageFile::CATEGORY_QRIS;
        if (str_contains($path, '/social-media/')) return StorageFile::CATEGORY_SOCIAL_MEDIA;
        if (str_contains($path, '/expenses/')) return StorageFile::CATEGORY_EXPENSE_RECEIPT;
        if (str_contains($path, 'feedback/')) return StorageFile::CATEGORY_FEEDBACK_ATTACHMENT;
        if (str_contains($path, 'avatars/')) return StorageFile::CATEGORY_OWNER_AVATAR;
        if (str_contains($path, 'invoices/')) return StorageFile::CATEGORY_INVOICE_ATTACHMENT;
        if (str_contains($path, 'orders/')) return StorageFile::CATEGORY_PURCHASE_ORDER_ATTACHMENT;
        if (str_contains($path, 'import/')) return StorageFile::CATEGORY_IMPORT_TEMPORARY;

        return StorageFile::CATEGORY_DOCUMENT;
    }

    private function detectModuleFromCategory(string $category): string
    {
        return match ($category) {
            StorageFile::CATEGORY_BUSINESS_LOGO => 'settings',
            StorageFile::CATEGORY_PRODUCT_IMAGE => 'product',
            StorageFile::CATEGORY_LANDING_PAGE_IMAGE => 'landing_page',
            StorageFile::CATEGORY_COMMUNITY_IMAGE => 'community',
            StorageFile::CATEGORY_QRIS => 'commerce',
            StorageFile::CATEGORY_SOCIAL_MEDIA => 'social_media',
            StorageFile::CATEGORY_EXPENSE_RECEIPT => 'finance',
            StorageFile::CATEGORY_FEEDBACK_ATTACHMENT => 'feedback',
            StorageFile::CATEGORY_OWNER_AVATAR => 'profile',
            StorageFile::CATEGORY_IMPORT_TEMPORARY => 'import',
            default => 'business',
        };
    }
}
