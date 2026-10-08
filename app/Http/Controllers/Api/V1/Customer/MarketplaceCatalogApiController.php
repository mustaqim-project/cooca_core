<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\CommerceProductReview;
use App\Models\CommerceStoreSetting;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class MarketplaceCatalogApiController extends Controller
{
    /**
     * Get marketplace home screen feed.
     * GET /api/v1/marketplace/home
     */
    public function homeFeed(Request $request): JsonResponse
    {
        // 1. Categories
        $categories = ProductCategory::select('id', 'name', 'slug', 'description')
            ->whereHas('products', fn($q) => $q->where('is_active', true))
            ->limit(10)
            ->get();

        if ($categories->isEmpty()) {
            $categories = ProductCategory::select('id', 'name', 'slug', 'description')->limit(8)->get();
        }

        // 2. Featured Stores
        $featuredStores = Business::where('is_active', true)
            ->whereHas('products', fn($q) => $q->where('is_active', true))
            ->select('id', 'name', 'slug', 'logo_path', 'phone', 'address')
            ->limit(6)
            ->get();

        // 3. Recommended / Popular Products
        $products = Product::where('is_active', true)
            ->with(['business:id,name,slug,logo_path', 'category:id,name,slug'])
            ->latest()
            ->limit(16)
            ->get()
            ->map(function (Product $p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'code' => $p->code,
                    'slug' => $p->slug,
                    'selling_price' => (float) $p->selling_price,
                    'image_url' => $p->image_url,
                    'category' => $p->category?->name,
                    'store' => [
                        'id' => $p->business?->id,
                        'name' => $p->business?->name,
                        'slug' => $p->business?->slug,
                    ],
                ];
            });

        // 4. Promo Banners
        $banners = [
            [
                'id' => 'b1',
                'title' => 'Dukung Produk UMKM Lokal',
                'subtitle' => 'Belanja langsung dari produsen tangan pertama di Cooca.',
                'image_url' => null,
            ],
            [
                'id' => 'b2',
                'title' => 'Pengiriman Cepat Seluruh Indonesia',
                'subtitle' => 'Terintegrasi kurir instan & ekspedisi nasional terpercaya.',
                'image_url' => null,
            ],
        ];

        return response()->json([
            'success' => true,
            'banners' => $banners,
            'categories' => $categories,
            'featured_stores' => $featuredStores,
            'popular_products' => $products,
        ], Response::HTTP_OK);
    }

    /**
     * Get product categories list.
     * GET /api/v1/marketplace/categories
     */
    public function categories(Request $request): JsonResponse
    {
        $categories = ProductCategory::select('id', 'name', 'slug', 'description')
            ->withCount(['products' => fn($q) => $q->where('is_active', true)])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ], Response::HTTP_OK);
    }

    /**
     * Search products with query, category, price range, and sorting.
     * GET /api/v1/marketplace/products/search
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $query = (string) ($request->query('q') ?? $request->query('search') ?? '');
        $categorySlug = $request->query('category');
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        $storeSlug = $request->query('store');
        $sort = $request->query('sort', 'newest');
        $perPage = (int) $request->query('per_page', 20);

        $builder = Product::where('is_active', true)
            ->with(['business:id,name,slug,logo_path', 'category:id,name,slug']);

        if ($query !== '') {
            $builder->where(function ($q) use ($query): void {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('code', 'like', "%{$query}%")
                    ->orWhereHas('business', fn($bq) => $bq->where('name', 'like', "%{$query}%"));
            });
        }

        if (! empty($categorySlug)) {
            $builder->whereHas('category', fn($q) => $q->where('slug', $categorySlug)->orWhere('id', $categorySlug));
        }

        if (! empty($storeSlug)) {
            $builder->whereHas('business', fn($q) => $q->where('slug', $storeSlug)->orWhere('id', $storeSlug));
        }

        if ($minPrice !== null && is_numeric($minPrice)) {
            $builder->where('selling_price', '>=', (float) $minPrice);
        }

        if ($maxPrice !== null && is_numeric($maxPrice)) {
            $builder->where('selling_price', '<=', (float) $maxPrice);
        }

        if ($sort === 'cheapest') {
            $builder->orderBy('selling_price', 'asc');
        } elseif ($sort === 'highest_price') {
            $builder->orderBy('selling_price', 'desc');
        } else {
            $builder->latest();
        }

        $paginated = $builder->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $paginated->items(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Get detailed product view with gallery, store details, and reviews.
     * GET /api/v1/marketplace/products/{slug}
     */
    public function productDetail(string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)
            ->orWhere('id', $slug)
            ->where('is_active', true)
            ->with([
                'business:id,name,slug,logo_path,phone,address',
                'category:id,name,slug',
                'images',
                'bundleItems.bundledProduct',
            ])
            ->first();

        if (! $product) {
            return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $reviews = CommerceProductReview::where('product_id', $product->id)
            ->where('is_published', true)
            ->with(['globalCustomer:id,name,avatar_url'])
            ->latest()
            ->limit(10)
            ->get();

        $ratingSummary = [
            'average_rating' => round((float) CommerceProductReview::where('product_id', $product->id)->where('is_published', true)->avg('rating'), 1),
            'total_reviews' => CommerceProductReview::where('product_id', $product->id)->where('is_published', true)->count(),
        ];

        $relatedProducts = Product::where('business_id', $product->business_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->limit(6)
            ->get(['id', 'name', 'slug', 'selling_price', 'image_path']);

        return response()->json([
            'success' => true,
            'data' => $product,
            'rating' => $ratingSummary,
            'reviews' => $reviews,
            'related_products' => $relatedProducts,
        ], Response::HTTP_OK);
    }

    /**
     * Get merchant store profile and their product catalog.
     * GET /api/v1/marketplace/stores/{slug}
     */
    public function storeProfile(string $slug): JsonResponse
    {
        $store = Business::where('slug', $slug)
            ->orWhere('id', $slug)
            ->where('is_active', true)
            ->first();

        if (! $store) {
            return response()->json(['success' => false, 'message' => 'Toko tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $storeSettings = CommerceStoreSetting::where('business_id', $store->id)->first();

        $products = Product::where('business_id', $store->id)
            ->where('is_active', true)
            ->with('category:id,name')
            ->latest()
            ->paginate(24);

        return response()->json([
            'success' => true,
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
                'slug' => $store->slug,
                'logo_url' => $store->logo_url,
                'phone' => $store->phone,
                'address' => $store->address,
                'currency' => $store->currency ?? 'IDR',
                'is_open' => $storeSettings?->is_store_open ?? true,
                'banner_url' => $storeSettings?->banner_url,
            ],
            'products' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ],
        ], Response::HTTP_OK);
    }
}
