@extends('layouts.public_marketing')

@section('title', 'Blog & Edukasi Bisnis UMKM | Panduan Finansial & Kasir — Cooca UMKM')
@section('description', 'Pusat edukasi dan panduan praktis UMKM Indonesia. Pelajari tutorial cara hitung HPP, BEP, pembukuan kas, serta wawasan seputar kasir POS digital dan AI bisnis.')
@section('keywords', 'blog umkm indonesia, tutorial pembukuan usaha, cara hitung hpp makanan, cara hitung bep, tips bisnis toko kecil, ai untuk umkm')

@section('content')
<div class="pt-12 pb-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="text-center max-w-3xl mx-auto mb-12">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 font-bold text-xs mb-4">
                <i data-lucide="book-open" class="w-4 h-4"></i>
                <span>Knowledge Base &amp; Tutorials</span>
            </div>
            <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight">
                Pusat Edukasi &amp; <span class="text-gradient-accent">Panduan Bisnis UMKM</span>
            </h1>
            <p class="text-sm sm:text-base text-slate-400 mt-4 leading-relaxed">
                Pelajari strategi keuangan praktis, tutorial pembukuan, dan wawasan digitalisasi untuk mengakselerasi pertumbuhan usaha Anda.
            </p>
        </div>

        <!-- Filter Tabs & Search Bar -->
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-12 pb-6 border-b border-slate-800">
            <!-- Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto pb-2 md:pb-0 text-xs font-bold">
                <a href="{{ route('blog.index') }}" class="px-4 py-2 rounded-xl transition-all {{ empty($currentCluster) ? 'bg-indigo-600 text-white shadow-lg' : 'bg-slate-900 text-slate-400 hover:text-white' }}">
                    Semua Artikel
                </a>
                <a href="{{ route('blog.index', ['cluster' => 'tutorial']) }}" class="px-4 py-2 rounded-xl transition-all {{ $currentCluster === 'tutorial' ? 'bg-indigo-600 text-white shadow-lg' : 'bg-slate-900 text-slate-400 hover:text-white' }}">
                    Tutorial "Cara" (Cluster K)
                </a>
                <a href="{{ route('blog.index', ['cluster' => 'edukasi']) }}" class="px-4 py-2 rounded-xl transition-all {{ $currentCluster === 'edukasi' ? 'bg-indigo-600 text-white shadow-lg' : 'bg-slate-900 text-slate-400 hover:text-white' }}">
                    Edukasi Topikal (Cluster O)
                </a>
            </div>

            <!-- Search Form -->
            <form action="{{ route('blog.index') }}" method="GET" class="w-full md:w-72">
                @if($currentCluster)
                    <input type="hidden" name="cluster" value="{{ $currentCluster }}">
                @endif
                <div class="relative">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Cari artikel / panduan..." class="w-full pl-9 pr-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white text-xs focus:border-indigo-500 focus:outline-none">
                    <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3 top-2.5"></i>
                </div>
            </form>
        </div>

        <!-- Featured Post (If on Page 1 without filters) -->
        @if($featuredPost)
        <div class="mb-14 glass-card p-6 sm:p-10 rounded-3xl overflow-hidden group">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-7 space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $featuredPost->cluster === 'tutorial' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' }}">
                            {{ $featuredPost->category }}
                        </span>
                        <span class="text-xs text-slate-500 font-mono">{{ $featuredPost->read_time }} menit baca</span>
                    </div>

                    <a href="{{ route('blog.show', $featuredPost->slug) }}" class="block">
                        <h2 class="text-2xl sm:text-3xl font-black text-white group-hover:text-indigo-400 transition-colors leading-tight">
                            {{ $featuredPost->title }}
                        </h2>
                    </a>

                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                        {{ $featuredPost->excerpt }}
                    </p>

                    <div class="pt-4 flex items-center justify-between">
                        <div class="text-xs text-slate-400 font-semibold">{{ $featuredPost->author_name }}</div>
                        <a href="{{ route('blog.show', $featuredPost->slug) }}" class="text-xs font-bold text-indigo-400 flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                            <span>Baca Selengkapnya</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-5 h-64 rounded-2xl overflow-hidden relative">
                    <img src="{{ $featuredPost->cover_image ?? 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80' }}" alt="{{ $featuredPost->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                </div>
            </div>
        </div>
        @endif

        <!-- Post Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
            @forelse($posts as $post)
                @if(!$featuredPost || $post->id !== $featuredPost->id)
                <article class="glass-card rounded-3xl overflow-hidden flex flex-col justify-between group">
                    <div>
                        <div class="h-48 overflow-hidden relative">
                            <img src="{{ $post->cover_image ?? 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80' }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            <span class="absolute top-3 left-3 px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider backdrop-blur-md {{ $post->cluster === 'tutorial' ? 'bg-emerald-950/80 text-emerald-300 border border-emerald-500/30' : 'bg-indigo-950/80 text-indigo-300 border border-indigo-500/30' }}">
                                {{ $post->category }}
                            </span>
                        </div>

                        <div class="p-6 space-y-3">
                            <div class="flex items-center gap-2 text-[11px] text-slate-500 font-mono">
                                <span>{{ $post->published_at ? $post->published_at->format('d M Y') : '' }}</span>
                                <span>•</span>
                                <span>{{ $post->read_time }} min baca</span>
                            </div>

                            <a href="{{ route('blog.show', $post->slug) }}" class="block">
                                <h3 class="text-base font-bold text-white group-hover:text-indigo-400 transition-colors leading-snug">
                                    {{ $post->title }}
                                </h3>
                            </a>

                            <p class="text-xs text-slate-400 line-clamp-3 leading-relaxed">
                                {{ $post->excerpt }}
                            </p>
                        </div>
                    </div>

                    <div class="p-6 pt-0 border-t border-slate-800/80 mt-4 flex items-center justify-between text-xs">
                        <span class="text-slate-400 text-[11px]">{{ $post->author_name }}</span>
                        <a href="{{ route('blog.show', $post->slug) }}" class="font-bold text-indigo-400 flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                            <span>Baca</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </article>
                @endif
            @empty
                <div class="col-span-full py-16 text-center text-slate-400 text-sm">
                    Belum ada artikel untuk kategori ini.
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="flex justify-center">
            {{ $posts->links() }}
        </div>

    </div>
</div>
@endsection
