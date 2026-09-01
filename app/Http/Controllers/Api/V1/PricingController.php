<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Pricing\PricingEngine;
use App\Http\Controllers\Controller;
use App\Http\Resources\PricingRuleResource;
use App\Models\Fee;
use App\Models\PricingRule;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

final class PricingController extends Controller
{
    public function __construct(private readonly PricingEngine $pricingEngine = new PricingEngine) {}

    /**
     * Calculate selling price and profit metrics live.
     */
    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'hpp' => ['required', 'numeric', 'gte:0'],
            'strategy' => ['required', 'string', Rule::in(PricingRule::STRATEGIES)],
            'percentage_or_value' => ['required', 'numeric'],
            'include_fees' => ['nullable', 'boolean'],
        ]);

        $hpp = (float) $validated['hpp'];
        $val = (float) $validated['percentage_or_value'];
        $strategy = $validated['strategy'];

        try {
            $pricing = match ($strategy) {
                PricingRule::STRATEGY_MARKUP => $this->pricingEngine->fromMarkup($hpp, $val),
                PricingRule::STRATEGY_MARGIN => $this->pricingEngine->fromMargin($hpp, $val),
                PricingRule::STRATEGY_TARGET_PROFIT => $this->pricingEngine->fromTargetProfit($hpp, $val),
                default => $this->pricingEngine->fromMarkup($hpp, $val),
            };

            $netResult = null;
            if (! empty($validated['include_fees'])) {
                $deductionFees = Fee::where('type', Fee::TYPE_PRICE_DEDUCTION)
                    ->where('is_active', true)
                    ->get();
                $netResult = $this->pricingEngine->netRevenue($pricing['selling_price'], $hpp, $deductionFees->all());
            }

            return response()->json([
                'pricing' => $pricing,
                'net_revenue_analysis' => $netResult,
            ], Response::HTTP_OK);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * List all pricing rules.
     */
    public function index(Request $request): JsonResponse
    {
        $rules = PricingRule::latest()->get();

        return response()->json([
            'data' => PricingRuleResource::collection($rules),
        ], Response::HTTP_OK);
    }

    /**
     * Store a new pricing rule.
     */
    public function store(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'cost_model_id' => ['nullable', 'string', 'exists:cost_models,id'],
            'product_id' => ['nullable', 'string', 'exists:products,id'],
            'name' => ['required', 'string', 'max:255'],
            'strategy' => ['required', 'string', Rule::in(PricingRule::STRATEGIES)],
            'value' => ['required', 'numeric'],
            'target_profit_amount' => ['nullable', 'numeric', 'gte:0'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        /** @var PricingRule $rule */
        $rule = PricingRule::create($validated);

        return response()->json([
            'message' => 'Pricing rule created successfully.',
            'pricing_rule' => new PricingRuleResource($rule),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show pricing rule.
     */
    public function show(Request $request, PricingRule $pricingRule): JsonResponse
    {
        return response()->json([
            'pricing_rule' => new PricingRuleResource($pricingRule),
        ], Response::HTTP_OK);
    }

    /**
     * Update pricing rule.
     */
    public function update(Request $request, PricingRule $pricingRule): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['string', 'max:255'],
            'strategy' => ['string', Rule::in(PricingRule::STRATEGIES)],
            'value' => ['numeric'],
            'target_profit_amount' => ['nullable', 'numeric', 'gte:0'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $pricingRule->update($validated);

        return response()->json([
            'message' => 'Pricing rule updated successfully.',
            'pricing_rule' => new PricingRuleResource($pricingRule),
        ], Response::HTTP_OK);
    }

    /**
     * Delete pricing rule.
     */
    public function destroy(Request $request, PricingRule $pricingRule): JsonResponse
    {
        $pricingRule->delete();

        return response()->json([
            'message' => 'Pricing rule deleted successfully.',
        ], Response::HTTP_OK);
    }
}
