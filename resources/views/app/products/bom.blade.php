@extends('layouts.app', [
    'title' => 'Resep & BOM — ' . $product->name,
    'headerTitle' => 'Struktur Resep / Bill of Materials (BOM)',
    'headerSubtitle' => 'Komposisi bahan, susut per item, dan total biaya terakumulasi untuk ' . $product->name
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
    showAddItemModal: false,
    deleteModalOpen: false,
    deleteTarget: { id: null, name: '' },
    openDelete(id, name) {
        this.deleteTarget = { id, name };
        this.deleteModalOpen = true;
    },
    closeDelete() {
        this.deleteModalOpen = false;
        this.deleteTarget = { id: null, name: '' };
    },
    submitDelete() {
        if (this.deleteTarget.id) {
            document.getElementById('form-delete-' + this.deleteTarget.id).submit();
        }
    }
}">

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <!-- Breadcrumb minimal -->
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <a href="{{ route('products.index') }}" class="hover:text-[#007AFF] transition-colors">Katalog Produk</a>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">{{ $product->name }}</span>
                <span>›</span>
                <span class="text-black/70 dark:text-white/70 font-medium">BOM</span>
            </nav>
            <div class="flex items-center gap-2.5">
                <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">{{ $product->name }}</h1>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] tabular-nums">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                    Output: {{ $bomHeader->output_quantity }} {{ $product->outputUnit?->name ?? 'pcs' }}
                </span>
            </div>
            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Model HPP: {{ $costModel->name }} ({{ strtoupper($costModel->method) }})</p>
        </div>

        <!-- Toolbar Actions -->
        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
            <a href="{{ route('products.index') }}" class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                <span>Daftar Produk</span>
            </a>

            @if(\App\Support\Context::hasPermission('costing.manage'))
            <a href="{{ route('import.index', ['tab' => 'recipes']) }}" 
               class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5"
               title="Import formula resep dari file Excel / CSV">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                <span>Import Resep</span>
            </a>

            <button type="button" @click="showAddItemModal = true" 
                    class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                <span>Tambah Bahan</span>
            </button>
            @endif
        </div>
    </header>

    <!-- ===================================================== -->
    <!-- 2. TOTAL ACCUMULATED COST KPI                         -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Biaya Bahan Resep (BOM)</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">
                    {{ $business->currency_symbol }} {{ number_format((float) ($explosion['total_rolled_up_material_cost'] ?? 0), 0, ',', '.') }}
                </span>
                <span class="text-[11px] font-medium text-[#34C759] dark:text-[#30D158]">Rolled-up Cost</span>
            </div>
        </div>

        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Komponen Terdaftar</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-black dark:text-white">
                    {{ $bomHeader->items->count() }}
                </span>
                <span class="text-[11px] text-black/40 dark:text-white/40">Bahan Baku</span>
            </div>
        </div>

        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Output Batch Standar</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-[#007AFF]">
                    {{ $bomHeader->output_quantity }} {{ $product->outputUnit?->code ?? 'pcs' }}
                </span>
                <span class="text-[11px] text-black/40 dark:text-white/40">Satuan Akhir</span>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. BOM ITEMS TABLE (Dense High-Density Data List)      -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
        <div class="px-4 py-3 border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] flex items-center justify-between">
            <h2 class="text-[13px] font-semibold text-black dark:text-white">Komponen Bahan Terdaftar ({{ $bomHeader->items->count() }} Komponen)</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02]">
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Komponen Bahan Baku</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Jumlah Resep</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Harga Efektif Bahan</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Susut (Waste %)</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Subtotal Biaya</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($bomHeader->items as $item)
                    @php
                        $mat = $item->material;
                        $price = $mat?->prices->first()?->effective_cost ?? 0;
                        $itemSubtotal = $price * $item->quantity * (1 + ($item->waste_percentage / 100));
                    @endphp
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="py-3 px-4 font-medium text-black dark:text-white">
                            <div class="font-semibold">{{ $mat?->name ?? 'Sub-BOM' }}</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums">{{ $mat?->sku ?? '-' }}</div>
                            @if($item->notes)
                                <div class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">{{ $item->notes }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center tabular-nums font-semibold text-black dark:text-white">
                            {{ $item->quantity }} {{ $item->unit?->code ?? $mat?->unit?->code }}
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums text-black/70 dark:text-white/70">
                            {{ $business->currency_symbol }} {{ number_format((float)$price, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($item->waste_percentage > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A] tabular-nums">
                                    +{{ $item->waste_percentage }}%
                                </span>
                            @else
                                <span class="text-black/40 dark:text-white/40 tabular-nums">0%</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums font-bold text-[#34C759] dark:text-[#30D158]">
                            {{ $business->currency_symbol }} {{ number_format((float)$itemSubtotal, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            @if(\App\Support\Context::hasPermission('costing.manage'))
                            <button type="button" @click="openDelete('{{ $item->id }}', '{{ addslashes($mat?->name ?? 'Komponen Resep') }}')"
                                    class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/8 transition-colors inline-flex items-center">
                                Hapus
                            </button>
                            <form id="form-delete-{{ $item->id }}" method="POST" action="{{ route('bom.items.destroy', $item->id) }}" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-black/45 dark:text-white/45">
                            Resep masih kosong. Klik tombol "+ Tambah Bahan" di toolbar atas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 4. MODAL: TAMBAH BAHAN KE BOM (Apple Sheet)           -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('costing.manage'))
    <div x-show="showAddItemModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]">
        <div class="w-full max-w-md rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/10 dark:border-white/10 p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)]" @click.outside="showAddItemModal = false">
            <div class="flex items-center justify-between border-b border-black/10 dark:border-white/10 pb-3">
                <h3 class="text-[16px] font-semibold text-black dark:text-white">Tambah Bahan ke Resep / BOM</h3>
                <button @click="showAddItemModal = false" class="p-1 rounded-[6px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">✕</button>
            </div>

            <form method="POST" action="{{ route('bom.items.store', $bomHeader->id) }}" class="space-y-3.5 text-[13px]">
                @csrf
                
                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Pilih Bahan Baku <span class="text-[#FF3B30]">*</span></label>
                    <select name="material_id" required class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="">-- Pilih Bahan Baku --</option>
                        @foreach($materials as $m)
                            <option value="{{ $m->id }}">
                                {{ $m->name }} ({{ $m->unit?->code }} - {{ $business->currency_symbol }} {{ number_format((float)($m->prices->first()?->effective_cost ?? 0), 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Jumlah Pemakaian <span class="text-[#FF3B30]">*</span></label>
                        <input type="number" name="quantity" step="any" min="0.0001" required placeholder="0.5"
                               class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Satuan Takar <span class="text-[#FF3B30]">*</span></label>
                        <select name="unit_id" required class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Susut Proses / Waste Tambahan (%)</label>
                    <input type="number" name="waste_percentage" value="0" min="0" max="100" step="0.5"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">Misal sisa adonan pada wadah atau potongan terbuang.</p>
                </div>

                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Catatan Khusus (Opsional)</label>
                    <input type="text" name="notes" placeholder="Misal: dimasukkan pada menit terakhir"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-black/10 dark:border-white/10">
                    <button type="button" @click="showAddItemModal = false" class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] transition">Batal</button>
                    <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition shadow-[0_1px_2px_rgba(0,122,255,0.25)]">Tambah ke Resep</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 5. APPLE ALERT DIALOG (Hapus Komponen Resep)          -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('costing.manage'))
    <div x-show="deleteModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px]">
        <div class="w-[290px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
            @click.away="closeDelete()">
            <div class="px-4 pt-5 pb-4">
                <p class="text-[17px] font-semibold text-black dark:text-white">Hapus Bahan Resep?</p>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                    <span x-text="deleteTarget.name" class="font-medium text-black dark:text-white"></span> akan dihapus dari formula BOM produk ini.
                </p>
            </div>
            <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                <button type="button" @click="closeDelete()" class="py-3 text-[#007AFF] border-r border-black/10 dark:border-white/10 active:bg-black/5 transition-colors">
                    Batal
                </button>
                <button type="button" @click="submitDelete()" class="py-3 text-[#FF3B30] font-semibold active:bg-black/5 transition-colors">
                    Hapus
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
