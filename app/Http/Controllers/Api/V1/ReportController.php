<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Report\ReportingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ReportController extends Controller
{
    public function __construct(private readonly ReportingService $service = new ReportingService) {}

    /**
     * Report 1: HPP per Product.
     */
    public function hppPerProduct(Request $request): JsonResponse
    {
        $report = $this->service->hppPerProduct();

        return response()->json([
            'report_type' => 'hpp_per_product',
            'generated_at' => now()->toIso8601String(),
            'data' => $report,
        ], Response::HTTP_OK);
    }

    /**
     * Report 2: Cost Breakdown Summary.
     */
    public function costBreakdown(Request $request): JsonResponse
    {
        $summary = $this->service->costBreakdownSummary();

        return response()->json([
            'report_type' => 'cost_breakdown_summary',
            'generated_at' => now()->toIso8601String(),
            'summary' => $summary,
        ], Response::HTTP_OK);
    }

    /**
     * API Documentation & Endpoint Catalog (§96 Blueprint API-First).
     */
    public function docs(): JsonResponse
    {
        return response()->json([
            'api_name' => 'Universal HPP Calculator Engine API',
            'version' => 'v1',
            'documentation_url' => 'https://github.com/universal-hpp/calculator-hpp',
            'modules' => [
                'auth' => '/api/v1/auth',
                'units' => '/api/v1/units',
                'materials' => '/api/v1/materials',
                'products' => '/api/v1/products',
                'bom' => '/api/v1/bom-headers',
                'labor' => '/api/v1/labor-rates',
                'machines' => '/api/v1/machines',
                'overheads' => '/api/v1/overheads',
                'costing' => '/api/v1/costing/calculate',
                'pricing' => '/api/v1/pricing/calculate',
                'profitability' => '/api/v1/profitability/analyze',
                'bep' => '/api/v1/bep/calculate',
                'variance' => '/api/v1/variance/analyze',
                'simulation' => '/api/v1/cost-models/{slug}/simulate',
                'templates' => '/api/v1/business-type-templates',
                'reports' => '/api/v1/reports',
            ],
        ], Response::HTTP_OK);
    }
}
