<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Purchasing;

use App\Domain\Accounting\AutoJournalService;
use App\Domain\Inventory\StockService;
use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class GoodsReceiptController extends Controller
{
    public function __construct(
        private readonly AutoJournalService $journalService = new AutoJournalService,
        private readonly StockService $stockService = new StockService
    ) {}

    /**
     * Store Goods Receipt from Purchase Order.
     */
    public function store(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($purchaseOrder->business_id !== $business->id) {
            return response()->json(['message' => 'Pesanan pembelian tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $user = $request->user();

        $validated = $request->validate([
            'location_id' => ['required', 'exists:locations,id'],
            'receipt_number' => ['nullable', 'string', 'max:64'],
            'receipt_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.batch_number' => ['nullable', 'string', 'max:64'],
            'items.*.expiry_date' => ['nullable', 'date'],
        ]);

        $goodsReceipt = DB::transaction(function () use ($business, $purchaseOrder, $validated, $user) {
            $receiptNumber = $validated['receipt_number'] ?? ('GR-' . date('Ym') . '-' . rand(1000, 9999));

            $gr = GoodsReceipt::create([
                'business_id' => $business->id,
                'location_id' => $validated['location_id'],
                'supplier_id' => $purchaseOrder->supplier_id,
                'purchase_order_id' => $purchaseOrder->id,
                'receipt_number' => $receiptNumber,
                'receipt_date' => $validated['receipt_date'],
                'status' => 'completed',
                'notes' => $validated['notes'] ?? null,
                'received_by' => $user->id,
            ]);

            foreach ($validated['items'] as $item) {
                $qty = (float) $item['quantity'];
                if ($qty <= 0) {
                    continue;
                }
                $cost = (float) $item['unit_cost'];

                GoodsReceiptItem::create([
                    'goods_receipt_id' => $gr->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                    'batch_number' => $item['batch_number'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                ]);

                // Record stock movement & update inventory stock
                $this->stockService->recordMovement(
                    businessId: $business->id,
                    locationId: $validated['location_id'],
                    productId: $item['product_id'],
                    movementType: StockMovement::TYPE_GOODS_RECEIPT,
                    quantityChange: $qty,
                    unitCost: $cost,
                    referenceId: $gr->id,
                    referenceNumber: $receiptNumber,
                    batchNumber: $item['batch_number'] ?? null,
                    expiryDate: $item['expiry_date'] ?? null,
                    notes: "Penerimaan PO {$purchaseOrder->po_number}",
                    userId: $user->id
                );
            }

            // Update Purchase Order status
            $purchaseOrder->update(['status' => PurchaseOrder::STATUS_COMPLETED]);

            return $gr;
        });

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
