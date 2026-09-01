<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Calculation\CalculationEngine;
use App\Domain\Versioning\ApprovalWorkflowService;
use App\Domain\Versioning\ProductCostVersionService;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCostVersionResource;
use App\Models\CostModel;
use App\Models\Product;
use App\Models\ProductCostVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

final class ProductCostVersionController extends Controller
{
    /**
     * List all cost versions for a product.
     */
    public function index(Request $request, Product $product): JsonResponse
    {
        $versions = ProductCostVersion::where('product_id', $product->id)
            ->latest('version_number')
            ->get();

        return response()->json([
            'data' => ProductCostVersionResource::collection($versions),
        ], Response::HTTP_OK);
    }

    /**
     * Create a new immutable snapshot cost version.
     */
    public function store(
        Request $request,
        Product $product,
        CalculationEngine $engine,
        ProductCostVersionService $versionService
    ): JsonResponse {
        $validated = $request->validate([
            'cost_model_id' => ['nullable', 'string', 'exists:cost_models,id'],
            'version_label' => ['nullable', 'string', 'max:255'],
            'effective_from' => ['nullable', 'date'],
        ]);

        /** @var CostModel|null $costModel */
        $costModel = isset($validated['cost_model_id'])
            ? CostModel::findOrFail($validated['cost_model_id'])
            : $product->costModels()->where('is_active', true)->firstOrFail();

        $dto = $engine->calculate($costModel);

        $version = $versionService->createVersion(
            $product,
            $costModel,
            $dto,
            $validated['version_label'] ?? null,
            $validated['effective_from'] ?? null
        );

        return response()->json([
            'message' => 'Product cost version snapshot created successfully.',
            'version' => new ProductCostVersionResource($version),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show cost version details.
     */
    public function show(Request $request, ProductCostVersion $productCostVersion): JsonResponse
    {
        return response()->json([
            'version' => new ProductCostVersionResource($productCostVersion),
        ], Response::HTTP_OK);
    }

    /**
     * Transition status to in_review.
     */
    public function submitForReview(Request $request, ProductCostVersion $productCostVersion, ApprovalWorkflowService $workflow): JsonResponse
    {
        try {
            $version = $workflow->transition($productCostVersion, ProductCostVersion::STATUS_IN_REVIEW, $request->input('notes'));

            return response()->json([
                'message' => 'Cost version submitted for review.',
                'version' => new ProductCostVersionResource($version),
            ], Response::HTTP_OK);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Transition status to approved.
     */
    public function approve(Request $request, ProductCostVersion $productCostVersion, ApprovalWorkflowService $workflow): JsonResponse
    {
        try {
            $version = $workflow->transition($productCostVersion, ProductCostVersion::STATUS_APPROVED, $request->input('notes'));

            return response()->json([
                'message' => 'Cost version approved successfully.',
                'version' => new ProductCostVersionResource($version),
            ], Response::HTTP_OK);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Transition status to published.
     */
    public function publish(Request $request, ProductCostVersion $productCostVersion, ApprovalWorkflowService $workflow): JsonResponse
    {
        try {
            $version = $workflow->transition($productCostVersion, ProductCostVersion::STATUS_PUBLISHED, $request->input('notes'));

            return response()->json([
                'message' => 'Cost version published successfully.',
                'version' => new ProductCostVersionResource($version),
            ], Response::HTTP_OK);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Transition status to archived.
     */
    public function archive(Request $request, ProductCostVersion $productCostVersion, ApprovalWorkflowService $workflow): JsonResponse
    {
        try {
            $version = $workflow->transition($productCostVersion, ProductCostVersion::STATUS_ARCHIVED, $request->input('notes'));

            return response()->json([
                'message' => 'Cost version archived successfully.',
                'version' => new ProductCostVersionResource($version),
            ], Response::HTTP_OK);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
