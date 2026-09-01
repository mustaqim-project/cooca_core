<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Sales;

use App\Domain\Sales\SalesPipelineService;
use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class QuotationController extends Controller
{
    public function __construct(
        private readonly SalesPipelineService $salesPipelineService = new SalesPipelineService
    ) {}

    /**
     * List quotations with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $query = Quotation::with(['customer:id,name,company_name,phone'])
            ->where('business_id', $business->id)
            ->withCount('items')
            ->latest('date');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('quotation_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('customer', fn($c) => $c->where('name', 'LIKE', "%{$search}%"));
            });
        }

        $quotations = $query->paginate(20);

        return response()->json([
            'quotations' => $quotations->items(),
            'pagination' => [
                'current_page' => $quotations->currentPage(),
                'last_page' => $quotations->lastPage(),
                'per_page' => $quotations->perPage(),
                'total' => $quotations->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Create new quotation.
     */
    public function store(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'quotation_number' => ['nullable', 'string', 'max:64'],
            'date' => ['required', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:date'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.product_name' => ['required_without:items.*.product_id', 'nullable', 'string'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $quotation = $this->salesPipelineService->createQuotation($business, $validated);

        return response()->json([
            'message' => "Surat Penawaran {$quotation->quotation_number} berhasil diterbitkan.",
            'quotation' => $quotation->load(['customer', 'items.product']),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show quotation detail.
     */
    public function show(Request $request, Quotation $quotation): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($quotation->business_id !== $business->id) {
            return response()->json(['message' => 'Penawaran tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $quotation->load(['customer', 'items.product.outputUnit', 'salesOrder']);

        return response()->json([
            'quotation' => $quotation,
        ], Response::HTTP_OK);
    }

    /**
     * Convert quotation to Sales Order.
     */
    public function convert(Request $request, Quotation $quotation): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($quotation->business_id !== $business->id) {
            return response()->json(['message' => 'Penawaran tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        if ($quotation->salesOrder) {
            return response()->json([
                'message' => "Penawaran ini sudah dikonversi ke Pesanan {$quotation->salesOrder->so_number}.",
                'sales_order' => $quotation->salesOrder->load(['customer', 'items']),
            ], Response::HTTP_OK);
        }

        $salesOrder = $this->salesPipelineService->convertQuotationToSalesOrder($quotation);

        return response()->json([
            'message' => "Penawaran {$quotation->quotation_number} berhasil diubah menjadi Pesanan Penjualan {$salesOrder->so_number}!",
            'sales_order' => $salesOrder->load(['customer', 'items.product']),
        ], Response::HTTP_CREATED);
    }
}
