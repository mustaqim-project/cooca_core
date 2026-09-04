<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Purchasing;

use App\Domain\Report\PurchaseReportService;
use App\Http\Controllers\Controller;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reporting pembelian berbasis SNAPSHOT harga beli pada detail Purchase Order.
 *
 * Semua average / min / max harga dibaca dari snapshot transaksi
 * (`purchase_price_snapshot`), bukan dari harga master produk/supplier.
 */
final class PurchaseReportController extends Controller
{
    public function __construct(
        private readonly PurchaseReportService $reportService = new PurchaseReportService
    ) {}

    /**
     * Ringkasan pembelian: total qty, total nilai, average harga beli, min & max.
     */
    public function summary(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $from = $request->filled('start_date') ? Carbon::parse($request->get('start_date')) : null;
        $to = $request->filled('end_date') ? Carbon::parse($request->get('end_date')) : null;

        $report = $this->reportService->summary(
            businessId: $business->id,
            from: $from,
            to: $to,
            supplierId: $request->filled('supplier_id') ? (string) $request->get('supplier_id') : null
        );

        return response()->json($report, Response::HTTP_OK);
    }

    /**
     * Riwayat perubahan harga beli per produk (chronological).
     */
    public function history(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $from = $request->filled('start_date') ? Carbon::parse($request->get('start_date')) : null;
        $to = $request->filled('end_date') ? Carbon::parse($request->get('end_date')) : null;

        $history = $this->reportService->history(
            businessId: $business->id,
            productId: $request->filled('product_id') ? (string) $request->get('product_id') : null,
            from: $from,
            to: $to
        );

        return response()->json([
            'history' => $history,
        ], Response::HTTP_OK);
    }

    /**
     * Average harga beli per periode (bulanan).
     */
    public function period(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $from = $request->filled('start_date') ? Carbon::parse($request->get('start_date')) : null;
        $to = $request->filled('end_date') ? Carbon::parse($request->get('end_date')) : null;

        $summary = $this->reportService->summary(
            businessId: $business->id,
            from: $from,
            to: $to,
            supplierId: $request->filled('supplier_id') ? (string) $request->get('supplier_id') : null
        );

        return response()->json([
            'by_period' => $summary['by_period'],
        ], Response::HTTP_OK);
    }

    /**
     * Average harga beli per supplier.
     */
    public function supplier(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $from = $request->filled('start_date') ? Carbon::parse($request->get('start_date')) : null;
        $to = $request->filled('end_date') ? Carbon::parse($request->get('end_date')) : null;

        $summary = $this->reportService->summary(
            businessId: $business->id,
            from: $from,
            to: $to,
            supplierId: $request->filled('supplier_id') ? (string) $request->get('supplier_id') : null
        );

        return response()->json([
            'by_supplier' => $summary['by_supplier'],
        ], Response::HTTP_OK);
    }

    /**
     * Average harga beli per produk.
     */
    public function product(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $from = $request->filled('start_date') ? Carbon::parse($request->get('start_date')) : null;
        $to = $request->filled('end_date') ? Carbon::parse($request->get('end_date')) : null;

        $summary = $this->reportService->summary(
            businessId: $business->id,
            from: $from,
            to: $to,
            supplierId: $request->filled('supplier_id') ? (string) $request->get('supplier_id') : null
        );

        return response()->json([
            'by_product' => $summary['by_product'],
        ], Response::HTTP_OK);
    }
}