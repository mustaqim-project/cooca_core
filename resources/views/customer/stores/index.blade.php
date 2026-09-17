@extends('layouts.customer', ['title' => 'Jelajahi Toko'])

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="bento-card p-6 bg-gradient-to-br from-white to-[#007AFF]/5 dark:from-[#1C1C1E] dark:to-[#007AFF]/10">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-black dark:text-white tracking-tight">Jelajahi Toko Mitra</h1>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1">
                    Login sekali, belanja di semua toko UMKM. Satu akun terpadu, beragam pilihan.
                </p>
            </div>
            <a href="{{ route('customer.cart') }}"
               class="inline-flex items-center gap-2 h-10 px-4 bg-[#007AFF] text-white rounded-[12px] text-sm font-semibold hover:bg-[#0071E3] transition active:scale-[0.98] shadow-sm">
                <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                <span>Lihat Keranjang</span>
            </a>
        </div>

        {{-- Search --}}
        <form method="GET" action="{{ route('customer.stores') }}" class="mt-5">
            <div class="relative max-w-md">
                <input type="text" name="q" value="{{ $search }}"
                       placeholder="Cari nama toko, kota, atau kategori..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-sm text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 shadow-xs">
                <i data-lucide="search" class="w-4 h-4 text-black/40 dark:text-white/40 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
            </div>
        </form>
    </div>

    {{-- Store Grid --}}
    @if($stores->isEmpty())
        <div class="bento-card p-12 text-center text-black/50 dark:text-white/50 space-y-3">
            <i data-lucide="store" class="w-12 h-12 mx-auto stroke-1 opacity-40"></i>
            <p class="font-semibold text-black/70 dark:text-white/70">Tidak ada toko ditemukan.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($stores as $store)
            @php
                $storeLogo = $store->store_logo_url ?: $store->logo_url;
            @endphp
            <a href="{{ route('customer.stores.show', $store->slug) }}"
               class="bento-card p-5 hover:shadow-lg hover:-translate-y-0.5 transition-all group block">
                <div class="flex items-start gap-4">
                    {{-- Logo --}}
                    @if($storeLogo)
                        <img src="{{ $storeLogo }}" alt="{{ $store->name }}"
                             class="w-14 h-14 rounded-[14px] object-cover border border-black/5 dark:border-white/10 shrink-0 bg-white p-1">
                    @else
                        <div class="w-14 h-14 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold text-lg shrink-0">
                            {{ strtoupper(substr($store->name, 0, 2)) }}
                        </div>
                    @endif

                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-[15px] text-black dark:text-white group-hover:text-[#007AFF] transition-colors truncate">
                            {{ $store->name }}
                        </h3>
                        @if($store->city || $store->address)
                            <p class="text-xs text-black/50 dark:text-white/50 mt-0.5 flex items-center gap-1 truncate">
                                <i data-lucide="map-pin" class="w-3 h-3 shrink-0"></i>
                                <span>{{ $store->city ?: $store->address }}</span>
                            </p>
                        @endif
                        @if($store->industry || $store->industry_category)
                            <span class="inline-block mt-2 px-2.5 py-0.5 bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] text-[11px] font-semibold rounded-full border border-[#34C759]/20">
                                {{ ucfirst(str_replace('_', ' ', (string) ($store->industry ?: $store->industry_category))) }}
                            </span>
                        @endif
                    </div>

                    <i data-lucide="chevron-right" class="w-4 h-4 text-black/30 group-hover:text-[#007AFF] group-hover:translate-x-0.5 transition mt-1 shrink-0"></i>
                </div>
            </a>
            @endforeach
        </div>

        <div class="mt-4">{{ $stores->withQueryString()->links() }}</div>
    @endif

</div>
@endsection
