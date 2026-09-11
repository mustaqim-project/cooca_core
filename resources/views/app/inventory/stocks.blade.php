@extends('layouts.app', [
    'title' => 'Inventori & Stok Multi-Gudang',
    'headerTitle' => 'Inventori & Stok Multi-Gudang',
    'headerSubtitle' => 'Pantau stok produk di seluruh cabang/outlet & gudang secara real-time dengan sistem kartu stok'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
    showAdjustModal: false,
    selectedStock: null,
    newQuantity: 0,
    unitCost: 0,
    openAdjust(stockId) {
        const stock = (window.COOCA_STOCKS || []).find(s => s.id === stockId);
        if (!stock) return;
        this.selectedStock = stock;
        this.newQuantity = Number(stock.quantity);
        this.unitCost = Number(stock.last_cost || (stock.product ? stock.product.base_cost : 0) || 0);
        this.showAdjustModal = true;
    }
}">
    <script>
        window.COOCA_STOCKS = @json($stocks->items());
    </script>
    
    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <!-- Breadcrumb minimal -->
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <span class="text-black/70 dark:text-white/70 font-medium">Inventori</span>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Saldo Stok Real-Time</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Inventori &amp; Stok Real-Time</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Pantau stok produk di seluruh cabang &amp; gudang secara terpusat</p>
        </div>

        <!-- Quick Navigation Toolbar Actions -->
        <div class="flex items-center gap-2 flex-wrap w-full sm:w-auto">
            <a href="{{ route('import.index', ['tab' => 'inventory']) }}" class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span>Import Stok</span>
            </a>

            <a href="{{ route('inventory.movements') }}" class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Kartu Stok</span>
            </a>

            <a href="{{ route('inventory.opnames.index') }}" class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-[#FF9500]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                </svg>
                <span>Stock Opname</span>
            </a>

            <a href="{{ route('inventory.transfers.index') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                </svg>
                <span>Transfer Stok</span>
            </a>
        </div>
    </header>

    <!-- Session Feedback Banner -->
    @if(session('success'))
    <div class="rounded-[12px] bg-[#34C759]/10 border border-[#34C759]/20 px-4 py-3 text-[13px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-2.5">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 2. KPI SUMMARY ROW (Flat Neutral Material)            -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        <!-- Tile 1: Nilai Aset (System Green for Valuation) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Nilai Aset Inventori</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">HPP</span>
            </div>
            <div class="mt-3">
                <div class="text-[24px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">
                    Rp {{ number_format($totalValuation, 0, ',', '.') }}
                </div>
                <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Berdasarkan HPP / Biaya Pokok terakhir</p>
            </div>
        </div>

        <!-- Tile 2: Peringatan Stok Minimum (System Orange Warning) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Peringatan Stok Minimum</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $lowStockCount > 0 ? 'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]' : 'bg-black/6 dark:bg-white/8 text-black/50 dark:text-white/50' }}">
                    {{ $lowStockCount > 0 ? 'Perlu Restock' : 'Aman' }}
                </span>
            </div>
            <div class="mt-3">
                <div class="text-[24px] font-bold tabular-nums {{ $lowStockCount > 0 ? 'text-[#FF9500] dark:text-[#FF9F0A]' : 'text-black dark:text-white' }}">
                    {{ $lowStockCount }} Produk
                </div>
                <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Kuantitas di bawah batas stok minimum</p>
            </div>
        </div>

        <!-- Tile 3: Total Cabang / Gudang (Neutral) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Cabang / Outlet</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/12 text-[#007AFF]">Lokasi</span>
            </div>
            <div class="mt-3">
                <div class="text-[24px] font-bold tabular-nums text-black dark:text-white">
                    {{ $locations->count() }} Lokasi
                </div>
                <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Outlet penjualan &amp; gudang penyimpanan</p>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. FILTER BAR (macOS Style Toolbar Controls)          -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3.5 sm:p-4">
        <form method="GET" action="{{ route('inventory.stocks') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 sm:gap-3 items-center">
            <!-- Search Field (macOS Capsule) -->
            <div class="sm:col-span-6 relative">
                <svg class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama produk / kode SKU..."
                    class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-9 pr-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>

            <!-- Location Dropdown -->
            <div class="sm:col-span-4">
                <select name="location_id" class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    <option value="">Semua Lokasi / Outlet</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('location_id') === $loc->id ? 'selected' : '' }}>
                            {{ $loc->name }} ({{ strtoupper($loc->type ?? 'Outlet') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Actions -->
            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="flex-1 h-9 px-3 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-semibold active:scale-[0.97] transition-all flex items-center justify-center">
                    Filter
                </button>
                <a href="{{ route('inventory.stocks') }}" class="h-9 px-3 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.97] transition-all flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- ===================================================== -->
    <!-- 4. DENSE DATA TABLE (DESKTOP & TABLET)                 -->
    <!-- ===================================================== -->
    <div class="hidden sm:block rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10 whitespace-nowrap">
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Produk &amp; SKU</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Lokasi / Outlet</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Stok Fisik</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Stok Min.</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">HPP / Unit</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Total Nilai</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Status</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($stocks as $st)
                    @php 
                        $isLow = $st->quantity <= ($st->product->min_stock ?? 0); 
                        $val = $st->quantity * ($st->last_cost > 0 ? $st->last_cost : ($st->product->base_cost ?? 0));
                    @endphp
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="py-3 px-4">
                            <div class="font-medium text-black dark:text-white">{{ $st->product->name ?? 'Produk' }}</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums">{{ $st->product->code ?? '-' }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-black/5 dark:bg-white/5 text-black/70 dark:text-white/70">
                                {{ $st->location->name ?? 'Outlet Utama' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums font-semibold text-black dark:text-white text-[14px]">
                            {{ rtrim(rtrim((string)$st->quantity, '0'), '.') }} <span class="text-[12px] font-normal text-black/50 dark:text-white/50">{{ $st->product->outputUnit->code ?? '' }}</span>
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums text-black/50 dark:text-white/50">
                            {{ $st->product->min_stock ?? 0 }}
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums text-black/70 dark:text-white/70">
                            Rp {{ number_format($st->last_cost > 0 ? $st->last_cost : ($st->product->base_cost ?? 0), 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums font-semibold text-[#34C759] dark:text-[#30D158]">
                            Rp {{ number_format($val, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($st->quantity <= 0)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span> Habis
                                </span>
                            @elseif($isLow)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span> Menipis
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Aman
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right">
                            <button type="button" @click="openAdjust('{{ $st->id }}')" class="h-7 px-2.5 rounded-[6px] text-[12px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 active:scale-[0.97] transition-all">
                                Sesuaikan
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-black/40 dark:text-white/40">Belum ada data stok tercatat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($stocks->hasPages())
        <div class="px-4 py-3 border-t border-black/5 dark:border-white/10">
            {{ $stocks->links() }}
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 5. MOBILE GROUPED INSET LIST (Standar iOS List View)   -->
    <!-- ===================================================== -->
    <div class="sm:hidden rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
        @forelse($stocks as $st)
        @php 
            $isLow = $st->quantity <= ($st->product->min_stock ?? 0); 
            $val = $st->quantity * ($st->last_cost > 0 ? $st->last_cost : ($st->product->base_cost ?? 0));
        @endphp
        <div class="p-3.5 flex items-center justify-between gap-3 active:bg-black/[0.02] dark:active:bg-white/[0.04] transition-colors">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5">
                    <p class="text-[15px] font-medium text-black dark:text-white truncate">{{ $st->product->name ?? 'Produk' }}</p>
                    @if($st->quantity <= 0)
                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30] shrink-0"></span>
                    @elseif($isLow)
                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500] shrink-0"></span>
                    @else
                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] shrink-0"></span>
                    @endif
                </div>
                <p class="text-[13px] text-black/45 dark:text-white/45 mt-0.5 tabular-nums">
                    {{ rtrim(rtrim((string)$st->quantity, '0'), '.') }} {{ $st->product->outputUnit->code ?? '' }} · {{ $st->location->name ?? 'Outlet Utama' }}
                </p>
                <p class="text-[11px] text-[#34C759] dark:text-[#30D158] mt-0.5 tabular-nums font-medium">
                    Nilai: Rp {{ number_format($val, 0, ',', '.') }}
                </p>
            </div>
            <div class="shrink-0">
                <button type="button" @click="openAdjust('{{ $st->id }}')" class="h-8 px-3 rounded-[8px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 active:scale-[0.97] transition-all">
                    Sesuaikan
                </button>
            </div>
        </div>
        @empty
        <div class="py-8 text-center text-black/40 dark:text-white/40 text-[13px]">Belum ada data stok tercatat.</div>
        @endforelse

        @if($stocks->hasPages())
        <div class="p-3 border-t border-black/5 dark:border-white/10">
            {{ $stocks->links() }}
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 6. APPLE SHEET: PENYESUAIAN STOK CEPAT                -->
    <!-- ===================================================== -->
    <div x-show="showAdjustModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/25 backdrop-blur-[2px] p-0 sm:p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-full sm:max-w-md rounded-t-[20px] sm:rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)]"
            @click.away="showAdjustModal = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0"
            x-transition:enter-end="translate-y-0 sm:scale-100 sm:opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-y-0 sm:scale-100 sm:opacity-100"
            x-transition:leave-end="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0">

            <!-- Mobile Grabber Handle -->
            <div class="sm:hidden w-10 h-1 rounded-full bg-black/20 dark:bg-white/20 mx-auto mb-2"></div>

            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                <h3 class="text-[17px] font-semibold text-black dark:text-white">Penyesuaian Stok Cepat</h3>
                <button type="button" @click="showAdjustModal = false" class="text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('inventory.stocks.adjust') }}" method="POST" class="space-y-4 text-[13px]">
                @csrf
                <input type="hidden" name="location_id" :value="selectedStock ? selectedStock.location_id : ''">
                <input type="hidden" name="product_id" :value="selectedStock ? selectedStock.product_id : ''">

                <div class="p-3.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06]">
                    <div class="font-semibold text-black dark:text-white text-[14px]" x-text="selectedStock ? selectedStock.product.name : ''"></div>
                    <div class="text-[12px] text-black/50 dark:text-white/50 mt-0.5" x-text="selectedStock ? selectedStock.location.name : ''"></div>
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Jumlah Stok Baru *</label>
                    <input type="number" step="any" name="new_quantity" x-model.number="newQuantity" required
                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Biaya Pokok (HPP) per Unit (Rp)</label>
                    <input type="number" name="unit_cost" x-model.number="unitCost"
                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Alasan Penyesuaian</label>
                    <input type="text" name="notes" placeholder="Misal: Koreksi saldo awal, barang rusak..."
                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showAdjustModal = false" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.97] transition-all">
                        Batal
                    </button>
                    <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
