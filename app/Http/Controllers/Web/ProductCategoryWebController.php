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
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $category = ProductCategory::create([
            'business_id' => $business->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kategori produk berhasil ditambahkan.',
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
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $category->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kategori produk berhasil diperbarui.',
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
