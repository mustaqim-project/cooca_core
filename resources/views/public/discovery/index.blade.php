@extends('layouts.public_marketing')

@section('title', 'Jelajah Toko & Direktori Bisnis UMKM Indonesia | Cooca')
@section('description', 'Temukan ribuan toko online, kafe, restoran, butik, katering, dan layanan UMKM terpercaya di Indonesia. Belanja langsung tanpa perantara, pesan antar, atau reservasi meja online.')
@section('keywords', 'direktori umkm, jelajah toko online, toko lokal terdekat, belanja langsung umkm, pesan antar makanan lokal, reservasi resto kafe')

@section('content')
    <div class="pt-6 sm:pt-10 pb-24 bg-[#F5F5F7] dark:bg-[#000000] min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12 sm:space-y-16">

            <!-- 2-Grid Hero Section -->
            <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center pt-4">
                <!-- Left: Headline, Search & Filters (7 cols) -->
                <div class="lg:col-span-7 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                        <i data-lucide="compass" class="w-4 h-4"></i>
                        <span>Direktori UMKM Terverifikasi COOCA</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-[1.15]">
                        Jelajah Profil &amp; Toko Resmi UMKM Lokal.
                    </h1>

                    <p class="text-base sm:text-lg text-[#6E6E73] dark:text-[#86868B] leading-relaxed max-w-xl">
                        Temukan toko fisik, kafe, penyedia jasa servis, dan produsen kreatif di sekitar Anda. Transaksi langsung ke pemilik usaha tanpa biaya perantara tambahan.
                    </p>

                    <!-- Search Form -->
                    <form method="GET" action="{{ route('public.discovery.index') }}" class="pt-1">
                        @if ($category)
                            <input type="hidden" name="kategori" value="{{ $category }}">
                        @endif
                        @if ($capability)
                            <input type="hidden" name="fitur" value="{{ $capability }}">
                        @endif
                        <div class="relative flex items-center bg-white dark:bg-[#1C1C1E] rounded-[16px] border border-black/[0.1] dark:border-white/[0.12] p-1.5 shadow-sm focus-within:ring-2 focus-within:ring-[#007AFF] transition">
                            <i data-lucide="search" class="w-5 h-5 ml-3.5 text-[#6E6E73] dark:text-[#86868B] shrink-0"></i>
                            <input type="text" name="q" value="{{ $search }}"
                                placeholder="Cari nama toko, jenis usaha, atau kota..."
                                class="w-full bg-transparent border-0 px-3.5 py-3 text-[16px] text-[#1D1D1F] dark:text-[#F5F5F7] placeholder-[#6E6E73]/50 focus:outline-none">
                            <button type="submit"
                                class="shrink-0 h-11 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-sm font-semibold shadow-sm active:scale-[0.98] transition">
                                Cari
                            </button>
                        </div>
                    </form>

                    <!-- Quick Reassurance badges -->
                    <div class="flex flex-wrap items-center gap-3 text-xs text-[#6E6E73] dark:text-[#86868B] pt-1">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759]"></i>
                            <span>{{ $totalStores }} Toko Terdaftar</span>
                        </span>
                        <span>•</span>
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="shield-check" class="w-4 h-4 text-[#007AFF]"></i>
                            <span>Identitas Pemilik Diverifikasi</span>
                        </span>
                        <span>•</span>
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="phone" class="w-4 h-4 text-[#FF9500]"></i>
                            <span>Chat Langsung via WhatsApp</span>
                        </span>
                    </div>
                </div>

                <!-- Right: Directory Ecosystem Preview (5 cols) -->
                <div class="lg:col-span-5">
                    <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] p-6 shadow-sm space-y-4">
                        <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#FF5F56]"></span>
                                <span class="w-3 h-3 rounded-full bg-[#FFBD2E]"></span>
                                <span class="w-3 h-3 rounded-full bg-[#27C93F]"></span>
                                <span class="text-xs font-mono font-semibold text-[#6E6E73] dark:text-[#86868B] ml-2">Direktori Bisnis Aktif</span>
                            </div>
                            <span class="text-[11px] font-semibold text-[#34C759] bg-[#34C759]/10 px-2.5 py-0.5 rounded-full flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Realtime
                            </span>
                        </div>

                        <!-- Mini Store Directory Spotlight -->
                        <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="text-xs font-bold text-[#007AFF] uppercase tracking-wider">Layanan Tersedia</div>
                                <span class="text-[11px] text-[#6E6E73] dark:text-[#86868B]">Seluruh Indonesia</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div class="p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] flex items-center gap-2 font-medium">
                                    <i data-lucide="store" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                    <span>Ambil di Toko</span>
                                </div>
                                <div class="p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] flex items-center gap-2 font-medium">
                                    <i data-lucide="bike" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                    <span>Kurir Lokal</span>
                                </div>
                                <div class="p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] flex items-center gap-2 font-medium">
                                    <i data-lucide="calendar-check" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                                    <span>Reservasi Meja</span>
                                </div>
                                <div class="p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] flex items-center gap-2 font-medium">
                                    <i data-lucide="truck" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                    <span>Pesanan PO</span>
                                </div>
                            </div>
                        </div>

                        <!-- Verification Footer -->
                        <div class="p-3 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] text-xs font-semibold flex items-center justify-between">
                            <span>Status Toko: Terdaftar Resmi</span>
                            <span class="font-mono">Bebas Biaya Perantara</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Filters Section -->
            <section class="space-y-4 pt-2">
                <!-- Category Filters -->
                <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
                    <a href="{{ route('public.discovery.index', array_filter(['q' => $search, 'fitur' => $capability])) }}"
                        class="h-10 px-4 rounded-[12px] text-xs sm:text-sm font-semibold whitespace-nowrap transition flex items-center gap-1.5 {{ empty($category) || $category === 'all' ? 'bg-[#1D1D1F] dark:bg-[#F5F5F7] text-white dark:text-black shadow-sm' : 'bg-white dark:bg-[#1C1C1E] text-[#1D1D1F] dark:text-[#F5F5F7] border border-black/[0.08] dark:border-white/[0.1] hover:border-[#007AFF]/30' }}">
                        Semua Kategori ({{ $totalStores }})
                    </a>
                    @foreach ($categories as $key => $cat)
                        <a href="{{ route('public.discovery.index', array_filter(['kategori' => $key, 'q' => $search, 'fitur' => $capability])) }}"
                            class="h-10 px-4 rounded-[12px] text-xs sm:text-sm font-semibold whitespace-nowrap transition flex items-center gap-1.5 {{ $category === $key ? 'bg-[#007AFF] text-white shadow-sm' : 'bg-white dark:bg-[#1C1C1E] text-[#1D1D1F] dark:text-[#F5F5F7] border border-black/[0.08] dark:border-white/[0.1] hover:border-[#007AFF]/30' }}">
                            <i data-lucide="{{ $cat['icon'] }}" class="w-4 h-4"></i>
                            <span>{{ $cat['label'] }} ({{ $cat['count'] }})</span>
                        </a>
                    @endforeach
                </div>

                <!-- Service Capabilities Filter -->
                <div class="flex items-center gap-2 overflow-x-auto text-xs pb-1 scrollbar-none">
                    <span class="text-[#6E6E73] dark:text-[#86868B] font-semibold mr-1 shrink-0">Opsi Layanan:</span>
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
                    @foreach ($capabilities as $capKey => $cap)
                        <a href="{{ route('public.discovery.index', array_filter(['fitur' => $capability === $capKey ? null : $capKey, 'kategori' => $category, 'q' => $search])) }}"
                            class="h-8 px-3 rounded-[10px] border transition flex items-center gap-1.5 whitespace-nowrap {{ $capability === $capKey ? 'bg-[#34C759]/15 border-[#34C759] text-[#248A3D] dark:text-[#30D158] font-bold' : 'border-black/[0.08] dark:border-white/[0.1] bg-white dark:bg-[#1C1C1E] text-[#6E6E73] dark:text-[#86868B] hover:text-[#1D1D1F]' }}">
                            <i data-lucide="{{ $cap['icon'] }}" class="w-3.5 h-3.5"></i>
                            <span>{{ $cap['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </section>

            <!-- Store Cards Grid -->
            @if ($businesses->isEmpty())
                <div class="bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/[0.08] dark:border-white/[0.1] p-12 text-center max-w-lg mx-auto space-y-4 shadow-sm">
                    <div class="w-14 h-14 rounded-full bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center mx-auto text-[#6E6E73] dark:text-[#86868B]">
                        <i data-lucide="store" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Toko Belum Ditemukan</h3>
                    <p class="text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                        Tidak ada toko yang sesuai dengan pencarian "{{ $search }}". Silakan gunakan kata kunci lain atau hapus filter kategori.
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('public.discovery.index') }}"
                            class="h-11 px-5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-semibold inline-flex items-center gap-2 shadow-sm transition">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                            <span>Reset Semua Filter</span>
                        </a>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($businesses as $store)
                        @php
                            $setting = $store->storeSetting;
                            $landing = $store->landingPage;
                            $primaryLoc = $store->locations->first();
                            $storeUrl = url('/' . $store->slug);
                        @endphp
                        <div class="bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/[0.08] dark:border-white/[0.1] p-6 flex flex-col justify-between hover:border-[#007AFF]/30 transition shadow-sm group">
                            <div class="space-y-4">
                                <!-- Store Header & Avatar -->
                                <div class="flex items-start gap-3.5">
                                    @if ($store->logo_path)
                                        <img src="{{ Storage::url($store->logo_path) }}" alt="{{ $store->name }}"
                                            class="w-13 h-13 rounded-[14px] object-cover border border-black/[0.06] dark:border-white/[0.08] shrink-0">
                                    @else
                                        <div class="w-13 h-13 rounded-[14px] bg-[#007AFF] text-white font-extrabold text-base flex items-center justify-center shrink-0">
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

                                        <div class="flex items-center gap-2 text-xs text-[#6E6E73] dark:text-[#86868B] mt-0.5">
                                            @if ($store->industry_category || $store->template_code)
                                                <span class="font-semibold text-[#007AFF] dark:text-[#0A84FF] capitalize">
                                                    {{ str_replace('_', ' ', $store->template_code ?: $store->industry_category) }}
                                                </span>
                                                <span>•</span>
                                            @endif
                                            <span class="truncate">
                                                {{ $store->address ?: $primaryLoc?->name ?? 'Indonesia' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Description / Headline -->
                                <p class="text-sm text-[#6E6E73] dark:text-[#86868B] line-clamp-2 leading-relaxed">
                                    {{ $landing?->headline ?: ($store->description ?: 'Melayani pemesanan langsung online, siap kirim atau ambil di toko dengan pelayanan ramah.') }}
                                </p>

                                <!-- Capabilities Badges -->
                                <div class="flex flex-wrap items-center gap-2 pt-1 text-xs">
                                    @if ($setting?->allow_delivery)
                                        <span class="inline-flex items-center gap-1 font-semibold text-[#248A3D] dark:text-[#30D158]">
                                            <i data-lucide="bike" class="w-3 h-3"></i>
                                            <span>Kurir Toko</span>
                                        </span>
                                    @endif
                                    @if ($setting?->allow_pickup)
                                        <span class="inline-flex items-center gap-1 font-semibold text-[#6E6E73] dark:text-[#86868B]">
                                            <i data-lucide="store" class="w-3 h-3"></i>
                                            <span>Ambil Sendiri</span>
                                        </span>
                                    @endif
                                    @if ($setting?->allow_scheduled_order)
                                        <span class="inline-flex items-center gap-1 font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                                            <i data-lucide="calendar-clock" class="w-3 h-3"></i>
                                            <span>Pre-Order</span>
                                        </span>
                                    @endif
                                    @if ($setting?->allow_request_order)
                                        <span class="inline-flex items-center gap-1 font-semibold text-[#FF9500]">
                                            <i data-lucide="file-question" class="w-3 h-3"></i>
                                            <span>Custom Order</span>
                                        </span>
                                    @endif
                                    @if ($setting?->allow_reservation)
                                        <span class="inline-flex items-center gap-1 font-semibold text-[#5856D6]">
                                            <i data-lucide="calendar-check" class="w-3 h-3"></i>
                                            <span>Reservasi</span>
                                        </span>
                                    @endif
                                    @if ($setting?->allow_customer_po)
                                        <span class="inline-flex items-center gap-1 font-semibold text-[#AF52DE]">
                                            <i data-lucide="truck" class="w-3 h-3"></i>
                                            <span>PO B2B</span>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Card Footer: Stats & CTA -->
                            <div class="pt-5 mt-5 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-3">
                                <div class="text-xs text-[#6E6E73] dark:text-[#86868B]">
                                    <span class="font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">{{ $store->products_count }}</span> Produk Aktif
                                </div>

                                <a href="{{ $storeUrl }}"
                                    class="h-10 px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-semibold flex items-center gap-1.5 shadow-sm transition active:scale-95">
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

            <!-- Call to action for merchants (Apple Inset Enterprise Banner) -->
            <section class="p-8 sm:p-12 rounded-[24px] bg-[#161618] border border-white/[0.08] text-white flex flex-col md:flex-row items-center justify-between gap-8 shadow-sm">
                <div class="space-y-2.5 max-w-xl text-center md:text-left">
                    <div class="text-xs font-semibold uppercase tracking-wider text-[#86868B] inline-flex items-center gap-1.5">
                        <i data-lucide="store" class="w-4 h-4 text-[#34C759]"></i>
                        <span>Pendaftaran Toko Baru</span>
                    </div>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                        Punya Usaha dan Ingin Tampil di Direktori Ini?
                    </h3>
                    <p class="text-sm text-[#86868B] leading-relaxed">
                        Buka etalase toko online gratis selamanya di COOCA. Kelola katalog produk, terima pesanan antar, dan terima pembayaran langsung tanpa potongan biaya per transaksi.
                    </p>
                </div>
                <a href="{{ route('register') }}"
                    class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-sm font-semibold shadow-sm shrink-0 transition active:scale-95 flex items-center gap-2">
                    <span>Buka Toko Gratis Sekarang</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </section>

        </div>
    </div>
@endsection

