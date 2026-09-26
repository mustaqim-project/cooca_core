@extends('layouts.app', ['title' => 'Jurnal Akuntansi Otomatis'])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-5 pb-28 lg:pb-12">

    {{-- ========================================================== --}}
    {{-- TOOLBAR / PAGE HEADER                                      --}}
    {{-- ========================================================== --}}
    <header class="rounded-[16px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <span class="text-black/70 dark:text-white/70">Keuangan &amp; Akuntansi</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <span class="text-black dark:text-white font-medium">Jurnal Akuntansi</span>
            </nav>
            <h1 class="text-[20px] sm:text-[22px] font-bold text-black dark:text-white tracking-tight">Jurnal Akuntansi Otomatis</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Seluruh transaksi dijurnal berpasangan (double-entry) secara otomatis sesuai standar SAK EMKM</p>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            @if (\App\Support\Context::hasPermission('finance.cash_bank') || \App\Support\Context::hasPermission('accounting.view'))
                <a href="{{ route('finance.cash-bank.ledger') }}"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                    <i data-lucide="book-open" class="w-4 h-4"></i>
                    <span>Buku Kas &amp; Ledger</span>
                </a>
            @endif
            @if (\App\Support\Context::hasPermission('expenses.view') || \App\Support\Context::hasPermission('expenses.manage'))
                <a href="{{ route('finance.expenses.index') }}"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                    <i data-lucide="receipt" class="w-4 h-4 text-[#FF3B30]"></i>
                    <span>Beban Operasional</span>
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

    {{-- ========================================================== --}}
    {{-- KPI - Debit, Kredit & Status Keseimbangan (BENTO)          --}}
    {{-- ========================================================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">Total Debit Pembukuan</p>
                <p class="text-[22px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] mt-1">Rp {{ number_format($totalDebit, 0, ',', '.') }}</p>
                <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Saldo debit terverifikasi</p>
            </div>
            <div class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center shrink-0">
                <i data-lucide="arrow-down-left" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">Total Kredit Pembukuan</p>
                <p class="text-[22px] font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF] mt-1">Rp {{ number_format($totalCredit, 0, ',', '.') }}</p>
                <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Saldo kredit terverifikasi</p>
            </div>
            <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                <i data-lucide="arrow-up-right" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">Integritas Double-Entry</p>
                <div class="mt-1.5 flex items-center gap-1.5">
                    @if ($isBalanced)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[12px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                            Seimbang (Valid)
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[12px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                            <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                            Selisih Pembukuan
                        </span>
                    @endif
                </div>
                <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">
                    {{ $isBalanced ? 'Tidak ada deviasi pembukuan' : 'Selisih Rp ' . number_format(abs($totalDebit - $totalCredit), 0, ',', '.') }}
                </p>
            </div>
            <div class="w-10 h-10 rounded-[12px] {{ $isBalanced ? 'bg-[#34C759]/10 text-[#34C759]' : 'bg-[#FF3B30]/10 text-[#FF3B30]' }} flex items-center justify-center shrink-0">
                <i data-lucide="{{ $isBalanced ? 'shield-check' : 'alert-octagon' }}" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- SEARCH & FILTER BAR                                       --}}
    {{-- ========================================================== --}}
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3.5 shadow-xs">
        <form method="GET" action="{{ route('finance.journals.index') }}" class="flex flex-wrap items-center gap-2.5">
            <div class="relative flex-1 min-w-[200px]">
                <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nomor jurnal / keterangan..."
                    class="w-full h-11 sm:h-9 pl-8 pr-3 text-[16px] sm:text-[13px] bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
            </div>
            <select name="reference_type"
                class="h-11 sm:h-9 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                <option value="">Semua Tipe Referensi</option>
                <option value="pos_order" @selected(request('reference_type') === 'pos_order')>Penjualan POS</option>
                <option value="expense" @selected(request('reference_type') === 'expense')>Beban Operasional</option>
                <option value="cash_transfer" @selected(request('reference_type') === 'cash_transfer')>Transfer Kas/Bank</option>
                <option value="supplier_invoice" @selected(request('reference_type') === 'supplier_invoice')>Tagihan Supplier</option>
                <option value="supplier_payment" @selected(request('reference_type') === 'supplier_payment')>Pelunasan Tagihan</option>
            </select>
            <input type="date" name="start_date" value="{{ request('start_date') }}"
                class="h-11 sm:h-9 px-2.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[12px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
            <span class="text-black/30 dark:text-white/30 text-[12px]">-</span>
            <input type="date" name="end_date" value="{{ request('end_date') }}"
                class="h-11 sm:h-9 px-2.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[12px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
            <button type="submit"
                class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] bg-[#007AFF] text-white text-[13px] font-semibold hover:bg-[#0071E3] transition-colors flex items-center justify-center">Filter</button>
            @if (request('search') || request('reference_type') || request('start_date') || request('end_date'))
                <a href="{{ route('finance.journals.index') }}"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3 rounded-[10px] bg-black/5 dark:bg-white/5 text-black/60 dark:text-white/60 hover:bg-black/10 dark:hover:bg-white/10 text-[13px] flex items-center justify-center transition-colors">Reset</a>
            @endif
        </form>
    </div>

    {{-- ========================================================== --}}
    {{-- JOURNAL ENTRIES                                            --}}
    {{-- ========================================================== --}}
    <div class="space-y-3.5">
        @forelse($entries as $entry)
            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-xs">
                {{-- Entry Header --}}
                <div class="px-4 sm:px-5 py-3.5 bg-black/[0.02] dark:bg-white/[0.03] border-b border-black/5 dark:border-white/10 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <span class="tabular-nums font-mono font-bold text-[#007AFF] dark:text-[#0A84FF] text-[13.5px]">#{{ $entry->entry_number }}</span>
                        <span class="text-[12.5px] text-black/50 dark:text-white/50 font-medium">{{ $entry->entry_date->format('d M Y') }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-black/[0.06] dark:bg-white/[0.08] text-black/65 dark:text-white/65">
                            {{ strtoupper($entry->reference_type ?? 'Jurnal') }}
                        </span>
                    </div>
                    <div class="text-[13px] text-black/70 dark:text-white/70 truncate max-w-md">
                        {{ $entry->description }}
                    </div>
                </div>

                {{-- Entry Lines Table (Desktop) --}}
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left text-[13px]">
                        <thead>
                            <tr class="border-b border-black/[0.04] dark:border-white/[0.06] bg-black/[0.01] dark:bg-white/[0.01]">
                                <th class="px-4 sm:px-5 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40">
                                    Kode &amp; Nama Akun (COA)</th>
                                <th class="px-4 sm:px-5 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 text-right">
                                    Debit (Rp)</th>
                                <th class="px-4 sm:px-5 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 text-right">
                                    Kredit (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                            @foreach ($entry->lines as $line)
                                <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                    <td class="px-4 sm:px-5 py-3">
                                        <span class="tabular-nums font-mono font-semibold text-[#34C759] dark:text-[#30D158] mr-2 text-[12px]">{{ $line->account->code ?? '-' }}</span>
                                        <span class="text-black/85 dark:text-white/85 font-medium">{{ $line->account->name ?? '-' }}</span>
                                        @if ($line->notes)
                                            <span class="text-[11.5px] text-black/40 dark:text-white/40 ml-2">({{ $line->notes }})</span>
                                        @endif
                                    </td>
                                    <td class="px-4 sm:px-5 py-3 text-right tabular-nums">
                                        @if ($line->type === 'debit')
                                            <span class="font-bold text-black dark:text-white">{{ number_format($line->amount, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-black/20 dark:text-white/20">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 sm:px-5 py-3 text-right tabular-nums">
                                        @if ($line->type === 'credit')
                                            <span class="font-bold text-[#007AFF] dark:text-[#0A84FF]">{{ number_format($line->amount, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-black/20 dark:text-white/20">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Entry Lines List (Mobile Only) --}}
                <div class="sm:hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @foreach ($entry->lines as $line)
                        <div class="p-3.5 flex items-center justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="tabular-nums font-mono font-semibold text-[#34C759] dark:text-[#30D158] text-[11px] bg-[#34C759]/10 px-1.5 py-0.5 rounded-[4px]">{{ $line->account->code ?? '-' }}</span>
                                    <span class="text-[13px] font-medium text-black dark:text-white truncate">{{ $line->account->name ?? '-' }}</span>
                                </div>
                                @if ($line->notes)
                                    <p class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">{{ $line->notes }}</p>
                                @endif
                            </div>
                            <div class="text-right shrink-0">
                                @if ($line->type === 'debit')
                                    <span class="text-[10px] uppercase font-bold tracking-wider text-[#34C759] dark:text-[#30D158] block">Debit</span>
                                    <span class="text-[13px] font-bold tabular-nums text-black dark:text-white">Rp {{ number_format($line->amount, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-[10px] uppercase font-bold tracking-wider text-[#007AFF] dark:text-[#0A84FF] block">Kredit</span>
                                    <span class="text-[13px] font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF]">Rp {{ number_format($line->amount, 0, ',', '.') }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 py-16 text-center shadow-xs">
                <div class="flex flex-col items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center text-black/25 dark:text-white/25">
                        <i data-lucide="book-open" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-[15px] font-semibold text-black/60 dark:text-white/60">Belum ada entri jurnal akuntansi</p>
                        <p class="text-[13px] text-black/40 dark:text-white/40 mt-0.5">Jurnal dibuat otomatis saat kasir checkout, transaksi kas, atau beban dicatat</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @if ($entries->hasPages())
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 px-5 py-3.5 flex items-center justify-between text-[13px] text-black/60 dark:text-white/60 shadow-xs">
            <div>
                Menampilkan <span class="font-medium text-black dark:text-white tabular-nums">{{ $entries->firstItem() }}–{{ $entries->lastItem() }}</span> dari <span class="font-medium text-black dark:text-white tabular-nums">{{ $entries->total() }}</span> entri
            </div>
            <div class="flex items-center gap-2">
                @if ($entries->onFirstPage())
                    <span class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-black/30 dark:text-white/30 cursor-not-allowed flex items-center"><i data-lucide="chevron-left" class="w-3.5 h-3.5 mr-1"></i> Sebelumnya</span>
                @else
                    <a href="{{ $entries->previousPageUrl() }}" class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors flex items-center"><i data-lucide="chevron-left" class="w-3.5 h-3.5 mr-1"></i> Sebelumnya</a>
                @endif
                <span class="h-8 px-2.5 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] text-black dark:text-white font-medium flex items-center tabular-nums">{{ $entries->currentPage() }}</span>
                @if ($entries->hasMorePages())
                    <a href="{{ $entries->nextPageUrl() }}" class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors flex items-center">Selanjutnya <i data-lucide="chevron-right" class="w-3.5 h-3.5 ml-1"></i></a>
                @else
                    <span class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-black/30 dark:text-white/30 cursor-not-allowed flex items-center">Selanjutnya <i data-lucide="chevron-right" class="w-3.5 h-3.5 ml-1"></i></span>
                @endif
            </div>
        </div>
    @endif

</div>
@endsection
