<?php

declare(strict_types=1);

namespace App\Domain\Storage;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class AdminStorage
{
    public const DISK_PUBLIC = 'public';
    public const DISK_PRIVATE = 'local';

    public const FOLDER_BRANDING = 'branding';
    public const FOLDER_TEMPLATES = 'templates';
    public const FOLDER_QRIS = 'qris';
    public const FOLDER_SOCIAL_MEDIA = 'social-media';
    public const FOLDER_SETTLEMENT_PROOFS = 'settlements/proofs';

    /**
     * Get the relative path on public storage for an admin subfolder.
     * Example: "admin/branding" or "admin/templates"
     */
    public static function publicDir(string $category): string
    {
        $clean = trim($category, '/');

        return "admin/{$clean}";
    }

    /**
     * Ensure core admin directories exist on both storage disk and public folder.
     */
    public static function ensureAdminDirectories(): void
    {
        $subfolders = [
            self::FOLDER_BRANDING,
            self::FOLDER_TEMPLATES,
            self::FOLDER_QRIS,
            self::FOLDER_SOCIAL_MEDIA,
        ];

        foreach ($subfolders as $folder) {
            $storageDir = "admin/{$folder}";
            if (! Storage::disk(self::DISK_PUBLIC)->exists($storageDir)) {
                Storage::disk(self::DISK_PUBLIC)->makeDirectory($storageDir);
            }

            try {
                $publicWebDir = public_path("admin/{$folder}");
                if (! is_dir($publicWebDir)) {
                    @mkdir($publicWebDir, 0755, true);
                }
            } catch (\Throwable) {
                // Ignore filesystem permission edge cases
            }
        }
    }

    /**
     * Store a public asset uploaded by admin into public/admin/{category}.
     * Returns the relative path on the public disk (e.g. "admin/branding/abc.png").
     */
    public static function storePublicFile(
        UploadedFile $file,
        string $category,
        ?string $customFilename = null
    ): string {
        self::ensureAdminDirectories();

        $cleanCategory = trim($category, '/');
        $filename = $customFilename
            ?: (string) Str::uuid() . '.' . strtolower($file->getClientOriginalExtension());

        $subDir = "admin/{$cleanCategory}";

        // Store on public storage disk
        $path = $file->storeAs($subDir, $filename, self::DISK_PUBLIC);

        // Also ensure a direct copy in public_path("admin/{$cleanCategory}/...") for non-symlink fallbacks
        try {
            $destDir = public_path($subDir);
            if (! is_dir($destDir)) {
                @mkdir($destDir, 0755, true);
            }
            $destPath = $destDir . DIRECTORY_SEPARATOR . $filename;
            $sourcePath = Storage::disk(self::DISK_PUBLIC)->path($path);
            if (file_exists($sourcePath) && ! file_exists($destPath)) {
                @copy($sourcePath, $destPath);
            }
        } catch (\Throwable) {
            // Non-critical fallback
        }

        return $path;
    }

    /**
     * Store a sensitive / credential file into private storage disk ('local').
     * Guaranteed inaccessible directly from public web requests.
     * Example: "settlements/proofs/abc.jpg"
     */
    public static function storePrivateFile(
        UploadedFile $file,
        string $category = self::FOLDER_SETTLEMENT_PROOFS,
        ?string $customFilename = null
    ): string {
        $cleanCategory = trim($category, '/');
        $filename = $customFilename
            ?: (string) Str::uuid() . '.' . strtolower($file->getClientOriginalExtension());

        return $file->storeAs($cleanCategory, $filename, self::DISK_PRIVATE);
    }

    /**
     * Physically delete an old public file from disk, ensuring no orphan files remain.
     * Supports both new 'admin/{category}/...' paths and legacy paths ('branding/...', 'templates/...').
     */
    public static function deletePublicFile(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        $deleted = false;
        $clean = ltrim($path, '/');
        if (str_starts_with($clean, 'public/')) {
            $clean = substr($clean, 7);
        }
        if (str_starts_with($clean, 'storage/')) {
            $clean = substr($clean, 8);
        }

        // 1. Check primary public storage disk
        if (Storage::disk(self::DISK_PUBLIC)->exists($clean)) {
            $deleted = Storage::disk(self::DISK_PUBLIC)->delete($clean) || $deleted;
        }

        // 2. Check if clean path needs admin/ prefix on public disk
        $adminPrefixed = 'admin/' . ltrim($clean, '/');
        if (Storage::disk(self::DISK_PUBLIC)->exists($adminPrefixed)) {
            $deleted = Storage::disk(self::DISK_PUBLIC)->delete($adminPrefixed) || $deleted;
        }

        // 3. Check public_path directly
        $directPublic = public_path($clean);
        if (file_exists($directPublic) && is_file($directPublic)) {
            @unlink($directPublic);
            $deleted = true;
        }

        // 4. Check public_path with admin prefix
        $directAdminPublic = public_path($adminPrefixed);
        if (file_exists($directAdminPublic) && is_file($directAdminPublic)) {
            @unlink($directAdminPublic);
            $deleted = true;
        }

        // 5. Check storage_path directly
        $storageDirect = storage_path('app/public/' . $clean);
        if (file_exists($storageDirect) && is_file($storageDirect)) {
            @unlink($storageDirect);
            $deleted = true;
        }

        // 6. Check legacy downloads folder for templates
        $legacyDownload = public_path('downloads/' . basename($clean));
        if (file_exists($legacyDownload) && is_file($legacyDownload)) {
            @unlink($legacyDownload);
            $deleted = true;
        }

        return $deleted;
    }

    /**
     * Physically delete a private / credential file from local storage disk.
     */
    public static function deletePrivateFile(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        $deleted = false;
        $clean = ltrim($path, '/');

        if (Storage::disk(self::DISK_PRIVATE)->exists($clean)) {
            $deleted = Storage::disk(self::DISK_PRIVATE)->delete($clean) || $deleted;
        }

        // Also check if legacy file was mistakenly stored in public disk
        if (Storage::disk(self::DISK_PUBLIC)->exists($clean)) {
            $deleted = Storage::disk(self::DISK_PUBLIC)->delete($clean) || $deleted;
        }

        return $deleted;
    }

    /**
     * Resolve a public web URL for a given admin asset path.
     */
    public static function publicUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $clean = ltrim($path, '/');
        if (str_starts_with($clean, 'public/')) {
            $clean = substr($clean, 7);
        }

        if (str_starts_with($clean, 'storage/')) {
            return asset($clean);
        }

        // If file exists directly in public_path(clean)
        if (file_exists(public_path($clean))) {
            return asset($clean);
        }

        // If path already starts with admin/
        if (str_starts_with($clean, 'admin/')) {
            if (file_exists(public_path($clean))) {
                return asset($clean);
            }

            return asset('storage/' . $clean);
        }

        return asset('storage/' . $clean);
    }
}
