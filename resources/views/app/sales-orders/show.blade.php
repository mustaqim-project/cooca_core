@extends('layouts.app', ['title' => 'Pesanan Penjualan ' . $salesOrder->so_number])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('sales.orders.index') }}" class="p-2 rounded-xl bg-slate-800 text-slate-400 hover:text-white transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400 font-mono">Pesanan Penjualan</span>
                    @if($salesOrder->status === 'fulfilled')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">FULFILLED</span>
                    @elseif($salesOrder->status === 'partially_fulfilled')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-500/20 text-amber-400 border border-amber-500/30">SEBAGIAN</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-cyan-500/20 text-cyan-400 border border-cyan-500/30">CONFIRMED</span>
                    @endif
                </div>
                <h1 class="text-2xl font-black text-white font-mono">{{ $salesOrder->so_number }}</h1>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- 1-Click Generate Invoice Button -->
            @if($salesOrder->status !== 'fulfilled')
            <form action="{{ route('sales.orders.generate-invoice', $salesOrder) }}" method="POST" onsubmit="return confirm('Terbitkan Faktur Tagihan (Invoice) resmi dari pesanan penjualan ini?')">
                @csrf
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-black text-xs shadow-lg shadow-cyan-500/20 transition flex items-center gap-2">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                    <span>Terbitkan Faktur Tagihan (Invoice)</span>
                </button>
            </form>
            @endif

            <!-- Print -->
            <button onclick="window.print()" class="px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition flex items-center gap-1.5">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak</span>
            </button>
        </div>
    </div>

    <!-- Linked Quotation / Invoices Notification -->
    @if($salesOrder->quotation || $salesOrder->invoices->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @if($salesOrder->quotation)
        <div class="glass-card rounded-2xl p-3.5 border border-slate-800 flex items-center justify-between text-xs">
            <div>
                <span class="text-[10px] text-slate-500 font-bold uppercase">Berasal dari Penawaran:</span>
                <p class="font-mono font-bold text-emerald-400">{{ $salesOrder->quotation->quotation_number }}</p>
            </div>
            <a href="{{ route('sales.quotations.show', $salesOrder->quotation) }}" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 transition">
                Lihat Penawaran
            </a>
        </div>
        @endif

        @if($salesOrder->invoices->count() > 0)
        <div class="glass-card rounded-2xl p-3.5 border border-slate-800 flex items-center justify-between text-xs">
            <div>
                <span class="text-[10px] text-slate-500 font-bold uppercase">Faktur Tagihan Terbit:</span>
                <p class="font-mono font-bold text-white">{{ $salesOrder->invoices->first()->invoice_number }}</p>
            </div>
            <a href="{{ route('invoices.show', $salesOrder->invoices->first()) }}" class="px-2.5 py-1 rounded-lg bg-cyan-500/20 text-cyan-400 hover:bg-cyan-500/30 transition">
                Buka Faktur
            </a>
        </div>
        @endif
    </div>
    @endif

    <!-- Printable Sales Order Card -->
    <div class="glass-card rounded-3xl p-8 border border-slate-800 bg-slate-950/80 space-y-8 print:p-0 print:border-none">
        <div class="flex flex-col sm:flex-row justify-between items-start gap-6 border-b border-slate-800/80 pb-6">
            <div>
                <span class="text-xl font-black text-white tracking-tight">{{ $business->name }}</span>
                <p class="text-xs text-slate-400 mt-1">{{ $business->address ?? 'Operasional Bisnis' }}</p>
                <p class="text-xs text-slate-400">{{ $business->phone ?? '' }}</p>
            </div>

            <div class="text-right">
                <h2 class="text-2xl font-black text-cyan-400 uppercase tracking-wider">PESANAN PENJUALAN</h2>
                <p class="text-xs font-mono text-slate-400 mt-1">{{ $salesOrder->so_number }}</p>
                <div class="mt-2 text-xs space-y-0.5 text-slate-400">
                    <p>Tanggal Pesanan: <span class="text-white font-mono">{{ $salesOrder->order_date->format('d M Y') }}</span></p>
                    @if($salesOrder->expected_delivery_date)
                    <p>Estimasi Kirim: <span class="text-white font-mono">{{ $salesOrder->expected_delivery_date->format('d M Y') }}</span></p>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-slate-900/50 rounded-2xl p-4 border border-slate-800/60">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-500">PELANGGAN:</span>
                <h3 class="text-sm font-black text-white mt-1">{{ $salesOrder->customer->name }}</h3>
                <p class="text-xs text-slate-400 mt-0.5">{{ $salesOrder->customer->phone }}</p>
            </div>
            @if($salesOrder->shipping_address)
            <div class="bg-slate-900/50 rounded-2xl p-4 border border-slate-800/60">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-500">ALAMAT PENGIRIMAN:</span>
                <p class="text-xs text-slate-300 mt-1">{{ $salesOrder->shipping_address }}</p>
            </div>
            @endif
        </div>

        <!-- Items Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-slate-500 uppercase text-[10px] font-bold border-b border-slate-800">
                    <tr>
                        <th class="py-2.5 px-3">No.</th>
                        <th class="py-2.5 px-3">Deskripsi Produk</th>
                        <th class="py-2.5 px-3 text-right">Harga Satuan</th>
                        <th class="py-2.5 px-3 text-right">Kuantitas</th>
                        <th class="py-2.5 px-3 text-right">Terpenuhi</th>
                        <th class="py-2.5 px-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-mono">
                    @foreach($salesOrder->items as $idx => $item)
                    <tr>
                        <td class="py-3 px-3 text-slate-500">{{ $idx + 1 }}</td>
                        <td class="py-3 px-3 font-sans font-medium text-white">
                            {{ $item->product_name }}
                        </td>
                        <td class="py-3 px-3 text-right text-slate-400">
                            Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-3 text-right text-white font-bold">
                            {{ (float) $item->quantity }}
                        </td>
                        <td class="py-3 px-3 text-right text-cyan-400 font-bold">
                            {{ (float) $item->fulfilled_quantity }}
                        </td>
                        <td class="py-3 px-3 text-right font-bold text-white">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Total -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-6 pt-4 border-t border-slate-800">
            <div class="text-xs text-slate-500">
                @if($salesOrder->notes)
                <p class="text-slate-400 font-bold">Catatan:</p>
                <p>{{ $salesOrder->notes }}</p>
                @endif
            </div>

            <div class="w-full sm:w-72 space-y-2 text-xs">
                <div class="flex justify-between text-slate-400">
                    <span>Subtotal:</span>
                    <span class="font-mono font-bold text-white">Rp {{ number_format($salesOrder->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($salesOrder->discount_amount > 0)
                <div class="flex justify-between text-rose-400">
                    <span>Diskon:</span>
                    <span class="font-mono font-bold">- Rp {{ number_format($salesOrder->discount_amount, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="pt-2 border-t border-slate-800 flex justify-between text-base font-black text-white">
                    <span>Total Pesanan:</span>
                    <span class="font-mono text-cyan-400">Rp {{ number_format($salesOrder->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
