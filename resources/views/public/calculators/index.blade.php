@extends('layouts.public_marketing')

@section('title', 'Koleksi 8 Kalkulator Bisnis UMKM Online Gratis | COOCA')
@section('description', 'Koleksi kalkulator bisnis gratis untuk UMKM: Kalkulator HPP, BEP Titik Impas, Margin Harga Jual, Laba Bersih, Gaji Karyawan, PPh Final 0.5%, dan Simulasi What-If.')
@section('og_title', 'Koleksi 8 Kalkulator Bisnis UMKM Online Gratis | COOCA')
@section('og_description', 'Hitung HPP, BEP titik impas, margin harga jual, laba bersih, gaji, dan simulasi bisnis gratis tanpa instalasi.')
@section('canonical', route('kalkulator.index'))
@section('og_type', 'website')
@section('keywords', 'kalkulator bisnis umkm, kalkulator hpp online, kalkulator bep gratis, hitung harga jual margin, simulasi laba rugi, kalkulator pph 0.5')

@push('seo')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "WebApplication",
  "name": "Koleksi Kalkulator Bisnis & Finansial UMKM COOCA",
  "url": "{{ route('kalkulator.index') }}",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "All",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "IDR"
  },
  "description": "Koleksi kalkulator finansial gratis untuk UMKM: perhitungan HPP, titik impas BEP, margin laba, dan pajak."
}
</script>
@endpush

