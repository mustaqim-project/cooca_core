@extends('layouts.app', [
    'title' => 'Resep & BOM - ' . $product->name,
    'headerTitle' => 'Struktur Resep / Bill of Materials (BOM)',
    'headerSubtitle' => 'Komposisi bahan baku, takaran porsi, susut proses, dan kalkulasi HPP terakumulasi untuk ' . $product->name,
])

@section('content')
<div class="max-w-[1440px] mx-auto w-full px-4 sm:px-6 lg:px-8 space-y-6 pb-28 sm:pb-32 lg:pb-10" x-data="{
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
            const f = document.getElementById('form-delete-' + this.deleteTarget.id);
            if (f) f.submit();
        }
    }
}">

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
    <!-- ===================================================== -->
    <header class="rounded-[20px] backdrop-blur-xl bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] px-5 sm:px-7 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
        <div class="min-w-0 flex flex-col justify-center">
            <!-- Breadcrumb minimal -->
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <a href="{{ route('products.index') }}" class="hover:text-[#007AFF] transition-colors">Katalog Produk</a>
                <span>›</span>
                <span class="text-black/70 dark:text-white/70 font-medium truncate">{{ $product->name }}</span>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">BOM</span>
            </nav>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-xl sm:text-2xl font-bold text-[#1C1C1E] dark:text-[#F2F2F7] tracking-tight leading-snug truncate">
                    {{ $product->name }}
                </h1>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[12px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] tabular-nums shrink-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                    Batch: {{ $bomHeader->output_quantity }} {{ $product->outputUnit?->name ?? 'pcs' }}
                </span>
            </div>
            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">
                Model HPP: {{ $costModel->name }} ({{ strtoupper($costModel->method) }})
            </p>
        </div>

        <!-- Toolbar Actions -->
        <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
            <a href="{{ route('products.index') }}"
                class="h-10 px-3.5 rounded-[12px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] border border-black/[0.06] dark:border-white/[0.08] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Katalog Produk</span>
            </a>

            @if (\App\Support\Context::hasPermission('costing.manage'))
                <a href="{{ route('import.index', ['tab' => 'recipes']) }}"
                    class="h-10 px-3.5 rounded-[12px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] border border-black/[0.06] dark:border-white/[0.08] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5"
                    title="Import formula resep dari file Excel / CSV">
                    <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    <span>Import Resep</span>
                </a>

                <button type="button" @click="showAddItemModal = true"
                    class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 shadow-sm shadow-[#007AFF]/25">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Tambah Bahan</span>
                </button>
            @endif
        </div>
    </header>

    <!-- ===================================================== -->
    <!-- 2. TOTAL ACCUMULATED COST KPI (Bento Cards)            -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 lg:gap-5">
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 flex flex-col justify-between shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[13px] font-medium text-black/50 dark:text-white/50">Total Biaya Bahan Resep (BOM)</span>
                <div class="w-8 h-8 rounded-full bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl sm:text-3xl font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">
                    {{ $business->currency_symbol }} {{ number_format((float) ($explosion['total_rolled_up_material_cost'] ?? 0), 0, ',', '.') }}
                </span>
                <span class="text-[12px] font-medium text-[#34C759] dark:text-[#30D158]">Rolled-up Cost</span>
            </div>
        </div>

        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 flex flex-col justify-between shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[13px] font-medium text-black/50 dark:text-white/50">Komponen Bahan Baku</span>
                <div class="w-8 h-8 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl sm:text-3xl font-bold tabular-nums text-black dark:text-white">
                    {{ $bomHeader->items->count() }}
                </span>
                <span class="text-[12px] text-black/40 dark:text-white/40">Komposisi</span>
            </div>
        </div>

        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 flex flex-col justify-between shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[13px] font-medium text-black/50 dark:text-white/50">Output Batch Standar</span>
                <div class="w-8 h-8 rounded-full bg-[#AF52DE]/10 text-[#AF52DE] dark:text-[#BF5AF2] flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl sm:text-3xl font-bold tabular-nums text-[#007AFF]">
                    {{ $bomHeader->output_quantity }} {{ $product->outputUnit?->code ?? 'pcs' }}
                </span>
                <span class="text-[12px] text-black/40 dark:text-white/40">Satuan Output</span>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. BOM ITEMS TABLE (Desktop & Tablet View)            -->
    <!-- ===================================================== -->
    <div class="hidden sm:block rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
        <div class="px-6 py-4 border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02] flex items-center justify-between">
            <h2 class="text-[14px] font-bold text-black dark:text-white tracking-tight">
                Komponen Bahan Terdaftar ({{ $bomHeader->items->count() }} Bahan)
            </h2>
        </div>

        <div class="overflow-x-auto scrollbar-thin">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02]">
                        <th class="py-3 px-5 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40">Komponen Bahan Baku</th>
                        <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 text-center">Jumlah Resep</th>
                        <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 text-right">Harga Efektif Bahan</th>
                        <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 text-center">Susut (Waste %)</th>
                        <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 text-right">Subtotal Biaya</th>
                        <th class="py-3 px-5 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($bomHeader->items as $item)
                        @php
                            $mat = $item->material;
                            $price = $mat?->prices->first()?->effective_cost ?? 0;
                            $itemSubtotal = $price * $item->quantity * (1 + $item->waste_percentage / 100);
                        @endphp
                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.025] transition-colors">
                            <td class="py-3.5 px-5 font-medium text-black dark:text-white">
                                <div class="font-semibold text-[13.5px]">{{ $mat?->name ?? 'Sub-BOM' }}</div>
                                <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums">
                                    {{ $mat?->sku ?? '-' }}
                                </div>
                                @if ($item->notes)
                                    <div class="text-[11px] text-black/40 dark:text-white/40 mt-0.5 italic">
                                        {{ $item->notes }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center tabular-nums font-semibold text-black dark:text-white">
                                {{ $item->quantity }} {{ $item->unit?->code ?? $mat?->unit?->code }}
                            </td>
                            <td class="py-3.5 px-4 text-right tabular-nums text-black/70 dark:text-white/70">
                                {{ $business->currency_symbol }} {{ number_format((float) $price, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if ($item->waste_percentage > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A] tabular-nums">
                                        +{{ $item->waste_percentage }}%
                                    </span>
                                @else
                                    <span class="text-black/40 dark:text-white/40 tabular-nums text-[12px]">0%</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right tabular-nums font-bold text-[#34C759] dark:text-[#30D158]">
                                {{ $business->currency_symbol }} {{ number_format((float) $itemSubtotal, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                @if (\App\Support\Context::hasPermission('costing.manage'))
                                    <button type="button"
                                        @click="openDelete('{{ $item->id }}', '{{ addslashes($mat?->name ?? 'Komponen Resep') }}')"
                                        class="h-8 px-3 rounded-[8px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/8 transition-colors inline-flex items-center">
                                        Hapus
                                    </button>
                                    <form id="form-delete-{{ $item->id }}" method="POST"
                                        action="{{ route('bom.items.destroy', $item->id) }}" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-14 text-center text-black/40 dark:text-white/40">
                                Resep belum memiliki komponen bahan. Klik tombol "+ Tambah Bahan" di toolbar atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 4. BOM ITEMS MOBILE LIST CARDS (< sm)                 -->
    <!-- ===================================================== -->
    <div class="sm:hidden space-y-3">
        @forelse($bomHeader->items as $item)
            @php
                $mat = $item->material;
                $price = $mat?->prices->first()?->effective_cost ?? 0;
                $itemSubtotal = $price * $item->quantity * (1 + $item->waste_percentage / 100);
            @endphp
            <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] space-y-3 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <h3 class="text-[15px] font-semibold text-black dark:text-white truncate">
                            {{ $mat?->name ?? 'Sub-BOM' }}
                        </h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5 tabular-nums">
                            Takaran: <strong class="text-black dark:text-white">{{ $item->quantity }} {{ $item->unit?->code ?? $mat?->unit?->code }}</strong>
                        </p>
                    </div>

                    @if (\App\Support\Context::hasPermission('costing.manage'))
                        <button type="button"
                            @click="openDelete('{{ $item->id }}', '{{ addslashes($mat?->name ?? 'Komponen Resep') }}')"
                            class="h-8 px-3 rounded-[8px] text-[12px] font-medium text-[#FF3B30] bg-[#FF3B30]/10 flex items-center shrink-0">
                            Hapus
                        </button>
                    @endif
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-black/[0.04] dark:border-white/[0.06] text-[12.5px]">
                    <div class="text-black/50 dark:text-white/50">
                        Harga: {{ $business->currency_symbol }} {{ number_format((float) $price, 0, ',', '.') }}
                        @if ($item->waste_percentage > 0)
                            <span class="text-[#FF3B30] font-semibold ml-1">(+{{ $item->waste_percentage }}% susut)</span>
                        @endif
                    </div>
                    <div class="font-bold text-[#34C759] dark:text-[#30D158] tabular-nums text-[13.5px]">
                        {{ $business->currency_symbol }} {{ number_format((float) $itemSubtotal, 0, ',', '.') }}
                    </div>
                </div>

                @if ($item->notes)
                    <p class="text-[11px] text-black/40 dark:text-white/40 italic pt-1 border-t border-black/[0.03] dark:border-white/[0.05]">
                        Catatan: {{ $item->notes }}
                    </p>
                @endif
            </div>
        @empty
            <div class="p-8 text-center text-black/40 dark:text-white/40 text-[13px] bg-white dark:bg-[#1C1C1E] rounded-[16px] border border-black/[0.06] dark:border-white/[0.08]">
                Resep belum memiliki bahan baku. Klik tombol "+ Tambah Bahan" di atas.
            </div>
        @endforelse
    </div>

    <!-- ===================================================== -->
    <!-- 5. MODAL: TAMBAH BAHAN KE BOM (Bento Modal)           -->
    <!-- ===================================================== -->
    @if (\App\Support\Context::hasPermission('costing.manage'))
        <div x-show="showAddItemModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-0 sm:p-4 bg-black/30 backdrop-blur-md"
            @keydown.escape.window="showAddItemModal = false">
            <div class="w-full max-w-full sm:max-w-xl md:max-w-2xl inset-x-0 bottom-0 sm:inset-auto sm:my-auto rounded-t-[28px] sm:rounded-[24px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_25px_60px_rgba(0,0,0,0.3)] max-h-[94vh] sm:max-h-[90vh] flex flex-col overflow-hidden"
                @click.outside="showAddItemModal = false">

                <!-- Mobile Grab Bar -->
                <div class="w-10 h-1.5 rounded-full bg-black/20 dark:bg-white/20 mx-auto my-2.5 sm:hidden shrink-0"></div>

                <!-- Sticky Header -->
                <div class="sticky top-0 z-20 backdrop-blur-xl bg-white/95 dark:bg-[#1C1C1E]/95 border-b border-black/[0.06] dark:border-white/[0.08] px-6 sm:px-8 py-4 sm:py-5 flex items-center justify-between gap-4 shrink-0">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white tracking-tight leading-snug truncate">
                                Tambah Bahan ke Formula Resep
                            </h3>
                            <p class="text-[12px] sm:text-[13px] text-black/50 dark:text-white/50 truncate">
                                Produk: {{ $product->name }} (Batch {{ $bomHeader->output_quantity }} {{ $product->outputUnit?->name ?? 'pcs' }})
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="showAddItemModal = false"
                        class="w-8 sm:w-9 h-8 sm:h-9 rounded-full bg-black/[0.05] dark:bg-white/[0.1] hover:bg-black/[0.1] dark:hover:bg-white/[0.15] text-black/60 dark:text-white/60 flex items-center justify-center active:scale-95 transition-all shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Form Body -->
                <form method="POST" action="{{ route('bom.items.store', $bomHeader->id) }}"
                    class="flex-1 overflow-y-auto p-6 sm:p-8 overscroll-contain sidebar-scroll flex flex-col justify-between space-y-5">
                    @csrf

                    <div class="space-y-4">
                        <div>
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1.5 text-[13px]">
                                Pilih Bahan Baku <span class="text-[#FF3B30]">*</span>
                            </label>
                            <select name="material_id" required
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                <option value="">-- Pilih Bahan Baku dari Gudang --</option>
                                @foreach ($materials as $m)
                                    <option value="{{ $m->id }}">
                                        {{ $m->name }} ({{ $m->unit?->code }} - {{ $business->currency_symbol }} {{ number_format((float) ($m->prices->first()?->effective_cost ?? 0), 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-medium text-black/70 dark:text-white/70 mb-1.5 text-[13px]">
                                    Jumlah Pemakaian <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="number" name="quantity" step="any" min="0.0001" required placeholder="0.5"
                                    class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white tabular-nums font-semibold focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>
                            <div>
                                <label class="block font-medium text-black/70 dark:text-white/70 mb-1.5 text-[13px]">
                                    Satuan Takar <span class="text-[#FF3B30]">*</span>
                                </label>
                                <select name="unit_id" required
                                    class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    @foreach ($units as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">
                                Susut Proses / Waste (%)
                            </label>
                            <div class="flex items-center gap-2">
                                <input type="number" name="waste_percentage" value="0" min="0" max="100" step="0.5"
                                    class="w-24 h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3 text-[16px] sm:text-[14px] text-black dark:text-white tabular-nums font-bold focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                <span class="text-[13px] text-black/60 dark:text-white/60 font-medium">% tambahan susut</span>
                            </div>
                            <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">
                                Persentase bahan terbuang saat pengolahan (misal: kulit terkelupas atau sisa adonan pada wadah).
                            </p>
                        </div>

                        <div>
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">
                                Catatan Khusus (Opsional)
                            </label>
                            <input type="text" name="notes" placeholder="Misal: dimasukkan pada menit terakhir pemanggangan"
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                    </div>

                    <!-- Sticky Footer -->
                    <div class="sticky bottom-0 -mx-6 sm:-mx-8 -mb-6 sm:-mb-8 mt-6 backdrop-blur-xl bg-white/95 dark:bg-[#1C1C1E]/95 border-t border-black/[0.06] dark:border-white/[0.08] px-6 sm:px-8 py-4 flex items-center justify-end gap-3 shrink-0">
                        <button type="button" @click="showAddItemModal = false"
                            class="h-11 px-5 rounded-[12px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] transition active:scale-[0.98]">
                            Batal
                        </button>
                        <button type="submit"
                            class="h-11 px-6 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition shadow-sm shadow-[#007AFF]/25">
                            Tambah ke Resep
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- ===================================================== -->
    <!-- 6. APPLE ALERT DIALOG (Hapus Komponen Resep)          -->
    <!-- ===================================================== -->
    @if (\App\Support\Context::hasPermission('costing.manage'))
        <div x-show="deleteModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/30 backdrop-blur-sm">
            <div class="w-full max-w-[320px] rounded-[20px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-2xl overflow-hidden text-center shadow-2xl border border-black/[0.08] dark:border-white/[0.1]"
                @click.outside="closeDelete()">
                <div class="px-5 pt-6 pb-4">
                    <div class="w-10 h-10 rounded-full bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center mx-auto mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <h4 class="text-[17px] font-bold text-black dark:text-white tracking-tight">Hapus Bahan Resep?</h4>
                    <p class="text-[13px] text-black/60 dark:text-white/60 mt-1.5 leading-snug">
                        <span x-text="deleteTarget.name" class="font-semibold text-black dark:text-white"></span> akan dihapus dari formula resep produk ini.
                    </p>
                    <div class="mt-3 p-2.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] text-[11px] text-black/50 dark:text-white/50 text-left flex items-start gap-1.5">
                        <svg class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                        <span>Tenang: Bahan hanya dihapus dari formula resep ini. Data master stok dan harga bahan tetap aman tersimpan.</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 border-t border-black/[0.08] dark:border-white/[0.1] text-[15px] font-medium">
                    <button type="button" @click="closeDelete()"
                        class="py-3 text-[#007AFF] border-r border-black/[0.08] dark:border-white/[0.1] active:bg-black/5 dark:active:bg-white/5 transition-colors">
                        Batal
                    </button>
                    <button type="button" @click="submitDelete()"
                        class="py-3 text-[#FF3B30] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
