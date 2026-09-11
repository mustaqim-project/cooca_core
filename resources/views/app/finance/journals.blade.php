@extends('layouts.app', ['title' => 'Jurnal Akuntansi Otomatis'])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-5 pb-12">

    {{-- ========================================================== --}}
    {{-- TOOLBAR / PAGE HEADER --}}
    {{-- ========================================================== --}}
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <span class="text-black/70 dark:text-white/70">Keuangan & Kas</span>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Jurnal Akuntansi</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Jurnal Akuntansi Otomatis</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Seluruh transaksi dijurnal berpasangan (double-entry) secara otomatis</p>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            @if(\App\Support\Context::hasPermission('finance.cash_bank') || \App\Support\Context::hasPermission('accounting.view'))
            <a href="{{ route('finance.cash-bank.ledger') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                <span>Buku Kas &amp; Ledger</span>
            </a>
            @endif
            @if(\App\Support\Context::hasPermission('expenses.view') || \App\Support\Context::hasPermission('expenses.manage'))
            <a href="{{ route('finance.expenses.index') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-[#FF3B30]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18-3a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3h18V6z"/></svg>
                <span>Beban Operasional</span>
            </a>
            @endif
        </div>
    </header>    {{-- ========================================================== --}}
    {{-- KPI — Debit, Kredit & Status Keseimbangan --}}
    {{-- ========================================================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-black/45 dark:text-white/45 uppercase tracking-wide">Total Debit Pembukuan</p>
                <p class="text-[22px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] mt-1">Rp {{ number_format($totalDebit, 0, ',', '.') }}</p>
                <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Saldo debit terverifikasi</p>
            </div>
            <div class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-black/45 dark:text-white/45 uppercase tracking-wide">Total Kredit Pembukuan</p>
                <p class="text-[22px] font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF] mt-1">Rp {{ number_format($totalCredit, 0, ',', '.') }}</p>
                <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Saldo kredit terverifikasi</p>
            </div>
            <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-black/45 dark:text-white/45 uppercase tracking-wide">Integritas Double-Entry</p>
                <div class="mt-1.5 flex items-center gap-1.5">
                    @if($isBalanced)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[12px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            Seimbang (Valid)
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[12px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                            Selisih Pembukuan
                        </span>
                    @endif
                </div>
                <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">
                    {{ $isBalanced ? 'Tidak ada deviasi pembukuan' : 'Selisih Rp ' . number_format(abs($totalDebit - $totalCredit), 0, ',', '.') }}
                </p>
            </div>
            <div class="w-10 h-10 rounded-[12px] {{ $isBalanced ? 'bg-[#34C759]/10 text-[#34C759]' : 'bg-[#FF3B30]/10 text-[#FF3B30]' }} flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
            </div>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- SEARCH & FILTER BAR --}}
    {{-- ========================================================== --}}
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3.5">
        <form method="GET" action="{{ route('finance.journals.index') }}" class="flex flex-wrap items-center gap-2.5">
            <div class="relative flex-1 min-w-[200px]">
                <svg class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor jurnal / keterangan..."
                       class="w-full h-9 pl-8 pr-3 text-[13px] bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
            </div>
            <select name="reference_type" class="h-9 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                <option value="">Semua Tipe Referensi</option>
                <option value="pos_order" @selected(request('reference_type') === 'pos_order')>Penjualan POS</option>
                <option value="expense" @selected(request('reference_type') === 'expense')>Beban Operasional</option>
                <option value="cash_transfer" @selected(request('reference_type') === 'cash_transfer')>Transfer Kas/Bank</option>
                <option value="supplier_invoice" @selected(request('reference_type') === 'supplier_invoice')>Tagihan Supplier</option>
                <option value="supplier_payment" @selected(request('reference_type') === 'supplier_payment')>Pelunasan Tagihan</option>
            </select>
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="h-9 px-2.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[12px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
            <span class="text-black/30 dark:text-white/30 text-[12px]">—</span>
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="h-9 px-2.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[12px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
            <button type="submit" class="h-9 px-4 rounded-[10px] bg-[#007AFF] text-white text-[13px] font-semibold hover:bg-[#0071E3] transition-colors">Filter</button>
            @if(request('search') || request('reference_type') || request('start_date') || request('end_date'))
                <a href="{{ route('finance.journals.index') }}" class="h-9 px-3 rounded-[10px] bg-black/5 dark:bg-white/5 text-black/60 dark:text-white/60 hover:bg-black/10 dark:hover:bg-white/10 text-[13px] flex items-center transition-colors">Reset</a>
            @endif
        </form>
    </div>

    {{-- ========================================================== --}}
    {{-- JOURNAL ENTRIES --}}
    {{-- ========================================================== --}}
    <div class="space-y-3">
        @forelse($entries as $entry)
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
            {{-- Entry Header --}}
            <div class="px-4 py-3 bg-black/[0.02] dark:bg-white/[0.03] border-b border-black/5 dark:border-white/10 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="tabular-nums font-semibold text-[#007AFF] dark:text-[#0A84FF] text-[13px]">#{{ $entry->entry_number }}</span>
                    <span class="text-[13px] text-black/50 dark:text-white/50">{{ $entry->entry_date->format('d M Y') }}</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-black/[0.06] dark:bg-white/[0.08] text-black/55 dark:text-white/55">
                        {{ strtoupper($entry->reference_type ?? 'Jurnal') }}
                    </span>
                </div>
                <div class="text-[13px] text-black/60 dark:text-white/60 truncate max-w-sm">
                    {{ $entry->description }}
                </div>
            </div>

            {{-- Entry Lines Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/[0.04] dark:border-white/[0.06]">
                            <th class="px-4 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Kode & Nama Akun (COA)</th>
                            <th class="px-4 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Debit (Rp)</th>
                            <th class="px-4 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Kredit (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @foreach($entry->lines as $line)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                            <td class="px-4 py-2.5">
                                <span class="tabular-nums font-semibold text-[#34C759] dark:text-[#30D158] mr-2 text-[12px]">{{ $line->account->code ?? '-' }}</span>
                                <span class="text-black/80 dark:text-white/80">{{ $line->account->name ?? '-' }}</span>
                                @if($line->notes)
                                    <span class="text-[11px] text-black/40 dark:text-white/40 ml-2">({{ $line->notes }})</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-right tabular-nums">
                                @if($line->type === 'debit')
                                    <span class="font-semibold text-black dark:text-white">{{ number_format($line->amount, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-black/20 dark:text-white/20">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-right tabular-nums">
                                @if($line->type === 'credit')
                                    <span class="font-semibold text-[#007AFF] dark:text-[#0A84FF]">{{ number_format($line->amount, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-black/20 dark:text-white/20">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @empty
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 py-16 text-center">
            <div class="flex flex-col items-center gap-3">
                <svg class="w-12 h-12 text-black/15 dark:text-white/15" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                <div>
                    <p class="text-[15px] font-semibold text-black/60 dark:text-white/60">Belum ada entri jurnal akuntansi</p>
                    <p class="text-[13px] text-black/40 dark:text-white/40 mt-0.5">Jurnal dibuat otomatis saat kasir checkout, transaksi kas, atau beban dicatat</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    @if($entries->hasPages())
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 px-4 py-3 flex items-center justify-between text-[13px] text-black/60 dark:text-white/60">
        <div>
            Menampilkan <span class="font-medium text-black dark:text-white tabular-nums">{{ $entries->firstItem() }}–{{ $entries->lastItem() }}</span>
            dari <span class="font-medium text-black dark:text-white tabular-nums">{{ $entries->total() }}</span> entri
        </div>
        <div class="flex items-center gap-2">
            @if($entries->onFirstPage())
                <span class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-black/30 dark:text-white/30 cursor-not-allowed">‹ Sebelumnya</span>
            @else
                <a href="{{ $entries->previousPageUrl() }}" class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors flex items-center">‹ Sebelumnya</a>
            @endif
            <span class="h-8 px-2.5 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] text-black dark:text-white font-medium flex items-center tabular-nums">{{ $entries->currentPage() }}</span>
            @if($entries->hasMorePages())
                <a href="{{ $entries->nextPageUrl() }}" class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors flex items-center">Selanjutnya ›</a>
            @else
                <span class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-black/30 dark:text-white/30 cursor-not-allowed">Selanjutnya ›</span>
            @endif
        </div>
    </div>
    @endif

</div>
@endsection
