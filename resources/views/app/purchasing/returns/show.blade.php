@extends('layouts.app', [
    'title' => 'Detail Retur ' . $return->return_number,
    'headerTitle' => 'Detail Retur Pembelian',
    'headerSubtitle' => 'Rincian fisik barang yang diretur ke pemasok dan penyesuaian stok'
])

@section('content')
<div class="max-w-[1080px] mx-auto space-y-6 pb-12">
    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / HEADER (macOS Sonoma Style)              -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <!-- Breadcrumb minimal -->
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <a href="{{ route('purchase.returns.index') }}" class="hover:text-[#007AFF] transition-colors">Retur Pembelian</a>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">{{ $return->return_number }}</span>
            </nav>
            <div class="flex items-center gap-2.5">
                <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">{{ $return->return_number }}</h1>
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
            </div>
            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">
                Ref. GR: <span class="font-medium text-black dark:text-white tabular-nums">{{ $return->goodsReceipt->receipt_number ?? '-' }}</span> &bull; 
                Supplier: <span class="font-medium text-black dark:text-white">{{ $return->supplier->name ?? '-' }}</span>
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('purchase.returns.index') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Kembali</span>
            </a>
        </div>
    </header>

    @if(session('success'))
    <div class="rounded-[12px] bg-[#34C759]/12 border border-[#34C759]/20 px-4 py-3 text-[13px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 2. STATUS & SUMMARY COCKPIT                           -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-black/5 dark:border-white/10 pb-4">
            <div>
                <span class="text-[11px] font-medium text-black/50 dark:text-white/50 uppercase tracking-wide">Status Dokumen</span>
                <div class="mt-1">
                    @if($return->status === 'completed')
                        <span class="text-[15px] font-semibold text-[#34C759] dark:text-[#30D158]">Selesai (Stok Fisik Telah Dipotong)</span>
                    @elseif($return->status === 'approved')
                        <span class="text-[15px] font-semibold text-[#007AFF] dark:text-[#0A84FF]">Disetujui (Menunggu Pengiriman Fisik)</span>
                    @else
                        <span class="text-[15px] font-semibold text-[#FF9500] dark:text-[#FF9F0A]">Draft (Menunggu Persetujuan Manajer)</span>
                    @endif
                </div>
            </div>

            <div class="text-left sm:text-right">
                <span class="text-[11px] font-medium text-black/50 dark:text-white/50 uppercase tracking-wide">Total Nilai Retur</span>
                <div class="text-[22px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A] mt-0.5">
                    Rp {{ number_format($return->total_amount, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <div class="text-[13px] text-black/70 dark:text-white/70">
            <span class="font-medium text-black dark:text-white">Alasan Retur / Klaim:</span>
            <span class="ml-1">{{ $return->reason }}</span>
        </div>

        <!-- Action Buttons -->
        @if($return->status === 'draft' || $return->status === 'approved')
        <div class="flex items-center gap-3 pt-2">
            @if($return->status === 'draft')
            <form method="POST" action="{{ route('purchase.returns.approve', $return) }}">
                @csrf
                <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <span>Setujui Retur</span>
                </button>
            </form>
            @endif

            @if($return->status === 'approved')
            <form method="POST" action="{{ route('purchase.returns.complete', $return) }}">
                @csrf
                <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#34C759] hover:bg-[#2FB350] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(52,199,89,0.25)]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Selesaikan &amp; Potong Stok Gudang</span>
                </button>
            </form>
            @endif
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 3. ITEMS TABLE CARD                                   -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
            <h3 class="text-[14px] font-semibold text-black dark:text-white">Daftar Barang yang Diretur</h3>
            <span class="text-[12px] text-black/50 dark:text-white/50 tabular-nums">{{ count($return->items) }} Item Baris</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10">
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Nama Produk / Bahan</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Kuantitas</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Harga Satuan (Rp)</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Subtotal (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @foreach($return->items as $item)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3 font-medium text-black dark:text-white">
                            {{ $item->item_name }}
                        </td>
                        <td class="px-4 py-3 text-center tabular-nums font-semibold text-[#FF9500] dark:text-[#FF9F0A]">
                            {{ $item->quantity }}
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums text-black/60 dark:text-white/60">
                            {{ number_format($item->unit_price ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums font-semibold text-black dark:text-white">
                            {{ number_format($item->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
