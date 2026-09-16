<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Commerce;

use App\Domain\Commerce\CartService;
use App\Domain\Commerce\Storefront\CommerceOrderService;
use App\Domain\Commerce\Storefront\CommercePaymentProofService;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\CommerceOrder;
use App\Models\CustomerCart;
use App\Models\CustomerCartItem;
use App\Models\GlobalCustomer;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Throwable;

final class CustomerPortalController extends Controller
{
    public function __construct(
        private readonly CommercePaymentProofService $proofService = new CommercePaymentProofService(),
        private readonly CartService $cartService = new CartService(),
        private readonly CommerceOrderService $orderService = new CommerceOrderService(),
    ) {}

    // ─────────────────────────────────────────────────────────
    // Auth Helper
    // ─────────────────────────────────────────────────────────

    private function customer(): GlobalCustomer
    {
        $customer = Auth::guard('customer')->user();
        if ($customer instanceof \App\Models\Customer) {
            return GlobalCustomer::firstOrCreate(
                ['phone' => $customer->phone],
                [
                    'name'              => $customer->name,
                    'email'             => $customer->email,
                    'shipping_address'  => $customer->shipping_address,
                    'phone_verified_at' => now(),
                ]
            );
        }

        /** @var GlobalCustomer $customer */
        return $customer;
    }

