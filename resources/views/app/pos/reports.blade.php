@extends('layouts.app', ['title' => __('pos.reports_title')])

@section('content')
<div class="max-w-[1440px] mx-auto space-y-6 pb-16">

    {{-- 1. MODULE HEADER & EXPORT ACTIONS --}}
    <x-module-header
        module="pos"
        :title="__('pos.reports_title')"
        :subtitle="__('pos.reports_subtitle')">
        <x-slot:actions>
            @if(\App\Support\Context::hasPermission('pos.reports_export') || \App\Support\Context::hasPermission('pos.reports'))
                <a id="btnPosExportExcel" href="{{ route('pos.reports.export-excel', request()->query()) }}"
                    class="min-h-[40px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all flex items-center justify-center gap-2 shadow-xs">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-[#34C759]"></i>
                    <span>{{ __('pos.reports_export_excel') }}</span>
                </a>

                <a id="btnPosPrintSummary" href="{{ route('pos.reports.print-summary', request()->query()) }}" target="_blank"
                    class="min-h-[40px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all flex items-center justify-center gap-2 shadow-xs cursor-pointer">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>{{ __('pos.reports_print_pdf') }}</span>
                </a>
            @endif
        </x-slot:actions>
    </x-module-header>

    <x-module-tabs module="pos" />

    {{-- 2. MULTI-DIMENSIONAL FILTER BAR (FASE 3) --}}
    @include('app.pos.reports.partials.filter_bar')

    {{-- 3. 15-TAB MODULAR SEGMENTED CONTROLS (FASE 4) --}}
    @php
        $tabDefinitions = [
            'overview' => ['label' => 'Ringkasan', 'icon' => 'layout-dashboard'],
            'transactions' => ['label' => 'Buku Transaksi', 'icon' => 'receipt'],
            'products' => ['label' => 'Produk & Menu', 'icon' => 'package'],
            'categories' => ['label' => 'Kategori', 'icon' => 'tag'],
            'cashiers' => ['label' => 'Kasir & Staf', 'icon' => 'users'],
            'outlets' => ['label' => 'Cabang', 'icon' => 'store'],
            'payments' => ['label' => 'Pembayaran', 'icon' => 'credit-card'],
            'discounts' => ['label' => 'Audit Diskon', 'icon' => 'percent'],
            'refunds' => ['label' => 'Retur & Refund', 'icon' => 'rotate-ccw'],
            'voids' => ['label' => 'Audit Void/Fraud', 'icon' => 'shield-alert'],
            'shifts' => ['label' => 'Rekonsiliasi Kas Shift', 'icon' => 'coins'],
            'hourly' => ['label' => 'Jam Sibuk (Heatmap)', 'icon' => 'clock'],
            'customers' => ['label' => 'Pelanggan', 'icon' => 'user-check'],
            'channels' => ['label' => 'Saluran Jual', 'icon' => 'shopping-bag'],
            'profitability' => ['label' => 'Margin & HPP', 'icon' => 'trending-up'],
        ];
    @endphp

    <div class="border-b border-black/10 dark:border-white/10 overflow-x-auto no-scrollbar py-1">
        <nav class="flex items-center gap-1.5 min-w-max">
            @foreach ($tabDefinitions as $tKey => $tMeta)
                @php
                    $queryParams = array_merge(request()->query(), ['tab' => $tKey]);
                    $isActive = $activeTab === $tKey;
                @endphp
                <a href="{{ route('pos.reports.index', $queryParams) }}"
                    class="flex items-center gap-2 px-3.5 py-2 rounded-[10px] text-[13px] font-semibold transition-all {{ $isActive ? 'bg-[#007AFF] text-white shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.06]' }}">
                    <i data-lucide="{{ $tMeta['icon'] }}" class="w-4 h-4 {{ $isActive ? 'text-white' : 'text-black/50 dark:text-white/50' }}"></i>
                    <span>{{ $tMeta['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    {{-- 4. DYNAMIC TAB VIEW CONTENT --}}
    <div>
        @if (view()->exists("app.pos.reports.tabs.{$activeTab}"))
            @include("app.pos.reports.tabs.{$activeTab}")
        @else
            @include('app.pos.reports.tabs.overview')
        @endif
    </div>

    {{-- 5. SLIDE-OVER QUICK-VIEW MODAL (FASE 5) --}}
    @include('app.pos.reports.partials.order_detail_modal')

</div>
@endsection
