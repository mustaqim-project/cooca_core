@extends('layouts.public_marketing')

@section('title', 'Simulasi Bisnis What-If (Sensitivitas Biaya & Diskon) Online Gratis | Cooca UMKM')
@section('description', 'Alat simulasi What-If bisnis UMKM online. Uji skenario kenaikan harga bahan baku, kenaikan upah tenaga kerja, atau dampak pemberian diskon promo terhadap sisa keuntungan bersih.')
@section('keywords', 'simulasi bisnis what-if, kalkulator sensitivitas biaya, dampak diskon terhadap laba, skenario kenaikan bahan baku, simulator bisnis umkm online')

@section('content')
<div class="pt-10 pb-20">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-slate-400 mb-6">
            <a href="{{ route('landing') }}" class="hover:text-white">Beranda</a>
            <span>/</span>
            <a href="{{ route('kalkulator.index') }}" class="hover:text-white">Kalkulator</a>
            <span>/</span>
            <span class="text-cyan-400 font-semibold">Simulasi What-If</span>
        </nav>

        <!-- Header -->
        <div class="text-center max-w-2xl mx-auto mb-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 font-bold text-xs mb-3">
                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                <span>Decision Simulator</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight">Simulasi What-If Bisnis UMKM</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-2">
                Apa yang terjadi pada keuntungan Anda jika harga minyak/tepung naik 15%? Atau jika Anda memberi diskon promo 20%? Uji risikonya sebelum mengambil keputusan!
            </p>
        </div>

        <!-- Calculator Interactive App -->
        <div class="glass-card p-6 sm:p-8 rounded-3xl mb-12" x-data="{
            baseRevenue: 40000000,    // Omzet normal
            baseMaterial: 18000000,   // Biaya bahan baku normal
            baseFixedCost: 10000000,  // Biaya operasional & gaji normal

            // Skenario Perubahan (Slider delta)
            deltaMaterialPct: 10,     // Kenaikan bahan baku (%)
            discountPct: 0,           // Diskon promo (%)
            salesVolumeBoostPct: 0,   // Pertambahan volume pembeli akibat promo (%)

            // Keuntungan Baseline
            get baseProfit() {
                return this.baseRevenue - this.baseMaterial - this.baseFixedCost;
            },

            // Keuntungan Simulasi Baru
            get simRevenue() {
                const effectivePriceRatio = 1 - (this.discountPct / 100);
                const volumeMultiplier = 1 + (this.salesVolumeBoostPct / 100);
                return Math.round(this.baseRevenue * effectivePriceRatio * volumeMultiplier);
            },
            get simMaterial() {
                const materialPriceMultiplier = 1 + (this.deltaMaterialPct / 100);
                const volumeMultiplier = 1 + (this.salesVolumeBoostPct / 100);
                return Math.round(this.baseMaterial * materialPriceMultiplier * volumeMultiplier);
            },
            get simProfit() {
                return this.simRevenue - this.simMaterial - this.baseFixedCost;
            },
            get deltaProfit() {
                return this.simProfit - this.baseProfit;
            }
        }">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Left: Skenario Adjustments -->
                <div class="lg:col-span-7 space-y-5">
                    <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800">
                        <span class="text-xs font-bold text-white uppercase tracking-wide block mb-3">1. Kondisi Bisnis Normal Saat Ini</span>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                            <div class="p-2.5 rounded-xl bg-slate-900 border border-slate-800">
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Omzet Bulanan</span>
                                <span class="font-mono font-bold text-white text-sm">Rp <span x-text="Number(baseRevenue).toLocaleString('id-ID')"></span></span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-slate-900 border border-slate-800">
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Biaya Bahan</span>
                                <span class="font-mono font-bold text-amber-400 text-sm">Rp <span x-text="Number(baseMaterial).toLocaleString('id-ID')"></span></span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-slate-900 border border-slate-800">
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Laba Normal</span>
                                <span class="font-mono font-bold text-emerald-400 text-sm">Rp <span x-text="baseProfit.toLocaleString('id-ID')"></span></span>
                            </div>
                        </div>
                    </div>

                    <!-- Slider 1: Kenaikan Bahan Baku -->
                    <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800">
                        <div class="flex justify-between items-center mb-1">
                            <label class="text-xs font-bold text-amber-400 uppercase tracking-wide">Skenario Kenaikan Harga Bahan Baku</label>
                            <span class="font-mono text-sm font-bold text-amber-400">+<span x-text="deltaMaterialPct"></span>%</span>
                        </div>
                        <input type="range" x-model.number="deltaMaterialPct" min="0" max="50" step="5" class="w-full accent-amber-500">
                        <p class="text-[11px] text-slate-500 mt-1">Simulasikan dampak kenaikan harga beras, telur, minyak, kopi, atau sparepart.</p>
                    </div>

                    <!-- Slider 2: Diskon Promo -->
                    <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800">
                        <div class="flex justify-between items-center mb-1">
                            <label class="text-xs font-bold text-purple-400 uppercase tracking-wide">Skenario Diskon Promo Penjualan</label>
                            <span class="font-mono text-sm font-bold text-purple-400"><span x-text="discountPct"></span>% Diskon</span>
                        </div>
                        <input type="range" x-model.number="discountPct" min="0" max="40" step="5" class="w-full accent-purple-500">
                    </div>

                    <!-- Slider 3: Kenaikan Volume Pembeli Akibat Promo -->
                    <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800" x-show="discountPct > 0">
                        <div class="flex justify-between items-center mb-1">
                            <label class="text-xs font-bold text-cyan-400 uppercase tracking-wide">Estimasi Kenaikan Pembeli karena Diskon</label>
                            <span class="font-mono text-sm font-bold text-cyan-400">+<span x-text="salesVolumeBoostPct"></span>% Volume</span>
                        </div>
                        <input type="range" x-model.number="salesVolumeBoostPct" min="0" max="100" step="10" class="w-full accent-cyan-500">
                        <p class="text-[11px] text-slate-500 mt-1">Diskon biasanya menarik pembeli baru. Berapa perkiraan lonjakan transaksinya?</p>
                    </div>
                </div>

                <!-- Right: Simulation Output Card -->
                <div class="lg:col-span-5 p-6 rounded-2xl bg-slate-950/90 border border-cyan-500/30 flex flex-col justify-between">
                    <div class="space-y-4">
                        <span class="text-xs font-bold uppercase tracking-wider text-cyan-400 block">Hasil Simulasi Laba</span>

                        <!-- Perbandingan Laba -->
                        <div class="p-4 rounded-xl border" :class="simProfit >= baseProfit ? 'bg-emerald-950/30 border-emerald-500/40' : 'bg-rose-950/30 border-rose-500/40'">
                            <span class="text-[10px] font-bold uppercase text-slate-400 block">Laba Bersih Setelah Skenario</span>
                            <div class="text-2xl sm:text-3xl font-black font-mono mt-1" :class="simProfit >= 0 ? 'text-emerald-400' : 'text-rose-400'">
                                Rp <span x-text="simProfit.toLocaleString('id-ID')"></span>
                            </div>
                            <div class="text-xs mt-2 font-bold flex items-center gap-1.5" :class="deltaProfit >= 0 ? 'text-emerald-400' : 'text-rose-400'">
                                <i :data-lucide="deltaProfit >= 0 ? 'trending-up' : 'trending-down'" class="w-4 h-4"></i>
                                <span>Selisih: <span x-text="deltaProfit >= 0 ? '+' : ''"></span>Rp <span x-text="deltaProfit.toLocaleString('id-ID')"></span> dari kondisi awal</span>
                            </div>
                        </div>

                        <!-- Rincian Proyeksi Baru -->
                        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-2 text-xs">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Omzet Proyeksi:</span>
                                <span class="font-mono font-bold text-white">Rp <span x-text="simRevenue.toLocaleString('id-ID')"></span></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Beban Bahan Baru:</span>
                                <span class="font-mono text-amber-400">Rp <span x-text="simMaterial.toLocaleString('id-ID')"></span></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Beban Tetap:</span>
                                <span class="font-mono text-slate-300">Rp <span x-text="Number(baseFixedCost).toLocaleString('id-ID')"></span></span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-800 mt-6">
                        <a href="{{ route('register') }}" class="w-full glow-btn py-3 rounded-xl text-white font-bold text-xs flex items-center justify-center gap-2">
                            <span>Gunakan Simulasi Real-Time di Cooca</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
