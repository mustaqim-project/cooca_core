<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Pos;

use App\Domain\Commerce\SalesReturnService;
use App\Domain\Pos\PosOrderService;
use App\Http\Controllers\Controller;
use App\Models\PosOrder;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PosOrderWebController extends Controller
{
    public function __construct(
        private readonly PosOrderService $orderService = new PosOrderService,
        private readonly SalesReturnService $salesReturnService = new SalesReturnService
    ) {}

    /**
     * Display listing of POS transaction orders.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();

        $query = PosOrder::where('business_id', $business->id)
            ->with(['customer', 'user', 'location', 'payments', 'items'])
            ->latest('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('date')) {
            $query->whereDate('order_date', $request->get('date'));
        }

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name_guest', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('app.pos.orders', compact('business', 'orders'));
    }

    /**
     * Show POS order details (JSON or View).
     */
    public function show(PosOrder $order): JsonResponse
    {
        $order->load(['items', 'payments', 'customer', 'user', 'location']);
        return response()->json(['success' => true, 'order' => $order]);
    }

    /**
     * Process void transaction.
     */
    public function void(Request $request, PosOrder $order): RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $voided = $this->orderService->voidOrder($order, $user, $validated['reason']);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Transaksi #{$voided->order_number} berhasil dibatalkan (void).",
                'order' => $voided,
            ]);
        }

        return redirect()->back()->with('success', "Transaksi #{$voided->order_number} berhasil di-void!");
    }

    /**
     * Process refund transaction.
     */
    public function refund(Request $request, PosOrder $order): RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
            'restore_stock' => ['nullable', 'boolean'],
            'items' => ['nullable', 'array', 'min:1'],
            'items.*.pos_order_item_id' => ['required_with:items', 'exists:pos_order_items,id'],
            'items.*.quantity' => ['required_with:items', 'numeric', 'gt:0'],
        ]);

        if (! empty($validated['items'])) {
            try {
                $return = $this->salesReturnService->createFromPosOrder($order, $validated['items'], ['reason' => $validated['reason'], 'created_by' => $user->id]);
                $return = $this->salesReturnService->approve($return, $user->id);
                $this->salesReturnService->complete($return, $user->id);
            } catch (\InvalidArgumentException $exception) {
                return back()->withErrors(['refund' => $exception->getMessage()]);
            }
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => "Refund parsial #{$order->order_number} berhasil.", 'order' => $order->fresh(['items', 'salesReturns']), 'return' => $return]);
            }
            return back()->with('success', "Refund parsial #{$order->order_number} berhasil!");
        }

        $restoreStock = (bool) ($validated['restore_stock'] ?? true);
        try {
            $refunded = $this->orderService->refundOrder($order, $user, $validated['reason'], $restoreStock);
        } catch (\InvalidArgumentException $exception) {
            return back()->withErrors(['refund' => $exception->getMessage()]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Transaksi #{$refunded->order_number} berhasil direfund / diretur.",
                'order' => $refunded,
            ]);
        }

        return redirect()->back()->with('success', "Transaksi #{$refunded->order_number} berhasil direfund!");
    }
}
