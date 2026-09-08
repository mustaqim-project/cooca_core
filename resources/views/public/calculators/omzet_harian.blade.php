@extends('layouts.public_marketing')

@section('title', 'Kalkulator Target Omzet Harian & Jumlah Transaksi Online | Cooca UMKM')
@section('description', 'Kalkulator target omzet harian online untuk toko dan kafe. Pecah target omzet bulanan menjadi target omzet harian, jumlah transaksi pembeli, dan nilai keranjang belanja rata-rata.')
@section('keywords', 'kalkulator omzet harian, hitung target penjualan bulanan, average order value kasir, target transaksi toko, sales target breakdown')

@section('content')
<div class="pt-10 pb-20">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-slate-400 mb-6">
            <a href="{{ route('landing') }}" class="hover:text-white">Beranda</a>
            <span>/</span>
            <a href="{{ route('kalkulator.index') }}" class="hover:text-white">Kalkulator</a>
            <span>/</span>
            <span class="text-cyan-400 font-semibold">Kalkulator Target Omzet</span>
        </nav>

        <!-- Header -->
        <div class="text-center max-w-2xl mx-auto mb-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 font-bold text-xs mb-3">
                <i data-lucide="target" class="w-3.5 h-3.5"></i>
                <span>Sales Goal Breakdown</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight">Kalkulator Target Omzet Harian</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-2">
                Jangan biarkan target bulanan terasa mustahil. Pecah menjadi target riil per hari dan per transaksi pelanggan.
            </p>
        </div>

        <!-- Calculator Interactive App -->
        <div class="glass-card p-6 sm:p-8 rounded-3xl mb-12" x-data="{
            monthlyTarget: 45000000,
            openDays: 26,
            averageTicket: 35000,

            get dailyRevenueTarget() {
                if (this.openDays <= 0) return 0;
                return Math.round(this.monthlyTarget / this.openDays);
            },
            get dailyTransactionsNeeded() {
                if (this.averageTicket <= 0) return 0;
                return Math.ceil(this.dailyRevenueTarget / this.averageTicket);
            },
            get hourlyTransactionsNeeded() {
                // Asumsi buka 10 jam sehari
                return Math.ceil(this.dailyTransactionsNeeded / 10);
            }
        }">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Left: Inputs -->
                <div class="lg:col-span-7 space-y-5">
                    <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800">
                        <div class="flex justify-between items-center mb-1">
                            <label class="text-xs font-bold text-white uppercase tracking-wide">Target Omzet Bulanan</label>
                            <span class="font-mono text-sm font-bold text-cyan-400">Rp <span x-text="Number(monthlyTarget).toLocaleString('id-ID')"></span></span>
                        </div>
                        <input type="range" x-model.number="monthlyTarget" min="5000000" max="250000000" step="1000000" class="w-full accent-cyan-500">
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800">
                        <div class="flex justify-between items-center mb-1">
                            <label class="text-xs font-bold text-slate-300 uppercase tracking-wide">Jumlah Hari Buka Toko per Bulan</label>
                            <span class="font-mono text-sm font-bold text-white"><span x-text="openDays"></span> Hari</span>
                        </div>
                        <input type="range" x-model.number="openDays" min="15" max="31" step="1" class="w-full accent-cyan-500">
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800">
                        <div class="flex justify-between items-center mb-1">
                            <label class="text-xs font-bold text-slate-300 uppercase tracking-wide">Rata-rata Belanja per Pembeli (Basket Size)</label>
                            <span class="font-mono text-sm font-bold text-white">Rp <span x-text="Number(averageTicket).toLocaleString('id-ID')"></span></span>
                        </div>
                        <input type="range" x-model.number="averageTicket" min="5000" max="200000" step="5000" class="w-full accent-cyan-500">
                    </div>
                </div>

                <!-- Right: Summary Card -->
                <div class="lg:col-span-5 p-6 rounded-2xl bg-slate-950/90 border border-cyan-500/30 flex flex-col justify-between">
                    <div class="space-y-4">
                        <span class="text-xs font-bold uppercase tracking-wider text-cyan-400 block">Target Penjualan Harian</span>

                        <div class="p-4 rounded-xl bg-cyan-950/30 border border-cyan-500/30">
                            <span class="text-[10px] font-bold uppercase text-slate-400 block">Target Omzet per Hari Buka</span>
                            <div class="text-3xl font-black text-cyan-400 font-mono mt-1">
                                Rp <span x-text="dailyRevenueTarget.toLocaleString('id-ID')"></span>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-slate-400">Transaksi Diperlukan:</span>
                                <span class="font-mono font-bold text-white text-base"><span x-text="dailyTransactionsNeeded"></span> struk / hari</span>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t border-slate-800">
                                <span class="text-xs text-slate-400">Rata-rata per Jam (10 Jam):</span>
                                <span class="font-mono font-bold text-emerald-400 text-sm">~<span x-text="hourlyTransactionsNeeded"></span> pembeli / jam</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-800 mt-6">
                        <a href="{{ route('register') }}" class="w-full glow-btn py-3 rounded-xl text-white font-bold text-xs flex items-center justify-center gap-2">
                            <span>Mulai Pantau Omzet Toko Gratis</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
