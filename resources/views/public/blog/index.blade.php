@extends('layouts.public_marketing')

@section('title', 'Blog & Edukasi Bisnis UMKM | Panduan Finansial & Kasir - COOCA')
@section('description', 'Pusat edukasi dan panduan praktis UMKM Indonesia. Pelajari tutorial cara hitung HPP, BEP, pembukuan kas, serta wawasan seputar kasir POS digital dan AI bisnis.')
@section('og_title', 'Blog & Edukasi Bisnis UMKM | Panduan Finansial & Kasir - COOCA')
@section('og_description', 'Pusat edukasi dan panduan praktis UMKM Indonesia. Pelajari tutorial cara hitung HPP, BEP, pembukuan kas, serta wawasan seputar kasir POS digital.')
@section('canonical', route('blog.index'))
@section('og_type', 'website')
@section('keywords', 'blog umkm indonesia, tutorial pembukuan usaha, cara hitung hpp makanan, cara hitung bep, tips bisnis toko kecil, ai untuk umkm')

@push('seo')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "Blog",
  "name": "Blog & Edukasi Bisnis UMKM COOCA",
  "description": "Pusat panduan praktis, tips finansial, dan tutorial operasional UMKM Indonesia.",
  "url": "{{ route('blog.index') }}",
  "publisher": {
    "@type": "Organization",
    "name": "COOCA Indonesia",
    "url": "{{ url('/') }}"
  }
}
</script>
@endpush

