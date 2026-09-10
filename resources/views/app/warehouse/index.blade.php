@extends('layouts.app', [
    'title' => 'Gudang & Lokasi Penyimpanan',
    'headerTitle' => 'Gudang & Lokasi Penyimpanan',
    'headerSubtitle' => 'Kelola fisik penyimpanan multi-lokasi, cabang outlet, dan pergerakan stok barang.'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
    showCreateModal: false,
    showEditModal: false,
    editData: {
        id: null,
        name: '',
        type: 'warehouse',
        code: '',
        phone: '',
        address: '',
        is_active: true
    },
    deleteModalOpen: false,
    deleteTarget: { id: null, name: '' },

    openEdit(loc) {
        this.editData = {
            id: loc.id,
            name: loc.name,
            type: loc.type || 'warehouse',
            code: loc.code || '',
            phone: loc.phone || '',
            address: loc.address || '',
            is_active: Boolean(loc.is_active)
        };
        this.showEditModal = true;
    },
    openDelete(id, name) {
        this.deleteTarget = { id, name };
        this.deleteModalOpen = true;
    },
    closeDelete() {
        this.deleteModalOpen = false;
        this.deleteTarget = { id: null, name: '' };
    },
    submitDelete() {
        if (this.deleteTarget.id) {
            const form = document.getElementById('form-delete-location-' + this.deleteTarget.id);
            if (form) form.submit();
        }
    }
}">

    {{-- ===================================================== --}}
    {{-- 1. SUB-NAVIGATION TABS (Apple Segmented Control)      --}}
    {{-- ===================================================== --}}
    <div class="overflow-x-auto pb-1 scrollbar-none">
        <div class="inline-flex p-1 rounded-[11px] bg-black/[0.05] dark:bg-white/[0.07] border border-black/5 dark:border-white/10 text-[13px] font-medium whitespace-nowrap">
            <a href="{{ route('warehouse.index') }}"
               class="px-3.5 py-1.5 rounded-[9px] bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-semibold flex items-center gap-1.5 transition-all">
                <svg class="w-4 h-4 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
                <span>Gudang &amp; Lokasi</span>
                <span class="px-1.5 py-0.2 rounded-full text-[11px] tabular-nums font-semibold bg-[#007AFF]/12 text-[#007AFF]">{{ $locations->count() }}</span>
            </a>
            <a href="{{ route('inventory.index') }}"
               class="px-3.5 py-1.5 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                </svg>
                <span>Stok Inventori</span>
            </a>
            <a href="{{ route('inventory.transfers') }}"
               class="px-3.5 py-1.5 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                </svg>
                <span>Transfer Stok</span>
            </a>
            <a href="{{ route('inventory.opnames') }}"
               class="px-3.5 py-1.5 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                </svg>
                <span>Stock Opname</span>
            </a>
            <a href="{{ route('inventory.movements') }}"
               class="px-3.5 py-1.5 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />
                </svg>
                <span>Riwayat Mutasi</span>
            </a>
            <a href="{{ route('purchase-orders.index') }}"
               class="px-3.5 py-1.5 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                </svg>
                <span>PO Supplier</span>
            </a>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- 2. TOOLBAR / PAGE HEADER (macOS Sonoma Style)         --}}
    {{-- ===================================================== --}}
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            {{-- Breadcrumb minimal --}}
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <span class="text-black/70 dark:text-white/70 font-medium">Inventori</span>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Gudang &amp; Lokasi</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Gudang &amp; Lokasi Penyimpanan</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Kelola pusat penyimpanan fisik, cabang outlet, dan pergerakan stok barang</p>
        </div>

        {{-- Toolbar Actions --}}
        <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
            @if(\App\Support\Context::hasPermission('inventory.view'))
            <a href="{{ route('materials.index') }}"
               class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                </svg>
                <span>Katalog Bahan</span>
            </a>
            <a href="{{ route('products.index') }}"
               class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                </svg>
                <span>Katalog Produk</span>
            </a>
            @endif

            @if(\App\Support\Context::hasPermission('inventory.manage'))
            <button type="button" @click="showCreateModal = true"
                    class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Gudang</span>
            </button>
            @endif
        </div>
    </header>

    {{-- ===================================================== --}}
    {{-- FLASH MESSAGES                                        --}}
    {{-- ===================================================== --}}
    @if(session('success'))
    <div class="rounded-[12px] bg-[#34C759]/10 border border-[#34C759]/20 px-4 py-3 text-[13px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-2.5">
        <svg class="w-4 h-4 shrink-0 text-[#34C759]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="rounded-[12px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 px-4 py-3 text-[13px] text-[#C41E17] dark:text-[#FF453A] flex items-center gap-2.5">
        <svg class="w-4 h-4 shrink-0 text-[#FF3B30]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <span class="font-medium">{{ session('error') }}</span>
    </div>
    @endif

    {{-- ===================================================== --}}
    {{-- 3. KPI SUMMARY (Flat Neutral Apple HIG Cards)         --}}
    {{-- ===================================================== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        {{-- Tile 1: Total Lokasi --}}
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Lokasi</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-black dark:text-white">{{ $stats['total_locations'] ?? 0 }}</span>
                <span class="text-[11px] text-black/40 dark:text-white/40">Gudang &amp; Cabang</span>
            </div>
        </div>

        {{-- Tile 2: Lokasi Aktif --}}
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Lokasi Aktif</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">{{ $stats['active_locations'] ?? 0 }}</span>
                <span class="text-[11px] font-medium text-[#34C759] dark:text-[#30D158]">Siap Operasi</span>
            </div>
        </div>

        {{-- Tile 3: Total SKU Terdaftar --}}
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">SKU Terdaftar</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-[#5856D6] dark:text-[#5E5CE6]">{{ number_format($stats['total_sku'] ?? 0, 0, ',', '.') }}</span>
                <span class="text-[11px] text-black/40 dark:text-white/40">Item Fisik</span>
            </div>
        </div>

        {{-- Tile 4: Total Unit Stok Fisik --}}
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Unit Fisik</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF]">{{ number_format($stats['total_stock_units'] ?? 0, 0, ',', '.') }}</span>
                <span class="text-[11px] text-black/40 dark:text-white/40">Seluruh Lokasi</span>
            </div>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- 4. LOCATIONS GRID (Apple Squircle Data Cards)         --}}
    {{-- ===================================================== --}}
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <h2 class="text-[17px] font-semibold text-black dark:text-white tracking-tight">Daftar Lokasi Fisik</h2>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 tabular-nums">
                    {{ $locations->count() }}
                </span>
            </div>
            <span class="text-[13px] text-black/45 dark:text-white/45">Multi-lokasi penyimpanan terintegrasi</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
            @forelse($locations as $loc)
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between space-y-4 hover:shadow-[0_4px_20px_rgba(0,0,0,0.04)] transition-all">
                {{-- Header Card --}}
                <div>
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            {{-- Type Glyph Container --}}
                            <div class="w-10 h-10 rounded-[10px] flex items-center justify-center shrink-0
                                @if($loc->type === 'warehouse')
                                    bg-[#007AFF]/10 text-[#007AFF]
                                @elseif($loc->type === 'central_kitchen')
                                    bg-[#FF9500]/10 text-[#FF9500]
                                @else
                                    bg-[#34C759]/10 text-[#34C759]
                                @endif
                            ">
                                @if($loc->type === 'warehouse')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                    </svg>
                                @elseif($loc->type === 'central_kitchen')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 1 3 2.48Z" />
                                    </svg>
                                @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                                    </svg>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-[16px] font-semibold text-black dark:text-white truncate">
                                    {{ $loc->name }}
                                </h3>
                                <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                    @if($loc->code)
                                        <span class="text-[11px] font-mono tabular-nums text-black/50 dark:text-white/50">
                                            {{ $loc->code }}
                                        </span>
                                        <span class="text-[10px] text-black/30 dark:text-white/30">·</span>
                                    @endif
                                    <span class="text-[11px] text-black/60 dark:text-white/60">
                                        @if($loc->type === 'warehouse')
                                            Gudang
                                        @elseif($loc->type === 'central_kitchen')
                                            Dapur Pusat
                                        @else
                                            Outlet
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Status Pill --}}
                        <div>
                            @if($loc->is_active)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-black/[0.06] dark:bg-white/[0.08] text-black/55 dark:text-white/55">
                                    <span class="w-1.5 h-1.5 rounded-full bg-black/40 dark:bg-white/40"></span> Nonaktif
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Contact & Address info --}}
                    <div class="mt-3.5 space-y-1.5 text-[12px] text-black/60 dark:text-white/60">
                        @if($loc->phone)
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-black/40 dark:text-white/40 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                            </svg>
                            <span class="font-mono tabular-nums">{{ $loc->phone }}</span>
                        </div>
                        @endif

                        @if($loc->address)
                        <div class="flex items-start gap-2">
                            <svg class="w-3.5 h-3.5 text-black/40 dark:text-white/40 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                            </svg>
                            <span class="line-clamp-1 text-black/50 dark:text-white/50">{{ $loc->address }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Stats Strip --}}
                <div class="pt-3 border-t border-black/5 dark:border-white/5">
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="rounded-[8px] bg-black/[0.02] dark:bg-white/[0.03] p-2">
                            <span class="text-[10px] text-black/40 dark:text-white/40 block font-medium">SKU</span>
                            <span class="text-[13px] font-semibold tabular-nums text-black dark:text-white">
                                {{ $loc->stocks_count ?? 0 }}
                            </span>
                        </div>
                        <div class="rounded-[8px] bg-black/[0.02] dark:bg-white/[0.03] p-2">
                            <span class="text-[10px] text-black/40 dark:text-white/40 block font-medium">Unit Stok</span>
                            <span class="text-[13px] font-semibold tabular-nums text-black dark:text-white">
                                {{ number_format($loc->total_stock_units ?? 0, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="rounded-[8px] bg-black/[0.02] dark:bg-white/[0.03] p-2">
                            <span class="text-[10px] text-black/40 dark:text-white/40 block font-medium">Nilai Aset</span>
                            <span class="text-[13px] font-semibold tabular-nums text-[#34C759] dark:text-[#30D158] truncate block" title="Rp {{ number_format($loc->total_valuation ?? 0, 0, ',', '.') }}">
                                {{ ($loc->total_valuation ?? 0) >= 1000000 ? number_format(($loc->total_valuation ?? 0) / 1000000, 1) . 'jt' : number_format(($loc->total_valuation ?? 0) / 1000, 0) . 'rb' }}
                            </span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-3.5 flex items-center justify-between gap-2">
                        <a href="{{ route('warehouse.show', $loc) }}"
                           class="h-8 flex-1 rounded-[8px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1">
                            <span>Kelola Stok</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </a>

                        @if(\App\Support\Context::hasPermission('inventory.manage'))
                        <div class="flex items-center gap-1 shrink-0">
                            <button type="button" @click="openEdit({{ json_encode($loc) }})"
                                    class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all flex items-center gap-1"
                                    title="Edit Lokasi">
                                <span>Edit</span>
                            </button>

                            <button type="button" @click="openDelete({{ $loc->id }}, '{{ addslashes($loc->name) }}')"
                                    class="h-8 w-8 rounded-[8px] text-[#FF3B30] hover:bg-[#FF3B30]/10 active:scale-[0.97] transition-all flex items-center justify-center"
                                    title="Hapus Lokasi">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>

                            <form id="form-delete-location-{{ $loc->id }}" action="{{ route('warehouse.destroy', $loc) }}" method="POST" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full py-12 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 text-center">
                <div class="w-14 h-14 rounded-full bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center mx-auto mb-3 text-black/30 dark:text-white/30">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                </div>
                <h3 class="text-[17px] font-semibold text-black dark:text-white">Belum Ada Gudang atau Lokasi</h3>
                <p class="text-[13px] text-black/50 dark:text-white/50 mt-1 max-w-sm mx-auto">
                    Daftarkan gudang utama, dapur produksi, atau cabang outlet untuk mulai mengelola pencatatan stok.
                </p>
                @if(\App\Support\Context::hasPermission('inventory.manage'))
                <button type="button" @click="showCreateModal = true"
                        class="mt-4 h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Tambah Gudang Pertama</span>
                </button>
                @endif
            </div>
            @endforelse
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- 5. INBOUND WORKFLOW GUIDE (Apple Horizontal Steps)    --}}
    {{-- ===================================================== --}}
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-[15px] font-semibold text-black dark:text-white">Alur Masuk Barang &amp; Penerimaan PO</h3>
                <p class="text-[12px] text-black/50 dark:text-white/50">Prosedur operasional standar penerimaan inventori fisik</p>
            </div>
            <span class="text-[11px] font-semibold text-[#007AFF] bg-[#007AFF]/10 px-2 py-0.5 rounded-full">SOP Gudang</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            {{-- Step 1 --}}
            <div class="rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] p-3 border border-black/[0.04] dark:border-white/[0.04]">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-5 h-5 rounded-full bg-[#007AFF] text-white text-[11px] font-bold flex items-center justify-center tabular-nums">1</span>
                    <span class="text-[13px] font-semibold text-black dark:text-white">Buat PO Supplier</span>
                </div>
                <p class="text-[12px] text-black/60 dark:text-white/60 leading-snug">
                    Terbitkan Purchase Order ke vendor melalui menu PO dengan item dan harga acuan.
                </p>
            </div>

            {{-- Step 2 --}}
            <div class="rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] p-3 border border-black/[0.04] dark:border-white/[0.04]">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-5 h-5 rounded-full bg-[#007AFF] text-white text-[11px] font-bold flex items-center justify-center tabular-nums">2</span>
                    <span class="text-[13px] font-semibold text-black dark:text-white">Fisik Tiba di Gudang</span>
                </div>
                <p class="text-[12px] text-black/60 dark:text-white/60 leading-snug">
                    Vendor mengirim barang. Petugas gudang memeriksa surat jalan dan kondisi packaging.
                </p>
            </div>

            {{-- Step 3 --}}
            <div class="rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] p-3 border border-black/[0.04] dark:border-white/[0.04]">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-5 h-5 rounded-full bg-[#007AFF] text-white text-[11px] font-bold flex items-center justify-center tabular-nums">3</span>
                    <span class="text-[13px] font-semibold text-black dark:text-white">Terima &amp; Rekam PO</span>
                </div>
                <p class="text-[12px] text-black/60 dark:text-white/60 leading-snug">
                    Buka dokumen PO, klik <em>Terima Barang</em>, dan sistem otomatis menambahkan stok fisik.
                </p>
            </div>

            {{-- Step 4 --}}
            <div class="rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] p-3 border border-black/[0.04] dark:border-white/[0.04]">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-5 h-5 rounded-full bg-[#34C759] text-white text-[11px] font-bold flex items-center justify-center tabular-nums">✓</span>
                    <span class="text-[13px] font-semibold text-black dark:text-white">Stok Terdistribusi</span>
                </div>
                <p class="text-[12px] text-black/60 dark:text-white/60 leading-snug">
                    Stok terupdate real-time dan siap dipakai di POS kasir atau ditransfer antar cabang.
                </p>
            </div>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- 6. RECENT MOVEMENTS AUDIT TABLE                       --}}
    {{-- ===================================================== --}}
    @if(isset($recentMovements) && $recentMovements->isNotEmpty())
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="px-4 py-3.5 border-b border-black/5 dark:border-white/10 flex items-center justify-between gap-3">
            <div>
                <h3 class="text-[15px] font-semibold text-black dark:text-white">Audit Trail Mutasi Terkini</h3>
                <p class="text-[12px] text-black/50 dark:text-white/50">Log mutasi real-time dari transaksi POS, PO, dan penyesuaian stok</p>
            </div>
            <a href="{{ route('inventory.movements') }}"
               class="text-[13px] font-medium text-[#007AFF] hover:underline flex items-center gap-1">
                <span>Lihat Semua Mutasi</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">
                        <th class="px-4 py-2.5">Produk</th>
                        <th class="px-4 py-2.5">Gudang / Lokasi</th>
                        <th class="px-4 py-2.5">Tipe Mutasi</th>
                        <th class="px-4 py-2.5 text-right">Perubahan Qty</th>
                        <th class="px-4 py-2.5 text-right">Saldo Akhir</th>
                        <th class="px-4 py-2.5 text-right">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @foreach($recentMovements as $mv)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3">
                            <div class="font-medium text-black dark:text-white">{{ $mv->product?->name ?? '-' }}</div>
                            @if($mv->product?->code)
                                <div class="text-[11px] font-mono tabular-nums text-black/45 dark:text-white/45">{{ $mv->product->code }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-black/70 dark:text-white/70 font-medium">{{ $mv->location?->name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $mvLabels = [
                                    'goods_receipt'  => ['label' => 'Penerimaan PO', 'pill' => 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]'],
                                    'pos_sale'       => ['label' => 'Penjualan POS', 'pill' => 'bg-[#007AFF]/12 text-[#007AFF]'],
                                    'adjustment'     => ['label' => 'Penyesuaian', 'pill' => 'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]'],
                                    'transfer_in'    => ['label' => 'Transfer Masuk', 'pill' => 'bg-[#5856D6]/12 text-[#413FA6] dark:text-[#5E5CE6]'],
                                    'transfer_out'   => ['label' => 'Transfer Keluar', 'pill' => 'bg-black/[0.06] dark:bg-white/[0.08] text-black/60 dark:text-white/60'],
                                    'opname'         => ['label' => 'Opname Fisik', 'pill' => 'bg-[#AF52DE]/12 text-[#7C3AA6] dark:text-[#BF5AF2]'],
                                ];
                                $mvInfo = $mvLabels[$mv->movement_type] ?? ['label' => ucfirst(str_replace('_', ' ', $mv->movement_type)), 'pill' => 'bg-black/[0.06] dark:bg-white/[0.08] text-black/60 dark:text-white/60'];
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $mvInfo['pill'] }}">
                                {{ $mvInfo['label'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums font-semibold {{ $mv->quantity_change >= 0 ? 'text-[#34C759] dark:text-[#30D158]' : 'text-[#FF3B30] dark:text-[#FF453A]' }}">
                            {{ $mv->quantity_change >= 0 ? '+' : '' }}{{ number_format($mv->quantity_change, 2) }}
                            <span class="text-[11px] text-black/40 dark:text-white/40 font-normal ml-0.5">{{ $mv->product?->outputUnit?->symbol }}</span>
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums font-semibold text-black dark:text-white">
                            {{ number_format($mv->balance_after, 2) }}
                        </td>
                        <td class="px-4 py-3 text-right text-[12px] text-black/45 dark:text-white/45 whitespace-nowrap">
                            {{ $mv->created_at->diffForHumans() }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ===================================================== --}}
    {{-- 7. APPLE SHEET: TAMBAH GUDANG                         --}}
    {{-- ===================================================== --}}
    <div x-show="showCreateModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px] p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="w-full max-w-lg rounded-[18px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.25)] p-6 space-y-5 max-h-[90vh] overflow-y-auto"
             @click.outside="showCreateModal = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3.5">
                <div>
                    <h3 class="text-[17px] font-semibold text-black dark:text-white">Tambah Lokasi / Gudang Baru</h3>
                    <p class="text-[13px] text-black/50 dark:text-white/50">Konfigurasikan fisik penyimpanan atau cabang bisnis</p>
                </div>
                <button type="button" @click="showCreateModal = false" class="p-1 rounded-[6px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('warehouse.store') }}" method="POST" class="space-y-4 text-[13px]">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                            Nama Gudang / Outlet <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="text" name="name" required placeholder="Contoh: Gudang Utama, Kitchen Jakarta, Outlet Senopati..."
                               class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                            Tipe Lokasi <span class="text-[#FF3B30]">*</span>
                        </label>
                        <select name="type" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <option value="warehouse">🏢 Gudang (Warehouse)</option>
                            <option value="outlet">🏪 Outlet / Toko Retail</option>
                            <option value="central_kitchen">🍳 Dapur Pusat (Central Kitchen)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                            Kode Lokasi
                        </label>
                        <input type="text" name="code" placeholder="Misal: WH-01, OTL-01..."
                               class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white font-mono placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                            Nomor Telepon
                        </label>
                        <input type="text" name="phone" placeholder="08xxxxxxxxxx / +62..."
                               class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                            Status Awal
                        </label>
                        <div class="h-10 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] text-[13px] text-black/70 dark:text-white/70 font-medium flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                            <span>Langsung Aktif</span>
                        </div>
                    </div>
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                            Alamat Lengkap
                        </label>
                        <textarea name="address" rows="2" placeholder="Alamat fisik, nomor jalan, kecamatan, kota..."
                                  class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] p-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition resize-none"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3.5 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showCreateModal = false"
                            class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                        Simpan Gudang
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- 8. APPLE SHEET: EDIT GUDANG                           --}}
    {{-- ===================================================== --}}
    <div x-show="showEditModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px] p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="w-full max-w-lg rounded-[18px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.25)] p-6 space-y-5 max-h-[90vh] overflow-y-auto"
             @click.outside="showEditModal = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3.5">
                <div>
                    <h3 class="text-[17px] font-semibold text-black dark:text-white">Edit Informasi Gudang</h3>
                    <p class="text-[13px] text-black/50 dark:text-white/50" x-text="'Memperbarui: ' + editData.name"></p>
                </div>
                <button type="button" @click="showEditModal = false" class="p-1 rounded-[6px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form :action="'{{ url('/warehouse') }}/' + editData.id" method="POST" class="space-y-4 text-[13px]">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                            Nama Gudang / Lokasi <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="text" name="name" :value="editData.name" required
                               class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                            Tipe Lokasi
                        </label>
                        <select name="type" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <option value="warehouse" :selected="editData.type === 'warehouse'">🏢 Gudang (Warehouse)</option>
                            <option value="outlet" :selected="editData.type === 'outlet'">🏪 Outlet / Toko</option>
                            <option value="central_kitchen" :selected="editData.type === 'central_kitchen'">🍳 Dapur Pusat</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                            Kode Lokasi
                        </label>
                        <input type="text" name="code" :value="editData.code"
                               class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                            Nomor Telepon
                        </label>
                        <input type="text" name="phone" :value="editData.phone"
                               class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                            Alamat Lengkap
                        </label>
                        <textarea name="address" rows="2" :value="editData.address"
                                  class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] p-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition resize-none"></textarea>
                    </div>
                    <div class="col-span-1 sm:col-span-2 flex items-center gap-3 p-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06]">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1"
                               :checked="editData.is_active" class="w-4 h-4 rounded text-[#007AFF] focus:ring-[#007AFF]">
                        <label for="edit_is_active" class="text-[13px] text-black/80 dark:text-white/80 font-medium cursor-pointer">
                            Gudang beroperasi aktif (dapat menerima PO, transfer stok, dan alokasi produk)
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3.5 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showEditModal = false"
                            class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- 9. APPLE ALERT DIALOG (Centered Confirmation)         --}}
    {{-- ===================================================== --}}
    <div x-show="deleteModalOpen"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px] p-4"
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
                <p class="text-[17px] font-semibold text-black dark:text-white">Hapus Lokasi?</p>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                    <span x-text="deleteTarget.name" class="font-medium text-black dark:text-white"></span> akan dihapus dari sistem. Pastikan tidak ada stok atau mutasi aktif.
                </p>
            </div>

            <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                <button type="button" @click="closeDelete()"
                        class="py-3 text-[#007AFF] border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Batal
                </button>
                <button type="button" @click="submitDelete()"
                        class="py-3 text-[#FF3B30] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Hapus
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
