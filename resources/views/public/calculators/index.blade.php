@extends('layouts.public_marketing')

@section('title', 'Koleksi 8 Kalkulator Bisnis UMKM Online Gratis | Cooca')
@section('description',
    'Koleksi kalkulator bisnis gratis untuk UMKM: Kalkulator HPP, BEP Titik Impas, Margin Harga
    Jual, Laba Bersih, Gaji Karyawan, PPh Final 0.5%, dan Simulasi What-If.')
@section('keywords',
    'kalkulator bisnis umkm, kalkulator hpp online, kalkulator bep gratis, hitung harga jual margin,
    simulasi laba rugi, kalkulator pph 0.5')

@section('content')
    <div class="pt-6 sm:pt-10 pb-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16 sm:space-y-20">

            <!-- ═══ HERO SECTION (Mandatory 2-Grid Layout) ═══ -->
            <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

                <!-- KIRI: Headline & CTA (7 Cols) -->
                <div class="lg:col-span-7 space-y-6 text-left">
                    <div class="space-y-2">
                        <p
                            class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                            Alat Bantu Keputusan Bisnis UMKM
                        </p>
                        <h1
                            class="text-3xl sm:text-5xl lg:text-6xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-[1.12]">
                            Kalkulator Finansial &amp; <span class="text-[#007AFF] dark:text-[#0A84FF]">HPP Presisi</span>
                        </h1>
                    </div>

                    <p class="text-base sm:text-lg text-[#48484A] dark:text-[#AEAEB2] leading-relaxed max-w-2xl font-normal">
                        Ambil keputusan harga jual dan kelola modal dengan pasti. Hitung biaya bahan baku per porsi,
                        tentukan titik impas (BEP), dan simulasikan laba bersih riil secara instan tanpa perlu registrasi.
                    </p>

                    <!-- Action Buttons -->
                    <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
                        <a href="#katalog-kalkulator"
                            class="px-8 py-3.5 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-base flex items-center justify-center gap-2 shadow-sm active:scale-[0.98] transition-all min-h-[50px]">
                            <span>Pilih Kalkulator Bisnis</span>
                            <i data-lucide="arrow-down" class="w-4 h-4"></i>
                        </a>
                        <a href="{{ route('kalkulator.hpp') }}"
                            class="px-6 py-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.1] dark:border-white/[0.12] text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-black/[0.02] dark:hover:bg-white/[0.04] text-sm font-semibold flex items-center justify-center gap-2 shadow-sm transition active:scale-[0.98] min-h-[50px]">
                            <i data-lucide="layers" class="w-4 h-4 text-[#007AFF]"></i>
                            <span>Hitung HPP Produk (Populer)</span>
                        </a>
                    </div>

                    <!-- Reassurance Checkpoints for UMKM 40-65 -->
                    <div
                        class="pt-2 flex flex-wrap items-center gap-y-2 gap-x-5 text-xs text-[#6E6E73] dark:text-[#86868B]">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i>
                            <span>100% Gratis Tanpa Batas</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i>
                            <span>Standar Finansial Akuntansi</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i>
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
                        class="bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[24px] shadow-sm p-6 sm:p-7 space-y-5">

                        <div
                            class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#FF5F56] border border-black/10"></span>
                                <span class="w-3 h-3 rounded-full bg-[#FFBD2E] border border-black/10"></span>
                                <span class="w-3 h-3 rounded-full bg-[#27C93F] border border-black/10"></span>
                            </div>
                            <span class="text-xs font-semibold text-[#8E8E93] dark:text-[#98989D]">
                                Simulasi Instan HPP &amp; Laba
                            </span>
                            <div class="w-6"></div>
                        </div>

                        <!-- Interactive Inputs -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-[#48484A] dark:text-[#AEAEB2] mb-1.5">
                                    Modal Bahan Pokok (Rp)
                                </label>
                                <input type="number" x-model.number="sampleCost" step="1000" min="1000"
                                    class="w-full h-12 px-4 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/[0.06] dark:border-white/[0.08] text-base font-semibold tabular-nums text-[#1D1D1F] dark:text-[#F5F5F7] focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="text-xs font-semibold text-[#48484A] dark:text-[#AEAEB2]">Target Margin
                                        Laba</label>
                                    <span class="text-xs font-bold text-[#007AFF] dark:text-[#0A84FF]"
                                        x-text="targetMargin + '%'"></span>
                                </div>
                                <input type="range" min="10" max="80" step="5"
                                    x-model.number="targetMargin" class="w-full accent-[#007AFF] cursor-pointer">
                            </div>
                        </div>

                        <!-- Real-Time Calculation Result Card -->
                        <div
                            class="p-4 rounded-[16px] bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/[0.04] dark:border-white/[0.06] space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-[#6E6E73] dark:text-[#86868B]">Harga Jual Rekomendasi</span>
                                <span class="text-xs font-bold text-[#34C759] dark:text-[#30D158]">Aman dari Rugi</span>
                            </div>
                            <p class="text-3xl font-extrabold tabular-nums text-[#1D1D1F] dark:text-[#F5F5F7]">
                                Rp <span x-text="sellingPrice.toLocaleString('id-ID')"></span>
                            </p>
                            <div
                                class="flex items-center justify-between pt-2 border-t border-black/[0.06] dark:border-white/[0.08] text-xs">
                                <span class="text-[#6E6E73] dark:text-[#86868B]">Estimasi Laba per Porsi:</span>
                                <span class="font-bold text-[#34C759] dark:text-[#30D158] tabular-nums">
                                    +Rp <span x-text="profitAmount.toLocaleString('id-ID')"></span>
                                </span>
                            </div>
                        </div>

                        <a href="{{ route('kalkulator.hpp') }}"
                            class="w-full py-3 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-semibold flex items-center justify-center gap-1.5 transition active:scale-[0.98]">
                            <span>Buka Kalkulator HPP Lengkap</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>

                    </div>
                </div>

            </section>

            <!-- ═══ BENTO GRID 8 KALKULATOR ═══ -->
            <section id="katalog-kalkulator" class="space-y-8 scroll-mt-20">
                <div class="max-w-2xl space-y-2">
                    <p class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">Katalog
                        Perhitungan</p>
                    <h2
                        class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">
                        Pilih Alat Hitung Sesuai Kebutuhan Anda
                    </h2>
                    <p class="text-sm sm:text-base text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                        Didesain dengan petunjuk yang jelas dan bahasa Indonesia yang mudah dipahami pemilik usaha.
                    </p>
                </div>

                <!-- Bento Composition: Asymmetric Hierarchy -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">

                    <!-- 1. HPP (Anchor 1: Spans 2 cols) -->
                    <a href="{{ route('kalkulator.hpp') }}"
                        class="col-span-1 md:col-span-2 p-6 sm:p-8 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-sm hover:border-[#007AFF]/40 flex flex-col justify-between space-y-5 transition-all group">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div
                                    class="w-12 h-12 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <i data-lucide="layers" class="w-6 h-6"></i>
                                </div>
                                <span
                                    class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF] px-2.5 py-1 rounded-[8px] bg-[#007AFF]/10">
                                    Paling Sering Digunakan
                                </span>
                            </div>

                            <div>
                                <h3
                                    class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] transition-colors">
                                    Kalkulator HPP &amp; Harga Jual
                                </h3>
                                <p class="text-sm sm:text-base text-[#48484A] dark:text-[#AEAEB2] leading-relaxed mt-1.5">
                                    Hitung biaya bahan baku, upah kerja, dan biaya operasional per porsi produk agar harga
                                    jual tidak nombok.
                                </p>
                            </div>

                            <div
                                class="p-3 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between text-xs font-mono text-[#6E6E73] dark:text-[#86868B]">
                                <span>[Bahan] + [Tenaga Kerja] + [Overhead]</span>
                                <span class="font-bold text-[#007AFF] dark:text-[#0A84FF]">= HPP Pokok</span>
                            </div>
                        </div>

                        <div
                            class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between text-sm font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                            <span>Mulai Hitung HPP</span>
                            <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </a>

                    <!-- 2. BEP (Anchor 2: Spans 1 col) -->
                    <a href="{{ route('kalkulator.bep') }}"
                        class="col-span-1 p-6 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-sm hover:border-[#34C759]/40 flex flex-col justify-between space-y-4 transition-all group">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div
                                    class="w-11 h-11 rounded-[14px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <i data-lucide="scale" class="w-5 h-5"></i>
                                </div>
                                <span class="text-xs font-semibold text-[#34C759] dark:text-[#30D158]">Titik Impas</span>
                            </div>

                            <div>
                                <h3
                                    class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#34C759] transition-colors">
                                    Kalkulator BEP
                                </h3>
                                <p class="text-xs sm:text-sm text-[#48484A] dark:text-[#AEAEB2] leading-relaxed mt-1">
                                    Ketahui minimal berapa porsi atau rupiah yang harus terjual per bulan agar usaha balik
                                    modal.
                                </p>
                            </div>
                        </div>

                        <div
                            class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between text-xs font-semibold text-[#34C759] dark:text-[#30D158]">
                            <span>Hitung BEP</span>
                            <i data-lucide="arrow-right"
                                class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </a>

                    <!-- 3. Harga Jual & Markup (Anchor 3: Spans 1 col) -->
                    <a href="{{ route('kalkulator.harga-jual') }}"
                        class="col-span-1 p-6 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-sm hover:border-[#FF9500]/40 flex flex-col justify-between space-y-4 transition-all group">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div
                                    class="w-11 h-11 rounded-[14px] bg-[#FF9500]/10 text-[#FF9500] dark:text-[#FF9F0A] flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <i data-lucide="tag" class="w-5 h-5"></i>
                                </div>
                                <span class="text-xs font-semibold text-[#FF9500] dark:text-[#FF9F0A]">Margin Laba</span>
                            </div>

                            <div>
                                <h3
                                    class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#FF9500] transition-colors">
                                    Margin Harga Jual
                                </h3>
                                <p class="text-xs sm:text-sm text-[#48484A] dark:text-[#AEAEB2] leading-relaxed mt-1">
                                    Pahami perbedaan markup dan margin agar persentase keuntungan tidak keliru dihitung.
                                </p>
                            </div>
                        </div>

                        <div
                            class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between text-xs font-semibold text-[#FF9500] dark:text-[#FF9F0A]">
                            <span>Buka Kalkulator</span>
                            <i data-lucide="arrow-right"
                                class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </a>

                    <!-- 4. Laba Bersih Riil (Spans 2 cols) -->
                    <a href="{{ route('kalkulator.laba-bersih') }}"
                        class="col-span-1 md:col-span-2 p-6 sm:p-8 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-sm hover:border-[#007AFF]/40 flex flex-col justify-between space-y-5 transition-all group">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div
                                    class="w-12 h-12 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <i data-lucide="pie-chart" class="w-6 h-6"></i>
                                </div>
                                <span
                                    class="text-xs font-bold uppercase tracking-wider text-[#34C759] dark:text-[#30D158] px-2.5 py-1 rounded-[8px] bg-[#34C759]/10">
                                    Laba Riil
                                </span>
                            </div>

                            <div>
                                <h3
                                    class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] transition-colors">
                                    Kalkulator Laba Bersih Usaha
                                </h3>
                                <p class="text-sm sm:text-base text-[#48484A] dark:text-[#AEAEB2] leading-relaxed mt-1.5">
                                    Simulasikan total omzet dikurangi seluruh beban operasional (gaji, sewa tempat, listrik,
                                    bahan) untuk mengetahui uang bersih yang benar-benar bisa dibawa pulang.
                                </p>
                            </div>

                            <div
                                class="p-3 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between text-xs font-mono text-[#6E6E73] dark:text-[#86868B]">
                                <span>Total Omzet - Biaya Operasional - Pajak</span>
                                <span class="font-bold text-[#34C759] dark:text-[#30D158]">= Uang Masuk Bersih</span>
                            </div>
                        </div>

                        <div
                            class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between text-sm font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                            <span>Audit Laba Bersih</span>
                            <i data-lucide="arrow-right"
                                class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </a>

                    <!-- 5. Gaji Karyawan (Spans 1 col) -->
                    <a href="{{ route('kalkulator.gaji-karyawan') }}"
                        class="col-span-1 p-6 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-sm hover:border-[#007AFF]/40 flex flex-col justify-between space-y-4 transition-all group">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div
                                    class="w-11 h-11 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <i data-lucide="users" class="w-5 h-5"></i>
                                </div>
                                <span class="text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF]">Payroll</span>
                            </div>

                            <div>
                                <h3
                                    class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] transition-colors">
                                    Gaji Karyawan
                                </h3>
                                <p class="text-xs sm:text-sm text-[#48484A] dark:text-[#AEAEB2] leading-relaxed mt-1">
                                    Perhitungan upah harian, bulanan, lembur, dan bonus karyawan dengan transparan.
                                </p>
                            </div>
                        </div>

                        <div
                            class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                            <span>Buka Tool</span>
                            <i data-lucide="arrow-right"
                                class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </a>

                    <!-- 6. PPh Final 0.5% (Spans 1 col) -->
                    <a href="{{ route('kalkulator.pph-final') }}"
                        class="col-span-1 p-6 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-sm hover:border-[#FF3B30]/40 flex flex-col justify-between space-y-4 transition-all group">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div
                                    class="w-11 h-11 rounded-[14px] bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A] flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <i data-lucide="receipt" class="w-5 h-5"></i>
                                </div>
                                <span class="text-xs font-semibold text-[#FF3B30] dark:text-[#FF453A]">Pajak PP 55</span>
                            </div>

                            <div>
                                <h3
                                    class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#FF3B30] transition-colors">
                                    PPh Final 0.5%
                                </h3>
                                <p class="text-xs sm:text-sm text-[#48484A] dark:text-[#AEAEB2] leading-relaxed mt-1">
                                    Simulasi kewajiban pajak dengan batas omzet tidak kena pajak hingga Rp 500 juta per
                                    tahun.
                                </p>
                            </div>
                        </div>

                        <div
                            class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between text-xs font-semibold text-[#FF3B30] dark:text-[#FF453A]">
                            <span>Hitung Pajak</span>
                            <i data-lucide="arrow-right"
                                class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </a>

                    <!-- 7. Target Omzet Harian (Spans 1 col) -->
                    <a href="{{ route('kalkulator.omzet-harian') }}"
                        class="col-span-1 p-6 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-sm hover:border-[#007AFF]/40 flex flex-col justify-between space-y-4 transition-all group">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div
                                    class="w-11 h-11 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <i data-lucide="target" class="w-5 h-5"></i>
                                </div>
                                <span class="text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF]">Target Harian</span>
                            </div>

                            <div>
                                <h3
                                    class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] transition-colors">
                                    Omzet Harian
                                </h3>
                                <p class="text-xs sm:text-sm text-[#48484A] dark:text-[#AEAEB2] leading-relaxed mt-1">
                                    Pecah target omzet bulanan menjadi target transaksi dan rata-rata struk kasir per hari.
                                </p>
                            </div>
                        </div>

                        <div
                            class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                            <span>Buka Tool</span>
                            <i data-lucide="arrow-right"
                                class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </a>

                    <!-- 8. Simulasi What-If (Spans 1 col) -->
                    <a href="{{ route('kalkulator.simulasi-what-if') }}"
                        class="col-span-1 p-6 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-sm hover:border-[#007AFF]/40 flex flex-col justify-between space-y-4 transition-all group">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div
                                    class="w-11 h-11 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <i data-lucide="sliders-horizontal" class="w-5 h-5"></i>
                                </div>
                                <span class="text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF]">Simulasi
                                    Skenario</span>
                            </div>

                            <div>
                                <h3
                                    class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] transition-colors">
                                    Simulasi What-If
                                </h3>
                                <p class="text-xs sm:text-sm text-[#48484A] dark:text-[#AEAEB2] leading-relaxed mt-1">
                                    Uji dampak jika harga bahan baku naik 10% atau volume penjualan turun terhadap sisa
                                    keuntungan.
                                </p>
                            </div>
                        </div>

                        <div
                            class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                            <span>Mulai Simulasi</span>
                            <i data-lucide="arrow-right"
                                class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </a>

                </div>
            </section>

            <!-- ═══ INSET CONVERSION BANNER (Calm Apple HIG Surface) ═══ -->
            <section
                class="p-8 sm:p-12 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-sm">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    <div class="lg:col-span-8 space-y-3 text-left">
                        <p class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                            Otomatisasi Penuh</p>
                        <h3 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">
                            Otomatiskan Seluruh Perhitungan Ini di Kasir Cooca
                        </h3>
                        <p class="text-sm sm:text-base text-[#48484A] dark:text-[#AEAEB2] leading-relaxed max-w-2xl">
                            Tidak perlu lagi menghitung satu per satu. Setiap transaksi kasir POS otomatis memotong stok
                            bahan baku, menghitung HPP secara riil, dan menyusun laporan laba rugi harian.
                        </p>
                    </div>
                    <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col gap-3">
                        <a href="{{ route('register') }}"
                            class="w-full py-4 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-base flex items-center justify-center gap-2 shadow-sm active:scale-[0.98] transition min-h-[48px]">
                            <span>Daftar Akun Gratis Sekarang</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            </section>

        </div>
    </div>
@endsection
