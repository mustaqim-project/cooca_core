<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerWishlist;
use App\Models\GlobalCustomer;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CustomerWishlistApiController extends Controller
{
    private function customer(Request $request): GlobalCustomer
    {
        /** @var GlobalCustomer $user */
        $user = $request->user();
        return $user;
    }

    /**
     * Get active wishlist for authenticated customer.
     * GET /api/v1/customer/wishlist
     */
    public function index(Request $request): JsonResponse
    {
        $customer = $this->customer($request);

        $wishlists = CustomerWishlist::where('global_customer_id', $customer->id)
            ->with([
                'product' => function ($query): void {
                    $query->select('id', 'business_id', 'name', 'slug', 'selling_price', 'image_url', 'is_active', 'status')
                        ->with('business:id,name,slug,logo_url,address');
                },
            ])
            ->latest()
            ->paginate((int) $request->input('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data'   => $wishlists->items(),
            'meta'   => [
                'current_page' => $wishlists->currentPage(),
                'last_page'    => $wishlists->lastPage(),
                'per_page'     => $wishlists->perPage(),
                'total'        => $wishlists->total(),
            ],
        ]);
    }

    /**
     * Toggle a product in customer's wishlist.
     * POST /api/v1/customer/wishlist/toggle
     */
    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'uuid', 'exists:products,id'],
        ]);

        $customer = $this->customer($request);
        $productId = (string) $validated['product_id'];

        // Verify product exists & is active
        $product = Product::where('id', $productId)->first();
        if ($product === null) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Produk tidak ditemukan.',
            ], Response::HTTP_NOT_FOUND);
        }

        $existing = CustomerWishlist::where('global_customer_id', $customer->id)
            ->where('product_id', $productId)
            ->first();

        if ($existing !== null) {
            $existing->delete();

            return response()->json([
                'status'        => 'success',
                'message'       => 'Produk berhasil dihapus dari daftar favorit.',
                'action'        => 'removed',
                'is_wishlisted' => false,
                'product_id'    => $productId,
            ]);
        }

        $created = CustomerWishlist::create([
            'global_customer_id' => $customer->id,
            'product_id'         => $productId,
        ]);

        return response()->json([
            'status'        => 'success',
            'message'       => 'Produk berhasil ditambahkan ke daftar favorit.',
            'action'        => 'added',
            'is_wishlisted' => true,
            'wishlist_id'   => $created->id,
            'product_id'    => $productId,
        ], Response::HTTP_CREATED);
    }
}
