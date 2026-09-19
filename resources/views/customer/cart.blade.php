@extends('layouts.customer', ['title' => 'Keranjang Belanja'])

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="bento-card p-6 bg-gradient-to-br from-white to-[#007AFF]/5 dark:from-[#1C1C1E] dark:to-[#007AFF]/10">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                        <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    </div>
                    <h1 class="text-2xl font-extrabold text-black dark:text-white tracking-tight">Keranjang Belanja</h1>
                </div>
                <p class="text-xs text-black/60 dark:text-white/60 mt-1.5">
                    {{ $carts->count() }} Toko &bull; Grand Total
                    <span class="font-extrabold text-[#007AFF] tabular-nums">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                </p>
            </div>
            <a href="{{ route('customer.stores') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 rounded-xl text-xs font-semibold transition-colors">
                <i data-lucide="store" class="w-3.5 h-3.5"></i>
                <span>Jelajahi Toko Lain</span>
            </a>
        </div>
    </div>

    @if($carts->isEmpty())
        <div class="bento-card p-16 text-center">
            <div class="w-16 h-16 rounded-2xl bg-black/5 dark:bg-white/5 flex items-center justify-center mx-auto mb-4 text-black/30 dark:text-white/30">
                <i data-lucide="shopping-bag" class="w-8 h-8 stroke-1"></i>
            </div>
            <h2 class="font-bold text-lg text-black dark:text-white">Keranjang Masih Kosong</h2>
            <p class="text-xs text-black/50 dark:text-white/50 mt-1.5 mb-6 max-w-sm mx-auto">
                Yuk, temukan produk UMKM favorit Anda dan mulai berbelanja langsung!
            </p>
            <a href="{{ route('customer.stores') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#007AFF] text-white rounded-xl text-xs font-bold hover:bg-[#0062CC] transition-colors shadow-xs">
                <i data-lucide="compass" class="w-4 h-4"></i>
                <span>Jelajahi Toko UMKM</span>
            </a>
        </div>
    @else

        {{-- Cart grouped per toko --}}
        @foreach($carts as $cart)
        <div class="bento-card overflow-hidden" id="cart-store-{{ $cart->business_id }}">

            {{-- Store Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-4 border-b border-black/5 dark:border-white/5 bg-black/[0.015] dark:bg-white/[0.015]">
                <div class="flex items-center gap-3">
                    <a href="{{ route('customer.stores.show', $cart->business->slug) }}"
                       class="flex items-center gap-3 group">
                        @if($cart->business->store_logo_url ?: $cart->business->logo_url)
                            <img src="{{ $cart->business->store_logo_url ?: $cart->business->logo_url }}"
                                 class="w-10 h-10 rounded-xl object-cover border border-black/5 dark:border-white/10 shrink-0" alt="{{ $cart->business->name }}">
                        @else
                            <div class="w-10 h-10 rounded-xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0 border border-[#007AFF]/15">
                                <i data-lucide="store" class="w-5 h-5"></i>
                            </div>
                        @endif
                        <div>
                            <p class="font-bold text-[14px] text-black dark:text-white group-hover:text-[#007AFF] transition-colors">
                                {{ $cart->business->name }}
                            </p>
                            @if($cart->business->city || $cart->business->address)
                                <p class="text-[11px] text-black/40 dark:text-white/40 flex items-center gap-1">
                                    <i data-lucide="map-pin" class="w-3 h-3 text-[#007AFF]"></i>
                                    <span>{{ $cart->business->city ?? Str::limit($cart->business->address, 30) }}</span>
                                </p>
                            @endif
                        </div>
                    </a>
                </div>

                <div class="flex items-center gap-3 self-end sm:self-center">
                    <a href="{{ $cart->business->public_url }}" target="_blank"
                       class="text-[11.5px] font-semibold text-black/50 dark:text-white/50 hover:text-[#007AFF] flex items-center gap-1 transition-colors">
                        <i data-lucide="external-link" class="w-3 h-3"></i>
                        <span>Etalase Toko</span>
                    </a>
                    <span class="text-black/20 dark:text-white/20">&bull;</span>
                    <span class="text-xs font-semibold text-black/50 dark:text-white/50">
                        {{ $cart->items->count() }} item
                    </span>
                </div>
            </div>

            {{-- Items --}}
            <div class="divide-y divide-black/5 dark:divide-white/5">
                @foreach($cart->items as $item)
                <div class="flex items-center gap-4 px-5 py-4" id="item-{{ $item->id }}">
                    {{-- Product image --}}
                    <div class="w-14 h-14 rounded-xl bg-black/[0.03] dark:bg-white/[0.03] flex items-center justify-center shrink-0 overflow-hidden border border-black/5 dark:border-white/10">
                        @if($item->product?->image_url ?? null)
                            <img src="{{ $item->product->image_url }}" class="w-full h-full object-cover" alt="{{ $item->product->name }}">
                        @else
                            <i data-lucide="package" class="w-6 h-6 text-black/30 dark:text-white/30 stroke-1"></i>
                        @endif
                    </div>

                    {{-- Details --}}
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-[13.5px] text-black dark:text-white truncate">
                            {{ $item->product?->name ?? 'Produk tidak tersedia' }}
                        </p>
                        <p class="text-xs text-black/50 dark:text-white/50 mt-0.5 tabular-nums">
                            Rp {{ number_format($item->unit_price, 0, ',', '.') }} / item
                        </p>
                    </div>

                    {{-- Qty control --}}
                    <div class="flex items-center gap-1.5 shrink-0 bg-black/5 dark:bg-white/5 p-1 rounded-xl">
                        <button onclick="updateQty('{{ $cart->business->slug }}', '{{ $item->id }}', {{ $item->quantity - 1 }})"
                                class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-bold hover:bg-[#FF3B30]/15 hover:text-[#FF3B30] transition-colors"
                                title="Kurangi">
                            <i data-lucide="minus" class="w-3 h-3"></i>
                        </button>
                        <span class="w-7 text-center font-bold text-xs tabular-nums text-black dark:text-white" id="qty-{{ $item->id }}">
                            {{ $item->quantity % 1 == 0 ? (int)$item->quantity : $item->quantity }}
                        </span>
                        <button onclick="updateQty('{{ $cart->business->slug }}', '{{ $item->id }}', {{ $item->quantity + 1 }})"
                                class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-bold hover:bg-[#34C759]/15 hover:text-[#34C759] transition-colors"
                                title="Tambah">
                            <i data-lucide="plus" class="w-3 h-3"></i>
                        </button>
                    </div>

                    {{-- Line total --}}
                    <div class="text-right shrink-0 min-w-[85px]">
                        <p class="font-extrabold text-[14px] text-black dark:text-white tabular-nums" id="total-{{ $item->id }}">
                            Rp {{ number_format($item->quantity * $item->unit_price, 0, ',', '.') }}
                        </p>
                    </div>

                    {{-- Remove --}}
                    <button onclick="removeItem('{{ $cart->business->slug }}', '{{ $item->id }}')"
                            class="w-8 h-8 rounded-lg flex items-center justify-center text-black/30 hover:text-[#FF3B30] hover:bg-[#FF3B30]/10 transition-colors shrink-0"
                            title="Hapus dari keranjang">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
                @endforeach
            </div>

            {{-- Store subtotal + Checkout --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-4 bg-black/[0.015] dark:bg-white/[0.015] border-t border-black/5 dark:border-white/5">
                <div>
                    <p class="text-[11px] text-black/50 dark:text-white/50 uppercase tracking-wider font-bold">Subtotal {{ $cart->business->name }}</p>
                    <p class="font-extrabold text-[16px] text-black dark:text-white tabular-nums" id="subtotal-{{ $cart->business_id }}">
                        Rp {{ number_format($cart->items->sum(fn($i) => $i->quantity * $i->unit_price), 0, ',', '.') }}
                    </p>
                </div>
                <form method="POST" action="{{ route('customer.cart.checkout', $cart->business->slug) }}">
                    @csrf
                    <button type="submit"
                            class="w-full sm:w-auto px-5 py-2.5 bg-[#007AFF] text-white rounded-xl font-bold text-xs hover:bg-[#0062CC] active:scale-95 transition-all flex items-center justify-center gap-2 shadow-xs">
                        <span>Lanjut Checkout</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </button>
                </form>
            </div>
        </div>
        @endforeach

        {{-- Grand Total Bar --}}
        <div class="bento-card p-5 bg-gradient-to-r from-[#007AFF] to-[#5AC8FA] text-white shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white/80 text-xs font-semibold">Grand Total Semua Toko</p>
                    <p class="text-2xl font-black tracking-tight tabular-nums" id="grand-total">
                        Rp {{ number_format($grandTotal, 0, ',', '.') }}
                    </p>
                </div>
                <div class="text-right text-white/80 text-xs leading-relaxed">
                    Pesanan diproses &amp; diantar<br>oleh masing-masing toko
                </div>
            </div>
        </div>

    @endif
</div>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name=csrf-token]').content;

async function updateQty(storeSlug, itemId, newQty) {
    if (newQty <= 0) {
        return removeItem(storeSlug, itemId);
    }

    try {
        const res = await fetch(`/customer/cart/${storeSlug}/item/${itemId}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ quantity: newQty }),
        });
        const data = await res.json();
        if (data.removed) {
            document.getElementById(`item-${itemId}`)?.remove();
            location.reload();
        } else {
            location.reload();
        }
    } catch {
        alert('Gagal mengubah kuantitas. Silakan coba lagi.');
    }
}

async function removeItem(storeSlug, itemId) {
    if (!confirm('Hapus produk ini dari keranjang?')) return;
    try {
        const res = await fetch(`/customer/cart/${storeSlug}/item/${itemId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById(`item-${itemId}`)?.remove();
            location.reload();
        }
    } catch {
        alert('Gagal menghapus produk. Silakan coba lagi.');
    }
}
</script>
@endpush
@endsection
