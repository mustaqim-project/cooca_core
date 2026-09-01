<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MaterialCategoryResource;
use App\Models\MaterialCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class MaterialCategoryController extends Controller
{
    /**
     * List all material categories.
     */
    public function index(Request $request): JsonResponse
    {
        $categories = MaterialCategory::latest()->get();

        return response()->json([
            'categories' => MaterialCategoryResource::collection($categories),
        ], Response::HTTP_OK);
    }

    /**
     * Store a new material category.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        /** @var MaterialCategory $category */
        $category = MaterialCategory::create($validated);

        return response()->json([
            'message' => 'Material category created successfully.',
            'category' => new MaterialCategoryResource($category),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show material category.
     */
    public function show(Request $request, MaterialCategory $materialCategory): JsonResponse
    {
        return response()->json([
            'category' => new MaterialCategoryResource($materialCategory),
        ], Response::HTTP_OK);
    }

    /**
     * Update material category.
     */
    public function update(Request $request, MaterialCategory $materialCategory): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $materialCategory->update($validated);

        return response()->json([
            'message' => 'Material category updated successfully.',
            'category' => new MaterialCategoryResource($materialCategory),
        ], Response::HTTP_OK);
    }

    /**
     * Delete material category.
     */
    public function destroy(Request $request, MaterialCategory $materialCategory): JsonResponse
    {
        $materialCategory->delete();

        return response()->json([
            'message' => 'Material category deleted successfully.',
        ], Response::HTTP_OK);
    }
}
