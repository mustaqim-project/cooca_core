@extends('layouts.public_marketing')

@section('title', ($post->meta_title ?? $post->title) . ' | Cooca')
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
    <div class="pt-8 pb-24">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

            <!-- Breadcrumbs (Apple Inset Style) -->
            <nav class="flex items-center gap-2 text-xs text-[#6E6E73] dark:text-[#86868B]">
                <a href="{{ route('landing') }}"
                    class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Beranda</a>
                <span>/</span>
                <a href="{{ route('blog.index') }}"
                    class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Blog</a>
                <span>/</span>
                <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold">{{ $post->category }}</span>
            </nav>

            <!-- Article Header -->
            <header class="space-y-4">
                <div class="flex items-center gap-3">
                    <span
                        class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $post->cluster === 'tutorial' ? 'bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158]' : 'bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]' }}">
                        {{ $post->category }}
                    </span>
                    <span class="text-xs text-[#6E6E73] dark:text-[#86868B] font-mono">{{ $post->read_time }} menit
                        baca</span>
                    <span class="text-xs text-black/20 dark:text-white/20">•</span>
                    <span
                        class="text-xs text-[#6E6E73] dark:text-[#86868B] font-mono">{{ $post->published_at ? $post->published_at->format('d M Y') : '' }}</span>
                </div>

                <h1
                    class="text-2xl sm:text-4xl lg:text-5xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] leading-tight tracking-tight">
                    {{ $post->title }}
                </h1>

                <div
                    class="flex items-center gap-3 pt-3 text-xs text-[#6E6E73] dark:text-[#86868B] border-t border-black/[0.06] dark:border-white/[0.08]">
                    <div
                        class="w-8 h-8 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center font-bold">
                        {{ substr($post->author_name, 0, 1) }}
                    </div>
                    <div>
                        <span class="font-bold text-[#1D1D1F] dark:text-[#F5F5F7] block">{{ $post->author_name }}</span>
                        <span class="text-[10px] text-[#6E6E73] dark:text-[#86868B]">Diverifikasi Tim Finansial COOCA</span>
                    </div>
                </div>
            </header>

            <!-- Featured Image -->
            @if ($post->cover_image)
                <div
                    class="rounded-[26px] overflow-hidden max-h-96 w-full relative border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
                    <img src="{{ $post->cover_image }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                </div>
            @endif

            <!-- Article Body -->
            <div
                class="prose max-w-none text-[#1D1D1F]/90 dark:text-[#F5F5F7]/90 dark:prose-invert prose-headings:font-bold prose-headings:text-[#1D1D1F] dark:prose-headings:text-[#F5F5F7] prose-a:text-[#007AFF] dark:prose-a:text-[#0A84FF] text-sm sm:text-base leading-relaxed space-y-6">
                {!! $post->content !!}
            </div>

            <!-- Share & Conversion Banner (Apple Inset Enterprise Card) -->
            <div
                class="p-6 sm:p-8 rounded-[24px] bg-[#161618] border border-white/[0.08] text-white flex flex-col sm:flex-row items-center justify-between gap-6 shadow-sm">
                <div class="space-y-1.5 text-center sm:text-left">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#0A84FF]">100% Gratis Selamanya</span>
                    <h4 class="text-lg sm:text-xl font-bold text-white tracking-tight">Mulai Praktikkan di Usaha Anda dengan
                        Cooca</h4>
                    <p class="text-xs text-[#86868B] max-w-md">Software kasir, pembukuan kas otomatis, kalkulator HPP, dan
                        asisten AI tanpa biaya langganan.</p>
                </div>
                <a href="{{ route('register') }}"
                    class="px-6 py-3 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-xs shrink-0 shadow-sm active:scale-95 transition-all">
                    Daftar Gratis Sekarang →
                </a>
            </div>

            <!-- Related Posts (Bento Row) -->
            @if ($relatedPosts->isNotEmpty())
                <div class="pt-10 border-t border-black/[0.06] dark:border-white/[0.08] space-y-6">
                    <h3 class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Artikel Terkait yang Relevan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach ($relatedPosts as $rel)
                            <a href="{{ route('blog.show', $rel->slug) }}"
                                class="bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 rounded-[22px] group flex flex-col justify-between shadow-sm hover:shadow-md hover:border-[#007AFF]/30 hover:-translate-y-0.5 transition-all">
                                <div class="space-y-2">
                                    <span
                                        class="text-[10px] font-bold text-[#007AFF] dark:text-[#0A84FF] uppercase">{{ $rel->category }}</span>
                                    <h4
                                        class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors leading-snug">
                                        {{ $rel->title }}</h4>
                                </div>
                                <span
                                    class="text-xs text-[#007AFF] dark:text-[#0A84FF] font-semibold mt-4 flex items-center gap-1.5">
                                    <span>Baca</span>
                                    <i data-lucide="arrow-right"
                                        class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform"></i>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
@endsection
