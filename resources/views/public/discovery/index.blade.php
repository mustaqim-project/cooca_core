@extends('layouts.public_marketing')

@section('title', 'Jelajah Toko & Direktori Bisnis UMKM Indonesia | COOCA')
@section('description', 'Temukan ribuan toko online, kafe, restoran, butik, katering, dan layanan UMKM terpercaya di Indonesia. Belanja langsung tanpa perantara, pesan antar, atau reservasi meja online.')
@section('og_title', 'Jelajah Toko & Direktori Bisnis UMKM Indonesia | COOCA')
@section('og_description', 'Temukan ribuan toko online, kafe, restoran, butik, katering, dan layanan UMKM terpercaya di Indonesia. Belanja langsung tanpa perantara.')
@section('canonical', route('public.discovery.index'))
@section('og_type', 'website')
@section('keywords', 'direktori umkm, jelajah toko online, toko lokal terdekat, belanja langsung umkm, pesan antar makanan lokal, reservasi resto kafe')

@push('seo')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "CollectionPage",
  "name": "Jelajah Toko & Direktori Bisnis UMKM Indonesia COOCA",
  "description": "Direktori toko online dan profil resmi UMKM Indonesia terverifikasi.",
  "url": "{{ route('public.discovery.index') }}",
  "publisher": {
    "@type": "Organization",
    "name": "COOCA Indonesia",
    "url": "{{ url('/') }}"
  }
}
</script>
@endpush

