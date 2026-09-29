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
            'customer_name'           => ['required', 'string', 'min:2', 'max:150'],
            'customer_phone'          => ['required', 'string', 'min:8', 'max:30'],
            'customer_email'          => ['nullable', 'email', 'max:150'],
            'fulfillment_type'        => ['required', 'string', 'in:pickup,merchant_delivery,delivery,courier_manual'],
            'pickup_location_id'      => ['nullable', 'uuid', 'exists:locations,id'],
            'pos_table_id'            => ['nullable', 'uuid'],
            'shipping_address'        => ['nullable', 'string', 'max:500'],
            'shipping_rule_id'        => ['nullable', 'string', 'max:100'],
            'shipping_fee'            => ['nullable', 'numeric', 'min:0'],
            'destination_postal_code' => ['nullable', 'string', 'max:10'],
            'postal_code'             => ['nullable', 'string', 'max:10'],
            'destination_latitude'    => ['nullable', 'numeric', 'between:-90,90'],
            'latitude'                => ['nullable', 'numeric', 'between:-90,90'],
            'destination_longitude'   => ['nullable', 'numeric', 'between:-180,180'],
            'longitude'               => ['nullable', 'numeric', 'between:-180,180'],
            'biteship_area_id'        => ['nullable', 'string', 'max:100'],
            'destination_area_id'     => ['nullable', 'string', 'max:100'],
            'courier_company'         => ['nullable', 'string', 'max:50'],
            'courier_type'            => ['nullable', 'string', 'max:50'],
            'courier_name'            => ['nullable', 'string', 'max:100'],
            'biteship_service_fee'    => ['nullable', 'numeric', 'min:0'],
            'distance_km'             => ['nullable', 'numeric', 'min:0'],
            'scheduled_date'          => ['nullable', 'date'],
            'scheduled_time_slot'     => ['nullable', 'string', 'max:50'],
            'payment_gateway'         => ['nullable', 'string', 'in:tripay,manual'],
            'payment_channel'         => ['nullable', 'string', 'max:64'],
            'payment_method_id'       => ['nullable', 'uuid', 'exists:commerce_payment_methods,id'],
            'save_to_address_book'    => ['nullable', 'boolean'],
            'address_label'           => ['nullable', 'string', 'max:50'],
            'notes'                   => ['nullable', 'string', 'max:500'],
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.product_id'      => ['required', 'uuid', 'exists:products,id'],
            'items.*.quantity'        => ['required', 'numeric', 'gt:0'],
            'items.*.notes'           => ['nullable', 'string', 'max:255'],
        ], [
            'items.required'          => __('storefront.messages.cart_empty'),
            'items.min'               => __('storefront.messages.cart_min_item'),
            'items.*.quantity.required' => __('storefront.messages.quantity_required'),
            'items.*.quantity.gt'     => __('storefront.messages.quantity_gt_zero'),
        ]);

        try {
            $customerData = [
                'name'    => $validated['customer_name'],
                'phone'   => $validated['customer_phone'],
                'email'   => $validated['customer_email'] ?? null,
                'address' => $validated['shipping_address'] ?? null,
                'notes'   => $validated['notes'] ?? null,
            ];

            $options = [
                'order_notes'              => $validated['notes'] ?? null,
                'shipping_rule_id'         => $validated['shipping_rule_id'] ?? null,
                'shipping_cost'            => isset($validated['shipping_fee']) ? (float) $validated['shipping_fee'] : null,
                'shipping_courier_code'    => $validated['courier_company'] ?? null,
                'shipping_courier_service' => $validated['courier_type'] ?? null,
                'shipping_courier_name'    => $validated['courier_name'] ?? null,
                'destination_postal_code'  => $validated['destination_postal_code'] ?? ($validated['postal_code'] ?? null),
                'destination_latitude'     => $validated['destination_latitude'] ?? ($validated['latitude'] ?? null),
                'destination_longitude'    => $validated['destination_longitude'] ?? ($validated['longitude'] ?? null),
                'destination_area_id'      => $validated['destination_area_id'] ?? ($validated['biteship_area_id'] ?? null),
                'distance_km'              => $validated['distance_km'] ?? null,
                'location_id'              => $validated['pickup_location_id'] ?? null,
                'pos_table_id'             => null,
                'biteship_service_fee'     => isset($validated['biteship_service_fee']) ? (float) $validated['biteship_service_fee'] : null,
            ];

            // Auto-save address to customer's address book if requested or if customer has no saved addresses
            /** @var \App\Models\GlobalCustomer|null $authCustomer */
            $authCustomer = auth('customer')->user();
            if ($authCustomer && ! empty($validated['shipping_address'])) {
                $shouldSave = filter_var($request->input('save_to_address_book', false), FILTER_VALIDATE_BOOLEAN)
                    || $authCustomer->addresses()->count() === 0;

                if ($shouldSave) {
                    $authCustomer->addresses()->firstOrCreate(
                        ['full_address' => $validated['shipping_address']],
                        [
                            'label'            => (string) ($request->input('address_label') ?: 'Alamat Pengiriman'),
                            'recipient_name'   => $validated['customer_name'],
                            'recipient_phone'  => $validated['customer_phone'],
                            'postal_code'      => $validated['destination_postal_code'] ?? ($validated['postal_code'] ?? null),
                            'biteship_area_id' => $validated['destination_area_id'] ?? ($validated['biteship_area_id'] ?? null),
                            'latitude'         => isset($validated['latitude']) ? (float) $validated['latitude'] : (isset($validated['destination_latitude']) ? (float) $validated['destination_latitude'] : null),
                            'longitude'        => isset($validated['longitude']) ? (float) $validated['longitude'] : (isset($validated['destination_longitude']) ? (float) $validated['destination_longitude'] : null),
                            'is_default'       => $authCustomer->addresses()->count() === 0,
                        ]
                    );
                }
            }

            $fulfillmentType = $validated['fulfillment_type'] === 'delivery' ? 'merchant_delivery' : $validated['fulfillment_type'];

            // Storefront orders are processed exclusively via QRIS Cooca Pay (Tripay)
            $paymentGateway = 'tripay';
            $paymentChannel = 'QRIS';
            $paymentMethodId = null;

            if (! empty($validated['scheduled_date'])) {
                $order = $this->orderService->createScheduledOrder(
                    business: $business,
                    customerData: $customerData,
                    itemsData: $validated['items'],
                    fulfillmentType: $fulfillmentType,
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
                    fulfillmentType: $fulfillmentType,
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
                    'tracking_url' => url("/{$business->slug}/order/{$order->tracking_token}"),
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
                    'tracking_url' => url("/{$business->slug}/order/{$order->tracking_token}"),
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
            'payment_proof.required' => __('storefront.tracking.choose_file'),
            'payment_proof.mimes' => __('storefront.tracking.upload_proof_desc'),
            'payment_proof.max' => __('storefront.tracking.upload_proof_desc'),
        ]);

        try {
            $this->proofService->submitProof(
                order: $order,
                file: $request->file('payment_proof'),
                senderBank: $request->input('sender_bank'),
                senderAccountName: $request->input('sender_account_name')
            );

            return back()->with('success', __('storefront.messages.proof_success'));
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
        $destinationPostalCode = $request->input('destination_postal_code', $request->input('postal_code'));
        $destinationAddress = $request->input('destination_address', $request->input('shipping_address'));
        $items = $request->input('items', []);

        $destinationCoordinates = null;
        $lat = $request->input('latitude', $request->input('customer_lat'));
        $lng = $request->input('longitude', $request->input('customer_lng'));
        if ($lat !== null && $lng !== null && is_numeric($lat) && is_numeric($lng)) {
            $destinationCoordinates = [
                'latitude' => (float) $lat,
                'longitude' => (float) $lng,
            ];
        }

        $shippingService = new \App\Domain\Commerce\Storefront\CommerceShippingService();
        $result = $shippingService->calculateShipping(
            business: $business,
            subtotal: $subtotal,
            distanceKm: $distanceKm,
            preferredRuleId: $preferredRuleId,
            destinationPostalCode: $destinationPostalCode ? (string) $destinationPostalCode : null,
            destinationCoordinates: $destinationCoordinates,
            items: is_array($items) ? $items : [],
            destinationAddress: $destinationAddress ? (string) $destinationAddress : null
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
                    'tracking_url' => url("/{$business->slug}/order/{$order->tracking_token}"),
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
