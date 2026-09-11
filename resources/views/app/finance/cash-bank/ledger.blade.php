@extends('layouts.app', ['title' => 'Buku Kas & Ledger'])

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
                <a href="{{ route('finance.cash-bank.index') }}" class="hover:text-[#007AFF] transition-colors">Kas & Bank</a>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Buku Kas & Ledger</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Buku Kas & Ledger Rekening</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Riwayat kronologis mutasi debit dan kredit seluruh akun kas dan rekening bank</p>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            @if(\App\Support\Context::hasPermission('finance.cash_bank'))
            <a href="{{ route('finance.cash-bank.index') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                <span>Kembali ke Kas & Bank</span>
            </a>
            @endif
        </div>
    </header>

    {{-- ========================================================== --}}
    {{-- FILTER & ACCOUNT SUMMARY CARD --}}
    {{-- ========================================================== --}}
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 space-y-3">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <form method="GET" action="{{ route('finance.cash-bank.ledger') }}" class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-black/50 dark:text-white/50" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.539.092.917.56.917 1.109v1.244a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.422A2.25 2.25 0 013 5.831V4.787c0-.548.378-1.017.917-1.109A48.834 48.834 0 0112 3z"/></svg>
                    <span class="text-[13px] font-semibold text-black/80 dark:text-white/80">Filter:</span>
                </div>
                <select name="account_id" class="h-9 px-3 bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition-all">
                    <option value="">Semua Rekening</option>
                    @foreach($accounts as $item)
                        <option value="{{ $item->id }}" @selected(request('account_id') === $item->id)>
                            {{ $item->name }} (Rp {{ number_format($item->current_balance, 0, ',', '.') }})
                        </option>
                    @endforeach
                </select>
                <select name="type" class="h-9 px-3 bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition-all">
                    <option value="">Semua Jenis Arus</option>
                    <option value="in" @selected(request('type') === 'in')>Penerimaan (Masuk)</option>
                    <option value="out" @selected(request('type') === 'out')>Pengeluaran (Keluar)</option>
                    <option value="transfer" @selected(request('type') === 'transfer')>Mutasi Transfer</option>
                </select>
                <select name="method" class="h-9 px-3 bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition-all">
                    <option value="">Semua Metode Bayar</option>
                    <option value="cash" @selected(request('method') === 'cash')>Tunai / Cash POS</option>
                    <option value="qris" @selected(request('method') === 'qris')>QRIS / E-Wallet</option>
                    <option value="transfer" @selected(in_array(request('method'), ['transfer', 'bank_transfer']))>Transfer Bank</option>
                    <option value="edc" @selected(in_array(request('method'), ['edc', 'edc_debit', 'edc_credit']))>Mesin EDC (Debit / Kredit)</option>
                </select>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="h-9 px-2.5 bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 rounded-[10px] text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                <span class="text-black/30 dark:text-white/30 text-[12px]">—</span>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="h-9 px-2.5 bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 rounded-[10px] text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari keterangan..." class="h-9 px-3 bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 rounded-[10px] text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                <button type="submit" class="h-9 px-3.5 rounded-[10px] bg-[#007AFF] text-white text-[13px] font-medium hover:bg-[#0071E3] transition-colors">Terapkan</button>
                @if(request('account_id') || request('type') || request('method') || request('start_date') || request('end_date') || request('search'))
                    <a href="{{ route('finance.cash-bank.ledger') }}" class="h-9 px-3 rounded-[10px] bg-black/5 dark:bg-white/5 text-black/60 dark:text-white/60 hover:bg-black/10 dark:hover:bg-white/10 text-[13px] flex items-center transition-colors">Reset</a>
                @endif
            </form>

            @if($account)
            <div class="flex items-center gap-3 bg-[#F2F2F7]/80 dark:bg-[#2C2C2E]/80 border border-black/5 dark:border-white/5 rounded-[12px] px-4 py-2 shrink-0">
                <div>
                    <span class="text-[11px] uppercase font-semibold text-black/50 dark:text-white/50 tracking-wider">Saldo Terpilih</span>
                    <div class="text-[18px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] tracking-tight">
                        Rp {{ number_format($account->current_balance, 0, ',', '.') }}
                    </div>
                </div>
                <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- LEDGER TRANSACTIONS TABLE --}}
    {{-- ========================================================== --}}
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="px-5 py-4 border-b border-black/5 dark:border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <h2 class="text-[14px] font-semibold text-black dark:text-white">Buku Mutasi Kas & Ledger</h2>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 tabular-nums">
                    {{ $transactions->total() }} Baris
                </span>
            </div>
            @if($account)
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">
                Menampilkan mutasi akun: <strong class="text-black dark:text-white">{{ $account->name }}</strong>
            </span>
            @endif
        </div>

        <div class="overflow-x-auto">
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
                        <td class="py-3 px-4 tabular-nums text-black/60 dark:text-white/60 whitespace-nowrap">
                            {{ $transaction->transaction_date->format('d M Y') }}
                        </td>
                        <td class="py-3 px-4 font-semibold text-black dark:text-white whitespace-nowrap">
                            {{ $transaction->cashAccount->name }}
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            @if($isOut && $isTrf)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A] border border-[#FF3B30]/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30] dark:bg-[#FF453A]"></span>
                                    <span>Transfer Keluar</span>
                                </span>
                            @elseif(!$isOut && $isTrf)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] border border-[#007AFF]/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF] dark:bg-[#0A84FF]"></span>
                                    <span>Transfer Masuk</span>
                                </span>
                            @elseif($isOut)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A] border border-[#FF3B30]/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30] dark:bg-[#FF453A]"></span>
                                    <span>Keluar</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] dark:bg-[#30D158]"></span>
                                    <span>Masuk</span>
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-black/80 dark:text-white/80 max-w-xs truncate">
                            {{ $transaction->description }}
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums font-semibold whitespace-nowrap {{ $isOut ? 'text-[#FF3B30] dark:text-[#FF453A]' : 'text-[#34C759] dark:text-[#30D158]' }}">
                            {{ $isOut ? '-' : '+' }} Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums font-bold text-black dark:text-white whitespace-nowrap">
                            Rp {{ number_format($transaction->balance_after, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center">
                            <div class="w-12 h-12 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.04] text-black/40 dark:text-white/40 mx-auto flex items-center justify-center mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                            </div>
                            <p class="text-[14px] font-semibold text-black dark:text-white">Belum Ada Riwayat Mutasi</p>
                            <p class="text-[12px] text-black/50 dark:text-white/50 mt-1">Belum ada catatan mutasi kas atau bank untuk akun yang dipilih.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
        <div class="px-5 py-3.5 border-t border-black/5 dark:border-white/5 bg-black/[0.01] dark:bg-white/[0.01]">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
