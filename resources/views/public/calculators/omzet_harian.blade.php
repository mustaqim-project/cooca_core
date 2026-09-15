@extends('layouts.public_marketing')

@section('title', 'Kalkulator Target Omzet Harian & Jumlah Transaksi Online | Cooca UMKM')
@section('description', 'Kalkulator target omzet harian online untuk toko dan kafe. Pecah target omzet bulanan menjadi target omzet harian, jumlah transaksi pembeli, dan nilai keranjang belanja rata-rata.')
@section('keywords', 'kalkulator omzet harian, hitung target penjualan bulanan, average order value kasir, target transaksi toko, sales target breakdown')

@section('content')
<div class="pt-8 pb-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-[#6E6E73] dark:text-[#86868B]">
            <a href="{{ route('landing') }}" class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Beranda</a>
            <span>/</span>
            <a href="{{ route('kalkulator.index') }}" class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Kalkulator</a>
            <span>/</span>
            <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold">Kalkulator Target Omzet</span>
        </nav>

        <!-- Header -->
        <div class="text-center max-w-2xl mx-auto space-y-3">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-bold text-xs">
                <i data-lucide="target" class="w-3.5 h-3.5"></i>
                <span>Sales Goal Breakdown</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">Kalkulator Target Omzet Harian</h1>
            <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] max-w-xl mx-auto leading-relaxed">
                Jangan biarkan target bulanan terasa mustahil. Pecah menjadi target riil per hari dan per transaksi pelanggan.
            </p>
        </div>

        <!-- Calculator Interactive App (2-Column Bento System) -->
        <div class="glass-card p-6 sm:p-8 rounded-[28px]" x-data="{
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
                return Math.ceil(this.dailyTransactionsNeeded / 10);
            }
        }">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- Left: Apple Inset Inputs (7 Kolom) -->
                <div class="lg:col-span-7 space-y-4">
                    <div class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] uppercase tracking-wide">Target Omzet Bulanan</label>
                            <span class="font-mono text-sm font-bold text-[#007AFF] dark:text-[#0A84FF]">Rp <span x-text="Number(monthlyTarget).toLocaleString('id-ID')"></span></span>
                        </div>
                        <input type="range" x-model.number="monthlyTarget" min="5000000" max="250000000" step="1000000" class="w-full accent-[#007AFF] cursor-pointer">
                    </div>

                    <div class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] uppercase tracking-wide">Jumlah Hari Buka Toko per Bulan</label>
                            <span class="font-mono text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]"><span x-text="openDays"></span> Hari</span>
                        </div>
                        <input type="range" x-model.number="openDays" min="15" max="31" step="1" class="w-full accent-[#007AFF] cursor-pointer">
                    </div>

                    <div class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] uppercase tracking-wide">Rata-rata Belanja per Pembeli (Basket Size)</label>
                            <span class="font-mono text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Rp <span x-text="Number(averageTicket).toLocaleString('id-ID')"></span></span>
                        </div>
                        <input type="range" x-model.number="averageTicket" min="5000" max="200000" step="5000" class="w-full accent-[#007AFF] cursor-pointer">
                    </div>
                </div>

                <!-- Right: Sticky Bento Output Card (5 Kolom) -->
                <div class="lg:col-span-5 sticky top-24 space-y-5">
                    <div class="glass-card p-6 sm:p-7 rounded-[26px] space-y-5 bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                        <span class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF] block">Target Penjualan Harian</span>

                        <div class="p-4 rounded-[18px] bg-[#007AFF]/10 border border-[#007AFF]/20">
                            <span class="text-[10px] font-bold uppercase text-[#6E6E73] dark:text-[#86868B] block">Target Omzet per Hari Buka</span>
                            <div class="text-3xl font-black text-[#007AFF] dark:text-[#0A84FF] font-mono mt-1">
                                Rp <span x-text="dailyRevenueTarget.toLocaleString('id-ID')"></span>
                            </div>
                        </div>

                        <div class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-[#6E6E73] dark:text-[#86868B]">Transaksi Diperlukan:</span>
                                <span class="font-mono font-bold text-[#1D1D1F] dark:text-[#F5F5F7] text-base"><span x-text="dailyTransactionsNeeded"></span> struk / hari</span>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t border-black/[0.04] dark:border-white/[0.06] text-xs">
                                <span class="text-[#6E6E73] dark:text-[#86868B]">Rata-rata per Jam (10 Jam):</span>
                                <span class="font-mono font-bold text-[#34C759] dark:text-[#30D158] text-sm">~<span x-text="hourlyTransactionsNeeded"></span> pembeli / jam</span>
                            </div>
                        </div>

                        <div class="pt-2">
                            <a href="{{ route('register') }}" class="w-full glow-btn py-3.5 rounded-[14px] text-white font-semibold text-xs flex items-center justify-center gap-2 active:scale-[0.98] transition-transform">
                                <span>Mulai Pantau Omzet Toko Gratis</span>
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
