<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CostCategoryResource;
use App\Http\Resources\CostModelResource;
use App\Models\CostCategory;
use App\Models\CostComponent;
use App\Models\CostModel;
use App\Models\Product;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

final class CostModelController extends Controller
{
    /**
     * List all available cost categories and components.
     */
    public function categories(Request $request): JsonResponse
    {
        $categories = CostCategory::with('components')->get();

        return response()->json([
            'categories' => CostCategoryResource::collection($categories),
        ], Response::HTTP_OK);
    }

    /**
     * Create a new cost model for a product.
     */
    public function store(Request $request, Product $product): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'method' => ['required', 'string', Rule::in(CostModel::METHODS)],
            'output_basis' => ['nullable', 'string', Rule::in([CostModel::BASIS_PLANNED, CostModel::BASIS_ACTUAL, CostModel::BASIS_SELLABLE])],
            'notes' => ['nullable', 'string'],
        ]);

        /** @var CostModel $costModel */
        $costModel = CostModel::create([
            'business_id' => $business->id,
            'product_id' => $product->id,
            'name' => $validated['name'],
            'method' => $validated['method'],
            'output_basis' => $validated['output_basis'] ?? CostModel::BASIS_PLANNED,
            'is_active' => true,
            'notes' => $validated['notes'] ?? null,
        ]);

        $costModel->load(['product', 'components', 'bomHeader']);

        return response()->json([
            'message' => 'Cost model created successfully.',
            'cost_model' => new CostModelResource($costModel),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show cost model.
     */
    public function show(Request $request, CostModel $costModel): JsonResponse
    {
        $costModel->load(['product', 'components', 'bomHeader.items.material', 'bomHeader.items.unit']);

        return response()->json([
            'cost_model' => new CostModelResource($costModel),
        ], Response::HTTP_OK);
    }

    /**
     * Update cost model.
     */
    public function update(Request $request, CostModel $costModel): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['string', 'max:255'],
            'method' => ['string', Rule::in(CostModel::METHODS)],
            'output_basis' => ['string', Rule::in([CostModel::BASIS_PLANNED, CostModel::BASIS_ACTUAL, CostModel::BASIS_SELLABLE])],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $costModel->update($validated);
        $costModel->load(['product', 'components', 'bomHeader']);

        return response()->json([
            'message' => 'Cost model updated successfully.',
            'cost_model' => new CostModelResource($costModel),
        ], Response::HTTP_OK);
    }

    /**
     * Delete cost model.
     */
    public function destroy(Request $request, CostModel $costModel): JsonResponse
    {
        $costModel->delete();

        return response()->json([
            'message' => 'Cost model deleted successfully.',
        ], Response::HTTP_OK);
    }

    /**
     * Sync components attached to a cost model with inclusion flag.
     */
    public function syncComponents(Request $request, CostModel $costModel): JsonResponse
    {
        $validated = $request->validate([
            'components' => ['required', 'array'],
            'components.*.cost_component_id' => ['required', 'string', 'exists:cost_components,id'],
            'components.*.is_included_in_hpp' => ['required', 'boolean'],
            'components.*.notes' => ['nullable', 'string'],
        ]);

        $syncData = [];
        $warnings = [];

        foreach ($validated['components'] as $item) {
            $component = CostComponent::with('category')->find($item['cost_component_id']);

            if ($component !== null) {
                // Accounting validation warning (§36 blueprint)
                if ($component->category?->code === CostCategory::CODE_OTHER && $item['is_included_in_hpp']) {
                    $warnings[] = "Warning: Component '{$component->name}' is categorized as non-production/Opex. Including it in standard COGS/HPP is non-standard in GAAP/IFRS.";
                }
            }

            $syncData[$item['cost_component_id']] = [
                'id' => (string) Str::uuid(),
                'is_included_in_hpp' => $item['is_included_in_hpp'],
                'notes' => $item['notes'] ?? null,
            ];
        }

        $costModel->components()->sync($syncData);
        $costModel->load(['components']);

        return response()->json([
            'message' => 'Cost model components updated successfully.',
            'warnings' => $warnings,
            'cost_model' => new CostModelResource($costModel),
        ], Response::HTTP_OK);
    }
}
