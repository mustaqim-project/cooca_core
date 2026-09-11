@extends('layouts.app', [
    'title' => 'Retur Pembelian ke Supplier',
    'headerTitle' => 'Retur Pembelian',
    'headerSubtitle' => 'Kelola pengembalian barang rusak atau tidak sesuai spesifikasi ke vendor dan penyesuaian stok otomatis'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12">
    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Style)          -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <!-- Breadcrumb minimal -->
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <span class="text-black/70 dark:text-white/70 font-medium">Pembelian &amp; Vendor</span>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Retur Pembelian</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Retur Pembelian ke Supplier</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Kelola pengembalian fisik barang rusak / cacat ke vendor dan penyesuaian saldo stok.</p>
        </div>

        <!-- Toolbar Actions -->
        <div class="flex items-center gap-2 w-full sm:w-auto">
            @if(\App\Support\Context::hasPermission('purchase.returns') || \App\Support\Context::hasPermission('purchasing.manage'))
            <a href="{{ route('purchase.returns.create') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Buat Retur Baru</span>
            </a>
            @endif
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
    <!-- 2. KPI SUMMARY ROW                                     -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
        <!-- Tile 1: Total Dokumen Retur -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Retur Terdata</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums text-black dark:text-white">
                    {{ $returns->total() }} Dokumen
                </span>
                <span class="text-[11px] text-black/40 dark:text-white/40">Klaim Vendor</span>
            </div>
        </div>

        <!-- Tile 2: Total Nilai Retur (System Orange Warning/Claim) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Nilai Retur</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">
                    Rp {{ number_format($returns->sum('total_amount'), 0, ',', '.') }}
                </span>
                <span class="text-[11px] font-semibold text-[#FF9500] dark:text-[#FF9F0A]">Nilai Klaim</span>
            </div>
        </div>

        <!-- Tile 3: Status Selesai (System Green) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Stok Berhasil Disesuaikan</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">
                    {{ $returns->where('status', 'completed')->count() }} Selesai
                </span>
                <span class="text-[11px] font-semibold text-[#34C759] dark:text-[#30D158]">Inventori Sinkron</span>
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
                    <tr class="border-b border-black/5 dark:border-white/10">
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">No. Retur</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Supplier / Vendor</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Ref. Penerimaan (GR)</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Tanggal</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Nilai Total</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Status</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($returns as $return)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3">
                            <a href="{{ route('purchase.returns.show', $return) }}" class="font-medium text-[#007AFF] hover:underline">
                                {{ $return->return_number }}
                            </a>
                        </td>
                        <td class="px-4 py-3 font-medium text-black dark:text-white">
                            {{ $return->supplier->name ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-black/60 dark:text-white/60 tabular-nums">
                            {{ $return->goodsReceipt->receipt_number ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-black/60 dark:text-white/60 tabular-nums">
                            {{ $return->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums font-semibold text-black dark:text-white">
                            Rp {{ number_format($return->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
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
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('purchase.returns.show', $return) }}" class="h-7 px-2.5 rounded-[6px] text-[12px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors inline-flex items-center">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-[13px] text-black/40 dark:text-white/40">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-8 h-8 text-black/20 dark:text-white/20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                                </svg>
                                <span>Belum ada transaksi retur pembelian.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 4. MOBILE GROUPED INSET LIST (iOS 18 Style)            -->
    <!-- ===================================================== -->
    <div class="sm:hidden rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
        @forelse($returns as $return)
        <a href="{{ route('purchase.returns.show', $return) }}" class="p-3.5 flex items-center justify-between gap-3 active:bg-black/[0.03] dark:active:bg-white/[0.05] transition-colors">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <p class="text-[15px] font-medium text-black dark:text-white truncate">{{ $return->return_number }}</p>
                    @if($return->status === 'completed')
                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] shrink-0"></span>
                    @elseif($return->status === 'approved')
                        <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF] shrink-0"></span>
                    @else
                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500] shrink-0"></span>
                    @endif
                </div>
                <p class="text-[13px] text-black/60 dark:text-white/60 truncate mt-0.5">{{ $return->supplier->name ?? '-' }} &bull; GR: {{ $return->goodsReceipt->receipt_number ?? '-' }}</p>
                <div class="flex items-center gap-2 mt-1 text-[12px] text-black/45 dark:text-white/45 tabular-nums">
                    <span>{{ $return->created_at->format('d/m/Y') }}</span>
                    <span>&bull;</span>
                    <span class="font-semibold text-black dark:text-white">Rp {{ number_format($return->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="flex items-center gap-1 text-black/30 dark:text-white/30 shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </div>
        </a>
        @empty
        <div class="p-8 text-center text-[13px] text-black/40 dark:text-white/40">
            Belum ada transaksi retur pembelian.
        </div>
        @endforelse
    </div>

    <!-- ===================================================== -->
    <!-- 5. PAGINATION                                         -->
    <!-- ===================================================== -->
    @if($returns->hasPages())
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3">
        {{ $returns->links() }}
    </div>
    @endif
</div>
@endsection
