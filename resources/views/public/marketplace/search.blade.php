@extends('layouts.public_marketing')

@section('title', ($search ? "Hasil Pencarian: {$search}" : 'Cari Produk UMKM') . ' | Cooca Marketplace')
@section('description', 'Cari dan temukan produk UMKM lokal terpercaya di seluruh Indonesia. Filter berdasarkan kategori, harga, dan toko.')

@section('content')
<div class="pt-8 sm:pt-12 pb-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        {{-- ═══ SEARCH HEADER ═══ --}}
        <div class="space-y-4">
            <div class="flex items-center gap-2 text-xs text-[#6E6E73] dark:text-[#86868B]">
                <a href="{{ route('marketplace.index') }}" class="hover:text-[#007AFF] transition">Marketplace</a>
                <span>/</span>
                <span class="text-[#1D1D1F] dark:text-[#F5F5F7] font-medium">Cari Produk</span>
            </div>

            <form method="GET" action="{{ route('marketplace.search') }}" class="max-w-3xl">
                <div class="relative flex items-center bg-white dark:bg-[#1C1C1E] rounded-[16px] shadow-lg border border-black/[0.06] dark:border-white/[0.08] p-1.5 focus-within:ring-2 focus-within:ring-[#007AFF] transition">
                    <i data-lucide="search" class="w-5 h-5 ml-3.5 text-black/40 dark:text-white/40 shrink-0"></i>
                    <input type="text" name="q" value="{{ $search }}"
                        placeholder="Cari produk, toko, atau kategori..."
                        class="w-full bg-transparent border-0 px-3 py-2.5 text-[16px] sm:text-sm text-[#1D1D1F] dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none">
                    @if ($category && $category !== 'semua')
                        <input type="hidden" name="kategori" value="{{ $category }}">
                    @endif
                    <button type="submit"
                        class="shrink-0 px-5 py-2.5 rounded-[12px] bg-[#007AFF] hover:bg-[#0066CC] text-white text-sm font-semibold shadow-md active:scale-[0.98] transition min-h-[44px]">
                        Cari
                    </button>
                </div>
            </form>
        </div>

        {{-- ═══ FILTERS ROW ═══ --}}
        <div class="flex flex-wrap items-center gap-2">
            {{-- Category --}}
            <a href="{{ route('marketplace.search', array_filter(['q' => $search, 'harga' => $priceRange, 'urut' => $sort])) }}"
               class="px-4 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition {{ empty($category) || $category === 'semua' ? 'bg-[#1D1D1F] dark:bg-white text-white dark:text-black shadow-sm' : 'bg-black/5 dark:bg-white/5 text-black/60 dark:text-white/60 hover:bg-black/10 dark:hover:bg-white/10' }}">
                Semua
            </a>
            @foreach ($categories as $key => $cat)
                <a href="{{ route('marketplace.search', array_filter(['q' => $search, 'kategori' => $key, 'harga' => $priceRange, 'urut' => $sort])) }}"
                   class="px-4 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition flex items-center gap-1.5 {{ $category === $key ? 'bg-[#007AFF] text-white shadow-sm' : 'bg-black/5 dark:bg-white/5 text-black/60 dark:text-white/60 hover:bg-black/10 dark:hover:bg-white/10' }}">
                    <i data-lucide="{{ $cat['icon'] }}" class="w-3.5 h-3.5"></i>
                    <span>{{ $cat['label'] }}</span>
                </a>
            @endforeach

            <div class="h-5 w-px bg-black/10 dark:bg-white/10 mx-1"></div>

            {{-- Price Range --}}
            @php
                $priceOptions = [
                    'murah'  => 'Di bawah 50rb',
                    'sedang' => '50rb - 200rb',
                    'mahal'  => 'Di atas 200rb',
                ];
            @endphp
            @foreach ($priceOptions as $pKey => $pLabel)
                <a href="{{ route('marketplace.search', array_filter(['q' => $search, 'kategori' => $category, 'harga' => $priceRange === $pKey ? '' : $pKey, 'urut' => $sort])) }}"
                   class="px-3 py-1.5 rounded-full text-[11px] font-medium whitespace-nowrap transition {{ $priceRange === $pKey ? 'bg-[#34C759]/20 text-[#34C759] dark:text-[#30D158]' : 'bg-black/5 dark:bg-white/5 text-black/50 dark:text-white/50 hover:bg-black/10' }}">
                    {{ $pLabel }}
                </a>
            @endforeach

            <div class="h-5 w-px bg-black/10 dark:bg-white/10 mx-1"></div>

            {{-- Sort --}}
            @php
                $sortOptions = [
                    'terbaru'       => 'Terbaru',
                    'harga_rendah'  => 'Termurah',
                    'harga_tinggi'  => 'Termahal',
                    'nama'          => 'Nama A-Z',
                ];
            @endphp
            <select onchange="window.location.href=this.value"
                    class="text-xs bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] px-3 py-2 text-[16px] sm:text-xs text-[#1D1D1F] dark:text-white focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                @foreach ($sortOptions as $sKey => $sLabel)
                    <option value="{{ route('marketplace.search', array_filter(['q' => $search, 'kategori' => $category, 'harga' => $priceRange, 'urut' => $sKey])) }}"
                            {{ $sort === $sKey ? 'selected' : '' }}>
                        {{ $sLabel }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- ═══ RESULTS ═══ --}}
        @if ($search)
            <p class="text-sm text-[#6E6E73] dark:text-[#86868B]">
                Menampilkan <span class="font-semibold text-[#1D1D1F] dark:text-[#F5F5F7] tabular-nums">{{ $products->total() }}</span> hasil untuk
                "<span class="font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">{{ $search }}</span>"
            </p>
        @endif

        @if ($products->isNotEmpty())
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3 sm:gap-4">
                @foreach ($products as $product)
                    <a href="{{ url('/' . $product->business->slug . '/produk/' . ($product->slug ?: $product->id)) }}"
                       class="group bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden hover:shadow-lg hover:border-[#007AFF]/20 transition">
                        {{-- Product Image --}}
                        <div class="aspect-square bg-[#F2F2F7] dark:bg-[#2C2C2E] overflow-hidden">
                            @if ($product->image_url)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-black/20 dark:text-white/20">
                                    <i data-lucide="package" class="w-10 h-10"></i>
                                </div>
                            @endif
                        </div>

                        {{-- Product Info --}}
                        <div class="p-3 sm:p-4 space-y-1">
                            <h3 class="font-semibold text-sm text-[#1D1D1F] dark:text-[#F5F5F7] line-clamp-2 leading-snug">{{ $product->name }}</h3>
                            <p class="text-xs text-[#6E6E73] dark:text-[#86868B] line-clamp-1 flex items-center gap-1">
                                <i data-lucide="store" class="w-3 h-3 shrink-0"></i>
                                {{ $product->business->name }}
                            </p>
                            <p class="font-bold text-base text-[#1D1D1F] dark:text-[#F5F5F7] tabular-nums pt-0.5">
                                Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="pt-4">
                {{ $products->links() }}
            </div>
        @else
            <div class="py-20 text-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-[#F2F2F7] dark:bg-[#2C2C2E] text-[#6E6E73] mx-auto flex items-center justify-center">
                    <i data-lucide="search-x" class="w-8 h-8"></i>
                </div>
                <h3 class="font-bold text-lg text-[#1D1D1F] dark:text-[#F5F5F7]">Tidak ditemukan</h3>
                <p class="text-sm text-[#6E6E73] dark:text-[#86868B] max-w-sm mx-auto">
                    Coba ubah kata kunci pencarian atau filter kategori Anda.
                </p>
                <a href="{{ route('marketplace.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-[12px] bg-[#007AFF] hover:bg-[#0066CC] text-white text-sm font-semibold shadow-md active:scale-[0.98] transition min-h-[44px]">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Kembali ke Marketplace</span>
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
