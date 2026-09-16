@extends('layouts.public_marketing')

@section('title', 'Blog & Edukasi Bisnis UMKM | Panduan Finansial & Kasir - Cooca')
@section('description',
    'Pusat edukasi dan panduan praktis UMKM Indonesia. Pelajari tutorial cara hitung HPP, BEP,
    pembukuan kas, serta wawasan seputar kasir POS digital dan AI bisnis.')
@section('keywords',
    'blog umkm indonesia, tutorial pembukuan usaha, cara hitung hpp makanan, cara hitung bep, tips
    bisnis toko kecil, ai untuk umkm')

@section('content')
    <div class="pt-10 pb-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

            <!-- Header -->
            <div class="text-center max-w-3xl mx-auto space-y-3">
                <div
                    class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-bold text-xs">
                    <i data-lucide="book-open" class="w-4 h-4"></i>
                    <span>Knowledge Base &amp; Tutorials</span>
                </div>
                <h1
                    class="text-3xl sm:text-5xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-tight">
                    Pusat Edukasi &amp; <span class="text-[#007AFF] dark:text-[#0A84FF]">Panduan Bisnis UMKM</span>
                </h1>
                <p class="text-sm sm:text-base text-[#6E6E73] dark:text-[#86868B] max-w-2xl mx-auto leading-relaxed">
                    Pelajari strategi keuangan praktis, tutorial pembukuan, dan wawasan digitalisasi untuk mengakselerasi
                    pertumbuhan usaha Anda secara terukur.
                </p>
            </div>

            <!-- Filter Tabs & Search Bar (Apple Segmented Control) -->
            <div
                class="flex flex-col md:flex-row items-center justify-between gap-4 pb-6 border-b border-black/[0.06] dark:border-white/[0.08]">
                <!-- Apple Segmented Control Tabs -->
                <div
                    class="flex items-center gap-1.5 p-1 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.04] dark:border-white/[0.06] overflow-x-auto w-full md:w-auto text-xs">
                    <a href="{{ route('blog.index') }}"
                        class="px-4 py-2 rounded-[12px] transition-all font-semibold whitespace-nowrap {{ empty($currentCluster) ? 'bg-white dark:bg-[#2C2C2E] text-[#1D1D1F] dark:text-[#F5F5F7] shadow-sm' : 'text-[#6E6E73] dark:text-[#86868B] hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7]' }}">
                        Semua Artikel
                    </a>
                    <a href="{{ route('blog.index', ['cluster' => 'tutorial']) }}"
                        class="px-4 py-2 rounded-[12px] transition-all font-semibold whitespace-nowrap {{ $currentCluster === 'tutorial' ? 'bg-white dark:bg-[#2C2C2E] text-[#1D1D1F] dark:text-[#F5F5F7] shadow-sm' : 'text-[#6E6E73] dark:text-[#86868B] hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7]' }}">
                        Tutorial &amp; Panduan Praktis
                    </a>
                    <a href="{{ route('blog.index', ['cluster' => 'edukasi']) }}"
                        class="px-4 py-2 rounded-[12px] transition-all font-semibold whitespace-nowrap {{ $currentCluster === 'edukasi' ? 'bg-white dark:bg-[#2C2C2E] text-[#1D1D1F] dark:text-[#F5F5F7] shadow-sm' : 'text-[#6E6E73] dark:text-[#86868B] hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7]' }}">
                        Wawasan &amp; Edukasi Finansial
                    </a>
                </div>

                <!-- Search Form (Apple Inset Input) -->
                <form action="{{ route('blog.index') }}" method="GET" class="w-full md:w-72">
                    @if ($currentCluster)
                        <input type="hidden" name="cluster" value="{{ $currentCluster }}">
                    @endif
                    <div class="relative">
                        <input type="text" name="q" value="{{ $search }}"
                            placeholder="Cari artikel / panduan..."
                            class="w-full h-10 pl-9 pr-4 bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.08] dark:border-white/[0.1] rounded-[14px] text-[#1D1D1F] dark:text-[#F5F5F7] text-xs placeholder-[#6E6E73]/60 focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all">
                        <i data-lucide="search" class="w-4 h-4 text-[#6E6E73] absolute left-3 top-3"></i>
                    </div>
                </form>
            </div>

            <!-- Featured Post (If on Page 1 without filters) -->
            @if ($featuredPost)
                <div
                    class="bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-6 sm:p-8 lg:p-10 rounded-[28px] overflow-hidden group shadow-sm hover:shadow-md hover:border-[#007AFF]/30 transition-all">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                        <div class="lg:col-span-7 space-y-4">
                            <div class="flex items-center gap-3">
                                <span
                                    class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $featuredPost->cluster === 'tutorial' ? 'bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158]' : 'bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]' }}">
                                    {{ $featuredPost->category }}
                                </span>
                                <span
                                    class="text-xs text-[#6E6E73] dark:text-[#86868B] font-mono">{{ $featuredPost->read_time }}
                                    menit baca</span>
                            </div>

                            <a href="{{ route('blog.show', $featuredPost->slug) }}" class="block">
                                <h2
                                    class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors leading-tight">
                                    {{ $featuredPost->title }}
                                </h2>
                            </a>

                            <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed line-clamp-3">
                                {{ $featuredPost->excerpt }}
                            </p>

                            <div
                                class="pt-4 flex items-center justify-between border-t border-black/[0.06] dark:border-white/[0.08]">
                                <div class="text-xs text-[#6E6E73] dark:text-[#86868B] font-medium">
                                    {{ $featuredPost->author_name }}</div>
                                <a href="{{ route('blog.show', $featuredPost->slug) }}"
                                    class="text-xs font-bold text-[#007AFF] dark:text-[#0A84FF] flex items-center gap-1.5 group-hover:translate-x-0.5 transition-transform">
                                    <span>Baca Selengkapnya</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>

                        <div
                            class="lg:col-span-5 h-64 sm:h-72 rounded-[22px] overflow-hidden relative border border-black/[0.06] dark:border-white/[0.08]">
                            <img src="{{ $featuredPost->cover_image ?? 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80' }}"
                                alt="{{ $featuredPost->title }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </div>
                    </div>
                </div>
            @endif

            <!-- Post Grid (Apple Bento Style 2-Col Mobile / 3-Col Desktop) -->
            <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-6 lg:gap-8">
                @forelse($posts as $post)
                    @if (!$featuredPost || $post->id !== $featuredPost->id)
                        <article
                            class="bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] rounded-[20px] sm:rounded-[24px] overflow-hidden flex flex-col justify-between shadow-sm hover:shadow-md hover:border-[#007AFF]/30 active:scale-[0.98] transition-all group">
                            <div>
                                <div
                                    class="h-28 sm:h-48 overflow-hidden relative border-b border-black/[0.06] dark:border-white/[0.08]">
                                    <img src="{{ $post->cover_image ?? 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80' }}"
                                        alt="{{ $post->title }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    <span
                                        class="absolute top-2 left-2 sm:top-3 sm:left-3 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-[8px] sm:rounded-[10px] text-[8px] sm:text-[10px] font-bold uppercase tracking-wider backdrop-blur-md {{ $post->cluster === 'tutorial' ? 'bg-[#34C759]/90 text-white shadow-sm' : 'bg-[#007AFF]/90 text-white shadow-sm' }}">
                                        {{ $post->category }}
                                    </span>
                                </div>

                                <div class="p-3 sm:p-6 space-y-1.5 sm:space-y-3">
                                    <div
                                        class="flex items-center gap-1.5 sm:gap-2 text-[9px] sm:text-[11px] text-[#6E6E73] dark:text-[#86868B] font-mono">
                                        <span>{{ $post->published_at ? $post->published_at->format('d M') : '' }}</span>
                                        <span>•</span>
                                        <span>{{ $post->read_time }}m</span>
                                    </div>

                                    <a href="{{ route('blog.show', $post->slug) }}" class="block">
                                        <h3
                                            class="text-xs sm:text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors leading-snug line-clamp-2">
                                            {{ $post->title }}
                                        </h3>
                                    </a>

                                    <p
                                        class="text-[11px] sm:text-xs text-[#6E6E73] dark:text-[#86868B] line-clamp-2 leading-relaxed hidden sm:block">
                                        {{ $post->excerpt }}
                                    </p>
                                </div>
                            </div>

                            <div
                                class="p-3 sm:p-6 pt-0 sm:pt-0 border-t border-black/[0.04] dark:border-white/[0.06] mt-2 sm:mt-4 flex items-center justify-between text-[11px] sm:text-xs">
                                <span
                                    class="text-[#6E6E73] dark:text-[#86868B] text-[10px] sm:text-[11px] font-medium truncate max-w-[80px] sm:max-w-none">{{ $post->author_name }}</span>
                                <a href="{{ route('blog.show', $post->slug) }}"
                                    class="font-bold text-[#007AFF] dark:text-[#0A84FF] flex items-center gap-1 group-hover:translate-x-0.5 transition-transform">
                                    <span>Baca</span>
                                    <i data-lucide="arrow-right" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i>
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
            <div class="flex justify-center pt-4">
                {{ $posts->links() }}
            </div>

        </div>
    </div>
@endsection
