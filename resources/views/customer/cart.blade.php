@extends('layouts.customer', ['title' => 'Cart Belanja'])

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="bento-card p-6 bg-gradient-to-br from-white to-[#007AFF]/5 dark:from-[#1C1C1E] dark:to-[#007AFF]/10">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-black dark:text-white tracking-tight">Cart Belanja ??</h1>
                <p class="text-sm text-black/60 dark:text-white/60 mt-1">
                    {{ $carts->count() }} toko · Grand Total
                    <span class="font-bold text-[#007AFF]">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                </p>
            </div>
            <a href="{{ route('customer.stores') }}"
               class="px-4 py-2 bg-black/5 dark:bg-white/10 rounded-xl text-sm font-semibold hover:bg-black/10 transition-colors">
                + Toko Lain
            </a>
        </div>
    </div>

    @if($carts->isEmpty())
        <div class="bento-card p-16 text-center">
            <span class="text-5xl block mb-4">??</span>
            <h2 class="font-bold text-lg text-black dark:text-white">Cart Kosong</h2>
            <p class="text-sm text-black/50 dark:text-white/50 mt-2 mb-6">Yuk, mulai belanja dari toko favoritmu!</p>
            <a href="{{ route('customer.stores') }}"
               class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#007AFF] text-white rounded-xl font-semibold hover:bg-[#0062CC] transition-colors">
                ??? Jelajahi Toko
            </a>
        </div>
    @else

        {{-- Cart grouped per toko --}}
        @foreach($carts as $cart)
        <div class="bento-card overflow-hidden" id="cart-store-{{ $cart->business_id }}">

            {{-- Store Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-black/5 dark:border-white/5 bg-black/[0.015] dark:bg-white/[0.015]">
                <a href="{{ route('customer.stores.show', $cart->business->slug) }}"
                   class="flex items-center gap-3 hover:opacity-80 transition-opacity">
                    @if($cart->business->commerceStoreSetting?->logo_url)
                        <img src="{{ $cart->business->commerceStoreSetting->logo_url }}"
                             class="w-9 h-9 rounded-xl object-cover border border-black/5" alt="{{ $cart->business->name }}">
                    @else
                        <div class="w-9 h-9 rounded-xl bg-[#007AFF]/10 flex items-center justify-center text-lg">??</div>
                    @endif
                    <div>
                        <p class="font-bold text-[14px] text-black dark:text-white">{{ $cart->business->name }}</p>
                        @if($cart->business->city)
                            <p class="text-[11px] text-black/40 dark:text-white/40">?? {{ $cart->business->city }}</p>
                        @endif
                    </div>
                </a>
                <span class="text-sm font-semibold text-black/50 dark:text-white/50">
                    {{ $cart->items->count() }} item
                </span>
            </div>

            {{-- Items --}}
            <div class="divide-y divide-black/5 dark:divide-white/5">
                @foreach($cart->items as $item)
                <div class="flex items-center gap-4 px-5 py-4" id="item-{{ $item->id }}">
                    {{-- Product image --}}
                    <div class="w-14 h-14 rounded-xl bg-black/[0.03] dark:bg-white/[0.03] flex items-center justify-center shrink-0 overflow-hidden">
                        @if($item->product?->image_url ?? null)
                            <img src="{{ $item->product->image_url }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-2xl">???</span>
                        @endif
                    </div>

                    {{-- Details --}}
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-[13.5px] text-black dark:text-white truncate">
                            {{ $item->product?->name ?? 'Produk tidak tersedia' }}
                        </p>
                        <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">
                            Rp {{ number_format($item->unit_price, 0, ',', '.') }} / item
                        </p>
                    </div>

                    {{-- Qty control --}}
                    <div class="flex items-center gap-2 shrink-0">
                        <button onclick="updateQty('{{ $cart->business->slug }}', '{{ $item->id }}', {{ $item->quantity - 1 }})"
                                class="w-7 h-7 rounded-lg bg-black/5 dark:bg-white/10 text-sm font-bold hover:bg-[#FF3B30]/10 hover:text-[#FF3B30] transition-colors">-</button>
                        <span class="w-8 text-center font-bold text-[13px] tabular-nums" id="qty-{{ $item->id }}">
                            {{ $item->quantity % 1 == 0 ? (int)$item->quantity : $item->quantity }}
                        </span>
                        <button onclick="updateQty('{{ $cart->business->slug }}', '{{ $item->id }}', {{ $item->quantity + 1 }})"
                                class="w-7 h-7 rounded-lg bg-black/5 dark:bg-white/10 text-sm font-bold hover:bg-[#34C759]/10 hover:text-[#34C759] transition-colors">+</button>
                    </div>

                    {{-- Line total --}}
                    <div class="text-right shrink-0 min-w-[80px]">
                        <p class="font-extrabold text-[14px] text-black dark:text-white" id="total-{{ $item->id }}">
                            Rp {{ number_format($item->quantity * $item->unit_price, 0, ',', '.') }}
                        </p>
                    </div>

                    {{-- Remove --}}
                    <button onclick="removeItem('{{ $cart->business->slug }}', '{{ $item->id }}')"
                            class="w-7 h-7 rounded-lg flex items-center justify-center text-black/30 hover:text-[#FF3B30] hover:bg-[#FF3B30]/10 transition-colors shrink-0">
                        ?
                    </button>
                </div>
                @endforeach
            </div>

            {{-- Store subtotal + Checkout --}}
            <div class="flex items-center justify-between px-5 py-4 bg-black/[0.015] dark:bg-white/[0.015] border-t border-black/5 dark:border-white/5">
                <div>
                    <p class="text-[11px] text-black/50 dark:text-white/50 uppercase tracking-wider font-bold">Subtotal {{ $cart->business->name }}</p>
                    <p class="font-extrabold text-[16px] text-black dark:text-white" id="subtotal-{{ $cart->business_id }}">
                        Rp {{ number_format($cart->items->sum(fn($i) => $i->quantity * $i->unit_price), 0, ',', '.') }}
                    </p>
                </div>
                <form method="POST" action="{{ route('customer.cart.checkout', $cart->business->slug) }}">
                    @csrf
                    <button type="submit"
                            class="px-6 py-2.5 bg-[#007AFF] text-white rounded-xl font-bold text-sm hover:bg-[#0062CC] active:scale-95 transition-all">
                        Checkout ke {{ $cart->business->name }} ?
                    </button>
                </form>
            </div>
        </div>
        @endforeach

        {{-- Grand Total Bar --}}
        <div class="bento-card p-5 bg-gradient-to-r from-[#007AFF] to-[#5AC8FA] text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white/70 text-sm font-semibold">Grand Total Semua Toko</p>
                    <p class="text-2xl font-black tracking-tight" id="grand-total">
                        Rp {{ number_format($grandTotal, 0, ',', '.') }}
                    </p>
                </div>
                <div class="text-right text-white/70 text-sm">
                    Checkout tiap toko<br>secara terpisah
                </div>
            </div>
        </div>

    @endif
</div>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name=csrf-token]').content;

async function updateQty(storeSlug, itemId, newQty) {
    const res = await fetch(`/customer/cart/${storeSlug}/item/${itemId}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ quantity: newQty }),
    });
    const data = await res.json();
    if (data.removed) {
        document.getElementById(`item-${itemId}`)?.remove();
    } else {
        location.reload();
    }
}

async function removeItem(storeSlug, itemId) {
    if (!confirm('Hapus item ini dari cart?')) return;
    const res = await fetch(`/customer/cart/${storeSlug}/item/${itemId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
    });
    const data = await res.json();
    if (data.success) {
        document.getElementById(`item-${itemId}`)?.remove();
    }
}
</script>
@endpush
@endsection
