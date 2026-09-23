@extends('public.storefront.layouts.app')

@section('content')
    <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 space-y-8">

        {{-- Breadcrumb & Header Title --}}
        <div class="flex flex-col gap-2">
            <div class="flex items-center gap-2 text-xs text-neutral-500">
                <a href="{{ url('/' . $business->slug) }}" class="hover:text-theme-primary transition">Beranda</a>
                <span>/</span>
                <span class="text-neutral-800 dark:text-neutral-200 font-medium">Katalog Toko</span>
            </div>
            <h1 class="font-heading font-extrabold text-2xl sm:text-4xl text-neutral-900 dark:text-white">
                Katalog Produk &amp; Layanan
            </h1>
            <p class="text-sm text-neutral-500 max-w-2xl">
                Jelajahi seluruh koleksi produk dan layanan resmi {{ $business->name }}. Temukan barang kebutuhan Anda
                dengan harga terbaik.
            </p>
        </div>

        {{-- Filter & Search Toolbar --}}
        <div
            class="p-4 sm:p-5 rounded-[20px] bg-white dark:bg-neutral-800/80 border border-black/5 dark:border-white/10 shadow-sm">
            <form method="GET" action="{{ url('/' . $business->slug . '/katalog') }}"
                class="grid grid-cols-1 sm:grid-cols-12 gap-3 sm:gap-4 items-center">

                {{-- Keyword Search --}}
                <div class="sm:col-span-5 relative">
                    <i data-lucide="search" class="w-4 h-4 text-neutral-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text" name="q" value="{{ $search }}"
                        placeholder="Cari nama produk atau deskripsi..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-[16px] sm:text-sm text-neutral-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-theme-primary">
                </div>

                {{-- Category Filter --}}
                <div class="sm:col-span-3">
                    <select name="category" onchange="this.form.submit()"
                        class="w-full px-3 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-[16px] sm:text-sm text-neutral-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-theme-primary">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $categoryId === $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Sorting --}}
                <div class="sm:col-span-2">
                    <select name="sort" onchange="this.form.submit()"
                        class="w-full px-3 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-[16px] sm:text-sm text-neutral-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-theme-primary">
                        <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>Terpopuler</option>
                        <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Terbaru</option>
                        <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>Harga Terendah</option>
                        <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Harga Tertinggi</option>
                    </select>
                </div>

                {{-- Submit / Reset Button --}}
                <div class="sm:col-span-2 flex items-center gap-2">
                    <button type="submit"
                        class="w-full py-2.5 px-4 rounded-[12px] text-sm font-semibold theme-btn-primary shadow-sm flex items-center justify-center gap-1.5 min-h-[44px]">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span>Terapkan</span>
                    </button>
                    @if ($search !== '' || !empty($categoryId) || $sort !== 'popular')
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="p-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-100 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-200 transition min-h-[44px] flex items-center justify-center"
                            title="Reset Filter">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>

            </form>
        </div>

        {{-- Product Grid --}}
        @if ($products->isNotEmpty())
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach ($products as $item)
                    @php
                        $pdpUrl = url('/' . $business->slug . '/produk/' . ($item->slug ?: $item->id));
                        $hasPrice = ($item->show_price_on_web ?? true) && $item->selling_price > 0;
                    @endphp
                    <div
                        class="group flex flex-col rounded-[20px] overflow-hidden bg-white dark:bg-neutral-800/80 border border-black/5 dark:border-white/10 shadow-sm hover:shadow-xl transition-all duration-300">
                        {{-- Thumbnail --}}
                        <a href="{{ $pdpUrl }}"
                            class="relative aspect-square overflow-hidden bg-neutral-100 dark:bg-neutral-900 block">
                            @if ($item->image_url)
                                <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                    loading="lazy">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-neutral-400">
                                    <i data-lucide="package" class="w-12 h-12 stroke-1"></i>
                                </div>
                            @endif

                            @if ($item->is_preorder)
                                <span
                                    class="absolute top-2.5 left-2.5 px-2.5 py-1 rounded-[8px] text-[10px] font-bold bg-amber-500 text-white shadow-sm">
                                    Pre-Order
                                </span>
                            @endif
                        </a>

                        {{-- Info --}}
                        <div class="p-4 flex flex-col flex-1">
                            @if ($item->category)
                                <span
                                    class="text-[11px] font-medium text-neutral-400 dark:text-neutral-500 uppercase tracking-wider mb-1 line-clamp-1">
                                    {{ $item->category->name }}
                                </span>
                            @endif

                            <a href="{{ $pdpUrl }}"
                                class="font-heading font-bold text-sm sm:text-base text-neutral-900 dark:text-white group-hover:text-theme-primary transition line-clamp-2 leading-snug mb-2">
                                {{ $item->name }}
                            </a>

                            <div
                                class="mt-auto pt-3 flex items-center justify-between gap-2 border-t border-black/5 dark:border-white/10">
                                <div class="flex flex-col">
                                    <span class="text-[10px] text-neutral-400 font-medium">Harga</span>
                                    <span class="font-bold text-sm sm:text-base text-neutral-900 dark:text-white"
                                        style="font-variant-numeric: tabular-nums;">
                                        {{ $hasPrice ? 'Rp ' . number_format((float) $item->selling_price, 0, ',', '.') : 'Hubungi Toko' }}
                                    </span>
                                </div>

                                @if ($hasPrice)
                                    <button type="button"
                                        @click="$store.cart.add({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', price: {{ (float) $item->selling_price }}, image_url: '{{ $item->image_url }}' }, 1)"
                                        class="p-2.5 rounded-[12px] bg-neutral-100 dark:bg-neutral-700 hover:bg-theme-primary hover:text-white text-neutral-700 dark:text-neutral-200 transition active:scale-[0.95] min-w-[44px] min-h-[44px] flex items-center justify-center"
                                        title="Tambah ke Keranjang">
                                        <i data-lucide="plus" class="w-4 h-4"></i>
                                    </button>
                                @else
                                    <a href="{{ $pdpUrl }}"
                                        class="p-2 rounded-lg bg-neutral-100 dark:bg-neutral-700 text-xs font-semibold text-neutral-700 dark:text-neutral-300">
                                        Lihat
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="pt-6">
                {{ $products->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div
                class="py-16 text-center rounded-[20px] bg-white dark:bg-neutral-800/60 border border-black/5 dark:border-white/10 p-8 space-y-4">
                <div
                    class="w-16 h-16 rounded-full bg-neutral-100 dark:bg-neutral-700 text-neutral-400 mx-auto flex items-center justify-center">
                    <i data-lucide="package-x" class="w-8 h-8"></i>
                </div>
                <h3 class="font-heading font-bold text-lg text-neutral-800 dark:text-neutral-200">
                    Tidak ada produk ditemukan
                </h3>
                <p class="text-sm text-neutral-500 max-w-sm mx-auto">
                    Coba ubah kata kunci pencarian Anda atau reset filter untuk menampilkan semua produk.
                </p>
                <a href="{{ url('/' . $business->slug . '/katalog') }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-[12px] font-medium text-sm theme-btn-primary min-h-[44px]">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    <span>Reset Semua Filter</span>
                </a>
            </div>
        @endif

    </div>
@endsection
