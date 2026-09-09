@extends('layouts.app', ['title' => 'Detail Gudang — ' . $location->name])

@section('content')
<div class="space-y-6" x-data="{
    showEditModal: false,
    showAdjustModal: false,
    selectedStock: null,
    newQuantity: 0,
    unitCost: 0,
    openAdjust(stock) {
        this.selectedStock = stock;
        this.newQuantity = Number(stock.quantity);
        this.unitCost = Number(stock.last_cost || 0);
        this.showAdjustModal = true;
    }
}">

    {{-- ===== BREADCRUMB & HEADER ===== --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-1.5 mb-2 text-xs text-slate-500">
                <a href="{{ route('warehouse.index') }}" class="hover:text-cyan-400 transition font-semibold">Manajemen Gudang</a>
                <i data-lucide="chevron-right" class="w-3 h-3"></i>
                <span class="text-slate-300 font-semibold">{{ $location->name }}</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0
                    {{ $location->type === 'warehouse' ? 'bg-cyan-500/15 border border-cyan-500/30' : '' }}
                    {{ $location->type === 'outlet' ? 'bg-emerald-500/15 border border-emerald-500/30' : '' }}
                    {{ $location->type === 'central_kitchen' ? 'bg-orange-500/15 border border-orange-500/30' : '' }}
                ">
                    @if($location->type === 'warehouse')
                        <i data-lucide="warehouse" class="w-6 h-6 text-cyan-400"></i>
                    @elseif($location->type === 'central_kitchen')
                        <i data-lucide="utensils" class="w-6 h-6 text-orange-400"></i>
                    @else
                        <i data-lucide="store" class="w-6 h-6 text-emerald-400"></i>
                    @endif
                </div>
                <div>
                    <h1 class="text-2xl font-black text-white tracking-tight">{{ $location->name }}</h1>
                    <div class="flex items-center gap-2 mt-1">
                        @if($location->type === 'warehouse')
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 uppercase">Gudang</span>
                        @elseif($location->type === 'central_kitchen')
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-orange-500/20 text-orange-300 border border-orange-500/30 uppercase">Dapur Pusat</span>
                        @else
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 uppercase">Outlet</span>
                        @endif
                        @if($location->code)
                            <span class="text-[10px] font-mono text-slate-500">{{ $location->code }}</span>
                        @endif
                        @if(!$location->is_active)
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-slate-700 text-slate-400 uppercase">Nonaktif</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('warehouse.index') }}" class="px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition flex items-center gap-1.5">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali</span>
            </a>
            @if(\App\Support\Context::hasPermission('inventory.manage'))
                <button @click="showEditModal = true"
                        class="px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition flex items-center gap-1.5">
                    <i data-lucide="pencil" class="w-4 h-4 text-amber-400"></i>
                    <span>Edit Gudang</span>
                </button>
            @endif
            @if(\App\Support\Context::hasPermission('receiving.manage'))
                <a href="{{ route('purchase-orders.index') }}?po_type=supplier"
                   class="px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs shadow-lg shadow-cyan-500/20 transition flex items-center gap-2">
                    <i data-lucide="package-plus" class="w-4 h-4"></i>
                    <span>Terima Barang dari PO</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- ===== INFO GUDANG ===== --}}
    <div class="glass-card rounded-2xl border border-slate-800 p-5">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            @if($location->address)
            <div class="flex items-start gap-2">
                <i data-lucide="map-pin" class="w-4 h-4 text-slate-500 mt-0.5 shrink-0"></i>
                <div>
                    <div class="text-[10px] text-slate-500 uppercase font-bold mb-0.5">Alamat</div>
                    <div class="text-slate-300">{{ $location->address }}</div>
                </div>
            </div>
            @endif
            @if($location->phone)
            <div class="flex items-start gap-2">
                <i data-lucide="phone" class="w-4 h-4 text-slate-500 mt-0.5 shrink-0"></i>
                <div>
                    <div class="text-[10px] text-slate-500 uppercase font-bold mb-0.5">Telepon</div>
                    <div class="text-slate-300">{{ $location->phone }}</div>
                </div>
            </div>
            @endif
            <div class="flex items-start gap-2">
                <i data-lucide="calendar" class="w-4 h-4 text-slate-500 mt-0.5 shrink-0"></i>
                <div>
                    <div class="text-[10px] text-slate-500 uppercase font-bold mb-0.5">Dibuat</div>
                    <div class="text-slate-300">{{ $location->created_at->format('d M Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== KPI CARDS ===== --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="glass-card rounded-2xl p-5 border border-slate-800">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Total Produk</div>
            <div class="text-3xl font-black text-white font-mono">{{ $stocks->total() }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Item dengan stok > 0</div>
        </div>
        <div class="glass-card rounded-2xl p-5 border border-slate-800">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nilai Aset</div>
            <div class="text-xl font-black text-emerald-400 font-mono">Rp {{ number_format($totalValuation, 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Berdasarkan HPP terakhir</div>
        </div>
        <div class="glass-card rounded-2xl p-5 border border-slate-800">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Stok Minimum</div>
            <div class="text-3xl font-black {{ $lowStockCount > 0 ? 'text-amber-400' : 'text-slate-300' }} font-mono">{{ $lowStockCount }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Butuh segera restock</div>
        </div>
        <div class="glass-card rounded-2xl p-5 border border-slate-800">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Penerimaan Barang</div>
            <div class="text-3xl font-black text-white font-mono">{{ $receipts->count() }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Goods Receipt terbaru</div>
        </div>
    </div>

    {{-- ===== AKSI CEPAT ===== --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <a href="{{ route('purchase-orders.index') }}?po_type=supplier"
           class="glass-card-interactive rounded-2xl border border-slate-800 p-4 flex items-center gap-4 group">
            <div class="w-10 h-10 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center shrink-0">
                <i data-lucide="truck" class="w-5 h-5 text-amber-400"></i>
            </div>
            <div>
                <div class="font-bold text-white text-sm">Terima dari PO</div>
                <div class="text-[11px] text-slate-500">Buka daftar PO Supplier → klik "Terima Barang"</div>
            </div>
        </a>
        <a href="{{ route('inventory.transfers.index') }}"
           class="glass-card-interactive rounded-2xl border border-slate-800 p-4 flex items-center gap-4 group">
            <div class="w-10 h-10 rounded-xl bg-indigo-500/15 border border-indigo-500/30 flex items-center justify-center shrink-0">
                <i data-lucide="arrow-left-right" class="w-5 h-5 text-indigo-400"></i>
            </div>
            <div>
                <div class="font-bold text-white text-sm">Transfer Stok</div>
                <div class="text-[11px] text-slate-500">Kirim barang ke gudang / outlet lain</div>
            </div>
        </a>
        <a href="{{ route('inventory.opnames.index') }}"
           class="glass-card-interactive rounded-2xl border border-slate-800 p-4 flex items-center gap-4 group">
            <div class="w-10 h-10 rounded-xl bg-purple-500/15 border border-purple-500/30 flex items-center justify-center shrink-0">
                <i data-lucide="clipboard-check" class="w-5 h-5 text-purple-400"></i>
            </div>
            <div>
                <div class="font-bold text-white text-sm">Stock Opname</div>
                <div class="text-[11px] text-slate-500">Hitung fisik stok & rekonsiliasi selisih</div>
            </div>
        </a>
    </div>

    {{-- ===== STOK PRODUK DI GUDANG INI ===== --}}
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-2">
                <i data-lucide="package" class="w-4 h-4 text-emerald-400"></i>
                <span class="font-bold text-white text-sm">Stok Produk di Gudang Ini</span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('inventory.stocks') }}?location_id={{ $location->id }}" class="text-xs text-slate-400 hover:text-emerald-400 transition font-semibold">Lihat Detail Lengkap →</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="w-full text-left text-xs text-slate-300 min-w-[650px]">
                <thead class="bg-slate-900/80 text-slate-400 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-800 whitespace-nowrap">
                    <tr>
                        <th class="py-3.5 px-4">Produk</th>
                        <th class="py-3.5 px-4">Kategori</th>
                        <th class="py-3.5 px-4 text-right">Stok</th>
                        <th class="py-3.5 px-4 text-right">Min. Stok</th>
                        <th class="py-3.5 px-4 text-right">HPP/Unit</th>
                        <th class="py-3.5 px-4 text-right">Nilai</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        @if(\App\Support\Context::hasPermission('inventory.manage'))
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($stocks as $stock)
                    @php
                        $isLow = $stock->product && $stock->product->min_stock > 0 && $stock->quantity <= $stock->product->min_stock;
                        $valuation = (float)$stock->quantity * (float)$stock->last_cost;
                    @endphp
                    <tr class="hover:bg-slate-800/30 transition {{ $isLow ? 'bg-amber-500/5' : '' }}">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-white">{{ $stock->product?->name ?? '—' }}</div>
                            @if($stock->product?->code)
                                <div class="text-[10px] font-mono text-slate-500">{{ $stock->product->code }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-slate-400">
                            {{ $stock->product?->category?->name ?? '—' }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <span class="font-black font-mono text-sm {{ $isLow ? 'text-amber-400' : 'text-white' }}">
                                {{ number_format($stock->quantity, 2) }}
                            </span>
                            <span class="text-slate-500 ml-1">{{ $stock->product?->outputUnit?->symbol ?? '' }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono text-slate-500">
                            {{ $stock->product ? number_format($stock->product->min_stock, 2) : '—' }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono text-slate-400">
                            Rp {{ number_format($stock->last_cost, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-400">
                            Rp {{ number_format($valuation, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($isLow)
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">⚠ Hampir Habis</span>
                            @elseif($stock->quantity <= 0)
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">Habis</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Tersedia</span>
                            @endif
                        </td>
                        @if(\App\Support\Context::hasPermission('inventory.manage'))
                        <td class="py-3.5 px-4 text-right">
                            <button
                                @click="openAdjust({
                                    id: '{{ $stock->id }}',
                                    product_id: '{{ $stock->product_id }}',
                                    location_id: '{{ $stock->location_id }}',
                                    product_name: '{{ addslashes($stock->product?->name ?? '') }}',
                                    quantity: '{{ $stock->quantity }}',
                                    last_cost: '{{ $stock->last_cost }}'
                                })"
                                class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-cyan-500/20 text-slate-400 hover:text-cyan-400 text-xs font-semibold transition border border-slate-700 hover:border-cyan-500/30">
                                Sesuaikan
                            </button>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-10 text-center text-slate-500">
                            <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-slate-700"></i>
                            <div>Belum ada stok di gudang ini.</div>
                            <div class="text-xs mt-1">Terima barang dari Purchase Order Supplier untuk mulai mengisi stok.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($stocks->hasPages())
        <div class="p-4 border-t border-slate-800">{{ $stocks->links() }}</div>
        @endif
    </div>

    {{-- ===== RIWAYAT PENERIMAAN BARANG (GOODS RECEIPTS) ===== --}}
    @if($receipts->isNotEmpty())
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
        <div class="p-4 border-b border-slate-800 flex items-center gap-2">
            <i data-lucide="package-check" class="w-4 h-4 text-amber-400"></i>
            <span class="font-bold text-white text-sm">Riwayat Penerimaan Barang (Goods Receipt)</span>
        </div>
        <div class="table-responsive">
            <table class="w-full text-left text-xs text-slate-300 min-w-[620px]">
                <thead class="bg-slate-900/80 text-slate-400 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-800 whitespace-nowrap">
                    <tr>
                        <th class="py-3.5 px-4">No. Penerimaan</th>
                        <th class="py-3.5 px-4">Tanggal</th>
                        <th class="py-3.5 px-4">Supplier</th>
                        <th class="py-3.5 px-4">No. PO</th>
                        <th class="py-3.5 px-4 text-center">Item</th>
                        <th class="py-3.5 px-4">Diterima Oleh</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @foreach($receipts as $gr)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-white font-mono">#{{ $gr->receipt_number }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-slate-400">{{ $gr->receipt_date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="py-3.5 px-4 font-semibold text-slate-200">{{ $gr->supplier?->name ?? 'Tanpa Supplier' }}</td>
                        <td class="py-3.5 px-4">
                            @if($gr->purchaseOrder)
                                <a href="{{ route('purchase-orders.show', $gr->purchaseOrder->id) }}"
                                   class="font-mono text-amber-400 hover:text-amber-300 transition">{{ $gr->purchaseOrder->po_number }}</a>
                            @else
                                <span class="text-slate-600">1-Klik (Solo Mode)</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-300">{{ $gr->items->count() }}</td>
                        <td class="py-3.5 px-4 text-slate-400">{{ $gr->receiver?->name ?? '—' }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                Diterima
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ===== MUTASI STOK TERKINI DI GUDANG INI ===== --}}
    @if($recentMovements->isNotEmpty())
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="history" class="w-4 h-4 text-indigo-400"></i>
                <span class="font-bold text-white text-sm">Kartu Stok — Mutasi Terkini</span>
            </div>
            <a href="{{ route('inventory.movements') }}?location_id={{ $location->id }}" class="text-xs text-slate-400 hover:text-indigo-400 transition font-semibold">Lihat Semua Mutasi →</a>
        </div>
        <div class="table-responsive">
            <table class="w-full text-left text-xs text-slate-300 min-w-[650px]">
                <thead class="bg-slate-900/80 text-slate-400 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-800 whitespace-nowrap">
                    <tr>
                        <th class="py-3.5 px-4">Produk</th>
                        <th class="py-3.5 px-4">Tipe Mutasi</th>
                        <th class="py-3.5 px-4">No. Referensi</th>
                        <th class="py-3.5 px-4 text-right">Perubahan</th>
                        <th class="py-3.5 px-4 text-right">Saldo Akhir</th>
                        <th class="py-3.5 px-4">Oleh</th>
                        <th class="py-3.5 px-4">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @foreach($recentMovements as $mv)
                    @php
                        $mvLabels = [
                            'goods_receipt'  => ['label' => 'Penerimaan Barang', 'color' => 'emerald'],
                            'pos_sale'       => ['label' => 'Penjualan POS', 'color' => 'teal'],
                            'adjustment'     => ['label' => 'Penyesuaian Manual', 'color' => 'amber'],
                            'transfer_in'    => ['label' => 'Transfer Masuk', 'color' => 'indigo'],
                            'transfer_out'   => ['label' => 'Transfer Keluar', 'color' => 'slate'],
                            'opname'         => ['label' => 'Rekonsiliasi Opname', 'color' => 'purple'],
                            'initial'        => ['label' => 'Stok Awal', 'color' => 'cyan'],
                            'pos_refund'     => ['label' => 'Refund POS', 'color' => 'rose'],
                        ];
                        $mvInfo = $mvLabels[$mv->movement_type] ?? ['label' => ucfirst(str_replace('_', ' ', $mv->movement_type)), 'color' => 'slate'];
                    @endphp
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-white">{{ $mv->product?->name ?? '—' }}</div>
                            <div class="text-[10px] text-slate-500 font-mono">{{ $mv->product?->code ?? '' }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-{{ $mvInfo['color'] }}-500/20 text-{{ $mvInfo['color'] }}-400 border border-{{ $mvInfo['color'] }}-500/30">
                                {{ $mvInfo['label'] }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-slate-500 text-[11px]">
                            {{ $mv->reference_number ?? '—' }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono font-black text-sm {{ $mv->quantity_change >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                            {{ $mv->quantity_change >= 0 ? '+' : '' }}{{ number_format($mv->quantity_change, 2) }}
                            <span class="text-slate-600 text-[10px]">{{ $mv->product?->outputUnit?->symbol }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono text-slate-300">
                            {{ number_format($mv->balance_after, 2) }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-400">{{ $mv->creator?->name ?? 'Sistem' }}</td>
                        <td class="py-3.5 px-4 text-slate-500">{{ $mv->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ===== MODAL: EDIT GUDANG ===== --}}
    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" style="display:none;">
        <div class="w-full max-w-lg glass-card rounded-2xl border border-slate-700 p-6 space-y-5 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between">
                <h3 class="font-extrabold text-lg text-white">Edit Gudang: {{ $location->name }}</h3>
                <button @click="showEditModal = false" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <form action="{{ route('warehouse.update', $location->id) }}" method="POST" class="space-y-4 text-sm">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-slate-400 font-bold uppercase text-xs mb-1.5">Nama</label>
                        <input type="text" name="name" value="{{ $location->name }}" required
                               class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-cyan-500">
                    </div>
                    <div>
                        <label class="block text-slate-400 font-bold uppercase text-xs mb-1.5">Tipe</label>
                        <select name="type" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-cyan-500">
                            <option value="warehouse" {{ $location->type === 'warehouse' ? 'selected' : '' }}>🏢 Gudang</option>
                            <option value="outlet" {{ $location->type === 'outlet' ? 'selected' : '' }}>🏪 Outlet</option>
                            <option value="central_kitchen" {{ $location->type === 'central_kitchen' ? 'selected' : '' }}>🍳 Dapur Pusat</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-400 font-bold uppercase text-xs mb-1.5">Kode</label>
                        <input type="text" name="code" value="{{ $location->code }}"
                               class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white font-mono focus:outline-none focus:border-cyan-500">
                    </div>
                    <div>
                        <label class="block text-slate-400 font-bold uppercase text-xs mb-1.5">Telepon</label>
                        <input type="text" name="phone" value="{{ $location->phone }}"
                               class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-cyan-500">
                    </div>
                    <div>
                        <label class="block text-slate-400 font-bold uppercase text-xs mb-1.5">Status</label>
                        <select name="is_active" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-cyan-500">
                            <option value="1" {{ $location->is_active ? 'selected' : '' }}>✅ Aktif</option>
                            <option value="0" {{ !$location->is_active ? 'selected' : '' }}>❌ Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-slate-400 font-bold uppercase text-xs mb-1.5">Alamat</label>
                        <textarea name="address" rows="2"
                                  class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-cyan-500 resize-none">{{ $location->address }}</textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-slate-800">
                    <button type="button" @click="showEditModal = false"
                            class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-sm transition">Batal</button>
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-sm transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== MODAL: PENYESUAIAN STOK ===== --}}
    @if(\App\Support\Context::hasPermission('inventory.manage'))
    <div x-show="showAdjustModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" style="display:none;">
        <div class="w-full max-w-md glass-card rounded-2xl border border-slate-700 p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-lg text-white">Sesuaikan Stok</h3>
                    <p class="text-xs text-slate-400 mt-0.5" x-text="selectedStock ? selectedStock.product_name : ''"></p>
                </div>
                <button @click="showAdjustModal = false" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <form action="{{ route('inventory.stocks.adjust') }}" method="POST" class="space-y-3 text-sm">
                @csrf
                <input type="hidden" name="product_id" x-bind:value="selectedStock?.product_id">
                <input type="hidden" name="location_id" value="{{ $location->id }}">
                <div>
                    <label class="block text-slate-400 font-bold uppercase text-xs mb-1.5">Stok Saat Ini</label>
                    <div class="px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white font-mono font-black" x-text="selectedStock?.quantity ?? 0"></div>
                </div>
                <div>
                    <label class="block text-slate-400 font-bold uppercase text-xs mb-1.5">Kuantitas Baru (Aktual)</label>
                    <input type="number" name="new_quantity" step="any" x-model="newQuantity" min="0" required
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white font-mono focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-slate-400 font-bold uppercase text-xs mb-1.5">HPP / Biaya Satuan (opsional)</label>
                    <input type="number" name="unit_cost" step="any" x-model="unitCost" min="0"
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white font-mono focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-slate-400 font-bold uppercase text-xs mb-1.5">Keterangan</label>
                    <input type="text" name="notes" placeholder="Alasan penyesuaian..."
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-slate-800">
                    <button type="button" @click="showAdjustModal = false"
                            class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-sm">Batal</button>
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-sm">
                        Simpan Penyesuaian
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection
