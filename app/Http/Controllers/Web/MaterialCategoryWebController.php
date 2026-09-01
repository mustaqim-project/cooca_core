<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MaterialCategory;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class MaterialCategoryWebController extends Controller
{
    /**
     * Store a new material category (via regular form or AJAX modal).
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $category = MaterialCategory::create([
            'business_id' => $business->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kategori bahan baku berhasil ditambahkan.',
                'category' => $category,
            ], 201);
        }

        return back()->with('success', 'Kategori bahan baku berhasil ditambahkan.');
    }

    /**
     * Update an existing material category.
     */
    public function update(Request $request, MaterialCategory $category): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $category->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kategori bahan baku berhasil diperbarui.',
                'category' => $category,
            ]);
        }

        return back()->with('success', 'Kategori bahan baku berhasil diperbarui.');
    }

    /**
     * Delete a material category.
     */
    public function destroy(MaterialCategory $category): RedirectResponse
    {
        $category->delete();

        return back()->with('success', 'Kategori bahan baku berhasil dihapus.');
    }
}
