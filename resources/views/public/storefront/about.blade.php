@extends('public.storefront.layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-16 space-y-16">

        {{-- Breadcrumb & Header --}}
        <div class="max-w-3xl space-y-3">
            <div class="flex items-center gap-2 text-xs text-neutral-500">
                <a href="{{ url('/' . $business->slug) }}" class="hover:text-theme-primary transition">Beranda</a>
                <span>/</span>
                <span class="text-neutral-900 dark:text-white font-medium">Tentang Kami</span>
            </div>
            <div class="inline-block px-3 py-1 rounded-full text-xs font-semibold theme-badge">Profil Usaha</div>
            <h1 class="font-heading font-extrabold text-3xl sm:text-5xl text-neutral-900 dark:text-white tracking-tight">
                {{ $landingPage->about_title ?: 'Cerita & Dedikasi ' . $business->name }}
            </h1>
            <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 leading-relaxed">
                Mengenal lebih dekat visi, standar kualitas, dan dedikasi kami dalam menghadirkan produk dan layanan terbaik
                bagi pelanggan.
            </p>
        </div>

        {{-- Main Story & Visual --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            <div
                class="lg:col-span-7 space-y-6 text-sm sm:text-base text-neutral-700 dark:text-neutral-300 leading-relaxed">
                @if ($landingPage->about_story)
                    <div class="whitespace-pre-line space-y-4">
                        {{ $landingPage->about_story }}
                    </div>
                @else
                    <p>
                        {{ $business->description ?: 'Kami adalah usaha yang berkomitmen memberikan nilai tambah nyata bagi setiap pelanggan kami melalui dedikasi kualitas, integritas, dan layanan prima berstandar tinggi.' }}
                    </p>
                    <p>
                        Berdiri dengan semangat melayani, kami terus berinovasi dan menjaga kepercayaan pelanggan dengan
                        standar produk terbaik, bahan baku terkurasi, dan pelayanan yang ramah serta responsif.
                    </p>
                @endif

                {{-- Quality Badges --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 pt-4 border-t border-black/5 dark:border-white/10">
                    <div
                        class="p-4 rounded-xl bg-white dark:bg-neutral-800 border border-black/5 dark:border-white/10 shadow-sm">
                        <div
                            class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center mb-2">
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                        </div>
                        <div class="font-bold text-xs text-neutral-900 dark:text-white">100% Terpercaya</div>
                        <div class="text-[11px] text-neutral-500 mt-0.5">Kualitas teruji & terjamin</div>
                    </div>

                    <div
                        class="p-4 rounded-xl bg-white dark:bg-neutral-800 border border-black/5 dark:border-white/10 shadow-sm">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mb-2">
                            <i data-lucide="award" class="w-5 h-5"></i>
                        </div>
                        <div class="font-bold text-xs text-neutral-900 dark:text-white">Standar Mutu</div>
                        <div class="text-[11px] text-neutral-500 mt-0.5">Bahan & proses higienis</div>
                    </div>

                    <div
                        class="p-4 rounded-xl bg-white dark:bg-neutral-800 border border-black/5 dark:border-white/10 shadow-sm col-span-2 sm:col-span-1">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center mb-2">
                            <i data-lucide="headphones" class="w-5 h-5"></i>
                        </div>
                        <div class="font-bold text-xs text-neutral-900 dark:text-white">Layanan Prima</div>
                        <div class="text-[11px] text-neutral-500 mt-0.5">Dukungan CS responsif</div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5">
                <div
                    class="rounded-theme overflow-hidden border border-black/5 dark:border-white/10 shadow-xl aspect-square bg-neutral-100 dark:bg-neutral-800">
                    <img src="{{ $landingPage->about_image_url ?: ($landingPage->hero_image_url ?: 'https://images.unsplash.com/photo-1556761175-b413da4baf72?w=1000&auto=format&fit=crop&q=80') }}"
                        alt="{{ $business->name }}" class="w-full h-full object-cover">
                </div>
            </div>
        </div>

        {{-- Gallery of Business (If present) --}}
        @php
            $galleryImages = (array) ($landingPage->gallery_images ?? []);
        @endphp
        @if (!empty($galleryImages))
            <div class="space-y-6 pt-8 border-t border-black/5 dark:border-white/10">
                <div>
                    <h2 class="font-heading font-bold text-2xl text-neutral-900 dark:text-white">
                        {{ $landingPage->gallery_title ?: 'Galeri Aktivitas & Suasana' }}
                    </h2>
                    <p class="text-xs sm:text-sm text-neutral-500">Momen dan dedikasi tim kami dalam melayani Anda.</p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach ($galleryImages as $img)
                        @php
                            $imgUrl = is_array($img) ? $img['url'] ?? '' : (string) $img;
                        @endphp
                        @if ($imgUrl)
                            <div class="aspect-square rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition">
                                <img src="{{ $imgUrl }}" alt="Galeri {{ $business->name }}"
                                    class="w-full h-full object-cover hover:scale-105 transition duration-300">
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        {{-- CTA Bottom --}}
        <div class="p-8 sm:p-12 rounded-theme bg-neutral-900 text-white text-center space-y-4">
            <h2 class="font-heading font-extrabold text-2xl sm:text-3xl tracking-tight">
                Siap Merasakan Pengalaman Terbaik Bersama Kami?
            </h2>
            <p class="text-sm text-neutral-400 max-w-xl mx-auto">
                Kunjungi katalog online kami untuk pemesanan langsung atau hubungi tim customer service kami via WhatsApp.
            </p>
            <div class="flex flex-wrap items-center justify-center gap-4 pt-2">
                @if ($landingPage->isPageActive('catalog'))
                    <a href="{{ url('/' . $business->slug . '/katalog') }}"
                        class="px-6 py-3 rounded-xl font-semibold text-sm theme-btn-primary">
                        Buka Katalog Toko
                    </a>
                @endif
                @if ($hasWhatsapp)
                    <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank"
                        class="px-6 py-3 rounded-xl font-semibold text-sm bg-white text-neutral-900 hover:bg-neutral-100 transition">
                        Hubungi via WhatsApp
                    </a>
                @endif
            </div>
        </div>

    </div>
@endsection
