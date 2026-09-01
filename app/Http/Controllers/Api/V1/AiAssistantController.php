<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Ai\AiSalesAnalysisService;
use App\Domain\Billing\EntitlementService;
use App\Http\Controllers\Controller;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AiAssistantController extends Controller
{
    public function __construct(
        private readonly AiSalesAnalysisService $aiService,
        private readonly EntitlementService $entitlementService,
    ) {}

    /**
     * Sales forecasting (linear trend + day-of-week seasonality).
     */
    public function salesForecasting(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $days = (int) $request->input('days', 14);

        $result = $this->aiService->getSalesForecasting($business, $days);

        return response()->json($result, Response::HTTP_OK);
    }

    /**
     * Stock prediction and reorder recommendations.
     */
    public function stockPrediction(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $result = $this->aiService->getStockPredictionAndReorder($business);

        return response()->json($result, Response::HTTP_OK);
    }

    /**
     * Anomaly & fraud detection (POS).
     */
    public function fraudDetection(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $result = $this->aiService->detectAnomaliesAndFraud($business);

        return response()->json($result, Response::HTTP_OK);
    }

    /**
     * Product profitability matrix (BCG-style).
     */
    public function profitabilityMatrix(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $result = $this->aiService->getProductProfitabilityMatrix($business);

        return response()->json($result, Response::HTTP_OK);
    }

    /**
     * Smart pricing recommendations.
     */
    public function smartPricing(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $result = $this->aiService->getSmartPricingRecommendations($business);

        return response()->json($result, Response::HTTP_OK);
    }

    /**
     * Smart promo / bundle recommendations.
     */
    public function smartPromo(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $result = $this->aiService->getSmartPromoRecommendations($business);

        return response()->json($result, Response::HTTP_OK);
    }

    /**
     * Customer RFM segmentation.
     */
    public function customerRfm(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $result = $this->aiService->getCustomerBehaviorRfm($business);

        return response()->json($result, Response::HTTP_OK);
    }

    /**
     * Cashier performance scorecard.
     */
    public function cashierPerformance(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $result = $this->aiService->getCashierPerformance($business);

        return response()->json($result, Response::HTTP_OK);
    }

    /**
     * Natural language query interface – main AI chat endpoint.
     */
    public function chat(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();

        $request->validate([
            'query' => ['required', 'string', 'max:1000'],
        ]);

        // Check entitlement
        $subscription = $this->entitlementService->getSubscription($business);
        if (!$subscription->isCorePlan() && ($subscription->ai_tokens_remaining ?? 0) <= 0) {
            return response()->json([
                'message' => 'Kuota token AI Anda habis. Upgrade ke Core Plan untuk akses AI tanpa batas.',
                'upgrade_required' => true,
            ], Response::HTTP_PAYMENT_REQUIRED);
        }

        $query = (string) $request->input('query');
        $result = $this->aiService->askNaturalLanguage($business, $query);

        return response()->json($result, Response::HTTP_OK);
    }

    /**
     * Confirm and execute an AI-proposed action (Human-in-the-Loop).
     */
    public function executeAction(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();

        $validated = $request->validate([
            'action_type' => ['required', 'string', 'in:create_invoice,create_quotation'],
            'payload' => ['required', 'array'],
        ]);

        $result = $this->aiService->executeActionDraft(
            $business,
            $user,
            $validated['action_type'],
            $validated['payload'],
        );

        $status = $result['success'] ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY;

        return response()->json($result, $status);
    }

    /**
     * Current token usage / subscription info.
     */
    public function tokenUsage(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $subscription = $this->entitlementService->getSubscription($business);

        return response()->json([
            'plan' => $subscription->plan_code,
            'is_core_plan' => $subscription->isCorePlan(),
            'ai_tokens_monthly_allowance' => $subscription->ai_tokens_monthly_allowance,
            'ai_tokens_remaining' => $subscription->ai_tokens_remaining,
            'last_reset_at' => $subscription->last_token_reset_at,
            'status' => $subscription->status,
        ], Response::HTTP_OK);
    }
}
