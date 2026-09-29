@props([
    'product',
    'business',
    'theme' => null,
])

@php
    $productSlug = $product->slug ?: $product->id;
    $productDetailUrl = url('/' . $business->slug . '/produk/' . $productSlug);
    $price = (float) ($product->price ?? $product->selling_price ?? 0);
    $slashPrice = (float) ($product->slash_price ?? 0);
    $hasDiscount = $slashPrice > $price;
    $discountPercent = $hasDiscount && $slashPrice > 0 ? round((($slashPrice - $price) / $slashPrice) * 100) : 0;
    $trackStock = (bool) ($product->track_stock ?? false);
    $currentStock = (int) ($product->stock ?? 0);
    $isOutOfStock = $trackStock && $currentStock <= 0;
    $isLowStock = $trackStock && $currentStock > 0 && $currentStock <= 5;
    $isPreOrder = (bool) ($product->is_preorder ?? false);

    // Payload formatted safely for Alpine.js store insertion
    $productPayload = [
        'id' => $product->id,
        'name' => $product->name,
        'price' => $price,
        'image_url' => $product->image_url,
        'quantity' => 1,
        'notes' => '',
    ];
@endphp

<div class="group relative flex flex-col bg-white dark:bg-[#181E2A] rounded-2xl sm:rounded-3xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.03)] hover:shadow-[0_12px_32px_rgba(0,0,0,0.08)] hover:-translate-y-1 transition-all duration-300 overflow-hidden">
    
    {{-- 1. Media Image Container --}}
    <a href="{{ $productDetailUrl }}" class="relative block aspect-[4/3] sm:aspect-square w-full overflow-hidden bg-neutral-100 dark:bg-neutral-800 focus:outline-none">
        @if ($product->image_url)
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
        @else
            <div class="w-full h-full flex flex-col items-center justify-center text-neutral-300 dark:text-neutral-600 p-4">
                <i data-lucide="package" class="w-10 h-10 stroke-[1.5] mb-1"></i>
                <span class="text-[11px] font-medium tracking-wide uppercase">{{ $business->name }}</span>
            </div>
        @endif

        {{-- Badges Container (Top Left & Top Right) --}}
        <div class="absolute top-2.5 inset-x-2.5 flex items-start justify-between gap-1.5 pointer-events-none">
            {{-- Category Pill --}}
            @if ($product->category)
                <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold tracking-wide uppercase bg-black/60 dark:bg-white/80 text-white dark:text-black backdrop-blur-md shadow-xs">
                    {{ $product->category->name }}
                </span>
            @else
                <span></span>
            @endif

            {{-- Discount / Pre-Order / Stock Badge --}}
            @if ($hasDiscount)
                <span class="px-2 py-0.5 rounded-lg text-[10px] font-extrabold tracking-wide bg-rose-500 text-white shadow-xs animate-pulse">
                    -{{ $discountPercent }}%
                </span>
            @elseif ($isPreOrder)
                <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold tracking-wide bg-amber-500 text-white shadow-xs">
                    {{ __('storefront.catalog.preorder') }}
                </span>
            @elseif ($isOutOfStock)
                <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold tracking-wide bg-neutral-800 text-white shadow-xs">
                    {{ __('storefront.catalog.out_of_stock') }}
                </span>
            @elseif ($isLowStock)
                <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold tracking-wide bg-amber-500/90 text-white backdrop-blur-md shadow-xs">
                    {{ __('storefront.catalog.limited_stock') }} ({{ $currentStock }})
                </span>
            @endif
        </div>
    </a>

    {{-- 2. Card Content --}}
    <div class="p-3.5 sm:p-4 flex flex-col flex-1 justify-between gap-3">
        <div class="space-y-1">
            <a href="{{ $productDetailUrl }}" class="focus:outline-none">
                <h3 class="font-heading font-semibold text-xs sm:text-sm text-neutral-900 dark:text-white leading-snug line-clamp-2 group-hover:text-theme-primary transition-colors">
                    {{ $product->name }}
                </h3>
            </a>

            @if ($product->short_description || $product->description)
                <p class="text-[11px] text-neutral-500 dark:text-neutral-400 line-clamp-1 leading-relaxed">
                    {{ Str::limit(strip_tags($product->short_description ?: $product->description), 60) }}
                </p>
            @endif
        </div>

        {{-- 3. Price & Action Stepper --}}
        <div class="pt-2 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between gap-2">
            <div class="flex flex-col">
                <div class="flex items-baseline gap-1.5 flex-wrap">
                    <span class="font-heading font-bold text-xs sm:text-sm text-neutral-900 dark:text-white font-mono tracking-tight tabular-nums">
                        Rp {{ number_format($price, 0, ',', '.') }}
                    </span>
                    @if ($hasDiscount)
                        <span class="text-[10px] sm:text-xs text-neutral-400 dark:text-neutral-500 line-through font-mono tabular-nums">
                            Rp {{ number_format($slashPrice, 0, ',', '.') }}
                        </span>
                    @endif
                </div>
                @if ($product->unit)
                    <span class="text-[10px] text-neutral-400 font-medium">/ {{ $product->unit }}</span>
                @endif
            </div>

            {{-- Safe Add to Cart Button --}}
            @if ($isOutOfStock)
                <button type="button" disabled
                    class="px-2.5 py-1.5 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-neutral-400 dark:text-neutral-600 text-[11px] font-semibold cursor-not-allowed">
                    {{ __('storefront.catalog.out_of_stock') }}
                </button>
            @else
                <button type="button"
                    @click="$store.cart.add(@js($productPayload)); $dispatch('product-added-notification', { name: @js($product->name) })"
                    class="p-2 sm:px-3 sm:py-1.5 rounded-xl text-white text-xs font-semibold shadow-xs flex items-center justify-center gap-1.5 hover:opacity-90 active:scale-95 transition-all duration-150 shrink-0"
                    style="background-color: var(--theme-primary);"
                    title="{{ __('storefront.catalog.add_to_cart') }}"
                    aria-label="{{ __('storefront.catalog.add_to_cart') }}: {{ $product->name }}">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span class="hidden sm:inline text-[11px]">{{ __('storefront.catalog.buy') }}</span>
                </button>
            @endif
        </div>
    </div>
</div>
