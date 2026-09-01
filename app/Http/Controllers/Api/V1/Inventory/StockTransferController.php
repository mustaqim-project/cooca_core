<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Inventory;

use App\Domain\Inventory\StockService;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class StockTransferController extends Controller
{
    public function __construct(
        private readonly StockService $stockService = new StockService
    ) {}

    /**
     * List stock transfers between locations.
     */
    public function index(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $query = StockTransfer::where('business_id', $business->id)
            ->with(['sourceLocation:id,name', 'destinationLocation:id,name', 'creator:id,name', 'receiver:id,name'])
            ->withCount('items')
            ->latest('transfer_date');

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('source_location_id')) {
            $query->where('source_location_id', $request->get('source_location_id'));
        }

        if ($request->filled('destination_location_id')) {
            $query->where('destination_location_id', $request->get('destination_location_id'));
        }

        $transfers = $query->paginate(20);

        return response()->json([
            'transfers' => $transfers->items(),
            'pagination' => [
                'current_page' => $transfers->currentPage(),
                'last_page' => $transfers->lastPage(),
                'per_page' => $transfers->perPage(),
                'total' => $transfers->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Create a new stock transfer request.
     */
    public function store(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();

        $validated = $request->validate([
            'source_location_id' => ['required', 'string', 'exists:locations,id', 'different:destination_location_id'],
            'destination_location_id' => ['required', 'string', 'exists:locations,id'],
            'transfer_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'string', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
        ]);

        $transferNumber = 'TRF-' . date('Ymd') . '-' . rand(100, 999);

        $transfer = StockTransfer::create([
            'business_id' => $business->id,
            'source_location_id' => $validated['source_location_id'],
            'destination_location_id' => $validated['destination_location_id'],
            'transfer_number' => $transferNumber,
            'transfer_date' => $validated['transfer_date'],
            'status' => StockTransfer::STATUS_PENDING,
            'notes' => $validated['notes'] ?? null,
            'created_by' => $user->id,
            'sent_at' => now(),
        ]);

        foreach ($validated['items'] as $itemData) {
            $product = Product::find($itemData['product_id']);
            $cost = $product ? (float) $product->base_cost : 0.0;
            $qty = (float) $itemData['quantity'];

            StockTransferItem::create([
                'stock_transfer_id' => $transfer->id,
                'product_id' => $itemData['product_id'],
                'quantity' => $qty,
                'unit_cost' => $cost,
                'total_cost' => $qty * $cost,
            ]);
        }

        $transfer->load(['sourceLocation', 'destinationLocation', 'creator', 'items.product.outputUnit']);

        return response()->json([
            'message' => "Transfer Stok #{$transferNumber} berhasil dibuat.",
            'transfer' => $transfer,
        ], Response::HTTP_CREATED);
    }

    /**
     * Show Stock Transfer details.
     */
    public function show(Request $request, StockTransfer $stockTransfer): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($stockTransfer->business_id !== $business->id) {
            return response()->json(['message' => 'Transfer stok tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $stockTransfer->load(['sourceLocation', 'destinationLocation', 'creator', 'receiver', 'items.product.outputUnit']);

        return response()->json([
            'transfer' => $stockTransfer,
        ], Response::HTTP_OK);
    }

    /**
     * Receive and complete a Stock Transfer at destination location.
     */
    public function receive(Request $request, StockTransfer $stockTransfer): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($stockTransfer->business_id !== $business->id) {
            return response()->json(['message' => 'Transfer stok tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $user = $request->user();
        $this->stockService->completeStockTransfer($stockTransfer, $user);

        return response()->json([
            'message' => "Transfer Stok #{$stockTransfer->transfer_number} telah diterima & stok diperbarui.",
            'transfer' => $stockTransfer->fresh(['sourceLocation', 'destinationLocation', 'creator', 'receiver', 'items.product']),
        ], Response::HTTP_OK);
    }
}
