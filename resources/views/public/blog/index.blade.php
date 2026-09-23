@extends('layouts.public_marketing')

@section('title', 'Blog & Edukasi Bisnis UMKM | Panduan Finansial & Kasir - Cooca')
@section('description', 'Pusat edukasi dan panduan praktis UMKM Indonesia. Pelajari tutorial cara hitung HPP, BEP, pembukuan kas, serta wawasan seputar kasir POS digital dan AI bisnis.')
@section('keywords', 'blog umkm indonesia, tutorial pembukuan usaha, cara hitung hpp makanan, cara hitung bep, tips bisnis toko kecil, ai untuk umkm')

@section('content')
    <div class="pt-6 sm:pt-10 pb-24 bg-[#F5F5F7] dark:bg-[#000000] min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12 sm:space-y-16">

            <!-- 2-Grid Hero Section -->
            <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center pt-4">
                <!-- Left: Headline, Value Proposition & Actions (7 cols) -->
                <div class="lg:col-span-7 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                        <i data-lucide="book-open" class="w-4 h-4"></i>
                        <span>Pusat Panduan &amp; Edukasi UMKM</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-[1.15]">
                        Wawasan Praktis untuk Kembangkan Usaha Anda.
                    </h1>

                    <p class="text-base sm:text-lg text-[#6E6E73] dark:text-[#86868B] leading-relaxed max-w-xl">
                        Pelajari cara menghitung modal pokok (HPP), titik impas (BEP), tips mengelola arus kas harian, dan tutorial penggunaan aplikasi kasir tanpa rumus yang rumit.
                    </p>

                    <!-- Trust highlights for UMKM 40-65 -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                        <div class="p-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                            <div class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] flex items-center gap-1.5">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759]"></i>
                                <span>Bahasa Sederhana</span>
                            </div>
                            <div class="text-[12px] text-[#6E6E73] dark:text-[#86868B] mt-1">Bebas istilah finansial yang berbelit</div>
                        </div>

                        <div class="p-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                            <div class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] flex items-center gap-1.5">
                                <i data-lucide="calculator" class="w-4 h-4 text-[#007AFF]"></i>
                                <span>Lengkap Contoh</span>
                            </div>
                            <div class="text-[12px] text-[#6E6E73] dark:text-[#86868B] mt-1">Studi kasus nyata warung & bengkel</div>
                        </div>

                        <div class="p-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                            <div class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] flex items-center gap-1.5">
                                <i data-lucide="award" class="w-4 h-4 text-[#FF9500]"></i>
                                <span>Praktis Diterapkan</span>
                            </div>
                            <div class="text-[12px] text-[#6E6E73] dark:text-[#86868B] mt-1">Bisa langsung dipraktekkan hari ini</div>
                        </div>
                    </div>

                    <!-- CTA -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-2">
                        <a href="#katalog-artikel"
                            class="h-12 px-7 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-sm active:scale-[0.98] transition-all">
                            <span>Baca Artikel & Panduan</span>
                            <i data-lucide="arrow-down" class="w-4 h-4"></i>
                        </a>
                        <a href="{{ route('kalkulator.index') }}"
                            class="h-12 px-6 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.1] dark:border-white/[0.15] text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-black/[0.03] dark:hover:bg-white/[0.06] font-semibold text-sm flex items-center justify-center gap-2 transition-all">
                            <i data-lucide="calculator" class="w-4 h-4 text-[#34C759]"></i>
                            <span>Coba Kalkulator Usaha</span>
                        </a>
                    </div>
                </div>

                <!-- Right: Knowledge Base Preview Widget (5 cols) -->
                <div class="lg:col-span-5">
                    <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] p-6 shadow-sm space-y-4">
                        <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#FF5F56]"></span>
                                <span class="w-3 h-3 rounded-full bg-[#FFBD2E]"></span>
                                <span class="w-3 h-3 rounded-full bg-[#27C93F]"></span>
                                <span class="text-xs font-mono font-semibold text-[#6E6E73] dark:text-[#86868B] ml-2">Kurikulum Bisnis Praktis</span>
                            </div>
                            <span class="text-[11px] font-semibold text-[#007AFF] bg-[#007AFF]/10 px-2.5 py-0.5 rounded-full">
                                Edukasi Gratis
                            </span>
                        </div>

                        <!-- Top Topic Bento Highlights -->
                        <div class="space-y-2.5">
                            <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center font-bold text-xs">
                                        01
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Rumus Hitung HPP Kuliner</div>
                                        <div class="text-[11px] text-[#6E6E73] dark:text-[#86868B]">Bahan baku, porsi & biaya operasional</div>
                                    </div>
                                </div>
                                <i data-lucide="check" class="w-4 h-4 text-[#34C759]"></i>
                            </div>

                            <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold text-xs">
                                        02
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Cara Menentukan BEP Toko</div>
                                        <div class="text-[11px] text-[#6E6E73] dark:text-[#86868B]">Berapa porsi minimal agar tidak rugi</div>
                                    </div>
                                </div>
                                <i data-lucide="check" class="w-4 h-4 text-[#007AFF]"></i>
                            </div>

                            <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-[10px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center font-bold text-xs">
                                        03
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Pemisahan Dompet Pribadi</div>
                                        <div class="text-[11px] text-[#6E6E73] dark:text-[#86868B]">Tips agar uang usaha tidak terpakai</div>
                                    </div>
                                </div>
                                <i data-lucide="check" class="w-4 h-4 text-[#FF9500]"></i>
                            </div>
                        </div>

                        <div class="p-3 rounded-[12px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] text-xs font-semibold text-center flex items-center justify-center gap-2">
                            <i data-lucide="book-open" class="w-4 h-4"></i>
                            <span>Semua Panduan Dapat Dibaca Gratis</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Filter Tabs & Search Bar (Apple Segmented Control) -->
            <section id="katalog-artikel" class="space-y-6 pt-4">
                <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 pb-6 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <!-- Apple Segmented Control Tabs -->
                    <div class="flex items-center gap-1.5 p-1 rounded-[16px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] overflow-x-auto text-xs">
                        <a href="{{ route('blog.index') }}"
                            class="h-10 px-4 rounded-[12px] transition-all font-semibold whitespace-nowrap flex items-center {{ empty($currentCluster) ? 'bg-white dark:bg-[#1C1C1E] text-[#1D1D1F] dark:text-[#F5F5F7] shadow-sm' : 'text-[#6E6E73] dark:text-[#86868B] hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7]' }}">
                            Semua Artikel
                        </a>
                        <a href="{{ route('blog.index', ['cluster' => 'tutorial']) }}"
                            class="h-10 px-4 rounded-[12px] transition-all font-semibold whitespace-nowrap flex items-center {{ $currentCluster === 'tutorial' ? 'bg-white dark:bg-[#1C1C1E] text-[#1D1D1F] dark:text-[#F5F5F7] shadow-sm' : 'text-[#6E6E73] dark:text-[#86868B] hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7]' }}">
                            Tutorial &amp; Panduan Praktis
                        </a>
                        <a href="{{ route('blog.index', ['cluster' => 'edukasi']) }}"
                            class="h-10 px-4 rounded-[12px] transition-all font-semibold whitespace-nowrap flex items-center {{ $currentCluster === 'edukasi' ? 'bg-white dark:bg-[#1C1C1E] text-[#1D1D1F] dark:text-[#F5F5F7] shadow-sm' : 'text-[#6E6E73] dark:text-[#86868B] hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7]' }}">
                            Wawasan Finansial
                        </a>
                    </div>

                    <!-- Search Form (Apple Inset Input) -->
                    <form action="{{ route('blog.index') }}" method="GET" class="w-full md:w-80">
                        @if ($currentCluster)
                            <input type="hidden" name="cluster" value="{{ $currentCluster }}">
                        @endif
                        <div class="relative flex items-center">
                            <i data-lucide="search" class="w-4 h-4 text-[#6E6E73] absolute left-3.5"></i>
                            <input type="text" name="q" value="{{ $search }}"
                                placeholder="Cari judul artikel atau topik..."
                                class="w-full h-11 pl-10 pr-4 bg-white dark:bg-[#1C1C1E] border border-black/[0.1] dark:border-white/[0.12] rounded-[14px] text-[#1D1D1F] dark:text-[#F5F5F7] text-[16px] placeholder-[#6E6E73]/60 focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all">
                        </div>
                    </form>
                </div>

                <!-- Featured Post (If on Page 1 without filters) -->
                @if ($featuredPost)
                    <div class="bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] p-6 sm:p-8 lg:p-10 rounded-[24px] overflow-hidden group shadow-sm hover:border-[#007AFF]/30 transition-all">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                            <div class="lg:col-span-7 space-y-4">
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-md {{ $featuredPost->cluster === 'tutorial' ? 'bg-[#34C759]/10 text-[#34C759]' : 'bg-[#007AFF]/10 text-[#007AFF]' }}">
                                        {{ $featuredPost->category }}
                                    </span>
                                    <span class="text-xs text-[#6E6E73] dark:text-[#86868B] font-mono">
                                        {{ $featuredPost->read_time }} menit baca
                                    </span>
                                </div>

                                <a href="{{ route('blog.show', $featuredPost->slug) }}" class="block">
                                    <h2 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] transition-colors leading-tight">
                                        {{ $featuredPost->title }}
                                    </h2>
                                </a>

                                <p class="text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed line-clamp-3">
                                    {{ $featuredPost->excerpt }}
                                </p>

                                <div class="pt-4 flex items-center justify-between border-t border-black/[0.06] dark:border-white/[0.08]">
                                    <div class="text-xs text-[#6E6E73] dark:text-[#86868B] font-medium">
                                        Ditulis oleh {{ $featuredPost->author_name }}
                                    </div>
                                    <a href="{{ route('blog.show', $featuredPost->slug) }}"
                                        class="h-10 px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-semibold flex items-center gap-1.5 transition">
                                        <span>Baca Lengkap</span>
                                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="lg:col-span-5 h-60 sm:h-72 rounded-[20px] overflow-hidden relative border border-black/[0.06] dark:border-white/[0.08]">
                                <img src="{{ $featuredPost->cover_image ?? 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80' }}"
                                    alt="{{ $featuredPost->title }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Post Grid (Accessible Bento Cards for 40-65) -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($posts as $post)
                        @if (!$featuredPost || $post->id !== $featuredPost->id)
                            <article class="bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[22px] overflow-hidden flex flex-col justify-between shadow-sm hover:border-[#007AFF]/30 transition-all group">
                                <div>
                                    <div class="h-44 sm:h-48 overflow-hidden relative border-b border-black/[0.06] dark:border-white/[0.08]">
                                        <img src="{{ $post->cover_image ?? 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80' }}"
                                            alt="{{ $post->title }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                        <span class="absolute top-3 left-3 px-2.5 py-1 rounded-[8px] text-xs font-bold uppercase tracking-wider {{ $post->cluster === 'tutorial' ? 'bg-[#34C759] text-white' : 'bg-[#007AFF] text-white' }}">
                                            {{ $post->category }}
                                        </span>
                                    </div>

                                    <div class="p-6 space-y-3">
                                        <div class="flex items-center gap-2 text-xs text-[#6E6E73] dark:text-[#86868B] font-mono">
                                            <span>{{ $post->published_at ? $post->published_at->format('d M Y') : '' }}</span>
                                            <span>•</span>
                                            <span>{{ $post->read_time }} menit baca</span>
                                        </div>

                                        <a href="{{ route('blog.show', $post->slug) }}" class="block">
                                            <h3 class="text-base sm:text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] transition-colors leading-snug line-clamp-2">
                                                {{ $post->title }}
                                            </h3>
                                        </a>

                                        <p class="text-sm text-[#6E6E73] dark:text-[#86868B] line-clamp-2 leading-relaxed">
                                            {{ $post->excerpt }}
                                        </p>
                                    </div>
                                </div>

                                <div class="p-6 pt-0 border-t border-black/[0.06] dark:border-white/[0.08] mt-4 flex items-center justify-between text-xs">
                                    <span class="text-[#6E6E73] dark:text-[#86868B] font-medium truncate max-w-[140px]">
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
                        <div class="col-span-full py-16 text-center text-[#6E6E73] dark:text-[#86868B] text-sm">
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

