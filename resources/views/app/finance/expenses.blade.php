@extends('layouts.app', ['title' => 'Beban Operasional Toko'])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-5 pb-12" x-data="{ showCreateModal: false }">

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
                <span class="text-black dark:text-white font-medium">Beban Operasional</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Beban & Biaya Operasional</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Catat biaya operasional toko dengan jurnal otomatis double-entry</p>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            @if(\App\Support\Context::hasPermission('accounting.view') || \App\Support\Context::hasPermission('expenses.manage'))
            <a href="{{ route('finance.journals.index') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                <span>Lihat Buku Jurnal</span>
            </a>
            @endif
            @if(\App\Support\Context::hasPermission('expenses.manage'))
            <button @click="showCreateModal = true" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(255,59,48,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Catat Biaya Baru</span>
            </button>
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
    {{-- KPI — Total Beban Bulan Ini --}}
    {{-- ========================================================== --}}
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 flex items-center justify-between">
        <div>
            <p class="text-[12px] font-medium text-black/50 dark:text-white/50 uppercase tracking-wide">Total Beban Operasional Bulan Ini</p>
            <p class="text-[28px] font-bold tabular-nums text-[#FF3B30] dark:text-[#FF453A] mt-1 tracking-tight">Rp {{ number_format($totalExpensesThisMonth, 0, ',', '.') }}</p>
        </div>
        <div class="w-12 h-12 rounded-[14px] bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A] flex items-center justify-center shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185z"/></svg>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- SEARCH & FILTER BAR --}}
    {{-- ========================================================== --}}
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3.5">
        <form method="GET" action="{{ route('finance.expenses.index') }}" class="flex flex-wrap items-center gap-2.5">
            <div class="relative flex-1 min-w-[200px]">
                <svg class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor bukti / deskripsi / kategori..."
                       class="w-full h-9 pl-8 pr-3 text-[13px] bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
            </div>
            <select name="category" class="h-9 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                <option value="">Semua Kategori</option>
                @foreach($categories ?? [] as $cat)
                    <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ ucfirst($cat) }}</option>
                @endforeach
            </select>
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="h-9 px-2.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[12px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
            <span class="text-black/30 dark:text-white/30 text-[12px]">—</span>
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="h-9 px-2.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[12px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
            <button type="submit" class="h-9 px-4 rounded-[10px] bg-[#007AFF] text-white text-[13px] font-semibold hover:bg-[#0071E3] transition-colors">Filter</button>
            @if(request('search') || request('category') || request('start_date') || request('end_date'))
                <a href="{{ route('finance.expenses.index') }}" class="h-9 px-3 rounded-[10px] bg-black/5 dark:bg-white/5 text-black/60 dark:text-white/60 hover:bg-black/10 dark:hover:bg-white/10 text-[13px] flex items-center transition-colors">Reset</a>
            @endif
        </form>
    </div>

    {{-- ========================================================== --}}
    {{-- EXPENSES TABLE --}}
    {{-- ========================================================== --}}
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px] min-w-[600px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10">
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">No. Bukti / Tanggal</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Kategori & Keterangan</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Outlet / Lokasi</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Metode Bayar</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Nominal Biaya</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($expenses as $ex)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3">
                            <div class="font-semibold tabular-nums text-black dark:text-white">#{{ $ex->expense_number }}</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">{{ $ex->expense_date->format('d M Y') }}</div>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $catMap = [
                                    'operational' => 'Operasional',
                                    'utilities'   => 'Listrik & Air',
                                    'supplies'    => 'Kemasan',
                                    'salaries'    => 'Gaji & Upah',
                                    'maintenance' => 'Perawatan',
                                    'other'       => 'Lain-lain',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                                {{ $catMap[$ex->category] ?? $ex->category }}
                            </span>
                            <div class="text-[13px] text-black/80 dark:text-white/80 mt-1">{{ $ex->description }}</div>
                        </td>
                        <td class="px-4 py-3 text-black/60 dark:text-white/60">
                            {{ $ex->location->name ?? 'Outlet Utama' }}
                        </td>
                        <td class="px-4 py-3 text-[12px] text-black/60 dark:text-white/60 capitalize">
                            {{ str_replace('_', ' ', $ex->payment_method) }}
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums font-semibold text-[#FF3B30] dark:text-[#FF453A]">
                            Rp {{ number_format($ex->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 text-black/15 dark:text-white/15" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185z"/></svg>
                                <div>
                                    <p class="text-[15px] font-semibold text-black/60 dark:text-white/60">Belum ada pencatatan biaya</p>
                                    <p class="text-[13px] text-black/40 dark:text-white/40 mt-0.5">Catat biaya operasional pertama Anda</p>
                                </div>
                                <button @click="showCreateModal = true" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.97] transition-all">
                                    Catat Biaya Baru
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
        <div class="px-4 py-3 border-t border-black/5 dark:border-white/10 flex items-center justify-between text-[13px] text-black/60 dark:text-white/60">
            <div>
                Menampilkan <span class="font-medium text-black dark:text-white tabular-nums">{{ $expenses->firstItem() }}–{{ $expenses->lastItem() }}</span>
                dari <span class="font-medium text-black dark:text-white tabular-nums">{{ $expenses->total() }}</span> catatan
            </div>
            <div class="flex items-center gap-2">
                @if($expenses->onFirstPage())
                    <span class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-black/30 dark:text-white/30 cursor-not-allowed">‹ Sebelumnya</span>
                @else
                    <a href="{{ $expenses->previousPageUrl() }}" class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors flex items-center">‹ Sebelumnya</a>
                @endif
                <span class="h-8 px-2.5 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] text-black dark:text-white font-medium flex items-center tabular-nums">{{ $expenses->currentPage() }}</span>
                @if($expenses->hasMorePages())
                    <a href="{{ $expenses->nextPageUrl() }}" class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors flex items-center">Selanjutnya ›</a>
                @else
                    <span class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-black/30 dark:text-white/30 cursor-not-allowed">Selanjutnya ›</span>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- ========================================================== --}}
    {{-- MODAL — Catat Biaya (Apple Sheet macOS floating) --}}
    {{-- ========================================================== --}}
    <div x-show="showCreateModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-full max-w-md rounded-[20px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.2)] overflow-hidden"
            @click.away="showCreateModal = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            {{-- Sheet Header --}}
            <div class="flex items-center justify-between px-5 pt-5 pb-4 border-b border-black/5 dark:border-white/10">
                <h3 class="text-[17px] font-semibold text-black dark:text-white">Catat Beban Operasional</h3>
                <button type="button" @click="showCreateModal = false" class="w-8 h-8 rounded-full bg-black/[0.06] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:bg-black/[0.10] dark:hover:bg-white/[0.12] flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Form --}}
            <form action="{{ route('finance.expenses.store') }}" method="POST" class="px-5 py-4 space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70">Tanggal</label>
                        <input type="date" name="expense_date" value="{{ date('Y-m-d') }}"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70">Kategori</label>
                        <select name="category"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <option value="operational">Operasional Toko</option>
                            <option value="utilities">Listrik, Air & Internet</option>
                            <option value="supplies">Kemasan & Plastik</option>
                            <option value="salaries">Gaji & Upah Kasir</option>
                            <option value="maintenance">Perawatan & Reparasi</option>
                            <option value="other">Lain-lain</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70">Nominal (Rp)</label>
                        <input type="number" name="amount" required placeholder="0"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] tabular-nums font-semibold text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70">Metode Bayar</label>
                        <select name="payment_method"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <option value="cash">Kas Tunai Kasir</option>
                            <option value="petty_cash">Kas Kecil (Petty Cash)</option>
                            <option value="bank_transfer">Transfer Rekening Bank</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70">Akun Beban (Bagan Akun / COA)</label>
                    <select name="account_id"
                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="">Otomatis (Sesuai Standar Akuntansi)</option>
                        @foreach($accounts as $coa)
                            <option value="{{ $coa->id }}">{{ $coa->code }} - {{ $coa->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70">Sumber Kas / Rekening Bank</label>
                    <select name="cash_account_id"
                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="">Otomatis Sesuai Metode</option>
                        @foreach($cashAccounts as $ca)
                            <option value="{{ $ca->id }}">{{ $ca->name }} (Saldo: Rp {{ number_format($ca->current_balance, 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70">Outlet / Lokasi</label>
                    <select name="location_id"
                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="">Semua / Outlet Utama</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70">Keterangan Pengeluaran</label>
                    <input type="text" name="description" required placeholder="Misal: Beli es batu kristal & kantong kresek..."
                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="flex items-center gap-2 pt-2 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showCreateModal = false"
                        class="flex-1 h-11 rounded-[10px] text-[15px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 active:opacity-70 transition-all">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 h-11 rounded-[10px] text-[15px] font-semibold text-white bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.97] active:opacity-80 transition-all shadow-[0_1px_2px_rgba(255,59,48,0.25)]">
                        Simpan Pengeluaran
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
