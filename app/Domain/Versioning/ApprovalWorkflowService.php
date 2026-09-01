<?php

declare(strict_types=1);

namespace App\Domain\Versioning;

use App\Models\ProductCostVersion;
use App\Support\Context;
use InvalidArgumentException;

final class ApprovalWorkflowService
{
    /** @var array<string, array<int, string>> */
    private const ALLOWED_TRANSITIONS = [
        ProductCostVersion::STATUS_DRAFT => [
            ProductCostVersion::STATUS_IN_REVIEW,
            ProductCostVersion::STATUS_ARCHIVED,
        ],
        ProductCostVersion::STATUS_IN_REVIEW => [
            ProductCostVersion::STATUS_APPROVED,
            ProductCostVersion::STATUS_DRAFT, // Rejected back to draft
            ProductCostVersion::STATUS_ARCHIVED,
        ],
        ProductCostVersion::STATUS_APPROVED => [
            ProductCostVersion::STATUS_PUBLISHED,
            ProductCostVersion::STATUS_ARCHIVED,
        ],
        ProductCostVersion::STATUS_PUBLISHED => [
            ProductCostVersion::STATUS_ARCHIVED,
        ],
        ProductCostVersion::STATUS_ARCHIVED => [],
    ];

    /**
     * Transition a cost version to a new status.
     *
     * @throws InvalidArgumentException
     */
    public function transition(ProductCostVersion $version, string $newStatus, ?string $notes = null): ProductCostVersion
    {
        $currentStatus = $version->status;

        if ($currentStatus === $newStatus) {
            return $version;
        }

        $allowed = self::ALLOWED_TRANSITIONS[$currentStatus] ?? [];
        if (! in_array($newStatus, $allowed, true)) {
            throw new InvalidArgumentException("Illegal status transition from '{$currentStatus}' to '{$newStatus}'.");
        }

        $user = Context::user();
        $history = $version->status_history ?? [];
        $history[] = [
            'status' => $newStatus,
            'changed_by' => $user?->id,
            'changed_at' => now()->toIso8601String(),
            'notes' => $notes ?? "Transitioned to {$newStatus}",
        ];

        $version->update([
            'status' => $newStatus,
            'status_history' => $history,
        ]);

        return $version;
    }
}
