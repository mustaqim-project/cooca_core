<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\LandingPage\IndustryPresets;
use App\Http\Controllers\Controller;
use App\Models\BusinessLandingPage;
use App\Models\Product;
use App\Domain\Storage\OwnerStorageQuotaService;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class BusinessLandingPageWebController extends Controller
{
    /**
     * Display the CMS editor for the business owner's landing page.
     */
    public function edit(): View
    {
        $business    = Context::requireBusiness();
        $landingPage = BusinessLandingPage::firstOrCreate(
            ['business_id' => $business->id]
        );

        // On first creation (or a completely empty record) auto-apply the default
        // industry preset so the CMS never shows a fully null landing page.
        // NOTE: presets contain only text content — NO stock/unsplash images.
        if ($landingPage->wasRecentlyCreated || (blank($landingPage->headline) && blank($landingPage->values))) {
            $presetKey  = $landingPage->industry_preset ?: 'retail';
            $preset     = IndustryPresets::get($presetKey, $business->name);

            $defaults = [
                'industry_preset'         => $presetKey,
                'theme_color'             => $landingPage->theme_color ?: ($preset['theme_color'] ?? '#10B981'),
                'headline'                => $landingPage->headline ?: ($preset['headline'] ?? ''),
                'subheadline'             => $landingPage->subheadline ?: ($preset['subheadline'] ?? ''),
                'announcement_badge'      => $landingPage->announcement_badge ?: ($preset['announcement_badge'] ?? ''),
                'cta_primary_text'        => $landingPage->cta_primary_text ?: ($preset['cta_primary_text'] ?? 'Pesan via WhatsApp'),
                'cta_secondary_text'      => $landingPage->cta_secondary_text ?: ($preset['cta_secondary_text'] ?? 'Lihat Layanan & Menu'),
                'about_title'             => $landingPage->about_title ?: ($preset['about_title'] ?? 'Tentang Kami'),
                'about_story'             => $landingPage->about_story ?: ($preset['about_story'] ?? ''),
                'services_title'          => $landingPage->services_title ?: 'Layanan & Produk Pilihan',
                'services_subtitle'       => $landingPage->services_subtitle ?: 'Kualitas terbaik dan pelayanan prima untuk setiap pelanggan.',
                'gallery_title'           => $landingPage->gallery_title ?: 'Galeri Bisnis Kami',
                'gallery_subtitle'        => $landingPage->gallery_subtitle ?: 'Dokumentasi dan aktivitas terbaik kami.',
                'footer_navigation_title' => $landingPage->footer_navigation_title ?: 'Navigasi',
                'footer_services_title'   => $landingPage->footer_services_title ?: 'Layanan',
                'footer_contact_title'    => $landingPage->footer_contact_title ?: 'Kontak Kami',
                'footer_copyright'        => $landingPage->footer_copyright ?: ('© ' . date('Y') . ' ' . $business->name . '. All rights reserved.'),
                'values'                  => $landingPage->values ?: ($preset['values'] ?? []),
                'custom_services'         => $landingPage->custom_services ?: ($preset['services'] ?? []),
                'faqs'                    => $landingPage->faqs ?: ($preset['faqs'] ?? []),
                'testimonials'            => $landingPage->testimonials ?: ($preset['testimonials'] ?? []),
                'is_published'            => $landingPage->is_published ?? false,
            ];

            foreach ($defaults as $field => $value) {
                if (blank($landingPage->{$field})) {
                    $landingPage->{$field} = $value;
                }
            }
            $landingPage->save();
            $landingPage->refresh();
        }

        $industries = IndustryPresets::forModal();

        // Active POS products for showcase preview
        $posProducts = Product::where('business_id', $business->id)
            ->where('is_active', true)
            ->take(8)
            ->get();

        $publicUrl = route('public.business.landing', $business->slug);

        return view('app.landing_page.edit', compact(
            'business',
            'landingPage',
            'industries',
            'posProducts',
            'publicUrl'
        ));
    }

    /**
     * Update the landing page CMS content.
     */
    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $business    = Context::requireBusiness();
        $landingPage = BusinessLandingPage::firstOrNew(['business_id' => $business->id]);

        $validated = $request->validate([
            'industry_preset'               => 'nullable|string',
            'theme_color'                   => 'nullable|string|regex:/^#[0-9a-fA-F]{6}$/',
            'headline'                      => 'nullable|string|max:255',
            'subheadline'                   => 'nullable|string|max:2000',
            'announcement_badge'            => 'nullable|string|max:255',
            'hero_image_url'                => 'nullable|string|max:500',
            'logo_url'                      => 'nullable|string|max:500',
            'cta_primary_text'              => 'nullable|string|max:100',
            'cta_primary_url'               => 'nullable|string|max:500',
            'cta_secondary_text'            => 'nullable|string|max:100',
            'cta_secondary_url'             => 'nullable|string|max:500',
            'about_title'                   => 'nullable|string|max:255',
            'about_story'                   => 'nullable|string|max:5000',
            'services_title'                => 'nullable|string|max:120',
            'services_subtitle'             => 'nullable|string|max:500',
            'whatsapp_number'               => 'nullable|string|max:30',
            'custom_phone'                  => 'nullable|string|max:30',
            'whatsapp_welcome_message'      => 'nullable|string|max:1000',
            'custom_email'                  => 'nullable|email|max:255',
            'custom_address'                => 'nullable|string|max:500',
            'google_maps_embed_url'         => 'nullable|string|max:2000',
            'gallery_title'                 => 'nullable|string|max:120',
            'gallery_subtitle'              => 'nullable|string|max:500',
            'meta_title'                    => 'nullable|string|max:120',
            'meta_description'              => 'nullable|string|max:320',
            'meta_keywords'                 => 'nullable|string|max:500',
            'og_image_url'                  => 'nullable|string|max:500',
            'footer_description'            => 'nullable|string|max:1000',
            'footer_navigation_title'       => 'nullable|string|max:80',
            'footer_services_title'         => 'nullable|string|max:80',
            'footer_contact_title'          => 'nullable|string|max:80',
            'footer_cta_text'               => 'nullable|string|max:100',
            'footer_copyright'              => 'nullable|string|max:255',
            'section_visibility_json'       => 'nullable|json',
            'values_json'                   => 'nullable|json',
            'operational_hours_json'        => 'nullable|json',
            'custom_services_json'          => 'nullable|json',
            'testimonials_json'             => 'nullable|json',
            'faqs_json'                     => 'nullable|json',
            'stats'                         => 'nullable|array',
            'social_links'                  => 'nullable|array',
            'social_links.*'                => 'nullable|string|max:500',
            'hero_image'                    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096|dimensions:max_width=6000,max_height=6000',
            'logo_image'                    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096|dimensions:max_width=2000,max_height=2000',
            'about_image'                   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096|dimensions:max_width=3000,max_height=3000',
            'og_image'                      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096|dimensions:max_width=3000,max_height=3000',
            'gallery_images'                => 'nullable|array|max:20',
            'gallery_images.*'              => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096|dimensions:max_width=3000,max_height=3000',
            'gallery_images_json'           => 'nullable|string',
            'new_gallery_captions'          => 'nullable|array',
            'new_gallery_captions.*'        => 'nullable|string|max:255',
            'service_images.*'              => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096|dimensions:max_width=3000,max_height=3000',
            'remove_hero_image'             => 'nullable|boolean',
            'remove_logo_image'             => 'nullable|boolean',
            'remove_about_image'            => 'nullable|boolean',
            'remove_og_image'               => 'nullable|boolean',
            'remove_gallery'                => 'nullable|boolean',
            'remove_service_images'         => 'nullable|array',
            'remove_service_images.*'       => 'nullable|boolean',
        ]);

        $galleryFiles = collect($request->file('gallery_images', []))->filter();
        $owner = app(OwnerStorageQuotaService::class)->ownerForBusiness($business);
        $uploads = collect([$request->file('hero_image'), $request->file('logo_image'), $request->file('about_image'), $request->file('og_image')])
            ->filter()
            ->merge($galleryFiles)
            ->merge(collect($request->file('service_images', []))->filter());
        $uploadBytes = $uploads->sum(fn ($file) => (int) $file->getSize());
        if ($owner && $uploadBytes > 0) {
            app(\App\Domain\Storage\StorageTrackingService::class)->assertCanUpload($owner, $uploadBytes, 'hero_image');
        }

        // Only override stored content with fields the form actually submitted.
        // This protects against accidental data loss (missing/failed hidden inputs
        // must never wipe the saved landing page back to null).
        $scalarFields = [
            'industry_preset', 'theme_color', 'headline', 'subheadline', 'announcement_badge',
            'hero_image_url', 'logo_url', 'cta_primary_text', 'cta_primary_url',
            'cta_secondary_text', 'cta_secondary_url', 'about_title', 'about_story',
            'services_title', 'services_subtitle', 'whatsapp_number', 'custom_phone',
            'whatsapp_welcome_message', 'custom_email', 'custom_address',
            'google_maps_embed_url', 'gallery_title', 'gallery_subtitle',
            'meta_title', 'meta_description', 'meta_keywords',
            'footer_description', 'footer_navigation_title', 'footer_services_title',
            'footer_contact_title', 'footer_cta_text', 'footer_copyright',
        ];

        foreach ($scalarFields as $scalarField) {
            if (! $request->has($scalarField)) {
                continue; // Field not submitted → preserve stored value.
            }
            $incoming = $request->input($scalarField);
            if (is_string($incoming)) {
                $incoming = trim($incoming);
                if ($incoming === '') {
                    $incoming = null;
                }
            }
            $landingPage->{$scalarField} = $incoming;
        }

        $landingPage->dark_mode         = $request->boolean('dark_mode');
        $landingPage->show_pos_products = $request->boolean('show_pos_products');

        // JSON collections — only replace when a valid (non-empty) JSON string was posted.
        $jsonFields = [
            'values_json' => 'values',
            'operational_hours_json' => 'operational_hours',
            'custom_services_json' => 'custom_services',
            'testimonials_json' => 'testimonials',
            'faqs_json' => 'faqs',
            'section_visibility_json' => 'section_visibility',
        ];
        foreach ($jsonFields as $jsonInput => $attribute) {
            $raw = $request->input($jsonInput);
            if (! is_string($raw) || trim($raw) === '') {
                continue;
            }
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $landingPage->{$attribute} = $decoded;
            }
        }

        if ($request->has('stats') && is_array($request->input('stats'))) {
            $landingPage->stats = $request->input('stats');
        }
        if ($request->has('social_links') && is_array($request->input('social_links'))) {
            $landingPage->social_links = $request->input('social_links');
        }

        foreach ([
            'hero_image' => 'hero_image_url',
            'logo_image' => 'logo_url',
            'about_image' => 'about_image_url',
            'og_image' => 'og_image_url',
        ] as $input => $column) {
            $removeField = 'remove_' . $input;
            if ($request->boolean($removeField) && $landingPage->{$column}) {
                $this->deleteStoredBusinessImage($landingPage->{$column});
                $landingPage->{$column} = null;
            }
            if ($request->hasFile($input)) {
                $oldPath = $landingPage->{$column};
                $landingPage->{$column} = $this->storeBusinessImage($request->file($input), $business->id, $input);
                $this->deleteStoredBusinessImage($oldPath);
            }
        }

        if ($request->boolean('remove_gallery')) {
            foreach (($landingPage->gallery_images ?? []) as $oldImage) {
                $this->deleteStoredBusinessImage(is_array($oldImage) ? ($oldImage['url'] ?? null) : $oldImage);
            }
            $landingPage->gallery_images = [];
        } elseif ($request->has('gallery_images_json')) {
            $existing = json_decode((string) $request->input('gallery_images_json'), true) ?: [];
            $resultGallery = [];
            foreach ($existing as $item) {
                $url = is_array($item) ? ($item['url'] ?? '') : (string) $item;
                $caption = is_array($item) ? ($item['caption'] ?? '') : '';
                if (filled($url)) {
                    $resultGallery[] = [
                        'url' => trim($url),
                        'caption' => trim($caption),
                    ];
                }
            }

            if ($galleryFiles->isNotEmpty()) {
                $captions = $request->input('new_gallery_captions', []);
                foreach ($galleryFiles as $idx => $file) {
                    $url = $this->storeBusinessImage($file, $business->id, 'gallery');
                    $caption = isset($captions[$idx]) && is_string($captions[$idx]) ? trim($captions[$idx]) : '';
                    $resultGallery[] = [
                        'url' => $url,
                        'caption' => $caption,
                    ];
                }
            }

            $landingPage->gallery_images = $resultGallery;
        } elseif ($galleryFiles->isNotEmpty()) {
            $oldGallery = $landingPage->gallery_images ?? [];
            $landingPage->gallery_images = $galleryFiles
                ->map(fn ($file) => [
                    'url' => $this->storeBusinessImage($file, $business->id, 'gallery'),
                    'caption' => '',
                ])
                ->values()
                ->all();
            foreach ($oldGallery as $oldImage) {
                $this->deleteStoredBusinessImage(is_array($oldImage) ? ($oldImage['url'] ?? null) : $oldImage);
            }
        }

        $services = json_decode($request->input('custom_services_json', '[]'), true) ?: [];
        foreach (($request->file('service_images') ?? []) as $index => $file) {
            if ($file && isset($services[$index])) {
                $this->deleteStoredBusinessImage($services[$index]['image_url'] ?? null);
                $services[$index]['image_url'] = $this->storeBusinessImage($file, $business->id, 'services');
            }
        }
        foreach ($request->input('remove_service_images', []) as $index => $remove) {
            if ($remove && isset($services[$index]['image_url'])) {
                $this->deleteStoredBusinessImage($services[$index]['image_url']);
                $services[$index]['image_url'] = null;
            }
        }
        $landingPage->custom_services = $services;

        $landingPage->save();

        $message = 'Halaman website bisnis berhasil disimpan dan diperbarui!';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'landing_page' => $landingPage->fresh(),
            ]);
        }

        return back()->with('success', $message);
    }

    private function storeBusinessImage($file, string $businessId, string $category): string
    {
        $path = $file->store("businesses/{$businessId}/{$category}", 'public');

        $business = Business::find($businessId);
        if ($business) {
            $owner = app(OwnerStorageQuotaService::class)->ownerForBusiness($business);
            if ($owner) {
                app(\App\Domain\Storage\StorageTrackingService::class)->recordUpload(
                    file: $file,
                    filePath: $path,
                    category: \App\Models\StorageFile::CATEGORY_LANDING_PAGE_IMAGE,
                    module: 'landing_page',
                    owner: $owner,
                    business: $business,
                    uploader: auth()->user()
                );
            }
        }

        return Storage::disk('public')->url($path);
    }

    private function deleteStoredBusinessImage(?string $url): void
    {
        if (! $url || ! str_contains($url, '/storage/')) {
            return;
        }

        $path = ltrim((string) parse_url($url, PHP_URL_PATH), '/');
        $storageMarker = strpos($path, 'storage/');
        if ($storageMarker !== false) {
            $relPath = substr($path, $storageMarker + 8);
            app(\App\Domain\Storage\StorageTrackingService::class)->deleteFile($relPath, 'public');
        }
    }

    /**
     * Apply 1 of 20 industry presets to the current landing page.
     */
    public function applyPreset(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $presetKey = $request->input('preset', 'retail');

        $preset = IndustryPresets::get($presetKey, $business->name);

        return response()->json([
            'success' => true,
            'preset'  => $preset,
            'message' => "Preset industri berhasil dimuat ke editor.",
        ]);
    }

    /**
     * Toggle published status (form POST — returns redirect with flash).
     */
    public function togglePublish(Request $request): RedirectResponse|JsonResponse
    {
        $business    = Context::requireBusiness();
        $landingPage = BusinessLandingPage::firstOrCreate(['business_id' => $business->id]);

        $landingPage->is_published = ! $landingPage->is_published;
        $landingPage->save();

        $msg = $landingPage->is_published
            ? '🌐 Website bisnis Anda sekarang LIVE dan dapat diakses publik!'
            : '🔒 Website bisnis diarsipkan (tersembunyi dari publik).';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'is_published' => $landingPage->is_published,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }
}
