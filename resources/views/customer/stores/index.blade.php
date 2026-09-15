@extends('layouts.customer', ['title' => 'Jelajahi Toko'])

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="bento-card p-6 bg-gradient-to-br from-white to-[#007AFF]/5 dark:from-[#1C1C1E] dark:to-[#007AFF]/10">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-black dark:text-white tracking-tight">Jelajahi Toko ???</h1>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1">
                    Login sekali, belanja di semua toko. Satu cart, banyak pilihan.
                </p>
            </div>
            <a href="{{ route('customer.cart') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-[#007AFF] text-white rounded-xl text-sm font-semibold hover:bg-[#0062CC] transition-colors">
                ?? Lihat Cart Saya
            </a>
        </div>

        {{-- Search --}}
        <form method="GET" action="{{ route('customer.stores') }}" class="mt-5">
            <div class="relative max-w-md">
                <input type="text" name="q" value="{{ $search }}"
                       placeholder="Cari nama toko, kota, atau industri..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-sm focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-black/40">??</span>
            </div>
        </form>
    </div>

    {{-- Store Grid --}}
    @if($stores->isEmpty())
        <div class="bento-card p-12 text-center text-black/50 dark:text-white/50">
            <span class="text-4xl block mb-3">??</span>
            <p class="font-semibold">Tidak ada toko ditemukan.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($stores as $store)
            <a href="{{ route('customer.stores.show', $store->slug) }}"
               class="bento-card p-5 hover:shadow-lg hover:-translate-y-0.5 transition-all group block">
                <div class="flex items-start gap-4">
                    {{-- Logo --}}
                    @if($store->commerceStoreSetting?->logo_url)
                        <img src="{{ $store->commerceStoreSetting->logo_url }}" alt="{{ $store->name }}"
                             class="w-14 h-14 rounded-2xl object-cover border border-black/5 shrink-0">
                    @else
                        <div class="w-14 h-14 rounded-2xl bg-[#007AFF]/10 flex items-center justify-center text-2xl shrink-0">??</div>
                    @endif

                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-[15px] text-black dark:text-white group-hover:text-[#007AFF] transition-colors truncate">
                            {{ $store->name }}
                        </h3>
                        @if($store->city)
                            <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">?? {{ $store->city }}</p>
                        @endif
                        @if($store->industry)
                            <span class="inline-block mt-2 px-2 py-0.5 bg-[#34C759]/10 text-[#34C759] text-[11px] font-semibold rounded-full">
                                {{ $store->industry }}
                            </span>
                        @endif
                    </div>

                    <span class="text-black/30 group-hover:text-[#007AFF] transition-colors mt-1">?</span>
                </div>
            </a>
            @endforeach
        </div>

        <div class="mt-4">{{ $stores->withQueryString()->links() }}</div>
    @endif

</div>
@endsection
