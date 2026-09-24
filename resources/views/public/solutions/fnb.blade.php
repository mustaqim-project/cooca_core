@extends('layouts.public_marketing')

@section('title', 'Software Restoran & POS Kafe Terintegrasi Resep & Dapur | COOCA')
@section('description',
    'Solusi sistem operasi bisnis kuliner, kafe, resto, dan cloud kitchen. Manajemen meja, Kitchen Order Ticket (KOT), potong stok resep otomatis (BOM), dan pantau HPP porsi real-time.')
@section('og_title', 'Software Restoran & POS Kafe Terintegrasi Resep & Dapur | COOCA')
@section('og_description',
    'Sistem operasi F&B terintegrasi: kasir meja cepat, tiket dapur KOT, pemotongan stok bahan baku otomatis, dan laporan HPP akurat.')
@section('canonical', route('public.solutions.fnb'))
@section('og_type', 'product')
@section('keywords',
    'software restoran, pos fnb indonesia, aplikasi kasir kafe, aplikasi kasir restoran, software manajemen resto, kitchen display system, food cost hpp, manajemen meja resto')

    @push('seo')
        <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "COOCA F&B Operating System",
      "applicationCategory": "BusinessApplication",
      "operatingSystem": "Web, Android, iOS, Windows, macOS",
      "description": "Sistem operasi bisnis kuliner, kafe, restoran, dan cloud kitchen untuk kontrol meja, dapur, bahan baku resep, dan HPP.",
      "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "IDR"
      }
    }
    </script>
    @endpush

