<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\LandingPage\IndustryPresets;
use App\Http\Controllers\Controller;
use App\Models\BusinessLandingPage;
use App\Models\Product;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessLandingPageWebController extends Controller
{
    /**
     * Display the CMS editor for the business owner's landing page.
     */
    public function edit(): View
    {
        $business    = Context::requireBusiness();
        $landingPage = BusinessLandingPage::firstOrCreate(
            ['business_id' => $business->id],
            [
                'industry_preset' => 'retail',
                'is_published'    => true,
                'theme_color'     => '#10B981',
                'headline'        => "Selamat Datang di {$business->name}",
                'subheadline'     => $business->description ?: "Solusi terbaik dan terpercaya untuk segala kebutuhan Anda.",
                'whatsapp_number' => $business->phone,
                'custom_address'  => $business->address,
            ]
        );

        $industries = IndustryPresets::forModal();

        // If newly created and without values, apply default preset
        if (empty($landingPage->values)) {
            $preset = IndustryPresets::get($landingPage->industry_preset ?: 'retail', $business->name);
            $landingPage->update([
                'theme_color'        => $preset['theme_color'],
                'headline'           => $landingPage->headline ?: $preset['headline'],
                'subheadline'        => $landingPage->subheadline ?: $preset['subheadline'],
                'announcement_badge' => $preset['announcement_badge'],
                'cta_primary_text'   => $preset['cta_primary_text'],
                'cta_secondary_text' => $preset['cta_secondary_text'],
                'about_title'        => $preset['about_title'],
                'about_story'        => $preset['about_story'],
                'values'             => $preset['values'],
                'custom_services'    => $preset['services'],
                'faqs'               => $preset['faqs'],
                'testimonials'       => $preset['testimonials'],
            ]);
            $landingPage->refresh();
        }

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

        $request->validate([
            'industry_preset'               => 'required|string',
            'theme_color'                   => 'required|string|regex:/^#[0-9a-fA-F]{6}$/',
            'headline'                      => 'required|string|max:255',
            'subheadline'                   => 'nullable|string|max:2000',
            'announcement_badge'            => 'nullable|string|max:255',
            'hero_image_url'                => 'nullable|url|max:500',
            'logo_url'                      => 'nullable|url|max:500',
            'cta_primary_text'              => 'nullable|string|max:100',
            'cta_secondary_text'            => 'nullable|string|max:100',
            'about_title'                   => 'nullable|string|max:255',
            'about_story'                   => 'nullable|string|max:5000',
            'whatsapp_number'               => 'nullable|string|max:30',
            'whatsapp_default_message'      => 'nullable|string|max:1000',
            'custom_email'                  => 'nullable|email|max:255',
            'custom_address'                => 'nullable|string|max:500',
            'google_maps_embed'             => 'nullable|string|max:2000',
            'meta_title'                    => 'nullable|string|max:120',
            'meta_description'              => 'nullable|string|max:320',
            'section_visibility_json'       => 'nullable|string',
            'values_json'                   => 'nullable|string',
            'operational_hours_json'        => 'nullable|string',
            'custom_services_json'          => 'nullable|string',
            'testimonials_json'             => 'nullable|string',
            'faqs_json'                     => 'nullable|string',
            'stats'                         => 'nullable|array',
            'social_links'                  => 'nullable|array',
        ]);

        $landingPage->fill([
            'industry_preset'          => $request->input('industry_preset'),
            'theme_color'              => $request->input('theme_color', '#10B981'),
            'dark_mode'                => $request->boolean('dark_mode'),
            'show_pos_products'        => $request->boolean('show_pos_products'),
            'headline'                 => $request->input('headline'),
            'subheadline'              => $request->input('subheadline'),
            'announcement_badge'       => $request->input('announcement_badge'),
            'hero_image_url'           => $request->input('hero_image_url'),
            'logo_url'                 => $request->input('logo_url'),
            'cta_primary_text'         => $request->input('cta_primary_text'),
            'cta_secondary_text'       => $request->input('cta_secondary_text'),
            'about_title'              => $request->input('about_title'),
            'about_story'              => $request->input('about_story'),
            'whatsapp_number'          => $request->input('whatsapp_number'),
            'whatsapp_welcome_message' => $request->input('whatsapp_welcome_message'),
            'custom_address'           => $request->input('custom_address'),
            'google_maps_embed_url'    => $request->input('google_maps_embed_url'),
            'meta_title'               => $request->input('meta_title'),
            'meta_description'         => $request->input('meta_description'),
            'values'                   => json_decode($request->input('values_json', '[]'), true) ?: [],
            'operational_hours'        => json_decode($request->input('operational_hours_json', '[]'), true) ?: [],
            'custom_services'          => json_decode($request->input('custom_services_json', '[]'), true) ?: [],
            'testimonials'             => json_decode($request->input('testimonials_json', '[]'), true) ?: [],
            'faqs'                     => json_decode($request->input('faqs_json', '[]'), true) ?: [],
            'section_visibility'       => json_decode($request->input('section_visibility_json', '{}'), true) ?: [],
            'stats'                    => $request->input('stats', []),
            'social_links'             => $request->input('social_links', []),
        ]);

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
