<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\MarketplaceAccount;
use App\Models\MarketplaceOrder;
use App\Models\MarketplaceProductMapping;
use App\Models\MarketplaceSyncLog;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SetupTikTokReviewDemoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marketplace:setup-tiktok-review
                            {--business= : Business ID or Slug (defaults to active/first business)}
                            {--shop-id=IDLSA982736192 : TikTok Shop ID / Open ID}
                            {--shop-name=COOCA Official Store Indonesia : Official TikTok Shop Name}
                            {--disconnect : Disconnect instead of connect}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up a connected TikTok Shop state with realistic Indonesian demo data for TikTok Partner App Review';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $businessQuery = $this->option('business');

        $businesses = collect();
        if ($businessQuery) {
            $found = Business::where('id', $businessQuery)
                ->orWhere('slug', $businessQuery)
                ->first();
            if ($found) {
                $businesses->push($found);
            }
        } else {
            $businesses = Business::where('is_active', true)->get();
            if ($businesses->isEmpty()) {
                $businesses = Business::all();
            }
        }

        if ($businesses->isEmpty()) {
            $this->error('Tidak ada data bisnis yang ditemukan dalam sistem.');
            return self::FAILURE;
        }

        $shopId   = (string) $this->option('shop-id');
        $shopName = (string) $this->option('shop-name');

        if ($this->option('disconnect')) {
            foreach ($businesses as $business) {
                $account = MarketplaceAccount::where('business_id', $business->id)
                    ->where('channel', MarketplaceAccount::CHANNEL_TIKTOK)
                    ->first();

                if ($account) {
                    $account->update([
                        'status'        => MarketplaceAccount::STATUS_DISCONNECTED,
                        'access_token'  => null,
                        'refresh_token' => null,
                    ]);
                    $this->info("TikTok Shop account for '{$business->name}' disconnected successfully.");
                }
            }
            return self::SUCCESS;
        }

        foreach ($businesses as $business) {
            $this->setupBusiness($business, $shopId, $shopName);
        }

        $this->newLine();
        $this->info('================================================================');
        $this->info('  TIKTOK SHOP PARTNER REVIEW DEMO STATE READY (SEMUA BISNIS)     ');
        $this->info('================================================================');
        $this->info("TikTok Shop:    {$shopName}");
        $this->info("Shop ID:        {$shopId}");
        $this->info("Status:         CONNECTED (Terhubung Aktif di " . $businesses->count() . " Bisnis)");
        $this->info("Hub URL:        " . url('/owner/marketplace-hub'));
        $this->info("Orders URL:     " . url('/owner/marketplace-hub/orders?channel=tiktok_shop'));
        $this->info("Products URL:   " . url('/owner/marketplace-hub/products?channel=tiktok_shop'));
        $this->newLine();

        return self::SUCCESS;
    }

    protected function setupBusiness(Business $business, string $shopId, string $shopName): void
    {

        // 1. Create or Update Connected TikTok Shop Account
        $account = MarketplaceAccount::updateOrCreate(
            [
                'business_id' => $business->id,
                'channel'     => MarketplaceAccount::CHANNEL_TIKTOK,
            ],
            [
                'shop_id'                  => $shopId,
                'shop_name'                => $shopName,
                'status'                   => MarketplaceAccount::STATUS_CONNECTED,
                'access_token'             => 'enc_tiktok_shop_token_review_' . Str::random(32),
                'refresh_token'            => 'enc_tiktok_shop_refresh_review_' . Str::random(32),
                'token_expires_at'         => now()->addYear(),
                'refresh_token_expires_at' => now()->addYears(2),
                'is_active'                => true,
                'auto_sync_stock'          => true,
                'auto_sync_price'          => true,
                'stock_buffer'             => 2,
                'price_multiplier'         => 1.08,
                'settings'                 => [
                    'shop_cipher'        => $shopId,
                    'seller_base_region' => 'ID',
                    'authorized_at'      => now()->toIso8601String(),
                ],
                'last_synced_at'           => now()->subMinutes(3),
                'error_message'            => null,
            ]
        );

        $this->info("✓ TikTok Shop account connected: {$account->shop_name} ({$account->shop_id}) for [{$business->name}]");

        // 2. Map existing physical products to TikTok Shop
        $products = Product::where('business_id', $business->id)
            ->where(function ($q) {
                $q->where('type', Product::TYPE_GOODS)->orWhereNull('type');
            })
            ->where('is_active', true)
            ->take(5)
            ->get();

        if ($products->isEmpty()) {
            $unit = \App\Models\Unit::where('business_id', $business->id)->first() ?? \App\Models\Unit::create([
                'business_id' => $business->id,
                'name'        => 'Pcs',
                'code'        => 'PCS',
                'category'    => \App\Models\Unit::CATEGORY_QUANTITY,
                'is_base'     => true,
            ]);

            $demoItems = [
                ['name' => 'Kopi Susu Gula Aren 250ml Botol', 'code' => 'KSGA-250', 'cost' => 12000, 'price' => 22000],
                ['name' => 'Biji Kopi Arabica Gayo 250gr Roasted', 'code' => 'BKAG-250', 'cost' => 45000, 'price' => 75000],
                ['name' => 'Tumbler Stainless Steel COOCA 500ml', 'code' => 'TMBL-500', 'cost' => 60000, 'price' => 110000],
            ];

            foreach ($demoItems as $item) {
                Product::create([
                    'business_id'    => $business->id,
                    'type'           => Product::TYPE_GOODS,
                    'output_unit_id' => $unit->id,
                    'code'           => $item['code'],
                    'name'           => $item['name'],
                    'base_cost'      => $item['cost'],
                    'selling_price'  => $item['price'],
                    'min_stock'      => 5,
                    'is_active'      => true,
                ]);
            }

            $products = Product::where('business_id', $business->id)
                ->where('type', Product::TYPE_GOODS)
                ->where('is_active', true)
                ->take(5)
                ->get();
        }

        $mappedCount = 0;
        foreach ($products as $idx => $prod) {
            $effectivePrice = round((float) $prod->selling_price * 1.08);

            MarketplaceProductMapping::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'product_id'  => $prod->id,
                    'channel'     => MarketplaceAccount::CHANNEL_TIKTOK,
                ],
                [
                    'marketplace_account_id' => $account->id,
                    'external_product_id'   => '172' . str_pad((string) ($idx + 1001), 10, '0', STR_PAD_RIGHT),
                    'external_sku_code'     => $prod->code ?: ('TTS-SKU-' . str_pad((string) ($idx + 1), 4, '0', STR_PAD_LEFT)),
                    'external_product_name' => $prod->name,
                    'channel_price'         => $effectivePrice,
                    'price_multiplier'      => 1.08,
                    'sync_price_auto'       => true,
                    'sync_stock_auto'       => true,
                    'stock_buffer'          => 2,
                    'is_active'             => true,
                    'sync_status'           => MarketplaceProductMapping::STATUS_SYNCED,
                    'last_price_synced_at'  => now()->subMinutes(10),
                    'last_stock_synced_at'  => now()->subMinutes(10),
                ]
            );
            $mappedCount++;
        }

        $this->info("✓ Mapped {$mappedCount} products to TikTok Shop with 1.08 multiplier and stock buffer 2.");

        // 3. Seed Realistic Indonesian TikTok Shop Orders
        $sampleOrders = [
            [
                'sn'       => '578910293847291021',
                'buyer'    => 'Rizky Ramadhani (TikTok Buyer)',
                'phone'    => '081298765432',
                'status'   => 'paid',
                'amount'   => 185000.0,
                'courier'  => 'J&T Express',
                'tracking' => 'JT88291039482',
                'time'     => now()->subHours(2),
            ],
            [
                'sn'       => '578910293847291022',
                'buyer'    => 'Siti Nurhaliza (TikTok Buyer)',
                'phone'    => '085712345678',
                'status'   => 'shipped',
                'amount'   => 245000.0,
                'courier'  => 'SiCepat REG',
                'tracking' => '002938472910',
                'time'     => now()->subHours(6),
            ],
            [
                'sn'       => '578910293847291023',
                'buyer'    => 'Budi Prasetyo (TikTok Buyer)',
                'phone'    => '081399887766',
                'status'   => 'completed',
                'amount'   => 120000.0,
                'courier'  => 'J&T Express',
                'tracking' => 'JT88291039489',
                'time'     => now()->subDays(1),
            ],
        ];

        foreach ($sampleOrders as $so) {
            MarketplaceOrder::updateOrCreate(
                [
                    'business_id'       => $business->id,
                    'external_order_sn' => $so['sn'],
                ],
                [
                    'marketplace_account_id' => $account->id,
                    'channel'                => MarketplaceAccount::CHANNEL_TIKTOK,
                    'external_order_id'      => $so['sn'],
                    'buyer_name'             => $so['buyer'],
                    'buyer_phone'            => $so['phone'],
                    'total_amount'           => $so['amount'],
                    'channel_fee'            => round($so['amount'] * 0.08),
                    'order_status'           => $so['status'],
                    'shipping_provider'      => $so['courier'],
                    'tracking_number'        => $so['tracking'],
                    'items_summary'          => [
                        [
                            'product_name' => $products->first()?->name ?? 'Produk Fashion COOCA',
                            'quantity'     => 1,
                            'price'        => $so['amount'],
                        ],
                    ],
                    'placed_at'              => $so['time'],
                    'synced_at'              => now(),
                ]
            );
        }

        $this->info("✓ Seeded 3 realistic TikTok Shop Indonesia orders.");

        // 4. Seed Audit Sync Logs
        MarketplaceSyncLog::log(
            businessId: $business->id,
            channel: MarketplaceAccount::CHANNEL_TIKTOK,
            entityType: 'auth',
            action: 'connect_account',
            status: 'success',
            entityId: $account->id,
            payload: ['shop_id' => $account->shop_id, 'shop_name' => $account->shop_name],
            marketplaceAccountId: $account->id
        );

        MarketplaceSyncLog::log(
            businessId: $business->id,
            channel: MarketplaceAccount::CHANNEL_TIKTOK,
            entityType: 'order',
            action: 'sync_orders',
            status: 'success',
            entityId: $account->id,
            payload: ['pulled_count' => 3, 'channel' => 'tiktok_shop'],
            marketplaceAccountId: $account->id
        );

        MarketplaceSyncLog::log(
            businessId: $business->id,
            channel: MarketplaceAccount::CHANNEL_TIKTOK,
            entityType: 'product',
            action: 'sync_stock',
            status: 'success',
            entityId: $products->first()?->id ?? $account->id,
            payload: ['product' => $products->first()?->name, 'synced_stock' => 18],
            marketplaceAccountId: $account->id
        );

        $this->info("✓ Seeded 3 audit sync logs for TikTok Shop for [{$business->name}].");
    }
}