@push('styles')
<style>
    /* ── Blog Responsive Overrides ── */
    /* Scrollable tabs with gradient hints */
    .blog-tabs-scroll {
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
        scroll-snap-type: x mandatory;
    }
    .blog-tabs-scroll::-webkit-scrollbar { display: none; }
    .blog-tabs-scroll > a { scroll-snap-align: start; }

    /* Gradient scroll hint for category pills */
    .blog-pills-wrap {
        position: relative;
    }
    .blog-pills-wrap::after {
        content: '';
        position: absolute;
        right: 0; top: 0; bottom: 0; width: 2.5rem;
        background: linear-gradient(to right, transparent, var(--blog-pills-bg, #F2F2F7));
        pointer-events: none;
        opacity: 1;
        transition: opacity .2s;
    }
    .dark .blog-pills-wrap::after {
        --blog-pills-bg: #000000;
    }

    /* Touch states on interactive elements */
    .blog-touch-target {
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
    }
    .blog-touch-target:active {
        transform: scale(0.98);
    }

    /* Pagination override for touch */
    .blog-pagination nav span,
    .blog-pagination nav a {
        min-width: 44px !important;
        min-height: 44px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 0.875rem !important;
        border-radius: 12px !important;
    }

    /* Mobile bottom bar (blog-specific) */
    .blog-bottom-bar {
        display: flex;
        position: fixed;
        inset: auto 0 0 0;
        z-index: 40;
        background: rgba(255,255,255,0.96);
        backdrop-filter: blur(20px) saturate(1.8);
        -webkit-backdrop-filter: blur(20px) saturate(1.8);
        border-top: 1px solid rgba(0,0,0,0.08);
        padding-bottom: env(safe-area-inset-bottom, 0px);
    }
    .dark .blog-bottom-bar {
        background: rgba(28,28,30,0.96);
        border-top-color: rgba(255,255,255,0.08);
    }
    .blog-bottom-bar a {
        flex: 1;
        min-height: 56px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 2px;
        color: #8E8E93;
        text-decoration: none;
        font-size: 0.625rem;
        font-weight: 600;
        letter-spacing: 0.01em;
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
        transition: color .15s;
    }
    .blog-bottom-bar a.active,
    .blog-bottom-bar a[aria-current="page"] {
        color: #007AFF;
    }
    .blog-bottom-bar svg,
    .blog-bottom-bar i {
        width: 22px;
        height: 22px;
    }

    /* Hide bottom bar on tablet+ */
    @media (min-width: 768px) {
        .blog-bottom-bar { display: none; }
    }

    /* Reserve space for bottom bar on mobile */
    .blog-page-wrap {
        padding-bottom: calc(56px + env(safe-area-inset-bottom, 0px));
    }
    @media (min-width: 768px) {
        .blog-page-wrap { padding-bottom: 0; }
    }

    /* Focus visible ring */
    .blog-focus:focus-visible {
        outline: 3px solid #007AFF;
        outline-offset: 2px;
    }
</style>
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
class="blog-page-wrap w-full font-sans antialiased bg-[#F2F2F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] transition-colors">

    {{-- ═══════════════════════════════════════════════════════════════════════
         1. HERO SECTION — Ringkas di Mobile, Penuh di Desktop
    ═══════════════════════════════════════════════════════════════════════ --}}
    <section class="relative bg-[#060B1E] text-white pt-10 sm:pt-14 lg:pt-20 pb-10 sm:pb-14 lg:pb-16 border-b border-white/[0.08] overflow-hidden">
        {{-- Subtle Glow --}}
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_60%_at_50%_-20%,rgba(0,122,255,0.15),transparent)] pointer-events-none"></div>

        <div class="relative max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Breadcrumb Navigation --}}
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-5 lg:mb-6" aria-label="Breadcrumb">
                <a href="{{ route('landing') }}" class="hover:text-white transition-colors py-1 blog-focus blog-touch-target">Beranda</a>
                <span aria-hidden="true" class="text-slate-600">/</span>
                <span class="text-[#0A84FF] font-semibold" aria-current="page">Blog &amp; Panduan Praktis</span>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-12 items-center">
                
                {{-- KIRI: Headline & Actions --}}
                <div class="lg:col-span-5 space-y-5 text-center mx-auto flex flex-col items-center lg:text-left lg:items-start lg:mx-0">
                    <div class="space-y-3 w-full">
                        {{-- Kicker --}}
                        <div class="text-xs font-bold uppercase tracking-wider text-[#0A84FF]">
                            PUSAT PANDUAN &amp; EDUKASI UMKM
                        </div>

                        <h1 class="text-[1.75rem] sm:text-4xl md:text-5xl lg:text-[2.65rem] xl:text-[3rem] font-extrabold tracking-tight text-white leading-[1.15] text-balance break-words max-w-[22rem] sm:max-w-xl lg:max-w-none mx-auto lg:mx-0">
                            Wawasan Praktis untuk Kembangkan Usaha Anda
                        </h1>
                    </div>

                    <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal text-pretty max-w-[32rem] lg:max-w-xl mx-auto lg:mx-0">
                        Pelajari cara menghitung modal pokok (HPP), titik impas (BEP), tips mengelola arus kas harian, dan tutorial kasir tanpa rumus yang rumit.
                    </p>

                    {{-- Trust Commitments — teks diperbesar ke minimum 12px --}}
                    <div class="grid grid-cols-3 gap-2 sm:gap-3 pt-1 text-left w-full">
                        <div class="p-3 sm:p-3.5 rounded-[14px] sm:rounded-[16px] bg-white/[0.04] border border-white/[0.08]">
                            <div class="text-xs font-bold text-white flex items-center gap-1.5 sm:gap-2">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span class="leading-snug">Bahasa Mudah</span>
                            </div>
                            <div class="text-xs text-slate-400 mt-1 leading-normal hidden sm:block">Bebas istilah asing</div>
                        </div>

                        <div class="p-3 sm:p-3.5 rounded-[14px] sm:rounded-[16px] bg-white/[0.04] border border-white/[0.08]">
                            <div class="text-xs font-bold text-white flex items-center gap-1.5 sm:gap-2">
                                <i data-lucide="calculator" class="w-4 h-4 text-[#0A84FF] shrink-0"></i>
                                <span class="leading-snug">Contoh Nyata</span>
                            </div>
                            <div class="text-xs text-slate-400 mt-1 leading-normal hidden sm:block">Studi kasus toko &amp; resto</div>
                        </div>

                        <div class="p-3 sm:p-3.5 rounded-[14px] sm:rounded-[16px] bg-white/[0.04] border border-white/[0.08]">
                            <div class="text-xs font-bold text-white flex items-center gap-1.5 sm:gap-2">
                                <i data-lucide="award" class="w-4 h-4 text-amber-400 shrink-0"></i>
                                <span class="leading-snug">Siap Pakai</span>
                            </div>
                            <div class="text-xs text-slate-400 mt-1 leading-normal hidden sm:block">Bisa diterapkan hari ini</div>
                        </div>
                    </div>

                    {{-- CTA Buttons — min-height 48px, full-width on mobile --}}
                    <div class="pt-1 flex flex-col sm:flex-row items-stretch sm:items-center sm:justify-center lg:justify-start gap-3 w-full sm:w-auto">
                        <a href="#katalog-artikel"
                            class="blog-touch-target blog-focus h-12 px-7 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm flex items-center justify-center gap-2 transition shadow-sm min-h-[48px]">
                            <span>Baca Artikel &amp; Panduan</span>
                            <i data-lucide="arrow-down" class="w-4 h-4 shrink-0"></i>
                        </a>
                        <a href="{{ route('kalkulator.index') }}"
                            class="blog-touch-target blog-focus h-12 px-6 rounded-[14px] bg-white/[0.06] hover:bg-white/[0.1] border border-white/[0.12] text-white text-sm font-semibold flex items-center justify-center gap-2 transition min-h-[48px]">
                            <i data-lucide="calculator" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                            <span>Coba Kalkulator Usaha</span>
                        </a>
                    </div>
                </div>

                {{-- KANAN: Knowledge Base Preview — HIDDEN on mobile to reduce scroll --}}
                <div class="hidden lg:block lg:col-span-7">
                    <div class="rounded-[24px] bg-white/[0.04] backdrop-blur-md p-6 sm:p-7 border border-white/[0.08] space-y-4">
                        <div class="flex items-center justify-between border-b border-white/[0.08] pb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#FF5F56]"></span>
                                <span class="w-3 h-3 rounded-full bg-[#FFBD2E]"></span>
                                <span class="w-3 h-3 rounded-full bg-[#27C93F]"></span>
                                <span class="text-xs font-mono font-semibold text-slate-300 ml-2">
                                    Topik Unggulan UMKM
                                </span>
                            </div>
                            <span class="text-xs font-semibold text-emerald-400 bg-emerald-500/10 px-2.5 py-0.5 rounded-full border border-emerald-500/20">
                                Bebas Akses
                            </span>
                        </div>

                        {{-- Top Topic Bento Highlights --}}
                        <div class="grid grid-cols-3 gap-3">
                            <a href="{{ route('kalkulator.hpp') }}" 
                                class="blog-touch-target blog-focus p-4 rounded-[16px] bg-white/[0.03] hover:bg-white/[0.06] transition border border-white/[0.06] hover:border-[#0A84FF]/40 group flex flex-col justify-between min-h-[44px]">
                                <div class="space-y-2">
                                    <div class="w-8 h-8 rounded-[10px] bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/20">
                                        01
                                    </div>
                                    <div class="text-xs font-bold text-white group-hover:text-[#0A84FF] transition">
                                        Rumus HPP Kuliner
                                    </div>
                                    <div class="text-xs text-slate-400 leading-normal">
                                        Bahan baku, porsi, dan takaran akurat.
                                    </div>
                                </div>
                                <div class="pt-3 flex items-center text-xs font-semibold text-[#0A84FF]">
                                    <span>Buka Alat</span>
                                    <i data-lucide="arrow-right" class="w-3 h-3 ml-1 group-hover:translate-x-0.5 transition-transform"></i>
                                </div>
                            </a>

                            <a href="{{ route('kalkulator.bep') }}" 
                                class="blog-touch-target blog-focus p-4 rounded-[16px] bg-white/[0.03] hover:bg-white/[0.06] transition border border-white/[0.06] hover:border-[#0A84FF]/40 group flex flex-col justify-between min-h-[44px]">
                                <div class="space-y-2">
                                    <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/15 text-[#0A84FF] flex items-center justify-center font-bold text-xs border border-[#0A84FF]/30">
                                        02
                                    </div>
                                    <div class="text-xs font-bold text-white group-hover:text-[#0A84FF] transition">
                                        Titik Impas (BEP)
                                    </div>
                                    <div class="text-xs text-slate-400 leading-normal">
                                        Berapa porsi minimal agar usaha tidak rugi.
                                    </div>
                                </div>
                                <div class="pt-3 flex items-center text-xs font-semibold text-[#0A84FF]">
                                    <span>Buka Alat</span>
                                    <i data-lucide="arrow-right" class="w-3 h-3 ml-1 group-hover:translate-x-0.5 transition-transform"></i>
                                </div>
                            </a>

                            <a href="{{ route('public.resources.guides') }}" 
                                class="blog-touch-target blog-focus p-4 rounded-[16px] bg-white/[0.03] hover:bg-white/[0.06] transition border border-white/[0.06] hover:border-[#0A84FF]/40 group flex flex-col justify-between min-h-[44px]">
                                <div class="space-y-2">
                                    <div class="w-8 h-8 rounded-[10px] bg-amber-500/15 text-amber-400 flex items-center justify-center font-bold text-xs border border-amber-500/30">
                                        03
                                    </div>
                                    <div class="text-xs font-bold text-white group-hover:text-[#0A84FF] transition">
                                        SOP Kasir Toko
                                    </div>
                                    <div class="text-xs text-slate-400 leading-normal">
                                        Cegah kebocoran kas dan selisih shift.
                                    </div>
                                </div>
                                <div class="pt-3 flex items-center text-xs font-semibold text-[#0A84FF]">
                                    <span>Buka Panduan</span>
                                    <i data-lucide="arrow-right" class="w-3 h-3 ml-1 group-hover:translate-x-0.5 transition-transform"></i>
                                </div>
                            </a>
                        </div>

                        <div class="p-3.5 rounded-[16px] bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i data-lucide="book-open" class="w-4 h-4 shrink-0"></i>
                                <span>Kurikulum dan artikel diperbarui setiap minggu secara gratis.</span>
                            </div>
                            <a href="{{ route('public.resources.blog') }}" class="blog-touch-target blog-focus font-bold underline text-xs shrink-0 ml-2 hover:text-emerald-300 py-2">
                                Kurikulum Lengkap
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════════
         2. BLOG CATALOG & SEARCH — Touch-Optimized
    ═══════════════════════════════════════════════════════════════════════ --}}
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-16 space-y-8 sm:space-y-10 lg:space-y-12">

        {{-- Filter Tabs & Search Bar --}}
        <section id="katalog-artikel" class="space-y-5 sm:space-y-6 scroll-mt-20">
            <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 pb-5 sm:pb-6 border-b border-black/[0.06] dark:border-white/[0.08]">
                
                {{-- Segmented Control Tabs — Touch-friendly 44px height, scroll-snap --}}
                <div class="blog-tabs-scroll flex items-center gap-1.5 p-1 rounded-[16px] bg-slate-200/60 dark:bg-[#1C1C1E] border border-black/[0.04] dark:border-white/[0.08] overflow-x-auto text-xs">
                    <a href="{{ route('blog.index') }}"
                        class="blog-touch-target blog-focus min-h-[44px] min-w-[44px] px-4 rounded-[12px] transition-all font-semibold whitespace-nowrap flex items-center {{ empty($currentCluster) ? 'bg-white dark:bg-[#2C2C2E] text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                        Semua Artikel
                    </a>
                    <a href="{{ route('blog.index', ['cluster' => 'tutorial']) }}"
                        class="blog-touch-target blog-focus min-h-[44px] min-w-[44px] px-4 rounded-[12px] transition-all font-semibold whitespace-nowrap flex items-center {{ $currentCluster === 'tutorial' ? 'bg-white dark:bg-[#2C2C2E] text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                        Tutorial &amp; Panduan
                    </a>
                    <a href="{{ route('blog.index', ['cluster' => 'edukasi']) }}"
                        class="blog-touch-target blog-focus min-h-[44px] min-w-[44px] px-4 rounded-[12px] transition-all font-semibold whitespace-nowrap flex items-center {{ $currentCluster === 'edukasi' ? 'bg-white dark:bg-[#2C2C2E] text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                        Wawasan Finansial
                    </a>
                </div>

                {{-- Search Form — input already 16px (anti-zoom iOS) --}}
                <form action="{{ route('blog.index') }}" method="GET" class="w-full md:w-80">
                    @if ($currentCluster)
                        <input type="hidden" name="cluster" value="{{ $currentCluster }}">
                    @endif
                    @if ($currentCategory)
                        <input type="hidden" name="category" value="{{ $currentCategory }}">
                    @endif
                    <div class="relative flex items-center">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5"></i>
                        <input type="text" name="q" value="{{ $search }}"
                            placeholder="Cari judul artikel atau topik..."
                            class="blog-focus w-full h-12 pl-10 pr-4 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[14px] text-slate-900 dark:text-white text-[16px] placeholder-slate-400 focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition shadow-xs">
                    </div>
                </form>
            </div>

            {{-- Category Pills Bar — 44px touch targets with scroll gradient hint --}}
            @if (isset($categories) && $categories->isNotEmpty())
                <div class="blog-pills-wrap">
                    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs no-scrollbar blog-tabs-scroll pr-8">
                        <span class="text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider text-xs shrink-0 mr-1">
                            Kategori:
                        </span>
                        <a href="{{ route('blog.index', request()->except(['category', 'page'])) }}"
                            class="blog-touch-target blog-focus min-h-[44px] px-4 py-2 rounded-[12px] font-semibold whitespace-nowrap transition flex items-center {{ empty($currentCategory) ? 'bg-[#007AFF] text-white shadow-xs' : 'bg-white dark:bg-[#1C1C1E] text-slate-600 dark:text-slate-300 border border-black/[0.06] dark:border-white/[0.08] hover:bg-slate-50 dark:hover:bg-[#2C2C2E]' }}">
                            Semua
                        </a>
                        @foreach ($categories as $cat)
                            <a href="{{ route('blog.index', array_merge(request()->except(['category', 'page']), ['category' => $cat])) }}"
                                class="blog-touch-target blog-focus min-h-[44px] px-4 py-2 rounded-[12px] font-semibold whitespace-nowrap transition flex items-center {{ ($currentCategory ?? '') === $cat ? 'bg-[#007AFF] text-white shadow-xs' : 'bg-white dark:bg-[#1C1C1E] text-slate-600 dark:text-slate-300 border border-black/[0.06] dark:border-white/[0.08] hover:bg-slate-50 dark:hover:bg-[#2C2C2E]' }}">
                                {{ $cat }}
                            </a>
                        @endforeach
                        @if (!empty($currentCategory))
                            <a href="{{ route('blog.index', request()->except(['category', 'page'])) }}"
                                class="blog-touch-target blog-focus text-xs text-rose-500 hover:underline ml-2 shrink-0 font-medium flex items-center gap-1 min-h-[44px] px-2">
                                <i data-lucide="x" class="w-4 h-4"></i>
                                <span>Reset</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Featured Post (Page 1 without filters) --}}
            @if ($featuredPost)
                <div class="bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 sm:p-8 lg:p-10 rounded-[20px] sm:rounded-[24px] overflow-hidden group shadow-sm hover:border-[#007AFF]/40 hover:shadow-lg transition-all">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 items-center">
                        <div class="lg:col-span-7 space-y-4">
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-bold uppercase tracking-wider px-3 py-1.5 rounded-[8px] {{ $featuredPost->cluster === 'tutorial' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]' }}">
                                    {{ $featuredPost->category }}
                                </span>
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                    {{ $featuredPost->read_time }} menit baca
                                </span>
                            </div>

                            <a href="{{ route('blog.show', $featuredPost->slug) }}" class="block">
                                <h2 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-slate-900 dark:text-white group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors leading-tight">
                                    {{ $featuredPost->title }}
                                </h2>
                            </a>

                            <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed line-clamp-3">
                                {{ $featuredPost->excerpt }}
                            </p>

                            <div class="pt-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                                    Ditulis oleh {{ $featuredPost->author_name }}
                                </div>
                                <a href="{{ route('blog.show', $featuredPost->slug) }}"
                                    class="blog-touch-target blog-focus min-h-[44px] px-5 rounded-[12px] bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs font-semibold flex items-center gap-1.5 transition shadow-xs active:scale-[0.98] w-full sm:w-auto justify-center">
                                    <span>Baca Lengkap</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        </div>

                        <div class="lg:col-span-5 h-52 sm:h-64 lg:h-72 rounded-[16px] sm:rounded-[18px] overflow-hidden relative border border-black/[0.06] dark:border-white/[0.08] bg-slate-100 dark:bg-[#2C2C2E]">
                            <img src="{{ $featuredPost->cover_image ?? 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80' }}"
                                alt="{{ $featuredPost->title }}"
                                loading="lazy"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                width="800" height="450">
                        </div>
                    </div>
                </div>
            @endif

            {{-- Post Grid — Cards as full tap targets --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                @forelse($posts as $post)
                    @if (!$featuredPost || $post->id !== $featuredPost->id)
                        <article class="bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] rounded-[18px] sm:rounded-[22px] overflow-hidden flex flex-col justify-between shadow-sm hover:border-[#007AFF]/40 hover:shadow-lg transition-all group">
                            {{-- Card wrapped in block link for full-card tap --}}
                            <a href="{{ route('blog.show', $post->slug) }}" class="blog-touch-target blog-focus block flex-1">
                                <div class="h-40 sm:h-44 lg:h-48 overflow-hidden relative border-b border-black/[0.06] dark:border-white/[0.08] bg-slate-100 dark:bg-[#2C2C2E]">
                                    <img src="{{ $post->cover_image ?? 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80' }}"
                                        alt="{{ $post->title }}"
                                        loading="lazy"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                        width="800" height="450">
                                    <span class="absolute top-3 left-3 px-2.5 py-1.5 rounded-[8px] text-xs font-bold uppercase tracking-wider {{ $post->cluster === 'tutorial' ? 'bg-emerald-600 text-white' : 'bg-[#007AFF] text-white' }}">
                                        {{ $post->category }}
                                    </span>
                                </div>

                                <div class="p-5 sm:p-6 space-y-2.5 sm:space-y-3">
                                    <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 font-mono">
                                        <span>{{ $post->published_at ? $post->published_at->format('d M Y') : '' }}</span>
                                        <span>•</span>
                                        <span>{{ $post->read_time }} menit baca</span>
                                    </div>

                                    <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors leading-snug line-clamp-2">
                                        {{ $post->title }}
                                    </h3>

                                    <p class="text-sm text-slate-600 dark:text-slate-300 line-clamp-2 leading-relaxed">
                                        {{ $post->excerpt }}
                                    </p>
                                </div>
                            </a>

                            <div class="px-5 sm:px-6 pb-5 sm:pb-6 pt-0 border-t border-black/[0.04] dark:border-white/[0.06] mt-auto flex items-center justify-between text-xs">
                                <span class="text-slate-500 dark:text-slate-400 font-medium truncate max-w-[140px]">
                                    {{ $post->author_name }}
                                </span>
                                <span class="font-bold text-[#007AFF] dark:text-[#0A84FF] flex items-center gap-1 group-hover:translate-x-0.5 transition-transform min-h-[44px] py-2">
                                    <span>Baca Artikel</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </span>
                            </div>
                        </article>
                    @endif
                @empty
                    <div class="col-span-full py-16 text-center space-y-3">
                        <div class="text-slate-400 dark:text-slate-500">
                            <i data-lucide="search-x" class="w-10 h-10 mx-auto mb-3 opacity-50"></i>
                        </div>
                        <p class="text-slate-500 dark:text-slate-400 text-sm">
                            Belum ada artikel untuk kategori atau pencarian ini.
                        </p>
                        <a href="{{ route('blog.index') }}" class="blog-touch-target blog-focus inline-flex items-center gap-1.5 text-sm font-semibold text-[#007AFF] min-h-[44px] px-4">
                            <i data-lucide="arrow-left" class="w-4 h-4"></i>
                            Lihat Semua Artikel
                        </a>
                    </div>
                @endforelse
            </div>

            {{-- Pagination — Touch-friendly override --}}
            <div class="flex justify-center pt-4 sm:pt-6 blog-pagination">
                {{ $posts->links() }}
            </div>
        </section>

    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════
         3. MOBILE BOTTOM NAVIGATION BAR
         Bottom bar (56px) with 4 key destinations — visible only on mobile
    ═══════════════════════════════════════════════════════════════════════ --}}
    <nav class="blog-bottom-bar md:hidden" aria-label="Navigasi blog">
        <a href="{{ route('landing') }}" class="blog-touch-target">
            <i data-lucide="home" class="w-5 h-5"></i>
            <span>Beranda</span>
        </a>
        <a href="{{ route('blog.index') }}" class="blog-touch-target active" aria-current="page">
            <i data-lucide="book-open" class="w-5 h-5"></i>
            <span>Blog</span>
        </a>
        <a href="{{ route('kalkulator.index') }}" class="blog-touch-target">
            <i data-lucide="calculator" class="w-5 h-5"></i>
            <span>Kalkulator</span>
        </a>
        <a href="{{ route('register') }}" class="blog-touch-target">
            <i data-lucide="user-plus" class="w-5 h-5"></i>
            <span>Daftar</span>
        </a>
    </nav>

</div>
@endsection
