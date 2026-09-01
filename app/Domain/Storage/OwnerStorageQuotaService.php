<?php

declare(strict_types=1);

namespace App\Domain\Storage;

use App\Models\Business;
use App\Models\OwnerStorageTopup;
use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Storage;

final class OwnerStorageQuotaService
{
    public const DEFAULT_LIMIT_BYTES = 3 * 1024 * 1024 * 1024;

    public function getBaseLimitBytes(): int
    {
        $gb = SystemSetting::get('owner_storage_limit_gb', '3');
        return max(0, (int) $gb) * 1024 * 1024 * 1024;
    }

    public function getUsageBytes(User $owner): int
    {
        $total = 0;
        foreach ($owner->businesses as $business) {
            $path = "businesses/{$business->id}";
            foreach (Storage::disk('public')->allFiles($path) as $file) {
                $total += (int) Storage::disk('public')->size($file);
            }
            if ($business->logo_path && Storage::disk('public')->exists($business->logo_path)) {
                $total += (int) Storage::disk('public')->size($business->logo_path);
            }
        }
        return $total;
    }

    public function getLimitBytes(User $owner): int
    {
        return $this->getBaseLimitBytes() + (int) OwnerStorageTopup::where('owner_id', $owner->id)
            ->whereHas('payment', fn ($query) => $query->where('status', 'approved'))
            ->sum('storage_bytes');
    }

    public function canUpload(User $owner, int $additionalBytes = 0): bool
    {
        return $this->getUsageBytes($owner) + max(0, $additionalBytes) <= $this->getLimitBytes($owner);
    }

    public function getSummary(User $owner): array
    {
        $used = $this->getUsageBytes($owner);
        $limit = $this->getLimitBytes($owner);
        return [
            'used_bytes' => $used,
            'limit_bytes' => $limit,
            'used_mb' => round($used / 1048576, 2),
            'limit_gb' => round($limit / 1073741824, 2),
            'percentage' => $limit > 0 ? min(100, round($used / $limit * 100, 1)) : 0,
            'is_over_limit' => $used > $limit,
        ];
    }

    public function ownerForBusiness(Business $business): ?User
    {
        return $business->users()->wherePivot('role', 'owner')->first();
    }
}
