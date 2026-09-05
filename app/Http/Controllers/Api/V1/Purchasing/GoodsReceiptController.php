<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Purchasing;

use App\Domain\Inventory\StockService;
use App\Domain\Purchasing\GoodsReceiptService;
use App\Domain\Purchasing\SupplierInvoiceService;
use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

final class GoodsReceiptController extends Controller
{
    public function __construct(
        private readonly StockService $stockService = new StockService,
        private readonly GoodsReceiptService $goodsReceiptService = new GoodsReceiptService,
        private readonly SupplierInvoiceService $supplierInvoiceService = new SupplierInvoiceService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $receipts = GoodsReceipt::with(['supplier', 'location', 'items.product'])
            ->where('business_id', $business->id)
            ->latest('receipt_date')
            ->paginate(20);
        return response()->json(['goods_receipts' => $receipts]);
    }

    public function show(Request $request, GoodsReceipt $goodsReceipt): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($goodsReceipt->business_id !== $business->id) {
            return response()->json(['message' => 'Goods Receipt tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }
        return response()->json(['goods_receipt' => $goodsReceipt->load(['supplier', 'location', 'items.product', 'supplierInvoice'])]);
    }

    /**
     * Store Goods Receipt from Purchase Order.
     */
    public function store(Request $request, ?PurchaseOrder $purchaseOrder = null): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();

        $validated = $request->validate([
            'location_id' => ['required', 'exists:locations,id'],
            'purchase_order_id' => ['required', 'exists:purchase_orders,id'],
            'receipt_number' => ['nullable', 'string', 'max:64'],
            'receipt_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.material_id' => ['nullable', 'exists:materials,id'],
            'items.*.item_name' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.batch_number' => ['nullable', 'string', 'max:64'],
            'items.*.expiry_date' => ['nullable', 'date'],
        ]);

        $purchaseOrder ??= PurchaseOrder::findOrFail($validated['purchase_order_id']);
        if ($purchaseOrder->business_id !== $business->id) {
            return response()->json(['message' => 'Pesanan pembelian tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $goodsReceipt = $this->goodsReceiptService->receive($purchaseOrder, $validated, $user?->id);

        return response()->json([
            'message' => 'Penerimaan barang fisik berhasil dicatat dan stok gudang telah diperbarui.',
            'goods_receipt' => $goodsReceipt->load(['items.product', 'location']),
        ], Response::HTTP_CREATED);
    }

    /**
     * 1-Click Instant Stock-In (Beli Langsung ke Stok tanpa alur PO).
     */
    public function instantStockIn(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();

        $validated = $request->validate([
            'location_id' => ['required', 'exists:locations,id'],
            'product_id' => ['required_without:items', 'exists:products,id'],
            'quantity' => ['required_without:items', 'numeric', 'min:0.01'],
            'unit_cost' => ['required_without:items', 'numeric', 'min:0'],
            'items' => ['nullable', 'array', 'min:1'],
            'items.*.product_id' => ['required_with:items', 'exists:products,id'],
            'items.*.quantity' => ['required_with:items', 'numeric', 'min:0.01'],
            'items.*.unit_cost' => ['required_with:items', 'numeric', 'min:0'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $movement = DB::transaction(function () use ($business, $validated, $user) {
            $item = $validated['items'][0] ?? $validated;
            $qty = (float) $item['quantity'];
            $cost = (float) $item['unit_cost'];

            return $this->stockService->recordMovement(
                businessId: $business->id,
                locationId: $validated['location_id'],
                productId: $item['product_id'],
                movementType: StockMovement::TYPE_GOODS_RECEIPT,
                quantityChange: $qty,
                unitCost: $cost,
                referenceId: null,
                referenceNumber: null,
                notes: $validated['notes'] ?? 'Beli langsung ke stok (Mobile App)',
                userId: $user->id
            );
        });

        return response()->json([
            'message' => 'Pembelian langsung berhasil dicatat! Stok produk telah bertambah seketika.',
            'movement' => $movement->load(['product.outputUnit', 'location']),
            'receipt' => $movement,
        ], Response::HTTP_CREATED);
    }
}
