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
    <div class="bg-white dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors duration-300 min-h-screen">

        <!-- ═══ HERO SECTION: Midnight Dark with Ambient Glows ═══ -->
        <section
            class="relative bg-[#060B1E] text-white pt-8 sm:pt-12 pb-14 lg:pb-16 overflow-hidden border-b border-white/10 w-full min-w-full">
            <!-- Dual Ambient Glows -->
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none">
            </div>
            <div
                class="absolute -bottom-40 -left-40 w-96 h-96 bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none">
            </div>

            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 space-y-6">
                <!-- Breadcrumbs -->
                <nav class="flex items-center gap-2 text-xs text-slate-400">
                    <a href="{{ route('landing') }}" class="hover:text-white transition-colors">Beranda</a>
                    <span>/</span>
                    <a href="{{ route('blog.index') }}" class="hover:text-white transition-colors">Blog &amp; Panduan</a>
                    <span>/</span>
                    <span class="text-[#00C4D8] font-semibold">{{ $post->category }}</span>
                </nav>

                <!-- Article Header Content -->
                <header class="space-y-4">
                    <div class="flex items-center gap-3">
                        <span
                            class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-md {{ $post->cluster === 'tutorial' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-[#007AFF]/20 text-[#00C4D8] border border-[#007AFF]/30' }}">
                            {{ $post->category }}
                        </span>
                        <span class="text-xs text-slate-300 font-mono">{{ $post->read_time }} menit baca</span>
                        <span class="text-xs text-slate-500">•</span>
                        <span
                            class="text-xs text-slate-300 font-mono">{{ $post->published_at ? $post->published_at->format('d M Y') : '' }}</span>
                    </div>

                    <h1
                        class="text-4xl sm:text-5xl lg:text-[3.25rem] xl:text-[3.75rem] font-extrabold text-white leading-tight tracking-tight">
                        {{ $post->title }}
                    </h1>

                    <div class="flex items-center gap-3 pt-4 text-xs text-slate-300 border-t border-white/10">
                        <div
                            class="w-9 h-9 rounded-full bg-[#007AFF]/20 text-[#00C4D8] border border-[#007AFF]/30 flex items-center justify-center font-bold text-sm">
                            {{ substr($post->author_name, 0, 1) }}
                        </div>
                        <div>
                            <span class="font-bold text-white text-sm block">{{ $post->author_name }}</span>
                            <span class="text-xs text-slate-300">Diverifikasi Tim Spesialis Finansial COOCA</span>
                        </div>
                    </div>
                </header>
            </div>
        </section>

        <!-- ═══ ARTICLE BODY CONTENT (Light / Dark Compatible) ═══ -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-12">

            <!-- Featured Image -->
            @if ($post->cover_image)
                <div
                    class="rounded-2xl overflow-hidden max-h-[440px] w-full relative border border-slate-200/80 dark:border-white/10 shadow-md">
                    <img src="{{ $post->cover_image }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                </div>
            @endif

            <!-- Article Body with comfortable reading typography -->
            <article
                class="bg-white dark:bg-[#0E172F]/70 rounded-2xl border border-slate-200/80 dark:border-white/10 p-6 sm:p-10 shadow-sm text-slate-900 dark:text-white">
                <div
                    class="prose max-w-none text-slate-800 dark:text-slate-200 dark:prose-invert prose-headings:font-bold prose-headings:text-slate-900 dark:prose-headings:text-white prose-a:text-[#007AFF] dark:prose-a:text-[#00C4D8] text-base sm:text-lg leading-relaxed space-y-6">
                    {!! $post->content !!}
                </div>
            </article>

            <!-- Conversion Banner -->
            <section
                class="relative p-8 sm:p-10 rounded-[28px] bg-[#060B1E] text-white border border-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] overflow-hidden flex flex-col sm:flex-row items-center justify-between gap-6">
                <div
                    class="absolute -top-24 -right-24 w-80 h-80 bg-[#007AFF]/20 rounded-full blur-[100px] pointer-events-none">
                </div>
                <div
                    class="absolute -bottom-24 -left-24 w-80 h-80 bg-[#00C4D8]/15 rounded-full blur-[100px] pointer-events-none">
                </div>

                <div class="space-y-2 text-center sm:text-left relative z-10">
                    <div
                        class="text-xs font-semibold uppercase tracking-wider text-emerald-400 flex items-center gap-1.5 justify-center sm:justify-start">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                        <span>100% Gratis Selamanya Tanpa Biaya</span>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">
                        Mulai Otomatiskan Pembukuan Usaha Anda
                    </h3>
                    <p class="text-sm text-slate-300 max-w-md leading-relaxed">
                        Nikmati software kasir, pencatatan otomatis transaksi, kalkulator HPP akurat, dan cetak struk dari
                        HP tanpa biaya langganan bulanan.
                    </p>
                </div>
                <a href="{{ route('register') }}"
                    class="h-12 px-7 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm shrink-0 shadow-lg shadow-[#007AFF]/25 active:scale-95 transition-all flex items-center gap-2 relative z-10">
                    <span>Daftar Akun Gratis Sekarang</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </section>

            <!-- Related Posts (Bento Row) -->
            @if ($relatedPosts->isNotEmpty())
                <section class="pt-8 border-t border-slate-200/80 dark:border-white/10 space-y-6">
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">Panduan Terkait yang Bermanfaat</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        @foreach ($relatedPosts as $rel)
                            <a href="{{ route('blog.show', $rel->slug) }}"
                                class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 rounded-2xl group flex flex-col justify-between shadow-sm hover:border-[#007AFF]/40 hover:shadow-xl hover:shadow-[#007AFF]/5 transition-all">
                                <div class="space-y-2">
                                    <span
                                        class="text-xs font-bold text-[#007AFF] dark:text-[#00C4D8] uppercase">{{ $rel->category }}</span>
                                    <h4
                                        class="text-base font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors leading-snug">
                                        {{ $rel->title }}
                                    </h4>
                                </div>
                                <span
                                    class="text-xs text-[#007AFF] dark:text-[#00C4D8] font-semibold mt-4 flex items-center gap-1.5">
                                    <span>Baca Panduan</span>
                                    <i data-lucide="arrow-right"
                                        class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform"></i>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

        </div>
    </div>
@endsection
