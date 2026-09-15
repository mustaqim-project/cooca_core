@extends('layouts.public_marketing')

@section('title', 'Jelajah Toko & Direktori Bisnis UMKM Indonesia | Cooca UMKM')
@section('description', 'Temukan ribuan toko online, kafe, restoran, butik, katering, dan layanan UMKM terpercaya di Indonesia. Belanja langsung tanpa perantara, pesan antar, atau reservasi meja online.')
@section('keywords', 'direktori umkm, jelajah toko online, toko lokal terdekat, belanja langsung umkm, pesan antar makanan lokal, reservasi resto kafe')

@section('content')
<div class="pt-8 sm:pt-12 pb-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

        <!-- ═══ HERO & SEARCH SECTION ═══ -->
        <div class="text-center max-w-3xl mx-auto space-y-4">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-bold text-xs">
                <i data-lucide="compass" class="w-4 h-4"></i>
                <span>Direktori Terverifikasi Cooca UMKM</span>
            </div>
            <h1 class="text-3xl sm:text-5xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-tight">
                Jelajah <span class="text-[#007AFF] dark:text-[#0A84FF]">Toko &amp; Etalase</span> Lokal
            </h1>
            <p class="text-sm sm:text-base text-[#6E6E73] dark:text-[#86868B] max-w-2xl mx-auto leading-relaxed">
                Temukan produk lokal berkualitas, kuliner lezat, busana kreatif, dan layanan profesional langsung dari pemilik usaha tanpa perantara.
            </p>

            <!-- Search Form -->
            <form method="GET" action="{{ route('public.discovery.index') }}" class="pt-2 max-w-2xl mx-auto">
                @if($category) <input type="hidden" name="kategori" value="{{ $category }}"> @endif
                @if($capability) <input type="hidden" name="fitur" value="{{ $capability }}"> @endif
                <div class="relative flex items-center bg-white dark:bg-[#1C1C1E] rounded-full shadow-lg border border-black/10 dark:border-white/15 p-1.5 focus-within:ring-2 focus-within:ring-[#007AFF] transition">
                    <i data-lucide="search" class="w-5 h-5 ml-3.5 text-black/40 dark:text-white/40 shrink-0"></i>
                    <input type="text" name="q" value="{{ $search }}"
                           placeholder="Cari nama toko, menu makanan, busana, atau kota..."
                           class="w-full bg-transparent border-0 px-3 py-2 text-sm text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none">
                    <button type="submit"
                            class="shrink-0 px-5 py-2.5 rounded-full bg-[#007AFF] hover:bg-[#0066CC] text-white text-xs font-semibold shadow-md active:scale-95 transition">
                        Cari Toko
                    </button>
                </div>
            </form>
        </div>

        <!-- ═══ CATEGORY & CAPABILITY FILTERS ═══ -->
        <div class="space-y-4">
            <!-- Category Pills -->
            <div class="flex items-center justify-center gap-2 overflow-x-auto pb-2 scrollbar-none">
                <a href="{{ route('public.discovery.index', array_filter(['q' => $search, 'fitur' => $capability])) }}"
                   class="px-4 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition {{ empty($category) || $category === 'all' ? 'bg-[#1D1D1F] dark:bg-white text-white dark:text-black shadow-sm' : 'bg-black/5 dark:bg-white/5 text-black/60 dark:text-white/60 hover:bg-black/10 dark:hover:bg-white/10' }}">
                    Semua Toko ({{ $totalStores }})
                </a>
                @foreach($categories as $key => $cat)
                    <a href="{{ route('public.discovery.index', array_filter(['kategori' => $key, 'q' => $search, 'fitur' => $capability])) }}"
                       class="px-4 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition flex items-center gap-1.5 {{ $category === $key ? 'bg-[#007AFF] text-white shadow-sm' : 'bg-black/5 dark:bg-white/5 text-black/60 dark:text-white/60 hover:bg-black/10 dark:hover:bg-white/10' }}">
                        <i data-lucide="{{ $cat['icon'] }}" class="w-3.5 h-3.5"></i>
                        <span>{{ $cat['label'] }} ({{ $cat['count'] }})</span>
                    </a>
                @endforeach
            </div>

            <!-- Capability Quick Toggles -->
            <div class="flex items-center justify-center gap-1.5 overflow-x-auto text-[11px] pb-1 scrollbar-none">
                <span class="text-black/40 dark:text-white/40 font-medium mr-1 shrink-0">Layanan:</span>
                @php
                    $capabilities = [
                        'pickup' => ['label' => 'Ambil Sendiri', 'icon' => 'store'],
                        'delivery' => ['label' => 'Kurir Toko', 'icon' => 'bike'],
                        'scheduled' => ['label' => 'Pre-Order Terjadwal', 'icon' => 'calendar-clock'],
                        'request' => ['label' => 'Custom Request', 'icon' => 'file-question'],
                        'po' => ['label' => 'PO B2B Bertahap', 'icon' => 'truck'],
                        'reservation' => ['label' => 'Reservasi Meja/Jasa', 'icon' => 'calendar-check'],
                    ];
                @endphp
                @foreach($capabilities as $capKey => $cap)
                    <a href="{{ route('public.discovery.index', array_filter(['fitur' => $capability === $capKey ? null : $capKey, 'kategori' => $category, 'q' => $search])) }}"
                       class="px-2.5 py-1 rounded-full border transition flex items-center gap-1 whitespace-nowrap {{ $capability === $capKey ? 'bg-[#34C759]/15 border-[#34C759] text-[#248A3D] dark:text-[#30D158] font-bold' : 'border-black/10 dark:border-white/10 text-black/60 dark:text-white/60 hover:border-black/20' }}">
                        <i data-lucide="{{ $cap['icon'] }}" class="w-3 h-3"></i>
                        <span>{{ $cap['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- ═══ BENTO DIRECTORY GRID ═══ -->
        @if($businesses->isEmpty())
            <div class="bg-white dark:bg-[#1C1C1E] rounded-[28px] border border-black/5 dark:border-white/10 p-12 text-center max-w-lg mx-auto space-y-4 shadow-sm">
                <div class="w-14 h-14 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center mx-auto text-black/30 dark:text-white/30">
                    <i data-lucide="store-off" class="w-7 h-7"></i>
                </div>
                <h3 class="text-base font-bold text-black dark:text-white">Toko Tidak Ditemukan</h3>
                <p class="text-xs text-black/50 dark:text-white/50 leading-relaxed">
                    Tidak ada toko yang sesuai dengan pencarian "{{ $search }}". Silakan coba kata kunci lain atau hapus filter kategori.
                </p>
                <div class="pt-2">
                    <a href="{{ route('public.discovery.index') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-[#007AFF] text-white text-xs font-semibold hover:bg-[#0066CC] transition">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        <span>Reset Semua Filter</span>
                    </a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($businesses as $store)
                    @php
                        $setting = $store->storeSetting;
                        $landing = $store->landingPage;
                        $primaryLoc = $store->locations->first();
                        $storeUrl = url('/' . $store->slug);
                    @endphp
                    <div class="bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/5 dark:border-white/10 p-6 flex flex-col justify-between hover:shadow-xl hover:border-[#007AFF]/30 transition-all duration-300 group">
                        <div class="space-y-4">
                            <!-- Store Header & Avatar -->
                            <div class="flex items-start gap-3.5">
                                @if($store->logo_path)
                                    <img src="{{ Storage::url($store->logo_path) }}" alt="{{ $store->name }}"
                                         class="w-13 h-13 rounded-[16px] object-cover border border-black/10 dark:border-white/10 shadow-sm shrink-0">
                                @else
                                    <div class="w-13 h-13 rounded-[16px] bg-gradient-to-br from-[#007AFF]/15 to-[#5856D6]/15 dark:from-[#007AFF]/25 dark:to-[#5856D6]/25 text-[#007AFF] dark:text-[#0A84FF] font-black text-lg flex items-center justify-center shrink-0 border border-black/5 dark:border-white/10">
                                        {{ Str::upper(substr($store->name, 0, 2)) }}
                                    </div>
                                @endif

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5">
                                        <h2 class="font-bold text-base text-[#1D1D1F] dark:text-[#F5F5F7] truncate group-hover:text-[#007AFF] transition">
                                            {{ $store->name }}
                                        </h2>
                                        <i data-lucide="badge-check" class="w-4 h-4 text-[#007AFF] shrink-0" title="Toko Terverifikasi"></i>
                                    </div>

                                    <div class="flex items-center gap-2 text-[11.5px] text-black/50 dark:text-white/50 mt-0.5">
                                        @if($store->industry_category || $store->template_code)
                                            <span class="font-medium text-[#007AFF] dark:text-[#0A84FF] capitalize">
                                                {{ str_replace('_', ' ', $store->template_code ?: $store->industry_category) }}
                                            </span>
                                            <span>•</span>
                                        @endif
                                        <span class="truncate">
                                            {{ $store->address ?: ($primaryLoc?->name ?? 'Indonesia') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Description / Headline -->
                            <p class="text-xs text-black/60 dark:text-white/60 line-clamp-2 leading-relaxed">
                                {{ $landing?->headline ?: ($store->description ?: 'Melayani pemesanan langsung online, siap kirim atau ambil di toko dengan pelayanan ramah.') }}
                            </p>

                            <!-- Capabilities Badges -->
                            <div class="flex flex-wrap items-center gap-1.5 pt-1">
                                @if($setting?->allow_delivery)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]">
                                        <i data-lucide="bike" class="w-2.5 h-2.5"></i>
                                        <span>Kurir Toko</span>
                                    </span>
                                @endif
                                @if($setting?->allow_pickup)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">
                                        <i data-lucide="store" class="w-2.5 h-2.5"></i>
                                        <span>Ambil di Toko</span>
                                    </span>
                                @endif
                                @if($setting?->allow_scheduled_order)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]">
                                        <i data-lucide="calendar-clock" class="w-2.5 h-2.5"></i>
                                        <span>Pre-Order</span>
                                    </span>
                                @endif
                                @if($setting?->allow_request_order)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400">
                                        <i data-lucide="file-question" class="w-2.5 h-2.5"></i>
                                        <span>Custom Order</span>
                                    </span>
                                @endif
                                @if($setting?->allow_reservation)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#5856D6]/10 text-[#5856D6]">
                                        <i data-lucide="calendar-check" class="w-2.5 h-2.5"></i>
                                        <span>Reservasi Meja</span>
                                    </span>
                                @endif
                                @if($setting?->allow_customer_po)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-purple-500/10 text-purple-600">
                                        <i data-lucide="truck" class="w-2.5 h-2.5"></i>
                                        <span>PO Batch B2B</span>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Card Footer: Stats & CTA -->
                        <div class="pt-5 mt-5 border-t border-black/5 dark:border-white/10 flex items-center justify-between gap-3">
                            <div class="text-[11.5px] text-black/50 dark:text-white/50">
                                <span class="font-bold text-black dark:text-white">{{ $store->products_count }}</span> Produk Aktif
                            </div>

                            <a href="{{ $storeUrl }}"
                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-[#007AFF] group-hover:bg-[#0066CC] text-white text-xs font-semibold shadow-sm transition active:scale-95">
                                <span>Kunjungi Toko</span>
                                <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="pt-6">
                {{ $businesses->links() }}
            </div>
        @endif

        <!-- ═══ CALL TO ACTION FOR MERCHANTS ═══ -->
        <div class="mt-16 bg-gradient-to-r from-[#007AFF]/10 via-[#5856D6]/10 to-[#34C759]/10 rounded-[28px] border border-black/5 dark:border-white/10 p-8 sm:p-10 flex flex-col md:flex-row items-center justify-between gap-6 text-center md:text-left">
            <div class="space-y-2 max-w-xl">
                <h3 class="text-xl sm:text-2xl font-extrabold text-black dark:text-white">
                    Punya Usaha dan Ingin Muncul di Sini?
                </h3>
                <p class="text-xs sm:text-sm text-black/60 dark:text-white/60 leading-relaxed">
                    Buka etalase toko online gratis selamanya di Cooca UMKM. Kelola katalog produk, terima pesanan antar, dan terima pembayaran manual transfer & QRIS tanpa potongan biaya.
                </p>
            </div>
            <a href="{{ route('register') }}"
               class="px-6 py-3 rounded-full bg-[#007AFF] hover:bg-[#0066CC] text-white text-xs sm:text-sm font-semibold shadow-lg shrink-0 transition active:scale-95">
                Buka Toko Gratis Sekarang
            </a>
        </div>

    </div>
</div>
@endsection
