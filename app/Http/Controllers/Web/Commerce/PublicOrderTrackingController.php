<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Commerce;

use App\Domain\Commerce\Storefront\CommerceOrderService;
use App\Domain\Commerce\Storefront\CommercePaymentProofService;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\CommerceOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Domain\Payment\TripayService;
use Carbon\Carbon;
use Illuminate\View\View;
use Throwable;

final class PublicOrderTrackingController extends Controller
{
    public function __construct(
        private readonly CommerceOrderService $orderService = new CommerceOrderService(),
        private readonly CommercePaymentProofService $proofService = new CommercePaymentProofService(),
        private readonly TripayService $tripayService = new TripayService()
    ) {}

    /**
     * Submit online checkout order from public storefront.
     */
    public function submitCheckout(Request $request, string $slug): JsonResponse
    {
        $business = Business::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'min:2', 'max:150'],
            'customer_phone' => ['required', 'string', 'min:8', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:150'],
            'fulfillment_type' => ['required', 'string', 'in:pickup,merchant_delivery'],
            'shipping_address' => ['nullable', 'string', 'max:500'],
            'shipping_rule_id' => ['nullable', 'uuid'],
            'distance_km' => ['nullable', 'numeric', 'min:0'],
            'scheduled_date' => ['nullable', 'date'],
            'scheduled_time_slot' => ['nullable', 'string', 'max:50'],
            'payment_gateway' => ['nullable', 'string', 'in:manual,tripay'],
            'payment_channel' => ['nullable', 'string', 'max:64'],
            'payment_method_id' => ['nullable', 'uuid', 'exists:commerce_payment_methods,id'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ], [
            'items.required' => 'Keranjang belanja tidak boleh kosong.',
            'items.min' => 'Keranjang belanja minimal harus memiliki 1 item.',
            'items.*.quantity.required' => 'Jumlah pesanan wajib diisi.',
            'items.*.quantity.gt' => 'Jumlah item pesanan harus lebih dari 0.',
        ]);

