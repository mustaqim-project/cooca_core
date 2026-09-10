@extends('layouts.app', [
    'title' => 'Pesanan Penjualan (Sales Orders) — Cooca UMKM',
    'headerTitle' => 'Pesanan Penjualan (Sales Orders)',
    'headerSubtitle' => 'Kelola pesanan pelanggan terkonfirmasi, alokasi stok pengiriman, dan terbitkan faktur penagihan (Invoice).'
])

@section('content')
@php
    $totalCount = $salesOrders->total();
    $confirmedOrders = $salesOrders->filter(fn($so) => $so->status === 'confirmed');
    $partialOrders = $salesOrders->filter(fn($so) => $so->status === 'partially_fulfilled');
    $fulfilledOrders = $salesOrders->filter(fn($so) => $so->status === 'fulfilled');
    $currentStatus = request('status', '');
    $currentSearch = request('search', '');
@endphp

<div class="max-w-[1360px] mx-auto space-y-6 pb-12">

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <span class="text-black/70 dark:text-white/70 font-medium">Penjualan</span>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Sales Orders</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Pesanan Penjualan (SO)</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Pipeline pesanan pelanggan terkonfirmasi, alokasi stok gudang, dan penerbitan faktur.</p>
        </div>

        <div class="flex items-center gap-2.5 w-full sm:w-auto">
            <a href="{{ route('sales.orders.create') }}"
                class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] w-full sm:w-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Buat Sales Order</span>
            </a>
        </div>
    </header>

    <!-- ===================================================== -->
    <!-- 2. KPI SUMMARY (Flat Neutral Material, Apple Standard) -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Tile 1: Total Orders (Neutral) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Sales Order</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-black dark:text-white">{{ number_format($totalCount, 0, ',', '.') }}</span>
                <span class="text-[11px] font-medium text-black/40 dark:text-white/40">Tercatat</span>
            </div>
        </div>

        <!-- Tile 2: Terkonfirmasi (System Blue) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Terkonfirmasi</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF]">{{ $confirmedOrders->count() }}</span>
                <span class="text-[11px] font-medium text-[#007AFF] dark:text-[#0A84FF]">Siap proses</span>
            </div>
        </div>

        <!-- Tile 3: Sebagian Terpenuhi (System Orange) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Sebagian Terpenuhi</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">{{ $partialOrders->count() }}</span>
                <span class="text-[11px] font-medium text-[#FF9500] dark:text-[#FF9F0A]">Parsial</span>
            </div>
        </div>

        <!-- Tile 4: Selesai / Fulfilled (System Green) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Selesai (Fulfilled)</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">{{ $fulfilledOrders->count() }}</span>
                <span class="text-[11px] font-medium text-[#34C759] dark:text-[#30D158]">Tuntas</span>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. CONTROLS: SEARCH & APPLE SEGMENTED CONTROL         -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 sm:p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <!-- Search Form (macOS Style: Clean Fill) -->
        <form method="GET" action="{{ route('sales.orders.index') }}" class="relative flex-1 max-w-md">
            @if($currentStatus)
                <input type="hidden" name="status" value="{{ $currentStatus }}">
            @endif
            <svg class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            <input type="text" name="search" value="{{ $currentSearch }}" placeholder="Cari nomor SO atau nama pelanggan..."
                class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-9 pr-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
        </form>

        <!-- Segmented Control Filters -->
        <div class="inline-flex p-0.5 rounded-[9px] bg-black/[0.06] dark:bg-white/[0.08] text-[13px] font-medium self-start sm:self-auto overflow-x-auto max-w-full">
            <a href="{{ route('sales.orders.index', ['search' => $currentSearch]) }}"
                class="px-3 py-1 rounded-[7px] transition-all whitespace-nowrap {{ empty($currentStatus) ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55' }}">
                Semua ({{ $totalCount }})
            </a>
            <a href="{{ route('sales.orders.index', ['status' => 'confirmed', 'search' => $currentSearch]) }}"
                class="px-3 py-1 rounded-[7px] transition-all whitespace-nowrap {{ $currentStatus === 'confirmed' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55' }}">
                Terkonfirmasi ({{ $confirmedOrders->count() }})
            </a>
            <a href="{{ route('sales.orders.index', ['status' => 'partially_fulfilled', 'search' => $currentSearch]) }}"
                class="px-3 py-1 rounded-[7px] transition-all whitespace-nowrap {{ $currentStatus === 'partially_fulfilled' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55' }}">
                Sebagian ({{ $partialOrders->count() }})
            </a>
            <a href="{{ route('sales.orders.index', ['status' => 'fulfilled', 'search' => $currentSearch]) }}"
                class="px-3 py-1 rounded-[7px] transition-all whitespace-nowrap {{ $currentStatus === 'fulfilled' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55' }}">
                Selesai ({{ $fulfilledOrders->count() }})
            </a>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 4. DENSE DATA TABLE (DESKTOP & TABLET)                 -->
    <!-- ===================================================== -->
    <div class="hidden sm:block rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10 text-black/40 dark:text-white/40 text-[11px] font-semibold uppercase tracking-wide">
                        <th class="py-2.5 px-4">No. Sales Order</th>
                        <th class="py-2.5 px-4">Pelanggan</th>
                        <th class="py-2.5 px-4">Tanggal Pesanan</th>
                        <th class="py-2.5 px-4">Estimasi Kirim</th>
                        <th class="py-2.5 px-4 text-right">Pajak</th>
                        <th class="py-2.5 px-4 text-right">Nilai Pesanan</th>
                        <th class="py-2.5 px-4 text-center">Status Pemenuhan</th>
                        <th class="py-2.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($salesOrders as $so)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="py-3 px-4 font-semibold">
                            <a href="{{ route('sales.orders.show', $so) }}" class="text-[#007AFF] hover:underline tabular-nums">
                                {{ $so->so_number }}
                            </a>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-medium text-black dark:text-white">
                                {{ $so->customer->name }}
                            </div>
                            @if($so->customer->company)
                                <div class="text-[11px] text-black/45 dark:text-white/45">
                                    {{ $so->customer->company }}
                                </div>
                            @endif
                        </td>
                        <td class="py-3 px-4 tabular-nums text-black/60 dark:text-white/60 whitespace-nowrap">
                            {{ $so->order_date->format('d M Y') }}
                        </td>
                        <td class="py-3 px-4 tabular-nums text-black/60 dark:text-white/60 whitespace-nowrap">
                            {{ $so->expected_delivery_date ? $so->expected_delivery_date->format('d M Y') : '—' }}
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums text-black/60 dark:text-white/60 whitespace-nowrap">
                            <div>Rp {{ number_format($so->tax_amount, 0, ',', '.') }}</div>
                            <span class="text-[10px] text-black/40 dark:text-white/40">({{ number_format($so->tax_percentage, 1) }}%)</span>
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums font-semibold text-black dark:text-white whitespace-nowrap">
                            Rp {{ number_format($so->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            @if($so->status === 'fulfilled')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Selesai
                                </span>
                            @elseif($so->status === 'partially_fulfilled')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span> Sebagian
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span> Terkonfirmasi
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right whitespace-nowrap">
                            <a href="{{ route('sales.orders.show', $so) }}"
                                class="h-7 px-2.5 rounded-[6px] text-[12px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors inline-flex items-center">
                                Detail ›
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-14 text-center">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-10 h-10 text-black/20 dark:text-white/20 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                <h4 class="text-[15px] font-medium text-black dark:text-white">Belum Ada Pesanan Penjualan</h4>
                                <p class="text-[13px] text-black/50 dark:text-white/50 max-w-sm">Mulai buat sales order baru untuk mengunci alokasi stok dan menerbitkan faktur tagihan resmi.</p>
                                <a href="{{ route('sales.orders.create') }}"
                                    class="mt-2 h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] transition flex items-center gap-1.5">
                                    <span>Buat Pesanan Pertama</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 5. MOBILE GROUPED INSET LIST (Standar iOS 18)         -->
    <!-- ===================================================== -->
    <div class="sm:hidden rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
        @forelse($salesOrders as $so)
        <a href="{{ route('sales.orders.show', $so) }}" class="p-3.5 flex items-center justify-between gap-3 active:bg-black/[0.03] dark:active:bg-white/[0.05] transition-colors">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <p class="text-[15px] font-semibold text-black dark:text-white truncate tabular-nums">{{ $so->so_number }}</p>
                    @if($so->status === 'fulfilled')
                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] shrink-0"></span>
                    @elseif($so->status === 'partially_fulfilled')
                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500] shrink-0"></span>
                    @else
                        <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF] shrink-0"></span>
                    @endif
                </div>
                <p class="text-[13px] text-black/60 dark:text-white/60 truncate">{{ $so->customer->name }}</p>
                <p class="text-[12px] text-black/45 dark:text-white/45 mt-0.5 tabular-nums">
                    {{ $so->order_date->format('d M Y') }} &bull; Rp {{ number_format($so->total_amount, 0, ',', '.') }}
                </p>
            </div>
            <div class="flex items-center gap-1 shrink-0 text-black/30 dark:text-white/30">
                <span class="text-[12px] font-medium text-[#007AFF]">Detail</span>
                <span class="text-[14px]">›</span>
            </div>
        </a>
        @empty
        <div class="p-8 text-center text-black/40 dark:text-white/40 text-[13px]">
            Belum ada pesanan penjualan terdaftar.
        </div>
        @endforelse
    </div>

    <!-- ===================================================== -->
    <!-- 6. PAGINATION (Minimalist Apple HIG Stepper)          -->
    <!-- ===================================================== -->
    @if($salesOrders->hasPages())
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 px-4 py-3 flex items-center justify-between text-[13px] text-black/60 dark:text-white/60">
        <div>
            Menampilkan <span class="font-medium text-black dark:text-white tabular-nums">{{ $salesOrders->firstItem() ?? 0 }}–{{ $salesOrders->lastItem() ?? 0 }}</span> dari <span class="font-medium text-black dark:text-white tabular-nums">{{ $salesOrders->total() }}</span> pesanan
        </div>
        <div>
            {{ $salesOrders->links() }}
        </div>
    </div>
    @endif

</div>
@endsection
