@extends('layouts.app', ['title' => 'Piutang Usaha (AR Aging)'])

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
                <span class="text-emerald-400 font-semibold">Piutang Usaha (AR Aging)</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Piutang Usaha (AR Aging)</h1>
            <p class="text-sm text-slate-400 mt-0.5">Analisis umur piutang pelanggan dan jadwal penagihan faktur tempo.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('invoices.create') }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg shadow-emerald-500/20 transition flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Buat Faktur Baru</span>
            </a>
            <a href="{{ route('invoices.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs transition flex items-center gap-2">
                <i data-lucide="receipt" class="w-4 h-4 text-emerald-400"></i>
                <span>Daftar Faktur</span>
            </a>
        </div>
    </div>

    @php
        $totalBalance = $invoices->sum('balance_due');
        $notDue = $invoices->filter(fn($i) => $i->due_date && $i->due_date->isFuture())->sum('balance_due');
        $overdue1to30 = $invoices->filter(function($i) {
            if (!$i->due_date || $i->due_date->isFuture()) return false;
            $days = now()->diffInDays($i->due_date);
            return $days >= 0 && $days <= 30;
        })->sum('balance_due');
        $overdue30plus = $invoices->filter(function($i) {
            if (!$i->due_date || $i->due_date->isFuture()) return false;
            return now()->diffInDays($i->due_date) > 30;
        })->sum('balance_due');
    @endphp

    <!-- AR Aging Summary Widgets -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="glass-card rounded-2xl p-4 border border-slate-800 flex justify-between items-center">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Piutang Berjalan</div>
                <div class="text-xl font-black text-white font-mono mt-1">Rp {{ number_format($totalBalance, 0, ',', '.') }}</div>
                <div class="text-[10px] text-slate-500 mt-0.5">{{ $invoices->total() }} Faktur Terbuka</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                <i data-lucide="wallet" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-4 border border-slate-800 flex justify-between items-center">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Belum Jatuh Tempo (Lancar)</div>
                <div class="text-xl font-black text-emerald-400 font-mono mt-1">Rp {{ number_format($notDue, 0, ',', '.') }}</div>
                <div class="text-[10px] text-slate-500 mt-0.5">Sesuai termin pembayaran</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-4 border border-slate-800 flex justify-between items-center">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Lewat Tempo (1-30 Hari)</div>
                <div class="text-xl font-black text-amber-400 font-mono mt-1">Rp {{ number_format($overdue1to30, 0, ',', '.') }}</div>
                <div class="text-[10px] text-amber-500/80 mt-0.5">Perlu tindak lanjut tagihan</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
                <i data-lucide="clock" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-4 border border-slate-800 flex justify-between items-center">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Lewat Tempo (> 30 Hari)</div>
                <div class="text-xl font-black text-rose-400 font-mono mt-1">Rp {{ number_format($overdue30plus, 0, ',', '.') }}</div>
                <div class="text-[10px] text-rose-500/80 mt-0.5">Kategori kritis / macet</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
        <div class="p-4 border-b border-slate-800/80 flex items-center justify-between">
            <h2 class="text-sm font-bold text-white flex items-center gap-2">
                <i data-lucide="arrow-down-left" class="w-4 h-4 text-cyan-400"></i>
                <span>Daftar Piutang Faktur Pelanggan</span>
            </h2>
            <span class="text-xs text-slate-400 font-mono">{{ $invoices->total() }} Faktur</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="border-b border-slate-800 bg-slate-950/60 uppercase text-[10px] text-slate-400 font-bold tracking-wider">
                    <tr>
                        <th class="p-4">No. Faktur</th>
                        <th class="p-4">Pelanggan</th>
                        <th class="p-4">Tgl. Terbit</th>
                        <th class="p-4">Jatuh Tempo</th>
                        <th class="p-4 text-right">Total Faktur</th>
                        <th class="p-4 text-right">Sisa Piutang</th>
                        <th class="p-4 text-center">Umur Piutang</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-sans">
                    @forelse($invoices as $invoice)
                    @php
                        $daysDiff = $invoice->due_date ? (int) now()->startOfDay()->diffInDays($invoice->due_date, false) : 0;
                        $isOverdue = $daysDiff < 0;
                        $daysOverdue = abs($daysDiff);
                    @endphp
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="p-4 font-mono font-bold text-white">
                            <a href="{{ route('invoices.show', $invoice) }}" class="hover:text-emerald-400 transition">
                                {{ $invoice->invoice_number }}
                            </a>
                        </td>
                        <td class="p-4 font-semibold text-slate-200">
                            {{ $invoice->customer?->name ?? 'Pelanggan Umum' }}
                            @if($invoice->customer?->phone)
                                <div class="text-[10px] text-slate-500 font-mono">{{ $invoice->customer->phone }}</div>
                            @endif
                        </td>
                        <td class="p-4 text-slate-400">{{ $invoice->invoice_date?->format('d M Y') ?? '-' }}</td>
                        <td class="p-4 {{ $isOverdue ? 'text-rose-400 font-bold' : 'text-slate-300' }}">
                            {{ $invoice->due_date?->format('d M Y') ?? '-' }}
                        </td>
                        <td class="p-4 text-right font-mono text-slate-400">
                            Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="p-4 text-right font-mono font-black text-cyan-400">
                            Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}
                        </td>
                        <td class="p-4 text-center">
                            @if($isOverdue)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                    Lewat {{ $daysOverdue }} hr
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    Sisa {{ $daysDiff }} hr
                                </span>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            <a href="{{ route('invoices.show', $invoice) }}" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-emerald-600 hover:text-white text-slate-300 font-bold transition text-[11px] inline-flex items-center gap-1">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                <span>Detail</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-12 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <i data-lucide="check-circle-2" class="w-8 h-8 text-emerald-500/40"></i>
                                <span class="text-sm font-semibold text-slate-400">Semua Piutang Pelanggan Telah Lunas!</span>
                                <span class="text-xs text-slate-600">Tidak ada saldo piutang berjalan yang menunggu pembayaran.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
        <div class="p-4 border-t border-slate-800 bg-slate-950/40">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