@section('content')
    <div class="bg-white dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors duration-300">

        {{-- 1. HERO SECTION (Unified Bento Cockpit - No Breadcrumb) --}}
        <section
            class="relative bg-[#060B1E] text-white overflow-hidden border-b border-white/10 min-h-[calc(100svh-4rem)] lg:min-h-[calc(100svh-84px)] flex items-center">
            {{-- Dual Ambient Glowing Blurs --}}
            <div class="absolute top-1/4 -right-24 w-96 h-96 bg-[#007AFF]/20 rounded-full blur-[120px] pointer-events-none">
            </div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-[#00C4D8]/15 rounded-full blur-[140px] pointer-events-none">
            </div>

            <div
                class="max-w-[1300px] mx-auto px-3.5 sm:px-6 lg:px-8 relative z-10 w-full pt-6 pb-[calc(5rem+env(safe-area-inset-bottom,0px))] sm:pt-8 sm:pb-20 lg:py-14">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

                    {{-- Left Column: Narrative & CTA (6 Cols) --}}
                    <div class="lg:col-span-6 space-y-5 text-left">
                        {{-- Typographic Overline Kicker with Pulse Dot (Zero Pill Abuse) --}}
                        <div class="flex items-center gap-2.5">
                            <span class="inline-flex w-2 h-2 rounded-full bg-[#00C4D8] animate-pulse"></span>
                            <p class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#00C4D8]">
                                Sistem Operasi Bisnis Kuliner &amp; Kafe
                            </p>
                        </div>

                        {{-- Main Headline --}}
                        <h1
                            class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.85rem] xl:text-[3.25rem] font-extrabold text-white tracking-tight leading-[1.12] text-balance">
                            Dari Meja Tamu, Dapur, hingga Stok Resep <span
                                class="bg-gradient-to-r from-[#00C4D8] via-[#60A5FA] to-[#007AFF] bg-clip-text text-transparent">Terhubung Otomatis</span>
                        </h1>

                        {{-- Subtitle Paragraph --}}
                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal text-pretty max-w-2xl">
                            Kendalikan operasional kafe, kedai kopi, restoran, dan cloud kitchen Anda dalam satu sistem. Setiap porsi pesanan di kasir otomatis memotong takaran bahan baku di dapur, mencetak tiket masak, dan menghitung laba bersih tanpa kalkulasi manual.
                        </p>

                        {{-- Tangible Value Highlights Bento Tiles --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1 text-left w-full">
                            <div class="p-2.5 sm:p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5 hover:bg-white/[0.08] transition-colors">
                                <div class="w-6 h-6 rounded-lg bg-emerald-500/15 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5 border border-emerald-500/20">
                                    <i data-lucide="scale" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200 leading-snug min-w-0 flex-1">Potong stok bahan baku per gram &amp; ml</span>
                            </div>
                            <div class="p-2.5 sm:p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5 hover:bg-white/[0.08] transition-colors">
                                <div class="w-6 h-6 rounded-lg bg-sky-500/15 text-[#00C4D8] flex items-center justify-center shrink-0 mt-0.5 border border-sky-400/20">
                                    <i data-lucide="printer" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200 leading-snug min-w-0 flex-1">Cetak tiket KOT terpisah (Kitchen &amp; Bar)</span>
                            </div>
                            <div class="p-2.5 sm:p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5 hover:bg-white/[0.08] transition-colors">
                                <div class="w-6 h-6 rounded-lg bg-amber-500/15 text-amber-400 flex items-center justify-center shrink-0 mt-0.5 border border-amber-400/20">
                                    <i data-lucide="layout-grid" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200 leading-snug min-w-0 flex-1">Split bill &amp; manajemen visual meja</span>
                            </div>
                            <div class="p-2.5 sm:p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5 hover:bg-white/[0.08] transition-colors">
                                <div class="w-6 h-6 rounded-lg bg-purple-500/15 text-purple-400 flex items-center justify-center shrink-0 mt-0.5 border border-purple-400/20">
                                    <i data-lucide="pie-chart" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200 leading-snug min-w-0 flex-1">Pantau food cost &amp; HPP porsi akurat</span>
                            </div>
                        </div>

                        {{-- Action CTAs (Left-aligned) --}}
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                            <a href="{{ route('register') }}"
                                class="inline-flex justify-center items-center gap-2.5 px-6 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm shadow-lg shadow-[#007AFF]/25 hover:shadow-xl hover:shadow-[#007AFF]/35 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 min-h-[48px]">
                                <span>Mulai Coba F&amp;B POS</span>
                                <i data-lucide="arrow-right" class="w-4 h-4 shrink-0" aria-hidden="true"></i>
                            </a>
                            <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text=Halo%20saya%20tertarik%20dengan%20solusi%20FnB%20dan%20Restoran%20COOCA"
                                target="_blank" rel="noopener"
                                class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 border border-white/15 text-white backdrop-blur-sm text-sm font-semibold hover:-translate-y-0.5 active:translate-y-0 transition-all min-h-[48px]">
                                <i data-lucide="message-circle" class="w-4 h-4 text-emerald-400 shrink-0" aria-hidden="true"></i>
                                <span>Tanya Solusi Kuliner</span>
                            </a>
                        </div>

                        {{-- Reassurance Checkpoints --}}
                        <div class="pt-3 border-t border-white/10 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-slate-300">
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Multi-Meja &amp; Split Bill</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>BOM Resep Gram &amp; Ml</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Cetak KOT Dapur &amp; Bar</span>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Apple Bento Restaurant Deck Cockpit (6 Cols) --}}
                    <div class="lg:col-span-6 relative mt-4 lg:mt-0">
                        {{-- Spotlight glow behind window --}}
                        <div class="absolute -inset-1.5 bg-gradient-to-r from-[#007AFF]/30 to-[#00C4D8]/30 rounded-[32px] blur-xl opacity-75"></div>

                        <div
                            class="relative bg-[#0A122C]/90 border border-white/15 rounded-[18px] sm:rounded-[28px] p-3.5 sm:p-5 lg:p-6 shadow-2xl backdrop-blur-2xl text-white space-y-4">
                            {{-- Specular top highlight line --}}
                            <div class="absolute top-0 inset-x-8 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>

                            {{-- Header Table Status --}}
                            <div class="flex items-center justify-between gap-3 pb-3 border-b border-white/10">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center shrink-0 border border-amber-500/30">
                                        <i data-lucide="utensils" class="w-4 h-4"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-white text-xs sm:text-sm truncate">Meja #04 - Sedang Makan</div>
                                        <div class="text-[10px] text-slate-400 truncate">Kasir Sudirman • Dine In</div>
                                    </div>
                                </div>
                                <span
                                    class="text-[11px] font-semibold text-[#00C4D8] bg-[#00C4D8]/15 border border-[#00C4D8]/30 px-2.5 py-1 rounded-full shrink-0">
                                    4 Menu Aktif
                                </span>
                            </div>

                            {{-- Order Line Items & BOM Recipe Deduction Preview --}}
                            <div class="space-y-2.5 text-xs">
                                <div class="p-3 rounded-xl bg-[#060B1E]/80 border border-white/10 space-y-2">
                                    <div class="flex justify-between items-start gap-2 text-xs font-semibold text-white">
                                        <div class="min-w-0 flex-1">
                                            <p class="font-bold text-white truncate">2x Iced Caramel Macchiato</p>
                                            <p class="text-[11px] text-slate-300 font-normal truncate">Less ice, oatmilk substitution</p>
                                        </div>
                                        <span class="font-mono text-slate-200 shrink-0 font-bold">Rp 76.000</span>
                                    </div>
                                    <div
                                        class="pt-1.5 border-t border-dashed border-white/10 flex items-center justify-between gap-2 text-[11px] text-emerald-400 font-mono">
                                        <span class="flex items-center gap-1 min-w-0 flex-1 truncate">
                                            <i data-lucide="layers" class="w-3 h-3 shrink-0" aria-hidden="true"></i>
                                            <span class="truncate">BOM: Kopi 36g, Susu 300ml, Oatmilk</span>
                                        </span>
                                        <span class="shrink-0 text-[10px] font-bold">Stok Terpotong</span>
                                    </div>
                                </div>

                                <div class="p-3 rounded-xl bg-[#060B1E]/80 border border-white/10 space-y-2">
                                    <div class="flex justify-between items-start gap-2 text-xs font-semibold text-white">
                                        <div class="min-w-0 flex-1">
                                            <p class="font-bold text-white truncate">1x Beef Truffle Pasta</p>
                                            <p class="text-[11px] text-slate-300 font-normal truncate">Tiket Dapur #KOT-082</p>
                                        </div>
                                        <span class="font-mono text-slate-200 shrink-0 font-bold">Rp 68.000</span>
                                    </div>
                                    <div
                                        class="pt-1.5 border-t border-dashed border-white/10 flex items-center justify-between gap-2 text-[11px] text-amber-400 font-mono">
                                        <span class="flex items-center gap-1 min-w-0 flex-1 truncate">
                                            <i data-lucide="chef-hat" class="w-3 h-3 shrink-0" aria-hidden="true"></i>
                                            <span class="truncate">Status: Sedang Dimasak</span>
                                        </span>
                                        <span class="shrink-0 text-[10px] font-bold">Est. 8 Mnt</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Calculation Breakdown --}}
                            <div class="p-3.5 rounded-xl bg-[#060B1E]/90 border border-white/10 space-y-1.5 text-xs">
                                <div class="flex justify-between text-slate-300">
                                    <span>Subtotal Pesanan</span>
                                    <span class="font-mono text-slate-200">Rp 144.000</span>
                                </div>
                                <div class="flex justify-between text-slate-300">
                                    <span>Pajak Resto (PB1 10%)</span>
                                    <span class="font-mono text-slate-200">Rp 14.400</span>
                                </div>
                                <div class="flex justify-between text-white font-bold pt-2 border-t border-white/10 text-xs sm:text-sm">
                                    <span>Total Tagihan Meja</span>
                                    <span class="font-mono text-[#00C4D8] font-extrabold">Rp 158.400</span>
                                </div>
                            </div>

                            {{-- Operational Quick Actions --}}
                            <div class="grid grid-cols-2 gap-2.5 text-xs">
                                <div class="p-2.5 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center gap-2">
                                    <i data-lucide="printer" class="w-3.5 h-3.5 text-[#00C4D8]" aria-hidden="true"></i>
                                    <span class="font-medium text-slate-200 text-[11px]">KOT Dapur Otomatis</span>
                                </div>
                                <div class="p-2.5 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center gap-2">
                                    <i data-lucide="split" class="w-3.5 h-3.5 text-emerald-400" aria-hidden="true"></i>
                                    <span class="font-medium text-slate-200 text-[11px]">Split Bill Siap</span>
                                </div>
                            </div>

                            {{-- Floating Badges --}}
                            <div class="hidden sm:flex absolute -top-3.5 -right-3.5 items-center gap-2 px-3 py-1.5 rounded-xl bg-[#060B1E]/95 border border-emerald-500/40 shadow-xl backdrop-blur-md text-[11px] font-medium text-white">
                                <i data-lucide="scale" class="w-3.5 h-3.5 text-emerald-400"></i>
                                <span>BOM Recipe: <strong class="text-emerald-400">Auto-Deduct</strong></span>
                            </div>
                            <div class="hidden sm:flex absolute -bottom-3.5 -left-3.5 items-center gap-2 px-3 py-1.5 rounded-xl bg-[#060B1E]/95 border border-[#00C4D8]/40 shadow-xl backdrop-blur-md text-[11px] font-medium text-white">
                                <i data-lucide="printer" class="w-3.5 h-3.5 text-[#00C4D8]"></i>
                                <span>Multi-KOT Kitchen &amp; Bar</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- Content Body with Light/Dark Mode --}}
        <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 space-y-20 sm:space-y-28">

            {{-- Deep Sector Pain Points --}}
            <section
                class="p-6 sm:p-10 rounded-[24px] bg-rose-500/[0.04] dark:bg-rose-500/[0.08] border border-rose-500/20 space-y-8">
                <div class="max-w-2xl">
                    <span class="text-xs uppercase tracking-wider font-bold text-rose-600 dark:text-rose-400 block">
                        Tantangan Nyata Industri F&B
                    </span>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white mt-1">
                        Kendala Operasional yang Menggerus Margin Restoran dan Kafe
                    </h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                    <div
                        class="p-5 rounded-[18px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-8 h-8 rounded-[10px] bg-rose-500/10 text-rose-600 dark:text-rose-400 font-bold text-xs flex items-center justify-center font-mono">
                            01</div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Bocoran Bahan Baku & Food Waste</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Biji kopi, sirup, daging, dan susu sering habis lebih cepat dari omzet penjualan karena takaran
                            porsi tidak terstandar dan ketiadaan sistem potong gramatur resep.
                        </p>
                    </div>

                    <div
                        class="p-5 rounded-[18px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-8 h-8 rounded-[10px] bg-rose-500/10 text-rose-600 dark:text-rose-400 font-bold text-xs flex items-center justify-center font-mono">
                            02</div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Tiket Dapur Tertukar Saat Rush Hour
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Kertas bon kasir yang dibawa lari pelayan ke dapur sering hilang atau salah baca catatan pesanan
                            khusus, memicu komplain tamu dan makanan terpaksa dibuang.
                        </p>
                    </div>

                    <div
                        class="p-5 rounded-[18px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-8 h-8 rounded-[10px] bg-rose-500/10 text-rose-600 dark:text-rose-400 font-bold text-xs flex items-center justify-center font-mono">
                            03</div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Laba Semu Akibat Fluktuasi Harga Bahan
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Resto terlihat ramai setiap malam tetapi saldo kas tidak bertambah karena HPP menu tidak
                            di-update ketika harga telur, cabai, minyak, atau daging di pasar melonjak.
                        </p>
                    </div>
                </div>
            </section>

            {{-- 6 Specialized Features (Bento Grid) --}}
            <section class="space-y-8">
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#00C4D8] block">
                        Kapabilitas Khusus F&B
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        Fitur Operasional yang Dibangun Khusus untuk Kuliner
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 rounded-[22px] space-y-3 shadow-sm hover:border-[#007AFF]/40 transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-amber-500/10 text-amber-500 flex items-center justify-center">
                            <i data-lucide="layers" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Resep & Potong Stok Bahan (BOM)</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Definisikan resep standar menu Anda. Setiap 1 porsi nasi goreng atau 1 cup latte terjual, stok
                            beras, telur, daging, dan biji kopi terpotong otomatis di inventaris.
                        </p>
                    </div>

                    <div
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 rounded-[22px] space-y-3 shadow-sm hover:border-[#007AFF]/40 transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-blue-500/10 text-[#007AFF] dark:text-[#00C4D8] flex items-center justify-center">
                            <i data-lucide="printer" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Kitchen Order Ticket (KOT) Terpisah
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Sistem secara pintar membagi order: makanan tercetak di dapur utama (kitchen), sementara minuman
                            langsung tercetak di printer station barista bar secara simultan.
                        </p>
                    </div>

                    <div
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 rounded-[22px] space-y-3 shadow-sm hover:border-[#007AFF]/40 transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                            <i data-lucide="layout-grid" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Denah Meja Visual & Split Bill</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Pantau meja terisi, kosong, atau sedang reservasi. Kasir dapat dengan cepat memindahkan meja
                            tamu, menggabungkan meja, atau memisahkan tagihan pembayaran per orang.
                        </p>
                    </div>

                    <div
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 rounded-[22px] space-y-3 shadow-sm hover:border-[#007AFF]/40 transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-purple-500/10 text-purple-500 flex items-center justify-center">
                            <i data-lucide="qr-code" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Pemesanan Mandiri QR di Meja</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Tamu dapat memindai barcode QR yang ditempel di meja untuk melihat buku menu digital, memesan
                            langsung dari HP, dan bayar lewat QRIS tanpa menunggu staf.
                        </p>
                    </div>

                    <div
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 rounded-[22px] space-y-3 shadow-sm hover:border-[#007AFF]/40 transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-rose-500/10 text-rose-500 flex items-center justify-center">
                            <i data-lucide="calculator" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Food Costing & Margin Per Porsi</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Ketahui persis berapa keuntungan bersih dari setiap menu setelah dikurangi biaya bahan baku,
                            bumbu, packaging takeaway, dan biaya operasional per porsi.
                        </p>
                    </div>

                    <div
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 rounded-[22px] space-y-3 shadow-sm hover:border-[#007AFF]/40 transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-indigo-500/10 text-indigo-500 flex items-center justify-center">
                            <i data-lucide="clock" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Shift Kasir & Tutup Buku Anti
                            Selisih</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Pencatatan kas modal awal, penerimaan tunai, QRIS, kartu debit, dan pengeluaran petty cash.
                            Laporan tutup shift kasir tercetak rapi untuk mencegah kebocoran uang kas.
                        </p>
                    </div>
                </div>
            </section>

            {{-- Operational Flow / Connected System Architecture (Midnight Strip) --}}
            <section
                class="p-6 sm:p-10 rounded-[24px] bg-[#060B1E] text-white border border-white/10 space-y-8 relative overflow-hidden shadow-xl">
                <div
                    class="absolute -right-20 -bottom-20 w-80 h-80 bg-[#007AFF]/10 rounded-full blur-[100px] pointer-events-none">
                </div>

                <div class="max-w-2xl relative z-10">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#00C4D8] block">
                        Alur Ekosistem Kuliner
                    </span>
                    <h2 class="text-xl sm:text-2xl font-bold text-white mt-1">
                        Bagaimana COOCA Menghubungkan Dapur, Kasir, dan Finansial
                    </h2>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 relative z-10">
                    <div class="p-5 rounded-[18px] bg-white/5 border border-white/10 space-y-2 backdrop-blur-sm">
                        <div class="text-xs font-mono font-bold text-[#00C4D8]">Langkah 01</div>
                        <h4 class="text-sm font-bold text-white">Pemesanan Masuk</h4>
                        <p class="text-xs text-slate-300 leading-relaxed">Pelayan input via tablet atau tamu scan QR
                            mandiri di meja.</p>
                    </div>
                    <div class="p-5 rounded-[18px] bg-white/5 border border-white/10 space-y-2 backdrop-blur-sm">
                        <div class="text-xs font-mono font-bold text-amber-400">Langkah 02</div>
                        <h4 class="text-sm font-bold text-white">Tiket Dapur Cetak</h4>
                        <p class="text-xs text-slate-300 leading-relaxed">KOT tercetak di printer koki dapur & bar barista
                            secara otomatis.</p>
                    </div>
                    <div class="p-5 rounded-[18px] bg-white/5 border border-white/10 space-y-2 backdrop-blur-sm">
                        <div class="text-xs font-mono font-bold text-emerald-400">Langkah 03</div>
                        <h4 class="text-sm font-bold text-white">Bahan Baku Terpotong</h4>
                        <p class="text-xs text-slate-300 leading-relaxed">Inventaris bahan segar di gudang berkurang sesuai
                            gramatur resep.</p>
                    </div>
                    <div class="p-5 rounded-[18px] bg-white/5 border border-white/10 space-y-2 backdrop-blur-sm">
                        <div class="text-xs font-mono font-bold text-purple-400">Langkah 04</div>
                        <h4 class="text-sm font-bold text-white">Jurnal Kas Masuk</h4>
                        <p class="text-xs text-slate-300 leading-relaxed">Pembayaran lunas langsung tercatat di laporan
                            laba rugi owner.</p>
                    </div>
                </div>
            </section>

            {{-- Sector Specific FAQs --}}
            <section class="space-y-6 max-w-3xl mx-auto">
                <div class="text-center space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#00C4D8] block">Tanya
                        Jawab</span>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Pertanyaan Umum Seputar COOCA
                        F&B</h2>
                </div>

                <div class="space-y-3.5">
                    <details
                        class="group bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-[18px] p-5 transition-all">
                        <summary
                            class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                            <span>Apakah COOCA bisa mencetak ke printer Bluetooth thermal dan LAN dapur?</span>
                            <i data-lucide="chevron-down"
                                class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"
                                aria-hidden="true"></i>
                        </summary>
                        <p
                            class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-white/10 pt-3">
                            Ya. COOCA mendukung koneksi printer thermal Bluetooth (58mm dan 80mm) yang biasa digunakan di
                            kasir mobile, serta printer kabel LAN (Ethernet) yang biasa dipasang di dalam area dapur yang
                            bersuhu panas.
                        </p>
                    </details>

                    <details
                        class="group bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-[18px] p-5 transition-all">
                        <summary
                            class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                            <span>Bagaimana jika menu memiliki varian (contoh: Level Pedas, Less Sugar, Extra Shot)?</span>
                            <i data-lucide="chevron-down"
                                class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"
                                aria-hidden="true"></i>
                        </summary>
                        <p
                            class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-white/10 pt-3">
                            COOCA memiliki fitur modifier dan add-on dinamis. Anda dapat menambahkan opsi modifier berbayar
                            (seperti Extra Shot Espresso +Rp 5.000) yang ikut memotong bahan baku tambahan, maupun modifier
                            gratis (seperti Less Sugar / No Ice) yang tercetak jelas sebagai catatan koki/barista.
                        </p>
                    </details>

                    <details
                        class="group bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-[18px] p-5 transition-all">
                        <summary
                            class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                            <span>Apakah COOCA cocok untuk usaha kuliner yang memiliki banyak cabang kafe?</span>
                            <i data-lucide="chevron-down"
                                class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"
                                aria-hidden="true"></i>
                        </summary>
                        <p
                            class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-white/10 pt-3">
                            Sangat cocok. COOCA didesain multi-outlet secara bawaan. Anda dapat memantau penjualan seluruh
                            cabang dari satu dashboard, mendistribusikan bahan baku dari central kitchen (gudang pusat) ke
                            tiap outlet cabang, dan membandingkan performa omzet per gerai secara real-time.
                        </p>
                    </details>

                    <details
                        class="group bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-[18px] p-5 transition-all">
                        <summary
                            class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                            <span>Bagaimana cara menangani bahan baku yang basi atau tumpah (spoilage/waste)?</span>
                            <i data-lucide="chevron-down"
                                class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"
                                aria-hidden="true"></i>
                        </summary>
                        <p
                            class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-white/10 pt-3">
                            Tersedia fitur Waste & Stock Adjustment. Staf dapur dapat mencatat bahan yang rusak atau tumpah
                            disertai alasan. Sistem akan mencatatnya sebagai beban kerugian operasional sehingga catatan
                            sisa fisik di gudang tetap sinkron dengan laporan keuangan.
                        </p>
                    </details>
                </div>
            </section>

            {{-- Related Modules & Vertical Cross Links --}}
            <section class="border-t border-slate-200/80 dark:border-white/10 pt-12 space-y-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Jelajahi Solusi Industri & Modul Terkait</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <a href="{{ route('public.erp.pos') }}"
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-md transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF] dark:text-[#00C4D8]">Modul Kasir</span>
                        <h4
                            class="text-sm font-bold text-slate-900 dark:text-white mt-1.5 group-hover:text-[#007AFF] dark:group-hover:text-[#00C4D8] transition-colors">
                            POS Kasir Cepat</h4>
                    </a>
                    <a href="{{ route('public.erp.inventory') }}"
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-md transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF] dark:text-[#00C4D8]">Modul Stok</span>
                        <h4
                            class="text-sm font-bold text-slate-900 dark:text-white mt-1.5 group-hover:text-[#007AFF] dark:group-hover:text-[#00C4D8] transition-colors">
                            Manajemen Stok & BOM</h4>
                    </a>
                    <a href="{{ route('public.solutions.retail') }}"
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-md transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF] dark:text-[#00C4D8]">Solusi Industri</span>
                        <h4
                            class="text-sm font-bold text-slate-900 dark:text-white mt-1.5 group-hover:text-[#007AFF] dark:group-hover:text-[#00C4D8] transition-colors">
                            Retail & Swalayan</h4>
                    </a>
                    <a href="{{ route('public.solutions.services') }}"
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-md transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF] dark:text-[#00C4D8]">Solusi Industri</span>
                        <h4
                            class="text-sm font-bold text-slate-900 dark:text-white mt-1.5 group-hover:text-[#007AFF] dark:group-hover:text-[#00C4D8] transition-colors">
                            Bisnis Jasa & Servis</h4>
                    </a>
                </div>
            </section>

            {{-- Final CTA (Midnight #060B1E Card) --}}
            <section
                class="relative p-8 sm:p-14 rounded-[28px] bg-[#060B1E] text-white border border-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] overflow-hidden text-center space-y-6">
                <div
                    class="absolute -right-20 -top-20 w-80 h-80 bg-[#007AFF]/15 rounded-full blur-[100px] pointer-events-none">
                </div>
                <div
                    class="absolute -left-20 -bottom-20 w-80 h-80 bg-[#00C4D8]/10 rounded-full blur-[100px] pointer-events-none">
                </div>

                <div class="relative z-10 space-y-4 max-w-2xl mx-auto">
                    <div
                        class="text-xs font-semibold uppercase tracking-wider text-[#00C4D8] inline-flex items-center gap-1.5">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400" aria-hidden="true"></i>
                        <span>Tersedia untuk Android, Tablet, Laptop & Printer Kasir</span>
                    </div>
                    <h3 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                        Tingkatkan Kecepatan Pelayanan Kafe dan Resto Anda Sekarang
                    </h3>
                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
                        Daftar akun COOCA hari ini dan nikmati kemudahan mencatat pesanan meja, sinkronisasi tiket dapur,
                        dan
                        kontrol food cost tanpa software rumit.
                    </p>
                    <div class="pt-3 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                        <a href="{{ route('register') }}"
                            class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm inline-flex items-center gap-2 shadow-lg shadow-[#007AFF]/25 active:scale-95 transition-all">
                            <span>Coba F&B POS Gratis</span>
                            <i data-lucide="arrow-right" class="w-4 h-4" aria-hidden="true"></i>
                        </a>
                        <a href="{{ route('public.pricing') }}"
                            class="h-12 px-7 rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white font-semibold text-sm inline-flex items-center gap-2 transition-all">
                            <span>Lihat Paket Harga</span>
                        </a>
                    </div>
                </div>
            </section>

        </div>
    </div>
@endsection
