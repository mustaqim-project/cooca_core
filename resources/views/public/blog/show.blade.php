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
    <div class="pt-6 sm:pt-10 pb-24 bg-[#F5F5F7] dark:bg-[#000000] min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

            <!-- Breadcrumbs (Apple HIG Inset Style) -->
            <nav class="flex items-center gap-2 text-xs text-[#6E6E73] dark:text-[#86868B]">
                <a href="{{ route('landing') }}" class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Beranda</a>
                <span>/</span>
                <a href="{{ route('blog.index') }}" class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Blog & Panduan</a>
                <span>/</span>
                <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold">{{ $post->category }}</span>
            </nav>

            <!-- Article Header -->
            <header class="space-y-5">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-md {{ $post->cluster === 'tutorial' ? 'bg-[#34C759]/10 text-[#34C759]' : 'bg-[#007AFF]/10 text-[#007AFF]' }}">
                        {{ $post->category }}
                    </span>
                    <span class="text-xs text-[#6E6E73] dark:text-[#86868B] font-mono">{{ $post->read_time }} menit baca</span>
                    <span class="text-xs text-[#6E6E73] dark:text-[#86868B]">•</span>
                    <span class="text-xs text-[#6E6E73] dark:text-[#86868B] font-mono">{{ $post->published_at ? $post->published_at->format('d M Y') : '' }}</span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] leading-tight tracking-tight">
                    {{ $post->title }}
                </h1>

                <div class="flex items-center gap-3 pt-4 text-xs text-[#6E6E73] dark:text-[#86868B] border-t border-black/[0.06] dark:border-white/[0.08]">
                    <div class="w-9 h-9 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center font-bold text-sm">
                        {{ substr($post->author_name, 0, 1) }}
                    </div>
                    <div>
                        <span class="font-bold text-[#1D1D1F] dark:text-[#F5F5F7] text-sm block">{{ $post->author_name }}</span>
                        <span class="text-xs text-[#6E6E73] dark:text-[#86868B]">Diverifikasi Tim Spesialis Finansial COOCA</span>
                    </div>
                </div>
            </header>

            <!-- Featured Image -->
            @if ($post->cover_image)
                <div class="rounded-[24px] overflow-hidden max-h-[420px] w-full relative border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
                    <img src="{{ $post->cover_image }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                </div>
            @endif

            <!-- Article Body with comfortable reading typography for 40-65 -->
            <div class="bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/[0.08] dark:border-white/[0.1] p-6 sm:p-10 shadow-sm">
                <div class="prose max-w-none text-[#1D1D1F] dark:text-[#F5F5F7] dark:prose-invert prose-headings:font-bold prose-headings:text-[#1D1D1F] dark:prose-headings:text-[#F5F5F7] prose-a:text-[#007AFF] dark:prose-a:text-[#0A84FF] text-base sm:text-lg leading-relaxed space-y-6">
                    {!! $post->content !!}
                </div>
            </div>

            <!-- Share & Conversion Banner (Apple Inset Enterprise Card) -->
            <section class="p-8 sm:p-10 rounded-[24px] bg-[#161618] border border-white/[0.08] text-white flex flex-col sm:flex-row items-center justify-between gap-6 shadow-sm">
                <div class="space-y-2 text-center sm:text-left">
                    <div class="text-xs font-semibold uppercase tracking-wider text-[#34C759] flex items-center gap-1.5 justify-center sm:justify-start">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                        <span>100% Gratis Selamanya Tanpa Biaya</span>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">
                        Mulai Otomatiskan Pembukuan Usaha Anda
                    </h3>
                    <p class="text-sm text-[#86868B] max-w-md leading-relaxed">
                        Nikmati software kasir, pencatatan otomatis transaksi, kalkulator HPP akurat, dan cetak struk dari HP tanpa biaya langganan bulanan.
                    </p>
                </div>
                <a href="{{ route('register') }}"
                    class="h-12 px-7 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm shrink-0 shadow-sm active:scale-95 transition-all flex items-center gap-2">
                    <span>Daftar Akun Gratis Sekarang</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </section>

            <!-- Related Posts (Bento Row) -->
            @if ($relatedPosts->isNotEmpty())
                <section class="pt-8 border-t border-black/[0.06] dark:border-white/[0.08] space-y-6">
                    <h3 class="text-xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Panduan Terkait yang Bermanfaat</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        @foreach ($relatedPosts as $rel)
                            <a href="{{ route('blog.show', $rel->slug) }}"
                                class="bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-6 rounded-[20px] group flex flex-col justify-between shadow-sm hover:border-[#007AFF]/30 transition-all">
                                <div class="space-y-2">
                                    <span class="text-xs font-bold text-[#007AFF] dark:text-[#0A84FF] uppercase">{{ $rel->category }}</span>
                                    <h4 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] transition-colors leading-snug">
                                        {{ $rel->title }}
                                    </h4>
                                </div>
                                <span class="text-xs text-[#007AFF] dark:text-[#0A84FF] font-semibold mt-4 flex items-center gap-1.5">
                                    <span>Baca Panduan</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform"></i>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

        </div>
    </div>
@endsection

