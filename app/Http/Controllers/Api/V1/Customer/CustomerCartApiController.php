<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Customer;

use App\Domain\Commerce\CartService;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\CustomerCart;
use App\Models\CustomerCartItem;
use App\Models\GlobalCustomer;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CustomerCartApiController extends Controller
{
    public function __construct(
        private readonly CartService $cartService = new CartService()
    ) {}

    private function customer(Request $request): GlobalCustomer
    {
        /** @var GlobalCustomer $user */
        $user = $request->user();
        return $user;
    }

    /**
     * Get multi-store cart grouped per merchant.
     * GET /api/v1/customer/cart
     */
    public function index(Request $request): JsonResponse
    {
        $customer = $this->customer($request);

        $carts = CustomerCart::where('global_customer_id', $customer->id)
            ->whereHas('items')
            ->with([
                'business:id,name,slug,logo_url,phone,address',
                'items.product:id,name,slug,selling_price,image_url',
            ])
            ->get()
            ->map(function (CustomerCart $cart) {
                return [
                    'cart_id' => $cart->id,
                    'business' => [
                        'id' => $cart->business?->id,
                        'name' => $cart->business?->name,
                        'slug' => $cart->business?->slug,
                        'logo_url' => $cart->business?->logo_url,
                        'address' => $cart->business?->address,
                    ],
                    'items_count' => $cart->items->sum('quantity'),
                    'subtotal' => (float) $cart->items->sum(fn($i) => (float) $i->quantity * (float) ($i->product?->selling_price ?? $i->unit_price ?? 0)),
                    'items' => $cart->items->map(fn(CustomerCartItem $item) => [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'name' => $item->product?->name ?? 'Produk',
                        'slug' => $item->product?->slug,
                        'selling_price' => (float) ($item->product?->selling_price ?? $item->unit_price),
                        'quantity' => (float) $item->quantity,
                        'line_total' => (float) ($item->quantity * ($item->product?->selling_price ?? $item->unit_price)),
                        'image_url' => $item->product?->image_url,
                        'notes' => $item->notes,
                        'selected_modifiers' => $item->selected_modifiers,
                    ]),
                ];
            });

        $grandTotal = (float) $carts->sum('subtotal');
        $totalItems = (int) $carts->sum('items_count');

        return response()->json([
            'success' => true,
            'data' => [
                'carts' => $carts,
                'total_merchants' => $carts->count(),
                'total_items' => $totalItems,
                'grand_total' => $grandTotal,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Add product to a specific merchant's cart.
     * POST /api/v1/customer/cart/{slug}/add
     */
    public function addItem(Request $request, string $slug): JsonResponse
    {
        $customer = $this->customer($request);

        $validated = $request->validate([
            'product_id' => ['required', 'uuid', 'exists:products,id'],
            'quantity' => ['nullable', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string', 'max:255'],
            'selected_modifiers' => ['nullable', 'array'],
        ]);

        $business = Business::where('slug', $slug)->orWhere('id', $slug)->where('is_active', true)->firstOrFail();
        $product = Product::where('id', $validated['product_id'])
            ->where('business_id', $business->id)
            ->where('is_active', true)
            ->firstOrFail();

        $cart = $this->cartService->getOrCreate($customer, $business);
        $cartItem = $this->cartService->addItem(
            cart: $cart,
            product: $product,
            quantity: (float) ($validated['quantity'] ?? 1.0),
            notes: $validated['notes'] ?? null,
            selectedModifiers: $validated['selected_modifiers'] ?? null,
        );

        $cart->load('items.product');

        return response()->json([
            'success' => true,
            'message' => "{$product->name} berhasil ditambahkan ke keranjang.",
            'data' => [
                'cart_id' => $cart->id,
                'business_id' => $business->id,
                'cart_item' => $cartItem,
                'total_cart_items' => $cart->items->sum('quantity'),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Update quantity of a cart item.
     * PATCH /api/v1/customer/cart/{slug}/item/{item}
     */
    public function updateItem(Request $request, string $slug, string $item): JsonResponse
    {
        $customer = $this->customer($request);

        $validated = $request->validate([
            'quantity' => ['required', 'numeric', 'min:0'],
        ]);

        $cartItem = CustomerCartItem::whereHas('cart', function ($q) use ($slug, $customer): void {
            $q->where('global_customer_id', $customer->id)
                ->whereHas('business', fn($bq) => $bq->where('slug', $slug)->orWhere('id', $slug));
        })->find($item);

        if (! $cartItem) {
            return response()->json(['success' => false, 'message' => 'Item keranjang tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $updated = $this->cartService->updateItem($cartItem, (float) $validated['quantity']);

        return response()->json([
            'success' => true,
            'message' => $updated === null ? 'Item telah dihapus dari keranjang.' : 'Jumlah belanjaan berhasil diperbarui.',
            'data' => $updated,
            'removed' => $updated === null,
        ], Response::HTTP_OK);
    }

    /**
     * Remove item from cart.
     * DELETE /api/v1/customer/cart/{slug}/item/{item}
     */
    public function removeItem(Request $request, string $slug, string $item): JsonResponse
    {
        $customer = $this->customer($request);

        $cartItem = CustomerCartItem::whereHas('cart', function ($q) use ($slug, $customer): void {
            $q->where('global_customer_id', $customer->id)
                ->whereHas('business', fn($bq) => $bq->where('slug', $slug)->orWhere('id', $slug));
        })->find($item);

        if (! $cartItem) {
            return response()->json(['success' => false, 'message' => 'Item keranjang tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $this->cartService->removeItem($cartItem);

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dihapus dari keranjang.',
        ], Response::HTTP_OK);
    }
}
