@extends('layouts.app', [
    'title' => 'Detail Gudang — ' . $location->name,
    'headerTitle' => 'Detail Gudang: ' . $location->name,
    'headerSubtitle' => 'Pantau stok aktual, riwayat penerimaan barang dari PO, dan mutasi kartu stok lokasi ini.'
])

@section('content')
<div class="space-y-6" x-data="{
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

    {{-- ===== SUB-NAVIGATION TABS ===== --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-slate-200/80 dark:border-slate-800/80 scrollbar-none">
        <a href="{{ route('warehouse.index') }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20 shadow-xs">
            <i data-lucide="warehouse" class="w-4 h-4 text-cyan-500"></i>
            <span>Daftar Gudang & Lokasi</span>
        </a>
        <a href="{{ route('inventory.stocks') }}?location_id={{ $location->id }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all whitespace-nowrap text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60">
            <i data-lucide="layers" class="w-4 h-4 text-slate-400"></i>
            <span>Stok di Lokasi Ini</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] font-mono font-bold bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300">{{ $stocks->total() }}</span>
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
        <a href="{{ route('inventory.movements') }}?location_id={{ $location->id }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all whitespace-nowrap text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60">
            <i data-lucide="activity" class="w-4 h-4 text-slate-400"></i>
            <span>Riwayat Mutasi</span>
        </a>
        <a href="{{ route('purchase-orders.index') }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all whitespace-nowrap text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60">
            <i data-lucide="truck" class="w-4 h-4 text-slate-400"></i>
            <span>PO Supplier</span>
        </a>
    </div>

    {{-- ===== BREADCRUMB & HEADER SECTION ===== --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-1.5 mb-2 text-xs text-slate-500 dark:text-slate-400">
                <a href="{{ route('warehouse.index') }}" class="hover:text-cyan-600 dark:hover:text-cyan-400 transition font-medium">Gudang & Lokasi</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
                <span class="text-slate-800 dark:text-slate-200 font-bold">{{ $location->name }}</span>
            </nav>

            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 shadow-2xs
                    @if($location->type === 'warehouse')
                        bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20
                    @elseif($location->type === 'central_kitchen')
                        bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20
                    @else
                        bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20
                    @endif
                ">
                    @if($location->type === 'warehouse')
                        <i data-lucide="warehouse" class="w-6 h-6"></i>
                    @elseif($location->type === 'central_kitchen')
                        <i data-lucide="utensils" class="w-6 h-6"></i>
                    @else
                        <i data-lucide="store" class="w-6 h-6"></i>
                    @endif
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        {{ $location->name }}
                    </h1>
                    <div class="flex items-center gap-2 mt-1 flex-wrap">
                        @if($location->type === 'warehouse')
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider bg-cyan-50 text-cyan-700 border border-cyan-200 dark:bg-cyan-500/15 dark:text-cyan-300 dark:border-cyan-500/30">
                                Gudang
                            </span>
                        @elseif($location->type === 'central_kitchen')
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:border-amber-500/30">
                                Dapur Pusat
                            </span>
                        @else
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/30">
                                Outlet
                            </span>
                        @endif

                        @if($location->code)
                            <span class="text-[10px] font-mono font-bold text-slate-500 dark:text-slate-400 px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                {{ $location->code }}
                            </span>
                        @endif

                        @if($location->is_primary)
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-500/15 dark:text-indigo-300 dark:border-indigo-500/30">
                                Lokasi Utama
                            </span>
                        @endif

                        @if(!$location->is_active)
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700">
                                Nonaktif
                            </span>
                        @else
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/30">
                                Aktif
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Right Actions --}}
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('warehouse.index') }}"
               class="px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-200/90 dark:border-slate-800 shadow-2xs transition inline-flex items-center gap-1.5">
                <i data-lucide="arrow-left" class="w-4 h-4 text-slate-400"></i>
                <span>Kembali</span>
            </a>
            @if(\App\Support\Context::hasPermission('inventory.manage'))
            <button @click="showEditModal = true"
                    class="px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-200/90 dark:border-slate-800 shadow-2xs transition inline-flex items-center gap-1.5">
                <i data-lucide="pencil" class="w-4 h-4 text-amber-500"></i>
                <span>Edit Gudang</span>
            </button>
            @endif
            @if(\App\Support\Context::hasPermission('receiving.manage'))
            <a href="{{ route('purchase-orders.index') }}?po_type=supplier"
               class="px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs shadow-sm hover:shadow transition inline-flex items-center gap-2">
                <i data-lucide="package-plus" class="w-4 h-4"></i>
                <span>Terima Barang dari PO</span>
            </a>
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

    {{-- ===== METADATA STRIP ===== --}}
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
            <div class="flex items-start gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0 text-slate-500">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider block">Alamat Fisik</span>
                    <span class="text-slate-800 dark:text-slate-200 font-medium leading-relaxed">
                        {{ $location->address ?: 'Belum ada alamat terdaftar' }}
                    </span>
                </div>
            </div>
            <div class="flex items-start gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0 text-slate-500">
                    <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider block">Kontak / Telepon</span>
                    <span class="text-slate-800 dark:text-slate-200 font-mono font-medium">
                        {{ $location->phone ?: '—' }}
                    </span>
                </div>
            </div>
            <div class="flex items-start gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0 text-slate-500">
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider block">Terdaftar Sejak</span>
                    <span class="text-slate-800 dark:text-slate-200 font-medium">
                        {{ $location->created_at->format('d M Y') }} ({{ $location->created_at->diffForHumans() }})
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== 4 COMMAND KPI METRICS ===== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        {{-- KPI 1: Total Produk --}}
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs relative overflow-hidden group">
            <div class="flex items-center justify-between gap-2 mb-3">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Produk</span>
                <div class="w-8 h-8 rounded-xl bg-cyan-500/10 dark:bg-cyan-500/15 border border-cyan-500/20 flex items-center justify-center">
                    <i data-lucide="package" class="w-4 h-4 text-cyan-600 dark:text-cyan-400"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-mono tracking-tight">{{ $stocks->total() }}</div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Item fisik terdaftar di lokasi ini</div>
        </div>

        {{-- KPI 2: Total Nilai Aset --}}
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs relative overflow-hidden group">
            <div class="flex items-center justify-between gap-2 mb-3">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nilai Aset Stok</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/15 border border-emerald-500/20 flex items-center justify-center">
                    <i data-lucide="coins" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                </div>
            </div>
            <div class="text-lg sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono tracking-tight truncate">
                Rp {{ number_format($totalValuation, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Berdasarkan kalkulasi HPP unit</div>
        </div>

        {{-- KPI 3: Stok Minimum --}}
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs relative overflow-hidden group">
            <div class="flex items-center justify-between gap-2 mb-3">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Stok Minimum</span>
                <div class="w-8 h-8 rounded-xl {{ $lowStockCount > 0 ? 'bg-amber-500/15 border-amber-500/30' : 'bg-slate-100 dark:bg-slate-800' }} border flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-4 h-4 {{ $lowStockCount > 0 ? 'text-amber-500' : 'text-slate-400' }}"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black {{ $lowStockCount > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-white' }} font-mono tracking-tight">
                {{ $lowStockCount }}
            </div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                {{ $lowStockCount > 0 ? 'Item mendekati batas kritis' : 'Stok dalam batas aman' }}
            </div>
        </div>

        {{-- KPI 4: Penerimaan Barang PO --}}
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs relative overflow-hidden group">
            <div class="flex items-center justify-between gap-2 mb-3">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Penerimaan PO</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/15 border border-indigo-500/20 flex items-center justify-center">
                    <i data-lucide="package-check" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-mono tracking-tight">
                {{ $receipts->count() }}
            </div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Dokumen Goods Receipt tersimpan</div>
        </div>
    </div>

    {{-- ===== QUICK ACTION SHORTCUT CARDS ===== --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        <a href="{{ route('purchase-orders.index') }}?po_type=supplier"
           class="p-4 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs hover:shadow-md transition-all flex items-center gap-3.5 group">
            <div class="w-11 h-11 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <i data-lucide="truck" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="font-bold text-sm text-slate-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                    Terima dari PO Supplier
                </h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Buka PO confirmed → klik Terima Barang</p>
            </div>
        </a>

        <a href="{{ route('inventory.transfers.index') }}"
           class="p-4 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs hover:shadow-md transition-all flex items-center gap-3.5 group">
            <div class="w-11 h-11 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <i data-lucide="arrow-left-right" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="font-bold text-sm text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                    Transfer Stok Antar Lokasi
                </h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Kirim stok ke cabang / outlet lain</p>
            </div>
        </a>

        <a href="{{ route('inventory.opnames.index') }}"
           class="p-4 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs hover:shadow-md transition-all flex items-center gap-3.5 group">
            <div class="w-11 h-11 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <i data-lucide="clipboard-check" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="font-bold text-sm text-slate-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
                    Stock Opname Fisik
                </h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Rekonsiliasi selisih stok buku vs riil</p>
            </div>
        </a>
    </div>

    {{-- ===== STOK PRODUK DI GUDANG INI ===== --}}
    <div class="rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/15 border border-emerald-500/20 flex items-center justify-center">
                    <i data-lucide="package" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Daftar Stok Produk di Lokasi Ini</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Daftar kuantitas fisik dan valuasi HPP per item</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <div class="relative w-48 sm:w-60">
                    <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                    <input type="text" x-model="searchStock" placeholder="Cari nama produk / kode..."
                           class="w-full pl-9 pr-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                </div>
                <a href="{{ route('inventory.stocks') }}?location_id={{ $location->id }}"
                   class="text-xs text-emerald-600 dark:text-emerald-400 hover:underline font-bold inline-flex items-center gap-1">
                    <span>Semua Stok</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300 min-w-[700px]">
                <thead class="bg-slate-50/80 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-200/80 dark:border-slate-800/80 whitespace-nowrap">
                    <tr>
                        <th class="py-3 px-4">Produk</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4 text-right">Stok Aktual</th>
                        <th class="py-3 px-4 text-right">Min. Stok</th>
                        <th class="py-3 px-4 text-right">HPP / Unit</th>
                        <th class="py-3 px-4 text-right">Total Nilai</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        @if(\App\Support\Context::hasPermission('inventory.manage'))
                        <th class="py-3 px-4 text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($stocks as $stock)
                    @php
                        $isLow = $stock->product && $stock->product->min_stock > 0 && $stock->quantity <= $stock->product->min_stock;
                        $valuation = (float)$stock->quantity * (float)$stock->last_cost;
                    @endphp
                    <tr x-show="!searchStock || '{{ strtolower($stock->product?->name . ' ' . $stock->product?->code . ' ' . ($stock->product?->category?->name ?? '')) }}'.includes(searchStock.toLowerCase())"
                        class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors {{ $isLow ? 'bg-amber-500/5' : '' }}">
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900 dark:text-white">{{ $stock->product?->name ?? '—' }}</div>
                            @if($stock->product?->code)
                                <div class="text-[10px] font-mono text-slate-500">{{ $stock->product->code }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                            {{ $stock->product?->category?->name ?? '—' }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            <span class="font-black font-mono text-sm {{ $isLow ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-white' }}">
                                {{ number_format($stock->quantity, 2) }}
                            </span>
                            <span class="text-[10px] text-slate-400 ml-0.5">{{ $stock->product?->outputUnit?->symbol ?? '' }}</span>
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-slate-500 dark:text-slate-400">
                            {{ $stock->product ? number_format($stock->product->min_stock, 2) : '—' }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-slate-700 dark:text-slate-300">
                            Rp {{ number_format($stock->last_cost, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                            Rp {{ number_format($valuation, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($isLow)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:border-amber-500/30">
                                    ⚠ Hampir Habis
                                </span>
                            @elseif($stock->quantity <= 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-500/15 dark:text-rose-300 dark:border-rose-500/30">
                                    Habis
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/30">
                                    Tersedia
                                </span>
                            @endif
                        </td>
                        @if(\App\Support\Context::hasPermission('inventory.manage'))
                        <td class="py-3 px-4 text-right">
                            <button
                                @click="openAdjust({
                                    id: '{{ $stock->id }}',
                                    product_id: '{{ $stock->product_id }}',
                                    location_id: '{{ $stock->location_id }}',
                                    product_name: '{{ addslashes($stock->product?->name ?? '') }}',
                                    quantity: '{{ $stock->quantity }}',
                                    last_cost: '{{ $stock->last_cost }}'
                                })"
                                class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-cyan-50 dark:bg-slate-800 dark:hover:bg-cyan-500/20 text-slate-700 hover:text-cyan-700 dark:text-slate-300 dark:hover:text-cyan-300 text-xs font-bold transition border border-slate-200 dark:border-slate-700">
                                Sesuaikan
                            </button>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-500 dark:text-slate-400">
                            <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-2.5">
                                <i data-lucide="inbox" class="w-6 h-6 text-slate-400"></i>
                            </div>
                            <div class="font-bold text-slate-800 dark:text-slate-200 text-sm">Belum ada stok fisik di gudang ini</div>
                            <div class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                                Lakukan penerimaan barang dari Purchase Order Supplier atau transfer stok dari cabang lain.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($stocks->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">{{ $stocks->links() }}</div>
        @endif
    </div>

    {{-- ===== RIWAYAT PENERIMAAN BARANG (GOODS RECEIPTS) ===== --}}
    @if($receipts->isNotEmpty())
    <div class="rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-amber-500/10 dark:bg-amber-500/15 border border-amber-500/20 flex items-center justify-center">
                    <i data-lucide="package-check" class="w-4 h-4 text-amber-600 dark:text-amber-400"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Riwayat Penerimaan Barang (Goods Receipt)</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Daftar inbound barang yang diterima masuk ke gudang ini</p>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300 min-w-[640px]">
                <thead class="bg-slate-50/80 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-200/80 dark:border-slate-800/80 whitespace-nowrap">
                    <tr>
                        <th class="py-3 px-4">No. Penerimaan</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Supplier</th>
                        <th class="py-3 px-4">No. PO</th>
                        <th class="py-3 px-4 text-center">Item</th>
                        <th class="py-3 px-4">Diterima Oleh</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @foreach($receipts as $gr)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="py-3 px-4 font-mono font-bold text-slate-900 dark:text-white">
                            #{{ $gr->receipt_number }}
                        </td>
                        <td class="py-3 px-4 text-slate-500 dark:text-slate-400">
                            {{ $gr->receipt_date?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-800 dark:text-slate-200">
                            {{ $gr->supplier?->name ?? 'Tanpa Supplier' }}
                        </td>
                        <td class="py-3 px-4">
                            @if($gr->purchaseOrder)
                                <a href="{{ route('purchase-orders.show', $gr->purchaseOrder->id) }}"
                                   class="font-mono text-amber-600 dark:text-amber-400 hover:underline font-bold">
                                    {{ $gr->purchaseOrder->po_number }}
                                </a>
                            @else
                                <span class="text-slate-400 font-medium">1-Klik (Solo Mode)</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center font-mono font-bold text-slate-800 dark:text-slate-200">
                            {{ $gr->items->count() }}
                        </td>
                        <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                            {{ $gr->receiver?->name ?? '—' }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/30">
                                Diterima
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ===== KARTU STOK — MUTASI TERKINI ===== --}}
    @if($recentMovements->isNotEmpty())
    <div class="rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/15 border border-indigo-500/20 flex items-center justify-center">
                    <i data-lucide="history" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Kartu Stok — Mutasi Terkini</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Audit trail pergerakan saldo barang di lokasi ini</p>
                </div>
            </div>
            <a href="{{ route('inventory.movements') }}?location_id={{ $location->id }}"
               class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-bold inline-flex items-center gap-1">
                <span>Lihat Semua Mutasi</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300 min-w-[650px]">
                <thead class="bg-slate-50/80 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-200/80 dark:border-slate-800/80 whitespace-nowrap">
                    <tr>
                        <th class="py-3 px-4">Produk</th>
                        <th class="py-3 px-4">Tipe Mutasi</th>
                        <th class="py-3 px-4">No. Referensi</th>
                        <th class="py-3 px-4 text-right">Perubahan Qty</th>
                        <th class="py-3 px-4 text-right">Saldo Akhir</th>
                        <th class="py-3 px-4">Operator</th>
                        <th class="py-3 px-4">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @foreach($recentMovements as $mv)
                    @php
                        $mvLabels = [
                            'goods_receipt'  => ['label' => 'Penerimaan PO', 'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/30'],
                            'pos_sale'       => ['label' => 'Penjualan POS', 'bg' => 'bg-cyan-50 text-cyan-700 border-cyan-200 dark:bg-cyan-500/15 dark:text-cyan-300 dark:border-cyan-500/30'],
                            'adjustment'     => ['label' => 'Penyesuaian Manual', 'bg' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:border-amber-500/30'],
                            'transfer_in'    => ['label' => 'Transfer Masuk', 'bg' => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-500/15 dark:text-indigo-300 dark:border-indigo-500/30'],
                            'transfer_out'   => ['label' => 'Transfer Keluar', 'bg' => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'],
                            'opname'         => ['label' => 'Rekonsiliasi Opname', 'bg' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-500/15 dark:text-purple-300 dark:border-purple-500/30'],
                            'initial'        => ['label' => 'Stok Awal', 'bg' => 'bg-cyan-50 text-cyan-700 border-cyan-200 dark:bg-cyan-500/15 dark:text-cyan-300 dark:border-cyan-500/30'],
                            'pos_refund'     => ['label' => 'Refund POS', 'bg' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-500/15 dark:text-rose-300 dark:border-rose-500/30'],
                        ];
                        $mvInfo = $mvLabels[$mv->movement_type] ?? ['label' => ucfirst(str_replace('_', ' ', $mv->movement_type)), 'bg' => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'];
                    @endphp
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900 dark:text-white">{{ $mv->product?->name ?? '—' }}</div>
                            @if($mv->product?->code)
                                <div class="text-[10px] font-mono text-slate-500">{{ $mv->product->code }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $mvInfo['bg'] }}">
                                {{ $mvInfo['label'] }}
                            </span>
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-500 text-[11px]">
                            {{ $mv->reference_number ?? '—' }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold {{ $mv->quantity_change >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ $mv->quantity_change >= 0 ? '+' : '' }}{{ number_format($mv->quantity_change, 2) }}
                            <span class="text-[10px] text-slate-400 font-sans ml-0.5">{{ $mv->product?->outputUnit?->symbol }}</span>
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-slate-800 dark:text-slate-200">
                            {{ number_format($mv->balance_after, 2) }}
                        </td>
                        <td class="py-3 px-4 text-slate-600 dark:text-slate-400">{{ $mv->creator?->name ?? 'Sistem' }}</td>
                        <td class="py-3 px-4 text-slate-500 dark:text-slate-400 text-[11px] whitespace-nowrap">{{ $mv->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

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
                        <h3 class="font-bold text-base text-slate-900 dark:text-white">Edit Gudang: {{ $location->name }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Perbarui informasi dan status operasional lokasi.</p>
                    </div>
                </div>
                <button @click="showEditModal = false" class="p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 dark:hover:text-white transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('warehouse.update', $location->id) }}" method="POST" class="space-y-4 text-xs sm:text-sm">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                            Nama Gudang / Lokasi <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ $location->name }}" required
                               class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 transition">
                    </div>
                    <div>
                        <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                            Tipe Lokasi
                        </label>
                        <select name="type" class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 transition">
                            <option value="warehouse" {{ $location->type === 'warehouse' ? 'selected' : '' }}>🏢 Gudang (Warehouse)</option>
                            <option value="outlet" {{ $location->type === 'outlet' ? 'selected' : '' }}>🏪 Outlet / Toko</option>
                            <option value="central_kitchen" {{ $location->type === 'central_kitchen' ? 'selected' : '' }}>🍳 Dapur Pusat</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                            Kode Lokasi
                        </label>
                        <input type="text" name="code" value="{{ $location->code }}"
                               class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs sm:text-sm font-mono focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 transition">
                    </div>
                    <div>
                        <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                            Nomor Telepon
                        </label>
                        <input type="text" name="phone" value="{{ $location->phone }}"
                               class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 transition">
                    </div>
                    <div>
                        <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                            Status Operasional
                        </label>
                        <select name="is_active" class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 transition">
                            <option value="1" {{ $location->is_active ? 'selected' : '' }}>✅ Aktif Beroperasi</option>
                            <option value="0" {{ !$location->is_active ? 'selected' : '' }}>❌ Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                            Alamat Lengkap
                        </label>
                        <textarea name="address" rows="2"
                                  class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 transition resize-none">{{ $location->address }}</textarea>
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

    {{-- ===== MODAL: PENYESUAIAN STOK (ADJUSTMENT) ===== --}}
    @if(\App\Support\Context::hasPermission('inventory.manage'))
    <div x-show="showAdjustModal"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-xs p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display:none;">
        <div class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl p-6 space-y-4"
             @click.outside="showAdjustModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center">
                        <i data-lucide="sliders" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-slate-900 dark:text-white">Sesuaikan Stok Fisik</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400" x-text="selectedStock ? selectedStock.product_name : ''"></p>
                    </div>
                </div>
                <button @click="showAdjustModal = false" class="p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 dark:hover:text-white transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('inventory.stocks.adjust') }}" method="POST" class="space-y-3.5 text-xs sm:text-sm">
                @csrf
                <input type="hidden" name="product_id" x-bind:value="selectedStock?.product_id">
                <input type="hidden" name="location_id" value="{{ $location->id }}">

                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                        Stok Tercatat Saat Ini (Sistem)
                    </label>
                    <div class="px-3.5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white font-mono font-black text-sm flex items-center justify-between">
                        <span x-text="selectedStock?.quantity ?? 0"></span>
                        <span class="text-xs text-slate-400 font-sans">Unit</span>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                        Kuantitas Baru Riil (Hasil Fisik) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="new_quantity" step="any" x-model="newQuantity" min="0" required
                           class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white font-mono text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                </div>

                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                        HPP / Biaya Satuan Terakhir (Opsional)
                    </label>
                    <input type="number" name="unit_cost" step="any" x-model="unitCost" min="0"
                           class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white font-mono text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                </div>

                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                        Keterangan Penyesuaian
                    </label>
                    <input type="text" name="notes" placeholder="Contoh: Selisih fisik stock opname, barang rusak..."
                           class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="showAdjustModal = false"
                            class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition shadow-sm hover:shadow">
                        Simpan Penyesuaian
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection
