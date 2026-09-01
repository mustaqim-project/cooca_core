<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ProductCategoryController extends Controller
{
    /**
     * List product categories for active business.
     */
    public function index(Request $request): JsonResponse
    {
        $categories = ProductCategory::latest()->get();

        return response()->json([
            'categories' => ProductCategoryResource::collection($categories),
        ], Response::HTTP_OK);
    }

    /**
     * Store product category.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        /** @var ProductCategory $category */
        $category = ProductCategory::create($validated);

        return response()->json([
            'message' => 'Product category created successfully.',
            'category' => new ProductCategoryResource($category),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show product category.
     */
    public function show(Request $request, ProductCategory $productCategory): JsonResponse
    {
        return response()->json([
            'category' => new ProductCategoryResource($productCategory),
        ], Response::HTTP_OK);
    }

    /**
     * Update product category.
     */
    public function update(Request $request, ProductCategory $productCategory): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $productCategory->update($validated);

        return response()->json([
            'message' => 'Product category updated successfully.',
            'category' => new ProductCategoryResource($productCategory),
        ], Response::HTTP_OK);
    }

    /**
     * Delete product category.
     */
    public function destroy(Request $request, ProductCategory $productCategory): JsonResponse
    {
        $productCategory->delete();

        return response()->json([
            'message' => 'Product category deleted successfully.',
        ], Response::HTTP_OK);
    }
}
