@extends('layouts.public_marketing')
@php
    // Wajib: Untuk artikel blog, mutlak gunakan cover image blog jika tersedia
    $coverImg = $post->cover_image;
    if (!empty($coverImg)) {
        $articleOgImage = (str_starts_with($coverImg, 'http://') || str_starts_with($coverImg, 'https://'))
            ? $coverImg
            : (str_starts_with($coverImg, '/') ? url($coverImg) : asset($coverImg));
    } else {
        $articleOgImage = asset('assets/seo/cooca-og-default.jpg');
    }
    $articleSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $post->title,
        'description' => $post->excerpt,
        'image' => [$articleOgImage],
        'author' => [
            '@type' => 'Person',
            'name' => $post->author_name ?? 'Tim Edukasi COOCA',
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'COOCA Indonesia',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => asset('assets/image/1785229034_logo_dark.png'),
            ],
        ],
        'datePublished' => $post->published_at ? $post->published_at->toIso8601String() : $post->created_at->toIso8601String(),
        'dateModified' => $post->updated_at->toIso8601String(),
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => route('blog.show', $post->slug),
        ],
    ];
@endphp

@section('title', ($post->meta_title ?? $post->title) . ' | COOCA Edukasi UMKM')
@section('description', $post->meta_description ?? $post->excerpt)
@section('og_title', ($post->meta_title ?? $post->title) . ' | COOCA')
@section('og_description', $post->meta_description ?? $post->excerpt)
@section('canonical', route('blog.show', $post->slug))
@section('og_type', 'article')
@section('og_image', $articleOgImage)
@section('article_published_time', $post->published_at ? $post->published_at->toIso8601String() : $post->created_at->toIso8601String())
@section('article_modified_time', $post->updated_at->toIso8601String())
@section('article_author', $post->author_name ?? 'COOCA Indonesia')

