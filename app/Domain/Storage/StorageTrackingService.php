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
     */
    public function recordDeletion(string $filePath, string $disk = 'public'): ?StorageFile
    {
        $storageFile = StorageFile::where('disk', $disk)
            ->where('file_path', $filePath)
            ->first();

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
     */
    public function deleteFile(string $filePath, string $disk = 'public'): bool
    {
        if (Storage::disk($disk)->exists($filePath)) {
            Storage::disk($disk)->delete($filePath);
        }

        $this->recordDeletion($filePath, $disk);

        return true;
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
     */
    public function recalculate(User $owner): array
    {
        $owner->loadMissing('businesses');
        $businesses = $owner->businesses;
        $trackedCount = 0;
        $untrackedAdded = 0;
        $orphanedCleaned = 0;

        $activePathsOnDisk = [];

        foreach ($businesses as $business) {
            $businessDir = "businesses/{$business->id}";
            if (Storage::disk('public')->exists($businessDir)) {
                $files = Storage::disk('public')->allFiles($businessDir);
                foreach ($files as $file) {
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

            // Check business logo specifically if outside businesses/{id}
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

        // Check user avatar
        if ($owner->avatar && ! str_starts_with($owner->avatar, 'http') && Storage::disk('public')->exists($owner->avatar)) {
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

        // Detect orphaned records for this owner (files in DB that don't exist physically)
        $ownerFiles = StorageFile::where('owner_id', $owner->id)
            ->where('status', StorageFile::STATUS_ACTIVE)
            ->whereNull('deleted_at')
            ->get();

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

        return [
            'owner_id' => $owner->id,
            'owner_name' => $owner->name,
            'scanned_files' => count($activePathsOnDisk),
            'untracked_added' => $untrackedAdded,
            'orphaned_cleaned' => $orphanedCleaned,
            'total_used_bytes' => $summary['used_bytes'],
            'total_used_mb' => $summary['used_mb'],
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
     * Get detailed breakdown of owner storage for UI presentation.
     */
    public function getStorageDetails(User $owner): array
    {
        $summary = $this->ownerQuotaService->getSummary($owner);
        $limitBytes = (int) $summary['limit_bytes'];
        $usedBytes = (int) $summary['used_bytes'];
        $remainingBytes = max(0, $limitBytes - $usedBytes);

        // 1. Breakdown by Business
        $businessStats = [];
        $owner->loadMissing('businesses');
        foreach ($owner->businesses as $business) {
            $bBytes = (int) StorageFile::where('business_id', $business->id)
                ->where('status', StorageFile::STATUS_ACTIVE)
                ->where('is_temporary', false)
                ->sum('file_size');

            $bCount = (int) StorageFile::where('business_id', $business->id)
                ->where('status', StorageFile::STATUS_ACTIVE)
                ->where('is_temporary', false)
                ->count();

            $businessStats[] = [
                'id' => $business->id,
                'name' => $business->name,
                'used_bytes' => $bBytes,
                'used_mb' => round($bBytes / 1048576, 2),
                'files_count' => $bCount,
                'percentage' => $limitBytes > 0 ? round(($bBytes / $limitBytes) * 100, 1) : 0,
            ];
        }

        // Also check files without business (owner-level e.g. avatar, profile)
        $ownerDirectBytes = (int) StorageFile::where('owner_id', $owner->id)
            ->whereNull('business_id')
            ->where('status', StorageFile::STATUS_ACTIVE)
            ->where('is_temporary', false)
            ->sum('file_size');

        $ownerDirectCount = (int) StorageFile::where('owner_id', $owner->id)
            ->whereNull('business_id')
            ->where('status', StorageFile::STATUS_ACTIVE)
            ->where('is_temporary', false)
            ->count();

        if ($ownerDirectCount > 0) {
            $businessStats[] = [
                'id' => null,
                'name' => 'Akun Owner / Profil',
                'used_bytes' => $ownerDirectBytes,
                'used_mb' => round($ownerDirectBytes / 1048576, 2),
                'files_count' => $ownerDirectCount,
                'percentage' => $limitBytes > 0 ? round(($ownerDirectBytes / $limitBytes) * 100, 1) : 0,
            ];
        }

        // 2. Breakdown by Category
        $categories = StorageFile::where('owner_id', $owner->id)
            ->where('status', StorageFile::STATUS_ACTIVE)
            ->where('is_temporary', false)
            ->select('category', DB::raw('SUM(file_size) as total_bytes'), DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->get()
            ->map(function ($row) use ($limitBytes) {
                $cBytes = (int) $row->total_bytes;
                return [
                    'category' => $row->category,
                    'label' => StorageFile::CATEGORIES[$row->category] ?? ucfirst(str_replace('_', ' ', (string) $row->category)),
                    'used_bytes' => $cBytes,
                    'used_mb' => round($cBytes / 1048576, 2),
                    'files_count' => (int) $row->count,
                    'percentage' => $limitBytes > 0 ? round(($cBytes / $limitBytes) * 100, 1) : 0,
                ];
            })
            ->values()
            ->all();

        // 3. Top Largest Files (max 10)
        $largestFiles = StorageFile::with(['business'])
            ->where('owner_id', $owner->id)
            ->where('status', StorageFile::STATUS_ACTIVE)
            ->where('is_temporary', false)
            ->orderByDesc('file_size')
            ->limit(10)
            ->get()
            ->map(function (StorageFile $file) {
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
                ];
            })
            ->all();

        return [
            'limit_bytes' => $limitBytes,
            'limit_gb' => $summary['limit_gb'],
            'used_bytes' => $usedBytes,
            'used_mb' => $summary['used_mb'],
            'used_gb' => round($usedBytes / 1073741824, 2),
            'remaining_bytes' => $remainingBytes,
            'remaining_mb' => round($remainingBytes / 1048576, 2),
            'remaining_gb' => round($remainingBytes / 1073741824, 2),
            'percentage' => $summary['percentage'],
            'is_over_limit' => $summary['is_over_limit'],
            'total_files_count' => (int) StorageFile::where('owner_id', $owner->id)
                ->where('status', StorageFile::STATUS_ACTIVE)
                ->where('is_temporary', false)
                ->count(),
            'business_breakdown' => $businessStats,
            'category_breakdown' => $categories,
            'largest_files' => $largestFiles,
        ];
    }

    private function detectCategoryFromPath(string $path): string
    {
        if (str_contains($path, '/logo/')) return StorageFile::CATEGORY_BUSINESS_LOGO;
        if (str_contains($path, '/products/')) return StorageFile::CATEGORY_PRODUCT_IMAGE;
        if (str_contains($path, '/landing/')) return StorageFile::CATEGORY_LANDING_PAGE_IMAGE;
        if (str_contains($path, '/community/')) return StorageFile::CATEGORY_COMMUNITY_IMAGE;
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
            StorageFile::CATEGORY_FEEDBACK_ATTACHMENT => 'feedback',
            StorageFile::CATEGORY_OWNER_AVATAR => 'profile',
            StorageFile::CATEGORY_IMPORT_TEMPORARY => 'import',
            default => 'business',
        };
    }
}
