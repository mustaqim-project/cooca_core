@extends('layouts.public_marketing')

@section('title', 'Kalkulator HPP & Harga Jual Online Gratis | Cooca UMKM')
@section('description', 'Kalkulator HPP (Harga Pokok Penjualan) 3-Pilar online gratis. Hitung biaya bahan baku, upah tenaga kerja, biaya overhead, dan tentukan target markup atau margin keuntungan secara instan.')
@section('keywords', 'kalkulator hpp, hitung harga pokok penjualan online, rumus hpp makanan, kalkulator margin keuntungan, hitung harga jual f&b')

@section('content')
<div class="pt-10 pb-20">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-slate-400 mb-6">
            <a href="{{ route('landing') }}" class="hover:text-white">Beranda</a>
            <span>/</span>
            <a href="{{ route('kalkulator.index') }}" class="hover:text-white">Kalkulator</a>
            <span>/</span>
            <span class="text-indigo-400 font-semibold">Kalkulator HPP</span>
        </nav>

        <!-- Header -->
        <div class="text-center max-w-2xl mx-auto mb-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 font-bold text-xs mb-3">
                <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                <span>Metode 3-Pilar Standar Finansial</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight">Kalkulator HPP &amp; Harga Jual Instan</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-2">
                Masukkan komponen biaya bahan baku, alokasi upah, dan beban overhead untuk menemukan Harga Pokok Penjualan (HPP) murni serta harga jual rekomendasi.
            </p>
        </div>

        <!-- Calculator Interactive App -->
        <div class="glass-card p-6 sm:p-8 rounded-3xl mb-12" x-data="{
            matCost: 25000,
            labCost: 6500,
            ovhCost: 8500,
            targetRate: 40,
            calcMode: 'margin', // 'margin' or 'markup'

            get totalHpp() {
                return (parseFloat(this.matCost) || 0) + (parseFloat(this.labCost) || 0) + (parseFloat(this.ovhCost) || 0);
            },
            get sellingPrice() {
                const hpp = this.totalHpp;
                const rate = parseFloat(this.targetRate) || 0;
                if (this.calcMode === 'margin') {
                    if (rate >= 100) return 0;
                    return Math.round(hpp / (1 - (rate / 100)));
                } else {
                    return Math.round(hpp * (1 + (rate / 100)));
                }
            },
            get profitNominal() {
                return this.sellingPrice - this.totalHpp;
            },
            get marginPercent() {
                if (this.sellingPrice <= 0) return 0;
                return Math.round((this.profitNominal / this.sellingPrice) * 100);
            },
            get markupPercent() {
                if (this.totalHpp <= 0) return 0;
                return Math.round((this.profitNominal / this.totalHpp) * 100);
            }
        }">
            <!-- Mode Switcher -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-5 mb-6">
                <div>
                    <h2 class="text-lg font-bold text-white">Simulasi Biaya &amp; Penetapan Laba</h2>
                    <p class="text-xs text-slate-400">Pilih apakah Anda ingin menghitung berdasarkan Margin atau Markup.</p>
                </div>
                <div class="flex items-center p-1 bg-slate-950 rounded-xl border border-slate-800 text-xs shrink-0">
                    <button type="button" @click="calcMode = 'margin'" :class="calcMode === 'margin' ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-400'" class="px-4 py-1.5 rounded-lg font-bold transition-all">
                        Target Margin (%)
                    </button>
                    <button type="button" @click="calcMode = 'markup'" :class="calcMode === 'markup' ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-400'" class="px-4 py-1.5 rounded-lg font-bold transition-all">
                        Target Markup (%)
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Left: Inputs -->
                <div class="lg:col-span-7 space-y-5">
                    <!-- 1. Bahan Baku -->
                    <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800">
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-xs font-bold text-blue-400 uppercase tracking-wide">1. Biaya Bahan Baku (Material)</label>
                            <span class="font-mono text-sm font-bold text-white">Rp <span x-text="Number(matCost).toLocaleString('id-ID')"></span></span>
                        </div>
                        <input type="number" x-model.number="matCost" class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white font-mono text-sm focus:border-indigo-500 focus:outline-none mb-2">
                        <input type="range" x-model.number="matCost" min="1000" max="150000" step="500" class="w-full accent-blue-500">
                        <p class="text-[11px] text-slate-500 mt-1">Bahan utama + bumbu + kemasan langsung per unit produk.</p>
                    </div>

                    <!-- 2. Tenaga Kerja -->
                    <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800">
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-xs font-bold text-purple-400 uppercase tracking-wide">2. Biaya Tenaga Kerja Langsung</label>
                            <span class="font-mono text-sm font-bold text-white">Rp <span x-text="Number(labCost).toLocaleString('id-ID')"></span></span>
                        </div>
                        <input type="number" x-model.number="labCost" class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white font-mono text-sm focus:border-indigo-500 focus:outline-none mb-2">
                        <input type="range" x-model.number="labCost" min="0" max="80000" step="500" class="w-full accent-purple-500">
                        <p class="text-[11px] text-slate-500 mt-1">Upah per porsi / alokasi waktu masak atau pembuatan barang.</p>
                    </div>

                    <!-- 3. Overhead -->
                    <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800">
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-xs font-bold text-emerald-400 uppercase tracking-wide">3. Biaya Overhead &amp; Operasional</label>
                            <span class="font-mono text-sm font-bold text-white">Rp <span x-text="Number(ovhCost).toLocaleString('id-ID')"></span></span>
                        </div>
                        <input type="number" x-model.number="ovhCost" class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white font-mono text-sm focus:border-indigo-500 focus:outline-none mb-2">
                        <input type="range" x-model.number="ovhCost" min="0" max="60000" step="500" class="w-full accent-emerald-500">
                        <p class="text-[11px] text-slate-500 mt-1">Alokasi gas, listrik, sewa tempat, dan plastik per porsi.</p>
                    </div>

                    <!-- 4. Target Profit Rate -->
                    <div class="p-4 rounded-2xl bg-slate-950/80 border border-indigo-500/30">
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-xs font-bold text-indigo-400 uppercase tracking-wide">
                                Target <span x-text="calcMode === 'margin' ? 'Gross Margin' : 'Markup'"></span>
                            </label>
                            <span class="font-mono text-sm font-bold text-indigo-400" x-text="targetRate + '%'"></span>
                        </div>
                        <input type="range" x-model.number="targetRate" min="5" max="90" step="1" class="w-full accent-indigo-500">
                    </div>
                </div>

                <!-- Right: Summary Card -->
                <div class="lg:col-span-5 flex flex-col justify-between p-6 rounded-2xl bg-slate-950/90 border border-indigo-500/20">
                    <div class="space-y-6">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total HPP per Unit</span>
                            <div class="text-2xl sm:text-3xl font-black text-white font-mono mt-1">
                                Rp <span x-text="totalHpp.toLocaleString('id-ID')"></span>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl bg-indigo-950/40 border border-indigo-500/30">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-300">Rekomendasi Harga Jual</span>
                            <div class="text-3xl sm:text-4xl font-black text-emerald-400 font-mono mt-1">
                                Rp <span x-text="sellingPrice.toLocaleString('id-ID')"></span>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-1">
                                Laba kotor: <strong class="text-white">Rp <span x-text="profitNominal.toLocaleString('id-ID')"></span></strong> per produk.
                            </div>
                        </div>

                        <!-- Metrics Comparison -->
                        <div class="grid grid-cols-2 gap-3 pt-2 border-t border-slate-800">
                            <div class="p-3 rounded-xl bg-slate-900 border border-slate-800">
                                <span class="text-[10px] text-slate-400 uppercase font-bold block">Gross Margin</span>
                                <span class="text-base font-extrabold text-white font-mono" x-text="marginPercent + '%'"></span>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-900 border border-slate-800">
                                <span class="text-[10px] text-slate-400 uppercase font-bold block">Markup Rate</span>
                                <span class="text-base font-extrabold text-white font-mono" x-text="markupPercent + '%'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Direct Conversion to Cooca UMKM -->
                    <div class="pt-6 border-t border-slate-800 mt-6 space-y-3">
                        <p class="text-[11px] text-slate-400 text-center">
                            Ingin HPP resep langsung terpotong saat kasir input pesanan?
                        </p>
                        <a href="{{ route('register') }}" class="w-full glow-btn py-3 rounded-xl text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg">
                            <span>Buka Akun Kasir Gratis</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Educational Section -->
        <div class="space-y-8 text-slate-300 text-sm leading-relaxed border-t border-slate-800 pt-12">
            <div>
                <h3 class="text-xl font-bold text-white mb-3">Apa itu HPP (Harga Pokok Penjualan)?</h3>
                <p>
                    Harga Pokok Penjualan (HPP) atau <em>Cost of Goods Sold (COGS)</em> adalah total biaya langsung yang dikeluarkan untuk memproduksi atau membeli barang dagangan hingga siap dijual kepada konsumen. Mengetahui HPP dengan akurat adalah syarat mutlak agar Anda tidak menjual rugi saat memberikan diskon atau promo.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800">
                    <h4 class="font-bold text-indigo-400 mb-2">Perbedaan Margin vs Markup</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        <strong>Markup</strong> adalah persentase laba yang ditambahkan di atas HPP: <code>(Harga Jual - HPP) / HPP</code>.<br><br>
                        <strong>Margin</strong> adalah persentase laba dari total harga jual: <code>(Harga Jual - HPP) / Harga Jual</code>.<br><br>
                        <em>Contoh:</em> HPP Rp 10.000 dijual Rp 15.000 memiliki Markup 50%, namun Margin-nya adalah 33.3%.
                    </p>
                </div>
                <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800">
                    <h4 class="font-bold text-emerald-400 mb-2">Rumus 3-Pilar Cooca UMKM</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Cooca membagi HPP menjadi 3 pilar standar akuntansi manufaktur:<br>
                        1. <strong>Material:</strong> Bahan mentah dan kemasan.<br>
                        2. <strong>Labor:</strong> Upah pekerja per satuan output.<br>
                        3. <strong>Overhead:</strong> Beban pendukung seperti listrik, gas, air, dan penyusutan alat.
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
