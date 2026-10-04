@extends('layouts.app', [
    'title' => __('purchasing.title'),
    'headerTitle' => __('purchasing.header_title'),
    'headerSubtitle' => __('purchasing.header_subtitle')
])

@section('content')
@php
    $canCustomerPo = $business->isModuleEnabled('customer_po') || $business->isModuleEnabled('b2b_sales');
    $currentStatus = request('status', 'all');
    $currentType = request('po_type', '');
@endphp

<div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="{
    instantModalOpen: false,
    deleteModalOpen: false,
    isInstantSubmitting: false,
    deleteTarget: { id: null, number: '' },
    stockInType: 'product',
    openDelete(id, number) {
        this.deleteTarget = { id, number };
        this.deleteModalOpen = true;
    },
    closeDelete() {
        this.deleteModalOpen = false;
        this.deleteTarget = { id: null, number: '' };
    },
    submitDelete() {
        if (this.deleteTarget.id) {
            document.getElementById('form-delete-po-' + this.deleteTarget.id).submit();
        }
    }
}">

    {{-- ===================================================== --}}
    {{-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Style)          --}}
    {{-- ===================================================== --}}
    <x-module-header
        :title="__('purchasing.header_title')"
        :subtitle="__('purchasing.header_subtitle')">
        @if(\App\Support\Context::hasPermission('purchasing.manage'))
            <button type="button" @click="instantModalOpen = true"
                    class="min-h-[44px] px-3.5 rounded-[10px] text-[13px] font-medium text-[#FF9500] dark:text-[#FF9F0A] bg-[#FF9500]/10 hover:bg-[#FF9500]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="zap" class="w-4 h-4"></i>
                <span>{{ __('purchasing.actions.instant_stock_in') }}</span>
            </button>

            <a href="{{ route('purchase-orders.create') }}" 
               class="min-h-[44px] px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>{{ __('purchasing.actions.create_po') }}</span>
            </a>
        @endif
    </x-module-header>

    {{-- ===================================================== --}}
    {{-- 2. MODULE TABS (SSOT)                                 --}}
    {{-- ===================================================== --}}
    <x-module-tabs module="purchasing" />

    <!-- ===================================================== -->
    <!-- 3. KPI SUMMARY ROW (Bento HIG)                        -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Tile 1: Total Pesanan -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('purchasing.kpis.total_orders') }}</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums text-black dark:text-white">{{ $totalOrders }} PO</span>
                <span class="text-[11px] text-black/40 dark:text-white/40">{{ __('purchasing.kpis.accumulation') }}</span>
            </div>
        </div>

        <!-- Tile 2: Dikonfirmasi (System Blue) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('purchasing.kpis.confirmed') }}</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF]">{{ $totalConfirmed }} PO</span>
                <span class="text-[11px] font-semibold text-[#007AFF] dark:text-[#0A84FF]">{{ __('purchasing.kpis.scheduled') }}</span>
            </div>
        </div>

        <!-- Tile 3: Sudah Jadi Faktur (System Purple) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('purchasing.kpis.invoiced') }}</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums text-[#AF52DE] dark:text-[#BF5AF2]">{{ $totalInvoiced }} PO</span>
                <span class="text-[11px] font-semibold text-[#AF52DE] dark:text-[#BF5AF2]">{{ __('purchasing.kpis.invoiced_badge') }}</span>
            </div>
        </div>

        <!-- Tile 4: Nilai Total Pesanan (System Green) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('purchasing.kpis.total_value') }}</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">
                    {{ $business->currency_symbol }} {{ number_format($totalSum, 0, ',', '.') }}
                </span>
                <span class="text-[11px] font-semibold text-[#34C759] dark:text-[#30D158]">{{ __('purchasing.kpis.revenue_cost') }}</span>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 4. CONTROLS: SEGMENTED TABS & FILTERS                 -->
    <!-- ===================================================== -->
    <div class="space-y-3">
        <!-- Bento Segmented Control Tabs for Status (Deep-Linking) -->
        <div class="flex items-center overflow-x-auto p-1 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] text-[13px] font-medium">
            <a href="{{ route('purchase-orders.index', array_merge(request()->query(), ['status' => 'all'])) }}"
               class="px-4 py-1.5 rounded-[9px] whitespace-nowrap transition-all {{ $currentStatus === 'all' || empty($currentStatus) ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                {{ __('purchasing.tabs.all') }}
            </a>
            <a href="{{ route('purchase-orders.index', array_merge(request()->query(), ['status' => 'draft'])) }}"
               class="px-4 py-1.5 rounded-[9px] whitespace-nowrap transition-all {{ $currentStatus === 'draft' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                {{ __('purchasing.tabs.draft') }}
            </a>
            <a href="{{ route('purchase-orders.index', array_merge(request()->query(), ['status' => 'confirmed'])) }}"
               class="px-4 py-1.5 rounded-[9px] whitespace-nowrap transition-all {{ $currentStatus === 'confirmed' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                {{ __('purchasing.tabs.confirmed') }}
            </a>
            <a href="{{ route('purchase-orders.index', array_merge(request()->query(), ['status' => 'fully_invoiced'])) }}"
               class="px-4 py-1.5 rounded-[9px] whitespace-nowrap transition-all {{ $currentStatus === 'fully_invoiced' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                {{ __('purchasing.tabs.invoiced') }}
            </a>
            <a href="{{ route('purchase-orders.index', array_merge(request()->query(), ['status' => 'cancelled'])) }}"
               class="px-4 py-1.5 rounded-[9px] whitespace-nowrap transition-all {{ $currentStatus === 'cancelled' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                {{ __('purchasing.tabs.cancelled') }}
            </a>
        </div>

        <!-- Search Bar and Secondary Filters -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 sm:p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <form method="GET" action="{{ route('purchase-orders.index') }}" class="w-full flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <input type="hidden" name="status" value="{{ $currentStatus }}">

                <!-- macOS Search Field -->
                <div class="relative flex-1">
                    <svg class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="{{ __('purchasing.placeholders.search') }}" 
                           class="w-full h-11 sm:h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-9 pr-3 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                @if($canCustomerPo)
                <!-- Tipe PO Filter (Dynamic Context-Aware Auto-Hiding) -->
                <div class="flex items-center gap-2">
                    <select name="po_type" onchange="this.form.submit()"
                            class="h-11 sm:h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="">{{ __('purchasing.filters.all_types') }}</option>
                        <option value="customer" {{ $currentType === 'customer' ? 'selected' : '' }}>{{ __('purchasing.types.customer') }}</option>
                        <option value="supplier" {{ $currentType === 'supplier' ? 'selected' : '' }}>{{ __('purchasing.types.supplier') }}</option>
                    </select>
                </div>
                @endif

                @if(request('search') || request('po_type') || (request('status') && request('status') !== 'all'))
                <a href="{{ route('purchase-orders.index') }}" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3 rounded-[10px] text-[13px] font-medium text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] transition-colors inline-flex items-center justify-center gap-1">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    <span>{{ __('purchasing.actions.reset') }}</span>
                </a>
                @endif
            </form>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 5. DENSE DATA TABLE (DESKTOP & TABLET)                 -->
    <!-- ===================================================== -->
    <div class="hidden sm:block rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10">
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">{{ __('purchasing.fields.po_number_date') }}</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">{{ __('purchasing.fields.po_type') }}</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">{{ __('purchasing.fields.related_party') }}</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">{{ __('purchasing.fields.status') }}</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">{{ __('purchasing.fields.items_count') }}</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">{{ __('purchasing.fields.total_amount') }}</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">{{ __('purchasing.fields.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($purchaseOrders as $po)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3">
                            <a href="{{ route('purchase-orders.show', $po->id) }}" class="font-medium text-[#007AFF] hover:underline tabular-nums">
                                {{ $po->po_number }}
                            </a>
                            <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 tabular-nums">{{ $po->order_date?->translatedFormat('d M Y') }}</div>
                        </td>
                        <td class="px-4 py-3">
                            @if($po->po_type === 'customer')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                    {{ __('purchasing.types.customer_badge') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF]">
                                    {{ __('purchasing.types.supplier_badge') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($po->customer)
                                <div class="font-medium text-black dark:text-white">{{ $po->customer->name }}</div>
                                @if($po->customer->company_name)
                                    <div class="text-[11px] text-black/45 dark:text-white/45">{{ $po->customer->company_name }}</div>
                                @endif
                            @elseif($po->supplier)
                                <div class="font-medium text-black dark:text-white">{{ $po->supplier->name }}</div>
                                <div class="text-[11px] text-black/45 dark:text-white/45">{{ $po->supplier->contact_person }}</div>
                            @else
                                <span class="text-black/30 dark:text-white/30">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $statusBadges = [
                                    'draft' => 'bg-black/6 dark:bg-white/8 text-black/60 dark:text-white/60',
                                    'confirmed' => 'bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF]',
                                    'partially_invoiced' => 'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]',
                                    'fully_invoiced' => 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]',
                                    'completed' => 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]',
                                    'cancelled' => 'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]',
                                ];
                                $badgeClass = $statusBadges[$po->status] ?? 'bg-black/6 dark:bg-white/8 text-black/60 dark:text-white/60';
                                $statusLabel = __('purchasing.statuses.' . $po->status);
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $badgeClass }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center tabular-nums text-black/70 dark:text-white/70">
                            {{ $po->items->count() }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold tabular-nums text-black dark:text-white">
                            {{ $business->currency_symbol }} {{ number_format((float) $po->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('purchase-orders.show', $po->id) }}" class="h-8 px-2.5 rounded-[6px] text-[12px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors inline-flex items-center" title="{{ __('purchasing.actions.view_detail') }}">
                                    {{ __('purchasing.actions.view_detail') }}
                                </a>

                                <a href="{{ route('purchase-orders.print', $po->id) }}?download=1" target="_blank" class="h-8 w-8 rounded-[6px] text-black/60 hover:text-black dark:text-white/60 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition-colors inline-flex items-center justify-center" title="{{ __('purchasing.actions.download_pdf') }}">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                </a>

                                <a href="{{ route('purchase-orders.print', $po->id) }}" target="_blank" class="h-8 w-8 rounded-[6px] text-black/60 hover:text-black dark:text-white/60 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition-colors inline-flex items-center justify-center" title="{{ __('purchasing.actions.print') }}">
                                    <i data-lucide="printer" class="w-4 h-4"></i>
                                </a>

                                @if(\App\Support\Context::hasPermission('invoices.create') && $po->po_type === 'customer' && $po->status !== 'fully_invoiced' && $po->status !== 'cancelled')
                                <form method="POST" action="{{ route('purchase-orders.generate-invoice', $po->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="h-8 px-2 rounded-[6px] text-[12px] font-medium text-[#34C759] hover:bg-[#34C759]/10 transition-colors inline-flex items-center" title="{{ __('purchasing.actions.generate_invoice_title') }}">
                                        {{ __('purchasing.actions.generate_invoice') }}
                                    </button>
                                </form>
                                @endif

                                @if((\App\Support\Context::hasPermission('receiving.manage') || \App\Support\Context::hasPermission('purchasing.manage')) && $po->po_type === 'supplier' && $po->status === 'confirmed')
                                <a href="{{ route('purchasing.receipts.create', $po->id) }}" class="h-8 px-2 rounded-[6px] text-[12px] font-medium text-[#FF9500] hover:bg-[#FF9500]/10 transition-colors inline-flex items-center" title="{{ __('purchasing.actions.receive_goods_title') }}">
                                    {{ __('purchasing.actions.receive_goods') }}
                                </a>
                                @endif

                                @if(\App\Support\Context::hasPermission('purchasing.manage') && $po->status === 'draft')
                                <button type="button" @click="openDelete({{ $po->id }}, '{{ addslashes($po->po_number) }}')" class="h-8 w-8 rounded-[6px] text-[#FF3B30] hover:bg-[#FF3B30]/10 transition-colors inline-flex items-center justify-center" title="{{ __('purchasing.actions.delete_draft') }}">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                                <form id="form-delete-po-{{ $po->id }}" method="POST" action="{{ route('purchase-orders.destroy', $po->id) }}" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-[13px] text-black/40 dark:text-white/40">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <i data-lucide="file-text" class="w-8 h-8 text-black/20 dark:text-white/20"></i>
                                <span>{{ __('purchasing.messages.empty_state') }}</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 6. MOBILE GROUPED INSET LIST (iOS 18 Style)            -->
    <!-- ===================================================== -->
    <div class="sm:hidden rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
        @forelse($purchaseOrders as $po)
        <a href="{{ route('purchase-orders.show', $po->id) }}" class="p-4 flex items-center justify-between gap-3 active:bg-black/[0.03] dark:active:bg-white/[0.05] transition-colors">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <p class="text-[16px] font-medium text-black dark:text-white truncate tabular-nums">{{ $po->po_number }}</p>
                    @if($po->status === 'fully_invoiced' || $po->status === 'completed')
                        <span class="w-2 h-2 rounded-full bg-[#34C759] shrink-0"></span>
                    @elseif($po->status === 'confirmed')
                        <span class="w-2 h-2 rounded-full bg-[#007AFF] shrink-0"></span>
                    @elseif($po->status === 'partially_invoiced')
                        <span class="w-2 h-2 rounded-full bg-[#FF9500] shrink-0"></span>
                    @elseif($po->status === 'cancelled')
                        <span class="w-2 h-2 rounded-full bg-[#FF3B30] shrink-0"></span>
                    @else
                        <span class="w-2 h-2 rounded-full bg-black/30 dark:bg-white/30 shrink-0"></span>
                    @endif
                </div>
                <p class="text-[14px] text-black/60 dark:text-white/60 truncate mt-0.5">
                    {{ $po->customer ? $po->customer->name : ($po->supplier ? $po->supplier->name : '-') }}
                </p>
                <div class="flex items-center gap-2 mt-1 text-[13px] text-black/45 dark:text-white/45 tabular-nums">
                    <span>{{ $po->order_date?->translatedFormat('d M Y') }}</span>
                    <span>&bull;</span>
                    <span class="font-semibold text-black dark:text-white">{{ $business->currency_symbol }} {{ number_format((float) $po->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="flex items-center gap-1 text-black/30 dark:text-white/30 shrink-0">
                <i data-lucide="chevron-right" class="w-5 h-5"></i>
            </div>
        </a>
        @empty
        <div class="p-8 text-center text-[13px] text-black/40 dark:text-white/40">
            {{ __('purchasing.messages.empty_state') }}
        </div>
        @endforelse
    </div>

    <!-- ===================================================== -->
    <!-- 7. PAGINATION                                         -->
    <!-- ===================================================== -->
    @if($purchaseOrders->hasPages())
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3">
        {{ $purchaseOrders->links() }}
    </div>
    @endif

    @if(\App\Support\Context::hasPermission('purchasing.manage'))
    <!-- ===================================================== -->
    <!-- 8. INSTANT STOCK-IN SHEET MODAL (Bahan & Kas Outflow) -->
    <!-- ===================================================== -->
    <div x-show="instantModalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px] p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-full max-w-lg rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.25)] space-y-4"
            @click.away="instantModalOpen = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <div class="px-5 pt-5 pb-3 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <div>
                    <h3 class="text-[16px] font-semibold text-black dark:text-white">{{ __('purchasing.instant_stock_in.title') }}</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('purchasing.instant_stock_in.subtitle') }}</p>
                </div>
                <button type="button" @click="instantModalOpen = false" class="min-h-[44px] min-w-[44px] rounded-[8px] text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 flex items-center justify-center">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('purchasing.instant-stock-in') }}" method="POST" @submit="if(isInstantSubmitting) { $event.preventDefault(); return; } isInstantSubmitting = true" class="p-5 pt-0 space-y-4 text-[13px]">
                @csrf
                
                <!-- Stock Type Switcher: Produk vs Bahan Baku -->
                <div class="flex items-center gap-1 p-1 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] text-[13px] font-medium">
                    <button type="button" @click="stockInType = 'product'"
                            :class="stockInType === 'product' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55'"
                            class="flex-1 py-1.5 rounded-[7px] text-center transition-all">
                        {{ __('purchasing.instant_stock_in.item_product') }}
                    </button>
                    <button type="button" @click="stockInType = 'material'"
                            :class="stockInType === 'material' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55'"
                            class="flex-1 py-1.5 rounded-[7px] text-center transition-all">
                        {{ __('purchasing.instant_stock_in.item_material') }}
                    </button>
                </div>

                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">
                        {{ __('purchasing.instant_stock_in.destination_warehouse') }} <span class="text-[#FF3B30]">*</span>
                    </label>
                    <select name="location_id" required class="w-full h-11 sm:h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        @foreach($locations as $loc)
                        <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Product Selector -->
                <div x-show="stockInType === 'product'">
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">
                        {{ __('purchasing.fields.item') }} <span class="text-[#FF3B30]">*</span>
                    </label>
                    <select name="product_id" :required="stockInType === 'product'" class="w-full h-11 sm:h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="">{{ __('purchasing.placeholders.choose_product') }}</option>
                        @foreach($products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Material Selector -->
                <div x-show="stockInType === 'material'" style="display: none;">
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">
                        {{ __('purchasing.instant_stock_in.item_material') }} <span class="text-[#FF3B30]">*</span>
                    </label>
                    <select name="material_id" :required="stockInType === 'material'" class="w-full h-11 sm:h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="">{{ __('purchasing.placeholders.choose_material') }}</option>
                        @foreach($materials as $m)
                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">
                            {{ __('purchasing.instant_stock_in.quantity_in') }} <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="number" name="quantity" min="0.0001" step="any" required placeholder="10"
                               class="w-full h-11 sm:h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] tabular-nums font-semibold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">
                            {{ __('purchasing.instant_stock_in.unit_cost') }} <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="number" name="unit_cost" min="0" step="any" required placeholder="25000"
                               class="w-full h-11 sm:h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] tabular-nums font-semibold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>

                <!-- Sumber Kas Pembayaran (Accounting Ledger Outflow) -->
                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">
                        {{ __('purchasing.instant_stock_in.cash_account') }}
                    </label>
                    <select name="cash_account_id" class="w-full h-11 sm:h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="">{{ __('purchasing.instant_stock_in.no_cash_outflow') }}</option>
                        @foreach($cashAccounts as $ca)
                        <option value="{{ $ca->id }}">{{ $ca->name }} ({{ $ca->account_number ?? 'Tunai' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">
                        {{ __('purchasing.instant_stock_in.supplier_optional') }}
                    </label>
                    <select name="supplier_id" class="w-full h-11 sm:h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="">{{ __('purchasing.instant_stock_in.no_supplier_market') }}</option>
                        @foreach($suppliers as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">{{ __('purchasing.fields.notes') }}</label>
                    <input type="text" name="notes" placeholder="{{ __('purchasing.instant_stock_in.notes_placeholder') }}"
                           class="w-full h-11 sm:h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="instantModalOpen = false" class="min-h-[44px] px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.97] transition-all">
                        {{ __('purchasing.actions.cancel') }}
                    </button>
                    <button type="submit" :disabled="isInstantSubmitting" class="min-h-[44px] px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 disabled:opacity-50 transition-all shadow-[0_1px_2px_rgba(0,122,255,0.25)] flex items-center gap-1.5">
                        <template x-if="!isInstantSubmitting">
                            <i data-lucide="check" class="w-4 h-4"></i>
                        </template>
                        <template x-if="isInstantSubmitting">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                        </template>
                        <span x-text="isInstantSubmitting ? '{{ __('purchasing.actions.submitting') }}' : '{{ __('purchasing.instant_stock_in.submit') }}'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if(\App\Support\Context::hasPermission('purchasing.manage'))
    <!-- ===================================================== -->
    <!-- 9. APPLE ALERT DIALOG (Hapus Draft PO)                 -->
    <!-- ===================================================== -->
    <div x-show="deleteModalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px]"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-[290px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
            @click.away="closeDelete()"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <div class="px-4 pt-5 pb-4">
                <p class="text-[17px] font-semibold text-black dark:text-white">{{ __('purchasing.modals.delete_title') }}</p>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                    {{ __('purchasing.modals.delete_desc') }} <span x-text="deleteTarget.number" class="font-medium text-black dark:text-white tabular-nums"></span>.
                </p>
            </div>

            <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                <button type="button" @click="closeDelete()" class="py-3 text-[#007AFF] border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    {{ __('purchasing.actions.cancel') }}
                </button>
                <button type="button" @click="submitDelete()" class="py-3 text-[#FF3B30] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    {{ __('purchasing.modals.delete_confirm') }}
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
