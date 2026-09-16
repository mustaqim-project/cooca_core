@extends('layouts.public_marketing')

@section('title', 'Kalkulator PPh Final UMKM 0.5% (PP 55/2022) Online Gratis | Cooca')
@section('description', 'Kalkulator simulasi pajak PPh Final 0.5% UMKM online gratis sesuai UU HPP dan PP 55/2022.
    Lengkap dengan perhitungan batas omzet Rp 500 juta bebas pajak per tahun.')
@section('keywords', 'kalkulator pph final 0.5, hitung pajak umkm online, pp 55 2022 pajak umkm, batas omzet 500 juta
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

            <!-- Header -->
            <div class="text-center max-w-2xl mx-auto space-y-3">
                <div
                    class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A] font-bold text-xs">
                    <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                    <span>Aturan Resmi PP 55/2022</span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">Kalkulator
                    PPh Final UMKM 0.5%</h1>
                <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] max-w-xl mx-auto leading-relaxed">
                    Ketahui kewajiban pajak Anda. Wajib Pajak Orang Pribadi berhak atas fasilitas omzet s.d <strong>Rp 500
                        Juta Bebas Pajak</strong> setiap tahun pajak.
                </p>
            </div>

            <!-- Calculator Interactive App (2-Column Bento System) -->
            <div class="glass-card p-6 sm:p-8 rounded-[28px]" x-data="{
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
                                class="inline-flex p-1 bg-black/[0.04] dark:bg-white/[0.06] rounded-full border border-black/[0.04] dark:border-white/[0.06] text-xs font-semibold w-full">
                                <button type="button" @click="taxpayerType = 'individual'"
                                    :class="taxpayerType === 'individual' ?
                                        'bg-white dark:bg-[#1C1C1E] text-[#1D1D1F] dark:text-[#F5F5F7] shadow-sm' :
                                        'text-[#6E6E73] dark:text-[#86868B]'"
                                    class="w-1/2 py-2 rounded-full transition-all">
                                    Orang Pribadi (Bebas s.d 500 Juta)
                                </button>
                                <button type="button" @click="taxpayerType = 'corporate'"
                                    :class="taxpayerType === 'corporate' ?
                                        'bg-white dark:bg-[#1C1C1E] text-[#1D1D1F] dark:text-[#F5F5F7] shadow-sm' :
                                        'text-[#6E6E73] dark:text-[#86868B]'"
                                    class="w-1/2 py-2 rounded-full transition-all">
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
                            <div class="flex justify-between text-[10px] text-[#6E6E73] dark:text-[#86868B] pt-1">
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
                                <span class="text-[10px] font-bold uppercase text-[#6E6E73] dark:text-[#86868B] block">Total
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
