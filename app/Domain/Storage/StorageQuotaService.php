<?php

declare(strict_types=1);

namespace App\Domain\Storage;

use App\Models\Business;
use Illuminate\Support\Facades\Storage;

class StorageQuotaService
{
    public const DEFAULT_FREE_LIMIT_BYTES = OwnerStorageQuotaService::DEFAULT_LIMIT_BYTES;
    public const DEFAULT_CORE_LIMIT_BYTES = OwnerStorageQuotaService::DEFAULT_LIMIT_BYTES;

    /**
     * Get estimated storage usage for a given business in bytes.
     */
    public function getUsageBytes(Business $business): int
    {
        $owner = app(OwnerStorageQuotaService::class)->ownerForBusiness($business);
        if ($owner) return app(OwnerStorageQuotaService::class)->getUsageBytes($owner);

        $businessPath = "businesses/{$business->id}";
        $totalBytes = 0;

        if (Storage::disk('public')->exists($businessPath)) {
            $files = Storage::disk('public')->allFiles($businessPath);
            foreach ($files as $file) {
                $totalBytes += Storage::disk('public')->size($file);
            }
        }

        return $totalBytes;
    }

    /**
     * Check if a business has storage capacity to upload additional bytes.
     */
    public function canUpload(Business $business, int $additionalBytes = 0): bool
    {
        $currentUsage = $this->getUsageBytes($business);
        $owner = app(OwnerStorageQuotaService::class)->ownerForBusiness($business);
        $maxLimit = $owner ? app(OwnerStorageQuotaService::class)->getLimitBytes($owner) : self::DEFAULT_CORE_LIMIT_BYTES;

        return ($currentUsage + $additionalBytes) <= $maxLimit;
    }

    /**
     * Get formatted human readable storage summary.
     */
    public function getSummary(Business $business): array
    {
        $usedBytes = $this->getUsageBytes($business);
        $owner = app(OwnerStorageQuotaService::class)->ownerForBusiness($business);
        $limitBytes = $owner ? app(OwnerStorageQuotaService::class)->getLimitBytes($owner) : self::DEFAULT_CORE_LIMIT_BYTES;
        $usedMb = round($usedBytes / (1024 * 1024), 2);
        $limitGb = round($limitBytes / (1024 * 1024 * 1024), 1);
        $percentage = $limitBytes > 0 ? round(($usedBytes / $limitBytes) * 100, 1) : 0;

        return [
            'used_bytes' => $usedBytes,
            'used_mb' => $usedMb,
            'limit_gb' => $limitGb,
            'percentage' => $percentage,
            'is_over_limit' => $usedBytes > $limitBytes,
        ];
    }
}
