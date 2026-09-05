<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Inventory;

use App\Domain\Inventory\StockService;
use App\Http\Controllers\Controller;
use App\Models\InventoryStock;
use App\Models\StockMovement;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class InventoryController extends Controller
{
    public function __construct(
        private readonly StockService $stockService = new StockService
    ) {}

    /**
     * List stock levels across locations/warehouses with low-stock filtering.
     */
    public function stocks(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $query = InventoryStock::where('business_id', $business->id)
            ->with(['product.outputUnit', 'product.category', 'location']);

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->get('location_id'));
        }

        if ($request->boolean('low_stock_only')) {
            $query->whereHas('product', function ($q) {
                $q->whereRaw('inventory_stocks.quantity <= products.min_stock');
            });
        }

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $stocks = $query->paginate(25);

        // Low stock count & total valuation
        $lowStockCount = InventoryStock::where('business_id', $business->id)
            ->whereHas('product', function ($q) {
                $q->whereRaw('inventory_stocks.quantity <= products.min_stock');
            })
            ->count();

        $totalValuation = (float) (InventoryStock::where('business_id', $business->id)
            ->selectRaw('SUM(quantity * last_cost) as total_val')
            ->value('total_val') ?? 0.0);

        return response()->json([
            'stocks' => $stocks->items(),
            'meta' => [
                'low_stock_count' => $lowStockCount,
                'total_valuation' => $totalValuation,
            ],
            'pagination' => [
                'current_page' => $stocks->currentPage(),
                'last_page' => $stocks->lastPage(),
                'per_page' => $stocks->perPage(),
                'total' => $stocks->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Quick stock adjustment.
     */
    public function adjust(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();

        $validated = $request->validate([
            'location_id' => ['required', 'string', 'exists:locations,id'],
            'product_id' => ['nullable', 'string', 'exists:products,id', 'required_without:material_id'],
            'material_id' => ['nullable', 'string', 'exists:materials,id', 'required_without:product_id'],
            'new_quantity' => ['nullable', 'numeric', 'min:0'],
            'type' => ['nullable', 'string', 'in:in,out,set'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $stock = $this->stockService->getOrCreateStock(
            $business->id,
            $validated['location_id'],
            $validated['product_id'] ?? null,
            false,
            $validated['material_id'] ?? null
        );

        $quantity = (float) ($validated['quantity'] ?? 0);
        $newQuantity = array_key_exists('new_quantity', $validated) && $validated['new_quantity'] !== null
            ? (float) $validated['new_quantity']
            : match ($validated['type'] ?? 'set') {
                'in' => (float) $stock->quantity + $quantity,
                'out' => max(0, (float) $stock->quantity - $quantity),
                default => $quantity,
            };
        $diff = $newQuantity - (float) $stock->quantity;
        $unitCost = (float) ($validated['unit_cost'] ?? $stock->last_cost ?? 0);

        if ($diff != 0) {
            $this->stockService->recordMovement(
                businessId: $business->id,
                locationId: $validated['location_id'],
                productId: $validated['product_id'] ?? null,
                movementType: StockMovement::TYPE_ADJUSTMENT,
                quantityChange: $diff,
                unitCost: $unitCost,
                notes: $validated['notes'] ?? $validated['reason'] ?? 'Penyesuaian stok via mobile API',
                userId: $user->id,
                materialId: $validated['material_id'] ?? null
            );
        }

        $stock->refresh()->load(['product.outputUnit', 'location']);

        return response()->json([
            'message' => 'Stok produk berhasil disesuaikan.',
            'stock' => $stock,
            'new_quantity' => (float) $stock->quantity,
        ], Response::HTTP_OK);
    }

    /**
     * Stock movements history / Stock Card (Kartu Stok).
     */
    public function movements(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $query = StockMovement::where('business_id', $business->id)
            ->with(['product.outputUnit', 'location:id,name', 'creator:id,name'])
            ->latest('created_at');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->get('product_id'));
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->get('location_id'));
        }

        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->get('movement_type'));
        }

        $movements = $query->paginate(30);

        return response()->json([
            'movements' => $movements->items(),
            'pagination' => [
                'current_page' => $movements->currentPage(),
                'last_page' => $movements->lastPage(),
                'per_page' => $movements->perPage(),
                'total' => $movements->total(),
            ],
        ], Response::HTTP_OK);
    }
}
