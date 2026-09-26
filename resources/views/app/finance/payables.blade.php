@extends('layouts.app', ['title' => 'Hutang Usaha (AP Aging)'])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-5 pb-28 lg:pb-12">

    {{-- ========================================================== --}}
    {{-- TOOLBAR / PAGE HEADER --}}
    {{-- ========================================================== --}}
    <header class="rounded-[16px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <span class="text-black/70 dark:text-white/70 font-medium">Keuangan &amp; Kas</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <span class="text-black dark:text-white font-medium">Hutang Usaha</span>
            </nav>
            <h1 class="text-[20px] sm:text-[22px] font-bold text-black dark:text-white tracking-tight">Hutang Usaha (AP Aging)</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Pantau jadwal jatuh tempo tagihan pemasok untuk menjaga kelancaran arus kas bisnis</p>
        </div>
        <div class="flex items-center gap-2.5 w-full sm:w-auto">
            @if(\App\Support\Context::hasPermission('purchasing.bills') || \App\Support\Context::hasPermission('purchasing.manage'))
            <a href="{{ route('purchasing.bills.index') }}" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#FF9500] hover:bg-[#E8880A] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(255,149,0,0.25)]">
                <i data-lucide="receipt" class="w-4 h-4"></i>
                <span>Tagihan Supplier</span>
            </a>
            @endif
            @if(\App\Support\Context::hasPermission('master_data.suppliers') || \App\Support\Context::hasPermission('purchasing.view'))
            <a href="{{ route('suppliers.index') }}" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="truck" class="w-4 h-4 text-[#FF9500]"></i>
                <span>Daftar Pemasok</span>
            </a>
            @endif
        </div>
    </header>

    {{-- ===================================================== --}}
    {{-- FINANCE HUB NAVIGATION TABS (Apple Segmented Control) --}}
    {{-- ===================================================== --}}
    <div class="overflow-x-auto pb-1 scrollbar-none">
        <div class="inline-flex p-1 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.07] border border-black/5 dark:border-white/10 text-[13px] font-medium whitespace-nowrap">
            @if(\App\Support\Context::hasPermission('finance.cash_bank'))
            <a href="{{ route('finance.cash-bank.index') }}"
                class="px-3.5 py-1.5 rounded-[10px] {{ request()->routeIs('finance.cash-bank.index') ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }} flex items-center gap-2 transition-all">
                <i data-lucide="wallet-cards" class="w-4 h-4 {{ request()->routeIs('finance.cash-bank.index') ? 'text-[#007AFF]' : 'text-black/40 dark:text-white/40' }}"></i>
                <span>Kas &amp; Rekening</span>
            </a>

            <a href="{{ route('finance.cash-bank.ledger') }}"
                class="px-3.5 py-1.5 rounded-[10px] {{ request()->routeIs('finance.cash-bank.ledger') ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }} flex items-center gap-2 transition-all">
                <i data-lucide="book-open" class="w-4 h-4 {{ request()->routeIs('finance.cash-bank.ledger') ? 'text-[#FF9500]' : 'text-black/40 dark:text-white/40' }}"></i>
                <span>Buku Kas &amp; Mutasi</span>
            </a>
            @endif

            @if(\App\Support\Context::hasPermission('expenses.view') || \App\Support\Context::hasPermission('expenses.manage'))
            <a href="{{ route('finance.expenses.index') }}"
                class="px-3.5 py-1.5 rounded-[10px] {{ request()->routeIs('finance.expenses.*') ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }} flex items-center gap-2 transition-all">
                <i data-lucide="receipt" class="w-4 h-4 {{ request()->routeIs('finance.expenses.*') ? 'text-[#FF3B30]' : 'text-black/40 dark:text-white/40' }}"></i>
                <span>Beban Operasional</span>
            </a>
            @endif

            @if(\App\Support\Context::hasPermission('accounting.view'))
            <a href="{{ route('finance.journals.index') }}"
                class="px-3.5 py-1.5 rounded-[10px] {{ request()->routeIs('finance.journals.*') ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }} flex items-center gap-2 transition-all">
                <i data-lucide="file-spreadsheet" class="w-4 h-4 {{ request()->routeIs('finance.journals.*') ? 'text-[#5856D6]' : 'text-black/40 dark:text-white/40' }}"></i>
                <span>Jurnal Akuntansi</span>
            </a>
            @endif

            @if(\App\Support\Context::hasPermission('finance.receivables') || \App\Support\Context::hasPermission('invoices.view'))
            <a href="{{ route('finance.receivables') }}"
                class="px-3.5 py-1.5 rounded-[10px] {{ request()->routeIs('finance.receivables*') ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }} flex items-center gap-2 transition-all">
                <i data-lucide="clock" class="w-4 h-4 {{ request()->routeIs('finance.receivables*') ? 'text-[#34C759]' : 'text-black/40 dark:text-white/40' }}"></i>
                <span>Piutang (AR)</span>
            </a>
            @endif

            @if(\App\Support\Context::hasPermission('finance.payables') || \App\Support\Context::hasPermission('purchasing.bills'))
            <a href="{{ route('finance.payables') }}"
                class="px-3.5 py-1.5 rounded-[10px] {{ request()->routeIs('finance.payables*') ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }} flex items-center gap-2 transition-all">
                <i data-lucide="arrow-up-right" class="w-4 h-4 {{ request()->routeIs('finance.payables*') ? 'text-[#FF9500]' : 'text-black/40 dark:text-white/40' }}"></i>
                <span>Hutang (AP)</span>
            </a>
            @endif

            @if(\App\Support\Context::hasPermission('finance.cash_bank') || \App\Support\Context::hasPermission('accounting.view'))
            <a href="{{ route('finance.settlements.index') }}"
                class="px-3.5 py-1.5 rounded-[10px] {{ request()->routeIs('finance.settlements.*') ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }} flex items-center gap-2 transition-all">
                <i data-lucide="credit-card" class="w-4 h-4 {{ request()->routeIs('finance.settlements.*') ? 'text-[#AF52DE]' : 'text-black/40 dark:text-white/40' }}"></i>
                <span>Settlement Gateway</span>
            </a>
            @endif
        </div>
    </div>

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
    {{-- AP AGING BENTO KPI TILES                                  --}}
    {{-- ========================================================== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
        <a href="{{ route('finance.payables', array_merge(request()->except(['page']), ['bucket' => 'all'])) }}"
           class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border {{ $currentBucket === 'all' ? 'border-[#007AFF] shadow-[0_0_0_1px_#007AFF]' : 'border-black/5 dark:border-white/5' }} p-4.5 flex flex-col justify-between hover:border-[#007AFF]/50 transition-all shadow-xs group">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">Total Hutang Supplier</p>
                <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                    <i data-lucide="layers" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2.5">
                <span class="text-[20px] sm:text-[22px] font-bold tabular-nums text-black dark:text-white">Rp {{ number_format($totalPayable, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">{{ $totalOpenCount }} Tagihan Terbuka</p>
        </a>

        <a href="{{ route('finance.payables', array_merge(request()->except(['page']), ['bucket' => 'not_due'])) }}"
           class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border {{ $currentBucket === 'not_due' ? 'border-[#34C759] shadow-[0_0_0_1px_#34C759]' : 'border-black/5 dark:border-white/5' }} p-4.5 flex flex-col justify-between hover:border-[#34C759]/50 transition-all shadow-xs group">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">Belum Jatuh Tempo</p>
                <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center shrink-0">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2.5">
                <span class="text-[20px] sm:text-[22px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">Rp {{ number_format($notDue, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-[#34C759] dark:text-[#30D158] mt-1">Lancar sesuai termin</p>
        </a>

        <a href="{{ route('finance.payables', array_merge(request()->except(['page']), ['bucket' => 'overdue_1_30'])) }}"
           class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border {{ $currentBucket === 'overdue_1_30' ? 'border-[#FF9500] shadow-[0_0_0_1px_#FF9500]' : 'border-black/5 dark:border-white/5' }} p-4.5 flex flex-col justify-between hover:border-[#FF9500]/50 transition-all shadow-xs group">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">Lewat 1–30 Hari</p>
                <div class="w-8 h-8 rounded-[10px] bg-[#FF9500]/10 text-[#FF9500] dark:text-[#FF9F0A] flex items-center justify-center shrink-0">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2.5">
                <span class="text-[20px] sm:text-[22px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">Rp {{ number_format($overdue1to30, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-[#FF9500] dark:text-[#FF9F0A] mt-1">Jadwalkan pelunasan</p>
        </a>

        <a href="{{ route('finance.payables', array_merge(request()->except(['page']), ['bucket' => 'overdue_30_plus'])) }}"
           class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border {{ $currentBucket === 'overdue_30_plus' ? 'border-[#FF3B30] shadow-[0_0_0_1px_#FF3B30]' : 'border-black/5 dark:border-white/5' }} p-4.5 flex flex-col justify-between hover:border-[#FF3B30]/50 transition-all shadow-xs group">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">Lewat > 30 Hari</p>
                <div class="w-8 h-8 rounded-[10px] bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A] flex items-center justify-center shrink-0">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2.5">
                <span class="text-[20px] sm:text-[22px] font-bold tabular-nums text-[#FF3B30] dark:text-[#FF453A]">Rp {{ number_format($overdue30plus, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-[#FF3B30] dark:text-[#FF453A] mt-1">Prioritas pelunasan</p>
        </a>
    </div>

    {{-- ========================================================== --}}
    {{-- SEARCH & FILTER BAR                                       --}}
    {{-- ========================================================== --}}
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3.5 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 shadow-xs">
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
                <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no. tagihan / supplier..."
                       class="w-full h-10 sm:h-8 pl-8 pr-3 text-[16px] sm:text-[12px] bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
            </div>
            @if(request('search') || request('bucket') !== 'all')
                <a href="{{ route('finance.payables') }}" class="min-h-[44px] sm:min-h-0 h-10 sm:h-8 px-3 rounded-[8px] text-[12px] text-black/50 dark:text-white/50 hover:bg-black/5 dark:hover:bg-white/5 flex items-center justify-center transition-colors">Reset</a>
            @endif
        </form>
    </div>

    {{-- ========================================================== --}}
    {{-- AP TABLE                                                  --}}
    {{-- ========================================================== --}}
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-sm">
        <div class="px-5 py-3.5 border-b border-black/5 dark:border-white/10 flex items-center justify-between bg-black/[0.01] dark:bg-white/[0.01]">
            <h2 class="text-[15px] font-semibold text-black dark:text-white">Daftar Tagihan Hutang Supplier</h2>
            <span class="text-[12px] font-medium text-black/45 dark:text-white/45 tabular-nums">{{ $invoices->total() }} Tagihan</span>
        </div>
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02]">
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">No. Tagihan</th>
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">Pemasok / Vendor</th>
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">Tgl. Tagihan</th>
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">Jatuh Tempo</th>
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45 text-right">Total Tagihan</th>
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45 text-right">Sisa Hutang</th>
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45 text-center">Umur Hutang</th>
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45 text-right">Aksi</th>
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
                        <td class="px-4 py-3 tabular-nums font-mono font-semibold text-black dark:text-white">
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
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span> Lewat {{ $daysOverdue }} hr
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Sisa {{ $daysDiff }} hr
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if(\App\Support\Context::hasPermission('purchasing.bills') || \App\Support\Context::hasPermission('purchasing.manage') || \App\Support\Context::hasPermission('invoices.record_payment'))
                            <a href="{{ route('purchasing.bills.index') }}" class="h-8 px-3 rounded-[8px] text-[12px] font-semibold text-[#FF9500] bg-[#FF9500]/10 hover:bg-[#FF9500]/15 active:scale-[0.97] transition-all inline-flex items-center gap-1 shadow-xs">
                                <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
                                <span>Bayar</span>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center text-black/25 dark:text-white/25">
                                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                                </div>
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

        {{-- MOBILE GROUPED INSET LIST (Apple iOS HIG) --}}
        <div class="sm:hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
            @forelse($invoices as $invoice)
            @php
                $daysDiff = $invoice->due_date ? (int) now()->startOfDay()->diffInDays($invoice->due_date, false) : 0;
                $isOverdue = $daysDiff < 0;
                $daysOverdue = abs($daysDiff);
            @endphp
            <div class="p-4 space-y-3 active:bg-black/[0.02] dark:active:bg-white/[0.03] transition-colors">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="font-semibold text-[14px] text-black dark:text-white font-mono">
                            {{ $invoice->invoice_number ?? $invoice->bill_number ?? '-' }}
                        </div>
                        <div class="text-[13px] font-medium text-black/80 dark:text-white/80 mt-0.5">
                            {{ $invoice->supplier?->name ?? 'Supplier Umum' }}
                        </div>
                        <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 tabular-nums">
                            Tgl: {{ $invoice->invoice_date?->format('d M Y') ?? $invoice->created_at?->format('d M Y') ?? '-' }}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-[11px] font-medium text-black/45 dark:text-white/45">Sisa Hutang</div>
                        <div class="tabular-nums font-bold text-[15px] text-[#FF9500] dark:text-[#FF9F0A] mt-0.5">
                            Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}
                        </div>
                        <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums mt-0.5">
                            Total: Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-black/[0.03] dark:border-white/[0.04] text-[12px]">
                    <div>
                        @if($isOverdue)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span> Lewat {{ $daysOverdue }} hr
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Sisa {{ $daysDiff }} hr
                            </span>
                        @endif
                    </div>
                    <div>
                        @if(\App\Support\Context::hasPermission('purchasing.bills') || \App\Support\Context::hasPermission('purchasing.manage') || \App\Support\Context::hasPermission('invoices.record_payment'))
                        <a href="{{ route('purchasing.bills.index') }}" class="min-h-[44px] h-10 px-4 rounded-[8px] text-[12px] font-semibold text-white bg-[#FF9500] hover:bg-[#E08500] active:scale-[0.97] transition-all inline-flex items-center justify-center gap-1.5 shadow-xs">
                            <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
                            <span>Bayar</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="py-12 text-center text-[13px] text-black/40 dark:text-white/40">
                Tidak ada hutang supplier terbuka
            </div>
            @endforelse
        </div>

        @if($invoices->hasPages())
        <div class="px-5 py-3.5 border-t border-black/5 dark:border-white/10 flex items-center justify-between text-[13px] text-black/60 dark:text-white/60">
            <span>{{ $invoices->total() }} tagihan</span>
            <div class="flex items-center gap-2">
                @if($invoices->onFirstPage())
                    <span class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-black/30 dark:text-white/30 cursor-not-allowed flex items-center"><i data-lucide="chevron-left" class="w-3.5 h-3.5 mr-1"></i> Sebelumnya</span>
                @else
                    <a href="{{ $invoices->previousPageUrl() }}" class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors flex items-center"><i data-lucide="chevron-left" class="w-3.5 h-3.5 mr-1"></i> Sebelumnya</a>
                @endif
                <span class="h-8 px-2.5 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] text-black dark:text-white font-medium flex items-center tabular-nums">{{ $invoices->currentPage() }}</span>
                @if($invoices->hasMorePages())
                    <a href="{{ $invoices->nextPageUrl() }}" class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors flex items-center">Selanjutnya <i data-lucide="chevron-right" class="w-3.5 h-3.5 ml-1"></i></a>
                @else
                    <span class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-black/30 dark:text-white/30 cursor-not-allowed flex items-center">Selanjutnya <i data-lucide="chevron-right" class="w-3.5 h-3.5 ml-1"></i></span>
                @endif
            </div>
        </div>
        @endif
    </div>

</div>
@endsection
