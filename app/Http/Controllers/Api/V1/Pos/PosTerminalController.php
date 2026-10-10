<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Pos;

use App\Domain\Crm\LoyaltyService;
use App\Domain\Payment\TripayService;
use App\Domain\Pos\PosOrderService;
use App\Domain\Pos\PosShiftService;
use App\Domain\System\AuditLogService;
use App\Domain\System\OperatingModeService;
use App\Http\Controllers\Controller;
use App\Models\CommerceReservation;
use App\Models\Customer;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosTable;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StoreEdcTerminal;
use App\Models\Voucher;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class PosTerminalController extends Controller
{
    public function __construct(
        private readonly PosOrderService $orderService = new PosOrderService,
        private readonly PosShiftService $shiftService = new PosShiftService,
        private readonly LoyaltyService $loyaltyService = new LoyaltyService,
        private readonly OperatingModeService $operatingModeService = new OperatingModeService,
        private readonly TripayService $tripayService = new TripayService
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
            ->with(['category', 'outputUnit', 'channelPrices', 'stocks' => function ($q) use ($selectedLocationId) {
                if ($selectedLocationId) {
                    $q->where('location_id', $selectedLocationId);
                }
            }])
            ->get()
            ->map(function (Product $p) use ($selectedLocationId) {
                $isService = $p->isService();
                $locStock = $p->stocks->first();
                $channelPricesMap = [
                    'dine_in' => (float) $p->selling_price,
                    'takeaway' => (float) $p->selling_price,
                ];
                if ($p->relationLoaded('channelPrices')) {
                    foreach ($p->channelPrices as $cp) {
                        $channelPricesMap[$cp->channel] = (float) $cp->price;
                    }
                }
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'code' => $p->code,
                    'slug' => $p->slug,
                    'type' => $p->type ?? Product::TYPE_GOODS,
                    'is_service' => $isService,
                    'track_inventory' => ! $isService,
                    'category_id' => $p->category_id,
                    'category_name' => $p->category?->name,
                    'unit_id' => $p->output_unit_id,
                    'unit_code' => $p->outputUnit?->code ?? ($isService ? 'jasa' : 'satuan'),
                    'selling_price' => (float) $p->selling_price,
                    'channel_prices' => $channelPricesMap,
                    'base_cost' => (float) $p->base_cost,
                    'stock' => $isService ? null : ($locStock ? (float) $locStock->quantity : 0.0),
                    'min_stock' => (float) $p->min_stock,
                    'image_url' => $p->image_url,
                    'modifier_groups' => $p->getAvailableModifierGroupsWithStock($selectedLocationId),
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

        // 9. Dynamic Industry Profile
        $industryProfile = [
            'template_code' => $business->template_code,
            'industry_category' => $business->industry_category,
            'is_food_industry' => $business->isFoodIndustry(),
            'has_dine_in' => $business->hasDineInFeature(),
            'is_pharmacy' => $business->isPharmacy(),
            'is_workshop' => $business->isWorkshop(),
            'is_laundry' => $business->isLaundry(),
            'is_retail' => $business->isRetailSector(),
            'is_service' => $business->isServiceSector(),
            'is_manufacturing' => $business->isManufacturingSector(),
        ];

        // 10. Dining Tables (if F&B Dine-In enabled)
        $tables = $business->hasDineInFeature()
            ? PosTable::where('business_id', $business->id)
                ->where('is_active', true)
                ->when($selectedLocationId, fn($q) => $q->where('location_id', $selectedLocationId))
                ->get()
                ->map(fn(PosTable $t) => [
                    'id' => $t->id,
                    'table_number' => $t->table_number,
                    'name' => $t->name,
                    'capacity' => $t->capacity,
                    'status' => $t->status,
                ])
            : [];

        // 11. Today's Reservations (F&B / Service)
        $todayReservations = CommerceReservation::where('business_id', $business->id)
            ->whereDate('reservation_date', Carbon::today())
            ->whereNotIn('status', [CommerceReservation::STATUS_CANCELLED, CommerceReservation::STATUS_NO_SHOW])
            ->with('posTable')
            ->orderBy('time_slot')
            ->get()
            ->map(fn(CommerceReservation $r) => [
                'id' => $r->id,
                'reservation_code' => $r->reservation_code,
                'customer_name' => $r->customer_name,
                'customer_phone' => $r->customer_phone,
                'time_slot' => $r->time_slot,
                'guest_count' => $r->guest_count,
                'status' => $r->status,
                'pos_table_id' => $r->pos_table_id,
                'table_number' => $r->posTable?->table_number ?? $r->posTable?->name,
                'notes' => $r->notes,
            ]);

        // 12. Store EDC Terminals
        $edcTerminals = StoreEdcTerminal::where('business_id', $business->id)
            ->where('is_active', true)
            ->when($selectedLocationId, fn($q) => $q->where(fn($sub) => $sub->where('location_id', $selectedLocationId)->orWhereNull('location_id')))
            ->get()
            ->map(fn(StoreEdcTerminal $edc) => [
                'id' => $edc->id,
                'bank_name' => $edc->bank_name,
                'terminal_name' => $edc->terminal_name,
                'terminal_id_tid' => $edc->terminal_id_tid,
                'mdr_debit_percent' => (float) $edc->mdr_debit_percent,
                'mdr_credit_percent' => (float) $edc->mdr_credit_percent,
            ]);

        // 13. Technicians / Service Staff (Workshop / Services)
        $technicians = $business->users()
            ->select('users.id', 'users.name', 'users.email')
            ->get()
            ->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
            ]);

        return response()->json([
            'business' => [
                'id' => $business->id,
                'name' => $business->name,
                'slug' => $business->slug,
                'logo_url' => $business->logo_url,
                'currency' => $business->currency ?? 'IDR',
                'phone' => $business->phone,
                'address' => $business->address,
                'template_code' => $business->template_code,
                'industry_category' => $business->industry_category,
                'rounding_strategy' => $business->rounding_strategy,
                'pos_enable_tax' => (bool) $business->pos_enable_tax,
                'pos_tax_percent' => (float) $business->pos_tax_percent,
                'pos_enable_service_charge' => (bool) $business->pos_enable_service_charge,
                'pos_service_charge_percent' => (float) $business->pos_service_charge_percent,
                'pos_receipt_footer_note' => $business->pos_receipt_footer_note,
                'pos_auto_send_kds' => (bool) ($business->pos_auto_send_kds ?? false),
                'qris_image_url' => \App\Models\CommercePaymentMethod::where('business_id', $business->id)
                    ->where('type', \App\Models\CommercePaymentMethod::TYPE_QRIS)
                    ->where('is_active', true)
                    ->first()?->qris_image_url,
            ],
            'user' => [
                'id' => $user?->id,
                'name' => $user?->name,
                'email' => $user?->email,
            ],
            'industry_profile' => $industryProfile,
            'tables' => $tables,
            'today_reservations' => $todayReservations,
            'store_edc_terminals' => $edcTerminals,
            'technicians' => $technicians,
            'selected_location_id' => $selectedLocationId,
            'locations' => $locations,
            'active_shift' => $activeShift ? [
                'id' => $activeShift->id,
                'cashier_name' => $activeShift->user?->name ?? $user?->name ?? 'Kasir',
                'location_name' => $activeShift->location?->name ?? 'Utama',
                'opened_at' => $activeShift->opened_at,
                'opening_cash' => (float) $activeShift->opening_cash,
                'status' => $activeShift->status,
                'total_cash_sales' => (float) $activeShift->total_cash_sales,
                'total_non_cash_sales' => (float) $activeShift->total_non_cash_sales,
                'expected_cash' => (float) ($activeShift->opening_cash + $activeShift->total_cash_sales),
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
            ->map(function (Product $p) use ($locationId) {
                $isService = $p->isService();
                $locStock = $p->stocks->first();
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'code' => $p->code,
                    'type' => $p->type ?? Product::TYPE_GOODS,
                    'is_service' => $isService,
                    'track_inventory' => ! $isService,
                    'selling_price' => (float) $p->selling_price,
                    'base_cost' => (float) $p->base_cost,
                    'unit_code' => $p->outputUnit?->code ?? ($isService ? 'jasa' : 'pcs'),
                    'category_name' => $p->category?->name,
                    'stock' => $isService ? null : ($locStock ? (float) $locStock->quantity : 0.0),
                    'modifier_groups' => $p->getAvailableModifierGroupsWithStock($locationId ?: null),
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
            'items.*.batch_number' => ['nullable', 'string'],
            'items.*.expired_date' => ['nullable', 'string'],
            'items.*.dosage_instructions' => ['nullable', 'string'],
            'items.*.serial_number' => ['nullable', 'string'],
            'items.*.selected_modifiers' => ['nullable', 'array'],
            'payments' => ['required', 'array', 'min:1'],
            'payments.*.payment_method' => ['required', 'string'],
            'payments.*.amount' => ['required', 'numeric', 'min:0'],
            'payments.*.reference_number' => ['nullable', 'string'],
            'payments.*.store_edc_terminal_id' => ['nullable', 'string'],
            'customer_id' => ['nullable', 'string'],
            'customer_name_guest' => ['nullable', 'string', 'max:150'],
            'customer_phone_guest' => ['nullable', 'string', 'max:50'],
            'order_type' => ['nullable', 'string', 'in:dine_in,takeaway,delivery'],
            'sales_channel' => ['nullable', 'string'],
            'pos_table_id' => ['nullable', 'string'],
            'table_or_reference' => ['nullable', 'string', 'max:100'],
            'discount_type' => ['nullable', 'string', 'in:fixed,percentage'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'voucher_code' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
            'points_to_redeem' => ['nullable', 'integer', 'min:0'],
            'location_id' => ['nullable', 'string'],
            'pos_register_id' => ['nullable', 'string'],
            // Multi-Industry fields
            'client_uuid' => ['nullable', 'string', 'max:64'],
            'vehicle_license_plate' => ['nullable', 'string', 'max:30'],
            'vehicle_model' => ['nullable', 'string', 'max:100'],
            'vehicle_mileage' => ['nullable', 'numeric', 'min:0'],
            'technician_id' => ['nullable', 'string'],
            'service_notes' => ['nullable', 'string', 'max:1000'],
            'laundry_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'rack_location' => ['nullable', 'string', 'max:50'],
            'estimated_completion_at' => ['nullable', 'string'],
            'laundry_status' => ['nullable', 'string', 'max:50'],
        ]);

        // Idempotency Gate: if client retries after network timeout, return existing order
        if (! empty($validated['client_uuid'])) {
            $existingOrder = PosOrder::where('business_id', $business->id)
                ->where('client_uuid', $validated['client_uuid'])
                ->first();

            if ($existingOrder !== null) {
                $whatsappUrl = $this->loyaltyService->generateWhatsAppReceiptUrl($existingOrder);
                return response()->json([
                    'message' => 'Transaksi kasir sudah pernah diproses sebelumnya (idempotent).',
                    'order' => [
                        'id' => $existingOrder->id,
                        'order_number' => $existingOrder->order_number,
                        'order_date' => $existingOrder->order_date,
                        'total_amount' => (float) $existingOrder->total_amount,
                        'paid_amount' => (float) $existingOrder->paid_amount,
                        'change_amount' => (float) $existingOrder->change_amount,
                        'discount_amount' => (float) $existingOrder->discount_amount,
                        'total_hpp_cost' => (float) $existingOrder->total_hpp_cost,
                        'total_gross_profit' => (float) $existingOrder->total_gross_profit,
                        'points_earned' => (int) $existingOrder->points_earned,
                        'status' => $existingOrder->status,
                    ],
                    'whatsapp_url' => $whatsappUrl,
                    'duplicate_skipped' => true,
                ], Response::HTTP_OK);
            }
        }

        $activeShift = $this->shiftService->getActiveShift($business, $user, $validated['location_id'] ?? null);

        $isQrisCheckout = (count($validated['payments']) === 1 && strtolower((string) ($validated['payments'][0]['payment_method'] ?? '')) === 'qris');

        try {
            $order = $this->orderService->checkout(
                business: $business,
                cashier: $user,
                itemsData: $validated['items'],
                paymentsData: $validated['payments'],
                attributes: [
                    'waiting_payment' => $isQrisCheckout,
                    'payment_gateway' => $isQrisCheckout ? PosOrder::GATEWAY_TRIPAY : PosOrder::GATEWAY_MANUAL,
                    'payment_channel' => $isQrisCheckout ? 'QRIS' : null,
                    'customer_id' => $validated['customer_id'] ?? null,
                    'customer_name_guest' => $validated['customer_name_guest'] ?? null,
                    'customer_phone_guest' => $validated['customer_phone_guest'] ?? null,
                    'order_type' => $validated['order_type'] ?? 'takeaway',
                    'sales_channel' => $validated['sales_channel'] ?? 'dine_in',
                    'pos_table_id' => $validated['pos_table_id'] ?? null,
                    'pos_register_id' => $validated['pos_register_id'] ?? $activeShift?->pos_register_id ?? null,
                    'table_or_reference' => $validated['table_or_reference'] ?? null,
                    'discount_type' => $validated['discount_type'] ?? 'fixed',
                    'discount_value' => (float) ($validated['discount_value'] ?? 0),
                    'voucher_code' => $validated['voucher_code'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'location_id' => $validated['location_id'] ?? null,
                    // Multi-Industry
                    'vehicle_license_plate' => $validated['vehicle_license_plate'] ?? null,
                    'vehicle_model' => $validated['vehicle_model'] ?? null,
                    'vehicle_mileage' => $validated['vehicle_mileage'] ?? null,
                    'technician_id' => $validated['technician_id'] ?? null,
                    'service_notes' => $validated['service_notes'] ?? null,
                    'laundry_weight_kg' => $validated['laundry_weight_kg'] ?? null,
                    'rack_location' => $validated['rack_location'] ?? null,
                    'estimated_completion_at' => $validated['estimated_completion_at'] ?? null,
                    'laundry_status' => $validated['laundry_status'] ?? null,
                ],
                shift: $activeShift
            );

            if (! empty($validated['client_uuid'])) {
                $order->client_uuid = $validated['client_uuid'];
                $order->save();
            }

            if ($isQrisCheckout) {
                $tripayRes = $this->tripayService->createPosOrderTransaction($order, 'QRIS');

                if ($tripayRes['success'] ?? false) {
                    $order->update([
                        'status' => PosOrder::STATUS_WAITING_PAYMENT,
                        'payment_gateway' => PosOrder::GATEWAY_TRIPAY,
                        'payment_channel' => $tripayRes['payment_method'] ?? 'QRIS',
                        'gateway_reference' => $tripayRes['reference'] ?? null,
                        'gateway_pay_code' => $tripayRes['pay_code'] ?? null,
                        'gateway_pay_url' => $tripayRes['checkout_url'] ?? null,
                        'gateway_qr_url' => $tripayRes['qr_url'] ?? null,
                        'gateway_qr_string' => $tripayRes['qr_string'] ?? null,
                        'gateway_fee' => (float) ($tripayRes['fee'] ?? 0.0),
                        'gateway_expired_at' => isset($tripayRes['expired_time']) ? Carbon::createFromTimestamp($tripayRes['expired_time']) : null,
                    ]);

                    return response()->json([
                        'success' => true,
                        'is_qris' => true,
                        'message' => 'Silakan scan QRIS untuk menyelesaikan pembayaran.',
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
                            'is_paid' => $order->isPaid(),
                            'customer_name' => $order->customer_name_guest ?: ($order->customer?->name ?? 'Pelanggan Umum'),
                            'table_number' => $order->posTable?->table_number ?? $order->table_or_reference,
                            'created_at' => $order->created_at->format('H:i'),
                        ],
                        'payment' => [
                            'gateway' => 'tripay',
                            'channel' => $tripayRes['payment_method'] ?? 'QRIS',
                            'reference' => $tripayRes['reference'] ?? null,
                            'pay_code' => $tripayRes['pay_code'] ?? null,
                            'qr_url' => $tripayRes['qr_url'] ?? null,
                            'qr_string' => $tripayRes['qr_string'] ?? null,
                            'checkout_url' => $tripayRes['checkout_url'] ?? null,
                            'expired_time' => $tripayRes['expired_time'] ?? (time() + 900),
                        ],
                    ], Response::HTTP_CREATED);
                }

                $order->update([
                    'status' => PosOrder::STATUS_VOIDED,
                    'void_reason' => 'Gagal inisialisasi QRIS TriPay: ' . ($tripayRes['message'] ?? 'Unknown error'),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat QRIS TriPay: ' . ($tripayRes['message'] ?? 'Kendala gateway pembayaran'),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if (! empty($validated['points_to_redeem']) && $order->customer) {
                $this->loyaltyService->redeemPointsForOrder($order->customer, $order, (int) $validated['points_to_redeem']);
            }

            $whatsappUrl = $this->loyaltyService->generateWhatsAppReceiptUrl($order);

            return response()->json([
                'success' => true,
                'is_qris' => false,
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
                'success' => false,
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
     * Verify supervisor PIN with rate limiting and strict Bcrypt verification.
     */
    public function verifySupervisorPin(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        if ($this->operatingModeService->canBypassSupervisor($business, $user)) {
            return response()->json([
                'verified' => true,
                'message' => __('auth.supervisor_auth_allowed'),
            ], Response::HTTP_OK);
        }

        if (! $business->hasSupervisorPin()) {
            return response()->json([
                'verified' => false,
                'message' => __('pos.supervisor_pin_not_configured'),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $throttleKey = 'pos_supervisor_pin:' . $business->id . ':' . ($user?->id ?? $request->ip());
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = max(1, (int) ceil($seconds / 60));
            return response()->json([
                'verified' => false,
                'message' => __('auth.pin_supervisor_locked', ['minutes' => $minutes]),
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        $pin = (string) $request->input('pin', '');
        if ($pin === '') {
            return response()->json([
                'verified' => false,
                'message' => __('pos.supervisor_pin_required'),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($business->verifySupervisorPin($pin)) {
            RateLimiter::clear($throttleKey);
            return response()->json([
                'verified' => true,
                'message' => __('pos.supervisor_auth_verified'),
            ], Response::HTTP_OK);
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

        return response()->json([
            'verified' => false,
            'message' => $message,
        ], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Check in / Seat a customer reservation directly from mobile POS.
     */
    public function seatReservation(Request $request, CommerceReservation $reservation): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($reservation->business_id !== $business->id) {
            return response()->json(['message' => 'Reservasi tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $tableId = $request->input('pos_table_id', $reservation->pos_table_id);
        if (! $tableId) {
            return response()->json([
                'message' => 'Silakan pilih meja terlebih dahulu untuk tamu reservasi ini.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $table = PosTable::where('business_id', $business->id)->findOrFail($tableId);

            // Assign table if not assigned yet or changed
            if ($reservation->pos_table_id !== $table->id) {
                $reservationService = new \App\Domain\Commerce\Storefront\ReservationBookingService();
                $reservationService->assignTable($reservation, $table);
            }

            // Mark as seated
            $reservation->markAsSeated();

            // Create or get active table session
            $tableService = new \App\Domain\Pos\PosTableService();
            $session = $tableService->getOrCreateActiveSession(
                $table,
                $reservation->customer_name,
                $reservation->customer_phone
            );

            return response()->json([
                'message' => "Tamu #{$reservation->reservation_code} ({$reservation->customer_name}) berhasil duduk di Meja #{$table->table_number}.",
                'table' => [
                    'id' => $table->id,
                    'table_number' => $table->table_number,
                    'name' => $table->name,
                    'status' => $table->status,
                ],
                'session' => [
                    'id' => $session->id,
                    'session_number' => $session->session_number,
                    'customer_name' => $session->customer_name,
                    'customer_phone' => $session->customer_phone,
                ],
            ], Response::HTTP_OK);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Gagal menempatkan reservasi: ' . $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Send active cart directly to Kitchen Display System (KDS) from Mobile POS Terminal
     * without printing receipt/KOT (Paperless kitchen routing).
     */
    public function sendToKitchen(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'location_id' => ['nullable', 'string'],
            'pos_table_id' => ['nullable', 'string'],
            'customer_id' => ['nullable', 'string'],
            'customer_name_guest' => ['nullable', 'string', 'max:100'],
            'order_type' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $activeShift = $this->shiftService->getActiveShift($business, $user, $validated['location_id'] ?? null);

        if ($activeShift === null) {
            return response()->json([
                'success' => false,
                'message' => 'Shift kasir belum dibuka. Buka shift terlebih dahulu sebelum mengirim pesanan ke dapur.',
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $order = $this->orderService->sendToKitchen(
                business: $business,
                cashier: $user,
                itemsData: $validated['items'],
                attributes: [
                    'location_id' => $validated['location_id'] ?? $activeShift->location_id,
                    'pos_table_id' => $validated['pos_table_id'] ?? null,
                    'customer_id' => $validated['customer_id'] ?? null,
                    'customer_name_guest' => $validated['customer_name_guest'] ?? null,
                    'order_type' => $validated['order_type'] ?? 'dine_in',
                    'notes' => $validated['notes'] ?? null,
                ],
                shift: $activeShift
            );

            return response()->json([
                'success' => true,
                'message' => "Pesanan #{$order->order_number} berhasil dikirim ke Layar Dapur (KDS) tanpa cetak struk.",
                'order' => $order,
            ], Response::HTTP_OK);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Quick-toggle Auto-Send to KDS feature directly from POS Terminal.
     */
    public function toggleAutoKds(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $newState = ! (bool) ($business->pos_auto_send_kds ?? false);
        $business->update(['pos_auto_send_kds' => $newState]);

        return response()->json([
            'success' => true,
            'pos_auto_send_kds' => $newState,
            'message' => $newState
                ? 'Fitur Otomatis Kirim ke KDS DIAKTIFKAN. Setiap transaksi kasir diteruskan ke Layar Dapur tanpa cetak struk.'
                : 'Fitur Otomatis Kirim ke KDS DINONAKTIFKAN. Transaksi kasir diselesaikan tanpa masuk ke Layar Dapur.',
        ], Response::HTTP_OK);
    }

    /**
     * Check POS order payment status (Polled by terminal/mobile app during QRIS Cooca Pay session).
     */
    public function checkOrderStatus(Request $request, string $id): JsonResponse
    {
        $business = Context::requireBusiness();

        $order = PosOrder::where('business_id', $business->id)->find($id);
        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        // Check if already paid
        if ($order->isPaid() || in_array($order->status, [PosOrder::STATUS_CONFIRMED, PosOrder::STATUS_COMPLETED], true)) {
            $whatsappUrl = $this->loyaltyService->generateWhatsAppReceiptUrl($order);

            return response()->json([
                'success' => true,
                'is_paid' => true,
                'status' => $order->status,
                'message' => 'Pembayaran QRIS telah berhasil diverifikasi.',
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => (float) $order->total_amount,
                    'paid_amount' => (float) $order->paid_amount,
                    'change_amount' => (float) $order->change_amount,
                    'status' => $order->status,
                ],
                'whatsapp_url' => $whatsappUrl,
            ], Response::HTTP_OK);
        }

        // Active fail-safe fallback: If order is waiting payment and has tripay reference, query TriPay detail
        if ($order->status === PosOrder::STATUS_WAITING_PAYMENT && $order->gateway_reference) {
            try {
                $detail = $this->tripayService->getTransactionDetail($order->gateway_reference);
                $gwStatus = strtoupper((string) ($detail['status'] ?? ''));

                if ($gwStatus === 'PAID') {
                    $this->orderService->confirmQrisPayment(
                        $order,
                        $order->gateway_reference,
                        (float) ($detail['total_fee'] ?? 0),
                        $detail
                    );
                    $order->refresh();

                    $whatsappUrl = $this->loyaltyService->generateWhatsAppReceiptUrl($order);

                    return response()->json([
                        'success' => true,
                        'is_paid' => true,
                        'status' => $order->status,
                        'message' => 'Pembayaran QRIS berhasil dikonfirmasi dari TriPay.',
                        'order' => [
                            'id' => $order->id,
                            'order_number' => $order->order_number,
                            'total_amount' => (float) $order->total_amount,
                            'paid_amount' => (float) $order->paid_amount,
                            'change_amount' => (float) $order->change_amount,
                            'status' => $order->status,
                        ],
                        'whatsapp_url' => $whatsappUrl,
                    ], Response::HTTP_OK);
                } elseif (in_array($gwStatus, ['EXPIRED', 'FAILED', 'REFUND'], true)) {
                    $order->update([
                        'status' => PosOrder::STATUS_VOIDED,
                        'void_reason' => 'QRIS TriPay kedaluwarsa atau gagal (' . $gwStatus . ')',
                    ]);

                    return response()->json([
                        'success' => false,
                        'is_paid' => false,
                        'status' => $order->status,
                        'message' => 'Transaksi QRIS ' . strtolower($gwStatus) . '.',
                    ], Response::HTTP_OK);
                }
            } catch (Throwable $e) {
                // Log and continue polling
            }
        }

        return response()->json([
            'success' => true,
            'is_paid' => false,
            'status' => $order->status,
            'message' => 'Menunggu pembayaran QRIS...',
        ], Response::HTTP_OK);
    }

    /**
     * Serve or proxy QRIS image directly with CORS headers enabled.
     */
    public function getQrisImage(Request $request, string $id): \Symfony\Component\HttpFoundation\Response
    {
        $business = Context::requireBusiness();

        $order = PosOrder::where('business_id', $business->id)->find($id);
        if (! $order || empty($order->gateway_qr_url)) {
            return response()->json(['success' => false, 'message' => 'QR image tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(8)->get($order->gateway_qr_url);
            if ($response->successful()) {
                return response($response->body(), 200, [
                    'Content-Type' => 'image/png',
                    'Access-Control-Allow-Origin' => '*',
                    'Cache-Control' => 'no-cache, private',
                ]);
            }
        } catch (\Throwable) {
            // fallback redirect
        }

        return redirect()->away($order->gateway_qr_url);
    }

    /**
     * Cancel pending QRIS order from POS Mobile / Cashier Terminal.
     */
    public function cancelQrisOrder(Request $request, string $id): JsonResponse
    {
        $business = Context::requireBusiness();

        $order = PosOrder::where('business_id', $business->id)->find($id);
        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        if ($order->isPaid() || in_array($order->status, [PosOrder::STATUS_CONFIRMED, PosOrder::STATUS_COMPLETED], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan sudah lunas, tidak dapat dibatalkan melalui aksi ini.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order->update([
            'status' => PosOrder::STATUS_VOIDED,
            'void_reason' => $request->input('reason', 'Dibatalkan oleh kasir sebelum pembayaran QRIS.'),
        ]);

        if ($order->pos_table_id) {
            try {
                $table = PosTable::find($order->pos_table_id);
                if ($table && $table->status === 'occupied') {
                    $hasOther = PosOrder::where('pos_table_id', $table->id)
                        ->whereIn('status', [PosOrder::STATUS_CONFIRMED, PosOrder::STATUS_WAITING_PAYMENT])
                        ->where('id', '!=', $order->id)
                        ->exists();
                    if (! $hasOther) {
                        $table->update(['status' => 'available', 'current_order_id' => null]);
                    }
                }
            } catch (Throwable) {
                // Ignore table release error
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Sesi QRIS berhasil dibatalkan. Keranjang dapat diproses kembali.',
        ], Response::HTTP_OK);
    }

    /**
     * Simulate successful sandbox payment for testing without waiting webhook.
     */
    public function simulateSandboxPayment(Request $request, string $id): JsonResponse
    {
        $business = Context::requireBusiness();

        $order = PosOrder::where('business_id', $business->id)->find($id);
        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        if ($order->status !== PosOrder::STATUS_WAITING_PAYMENT) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan bukan dalam status menunggu pembayaran.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $ref = $order->gateway_reference ?: ('SIM-DEV-' . time());
        $this->orderService->confirmQrisPayment($order, $ref, 0, [
            'simulated' => true,
            'simulated_by' => $request->user()?->name ?? 'Kasir Mobile',
        ]);
        $order->refresh();

        $whatsappUrl = $this->loyaltyService->generateWhatsAppReceiptUrl($order);

        return response()->json([
            'success' => true,
            'is_paid' => true,
            'message' => '[Sandbox] Simulasi pembayaran QRIS berhasil.',
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => (float) $order->total_amount,
                'paid_amount' => (float) $order->paid_amount,
                'change_amount' => (float) $order->change_amount,
                'status' => $order->status,
            ],
            'whatsapp_url' => $whatsappUrl,
        ], Response::HTTP_OK);
    }
}

