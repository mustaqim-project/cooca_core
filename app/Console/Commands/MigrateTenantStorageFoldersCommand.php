<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Storage\StorageTrackingService;
use App\Domain\Storage\TenantStorage;
use App\Models\Business;
use App\Models\CommercePaymentMethod;
use App\Models\CommunityPost;
use App\Models\Expense;
use App\Models\Product;
use App\Models\StorageFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MigrateTenantStorageFoldersCommand extends Command
{
    protected $signature = 'storage:migrate-tenant-slugs 
                            {--business= : Business ID or slug to migrate} 
                            {--all : Migrate all businesses} 
                            {--dry-run : Simulate migration without modifying files or database}';

    protected $description = 'Migrate legacy tenant uploads (businesses/{id}, products/*, qris/*) to public/bisnis/{slug} and gate sensitive files to private storage';

    public function handle(StorageTrackingService $trackingService): int
    {
        $businessParam = $this->option('business');
        $all = $this->option('all');
        $dryRun = (bool) $this->option('dry-run');

        if (! $businessParam && ! $all) {
            $this->error('Tentukan opsi --business=<id|slug> atau --all.');
            return self::FAILURE;
        }

        $query = Business::query();
        if ($businessParam) {
            $query->where(function ($q) use ($businessParam) {
                $q->where('id', $businessParam)->orWhere('slug', $businessParam);
            });
        }

        $businesses = $query->get();

        if ($businesses->isEmpty()) {
            $this->warn('Tidak ada bisnis yang ditemukan.');
            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->warn('*** DRY-RUN MODE: Tidak ada berkas atau database yang diubah ***');
        }

        $this->info("Memulai migrasi storage folder untuk {$businesses->count()} bisnis...");

        $results = [];

        foreach ($businesses as $business) {
            $slug = TenantStorage::slugForBusiness($business);
            $filesMoved = 0;
            $recordsUpdated = 0;

            $this->line("<comment>Memproses [{$business->name}] (Slug: {$slug})</comment>");

            // 1. Migrasi Logo Bisnis
            if ($business->logo_path && ! str_starts_with($business->logo_path, "bisnis/{$slug}/")) {
                $oldPath = $business->logo_path;
                $newPath = TenantStorage::publicDir($business, TenantStorage::FOLDER_LOGO) . '/' . basename($oldPath);

                if (Storage::disk('public')->exists($oldPath)) {
                    if (! $dryRun) {
                        $this->safeMoveOrCopy('public', $oldPath, 'public', $newPath);
                        $business->update(['logo_path' => $newPath]);
                        $this->updateStorageFileRecord($business, $oldPath, $newPath, 'public');
                    }
                    $filesMoved++;
                    $recordsUpdated++;
                }
            }

            // 2. Migrasi Produk Bisnis
            $products = Product::where('business_id', $business->id)->whereNotNull('image_path')->get();
            foreach ($products as $product) {
                $oldPath = $product->image_path;
                if (! $oldPath || str_starts_with($oldPath, "bisnis/{$slug}/")) {
                    continue;
                }

                $newPath = TenantStorage::publicDir($business, TenantStorage::FOLDER_PRODUCTS) . '/' . basename($oldPath);

                if (Storage::disk('public')->exists($oldPath)) {
                    if (! $dryRun) {
                        $this->safeMoveOrCopy('public', $oldPath, 'public', $newPath);
                        $product->update(['image_path' => $newPath]);
                        $this->updateStorageFileRecord($business, $oldPath, $newPath, 'public');
                    }
                    $filesMoved++;
                    $recordsUpdated++;
                }
            }

            // 3. Migrasi QRIS Commerce
            $paymentMethods = CommercePaymentMethod::where('business_id', $business->id)->whereNotNull('qris_image_path')->get();
            foreach ($paymentMethods as $pm) {
                $oldPath = $pm->qris_image_path;
                if (! $oldPath || str_starts_with($oldPath, "bisnis/{$slug}/")) {
                    continue;
                }

                $newPath = TenantStorage::publicDir($business, TenantStorage::FOLDER_QRIS) . '/' . basename($oldPath);

                if (Storage::disk('public')->exists($oldPath)) {
                    if (! $dryRun) {
                        $this->safeMoveOrCopy('public', $oldPath, 'public', $newPath);
                        $pm->update(['qris_image_path' => $newPath]);
                        $this->updateStorageFileRecord($business, $oldPath, $newPath, 'public');
                    }
                    $filesMoved++;
                    $recordsUpdated++;
                }
            }

            // 4. Migrasi Foto Komunitas
            $posts = CommunityPost::where('business_id', $business->id)->whereNotNull('image_path')->get();
            foreach ($posts as $post) {
                $oldPath = $post->image_path;
                if (! $oldPath || str_starts_with($oldPath, "bisnis/{$slug}/")) {
                    continue;
                }

                $newPath = TenantStorage::publicDir($business, TenantStorage::FOLDER_COMMUNITY) . '/' . basename($oldPath);

                if (Storage::disk('public')->exists($oldPath)) {
                    if (! $dryRun) {
                        $this->safeMoveOrCopy('public', $oldPath, 'public', $newPath);
                        $post->update(['image_path' => $newPath]);
                        $this->updateStorageFileRecord($business, $oldPath, $newPath, 'public');
                    }
                    $filesMoved++;
                    $recordsUpdated++;
                }
            }

            // 5. Migrasi Bukti Pengeluaran/Nota (Pindahkan dari Public ke Private)
            $expenses = Expense::where('business_id', $business->id)->whereNotNull('receipt_image_path')->get();
            foreach ($expenses as $expense) {
                $oldPath = $expense->receipt_image_path;
                if (! $oldPath) {
                    continue;
                }

                // Cek apakah berkas lama ada di public disk
                if (Storage::disk('public')->exists($oldPath)) {
                    $newPrivatePath = TenantStorage::privateDir($business, TenantStorage::FOLDER_EXPENSES) . '/' . basename($oldPath);

                    if (! $dryRun) {
                        $content = Storage::disk('public')->get($oldPath);
                        Storage::disk('local')->put($newPrivatePath, $content);
                        Storage::disk('public')->delete($oldPath);

                        $expense->update(['receipt_image_path' => $newPrivatePath]);

                        // Update StorageFile ke private/local
                        StorageFile::where('file_path', $oldPath)->update([
                            'disk' => 'local',
                            'file_path' => $newPrivatePath,
                            'category' => StorageFile::CATEGORY_EXPENSE_RECEIPT,
                        ]);
                    }
                    $filesMoved++;
                    $recordsUpdated++;
                }
            }

            // 6. Migrasi sisa berkas dalam legacy directory businesses/{id}/
            $legacyDir = "businesses/{$business->id}";
            if (Storage::disk('public')->exists($legacyDir)) {
                $legacyFiles = Storage::disk('public')->allFiles($legacyDir);
                foreach ($legacyFiles as $oldFile) {
                    $subPath = Str::after($oldFile, "{$legacyDir}/");
                    $newFile = "bisnis/{$slug}/{$subPath}";

                    if (! $dryRun) {
                        $this->safeMoveOrCopy('public', $oldFile, 'public', $newFile);
                        $this->updateStorageFileRecord($business, $oldFile, $newFile, 'public');
                    }
                    $filesMoved++;
                }

                if (! $dryRun && empty(Storage::disk('public')->allFiles($legacyDir))) {
                    Storage::disk('public')->deleteDirectory($legacyDir);
                }
            }

            // Recalculate quota jika bukan dry run
            if (! $dryRun) {
                $owner = $business->owner ?? $business->users()->wherePivot('role', 'owner')->first();
                if ($owner) {
                    $trackingService->recalculate($owner);
                }
            }

            $results[] = [
                $business->name,
                $slug,
                $filesMoved,
                $recordsUpdated,
                $dryRun ? 'Simulated' : 'Success',
            ];
        }

        $this->table(
            ['Bisnis', 'Slug', 'Berkas Dipindah', 'Record DB Diperbarui', 'Status'],
            $results
        );

        $this->info('Migrasi storage slug bisnis selesai dengan aman!');

        return self::SUCCESS;
    }

    private function safeMoveOrCopy(string $sourceDisk, string $sourcePath, string $targetDisk, string $targetPath): void
    {
        if (! Storage::disk($sourceDisk)->exists($sourcePath)) {
            return;
        }

        $targetDir = dirname($targetPath);
        if (! Storage::disk($targetDisk)->exists($targetDir)) {
            Storage::disk($targetDisk)->makeDirectory($targetDir);
        }

        $content = Storage::disk($sourceDisk)->get($sourcePath);
        Storage::disk($targetDisk)->put($targetPath, $content);
        Storage::disk($sourceDisk)->delete($sourcePath);
    }

    private function updateStorageFileRecord(Business $business, string $oldPath, string $newPath, string $disk): void
    {
        StorageFile::where('file_path', $oldPath)->update([
            'disk' => $disk,
            'file_path' => $newPath,
            'file_name' => basename($newPath),
            'business_id' => $business->id,
        ]);
    }
}
