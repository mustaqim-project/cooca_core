@extends('layouts.public_marketing')

@push('seo')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&display=swap" rel="stylesheet">
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "SoftwareApplication",
        "name": "COOCA",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web, Cloud-based",
        "description": "Business Operating System & Omnichannel ERP untuk UMKM: Operasional, Penjualan, Keuangan, Inventory, Social Media, Marketplace, dan Automation.",
        "url": "{{ url('/') }}",
        "offers": {
            "@@type": "Offer",
            "price": "0",
            "priceCurrency": "IDR",
            "availability": "https://schema.org/InStock"
        },
        "publisher": {
            "@@type": "Organization",
            "name": "COOCA",
            "url": "{{ url('/') }}"
        }
    }
    </script>
@endpush

@php
    $siteLogoLightSetting = \App\Models\SystemSetting::get('site_logo_light');
    $siteLogoDarkSetting = \App\Models\SystemSetting::get('site_logo_dark');
    $siteLogoLightUrl = \App\Domain\Storage\AdminStorage::publicUrl($siteLogoLightSetting);
    $siteLogoDarkUrl = \App\Domain\Storage\AdminStorage::publicUrl($siteLogoDarkSetting);
@endphp

@section('content')
    <div class="relative overflow-hidden w-full font-sans">

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 1. HERO SECTION (Dark Isometric Neon Ecosystem Hub) ═══ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="relative bg-[#060913] text-white pt-10 pb-16 lg:pt-16 lg:pb-24 overflow-hidden">
            <!-- Ambient Radial Glows -->
            <div
                class="absolute top-1/3 right-1/4 w-[500px] h-[500px] bg-[#00C2FF]/15 rounded-full blur-[140px] pointer-events-none -z-0">
            </div>
            <div
                class="absolute -top-24 left-1/4 w-96 h-96 bg-[#007AFF]/10 rounded-full blur-[130px] pointer-events-none -z-0">
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">

                    <!-- Left Column: Copy & Value Proposition -->
                    <div class="lg:col-span-6 space-y-6 text-center lg:text-left">
                        <!-- Pill Badge -->
                        <div
                            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-cyan-950/60 border border-cyan-400/30 text-cyan-300 text-xs font-semibold shadow-[0_0_15px_rgba(0,194,255,0.2)]">
                            <span class="w-2 h-2 rounded-full bg-[#00C2FF] animate-pulse"></span>
                            <span>Business Operating System &amp; Omnichannel ERP</span>
                        </div>

                        <!-- Main Headline -->
                        <h1
                            class="text-4xl sm:text-5xl lg:text-[3.5em] font-extrabold text-white tracking-tight leading-[1.12]">
                            Run Your Business.<br>
                            From <span
                                class="text-transparent bg-clip-text bg-gradient-to-r from-[#00C2FF] via-[#38BDF8] to-[#60A5FA]">One Operating<br class="hidden sm:inline"> System.</span>
                        </h1>

                        <!-- Subtitle -->
                        <p class="text-base sm:text-lg text-slate-300 max-w-xl leading-relaxed font-normal mx-auto lg:mx-0">
                            COOCA membantu bisnis mengelola operasional, penjualan, keuangan, inventory, customer, social
                            media, marketplace, dan automation dalam satu ekosistem.
                        </p>

                        <!-- Punchline -->
                        <p class="text-sm text-slate-400 font-semibold tracking-wide">
                            One Business. One System. One Central Center.
                        </p>

                        <!-- Dual CTAs (Auth-Aware) -->
                        <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3 pt-2">
                            @if (auth('admin')->check())
                                <a href="{{ route('admin.dashboard') }}"
                                    class="w-full sm:w-auto px-7 py-3.5 rounded-full bg-[#00C2FF] hover:bg-[#00B4D8] text-slate-950 font-bold text-sm flex items-center justify-center gap-2 shadow-[0_0_25px_rgba(0,194,255,0.45)] hover:scale-105 active:scale-95 transition-all">
                                    <span>Dashboard Admin</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            @elseif (auth('web')->check())
                                <a href="{{ route('dashboard') }}"
                                    class="w-full sm:w-auto px-7 py-3.5 rounded-full bg-[#00C2FF] hover:bg-[#00B4D8] text-slate-950 font-bold text-sm flex items-center justify-center gap-2 shadow-[0_0_25px_rgba(0,194,255,0.45)] hover:scale-105 active:scale-95 transition-all">
                                    <span>Ke Dashboard</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            @else
                                <a href="{{ route('register') }}"
                                    class="w-full sm:w-auto px-7 py-3.5 rounded-full bg-[#00C2FF] hover:bg-[#00B4D8] text-slate-950 font-bold text-sm flex items-center justify-center gap-2 shadow-[0_0_25px_rgba(0,194,255,0.45)] hover:shadow-[0_0_35px_rgba(0,194,255,0.65)] hover:scale-105 active:scale-95 transition-all">
                                    <span>Coba COOCA Gratis</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                                <a href="{{ route('public.bos.how-it-works') }}"
                                    class="w-full sm:w-auto px-6 py-3.5 rounded-full bg-white/5 hover:bg-white/10 border border-white/20 text-white font-semibold text-sm flex items-center justify-center gap-2 hover:border-white/30 hover:scale-105 active:scale-95 transition-all">
                                    <span>Lihat Cara Kerja</span>
                                    <i data-lucide="play" class="w-3.5 h-3.5 fill-current"></i>
                                </a>
                            @endif
                        </div>

                        <!-- Horizontal Feature Tags -->
                        <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2 pt-3">
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/5 border border-white/10 text-slate-300 text-xs font-medium">
                                <i data-lucide="check" class="w-3.5 h-3.5 text-[#00C2FF]"></i>
                                <span>ERP</span>
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/5 border border-white/10 text-slate-300 text-xs font-medium">
                                <i data-lucide="check" class="w-3.5 h-3.5 text-[#00C2FF]"></i>
                                <span>Omnichannel</span>
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/5 border border-white/10 text-slate-300 text-xs font-medium">
                                <i data-lucide="check" class="w-3.5 h-3.5 text-[#00C2FF]"></i>
                                <span>Automation</span>
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/5 border border-white/10 text-slate-300 text-xs font-medium">
                                <i data-lucide="check" class="w-3.5 h-3.5 text-[#00C2FF]"></i>
                                <span>Marketplace</span>
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/5 border border-white/10 text-slate-300 text-xs font-medium">
                                <i data-lucide="check" class="w-3.5 h-3.5 text-[#00C2FF]"></i>
                                <span>AI</span>
                            </span>
                        </div>
                    </div>

                    <!-- Right Column: 3D Neon Ecosystem Hub Graphic -->
                    <div class="lg:col-span-6 relative flex items-center justify-center min-h-[460px] lg:min-h-[520px]">
                        <div class="relative w-full max-w-[460px] h-[460px] flex items-center justify-center">

                            <!-- Neon Connection Lines (SVG) -->
                            <svg class="absolute inset-0 w-full h-full pointer-events-none z-0" viewBox="0 0 460 460"
                                fill="none">
                                <defs>
                                    <filter id="cyanGlow" x="-20%" y="-20%" width="140%" height="140%">
                                        <feGaussianBlur stdDeviation="3" result="blur" />
                                        <feComposite in="SourceGraphic" in2="blur" operator="over" />
                                    </filter>
                                </defs>
                                <!-- Line to Social Media (top left) -->
                                <path d="M 230 180 L 105 85" stroke="#00C2FF" stroke-width="1.8" stroke-dasharray="3 3"
                                    opacity="0.75" filter="url(#cyanGlow)" />
                                <!-- Line to Marketplace (top) -->
                                <path d="M 230 160 L 230 75" stroke="#00C2FF" stroke-width="1.8" stroke-dasharray="3 3"
                                    opacity="0.75" filter="url(#cyanGlow)" />
                                <!-- Line to WhatsApp (top right) -->
                                <path d="M 230 180 L 355 85" stroke="#00C2FF" stroke-width="1.8" stroke-dasharray="3 3"
                                    opacity="0.75" filter="url(#cyanGlow)" />
                                <!-- Line to POS (mid left) -->
                                <path d="M 170 210 L 80 180" stroke="#00C2FF" stroke-width="1.8" stroke-dasharray="3 3"
                                    opacity="0.75" filter="url(#cyanGlow)" />
                                <!-- Line to Content Automation (mid right) -->
                                <path d="M 290 210 L 380 180" stroke="#00C2FF" stroke-width="1.8" stroke-dasharray="3 3"
                                    opacity="0.75" filter="url(#cyanGlow)" />
                                <!-- Line to Website (bottom left) -->
                                <path d="M 180 250 L 90 270" stroke="#00C2FF" stroke-width="1.8" stroke-dasharray="3 3"
                                    opacity="0.75" filter="url(#cyanGlow)" />
                                <!-- Line to Customers (bottom right) -->
                                <path d="M 280 250 L 370 270" stroke="#00C2FF" stroke-width="1.8" stroke-dasharray="3 3"
                                    opacity="0.75" filter="url(#cyanGlow)" />
                            </svg>

                            <!-- Central Stepped Pedestal Base -->
                            <div class="absolute bottom-4 inset-x-8 flex flex-col items-center z-10">
                                <!-- Stepped 3D Platform Rings -->
                                <div
                                    class="w-72 h-14 rounded-[100%] bg-gradient-to-b from-[#0e2a5c] to-[#061226] border border-[#00C2FF]/40 shadow-[0_0_35px_rgba(0,194,255,0.4)] flex items-center justify-center relative">
                                    <span class="text-[10px] uppercase font-bold tracking-[0.2em] text-cyan-300">Data •
                                        Automation • AI</span>
                                </div>
                            </div>

                            <!-- Central Glass Box (COOCA Core) -->
                            <div
                                class="relative z-20 w-44 sm:w-48 py-5 px-4 rounded-2xl bg-[#09152e]/90 backdrop-blur-xl border-2 border-[#00C2FF] shadow-[0_0_35px_rgba(0,194,255,0.55),inset_0_0_20px_rgba(0,194,255,0.2)] text-center transform hover:scale-105 transition-transform duration-300">
                                <!-- Logo / Wordmark from Admin Settings -->
                                <div class="mb-1 flex items-center justify-center">
                                    @if (!empty($siteLogoDarkUrl))
                                        <img src="{{ $siteLogoDarkUrl }}" alt="COOCA"
                                            class="h-8 w-auto object-contain mx-auto">
                                    @else
                                        <span
                                            class="font-black text-2xl tracking-wider text-white font-sans drop-shadow-[0_0_12px_rgba(0,194,255,0.6)]">COOCA</span>
                                    @endif
                                </div>
                                <p class="text-[11px] font-semibold text-cyan-300 tracking-tight">Business Operating System
                                </p>

                                <!-- Mini Inner Step Tag -->
                                <div
                                    class="mt-3 inline-block px-4 py-1 rounded-full bg-[#00C2FF] text-slate-950 text-[10px] font-extrabold tracking-wider shadow-[0_0_12px_rgba(0,194,255,0.6)]">
                                    ERP
                                </div>
                            </div>

                            <!-- Floating Node 1: Social Media (Top Left) -->
                            <div class="absolute top-8 left-4 z-20 flex flex-col items-center">
                                <div
                                    class="w-14 h-14 rounded-2xl bg-[#0c1b38]/90 border border-cyan-500/40 shadow-[0_4px_20px_rgba(0,194,255,0.25)] flex items-center justify-center gap-1.5 px-1 hover:scale-110 transition-transform">
                                    <span class="w-3.5 h-3.5 rounded-[4px] bg-gradient-to-tr from-[#f09433] via-[#dc2743] to-[#bc1888] flex items-center justify-center text-white shrink-0">
                                        <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="2" y="2" width="20" height="20" rx="5"></rect><circle cx="12" cy="12" r="3.5"></circle></svg>
                                    </span>
                                    <span class="w-3.5 h-3.5 rounded-[4px] bg-black border border-white/20 flex items-center justify-center text-white shrink-0">
                                        <svg class="w-2 h-2 fill-current" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64c.298-.002.595.042.88.13V9.4a6.33 6.33 0 0 0-1-.08A6.34 6.34 0 0 0 3 15.66a6.34 6.34 0 0 0 10.82 4.49 6.27 6.27 0 0 0 1.93-4.49V8.6a8.18 8.18 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-.93-.03z"/></svg>
                                    </span>
                                    <span class="w-3.5 h-3.5 rounded-[4px] bg-[#1877F2] flex items-center justify-center text-white shrink-0">
                                        <svg class="w-2 h-2 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                    </span>
                                </div>
                                <span class="mt-1 text-[11px] font-medium text-slate-300 text-center">Social Media</span>
                            </div>

                            <!-- Floating Node 2: Marketplace (Top Center) -->
                            <div class="absolute top-2 z-20 flex flex-col items-center">
                                <div
                                    class="w-13 h-13 rounded-2xl bg-[#0c1b38]/90 border border-cyan-500/40 shadow-[0_4px_20px_rgba(0,194,255,0.25)] flex items-center justify-center hover:scale-110 transition-transform p-3 text-[#00C2FF]">
                                    <i data-lucide="shopping-bag" class="w-6 h-6"></i>
                                </div>
                                <span class="mt-1 text-[11px] font-medium text-slate-300 text-center">Marketplace</span>
                            </div>

                            <!-- Floating Node 3: WhatsApp (Top Right) -->
                            <div class="absolute top-8 right-4 z-20 flex flex-col items-center">
                                <div
                                    class="w-14 h-14 rounded-2xl bg-[#0c1b38]/90 border border-cyan-500/40 shadow-[0_4px_20px_rgba(0,194,255,0.25)] flex items-center justify-center hover:scale-110 transition-transform text-[#25D366]">
                                    <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                    </svg>
                                </div>
                                <span class="mt-1 text-[11px] font-medium text-slate-300 text-center">WhatsApp</span>
                            </div>

                            <!-- Floating Node 4: POS (Mid Left) -->
                            <div class="absolute top-36 left-0 z-20 flex flex-col items-center">
                                <div
                                    class="w-13 h-13 rounded-2xl bg-[#0c1b38]/90 border border-cyan-500/40 shadow-[0_4px_20px_rgba(0,194,255,0.25)] flex items-center justify-center hover:scale-110 transition-transform p-3 text-cyan-400">
                                    <i data-lucide="store" class="w-6 h-6"></i>
                                </div>
                                <span class="mt-1 text-[11px] font-medium text-slate-300 text-center">POS</span>
                            </div>

                            <!-- Floating Node 5: Content Automation (Mid Right) -->
                            <div class="absolute top-36 right-0 z-20 flex flex-col items-center">
                                <div
                                    class="w-13 h-13 rounded-2xl bg-[#0c1b38]/90 border border-cyan-500/40 shadow-[0_4px_20px_rgba(0,194,255,0.25)] flex items-center justify-center hover:scale-110 transition-transform p-3 text-purple-400">
                                    <i data-lucide="sparkles" class="w-6 h-6"></i>
                                </div>
                                <span
                                    class="mt-1 text-[11px] font-medium text-slate-300 text-center">Content<br>Automation</span>
                            </div>

                            <!-- Floating Node 6: Website (Bottom Left) -->
                            <div class="absolute bottom-20 left-2 z-20 flex flex-col items-center">
                                <div
                                    class="w-13 h-13 rounded-2xl bg-[#0c1b38]/90 border border-cyan-500/40 shadow-[0_4px_20px_rgba(0,194,255,0.25)] flex items-center justify-center hover:scale-110 transition-transform p-3 text-blue-400">
                                    <i data-lucide="globe" class="w-6 h-6"></i>
                                </div>
                                <span class="mt-1 text-[11px] font-medium text-slate-300 text-center">Website</span>
                            </div>

                            <!-- Floating Node 7: Customers (Bottom Right) -->
                            <div class="absolute bottom-20 right-2 z-20 flex flex-col items-center">
                                <div
                                    class="w-13 h-13 rounded-2xl bg-[#0c1b38]/90 border border-cyan-500/40 shadow-[0_4px_20px_rgba(0,194,255,0.25)] flex items-center justify-center hover:scale-110 transition-transform p-3 text-cyan-300">
                                    <i data-lucide="users" class="w-6 h-6"></i>
                                </div>
                                <span class="mt-1 text-[11px] font-medium text-slate-300 text-center">Customers</span>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </section>


        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 2. FEATURE GRID ("Semua yang Anda Butuhkan...") ═══ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section
            class="bg-white dark:bg-[#070A14] py-20 lg:py-24 border-b border-slate-100 dark:border-white/5 transition-colors">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Section Header -->
                <div class="text-center max-w-3xl mx-auto space-y-3">
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        Semua yang Anda Butuhkan. Terhubung dalam Satu Sistem.
                    </h2>
                    <p class="text-slate-600 dark:text-slate-400 text-sm sm:text-base leading-relaxed">
                        Bisnis tidak berjalan dalam satu aplikasi. COOCA menghubungkan semua proses bisnis Anda, dari
                        operasional hingga pemasaran, dalam satu ekosistem yang terintegrasi.
                    </p>
                </div>

                <!-- 8 Bento Squircle Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-14">

                    <!-- 1. Sales -->
                    <a href="{{ route('public.erp.pos') }}"
                        class="group block p-6 rounded-3xl bg-white dark:bg-[#101726] border border-slate-100 dark:border-white/5 shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:shadow-[0_12px_30px_rgba(0,194,255,0.12)] hover:-translate-y-1 transition-all duration-300">
                        <div
                            class="w-12 h-12 rounded-2xl bg-sky-50 dark:bg-sky-950/60 border border-sky-100 dark:border-sky-800/40 text-sky-500 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i data-lucide="shopping-cart" class="w-6 h-6"></i>
                        </div>
                        <h3
                            class="text-base font-bold text-slate-900 dark:text-white mb-1.5 flex items-center justify-between">
                            <span>Sales</span>
                            <i data-lucide="arrow-right"
                                class="w-4 h-4 opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-[#00C2FF]"></i>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            Kelola POS, order, quotation, invoice, dan penjualan.
                        </p>
                    </a>

                    <!-- 2. Inventory -->
                    <a href="{{ route('public.erp.inventory') }}"
                        class="group block p-6 rounded-3xl bg-white dark:bg-[#101726] border border-slate-100 dark:border-white/5 shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:shadow-[0_12px_30px_rgba(0,194,255,0.12)] hover:-translate-y-1 transition-all duration-300">
                        <div
                            class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-950/60 border border-purple-100 dark:border-purple-800/40 text-purple-500 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i data-lucide="layers" class="w-6 h-6"></i>
                        </div>
                        <h3
                            class="text-base font-bold text-slate-900 dark:text-white mb-1.5 flex items-center justify-between">
                            <span>Inventory</span>
                            <i data-lucide="arrow-right"
                                class="w-4 h-4 opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-[#00C2FF]"></i>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            Pantau stok, warehouse, mutasi, hingga purchasing.
                        </p>
                    </a>

                    <!-- 3. Finance -->
                    <a href="{{ route('public.erp.finance') }}"
                        class="group block p-6 rounded-3xl bg-white dark:bg-[#101726] border border-slate-100 dark:border-white/5 shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:shadow-[0_12px_30px_rgba(0,194,255,0.12)] hover:-translate-y-1 transition-all duration-300">
                        <div
                            class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/60 border border-blue-100 dark:border-blue-800/40 text-blue-500 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i data-lucide="wallet" class="w-6 h-6"></i>
                        </div>
                        <h3
                            class="text-base font-bold text-slate-900 dark:text-white mb-1.5 flex items-center justify-between">
                            <span>Finance</span>
                            <i data-lucide="arrow-right"
                                class="w-4 h-4 opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-[#00C2FF]"></i>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            Kelola transaksi dan keuangan bisnis Anda.
                        </p>
                    </a>

                    <!-- 4. Customer -->
                    <a href="{{ route('public.omnichannel.customer') }}"
                        class="group block p-6 rounded-3xl bg-white dark:bg-[#101726] border border-slate-100 dark:border-white/5 shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:shadow-[0_12px_30px_rgba(0,194,255,0.12)] hover:-translate-y-1 transition-all duration-300">
                        <div
                            class="w-12 h-12 rounded-2xl bg-cyan-50 dark:bg-cyan-950/60 border border-cyan-100 dark:border-cyan-800/40 text-cyan-500 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i data-lucide="users" class="w-6 h-6"></i>
                        </div>
                        <h3
                            class="text-base font-bold text-slate-900 dark:text-white mb-1.5 flex items-center justify-between">
                            <span>Customer</span>
                            <i data-lucide="arrow-right"
                                class="w-4 h-4 opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-[#00C2FF]"></i>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            Kelola customer, relationship, dan riwayat transaksi.
                        </p>
                    </a>

                    <!-- 5. Social Media -->
                    <a href="{{ route('public.omnichannel.social-media') }}"
                        class="group block p-6 rounded-3xl bg-white dark:bg-[#101726] border border-slate-100 dark:border-white/5 shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:shadow-[0_12px_30px_rgba(0,194,255,0.12)] hover:-translate-y-1 transition-all duration-300">
                        <div
                            class="w-12 h-12 rounded-2xl bg-pink-50 dark:bg-pink-950/60 border border-pink-100 dark:border-pink-800/40 flex items-center justify-center gap-1.5 mb-4 group-hover:scale-110 transition-transform">
                            <span class="w-4 h-4 rounded-[4px] bg-gradient-to-tr from-[#f09433] via-[#dc2743] to-[#bc1888] flex items-center justify-center text-white shrink-0 shadow-xs">
                                <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="2" y="2" width="20" height="20" rx="5"></rect><circle cx="12" cy="12" r="3.5"></circle></svg>
                            </span>
                            <span class="w-4 h-4 rounded-[4px] bg-[#1877F2] flex items-center justify-center text-white shrink-0 shadow-xs">
                                <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </span>
                        </div>
                        <h3
                            class="text-base font-bold text-slate-900 dark:text-white mb-1.5 flex items-center justify-between">
                            <span>Social Media</span>
                            <i data-lucide="arrow-right"
                                class="w-4 h-4 opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-[#00C2FF]"></i>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            Hubungkan akun social media dan kelola konten.
                        </p>
                    </a>

                    <!-- 6. Content Automation -->
                    <a href="{{ route('public.content.creation') }}"
                        class="group block p-6 rounded-3xl bg-white dark:bg-[#101726] border border-slate-100 dark:border-white/5 shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:shadow-[0_12px_30px_rgba(0,194,255,0.12)] hover:-translate-y-1 transition-all duration-300">
                        <div
                            class="w-12 h-12 rounded-2xl bg-fuchsia-50 dark:bg-fuchsia-950/60 border border-fuchsia-100 dark:border-fuchsia-800/40 text-fuchsia-500 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i data-lucide="megaphone" class="w-6 h-6"></i>
                        </div>
                        <h3
                            class="text-base font-bold text-slate-900 dark:text-white mb-1.5 flex items-center justify-between">
                            <span>Content Automation</span>
                            <i data-lucide="arrow-right"
                                class="w-4 h-4 opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-[#00C2FF]"></i>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            Plan, schedule, publish, dan monitor konten.
                        </p>
                    </a>

                    <!-- 7. Marketplace -->
                    <a href="{{ route('marketplace.index') }}"
                        class="group block p-6 rounded-3xl bg-white dark:bg-[#101726] border border-slate-100 dark:border-white/5 shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:shadow-[0_12px_30px_rgba(0,194,255,0.12)] hover:-translate-y-1 transition-all duration-300">
                        <div
                            class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-800/40 text-indigo-500 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i data-lucide="store" class="w-6 h-6"></i>
                        </div>
                        <h3
                            class="text-base font-bold text-slate-900 dark:text-white mb-1.5 flex items-center justify-between">
                            <span>Marketplace</span>
                            <i data-lucide="arrow-right"
                                class="w-4 h-4 opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-[#00C2FF]"></i>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            Tingkatkan penjualan melalui berbagai marketplace.
                        </p>
                    </a>

                    <!-- 8. Analytics & AI -->
                    <a href="{{ route('public.erp.analytics') }}"
                        class="group block p-6 rounded-3xl bg-white dark:bg-[#101726] border border-slate-100 dark:border-white/5 shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:shadow-[0_12px_30px_rgba(0,194,255,0.12)] hover:-translate-y-1 transition-all duration-300">
                        <div
                            class="w-12 h-12 rounded-2xl bg-violet-50 dark:bg-violet-950/60 border border-violet-100 dark:border-violet-800/40 text-violet-500 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i data-lucide="trending-up" class="w-6 h-6"></i>
                        </div>
                        <h3
                            class="text-base font-bold text-slate-900 dark:text-white mb-1.5 flex items-center justify-between">
                            <span>Analytics &amp; AI</span>
                            <i data-lucide="arrow-right"
                                class="w-4 h-4 opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-[#00C2FF]"></i>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            Ubah data menjadi insight untuk keputusan lebih baik.
                        </p>
                    </a>

                </div>
            </div>
        </section>


        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 3. INTEGRATED WORKFLOW ("Your Business, Connected End-to-End") ═══ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section
            class="bg-[#F8FAFC] dark:bg-[#070B18] py-20 lg:py-24 border-b border-slate-100 dark:border-white/5 transition-colors">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">

                    <!-- Left Column -->
                    <div class="lg:col-span-4 space-y-5 text-center lg:text-left">
                        <div
                            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-sky-100 dark:bg-sky-950/60 border border-sky-200 dark:border-sky-800/40 text-sky-700 dark:text-sky-300 text-xs font-semibold">
                            <span>Integrated Workflow</span>
                        </div>

                        <h2
                            class="text-3xl sm:text-3.5xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">
                            Your Business,<br class="hidden sm:inline"> Connected End-to-End
                        </h2>

                        <p
                            class="text-slate-600 dark:text-slate-400 text-sm sm:text-base leading-relaxed max-w-md mx-auto lg:mx-0">
                            Dari perolehan pelanggan hingga transaksi bisnis, semuanya saling terhubung dalam satu sistem.
                        </p>

                        <div>
                            <a href="{{ route('public.bos.how-it-works') }}"
                                class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-[#00C2FF] hover:bg-[#00B4D8] text-white font-bold text-xs sm:text-sm shadow-[0_0_20px_rgba(0,194,255,0.4)] hover:scale-105 active:scale-95 transition-all">
                                <span>Lihat Alur Lengkap</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Right Column: Connected Node Workflow -->
                    <div class="lg:col-span-8 overflow-x-auto pb-4 lg:pb-0">
                        <div class="min-w-[540px] space-y-6">

                            <!-- Row 1: Acquisition to POS & Inventory -->
                            <div
                                class="flex items-center justify-between gap-2 p-4 rounded-3xl bg-white dark:bg-[#101726] border border-slate-200/80 dark:border-white/5 shadow-sm">
                                <!-- Node 1: Social Media -->
                                <div class="flex flex-col items-center text-center">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-purple-500 text-white flex items-center justify-center shadow-md">
                                        <i data-lucide="share-2" class="w-5 h-5"></i>
                                    </div>
                                    <span class="text-[10px] font-semibold text-slate-700 dark:text-slate-300 mt-2">Social
                                        Media</span>
                                </div>

                                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>

                                <!-- Node 2: Content -->
                                <div class="flex flex-col items-center text-center">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-blue-500 text-white flex items-center justify-center shadow-md">
                                        <i data-lucide="send" class="w-5 h-5"></i>
                                    </div>
                                    <span
                                        class="text-[10px] font-semibold text-slate-700 dark:text-slate-300 mt-2">Content</span>
                                </div>

                                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>

                                <!-- Node 3: Customer -->
                                <div class="flex flex-col items-center text-center">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-cyan-500 text-white flex items-center justify-center shadow-md">
                                        <i data-lucide="users" class="w-5 h-5"></i>
                                    </div>
                                    <span
                                        class="text-[10px] font-semibold text-slate-700 dark:text-slate-300 mt-2">Customer</span>
                                </div>

                                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>

                                <!-- Node 4: Chat / Order -->
                                <div class="flex flex-col items-center text-center">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center shadow-md">
                                        <i data-lucide="message-square" class="w-5 h-5"></i>
                                    </div>
                                    <span class="text-[10px] font-semibold text-slate-700 dark:text-slate-300 mt-2">Chat /
                                        Order</span>
                                </div>

                                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>

                                <!-- Node 5: Kasir / POS -->
                                <div class="flex flex-col items-center text-center">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-rose-500 text-white flex items-center justify-center shadow-md">
                                        <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                                    </div>
                                    <span class="text-[10px] font-semibold text-slate-700 dark:text-slate-300 mt-2">Kasir /
                                        POS</span>
                                </div>

                                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>

                                <!-- Node 6: Inventory -->
                                <div class="flex flex-col items-center text-center">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-teal-500 text-white flex items-center justify-center shadow-md">
                                        <i data-lucide="boxes" class="w-5 h-5"></i>
                                    </div>
                                    <span
                                        class="text-[10px] font-semibold text-slate-700 dark:text-slate-300 mt-2">Inventory</span>
                                </div>
                            </div>

                            <!-- Row 2: Production to Analytics (Connecting back) -->
                            <div
                                class="flex items-center justify-between gap-2 p-4 rounded-3xl bg-white dark:bg-[#101726] border border-slate-200/80 dark:border-white/5 shadow-sm">
                                <!-- Node 7: Analytics -->
                                <div class="flex flex-col items-center text-center">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center shadow-md">
                                        <i data-lucide="bar-chart-2" class="w-5 h-5"></i>
                                    </div>
                                    <span
                                        class="text-[10px] font-semibold text-slate-700 dark:text-slate-300 mt-2">Analytics</span>
                                </div>

                                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>

                                <!-- Node 8: Accounting -->
                                <div class="flex flex-col items-center text-center">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shadow-md">
                                        <i data-lucide="book-marked" class="w-5 h-5"></i>
                                    </div>
                                    <span
                                        class="text-[10px] font-semibold text-slate-700 dark:text-slate-300 mt-2">Accounting</span>
                                </div>

                                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>

                                <!-- Node 9: Finance -->
                                <div class="flex flex-col items-center text-center">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-md">
                                        <i data-lucide="coins" class="w-5 h-5"></i>
                                    </div>
                                    <span
                                        class="text-[10px] font-semibold text-slate-700 dark:text-slate-300 mt-2">Finance</span>
                                </div>

                                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>

                                <!-- Node 10: Purchasing -->
                                <div class="flex flex-col items-center text-center">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-indigo-500 text-white flex items-center justify-center shadow-md">
                                        <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                                    </div>
                                    <span
                                        class="text-[10px] font-semibold text-slate-700 dark:text-slate-300 mt-2">Purchasing</span>
                                </div>

                                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>

                                <!-- Node 11: Production -->
                                <div class="flex flex-col items-center text-center">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-blue-500 text-white flex items-center justify-center shadow-md">
                                        <i data-lucide="factory" class="w-5 h-5"></i>
                                    </div>
                                    <span
                                        class="text-[10px] font-semibold text-slate-700 dark:text-slate-300 mt-2">Production</span>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </section>


        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 4. "LEBIH DARI SEKADAR ERP" (Dark Section with Laptop/Mobile Mockup) ═══ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="relative bg-[#080D1E] text-white py-20 lg:py-28 overflow-hidden">
            <!-- Ambient lighting -->
            <div
                class="absolute top-1/2 left-1/3 w-96 h-96 bg-[#00C2FF]/10 rounded-full blur-[130px] pointer-events-none -z-0">
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">

                <!-- Section Heading -->
                <div class="space-y-3 mb-14 text-center lg:text-left">
                    <div
                        class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-cyan-950/60 border border-cyan-400/30 text-cyan-300 text-xs font-semibold">
                        <span>Business Operating System</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                        Lebih dari Sekadar ERP
                    </h2>
                    <p class="text-slate-400 text-sm sm:text-base max-w-2xl leading-relaxed mx-auto lg:mx-0">
                        COOCA adalah Business Operating System yang membantu Anda mengelola, menghubungkan, dan
                        mengembangkan bisnis.
                    </p>
                </div>

                <!-- Content Grid: 6 Pillars (Left) & Realistic Laptop Dashboard (Right) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

                    <!-- Left: 6 Pillars (2x3 Grid) -->
                    <div class="lg:col-span-5 grid grid-cols-2 gap-6">

                        <!-- 1. Operate -->
                        <div class="space-y-2">
                            <div
                                class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-[#00C2FF]">
                                <i data-lucide="gauge" class="w-5 h-5"></i>
                            </div>
                            <h4 class="text-sm font-bold text-white">Operate</h4>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Kelola operasional bisnis sehari-hari.
                            </p>
                        </div>

                        <!-- 2. Sell -->
                        <div class="space-y-2">
                            <div
                                class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-[#00C2FF]">
                                <i data-lucide="tag" class="w-5 h-5"></i>
                            </div>
                            <h4 class="text-sm font-bold text-white">Sell</h4>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Kelola penjualan dari berbagai channel.
                            </p>
                        </div>

                        <!-- 3. Engage -->
                        <div class="space-y-2">
                            <div
                                class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-[#00C2FF]">
                                <i data-lucide="message-square" class="w-5 h-5"></i>
                            </div>
                            <h4 class="text-sm font-bold text-white">Engage</h4>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Bangun hubungan dengan customer.
                            </p>
                        </div>

                        <!-- 4. Automate -->
                        <div class="space-y-2">
                            <div
                                class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-[#00C2FF]">
                                <i data-lucide="zap" class="w-5 h-5"></i>
                            </div>
                            <h4 class="text-sm font-bold text-white">Automate</h4>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Kurangi pekerjaan manual dengan automation.
                            </p>
                        </div>

                        <!-- 5. Analyze -->
                        <div class="space-y-2">
                            <div
                                class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-[#00C2FF]">
                                <i data-lucide="line-chart" class="w-5 h-5"></i>
                            </div>
                            <h4 class="text-sm font-bold text-white">Analyze</h4>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Pahami bisnis melalui data dan analytics.
                            </p>
                        </div>

                        <!-- 6. Decide -->
                        <div class="space-y-2">
                            <div
                                class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-[#00C2FF]">
                                <i data-lucide="compass" class="w-5 h-5"></i>
                            </div>
                            <h4 class="text-sm font-bold text-white">Decide</h4>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Ambil keputusan lebih tepat dan cepat.
                            </p>
                        </div>

                    </div>

                    <!-- Right: Realistic MacBook & iPhone Showcase -->
                    <div class="lg:col-span-7 relative">
                        <div class="relative w-full max-w-lg mx-auto lg:max-w-none">

                            <!-- Laptop Body Mockup -->
                            <div
                                class="relative rounded-2xl bg-slate-900 border border-slate-700 shadow-[0_20px_50px_rgba(0,0,0,0.6)] overflow-hidden p-2 sm:p-3">
                                <!-- Laptop Header Bar -->
                                <div
                                    class="h-6 bg-slate-800 rounded-t-lg flex items-center px-3 gap-1.5 border-b border-slate-700">
                                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                    <span class="text-[10px] text-slate-400 ml-2 font-mono">cooca.id/app/dashboard</span>
                                </div>

                                <!-- Laptop Screen Content (COOCA UI) -->
                                <div class="bg-white text-slate-900 p-4 rounded-b-lg font-sans">
                                    <!-- Top Stats -->
                                    <div class="grid grid-cols-3 gap-3 mb-4">
                                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                                            <div class="text-[10px] text-slate-500">Penjualan Bulan Ini</div>
                                            <div class="text-sm font-extrabold text-slate-900 mt-0.5">Rp 48.250.000</div>
                                            <div class="text-[9px] text-emerald-600 font-semibold mt-0.5">+18.4% vs lalu
                                            </div>
                                        </div>
                                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                                            <div class="text-[10px] text-slate-500">Total Transaksi</div>
                                            <div class="text-sm font-extrabold text-slate-900 mt-0.5">1.420 Order</div>
                                            <div class="text-[9px] text-emerald-600 font-semibold mt-0.5">+12.1% kasir
                                            </div>
                                        </div>
                                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                                            <div class="text-[10px] text-slate-500">Stok Kritis</div>
                                            <div class="text-sm font-extrabold text-amber-600 mt-0.5">3 Barang</div>
                                            <div class="text-[9px] text-slate-400 mt-0.5">Auto Restock on</div>
                                        </div>
                                    </div>

                                    <!-- Revenue Growth Bar Chart / Wave Simulation -->
                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                        <div class="flex items-center justify-between text-xs mb-2">
                                            <span class="font-bold text-slate-800">Tren Penjualan Multi-Channel</span>
                                            <span class="text-[10px] text-[#00C2FF] font-semibold">Real-Time Sync</span>
                                        </div>
                                        <div class="h-24 flex items-end gap-2 pt-2">
                                            <div class="w-full bg-sky-200 rounded-t h-[45%]"></div>
                                            <div class="w-full bg-sky-300 rounded-t h-[60%]"></div>
                                            <div class="w-full bg-sky-400 rounded-t h-[50%]"></div>
                                            <div class="w-full bg-[#00C2FF] rounded-t h-[80%]"></div>
                                            <div class="w-full bg-sky-300 rounded-t h-[65%]"></div>
                                            <div class="w-full bg-[#00C2FF] rounded-t h-[95%]"></div>
                                            <div
                                                class="w-full bg-blue-600 rounded-t h-[100%] shadow-[0_0_10px_rgba(0,194,255,0.4)]">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- iPhone Mockup Floating Right -->
                            <div
                                class="absolute -right-4 -bottom-6 w-36 sm:w-44 rounded-[28px] bg-slate-950 p-2 border-2 border-slate-700 shadow-2xl hidden xs:block">
                                <div class="rounded-[22px] bg-white text-slate-900 p-2.5 text-center">
                                    <div class="w-8 h-1 bg-slate-800 rounded-full mx-auto mb-2"></div>
                                    <div class="text-[9px] font-bold text-slate-500 uppercase">POS Mobile</div>
                                    <div class="text-xs font-black text-slate-900 mt-1">Rp 1.850.000</div>
                                    <div class="mt-2 space-y-1 text-[9px] text-left text-slate-600">
                                        <div class="flex justify-between py-0.5 border-b border-slate-100">
                                            <span>Order #1029</span>
                                            <span class="text-emerald-600 font-bold">Lunas</span>
                                        </div>
                                        <div class="flex justify-between py-0.5 border-b border-slate-100">
                                            <span>Order #1028</span>
                                            <span class="text-emerald-600 font-bold">Lunas</span>
                                        </div>
                                    </div>
                                    <div class="mt-2.5 py-1 px-2 rounded-lg bg-[#00C2FF] text-white text-[9px] font-bold">
                                        Scan QRIS Kasir
                                    </div>
                                </div>
                            </div>

                            <!-- Floating AI Assistant Badge (Bottom Left) -->
                            <div
                                class="absolute -bottom-5 left-4 z-20 p-3.5 rounded-2xl bg-[#0b1633]/95 backdrop-blur-xl border border-cyan-400/40 shadow-[0_10px_30px_rgba(0,194,255,0.35)] flex items-center gap-3 max-w-xs">
                                <div
                                    class="w-8 h-8 rounded-xl bg-[#00C2FF] text-slate-950 flex items-center justify-center shrink-0 shadow-[0_0_15px_rgba(0,194,255,0.6)]">
                                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-cyan-300 block">AI Assistant</span>
                                    <p class="text-[11px] text-slate-200 leading-snug">
                                        Ringkasan penjualan hari ini meningkat 24% dibanding kemarin.
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </section>


        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 5. INTEGRASI OMNICHANNEL ("Jangkau Pelanggan di Semua Channel") ═══ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section
            class="bg-white dark:bg-[#070A14] py-20 lg:py-24 border-b border-slate-100 dark:border-white/5 transition-colors">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">

                    <!-- Left Column -->
                    <div class="lg:col-span-5 space-y-5 text-center lg:text-left">
                        <div
                            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-sky-100 dark:bg-sky-950/60 border border-sky-200 dark:border-sky-800/40 text-sky-700 dark:text-sky-300 text-xs font-semibold">
                            <span>Integrasi Omnichannel</span>
                        </div>

                        <h2
                            class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">
                            Jangkau Pelanggan<br class="hidden sm:inline"> di Semua Channel
                        </h2>

                        <p
                            class="text-slate-600 dark:text-slate-400 text-sm sm:text-base leading-relaxed max-w-md mx-auto lg:mx-0">
                            Dari media sosial, marketplace, WhatsApp hingga toko fisik, COOCA menghubungkan semua channel
                            penjualan Anda.
                        </p>

                        <div>
                            <a href="{{ route('public.omnichannel.social-media') }}"
                                class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-[#00C2FF] hover:bg-[#00B4D8] text-white font-bold text-xs sm:text-sm shadow-[0_0_20px_rgba(0,194,255,0.4)] hover:scale-105 active:scale-95 transition-all">
                                <span>Jelajahi Omnichannel</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Right Column: Omnichannel Convergence Diagram -->
                    <div class="lg:col-span-7">
                        <div
                            class="p-6 sm:p-8 rounded-3xl bg-gradient-to-b from-[#F0F8FF] to-[#E6F4FE] dark:from-[#0C162D] dark:to-[#081022] border border-[#D4EAFB] dark:border-cyan-900/30 text-center relative overflow-hidden">

                            <!-- Top: Channel Badges (Instagram, TikTok, Facebook, WhatsApp, Shopee, Tokopedia, Store) -->
                            <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4 relative z-10">
                                <!-- Instagram -->
                                <div
                                    class="w-11 h-11 rounded-2xl bg-white dark:bg-[#141F36] shadow-sm border border-slate-200/60 dark:border-white/10 flex items-center justify-center hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none">
                                        <defs>
                                            <radialGradient id="ig-grad-conv" cx="20%" cy="115%" r="130%">
                                                <stop offset="0%" stop-color="#ffd600"/>
                                                <stop offset="10%" stop-color="#ff7a00"/>
                                                <stop offset="50%" stop-color="#ff0169"/>
                                                <stop offset="100%" stop-color="#d300c5"/>
                                            </radialGradient>
                                        </defs>
                                        <rect x="2" y="2" width="20" height="20" rx="5.5" fill="url(#ig-grad-conv)"/>
                                        <rect x="5.5" y="5.5" width="13" height="13" rx="3.5" stroke="white" stroke-width="1.8"/>
                                        <circle cx="12" cy="12" r="3.2" stroke="white" stroke-width="1.8"/>
                                        <circle cx="15.8" cy="8.2" r="0.9" fill="white"/>
                                    </svg>
                                </div>
                                <!-- TikTok -->
                                <div
                                    class="w-11 h-11 rounded-2xl bg-black shadow-sm border border-slate-800 flex items-center justify-center hover:scale-110 transition-transform text-white">
                                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                        <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64c.298-.002.595.042.88.13V9.4a6.33 6.33 0 0 0-1-.08A6.34 6.34 0 0 0 3 15.66a6.34 6.34 0 0 0 10.82 4.49 6.27 6.27 0 0 0 1.93-4.49V8.6a8.18 8.18 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-.93-.03z"/>
                                    </svg>
                                </div>
                                <!-- Facebook -->
                                <div
                                    class="w-11 h-11 rounded-2xl bg-[#1877F2] shadow-sm flex items-center justify-center hover:scale-110 transition-transform text-white">
                                    <svg class="w-5 h-5 fill-white" viewBox="0 0 24 24">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                    </svg>
                                </div>
                                <!-- WhatsApp -->
                                <div
                                    class="w-11 h-11 rounded-2xl bg-[#25D366] shadow-sm flex items-center justify-center hover:scale-110 transition-transform text-white">
                                    <svg class="w-5 h-5 fill-white" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                    </svg>
                                </div>
                                <!-- Shopee -->
                                <div
                                    class="w-11 h-11 rounded-2xl bg-[#EE4D2D] shadow-sm flex items-center justify-center hover:scale-110 transition-transform text-white">
                                    <svg class="w-5 h-5 fill-white" viewBox="0 0 24 24">
                                        <path d="M19.5 7.5h-2.25V6.75C17.25 3.85 14.9 1.5 12 1.5S6.75 3.85 6.75 6.75v.75H4.5C3.67 7.5 3 8.17 3 9v11.25C3 21.08 3.67 22.5 4.5 22.5h15c.83 0 1.5-1.42 1.5-2.25V9c0-.83-.67-1.5-1.5-1.5zm-11.25-.75c0-2.07 1.68-3.75 3.75-3.75s3.75 1.68 3.75 3.75v.75h-7.5v-.75zm6.47 8.38c-.14.86-.87 1.47-1.87 1.47-1.28 0-2.02-.91-2.02-2.08 0-1.41.97-2.14 2.12-2.45l.6-.16c.55-.14.81-.36.81-.72 0-.44-.39-.75-.98-.75-.62 0-1 .31-1.07.82h-1.35c.08-1.21.96-2.05 2.42-2.05 1.34 0 2.34.78 2.34 2 0 1.05-.62 1.76-1.83 2.08l-.66.17c-.69.18-.94.45-.94.88 0 .54.45.87 1.07.87.69 0 1.15-.37 1.22-.96h1.37z"/>
                                    </svg>
                                </div>
                                <!-- Tokopedia -->
                                <div
                                    class="w-11 h-11 rounded-2xl bg-[#03AC0E] shadow-sm flex items-center justify-center hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.5 8.5C4.5 7.67 5.17 7 6 7H18C18.83 7 19.5 7.67 19.5 8.5V19C19.5 20.1 18.6 21 17.5 21H6.5C5.4 21 4.5 20.1 4.5 19V8.5Z" fill="white"/>
                                        <path d="M8.5 7V5.5C8.5 3.57 10.07 2 12 2C13.93 2 15.5 3.57 15.5 5.5V7" stroke="white" stroke-width="2" stroke-linecap="round"/>
                                        <circle cx="9.2" cy="13" r="2.3" fill="#03AC0E"/>
                                        <circle cx="14.8" cy="13" r="2.3" fill="#03AC0E"/>
                                        <circle cx="9.8" cy="12.4" r="0.75" fill="white"/>
                                        <circle cx="15.4" cy="12.4" r="0.75" fill="white"/>
                                        <path d="M10.8 16.5C11.3 17 12.7 17 13.2 16.5" stroke="#03AC0E" stroke-width="1.2" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <!-- Toko Fisik -->
                                <div
                                    class="w-11 h-11 rounded-2xl bg-[#4F46E5] shadow-sm flex items-center justify-center hover:scale-110 transition-transform text-white">
                                    <i data-lucide="store" class="w-5 h-5"></i>
                                </div>
                            </div>

                            <!-- Connector Lines to Central Badge -->
                            <div class="h-10 flex items-center justify-center my-2">
                                <div class="w-0.5 h-full bg-gradient-to-b from-[#00C2FF] to-transparent"></div>
                            </div>

                            <!-- Center COOCA Hub Badge -->
                            <div
                                class="inline-flex items-center justify-center px-8 py-2.5 rounded-full bg-[#00C2FF] text-white font-extrabold text-sm shadow-[0_4px_20px_rgba(0,194,255,0.45)]">
                                COOCA
                            </div>

                            <!-- Flow Arrow Down -->
                            <div class="h-8 flex items-center justify-center my-2">
                                <i data-lucide="chevron-down" class="w-5 h-5 text-[#00C2FF] animate-bounce"></i>
                            </div>

                            <!-- Bottom Process Flow -->
                            <div
                                class="flex items-center justify-center gap-4 sm:gap-6 text-slate-700 dark:text-slate-300">
                                <!-- Order -->
                                <div class="flex items-center gap-1.5 text-xs font-semibold">
                                    <i data-lucide="shopping-cart" class="w-4 h-4 text-sky-500"></i>
                                    <span>Order</span>
                                </div>

                                <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-400"></i>

                                <!-- Inventory -->
                                <div class="flex items-center gap-1.5 text-xs font-semibold">
                                    <i data-lucide="boxes" class="w-4 h-4 text-purple-500"></i>
                                    <span>Inventory</span>
                                </div>

                                <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-400"></i>

                                <!-- Finance -->
                                <div class="flex items-center gap-1.5 text-xs font-semibold">
                                    <i data-lucide="badge-dollar-sign" class="w-4 h-4 text-blue-500"></i>
                                    <span>Finance</span>
                                </div>

                                <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-400"></i>

                                <!-- Customer -->
                                <div class="flex items-center gap-1.5 text-xs font-semibold">
                                    <i data-lucide="user-check" class="w-4 h-4 text-emerald-500"></i>
                                    <span>Customer</span>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </section>


        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 6. SPLIT SECTION: CONTENT AUTOMATION & MARKETPLACE ═══ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section
            class="bg-[#F8FAFC] dark:bg-[#070B18] py-20 lg:py-24 border-b border-slate-100 dark:border-white/5 transition-colors">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">

                    <!-- ════ LEFT CARD: CONTENT AUTOMATION ════ -->
                    <div
                        class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-[#101726] border border-slate-200/80 dark:border-white/5 shadow-sm space-y-6">
                        <div>
                            <span class="text-xs font-bold text-[#00C2FF] uppercase tracking-wider block mb-1">Content
                                Automation</span>
                            <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                                Kelola Konten, Maksimalkan Dampak
                            </h3>
                            <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm mt-2 leading-relaxed">
                                Buat, jadwalkan, dan publikasikan konten ke berbagai platform sosial media dari satu tempat.
                            </p>
                        </div>

                        <div>
                            <a href="{{ route('public.content.creation') }}"
                                class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-full bg-[#00C2FF] hover:bg-[#00B4D8] text-white font-bold text-xs shadow-sm hover:scale-105 active:scale-95 transition-all">
                                <span>Pelajari Lebih Lanjut</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>

                        <!-- Visual UI Mockup (Composer + Scheduled Posts) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                            <!-- Post Preview with Coffee/Pastry -->
                            <div
                                class="rounded-2xl border border-slate-100 dark:border-white/10 overflow-hidden bg-slate-50 dark:bg-slate-900/60 p-3">
                                <img src="https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=600&q=80"
                                    alt="Post Preview" class="w-full h-32 object-cover rounded-xl mb-2.5">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <div class="w-5 h-5 rounded-md bg-gradient-to-tr from-[#f09433] via-[#dc2743] to-[#bc1888] flex items-center justify-center text-white shrink-0 shadow-xs">
                                        <svg class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                            <circle cx="12" cy="12" r="3.5"></circle>
                                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" stroke-width="2.5"></line>
                                        </svg>
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-700 dark:text-slate-200">Kopi Susu Aren Spesial</span>
                                </div>
                                <p class="text-[9px] text-slate-500 dark:text-slate-400 line-clamp-2">
                                    Awali pagi harimu dengan sensasi creamy gula aren murni. Promo buy 1 get 1 hari ini!
                                </p>
                            </div>

                            <!-- Scheduled Channels List -->
                            <div
                                class="rounded-2xl border border-slate-100 dark:border-white/10 bg-slate-50 dark:bg-slate-900/60 p-3 flex flex-col justify-between">
                                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Jadwal
                                    Posting</div>
                                <div class="space-y-2">
                                    <div
                                        class="flex items-center justify-between p-2 rounded-xl bg-white dark:bg-slate-800 text-[10px]">
                                        <div class="flex items-center gap-2">
                                            <span class="w-4 h-4 rounded-[4px] bg-gradient-to-tr from-[#f09433] via-[#dc2743] to-[#bc1888] flex items-center justify-center text-white shrink-0 shadow-xs">
                                                <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="2" y="2" width="20" height="20" rx="5"></rect><circle cx="12" cy="12" r="3.5"></circle></svg>
                                            </span>
                                            <span class="font-bold text-slate-700 dark:text-slate-200">Instagram</span>
                                        </div>
                                        <span class="font-mono text-slate-500 font-semibold">12:00</span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-2 rounded-xl bg-white dark:bg-slate-800 text-[10px]">
                                        <div class="flex items-center gap-2">
                                            <span class="w-4 h-4 rounded-[4px] bg-[#1877F2] flex items-center justify-center text-white shrink-0 shadow-xs">
                                                <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                            </span>
                                            <span class="font-bold text-slate-700 dark:text-slate-200">Facebook</span>
                                        </div>
                                        <span class="font-mono text-slate-500 font-semibold">15:00</span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-2 rounded-xl bg-white dark:bg-slate-800 text-[10px]">
                                        <div class="flex items-center gap-2">
                                            <span class="w-4 h-4 rounded-[4px] bg-black flex items-center justify-center text-white shrink-0 shadow-xs">
                                                <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64c.298-.002.595.042.88.13V9.4a6.33 6.33 0 0 0-1-.08A6.34 6.34 0 0 0 3 15.66a6.34 6.34 0 0 0 10.82 4.49 6.27 6.27 0 0 0 1.93-4.49V8.6a8.18 8.18 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-.93-.03z"/></svg>
                                            </span>
                                            <span class="font-bold text-slate-700 dark:text-slate-200">TikTok</span>
                                        </div>
                                        <span class="font-mono text-slate-500 font-semibold">19:00</span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-2 rounded-xl bg-white dark:bg-slate-800 text-[10px]">
                                        <div class="flex items-center gap-2">
                                            <span class="w-4 h-4 rounded-[4px] bg-slate-900 dark:bg-slate-700 flex items-center justify-center text-white shrink-0 shadow-xs">
                                                <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 192 192"><path d="M141.537 88.9883C140.71 88.5919 139.87 88.2104 139.019 87.8451C137.537 60.5382 122.616 44.905 97.5619 44.745C97.4484 44.7443 97.3355 44.7443 97.222 44.7443C82.2364 44.7443 69.7731 51.1409 62.102 62.7807L75.0592 71.3093C80.8988 62.4521 90.0768 59.8804 97.222 59.8804C108.685 59.8804 117.845 66.868 119.539 80.0827C114.733 79.5298 109.689 79.3149 104.423 79.4357C76.8532 80.0682 60.1009 94.6192 60.6276 114.947C61.1274 134.249 76.5414 147.256 95.8087 147.256C112.527 147.256 122.951 138.891 127.818 126.049C133.513 135.539 141.874 140.897 153.861 140.897C168.423 140.897 178.688 129.845 178.688 110.822C178.688 77.0864 153.255 46.7583 103.784 46.7583C61.4287 46.7583 31.7828 75.3129 31.7828 116.634C31.7828 157.069 60.2783 186.256 103.047 186.256C121.758 186.256 137.957 180.378 149.336 169.311L138.647 157.859C129.742 166.52 117.579 171.12 103.047 171.12C70.6205 171.12 47.0116 148.145 47.0116 116.634C47.0116 83.9877 70.1878 61.8944 103.784 61.8944C143.082 61.8944 163.552 86.8778 163.552 110.822C163.552 121.849 157.947 127.807 149.605 127.807C142.062 127.807 136.702 122.569 134.195 112.518C139.734 104.305 142.115 94.7578 141.537 88.9883ZM103.957 132.183C88.4279 132.183 75.8776 123.633 75.5256 110.027C75.1432 95.2476 87.2796 93.3087 104.225 93.3087C109.07 93.3087 113.673 93.6309 117.917 94.2755C115.656 118.91 106.671 132.183 103.957 132.183Z"/></svg>
                                            </span>
                                            <span class="font-bold text-slate-700 dark:text-slate-200">Threads</span>
                                        </div>
                                        <span class="font-mono text-slate-500 font-semibold">21:00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ════ RIGHT CARD: MARKETPLACE ════ -->
                    <div
                        class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-[#101726] border border-slate-200/80 dark:border-white/5 shadow-sm space-y-6">
                        <div>
                            <span
                                class="text-xs font-bold text-[#00C2FF] uppercase tracking-wider block mb-1">Marketplace</span>
                            <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                                Temukan &amp; Jual Lebih Mudah
                            </h3>
                            <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm mt-2 leading-relaxed">
                                Jelajahi ribuan bisnis dan produk dari berbagai kategori dan lokasi. Dukung pertumbuhan
                                bisnis lokal.
                            </p>
                        </div>

                        <div>
                            <a href="{{ route('marketplace.index') }}"
                                class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-full bg-[#00C2FF] hover:bg-[#00B4D8] text-white font-bold text-xs shadow-sm hover:scale-105 active:scale-95 transition-all">
                                <span>Jelajahi Marketplace</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>

                        <!-- Visual UI Mockup (Search + Filter Pills + 3 Product Cards) -->
                        <div class="space-y-3 pt-2">
                            <!-- Search & Filter Bar -->
                            <div
                                class="flex items-center gap-2 p-2 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-white/10 text-xs">
                                <i data-lucide="search" class="w-4 h-4 text-slate-400 ml-1"></i>
                                <span class="text-slate-400 text-[11px]">Cari produk atau bisnis...</span>
                                <div
                                    class="ml-auto flex items-center gap-1 text-[10px] text-slate-500 bg-white dark:bg-slate-800 px-2 py-1 rounded-lg border border-slate-100 dark:border-white/5">
                                    <i data-lucide="map-pin" class="w-3 h-3 text-rose-500"></i>
                                    <span>Semua Kota</span>
                                </div>
                            </div>

                            <!-- Category Filter Chips -->
                            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-[10px]">
                                <span
                                    class="px-2.5 py-1 rounded-full bg-[#00C2FF] text-white font-bold shrink-0">Semua</span>
                                <span
                                    class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-medium shrink-0">F&amp;B</span>
                                <span
                                    class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-medium shrink-0">Retail</span>
                                <span
                                    class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-medium shrink-0">Workshop</span>
                                <span
                                    class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-medium shrink-0">Laundry</span>
                                <span
                                    class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-medium shrink-0">Lainnya</span>
                            </div>

                            <!-- 3 Mini Product Cards -->
                            <div class="grid grid-cols-3 gap-2.5">
                                <!-- Product 1: Nasi Bebek Jakarta -->
                                <div
                                    class="p-2 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-white/10 text-left">
                                    <img src="https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=400&q=80"
                                        alt="Nasi Bebek" class="w-full h-16 object-cover rounded-lg mb-1.5">
                                    <div class="text-[10px] font-bold text-slate-800 dark:text-white truncate">Nasi Bebek
                                        Madura</div>
                                    <div class="text-[9px] text-slate-400">F&amp;B</div>
                                    <div
                                        class="flex items-center justify-between text-[9px] mt-1 font-semibold text-slate-600 dark:text-slate-300">
                                        <span class="flex items-center gap-0.5 text-amber-500">
                                            <i data-lucide="star" class="w-2.5 h-2.5 fill-current"></i> 4.9
                                        </span>
                                        <span>Jakarta</span>
                                    </div>
                                </div>

                                <!-- Product 2: Kopi Susu Aren -->
                                <div
                                    class="p-2 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-white/10 text-left">
                                    <img src="https://images.unsplash.com/photo-1517256064527-09c73fc73e38?auto=format&fit=crop&w=400&q=80"
                                        alt="Kopi Susu" class="w-full h-16 object-cover rounded-lg mb-1.5">
                                    <div class="text-[10px] font-bold text-slate-800 dark:text-white truncate">Kopi Susu
                                        Aren</div>
                                    <div class="text-[9px] text-slate-400">Minuman</div>
                                    <div
                                        class="flex items-center justify-between text-[9px] mt-1 font-semibold text-slate-600 dark:text-slate-300">
                                        <span class="flex items-center gap-0.5 text-amber-500">
                                            <i data-lucide="star" class="w-2.5 h-2.5 fill-current"></i> 4.7
                                        </span>
                                        <span>Bandung</span>
                                    </div>
                                </div>

                                <!-- Product 3: Service AC -->
                                <div
                                    class="p-2 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-white/10 text-left">
                                    <img src="https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=400&q=80"
                                        alt="Service AC" class="w-full h-16 object-cover rounded-lg mb-1.5">
                                    <div class="text-[10px] font-bold text-slate-800 dark:text-white truncate">Jasa Service
                                        AC</div>
                                    <div class="text-[9px] text-slate-400">Jasa</div>
                                    <div
                                        class="flex items-center justify-between text-[9px] mt-1 font-semibold text-slate-600 dark:text-slate-300">
                                        <span class="flex items-center gap-0.5 text-amber-500">
                                            <i data-lucide="star" class="w-2.5 h-2.5 fill-current"></i> 4.8
                                        </span>
                                        <span>Surabaya</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </section>


        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 7. "COCOK UNTUK BERBAGAI JENIS BISNIS" (Solutions) ═══ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="bg-white dark:bg-[#070A14] py-20 lg:py-24 transition-colors">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Section Header with Right-Aligned Button -->
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-10">
                    <div class="space-y-2">
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                            Cocok untuk Berbagai Jenis Bisnis
                        </h2>
                        <p class="text-slate-600 dark:text-slate-400 text-sm sm:text-base">
                            COOCA dapat disesuaikan dengan kebutuhan berbagai jenis industri.
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('public.solutions.fnb') }}"
                            class="inline-flex items-center gap-1.5 px-6 py-2.5 rounded-full bg-[#00C2FF] hover:bg-[#00B4D8] text-white font-bold text-xs sm:text-sm shadow-[0_0_15px_rgba(0,194,255,0.35)] hover:scale-105 active:scale-95 transition-all">
                            <span>Lihat Semua Solusi</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>

                <!-- 6 Industry Photo Cards -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">

                    <!-- 1. F&B -->
                    <a href="{{ route('public.solutions.fnb') }}"
                        class="group relative h-48 rounded-2xl overflow-hidden block shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                        <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=600&q=80"
                            alt="F&B Restoran"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/30 to-transparent">
                        </div>
                        <div class="absolute bottom-3 inset-x-3 flex items-center justify-between text-white">
                            <span class="text-xs font-bold">F&amp;B</span>
                            <i data-lucide="arrow-right"
                                class="w-3.5 h-3.5 opacity-80 group-hover:translate-x-1 group-hover:opacity-100 transition-all"></i>
                        </div>
                    </a>

                    <!-- 2. Retail -->
                    <a href="{{ route('public.solutions.retail') }}"
                        class="group relative h-48 rounded-2xl overflow-hidden block shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                        <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=600&q=80"
                            alt="Retail Toko"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/30 to-transparent">
                        </div>
                        <div class="absolute bottom-3 inset-x-3 flex items-center justify-between text-white">
                            <span class="text-xs font-bold">Retail</span>
                            <i data-lucide="arrow-right"
                                class="w-3.5 h-3.5 opacity-80 group-hover:translate-x-1 group-hover:opacity-100 transition-all"></i>
                        </div>
                    </a>

                    <!-- 3. Workshop -->
                    <a href="{{ route('public.solutions.workshop') }}"
                        class="group relative h-48 rounded-2xl overflow-hidden block shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                        <img src="https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?auto=format&fit=crop&w=600&q=80"
                            alt="Bengkel"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/30 to-transparent">
                        </div>
                        <div class="absolute bottom-3 inset-x-3 flex items-center justify-between text-white">
                            <span class="text-xs font-bold">Workshop</span>
                            <i data-lucide="arrow-right"
                                class="w-3.5 h-3.5 opacity-80 group-hover:translate-x-1 group-hover:opacity-100 transition-all"></i>
                        </div>
                    </a>

                    <!-- 4. Laundry -->
                    <a href="{{ route('public.solutions.laundry') }}"
                        class="group relative h-48 rounded-2xl overflow-hidden block shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                        <img src="https://images.unsplash.com/photo-1517677208171-0bc6725a3e60?auto=format&fit=crop&w=600&q=80"
                            alt="Laundry"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/30 to-transparent">
                        </div>
                        <div class="absolute bottom-3 inset-x-3 flex items-center justify-between text-white">
                            <span class="text-xs font-bold">Laundry</span>
                            <i data-lucide="arrow-right"
                                class="w-3.5 h-3.5 opacity-80 group-hover:translate-x-1 group-hover:opacity-100 transition-all"></i>
                        </div>
                    </a>

                    <!-- 5. Manufacturing -->
                    <a href="{{ route('public.solutions.manufacturing') }}"
                        class="group relative h-48 rounded-2xl overflow-hidden block shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                        <img src="https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&w=600&q=80"
                            alt="Manufacturing"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/30 to-transparent">
                        </div>
                        <div class="absolute bottom-3 inset-x-3 flex items-center justify-between text-white">
                            <span class="text-xs font-bold">Manufacturing</span>
                            <i data-lucide="arrow-right"
                                class="w-3.5 h-3.5 opacity-80 group-hover:translate-x-1 group-hover:opacity-100 transition-all"></i>
                        </div>
                    </a>

                    <!-- 6. Services -->
                    <a href="{{ route('public.solutions.services') }}"
                        class="group relative h-48 rounded-2xl overflow-hidden block shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                        <img src="https://images.unsplash.com/photo-1600880292203-757bb62b4baf?auto=format&fit=crop&w=600&q=80"
                            alt="Services Jasa"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/30 to-transparent">
                        </div>
                        <div class="absolute bottom-3 inset-x-3 flex items-center justify-between text-white">
                            <span class="text-xs font-bold">Services</span>
                            <i data-lucide="arrow-right"
                                class="w-3.5 h-3.5 opacity-80 group-hover:translate-x-1 group-hover:opacity-100 transition-all"></i>
                        </div>
                    </a>

                </div>
            </div>
        </section>


        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 8. BOTTOM CTA BANNER (Dark with Desk Setup & Handwriting) ═══ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="relative bg-[#070B19] text-white py-20 lg:py-24 overflow-hidden border-t border-white/10">
            <!-- Ambient lighting -->
            <div
                class="absolute -top-24 right-1/4 w-[450px] h-[450px] bg-[#00C2FF]/15 rounded-full blur-[140px] pointer-events-none">
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

                    <!-- Left Column -->
                    <div class="lg:col-span-6 space-y-6 text-center lg:text-left">
                        <div
                            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-cyan-950/60 border border-cyan-400/30 text-cyan-300 text-xs font-semibold">
                            <span>Saatnya Beralih ke COOCA</span>
                        </div>

                        <h2
                            class="text-3xl sm:text-4xl lg:text-[44px] font-extrabold text-white tracking-tight leading-tight">
                            Kelola Bisnis Anda dengan Lebih Mudah, Terintegrasi, dan Cerdas.
                        </h2>

                        <p class="text-slate-300 text-sm sm:text-base leading-relaxed max-w-xl mx-auto lg:mx-0">
                            Mulai dari kebutuhan bisnis Anda. Pilih modul, hubungkan channel, dan jalankan bisnis Anda
                            bersama COOCA.
                        </p>

                        <!-- Auth-Aware Dual CTAs -->
                        <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3 pt-2">
                            @if (auth('admin')->check())
                                <a href="{{ route('admin.dashboard') }}"
                                    class="w-full sm:w-auto px-7 py-3.5 rounded-full bg-[#00C2FF] hover:bg-[#00B4D8] text-slate-950 font-bold text-sm flex items-center justify-center gap-2 shadow-[0_0_25px_rgba(0,194,255,0.45)] hover:scale-105 active:scale-95 transition-all">
                                    <span>Dashboard Admin</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            @elseif (auth('web')->check())
                                <a href="{{ route('dashboard') }}"
                                    class="w-full sm:w-auto px-7 py-3.5 rounded-full bg-[#00C2FF] hover:bg-[#00B4D8] text-slate-950 font-bold text-sm flex items-center justify-center gap-2 shadow-[0_0_25px_rgba(0,194,255,0.45)] hover:scale-105 active:scale-95 transition-all">
                                    <span>Ke Dashboard</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            @else
                                <a href="{{ route('register') }}"
                                    class="w-full sm:w-auto px-7 py-3.5 rounded-full bg-[#00C2FF] hover:bg-[#00B4D8] text-slate-950 font-bold text-sm flex items-center justify-center gap-2 shadow-[0_0_25px_rgba(0,194,255,0.45)] hover:scale-105 active:scale-95 transition-all">
                                    <span>Coba COOCA Gratis</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                                <a href="{{ route('public.demo') }}"
                                    class="w-full sm:w-auto px-6 py-3.5 rounded-full bg-white/5 hover:bg-white/10 border border-white/20 text-white font-semibold text-sm flex items-center justify-center gap-2 hover:border-white/30 hover:scale-105 active:scale-95 transition-all">
                                    <span>Lihat Demo</span>
                                    <i data-lucide="play" class="w-3.5 h-3.5 fill-current"></i>
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Right Column: Laptop Workspace with Handwritten Annotation -->
                    <div class="lg:col-span-6 relative">
                        <div class="relative max-w-md mx-auto lg:max-w-none">

                            <!-- Handwritten Annotation (One System, Endless Possibilities) -->
                            <div class="absolute -top-10 right-4 z-20 hidden sm:block text-right">
                                <span style="font-family: 'Caveat', cursive;"
                                    class="text-2xl sm:text-3xl text-cyan-300 font-bold block transform -rotate-3 drop-shadow-[0_2px_10px_rgba(0,194,255,0.5)]">
                                    One System, Endless Possibilities
                                </span>
                                <!-- Hand-drawn curved arrow pointing to laptop -->
                                <svg class="w-16 h-8 text-cyan-300 ml-auto mt-0.5" viewBox="0 0 100 50" fill="none"
                                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                    <path d="M 85 5 Q 50 35 15 45" />
                                    <path d="M 15 45 L 28 40" />
                                    <path d="M 15 45 L 24 32" />
                                </svg>
                            </div>

                            <!-- Desk Mockup Container -->
                            <div
                                class="rounded-3xl overflow-hidden border border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.5)] bg-slate-900">
                                <img src="https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&w=1000&q=80"
                                    alt="COOCA Workspace"
                                    class="w-full h-72 sm:h-80 object-cover opacity-90 hover:opacity-100 transition-opacity duration-300">
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </section>

    </div>
@endsection