    private function scopeCustomerOrders(\Illuminate\Database\Eloquent\Builder $query, GlobalCustomer $customer): \Illuminate\Database\Eloquent\Builder
    {
        $hasPhone = !empty($customer->phone);
        $hasEmail = !empty($customer->email);

        if (!$hasPhone && !$hasEmail) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($q) use ($customer, $hasPhone, $hasEmail) {
            if ($hasPhone) {
                $q->where('customer_phone', $customer->phone);
                if (str_starts_with($customer->phone, '62')) {
                    $q->orWhere('customer_phone', '0' . substr($customer->phone, 2));
                } elseif (str_starts_with($customer->phone, '0')) {
                    $q->orWhere('customer_phone', '62' . substr($customer->phone, 1));
                }
            }
            if ($hasEmail) {
                if ($hasPhone) {
                    $q->orWhere('customer_email', $customer->email);
                } else {
                    $q->where('customer_email', $customer->email);
                }
            }
        });
    }

    // ─────────────────────────────────────────────────────────
    // Dashboard
    // ─────────────────────────────────────────────────────────

    public function dashboard(): View
    {
        $customer = $this->customer();

        // Cross-store order query via phone or email (supports both 62 and 0 phone formats)
        $ordersQuery = $this->scopeCustomerOrders(CommerceOrder::query(), $customer);

        $recentOrders = (clone $ordersQuery)
            ->with(['business', 'items', 'latestProof'])
            ->latest()
            ->take(5)
            ->get();

        $crmCustomer = $customer->phone
            ? \App\Models\Customer::where('phone', $customer->phone)
            ->orWhere('phone', str_starts_with($customer->phone, '62') ? '0' . substr($customer->phone, 2) : '62' . substr($customer->phone, 1))
            ->first()
            : null;

        $stats = [
            'total_orders'    => (clone $ordersQuery)->count(),
            'pending_payment' => (clone $ordersQuery)->where('status', CommerceOrder::STATUS_PENDING_PAYMENT)->count(),
            'processing'      => (clone $ordersQuery)->whereIn('status', [
                CommerceOrder::STATUS_PROOF_SUBMITTED,
                CommerceOrder::STATUS_PAID,
                CommerceOrder::STATUS_PROCESSING,
                CommerceOrder::STATUS_READY,
            ])->count(),
            'completed'       => (clone $ordersQuery)->where('status', CommerceOrder::STATUS_COMPLETED)->count(),
            'membership_tier' => $crmCustomer?->membership_tier ?? 'Bronze',
            'points_balance'  => (int) ($crmCustomer?->points_balance ?? 0),
            'total_spent'     => (float) (clone $ordersQuery)->where('status', CommerceOrder::STATUS_COMPLETED)->sum('total_amount'),
        ];

        // Cart item count across all stores
        $cartCount = CustomerCart::where('global_customer_id', $customer->id)
            ->withCount('items')
            ->get()
            ->sum('items_count');

        return view('customer.dashboard', compact('customer', 'stats', 'recentOrders', 'cartCount'));
    }

    // ─────────────────────────────────────────────────────────
    // Orders
    // ─────────────────────────────────────────────────────────

    public function orders(Request $request): View
    {
        $customer = $this->customer();
        $status   = $request->query('status', 'all');
        $search   = trim((string) $request->query('q', ''));

        $baseQuery = $this->scopeCustomerOrders(CommerceOrder::query(), $customer);

        $statusCounts = [
            'all'             => (clone $baseQuery)->count(),
            'pending_payment' => (clone $baseQuery)->where('status', CommerceOrder::STATUS_PENDING_PAYMENT)->count(),
            'verifying'       => (clone $baseQuery)->where('status', CommerceOrder::STATUS_PROOF_SUBMITTED)->count(),
            'processing'      => (clone $baseQuery)->whereIn('status', [
                CommerceOrder::STATUS_PAID,
                CommerceOrder::STATUS_PROCESSING,
                CommerceOrder::STATUS_READY,
            ])->count(),
            'completed'       => (clone $baseQuery)->where('status', CommerceOrder::STATUS_COMPLETED)->count(),
            'cancelled'       => (clone $baseQuery)->whereIn('status', [
                CommerceOrder::STATUS_CANCELLED,
                CommerceOrder::STATUS_PAYMENT_REJECTED,
                CommerceOrder::STATUS_EXPIRED,
            ])->count(),
        ];

        $ordersQuery = clone $baseQuery;

        match ($status) {
            'pending_payment' => $ordersQuery->where('status', CommerceOrder::STATUS_PENDING_PAYMENT),
            'verifying'       => $ordersQuery->where('status', CommerceOrder::STATUS_PROOF_SUBMITTED),
            'processing'      => $ordersQuery->whereIn('status', [CommerceOrder::STATUS_PAID, CommerceOrder::STATUS_PROCESSING, CommerceOrder::STATUS_READY]),
            'completed'       => $ordersQuery->where('status', CommerceOrder::STATUS_COMPLETED),
            'cancelled'       => $ordersQuery->whereIn('status', [CommerceOrder::STATUS_CANCELLED, CommerceOrder::STATUS_PAYMENT_REJECTED, CommerceOrder::STATUS_EXPIRED]),
            default           => null,
        };

        if ($search !== '') {
            $ordersQuery->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('items', fn($iq) => $iq->where('product_name', 'like', "%{$search}%"))
                    ->orWhereHas('business', fn($bq) => $bq->where('name', 'like', "%{$search}%"));
            });
        }

        $orders = $ordersQuery
            ->with(['business', 'items.product', 'paymentMethod', 'latestProof'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('customer.orders.index', compact('customer', 'orders', 'statusCounts', 'status', 'search'));
    }

    public function orderDetail(string $id): View
    {
        $customer = $this->customer();

        $order = $this->scopeCustomerOrders(CommerceOrder::query(), $customer)
            ->where(fn($q) => $q->where('id', $id)->orWhere('order_number', $id))
            ->with(['business', 'items.product', 'paymentMethod', 'paymentProofs.verifier'])
            ->firstOrFail();

        return view('customer.orders.show', compact('customer', 'order'));
    }

    public function uploadProof(Request $request, string $id): RedirectResponse
    {
        $customer = $this->customer();

        $order = $this->scopeCustomerOrders(CommerceOrder::query(), $customer)
            ->where('id', $id)
            ->firstOrFail();

        $request->validate([
            'payment_proof'       => ['required', 'file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:5120'],
            'sender_bank'         => ['nullable', 'string', 'max:100'],
            'sender_account_name' => ['nullable', 'string', 'max:150'],
        ]);

        try {
            $this->proofService->submitProof(
                order: $order,
                file: $request->file('payment_proof'),
                senderBank: $request->input('sender_bank'),
                senderAccountName: $request->input('sender_account_name')
            );
            return back()->with('success', 'Bukti pembayaran berhasil dikirim! Toko akan segera memverifikasi.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────
    // Store Browser
    // ─────────────────────────────────────────────────────────

    /**
     * List semua toko aktif yang memiliki commerce storefront.
     */
    public function stores(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $storesQuery = Business::where('is_active', true)
            ->whereHas('commerceStoreSetting')
            ->with('commerceStoreSetting');

        if ($search !== '') {
            $storesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('industry', 'like', "%{$search}%");
            });
        }

        $stores = $storesQuery->paginate(12)->withQueryString();

        return view('customer.stores.index', compact('stores', 'search'));
    }

    /**
     * Produk-produk satu toko, bisa langsung add to cart.
     */
    public function storeProducts(Request $request, string $slug): View
    {
        $business = Business::where('slug', $slug)
            ->where('is_active', true)
            ->whereHas('commerceStoreSetting')
            ->with('commerceStoreSetting')
            ->firstOrFail();

        $search   = trim((string) $request->query('q', ''));
        $category = $request->query('category', '');

        $productsQuery = Product::where('business_id', $business->id)
            ->where('is_active', true)
            ->with('category');

        if ($search !== '') {
            $productsQuery->where('name', 'like', "%{$search}%");
        }

        if ($category !== '') {
            $productsQuery->whereHas('category', fn($q) => $q->where('slug', $category));
        }

        $products   = $productsQuery->paginate(16)->withQueryString();
        $categories = Product::where('business_id', $business->id)
            ->where('is_active', true)
            ->with('category')
            ->get()
            ->pluck('category')
            ->filter()
            ->unique('id');

        // Load customer existing cart for this store (for badge/qty display)
        $customer    = $this->customer();
        $cart        = CustomerCart::where('global_customer_id', $customer->id)
            ->where('business_id', $business->id)
            ->with('items')
            ->first();

        return view('customer.stores.show', compact('business', 'products', 'categories', 'cart', 'search', 'category'));
    }

    // ─────────────────────────────────────────────────────────
    // Cart (Shopee-style: grouped per toko)
    // ─────────────────────────────────────────────────────────

    /**
     * Tampilkan semua cart customer, grouped per toko.
     */
    public function cart(): View
    {
        $customer = $this->customer();

        // Load semua cart dengan items + product + business, hanya yang punya items
        $carts = CustomerCart::where('global_customer_id', $customer->id)
            ->with(['items.product', 'business.commerceStoreSetting'])
            ->whereHas('items')
            ->get();

        $grandTotal = $carts->sum(fn($c) => $c->items->sum(fn($i) => $i->quantity * $i->unit_price));

        return view('customer.cart', compact('customer', 'carts', 'grandTotal'));
    }

    /**
     * AJAX/Form: Tambah produk ke cart toko.
     */
    public function addToCart(Request $request, string $slug): JsonResponse|RedirectResponse
    {
        $request->validate([
            'product_id' => ['required', 'uuid', 'exists:products,id'],
            'quantity'   => ['nullable', 'numeric', 'min:0.1'],
            'notes'      => ['nullable', 'string', 'max:200'],
        ]);

        $business = Business::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $product  = Product::where('id', $request->input('product_id'))
            ->where('business_id', $business->id)
            ->where('is_active', true)
            ->firstOrFail();

        $customer = $this->customer();
        $cart     = $this->cartService->getOrCreate($customer, $business);

        $this->cartService->addItem(
            cart: $cart,
            product: $product,
            quantity: (float) ($request->input('quantity', 1)),
            notes: $request->input('notes'),
        );

        if ($request->expectsJson()) {
            $cart->load('items');
            return response()->json([
                'success'    => true,
                'message'    => "{$product->name} ditambahkan ke cart.",
                'cart_count' => $cart->items->sum('quantity'),
            ]);
        }

        return back()->with('success', "{$product->name} ditambahkan ke cart.");
    }

    /**
     * AJAX/Form: Update qty item.
     */
    public function updateCartItem(Request $request, string $slug, string $item): JsonResponse|RedirectResponse
    {
        $request->validate(['quantity' => ['required', 'numeric', 'min:0']]);

        $cartItem = CustomerCartItem::whereHas('cart', function ($q) use ($slug) {
            $q->whereHas('business', fn($bq) => $bq->where('slug', $slug))
                ->where('global_customer_id', $this->customer()->id);
        })->findOrFail($item);

        $updated = $this->cartService->updateItem($cartItem, (float) $request->input('quantity'));

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'removed' => $updated === null]);
        }

        return redirect()->route('customer.cart');
    }

    /**
     * AJAX/Form: Hapus item dari cart.
     */
    public function removeCartItem(Request $request, string $slug, string $item): JsonResponse|RedirectResponse
    {
        $cartItem = CustomerCartItem::whereHas('cart', function ($q) use ($slug) {
            $q->whereHas('business', fn($bq) => $bq->where('slug', $slug))
                ->where('global_customer_id', $this->customer()->id);
        })->findOrFail($item);

        $this->cartService->removeItem($cartItem);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('customer.cart')->with('info', 'Item dihapus dari cart.');
    }

    /**
     * Checkout cart ke toko tertentu.
     * Meneruskan ke PublicOrderTrackingController flow yang sudah ada.
     */
    public function checkout(Request $request, string $slug): RedirectResponse
    {
        $business = Business::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $customer = $this->customer();

        $cart = CustomerCart::where('global_customer_id', $customer->id)
            ->where('business_id', $business->id)
            ->with(['items.product'])
            ->first();

        if (! $cart || $cart->items->isEmpty()) {
            return redirect()->route('customer.cart')->with('error', 'Cart toko ini kosong.');
        }

        // Redirect ke storefront checkout page toko dengan cart pre-filled
        // via session - storefront sudah handle payment method, alamat, dsb.
        session()->put("customer_cart_checkout_{$business->id}", $cart->id);

        return redirect()->route('public.business.landing.legacy', ['slug' => $slug])
            ->with('info', 'Lanjutkan proses checkout di bawah.');
    }

    // ─────────────────────────────────────────────────────────
    // Profile
    // ─────────────────────────────────────────────────────────

    public function profile(): View
    {
        return view('customer.profile', ['customer' => $this->customer()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $customer = $this->customer();

        $validated = $request->validate([
            'name'             => ['required', 'string', 'min:2', 'max:150'],
            'phone'            => ['required', 'string', 'min:8', 'max:30'],
            'email'            => ['nullable', 'email', 'max:150'],
            'shipping_address' => ['nullable', 'string', 'max:500'],
            'new_password'     => ['nullable', 'string', 'min:6', 'confirmed'],
            'current_password' => ['nullable', 'required_with:new_password', 'string'],
        ]);

        if (! empty($validated['new_password'])) {
            if ($customer->password && ! Hash::check((string) $validated['current_password'], (string) $customer->password)) {
                return back()->withErrors(['current_password' => 'Kata sandi saat ini tidak cocok.']);
            }
            $customer->password = Hash::make($validated['new_password']);
        }

        if (! empty($validated['email'])) {
            $customer->email = $validated['email'];
        }

        $phoneChanged = $customer->phone !== $validated['phone'];
        if ($phoneChanged) {
            $customer->phone = $validated['phone'];
            $customer->phone_verified_at = null;
            $request->session()->forget('customer_otp_challenge');
        }

        $customer->name             = $validated['name'];
        $customer->shipping_address = $validated['shipping_address'] ?? $customer->shipping_address;
        $customer->save();

        // Sync CRM Customer record with same phone
        if ($customer->phone) {
            \App\Models\Customer::where('phone', $customer->phone)
                ->orWhere('phone', str_starts_with($customer->phone, '62') ? '0' . substr($customer->phone, 2) : '62' . substr($customer->phone, 1))
                ->update([
                    'name'             => $customer->name,
                    'email'            => $customer->email,
                    'shipping_address' => $customer->shipping_address,
                    'is_active'        => true,
                ]);
        }

        if ($phoneChanged) {
            return redirect()->route('customer.otp')->with('info', 'Nomor WhatsApp berhasil diubah. Silakan verifikasi nomor baru Anda.');
        }

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    // ---------------------------------------------------------
    // Profile Completion Gate
    // ---------------------------------------------------------

    /**
     * Show profile completion form (phone + address required before OTP).
     */
    public function profileComplete(): View
    {
        return view('customer.profile.complete', ['customer' => $this->customer()]);
    }

    /**
     * Save the phone (and optional address), then redirect to OTP.
     */
    public function storeProfileComplete(Request $request): RedirectResponse
    {
        $customer = $this->customer();

        $validated = $request->validate([
            'phone'            => ['required', 'string', 'min:8', 'max:30'],
            'shipping_address' => ['nullable', 'string', 'max:500'],
        ], [
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
            'phone.min'      => 'Nomor WhatsApp tidak valid.',
        ]);

        $phone = preg_replace('/\D+/', '', $validated['phone']);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (str_starts_with($phone, '8')) {
            $phone = '62' . $phone;
        }

        // If phone changed, reset phone verification so OTP is re-triggered
        if ($customer->phone !== $phone) {
            $customer->phone_verified_at = null;
            $request->session()->forget('customer_otp_challenge');
        }

        $customer->phone            = $phone;
        $customer->shipping_address = $validated['shipping_address'] ?? $customer->shipping_address;
        $customer->save();

        // Sync to CRM Customer records
        \App\Models\Customer::where('phone', $phone)
            ->whereNull('password')
            ->update(['name' => $customer->name, 'email' => $customer->email, 'is_active' => true]);

        $intended = session()->pull('url.intended', route('customer.otp'));

        // If phone not verified yet, go through OTP
        if (! $customer->isPhoneVerified()) {
            session()->put('url.intended', $intended);
            return redirect()->route('customer.otp')
                ->with('status', 'Nomor tersimpan. Verifikasi WhatsApp diperlukan.');
        }

        return redirect($intended)->with('success', 'Profil berhasil diperbarui.');
    }
}
