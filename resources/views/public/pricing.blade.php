@extends('layouts.public_marketing')

@section('title', 'Pilih Paket & Harga Transparan - Business Operating System | COOCA')
@section('description',
    'Mulai dari versi gratis dengan fitur dasar hingga paket lengkap dengan AI dan omnichannel.
    Semua paket sudah termasuk akses ke ekosistem COOCA.')

@section('content')
    <div x-data="{
        pricingCycle: 'monthly',
        openFaq: null,
        toggleFaq(idx) {
            this.openFaq = this.openFaq === idx ? null : idx;
        }
    }" class="w-full font-sans antialiased overflow-hidden">

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 1. HERO SECTION (Dark Midnight Blue with Ecosystem Visual) ══════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section
            class="relative bg-[#060B1E] text-white pt-10 sm:pt-16 pb-20 lg:pb-28 overflow-hidden border-b border-white/10">

            <!-- Subtle Ambient Background Glows -->
            <div
                class="absolute -top-24 right-1/4 w-[500px] h-[500px] bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none -z-0">
            </div>
            <div
                class="absolute bottom-0 left-1/4 w-[400px] h-[400px] bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none -z-0">
            </div>

            <div
                class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">

                <!-- Left Column: Copy, Headline & 5 Core Pillars -->
                <div class="lg:col-span-6 space-y-6 text-left">

                    <!-- Pill Badge -->
                    <div
                        class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#007AFF]/15 text-[#00C4D8] border border-[#00C4D8]/30 text-xs font-semibold tracking-wide">
                        <span>Harga Transparan • Tanpa Biaya Tersembunyi</span>
                    </div>

                    <!-- Main Heading -->
                    <h1
                        class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.75rem] xl:text-[3.25rem] font-extrabold text-white tracking-tight leading-[1.2] text-balance break-words">
                        Pilih Paket yang Sesuai dengan <span class="text-[#00C4D8]">Kebutuhan Bisnis Anda</span>
                    </h1>

                    <!-- Subtitle -->
                    <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-xl font-normal text-pretty">
                        Mulai dari yang gratis, hingga paket lengkap dengan fitur AI dan omnichannel. Semua paket sudah
                        termasuk akses ke ekosistem COOCA.
                    </p>

                    <!-- 5 Feature Badges Row -->
                    <div class="pt-4 grid grid-cols-3 sm:grid-cols-5 gap-4 sm:gap-3 items-start">

                        <!-- 1. ERP Lengkap -->
                        <div class="flex flex-col items-center text-center space-y-2 group cursor-default min-w-0">
                            <div
                                class="w-12 h-12 rounded-full border border-sky-400/40 bg-sky-500/10 flex items-center justify-center text-[#00C4D8] shadow-[0_0_15px_rgba(0,196,216,0.15)] group-hover:border-sky-400 group-hover:scale-105 transition-all shrink-0">
                                <i data-lucide="layers" class="w-5 h-5"></i>
                            </div>
                            <span class="text-xs text-slate-300 font-medium leading-tight">ERP<br>Lengkap</span>
                        </div>

                        <!-- 2. Omnichannel Terintegrasi -->
                        <div class="flex flex-col items-center text-center space-y-2 group cursor-default min-w-0">
                            <div
                                class="w-12 h-12 rounded-full border border-sky-400/40 bg-sky-500/10 flex items-center justify-center text-[#00C4D8] shadow-[0_0_15px_rgba(0,196,216,0.15)] group-hover:border-sky-400 group-hover:scale-105 transition-all shrink-0">
                                <i data-lucide="share-2" class="w-5 h-5"></i>
                            </div>
                            <span
                                class="text-xs text-slate-300 font-medium leading-tight">Omnichannel<br>Terintegrasi</span>
                        </div>

                        <!-- 3. Content Automation -->
                        <div class="flex flex-col items-center text-center space-y-2 group cursor-default min-w-0">
                            <div
                                class="w-12 h-12 rounded-full border border-sky-400/40 bg-sky-500/10 flex items-center justify-center text-[#00C4D8] shadow-[0_0_15px_rgba(0,196,216,0.15)] group-hover:border-sky-400 group-hover:scale-105 transition-all shrink-0">
                                <i data-lucide="pen-tool" class="w-5 h-5"></i>
                            </div>
                            <span class="text-xs text-slate-300 font-medium leading-tight">Content Automation<br>&amp;
                                Social Media</span>
                        </div>

                        <!-- 4. Marketplace & Order -->
                        <div class="flex flex-col items-center text-center space-y-2 group cursor-default min-w-0">
                            <div
                                class="w-12 h-12 rounded-full border border-sky-400/40 bg-sky-500/10 flex items-center justify-center text-[#00C4D8] shadow-[0_0_15px_rgba(0,196,216,0.15)] group-hover:border-sky-400 group-hover:scale-105 transition-all shrink-0">
                                <i data-lucide="store" class="w-5 h-5"></i>
                            </div>
                            <span class="text-xs text-slate-300 font-medium leading-tight">Marketplace<br>&amp; Order</span>
                        </div>

                        <!-- 5. AI Assistant -->
                        <div class="flex flex-col items-center text-center space-y-2 group cursor-default min-w-0">
                            <div
                                class="w-12 h-12 rounded-full border border-sky-400/40 bg-sky-500/10 flex items-center justify-center text-[#00C4D8] shadow-[0_0_15px_rgba(0,196,216,0.15)] group-hover:border-sky-400 group-hover:scale-105 transition-all shrink-0">
                                <i data-lucide="cpu" class="w-5 h-5"></i>
                            </div>
                            <span class="text-xs text-slate-300 font-medium leading-tight">AI Assistant<br>(berbayar)</span>
                        </div>

                    </div>

                </div>

                <!-- Right Column: Interactive Ecosystem Mockup with Network Nodes -->
                <div class="lg:col-span-6 relative flex items-center justify-center">

                    <div class="relative w-full max-w-xl">

                        <!-- Top Connected Nodes Layer (Social Media, Marketplace, WhatsApp) -->
                        <div class="flex items-center justify-center gap-6 sm:gap-10 pb-4 relative z-20">

                            <!-- Node 1: Social Media -->
                            <div class="flex flex-col items-center text-center space-y-1.5">
                                <div
                                    class="p-2.5 rounded-[14px] bg-[#142247]/90 border border-sky-400/30 flex items-center gap-1.5 shadow-lg backdrop-blur-md">
                                    <span
                                        class="w-5 h-5 rounded-full bg-gradient-to-tr from-[#F58529] via-[#DD2A7B] to-[#8134AF] flex items-center justify-center text-[10px] text-white font-bold">ig</span>
                                    <span
                                        class="w-5 h-5 rounded-full bg-black flex items-center justify-center text-[10px] text-white font-bold">tt</span>
                                    <span
                                        class="w-5 h-5 rounded-full bg-[#2AABEE] flex items-center justify-center text-[10px] text-white font-bold">tg</span>
                                </div>
                                <span class="text-[11px] font-semibold text-slate-300">Social Media</span>
                            </div>

                            <!-- Connecting Line 1 -->
                            <div
                                class="h-0.5 w-8 sm:w-12 bg-gradient-to-r from-sky-400/40 to-purple-400/40 -mt-5 hidden sm:block">
                            </div>

                            <!-- Node 2: Marketplace -->
                            <div class="flex flex-col items-center text-center space-y-1.5 relative">
                                <div
                                    class="p-2.5 rounded-[14px] bg-[#2E1065]/90 border border-purple-400/40 flex items-center justify-center shadow-lg backdrop-blur-md">
                                    <i data-lucide="shopping-bag" class="w-5 h-5 text-purple-300"></i>
                                </div>
                                <span class="text-[11px] font-semibold text-slate-300">Marketplace</span>
                                <!-- Vertical Wire into Laptop -->
                                <div
                                    class="absolute top-11 left-1/2 -translate-x-1/2 w-0.5 h-7 bg-purple-400/40 hidden sm:block">
                                </div>
                            </div>

                            <!-- Connecting Line 2 -->
                            <div
                                class="h-0.5 w-8 sm:w-12 bg-gradient-to-r from-purple-400/40 to-emerald-400/40 -mt-5 hidden sm:block">
                            </div>

                            <!-- Node 3: WhatsApp -->
                            <div class="flex flex-col items-center text-center space-y-1.5">
                                <div
                                    class="p-2.5 rounded-[14px] bg-[#064E3B]/90 border border-emerald-400/40 flex items-center justify-center shadow-lg backdrop-blur-md">
                                    <i data-lucide="message-circle" class="w-5 h-5 text-emerald-300"></i>
                                </div>
                                <span class="text-[11px] font-semibold text-slate-300">WhatsApp</span>
                            </div>

                        </div>

                        <!-- Main Device Image Container -->
                        <div class="relative z-10 w-full overflow-hidden rounded-[24px]">
                            <img src="{{ asset('assets/image/cooca_devices_mockup.jpg') }}"
                                alt="COOCA Devices & Ecosystem Mockup"
                                class="w-full h-auto object-contain transition-transform duration-500 hover:scale-[1.02]">
                        </div>

                        <!-- Floating Node Left: Inventory -->
                        <div
                            class="absolute top-1/2 -left-2 sm:-left-6 -translate-y-1/2 z-20 hidden sm:flex flex-col items-center text-center space-y-1">
                            <div
                                class="p-2.5 rounded-[14px] bg-[#0C4A6E]/90 border border-sky-400/50 flex items-center justify-center shadow-xl backdrop-blur-md">
                                <i data-lucide="package" class="w-5 h-5 text-sky-300"></i>
                            </div>
                            <span class="text-[11px] font-semibold text-slate-300">Inventory</span>
                        </div>

                        <!-- Floating Vertical Pill Stack Right -->
                        <div class="absolute top-8 -right-2 sm:-right-6 z-20 flex flex-col gap-2">
                            <div
                                class="px-3.5 py-1.5 rounded-full bg-[#0E1E45]/85 border border-sky-400/30 backdrop-blur-md flex items-center gap-2 text-xs font-semibold text-sky-200 shadow-lg">
                                <i data-lucide="layers" class="w-3.5 h-3.5 text-sky-400"></i>
                                <span>ERP</span>
                            </div>
                            <div
                                class="px-3.5 py-1.5 rounded-full bg-[#0E1E45]/85 border border-sky-400/30 backdrop-blur-md flex items-center gap-2 text-xs font-semibold text-sky-200 shadow-lg">
                                <i data-lucide="shopping-cart" class="w-3.5 h-3.5 text-sky-400"></i>
                                <span>POS</span>
                            </div>
                            <div
                                class="px-3.5 py-1.5 rounded-full bg-[#0E1E45]/85 border border-sky-400/30 backdrop-blur-md flex items-center gap-2 text-xs font-semibold text-sky-200 shadow-lg">
                                <i data-lucide="box" class="w-3.5 h-3.5 text-sky-400"></i>
                                <span>Inventory</span>
                            </div>
                            <div
                                class="px-3.5 py-1.5 rounded-full bg-[#0E1E45]/85 border border-sky-400/30 backdrop-blur-md flex items-center gap-2 text-xs font-semibold text-sky-200 shadow-lg">
                                <i data-lucide="wallet" class="w-3.5 h-3.5 text-sky-400"></i>
                                <span>Finance</span>
                            </div>
                            <div
                                class="px-3.5 py-1.5 rounded-full bg-[#0E1E45]/85 border border-sky-400/30 backdrop-blur-md flex items-center gap-2 text-xs font-semibold text-sky-200 shadow-lg">
                                <i data-lucide="users" class="w-3.5 h-3.5 text-sky-400"></i>
                                <span>CRM</span>
                            </div>
                            <div
                                class="px-3.5 py-1.5 rounded-full bg-[#00C4D8]/20 border border-[#00C4D8]/40 backdrop-blur-md flex items-center gap-1.5 text-xs font-bold text-[#00C4D8] shadow-lg">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                <span>More</span>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 2. OFFICIAL 4-TIER PRICING CARDS SECTION (Light Clean Surface) ══════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 sm:py-24 bg-[#FAFAFC] dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors">
            <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

                <!-- Section Header & Billing Cycle Segmented Control -->
                <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">

                    <div class="space-y-3 max-w-2xl">
                        <div
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#00C4D8]/15 text-[#0096B4] dark:text-[#00C4D8] text-xs font-bold uppercase tracking-wider">
                            <span>Paket Harga</span>
                        </div>
                        <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-[1.2] text-balance break-words">
                            Pilih Paket yang Tepat untuk Anda
                        </h2>
                        <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400 leading-relaxed font-normal text-pretty">
                            Mulai dari versi gratis dengan fitur dasar, hingga paket premium dengan fitur lengkap, AI, dan
                            dukungan prioritas. Semua paket dirancang untuk membantu bisnis Anda tumbuh lebih cepat.
                        </p>
                    </div>

                    <!-- Cycle Toggle Segmented Control with Savings Hook -->
                    <div class="flex items-center gap-4 self-start lg:self-end">
                        <div
                            class="inline-flex p-1 rounded-full bg-slate-200 dark:bg-slate-800 border border-slate-300/80 dark:border-slate-700">
                            <button type="button" @click="pricingCycle = 'monthly'"
                                :class="pricingCycle === 'monthly' ? 'bg-[#00B4D8] text-white shadow-sm font-bold' :
                                    'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                                class="px-5 py-2 rounded-full text-xs transition-all cursor-pointer">
                                Bulanan
                            </button>
                            <button type="button" @click="pricingCycle = 'annual'"
                                :class="pricingCycle === 'annual' ? 'bg-[#00B4D8] text-white shadow-sm font-bold' :
                                    'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                                class="px-5 py-2 rounded-full text-xs transition-all cursor-pointer">
                                Tahunan
                            </button>
                        </div>

                        <!-- Savings Callout with Arrow -->
                        <div class="hidden sm:flex items-center gap-1.5 text-xs font-semibold text-[#00B4D8]">
                            <svg class="w-4 h-4 text-[#00B4D8] -rotate-45 shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                            <span class="text-pretty">Hemat hingga 20% untuk pembayaran tahunan</span>
                        </div>
                    </div>

                </div>

                <!-- 4 Bento Pricing Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch">

                    <!-- 1. FREE PLAN CARD -->
                    <div
                        class="bg-white dark:bg-[#111827] rounded-[24px] border border-slate-200 dark:border-slate-800 p-6 sm:p-7 shadow-xs flex flex-col justify-between space-y-6 hover:shadow-md transition-all">
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-xl font-bold text-slate-900 dark:text-white">Free</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 min-h-[32px] leading-relaxed text-pretty">
                                    Cocok untuk mencoba dan memulai digitalisasi bisnis Anda.
                                </p>
                            </div>

                            <!-- Price -->
                            <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">Rp</span>
                                    <span
                                        class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tabular-nums">0</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">/bulan</span>
                                </div>
                            </div>

                            <!-- Features Checklist -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-2 pt-2">
                                <div class="p-2.5 rounded-[12px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2.5">
                                    <div class="w-5 h-5 rounded-[6px] bg-[#00B4D8]/10 text-[#00B4D8] flex items-center justify-center shrink-0">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-xs text-slate-700 dark:text-slate-300 font-medium leading-snug">1 Bisnis / User / Outlet</span>
                                </div>
                                <div class="p-2.5 rounded-[12px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2.5">
                                    <div class="w-5 h-5 rounded-[6px] bg-[#00B4D8]/10 text-[#00B4D8] flex items-center justify-center shrink-0">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-xs text-slate-700 dark:text-slate-300 font-medium leading-snug">50 Produk &amp; 20 Material</span>
                                </div>
                                <div class="p-2.5 rounded-[12px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2.5">
                                    <div class="w-5 h-5 rounded-[6px] bg-[#00B4D8]/10 text-[#00B4D8] flex items-center justify-center shrink-0">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-xs text-slate-700 dark:text-slate-300 font-medium leading-snug">30 Customer &amp; 20 Supplier</span>
                                </div>
                                <div class="p-2.5 rounded-[12px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2.5">
                                    <div class="w-5 h-5 rounded-[6px] bg-[#00B4D8]/10 text-[#00B4D8] flex items-center justify-center shrink-0">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-xs text-slate-700 dark:text-slate-300 font-medium leading-snug">10 PO &amp; 10 Invoice / bln</span>
                                </div>
                                <div class="p-2.5 rounded-[12px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2.5">
                                    <div class="w-5 h-5 rounded-[6px] bg-[#00B4D8]/10 text-[#00B4D8] flex items-center justify-center shrink-0">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-xs text-slate-700 dark:text-slate-300 font-medium leading-snug">100 Transaksi POS / bln</span>
                                </div>
                                <div class="p-2.5 rounded-[12px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2.5">
                                    <div class="w-5 h-5 rounded-[6px] bg-[#00B4D8]/10 text-[#00B4D8] flex items-center justify-center shrink-0">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-xs text-slate-700 dark:text-slate-300 font-medium leading-snug">3GB Cloud Storage</span>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('register') }}"
                            class="w-full py-3 rounded-[12px] border border-[#00B4D8] text-[#00B4D8] hover:bg-[#00B4D8]/10 font-bold text-xs text-center transition active:scale-[0.98] flex items-center justify-center min-h-[44px]">
                            Mulai Gratis
                        </a>
                    </div>

                    <!-- 2. STANDARD PLAN CARD (HIGHLIGHTED / POPULER) -->
                    <div
                        class="bg-white dark:bg-[#111827] rounded-[24px] border-2 border-[#00B4D8] p-6 sm:p-7 shadow-lg flex flex-col justify-between space-y-6 relative hover:shadow-xl transition-all">

                        <div class="space-y-4">
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-1.5 shrink-0">
                                    <span>Standard</span>
                                    <i data-lucide="zap" class="w-4 h-4 text-amber-500 fill-amber-500 shrink-0"></i>
                                </h3>
                                <span
                                    class="px-2.5 py-0.5 rounded-full bg-[#00B4D8] text-white text-[10px] font-bold uppercase tracking-wider shrink-0 text-center">
                                    Paling Populer
                                </span>
                            </div>

                            <p class="text-xs text-slate-500 dark:text-slate-400 min-h-[32px] leading-relaxed text-pretty">
                                Untuk bisnis yang mulai berkembang dan membutuhkan lebih banyak fitur dasar.
                            </p>

                            <!-- Price -->
                            <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">Rp</span>
                                    <span
                                        class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tabular-nums"
                                        x-text="pricingCycle === 'annual' ? '39.000' : '49.000'">49.000</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">/bulan</span>
                                </div>
                            </div>

                            <div class="text-[11px] font-bold text-slate-900 dark:text-white pt-1">
                                Semua fitur Free, ditambah:
                            </div>

                            <!-- Features Checklist -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-2 pt-1">
                                <div class="p-2.5 rounded-[12px] bg-slate-50 dark:bg-white/[0.03] border border-[#00B4D8]/20 flex items-center gap-2.5">
                                    <div class="w-5 h-5 rounded-[6px] bg-[#00B4D8]/15 text-[#00B4D8] flex items-center justify-center shrink-0">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-xs text-slate-800 dark:text-slate-200 font-medium leading-snug">Multi User &amp; Hak Akses</span>
                                </div>
                                <div class="p-2.5 rounded-[12px] bg-slate-50 dark:bg-white/[0.03] border border-[#00B4D8]/20 flex items-center gap-2.5">
                                    <div class="w-5 h-5 rounded-[6px] bg-[#00B4D8]/15 text-[#00B4D8] flex items-center justify-center shrink-0">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-xs text-slate-800 dark:text-slate-200 font-medium leading-snug">Multi Outlet &amp; Gudang</span>
                                </div>
                                <div class="p-2.5 rounded-[12px] bg-slate-50 dark:bg-white/[0.03] border border-[#00B4D8]/20 flex items-center gap-2.5">
                                    <div class="w-5 h-5 rounded-[6px] bg-[#00B4D8]/15 text-[#00B4D8] flex items-center justify-center shrink-0">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-xs text-slate-800 dark:text-slate-200 font-medium leading-snug">B2B / B2C Sales</span>
                                </div>
                                <div class="p-2.5 rounded-[12px] bg-slate-50 dark:bg-white/[0.03] border border-[#00B4D8]/20 flex items-center gap-2.5">
                                    <div class="w-5 h-5 rounded-[6px] bg-[#00B4D8]/15 text-[#00B4D8] flex items-center justify-center shrink-0">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-xs text-slate-800 dark:text-slate-200 font-medium leading-snug">Integrasi Marketplace &amp; WA</span>
                                </div>
                                <div class="p-2.5 rounded-[12px] bg-slate-50 dark:bg-white/[0.03] border border-[#00B4D8]/20 flex items-center gap-2.5">
                                    <div class="w-5 h-5 rounded-[6px] bg-[#00B4D8]/15 text-[#00B4D8] flex items-center justify-center shrink-0">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-xs text-slate-800 dark:text-slate-200 font-medium leading-snug">Laporan &amp; Dasbor Lengkap</span>
                                </div>
                                <div class="p-2.5 rounded-[12px] bg-slate-50 dark:bg-white/[0.03] border border-[#00B4D8]/20 flex items-center gap-2.5">
                                    <div class="w-5 h-5 rounded-[6px] bg-[#00B4D8]/15 text-[#00B4D8] flex items-center justify-center shrink-0">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-xs text-slate-800 dark:text-slate-200 font-medium leading-snug">Email &amp; Chat Support</span>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('register') }}"
                            class="w-full py-3 rounded-[12px] bg-[#00B4D8] hover:bg-[#0096B4] text-white font-bold text-xs text-center transition shadow-md active:scale-[0.98] flex items-center justify-center min-h-[44px]">
                            Pilih Standard
                        </a>
                    </div>

                    <!-- 3. PREMIUM PLAN CARD -->
                    <div
                        class="bg-white dark:bg-[#111827] rounded-[24px] border border-slate-200 dark:border-slate-800 p-6 sm:p-7 shadow-xs flex flex-col justify-between space-y-6 hover:shadow-md transition-all">
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-xl font-bold text-slate-900 dark:text-white">Premium</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 min-h-[32px] leading-relaxed text-pretty">
                                    Untuk bisnis yang ingin lebih produktif dengan fitur omnichannel dan automation.
                                </p>
                            </div>

                            <!-- Price -->
                            <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">Rp</span>
                                    <span
                                        class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tabular-nums"
                                        x-text="pricingCycle === 'annual' ? '79.000' : '99.000'">99.000</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">/bulan</span>
                                </div>
                            </div>

                            <div class="text-[11px] font-bold text-slate-900 dark:text-white pt-1">
                                Semua fitur Standard, ditambah:
                            </div>

                            <!-- Features Checklist -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-2 pt-1">
                                <div class="p-2.5 rounded-[12px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2.5">
                                    <div class="w-5 h-5 rounded-[6px] bg-[#00B4D8]/10 text-[#00B4D8] flex items-center justify-center shrink-0">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-xs text-slate-700 dark:text-slate-300 font-medium leading-snug">Content Automation</span>
                                </div>
                                <div class="p-2.5 rounded-[12px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2.5">
                                    <div class="w-5 h-5 rounded-[6px] bg-[#00B4D8]/10 text-[#00B4D8] flex items-center justify-center shrink-0">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-xs text-slate-700 dark:text-slate-300 font-medium leading-snug">Social Media Integration</span>
                                </div>
                                <div class="p-2.5 rounded-[12px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2.5">
                                    <div class="w-5 h-5 rounded-[6px] bg-[#00B4D8]/10 text-[#00B4D8] flex items-center justify-center shrink-0">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-xs text-slate-700 dark:text-slate-300 font-medium leading-snug">Advanced Analytics</span>
                                </div>
                                <div class="p-2.5 rounded-[12px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2.5">
                                    <div class="w-5 h-5 rounded-[6px] bg-[#00B4D8]/10 text-[#00B4D8] flex items-center justify-center shrink-0">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-xs text-slate-700 dark:text-slate-300 font-medium leading-snug">Shopee &amp; TikTok Marketplace</span>
                                </div>
                                <div class="p-2.5 rounded-[12px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2.5">
                                    <div class="w-5 h-5 rounded-[6px] bg-[#00B4D8]/10 text-[#00B4D8] flex items-center justify-center shrink-0">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-xs text-slate-700 dark:text-slate-300 font-medium leading-snug">API &amp; Webhook Integration</span>
                                </div>
                                <div class="p-2.5 rounded-[12px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2.5">
                                    <div class="w-5 h-5 rounded-[6px] bg-[#00B4D8]/10 text-[#00B4D8] flex items-center justify-center shrink-0">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-xs text-slate-700 dark:text-slate-300 font-medium leading-snug">Priority Support</span>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('register') }}"
                            class="w-full py-3 rounded-[12px] border border-[#00B4D8] text-[#00B4D8] hover:bg-[#00B4D8]/10 font-bold text-xs text-center transition active:scale-[0.98] flex items-center justify-center min-h-[44px]">
                            Pilih Premium
                        </a>
                    </div>

                    <!-- 4. PRESTIGE PLAN CARD -->
                    <div
                        class="bg-white dark:bg-[#111827] rounded-[24px] border border-slate-200 dark:border-slate-800 p-6 sm:p-7 shadow-xs flex flex-col justify-between space-y-6 hover:shadow-md transition-all">
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <i data-lucide="crown" class="w-5 h-5 text-amber-500 shrink-0"></i>
                                    <span>Prestige</span>
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 min-h-[32px] leading-relaxed text-pretty">
                                    Untuk bisnis yang membutuhkan solusi lengkap dengan AI dan dukungan prioritas.
                                </p>
                            </div>

                            <!-- Price -->
                            <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">Rp</span>
                                    <span
                                        class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tabular-nums"
                                        x-text="pricingCycle === 'annual' ? '159.000' : '199.000'">199.000</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">/bulan</span>
                                </div>
                            </div>

                            <div class="text-[11px] font-bold text-slate-900 dark:text-white pt-1">
                                Semua fitur Premium, ditambah:
                            </div>

                            <!-- Features Checklist -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-2 pt-1">
                                <div class="p-2.5 rounded-[12px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2.5">
                                    <div class="w-5 h-5 rounded-[6px] bg-[#00B4D8]/10 text-[#00B4D8] flex items-center justify-center shrink-0">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-xs text-slate-700 dark:text-slate-300 font-medium leading-snug">AI Assistant (Chat &amp; Analytics)</span>
                                </div>
                                <div class="p-2.5 rounded-[12px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2.5">
                                    <div class="w-5 h-5 rounded-[6px] bg-[#00B4D8]/10 text-[#00B4D8] flex items-center justify-center shrink-0">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-xs text-slate-700 dark:text-slate-300 font-medium leading-snug">AI Content Generation</span>
                                </div>
                                <div class="p-2.5 rounded-[12px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2.5">
                                    <div class="w-5 h-5 rounded-[6px] bg-[#00B4D8]/10 text-[#00B4D8] flex items-center justify-center shrink-0">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-xs text-slate-700 dark:text-slate-300 font-medium leading-snug">Advanced Manufacturing &amp; BOM</span>
                                </div>
                                <div class="p-2.5 rounded-[12px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2.5">
                                    <div class="w-5 h-5 rounded-[6px] bg-[#00B4D8]/10 text-[#00B4D8] flex items-center justify-center shrink-0">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-xs text-slate-700 dark:text-slate-300 font-medium leading-snug">Multi-Branch &amp; Multi-Company</span>
                                </div>
                                <div class="p-2.5 rounded-[12px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2.5">
                                    <div class="w-5 h-5 rounded-[6px] bg-[#00B4D8]/10 text-[#00B4D8] flex items-center justify-center shrink-0">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-xs text-slate-700 dark:text-slate-300 font-medium leading-snug">Dedicated Account Manager</span>
                                </div>
                                <div class="p-2.5 rounded-[12px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2.5">
                                    <div class="w-5 h-5 rounded-[6px] bg-[#00B4D8]/10 text-[#00B4D8] flex items-center justify-center shrink-0">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-xs text-slate-700 dark:text-slate-300 font-medium leading-snug">Priority Support 24/7</span>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('register') }}"
                            class="w-full py-3 rounded-[12px] border border-[#00B4D8] text-[#00B4D8] hover:bg-[#00B4D8]/10 font-bold text-xs text-center transition active:scale-[0.98] flex items-center justify-center min-h-[44px]">
                            Pilih Prestige
                        </a>
                    </div>

                </div>

            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 3. ADD-ON SECTION (Tingkatkan Potensi Bisnis Anda) ═══════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section
            class="py-12 sm:py-16 bg-white dark:bg-[#0B0F19] text-slate-900 dark:text-white border-t border-slate-200/60 dark:border-white/5 transition-colors">
            <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">

                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-stretch">

                    <!-- Left Featured Card: Call to Action -->
                    <div
                        class="md:col-span-5 p-7 sm:p-8 rounded-[24px] bg-[#EAF7FA] dark:bg-[#0C243B]/60 border border-[#00B4D8]/20 flex flex-col justify-between space-y-6">
                        <div class="space-y-3">
                            <span
                                class="px-2.5 py-0.5 rounded-full bg-[#00B4D8]/15 text-[#0096B4] dark:text-[#00C4D8] text-[11px] font-bold uppercase tracking-wider">
                                Add-On
                            </span>
                            <h3
                                class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug text-balance break-words">
                                Tingkatkan Potensi Bisnis Anda Dengan Add-On
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed font-normal text-pretty">
                                Sesuaikan kebutuhan bisnis Anda dengan add-on yang fleksibel. Aktifkan kapan saja sesuai
                                kebutuhan.
                            </p>
                        </div>

                        <a href="{{ route('public.bos.overview') }}"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-[#00B4D8] hover:bg-[#0096B4] text-white font-bold text-xs transition active:scale-[0.98] w-fit shadow-xs shrink-0">
                            <span>Lihat Detail Fitur</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5 shrink-0"></i>
                        </a>
                    </div>

                    <!-- Middle Card: Storage Cloud Add-On -->
                    <div
                        class="md:col-span-3.5 p-6 sm:p-7 rounded-[24px] bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 flex flex-col justify-between space-y-5 shadow-xs hover:shadow-md transition-all">
                        <div class="space-y-4">
                            <div
                                class="w-12 h-12 rounded-[16px] bg-blue-500 text-white flex items-center justify-center shadow-md shrink-0">
                                <i data-lucide="cloud" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-slate-900 dark:text-white leading-snug">Storage</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed text-pretty">
                                    Tambahan penyimpanan untuk file, media, dan data bisnis Anda.
                                </p>
                            </div>
                            <div class="pt-2 text-sm font-extrabold text-slate-900 dark:text-white">
                                Rp 10.000 <span class="text-xs font-normal text-slate-500 dark:text-slate-400">/ 1GB /
                                    bulan</span>
                            </div>
                        </div>

                        <a href="{{ route('login') }}"
                            class="w-full py-2.5 rounded-full bg-[#00B4D8] hover:bg-[#0096B4] text-white font-bold text-xs text-center transition active:scale-95 flex items-center justify-center shadow-xs min-h-[38px]">
                            Tambah
                        </a>
                    </div>

                    <!-- Right Card: AI Token Add-On -->
                    <div
                        class="md:col-span-3.5 p-6 sm:p-7 rounded-[24px] bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 flex flex-col justify-between space-y-5 shadow-xs hover:shadow-md transition-all">
                        <div class="space-y-4">
                            <div
                                class="w-12 h-12 rounded-[16px] bg-purple-600 text-white flex items-center justify-center shadow-md shrink-0">
                                <i data-lucide="cpu" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-slate-900 dark:text-white leading-snug">AI Token</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed text-pretty">
                                    Gunakan AI Assistant dan fitur AI lainnya untuk meningkatkan produktivitas.
                                </p>
                            </div>
                            <div class="pt-2 text-sm font-extrabold text-slate-900 dark:text-white">
                                Rp 20.000 <span class="text-xs font-normal text-slate-500 dark:text-slate-400">/ 1 juta
                                    token</span>
                            </div>
                        </div>

                        <a href="{{ route('login') }}"
                            class="w-full py-2.5 rounded-full bg-[#00B4D8] hover:bg-[#0096B4] text-white font-bold text-xs text-center transition active:scale-95 flex items-center justify-center shadow-xs min-h-[38px]">
                            Tambah
                        </a>
                    </div>

                </div>

            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 4. PROMO SPESIAL BANNER (3D Gift Box) ═══════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-6 sm:py-8 bg-white dark:bg-[#070A14] transition-colors">
            <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">

                <div
                    class="rounded-[24px] bg-gradient-to-r from-[#0C1E4A] via-[#0E3572] to-[#0A2558] p-6 sm:p-8 text-white flex flex-col md:flex-row items-center justify-between gap-6 shadow-xl relative overflow-hidden">

                    <!-- Background Subtle Star Glow -->
                    <div
                        class="absolute right-1/4 top-1/2 -translate-y-1/2 w-48 h-48 bg-sky-400/10 rounded-full blur-2xl pointer-events-none">
                    </div>

                    <div class="flex items-center gap-5 sm:gap-7 z-10 w-full md:w-auto">
                        <!-- 3D Gift Box Visual -->
                        <div
                            class="w-16 h-16 sm:w-20 sm:h-20 shrink-0 rounded-[18px] overflow-hidden bg-transparent flex items-center justify-center">
                            <img src="{{ asset('assets/image/promo_gift_box.jpg') }}" alt="Promo Spesial 1 Bulan Gratis"
                                class="w-full h-full object-contain">
                        </div>

                        <div class="space-y-1 min-w-0 flex-1">
                            <div class="text-[#00C4D8] text-xs font-bold uppercase tracking-wider">
                                Promo Spesial
                            </div>
                            <h3 class="text-lg sm:text-2xl font-extrabold text-white tracking-tight leading-snug text-balance break-words">
                                1 Bulan Gratis untuk Setiap Pendaftaran Baru
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-300 max-w-xl font-normal leading-relaxed text-pretty">
                                Dapatkan voucher 1 bulan gratis (patungan) untuk semua paket. Voucher dapat diaktifkan kapan
                                saja.
                            </p>
                        </div>
                    </div>

                    <div class="z-10 shrink-0 w-full md:w-auto">
                        <a href="{{ route('register') }}"
                            class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full bg-[#00B4D8] hover:bg-[#0096B4] text-white font-bold text-xs sm:text-sm transition shadow-md active:scale-95 w-full md:w-auto min-h-[44px]">
                            <span>Daftar Sekarang</span>
                            <i data-lucide="arrow-right" class="w-4 h-4 shrink-0"></i>
                        </a>
                    </div>

                </div>

            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 5. FAQ ACCORDION SECTION (Pertanyaan yang Sering Diajukan) ═══════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 sm:py-20 bg-white dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors"
            id="faq">
            <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

                <!-- Section Header -->
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                    <div class="space-y-2 max-w-2xl">
                        <span
                            class="px-2.5 py-0.5 rounded-full bg-[#00C4D8]/15 text-[#0096B4] dark:text-[#00C4D8] text-xs font-bold uppercase tracking-wider">
                            FAQ
                        </span>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-[1.2] text-balance break-words">
                            Pertanyaan yang Sering Diajukan
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-normal leading-relaxed text-pretty">
                            Temukan jawaban untuk pertanyaan yang paling sering ditanyakan tentang paket harga dan fitur
                            COOCA.
                        </p>
                    </div>

                    <a href="{{ route('public.resources.faq') }}"
                        class="text-xs font-bold text-[#00B4D8] hover:text-[#0096B4] flex items-center gap-1 transition-colors self-start sm:self-auto shrink-0">
                        <span>Lihat Semua FAQ</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5 shrink-0"></i>
                    </a>
                </div>

                <!-- 2-Column FAQ Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <!-- Left Column -->
                    <div class="space-y-3">

                        <!-- FAQ 1 -->
                        <div
                            class="rounded-[16px] bg-slate-50 dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800 transition-colors">
                            <button type="button" @click="toggleFaq(1)"
                                class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-3 text-xs sm:text-sm font-bold text-slate-900 dark:text-white cursor-pointer">
                                <span class="min-w-0 flex-1 leading-snug">Apakah ada biaya setup atau instalasi?</span>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform shrink-0"
                                    :class="openFaq === 1 ? 'rotate-180 text-[#00B4D8]' : ''"></i>
                            </button>
                            <div x-show="openFaq === 1" x-collapse x-cloak
                                class="px-4 pb-4 sm:px-5 sm:pb-5 pt-0 text-xs text-slate-600 dark:text-slate-400 leading-relaxed text-pretty border-t border-slate-200/40 dark:border-slate-800/40">
                                Tidak ada. Semua paket COOCA bebas biaya instalasi atau setup awal. Anda dapat langsung
                                mendaftar dan menggunakan sistem dalam hitungan menit tanpa perlu perangkat keras khusus.
                            </div>
                        </div>

                        <!-- FAQ 2 -->
                        <div
                            class="rounded-[16px] bg-slate-50 dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800 transition-colors">
                            <button type="button" @click="toggleFaq(2)"
                                class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-3 text-xs sm:text-sm font-bold text-slate-900 dark:text-white cursor-pointer">
                                <span class="min-w-0 flex-1 leading-snug">Bisa upgrade atau downgrade paket kapan saja?</span>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform shrink-0"
                                    :class="openFaq === 2 ? 'rotate-180 text-[#00B4D8]' : ''"></i>
                            </button>
                            <div x-show="openFaq === 2" x-collapse x-cloak
                                class="px-4 pb-4 sm:px-5 sm:pb-5 pt-0 text-xs text-slate-600 dark:text-slate-400 leading-relaxed text-pretty border-t border-slate-200/40 dark:border-slate-800/40">
                                Ya, tentu saja. Anda dapat berpindah antar paket kapan saja secara fleksibel melalui dasbor
                                billing. Sisa masa aktif Anda akan dikonversi secara prorata tanpa hangus.
                            </div>
                        </div>

                        <!-- FAQ 3 -->
                        <div
                            class="rounded-[16px] bg-slate-50 dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800 transition-colors">
                            <button type="button" @click="toggleFaq(3)"
                                class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-3 text-xs sm:text-sm font-bold text-slate-900 dark:text-white cursor-pointer">
                                <span class="min-w-0 flex-1 leading-snug">Bagaimana cara aktivasi voucher 1 bulan gratis?</span>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform shrink-0"
                                    :class="openFaq === 3 ? 'rotate-180 text-[#00B4D8]' : ''"></i>
                            </button>
                            <div x-show="openFaq === 3" x-collapse x-cloak
                                class="px-4 pb-4 sm:px-5 sm:pb-5 pt-0 text-xs text-slate-600 dark:text-slate-400 leading-relaxed text-pretty border-t border-slate-200/40 dark:border-slate-800/40">
                                Setiap pendaftaran akun baru akan otomatis menerima voucher gratis 1 bulan di akun Anda.
                                Voucher dapat diaktifkan kapan pun Anda siap memulai paket berbayar tanpa batas kedaluwarsa.
                            </div>
                        </div>

                    </div>

                    <!-- Right Column -->
                    <div class="space-y-3">

                        <!-- FAQ 4 -->
                        <div
                            class="rounded-[16px] bg-slate-50 dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800 transition-colors">
                            <button type="button" @click="toggleFaq(4)"
                                class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-3 text-xs sm:text-sm font-bold text-slate-900 dark:text-white cursor-pointer">
                                <span class="min-w-0 flex-1 leading-snug">Apakah data saya aman di COOCA?</span>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform shrink-0"
                                    :class="openFaq === 4 ? 'rotate-180 text-[#00B4D8]' : ''"></i>
                            </button>
                            <div x-show="openFaq === 4" x-collapse x-cloak
                                class="px-4 pb-4 sm:px-5 sm:pb-5 pt-0 text-xs text-slate-600 dark:text-slate-400 leading-relaxed text-pretty border-t border-slate-200/40 dark:border-slate-800/40">
                                Keamanan data Anda adalah prioritas utama kami. Seluruh database diisolasi per tenant dengan
                                enkripsi AES-256, pencadangan harian otomatis, dan kepatuhan penuh terhadap regulasi UU PDP
                                No. 27/2022.
                            </div>
                        </div>

                        <!-- FAQ 5 -->
                        <div
                            class="rounded-[16px] bg-slate-50 dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800 transition-colors">
                            <button type="button" @click="toggleFaq(5)"
                                class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-3 text-xs sm:text-sm font-bold text-slate-900 dark:text-white cursor-pointer">
                                <span class="min-w-0 flex-1 leading-snug">Apakah harga sudah termasuk semua fitur?</span>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform shrink-0"
                                    :class="openFaq === 5 ? 'rotate-180 text-[#00B4D8]' : ''"></i>
                            </button>
                            <div x-show="openFaq === 5" x-collapse x-cloak
                                class="px-4 pb-4 sm:px-5 sm:pb-5 pt-0 text-xs text-slate-600 dark:text-slate-400 leading-relaxed text-pretty border-t border-slate-200/40 dark:border-slate-800/40">
                                Ya, harga yang tertera sudah mencakup seluruh fitur yang tertera pada masing-masing paket
                                tanpa biaya tersembunyi. Untuk kebutuhan ekstra, Anda dapat menambah kuota Storage atau AI
                                Token kapan saja.
                            </div>
                        </div>

                        <!-- FAQ 6 -->
                        <div
                            class="rounded-[16px] bg-slate-50 dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800 transition-colors">
                            <button type="button" @click="toggleFaq(6)"
                                class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-3 text-xs sm:text-sm font-bold text-slate-900 dark:text-white cursor-pointer">
                                <span class="min-w-0 flex-1 leading-snug">Bagaimana jika saya membutuhkan fitur khusus?</span>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform shrink-0"
                                    :class="openFaq === 6 ? 'rotate-180 text-[#00B4D8]' : ''"></i>
                            </button>
                            <div x-show="openFaq === 6" x-collapse x-cloak
                                class="px-4 pb-4 sm:px-5 sm:pb-5 pt-0 text-xs text-slate-600 dark:text-slate-400 leading-relaxed text-pretty border-t border-slate-200/40 dark:border-slate-800/40">
                                Untuk kebutuhan kustom seperti integrasi API privat, arsitektur multi-perusahaan, atau modul
                                industri bertingkat, tim konsultan kami siap membantu melalui opsi konsultasi Prestige
                                Enterprise.
                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 6. BOTTOM CTA SECTION (Gradient Card) ════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-12 sm:py-16 bg-white dark:bg-[#070A14] transition-colors pb-24">
            <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">

                <div
                    class="rounded-[28px] bg-gradient-to-r from-[#0B1536] via-[#0E2866] to-[#14449E] p-8 sm:p-12 text-white flex flex-col lg:flex-row items-start lg:items-center justify-between gap-8 shadow-2xl relative overflow-hidden">

                    <div class="space-y-2 max-w-2xl z-10">
                        <span
                            class="px-3 py-1 rounded-full bg-[#007AFF]/25 text-[#00C4D8] text-xs font-bold uppercase tracking-wider">
                            Mulai Sekarang
                        </span>
                        <h2 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight leading-[1.2] text-balance break-words">
                            Siap Mengelola Bisnis Anda dengan COOCA?
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed font-normal text-pretty">
                            Bergabunglah dengan ribuan bisnis lainnya yang sudah mempercayai COOCA sebagai Business
                            Operating System &amp; Omnichannel ERP.
                        </p>
                    </div>

                    <div
                        class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto z-10 shrink-0">
                        <a href="{{ route('register') }}"
                            class="px-7 py-3.5 rounded-full bg-[#00B4D8] hover:bg-[#0096B4] text-white font-bold text-xs sm:text-sm transition shadow-lg active:scale-95 flex items-center justify-center gap-2 min-h-[44px]">
                            <span>Coba COOCA Gratis</span>
                            <i data-lucide="arrow-right" class="w-4 h-4 shrink-0"></i>
                        </a>
                        <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text={{ urlencode('Halo Tim COOCA, saya ingin berkonsultasi mengenai paket yang cocok untuk bisnis saya.') }}"
                            target="_blank" rel="noopener"
                            class="px-7 py-3.5 rounded-full border border-white/30 hover:bg-white/10 text-white font-semibold text-xs sm:text-sm transition active:scale-95 flex items-center justify-center min-h-[44px]">
                            Hubungi Tim Kami
                        </a>
                    </div>

                </div>

            </div>
        </section>

    </div>
@endsection
