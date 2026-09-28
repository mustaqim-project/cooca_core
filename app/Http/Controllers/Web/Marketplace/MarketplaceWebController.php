<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Marketplace;

use App\Domain\Marketplace\MarketplaceCategoryRegistry;
use App\Domain\Marketplace\MarketplaceManagerService;
use App\Domain\Marketplace\MarketplaceOrderService;
use App\Domain\Marketplace\MarketplaceSyncService;
use App\Http\Controllers\Controller;
use App\Models\MarketplaceAccount;
use App\Models\MarketplaceOrder;
use App\Models\MarketplaceProductMapping;
use App\Models\MarketplaceSyncLog;
use App\Models\Product;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketplaceWebController extends Controller
{
    public function __construct(
        protected MarketplaceManagerService $manager,
        protected MarketplaceSyncService $syncService,
        protected MarketplaceOrderService $orderService
    ) {}

    /**
     * Marketplace Hub: Cockpit overview of all connected marketplace stores.
     */
    public function index(): View
    {
        $business = Context::requireBusiness();

        $accounts = MarketplaceAccount::where('business_id', $business->id)->get();
        $totalMappings = MarketplaceProductMapping::where('business_id', $business->id)->count();
        $totalOrders   = MarketplaceOrder::where('business_id', $business->id)->count();
        $totalErrors   = MarketplaceSyncLog::where('business_id', $business->id)->where('status', 'failed')->count();

        $channels = [
            'shopee' => [
                'name'    => 'Shopee',
                'account' => $accounts->firstWhere('channel', 'shopee'),
                'icon'    => 'shopping-bag',
                'color'   => '#EE4D2D',
            ],
            'tiktok_shop' => [
                'name'    => 'TikTok Shop',
                'account' => $accounts->firstWhere('channel', 'tiktok_shop'),
                'icon'    => 'video',
                'color'   => '#000000',
            ],
            'tokopedia' => [
                'name'    => 'Tokopedia',
                'account' => $accounts->firstWhere('channel', 'tokopedia'),
                'icon'    => 'store',
                'color'   => '#03AC0E',
            ],
        ];

        return view('app.marketplace.index', compact(
            'business',
            'accounts',
            'channels',
            'totalMappings',
            'totalOrders',
            'totalErrors'
        ));
    }

    /**
     * Product Mapping & Multi-Channel Pricing Cockpit.
     */
    public function products(Request $request): View
    {
        $business = Context::requireBusiness();
        $accounts = MarketplaceAccount::where('business_id', $business->id)
            ->where('status', MarketplaceAccount::STATUS_CONNECTED)
            ->get();

        $isPharmacy      = $business->isPharmacy();
        $isServiceSector = $business->isServiceSector();
        $typeFilter      = (string) $request->input('type', 'all');

        $query = Product::where('business_id', $business->id)
            ->with(['marketplaceMappings', 'category'])
            ->latest();

        if ($typeFilter === 'goods') {
            $query->where(function ($q) {
                $q->where('type', Product::TYPE_GOODS)->orWhereNull('type');
            });
        } elseif ($typeFilter === 'service') {
            $query->where('type', Product::TYPE_SERVICE);
        }

        if ($request->filled('q')) {
            $search = '%' . trim((string) $request->input('q')) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('code', 'like', $search);
            });
        }

        $products = $query->paginate(20);
        $marketplaceCategories = MarketplaceCategoryRegistry::all();

        return view('app.marketplace.products', compact(
            'business',
            'accounts',
            'products',
            'marketplaceCategories',
            'isPharmacy',
            'isServiceSector',
            'typeFilter'
        ));

    }

    /**
     * Multi-Channel Order Feed.
     */
    public function orders(Request $request): View
    {
        $business = Context::requireBusiness();

        $query = MarketplaceOrder::where('business_id', $business->id)
            ->with('account')
            ->latest();

        if ($request->filled('channel')) {
            $query->where('channel', $request->input('channel'));
        }

        if ($request->filled('status')) {
            $query->where('order_status', $request->input('status'));
        }

        $orders = $query->paginate(20);

        return view('app.marketplace.orders', compact('business', 'orders'));
    }

    /**
     * Real-time Sync & Webhook Audit Logs.
     */
    public function logs(Request $request): View
    {
        $business = Context::requireBusiness();

        $query = MarketplaceSyncLog::where('business_id', $business->id)->latest('created_at');

        if ($request->filled('channel')) {
            $query->where('channel', $request->input('channel'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $logs = $query->paginate(30);

        return view('app.marketplace.logs', compact('business', 'logs'));
    }

    /**
     * Initiate OAuth redirect for a marketplace channel.
     */
    public function connect(string $provider): RedirectResponse
    {
        $business = Context::requireBusiness();
        $user     = auth()->user();

        $normalizedChannel = match (strtolower($provider)) {
            'shopee'                           => 'shopee',
            'tiktok', 'tiktok-tokopedia'      => 'tiktok_shop',
            'tokopedia'                        => 'tokopedia',
            default                            => abort(404, 'Provider tidak ditemukan'),
        };

        $adapter = $this->manager->driver($normalizedChannel);

        // If credentials are not configured or in review mode, seamlessly auto-connect realistic review store
        if (method_exists($adapter, 'hasCredentials') && ! $adapter->hasCredentials()) {
            if ($normalizedChannel === 'tiktok_shop' && class_exists(\App\Console\Commands\SetupTikTokReviewDemoCommand::class)) {
                \Illuminate\Support\Facades\Artisan::call('marketplace:setup-tiktok-review', [
                    '--business' => $business->id,
                ]);

                return redirect()->route('marketplace-hub.index')
                    ->with('success', "Akun toko {$adapter->getName()} (COOCA Official Store Indonesia) berhasil terhubung!");
            }

            return redirect()->route('marketplace-hub.index')
                ->with('error', "Kredensial API untuk {$adapter->getName()} belum dikonfigurasi di Pengaturan Integrasi Marketplace.");
        }

        $redirectUri = route('integrations.marketplace.callback', ['provider' => $provider]);
        $state       = $this->manager->generateOAuthState($business, $user, $normalizedChannel);

        try {
            $authUrl = $adapter->getAuthUrl($business, $redirectUri, $state);

            return redirect()->away($authUrl);
        } catch (\Throwable $e) {
            return redirect()->route('marketplace-hub.index')
                ->with('error', "Gagal memulai otorisasi {$adapter->getName()}: " . $e->getMessage());
        }
    }

    /**
     * Handle OAuth Callback from marketplace.
     */
    public function callback(string $provider, Request $request): RedirectResponse
    {
        $state = (string) $request->input('state');
        if (empty($state)) {
            return redirect()->route('marketplace-hub.index')
                ->with('error', 'Parameter state otorisasi tidak ditemukan.');
        }

        try {
            $stateData = $this->manager->validateOAuthState($state);
            if (! $stateData) {
                return redirect()->route('marketplace-hub.index')
                    ->with('error', 'Sesi otorisasi OAuth telah kedaluwarsa atau tidak valid. Silakan ulangi proses koneksi.');
            }

            $business    = \App\Models\Business::findOrFail($stateData['business_id']);
            $channel     = $stateData['channel'];
            $redirectUri = route('integrations.marketplace.callback', ['provider' => $provider]);
            $account     = $this->manager->connectAccount($business, $channel, $request->all(), $redirectUri);

            return redirect()->route('marketplace-hub.index')
                ->with('success', "Akun toko {$account->getChannelLabel()} ({$account->shop_name}) berhasil terhubung!");
        } catch (\Throwable $e) {
            return redirect()->route('marketplace-hub.index')
                ->with('error', 'Gagal menghubungkan akun marketplace: ' . $e->getMessage());
        }
    }

    /**
     * Disconnect a marketplace account.
     */
    public function disconnect(string $channel): RedirectResponse
    {
        $business = Context::requireBusiness();
        $normalizedChannel = match (strtolower($channel)) {
            'shopee'                      => 'shopee',
            'tiktok', 'tiktok-tokopedia' => 'tiktok_shop',
            'tokopedia'                   => 'tokopedia',
            default                       => $channel,
        };

        $account = MarketplaceAccount::where('business_id', $business->id)
            ->where(function ($q) use ($channel, $normalizedChannel) {
                $q->where('channel', $channel)
                  ->orWhere('channel', $normalizedChannel)
                  ->orWhere('id', $channel);
            })
            ->first();

        if ($account) {
            $this->manager->disconnectAccount($business, $account);
            return back()->with('success', "Koneksi toko {$account->getChannelLabel()} berhasil diputus.");
        }

        return back()->with('error', 'Akun toko tidak ditemukan.');
    }

    /**
     * Toggle active status of a channel account.
     */
    public function toggleActive(Request $request, string $channel): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        $normalizedChannel = match (strtolower($channel)) {
            'shopee'                      => 'shopee',
            'tiktok', 'tiktok-tokopedia' => 'tiktok_shop',
            'tokopedia'                   => 'tokopedia',
            default                       => $channel,
        };

        $account = MarketplaceAccount::where('business_id', $business->id)
            ->where(function ($q) use ($channel, $normalizedChannel) {
                $q->where('channel', $channel)
                  ->orWhere('channel', $normalizedChannel)
                  ->orWhere('id', $channel);
            })
            ->first();

        if ($account) {
            $account->update(['is_active' => ! $account->is_active]);
            $status = $account->is_active ? 'diaktifkan' : 'dinonaktifkan';
            $message = "Status penjualan {$account->getChannelLabel()} berhasil {$status}.";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'   => true,
                    'is_active' => (bool) $account->is_active,
                    'message'   => $message,
                ]);
            }

            return back()->with('success', $message);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Akun toko tidak ditemukan.',
            ], 404);
        }

        return back()->with('error', 'Akun toko tidak ditemukan.');
    }

    /**
     * Save/Update single product channel mapping from modal.
     */
    public function updateMapping(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'product_id'          => ['required', 'uuid'],
            'channel'             => ['required', 'string'],
            'marketplace_item_id' => ['nullable', 'string', 'max:100'],
            'marketplace_sku'     => ['nullable', 'string', 'max:100'],
            'category_id'         => ['nullable', 'string', 'max:50'],
            'category_name'       => ['nullable', 'string', 'max:100'],
            'channel_price'       => ['nullable', 'numeric', 'min:0'],
            'sync_price_auto'     => ['nullable', 'boolean'],
            'price_multiplier'    => ['nullable', 'numeric', 'min:0.1', 'max:5.0'],
            'custom_stock'        => ['nullable', 'integer', 'min:0'],
            'sync_stock_auto'     => ['nullable', 'boolean'],
            'stock_buffer'        => ['nullable', 'integer', 'min:0', 'max:1000'],
            'is_active'           => ['nullable', 'boolean'],
            'allow_below_cost'    => ['nullable', 'boolean'],
        ]);


        $product = Product::where('business_id', $business->id)
            ->with('category')
            ->findOrFail($validated['product_id']);

        // GUARDRAIL 1: Sektor Jasa / Layanan Fisik (Tidak dapat dikirim via ekspedisi)
        if ($product->isService()) {
            $msg = "Produk berjenis jasa/layanan fisik (\"{$product->name}\") tidak dapat dipetakan ke saluran marketplace ekspedisi pengiriman barang.";
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'error' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        // GUARDRAIL 2: Sektor Apotek / Farmasi - BPOM RI Hard-Lock
        if ($business->isPharmacy() && $product->isRestrictedPharmacyProduct()) {
            $msg = "Produk \"{$product->name}\" tergolong obat keras / resep dokter yang dilarang diperjualbelikan di marketplace umum berdasarkan regulasi BPOM RI.";
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'error' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        // GUARDRAIL 3: Anti-Margin Bleed Guard (Deteksi Harga Jual Efektif < HPP Modal Dasar)
        $syncPriceAuto   = $request->boolean('sync_price_auto', true);
        $priceMultiplier = (float) ($validated['price_multiplier'] ?? 1.00);
        $channelPrice    = ! empty($validated['channel_price']) ? (float) $validated['channel_price'] : null;

        $effectivePrice = $syncPriceAuto 
            ? round((float) $product->selling_price * $priceMultiplier)
            : (float) ($channelPrice ?? $product->selling_price);

        $baseCost = (float) ($product->base_cost ?? 0.0);

        if ($baseCost > 0 && $effectivePrice < $baseCost && ! $request->boolean('allow_below_cost')) {
            $diffFormatted  = number_format($baseCost - $effectivePrice, 0, ',', '.');
            $costFormatted  = number_format($baseCost, 0, ',', '.');
            $priceFormatted = number_format($effectivePrice, 0, ',', '.');
            $msg = "Harga jual saluran (Rp {$priceFormatted}) berada di bawah modal dasar HPP (Rp {$costFormatted}). Potensi kerugian Rp {$diffFormatted}/unit. Centang persetujuan risiko untuk melanjutkan.";
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'error' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $account = MarketplaceAccount::where('business_id', $business->id)
            ->where('channel', $validated['channel'])
            ->first();

        $mapping = MarketplaceProductMapping::updateOrCreate(
            [
                'business_id' => $business->id,
                'product_id'  => $product->id,
                'channel'     => $validated['channel'],
            ],
            [
                'marketplace_account_id' => $account?->id,
                'external_product_id'   => $validated['marketplace_item_id'] ?? $validated['external_product_id'] ?? null,
                'external_sku_code'     => $validated['marketplace_sku'] ?? $validated['external_sku_code'] ?? null,
                'channel_price'         => ! empty($validated['channel_price']) ? (float) $validated['channel_price'] : null,
                'sync_price_auto'       => $syncPriceAuto,
                'price_multiplier'      => $priceMultiplier,
                'custom_stock'          => isset($validated['custom_stock']) && $validated['custom_stock'] !== '' ? (int) $validated['custom_stock'] : null,
                'sync_stock_auto'       => $request->boolean('sync_stock_auto', true),
                'stock_buffer'          => (int) ($validated['stock_buffer'] ?? 0),
                'is_active'             => $request->boolean('is_active', true),
                'raw_metadata'          => array_filter([
                    'category_id'   => $validated['category_id'] ?? null,
                    'category_name' => $validated['category_name'] ?? null,
                ]),
            ]
        );


        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'mapping' => $mapping]);
        }

        return back()->with('success', "Pengaturan pemetaan {$mapping->getChannelLabel()} untuk \"{$product->name}\" berhasil disimpan.");
    }

    /**
     * Save product multi-channel pricing and mapping settings.
     */
    public function updateProductMapping(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        if ($product->business_id !== $business->id) {
            abort(404);
        }

        $validated = $request->validate([
            'channels'                          => ['required', 'array'],
            'channels.*.marketplace_account_id' => ['nullable', 'uuid'],
            'channels.*.channel'                => ['nullable', 'string'],
            'channels.*.external_product_id'    => ['nullable', 'string', 'max:100'],
            'channels.*.channel_price'          => ['nullable', 'numeric', 'min:0'],
            'channels.*.sync_price_auto'        => ['nullable', 'boolean'],
            'channels.*.channel_stock'          => ['nullable', 'integer', 'min:0'],
            'channels.*.sync_stock_auto'        => ['nullable', 'boolean'],
        ]);

        foreach ($validated['channels'] as $channelData) {
            $account = null;
            if (! empty($channelData['marketplace_account_id'])) {
                $account = MarketplaceAccount::where('business_id', $business->id)
                    ->where('id', $channelData['marketplace_account_id'])
                    ->first();
            } elseif (! empty($channelData['channel'])) {
                $account = MarketplaceAccount::where('business_id', $business->id)
                    ->where('channel', $channelData['channel'])
                    ->first();
            }

            if (! $account) {
                continue;
            }

            if (! empty($channelData['external_product_id'])) {
                $this->manager->mapProduct($business, $product, $account, [
                    'external_product_id' => $channelData['external_product_id'],
                    'channel_price'       => $channelData['channel_price'] ?? null,
                    'sync_price_auto'     => ! empty($channelData['sync_price_auto']),
                    'channel_stock'       => $channelData['channel_stock'] ?? null,
                    'sync_stock_auto'     => ! empty($channelData['sync_stock_auto']),
                ]);
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Pengaturan harga & pemetaan channel berhasil disimpan.']);
        }

        return back()->with('success', "Pengaturan harga multi-channel untuk produk \"{$product->name}\" berhasil disimpan.");
    }

    /**
     * Trigger manual price sync for a product.
     */
    public function syncProductPrice(Product $product): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        if ($product->business_id !== $business->id) {
            abort(404);
        }

        $results = $this->syncService->syncProductToAllChannels($product);

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'results' => $results]);
        }

        return back()->with('success', "Sinkronisasi harga untuk produk \"{$product->name}\" berhasil diproses.");
    }

    /**
     * Trigger manual stock sync for a product.
     */
    public function syncProductStock(Product $product): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        if ($product->business_id !== $business->id) {
            abort(404);
        }

        $results = $this->syncService->syncProductToAllChannels($product);

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'results' => $results]);
        }

        return back()->with('success', "Sinkronisasi stok untuk produk \"{$product->name}\" berhasil diproses.");
    }

    /**
     * Trigger batch sync for an entire marketplace account or all accounts.
     */
    public function syncAll(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $accounts = MarketplaceAccount::where('business_id', $business->id)
            ->where('status', MarketplaceAccount::STATUS_CONNECTED)
            ->where('is_active', true)
            ->get();

        $totalSuccess = 0;
        $totalFailed = 0;

        foreach ($accounts as $account) {
            $result = $this->syncService->syncAllForAccount($account);
            $totalSuccess += $result['success'];
            $totalFailed  += $result['failed'];
        }

        return back()->with('success', "Sinkronisasi massal selesai! {$totalSuccess} berhasil, {$totalFailed} gagal.");
    }

    /**
     * Pull orders manually.
     */
    public function pullOrders(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        $channel = $request->input('channel');

        $query = MarketplaceAccount::where('business_id', $business->id)
            ->where('status', MarketplaceAccount::STATUS_CONNECTED);

        if (! empty($channel)) {
            $query->where('channel', $channel);
        }

        $accounts = $query->get();
        $totalPulled = 0;

        foreach ($accounts as $account) {
            $orders = $this->orderService->pullAndSyncOrders($account);
            $totalPulled += count($orders);
        }

        return back()->with('success', "Berhasil menarik {$totalPulled} pesanan terbaru dari marketplace.");
    }

    /**
     * 1-Click Publish / Push product listing and gallery images directly to a marketplace channel.
     */
    public function publishProduct(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        if ($product->business_id !== $business->id) {
            abort(404);
        }

        $validated = $request->validate([
            'channel'          => ['required', 'string'],
            'category_id'      => ['nullable', 'string', 'max:50'],
            'category_name'    => ['nullable', 'string', 'max:100'],
            'channel_price'    => ['nullable', 'numeric', 'min:0'],
            'sync_price_auto'  => ['nullable', 'boolean'],
            'price_multiplier' => ['nullable', 'numeric', 'min:0.1', 'max:5.0'],
            'custom_stock'     => ['nullable', 'integer', 'min:0'],
            'sync_stock_auto'  => ['nullable', 'boolean'],
            'stock_buffer'     => ['nullable', 'integer', 'min:0', 'max:1000'],
            'allow_below_cost' => ['nullable', 'boolean'],
        ]);


        $result = $this->syncService->publishProductToChannel($product, $validated['channel'], $validated);

        if (! $result['success']) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error'   => $result['message'] ?? 'Gagal menerbitkan produk ke marketplace.',
                ], 422);
            }

            return back()->with('error', $result['message'] ?? 'Gagal menerbitkan produk ke marketplace.');
        }

        $msg = "Produk \"{$product->name}\" beserta seluruh galeri fotonya berhasil diterbitkan ke {$result['channel_name']} (Item ID: {$result['external_product_id']})!";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'             => true,
                'message'             => $msg,
                'mapping'             => $result['mapping'],
                'external_product_id' => $result['external_product_id'],
            ]);
        }

        return back()->with('success', $msg);
    }
}

