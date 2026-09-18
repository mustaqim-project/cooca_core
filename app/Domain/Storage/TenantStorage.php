<?php

declare(strict_types=1);

namespace App\Domain\Storage;

use App\Models\Business;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class TenantStorage
{
    public const DISK_PUBLIC = 'public';
    public const DISK_PRIVATE = 'local';

    public const FOLDER_LOGO = 'logo';
    public const FOLDER_PRODUCTS = 'products';
    public const FOLDER_LANDING = 'landing';
    public const FOLDER_QRIS = 'qris';
    public const FOLDER_SOCIAL_MEDIA = 'social-media';
    public const FOLDER_COMMUNITY = 'community';
    public const FOLDER_RECEIPTS = 'receipts';

    public const FOLDER_EXPENSES = 'expenses';
    public const FOLDER_INVOICE_PROOFS = 'invoice-receipts';
    public const FOLDER_CUSTOMER_PROOFS = 'customer-proofs';

    /**
     * Resolve a URL-safe, filesystem-safe slug for a given business.
     */
    public static function slugForBusiness(Business $business): string
    {
        $raw = ! empty($business->slug) ? $business->slug : Str::slug($business->name);
        $clean = trim((string) preg_replace('/[^a-z0-9\-_]/i', '-', strtolower($raw)), '-_');

        return ! empty($clean) ? $clean : (string) $business->id;
    }

    /**
     * Get the relative path on the public disk for a business subfolder.
     * Example: "bisnis/bengkel-bagema/products" or "bisnis/bengkel-bagema/products/abc.jpg"
     */
    public static function publicDir(Business $business, string $category): string
    {
        $slug = self::slugForBusiness($business);
        $cleanCategory = trim($category, '/');

        return "bisnis/{$slug}/{$cleanCategory}";
    }

    /**
     * Get the relative path on the private disk ('local') for a business subfolder.
     * Example: "bisnis/bengkel-bagema/expenses"
     */
    public static function privateDir(Business $business, string $category): string
    {
        $slug = self::slugForBusiness($business);
        $cleanCategory = trim($category, '/');

        return "bisnis/{$slug}/{$cleanCategory}";
    }

    /**
     * Ensure base tenant directories exist on public storage.
     */
    public static function ensureTenantDirectories(Business $business): void
    {
        $slug = self::slugForBusiness($business);
        $subfolders = [
            self::FOLDER_LOGO,
            self::FOLDER_PRODUCTS,
            self::FOLDER_LANDING,
            self::FOLDER_QRIS,
            self::FOLDER_SOCIAL_MEDIA,
            self::FOLDER_COMMUNITY,
            self::FOLDER_RECEIPTS,
        ];

        foreach ($subfolders as $folder) {
            $dir = "bisnis/{$slug}/{$folder}";
            if (! Storage::disk(self::DISK_PUBLIC)->exists($dir)) {
                Storage::disk(self::DISK_PUBLIC)->makeDirectory($dir);
            }
        }

        // Also ensure public/bisnis mirror if needed for non-symlinked environments
        try {
            $webPublicBisnis = public_path("bisnis/{$slug}");
            if (! is_dir($webPublicBisnis)) {
                @mkdir($webPublicBisnis, 0755, true);
            }
        } catch (\Throwable) {
            // Ignore filesystem permission edge cases
        }
    }

    /**
     * Resolve a public URL for a given stored relative path.
     * Supports both new 'bisnis/{slug}/...' and legacy 'businesses/{id}/...' or 'storage/...'.
     */
    public static function url(?string $path): ?string
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

        // Direct 'bisnis/...' path can be resolved via asset('bisnis/...') if symlinked, or asset('storage/' . $clean)
        if (str_starts_with($clean, 'bisnis/')) {
            // Check if public/bisnis exists directly
            if (file_exists(public_path($clean))) {
                return asset($clean);
            }

            return asset('storage/' . $clean);
        }

        return asset('storage/' . $clean);
    }

    /**
     * Synchronize physical folders and database paths when a business slug changes.
     */
    public static function handleSlugRenamed(Business $business, string $oldSlug, string $newSlug): void
    {
        $oldDir = "bisnis/{$oldSlug}";
        $newDir = "bisnis/{$newSlug}";

        if (Storage::disk(self::DISK_PUBLIC)->exists($oldDir)) {
            Storage::disk(self::DISK_PUBLIC)->move($oldDir, $newDir);
        }

        $oldPrivDir = "bisnis/{$oldSlug}";
        $newPrivDir = "bisnis/{$newSlug}";
        if (Storage::disk(self::DISK_PRIVATE)->exists($oldPrivDir)) {
            Storage::disk(self::DISK_PRIVATE)->move($oldPrivDir, $newPrivDir);
        }

        // Update database references for this business
        \App\Models\Product::where('business_id', $business->id)
            ->where('image_path', 'like', "bisnis/{$oldSlug}/%")
            ->get()
            ->each(function ($prod) use ($oldSlug, $newSlug) {
                $prod->update([
                    'image_path' => str_replace("bisnis/{$oldSlug}/", "bisnis/{$newSlug}/", $prod->image_path),
                ]);
            });

        if ($business->logo_path && str_contains($business->logo_path, "bisnis/{$oldSlug}/")) {
            $business->logo_path = str_replace("bisnis/{$oldSlug}/", "bisnis/{$newSlug}/", $business->logo_path);
        }

        \App\Models\CommercePaymentMethod::where('business_id', $business->id)
            ->where('qris_image_path', 'like', "bisnis/{$oldSlug}/%")
            ->get()
            ->each(function ($method) use ($oldSlug, $newSlug) {
                $method->update([
                    'qris_image_path' => str_replace("bisnis/{$oldSlug}/", "bisnis/{$newSlug}/", $method->qris_image_path),
                ]);
            });

        \App\Models\CommunityPost::where('business_id', $business->id)
            ->where('image_path', 'like', "bisnis/{$oldSlug}/%")
            ->get()
            ->each(function ($post) use ($oldSlug, $newSlug) {
                $post->update([
                    'image_path' => str_replace("bisnis/{$oldSlug}/", "bisnis/{$newSlug}/", $post->image_path),
                ]);
            });

        \App\Models\Expense::where('business_id', $business->id)
            ->where('receipt_image_path', 'like', "bisnis/{$oldSlug}/%")
            ->get()
            ->each(function ($ex) use ($oldSlug, $newSlug) {
                $ex->update([
                    'receipt_image_path' => str_replace("bisnis/{$oldSlug}/", "bisnis/{$newSlug}/", $ex->receipt_image_path),
                ]);
            });

        \App\Models\StorageFile::where('business_id', $business->id)
            ->where('file_path', 'like', "bisnis/{$oldSlug}/%")
            ->get()
            ->each(function ($sf) use ($oldSlug, $newSlug) {
                $sf->update([
                    'file_path' => str_replace("bisnis/{$oldSlug}/", "bisnis/{$newSlug}/", $sf->file_path),
                ]);
            });
    }
}
