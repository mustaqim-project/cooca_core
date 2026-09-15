@extends('layouts.customer', ['title' => $business->name . ' — Produk'])

@section('content')
<div class="space-y-6">

    {{-- Store Header --}}
    <div class="bento-card p-6 bg-gradient-to-br from-white to-[#007AFF]/5 dark:from-[#1C1C1E] dark:to-[#007AFF]/10">
        <div class="flex items-start gap-5">
            @if($business->commerceStoreSetting?->logo_url)
                <img src="{{ $business->commerceStoreSetting->logo_url }}" alt="{{ $business->name }}"
                     class="w-16 h-16 rounded-2xl object-cover border border-black/5 shrink-0">
            @else
                <div class="w-16 h-16 rounded-2xl bg-[#007AFF]/10 flex items-center justify-center text-3xl shrink-0">??</div>
            @endif
            <div class="flex-1">
                <h1 class="text-xl font-extrabold text-black dark:text-white">{{ $business->name }}</h1>
                @if($business->city) <p class="text-sm text-black/50 dark:text-white/50 mt-0.5">?? {{ $business->city }}</p> @endif
                @if($business->commerceStoreSetting?->description)
                    <p class="text-sm text-black/60 dark:text-white/60 mt-2 leading-relaxed">{{ $business->commerceStoreSetting->description }}</p>
                @endif
            </div>
            <div class="flex gap-2 shrink-0">
                <a href="{{ route('customer.cart') }}" id="cart-badge-btn"
                   class="relative inline-flex items-center gap-1.5 px-4 py-2 bg-[#007AFF] text-white rounded-xl text-sm font-semibold hover:bg-[#0062CC] transition-colors">
                    ?? Cart
                    @if($cart && $cart->items->count() > 0)
                        <span id="cart-badge"
                              class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-[#FF3B30] text-white text-[10px] font-black rounded-full flex items-center justify-center">
                            {{ $cart->items->count() }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('customer.stores') }}"
                   class="px-4 py-2 border border-black/10 dark:border-white/10 rounded-xl text-sm font-semibold hover:bg-black/5 transition-colors">
                    ? Toko Lain
                </a>
            </div>
        </div>

        {{-- Search & Category Filter --}}
        <form method="GET" action="{{ route('customer.stores.show', $business->slug) }}" class="mt-5 flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari produk..."
                       class="w-full pl-9 pr-4 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-sm focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-black/40 text-sm">??</span>
            </div>
            @if($categories->isNotEmpty())
                <select name="category"
                        class="px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-sm focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40"
                        onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->slug }}" @selected($category === $cat->slug)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            @endif
            <button type="submit" class="px-4 py-2 bg-black/5 dark:bg-white/10 rounded-xl text-sm font-semibold">Cari</button>
        </form>
    </div>

    {{-- Product Grid --}}
    @if($products->isEmpty())
        <div class="bento-card p-12 text-center text-black/50 dark:text-white/50">
            <span class="text-4xl block mb-3">??</span>
            <p class="font-semibold">Tidak ada produk ditemukan.</p>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($products as $product)
            <div class="bento-card p-0 overflow-hidden group flex flex-col">
                {{-- Product image --}}
                <div class="h-40 bg-black/[0.03] dark:bg-white/[0.03] flex items-center justify-center overflow-hidden">
                    @if($product->image_url ?? null)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    @else
                        <span class="text-5xl">???</span>
                    @endif
                </div>

                <div class="p-4 flex-1 flex flex-col">
                    <h3 class="font-semibold text-[13.5px] text-black dark:text-white leading-snug line-clamp-2">{{ $product->name }}</h3>
                    @if($product->category)
                        <span class="text-[11px] text-black/40 dark:text-white/40 mt-1">{{ $product->category->name }}</span>
                    @endif
                    <div class="mt-auto pt-3">
                        <p class="font-extrabold text-[15px] text-[#007AFF]">
                            Rp {{ number_format($product->selling_price ?? 0, 0, ',', '.') }}
                        </p>
                        <button
                            onclick="addToCart('{{ $business->slug }}', '{{ $product->id }}', this)"
                            data-product-id="{{ $product->id }}"
                            class="mt-2 w-full py-2 bg-[#007AFF] text-white text-sm font-semibold rounded-xl hover:bg-[#0062CC] active:scale-95 transition-all">
                            + Tambah
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
    btn.disabled = true;
    btn.textContent = '...';

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
            btn.textContent = '? Ditambahkan';
            btn.classList.replace('bg-[#007AFF]', 'bg-[#34C759]');
            // Update cart badge
            const badge = document.getElementById('cart-badge');
            if (badge) badge.textContent = data.cart_count;
            setTimeout(() => {
                btn.textContent = '+ Tambah';
                btn.classList.replace('bg-[#34C759]', 'bg-[#007AFF]');
                btn.disabled = false;
            }, 1500);
        } else {
            btn.textContent = '? Gagal';
            btn.disabled = false;
        }
    } catch {
        btn.textContent = '? Error';
        btn.disabled = false;
    }
}
</script>
@endpush
@endsection
