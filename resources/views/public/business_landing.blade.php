<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO METADATA --}}
    <title>{{ $landingPage->meta_title ?: "{$business->name} — Layanan & Produk Resmi" }}</title>
    <meta name="description" content="{{ $landingPage->meta_description ?: ($landingPage->subheadline ?: "Kunjungi {$business->name}. Dapatkan layanan dan produk terbaik dengan harga terjangkau.") }}">
    <meta name="keywords" content="{{ $landingPage->meta_keywords ?: "{$business->name}, toko, servis, promo, pesan whatsapp, indonesia" }}">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph / Social Sharing --}}
    <meta property="og:type" content="business.business">
    <meta property="og:title" content="{{ $landingPage->meta_title ?: $business->name }}">
    <meta property="og:description" content="{{ $landingPage->meta_description ?: $landingPage->subheadline }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if($landingPage->og_image_url || $landingPage->hero_image_url || $business->logo_url)
        <meta property="og:image" content="{{ $landingPage->og_image_url ?: ($landingPage->hero_image_url ?: $business->logo_url) }}">
    @endif

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            DEFAULT: '{{ $landingPage->theme_color ?: '#10B981' }}',
                            50: '{{ $landingPage->theme_color ?: '#10B981' }}10',
                            100: '{{ $landingPage->theme_color ?: '#10B981' }}20',
                            500: '{{ $landingPage->theme_color ?: '#10B981' }}',
                            600: '{{ $landingPage->theme_color ?: '#10B981' }}',
                        }
                    }
                }
            }
        }
    </script>

    {{-- Alpine.js & Lucide --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        :root {
            --primary: {{ $landingPage->theme_color ?: '#10B981' }};
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .bg-brand-primary {
            background-color: var(--primary);
        }
        .text-brand-primary {
            color: var(--primary);
        }
        .border-brand-primary {
            border-color: var(--primary);
        }
        .glow-brand {
            box-shadow: 0 10px 35px -5px rgba(var(--primary-rgb, 16, 185, 129), 0.35);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        .dark .glass-card {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>

    {{-- JSON-LD LocalBusiness Schema for Google Search --}}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "LocalBusiness",
        "name": "{{ $business->name }}",
        "description": "{{ $landingPage->subheadline }}",
        "url": "{{ url()->current() }}",
        "telephone": "{{ $landingPage->whatsapp_number ?: $business->phone }}",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "{{ $landingPage->custom_address ?: $business->address }}",
            "addressCountry": "ID"
        }
    }
    </script>
</head>

<body class="{{ $landingPage->dark_mode ? 'dark bg-slate-950 text-slate-100' : 'bg-slate-50 text-slate-900' }} antialiased selection:bg-brand-primary selection:text-white"
    x-data="{ mobileMenuOpen: false, waChatOpen: false }">

    {{-- ========================================================================= --}}
    {{-- TOPBAR / NAVBAR --}}
    {{-- ========================================================================= --}}
    <header class="sticky top-0 z-40 w-full backdrop-blur-md {{ $landingPage->dark_mode ? 'bg-slate-950/80 border-slate-800' : 'bg-white/80 border-slate-200' }} border-b transition-colors">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-4">

            {{-- Brand / Logo --}}
            <a href="#hero" class="flex items-center gap-3 group">
                @if($business->logo_url)
                    <img src="{{ $business->logo_url }}" alt="{{ $business->name }}" class="w-9 h-9 rounded-xl object-contain shadow-sm group-hover:scale-105 transition-transform">
                @else
                    <div class="w-9 h-9 rounded-xl bg-brand-primary text-white flex items-center justify-center font-black text-sm shadow-md">
                        {{ strtoupper(substr($business->name, 0, 2)) }}
                    </div>
                @endif
                <div>
                    <span class="font-extrabold text-sm sm:text-base tracking-tight block leading-tight group-hover:opacity-80 transition">{{ $business->name }}</span>
                    <span class="text-[10px] font-bold text-emerald-500 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Buka Hari Ini
                    </span>
                </div>
            </a>

            {{-- Navigation Links (Desktop) --}}
            <nav class="hidden md:flex items-center gap-6 text-xs font-semibold {{ $landingPage->dark_mode ? 'text-slate-300' : 'text-slate-600' }}">
                <a href="#layanan" class="hover:text-brand-primary transition">Layanan &amp; Menu</a>
                <a href="#tentang" class="hover:text-brand-primary transition">Tentang Kami</a>
                @if(!empty($landingPage->gallery_images))
                    <a href="#galeri" class="hover:text-brand-primary transition">Galeri</a>
                @endif
                @if(!empty($landingPage->faqs))
                    <a href="#faq" class="hover:text-brand-primary transition">Tanya Jawab</a>
                @endif
                <a href="#lokasi" class="hover:text-brand-primary transition">Lokasi &amp; Kontak</a>
            </nav>

            {{-- CTA WhatsApp Navbar Button --}}
            <div class="flex items-center gap-2">
                <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                    class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-primary text-white text-xs font-bold shadow-md hover:opacity-90 transition transform hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z"/></svg>
                    <span>Hubungi Kami</span>
                </a>

                {{-- Mobile Hamburger --}}
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 rounded-xl border border-slate-300 dark:border-slate-800 text-slate-600 dark:text-slate-300">
                    <i data-lucide="menu" class="w-5 h-5" x-show="!mobileMenuOpen"></i>
                    <i data-lucide="x" class="w-5 h-5" x-show="mobileMenuOpen" style="display: none;"></i>
                </button>
            </div>
        </div>

        {{-- Mobile Menu Dropdown --}}
        <div x-show="mobileMenuOpen" x-transition.opacity class="md:hidden border-t {{ $landingPage->dark_mode ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200' }} px-4 py-4 space-y-2 text-xs font-bold" style="display: none;">
            <a href="#layanan" @click="mobileMenuOpen = false" class="block py-2">Layanan &amp; Produk</a>
            <a href="#tentang" @click="mobileMenuOpen = false" class="block py-2">Tentang Kami</a>
            <a href="#lokasi" @click="mobileMenuOpen = false" class="block py-2">Lokasi &amp; Kontak</a>
            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" class="w-full mt-2 py-2.5 rounded-xl bg-brand-primary text-white text-center font-bold block">
                Chat via WhatsApp
            </a>
        </div>
    </header>

    {{-- ========================================================================= --}}
    {{-- HERO SECTION --}}
    {{-- ========================================================================= --}}
    <section id="hero" class="relative overflow-hidden pt-12 pb-16 sm:pt-20 sm:pb-24">
        {{-- Background Glow --}}
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-4xl h-72 bg-brand-primary opacity-15 blur-[100px] pointer-events-none rounded-full"></div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 text-center relative z-10 space-y-6">

            {{-- Announcement Badge --}}
            @if($landingPage->announcement_badge)
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-brand-primary/10 border border-brand-primary/30 text-brand-primary shadow-sm animate-pulse">
                    <span>{{ $landingPage->announcement_badge }}</span>
                </div>
            @endif

            {{-- Headline --}}
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight max-w-3xl mx-auto">
                {{ $landingPage->headline ?: "Selamat Datang di {$business->name}" }}
            </h1>

            {{-- Subheadline --}}
            @if($landingPage->subheadline)
                <p class="text-sm sm:text-lg {{ $landingPage->dark_mode ? 'text-slate-300' : 'text-slate-600' }} max-w-2xl mx-auto leading-relaxed">
                    {{ $landingPage->subheadline }}
                </p>
            @endif

            {{-- Action Buttons --}}
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                <a href="{{ $landingPage->cta_primary_url ?: $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                    class="w-full sm:w-auto px-7 py-3.5 rounded-2xl bg-brand-primary text-white font-extrabold text-sm shadow-xl glow-brand hover:opacity-95 transition transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z"/></svg>
                    <span>{{ $landingPage->cta_primary_text }}</span>
                </a>

                @if($landingPage->cta_secondary_text)
                    <a href="{{ $landingPage->cta_secondary_url ?: '#layanan' }}"
                        class="w-full sm:w-auto px-6 py-3.5 rounded-2xl {{ $landingPage->dark_mode ? 'bg-slate-900 border-slate-700 text-slate-200 hover:bg-slate-800' : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50' }} border font-bold text-sm shadow-sm transition flex items-center justify-center gap-2">
                        <span>{{ $landingPage->cta_secondary_text }}</span>
                        <i data-lucide="arrow-down" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

            {{-- Hero Media Banner (Optional) --}}
            @if($landingPage->hero_image_url)
                <div class="pt-6 max-w-3xl mx-auto">
                    <div class="rounded-3xl overflow-hidden shadow-2xl border {{ $landingPage->dark_mode ? 'border-slate-800' : 'border-slate-200' }}">
                        <img src="{{ $landingPage->hero_image_url }}" alt="{{ $business->name }}" class="w-full h-auto max-h-96 object-cover">
                    </div>
                </div>
            @endif

        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- VALUE PROPOSITIONS (4 PILAR KEUNGGULAN) --}}
    {{-- ========================================================================= --}}
    @if(!empty($landingPage->values))
        <section class="py-12 border-y {{ $landingPage->dark_mode ? 'bg-slate-900/40 border-slate-800/80' : 'bg-white border-slate-200/80' }}">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($landingPage->values as $val)
                        <div class="flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-brand-primary/10 border border-brand-primary/20 text-brand-primary flex items-center justify-center shrink-0">
                                <i data-lucide="{{ $val['icon'] ?? 'check' }}" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-sm {{ $landingPage->dark_mode ? 'text-white' : 'text-slate-900' }}">{{ $val['title'] ?? '' }}</h3>
                                <p class="text-xs {{ $landingPage->dark_mode ? 'text-slate-400' : 'text-slate-500' }} mt-0.5 leading-relaxed">{{ $val['desc'] ?? '' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ========================================================================= --}}
    {{-- LAYANAN & PRODUK SHOWCASE --}}
    {{-- ========================================================================= --}}
    <section id="layanan" class="py-16 sm:py-24">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-12">

            {{-- Section Title --}}
            <div class="text-center max-w-2xl mx-auto space-y-2">
                <span class="text-xs font-extrabold uppercase tracking-widest text-brand-primary">Pilihan Terbaik Kami</span>
                <h2 class="text-2xl sm:text-4xl font-black tracking-tight">
                    {{ $landingPage->services_title ?: 'Layanan & Produk Unggulan' }}
                </h2>
                <p class="text-xs sm:text-sm {{ $landingPage->dark_mode ? 'text-slate-400' : 'text-slate-600' }}">
                    {{ $landingPage->services_subtitle ?: 'Kualitas terbaik dengan penawaran harga terjangkau untuk kepuasan Anda' }}
                </p>
            </div>

            {{-- Custom Services Cards --}}
            @if(!empty($landingPage->custom_services))
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($landingPage->custom_services as $svc)
                        <div class="rounded-3xl glass-card p-6 flex flex-col justify-between space-y-4 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <h3 class="font-extrabold text-base {{ $landingPage->dark_mode ? 'text-white' : 'text-slate-900' }}">{{ $svc['title'] ?? '' }}</h3>
                                    @if(!empty($svc['badge']))
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-brand-primary/10 text-brand-primary border border-brand-primary/20">
                                            {{ $svc['badge'] }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs {{ $landingPage->dark_mode ? 'text-slate-400' : 'text-slate-500' }} leading-relaxed">
                                    {{ $svc['desc'] ?? '' }}
                                </p>
                            </div>

                            <div class="pt-3 border-t {{ $landingPage->dark_mode ? 'border-slate-800' : 'border-slate-200' }} flex items-center justify-between">
                                <div class="font-mono font-black text-sm text-brand-primary">
                                    {{ $svc['price'] ?? 'Hubungi Kami' }}
                                </div>
                                <a href="{{ $landingPage->getWhatsAppUrl($svc['title'] ?? 'Layanan') }}" target="_blank" rel="noopener"
                                    class="px-3.5 py-1.5 rounded-xl bg-brand-primary/15 hover:bg-brand-primary text-brand-primary hover:text-white font-bold text-xs transition flex items-center gap-1.5">
                                    <span>Pesan via WA</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Live POS Products (Integrated from Cashier Database) --}}
            @if($landingPage->show_pos_products && $posProducts->isNotEmpty())
                <div class="pt-8 space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-black text-lg sm:text-xl">Katalog Produk Ready Stock</h3>
                            <p class="text-xs {{ $landingPage->dark_mode ? 'text-slate-400' : 'text-slate-500' }}">Tersedia langsung di kasir toko kami</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach($posProducts as $prod)
                            <div class="rounded-2xl glass-card p-4 flex flex-col justify-between space-y-3 hover:shadow-lg transition">
                                <div>
                                    <div class="w-full h-32 rounded-xl {{ $landingPage->dark_mode ? 'bg-slate-800' : 'bg-slate-100' }} flex items-center justify-center mb-2 overflow-hidden">
                                        @if($prod->image_url)
                                            <img src="{{ $prod->image_url }}" alt="{{ $prod->name }}" class="w-full h-full object-cover">
                                        @else
                                            <i data-lucide="package" class="w-8 h-8 text-slate-400"></i>
                                        @endif
                                    </div>
                                    <h4 class="font-bold text-xs {{ $landingPage->dark_mode ? 'text-white' : 'text-slate-900' }} line-clamp-2">{{ $prod->name }}</h4>
                                </div>
                                <div class="pt-2 border-t {{ $landingPage->dark_mode ? 'border-slate-800' : 'border-slate-100' }} flex items-center justify-between">
                                    <span class="font-mono font-bold text-xs text-brand-primary">Rp {{ number_format($prod->selling_price, 0, ',', '.') }}</span>
                                    <a href="{{ $landingPage->getWhatsAppUrl($prod->name) }}" target="_blank"
                                        class="p-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500 text-emerald-500 hover:text-white transition">
                                        <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- TENTANG KAMI & JAM OPERASIONAL --}}
    {{-- ========================================================================= --}}
    <section id="tentang" class="py-16 {{ $landingPage->dark_mode ? 'bg-slate-900/60' : 'bg-slate-100/60' }}">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">

                {{-- Left: Story --}}
                <div class="lg:col-span-7 space-y-4">
                    <span class="text-xs font-extrabold uppercase tracking-widest text-brand-primary">Tentang Kami</span>
                    <h2 class="text-2xl sm:text-4xl font-black tracking-tight">
                        {{ $landingPage->about_title ?: "Dedikasi Kami di {$business->name}" }}
                    </h2>
                    <div class="text-xs sm:text-sm {{ $landingPage->dark_mode ? 'text-slate-300' : 'text-slate-600' }} leading-relaxed space-y-3 whitespace-pre-wrap">
                        {{ $landingPage->about_story ?: "Kami berdedikasi memberikan pelayanan prima dan produk terbaik untuk setiap pelanggan setia kami." }}
                    </div>

                    {{-- Badges info --}}
                    <div class="grid grid-cols-2 gap-3 pt-4">
                        <div class="p-3.5 rounded-2xl glass-card">
                            <div class="font-black text-lg text-brand-primary">100%</div>
                            <div class="text-[11px] text-slate-500 font-semibold">Komitmen Kepuasan</div>
                        </div>
                        <div class="p-3.5 rounded-2xl glass-card">
                            <div class="font-black text-lg text-brand-primary">Lokal &amp; Terpercaya</div>
                            <div class="text-[11px] text-slate-500 font-semibold">Melayani Sepenuh Hati</div>
                        </div>
                    </div>
                </div>

                {{-- Right: Jam Operasional Card --}}
                <div class="lg:col-span-5">
                    <div class="rounded-3xl glass-card p-6 border space-y-4 shadow-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-brand-primary/10 text-brand-primary flex items-center justify-center">
                                <i data-lucide="clock" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-sm">Jam Buka Operasional</h3>
                                <p class="text-[11px] text-slate-500">Waktu pelayanan toko kami</p>
                            </div>
                        </div>

                        <div class="space-y-2 text-xs divide-y {{ $landingPage->dark_mode ? 'divide-slate-800' : 'divide-slate-200' }}">
                            @php
                                $defaultHours = [
                                    ['day' => 'Senin - Jumat', 'hours' => '08:00 - 20:00 WIB', 'is_open' => true],
                                    ['day' => 'Sabtu', 'hours' => '08:00 - 18:00 WIB', 'is_open' => true],
                                    ['day' => 'Minggu / Libur', 'hours' => '09:00 - 16:00 WIB', 'is_open' => true],
                                ];
                                $hours = !empty($landingPage->operational_hours) ? $landingPage->operational_hours : $defaultHours;
                            @endphp
                            @foreach($hours as $h)
                                <div class="pt-2 flex justify-between items-center">
                                    <span class="font-semibold {{ $landingPage->dark_mode ? 'text-slate-300' : 'text-slate-700' }}">{{ $h['day'] }}</span>
                                    @if(!empty($h['is_open']))
                                        <span class="font-mono text-emerald-500 font-bold">{{ $h['hours'] ?? 'Buka' }}</span>
                                    @else
                                        <span class="font-mono text-rose-500 font-bold">Tutup</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank"
                            class="w-full py-2.5 rounded-xl bg-brand-primary text-white font-bold text-xs text-center block hover:opacity-90 transition">
                            Tanya Jadwal via WhatsApp
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- TESTIMONI PELANGGAN --}}
    {{-- ========================================================================= --}}
    @if(!empty($landingPage->testimonials))
        <section class="py-16 sm:py-24">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-10">
                <div class="text-center max-w-xl mx-auto space-y-2">
                    <span class="text-xs font-extrabold uppercase tracking-widest text-brand-primary">Ulasan Pelanggan</span>
                    <h2 class="text-2xl sm:text-4xl font-black tracking-tight">Apa Kata Mereka Tentang Kami</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($landingPage->testimonials as $testi)
                        <div class="rounded-3xl glass-card p-6 flex flex-col justify-between space-y-4 border">
                            {{-- Star Rating --}}
                            <div class="flex items-center gap-1 text-amber-400">
                                @for($i = 0; $i < ($testi['rating'] ?? 5); $i++)
                                    <i data-lucide="star" class="w-4 h-4 fill-amber-400"></i>
                                @endfor
                            </div>
                            <p class="text-xs {{ $landingPage->dark_mode ? 'text-slate-300' : 'text-slate-600' }} italic leading-relaxed">
                                "{{ $testi['quote'] ?? '' }}"
                            </p>
                            <div class="pt-3 border-t {{ $landingPage->dark_mode ? 'border-slate-800' : 'border-slate-100' }}">
                                <div class="font-extrabold text-xs {{ $landingPage->dark_mode ? 'text-white' : 'text-slate-900' }}">{{ $testi['name'] ?? 'Pelanggan' }}</div>
                                <div class="text-[10px] text-slate-500">{{ $testi['role'] ?? 'Pelanggan Terverifikasi' }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ========================================================================= --}}
    {{-- FAQ ACCORDION --}}
    {{-- ========================================================================= --}}
    @if(!empty($landingPage->faqs))
        <section id="faq" class="py-16 {{ $landingPage->dark_mode ? 'bg-slate-900/40' : 'bg-slate-100/60' }}">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 space-y-8" x-data="{ openFaq: null }">
                <div class="text-center space-y-2">
                    <span class="text-xs font-extrabold uppercase tracking-widest text-brand-primary">FAQ</span>
                    <h2 class="text-2xl sm:text-3xl font-black tracking-tight">Pertanyaan yang Sering Diajukan</h2>
                </div>

                <div class="space-y-3">
                    @foreach($landingPage->faqs as $index => $faq)
                        <div class="rounded-2xl glass-card overflow-hidden border">
                            <button @click="openFaq = (openFaq === {{ $index }} ? null : {{ $index }})"
                                class="w-full px-5 py-4 text-left flex items-center justify-between gap-4 font-bold text-xs sm:text-sm">
                                <span>{{ $faq['question'] ?? '' }}</span>
                                <i data-lucide="chevron-down" class="w-4 h-4 shrink-0 transition-transform duration-200"
                                    :class="openFaq === {{ $index }} ? 'rotate-180 text-brand-primary' : ''"></i>
                            </button>
                            <div x-show="openFaq === {{ $index }}" x-transition.opacity class="px-5 pb-4 text-xs {{ $landingPage->dark_mode ? 'text-slate-400' : 'text-slate-600' }} leading-relaxed" style="display: none;">
                                {{ $faq['answer'] ?? '' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ========================================================================= --}}
    {{-- LOKASI, PETA & KONTAK --}}
    {{-- ========================================================================= --}}
    <section id="lokasi" class="py-16 sm:py-24">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-10">
            <div class="text-center max-w-xl mx-auto space-y-2">
                <span class="text-xs font-extrabold uppercase tracking-widest text-brand-primary">Kunjungi Kami</span>
                <h2 class="text-2xl sm:text-4xl font-black tracking-tight">Lokasi &amp; Kontak</h2>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                {{-- Address & Details --}}
                <div class="lg:col-span-5 rounded-3xl glass-card p-6 space-y-6 border">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-brand-primary/10 text-brand-primary flex items-center justify-center shrink-0">
                            <i data-lucide="map-pin" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm">Alamat Toko</h3>
                            <p class="text-xs {{ $landingPage->dark_mode ? 'text-slate-400' : 'text-slate-600' }} mt-1 leading-relaxed">
                                {{ $landingPage->custom_address ?: ($business->address ?: 'Alamat belum diatur.') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-brand-primary/10 text-brand-primary flex items-center justify-center shrink-0">
                            <i data-lucide="phone" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm">Telepon / WhatsApp</h3>
                            <p class="text-xs font-mono {{ $landingPage->dark_mode ? 'text-slate-300' : 'text-slate-700' }} mt-1">
                                {{ $landingPage->whatsapp_number ?: ($business->phone ?: '-') }}
                            </p>
                        </div>
                    </div>

                    {{-- Social Media Icons --}}
                    @if($landingPage->instagram_handle || $landingPage->tiktok_handle || $landingPage->facebook_url)
                        <div class="pt-4 border-t {{ $landingPage->dark_mode ? 'border-slate-800' : 'border-slate-200' }} flex items-center gap-3">
                            <span class="text-xs text-slate-500 font-semibold">Ikuti Kami:</span>
                            @if($landingPage->instagram_handle)
                                <a href="https://instagram.com/{{ ltrim($landingPage->instagram_handle, '@') }}" target="_blank" class="p-2 rounded-xl bg-slate-800/10 hover:bg-brand-primary hover:text-white transition">
                                    <i data-lucide="instagram" class="w-4 h-4"></i>
                                </a>
                            @endif
                            @if($landingPage->tiktok_handle)
                                <a href="https://tiktok.com/@{{ ltrim($landingPage->tiktok_handle, '@') }}" target="_blank" class="p-2 rounded-xl bg-slate-800/10 hover:bg-brand-primary hover:text-white transition">
                                    <i data-lucide="video" class="w-4 h-4"></i>
                                </a>
                            @endif
                            @if($landingPage->facebook_url)
                                <a href="{{ $landingPage->facebook_url }}" target="_blank" class="p-2 rounded-xl bg-slate-800/10 hover:bg-brand-primary hover:text-white transition">
                                    <i data-lucide="facebook" class="w-4 h-4"></i>
                                </a>
                            @endif
                        </div>
                    @endif

                    <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank"
                        class="w-full py-3 rounded-2xl bg-brand-primary text-white font-bold text-xs text-center block shadow-md hover:opacity-90 transition">
                        Chat Sekarang via WhatsApp
                    </a>
                </div>

                {{-- Map Embed --}}
                <div class="lg:col-span-7 rounded-3xl overflow-hidden shadow-xl border {{ $landingPage->dark_mode ? 'border-slate-800' : 'border-slate-200' }} h-80">
                    @if($landingPage->google_maps_embed_url)
                        <iframe src="{{ $landingPage->google_maps_embed_url }}" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    @else
                        <div class="w-full h-full bg-slate-800 flex flex-col items-center justify-center p-6 text-center text-slate-400">
                            <i data-lucide="map" class="w-10 h-10 mb-2 opacity-50"></i>
                            <span class="text-xs font-semibold">Peta Google Maps dapat disematkan melalui CMS Owner.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- FOOTER --}}
    {{-- ========================================================================= --}}
    <footer class="py-8 border-t {{ $landingPage->dark_mode ? 'bg-slate-950 border-slate-800/80 text-slate-500' : 'bg-white border-slate-200 text-slate-400' }} text-xs text-center">
        <div class="max-w-6xl mx-auto px-4 space-y-2">
            <p>&copy; {{ date('Y') }} <strong>{{ $business->name }}</strong>. All rights reserved.</p>
            <p class="text-[11px] opacity-75">Didukung oleh sistem operasi bisnis <a href="https://cooca.id" target="_blank" class="font-bold hover:underline text-brand-primary">COOCA</a></p>
        </div>
    </footer>

    {{-- ========================================================================= --}}
    {{-- FLOATING WHATSAPP BUTTON (PULSING WIDGET) --}}
    {{-- ========================================================================= --}}
    <div class="fixed bottom-6 right-6 z-50 flex flex-col items-end">

        {{-- Floating Greeting Bubble --}}
        <div x-show="waChatOpen" x-transition.opacity
            class="mb-3 p-4 rounded-2xl glass-card shadow-2xl border max-w-xs text-xs space-y-2" style="display: none;">
            <div class="font-bold flex items-center justify-between">
                <span>{{ $business->name }}</span>
                <button @click="waChatOpen = false" class="text-slate-400 hover:text-slate-200">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </button>
            </div>
            <p class="text-[11px] text-slate-500 leading-relaxed">
                Halo! Ada yang bisa kami bantu seputar produk atau layanan kami?
            </p>
            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank"
                class="w-full py-2 rounded-xl bg-brand-primary text-white font-bold text-center block">
                Mulai Obrolan WhatsApp →
            </a>
        </div>

        {{-- Button Trigger --}}
        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
            class="w-14 h-14 rounded-full bg-[#25D366] text-white flex items-center justify-center shadow-2xl shadow-emerald-600/40 hover:scale-110 transition-transform duration-200 group relative">
            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0zm0 21.785a9.874 9.874 0 0 1-5.032-1.378l-.361-.214-3.741.981.998-3.648-.235-.374a9.861 9.861 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.888 9.884z"/></svg>
            <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-red-500 text-white text-[9px] font-black flex items-center justify-center animate-bounce">1</span>
        </a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>
</body>
</html>
