<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Pos;

use App\Domain\Commerce\SalesReturnService;
use App\Domain\Pos\PosOrderService;
use App\Domain\System\AuditLogService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pos\PosRefundOrderRequest;
use App\Http\Requests\Pos\PosVoidOrderRequest;
use App\Models\PosOrder;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Throwable;

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
            ->with(['customer', 'user', 'location', 'payments', 'items', 'technician'])
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
        $canBypassSupervisor = app(\App\Domain\System\OperatingModeService::class)->canBypassSupervisor($business, auth()->user());

        return view('app.pos.orders', compact('business', 'orders', 'canBypassSupervisor'));
    }

    /**
     * Show POS order details (JSON or View).
     */
    public function show(PosOrder $order): JsonResponse
    {
        $order->load(['items', 'payments', 'customer', 'user', 'location', 'technician']);
        return response()->json(['success' => true, 'order' => $order]);
    }

    /**
     * Process void transaction.
     */
    public function void(PosVoidOrderRequest $request, PosOrder $order): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        if ($order->business_id !== $business->id) {
            abort(403);
        }

        $user = auth()->user();

        if ($business->pos_require_pin_for_void) {
            $authCheck = $this->verifySupervisorAuthorization($request, $business, $user);
            if (! $authCheck['valid']) {
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $authCheck['message']], $authCheck['status']);
                }
                return back()->withErrors(['void' => $authCheck['message']]);
            }
        }

        $validated = $request->validated();

        try {
            $voided = $this->orderService->voidOrder($order, $user, $validated['reason']);
        } catch (Throwable $exception) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage(),
                ], 422);
            }
            return back()->withErrors(['void' => $exception->getMessage()]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('pos.order_voided_successfully', ['order_number' => $voided->order_number]),
                'order' => $voided,
            ]);
        }

        return redirect()->back()->with('success', __('pos.order_voided_successfully', ['order_number' => $voided->order_number]));
    }

    /**
     * Process refund transaction.
     */
    public function refund(PosRefundOrderRequest $request, PosOrder $order): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        if ($order->business_id !== $business->id) {
            abort(403);
        }

        $user = auth()->user();

        if ($business->pos_require_pin_for_refund) {
            $authCheck = $this->verifySupervisorAuthorization($request, $business, $user);
            if (! $authCheck['valid']) {
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $authCheck['message']], $authCheck['status']);
                }
                return back()->withErrors(['refund' => $authCheck['message']]);
            }
        }

        $validated = $request->validated();

        if (! empty($validated['items'])) {
            try {
                $return = $this->salesReturnService->createFromPosOrder($order, $validated['items'], ['reason' => $validated['reason'], 'created_by' => $user->id]);
                $return = $this->salesReturnService->approve($return, $user->id);
                $this->salesReturnService->complete($return, $user->id);
            } catch (Throwable $exception) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $exception->getMessage(),
                    ], 422);
                }
                return back()->withErrors(['refund' => $exception->getMessage()]);
            }
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('pos.partial_refund_successful', ['order_number' => $order->order_number]),
                    'order' => $order->fresh(['items', 'salesReturns']),
                    'return' => $return,
                ]);
            }
            return back()->with('success', __('pos.partial_refund_successful', ['order_number' => $order->order_number]));
        }

        $restoreStock = (bool) ($validated['restore_stock'] ?? true);
        try {
            $refunded = $this->orderService->refundOrder($order, $user, $validated['reason'], $restoreStock);
        } catch (Throwable $exception) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage(),
                ], 422);
            }
            return back()->withErrors(['refund' => $exception->getMessage()]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('pos.order_refunded_successfully', ['order_number' => $refunded->order_number]),
                'order' => $refunded,
            ]);
        }

        return redirect()->back()->with('success', __('pos.order_refunded_successfully', ['order_number' => $refunded->order_number]));
    }

    /**
     * Verify supervisor authorization for void or refund overrides with rate limiting and strict Bcrypt verification.
     *
     * @return array{valid: bool, message: string, status: int}
     */
    private function verifySupervisorAuthorization(Request $request, \App\Models\Business $business, ?\App\Models\User $user): array
    {
        if (app(\App\Domain\System\OperatingModeService::class)->canBypassSupervisor($business, $user)) {
            return ['valid' => true, 'message' => __('auth.supervisor_auth_allowed'), 'status' => 200];
        }

        if (! $business->hasSupervisorPin()) {
            return [
                'valid' => false,
                'message' => __('pos.supervisor_pin_invalid'),
                'status' => 403,
            ];
        }

        $throttleKey = 'pos_supervisor_pin:' . $business->id . ':' . ($user?->id ?? $request->ip());
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = max(1, (int) ceil($seconds / 60));
            return [
                'valid' => false,
                'message' => __('auth.pin_supervisor_locked', ['minutes' => $minutes]),
                'status' => 429,
            ];
        }

        $pin = (string) $request->input('pin', '');
        if ($pin === '') {
            return [
                'valid' => false,
                'message' => __('pos.supervisor_pin_required'),
                'status' => 422,
            ];
        }

        if ($business->verifySupervisorPin($pin)) {
            RateLimiter::clear($throttleKey);
            return ['valid' => true, 'message' => __('pos.supervisor_auth_verified'), 'status' => 200];
        }

        RateLimiter::hit($throttleKey, 600);
        $attempts = RateLimiter::attempts($throttleKey);
        $remaining = 5 - $attempts;

        if ($remaining <= 0) {
            AuditLogService::log(
                (string) $business->id,
                'POS_SUPERVISOR_PIN_LOCKED',
                $business,
                null,
                [
                    'reason' => 'Rate limit exceeded (5 failed attempts)',
                    'user_id' => $user?->id,
                    'ip' => $request->ip(),
                ]
            );
            $message = __('auth.pin_supervisor_locked', ['minutes' => 10]);
        } else {
            $message = __('pos.supervisor_pin_invalid_attempts', ['remaining' => $remaining]);
        }

        return [
            'valid' => false,
            'message' => $message,
            'status' => 403,
        ];
    }

    /**
     * Manually re-sync payment status from TriPay gateway failover.
     */
    public function syncGatewayStatus(Request $request, PosOrder $order): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        if ($order->business_id !== $business->id) {
            abort(403);
        }

        if ($order->isPaid()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Pesanan ini sudah berstatus lunas.']);
            }
            return back()->with('info', 'Pesanan ini sudah berstatus lunas.');
        }

        $reference = $order->gateway_reference;
        if (empty($reference)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Pesanan tidak memiliki referensi pembayaran TriPay.'], 422);
            }
            return back()->with('error', 'Pesanan tidak memiliki referensi pembayaran TriPay.');
        }

        try {
            $tripayService = new \App\Domain\Payment\TripayService();
            $detail = $tripayService->getTransactionDetail($reference);
            $status = strtoupper(trim((string) ($detail['status'] ?? '')));

            if ($status === 'PAID') {
                $totalFee = (float) ($detail['total_fee'] ?? 0.0);
                $paymentChannel = (string) ($detail['payment_method'] ?? ($order->payment_channel ?? 'QRIS'));
                $calculatedFee = strtoupper($paymentChannel) === 'QRIS'
                    ? $tripayService->calculateQrisFee((float) $order->total_amount)
                    : ($totalFee > 0 ? $totalFee : (float) ($order->gateway_fee ?? 0.0));

                \Illuminate\Support\Facades\DB::transaction(function () use ($order, $reference, $paymentChannel, $calculatedFee) {
                    $order->update([
                        'status' => PosOrder::STATUS_CONFIRMED,
                        'paid_amount' => $order->total_amount,
                        'change_amount' => 0.0,
                        'payment_gateway' => PosOrder::GATEWAY_TRIPAY,
                        'payment_channel' => $paymentChannel,
                        'gateway_reference' => $reference,
                        'gateway_fee' => $calculatedFee,
                    ]);

                    \App\Models\PosOrderPayment::firstOrCreate(
                        [
                            'pos_order_id' => $order->id,
                            'reference_number' => $reference,
                        ],
                        [
                            'payment_method' => 'qris',
                            'amount' => $order->total_amount,
                            'fee_amount' => $calculatedFee,
                            'net_amount' => max(0.0, $order->total_amount - $calculatedFee),
                            'status' => 'paid',
                            'notes' => "Lunas manual re-sync TriPay {$paymentChannel} (Meja " . ($order->table_or_reference ?? '-') . ')',
                        ]
                    );

                    // Commit recipe / BOM material stock for each item
                    $stockService = new \App\Domain\Inventory\StockService();
                    foreach ($order->items as $item) {
                        if ($item->product_id) {
                            $product = \App\Models\Product::find($item->product_id);
                            if ($product && ! $product->isService()) {
                                $stockService->deductForProductSale(
                                    businessId: $order->business_id,
                                    locationId: $order->location_id,
                                    product: $product,
                                    productQuantity: (float) $item->quantity,
                                    unitCost: (float) ($product->base_cost ?? 0.0),
                                    orderId: $order->id,
                                    orderNumber: $order->order_number,
                                    userId: auth()->id(),
                                    movementType: \App\Models\StockMovement::TYPE_POS_SALE,
                                    notes: "Penjualan QR Meja #{$order->order_number} (Re-sync)"
                                );
                            }
                        }
                    }
                });

                $msg = "Status pembayaran TriPay berhasil disinkronkan: LUNAS ({$paymentChannel}).";
                if ($request->wantsJson()) {
                    return response()->json(['success' => true, 'message' => $msg, 'order' => $order->fresh()]);
                }
                return back()->with('success', $msg);
            } elseif (in_array($status, ['EXPIRED', 'FAILED'], true)) {
                $order->update([
                    'status' => PosOrder::STATUS_VOIDED,
                    'void_reason' => 'Pembayaran QRIS Meja kadaluarsa/gagal via gateway (Re-sync).',
                ]);

                $msg = "Status pembayaran TriPay: {$status}. Pesanan telah dibatalkan.";
                if ($request->wantsJson()) {
                    return response()->json(['success' => true, 'message' => $msg, 'order' => $order->fresh()]);
                }
                return back()->with('warning', $msg);
            } else {
                $msg = "Status pembayaran di TriPay saat ini: {$status} (Menunggu Pembayaran).";
                if ($request->wantsJson()) {
                    return response()->json(['success' => true, 'message' => $msg, 'tripay_status' => $status]);
                }
                return back()->with('info', $msg);
            }
        } catch (\Throwable $e) {
            $err = 'Gagal menyinkronkan status TriPay: ' . $e->getMessage();
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $err], 500);
            }
            return back()->with('error', $err);
        }
    }
}
