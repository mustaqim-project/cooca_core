<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Customer;

use App\Domain\Commerce\Storefront\CommerceOrderService;
use App\Domain\Commerce\Storefront\CommercePaymentProofService;
use App\Domain\Payment\TripayService;
use App\Domain\Shipping\BiteshipService;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\CommerceOrder;
use App\Models\CommercePaymentProof;
use App\Models\CommerceProductReview;
use App\Models\CustomerCart;
use App\Models\GlobalCustomer;
use App\Models\GlobalCustomerAddress;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class CustomerCheckoutApiController extends Controller
{
    public function __construct(
        private readonly CommerceOrderService $orderService = new CommerceOrderService(),
        private readonly CommercePaymentProofService $proofService = new CommercePaymentProofService(),
        private readonly BiteshipService $biteshipService = new BiteshipService(),
        private readonly TripayService $tripayService = new TripayService()
    ) {}

    private function customer(Request $request): GlobalCustomer
    {
        /** @var GlobalCustomer $user */
        $user = $request->user();
        return $user;
    }

    /**
     * Calculate courier rates using Biteship.
     * POST /api/v1/customer/checkout/rates
     */
    public function calculateRates(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'store_slug' => ['required', 'string'],
            'address_id' => ['nullable', 'uuid'],
            'destination_postal_code' => ['nullable', 'string', 'max:10'],
            'destination_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'destination_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'destination_area_id' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
        ]);

        $business = Business::where('slug', $validated['store_slug'])
            ->orWhere('id', $validated['store_slug'])
            ->firstOrFail();

        // Resolve destination
        $postalCode = $validated['destination_postal_code'] ?? null;
        $latitude = $validated['destination_latitude'] ?? null;
        $longitude = $validated['destination_longitude'] ?? null;
        $areaId = $validated['destination_area_id'] ?? null;

        if (! empty($validated['address_id'])) {
            $addr = GlobalCustomerAddress::find($validated['address_id']);
            if ($addr) {
                $postalCode = $postalCode ?: $addr->postal_code;
                $latitude = $latitude ?: $addr->latitude;
                $longitude = $longitude ?: $addr->longitude;
                $areaId = $areaId ?: $addr->biteship_area_id;
            }
        }

        // Calculate total weight grams
        $totalWeight = 0.0;
        foreach ($validated['items'] as $item) {
            $prod = Product::find($item['product_id']);
            $weight = (float) ($prod?->weight_grams ?? 200.0);
            $totalWeight += $weight * (float) $item['quantity'];
        }
        $totalWeight = max(100.0, $totalWeight);

        try {
            $rates = $this->biteshipService->getRates([
                'business_id' => $business->id,
                'destination_postal_code' => $postalCode,
                'destination_latitude' => $latitude,
                'destination_longitude' => $longitude,
                'destination_area_id' => $areaId,
                'total_weight_grams' => (int) round($totalWeight),
            ]);

            return response()->json([
                'success' => true,
                'total_weight_grams' => $totalWeight,
                'rates' => $rates,
            ], Response::HTTP_OK);
        } catch (Throwable $e) {
            return response()->json([
                'success' => true,
                'total_weight_grams' => $totalWeight,
                'rates' => [
                    [
                        'courier_code' => 'courier_manual',
                        'courier_name' => 'Kurir Standar Toko',
                        'courier_service_name' => 'Reguler',
                        'price' => 15000,
                        'duration' => '1-3 Hari',
                    ],
                ],
                'note' => 'Fallback rate digunakan karena tarif real-time kurir belum terhubung.',
            ], Response::HTTP_OK);
        }
    }

    /**
     * Submit checkout order.
     * POST /api/v1/customer/checkout/submit
     */
    public function submitCheckout(Request $request): JsonResponse
    {
        $customer = $this->customer($request);

        $validated = $request->validate([
            'store_slug' => ['required', 'string'],
            'address_id' => ['nullable', 'uuid'],
            'recipient_name' => ['nullable', 'string', 'max:150'],
            'recipient_phone' => ['nullable', 'string', 'max:30'],
            'shipping_address' => ['nullable', 'string', 'max:1000'],
            'destination_postal_code' => ['nullable', 'string', 'max:10'],
            'destination_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'destination_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'destination_area_id' => ['nullable', 'string', 'max:100'],
            'fulfillment_type' => ['required', 'string', 'in:pickup,merchant_delivery,delivery,courier_manual'],
            'courier_code' => ['nullable', 'string', 'max:50'],
            'courier_service' => ['nullable', 'string', 'max:50'],
            'courier_name' => ['nullable', 'string', 'max:100'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'payment_gateway' => ['nullable', 'string', 'in:tripay,manual'],
            'payment_channel' => ['nullable', 'string', 'max:50'],
            'payment_method_id' => ['nullable', 'uuid'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ]);

        $business = Business::where('slug', $validated['store_slug'])
            ->orWhere('id', $validated['store_slug'])
            ->where('is_active', true)
            ->firstOrFail();

        // Resolve address details
        $address = null;
        if (! empty($validated['address_id'])) {
            $address = $customer->addresses()->find($validated['address_id']);
        }

        $customerData = [
            'name' => $validated['recipient_name'] ?? $address?->recipient_name ?? $customer->name,
            'phone' => $validated['recipient_phone'] ?? $address?->recipient_phone ?? $customer->phone,
            'email' => $customer->email,
            'address' => $validated['shipping_address'] ?? $address?->full_address ?? $customer->shipping_address,
            'notes' => $validated['notes'] ?? null,
        ];

        $options = [
            'global_customer_id' => $customer->id,
            'shipping_cost' => (float) ($validated['shipping_cost'] ?? 0.0),
            'shipping_courier_code' => $validated['courier_code'] ?? null,
            'shipping_courier_service' => $validated['courier_service'] ?? null,
            'shipping_courier_name' => $validated['courier_name'] ?? null,
            'destination_postal_code' => $validated['destination_postal_code'] ?? $address?->postal_code ?? null,
            'destination_latitude' => $validated['destination_latitude'] ?? $address?->latitude ?? null,
            'destination_longitude' => $validated['destination_longitude'] ?? $address?->longitude ?? null,
            'destination_area_id' => $validated['destination_area_id'] ?? $address?->biteship_area_id ?? null,
            'payment_gateway' => $validated['payment_gateway'] ?? 'manual',
            'payment_channel' => $validated['payment_channel'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];

        try {
            $order = $this->orderService->createCheckoutOrder(
                business: $business,
                customerData: $customerData,
                itemsData: $validated['items'],
                fulfillmentType: $validated['fulfillment_type'],
                paymentMethodId: $validated['payment_method_id'] ?? null,
                options: $options
            );

            // Clear purchased items from this store's cart
            $cart = CustomerCart::where('global_customer_id', $customer->id)
                ->where('business_id', $business->id)
                ->first();
            if ($cart) {
                $orderedProductIds = collect($validated['items'])->pluck('product_id')->all();
                $cart->items()->whereIn('product_id', $orderedProductIds)->delete();
            }

            // Generate TriPay transaction if requested
            $paymentInfo = null;
            if ($options['payment_gateway'] === 'tripay' && ! empty($options['payment_channel'])) {
                try {
                    $paymentInfo = $this->tripayService->createOrderTransaction($order, $options['payment_channel']);
                } catch (Throwable $e) {
                    // Fallback to manual if gateway error
                    $paymentInfo = ['error' => $e->getMessage()];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat!',
                'data' => $order->fresh(['items.product', 'paymentMethod']),
                'payment' => $paymentInfo,
            ], Response::HTTP_CREATED);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * List customer's orders with tab status filter.
     * GET /api/v1/customer/orders
     */
    public function orders(Request $request): JsonResponse
    {
        $customer = $this->customer($request);
        $tab = $request->query('tab', 'all');
        $perPage = (int) $request->query('per_page', 15);

        $query = CommerceOrder::where('global_customer_id', $customer->id)
            ->with(['business:id,name,slug,logo_url', 'items.product:id,name,image_url', 'paymentMethod'])
            ->latest();

        if ($tab === 'unpaid') {
            $query->whereIn('status', [CommerceOrder::STATUS_PENDING_PAYMENT, CommerceOrder::STATUS_PAYMENT_REJECTED]);
        } elseif ($tab === 'processing') {
            $query->whereIn('status', [CommerceOrder::STATUS_PROOF_SUBMITTED, CommerceOrder::STATUS_PAID, CommerceOrder::STATUS_PROCESSING, CommerceOrder::STATUS_READY]);
        } elseif ($tab === 'shipped') {
            $query->whereIn('status', [CommerceOrder::STATUS_SHIPPED, CommerceOrder::STATUS_DELIVERED]);
        } elseif ($tab === 'completed') {
            $query->where('status', CommerceOrder::STATUS_COMPLETED);
        } elseif ($tab === 'cancelled') {
            $query->whereIn('status', [CommerceOrder::STATUS_CANCELLED, CommerceOrder::STATUS_EXPIRED]);
        }

        $paginated = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $paginated->items(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'total' => $paginated->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Get single order detail with Anti-IDOR security.
     * GET /api/v1/customer/orders/{id}
     */
    public function orderDetail(Request $request, string $id): JsonResponse
    {
        $customer = $this->customer($request);

        $order = CommerceOrder::where('global_customer_id', $customer->id)
            ->where(fn($q) => $q->where('id', $id)->orWhere('order_number', $id))
            ->with([
                'business:id,name,slug,logo_url,phone,address',
                'items.product',
                'paymentMethod',
                'latestProof',
            ])
            ->first();

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'data' => $order,
        ], Response::HTTP_OK);
    }

    /**
     * Get live tracking for order waybill.
     * GET /api/v1/customer/orders/{id}/tracking
     */
    public function tracking(Request $request, string $id): JsonResponse
    {
        $customer = $this->customer($request);
        $order = CommerceOrder::where('global_customer_id', $customer->id)
            ->where(fn($q) => $q->where('id', $id)->orWhere('order_number', $id))
            ->first();

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $timeline = [
            [
                'status' => 'Pesanan Dibuat',
                'time' => $order->created_at?->toIso8601String(),
                'is_done' => true,
            ],
            [
                'status' => 'Pembayaran Dikonfirmasi',
                'time' => $order->paid_at?->toIso8601String(),
                'is_done' => in_array($order->status, [CommerceOrder::STATUS_PAID, CommerceOrder::STATUS_PROCESSING, CommerceOrder::STATUS_READY, CommerceOrder::STATUS_SHIPPED, CommerceOrder::STATUS_COMPLETED], true),
            ],
            [
                'status' => 'Diproses Penjual',
                'time' => null,
                'is_done' => in_array($order->status, [CommerceOrder::STATUS_PROCESSING, CommerceOrder::STATUS_READY, CommerceOrder::STATUS_SHIPPED, CommerceOrder::STATUS_COMPLETED], true),
            ],
            [
                'status' => 'Dalam Pengiriman Kurir',
                'time' => null,
                'is_done' => in_array($order->status, [CommerceOrder::STATUS_SHIPPED, CommerceOrder::STATUS_COMPLETED], true),
            ],
            [
                'status' => 'Pesanan Selesai',
                'time' => null,
                'is_done' => $order->status === CommerceOrder::STATUS_COMPLETED,
            ],
        ];

        return response()->json([
            'success' => true,
            'waybill_id' => $order->shipping_waybill_id,
            'courier_name' => $order->shipping_courier_name,
            'tracking_url' => $order->shipping_tracking_url,
            'timeline' => $timeline,
        ], Response::HTTP_OK);
    }

    /**
     * Upload manual payment proof.
     * POST /api/v1/customer/orders/{id}/upload-proof
     */
    public function uploadProof(Request $request, string $id): JsonResponse
    {
        $customer = $this->customer($request);
        $order = CommerceOrder::where('global_customer_id', $customer->id)
            ->where(fn($q) => $q->where('id', $id)->orWhere('order_number', $id))
            ->firstOrFail();

        $validated = $request->validate([
            'proof_image' => ['required', 'image', 'max:5120'], // max 5MB
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $path = $request->file('proof_image')->store('payment_proofs', 'public');

        $proof = CommercePaymentProof::create([
            'commerce_order_id' => $order->id,
            'proof_image_path' => $path,
            'notes' => $validated['notes'] ?? null,
            'status' => CommercePaymentProof::STATUS_PENDING,
        ]);

        $order->update([
            'status' => CommerceOrder::STATUS_PROOF_SUBMITTED,
            'payment_status' => CommerceOrder::PAYMENT_VERIFYING,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bukti pembayaran berhasil diunggah. Penjual akan segera memverifikasi.',
            'data' => $proof,
        ], Response::HTTP_OK);
    }

    /**
     * Cancel unpaid order.
     * POST /api/v1/customer/orders/{id}/cancel
     */
    public function cancelOrder(Request $request, string $id): JsonResponse
    {
        $customer = $this->customer($request);
        $order = CommerceOrder::where('global_customer_id', $customer->id)
            ->where(fn($q) => $q->where('id', $id)->orWhere('order_number', $id))
            ->firstOrFail();

        if ($order->status !== CommerceOrder::STATUS_PENDING_PAYMENT && $order->status !== CommerceOrder::STATUS_PROOF_SUBMITTED) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan yang sudah diproses atau dikirim tidak dapat dibatalkan otomatis.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order->update([
            'status' => CommerceOrder::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'rejection_reason' => 'Dibatalkan oleh pembeli.',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibatalkan.',
        ], Response::HTTP_OK);
    }

    /**
     * Confirm order received and mark completed.
     * POST /api/v1/customer/orders/{id}/complete
     */
    public function completeOrder(Request $request, string $id): JsonResponse
    {
        $customer = $this->customer($request);
        $order = CommerceOrder::where('global_customer_id', $customer->id)
            ->where(fn($q) => $q->where('id', $id)->orWhere('order_number', $id))
            ->firstOrFail();

        $order->update([
            'status' => CommerceOrder::STATUS_COMPLETED,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih telah berbelanja! Pesanan telah selesai.',
            'data' => $order,
        ], Response::HTTP_OK);
    }

    /**
     * Store verified review for completed order.
     * POST /api/v1/customer/orders/{id}/review
     */
    public function storeReview(Request $request, string $id): JsonResponse
    {
        $customer = $this->customer($request);
        $order = CommerceOrder::where('global_customer_id', $customer->id)
            ->where(fn($q) => $q->where('id', $id)->orWhere('order_number', $id))
            ->with('items')
            ->firstOrFail();

        if ($order->status !== CommerceOrder::STATUS_COMPLETED) {
            return response()->json([
                'success' => false,
                'message' => 'Ulasan hanya dapat diberikan setelah pesanan berstatus Selesai.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validated = $request->validate([
            'reviews' => ['required', 'array', 'min:1'],
            'reviews.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'reviews.*.order_item_id' => ['nullable', 'uuid'],
            'reviews.*.rating' => ['required', 'integer', 'min:1', 'max:5'],
            'reviews.*.review_text' => ['nullable', 'string', 'max:1000'],
        ]);

        $orderProductIds = $order->items->pluck('product_id')->all();

        foreach ($validated['reviews'] as $revData) {
            if (! in_array($revData['product_id'], $orderProductIds, true)) {
                continue;
            }

            CommerceProductReview::updateOrCreate(
                [
                    'business_id' => $order->business_id,
                    'commerce_order_id' => $order->id,
                    'product_id' => $revData['product_id'],
                    'global_customer_id' => $customer->id,
                ],
                [
                    'commerce_order_item_id' => $revData['order_item_id'] ?? null,
                    'rating' => (int) $revData['rating'],
                    'review_text' => $revData['review_text'] ?? null,
                    'is_verified_purchase' => true,
                    'is_published' => true,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih atas ulasan Anda! Ulasan Anda telah terverifikasi.',
        ], Response::HTTP_OK);
    }
}
