@extends('layouts.app', ['title' => 'Kas & Rekening Bank'])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-5 pb-12"
     x-data="{ showTransferModal: false, showInflowModal: false, showOutflowModal: false }">

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
                <span class="text-black dark:text-white font-medium">Kas & Bank</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Kas & Rekening Bank</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Pantau saldo rekening kasir, bank transfer, dan mutasi arus kas operasional</p>
        </div>
        <div class="flex items-center flex-wrap gap-2 w-full sm:w-auto">
            @if(\App\Support\Context::hasPermission('finance.cash_bank'))
            <button type="button" @click="showInflowModal = true"
                class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#34C759] hover:bg-[#2DBE50] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(52,199,89,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/></svg>
                <span>Kas Masuk</span>
            </button>
            <button type="button" @click="showOutflowModal = true"
                class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(255,59,48,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/></svg>
                <span>Kas Keluar</span>
            </button>
            <button type="button" @click="showTransferModal = true"
                class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                <span>Transfer</span>
            </button>
            @endif
            @if(\App\Support\Context::hasPermission('accounting.view') || \App\Support\Context::hasPermission('finance.cash_bank'))
            <a href="{{ route('finance.cash-bank.ledger') }}"
                class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-[#FF9500]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0118 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                <span>Buku Kas & Ledger</span>
            </a>
            @endif
        </div>
    </header>

    {{-- Flash Message --}}
    @if(session('success'))
    <div class="rounded-[12px] bg-[#34C759]/10 border border-[#34C759]/20 px-4 py-3 flex items-center gap-2.5 text-[13px] font-medium text-[#248A3D] dark:text-[#30D158]">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error') || $errors->any())
    <div class="rounded-[12px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 px-4 py-3 flex items-start gap-2.5 text-[13px] font-medium text-[#C41E17] dark:text-[#FF453A]">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
        <div class="space-y-0.5">
            @if(session('error'))
                <div>{{ session('error') }}</div>
            @endif
            @foreach($errors->all() as $err)
                <div>{{ $err }}</div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ========================================================== --}}
    {{-- ACCOUNT CARDS --}}
    {{-- ========================================================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        @forelse($accounts as $account)
        @php
            $icon = match($account->type) {
                'bank' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z"/>',
                'qris', 'ewallet' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z"/>',
                'petty_cash' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                default => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18-3a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3h18V6z"/>',
            };
            $color = match($account->type) {
                'bank' => '#007AFF',
                'qris', 'ewallet' => '#BF5AF2',
                'petty_cash' => '#FF9500',
                default => '#34C759',
            };
        @endphp
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 hover:shadow-[0_2px_12px_rgba(0,0,0,0.06)] transition-shadow">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[12px] flex items-center justify-center" style="background:{{ $color }}18">
                        <svg class="w-5 h-5" fill="none" stroke="{{ $color }}" stroke-width="1.5" viewBox="0 0 24 24">{!! $icon !!}</svg>
                    </div>
                    <div>
                        <h3 class="text-[15px] font-semibold text-black dark:text-white leading-tight">{{ $account->name }}</h3>
                        <span class="text-[11px] font-semibold uppercase tracking-wide" style="color:{{ $color }}">{{ strtoupper($account->type) }}</span>
                    </div>
                </div>
                @if($account->account_number)
                    <span class="text-[11px] tabular-nums text-black/40 dark:text-white/40">{{ $account->account_number }}</span>
                @endif
            </div>
            <div class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06] flex items-baseline justify-between">
                <div>
                    <p class="text-[11px] text-black/45 dark:text-white/45 font-medium uppercase tracking-wide">Saldo Tersedia</p>
                    <p class="text-[22px] font-bold tabular-nums text-black dark:text-white mt-0.5">Rp {{ number_format($account->current_balance, 0, ',', '.') }}</p>
                </div>
                <a href="{{ route('finance.cash-bank.ledger', ['account_id' => $account->id]) }}"
                    class="text-[13px] font-medium text-[#007AFF] hover:underline flex items-center gap-0.5">
                    Mutasi <span>›</span>
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 py-12 text-center">
            <p class="text-[15px] font-semibold text-black/60 dark:text-white/60">Belum ada akun kas atau rekening bank</p>
            <p class="text-[13px] text-black/40 dark:text-white/40 mt-1">Akun kas akan otomatis dibuat saat transaksi kasir pertama</p>
        </div>
        @endforelse
    </div>

    {{-- ========================================================== --}}
    {{-- POS PAYMENT CHANNELS BREAKDOWN --}}
    {{-- ========================================================== --}}
    @php
        $cashAmount = (float) ($posSummaryByMethod['cash']->total_amount ?? 0);
        $cashCount = (int) ($posSummaryByMethod['cash']->total_count ?? 0);

        $qrisAmount = (float) ($posSummaryByMethod['qris']->total_amount ?? 0);
        $qrisCount = (int) ($posSummaryByMethod['qris']->total_count ?? 0);

        $trfAmount = (float) (($posSummaryByMethod['transfer']->total_amount ?? 0) + ($posSummaryByMethod['bank_transfer']->total_amount ?? 0));
        $trfCount = (int) (($posSummaryByMethod['transfer']->total_count ?? 0) + ($posSummaryByMethod['bank_transfer']->total_count ?? 0));

        $edcAmount = (float) (($posSummaryByMethod['edc_debit']->total_amount ?? 0) + ($posSummaryByMethod['edc_credit']->total_amount ?? 0));
        $edcCount = (int) (($posSummaryByMethod['edc_debit']->total_count ?? 0) + ($posSummaryByMethod['edc_credit']->total_count ?? 0));

        $creditAmount = (float) ($posSummaryByMethod['customer_credit']->total_amount ?? 0);
        $creditCount = (int) ($posSummaryByMethod['customer_credit']->total_count ?? 0);

        $totalPos = $totalPosRevenue ?? ($cashAmount + $qrisAmount + $trfAmount + $edcAmount + $creditAmount);
        $curPeriod = $activePeriod ?? request('period', 'today');
    @endphp

    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 space-y-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-[15px] font-semibold text-black dark:text-white">Pemasukan Kasir POS per Metode Pembayaran</h2>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                        Auto-Tracking
                    </span>
                </div>
                <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Tracking penerimaan omset kasir berdasarkan saluran pembayaran masuk</p>
            </div>
            <div class="flex items-center gap-1 bg-black/[0.04] dark:bg-white/[0.06] p-1 rounded-[10px]">
                <a href="{{ route('finance.cash-bank.index', ['period' => 'today']) }}"
                   class="px-3 py-1 rounded-[7px] text-[12px] font-medium transition-colors {{ $curPeriod === 'today' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                    Hari Ini
                </a>
                <a href="{{ route('finance.cash-bank.index', ['period' => 'month']) }}"
                   class="px-3 py-1 rounded-[7px] text-[12px] font-medium transition-colors {{ $curPeriod === 'month' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                    Bulan Ini
                </a>
                <a href="{{ route('finance.cash-bank.index', ['period' => 'all']) }}"
                   class="px-3 py-1 rounded-[7px] text-[12px] font-medium transition-colors {{ $curPeriod === 'all' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                    Semua Waktu
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            {{-- 1. Cash --}}
            <a href="{{ route('finance.cash-bank.ledger', ['method' => 'cash']) }}"
               class="rounded-[12px] p-3.5 bg-[#34C759]/[0.06] dark:bg-[#34C759]/[0.10] border border-[#34C759]/20 hover:border-[#34C759]/40 transition-all group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-[#248A3D] dark:text-[#30D158] uppercase tracking-wider">Tunai (Cash)</span>
                        <span class="w-6 h-6 rounded-full bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] flex items-center justify-center text-[10px] font-bold">💵</span>
                    </div>
                    <div class="text-[18px] font-bold tabular-nums text-black dark:text-white mt-2">
                        Rp {{ number_format($cashAmount, 0, ',', '.') }}
                    </div>
                </div>
                <div class="mt-2.5 pt-2 border-t border-[#34C759]/15 flex items-center justify-between text-[11px] text-black/50 dark:text-white/50">
                    <span>{{ $cashCount }} Transaksi</span>
                    <span class="text-[#248A3D] dark:text-[#30D158] group-hover:translate-x-0.5 transition-transform font-medium">Buku Kas ›</span>
                </div>
            </a>

            {{-- 2. QRIS --}}
            <a href="{{ route('finance.cash-bank.ledger', ['method' => 'qris']) }}"
               class="rounded-[12px] p-3.5 bg-[#BF5AF2]/[0.06] dark:bg-[#BF5AF2]/[0.10] border border-[#BF5AF2]/20 hover:border-[#BF5AF2]/40 transition-all group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-[#8944AB] dark:text-[#BF5AF2] uppercase tracking-wider">QRIS / E-Wallet</span>
                        <span class="w-6 h-6 rounded-full bg-[#BF5AF2]/20 text-[#8944AB] dark:text-[#BF5AF2] flex items-center justify-center text-[10px] font-bold">📱</span>
                    </div>
                    <div class="text-[18px] font-bold tabular-nums text-black dark:text-white mt-2">
                        Rp {{ number_format($qrisAmount, 0, ',', '.') }}
                    </div>
                </div>
                <div class="mt-2.5 pt-2 border-t border-[#BF5AF2]/15 flex items-center justify-between text-[11px] text-black/50 dark:text-white/50">
                    <span>{{ $qrisCount }} Transaksi</span>
                    <span class="text-[#8944AB] dark:text-[#BF5AF2] group-hover:translate-x-0.5 transition-transform font-medium">Ledger ›</span>
                </div>
            </a>

            {{-- 3. Transfer Bank --}}
            <a href="{{ route('finance.cash-bank.ledger', ['method' => 'transfer']) }}"
               class="rounded-[12px] p-3.5 bg-[#007AFF]/[0.06] dark:bg-[#007AFF]/[0.10] border border-[#007AFF]/20 hover:border-[#007AFF]/40 transition-all group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-[#007AFF] dark:text-[#0A84FF] uppercase tracking-wider">Transfer Bank</span>
                        <span class="w-6 h-6 rounded-full bg-[#007AFF]/20 text-[#007AFF] flex items-center justify-center text-[10px] font-bold">🏦</span>
                    </div>
                    <div class="text-[18px] font-bold tabular-nums text-black dark:text-white mt-2">
                        Rp {{ number_format($trfAmount, 0, ',', '.') }}
                    </div>
                </div>
                <div class="mt-2.5 pt-2 border-t border-[#007AFF]/15 flex items-center justify-between text-[11px] text-black/50 dark:text-white/50">
                    <span>{{ $trfCount }} Transaksi</span>
                    <span class="text-[#007AFF] group-hover:translate-x-0.5 transition-transform font-medium">Ledger ›</span>
                </div>
            </a>

            {{-- 4. EDC Card --}}
            <a href="{{ route('finance.cash-bank.ledger', ['method' => 'edc']) }}"
               class="rounded-[12px] p-3.5 bg-[#FF9500]/[0.06] dark:bg-[#FF9500]/[0.10] border border-[#FF9500]/20 hover:border-[#FF9500]/40 transition-all group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-[#B25E00] dark:text-[#FF9F0A] uppercase tracking-wider">Mesin EDC</span>
                        <span class="w-6 h-6 rounded-full bg-[#FF9500]/20 text-[#B25E00] dark:text-[#FF9F0A] flex items-center justify-center text-[10px] font-bold">💳</span>
                    </div>
                    <div class="text-[18px] font-bold tabular-nums text-black dark:text-white mt-2">
                        Rp {{ number_format($edcAmount, 0, ',', '.') }}
                    </div>
                </div>
                <div class="mt-2.5 pt-2 border-t border-[#FF9500]/15 flex items-center justify-between text-[11px] text-black/50 dark:text-white/50">
                    <span>{{ $edcCount }} Transaksi</span>
                    <span class="text-[#B25E00] dark:text-[#FF9F0A] group-hover:translate-x-0.5 transition-transform font-medium">Ledger ›</span>
                </div>
            </a>

            {{-- 5. Customer Credit --}}
            <a href="{{ route('finance.receivables') }}"
               class="rounded-[12px] p-3.5 bg-[#FF3B30]/[0.06] dark:bg-[#FF3B30]/[0.10] border border-[#FF3B30]/20 hover:border-[#FF3B30]/40 transition-all group flex flex-col justify-between col-span-2 sm:col-span-1">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-[#C41E17] dark:text-[#FF453A] uppercase tracking-wider">Kasbon (Credit)</span>
                        <span class="w-6 h-6 rounded-full bg-[#FF3B30]/20 text-[#C41E17] dark:text-[#FF453A] flex items-center justify-center text-[10px] font-bold">📋</span>
                    </div>
                    <div class="text-[18px] font-bold tabular-nums text-black dark:text-white mt-2">
                        Rp {{ number_format($creditAmount, 0, ',', '.') }}
                    </div>
                </div>
                <div class="mt-2.5 pt-2 border-t border-[#FF3B30]/15 flex items-center justify-between text-[11px] text-black/50 dark:text-white/50">
                    <span>{{ $creditCount }} Piutang</span>
                    <span class="text-[#C41E17] dark:text-[#FF453A] group-hover:translate-x-0.5 transition-transform font-medium">Piutang ›</span>
                </div>
            </a>
        </div>

        {{-- Revenue Summary Bar --}}
        <div class="pt-3 border-t border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="text-[12px] font-medium text-black/60 dark:text-white/60">Total Omset Kasir Terbayar:</span>
                <span class="text-[16px] font-bold tabular-nums text-black dark:text-white">Rp {{ number_format($totalPos, 0, ',', '.') }}</span>
            </div>
            @if($totalPos > 0)
            <div class="flex items-center gap-3 text-[11px] text-black/50 dark:text-white/50">
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-[#34C759]"></span> Tunai: {{ round(($cashAmount / $totalPos) * 100) }}%</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-[#BF5AF2]"></span> QRIS: {{ round(($qrisAmount / $totalPos) * 100) }}%</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-[#007AFF]"></span> Transfer: {{ round(($trfAmount / $totalPos) * 100) }}%</span>
                @if($edcAmount > 0)
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-[#FF9500]"></span> EDC: {{ round(($edcAmount / $totalPos) * 100) }}%</span>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- RECENT TRANSACTIONS TABLE --}}
    {{-- ========================================================== --}}
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="px-4 py-3 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
            <h2 class="text-[15px] font-semibold text-black dark:text-white">Mutasi Transaksi Kas Terbaru</h2>
            @if(\App\Support\Context::hasPermission('accounting.view') || \App\Support\Context::hasPermission('finance.cash_bank'))
            <a href="{{ route('finance.cash-bank.ledger') }}" class="text-[13px] font-medium text-[#007AFF] hover:underline flex items-center gap-0.5">
                Lihat Seluruh Buku Kas <span>›</span>
            </a>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px] min-w-[620px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10">
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Tanggal</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Akun Rekening</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Arus</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Keterangan / Referensi</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Nominal</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($transactions as $transaction)
                    @php
                        $isOut = $transaction->isOutflow();
                        $isTrf = $transaction->isTransfer();
                    @endphp
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3 tabular-nums text-black/60 dark:text-white/60">{{ $transaction->transaction_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 font-medium text-black dark:text-white">{{ $transaction->cashAccount->name }}</td>
                        <td class="px-4 py-3">
                            @if($isOut && $isTrf)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/10 text-[#C41E17] dark:text-[#FF453A]">
                                    <span class="w-1 h-1 rounded-full bg-[#FF3B30]"></span> Transfer Keluar
                                </span>
                            @elseif(!$isOut && $isTrf)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]">
                                    <span class="w-1 h-1 rounded-full bg-[#007AFF]"></span> Transfer Masuk
                                </span>
                            @elseif($isOut)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/10 text-[#C41E17] dark:text-[#FF453A]">
                                    <span class="w-1 h-1 rounded-full bg-[#FF3B30]"></span> Keluar
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]">
                                    <span class="w-1 h-1 rounded-full bg-[#34C759]"></span> Masuk
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-black/70 dark:text-white/70">{{ $transaction->description }}</td>
                        <td class="px-4 py-3 text-right tabular-nums font-semibold {{ $isOut ? 'text-[#FF3B30] dark:text-[#FF453A]' : 'text-[#34C759] dark:text-[#30D158]' }}">
                            {{ $isOut ? '−' : '+' }} Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums font-bold text-black dark:text-white">
                            Rp {{ number_format($transaction->balance_after, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-[13px] text-black/40 dark:text-white/40">
                            Belum ada riwayat mutasi kas yang tercatat
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- MODAL HELPER (shared style) --}}
    {{-- ========================================================== --}}
    @php
    $modalInputCls = 'w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition';
    $modalSelectCls = 'w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition';
    $modalLabelCls = 'block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5';
    @endphp

    @if(\App\Support\Context::hasPermission('finance.cash_bank'))
    {{-- MODAL 1 — Kas Masuk --}}
    <div x-show="showInflowModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="w-full max-w-md rounded-[20px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.2)]"
            @click.away="showInflowModal = false"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
            <div class="flex items-center justify-between px-5 pt-5 pb-4 border-b border-black/5 dark:border-white/10">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-[#34C759]/15 text-[#34C759] flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/></svg>
                    </div>
                    <h3 class="text-[17px] font-semibold text-black dark:text-white">Catat Penerimaan Kas Masuk</h3>
                </div>
                <button type="button" @click="showInflowModal = false" class="w-8 h-8 rounded-full bg-black/[0.06] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:bg-black/10 dark:hover:bg-white/12 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('finance.cash-bank.inflow') }}" class="px-5 py-4 space-y-3">
                @csrf
                <div class="space-y-1.5">
                    <label class="{{ $modalLabelCls }}">Rekening Kas / Bank Penerima</label>
                    <select name="account_id" class="{{ $modalSelectCls }}">
                        <option value="">Otomatis (Berdasarkan Metode)</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }} ({{ strtoupper($acc->type) }}) — Rp {{ number_format($acc->current_balance, 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="{{ $modalLabelCls }}">Metode / Jenis Rekening</label>
                    <select name="account_method" class="{{ $modalSelectCls }}">
                        <option value="cash">Kas Tunai (Cash)</option>
                        <option value="bank_transfer">Rekening Bank</option>
                        <option value="qris">QRIS / E-Wallet</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="{{ $modalLabelCls }}">Nominal (Rp) *</label>
                    <input name="amount" type="number" min="1" step="0.01" required placeholder="500000" class="{{ $modalInputCls }} tabular-nums font-semibold">
                </div>
                <div class="space-y-1.5">
                    <label class="{{ $modalLabelCls }}">Keterangan Penerimaan *</label>
                    <input name="description" required placeholder="Contoh: Setoran modal awal atau pendapatan non-POS" class="{{ $modalInputCls }}">
                </div>
                <div class="flex items-center gap-2 pt-2 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showInflowModal = false" class="flex-1 h-11 rounded-[10px] text-[15px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 active:opacity-70 transition-all">Batal</button>
                    <button type="submit" class="flex-1 h-11 rounded-[10px] text-[15px] font-semibold text-white bg-[#34C759] hover:bg-[#2DBE50] active:scale-[0.97] active:opacity-80 transition-all shadow-[0_1px_2px_rgba(52,199,89,0.25)]">Simpan Kas Masuk</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 2 — Kas Keluar --}}
    <div x-show="showOutflowModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="w-full max-w-md rounded-[20px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.2)]"
            @click.away="showOutflowModal = false"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
            <div class="flex items-center justify-between px-5 pt-5 pb-4 border-b border-black/5 dark:border-white/10">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-[#FF3B30]/12 text-[#FF3B30] flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/></svg>
                    </div>
                    <h3 class="text-[17px] font-semibold text-black dark:text-white">Catat Pengeluaran Kas Keluar</h3>
                </div>
                <button type="button" @click="showOutflowModal = false" class="w-8 h-8 rounded-full bg-black/[0.06] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:bg-black/10 dark:hover:bg-white/12 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('finance.cash-bank.outflow') }}" class="px-5 py-4 space-y-3">
                @csrf
                <div class="space-y-1.5">
                    <label class="{{ $modalLabelCls }}">Sumber Rekening Kas / Bank</label>
                    <select name="account_id" class="{{ $modalSelectCls }}">
                        <option value="">Otomatis (Berdasarkan Metode)</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }} ({{ strtoupper($acc->type) }}) — Saldo: Rp {{ number_format($acc->current_balance, 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="{{ $modalLabelCls }}">Metode / Jenis Rekening</label>
                    <select name="account_method" class="{{ $modalSelectCls }}">
                        <option value="cash">Kas Tunai (Cash)</option>
                        <option value="bank_transfer">Rekening Bank</option>
                        <option value="qris">QRIS / E-Wallet</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="{{ $modalLabelCls }}">Nominal (Rp) *</label>
                    <input name="amount" type="number" min="1" step="0.01" required placeholder="250000" class="{{ $modalInputCls }} tabular-nums font-semibold">
                </div>
                <div class="space-y-1.5">
                    <label class="{{ $modalLabelCls }}">Keterangan Pengeluaran *</label>
                    <input name="description" required placeholder="Contoh: Pengambilan prive atau pengeluaran khusus" class="{{ $modalInputCls }}">
                </div>
                <div class="flex items-center gap-2 pt-2 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showOutflowModal = false" class="flex-1 h-11 rounded-[10px] text-[15px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 active:opacity-70 transition-all">Batal</button>
                    <button type="submit" class="flex-1 h-11 rounded-[10px] text-[15px] font-semibold text-white bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.97] active:opacity-80 transition-all shadow-[0_1px_2px_rgba(255,59,48,0.25)]">Simpan Kas Keluar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 3 — Transfer Antar Akun --}}
    <div x-show="showTransferModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="w-full max-w-md rounded-[20px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.2)]"
            @click.away="showTransferModal = false"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
            <div class="flex items-center justify-between px-5 pt-5 pb-4 border-b border-black/5 dark:border-white/10">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-[#007AFF]/12 text-[#007AFF] flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                    </div>
                    <h3 class="text-[17px] font-semibold text-black dark:text-white">Transfer Antar Rekening</h3>
                </div>
                <button type="button" @click="showTransferModal = false" class="w-8 h-8 rounded-full bg-black/[0.06] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:bg-black/10 dark:hover:bg-white/12 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('finance.cash-bank.transfer') }}" class="px-5 py-4 space-y-3">
                @csrf
                <div class="space-y-1.5">
                    <label class="{{ $modalLabelCls }}">Dari Akun Asal *</label>
                    <select name="from_account_id" required class="{{ $modalSelectCls }}">
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }} (Rp {{ number_format($account->current_balance, 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="{{ $modalLabelCls }}">Ke Akun Tujuan *</label>
                    <select name="to_account_id" required class="{{ $modalSelectCls }}">
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }} (Rp {{ number_format($account->current_balance, 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="{{ $modalLabelCls }}">Nominal Transfer (Rp) *</label>
                    <input name="amount" type="number" min="1" step="0.01" required placeholder="1000000" class="{{ $modalInputCls }} tabular-nums font-semibold">
                </div>
                <div class="space-y-1.5">
                    <label class="{{ $modalLabelCls }}">Keterangan Transfer *</label>
                    <input name="description" required placeholder="Contoh: Setoran uang tunai kasir ke rekening BCA" class="{{ $modalInputCls }}">
                </div>
                <div class="flex items-center gap-2 pt-2 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showTransferModal = false" class="flex-1 h-11 rounded-[10px] text-[15px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 active:opacity-70 transition-all">Batal</button>
                    <button type="submit" class="flex-1 h-11 rounded-[10px] text-[15px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all shadow-[0_1px_2px_rgba(0,122,255,0.25)]">Eksekusi Transfer</button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection
