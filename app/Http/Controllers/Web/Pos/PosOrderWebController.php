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
        $canBypassSupervisor = app(\App\Domain\System\OperatingModeService::class)->canBypassSupervisor($business, auth()->user());

        return view('app.pos.orders', compact('business', 'orders', 'canBypassSupervisor'));
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
        $business = Context::requireBusiness();
        if ($order->business_id !== $business->id) {
            abort(403);
        }

        $user = auth()->user();

        if ($business->pos_require_pin_for_void && ! $this->verifySupervisorAuthorization($request, $business, $user)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'PIN Supervisor salah atau otorisasi tidak valid.'], 403);
            }
            return back()->withErrors(['void' => 'PIN Supervisor salah atau otorisasi tidak valid.']);
        }

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
        $business = Context::requireBusiness();
        if ($order->business_id !== $business->id) {
            abort(403);
        }

        $user = auth()->user();

        if ($business->pos_require_pin_for_refund && ! $this->verifySupervisorAuthorization($request, $business, $user)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'PIN Supervisor salah atau otorisasi tidak valid.'], 403);
            }
            return back()->withErrors(['refund' => 'PIN Supervisor salah atau otorisasi tidak valid.']);
        }

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

    /**
     * Verify supervisor authorization for void or refund overrides.
     */
    private function verifySupervisorAuthorization(Request $request, \App\Models\Business $business, ?\App\Models\User $user): bool
    {
        if (app(\App\Domain\System\OperatingModeService::class)->canBypassSupervisor($business, $user)) {
            return true;
        }

        $pin = (string) $request->input('pin', '');
        if ($pin === '') {
            return false;
        }

        $validPin = (string) ($business->pos_supervisor_pin ?? '1234');

        return \Illuminate\Support\Facades\Hash::check($pin, $validPin) || hash_equals($validPin, $pin);
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
