@extends('layouts.app', ['title' => 'Detail Retur Pembelian'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('purchase.returns.index') }}" class="hover:text-white transition">Retur Pembelian</a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-slate-600"></i>
                <span class="text-rose-400 font-semibold">{{ $return->return_number }}</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">{{ $return->return_number }}</h1>
            <p class="text-xs text-slate-400 mt-1">
                Ref. GR: <span class="text-white font-mono font-bold">{{ $return->goodsReceipt->receipt_number ?? '-' }}</span> &bull; 
                Supplier: <span class="text-white font-bold">{{ $return->supplier->name ?? '-' }}</span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('purchase.returns.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-300 text-xs font-semibold transition">
                ← Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Status & Action Card -->
    <div class="glass-card rounded-2xl border border-slate-800 p-5 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800/80 pb-4">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status Dokumen</div>
                <div class="mt-1">
                    @if($return->status === 'completed')
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Selesai (Stok Telah Disesuaikan)</span>
                    @elseif($return->status === 'approved')
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">Disetujui (Menunggu Pengiriman Fisik)</span>
                    @else
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">Draft (Belum Disetujui)</span>
                    @endif
                </div>
            </div>

            <div class="text-right">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Nilai Retur</div>
                <div class="text-xl font-black text-rose-400 font-mono mt-0.5">
                    Rp {{ number_format($return->total_amount, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <div class="text-xs text-slate-300">
            <span class="text-slate-400 font-semibold">Alasan Retur:</span>
            <span class="ml-1 text-slate-200">{{ $return->reason }}</span>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-3 pt-2">
            @if($return->status === 'draft')
            <form method="POST" action="{{ route('purchase.returns.approve', $return) }}">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold shadow-lg shadow-amber-500/20 transition flex items-center gap-1.5">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Setujui Retur</span>
                </button>
            </form>
            @endif

            @if($return->status === 'approved')
            <form method="POST" action="{{ route('purchase.returns.complete', $return) }}">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-bold shadow-lg shadow-emerald-500/20 transition flex items-center gap-1.5">
                    <i data-lucide="check-check" class="w-4 h-4"></i>
                    <span>Selesaikan & Potong Stok Gudang</span>
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Items Table Card -->
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden shadow-xl">
        <div class="p-4 bg-slate-900/90 border-b border-slate-800 text-xs font-bold text-white">
            Daftar Barang yang Diretur
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300 font-mono">
                <thead class="bg-slate-950/60 text-slate-500 uppercase text-[10px] font-bold border-b border-slate-800/40">
                    <tr>
                        <th class="py-3 px-4 font-sans">Nama Produk / Bahan</th>
                        <th class="py-3 px-4 text-center">Kuantitas</th>
                        <th class="py-3 px-4 text-right">Harga Satuan (Rp)</th>
                        <th class="py-3 px-4 text-right">Subtotal (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50">
                    @foreach($return->items as $item)
                    <tr class="hover:bg-slate-800/30">
                        <td class="py-3 px-4 font-sans font-medium text-white">
                            {{ $item->item_name }}
                        </td>
                        <td class="py-3 px-4 text-center font-bold text-amber-300">
                            {{ $item->quantity }}
                        </td>
                        <td class="py-3 px-4 text-right text-slate-400">
                            {{ number_format($item->unit_price ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-black text-white">
                            {{ number_format($item->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
