@extends('layouts.app', ['title' => 'Piutang Usaha (AR Aging)'])

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
                <span class="text-black dark:text-white font-medium">Piutang Usaha</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Piutang Usaha (AR Aging)</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Analisis umur piutang pelanggan dan jadwal penagihan faktur tempo</p>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            @if(\App\Support\Context::hasPermission('invoices.create'))
            <a href="{{ route('invoices.create') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Buat Faktur Baru</span>
            </a>
            @endif
            @if(\App\Support\Context::hasPermission('invoices.view'))
            <a href="{{ route('invoices.index') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-[#34C759]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185z"/></svg>
                <span>Daftar Faktur</span>
            </a>
            @endif
        </div>
    </header>

    @php
        $totalBalance = $totalReceivable ?? $invoices->sum('balance_due');
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
    {{-- AR AGING KPI TILES --}}
    {{-- ========================================================== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <a href="{{ route('finance.receivables', array_merge(request()->except(['page']), ['bucket' => 'all'])) }}"
           class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border {{ $currentBucket === 'all' ? 'border-[#007AFF] shadow-[0_0_0_1px_#007AFF]' : 'border-black/5 dark:border-white/5' }} p-4 flex flex-col justify-between hover:border-[#007AFF]/50 transition-all">
            <p class="text-[11px] font-medium text-black/45 dark:text-white/45">Total Piutang Berjalan</p>
            <div class="mt-2">
                <span class="text-[22px] font-bold tabular-nums text-black dark:text-white">Rp {{ number_format($totalBalance, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">{{ $totalOpenCount }} Faktur Terbuka</p>
        </a>
        <a href="{{ route('finance.receivables', array_merge(request()->except(['page']), ['bucket' => 'not_due'])) }}"
           class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border {{ $currentBucket === 'not_due' ? 'border-[#34C759] shadow-[0_0_0_1px_#34C759]' : 'border-black/5 dark:border-white/5' }} p-4 flex flex-col justify-between hover:border-[#34C759]/50 transition-all">
            <p class="text-[11px] font-medium text-black/45 dark:text-white/45">Belum Jatuh Tempo (Lancar)</p>
            <div class="mt-2">
                <span class="text-[22px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">Rp {{ number_format($notDue, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-[#34C759] dark:text-[#30D158] mt-0.5">Sesuai termin pembayaran</p>
        </a>
        <a href="{{ route('finance.receivables', array_merge(request()->except(['page']), ['bucket' => 'overdue_1_30'])) }}"
           class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border {{ $currentBucket === 'overdue_1_30' ? 'border-[#FF9500] shadow-[0_0_0_1px_#FF9500]' : 'border-black/5 dark:border-white/5' }} p-4 flex flex-col justify-between hover:border-[#FF9500]/50 transition-all">
            <p class="text-[11px] font-medium text-black/45 dark:text-white/45">Lewat Tempo (1–30 Hari)</p>
            <div class="mt-2">
                <span class="text-[22px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">Rp {{ number_format($overdue1to30, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-[#FF9500] dark:text-[#FF9F0A] mt-0.5">Perlu tindak lanjut tagihan</p>
        </a>
        <a href="{{ route('finance.receivables', array_merge(request()->except(['page']), ['bucket' => 'overdue_30_plus'])) }}"
           class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border {{ $currentBucket === 'overdue_30_plus' ? 'border-[#FF3B30] shadow-[0_0_0_1px_#FF3B30]' : 'border-black/5 dark:border-white/5' }} p-4 flex flex-col justify-between hover:border-[#FF3B30]/50 transition-all">
            <p class="text-[11px] font-medium text-black/45 dark:text-white/45">Lewat Tempo (> 30 Hari)</p>
            <div class="mt-2">
                <span class="text-[22px] font-bold tabular-nums text-[#FF3B30] dark:text-[#FF453A]">Rp {{ number_format($overdue30plus, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-[#FF3B30] dark:text-[#FF453A] mt-0.5">Kategori kritis / macet</p>
        </a>
    </div>

    {{-- ========================================================== --}}
    {{-- SEARCH & FILTER BAR --}}
    {{-- ========================================================== --}}
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
            <a href="{{ route('finance.receivables', array_merge(request()->except(['page', 'bucket']), ['bucket' => 'all'])) }}"
               class="px-3 py-1.5 rounded-[8px] text-[12px] font-medium transition-colors whitespace-nowrap {{ $currentBucket === 'all' ? 'bg-[#007AFF] text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5' }}">
                Semua
            </a>
            <a href="{{ route('finance.receivables', array_merge(request()->except(['page', 'bucket']), ['bucket' => 'not_due'])) }}"
               class="px-3 py-1.5 rounded-[8px] text-[12px] font-medium transition-colors whitespace-nowrap {{ $currentBucket === 'not_due' ? 'bg-[#34C759] text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5' }}">
                Lancar (Sesuai Termin)
            </a>
            <a href="{{ route('finance.receivables', array_merge(request()->except(['page', 'bucket']), ['bucket' => 'overdue_1_30'])) }}"
               class="px-3 py-1.5 rounded-[8px] text-[12px] font-medium transition-colors whitespace-nowrap {{ $currentBucket === 'overdue_1_30' ? 'bg-[#FF9500] text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5' }}">
                Lewat 1–30 Hari
            </a>
            <a href="{{ route('finance.receivables', array_merge(request()->except(['page', 'bucket']), ['bucket' => 'overdue_30_plus'])) }}"
               class="px-3 py-1.5 rounded-[8px] text-[12px] font-medium transition-colors whitespace-nowrap {{ $currentBucket === 'overdue_30_plus' ? 'bg-[#FF3B30] text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5' }}">
                Lewat > 30 Hari
            </a>
        </div>
        <form method="GET" action="{{ route('finance.receivables') }}" class="flex items-center gap-2">
            <input type="hidden" name="bucket" value="{{ $currentBucket }}">
            <div class="relative flex-1 sm:w-64">
                <svg class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no. faktur / pelanggan..."
                       class="w-full h-8 pl-8 pr-3 text-[12px] bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
            </div>
            @if(request('search') || request('bucket') !== 'all')
                <a href="{{ route('finance.receivables') }}" class="h-8 px-2.5 rounded-[8px] text-[12px] text-black/50 dark:text-white/50 hover:bg-black/5 dark:hover:bg-white/5 flex items-center">Reset</a>
            @endif
        </form>
    </div>

    {{-- ========================================================== --}}
    {{-- AR TABLE --}}
    {{-- ========================================================== --}}
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="px-4 py-3 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
            <h2 class="text-[15px] font-semibold text-black dark:text-white">Daftar Piutang Faktur Pelanggan</h2>
            <span class="text-[13px] text-black/45 dark:text-white/45 tabular-nums">{{ $invoices->total() }} Faktur</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10">
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">No. Faktur</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Pelanggan</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Tgl. Terbit</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Jatuh Tempo</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Total Faktur</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Sisa Piutang</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Umur Piutang</th>
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
                        <td class="px-4 py-3">
                            @if(\App\Support\Context::hasPermission('invoices.view'))
                            <a href="{{ route('invoices.show', $invoice) }}" class="tabular-nums font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline">
                                {{ $invoice->invoice_number }}
                            </a>
                            @else
                            <span class="tabular-nums font-semibold text-black dark:text-white">{{ $invoice->invoice_number }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-black dark:text-white">{{ $invoice->customer?->name ?? 'Pelanggan Umum' }}</div>
                            @if($invoice->customer?->phone)
                                <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums mt-0.5">{{ $invoice->customer->phone }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-black/60 dark:text-white/60">{{ $invoice->invoice_date?->format('d M Y') ?? '-' }}</td>
                        <td class="px-4 py-3 {{ $isOverdue ? 'text-[#FF3B30] dark:text-[#FF453A] font-semibold' : 'text-black/60 dark:text-white/60' }}">
                            {{ $invoice->due_date?->format('d M Y') ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums text-black/60 dark:text-white/60">
                            Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums font-semibold text-[#34C759] dark:text-[#30D158]">
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
                            @if(\App\Support\Context::hasPermission('invoices.view'))
                            <a href="{{ route('invoices.show', $invoice) }}" class="h-7 px-2.5 rounded-[6px] text-[12px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Detail
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 text-black/15 dark:text-white/15" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <div>
                                    <p class="text-[15px] font-semibold text-black/60 dark:text-white/60">Semua Piutang Pelanggan Telah Lunas!</p>
                                    <p class="text-[13px] text-black/40 dark:text-white/40 mt-0.5">Tidak ada saldo piutang yang menunggu pembayaran</p>
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
            <span>{{ $invoices->total() }} faktur</span>
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
