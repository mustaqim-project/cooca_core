@extends('layouts.app', [
    'title' => 'Detail Gudang - ' . $location->name,
    'headerTitle' => 'Detail Gudang: ' . $location->name,
    'headerSubtitle' => 'Pantau stok aktual, riwayat penerimaan barang dari PO, dan mutasi kartu stok lokasi ini.',
])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="{
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
        {{-- ===================================================== --}}
        {{-- 1. TOOLBAR / PAGE HEADER                                --}}
        {{-- ===================================================== --}}
        <x-module-header
            title="{{ $location->name }}"
            subtitle="{{ $location->address ?: 'Belum ada alamat terdaftar' }}"
            badge="{{ $location->type === 'warehouse' ? 'Gudang' : ($location->type === 'central_kitchen' ? 'Dapur Pusat' : 'Outlet') }}"
            :breadcrumbs="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Inventori', 'url' => route('inventory.stocks')],
                ['label' => 'Lokasi Gudang', 'url' => route('warehouse.index')],
                ['label' => $location->name, 'url' => null],
            ]">
            <a href="{{ route('warehouse.index') }}"
                class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="arrow-left" class="w-4 h-4 text-black/50 dark:text-white/50"></i>
                <span>Kembali</span>
            </a>

            @if (\App\Support\Context::hasPermission('inventory.manage'))
                <button type="button" @click="showEditModal = true"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                    <i data-lucide="pencil" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Edit Gudang</span>
                </button>
            @endif

            @if (\App\Support\Context::hasPermission('receiving.manage'))
                <a href="{{ route('purchase-orders.index') }}?po_type=supplier"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Terima Barang dari PO</span>
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
        @if (session('error'))
            <div class="rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 px-4 py-3 text-xs text-[#C41E17] dark:text-[#FF453A] flex items-center gap-2.5">
                <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0 text-[#FF3B30]"></i>
                <span class="font-medium">{{ session('error') }}</span>
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
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 uppercase font-bold tracking-wider block">Alamat Fisik</span>
                        <span class="text-slate-800 dark:text-slate-200 font-medium leading-relaxed">
                            {{ $location->address ?: 'Belum ada alamat terdaftar' }}
                        </span>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-[10px] bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0 text-slate-500 dark:text-slate-400">
                        <i data-lucide="phone" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 uppercase font-bold tracking-wider block">Kontak / Telepon</span>
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
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 uppercase font-bold tracking-wider block">Terdaftar Sejak</span>
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
            {{-- KPI 1: Total Produk --}}
            <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Produk</span>
                    <div class="w-7 h-7 rounded-[8px] bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400">
                        <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl font-black tabular-nums text-slate-900 dark:text-white">{{ $stocks->total() }}</span>
                    <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">Item Fisik</span>
                </div>
            </div>

            {{-- KPI 2: Total Nilai Aset --}}
            <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Nilai Aset Stok</span>
                    <div class="w-7 h-7 rounded-[8px] bg-[#34C759]/10 flex items-center justify-center text-[#34C759]">
                        <i data-lucide="badge-dollar-sign" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-xl sm:text-2xl font-black tabular-nums text-[#34C759] dark:text-[#30D158] truncate">
                        Rp {{ number_format($totalValuation, 0, ',', '.') }}
                    </span>
                    <span class="text-[11px] font-semibold text-[#34C759] dark:text-[#30D158]">HPP</span>
                </div>
            </div>

            {{-- KPI 3: Stok Minimum --}}
            <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Stok Menipis</span>
                    <div class="w-7 h-7 rounded-[8px] {{ $lowStockCount > 0 ? 'bg-[#FF9500]/10 text-[#FF9500]' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }} flex items-center justify-center">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl font-black tabular-nums {{ $lowStockCount > 0 ? 'text-[#FF9500] dark:text-[#FF9F0A]' : 'text-slate-900 dark:text-white' }}">
                        {{ $lowStockCount }}
                    </span>
                    <span class="text-[11px] {{ $lowStockCount > 0 ? 'text-[#FF9500] dark:text-[#FF9F0A] font-bold' : 'text-slate-400 dark:text-slate-500 font-medium' }}">
                        {{ $lowStockCount > 0 ? 'Perlu Restock' : 'Batas Aman' }}
                    </span>
                </div>
            </div>

            {{-- KPI 4: Penerimaan Barang PO --}}
            <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Penerimaan PO</span>
                    <div class="w-7 h-7 rounded-[8px] bg-[#5856D6]/10 flex items-center justify-center text-[#5856D6]">
                        <i data-lucide="file-check" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl font-black tabular-nums text-[#5856D6] dark:text-[#5E5CE6]">{{ $receipts->count() }}</span>
                    <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">Dokumen GR</span>
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
                        Terima dari PO Supplier
                    </h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Buka PO confirmed &rarr; klik Terima Barang</p>
                </div>
            </a>

            <a href="{{ route('inventory.transfers.index') }}"
                class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:shadow-md active:scale-[0.98] transition-all flex items-center gap-3.5 group shadow-xs">
                <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0 border border-[#007AFF]/20">
                    <i data-lucide="arrow-left-right" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Transfer Stok Antar Lokasi
                    </h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Kirim stok ke cabang / outlet lain</p>
                </div>
            </a>

            <a href="{{ route('inventory.opnames.index') }}"
                class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:shadow-md active:scale-[0.98] transition-all flex items-center gap-3.5 group shadow-xs">
                <div class="w-10 h-10 rounded-[12px] bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center shrink-0 border border-[#AF52DE]/20">
                    <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Stock Opname Fisik
                    </h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Rekonsiliasi selisih stok buku vs riil</p>
                </div>
            </a>
        </div>

        {{-- ===================================================== --}}
        {{-- 6. STOK PRODUK DI GUDANG INI (Apple Dense Table)     --}}
        {{-- ===================================================== --}}
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden shadow-xs">
            <div class="p-4 sm:p-5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">Daftar Stok Produk di Lokasi Ini</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Kuantitas aktual fisik dan estimasi nilai persediaan berdasarkan HPP</p>
                </div>

                <div class="flex items-center gap-2.5">
                    {{-- Search Field --}}
                    <div class="relative w-48 sm:w-64">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                        <input type="text" x-model="searchStock" placeholder="Cari nama atau kode..."
                            class="w-full h-9 pl-8.5 pr-3 bg-slate-50 dark:bg-[#2C2C2E] border border-black/[0.06] dark:border-white/[0.08] rounded-[10px] text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                    </div>

                    <a href="{{ route('inventory.stocks') }}?location_id={{ $location->id }}"
                        class="text-xs text-[#007AFF] hover:underline font-bold inline-flex items-center gap-1">
                        <span>Semua Stok</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>

            {{-- Stocks Table (Desktop) --}}
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-black/[0.06] dark:border-white/[0.08] text-[11px] font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500 bg-slate-50/50 dark:bg-[#2C2C2E]/50">
                            <th class="px-4 py-3">Produk &amp; SKU</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3 text-right">Stok Aktual</th>
                            <th class="px-4 py-3 text-right">Min. Stok</th>
                            <th class="px-4 py-3 text-right">HPP / Unit</th>
                            <th class="px-4 py-3 text-right">Total Nilai</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            @if (\App\Support\Context::hasPermission('inventory.manage'))
                                <th class="px-4 py-3 text-right">Aksi</th>
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
                                        {{ $stock->product?->name ?? '-' }}</div>
                                    @if ($stock->product?->code)
                                        <div class="text-[11px] font-mono tabular-nums text-slate-500 dark:text-slate-400">
                                            {{ $stock->product->code }}</div>
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
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span> Hampir Habis
                                        </span>
                                    @elseif($stock->quantity <= 0)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span> Habis
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Tersedia
                                        </span>
                                    @endif
                                </td>
                                @if (\App\Support\Context::hasPermission('inventory.manage'))
                                    <td class="px-4 py-3.5 text-right">
                                        <button type="button"
                                            @click="openAdjust({
                                                id: '{{ $stock->id }}',
                                                product_id: '{{ $stock->product_id }}',
                                                location_id: '{{ $stock->location_id }}',
                                                product_name: '{{ addslashes($stock->product?->name ?? '') }}',
                                                quantity: '{{ $stock->quantity }}',
                                                last_cost: '{{ $stock->last_cost }}'
                                            })"
                                            class="h-7 px-2.5 rounded-[6px] text-xs font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.98] transition-all cursor-pointer">
                                            Sesuaikan
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
                                    <div class="font-bold text-slate-900 dark:text-white text-sm">Belum ada stok fisik di gudang ini</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                                        Lakukan penerimaan barang dari Purchase Order Supplier atau transfer stok dari cabang lain.
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
                                    {{ $stock->product?->name ?? '-' }}</h4>
                                <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    @if ($stock->product?->code)
                                        <span class="font-mono tabular-nums">{{ $stock->product->code }}</span>
                                        <span>&bull;</span>
                                    @endif
                                    <span>{{ $stock->product?->category?->name ?? 'Tanpa Kategori' }}</span>
                                </div>
                            </div>
                            <div>
                                @if ($isLow)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span> Hampir Habis
                                    </span>
                                @elseif($stock->quantity <= 0)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span> Habis
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Tersedia
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs bg-slate-50 dark:bg-[#2C2C2E] p-3 rounded-[12px] border border-black/[0.04] dark:border-white/[0.04]">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">Stok Aktual</span>
                                <span class="tabular-nums font-black text-sm {{ $isLow ? 'text-[#FF9500] dark:text-[#FF9F0A]' : 'text-slate-900 dark:text-white' }}">
                                    {{ number_format($stock->quantity, 2) }}
                                </span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500">{{ $stock->product?->outputUnit?->symbol ?? '' }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">Min. Stok</span>
                                <span class="tabular-nums text-slate-700 dark:text-slate-300 font-semibold">
                                    {{ $stock->product ? number_format($stock->product->min_stock, 2) : '-' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">HPP / Unit</span>
                                <span class="tabular-nums text-slate-600 dark:text-slate-400 font-semibold">
                                    Rp {{ number_format($stock->last_cost, 0, ',', '.') }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">Total Nilai</span>
                                <span class="tabular-nums font-bold text-[#34C759] dark:text-[#30D158]">
                                    Rp {{ number_format($valuation, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        @if (\App\Support\Context::hasPermission('inventory.manage'))
                            <div class="flex items-center justify-end pt-1">
                                <button type="button"
                                    @click="openAdjust({
                                        id: '{{ $stock->id }}',
                                        product_id: '{{ $stock->product_id }}',
                                        location_id: '{{ $stock->location_id }}',
                                        product_name: '{{ addslashes($stock->product?->name ?? '') }}',
                                        quantity: '{{ $stock->quantity }}',
                                        last_cost: '{{ $stock->last_cost }}'
                                    })"
                                    class="h-8 px-3.5 rounded-[8px] text-xs font-bold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.98] transition-all inline-flex items-center gap-1.5 cursor-pointer">
                                    <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
                                    <span>Sesuaikan Stok Fisik</span>
                                </button>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="py-10 px-4 text-center text-slate-500 dark:text-slate-400">
                        <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-2 text-slate-400">
                            <i data-lucide="package" class="w-5 h-5"></i>
                        </div>
                        <div class="font-bold text-slate-900 dark:text-white text-xs">Belum ada stok fisik di gudang ini</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-xs mx-auto">
                            Lakukan penerimaan barang dari Purchase Order Supplier atau transfer stok dari cabang lain.
                        </div>
                    </div>
                @endforelse
            </div>
            @if ($stocks->hasPages())
                <div class="p-3.5 border-t border-black/[0.06] dark:border-white/[0.08] text-xs">{{ $stocks->links() }}</div>
            @endif
        </div>

        {{-- ===================================================== --}}
        {{-- 7. RIWAYAT PENERIMAAN BARANG (GOODS RECEIPTS)         --}}
        {{-- ===================================================== --}}
        @if ($receipts->isNotEmpty())
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden shadow-xs">
                <div class="px-4 sm:px-5 py-4 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">Riwayat Penerimaan Barang (Goods Receipt)</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Daftar inbound barang yang telah diverifikasi masuk ke lokasi ini</p>
                </div>
                {{-- Desktop GR Table --}}
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-black/[0.06] dark:border-white/[0.08] text-[11px] font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500 bg-slate-50/50 dark:bg-[#2C2C2E]/50">
                                <th class="px-4 py-3">No. Penerimaan</th>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Supplier</th>
                                <th class="px-4 py-3">No. PO</th>
                                <th class="px-4 py-3 text-center">Item</th>
                                <th class="px-4 py-3">Diterima Oleh</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                            @foreach ($receipts as $gr)
                                <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="px-4 py-3.5 font-mono tabular-nums font-bold text-slate-900 dark:text-white">
                                        #{{ $gr->receipt_number }}
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-600 dark:text-slate-400">
                                        {{ $gr->receipt_date?->format('d/m/Y') ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3.5 font-semibold text-slate-900 dark:text-white">
                                        {{ $gr->supplier?->name ?? 'Tanpa Supplier' }}
                                    </td>
                                    <td class="px-4 py-3.5">
                                        @if ($gr->purchaseOrder)
                                            <a href="{{ route('purchase-orders.show', $gr->purchaseOrder->id) }}"
                                                class="font-mono tabular-nums text-[#007AFF] hover:underline font-bold">
                                                {{ $gr->purchaseOrder->po_number }}
                                            </a>
                                        @else
                                            <span class="text-slate-400 dark:text-slate-500 font-medium">1-Klik (Solo Mode)</span>
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
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Diterima
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile GR Cards --}}
                <div class="sm:hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @foreach ($receipts as $gr)
                        <div class="p-4 space-y-2.5 active:bg-black/[0.02] dark:active:bg-white/[0.02] transition-colors">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="font-mono tabular-nums font-bold text-xs text-slate-900 dark:text-white">#{{ $gr->receipt_number }}</span>
                                    <div class="font-bold text-xs text-slate-800 dark:text-slate-200 mt-0.5">
                                        {{ $gr->supplier?->name ?? 'Tanpa Supplier' }}</div>
                                </div>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Diterima
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-xs bg-slate-50 dark:bg-[#2C2C2E] p-2.5 rounded-[10px]">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">Tgl. Terima</span>
                                    <span class="text-slate-700 dark:text-slate-300">{{ $gr->receipt_date?->format('d/m/Y') ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">No. PO</span>
                                    @if ($gr->purchaseOrder)
                                        <a href="{{ route('purchase-orders.show', $gr->purchaseOrder->id) }}"
                                            class="font-mono tabular-nums text-[#007AFF] font-bold">
                                            {{ $gr->purchaseOrder->po_number }}
                                        </a>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500">1-Klik</span>
                                    @endif
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">Jumlah Item</span>
                                    <span class="tabular-nums font-bold text-slate-900 dark:text-white">{{ $gr->items->count() }} item</span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">Penerima</span>
                                    <span class="text-slate-700 dark:text-slate-300 truncate block">{{ $gr->receiver?->name ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ===================================================== --}}
        {{-- 8. KARTU STOK - MUTASI TERKINI                       --}}
        {{-- ===================================================== --}}
        @if ($recentMovements->isNotEmpty())
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden shadow-xs">
                <div class="px-4 sm:px-5 py-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">Kartu Stok - Mutasi Terkini</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Audit trail pergerakan saldo barang di lokasi ini</p>
                    </div>
                    <a href="{{ route('inventory.movements') }}?location_id={{ $location->id }}"
                        class="text-xs font-bold text-[#007AFF] hover:underline flex items-center gap-1">
                        <span>Lihat Semua Mutasi</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
                {{-- Desktop Movements Table --}}
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-black/[0.06] dark:border-white/[0.08] text-[11px] font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500 bg-slate-50/50 dark:bg-[#2C2C2E]/50">
                                <th class="px-4 py-3">Produk</th>
                                <th class="px-4 py-3">Tipe Mutasi</th>
                                <th class="px-4 py-3">No. Referensi</th>
                                <th class="px-4 py-3 text-right">Perubahan Qty</th>
                                <th class="px-4 py-3 text-right">Saldo Akhir</th>
                                <th class="px-4 py-3">Operator</th>
                                <th class="px-4 py-3 text-right">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                            @foreach ($recentMovements as $mv)
                                @php
                                    $mvLabels = [
                                        'goods_receipt' => ['label' => 'Penerimaan PO', 'pill' => 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]'],
                                        'pos_sale' => ['label' => 'Penjualan POS', 'pill' => 'bg-[#007AFF]/12 text-[#007AFF]'],
                                        'adjustment' => ['label' => 'Penyesuaian Manual', 'pill' => 'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]'],
                                        'transfer_in' => ['label' => 'Transfer Masuk', 'pill' => 'bg-[#5856D6]/12 text-[#413FA6] dark:text-[#5E5CE6]'],
                                        'transfer_out' => ['label' => 'Transfer Keluar', 'pill' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'],
                                        'opname' => ['label' => 'Rekonsiliasi Opname', 'pill' => 'bg-[#AF52DE]/12 text-[#7C3AA6] dark:text-[#BF5AF2]'],
                                        'initial' => ['label' => 'Stok Awal', 'pill' => 'bg-[#007AFF]/12 text-[#007AFF]'],
                                        'pos_refund' => ['label' => 'Refund POS', 'pill' => 'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]'],
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
                                        {{ $mv->creator?->name ?? 'Sistem' }}
                                    </td>
                                    <td class="px-4 py-3.5 text-right text-[11px] text-slate-400 dark:text-slate-500 whitespace-nowrap font-medium">
                                        {{ $mv->created_at->format('d/m/Y H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Movements Cards --}}
                <div class="sm:hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @foreach ($recentMovements as $mv)
                        @php
                            $mvLabels = [
                                'goods_receipt' => ['label' => 'Penerimaan PO', 'pill' => 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]'],
                                'pos_sale' => ['label' => 'Penjualan POS', 'pill' => 'bg-[#007AFF]/12 text-[#007AFF]'],
                                'adjustment' => ['label' => 'Penyesuaian Manual', 'pill' => 'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]'],
                                'transfer_in' => ['label' => 'Transfer Masuk', 'pill' => 'bg-[#5856D6]/12 text-[#413FA6] dark:text-[#5E5CE6]'],
                                'transfer_out' => ['label' => 'Transfer Keluar', 'pill' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'],
                                'opname' => ['label' => 'Rekonsiliasi Opname', 'pill' => 'bg-[#AF52DE]/12 text-[#7C3AA6] dark:text-[#BF5AF2]'],
                                'initial' => ['label' => 'Stok Awal', 'pill' => 'bg-[#007AFF]/12 text-[#007AFF]'],
                                'pos_refund' => ['label' => 'Refund POS', 'pill' => 'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]'],
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
                                    <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">Perubahan Qty</span>
                                    <span class="tabular-nums font-bold {{ $mv->quantity_change >= 0 ? 'text-[#34C759] dark:text-[#30D158]' : 'text-[#FF3B30] dark:text-[#FF453A]' }}">
                                        {{ $mv->quantity_change >= 0 ? '+' : '' }}{{ number_format($mv->quantity_change, 2) }} {{ $mv->product?->outputUnit?->symbol }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">Saldo Akhir</span>
                                    <span class="tabular-nums font-bold text-slate-900 dark:text-white">
                                        {{ number_format($mv->balance_after, 2) }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">No. Ref</span>
                                    <span class="font-mono text-[11px] text-slate-600 dark:text-slate-400 truncate block">{{ $mv->reference_number ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">Waktu</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400">{{ $mv->created_at->format('d/m/y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ===================================================== --}}
        {{-- 9. APPLE BENTO XXL SHEET: EDIT GUDANG / LOKASI        --}}
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
                                <span>Edit Gudang / Lokasi: {{ $location->name }}</span>
                                <span class="text-[11px] font-semibold text-[#007AFF] bg-[#007AFF]/12 px-2.5 py-0.5 rounded-full">Perbarui Data</span>
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Perbarui informasi alamat fisik, tipe lokasi, dan status operasional</p>
                        </div>
                    </div>
                    <button type="button" @click="showEditModal = false"
                        class="w-9 h-9 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form action="{{ route('warehouse.update', $location->id) }}" method="POST" class="flex flex-col flex-1 overflow-hidden">
                    @csrf
                    @method('PUT')
                    
                    <div class="p-5 sm:p-6 overflow-y-auto space-y-4 flex-1 text-xs">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                            
                            {{-- Kolom Kiri (6 Kolom) --}}
                            <div class="lg:col-span-6 space-y-4">
                                <div class="rounded-[18px] bg-slate-50/80 dark:bg-[#2C2C2E]/60 border border-black/[0.06] dark:border-white/[0.08] p-5 space-y-4">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="info" class="w-4 h-4 text-[#007AFF]"></i>
                                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Identitas Lokasi</span>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                            Nama Gudang / Lokasi <span class="text-[#FF3B30]">*</span>
                                        </label>
                                        <input type="text" name="name" value="{{ $location->name }}" required
                                            class="w-full h-11 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                Tipe Lokasi
                                            </label>
                                            <select name="type"
                                                class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                                <option value="warehouse" {{ $location->type === 'warehouse' ? 'selected' : '' }}>Gudang (Warehouse)</option>
                                                <option value="outlet" {{ $location->type === 'outlet' ? 'selected' : '' }}>Outlet / Toko</option>
                                                <option value="central_kitchen" {{ $location->type === 'central_kitchen' ? 'selected' : '' }}>Dapur Pusat (Central Kitchen)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                Kode Lokasi
                                            </label>
                                            <input type="text" name="code" value="{{ $location->code }}"
                                                class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            Nomor Telepon
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
                                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Alamat &amp; Status Operasional</span>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            Alamat Lengkap
                                        </label>
                                        <textarea name="address" rows="3"
                                            class="w-full bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] p-3 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition resize-none">{{ $location->address }}</textarea>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            Status Operasional
                                        </label>
                                        <select name="is_active"
                                            class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                            <option value="1" {{ $location->is_active ? 'selected' : '' }}>Aktif Beroperasi (Menerima Transaksi)</option>
                                            <option value="0" {{ !$location->is_active ? 'selected' : '' }}>Nonaktif (Ditutup Sementara)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="px-5 py-3.5 sm:px-6 sm:py-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-end gap-3 shrink-0 bg-slate-50/50 dark:bg-white/[0.02]">
                        <button type="button" @click="showEditModal = false"
                            class="min-h-[44px] h-11 px-5 rounded-[12px] text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                            class="min-h-[44px] h-11 px-6 rounded-[12px] text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all shadow-[0_2px_4px_rgba(0,122,255,0.25)] cursor-pointer flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- 10. APPLE BENTO XXL SHEET: PENYESUAIAN STOK (ADJUST)  --}}
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
                                    <span>Penyesuaian Stok Fisik (Stock Adjustment)</span>
                                    <span class="text-[11px] font-semibold text-[#007AFF] bg-[#007AFF]/12 px-2.5 py-0.5 rounded-full" x-text="selectedStock ? selectedStock.product_name : ''"></span>
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Sinkronkan saldo stok sistem dengan hasil penghitungan fisik riil di gudang ini</p>
                            </div>
                        </div>
                        <button type="button" @click="showAdjustModal = false"
                            class="w-9 h-9 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition cursor-pointer">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <form action="{{ route('inventory.stocks.adjust') }}" method="POST" class="flex flex-col flex-1 overflow-hidden">
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
                                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Input Kuantitas Fisik</span>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                Kuantitas Baru Riil (Hasil Fisik) <span class="text-[#FF3B30]">*</span>
                                            </label>
                                            <input type="number" name="new_quantity" step="any" x-model="newQuantity" min="0" required
                                                class="w-full h-11 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono tabular-nums font-bold text-sm focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                HPP / Biaya Satuan Terakhir (Opsional)
                                            </label>
                                            <input type="number" name="unit_cost" step="any" x-model="unitCost" min="0"
                                                class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                Keterangan / Alasan Penyesuaian
                                            </label>
                                            <input type="text" name="notes"
                                                placeholder="Contoh: Selisih fisik stock opname, barang rusak/kadaluarsa..."
                                                class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                        </div>
                                    </div>
                                </div>

                                {{-- Kolom Kanan: Bento Live Comparison & Impact Calculation (6 Kolom) --}}
                                <div class="lg:col-span-6 space-y-4">
                                    <div class="rounded-[18px] bg-slate-50 dark:bg-[#2C2C2E] border border-black/[0.06] dark:border-white/[0.08] p-5 space-y-4">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="calculator" class="w-4 h-4 text-[#007AFF]"></i>
                                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Analisis Dampak Mutasi</span>
                                        </div>

                                        <div class="grid grid-cols-2 gap-3">
                                            <div class="p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Stok Sistem</span>
                                                <span class="text-sm font-bold text-slate-900 dark:text-white tabular-nums font-mono">
                                                    <span x-text="Number(selectedStock?.quantity || 0).toLocaleString('id-ID')"></span>
                                                </span>
                                            </div>
                                            <div class="p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Kuantitas Baru</span>
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
                                                <span class="font-semibold text-xs">Selisih Mutasi Stok:</span>
                                                <span class="font-bold text-sm font-mono tabular-nums">
                                                    <span x-text="(Number(newQuantity) - Number(selectedStock?.quantity || 0)) > 0 ? '+' : ''"></span>
                                                    <span x-text="(Number(newQuantity) - Number(selectedStock?.quantity || 0)).toLocaleString('id-ID')"></span> Unit
                                                </span>
                                            </div>
                                            <div class="flex items-center justify-between pt-2 mt-2 border-t border-black/10 dark:border-white/10 text-[11px]">
                                                <span>Estimasi Perubahan Valuasi:</span>
                                                <span class="font-bold font-mono tabular-nums">
                                                    Rp <span x-text="Math.abs((Number(newQuantity) - Number(selectedStock?.quantity || 0)) * Number(unitCost || 0)).toLocaleString('id-ID')"></span>
                                                </span>
                                            </div>
                                        </div>

                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                                            Penyesuaian stok akan dicatat otomatis ke dalam <strong>Audit Trail Kartu Stok (StockMovement)</strong> untuk akuntabilitas internal dan pencegahan fraud.
                                        </p>
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="px-5 py-3.5 sm:px-6 sm:py-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-end gap-3 shrink-0 bg-slate-50/50 dark:bg-white/[0.02]">
                            <button type="button" @click="showAdjustModal = false"
                                class="min-h-[44px] h-11 px-5 rounded-[12px] text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                class="min-h-[44px] h-11 px-6 rounded-[12px] text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all shadow-[0_2px_4px_rgba(0,122,255,0.25)] cursor-pointer flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                <span>Simpan Penyesuaian Stok</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

    </div>
@endsection
