@extends('layouts.public_marketing')

@section('title', $solution['title'] . ' | COOCA Business Operating System')
@section('description', $solution['subheadline'])
@section('keywords', strtolower($solution['title']) . ', aplikasi kasir indonesia, software pos ' . strtolower($solution['badge']) . ', aplikasi pembukuan ' . strtolower($solution['slug']))

@push('seo')
    <link rel="canonical" href="{{ route('solusi.show', $solution['slug']) }}">
    <meta property="og:title" content="{{ $solution['title'] }} | COOCA">
    <meta property="og:description" content="{{ $solution['subheadline'] }}">
    <meta property="og:url" content="{{ route('solusi.show', $solution['slug']) }}">
    <meta property="og:type" content="product">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $solution['title'] }} | COOCA">
    <meta name="twitter:description" content="{{ $solution['subheadline'] }}">

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
    <div class="pt-6 sm:pt-10 pb-24 bg-[#F5F5F7] dark:bg-[#000000] min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16 sm:space-y-24">

            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-2 text-xs text-[#6E6E73] dark:text-[#86868B]" aria-label="Breadcrumb">
                <a href="{{ route('landing') }}" class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Beranda</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('public.bos.overview') }}" class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Solusi Industri</a>
                <span aria-hidden="true">/</span>
                <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold" aria-current="page">{{ $solution['badge'] }}</span>
            </nav>

            {{-- 2-Grid Hero Section --}}
            <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                {{-- Left: Headline, Value Proposition, Actions --}}
                <div class="lg:col-span-7 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-500/10 dark:bg-blue-400/15 border border-blue-500/20 text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                        <i data-lucide="store" class="w-3.5 h-3.5" aria-hidden="true"></i>
                        <span>Solusi Khusus {{ $solution['badge'] }}</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-[3rem] font-bold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-[1.15]">
                        {{ $solution['headline'] }}
                    </h1>

                    <p class="text-base sm:text-lg text-[#6E6E73] dark:text-[#86868B] leading-relaxed max-w-xl">
                        {{ $solution['subheadline'] }}
                    </p>

                    {{-- Tangible Operational Highlights --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Bisa dari HP Android, Tablet, atau Laptop</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Cetak struk kasir via printer Bluetooth thermal</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Laporan laba bersih & stok otomatis terhubung</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Terhubung ke pembukuan finansial tanpa ribet</span>
                        </div>
                    </div>

                    {{-- CTAs --}}
                    <div class="pt-3 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
                        <a href="{{ route('register') }}" class="h-12 px-7 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-sm active:scale-[0.98] transition-all">
                            <span>Mulai Coba Sekarang</span>
                            <i data-lucide="arrow-right" class="w-4 h-4" aria-hidden="true"></i>
                        </a>
                        <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text=Halo%20saya%20tertarik%20dengan%20solusi%20{{ urlencode($solution['title']) }}" target="_blank" rel="noopener" class="h-12 px-6 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-neutral-50 dark:hover:bg-neutral-800/50 text-sm font-semibold flex items-center justify-center gap-2 shadow-sm transition active:scale-[0.98]">
                            <i data-lucide="message-circle" class="w-4 h-4 text-[#34C759]" aria-hidden="true"></i>
                            <span>Konsultasi via WhatsApp</span>
                        </a>
                    </div>
                </div>

                {{-- Right: Simulated POS & Operating Card --}}
                <div class="lg:col-span-5">
                    <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 shadow-sm space-y-5">
                        <div class="flex items-center justify-between border-b border-neutral-100 dark:border-neutral-800/80 pb-4">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                <span class="text-xs font-mono font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Terminal Kasir: {{ $solution['badge'] }}</span>
                            </div>
                            <span class="text-[11px] font-semibold text-emerald-600 bg-emerald-500/10 px-2.5 py-0.5 rounded-full flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Kasir Siap
                            </span>
                        </div>

                        {{-- Simulated Active Ticket --}}
                        <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-3">
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Nota Transaksi #TRX-1049</span>
                                <span class="text-[#6E6E73] dark:text-[#86868B] font-mono">Hari Ini, 14:22</span>
                            </div>
                            <div class="space-y-2 text-xs">
                                <div class="flex justify-between text-[#1D1D1F] dark:text-[#F5F5F7]">
                                    <span>Paket Operasional {{ $solution['badge'] }} (1x)</span>
                                    <span class="font-mono font-semibold">Rp 45.000</span>
                                </div>
                                <div class="flex justify-between text-[#6E6E73] dark:text-[#86868B]">
                                    <span>HPP Terhitung Otomatis</span>
                                    <span class="font-mono">Rp 22.500</span>
                                </div>
                                <div class="flex justify-between text-emerald-600 dark:text-emerald-400 font-bold border-t border-dashed border-neutral-200 dark:border-neutral-700/60 pt-2">
                                    <span>Margin Keuntungan Bersih</span>
                                    <span class="font-mono">+50.0% (Rp 22.500)</span>
                                </div>
                            </div>
                        </div>

                        {{-- Hardware Integration Row --}}
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div class="p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 flex items-center gap-2">
                                <i data-lucide="printer" class="w-4 h-4 text-[#007AFF]" aria-hidden="true"></i>
                                <span class="font-medium text-[#1D1D1F] dark:text-[#F5F5F7]">Printer Siap</span>
                            </div>
                            <div class="p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 flex items-center gap-2">
                                <i data-lucide="qr-code" class="w-4 h-4 text-emerald-600" aria-hidden="true"></i>
                                <span class="font-medium text-[#1D1D1F] dark:text-[#F5F5F7]">QRIS Dinamis</span>
                            </div>
                        </div>

                        {{-- Action preview badge --}}
                        <div class="p-3 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] text-xs font-semibold text-center flex items-center justify-center gap-2">
                            <i data-lucide="check" class="w-4 h-4" aria-hidden="true"></i>
                            <span>Stok & Pembukuan Langsung Sinkron Otomatis</span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Section: Pain Points (Apple Bento Inset Card) --}}
            <section class="p-6 sm:p-10 rounded-[24px] bg-rose-500/[0.03] dark:bg-rose-500/[0.06] border border-rose-500/15 space-y-6">
                <div class="max-w-2xl">
                    <span class="text-xs uppercase tracking-wider font-bold text-rose-600 dark:text-rose-400 block">
                        Tantangan Sehari-hari
                    </span>
                    <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1">
                        Sering Mengalami Kendala Ini di Usaha Anda?
                    </h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-5">
                    @foreach ($solution['pain_points'] as $index => $pain)
                        <div class="p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/70 dark:border-neutral-800 space-y-3 shadow-sm">
                            <div class="w-8 h-8 rounded-[10px] bg-rose-500/10 text-rose-600 font-bold text-xs flex items-center justify-center font-mono">
                                0{{ $index + 1 }}
                            </div>
                            <p class="text-xs sm:text-sm text-[#1D1D1F] dark:text-[#F5F5F7] leading-relaxed">
                                {{ $pain }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Section: Fitur Solusi Khusus (Apple Bento Grid) --}}
            <section class="space-y-8">
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">
                        Fitur Unggulan Spesifik
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">
                        Bagaimana COOCA Mempermudah Operasional Harian
                    </h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 sm:gap-6">
                    @foreach ($solution['features'] as $feat)
                        <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 sm:p-7 rounded-[22px] space-y-3 shadow-sm hover:border-[#007AFF]/40 transition-all">
                            <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center">
                                <i data-lucide="check-circle" class="w-5 h-5" aria-hidden="true"></i>
                            </div>
                            <h3 class="text-base sm:text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">
                                {{ $feat['title'] }}
                            </h3>
                            <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                                {{ $feat['desc'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Testimonial Box (Apple Inset Card) --}}
            @if(isset($solution['testimonial']))
            <section class="p-8 sm:p-10 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 flex flex-col md:flex-row items-start md:items-center gap-6 sm:gap-8 shadow-sm">
                <div class="w-12 h-12 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                    <i data-lucide="quote" class="w-6 h-6" aria-hidden="true"></i>
                </div>
                <div class="space-y-3">
                    <p class="text-base sm:text-lg text-[#1D1D1F] dark:text-[#F5F5F7] italic leading-relaxed">
                        "{{ $solution['testimonial']['quote'] }}"
                    </p>
                    <div>
                        <div class="font-bold text-sm text-[#1D1D1F] dark:text-[#F5F5F7]">
                            {{ $solution['testimonial']['author'] }}
                        </div>
                        <div class="text-xs text-[#007AFF] dark:text-[#0A84FF] font-semibold mt-0.5">
                            {{ $solution['testimonial']['business'] }}
                        </div>
                    </div>
                </div>
            </section>
            @endif

            {{-- Cross-Link to Other Verticals --}}
            <section class="border-t border-neutral-200/80 dark:border-neutral-800 pt-12 space-y-6">
                <h3 class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Solusi Industri Lainnya</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @foreach ($otherSolutions as $os)
                        <a href="{{ route('solusi.show', $os['slug']) }}" class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-sm transition-all">
                            <span class="text-xs uppercase font-bold text-[#007AFF] dark:text-[#0A84FF]">{{ $os['badge'] }}</span>
                            <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1.5 group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors leading-snug">
                                {{ $os['title'] }}
                            </h4>
                        </a>
                    @endforeach
                </div>
            </section>

            {{-- Final CTA --}}
            <section class="p-8 sm:p-12 rounded-[24px] bg-[#161618] border border-white/[0.08] text-white text-center space-y-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-[#86868B] inline-flex items-center gap-1.5 mx-auto">
                    <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759]" aria-hidden="true"></i>
                    <span>Tersedia untuk Android, Tablet, Laptop, & Printer Bluetooth</span>
                </div>
                <h3 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                    Mulai Digitalisasi Usaha Anda Hari Ini
                </h3>
                <p class="text-sm text-[#86868B] max-w-xl mx-auto leading-relaxed">
                    Tidak perlu beli mesin kasir mahal. Cukup gunakan HP Android, tablet, atau laptop yang sudah Anda miliki sekarang untuk mengelola bisnis lebih rapi.
                </p>
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                    <a href="{{ route('register') }}" class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm inline-flex items-center gap-2 shadow-sm active:scale-95 transition-all">
                        <span>Daftar Akun COOCA</span>
                        <i data-lucide="arrow-right" class="w-4 h-4" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('public.pricing') }}" class="h-12 px-7 rounded-[14px] bg-white/10 hover:bg-white/15 text-white font-semibold text-sm inline-flex items-center gap-2 transition-all">
                        <span>Lihat Paket Harga</span>
                    </a>
                </div>
            </section>

        </div>
    </div>
@endsection
