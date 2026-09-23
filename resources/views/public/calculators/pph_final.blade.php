@extends('layouts.public_marketing')

@section('title', 'Kalkulator PPh Final UMKM 0.5% (PP 55/2022) Online Gratis | Cooca')
@section('description',
    'Kalkulator simulasi pajak PPh Final 0.5% UMKM online gratis sesuai UU HPP dan PP 55/2022.
    Lengkap dengan perhitungan batas omzet Rp 500 juta bebas pajak per tahun.')
@section('keywords',
    'kalkulator pph final 0.5, hitung pajak umkm online, pp 55 2022 pajak umkm, batas omzet 500 juta
    bebas pajak, cara setor pph final bulanan')

@section('content')
    <div class="pt-8 pb-24">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-xs text-[#6E6E73] dark:text-[#86868B]">
                <a href="{{ route('landing') }}"
                    class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Beranda</a>
                <span>/</span>
                <a href="{{ route('kalkulator.index') }}"
                    class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Kalkulator</a>
                <span>/</span>
                <span class="text-[#FF3B30] dark:text-[#FF453A] font-semibold">Kalkulator PPh Final</span>
            </nav>

            <!-- ═══ HERO SECTION (Mandatory 2-Grid Layout) ═══ -->
            <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

                <!-- KIRI: Headline & Penjelasan (7 Cols) -->
                <div class="lg:col-span-7 space-y-5 text-left">
                    <div class="space-y-2">
                        <p
                            class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#FF3B30] dark:text-[#FF453A]">
                            Aturan Pajak Resmi PP 55/2022
                        </p>
                        <h1
                            class="text-4xl sm:text-5xl lg:text-[3.25rem] xl:text-[3.75rem] font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-[1.15]">
                            Kalkulator PPh Final <span class="text-[#FF3B30] dark:text-[#FF453A]">UMKM 0.5%</span>
                        </h1>
                    </div>

                    <p class="text-base sm:text-lg text-[#48484A] dark:text-[#AEAEB2] leading-relaxed max-w-xl font-normal">
                        Ketahui kewajiban pajak resmi Anda tanpa salah hitung. Pemilik usaha perseorangan berhak atas
                        fasilitas pembebasan pajak untuk omzet hingga <strong>Rp 500 Juta pertama</strong> per tahun.
                    </p>

                    <!-- Reassurance Points for UMKM 40-65 -->
                    <div
                        class="pt-1 flex flex-wrap items-center gap-y-2 gap-x-5 text-xs text-[#6E6E73] dark:text-[#86868B]">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i>
                            <span>Bebas Pajak s.d Omzet Rp 500 Juta</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i>
                            <span>Tarif Ringan 0.5% dari Kelebihan Omzet</span>
                        </div>
                    </div>
                </div>

                <!-- KANAN: Visual Formula Preview Card (5 Cols) -->
                <div class="lg:col-span-5">
                    <div
                        class="bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[24px] shadow-sm p-5 sm:p-6 space-y-4">
                        <div
                            class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#FF5F56] border border-black/10"></span>
                                <span class="w-3 h-3 rounded-full bg-[#FFBD2E] border border-black/10"></span>
                                <span class="w-3 h-3 rounded-full bg-[#27C93F] border border-black/10"></span>
                            </div>
                            <span class="text-xs font-semibold text-[#8E8E93] dark:text-[#98989D]">Fasilitas PP
                                55/2022</span>
                            <div class="w-6"></div>
                        </div>

                        <div class="space-y-2.5 text-xs text-[#48484A] dark:text-[#AEAEB2]">
                            <div
                                class="flex items-center justify-between p-2.5 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E]">
                                <span class="font-medium">Omzet Kumulatif s.d 500 Juta</span>
                                <span class="font-mono font-bold text-[#34C759] dark:text-[#30D158]">Tarif 0% (Bebas)</span>
                            </div>
                            <div
                                class="flex items-center justify-between p-2.5 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E]">
                                <span class="font-medium">Omzet di Atas 500 Juta</span>
                                <span class="font-mono font-bold text-[#FF3B30] dark:text-[#FF453A]">Tarif 0.5%</span>
                            </div>
                        </div>

                        <div
                            class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between text-xs">
                            <span class="text-[#6E6E73] dark:text-[#86868B]">Badan Usaha (PT/CV):</span>
                            <span class="font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Langsung 0.5%</span>
                        </div>
                    </div>
                </div>

            </section>

            <!-- ═══ CALCULATOR INTERACTIVE APP ═══ -->
            <div class="bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-sm p-6 sm:p-8 rounded-[24px]"
                x-data="{
                    taxpayerType: 'individual',
                    cumulativePriorRevenue: 350000000,
                    currentMonthRevenue: 45000000,
                
                    get newCumulative() {
                        return (parseFloat(this.cumulativePriorRevenue) || 0) + (parseFloat(this.currentMonthRevenue) || 0);
                    },
                    get taxableRevenue() {
                        if (this.taxpayerType === 'corporate') {
                            return parseFloat(this.currentMonthRevenue) || 0;
                        }
                        const threshold = 500000000;
                        const prior = parseFloat(this.cumulativePriorRevenue) || 0;
                        const curr = parseFloat(this.currentMonthRevenue) || 0;
                        const total = prior + curr;
                
                        if (total <= threshold) {
                            return 0;
                        } else if (prior < threshold && total > threshold) {
                            return total - threshold;
                        } else {
                            return curr;
                        }
                    },
                    get taxDue() {
                        return Math.round(this.taxableRevenue * 0.005);
                    }
                }">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    <!-- Left: Apple Inset Input Controls (7 Kolom) -->
                    <div class="lg:col-span-7 space-y-4">
                        <!-- Segmented Switcher -->
                        <div
                            class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                            <label
                                class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] uppercase tracking-wide block">Bentuk
                                Usaha / Wajib Pajak</label>
                            <div
                                class="inline-flex p-1 bg-black/[0.04] dark:bg-white/[0.06] rounded-[12px] border border-black/[0.04] dark:border-white/[0.06] text-xs font-semibold w-full">
                                <button type="button" @click="taxpayerType = 'individual'"
                                    :class="taxpayerType === 'individual' ?
                                        'bg-white dark:bg-[#1C1C1E] text-[#1D1D1F] dark:text-[#F5F5F7] shadow-sm' :
                                        'text-[#6E6E73] dark:text-[#86868B]'"
                                    class="w-1/2 py-2 rounded-[10px] transition-all">
                                    Orang Pribadi (Bebas s.d 500 Juta)
                                </button>
                                <button type="button" @click="taxpayerType = 'corporate'"
                                    :class="taxpayerType === 'corporate' ?
                                        'bg-white dark:bg-[#1C1C1E] text-[#1D1D1F] dark:text-[#F5F5F7] shadow-sm' :
                                        'text-[#6E6E73] dark:text-[#86868B]'"
                                    class="w-1/2 py-2 rounded-[10px] transition-all">
                                    Badan (CV / PT - Tarif 0.5%)
                                </button>
                            </div>
                        </div>

                        <!-- Omzet Kumulatif Sebelumnya (Khusus Orang Pribadi) -->
                        <div x-show="taxpayerType === 'individual'"
                            class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                            <div class="flex justify-between items-center">
                                <label
                                    class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] uppercase tracking-wide">Omzet
                                    Kumulatif s.d Bulan Lalu</label>
                                <span class="font-mono text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Rp <span
                                        x-text="Number(cumulativePriorRevenue).toLocaleString('id-ID')"></span></span>
                            </div>
                            <input type="range" x-model.number="cumulativePriorRevenue" min="0" max="600000000"
                                step="5000000" class="w-full accent-[#007AFF] cursor-pointer">
                            <div class="flex justify-between text-xs text-[#6E6E73] dark:text-[#86868B] pt-1">
                                <span>Rp 0</span>
                                <span class="text-[#34C759] dark:text-[#30D158] font-bold">Batas Bebas: Rp 500 Juta</span>
                                <span>Rp 600 Juta+</span>
                            </div>
                        </div>

                        <!-- Omzet Bulan Ini -->
                        <div
                            class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                            <div class="flex justify-between items-center">
                                <label
                                    class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] uppercase tracking-wide">Omzet
                                    Penjualan Bulan Ini</label>
                                <span class="font-mono text-sm font-bold text-[#007AFF] dark:text-[#0A84FF]">Rp <span
                                        x-text="Number(currentMonthRevenue).toLocaleString('id-ID')"></span></span>
                            </div>
                            <input type="range" x-model.number="currentMonthRevenue" min="1000000" max="150000000"
                                step="1000000" class="w-full accent-[#007AFF] cursor-pointer">
                        </div>
                    </div>

                    <!-- Right: Sticky Bento Output Card (5 Kolom) -->
                    <div class="lg:col-span-5 sticky top-24 space-y-5">
                        <div
                            class="glass-card p-6 sm:p-7 rounded-[26px] space-y-5 bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                            <span
                                class="text-xs font-bold uppercase tracking-wider text-[#FF3B30] dark:text-[#FF453A] block">Kewajiban
                                Setor PPh Final Bulan Ini</span>

                            <div class="p-4 rounded-[18px] bg-[#FF3B30]/10 border border-[#FF3B30]/20">
                                <span class="text-xs font-bold uppercase text-[#6E6E73] dark:text-[#86868B] block">Total
                                    Pajak Terutang (0.5%)</span>
                                <div class="text-3xl font-black text-[#FF3B30] dark:text-[#FF453A] font-mono mt-1">
                                    Rp <span x-text="taxDue.toLocaleString('id-ID')"></span>
                                </div>
                                <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B] mt-2 leading-relaxed">
                                    <span x-show="taxDue === 0" class="text-[#34C759] dark:text-[#30D158] font-bold">Omzet
                                        Anda masih berada dalam batas bebas pajak fasilitas PP 55/2022.</span>
                                    <span x-show="taxDue > 0">Disetor paling lambat tanggal 15 bulan berikutnya melalui kode
                                        billing resmi DJP 411128-420.</span>
                                </p>
                            </div>

                            <div class="space-y-2 text-xs border-t border-black/[0.04] dark:border-white/[0.06] pt-3">
                                <div class="flex justify-between text-[#6E6E73] dark:text-[#86868B]">
                                    <span>Omzet Kena Pajak (DPP):</span>
                                    <span class="font-mono font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Rp <span
                                            x-text="taxableRevenue.toLocaleString('id-ID')"></span></span>
                                </div>
                                <div class="flex justify-between text-[#6E6E73] dark:text-[#86868B]"
                                    x-show="taxpayerType === 'individual'">
                                    <span>Total Akumulasi Omzet Tahun Ini:</span>
                                    <span class="font-mono text-[#1D1D1F] dark:text-[#F5F5F7] font-semibold">Rp <span
                                            x-text="newCumulative.toLocaleString('id-ID')"></span></span>
                                </div>
                            </div>

                            <div class="pt-2">
                                <a href="{{ route('register') }}"
                                    class="w-full glow-btn py-3.5 rounded-[14px] text-white font-semibold text-xs flex items-center justify-center gap-2 active:scale-[0.98] transition-transform">
                                    <span>Buka Akun Kasir Otomatis</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
