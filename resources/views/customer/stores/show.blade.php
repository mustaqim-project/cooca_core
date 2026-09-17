@extends('layouts.customer', ['title' => $business->name . ' — Produk'])

@section('content')
<div class="space-y-6">

    {{-- Store Header --}}
    <div class="bento-card p-6 bg-gradient-to-br from-white to-[#007AFF]/5 dark:from-[#1C1C1E] dark:to-[#007AFF]/10">
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-5">
            <div class="flex items-start gap-4">
                @if($business->store_logo_url ?: $business->logo_url)
                    <img src="{{ $business->store_logo_url ?: $business->logo_url }}" alt="{{ $business->name }}"
                         class="w-16 h-16 rounded-2xl object-cover border border-black/5 dark:border-white/10 shrink-0 shadow-xs">
                @else
                    <div class="w-16 h-16 rounded-2xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0 border border-[#007AFF]/15">
                        <i data-lucide="store" class="w-8 h-8"></i>
                    </div>
                @endif
                <div class="space-y-1">
                    <h1 class="text-xl font-extrabold text-black dark:text-white tracking-tight">{{ $business->name }}</h1>
                    
                    <div class="flex flex-wrap items-center gap-2 text-xs text-black/50 dark:text-white/50">
                        @if($business->city || $business->address)
                            <span class="inline-flex items-center gap-1 font-medium">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                {{ $business->city ?? Str::limit($business->address, 30) }}
                            </span>
                        @endif

                        @if($business->industry_category || $business->industry)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-black/5 dark:bg-white/5 text-[11px] font-semibold">
                                <i data-lucide="tag" class="w-3 h-3"></i>
                                {{ ucfirst($business->industry_category ?? $business->industry) }}
                            </span>
                        @endif
                    </div>

                    @if($business->description || $business->landingPage?->about_story)
                        <p class="text-xs text-black/60 dark:text-white/60 mt-1.5 leading-relaxed max-w-xl">
                            {{ Str::limit($business->description ?: $business->landingPage?->about_story, 150) }}
                        </p>
                    @endif
                </div>
            </div>

            {{-- Action CTAs --}}
            <div class="flex flex-wrap items-center gap-2 shrink-0 pt-2 md:pt-0">
                <a href="{{ url('/b/' . $business->slug) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 text-black dark:text-white rounded-xl text-xs font-semibold transition-colors">
                    <i data-lucide="external-link" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                    <span>Etalase Toko</span>
                </a>

                <a href="{{ route('customer.cart') }}" id="cart-badge-btn"
                   class="relative inline-flex items-center gap-1.5 px-4 py-2 bg-[#007AFF] text-white rounded-xl text-xs font-semibold hover:bg-[#0062CC] transition-colors shadow-xs">
                    <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                    <span>Keranjang</span>
                    @if($cart && $cart->items->count() > 0)
                        <span id="cart-badge"
                              class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-[#FF3B30] text-white text-[10px] font-black rounded-full flex items-center justify-center shadow-xs">
                            {{ $cart->items->count() }}
                        </span>
                    @endif
                </a>

                <a href="{{ route('customer.stores') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-black/10 dark:border-white/10 rounded-xl text-xs font-semibold hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    <span>Daftar Toko</span>
                </a>
            </div>
        </div>

        {{-- Search & Category Filter --}}
        <form method="GET" action="{{ route('customer.stores.show', $business->slug) }}" class="mt-5 flex flex-wrap gap-2.5">
            <div class="relative flex-1 min-w-[200px]">
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari produk di toko ini..."
                       class="w-full pl-9 pr-4 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-xs focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                <i data-lucide="search" class="w-3.5 h-3.5 text-black/40 dark:text-white/40 absolute left-3 top-1/2 -translate-y-1/2"></i>
            </div>
            @if($categories->isNotEmpty())
                <select name="category"
                        class="px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-xs focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40"
                        onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->slug }}" @selected($category === $cat->slug)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            @endif
            <button type="submit" class="px-4 py-2 bg-[#007AFF] text-white rounded-xl text-xs font-bold hover:bg-[#0062CC] transition">
                Cari
            </button>
        </form>
    </div>

    {{-- Product Grid --}}
    @if($products->isEmpty())
        <div class="bento-card p-12 text-center text-black/50 dark:text-white/50">
            <div class="w-12 h-12 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center mx-auto mb-3 text-black/40 dark:text-white/40">
                <i data-lucide="package-open" class="w-6 h-6"></i>
            </div>
            <p class="font-semibold text-sm">Tidak ada produk ditemukan.</p>
            <p class="text-xs text-black/40 dark:text-white/40 mt-1">Coba kata kunci lain atau pilih kategori berbeda.</p>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($products as $product)
            <div class="bento-card p-0 overflow-hidden group flex flex-col hover:border-[#007AFF]/40 transition duration-200">
                {{-- Product image --}}
                <div class="h-40 bg-black/[0.03] dark:bg-white/[0.03] flex items-center justify-center overflow-hidden relative">
                    @if($product->image_url ?? null)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    @else
                        <div class="flex flex-col items-center justify-center text-black/30 dark:text-white/30 gap-1">
                            <i data-lucide="package" class="w-8 h-8 stroke-1"></i>
                            <span class="text-[10px]">Tanpa Foto</span>
                        </div>
                    @endif
                </div>

                <div class="p-4 flex-1 flex flex-col">
                    <h3 class="font-bold text-[13.5px] text-black dark:text-white leading-snug line-clamp-2">{{ $product->name }}</h3>
                    @if($product->category)
                        <span class="text-[11px] text-black/40 dark:text-white/40 mt-1">{{ $product->category->name }}</span>
                    @endif
                    <div class="mt-auto pt-3">
                        <p class="font-extrabold text-[15px] text-[#007AFF] tabular-nums">
                            Rp {{ number_format($product->selling_price ?? 0, 0, ',', '.') }}
                        </p>
                        <button
                            onclick="addToCart('{{ $business->slug }}', '{{ $product->id }}', this)"
                            data-product-id="{{ $product->id }}"
                            class="mt-2.5 w-full py-2 bg-[#007AFF] text-white text-xs font-bold rounded-xl hover:bg-[#0062CC] active:scale-95 transition-all flex items-center justify-center gap-1.5 shadow-xs">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Tambah ke Keranjang</span>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-4">{{ $products->withQueryString()->links() }}</div>
    @endif

