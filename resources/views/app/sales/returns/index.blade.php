@extends('layouts.app', [
    'title' => 'Retur Penjualan — Cooca UMKM',
    'headerTitle' => 'Retur Penjualan & Pengembalian Barang',
    'headerSubtitle' => 'Kelola pengembalian barang pelanggan atas faktur penjualan atau kasir POS, penerbitan credit note, dan pengembalian stok gudang.'
])

@section('content')
@php
    $totalReturnsCount = $returns->total();
    $draftReturns = $returns->filter(fn($r) => $r->status === 'draft');
    $approvedReturns = $returns->filter(fn($r) => $r->status === 'approved');
    $completedReturns = $returns->filter(fn($r) => $r->status === 'completed');
    $pageTotalAmount = $returns->sum('total_amount');
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
                <span class="text-black dark:text-white font-medium">Retur Penjualan</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Retur Penjualan &amp; Kompensasi</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Pengembalian barang pelanggan, pemotongan faktur (Credit Note), dan pemulihan stok.</p>
        </div>

        <div class="flex items-center gap-2.5 w-full sm:w-auto">
            <a href="{{ route('sales.returns.create') }}"
                class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] w-full sm:w-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Buat Retur Penjualan</span>
            </a>
        </div>
    </header>

    <!-- Flash Notifications (Apple Banner Style) -->
    @if(session('success'))
        <div class="rounded-[14px] bg-[#34C759]/12 border border-[#34C759]/20 px-4 py-3 text-[13px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-3">
            <span class="w-2 h-2 rounded-full bg-[#34C759] shrink-0"></span>
            <div class="flex-1 font-medium">{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-[14px] bg-[#FF3B30]/12 border border-[#FF3B30]/20 px-4 py-3 text-[13px] text-[#C41E17] dark:text-[#FF453A] flex items-center gap-3">
            <span class="w-2 h-2 rounded-full bg-[#FF3B30] shrink-0"></span>
            <div class="flex-1 font-medium">{{ session('error') }}</div>
        </div>
    @endif

    <!-- ===================================================== -->
    <!-- 2. KPI SUMMARY (Flat Neutral Material, Apple Standard) -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Tile 1: Nilai Retur (System Red) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Nilai Retur Halaman</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] sm:text-[24px] font-bold tabular-nums text-[#FF3B30] dark:text-[#FF453A]">
                    Rp {{ number_format($pageTotalAmount, 0, ',', '.') }}
                </span>
                <span class="text-[11px] font-medium text-[#FF3B30] dark:text-[#FF453A]">Refund</span>
            </div>
        </div>

        <!-- Tile 2: Total Retur (Neutral) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Dokumen Retur</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-black dark:text-white">{{ number_format($totalReturnsCount, 0, ',', '.') }}</span>
                <span class="text-[11px] font-medium text-black/40 dark:text-white/40">Tercatat</span>
            </div>
        </div>

        <!-- Tile 3: Menunggu Persetujuan (System Orange) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Menunggu Persetujuan</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">{{ $draftReturns->count() }}</span>
                <span class="text-[11px] font-medium text-[#FF9500] dark:text-[#FF9F0A]">Draft</span>
            </div>
        </div>

        <!-- Tile 4: Selesai & Restock (System Green) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Selesai &amp; Restock</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">{{ $completedReturns->count() }}</span>
                <span class="text-[11px] font-medium text-[#34C759] dark:text-[#30D158]">Stok Kembali</span>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. DENSE DATA TABLE (DESKTOP & TABLET)                 -->
    <!-- ===================================================== -->
    <div class="hidden sm:block rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10 text-black/40 dark:text-white/40 text-[11px] font-semibold uppercase tracking-wide">
                        <th class="py-2.5 px-4">No. Retur</th>
                        <th class="py-2.5 px-4">Referensi Transaksi</th>
                        <th class="py-2.5 px-4">Pelanggan</th>
                        <th class="py-2.5 px-4">Tanggal Retur</th>
                        <th class="py-2.5 px-4">Metode Kompensasi</th>
                        <th class="py-2.5 px-4 text-right">Nilai Retur</th>
                        <th class="py-2.5 px-4 text-center">Status</th>
                        <th class="py-2.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($returns as $return)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="py-3 px-4 font-semibold whitespace-nowrap">
                            <a href="{{ route('sales.returns.show', $return) }}" class="text-[#007AFF] hover:underline tabular-nums">
                                {{ $return->return_number }}
                            </a>
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            @if($return->invoice)
                                <span class="inline-flex items-center gap-1 text-[12px] font-medium text-black/70 dark:text-white/70">
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-[#007AFF]/10 text-[#007AFF]">INV</span>
                                    <span class="tabular-nums">{{ $return->invoice->invoice_number }}</span>
                                </span>
                            @elseif($return->posOrder)
                                <span class="inline-flex items-center gap-1 text-[12px] font-medium text-black/70 dark:text-white/70">
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-[#5856D6]/10 text-[#5856D6]">POS</span>
                                    <span class="tabular-nums">{{ $return->posOrder->order_number }}</span>
                                </span>
                            @else
                                <span class="text-black/40 dark:text-white/40">—</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-medium text-black dark:text-white">
                            @if($return->invoice)
                                {{ $return->invoice->customer?->name ?? 'Pelanggan Umum' }}
                            @elseif($return->posOrder)
                                {{ $return->posOrder->customer?->name ?? ($return->posOrder->customer_name_guest ?: 'Tamu Kasir') }}
                            @else
                                {{ $return->customer?->name ?? 'Pelanggan Umum' }}
                            @endif
                        </td>
                        <td class="py-3 px-4 tabular-nums text-black/60 dark:text-white/60 whitespace-nowrap">
                            {{ $return->return_date ? $return->return_date->format('d/m/Y') : $return->created_at->format('d/m/Y') }}
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-[6px] text-[11px] font-medium bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70">
                                {{ str_replace('_', ' ', $return->refund_method ?? 'cash_refund') }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums font-semibold text-black dark:text-white whitespace-nowrap">
                            Rp {{ number_format($return->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            @if($return->status === 'completed')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Selesai
                                </span>
                            @elseif($return->status === 'approved')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span> Disetujui
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span> Draft
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right whitespace-nowrap">
                            <a href="{{ route('sales.returns.show', $return) }}"
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
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                                </svg>
                                <h4 class="text-[15px] font-medium text-black dark:text-white">Belum Ada Transaksi Retur</h4>
                                <p class="text-[13px] text-black/50 dark:text-white/50 max-w-sm">Pengembalian barang dari kasir POS atau faktur B2B akan tercatat rapi di sini untuk pemulihan stok.</p>
                                <a href="{{ route('sales.returns.create') }}"
                                    class="mt-2 h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] transition flex items-center gap-1.5">
                                    <span>Buat Retur Pertama</span>
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
    <!-- 4. MOBILE GROUPED INSET LIST (Standar iOS 18)         -->
    <!-- ===================================================== -->
    <div class="sm:hidden rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
        @forelse($returns as $return)
        <a href="{{ route('sales.returns.show', $return) }}" class="p-3.5 flex items-center justify-between gap-3 active:bg-black/[0.03] dark:active:bg-white/[0.05] transition-colors">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <p class="text-[15px] font-semibold text-black dark:text-white truncate tabular-nums">{{ $return->return_number }}</p>
                    @if($return->status === 'completed')
                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] shrink-0"></span>
                    @elseif($return->status === 'approved')
                        <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF] shrink-0"></span>
                    @else
                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500] shrink-0"></span>
                    @endif
                </div>
                <p class="text-[13px] text-black/60 dark:text-white/60 truncate">
                    @if($return->invoice)
                        {{ $return->invoice->customer?->name ?? 'Pelanggan Umum' }}
                    @elseif($return->posOrder)
                        {{ $return->posOrder->customer?->name ?? ($return->posOrder->customer_name_guest ?: 'Tamu Kasir') }}
                    @else
                        {{ $return->customer?->name ?? 'Pelanggan Umum' }}
                    @endif
                </p>
                <p class="text-[12px] text-black/45 dark:text-white/45 mt-0.5 tabular-nums">
                    {{ $return->return_date ? $return->return_date->format('d/m/Y') : $return->created_at->format('d/m/Y') }} &bull; Rp {{ number_format($return->total_amount, 0, ',', '.') }}
                </p>
            </div>
            <div class="flex items-center gap-1 shrink-0 text-black/30 dark:text-white/30">
                <span class="text-[12px] font-medium text-[#007AFF]">Detail</span>
                <span class="text-[14px]">›</span>
            </div>
        </a>
        @empty
        <div class="p-8 text-center text-black/40 dark:text-white/40 text-[13px]">
            Belum ada data retur penjualan.
        </div>
        @endforelse
    </div>

    <!-- ===================================================== -->
    <!-- 5. PAGINATION (Minimalist Apple HIG Stepper)          -->
    <!-- ===================================================== -->
    @if($returns->hasPages())
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 px-4 py-3 flex items-center justify-between text-[13px] text-black/60 dark:text-white/60">
        <div>
            Menampilkan <span class="font-medium text-black dark:text-white tabular-nums">{{ $returns->firstItem() ?? 0 }}–{{ $returns->lastItem() ?? 0 }}</span> dari <span class="font-medium text-black dark:text-white tabular-nums">{{ $returns->total() }}</span> retur
        </div>
        <div>
            {{ $returns->links() }}
        </div>
    </div>
    @endif

</div>
@endsection
