@extends('layouts.public_marketing')

@section('title', 'Koleksi 8 Kalkulator Bisnis UMKM Online Gratis | Cooca')
@section('description',
    'Koleksi kalkulator bisnis gratis untuk UMKM: Kalkulator HPP, BEP Titik Impas, Margin Harga
    Jual, Laba Bersih, Gaji Karyawan, PPh Final 0.5%, dan Simulasi What-If.')
@section('keywords',
    'kalkulator bisnis umkm, kalkulator hpp online, kalkulator bep gratis, hitung harga jual margin,
    simulasi laba rugi, kalkulator pph 0.5')

@section('content')
    <div class="bg-white dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors duration-300 min-h-screen">

        <!-- ═══ HERO SECTION: Midnight Dark with Ambient Glows ═══ -->
        <section
            class="relative bg-[#060B1E] text-white pt-10 sm:pt-14 pb-16 lg:pb-20 overflow-hidden border-b border-white/10 w-full min-w-full">
            <!-- Dual Ambient Glows -->
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none">
            </div>
            <div
                class="absolute -bottom-40 -left-40 w-96 h-96 bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none">
            </div>

            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

                    <!-- KIRI: Headline & CTA (7 Cols) -->
                    <div class="lg:col-span-7 space-y-6 text-left">
                        <div class="space-y-3">
                            <div
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#007AFF]/15 text-[#00C4D8] border border-[#00C4D8]/30 text-xs font-semibold backdrop-blur-md">
                                <i data-lucide="calculator" class="w-4 h-4"></i>
                                <span>Alat Bantu Keputusan Bisnis UMKM</span>
                            </div>
                            <h1
                                class="text-3xl sm:text-4xl md:text-5xl lg:text-[3.25rem] font-extrabold text-white tracking-tight leading-[1.15] text-balance break-words">
                                Kalkulator Finansial &amp; <span class="text-[#00C4D8]">HPP Presisi</span>
                            </h1>
                        </div>

                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-2xl font-normal text-pretty break-words">
                            Ambil keputusan harga jual dan kelola modal dengan pasti. Hitung biaya bahan baku per porsi,
                            tentukan titik impas (BEP), dan simulasikan laba bersih riil secara instan tanpa perlu
                            registrasi.
                        </p>

                        <!-- Action Buttons -->
                        <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
                            <a href="#katalog-kalkulator"
                                class="px-8 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-base flex items-center justify-center gap-2 shadow-lg shadow-[#007AFF]/25 active:scale-[0.98] transition-all min-h-[50px]">
                                <span>Pilih Kalkulator Bisnis</span>
                                <i data-lucide="arrow-down" class="w-4 h-4"></i>
                            </a>
                            <a href="{{ route('kalkulator.hpp') }}"
                                class="px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 border border-white/20 text-white text-sm font-semibold flex items-center justify-center gap-2 backdrop-blur-sm shadow-sm transition active:scale-[0.98] min-h-[50px]">
                                <i data-lucide="layers" class="w-4 h-4 text-[#00C4D8]"></i>
                                <span>Hitung HPP Produk (Populer)</span>
                            </a>
                        </div>

                        <!-- Reassurance Checkpoints -->
                        <div class="pt-2 flex flex-wrap items-center gap-y-2 gap-x-5 text-xs text-slate-300">
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                                <span>100% Gratis Tanpa Batas</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                                <span>Standar Finansial Akuntansi</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                                <span>Data Aman (Dihitung di Perangkat Anda)</span>
                            </div>
                        </div>
                    </div>

                    <!-- KANAN: Interactive Live Simulator Widget (5 Cols) -->
                    <div class="lg:col-span-5" x-data="{
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
                        <div
                            class="bg-[#0E1E45]/80 border border-white/10 ring-1 ring-white/10 rounded-2xl shadow-2xl p-6 sm:p-7 space-y-5 backdrop-blur-md text-white">

                            <div class="flex items-center justify-between border-b border-white/10 pb-4">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-[#FF5F56]"></span>
                                    <span class="w-3 h-3 rounded-full bg-[#FFBD2E]"></span>
                                    <span class="w-3 h-3 rounded-full bg-[#27C93F]"></span>
                                </div>
                                <span class="text-xs font-semibold text-slate-300">
                                    Simulasi Instan HPP &amp; Laba
                                </span>
                                <div class="w-6"></div>
                            </div>

                            <!-- Interactive Inputs -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-200 mb-1.5">
                                        Modal Bahan Pokok (Rp)
                                    </label>
                                    <input type="number" x-model.number="sampleCost" step="1000" min="1000"
                                        class="w-full h-12 px-4 rounded-xl bg-white/10 border border-white/20 text-base font-semibold tabular-nums text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="text-xs font-semibold text-slate-200">Target Margin Laba</label>
                                        <span class="text-xs font-bold text-[#00C4D8]" x-text="targetMargin + '%'"></span>
                                    </div>
                                    <input type="range" min="10" max="80" step="5"
                                        x-model.number="targetMargin" class="w-full accent-[#007AFF] cursor-pointer">
                                </div>
                            </div>

                            <!-- Real-Time Calculation Result Card -->
                            <div class="p-4 rounded-xl bg-white/5 border border-white/10 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-slate-300">Harga Jual Rekomendasi</span>
                                    <span class="text-xs font-bold text-emerald-400">Aman dari Rugi</span>
                                </div>
                                <p class="text-3xl font-extrabold tabular-nums text-white">
                                    Rp <span x-text="sellingPrice.toLocaleString('id-ID')"></span>
                                </p>
                                <div class="flex items-center justify-between pt-2 border-t border-white/10 text-xs">
                                    <span class="text-slate-300">Estimasi Laba per Porsi:</span>
                                    <span class="font-bold text-emerald-400 tabular-nums">
                                        +Rp <span x-text="profitAmount.toLocaleString('id-ID')"></span>
                                    </span>
                                </div>
                            </div>

                            <a href="{{ route('kalkulator.hpp') }}"
                                class="w-full py-3 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs font-semibold flex items-center justify-center gap-1.5 transition active:scale-[0.98] shadow-md shadow-[#007AFF]/25">
                                <span>Buka Kalkulator HPP Lengkap</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>

                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- ═══ BENTO GRID 8 KALKULATOR (Light / Dark Compatible) ═══ -->
        <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-16">

            <section id="katalog-kalkulator" class="space-y-8 scroll-mt-20">
                <div class="max-w-2xl space-y-2">
                    <p class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#00C4D8]">Katalog
                        Perhitungan</p>
                    <h2
                        class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight text-balance break-words">
                        Pilih Alat Hitung Sesuai Kebutuhan Anda
                    </h2>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed text-pretty break-words">
                        Didesain dengan petunjuk yang jelas dan bahasa Indonesia yang mudah dipahami pemilik usaha.
                    </p>
                </div>

                <!-- Bento Composition: Asymmetric Hierarchy -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">

                    <!-- 1. HPP (Anchor 1: Spans 2 cols) -->
                    <a href="{{ route('kalkulator.hpp') }}"
                        class="col-span-1 md:col-span-2 p-6 sm:p-8 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm hover:border-[#007AFF]/40 hover:shadow-xl hover:shadow-[#007AFF]/5 flex flex-col justify-between space-y-5 transition-all group">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div
                                    class="w-12 h-12 rounded-xl bg-[#007AFF]/10 text-[#007AFF] dark:text-[#00C4D8] flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <i data-lucide="layers" class="w-6 h-6"></i>
                                </div>
                                <span
                                    class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#00C4D8] px-2.5 py-1 rounded-lg bg-[#007AFF]/10">
                                    Paling Sering Digunakan
                                </span>
                            </div>

                            <div>
                                <h3
                                    class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                                    Kalkulator HPP &amp; Harga Jual
                                </h3>
                                <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed mt-1.5">
                                    Hitung biaya bahan baku, upah kerja, dan biaya operasional per porsi produk agar harga
                                    jual tidak nombok.
                                </p>
                            </div>

                            <div
                                class="p-3 rounded-xl bg-slate-100 dark:bg-white/5 border border-slate-200/80 dark:border-white/10 flex items-center justify-between text-xs font-mono text-slate-600 dark:text-slate-400">
                                <span>[Bahan] + [Tenaga Kerja] + [Overhead]</span>
                                <span class="font-bold text-[#007AFF] dark:text-[#00C4D8]">= HPP Pokok</span>
                            </div>
                        </div>

                        <div
                            class="pt-4 border-t border-slate-100 dark:border-white/10 flex items-center justify-between text-sm font-semibold text-[#007AFF] dark:text-[#00C4D8]">
                            <span>Mulai Hitung HPP</span>
                            <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </a>

                    <!-- 2. BEP (Anchor 2: Spans 1 col) -->
                    <a href="{{ route('kalkulator.bep') }}"
                        class="col-span-1 p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm hover:border-emerald-500/40 hover:shadow-xl hover:shadow-emerald-500/5 flex flex-col justify-between space-y-4 transition-all group">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div
                                    class="w-11 h-11 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <i data-lucide="scale" class="w-5 h-5"></i>
                                </div>
                                <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">Titik
                                    Impas</span>
                            </div>

                            <div>
                                <h3
                                    class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                    Kalkulator BEP
                                </h3>
                                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed mt-1">
                                    Ketahui minimal berapa porsi atau rupiah yang harus terjual per bulan agar usaha balik
                                    modal.
                                </p>
                            </div>
                        </div>

                        <div
                            class="pt-3 border-t border-slate-100 dark:border-white/10 flex items-center justify-between text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                            <span>Hitung BEP</span>
                            <i data-lucide="arrow-right"
                                class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </a>

                    <!-- 3. Harga Jual & Markup (Anchor 3: Spans 1 col) -->
                    <a href="{{ route('kalkulator.harga-jual') }}"
                        class="col-span-1 p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm hover:border-amber-500/40 hover:shadow-xl hover:shadow-amber-500/5 flex flex-col justify-between space-y-4 transition-all group">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div
                                    class="w-11 h-11 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <i data-lucide="tag" class="w-5 h-5"></i>
                                </div>
                                <span class="text-xs font-semibold text-amber-500">Margin Laba</span>
                            </div>

                            <div>
                                <h3
                                    class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-amber-500 transition-colors">
                                    Margin Harga Jual
                                </h3>
                                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed mt-1">
                                    Pahami perbedaan markup dan margin agar persentase keuntungan tidak keliru dihitung.
                                </p>
                            </div>
                        </div>

                        <div
                            class="pt-3 border-t border-slate-100 dark:border-white/10 flex items-center justify-between text-xs font-semibold text-amber-500">
                            <span>Buka Kalkulator</span>
                            <i data-lucide="arrow-right"
                                class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </a>

                    <!-- 4. Laba Bersih Riil (Spans 2 cols) -->
                    <a href="{{ route('kalkulator.laba-bersih') }}"
                        class="col-span-1 md:col-span-2 p-6 sm:p-8 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm hover:border-[#007AFF]/40 hover:shadow-xl hover:shadow-[#007AFF]/5 flex flex-col justify-between space-y-5 transition-all group">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div
                                    class="w-12 h-12 rounded-xl bg-[#007AFF]/10 text-[#007AFF] dark:text-[#00C4D8] flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <i data-lucide="pie-chart" class="w-6 h-6"></i>
                                </div>
                                <span
                                    class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 px-2.5 py-1 rounded-lg bg-emerald-500/10">
                                    Laba Riil
                                </span>
                            </div>

                            <div>
                                <h3
                                    class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                                    Kalkulator Laba Bersih Usaha
                                </h3>
                                <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed mt-1.5">
                                    Simulasikan total omzet dikurangi seluruh beban operasional (gaji, sewa tempat, listrik,
                                    bahan) untuk mengetahui uang bersih yang benar-benar bisa dibawa pulang.
                                </p>
                            </div>

                            <div
                                class="p-3 rounded-xl bg-slate-100 dark:bg-white/5 border border-slate-200/80 dark:border-white/10 flex items-center justify-between text-xs font-mono text-slate-600 dark:text-slate-400">
                                <span>Total Omzet - Biaya Operasional - Pajak</span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400">= Uang Masuk Bersih</span>
                            </div>
                        </div>

                        <div
                            class="pt-4 border-t border-slate-100 dark:border-white/10 flex items-center justify-between text-sm font-semibold text-[#007AFF] dark:text-[#00C4D8]">
                            <span>Audit Laba Bersih</span>
                            <i data-lucide="arrow-right"
                                class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </a>

                    <!-- 5. Gaji Karyawan (Spans 1 col) -->
                    <a href="{{ route('kalkulator.gaji-karyawan') }}"
                        class="col-span-1 p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm hover:border-[#007AFF]/40 hover:shadow-xl hover:shadow-[#007AFF]/5 flex flex-col justify-between space-y-4 transition-all group">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div
                                    class="w-11 h-11 rounded-xl bg-[#007AFF]/10 text-[#007AFF] dark:text-[#00C4D8] flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <i data-lucide="users" class="w-5 h-5"></i>
                                </div>
                                <span class="text-xs font-semibold text-[#007AFF] dark:text-[#00C4D8]">Payroll</span>
                            </div>

                            <div>
                                <h3
                                    class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                                    Gaji Karyawan
                                </h3>
                                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed mt-1">
                                    Perhitungan upah harian, bulanan, lembur, dan bonus karyawan dengan transparan.
                                </p>
                            </div>
                        </div>

                        <div
                            class="pt-3 border-t border-slate-100 dark:border-white/10 flex items-center justify-between text-xs font-semibold text-[#007AFF] dark:text-[#00C4D8]">
                            <span>Buka Tool</span>
                            <i data-lucide="arrow-right"
                                class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </a>

                    <!-- 6. PPh Final 0.5% (Spans 1 col) -->
                    <a href="{{ route('kalkulator.pph-final') }}"
                        class="col-span-1 p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm hover:border-rose-500/40 hover:shadow-xl hover:shadow-rose-500/5 flex flex-col justify-between space-y-4 transition-all group">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div
                                    class="w-11 h-11 rounded-xl bg-rose-500/10 text-rose-500 flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <i data-lucide="receipt" class="w-5 h-5"></i>
                                </div>
                                <span class="text-xs font-semibold text-rose-500">Pajak PP 55</span>
                            </div>

                            <div>
                                <h3
                                    class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-rose-500 transition-colors">
                                    PPh Final 0.5%
                                </h3>
                                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed mt-1">
                                    Simulasi kewajiban pajak dengan batas omzet tidak kena pajak hingga Rp 500 juta per
                                    tahun.
                                </p>
                            </div>
                        </div>

                        <div
                            class="pt-3 border-t border-slate-100 dark:border-white/10 flex items-center justify-between text-xs font-semibold text-rose-500">
                            <span>Hitung Pajak</span>
                            <i data-lucide="arrow-right"
                                class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </a>

                    <!-- 7. Target Omzet Harian (Spans 1 col) -->
                    <a href="{{ route('kalkulator.omzet-harian') }}"
                        class="col-span-1 p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm hover:border-[#007AFF]/40 hover:shadow-xl hover:shadow-[#007AFF]/5 flex flex-col justify-between space-y-4 transition-all group">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div
                                    class="w-11 h-11 rounded-xl bg-[#007AFF]/10 text-[#007AFF] dark:text-[#00C4D8] flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <i data-lucide="target" class="w-5 h-5"></i>
                                </div>
                                <span class="text-xs font-semibold text-[#007AFF] dark:text-[#00C4D8]">Target Harian</span>
                            </div>

                            <div>
                                <h3
                                    class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                                    Omzet Harian
                                </h3>
                                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed mt-1">
                                    Pecah target omzet bulanan menjadi target transaksi dan rata-rata struk kasir per hari.
                                </p>
                            </div>
                        </div>

                        <div
                            class="pt-3 border-t border-slate-100 dark:border-white/10 flex items-center justify-between text-xs font-semibold text-[#007AFF] dark:text-[#00C4D8]">
                            <span>Buka Tool</span>
                            <i data-lucide="arrow-right"
                                class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </a>

                    <!-- 8. Simulasi What-If (Spans 1 col) -->
                    <a href="{{ route('kalkulator.simulasi-what-if') }}"
                        class="col-span-1 p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm hover:border-[#007AFF]/40 hover:shadow-xl hover:shadow-[#007AFF]/5 flex flex-col justify-between space-y-4 transition-all group">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div
                                    class="w-11 h-11 rounded-xl bg-[#007AFF]/10 text-[#007AFF] dark:text-[#00C4D8] flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <i data-lucide="sliders-horizontal" class="w-5 h-5"></i>
                                </div>
                                <span class="text-xs font-semibold text-[#007AFF] dark:text-[#00C4D8]">Simulasi
                                    Skenario</span>
                            </div>

                            <div>
                                <h3
                                    class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                                    Simulasi What-If
                                </h3>
                                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed mt-1">
                                    Uji dampak jika harga bahan baku naik 10% atau volume penjualan turun terhadap sisa
                                    keuntungan.
                                </p>
                            </div>
                        </div>

                        <div
                            class="pt-3 border-t border-slate-100 dark:border-white/10 flex items-center justify-between text-xs font-semibold text-[#007AFF] dark:text-[#00C4D8]">
                            <span>Mulai Simulasi</span>
                            <i data-lucide="arrow-right"
                                class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </a>

                </div>
            </section>

            <!-- ═══ CONVERSION BANNER ═══ -->
            <section
                class="relative p-8 sm:p-12 rounded-[28px] bg-[#060B1E] text-white border border-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] overflow-hidden">
                <div
                    class="absolute -top-24 -right-24 w-80 h-80 bg-[#007AFF]/20 rounded-full blur-[100px] pointer-events-none">
                </div>
                <div
                    class="absolute -bottom-24 -left-24 w-80 h-80 bg-[#00C4D8]/15 rounded-full blur-[100px] pointer-events-none">
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center relative z-10">
                    <div class="lg:col-span-8 space-y-3 text-left">
                        <p class="text-xs font-bold uppercase tracking-wider text-[#00C4D8]">
                            Otomatisasi Penuh
                        </p>
                        <h3 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight leading-tight text-balance break-words">
                            Otomatiskan Seluruh Perhitungan Ini di Kasir Cooca
                        </h3>
                        <p class="text-sm sm:text-base text-slate-300 leading-relaxed max-w-2xl text-pretty break-words">
                            Tidak perlu lagi menghitung satu per satu. Setiap transaksi kasir POS otomatis memotong stok
                            bahan baku, menghitung HPP secara riil, dan menyusun laporan laba rugi harian.
                        </p>
                    </div>
                    <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col gap-3">
                        <a href="{{ route('register') }}"
                            class="w-full py-4 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-base flex items-center justify-center gap-2 shadow-lg shadow-[#007AFF]/25 active:scale-[0.98] transition min-h-[48px]">
                            <span>Daftar Akun Gratis Sekarang</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            </section>

        </div>
    </div>
@endsection