</div>

@push('scripts')
<script>
async function addToCart(storeSlug, productId, btn) {
    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="animate-pulse">Menambahkan...</span>';

    try {
        const res = await fetch(`/customer/cart/${storeSlug}/add`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ product_id: productId, quantity: 1 }),
        });
        const data = await res.json();

        if (data.success) {
            btn.innerHTML = '<i data-lucide="check" class="w-3.5 h-3.5"></i><span>Ditambahkan</span>';
            btn.classList.replace('bg-[#007AFF]', 'bg-[#34C759]');
            if (window.lucide) lucide.createIcons();

            // Update or create cart badge
            let badge = document.getElementById('cart-badge');
            if (badge) {
                badge.textContent = data.cart_count;
            } else {
                const btnBadge = document.getElementById('cart-badge-btn');
                if (btnBadge && data.cart_count > 0) {
                    badge = document.createElement('span');
                    badge.id = 'cart-badge';
                    badge.className = 'absolute -top-1.5 -right-1.5 w-5 h-5 bg-[#FF3B30] text-white text-[10px] font-black rounded-full flex items-center justify-center shadow-xs';
                    badge.textContent = data.cart_count;
                    btnBadge.appendChild(badge);
                }
            }

            setTimeout(() => {
                btn.innerHTML = originalContent;
                btn.classList.replace('bg-[#34C759]', 'bg-[#007AFF]');
                btn.disabled = false;
                if (window.lucide) lucide.createIcons();
            }, 1500);
        } else {
            btn.innerHTML = '<span>Gagal</span>';
            btn.classList.replace('bg-[#007AFF]', 'bg-[#FF3B30]');
            setTimeout(() => {
                btn.innerHTML = originalContent;
                btn.classList.replace('bg-[#FF3B30]', 'bg-[#007AFF]');
                btn.disabled = false;
                if (window.lucide) lucide.createIcons();
            }, 1500);
        }
    } catch {
        btn.innerHTML = '<span>Error</span>';
        btn.disabled = false;
    }
}
</script>
@endpush
@endsection
