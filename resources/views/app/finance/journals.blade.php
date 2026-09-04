@extends('layouts.app', ['title' => 'Jurnal Akuntansi Otomatis'])

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <span>Keuangan & Kas</span>
                <i data-lucide="chevron-right" class="w-3 h-3 text-slate-600"></i>
                <span class="text-amber-400 font-semibold">Jurnal Akuntansi Otomatis</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Jurnal Akuntansi Otomatis</h1>
            <p class="text-xs text-slate-400 mt-1">Seluruh transaksi penjualan POS, pengeluaran beban, persediaan stok, dan faktur otomatis dijurnal berpasangan (*double-entry*).</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('finance.cash-bank.ledger') }}" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-amber-300 text-xs font-semibold flex items-center gap-2 transition">
                <i data-lucide="book" class="w-4 h-4"></i>
                <span>Buku Kas & Ledger</span>
            </a>
            <a href="{{ route('finance.expenses.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition flex items-center gap-2">
                <i data-lucide="wallet" class="w-4 h-4 text-rose-400"></i>
                <span>Beban Operasional</span>
            </a>
        </div>
    </div>

    <!-- Balance Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="glass-card rounded-2xl p-5 border border-slate-800 flex justify-between items-center">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Debit Pembukuan</div>
                <div class="text-2xl font-black text-emerald-400 font-mono mt-1">Rp {{ number_format($totalDebit, 0, ',', '.') }}</div>
                <div class="text-[10px] text-slate-500 mt-0.5">Saldo debit terverifikasi seimbang</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                <i data-lucide="plus-circle" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-5 border border-slate-800 flex justify-between items-center">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Kredit Pembukuan</div>
                <div class="text-2xl font-black text-teal-400 font-mono mt-1">Rp {{ number_format($totalCredit, 0, ',', '.') }}</div>
                <div class="text-[10px] text-slate-500 mt-0.5">Saldo kredit terverifikasi seimbang</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-teal-500/10 border border-teal-500/20 text-teal-400 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Journal Entries List -->
    <div class="space-y-4">
        @forelse($entries as $entry)
        <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden shadow-xl">
            <div class="p-4 bg-slate-900/90 border-b border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-3">
                    <span class="font-mono font-black text-amber-400">#{{ $entry->entry_number }}</span>
                    <span class="text-slate-400">{{ $entry->entry_date->format('d M Y') }}</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-800 text-slate-300 border border-slate-700">
                        {{ strtoupper($entry->reference_type ?? 'Jurnal') }}
                    </span>
                </div>
                <div class="text-slate-300 font-medium truncate max-w-md">
                    {{ $entry->description }}
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300 font-mono">
                    <thead class="bg-slate-950/60 text-slate-500 uppercase text-[9px] font-bold border-b border-slate-800/40">
                        <tr>
                            <th class="py-2.5 px-4 font-sans">Kode & Nama Akun (COA)</th>
                            <th class="py-2.5 px-4 text-right">Debit (Rp)</th>
                            <th class="py-2.5 px-4 text-right">Kredit (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/40">
                        @foreach($entry->lines as $line)
                        <tr class="hover:bg-slate-800/30">
                            <td class="py-2.5 px-4 font-sans">
                                <span class="font-mono text-emerald-400 font-bold mr-2">{{ $line->account->code ?? '-' }}</span>
                                <span class="text-slate-200">{{ $line->account->name ?? '-' }}</span>
                                @if($line->notes)
                                    <span class="text-[10px] text-slate-500 ml-2">({{ $line->notes }})</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-4 text-right">
                                @if($line->type === 'debit')
                                    <span class="font-bold text-white">{{ number_format($line->amount, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-slate-600">-</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-4 text-right">
                                @if($line->type === 'credit')
                                    <span class="font-bold text-teal-300">{{ number_format($line->amount, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-slate-600">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @empty
        <div class="text-center py-16 glass-card rounded-2xl border border-slate-800 text-slate-500 text-xs">
            <div class="flex flex-col items-center justify-center gap-2">
                <i data-lucide="book-open" class="w-8 h-8 text-slate-600"></i>
                <span>Belum ada entri jurnal akuntansi. Jurnal dibuat otomatis saat kasir checkout POS atau beban dicatat.</span>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