@section('content')
<div x-data="{
    refreshIcons() {
        this.$nextTick(() => {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    }
}" x-init="refreshIcons()"
class="w-full font-sans antialiased bg-[#F2F2F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] transition-colors pb-24">

    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- ═══ 1. HERO SECTION: 2-Grid Bento Apple HIG Canvas ══════════════════════ -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- ═══ 1. HERO SECTION: 2-Grid Bento Apple HIG Canvas (Midnight Blue) ══════ -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <section class="relative bg-[#060B1E] text-white pt-12 sm:pt-16 lg:pt-20 pb-12 sm:pb-16 border-b border-white/[0.08] overflow-hidden">
        <!-- Subtle Glow -->
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_60%_at_50%_-20%,rgba(0,122,255,0.15),transparent)] pointer-events-none"></div>

        <div class="relative max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Breadcrumb Navigation -->
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-6" aria-label="Breadcrumb">
                <a href="{{ route('landing') }}" class="hover:text-white transition-colors">Beranda</a>
                <span aria-hidden="true" class="text-slate-600">/</span>
                <span class="text-[#0A84FF] font-semibold" aria-current="page">Jelajah &amp; Direktori Toko</span>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                
                <!-- KIRI: Headline, Search & Reassurance (Mobile Center, Desktop Left ~ 5 Cols) -->
                <div class="lg:col-span-5 space-y-6 text-center mx-auto flex flex-col items-center lg:text-left lg:items-start lg:mx-0">
                    <div class="space-y-3 w-full">
                        <!-- Pure Typographic Kicker -->
                        <div class="text-[12px] sm:text-[13px] font-bold uppercase tracking-wider text-[#0A84FF]">
                            DIREKTORI UMKM TERVERIFIKASI
                        </div>

                        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.65rem] xl:text-[3rem] font-extrabold tracking-tight text-white leading-[1.15] text-balance break-words max-w-[22rem] sm:max-w-xl lg:max-w-none mx-auto lg:mx-0">
                            Jelajah Profil &amp; Toko Resmi UMKM Lokal
                        </h1>
                    </div>

                    <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal text-pretty max-w-[32rem] lg:max-w-xl mx-auto lg:mx-0">
                        Temukan toko fisik, kafe, penyedia jasa servis, dan produsen kreatif di sekitar Anda. Transaksi langsung ke pemilik usaha tanpa biaya perantara tambahan.
                    </p>

                    <!-- Search Form inside Hero -->
                    <form method="GET" action="{{ route('public.discovery.index') }}" class="pt-1 w-full max-w-[32rem] lg:max-w-none">
                        @if ($category)
                            <input type="hidden" name="kategori" value="{{ $category }}">
                        @endif
                        @if ($capability)
                            <input type="hidden" name="fitur" value="{{ $capability }}">
                        @endif
                        <div class="relative flex items-center bg-white/[0.06] rounded-[16px] border border-white/[0.12] p-1.5 shadow-sm focus-within:ring-2 focus-within:ring-[#0A84FF]/30 focus-within:border-[#0A84FF] transition">
                            <i data-lucide="search" class="w-5 h-5 ml-3.5 text-slate-400 shrink-0"></i>
                            <input type="text" name="q" value="{{ $search }}"
                                placeholder="Cari nama toko, jenis usaha, atau kota..."
                                class="w-full bg-transparent border-0 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:outline-none">
                            <button type="submit"
                                class="shrink-0 h-10 px-5 rounded-[12px] bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs font-semibold shadow-xs active:scale-[0.98] transition flex items-center gap-1.5 min-h-[44px]">
                                <span>Cari</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5 shrink-0"></i>
                            </button>
                        </div>
                    </form>

                    <!-- Quick Reassurance Badges -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1 text-left w-full">
                        <div class="p-3.5 rounded-[16px] bg-white/[0.04] border border-white/[0.08]">
                            <div class="text-xs font-bold text-white flex items-center gap-1.5">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>{{ $totalStores }} Toko</span>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Terdaftar resmi</div>
                        </div>

                        <div class="p-3.5 rounded-[16px] bg-white/[0.04] border border-white/[0.08]">
                            <div class="text-xs font-bold text-white flex items-center gap-1.5">
                                <i data-lucide="shield-check" class="w-4 h-4 text-[#0A84FF] shrink-0"></i>
                                <span>Terverifikasi</span>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Identitas pemilik sah</div>
                        </div>

                        <div class="p-3.5 rounded-[16px] bg-white/[0.04] border border-white/[0.08]">
                            <div class="text-xs font-bold text-white flex items-center gap-1.5">
                                <i data-lucide="phone" class="w-4 h-4 text-amber-400 shrink-0"></i>
                                <span>Langsung</span>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Bebas komisi perantara</div>
                        </div>
                    </div>
                </div>

                <!-- KANAN: Directory Ecosystem Preview Widget (7 Cols ~ 58%) -->
                <div class="lg:col-span-7">
                    <div class="rounded-[24px] bg-white/[0.04] backdrop-blur-md p-6 sm:p-7 border border-white/[0.08] space-y-4">
                        <div class="flex items-center justify-between border-b border-white/[0.08] pb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#FF5F56]"></span>
                                <span class="w-3 h-3 rounded-full bg-[#FFBD2E]"></span>
                                <span class="w-3 h-3 rounded-full bg-[#27C93F]"></span>
                                <span class="text-xs font-mono font-semibold text-slate-300 ml-2">
                                    Direktori Bisnis Aktif
                                </span>
                            </div>
                            <span class="text-[11px] font-semibold text-emerald-400 bg-emerald-500/10 px-2.5 py-0.5 rounded-full flex items-center gap-1.5 border border-emerald-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                Realtime
                            </span>
                        </div>

                        <!-- Mini Store Directory Spotlight -->
                        <div class="p-4 rounded-[18px] bg-white/[0.03] border border-white/[0.06] space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="text-xs font-bold uppercase tracking-wider text-[#0A84FF]">
                                    Layanan Tersedia di Seluruh Indonesia
                                </div>
                                <span class="text-[11px] text-slate-400 font-medium">Bebas Potongan</span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-xs">
                                <div class="p-3 rounded-[12px] bg-white/[0.04] border border-white/[0.08] flex items-center gap-2 font-medium text-slate-200">
                                    <div class="w-7 h-7 rounded-[8px] bg-[#007AFF]/15 text-[#0A84FF] flex items-center justify-center shrink-0">
                                        <i data-lucide="store" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="truncate">Ambil di Toko</span>
                                </div>

                                <div class="p-3 rounded-[12px] bg-white/[0.04] border border-white/[0.08] flex items-center gap-2 font-medium text-slate-200">
                                    <div class="w-7 h-7 rounded-[8px] bg-emerald-500/15 text-emerald-400 flex items-center justify-center shrink-0">
                                        <i data-lucide="bike" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="truncate">Kurir Lokal</span>
                                </div>

                                <div class="p-3 rounded-[12px] bg-white/[0.04] border border-white/[0.08] flex items-center gap-2 font-medium text-slate-200">
                                    <div class="w-7 h-7 rounded-[8px] bg-indigo-500/15 text-indigo-400 flex items-center justify-center shrink-0">
                                        <i data-lucide="calendar-check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="truncate">Reservasi</span>
                                </div>

                                <div class="p-3 rounded-[12px] bg-white/[0.04] border border-white/[0.08] flex items-center gap-2 font-medium text-slate-200">
                                    <div class="w-7 h-7 rounded-[8px] bg-amber-500/15 text-amber-400 flex items-center justify-center shrink-0">
                                        <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="truncate">Pesanan PO</span>
                                </div>
                            </div>
                        </div>

                        <!-- Verification Footer -->
                        <div class="p-3.5 rounded-[16px] bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i data-lucide="shield-check" class="w-4 h-4 shrink-0"></i>
                                <span>Status Toko: Terdaftar Resmi &amp; Bebas Biaya Transaksi</span>
                            </div>
                            <a href="{{ route('register') }}" class="font-bold underline text-xs shrink-0 ml-2 hover:text-emerald-300">
                                Daftarkan Usaha Anda
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- ═══ 2. DIRECTORY CONTENT & FILTERS ═══════════════════════════════════════ -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 space-y-10">

        <!-- Filters Section -->
        <section class="space-y-4">
            <!-- Category Filters -->
            <div class="flex items-center gap-2 overflow-x-auto pb-2 no-scrollbar">
                <a href="{{ route('public.discovery.index', array_filter(['q' => $search, 'fitur' => $capability])) }}"
                    class="h-10 px-4 rounded-[12px] text-xs font-semibold whitespace-nowrap transition flex items-center gap-1.5 {{ empty($category) || $category === 'all' ? 'bg-[#007AFF] text-white shadow-xs' : 'bg-white dark:bg-[#1C1C1E] text-slate-700 dark:text-slate-300 border border-black/[0.06] dark:border-white/[0.08] hover:bg-slate-50 dark:hover:bg-[#2C2C2E]' }}">
                    Semua Kategori ({{ $totalStores }})
                </a>
                @foreach ($categories as $key => $cat)
                    <a href="{{ route('public.discovery.index', array_filter(['kategori' => $key, 'q' => $search, 'fitur' => $capability])) }}"
                        class="h-10 px-4 rounded-[12px] text-xs font-semibold whitespace-nowrap transition flex items-center gap-1.5 {{ $category === $key ? 'bg-[#007AFF] text-white shadow-xs' : 'bg-white dark:bg-[#1C1C1E] text-slate-700 dark:text-slate-300 border border-black/[0.06] dark:border-white/[0.08] hover:bg-slate-50 dark:hover:bg-[#2C2C2E]' }}">
                        <i data-lucide="{{ $cat['icon'] }}" class="w-4 h-4"></i>
                        <span>{{ $cat['label'] }} ({{ $cat['count'] }})</span>
                    </a>
                @endforeach
            </div>

            <!-- Service Capabilities Filter -->
            <div class="flex items-center gap-2 overflow-x-auto text-xs pb-1 no-scrollbar">
                <span class="text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider text-[11px] shrink-0 mr-1">
                    Opsi Layanan:
                </span>
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
                        class="h-8 px-3 rounded-[8px] border transition flex items-center gap-1.5 whitespace-nowrap {{ $capability === $capKey ? 'bg-emerald-500/15 border-emerald-500 text-emerald-600 dark:text-emerald-400 font-bold' : 'border-black/[0.06] dark:border-white/[0.08] bg-white dark:bg-[#1C1C1E] text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                        <i data-lucide="{{ $cap['icon'] }}" class="w-3.5 h-3.5"></i>
                        <span>{{ $cap['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </section>

        <!-- Store Cards Grid -->
        @if ($businesses->isEmpty())
            <div class="bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/[0.06] dark:border-white/[0.08] p-12 text-center max-w-lg mx-auto space-y-4 shadow-sm">
                <div class="w-14 h-14 rounded-full bg-slate-100 dark:bg-[#2C2C2E] flex items-center justify-center mx-auto text-slate-500 dark:text-slate-400">
                    <i data-lucide="store" class="w-7 h-7"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Toko Belum Ditemukan</h3>
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    Tidak ada toko yang sesuai dengan pencarian "{{ $search }}". Silakan gunakan kata kunci lain atau hapus filter kategori.
                </p>
                <div class="pt-2">
                    <a href="{{ route('public.discovery.index') }}"
                        class="h-11 px-5 rounded-[12px] bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs font-semibold inline-flex items-center gap-2 shadow-xs transition active:scale-[0.98]">
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
                    <div class="bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/[0.06] dark:border-white/[0.08] p-6 flex flex-col justify-between shadow-sm hover:border-[#007AFF]/40 hover:shadow-lg transition-all group">
                        <div class="space-y-4">
                            <!-- Store Header & Avatar -->
                            <div class="flex items-start gap-3.5">
                                @if ($store->logo_path)
                                    <img src="{{ Storage::url($store->logo_path) }}" alt="{{ $store->name }}"
                                        class="w-12 h-12 rounded-[14px] object-cover border border-black/[0.06] dark:border-white/[0.08] shrink-0">
                                @else
                                    <div class="w-12 h-12 rounded-[14px] bg-[#007AFF] text-white font-extrabold text-sm flex items-center justify-center shrink-0 shadow-xs">
                                        {{ Str::upper(substr($store->name, 0, 2)) }}
                                    </div>
                                @endif

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5">
                                        <h2 class="font-bold text-base text-slate-900 dark:text-white truncate group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition">
                                            {{ $store->name }}
                                        </h2>
                                        <i data-lucide="badge-check" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF] shrink-0"
                                            title="Toko Terverifikasi"></i>
                                    </div>

                                    <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                        @if ($store->industry_category || $store->template_code)
                                            <span class="font-semibold text-[#007AFF] dark:text-[#0A84FF] capitalize">
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
                            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 line-clamp-2 leading-relaxed">
                                {{ $landing?->headline ?: ($store->description ?: 'Melayani pemesanan langsung online, siap kirim atau ambil di toko dengan pelayanan ramah.') }}
                            </p>

                            <!-- Capabilities Badges -->
                            <div class="flex flex-wrap items-center gap-1.5 pt-1 text-[11px]">
                                @if ($setting?->allow_delivery)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[6px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-semibold">
                                        <i data-lucide="bike" class="w-3 h-3"></i>
                                        <span>Kurir Toko</span>
                                    </span>
                                @endif
                                @if ($setting?->allow_pickup)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[6px] bg-slate-100 dark:bg-[#2C2C2E] text-slate-700 dark:text-slate-300 font-semibold">
                                        <i data-lucide="store" class="w-3 h-3"></i>
                                        <span>Ambil Sendiri</span>
                                    </span>
                                @endif
                                @if ($setting?->allow_scheduled_order)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[6px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-semibold">
                                        <i data-lucide="calendar-clock" class="w-3 h-3"></i>
                                        <span>Pre-Order</span>
                                    </span>
                                @endif
                                @if ($setting?->allow_request_order)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[6px] bg-amber-500/10 text-amber-600 dark:text-amber-400 font-semibold">
                                        <i data-lucide="file-question" class="w-3 h-3"></i>
                                        <span>Custom Order</span>
                                    </span>
                                @endif
                                @if ($setting?->allow_reservation)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[6px] bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-semibold">
                                        <i data-lucide="calendar-check" class="w-3 h-3"></i>
                                        <span>Reservasi</span>
                                    </span>
                                @endif
                                @if ($setting?->allow_customer_po)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[6px] bg-purple-500/10 text-purple-600 dark:text-purple-400 font-semibold">
                                        <i data-lucide="truck" class="w-3 h-3"></i>
                                        <span>PO B2B</span>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Card Footer: Stats & CTA -->
                        <div class="pt-5 mt-5 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between gap-3">
                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                <span class="font-bold text-slate-900 dark:text-white">{{ $store->products_count }}</span>
                                Produk Aktif
                            </div>

                            <a href="{{ $storeUrl }}"
                                class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs font-semibold flex items-center gap-1.5 shadow-xs transition active:scale-[0.98]">
                                <span>Kunjungi Toko</span>
                                <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="pt-6 flex justify-center">
                {{ $businesses->links() }}
            </div>
        @endif

        <!-- ═══ CONVERSION BANNER FOR MERCHANTS ═══ -->
        <section class="p-8 sm:p-10 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="space-y-2 max-w-xl text-center md:text-left">
                <div class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF] inline-flex items-center gap-1.5">
                    <i data-lucide="store" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                    <span>PENDAFTARAN TOKO BARU</span>
                </div>
                <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Punya Usaha dan Ingin Tampil di Direktori Ini?
                </h3>
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    Buka etalase toko online gratis selamanya di COOCA. Kelola katalog produk, terima pesanan antar, dan terima pembayaran langsung tanpa potongan komisi per transaksi.
                </p>
            </div>
            <a href="{{ route('register') }}"
                class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white text-sm font-semibold shadow-xs shrink-0 transition active:scale-[0.98] flex items-center gap-2">
                <span>Buka Toko Gratis Sekarang</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </section>

    </div>
</div>
@endsection
