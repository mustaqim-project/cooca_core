<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public Marketplace Controller — aggregates storefronts into a unified marketplace.
 *
 * All queries are scoped to publicly discoverable businesses only.
 * No tenant-specific data is exposed without proper scope.
 */
final class PublicMarketplaceController extends Controller
{
    /**
     * Marketplace homepage — featured stores, popular products, categories.
     */
    public function index(Request $request): View
    {
        // Featured stores (discoverable, with products)
        $featuredStores = Business::where('is_active', true)
            ->whereHas('storeSetting', function ($q): void {
                $q->where('is_storefront_enabled', true)
                  ->where('is_discoverable', true);
            })
            ->with(['landingPage', 'storeSetting'])
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->having('products_count', '>', 0)
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        // Popular products across all discoverable stores
        $popularProducts = Product::where('is_active', true)
            ->where('show_in_website', true)
            ->whereHas('business', function ($q): void {
                $q->where('is_active', true)
                  ->whereHas('storeSetting', function ($sq): void {
                      $sq->where('is_storefront_enabled', true)
                        ->where('is_discoverable', true);
                  });
            })
            ->with(['business', 'category'])
            ->orderByDesc('selling_price')
            ->take(12)
            ->get();

        // Category aggregation
        $categories = $this->buildCategoryData();

        // Statistics
        $totalStores = Business::where('is_active', true)
            ->whereHas('storeSetting', fn ($q) => $q->where('is_storefront_enabled', true)->where('is_discoverable', true))
            ->count();

        $totalProducts = Product::where('is_active', true)
            ->where('show_in_website', true)
            ->whereHas('business', function ($q): void {
                $q->where('is_active', true)
                  ->whereHas('storeSetting', fn ($sq) => $sq->where('is_storefront_enabled', true)->where('is_discoverable', true));
            })
            ->count();

        return view('public.marketplace.index', compact(
            'featuredStores',
            'popularProducts',
            'categories',
            'totalStores',
            'totalProducts'
        ));
    }

    /**
     * Marketplace-wide product search across all discoverable stores.
     */
    public function search(Request $request): View|\Illuminate\Http\JsonResponse
    {
        $search = trim((string) $request->query('q', ''));
        $category = trim((string) $request->query('kategori', ''));
        $priceRange = trim((string) $request->query('harga', ''));
        $sort = trim((string) $request->query('urut', 'terbaru'));

        $query = Product::where('is_active', true)
            ->where('show_in_website', true)
            ->whereHas('business', function ($q): void {
                $q->where('is_active', true)
                  ->whereHas('storeSetting', function ($sq): void {
                      $sq->where('is_storefront_enabled', true)
                        ->where('is_discoverable', true);
                  });
            })
            ->with(['business.storeSetting', 'category']);

        // Search with escaped LIKE wildcards (Security fix)
        if ($search !== '') {
            $escapedSearch = str_replace(['%', '_'], ['\%', '\_'], $search);
            $query->where(function ($q) use ($escapedSearch): void {
                $q->where('name', 'like', "%{$escapedSearch}%")
                  ->orWhere('description', 'like', "%{$escapedSearch}%")
                  ->orWhereHas('business', fn ($bq) => $bq->where('name', 'like', "%{$escapedSearch}%"));
            });
        }

        // Category filter
        if ($category !== '' && $category !== 'semua') {
            $query->whereHas('business', function ($q) use ($category): void {
                $q->where('industry_category', $category)
                  ->orWhere('template_code', 'like', "{$category}%");
            });
        }

        // Price range filter
        match ($priceRange) {
            'murah'  => $query->where('selling_price', '<', 50000),
            'sedang' => $query->whereBetween('selling_price', [50000, 200000]),
            'mahal'  => $query->where('selling_price', '>', 200000),
            default  => null,
        };

        // Sort
        match ($sort) {
            'harga_rendah' => $query->orderBy('selling_price', 'asc'),
            'harga_tinggi' => $query->orderBy('selling_price', 'desc'),
            'nama'         => $query->orderBy('name', 'asc'),
            default        => $query->latest(),
        };

        $products = $query->paginate(24)->withQueryString();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'total' => $products->total(),
                'data' => collect($products->items())->map(function (Product $product): array {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'price' => (float) $product->selling_price,
                        'formatted_price' => 'Rp ' . number_format((float) $product->selling_price, 0, ',', '.'),
                        'image_url' => $product->image_url,
                        'category' => $product->category?->name ?? 'Produk',
                        'business_name' => $product->business?->name,
                        'business_slug' => $product->business?->slug,
                        'city' => $product->business?->storeSetting?->city ?? 'Indonesia',
                        'url' => url('/' . ($product->business?->slug ?? 'toko') . '/produk/' . ($product->slug ?: $product->id)),
                    ];
                })->all(),
            ]);
        }

        $categories = $this->buildCategoryData();

        return view('public.marketplace.search', compact(
            'products',
            'search',
            'category',
            'priceRange',
            'sort',
            'categories'
        ));
    }

    /**
     * Build category count data for filter pills.
     *
     * @return array<string, array<string, mixed>>
     */
    private function buildCategoryData(): array
    {
        $baseScope = fn ($q) => $q->where('is_active', true)
            ->whereHas('storeSetting', fn ($sq) => $sq->where('is_storefront_enabled', true)->where('is_discoverable', true));

        return [
            'fnb' => [
                'label' => 'Kuliner',
                'icon'  => 'utensils',
                'count' => Business::where($baseScope)
                    ->where(fn ($q) => $q->where('industry_category', 'fnb')->orWhere('template_code', 'like', 'fnb%'))
                    ->count(),
            ],
            'retail' => [
                'label' => 'Ritel',
                'icon'  => 'shopping-bag',
                'count' => Business::where($baseScope)
                    ->where(fn ($q) => $q->where('industry_category', 'retail')->orWhere('template_code', 'like', 'retail%'))
                    ->count(),
            ],
            'service' => [
                'label' => 'Jasa',
                'icon'  => 'sparkles',
                'count' => Business::where($baseScope)
                    ->where(fn ($q) => $q->where('industry_category', 'service')->orWhere('template_code', 'like', 'service%'))
                    ->count(),
            ],
            'manufacture' => [
                'label' => 'Produsen',
                'icon'  => 'factory',
                'count' => Business::where($baseScope)
                    ->where(fn ($q) => $q->where('industry_category', 'manufacture')->orWhere('template_code', 'like', 'mfg%'))
                    ->count(),
            ],
        ];
    }
}
