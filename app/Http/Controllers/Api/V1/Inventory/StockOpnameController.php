<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Inventory;

use App\Domain\Inventory\StockService;
use App\Http\Controllers\Controller;
use App\Models\InventoryStock;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class StockOpnameController extends Controller
{
    public function __construct(
        private readonly StockService $stockService = new StockService
    ) {}

    /**
     * List stock opname sessions.
     */
    public function index(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $query = StockOpname::where('business_id', $business->id)
            ->with(['location:id,name', 'conductor:id,name', 'reconciler:id,name'])
            ->withCount('items')
            ->latest('opname_date');

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->get('location_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $opnames = $query->paginate(20);

        return response()->json([
            'opnames' => $opnames->items(),
            'pagination' => [
                'current_page' => $opnames->currentPage(),
                'last_page' => $opnames->lastPage(),
                'per_page' => $opnames->perPage(),
                'total' => $opnames->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Create and record a new Stock Opname session.
     */
    public function store(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();

        $validated = $request->validate([
            'location_id' => ['required', 'string', 'exists:locations,id'],
            'opname_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.material_id' => ['nullable', 'string', 'exists:materials,id'],
            'items.*.product_id' => ['nullable', 'string', 'exists:products,id'],
            'items.*.physical_quantity' => ['required', 'numeric', 'min:0'],
        ]);

        $opnameNumber = 'OPN-' . date('Ymd') . '-' . rand(100, 999);

        $opname = StockOpname::create([
            'business_id' => $business->id,
            'location_id' => $validated['location_id'],
            'opname_number' => $opnameNumber,
            'opname_date' => $validated['opname_date'],
            'status' => StockOpname::STATUS_IN_PROGRESS,
            'notes' => $validated['notes'] ?? null,
            'conducted_by' => $user->id,
        ]);

        foreach ($validated['items'] as $itemData) {
            $materialId = $itemData['material_id'] ?? null;
            $productId = $itemData['product_id'] ?? null;

            if (!$materialId && !$productId) {
                continue;
            }

            $stockQuery = InventoryStock::where('business_id', $business->id)
                ->where('location_id', $validated['location_id']);

            if ($materialId) {
                $stockQuery->where('material_id', $materialId);
            } else {
                $stockQuery->where('product_id', $productId);
            }

            $stock = $stockQuery->first();

            $sysQty = $stock ? (float) $stock->quantity : 0.0;
            $physQty = (float) $itemData['physical_quantity'];
            $diff = $physQty - $sysQty;
            $unitCost = (float) ($stock?->last_cost ?? 0.0);

            if ($unitCost <= 0 && $materialId) {
                $mat = \App\Models\Material::withoutGlobalScopes()->where('business_id', $business->id)->with('latestPrice')->find($materialId);
                $unitCost = (float) ($mat?->latestPrice?->purchase_price ?? 0.0);
            }

            StockOpnameItem::create([
                'stock_opname_id' => $opname->id,
                'material_id' => $materialId,
                'product_id' => $productId,
                'system_quantity' => $sysQty,
                'physical_quantity' => $physQty,
                'difference_quantity' => $diff,
                'unit_cost' => $unitCost,
                'total_difference_cost' => $diff * $unitCost,
            ]);
        }

        $opname->load(['location', 'conductor', 'items.material.unit', 'items.product.outputUnit']);

        return response()->json([
            'message' => "Stock Opname #{$opnameNumber} berhasil disimpan.",
            'opname' => $opname,
        ], Response::HTTP_CREATED);
    }

    /**
     * Show Stock Opname details with item variances.
     */
    public function show(Request $request, StockOpname $stockOpname): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($stockOpname->business_id !== $business->id) {
            return response()->json(['message' => 'Stock opname tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $stockOpname->load(['location', 'conductor', 'reconciler', 'items.product.outputUnit']);

        return response()->json([
            'opname' => $stockOpname,
        ], Response::HTTP_OK);
    }

    /**
     * Reconcile Stock Opname (adjust physical vs system quantities).
     */
    public function reconcile(Request $request, StockOpname $stockOpname): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($stockOpname->business_id !== $business->id) {
            return response()->json(['message' => 'Stock opname tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $user = $request->user();
        $this->stockService->reconcileStockOpname($stockOpname, $user);

        return response()->json([
            'message' => "Stock Opname #{$stockOpname->opname_number} berhasil direkonsiliasi dan saldo stok diperbarui.",
            'opname' => $stockOpname->fresh(['location', 'conductor', 'reconciler', 'items.product']),
        ], Response::HTTP_OK);
    }
}
