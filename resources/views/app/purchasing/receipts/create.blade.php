@extends('layouts.app', [
    'title' => 'Penerimaan Barang PO ' . $purchaseOrder->po_number,
    'headerTitle' => 'Penerimaan Barang Fisik',
    'headerSubtitle' => 'Konfirmasi kedatangan barang fisik dan pembaruan stok gudang'
])

@section('content')
<div class="max-w-[1080px] mx-auto space-y-6 pb-12">
    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / HEADER (macOS Sonoma Style)              -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <!-- Breadcrumb minimal -->
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <a href="{{ route('purchase-orders.index') }}" class="hover:text-[#007AFF] transition-colors">Pesanan Pembelian</a>
                <span>›</span>
                <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="hover:text-[#007AFF] transition-colors">{{ $purchaseOrder->po_number }}</a>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Penerimaan Barang</span>
            </nav>
            <div class="flex items-center gap-2">
                <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Penerimaan Barang Fisik (Goods Receipt)</h1>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF]">
                    PO: {{ $purchaseOrder->po_number }}
                </span>
            </div>
            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Konfirmasi kedatangan barang dari pemasok untuk menambah saldo stok gudang secara otomatis.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Kembali ke PO</span>
            </a>
        </div>
    </header>

    @if($errors->any())
    <div class="rounded-[12px] bg-[#FF3B30]/12 border border-[#FF3B30]/20 p-4 text-[13px] text-[#C41E17] dark:text-[#FF453A] space-y-1">
        @foreach($errors->all() as $error)
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <span>{{ $error }}</span>
            </div>
        @endforeach
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 2. MAIN RECEIPT FORM                                  -->
    <!-- ===================================================== -->
    <form action="{{ route('purchasing.receipts.store', $purchaseOrder) }}" method="POST" class="space-y-6">
        @csrf

        <!-- Section 1: Lokasi & Data Header -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 space-y-4">
            <h3 class="text-[14px] font-semibold text-black dark:text-white">1. Data Penerimaan &amp; Lokasi Gudang</h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">Gudang Tujuan Masuk <span class="text-[#FF3B30]">*</span></label>
                    <select name="location_id" required class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        @foreach($locations as $loc)
                        <option value="{{ $loc->id }}">{{ $loc->name }} ({{ $loc->type }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">No. Penerimaan (GR Number)</label>
                    <input type="text" name="receipt_number" value="{{ $nextReceiptNumber }}"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] font-medium tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">Tanggal Terima Fisik <span class="text-[#FF3B30]">*</span></label>
                    <input type="date" name="receipt_date" value="{{ date('Y-m-d') }}" required
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>
            </div>

            <div>
                <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">Catatan Kondisi Barang / Ekspedisi</label>
                <textarea name="notes" rows="2" placeholder="Contoh: Diterima dalam kondisi baik dan kemasan tersegel rapi..."
                          class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] p-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition"></textarea>
            </div>
        </div>

        <!-- Section 2: Verifikasi Kuantitas Barang -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <div>
                    <h3 class="text-[14px] font-semibold text-black dark:text-white">2. Verifikasi Kuantitas Barang Diterima</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Sesuaikan kuantitas fisik yang benar-benar sampai di gudang.</p>
                </div>
                <span class="text-[12px] text-black/50 dark:text-white/50 tabular-nums">{{ count($purchaseOrder->items) }} Item Baris</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10">
                            <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Produk / Bahan</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Dipesan di PO</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right w-40">Kuantitas Masuk Fisik</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right w-36">Harga Beli Satuan</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 w-44">No. Batch (Opsional)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @foreach($purchaseOrder->items as $idx => $item)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                            <td class="px-4 py-3 font-medium text-black dark:text-white">
                                {{ $item->product ? $item->product->name : ($item->material ? $item->material->name : $item->item_name) }}
                                <input type="hidden" name="items[{{ $idx }}][product_id]" value="{{ $item->product_id ?? '' }}">
                                <input type="hidden" name="items[{{ $idx }}][material_id]" value="{{ $item->material_id ?? '' }}">
                                <input type="hidden" name="items[{{ $idx }}][item_name]" value="{{ $item->item_name ?? ($item->product ? $item->product->name : ($item->material ? $item->material->name : '')) }}">
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums text-black/60 dark:text-white/60">
                                {{ (float) $item->quantity }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <input type="number" name="items[{{ $idx }}][quantity]" value="{{ (float) $item->quantity }}" min="0" step="any" required
                                       class="w-full h-8 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[13px] text-right font-semibold tabular-nums text-[#007AFF] dark:text-[#0A84FF] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </td>
                            <td class="px-4 py-3 text-right">
                                <input type="number" name="items[{{ $idx }}][unit_cost]" value="{{ (float) $item->unit_cost }}" min="0" step="any" required
                                       class="w-full h-8 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[13px] text-right tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </td>
                            <td class="px-4 py-3">
                                <input type="text" name="items[{{ $idx }}][batch_number]" placeholder="BCH-..."
                                       class="w-full h-8 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.97] active:opacity-80 transition-all flex items-center">
                Batal
            </a>
            @if(\App\Support\Context::hasPermission('receiving.manage') || \App\Support\Context::hasPermission('purchasing.manage'))
            <button type="submit" class="h-9 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Simpan Penerimaan &amp; Tambah Stok</span>
            </button>
            @endif
        </div>
    </form>
</div>
@endsection
