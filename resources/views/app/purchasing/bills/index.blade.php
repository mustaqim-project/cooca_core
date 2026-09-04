@extends('layouts.app', ['title' => 'Tagihan & Hutang Supplier'])

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <span>Pembelian & Vendor</span>
                <i data-lucide="chevron-right" class="w-3 h-3 text-slate-600"></i>
                <span class="text-amber-400 font-semibold">Tagihan & Hutang Supplier</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Tagihan & Hutang Supplier</h1>
            <p class="text-xs text-slate-400 mt-1">Kelola faktur tagihan masuk dari supplier atas penerimaan barang (PO) dan pantau jadwal pelunasan.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('finance.payables') }}" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-amber-300 text-xs font-semibold flex items-center gap-2 transition">
                <i data-lucide="clock" class="w-4 h-4"></i>
                <span>Analisis AP Aging</span>
            </a>
            <a href="{{ route('purchase-orders.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold flex items-center gap-2 transition">
                <i data-lucide="file-text" class="w-4 h-4 text-amber-400"></i>
                <span>Daftar PO</span>
            </a>
        </div>
    </div>

    <!-- KPI Summary Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="glass-card rounded-2xl p-4 border border-slate-800">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Tagihan Masuk</div>
                    <div class="text-xl font-black text-white font-mono mt-1">
                        Rp {{ number_format($invoices->sum('total_amount'), 0, ',', '.') }}
                    </div>
                </div>
                <div class="p-2.5 rounded-xl bg-slate-800 text-slate-300">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-4 border border-slate-800">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] font-bold text-amber-400 uppercase tracking-wider">Sisa Hutang Belum Lunas</div>
                    <div class="text-xl font-black text-amber-400 font-mono mt-1">
                        Rp {{ number_format($invoices->sum('balance_due'), 0, ',', '.') }}
                    </div>
                </div>
                <div class="p-2.5 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20">
                    <i data-lucide="hourglass" class="w-5 h-5"></i>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-4 border border-slate-800">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Tagihan Terdata</div>
                    <div class="text-xl font-black text-white font-mono mt-1">
                        {{ $invoices->total() }} Faktur
                    </div>
                </div>
                <div class="p-2.5 rounded-xl bg-blue-500/10 text-blue-400">
                    <i data-lucide="files" class="w-5 h-5"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="glass-card rounded-2xl p-4 border border-slate-800">
        <form method="GET" class="flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2">
                <span class="text-slate-400 font-semibold">Filter Status:</span>
                <select name="status" onchange="this.form.submit()" class="bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white focus:border-amber-500">
                    <option value="">Semua Status Tagihan</option>
                    <option value="unpaid" @selected(request('status') === 'unpaid')>Belum Bayar (Unpaid)</option>
                    <option value="partial" @selected(request('status') === 'partial')>Bayar Sebagian (Partial)</option>
                    <option value="paid" @selected(request('status') === 'paid')>Lunas (Paid)</option>
                </select>
            </div>
            @if(request('status'))
            <a href="{{ route('purchasing.bills.index') }}" class="text-slate-400 hover:text-white transition flex items-center gap-1">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                <span>Reset Filter</span>
            </a>
            @endif
        </form>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Invoices Table -->
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-900/90 text-slate-400 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">No. Tagihan</th>
                        <th class="py-3.5 px-4">Pemasok / Vendor</th>
                        <th class="py-3.5 px-4">Tanggal Masuk</th>
                        <th class="py-3.5 px-4">Jatuh Tempo</th>
                        <th class="py-3.5 px-4 text-right">Total Tagihan</th>
                        <th class="py-3.5 px-4 text-right">Sisa Hutang</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-mono">
                    @forelse($invoices as $invoice)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3 px-4 font-bold text-white">
                            <a href="{{ route('purchasing.bills.show', $invoice) }}" class="text-amber-400 hover:underline flex items-center gap-1.5 font-sans">
                                <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                                <span>{{ $invoice->invoice_number }}</span>
                            </a>
                        </td>
                        <td class="py-3 px-4 font-sans font-medium text-slate-200">
                            {{ $invoice->supplier->name ?? 'Supplier Umum' }}
                        </td>
                        <td class="py-3 px-4 font-sans text-slate-400">
                            {{ $invoice->invoice_date->format('d/m/Y') }}
                        </td>
                        <td class="py-3 px-4 font-sans">
                            @if($invoice->due_date)
                                <span class="{{ $invoice->due_date->isPast() && $invoice->balance_due > 0 ? 'text-rose-400 font-bold' : 'text-slate-400' }}">
                                    {{ $invoice->due_date->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-slate-600">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-white">
                            Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-black {{ $invoice->balance_due > 0 ? 'text-amber-400' : 'text-emerald-400' }}">
                            Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-center font-sans">
                            @if($invoice->status === 'paid')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Lunas</span>
                            @elseif($invoice->status === 'partial')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">Sebagian</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">Belum Bayar</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right font-sans">
                            <a href="{{ route('purchasing.bills.show', $invoice) }}" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold inline-flex items-center gap-1 transition">
                                <span>Detail</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-500 text-xs font-sans">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <i data-lucide="receipt" class="w-8 h-8 text-slate-600"></i>
                                <span>Belum ada tagihan supplier yang terdaftar.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $invoices->links() }}
    </div>
</div>
@endsection
