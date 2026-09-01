<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Purchasing;

use App\Domain\Commerce\PurchaseOrderService;
use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PurchaseOrderController extends Controller
{
    public function __construct(
        private readonly PurchaseOrderService $poService = new PurchaseOrderService
    ) {}

    /**
     * List purchase orders.
     */
    public function index(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $query = PurchaseOrder::where('business_id', $business->id)
            ->with(['customer:id,name,company_name', 'supplier:id,name', 'items'])
            ->latest('order_date');

        if ($request->filled('po_type')) {
            $query->where('po_type', $request->get('po_type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$search}%")->orWhere('company_name', 'like', "%{$search}%"))
                    ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$search}%"));
            });
        }

        $purchaseOrders = $query->paginate(20);

        return response()->json([
            'purchase_orders' => $purchaseOrders->items(),
            'pagination' => [
                'current_page' => $purchaseOrders->currentPage(),
                'last_page' => $purchaseOrders->lastPage(),
                'per_page' => $purchaseOrders->perPage(),
                'total' => $purchaseOrders->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Store new purchase order.
     */
    public function store(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'po_type' => ['required', 'string', 'in:customer,supplier'],
            'po_number' => ['nullable', 'string', 'max:100'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'customer_id' => ['nullable', 'required_if:po_type,customer', 'exists:customers,id'],
            'supplier_id' => ['nullable', 'required_if:po_type,supplier', 'exists:suppliers,id'],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date'],
            'discount_type' => ['nullable', 'string', 'in:percentage,fixed'],
            'discount_value' => ['nullable', 'numeric', 'gte:0'],
            'tax_percentage' => ['nullable', 'numeric', 'gte:0', 'lte:100'],
            'terms_and_conditions' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.material_id' => ['nullable', 'exists:materials,id'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.sku' => ['nullable', 'string', 'max:100'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_id' => ['required', 'exists:units,id'],
            'items.*.unit_price' => ['required', 'numeric', 'gte:0'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        $po = $this->poService->createPurchaseOrder($business, $validated, $validated['items']);

        return response()->json([
            'message' => "Pesanan {$po->po_number} berhasil dibuat.",
            'purchase_order' => $po->load(['customer', 'supplier', 'items.unit']),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show purchase order details.
     */
    public function show(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($purchaseOrder->business_id !== $business->id) {
            return response()->json(['message' => 'Pesanan pembelian tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $purchaseOrder->load(['customer', 'supplier', 'items.unit', 'items.product', 'items.material']);

        return response()->json([
            'purchase_order' => $purchaseOrder,
        ], Response::HTTP_OK);
    }

    /**
     * Update draft purchase order.
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($purchaseOrder->business_id !== $business->id) {
            return response()->json(['message' => 'Pesanan pembelian tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        if ($purchaseOrder->status !== PurchaseOrder::STATUS_DRAFT) {
            return response()->json(['message' => 'Hanya pesanan berstatus draft yang dapat diubah.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validated = $request->validate([
            'reference_number' => ['nullable', 'string', 'max:100'],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date'],
            'discount_type' => ['nullable', 'string', 'in:percentage,fixed'],
            'discount_value' => ['nullable', 'numeric', 'gte:0'],
            'tax_percentage' => ['nullable', 'numeric', 'gte:0', 'lte:100'],
            'terms_and_conditions' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $purchaseOrder->update($validated);

        return response()->json([
            'message' => 'Pesanan pembelian berhasil diperbarui.',
            'purchase_order' => $purchaseOrder->fresh(['customer', 'supplier', 'items']),
        ], Response::HTTP_OK);
    }

    /**
     * Confirm purchase order.
     */
    public function confirm(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($purchaseOrder->business_id !== $business->id) {
            return response()->json(['message' => 'Pesanan pembelian tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $this->poService->confirmPurchaseOrder($purchaseOrder);

        return response()->json([
            'message' => "Pesanan {$purchaseOrder->po_number} berhasil dikonfirmasi.",
            'purchase_order' => $purchaseOrder->fresh(['customer', 'supplier', 'items']),
        ], Response::HTTP_OK);
    }

    /**
     * Cancel purchase order.
     */
    public function cancel(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($purchaseOrder->business_id !== $business->id) {
            return response()->json(['message' => 'Pesanan pembelian tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $this->poService->cancelPurchaseOrder($purchaseOrder);

        return response()->json([
            'message' => "Pesanan {$purchaseOrder->po_number} berhasil dibatalkan.",
            'purchase_order' => $purchaseOrder->fresh(),
        ], Response::HTTP_OK);
    }

    /**
     * Generate invoice from purchase order.
     */
    public function generateInvoice(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($purchaseOrder->business_id !== $business->id) {
            return response()->json(['message' => 'Pesanan pembelian tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $invoice = $this->poService->generateInvoiceFromPO($purchaseOrder);

        return response()->json([
            'message' => "Faktur {$invoice->invoice_number} berhasil diterbitkan dari PO {$purchaseOrder->po_number}.",
            'invoice' => $invoice->load(['customer', 'items']),
        ], Response::HTTP_CREATED);
    }

    /**
     * Delete draft purchase order.
     */
    public function destroy(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($purchaseOrder->business_id !== $business->id) {
            return response()->json(['message' => 'Pesanan pembelian tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        if ($purchaseOrder->status !== PurchaseOrder::STATUS_DRAFT) {
            return response()->json(['message' => 'Hanya pesanan berstatus draft yang dapat dihapus.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $purchaseOrder->items()->delete();
        $purchaseOrder->delete();

        return response()->json([
            'message' => 'Pesanan pembelian berhasil dihapus.',
        ], Response::HTTP_OK);
    }
}
