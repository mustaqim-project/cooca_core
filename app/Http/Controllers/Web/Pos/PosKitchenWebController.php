<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Pos;

use App\Domain\Pos\PosOrderService;
use App\Http\Controllers\Controller;
use App\Models\BomHeader;
use App\Models\CommerceOrder;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\PosOrder;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

final class PosKitchenWebController extends Controller
{
    public function __construct(
        private readonly PosOrderService $orderService = new PosOrderService
    ) {}

    /**
     * Display the Kitchen & Bar Display Kanban board.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();

        $activeOrders = PosOrder::where('business_id', $business->id)
            ->whereIn('status', [
                PosOrder::STATUS_CONFIRMED,
                PosOrder::STATUS_PREPARING,
                PosOrder::STATUS_READY,
            ])
            ->with(['items.modifiers', 'posTable'])
            ->oldest('created_at')
            ->get();

        return view('app.pos.kitchen', compact('business', 'activeOrders'));
    }

    /**
     * Get active kitchen orders JSON for live polling/refresh.
     */
    public function getActiveOrders(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $activeOrders = PosOrder::where('business_id', $business->id)
            ->whereIn('status', [
                PosOrder::STATUS_CONFIRMED,
                PosOrder::STATUS_PREPARING,
                PosOrder::STATUS_READY,
            ])
            ->with(['items.modifiers', 'posTable'])
            ->oldest('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'orders' => $activeOrders,
        ]);
    }

    /**
     * Update order status from Kitchen/Bar display.
     */
    public function updateStatus(Request $request, PosOrder $order): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($order->business_id !== $business->id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:preparing,ready,served'],
        ]);

        try {
            $updated = $this->orderService->updateOrderStatus($order, $validated['status'], auth()->user());

            return response()->json([
                'success' => true,
                'message' => "Status pesanan diperbarui ke {$validated['status']}.",
                'order' => $updated->load(['items.modifiers', 'posTable']),
            ]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Daily Kitchen Batch Prep Sheet for Katering, Meal Prep & Central Kitchens.
     * Aggregates menu portions and calculates raw material requirements from BOM recipes.
     */
    public function prepSheet(Request $request): View
    {
        $business = Context::requireBusiness();
        $targetDate = $request->query('date', Carbon::today()->toDateString());
        $locationId = $request->query('location_id');

        $locations = Location::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('is_primary', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        // 1. Fetch Scheduled Commerce Orders for target date
        $commerceOrdersQuery = CommerceOrder::where('business_id', $business->id)
            ->whereDate('scheduled_date', $targetDate)
            ->whereNotIn('status', [CommerceOrder::STATUS_CANCELLED, CommerceOrder::STATUS_EXPIRED])
            ->with(['items.product.category', 'customer']);

        if ($locationId) {
            $commerceOrdersQuery->where('location_id', $locationId);
        }

        $scheduledOrders = $commerceOrdersQuery->get();

        // 2. Fetch Direct POS Orders on target date
        $posOrdersQuery = PosOrder::where('business_id', $business->id)
            ->whereDate('order_date', $targetDate)
            ->whereNotIn('status', [PosOrder::STATUS_VOIDED, PosOrder::STATUS_REJECTED])
            ->with(['items.product.category']);

        if ($locationId) {
            $posOrdersQuery->where('location_id', $locationId);
        }

        $posOrders = $posOrdersQuery->get();

        // 3. Aggregate Portions per Finished Product
        $menuPortions = [];

        foreach ($scheduledOrders as $ord) {
            foreach ($ord->items as $item) {
                $pid = $item->product_id ?? $item->product_name;
                if (! isset($menuPortions[$pid])) {
                    $menuPortions[$pid] = [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product_name,
                        'category' => $item->product?->category?->name ?? 'Menu Online',
                        'product' => $item->product,
                        'total_quantity' => 0.0,
                        'time_slots' => [],
                        'orders_count' => 0,
                    ];
                }
                $menuPortions[$pid]['total_quantity'] += (float) $item->quantity;
                $menuPortions[$pid]['orders_count']++;
                $slot = $ord->scheduled_time_slot ?: 'Reguler';
                $menuPortions[$pid]['time_slots'][$slot] = ($menuPortions[$pid]['time_slots'][$slot] ?? 0) + (float) $item->quantity;
            }
        }

        foreach ($posOrders as $ord) {
            foreach ($ord->items as $item) {
                $pid = $item->product_id ?? $item->product_name;
                if (! isset($menuPortions[$pid])) {
                    $menuPortions[$pid] = [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product_name,
                        'category' => $item->product?->category?->name ?? 'Menu POS',
                        'product' => $item->product,
                        'total_quantity' => 0.0,
                        'time_slots' => [],
                        'orders_count' => 0,
                    ];
                }
                $menuPortions[$pid]['total_quantity'] += (float) $item->quantity;
                $menuPortions[$pid]['orders_count']++;
                $slot = 'POS Langsung';
                $menuPortions[$pid]['time_slots'][$slot] = ($menuPortions[$pid]['time_slots'][$slot] ?? 0) + (float) $item->quantity;
            }
        }

        // 4. Calculate Raw Material Requirements from Active BOM / Recipe
        $materialRequirements = [];

        foreach ($menuPortions as $portion) {
            $product = $portion['product'];
            $orderedQty = $portion['total_quantity'];

            if (! $product) {
                continue;
            }

            // Find BOM recipes through CostModel
            $bomHeaders = BomHeader::whereHas('costModel', function ($q) use ($business, $product) {
                $q->where('business_id', $business->id)->where('product_id', $product->id);
            })->with(['items.material.unit'])->get();

            foreach ($bomHeaders as $header) {
                foreach ($header->items as $bomItem) {
                    $material = $bomItem->material;
                    if (! $material) {
                        continue;
                    }

                    $matId = $material->id;
                    $wasteMultiplier = 1.0 + ((float) ($bomItem->waste_percentage ?? 0.0) / 100.0);
                    $neededQty = ((float) $bomItem->quantity) * $orderedQty * $wasteMultiplier;

                    if (! isset($materialRequirements[$matId])) {
                        // Current stock on hand for material
                        $stockQuery = InventoryStock::where('business_id', $business->id)
                            ->where('material_id', $matId);
                        if ($locationId) {
                            $stockQuery->where('location_id', $locationId);
                        }
                        $stockOnHand = (float) $stockQuery->sum('quantity');

                        $materialRequirements[$matId] = [
                            'material_id' => $matId,
                            'material_name' => $material->name,
                            'material_code' => $material->code,
                            'unit' => $material->unit?->code ?? ($material->unit?->name ?? 'Satuan'),
                            'required_quantity' => 0.0,
                            'stock_on_hand' => $stockOnHand,
                            'used_in_menus' => [],
                        ];
                    }

                    $materialRequirements[$matId]['required_quantity'] += $neededQty;
                    $materialRequirements[$matId]['used_in_menus'][] = "{$portion['product_name']} ({$orderedQty} porsi)";
                }
            }
        }

        // Calculate shortages
        $shortageCount = 0;
        foreach ($materialRequirements as &$mat) {
            $diff = $mat['stock_on_hand'] - $mat['required_quantity'];
            $mat['shortage'] = $diff < 0 ? abs($diff) : 0.0;
            $mat['is_sufficient'] = $diff >= 0;
            if (! $mat['is_sufficient']) {
                $shortageCount++;
            }
        }
        unset($mat);

        $totalOrdersCount = $scheduledOrders->count() + $posOrders->count();
        $totalPortionsCount = array_sum(array_column($menuPortions, 'total_quantity'));

        return view('app.pos.prep_sheet', compact(
            'business',
            'targetDate',
            'locationId',
            'locations',
            'scheduledOrders',
            'menuPortions',
            'materialRequirements',
            'totalOrdersCount',
            'totalPortionsCount',
            'shortageCount'
        ));
    }
}
