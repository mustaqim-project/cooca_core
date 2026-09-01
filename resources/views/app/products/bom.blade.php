@extends('layouts.app', [
    'title' => 'Resep & BOM — ' . $product->name,
    'headerTitle' => 'Struktur Resep / Bill of Materials (BOM)',
    'headerSubtitle' => 'Komposisi bahan, susut per item, dan total biaya terakumulasi untuk ' . $product->name
])

@section('content')
<div class="space-y-6" x-data="{ showAddItemModal: false }">

    <!-- Top Info Card -->
    <div class="glass-card p-6 rounded-2xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('products.index') }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 hover:bg-slate-800 text-slate-400 hover:text-white transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-bold text-white">{{ $product->name }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full bg-slate-800 text-emerald-400 font-mono text-[10px] uppercase font-bold">
                        Output: {{ $bomHeader->output_quantity }} {{ $product->outputUnit?->name ?? 'pcs' }}
                    </span>
                </div>
                <p class="text-xs text-slate-400 mt-0.5">Model HPP: {{ $costModel->name }} ({{ strtoupper($costModel->method) }})</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="p-3 rounded-xl bg-slate-950/80 border border-slate-800 text-right">
                <div class="text-[10px] text-slate-400 uppercase font-semibold">Total Biaya Bahan BOM</div>
                <div class="text-lg font-extrabold text-emerald-400 font-mono">
                    {{ $business->currency_symbol }} {{ number_format((float) ($explosion['total_rolled_up_material_cost'] ?? 0), 0, ',', '.') }}
                </div>
            </div>

            <button @click="showAddItemModal = true" 
                    class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-lg shadow-emerald-500/20 flex items-center gap-2 transition-all">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Tambah Bahan ke Resep</span>
            </button>
        </div>
    </div>

    <!-- BOM Items Table Card -->
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="p-4 border-b border-slate-800 bg-slate-900/40 flex items-center justify-between">
            <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="list-tree" class="w-4 h-4 text-emerald-400"></i>
                <span>Komponen Bahan Terdaftar ({{ $bomHeader->items->count() }} Komponen)</span>
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-800 bg-slate-900/50">
                        <th class="py-3.5 px-4 font-semibold">Komponen Bahan Baku</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Jumlah Resep</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Harga Efektif Bahan</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Waste %</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Subtotal Biaya</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($bomHeader->items as $item)
                    @php
                        $mat = $item->material;
                        $price = $mat?->prices->first()?->effective_cost ?? 0;
                        $itemSubtotal = $price * $item->quantity * (1 + ($item->waste_percentage / 100));
                    @endphp
                    <tr class="hover:bg-slate-900/40 transition-colors">
                        <td class="py-3.5 px-4 font-medium text-white">
                            <div class="font-bold">{{ $mat?->name ?? 'Sub-BOM' }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $mat?->sku ?? '-' }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono font-bold text-white">
                            {{ $item->quantity }} {{ $item->unit?->code ?? $mat?->unit?->code }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono text-slate-300">
                            {{ $business->currency_symbol }} {{ number_format((float)$price, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($item->waste_percentage > 0)
                                <span class="px-2 py-0.5 rounded-full bg-red-500/10 text-red-400 font-mono font-bold text-[10px]">
                                    +{{ $item->waste_percentage }}%
                                </span>
                            @else
                                <span class="text-slate-500">0%</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono font-extrabold text-emerald-400">
                            {{ $business->currency_symbol }} {{ number_format((float)$itemSubtotal, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <form method="POST" action="{{ route('bom.items.destroy', $item->id) }}" onsubmit="return confirm('Hapus komponen ini dari resep?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-500 hover:text-red-400 rounded transition-colors">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500">
                            Resep masih kosong. Klik tombol "+ Tambah Bahan ke Resep" di atas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Item ke BOM -->
    <div x-show="showAddItemModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="glass-card max-w-md w-full p-6 rounded-2xl space-y-4" @click.outside="showAddItemModal = false">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Tambah Bahan ke Resep / BOM</h3>
                <button @click="showAddItemModal = false" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form method="POST" action="{{ route('bom.items.store', $bomHeader->id) }}" class="space-y-3.5 text-xs">
                @csrf
                
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Pilih Bahan Baku *</label>
                    <select name="material_id" required class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                        <option value="">-- Pilih Bahan --</option>
                        @foreach($materials as $m)
                            <option value="{{ $m->id }}">
                                {{ $m->name }} (Satuan: {{ $m->unit?->code }} - Efektif: {{ $business->currency_symbol }} {{ number_format((float)($m->prices->first()?->effective_cost ?? 0), 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Jumlah Pemakaian *</label>
                        <input type="number" name="quantity" step="any" min="0.0001" required placeholder="0.5"
                               class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Satuan Takar *</label>
                        <select name="unit_id" required class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Susut Proses / Waste Tambahan (%)</label>
                    <input type="number" name="waste_percentage" value="0" min="0" max="100" step="0.5"
                           class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    <p class="text-[10px] text-slate-400 mt-1">Misal sisa adonan di wadah atau potongan terbuang.</p>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Catatan Khusus</label>
                    <input type="text" name="notes" placeholder="Dicampur di tahap akhir"
                           class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="showAddItemModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold shadow-lg shadow-emerald-500/20">Tambah ke Resep</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
