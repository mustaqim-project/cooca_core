@extends('layouts.public_marketing')

@section('title', 'Kalkulator HPP & Harga Jual Online Gratis | Cooca UMKM')
@section('description', 'Kalkulator HPP (Harga Pokok Penjualan) 3-Pilar online gratis. Hitung biaya bahan baku, upah tenaga kerja, biaya overhead, dan tentukan target markup atau margin keuntungan secara instan.')
@section('keywords', 'kalkulator hpp, hitung harga pokok penjualan online, rumus hpp makanan, kalkulator margin keuntungan, hitung harga jual f&b')

@section('content')
<div class="pt-8 pb-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

        <!-- Breadcrumbs (Apple Inset Style) -->
        <nav class="flex items-center gap-2 text-xs text-[#6E6E73] dark:text-[#86868B]">
            <a href="{{ route('landing') }}" class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Beranda</a>
            <span>/</span>
            <a href="{{ route('kalkulator.index') }}" class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Kalkulator</a>
            <span>/</span>
            <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold">Kalkulator HPP</span>
        </nav>

        <!-- Header -->
        <div class="text-center max-w-2xl mx-auto space-y-3">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-bold text-xs">
                <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                <span>Metode 3-Pilar Standar Finansial</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">Kalkulator HPP &amp; Harga Jual Instan</h1>
            <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] max-w-xl mx-auto leading-relaxed">
                Masukkan biaya bahan baku, alokasi upah, dan overhead untuk menemukan Harga Pokok Penjualan (HPP) murni serta harga jual rekomendasi.
            </p>
        </div>

        <!-- Calculator Interactive App (2-Column Bento System) -->
        <div class="glass-card p-6 sm:p-8 rounded-[28px]" x-data="{
            matCost: 25000,
            labCost: 6500,
            ovhCost: 8500,
            targetRate: 40,
            calcMode: 'margin',

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
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-black/[0.06] dark:border-white/[0.08] pb-5 mb-6">
                <div>
                    <h2 class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Simulasi Biaya &amp; Penetapan Laba</h2>
                    <p class="text-xs text-[#6E6E73] dark:text-[#86868B]">Pilih apakah Anda ingin menghitung berdasarkan Margin atau Markup.</p>
                </div>
                <div class="inline-flex p-1 bg-black/[0.04] dark:bg-white/[0.06] rounded-full border border-black/[0.04] dark:border-white/[0.06] text-xs font-semibold shrink-0">
                    <button type="button" @click="calcMode = 'margin'" :class="calcMode === 'margin' ? 'bg-white dark:bg-[#1C1C1E] text-[#1D1D1F] dark:text-[#F5F5F7] shadow-sm' : 'text-[#6E6E73] dark:text-[#86868B] hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7]'" class="px-4 py-1.5 rounded-full transition-all">
                        Target Margin (%)
                    </button>
                    <button type="button" @click="calcMode = 'markup'" :class="calcMode === 'markup' ? 'bg-white dark:bg-[#1C1C1E] text-[#1D1D1F] dark:text-[#F5F5F7] shadow-sm' : 'text-[#6E6E73] dark:text-[#86868B] hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7]'" class="px-4 py-1.5 rounded-full transition-all">
                        Target Markup (%)
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- Left: Apple Inset Input Controls (7 Kolom) -->
                <div class="lg:col-span-7 space-y-4">
                    <!-- 1. Bahan Baku -->
                    <div class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-bold text-[#007AFF] dark:text-[#0A84FF] uppercase tracking-wide">1. Biaya Bahan Baku (Material)</label>
                            <span class="font-mono text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Rp <span x-text="Number(matCost).toLocaleString('id-ID')"></span></span>
                        </div>
                        <input type="number" x-model.number="matCost" class="w-full h-11 px-3.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[#1D1D1F] dark:text-[#F5F5F7] font-mono text-sm focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all">
                        <input type="range" x-model.number="matCost" min="1000" max="150000" step="500" class="w-full accent-[#007AFF] cursor-pointer">
                        <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B]">Bahan mentah + bumbu + kemasan langsung per porsi.</p>
                    </div>

                    <!-- 2. Tenaga Kerja -->
                    <div class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] uppercase tracking-wide">2. Biaya Tenaga Kerja Langsung</label>
                            <span class="font-mono text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Rp <span x-text="Number(labCost).toLocaleString('id-ID')"></span></span>
                        </div>
                        <input type="number" x-model.number="labCost" class="w-full h-11 px-3.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[#1D1D1F] dark:text-[#F5F5F7] font-mono text-sm focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all">
                        <input type="range" x-model.number="labCost" min="0" max="50000" step="500" class="w-full accent-[#007AFF] cursor-pointer">
                        <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B]">Upah memasak/produksi dibagi estimasi jumlah output per hari.</p>
                    </div>

                    <!-- 3. Overhead -->
                    <div class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-bold text-[#FF9500] dark:text-[#FF9F0A] uppercase tracking-wide">3. Biaya Overhead Operasional</label>
                            <span class="font-mono text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Rp <span x-text="Number(ovhCost).toLocaleString('id-ID')"></span></span>
                        </div>
                        <input type="number" x-model.number="ovhCost" class="w-full h-11 px-3.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[#1D1D1F] dark:text-[#F5F5F7] font-mono text-sm focus:border-[#FF9500] focus:ring-2 focus:ring-[#FF9500]/20 focus:outline-none transition-all">
                        <input type="range" x-model.number="ovhCost" min="0" max="50000" step="500" class="w-full accent-[#FF9500] cursor-pointer">
                        <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B]">Gas elpiji, listrik, air, sabun, dan kantong plastik.</p>
                    </div>

                    <!-- 4. Target Margin / Markup Rate -->
                    <div class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-bold text-[#007AFF] dark:text-[#0A84FF] uppercase tracking-wide">
                                Target <span x-text="calcMode === 'margin' ? 'Gross Margin' : 'Markup'"></span>
                            </label>
                            <span class="font-mono text-sm font-bold text-[#007AFF] dark:text-[#0A84FF]" x-text="targetRate + '%'"></span>
                        </div>
                        <input type="range" x-model.number="targetRate" min="5" max="90" step="1" class="w-full accent-[#007AFF] cursor-pointer">
                    </div>
                </div>

                <!-- Right: Sticky Bento Output Card (5 Kolom) -->
                <div class="lg:col-span-5 sticky top-24 space-y-5">
                    <div class="glass-card p-6 sm:p-7 rounded-[26px] space-y-5 bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                        <div class="space-y-4">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-[#6E6E73] dark:text-[#86868B]">Total HPP Modal per Unit</span>
                                <div class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] font-mono mt-1">
                                    Rp <span x-text="totalHpp.toLocaleString('id-ID')"></span>
                                </div>
                            </div>

                            <div class="p-4 rounded-[18px] bg-[#34C759]/10 border border-[#34C759]/20">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-[#34C759] dark:text-[#30D158]">Rekomendasi Harga Jual</span>
                                <div class="text-3xl sm:text-4xl font-black text-[#34C759] dark:text-[#30D158] font-mono mt-1">
                                    Rp <span x-text="sellingPrice.toLocaleString('id-ID')"></span>
                                </div>
                                <div class="text-[11px] text-[#6E6E73] dark:text-[#86868B] mt-1">
                                    Laba kotor: <strong class="text-[#1D1D1F] dark:text-[#F5F5F7] font-semibold">Rp <span x-text="profitNominal.toLocaleString('id-ID')"></span></strong> per produk.
                                </div>
                            </div>

                            <!-- Metrics Comparison -->
                            <div class="grid grid-cols-2 gap-3 pt-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                                <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06]">
                                    <span class="text-[10px] text-[#6E6E73] dark:text-[#86868B] uppercase font-bold block">Gross Margin</span>
                                    <span class="text-base font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] font-mono" x-text="marginPercent + '%'"></span>
                                </div>
                                <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06]">
                                    <span class="text-[10px] text-[#6E6E73] dark:text-[#86868B] uppercase font-bold block">Markup Rate</span>
                                    <span class="text-base font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] font-mono" x-text="markupPercent + '%'"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Conversion CTA -->
                        <div class="pt-5 border-t border-black/[0.06] dark:border-white/[0.08] space-y-2">
                            <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B] text-center">
                                Ingin HPP resep langsung terpotong saat kasir input pesanan?
                            </p>
                            <a href="{{ route('register') }}" class="w-full glow-btn py-3.5 rounded-[14px] text-white font-semibold text-xs flex items-center justify-center gap-2 active:scale-[0.98] transition-transform">
                                <span>Buka Akun Kasir Gratis</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Educational Section (Apple Inset Style) -->
        <div class="space-y-6 text-[#6E6E73] dark:text-[#86868B] text-sm leading-relaxed border-t border-black/[0.06] dark:border-white/[0.08] pt-10">
            <div>
                <h3 class="text-xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mb-2">Apa itu HPP (Harga Pokok Penjualan)?</h3>
                <p>
                    Harga Pokok Penjualan (HPP) atau <em>Cost of Goods Sold (COGS)</em> adalah total biaya langsung yang dikeluarkan untuk memproduksi atau membeli barang dagangan hingga siap dijual kepada konsumen. Mengetahui HPP dengan akurat adalah syarat mutlak agar Anda tidak menjual rugi saat memberikan diskon atau promo.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="p-5 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
                    <h4 class="font-bold text-[#007AFF] dark:text-[#0A84FF] mb-2">Perbedaan Margin vs Markup</h4>
                    <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                        <strong>Markup</strong> adalah persentase laba yang ditambahkan di atas HPP: <code>(Harga Jual - HPP) / HPP</code>.<br><br>
                        <strong>Margin</strong> adalah persentase laba dari total harga jual: <code>(Harga Jual - HPP) / Harga Jual</code>.<br><br>
                        <em>Contoh:</em> HPP Rp 10.000 dijual Rp 15.000 memiliki Markup 50%, namun Margin-nya adalah 33.3%.
                    </p>
                </div>
                <div class="p-5 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
                    <h4 class="font-bold text-[#34C759] dark:text-[#30D158] mb-2">Rumus 3-Pilar Cooca UMKM</h4>
                    <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                        Cooca membagi HPP menjadi 3 pilar standar akuntansi manufaktur:<br>
                        1. <strong>Material:</strong> Bahan mentah dan kemasan langsung.<br>
                        2. <strong>Labor:</strong> Upah pekerja per satuan output.<br>
                        3. <strong>Overhead:</strong> Beban pendukung seperti listrik, gas, air, dan perlengkapan.
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
