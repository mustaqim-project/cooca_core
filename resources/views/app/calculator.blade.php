@extends('layouts.app', [
    'title' => __('calculator.title'),
    'headerTitle' => __('calculator.header_title'),
    'headerSubtitle' => __('calculator.header_subtitle'),
])

@section('content')
    @php
        $isFoodBiz = $business?->isFoodIndustry() ?? true;
        $isWorkshopBiz = $business?->isWorkshop() ?? false;
        $isLaundryBiz = $business?->isLaundry() ?? false;

        $initialQuickName = $isWorkshopBiz ? __('calculator.preset_workshop_oil') : ($isLaundryBiz ? __('calculator.preset_laundry_regular') : __('calculator.preset_fnb_coffee'));
        $initialQuickMat  = $isWorkshopBiz ? 45000 : ($isLaundryBiz ? 2500 : 4500);
        $initialQuickLab  = $isWorkshopBiz ? 25000 : ($isLaundryBiz ? 3000 : 1000);
        $initialQuickOver = $isWorkshopBiz ? 5000 : ($isLaundryBiz ? 1500 : 500);
        $initialQuickMargin = $isWorkshopBiz ? 35 : ($isLaundryBiz ? 40 : 40);
    @endphp

    <div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="{
        localeCode: '{{ app()->getLocale() === 'en' ? 'en-US' : 'id-ID' }}',
        activeTab: '{{ $tab }}', // 'quick' or 'advanced'

        setTab(tab) {
            this.activeTab = tab;
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url);
            this.$nextTick(() => {
                if (window.lucide) {
                    lucide.createIcons();
                }
            });
        },
    
        // QUICK MODE STATE (Adaptive Per Business Industry)
        quickName: {{ Js::from($initialQuickName) }},
        quickMaterial: {{ $initialQuickMat }},
        quickLabor: {{ $initialQuickLab }},
        quickOverhead: {{ $initialQuickOver }},
        quickMargin: {{ $initialQuickMargin }},
        quickSellOnline: {{ $isFoodBiz ? 'true' : 'false' }},
        quickOnlineFeePct: 20,
        quickMonthlyFixedCost: 1500000,
    
        // Quick presets (Multi-Industry: F&B, Workshop, Laundry, Retail)
        applyPreset(preset) {
            // Workshop Presets
            if (preset === 'oli') {
                this.quickName = {{ Js::from(__('calculator.preset_workshop_oil')) }};
                this.quickMaterial = 45000;
                this.quickLabor = 25000;
                this.quickOverhead = 5000;
                this.quickMargin = 35;
            } else if (preset === 'tuneup') {
                this.quickName = {{ Js::from(__('calculator.preset_workshop_tuneup')) }};
                this.quickMaterial = 30000;
                this.quickLabor = 50000;
                this.quickOverhead = 10000;
                this.quickMargin = 40;
            } else if (preset === 'rem') {
                this.quickName = {{ Js::from(__('calculator.preset_workshop_brake')) }};
                this.quickMaterial = 65000;
                this.quickLabor = 20000;
                this.quickOverhead = 5000;
                this.quickMargin = 35;
            } else if (preset === 'servis_berkala') {
                this.quickName = {{ Js::from(__('calculator.preset_workshop_periodic')) }};
                this.quickMaterial = 95000;
                this.quickLabor = 60000;
                this.quickOverhead = 15000;
                this.quickMargin = 35;
            }
            // Laundry Presets
            else if (preset === 'kiloan') {
                this.quickName = {{ Js::from(__('calculator.preset_laundry_regular')) }};
                this.quickMaterial = 2500;
                this.quickLabor = 3000;
                this.quickOverhead = 1500;
                this.quickMargin = 40;
            } else if (preset === 'bedcover') {
                this.quickName = {{ Js::from(__('calculator.preset_laundry_bedcover')) }};
                this.quickMaterial = 6000;
                this.quickLabor = 8000;
                this.quickOverhead = 4000;
                this.quickMargin = 45;
            } else if (preset === 'dryclean') {
                this.quickName = {{ Js::from(__('calculator.preset_laundry_dryclean')) }};
                this.quickMaterial = 8000;
                this.quickLabor = 15000;
                this.quickOverhead = 5000;
                this.quickMargin = 50;
            } else if (preset === 'sepatu') {
                this.quickName = {{ Js::from(__('calculator.preset_laundry_shoes')) }};
                this.quickMaterial = 5000;
                this.quickLabor = 12000;
                this.quickOverhead = 3000;
                this.quickMargin = 50;
            }
            // F&B / Retail / General Presets
            else if (preset === 'kopi') {
                this.quickName = {{ Js::from(__('calculator.preset_fnb_coffee')) }};
                this.quickMaterial = 4500;
                this.quickLabor = 1000;
                this.quickOverhead = 500;
                this.quickMargin = 40;
            } else if (preset === 'geprek') {
                this.quickName = {{ Js::from(__('calculator.preset_fnb_chicken')) }};
                this.quickMaterial = 8500;
                this.quickLabor = 2000;
                this.quickOverhead = 1000;
                this.quickMargin = 35;
            } else if (preset === 'kaos') {
                this.quickName = {{ Js::from(__('calculator.preset_general_tshirt')) }};
                this.quickMaterial = 38000;
                this.quickLabor = 8000;
                this.quickOverhead = 4000;
                this.quickMargin = 45;
            } else if (preset === 'kue') {
                this.quickName = {{ Js::from(__('calculator.preset_fnb_cake')) }};
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
            if (this.quickSaveLoading) return;
            if (!this.quickName.trim()) {
                const errMsg = {{ Js::from(__('calculator.err_enter_product_name')) }};
                if (window.AppAlert) {
                    AppAlert.warning(errMsg);
                } else {
                    alert(errMsg);
                }
                return;
            }
            this.quickSaveLoading = true;
            this.quickSaveSuccessMsg = '';
            this.quickSaveErrorMsg = '';
    
            const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
    
            fetch('{{ route('calculator.quick-create-product') }}', {
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
                        this.quickSaveErrorMsg = data.message || {{ Js::from(__('calculator.err_save_failed')) }};
                        if (window.coocaToast) {
                            window.coocaToast(this.quickSaveErrorMsg, 'error');
                        }
                    }
                })
                .catch(err => {
                    this.quickSaveLoading = false;
                    this.quickSaveErrorMsg = {{ Js::from(__('calculator.err_system_error')) }};
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
            fetch(`{{ url('calculator/calculate') }}/${this.selectedCostModel.id}`)
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
            if (this.isApplying) return;
            if (!this.selectedProduct || !this.calcResult) return;
            this.isApplying = true;
            this.applySuccessMsg = '';
            this.applyErrorMsg = '';
    
            const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
    
            fetch('{{ route('calculator.apply-to-product') }}', {
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
                        this.applyErrorMsg = data.message || {{ Js::from(__('calculator.err_apply_failed')) }};
                        if (window.coocaToast) {
                            window.coocaToast(this.applyErrorMsg, 'error');
                        }
                    }
                })
                .catch(() => {
                    this.isApplying = false;
                    this.applyErrorMsg = {{ Js::from(__('calculator.err_process_failed')) }};
                    if (window.coocaToast) {
                        window.coocaToast(this.applyErrorMsg, 'error');
                    }
                });
        },
    
        saveResult() {
            if (this.isSaving) return;
            if (!this.selectedCostModel) return;
            this.isSaving = true;
            this.saveSuccessMsg = '';
            this.saveErrorMsg = '';
    
            const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
    
            fetch('{{ route('calculator.save') }}', {
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
                        this.saveErrorMsg = data.message || {{ Js::from(__('calculator.err_save_run_failed')) }};
                        if (window.coocaToast) {
                            window.coocaToast(this.saveErrorMsg, 'error');
                        }
                    }
                })
                .catch(() => {
                    this.isSaving = false;
                    this.saveErrorMsg = {{ Js::from(__('calculator.err_system_save_run')) }};
                    if (window.coocaToast) {
                        window.coocaToast(this.saveErrorMsg, 'error');
                    }
                });
        }
    }">

        <!-- ========================================== -->
        <!-- 0. BREADCRUMB BAR (APPLE MINIMALIST)       -->
        <!-- ========================================== -->
        <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 py-0.5 whitespace-nowrap print:hidden"
            aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors font-medium">{{ __('dashboard.breadcrumb_dashboard') }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
            <span class="text-black/60 dark:text-white/60 font-medium">{{ __('calculator.breadcrumb_costing') }}</span>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
            <span class="text-black/80 dark:text-white/80 font-medium">{{ __('calculator.breadcrumb_calculator') }}</span>
        </nav>

        <!-- ===================================================== -->
        <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
        <!-- ===================================================== -->
        <header
            class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5 transition-colors shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
            <div class="space-y-1.5 max-w-2xl">
                <div class="flex items-center gap-2">
                    <span class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40">
                        {{ __('calculator.category_pricing') }}
                    </span>
                </div>
                <h1 class="text-[20px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight">
                    {{ __('calculator.toolbar_title') }}
                </h1>
                <p class="text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                    {{ __('calculator.toolbar_desc') }}
                </p>
            </div>

            <!-- Toolbar Action Buttons (Apple HIG Gray Buttons) -->
            <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2 w-full lg:w-auto">
                @if (\App\Support\Context::hasPermission('reports.export') || \App\Support\Context::hasPermission('costing.manage'))
                    <a href="{{ route('calculator.export-excel') }}"
                        class="col-span-1 min-h-[44px] sm:min-h-0 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 whitespace-nowrap">
                        <i data-lucide="download" class="w-4 h-4 text-black/60 dark:text-white/60 shrink-0"></i>
                        <span>{{ __('calculator.export_csv') }}</span>
                    </a>
                @endif
                @if (\App\Support\Context::hasPermission('products.view'))
                    <a href="{{ route('products.index') }}"
                        class="col-span-1 min-h-[44px] sm:min-h-0 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 whitespace-nowrap">
                        <i data-lucide="package" class="w-4 h-4 text-black/60 dark:text-white/60 shrink-0"></i>
                        <span>{{ __('calculator.product_catalog') }}</span>
                    </a>
                @endif
            </div>
        </header>

        {{-- ===================================================== --}}
        {{-- COSTING HUB NAVIGATION TABS (Apple Segmented Control) --}}
        {{-- ===================================================== --}}
        <div class="overflow-x-auto pb-1 scrollbar-none">
            <div class="inline-flex p-1 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.07] border border-black/5 dark:border-white/10 text-[13px] font-medium whitespace-nowrap">
                <a href="{{ route('calculator.index') }}"
                    class="min-h-[44px] sm:min-h-[36px] px-3.5 py-1.5 rounded-[10px] {{ request()->routeIs('calculator*') ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }} flex items-center gap-2 transition-all">
                    <i data-lucide="calculator" class="w-4 h-4 {{ request()->routeIs('calculator*') ? 'text-[#007AFF]' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                    <span>{{ __('calculator.tab_calc_price') }}</span>
                </a>

                @if((\App\Support\Context::hasPermission('labor_machines.view') || \App\Support\Context::hasPermission('costing.manage')) && (request()->routeIs('labor-machines.*') || (\App\Support\Context::business()?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_LABOR_MACHINES) ?? true)))
                <a href="{{ route('labor-machines.index') }}"
                    class="min-h-[44px] sm:min-h-[36px] px-3.5 py-1.5 rounded-[10px] {{ request()->routeIs('labor-machines.*') ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }} flex items-center gap-2 transition-all">
                    <i data-lucide="cog" class="w-4 h-4 {{ request()->routeIs('labor-machines.*') ? 'text-[#FF9500]' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                    <span>{{ __('calculator.tab_labor_machines') }}</span>
                </a>
                @endif

                @if(\App\Support\Context::hasPermission('costing.view_margin') || \App\Support\Context::hasPermission('reports.costing') || \App\Support\Context::hasPermission('costing.manage'))
                <a href="{{ route('profitability.index') }}"
                    class="min-h-[44px] sm:min-h-[36px] px-3.5 py-1.5 rounded-[10px] {{ request()->routeIs('profitability.*') ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }} flex items-center gap-2 transition-all">
                    <i data-lucide="trending-up" class="w-4 h-4 {{ request()->routeIs('profitability.*') ? 'text-[#34C759]' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                    <span>{{ __('calculator.tab_bep_margin') }}</span>
                </a>
                @endif
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- 2. SEGMENTED MODE SWITCHER (Apple Segmented Control)  -->
        <!-- ===================================================== -->
        <div
            class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-2 sm:p-3 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-2.5 shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
            <div
                class="inline-flex p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] border border-black/5 dark:border-white/5 w-full md:w-auto text-[13px] font-medium shrink-0">
                <button type="button"
                    @click="setTab('quick')"
                    :class="activeTab === 'quick' ?
                        'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-semibold' :
                        'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                    class="min-h-[44px] sm:min-h-[36px] px-3.5 sm:px-4 rounded-[9px] transition-all flex items-center justify-center gap-2 flex-1 md:flex-none cursor-pointer active:scale-[0.97] active:opacity-80 whitespace-nowrap">
                    <i data-lucide="zap" class="w-3.5 h-3.5 text-[#FF9500] dark:text-[#FF9F0A] shrink-0"></i>
                    <span>{{ __('calculator.mode_quick') }}</span>
                </button>

                <button type="button"
                    @click="setTab('advanced')"
                    :class="activeTab === 'advanced' ?
                        'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-semibold' :
                        'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                    class="min-h-[44px] sm:min-h-[36px] px-3.5 sm:px-4 rounded-[9px] transition-all flex items-center justify-center gap-2 flex-1 md:flex-none cursor-pointer active:scale-[0.97] active:opacity-80 whitespace-nowrap">
                    <i data-lucide="layers" class="w-3.5 h-3.5 text-[#5856D6] dark:text-[#5E5CE6] shrink-0"></i>
                    <span>{{ __('calculator.mode_advanced') }}</span>
                </button>
            </div>

            <div class="hidden sm:flex items-center gap-2 text-[12px] text-black/50 dark:text-white/50 px-2 min-w-0">
                <i data-lucide="info" class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i>
                <span class="truncate">
                    <span x-show="activeTab === 'quick'">{{ __('calculator.mode_quick_desc') }}</span>
                    <span x-show="activeTab === 'advanced'">{{ __('calculator.mode_advanced_desc') }}</span>
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
                <div
                    class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('calculator.kpi_total_cogs') }}</span>
                            <div
                                class="w-7 h-7 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.08] flex items-center justify-center text-black/60 dark:text-white/60">
                                <i data-lucide="calculator" class="w-3.5 h-3.5"></i>
                            </div>
                        </div>
                        <div
                            class="text-[20px] sm:text-[26px] font-bold tabular-nums text-black dark:text-white tracking-tight truncate">
                            Rp <span x-text="quickTotalHpp.toLocaleString(localeCode)"></span>
                        </div>
                    </div>
                    <div
                        class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 text-[11px] text-black/50 dark:text-white/50 flex items-center justify-between">
                        <span>{{ __('calculator.kpi_materials') }}: <strong class="tabular-nums font-semibold text-black dark:text-white"
                                x-text="quickMaterialPct + '%'"></strong></span>
                        <span class="font-medium text-black/40 dark:text-white/40">{{ __('calculator.kpi_per_unit') }}</span>
                    </div>
                </div>

                <!-- Pillar 2: Rekomendasi Kasir (System Blue) -->
                <div
                    class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('calculator.kpi_pos_rec') }}</span>
                            <div
                                class="w-7 h-7 rounded-[8px] bg-[#007AFF]/10 flex items-center justify-center text-[#007AFF]">
                                <i data-lucide="store" class="w-3.5 h-3.5"></i>
                            </div>
                        </div>
                        <div
                            class="text-[20px] sm:text-[26px] font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF] tracking-tight truncate">
                            Rp <span x-text="quickOfflinePrice.toLocaleString(localeCode)"></span>
                        </div>
                    </div>
                    <div
                        class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 text-[11px] text-black/50 dark:text-white/50 flex items-center justify-between">
                        <span>{{ __('calculator.kpi_markup') }}: <strong class="tabular-nums font-semibold text-black dark:text-white"
                                x-text="quickMarkupPct + '%'"></strong></span>
                        <span class="font-medium text-[#007AFF]">{{ __('calculator.kpi_offline_store') }}</span>
                    </div>
                </div>

                <!-- Pillar 3: Untung Bersih / Pcs (System Green) -->
                <div
                    class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('calculator.kpi_net_profit') }}</span>
                            <div
                                class="w-7 h-7 rounded-[8px] bg-[#34C759]/12 flex items-center justify-center text-[#34C759] dark:text-[#30D158]">
                                <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                            </div>
                        </div>
                        <div
                            class="text-[20px] sm:text-[26px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] tracking-tight truncate">
                            + Rp <span x-text="quickOfflineProfit.toLocaleString(localeCode)"></span>
                        </div>
                    </div>
                    <div
                        class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 text-[11px] text-black/50 dark:text-white/50 flex items-center justify-between">
                        <span>{{ __('calculator.margin_label') }}: <strong class="tabular-nums font-semibold text-[#34C759] dark:text-[#30D158]"
                                x-text="quickMargin + '%'"></strong></span>
                        <span class="font-medium text-[#34C759] dark:text-[#30D158]">{{ __('calculator.kpi_net_profit_badge') }}</span>
                    </div>
                </div>

                <!-- Pillar 4: Target BEP (System Indigo) -->
                <div
                    class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('calculator.kpi_target_bep') }}</span>
                            <div
                                class="w-7 h-7 rounded-[8px] bg-[#5856D6]/10 flex items-center justify-center text-[#5856D6] dark:text-[#5E5CE6]">
                                <i data-lucide="target" class="w-3.5 h-3.5"></i>
                            </div>
                        </div>
                        <div
                            class="text-[20px] sm:text-[26px] font-bold tabular-nums text-black dark:text-white tracking-tight truncate">
                            <span x-text="quickBepUnitsDaily"></span> <span
                                class="text-[13px] font-medium text-black/50 dark:text-white/50">{{ __('calculator.kpi_bep_unit_day') }}</span>
                        </div>
                    </div>
                    <div
                        class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 text-[11px] text-black/50 dark:text-white/50 flex items-center justify-between">
                        <span>{{ app()->getLocale() === 'en' ? 'Month:' : 'Bulan:' }} <strong class="tabular-nums font-semibold text-black dark:text-white"
                                x-text="quickBepUnitsMonthly"></strong> {{ app()->getLocale() === 'en' ? 'units' : 'pcs' }}</span>
                        <span class="font-medium text-[#5856D6]">{{ __('calculator.kpi_fixed_overhead') }}</span>
                    </div>
                </div>
            </div>

            <!-- 1-Click Quick Preset Chips (Apple Pill Style - Zero Emoji, Lucide Icons) -->
            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-0.5 whitespace-nowrap text-[12px]">
                <span
                    class="text-black/40 dark:text-white/40 font-semibold uppercase tracking-wider text-[11px] shrink-0 flex items-center gap-1.5 px-1">
                    <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                    <span>{{ __('calculator.preset_label') }}</span>
                </span>
                @if ($isWorkshopBiz)
                    <button type="button" @click="applyPreset('oli')"
                        class="min-h-[44px] px-4 py-2 rounded-full text-[12px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all cursor-pointer flex items-center gap-2 shrink-0">
                        <i data-lucide="droplet" class="w-3.5 h-3.5 text-[#8E8E93] dark:text-[#98989D]"></i>
                        <span>{{ __('calculator.preset_workshop_oil') }}</span>
                    </button>
                    <button type="button" @click="applyPreset('tuneup')"
                        class="min-h-[44px] px-4 py-2 rounded-full text-[12px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all cursor-pointer flex items-center gap-2 shrink-0">
                        <i data-lucide="wrench" class="w-3.5 h-3.5 text-[#8E8E93] dark:text-[#98989D]"></i>
                        <span>{{ __('calculator.preset_workshop_tuneup') }}</span>
                    </button>
                    <button type="button" @click="applyPreset('rem')"
                        class="min-h-[44px] px-4 py-2 rounded-full text-[12px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all cursor-pointer flex items-center gap-2 shrink-0">
                        <i data-lucide="disc" class="w-3.5 h-3.5 text-[#8E8E93] dark:text-[#98989D]"></i>
                        <span>{{ __('calculator.preset_workshop_brake') }}</span>
                    </button>
                    <button type="button" @click="applyPreset('servis_berkala')"
                        class="min-h-[44px] px-4 py-2 rounded-full text-[12px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all cursor-pointer flex items-center gap-2 shrink-0">
                        <i data-lucide="activity" class="w-3.5 h-3.5 text-[#8E8E93] dark:text-[#98989D]"></i>
                        <span>{{ __('calculator.preset_workshop_periodic') }}</span>
                    </button>
                @elseif ($isLaundryBiz)
                    <button type="button" @click="applyPreset('kiloan')"
                        class="min-h-[44px] px-4 py-2 rounded-full text-[12px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all cursor-pointer flex items-center gap-2 shrink-0">
                        <i data-lucide="shirt" class="w-3.5 h-3.5 text-[#8E8E93] dark:text-[#98989D]"></i>
                        <span>{{ __('calculator.preset_laundry_regular') }}</span>
                    </button>
                    <button type="button" @click="applyPreset('bedcover')"
                        class="min-h-[44px] px-4 py-2 rounded-full text-[12px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all cursor-pointer flex items-center gap-2 shrink-0">
                        <i data-lucide="layers" class="w-3.5 h-3.5 text-[#8E8E93] dark:text-[#98989D]"></i>
                        <span>{{ __('calculator.preset_laundry_bedcover') }}</span>
                    </button>
                    <button type="button" @click="applyPreset('dryclean')"
                        class="min-h-[44px] px-4 py-2 rounded-full text-[12px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all cursor-pointer flex items-center gap-2 shrink-0">
                        <i data-lucide="sparkle" class="w-3.5 h-3.5 text-[#8E8E93] dark:text-[#98989D]"></i>
                        <span>{{ __('calculator.preset_laundry_dryclean') }}</span>
                    </button>
                    <button type="button" @click="applyPreset('sepatu')"
                        class="min-h-[44px] px-4 py-2 rounded-full text-[12px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all cursor-pointer flex items-center gap-2 shrink-0">
                        <i data-lucide="footprints" class="w-3.5 h-3.5 text-[#8E8E93] dark:text-[#98989D]"></i>
                        <span>{{ __('calculator.preset_laundry_shoes') }}</span>
                    </button>
                @else
                    <button type="button" @click="applyPreset('kopi')"
                        class="min-h-[44px] px-4 py-2 rounded-full text-[12px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all cursor-pointer flex items-center gap-2 shrink-0">
                        <i data-lucide="coffee" class="w-3.5 h-3.5 text-[#8E8E93] dark:text-[#98989D]"></i>
                        <span>{{ __('calculator.preset_fnb_coffee') }}</span>
                    </button>
                    <button type="button" @click="applyPreset('geprek')"
                        class="min-h-[44px] px-4 py-2 rounded-full text-[12px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all cursor-pointer flex items-center gap-2 shrink-0">
                        <i data-lucide="utensils" class="w-3.5 h-3.5 text-[#8E8E93] dark:text-[#98989D]"></i>
                        <span>{{ __('calculator.preset_fnb_chicken') }}</span>
                    </button>
                    <button type="button" @click="applyPreset('kaos')"
                        class="min-h-[44px] px-4 py-2 rounded-full text-[12px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all cursor-pointer flex items-center gap-2 shrink-0">
                        <i data-lucide="shirt" class="w-3.5 h-3.5 text-[#8E8E93] dark:text-[#98989D]"></i>
                        <span>{{ __('calculator.preset_general_tshirt') }}</span>
                    </button>
                    <button type="button" @click="applyPreset('kue')"
                        class="min-h-[44px] px-4 py-2 rounded-full text-[12px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all cursor-pointer flex items-center gap-2 shrink-0">
                        <i data-lucide="cookie" class="w-3.5 h-3.5 text-[#8E8E93] dark:text-[#98989D]"></i>
                        <span>{{ __('calculator.preset_fnb_cake') }}</span>
                    </button>
                @endif
            </div>

            <!-- Main 3-Pillar Calculator Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- LEFT COLUMN: 3 PILLARS INPUT (7 cols) -->
                <div class="lg:col-span-7 space-y-5">

                    <!-- Product Name Input Card -->
                    <div
                        class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] space-y-2">
                        <label
                            class="block text-[12px] font-semibold text-black/70 dark:text-white/70 uppercase tracking-wide flex items-center gap-1.5">
                            <i data-lucide="tag" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                            <span>{{ __('calculator.input_product_name') }}</span>
                        </label>
                        <input type="text" x-model="quickName" placeholder="{{ __('calculator.input_product_name_placeholder') }}"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[10px] px-3.5 text-[16px] sm:text-[15px] font-semibold text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition">
                    </div>

                    <!-- The 3 Pillars of HPP Container -->
                    <div
                        class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 shadow-[0_1px_2px_rgba(0,0,0,0.02)] space-y-4">
                        <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                            <h2 class="text-[14px] font-semibold text-black dark:text-white flex items-center gap-2">
                                <i data-lucide="calculator" class="w-4 h-4 text-[#007AFF]"></i>
                                <span>{{ __('calculator.three_pillars_title') }}</span>
                            </h2>
                            <span class="text-[12px] text-black/40 dark:text-white/40">{{ __('calculator.per_portion_unit') }}</span>
                        </div>

                        <!-- PILAR 1: Bahan & Kemasan (Teal Accent) -->
                        <div
                            class="p-4 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden group">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div
                                        class="w-6 h-6 rounded-[6px] bg-[#30B0C7] text-white flex items-center justify-center font-bold text-[11px]">
                                        1
                                    </div>
                                    <div>
                                        <div class="font-semibold text-[13px] text-black dark:text-white">{{ __('calculator.pillar_material_title') }}</div>
                                        <div class="text-[11px] text-black/50 dark:text-white/50">{{ __('calculator.pillar_material_desc') }}</div>
                                    </div>
                                </div>
                                <span class="text-[12px] font-semibold tabular-nums text-[#30B0C7] dark:text-[#40C8E0]"
                                    x-text="quickMaterialPct + '%'"></span>
                            </div>
                            <div class="relative pt-1">
                                <div
                                    class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40 font-semibold text-[14px]">
                                    Rp
                                </div>
                                <input type="number" x-model.number="quickMaterial" min="0" step="500"
                                    class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[10px] pl-10 pr-3.5 text-[16px] font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition">
                            </div>
                        </div>

                        <!-- PILAR 2: Tenaga Kerja / Upah (Indigo Accent) -->
                        <div
                            class="p-4 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden group">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div
                                        class="w-6 h-6 rounded-[6px] bg-[#5856D6] text-white flex items-center justify-center font-bold text-[11px]">
                                        2
                                    </div>
                                    <div>
                                        <div class="font-semibold text-[13px] text-black dark:text-white">{{ __('calculator.pillar_labor_title') }}</div>
                                        <div class="text-[11px] text-black/50 dark:text-white/50">{{ __('calculator.pillar_labor_desc') }}</div>
                                    </div>
                                </div>
                                <span class="text-[12px] font-semibold tabular-nums text-[#5856D6] dark:text-[#5E5CE6]"
                                    x-text="quickLaborPct + '%'"></span>
                            </div>
                            <div class="relative pt-1">
                                <div
                                    class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40 font-semibold text-[14px]">
                                    Rp
                                </div>
                                <input type="number" x-model.number="quickLabor" min="0" step="500"
                                    class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[10px] pl-10 pr-3.5 text-[16px] font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition">
                            </div>
                        </div>

                        <!-- PILAR 3: Operasional / Utilitas (Orange Accent) -->
                        <div
                            class="p-4 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden group">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div
                                        class="w-6 h-6 rounded-[6px] bg-[#FF9500] text-white flex items-center justify-center font-bold text-[11px]">
                                        3
                                    </div>
                                    <div>
                                        <div class="font-semibold text-[13px] text-black dark:text-white">{{ __('calculator.pillar_overhead_title') }}</div>
                                        <div class="text-[11px] text-black/50 dark:text-white/50">{{ __('calculator.pillar_overhead_desc') }}</div>
                                    </div>
                                </div>
                                <span class="text-[12px] font-semibold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]"
                                    x-text="quickOverheadPct + '%'"></span>
                            </div>
                            <div class="relative pt-1">
                                <div
                                    class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40 font-semibold text-[14px]">
                                    Rp
                                </div>
                                <input type="number" x-model.number="quickOverhead" min="0" step="500"
                                    class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[10px] pl-10 pr-3.5 text-[16px] font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition">
                            </div>
                        </div>

                        <!-- Visual Proportion Bar (Apple Continuous Bar) -->
                        <div class="space-y-2 pt-3 border-t border-black/5 dark:border-white/10">
                            <div class="flex justify-between text-[12px] font-medium">
                                <span class="text-black/60 dark:text-white/60">{{ __('calculator.cost_composition_title') }}</span>
                                <span class="tabular-nums font-semibold text-black dark:text-white">Total: Rp <span
                                        x-text="quickTotalHpp.toLocaleString(localeCode)"></span></span>
                            </div>
                            <div
                                class="w-full h-2.5 rounded-full bg-black/[0.06] dark:bg-white/[0.08] overflow-hidden flex">
                                <div :style="'width: ' + quickMaterialPct + '%'"
                                    class="bg-[#30B0C7] transition-all duration-300" title="{{ __('calculator.comp_material') }}"></div>
                                <div :style="'width: ' + quickLaborPct + '%'"
                                    class="bg-[#5856D6] transition-all duration-300" title="{{ __('calculator.comp_labor') }}"></div>
                                <div :style="'width: ' + quickOverheadPct + '%'"
                                    class="bg-[#FF9500] transition-all duration-300" title="{{ __('calculator.comp_overhead') }}"></div>
                            </div>
                            <div
                                class="flex flex-wrap items-center justify-between gap-2 text-[11px] text-black/50 dark:text-white/50 pt-1">
                                <div class="flex items-center gap-1.5">
                                    <div class="w-2 h-2 rounded-full bg-[#30B0C7]"></div><span>{{ __('calculator.comp_material') }} (<span
                                            class="tabular-nums font-medium"
                                            x-text="quickMaterialPct + '%'"></span>)</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <div class="w-2 h-2 rounded-full bg-[#5856D6]"></div><span>{{ __('calculator.comp_labor') }} (<span
                                            class="tabular-nums font-medium" x-text="quickLaborPct + '%'"></span>)</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <div class="w-2 h-2 rounded-full bg-[#FF9500]"></div><span>{{ __('calculator.comp_overhead') }} (<span
                                            class="tabular-nums font-medium"
                                            x-text="quickOverheadPct + '%'"></span>)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Human-Centric Narrative Card (Apple HIG Callout Style) -->
                    <div
                        class="p-4 sm:p-5 rounded-[14px] bg-[#AF52DE]/5 dark:bg-[#AF52DE]/10 border border-[#AF52DE]/15 flex items-start gap-3.5">
                        <div
                            class="w-8 h-8 rounded-[8px] bg-[#AF52DE]/10 text-[#AF52DE] dark:text-[#BF5AF2] flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                        </div>
                        <div class="space-y-0.5 text-[13px]">
                            <h3 class="font-semibold text-black dark:text-white">{{ __('calculator.cogs_summary_title') }}</h3>
                            <p class="text-black/70 dark:text-white/70 leading-relaxed text-[13px]">
                                {!! __('calculator.cogs_summary_desc', [
                                    'name' => '<strong class="text-black dark:text-white font-semibold" x-text="quickName"></strong>',
                                    'total' => '<strong class="text-[#34C759] dark:text-[#30D158] tabular-nums font-bold"><span x-text="quickTotalHpp.toLocaleString(localeCode)"></span></strong>',
                                    'material' => '<strong class="text-black dark:text-white tabular-nums font-bold" x-text="quickMaterialPct + \'%\'"></strong>'
                                ]) !!}
                            </p>
                        </div>
                    </div>

                </div>

                <!-- RIGHT COLUMN: SMART PRICING & PROFIT STRATEGY (5 cols) -->
                <div class="lg:col-span-5 space-y-5">

                    <!-- Pricing & Profit Engine Card -->
                    <div
                        class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 shadow-[0_1px_2px_rgba(0,0,0,0.02)] space-y-5 lg:sticky lg:top-24 transition-colors">
                        <div class="border-b border-black/5 dark:border-white/10 pb-3 flex items-center justify-between">
                            <h2 class="text-[14px] font-semibold text-black dark:text-white flex items-center gap-2">
                                <i data-lucide="trending-up" class="w-4 h-4 text-[#34C759]"></i>
                                <span>{{ __('calculator.target_margin_title') }}</span>
                            </h2>
                        </div>

                        <!-- Margin Slider Control -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[12px] font-medium text-black/60 dark:text-white/60">{{ __('calculator.target_margin_label') }}</span>
                                <div class="flex items-baseline gap-0.5">
                                    <span class="text-[24px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]"
                                        x-text="quickMargin"></span>
                                    <span class="text-[13px] font-bold text-[#34C759] dark:text-[#30D158]">%</span>
                                </div>
                            </div>

                            <input type="range" x-model.number="quickMargin" min="10" max="80"
                                step="5"
                                class="w-full h-2 bg-black/[0.06] dark:bg-white/[0.08] rounded-full appearance-none cursor-pointer accent-[#007AFF]">

                            <!-- Margin Health Indicator (Apple Tinted Callout - Zero Emoji) -->
                            <div class="p-3 rounded-[10px] text-[12px] flex items-center gap-2"
                                :class="{
                                    'bg-[#FF9500]/10 text-[#B25E00] dark:text-[#FF9F0A] border border-[#FF9500]/20': quickMargin < 25,
                                    'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20': quickMargin >= 25 && quickMargin <= 60,
                                    'bg-[#AF52DE]/10 text-[#7C3AA6] dark:text-[#BF5AF2] border border-[#AF52DE]/20': quickMargin > 60
                                }">
                                <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
                                <span x-show="quickMargin < 25">{{ __('calculator.margin_thin_warning') }}</span>
                                <span x-show="quickMargin >= 25 && quickMargin <= 60">{{ __('calculator.margin_ideal_info') }}</span>
                                <span x-show="quickMargin > 60">{{ __('calculator.margin_premium_info') }}</span>
                            </div>
                        </div>

                        <!-- Pricing Result Box -->
                        <div
                            class="p-4 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3">
                            <div>
                                <div class="text-[11px] font-medium text-black/50 dark:text-white/50">{{ __('calculator.recommended_price_title') }}</div>
                                <div
                                    class="text-[26px] sm:text-[30px] font-bold tabular-nums text-black dark:text-white tracking-tight mt-0.5">
                                    Rp <span x-text="quickOfflinePrice.toLocaleString(localeCode)"></span>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-black/5 dark:border-white/10 space-y-1.5 text-[12px]">
                                <div class="flex justify-between items-center">
                                    <span class="text-black/60 dark:text-white/60">{{ __('calculator.net_profit_per_unit') }}</span>
                                    <span class="tabular-nums font-bold text-[#34C759] dark:text-[#30D158]">+ Rp <span
                                            x-text="quickOfflineProfit.toLocaleString(localeCode)"></span></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-black/60 dark:text-white/60">{{ __('calculator.markup_from_cogs') }}</span>
                                    <span class="tabular-nums font-semibold text-black dark:text-white"><span
                                            x-text="quickMarkupPct"></span>{{ __('calculator.markup_of_cogs_suffix') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Online Food / Marketplace Simulator Switch -->
                        <div
                            class="p-4 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="{{ $isFoodBiz ? 'bike' : 'shopping-bag' }}" class="w-4 h-4 text-[#AF52DE]"></i>
                                    <span class="text-[13px] font-semibold text-black dark:text-white">{{ $isFoodBiz ? __('calculator.sell_online_switch') : __('calculator.sell_marketplace_switch') }}</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="quickSellOnline" class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-black/[0.12] dark:bg-white/[0.15] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]">
                                    </div>
                                </label>
                            </div>

                            <div x-show="quickSellOnline"
                                class="space-y-3 pt-3 border-t border-black/5 dark:border-white/10 text-[12px]"
                                style="display: none;">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-black/60 dark:text-white/60">{{ __('calculator.app_fee_label') }}</span>
                                    <select x-model.number="quickOnlineFeePct"
                                        class="h-8 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[8px] px-2.5 text-black dark:text-white text-[12px] font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition">
                                        <option :value="20">{{ __('calculator.app_fee_opt_20') }}</option>
                                        <option :value="15">{{ __('calculator.app_fee_opt_15') }}</option>
                                        <option :value="10">{{ __('calculator.app_fee_opt_10') }}</option>
                                    </select>
                                </div>

                                <div class="p-3.5 rounded-[10px] bg-[#AF52DE]/10 border border-[#AF52DE]/20 space-y-1">
                                    <div class="text-[11px] text-[#AF52DE] dark:text-[#BF5AF2] font-semibold">{{ __('calculator.online_must_price_title') }}</div>
                                    <div class="text-[22px] font-bold tabular-nums text-[#AF52DE] dark:text-[#BF5AF2]">
                                        Rp <span x-text="quickOnlinePrice.toLocaleString(localeCode)"></span>
                                    </div>
                                    <p class="text-[11px] text-black/60 dark:text-white/60 leading-tight pt-1">
                                        {!! __('calculator.online_must_price_desc', [
                                            'online' => '<span class="tabular-nums font-semibold" x-text="quickOnlinePrice.toLocaleString(localeCode)"></span>',
                                            'fee' => '<span x-text="quickOnlineFeePct"></span>',
                                            'cut' => '<span class="tabular-nums" x-text="quickOnlineFeeNominal.toLocaleString(localeCode)"></span>',
                                            'offline' => '<strong class="text-black dark:text-white tabular-nums">Rp <span x-text="quickOfflinePrice.toLocaleString(localeCode)"></span></strong>'
                                        ]) !!}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Mini BEP (Break-Even Point) Tracker -->
                        <div
                            class="p-4 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2.5 text-[12px]">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-black dark:text-white flex items-center gap-1.5">
                                    <i data-lucide="target" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                    <span>{{ __('calculator.kpi_target_bep') }}</span>
                                </span>
                                <span class="text-[11px] text-black/40 dark:text-white/40">{{ __('calculator.bep_routine_fixed') }}</span>
                            </div>
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="text-black/60 dark:text-white/60 text-[11px]">{{ __('calculator.bep_monthly_rent_label') }}</span>
                                <input type="number" x-model.number="quickMonthlyFixedCost" step="100000"
                                    class="w-full sm:w-36 h-8 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[8px] px-2.5 text-right text-[16px] sm:text-[12px] font-semibold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition">
                            </div>
                            <div class="pt-1 text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                {!! __('calculator.bep_explanation', [
                                    'daily' => '<strong class="text-[#34C759] dark:text-[#30D158] font-semibold tabular-nums"><span x-text="quickBepUnitsDaily"></span></strong>',
                                    'monthly' => '<span class="tabular-nums font-semibold" x-text="quickBepUnitsMonthly"></span>'
                                ]) !!}
                            </div>
                        </div>

                        <!-- Save as Product Button (Apple Primary Filled Button) -->
                        @if (\App\Support\Context::hasPermission('products.create'))
                            <button type="button" @click="showQuickSaveModal = true"
                                class="w-full h-11 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 shadow-[0_1px_2px_rgba(0,122,255,0.25)] transition-all cursor-pointer flex items-center justify-center gap-2 group">
                                <i data-lucide="plus-circle"
                                    class="w-4 h-4 group-hover:scale-105 transition-transform"></i>
                                <span>{{ __('calculator.save_to_pos_product_btn') }}</span>
                            </button>
                        @endif
                    </div>

                </div>

            </div>

        </div>

        <!-- ========================================== -->
        <!-- TAB 2: MODE DETAIL RESEP (BOM)             -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'advanced'" class="space-y-6" style="display: none;">

            <!-- Product Selector for BOM -->
            <div
                class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 shadow-[0_1px_2px_rgba(0,0,0,0.02)] flex flex-col md:flex-row items-start md:items-center justify-between gap-4 transition-colors">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-[10px] bg-[#5856D6]/10 text-[#5856D6] dark:text-[#5E5CE6] flex items-center justify-center shrink-0">
                        <i data-lucide="layers" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-[14px] font-semibold text-black dark:text-white">{{ __('calculator.advanced_catalog_title') }}</h2>
                        <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('calculator.advanced_catalog_desc') }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap w-full md:w-auto">
                    <select x-model="selectedProductId" @change="selectProduct($event.target.value)"
                        class="h-9 px-3 bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[10px] text-[13px] font-medium text-black dark:text-white w-full sm:w-auto min-w-0 sm:min-w-[260px] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition">
                        <option value="">{{ __('calculator.advanced_select_placeholder') }}</option>
                        <template x-for="p in products" :key="p.id">
                            <option :value="p.id"
                                x-text="p.name + ' (' + (p.output_unit ? p.output_unit.name : 'pcs') + ')'"></option>
                        </template>
                    </select>

                    <template x-if="selectedProduct">
                        <div class="flex items-center gap-2">
                            @if (\App\Support\Context::hasPermission('products.manage') || \App\Support\Context::hasPermission('costing.manage'))
                                <a :href="'{{ url('products') }}/' + selectedProduct.slug + '/bom'"
                                    class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5 text-black/60 dark:text-white/60"></i>
                                    <span>{{ __('calculator.edit_bom_recipe') }}</span>
                                </a>
                            @endif
                            @if (\App\Support\Context::hasPermission('products.edit'))
                                <a :href="'{{ url('products') }}?edit=' + selectedProduct.id"
                                    class="h-9 px-3.5 rounded-[10px] text-[13px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                                    <i data-lucide="pencil-line" class="w-3.5 h-3.5"></i>
                                    <span>{{ __('calculator.edit_price') }}</span>
                                </a>
                            @endif
                        </div>
                    </template>
                </div>
            </div>

            <!-- BOM Calculation Results View -->
            <template x-if="selectedProduct && calcResult">
                <div class="space-y-6">
                    <!-- Result 4 Bento Cards (Apple HIG Style) -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                        <div
                            class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('calculator.cogs_per_unit_title') }}</span>
                                    <div
                                        class="w-7 h-7 rounded-[8px] bg-[#34C759]/12 flex items-center justify-center text-[#34C759] dark:text-[#30D158]">
                                        <i data-lucide="coins" class="w-3.5 h-3.5"></i>
                                    </div>
                                </div>
                                <div
                                    class="text-[20px] sm:text-[26px] font-bold tabular-nums text-black dark:text-white tracking-tight truncate">
                                    Rp <span x-text="Math.round(calcResult.hpp_per_unit).toLocaleString(localeCode)"></span>
                                </div>
                            </div>
                            <div
                                class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 text-[11px] text-black/50 dark:text-white/50">
                                {!! __('calculator.unit_label', ['unit' => '<strong class="text-black dark:text-white font-medium" x-text="selectedProduct.output_unit ? selectedProduct.output_unit.name : \'pcs\'"></strong>']) !!}
                            </div>
                        </div>

                        <div
                            class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('calculator.raw_materials_title') }}</span>
                                    <div
                                        class="w-7 h-7 rounded-[8px] bg-[#007AFF]/10 flex items-center justify-center text-[#007AFF]">
                                        <i data-lucide="package" class="w-3.5 h-3.5"></i>
                                    </div>
                                </div>
                                <div
                                    class="text-[20px] sm:text-[26px] font-bold tabular-nums text-black dark:text-white tracking-tight truncate">
                                    Rp <span
                                        x-text="Math.round(calcResult.total_material_cost).toLocaleString(localeCode)"></span>
                                </div>
                            </div>
                            <div
                                class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 text-[11px] text-[#007AFF] font-semibold tabular-nums">
                                <span
                                    x-text="((calcResult.total_material_cost / calcResult.total_hpp) * 100).toFixed(1) + '% {{ __('calculator.portion_of_cogs', ['pct' => '']) }}'"></span>
                            </div>
                        </div>

                        <div
                            class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('calculator.labor_and_machines_title') }}</span>
                                    <div
                                        class="w-7 h-7 rounded-[8px] bg-[#5856D6]/10 flex items-center justify-center text-[#5856D6] dark:text-[#5E5CE6]">
                                        <i data-lucide="users" class="w-3.5 h-3.5"></i>
                                    </div>
                                </div>
                                <div
                                    class="text-[20px] sm:text-[26px] font-bold tabular-nums text-black dark:text-white tracking-tight truncate">
                                    Rp <span
                                        x-text="Math.round(calcResult.total_labor_cost + calcResult.total_machine_cost).toLocaleString(localeCode)"></span>
                                </div>
                            </div>
                            <div
                                class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 text-[11px] text-black/50 dark:text-white/50">
                                {{ __('calculator.labor_and_utilities') }}
                            </div>
                        </div>

                        <div
                            class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('calculator.overhead_alloc_title') }}</span>
                                    <div
                                        class="w-7 h-7 rounded-[8px] bg-[#FF9500]/10 flex items-center justify-center text-[#FF9500] dark:text-[#FF9F0A]">
                                        <i data-lucide="pie-chart" class="w-3.5 h-3.5"></i>
                                    </div>
                                </div>
                                <div
                                    class="text-[20px] sm:text-[26px] font-bold tabular-nums text-black dark:text-white tracking-tight truncate">
                                    Rp <span
                                        x-text="Math.round(calcResult.total_overhead_cost).toLocaleString(localeCode)"></span>
                                </div>
                            </div>
                            <div
                                class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 text-[11px] text-black/50 dark:text-white/50">
                                {{ __('calculator.overhead_factory_ops') }}
                            </div>
                        </div>
                    </div>

                    <!-- High-Density Breakdown Table (Apple HIG Dense Table) -->
                    <div
                        class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                        <div
                            class="p-4 sm:p-5 border-b border-black/5 dark:border-white/10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                            <div>
                                <h2 class="text-[14px] font-semibold text-black dark:text-white flex items-center gap-2">
                                    <i data-lucide="list-checks" class="w-4 h-4 text-[#5856D6]"></i>
                                    <span>{{ __('calculator.recipe_breakdown_title') }}</span>
                                </h2>
                                <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('calculator.recipe_breakdown_desc') }}</p>
                            </div>
                            <div class="flex items-center gap-2 flex-wrap w-full sm:w-auto">
                                @if (\App\Support\Context::hasPermission('costing.manage'))
                                    <button type="button" @click="showSaveModal = true"
                                        class="h-8 px-3 rounded-[8px] text-[12px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 flex-1 sm:flex-none">
                                        <i data-lucide="bookmark" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                        <span>{{ __('calculator.btn_save_run') }}</span>
                                    </button>
                                @endif
                                @if (\App\Support\Context::hasPermission('costing.manage') || \App\Support\Context::hasPermission('products.edit'))
                                    <button type="button" @click="applyToProduct()" :disabled="isApplying"
                                        class="h-8 px-3.5 rounded-[8px] text-[12px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 disabled:opacity-50 flex-1 sm:flex-none">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                        <span x-text="isApplying ? '{{ __('calculator.btn_applying') }}' : '{{ __('calculator.btn_apply_to_product') }}'"></span>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-[13px]">
                                <thead>
                                    <tr
                                        class="border-b border-black/5 dark:border-white/10 text-black/40 dark:text-white/40 uppercase tracking-wide text-[11px] font-semibold">
                                        <th class="px-4 py-2.5 w-12 text-center">{{ __('calculator.col_no') }}</th>
                                        <th class="px-4 py-2.5">{{ __('calculator.col_component_name') }}</th>
                                        <th class="px-4 py-2.5">{{ __('calculator.col_category') }}</th>
                                        <th class="px-4 py-2.5 text-right">{{ __('calculator.col_nominal_cogs') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                                    <template x-for="(item, idx) in calcResult.breakdown" :key="idx">
                                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                            <td class="px-4 py-3 text-center text-black/40 dark:text-white/40 tabular-nums"
                                                x-text="idx + 1"></td>
                                            <td class="px-4 py-3 font-medium text-black dark:text-white"
                                                x-text="item.name"></td>
                                            <td class="px-4 py-3">
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold"
                                                    :class="{
                                                        'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]': item
                                                            .type === 'material',
                                                        'bg-[#5856D6]/12 text-[#413FA6] dark:text-[#5E5CE6]': item
                                                            .type === 'labor',
                                                        'bg-[#007AFF]/12 text-[#0062CC] dark:text-[#0A84FF]': item
                                                            .type === 'machine',
                                                        'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]': item
                                                            .type === 'overhead'
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
                                            <td
                                                class="px-4 py-3 text-right font-semibold tabular-nums text-black dark:text-white">
                                                Rp <span x-text="Math.round(item.cost).toLocaleString(localeCode)"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                                <tfoot>
                                    <tr
                                        class="border-t border-black/10 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] font-semibold text-[13px]">
                                        <td colspan="3"
                                            class="px-4 py-3 text-right uppercase tracking-wide text-[11px] text-black/50 dark:text-white/50">
                                            {{ __('calculator.total_cogs_all') }}
                                        </td>
                                        <td
                                            class="px-4 py-3 text-right tabular-nums text-[#34C759] dark:text-[#30D158] text-[15px] font-bold">
                                            Rp <span
                                                x-text="Math.round(calcResult.total_hpp).toLocaleString(localeCode)"></span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Recent Runs Table (Persisted Costing Runs) -->
                    <div
                        class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                        <div
                            class="p-4 sm:p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                            <div>
                                <h3 class="text-[14px] font-semibold text-black dark:text-white flex items-center gap-2">
                                    <i data-lucide="history" class="w-4 h-4 text-black/50 dark:text-white/50"></i>
                                    <span>{{ __('calculator.saved_runs_title') }}</span>
                                </h3>
                                <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('calculator.saved_runs_desc') }}</p>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-[13px]">
                                <thead>
                                    <tr
                                        class="border-b border-black/5 dark:border-white/10 text-black/40 dark:text-white/40 uppercase tracking-wide text-[11px] font-semibold">
                                        <th class="px-4 py-2.5">{{ __('calculator.col_date') }}</th>
                                        <th class="px-4 py-2.5">{{ __('calculator.col_related_product') }}</th>
                                        <th class="px-4 py-2.5">{{ __('calculator.col_notes') }}</th>
                                        <th class="px-4 py-2.5 text-right">{{ __('calculator.col_unit_cogs') }}</th>
                                        <th class="px-4 py-2.5 text-right">{{ __('calculator.col_total_cogs') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                                    <template x-for="run in recentRuns" :key="run.id">
                                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                            <td class="px-4 py-3 text-black/50 dark:text-white/50 tabular-nums whitespace-nowrap text-[12px]"
                                                x-text="run.created_at"></td>
                                            <td class="px-4 py-3 font-medium text-black dark:text-white"
                                                x-text="run.product_name || (run.cost_model && run.cost_model.product ? run.cost_model.product.name : '-')">
                                            </td>
                                            <td class="px-4 py-3 text-black/60 dark:text-white/60 truncate max-w-xs text-[12px]"
                                                x-text="run.notes || '-'"></td>
                                            <td
                                                class="px-4 py-3 text-right font-bold tabular-nums text-[#34C759] dark:text-[#30D158] whitespace-nowrap">
                                                Rp <span
                                                    x-text="Math.round(run.hpp_per_unit || (run.result ? run.result.hpp_per_unit : 0)).toLocaleString(localeCode)"></span>
                                            </td>
                                            <td
                                                class="px-4 py-3 text-right font-semibold tabular-nums text-black dark:text-white whitespace-nowrap">
                                                Rp <span
                                                    x-text="Math.round(run.total_hpp || (run.result ? run.result.total_hpp : 0)).toLocaleString(localeCode)"></span>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="!recentRuns || recentRuns.length === 0">
                                        <td colspan="5"
                                            class="px-4 py-8 text-center text-black/40 dark:text-white/40 text-[13px]">
                                            {{ __('calculator.saved_runs_empty') }}
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
                <div
                    class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-12 sm:p-16 text-center shadow-[0_1px_2px_rgba(0,0,0,0.02)] space-y-3">
                    <div
                        class="w-14 h-14 rounded-[14px] bg-[#5856D6]/10 flex items-center justify-center mx-auto text-[#5856D6] dark:text-[#5E5CE6]">
                        <i data-lucide="package-search" class="w-7 h-7"></i>
                    </div>
                    <div class="space-y-1 max-w-md mx-auto">
                        <h3 class="text-[16px] font-semibold text-black dark:text-white">{{ __('calculator.no_product_selected_title') }}</h3>
                        <p class="text-[13px] text-black/50 dark:text-white/50 leading-relaxed">
                            {{ __('calculator.no_product_selected_desc') }}
                        </p>
                    </div>
                    <div class="pt-2 flex justify-center">
                        <a href="{{ route('products.index') }}"
                            class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                            <i data-lucide="plus-circle" class="w-4 h-4 text-[#007AFF]"></i>
                            <span>{{ __('calculator.btn_manage_products') }}</span>
                        </a>
                    </div>
                </div>
            </template>
        </div>

        <!-- ===================================================== -->
        <!-- MODAL 1: SIMPAN JADI PRODUK BARU (Apple Bento XXL Sheet) -->
        <!-- ===================================================== -->
        @if (\App\Support\Context::hasPermission('products.create'))
            <template x-teleport="body">
                <div x-show="showQuickSaveModal" x-cloak
                    class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                    role="dialog" aria-modal="true" aria-labelledby="quick-save-modal-title"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    style="display: none;">
                    <div @click.away="showQuickSaveModal = false"
                        class="w-full max-w-[95vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-[1350px] max-h-[92vh] rounded-[24px] bg-white/98 dark:bg-[#1C1C1E]/98 backdrop-blur-2xl border border-black/[0.08] dark:border-white/[0.12] flex flex-col shadow-[0_25px_60px_rgba(0,0,0,0.35)] overflow-hidden"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95">
                        
                        {{-- Modal Header Bar --}}
                        <div class="px-6 py-4.5 sm:px-8 sm:py-5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-4 shrink-0 bg-black/[0.02] dark:bg-white/[0.02]">
                            <div class="flex items-center gap-3.5">
                                <div class="w-11 h-11 rounded-[14px] bg-[#007AFF]/12 text-[#007AFF] flex items-center justify-center shrink-0 border border-[#007AFF]/20">
                                    <i data-lucide="package-plus" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h3 id="quick-save-modal-title" class="text-base sm:text-lg font-bold text-black dark:text-white tracking-tight">
                                        {{ __('calculator.quick_save_modal_title') }}
                                    </h3>
                                    <p class="text-xs text-black/55 dark:text-white/55">
                                        {{ __('calculator.quick_save_modal_desc') }}
                                    </p>
                                </div>
                            </div>
                            <button type="button" @click="showQuickSaveModal = false"
                                class="w-9 h-9 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/55 dark:text-white/55 hover:text-black dark:hover:text-white transition cursor-pointer">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        {{-- Modal Scrollable Body (2-Column Bento on Desktop) --}}
                        <div class="p-6 sm:p-8 overflow-y-auto space-y-6 flex-1">
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                                
                                {{-- Kolom Kiri: Form & Konfirmasi Parameter (7 Kolom) --}}
                                <div class="lg:col-span-7 space-y-5">
                                    <div class="rounded-[18px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/[0.06] dark:border-white/[0.08] p-5 space-y-4">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="tag" class="w-4 h-4 text-[#007AFF]"></i>
                                            <span class="text-xs font-bold uppercase tracking-wider text-black/55 dark:text-white/55">{{ __('calculator.product_identity_title') }}</span>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold text-black/80 dark:text-white/80 mb-1.5">
                                                {{ __('calculator.product_name_label') }} <span class="text-[#FF3B30]">*</span>
                                            </label>
                                            <input type="text" x-model="quickName" placeholder="{{ __('calculator.input_product_name_placeholder') }}"
                                                class="w-full h-12 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[12px] px-4 text-[16px] sm:text-sm font-semibold text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                            <p class="text-[11px] text-black/50 dark:text-white/50 mt-1">
                                                {{ __('calculator.sku_barcode_generated_note') }}
                                            </p>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                                            <div class="p-3.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                                                <span class="text-[11px] text-black/55 dark:text-white/55 block mb-0.5">{{ __('calculator.modal_material_label') }}</span>
                                                <span class="text-xs font-bold text-black dark:text-white tabular-nums">Rp <span x-text="Number(quickMaterial || 0).toLocaleString(localeCode)"></span></span>
                                            </div>
                                            <div class="p-3.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                                                <span class="text-[11px] text-black/55 dark:text-white/55 block mb-0.5">{{ __('calculator.modal_labor_label') }}</span>
                                                <span class="text-xs font-bold text-black dark:text-white tabular-nums">Rp <span x-text="Number(quickLabor || 0).toLocaleString(localeCode)"></span></span>
                                            </div>
                                            <div class="p-3.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                                                <span class="text-[11px] text-black/55 dark:text-white/55 block mb-0.5">{{ __('calculator.modal_overhead_label') }}</span>
                                                <span class="text-xs font-bold text-black dark:text-white tabular-nums">Rp <span x-text="Number(quickOverhead || 0).toLocaleString(localeCode)"></span></span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Feedback Alert Messages --}}
                                    <template x-if="quickSaveSuccessMsg">
                                        <div class="p-4 rounded-[14px] bg-[#34C759]/12 border border-[#34C759]/30 text-[#248A3D] dark:text-[#30D158] text-xs font-semibold flex items-center gap-3">
                                            <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
                                            <span x-text="quickSaveSuccessMsg"></span>
                                        </div>
                                    </template>

                                    <template x-if="quickSaveErrorMsg">
                                        <div class="p-4 rounded-[14px] bg-[#FF3B30]/12 border border-[#FF3B30]/30 text-[#C41E17] dark:text-[#FF453A] text-xs font-semibold flex items-center gap-3">
                                            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                                            <span x-text="quickSaveErrorMsg"></span>
                                        </div>
                                    </template>

                                    <div class="rounded-[16px] bg-[#007AFF]/5 dark:bg-[#007AFF]/10 border border-[#007AFF]/20 p-4 flex items-start gap-3">
                                        <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5"></i>
                                        <p class="text-xs text-black/70 dark:text-white/70 leading-relaxed">
                                            {!! __('calculator.modal_active_pos_info', ['price' => '<strong class="text-black dark:text-white tabular-nums" x-text="quickOfflinePrice.toLocaleString(localeCode)"></strong>']) !!}
                                        </p>
                                    </div>
                                </div>

                                {{-- Kolom Kanan: Bento Live Preview Card (5 Kolom) --}}
                                <div class="lg:col-span-5 space-y-4">
                                    <div class="rounded-[20px] bg-[#1C1C1E] text-white p-6 shadow-xl border border-white/10 space-y-4 relative overflow-hidden">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[11px] font-bold uppercase tracking-wider text-white/60">{{ __('calculator.card_pos_preview') }}</span>
                                            <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                                        </div>

                                        <div>
                                            <h4 class="text-lg font-bold tracking-tight text-white line-clamp-1" x-text="quickName || '{{ __('calculator.product_name_label') }}'"></h4>
                                            <span class="text-xs text-white/60">{{ __('calculator.unit_pcs') }}</span>
                                        </div>

                                        <div class="pt-2 border-t border-white/10 flex items-baseline justify-between">
                                            <div>
                                                <span class="text-[10px] uppercase font-bold text-white/50 block">{{ __('calculator.pos_selling_price_label') }}</span>
                                                <span class="text-2xl font-bold text-white tabular-nums">
                                                    Rp <span x-text="quickOfflinePrice.toLocaleString(localeCode)"></span>
                                                </span>
                                            </div>
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-[#34C759]/20 text-[#30D158] border border-[#34C759]/30 tabular-nums">
                                                {{ __('calculator.margin_label') }} <span x-text="quickMargin"></span>%
                                            </span>
                                        </div>

                                        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-white/10 text-xs">
                                            <div class="p-2.5 rounded-[10px] bg-white/5">
                                                <span class="text-[10px] text-white/50 block">{{ __('calculator.net_cogs_modal') }}</span>
                                                <span class="font-bold text-white tabular-nums">Rp <span x-text="quickTotalHpp.toLocaleString(localeCode)"></span></span>
                                            </div>
                                            <div class="p-2.5 rounded-[10px] bg-white/5">
                                                <span class="text-[10px] text-white/50 block">{{ __('calculator.gross_profit_unit') }}</span>
                                                <span class="font-bold text-[#30D158] tabular-nums">+Rp <span x-text="quickOfflineProfit.toLocaleString(localeCode)"></span></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="rounded-[16px] bg-black/[0.03] dark:bg-[#2C2C2E] border border-black/[0.06] dark:border-white/[0.08] p-4 text-xs space-y-2">
                                        <div class="flex justify-between items-center text-black/60 dark:text-white/60">
                                            <span>{{ __('calculator.monthly_bep_target') }}</span>
                                            <span class="font-bold text-black dark:text-white tabular-nums"><span x-text="quickBepUnitsMonthly"></span> {{ __('calculator.portion_per_month') }}</span>
                                        </div>
                                        <div class="flex justify-between items-center text-black/60 dark:text-white/60">
                                            <span>{{ __('calculator.daily_avg_sales') }}</span>
                                            <span class="font-bold text-[#007AFF] tabular-nums"><span x-text="quickBepUnitsDaily"></span> {{ __('calculator.portion_per_day') }}</span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- Modal Footer Actions --}}
                        <div class="px-6 py-4 sm:px-8 sm:py-4.5 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-end gap-3 shrink-0 bg-black/[0.02] dark:bg-white/[0.02]">
                            <button type="button" @click="showQuickSaveModal = false"
                                class="min-h-[44px] h-11 px-5 rounded-[12px] text-xs font-semibold text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition cursor-pointer">
                                {{ __('calculator.btn_cancel') }}
                            </button>
                            <button type="button" @click="saveQuickProduct()" :disabled="quickSaveLoading"
                                class="min-h-[44px] h-11 px-6 rounded-[12px] text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all shadow-[0_2px_4px_rgba(0,122,255,0.3)] flex items-center gap-2 cursor-pointer disabled:opacity-50">
                                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                <span x-text="quickSaveLoading ? '{{ __('calculator.btn_saving_product') }}' : '{{ __('calculator.btn_save_product_confirm') }}'"></span>
                            </button>
                        </div>

                    </div>
                </div>
            </template>
        @endif

        <!-- ===================================================== -->
        <!-- MODAL 2: SIMPAN RIWAYAT KALKULASI BOM (Apple Bento XXL)-->
        <!-- ===================================================== -->
        @if (\App\Support\Context::hasPermission('costing.manage'))
            <template x-teleport="body">
                <div x-show="showSaveModal" x-cloak
                    class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                    role="dialog" aria-modal="true" aria-labelledby="save-run-modal-title"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    style="display: none;">
                    <div @click.away="showSaveModal = false"
                        class="w-full max-w-[95vw] lg:max-w-4xl xl:max-w-5xl max-h-[92vh] rounded-[24px] bg-white/98 dark:bg-[#1C1C1E]/98 backdrop-blur-2xl border border-black/[0.08] dark:border-white/[0.12] flex flex-col shadow-[0_25px_60px_rgba(0,0,0,0.35)] overflow-hidden"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95">
                        
                        {{-- Modal Header Bar --}}
                        <div class="px-6 py-4.5 sm:px-8 sm:py-5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-4 shrink-0 bg-black/[0.02] dark:bg-white/[0.02]">
                            <div class="flex items-center gap-3.5">
                                <div class="w-11 h-11 rounded-[14px] bg-[#007AFF]/12 text-[#007AFF] flex items-center justify-center shrink-0 border border-[#007AFF]/20">
                                    <i data-lucide="bookmark" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h3 id="save-run-modal-title" class="text-base sm:text-lg font-bold text-black dark:text-white tracking-tight">
                                        {{ __('calculator.save_run_modal_title') }}
                                    </h3>
                                    <p class="text-xs text-black/55 dark:text-white/55">
                                        {{ __('calculator.save_run_modal_desc') }}
                                    </p>
                                </div>
                            </div>
                            <button type="button" @click="showSaveModal = false"
                                class="w-9 h-9 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/55 dark:text-white/55 hover:text-black dark:hover:text-white transition cursor-pointer">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        {{-- Modal Body --}}
                        <div class="p-6 sm:p-8 overflow-y-auto space-y-5 flex-1">
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                                
                                <div class="lg:col-span-7 space-y-4">
                                    <div class="rounded-[18px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/[0.06] dark:border-white/[0.08] p-5 space-y-3.5">
                                        <label class="block text-xs font-semibold text-black/80 dark:text-white/80">
                                            {{ __('calculator.run_notes_label') }}
                                        </label>
                                        <textarea x-model="saveNotes" rows="4"
                                            placeholder="{{ __('calculator.run_notes_placeholder') }}"
                                            class="w-full bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[12px] p-3.5 text-[16px] sm:text-xs text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition resize-none"></textarea>
                                        <p class="text-[11px] text-black/50 dark:text-white/50">
                                            {{ __('calculator.run_notes_audit_note') }}
                                        </p>
                                    </div>

                                    <template x-if="saveSuccessMsg">
                                        <div class="p-4 rounded-[14px] bg-[#34C759]/12 border border-[#34C759]/30 text-[#248A3D] dark:text-[#30D158] text-xs font-semibold flex items-center gap-3">
                                            <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
                                            <span x-text="saveSuccessMsg"></span>
                                        </div>
                                    </template>

                                    <template x-if="saveErrorMsg">
                                        <div class="p-4 rounded-[14px] bg-[#FF3B30]/12 border border-[#FF3B30]/30 text-[#C41E17] dark:text-[#FF453A] text-xs font-semibold flex items-center gap-3">
                                            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                                            <span x-text="saveErrorMsg"></span>
                                        </div>
                                    </template>
                                </div>

                                <div class="lg:col-span-5 space-y-3.5">
                                    <div class="rounded-[18px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/[0.06] dark:border-white/[0.08] p-5 space-y-3 text-xs">
                                        <span class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 block">{{ __('calculator.run_summary_title') }}</span>
                                        
                                        <div class="space-y-2 divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                                            <div class="flex justify-between items-center pt-1.5">
                                                <span class="text-black/60 dark:text-white/60">{{ __('calculator.run_cost_model_label') }}</span>
                                                <span class="font-bold text-black dark:text-white" x-text="selectedCostModel ? selectedCostModel.name : (selectedProduct ? selectedProduct.name : {{ Js::from(__('calculator.selected_model_fallback')) }})"></span>
                                            </div>
                                            <div class="flex justify-between items-center pt-1.5">
                                                <span class="text-black/60 dark:text-white/60">{{ __('calculator.run_total_batch_label') }}</span>
                                                <span class="font-bold text-black dark:text-white tabular-nums" x-text="calcResult ? ('Rp ' + Math.round(calcResult.total_hpp || 0).toLocaleString(localeCode)) : 'Rp 0'"></span>
                                            </div>
                                            <div class="flex justify-between items-center pt-1.5">
                                                <span class="text-black/60 dark:text-white/60">{{ __('calculator.run_unit_cogs_label') }}</span>
                                                <span class="font-bold text-[#007AFF] dark:text-[#0A84FF] tabular-nums" x-text="calcResult ? ('Rp ' + Math.round(calcResult.hpp_per_unit || 0).toLocaleString(localeCode)) : 'Rp 0'"></span>
                                            </div>
                                            <div class="flex justify-between items-center pt-1.5">
                                                <span class="text-black/60 dark:text-white/60">{{ __('calculator.run_save_time_label') }}</span>
                                                <span class="font-medium text-black dark:text-white">{{ date('d M Y H:i') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="px-6 py-4 sm:px-8 sm:py-4.5 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-end gap-3 shrink-0 bg-black/[0.02] dark:bg-white/[0.02]">
                            <button type="button" @click="showSaveModal = false"
                                class="min-h-[44px] h-11 px-5 rounded-[12px] text-xs font-semibold text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition cursor-pointer">
                                {{ __('calculator.btn_cancel') }}
                            </button>
                            <button type="button" @click="saveResult()" :disabled="isSaving"
                                class="min-h-[44px] h-11 px-6 rounded-[12px] text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all shadow-[0_2px_4px_rgba(0,122,255,0.3)] flex items-center gap-2 cursor-pointer disabled:opacity-50">
                                <i data-lucide="bookmark-check" class="w-4 h-4"></i>
                                <span x-text="isSaving ? '{{ __('calculator.btn_saving_run') }}' : '{{ __('calculator.btn_save_run_confirm') }}'"></span>
                            </button>
                        </div>

                    </div>
                </div>
            </template>
        @endif

    </div>
@endsection
