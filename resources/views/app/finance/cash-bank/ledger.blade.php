@extends('layouts.app', ['title' => 'Buku Kas & Ledger'])

@section('content')
<div class="space-y-6">

    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-emerald-400 transition">Dashboard</a>
                <span>/</span>
                <span class="text-slate-500">Keuangan</span>
                <span>/</span>
                <a href="{{ route('finance.cash-bank.index') }}" class="hover:text-emerald-400 transition">Kas & Bank</a>
                <span>/</span>
                <span class="text-amber-400 font-semibold">Buku Kas & Ledger</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Buku Kas & Ledger Rekening</h1>
            <p class="text-sm text-slate-400 mt-0.5">Riwayat kronologis mutasi debit dan kredit seluruh akun kas dan rekening bank.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('finance.cash-bank.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs transition flex items-center gap-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Kas & Bank</span>
            </a>
        </div>
    </div>

    <!-- Filter & Selector Bar -->
    <div class="glass-card rounded-2xl p-4 border border-slate-800 flex flex-wrap items-center justify-between gap-4">
        <form method="GET" action="{{ route('finance.cash-bank.ledger') }}" class="flex items-center flex-wrap gap-3">
            <div class="flex items-center gap-2">
                <i data-lucide="filter" class="w-4 h-4 text-slate-400"></i>
                <span class="text-xs font-bold text-slate-300">Pilih Rekening:</span>
            </div>
            <select name="account_id" onchange="this.form.submit()" class="px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:border-amber-500">
                <option value="">Semua Rekening Kas & Bank</option>
                @foreach($accounts as $item)
                    <option value="{{ $item->id }}" @selected($account?->id === $item->id)>
                        {{ $item->name }} (Rp {{ number_format($item->current_balance, 0, ',', '.') }})
                    </option>
                @endforeach
            </select>
        </form>

        @if($account)
        <div class="text-right">
            <span class="text-[10px] uppercase font-bold text-slate-500">Saldo Rekening Terpilih</span>
            <div class="text-lg font-black text-emerald-400 font-mono">
                Rp {{ number_format($account->current_balance, 0, ',', '.') }}
            </div>
        </div>
        @endif
    </div>

    <!-- Ledger Table Card -->
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
        <div class="p-4 border-b border-slate-800/80 flex items-center justify-between">
            <h2 class="text-sm font-bold text-white flex items-center gap-2">
                <i data-lucide="book-open" class="w-4 h-4 text-amber-400"></i>
                <span>Buku Mutasi Kas & Ledger</span>
            </h2>
            <span class="text-xs text-slate-400 font-mono">{{ $transactions->total() }} Baris Transaksi</span>
        </div>

        <div class="table-responsive">
            <table class="w-full text-left text-xs text-slate-300 min-w-[620px]">
                <thead class="border-b border-slate-800 bg-slate-950/60 uppercase text-[10px] text-slate-400 font-bold tracking-wider">
                    <tr>
                        <th class="p-4 whitespace-nowrap">Tanggal</th>
                        <th class="p-4 whitespace-nowrap">Rekening</th>
                        <th class="p-4 whitespace-nowrap">Arus</th>
                        <th class="p-4 whitespace-nowrap">Keterangan / Referensi</th>
                        <th class="p-4 text-right whitespace-nowrap">Debit / Kredit</th>
                        <th class="p-4 text-right whitespace-nowrap">Saldo Berjalan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-sans">
                    @forelse($transactions as $transaction)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="p-4 font-mono text-slate-400">{{ $transaction->transaction_date->format('d M Y') }}</td>
                        <td class="p-4 font-semibold text-white">{{ $transaction->cashAccount->name }}</td>
                        <td class="p-4">
                            @if($transaction->type === 'out')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-rose-500/10 text-rose-400 border border-rose-500/20">Keluar</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Masuk</span>
                            @endif
                        </td>
                        <td class="p-4 text-slate-300">{{ $transaction->description }}</td>
                        <td class="p-4 text-right font-mono font-bold {{ $transaction->type === 'out' ? 'text-rose-400' : 'text-emerald-400' }}">
                            {{ $transaction->type === 'out' ? '-' : '+' }} Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                        </td>
                        <td class="p-4 text-right font-mono font-black text-white">
                            Rp {{ number_format($transaction->balance_after, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-12 text-center text-slate-500">
                            Belum ada riwayat mutasi buku kas untuk akun ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
        <div class="p-4 border-t border-slate-800 bg-slate-950/40">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
