<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Sales;

use App\Domain\Sales\SalesPipelineService;
use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SalesOrderController extends Controller
{
    public function __construct(
        private readonly SalesPipelineService $salesPipelineService = new SalesPipelineService
    ) {}

    /**
     * List sales orders with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $query = SalesOrder::with(['customer:id,name,company_name', 'items'])
            ->where('business_id', $business->id)
            ->latest('order_date');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('so_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('customer', fn($c) => $c->where('name', 'LIKE', "%{$search}%"));
            });
        }

        $salesOrders = $query->paginate(20);

        return response()->json([
            'sales_orders' => $salesOrders->items(),
            'pagination' => [
                'current_page' => $salesOrders->currentPage(),
                'last_page' => $salesOrders->lastPage(),
                'per_page' => $salesOrders->perPage(),
                'total' => $salesOrders->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Show sales order detail.
     */
    public function show(Request $request, SalesOrder $salesOrder): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($salesOrder->business_id !== $business->id) {
            return response()->json(['message' => 'Pesanan penjualan tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $salesOrder->load(['customer', 'items.product.outputUnit', 'quotation', 'invoices']);

        return response()->json([
            'sales_order' => $salesOrder,
        ], Response::HTTP_OK);
    }

    /**
     * Generate invoice from sales order.
     */
    public function generateInvoice(Request $request, SalesOrder $salesOrder): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($salesOrder->business_id !== $business->id) {
            return response()->json(['message' => 'Pesanan penjualan tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $invoice = $this->salesPipelineService->generateInvoiceFromSalesOrder($salesOrder);

        return response()->json([
            'message' => "Faktur Penjualan {$invoice->invoice_number} berhasil diterbitkan dari Pesanan {$salesOrder->so_number}.",
            'invoice' => $invoice->load(['customer', 'items']),
        ], Response::HTTP_CREATED);
    }
}
