@extends('layouts.app', ['title' => 'Piutang Usaha (AR Aging)'])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-5 pb-28 lg:pb-12">

    {{-- ========================================================== --}}
    {{-- TOOLBAR / PAGE HEADER                                      --}}
    {{-- ========================================================== --}}
    <x-module-header
        title="Piutang Usaha (AR Aging)"
        subtitle="Analisis umur piutang pelanggan dan jadwal penagihan faktur tempo untuk kelancaran arus kas">
        @if(\App\Support\Context::hasPermission('invoices.create'))
            <a href="{{ route('invoices.create') }}" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-2 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Buat Faktur Baru</span>
            </a>
        @endif
        @if(\App\Support\Context::hasPermission('invoices.view'))
            <a href="{{ route('invoices.index') }}" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-2">
                <i data-lucide="file-text" class="w-4 h-4 text-[#34C759]"></i>
                <span>Daftar Faktur</span>
            </a>
        @endif
    </x-module-header>

    {{-- ========================================================== --}}
    {{-- MODULE TABS (SSOT)                                         --}}
    {{-- ========================================================== --}}
    <x-module-tabs module="finance" />

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
    {{-- AR AGING BENTO TILES                                       --}}
    {{-- ========================================================== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
        <a href="{{ route('finance.receivables', array_merge(request()->except(['page']), ['bucket' => 'all'])) }}"
           class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border {{ $currentBucket === 'all' ? 'border-[#007AFF] shadow-[0_0_0_1.5px_#007AFF]' : 'border-black/5 dark:border-white/5' }} p-4 flex flex-col justify-between hover:border-[#007AFF]/50 transition-all shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">Total Piutang</p>
                <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <span class="text-[18px] sm:text-[22px] font-extrabold tabular-nums text-black dark:text-white">Rp {{ number_format($totalBalance, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">{{ $totalOpenCount }} Faktur Terbuka</p>
        </a>

        <a href="{{ route('finance.receivables', array_merge(request()->except(['page']), ['bucket' => 'not_due'])) }}"
           class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border {{ $currentBucket === 'not_due' ? 'border-[#34C759] shadow-[0_0_0_1.5px_#34C759]' : 'border-black/5 dark:border-white/5' }} p-4 flex flex-col justify-between hover:border-[#34C759]/50 transition-all shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">Lancar (Belum Tempo)</p>
                <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <span class="text-[18px] sm:text-[22px] font-extrabold tabular-nums text-[#34C759] dark:text-[#30D158]">Rp {{ number_format($notDue, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-[#34C759] dark:text-[#30D158] mt-0.5">Sesuai termin penagihan</p>
        </a>

        <a href="{{ route('finance.receivables', array_merge(request()->except(['page']), ['bucket' => 'overdue_1_30'])) }}"
           class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border {{ $currentBucket === 'overdue_1_30' ? 'border-[#FF9500] shadow-[0_0_0_1.5px_#FF9500]' : 'border-black/5 dark:border-white/5' }} p-4 flex flex-col justify-between hover:border-[#FF9500]/50 transition-all shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">Lewat 1–30 Hari</p>
                <div class="w-8 h-8 rounded-[10px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <span class="text-[18px] sm:text-[22px] font-extrabold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">Rp {{ number_format($overdue1to30, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-[#FF9500] dark:text-[#FF9F0A] mt-0.5">Perlu follow-up penagihan</p>
        </a>

        <a href="{{ route('finance.receivables', array_merge(request()->except(['page']), ['bucket' => 'overdue_30_plus'])) }}"
           class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border {{ $currentBucket === 'overdue_30_plus' ? 'border-[#FF3B30] shadow-[0_0_0_1.5px_#FF3B30]' : 'border-black/5 dark:border-white/5' }} p-4 flex flex-col justify-between hover:border-[#FF3B30]/50 transition-all shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">Lewat &gt; 30 Hari</p>
                <div class="w-8 h-8 rounded-[10px] bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <span class="text-[18px] sm:text-[22px] font-extrabold tabular-nums text-[#FF3B30] dark:text-[#FF453A]">Rp {{ number_format($overdue30plus, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-[#FF3B30] dark:text-[#FF453A] mt-0.5">Prioritas penagihan intensif</p>
        </a>
    </div>

    {{-- ========================================================== --}}
    {{-- SEARCH & FILTER BAR                                        --}}
    {{-- ========================================================== --}}
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3.5 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 shadow-sm">
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 text-[12px]">
            <a href="{{ route('finance.receivables', array_merge(request()->except(['page', 'bucket']), ['bucket' => 'all'])) }}"
               class="px-3 py-1.5 rounded-[8px] font-medium transition-colors whitespace-nowrap {{ $currentBucket === 'all' ? 'bg-[#007AFF] text-white font-semibold' : 'text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5' }}">
                Semua
            </a>
            <a href="{{ route('finance.receivables', array_merge(request()->except(['page', 'bucket']), ['bucket' => 'not_due'])) }}"
               class="px-3 py-1.5 rounded-[8px] font-medium transition-colors whitespace-nowrap {{ $currentBucket === 'not_due' ? 'bg-[#34C759] text-white font-semibold' : 'text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5' }}">
                Lancar
            </a>
            <a href="{{ route('finance.receivables', array_merge(request()->except(['page', 'bucket']), ['bucket' => 'overdue_1_30'])) }}"
               class="px-3 py-1.5 rounded-[8px] font-medium transition-colors whitespace-nowrap {{ $currentBucket === 'overdue_1_30' ? 'bg-[#FF9500] text-white font-semibold' : 'text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5' }}">
                Lewat 1–30 Hari
            </a>
            <a href="{{ route('finance.receivables', array_merge(request()->except(['page', 'bucket']), ['bucket' => 'overdue_30_plus'])) }}"
               class="px-3 py-1.5 rounded-[8px] font-medium transition-colors whitespace-nowrap {{ $currentBucket === 'overdue_30_plus' ? 'bg-[#FF3B30] text-white font-semibold' : 'text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5' }}">
                Lewat &gt; 30 Hari
            </a>
        </div>
        <form method="GET" action="{{ route('finance.receivables') }}" class="flex items-center gap-2">
            <input type="hidden" name="bucket" value="{{ $currentBucket }}">
            <div class="relative flex-1 sm:w-64">
                <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no. faktur / pelanggan..."
                       class="w-full h-11 sm:h-9 pl-8 pr-3 text-[16px] sm:text-[12px] bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
            </div>
            @if(request('search') || request('bucket') !== 'all')
                <a href="{{ route('finance.receivables') }}" class="h-11 sm:h-9 px-3 rounded-[10px] text-[12px] font-medium text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5 flex items-center">Reset</a>
            @endif
        </form>
    </div>

    {{-- ========================================================== --}}
    {{-- AR TABLE (DESKTOP)                                         --}}
    {{-- ========================================================== --}}
    <div class="hidden sm:block rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
            <h2 class="text-[15px] font-bold text-black dark:text-white">Daftar Piutang Faktur Pelanggan</h2>
            <span class="text-[12px] font-bold text-black/50 dark:text-white/50 tabular-nums px-2.5 py-0.5 rounded-full bg-black/[0.04] dark:bg-white/[0.06]">{{ $invoices->total() }} Faktur</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02]">
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-black/45 dark:text-white/45">No. Faktur</th>
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-black/45 dark:text-white/45">Pelanggan</th>
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-black/45 dark:text-white/45">Tgl. Terbit</th>
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-black/45 dark:text-white/45">Jatuh Tempo</th>
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-black/45 dark:text-white/45 text-right">Total Faktur</th>
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-black/45 dark:text-white/45 text-right">Sisa Piutang</th>
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-black/45 dark:text-white/45 text-center">Umur Piutang</th>
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-black/45 dark:text-white/45 text-right">Aksi</th>
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
                        <td class="px-4 py-3.5">
                            @if(\App\Support\Context::hasPermission('invoices.view'))
                            <a href="{{ route('invoices.show', $invoice) }}" class="tabular-nums font-bold text-[#007AFF] dark:text-[#0A84FF] hover:underline">
                                {{ $invoice->invoice_number }}
                            </a>
                            @else
                            <span class="tabular-nums font-bold text-black dark:text-white">{{ $invoice->invoice_number }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-semibold text-black dark:text-white">{{ $invoice->customer?->name ?? 'Pelanggan Umum' }}</div>
                            @if($invoice->customer?->phone)
                                <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums mt-0.5">{{ $invoice->customer->phone }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-black/60 dark:text-white/60 tabular-nums">{{ $invoice->invoice_date?->format('d M Y') ?? '-' }}</td>
                        <td class="px-4 py-3.5 tabular-nums {{ $isOverdue ? 'text-[#FF3B30] dark:text-[#FF453A] font-semibold' : 'text-black/60 dark:text-white/60' }}">
                            {{ $invoice->due_date?->format('d M Y') ?? '-' }}
                        </td>
                        <td class="px-4 py-3.5 text-right tabular-nums text-black/60 dark:text-white/60 font-medium">
                            Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 text-right tabular-nums font-bold text-[#34C759] dark:text-[#30D158] text-[14px]">
                            Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            @if($isOverdue)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span> Lewat {{ $daysOverdue }} hr
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Sisa {{ $daysDiff }} hr
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-right">
                            @if(\App\Support\Context::hasPermission('invoices.view'))
                            <a href="{{ route('invoices.show', $invoice) }}" class="h-8 px-3 rounded-[8px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition-colors inline-flex items-center gap-1.5">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                <span>Detail</span>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-12 h-12 rounded-[14px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                                    <i data-lucide="check-circle-2" class="w-6 h-6"></i>
                                </div>
                                <div>
                                    <p class="text-[15px] font-bold text-black/70 dark:text-white/70">Semua Piutang Pelanggan Telah Lunas!</p>
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
        <div class="px-5 py-3.5 border-t border-black/5 dark:border-white/10 flex items-center justify-between text-[13px] text-black/60 dark:text-white/60">
            <span>{{ $invoices->total() }} faktur</span>
            <div class="flex items-center gap-2">
                @if($invoices->onFirstPage())
                    <span class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-black/30 dark:text-white/30 cursor-not-allowed flex items-center"><i data-lucide="chevron-left" class="w-3.5 h-3.5 mr-1"></i> Sebelumnya</span>
                @else
                    <a href="{{ $invoices->previousPageUrl() }}" class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors flex items-center"><i data-lucide="chevron-left" class="w-3.5 h-3.5 mr-1"></i> Sebelumnya</a>
                @endif
                <span class="h-8 px-2.5 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] text-black dark:text-white font-bold flex items-center tabular-nums">{{ $invoices->currentPage() }}</span>
                @if($invoices->hasMorePages())
                    <a href="{{ $invoices->nextPageUrl() }}" class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors flex items-center">Selanjutnya <i data-lucide="chevron-right" class="w-3.5 h-3.5 ml-1"></i></a>
                @else
                    <span class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-black/30 dark:text-white/30 cursor-not-allowed flex items-center">Selanjutnya <i data-lucide="chevron-right" class="w-3.5 h-3.5 ml-1"></i></span>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- ========================================================== --}}
    {{-- AR LIST CARDS (MOBILE ONLY)                                --}}
    {{-- ========================================================== --}}
    <div class="sm:hidden space-y-3">
        <div class="flex items-center justify-between px-1">
            <h2 class="text-[14px] font-bold text-black dark:text-white">Daftar Piutang Faktur</h2>
            <span class="text-[12px] text-black/45 dark:text-white/45 tabular-nums">{{ $invoices->total() }} Faktur</span>
        </div>

        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06] shadow-sm">
            @forelse($invoices as $invoice)
            @php
                $daysDiff = $invoice->due_date ? (int) now()->startOfDay()->diffInDays($invoice->due_date, false) : 0;
                $isOverdue = $daysDiff < 0;
                $daysOverdue = abs($daysDiff);
            @endphp
            <div class="p-4 space-y-2.5 active:bg-black/[0.02] dark:active:bg-white/[0.03] transition-colors">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        @if(\App\Support\Context::hasPermission('invoices.view'))
                        <a href="{{ route('invoices.show', $invoice) }}" class="tabular-nums font-bold text-[14px] text-[#007AFF] dark:text-[#0A84FF] hover:underline flex items-center gap-1">
                            {{ $invoice->invoice_number }}
                            <i data-lucide="chevron-right" class="w-3.5 h-3.5 inline"></i>
                        </a>
                        @else
                        <span class="tabular-nums font-bold text-[14px] text-black dark:text-white">{{ $invoice->invoice_number }}</span>
                        @endif
                        <p class="text-[13px] font-semibold text-black dark:text-white mt-0.5">{{ $invoice->customer?->name ?? 'Pelanggan Umum' }}</p>
                        @if($invoice->customer?->phone)
                        <p class="text-[11px] text-black/45 dark:text-white/45 tabular-nums flex items-center gap-1 mt-0.5">
                            <i data-lucide="phone" class="w-3 h-3 text-black/40 dark:text-white/40"></i>
                            {{ $invoice->customer->phone }}
                        </p>
                        @endif
                    </div>
                    <div>
                        @if($isOverdue)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span> Lewat {{ $daysOverdue }} hr
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Sisa {{ $daysDiff }} hr
                            </span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 text-[12px] bg-black/[0.02] dark:bg-white/[0.02] p-3 rounded-[12px]">
                    <div>
                        <span class="text-[10px] uppercase font-semibold text-black/40 dark:text-white/40 block">Tgl. Terbit</span>
                        <span class="text-black/70 dark:text-white/70 tabular-nums">{{ $invoice->invoice_date?->format('d M Y') ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-semibold text-black/40 dark:text-white/40 block">Jatuh Tempo</span>
                        <span class="tabular-nums {{ $isOverdue ? 'text-[#FF3B30] dark:text-[#FF453A] font-semibold' : 'text-black/70 dark:text-white/70' }}">
                            {{ $invoice->due_date?->format('d M Y') ?? '-' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-semibold text-black/40 dark:text-white/40 block">Total Faktur</span>
                        <span class="tabular-nums text-black/60 dark:text-white/60 font-medium">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-semibold text-black/40 dark:text-white/40 block">Sisa Piutang</span>
                        <span class="tabular-nums font-bold text-[#34C759] dark:text-[#30D158] text-[13px]">Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}</span>
                    </div>
                </div>

                @if(\App\Support\Context::hasPermission('invoices.view'))
                <div class="flex items-center justify-end pt-1">
                    <a href="{{ route('invoices.show', $invoice) }}" class="h-9 px-4 rounded-[10px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition-colors inline-flex items-center gap-1.5">
                        <i data-lucide="file-check" class="w-3.5 h-3.5"></i>
                        <span>Lihat Faktur &amp; Catat Bayar</span>
                    </a>
                </div>
                @endif
            </div>
            @empty
            <div class="py-12 px-4 text-center">
                <div class="flex flex-col items-center gap-2.5">
                    <div class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-[14px] font-bold text-black/70 dark:text-white/70">Semua Piutang Telah Lunas!</p>
                        <p class="text-[12px] text-black/40 dark:text-white/40 mt-0.5">Tidak ada saldo piutang yang menunggu pembayaran</p>
                    </div>
                </div>
            </div>
            @endforelse
        </div>

        @if($invoices->hasPages())
        <div class="p-3 bg-white dark:bg-[#1C1C1E] rounded-[16px] border border-black/5 dark:border-white/5 flex items-center justify-between text-[12px]">
            <span class="text-black/50 dark:text-white/50">{{ $invoices->total() }} faktur</span>
            <div class="flex items-center gap-1.5">
                @if($invoices->onFirstPage())
                    <span class="h-8 px-2.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] text-black/30 dark:text-white/30 cursor-not-allowed flex items-center text-[12px]"><i data-lucide="chevron-left" class="w-3.5 h-3.5 mr-0.5"></i> Prev</span>
                @else
                    <a href="{{ $invoices->previousPageUrl() }}" class="h-8 px-2.5 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] font-medium flex items-center text-[12px]"><i data-lucide="chevron-left" class="w-3.5 h-3.5 mr-0.5"></i> Prev</a>
                @endif
                <span class="h-8 px-2.5 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] text-black dark:text-white font-bold flex items-center tabular-nums text-[12px]">{{ $invoices->currentPage() }}</span>
                @if($invoices->hasMorePages())
                    <a href="{{ $invoices->nextPageUrl() }}" class="h-8 px-2.5 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] font-medium flex items-center text-[12px]">Next <i data-lucide="chevron-right" class="w-3.5 h-3.5 ml-0.5"></i></a>
                @else
                    <span class="h-8 px-2.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] text-black/30 dark:text-white/30 cursor-not-allowed flex items-center text-[12px]">Next <i data-lucide="chevron-right" class="w-3.5 h-3.5 ml-0.5"></i></span>
                @endif
            </div>
        </div>
        @endif
    </div>

</div>
@endsection

