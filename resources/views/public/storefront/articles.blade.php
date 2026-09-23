@extends('public.storefront.layouts.app')

@section('content')
    <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-16 space-y-10">

        {{-- Breadcrumb & Title --}}
        <div class="max-w-2xl space-y-3">
            <div class="flex items-center gap-2 text-xs text-neutral-500">
                <a href="{{ url('/' . $business->slug) }}" class="hover:text-theme-primary transition">Beranda</a>
                <span>/</span>
                <span class="text-neutral-900 dark:text-white font-medium">Artikel & Tips</span>
            </div>
            <div class="inline-block px-3 py-1 rounded-full text-xs font-semibold theme-badge">Edukasi & Berita</div>
            <h1 class="font-heading font-extrabold text-3xl sm:text-4xl text-neutral-900 dark:text-white">
                Artikel, Panduan & Kabar Terbaru
            </h1>
            <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-300">
                Wawasan seputar tren produk, tips perawatan, serta kabar promo dan update terbaru dari
                {{ $business->name }}.
            </p>
        </div>

        {{-- Articles Grid --}}
        @if ($articles->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                @foreach ($articles as $art)
                    <a href="{{ url('/' . $business->slug . '/artikel/' . $art->slug) }}"
                        class="group flex flex-col rounded-theme overflow-hidden bg-white dark:bg-neutral-800/80 border border-black/5 dark:border-white/10 shadow-sm hover:shadow-xl transition-all duration-300">
                        <div class="aspect-video bg-neutral-100 dark:bg-neutral-900 overflow-hidden relative">
                            @if ($art->cover_image)
                                <img src="{{ $art->cover_image }}" alt="{{ $art->title }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-neutral-400">
                                    <i data-lucide="newspaper" class="w-12 h-12 stroke-1"></i>
                                </div>
                            @endif
                        </div>

                        <div class="p-5 flex flex-col flex-1">
                            <div class="flex items-center gap-2 text-xs text-neutral-400 mb-2">
                                <span>{{ optional($art->published_at)->format('d M Y') ?? 'Baru saja' }}</span>
                                @if ($art->views_count > 0)
                                    <span>•</span>
                                    <span>{{ $art->views_count }} pembaca</span>
                                @endif
                            </div>

                            <h2
                                class="font-heading font-bold text-lg text-neutral-900 dark:text-white group-hover:text-theme-primary transition line-clamp-2 leading-snug mb-2">
                                {{ $art->title }}
                            </h2>

                            <p class="text-xs text-neutral-500 line-clamp-3 leading-relaxed mt-auto">
                                {{ $art->excerpt ?: strip_tags($art->content) }}
                            </p>

                            <div
                                class="pt-4 mt-3 border-t border-black/5 dark:border-white/10 flex items-center gap-1.5 text-xs font-semibold text-theme-primary">
                                <span>Baca Selengkapnya</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-1 transition"></i>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="pt-6">
                {{ $articles->links() }}
            </div>
        @else
            <div
                class="py-16 text-center rounded-theme bg-white dark:bg-neutral-800/60 border border-black/5 dark:border-white/10 p-8 space-y-3">
                <div
                    class="w-16 h-16 rounded-full bg-neutral-100 dark:bg-neutral-700 text-neutral-400 mx-auto flex items-center justify-center">
                    <i data-lucide="file-text" class="w-8 h-8"></i>
                </div>
                <h2 class="font-heading font-bold text-lg text-neutral-800 dark:text-neutral-200">Belum Ada Artikel
                    Dipublikasikan</h2>
                <p class="text-xs text-neutral-500 max-w-sm mx-auto">Nantikan panduan dan tips menarik yang akan kami
                    hadirkan segera.</p>
            </div>
        @endif

    </div>
@endsection
