<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Pos;

use App\Domain\Crm\LoyaltyService;
use App\Domain\Pos\PosOrderService;
use App\Domain\Pos\PosShiftService;
use App\Domain\System\OperatingModeService;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Voucher;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class PosTerminalController extends Controller
{
    public function __construct(
        private readonly PosOrderService $orderService = new PosOrderService,
        private readonly PosShiftService $shiftService = new PosShiftService,
        private readonly LoyaltyService $loyaltyService = new LoyaltyService,
        private readonly OperatingModeService $operatingModeService = new OperatingModeService
    ) {}

    /**
     * Get complete bootstrap dataset for mobile POS terminal.
     */
    public function terminalData(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();

        // 1. Locations
        $locations = Location::where('business_id', $business->id)
            ->where('is_active', true)
            ->get();

        $selectedLocationId = $request->query('location_id')
            ?? $locations->where('is_primary', true)->first()?->id
            ?? $locations->first()?->id;

        // 2. Active Shift
        $activeShift = $this->shiftService->getActiveShift($business, $user, $selectedLocationId);

        // 3. Product Categories
        $categories = ProductCategory::where('business_id', $business->id)->get();

        // 4. Products with active location stocks
        $products = Product::where('business_id', $business->id)
            ->where('is_active', true)
            ->with(['category', 'outputUnit', 'stocks' => function ($q) use ($selectedLocationId) {
                if ($selectedLocationId) {
                    $q->where('location_id', $selectedLocationId);
                }
            }])
            ->get()
            ->map(function (Product $p) {
                $locStock = $p->stocks->first();
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'code' => $p->code,
                    'slug' => $p->slug,
                    'category_id' => $p->category_id,
                    'category_name' => $p->category?->name,
                    'unit_id' => $p->output_unit_id,
                    'unit_code' => $p->outputUnit?->code ?? 'satuan',
                    'selling_price' => (float) $p->selling_price,
                    'base_cost' => (float) $p->base_cost,
                    'stock' => $locStock ? (float) $locStock->quantity : 0.0,
                    'min_stock' => (float) $p->min_stock,
                    'image_url' => $p->image_url,
                ];
            });

        // 5. Customers
        $customers = Customer::where('business_id', $business->id)
            ->where('is_active', true)
            ->select('id', 'name', 'phone', 'membership_tier', 'points_balance', 'credit_limit', 'current_credit_balance')
            ->get();

        // 6. Held Orders
        $heldOrders = PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_DRAFT_HELD)
            ->with('items')
            ->latest('held_at')
            ->get();

        // 7. Active Vouchers
        $vouchers = Voucher::where('business_id', $business->id)
            ->where('is_active', true)
            ->get();

        // 8. Operating Mode Profile
        $operatingMode = $this->operatingModeService->getOperatingModeProfile($business);
        $canBypassSupervisor = $this->operatingModeService->canBypassSupervisor($business, $user);
        $hideCostFromCashier = $this->operatingModeService->shouldHideCostFromCashier($business, $user);

        return response()->json([
            'business' => [
                'id' => $business->id,
                'name' => $business->name,
                'slug' => $business->slug,
                'logo_url' => $business->logo_url,
                'currency' => $business->currency ?? 'IDR',
                'phone' => $business->phone,
                'address' => $business->address,
            ],
            'user' => [
                'id' => $user?->id,
                'name' => $user?->name,
                'email' => $user?->email,
            ],
            'selected_location_id' => $selectedLocationId,
            'locations' => $locations,
            'active_shift' => $activeShift ? [
                'id' => $activeShift->id,
                'opened_at' => $activeShift->opened_at,
                'opening_cash' => (float) $activeShift->opening_cash,
                'status' => $activeShift->status,
            ] : null,
            'categories' => $categories,
            'products' => $products,
            'customers' => $customers,
            'held_orders' => $heldOrders,
            'recent_held_orders' => $heldOrders,
            'vouchers' => $vouchers,
            'operating_mode' => $operatingMode,
            'can_bypass_supervisor' => $canBypassSupervisor,
            'hide_cost_from_cashier' => $hideCostFromCashier,
        ], Response::HTTP_OK);
    }

    /**
     * Realtime search / barcode scan for products.
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $query = (string) $request->get('q', '');
        $locationId = (string) $request->get('location_id', '');

        $products = Product::where('business_id', $business->id)
            ->where('is_active', true)
            ->when($query !== '', function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('name', 'like', "%{$query}%")
                        ->orWhere('code', 'like', "%{$query}%");
                });
            })
            ->with(['stocks' => function ($q) use ($locationId) {
                if ($locationId !== '') {
                    $q->where('location_id', $locationId);
                }
            }, 'outputUnit', 'category'])
            ->limit(50)
            ->get()
            ->map(function (Product $p) {
                $locStock = $p->stocks->first();
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'code' => $p->code,
                    'selling_price' => (float) $p->selling_price,
                    'base_cost' => (float) $p->base_cost,
                    'unit_code' => $p->outputUnit?->code ?? 'pcs',
                    'category_name' => $p->category?->name,
                    'stock' => $locStock ? (float) $locStock->quantity : 0.0,
                ];
            });

        return response()->json([
            'products' => $products,
        ], Response::HTTP_OK);
    }

    /**
     * Checkout POS order transaction.
     */
    public function checkout(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'string'],
            'items.*.product_name' => ['required', 'string'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
            'payments' => ['required', 'array', 'min:1'],
            'payments.*.payment_method' => ['required', 'string'],
            'payments.*.amount' => ['required', 'numeric', 'min:0'],
            'payments.*.reference_number' => ['nullable', 'string'],
            'customer_id' => ['nullable', 'string'],
            'customer_name_guest' => ['nullable', 'string', 'max:150'],
            'order_type' => ['nullable', 'string', 'in:dine_in,takeaway,delivery'],
            'table_or_reference' => ['nullable', 'string', 'max:100'],
            'discount_type' => ['nullable', 'string', 'in:fixed,percentage'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'voucher_code' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
            'points_to_redeem' => ['nullable', 'integer', 'min:0'],
            'location_id' => ['nullable', 'string'],
        ]);

        $activeShift = $this->shiftService->getActiveShift($business, $user, $validated['location_id'] ?? null);

        try {
            $order = $this->orderService->checkout(
                business: $business,
                cashier: $user,
                itemsData: $validated['items'],
                paymentsData: $validated['payments'],
                attributes: [
                    'customer_id' => $validated['customer_id'] ?? null,
                    'customer_name_guest' => $validated['customer_name_guest'] ?? null,
                    'order_type' => $validated['order_type'] ?? 'takeaway',
                    'table_or_reference' => $validated['table_or_reference'] ?? null,
                    'discount_type' => $validated['discount_type'] ?? 'fixed',
                    'discount_value' => (float) ($validated['discount_value'] ?? 0),
                    'voucher_code' => $validated['voucher_code'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'location_id' => $validated['location_id'] ?? null,
                ],
                shift: $activeShift
            );

            if (! empty($validated['points_to_redeem']) && $order->customer) {
                $this->loyaltyService->redeemPointsForOrder($order->customer, $order, (int) $validated['points_to_redeem']);
            }

            $whatsappUrl = $this->loyaltyService->generateWhatsAppReceiptUrl($order);

            return response()->json([
                'message' => 'Transaksi kasir berhasil diselesaikan.',
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'order_date' => $order->order_date,
                    'total_amount' => (float) $order->total_amount,
                    'paid_amount' => (float) $order->paid_amount,
                    'change_amount' => (float) $order->change_amount,
                    'discount_amount' => (float) $order->discount_amount,
                    'total_hpp_cost' => (float) $order->total_hpp_cost,
                    'total_gross_profit' => (float) $order->total_gross_profit,
                    'points_earned' => (int) $order->points_earned,
                    'status' => $order->status,
                ],
                'whatsapp_url' => $whatsappUrl,
            ], Response::HTTP_CREATED);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Gagal memproses checkout: ' . $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Put an active cart on hold.
     */
    public function holdOrder(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'hold_label' => ['required', 'string', 'max:150'],
            'location_id' => ['nullable', 'string'],
        ]);

        $activeShift = $this->shiftService->getActiveShift($business, $user, $validated['location_id'] ?? null);

        try {
            $held = $this->orderService->holdOrder(
                business: $business,
                cashier: $user,
                itemsData: $validated['items'],
                holdLabel: $validated['hold_label'],
                shift: $activeShift
            );

            return response()->json([
                'message' => "Keranjang berhasil di-hold dengan label '{$held->hold_label}'.",
                'held_order' => $held->load('items'),
            ], Response::HTTP_CREATED);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * List held orders.
     */
    public function getHeldOrders(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $heldOrders = PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_DRAFT_HELD)
            ->with('items')
            ->latest('held_at')
            ->get();

        return response()->json([
            'held_orders' => $heldOrders,
        ], Response::HTTP_OK);
    }

    /**
     * Resume a held order.
     */
    public function resumeOrder(Request $request, PosOrder $posOrder): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($posOrder->business_id !== $business->id || $posOrder->status !== PosOrder::STATUS_DRAFT_HELD) {
            return response()->json([
                'message' => 'Pesanan ini tidak dalam status hold atau tidak ditemukan.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $items = $posOrder->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'unit_price' => (float) $item->unit_price,
                'quantity' => (float) $item->quantity,
                'discount_amount' => (float) $item->discount_amount,
                'notes' => $item->notes,
            ];
        });

        $posOrder->delete();

        return response()->json([
            'message' => 'Pesanan berhasil dilanjutkan ke kasir.',
            'items' => $items,
        ], Response::HTTP_OK);
    }

    /**
     * Structured thermal receipt data for Bluetooth printers (58mm / 80mm).
     */
    public function receipt(Request $request, PosOrder $posOrder): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($posOrder->business_id !== $business->id) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $posOrder->load(['items', 'payments', 'customer', 'user', 'location']);
        $whatsappUrl = $this->loyaltyService->generateWhatsAppReceiptUrl($posOrder);

        return response()->json([
            'store' => [
                'name' => $business->name,
                'address' => $business->address,
                'phone' => $business->phone,
                'footer_note' => 'Terima kasih atas kunjungan Anda!',
            ],
            'order' => [
                'order_number' => $posOrder->order_number,
                'order_date' => $posOrder->order_date,
                'cashier_name' => $posOrder->user?->name ?? 'Kasir',
                'customer_name' => $posOrder->customer?->name ?? $posOrder->customer_name_guest ?? 'Umum',
                'order_type' => $posOrder->order_type,
                'table_or_reference' => $posOrder->table_or_reference,
                'subtotal' => (float) $posOrder->subtotal,
                'discount' => (float) $posOrder->discount_amount,
                'tax' => (float) $posOrder->tax_amount,
                'total' => (float) $posOrder->total_amount,
                'paid' => (float) $posOrder->paid_amount,
                'change' => (float) $posOrder->change_amount,
                'points_earned' => (int) $posOrder->points_earned,
            ],
            'items' => $posOrder->items->map(fn($item) => [
                'name' => $item->product_name,
                'qty' => (float) $item->quantity,
                'price' => (float) $item->unit_price,
                'discount' => (float) $item->discount_amount,
                'total' => (float) $item->total_price,
                'notes' => $item->notes,
            ]),
            'payments' => $posOrder->payments->map(fn($p) => [
                'method' => strtoupper((string) $p->payment_method),
                'amount' => (float) $p->amount,
                'reference' => $p->reference_number,
            ]),
            'whatsapp_url' => $whatsappUrl,
        ], Response::HTTP_OK);
    }

    /**
     * Verify supervisor PIN.
     */
    public function verifySupervisorPin(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $pin = (string) $request->get('pin', '');
        $validPin = $business->pos_supervisor_pin ?? '1234';

        if ($pin === $validPin) {
            return response()->json([
                'verified' => true,
                'message' => 'Otorisasi Supervisor Terverifikasi.',
            ], Response::HTTP_OK);
        }

        return response()->json([
            'verified' => false,
            'message' => 'PIN Supervisor salah.',
        ], Response::HTTP_UNAUTHORIZED);
    }
}
