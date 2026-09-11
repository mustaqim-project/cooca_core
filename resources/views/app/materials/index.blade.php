@extends('layouts.app', [
    'title' => 'Bahan Baku & Harga',
    'headerTitle' => 'Katalog Bahan Baku & Pemasok',
    'headerSubtitle' => 'Kelola harga akuisisi efektif, rendemen (yield), susut (waste), dan riwayat harga bahan'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
    showAddModal: false,
    showEditModal: false,
    showPriceModal: false,
    showAddSupplierModal: false,
    showAddCategoryModal: false,
    showAddUnitModal: false,
    selectedMaterial: null,
    editMaterial: { id: '', slug: '', name: '', sku: '', category_id: '', supplier_id: '', unit_id: '' },
    deleteModalOpen: false,
    deleteTarget: { id: '', name: '' },
    init() {
        window.addEventListener('pageshow', () => {
            this.showAddModal = false;
            this.showEditModal = false;
            this.showPriceModal = false;
            this.showAddSupplierModal = false;
            this.showAddCategoryModal = false;
            this.showAddUnitModal = false;
            this.selectedMaterial = null;
            this.deleteModalOpen = false;
        });
    },

    openDelete(id, name) {
        this.deleteTarget = { id, name };
        this.deleteModalOpen = true;
    },
    closeDelete() {
        this.deleteModalOpen = false;
        this.deleteTarget = { id: '', name: '' };
    },
    submitDelete() {
        if (this.deleteTarget.id) {
            const form = document.getElementById('form-delete-' + this.deleteTarget.id);
            if (form) form.submit();
        }
    },
    openPriceModal(mat) {
        this.selectedMaterial = mat;
        this.showPriceModal = true;
    },
    openEditModal(mat) {
        this.editMaterial = {
            id: mat.id,
            slug: mat.slug,
            name: mat.name,
            sku: mat.code || mat.sku || '',
            category_id: mat.category_id || '',
            supplier_id: mat.supplier_id || '',
            unit_id: mat.unit_id
        };
        this.showEditModal = true;
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
                <span class="text-black/70 dark:text-white/70 font-medium">Inventori</span>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Bahan Baku &amp; Pemasok</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Katalog Bahan Baku &amp; Pemasok</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Kelola harga beli efektif, susut/rendemen (yield), dan pemasok bahan baku</p>
        </div>

        <!-- Toolbar Actions -->
        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
            @if(\App\Support\Context::hasPermission('materials.create'))
            <a href="{{ route('import.index', ['tab' => 'materials']) }}"
               class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5"
               title="Import data bahan baku massal dari file Excel / CSV">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                <span>Import</span>
            </a>

            <button @click="showAddModal = true"
                    class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                <span>Tambah Bahan Baku</span>
            </button>
            @endif
        </div>
    </header>

    <!-- ===================================================== -->
    <!-- 2. KPI SUMMARY (Flat Neutral Material, No Harsh Shadow)-->
    <!-- ===================================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Tile 1: Total Bahan -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Bahan Baku</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-black dark:text-white">{{ $materials->total() }}</span>
                <span class="text-[11px] text-black/40 dark:text-white/40">Item</span>
            </div>
        </div>

        <!-- Tile 2: Total Kategori -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Kategori Bahan</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-[#007AFF]">{{ $categories->count() }}</span>
                <span class="text-[11px] text-black/40 dark:text-white/40">Grup</span>
            </div>
        </div>

        <!-- Tile 3: Pemasok Terhubung -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Pemasok Terdaftar</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">{{ $suppliers->count() }}</span>
                <span class="text-[11px] font-medium text-[#34C759] dark:text-[#30D158]">Vendor Aktif</span>
            </div>
        </div>

        <!-- Tile 4: Akurasi Biaya & Yield -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Manajemen Susut</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">Yield &amp; Waste</span>
                <span class="text-[11px] text-[#FF9500] dark:text-[#FF9F0A]">Otomatis</span>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. SEARCH & CATEGORY CONTROLS (macOS Capsule Bar)      -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 sm:p-4 shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
        <form method="GET" action="{{ route('materials.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <!-- Search Field -->
            <div class="relative flex-1 max-w-md">
                <svg class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama bahan atau SKU..."
                       class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-9 pr-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>

            <!-- Category Filter -->
            <div class="flex items-center gap-2">
                <select name="category_id" onchange="this.form.submit()"
                        class="h-9 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>

                @if(request('search') || request('category_id'))
                <a href="{{ route('materials.index') }}" class="h-9 px-3 rounded-[10px] text-[12px] font-medium text-black/60 dark:text-white/60 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] flex items-center">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- ===================================================== -->
    <!-- 4. DENSE DATA TABLE (DESKTOP & TABLET)                 -->
    <!-- ===================================================== -->
    <div class="hidden sm:block rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02]">
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">Nama Bahan &amp; SKU</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">Kategori</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">Satuan Beli</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right whitespace-nowrap">Harga Beli Terakhir</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center whitespace-nowrap">Yield / Waste</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right whitespace-nowrap">Biaya Efektif / Unit</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($materials as $mat)
                    @php
                        $latestPrice = $mat->prices->first();
                    @endphp
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="py-3 px-4 font-medium text-black dark:text-white">
                            <div class="font-semibold text-[13px]">{{ $mat->name }}</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums">
                                {{ $mat->sku ?? 'No SKU' }} · Supplier: <span class="font-medium text-black/60 dark:text-white/60">{{ $mat->supplier?->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-black/60 dark:text-white/60">
                            {{ $mat->category?->name ?? 'Umum' }}
                        </td>
                        <td class="py-3 px-4 text-black/70 dark:text-white/70 tabular-nums">
                            {{ $mat->unit?->name ?? 'pcs' }} <span class="text-[11px] text-black/40 dark:text-white/40">({{ $mat->unit?->code }})</span>
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums font-semibold text-black dark:text-white">
                            {{ $business->currency_symbol }} {{ number_format((float) ($latestPrice?->purchase_price ?? 0), 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] tabular-nums">
                                {{ $mat->yield_percentage }}% Yield
                            </span>
                            @if($mat->waste_percentage > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A] tabular-nums ml-1">
                                {{ $mat->waste_percentage }}% Waste
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums font-bold text-[#34C759] dark:text-[#30D158]">
                            {{ $business->currency_symbol }} {{ number_format((float) ($latestPrice?->effective_cost ?? 0), 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                @if(\App\Support\Context::hasPermission('materials.edit'))
                                <button type="button" @click="openPriceModal({{ Js::from($mat) }})"
                                        class="h-7 px-2 rounded-[6px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition-colors">
                                    Update Harga
                                </button>
                                <button type="button" @click="openEditModal({{ Js::from($mat) }})"
                                        class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition-colors" title="Edit Spesifikasi Bahan">
                                    Edit
                                </button>
                                @endif
                                @if(\App\Support\Context::hasPermission('materials.delete'))
                                <button type="button" @click="openDelete('{{ $mat->id }}', {{ Js::from($mat->name) }})"
                                        class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/8 transition-colors" title="Hapus Bahan Baku">
                                    Hapus
                                </button>
                                <form id="form-delete-{{ $mat->id }}" method="POST" action="{{ route('materials.destroy', $mat->id) }}" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-black/45 dark:text-white/45">
                            Belum ada data bahan baku ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($materials->hasPages())
        <div class="px-4 py-3 border-t border-black/5 dark:border-white/10 flex items-center justify-between text-[13px] text-black/60 dark:text-white/60">
            {{ $materials->links() }}
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 5. MOBILE GROUPED INSET LIST (Standar iOS List View)   -->
    <!-- ===================================================== -->
    <div class="sm:hidden rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06] shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
        @forelse($materials as $mat)
        @php
            $latestPrice = $mat->prices->first();
        @endphp
        <div class="p-3.5 flex items-center justify-between gap-3 active:bg-black/[0.02] dark:active:bg-white/[0.03] transition-colors">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <p class="text-[14px] font-semibold text-black dark:text-white truncate">{{ $mat->name }}</p>
                    <span class="text-[11px] text-black/45 dark:text-white/45 tabular-nums">({{ $mat->unit?->code }})</span>
                </div>
                <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5 tabular-nums">
                    Efektif: <strong class="text-[#34C759] dark:text-[#30D158]">{{ $business->currency_symbol }} {{ number_format((float) ($latestPrice?->effective_cost ?? 0), 0, ',', '.') }}</strong>
                    · Beli: {{ $business->currency_symbol }} {{ number_format((float) ($latestPrice?->purchase_price ?? 0), 0, ',', '.') }}
                </p>
                <div class="mt-1 flex items-center gap-1.5">
                    <span class="text-[10px] font-semibold text-[#248A3D] dark:text-[#30D158]">{{ $mat->yield_percentage }}% Yield</span>
                    @if($mat->waste_percentage > 0)
                        <span class="text-[10px] font-semibold text-[#C41E17] dark:text-[#FF453A]">· {{ $mat->waste_percentage }}% Waste</span>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-1 shrink-0">
                @if(\App\Support\Context::hasPermission('materials.edit'))
                <button type="button" @click="openPriceModal({{ Js::from($mat) }})" class="h-8 px-2.5 rounded-[8px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 flex items-center">
                    Harga
                </button>
                <button type="button" @click="openEditModal({{ Js::from($mat) }})" class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-black/70 dark:text-white/70 bg-black/[0.06] dark:bg-white/[0.08] flex items-center">
                    Edit
                </button>
                @if(\App\Support\Context::hasPermission('materials.delete'))
                <button type="button" @click="openDelete('{{ $mat->id }}', {{ Js::from($mat->name) }})" class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-[#FF3B30] bg-[#FF3B30]/10 flex items-center">
                    Hapus
                </button>
                @endif
                @endif
            </div>
        </div>
        @empty
        <div class="p-8 text-center text-black/40 dark:text-white/40 text-[13px]">
            Belum ada data bahan baku ditemukan.
        </div>
        @endforelse

        @if($materials->hasPages())
        <div class="p-3 border-t border-black/5 dark:border-white/10">
            {{ $materials->links() }}
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 6. MODAL: TAMBAH BAHAN BAKU (Apple Sheet)             -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('materials.create'))
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]">
        <div class="w-full max-w-lg rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/10 dark:border-white/10 p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] max-h-[90vh] overflow-y-auto" @click.outside="showAddModal = false">
            <div class="flex items-center justify-between border-b border-black/10 dark:border-white/10 pb-3">
                <h3 class="text-[16px] font-semibold text-black dark:text-white">Tambah Bahan Baku Baru</h3>
                <button @click="showAddModal = false" class="p-1 rounded-[6px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">✕</button>
            </div>

            <form method="POST" action="{{ route('materials.store') }}" class="space-y-3.5 text-[13px]">
                @csrf

                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Nama Bahan Baku <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Tepung Terigu Protein Tinggi"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="font-medium text-black/70 dark:text-white/70">Satuan Beli <span class="text-[#FF3B30]">*</span></label>
                            <button type="button" @click="showAddUnitModal = true" class="text-[11px] text-[#007AFF] hover:underline">+ Satuan</button>
                        </div>
                        <select name="unit_id" required class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            @foreach($units as $u)
                                <option value="{{ $u->id }}" {{ $u->code === 'kg' ? 'selected' : '' }}>{{ $u->name }} ({{ $u->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="font-medium text-black/70 dark:text-white/70">Kategori</label>
                            <button type="button" @click="showAddCategoryModal = true" class="text-[11px] text-[#007AFF] hover:underline">+ Kategori</button>
                        </div>
                        <select name="category_id" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <option value="">-- Tanpa Kategori --</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="font-medium text-black/70 dark:text-white/70">Supplier / Vendor</label>
                            <button type="button" @click="showAddSupplierModal = true" class="text-[11px] text-[#007AFF] hover:underline">+ Supplier</button>
                        </div>
                        <select name="supplier_id" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <option value="">-- Tanpa Supplier --</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">SKU / Kode Bahan</label>
                        <input type="text" name="sku" placeholder="MAT-001" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>

                <!-- Yield & Waste -->
                <div class="grid grid-cols-2 gap-3 p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Rendemen (Yield %)</label>
                        <input type="number" name="yield_percentage" value="100" min="1" max="500" required
                               class="w-full h-9 px-3 bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[8px] text-black dark:text-white tabular-nums font-semibold focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Susut (Waste %)</label>
                        <input type="number" name="waste_percentage" value="0" min="0" max="100" required
                               class="w-full h-9 px-3 bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[8px] text-black dark:text-white tabular-nums font-semibold focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                </div>

                <!-- Initial Price -->
                <div class="p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2">
                    <span class="text-[12px] font-semibold text-black dark:text-white block">Harga Akuisisi Awal</span>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block text-[11px] text-black/60 dark:text-white/60 mb-1">Harga Beli</label>
                            <input type="number" name="purchase_price" required min="1" placeholder="50000"
                                   class="w-full h-9 px-2.5 bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[8px] text-black dark:text-white tabular-nums text-[13px] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                        <div>
                            <label class="block text-[11px] text-black/60 dark:text-white/60 mb-1">Ongkir</label>
                            <input type="number" name="shipping_cost" value="0" min="0"
                                   class="w-full h-9 px-2.5 bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[8px] text-black dark:text-white tabular-nums text-[13px] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                        <div>
                            <label class="block text-[11px] text-black/60 dark:text-white/60 mb-1">Diskon</label>
                            <input type="number" name="discount_amount" value="0" min="0"
                                   class="w-full h-9 px-2.5 bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[8px] text-black dark:text-white tabular-nums text-[13px] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                    </div>
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-black/10 dark:border-white/10">
                    <button type="button" @click="showAddModal = false" class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] transition">Batal</button>
                    <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition shadow-[0_1px_2px_rgba(0,122,255,0.25)]">Simpan Bahan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 7. MODAL: UPDATE HARGA BAHAN (Apple Sheet)            -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('materials.edit'))
    <div x-show="showPriceModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]">
        <div class="w-full max-w-md rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/10 dark:border-white/10 p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)]" @click.outside="showPriceModal = false">
            <div class="flex items-center justify-between border-b border-black/10 dark:border-white/10 pb-3">
                <div>
                    <h3 class="text-[16px] font-semibold text-black dark:text-white">Update Harga Bahan</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50" x-text="selectedMaterial ? selectedMaterial.name : ''"></p>
                </div>
                <button @click="showPriceModal = false" class="p-1 rounded-[6px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">✕</button>
            </div>

            <template x-if="selectedMaterial">
                <form :action="'/materials/' + selectedMaterial.slug + '/prices'" method="POST" class="space-y-3.5 text-[13px]">
                    @csrf

                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Harga Beli Baru (Rp) <span class="text-[#FF3B30]">*</span></label>
                        <input type="number" name="purchase_price" required min="1" step="100"
                               class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Ongkos Kirim</label>
                            <input type="number" name="shipping_cost" value="0" min="0"
                                   class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                        <div>
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Diskon Pembelian</label>
                            <input type="number" name="discount_amount" value="0" min="0"
                                   class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                    </div>

                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Catatan Penyesuaian</label>
                        <input type="text" name="notes" placeholder="Penyesuaian harga supplier"
                               class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div class="pt-3 flex justify-end gap-2 border-t border-black/10 dark:border-white/10">
                        <button type="button" @click="showPriceModal = false" class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] transition">Batal</button>
                        <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition shadow-[0_1px_2px_rgba(0,122,255,0.25)]">Simpan Riwayat</button>
                    </div>
                </form>
            </template>
        </div>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 8. MODAL: EDIT SPESIFIKASI BAHAN (Apple Sheet)        -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('materials.edit'))
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]">
        <div class="w-full max-w-lg rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/10 dark:border-white/10 p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] max-h-[90vh] overflow-y-auto" @click.outside="showEditModal = false">
            <div class="flex items-center justify-between border-b border-black/10 dark:border-white/10 pb-3">
                <h3 class="text-[16px] font-semibold text-black dark:text-white">Edit Spesifikasi Bahan Baku</h3>
                <button @click="showEditModal = false" class="p-1 rounded-[6px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">✕</button>
            </div>

            <form :action="'/materials/' + editMaterial.slug" method="POST" class="space-y-4 text-[13px]">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Nama Bahan Baku <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" name="name" x-model="editMaterial.name" required class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Kode SKU / Barcode</label>
                        <input type="text" name="sku" x-model="editMaterial.sku" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Kategori Bahan</label>
                        <select name="category_id" x-model="editMaterial.category_id" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <option value="">-- Tanpa Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Satuan Beli / Dasar <span class="text-[#FF3B30]">*</span></label>
                        <select name="unit_id" x-model="editMaterial.unit_id" required class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Pemasok Utama</label>
                        <select name="supplier_id" x-model="editMaterial.supplier_id" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <option value="">-- Tanpa Pemasok --</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-black/10 dark:border-white/10">
                    <button type="button" @click="showEditModal = false" class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] transition">Batal</button>
                    <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition shadow-[0_1px_2px_rgba(0,122,255,0.25)]">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 9. SUB-MODALS: SUPPLIER, KATEGORI & SATUAN            -->
    <!-- ===================================================== -->
    <div x-show="showAddSupplierModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]">
        <div class="w-full max-w-sm rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)]" @click.outside="showAddSupplierModal = false">
            <div class="flex items-center justify-between border-b border-black/10 dark:border-white/10 pb-3">
                <h3 class="text-[15px] font-semibold text-black dark:text-white">Tambah Supplier Baru</h3>
                <button @click="showAddSupplierModal = false" class="text-black/40 dark:text-white/40">✕</button>
            </div>
            <form method="POST" action="{{ route('suppliers.store') }}" class="space-y-3 text-[13px]">
                @csrf
                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Nama Supplier / Vendor <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: PT Bogasari" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">PIC</label>
                        <input type="text" name="contact_person" placeholder="Pak Budi" class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-3 text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">No. HP/WA</label>
                        <input type="text" name="phone" placeholder="08123456789" class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-3 text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                </div>
                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Alamat</label>
                    <input type="text" name="address" placeholder="Kota / Alamat gudang" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="showAddSupplierModal = false" class="h-8 px-3 rounded-[8px] text-[12px] bg-black/[0.06] dark:bg-white/[0.08] text-black/80 dark:text-white/80">Tutup</button>
                    <button type="submit" class="h-8 px-3.5 rounded-[8px] text-[12px] font-semibold bg-[#007AFF] text-white">Simpan Supplier</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showAddCategoryModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]">
        <div class="w-full max-w-sm rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)]" @click.outside="showAddCategoryModal = false">
            <div class="flex items-center justify-between border-b border-black/10 dark:border-white/10 pb-3">
                <h3 class="text-[15px] font-semibold text-black dark:text-white">Tambah Kategori Bahan</h3>
                <button @click="showAddCategoryModal = false" class="text-black/40 dark:text-white/40">✕</button>
            </div>
            <form method="POST" action="{{ route('material-categories.store') }}" class="space-y-3 text-[13px]">
                @csrf
                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Nama Kategori Bahan <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Tepung &amp; Biji / Bumbu" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Deskripsi</label>
                    <input type="text" name="description" placeholder="Catatan kategori..." class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="showAddCategoryModal = false" class="h-8 px-3 rounded-[8px] text-[12px] bg-black/[0.06] dark:bg-white/[0.08] text-black/80 dark:text-white/80">Tutup</button>
                    <button type="submit" class="h-8 px-3.5 rounded-[8px] text-[12px] font-semibold bg-[#007AFF] text-white">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showAddUnitModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]">
        <div class="w-full max-w-sm rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)]" @click.outside="showAddUnitModal = false">
            <div class="flex items-center justify-between border-b border-black/10 dark:border-white/10 pb-3">
                <h3 class="text-[15px] font-semibold text-black dark:text-white">Tambah Satuan Beli Baru</h3>
                <button @click="showAddUnitModal = false" class="text-black/40 dark:text-white/40">✕</button>
            </div>
            <form method="POST" action="{{ route('units.store') }}" class="space-y-3.5 text-[13px]">
                @csrf
                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Kode Simbol <span class="text-[#FF3B30]">*</span></label>
                        <input type="text" name="code" required placeholder="sak / roll" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Kategori <span class="text-[#FF3B30]">*</span></label>
                        <select name="category" required class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-2.5 text-black dark:text-white text-[12px] focus:ring-2 focus:ring-[#007AFF]/50">
                            <option value="weight">Berat (kg, sak)</option>
                            <option value="volume">Volume (l, jerigen)</option>
                            <option value="quantity">Kuantitas (pcs, pack)</option>
                            <option value="custom">Lainnya</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Nama Satuan <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Sak 25kg / Jerigen 5L" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="showAddUnitModal = false" class="h-8 px-3 rounded-[8px] text-[12px] bg-black/[0.06] dark:bg-white/[0.08] text-black/80 dark:text-white/80">Tutup</button>
                    <button type="submit" class="h-8 px-3.5 rounded-[8px] text-[12px] font-semibold bg-[#007AFF] text-white">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 10. APPLE ALERT DIALOG (Hapus Bahan Baku)              -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('materials.delete'))
    <div x-show="deleteModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px]">
        <div class="w-[290px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
            @click.away="closeDelete()">
            <div class="px-4 pt-5 pb-4">
                <p class="text-[17px] font-semibold text-black dark:text-white">Hapus Bahan Baku?</p>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                    <span x-text="deleteTarget.name" class="font-medium text-black dark:text-white"></span> akan dihapus dari basis data inventori bahan. Tindakan ini tidak dapat dibatalkan.
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