@section('content')
<div x-data="{
    refreshIcons() {
        this.$nextTick(() => {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    }
}" x-init="refreshIcons()"
class="w-full font-sans antialiased bg-[#F2F2F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] transition-colors pb-24">

    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- ═══ 1. HERO SECTION: 2-Grid Bento Apple HIG Canvas ══════════════════════ -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <section class="relative bg-[#060B1E] text-white pt-10 sm:pt-14 pb-16 lg:pb-20 overflow-hidden border-b border-white/10 w-full min-w-full">
        {{-- Dual Ambient Glows --}}
        <div class="absolute top-0 right-1/4 w-96 h-96 bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none"></div>
        <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10 space-y-6">

            <!-- Breadcrumb Navigation -->
            <nav class="flex items-center gap-2 text-xs text-slate-400" aria-label="Breadcrumb">
                <a href="{{ route('landing') }}" class="hover:text-white transition-colors">Beranda</a>
                <span aria-hidden="true" class="text-white/20">/</span>
                <span class="text-[#00C4D8] font-semibold" aria-current="page">Alat Hitung Bisnis UMKM</span>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                
                <!-- KIRI: Headline & CTA (Mobile Center, Desktop Left ~ 5 Cols) -->
                <div class="lg:col-span-5 space-y-6 text-center mx-auto flex flex-col items-center lg:text-left lg:items-start lg:mx-0">
                    <div class="space-y-3 w-full">
                        <!-- Pure Typographic Kicker -->
                        <div class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#00C4D8]">
                            Alat Bantu Keputusan Finansial UMKM
                        </div>

                        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.65rem] xl:text-[3rem] font-extrabold tracking-tight text-white leading-[1.15] text-balance break-words max-w-[22rem] sm:max-w-xl lg:max-w-none mx-auto lg:mx-0">
                            Kalkulator Finansial &amp; HPP Presisi
                        </h1>
                    </div>

                    <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal text-pretty max-w-[32rem] lg:max-w-xl mx-auto lg:mx-0">
                        Ambil keputusan harga jual dan kelola modal dengan pasti. Hitung biaya bahan baku per porsi, tentukan titik impas (BEP), dan simulasikan laba bersih riil secara instan tanpa perlu registrasi.
                    </p>

                    <!-- Trust Checkpoints -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1 text-left w-full">
                        <div class="p-3.5 rounded-[16px] bg-white/[0.05] border border-white/10">
                            <div class="text-xs font-bold text-white flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>100% Gratis</span>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Tanpa registrasi</div>
                        </div>

                        <div class="p-3.5 rounded-[16px] bg-white/[0.05] border border-white/10">
                            <div class="text-xs font-bold text-white flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-[#00C4D8] shrink-0"></i>
                                <span>Standar Akurat</span>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Rumus akuntansi riil</div>
                        </div>

                        <div class="p-3.5 rounded-[16px] bg-white/[0.05] border border-white/10">
                            <div class="text-xs font-bold text-white flex items-center gap-1.5">
                                <i data-lucide="shield-check" class="w-4 h-4 text-amber-400 shrink-0"></i>
                                <span>Privasi Aman</span>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Dihitung di browser</div>
                        </div>
                    </div>

                    <!-- Direct Action Buttons (Centered on Mobile, Row on Desktop) -->
                    <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center sm:justify-center lg:justify-start gap-3.5 w-full sm:w-auto">
                        <a href="#katalog-kalkulator"
                            class="h-12 px-7 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm flex items-center justify-center gap-2 active:scale-[0.98] transition shadow-lg shadow-[#007AFF]/25 min-h-[48px]">
                            <span>Pilih Alat Hitung</span>
                            <i data-lucide="arrow-down" class="w-4 h-4 shrink-0"></i>
                        </a>
                        <a href="{{ route('kalkulator.hpp') }}"
                            class="h-12 px-6 rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white text-sm font-semibold flex items-center justify-center gap-2 transition active:scale-[0.98] backdrop-blur-sm min-h-[48px]">
                            <i data-lucide="layers" class="w-4 h-4 text-[#00C4D8] shrink-0"></i>
                            <span>Hitung HPP Kuliner</span>
                        </a>
                    </div>
                </div>

                <!-- KANAN: Interactive Live Simulator Widget (7 Cols ~ 58%) -->
                <div class="lg:col-span-7 w-full" x-data="{
                    sampleCost: 20000,
                    targetMargin: 40,
                    get sellingPrice() {
                        let cost = parseFloat(this.sampleCost) || 0;
                        let m = parseFloat(this.targetMargin) || 0;
                        if (m >= 100) return 0;
                        return Math.round(cost / (1 - (m / 100)));
                    },
                    get profitAmount() {
                        return this.sellingPrice - (parseFloat(this.sampleCost) || 0);
                    }
                }">
                    <div class="rounded-[24px] bg-[#0B132B]/90 p-5 sm:p-6 shadow-2xl border border-white/10 backdrop-blur-xl text-white space-y-5">
                        <div class="flex items-center justify-between border-b border-white/10 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#FF5F56]"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-[#FFBD2E]"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-[#27C93F]"></span>
                                <span class="text-xs font-mono font-semibold text-slate-300 ml-2">
                                    Simulasi Instan HPP &amp; Laba
                                </span>
                            </div>
                            <span class="text-[11px] font-semibold text-emerald-400 bg-emerald-500/15 border border-emerald-500/25 px-2.5 py-0.5 rounded-full flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                Simulator Langsung
                            </span>
                        </div>

                        <!-- Interactive Inputs -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                    Modal Bahan Pokok (Rp)
                                </label>
                                <input type="number" x-model.number="sampleCost" step="1000" min="1000"
                                    class="w-full h-11 px-4 rounded-[12px] bg-white/[0.05] border border-white/15 text-sm font-semibold tabular-nums text-white focus:outline-none focus:ring-2 focus:ring-[#00C4D8]/30 focus:border-[#00C4D8]">
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="text-xs font-semibold text-slate-300">Target Margin Laba</label>
                                    <span class="text-xs font-bold text-[#00C4D8]" x-text="targetMargin + '%'"></span>
                                </div>
                                <input type="range" min="10" max="80" step="5"
                                    x-model.number="targetMargin" class="w-full accent-[#007AFF] cursor-pointer mt-2">
                            </div>
                        </div>

                        <!-- Real-Time Calculation Result Card -->
                        <div class="p-4 sm:p-5 rounded-[18px] bg-[#060B1E]/70 border border-white/10 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-300">Harga Jual Rekomendasi:</span>
                                <span class="text-xs font-bold text-emerald-400 bg-emerald-500/15 border border-emerald-500/25 px-2 py-0.5 rounded-[6px]">
                                    Aman Dari Rugi
                                </span>
                            </div>
                            <p class="text-2xl sm:text-3xl font-extrabold tabular-nums text-white">
                                Rp <span x-text="sellingPrice.toLocaleString('id-ID')"></span>
                            </p>
                            <div class="flex items-center justify-between pt-2 border-t border-white/10 text-xs">
                                <span class="text-slate-400">Estimasi Laba per Porsi:</span>
                                <span class="font-bold text-emerald-400 tabular-nums">
                                    +Rp <span x-text="profitAmount.toLocaleString('id-ID')"></span>
                                </span>
                            </div>
                        </div>

                        <a href="{{ route('kalkulator.hpp') }}"
                            class="w-full h-11 rounded-[12px] bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs font-semibold flex items-center justify-center gap-1.5 transition shadow-lg shadow-[#007AFF]/25 active:scale-[0.98]">
                            <span>Buka Kalkulator HPP Lengkap dengan Takaran Bahan</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- ═══ 2. BENTO GRID 8 KALKULATOR ═══════════════════════════════════════════ -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 space-y-16">

        <section id="katalog-kalkulator" class="space-y-8 scroll-mt-24">
            <div class="max-w-2xl space-y-2">
                <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">
                    KATALOG PERHITUNGAN
                </span>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight text-balance">
                    Pilih Alat Hitung Sesuai Kebutuhan Anda
                </h2>
                <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                    Didesain dengan petunjuk yang jelas dan bahasa Indonesia yang mudah dipahami pemilik usaha.
                </p>
            </div>

            <!-- Bento Composition: Asymmetric Hierarchy -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">

                <!-- 1. HPP (Anchor 1: Spans 2 cols) -->
                <a href="{{ route('kalkulator.hpp') }}"
                    class="col-span-1 md:col-span-2 p-6 sm:p-8 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm hover:border-[#007AFF]/40 hover:shadow-lg flex flex-col justify-between space-y-5 transition-all group">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="w-12 h-12 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center group-hover:scale-105 transition-transform">
                                <i data-lucide="layers" class="w-6 h-6"></i>
                            </div>
                            <span class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF] px-3 py-1 rounded-[8px] bg-[#007AFF]/10">
                                Paling Sering Digunakan
                            </span>
                        </div>

                        <div>
                            <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors">
                                Kalkulator HPP &amp; Harga Jual
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed mt-1.5">
                                Hitung biaya bahan baku, upah kerja, dan biaya operasional per porsi produk agar harga jual tidak nombok.
                            </p>
                        </div>

                        <div class="p-3 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/[0.04] dark:border-white/[0.04] flex items-center justify-between text-xs font-mono text-slate-600 dark:text-slate-400">
                            <span>[Bahan] + [Tenaga Kerja] + [Overhead]</span>
                            <span class="font-bold text-[#007AFF] dark:text-[#0A84FF]">= HPP Pokok</span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                        <span>Mulai Hitung HPP</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>

                <!-- 2. BEP (Anchor 2: Spans 1 col) -->
                <a href="{{ route('kalkulator.bep') }}"
                    class="col-span-1 p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm hover:border-emerald-500/40 hover:shadow-lg flex flex-col justify-between space-y-4 transition-all group">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="w-11 h-11 rounded-[12px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center group-hover:scale-105 transition-transform">
                                <i data-lucide="scale" class="w-5 h-5"></i>
                            </div>
                            <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">Titik Impas</span>
                        </div>

                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                Kalkulator BEP
                            </h3>
                            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mt-1">
                                Ketahui minimal berapa porsi atau omzet yang harus tercapai agar biaya modal tertutup.
                            </p>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                        <span>Hitung BEP</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>

                <!-- 3. Harga Jual & Markup (Spans 1 col) -->
                <a href="{{ route('kalkulator.harga-jual') }}"
                    class="col-span-1 p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm hover:border-amber-500/40 hover:shadow-lg flex flex-col justify-between space-y-4 transition-all group">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="w-11 h-11 rounded-[12px] bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center group-hover:scale-105 transition-transform">
                                <i data-lucide="tag" class="w-5 h-5"></i>
                            </div>
                            <span class="text-xs font-semibold text-amber-600 dark:text-amber-400">Margin Laba</span>
                        </div>

                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                                Margin Harga Jual
                            </h3>
                            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mt-1">
                                Pahami perbedaan markup dan margin agar persentase keuntungan tidak keliru dihitung.
                            </p>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between text-xs font-semibold text-amber-600 dark:text-amber-400">
                        <span>Buka Kalkulator</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>

                <!-- 4. Laba Bersih Riil (Spans 2 cols) -->
                <a href="{{ route('kalkulator.laba-bersih') }}"
                    class="col-span-1 md:col-span-2 p-6 sm:p-8 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm hover:border-[#007AFF]/40 hover:shadow-lg flex flex-col justify-between space-y-5 transition-all group">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="w-12 h-12 rounded-[14px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center group-hover:scale-105 transition-transform">
                                <i data-lucide="pie-chart" class="w-6 h-6"></i>
                            </div>
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 px-3 py-1 rounded-[8px] bg-emerald-500/10">
                                Laba Riil
                            </span>
                        </div>

                        <div>
                            <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors">
                                Kalkulator Laba Bersih Usaha
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed mt-1.5">
                                Simulasikan total omzet dikurangi seluruh beban operasional (gaji, sewa tempat, listrik, bahan) untuk mengetahui uang bersih yang benar-benar bisa dibawa pulang.
                            </p>
                        </div>

                        <div class="p-3 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/[0.04] dark:border-white/[0.04] flex items-center justify-between text-xs font-mono text-slate-600 dark:text-slate-400">
                            <span>Total Omzet - Biaya Operasional - Pajak</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">= Uang Masuk Bersih</span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                        <span>Audit Laba Bersih</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>

                <!-- 5. Gaji Karyawan (Spans 1 col) -->
                <a href="{{ route('kalkulator.gaji-karyawan') }}"
                    class="col-span-1 p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm hover:border-[#007AFF]/40 hover:shadow-lg flex flex-col justify-between space-y-4 transition-all group">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="w-11 h-11 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center group-hover:scale-105 transition-transform">
                                <i data-lucide="users" class="w-5 h-5"></i>
                            </div>
                            <span class="text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF]">Payroll</span>
                        </div>

                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors">
                                Gaji Karyawan
                            </h3>
                            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mt-1">
                                Perhitungan upah harian, bulanan, lembur, dan bonus karyawan dengan transparan.
                            </p>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                        <span>Buka Tool</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>

                <!-- 6. PPh Final 0.5% (Spans 1 col) -->
                <a href="{{ route('kalkulator.pph-final') }}"
                    class="col-span-1 p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm hover:border-rose-500/40 hover:shadow-lg flex flex-col justify-between space-y-4 transition-all group">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="w-11 h-11 rounded-[12px] bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center group-hover:scale-105 transition-transform">
                                <i data-lucide="receipt" class="w-5 h-5"></i>
                            </div>
                            <span class="text-xs font-semibold text-rose-600 dark:text-rose-400">Pajak PP 55</span>
                        </div>

                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-rose-600 dark:group-hover:text-rose-400 transition-colors">
                                PPh Final 0.5%
                            </h3>
                            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mt-1">
                                Simulasi kewajiban pajak dengan batas omzet tidak kena pajak hingga Rp 500 juta per tahun.
                            </p>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between text-xs font-semibold text-rose-600 dark:text-rose-400">
                        <span>Hitung Pajak</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>

                <!-- 7. Target Omzet Harian (Spans 1 col) -->
                <a href="{{ route('kalkulator.omzet-harian') }}"
                    class="col-span-1 p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm hover:border-[#007AFF]/40 hover:shadow-lg flex flex-col justify-between space-y-4 transition-all group">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="w-11 h-11 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center group-hover:scale-105 transition-transform">
                                <i data-lucide="target" class="w-5 h-5"></i>
                            </div>
                            <span class="text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF]">Target Harian</span>
                        </div>

                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors">
                                Omzet Harian
                            </h3>
                            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mt-1">
                                Pecah target omzet bulanan menjadi target transaksi dan rata-rata struk kasir per hari.
                            </p>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                        <span>Buka Tool</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>

                <!-- 8. Simulasi What-If (Spans 1 col) -->
                <a href="{{ route('kalkulator.simulasi-what-if') }}"
                    class="col-span-1 p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm hover:border-[#007AFF]/40 hover:shadow-lg flex flex-col justify-between space-y-4 transition-all group">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="w-11 h-11 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center group-hover:scale-105 transition-transform">
                                <i data-lucide="sliders-horizontal" class="w-5 h-5"></i>
                            </div>
                            <span class="text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF]">Simulasi Skenario</span>
                        </div>

                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors">
                                Simulasi What-If
                            </h3>
                            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mt-1">
                                Uji dampak jika harga bahan baku naik 10% atau volume penjualan turun terhadap sisa keuntungan.
                            </p>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                        <span>Mulai Simulasi</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>

            </div>
        </section>

        <!-- ═══ CONVERSION BANNER ═══ -->
        <section class="p-8 sm:p-10 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm flex flex-col lg:flex-row items-center justify-between gap-8">
            <div class="space-y-2 max-w-xl text-center lg:text-left">
                <div class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF] inline-flex items-center gap-1.5">
                    <i data-lucide="zap" class="w-4 h-4 text-amber-500"></i>
                    <span>OTOMATISASI PENUH</span>
                </div>
                <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Otomatiskan Seluruh Perhitungan Ini di Kasir COOCA
                </h3>
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    Tidak perlu lagi menghitung satu per satu secara manual. Setiap transaksi kasir POS otomatis memotong stok bahan baku, menghitung HPP secara riil, dan menyusun laporan laba rugi harian.
                </p>
            </div>
            <a href="{{ route('register') }}"
                class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm flex items-center gap-2 shrink-0 shadow-xs active:scale-[0.98] transition">
                <span>Daftar Akun Gratis Sekarang</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </section>

    </div>
</div>
@endsection
