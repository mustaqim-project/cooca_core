<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Pos;

use App\Domain\Crm\LoyaltyService;
use App\Domain\Pos\PosOrderService;
use App\Domain\Pos\PosShiftService;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosRegister;
use App\Models\PosShift;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Voucher;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

final class PosTerminalWebController extends Controller
{
    public function __construct(
        private readonly PosOrderService $orderService = new PosOrderService,
        private readonly PosShiftService $shiftService = new PosShiftService,
        private readonly LoyaltyService $loyaltyService = new LoyaltyService
    ) {}

    /**
     * Display the full-screen interactive POS cashier terminal.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        // 1. Locations / Outlets
        $locations = Location::where('business_id', $business->id)
            ->where('is_active', true)
            ->get();

        $selectedLocationId = $request->query('location_id')
            ?? session('pos_location_id')
            ?? $locations->where('is_primary', true)->first()?->id
            ?? $locations->first()?->id;

        if ($selectedLocationId) {
            session(['pos_location_id' => $selectedLocationId]);
        }

        // 2. Active Shift for Cashier
        $activeShift = $this->shiftService->getActiveShift($business, $user, $selectedLocationId);

        // 3. Product Categories
        $categories = ProductCategory::where('business_id', $business->id)->get();

        // 4. Products with Selling Price and Effective Stock (Material Master)
        $products = Product::where('business_id', $business->id)
            ->where('is_active', true)
            ->with(['category', 'outputUnit', 'costModels.costingRuns.result'])
            ->get()
            ->map(function ($p) use ($selectedLocationId) {
                // Effective stock dihitung dari Material master stock (via BOM/direct material).
                $p->current_stock = $p->calculateEffectiveStock($selectedLocationId);
                return $p;
            })
            ->each(function ($p): void {
                $p->setAttribute('image_url', $p->image_url);
            });

        // 5. Customers for CRM dropdown
        $customers = Customer::where('business_id', $business->id)
            ->where('is_active', true)
            ->select('id', 'name', 'phone', 'membership_tier', 'points_balance', 'credit_limit', 'current_credit_balance')
            ->get();

        // 6. Active Held Orders for this location
        $heldOrders = PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_DRAFT_HELD)
            ->with('items')
            ->latest('held_at')
            ->get();

        // 7. Active Vouchers
        $vouchers = Voucher::where('business_id', $business->id)
            ->where('is_active', true)
            ->get();

        // 8. Adaptive Operating Mode (Solo Owner vs. Team)
        $operatingModeService = new \App\Domain\System\OperatingModeService;
        $operatingMode = $operatingModeService->getOperatingModeProfile($business);
        $canBypassSupervisor = $operatingModeService->canBypassSupervisor($business, $user);
        $hideCostFromCashier = $operatingModeService->shouldHideCostFromCashier($business, $user);
        $posShowProductImages = (bool) $business->pos_show_product_images;

        return view('app.pos.terminal', compact(
            'business',
            'user',
            'locations',
            'selectedLocationId',
            'activeShift',
            'categories',
            'products',
            'customers',
            'heldOrders',
            'vouchers',
            'operatingMode',
            'canBypassSupervisor',
            'hideCostFromCashier',
            'posShowProductImages'
        ));
    }

    /**
     * Quick search products for barcode scanner or search input (AJAX).
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $query = (string) $request->get('q', '');
        $locationId = (string) $request->get('location_id', '');

        $products = Product::where('business_id', $business->id)
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('code', 'like', "%{$query}%");
            })
            ->with(['stocks' => function ($q) use ($locationId) {
                if ($locationId) {
                    $q->where('location_id', $locationId);
                }
            }])
            ->limit(20)
            ->get()
            ->map(function ($p) {
                $locStock = $p->stocks->first();
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'code' => $p->code,
                    'selling_price' => (float) $p->selling_price,
                    'base_cost' => (float) $p->base_cost,
                    'stock' => $locStock ? (float) $locStock->quantity : 0.0,
                ];
            });

        return response()->json(['success' => true, 'products' => $products]);
    }

    /**
     * Execute POS order checkout (AJAX).
     */
    public function checkout(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

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

        // Transaksi POS hanya boleh dilakukan setelah shift kasir dibuka.
        if ($activeShift === null) {
            return response()->json([
                'success' => false,
                'message' => 'Shift kasir belum dibuka. Buka shift terlebih dahulu sebelum melakukan transaksi POS.',
            ], 403);
        }

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

            // Handle points redemption if requested
            if (! empty($validated['points_to_redeem']) && $order->customer) {
                $this->loyaltyService->redeemPointsForOrder($order->customer, $order, (int) $validated['points_to_redeem']);
            }

            $whatsappUrl = $this->loyaltyService->generateWhatsAppReceiptUrl($order);

            // Auto-send WhatsApp receipt via connected Bot if enabled
            $botSent = false;
            try {
                $waSession = \App\Models\WhatsAppSession::where('business_id', $business->id)->first();
                if ($waSession && $waSession->status === 'connected' && $waSession->auto_send_receipt) {
                    $gateway = app(\App\Domain\WhatsApp\WhatsAppGatewayService::class);
                    $botSent = $gateway->sendReceipt($order);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[POS WhatsApp Auto-Receipt] ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaksi kasir berhasil diselesaikan.',
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => $order->total_amount,
                    'paid_amount' => $order->paid_amount,
                    'change_amount' => $order->change_amount,
                    'total_hpp_cost' => $order->total_hpp_cost,
                    'total_gross_profit' => $order->total_gross_profit,
                    'points_earned' => $order->points_earned,
                ],
                'whatsapp_url' => $whatsappUrl,
                'whatsapp_bot_sent' => $botSent,
                'receipt_url' => route('pos.receipt', $order->id),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses checkout: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Put an active cart on hold (AJAX).
     */
    public function holdOrder(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'hold_label' => ['required', 'string', 'max:150'],
            'location_id' => ['nullable', 'string'],
        ]);

        $activeShift = $this->shiftService->getActiveShift($business, $user, $validated['location_id'] ?? null);

        // Menahan (hold) keranjang juga hanya diperbolehkan dalam shift kasir yang terbuka.
        if ($activeShift === null) {
            return response()->json([
                'success' => false,
                'message' => 'Shift kasir belum dibuka. Buka shift terlebih dahulu sebelum menahan keranjang.',
            ], 403);
        }

        try {
            $held = $this->orderService->holdOrder(
                business: $business,
                cashier: $user,
                itemsData: $validated['items'],
                holdLabel: $validated['hold_label'],
                shift: $activeShift
            );

            return response()->json([
                'success' => true,
                'message' => "Keranjang berhasil di-hold dengan label '{$held->hold_label}'.",
                'held_order' => $held->load('items'),
            ]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Get list of currently held orders (AJAX).
     */
    public function getHeldOrders(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $heldOrders = PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_DRAFT_HELD)
            ->with('items')
            ->latest('held_at')
            ->get();

        return response()->json(['success' => true, 'held_orders' => $heldOrders]);
    }

    /**
     * Resume a held order (and remove held record from DB).
     */
    public function resumeOrder(PosOrder $order): JsonResponse
    {
        if ($order->status !== PosOrder::STATUS_DRAFT_HELD) {
            return response()->json(['success' => false, 'message' => 'Pesanan ini tidak dalam status hold.'], 400);
        }

        $items = $order->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'unit_price' => (float) $item->unit_price,
                'quantity' => (float) $item->quantity,
                'discount_amount' => (float) $item->discount_amount,
                'notes' => $item->notes,
            ];
        });

        $order->delete(); // Remove hold order once resumed

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dilanjutkan ke kasir.',
            'items' => $items,
        ]);
    }

    /**
     * Display printable receipt (58mm / 80mm thermal receipt) and digital preview.
     */
    public function printReceipt(PosOrder $order): View
    {
        $business = $order->business;
        $order->load(['items', 'payments', 'customer', 'user', 'location']);

        $whatsappUrl = $this->loyaltyService->generateWhatsAppReceiptUrl($order);

        return view('app.pos.receipt', compact('order', 'business', 'whatsappUrl'));
    }

    /**
     * Verify supervisor PIN for sensitive overrides (AJAX).
     */
    public function verifySupervisorPin(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $pin = (string) $request->get('pin', '');

        $validPin = $business->pos_supervisor_pin ?? '1234';

        if ($pin === $validPin) {
            return response()->json(['success' => true, 'message' => 'Otorisasi Supervisor Terverifikasi.']);
        }

        return response()->json(['success' => false, 'message' => 'PIN Supervisor salah.'], 401);
    }
}
