@extends('layouts.app', ['title' => 'Hutang Usaha (AP Aging)'])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-5 pb-12">

    {{-- ========================================================== --}}
    {{-- TOOLBAR / PAGE HEADER --}}
    {{-- ========================================================== --}}
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <span class="text-black/70 dark:text-white/70 font-medium">Keuangan</span>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Hutang Usaha</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Hutang Usaha (AP Aging)</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Pantau jadwal jatuh tempo tagihan pemasok untuk menjaga kelancaran arus kas</p>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            @if(\App\Support\Context::hasPermission('purchasing.bills') || \App\Support\Context::hasPermission('purchasing.manage'))
            <a href="{{ route('purchasing.bills.index') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#FF9500] hover:bg-[#E8880A] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(255,149,0,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185z"/></svg>
                <span>Tagihan Supplier</span>
            </a>
            @endif
            @if(\App\Support\Context::hasPermission('master_data.suppliers') || \App\Support\Context::hasPermission('purchasing.view'))
            <a href="{{ route('suppliers.index') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-[#FF9500]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                <span>Daftar Pemasok</span>
            </a>
            @endif
        </div>
    </header>

    @php
        $totalPayable = $totalPayable ?? $invoices->sum('balance_due');
        $notDue = $notDue ?? $invoices->filter(fn($i) => $i->due_date && $i->due_date->isFuture())->sum('balance_due');
        $overdue1to30 = $overdue1to30 ?? $invoices->filter(function($i) {
            if (!$i->due_date || $i->due_date->isFuture()) return false;
            $days = now()->diffInDays($i->due_date);
            return $days >= 0 && $days <= 30;
        })->sum('balance_due');
        $overdue30plus = $overdue30plus ?? $invoices->filter(function($i) {
            if (!$i->due_date || $i->due_date->isFuture()) return false;
            return now()->diffInDays($i->due_date) > 30;
        })->sum('balance_due');
        $totalOpenCount = $totalOpenCount ?? $invoices->total();
        $currentBucket = request('bucket', 'all');
    @endphp

    {{-- ========================================================== --}}
    {{-- AP AGING KPI TILES --}}
    {{-- ========================================================== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <a href="{{ route('finance.payables', array_merge(request()->except(['page']), ['bucket' => 'all'])) }}"
           class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border {{ $currentBucket === 'all' ? 'border-[#007AFF] shadow-[0_0_0_1px_#007AFF]' : 'border-black/5 dark:border-white/5' }} p-4 flex flex-col justify-between hover:border-[#007AFF]/50 transition-all">
            <p class="text-[11px] font-medium text-black/45 dark:text-white/45">Total Hutang Supplier</p>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums text-black dark:text-white">Rp {{ number_format($totalPayable, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">{{ $totalOpenCount }} Tagihan Terbuka</p>
        </a>
        <a href="{{ route('finance.payables', array_merge(request()->except(['page']), ['bucket' => 'not_due'])) }}"
           class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border {{ $currentBucket === 'not_due' ? 'border-[#34C759] shadow-[0_0_0_1px_#34C759]' : 'border-black/5 dark:border-white/5' }} p-4 flex flex-col justify-between hover:border-[#34C759]/50 transition-all">
            <p class="text-[11px] font-medium text-black/45 dark:text-white/45">Belum Jatuh Tempo (Lancar)</p>
            <div class="mt-2">
                <span class="text-[22px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">Rp {{ number_format($notDue, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-[#34C759] dark:text-[#30D158] mt-0.5">Sesuai tempo pembayaran</p>
        </a>
        <a href="{{ route('finance.payables', array_merge(request()->except(['page']), ['bucket' => 'overdue_1_30'])) }}"
           class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border {{ $currentBucket === 'overdue_1_30' ? 'border-[#FF9500] shadow-[0_0_0_1px_#FF9500]' : 'border-black/5 dark:border-white/5' }} p-4 flex flex-col justify-between hover:border-[#FF9500]/50 transition-all">
            <p class="text-[11px] font-medium text-black/45 dark:text-white/45">Lewat Tempo (1–30 Hari)</p>
            <div class="mt-2">
                <span class="text-[22px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">Rp {{ number_format($overdue1to30, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-[#FF9500] dark:text-[#FF9F0A] mt-0.5">Segera jadwalkan transfer</p>
        </a>
        <a href="{{ route('finance.payables', array_merge(request()->except(['page']), ['bucket' => 'overdue_30_plus'])) }}"
           class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border {{ $currentBucket === 'overdue_30_plus' ? 'border-[#FF3B30] shadow-[0_0_0_1px_#FF3B30]' : 'border-black/5 dark:border-white/5' }} p-4 flex flex-col justify-between hover:border-[#FF3B30]/50 transition-all">
            <p class="text-[11px] font-medium text-black/45 dark:text-white/45">Lewat Tempo (> 30 Hari)</p>
            <div class="mt-2">
                <span class="text-[22px] font-bold tabular-nums text-[#FF3B30] dark:text-[#FF453A]">Rp {{ number_format($overdue30plus, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-[#FF3B30] dark:text-[#FF453A] mt-0.5">Prioritas pelunasan mendesak</p>
        </a>
    </div>

    {{-- ========================================================== --}}
    {{-- SEARCH & FILTER BAR --}}
    {{-- ========================================================== --}}
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
            <a href="{{ route('finance.payables', array_merge(request()->except(['page', 'bucket']), ['bucket' => 'all'])) }}"
               class="px-3 py-1.5 rounded-[8px] text-[12px] font-medium transition-colors whitespace-nowrap {{ $currentBucket === 'all' ? 'bg-[#007AFF] text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5' }}">
                Semua
            </a>
            <a href="{{ route('finance.payables', array_merge(request()->except(['page', 'bucket']), ['bucket' => 'not_due'])) }}"
               class="px-3 py-1.5 rounded-[8px] text-[12px] font-medium transition-colors whitespace-nowrap {{ $currentBucket === 'not_due' ? 'bg-[#34C759] text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5' }}">
                Lancar (Sesuai Tempo)
            </a>
            <a href="{{ route('finance.payables', array_merge(request()->except(['page', 'bucket']), ['bucket' => 'overdue_1_30'])) }}"
               class="px-3 py-1.5 rounded-[8px] text-[12px] font-medium transition-colors whitespace-nowrap {{ $currentBucket === 'overdue_1_30' ? 'bg-[#FF9500] text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5' }}">
                Lewat 1–30 Hari
            </a>
            <a href="{{ route('finance.payables', array_merge(request()->except(['page', 'bucket']), ['bucket' => 'overdue_30_plus'])) }}"
               class="px-3 py-1.5 rounded-[8px] text-[12px] font-medium transition-colors whitespace-nowrap {{ $currentBucket === 'overdue_30_plus' ? 'bg-[#FF3B30] text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5' }}">
                Lewat > 30 Hari
            </a>
        </div>
        <form method="GET" action="{{ route('finance.payables') }}" class="flex items-center gap-2">
            <input type="hidden" name="bucket" value="{{ $currentBucket }}">
            <div class="relative flex-1 sm:w-64">
                <svg class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no. tagihan / supplier..."
                       class="w-full h-8 pl-8 pr-3 text-[12px] bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
            </div>
            @if(request('search') || request('bucket') !== 'all')
                <a href="{{ route('finance.payables') }}" class="h-8 px-2.5 rounded-[8px] text-[12px] text-black/50 dark:text-white/50 hover:bg-black/5 dark:hover:bg-white/5 flex items-center">Reset</a>
            @endif
        </form>
    </div>

    {{-- ========================================================== --}}
    {{-- AP TABLE --}}
    {{-- ========================================================== --}}
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="px-4 py-3 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
            <h2 class="text-[15px] font-semibold text-black dark:text-white">Daftar Tagihan Hutang Supplier</h2>
            <span class="text-[13px] text-black/45 dark:text-white/45 tabular-nums">{{ $invoices->total() }} Tagihan</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10">
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">No. Tagihan</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Pemasok / Vendor</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Tgl. Tagihan</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Jatuh Tempo</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Total Tagihan</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Sisa Hutang</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Umur Hutang</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($invoices as $invoice)
                    @php
                        $daysDiff = $invoice->due_date ? (int) now()->startOfDay()->diffInDays($invoice->due_date, false) : 0;
                        $isOverdue = $daysDiff < 0;
                        $daysOverdue = abs($daysDiff);
                    @endphp
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3 tabular-nums font-semibold text-black dark:text-white">
                            {{ $invoice->invoice_number ?? $invoice->bill_number ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-black dark:text-white">{{ $invoice->supplier?->name ?? 'Supplier Umum' }}</div>
                            @if($invoice->supplier?->phone)
                                <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums mt-0.5">{{ $invoice->supplier->phone }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-black/60 dark:text-white/60">{{ $invoice->invoice_date?->format('d M Y') ?? $invoice->created_at?->format('d M Y') ?? '-' }}</td>
                        <td class="px-4 py-3 {{ $isOverdue ? 'text-[#FF3B30] dark:text-[#FF453A] font-semibold' : 'text-black/60 dark:text-white/60' }}">
                            {{ $invoice->due_date?->format('d M Y') ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums text-black/60 dark:text-white/60">
                            Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums font-semibold text-[#FF9500] dark:text-[#FF9F0A]">
                            Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($isOverdue)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span> Lewat {{ $daysOverdue }} hr
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Sisa {{ $daysDiff }} hr
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if(\App\Support\Context::hasPermission('purchasing.bills') || \App\Support\Context::hasPermission('purchasing.manage') || \App\Support\Context::hasPermission('invoices.record_payment'))
                            <a href="{{ route('purchasing.bills.index') }}" class="h-7 px-2.5 rounded-[6px] text-[12px] font-medium text-[#FF9500] hover:bg-[#FF9500]/8 transition-colors inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18-3a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3h18V6z"/></svg>
                                Bayar
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 text-black/15 dark:text-white/15" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                                <div>
                                    <p class="text-[15px] font-semibold text-black/60 dark:text-white/60">Tidak Ada Hutang Supplier Terbuka</p>
                                    <p class="text-[13px] text-black/40 dark:text-white/40 mt-0.5">Seluruh tagihan pembelian telah dibayar lunas</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
        <div class="px-4 py-3 border-t border-black/5 dark:border-white/10 flex items-center justify-between text-[13px] text-black/60 dark:text-white/60">
            <span>{{ $invoices->total() }} tagihan</span>
            <div class="flex items-center gap-2">
                @if($invoices->onFirstPage())
                    <span class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-black/30 dark:text-white/30 cursor-not-allowed">‹ Sebelumnya</span>
                @else
                    <a href="{{ $invoices->previousPageUrl() }}" class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors flex items-center">‹ Sebelumnya</a>
                @endif
                <span class="h-8 px-2.5 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] text-black dark:text-white font-medium flex items-center tabular-nums">{{ $invoices->currentPage() }}</span>
                @if($invoices->hasMorePages())
                    <a href="{{ $invoices->nextPageUrl() }}" class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors flex items-center">Selanjutnya ›</a>
                @else
                    <span class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-black/30 dark:text-white/30 cursor-not-allowed">Selanjutnya ›</span>
                @endif
            </div>
        </div>
        @endif
    </div>

</div>
@endsection
