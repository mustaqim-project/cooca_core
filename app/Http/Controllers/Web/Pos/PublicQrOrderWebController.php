<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Pos;

use App\Domain\Pos\PosOrderService;
use App\Http\Controllers\Controller;
use App\Models\PosOrder;
use App\Models\PosTable;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

final class PublicQrOrderWebController extends Controller
{
    public function __construct(
        private readonly PosOrderService $orderService = new PosOrderService
    ) {}

    /**
     * Show public mobile-first ordering menu for a scanned table.
     */
    public function showMenu(Request $request, string $qrToken): View
    {
        $table = PosTable::where('qr_token', $qrToken)
            ->where('is_active', true)
            ->with(['business', 'activeSession.orders.items.modifiers'])
            ->first();

        if (! $table || ! $table->business || ! $table->business->is_active) {
            return view('public.qr-order.invalid-qr', [
                'message' => 'QR Code meja tidak valid atau sudah tidak aktif. Silakan hubungi staff restoran.',
            ]);
        }

        $business = $table->business;
        $locationId = $table->location_id;

        // Categories
        $categories = ProductCategory::where('business_id', $business->id)->orderBy('name')->get();

        // Products with effective stock & modifier groups
        $products = Product::where('business_id', $business->id)
            ->where('is_active', true)
            ->with(['category', 'outputUnit'])
            ->get()
            ->map(function (Product $p) use ($locationId): array {
                $stock = $p->calculateEffectiveStock($locationId);
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'code' => $p->code,
                    'description' => $p->description,
                    'selling_price' => (float) $p->selling_price,
                    'category_id' => $p->category_id,
                    'image_url' => $p->image_url,
                    'stock' => $stock,
                    'is_available' => $stock > 0,
                    'unit_name' => $p->outputUnit?->name ?? 'Porsi',
                    'modifier_groups' => $p->getAvailableModifierGroupsWithStock($locationId),
                ];
            })
            ->values()
            ->all();

        // Check if table currently has an active session and recent orders
        $activeSession = $table->activeSession;
        $recentOrders = $activeSession ? $activeSession->orders()->with('items.modifiers')->latest()->get() : collect();

        return view('public.qr-order.menu', compact(
            'table',
            'business',
            'categories',
            'products',
            'activeSession',
            'recentOrders'
        ));
    }

    /**
     * Submit customer QR order.
     */
    public function submitOrder(Request $request, string $qrToken): JsonResponse
    {
        $table = PosTable::where('qr_token', $qrToken)
            ->where('is_active', true)
            ->with('business')
            ->first();

        if (! $table || ! $table->business || ! $table->business->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code meja tidak valid atau sudah tidak aktif.',
            ], 404);
        }

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'min:2', 'max:150'],
            'customer_phone' => ['required', 'string', 'min:8', 'max:30'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:1'],
            'items.*.selected_modifiers' => ['nullable', 'array'],
            'items.*.selected_modifiers.*' => ['string'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $order = $this->orderService->createQrOrder(
                table: $table,
                customerName: $validated['customer_name'],
                customerPhone: $validated['customer_phone'],
                itemsData: $validated['items'],
                orderNotes: $validated['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dikirim ke kasir.',
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => (float) $order->total_amount,
                    'status' => $order->status,
                    'table_number' => $table->table_number,
                    'customer_name' => $order->customer_name_guest,
                    'created_at' => $order->created_at->format('H:i'),
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
     * Get live status of an order for tracking screen.
     */
    public function trackOrder(string $qrToken, PosOrder $order): JsonResponse
    {
        $table = PosTable::where('qr_token', $qrToken)->firstOrFail();
        if ($order->pos_table_id !== $table->id) {
            abort(403);
        }

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total_amount' => (float) $order->total_amount,
                'rejection_reason' => $order->rejection_reason,
                'items' => $order->items->map(fn ($item) => [
                    'name' => $item->product_name,
                    'quantity' => (float) $item->quantity,
                    'modifiers' => $item->modifiers_display_text,
                    'notes' => $item->notes,
                ]),
            ],
        ]);
    }
}
