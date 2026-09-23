@extends('layouts.public_marketing')

@section('title', ($post->meta_title ?? $post->title) . ' | COOCA Edukasi UMKM')
@section('description', $post->meta_description ?? $post->excerpt)
@section('og_title', ($post->meta_title ?? $post->title) . ' | COOCA')
@section('og_description', $post->meta_description ?? $post->excerpt)
@section('canonical', route('blog.show', $post->slug))
@section('og_type', 'article')
@section('og_image', $post->cover_image ?? 'https://cooca.id/assets/image/cooca.png')

@push('seo')

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "Article",
        "headline": "{{ addslashes($post->title) }}",
        "description": "{{ addslashes($post->excerpt) }}",
        "image": "{{ $post->cover_image ?? 'https://cooca.id/assets/image/cooca.png' }}",
        "author": {
            "@type": "Organization",
            "name": "{{ addslashes($post->author_name) }}"
        },
        "publisher": {
            "@type": "Organization",
            "name": "COOCA Indonesia",
            "logo": {
                "@type": "ImageObject",
                "url": "https://cooca.id/assets/image/1785229034_logo_dark.png"
            }
        },
        "datePublished": "{{ $post->published_at ? $post->published_at->toIso8601String() : $post->created_at->toIso8601String() }}",
        "dateModified": "{{ $post->updated_at->toIso8601String() }}"
    }
    </script>
@endpush

