<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Storage\StorageTrackingService;
use App\Models\User;
use Illuminate\Console\Command;

class RecalculateStorageUsageCommand extends Command
{
    protected $signature = 'storage:recalculate {--owner= : Owner User ID or email} {--all : Recalculate for all owners}';

    protected $description = 'Reconcile physical storage files with storage_files table and recalculate quota usage';

    public function handle(StorageTrackingService $trackingService): int
    {
        $ownerParam = $this->option('owner');
        $all = $this->option('all');

        if (! $ownerParam && ! $all) {
            $this->error('Please specify --owner=<id|email> or --all to recalculate storage.');
            return self::FAILURE;
        }

        $query = User::query();

        if ($ownerParam) {
            $query->where(function ($q) use ($ownerParam) {
                $q->where('id', $ownerParam)->orWhere('email', $ownerParam);
            });
        } else {
            // Find all users who are owners of at least one business
            $query->whereHas('businesses', function ($q) {
                $q->where('business_users.role', 'owner');
            });
        }

        $owners = $query->get();

        if ($owners->isEmpty()) {
            $this->warn('No matching owners found.');
            return self::SUCCESS;
        }

        $this->info("Recalculating storage quota for {$owners->count()} owner(s)...");

        $rows = [];
        foreach ($owners as $owner) {
            $result = $trackingService->recalculate($owner);
            $rows[] = [
                $result['owner_name'],
                $result['scanned_files'],
                $result['untracked_added'],
                $result['orphaned_cleaned'],
                $result['total_used_mb'] . ' MB',
                $result['limit_gb'] . ' GB',
                $result['percentage'] . '%',
            ];
        }

        $this->table(
            ['Owner', 'Files On Disk', 'Untracked Added', 'Orphaned Cleaned', 'Used', 'Limit', 'Usage %'],
            $rows
        );

        $this->info('Storage recalculation and synchronization complete!');

        return self::SUCCESS;
    }
}
