@extends('layouts.app', ['title' => 'Penawaran ' . $quotation->quotation_number])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('sales.quotations.index') }}" class="p-2 rounded-xl bg-slate-800 text-slate-400 hover:text-white transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400 font-mono">Surat Penawaran Resmi</span>
                    @if($quotation->status === 'accepted')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">DISETUJUI</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-cyan-500/20 text-cyan-400 border border-cyan-500/30">{{ strtoupper($quotation->status) }}</span>
                    @endif
                </div>
                <h1 class="text-2xl font-black text-white font-mono">{{ $quotation->quotation_number }}</h1>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- 1-Click Convert to Sales Order Button -->
            @if(!$quotation->salesOrder && $quotation->status !== 'rejected')
            <form action="{{ route('sales.quotations.convert', $quotation) }}" method="POST" onsubmit="return AppAlert.confirmSubmit(event, this, 'Konversi surat penawaran ini menjadi Pesanan Penjualan (Sales Order)?', 'Ubah Menjadi Sales Order?', 'info')">
                @csrf
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/20 transition flex items-center gap-2">
                    <i data-lucide="arrow-right-circle" class="w-4 h-4"></i>
                    <span>Ubah Menjadi Sales Order</span>
                </button>
            </form>
            @elseif($quotation->salesOrder)
            <a href="{{ route('sales.orders.show', $quotation->salesOrder) }}" class="px-4 py-2.5 rounded-xl bg-cyan-500/20 border border-cyan-500/30 text-cyan-400 font-bold text-xs transition flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                <span>Lihat Sales Order ({{ $quotation->salesOrder->so_number }})</span>
            </a>
            @endif

            <!-- Print / Cetak -->
            <button onclick="window.print()" class="px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition flex items-center gap-1.5">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak</span>
            </button>

            <!-- WhatsApp Share -->
            @php
                $waText = urlencode("Halo {$quotation->customer->name}, berikut kami kirimkan Surat Penawaran Resmi {$quotation->quotation_number} sebesar Rp " . number_format($quotation->total_amount, 0, ',', '.') . ". Terima kasih.");
            @endphp
            @if($quotation->customer->phone)
            <a href="https://api.whatsapp.com/send?phone={{ preg_replace('/[^0-9]/', '', $quotation->customer->phone) }}&text={{ $waText }}" target="_blank" class="px-3.5 py-2.5 rounded-xl bg-emerald-600/20 hover:bg-emerald-600/30 border border-emerald-500/30 text-emerald-400 text-xs font-bold transition flex items-center gap-1.5">
                <i data-lucide="message-square" class="w-4 h-4"></i>
                <span>Kirim WhatsApp</span>
            </a>
            @endif
        </div>
    </div>

    <!-- Printable Quotation Card -->
    <div class="glass-card rounded-3xl p-8 border border-slate-800 bg-slate-950/80 space-y-8 print:p-0 print:border-none">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-6 border-b border-slate-800/80 pb-6">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xl font-black text-white tracking-tight">{{ $business->name }}</span>
                </div>
                <p class="text-xs text-slate-400 mt-1">{{ $business->address ?? 'Operasional Bisnis' }}</p>
                <p class="text-xs text-slate-400">{{ $business->phone ?? '' }} • {{ $business->email ?? '' }}</p>
            </div>

            <div class="text-right">
                <h2 class="text-2xl font-black text-emerald-400 uppercase tracking-wider">PENAWARAN HARGA</h2>
                <p class="text-xs font-mono text-slate-400 mt-1">{{ $quotation->quotation_number }}</p>
                <div class="mt-2 text-xs space-y-0.5 text-slate-400">
                    <p>Tanggal: <span class="text-white font-mono">{{ $quotation->date->format('d M Y') }}</span></p>
                    @if($quotation->expiry_date)
                    <p>Berlaku Sampai: <span class="text-white font-mono">{{ $quotation->expiry_date->format('d M Y') }}</span></p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Kepada / Customer Details -->
        <div class="bg-slate-900/50 rounded-2xl p-4 border border-slate-800/60 max-w-sm">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-500">DITUJUKAN KEPADA:</span>
            <h3 class="text-sm font-black text-white mt-1">{{ $quotation->customer->name }}</h3>
            @if($quotation->customer->company)
            <p class="text-xs text-slate-400">{{ $quotation->customer->company }}</p>
            @endif
            @if($quotation->customer->address)
            <p class="text-xs text-slate-400 mt-1">{{ $quotation->customer->address }}</p>
            @endif
            <p class="text-xs text-slate-400 mt-0.5">{{ $quotation->customer->phone }}</p>
        </div>

        <!-- Table of Items -->
        <div class="table-responsive-wide">
            <table class="w-full text-left text-xs text-slate-300 min-w-[580px]">
                <thead class="text-slate-500 uppercase text-[10px] font-bold border-b border-slate-800 whitespace-nowrap">
                    <tr>
                        <th class="py-2.5 px-3">No.</th>
                        <th class="py-2.5 px-3">Deskripsi Item</th>
                        <th class="py-2.5 px-3 text-right">Harga Satuan</th>
                        <th class="py-2.5 px-3 text-right">Jumlah</th>
                        <th class="py-2.5 px-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-mono">
                    @foreach($quotation->items as $idx => $item)
                    <tr>
                        <td class="py-3 px-3 text-slate-500">{{ $idx + 1 }}</td>
                        <td class="py-3 px-3 font-sans font-medium text-white">
                            {{ $item->product_name }}
                            @if($item->notes)
                            <p class="text-[11px] text-slate-500">{{ $item->notes }}</p>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-right text-slate-400">
                            Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-3 text-right text-white font-bold">
                            {{ (float) $item->quantity }}
                        </td>
                        <td class="py-3 px-3 text-right font-bold text-white">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Financial Calculation -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-6 pt-4 border-t border-slate-800">
            <div class="space-y-2 text-xs text-slate-400 max-w-sm">
                @if($quotation->notes)
                <p class="font-bold text-white">Catatan Tambahan:</p>
                <p class="whitespace-pre-line">{{ $quotation->notes }}</p>
                @endif
                <p class="text-[11px] text-slate-500 mt-2">Diterbitkan secara digital oleh Cooca UMKM (cooca.id).</p>
            </div>

            <div class="w-full sm:w-72 space-y-2 text-xs">
                <div class="flex justify-between text-slate-400">
                    <span>Subtotal:</span>
                    <span class="font-mono font-bold text-white">Rp {{ number_format($quotation->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($quotation->discount_amount > 0)
                <div class="flex justify-between text-rose-400">
                    <span>Diskon:</span>
                    <span class="font-mono font-bold">- Rp {{ number_format($quotation->discount_amount, 0, ',', '.') }}</span>
                </div>
                @endif
                @if($quotation->tax_amount > 0)
                <div class="flex justify-between text-slate-400">
                    <span>Pajak (PPN):</span>
                    <span class="font-mono font-bold text-white">Rp {{ number_format($quotation->tax_amount, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="pt-2 border-t border-slate-800 flex justify-between text-base font-black text-white">
                    <span>Total Penawaran:</span>
                    <span class="font-mono text-emerald-400">Rp {{ number_format($quotation->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
