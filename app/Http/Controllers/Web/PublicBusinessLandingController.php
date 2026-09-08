<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessLandingPage;
use App\Models\Product;
use App\Models\ProductCategory;
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

        abort_unless($landingPage, 404);

        $isAuthorizedPreview = request()->boolean('preview')
            && request()->user()?->active_business_id === $business->id;

        abort_unless($landingPage->is_published || $isAuthorizedPreview, 404);

        // Active POS products if enabled. The public page needs the full catalog
        // for its client-side category filter, while the showcase remains limited.
        $posProducts = collect();
        $productCategories = collect();
        if ($landingPage->show_pos_products) {
            $posProducts = Product::where('business_id', $business->id)
                ->where('is_active', true)
                ->with('category')
                ->orderBy('name')
                ->get();

            $productCategories = ProductCategory::where('business_id', $business->id)
                ->whereHas('products', fn ($query) => $query->where('is_active', true))
                ->orderBy('name')
                ->get();
        }

        $productPayload = $posProducts->map(fn (Product $product): array => [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => (float) $product->selling_price,
            'image_url' => $product->image_url,
            'category_id' => $product->category_id,
            'category' => $product->category?->name,
        ])->values();

        return view('public.business_landing', compact(
            'business',
            'landingPage',
            'posProducts',
            'productCategories',
            'productPayload'
        ));
    }
}
