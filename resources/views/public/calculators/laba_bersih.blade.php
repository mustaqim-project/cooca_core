@extends('layouts.public_marketing')

@section('title', 'Kalkulator Laba Bersih (Net Profit) Usaha Online Gratis | Cooca UMKM')
@section('description', 'Kalkulator simulasi laba bersih (net profit) UMKM online. Hitung pendapatan kotor, HPP barang terjual, biaya operasional, gaji, sewa, listrik, dan pajak untuk mengetahui laba bersih riil.')
@section('keywords', 'kalkulator laba bersih, rumus net profit margin umkm, hitung keuntungan usaha bulanan, laporan laba rugi sederhana, simulasi laba kotor bersih')

@section('content')
<div class="pt-10 pb-20">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-slate-400 mb-6">
            <a href="{{ route('landing') }}" class="hover:text-white">Beranda</a>
            <span>/</span>
            <a href="{{ route('kalkulator.index') }}" class="hover:text-white">Kalkulator</a>
            <span>/</span>
            <span class="text-blue-400 font-semibold">Kalkulator Laba Bersih</span>
        </nav>

        <!-- Header -->
        <div class="text-center max-w-2xl mx-auto mb-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 font-bold text-xs mb-3">
                <i data-lucide="pie-chart" class="w-3.5 h-3.5"></i>
                <span>Profitability Simulator</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight">Kalkulator Laba Bersih Usaha</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-2">
                Omzet besar belum tentu untung besar. Masukkan pendapatan dan seluruh pos pengeluaran Anda untuk melihat berapa rupiah uang yang benar-benar bisa dibawa pulang.
            </p>
        </div>

        <!-- Calculator Interactive App -->
        <div class="glass-card p-6 sm:p-8 rounded-3xl mb-12" x-data="{
            revenue: 35000000,     // Total Omzet bulanan
            cogsRate: 55,          // HPP dalam % dari omzet
            staffSalaries: 4000000,// Gaji Karyawan
            rentExpense: 2000000,  // Sewa tempat bulanan
            utilityExpense: 1200000, // Listrik, air, kuota, retribusi
            taxRate: 0.5,          // Pajak UMKM (PPh Final)

            get totalCogs() {
                return Math.round(this.revenue * (this.cogsRate / 100));
            },
            get grossProfit() {
                return this.revenue - this.totalCogs;
            },
            get totalOpex() {
                return (parseFloat(this.staffSalaries) || 0) + (parseFloat(this.rentExpense) || 0) + (parseFloat(this.utilityExpense) || 0);
            },
            get operatingProfit() {
                return this.grossProfit - this.totalOpex;
            },
            get taxAmount() {
                return Math.round(this.revenue * (this.taxRate / 100));
            },
            get netProfit() {
                return this.operatingProfit - this.taxAmount;
            },
            get netMargin() {
                if (this.revenue <= 0) return 0;
                return Math.round((this.netProfit / this.revenue) * 100);
            }
        }">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Left: Inputs -->
                <div class="lg:col-span-7 space-y-4">
                    <!-- 1. Omzet -->
                    <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800">
                        <div class="flex justify-between items-center mb-1">
                            <label class="text-xs font-bold text-white uppercase tracking-wide">1. Total Omzet (Pendapatan Bulanan)</label>
                            <span class="font-mono text-sm font-bold text-indigo-400">Rp <span x-text="Number(revenue).toLocaleString('id-ID')"></span></span>
                        </div>
                        <input type="range" x-model.number="revenue" min="5000000" max="250000000" step="1000000" class="w-full accent-indigo-500">
                    </div>

                    <!-- 2. HPP Rate -->
                    <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800">
                        <div class="flex justify-between items-center mb-1">
                            <label class="text-xs font-bold text-slate-300 uppercase tracking-wide">2. Estimasi HPP / Modal Bahan</label>
                            <span class="font-mono text-sm font-bold text-amber-400"><span x-text="cogsRate"></span>% (Rp <span x-text="totalCogs.toLocaleString('id-ID')"></span>)</span>
                        </div>
                        <input type="range" x-model.number="cogsRate" min="10" max="85" step="1" class="w-full accent-amber-500">
                        <p class="text-[11px] text-slate-500 mt-1">Standar F&B berkisar 30-40%, Retail kelontong 60-80%.</p>
                    </div>

                    <!-- 3. Biaya Operasional (Opex) -->
                    <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-3">
                        <span class="text-xs font-bold text-slate-300 uppercase tracking-wide block">3. Biaya Operasional (Opex)</span>

                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-slate-400">Total Gaji Staf:</span>
                                <span class="font-mono text-white">Rp <span x-text="Number(staffSalaries).toLocaleString('id-ID')"></span></span>
                            </div>
                            <input type="range" x-model.number="staffSalaries" min="0" max="30000000" step="500000" class="w-full accent-purple-500">
                        </div>

                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-slate-400">Sewa Tempat:</span>
                                <span class="font-mono text-white">Rp <span x-text="Number(rentExpense).toLocaleString('id-ID')"></span></span>
                            </div>
                            <input type="range" x-model.number="rentExpense" min="0" max="15000000" step="250000" class="w-full accent-emerald-500">
                        </div>

                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-slate-400">Listrik, Air, Wifi &amp; Lainnya:</span>
                                <span class="font-mono text-white">Rp <span x-text="Number(utilityExpense).toLocaleString('id-ID')"></span></span>
                            </div>
                            <input type="range" x-model.number="utilityExpense" min="100000" max="10000000" step="100000" class="w-full accent-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Right: Breakdown Waterfall -->
                <div class="lg:col-span-5 p-6 rounded-2xl bg-slate-950/90 border border-blue-500/30 flex flex-col justify-between">
                    <div class="space-y-4">
                        <span class="text-xs font-bold uppercase tracking-wider text-blue-400 block">Laporan Laba Rugi Mini</span>

                        <div class="space-y-2 text-xs border-b border-slate-800 pb-4">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Omzet Penjualan:</span>
                                <span class="font-mono font-bold text-white">Rp <span x-text="Number(revenue).toLocaleString('id-ID')"></span></span>
                            </div>
                            <div class="flex justify-between text-rose-400">
                                <span>- HPP (<span x-text="cogsRate"></span>%):</span>
                                <span class="font-mono">Rp <span x-text="totalCogs.toLocaleString('id-ID')"></span></span>
                            </div>
                            <div class="flex justify-between font-bold pt-1 border-t border-slate-800/80 text-white">
                                <span>= Laba Kotor (Gross Profit):</span>
                                <span class="font-mono text-emerald-400">Rp <span x-text="grossProfit.toLocaleString('id-ID')"></span></span>
                            </div>
                        </div>

                        <div class="space-y-2 text-xs border-b border-slate-800 pb-4">
                            <div class="flex justify-between text-rose-400">
                                <span>- Biaya Operasional (Opex):</span>
                                <span class="font-mono">Rp <span x-text="totalOpex.toLocaleString('id-ID')"></span></span>
                            </div>
                            <div class="flex justify-between text-rose-400">
                                <span>- Pajak PPh Final 0.5%:</span>
                                <span class="font-mono">Rp <span x-text="taxAmount.toLocaleString('id-ID')"></span></span>
                            </div>
                        </div>

                        <!-- Laba Bersih Final -->
                        <div class="p-4 rounded-xl bg-blue-950/30 border border-blue-500/30">
                            <span class="text-[10px] font-bold uppercase text-slate-400 block">Laba Bersih Akhir (Net Profit)</span>
                            <div class="text-2xl sm:text-3xl font-black font-mono mt-1" :class="netProfit >= 0 ? 'text-emerald-400' : 'text-rose-400'">
                                Rp <span x-text="netProfit.toLocaleString('id-ID')"></span>
                            </div>
                            <div class="text-xs text-slate-300 mt-1">
                                Net Profit Margin: <strong class="text-white" x-text="netMargin + '%'"></strong>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-800 mt-6">
                        <a href="{{ route('register') }}" class="w-full glow-btn py-3 rounded-xl text-white font-bold text-xs flex items-center justify-center gap-2">
                            <span>Otomatiskan Pembukuan Usaha</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
