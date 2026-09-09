@extends('layouts.app', [
    'title' => 'Manajemen Gudang & Lokasi',
    'headerTitle' => 'Gudang & Lokasi Bisnis',
    'headerSubtitle' => 'Kelola multi-gudang, outlet, dan dapur pusat serta pantau pergerakan stok antar lokasi.'
])

@section('content')
<div class="space-y-6" x-data="{
    showCreateModal: false,
    showEditModal: false,
    searchQuery: '',
    typeFilter: 'all',
    editData: {
        id: '',
        name: '',
        type: 'warehouse',
        code: '',
        phone: '',
        address: '',
        is_active: true
    },
    openEdit(loc) {
        this.editData = { ...loc };
        this.showEditModal = true;
    }
}">

    {{-- ===== SUB-NAVIGATION TABS ===== --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-slate-200/80 dark:border-slate-800/80 scrollbar-none">
        <a href="{{ route('warehouse.index') }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20 shadow-xs">
            <i data-lucide="warehouse" class="w-4 h-4 text-cyan-500"></i>
            <span>Daftar Gudang & Lokasi</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] font-mono font-bold bg-cyan-500/20 text-cyan-700 dark:text-cyan-300">{{ $locations->count() }}</span>
        </a>
        <a href="{{ route('inventory.stocks') }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all whitespace-nowrap text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60">
            <i data-lucide="layers" class="w-4 h-4 text-slate-400"></i>
            <span>Stok Real-Time</span>
        </a>
        <a href="{{ route('inventory.transfers.index') }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all whitespace-nowrap text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60">
            <i data-lucide="arrow-left-right" class="w-4 h-4 text-slate-400"></i>
            <span>Transfer Stok</span>
        </a>
        <a href="{{ route('inventory.opnames.index') }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all whitespace-nowrap text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60">
            <i data-lucide="clipboard-check" class="w-4 h-4 text-slate-400"></i>
            <span>Stock Opname</span>
        </a>
        <a href="{{ route('inventory.movements') }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all whitespace-nowrap text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60">
            <i data-lucide="activity" class="w-4 h-4 text-slate-400"></i>
            <span>Riwayat Mutasi</span>
        </a>
        <a href="{{ route('purchase-orders.index') }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all whitespace-nowrap text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60">
            <i data-lucide="truck" class="w-4 h-4 text-slate-400"></i>
            <span>Purchase Order</span>
        </a>
    </div>

    {{-- ===== TOP ACTION TOOLBAR ===== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wider bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20">
                    Multi-Location Cockpit
                </span>
                <span class="text-xs text-slate-400">·</span>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Semua entitas fisik penyimpanan produk</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">
                Gudang & Lokasi Penyimpanan
            </h1>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('inventory.stocks') }}"
               class="px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-200/90 dark:border-slate-800 shadow-2xs transition inline-flex items-center gap-1.5">
                <i data-lucide="layers" class="w-4 h-4 text-cyan-500"></i>
                <span>Semua Stok</span>
            </a>
            <a href="{{ route('inventory.transfers.index') }}"
               class="px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-200/90 dark:border-slate-800 shadow-2xs transition inline-flex items-center gap-1.5">
                <i data-lucide="arrow-left-right" class="w-4 h-4 text-indigo-500"></i>
                <span>Transfer Stok</span>
            </a>
            <a href="{{ route('purchase-orders.index') }}"
               class="px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-200/90 dark:border-slate-800 shadow-2xs transition inline-flex items-center gap-1.5">
                <i data-lucide="truck" class="w-4 h-4 text-amber-500"></i>
                <span>PO Supplier</span>
            </a>
            @if(\App\Support\Context::hasPermission('inventory.manage'))
            <button @click="showCreateModal = true"
                    class="px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs shadow-sm hover:shadow transition inline-flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Tambah Gudang</span>
            </button>
            @endif
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-800 dark:text-emerald-300 text-xs sm:text-sm font-medium flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-emerald-500/20 flex items-center justify-center shrink-0">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
            </div>
            <div class="flex-1">{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-800 dark:text-rose-300 text-xs sm:text-sm font-medium flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-rose-500/20 flex items-center justify-center shrink-0">
                <i data-lucide="alert-octagon" class="w-4 h-4 text-rose-600 dark:text-rose-400"></i>
            </div>
            <div class="flex-1">{{ session('error') }}</div>
        </div>
    @endif

    {{-- ===== 4 COMMAND KPI METRICS ===== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        {{-- KPI 1: Total Gudang --}}
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs relative overflow-hidden group">
            <div class="flex items-center justify-between gap-2 mb-3">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Gudang</span>
                <div class="w-8 h-8 rounded-xl bg-cyan-500/10 dark:bg-cyan-500/15 border border-cyan-500/20 flex items-center justify-center">
                    <i data-lucide="warehouse" class="w-4 h-4 text-cyan-600 dark:text-cyan-400"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-mono tracking-tight">{{ $totalWarehouses }}</div>
            <div class="flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>{{ $activeWarehouses }} beroperasi aktif</span>
            </div>
        </div>

        {{-- KPI 2: Total Nilai Aset --}}
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs relative overflow-hidden group">
            <div class="flex items-center justify-between gap-2 mb-3">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Nilai Aset</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/15 border border-emerald-500/20 flex items-center justify-center">
                    <i data-lucide="coins" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                </div>
            </div>
            <div class="text-lg sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono tracking-tight truncate">
                Rp {{ number_format($totalValuation, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                Kalkulasi HPP seluruh lokasi
            </div>
        </div>

        {{-- KPI 3: Stok Minimum --}}
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs relative overflow-hidden group">
            <div class="flex items-center justify-between gap-2 mb-3">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Stok Minimum</span>
                <div class="w-8 h-8 rounded-xl {{ $totalLowStock > 0 ? 'bg-amber-500/15 border-amber-500/30' : 'bg-slate-100 dark:bg-slate-800' }} border flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-4 h-4 {{ $totalLowStock > 0 ? 'text-amber-500' : 'text-slate-400' }}"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black {{ $totalLowStock > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-white' }} font-mono tracking-tight">
                {{ $totalLowStock }}
            </div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                {{ $totalLowStock > 0 ? 'Item memerlukan restock PO' : 'Semua stok dalam batas aman' }}
            </div>
        </div>

        {{-- KPI 4: Mutasi Terkini --}}
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs relative overflow-hidden group">
            <div class="flex items-center justify-between gap-2 mb-3">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Mutasi Terkini</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/15 border border-indigo-500/20 flex items-center justify-center">
                    <i data-lucide="activity" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-mono tracking-tight">
                {{ $recentMovements->count() }}
            </div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                Pergerakan stok tercatat di audit log
            </div>
        </div>
    </div>

    {{-- ===== GUDANG CARDS GRID SECTION ===== --}}
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <div class="w-2.5 h-2.5 rounded-full bg-cyan-500"></div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                    Daftar Gudang & Lokasi Aktif
                </h2>
                <span class="text-xs text-slate-400 font-mono">({{ $locations->count() }})</span>
            </div>

            {{-- Quick Filter / Search --}}
            <div class="flex items-center gap-2">
                <div class="relative w-full sm:w-64">
                    <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                    <input type="text" x-model="searchQuery" placeholder="Cari nama / kode gudang..."
                           class="w-full pl-9 pr-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 transition">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($locations as $loc)
            <div x-show="!searchQuery || '{{ strtolower($loc->name . ' ' . $loc->code . ' ' . $loc->type) }}'.includes(searchQuery.toLowerCase())"
                 class="rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs hover:shadow-md transition-all duration-200 flex flex-col justify-between overflow-hidden group">
                
                {{-- Card Top Section --}}
                <div class="p-5">
                    {{-- Header & Type Badges --}}
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0
                                @if($loc->type === 'warehouse')
                                    bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20
                                @elseif($loc->type === 'central_kitchen')
                                    bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20
                                @else
                                    bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20
                                @endif
                            ">
                                @if($loc->type === 'warehouse')
                                    <i data-lucide="warehouse" class="w-5 h-5"></i>
                                @elseif($loc->type === 'central_kitchen')
                                    <i data-lucide="utensils" class="w-5 h-5"></i>
                                @else
                                    <i data-lucide="store" class="w-5 h-5"></i>
                                @endif
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 dark:text-white text-sm leading-snug group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors">
                                    {{ $loc->name }}
                                </h3>
                                @if($loc->code)
                                    <div class="text-[10px] font-mono text-slate-500 dark:text-slate-400 font-semibold tracking-wider mt-0.5">
                                        {{ $loc->code }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Badges --}}
                        <div class="flex flex-col items-end gap-1">
                            @if($loc->type === 'warehouse')
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider bg-cyan-50 text-cyan-700 border border-cyan-200 dark:bg-cyan-500/15 dark:text-cyan-300 dark:border-cyan-500/30">
                                    Gudang
                                </span>
                            @elseif($loc->type === 'central_kitchen')
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:border-amber-500/30">
                                    Dapur
                                </span>
                            @else
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/30">
                                    Outlet
                                </span>
                            @endif

                            @if($loc->is_primary)
                                <span class="text-[9px] px-1.5 py-0.5 rounded-md font-bold uppercase bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-500/15 dark:text-indigo-300 dark:border-indigo-500/30">
                                    Utama
                                </span>
                            @endif
                            @if(!$loc->is_active)
                                <span class="text-[9px] px-1.5 py-0.5 rounded-md font-bold uppercase bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700">
                                    Nonaktif
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Address / Phone preview --}}
                    @if($loc->address)
                        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-1 mb-2 flex items-center gap-1.5">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5 shrink-0 text-slate-400"></i>
                            <span>{{ $loc->address }}</span>
                        </p>
                    @endif
                    @if($loc->phone)
                        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-1 mb-3 flex items-center gap-1.5">
                            <i data-lucide="phone" class="w-3.5 h-3.5 shrink-0 text-slate-400"></i>
                            <span class="font-mono">{{ $loc->phone }}</span>
                        </p>
                    @endif

                    {{-- Stats Strip --}}
                    <div class="grid grid-cols-3 gap-2 p-3 rounded-xl bg-slate-50/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800/80">
                        <div class="text-center">
                            <div class="text-sm font-black text-slate-900 dark:text-white font-mono">
                                {{ $loc->total_products }}
                            </div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 font-medium mt-0.5">Produk</div>
                        </div>
                        <div class="text-center border-x border-slate-200 dark:border-slate-800">
                            <div class="text-sm font-black text-emerald-600 dark:text-emerald-400 font-mono">
                                @if($loc->total_valuation >= 1000000)
                                    {{ number_format($loc->total_valuation / 1000000, 1) }}JT
                                @else
                                    {{ number_format($loc->total_valuation / 1000, 0) }}K
                                @endif
                            </div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 font-medium mt-0.5">Nilai Aset</div>
                        </div>
                        <div class="text-center">
                            <div class="text-sm font-black font-mono {{ $loc->low_stock_count > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-700 dark:text-slate-300' }}">
                                {{ $loc->low_stock_count }}
                            </div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 font-medium mt-0.5">Min. Stok</div>
                        </div>
                    </div>
                </div>

                {{-- Card Actions Footer --}}
                <div class="px-5 py-3 bg-slate-50/50 dark:bg-slate-950/30 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-2">
                    <a href="{{ route('warehouse.show', $loc->id) }}"
                       class="flex-1 py-2 px-3 rounded-xl bg-cyan-50 dark:bg-cyan-500/10 hover:bg-cyan-100 dark:hover:bg-cyan-500/20 text-cyan-700 dark:text-cyan-300 text-xs font-bold transition border border-cyan-200 dark:border-cyan-500/20 inline-flex items-center justify-center gap-1.5">
                        <i data-lucide="arrow-right-circle" class="w-3.5 h-3.5"></i>
                        <span>Detail Gudang</span>
                    </a>
                    @if(\App\Support\Context::hasPermission('inventory.manage'))
                    <button
                        @click="openEdit({
                            id: '{{ $loc->id }}',
                            name: '{{ addslashes($loc->name) }}',
                            type: '{{ $loc->type }}',
                            code: '{{ $loc->code ?? '' }}',
                            phone: '{{ $loc->phone ?? '' }}',
                            address: '{{ addslashes($loc->address ?? '') }}',
                            is_active: {{ $loc->is_active ? 'true' : 'false' }}
                        })"
                        title="Edit Info Gudang"
                        class="p-2 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 transition">
                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                    </button>
                    @if(!$loc->is_primary)
                    <form action="{{ route('warehouse.destroy', $loc->id) }}" method="POST"
                          onsubmit="return AppAlert.confirmSubmit(event, this, 'Hapus gudang {{ addslashes($loc->name) }}? Pastikan tidak ada stok aktif di lokasi ini.', 'Hapus Gudang?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                title="Hapus Gudang"
                                class="p-2 rounded-xl bg-white dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 border border-slate-200 dark:border-slate-700 transition">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </form>
                    @endif
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-full rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 p-12 text-center">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="warehouse" class="w-8 h-8 text-slate-400"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Belum Ada Gudang / Lokasi</h3>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 mb-5 max-w-md mx-auto">
                    Mulai kelola inventaris multi-lokasi dengan menambahkan gudang pusat, dapur produksi, atau outlet toko Anda.
                </p>
                @if(\App\Support\Context::hasPermission('inventory.manage'))
                <button @click="showCreateModal = true"
                        class="px-5 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs shadow-sm hover:shadow transition inline-flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Tambah Gudang Pertama</span>
                </button>
                @endif
            </div>
            @endforelse
        </div>
    </div>

    {{-- ===== PANDUAN WORKFLOW BARANG MASUK ===== --}}
    <div class="p-6 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs space-y-4">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/15 border border-indigo-500/20 flex items-center justify-center">
                    <i data-lucide="book-open" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm">Alur Standar Penerimaan Barang Masuk (Inbound)</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Ikuti 4 langkah ini untuk memastikan integritas stok dan akurasi HPP bisnis.</p>
                </div>
            </div>
            <a href="{{ route('purchase-orders.create', ['type' => 'supplier']) }}"
               class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline inline-flex items-center gap-1">
                <span>Buat PO Baru</span>
                <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 sm:gap-4 pt-1">
            {{-- Step 1 --}}
            <div class="p-4 rounded-xl bg-slate-50/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800/80 relative">
                <div class="w-7 h-7 rounded-lg bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-amber-600 dark:text-amber-400 font-mono font-black text-xs mb-2.5">
                    1
                </div>
                <h4 class="text-xs font-bold text-slate-900 dark:text-white mb-1">Buat Purchase Order</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                    Terbitkan PO Supplier berisi daftar item, estimasi biaya, dan termin pembayaran yang disepakati.
                </p>
            </div>

            {{-- Step 2 --}}
            <div class="p-4 rounded-xl bg-slate-50/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800/80 relative">
                <div class="w-7 h-7 rounded-lg bg-cyan-500/15 border border-cyan-500/30 flex items-center justify-center text-cyan-600 dark:text-cyan-400 font-mono font-black text-xs mb-2.5">
                    2
                </div>
                <h4 class="text-xs font-bold text-slate-900 dark:text-white mb-1">Konfirmasi Pesanan</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                    Ubah status PO menjadi Dikonfirmasi saat vendor menyetujui jadwal pengiriman barang.
                </p>
            </div>

            {{-- Step 3 --}}
            <div class="p-4 rounded-xl bg-slate-50/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800/80 relative">
                <div class="w-7 h-7 rounded-lg bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 font-mono font-black text-xs mb-2.5">
                    3
                </div>
                <h4 class="text-xs font-bold text-slate-900 dark:text-white mb-1">Terima Barang ke Gudang</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                    Klik "Terima Barang" di detail PO. Pilih lokasi gudang tujuan dan periksa kuantitas fisik aktual.
                </p>
            </div>

            {{-- Step 4 --}}
            <div class="p-4 rounded-xl bg-slate-50/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800/80 relative">
                <div class="w-7 h-7 rounded-lg bg-indigo-500/15 border border-indigo-500/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-mono font-black text-xs mb-2.5">
                    4
                </div>
                <h4 class="text-xs font-bold text-slate-900 dark:text-white mb-1">Stok & Nilai Otomatis Sinkron</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                    Sistem otomatis mengkalkulasi HPP rata-rata berjalan, mencatat kartu stok, dan mengupdate saldo.
                </p>
            </div>
        </div>
    </div>

    {{-- ===== RIWAYAT MUTASI TERKINI ===== --}}
    @if($recentMovements->isNotEmpty())
    <div class="rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/15 border border-indigo-500/20 flex items-center justify-center">
                    <i data-lucide="activity" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Aktivitas & Mutasi Stok Terkini</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Log mutasi real-time dari transaksi POS, PO, dan penyesuaian</p>
                </div>
            </div>
            <a href="{{ route('inventory.movements') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-bold inline-flex items-center gap-1">
                <span>Lihat Semua Mutasi</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300 min-w-[620px]">
                <thead class="bg-slate-50/80 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-200/80 dark:border-slate-800/80 whitespace-nowrap">
                    <tr>
                        <th class="py-3 px-4">Produk</th>
                        <th class="py-3 px-4">Gudang / Lokasi</th>
                        <th class="py-3 px-4">Tipe Mutasi</th>
                        <th class="py-3 px-4 text-right">Perubahan Qty</th>
                        <th class="py-3 px-4 text-right">Saldo Akhir</th>
                        <th class="py-3 px-4">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @foreach($recentMovements as $mv)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900 dark:text-white">{{ $mv->product?->name ?? '-' }}</div>
                            @if($mv->product?->code)
                                <div class="text-[10px] font-mono text-slate-500">{{ $mv->product->code }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-600 dark:text-slate-300 font-medium">{{ $mv->location?->name ?? '-' }}</td>
                        <td class="py-3 px-4">
                            @php
                                $mvLabels = [
                                    'goods_receipt'  => ['label' => 'Penerimaan PO', 'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/30'],
                                    'pos_sale'       => ['label' => 'Penjualan POS', 'bg' => 'bg-cyan-50 text-cyan-700 border-cyan-200 dark:bg-cyan-500/15 dark:text-cyan-300 dark:border-cyan-500/30'],
                                    'adjustment'     => ['label' => 'Penyesuaian', 'bg' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:border-amber-500/30'],
                                    'transfer_in'    => ['label' => 'Transfer Masuk', 'bg' => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-500/15 dark:text-indigo-300 dark:border-indigo-500/30'],
                                    'transfer_out'   => ['label' => 'Transfer Keluar', 'bg' => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'],
                                    'opname'         => ['label' => 'Opname Fisik', 'bg' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-500/15 dark:text-purple-300 dark:border-purple-500/30'],
                                ];
                                $mvInfo = $mvLabels[$mv->movement_type] ?? ['label' => ucfirst(str_replace('_', ' ', $mv->movement_type)), 'bg' => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'];
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $mvInfo['bg'] }}">
                                {{ $mvInfo['label'] }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold {{ $mv->quantity_change >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ $mv->quantity_change >= 0 ? '+' : '' }}{{ number_format($mv->quantity_change, 2) }}
                            <span class="text-[10px] text-slate-400 font-sans ml-0.5">{{ $mv->product?->outputUnit?->symbol }}</span>
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-slate-800 dark:text-slate-200">
                            {{ number_format($mv->balance_after, 2) }}
                        </td>
                        <td class="py-3 px-4 text-slate-500 dark:text-slate-400 text-[11px] whitespace-nowrap">
                            {{ $mv->created_at->diffForHumans() }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ===== MODAL: TAMBAH GUDANG ===== --}}
    <div x-show="showCreateModal"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-xs p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display:none;">
        <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl p-6 space-y-5 max-h-[90vh] overflow-y-auto"
             @click.outside="showCreateModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20 flex items-center justify-center">
                        <i data-lucide="warehouse" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-slate-900 dark:text-white">Tambah Lokasi / Gudang Baru</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Konfigurasikan fisik penyimpanan atau cabang bisnis.</p>
                    </div>
                </div>
                <button @click="showCreateModal = false" class="p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 dark:hover:text-white transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('warehouse.store') }}" method="POST" class="space-y-4 text-xs sm:text-sm">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                            Nama Gudang / Outlet <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" required placeholder="Contoh: Gudang Utama, Kitchen Jakarta, Outlet Senopati..."
                               class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 transition">
                    </div>
                    <div>
                        <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                            Tipe Lokasi <span class="text-rose-500">*</span>
                        </label>
                        <select name="type" class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 transition">
                            <option value="warehouse">🏢 Gudang (Warehouse)</option>
                            <option value="outlet">🏪 Outlet / Toko Retail</option>
                            <option value="central_kitchen">🍳 Dapur Pusat (Central Kitchen)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                            Kode Lokasi
                        </label>
                        <input type="text" name="code" placeholder="Misal: WH-01, OTL-01..."
                               class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs sm:text-sm font-mono focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 transition">
                    </div>
                    <div>
                        <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                            Nomor Telepon
                        </label>
                        <input type="text" name="phone" placeholder="08xxxxxxxxxx / +62..."
                               class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 transition">
                    </div>
                    <div>
                        <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                            Status Awal
                        </label>
                        <div class="px-3.5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-xs text-slate-600 dark:text-slate-300 font-medium flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Langsung Aktif & Siap Digunakan</span>
                        </div>
                    </div>
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                            Alamat Lengkap
                        </label>
                        <textarea name="address" rows="2" placeholder="Alamat fisik, nomor jalan, kecamatan, kota..."
                                  class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 transition resize-none"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="showCreateModal = false"
                            class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs transition shadow-sm hover:shadow">
                        Simpan Gudang
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== MODAL: EDIT GUDANG ===== --}}
    <div x-show="showEditModal"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-xs p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display:none;">
        <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl p-6 space-y-5 max-h-[90vh] overflow-y-auto"
             @click.outside="showEditModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20 flex items-center justify-center">
                        <i data-lucide="pencil" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-slate-900 dark:text-white">Edit Informasi Gudang</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400" x-text="'Memperbarui: ' + editData.name"></p>
                    </div>
                </div>
                <button @click="showEditModal = false" class="p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 dark:hover:text-white transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'{{ url('/warehouse') }}/' + editData.id" method="POST" class="space-y-4 text-xs sm:text-sm">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                            Nama Gudang / Lokasi <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" :value="editData.name" required
                               class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 transition">
                    </div>
                    <div>
                        <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                            Tipe Lokasi
                        </label>
                        <select name="type" class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 transition">
                            <option value="warehouse" :selected="editData.type === 'warehouse'">🏢 Gudang (Warehouse)</option>
                            <option value="outlet" :selected="editData.type === 'outlet'">🏪 Outlet / Toko</option>
                            <option value="central_kitchen" :selected="editData.type === 'central_kitchen'">🍳 Dapur Pusat</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                            Kode Lokasi
                        </label>
                        <input type="text" name="code" :value="editData.code"
                               class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs sm:text-sm font-mono focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 transition">
                    </div>
                    <div>
                        <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                            Nomor Telepon
                        </label>
                        <input type="text" name="phone" :value="editData.phone"
                               class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 transition">
                    </div>
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                            Alamat Lengkap
                        </label>
                        <textarea name="address" rows="2" :value="editData.address"
                                  class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 transition resize-none"></textarea>
                    </div>
                    <div class="col-span-1 sm:col-span-2 flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1"
                               :checked="editData.is_active" class="w-4 h-4 accent-cyan-600 rounded">
                        <label for="edit_is_active" class="text-xs text-slate-700 dark:text-slate-300 font-medium cursor-pointer">
                            Gudang beroperasi aktif (dapat menerima PO, transfer stok, dan menerbitkan barang POS)
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="showEditModal = false"
                            class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs transition shadow-sm hover:shadow">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
