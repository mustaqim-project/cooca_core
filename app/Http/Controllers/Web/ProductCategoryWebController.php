<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class ProductCategoryWebController extends Controller
{
    /**
     * Store a new product category (via regular form or AJAX modal).
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name'                      => ['required', 'string', 'max:150'],
            'description'               => ['nullable', 'string', 'max:500'],
            'marketplace_category_id'   => ['nullable', 'string', 'max:50'],
            'marketplace_category_name' => ['nullable', 'string', 'max:100'],
            'cascade_to_products'       => ['nullable', 'boolean'],
        ]);

        $marketplaceCategoryName = $validated['marketplace_category_name'] ?? null;
        if (! empty($validated['marketplace_category_id']) && empty($marketplaceCategoryName)) {
            $matched = \App\Domain\Marketplace\MarketplaceCategoryRegistry::find($validated['marketplace_category_id']);
            $marketplaceCategoryName = $matched['name'] ?? null;
        }

        $category = ProductCategory::create([
            'business_id'               => $business->id,
            'name'                      => $validated['name'],
            'description'               => $validated['description'] ?? null,
            'marketplace_category_id'   => $validated['marketplace_category_id'] ?? null,
            'marketplace_category_name' => $marketplaceCategoryName,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Kategori produk berhasil ditambahkan.',
                'category' => $category,
            ], 201);
        }

        return back()->with('success', 'Kategori produk berhasil ditambahkan.');
    }

    /**
     * Update an existing product category.
     */
    public function update(Request $request, ProductCategory $category): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name'                      => ['required', 'string', 'max:150'],
            'description'               => ['nullable', 'string', 'max:500'],
            'marketplace_category_id'   => ['nullable', 'string', 'max:50'],
            'marketplace_category_name' => ['nullable', 'string', 'max:100'],
            'cascade_to_products'       => ['nullable', 'boolean'],
        ]);

        $marketplaceCategoryName = $validated['marketplace_category_name'] ?? null;
        if (! empty($validated['marketplace_category_id']) && empty($marketplaceCategoryName)) {
            $matched = \App\Domain\Marketplace\MarketplaceCategoryRegistry::find($validated['marketplace_category_id']);
            $marketplaceCategoryName = $matched['name'] ?? null;
        }

        $category->update([
            'name'                      => $validated['name'],
            'description'               => $validated['description'] ?? null,
            'marketplace_category_id'   => $validated['marketplace_category_id'] ?? null,
            'marketplace_category_name' => $marketplaceCategoryName,
        ]);

        // Cascade category update to all products and their marketplace mappings
        if ($request->boolean('cascade_to_products', true) && ! empty($category->marketplace_category_id)) {
            $productIds = $category->products()->pluck('id');
            if ($productIds->isNotEmpty()) {
                $mappings = \App\Models\MarketplaceProductMapping::whereIn('product_id', $productIds)->get();
                foreach ($mappings as $mapping) {
                    $raw = (array) ($mapping->raw_metadata ?? []);
                    $raw['category_id']   = $category->marketplace_category_id;
                    $raw['category_name'] = $category->marketplace_category_name;
                    $mapping->update(['raw_metadata' => $raw]);
                }
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Kategori produk berhasil diperbarui.',
                'category' => $category,
            ]);
        }

        return back()->with('success', 'Kategori produk berhasil diperbarui.');
    }

    /**
     * Delete a product category.
     */
    public function destroy(ProductCategory $category): RedirectResponse
    {
        $category->delete();

        return back()->with('success', 'Kategori produk berhasil dihapus.');
    }
}
