@extends('layouts.app', [
    'title' => 'Tagihan & Hutang Supplier',
    'headerTitle' => 'Tagihan & Hutang Supplier',
    'headerSubtitle' => 'Kelola faktur tagihan masuk dari supplier atas penerimaan barang (PO) dan pantau jadwal pelunasan'
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
                <span class="text-black dark:text-white font-medium">Tagihan &amp; Hutang</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Tagihan &amp; Hutang Supplier</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Kelola faktur tagihan masuk dari supplier atas penerimaan barang (PO) dan pantau jadwal pelunasan.</p>
        </div>

        <!-- Toolbar Actions -->
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <a href="{{ route('finance.payables') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-[#FF9500] dark:text-[#FF9F0A]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Analisis AP Aging</span>
            </a>

            <a href="{{ route('purchase-orders.index') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                <span>Daftar PO</span>
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
    <!-- 2. KPI SUMMARY ROW                                     -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        <!-- Tile 1: Total Tagihan Masuk -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Tagihan Masuk</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums text-black dark:text-white">
                    Rp {{ number_format($invoices->sum('total_amount'), 0, ',', '.') }}
                </span>
                <span class="text-[11px] text-black/40 dark:text-white/40">Akumulasi</span>
            </div>
        </div>

        <!-- Tile 2: Sisa Hutang Belum Lunas (Warning / System Orange) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Sisa Hutang Belum Lunas</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">
                    Rp {{ number_format($invoices->sum('balance_due'), 0, ',', '.') }}
                </span>
                <span class="text-[11px] font-semibold text-[#FF9500] dark:text-[#FF9F0A]">Perlu Pelunasan</span>
            </div>
        </div>

        <!-- Tile 3: Total Faktur Terdata (System Blue) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Tagihan Terdata</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF]">
                    {{ $invoices->total() }} Faktur
                </span>
                <span class="text-[11px] text-black/40 dark:text-white/40">Database AP</span>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. CONTROLS: SEGMENTED FILTER                          -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 sm:p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <form method="GET" action="{{ route('purchasing.bills.index') }}" class="w-full flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Status:</span>
                <!-- Segmented Control -->
                <div class="inline-flex p-0.5 rounded-[9px] bg-black/[0.06] dark:bg-white/[0.08] text-[13px] font-medium">
                    <a href="{{ route('purchasing.bills.index') }}" class="px-3 py-1 rounded-[7px] transition-all {{ !request('status') ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55 hover:text-black dark:hover:text-white' }}">
                        Semua
                    </a>
                    <a href="{{ route('purchasing.bills.index', ['status' => 'unpaid']) }}" class="px-3 py-1 rounded-[7px] transition-all {{ request('status') === 'unpaid' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55 hover:text-black dark:hover:text-white' }}">
                        Belum Bayar
                    </a>
                    <a href="{{ route('purchasing.bills.index', ['status' => 'partial']) }}" class="px-3 py-1 rounded-[7px] transition-all {{ request('status') === 'partial' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55 hover:text-black dark:hover:text-white' }}">
                        Sebagian
                    </a>
                    <a href="{{ route('purchasing.bills.index', ['status' => 'paid']) }}" class="px-3 py-1 rounded-[7px] transition-all {{ request('status') === 'paid' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55 hover:text-black dark:hover:text-white' }}">
                        Lunas
                    </a>
                </div>
            </div>

            @if(request('status'))
            <a href="{{ route('purchasing.bills.index') }}" class="h-8 px-3 rounded-[8px] text-[12px] font-medium text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] transition-colors inline-flex items-center gap-1.5 self-start sm:self-auto">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>Reset Filter</span>
            </a>
            @endif
        </form>
    </div>

    <!-- ===================================================== -->
    <!-- 4. DENSE DATA TABLE (DESKTOP & TABLET)                 -->
    <!-- ===================================================== -->
    <div class="hidden sm:block rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10">
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">No. Tagihan</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Pemasok / Vendor</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Tanggal Masuk</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Jatuh Tempo</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Total Tagihan</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Sisa Hutang</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Status</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($invoices as $invoice)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3">
                            <a href="{{ route('purchasing.bills.show', $invoice) }}" class="font-medium text-[#007AFF] hover:underline">
                                {{ $invoice->invoice_number }}
                            </a>
                        </td>
                        <td class="px-4 py-3 font-medium text-black dark:text-white">
                            {{ $invoice->supplier->name ?? 'Supplier Umum' }}
                        </td>
                        <td class="px-4 py-3 text-black/60 dark:text-white/60 tabular-nums">
                            {{ $invoice->invoice_date->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 tabular-nums">
                            @if($invoice->due_date)
                                <span class="{{ $invoice->due_date->isPast() && $invoice->balance_due > 0 ? 'text-[#FF3B30] dark:text-[#FF453A] font-semibold' : 'text-black/60 dark:text-white/60' }}">
                                    {{ $invoice->due_date->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-black/30 dark:text-white/30">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums text-black dark:text-white font-medium">
                            Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums font-semibold {{ $invoice->balance_due > 0 ? 'text-[#FF9500] dark:text-[#FF9F0A]' : 'text-[#34C759] dark:text-[#30D158]' }}">
                            Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($invoice->status === 'paid')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Lunas
                                </span>
                            @elseif($invoice->status === 'partial')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span> Sebagian
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span> Belum Bayar
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('purchasing.bills.show', $invoice) }}" class="h-7 px-2.5 rounded-[6px] text-[12px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors inline-flex items-center">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-[13px] text-black/40 dark:text-white/40">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-8 h-8 text-black/20 dark:text-white/20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                <span>Belum ada tagihan supplier yang terdaftar.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 5. MOBILE GROUPED INSET LIST (iOS 18 Style)            -->
    <!-- ===================================================== -->
    <div class="sm:hidden rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
        @forelse($invoices as $invoice)
        <a href="{{ route('purchasing.bills.show', $invoice) }}" class="p-3.5 flex items-center justify-between gap-3 active:bg-black/[0.03] dark:active:bg-white/[0.05] transition-colors">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <p class="text-[15px] font-medium text-black dark:text-white truncate">{{ $invoice->invoice_number }}</p>
                    @if($invoice->status === 'paid')
                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] shrink-0"></span>
                    @elseif($invoice->status === 'partial')
                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500] shrink-0"></span>
                    @else
                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30] shrink-0"></span>
                    @endif
                </div>
                <p class="text-[13px] text-black/60 dark:text-white/60 truncate mt-0.5">{{ $invoice->supplier->name ?? 'Supplier Umum' }}</p>
                <div class="flex items-center gap-2 mt-1 text-[12px] text-black/45 dark:text-white/45 tabular-nums">
                    <span>{{ $invoice->invoice_date->format('d/m/Y') }}</span>
                    <span>&bull;</span>
                    <span class="font-semibold {{ $invoice->balance_due > 0 ? 'text-[#FF9500] dark:text-[#FF9F0A]' : 'text-[#34C759] dark:text-[#30D158]' }}">
                        Sisa: Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}
                    </span>
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
            Belum ada tagihan supplier yang terdaftar.
        </div>
        @endforelse
    </div>

    <!-- ===================================================== -->
    <!-- 6. PAGINATION                                         -->
    <!-- ===================================================== -->
    @if($invoices->hasPages())
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3">
        {{ $invoices->links() }}
    </div>
    @endif
</div>
@endsection
