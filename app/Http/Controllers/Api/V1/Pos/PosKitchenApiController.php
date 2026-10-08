<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Pos;

use App\Domain\Pos\PosOrderService;
use App\Http\Controllers\Controller;
use App\Models\BomHeader;
use App\Models\PosOrder;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class PosKitchenApiController extends Controller
{
    public function __construct(
        private readonly PosOrderService $orderService = new PosOrderService
    ) {}

    /**
     * Get active kitchen orders for KDS display.
     * GET /api/v1/pos/kitchen/orders
     */
    public function index(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $locationId = $request->query('location_id') ?? $request->header('X-Location-ID');

        $query = PosOrder::where('business_id', $business->id)
            ->whereIn('status', [
                PosOrder::STATUS_CONFIRMED,
                PosOrder::STATUS_PREPARING,
                PosOrder::STATUS_READY,
            ])
            ->when(! empty($locationId), fn($q) => $q->where('location_id', $locationId))
            ->with(['items.modifiers', 'posTable', 'location:id,name', 'cashier:id,name'])
            ->oldest('created_at');

        $activeOrders = $query->get();

        $incoming = $activeOrders->where('status', PosOrder::STATUS_CONFIRMED)->values();
        $cooking = $activeOrders->where('status', PosOrder::STATUS_PREPARING)->values();
        $ready = $activeOrders->where('status', PosOrder::STATUS_READY)->values();

        return response()->json([
            'success' => true,
            'data' => $activeOrders,
            'summary' => [
                'total_active' => $activeOrders->count(),
                'incoming_count' => $incoming->count(),
                'cooking_count' => $cooking->count(),
                'ready_count' => $ready->count(),
            ],
            'columns' => [
                'incoming' => $incoming,
                'cooking' => $cooking,
                'ready' => $ready,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Update order kitchen status.
     * POST /api/v1/pos/kitchen/orders/{order}/status
     */
    public function updateStatus(Request $request, PosOrder $order): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($order->business_id !== $business->id) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan di bisnis ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:preparing,ready,served,completed'],
        ]);

        try {
            $user = $request->user();
            $targetStatus = $validated['status'] === 'completed' ? 'served' : $validated['status'];
            $updated = $this->orderService->updateOrderStatus($order, $targetStatus, $user);

            return response()->json([
                'success' => true,
                'message' => "Status pesanan dapur berhasil diperbarui ke '{$validated['status']}'.",
                'data' => $updated->load(['items.modifiers', 'posTable', 'location:id,name']),
            ], Response::HTTP_OK);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Get aggregated daily prep sheet from active menu BOMs.
     * GET /api/v1/pos/kitchen/prep-sheet
     */
    public function prepSheet(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $date = $request->query('date', now()->toDateString());
        $locationId = $request->query('location_id') ?? $request->header('X-Location-ID');

        $activeOrders = PosOrder::where('business_id', $business->id)
            ->whereDate('created_at', $date)
            ->whereIn('status', [
                PosOrder::STATUS_CONFIRMED,
                PosOrder::STATUS_PREPARING,
                PosOrder::STATUS_READY,
                PosOrder::STATUS_COMPLETED,
            ])
            ->when(! empty($locationId), fn($q) => $q->where('location_id', $locationId))
            ->with(['items.product'])
            ->get();

        $menuPortions = [];
        $rawMaterialRequirements = [];

        foreach ($activeOrders as $order) {
            foreach ($order->items as $item) {
                $pId = (string) $item->product_id;
                $name = $item->product_name ?? $item->product?->name ?? 'Produk';
                $qty = (float) $item->quantity;

                if (! isset($menuPortions[$pId])) {
                    $menuPortions[$pId] = [
                        'product_id' => $pId,
                        'product_name' => $name,
                        'total_quantity' => 0.0,
                    ];
                }
                $menuPortions[$pId]['total_quantity'] += $qty;

                // Explode BOM if available
                $bomHeader = BomHeader::where('business_id', $business->id)
                    ->where('product_id', $pId)
                    ->where('is_active', true)
                    ->with('items.material.outputUnit')
                    ->first();

                if ($bomHeader) {
                    foreach ($bomHeader->items as $bomItem) {
                        $matId = (string) $bomItem->material_id;
                        $matName = $bomItem->material?->name ?? 'Bahan';
                        $unit = $bomItem->material?->outputUnit?->code ?? 'satuan';
                        $needed = (float) $bomItem->quantity * $qty;

                        if (! isset($rawMaterialRequirements[$matId])) {
                            $rawMaterialRequirements[$matId] = [
                                'material_id' => $matId,
                                'material_name' => $matName,
                                'unit' => $unit,
                                'total_needed' => 0.0,
                            ];
                        }
                        $rawMaterialRequirements[$matId]['total_needed'] += $needed;
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'date' => $date,
            'data' => [
                'menu_portions' => array_values($menuPortions),
                'materials_required' => array_values($rawMaterialRequirements),
            ],
        ], Response::HTTP_OK);
    }
}
