@extends('layouts.app', [
    'title' => 'Faktur Penjualan (Invoices)',
    'headerTitle' => 'Manajemen Faktur & Penagihan',
    'headerSubtitle' => 'Kelola faktur penjualan komersial, pelacakan piutang, dan pencatatan pembayaran'
])

@section('content')
<div class="space-y-6">

    <!-- KPI Summary Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="glass-card p-4 rounded-2xl border border-slate-800 flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center shrink-0">
                <i data-lucide="receipt" class="w-5 h-5 text-blue-400"></i>
            </div>
            <div>
                <div class="text-[11px] text-slate-400 font-medium">Total Nilai Faktur</div>
                <div class="text-lg font-extrabold text-white font-mono">{{ $business->currency_symbol }} {{ number_format($totalInvoiced, 0, ',', '.') }}</div>
            </div>
        </div>

        <div class="glass-card p-4 rounded-2xl border border-slate-800 flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center shrink-0">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-400"></i>
            </div>
            <div>
                <div class="text-[11px] text-slate-400 font-medium">Sudah Diterima (Lunas)</div>
                <div class="text-lg font-extrabold text-emerald-400 font-mono">{{ $business->currency_symbol }} {{ number_format($totalPaid, 0, ',', '.') }}</div>
            </div>
        </div>

        <div class="glass-card p-4 rounded-2xl border border-slate-800 flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center shrink-0">
                <i data-lucide="clock" class="w-5 h-5 text-amber-400"></i>
            </div>
            <div>
                <div class="text-[11px] text-slate-400 font-medium">Sisa Piutang (Belum Dibayar)</div>
                <div class="text-lg font-extrabold text-amber-400 font-mono">{{ $business->currency_symbol }} {{ number_format($totalReceivables, 0, ',', '.') }}</div>
            </div>
        </div>

        <div class="glass-card p-4 rounded-2xl border border-slate-800 flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-teal-500/10 border border-teal-500/20 flex items-center justify-center shrink-0">
                <i data-lucide="trending-up" class="w-5 h-5 text-teal-400"></i>
            </div>
            <div>
                <div class="text-[11px] text-slate-400 font-medium">Laba Kotor Riil (Gross Profit)</div>
                <div class="text-lg font-extrabold text-teal-400 font-mono">{{ $business->currency_symbol }} {{ number_format($totalGrossProfit, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <!-- Search & Filters -->
        <form method="GET" action="{{ route('invoices.index') }}" class="flex-1 flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[220px] max-w-md">
                <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari no. faktur, nama klien..." 
                       class="w-full pl-10 pr-4 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-xs text-white">
            </div>

            <select name="status" onchange="this.form.submit()"
                    class="px-3 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-xs text-white">
                <option value="">Semua Status Pembayaran</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent (Terkirim)</option>
                <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid (Belum Bayar)</option>
                <option value="partially_paid" {{ request('status') === 'partially_paid' ? 'selected' : '' }}>Partially Paid (Cicilan)</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid (Lunas)</option>
                <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue (Jatuh Tempo)</option>
            </select>
        </form>

        <div class="flex items-center gap-2">
            <a href="{{ route('invoices.export-excel') }}" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                <i data-lucide="download" class="w-4 h-4 text-slate-400"></i>
                <span>Export CSV</span>
            </a>
            <a href="{{ route('invoices.create') }}" 
               class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2 transition-all shrink-0">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Buat Faktur Baru</span>
            </a>
        </div>
    </div>

    <!-- Invoices Table Card -->
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-800 bg-slate-900/50">
                        <th class="py-3.5 px-4 font-semibold">Nomor Faktur & Tanggal</th>
                        <th class="py-3.5 px-4 font-semibold">Pelanggan / Klien</th>
                        <th class="py-3.5 px-4 font-semibold">Jatuh Tempo</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Status Bayar</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Total Tagihan</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Sisa Piutang</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Laba Kotor</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($invoices as $inv)
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="py-3 px-4">
                            <a href="{{ route('invoices.show', $inv->id) }}" class="font-bold text-white text-sm hover:text-emerald-400 font-mono transition-colors">
                                {{ $inv->invoice_number }}
                            </a>
                            <div class="text-[11px] text-slate-400 mt-0.5">{{ $inv->invoice_date?->translatedFormat('d M Y') }}</div>
                            @if($inv->purchaseOrder)
                                <div class="text-[10px] text-slate-500 font-mono mt-0.5">PO: {{ $inv->purchaseOrder->po_number }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-semibold text-white">{{ $inv->customer?->name ?? 'Pelanggan' }}</div>
                            @if($inv->customer?->company_name)
                                <div class="text-[11px] text-slate-400">{{ $inv->customer->company_name }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-300">
                            <div>{{ $inv->due_date?->translatedFormat('d M Y') }}</div>
                            @if($inv->balance_due > 0 && $inv->due_date && $inv->due_date->isPast())
                                <div class="text-[10px] text-rose-400 font-bold">Terlewat {{ $inv->due_date->diffForHumans() }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            @php
                                $badges = [
                                    'draft' => 'bg-slate-800 text-slate-300 border-slate-700',
                                    'sent' => 'bg-blue-500/15 text-blue-400 border-blue-500/30',
                                    'unpaid' => 'bg-sky-500/15 text-sky-400 border-sky-500/30',
                                    'partially_paid' => 'bg-amber-500/15 text-amber-400 border-amber-500/30',
                                    'paid' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
                                    'overdue' => 'bg-rose-500/15 text-rose-400 border-rose-500/30',
                                    'void' => 'bg-slate-800 text-slate-500 border-slate-700 line-through',
                                ];
                                $badgeClass = $badges[$inv->status] ?? 'bg-slate-800 text-slate-300 border-slate-700';
                            @endphp
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $badgeClass }}">
                                {{ str_replace('_', ' ', $inv->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-white text-sm">
                            {{ $business->currency_symbol }} {{ number_format((float) $inv->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-semibold {{ $inv->balance_due > 0 ? 'text-amber-400' : 'text-emerald-400' }}">
                            {{ $business->currency_symbol }} {{ number_format((float) $inv->balance_due, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-semibold text-teal-400">
                            {{ $business->currency_symbol }} {{ number_format((float) $inv->total_gross_profit, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('invoices.show', $inv->id) }}" class="p-1.5 hover:bg-slate-800 rounded-lg text-slate-400 hover:text-white transition-colors" title="Lihat Faktur">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>

                                <a href="{{ route('invoices.print', $inv->id) }}?download=1" target="_blank" class="p-1.5 hover:bg-emerald-500/20 rounded-lg text-slate-400 hover:text-emerald-400 transition-colors" title="Download PDF Langsung">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                </a>

                                <a href="{{ route('invoices.print', $inv->id) }}" target="_blank" class="p-1.5 hover:bg-slate-800 rounded-lg text-slate-400 hover:text-white transition-colors" title="Cetak / Pratinjau Faktur">
                                    <i data-lucide="printer" class="w-4 h-4"></i>
                                </a>

                                @if($inv->status === 'draft' || $inv->status === 'void')
                                <form method="POST" action="{{ route('invoices.destroy', $inv->id) }}" onsubmit="return confirm('Hapus faktur {{ $inv->invoice_number }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 hover:bg-rose-500/20 rounded-lg text-slate-400 hover:text-rose-400 transition-colors" title="Hapus Faktur">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-500">
                            <i data-lucide="receipt" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                            <p class="text-sm font-medium">Belum ada faktur penjualan yang diterbitkan.</p>
                            <p class="text-xs text-slate-400 mt-1">Buat faktur dari katalog produk, PO pelanggan, atau langsung dari kalkulator HPP.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
        <div class="p-4 border-t border-slate-800 bg-slate-900/40">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
