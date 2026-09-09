@extends('layouts.app', ['title' => 'Pesanan Penjualan (Sales Orders)'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-cyan-500/20 text-cyan-400 border border-cyan-500/30">
                    SALES PIPELINE &amp; FULFILLMENT
                </span>
                <span class="text-xs text-slate-400 font-mono">Pesanan Terkonfirmasi</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight mt-1">Pesanan Penjualan (Sales Orders)</h1>
            <p class="text-xs text-slate-400 mt-0.5">Kelola pesanan pelanggan terkonfirmasi, alokasi stok, dan terbitkan faktur penagihan (Invoice).</p>
        </div>
        <a href="{{ route('sales.orders.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-xs font-black shadow-lg shadow-cyan-500/20 transition whitespace-nowrap">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Buat Sales Order</span>
        </a>
    </div>


    <!-- Filter Bar -->
    <div class="glass-card rounded-2xl p-4 border border-slate-800 flex flex-col sm:flex-row gap-3 items-center justify-between">
        <form method="GET" action="{{ route('sales.orders.index') }}" class="flex-1 flex flex-wrap gap-2 w-full">
            <div class="relative flex-1 min-w-[200px]">
                <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor SO atau nama pelanggan..."
                       class="w-full bg-slate-900 border border-slate-700/80 rounded-xl pl-9 pr-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500">
            </div>
            <select name="status" onchange="this.form.submit()" class="bg-slate-900 border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-cyan-500">
                <option value="">Semua Status</option>
                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Terkonfirmasi</option>
                <option value="partially_fulfilled" {{ request('status') === 'partially_fulfilled' ? 'selected' : '' }}>Sebagian Terpenuhi</option>
                <option value="fulfilled" {{ request('status') === 'fulfilled' ? 'selected' : '' }}>Selesai (Fulfilled)</option>
            </select>
        </form>
    </div>

    <!-- Table -->
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
        <div class="table-responsive">
            <table class="w-full text-left text-xs text-slate-300 min-w-[700px]">
                <thead class="bg-slate-900/80 text-slate-400 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-800 whitespace-nowrap">
                    <tr>
                        <th class="py-3 px-4">No. Sales Order</th>
                        <th class="py-3 px-4">Pelanggan</th>
                        <th class="py-3 px-4">Tanggal Pesanan</th>
                        <th class="py-3 px-4">Estimasi Kirim</th>
                        <th class="py-3 px-4 text-right">Pajak</th>
                        <th class="py-3 px-4 text-right">Nilai Pesanan</th>
                        <th class="py-3 px-4 text-center">Status Pemenuhan</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-mono">
                    @forelse($salesOrders as $so)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3.5 px-4 font-bold text-white">
                            <a href="{{ route('sales.orders.show', $so) }}" class="text-cyan-400 hover:underline">
                                {{ $so->so_number }}
                            </a>
                        </td>
                        <td class="py-3.5 px-4 font-sans font-medium text-white">
                            {{ $so->customer->name }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-400">
                            {{ $so->order_date->format('d M Y') }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-400">
                            {{ $so->expected_delivery_date ? $so->expected_delivery_date->format('d M Y') : '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-right text-amber-300">
                            Rp {{ number_format($so->tax_amount, 0, ',', '.') }}
                            <span class="block text-[10px] text-slate-500">{{ number_format($so->tax_percentage, 2) }}%</span>
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-white text-sm">
                            Rp {{ number_format($so->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-center font-sans">
                            @if($so->status === 'fulfilled')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Selesai</span>
                            @elseif($so->status === 'partially_fulfilled')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">Sebagian</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-500/20 text-cyan-400 border border-cyan-500/30">Terkonfirmasi</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center font-sans">
                            <a href="{{ route('sales.orders.show', $so) }}" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs transition">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-500 font-sans">Belum ada pesanan penjualan (Sales Order).</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($salesOrders->hasPages())
        <div class="p-4 border-t border-slate-800">
            {{ $salesOrders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
