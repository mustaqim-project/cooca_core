<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Commerce;

use App\Domain\Commerce\GroupOrder\CommerceGroupOrderService;
use App\Domain\Payment\TripayService;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\CommerceGroupOrder;
use App\Models\CommerceGroupOrderItem;
use App\Models\CommerceOrder;
use App\Models\Product;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class CommerceGroupOrderWebController extends Controller
{
    public function __construct(
        private readonly CommerceGroupOrderService $groupService = new CommerceGroupOrderService(),
        private readonly TripayService $tripayService = new TripayService()
    ) {}

    private function findBusiness(string $slug): Business
    {
        $business = Business::where('slug', $slug)->where('is_active', true)->first();

        if (! $business) {
            $business = Business::where('is_active', true)->get()->first(
                fn (Business $b) => Str::slug($b->name) === $slug
            );
        }

        abort_unless($business, 404, 'Toko tidak ditemukan atau sedang tidak aktif.');

        return $business;
    }

    private function findGroup(Business $business, string $token): CommerceGroupOrder
    {
        $group = CommerceGroupOrder::where('business_id', $business->id)
            ->where('share_token', $token)
            ->firstOrFail();

        return $group;
    }

    /**
     * Start / Create a new Group Order session.
     */
    public function store(Request $request, string $slug): JsonResponse
    {
        $customer = auth('customer')->user();
        if (! $customer) {
            return response()->json([
                'success'   => false,
                'message'   => 'Silakan login akun pelanggan terlebih dahulu untuk membuat Pesan Bareng.',
                'login_url' => route('customer.login', ['redirect' => $request->fullUrl()]),
            ], 401);
        }

        $business = $this->findBusiness($slug);

        $validated = $request->validate([
            'title'               => ['nullable', 'string', 'max:100'],
            'scheduled_date'      => ['nullable', 'date'],
            'scheduled_time_slot' => ['nullable', 'string', 'max:50'],
            'delivery_address'    => ['nullable', 'string', 'max:500'],
            'delivery_notes'      => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $group = $this->groupService->createGroup($business, $customer, $validated);

            $targetUrl = url("/{$business->slug}?group_order={$group->share_token}");

            return response()->json([
                'success'      => true,
                'message'      => 'Sesi Pesan Bareng berhasil dibuat! Bagikan tautan ke rekan Anda.',
                'share_token'  => $group->share_token,
                'redirect_url' => $targetUrl,
                'group'        => [
                    'id'          => $group->id,
                    'title'       => $group->title,
                    'share_token' => $group->share_token,
                    'status'      => $group->status,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get live shared cart status & items grouped per colleague.
     */
    public function show(string $slug, string $token): JsonResponse
    {
        $business = $this->findBusiness($slug);
        $group = $this->findGroup($business, $token);
        $customer = auth('customer')->user();

        $group->loadMissing(['host', 'order']);

        $trackingUrl = null;
        if ($group->order) {
            $trackingUrl = route('public.storefront.order.track', [
                'slug'  => $business->slug,
                'token' => $group->order->tracking_token,
            ]);
        }

        return response()->json([
            'success' => true,
            'group'   => [
                'id'                 => $group->id,
                'title'              => $group->title,
                'share_token'        => $group->share_token,
                'status'             => $group->status,
                'is_open'            => $group->isOpen(),
                'is_locked'          => $group->isLocked(),
                'is_checked_out'     => $group->isCheckedOut(),
                'is_host'            => $group->isHost($customer),
                'host_name'          => $group->host?->name ?? 'Host',
                'scheduled_date'     => $group->scheduled_date?->toDateString(),
                'subtotal'           => (float) $group->subtotal,
                'total_quantity'     => (float) $group->total_quantity,
                'members_count'      => (int) $group->members_count,
                'order_tracking_url' => $trackingUrl,
            ],
            'current_user' => $customer ? [
                'id'    => $customer->id,
                'name'  => $customer->name,
                'phone' => $customer->phone,
            ] : null,
            'split_bill' => $group->getSplitBillSummary(),
        ]);
    }

    /**
     * Member adds an item to the shared group cart.
     */
    public function addItem(Request $request, string $slug, string $token): JsonResponse
    {
        $customer = auth('customer')->user();
        if (! $customer) {
            return response()->json([
                'success'   => false,
                'message'   => 'Silakan login terlebih dahulu untuk menambahkan menu ke Pesan Bareng.',
                'login_url' => route('customer.login', ['redirect' => $request->fullUrl()]),
            ], 401);
        }

        $business = $this->findBusiness($slug);
        $group = $this->findGroup($business, $token);

        $validated = $request->validate([
            'product_id' => ['required', 'string', 'exists:products,id'],
            'quantity'   => ['required', 'numeric', 'min:1'],
            'notes'      => ['nullable', 'string', 'max:255'],
        ]);

        $product = Product::where('id', $validated['product_id'])
            ->where('business_id', $business->id)
            ->firstOrFail();

        try {
            $this->groupService->addItem(
                group: $group,
                member: $customer,
                product: $product,
                quantity: (float) $validated['quantity'],
                notes: $validated['notes'] ?? null
            );

            return $this->show($slug, $token);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update quantity or notes of a shared cart item.
     */
    public function updateItem(Request $request, string $slug, string $token, string $itemId): JsonResponse
    {
        $customer = auth('customer')->user();
        if (! $customer) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu.',
            ], 401);
        }

        $business = $this->findBusiness($slug);
        $group = $this->findGroup($business, $token);

        $item = CommerceGroupOrderItem::where('id', $itemId)
            ->where('group_order_id', $group->id)
            ->firstOrFail();

        $validated = $request->validate([
            'quantity' => ['required', 'numeric'],
            'notes'    => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->groupService->updateItem(
                item: $item,
                user: $customer,
                quantity: (float) $validated['quantity'],
                notes: $validated['notes'] ?? null
            );

            return $this->show($slug, $token);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete an item from the shared cart.
     */
    public function removeItem(string $slug, string $token, string $itemId): JsonResponse
    {
        $customer = auth('customer')->user();
        if (! $customer) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu.',
            ], 401);
        }

        $business = $this->findBusiness($slug);
        $group = $this->findGroup($business, $token);

        $item = CommerceGroupOrderItem::where('id', $itemId)
            ->where('group_order_id', $group->id)
            ->firstOrFail();

        try {
            $this->groupService->removeItem($item, $customer);

            return $this->show($slug, $token);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Host locks the group order (preventing further edits by members).
     */
    public function lock(string $slug, string $token): JsonResponse
    {
        $customer = auth('customer')->user();
        if (! $customer) {
            return response()->json(['success' => false, 'message' => 'Silakan login terlebih dahulu.'], 401);
        }

        $business = $this->findBusiness($slug);
        $group = $this->findGroup($business, $token);

        try {
            $this->groupService->lockGroup($group, $customer);

            return $this->show($slug, $token);
        } catch (DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Host unlocks the group order.
     */
    public function unlock(string $slug, string $token): JsonResponse
    {
        $customer = auth('customer')->user();
        if (! $customer) {
            return response()->json(['success' => false, 'message' => 'Silakan login terlebih dahulu.'], 401);
        }

        $business = $this->findBusiness($slug);
        $group = $this->findGroup($business, $token);

        try {
            $this->groupService->unlockGroup($group, $customer);

            return $this->show($slug, $token);
        } catch (DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Host checks out the shared basket into a final CommerceOrder.
     */
    public function checkout(Request $request, string $slug, string $token): JsonResponse
    {
        $customer = auth('customer')->user();
        if (! $customer) {
            return response()->json(['success' => false, 'message' => 'Silakan login terlebih dahulu.'], 401);
        }

        $business = $this->findBusiness($slug);
        $group = $this->findGroup($business, $token);

        $validated = $request->validate([
            'payment_gateway'   => ['nullable', 'string', 'in:manual,tripay'],
            'payment_channel'   => ['nullable', 'string', 'max:64'],
            'payment_method_id' => ['nullable', 'string', 'exists:commerce_payment_methods,id'],
            'delivery_address'  => ['nullable', 'string', 'max:500'],
            'delivery_notes'    => ['nullable', 'string', 'max:500'],
            'fulfillment_type'  => ['nullable', 'string', 'in:pickup,merchant_delivery,courier_manual'],
            'shipping_fee'      => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $paymentGateway = $validated['payment_gateway'] ?? 'manual';
            $paymentChannel = $validated['payment_channel'] ?? 'QRIS';

            $order = $this->groupService->checkoutGroup($group, $customer, $validated);

            $paymentData = null;
            if ($paymentGateway === 'tripay') {
                $tripayRes = $this->tripayService->createTransaction($order, $paymentChannel);
                if ($tripayRes['success'] ?? false) {
                    $order->update([
                        'payment_gateway'   => CommerceOrder::GATEWAY_TRIPAY,
                        'payment_channel'   => $tripayRes['payment_method'] ?? $paymentChannel,
                        'gateway_reference' => $tripayRes['reference'] ?? null,
                        'gateway_pay_code'  => $tripayRes['pay_code'] ?? null,
                        'gateway_pay_url'   => $tripayRes['checkout_url'] ?? null,
                        'gateway_qr_url'    => $tripayRes['qr_url'] ?? null,
                        'gateway_qr_string' => $tripayRes['qr_string'] ?? null,
                        'gateway_fee'       => (float) ($tripayRes['fee'] ?? 0.0),
                        'gateway_expired_at'=> isset($tripayRes['expired_time']) ? Carbon::createFromTimestamp($tripayRes['expired_time']) : null,
                        'gateway_payload'   => $tripayRes['raw_response'] ?? null,
                    ]);

                    $paymentData = [
                        'channel'      => $tripayRes['payment_method'] ?? $paymentChannel,
                        'qr_url'       => $tripayRes['qr_url'] ?? null,
                        'pay_code'     => $tripayRes['pay_code'] ?? null,
                        'checkout_url' => $tripayRes['checkout_url'] ?? null,
                        'fee'          => (float) ($tripayRes['fee'] ?? 0.0),
                    ];
                } else {
                    Log::warning("[CommerceGroupOrderWebController] TriPay create error for #{$order->order_number}: " . ($tripayRes['message'] ?? 'Unknown'));
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

            $trackingUrl = route('public.storefront.order.track', [
                'slug'  => $business->slug,
                'token' => $order->tracking_token,
            ]);

            return response()->json([
                'success'      => true,
                'message'      => 'Pesanan bersama berhasil dibuat! Silakan lanjutkan pembayaran.',
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'tracking_url' => $trackingUrl,
                'payment'      => $paymentData,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
