<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Storefront;

use App\Domain\Storage\OwnerStorageQuotaService;
use App\Domain\Storage\StorageTrackingService;
use App\Domain\Storage\TenantStorage;
use App\Domain\Storefront\StorefrontThemeService;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessLandingPage;
use App\Models\StorageFile;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

final class StorefrontPopupWebController extends Controller
{
    public function __construct(
        private readonly StorefrontThemeService $themeService,
        private readonly OwnerStorageQuotaService $ownerStorageQuotaService,
        private readonly StorageTrackingService $storageTrackingService
    ) {}

    /**
     * Display the CMS editor for the promotional pop-up modal.
     */
    public function edit(): View
    {
        $business = Context::requireBusiness();
        $landingPage = BusinessLandingPage::firstOrCreate(
            ['business_id' => $business->id]
        );

        $activeTheme = $this->themeService->resolveTheme($landingPage);

        return view('app.landing_page.popup', compact('business', 'landingPage', 'activeTheme'));
    }

    /**
     * Update the promotional pop-up configuration.
     */
    public function update(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        $landingPage = BusinessLandingPage::firstOrCreate(
            ['business_id' => $business->id]
        );

        $validated = $request->validate([
            'popup_enabled'   => ['nullable', 'boolean'],
            'popup_title'     => ['nullable', 'string', 'max:255'],
            'popup_badge'     => ['nullable', 'string', 'max:50'],
            'popup_content'   => ['nullable', 'string', 'max:3000'],
            'popup_image'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'popup_image_url' => ['nullable', 'string', 'max:500'],
            'remove_popup_image' => ['nullable', 'boolean'],
            'popup_cta_text'  => ['nullable', 'string', 'max:100'],
            'popup_cta_url'   => ['nullable', 'string', 'max:500'],
            'popup_frequency' => ['required', 'in:always,once_per_session,once_per_day'],
            'popup_starts_at' => ['nullable', 'date'],
            'popup_ends_at'   => ['nullable', 'date', 'after_or_equal:popup_starts_at'],
        ]);

        // Handle image removal
        if ($request->boolean('remove_popup_image') && $landingPage->popup_image_path) {
            $this->deleteStoredImage($landingPage->popup_image_path);
            $landingPage->popup_image_path = null;
        }

        // Handle file upload
        if ($request->hasFile('popup_image')) {
            $file = $request->file('popup_image');
            $owner = $this->ownerStorageQuotaService->ownerForBusiness($business);
            if ($owner) {
                $this->storageTrackingService->assertCanUpload($owner, (int) $file->getSize(), 'popup_image');
            }

            $oldPath = $landingPage->popup_image_path;
            $landingPage->popup_image_path = $this->storePopupImage($file, $business);

            if ($oldPath) {
                $this->deleteStoredImage($oldPath);
            }
        } elseif ($request->filled('popup_image_url')) {
            $landingPage->popup_image_path = $request->input('popup_image_url');
        }

        $landingPage->popup_enabled   = $request->boolean('popup_enabled');
        $landingPage->popup_title     = $validated['popup_title'] ?? null;
        $landingPage->popup_badge     = $validated['popup_badge'] ?? null;
        $landingPage->popup_content   = $validated['popup_content'] ?? null;
        $landingPage->popup_cta_text  = $validated['popup_cta_text'] ?? null;
        $landingPage->popup_cta_url   = $validated['popup_cta_url'] ?? null;
        $landingPage->popup_frequency = $validated['popup_frequency'];
        $landingPage->popup_starts_at = ! empty($validated['popup_starts_at']) ? $validated['popup_starts_at'] : null;
        $landingPage->popup_ends_at   = ! empty($validated['popup_ends_at']) ? $validated['popup_ends_at'] : null;

        $landingPage->save();

        return redirect()->route('landing-page.popup.edit')
            ->with('success', 'Pengaturan Pop-up Promo Storefront berhasil disimpan.');
    }

    /**
     * Store uploaded pop-up image securely within tenant storage.
     */
    private function storePopupImage(UploadedFile $file, Business $business): string
    {
        $dir = TenantStorage::publicDir($business, 'landing/popup');
        $path = $file->store($dir, 'public');

        $owner = $this->ownerStorageQuotaService->ownerForBusiness($business);
        if ($owner) {
            $this->storageTrackingService->recordUpload(
                file: $file,
                filePath: $path,
                category: StorageFile::CATEGORY_LANDING_PAGE_IMAGE,
                module: 'storefront_popup',
                owner: $owner,
                business: $business,
                uploader: auth()->user()
            );
        }

        return TenantStorage::url($path) ?? Storage::disk('public')->url($path);
    }

    /**
     * Delete stored business image from disk.
     */
    private function deleteStoredImage(?string $url): void
    {
        if (! $url) {
            return;
        }

        $path = ltrim((string) parse_url($url, PHP_URL_PATH), '/');
        $relPath = null;

        if (str_contains($path, 'bisnis/')) {
            $marker = strpos($path, 'bisnis/');
            $relPath = substr($path, $marker);
        } elseif (str_contains($path, 'storage/')) {
            $marker = strpos($path, 'storage/');
            $relPath = substr($path, $marker + 8);
        }

        if ($relPath) {
            $this->storageTrackingService->deleteFile($relPath, 'public');
        }
    }
}
