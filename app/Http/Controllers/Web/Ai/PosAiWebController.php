<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Ai;

use App\Domain\Ai\AiSalesAnalysisService;
use App\Http\Controllers\Controller;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

final class PosAiWebController extends Controller
{
    public function __construct(
        private readonly AiSalesAnalysisService $aiService = new AiSalesAnalysisService
    ) {}

    /**
     * Display AI POS Cockpit Command Center.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();

        // 1. Sales Forecasting
        $forecasting = $this->aiService->getSalesForecasting($business, 14);

        // 2. Stock Prediction & Reorder Suggestions
        $stockPrediction = $this->aiService->getStockPredictionAndReorder($business);

        // 3. Anomaly & Fraud Detection
        $anomalies = $this->aiService->detectAnomaliesAndFraud($business);

        // 4. BCG / Menu Engineering Matrix
        $bcgMatrix = $this->aiService->getProductProfitabilityMatrix($business);

        // 5. Smart Dynamic Pricing Suggestions
        $pricingRecommendations = $this->aiService->getSmartPricingRecommendations($business);

        // 6. Smart Promo & Bundling
        $promoBundles = $this->aiService->getSmartPromoRecommendations($business);

        // 7. Cashier Performance Scorecards
        $cashierPerformance = $this->aiService->getCashierPerformance($business);

        // 8. Customer Behavior (RFM Segments)
        $rfmSegments = $this->aiService->getCustomerBehaviorRfm($business);

        return view('app.ai.index', compact(
            'business',
            'forecasting',
            'stockPrediction',
            'anomalies',
            'bcgMatrix',
            'pricingRecommendations',
            'promoBundles',
            'cashierPerformance',
            'rfmSegments'
        ));
    }

    /**
     * Natural Language AI Query Endpoint (AJAX).
     */
    public function ask(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'query' => ['required', 'string', 'max:500'],
        ]);

        try {
            $response = $this->aiService->askNaturalLanguage($business, $validated['query']);

            return response()->json([
                'success' => true,
                'response' => $response,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pertanyaan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Execute confirmed AI Action Draft (Human-in-the-Loop).
     */
    public function executeAction(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $validated = $request->validate([
            'action_type' => ['required', 'string'],
            'payload' => ['required', 'array'],
        ]);

        try {
            $result = $this->aiService->executeActionDraft($business, $user, $validated['action_type'], $validated['payload']);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengeksekusi aksi: ' . $e->getMessage(),
            ], 422);
        }
    }
}
