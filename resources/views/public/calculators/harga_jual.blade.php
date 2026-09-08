@extends('layouts.public_marketing')

@section('title', 'Kalkulator Harga Jual (Markup vs Margin) Online Gratis | Cooca UMKM')
@section('description', 'Kalkulator penetapan harga jual produk online. Hitung perbandingan formula markup vs profit margin dan temukan harga jual psikologis (charm pricing) untuk meningkatkan penjualan.')
@section('keywords', 'kalkulator harga jual, hitung markup dan margin, rumus harga jual barang, kalkulator harga psikologis, pricing calculator umkm')

@section('content')
<div class="pt-10 pb-20">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-slate-400 mb-6">
            <a href="{{ route('landing') }}" class="hover:text-white">Beranda</a>
            <span>/</span>
            <a href="{{ route('kalkulator.index') }}" class="hover:text-white">Kalkulator</a>
            <span>/</span>
            <span class="text-amber-400 font-semibold">Kalkulator Harga Jual</span>
        </nav>

        <!-- Header -->
        <div class="text-center max-w-2xl mx-auto mb-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 font-bold text-xs mb-3">
                <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                <span>Pricing Strategy</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight">Kalkulator Harga Jual &amp; Margin</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-2">
                Jangan sampai tertukar antara Markup dan Margin. Masukkan modal dasar Anda untuk melihat opsi harga jual yang menguntungkan.
            </p>
        </div>

        <!-- Calculator Interactive App -->
        <div class="glass-card p-6 sm:p-8 rounded-3xl mb-12" x-data="{
            costPrice: 50000,
            percent: 30,

            // Hitungan jika percent dianggap Markup
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

            // Hitungan jika percent dianggap Margin
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

            // Harga psikologis (Charm pricing akhiran 900 / 500)
            get charmPrice() {
                const p = this.marginPrice;
                return Math.floor(p / 1000) * 1000 + 900;
            }
        }">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Left: Inputs -->
                <div class="lg:col-span-6 space-y-6">
                    <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800">
                        <label class="text-xs font-bold text-slate-300 uppercase tracking-wide block mb-2">Modal Barang (HPP / Biaya Kulakan)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-slate-400 font-mono text-sm">Rp</span>
                            <input type="number" x-model.number="costPrice" class="w-full pl-10 pr-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white font-mono text-base focus:border-amber-500 focus:outline-none">
                        </div>
                        <input type="range" x-model.number="costPrice" min="5000" max="500000" step="1000" class="w-full accent-amber-500 mt-3">
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800">
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-xs font-bold text-slate-300 uppercase tracking-wide">Target Persentase Keuntungan</label>
                            <span class="font-mono text-base font-bold text-amber-400" x-text="percent + '%'"></span>
                        </div>
                        <input type="range" x-model.number="percent" min="5" max="80" step="1" class="w-full accent-amber-500">
                    </div>

                    <!-- Perbandingan Cepat -->
                    <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-3 text-xs text-slate-400">
                        <div class="font-bold text-white flex items-center gap-2">
                            <i data-lucide="info" class="w-4 h-4 text-amber-400"></i>
                            <span>Catatan Penting:</span>
                        </div>
                        <p>
                            Jika Anda menargetkan <strong>{{-- percent --}} Margin</strong>, harga jual harus lebih tinggi daripada <strong>Markup</strong> untuk menutup biaya operasional dan diskon.
                        </p>
                    </div>
                </div>

                <!-- Right: Comparison Cards -->
                <div class="lg:col-span-6 space-y-4">
                    <!-- Skenario 1: Metode Margin (Direkomendasikan) -->
                    <div class="p-5 rounded-2xl bg-indigo-950/40 border border-indigo-500/40 relative">
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 font-bold text-[10px] uppercase mb-2">
                            <i data-lucide="check" class="w-3 h-3"></i>
                            <span>Metode Margin (Direkomendasikan)</span>
                        </div>
                        <div class="text-xs text-slate-400">Harga Jual Ideal:</div>
                        <div class="text-2xl sm:text-3xl font-black text-emerald-400 font-mono mt-0.5">
                            Rp <span x-text="marginPrice.toLocaleString('id-ID')"></span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-3 pt-3 border-t border-indigo-500/20 text-xs">
                            <div>Nominal Laba: <strong class="text-white font-mono">Rp <span x-text="marginProfit.toLocaleString('id-ID')"></span></strong></div>
                            <div>Setara Markup: <strong class="text-indigo-400 font-mono"><span x-text="marginRealMarkup"></span>%</strong></div>
                        </div>
                    </div>

                    <!-- Skenario 2: Metode Markup -->
                    <div class="p-5 rounded-2xl bg-slate-950/80 border border-slate-800">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Metode Markup Konvensional</div>
                        <div class="text-xs text-slate-400">Harga Jual:</div>
                        <div class="text-xl sm:text-2xl font-black text-white font-mono mt-0.5">
                            Rp <span x-text="markupPrice.toLocaleString('id-ID')"></span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-3 pt-3 border-t border-slate-800 text-xs">
                            <div>Nominal Laba: <strong class="text-white font-mono">Rp <span x-text="markupProfit.toLocaleString('id-ID')"></span></strong></div>
                            <div>Margin Riil: <strong class="text-amber-400 font-mono"><span x-text="markupRealMargin"></span>%</strong></div>
                        </div>
                    </div>

                    <!-- Charm Pricing -->
                    <div class="p-4 rounded-xl bg-amber-950/20 border border-amber-500/30 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-amber-400 uppercase tracking-wider block">Harga Psikologis (Charm Price)</span>
                            <span class="text-xs text-slate-400">Akhiran 900 terbukti meningkatkan konversi beli</span>
                        </div>
                        <span class="font-mono text-lg font-black text-amber-300">Rp <span x-text="charmPrice.toLocaleString('id-ID')"></span></span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
