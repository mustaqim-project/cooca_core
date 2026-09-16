<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessLandingPage;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicBusinessLandingController extends Controller
{
    /**
     * Render the public single-page landing for a business tenant.
     */
    public function show(string $slug): View
    {
        $business = Business::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (! $business) {
            $business = Business::where('is_active', true)
                ->get()
                ->first(fn (Business $candidate): bool => Str::slug($candidate->name) === $slug);
        }

        abort_unless($business, 404);

        $landingPage = BusinessLandingPage::where('business_id', $business->id)->first();

        if (! $landingPage) {
            $landingPage = BusinessLandingPage::create([
                'business_id' => $business->id,
                'headline' => $business->name,
                'subheadline' => $business->description ?: 'Selamat datang di toko & etalase resmi ' . $business->name . '.',
                'is_published' => true,
                'show_pos_products' => true,
                'theme_color' => '#007AFF',
                'cta_primary_text' => 'Beli Sekarang',
                'whatsapp_number' => $business->phone ?: '081234567890',
            ]);
        }

        $isAuthorizedPreview = request()->boolean('preview')
            && request()->user()?->active_business_id === $business->id;

        abort_unless($landingPage->is_published || $isAuthorizedPreview, 404);

        // 0. Storefront settings & active payment methods & shipping rules
        $storeSetting = \App\Models\CommerceStoreSetting::where('business_id', $business->id)->first();
        if (! $storeSetting) {
            $storeSetting = \App\Models\CommerceStoreSetting::create([
                'business_id' => $business->id,
                'is_storefront_enabled' => true,
                'is_discoverable' => true,
                'allow_pickup' => true,
                'allow_delivery' => true,
                'allow_scheduled_order' => true,
                'allow_request_order' => true,
                'min_order_amount' => 10000,
            ]);
        }

        $paymentMethods = \App\Models\CommercePaymentMethod::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $shippingRules = \App\Models\CommerceShippingRule::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $posTables = \App\Models\PosTable::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('table_number')
            ->get();

        // 1. Physical Goods catalog (Produk Fisik)
        $posProducts = Product::where('business_id', $business->id)
            ->where('is_active', true)
            ->goods()
            ->with('category')
            ->orderBy('name')
            ->get();

        $productCategories = ProductCategory::where('business_id', $business->id)
            ->whereHas('products', fn ($query) => $query->where('is_active', true)->goods())
            ->orderBy('name')
            ->get();

        $productPayload = $posProducts->map(fn (Product $product): array => [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => (float) $product->selling_price,
            'image_url' => $product->image_url,
            'category_id' => $product->category_id,
            'category' => $product->category?->name,
            'type' => 'goods',
            'stock' => $product->calculateEffectiveAvailableStock(),
        ])->values();

        // 2. Services & Layanan: Sinkronkan otomatis dari master katalog Jasa & Layanan (/services)
        $dbServices = Product::where('business_id', $business->id)
            ->where('is_active', true)
            ->services()
            ->with('category')
            ->orderBy('name')
            ->get();

        $serviceCategories = ProductCategory::where('business_id', $business->id)
            ->whereHas('products', fn ($query) => $query->where('is_active', true)->services())
            ->orderBy('name')
            ->get();

        $mappedDbServices = $dbServices->map(fn (Product $p): array => [
            'id' => $p->id,
            'title' => $p->name,
            'name' => $p->name,
            'description' => $p->description ?: 'Layanan profesional terpercaya.',
            'price' => $p->selling_price > 0 ? 'Rp ' . number_format((float) $p->selling_price, 0, ',', '.') : 'Hubungi kami',
            'raw_price' => (float) $p->selling_price,
            'image_url' => $p->image_url,
            'category_id' => $p->category_id,
            'category' => $p->category?->name,
            'icon' => 'sparkles',
            'badge' => 'Layanan',
            'type' => 'service',
            'stock' => null,
        ])->all();

        $customServices = $landingPage->custom_services ?? [];
        $services = collect(array_merge($mappedDbServices, $customServices))->map(fn (array $service): array => [
            ...$service,
            'description' => $service['description'] ?? $service['desc'] ?? '',
        ]);

        $customer = auth('customer')->user();
        $dbCartItems = null;
        $openCheckoutModal = false;

        if ($customer) {
            $customerCart = \App\Models\CustomerCart::where('global_customer_id', $customer->id)
                ->where('business_id', $business->id)
                ->with(['items.product'])
                ->first();

            if ($customerCart && $customerCart->items->isNotEmpty()) {
                $dbCartItems = $customerCart->items->map(fn ($item) => [
                    'id'        => $item->product_id,
                    'name'      => $item->product?->name ?? 'Produk',
                    'price'     => (float) $item->unit_price,
                    'image_url' => $item->product?->image_url,
                    'quantity'  => (float) $item->quantity,
                    'notes'     => $item->notes ?? '',
                ])->values()->all();
            }

            if (session()->has("customer_cart_checkout_{$business->id}")) {
                $openCheckoutModal = true;
                session()->forget("customer_cart_checkout_{$business->id}");
            }
        }

        return view('public.business_landing', compact(
            'business',
            'landingPage',
            'services',
            'serviceCategories',
            'posProducts',
            'productCategories',
            'productPayload',
            'storeSetting',
            'paymentMethods',
            'shippingRules',
            'posTables',
            'dbCartItems',
            'openCheckoutModal'
        ));
    }
}
