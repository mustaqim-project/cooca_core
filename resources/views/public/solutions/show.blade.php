@extends('layouts.public_marketing')

@section('title', $solution['title'] . ' | COOCA Business Operating System')
@section('description', $solution['subheadline'])
@section('og_title', $solution['title'] . ' | COOCA')
@section('og_description', $solution['subheadline'])
@section('canonical', route('solusi.show', $solution['slug']))
@section('og_type', 'product')
@section('keywords', strtolower($solution['title']) . ', aplikasi kasir indonesia, software pos ' . strtolower($solution['badge']) . ', aplikasi pembukuan ' . strtolower($solution['slug']))

    @push('seo')
        <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "COOCA {{ $solution['badge'] }}",
      "applicationCategory": "BusinessApplication",
      "operatingSystem": "Web, Android, iOS, Windows, macOS",
      "description": "{{ addslashes($solution['subheadline']) }}",
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
                                Solusi Khusus {{ $solution['badge'] }}
                            </p>
                        </div>

                        {{-- Main Headline --}}
                        <h1
                            class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.85rem] xl:text-[3.25rem] font-extrabold text-white tracking-tight leading-[1.12] text-balance">
                            {{ $solution['headline'] }}
                        </h1>

                        {{-- Subtitle Paragraph --}}
                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal text-pretty max-w-2xl">
                            {{ $solution['subheadline'] }}
                        </p>

                        {{-- Tangible Operational Highlights Bento Tiles --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1 text-left w-full">
                            <div class="p-2.5 sm:p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5 hover:bg-white/[0.08] transition-colors">
                                <div class="w-6 h-6 rounded-lg bg-emerald-500/15 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5 border border-emerald-500/20">
                                    <i data-lucide="smartphone" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200 leading-snug min-w-0 flex-1">Android, Tablet, atau Laptop</span>
                            </div>
                            <div class="p-2.5 sm:p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5 hover:bg-white/[0.08] transition-colors">
                                <div class="w-6 h-6 rounded-lg bg-sky-500/15 text-[#00C4D8] flex items-center justify-center shrink-0 mt-0.5 border border-sky-400/20">
                                    <i data-lucide="printer" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200 leading-snug min-w-0 flex-1">Printer Bluetooth Thermal</span>
                            </div>
                            <div class="p-2.5 sm:p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5 hover:bg-white/[0.08] transition-colors">
                                <div class="w-6 h-6 rounded-lg bg-amber-500/15 text-amber-400 flex items-center justify-center shrink-0 mt-0.5 border border-amber-400/20">
                                    <i data-lucide="line-chart" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200 leading-snug min-w-0 flex-1">Laba bersih &amp; stok otomatis</span>
                            </div>
                            <div class="p-2.5 sm:p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5 hover:bg-white/[0.08] transition-colors">
                                <div class="w-6 h-6 rounded-lg bg-purple-500/15 text-purple-400 flex items-center justify-center shrink-0 mt-0.5 border border-purple-400/20">
                                    <i data-lucide="book-open" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200 leading-snug min-w-0 flex-1">Sinkron pembukuan finansial</span>
                            </div>
                        </div>

                        {{-- Action CTAs (Left-aligned) --}}
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                            <a href="{{ route('register') }}"
                                class="inline-flex justify-center items-center gap-2.5 px-6 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm shadow-lg shadow-[#007AFF]/25 hover:shadow-xl hover:shadow-[#007AFF]/35 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 min-h-[48px]">
                                <span>Mulai Coba Sekarang</span>
                                <i data-lucide="arrow-right" class="w-4 h-4 shrink-0" aria-hidden="true"></i>
                            </a>
                            <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text=Halo%20saya%20tertarik%20dengan%20solusi%20{{ urlencode($solution['title']) }}"
                                target="_blank" rel="noopener"
                                class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 border border-white/15 text-white backdrop-blur-sm text-sm font-semibold hover:-translate-y-0.5 active:translate-y-0 transition-all min-h-[48px]">
                                <i data-lucide="message-circle" class="w-4 h-4 text-emerald-400 shrink-0" aria-hidden="true"></i>
                                <span>Konsultasi via WhatsApp</span>
                            </a>
                        </div>

                        {{-- Reassurance Checkpoints --}}
                        <div class="pt-3 border-t border-white/10 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-slate-300">
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Android, Tablet &amp; Laptop</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Printer Thermal Bluetooth</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Sinkron Finansial Real-Time</span>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Apple Bento Terminal Operating Card (6 Cols) --}}
                    <div class="lg:col-span-6 relative mt-4 lg:mt-0">
                        {{-- Spotlight glow behind window --}}
                        <div class="absolute -inset-1.5 bg-gradient-to-r from-[#007AFF]/30 to-[#00C4D8]/30 rounded-[32px] blur-xl opacity-75"></div>

                        <div
                            class="relative bg-[#0A122C]/90 border border-white/15 rounded-[18px] sm:rounded-[28px] p-3.5 sm:p-5 lg:p-6 shadow-2xl backdrop-blur-2xl text-white space-y-4">
                            {{-- Specular top highlight line --}}
                            <div class="absolute top-0 inset-x-8 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>

                            {{-- Terminal Header --}}
                            <div class="flex items-center justify-between gap-3 pb-3 border-b border-white/10">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-[#007AFF]/20 text-[#00C4D8] flex items-center justify-center shrink-0 border border-[#007AFF]/30">
                                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-white text-xs sm:text-sm truncate">Terminal: {{ $solution['badge'] }}</div>
                                        <div class="text-[10px] text-slate-400 truncate">Kasir POS &amp; Pembukuan Operasional</div>
                                    </div>
                                </div>
                                <span
                                    class="text-[11px] font-semibold text-emerald-400 bg-emerald-400/15 border border-emerald-400/30 px-2.5 py-1 rounded-full flex items-center gap-1.5 shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0"></span> Kasir Siap
                                </span>
                            </div>

                            {{-- Simulated Active Ticket --}}
                            <div class="p-3.5 rounded-xl bg-[#060B1E]/80 border border-white/10 space-y-2.5">
                                <div class="flex justify-between items-center gap-2 text-xs">
                                    <span class="font-bold text-white truncate">Nota #TRX-1049</span>
                                    <span class="text-slate-400 font-mono shrink-0 text-[11px]">Hari Ini, 14:22</span>
                                </div>
                                <div class="space-y-2 text-xs">
                                    <div class="flex justify-between items-center gap-2 text-slate-200">
                                        <span class="truncate">Paket {{ $solution['badge'] }} (1x)</span>
                                        <span class="font-mono font-semibold text-white shrink-0">Rp 45.000</span>
                                    </div>
                                    <div class="flex justify-between items-center gap-2 text-slate-300">
                                        <span class="truncate">HPP Terhitung Otomatis</span>
                                        <span class="font-mono text-slate-400 shrink-0">Rp 22.500</span>
                                    </div>
                                    <div
                                        class="flex justify-between items-center gap-2 text-emerald-400 font-bold border-t border-dashed border-white/10 pt-2 text-xs">
                                        <span class="truncate">Margin Keuntungan</span>
                                        <span class="font-mono shrink-0">+50.0% (Rp 22.500)</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Hardware Integration Row --}}
                            <div class="grid grid-cols-2 gap-2.5 text-xs">
                                <div class="p-2.5 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center gap-2">
                                    <i data-lucide="printer" class="w-3.5 h-3.5 text-[#00C4D8] shrink-0" aria-hidden="true"></i>
                                    <span class="font-medium text-slate-200 text-[11px] truncate">Printer Siap</span>
                                </div>
                                <div class="p-2.5 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center gap-2">
                                    <i data-lucide="qr-code" class="w-3.5 h-3.5 text-emerald-400 shrink-0" aria-hidden="true"></i>
                                    <span class="font-medium text-slate-200 text-[11px] truncate">QRIS Dinamis</span>
                                </div>
                            </div>

                            {{-- Action preview badge --}}
                            <div
                                class="p-3 rounded-xl bg-[#007AFF]/15 text-[#00C4D8] border border-[#00C4D8]/30 text-xs font-semibold text-center flex items-center justify-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0" aria-hidden="true"></i>
                                <span class="truncate">Stok &amp; Pembukuan Langsung Sinkron</span>
                            </div>

                            {{-- Floating Badges --}}
                            <div class="hidden sm:flex absolute -top-3.5 -right-3.5 items-center gap-2 px-3 py-1.5 rounded-xl bg-[#060B1E]/95 border border-emerald-500/40 shadow-xl backdrop-blur-md text-[11px] font-medium text-white">
                                <i data-lucide="printer" class="w-3.5 h-3.5 text-emerald-400"></i>
                                <span>Printer &amp; QRIS: <strong class="text-emerald-400">Ready</strong></span>
                            </div>
                            <div class="hidden sm:flex absolute -bottom-3.5 -left-3.5 items-center gap-2 px-3 py-1.5 rounded-xl bg-[#060B1E]/95 border border-[#00C4D8]/40 shadow-xl backdrop-blur-md text-[11px] font-medium text-white">
                                <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-[#00C4D8]"></i>
                                <span>Real-Time Sync Pembukuan</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- Content Body with Light/Dark Mode --}}
        <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 space-y-20 sm:space-y-28">

            {{-- Section: Pain Points (Apple Bento Inset Card) --}}
            <section
                class="p-6 sm:p-10 rounded-[24px] bg-rose-500/[0.04] dark:bg-rose-500/[0.08] border border-rose-500/20 space-y-6">
                <div class="max-w-2xl">
                    <span class="text-xs uppercase tracking-wider font-bold text-rose-600 dark:text-rose-400 block">
                        Tantangan Sehari-hari
                    </span>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white mt-1 leading-snug text-balance break-words">
                        Sering Mengalami Kendala Ini di Usaha Anda?
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-5">
                    @foreach ($solution['pain_points'] as $index => $pain)
                        <div
                            class="p-5 rounded-[18px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                            <div
                                class="w-8 h-8 rounded-[10px] bg-rose-500/10 text-rose-600 dark:text-rose-400 font-bold text-xs flex items-center justify-center font-mono">
                                0{{ $index + 1 }}
                            </div>
                            <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 leading-relaxed text-pretty">
                                {{ $pain }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Section: Fitur Solusi Khusus (Apple Bento Grid) --}}
            <section class="space-y-8">
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#00C4D8] block">
                        Fitur Unggulan Spesifik
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug text-balance break-words">
                        Bagaimana COOCA Mempermudah Operasional Harian
                    </h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 sm:gap-6">
                    @foreach ($solution['features'] as $feat)
                        <div
                            class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 sm:p-7 rounded-[22px] space-y-3 shadow-sm hover:border-[#007AFF]/40 transition-all">
                            <div
                                class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#00C4D8] flex items-center justify-center">
                                <i data-lucide="check-circle" class="w-5 h-5" aria-hidden="true"></i>
                            </div>
                            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">
                                {{ $feat['title'] }}
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                                {{ $feat['desc'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Testimonial Box (Apple Inset Card) --}}
            @if (isset($solution['testimonial']))
                <section
                    class="p-8 sm:p-10 rounded-[24px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 flex flex-col md:flex-row items-start md:items-center gap-6 sm:gap-8 shadow-sm">
                    <div
                        class="w-12 h-12 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#00C4D8] flex items-center justify-center shrink-0">
                        <i data-lucide="quote" class="w-6 h-6" aria-hidden="true"></i>
                    </div>
                    <div class="space-y-3">
                        <p class="text-base sm:text-lg text-slate-800 dark:text-slate-200 italic leading-relaxed">
                            "{{ $solution['testimonial']['quote'] }}"
                        </p>
                        <div>
                            <div class="font-bold text-sm text-slate-900 dark:text-white">
                                {{ $solution['testimonial']['author'] }}
                            </div>
                            <div class="text-xs text-[#007AFF] dark:text-[#00C4D8] font-semibold mt-0.5">
                                {{ $solution['testimonial']['business'] }}
                            </div>
                        </div>
                    </div>
                </section>
            @endif

            {{-- Cross-Link to Other Verticals --}}
            <section class="border-t border-slate-200/80 dark:border-white/10 pt-12 space-y-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Solusi Industri Lainnya</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @foreach ($otherSolutions as $os)
                        <a href="{{ route('solusi.show', $os['slug']) }}"
                            class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-md transition-all">
                            <span
                                class="text-xs uppercase font-bold text-[#007AFF] dark:text-[#00C4D8]">{{ $os['badge'] }}</span>
                            <h4
                                class="text-sm font-bold text-slate-900 dark:text-white mt-1.5 group-hover:text-[#007AFF] dark:group-hover:text-[#00C4D8] transition-colors leading-snug">
                                {{ $os['title'] }}
                            </h4>
                        </a>
                    @endforeach
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
                        <span>Tersedia untuk Android, Tablet, Laptop, & Printer Bluetooth</span>
                    </div>
                    <h3 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight leading-snug text-balance break-words">
                        Mulai Digitalisasi Usaha Anda Hari Ini
                    </h3>
                    <p class="text-sm sm:text-base text-slate-300 max-w-xl mx-auto leading-relaxed text-pretty">
                        Tidak perlu beli mesin kasir mahal. Cukup gunakan HP Android, tablet, atau laptop yang sudah Anda
                        miliki sekarang untuk mengelola bisnis lebih rapi.
                    </p>
                    <div class="pt-3 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                        <a href="{{ route('register') }}"
                            class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm inline-flex items-center gap-2 shadow-lg shadow-[#007AFF]/25 active:scale-95 transition-all">
                            <span>Daftar Akun COOCA</span>
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
