@extends('layouts.public_marketing')

@section('title', 'Blog & Edukasi Bisnis UMKM | Panduan Finansial & Kasir - Cooca')
@section('description', 'Pusat edukasi dan panduan praktis UMKM Indonesia. Pelajari tutorial cara hitung HPP, BEP, pembukuan kas, serta wawasan seputar kasir POS digital dan AI bisnis.')
@section('keywords', 'blog umkm indonesia, tutorial pembukuan usaha, cara hitung hpp makanan, cara hitung bep, tips bisnis toko kecil, ai untuk umkm')

@section('content')
<div class="bg-white dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors duration-300 min-h-screen">

    <!-- ═══ HERO SECTION: Midnight Dark with Ambient Glows ═══ -->
    <section class="relative bg-[#060B1E] text-white pt-10 sm:pt-14 pb-16 lg:pb-20 overflow-hidden border-b border-white/10 w-full min-w-full">
        <!-- Dual Ambient Glows -->
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                <!-- Left: Headline, Value Proposition & Actions (7 cols) -->
                <div class="lg:col-span-7 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#007AFF]/15 text-[#00C4D8] border border-[#00C4D8]/30 text-xs font-semibold backdrop-blur-md">
                        <i data-lucide="book-open" class="w-4 h-4"></i>
                        <span>Pusat Panduan &amp; Edukasi UMKM</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-[1.15]">
                        Wawasan Praktis untuk <span class="text-[#00C4D8]">Kembangkan Usaha Anda.</span>
                    </h1>

                    <p class="text-base sm:text-lg text-slate-300 font-normal leading-relaxed max-w-xl">
                        Pelajari cara menghitung modal pokok (HPP), titik impas (BEP), tips mengelola arus kas harian, dan
                        tutorial penggunaan aplikasi kasir tanpa rumus yang rumit.
                    </p>

                    <!-- Trust highlights for UMKM -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                        <div class="p-3.5 rounded-xl bg-white/5 border border-white/10">
                            <div class="text-xs font-bold text-white flex items-center gap-1.5">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400"></i>
                                <span>Bahasa Sederhana</span>
                            </div>
                            <div class="text-[12px] text-slate-300 mt-1">Bebas istilah finansial yang berbelit</div>
                        </div>

                        <div class="p-3.5 rounded-xl bg-white/5 border border-white/10">
                            <div class="text-xs font-bold text-white flex items-center gap-1.5">
                                <i data-lucide="calculator" class="w-4 h-4 text-[#00C4D8]"></i>
                                <span>Lengkap Contoh</span>
                            </div>
                            <div class="text-[12px] text-slate-300 mt-1">Studi kasus nyata warung &amp; bengkel</div>
                        </div>

                        <div class="p-3.5 rounded-xl bg-white/5 border border-white/10">
                            <div class="text-xs font-bold text-white flex items-center gap-1.5">
                                <i data-lucide="award" class="w-4 h-4 text-amber-400"></i>
                                <span>Praktis Diterapkan</span>
                            </div>
                            <div class="text-[12px] text-slate-300 mt-1">Bisa langsung dipraktekkan hari ini</div>
                        </div>
                    </div>

                    <!-- CTA -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                        <a href="#katalog-artikel"
                            class="h-12 px-7 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-lg shadow-[#007AFF]/25 active:scale-[0.98] transition-all">
                            <span>Baca Artikel &amp; Panduan</span>
                            <i data-lucide="arrow-down" class="w-4 h-4"></i>
                        </a>
                        <a href="{{ route('kalkulator.index') }}"
                            class="h-12 px-6 rounded-xl bg-white/10 hover:bg-white/15 border border-white/20 text-white font-semibold text-sm flex items-center justify-center gap-2 backdrop-blur-sm transition-all">
                            <i data-lucide="calculator" class="w-4 h-4 text-emerald-400"></i>
                            <span>Coba Kalkulator Usaha</span>
                        </a>
                    </div>
                </div>

                <!-- Right: Knowledge Base Preview Widget (5 cols) -->
                <div class="lg:col-span-5">
                    <div class="rounded-2xl bg-[#0E1E45]/80 p-6 shadow-2xl border border-white/10 ring-1 ring-white/10 backdrop-blur-md text-white space-y-4">
                        <div class="flex items-center justify-between border-b border-white/10 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#FF5F56]"></span>
                                <span class="w-3 h-3 rounded-full bg-[#FFBD2E]"></span>
                                <span class="w-3 h-3 rounded-full bg-[#27C93F]"></span>
                                <span class="text-xs font-mono font-semibold text-slate-300 ml-2">Kurikulum Bisnis Praktis</span>
                            </div>
                            <span class="text-[11px] font-semibold text-[#00C4D8] bg-[#007AFF]/20 border border-[#007AFF]/30 px-2.5 py-0.5 rounded-full">
                                Edukasi Gratis
                            </span>
                        </div>

                        <!-- Top Topic Bento Highlights -->
                        <div class="space-y-2.5">
                            <div class="p-3.5 rounded-xl bg-white/5 border border-white/10 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs">
                                        01
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-white">Rumus Hitung HPP Kuliner</div>
                                        <div class="text-[11px] text-slate-300">Bahan baku, porsi &amp; biaya operasional</div>
                                    </div>
                                </div>
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                            </div>

                            <div class="p-3.5 rounded-xl bg-white/5 border border-white/10 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-[#007AFF]/20 text-[#00C4D8] flex items-center justify-center font-bold text-xs">
                                        02
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-white">Cara Menentukan BEP Toko</div>
                                        <div class="text-[11px] text-slate-300">Berapa porsi minimal agar tidak rugi</div>
                                    </div>
                                </div>
                                <i data-lucide="check" class="w-4 h-4 text-[#00C4D8]"></i>
                            </div>

                            <div class="p-3.5 rounded-xl bg-white/5 border border-white/10 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold text-xs">
                                        03
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-white">Pemisahan Dompet Pribadi</div>
                                        <div class="text-[11px] text-slate-300">Tips agar uang usaha tidak terpakai</div>
                                    </div>
                                </div>
                                <i data-lucide="check" class="w-4 h-4 text-amber-400"></i>
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-xs font-semibold text-center flex items-center justify-center gap-2">
                            <i data-lucide="book-open" class="w-4 h-4"></i>
                            <span>Semua Panduan Dapat Dibaca Gratis</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══ BLOG CATALOG & SEARCH (Light / Dark Compatible) ═══ -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">

        <!-- Filter Tabs & Search Bar -->
        <section id="katalog-artikel" class="space-y-6 scroll-mt-20">
            <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 pb-6 border-b border-slate-200/80 dark:border-white/10">
                <!-- Segmented Control Tabs -->
                <div class="flex items-center gap-1.5 p-1 rounded-2xl bg-slate-100 dark:bg-white/5 border border-slate-200/80 dark:border-white/10 overflow-x-auto text-xs">
                    <a href="{{ route('blog.index') }}"
                        class="h-10 px-4 rounded-xl transition-all font-semibold whitespace-nowrap flex items-center {{ empty($currentCluster) ? 'bg-white dark:bg-[#0E172F] text-slate-900 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                        Semua Artikel
                    </a>
                    <a href="{{ route('blog.index', ['cluster' => 'tutorial']) }}"
                        class="h-10 px-4 rounded-xl transition-all font-semibold whitespace-nowrap flex items-center {{ $currentCluster === 'tutorial' ? 'bg-white dark:bg-[#0E172F] text-slate-900 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                        Tutorial &amp; Panduan Praktis
                    </a>
                    <a href="{{ route('blog.index', ['cluster' => 'edukasi']) }}"
                        class="h-10 px-4 rounded-xl transition-all font-semibold whitespace-nowrap flex items-center {{ $currentCluster === 'edukasi' ? 'bg-white dark:bg-[#0E172F] text-slate-900 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                        Wawasan Finansial
                    </a>
                </div>

                <!-- Search Form -->
                <form action="{{ route('blog.index') }}" method="GET" class="w-full md:w-80">
                    @if ($currentCluster)
                        <input type="hidden" name="cluster" value="{{ $currentCluster }}">
                    @endif
                    <div class="relative flex items-center">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5"></i>
                        <input type="text" name="q" value="{{ $search }}"
                            placeholder="Cari judul artikel atau topik..."
                            class="w-full h-11 pl-10 pr-4 bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-xl text-slate-900 dark:text-white text-[16px] placeholder-slate-400 focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all">
                    </div>
                </form>
            </div>

            <!-- Featured Post (If on Page 1 without filters) -->
            @if ($featuredPost)
                <div class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 sm:p-8 lg:p-10 rounded-2xl overflow-hidden group shadow-sm hover:border-[#007AFF]/40 hover:shadow-xl hover:shadow-[#007AFF]/5 transition-all">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                        <div class="lg:col-span-7 space-y-4">
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-md {{ $featuredPost->cluster === 'tutorial' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-[#007AFF]/10 text-[#007AFF] dark:text-[#00C4D8]' }}">
                                    {{ $featuredPost->category }}
                                </span>
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                    {{ $featuredPost->read_time }} menit baca
                                </span>
                            </div>

                            <a href="{{ route('blog.show', $featuredPost->slug) }}" class="block">
                                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors leading-tight">
                                    {{ $featuredPost->title }}
                                </h2>
                            </a>

                            <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed line-clamp-3">
                                {{ $featuredPost->excerpt }}
                            </p>

                            <div class="pt-4 flex items-center justify-between border-t border-slate-100 dark:border-white/10">
                                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                                    Ditulis oleh {{ $featuredPost->author_name }}
                                </div>
                                <a href="{{ route('blog.show', $featuredPost->slug) }}"
                                    class="h-10 px-4 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs font-semibold flex items-center gap-1.5 transition">
                                    <span>Baca Lengkap</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        </div>

                        <div class="lg:col-span-5 h-60 sm:h-72 rounded-xl overflow-hidden relative border border-slate-200/80 dark:border-white/10">
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
                        <article class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-2xl overflow-hidden flex flex-col justify-between shadow-sm hover:border-[#007AFF]/40 hover:shadow-xl hover:shadow-[#007AFF]/5 transition-all group">
                            <div>
                                <div class="h-44 sm:h-48 overflow-hidden relative border-b border-slate-200/80 dark:border-white/10">
                                    <img src="{{ $post->cover_image ?? 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80' }}"
                                        alt="{{ $post->title }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    <span class="absolute top-3 left-3 px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wider {{ $post->cluster === 'tutorial' ? 'bg-emerald-600 text-white' : 'bg-[#007AFF] text-white' }}">
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
                                        <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors leading-snug line-clamp-2">
                                            {{ $post->title }}
                                        </h3>
                                    </a>

                                    <p class="text-sm text-slate-600 dark:text-slate-300 line-clamp-2 leading-relaxed">
                                        {{ $post->excerpt }}
                                    </p>
                                </div>
                            </div>

                            <div class="p-6 pt-0 border-t border-slate-100 dark:border-white/10 mt-4 flex items-center justify-between text-xs">
                                <span class="text-slate-500 dark:text-slate-400 font-medium truncate max-w-[140px]">
                                    {{ $post->author_name }}
                                </span>
                                <a href="{{ route('blog.show', $post->slug) }}"
                                    class="font-bold text-[#007AFF] dark:text-[#00C4D8] flex items-center gap-1 group-hover:translate-x-0.5 transition-transform">
                                    <span>Baca Artikel</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        </article>
                    @endif
                @empty
                    <div class="col-span-full py-16 text-center text-slate-500 dark:text-slate-400 text-sm">
                        Belum ada artikel untuk kategori ini.
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
