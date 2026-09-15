@extends('layouts.public_marketing')

@section('title', 'Kalkulator Harga Jual (Markup vs Margin) Online Gratis | Cooca UMKM')
@section('description', 'Kalkulator penetapan harga jual produk online. Hitung perbandingan formula markup vs profit margin dan temukan harga jual psikologis (charm pricing) untuk meningkatkan penjualan.')
@section('keywords', 'kalkulator harga jual, hitung markup dan margin, rumus harga jual barang, kalkulator harga psikologis, pricing calculator umkm')

@section('content')
<div class="pt-8 pb-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-[#6E6E73] dark:text-[#86868B]">
            <a href="{{ route('landing') }}" class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Beranda</a>
            <span>/</span>
            <a href="{{ route('kalkulator.index') }}" class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Kalkulator</a>
            <span>/</span>
            <span class="text-[#FF9500] dark:text-[#FF9F0A] font-semibold">Kalkulator Harga Jual</span>
        </nav>

        <!-- Header -->
        <div class="text-center max-w-2xl mx-auto space-y-3">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#FF9500]/10 text-[#FF9500] dark:text-[#FF9F0A] font-bold text-xs">
                <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                <span>Pricing Strategy</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">Kalkulator Harga Jual &amp; Margin</h1>
            <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] max-w-xl mx-auto leading-relaxed">
                Jangan sampai tertukar antara Markup dan Margin. Masukkan modal dasar Anda untuk melihat opsi harga jual yang menguntungkan.
            </p>
        </div>

        <!-- Calculator Interactive App (2-Column Bento System) -->
        <div class="glass-card p-6 sm:p-8 rounded-[28px]" x-data="{
            costPrice: 50000,
            percent: 30,

            get markupPrice() {
                return Math.round(this.costPrice * (1 + (this.percent / 100)));
            },
            get markupProfit() {
                return this.markupPrice - this.costPrice;
            },
            get markupRealMargin() {
                if (this.markupPrice <= 0) return 0;
                return Math.round((this.markupProfit / this.markupPrice) * 100);
            },

            get marginPrice() {
                if (this.percent >= 100) return 0;
                return Math.round(this.costPrice / (1 - (this.percent / 100)));
            },
            get marginProfit() {
                return this.marginPrice - this.costPrice;
            },
            get marginRealMarkup() {
                if (this.costPrice <= 0) return 0;
                return Math.round((this.marginProfit / this.costPrice) * 100);
            },

            get charmPrice() {
                const p = this.marginPrice;
                return Math.floor(p / 1000) * 1000 + 900;
            }
        }">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- Left: Apple Inset Input Controls (6 Kolom) -->
                <div class="lg:col-span-6 space-y-4">
                    <div class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                        <label class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] uppercase tracking-wide block">Modal Barang (HPP / Biaya Kulakan)</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-2.5 text-[#6E6E73] dark:text-[#86868B] font-mono text-sm">Rp</span>
                            <input type="number" x-model.number="costPrice" class="w-full h-11 pl-10 pr-3.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[#1D1D1F] dark:text-[#F5F5F7] font-mono text-base focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all">
                        </div>
                        <input type="range" x-model.number="costPrice" min="5000" max="500000" step="1000" class="w-full accent-[#007AFF] cursor-pointer">
                    </div>

                    <div class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] uppercase tracking-wide">Target Persentase Keuntungan</label>
                            <span class="font-mono text-base font-bold text-[#007AFF] dark:text-[#0A84FF]" x-text="percent + '%'"></span>
                        </div>
                        <input type="range" x-model.number="percent" min="5" max="80" step="1" class="w-full accent-[#007AFF] cursor-pointer">
                    </div>

                    <!-- Perbandingan Cepat -->
                    <div class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-1.5 text-xs text-[#6E6E73] dark:text-[#86868B]">
                        <div class="font-bold text-[#1D1D1F] dark:text-[#F5F5F7] flex items-center gap-1.5">
                            <i data-lucide="info" class="w-4 h-4 text-[#007AFF]"></i>
                            <span>Catatan Finansial:</span>
                        </div>
                        <p>
                            Jika Anda menargetkan <strong>Margin</strong>, harga jual akan lebih tinggi daripada <strong>Markup</strong> untuk menutup biaya operasional dan ruang diskon promosi.
                        </p>
                    </div>
                </div>

                <!-- Right: Comparison Bento Cards (6 Kolom) -->
                <div class="lg:col-span-6 space-y-4">
                    <!-- Skenario 1: Metode Margin (Direkomendasikan) -->
                    <div class="p-5 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] relative shadow-sm">
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] font-bold text-[10px] uppercase mb-2">
                            <i data-lucide="check" class="w-3 h-3"></i>
                            <span>Metode Margin (Direkomendasikan)</span>
                        </div>
                        <div class="text-xs text-[#6E6E73] dark:text-[#86868B]">Harga Jual Ideal:</div>
                        <div class="text-2xl sm:text-3xl font-black text-[#34C759] dark:text-[#30D158] font-mono mt-0.5">
                            Rp <span x-text="marginPrice.toLocaleString('id-ID')"></span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-3 pt-3 border-t border-black/[0.04] dark:border-white/[0.06] text-xs">
                            <div>Nominal Laba: <strong class="text-[#1D1D1F] dark:text-[#F5F5F7] font-mono">Rp <span x-text="marginProfit.toLocaleString('id-ID')"></span></strong></div>
                            <div>Setara Markup: <strong class="text-[#007AFF] dark:text-[#0A84FF] font-mono"><span x-text="marginRealMarkup"></span>%</strong></div>
                        </div>
                    </div>

                    <!-- Skenario 2: Metode Markup -->
                    <div class="p-5 rounded-[22px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06]">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-[#6E6E73] dark:text-[#86868B] mb-1">Metode Markup Konvensional</div>
                        <div class="text-xs text-[#6E6E73] dark:text-[#86868B]">Harga Jual:</div>
                        <div class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] font-mono mt-0.5">
                            Rp <span x-text="markupPrice.toLocaleString('id-ID')"></span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-3 pt-3 border-t border-black/[0.04] dark:border-white/[0.06] text-xs">
                            <div>Nominal Laba: <strong class="text-[#1D1D1F] dark:text-[#F5F5F7] font-mono">Rp <span x-text="markupProfit.toLocaleString('id-ID')"></span></strong></div>
                            <div>Margin Riil: <strong class="text-[#FF9500] dark:text-[#FF9F0A] font-mono"><span x-text="markupRealMargin"></span>%</strong></div>
                        </div>
                    </div>

                    <!-- Charm Pricing -->
                    <div class="p-4 rounded-[18px] bg-[#FF9500]/10 border border-[#FF9500]/20 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-[#FF9500] dark:text-[#FF9F0A] uppercase tracking-wider block">Harga Psikologis (Charm Price)</span>
                            <span class="text-xs text-[#6E6E73] dark:text-[#86868B]">Akhiran 900 terbukti meningkatkan konversi penjualan</span>
                        </div>
                        <span class="font-mono text-lg font-black text-[#FF9500] dark:text-[#FF9F0A]">Rp <span x-text="charmPrice.toLocaleString('id-ID')"></span></span>
                    </div>

                    <!-- Conversion CTA -->
                    <div class="pt-3">
                        <a href="{{ route('register') }}" class="w-full glow-btn py-3.5 rounded-[14px] text-white font-semibold text-xs flex items-center justify-center gap-2 active:scale-[0.98] transition-transform">
                            <span>Pasang Harga Ini di Kasir Cooca</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
