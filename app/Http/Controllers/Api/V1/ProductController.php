<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ProductController extends Controller
{
    /**
     * List products with filters and search.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['outputUnit', 'category', 'costModels']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        if ($request->filled('search')) {
            $search = (string) $request->query('search');
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $products = $query->latest()->paginate(25);

        return response()->json([
            'data' => ProductResource::collection($products),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Store a new product.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'output_unit_id' => ['required', 'string', 'exists:units,id'],
            'category_id' => ['nullable', 'string', 'exists:product_categories,id'],
            'description' => ['nullable', 'string'],
            'business_type_hint' => ['nullable', 'string', 'in:fnb,retail,manufacturing,service,workshop,project,general'],
        ]);

        /** @var Product $product */
        $product = Product::create($validated);
        $product->load(['outputUnit', 'category', 'costModels']);

        return response()->json([
            'message' => 'Product created successfully.',
            'product' => new ProductResource($product),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show product details.
     */
    public function show(Request $request, Product $product): JsonResponse
    {
        $product->load(['outputUnit', 'category', 'costModels.components', 'costModels.bomHeader.items']);

        return response()->json([
            'product' => new ProductResource($product),
        ], Response::HTTP_OK);
    }

    /**
     * Update product.
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'output_unit_id' => ['string', 'exists:units,id'],
            'category_id' => ['nullable', 'string', 'exists:product_categories,id'],
            'description' => ['nullable', 'string'],
            'business_type_hint' => ['nullable', 'string', 'in:fnb,retail,manufacturing,service,workshop,project,general'],
        ]);

        $product->update($validated);
        $product->load(['outputUnit', 'category', 'costModels']);

        return response()->json([
            'message' => 'Product updated successfully.',
            'product' => new ProductResource($product),
        ], Response::HTTP_OK);
    }

    /**
     * Delete product.
     */
    public function destroy(Request $request, Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully.',
        ], Response::HTTP_OK);
    }
}
