@extends('public.storefront.layouts.app')

@php
    $artTitle = $article->title . ' | ' . $business->name;
    $artRawDesc = strip_tags($article->excerpt ?: ($article->content ?: 'Baca artikel selengkapnya di ' . $business->name . '. Dapatkan wawasan menarik dan informasi terpercaya.'));
    $artDesc = \Illuminate\Support\Str::limit($artRawDesc, 155);
    $artCanonical = url('/' . $business->slug . '/artikel/' . $article->slug);
    // Wajib: Untuk artikel, mutlak prioritaskan cover image artikel
    $rawArtImg = $article->cover_image;
    if (!empty($rawArtImg)) {
        $artOgImage = (str_starts_with($rawArtImg, 'http://') || str_starts_with($rawArtImg, 'https://'))
            ? $rawArtImg
            : (str_starts_with($rawArtImg, '/') ? url($rawArtImg) : asset($rawArtImg));
    } else {
        $artOgImage = $landingPage->og_image_url ?: ($landingPage->hero_image_url ?: ($business->logo_url ?: asset('assets/seo/cooca-og-default.jpg')));
    }
@endphp

@section('title', $artTitle)
@section('description', $artDesc)
@section('canonical', $artCanonical)
@section('og_title', $article->title . ' - ' . $business->name)
@section('og_description', $artDesc)
@section('og_image', $artOgImage)
@section('og_type', 'article')

@push('seo')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "Article",
  "headline": "{{ addslashes($article->title) }}",
  "description": "{{ addslashes($artDesc) }}",
  "image": ["{{ (str_starts_with((string)$artOgImage, 'http://') || str_starts_with((string)$artOgImage, 'https://')) ? $artOgImage : url($artOgImage) }}"],
  "datePublished": "{{ optional($article->published_at)->toIso8601String() ?? now()->toIso8601String() }}",
  "author": {
    "@type": "Person",
    "name": "{{ addslashes($article->author_name ?: $business->name) }}"
  },
  "publisher": {
    "@type": "Organization",
    "name": "{{ addslashes($business->name) }}"
  },
  "mainEntityOfPage": {
    "@type": "WebPage",
    "@id": "{{ $artCanonical }}"
  }
}
</script>
@endpush

@section('content')
<article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-16 space-y-10"
         x-data="{
             copied: false,
             copyUrl() {
                 navigator.clipboard.writeText(window.location.href);
                 this.copied = true;
                 setTimeout(() => this.copied = false, 2000);
             }
         }">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-xs text-neutral-500">
        <a href="{{ url('/' . $business->slug) }}" class="hover:text-theme-primary transition">Beranda</a>
        <span>/</span>
        <a href="{{ url('/' . $business->slug . '/artikel') }}" class="hover:text-theme-primary transition">Artikel</a>
        <span>/</span>
        <span class="text-neutral-900 dark:text-white font-medium line-clamp-1">{{ $article->title }}</span>
    </nav>

    {{-- Header --}}
    <header class="space-y-4">
        <div class="flex items-center gap-3 text-xs text-neutral-500">
            <span>{{ optional($article->published_at)->format('d F Y') }}</span>
            @if ($article->author_name)
                <span>•</span>
                <span>Ditulis oleh: {{ $article->author_name }}</span>
            @endif
            @if ($article->views_count > 0)
                <span>•</span>
                <span>{{ $article->views_count }} tayangan</span>
            @endif
        </div>

        <h1 class="font-heading font-extrabold text-2xl sm:text-4xl lg:text-5xl text-neutral-900 dark:text-white tracking-tight leading-tight">
            {{ $article->title }}
        </h1>

        @if ($article->excerpt)
            <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 font-normal leading-relaxed border-l-4 border-theme-primary pl-4 py-1 italic">
                {{ $article->excerpt }}
            </p>
        @endif
    </header>

    {{-- Featured Image --}}
    @if ($article->cover_image)
        <div class="rounded-theme overflow-hidden shadow-lg border border-black/5 dark:border-white/10 aspect-video bg-neutral-100 dark:bg-neutral-800">
            <img src="{{ $article->cover_image }}" alt="{{ $article->title }}" class="w-full h-full object-cover">
        </div>
    @endif

    {{-- Article Body --}}
    <div class="prose prose-neutral dark:prose-invert max-w-none text-neutral-800 dark:text-neutral-200 text-sm sm:text-base leading-relaxed space-y-6">
        {!! nl2br(e($article->content)) !!}
    </div>

    {{-- Share Bar --}}
    <div class="pt-6 border-t border-black/5 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs">
        <span class="font-semibold text-neutral-700 dark:text-neutral-300">Bagikan artikel ini ke rekan Anda:</span>
        <div class="flex items-center gap-2">
            <button type="button" 
                    @click="copyUrl()" 
                    class="px-3 py-2 rounded-xl bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-700 font-medium flex items-center gap-1.5 transition">
                <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                <span x-text="copied ? 'Tersalin!' : 'Salin Tautan'"></span>
            </button>

            <a href="https://api.whatsapp.com/send?text={{ urlencode($article->title . ' - ' . url()->current()) }}" 
               target="_blank" 
               rel="noopener"
               class="px-3 py-2 rounded-xl bg-[#25D366] text-white font-medium flex items-center gap-1.5 hover:opacity-90 transition">
                <i data-lucide="share-2" class="w-3.5 h-3.5"></i>
                <span>WhatsApp</span>
            </a>
        </div>
    </div>

    {{-- Related Articles --}}
    @if ($relatedArticles->isNotEmpty())
        <div class="pt-12 border-t border-black/5 dark:border-white/10 space-y-6">
            <h2 class="font-heading font-bold text-xl text-neutral-900 dark:text-white">Artikel Lainnya</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach ($relatedArticles as $rel)
                    <a href="{{ url('/' . $business->slug . '/artikel/' . $rel->slug) }}" 
                       class="p-4 rounded-xl bg-white dark:bg-neutral-800 border border-black/5 dark:border-white/10 shadow-sm hover:shadow-md transition flex flex-col">
                        <span class="text-[10px] text-neutral-400 mb-1">{{ optional($rel->published_at)->format('d M Y') }}</span>
                        <h3 class="font-heading font-bold text-sm text-neutral-900 dark:text-white line-clamp-2 leading-snug">
                            {{ $rel->title }}
                        </h3>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

</article>
@endsection
