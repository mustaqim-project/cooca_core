@extends('layouts.app', [
    'title' => 'Kalkulator HPP — Cooca Core',
    'headerTitle' => 'Kalkulator HPP & Penetapan Harga',
    'headerSubtitle' => 'Hitung modal bersih per porsi/pcs secara mudah dan tentukan harga jual yang menguntungkan'
])

@section('content')
<div class="space-y-6" x-data="{
    activeTab: 'quick', // 'quick' or 'advanced'

    // QUICK MODE STATE
    quickName: 'Kopi Susu Gula Aren',
    quickMaterial: 4500,
    quickLabor: 1000,
    quickOverhead: 500,
    quickMargin: 40,
    quickSellOnline: true,
    quickOnlineFeePct: 20,
    quickMonthlyFixedCost: 1500000,

    // Quick presets
    applyPreset(preset) {
        if (preset === 'kopi') {
            this.quickName = 'Kopi Susu Gula Aren';
            this.quickMaterial = 4500;
            this.quickLabor = 1000;
            this.quickOverhead = 500;
            this.quickMargin = 40;
        } else if (preset === 'geprek') {
            this.quickName = 'Paket Ayam Geprek Nasi';
            this.quickMaterial = 8500;
            this.quickLabor = 2000;
            this.quickOverhead = 1000;
            this.quickMargin = 35;
        } else if (preset === 'kaos') {
            this.quickName = 'Kaos Polos Sablon Distro';
            this.quickMaterial = 38000;
            this.quickLabor = 8000;
            this.quickOverhead = 4000;
            this.quickMargin = 45;
        } else if (preset === 'kue') {
            this.quickName = 'Brownies Fudgy Panggang (Box)';
            this.quickMaterial = 24000;
            this.quickLabor = 5000;
            this.quickOverhead = 3000;
            this.quickMargin = 40;
        }
    },

    // Quick Mode Computed
    get quickTotalHpp() {
        return Math.max(0, (parseFloat(this.quickMaterial) || 0) + (parseFloat(this.quickLabor) || 0) + (parseFloat(this.quickOverhead) || 0));
    },
    get quickMaterialPct() {
        if (this.quickTotalHpp === 0) return 0;
        return Math.round(((parseFloat(this.quickMaterial) || 0) / this.quickTotalHpp) * 100);
    },
    get quickLaborPct() {
        if (this.quickTotalHpp === 0) return 0;
        return Math.round(((parseFloat(this.quickLabor) || 0) / this.quickTotalHpp) * 100);
    },
    get quickOverheadPct() {
        if (this.quickTotalHpp === 0) return 0;
        return Math.max(0, 100 - this.quickMaterialPct - this.quickLaborPct);
    },
    get quickOfflinePrice() {
        if (this.quickTotalHpp <= 0) return 0;
        if (this.quickMargin >= 100) return 0;
        let p = this.quickTotalHpp / (1 - (this.quickMargin / 100));
        return Math.ceil(p / 500) * 500; // Round up to nearest 500 for clean rupiah
    },
    get quickOfflineProfit() {
        return Math.max(0, this.quickOfflinePrice - this.quickTotalHpp);
    },
    get quickMarkupPct() {
        if (this.quickTotalHpp <= 0) return 0;
        return Math.round((this.quickOfflineProfit / this.quickTotalHpp) * 100);
    },
    get quickOnlinePrice() {
        if (!this.quickSellOnline || this.quickOfflinePrice <= 0) return this.quickOfflinePrice;
        let feeDecimal = (parseFloat(this.quickOnlineFeePct) || 0) / 100;
        if (feeDecimal >= 1) return this.quickOfflinePrice;
        let online = this.quickOfflinePrice / (1 - feeDecimal);
        return Math.ceil(online / 1000) * 1000; // Round to nearest 1000
    },
    get quickOnlineFeeNominal() {
        let feeDecimal = (parseFloat(this.quickOnlineFeePct) || 0) / 100;
        return Math.round(this.quickOnlinePrice * feeDecimal);
    },
    get quickOnlineNetRevenue() {
        return this.quickOnlinePrice - this.quickOnlineFeeNominal;
    },
    get quickBepUnitsMonthly() {
        let profit = this.quickOfflineProfit;
        if (profit <= 0) return 0;
        return Math.ceil((parseFloat(this.quickMonthlyFixedCost) || 0) / profit);
    },
    get quickBepUnitsDaily() {
        return Math.ceil(this.quickBepUnitsMonthly / 30);
    },

    // Quick Save Modal State
    showQuickSaveModal: false,
    quickSaveLoading: false,
    quickSaveSuccessMsg: '',
    quickSaveErrorMsg: '',

    saveQuickProduct() {
        if (!this.quickName.trim()) {
            alert('Silakan masukkan nama produk terlebih dahulu.');
            return;
        }
        this.quickSaveLoading = true;
        this.quickSaveSuccessMsg = '';
        this.quickSaveErrorMsg = '';

        const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';

        fetch('/calculator/quick-create-product', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                name: this.quickName,
                material_cost: this.quickMaterial,
                labor_cost: this.quickLabor,
                overhead_cost: this.quickOverhead,
                selling_price: this.quickOfflinePrice
            })
        })
        .then(res => res.json())
        .then(data => {
            this.quickSaveLoading = false;
            if (data.success) {
                this.quickSaveSuccessMsg = data.message;
                if (data.product) {
                    this.products.unshift(data.product);
                }
                if (window.coocaToast) {
                    window.coocaToast(data.message, 'success');
                }
                setTimeout(() => {
                    this.showQuickSaveModal = false;
                    this.quickSaveSuccessMsg = '';
                }, 2000);
            } else {
                this.quickSaveErrorMsg = data.message || 'Gagal menyimpan produk.';
                if (window.coocaToast) {
                    window.coocaToast(this.quickSaveErrorMsg, 'error');
                }
            }
        })
        .catch(err => {
            this.quickSaveLoading = false;
            this.quickSaveErrorMsg = 'Terjadi kesalahan sistem.';
            if (window.coocaToast) {
                window.coocaToast(this.quickSaveErrorMsg, 'error');
            }
        });
    },

    // ADVANCED BOM STATE (Preserved)
    products: {{ Js::from($products) }},
    fees: {{ Js::from($fees) }},
    recentRuns: {{ Js::from($recentRuns) }},
    selectedProductId: '',
    selectedProduct: null,
    selectedCostModel: null,
    isCalculating: false,
    calcResult: null,
    isSaving: false,
    saveNotes: '',
    saveSuccessMsg: '',
    saveErrorMsg: '',
    isApplying: false,
    applySuccessMsg: '',
    applyErrorMsg: '',

    init() {
        if (this.products.length > 0) {
            this.selectProduct(this.products[0].id);
        }
    },

    selectProduct(prodId) {
        this.selectedProductId = prodId;
        this.selectedProduct = this.products.find(p => p.id === prodId) || null;
        if (this.selectedProduct && this.selectedProduct.cost_models && this.selectedProduct.cost_models.length > 0) {
            this.selectedCostModel = this.selectedProduct.cost_models[0];
            this.runCalculation();
        } else {
            this.selectedCostModel = null;
            this.calcResult = null;
        }
    },

    runCalculation() {
        if (!this.selectedCostModel) return;
        this.isCalculating = true;
        fetch(`/calculator/calculate/${this.selectedCostModel.id}`)
            .then(res => res.json())
            .then(data => {
                this.calcResult = data.result;
                this.isCalculating = false;
            })
            .catch(err => {
                console.error(err);
                this.isCalculating = false;
            });
    },

    applyToProduct() {
        if (!this.selectedProduct || !this.calcResult) return;
        this.isApplying = true;
        this.applySuccessMsg = '';
        this.applyErrorMsg = '';

        const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';

        fetch('/calculator/apply-to-product', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                cost_model_id: this.selectedCostModel.id,
                selling_price: Math.round(this.calcResult.hpp_per_unit / 0.6)
            })
        })
        .then(res => res.json())
        .then(data => {
            this.isApplying = false;
            if (data.success) {
                this.applySuccessMsg = data.message;
                setTimeout(() => { this.applySuccessMsg = ''; }, 4000);
            } else {
                this.applyErrorMsg = data.message || 'Gagal menerapkan harga.';
            }
        })
        .catch(() => {
            this.isApplying = false;
            this.applyErrorMsg = 'Gagal memproses permohonan.';
        });
    }
}">

    <!-- Mode Selector Banner -->
    <div class="glass-card rounded-3xl p-3 sm:p-4 border border-slate-800 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <div class="flex items-center gap-1.5 p-1 rounded-2xl bg-slate-950/80 border border-slate-800/80">
            <button type="button" @click="activeTab = 'quick'"
                    :class="activeTab === 'quick' ? 'bg-gradient-to-r from-emerald-600 to-teal-500 text-white font-black shadow-lg shadow-emerald-500/20' : 'text-slate-400 hover:text-white'"
                    class="px-4 py-2 rounded-xl text-xs transition flex items-center gap-2">
                <i data-lucide="zap" class="w-4 h-4 text-amber-300"></i>
                <span>Mode Cepat (3 Pilar HPP)</span>
                <span class="px-1.5 py-0.2 rounded-full text-[9px] font-black bg-white/20 text-white uppercase">Simpel</span>
            </button>

            <button type="button" @click="activeTab = 'advanced'"
                    :class="activeTab === 'advanced' ? 'bg-indigo-600 text-white font-black shadow-lg shadow-indigo-500/20' : 'text-slate-400 hover:text-white'"
                    class="px-4 py-2 rounded-xl text-xs transition flex items-center gap-2">
                <i data-lucide="layers" class="w-4 h-4 text-indigo-300"></i>
                <span>Mode Detail Resep (BOM)</span>
            </button>
        </div>

        <div class="flex items-center justify-end gap-2 text-xs">
            <a href="{{ route('calculator.export-excel') }}"
               class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-300 hover:text-white transition flex items-center gap-1.5 font-bold">
                <i data-lucide="download" class="w-3.5 h-3.5 text-emerald-400"></i>
                <span>Export Riwayat (.CSV)</span>
            </a>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 1: MODE CEPAT (3 PILAR HPP)             -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'quick'" class="space-y-6">

        <!-- 1-Click Quick Preset Chips -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
            <span class="text-slate-500 font-bold uppercase tracking-wider text-[10px] shrink-0">Contoh Cepat:</span>
            <button type="button" @click="applyPreset('kopi')" class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-300 hover:text-white shrink-0 transition flex items-center gap-1.5">
                <span>☕ Kopi Susu</span>
            </button>
            <button type="button" @click="applyPreset('geprek')" class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-300 hover:text-white shrink-0 transition flex items-center gap-1.5">
                <span>🍗 Ayam Geprek Nasi</span>
            </button>
            <button type="button" @click="applyPreset('kaos')" class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-300 hover:text-white shrink-0 transition flex items-center gap-1.5">
                <span>👕 Kaos Sablon Distro</span>
            </button>
            <button type="button" @click="applyPreset('kue')" class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-300 hover:text-white shrink-0 transition flex items-center gap-1.5">
                <span>🍰 Brownies Panggang</span>
            </button>
        </div>

        <!-- Main 3-Pillar Calculator Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- LEFT COLUMN: 3 PILLARS INPUT (7 cols) -->
            <div class="lg:col-span-7 space-y-6">

                <!-- Product Name Input Card -->
                <div class="glass-card rounded-3xl p-5 border border-slate-800 space-y-2">
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="tag" class="w-4 h-4 text-emerald-400"></i>
                        <span>Nama Produk / Menu Yang Dihitung</span>
                    </label>
                    <input type="text" x-model="quickName" placeholder="Contoh: Kopi Susu Gula Aren 250ml"
                           class="w-full bg-slate-950/80 border border-slate-700/80 rounded-2xl px-4 py-3 text-base font-black text-white focus:outline-none focus:border-emerald-500 transition">
                </div>

                <!-- The 3 Pillars of HPP Container -->
                <div class="glass-card rounded-3xl p-6 border border-slate-800 space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <h3 class="text-base font-black text-white flex items-center gap-2">
                            <i data-lucide="calculator" class="w-5 h-5 text-emerald-400"></i>
                            <span>3 Komponen Modal Bersih (HPP)</span>
                        </h3>
                        <span class="text-xs text-slate-400 font-mono">Biaya per 1 Porsi / Pcs</span>
                    </div>

                    <!-- PILAR 1: Bahan & Kemasan -->
                    <div class="p-5 rounded-2xl bg-slate-950/60 border border-emerald-500/30 space-y-2 relative overflow-hidden group">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs">
                                    1
                                </div>
                                <div>
                                    <div class="font-bold text-sm text-white">Biaya Bahan Baku & Kemasan</div>
                                    <div class="text-[11px] text-slate-400">Bahan utama, bumbu, cup, botol, plastik, label kemasan</div>
                                </div>
                            </div>
                            <span class="text-xs font-mono font-bold text-emerald-400" x-text="quickMaterialPct + '%'"></span>
                        </div>
                        <div class="flex items-center gap-2 pt-1">
                            <span class="text-base font-black text-slate-400 font-mono">Rp</span>
                            <input type="number" x-model.number="quickMaterial" min="0" step="500"
                                   class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-lg font-black font-mono text-white focus:outline-none focus:border-emerald-500">
                        </div>
                    </div>

                    <!-- PILAR 2: Tenaga Kerja / Upah -->
                    <div class="p-5 rounded-2xl bg-slate-950/60 border border-indigo-500/30 space-y-2 relative overflow-hidden group">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-xs">
                                    2
                                </div>
                                <div>
                                    <div class="font-bold text-sm text-white">Biaya Upah & Tenaga Kerja</div>
                                    <div class="text-[11px] text-slate-400">Ongkos masak, barista, penjahit, atau upah kerja per pcs (isi 0 jika sendiri)</div>
                                </div>
                            </div>
                            <span class="text-xs font-mono font-bold text-indigo-400" x-text="quickLaborPct + '%'"></span>
                        </div>
                        <div class="flex items-center gap-2 pt-1">
                            <span class="text-base font-black text-slate-400 font-mono">Rp</span>
                            <input type="number" x-model.number="quickLabor" min="0" step="500"
                                   class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-lg font-black font-mono text-white focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <!-- PILAR 3: Operasional / Listrik / Gas -->
                    <div class="p-5 rounded-2xl bg-slate-950/60 border border-amber-500/30 space-y-2 relative overflow-hidden group">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold text-xs">
                                    3
                                </div>
                                <div>
                                    <div class="font-bold text-sm text-white">Biaya Operasional & Utilitas</div>
                                    <div class="text-[11px] text-slate-400">Alokasi gas elpiji, listrik, air, sewa tempat per porsi</div>
                                </div>
                            </div>
                            <span class="text-xs font-mono font-bold text-amber-400" x-text="quickOverheadPct + '%'"></span>
                        </div>
                        <div class="flex items-center gap-2 pt-1">
                            <span class="text-base font-black text-slate-400 font-mono">Rp</span>
                            <input type="number" x-model.number="quickOverhead" min="0" step="500"
                                   class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-lg font-black font-mono text-white focus:outline-none focus:border-amber-500">
                        </div>
                    </div>

                    <!-- Visual Proportion Bar -->
                    <div class="space-y-2 pt-2">
                        <div class="flex justify-between text-[11px] font-bold">
                            <span class="text-slate-400">Komposisi Pengeluaran Modal:</span>
                            <span class="font-mono text-white">Total: Rp <span x-text="quickTotalHpp.toLocaleString('id-ID')"></span></span>
                        </div>
                        <div class="w-full h-3 rounded-full bg-slate-900 overflow-hidden flex">
                            <div :style="'width: ' + quickMaterialPct + '%'" class="bg-emerald-500 transition-all duration-300" title="Bahan Baku"></div>
                            <div :style="'width: ' + quickLaborPct + '%'" class="bg-indigo-500 transition-all duration-300" title="Upah Kerja"></div>
                            <div :style="'width: ' + quickOverheadPct + '%'" class="bg-amber-500 transition-all duration-300" title="Operasional"></div>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-slate-400 font-semibold pt-1">
                            <div class="flex items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-emerald-500"></div><span>Bahan (<span x-text="quickMaterialPct + '%'"></span>)</span></div>
                            <div class="flex items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-indigo-500"></div><span>Upah (<span x-text="quickLaborPct + '%'"></span>)</span></div>
                            <div class="flex items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-amber-500"></div><span>Operasional (<span x-text="quickOverheadPct + '%'"></span>)</span></div>
                        </div>
                    </div>
                </div>

                <!-- Human-Centric Narrative Card -->
                <div class="p-5 rounded-3xl bg-gradient-to-r from-emerald-950/40 via-slate-900 to-slate-950 border border-emerald-500/30 flex items-start gap-4">
                    <div class="p-3 rounded-2xl bg-emerald-500/20 text-emerald-400 shrink-0 mt-0.5">
                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                    </div>
                    <div class="space-y-1 text-xs">
                        <h4 class="font-black text-white text-sm">Kesimpulan Modal Bersih (Bahasa Sederhana)</h4>
                        <p class="text-slate-300 leading-relaxed">
                            Untuk menghasilkan 1 porsi <strong class="text-white font-bold" x-text="quickName"></strong>, modal riil yang Anda keluarkan adalah <strong class="text-emerald-400 font-mono font-bold">Rp <span x-text="quickTotalHpp.toLocaleString('id-ID')"></span></strong>.
                            Sebesar <strong class="text-white" x-text="quickMaterialPct + '%'"></strong> uang habis untuk bahan & kemasan.
                        </p>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: SMART PRICING & PROFIT STRATEGY (5 cols) -->
            <div class="lg:col-span-5 space-y-6">

                <!-- Pricing & Profit Engine Card -->
                <div class="glass-card rounded-3xl p-6 border border-slate-800 space-y-6 sticky top-24">
                    <div class="border-b border-slate-800 pb-3 flex items-center justify-between">
                        <h3 class="text-base font-black text-white flex items-center gap-2">
                            <i data-lucide="trending-up" class="w-5 h-5 text-teal-400"></i>
                            <span>Target Untung & Harga Jual</span>
                        </h3>
                        <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-teal-500/20 text-teal-300">
                            Smart Coach
                        </span>
                    </div>

                    <!-- Margin Slider Control -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-300">Target Margin Laba Bersih:</span>
                            <div class="flex items-baseline gap-1">
                                <span class="text-2xl font-black font-mono text-emerald-400" x-text="quickMargin"></span>
                                <span class="text-xs font-bold text-emerald-400">%</span>
                            </div>
                        </div>

                        <input type="range" x-model.number="quickMargin" min="10" max="80" step="5"
                               class="w-full h-2.5 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-emerald-500">

                        <!-- Margin Health Indicator -->
                        <div class="p-3 rounded-2xl text-xs flex items-center gap-2"
                             :class="{
                                 'bg-amber-950/40 text-amber-300 border border-amber-500/30': quickMargin < 25,
                                 'bg-emerald-950/40 text-emerald-300 border border-emerald-500/30': quickMargin >= 25 && quickMargin <= 60,
                                 'bg-indigo-950/40 text-indigo-300 border border-indigo-500/30': quickMargin > 60
                             }">
                            <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
                            <span x-show="quickMargin < 25">⚠️ <strong>Margin Tipis:</strong> Rawan nombok jika harga bahan baku naik.</span>
                            <span x-show="quickMargin >= 25 && quickMargin <= 60">✨ <strong>Sehat & Ideal:</strong> Rekomendasi standar bisnis UMKM & kuliner.</span>
                            <span x-show="quickMargin > 60">💎 <strong>Margin Premium:</strong> Keuntungan tinggi, pastikan kemasan & rasa bersaing.</span>
                        </div>
                    </div>

                    <!-- Pricing Result Box -->
                    <div class="p-5 rounded-2xl bg-slate-950/90 border border-emerald-500/30 space-y-4">
                        <div>
                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Harga Jual Rekomendasi (Kasir / Toko)</div>
                            <div class="text-3xl font-black font-mono text-white tracking-tight mt-0.5">
                                Rp <span x-text="quickOfflinePrice.toLocaleString('id-ID')"></span>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-800 space-y-2 text-xs">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Untung Bersih per Pcs:</span>
                                <span class="font-mono font-bold text-emerald-400">+ Rp <span x-text="quickOfflineProfit.toLocaleString('id-ID')"></span></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Markup dari Modal HPP:</span>
                                <span class="font-mono text-slate-300"><span x-text="quickMarkupPct"></span>% dari modal</span>
                            </div>
                        </div>
                    </div>

                    <!-- Online Food / Marketplace Simulator Switch -->
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i data-lucide="bike" class="w-4 h-4 text-purple-400"></i>
                                <span class="text-xs font-bold text-white">Jual di Ojek Online? (GoFood/Grab)</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" x-model="quickSellOnline" class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>

                        <div x-show="quickSellOnline" class="space-y-3 pt-2 border-t border-slate-800 text-xs" style="display: none;">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">Potongan Komisi Aplikasi:</span>
                                <select x-model.number="quickOnlineFeePct" class="bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1 text-white font-mono text-xs">
                                    <option :value="20">20% (Standar GoFood/Grab/Shopee)</option>
                                    <option :value="15">15% (Promo Merchant)</option>
                                    <option :value="10">10% (Marketplace Toko Online)</option>
                                </select>
                            </div>

                            <div class="p-3 rounded-xl bg-purple-950/30 border border-purple-500/30 space-y-1">
                                <div class="text-[10px] text-purple-300 font-bold uppercase">Harga Wajib Pasang di Aplikasi Online:</div>
                                <div class="text-2xl font-black font-mono text-purple-200">
                                    Rp <span x-text="quickOnlinePrice.toLocaleString('id-ID')"></span>
                                </div>
                                <p class="text-[10px] text-slate-400 leading-tight pt-1">
                                    Dipasang Rp <span x-text="quickOnlinePrice.toLocaleString('id-ID')"></span> $\rightarrow$ dipotong komisi 20% (Rp <span x-text="quickOnlineFeeNominal.toLocaleString('id-ID')"></span>) $\rightarrow$ uang yang Anda terima <strong class="text-white">tetap utuh Rp <span x-text="quickOfflinePrice.toLocaleString('id-ID')"></span></strong> tanpa tekor komisi!
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Mini BEP (Break-Even Point) Tracker -->
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-300 flex items-center gap-1.5">
                                <i data-lucide="target" class="w-3.5 h-3.5 text-cyan-400"></i>
                                <span>Target Balik Modal (BEP)</span>
                            </span>
                            <span class="text-[10px] text-slate-500 font-mono">Simulasi Beban</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-slate-500 text-[11px]">Beban Sewa/Listrik Bln:</span>
                            <input type="number" x-model.number="quickMonthlyFixedCost" step="100000"
                                   class="w-32 bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-right text-xs font-mono text-white">
                        </div>
                        <div class="pt-1 text-[11px] text-slate-300 leading-relaxed">
                            💡 Jual minimal <strong class="text-emerald-400 font-bold font-mono"><span x-text="quickBepUnitsDaily"></span> pcs/hari</strong> (atau <span x-text="quickBepUnitsMonthly"></span> pcs/bulan) agar balik modal operasional. Porsi berikutnya adalah <strong class="text-white">keuntungan bersih Anda!</strong>
                        </div>
                    </div>

                    <!-- Save as Product Button -->
                    <button type="button" @click="showQuickSaveModal = true"
                            class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-slate-950 font-black text-xs shadow-xl shadow-emerald-500/20 transition flex items-center justify-center gap-2 group">
                        <i data-lucide="plus-circle" class="w-4 h-4 group-hover:scale-110 transition-transform"></i>
                        <span>Simpan Jadi Produk Baru di Kasir</span>
                    </button>
                </div>

            </div>

        </div>

    </div>

    <!-- ========================================== -->
    <!-- TAB 2: MODE DETAIL RESEP (BOM)             -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'advanced'" class="space-y-6" style="display: none;">

        <!-- Product Selector for BOM -->
        <div class="glass-card p-6 rounded-3xl border border-slate-800 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 shrink-0">
                    <i data-lucide="layers" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Pilih Produk Dari Katalog</h3>
                    <p class="text-xs text-slate-400">Sistem otomatis menghitung resep terperinci (BOM) dan alokasi mesin</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <select x-model="selectedProductId" @change="selectProduct($event.target.value)"
                        class="px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl text-xs font-semibold text-white min-w-[240px]">
                    <option value="">-- Pilih Produk --</option>
                    <template x-for="p in products" :key="p.id">
                        <option :value="p.id" x-text="p.name + ' (' + (p.output_unit ? p.output_unit.name : 'pcs') + ')'"></option>
                    </template>
                </select>

                <template x-if="selectedProduct">
                    <a :href="'/products/' + selectedProduct.slug + '/bom'"
                       class="px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-white flex items-center gap-1.5 transition">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        <span>Edit Resep BOM</span>
                    </a>
                </template>
            </div>
        </div>

        <!-- BOM Calculation Results View -->
        <template x-if="selectedProduct && calcResult">
            <div class="space-y-6">
                <!-- Result 4 Bento Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="glass-card p-5 rounded-2xl border-emerald-500/30 bg-slate-950/80">
                        <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider">HPP per Unit</span>
                        <div class="text-2xl font-extrabold text-white font-mono mt-1">
                            Rp <span x-text="Math.round(calcResult.hpp_per_unit).toLocaleString('id-ID')"></span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-mono">Satuan: <span x-text="selectedProduct.output_unit ? selectedProduct.output_unit.name : 'pcs'"></span></span>
                    </div>

                    <div class="glass-card p-5 rounded-2xl border-blue-500/30 bg-slate-950/80">
                        <span class="text-[10px] font-bold text-blue-400 uppercase tracking-wider">Bahan Baku (Material)</span>
                        <div class="text-2xl font-extrabold text-white font-mono mt-1">
                            Rp <span x-text="Math.round(calcResult.total_material_cost).toLocaleString('id-ID')"></span>
                        </div>
                        <span class="text-[10px] text-blue-400 font-mono" x-text="((calcResult.total_material_cost / calcResult.total_hpp) * 100).toFixed(1) + '% porsi'"></span>
                    </div>

                    <div class="glass-card p-5 rounded-2xl border-indigo-500/30 bg-slate-950/80">
                        <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider">Upah Kerja & Mesin</span>
                        <div class="text-2xl font-extrabold text-white font-mono mt-1">
                            Rp <span x-text="Math.round(calcResult.total_labor_cost + calcResult.total_machine_cost).toLocaleString('id-ID')"></span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-mono">Tenaga Kerja & Utilitas</span>
                    </div>

                    <div class="glass-card p-5 rounded-2xl border-amber-500/30 bg-slate-950/80">
                        <span class="text-[10px] font-bold text-amber-400 uppercase tracking-wider">Alokasi Overhead</span>
                        <div class="text-2xl font-extrabold text-white font-mono mt-1">
                            Rp <span x-text="Math.round(calcResult.total_overhead_cost).toLocaleString('id-ID')"></span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-mono">Pabrikasi / Operasional</span>
                    </div>
                </div>

                <!-- Breakdown Table & Apply Button -->
                <div class="glass-card rounded-3xl p-6 border border-slate-800 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <h4 class="font-bold text-white text-sm">Rincian Komponen Resep Terdaftar</h4>
                        <button type="button" @click="applyToProduct()" :disabled="isApplying"
                                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow transition flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span x-text="isApplying ? 'Menerapkan...' : 'Terapkan ke Harga Produk'"></span>
                        </button>
                    </div>

                    <div class="space-y-2">
                        <template x-for="(item, idx) in calcResult.breakdown" :key="idx">
                            <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between text-xs">
                                <div>
                                    <div class="font-bold text-white" x-text="item.name"></div>
                                    <div class="text-[10px] text-slate-500 capitalize" x-text="item.type"></div>
                                </div>
                                <div class="text-right font-mono font-bold text-emerald-400">
                                    Rp <span x-text="Math.round(item.cost).toLocaleString('id-ID')"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        <template x-if="!selectedProduct">
            <div class="glass-card p-12 rounded-3xl border border-slate-800 text-center">
                <i data-lucide="package-search" class="w-10 h-10 text-slate-600 mx-auto mb-3"></i>
                <h4 class="text-white font-bold text-sm">Belum Ada Produk Dipilih</h4>
                <p class="text-xs text-slate-400 mt-1">Pilih salah satu produk pada menu dropdown di atas.</p>
            </div>
        </template>
    </div>

    <!-- ========================================== -->
    <!-- MODAL SIMPAN JADI PRODUK BARU              -->
    <!-- ========================================== -->
    <div x-show="showQuickSaveModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80" style="display: none;">
        <div @click.away="showQuickSaveModal = false" class="glass-card rounded-3xl p-6 border border-emerald-500/40 max-w-md w-full space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h4 class="font-black text-white text-base flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-5 h-5 text-emerald-400"></i>
                    <span>Simpan Menjadi Produk Baru</span>
                </h4>
                <button type="button" @click="showQuickSaveModal = false" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <p class="text-xs text-slate-300 leading-relaxed">
                Produk ini akan langsung didaftarkan ke katalog master produk dan siap digunakan di terminal kasir POS.
            </p>

            <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-400">Nama Produk:</span>
                    <span class="font-bold text-white" x-text="quickName"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Modal Bersih (HPP):</span>
                    <span class="font-mono font-bold text-slate-200">Rp <span x-text="quickTotalHpp.toLocaleString('id-ID')"></span></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Harga Jual Kasir:</span>
                    <span class="font-mono font-black text-emerald-400">Rp <span x-text="quickOfflinePrice.toLocaleString('id-ID')"></span></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Estimasi Untung/Pcs:</span>
                    <span class="font-mono font-bold text-teal-300">+ Rp <span x-text="quickOfflineProfit.toLocaleString('id-ID')"></span> (<span x-text="quickMargin"></span>%)</span>
                </div>
            </div>

            <template x-if="quickSaveSuccessMsg">
                <div class="p-3 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-bold flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span x-text="quickSaveSuccessMsg"></span>
                </div>
            </template>

            <template x-if="quickSaveErrorMsg">
                <div class="p-3 rounded-xl bg-rose-500/20 text-rose-300 border border-rose-500/30 text-xs font-bold flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    <span x-text="quickSaveErrorMsg"></span>
                </div>
            </template>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" @click="showQuickSaveModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold">
                    Batal
                </button>
                <button type="button" @click="saveQuickProduct()" :disabled="quickSaveLoading"
                        class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-black shadow transition flex items-center gap-2 disabled:opacity-50">
                    <span x-text="quickSaveLoading ? 'Menyimpan...' : 'Ya, Daftarkan Produk'"></span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
