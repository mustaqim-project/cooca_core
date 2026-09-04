@extends('layouts.app', ['title' => 'Buat Retur Pembelian'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('purchase.returns.index') }}" class="hover:text-white transition">Retur Pembelian</a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-slate-600"></i>
                <span class="text-rose-400 font-semibold">Form Retur Baru</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Formulir Retur Pembelian</h1>
            <p class="text-xs text-slate-400 mt-1">Kembalikan fisik barang ke vendor dari Goods Receipt yang telah diterima.</p>
        </div>
        <a href="{{ route('purchase.returns.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-300 text-xs font-semibold transition">
            ← Batal & Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs space-y-1">
            @foreach($errors->all() as $error)
                <div class="flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-400"></i>
                    <span>{{ $error }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('purchase.returns.store') }}" class="glass-card rounded-2xl border border-slate-800 p-6 space-y-5">
        @csrf

        <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Pilih Penerimaan Barang (Goods Receipt) *</label>
            <select name="goods_receipt_id" required class="w-full bg-slate-900 border border-slate-700 focus:border-rose-500 rounded-xl px-4 py-3 text-white text-xs">
                <option value="">-- Pilih Dokumen Goods Receipt --</option>
                @foreach($receipts as $receipt)
                    <option value="{{ $receipt->id }}">
                        {{ $receipt->receipt_number }} — {{ $receipt->supplier?->name ?? 'Supplier' }} ({{ $receipt->received_date?->format('d/m/Y') }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="p-4 rounded-xl bg-slate-950/80 border border-slate-800/80 space-y-3">
            <div class="text-xs font-bold text-slate-300">Detail Item yang Dikembalikan</div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">ID Item Goods Receipt *</label>
                    <input name="items[0][goods_receipt_item_id]" placeholder="Masukkan ID baris item GR" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white text-xs font-mono">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Kuantitas Dikembalikan (Qty) *</label>
                    <input name="items[0][quantity]" type="number" min="0.01" step="any" placeholder="Contoh: 5" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white text-xs font-mono">
                </div>
            </div>
        </div>

        <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Alasan Pengembalian / Catatan Klaim *</label>
            <input name="reason" placeholder="Contoh: Kemasan bocor saat pengiriman, produk kedaluwarsa, dsb." required class="w-full bg-slate-900 border border-slate-700 focus:border-rose-500 rounded-xl px-4 py-3 text-white text-xs">
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
            <a href="{{ route('purchase.returns.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 text-xs font-semibold transition">
                Batal
            </a>
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-rose-500 to-amber-500 hover:from-rose-400 hover:to-amber-400 text-slate-950 font-bold text-xs shadow-lg shadow-rose-500/20 transition flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span>Simpan Draft Retur</span>
            </button>
        </div>
    </form>
</div>
@endsection
