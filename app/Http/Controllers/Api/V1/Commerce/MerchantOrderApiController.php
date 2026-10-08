<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Commerce;

use App\Domain\Commerce\Storefront\CommercePaymentProofService;
use App\Domain\Shipping\BiteshipService;
use App\Http\Controllers\Controller;
use App\Models\CommerceOrder;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class MerchantOrderApiController extends Controller
{
    public function __construct(
        private readonly CommercePaymentProofService $proofService = new CommercePaymentProofService(),
        private readonly BiteshipService $biteshipService = new BiteshipService()
    ) {}

    /**
     * List storefront online orders for the active business.
     * GET /api/v1/commerce/orders
     */
    public function index(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $status = $request->query('status');
        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 20);

        $query = CommerceOrder::where('business_id', $business->id)
            ->with(['items.product:id,name,image_url', 'paymentMethod:id,name,type', 'latestProof'])
            ->latest();

        if (! empty($status)) {
            if ($status === 'needs_verification') {
                $query->where('status', CommerceOrder::STATUS_PROOF_SUBMITTED);
            } elseif ($status === 'unpaid') {
                $query->whereIn('status', [CommerceOrder::STATUS_PENDING_PAYMENT, CommerceOrder::STATUS_PAYMENT_REJECTED]);
            } elseif ($status === 'processing') {
                $query->whereIn('status', [CommerceOrder::STATUS_PAID, CommerceOrder::STATUS_PROCESSING, CommerceOrder::STATUS_READY]);
            } elseif ($status === 'shipped') {
                $query->whereIn('status', [CommerceOrder::STATUS_SHIPPED, CommerceOrder::STATUS_DELIVERED]);
            } elseif ($status === 'completed') {
                $query->where('status', CommerceOrder::STATUS_COMPLETED);
            } elseif ($status === 'cancelled') {
                $query->whereIn('status', [CommerceOrder::STATUS_CANCELLED, CommerceOrder::STATUS_EXPIRED]);
            } else {
                $query->where('status', $status);
            }
        }

        if (! empty($search)) {
            $query->where(function ($q) use ($search): void {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate($perPage);

        $summary = [
            'total_orders' => CommerceOrder::where('business_id', $business->id)->count(),
            'needs_verification' => CommerceOrder::where('business_id', $business->id)->where('status', CommerceOrder::STATUS_PROOF_SUBMITTED)->count(),
            'processing' => CommerceOrder::where('business_id', $business->id)->whereIn('status', [CommerceOrder::STATUS_PAID, CommerceOrder::STATUS_PROCESSING])->count(),
            'ready_to_ship' => CommerceOrder::where('business_id', $business->id)->where('status', CommerceOrder::STATUS_READY)->count(),
        ];

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'data' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Show detail of a single storefront order.
     * GET /api/v1/commerce/orders/{order}
     */
    public function show(CommerceOrder $order): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($order->business_id !== $business->id) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan di bisnis ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        $order->load([
            'items.product',
            'paymentMethod',
            'paymentProofs.verifier',
            'customer',
            'globalCustomer',
            'location:id,name',
        ]);

        return response()->json([
            'success' => true,
            'data' => $order,
        ], Response::HTTP_OK);
    }

    /**
     * Verify payment proof for manual bank transfer.
     * POST /api/v1/commerce/orders/{order}/verify-payment
     */
    public function verifyPayment(Request $request, CommerceOrder $order): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($order->business_id !== $business->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], Response::HTTP_FORBIDDEN);
        }

        $proof = $order->latestProof;
        if (! $proof) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada bukti transfer yang dapat diverifikasi.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $user = $request->user();
            $this->proofService->verifyProof($proof, $user);

            $order->refresh()->load(['items.product', 'paymentMethod']);

            return response()->json([
                'success' => true,
                'message' => "Pembayaran pesanan #{$order->order_number} berhasil diverifikasi.",
                'data' => $order,
            ], Response::HTTP_OK);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Reject payment proof with reason.
     * POST /api/v1/commerce/orders/{order}/reject-payment
     */
    public function rejectPayment(Request $request, CommerceOrder $order): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($order->business_id !== $business->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $proof = $order->latestProof;
        if (! $proof) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada bukti transfer yang dapat ditolak.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $user = $request->user();
            $this->proofService->rejectProof($proof, $user, $validated['reason']);

            $order->refresh();

            return response()->json([
                'success' => true,
                'message' => "Bukti pembayaran pesanan #{$order->order_number} berhasil ditolak.",
                'data' => $order,
            ], Response::HTTP_OK);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Request courier pickup via Biteship API.
     * POST /api/v1/commerce/orders/{order}/request-pickup
     */
    public function requestPickup(Request $request, CommerceOrder $order): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($order->business_id !== $business->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], Response::HTTP_FORBIDDEN);
        }

        if (empty($order->shipping_courier_code)) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan ini tidak memiliki ekspedisi pengiriman kurir resmi.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $biteshipOrder = $this->biteshipService->createOrder($order);

            $order->update([
                'biteship_order_id' => $biteshipOrder['id'] ?? null,
                'shipping_waybill_id' => $biteshipOrder['waybill_id'] ?? $biteshipOrder['tracking_id'] ?? null,
                'shipping_tracking_url' => $biteshipOrder['tracking_url'] ?? null,
                'shipping_status' => 'allocated',
                'status' => CommerceOrder::STATUS_SHIPPED,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Penjemputan paket kurir Biteship ({$order->shipping_courier_name}) berhasil dipesan.",
                'data' => $order->refresh(),
            ], Response::HTTP_OK);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal request pickup Biteship: ' . $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update waybill manually for custom courier / local delivery.
     * POST /api/v1/commerce/orders/{order}/waybill
     */
    public function updateWaybill(Request $request, CommerceOrder $order): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($order->business_id !== $business->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'waybill_id' => ['required', 'string', 'max:100'],
            'courier_name' => ['nullable', 'string', 'max:100'],
        ]);

        $order->update([
            'shipping_waybill_id' => $validated['waybill_id'],
            'shipping_courier_name' => $validated['courier_name'] ?? $order->shipping_courier_name,
            'shipping_status' => 'shipping',
            'status' => CommerceOrder::STATUS_SHIPPED,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Nomor resi pengiriman berhasil diperbarui ke #{$validated['waybill_id']}.",
            'data' => $order->refresh(),
        ], Response::HTTP_OK);
    }

    /**
     * Return thermal shipping label data for mobile Bluetooth thermal printer.
     * GET /api/v1/commerce/orders/{order}/shipping-label-data
     */
    public function shippingLabelData(CommerceOrder $order): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($order->business_id !== $business->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], Response::HTTP_FORBIDDEN);
        }

        $order->load(['items.product', 'business', 'location']);

        $labelData = [
            'order_number' => $order->order_number,
            'waybill_id' => $order->shipping_waybill_id ?? '-',
            'courier' => [
                'code' => $order->shipping_courier_code,
                'name' => $order->shipping_courier_name ?? 'Kurir Toko',
                'service' => $order->shipping_courier_service,
            ],
            'sender' => [
                'store_name' => $business->name,
                'phone' => $business->phone ?? '-',
                'address' => $business->address ?? '-',
            ],
            'recipient' => [
                'name' => $order->customer_name,
                'phone' => $order->customer_phone,
                'address' => $order->shipping_address ?? '-',
                'postal_code' => $order->destination_postal_code ?? '-',
            ],
            'package' => [
                'total_weight_grams' => $order->items->sum(fn($i) => (float) ($i->product?->weight_grams ?? 200) * (float) $i->quantity),
                'total_items' => $order->items->sum('quantity'),
                'items_summary' => $order->items->map(fn($i) => [
                    'name' => $i->product_name ?? $i->product?->name,
                    'qty' => (float) $i->quantity,
                ]),
            ],
            'barcode_value' => $order->shipping_waybill_id ?: $order->order_number,
            'created_at' => $order->created_at?->toIso8601String(),
        ];

        return response()->json([
            'success' => true,
            'data' => $labelData,
        ], Response::HTTP_OK);
    }
}
