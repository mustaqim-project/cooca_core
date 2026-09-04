@extends('layouts.app', ['title' => 'Penerimaan Barang PO ' . $purchaseOrder->po_number])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                    GOODS RECEIPT (PENGADAAN)
                </span>
                <span class="text-xs text-slate-400 font-mono">PO: {{ $purchaseOrder->po_number }}</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight mt-1">Penerimaan Barang Fisik Gudang</h1>
            <p class="text-xs text-slate-400 mt-0.5">Konfirmasi kedatangan barang dari pemasok untuk menambah saldo stok gudang secara otomatis.</p>
        </div>
        <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="text-xs text-slate-400 hover:text-white transition flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke PO</span>
        </a>
    </div>

    <!-- Form -->
    <form action="{{ route('purchasing.receipts.store', $purchaseOrder) }}" method="POST" class="space-y-6">
        @csrf

        <div class="glass-card rounded-2xl p-6 border border-slate-800 space-y-4">
            <h3 class="text-sm font-black text-white uppercase tracking-wider text-emerald-400">1. Data Penerimaan & Lokasi Gudang</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Gudang Tujuan Masuk <span class="text-rose-400">*</span></label>
                    <select name="location_id" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:border-emerald-500">
                        @foreach($locations as $loc)
                        <option value="{{ $loc->id }}">{{ $loc->name }} ({{ $loc->type }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">No. Penerimaan (GR Number)</label>
                    <input type="text" name="receipt_number" value="{{ $nextReceiptNumber }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-xs font-mono text-emerald-400 focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Tanggal Terima Fisik</label>
                    <input type="date" name="receipt_date" value="{{ date('Y-m-d') }}" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <div class="pt-2">
                <label class="block text-xs font-bold text-slate-400 mb-1">Catatan Kondisi Barang / Ekspedisi</label>
                <textarea name="notes" rows="2" placeholder="Contoh: Diterima dalam kondisi baik dan tersegel rapi..." class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-emerald-500"></textarea>
            </div>
        </div>

        <!-- Table of Items from PO -->
        <div class="glass-card rounded-2xl p-6 border border-slate-800 space-y-4">
            <h3 class="text-sm font-black text-white uppercase tracking-wider text-emerald-400">2. Verifikasi Kuantitas Barang Diterima</h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="text-slate-500 uppercase text-[10px] font-bold border-b border-slate-800">
                        <tr>
                            <th class="py-2.5 px-3">Produk / Bahan</th>
                            <th class="py-2.5 px-3 text-right">Dipesan di PO</th>
                            <th class="py-2.5 px-3 text-right w-36">Kuantitas Masuk Fisik</th>
                            <th class="py-2.5 px-3 text-right w-32">Harga Beli Satuan</th>
                            <th class="py-2.5 px-3">No. Batch (Opsional)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-mono">
                        @foreach($purchaseOrder->items as $idx => $item)
                        <tr>
                            <td class="py-3 px-3 font-sans font-medium text-white">
                                {{ $item->product ? $item->product->name : ($item->material ? $item->material->name : $item->item_name) }}
                                <input type="hidden" name="items[{{ $idx }}][product_id]" value="{{ $item->product_id ?? '' }}">
                                <input type="hidden" name="items[{{ $idx }}][material_id]" value="{{ $item->material_id ?? '' }}">
                                <input type="hidden" name="items[{{ $idx }}][item_name]" value="{{ $item->item_name ?? ($item->product ? $item->product->name : ($item->material ? $item->material->name : '')) }}">
                            </td>
                            <td class="py-3 px-3 text-right text-slate-400">
                                {{ (float) $item->quantity }}
                            </td>
                            <td class="py-3 px-3 text-right">
                                <input type="number" name="items[{{ $idx }}][quantity]" value="{{ (float) $item->quantity }}" min="0" step="any" required class="w-full bg-slate-900 border border-emerald-500/50 rounded-lg px-2 py-1.5 text-xs text-right font-bold text-emerald-400 focus:outline-none focus:border-emerald-500">
                            </td>
                            <td class="py-3 px-3 text-right">
                                <input type="number" name="items[{{ $idx }}][unit_cost]" value="{{ (float) $item->unit_cost }}" min="0" step="any" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2 py-1.5 text-xs text-right text-white focus:outline-none focus:border-emerald-500">
                            </td>
                            <td class="py-3 px-3">
                                <input type="text" name="items[{{ $idx }}][batch_number]" placeholder="BCH-..." class="w-full bg-slate-900 border border-slate-700/60 rounded-lg px-2 py-1.5 text-xs text-white focus:outline-none focus:border-emerald-500">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition">Batal</a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-black shadow-lg shadow-emerald-500/20 transition flex items-center gap-2">
                <i data-lucide="package-check" class="w-4 h-4"></i>
                <span>Simpan Penerimaan & Tambah Stok</span>
            </button>
        </div>
    </form>
</div>
@endsection
