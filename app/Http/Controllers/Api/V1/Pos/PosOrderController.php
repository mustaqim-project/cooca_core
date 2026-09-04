<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Pos;

use App\Domain\Commerce\SalesReturnService;
use App\Domain\Pos\PosOrderService;
use App\Http\Controllers\Controller;
use App\Models\PosOrder;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PosOrderController extends Controller
{
    public function __construct(
        private readonly PosOrderService $orderService = new PosOrderService,
        private readonly SalesReturnService $salesReturnService = new SalesReturnService,
    ) {}

    /**
     * List POS orders with filters and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $query = PosOrder::where('business_id', $business->id)
            ->with(['customer:id,name,phone', 'user:id,name', 'location:id,name', 'payments'])
            ->latest('order_date')
            ->latest('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('date')) {
            $query->whereDate('order_date', $request->get('date'));
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->get('location_id'));
        }

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name_guest', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->paginate(20);

        return response()->json([
            'orders' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Show POS order details with line items and payments.
     */
    public function show(Request $request, PosOrder $posOrder): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($posOrder->business_id !== $business->id) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $posOrder->load(['items.product', 'payments', 'customer', 'user', 'location', 'shift']);

        return response()->json([
            'order' => $posOrder,
        ], Response::HTTP_OK);
    }

    /**
     * Void a POS transaction.
     */
    public function void(Request $request, PosOrder $posOrder): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($posOrder->business_id !== $business->id) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $user = $request->user();

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $voided = $this->orderService->voidOrder($posOrder, $user, $validated['reason']);

        return response()->json([
            'message' => "Transaksi #{$voided->order_number} berhasil dibatalkan (void).",
            'order' => $voided->load(['items', 'payments', 'customer']),
        ], Response::HTTP_OK);
    }

    /**
     * Refund a POS transaction.
     */
    public function refund(Request $request, PosOrder $posOrder): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($posOrder->business_id !== $business->id) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $user = $request->user();

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
            'restore_stock' => ['nullable', 'boolean'],
            'items' => ['nullable', 'array', 'min:1'],
            'items.*.pos_order_item_id' => ['required_with:items', 'exists:pos_order_items,id'],
            'items.*.quantity' => ['required_with:items', 'numeric', 'gt:0'],
        ]);

        if (! empty($validated['items'])) {
            try {
                $return = $this->salesReturnService->createFromPosOrder($posOrder, $validated['items'], ['reason' => $validated['reason'], 'created_by' => $user->id]);
                $return = $this->salesReturnService->approve($return, $user->id);
                $this->salesReturnService->complete($return, $user->id);
            } catch (\InvalidArgumentException $exception) {
                return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            return response()->json(['message' => "Refund parsial #{$posOrder->order_number} berhasil.", 'order' => $posOrder->fresh(['items', 'payments', 'salesReturns']), 'return' => $return], Response::HTTP_OK);
        }

        $restoreStock = (bool) ($validated['restore_stock'] ?? true);
        try {
            $refunded = $this->orderService->refundOrder($posOrder, $user, $validated['reason'], $restoreStock);
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'message' => "Transaksi #{$refunded->order_number} berhasil direfund / diretur.",
            'order' => $refunded->load(['items', 'payments', 'customer']),
        ], Response::HTTP_OK);
    }
}
