@extends('layouts.public_marketing')

@section('title', 'Koleksi 8 Kalkulator Bisnis UMKM Online Gratis | Cooca')
@section('description', 'Koleksi kalkulator bisnis gratis untuk UMKM: Kalkulator HPP, BEP Titik Impas, Margin Harga
    Jual, Laba Bersih, Gaji Karyawan, PPh Final 0.5%, dan Simulasi What-If.')
@section('keywords', 'kalkulator bisnis umkm, kalkulator hpp online, kalkulator bep gratis, hitung harga jual margin,
    simulasi laba rugi, kalkulator pph 0.5')

@section('content')
    <div class="pt-10 pb-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

            <!-- Header -->
            <div class="text-center max-w-3xl mx-auto space-y-3">
                <div
                    class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-bold text-xs">
                    <i data-lucide="calculator" class="w-4 h-4"></i>
                    <span>Interactive Business Tools</span>
                </div>
                <h1
                    class="text-3xl sm:text-5xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-tight">
                    Koleksi <span class="text-[#007AFF] dark:text-[#0A84FF]">Kalkulator Bisnis</span> UMKM
                </h1>
                <p class="text-sm sm:text-base text-[#6E6E73] dark:text-[#86868B] max-w-2xl mx-auto leading-relaxed">
                    Ambil keputusan bisnis lebih cepat, akurat, dan berbasis data standar akuntansi. Semua tools dapat Anda
                    gunakan secara instan tanpa perlu registrasi atau berlangganan.
                </p>
            </div>

            <!-- Bento Grid 8 Kalkulator (Apple Asymmetric Bento Layout) -->
            <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6">

                <!-- 1. HPP (Hero Bento: Spans 2-col on Mobile, 2-col on Desktop) -->
                <a href="{{ route('kalkulator.hpp') }}"
                    class="col-span-2 lg:col-span-2 glass-card p-5 sm:p-7 rounded-[24px] sm:rounded-[28px] group flex flex-col justify-between hover:border-[#007AFF]/40 active:scale-[0.98] transition-all">
                    <div class="space-y-3.5">
                        <div class="flex items-center justify-between">
                            <div
                                class="w-10 h-10 sm:w-12 sm:h-12 rounded-[16px] sm:rounded-[18px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center group-hover:scale-105 transition-transform">
                                <i data-lucide="layers" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                            </div>
                            <span
                                class="px-2.5 sm:px-3 py-1 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] text-[10px] sm:text-[11px] font-bold uppercase tracking-wider flex items-center gap-1">
                                <i data-lucide="star" class="w-3 h-3"></i>
                                <span>Paling Populer • 3-Pilar</span>
                            </span>
                        </div>

                        <div>
                            <h2
                                class="text-base sm:text-xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors leading-snug">
                                Kalkulator HPP &amp; Harga Jual
                            </h2>
                            <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed mt-1">
                                Hitung biaya bahan baku, upah kerja, dan overhead per porsi untuk mengunci modal aman.
                            </p>
                        </div>

                        <!-- Apple Inset Formula Pill -->
                        <div
                            class="p-2.5 sm:p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between text-[10px] sm:text-xs font-mono text-[#6E6E73] dark:text-[#86868B]">
                            <span>[Bahan] + [Tenaga Kerja] + [Overhead]</span>
                            <span class="font-bold text-[#007AFF] dark:text-[#0A84FF]">= HPP Murni</span>
                        </div>
                    </div>

                    <div
                        class="mt-4 sm:mt-5 pt-3 sm:pt-4 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between text-xs font-bold text-[#007AFF] dark:text-[#0A84FF]">
                        <span>Hitung HPP Sekarang</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>

                <!-- 2. BEP (Compact Bento Widget: Spans 1-col) -->
                <a href="{{ route('kalkulator.bep') }}"
                    class="col-span-1 lg:col-span-1 glass-card p-4 sm:p-6 rounded-[22px] sm:rounded-[26px] group flex flex-col justify-between hover:border-[#34C759]/40 active:scale-[0.98] transition-all">
                    <div class="space-y-2.5 sm:space-y-3">
                        <div class="flex items-center justify-between">
                            <div
                                class="w-9 h-9 sm:w-11 sm:h-11 rounded-[14px] sm:rounded-[16px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center group-hover:scale-105 transition-transform">
                                <i data-lucide="scale" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                            </div>
                            <span
                                class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-[#34C759] dark:text-[#30D158] px-2 py-0.5 rounded-full bg-[#34C759]/10">Titik
                                Impas</span>
                        </div>
                        <div>
                            <h2
                                class="text-xs sm:text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#34C759] dark:group-hover:text-[#30D158] transition-colors leading-snug">
                                Kalkulator BEP</h2>
                            <p class="text-[10px] sm:text-xs text-[#6E6E73] dark:text-[#86868B] mt-0.5 hidden sm:block">
                                Target unit &amp; omzet impas anti-rugi.</p>
                        </div>
                        <div
                            class="p-2 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.04] text-[10px] font-mono text-[#34C759] dark:text-[#30D158] font-semibold text-center truncate">
                            Unit &amp; Rupiah
                        </div>
                    </div>
                    <div
                        class="mt-3 sm:mt-5 pt-2.5 sm:pt-4 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between text-[11px] sm:text-xs font-bold text-[#34C759] dark:text-[#30D158]">
                        <span>Buka Tool</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>

                <!-- 3. Harga Jual (Compact Bento Widget: Spans 1-col) -->
                <a href="{{ route('kalkulator.harga-jual') }}"
                    class="col-span-1 lg:col-span-1 glass-card p-4 sm:p-6 rounded-[22px] sm:rounded-[26px] group flex flex-col justify-between hover:border-[#FF9500]/40 active:scale-[0.98] transition-all">
                    <div class="space-y-2.5 sm:space-y-3">
                        <div class="flex items-center justify-between">
                            <div
                                class="w-9 h-9 sm:w-11 sm:h-11 rounded-[14px] sm:rounded-[16px] bg-[#FF9500]/10 text-[#FF9500] dark:text-[#FF9F0A] flex items-center justify-center group-hover:scale-105 transition-transform">
                                <i data-lucide="tag" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                            </div>
                            <span
                                class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-[#FF9500] dark:text-[#FF9F0A] px-2 py-0.5 rounded-full bg-[#FF9500]/10">Margin</span>
                        </div>
                        <div>
                            <h2
                                class="text-xs sm:text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#FF9500] dark:group-hover:text-[#FF9F0A] transition-colors leading-snug">
                                Harga Jual</h2>
                            <p class="text-[10px] sm:text-xs text-[#6E6E73] dark:text-[#86868B] mt-0.5 hidden sm:block">
                                Perbandingan markup vs margin kotor.</p>
                        </div>
                        <div
                            class="p-2 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.04] text-[10px] font-mono text-[#FF9500] dark:text-[#FF9F0A] font-semibold text-center truncate">
                            Markup vs Margin
                        </div>
                    </div>
                    <div
                        class="mt-3 sm:mt-5 pt-2.5 sm:pt-4 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between text-[11px] sm:text-xs font-bold text-[#FF9500] dark:text-[#FF9F0A]">
                        <span>Buka Tool</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>

                <!-- 4. Laba Bersih (Hero Bento: Spans 2-col on Mobile, 2-col on Desktop) -->
                <a href="{{ route('kalkulator.laba-bersih') }}"
                    class="col-span-2 lg:col-span-2 glass-card p-5 sm:p-7 rounded-[24px] sm:rounded-[28px] group flex flex-col justify-between hover:border-[#007AFF]/40 active:scale-[0.98] transition-all">
                    <div class="space-y-3.5">
                        <div class="flex items-center justify-between">
                            <div
                                class="w-10 h-10 sm:w-12 sm:h-12 rounded-[16px] sm:rounded-[18px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center group-hover:scale-105 transition-transform">
                                <i data-lucide="pie-chart" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                            </div>
                            <span
                                class="px-2.5 sm:px-3 py-1 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] text-[10px] sm:text-[11px] font-bold uppercase tracking-wider">
                                Waterfall Finansial
                            </span>
                        </div>

                        <div>
                            <h2
                                class="text-base sm:text-xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors leading-snug">
                                Kalkulator Laba Bersih &amp; Rugi Riil
                            </h2>
                            <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed mt-1">
                                Simulasikan omzet, beban operasional, gaji, listrik, dan pajak hingga menemukan laba bersih
                                riil.
                            </p>
                        </div>

                        <!-- Apple Inset Waterfall Pill -->
                        <div
                            class="p-2.5 sm:p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between text-[10px] sm:text-xs font-mono text-[#6E6E73] dark:text-[#86868B]">
                            <span>Omzet - HPP - Operasional - Pajak</span>
                            <span class="font-bold text-[#34C759] dark:text-[#30D158]">= Net Profit</span>
                        </div>
                    </div>

                    <div
                        class="mt-4 sm:mt-5 pt-3 sm:pt-4 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between text-xs font-bold text-[#007AFF] dark:text-[#0A84FF]">
                        <span>Audit Laba Bersih</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>

                <!-- 5. Gaji Karyawan (Compact Bento Widget: Spans 1-col) -->
                <a href="{{ route('kalkulator.gaji-karyawan') }}"
                    class="col-span-1 lg:col-span-1 glass-card p-4 sm:p-6 rounded-[22px] sm:rounded-[26px] group flex flex-col justify-between hover:border-[#007AFF]/40 active:scale-[0.98] transition-all">
                    <div class="space-y-2.5 sm:space-y-3">
                        <div class="flex items-center justify-between">
                            <div
                                class="w-9 h-9 sm:w-11 sm:h-11 rounded-[14px] sm:rounded-[16px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center group-hover:scale-105 transition-transform">
                                <i data-lucide="users" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                            </div>
                            <span
                                class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF] px-2 py-0.5 rounded-full bg-[#007AFF]/10">Payroll</span>
                        </div>
                        <div>
                            <h2
                                class="text-xs sm:text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors leading-snug">
                                Gaji Karyawan</h2>
                            <p class="text-[10px] sm:text-xs text-[#6E6E73] dark:text-[#86868B] mt-0.5 hidden sm:block">Upah
                                harian, bulanan &amp; tunjangan.</p>
                        </div>
                        <div
                            class="p-2 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.04] text-[10px] font-mono text-[#007AFF] dark:text-[#0A84FF] font-semibold text-center truncate">
                            Lembur &amp; Bonus
                        </div>
                    </div>
                    <div
                        class="mt-3 sm:mt-5 pt-2.5 sm:pt-4 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between text-[11px] sm:text-xs font-bold text-[#007AFF] dark:text-[#0A84FF]">
                        <span>Buka Tool</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>

                <!-- 6. PPh Final 0.5% (Compact Bento Widget: Spans 1-col) -->
                <a href="{{ route('kalkulator.pph-final') }}"
                    class="col-span-1 lg:col-span-1 glass-card p-4 sm:p-6 rounded-[22px] sm:rounded-[26px] group flex flex-col justify-between hover:border-[#FF3B30]/40 active:scale-[0.98] transition-all">
                    <div class="space-y-2.5 sm:space-y-3">
                        <div class="flex items-center justify-between">
                            <div
                                class="w-9 h-9 sm:w-11 sm:h-11 rounded-[14px] sm:rounded-[16px] bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A] flex items-center justify-center group-hover:scale-105 transition-transform">
                                <i data-lucide="receipt" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                            </div>
                            <span
                                class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-[#FF3B30] dark:text-[#FF453A] px-2 py-0.5 rounded-full bg-[#FF3B30]/10">PP
                                55</span>
                        </div>
                        <div>
                            <h2
                                class="text-xs sm:text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#FF3B30] dark:group-hover:text-[#FF453A] transition-colors leading-snug">
                                PPh Final 0.5%</h2>
                            <p class="text-[10px] sm:text-xs text-[#6E6E73] dark:text-[#86868B] mt-0.5 hidden sm:block">
                                Pajak UMKM dengan batas 500 juta.</p>
                        </div>
                        <div
                            class="p-2 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.04] text-[10px] font-mono text-[#FF3B30] dark:text-[#FF453A] font-semibold text-center truncate">
                            Bebas &lt; 500Jt
                        </div>
                    </div>
                    <div
                        class="mt-3 sm:mt-5 pt-2.5 sm:pt-4 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between text-[11px] sm:text-xs font-bold text-[#FF3B30] dark:text-[#FF453A]">
                        <span>Buka Tool</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>

                <!-- 7. Target Omzet Harian (Compact Bento Widget: Spans 1-col) -->
                <a href="{{ route('kalkulator.omzet-harian') }}"
                    class="col-span-1 lg:col-span-1 glass-card p-4 sm:p-6 rounded-[22px] sm:rounded-[26px] group flex flex-col justify-between hover:border-[#007AFF]/40 active:scale-[0.98] transition-all">
                    <div class="space-y-2.5 sm:space-y-3">
                        <div class="flex items-center justify-between">
                            <div
                                class="w-9 h-9 sm:w-11 sm:h-11 rounded-[14px] sm:rounded-[16px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center group-hover:scale-105 transition-transform">
                                <i data-lucide="target" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                            </div>
                            <span
                                class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF] px-2 py-0.5 rounded-full bg-[#007AFF]/10">Target</span>
                        </div>
                        <div>
                            <h2
                                class="text-xs sm:text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors leading-snug">
                                Omzet Harian</h2>
                            <p class="text-[10px] sm:text-xs text-[#6E6E73] dark:text-[#86868B] mt-0.5 hidden sm:block">
                                Target transaksi harian &amp; struk kasir.</p>
                        </div>
                        <div
                            class="p-2 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.04] text-[10px] font-mono text-[#007AFF] dark:text-[#0A84FF] font-semibold text-center truncate">
                            AOV &amp; Struk
                        </div>
                    </div>
                    <div
                        class="mt-3 sm:mt-5 pt-2.5 sm:pt-4 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between text-[11px] sm:text-xs font-bold text-[#007AFF] dark:text-[#0A84FF]">
                        <span>Buka Tool</span>
                        <i data-lucide="arrow-right"
                            class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>

                <!-- 8. What-If Simulation (Compact / Accent Bento Widget: Spans 1-col on Mobile, 1-col on Desktop) -->
                <a href="{{ route('kalkulator.simulasi-what-if') }}"
                    class="col-span-1 lg:col-span-1 glass-card p-4 sm:p-6 rounded-[22px] sm:rounded-[26px] group flex flex-col justify-between hover:border-[#007AFF]/40 active:scale-[0.98] transition-all">
                    <div class="space-y-2.5 sm:space-y-3">
                        <div class="flex items-center justify-between">
                            <div
                                class="w-9 h-9 sm:w-11 sm:h-11 rounded-[14px] sm:rounded-[16px] bg-[#007AFF]/15 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center group-hover:scale-105 transition-transform">
                                <i data-lucide="sparkles" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                            </div>
                            <span
                                class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF] px-2 py-0.5 rounded-full bg-[#007AFF]/10">AI
                                Tool</span>
                        </div>
                        <div>
                            <h2
                                class="text-xs sm:text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors leading-snug">
                                Simulasi What-If</h2>
                            <p class="text-[10px] sm:text-xs text-[#6E6E73] dark:text-[#86868B] mt-0.5 hidden sm:block">
                                Dampak kenaikan bahan baku terhadap laba.</p>
                        </div>
                        <div
                            class="p-2 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.04] text-[10px] font-mono text-[#007AFF] dark:text-[#0A84FF] font-semibold text-center truncate">
                            Sensitivitas Biaya
                        </div>
                    </div>
                    <div
                        class="mt-3 sm:mt-5 pt-2.5 sm:pt-4 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between text-[11px] sm:text-xs font-bold text-[#007AFF] dark:text-[#0A84FF]">
                        <span>Mulai Simulasi</span>
                        <i data-lucide="arrow-right"
                            class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>

            </div>

            <!-- Inset Conversion Card (Clean Apple Inset - No Purple Gradient) -->
            <div
                class="p-8 sm:p-12 rounded-[28px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] flex flex-col lg:flex-row items-center justify-between gap-8 shadow-sm">
                <div class="space-y-3 max-w-xl text-center lg:text-left">
                    <div
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] text-xs font-bold uppercase tracking-wider">
                        <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                        <span>Otomasi Tanpa Ribet</span>
                    </div>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7]">Otomatiskan
                        Perhitungan di Kasir Anda</h3>
                    <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                        Setiap transaksi kasir otomatis memotong stok bahan, menghitung HPP riil, dan menyusun laporan laba
                        rugi. Bebas biaya selamanya tanpa kartu kredit.
                    </p>
                </div>
                <a href="{{ route('register') }}"
                    class="glow-btn px-8 py-3.5 rounded-[16px] text-white font-semibold text-xs flex items-center gap-2 shrink-0 active:scale-[0.98] transition-transform">
                    <span>Daftar Akun Gratis Sekarang</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

        </div>
    </div>
@endsection
