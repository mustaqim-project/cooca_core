@extends('layouts.app', ['title' => 'Jurnal Otomatis & Buku Besar'])

@section('content')
<div class="space-y-6">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Jurnal Umum & Pembukuan Otomatis</h1>
            <p class="text-sm text-slate-400 mt-1">Seluruh transaksi penjualan kasir POS, HPP modal, dan biaya operasional otomatis dijurnal berpasangan (*double-entry*).</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('finance.expenses.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition flex items-center gap-2">
                <i data-lucide="wallet" class="w-4 h-4 text-emerald-400"></i>
                <span>Beban Operasional Toko</span>
            </a>
        </div>
    </div>

    <!-- Balance Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="glass-card rounded-2xl p-4 border border-slate-800 flex justify-between items-center">
            <div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Debit Keseluruhan</div>
                <div class="text-2xl font-black text-emerald-400 font-mono mt-1">Rp {{ number_format($totalDebit, 0, ',', '.') }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                <i data-lucide="plus-circle" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-4 border border-slate-800 flex justify-between items-center">
            <div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Kredit Keseluruhan</div>
                <div class="text-2xl font-black text-teal-400 font-mono mt-1">Rp {{ number_format($totalCredit, 0, ',', '.') }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-teal-500/10 text-teal-400 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Journal Entries Table -->
    <div class="space-y-4">
        @forelse($entries as $entry)
        <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
            <div class="p-3.5 bg-slate-900/80 border-b border-slate-800/80 flex flex-wrap items-center justify-between gap-2 text-xs">
                <div class="flex items-center gap-3">
                    <span class="font-black text-white font-mono">#{{ $entry->entry_number }}</span>
                    <span class="text-slate-400 font-sans">{{ $entry->entry_date->format('d/m/Y') }}</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-800 text-slate-300">
                        {{ strtoupper($entry->reference_type ?? 'Jurnal') }}
                    </span>
                </div>
                <div class="text-slate-400 font-medium">
                    {{ $entry->description }}
                </div>
            </div>

            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/60 text-slate-500 uppercase text-[9px] font-bold border-b border-slate-800/40">
                    <tr>
                        <th class="py-2 px-4">Kode & Nama Akun</th>
                        <th class="py-2 px-4 text-right">Debit (Rp)</th>
                        <th class="py-2 px-4 text-right">Kredit (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40 font-mono">
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
                                <span class="font-bold text-white">{{ number_format($line->amount, 0, ',', '.') }}</span>
                            @else
                                <span class="text-slate-600">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @empty
        <div class="text-center py-12 glass-card rounded-2xl border border-slate-800 text-slate-500 text-xs">
            Belum ada ayat jurnal transaksi yang tercatat. Jurnal akan terisi otomatis saat kasir memproses transaksi POS.
        </div>
        @endforelse

        @if($entries->hasPages())
        <div class="p-4 glass-card rounded-2xl border border-slate-800">
            {{ $entries->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