        try {
            $customerData = [
                'name' => $validated['customer_name'],
                'phone' => $validated['customer_phone'],
                'email' => $validated['customer_email'] ?? null,
                'address' => $validated['shipping_address'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ];

            $options = [
                'order_notes' => $validated['notes'] ?? null,
                'shipping_rule_id' => $validated['shipping_rule_id'] ?? null,
                'distance_km' => $validated['distance_km'] ?? null,
            ];

            $paymentGateway = $validated['payment_gateway'] ?? 'tripay';
            $paymentChannel = $validated['payment_channel'] ?? 'QRIS';
            $paymentMethodId = $paymentGateway === 'manual' ? ($validated['payment_method_id'] ?? null) : null;

            if (! empty($validated['scheduled_date'])) {
                $order = $this->orderService->createScheduledOrder(
                    business: $business,
                    customerData: $customerData,
                    itemsData: $validated['items'],
                    fulfillmentType: $validated['fulfillment_type'],
                    scheduledDate: (string) $validated['scheduled_date'],
                    scheduledTimeSlot: $validated['scheduled_time_slot'] ?? null,
                    paymentMethodId: $paymentMethodId,
                    options: $options
                );
            } else {
                $order = $this->orderService->createCheckoutOrder(
                    business: $business,
                    customerData: $customerData,
                    itemsData: $validated['items'],
                    fulfillmentType: $validated['fulfillment_type'],
                    paymentMethodId: $paymentMethodId,
                    options: $options
                );
            }

            // If TriPay gateway requested, initiate transaction with TriPay
            if ($paymentGateway === 'tripay') {
                $tripayRes = $this->tripayService->createTransaction($order, $paymentChannel);

                if ($tripayRes['success'] ?? false) {
                    $order->update([
                        'payment_gateway' => CommerceOrder::GATEWAY_TRIPAY,
                        'payment_channel' => $tripayRes['payment_method'] ?? $paymentChannel,
                        'gateway_reference' => $tripayRes['reference'] ?? null,
                        'gateway_pay_code' => $tripayRes['pay_code'] ?? null,
                        'gateway_pay_url' => $tripayRes['checkout_url'] ?? null,
                        'gateway_qr_url' => $tripayRes['qr_url'] ?? null,
                        'gateway_qr_string' => $tripayRes['qr_string'] ?? null,
                        'gateway_fee' => (float) ($tripayRes['fee'] ?? 0.0),
                        'gateway_expired_at' => isset($tripayRes['expired_time']) ? Carbon::createFromTimestamp($tripayRes['expired_time']) : null,
                        'gateway_payload' => $tripayRes['raw_response'] ?? null,
                    ]);
                } else {
                    \Illuminate\Support\Facades\Log::warning("[PublicOrderTrackingController] TriPay create error for #{$order->order_number}: " . ($tripayRes['message'] ?? 'Unknown'));
                    // Set as tripay channel pending retry or fallback
                    $order->update([
                        'payment_gateway' => CommerceOrder::GATEWAY_TRIPAY,
                        'payment_channel' => $paymentChannel,
                    ]);
                }
            } else {
                $order->update([
                    'payment_gateway' => CommerceOrder::GATEWAY_MANUAL,
                ]);
            }

            // Clear customer DB cart for this business if authenticated
            $authCustomer = auth('customer')->user();
            if ($authCustomer) {
                $cart = \App\Models\CustomerCart::where('global_customer_id', $authCustomer->id)
                    ->where('business_id', $business->id)
                    ->first();
                if ($cart) {
                    $cart->items()->delete();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat.',
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'tracking_token' => $order->tracking_token,
                    'total_amount' => (float) $order->total_amount,
                    'status' => $order->status,
                    'payment_gateway' => $order->payment_gateway,
                    'payment_channel' => $order->payment_channel,
                    'tracking_url' => url("/b/{$business->slug}/order/{$order->tracking_token}"),
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }


    /**
     * Submit a customer custom Request Order (RFQ / special catering / customized goods).
     */
    public function submitRequestOrder(Request $request, string $slug): JsonResponse
    {
        $business = Business::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'min:2', 'max:150'],
            'customer_phone' => ['required', 'string', 'min:8', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:150'],
            'fulfillment_type' => ['required', 'string', 'in:pickup,merchant_delivery'],
            'shipping_address' => ['nullable', 'string', 'max:500'],
            'scheduled_date' => ['nullable', 'date'],
            'scheduled_time_slot' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'uuid', 'exists:products,id'],
            'items.*.product_name' => ['required', 'string', 'max:200'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ], [
            'items.required' => 'Daftar permintaan barang tidak boleh kosong.',
            'items.min' => 'Daftar permintaan barang minimal harus memiliki 1 item.',
            'items.*.quantity.required' => 'Jumlah permintaan wajib diisi.',
            'items.*.quantity.gt' => 'Jumlah permintaan barang harus lebih dari 0.',
        ]);

        try {
            $order = $this->orderService->createRequestOrder(
                business: $business,
                customerData: [
                    'name' => $validated['customer_name'],
                    'phone' => $validated['customer_phone'],
                    'email' => $validated['customer_email'] ?? null,
                    'address' => $validated['shipping_address'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ],
                itemsData: $validated['items'],
                fulfillmentType: $validated['fulfillment_type'],
                options: [
                    'order_notes' => $validated['notes'] ?? null,
                    'scheduled_date' => $validated['scheduled_date'] ?? null,
                    'scheduled_time_slot' => $validated['scheduled_time_slot'] ?? null,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Permintaan pesanan khusus Anda berhasil dikirim. Toko akan segera meninjau dan mengirimkan penawaran harga.',
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'tracking_token' => $order->tracking_token,
                    'total_amount' => (float) $order->total_amount,
                    'status' => $order->status,
                    'tracking_url' => url("/b/{$business->slug}/order/{$order->tracking_token}"),
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Show live tracking status and payment instruction for an order.
     */
    public function show(string $slug, string $token): View
    {
        $business = Business::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $order = CommerceOrder::where('business_id', $business->id)
            ->where('tracking_token', $token)
            ->with(['items.product', 'paymentMethod', 'paymentProofs.verifier', 'groupOrder.items.member', 'groupOrder.host'])
            ->firstOrFail();

        return view('public.storefront.order_tracking', compact('business', 'order'));
    }

    /**
     * Poll order status and payment updates via AJAX.
     */
    public function checkStatus(string $slug, string $token): JsonResponse
    {
        $business = Business::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $order = CommerceOrder::where('business_id', $business->id)
            ->where('tracking_token', $token)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'is_paid' => $order->isPaid(),
            'paid_at' => $order->paid_at ? $order->paid_at->translatedFormat('d M Y, H:i') . ' WIB' : null,
            'payment_gateway' => $order->payment_gateway,
            'payment_channel' => $order->payment_channel,
            'gateway_pay_code' => $order->gateway_pay_code,
            'gateway_qr_url' => $order->gateway_qr_url,
        ]);
    }


    /**
     * Upload payment transfer receipt proof.
     */
    public function uploadProof(Request $request, string $slug, string $token): RedirectResponse
    {
        $business = Business::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $order = CommerceOrder::where('business_id', $business->id)
            ->where('tracking_token', $token)
            ->firstOrFail();

        $request->validate([
            'payment_proof' => ['required', 'file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:5120'],
            'sender_bank' => ['nullable', 'string', 'max:100'],
            'sender_account_name' => ['nullable', 'string', 'max:150'],
        ], [
            'payment_proof.required' => 'Foto atau berkas bukti transfer wajib diunggah.',
            'payment_proof.mimes' => 'Format berkas bukti transfer harus berupa JPG, PNG, WEBP, atau PDF.',
            'payment_proof.max' => 'Ukuran berkas bukti transfer maksimal 5 MB.',
        ]);

        try {
            $this->proofService->submitProof(
                order: $order,
                file: $request->file('payment_proof'),
                senderBank: $request->input('sender_bank'),
                senderAccountName: $request->input('sender_account_name')
            );

            return back()->with('success', 'Bukti transfer berhasil diunggah! Toko akan segera memverifikasi pesanan Anda.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Public calculation of available shipping rates for storefront checkout.
     */
    public function calculateShippingQuote(Request $request, string $slug): JsonResponse
    {
        $business = Business::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $subtotal = (float) $request->input('subtotal', 0.0);
        $distanceKm = $request->filled('distance_km') ? (float) $request->input('distance_km') : null;
        $preferredRuleId = $request->input('shipping_rule_id');

        $shippingService = new \App\Domain\Commerce\Storefront\CommerceShippingService();
        $result = $shippingService->calculateShipping(
            business: $business,
            subtotal: $subtotal,
            distanceKm: $distanceKm,
            preferredRuleId: $preferredRuleId
        );

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Submit a Customer Purchase Order (PO) with multi-drop delivery schedules.
     */
    public function submitCustomerPo(Request $request, string $slug): JsonResponse
    {
        $business = Business::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'min:2', 'max:150'],
            'customer_phone' => ['required', 'string', 'min:8', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:150'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'customer_po_number' => ['nullable', 'string', 'max:100'],
            'shipping_address' => ['nullable', 'string', 'max:500'],
            'payment_method_id' => ['nullable', 'uuid', 'exists:commerce_payment_methods,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'uuid', 'exists:products,id'],
            'items.*.product_name' => ['required', 'string', 'max:200'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
            'batches' => ['required', 'array', 'min:1'],
            'batches.*.scheduled_date' => ['required', 'date'],
            'batches.*.scheduled_time_slot' => ['nullable', 'string', 'max:50'],
            'batches.*.quantity' => ['required', 'numeric', 'gt:0'],
            'batches.*.shipping_address' => ['nullable', 'string', 'max:500'],
            'batches.*.notes' => ['nullable', 'string', 'max:500'],
        ], [
            'items.required' => 'Daftar item PO wajib diisi.',
            'items.*.quantity.gt' => 'Jumlah unit produk PO harus lebih dari 0.',
            'batches.required' => 'Jadwal batch pengiriman wajib ditentukan.',
            'batches.*.quantity.gt' => 'Jumlah pengiriman pada setiap batch harus lebih dari 0.',
        ]);

        try {
            $poService = app(\App\Domain\Commerce\Storefront\CustomerPoBatchService::class);
            $order = $poService->createCustomerPoWithBatches(
                business: $business,
                customerData: [
                    'name' => $validated['customer_name'],
                    'phone' => $validated['customer_phone'],
                    'email' => $validated['customer_email'] ?? null,
                    'company_name' => $validated['company_name'] ?? null,
                    'customer_po_number' => $validated['customer_po_number'] ?? null,
                    'address' => $validated['shipping_address'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ],
                itemsData: $validated['items'],
                batchesData: $validated['batches'],
                paymentMethodId: $validated['payment_method_id'] ?? null,
                options: [
                    'order_notes' => $validated['notes'] ?? null,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Purchase Order (PO) berhasil diajukan dengan jadwal pengiriman multi-drop.',
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_po_number' => $order->customer_po_number,
                    'company_name' => $order->company_name,
                    'order_type' => $order->order_type,
                    'tracking_token' => $order->tracking_token,
                    'total_amount' => (float) $order->total_amount,
                    'status' => $order->status,
                    'batches_count' => $order->batches->count(),
                    'tracking_url' => url("/b/{$business->slug}/order/{$order->tracking_token}"),
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
