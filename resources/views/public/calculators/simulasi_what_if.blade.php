@extends('layouts.public_marketing')

@section('title', 'Simulasi Bisnis What-If (Sensitivitas Biaya & Diskon) Online Gratis | Cooca')
@section('description', 'Alat simulasi What-If bisnis UMKM online. Uji skenario kenaikan harga bahan baku, kenaikan upah
    tenaga kerja, atau dampak pemberian diskon promo terhadap sisa keuntungan bersih.')
@section('keywords', 'simulasi bisnis what-if, kalkulator sensitivitas biaya, dampak diskon terhadap laba, skenario
    kenaikan bahan baku, simulator bisnis umkm online')

@section('content')
    <div class="pt-8 pb-24">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

            <!-- Breadcrumbs (Apple Inset Style) -->
            <nav class="flex items-center gap-2 text-xs text-[#6E6E73] dark:text-[#86868B]">
                <a href="{{ route('landing') }}"
                    class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Beranda</a>
                <span>/</span>
                <a href="{{ route('kalkulator.index') }}"
                    class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Kalkulator</a>
                <span>/</span>
                <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold">Simulasi What-If</span>
            </nav>

            <!-- Header -->
            <div class="text-center max-w-2xl mx-auto space-y-3">
                <div
                    class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-bold text-xs">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    <span>Decision Simulator</span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">Simulasi
                    What-If Bisnis UMKM</h1>
                <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] max-w-xl mx-auto leading-relaxed">
                    Apa yang terjadi pada keuntungan Anda jika harga bahan baku naik 15%? Atau jika Anda memberi diskon
                    promo 20%? Uji dampaknya secara real-time sebelum mengambil keputusan!
                </p>
            </div>

            <!-- Calculator Interactive App (2-Column Bento System) -->
            <div class="glass-card p-6 sm:p-8 rounded-[28px]" x-data="{
                baseRevenue: 40000000, // Omzet normal
                baseMaterial: 18000000, // Biaya bahan baku normal
                baseFixedCost: 10000000, // Biaya operasional & gaji normal
            
                // Skenario Perubahan (Slider delta)
                deltaMaterialPct: 10, // Kenaikan bahan baku (%)
                discountPct: 0, // Diskon promo (%)
                salesVolumeBoostPct: 0, // Pertambahan volume pembeli akibat promo (%)
            
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
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    <!-- Left: Skenario Adjustments (7 Cols) -->
                    <div class="lg:col-span-7 space-y-6">
                        <!-- Baseline Inset Box -->
                        <div
                            class="p-5 rounded-[22px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-3">
                            <span
                                class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] uppercase tracking-wide block">1.
                                Kondisi Bisnis Normal Saat Ini</span>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                                <div
                                    class="p-3.5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
                                    <span
                                        class="text-[#6E6E73] dark:text-[#86868B] block text-[10px] uppercase font-bold">Omzet
                                        Bulanan</span>
                                    <span class="font-mono font-bold text-[#1D1D1F] dark:text-[#F5F5F7] text-sm">Rp <span
                                            x-text="Number(baseRevenue).toLocaleString('id-ID')"></span></span>
                                </div>
                                <div
                                    class="p-3.5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
                                    <span
                                        class="text-[#6E6E73] dark:text-[#86868B] block text-[10px] uppercase font-bold">Biaya
                                        Bahan</span>
                                    <span class="font-mono font-bold text-[#FF9500] dark:text-[#FF9F0A] text-sm">Rp <span
                                            x-text="Number(baseMaterial).toLocaleString('id-ID')"></span></span>
                                </div>
                                <div
                                    class="p-3.5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
                                    <span
                                        class="text-[#6E6E73] dark:text-[#86868B] block text-[10px] uppercase font-bold">Laba
                                        Normal</span>
                                    <span class="font-mono font-bold text-[#34C759] dark:text-[#30D158] text-sm">Rp <span
                                            x-text="baseProfit.toLocaleString('id-ID')"></span></span>
                                </div>
                            </div>
                        </div>

                        <!-- Slider 1: Kenaikan Bahan Baku -->
                        <div
                            class="p-5 rounded-[22px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                            <div class="flex justify-between items-center">
                                <label
                                    class="text-xs font-bold text-[#FF9500] dark:text-[#FF9F0A] uppercase tracking-wide">Skenario
                                    Kenaikan Harga Bahan Baku</label>
                                <span
                                    class="font-mono text-sm font-bold text-[#FF9500] dark:text-[#FF9F0A] bg-[#FF9500]/10 px-2.5 py-1 rounded-full">+<span
                                        x-text="deltaMaterialPct"></span>%</span>
                            </div>
                            <input type="range" x-model.number="deltaMaterialPct" min="0" max="50"
                                step="5" class="w-full accent-[#FF9500] cursor-pointer">
                            <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B]">Simulasikan dampak kenaikan harga
                                beras, telur, minyak, kopi, atau sparepart.</p>
                        </div>

                        <!-- Slider 2: Diskon Promo -->
                        <div
                            class="p-5 rounded-[22px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                            <div class="flex justify-between items-center">
                                <label
                                    class="text-xs font-bold text-[#007AFF] dark:text-[#0A84FF] uppercase tracking-wide">Skenario
                                    Diskon Promo Penjualan</label>
                                <span
                                    class="font-mono text-sm font-bold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 px-2.5 py-1 rounded-full"><span
                                        x-text="discountPct"></span>% Diskon</span>
                            </div>
                            <input type="range" x-model.number="discountPct" min="0" max="40" step="5"
                                class="w-full accent-[#007AFF] cursor-pointer">
                            <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B]">Potongan harga langsung untuk promosi
                                musiman atau cuci gudang.</p>
                        </div>

                        <!-- Slider 3: Kenaikan Volume Pembeli Akibat Promo -->
                        <div class="p-5 rounded-[22px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2"
                            x-show="discountPct > 0" x-transition>
                            <div class="flex justify-between items-center">
                                <label
                                    class="text-xs font-bold text-[#34C759] dark:text-[#30D158] uppercase tracking-wide">Estimasi
                                    Lonjakan Volume Pembeli</label>
                                <span
                                    class="font-mono text-sm font-bold text-[#34C759] dark:text-[#30D158] bg-[#34C759]/10 px-2.5 py-1 rounded-full">+<span
                                        x-text="salesVolumeBoostPct"></span>% Volume</span>
                            </div>
                            <input type="range" x-model.number="salesVolumeBoostPct" min="0" max="100"
                                step="10" class="w-full accent-[#34C759] cursor-pointer">
                            <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B]">Diskon biasanya menarik pembeli baru.
                                Berapa perkiraan lonjakan pesanannya?</p>
                        </div>
                    </div>

                    <!-- Right: Simulation Output Card (5 Cols Sticky) -->
                    <div class="lg:col-span-5 lg:sticky lg:top-24">
                        <div
                            class="p-6 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-5">
                            <div
                                class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.08]">
                                <span
                                    class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">Hasil
                                    Simulasi Laba</span>
                                <span
                                    class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-black/[0.04] dark:bg-white/[0.06] text-[#6E6E73] dark:text-[#86868B]">Proyeksi</span>
                            </div>

                            <!-- Perbandingan Laba -->
                            <div class="p-4 rounded-[18px] border shadow-sm transition-all"
                                :class="simProfit >= baseProfit ? 'bg-[#34C759]/10 border-[#34C759]/20' :
                                    'bg-[#FF3B30]/10 border-[#FF3B30]/20'">
                                <span class="text-[10px] font-bold uppercase text-[#6E6E73] dark:text-[#86868B] block">Laba
                                    Bersih Setelah Skenario</span>
                                <div class="text-2xl sm:text-3xl font-extrabold font-mono mt-1"
                                    :class="simProfit >= 0 ? 'text-[#34C759] dark:text-[#30D158]' :
                                        'text-[#FF3B30] dark:text-[#FF453A]'">
                                    Rp <span x-text="simProfit.toLocaleString('id-ID')"></span>
                                </div>
                                <div class="text-xs mt-2 font-semibold flex items-center gap-1.5"
                                    :class="deltaProfit >= 0 ? 'text-[#34C759] dark:text-[#30D158]' :
                                        'text-[#FF3B30] dark:text-[#FF453A]'">
                                    <i :data-lucide="deltaProfit >= 0 ? 'trending-up' : 'trending-down'"
                                        class="w-4 h-4"></i>
                                    <span>Selisih: <span x-text="deltaProfit >= 0 ? '+' : ''"></span>Rp <span
                                            x-text="deltaProfit.toLocaleString('id-ID')"></span> dari kondisi awal</span>
                                </div>
                            </div>

                            <!-- Rincian Proyeksi Baru -->
                            <div
                                class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2.5 text-xs">
                                <div class="flex justify-between items-center">
                                    <span class="text-[#6E6E73] dark:text-[#86868B]">Omzet Proyeksi:</span>
                                    <span class="font-mono font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Rp <span
                                            x-text="simRevenue.toLocaleString('id-ID')"></span></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-[#6E6E73] dark:text-[#86868B]">Beban Bahan Baru:</span>
                                    <span class="font-mono text-[#FF9500] dark:text-[#FF9F0A] font-semibold">Rp <span
                                            x-text="simMaterial.toLocaleString('id-ID')"></span></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-[#6E6E73] dark:text-[#86868B]">Beban Operasional Tetap:</span>
                                    <span class="font-mono text-[#1D1D1F] dark:text-[#F5F5F7]">Rp <span
                                            x-text="Number(baseFixedCost).toLocaleString('id-ID')"></span></span>
                                </div>
                            </div>

                            <!-- Action CTA -->
                            <div class="pt-2">
                                <a href="{{ route('register') }}"
                                    class="w-full py-3.5 rounded-[16px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-xs flex items-center justify-center gap-2 shadow-sm active:scale-[0.98] transition-all">
                                    <span>Gunakan Simulasi Real-Time di Cooca</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Educational Bento Cards -->
            <div
                class="space-y-6 text-[#6E6E73] dark:text-[#86868B] text-sm leading-relaxed border-t border-black/[0.06] dark:border-white/[0.08] pt-10">
                <div>
                    <h3 class="text-xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mb-2">Mengapa Simulasi What-If Penting
                        Bagi UMKM?</h3>
                    <p>
                        Banyak pengusaha UMKM terlena memberikan diskon besar tanpa memperhitungkan berapa lonjakan kuantiti
                        penjualan yang dibutuhkan untuk menutupi margin keuntungan yang tergerus. Simulasi sensitivitas
                        What-If membantu Anda mengambil keputusan berbasis data nyata, bukan sekadar intuisi.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div
                        class="p-5 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
                        <h4 class="font-bold text-[#007AFF] dark:text-[#0A84FF] mb-2">Jebakan Diskon Promo</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Jika margin keuntungan Anda adalah 30%, memberikan diskon 15% mengharuskan Anda menjual
                            <strong>100% lebih banyak volume</strong> hanya untuk menghasilkan nominal laba bersih yang
                            sama. Jangan adakan diskon tanpa mengukur elastisitas permintaan.
                        </p>
                    </div>
                    <div
                        class="p-5 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
                        <h4 class="font-bold text-[#FF9500] dark:text-[#FF9F0A] mb-2">Strategi Hadapi Inflasi Bahan Baku
                        </h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Saat bahan baku melonjak, Anda memiliki 3 opsi: menaikkan harga jual, mengecilkan porsi
                            (*shrinkflation*), atau melakukan rekayasa menu (*menu engineering*) dengan menonjolkan produk
                            bertarget margin tinggi.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
