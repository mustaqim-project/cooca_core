<?php

declare(strict_types=1);

namespace App\Domain\Commerce;

use App\Models\Business;
use App\Models\CustomerCart;
use App\Models\CustomerCartItem;
use App\Models\GlobalCustomer;
use App\Models\Product;
use Illuminate\Support\Str;

class CartService
{
    /**
     * Get or create cart for a customer in a specific store.
     */
    public function getOrCreate(GlobalCustomer $customer, Business $business): CustomerCart
    {
        return CustomerCart::firstOrCreate(
            [
                'global_customer_id' => $customer->id,
                'business_id'        => $business->id,
            ],
            ['expires_at' => null],
        );
    }

    /**
     * Add a product to cart or increment quantity if already exists.
     */
    public function addItem(
        CustomerCart $cart,
        Product $product,
        float $quantity = 1.0,
        ?string $notes = null,
        ?array $selectedModifiers = null
    ): CustomerCartItem {
        $unitPrice = $product->selling_price ?? 0.0;

        $existing = $cart->items()->where('product_id', $product->id)->first();

        if ($existing) {
            $existing->increment('quantity', $quantity);

            return $existing->fresh();
        }

        return $cart->items()->create([
            'product_id'         => $product->id,
            'quantity'           => $quantity,
            'unit_price'         => $unitPrice,
            'notes'              => $notes,
            'selected_modifiers' => $selectedModifiers,
        ]);
    }

    /**
     * Update quantity of a cart item. Removes if qty <= 0.
     */
    public function updateItem(CustomerCartItem $item, float $quantity): ?CustomerCartItem
    {
        if ($quantity <= 0) {
            $item->delete();

            return null;
        }

        $item->update(['quantity' => $quantity]);

        return $item->fresh();
    }

    /**
     * Remove a single item from cart.
     */
    public function removeItem(CustomerCartItem $item): void
    {
        $item->delete();
    }

    /**
     * Clear all items in a cart (keep the cart record).
     */
    public function clear(CustomerCart $cart): void
    {
        $cart->items()->delete();
    }

    /**
     * Get cart with items eager-loaded.
     */
    public function load(CustomerCart $cart): CustomerCart
    {
        return $cart->load(['items.product']);
    }

    /**
     * Convert cart items to order line format for CommerceOrderService.
     */
    public function toOrderLines(CustomerCart $cart): array
    {
        return $cart->items->map(fn (CustomerCartItem $item) => [
            'product_id'  => $item->product_id,
            'product_name'=> $item->product?->name,
            'quantity'    => $item->quantity,
            'unit_price'  => $item->unit_price,
            'notes'       => $item->notes,
        ])->toArray();
    }
}
