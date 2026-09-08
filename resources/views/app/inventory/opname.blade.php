@extends('layouts.app', ['title' => 'Stock Opname'])

@section('content')
<div class="space-y-6" x-data="{
    showCreateModal: false,
    selectedLocationId: '{{ $locations->first()?->id ?? '' }}',
    items: [
        { material_id: '', product_id: '', item_type: 'material', physical_quantity: 0 }
    ],
    addItem() {
        this.items.push({ material_id: '', product_id: '', item_type: 'material', physical_quantity: 0 });
    },
    removeItem(idx) {
        this.items.splice(idx, 1);
    },
    changeItemType(item) {
        item.material_id = '';
        item.product_id = '';
    }
}">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Stock Opname Fisik</h1>
            <p class="text-sm text-slate-400 mt-1">Hitung fisik bahan baku atau produk, tinjau selisih, lalu rekonsiliasi stok secara terkontrol.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('inventory.stocks') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition">
                ← Kembali ke Saldo Stok
            </a>
            <button @click="showCreateModal = true" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-lg shadow-amber-500/20 transition flex items-center gap-2">
                <i data-lucide="clipboard-check" class="w-4 h-4"></i>
                <span>Mulai Stock Opname Baru</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm space-y-1">
            <div class="flex items-center gap-2 font-bold"><i data-lucide="alert-circle" class="w-5 h-5"></i><span>Stock opname belum disimpan.</span></div>
            <ul class="list-disc pl-7 text-xs text-rose-200">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Opnames List -->
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-900/80 text-slate-400 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">No. Opname</th>
                        <th class="py-3.5 px-4">Tanggal / Lokasi</th>
                        <th class="py-3.5 px-4">Pelaksana</th>
                        <th class="py-3.5 px-4 text-center">Jumlah Item</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($opnames as $op)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-white font-mono">#{{ $op->opname_number }}</div>
                            <div class="text-[11px] text-slate-400">{{ $op->notes ?? 'Stock taking rutin bahan baku' }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div>{{ $op->opname_date->format('d/m/Y') }}</div>
                            <span class="text-[10px] text-slate-400">{{ $op->location->name ?? 'Outlet' }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-medium text-slate-200">{{ $op->conductor->name ?? 'Staff' }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono font-bold">
                            {{ $op->items->count() }} Item
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($op->status === 'reconciled')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Direkonsiliasi</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">Sedang Dihitung</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            @if($op->status !== 'reconciled')
                                <form action="{{ route('inventory.opnames.reconcile', $op->id) }}" method="POST" class="inline" onsubmit="return AppAlert.confirmSubmit(event, this, 'Terapkan selisih fisik opname ini ke saldo stok sistem?', 'Rekonsiliasi Stok Opname?', 'warning')">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs transition">
                                        Rekonsiliasi Stok
                                    </button>
                                </form>
                            @else
                                <span class="text-slate-500 text-xs">Selesai</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500">Belum ada sesi stock opname.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($opnames->hasPages())
        <div class="p-4 border-t border-slate-800">
            {{ $opnames->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Buat Stock Opname -->
    <div x-show="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" style="display: none;">
        <div class="w-full max-w-xl glass-card rounded-2xl border border-slate-700 p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            <h3 class="font-extrabold text-lg text-white">Mulai Stock Opname Baru</h3>
            <form action="{{ route('inventory.opnames.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-400 font-bold uppercase mb-1">Lokasi Outlet / Gudang</label>
                        <select name="location_id" x-model="selectedLocationId" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-400 font-bold uppercase mb-1">Tanggal Opname</label>
                        <input type="date" name="opname_date" value="{{ date('Y-m-d') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-400 font-bold uppercase mb-1">Catatan</label>
                    <input type="text" name="notes" placeholder="Misal: Opname bulanan fisik bahan baku..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                </div>

                <!-- Items Row -->
                <div class="space-y-2 pt-2 border-t border-slate-800">
                    <div class="flex items-center justify-between">
                            <label class="font-bold text-slate-300 uppercase tracking-wider">Item yang Dihitung:</label>
                        <button type="button" @click="addItem()" class="text-emerald-400 hover:text-emerald-300 font-bold">+ Tambah Item</button>
                    </div>

                    <template x-for="(item, idx) in items" :key="idx">
                        <div class="grid grid-cols-[7rem_minmax(0,1fr)_7rem_auto] items-center gap-2">
                            <select x-model="item.item_type" @change="changeItemType(item)" class="bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white">
                                <option value="material">Bahan</option>
                                <option value="product">Produk</option>
                            </select>
                            <select x-show="item.item_type === 'material'" :name="'items[' + idx + '][material_id]'" x-model="item.material_id" class="min-w-0 bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                                <option value="">-- Pilih Bahan Baku --</option>
                                @foreach($materials as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->unit->code ?? 'unit' }})</option>
                                @endforeach
                            </select>
                            <select x-show="item.item_type === 'product'" :name="'items[' + idx + '][product_id]'" x-model="item.product_id" class="min-w-0 bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                                <option value="">-- Pilih Produk --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}{{ $product->code ? ' (' . $product->code . ')' : '' }}</option>
                                @endforeach
                            </select>
                            <input type="number" step="any" :name="'items[' + idx + '][physical_quantity]'" x-model.number="item.physical_quantity" placeholder="Qty Fisik" class="w-28 bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white font-mono text-center">
                            <button type="button" @click="removeItem(idx)" class="p-2 text-rose-400 hover:text-rose-300"><i data-lucide="x" class="w-4 h-4"></i></button>
                        </div>
                    </template>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold text-xs">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs">Simpan Data Opname</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
