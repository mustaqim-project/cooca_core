@extends('layouts.app', ['title' => 'Transfer Stok Antar Outlet'])

@section('content')
<div class="space-y-6" x-data="{
    showCreateModal: false,
    items: [
        @if($products->count() > 0)
            { product_id: '{{ $products->first()->id }}', quantity: 1 }
        @endif
    ],
    addItem() {
        this.items.push({ product_id: '{{ $products->first()?->id ?? '' }}', quantity: 1 });
    },
    removeItem(idx) {
        this.items.splice(idx, 1);
    }
}">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Transfer Stok Antar Cabang / Gudang</h1>
            <p class="text-sm text-slate-400 mt-1">Kirim dan terima barang antar outlet atau gudang pusat dengan pencatatan mutasi otomatis.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('inventory.stocks') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition">
                ← Kembali ke Saldo Stok
            </a>
            <button @click="showCreateModal = true" class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs shadow-lg shadow-emerald-500/20 transition flex items-center gap-2">
                <i data-lucide="send" class="w-4 h-4"></i>
                <span>Kirim Transfer Baru</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Transfers Table -->
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-900/80 text-slate-400 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">No. Transfer</th>
                        <th class="py-3.5 px-4">Tanggal</th>
                        <th class="py-3.5 px-4">Asal → Tujuan</th>
                        <th class="py-3.5 px-4">Pengirim / Penerima</th>
                        <th class="py-3.5 px-4 text-center">Item</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($transfers as $tr)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-white font-mono">#{{ $tr->transfer_number }}</div>
                            <div class="text-[11px] text-slate-400">{{ $tr->notes ?? 'Mutasi internal' }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div>{{ $tr->transfer_date->format('d/m/Y') }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-white flex items-center gap-1.5">
                                <span>{{ $tr->sourceLocation->name ?? 'Asal' }}</span>
                                <span class="text-emerald-400">→</span>
                                <span>{{ $tr->destinationLocation->name ?? 'Tujuan' }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div>Kirim: <span class="font-medium text-slate-200">{{ $tr->creator->name ?? '-' }}</span></div>
                            <div class="text-[10px] text-slate-400">Terima: {{ $tr->receiver->name ?? 'Belum' }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono font-bold">
                            {{ $tr->items->count() }} Produk
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($tr->status === 'received')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Diterima</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">Dalam Pengiriman</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            @if($tr->status !== 'received')
                                <form action="{{ route('inventory.transfers.receive', $tr->id) }}" method="POST" class="inline" onsubmit="return AppAlert.confirmSubmit(event, this, 'Konfirmasi bahwa barang transfer ini sudah sampai dan diterima di lokasi tujuan?', 'Konfirmasi Penerimaan Transfer?', 'info')">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs transition">
                                        Konfirmasi Terima
                                    </button>
                                </form>
                            @else
                                <span class="text-slate-500 text-xs">Selesai</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500">Belum ada transfer stok antar outlet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transfers->hasPages())
        <div class="p-4 border-t border-slate-800">
            {{ $transfers->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Buat Transfer Stok -->
    <div x-show="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" style="display: none;">
        <div class="w-full max-w-xl glass-card rounded-2xl border border-slate-700 p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            <h3 class="font-extrabold text-lg text-white">Kirim Transfer Stok Baru</h3>
            <form action="{{ route('inventory.transfers.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-400 font-bold uppercase mb-1">Lokasi Asal (Pengirim)</label>
                        <select name="source_location_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-400 font-bold uppercase mb-1">Lokasi Tujuan (Penerima)</label>
                        <select name="destination_location_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ $loop->last ? 'selected' : '' }}>{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-400 font-bold uppercase mb-1">Tanggal Transfer</label>
                        <input type="date" name="transfer_date" value="{{ date('Y-m-d') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block text-slate-400 font-bold uppercase mb-1">Catatan / No. Surat Jalan</label>
                        <input type="text" name="notes" placeholder="Misal: Surat Jalan #SJ-01..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                    </div>
                </div>

                <!-- Items Row -->
                <div class="space-y-2 pt-2 border-t border-slate-800">
                    <div class="flex items-center justify-between">
                        <label class="font-bold text-slate-300 uppercase tracking-wider">Produk yang Ditransfer:</label>
                        <button type="button" @click="addItem()" class="text-emerald-400 hover:text-emerald-300 font-bold">+ Tambah Produk</button>
                    </div>

                    <template x-for="(item, idx) in items" :key="idx">
                        <div class="flex items-center gap-2">
                            <select :name="'items[' + idx + '][product_id]'" x-model="item.product_id" class="flex-1 bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                            <input type="number" step="any" :name="'items[' + idx + '][quantity]'" x-model.number="item.quantity" placeholder="Qty" class="w-24 bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white font-mono text-center">
                            <button type="button" @click="removeItem(idx)" class="p-2 text-rose-400 hover:text-rose-300"><i data-lucide="x" class="w-4 h-4"></i></button>
                        </div>
                    </template>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold text-xs">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs">Kirim Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
