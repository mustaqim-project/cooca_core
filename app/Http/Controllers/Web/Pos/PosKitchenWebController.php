<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Pos;

use App\Domain\Pos\PosOrderService;
use App\Http\Controllers\Controller;
use App\Models\PosOrder;
use App\Support\Context;
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
}
