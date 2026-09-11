@extends('layouts.app', [
    'title' => 'Kalkulator HPP — Cooca UMKM',
    'headerTitle' => 'Kalkulator HPP & Penetapan Harga',
    'headerSubtitle' => 'Hitung modal bersih per porsi/pcs secara mudah dan tentukan harga jual yang menguntungkan'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
    activeTab: '{{ $tab }}', // 'quick' or 'advanced'

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
            if (window.AppAlert) {
                AppAlert.warning('Silakan masukkan nama produk terlebih dahulu.');
            } else {
                alert('Silakan masukkan nama produk terlebih dahulu.');
            }
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
                }, 1800);
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
    selectedProductId: '{{ $selectedProductId ?? '' }}',
    selectedProduct: null,
    selectedCostModel: null,
    isCalculating: false,
    calcResult: null,

    // Advanced Costing Run Save Modal State
    showSaveModal: false,
    isSaving: false,
    saveNotes: '',
    saveSuccessMsg: '',
    saveErrorMsg: '',

    isApplying: false,
    applySuccessMsg: '',
    applyErrorMsg: '',

    init() {
        // If a product was passed via URL (?product_id=...), preselect it.
        const preselected = this.selectedProductId;
        if (preselected && this.products.some(p => p.id === preselected)) {
            this.selectProduct(preselected);
        } else if (this.activeTab === 'advanced' && this.products.length > 0) {
            // Otherwise fallback to first product in advanced mode
            this.selectProduct(this.products[0].id);
        }

        this.$nextTick(() => {
            if (window.lucide) {
                lucide.createIcons();
            }
        });
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
        this.$nextTick(() => {
            if (window.lucide) {
                lucide.createIcons();
            }
        });
    },

    runCalculation() {
        if (!this.selectedCostModel) return;
        this.isCalculating = true;
        fetch(`/calculator/calculate/${this.selectedCostModel.id}`)
            .then(res => res.json())
            .then(data => {
                this.calcResult = data.result;
                this.isCalculating = false;
                this.$nextTick(() => {
                    if (window.lucide) {
                        lucide.createIcons();
                    }
                });
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
                if (window.coocaToast) {
                    window.coocaToast(data.message, 'success');
                }
                setTimeout(() => { this.applySuccessMsg = ''; }, 4000);
            } else {
                this.applyErrorMsg = data.message || 'Gagal menerapkan harga.';
                if (window.coocaToast) {
                    window.coocaToast(this.applyErrorMsg, 'error');
                }
            }
        })
        .catch(() => {
            this.isApplying = false;
            this.applyErrorMsg = 'Gagal memproses permohonan.';
            if (window.coocaToast) {
                window.coocaToast(this.applyErrorMsg, 'error');
            }
        });
    },

    saveResult() {
        if (!this.selectedCostModel) return;
        this.isSaving = true;
        this.saveSuccessMsg = '';
        this.saveErrorMsg = '';

        const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';

        fetch('/calculator/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                cost_model_id: this.selectedCostModel.id,
                notes: this.saveNotes
            })
        })
        .then(res => res.json())
        .then(data => {
            this.isSaving = false;
            if (data.success) {
                this.saveSuccessMsg = data.message;
                if (data.run) {
                    this.recentRuns.unshift(data.run);
                }
                if (window.coocaToast) {
                    window.coocaToast(data.message, 'success');
                }
                setTimeout(() => {
                    this.showSaveModal = false;
                    this.saveSuccessMsg = '';
                    this.saveNotes = '';
                }, 1800);
            } else {
                this.saveErrorMsg = data.message || 'Gagal menyimpan riwayat kalkulasi.';
                if (window.coocaToast) {
                    window.coocaToast(this.saveErrorMsg, 'error');
                }
            }
        })
        .catch(() => {
            this.isSaving = false;
            this.saveErrorMsg = 'Terjadi kesalahan sistem saat menyimpan.';
            if (window.coocaToast) {
                window.coocaToast(this.saveErrorMsg, 'error');
            }
        });
    }
}">

    <!-- ========================================== -->
    <!-- 0. BREADCRUMB BAR (APPLE MINIMALIST)       -->
    <!-- ========================================== -->
    <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 py-0.5 whitespace-nowrap print:hidden" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors font-medium">Dashboard</a>
        <span>›</span>
        <span class="text-black/60 dark:text-white/60 font-medium">Kalkulator Bisnis</span>
        <span>›</span>
        <span class="text-black/80 dark:text-white/80 font-medium">Kalkulator HPP &amp; Penetapan Harga</span>
    </nav>

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
    <!-- ===================================================== -->
    <header class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5 transition-colors shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
        <div class="space-y-1.5 max-w-2xl">
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1.5 bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span>
                    <span>Biaya &amp; Penetapan Harga</span>
                </span>
                <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1.5 bg-[#AF52DE]/10 text-[#AF52DE] dark:text-[#BF5AF2]">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#AF52DE]"></span>
                    <span>Smart Profit Coach</span>
                </span>
            </div>
            <h1 class="text-[20px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight">
                Kalkulator HPP &amp; Strategi Harga Jual
            </h1>
            <p class="text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                Hitung modal bersih per porsi/unit (Bahan Baku, Tenaga Kerja, Operasional) secara presisi, tentukan margin keuntungan sehat, dan simulasi harga jual online tanpa boncos potongan komisi aplikasi.
            </p>
        </div>

        <!-- Toolbar Action Buttons (Apple HIG Gray Buttons) -->
        <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2 w-full lg:w-auto">
            <a href="{{ route('calculator.export-excel') }}"
               class="col-span-1 h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="download" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                <span>Export (.CSV)</span>
            </a>
            <a href="{{ route('products.index') }}"
               class="col-span-1 h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="package" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                <span>Katalog Produk</span>
            </a>
        </div>
    </header>

    <!-- ===================================================== -->
    <!-- 2. SEGMENTED MODE SWITCHER (Apple Segmented Control)  -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 sm:p-3.5 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
        <div class="inline-flex p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] border border-black/5 dark:border-white/5 w-full sm:w-auto text-[13px] font-medium">
            <button type="button" @click="activeTab = 'quick'; $nextTick(() => { if (window.lucide) lucide.createIcons(); })"
                    :class="activeTab === 'quick' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                    class="h-8 px-4 rounded-[9px] transition-all flex items-center justify-center gap-2 flex-1 sm:flex-none cursor-pointer active:scale-[0.97] active:opacity-80">
                <i data-lucide="zap" class="w-3.5 h-3.5 text-[#FF9500] dark:text-[#FF9F0A]"></i>
                <span>Mode Cepat (3 Pilar HPP)</span>
                <span class="rounded-full px-1.5 py-0.2 text-[10px] font-semibold bg-[#007AFF]/10 text-[#007AFF]">Simpel</span>
            </button>

            <button type="button" @click="activeTab = 'advanced'; $nextTick(() => { if (window.lucide) lucide.createIcons(); })"
                    :class="activeTab === 'advanced' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                    class="h-8 px-4 rounded-[9px] transition-all flex items-center justify-center gap-2 flex-1 sm:flex-none cursor-pointer active:scale-[0.97] active:opacity-80">
                <i data-lucide="layers" class="w-3.5 h-3.5 text-[#5856D6] dark:text-[#5E5CE6]"></i>
                <span>Mode Detail Resep (BOM)</span>
            </button>
        </div>

        <div class="flex items-center gap-2 text-[13px] text-black/50 dark:text-white/50 px-1">
            <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
            <span class="text-[12px] sm:text-[13px]">
                <span x-show="activeTab === 'quick'">Simulasi instan 3 pilar modal tanpa perlu input katalog</span>
                <span x-show="activeTab === 'advanced'">Kalkulasi otomatis dari Bill of Materials &amp; alokasi mesin</span>
            </span>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 1: MODE CEPAT (3 PILAR HPP)             -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'quick'" class="space-y-6">

        <!-- 4-Pillar KPI Summary Row (Apple Flat Neutral Material) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <!-- Pillar 1: Total Modal (HPP) -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Modal (HPP)</span>
                        <div class="w-7 h-7 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.08] flex items-center justify-center text-black/60 dark:text-white/60">
                            <i data-lucide="calculator" class="w-3.5 h-3.5"></i>
                        </div>
                    </div>
                    <div class="text-[20px] sm:text-[26px] font-bold tabular-nums text-black dark:text-white tracking-tight truncate">
                        Rp <span x-text="quickTotalHpp.toLocaleString('id-ID')"></span>
                    </div>
                </div>
                <div class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 text-[11px] text-black/50 dark:text-white/50 flex items-center justify-between">
                    <span>Bahan: <strong class="tabular-nums font-semibold text-black dark:text-white" x-text="quickMaterialPct + '%'"></strong></span>
                    <span class="font-medium text-black/40 dark:text-white/40">Per Unit</span>
                </div>
            </div>

            <!-- Pillar 2: Rekomendasi Kasir (System Blue) -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Rekomendasi Kasir</span>
                        <div class="w-7 h-7 rounded-[8px] bg-[#007AFF]/10 flex items-center justify-center text-[#007AFF]">
                            <i data-lucide="store" class="w-3.5 h-3.5"></i>
                        </div>
                    </div>
                    <div class="text-[20px] sm:text-[26px] font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF] tracking-tight truncate">
                        Rp <span x-text="quickOfflinePrice.toLocaleString('id-ID')"></span>
                    </div>
                </div>
                <div class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 text-[11px] text-black/50 dark:text-white/50 flex items-center justify-between">
                    <span>Markup: <strong class="tabular-nums font-semibold text-black dark:text-white" x-text="quickMarkupPct + '%'"></strong></span>
                    <span class="font-medium text-[#007AFF]">Toko Offline</span>
                </div>
            </div>

            <!-- Pillar 3: Untung Bersih / Pcs (System Green) -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Untung Bersih / Pcs</span>
                        <div class="w-7 h-7 rounded-[8px] bg-[#34C759]/12 flex items-center justify-center text-[#34C759] dark:text-[#30D158]">
                            <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                        </div>
                    </div>
                    <div class="text-[20px] sm:text-[26px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] tracking-tight truncate">
                        + Rp <span x-text="quickOfflineProfit.toLocaleString('id-ID')"></span>
                    </div>
                </div>
                <div class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 text-[11px] text-black/50 dark:text-white/50 flex items-center justify-between">
                    <span>Margin: <strong class="tabular-nums font-semibold text-[#34C759] dark:text-[#30D158]" x-text="quickMargin + '%'"></strong></span>
                    <span class="font-medium text-[#34C759] dark:text-[#30D158]">Nett Profit</span>
                </div>
            </div>

            <!-- Pillar 4: Target BEP (System Indigo) -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Target Balik Modal (BEP)</span>
                        <div class="w-7 h-7 rounded-[8px] bg-[#5856D6]/10 flex items-center justify-center text-[#5856D6] dark:text-[#5E5CE6]">
                            <i data-lucide="target" class="w-3.5 h-3.5"></i>
                        </div>
                    </div>
                    <div class="text-[20px] sm:text-[26px] font-bold tabular-nums text-black dark:text-white tracking-tight truncate">
                        <span x-text="quickBepUnitsDaily"></span> <span class="text-[13px] font-medium text-black/50 dark:text-white/50">pcs/hari</span>
                    </div>
                </div>
                <div class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 text-[11px] text-black/50 dark:text-white/50 flex items-center justify-between">
                    <span>Bulan: <strong class="tabular-nums font-semibold text-black dark:text-white" x-text="quickBepUnitsMonthly"></strong> pcs</span>
                    <span class="font-medium text-[#5856D6]">Beban Rutin</span>
                </div>
            </div>
        </div>

        <!-- 1-Click Quick Preset Chips (Apple Pill Style) -->
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-0.5 whitespace-nowrap text-[12px]">
            <span class="text-black/40 dark:text-white/40 font-semibold uppercase tracking-wider text-[11px] shrink-0 flex items-center gap-1.5 px-1">
                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                <span>Preset Cepat:</span>
            </span>
            <button type="button" @click="applyPreset('kopi')" class="h-8 px-3 rounded-full text-[12px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all cursor-pointer flex items-center gap-1.5 shrink-0">
                <span>☕ Kopi Susu Aren</span>
            </button>
            <button type="button" @click="applyPreset('geprek')" class="h-8 px-3 rounded-full text-[12px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all cursor-pointer flex items-center gap-1.5 shrink-0">
                <span>🍗 Ayam Geprek Nasi</span>
            </button>
            <button type="button" @click="applyPreset('kaos')" class="h-8 px-3 rounded-full text-[12px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all cursor-pointer flex items-center gap-1.5 shrink-0">
                <span>👕 Kaos Sablon Distro</span>
            </button>
            <button type="button" @click="applyPreset('kue')" class="h-8 px-3 rounded-full text-[12px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all cursor-pointer flex items-center gap-1.5 shrink-0">
                <span>🍰 Brownies Panggang</span>
            </button>
        </div>

        <!-- Main 3-Pillar Calculator Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- LEFT COLUMN: 3 PILLARS INPUT (7 cols) -->
            <div class="lg:col-span-7 space-y-5">

                <!-- Product Name Input Card -->
                <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] space-y-2">
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 uppercase tracking-wide flex items-center gap-1.5">
                        <i data-lucide="tag" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                        <span>Nama Produk / Menu Yang Dihitung *</span>
                    </label>
                    <input type="text" x-model="quickName" placeholder="Contoh: Kopi Susu Gula Aren 250ml"
                           class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[10px] px-3.5 text-[15px] font-semibold text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition">
                </div>

                <!-- The 3 Pillars of HPP Container -->
                <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 shadow-[0_1px_2px_rgba(0,0,0,0.02)] space-y-4">
                    <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                        <h2 class="text-[14px] font-semibold text-black dark:text-white flex items-center gap-2">
                            <i data-lucide="calculator" class="w-4 h-4 text-[#007AFF]"></i>
                            <span>3 Komponen Modal Bersih (HPP)</span>
                        </h2>
                        <span class="text-[12px] text-black/40 dark:text-white/40">Per 1 Porsi / Pcs</span>
                    </div>

                    <!-- PILAR 1: Bahan & Kemasan (Teal Accent) -->
                    <div class="p-4 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden group">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-6 h-6 rounded-[6px] bg-[#30B0C7] text-white flex items-center justify-center font-bold text-[11px]">
                                    1
                                </div>
                                <div>
                                    <div class="font-semibold text-[13px] text-black dark:text-white">Biaya Bahan Baku &amp; Kemasan</div>
                                    <div class="text-[11px] text-black/50 dark:text-white/50">Bahan utama, bumbu, cup, botol, plastik, label kemasan</div>
                                </div>
                            </div>
                            <span class="text-[12px] font-semibold tabular-nums text-[#30B0C7] dark:text-[#40C8E0]" x-text="quickMaterialPct + '%'"></span>
                        </div>
                        <div class="relative pt-1">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40 font-semibold text-[14px]">
                                Rp
                            </div>
                            <input type="number" x-model.number="quickMaterial" min="0" step="500"
                                   class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[10px] pl-10 pr-3.5 text-[16px] font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition">
                        </div>
                    </div>

                    <!-- PILAR 2: Tenaga Kerja / Upah (Indigo Accent) -->
                    <div class="p-4 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden group">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-6 h-6 rounded-[6px] bg-[#5856D6] text-white flex items-center justify-center font-bold text-[11px]">
                                    2
                                </div>
                                <div>
                                    <div class="font-semibold text-[13px] text-black dark:text-white">Biaya Upah &amp; Tenaga Kerja</div>
                                    <div class="text-[11px] text-black/50 dark:text-white/50">Ongkos masak, barista, penjahit per pcs (isi 0 jika dikerjakan mandiri)</div>
                                </div>
                            </div>
                            <span class="text-[12px] font-semibold tabular-nums text-[#5856D6] dark:text-[#5E5CE6]" x-text="quickLaborPct + '%'"></span>
                        </div>
                        <div class="relative pt-1">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40 font-semibold text-[14px]">
                                Rp
                            </div>
                            <input type="number" x-model.number="quickLabor" min="0" step="500"
                                   class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[10px] pl-10 pr-3.5 text-[16px] font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition">
                        </div>
                    </div>

                    <!-- PILAR 3: Operasional / Utilitas (Orange Accent) -->
                    <div class="p-4 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden group">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-6 h-6 rounded-[6px] bg-[#FF9500] text-white flex items-center justify-center font-bold text-[11px]">
                                    3
                                </div>
                                <div>
                                    <div class="font-semibold text-[13px] text-black dark:text-white">Biaya Operasional &amp; Utilitas</div>
                                    <div class="text-[11px] text-black/50 dark:text-white/50">Alokasi gas elpiji, listrik, air, sewa tempat per porsi</div>
                                </div>
                            </div>
                            <span class="text-[12px] font-semibold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]" x-text="quickOverheadPct + '%'"></span>
                        </div>
                        <div class="relative pt-1">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40 font-semibold text-[14px]">
                                Rp
                            </div>
                            <input type="number" x-model.number="quickOverhead" min="0" step="500"
                                   class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[10px] pl-10 pr-3.5 text-[16px] font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition">
                        </div>
                    </div>

                    <!-- Visual Proportion Bar (Apple Continuous Bar) -->
                    <div class="space-y-2 pt-3 border-t border-black/5 dark:border-white/10">
                        <div class="flex justify-between text-[12px] font-medium">
                            <span class="text-black/60 dark:text-white/60">Komposisi Pengeluaran Modal:</span>
                            <span class="tabular-nums font-semibold text-black dark:text-white">Total: Rp <span x-text="quickTotalHpp.toLocaleString('id-ID')"></span></span>
                        </div>
                        <div class="w-full h-2.5 rounded-full bg-black/[0.06] dark:bg-white/[0.08] overflow-hidden flex">
                            <div :style="'width: ' + quickMaterialPct + '%'" class="bg-[#30B0C7] transition-all duration-300" title="Bahan Baku"></div>
                            <div :style="'width: ' + quickLaborPct + '%'" class="bg-[#5856D6] transition-all duration-300" title="Upah Kerja"></div>
                            <div :style="'width: ' + quickOverheadPct + '%'" class="bg-[#FF9500] transition-all duration-300" title="Operasional"></div>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-2 text-[11px] text-black/50 dark:text-white/50 pt-1">
                            <div class="flex items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-[#30B0C7]"></div><span>Bahan (<span class="tabular-nums font-medium" x-text="quickMaterialPct + '%'"></span>)</span></div>
                            <div class="flex items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-[#5856D6]"></div><span>Upah (<span class="tabular-nums font-medium" x-text="quickLaborPct + '%'"></span>)</span></div>
                            <div class="flex items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-[#FF9500]"></div><span>Operasional (<span class="tabular-nums font-medium" x-text="quickOverheadPct + '%'"></span>)</span></div>
                        </div>
                    </div>
                </div>

                <!-- Human-Centric Narrative Card (Apple HIG Callout Style) -->
                <div class="p-4 sm:p-5 rounded-[14px] bg-[#AF52DE]/5 dark:bg-[#AF52DE]/10 border border-[#AF52DE]/15 flex items-start gap-3.5">
                    <div class="w-8 h-8 rounded-[8px] bg-[#AF52DE]/10 text-[#AF52DE] dark:text-[#BF5AF2] flex items-center justify-center shrink-0 mt-0.5">
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                    </div>
                    <div class="space-y-0.5 text-[13px]">
                        <h3 class="font-semibold text-black dark:text-white">Kesimpulan Modal Bersih</h3>
                        <p class="text-black/70 dark:text-white/70 leading-relaxed text-[13px]">
                            Untuk menghasilkan 1 porsi <strong class="text-black dark:text-white font-semibold" x-text="quickName"></strong>, modal riil yang Anda keluarkan adalah <strong class="text-[#34C759] dark:text-[#30D158] tabular-nums font-bold">Rp <span x-text="quickTotalHpp.toLocaleString('id-ID')"></span></strong>.
                            Sebesar <strong class="text-black dark:text-white tabular-nums font-bold" x-text="quickMaterialPct + '%'"></strong> uang terserap untuk bahan baku &amp; kemasan.
                        </p>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: SMART PRICING & PROFIT STRATEGY (5 cols) -->
            <div class="lg:col-span-5 space-y-5">

                <!-- Pricing & Profit Engine Card -->
                <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 shadow-[0_1px_2px_rgba(0,0,0,0.02)] space-y-5 lg:sticky lg:top-24 transition-colors">
                    <div class="border-b border-black/5 dark:border-white/10 pb-3 flex items-center justify-between">
                        <h2 class="text-[14px] font-semibold text-black dark:text-white flex items-center gap-2">
                            <i data-lucide="trending-up" class="w-4 h-4 text-[#34C759]"></i>
                            <span>Target Untung &amp; Harga Jual</span>
                        </h2>
                        <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                            Smart Coach
                        </span>
                    </div>

                    <!-- Margin Slider Control -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[12px] font-medium text-black/60 dark:text-white/60">Target Margin Laba Bersih:</span>
                            <div class="flex items-baseline gap-0.5">
                                <span class="text-[24px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]" x-text="quickMargin"></span>
                                <span class="text-[13px] font-bold text-[#34C759] dark:text-[#30D158]">%</span>
                            </div>
                        </div>

                        <input type="range" x-model.number="quickMargin" min="10" max="80" step="5"
                               class="w-full h-2 bg-black/[0.06] dark:bg-white/[0.08] rounded-full appearance-none cursor-pointer accent-[#007AFF]">

                        <!-- Margin Health Indicator (Apple Tinted Callout) -->
                        <div class="p-3 rounded-[10px] text-[12px] flex items-center gap-2"
                             :class="{
                                 'bg-[#FF9500]/10 text-[#B25E00] dark:text-[#FF9F0A] border border-[#FF9500]/20': quickMargin < 25,
                                 'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20': quickMargin >= 25 && quickMargin <= 60,
                                 'bg-[#AF52DE]/10 text-[#7C3AA6] dark:text-[#BF5AF2] border border-[#AF52DE]/20': quickMargin > 60
                             }">
                            <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
                            <span x-show="quickMargin < 25">⚠️ <strong>Margin Tipis:</strong> Rawan rugi jika terjadi kenaikan harga bahan baku supplier.</span>
                            <span x-show="quickMargin >= 25 && quickMargin <= 60">✨ <strong>Sehat &amp; Ideal:</strong> Standar rasio laba kotor UMKM, kuliner &amp; ritel.</span>
                            <span x-show="quickMargin > 60">💎 <strong>Margin Premium:</strong> Keuntungan tinggi, pastikan kemasan &amp; kualitas rasa bersaing.</span>
                        </div>
                    </div>

                    <!-- Pricing Result Box -->
                    <div class="p-4 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3">
                        <div>
                            <div class="text-[11px] font-medium text-black/50 dark:text-white/50">Harga Jual Rekomendasi (Kasir / Toko)</div>
                            <div class="text-[26px] sm:text-[30px] font-bold tabular-nums text-black dark:text-white tracking-tight mt-0.5">
                                Rp <span x-text="quickOfflinePrice.toLocaleString('id-ID')"></span>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-black/5 dark:border-white/10 space-y-1.5 text-[12px]">
                            <div class="flex justify-between items-center">
                                <span class="text-black/60 dark:text-white/60">Untung Bersih per Pcs:</span>
                                <span class="tabular-nums font-bold text-[#34C759] dark:text-[#30D158]">+ Rp <span x-text="quickOfflineProfit.toLocaleString('id-ID')"></span></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-black/60 dark:text-white/60">Markup dari Modal:</span>
                                <span class="tabular-nums font-semibold text-black dark:text-white"><span x-text="quickMarkupPct"></span>% dari HPP</span>
                            </div>
                        </div>
                    </div>

                    <!-- Online Food / Marketplace Simulator Switch -->
                    <div class="p-4 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i data-lucide="bike" class="w-4 h-4 text-[#AF52DE]"></i>
                                <span class="text-[13px] font-semibold text-black dark:text-white">Jual di Ojek Online? (GoFood/Grab)</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" x-model="quickSellOnline" class="sr-only peer">
                                <div class="w-11 h-6 bg-black/[0.12] dark:bg-white/[0.15] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]"></div>
                            </label>
                        </div>

                        <div x-show="quickSellOnline" class="space-y-3 pt-3 border-t border-black/5 dark:border-white/10 text-[12px]" style="display: none;">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-black/60 dark:text-white/60">Komisi Aplikasi:</span>
                                <select x-model.number="quickOnlineFeePct" class="h-8 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[8px] px-2.5 text-black dark:text-white text-[12px] font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition">
                                    <option :value="20">20% (Standar GoFood / GrabFood / ShopeeFood)</option>
                                    <option :value="15">15% (Promo Merchant)</option>
                                    <option :value="10">10% (Marketplace Toko Online)</option>
                                </select>
                            </div>

                            <div class="p-3.5 rounded-[10px] bg-[#AF52DE]/10 border border-[#AF52DE]/20 space-y-1">
                                <div class="text-[11px] text-[#AF52DE] dark:text-[#BF5AF2] font-semibold">Harga Wajib Pasang di Aplikasi Online:</div>
                                <div class="text-[22px] font-bold tabular-nums text-[#AF52DE] dark:text-[#BF5AF2]">
                                    Rp <span x-text="quickOnlinePrice.toLocaleString('id-ID')"></span>
                                </div>
                                <p class="text-[11px] text-black/60 dark:text-white/60 leading-tight pt-1">
                                    Pasang Rp <span class="tabular-nums font-semibold" x-text="quickOnlinePrice.toLocaleString('id-ID')"></span> &rarr; potongan komisi 20% (Rp <span class="tabular-nums" x-text="quickOnlineFeeNominal.toLocaleString('id-ID')"></span>) &rarr; omzet bersih <strong class="text-black dark:text-white tabular-nums">tetap utuh Rp <span x-text="quickOfflinePrice.toLocaleString('id-ID')"></span></strong> tanpa boncos komisi!
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Mini BEP (Break-Even Point) Tracker -->
                    <div class="p-4 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2.5 text-[12px]">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-black dark:text-white flex items-center gap-1.5">
                                <i data-lucide="target" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Target Balik Modal (BEP)</span>
                            </span>
                            <span class="text-[11px] text-black/40 dark:text-white/40">Beban Rutin</span>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-black/60 dark:text-white/60 text-[11px]">Beban Sewa/Listrik Bln:</span>
                            <input type="number" x-model.number="quickMonthlyFixedCost" step="100000"
                                   class="w-full sm:w-36 h-8 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[8px] px-2.5 text-right text-[12px] font-semibold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition">
                        </div>
                        <div class="pt-1 text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                            💡 Jual minimal <strong class="text-[#34C759] dark:text-[#30D158] font-semibold tabular-nums"><span x-text="quickBepUnitsDaily"></span> pcs/hari</strong> (atau <span class="tabular-nums font-semibold" x-text="quickBepUnitsMonthly"></span> pcs/bulan) agar impas beban rutin. Penjualan berikutnya adalah <strong class="text-black dark:text-white">keuntungan bersih Anda!</strong>
                        </div>
                    </div>

                    <!-- Save as Product Button (Apple Primary Filled Button) -->
                    <button type="button" @click="showQuickSaveModal = true"
                            class="w-full h-11 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 shadow-[0_1px_2px_rgba(0,122,255,0.25)] transition-all cursor-pointer flex items-center justify-center gap-2 group">
                        <i data-lucide="plus-circle" class="w-4 h-4 group-hover:scale-105 transition-transform"></i>
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
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 shadow-[0_1px_2px_rgba(0,0,0,0.02)] flex flex-col md:flex-row items-start md:items-center justify-between gap-4 transition-colors">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-[10px] bg-[#5856D6]/10 text-[#5856D6] dark:text-[#5E5CE6] flex items-center justify-center shrink-0">
                    <i data-lucide="layers" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-[14px] font-semibold text-black dark:text-white">Pilih Produk Dari Katalog Master</h2>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Sistem otomatis menghitung resep terperinci (BOM) dan alokasi tarif mesin</p>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap w-full md:w-auto">
                <select x-model="selectedProductId" @change="selectProduct($event.target.value)"
                        class="h-9 px-3 bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[10px] text-[13px] font-medium text-black dark:text-white w-full sm:w-auto min-w-0 sm:min-w-[260px] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition">
                    <option value="">-- Pilih Produk Terdaftar --</option>
                    <template x-for="p in products" :key="p.id">
                        <option :value="p.id" x-text="p.name + ' (' + (p.output_unit ? p.output_unit.name : 'pcs') + ')'"></option>
                    </template>
                </select>

                <template x-if="selectedProduct">
                    <a :href="'/products/' + selectedProduct.slug + '/bom'"
                       class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5 text-black/60 dark:text-white/60"></i>
                        <span>Edit Resep BOM</span>
                    </a>
                </template>
                <template x-if="selectedProduct">
                    <a :href="'/products?edit=' + selectedProduct.id"
                       class="h-9 px-3.5 rounded-[10px] text-[13px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                        <i data-lucide="pencil-line" class="w-3.5 h-3.5"></i>
                        <span>Edit Harga</span>
                    </a>
                </template>
            </div>
        </div>

        <!-- BOM Calculation Results View -->
        <template x-if="selectedProduct && calcResult">
            <div class="space-y-6">
                <!-- Result 4 Bento Cards (Apple HIG Style) -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">HPP per Unit</span>
                                <div class="w-7 h-7 rounded-[8px] bg-[#34C759]/12 flex items-center justify-center text-[#34C759] dark:text-[#30D158]">
                                    <i data-lucide="coins" class="w-3.5 h-3.5"></i>
                                </div>
                            </div>
                            <div class="text-[20px] sm:text-[26px] font-bold tabular-nums text-black dark:text-white tracking-tight truncate">
                                Rp <span x-text="Math.round(calcResult.hpp_per_unit).toLocaleString('id-ID')"></span>
                            </div>
                        </div>
                        <div class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 text-[11px] text-black/50 dark:text-white/50">
                            Satuan: <strong class="text-black dark:text-white font-medium" x-text="selectedProduct.output_unit ? selectedProduct.output_unit.name : 'pcs'"></strong>
                        </div>
                    </div>

                    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Bahan Baku (Material)</span>
                                <div class="w-7 h-7 rounded-[8px] bg-[#007AFF]/10 flex items-center justify-center text-[#007AFF]">
                                    <i data-lucide="package" class="w-3.5 h-3.5"></i>
                                </div>
                            </div>
                            <div class="text-[20px] sm:text-[26px] font-bold tabular-nums text-black dark:text-white tracking-tight truncate">
                                Rp <span x-text="Math.round(calcResult.total_material_cost).toLocaleString('id-ID')"></span>
                            </div>
                        </div>
                        <div class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 text-[11px] text-[#007AFF] font-semibold tabular-nums">
                            <span x-text="((calcResult.total_material_cost / calcResult.total_hpp) * 100).toFixed(1) + '% porsi modal'"></span>
                        </div>
                    </div>

                    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Upah Kerja &amp; Mesin</span>
                                <div class="w-7 h-7 rounded-[8px] bg-[#5856D6]/10 flex items-center justify-center text-[#5856D6] dark:text-[#5E5CE6]">
                                    <i data-lucide="users" class="w-3.5 h-3.5"></i>
                                </div>
                            </div>
                            <div class="text-[20px] sm:text-[26px] font-bold tabular-nums text-black dark:text-white tracking-tight truncate">
                                Rp <span x-text="Math.round(calcResult.total_labor_cost + calcResult.total_machine_cost).toLocaleString('id-ID')"></span>
                            </div>
                        </div>
                        <div class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 text-[11px] text-black/50 dark:text-white/50">
                            Tenaga Kerja &amp; Utilitas
                        </div>
                    </div>

                    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Alokasi Overhead</span>
                                <div class="w-7 h-7 rounded-[8px] bg-[#FF9500]/10 flex items-center justify-center text-[#FF9500] dark:text-[#FF9F0A]">
                                    <i data-lucide="pie-chart" class="w-3.5 h-3.5"></i>
                                </div>
                            </div>
                            <div class="text-[20px] sm:text-[26px] font-bold tabular-nums text-black dark:text-white tracking-tight truncate">
                                Rp <span x-text="Math.round(calcResult.total_overhead_cost).toLocaleString('id-ID')"></span>
                            </div>
                        </div>
                        <div class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 text-[11px] text-black/50 dark:text-white/50">
                            Pabrikasi / Operasional
                        </div>
                    </div>
                </div>

                <!-- High-Density Breakdown Table (Apple HIG Dense Table) -->
                <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                    <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div>
                            <h2 class="text-[14px] font-semibold text-black dark:text-white flex items-center gap-2">
                                <i data-lucide="list-checks" class="w-4 h-4 text-[#5856D6]"></i>
                                <span>Rincian Komponen Resep Terdaftar</span>
                            </h2>
                            <p class="text-[12px] text-black/50 dark:text-white/50">Komposisi perhitungan HPP berdasarkan kartu Bill of Materials (BOM)</p>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap w-full sm:w-auto">
                            <button type="button" @click="showSaveModal = true"
                                    class="h-8 px-3 rounded-[8px] text-[12px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 flex-1 sm:flex-none">
                                <i data-lucide="bookmark" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Simpan Riwayat</span>
                            </button>
                            <button type="button" @click="applyToProduct()" :disabled="isApplying"
                                    class="h-8 px-3.5 rounded-[8px] text-[12px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 disabled:opacity-50 flex-1 sm:flex-none">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                <span x-text="isApplying ? 'Menerapkan...' : 'Terapkan ke Produk'"></span>
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-[13px]">
                            <thead>
                                <tr class="border-b border-black/5 dark:border-white/10 text-black/40 dark:text-white/40 uppercase tracking-wide text-[11px] font-semibold">
                                    <th class="px-4 py-2.5 w-12 text-center">No</th>
                                    <th class="px-4 py-2.5">Nama Komponen / Bahan</th>
                                    <th class="px-4 py-2.5">Kategori</th>
                                    <th class="px-4 py-2.5 text-right">Biaya Nominal (HPP)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                                <template x-for="(item, idx) in calcResult.breakdown" :key="idx">
                                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                        <td class="px-4 py-3 text-center text-black/40 dark:text-white/40 tabular-nums" x-text="idx + 1"></td>
                                        <td class="px-4 py-3 font-medium text-black dark:text-white" x-text="item.name"></td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold"
                                                  :class="{
                                                      'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]': item.type === 'material',
                                                      'bg-[#5856D6]/12 text-[#413FA6] dark:text-[#5E5CE6]': item.type === 'labor',
                                                      'bg-[#007AFF]/12 text-[#0062CC] dark:text-[#0A84FF]': item.type === 'machine',
                                                      'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]': item.type === 'overhead'
                                                  }">
                                                <span class="w-1.5 h-1.5 rounded-full"
                                                      :class="{
                                                          'bg-[#34C759]': item.type === 'material',
                                                          'bg-[#5856D6]': item.type === 'labor',
                                                          'bg-[#007AFF]': item.type === 'machine',
                                                          'bg-[#FF9500]': item.type === 'overhead'
                                                      }"></span>
                                                <span class="capitalize" x-text="item.type"></span>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold tabular-nums text-black dark:text-white">
                                            Rp <span x-text="Math.round(item.cost).toLocaleString('id-ID')"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-black/10 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] font-semibold text-[13px]">
                                    <td colspan="3" class="px-4 py-3 text-right uppercase tracking-wide text-[11px] text-black/50 dark:text-white/50">
                                        Total HPP Keseluruhan:
                                    </td>
                                    <td class="px-4 py-3 text-right tabular-nums text-[#34C759] dark:text-[#30D158] text-[15px] font-bold">
                                        Rp <span x-text="Math.round(calcResult.total_hpp).toLocaleString('id-ID')"></span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Recent Runs Table (Persisted Costing Runs) -->
                <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                    <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                        <div>
                            <h3 class="text-[14px] font-semibold text-black dark:text-white flex items-center gap-2">
                                <i data-lucide="history" class="w-4 h-4 text-black/50 dark:text-white/50"></i>
                                <span>Riwayat Kalkulasi HPP Tersimpan</span>
                            </h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50">Arsip riwayat kalkulasi yang pernah dicatat untuk audit biaya bisnis</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-[13px]">
                            <thead>
                                <tr class="border-b border-black/5 dark:border-white/10 text-black/40 dark:text-white/40 uppercase tracking-wide text-[11px] font-semibold">
                                    <th class="px-4 py-2.5">Tanggal</th>
                                    <th class="px-4 py-2.5">Produk Terkait</th>
                                    <th class="px-4 py-2.5">Catatan</th>
                                    <th class="px-4 py-2.5 text-right">HPP / Unit</th>
                                    <th class="px-4 py-2.5 text-right">Total HPP</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                                <template x-for="run in recentRuns" :key="run.id">
                                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                        <td class="px-4 py-3 text-black/50 dark:text-white/50 tabular-nums whitespace-nowrap text-[12px]" x-text="run.created_at"></td>
                                        <td class="px-4 py-3 font-medium text-black dark:text-white" x-text="run.product_name || (run.cost_model && run.cost_model.product ? run.cost_model.product.name : '-')"></td>
                                        <td class="px-4 py-3 text-black/60 dark:text-white/60 truncate max-w-xs text-[12px]" x-text="run.notes || '-'"></td>
                                        <td class="px-4 py-3 text-right font-bold tabular-nums text-[#34C759] dark:text-[#30D158] whitespace-nowrap">
                                            Rp <span x-text="Math.round(run.hpp_per_unit || (run.result ? run.result.hpp_per_unit : 0)).toLocaleString('id-ID')"></span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold tabular-nums text-black dark:text-white whitespace-nowrap">
                                            Rp <span x-text="Math.round(run.total_hpp || (run.result ? run.result.total_hpp : 0)).toLocaleString('id-ID')"></span>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="!recentRuns || recentRuns.length === 0">
                                    <td colspan="5" class="px-4 py-8 text-center text-black/40 dark:text-white/40 text-[13px]">
                                        Belum ada riwayat kalkulasi yang disimpan. Klik tombol "Simpan Riwayat" untuk mengarsipkan hasil kalkulasi.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </template>

        <!-- Empty State for Tab 2 (Apple HIG Empty State) -->
        <template x-if="!selectedProduct">
            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-12 sm:p-16 text-center shadow-[0_1px_2px_rgba(0,0,0,0.02)] space-y-3">
                <div class="w-14 h-14 rounded-[14px] bg-[#5856D6]/10 flex items-center justify-center mx-auto text-[#5856D6] dark:text-[#5E5CE6]">
                    <i data-lucide="package-search" class="w-7 h-7"></i>
                </div>
                <div class="space-y-1 max-w-md mx-auto">
                    <h3 class="text-[16px] font-semibold text-black dark:text-white">Belum Ada Produk Dipilih</h3>
                    <p class="text-[13px] text-black/50 dark:text-white/50 leading-relaxed">
                        Silakan pilih salah satu produk terdaftar pada menu dropdown di atas untuk melihat rincian kalkulasi bahan baku (BOM), tarif upah kerja, dan alokasi mesin secara otomatis.
                    </p>
                </div>
                <div class="pt-2 flex justify-center">
                    <a href="{{ route('products.index') }}"
                       class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                        <i data-lucide="plus-circle" class="w-4 h-4 text-[#007AFF]"></i>
                        <span>Kelola Master Produk &amp; Resep</span>
                    </a>
                </div>
            </div>
        </template>
    </div>

    <!-- ===================================================== -->
    <!-- MODAL 1: SIMPAN JADI PRODUK BARU (Apple Sheet Style)  -->
    <!-- ===================================================== -->
    <div x-show="showQuickSaveModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]"
         style="display: none;">
        <div @click.away="showQuickSaveModal = false"
             class="w-full max-w-md rounded-[18px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                <h3 class="text-[15px] font-semibold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Simpan Jadi Produk Baru</span>
                </h3>
                <button type="button" @click="showQuickSaveModal = false" class="w-7 h-7 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white transition">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </button>
            </div>

            <p class="text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                Produk ini akan langsung didaftarkan ke katalog master produk dan siap digunakan di terminal kasir POS.
            </p>

            <div class="p-4 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 space-y-2 text-[13px]">
                <div class="flex justify-between items-center">
                    <span class="text-black/50 dark:text-white/50">Nama Produk:</span>
                    <span class="font-semibold text-black dark:text-white truncate max-w-[200px]" x-text="quickName"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-black/50 dark:text-white/50">Modal Bersih (HPP):</span>
                    <span class="tabular-nums font-semibold text-black dark:text-white">Rp <span x-text="quickTotalHpp.toLocaleString('id-ID')"></span></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-black/50 dark:text-white/50">Harga Jual Kasir:</span>
                    <span class="tabular-nums font-bold text-[#007AFF] dark:text-[#0A84FF]">Rp <span x-text="quickOfflinePrice.toLocaleString('id-ID')"></span></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-black/50 dark:text-white/50">Estimasi Untung:</span>
                    <span class="tabular-nums font-bold text-[#34C759] dark:text-[#30D158]">+ Rp <span x-text="quickOfflineProfit.toLocaleString('id-ID')"></span> (<span x-text="quickMargin"></span>%)</span>
                </div>
            </div>

            <template x-if="quickSaveSuccessMsg">
                <div class="p-3 rounded-[10px] bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] text-[12px] font-medium flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                    <span x-text="quickSaveSuccessMsg"></span>
                </div>
            </template>

            <template x-if="quickSaveErrorMsg">
                <div class="p-3 rounded-[10px] bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A] text-[12px] font-medium flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <span x-text="quickSaveErrorMsg"></span>
                </div>
            </template>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" @click="showQuickSaveModal = false" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition">
                    Batal
                </button>
                <button type="button" @click="saveQuickProduct()" :disabled="quickSaveLoading"
                        class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition shadow-[0_1px_2px_rgba(0,122,255,0.25)] flex items-center gap-1.5 disabled:opacity-50">
                    <span x-text="quickSaveLoading ? 'Menyimpan...' : 'Ya, Daftarkan Produk'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- MODAL 2: SIMPAN RIWAYAT KALKULASI BOM (Apple Sheet)   -->
    <!-- ===================================================== -->
    <div x-show="showSaveModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]"
         style="display: none;">
        <div @click.away="showSaveModal = false"
             class="w-full max-w-md rounded-[18px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                <h3 class="text-[15px] font-semibold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="bookmark" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Simpan Riwayat Kalkulasi</span>
                </h3>
                <button type="button" @click="showSaveModal = false" class="w-7 h-7 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white transition">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </button>
            </div>

            <p class="text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                Simpan hasil kalkulasi HPP ini sebagai arsip resmi untuk melacak perubahan struktur biaya dan harga antar periode.
            </p>

            <div class="space-y-1.5">
                <label class="block text-[12px] font-medium text-black/70 dark:text-white/70">
                    Catatan Kalkulasi (Opsional)
                </label>
                <textarea x-model="saveNotes" rows="3" placeholder="Contoh: Penyesuaian tarif listrik &amp; harga gula triwulan 1"
                          class="w-full bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[10px] px-3.5 py-2.5 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition resize-none"></textarea>
            </div>

            <template x-if="saveSuccessMsg">
                <div class="p-3 rounded-[10px] bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] text-[12px] font-medium flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                    <span x-text="saveSuccessMsg"></span>
                </div>
            </template>

            <template x-if="saveErrorMsg">
                <div class="p-3 rounded-[10px] bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A] text-[12px] font-medium flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <span x-text="saveErrorMsg"></span>
                </div>
            </template>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" @click="showSaveModal = false" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition">
                    Batal
                </button>
                <button type="button" @click="saveResult()" :disabled="isSaving"
                        class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition shadow-[0_1px_2px_rgba(0,122,255,0.25)] flex items-center gap-1.5 disabled:opacity-50">
                    <span x-text="isSaving ? 'Menyimpan...' : 'Simpan Riwayat'"></span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
