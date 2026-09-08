@extends('layouts.public_marketing')

@section('title', 'Kalkulator BEP (Break Even Point) Online Gratis | Cooca UMKM')
@section('description', 'Kalkulator BEP (Titik Impas) online gratis untuk UMKM. Hitung berapa unit produk atau nominal rupiah omzet yang harus dicapai agar bisnis tidak merugi.')
@section('keywords', 'kalkulator bep, hitung titik impas online, rumus break even point rupiah, bep unit warung, kalkulator bep umkm')

@section('content')
<div class="pt-10 pb-20">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-slate-400 mb-6">
            <a href="{{ route('landing') }}" class="hover:text-white">Beranda</a>
            <span>/</span>
            <a href="{{ route('kalkulator.index') }}" class="hover:text-white">Kalkulator</a>
            <span>/</span>
            <span class="text-emerald-400 font-semibold">Kalkulator BEP</span>
        </nav>

        <!-- Header -->
        <div class="text-center max-w-2xl mx-auto mb-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 font-bold text-xs mb-3">
                <i data-lucide="scale" class="w-3.5 h-3.5"></i>
                <span>Titik Impas Bebas Rugi</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight">Kalkulator BEP (Break Even Point)</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-2">
                Ketahui batas minimal penjualan bulanan Anda. Penjualan di atas titik BEP adalah keuntungan murni bagi bisnis Anda.
            </p>
        </div>

        <!-- Calculator Interactive App -->
        <div class="glass-card p-6 sm:p-8 rounded-3xl mb-12" x-data="{
            fixedCost: 4500000,   // Biaya Tetap bulanan (Sewa, gaji pokok, wifi)
            pricePerUnit: 25000,  // Harga jual per porsi/unit
            varCostPerUnit: 13000,// Biaya variabel per unit (Bahan baku, cup)

            get contributionMargin() {
                return Math.max(0, this.pricePerUnit - this.varCostPerUnit);
            },
            get cmRatio() {
                if (this.pricePerUnit <= 0) return 0;
                return this.contributionMargin / this.pricePerUnit;
            },
            get bepUnits() {
                if (this.contributionMargin <= 0) return 0;
                return Math.ceil(this.fixedCost / this.contributionMargin);
            },
            get bepRevenue() {
                if (this.cmRatio <= 0) return 0;
                return Math.round(this.fixedCost / this.cmRatio);
            },
            get bepDailyUnits() {
                return Math.ceil(this.bepUnits / 30);
            },
            get bepDailyRevenue() {
                return Math.round(this.bepRevenue / 30);
            }
        }">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Left: Inputs -->
                <div class="lg:col-span-7 space-y-5">
                    <!-- 1. Biaya Tetap Bulanan -->
                    <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800">
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-xs font-bold text-emerald-400 uppercase tracking-wide">1. Total Biaya Tetap (Fixed Cost) / Bulan</label>
                            <span class="font-mono text-sm font-bold text-white">Rp <span x-text="Number(fixedCost).toLocaleString('id-ID')"></span></span>
                        </div>
                        <input type="number" x-model.number="fixedCost" class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white font-mono text-sm focus:border-emerald-500 focus:outline-none mb-2">
                        <input type="range" x-model.number="fixedCost" min="500000" max="30000000" step="250000" class="w-full accent-emerald-500">
                        <p class="text-[11px] text-slate-500 mt-1">Sewa tempat, gaji karyawan tetap, internet, retribusi toko.</p>
                    </div>

                    <!-- 2. Harga Jual Rata-rata per Unit -->
                    <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800">
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-xs font-bold text-indigo-400 uppercase tracking-wide">2. Rata-rata Harga Jual per Porsi/Unit</label>
                            <span class="font-mono text-sm font-bold text-white">Rp <span x-text="Number(pricePerUnit).toLocaleString('id-ID')"></span></span>
                        </div>
                        <input type="number" x-model.number="pricePerUnit" class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white font-mono text-sm focus:border-emerald-500 focus:outline-none mb-2">
                        <input type="range" x-model.number="pricePerUnit" min="1000" max="250000" step="1000" class="w-full accent-indigo-500">
                        <p class="text-[11px] text-slate-500 mt-1">Harga jual rata-rata produk atau menu andalan Anda.</p>
                    </div>

                    <!-- 3. Biaya Variabel per Unit -->
                    <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800">
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-xs font-bold text-amber-400 uppercase tracking-wide">3. Biaya Variabel (Bahan Baku) per Unit</label>
                            <span class="font-mono text-sm font-bold text-white">Rp <span x-text="Number(varCostPerUnit).toLocaleString('id-ID')"></span></span>
                        </div>
                        <input type="number" x-model.number="varCostPerUnit" class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white font-mono text-sm focus:border-emerald-500 focus:outline-none mb-2">
                        <input type="range" x-model.number="varCostPerUnit" min="500" max="150000" step="500" class="w-full accent-amber-500">
                        <p class="text-[11px] text-slate-500 mt-1">Modal bahan baku yang keluar hanya saat barang dibuat/terjual.</p>
                    </div>
                </div>

                <!-- Right: Summary Card -->
                <div class="lg:col-span-5 flex flex-col justify-between p-6 rounded-2xl bg-slate-950/90 border border-emerald-500/20">
                    <div class="space-y-6">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Target BEP Penjualan Bulanan</span>
                            <div class="text-3xl sm:text-4xl font-black text-emerald-400 font-mono mt-1">
                                Rp <span x-text="bepRevenue.toLocaleString('id-ID')"></span>
                            </div>
                            <div class="text-xs text-slate-400 mt-1">
                                Setara dengan: <strong class="text-white"><span x-text="bepUnits.toLocaleString('id-ID')"></span> unit / porsi</strong> per bulan.
                            </div>
                        </div>

                        <!-- Target Harian -->
                        <div class="p-4 rounded-xl bg-emerald-950/40 border border-emerald-500/30 space-y-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-300">Target Minimal Harian (30 Hari)</span>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-slate-300">Omzet Harian:</span>
                                <span class="font-mono font-bold text-white text-sm">Rp <span x-text="bepDailyRevenue.toLocaleString('id-ID')"></span></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-slate-300">Penjualan Harian:</span>
                                <span class="font-mono font-bold text-emerald-400 text-sm"><span x-text="bepDailyUnits"></span> unit/hari</span>
                            </div>
                        </div>

                        <!-- Margin Kontribusi -->
                        <div class="p-3 rounded-xl bg-slate-900 border border-slate-800">
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-400">Margin Kontribusi / Unit:</span>
                                <span class="font-mono font-bold text-white">Rp <span x-text="contributionMargin.toLocaleString('id-ID')"></span></span>
                            </div>
                            <div class="flex justify-between text-xs mt-1">
                                <span class="text-slate-400">Rasio Margin Kontribusi:</span>
                                <span class="font-mono font-bold text-indigo-400"><span x-text="Math.round(cmRatio * 100)"></span>%</span>
                            </div>
                        </div>
                    </div>

                    <!-- CTA -->
                    <div class="pt-6 border-t border-slate-800 mt-6 space-y-3">
                        <p class="text-[11px] text-slate-400 text-center">
                            Pantau posisi BEP Anda otomatis setiap hari di dashboard Cooca UMKM.
                        </p>
                        <a href="{{ route('register') }}" class="w-full glow-btn py-3 rounded-xl text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg">
                            <span>Mulai Sekarang - Gratis</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
