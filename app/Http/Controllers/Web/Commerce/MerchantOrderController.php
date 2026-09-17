<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Commerce;

use App\Domain\Commerce\Storefront\CommercePaymentProofService;
use App\Domain\Inventory\StockService;
use App\Http\Controllers\Controller;
use App\Models\CommerceOrder;
use App\Models\CommercePaymentProof;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

final class MerchantOrderController extends Controller
{
    public function __construct(
        private readonly CommercePaymentProofService $proofService = new CommercePaymentProofService(),
        private readonly StockService $stockService = new StockService()
    ) {}

    /**
     * Display list of storefront online orders.
     */
    public function index(Request $request): View
    {
        $business = Context::business();
        abort_unless($business, 404);
        abort_unless(Context::hasPermission('storefront.orders.view'), 403);

        $statusTab = $request->query('tab', 'all');
        $typeFilter = $request->query('type', 'all');

        $query = CommerceOrder::where('business_id', $business->id)
            ->with(['items', 'paymentMethod', 'latestProof'])
            ->latest();

        if ($typeFilter !== 'all') {
            if ($typeFilter === 'customer_po') {
                $query->whereIn('order_type', [CommerceOrder::TYPE_CUSTOMER_PO, CommerceOrder::TYPE_PO_BATCH]);
            } else {
                $query->where('order_type', $typeFilter);
            }
        }

        if ($statusTab === 'needs_verification') {
            $query->where('status', CommerceOrder::STATUS_PROOF_SUBMITTED);
        } elseif ($statusTab === 'unpaid') {
            $query->whereIn('status', [CommerceOrder::STATUS_PENDING_PAYMENT, CommerceOrder::STATUS_PAYMENT_REJECTED]);
        } elseif ($statusTab === 'processing') {
            $query->whereIn('status', [CommerceOrder::STATUS_PAID, CommerceOrder::STATUS_PROCESSING, CommerceOrder::STATUS_READY]);
        } elseif ($statusTab === 'completed') {
            $query->where('status', CommerceOrder::STATUS_COMPLETED);
        } elseif ($statusTab === 'cancelled') {
            $query->whereIn('status', [CommerceOrder::STATUS_CANCELLED, CommerceOrder::STATUS_EXPIRED]);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $orders = $query->with(['groupOrder', 'items'])->latest()->paginate(20)->withQueryString();

        $needsVerificationCount = CommerceOrder::where('business_id', $business->id)
            ->where('status', CommerceOrder::STATUS_PROOF_SUBMITTED)
            ->count();

        return view('app.storefront.orders.index', compact('business', 'orders', 'statusTab', 'typeFilter', 'needsVerificationCount'));
    }

    /**
     * Display detail of a specific storefront order.
     */
    public function show(CommerceOrder $order): View
    {
        $business = Context::business();
        abort_unless($business && $order->business_id === $business->id, 403);
        abort_unless(Context::hasPermission('storefront.orders.view'), 403);

        $order->load(['items.product', 'paymentMethod', 'paymentProofs.verifier', 'customer', 'batches', 'groupOrder.items.member', 'groupOrder.host']);

        return view('app.storefront.orders.show', compact('business', 'order'));
    }

    /**
     * Verify payment proof and commit stock.
     */
    public function verifyPayment(CommerceOrder $order): RedirectResponse
    {
        $business = Context::business();
        abort_unless($business && $order->business_id === $business->id, 403);
        abort_unless(Context::hasPermission('storefront.orders.process'), 403);

        $proof = $order->latestProof;
        if (! $proof) {
            return back()->with('error', 'Tidak ada bukti transfer yang dapat diverifikasi.');
        }

        try {
            $this->proofService->verifyProof($proof, Auth::user());

            return back()->with('success', "Pembayaran pesanan #{$order->order_number} berhasil diverifikasi! Stok telah dikurangkan dan pelanggan telah dinotifikasi.");
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject payment proof with reason.
     */
    public function rejectPayment(Request $request, CommerceOrder $order): RedirectResponse
    {
        $business = Context::business();
        abort_unless($business && $order->business_id === $business->id, 403);
        abort_unless(Context::hasPermission('storefront.orders.process'), 403);

        $proof = $order->latestProof;
        if (! $proof) {
            return back()->with('error', 'Tidak ada bukti transfer yang dapat ditolak.');
        }

        $request->validate([
            'rejection_reason' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        try {
            $this->proofService->rejectProof($proof, Auth::user(), $request->input('rejection_reason'));

            return back()->with('success', "Bukti pembayaran pesanan #{$order->order_number} telah ditolak. Pelanggan telah diminta mengunggah ulang.");
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update order operational fulfillment status.
     */
    public function updateStatus(Request $request, CommerceOrder $order): RedirectResponse
    {
        $business = Context::business();
        abort_unless($business && $order->business_id === $business->id, 403);
        abort_unless(Context::hasPermission('storefront.orders.process'), 403);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:processing,ready,fulfilled,completed,cancelled'],
            'cancel_reason' => ['nullable', 'required_if:status,cancelled', 'string', 'max:255'],
        ]);

        $newStatus = $validated['status'];

        if ($newStatus === CommerceOrder::STATUS_CANCELLED) {
            // Release reserved stock if unpaid
            if (! $order->isPaid()) {
                foreach ($order->items as $item) {
                    if ($item->product && $item->product->isGoods()) {
                        $this->stockService->releaseProductReservedStock(
                            businessId: $order->business_id,
                            locationId: $order->location_id,
                            product: $item->product,
                            productQuantity: (float) $item->quantity
                        );
                    }
                }
            }

            $order->update([
                'status' => CommerceOrder::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'rejection_reason' => $validated['cancel_reason'] ?? 'Dibatalkan oleh toko.',
            ]);

            return back()->with('success', "Pesanan #{$order->order_number} berhasil dibatalkan dan stok reservasi telah dilepaskan.");
        }

        $order->update(['status' => $newStatus]);

        return back()->with('success', "Status pesanan #{$order->order_number} berhasil diperbarui menjadi {$newStatus}.");
    }

    /**
     * Submit finalized quotation for customer Request Order.
     */
    public function quoteRequestOrder(Request $request, CommerceOrder $order): RedirectResponse
    {
        $business = Context::business();
        abort_unless($business && $order->business_id === $business->id, 403);
        abort_unless(Context::hasPermission('storefront.orders.process'), 403);

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'uuid'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'payment_method_id' => ['nullable', 'uuid', 'exists:commerce_payment_methods,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $orderService = new \App\Domain\Commerce\Storefront\CommerceOrderService();
            $orderService->quoteRequestOrder(
                order: $order,
                itemsQuotation: $validated['items'],
                shippingCost: (float) ($validated['shipping_cost'] ?? 0.0),
                paymentMethodId: $validated['payment_method_id'] ?? null,
                notes: $validated['notes'] ?? null
            );

            return back()->with('success', "Penawaran harga untuk pesanan #{$order->order_number} berhasil dikirim ke pelanggan.");
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update fulfillment status of a specific delivery batch.
     */
    public function updateBatchStatus(Request $request, CommerceOrder $order, \App\Models\CommerceOrderBatch $batch): RedirectResponse
    {
        $business = Context::business();
        abort_unless($business && $order->business_id === $business->id && $batch->commerce_order_id === $order->id, 403);
        abort_unless(Context::hasPermission('storefront.orders.process'), 403);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:scheduled,in_preparation,shipped,delivered,cancelled'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $batchService = app(\App\Domain\Commerce\Storefront\CustomerPoBatchService::class);
            $batchService->updateBatchStatus(
                batch: $batch,
                status: $validated['status'],
                trackingNumber: $validated['tracking_number'] ?? null,
                notes: $validated['notes'] ?? null
            );

            return back()->with('success', "Status pengiriman batch #{$batch->batch_number} ({$batch->batch_code}) berhasil diperbarui.");
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Securely stream private payment proof image or PDF file to authorized merchant.
     */
    public function streamProof(CommercePaymentProof $proof)
    {
        $business = Context::business();
        abort_unless($business && $proof->business_id === $business->id, 403);
        abort_unless(Context::hasPermission('storefront.orders.view'), 403);

        if (! Storage::disk('local')->exists($proof->file_path)) {
            abort(404, 'Berkas bukti transfer tidak ditemukan di penyimpanan server.');
        }

        return Storage::disk('local')->response($proof->file_path);
    }

    /**
     * Manually re-sync payment status from TriPay gateway failover.
     */
    public function syncGatewayStatus(CommerceOrder $order): RedirectResponse
    {
        $business = Context::business();
        abort_unless($business && $order->business_id === $business->id, 403);
        abort_unless(Context::hasPermission('storefront.orders.process'), 403);

        if ($order->isPaid()) {
            return back()->with('info', 'Pesanan ini sudah berstatus lunas.');
        }

        $reference = $order->gateway_reference;
        if (empty($reference)) {
            return back()->with('error', 'Pesanan ini tidak memiliki referensi pembayaran gateway TriPay.');
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
                        'status' => CommerceOrder::STATUS_PAID,
                        'payment_status' => CommerceOrder::PAYMENT_PAID,
                        'payment_gateway' => CommerceOrder::GATEWAY_TRIPAY,
                        'payment_channel' => $paymentChannel,
                        'gateway_reference' => $reference,
                        'gateway_fee' => $calculatedFee,
                        'paid_at' => \Carbon\Carbon::now(),
                        'rejection_reason' => null,
                    ]);

                    // Commit physical stock for goods
                    foreach ($order->items as $item) {
                        if ($item->product && $item->product->isGoods()) {
                            $this->stockService->commitProductReservedStock(
                                businessId: $order->business_id,
                                locationId: $order->location_id,
                                product: $item->product,
                                productQuantity: (float) $item->quantity,
                                unitCost: (float) $item->product->base_cost,
                                referenceId: $order->id,
                                referenceNumber: $order->order_number,
                                userId: Auth::id()
                            );
                        }
                    }
                });

                return back()->with('success', "Status pembayaran TriPay berhasil disinkronkan: LUNAS ({$paymentChannel}). Stok produk telah dialokasikan.");
            } elseif (in_array($status, ['EXPIRED', 'FAILED'], true)) {
                $order->update([
                    'status' => $status === 'EXPIRED' ? CommerceOrder::STATUS_EXPIRED : CommerceOrder::STATUS_CANCELLED,
                    'payment_status' => CommerceOrder::PAYMENT_FAILED,
                    'cancelled_at' => \Carbon\Carbon::now(),
                ]);

                return back()->with('warning', "Status pembayaran TriPay berhasil disinkronkan: {$status}.");
            } else {
                return back()->with('info', "Status pembayaran di TriPay saat ini: {$status} (Menunggu Pembayaran).");
            }
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal menyinkronkan status TriPay: ' . $e->getMessage());
        }
    }
}
