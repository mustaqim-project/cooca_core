@extends('layouts.app', [
    'title' => __('warehouse.detail_title', ['name' => $location->name]),
    'headerTitle' => __('warehouse.detail_header', ['name' => $location->name]),
    'headerSubtitle' => __('warehouse.detail_subtitle'),
])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="{
        activeTab: new URLSearchParams(window.location.search).get('tab') || 'stocks',
        showEditModal: new URLSearchParams(window.location.search).get('edit') === '1' || new URLSearchParams(window.location.search).get('edit') === 'true',
        showAdjustModal: false,
        confirmModalOpen: false,
        confirmAction: '',
        confirmFormId: '',
        confirmTitle: '',
        confirmDesc: '',
        confirmIsDanger: false,
        searchStock: '',
        selectedStock: null,
        newQuantity: 0,
        unitCost: 0,
        reasonCode: 'opname_variance',
        notes: '',
        setTab(tab) {
            this.activeTab = tab;
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url);
        },
        openAdjust(stock) {
            this.selectedStock = stock;
            this.newQuantity = Number(stock.quantity);
            this.unitCost = Number(stock.last_cost || 0);
            this.reasonCode = 'opname_variance';
            this.notes = '';
            this.showAdjustModal = true;
        },
        openConfirm(formId, title, desc, isDanger = false) {
            this.confirmFormId = formId;
            this.confirmTitle = title;
            this.confirmDesc = desc;
            this.confirmIsDanger = isDanger;
            this.confirmModalOpen = true;
        },
        executeConfirm() {
            if (this.confirmFormId) {
                const form = document.getElementById(this.confirmFormId);
                if (form) {
                    form.submit();
                }
            }
            this.confirmModalOpen = false;
        }
    }">

        {{-- ===================================================== --}}
        {{-- 1. TOOLBAR / PAGE HEADER                                --}}
        {{-- ===================================================== --}}
        @php
            $badgeLabel = __('warehouse.types.warehouse');
            if ($location->type === 'central_kitchen') {
                if (str_starts_with($business->template_code ?? '', 'mfg_')) {
                    $badgeLabel = __('warehouse.types.central_kitchen_mfg');
                } elseif (($business->template_code ?? '') === 'service_contractor') {
                    $badgeLabel = __('warehouse.types.central_kitchen_contractor');
                } else {
                    $badgeLabel = __('warehouse.types.central_kitchen');
                }
            } elseif (in_array($location->type, ['outlet', 'store'], true)) {
                $badgeLabel = __('warehouse.types.outlet');
            }
        @endphp
        <x-module-header
            title="{{ $location->name }}"
            subtitle="{{ $location->address ?: __('warehouse.no_address') }}"
            badge="{{ $badgeLabel }}"
            :breadcrumbs="[
                ['label' => __('warehouse.breadcrumbs.dashboard'), 'url' => route('dashboard')],
                ['label' => __('warehouse.breadcrumbs.inventory'), 'url' => route('inventory.stocks')],
                ['label' => __('warehouse.breadcrumbs.warehouse_locations'), 'url' => route('warehouse.index')],
                ['label' => $location->name, 'url' => null],
            ]">
            <a href="{{ route('warehouse.index') }}"
                class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="arrow-left" class="w-4 h-4 text-black/50 dark:text-white/50"></i>
                <span>{{ __('warehouse.actions.back') }}</span>
            </a>

            @if (\App\Support\Context::hasPermission('inventory.manage'))
                <button type="button" @click="showEditModal = true"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                    <i data-lucide="pencil" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>{{ __('warehouse.actions.edit_location') }}</span>
                </button>
            @endif

            @if (\App\Support\Context::hasPermission('receiving.manage'))
                <a href="{{ route('purchase-orders.index') }}?po_type=supplier"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>{{ __('warehouse.actions.receive_from_po') }}</span>
                </a>
            @endif
        </x-module-header>

        {{-- ===================================================== --}}
        {{-- 2. MODULE TABS (SSOT)                                   --}}
        {{-- ===================================================== --}}
        <x-module-tabs module="inventory" />

        {{-- ===================================================== --}}
        {{-- FLASH MESSAGES                                        --}}
        {{-- ===================================================== --}}
        @if (session('success'))
            <div class="rounded-[14px] bg-[#34C759]/10 border border-[#34C759]/20 px-4 py-3 text-xs text-[#248A3D] dark:text-[#30D158] flex items-center gap-2.5">
                <i data-lucide="check-circle" class="w-4 h-4 shrink-0 text-[#34C759]"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif
        @if (session('warning'))
            <div class="rounded-[14px] bg-[#FF9500]/10 border border-[#FF9500]/20 px-4 py-3 text-xs text-[#B25E00] dark:text-[#FF9F0A] flex items-center gap-2.5">
                <i data-lucide="shield-alert" class="w-4 h-4 shrink-0 text-[#FF9500]"></i>
                <span class="font-medium">{{ session('warning') }}</span>
            </div>
        @endif

        {{-- ===================================================== --}}
        {{-- 3. METADATA INSPECTOR STRIP (Bento Strip)             --}}
        {{-- ===================================================== --}}
        <div class="p-4 sm:p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-[10px] bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0 text-slate-500 dark:text-slate-400">
                        <i data-lucide="map-pin" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 uppercase font-bold tracking-wider block">
                            {{ __('warehouse.inspector.physical_address') }}
                        </span>
                        <span class="text-slate-800 dark:text-slate-200 font-medium leading-relaxed">
                            {{ $location->address ?: __('warehouse.no_address') }}
                        </span>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-[10px] bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0 text-slate-500 dark:text-slate-400">
                        <i data-lucide="phone" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 uppercase font-bold tracking-wider block">
                            {{ __('warehouse.inspector.contact_phone') }}
                        </span>
                        <span class="text-slate-800 dark:text-slate-200 font-mono tabular-nums font-semibold">
                            {{ $location->phone ?: '-' }}
                        </span>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-[10px] bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0 text-slate-500 dark:text-slate-400">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 uppercase font-bold tracking-wider block">
                            {{ __('warehouse.inspector.registered_since') }}
                        </span>
                        <span class="text-slate-800 dark:text-slate-200 font-medium">
                            {{ $location->created_at->format('d M Y') }} ({{ $location->created_at->diffForHumans() }})
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- 4. COMMAND KPI METRICS (Bento Apple HIG Cards)        --}}
        {{-- ===================================================== --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            {{-- KPI 1: Total Komoditas --}}
            <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('warehouse.kpis.total_items') }}</span>
                    <div class="w-7 h-7 rounded-[8px] bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400">
                        <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl font-black tabular-nums text-slate-900 dark:text-white">{{ $stocks->total() }}</span>
                    <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">{{ __('warehouse.stock_table.col_item') }}</span>
                </div>
            </div>

            {{-- KPI 2: Total Nilai Aset --}}
            <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('warehouse.kpis.stock_asset_value') }}</span>
                    <div class="w-7 h-7 rounded-[8px] bg-[#34C759]/10 flex items-center justify-center text-[#34C759]">
                        <i data-lucide="badge-dollar-sign" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-xl sm:text-2xl font-black tabular-nums text-[#34C759] dark:text-[#30D158] truncate">
                        Rp {{ number_format($totalValuation, 0, ',', '.') }}
                    </span>
                    <span class="text-[11px] font-semibold text-[#34C759] dark:text-[#30D158]">{{ __('warehouse.kpis.cogs_valuation') }}</span>
                </div>
            </div>

            {{-- KPI 3: Stok Minimum --}}
            <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('warehouse.kpis.low_stock') }}</span>
                    <div class="w-7 h-7 rounded-[8px] {{ $lowStockCount > 0 ? 'bg-[#FF9500]/10 text-[#FF9500]' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }} flex items-center justify-center">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl font-black tabular-nums {{ $lowStockCount > 0 ? 'text-[#FF9500] dark:text-[#FF9F0A]' : 'text-slate-900 dark:text-white' }}">
                        {{ $lowStockCount }}
                    </span>
                    <span class="text-[11px] {{ $lowStockCount > 0 ? 'text-[#FF9500] dark:text-[#FF9F0A] font-bold' : 'text-slate-400 dark:text-slate-500 font-medium' }}">
                        {{ $lowStockCount > 0 ? __('warehouse.kpis.need_restock') : __('warehouse.kpis.safe_threshold') }}
                    </span>
                </div>
            </div>

            {{-- KPI 4: Penerimaan Barang PO --}}
            <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('warehouse.tabs.receipts') }}</span>
                    <div class="w-7 h-7 rounded-[8px] bg-[#5856D6]/10 flex items-center justify-center text-[#5856D6]">
                        <i data-lucide="file-check" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl font-black tabular-nums text-[#5856D6] dark:text-[#5E5CE6]">{{ $receipts->count() }}</span>
                    <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">{{ __('warehouse.receipts_table.col_gr_number') }}</span>
                </div>
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- 5. QUICK ACTION SHORTCUT CARDS                        --}}
        {{-- ===================================================== --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
            <a href="{{ route('purchase-orders.index') }}?po_type=supplier"
                class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:shadow-md active:scale-[0.98] transition-all flex items-center gap-3.5 group shadow-xs">
                <div class="w-10 h-10 rounded-[12px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center shrink-0 border border-[#FF9500]/20">
                    <i data-lucide="truck" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        {{ __('warehouse.shortcuts.receive_po_title') }}
                    </h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('warehouse.shortcuts.receive_po_desc') }}</p>
                </div>
            </a>

            <a href="{{ route('inventory.transfers.index') }}"
                class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:shadow-md active:scale-[0.98] transition-all flex items-center gap-3.5 group shadow-xs">
                <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0 border border-[#007AFF]/20">
                    <i data-lucide="arrow-left-right" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        {{ __('warehouse.shortcuts.transfer_stock_title') }}
                    </h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('warehouse.shortcuts.transfer_stock_desc') }}</p>
                </div>
            </a>

            <a href="{{ route('inventory.opnames.index') }}"
                class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:shadow-md active:scale-[0.98] transition-all flex items-center gap-3.5 group shadow-xs">
                <div class="w-10 h-10 rounded-[12px] bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center shrink-0 border border-[#AF52DE]/20">
                    <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        {{ __('warehouse.shortcuts.stock_opname_title') }}
                    </h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('warehouse.shortcuts.stock_opname_desc') }}</p>
                </div>
            </a>
        </div>

        {{-- ===================================================== --}}
        {{-- 6. PENDING APPROVAL ALERT BANNER                      --}}
        {{-- ===================================================== --}}
        @if (!empty($pendingAdjustments) && $pendingAdjustments->isNotEmpty())
            <div x-show="activeTab !== 'approvals'" class="rounded-[18px] bg-gradient-to-r from-[#FF9500]/10 via-[#FF9500]/5 to-transparent border border-[#FF9500]/30 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-[10px] bg-[#FF9500] text-white flex items-center justify-center shrink-0 shadow-sm shadow-amber-500/20">
                        <i data-lucide="shield-alert" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">
                            {{ __('warehouse.approvals.title') }}
                        </h4>
                        <p class="text-[11px] text-slate-600 dark:text-slate-400">
                            {{ __('warehouse.approvals.pending_count', ['count' => $pendingAdjustments->count()]) }} &bull; {{ __('warehouse.approvals.subtitle') }}
                        </p>
                    </div>
                </div>
                <button type="button" @click="setTab('approvals')"
                    class="min-h-[38px] px-4 rounded-[10px] text-xs font-bold text-white bg-[#FF9500] hover:bg-[#E68600] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 shrink-0 shadow-xs cursor-pointer">
                    <span>{{ __('warehouse.tabs.approvals') }}</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </button>
            </div>
        @endif

        {{-- ===================================================== --}}
        {{-- 7. 4 BENTO APPLE HIG UNDERLINE TABS                   --}}
        {{-- ===================================================== --}}
        <div class="border-b border-black/[0.08] dark:border-white/[0.08] -mb-2">
            <div class="flex items-center gap-6 sm:gap-8 overflow-x-auto no-scrollbar">
                {{-- Tab 1: Stocks --}}
                <button type="button" @click="setTab('stocks')"
                    class="relative pb-3 text-xs sm:text-sm font-semibold transition-colors flex items-center gap-2 cursor-pointer shrink-0 min-h-[44px]"
                    :class="activeTab === 'stocks' ? 'text-[#007AFF] dark:text-[#0A84FF]' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                    <i data-lucide="package" class="w-4 h-4"></i>
                    <span>{{ __('warehouse.tabs.stocks') }}</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold tabular-nums"
                        :class="activeTab === 'stocks' ? 'bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]' : 'bg-black/5 dark:bg-white/5 text-slate-500'">
                        {{ $stocks->total() }}
                    </span>
                    <div x-show="activeTab === 'stocks'" class="absolute bottom-0 inset-x-0 h-0.5 bg-[#007AFF] dark:bg-[#0A84FF] rounded-full"></div>
                </button>

                {{-- Tab 2: Receipts --}}
                <button type="button" @click="setTab('receipts')"
                    class="relative pb-3 text-xs sm:text-sm font-semibold transition-colors flex items-center gap-2 cursor-pointer shrink-0 min-h-[44px]"
                    :class="activeTab === 'receipts' ? 'text-[#007AFF] dark:text-[#0A84FF]' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                    <i data-lucide="truck" class="w-4 h-4"></i>
                    <span>{{ __('warehouse.tabs.receipts') }}</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold tabular-nums"
                        :class="activeTab === 'receipts' ? 'bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]' : 'bg-black/5 dark:bg-white/5 text-slate-500'">
                        {{ $receipts->count() }}
                    </span>
                    <div x-show="activeTab === 'receipts'" class="absolute bottom-0 inset-x-0 h-0.5 bg-[#007AFF] dark:bg-[#0A84FF] rounded-full"></div>
                </button>

                {{-- Tab 3: Movements --}}
                <button type="button" @click="setTab('movements')"
                    class="relative pb-3 text-xs sm:text-sm font-semibold transition-colors flex items-center gap-2 cursor-pointer shrink-0 min-h-[44px]"
                    :class="activeTab === 'movements' ? 'text-[#007AFF] dark:text-[#0A84FF]' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                    <i data-lucide="arrow-left-right" class="w-4 h-4"></i>
                    <span>{{ __('warehouse.tabs.movements') }}</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold tabular-nums"
                        :class="activeTab === 'movements' ? 'bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]' : 'bg-black/5 dark:bg-white/5 text-slate-500'">
                        {{ $recentMovements->count() }}
                    </span>
                    <div x-show="activeTab === 'movements'" class="absolute bottom-0 inset-x-0 h-0.5 bg-[#007AFF] dark:bg-[#0A84FF] rounded-full"></div>
                </button>

                {{-- Tab 4: Approvals (Maker-Checker) --}}
                @if (!empty($pendingAdjustments) && $pendingAdjustments->isNotEmpty())
                    <button type="button" @click="setTab('approvals')"
                        class="relative pb-3 text-xs sm:text-sm font-semibold transition-colors flex items-center gap-2 cursor-pointer shrink-0 min-h-[44px]"
                        :class="activeTab === 'approvals' ? 'text-[#FF9500] dark:text-[#FF9F0A]' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                        <i data-lucide="shield-alert" class="w-4 h-4 text-[#FF9500]"></i>
                        <span>{{ __('warehouse.tabs.approvals') }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500] text-white tabular-nums animate-pulse">
                            {{ $pendingAdjustments->count() }}
                        </span>
                        <div x-show="activeTab === 'approvals'" class="absolute bottom-0 inset-x-0 h-0.5 bg-[#FF9500] rounded-full"></div>
                    </button>
                @endif
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- TAB PANEL 1: STOCKS (Daftar Stok Produk di Lokasi)    --}}
        {{-- ===================================================== --}}
        <div x-show="activeTab === 'stocks'" class="space-y-6">
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden shadow-xs">
                <div class="p-4 sm:p-5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                            {{ __('warehouse.stock_table.title') }}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ __('warehouse.stock_table.subtitle') }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2.5">
                        {{-- Search Field --}}
                        <div class="relative w-48 sm:w-64">
                            <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                            <input type="text" x-model="searchStock" placeholder="{{ __('warehouse.stock_table.search_placeholder') }}"
                                class="w-full h-9 pl-8.5 pr-3 bg-slate-50 dark:bg-[#2C2C2E] border border-black/[0.06] dark:border-white/[0.08] rounded-[10px] text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                        </div>

                        <a href="{{ route('inventory.stocks') }}?location_id={{ $location->id }}"
                            class="text-xs text-[#007AFF] hover:underline font-bold inline-flex items-center gap-1">
                            <span>{{ __('warehouse.stock_table.all_stocks_btn') }}</span>
                            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>

                {{-- Stocks Table (Desktop) --}}
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-black/[0.06] dark:border-white/[0.08] text-[11px] font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500 bg-slate-50/50 dark:bg-[#2C2C2E]/50">
                                <th class="px-4 py-3">{{ __('warehouse.stock_table.col_item') }}</th>
                                <th class="px-4 py-3">{{ __('warehouse.stock_table.col_category') }}</th>
                                <th class="px-4 py-3 text-right">{{ __('warehouse.stock_table.col_qty') }}</th>
                                <th class="px-4 py-3 text-right">{{ __('warehouse.stock_table.col_min_stock') }}</th>
                                <th class="px-4 py-3 text-right">{{ __('warehouse.stock_table.col_unit_cost') }}</th>
                                <th class="px-4 py-3 text-right">{{ __('warehouse.stock_table.col_total_val') }}</th>
                                <th class="px-4 py-3 text-center">{{ __('warehouse.stock_table.col_status') }}</th>
                                @if (\App\Support\Context::hasPermission('inventory.manage'))
                                    <th class="px-4 py-3 text-right">{{ __('warehouse.stock_table.col_actions') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                            @forelse($stocks as $stock)
                                @php
                                    $isLow =
                                        $stock->product &&
                                        $stock->product->min_stock > 0 &&
                                        $stock->quantity <= $stock->product->min_stock;
                                    $valuation = (float) $stock->quantity * (float) $stock->last_cost;
                                @endphp
                                <tr x-show="!searchStock || '{{ strtolower($stock->product?->name . ' ' . $stock->product?->code . ' ' . ($stock->product?->category?->name ?? '')) }}'.includes(searchStock.toLowerCase())"
                                    class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors {{ $isLow ? 'bg-[#FF9500]/5' : '' }}">
                                    <td class="px-4 py-3.5">
                                        <div class="font-bold text-slate-900 dark:text-white">
                                            {{ $stock->product?->name ?? '-' }}
                                        </div>
                                        @if ($stock->product?->code)
                                            <div class="text-[11px] font-mono tabular-nums text-slate-500 dark:text-slate-400">
                                                {{ $stock->product->code }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300">
                                        {{ $stock->product?->category?->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3.5 text-right">
                                        <span class="font-bold tabular-nums text-sm {{ $isLow ? 'text-[#FF9500] dark:text-[#FF9F0A]' : 'text-slate-900 dark:text-white' }}">
                                            {{ number_format($stock->quantity, 2) }}
                                        </span>
                                        <span class="text-[11px] text-slate-400 dark:text-slate-500 ml-0.5">{{ $stock->product?->outputUnit?->symbol ?? '' }}</span>
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-mono tabular-nums text-slate-500 dark:text-slate-400">
                                        {{ $stock->product ? number_format($stock->product->min_stock, 2) : '-' }}
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-mono tabular-nums text-slate-700 dark:text-slate-300">
                                        Rp {{ number_format($stock->last_cost, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-mono tabular-nums font-bold text-[#34C759] dark:text-[#30D158]">
                                        Rp {{ number_format($valuation, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        @if ($isLow)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span> {{ __('warehouse.stock_table.status_low') }}
                                            </span>
                                        @elseif($stock->quantity <= 0)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span> {{ __('warehouse.stock_table.status_out') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> {{ __('warehouse.stock_table.status_available') }}
                                            </span>
                                        @endif
                                    </td>
                                    @if (\App\Support\Context::hasPermission('inventory.manage'))
                                        <td class="px-4 py-3.5 text-right">
                                            <button type="button"
                                                @click="openAdjust(@js([
                                                    'id' => $stock->id,
                                                    'product_id' => $stock->product_id,
                                                    'location_id' => $stock->location_id,
                                                    'product_name' => $stock->product?->name ?? '',
                                                    'quantity' => $stock->quantity,
                                                    'last_cost' => $stock->last_cost,
                                                ]))"
                                                class="min-h-[44px] sm:min-h-[32px] px-2.5 rounded-[6px] text-xs font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.98] transition-all cursor-pointer inline-flex items-center">
                                                {{ __('warehouse.actions.quick_adjust') }}
                                            </button>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-12 text-center text-slate-500 dark:text-slate-400">
                                        <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-2 text-slate-400">
                                            <i data-lucide="package" class="w-6 h-6"></i>
                                        </div>
                                        <div class="font-bold text-slate-900 dark:text-white text-sm">
                                            {{ __('warehouse.stock_table.empty') }}
                                        </div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                                            {{ __('warehouse.stock_table.empty_desc') }}
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Stocks List (Mobile Only) --}}
                <div class="sm:hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($stocks as $stock)
                        @php
                            $isLow =
                                $stock->product &&
                                $stock->product->min_stock > 0 &&
                                $stock->quantity <= $stock->product->min_stock;
                            $valuation = (float) $stock->quantity * (float) $stock->last_cost;
                        @endphp
                        <div x-show="!searchStock || '{{ strtolower($stock->product?->name . ' ' . $stock->product?->code . ' ' . ($stock->product?->category?->name ?? '')) }}'.includes(searchStock.toLowerCase())"
                            class="p-4 space-y-3 active:bg-black/[0.02] dark:active:bg-white/[0.02] transition-colors {{ $isLow ? 'bg-[#FF9500]/5' : '' }}">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h4 class="font-bold text-sm text-slate-900 dark:text-white">
                                        {{ $stock->product?->name ?? '-' }}
                                    </h4>
                                    <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                        @if ($stock->product?->code)
                                            <span class="font-mono tabular-nums">{{ $stock->product->code }}</span>
                                            <span>&bull;</span>
                                        @endif
                                        <span>{{ $stock->product?->category?->name ?? '-' }}</span>
                                    </div>
                                </div>
                                <div>
                                    @if ($isLow)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span> {{ __('warehouse.stock_table.status_low') }}
                                        </span>
                                    @elseif($stock->quantity <= 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span> {{ __('warehouse.stock_table.status_out') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> {{ __('warehouse.stock_table.status_available') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-xs bg-slate-50 dark:bg-[#2C2C2E] p-3 rounded-[12px] border border-black/[0.04] dark:border-white/[0.04]">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">{{ __('warehouse.stock_table.col_qty') }}</span>
                                    <span class="tabular-nums font-black text-sm {{ $isLow ? 'text-[#FF9500] dark:text-[#FF9F0A]' : 'text-slate-900 dark:text-white' }}">
                                        {{ number_format($stock->quantity, 2) }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500">{{ $stock->product?->outputUnit?->symbol ?? '' }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">{{ __('warehouse.stock_table.col_min_stock') }}</span>
                                    <span class="tabular-nums text-slate-700 dark:text-slate-300 font-semibold">
                                        {{ $stock->product ? number_format($stock->product->min_stock, 2) : '-' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">{{ __('warehouse.stock_table.col_unit_cost') }}</span>
                                    <span class="tabular-nums text-slate-600 dark:text-slate-400 font-semibold">
                                        Rp {{ number_format($stock->last_cost, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">{{ __('warehouse.stock_table.col_total_val') }}</span>
                                    <span class="tabular-nums font-bold text-[#34C759] dark:text-[#30D158]">
                                        Rp {{ number_format($valuation, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            @if (\App\Support\Context::hasPermission('inventory.manage'))
                                <div class="flex items-center justify-end pt-1">
                                    <button type="button"
                                        @click="openAdjust(@js([
                                            'id' => $stock->id,
                                            'product_id' => $stock->product_id,
                                            'location_id' => $stock->location_id,
                                            'product_name' => $stock->product?->name ?? '',
                                            'quantity' => $stock->quantity,
                                            'last_cost' => $stock->last_cost,
                                        ]))"
                                        class="min-h-[44px] px-3.5 rounded-[8px] text-xs font-bold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.98] transition-all inline-flex items-center gap-1.5 cursor-pointer">
                                        <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
                                        <span>{{ __('warehouse.actions.quick_adjust') }}</span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="py-10 px-4 text-center text-slate-500 dark:text-slate-400">
                            <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-2 text-slate-400">
                                <i data-lucide="package" class="w-5 h-5"></i>
                            </div>
                            <div class="font-bold text-slate-900 dark:text-white text-xs">{{ __('warehouse.stock_table.empty') }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-xs mx-auto">
                                {{ __('warehouse.stock_table.empty_desc') }}
                            </div>
                        </div>
                    @endforelse
                </div>
                @if ($stocks->hasPages())
                    <div class="p-3.5 border-t border-black/[0.06] dark:border-white/[0.08] text-xs">{{ $stocks->links() }}</div>
                @endif
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- TAB PANEL 2: RECEIPTS (Riwayat Penerimaan GRN)       --}}
        {{-- ===================================================== --}}
        <div x-show="activeTab === 'receipts'" class="space-y-6">
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden shadow-xs">
                <div class="px-4 sm:px-5 py-4 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        {{ __('warehouse.receipts_table.title') }}
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        {{ __('warehouse.receipts_table.subtitle') }}
                    </p>
                </div>
                {{-- Desktop GR Table --}}
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-black/[0.06] dark:border-white/[0.08] text-[11px] font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500 bg-slate-50/50 dark:bg-[#2C2C2E]/50">
                                <th class="px-4 py-3">{{ __('warehouse.receipts_table.col_gr_number') }}</th>
                                <th class="px-4 py-3">{{ __('warehouse.receipts_table.col_date') }}</th>
                                <th class="px-4 py-3">{{ __('warehouse.receipts_table.col_supplier') }}</th>
                                <th class="px-4 py-3">{{ __('warehouse.receipts_table.col_po_ref') }}</th>
                                <th class="px-4 py-3 text-center">{{ __('warehouse.receipts_table.col_items') }}</th>
                                <th class="px-4 py-3">{{ __('warehouse.receipts_table.col_receiver') }}</th>
                                <th class="px-4 py-3 text-center">{{ __('warehouse.receipts_table.col_status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                            @forelse ($receipts as $gr)
                                <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="px-4 py-3.5 font-mono tabular-nums font-bold text-slate-900 dark:text-white">
                                        #{{ $gr->receipt_number }}
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-600 dark:text-slate-400">
                                        {{ $gr->receipt_date?->format('d/m/Y') ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3.5 font-semibold text-slate-900 dark:text-white">
                                        {{ $gr->supplier?->name ?? __('warehouse.receipts_table.no_supplier') }}
                                    </td>
                                    <td class="px-4 py-3.5">
                                        @if ($gr->purchaseOrder)
                                            <a href="{{ route('purchase-orders.show', $gr->purchaseOrder->id) }}"
                                                class="font-mono tabular-nums text-[#007AFF] hover:underline font-bold">
                                                {{ $gr->purchaseOrder->po_number }}
                                            </a>
                                        @else
                                            <span class="text-slate-400 dark:text-slate-500 font-medium">
                                                {{ __('warehouse.receipts_table.one_click_solo') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-center font-mono tabular-nums font-bold text-slate-900 dark:text-white">
                                        {{ $gr->items->count() }}
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-600 dark:text-slate-400">
                                        {{ $gr->receiver?->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> {{ __('warehouse.receipts_table.status_received') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-500 dark:text-slate-400">
                                        <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-2 text-slate-400">
                                            <i data-lucide="truck" class="w-6 h-6"></i>
                                        </div>
                                        <div class="font-bold text-slate-900 dark:text-white text-sm">
                                            {{ __('warehouse.receipts_table.empty') }}
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile GR Cards --}}
                <div class="sm:hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse ($receipts as $gr)
                        <div class="p-4 space-y-2.5 active:bg-black/[0.02] dark:active:bg-white/[0.02] transition-colors">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="font-mono tabular-nums font-bold text-xs text-slate-900 dark:text-white">#{{ $gr->receipt_number }}</span>
                                    <div class="font-bold text-xs text-slate-800 dark:text-slate-200 mt-0.5">
                                        {{ $gr->supplier?->name ?? __('warehouse.receipts_table.no_supplier') }}
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> {{ __('warehouse.receipts_table.status_received') }}
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-xs bg-slate-50 dark:bg-[#2C2C2E] p-2.5 rounded-[10px]">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">{{ __('warehouse.receipts_table.col_date') }}</span>
                                    <span class="text-slate-700 dark:text-slate-300">{{ $gr->receipt_date?->format('d/m/Y') ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">{{ __('warehouse.receipts_table.col_po_ref') }}</span>
                                    @if ($gr->purchaseOrder)
                                        <a href="{{ route('purchase-orders.show', $gr->purchaseOrder->id) }}"
                                            class="font-mono tabular-nums text-[#007AFF] font-bold">
                                            {{ $gr->purchaseOrder->po_number }}
                                        </a>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500">{{ __('warehouse.receipts_table.one_click_solo') }}</span>
                                    @endif
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">{{ __('warehouse.receipts_table.col_items') }}</span>
                                    <span class="tabular-nums font-bold text-slate-900 dark:text-white">{{ $gr->items->count() }} item</span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">{{ __('warehouse.receipts_table.col_receiver') }}</span>
                                    <span class="text-slate-700 dark:text-slate-300 truncate block">{{ $gr->receiver?->name ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-10 px-4 text-center text-slate-500 dark:text-slate-400">
                            <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-2 text-slate-400">
                                <i data-lucide="truck" class="w-6 h-6"></i>
                            </div>
                            <div class="font-bold text-slate-900 dark:text-white text-xs">{{ __('warehouse.receipts_table.empty') }}</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- TAB PANEL 3: MOVEMENTS (Kartu Stok - Mutasi Terkini)   --}}
        {{-- ===================================================== --}}
        <div x-show="activeTab === 'movements'" class="space-y-6">
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden shadow-xs">
                <div class="px-4 sm:px-5 py-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                            {{ __('warehouse.movements_table.title') }}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ __('warehouse.movements_table.subtitle') }}
                        </p>
                    </div>
                    <a href="{{ route('inventory.movements') }}?location_id={{ $location->id }}"
                        class="text-xs font-bold text-[#007AFF] hover:underline flex items-center gap-1">
                        <span>{{ __('warehouse.movements_table.see_all_movements') }}</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
                {{-- Desktop Movements Table --}}
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-black/[0.06] dark:border-white/[0.08] text-[11px] font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500 bg-slate-50/50 dark:bg-[#2C2C2E]/50">
                                <th class="px-4 py-3">{{ __('warehouse.movements_table.col_item') }}</th>
                                <th class="px-4 py-3">{{ __('warehouse.movements_table.col_type') }}</th>
                                <th class="px-4 py-3">{{ __('warehouse.movements_table.col_ref') }}</th>
                                <th class="px-4 py-3 text-right">{{ __('warehouse.movements_table.col_delta') }}</th>
                                <th class="px-4 py-3 text-right">{{ __('warehouse.movements_table.col_balance') }}</th>
                                <th class="px-4 py-3">{{ __('warehouse.movements_table.col_operator') }}</th>
                                <th class="px-4 py-3 text-right">{{ __('warehouse.movements_table.col_datetime') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                            @forelse ($recentMovements as $mv)
                                @php
                                    $mvLabels = [
                                        'goods_receipt' => ['label' => __('warehouse.movements_table.type_goods_receipt'), 'pill' => 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]'],
                                        'pos_sale' => ['label' => __('warehouse.movements_table.type_pos_sale'), 'pill' => 'bg-[#007AFF]/12 text-[#007AFF]'],
                                        'adjustment' => ['label' => __('warehouse.movements_table.type_adjustment'), 'pill' => 'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]'],
                                        'transfer_in' => ['label' => __('warehouse.movements_table.type_transfer_in'), 'pill' => 'bg-[#5856D6]/12 text-[#413FA6] dark:text-[#5E5CE6]'],
                                        'transfer_out' => ['label' => __('warehouse.movements_table.type_transfer_out'), 'pill' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'],
                                        'opname' => ['label' => __('warehouse.movements_table.type_opname'), 'pill' => 'bg-[#AF52DE]/12 text-[#7C3AA6] dark:text-[#BF5AF2]'],
                                        'initial' => ['label' => __('warehouse.movements_table.type_initial'), 'pill' => 'bg-[#007AFF]/12 text-[#007AFF]'],
                                        'pos_refund' => ['label' => __('warehouse.movements_table.type_pos_refund'), 'pill' => 'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]'],
                                    ];
                                    $mvInfo = $mvLabels[$mv->movement_type] ?? ['label' => ucfirst(str_replace('_', ' ', $mv->movement_type)), 'pill' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'];
                                @endphp
                                <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="px-4 py-3.5">
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $mv->product?->name ?? '-' }}</div>
                                        @if ($mv->product?->code)
                                            <div class="text-[11px] font-mono tabular-nums text-slate-500 dark:text-slate-400">{{ $mv->product->code }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold {{ $mvInfo['pill'] }}">
                                            {{ $mvInfo['label'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 font-mono tabular-nums text-slate-500 dark:text-slate-400 text-xs">
                                        {{ $mv->reference_number ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-mono tabular-nums font-bold {{ $mv->quantity_change >= 0 ? 'text-[#34C759] dark:text-[#30D158]' : 'text-[#FF3B30] dark:text-[#FF453A]' }}">
                                        {{ $mv->quantity_change >= 0 ? '+' : '' }}{{ number_format($mv->quantity_change, 2) }}
                                        <span class="text-[11px] text-slate-400 dark:text-slate-500 font-normal ml-0.5">{{ $mv->product?->outputUnit?->symbol }}</span>
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-mono tabular-nums font-bold text-slate-900 dark:text-white">
                                        {{ number_format($mv->balance_after, 2) }}
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-600 dark:text-slate-400 font-medium">
                                        {{ $mv->creator?->name ?? 'System' }}
                                    </td>
                                    <td class="px-4 py-3.5 text-right text-[11px] text-slate-400 dark:text-slate-500 whitespace-nowrap font-medium">
                                        {{ $mv->created_at->format('d/m/Y H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-500 dark:text-slate-400">
                                        <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-2 text-slate-400">
                                            <i data-lucide="arrow-left-right" class="w-6 h-6"></i>
                                        </div>
                                        <div class="font-bold text-slate-900 dark:text-white text-sm">
                                            {{ __('warehouse.movements_table.empty') }}
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Movements Cards --}}
                <div class="sm:hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse ($recentMovements as $mv)
                        @php
                            $mvLabels = [
                                'goods_receipt' => ['label' => __('warehouse.movements_table.type_goods_receipt'), 'pill' => 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]'],
                                'pos_sale' => ['label' => __('warehouse.movements_table.type_pos_sale'), 'pill' => 'bg-[#007AFF]/12 text-[#007AFF]'],
                                'adjustment' => ['label' => __('warehouse.movements_table.type_adjustment'), 'pill' => 'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]'],
                                'transfer_in' => ['label' => __('warehouse.movements_table.type_transfer_in'), 'pill' => 'bg-[#5856D6]/12 text-[#413FA6] dark:text-[#5E5CE6]'],
                                'transfer_out' => ['label' => __('warehouse.movements_table.type_transfer_out'), 'pill' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'],
                                'opname' => ['label' => __('warehouse.movements_table.type_opname'), 'pill' => 'bg-[#AF52DE]/12 text-[#7C3AA6] dark:text-[#BF5AF2]'],
                                'initial' => ['label' => __('warehouse.movements_table.type_initial'), 'pill' => 'bg-[#007AFF]/12 text-[#007AFF]'],
                                'pos_refund' => ['label' => __('warehouse.movements_table.type_pos_refund'), 'pill' => 'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]'],
                            ];
                            $mvInfo = $mvLabels[$mv->movement_type] ?? ['label' => ucfirst(str_replace('_', ' ', $mv->movement_type)), 'pill' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'];
                        @endphp
                        <div class="p-4 space-y-2.5 active:bg-black/[0.02] dark:active:bg-white/[0.02] transition-colors">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="font-bold text-xs text-slate-900 dark:text-white">{{ $mv->product?->name ?? '-' }}</div>
                                    @if ($mv->product?->code)
                                        <span class="text-[11px] font-mono tabular-nums text-slate-500 dark:text-slate-400">{{ $mv->product->code }}</span>
                                    @endif
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $mvInfo['pill'] }} shrink-0">
                                    {{ $mvInfo['label'] }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-xs bg-slate-50 dark:bg-[#2C2C2E] p-2.5 rounded-[10px]">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">{{ __('warehouse.movements_table.col_delta') }}</span>
                                    <span class="tabular-nums font-bold {{ $mv->quantity_change >= 0 ? 'text-[#34C759] dark:text-[#30D158]' : 'text-[#FF3B30] dark:text-[#FF453A]' }}">
                                        {{ $mv->quantity_change >= 0 ? '+' : '' }}{{ number_format($mv->quantity_change, 2) }} {{ $mv->product?->outputUnit?->symbol }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">{{ __('warehouse.movements_table.col_balance') }}</span>
                                    <span class="tabular-nums font-bold text-slate-900 dark:text-white">
                                        {{ number_format($mv->balance_after, 2) }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">{{ __('warehouse.movements_table.col_ref') }}</span>
                                    <span class="font-mono text-[11px] text-slate-600 dark:text-slate-400 truncate block">{{ $mv->reference_number ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">{{ __('warehouse.movements_table.col_datetime') }}</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400">{{ $mv->created_at->format('d/m/y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-10 px-4 text-center text-slate-500 dark:text-slate-400">
                            <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-2 text-slate-400">
                                <i data-lucide="arrow-left-right" class="w-6 h-6"></i>
                            </div>
                            <div class="font-bold text-slate-900 dark:text-white text-xs">{{ __('warehouse.movements_table.empty') }}</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- TAB PANEL 4: APPROVALS (Maker-Checker Otorisasi)      --}}
        {{-- ===================================================== --}}
        <div x-show="activeTab === 'approvals'" class="space-y-6">
            @if (!empty($pendingAdjustments) && $pendingAdjustments->isNotEmpty())
                <div class="rounded-[20px] bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-transparent dark:from-amber-500/15 dark:to-transparent border border-amber-500/30 p-5 space-y-4 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[12px] bg-[#FF9500] text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-500/20">
                                <i data-lucide="shield-alert" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                                        {{ __('warehouse.approvals.title') }}
                                    </h3>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500] text-white tabular-nums">
                                        {{ __('warehouse.approvals.pending_count', ['count' => $pendingAdjustments->count()]) }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">
                                    {{ __('warehouse.approvals.subtitle') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="divide-y divide-amber-500/20 rounded-[14px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-amber-500/20 overflow-hidden">
                        @foreach ($pendingAdjustments as $adj)
                            <div class="p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div class="space-y-1.5 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-mono font-bold text-xs text-slate-900 dark:text-white">#{{ $adj->adjustment_number }}</span>
                                        <span class="text-[11px] text-slate-400 dark:text-slate-500">&bull;</span>
                                        <span class="text-xs text-slate-600 dark:text-slate-400">{{ $adj->adjustment_date?->format('d/m/Y') }}</span>
                                        <span class="text-[11px] text-slate-400 dark:text-slate-500">&bull;</span>
                                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                            {{ __('warehouse.approvals.submitted_by', ['name' => $adj->creator?->name ?? 'Staf']) }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-slate-800 dark:text-slate-200">
                                        @foreach ($adj->items as $item)
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold">{{ $item->product?->name }}</span>:
                                                <span class="font-mono text-red-600 dark:text-red-400 font-semibold">{{ $item->difference_quantity > 0 ? '+' : '' }}{{ number_format($item->difference_quantity, 2) }} {{ $item->product?->outputUnit?->symbol }}</span>
                                                <span class="text-slate-400">({{ __('warehouse.approvals.loss_estimate') }} <strong class="font-mono text-red-600 dark:text-red-400">Rp {{ number_format($item->total_cost, 0, ',', '.') }}</strong>)</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if ($adj->notes)
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 italic">
                                            "{{ $adj->notes }}"
                                        </p>
                                    @endif
                                </div>

                                @if (\App\Support\Context::isOwner() || auth()->user()?->hasRole('owner') || auth()->user()?->hasRole('supervisor'))
                                    <div class="flex items-center gap-2 shrink-0">
                                        <form id="reject-adj-{{ $adj->id }}" method="POST" action="{{ route('inventory.adjustments.reject', $adj->id) }}">
                                            @csrf
                                            <button type="button"
                                                @click="openConfirm('reject-adj-{{ $adj->id }}', '{{ __('warehouse.approvals.modal_reject_title') }}', '{{ __('warehouse.approvals.modal_reject_desc') }}', true)"
                                                class="min-h-[44px] sm:min-h-0 h-9 px-3.5 rounded-[10px] text-xs font-semibold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition-all flex items-center gap-1.5 cursor-pointer">
                                                <i data-lucide="x-circle" class="w-4 h-4"></i>
                                                <span>{{ __('warehouse.approvals.reject_btn') }}</span>
                                            </button>
                                        </form>

                                        <form id="approve-adj-{{ $adj->id }}" method="POST" action="{{ route('inventory.adjustments.approve', $adj->id) }}">
                                            @csrf
                                            <button type="button"
                                                @click="openConfirm('approve-adj-{{ $adj->id }}', '{{ __('warehouse.approvals.modal_approve_title') }}', '{{ __('warehouse.approvals.modal_approve_desc') }}', false)"
                                                class="min-h-[44px] sm:min-h-0 h-9 px-4 rounded-[10px] text-xs font-bold text-white bg-[#34C759] hover:bg-[#2FB34F] active:scale-[0.98] transition-all flex items-center gap-1.5 shadow-sm shadow-green-600/20 cursor-pointer">
                                                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                                                <span>{{ __('warehouse.approvals.approve_btn') }}</span>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="px-3 py-1.5 rounded-[10px] text-xs font-medium text-amber-700 dark:text-amber-300 bg-amber-500/10 border border-amber-500/20">
                                        {{ __('warehouse.approvals.waiting_owner_auth') }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-12 text-center text-slate-500 dark:text-slate-400 shadow-xs">
                    <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-2 text-slate-400">
                        <i data-lucide="shield-check" class="w-6 h-6"></i>
                    </div>
                    <div class="font-bold text-slate-900 dark:text-white text-sm">
                        {{ __('warehouse.approvals.title') }}
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                        {{ __('warehouse.approvals.subtitle') }}
                    </div>
                </div>
            @endif
        </div>

        {{-- ===================================================== --}}
        {{-- APPLE CONFIRMATION MODAL SHEET (MAKER-CHECKER ACTION) --}}
        {{-- ===================================================== --}}
        <div x-show="confirmModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-md p-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="w-full max-w-md rounded-[22px] bg-white/98 dark:bg-[#1C1C1E]/98 backdrop-blur-2xl border border-black/[0.08] dark:border-white/[0.12] shadow-[0_25px_60px_rgba(0,0,0,0.35)] p-6 space-y-4 overflow-hidden text-center"
                @click.outside="confirmModalOpen = false"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">

                <div class="w-12 h-12 rounded-[14px] mx-auto flex items-center justify-center"
                    :class="confirmIsDanger ? 'bg-[#FF3B30]/12 text-[#FF3B30]' : 'bg-[#34C759]/12 text-[#34C759]'">
                    <template x-if="confirmIsDanger">
                        <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                    </template>
                    <template x-if="!confirmIsDanger">
                        <i data-lucide="check-circle-2" class="w-6 h-6"></i>
                    </template>
                </div>

                <div class="space-y-1.5">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white" x-text="confirmTitle"></h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed" x-text="confirmDesc"></p>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-2">
                    <button type="button" @click="confirmModalOpen = false"
                        class="min-h-[44px] px-4 rounded-[12px] text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition cursor-pointer">
                        {{ __('warehouse.actions.cancel') }}
                    </button>
                    <button type="button" @click="executeConfirm()"
                        class="min-h-[44px] px-4 rounded-[12px] text-xs font-bold text-white transition cursor-pointer active:scale-[0.98]"
                        :class="confirmIsDanger ? 'bg-[#FF3B30] hover:bg-[#E02D22]' : 'bg-[#34C759] hover:bg-[#2FB34F]'">
                        <span x-text="confirmIsDanger ? '{{ __('warehouse.actions.reject') }}' : '{{ __('warehouse.actions.approve') }}'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- 8. APPLE BENTO XXL SHEET: EDIT GUDANG / LOKASI        --}}
        {{-- ===================================================== --}}
        <div x-show="showEditModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-md p-3 sm:p-6"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="w-full max-w-[94vw] md:max-w-2xl lg:max-w-3xl xl:max-w-4xl max-h-[88vh] rounded-[22px] bg-white/98 dark:bg-[#1C1C1E]/98 backdrop-blur-2xl border border-black/[0.08] dark:border-white/[0.12] shadow-[0_25px_60px_rgba(0,0,0,0.35)] flex flex-col overflow-hidden"
                @click.outside="showEditModal = false" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95">

                {{-- Modal Header --}}
                <div class="px-5 py-4 sm:px-6 sm:py-4.5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-3 shrink-0 bg-slate-50/50 dark:bg-white/[0.02]">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-[14px] bg-[#007AFF]/12 text-[#007AFF] flex items-center justify-center shrink-0 border border-[#007AFF]/20">
                            <i data-lucide="pencil-line" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                                <span>{{ __('warehouse.actions.edit_location_title', ['name' => $location->name]) }}</span>
                                <span class="text-[11px] font-semibold text-[#007AFF] bg-[#007AFF]/12 px-2.5 py-0.5 rounded-full">
                                    {{ __('warehouse.actions.edit_location') }}
                                </span>
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ __('warehouse.sections.general_info_desc') }}
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="showEditModal = false"
                        class="min-w-[44px] min-h-[44px] w-11 h-11 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form action="{{ route('warehouse.update', $location->id) }}" method="POST"
                    x-data="{
                        submitting: false,
                        tzMode: '{{ $location->timezone_mode ?? 'inherit' }}',
                        customTimezone: '{{ $location->timezone ?? ($business->timezone ?? 'Asia/Jakarta') }}',
                        ohMode: '{{ $location->operating_hours_mode ?? 'inherit' }}',
                        operatingHours: @json(\App\Support\TimezoneHelper::normalizeOperatingHours($location->operating_hours ?: $business->operating_hours)),
                        addPeriod(dayKey) {
                            if (!this.operatingHours[dayKey]) {
                                this.operatingHours[dayKey] = { day_name: dayKey, is_open: true, periods: [] };
                            }
                            if (!Array.isArray(this.operatingHours[dayKey].periods)) {
                                this.operatingHours[dayKey].periods = [];
                            }
                            this.operatingHours[dayKey].periods.push({ start: '17:00', end: '22:00' });
                        },
                        removePeriod(dayKey, index) {
                            if (this.operatingHours[dayKey] && Array.isArray(this.operatingHours[dayKey].periods)) {
                                this.operatingHours[dayKey].periods.splice(index, 1);
                            }
                        },
                        isOvernight(period) {
                            if (!period || !period.start || !period.end) return false;
                            return period.end < period.start;
                        }
                    }"
                    @submit="submitting = true" class="flex flex-col flex-1 overflow-hidden">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="operating_hours_json" :value="JSON.stringify(operatingHours)">
                    
                    <div class="p-5 sm:p-6 overflow-y-auto space-y-4 flex-1 text-xs">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                            
                            {{-- Kolom Kiri (6 Kolom) --}}
                            <div class="lg:col-span-6 space-y-4">
                                <div class="rounded-[18px] bg-slate-50/80 dark:bg-[#2C2C2E]/60 border border-black/[0.06] dark:border-white/[0.08] p-5 space-y-4">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="info" class="w-4 h-4 text-[#007AFF]"></i>
                                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                            {{ __('warehouse.sections.general_info') }}
                                        </span>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                            {{ __('warehouse.fields.name') }}
                                        </label>
                                        <input type="text" name="name" value="{{ $location->name }}" required
                                            class="w-full h-11 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                {{ __('warehouse.fields.type') }}
                                            </label>
                                            <select name="type"
                                                class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                                <option value="warehouse" {{ $location->type === 'warehouse' ? 'selected' : '' }}>{{ __('warehouse.types.warehouse') }}</option>
                                                <option value="outlet" {{ $location->type === 'outlet' ? 'selected' : '' }}>{{ __('warehouse.types.outlet') }}</option>
                                                @if(str_starts_with($business->template_code ?? '', 'fnb_'))
                                                    <option value="central_kitchen" {{ $location->type === 'central_kitchen' ? 'selected' : '' }}>{{ __('warehouse.types.central_kitchen') }}</option>
                                                @elseif(str_starts_with($business->template_code ?? '', 'mfg_'))
                                                    <option value="central_kitchen" {{ $location->type === 'central_kitchen' ? 'selected' : '' }}>{{ __('warehouse.types.central_kitchen_mfg') }}</option>
                                                @elseif(($business->template_code ?? '') === 'service_contractor')
                                                    <option value="central_kitchen" {{ $location->type === 'central_kitchen' ? 'selected' : '' }}>{{ __('warehouse.types.central_kitchen_contractor') }}</option>
                                                @elseif($location->type === 'central_kitchen')
                                                    <option value="central_kitchen" selected>{{ __('warehouse.types.central_kitchen') }}</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                {{ __('warehouse.fields.code') }}
                                            </label>
                                            <input type="text" name="code" value="{{ $location->code }}"
                                                class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            {{ __('warehouse.fields.phone') }}
                                        </label>
                                        <input type="text" name="phone" value="{{ $location->phone }}"
                                            class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                    </div>
                                </div>
                            </div>

                            {{-- Kolom Kanan (6 Kolom) --}}
                            <div class="lg:col-span-6 space-y-4">
                                <div class="rounded-[18px] bg-slate-50/80 dark:bg-[#2C2C2E]/60 border border-black/[0.06] dark:border-white/[0.08] p-5 space-y-4">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="map-pin" class="w-4 h-4 text-[#007AFF]"></i>
                                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                            {{ __('warehouse.sections.address_logistics') }}
                                        </span>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            {{ __('warehouse.fields.address') }}
                                        </label>
                                        <textarea name="address" rows="3"
                                            class="w-full bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] p-3 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition resize-none">{{ $location->address }}</textarea>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            {{ __('warehouse.fields.is_active') }}
                                        </label>
                                        <select name="is_active"
                                            class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                            <option value="1" {{ $location->is_active ? 'selected' : '' }}>{{ __('warehouse.badges.active') }}</option>
                                            <option value="0" {{ !$location->is_active ? 'selected' : '' }}>{{ __('warehouse.badges.inactive') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Kolom Penuh: Zona Waktu & Jam Operasional Cabang --}}
                            <div class="lg:col-span-12 space-y-4">
                                <div class="rounded-[18px] bg-slate-50/80 dark:bg-[#2C2C2E]/60 border border-black/[0.06] dark:border-white/[0.08] p-5 space-y-5">
                                    <div class="flex items-center gap-2 border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                                        <i data-lucide="clock" class="w-4 h-4 text-[#FF9500]"></i>
                                        <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                            Zona Waktu &amp; Jam Operasional Cabang
                                        </span>
                                    </div>

                                    {{-- Timezone Inheritance or Custom --}}
                                    <div class="space-y-2.5">
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                                            Pengaturan Zona Waktu Cabang
                                        </label>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <label class="flex items-start gap-3 p-3 rounded-[12px] border cursor-pointer transition-all"
                                                :class="tzMode === 'inherit' ? 'bg-[#007AFF]/10 border-[#007AFF]/30 text-slate-900 dark:text-white' : 'bg-white dark:bg-[#1C1C1E] border-black/[0.08] dark:border-white/[0.12] text-slate-600 dark:text-slate-400'">
                                                <input type="radio" name="timezone_mode" value="inherit" x-model="tzMode" class="mt-0.5">
                                                <div>
                                                    <div class="font-semibold text-xs text-slate-900 dark:text-white">Ikuti Usaha Pusat</div>
                                                    <div class="text-[11px] opacity-75">Zona waktu otomatis mengikuti acuan pusat ({{ $business->timezone ?? 'Asia/Jakarta' }})</div>
                                                </div>
                                            </label>
                                            <label class="flex items-start gap-3 p-3 rounded-[12px] border cursor-pointer transition-all"
                                                :class="tzMode === 'custom' ? 'bg-[#007AFF]/10 border-[#007AFF]/30 text-slate-900 dark:text-white' : 'bg-white dark:bg-[#1C1C1E] border-black/[0.08] dark:border-white/[0.12] text-slate-600 dark:text-slate-400'">
                                                <input type="radio" name="timezone_mode" value="custom" x-model="tzMode" class="mt-0.5">
                                                <div>
                                                    <div class="font-semibold text-xs text-slate-900 dark:text-white">Zona Waktu Khusus</div>
                                                    <div class="text-[11px] opacity-75">Atur zona waktu berbeda untuk cabang di luar pulau (WITA, WIT, dll.)</div>
                                                </div>
                                            </label>
                                        </div>

                                        <div x-show="tzMode === 'custom'" class="pt-2">
                                            <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1">
                                                Pilih Zona Waktu Cabang Ini
                                            </label>
                                            <select name="timezone" x-model="customTimezone"
                                                class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                                @foreach ($timezones as $groupLabel => $zones)
                                                    <optgroup label="{{ $groupLabel }}">
                                                        @foreach ($zones as $zoneId => $zoneName)
                                                            <option value="{{ $zoneId }}">{{ $zoneName }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Operating Hours Inheritance or Custom --}}
                                    <div class="space-y-2.5 pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                                            Pengaturan Jadwal Jam Kerja Cabang
                                        </label>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <label class="flex items-start gap-3 p-3 rounded-[12px] border cursor-pointer transition-all"
                                                :class="ohMode === 'inherit' ? 'bg-[#007AFF]/10 border-[#007AFF]/30 text-slate-900 dark:text-white' : 'bg-white dark:bg-[#1C1C1E] border-black/[0.08] dark:border-white/[0.12] text-slate-600 dark:text-slate-400'">
                                                <input type="radio" name="operating_hours_mode" value="inherit" x-model="ohMode" class="mt-0.5">
                                                <div>
                                                    <div class="font-semibold text-xs text-slate-900 dark:text-white">Ikuti Jam Usaha Pusat</div>
                                                    <div class="text-[11px] opacity-75">Jadwal buka-tutup cabang ini otomatis sinkron dengan jam operasional bisnis pusat</div>
                                                </div>
                                            </label>
                                            <label class="flex items-start gap-3 p-3 rounded-[12px] border cursor-pointer transition-all"
                                                :class="ohMode === 'custom' ? 'bg-[#007AFF]/10 border-[#007AFF]/30 text-slate-900 dark:text-white' : 'bg-white dark:bg-[#1C1C1E] border-black/[0.08] dark:border-white/[0.12] text-slate-600 dark:text-slate-400'">
                                                <input type="radio" name="operating_hours_mode" value="custom" x-model="ohMode" class="mt-0.5">
                                                <div>
                                                    <div class="font-semibold text-xs text-slate-900 dark:text-white">Jadwal Khusus Cabang</div>
                                                    <div class="text-[11px] opacity-75">Cabang memiliki jam operasional sendiri yang berbeda dari pusat</div>
                                                </div>
                                            </label>
                                        </div>

                                        {{-- Weekly custom scheduler --}}
                                        <div x-show="ohMode === 'custom'" class="pt-3 space-y-2.5">
                                            <template x-for="(dayData, dayKey) in operatingHours" :key="dayKey">
                                                <div class="p-3 rounded-[12px] border border-black/[0.06] dark:border-white/[0.08] bg-white dark:bg-[#1C1C1E] space-y-2">
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center gap-2.5">
                                                            <input type="checkbox" x-model="dayData.is_open" class="rounded border-slate-300 text-[#007AFF] focus:ring-[#007AFF]">
                                                            <span class="font-semibold text-xs text-slate-900 dark:text-white" x-text="dayData.day_name"></span>
                                                            <span class="text-[10px] px-1.5 py-0.2 rounded font-medium"
                                                                :class="dayData.is_open ? 'bg-[#34C759]/10 text-[#34C759]' : 'bg-slate-100 dark:bg-slate-800 text-slate-500'"
                                                                x-text="dayData.is_open ? 'Buka' : 'Tutup'"></span>
                                                        </div>
                                                        <template x-if="dayData.is_open">
                                                            <button type="button" @click="addPeriod(dayKey)"
                                                                class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 cursor-pointer">
                                                                <i data-lucide="plus" class="w-3 h-3"></i>
                                                                <span>Tambah Sesi</span>
                                                            </button>
                                                        </template>
                                                    </div>

                                                    <template x-if="dayData.is_open">
                                                        <div class="space-y-1.5 pl-6">
                                                            <template x-for="(period, pIdx) in dayData.periods" :key="pIdx">
                                                                <div class="flex items-center gap-2">
                                                                    <input type="time" x-model="period.start" class="h-8 px-2 bg-slate-50 dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] rounded-[6px] text-xs">
                                                                    <span class="text-slate-400">-</span>
                                                                    <input type="time" x-model="period.end" class="h-8 px-2 bg-slate-50 dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] rounded-[6px] text-xs">
                                                                    
                                                                    <template x-if="isOvernight(period)">
                                                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-[#AF52DE]/10 text-[#AF52DE]">Overnight</span>
                                                                    </template>

                                                                    <template x-if="dayData.periods.length > 1">
                                                                        <button type="button" @click="removePeriod(dayKey, pIdx)" class="text-slate-400 hover:text-red-500 p-1">
                                                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                                                        </button>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="px-5 py-3.5 sm:px-6 sm:py-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-end gap-3 shrink-0 bg-slate-50/50 dark:bg-white/[0.02]">
                        <button type="button" @click="showEditModal = false"
                            class="min-h-[44px] h-11 px-5 rounded-[12px] text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                            {{ __('warehouse.actions.cancel') }}
                        </button>
                        <button type="submit" :disabled="submitting"
                            class="min-h-[44px] h-11 px-6 rounded-[12px] text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all shadow-[0_2px_4px_rgba(0,122,255,0.25)] cursor-pointer flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <template x-if="submitting">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            <template x-if="!submitting">
                                <i data-lucide="check" class="w-4 h-4"></i>
                            </template>
                            <span x-text="submitting ? '{{ __('warehouse.actions.submitting') }}' : '{{ __('warehouse.actions.save_changes') }}'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- 9. APPLE BENTO XXL SHEET: PENYESUAIAN STOK (ADJUST)   --}}
        {{-- ===================================================== --}}
        @if (\App\Support\Context::hasPermission('inventory.manage'))
            <div x-show="showAdjustModal" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-md p-3 sm:p-6"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                <div class="w-full max-w-[94vw] md:max-w-2xl lg:max-w-3xl xl:max-w-4xl max-h-[88vh] rounded-[22px] bg-white/98 dark:bg-[#1C1C1E]/98 backdrop-blur-2xl border border-black/[0.08] dark:border-white/[0.12] shadow-[0_25px_60px_rgba(0,0,0,0.35)] flex flex-col overflow-hidden"
                    @click.outside="showAdjustModal = false" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95">

                    {{-- Modal Header --}}
                    <div class="px-5 py-4 sm:px-6 sm:py-4.5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-3 shrink-0 bg-slate-50/50 dark:bg-white/[0.02]">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-[14px] bg-[#007AFF]/12 text-[#007AFF] flex items-center justify-center shrink-0 border border-[#007AFF]/20">
                                <i data-lucide="sliders" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                                    <span>{{ __('warehouse.adjust_modal.title') }}</span>
                                    <span class="text-[11px] font-semibold text-[#007AFF] bg-[#007AFF]/12 px-2.5 py-0.5 rounded-full" x-text="selectedStock ? selectedStock.product_name : ''"></span>
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ __('warehouse.adjust_modal.subtitle') }}
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="showAdjustModal = false"
                            class="min-w-[44px] min-h-[44px] w-11 h-11 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition cursor-pointer">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <form action="{{ route('inventory.stocks.adjust') }}" method="POST" x-data="{ submitting: false }" @submit="submitting = true" class="flex flex-col flex-1 overflow-hidden">
                        @csrf
                        <input type="hidden" name="product_id" x-bind:value="selectedStock?.product_id">
                        <input type="hidden" name="location_id" value="{{ $location->id }}">

                        <div class="p-5 sm:p-6 overflow-y-auto space-y-4 flex-1 text-xs">
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                                
                                {{-- Kolom Kiri: Input Parameter Penyesuaian (6 Kolom) --}}
                                <div class="lg:col-span-6 space-y-4">
                                    <div class="rounded-[18px] bg-slate-50/80 dark:bg-[#2C2C2E]/60 border border-black/[0.06] dark:border-white/[0.08] p-5 space-y-4">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="package" class="w-4 h-4 text-[#007AFF]"></i>
                                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                                {{ __('warehouse.adjust_modal.section_input') }}
                                            </span>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                {{ __('warehouse.adjust_modal.field_new_qty') }}
                                            </label>
                                            <input type="number" name="new_quantity" step="any" x-model="newQuantity" min="0" required
                                                class="w-full h-11 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono tabular-nums font-bold text-sm focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                {{ __('warehouse.adjust_modal.field_cost') }}
                                            </label>
                                            <input type="number" name="unit_cost" step="any" x-model="unitCost" min="0"
                                                class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                {{ __('warehouse.adjust_modal.field_reason') }}
                                            </label>
                                            <select name="reason_code" x-model="reasonCode" required
                                                class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                                <option value="opname_variance">{{ __('warehouse.adjust_modal.reason_variance') }}</option>
                                                <option value="damaged">{{ __('warehouse.adjust_modal.reason_damaged') }}</option>
                                                <option value="expired">{{ __('warehouse.adjust_modal.reason_expired') }}</option>
                                                <option value="theft_loss">{{ __('warehouse.adjust_modal.reason_theft') }}</option>
                                                <option value="initial_balance">{{ __('warehouse.adjust_modal.reason_initial') }}</option>
                                                <option value="other">{{ __('warehouse.adjust_modal.reason_other') }}</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                {{ __('warehouse.adjust_modal.field_notes') }} <span x-show="reasonCode === 'other'" class="text-[#FF3B30]">*</span>
                                            </label>
                                            <input type="text" name="notes" x-model="notes"
                                                :placeholder="reasonCode === 'other' ? '{{ __('warehouse.adjust_modal.placeholder_other') }}' : '{{ __('warehouse.adjust_modal.placeholder_example') }}'"
                                                :required="reasonCode === 'other'"
                                                class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                            <p x-show="reasonCode === 'other'" class="text-[10px] text-amber-600 dark:text-amber-400 mt-1">
                                                {{ __('warehouse.adjust_modal.notes_other_warning') }}
                                            </p>
                                        </div>

                                        {{-- Supervisor PIN Prompt on High-Value / Volume Shrinkage --}}
                                        <div x-show="(Number(newQuantity) - Number(selectedStock?.quantity || 0)) < 0 && (Math.abs(Number(newQuantity) - Number(selectedStock?.quantity || 0)) > 10 || Math.abs((Number(newQuantity) - Number(selectedStock?.quantity || 0)) * Number(unitCost || 0)) > 100000)"
                                            x-transition
                                            class="p-3.5 rounded-[14px] bg-red-500/10 border border-red-500/30 space-y-2">
                                            <div class="flex items-center gap-2 text-red-600 dark:text-red-400">
                                                <i data-lucide="shield-alert" class="w-4 h-4 shrink-0"></i>
                                                <span class="font-bold text-xs">{{ __('warehouse.adjust_modal.supervisor_pin_title') }}</span>
                                            </div>
                                            <p class="text-[11px] text-red-700 dark:text-red-300 leading-relaxed">
                                                {{ __('warehouse.adjust_modal.supervisor_pin_desc') }}
                                            </p>
                                            <div>
                                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                    {{ __('warehouse.adjust_modal.supervisor_pin_field') }}
                                                </label>
                                                <input type="password" name="supervisor_pin" maxlength="10" placeholder="{{ __('warehouse.adjust_modal.supervisor_pin_placeholder') }}"
                                                    :required="(Number(newQuantity) - Number(selectedStock?.quantity || 0)) < 0 && (Math.abs(Number(newQuantity) - Number(selectedStock?.quantity || 0)) > 10 || Math.abs((Number(newQuantity) - Number(selectedStock?.quantity || 0)) * Number(unitCost || 0)) > 100000)"
                                                    class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-red-300 dark:border-red-500/40 rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono tracking-widest focus:outline-none focus:ring-2 focus:ring-red-500 transition">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Kolom Kanan: Bento Live Comparison & Impact Calculation (6 Kolom) --}}
                                <div class="lg:col-span-6 space-y-4">
                                    <div class="rounded-[18px] bg-slate-50 dark:bg-[#2C2C2E] border border-black/[0.06] dark:border-white/[0.08] p-5 space-y-4">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="calculator" class="w-4 h-4 text-[#007AFF]"></i>
                                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                                {{ __('warehouse.adjust_modal.section_impact') }}
                                            </span>
                                        </div>

                                        <div class="grid grid-cols-2 gap-3">
                                            <div class="p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">
                                                    {{ __('warehouse.adjust_modal.current_system_stock') }}
                                                </span>
                                                <span class="text-sm font-bold text-slate-900 dark:text-white tabular-nums font-mono">
                                                    <span x-text="Number(selectedStock?.quantity || 0).toLocaleString('id-ID')"></span>
                                                </span>
                                            </div>
                                            <div class="p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">
                                                    {{ __('warehouse.adjust_modal.new_physical_qty') }}
                                                </span>
                                                <span class="text-sm font-bold text-[#007AFF] tabular-nums font-mono">
                                                    <span x-text="Number(newQuantity || 0).toLocaleString('id-ID')"></span>
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Selisih Perubahan (Delta) Card --}}
                                        <div class="p-4 rounded-[14px] border"
                                            :class="(Number(newQuantity) - Number(selectedStock?.quantity || 0)) > 0 
                                                ? 'bg-[#34C759]/10 border-[#34C759]/30 text-[#248A3D] dark:text-[#30D158]' 
                                                : ((Number(newQuantity) - Number(selectedStock?.quantity || 0)) < 0 
                                                    ? 'bg-[#FF3B30]/10 border-[#FF3B30]/30 text-[#C41E17] dark:text-[#FF453A]' 
                                                    : 'bg-black/5 dark:bg-white/5 border-black/10 dark:border-white/10 text-slate-600 dark:text-slate-400')">
                                            <div class="flex items-center justify-between">
                                                <span class="font-semibold text-xs">{{ __('warehouse.adjust_modal.delta_card_title') }}</span>
                                                <span class="font-bold text-sm font-mono tabular-nums">
                                                    <span x-text="(Number(newQuantity) - Number(selectedStock?.quantity || 0)) > 0 ? '+' : ''"></span>
                                                    <span x-text="(Number(newQuantity) - Number(selectedStock?.quantity || 0)).toLocaleString('id-ID')"></span> Unit
                                                </span>
                                            </div>
                                            <div class="flex items-center justify-between pt-2 mt-2 border-t border-black/10 dark:border-white/10 text-[11px]">
                                                <span>{{ __('warehouse.adjust_modal.valuation_delta_title') }}</span>
                                                <span class="font-bold font-mono tabular-nums">
                                                    Rp <span x-text="Math.abs((Number(newQuantity) - Number(selectedStock?.quantity || 0)) * Number(unitCost || 0)).toLocaleString('id-ID')"></span>
                                                </span>
                                            </div>
                                        </div>

                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                                            {{ __('warehouse.adjust_modal.audit_trail_notice') }}
                                        </p>
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="px-5 py-3.5 sm:px-6 sm:py-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-end gap-3 shrink-0 bg-slate-50/50 dark:bg-white/[0.02]">
                            <button type="button" @click="showAdjustModal = false"
                                class="min-h-[44px] h-11 px-5 rounded-[12px] text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                                {{ __('warehouse.actions.cancel') }}
                            </button>
                            <button type="submit" :disabled="submitting"
                                class="min-h-[44px] h-11 px-6 rounded-[12px] text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all shadow-[0_2px_4px_rgba(0,122,255,0.25)] cursor-pointer flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                <template x-if="submitting">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </template>
                                <template x-if="!submitting">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                </template>
                                <span x-text="submitting ? '{{ __('warehouse.actions.submitting') }}' : '{{ __('warehouse.actions.submit_adjustment') }}'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

    </div>
@endsection