@push('seo')
    <script type="application/ld+json">
    {!! json_encode($articleSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
@endpush

@push('styles')
<style>
    /* ══════════════════════════════════════════════════════════════════
       ARTICLE READER — Editorial-grade reading experience
       Aesthetic: Clean editorial, tinted paper warmth, high readability
    ══════════════════════════════════════════════════════════════════ */

    /* ── Touch & Focus ── */
    .art-touch { touch-action: manipulation; -webkit-tap-highlight-color: transparent; }
    .art-touch:active { transform: scale(0.98); }
    .art-focus:focus-visible { outline: 3px solid #007AFF; outline-offset: 2px; }

    /* ── Reading progress bar ── */
    .reading-progress {
        position: fixed; top: 0; left: 0; right: 0; z-index: 60;
        height: 3px;
        background: transparent;
        pointer-events: none;
    }
    .reading-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #007AFF, #00C4D8);
        transition: width 120ms ease-out;
        border-radius: 0 2px 2px 0;
    }

    /* ── Article prose — comfortable long-read typography ── */
    .article-prose {
        font-size: 1.0625rem;  /* 17px — sweet spot between 16 and 18 */
        line-height: 1.8;
        letter-spacing: 0.005em;
        color: #2C2C2E;
        word-break: break-word;
        overflow-wrap: break-word;
    }
    .article-prose.text-large {
        font-size: 1.1875rem; /* 19px */
        line-height: 1.85;
    }
    .dark .article-prose {
        color: #D1D1D6;
    }

    /* Heading hierarchy */
    .article-prose h2 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1D1D1F;
        margin: 2.5rem 0 1rem;
        line-height: 1.25;
        letter-spacing: -0.01em;
        scroll-margin-top: 5rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid rgba(0,0,0,0.06);
    }
    .dark .article-prose h2 {
        color: #F5F5F7;
        border-bottom-color: rgba(255,255,255,0.08);
    }
    .article-prose h3 {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1D1D1F;
        margin: 2rem 0 0.75rem;
        line-height: 1.3;
        scroll-margin-top: 5rem;
    }
    .dark .article-prose h3 { color: #F5F5F7; }

    .article-prose h4 {
        font-size: 1.0625rem;
        font-weight: 700;
        color: #1D1D1F;
        margin: 1.5rem 0 0.5rem;
    }
    .dark .article-prose h4 { color: #F5F5F7; }

    /* Paragraphs */
    .article-prose p {
        margin-bottom: 1.5rem;
        max-width: 65ch;
    }

    /* Links */
    .article-prose a {
        color: #007AFF;
        text-decoration: underline;
        text-underline-offset: 3px;
        text-decoration-thickness: 1px;
        transition: opacity 0.15s;
    }
    .article-prose a:hover { opacity: 0.75; }
    .dark .article-prose a { color: #0A84FF; }

    /* Bold & emphasis */
    .article-prose strong { font-weight: 700; color: #1D1D1F; }
    .dark .article-prose strong { color: #F5F5F7; }
    .article-prose em { font-style: italic; }

    /* Lists */
    .article-prose ul, .article-prose ol {
        margin: 1.25rem 0;
        padding-left: 1.5rem;
    }
    .article-prose ul { list-style: disc; }
    .article-prose ol { list-style: decimal; }
    .article-prose li {
        margin-bottom: 0.5rem;
        line-height: 1.7;
        padding-left: 0.25rem;
    }
    .article-prose li::marker {
        color: #007AFF;
    }
    .dark .article-prose li::marker { color: #0A84FF; }

    /* Blockquote */
    .article-prose blockquote {
        margin: 1.75rem 0;
        padding: 1.25rem 1.5rem;
        border-left: 4px solid #007AFF;
        background: rgba(0,122,255,0.04);
        border-radius: 0 12px 12px 0;
        font-style: italic;
        color: #48484A;
    }
    .dark .article-prose blockquote {
        background: rgba(10,132,255,0.08);
        color: #AEAEB2;
        border-left-color: #0A84FF;
    }

    /* Code */
    .article-prose code {
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.875em;
        background: #F2F2F7;
        padding: 2px 6px;
        border-radius: 6px;
        color: #1D1D1F;
    }
    .dark .article-prose code {
        background: #2C2C2E;
        color: #E5E5EA;
    }
    .article-prose pre {
        margin: 1.5rem 0;
        padding: 1.25rem;
        background: #1C1C1E;
        border-radius: 14px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .article-prose pre code {
        background: none;
        padding: 0;
        color: #E5E5EA;
        font-size: 0.8125rem;
        line-height: 1.6;
    }

    /* Tables */
    .article-prose table {
        width: 100%;
        border-collapse: collapse;
        margin: 1.5rem 0;
        font-size: 0.9375rem;
        overflow-x: auto;
        display: block;
    }
    @media (min-width: 640px) {
        .article-prose table { display: table; }
    }
    .article-prose th, .article-prose td {
        padding: 0.75rem 1rem;
        text-align: left;
        border-bottom: 1px solid rgba(0,0,0,0.06);
    }
    .dark .article-prose th, .dark .article-prose td {
        border-bottom-color: rgba(255,255,255,0.08);
    }
    .article-prose th {
        font-weight: 700;
        font-size: 0.8125rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #8E8E93;
        background: #F9F9FB;
    }
    .dark .article-prose th {
        background: #2C2C2E;
        color: #AEAEB2;
    }

    /* Images in prose */
    .article-prose img {
        max-width: 100%;
        height: auto;
        border-radius: 14px;
        margin: 1.75rem 0;
    }

    /* Horizontal rule */
    .article-prose hr {
        border: none;
        height: 1px;
        background: rgba(0,0,0,0.08);
        margin: 2.5rem 0;
    }
    .dark .article-prose hr { background: rgba(255,255,255,0.08); }

    /* ── Related articles horizontal slider (mobile) ── */
    .related-slider {
        display: flex;
        gap: 0.75rem;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scroll-snap-type: x mandatory;
        scrollbar-width: none;
        -ms-overflow-style: none;
        padding-bottom: 0.5rem;
    }
    .related-slider::-webkit-scrollbar { display: none; }
    .related-slider > * {
        scroll-snap-align: start;
        flex: 0 0 80%;
        min-width: 280px;
        max-width: 320px;
    }
    @media (min-width: 640px) {
        .related-slider > * {
            flex: 0 0 45%;
            max-width: none;
        }
    }

    /* ── Mobile bottom bar ── */
    .art-bottom-bar {
        display: flex;
        position: fixed; inset: auto 0 0 0;
        z-index: 40;
        background: rgba(255,255,255,0.96);
        backdrop-filter: blur(20px) saturate(1.8);
        -webkit-backdrop-filter: blur(20px) saturate(1.8);
        border-top: 1px solid rgba(0,0,0,0.08);
        padding-bottom: env(safe-area-inset-bottom, 0px);
    }
    .dark .art-bottom-bar {
        background: rgba(28,28,30,0.96);
        border-top-color: rgba(255,255,255,0.08);
    }
    .art-bottom-bar a, .art-bottom-bar button {
        flex: 1; min-height: 52px;
        display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 2px;
        color: #8E8E93; text-decoration: none; font-size: 0.625rem; font-weight: 600;
        touch-action: manipulation; border: none; background: none; cursor: pointer;
        transition: color .15s;
    }
    .art-bottom-bar .active { color: #007AFF; }
    .art-bottom-bar i { width: 20px; height: 20px; }
    @media (min-width: 768px) { .art-bottom-bar { display: none; } }

    /* Reserve bottom bar space */
    .art-page { padding-bottom: calc(52px + env(safe-area-inset-bottom, 0px)); }
    @media (min-width: 768px) { .art-page { padding-bottom: 0; } }

    /* ── Responsive type scale ── */
    @media (min-width: 640px) {
        .article-prose { font-size: 1.125rem; /* 18px */ }
        .article-prose.text-large { font-size: 1.25rem; }
        .article-prose h2 { font-size: 1.75rem; }
        .article-prose h3 { font-size: 1.375rem; }
    }
    @media (min-width: 1024px) {
        .article-prose h2 { font-size: 1.875rem; }
        .article-prose h3 { font-size: 1.5rem; }
    }
</style>
@endpush

@section('content')
<div x-data="{
    scrollPercent: 0,
    fontSize: 'normal',
    copiedLink: false,
    headings: [],
    activeHeading: '',
    mobileTocOpen: false,
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
        if (scrollY < articleTop) { this.scrollPercent = 0; }
        else if (scrollY > articleTop + totalHeight - windowHeight) { this.scrollPercent = 100; }
        else { this.scrollPercent = Math.round(((scrollY - articleTop) / (totalHeight - windowHeight)) * 100); }
    },
    generateTOC() {
        this.$nextTick(() => {
            const article = document.getElementById('article-content');
            if (!article) return;
            const headingEls = article.querySelectorAll('h2, h3');
            const items = [];
            headingEls.forEach((el, index) => {
                if (!el.id) el.id = 'heading-' + index;
                items.push({ id: el.id, text: el.innerText, level: el.tagName.toLowerCase() });
            });
            this.headings = items;
            if (items.length > 0) this.activeHeading = items[0].id;
        });
    },
    scrollToHeading(id) {
        const el = document.getElementById(id);
        if (el) {
            const offset = 80;
            const pos = el.getBoundingClientRect().top + window.scrollY - offset;
            window.scrollTo({ top: pos, behavior: 'smooth' });
            this.activeHeading = id;
            this.mobileTocOpen = false;
        }
    },
    copyArticleLink() {
        navigator.clipboard.writeText(window.location.href).then(() => {
            this.copiedLink = true;
            setTimeout(() => { this.copiedLink = false; }, 2500);
        });
    },
    refreshIcons() {
        this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
    }
}" @scroll.window="updateScroll()" x-init="init()"
class="art-page w-full font-sans antialiased bg-[#F2F2F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7]">

    {{-- ═══ READING PROGRESS BAR ═══ --}}
    <div class="reading-progress">
        <div class="reading-progress-fill" :style="'width:' + scrollPercent + '%'"></div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         HERO — Compact metadata header + cover image
         Mobile: minimal (category, title, author row)
         Desktop: full breadcrumb + excerpt + toolbar
    ═══════════════════════════════════════════════════════════════════ --}}
    <header class="relative bg-[#060B1E] text-white overflow-hidden">
        {{-- Ambient glow --}}
        <div class="absolute top-0 right-1/4 w-80 h-80 bg-[#007AFF]/12 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10 pt-6 sm:pt-10 lg:pt-12 pb-6 sm:pb-10 lg:pb-12">

            {{-- Breadcrumbs — hidden on mobile, shown sm+ --}}
            <nav class="hidden sm:flex items-center gap-2 text-xs text-slate-400 mb-5" aria-label="Breadcrumb">
                <a href="{{ route('landing') }}" class="art-touch art-focus hover:text-white transition-colors py-1">Beranda</a>
                <span class="text-white/20">/</span>
                <a href="{{ route('blog.index') }}" class="art-touch art-focus hover:text-white transition-colors py-1">Blog</a>
                <span class="text-white/20">/</span>
                <span class="text-[#00C4D8] font-semibold truncate max-w-[200px]" aria-current="page">{{ $post->category ?? 'Edukasi' }}</span>
            </nav>

            {{-- Category + Reading time --}}
            <div class="flex items-center gap-2 mb-3 sm:mb-4">
                <span class="text-xs font-bold uppercase tracking-wider text-[#00C4D8]">
                    {{ $post->category ?? 'Panduan Bisnis' }}
                </span>
                <span class="text-white/20">·</span>
                <span class="text-xs text-slate-300 font-medium">{{ $post->read_time ?? 3 }} menit baca</span>
                <span class="text-white/20 hidden sm:inline">·</span>
                <span class="text-xs text-slate-400 hidden sm:inline">{{ $post->published_at ? $post->published_at->format('d M Y') : $post->created_at->format('d M Y') }}</span>
            </div>

            {{-- Title --}}
            <h1 class="text-[1.5rem] sm:text-[2rem] lg:text-[2.5rem] font-extrabold text-white leading-[1.2] tracking-tight text-balance max-w-3xl">
                {{ $post->title }}
            </h1>

            {{-- Excerpt — hidden on mobile --}}
            @if ($post->excerpt)
                <p class="hidden sm:block text-base sm:text-lg text-slate-300 leading-relaxed mt-3 max-w-2xl text-pretty">
                    {{ $post->excerpt }}
                </p>
            @endif

            {{-- Author + toolbar row --}}
            <div class="mt-5 sm:mt-6 pt-4 sm:pt-5 border-t border-white/10 flex items-center justify-between gap-3">
                {{-- Author --}}
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-[#007AFF]/20 text-[#00C4D8] border border-[#00C4D8]/30 flex items-center justify-center shrink-0">
                        <i data-lucide="user-check" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-sm font-bold text-white truncate">{{ $post->author_name ?? 'Tim Edukasi COOCA' }}</div>
                        <div class="text-xs text-slate-400 truncate hidden sm:block">Panduan UMKM terverifikasi</div>
                    </div>
                </div>

                {{-- Toolbar — compact on mobile --}}
                <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                    {{-- Font size toggle --}}
                    <div class="hidden sm:flex items-center bg-[#0E1E45] rounded-[10px] p-0.5 border border-white/15">
                        <button @click="fontSize = 'normal'"
                            :class="fontSize === 'normal' ? 'bg-[#007AFF] text-white font-bold' : 'text-slate-400'"
                            class="art-touch art-focus w-10 h-9 rounded-[8px] text-sm flex items-center justify-center transition cursor-pointer">A</button>
                        <button @click="fontSize = 'large'"
                            :class="fontSize === 'large' ? 'bg-[#007AFF] text-white font-bold' : 'text-slate-400'"
                            class="art-touch art-focus w-10 h-9 rounded-[8px] text-sm flex items-center justify-center transition cursor-pointer font-semibold">A+</button>
                    </div>

                    {{-- WhatsApp share --}}
                    <a href="https://api.whatsapp.com/send?text={{ urlencode($post->title . ' — ' . url()->current()) }}"
                        target="_blank" rel="noopener noreferrer"
                        class="art-touch art-focus min-h-[44px] min-w-[44px] px-3 rounded-[10px] bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold flex items-center gap-1.5 transition"
                        aria-label="Kirim ke WhatsApp">
                        <i data-lucide="share-2" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">WA</span>
                    </a>

                    {{-- Copy link --}}
                    <button @click="copyArticleLink()"
                        class="art-touch art-focus min-h-[44px] min-w-[44px] px-3 rounded-[10px] bg-white/10 hover:bg-white/15 border border-white/15 text-white text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer"
                        aria-label="Salin tautan">
                        <i data-lucide="link" class="w-4 h-4"></i>
                        <span class="hidden sm:inline" x-text="copiedLink ? 'Tersalin!' : 'Salin'">Salin</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    {{-- ═══════════════════════════════════════════════════════════════════
         COVER IMAGE — Full-width, immersive
    ═══════════════════════════════════════════════════════════════════ --}}
    @if ($post->cover_image)
        <div class="max-w-[1280px] mx-auto px-0 sm:px-6 lg:px-8 -mt-1 sm:mt-0">
            <div class="sm:rounded-b-[20px] lg:rounded-b-[24px] overflow-hidden aspect-[16/9] sm:aspect-[2/1] lg:aspect-[2.5/1] w-full bg-slate-100 dark:bg-[#1C1C1E] sm:border sm:border-t-0 border-black/[0.06] dark:border-white/[0.08]">
                <img src="{{ $post->cover_image }}" alt="{{ $post->title }}" class="w-full h-full object-cover" loading="eager" width="1280" height="512">
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════
         MAIN CONTENT AREA — Article + Sidebar
    ═══════════════════════════════════════════════════════════════════ --}}
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10 lg:py-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-0 lg:gap-10 items-start">

            {{-- ═══ KOLOM UTAMA (8 cols) ═══ --}}
            <main class="lg:col-span-8 min-w-0">

                {{-- Mobile TOC — collapsible accordion --}}
                <div x-show="headings.length > 0" class="lg:hidden mb-5">
                    <button @click="mobileTocOpen = !mobileTocOpen"
                        class="art-touch art-focus w-full min-h-[48px] px-4 flex items-center justify-between rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs"
                        :aria-expanded="mobileTocOpen">
                        <span class="flex items-center gap-2 text-sm font-semibold text-slate-800 dark:text-white">
                            <i data-lucide="list" class="w-4 h-4 text-[#007AFF]"></i>
                            Daftar Isi
                        </span>
                        <span class="flex items-center gap-2">
                            <span class="text-xs font-mono text-[#007AFF]" x-text="scrollPercent + '%'"></span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform" :class="mobileTocOpen && 'rotate-180'"></i>
                        </span>
                    </button>
                    <nav x-show="mobileTocOpen" x-collapse class="mt-1.5 p-3 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs max-h-[40vh] overflow-y-auto space-y-0.5">
                        <template x-for="(item, idx) in headings" :key="idx">
                            <button @click="scrollToHeading(item.id)"
                                :class="activeHeading === item.id ? 'bg-[#007AFF]/10 text-[#007AFF] font-bold' : 'text-slate-600 dark:text-slate-400 font-medium'"
                                class="art-touch w-full text-left min-h-[44px] py-2 px-3 rounded-[10px] flex items-start gap-2 text-sm leading-snug transition"
                                :style="item.level === 'h3' ? 'padding-left:1.5rem' : ''">
                                <span class="text-slate-400 text-xs mt-0.5" x-show="item.level === 'h3'">•</span>
                                <span class="line-clamp-2" x-text="item.text"></span>
                            </button>
                        </template>
                    </nav>
                </div>

                {{-- Article body — clean editorial card --}}
                <article id="article-content"
                    class="bg-white dark:bg-[#1C1C1E] rounded-[16px] sm:rounded-[20px] lg:rounded-[24px] border border-black/[0.06] dark:border-white/[0.08] shadow-sm overflow-hidden">

                    {{-- Article content --}}
                    <div class="px-5 py-6 sm:px-8 sm:py-8 lg:px-12 lg:py-10">
                        <div class="article-prose" :class="fontSize === 'large' && 'text-large'">
                            {!! $post->content !!}
                        </div>
                    </div>

                    {{-- Article footer — verification + share --}}
                    <div class="px-5 sm:px-8 lg:px-12 py-5 bg-[#F9F9FB] dark:bg-[#0D0D0F] border-t border-black/[0.04] dark:border-white/[0.06] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                            <i data-lucide="shield-check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                            <span>Materi terverifikasi sesuai regulasi UMKM Indonesia</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="https://api.whatsapp.com/send?text={{ urlencode($post->title . ' — ' . url()->current()) }}"
                                target="_blank" rel="noopener noreferrer"
                                class="art-touch art-focus min-h-[44px] px-4 rounded-[10px] bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold flex items-center gap-1.5 transition">
                                <i data-lucide="share-2" class="w-3.5 h-3.5"></i>
                                <span>Kirim WA</span>
                            </a>
                            <button @click="copyArticleLink()"
                                class="art-touch art-focus min-h-[44px] px-4 rounded-[10px] bg-slate-100 dark:bg-[#2C2C2E] hover:bg-slate-200 dark:hover:bg-[#38383A] text-slate-700 dark:text-white text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer">
                                <i data-lucide="link" class="w-3.5 h-3.5"></i>
                                <span x-text="copiedLink ? 'Tersalin!' : 'Salin Link'">Salin Link</span>
                            </button>
                        </div>
                    </div>
                </article>

                {{-- CTA Banner — below article --}}
                <div class="mt-6 sm:mt-8 p-5 sm:p-7 rounded-[16px] sm:rounded-[20px] bg-gradient-to-br from-[#007AFF] to-[#0055CC] text-white flex flex-col sm:flex-row items-center justify-between gap-4 shadow-lg shadow-[#007AFF]/15">
                    <div class="space-y-1.5 text-center sm:text-left">
                        <div class="text-lg sm:text-xl font-extrabold tracking-tight">Kelola Kasir &amp; Laporan Otomatis</div>
                        <p class="text-sm text-white/80 max-w-md">Sistem kasir gratis selamanya untuk UMKM. Catat penjualan dari smartphone tanpa biaya.</p>
                    </div>
                    <a href="{{ route('register') }}"
                        class="art-touch art-focus min-h-[48px] px-6 rounded-[12px] bg-white text-[#007AFF] font-bold text-sm flex items-center gap-2 shrink-0 hover:bg-white/90 transition w-full sm:w-auto justify-center shadow-sm">
                        <span>Daftar Gratis</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>

            </main>

            {{-- ═══ SIDEBAR (4 cols) — Desktop only ═══ --}}
            <aside class="hidden lg:block lg:col-span-4 space-y-5 lg:sticky lg:top-16">

                {{-- Table of Contents --}}
                <div x-show="headings.length > 0"
                    class="p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
                    <div class="flex items-center justify-between mb-3 pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-white flex items-center gap-2">
                            <i data-lucide="list" class="w-4 h-4 text-[#007AFF]"></i> Daftar Isi
                        </span>
                        <span class="text-xs font-mono font-semibold text-[#007AFF]" x-text="scrollPercent + '%'"></span>
                    </div>
                    <nav class="space-y-0.5 max-h-[50vh] overflow-y-auto pr-1">
                        <template x-for="(item, idx) in headings" :key="idx">
                            <button @click="scrollToHeading(item.id)"
                                :class="activeHeading === item.id ? 'bg-[#007AFF]/8 text-[#007AFF] font-bold border-l-2 border-[#007AFF]' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white font-medium border-l-2 border-transparent'"
                                class="art-touch w-full text-left min-h-[40px] py-1.5 px-3 rounded-r-[8px] flex items-start gap-2 text-[0.8125rem] leading-snug transition"
                                :style="item.level === 'h3' ? 'padding-left:1.25rem' : ''">
                                <span x-text="item.text" class="line-clamp-2"></span>
                            </button>
                        </template>
                    </nav>
                </div>

                {{-- WhatsApp Konsultasi --}}
                <div class="p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-[10px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <i data-lucide="message-circle" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-slate-900 dark:text-white">Pertanyaan Usaha?</div>
                            <div class="text-xs text-slate-500">Konsultasi gratis ke tim COOCA</div>
                        </div>
                    </div>
                    <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text={{ urlencode('Halo Tim COOCA, saya membaca artikel ' . $post->title . ' dan ingin konsultasi.') }}"
                        target="_blank" rel="noopener noreferrer"
                        class="art-touch art-focus w-full min-h-[44px] rounded-[12px] bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm flex items-center justify-center gap-2 transition shadow-sm">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                        Tanya via WhatsApp
                    </a>
                </div>

                {{-- Tools --}}
                <div class="p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-2.5">
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-white flex items-center gap-2 mb-1">
                        <i data-lucide="wrench" class="w-4 h-4 text-[#007AFF]"></i> Alat Gratis
                    </div>
                    @foreach ([
                        ['route' => route('kalkulator.hpp'), 'label' => 'Kalkulator HPP', 'sub' => 'Hitung modal bahan baku'],
                        ['route' => route('kalkulator.laba-bersih'), 'label' => 'Simulasi Laba Bersih', 'sub' => 'Omzet & beban operasional'],
                        ['route' => route('template.index'), 'label' => 'Template Pembukuan', 'sub' => 'Spreadsheet gratis'],
                    ] as $tool)
                        <a href="{{ $tool['route'] }}"
                            class="art-touch art-focus min-h-[44px] p-3 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E] hover:bg-slate-200 dark:hover:bg-[#38383A] transition flex items-center justify-between gap-3 group">
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-slate-800 dark:text-white group-hover:text-[#007AFF] transition truncate">{{ $tool['label'] }}</div>
                                <div class="text-xs text-slate-500 truncate">{{ $tool['sub'] }}</div>
                            </div>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-[#007AFF] shrink-0 transition"></i>
                        </a>
                    @endforeach
                </div>

                {{-- Back link --}}
                <a href="{{ route('blog.index') }}" class="art-touch art-focus flex items-center justify-center gap-1.5 text-sm font-semibold text-[#007AFF] hover:underline min-h-[44px]">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Semua Artikel
                </a>
            </aside>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         RELATED ARTICLES — Horizontal slider on mobile, grid on desktop
    ═══════════════════════════════════════════════════════════════════ --}}
    @if ($relatedPosts->isNotEmpty())
        <section class="border-t border-black/[0.06] dark:border-white/[0.08] py-10 sm:py-14 lg:py-16 bg-white dark:bg-[#0D0D0F]">
            <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">

                {{-- Section header --}}
                <div class="flex items-end justify-between gap-4 mb-6 sm:mb-8">
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-[#007AFF] mb-1">Baca Juga</div>
                        <h2 class="text-lg sm:text-xl lg:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Artikel Terkait</h2>
                    </div>
                    <a href="{{ route('blog.index') }}" class="art-touch art-focus text-sm font-semibold text-[#007AFF] hover:underline flex items-center gap-1 min-h-[44px] shrink-0">
                        Semua <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                {{-- Mobile: horizontal slider | Desktop: 3-col grid --}}
                <div class="related-slider lg:!grid lg:!grid-cols-3 lg:!gap-6 lg:!overflow-visible">
                    @foreach ($relatedPosts as $rel)
                        <a href="{{ route('blog.show', $rel->slug) }}"
                            class="art-touch art-focus block bg-[#F9F9FB] dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] rounded-[16px] overflow-hidden hover:border-[#007AFF]/30 hover:shadow-lg transition-all group">

                            {{-- Thumbnail --}}
                            @if ($rel->cover_image)
                                <div class="h-36 sm:h-40 overflow-hidden bg-slate-100 dark:bg-[#2C2C2E]">
                                    <img src="{{ $rel->cover_image }}" alt="{{ $rel->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy" width="400" height="200">
                                </div>
                            @endif

                            <div class="p-4 space-y-2">
                                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                    <span class="font-semibold text-[#007AFF] uppercase tracking-wider">{{ $rel->category ?? 'UMKM' }}</span>
                                    <span>·</span>
                                    <span>{{ $rel->read_time ?? 3 }} min</span>
                                </div>
                                <h3 class="text-[0.9375rem] font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors leading-snug line-clamp-2">
                                    {{ $rel->title }}
                                </h3>
                                <div class="flex items-center gap-1 text-xs font-semibold text-[#007AFF] pt-1">
                                    <span>Baca</span>
                                    <i data-lucide="arrow-right" class="w-3 h-3 group-hover:translate-x-0.5 transition-transform"></i>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ═══ MOBILE BOTTOM BAR ═══ --}}
    <nav class="art-bottom-bar md:hidden" aria-label="Navigasi">
        <a href="{{ route('landing') }}" class="art-touch"><i data-lucide="home"></i><span>Beranda</span></a>
        <a href="{{ route('blog.index') }}" class="art-touch active"><i data-lucide="book-open"></i><span>Blog</span></a>
        <a href="{{ route('kalkulator.index') }}" class="art-touch"><i data-lucide="calculator"></i><span>Hitung</span></a>
        <button @click="copyArticleLink()" class="art-touch"><i data-lucide="share-2"></i><span x-text="copiedLink ? 'Tersalin!' : 'Bagikan'">Bagikan</span></button>
    </nav>

</div>
@endsection
