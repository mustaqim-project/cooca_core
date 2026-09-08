<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\LandingPage\IndustryPresets;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessLandingPage;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicBusinessLandingController extends Controller
{
    /**
     * Render the public single-page landing for a business tenant.
     */
    public function show(string $slug): View
    {
        $business = Business::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $landingPage = BusinessLandingPage::where('business_id', $business->id)->first();

        // If no customized landing page yet, create a default one from preset
        if (! $landingPage) {
            $preset = IndustryPresets::get('retail', $business->name);
            $landingPage = new BusinessLandingPage([
                'business_id'        => $business->id,
                'is_published'       => true,
                'industry_preset'    => 'retail',
                'theme_color'        => '#10B981',
                'headline'           => "Selamat Datang di {$business->name}",
                'subheadline'        => $business->description ?: $preset['subheadline'],
                'announcement_badge' => $preset['announcement_badge'],
                'cta_primary_text'   => $preset['cta_primary_text'],
                'cta_secondary_text' => $preset['cta_secondary_text'],
                'about_title'        => $preset['about_title'],
                'about_story'        => $preset['about_story'],
                'values'             => $preset['values'],
                'custom_services'    => $preset['services'],
                'faqs'               => $preset['faqs'],
                'testimonials'       => $preset['testimonials'],
                'whatsapp_number'    => $business->phone,
                'custom_address'     => $business->address,
            ]);
        }

        $isAuthorizedPreview = request()->boolean('preview')
            && request()->user()?->active_business_id === $business->id;

        abort_unless($landingPage->is_published || $isAuthorizedPreview, 404);

        // Active POS products if enabled
        $posProducts = collect();
        if ($landingPage->show_pos_products) {
            $posProducts = Product::where('business_id', $business->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->take(12)
                ->get();
        }

        return view('public.business_landing', compact(
            'business',
            'landingPage',
            'posProducts'
        ));
    }
}
