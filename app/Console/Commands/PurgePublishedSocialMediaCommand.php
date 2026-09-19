<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Storage\StorageTrackingService;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaPost;
use App\Models\StorageFile;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PurgePublishedSocialMediaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'social-media:purge-published {--days=1 : Number of days after publish to purge}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge published social media posts, server media files, and inactive credentials 1 day after publication.';

    /**
     * Execute the console command.
     */
    public function handle(StorageTrackingService $trackingService): int
    {
        $days = (int) $this->option('days');
        if ($days <= 0) {
            $days = 1;
        }

        $cutoff = Carbon::now()->subDays($days);
        $this->info("Menjalankan pembersihan media sosial yang terpublikasi sebelum {$cutoff->toDateTimeString()} ({$days} hari lalu)...");

        $purgedPostsCount = 0;
        $deletedFilesCount = 0;
        $reclaimedBytes = 0;

        // 1. Ambil seluruh postingan yang sudah published >= 1 hari lalu
        $posts = SocialMediaPost::where('status', 'published')
            ->where(function ($query) use ($cutoff) {
                $query->where('published_at', '<=', $cutoff)
                    ->orWhere(function ($q2) use ($cutoff) {
                        $q2->whereNull('published_at')->where('created_at', '<=', $cutoff);
                    });
            })
            ->with(['media', 'targets', 'comments'])
            ->get();

        foreach ($posts as $post) {
            $this->line("Memproses pembersihan Konten ID: {$post->id} (Publikasi: " . ($post->published_at?->toDateTimeString() ?? $post->created_at->toDateTimeString()) . ")");

            // A. Hapus file fisik dari SocialPostMedia
            foreach ($post->media as $media) {
                if (! empty($media->local_path)) {
                    $size = $this->deletePhysicalFile($media->local_path, $trackingService);
                    if ($size > 0) {
                        $deletedFilesCount++;
                        $reclaimedBytes += $size;
                    }
                }
            }

            // B. Hapus file fisik dari local_media_paths (legacy array)
            if (! empty($post->local_media_paths) && is_array($post->local_media_paths)) {
                foreach ($post->local_media_paths as $localPath) {
                    if (! empty($localPath)) {
                        $size = $this->deletePhysicalFile($localPath, $trackingService);
                        if ($size > 0) {
                            $deletedFilesCount++;
                            $reclaimedBytes += $size;
                        }
                    }
                }
            }

            // C. Hapus data anak & induk postingan
            try {
                $post->media()->delete();
                $post->targets()->delete();
                $post->comments()->delete();
                $post->delete();
                $purgedPostsCount++;
            } catch (\Throwable $e) {
                Log::warning("[PurgeSocialMedia] Gagal menghapus record post {$post->id}: {$e->getMessage()}");
            }
        }

        // 2. Bersihkan berkas sementara orphan di direktori social-media/temp
        $tempCleaned = $this->purgeOrphanTempFiles($cutoff);
        $deletedFilesCount += $tempCleaned['count'];
        $reclaimedBytes += $tempCleaned['bytes'];

        // 3. Bersihkan kredensial akun media sosial yang disconnected / expired > 1 hari
        $credentialsPurged = $this->purgeInactiveCredentials($cutoff);

        $reclaimedMb = round($reclaimedBytes / 1048576, 2);

        $this->info("Pembersihan selesai:");
        $this->line("- Postingan terpublikasi dihapus: {$purgedPostsCount}");
        $this->line("- Berkas fisik media dihapus: {$deletedFilesCount} (Menghemat: {$reclaimedMb} MB)");
        $this->line("- Kredensial tidak aktif dibersihkan: {$credentialsPurged}");

        Log::info("[PurgeSocialMedia] Selesai. Posts: {$purgedPostsCount}, Berkas: {$deletedFilesCount} ({$reclaimedMb} MB), Kredensial: {$credentialsPurged}");

        return self::SUCCESS;
    }

    /**
     * Hapus berkas fisik dari disk public dan local, serta record storage_files.
     */
    private function deletePhysicalFile(string $filePath, StorageTrackingService $trackingService): int
    {
        $size = 0;
        if (Storage::disk('public')->exists($filePath)) {
            $size = (int) Storage::disk('public')->size($filePath);
            Storage::disk('public')->delete($filePath);
        } elseif (Storage::disk('local')->exists($filePath)) {
            $size = (int) Storage::disk('local')->size($filePath);
            Storage::disk('local')->delete($filePath);
        }

        // Tandai / hapus dari storage_files jika tercatat
        $trackingService->recordDeletion($filePath, 'public');
        StorageFile::where('file_path', $filePath)->delete();

        return $size;
    }

    /**
     * Hapus berkas sementara orphan di direktori social-media yang lebih tua dari cutoff.
     */
    private function purgeOrphanTempFiles(Carbon $cutoff): array
    {
        $count = 0;
        $bytes = 0;

        $tempDirs = ['social-media/temp'];

        foreach ($tempDirs as $dir) {
            if (Storage::disk('public')->exists($dir)) {
                $files = Storage::disk('public')->allFiles($dir);
                foreach ($files as $file) {
                    $lastModified = Carbon::createFromTimestamp(Storage::disk('public')->lastModified($file));
                    if ($lastModified->lessThanOrEqualTo($cutoff)) {
                        $bytes += (int) Storage::disk('public')->size($file);
                        Storage::disk('public')->delete($file);
                        $count++;
                    }
                }
            }
        }

        return ['count' => $count, 'bytes' => $bytes];
    }

    /**
     * Hapus token kredensial dari akun yang statusnya disconnected, expired, atau revoked.
     */
    private function purgeInactiveCredentials(Carbon $cutoff): int
    {
        $accounts = SocialMediaAccount::whereIn('status', ['disconnected', 'expired', 'revoked'])
            ->orWhere(function ($query) use ($cutoff) {
                $query->whereNotNull('token_expires_at')
                    ->where('token_expires_at', '<=', $cutoff);
            })
            ->get();

        $purgedCount = 0;
        foreach ($accounts as $account) {
            if (! empty($account->access_token) || ! empty($account->refresh_token)) {
                $account->update([
                    'access_token'  => '',
                    'refresh_token' => null,
                    'status'        => 'expired',
                ]);
                $purgedCount++;
            }
        }

        return $purgedCount;
    }
}
