@extends('layouts.app', ['title' => 'Retur Penjualan'])

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <span>Kasir & Penjualan</span>
                <i data-lucide="chevron-right" class="w-3 h-3 text-slate-600"></i>
                <span class="text-rose-400 font-semibold">Retur Penjualan</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Retur Penjualan &amp; Pengembalian</h1>
            <p class="text-xs text-slate-400 mt-1">Kelola pengembalian barang dari pelanggan atas faktur penjualan atau struk kasir POS, penerbitan credit note, dan pengembalian stok.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('sales.returns.create') }}" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-rose-500 to-pink-500 hover:from-rose-400 hover:to-pink-400 text-white font-bold text-xs shadow-lg shadow-rose-500/20 flex items-center gap-2 transition cursor-pointer">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Buat Retur Penjualan</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="glass-card rounded-2xl p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Retur List Table -->
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-900/90 text-slate-400 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">No. Retur</th>
                        <th class="py-3.5 px-4">Referensi Transaksi</th>
                        <th class="py-3.5 px-4">Pelanggan</th>
                        <th class="py-3.5 px-4">Tanggal</th>
                        <th class="py-3.5 px-4 text-right">Nilai Retur</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-mono">
                    @forelse($returns as $return)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3 px-4 font-bold text-white">
                            <a href="{{ route('sales.returns.show', $return) }}" class="text-rose-400 hover:underline flex items-center gap-1.5 font-sans">
                                <i data-lucide="undo-2" class="w-3.5 h-3.5"></i>
                                <span>{{ $return->return_number }}</span>
                            </a>
                        </td>
                        <td class="py-3 px-4 font-sans text-slate-300">
                            @if($return->invoice)
                                <div class="flex items-center gap-1.5">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30 font-mono">INV</span>
                                    <span class="font-mono text-white">{{ $return->invoice->invoice_number }}</span>
                                </div>
                            @elseif($return->posOrder)
                                <div class="flex items-center gap-1.5">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30 font-mono">POS</span>
                                    <span class="font-mono text-white">{{ $return->posOrder->order_number }}</span>
                                </div>
                            @else
                                <span class="text-slate-500">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-sans font-medium text-slate-200">
                            @if($return->invoice)
                                {{ $return->invoice->customer?->name ?? 'Pelanggan Umum' }}
                            @elseif($return->posOrder)
                                {{ $return->posOrder->customer?->name ?? ($return->posOrder->customer_name_guest ?: 'Tamu Kasir') }}
                            @else
                                {{ $return->customer?->name ?? 'Pelanggan Umum' }}
                            @endif
                        </td>
                        <td class="py-3 px-4 font-sans text-slate-400">
                            {{ $return->return_date ? $return->return_date->format('d/m/Y') : $return->created_at->format('d/m/Y') }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-white">
                            Rp {{ number_format($return->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-center font-sans">
                            @if($return->status === 'completed')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Selesai</span>
                            @elseif($return->status === 'approved')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">Disetujui</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">Draft</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right font-sans">
                            <a href="{{ route('sales.returns.show', $return) }}" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold inline-flex items-center gap-1 transition">
                                <span>Detail</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-500 text-xs font-sans">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <i data-lucide="undo-2" class="w-8 h-8 text-slate-600"></i>
                                <span>Belum ada transaksi retur penjualan.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $returns->links() }}
    </div>
</div>
@endsection
