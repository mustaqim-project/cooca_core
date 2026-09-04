@extends('layouts.app', ['title' => 'Hutang Usaha (AP Aging)'])

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
                <span class="text-amber-400 font-semibold">Hutang Usaha (AP Aging)</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Hutang Usaha (AP Aging)</h1>
            <p class="text-sm text-slate-400 mt-0.5">Pantau jadwal jatuh tempo tagihan pemasok untuk menjaga kelancaran arus kas.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('purchasing.bills.index') }}" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-lg shadow-amber-500/20 transition flex items-center gap-2">
                <i data-lucide="receipt" class="w-4 h-4"></i>
                <span>Tagihan Supplier</span>
            </a>
            <a href="{{ route('suppliers.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs transition flex items-center gap-2">
                <i data-lucide="users-2" class="w-4 h-4 text-amber-400"></i>
                <span>Daftar Pemasok</span>
            </a>
        </div>
    </div>

    @php
        $totalPayable = $invoices->sum('balance_due');
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

    <!-- AP Aging Summary Widgets -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="glass-card rounded-2xl p-4 border border-slate-800 flex justify-between items-center">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Hutang Supplier</div>
                <div class="text-xl font-black text-white font-mono mt-1">Rp {{ number_format($totalPayable, 0, ',', '.') }}</div>
                <div class="text-[10px] text-slate-500 mt-0.5">{{ $invoices->total() }} Tagihan Terbuka</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
                <i data-lucide="credit-card" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-4 border border-slate-800 flex justify-between items-center">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Belum Jatuh Tempo (Lancar)</div>
                <div class="text-xl font-black text-emerald-400 font-mono mt-1">Rp {{ number_format($notDue, 0, ',', '.') }}</div>
                <div class="text-[10px] text-slate-500 mt-0.5">Sesuai tempo pembayaran</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-4 border border-slate-800 flex justify-between items-center">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Lewat Tempo (1-30 Hari)</div>
                <div class="text-xl font-black text-amber-400 font-mono mt-1">Rp {{ number_format($overdue1to30, 0, ',', '.') }}</div>
                <div class="text-[10px] text-amber-500/80 mt-0.5">Segera jadwalkan transfer</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
                <i data-lucide="clock" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-4 border border-slate-800 flex justify-between items-center">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Lewat Tempo (> 30 Hari)</div>
                <div class="text-xl font-black text-rose-400 font-mono mt-1">Rp {{ number_format($overdue30plus, 0, ',', '.') }}</div>
                <div class="text-[10px] text-rose-500/80 mt-0.5">Prioritas pelunasan mendesak</div>
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
                <i data-lucide="arrow-up-right" class="w-4 h-4 text-amber-400"></i>
                <span>Daftar Tagihan Hutang Supplier</span>
            </h2>
            <span class="text-xs text-slate-400 font-mono">{{ $invoices->total() }} Tagihan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="border-b border-slate-800 bg-slate-950/60 uppercase text-[10px] text-slate-400 font-bold tracking-wider">
                    <tr>
                        <th class="p-4">No. Tagihan</th>
                        <th class="p-4">Pemasok / Vendor</th>
                        <th class="p-4">Tgl. Tagihan</th>
                        <th class="p-4">Jatuh Tempo</th>
                        <th class="p-4 text-right">Total Tagihan</th>
                        <th class="p-4 text-right">Sisa Hutang</th>
                        <th class="p-4 text-center">Umur Hutang</th>
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
                            {{ $invoice->invoice_number ?? $invoice->bill_number ?? '-' }}
                        </td>
                        <td class="p-4 font-semibold text-slate-200">
                            {{ $invoice->supplier?->name ?? 'Supplier Umum' }}
                            @if($invoice->supplier?->phone)
                                <div class="text-[10px] text-slate-500 font-mono">{{ $invoice->supplier->phone }}</div>
                            @endif
                        </td>
                        <td class="p-4 text-slate-400">{{ $invoice->invoice_date?->format('d M Y') ?? $invoice->created_at?->format('d M Y') ?? '-' }}</td>
                        <td class="p-4 {{ $isOverdue ? 'text-rose-400 font-bold' : 'text-slate-300' }}">
                            {{ $invoice->due_date?->format('d M Y') ?? '-' }}
                        </td>
                        <td class="p-4 text-right font-mono text-slate-400">
                            Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="p-4 text-right font-mono font-black text-amber-400">
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
                            <a href="{{ route('purchasing.bills.index') }}" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-amber-500 hover:text-slate-950 text-slate-300 font-bold transition text-[11px] inline-flex items-center gap-1">
                                <i data-lucide="wallet" class="w-3.5 h-3.5"></i>
                                <span>Bayar</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-12 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <i data-lucide="shield-check" class="w-8 h-8 text-emerald-500/40"></i>
                                <span class="text-sm font-semibold text-slate-400">Tidak Ada Hutang Supplier Terbuka</span>
                                <span class="text-xs text-slate-600">Seluruh tagihan pembelian telah dibayar lunas.</span>
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
