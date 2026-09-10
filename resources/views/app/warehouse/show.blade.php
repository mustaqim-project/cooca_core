@extends('layouts.app', [
    'title' => 'Detail Gudang — ' . $location->name,
    'headerTitle' => 'Detail Gudang: ' . $location->name,
    'headerSubtitle' => 'Pantau stok aktual, riwayat penerimaan barang dari PO, dan mutasi kartu stok lokasi ini.'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
    showEditModal: false,
    showAdjustModal: false,
    searchStock: '',
    selectedStock: null,
    newQuantity: 0,
    unitCost: 0,
    openAdjust(stock) {
        this.selectedStock = stock;
        this.newQuantity = Number(stock.quantity);
        this.unitCost = Number(stock.last_cost || 0);
        this.showAdjustModal = true;
    }
}">

    {{-- ===================================================== --}}
    {{-- 1. SUB-NAVIGATION TABS (Apple Segmented Control)      --}}
    {{-- ===================================================== --}}
    <div class="overflow-x-auto pb-1 scrollbar-none">
        <div class="inline-flex p-1 rounded-[11px] bg-black/[0.05] dark:bg-white/[0.07] border border-black/5 dark:border-white/10 text-[13px] font-medium whitespace-nowrap">
            <a href="{{ route('warehouse.index') }}"
               class="px-3.5 py-1.5 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
                <span>Daftar Gudang &amp; Lokasi</span>
            </a>
            <a href="{{ route('inventory.stocks') }}?location_id={{ $location->id }}"
               class="px-3.5 py-1.5 rounded-[9px] bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-semibold flex items-center gap-1.5 transition-all">
                <svg class="w-4 h-4 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                </svg>
                <span>Stok di Lokasi Ini</span>
                <span class="px-1.5 py-0.2 rounded-full text-[11px] tabular-nums font-semibold bg-[#007AFF]/12 text-[#007AFF]">{{ $stocks->total() }}</span>
            </a>
            <a href="{{ route('inventory.transfers.index') }}"
               class="px-3.5 py-1.5 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                </svg>
                <span>Transfer Stok</span>
            </a>
            <a href="{{ route('inventory.opnames.index') }}"
               class="px-3.5 py-1.5 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                </svg>
                <span>Stock Opname</span>
            </a>
            <a href="{{ route('inventory.movements') }}?location_id={{ $location->id }}"
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
    {{-- 2. TOOLBAR / COCKPIT HEADER (macOS Sonoma Style)      --}}
    {{-- ===================================================== --}}
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-start sm:items-center gap-3.5">
            {{-- Location Icon Tile --}}
            <div class="w-11 h-11 rounded-[12px] flex items-center justify-center shrink-0
                @if($location->type === 'warehouse')
                    bg-[#007AFF]/10 text-[#007AFF]
                @elseif($location->type === 'central_kitchen')
                    bg-[#FF9500]/10 text-[#FF9500]
                @else
                    bg-[#34C759]/10 text-[#34C759]
                @endif
            ">
                @if($location->type === 'warehouse')
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                @elseif($location->type === 'central_kitchen')
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 1 3 2.48Z" />
                    </svg>
                @else
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                    </svg>
                @endif
            </div>

            <div>
                {{-- Breadcrumb minimal --}}
                <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-0.5">
                    <a href="{{ route('warehouse.index') }}" class="hover:text-[#007AFF] transition-colors">Gudang &amp; Lokasi</a>
                    <span>›</span>
                    <span class="text-black dark:text-white font-medium">{{ $location->name }}</span>
                </nav>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">
                        {{ $location->name }}
                    </h1>

                    {{-- Badges --}}
                    @if($location->type === 'warehouse')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/12 text-[#007AFF]">
                            Gudang
                        </span>
                    @elseif($location->type === 'central_kitchen')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                            Dapur Pusat
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                            Outlet
                        </span>
                    @endif

                    @if($location->code)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-mono tabular-nums font-semibold bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60">
                            {{ $location->code }}
                        </span>
                    @endif

                    @if($location->is_primary)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#5856D6]/12 text-[#413FA6] dark:text-[#5E5CE6]">
                            Lokasi Utama
                        </span>
                    @endif

                    @if($location->is_active)
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
        </div>

        {{-- Top Right Actions --}}
        <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
            <a href="{{ route('warehouse.index') }}"
               class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-black/50 dark:text-white/50" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                <span>Kembali</span>
            </a>

            @if(\App\Support\Context::hasPermission('inventory.manage'))
            <button type="button" @click="showEditModal = true"
                    class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                </svg>
                <span>Edit Gudang</span>
            </button>
            @endif

            @if(\App\Support\Context::hasPermission('receiving.manage'))
            <a href="{{ route('purchase-orders.index') }}?po_type=supplier"
               class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Terima Barang dari PO</span>
            </a>
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
    {{-- 3. METADATA INSPECTOR STRIP (Flat Neutral Surface)    --}}
    {{-- ===================================================== --}}
    <div class="p-4 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-[13px]">
            <div class="flex items-start gap-2.5">
                <div class="w-7 h-7 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center shrink-0 text-black/50 dark:text-white/50">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    </svg>
                </div>
                <div>
                    <span class="text-[11px] text-black/40 dark:text-white/40 uppercase font-semibold tracking-wide block">Alamat Fisik</span>
                    <span class="text-black/80 dark:text-white/80 font-medium leading-relaxed">
                        {{ $location->address ?: 'Belum ada alamat terdaftar' }}
                    </span>
                </div>
            </div>

            <div class="flex items-start gap-2.5">
                <div class="w-7 h-7 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center shrink-0 text-black/50 dark:text-white/50">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                    </svg>
                </div>
                <div>
                    <span class="text-[11px] text-black/40 dark:text-white/40 uppercase font-semibold tracking-wide block">Kontak / Telepon</span>
                    <span class="text-black/80 dark:text-white/80 font-mono tabular-nums font-medium">
                        {{ $location->phone ?: '—' }}
                    </span>
                </div>
            </div>

            <div class="flex items-start gap-2.5">
                <div class="w-7 h-7 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center shrink-0 text-black/50 dark:text-white/50">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5" />
                    </svg>
                </div>
                <div>
                    <span class="text-[11px] text-black/40 dark:text-white/40 uppercase font-semibold tracking-wide block">Terdaftar Sejak</span>
                    <span class="text-black/80 dark:text-white/80 font-medium">
                        {{ $location->created_at->format('d M Y') }} ({{ $location->created_at->diffForHumans() }})
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- 4. COMMAND KPI METRICS (Apple Flat Neutral Cards)     --}}
    {{-- ===================================================== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        {{-- KPI 1: Total Produk --}}
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Produk</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-black dark:text-white">{{ $stocks->total() }}</span>
                <span class="text-[11px] text-black/40 dark:text-white/40">Item Fisik</span>
            </div>
        </div>

        {{-- KPI 2: Total Nilai Aset --}}
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Nilai Aset Stok</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] sm:text-[24px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] truncate">
                    Rp {{ number_format($totalValuation, 0, ',', '.') }}
                </span>
                <span class="text-[11px] font-medium text-[#34C759] dark:text-[#30D158]">Valuasi HPP</span>
            </div>
        </div>

        {{-- KPI 3: Stok Minimum --}}
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Stok Menipis</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums {{ $lowStockCount > 0 ? 'text-[#FF9500] dark:text-[#FF9F0A]' : 'text-black dark:text-white' }}">
                    {{ $lowStockCount }}
                </span>
                <span class="text-[11px] {{ $lowStockCount > 0 ? 'text-[#FF9500] dark:text-[#FF9F0A] font-medium' : 'text-black/40 dark:text-white/40' }}">
                    {{ $lowStockCount > 0 ? 'Perlu Restock' : 'Batas Aman' }}
                </span>
            </div>
        </div>

        {{-- KPI 4: Penerimaan Barang PO --}}
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Penerimaan PO</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-[#5856D6] dark:text-[#5E5CE6]">{{ $receipts->count() }}</span>
                <span class="text-[11px] text-black/40 dark:text-white/40">Dokumen GR</span>
            </div>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- 5. QUICK ACTION SHORTCUT CARDS                        --}}
    {{-- ===================================================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        <a href="{{ route('purchase-orders.index') }}?po_type=supplier"
           class="p-4 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 hover:shadow-[0_4px_20px_rgba(0,0,0,0.04)] active:scale-[0.98] transition-all flex items-center gap-3.5 group">
            <div class="w-10 h-10 rounded-[10px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                </svg>
            </div>
            <div>
                <h4 class="font-semibold text-[14px] text-black dark:text-white group-hover:text-[#007AFF] transition-colors">
                    Terima dari PO Supplier
                </h4>
                <p class="text-[12px] text-black/50 dark:text-white/50">Buka PO confirmed → klik Terima Barang</p>
            </div>
        </a>

        <a href="{{ route('inventory.transfers.index') }}"
           class="p-4 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 hover:shadow-[0_4px_20px_rgba(0,0,0,0.04)] active:scale-[0.98] transition-all flex items-center gap-3.5 group">
            <div class="w-10 h-10 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                </svg>
            </div>
            <div>
                <h4 class="font-semibold text-[14px] text-black dark:text-white group-hover:text-[#007AFF] transition-colors">
                    Transfer Stok Antar Lokasi
                </h4>
                <p class="text-[12px] text-black/50 dark:text-white/50">Kirim stok ke cabang / outlet lain</p>
            </div>
        </a>

        <a href="{{ route('inventory.opnames.index') }}"
           class="p-4 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 hover:shadow-[0_4px_20px_rgba(0,0,0,0.04)] active:scale-[0.98] transition-all flex items-center gap-3.5 group">
            <div class="w-10 h-10 rounded-[10px] bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                </svg>
            </div>
            <div>
                <h4 class="font-semibold text-[14px] text-black dark:text-white group-hover:text-[#007AFF] transition-colors">
                    Stock Opname Fisik
                </h4>
                <p class="text-[12px] text-black/50 dark:text-white/50">Rekonsiliasi selisih stok buku vs riil</p>
            </div>
        </a>
    </div>

    {{-- ===================================================== --}}
    {{-- 6. STOK PRODUK DI GUDANG INI (Apple Dense Table)     --}}
    {{-- ===================================================== --}}
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="p-4 border-b border-black/5 dark:border-white/10 flex items-center justify-between flex-wrap gap-3">
            <div>
                <h3 class="text-[15px] font-semibold text-black dark:text-white">Daftar Stok Produk di Lokasi Ini</h3>
                <p class="text-[12px] text-black/50 dark:text-white/50">Kuantitas aktual fisik dan estimasi nilai persediaan</p>
            </div>

            <div class="flex items-center gap-2.5">
                {{-- Search Field --}}
                <div class="relative w-48 sm:w-64">
                    <svg class="w-3.5 h-3.5 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <input type="text" x-model="searchStock" placeholder="Cari nama atau kode..."
                           class="w-full h-8.5 pl-8.5 pr-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] text-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <a href="{{ route('inventory.stocks') }}?location_id={{ $location->id }}"
                   class="text-[12px] text-[#007AFF] hover:underline font-semibold inline-flex items-center gap-1">
                    <span>Semua Stok</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">
                        <th class="px-4 py-2.5">Produk &amp; SKU</th>
                        <th class="px-4 py-2.5">Kategori</th>
                        <th class="px-4 py-2.5 text-right">Stok Aktual</th>
                        <th class="px-4 py-2.5 text-right">Min. Stok</th>
                        <th class="px-4 py-2.5 text-right">HPP / Unit</th>
                        <th class="px-4 py-2.5 text-right">Total Nilai</th>
                        <th class="px-4 py-2.5 text-center">Status</th>
                        @if(\App\Support\Context::hasPermission('inventory.manage'))
                        <th class="px-4 py-2.5 text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($stocks as $stock)
                    @php
                        $isLow = $stock->product && $stock->product->min_stock > 0 && $stock->quantity <= $stock->product->min_stock;
                        $valuation = (float)$stock->quantity * (float)$stock->last_cost;
                    @endphp
                    <tr x-show="!searchStock || '{{ strtolower($stock->product?->name . ' ' . $stock->product?->code . ' ' . ($stock->product?->category?->name ?? '')) }}'.includes(searchStock.toLowerCase())"
                        class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors {{ $isLow ? 'bg-[#FF9500]/5' : '' }}">
                        <td class="px-4 py-3">
                            <div class="font-medium text-black dark:text-white">{{ $stock->product?->name ?? '—' }}</div>
                            @if($stock->product?->code)
                                <div class="text-[11px] font-mono tabular-nums text-black/45 dark:text-white/45">{{ $stock->product->code }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-black/60 dark:text-white/60">
                            {{ $stock->product?->category?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <span class="font-semibold tabular-nums text-[14px] {{ $isLow ? 'text-[#FF9500] dark:text-[#FF9F0A]' : 'text-black dark:text-white' }}">
                                {{ number_format($stock->quantity, 2) }}
                            </span>
                            <span class="text-[11px] text-black/40 dark:text-white/40 ml-0.5">{{ $stock->product?->outputUnit?->symbol ?? '' }}</span>
                        </td>
                        <td class="px-4 py-3 text-right font-mono tabular-nums text-black/50 dark:text-white/50">
                            {{ $stock->product ? number_format($stock->product->min_stock, 2) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono tabular-nums text-black/70 dark:text-white/70">
                            Rp {{ number_format($stock->last_cost, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono tabular-nums font-semibold text-[#34C759] dark:text-[#30D158]">
                            Rp {{ number_format($valuation, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($isLow)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span> Hampir Habis
                                </span>
                            @elseif($stock->quantity <= 0)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span> Habis
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Tersedia
                                </span>
                            @endif
                        </td>
                        @if(\App\Support\Context::hasPermission('inventory.manage'))
                        <td class="px-4 py-3 text-right">
                            <button type="button"
                                @click="openAdjust({
                                    id: '{{ $stock->id }}',
                                    product_id: '{{ $stock->product_id }}',
                                    location_id: '{{ $stock->location_id }}',
                                    product_name: '{{ addslashes($stock->product?->name ?? '') }}',
                                    quantity: '{{ $stock->quantity }}',
                                    last_cost: '{{ $stock->last_cost }}'
                                })"
                                class="h-7 px-2.5 rounded-[6px] text-[12px] font-medium text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] transition-all">
                                Sesuaikan
                            </button>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-black/50 dark:text-white/50">
                            <div class="w-12 h-12 rounded-full bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center mx-auto mb-2 text-black/30 dark:text-white/30">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                </svg>
                            </div>
                            <div class="font-semibold text-black dark:text-white text-[14px]">Belum ada stok fisik di gudang ini</div>
                            <div class="text-[12px] text-black/45 dark:text-white/45 mt-1 max-w-sm mx-auto">
                                Lakukan penerimaan barang dari Purchase Order Supplier atau transfer stok dari cabang lain.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($stocks->hasPages())
        <div class="p-3.5 border-t border-black/5 dark:border-white/10">{{ $stocks->links() }}</div>
        @endif
    </div>

    {{-- ===================================================== --}}
    {{-- 7. RIWAYAT PENERIMAAN BARANG (GOODS RECEIPTS)         --}}
    {{-- ===================================================== --}}
    @if($receipts->isNotEmpty())
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="px-4 py-3.5 border-b border-black/5 dark:border-white/10">
            <h3 class="text-[15px] font-semibold text-black dark:text-white">Riwayat Penerimaan Barang (Goods Receipt)</h3>
            <p class="text-[12px] text-black/50 dark:text-white/50">Daftar inbound barang yang telah diverifikasi masuk ke lokasi ini</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">
                        <th class="px-4 py-2.5">No. Penerimaan</th>
                        <th class="px-4 py-2.5">Tanggal</th>
                        <th class="px-4 py-2.5">Supplier</th>
                        <th class="px-4 py-2.5">No. PO</th>
                        <th class="px-4 py-2.5 text-center">Item</th>
                        <th class="px-4 py-2.5">Diterima Oleh</th>
                        <th class="px-4 py-2.5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @foreach($receipts as $gr)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3 font-mono tabular-nums font-semibold text-black dark:text-white">
                            #{{ $gr->receipt_number }}
                        </td>
                        <td class="px-4 py-3 text-black/60 dark:text-white/60">
                            {{ $gr->receipt_date?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 font-medium text-black dark:text-white">
                            {{ $gr->supplier?->name ?? 'Tanpa Supplier' }}
                        </td>
                        <td class="px-4 py-3">
                            @if($gr->purchaseOrder)
                                <a href="{{ route('purchase-orders.show', $gr->purchaseOrder->id) }}"
                                   class="font-mono tabular-nums text-[#007AFF] hover:underline font-semibold">
                                    {{ $gr->purchaseOrder->po_number }}
                                </a>
                            @else
                                <span class="text-black/40 dark:text-white/40 font-medium">1-Klik (Solo Mode)</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center font-mono tabular-nums font-semibold text-black dark:text-white">
                            {{ $gr->items->count() }}
                        </td>
                        <td class="px-4 py-3 text-black/60 dark:text-white/60">
                            {{ $gr->receiver?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Diterima
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ===================================================== --}}
    {{-- 8. KARTU STOK — MUTASI TERKINI                       --}}
    {{-- ===================================================== --}}
    @if($recentMovements->isNotEmpty())
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="px-4 py-3.5 border-b border-black/5 dark:border-white/10 flex items-center justify-between gap-3">
            <div>
                <h3 class="text-[15px] font-semibold text-black dark:text-white">Kartu Stok — Mutasi Terkini</h3>
                <p class="text-[12px] text-black/50 dark:text-white/50">Audit trail pergerakan saldo barang di lokasi ini</p>
            </div>
            <a href="{{ route('inventory.movements') }}?location_id={{ $location->id }}"
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
                        <th class="px-4 py-2.5">Tipe Mutasi</th>
                        <th class="px-4 py-2.5">No. Referensi</th>
                        <th class="px-4 py-2.5 text-right">Perubahan Qty</th>
                        <th class="px-4 py-2.5 text-right">Saldo Akhir</th>
                        <th class="px-4 py-2.5">Operator</th>
                        <th class="px-4 py-2.5 text-right">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @foreach($recentMovements as $mv)
                    @php
                        $mvLabels = [
                            'goods_receipt'  => ['label' => 'Penerimaan PO', 'pill' => 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]'],
                            'pos_sale'       => ['label' => 'Penjualan POS', 'pill' => 'bg-[#007AFF]/12 text-[#007AFF]'],
                            'adjustment'     => ['label' => 'Penyesuaian Manual', 'pill' => 'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]'],
                            'transfer_in'    => ['label' => 'Transfer Masuk', 'pill' => 'bg-[#5856D6]/12 text-[#413FA6] dark:text-[#5E5CE6]'],
                            'transfer_out'   => ['label' => 'Transfer Keluar', 'pill' => 'bg-black/[0.06] dark:bg-white/[0.08] text-black/60 dark:text-white/60'],
                            'opname'         => ['label' => 'Rekonsiliasi Opname', 'pill' => 'bg-[#AF52DE]/12 text-[#7C3AA6] dark:text-[#BF5AF2]'],
                            'initial'        => ['label' => 'Stok Awal', 'pill' => 'bg-[#007AFF]/12 text-[#007AFF]'],
                            'pos_refund'     => ['label' => 'Refund POS', 'pill' => 'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]'],
                        ];
                        $mvInfo = $mvLabels[$mv->movement_type] ?? ['label' => ucfirst(str_replace('_', ' ', $mv->movement_type)), 'pill' => 'bg-black/[0.06] dark:bg-white/[0.08] text-black/60 dark:text-white/60'];
                    @endphp
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3">
                            <div class="font-medium text-black dark:text-white">{{ $mv->product?->name ?? '—' }}</div>
                            @if($mv->product?->code)
                                <div class="text-[11px] font-mono tabular-nums text-black/45 dark:text-white/45">{{ $mv->product->code }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $mvInfo['pill'] }}">
                                {{ $mvInfo['label'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-mono tabular-nums text-black/50 dark:text-white/50 text-[12px]">
                            {{ $mv->reference_number ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono tabular-nums font-semibold {{ $mv->quantity_change >= 0 ? 'text-[#34C759] dark:text-[#30D158]' : 'text-[#FF3B30] dark:text-[#FF453A]' }}">
                            {{ $mv->quantity_change >= 0 ? '+' : '' }}{{ number_format($mv->quantity_change, 2) }}
                            <span class="text-[11px] text-black/40 dark:text-white/40 font-normal ml-0.5">{{ $mv->product?->outputUnit?->symbol }}</span>
                        </td>
                        <td class="px-4 py-3 text-right font-mono tabular-nums font-semibold text-black dark:text-white">
                            {{ number_format($mv->balance_after, 2) }}
                        </td>
                        <td class="px-4 py-3 text-black/60 dark:text-white/60">{{ $mv->creator?->name ?? 'Sistem' }}</td>
                        <td class="px-4 py-3 text-right text-[12px] text-black/45 dark:text-white/45 whitespace-nowrap">{{ $mv->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ===================================================== --}}
    {{-- 9. APPLE SHEET: EDIT GUDANG                           --}}
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
                    <h3 class="text-[17px] font-semibold text-black dark:text-white">Edit Gudang: {{ $location->name }}</h3>
                    <p class="text-[13px] text-black/50 dark:text-white/50">Perbarui informasi dan status operasional lokasi</p>
                </div>
                <button type="button" @click="showEditModal = false" class="p-1 rounded-[6px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('warehouse.update', $location->id) }}" method="POST" class="space-y-4 text-[13px]">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                            Nama Gudang / Lokasi <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="text" name="name" value="{{ $location->name }}" required
                               class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                            Tipe Lokasi
                        </label>
                        <select name="type" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <option value="warehouse" {{ $location->type === 'warehouse' ? 'selected' : '' }}>🏢 Gudang (Warehouse)</option>
                            <option value="outlet" {{ $location->type === 'outlet' ? 'selected' : '' }}>🏪 Outlet / Toko</option>
                            <option value="central_kitchen" {{ $location->type === 'central_kitchen' ? 'selected' : '' }}>🍳 Dapur Pusat</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                            Kode Lokasi
                        </label>
                        <input type="text" name="code" value="{{ $location->code }}"
                               class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                            Nomor Telepon
                        </label>
                        <input type="text" name="phone" value="{{ $location->phone }}"
                               class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                            Status Operasional
                        </label>
                        <select name="is_active" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <option value="1" {{ $location->is_active ? 'selected' : '' }}>✅ Aktif Beroperasi</option>
                            <option value="0" {{ !$location->is_active ? 'selected' : '' }}>❌ Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                            Alamat Lengkap
                        </label>
                        <textarea name="address" rows="2"
                                  class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] p-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition resize-none">{{ $location->address }}</textarea>
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
    {{-- 10. APPLE SHEET: PENYESUAIAN STOK (ADJUSTMENT)        --}}
    {{-- ===================================================== --}}
    @if(\App\Support\Context::hasPermission('inventory.manage'))
    <div x-show="showAdjustModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px] p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="w-full max-w-md rounded-[18px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.25)] p-6 space-y-4"
             @click.outside="showAdjustModal = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                <div>
                    <h3 class="text-[17px] font-semibold text-black dark:text-white">Sesuaikan Stok Fisik</h3>
                    <p class="text-[13px] text-black/50 dark:text-white/50" x-text="selectedStock ? selectedStock.product_name : ''"></p>
                </div>
                <button type="button" @click="showAdjustModal = false" class="p-1 rounded-[6px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('inventory.stocks.adjust') }}" method="POST" class="space-y-3.5 text-[13px]">
                @csrf
                <input type="hidden" name="product_id" x-bind:value="selectedStock?.product_id">
                <input type="hidden" name="location_id" value="{{ $location->id }}">

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                        Stok Tercatat Saat Ini (Sistem)
                    </label>
                    <div class="h-10 px-3.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] text-black dark:text-white font-mono tabular-nums font-semibold text-[14px] flex items-center justify-between">
                        <span x-text="selectedStock?.quantity ?? 0"></span>
                        <span class="text-[11px] text-black/40 dark:text-white/40 font-sans">Unit</span>
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                        Kuantitas Baru Riil (Hasil Fisik) <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="number" name="new_quantity" step="any" x-model="newQuantity" min="0" required
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white font-mono tabular-nums text-[14px] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                        HPP / Biaya Satuan Terakhir (Opsional)
                    </label>
                    <input type="number" name="unit_cost" step="any" x-model="unitCost" min="0"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white font-mono tabular-nums text-[14px] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                        Keterangan Penyesuaian
                    </label>
                    <input type="text" name="notes" placeholder="Contoh: Selisih fisik stock opname, barang rusak..."
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white text-[13px] placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showAdjustModal = false"
                            class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                        Simpan Penyesuaian
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection
