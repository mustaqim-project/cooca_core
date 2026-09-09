@extends('layouts.app', ['title' => 'Inventori & Stok Multi-Gudang'])

@section('content')
<div class="space-y-6" x-data="{
    showAdjustModal: false,
    selectedStock: null,
    newQuantity: 0,
    unitCost: 0,
    openAdjust(stockId) {
        const stock = (window.COOCA_STOCKS || []).find(s => s.id === stockId);
        if (!stock) return;
        this.selectedStock = stock;
        this.newQuantity = Number(stock.quantity);
        this.unitCost = Number(stock.last_cost || (stock.product ? stock.product.base_cost : 0) || 0);
        this.showAdjustModal = true;
    }
}">
    <script>
        window.COOCA_STOCKS = @json($stocks->items());
    </script>
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Inventori & Stok Real-Time</h1>
            <p class="text-sm text-slate-400 mt-1">Pantau stok produk di seluruh cabang/outlet & gudang secara real-time dengan sistem kartu stok.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('import.index', ['tab' => 'inventory']) }}" class="px-3.5 py-2.5 rounded-xl bg-violet-500/15 hover:bg-violet-500/25 text-violet-300 border border-violet-500/30 text-xs font-bold transition flex items-center gap-1.5">
                <i data-lucide="upload-cloud" class="w-4 h-4 text-violet-400"></i>
                <span>Import Stok</span>
            </a>
            <a href="{{ route('inventory.movements') }}" class="px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition flex items-center gap-1.5">
                <i data-lucide="history" class="w-4 h-4 text-emerald-400"></i>
                <span>Kartu Stok</span>
            </a>
            <a href="{{ route('inventory.opnames.index') }}" class="px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition flex items-center gap-1.5">
                <i data-lucide="clipboard-check" class="w-4 h-4 text-amber-400"></i>
                <span>Stock Opname</span>
            </a>
            <a href="{{ route('inventory.transfers.index') }}" class="px-3.5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-bold transition flex items-center gap-1.5 shadow-lg shadow-emerald-500/20">
                <i data-lucide="arrow-left-right" class="w-4 h-4"></i>
                <span>Transfer Stok</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="glass-card rounded-2xl p-4 border border-slate-800">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Nilai Aset Inventori</div>
            <div class="text-2xl font-black text-emerald-400 font-mono mt-1">Rp {{ number_format($totalValuation, 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Berdasarkan HPP / Biaya Pokok terakhir</div>
        </div>

        <div class="glass-card rounded-2xl p-4 border border-slate-800">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Peringatan Stok Minimum</div>
            <div class="text-2xl font-black {{ $lowStockCount > 0 ? 'text-amber-400' : 'text-slate-200' }} font-mono mt-1">
                {{ $lowStockCount }} Produk
            </div>
            <div class="text-[11px] text-slate-500 mt-1">Stok di bawah batas min_stock</div>
        </div>

        <div class="glass-card rounded-2xl p-4 border border-slate-800">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Cabang / Outlet</div>
            <div class="text-2xl font-black text-white font-mono mt-1">{{ $locations->count() }} Lokasi</div>
            <div class="text-[11px] text-slate-500 mt-1">Outlet penjualan & gudang penyimpanan</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="glass-card rounded-2xl p-4 border border-slate-800">
        <form method="GET" action="{{ route('inventory.stocks') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama produk / kode SKU..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-emerald-500">
            </div>
            <div>
                <select name="location_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none">
                    <option value="">Semua Lokasi / Outlet</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('location_id') === $loc->id ? 'selected' : '' }}>
                            {{ $loc->name }} ({{ strtoupper($loc->type ?? 'Outlet') }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold transition">
                    Filter
                </button>
                <a href="{{ route('inventory.stocks') }}" class="px-3 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-400 text-xs font-semibold flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Stock Table -->
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
        <div class="table-responsive">
            <table class="w-full text-left text-xs text-slate-300 min-w-[700px]">
                <thead class="bg-slate-900/80 text-slate-400 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4 whitespace-nowrap">Produk / SKU</th>
                        <th class="py-3.5 px-4 whitespace-nowrap">Lokasi / Outlet</th>
                        <th class="py-3.5 px-4 text-right whitespace-nowrap">Stok Fisik</th>
                        <th class="py-3.5 px-4 text-right whitespace-nowrap">Stok Min.</th>
                        <th class="py-3.5 px-4 text-right whitespace-nowrap">HPP / Unit</th>
                        <th class="py-3.5 px-4 text-right whitespace-nowrap">Total Nilai</th>
                        <th class="py-3.5 px-4 text-center whitespace-nowrap">Status</th>
                        <th class="py-3.5 px-4 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($stocks as $st)
                    @php 
                        $isLow = $st->quantity <= ($st->product->min_stock ?? 0); 
                        $val = $st->quantity * ($st->last_cost > 0 ? $st->last_cost : ($st->product->base_cost ?? 0));
                    @endphp
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-white">{{ $st->product->name ?? 'Produk' }}</div>
                            <div class="text-[11px] text-slate-400 font-mono">{{ $st->product->code ?? '-' }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded-lg bg-slate-900 border border-slate-800 text-slate-300 font-medium">
                                {{ $st->location->name ?? 'Outlet Utama' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono font-bold text-white text-sm">
                            {{ rtrim(rtrim((string)$st->quantity, '0'), '.') }} {{ $st->product->outputUnit->code ?? '' }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono text-slate-400">
                            {{ $st->product->min_stock ?? 0 }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono text-slate-300">
                            Rp {{ number_format($st->last_cost > 0 ? $st->last_cost : ($st->product->base_cost ?? 0), 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-400">
                            Rp {{ number_format($val, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($st->quantity <= 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">Habis</span>
                            @elseif($isLow)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">Menipis</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Aman</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <button @click="openAdjust('{{ $st->id }}')" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition">
                                Sesuaikan
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-500">Belum ada data stok tercatat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($stocks->hasPages())
        <div class="p-4 border-t border-slate-800">
            {{ $stocks->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Penyesuaian Stok Cepat -->
    <div x-show="showAdjustModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" style="display: none;">
        <div class="w-full max-w-md glass-card rounded-2xl border border-slate-700 p-6 space-y-4">
            <h3 class="font-extrabold text-lg text-white">Penyesuaian Stok Cepat</h3>
            <form action="{{ route('inventory.stocks.adjust') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <input type="hidden" name="location_id" :value="selectedStock ? selectedStock.location_id : ''">
                <input type="hidden" name="product_id" :value="selectedStock ? selectedStock.product_id : ''">

                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800">
                    <div class="font-bold text-white" x-text="selectedStock ? selectedStock.product.name : ''"></div>
                    <div class="text-[11px] text-slate-400" x-text="selectedStock ? selectedStock.location.name : ''"></div>
                </div>

                <div>
                    <label class="block text-slate-400 font-bold uppercase mb-1">Jumlah Stok Baru</label>
                    <input type="number" step="any" name="new_quantity" x-model.number="newQuantity" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-lg font-bold font-mono text-white focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-slate-400 font-bold uppercase mb-1">Biaya Pokok (HPP) per Unit (Rp)</label>
                    <input type="number" name="unit_cost" x-model.number="unitCost" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                </div>

                <div>
                    <label class="block text-slate-400 font-bold uppercase mb-1">Alasan Penyesuaian</label>
                    <input type="text" name="notes" placeholder="Misal: Koreksi saldo awal, barang rusak..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                    <button type="button" @click="showAdjustModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold text-xs">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