@section('content')
<div x-data="{
    scrollPercent: 0,
    fontSize: 'normal',
    copiedLink: false,
    headings: [],
    activeHeading: '',
    init() {
        this.updateScroll();
        this.generateTOC();
        this.refreshIcons();
    },
    updateScroll() {
        const article = document.getElementById('article-content');
        if (!article) return;
        const totalHeight = article.clientHeight;
        const windowHeight = window.innerHeight;
        const scrollY = window.scrollY || window.pageYOffset;
        const articleTop = article.offsetTop;
        
        if (scrollY < articleTop) {
            this.scrollPercent = 0;
        } else if (scrollY > articleTop + totalHeight - windowHeight) {
            this.scrollPercent = 100;
        } else {
            this.scrollPercent = Math.round(((scrollY - articleTop) / (totalHeight - windowHeight)) * 100);
        }
    },
    generateTOC() {
        this.$nextTick(() => {
            const article = document.getElementById('article-content');
            if (!article) return;
            const headingEls = article.querySelectorAll('h2, h3');
            const items = [];
            headingEls.forEach((el, index) => {
                if (!el.id) {
                    el.id = 'heading-' + index;
                }
                items.push({
                    id: el.id,
                    text: el.innerText,
                    level: el.tagName.toLowerCase()
                });
            });
            this.headings = items;
            if (items.length > 0) {
                this.activeHeading = items[0].id;
            }
        });
    },
    scrollToHeading(id) {
        const el = document.getElementById(id);
        if (el) {
            const offset = 90;
            const bodyRect = document.body.getBoundingClientRect().top;
            const elementRect = el.getBoundingClientRect().top;
            const elementPosition = elementRect - bodyRect;
            const offsetPosition = elementPosition - offset;
            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });
            this.activeHeading = id;
        }
    },
    copyArticleLink() {
        navigator.clipboard.writeText(window.location.href).then(() => {
            this.copiedLink = true;
            setTimeout(() => { this.copiedLink = false; }, 2500);
        });
    },
    refreshIcons() {
        this.$nextTick(() => {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    }
}" 
@scroll.window="updateScroll()" 
x-init="init()"
class="w-full font-sans antialiased bg-[#F2F2F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] transition-colors pb-24">

    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- ═══ 1. STICKY TOP READING PROGRESS BAR ═══════════════════════════════════ -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <div class="fixed top-0 left-0 right-0 z-50 h-1 bg-slate-200/50 dark:bg-white/10 pointer-events-none">
        <div class="h-full bg-[#007AFF] dark:bg-[#0A84FF] transition-all duration-100 ease-out"
            :style="'width: ' + scrollPercent + '%'"></div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- ═══ 2. ARTICLE HERO & METADATA SECTION ═══════════════════════════════════ -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <header class="pt-10 sm:pt-14 pb-10 sm:pb-12 border-b border-black/[0.06] dark:border-white/[0.08] bg-white dark:bg-[#151B2B]">
        <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400" aria-label="Breadcrumb">
                <a href="{{ route('landing') }}" class="hover:text-slate-900 dark:hover:text-white transition-colors">Beranda</a>
                <span aria-hidden="true" class="text-slate-300 dark:text-slate-700">/</span>
                <a href="{{ route('blog.index') }}" class="hover:text-slate-900 dark:hover:text-white transition-colors">Katalog Blog</a>
                <span aria-hidden="true" class="text-slate-300 dark:text-slate-700">/</span>
                <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold truncate max-w-[200px] sm:max-w-none" aria-current="page">
                    {{ $post->category ?? 'Edukasi UMKM' }}
                </span>
            </nav>

            <div class="max-w-4xl space-y-4">
                
                <!-- Category Kicker & Reading Time Badges -->
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]">
                        {{ $post->category ?? 'Panduan Bisnis' }}
                    </span>
                    <span class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5 font-medium">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                        <span>{{ $post->read_time ?? 3 }} menit baca</span>
                    </span>
                    <span class="text-xs text-slate-300 dark:text-slate-700">•</span>
                    <span class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5 font-medium">
                        <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                        <span>{{ $post->published_at ? $post->published_at->format('d M Y') : $post->created_at->format('d M Y') }}</span>
                    </span>
                    @if ($post->views_count > 0)
                        <span class="text-xs text-slate-300 dark:text-slate-700">•</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5 font-medium">
                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                            <span>{{ number_format($post->views_count) }} kali dibaca</span>
                        </span>
                    @endif
                </div>

                <!-- Main Article Title (H1) -->
                <h1 class="text-3xl sm:text-4xl lg:text-[2.75rem] font-extrabold text-slate-900 dark:text-white leading-[1.2] tracking-tight text-balance">
                    {{ $post->title }}
                </h1>

                <!-- Excerpt Subtitle -->
                @if ($post->excerpt)
                    <p class="text-base sm:text-lg text-slate-600 dark:text-slate-300 leading-relaxed font-normal pt-1 text-pretty">
                        {{ $post->excerpt }}
                    </p>
                @endif

                <!-- Author & Reading Accessibility Toolbar -->
                <div class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    
                    <!-- Author Information -->
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center font-bold text-sm shrink-0">
                            <i data-lucide="user-check" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-slate-900 dark:text-white leading-tight">
                                {{ $post->author_name ?? 'Tim Edukasi COOCA' }}
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 leading-tight mt-0.5">
                                Ditinjau untuk standar operasional UMKM Indonesia
                            </div>
                        </div>
                    </div>

                    <!-- Reading Tools: Font Resizer & Quick Sharing -->
                    <div class="flex items-center gap-2">
                        <!-- Font Size Toggle -->
                        <div class="flex items-center bg-[#F2F2F7] dark:bg-[#1C1C1E] rounded-[10px] p-1 border border-black/[0.06] dark:border-white/[0.08]">
                            <button @click="fontSize = 'normal'" 
                                :class="fontSize === 'normal' ? 'bg-white dark:bg-[#2C2C2E] text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-500 dark:text-slate-400 font-medium'"
                                class="px-2.5 py-1 text-xs rounded-[7px] transition-all" title="Ukuran Font Standar">
                                A
                            </button>
                            <button @click="fontSize = 'large'" 
                                :class="fontSize === 'large' ? 'bg-white dark:bg-[#2C2C2E] text-[#007AFF] dark:text-[#0A84FF] shadow-xs font-bold' : 'text-slate-500 dark:text-slate-400 font-medium'"
                                class="px-2.5 py-1 text-sm rounded-[7px] transition-all font-semibold" title="Ukuran Font Besar (Ramah 40-65 Thn)">
                                A+
                            </button>
                        </div>

                        <!-- WhatsApp Share -->
                        <a href="https://api.whatsapp.com/send?text={{ urlencode($post->title . ' - Baca panduan selengkapnya di COOCA: ' . url()->current()) }}"
                            target="_blank" rel="noopener noreferrer"
                            class="h-9 px-3 rounded-[10px] bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold flex items-center gap-1.5 transition shadow-sm"
                            title="Bagikan ke WhatsApp">
                            <i data-lucide="share-2" class="w-3.5 h-3.5"></i>
                            <span class="hidden sm:inline">Kirim WA</span>
                        </a>

                        <!-- Copy Link Button -->
                        <button @click="copyArticleLink()" 
                            class="h-9 px-3 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white text-xs font-semibold flex items-center gap-1.5 transition">
                            <i data-lucide="link" class="w-3.5 h-3.5"></i>
                            <span x-text="copiedLink ? 'Tersalin!' : 'Salin'">Salin</span>
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </header>

    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- ═══ 3. MAIN ARTICLE GRID: CONTENT (8 COLS) + SIDEBAR (4 COLS) ═══════════ -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
            
            <!-- ═══ KOLOM UTAMA ARTIKEL (8 COLS) ═══ -->
            <main class="lg:col-span-8 space-y-8">
                
                <!-- Featured Cover Image (if available) -->
                @if ($post->cover_image)
                    <div class="rounded-[20px] overflow-hidden max-h-[460px] w-full border border-black/[0.06] dark:border-white/[0.08] shadow-sm bg-slate-100 dark:bg-[#1C1C1E]">
                        <img src="{{ $post->cover_image }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                    </div>
                @endif

                <!-- Executive Summary Box (Ringkasan Poin Kunci 30 Detik) -->
                <div class="p-5 sm:p-6 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span>Ringkasan Inti Artikel (30 Detik)</span>
                    </div>
                    <div class="text-sm sm:text-base text-slate-700 dark:text-slate-300 leading-relaxed space-y-1.5">
                        <p>{{ $post->excerpt ?? 'Pelajari langkah praktis dalam artikel ini untuk menata pembukuan kasir, menekan biaya bahan baku, dan menghindari kebocoran uang kas operasional gerai Anda.' }}</p>
                    </div>
                </div>

                <!-- Main Article Body -->
                <article id="article-content" 
                    class="bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/[0.06] dark:border-white/[0.08] p-6 sm:p-10 lg:p-12 shadow-sm text-slate-900 dark:text-white transition-all">
                    
                    <div class="prose max-w-none text-slate-800 dark:text-slate-200 dark:prose-invert 
                        prose-headings:font-bold prose-headings:text-slate-900 dark:prose-headings:text-white 
                        prose-h2:text-2xl sm:prose-h2:text-3xl prose-h2:mt-10 prose-h2:mb-4 prose-h2:scroll-mt-24
                        prose-h3:text-xl sm:prose-h3:text-2xl prose-h3:mt-8 prose-h3:mb-3 prose-h3:scroll-mt-24
                        prose-p:leading-relaxed prose-p:mb-5
                        prose-strong:text-slate-900 dark:prose-strong:text-white prose-strong:font-bold
                        prose-a:text-[#007AFF] dark:prose-a:text-[#0A84FF] prose-a:underline hover:prose-a:opacity-80
                        prose-ul:my-5 prose-ul:list-disc prose-ul:pl-6 prose-li:my-1.5
                        prose-ol:my-5 prose-ol:list-decimal prose-ol:pl-6 prose-li:my-1.5
                        prose-table:border-collapse prose-table:w-full prose-td:p-3 prose-th:p-3 prose-th:bg-slate-100 dark:prose-th:bg-[#2C2C2E]
                        prose-blockquote:border-l-4 prose-blockquote:border-[#007AFF] prose-blockquote:pl-4 prose-blockquote:italic prose-blockquote:text-slate-600 dark:prose-blockquote:text-slate-400"
                        :class="fontSize === 'large' ? 'text-lg sm:text-xl leading-loose' : 'text-base sm:text-lg leading-relaxed'">
                        {!! $post->content !!}
                    </div>

                    <!-- Bottom Verification & Reassurance -->
                    <div class="mt-12 pt-6 border-t border-black/[0.06] dark:border-white/[0.08] flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs text-slate-500 dark:text-slate-400">
                        <div class="flex items-center gap-2">
                            <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                            <span>Materi ini disesuaikan dengan regulasi perpajakan dan operasional UMKM Indonesia.</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="copyArticleLink()" class="font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline flex items-center gap-1">
                                <i data-lucide="share" class="w-3.5 h-3.5"></i>
                                <span>Bagikan Panduan</span>
                            </button>
                        </div>
                    </div>

                </article>

                <!-- Mid-Article Action Bento: Cobalah Langsung di Usaha Anda -->
                <div class="p-6 sm:p-8 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm flex flex-col sm:flex-row items-center justify-between gap-6">
                    <div class="space-y-2 text-center sm:text-left">
                        <div class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                            PRAKTIKKAN SEKARANG
                        </div>
                        <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">
                            Kelola Kasir &amp; Laporan Keuangan Secara Otomatis
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 max-w-lg leading-relaxed">
                            COOCA menyediakan sistem kasir gratis selamanya untuk UMKM. Mulai catat penjualan dari smartphone Anda tanpa biaya instalasi.
                        </p>
                    </div>
                    <a href="{{ route('register') }}"
                        class="h-12 px-7 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-xs sm:text-sm shrink-0 flex items-center gap-2 shadow-sm transition active:scale-[0.98]">
                        <span>Daftar Akun Gratis</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>

            </main>

            <!-- ═══ KOLOM SIDEBAR (4 COLS) ═══ -->
            <aside class="lg:col-span-4 space-y-6 lg:sticky lg:top-20">
                
                <!-- SIDEBAR WIDGET 1: DAFTAR ISI OTOMATIS (TABLE OF CONTENTS) -->
                <div x-show="headings.length > 0" 
                    class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                        <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                            <i data-lucide="list" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]"></i>
                            <span>Daftar Isi Artikel</span>
                        </div>
                        <span class="text-[11px] font-mono font-semibold text-[#007AFF] dark:text-[#0A84FF]" x-text="scrollPercent + '%'"></span>
                    </div>

                    <!-- Navigation Links generated from H2 and H3 -->
                    <nav class="space-y-1.5 max-h-[360px] overflow-y-auto pr-1 text-xs">
                        <template x-for="(item, idx) in headings" :key="idx">
                            <button @click="scrollToHeading(item.id)" 
                                :class="activeHeading === item.id ? 'bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                                class="w-full text-left py-2 px-3 rounded-[10px] transition-all flex items-start gap-2 leading-snug"
                                :style="item.level === 'h3' ? 'padding-left: 1.5rem;' : ''">
                                <span class="text-slate-400 text-[10px] mt-0.5" x-show="item.level === 'h3'">•</span>
                                <span class="truncate" x-text="item.text"></span>
                            </button>
                        </template>
                    </nav>
                </div>

                <!-- SIDEBAR WIDGET 2: KONSULTASI CEPAT WHATSAPP -->
                <div class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-4">
                    <div class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <i data-lucide="message-circle" class="w-5 h-5"></i>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-base font-bold text-slate-900 dark:text-white">Punya Pertanyaan Usaha?</h4>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Konsultasikan kendala hitung HPP atau pengaturan kasir gerai Anda langsung dengan tim pendamping COOCA.
                        </p>
                    </div>
                    <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text={{ urlencode('Halo Tim COOCA, saya membaca artikel ' . $post->title . ' dan ingin konsultasi mengenai implementasinya.') }}"
                        target="_blank" rel="noopener noreferrer"
                        class="w-full h-11 px-4 rounded-[12px] bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center justify-center gap-2 transition shadow-sm">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                        <span>Tanya Gratis via WhatsApp</span>
                    </a>
                </div>

                <!-- SIDEBAR WIDGET 3: ALAT BANTU RELEVAN -->
                <div class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="wrench" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]"></i>
                        <span>Alat Hitung &amp; Template Gratis</span>
                    </div>

                    <div class="space-y-2 pt-1 text-xs">
                        <a href="{{ route('kalkulator.hpp') }}" 
                            class="p-3 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E] hover:bg-slate-200/70 dark:hover:bg-[#38383A] transition flex items-center justify-between gap-3 group">
                            <div class="space-y-0.5 min-w-0">
                                <div class="font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition truncate">
                                    Kalkulator HPP Produk
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">Hitung modal takaran bahan baku</div>
                            </div>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-[#007AFF] shrink-0"></i>
                        </a>

                        <a href="{{ route('kalkulator.laba-bersih') }}" 
                            class="p-3 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E] hover:bg-slate-200/70 dark:hover:bg-[#38383A] transition flex items-center justify-between gap-3 group">
                            <div class="space-y-0.5 min-w-0">
                                <div class="font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition truncate">
                                    Simulasi Laba Bersih
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">Perhitungan omzet &amp; beban operasional</div>
                            </div>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-[#007AFF] shrink-0"></i>
                        </a>

                        <a href="{{ route('template.index') }}" 
                            class="p-3 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E] hover:bg-slate-200/70 dark:hover:bg-[#38383A] transition flex items-center justify-between gap-3 group">
                            <div class="space-y-0.5 min-w-0">
                                <div class="font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition truncate">
                                    Template Excel Pembukuan
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">Unduh file spreadsheet gratis</div>
                            </div>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-[#007AFF] shrink-0"></i>
                        </a>
                    </div>
                </div>

                <!-- SIDEBAR WIDGET 4: KEMBALI KE KATALOG -->
                <div class="text-center pt-2">
                    <a href="{{ route('blog.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline">
                        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                        <span>Lihat Semua 100 Artikel Edukasi</span>
                    </a>
                </div>

            </aside>

        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- ═══ 4. ARTIKEL TERKAIT (RELATED ARTICLES BENTO ROW) ══════════════════════ -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    @if ($relatedPosts->isNotEmpty())
        <section class="border-t border-black/[0.06] dark:border-white/[0.08] py-16 sm:py-20 bg-white/50 dark:bg-[#151B2B]/40">
            <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                    <div class="space-y-1">
                        <div class="text-[12px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                            PANDUAN TERKAIT
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                            Artikel Lain yang Sebaiknya Anda Baca
                        </h2>
                    </div>
                    <a href="{{ route('blog.index') }}" class="text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline flex items-center gap-1">
                        <span>Buka Katalog Lengkap</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                <!-- 3 Bento Related Post Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ($relatedPosts as $rel)
                        <article class="bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] rounded-[22px] overflow-hidden flex flex-col justify-between shadow-sm hover:border-[#007AFF]/40 hover:shadow-lg transition-all group">
                            <div>
                                <!-- Image Thumbnail (if available) -->
                                @if ($rel->cover_image)
                                    <div class="h-44 overflow-hidden relative border-b border-black/[0.06] dark:border-white/[0.08] bg-slate-100 dark:bg-[#2C2C2E]">
                                        <img src="{{ $rel->cover_image }}" alt="{{ $rel->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                        <span class="absolute top-3 left-3 px-2.5 py-1 rounded-[8px] text-[11px] font-bold uppercase tracking-wider bg-[#007AFF] text-white">
                                            {{ $rel->category ?? 'UMKM' }}
                                        </span>
                                    </div>
                                @endif

                                <div class="p-6 space-y-3">
                                    <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 font-mono">
                                        <span>{{ $rel->published_at ? $rel->published_at->format('d M Y') : $rel->created_at->format('d M Y') }}</span>
                                        <span>•</span>
                                        <span>{{ $rel->read_time ?? 3 }} menit baca</span>
                                    </div>

                                    <a href="{{ route('blog.show', $rel->slug) }}" class="block">
                                        <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors leading-snug line-clamp-2">
                                            {{ $rel->title }}
                                        </h3>
                                    </a>

                                    @if ($rel->excerpt)
                                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 line-clamp-2 leading-relaxed">
                                            {{ $rel->excerpt }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div class="p-6 pt-0 border-t border-black/[0.04] dark:border-white/[0.06] mt-4 flex items-center justify-between text-xs">
                                <span class="text-slate-500 dark:text-slate-400 font-medium truncate max-w-[130px]">
                                    {{ $rel->author_name ?? 'Tim COOCA' }}
                                </span>
                                <a href="{{ route('blog.show', $rel->slug) }}" class="font-bold text-[#007AFF] dark:text-[#0A84FF] flex items-center gap-1 group-hover:translate-x-0.5 transition-transform">
                                    <span>Baca Panduan</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

            </div>
        </section>
    @endif

</div>
@endsection
