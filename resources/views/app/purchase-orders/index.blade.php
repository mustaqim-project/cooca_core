@extends('layouts.app', [
    'title' => 'Purchase Orders (PO)',
    'headerTitle' => 'Manajemen Purchase Order (PO)',
    'headerSubtitle' => 'Kelola pesanan masuk dari pelanggan dan order pengadaan bahan baku ke pemasok'
])

@section('content')
<div class="space-y-6">

    <!-- KPI Summary Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="glass-card p-4 rounded-2xl border border-slate-800 flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center shrink-0">
                <i data-lucide="file-text" class="w-5 h-5 text-blue-400"></i>
            </div>
            <div>
                <div class="text-[11px] text-slate-400 font-medium">Total Pesanan</div>
                <div class="text-lg font-extrabold text-white font-mono">{{ $totalOrders }} PO</div>
            </div>
        </div>

        <div class="glass-card p-4 rounded-2xl border border-slate-800 flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center shrink-0">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-400"></i>
            </div>
            <div>
                <div class="text-[11px] text-slate-400 font-medium">Dikonfirmasi (Siap Faktur)</div>
                <div class="text-lg font-extrabold text-emerald-400 font-mono">{{ $totalConfirmed }} PO</div>
            </div>
        </div>

        <div class="glass-card p-4 rounded-2xl border border-slate-800 flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center shrink-0">
                <i data-lucide="receipt" class="w-5 h-5 text-purple-400"></i>
            </div>
            <div>
                <div class="text-[11px] text-slate-400 font-medium">Sudah Jadi Faktur</div>
                <div class="text-lg font-extrabold text-purple-400 font-mono">{{ $totalInvoiced }} PO</div>
            </div>
        </div>

        <div class="glass-card p-4 rounded-2xl border border-slate-800 flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-teal-500/10 border border-teal-500/20 flex items-center justify-center shrink-0">
                <i data-lucide="dollar-sign" class="w-5 h-5 text-teal-400"></i>
            </div>
            <div>
                <div class="text-[11px] text-slate-400 font-medium">Nilai Total Pesanan</div>
                <div class="text-lg font-extrabold text-teal-400 font-mono">{{ $business->currency_symbol }} {{ number_format($totalSum, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <!-- Search & Filters -->
        <form method="GET" action="{{ route('purchase-orders.index') }}" class="flex-1 flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[220px] max-w-md">
                <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari no. PO, nama klien / vendor..." 
                       class="w-full pl-10 pr-4 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-xs text-white">
            </div>

            <select name="po_type" onchange="this.form.submit()"
                    class="px-3 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-xs text-white">
                <option value="">Semua Tipe PO</option>
                <option value="customer" {{ request('po_type') === 'customer' ? 'selected' : '' }}>PO Pelanggan (Penjualan)</option>
                <option value="supplier" {{ request('po_type') === 'supplier' ? 'selected' : '' }}>PO Supplier (Pengadaan)</option>
            </select>

            <select name="status" onchange="this.form.submit()"
                    class="px-3 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-xs text-white">
                <option value="">Semua Status</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                <option value="fully_invoiced" {{ request('status') === 'fully_invoiced' ? 'selected' : '' }}>Invoiced</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </form>

        <div class="flex items-center gap-2 shrink-0">
            <button type="button" onclick="document.getElementById('instantStockInModal').classList.remove('hidden')"
                    class="px-3.5 py-2 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 border border-amber-500/30 text-amber-400 text-xs font-bold flex items-center gap-1.5 transition-all">
                <i data-lucide="zap" class="w-4 h-4"></i>
                <span>1-Klik Beli ke Stok</span>
            </button>

            <a href="{{ route('purchase-orders.create') }}" 
               class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2 transition-all shrink-0">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Buat PO Baru</span>
            </a>
        </div>
    </div>

    <!-- Purchase Orders Table Card -->
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-800 bg-slate-900/50">
                        <th class="py-3.5 px-4 font-semibold">Nomor PO & Tanggal</th>
                        <th class="py-3.5 px-4 font-semibold">Tipe</th>
                        <th class="py-3.5 px-4 font-semibold">Pihak Terkait (Klien / Vendor)</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Status</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Jumlah Item</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Nilai Total</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($purchaseOrders as $po)
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="py-3 px-4">
                            <a href="{{ route('purchase-orders.show', $po->id) }}" class="font-bold text-white text-sm hover:text-emerald-400 font-mono transition-colors">
                                {{ $po->po_number }}
                            </a>
                            <div class="text-[11px] text-slate-400 mt-0.5">{{ $po->order_date?->translatedFormat('d M Y') }}</div>
                        </td>
                        <td class="py-3 px-4">
                            @if($po->po_type === 'customer')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    Pesanan Klien
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                    Pengadaan Vendor
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @if($po->customer)
                                <div class="font-semibold text-white">{{ $po->customer->name }}</div>
                                @if($po->customer->company_name)
                                    <div class="text-[11px] text-slate-400">{{ $po->customer->company_name }}</div>
                                @endif
                            @elseif($po->supplier)
                                <div class="font-semibold text-white">{{ $po->supplier->name }}</div>
                                <div class="text-[11px] text-slate-400">{{ $po->supplier->contact_person }}</div>
                            @else
                                <span class="text-slate-500">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            @php
                                $badges = [
                                    'draft' => 'bg-slate-800 text-slate-300 border-slate-700',
                                    'confirmed' => 'bg-blue-500/15 text-blue-400 border-blue-500/30',
                                    'partially_invoiced' => 'bg-amber-500/15 text-amber-400 border-amber-500/30',
                                    'fully_invoiced' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
                                    'completed' => 'bg-teal-500/15 text-teal-400 border-teal-500/30',
                                    'cancelled' => 'bg-rose-500/15 text-rose-400 border-rose-500/30',
                                ];
                                $badgeClass = $badges[$po->status] ?? 'bg-slate-800 text-slate-300 border-slate-700';
                            @endphp
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $badgeClass }}">
                                {{ str_replace('_', ' ', $po->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center font-mono text-slate-300">
                            {{ $po->items->count() }} item
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-white text-sm">
                            {{ $business->currency_symbol }} {{ number_format((float) $po->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('purchase-orders.show', $po->id) }}" class="p-1.5 hover:bg-slate-800 rounded-lg text-slate-400 hover:text-white transition-colors" title="Lihat Detail">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>

                                <a href="{{ route('purchase-orders.print', $po->id) }}?download=1" target="_blank" class="p-1.5 hover:bg-emerald-500/20 rounded-lg text-slate-400 hover:text-emerald-400 transition-colors" title="Download PDF Langsung">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                </a>

                                <a href="{{ route('purchase-orders.print', $po->id) }}" target="_blank" class="p-1.5 hover:bg-slate-800 rounded-lg text-slate-400 hover:text-white transition-colors" title="Cetak / Pratinjau PO">
                                    <i data-lucide="printer" class="w-4 h-4"></i>
                                </a>

                                @if($po->po_type === 'customer' && $po->status !== 'fully_invoiced' && $po->status !== 'cancelled')
                                <form method="POST" action="{{ route('purchase-orders.generate-invoice', $po->id) }}">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-400 font-semibold text-[11px] flex items-center gap-1 transition-colors" title="Generate Faktur Langsung">
                                        <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                                        <span>Faktur</span>
                                    </button>
                                </form>
                                @endif

                                @if($po->po_type === 'supplier' && $po->status === 'confirmed')
                                <a href="{{ route('purchasing.receipts.create', $po->id) }}" class="px-2.5 py-1 rounded-lg bg-amber-500/15 hover:bg-amber-500/25 text-amber-400 font-semibold text-[11px] flex items-center gap-1 transition-colors" title="Terima Barang Masuk ke Gudang">
                                    <i data-lucide="package-check" class="w-3.5 h-3.5"></i>
                                    <span>Terima Gudang</span>
                                </a>
                                @endif

                                @if($po->status === 'draft')
                                <form method="POST" action="{{ route('purchase-orders.destroy', $po->id) }}" onsubmit="return AppAlert.confirmSubmit(event, this, 'Hapus draf PO {{ addslashes($po->po_number) }}?', 'Hapus Draf PO?', 'danger')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 hover:bg-rose-500/20 rounded-lg text-slate-400 hover:text-rose-400 transition-colors" title="Hapus Draft">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-500">
                            <i data-lucide="file-check" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                            <p class="text-sm font-medium">Belum ada dokumen Purchase Order.</p>
                            <p class="text-xs text-slate-400 mt-1">Buat PO pelanggan untuk memudahkan pembuatan faktur dan pengiriman barang.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($purchaseOrders->hasPages())
        <div class="p-4 border-t border-slate-800 bg-slate-900/40">
            {{ $purchaseOrders->links() }}
        </div>
        @endif
    </div>

    <!-- Solo-Owner 1-Click Instant Stock-In Modal -->
    <div id="instantStockInModal" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="glass-card rounded-3xl p-6 border border-slate-700 bg-slate-900 w-full max-w-lg space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <div class="p-2 rounded-xl bg-amber-500/20 text-amber-400">
                        <i data-lucide="zap" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-white">1-Klik Beli Langsung ke Stok</h3>
                        <p class="text-[11px] text-slate-400">Beli bahan/produk di pasar & langsung tambah stok seketika tanpa alur PO bertingkat.</p>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('instantStockInModal').classList.add('hidden')" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('purchasing.instant-stock-in') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Gudang Tujuan Masuk <span class="text-rose-400">*</span></label>
                    <select name="location_id" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-amber-500">
                        @foreach($locations as $loc)
                        <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Pilih Produk / Bahan Masuk <span class="text-rose-400">*</span></label>
                    <select name="product_id" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-amber-500">
                        <option value="">-- Pilih Produk --</option>
                        @foreach($products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Jumlah Masuk <span class="text-rose-400">*</span></label>
                        <input type="number" name="quantity" min="0.01" step="any" required placeholder="Contoh: 10" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-amber-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Harga Beli Satuan (Rp) <span class="text-rose-400">*</span></label>
                        <input type="number" name="unit_cost" min="0" step="any" required placeholder="Contoh: 25000" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-amber-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Pemasok / Toko Pembelian (Opsional)</label>
                    <select name="supplier_id" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-amber-500">
                        <option value="">-- Tanpa Pemasok Tertentu (Belanja Pasar) --</option>
                        @foreach($suppliers as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Catatan</label>
                    <input type="text" name="notes" placeholder="Contoh: Nota Toko Sumber Rejeki" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-amber-500">
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-800">
                    <button type="button" onclick="document.getElementById('instantStockInModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black shadow-lg shadow-amber-500/20 transition flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Beli & Tambah ke Stok</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
