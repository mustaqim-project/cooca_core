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
                <span class="text-[#0A84FF] font-semibold" aria-current="page">Blog &amp; Panduan Praktis</span>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                
                <!-- KIRI: Headline, Value Proposition & Actions (Mobile Center, Desktop Left ~ 5 Cols) -->
                <div class="lg:col-span-5 space-y-6 text-center mx-auto flex flex-col items-center lg:text-left lg:items-start lg:mx-0">
                    <div class="space-y-3 w-full">
                        <!-- Pure Typographic Kicker -->
                        <div class="text-[12px] sm:text-[13px] font-bold uppercase tracking-wider text-[#0A84FF]">
                            PUSAT PANDUAN &amp; EDUKASI UMKM
                        </div>

                        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.65rem] xl:text-[3rem] font-extrabold tracking-tight text-white leading-[1.15] text-balance break-words max-w-[22rem] sm:max-w-xl lg:max-w-none mx-auto lg:mx-0">
                            Wawasan Praktis untuk Kembangkan Usaha Anda
                        </h1>
                    </div>

                    <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal text-pretty max-w-[32rem] lg:max-w-xl mx-auto lg:mx-0">
                        Pelajari cara menghitung modal pokok (HPP), titik impas (BEP), tips mengelola arus kas harian, dan tutorial kasir tanpa rumus yang rumit.
                    </p>

                    <!-- Trust Commitments for UMKM -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1 text-left w-full">
                        <div class="p-3.5 rounded-[16px] bg-white/[0.04] border border-white/[0.08]">
                            <div class="text-xs font-bold text-white flex items-center gap-2">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span class="leading-snug">Bahasa Mudah</span>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-1 leading-normal">Bebas istilah asing</div>
                        </div>

                        <div class="p-3.5 rounded-[16px] bg-white/[0.04] border border-white/[0.08]">
                            <div class="text-xs font-bold text-white flex items-center gap-2">
                                <i data-lucide="calculator" class="w-4 h-4 text-[#0A84FF] shrink-0"></i>
                                <span class="leading-snug">Contoh Nyata</span>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-1 leading-normal">Studi kasus toko &amp; resto</div>
                        </div>

                        <div class="p-3.5 rounded-[16px] bg-white/[0.04] border border-white/[0.08]">
                            <div class="text-xs font-bold text-white flex items-center gap-2">
                                <i data-lucide="award" class="w-4 h-4 text-amber-400 shrink-0"></i>
                                <span class="leading-snug">Siap Pakai</span>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-1 leading-normal">Bisa diterapkan hari ini</div>
                        </div>
                    </div>

                    <!-- Direct Action Buttons (Centered on Mobile, Row on Desktop) -->
                    <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center sm:justify-center lg:justify-start gap-3.5 w-full sm:w-auto">
                        <a href="#katalog-artikel"
                            class="h-12 px-7 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm flex items-center justify-center gap-2 active:scale-[0.98] transition shadow-sm min-h-[48px]">
                            <span>Baca Artikel &amp; Panduan</span>
                            <i data-lucide="arrow-down" class="w-4 h-4 shrink-0"></i>
                        </a>
                        <a href="{{ route('kalkulator.index') }}"
                            class="h-12 px-6 rounded-[14px] bg-white/[0.06] hover:bg-white/[0.1] border border-white/[0.12] text-white text-sm font-semibold flex items-center justify-center gap-2 transition active:scale-[0.98] min-h-[48px]">
                            <i data-lucide="calculator" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                            <span>Coba Kalkulator Usaha</span>
                        </a>
                    </div>
                </div>

                <!-- KANAN: Knowledge Base Preview Widget (7 Cols ~ 58%) -->
                <div class="lg:col-span-7">
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
                            <span class="text-[11px] font-semibold text-emerald-400 bg-emerald-500/10 px-2.5 py-0.5 rounded-full border border-emerald-500/20">
                                Bebas Akses
                            </span>
                        </div>

                        <!-- Top Topic Bento Highlights -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <a href="{{ route('kalkulator.hpp') }}" 
                                class="p-4 rounded-[16px] bg-white/[0.03] hover:bg-white/[0.06] transition border border-white/[0.06] hover:border-[#0A84FF]/40 group flex flex-col justify-between">
                                <div class="space-y-2">
                                    <div class="w-8 h-8 rounded-[10px] bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/20">
                                        01
                                    </div>
                                    <div class="text-xs font-bold text-white group-hover:text-[#0A84FF] transition">
                                        Rumus HPP Kuliner
                                    </div>
                                    <div class="text-[11px] text-slate-400 leading-normal">
                                        Bahan baku, porsi, dan takaran akurat.
                                    </div>
                                </div>
                                <div class="pt-3 flex items-center text-[11px] font-semibold text-[#0A84FF]">
                                    <span>Buka Alat</span>
                                    <i data-lucide="arrow-right" class="w-3 h-3 ml-1 group-hover:translate-x-0.5 transition-transform"></i>
                                </div>
                            </a>

                            <a href="{{ route('kalkulator.bep') }}" 
                                class="p-4 rounded-[16px] bg-white/[0.03] hover:bg-white/[0.06] transition border border-white/[0.06] hover:border-[#0A84FF]/40 group flex flex-col justify-between">
                                <div class="space-y-2">
                                    <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/15 text-[#0A84FF] flex items-center justify-center font-bold text-xs border border-[#0A84FF]/30">
                                        02
                                    </div>
                                    <div class="text-xs font-bold text-white group-hover:text-[#0A84FF] transition">
                                        Titik Impas (BEP)
                                    </div>
                                    <div class="text-[11px] text-slate-400 leading-normal">
                                        Berapa porsi minimal agar usaha tidak rugi.
                                    </div>
                                </div>
                                <div class="pt-3 flex items-center text-[11px] font-semibold text-[#0A84FF]">
                                    <span>Buka Alat</span>
                                    <i data-lucide="arrow-right" class="w-3 h-3 ml-1 group-hover:translate-x-0.5 transition-transform"></i>
                                </div>
                            </a>

                            <a href="{{ route('public.resources.guides') }}" 
                                class="p-4 rounded-[16px] bg-white/[0.03] hover:bg-white/[0.06] transition border border-white/[0.06] hover:border-[#0A84FF]/40 group flex flex-col justify-between">
                                <div class="space-y-2">
                                    <div class="w-8 h-8 rounded-[10px] bg-amber-500/15 text-amber-400 flex items-center justify-center font-bold text-xs border border-amber-500/30">
                                        03
                                    </div>
                                    <div class="text-xs font-bold text-white group-hover:text-[#0A84FF] transition">
                                        SOP Kasir Toko
                                    </div>
                                    <div class="text-[11px] text-slate-400 leading-normal">
                                        Cegah kebocoran kas dan selisih shift.
                                    </div>
                                </div>
                                <div class="pt-3 flex items-center text-[11px] font-semibold text-[#0A84FF]">
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
                            <a href="{{ route('public.resources.blog') }}" class="font-bold underline text-xs shrink-0 ml-2 hover:text-emerald-300">
                                Kurikulum Lengkap
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- ═══ 2. BLOG CATALOG & SEARCH ═════════════════════════════════════════════ -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 space-y-12">

        <!-- Filter Tabs & Search Bar -->
        <section id="katalog-artikel" class="space-y-6 scroll-mt-24">
            <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 pb-6 border-b border-black/[0.06] dark:border-white/[0.08]">
                
                <!-- Segmented Control Tabs -->
                <div class="flex items-center gap-1.5 p-1 rounded-[16px] bg-slate-200/60 dark:bg-[#1C1C1E] border border-black/[0.04] dark:border-white/[0.08] overflow-x-auto text-xs">
                    <a href="{{ route('blog.index') }}"
                        class="h-10 px-4 rounded-[12px] transition-all font-semibold whitespace-nowrap flex items-center {{ empty($currentCluster) ? 'bg-white dark:bg-[#2C2C2E] text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                        Semua Artikel
                    </a>
                    <a href="{{ route('blog.index', ['cluster' => 'tutorial']) }}"
                        class="h-10 px-4 rounded-[12px] transition-all font-semibold whitespace-nowrap flex items-center {{ $currentCluster === 'tutorial' ? 'bg-white dark:bg-[#2C2C2E] text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                        Tutorial &amp; Panduan Praktis
                    </a>
                    <a href="{{ route('blog.index', ['cluster' => 'edukasi']) }}"
                        class="h-10 px-4 rounded-[12px] transition-all font-semibold whitespace-nowrap flex items-center {{ $currentCluster === 'edukasi' ? 'bg-white dark:bg-[#2C2C2E] text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                        Wawasan Finansial
                    </a>
                </div>

                <!-- Search Form -->
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
                            class="w-full h-11 pl-10 pr-4 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[14px] text-slate-900 dark:text-white text-[16px] sm:text-sm placeholder-slate-400 focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition shadow-xs">
                    </div>
                </form>
            </div>

            <!-- Category Pills Bar -->
            @if (isset($categories) && $categories->isNotEmpty())
                <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs no-scrollbar">
                    <span class="text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider text-[11px] shrink-0 mr-1">
                        Kategori:
                    </span>
                    <a href="{{ route('blog.index', request()->except(['category', 'page'])) }}"
                        class="px-3.5 py-1.5 rounded-[10px] font-semibold whitespace-nowrap transition {{ empty($currentCategory) ? 'bg-[#007AFF] text-white shadow-xs' : 'bg-white dark:bg-[#1C1C1E] text-slate-600 dark:text-slate-300 border border-black/[0.06] dark:border-white/[0.08] hover:bg-slate-50 dark:hover:bg-[#2C2C2E]' }}">
                        Semua
                    </a>
                    @foreach ($categories as $cat)
                        <a href="{{ route('blog.index', array_merge(request()->except(['category', 'page']), ['category' => $cat])) }}"
                            class="px-3.5 py-1.5 rounded-[10px] font-semibold whitespace-nowrap transition {{ ($currentCategory ?? '') === $cat ? 'bg-[#007AFF] text-white shadow-xs' : 'bg-white dark:bg-[#1C1C1E] text-slate-600 dark:text-slate-300 border border-black/[0.06] dark:border-white/[0.08] hover:bg-slate-50 dark:hover:bg-[#2C2C2E]' }}">
                            {{ $cat }}
                        </a>
                    @endforeach
                    @if (!empty($currentCategory))
                        <a href="{{ route('blog.index', request()->except(['category', 'page'])) }}"
                            class="text-xs text-rose-500 hover:underline ml-2 shrink-0 font-medium flex items-center gap-1">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            <span>Reset Filter</span>
                        </a>
                    @endif
                </div>
            @endif

            <!-- Featured Post (If on Page 1 without filters) -->
            @if ($featuredPost)
                <div class="bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-6 sm:p-8 lg:p-10 rounded-[24px] overflow-hidden group shadow-sm hover:border-[#007AFF]/40 hover:shadow-lg transition-all">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                        <div class="lg:col-span-7 space-y-4">
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-[8px] {{ $featuredPost->cluster === 'tutorial' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]' }}">
                                    {{ $featuredPost->category }}
                                </span>
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                    {{ $featuredPost->read_time }} menit baca
                                </span>
                            </div>

                            <a href="{{ route('blog.show', $featuredPost->slug) }}" class="block">
                                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors leading-tight">
                                    {{ $featuredPost->title }}
                                </h2>
                            </a>

                            <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed line-clamp-3">
                                {{ $featuredPost->excerpt }}
                            </p>

                            <div class="pt-4 flex items-center justify-between border-t border-black/[0.06] dark:border-white/[0.08]">
                                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                                    Ditulis oleh {{ $featuredPost->author_name }}
                                </div>
                                <a href="{{ route('blog.show', $featuredPost->slug) }}"
                                    class="h-10 px-5 rounded-[12px] bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs font-semibold flex items-center gap-1.5 transition shadow-xs active:scale-[0.98]">
                                    <span>Baca Lengkap</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        </div>

                        <div class="lg:col-span-5 h-60 sm:h-72 rounded-[18px] overflow-hidden relative border border-black/[0.06] dark:border-white/[0.08] bg-slate-100 dark:bg-[#2C2C2E]">
                            <img src="{{ $featuredPost->cover_image ?? 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80' }}"
                                alt="{{ $featuredPost->title }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </div>
                    </div>
                </div>
            @endif

            <!-- Post Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($posts as $post)
                    @if (!$featuredPost || $post->id !== $featuredPost->id)
                        <article class="bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] rounded-[22px] overflow-hidden flex flex-col justify-between shadow-sm hover:border-[#007AFF]/40 hover:shadow-lg transition-all group">
                            <div>
                                <div class="h-44 sm:h-48 overflow-hidden relative border-b border-black/[0.06] dark:border-white/[0.08] bg-slate-100 dark:bg-[#2C2C2E]">
                                    <img src="{{ $post->cover_image ?? 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80' }}"
                                        alt="{{ $post->title }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    <span class="absolute top-3 left-3 px-2.5 py-1 rounded-[8px] text-[11px] font-bold uppercase tracking-wider {{ $post->cluster === 'tutorial' ? 'bg-emerald-600 text-white' : 'bg-[#007AFF] text-white' }}">
                                        {{ $post->category }}
                                    </span>
                                </div>

                                <div class="p-6 space-y-3">
                                    <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 font-mono">
                                        <span>{{ $post->published_at ? $post->published_at->format('d M Y') : '' }}</span>
                                        <span>•</span>
                                        <span>{{ $post->read_time }} menit baca</span>
                                    </div>

                                    <a href="{{ route('blog.show', $post->slug) }}" class="block">
                                        <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors leading-snug line-clamp-2">
                                            {{ $post->title }}
                                        </h3>
                                    </a>

                                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 line-clamp-2 leading-relaxed">
                                        {{ $post->excerpt }}
                                    </p>
                                </div>
                            </div>

                            <div class="p-6 pt-0 border-t border-black/[0.04] dark:border-white/[0.06] mt-4 flex items-center justify-between text-xs">
                                <span class="text-slate-500 dark:text-slate-400 font-medium truncate max-w-[140px]">
                                    {{ $post->author_name }}
                                </span>
                                <a href="{{ route('blog.show', $post->slug) }}"
                                    class="font-bold text-[#007AFF] dark:text-[#0A84FF] flex items-center gap-1 group-hover:translate-x-0.5 transition-transform">
                                    <span>Baca Artikel</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        </article>
                    @endif
                @empty
                    <div class="col-span-full py-16 text-center text-slate-500 dark:text-slate-400 text-sm">
                        Belum ada artikel untuk kategori atau pencarian ini.
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="flex justify-center pt-6">
                {{ $posts->links() }}
            </div>
        </section>

    </div>
</div>
@endsection
