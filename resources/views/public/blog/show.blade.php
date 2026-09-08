@extends('layouts.public_marketing')

@section('title', ($post->meta_title ?? $post->title) . ' | Cooca UMKM')
@section('description', $post->meta_description ?? $post->excerpt)
@section('og_image', $post->cover_image ?? 'https://cooca.id/assets/image/cooca.png')

@push('seo')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "Article",
    "headline": "{{ $post->title }}",
    "description": "{{ $post->excerpt }}",
    "image": "{{ $post->cover_image }}",
    "author": {
        "@type": "Organization",
        "name": "{{ $post->author_name }}"
    },
    "publisher": {
        "@type": "Organization",
        "name": "COOCA.ID",
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
<div class="pt-10 pb-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-slate-400 mb-8">
            <a href="{{ route('landing') }}" class="hover:text-white">Beranda</a>
            <span>/</span>
            <a href="{{ route('blog.index') }}" class="hover:text-white">Blog</a>
            <span>/</span>
            <span class="text-indigo-400 font-semibold">{{ $post->category }}</span>
        </nav>

        <!-- Article Header -->
        <header class="mb-10 space-y-4">
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $post->cluster === 'tutorial' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' }}">
                    {{ $post->category }}
                </span>
                <span class="text-xs text-slate-400 font-mono">{{ $post->read_time }} menit baca</span>
                <span class="text-xs text-slate-500">•</span>
                <span class="text-xs text-slate-400 font-mono">{{ $post->published_at ? $post->published_at->format('d M Y') : '' }}</span>
            </div>

            <h1 class="text-2xl sm:text-4xl font-black text-white leading-tight">
                {{ $post->title }}
            </h1>

            <div class="flex items-center gap-3 pt-2 text-xs text-slate-400 border-t border-slate-800">
                <div class="w-8 h-8 rounded-full bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center text-indigo-300 font-bold">
                    {{ substr($post->author_name, 0, 1) }}
                </div>
                <div>
                    <span class="font-bold text-white block">{{ $post->author_name }}</span>
                    <span class="text-[10px] text-slate-500">Diverifikasi Tim Finansial COOCA</span>
                </div>
            </div>
        </header>

        <!-- Featured Image -->
        @if($post->cover_image)
        <div class="mb-12 rounded-3xl overflow-hidden max-h-96 w-full relative">
            <img src="{{ $post->cover_image }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
        </div>
        @endif

        <!-- Article Body -->
        <div class="prose prose-invert prose-indigo max-w-none text-slate-300 text-sm leading-relaxed space-y-6">
            {!! $post->content !!}
        </div>

        <!-- Share & Conversion Banner -->
        <div class="mt-14 p-6 sm:p-8 rounded-3xl bg-gradient-to-br from-indigo-950/60 to-slate-900 border border-indigo-500/30 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="space-y-1 text-center sm:text-left">
                <span class="text-[10px] font-bold uppercase text-indigo-400">Gratis Selamanya</span>
                <h4 class="text-lg font-bold text-white">Mulai Praktikkan di Usaha Anda dengan Cooca UMKM</h4>
                <p class="text-xs text-slate-300">Software kasir, pembukuan kas, kalkulator HPP, dan asisten AI tanpa biaya langganan.</p>
            </div>
            <a href="{{ route('register') }}" class="glow-btn px-6 py-3 rounded-xl text-white font-bold text-xs shrink-0 shadow-lg">
                Daftar Gratis Sekarang →
            </a>
        </div>

        <!-- Related Posts -->
        @if($relatedPosts->isNotEmpty())
        <div class="mt-16 pt-10 border-t border-slate-800">
            <h3 class="text-lg font-bold text-white mb-6">Artikel Terkait yang Relevan:</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($relatedPosts as $rel)
                <a href="{{ route('blog.show', $rel->slug) }}" class="glass-card p-5 rounded-2xl group flex flex-col justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-indigo-400 uppercase">{{ $rel->category }}</span>
                        <h4 class="text-sm font-bold text-white mt-1 group-hover:text-indigo-400 transition-colors leading-snug">{{ $rel->title }}</h4>
                    </div>
                    <span class="text-xs text-indigo-400 font-bold mt-4 flex items-center gap-1">
                        <span>Baca</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </span>
                </a>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
