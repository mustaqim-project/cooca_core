@extends('layouts.app', ['title' => 'Buku Kas & Ledger'])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-5 pb-16">

        {{-- ========================================================== --}}
        {{-- TOOLBAR / PAGE HEADER                                      --}}
        {{-- ========================================================== --}}
        <header class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
            <div>
                <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                    <span class="text-black/70 dark:text-white/70 font-medium">Keuangan &amp; Kas</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                    <a href="{{ route('finance.cash-bank.index') }}" class="hover:text-[#007AFF] transition-colors">Kas &amp; Bank</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                    <span class="text-black dark:text-white font-semibold">Buku Kas &amp; Ledger</span>
                </nav>
                <h1 class="text-[20px] sm:text-[22px] font-bold text-black dark:text-white tracking-tight">Buku Kas &amp; Ledger Rekening</h1>
                <p class="text-[13px] text-black/50 dark:text-white/50">Riwayat kronologis mutasi debit dan kredit seluruh akun kas tunai dan rekening bank operasional</p>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                @if (\App\Support\Context::hasPermission('finance.cash_bank'))
                    <a href="{{ route('finance.cash-bank.index') }}"
                        class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all flex items-center gap-2">
                        <i data-lucide="arrow-left" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                        <span>Kembali ke Kas &amp; Bank</span>
                    </a>
                @endif
            </div>
        </header>

        {{-- ========================================================== --}}
        {{-- BENTO KPI SUMMARY TILES                                    --}}
        {{-- ========================================================== --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45">Akun Aktif</span>
                    <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                        <i data-lucide="landmark" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-[15px] font-bold text-black dark:text-white truncate">
                        {{ $account ? $account->name : 'Semua Rekening' }}
                    </p>
                    <p class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">
                        {{ $account ? 'Filter khusus akun' : $accounts->count() . ' Rekening terdaftar' }}
                    </p>
                </div>
            </div>

            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45">Saldo Terkini</span>
                    <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center">
                        <i data-lucide="wallet" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-[18px] sm:text-[20px] font-extrabold tabular-nums text-[#34C759] dark:text-[#30D158] tracking-tight">
                        Rp {{ number_format($account ? $account->current_balance : $accounts->sum('current_balance'), 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">
                        {{ $account ? 'Saldo akun terpilih' : 'Total likuiditas kas & bank' }}
                    </p>
                </div>
            </div>

            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45">Total Baris Mutasi</span>
                    <div class="w-8 h-8 rounded-[10px] bg-[#5856D6]/10 text-[#5856D6] dark:text-[#5E5CE6] flex items-center justify-center">
                        <i data-lucide="receipt" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-[18px] sm:text-[20px] font-extrabold tabular-nums text-black dark:text-white tracking-tight">
                        {{ $transactions->total() }}
                    </p>
                    <p class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">Catatan mutasi terverifikasi</p>
                </div>
            </div>

            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45">Integritas Ledger</span>
                    <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                        Otomatis Sinkron
                    </span>
                    <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Realtime running balance</p>
                </div>
            </div>
        </div>

        {{-- ========================================================== --}}
        {{-- FILTER & SEARCH TOOLBAR                                    --}}
        {{-- ========================================================== --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 shadow-sm space-y-3">
            <form method="GET" action="{{ route('finance.cash-bank.ledger') }}"
                class="flex flex-wrap items-center gap-2.5">
                <div class="flex items-center gap-2 text-black/70 dark:text-white/70">
                    <i data-lucide="filter" class="w-4 h-4 text-black/40 dark:text-white/40"></i>
                    <span class="text-[13px] font-semibold">Filter:</span>
                </div>
                <select name="account_id"
                    class="h-11 sm:h-9 px-3 bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 rounded-[10px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition-all">
                    <option value="">Semua Rekening</option>
                    @foreach ($accounts as $item)
                        <option value="{{ $item->id }}" @selected(request('account_id') === $item->id)>
                            {{ $item->name }} (Rp {{ number_format($item->current_balance, 0, ',', '.') }})
                        </option>
                    @endforeach
                </select>
                <select name="type"
                    class="h-11 sm:h-9 px-3 bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 rounded-[10px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition-all">
                    <option value="">Semua Jenis Arus</option>
                    <option value="in" @selected(request('type') === 'in')>Penerimaan (Masuk)</option>
                    <option value="out" @selected(request('type') === 'out')>Pengeluaran (Keluar)</option>
                    <option value="transfer" @selected(request('type') === 'transfer')>Mutasi Transfer</option>
                </select>
                <select name="method"
                    class="h-11 sm:h-9 px-3 bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 rounded-[10px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition-all">
                    <option value="">Semua Metode Bayar</option>
                    <option value="cash" @selected(request('method') === 'cash')>Tunai / Cash POS</option>
                    <option value="qris" @selected(request('method') === 'qris')>QRIS / E-Wallet</option>
                    <option value="transfer" @selected(in_array(request('method'), ['transfer', 'bank_transfer']))>Transfer Bank</option>
                    <option value="edc" @selected(in_array(request('method'), ['edc', 'edc_debit', 'edc_credit']))>Mesin EDC (Debit / Kredit)</option>
                </select>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                    class="h-11 sm:h-9 px-3 bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 rounded-[10px] text-[16px] sm:text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                <span class="text-black/30 dark:text-white/30 text-[12px]">–</span>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                    class="h-11 sm:h-9 px-3 bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 rounded-[10px] text-[16px] sm:text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari keterangan..."
                    class="h-11 sm:h-9 px-3.5 bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 rounded-[10px] text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                <button type="submit"
                    class="h-11 sm:h-9 px-4 rounded-[10px] bg-[#007AFF] text-white text-[13px] font-semibold hover:bg-[#0071E3] transition-colors flex items-center gap-1.5 shadow-xs">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                    <span>Terapkan</span>
                </button>
                @if (request('account_id') || request('type') || request('method') || request('start_date') || request('end_date') || request('search'))
                    <a href="{{ route('finance.cash-bank.ledger') }}"
                        class="h-11 sm:h-9 px-3 rounded-[10px] bg-black/5 dark:bg-white/5 text-black/60 dark:text-white/60 hover:bg-black/10 dark:hover:bg-white/10 text-[13px] font-medium flex items-center transition-colors">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- ========================================================== --}}
        {{-- LEDGER TRANSACTIONS TABLE                                  --}}
        {{-- ========================================================== --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-sm">
            <div class="px-5 py-4 border-b border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <h2 class="text-[15px] font-bold text-black dark:text-white">Buku Mutasi Kas &amp; Ledger</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 tabular-nums">
                        {{ $transactions->total() }} Baris
                    </span>
                </div>
                @if ($account)
                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">
                        Menampilkan mutasi akun: <strong class="text-black dark:text-white">{{ $account->name }}</strong>
                    </span>
                @endif
            </div>

            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left text-[13px] min-w-[620px]">
                    <thead>
                        <tr class="bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/5 dark:border-white/5 text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">
                            <th class="py-3 px-4 whitespace-nowrap">Tanggal</th>
                            <th class="py-3 px-4 whitespace-nowrap">Rekening</th>
                            <th class="py-3 px-4 whitespace-nowrap">Arus</th>
                            <th class="py-3 px-4 whitespace-nowrap">Keterangan / Referensi</th>
                            <th class="py-3 px-4 text-right whitespace-nowrap">Debit / Kredit</th>
                            <th class="py-3 px-4 text-right whitespace-nowrap">Saldo Berjalan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($transactions as $transaction)
                            @php
                                $isOut = $transaction->isOutflow();
                                $isTrf = $transaction->isTransfer();
                            @endphp
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors">
                                <td class="py-3.5 px-4 tabular-nums text-black/60 dark:text-white/60 whitespace-nowrap">
                                    {{ $transaction->transaction_date->format('d M Y') }}
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-black dark:text-white whitespace-nowrap">
                                    {{ $transaction->cashAccount->name }}
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if ($isOut && $isTrf)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A] border border-[#FF3B30]/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30] dark:bg-[#FF453A]"></span>
                                            <span>Transfer Keluar</span>
                                        </span>
                                    @elseif(!$isOut && $isTrf)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] border border-[#007AFF]/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF] dark:bg-[#0A84FF]"></span>
                                            <span>Transfer Masuk</span>
                                        </span>
                                    @elseif($isOut)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A] border border-[#FF3B30]/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30] dark:bg-[#FF453A]"></span>
                                            <span>Keluar</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] dark:bg-[#30D158]"></span>
                                            <span>Masuk</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-black/80 dark:text-white/80 max-w-xs truncate">
                                    {{ $transaction->description }}
                                </td>
                                <td class="py-3.5 px-4 text-right tabular-nums font-bold whitespace-nowrap {{ $isOut ? 'text-[#FF3B30] dark:text-[#FF453A]' : 'text-[#34C759] dark:text-[#30D158]' }}">
                                    {{ $isOut ? '-' : '+' }} Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-right tabular-nums font-extrabold text-black dark:text-white whitespace-nowrap">
                                    Rp {{ number_format($transaction->balance_after, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-16 text-center">
                                    <div class="w-12 h-12 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.04] text-black/40 dark:text-white/40 mx-auto flex items-center justify-center mb-3">
                                        <i data-lucide="book-open" class="w-6 h-6"></i>
                                    </div>
                                    <p class="text-[15px] font-bold text-black dark:text-white">Belum Ada Riwayat Mutasi</p>
                                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-1">Belum ada catatan mutasi kas atau bank untuk filter yang dipilih.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- MOBILE GROUPED INSET LIST (Apple iOS HIG) --}}
            <div class="sm:hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                @forelse($transactions as $transaction)
                    @php
                        $isOut = $transaction->isOutflow();
                        $isTrf = $transaction->isTransfer();
                    @endphp
                    <div class="p-4 space-y-2.5 active:bg-black/[0.02] dark:active:bg-white/[0.03] transition-colors">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-[14px] text-black dark:text-white">{{ $transaction->cashAccount->name }}</span>
                                    @if ($isOut && $isTrf)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A]">
                                            Transfer Keluar
                                        </span>
                                    @elseif(!$isOut && $isTrf)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]">
                                            Transfer Masuk
                                        </span>
                                    @elseif($isOut)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A]">
                                            Keluar
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]">
                                            Masuk
                                        </span>
                                    @endif
                                </div>
                                <div class="text-[12px] text-black/45 dark:text-white/45 mt-0.5 tabular-nums">
                                    {{ $transaction->transaction_date->format('d M Y') }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="tabular-nums font-bold text-[14px] {{ $isOut ? 'text-[#FF3B30] dark:text-[#FF453A]' : 'text-[#34C759] dark:text-[#30D158]' }}">
                                    {{ $isOut ? '-' : '+' }} Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                </div>
                                <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums mt-0.5">
                                    Saldo: Rp {{ number_format($transaction->balance_after, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>

                        @if ($transaction->description)
                            <p class="text-[13px] text-black/75 dark:text-white/75 line-clamp-2">
                                {{ $transaction->description }}
                            </p>
                        @endif
                    </div>
                @empty
                    <div class="py-12 text-center text-black/45 dark:text-white/45 text-[13px]">
                        Belum ada catatan mutasi kas atau bank.
                    </div>
                @endforelse
            </div>

            @if ($transactions->hasPages())
                <div class="px-5 py-3.5 border-t border-black/5 dark:border-white/5 bg-black/[0.01] dark:bg-white/[0.01]">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection

