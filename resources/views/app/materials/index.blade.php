@extends('layouts.app', [
    'title' => 'Bahan Baku & Harga',
    'headerTitle' => 'Katalog Bahan Baku & Pemasok',
    'headerSubtitle' => 'Kelola harga akuisisi efektif, rendemen (yield), susut (waste), dan riwayat harga bahan'
])

@section('content')
<div class="w-full max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-10" x-data="{
    showAddModal: false,
    showEditModal: false,
    showPriceModal: false,
    showAddSupplierModal: false,
    showAddCategoryModal: false,
    showAddUnitModal: false,
    
    // Dynamic master data arrays for zero-reload injection
    categories: {{ Js::from($categories) }},
    units: {{ Js::from($units) }},
    suppliers: {{ Js::from($suppliers) }},

    // Form inputs state
    addForm: {
        name: '',
        sku: '',
        unit_id: '{{ $units->firstWhere('code', 'kg')?->id ?? $units->first()?->id ?? '' }}',
        category_id: '',
        supplier_id: '',
        yield_percentage: 100,
        waste_percentage: 0,
        purchase_price: '',
        shipping_cost: 0,
        discount_amount: 0
    },

    // Quick-add submodal form state
    quickCat: { name: '', description: '', isSubmitting: false, error: '' },
    quickUnit: { code: '', name: '', category: 'weight', isSubmitting: false, error: '' },
    quickSup: { name: '', contact_person: '', phone: '', address: '', isSubmitting: false, error: '' },

    // Edit and Price Modal State
    selectedMaterial: null,
    editMaterial: { id: '', slug: '', name: '', sku: '', category_id: '', supplier_id: '', unit_id: '' },
    
    // Delete Alert State
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
            this.deleteModalOpen = false;
        });
    },

    get effectiveCostPreview() {
        const price = parseFloat(this.addForm.purchase_price) || 0;
        const shipping = parseFloat(this.addForm.shipping_cost) || 0;
        const discount = parseFloat(this.addForm.discount_amount) || 0;
        const yieldPct = Math.max(1, parseFloat(this.addForm.yield_percentage) || 100);
        const netBase = Math.max(0, price + shipping - discount);
        return Math.round(netBase / (yieldPct / 100));
    },

    formatRupiah(num) {
        return new Intl.NumberFormat('id-ID').format(num || 0);
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
    },

    async submitQuickCategory() {
        if (!this.quickCat.name.trim()) return;
        this.quickCat.isSubmitting = true;
        this.quickCat.error = '';

        try {
            const res = await fetch('{{ route('material-categories.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    name: this.quickCat.name.trim(),
                    description: this.quickCat.description.trim() || null
                })
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal menyimpan kategori');

            const newCat = data.category || data.data || data;
            this.categories.push(newCat);
            this.addForm.category_id = newCat.id;
            if (this.showEditModal) this.editMaterial.category_id = newCat.id;
            
            this.quickCat = { name: '', description: '', isSubmitting: false, error: '' };
            this.showAddCategoryModal = false;
        } catch (e) {
            this.quickCat.error = e.message;
            this.quickCat.isSubmitting = false;
        }
    },

    async submitQuickUnit() {
        if (!this.quickUnit.code.trim() || !this.quickUnit.name.trim()) return;
        this.quickUnit.isSubmitting = true;
        this.quickUnit.error = '';

        try {
            const res = await fetch('{{ route('units.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    code: this.quickUnit.code.trim(),
                    name: this.quickUnit.name.trim(),
                    category: this.quickUnit.category
                })
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal menyimpan satuan');

            const newUnit = data.unit || data.data || data;
            this.units.push(newUnit);
            this.addForm.unit_id = newUnit.id;
            if (this.showEditModal) this.editMaterial.unit_id = newUnit.id;

            this.quickUnit = { code: '', name: '', category: 'weight', isSubmitting: false, error: '' };
            this.showAddUnitModal = false;
        } catch (e) {
            this.quickUnit.error = e.message;
            this.quickUnit.isSubmitting = false;
        }
    },

    async submitQuickSupplier() {
        if (!this.quickSup.name.trim()) return;
        this.quickSup.isSubmitting = true;
        this.quickSup.error = '';

        try {
            const res = await fetch('{{ route('suppliers.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    name: this.quickSup.name.trim(),
                    contact_person: this.quickSup.contact_person.trim() || null,
                    phone: this.quickSup.phone.trim() || null,
                    address: this.quickSup.address.trim() || null
                })
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal menyimpan supplier');

            const newSup = data.supplier || data.data || data;
            this.suppliers.push(newSup);
            this.addForm.supplier_id = newSup.id;
            if (this.showEditModal) this.editMaterial.supplier_id = newSup.id;

            this.quickSup = { name: '', contact_person: '', phone: '', address: '', isSubmitting: false, error: '' };
            this.showAddSupplierModal = false;
        } catch (e) {
            this.quickSup.error = e.message;
            this.quickSup.isSubmitting = false;
        }
    }
}">

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
    <!-- ===================================================== -->
    <header class="rounded-[20px] backdrop-blur-xl bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
        <div>
            <!-- Breadcrumb minimal -->
            <nav class="flex items-center gap-1.5 text-[11px] font-medium text-[#8E8E93] dark:text-[#98989D] mb-1 uppercase tracking-wider">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <svg class="w-3 h-3 text-black/30 dark:text-white/30" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <span>Inventori</span>
                <svg class="w-3 h-3 text-black/30 dark:text-white/30" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <span class="text-[#1C1C1E] dark:text-[#F2F2F7] font-semibold">Bahan Baku &amp; Pemasok</span>
            </nav>
            <h1 class="text-[20px] sm:text-[24px] font-bold text-[#1C1C1E] dark:text-[#F2F2F7] tracking-tight">Katalog Bahan Baku &amp; Pemasok</h1>
            <p class="text-[13px] text-[#3C3C43]/70 dark:text-[#EBEBF5]/70 mt-0.5">Kelola harga beli efektif, susut/rendemen (yield), dan relasi pemasok bahan baku</p>
        </div>

        <!-- Toolbar Actions -->
        <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
            @if(\App\Support\Context::hasPermission('materials.create'))
            <a href="{{ route('import.index', ['tab' => 'materials']) }}"
               class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all flex items-center justify-center gap-2"
               title="Import data bahan baku massal dari file Excel / CSV">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                <span>Import Excel</span>
            </a>

            <button type="button" @click="showAddModal = true"
                    class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all flex items-center justify-center gap-2 shadow-sm shadow-[#007AFF]/25">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                <span>Tambah Bahan Baku</span>
            </button>
            @endif
        </div>
    </header>

    <!-- ===================================================== -->
    <!-- 2. BENTO KPI METRICS SUMMARY                          -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Tile 1: Total Bahan -->
        <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-semibold text-[#8E8E93] dark:text-[#98989D] uppercase tracking-wider">Total Bahan Baku</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-[26px] font-bold tabular-nums text-[#1C1C1E] dark:text-[#F2F2F7]">{{ $materials->total() }}</span>
                <span class="text-[11px] font-medium text-[#8E8E93] dark:text-[#98989D]">Item Terdaftar</span>
            </div>
        </div>

        <!-- Tile 2: Kategori Bahan -->
        <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-semibold text-[#8E8E93] dark:text-[#98989D] uppercase tracking-wider">Kategori Bahan</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-[26px] font-bold tabular-nums text-[#5856D6]"><span x-text="categories.length">{{ $categories->count() }}</span></span>
                <span class="text-[11px] font-medium text-[#8E8E93] dark:text-[#98989D]">Klasifikasi</span>
            </div>
        </div>

        <!-- Tile 3: Pemasok Terhubung -->
        <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-semibold text-[#8E8E93] dark:text-[#98989D] uppercase tracking-wider">Pemasok Vendor</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-[26px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]"><span x-text="suppliers.length">{{ $suppliers->count() }}</span></span>
                <span class="text-[11px] font-medium text-[#34C759] dark:text-[#30D158]">Vendor Aktif</span>
            </div>
        </div>

        <!-- Tile 4: Akurasi Biaya & Yield -->
        <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-semibold text-[#8E8E93] dark:text-[#98989D] uppercase tracking-wider">Manajemen Susut</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-[22px] font-bold text-[#FF9500] dark:text-[#FF9F0A]">Yield &amp; Waste</span>
                <span class="text-[11px] font-medium text-[#8E8E93] dark:text-[#98989D]">Otomatis HPP</span>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. SEARCH & CATEGORY CONTROLS (macOS Capsule Bar)      -->
    <!-- ===================================================== -->
    <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] p-3.5 sm:p-4 shadow-xs">
        <form method="GET" action="{{ route('materials.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <!-- Search Field -->
            <div class="relative flex-1 max-w-md">
                <svg class="w-4 h-4 text-black/40 dark:text-white/40 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama bahan atau SKU..."
                       class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] pl-10 pr-3.5 text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>

            <!-- Category Filter -->
            <div class="flex items-center gap-2">
                <select name="category_id" onchange="this.form.submit()"
                        class="h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] text-[13px] font-medium text-[#1C1C1E] dark:text-[#F2F2F7] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>

                @if(request('search') || request('category_id'))
                <a href="{{ route('materials.index') }}" class="h-10 px-3.5 rounded-[12px] text-[13px] font-medium text-[#8E8E93] hover:text-[#1C1C1E] dark:hover:text-[#F2F2F7] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] transition-all flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>Reset</span>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- ===================================================== -->
    <!-- 4. DENSE DATA TABLE (DESKTOP & TABLET)                 -->
    <!-- ===================================================== -->
    <div class="hidden sm:block rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02]">
                        <th class="py-3 px-4 text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] whitespace-nowrap">Nama Bahan &amp; SKU</th>
                        <th class="py-3 px-4 text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] whitespace-nowrap">Kategori</th>
                        <th class="py-3 px-4 text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] whitespace-nowrap">Satuan Beli</th>
                        <th class="py-3 px-4 text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] text-right whitespace-nowrap">Harga Beli Terakhir</th>
                        <th class="py-3 px-4 text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] text-center whitespace-nowrap">Yield / Waste</th>
                        <th class="py-3 px-4 text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] text-right whitespace-nowrap">Biaya Efektif / Unit</th>
                        <th class="py-3 px-4 text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($materials as $mat)
                    @php
                        $latestPrice = $mat->prices->first();
                    @endphp
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="py-3.5 px-4 font-medium text-[#1C1C1E] dark:text-[#F2F2F7]">
                            <div class="font-semibold text-[13.5px]">{{ $mat->name }}</div>
                            <div class="text-[11px] text-[#8E8E93] dark:text-[#98989D] tabular-nums mt-0.5">
                                {{ $mat->sku ?? 'No SKU' }} · Supplier: <span class="font-medium text-[#1C1C1E]/80 dark:text-[#F2F2F7]/80">{{ $mat->supplier?->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-[#3C3C43]/80 dark:text-[#EBEBF5]/80">
                            {{ $mat->category?->name ?? 'Umum' }}
                        </td>
                        <td class="py-3.5 px-4 text-[#1C1C1E] dark:text-[#F2F2F7] tabular-nums font-medium">
                            {{ $mat->unit?->name ?? 'pcs' }} <span class="text-[11px] text-[#8E8E93] dark:text-[#98989D]">({{ $mat->unit?->code }})</span>
                        </td>
                        <td class="py-3.5 px-4 text-right tabular-nums font-semibold text-[#1C1C1E] dark:text-[#F2F2F7]">
                            {{ $business->currency_symbol }} {{ number_format((float) ($latestPrice?->purchase_price ?? 0), 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-[6px] text-[11px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] tabular-nums">
                                {{ $mat->yield_percentage }}% Yield
                            </span>
                            @if($mat->waste_percentage > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-[6px] text-[11px] font-semibold bg-[#FF3B30]/10 text-[#C41E17] dark:text-[#FF453A] tabular-nums ml-1">
                                {{ $mat->waste_percentage }}% Waste
                            </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right tabular-nums font-bold text-[#34C759] dark:text-[#30D158]">
                            {{ $business->currency_symbol }} {{ number_format((float) ($latestPrice?->effective_cost ?? 0), 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1.5">
                                @if(\App\Support\Context::hasPermission('materials.edit'))
                                <button type="button" @click="openPriceModal({{ Js::from($mat) }})"
                                        class="h-8 px-2.5 rounded-[8px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 transition-colors">
                                    Update Harga
                                </button>
                                <button type="button" @click="openEditModal({{ Js::from($mat) }})"
                                        class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-[#1C1C1E] dark:text-[#F2F2F7] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] transition-colors" title="Edit Spesifikasi Bahan">
                                    Edit
                                </button>
                                @endif
                                @if(\App\Support\Context::hasPermission('materials.delete'))
                                <button type="button" @click="openDelete('{{ $mat->id }}', {{ Js::from($mat->name) }})"
                                        class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/10 transition-colors" title="Hapus Bahan Baku">
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
                        <td colspan="7" class="py-12 text-center text-[#8E8E93] dark:text-[#98989D]">
                            Belum ada data bahan baku ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($materials->hasPages())
        <div class="px-4 py-3 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between text-[13px] text-[#8E8E93]">
            {{ $materials->links() }}
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 5. MOBILE GROUPED INSET LIST (Standar Apple HIG)      -->
    <!-- ===================================================== -->
    <div class="sm:hidden space-y-3">
        @forelse($materials as $mat)
        @php
            $latestPrice = $mat->prices->first();
        @endphp
        <div class="rounded-[20px] bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] p-4 shadow-xs space-y-2.5">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <h3 class="text-[15px] font-bold text-[#1C1C1E] dark:text-[#F2F2F7] leading-tight truncate">{{ $mat->name }}</h3>
                    <div class="text-[12px] text-[#8E8E93] dark:text-[#98989D] mt-0.5">
                        {{ $mat->sku ?? 'No SKU' }} · {{ $mat->category?->name ?? 'Umum' }}
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <span class="text-[15px] font-bold text-[#34C759] dark:text-[#30D158] tabular-nums block">
                        {{ $business->currency_symbol }} {{ number_format((float) ($latestPrice?->effective_cost ?? 0), 0, ',', '.') }}
                    </span>
                    <span class="text-[11px] text-[#8E8E93] dark:text-[#98989D]">/ {{ $mat->unit?->code }}</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-black/[0.04] dark:border-white/[0.06] text-[12px]">
                <div>
                    <span class="text-[#8E8E93] block text-[11px]">Harga Beli Terakhir:</span>
                    <span class="font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] tabular-nums">
                        {{ $business->currency_symbol }} {{ number_format((float) ($latestPrice?->purchase_price ?? 0), 0, ',', '.') }}
                    </span>
                </div>
                <div>
                    <span class="text-[#8E8E93] block text-[11px]">Pemasok:</span>
                    <span class="font-medium text-[#1C1C1E] dark:text-[#F2F2F7] truncate block">
                        {{ $mat->supplier?->name ?? '-' }}
                    </span>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-black/[0.04] dark:border-white/[0.06]">
                <div class="flex items-center gap-1.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-[6px] text-[10.5px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]">
                        {{ $mat->yield_percentage }}% Yield
                    </span>
                    @if($mat->waste_percentage > 0)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-[6px] text-[10.5px] font-semibold bg-[#FF3B30]/10 text-[#C41E17] dark:text-[#FF453A]">
                        {{ $mat->waste_percentage }}% Waste
                    </span>
                    @endif
                </div>

                <div class="flex items-center gap-1.5">
                    @if(\App\Support\Context::hasPermission('materials.edit'))
                    <button type="button" @click="openPriceModal({{ Js::from($mat) }})" class="h-9 px-3 rounded-[10px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 active:scale-95 transition-all">
                        Harga
                    </button>
                    <button type="button" @click="openEditModal({{ Js::from($mat) }})" class="h-9 px-3 rounded-[10px] text-[12px] font-medium text-[#1C1C1E] dark:text-[#F2F2F7] bg-black/[0.04] dark:bg-white/[0.06] active:scale-95 transition-all">
                        Edit
                    </button>
                    @endif
                    @if(\App\Support\Context::hasPermission('materials.delete'))
                    <button type="button" @click="openDelete('{{ $mat->id }}', {{ Js::from($mat->name) }})" class="h-9 px-3 rounded-[10px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/10 active:scale-95 transition-all">
                        Hapus
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 p-8 text-center text-[#8E8E93] border border-black/[0.06] dark:border-white/[0.08]">
            Belum ada data bahan baku ditemukan.
        </div>
        @endforelse

        @if($materials->hasPages())
        <div class="pt-2">
            {{ $materials->links() }}
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 6. MODAL: TAMBAH BAHAN BAKU (FULL LAYOUT XXL BENTO)   -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('materials.create'))
    <div x-show="showAddModal" x-cloak
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/40 backdrop-blur-sm"
         @keydown.escape.window="showAddModal = false">
        
        <!-- Modal Container: Apple Bottom Sheet on mobile, XXL Centered Bento Dialog on desktop -->
        <div class="w-full inset-x-0 bottom-0 rounded-t-[28px] sm:rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-[0_24px_60px_rgba(0,0,0,0.3)] max-h-[94vh] sm:max-h-[90vh] flex flex-col overflow-hidden sm:max-w-[95vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-7xl transition-all"
             @click.outside="showAddModal = false">
            
            <!-- Mobile Grab Handle Bar -->
            <div class="sm:hidden pt-2.5 pb-1 flex justify-center shrink-0">
                <div class="w-10 h-1.5 rounded-full bg-black/20 dark:bg-white/20"></div>
            </div>

            <!-- Modal Header -->
            <div class="px-5 sm:px-6 py-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between shrink-0 bg-[#F2F2F7]/50 dark:bg-white/[0.02]">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">Formulir Inventori</div>
                    <h3 class="text-[18px] sm:text-[22px] font-bold text-[#1C1C1E] dark:text-[#F2F2F7] tracking-tight">Tambah Bahan Baku Baru</h3>
                </div>
                <button type="button" @click="showAddModal = false" class="w-9 h-9 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Body: 12-Column Bento Form -->
            <form method="POST" action="{{ route('materials.store') }}" class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-6">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Kolom Kiri: 7 Kolom (Identitas Bahan & Spesifikasi) -->
                    <div class="lg:col-span-7 space-y-5">
                        <div class="rounded-[20px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/[0.04] dark:border-white/[0.06] p-4 sm:p-5 space-y-4">
                            <h4 class="text-[13px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">Identitas Bahan Baku</h4>

                            <div>
                                <label class="block text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1.5">
                                    Nama Bahan Baku <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="text" name="name" x-model="addForm.name" required
                                       placeholder="Contoh: Tepung Terigu Protein Tinggi Cakra Kembar"
                                       class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1.5">
                                        Kode SKU / Barcode
                                    </label>
                                    <input type="text" name="sku" x-model="addForm.sku"
                                           placeholder="MAT-001 (opsional)"
                                           class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7]">
                                            Satuan Beli <span class="text-[#FF3B30]">*</span>
                                        </label>
                                        <button type="button" @click="showAddUnitModal = true" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-0.5">
                                            <span>[ + ]</span> <span>Satuan Baru</span>
                                        </button>
                                    </div>
                                    <select name="unit_id" x-model="addForm.unit_id" required
                                            class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3 text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                        <template x-for="u in units" :key="u.id">
                                            <option :value="u.id" x-text="u.name + ' (' + u.code + ')'"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7]">Kategori Bahan</label>
                                        <button type="button" @click="showAddCategoryModal = true" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-0.5">
                                            <span>[ + ]</span> <span>Kategori</span>
                                        </button>
                                    </div>
                                    <select name="category_id" x-model="addForm.category_id"
                                            class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3 text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                        <option value="">-- Tanpa Kategori --</option>
                                        <template x-for="cat in categories" :key="cat.id">
                                            <option :value="cat.id" x-text="cat.name"></option>
                                        </template>
                                    </select>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7]">Supplier Utama</label>
                                        <button type="button" @click="showAddSupplierModal = true" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-0.5">
                                            <span>[ + ]</span> <span>Supplier</span>
                                        </button>
                                    </div>
                                    <select name="supplier_id" x-model="addForm.supplier_id"
                                            class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3 text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                        <option value="">-- Tanpa Supplier --</option>
                                        <template x-for="sup in suppliers" :key="sup.id">
                                            <option :value="sup.id" x-text="sup.name"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: 5 Kolom (Rendemen, Harga & Kalkulasi Efektif) -->
                    <div class="lg:col-span-5 space-y-5">
                        <!-- Rendemen (Yield) & Susut (Waste) -->
                        <div class="rounded-[20px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/[0.04] dark:border-white/[0.06] p-4 sm:p-5 space-y-3">
                            <h4 class="text-[13px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">Efisiensi Bahan (Yield &amp; Waste)</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[12px] font-medium text-[#1C1C1E] dark:text-[#F2F2F7] mb-1">Rendemen (Yield %)</label>
                                    <input type="number" name="yield_percentage" x-model="addForm.yield_percentage" min="1" max="500" required
                                           class="w-full h-10 px-3 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[10px] text-[15px] text-[#1C1C1E] dark:text-[#F2F2F7] font-semibold tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                    <span class="text-[10px] text-[#8E8E93] mt-0.5 block">Standar: 100%</span>
                                </div>
                                <div>
                                    <label class="block text-[12px] font-medium text-[#1C1C1E] dark:text-[#F2F2F7] mb-1">Susut (Waste %)</label>
                                    <input type="number" name="waste_percentage" x-model="addForm.waste_percentage" min="0" max="100" required
                                           class="w-full h-10 px-3 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[10px] text-[15px] text-[#1C1C1E] dark:text-[#F2F2F7] font-semibold tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                    <span class="text-[10px] text-[#8E8E93] mt-0.5 block">Standar: 0%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Harga Akuisisi Awal -->
                        <div class="rounded-[20px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/[0.04] dark:border-white/[0.06] p-4 sm:p-5 space-y-3">
                            <h4 class="text-[13px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">Harga Pembelian Awal</h4>
                            <div>
                                <label class="block text-[12px] font-medium text-[#1C1C1E] dark:text-[#F2F2F7] mb-1">Harga Beli Bersih (Rp) <span class="text-[#FF3B30]">*</span></label>
                                <input type="number" name="purchase_price" x-model="addForm.purchase_price" required min="1" placeholder="50000"
                                       class="w-full h-10 px-3.5 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[10px] text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] font-semibold tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-medium text-[#8E8E93] mb-1">Ongkos Kirim</label>
                                    <input type="number" name="shipping_cost" x-model="addForm.shipping_cost" min="0" value="0"
                                           class="w-full h-9 px-3 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[8px] text-[13px] text-[#1C1C1E] dark:text-[#F2F2F7] tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-[#8E8E93] mb-1">Diskon Beli</label>
                                    <input type="number" name="discount_amount" x-model="addForm.discount_amount" min="0" value="0"
                                           class="w-full h-9 px-3 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[8px] text-[13px] text-[#1C1C1E] dark:text-[#F2F2F7] tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                </div>
                            </div>
                        </div>

                        <!-- Live Cost Preview Card -->
                        <div class="rounded-[20px] p-4 bg-[#34C759]/10 border border-[#34C759]/20 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-[#248A3D] dark:text-[#30D158] block">Estimasi Biaya Efektif / Unit</span>
                                <span class="text-[11px] text-[#248A3D]/80 dark:text-[#30D158]/80">Termasuk ongkir, diskon &amp; faktor yield</span>
                            </div>
                            <div class="text-right">
                                <span class="text-[20px] font-bold tabular-nums text-[#248A3D] dark:text-[#30D158]">
                                    Rp <span x-text="formatRupiah(effectiveCostPreview)">0</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sticky Modal Actions Footer -->
                <div class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-end gap-3 sticky bottom-0 bg-white dark:bg-[#1C1C1E] pb-2 sm:pb-0">
                    <button type="button" @click="showAddModal = false"
                            class="h-11 px-5 rounded-[12px] text-[14px] font-medium text-[#1C1C1E] dark:text-[#F2F2F7] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="h-11 px-6 rounded-[12px] text-[14px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all shadow-sm shadow-[#007AFF]/25">
                        Simpan Bahan Baku
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 7. MODAL: UPDATE HARGA BAHAN                          -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('materials.edit'))
    <div x-show="showPriceModal" x-cloak
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/40 backdrop-blur-sm"
         @keydown.escape.window="showPriceModal = false">
        
        <div class="w-full inset-x-0 bottom-0 rounded-t-[28px] sm:rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-[0_24px_60px_rgba(0,0,0,0.3)] max-h-[90vh] flex flex-col overflow-hidden sm:max-w-xl transition-all"
             @click.outside="showPriceModal = false">
            
            <div class="sm:hidden pt-2.5 pb-1 flex justify-center shrink-0">
                <div class="w-10 h-1.5 rounded-full bg-black/20 dark:bg-white/20"></div>
            </div>

            <div class="px-5 sm:px-6 py-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between shrink-0 bg-[#F2F2F7]/50 dark:bg-white/[0.02]">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">Riwayat Harga Beli</div>
                    <h3 class="text-[18px] sm:text-[20px] font-bold text-[#1C1C1E] dark:text-[#F2F2F7]" x-text="selectedMaterial ? selectedMaterial.name : 'Update Harga'"></h3>
                </div>
                <button type="button" @click="showPriceModal = false" class="w-9 h-9 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black/60 dark:text-white/60 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <template x-if="selectedMaterial">
                <form :action="'/materials/' + (selectedMaterial.slug || selectedMaterial.id) + '/prices'" method="POST" class="p-5 sm:p-6 space-y-4 overflow-y-auto">
                    @csrf

                    <div>
                        <label class="block text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1">
                            Harga Beli Baru (Rp) <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="number" name="purchase_price" required min="1" step="100" placeholder="50000"
                               class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] font-semibold tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[12px] font-medium text-[#1C1C1E] dark:text-[#F2F2F7] mb-1">Ongkos Kirim</label>
                            <input type="number" name="shipping_cost" value="0" min="0"
                                   class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-[#1C1C1E] dark:text-[#F2F2F7] tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                        <div>
                            <label class="block text-[12px] font-medium text-[#1C1C1E] dark:text-[#F2F2F7] mb-1">Diskon Pembelian</label>
                            <input type="number" name="discount_amount" value="0" min="0"
                                   class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-[#1C1C1E] dark:text-[#F2F2F7] tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[12px] font-medium text-[#1C1C1E] dark:text-[#F2F2F7] mb-1">Pemasok / Vendor</label>
                        <select name="supplier_id"
                                class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-[#1C1C1E] dark:text-[#F2F2F7] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <option value="">-- Pemasok yang Sama / Umum --</option>
                            <template x-for="sup in suppliers" :key="sup.id">
                                <option :value="sup.id" :selected="selectedMaterial && selectedMaterial.supplier_id == sup.id" x-text="sup.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[12px] font-medium text-[#1C1C1E] dark:text-[#F2F2F7] mb-1">Catatan Penyesuaian</label>
                        <input type="text" name="notes" placeholder="Contoh: Kenaikan harga pabrik"
                               class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-[#1C1C1E] dark:text-[#F2F2F7] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div class="pt-3 flex justify-end gap-2.5 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <button type="button" @click="showPriceModal = false" class="h-10 px-4 rounded-[12px] text-[13px] font-medium text-[#1C1C1E] dark:text-[#F2F2F7] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] transition">Batal</button>
                        <button type="submit" class="h-10 px-5 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition shadow-sm shadow-[#007AFF]/25">Simpan Riwayat Harga</button>
                    </div>
                </form>
            </template>
        </div>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 8. MODAL: EDIT SPESIFIKASI BAHAN                      -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('materials.edit'))
    <div x-show="showEditModal" x-cloak
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/40 backdrop-blur-sm"
         @keydown.escape.window="showEditModal = false">
        
        <div class="w-full inset-x-0 bottom-0 rounded-t-[28px] sm:rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-[0_24px_60px_rgba(0,0,0,0.3)] max-h-[90vh] flex flex-col overflow-hidden sm:max-w-2xl transition-all"
             @click.outside="showEditModal = false">
            
            <div class="sm:hidden pt-2.5 pb-1 flex justify-center shrink-0">
                <div class="w-10 h-1.5 rounded-full bg-black/20 dark:bg-white/20"></div>
            </div>

            <div class="px-5 sm:px-6 py-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between shrink-0 bg-[#F2F2F7]/50 dark:bg-white/[0.02]">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">Edit Master Data</div>
                    <h3 class="text-[18px] sm:text-[20px] font-bold text-[#1C1C1E] dark:text-[#F2F2F7]">Ubah Spesifikasi Bahan</h3>
                </div>
                <button type="button" @click="showEditModal = false" class="w-9 h-9 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] text-black/60 dark:text-white/60 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="'/materials/' + (editMaterial.slug || editMaterial.id)" method="POST" class="p-5 sm:p-6 space-y-4 overflow-y-auto">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1.5">
                        Nama Bahan Baku <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="text" name="name" x-model="editMaterial.name" required
                           class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1.5">Kode SKU / Barcode</label>
                        <input type="text" name="sku" x-model="editMaterial.sku"
                               class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7]">Kategori Bahan</label>
                            <button type="button" @click="showAddCategoryModal = true" class="text-[11px] font-semibold text-[#007AFF] hover:underline">[ + ] Kategori</button>
                        </div>
                        <select name="category_id" x-model="editMaterial.category_id"
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] px-3 text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <option value="">-- Tanpa Kategori --</option>
                            <template x-for="cat in categories" :key="cat.id">
                                <option :value="cat.id" x-text="cat.name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7]">Satuan Beli <span class="text-[#FF3B30]">*</span></label>
                            <button type="button" @click="showAddUnitModal = true" class="text-[11px] font-semibold text-[#007AFF] hover:underline">[ + ] Satuan</button>
                        </div>
                        <select name="unit_id" x-model="editMaterial.unit_id" required
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] px-3 text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <template x-for="u in units" :key="u.id">
                                <option :value="u.id" x-text="u.name + ' (' + u.code + ')'"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7]">Pemasok Utama</label>
                            <button type="button" @click="showAddSupplierModal = true" class="text-[11px] font-semibold text-[#007AFF] hover:underline">[ + ] Supplier</button>
                        </div>
                        <select name="supplier_id" x-model="editMaterial.supplier_id"
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] px-3 text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <option value="">-- Tanpa Pemasok --</option>
                            <template x-for="sup in suppliers" :key="sup.id">
                                <option :value="sup.id" x-text="sup.name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="pt-3 flex justify-end gap-2.5 border-t border-black/[0.06] dark:border-white/[0.08]">
                    <button type="button" @click="showEditModal = false" class="h-10 px-4 rounded-[12px] text-[13px] font-medium text-[#1C1C1E] dark:text-[#F2F2F7] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] transition">Batal</button>
                    <button type="submit" class="h-10 px-5 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition shadow-sm shadow-[#007AFF]/25">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 9. SUB-MODALS AJAX QUICK-ADD (Zero Page Reload)       -->
    <!-- ===================================================== -->
    <!-- Quick Add Supplier Modal -->
    <div x-show="showAddSupplierModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
         @keydown.escape.window="showAddSupplierModal = false">
        <div class="w-full max-w-md rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] p-5 sm:p-6 space-y-4 shadow-2xl"
             @click.outside="showAddSupplierModal = false">
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                <h3 class="text-[16px] font-bold text-[#1C1C1E] dark:text-[#F2F2F7]">Tambah Supplier Baru</h3>
                <button type="button" @click="showAddSupplierModal = false" class="w-7 h-7 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center text-black/50 dark:text-white/50">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <template x-if="quickSup.error">
                <div class="p-2.5 rounded-[10px] bg-[#FF3B30]/10 text-[#FF3B30] text-[12px] font-medium" x-text="quickSup.error"></div>
            </template>

            <form @submit.prevent="submitQuickSupplier" class="space-y-3.5 text-[13px]">
                <div>
                    <label class="block font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1">Nama Supplier / Vendor <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" x-model="quickSup.name" required placeholder="Contoh: PT Bogasari Flour Mills"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block font-medium text-[#8E8E93] mb-1">PIC / Kontak</label>
                        <input type="text" x-model="quickSup.contact_person" placeholder="Pak Budi"
                               class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-[#1C1C1E] dark:text-[#F2F2F7] focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block font-medium text-[#8E8E93] mb-1">No. HP/WA</label>
                        <input type="text" x-model="quickSup.phone" placeholder="08123456789"
                               class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-[#1C1C1E] dark:text-[#F2F2F7] tabular-nums focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                </div>
                <div>
                    <label class="block font-medium text-[#8E8E93] mb-1">Alamat Gudang / Kantor</label>
                    <input type="text" x-model="quickSup.address" placeholder="Kota / Alamat pengiriman"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-[#1C1C1E] dark:text-[#F2F2F7] focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="showAddSupplierModal = false" class="h-9 px-3.5 rounded-[10px] text-[13px] bg-black/[0.06] dark:bg-white/[0.08] text-[#1C1C1E] dark:text-[#F2F2F7]">Batal</button>
                    <button type="submit" :disabled="quickSup.isSubmitting" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold bg-[#007AFF] text-white flex items-center gap-1.5 shadow-sm shadow-[#007AFF]/25">
                        <span x-show="quickSup.isSubmitting">Menyimpan...</span>
                        <span x-show="!quickSup.isSubmitting">Simpan Supplier</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Add Category Modal -->
    <div x-show="showAddCategoryModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
         @keydown.escape.window="showAddCategoryModal = false">
        <div class="w-full max-w-md rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] p-5 sm:p-6 space-y-4 shadow-2xl"
             @click.outside="showAddCategoryModal = false">
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                <h3 class="text-[16px] font-bold text-[#1C1C1E] dark:text-[#F2F2F7]">Tambah Kategori Bahan</h3>
                <button type="button" @click="showAddCategoryModal = false" class="w-7 h-7 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center text-black/50 dark:text-white/50">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <template x-if="quickCat.error">
                <div class="p-2.5 rounded-[10px] bg-[#FF3B30]/10 text-[#FF3B30] text-[12px] font-medium" x-text="quickCat.error"></div>
            </template>

            <form @submit.prevent="submitQuickCategory" class="space-y-3.5 text-[13px]">
                <div>
                    <label class="block font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1">Nama Kategori <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" x-model="quickCat.name" required placeholder="Contoh: Bumbu &amp; Rempah Alami"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div>
                    <label class="block font-medium text-[#8E8E93] mb-1">Deskripsi Kategori</label>
                    <input type="text" x-model="quickCat.description" placeholder="Catatan klasifikasi bahan..."
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-[#1C1C1E] dark:text-[#F2F2F7] focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="showAddCategoryModal = false" class="h-9 px-3.5 rounded-[10px] text-[13px] bg-black/[0.06] dark:bg-white/[0.08] text-[#1C1C1E] dark:text-[#F2F2F7]">Batal</button>
                    <button type="submit" :disabled="quickCat.isSubmitting" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold bg-[#007AFF] text-white flex items-center gap-1.5 shadow-sm shadow-[#007AFF]/25">
                        <span x-show="quickCat.isSubmitting">Menyimpan...</span>
                        <span x-show="!quickCat.isSubmitting">Simpan Kategori</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Add Unit Modal -->
    <div x-show="showAddUnitModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
         @keydown.escape.window="showAddUnitModal = false">
        <div class="w-full max-w-md rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] p-5 sm:p-6 space-y-4 shadow-2xl"
             @click.outside="showAddUnitModal = false">
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                <h3 class="text-[16px] font-bold text-[#1C1C1E] dark:text-[#F2F2F7]">Tambah Satuan Beli Baru</h3>
                <button type="button" @click="showAddUnitModal = false" class="w-7 h-7 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center text-black/50 dark:text-white/50">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <template x-if="quickUnit.error">
                <div class="p-2.5 rounded-[10px] bg-[#FF3B30]/10 text-[#FF3B30] text-[12px] font-medium" x-text="quickUnit.error"></div>
            </template>

            <form @submit.prevent="submitQuickUnit" class="space-y-3.5 text-[13px]">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1">Kode Simbol <span class="text-[#FF3B30]">*</span></label>
                        <input type="text" x-model="quickUnit.code" required placeholder="sak / jerigen"
                               class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] tabular-nums focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1">Tipe Dimensi <span class="text-[#FF3B30]">*</span></label>
                        <select x-model="quickUnit.category" required
                                class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-2.5 text-[13px] text-[#1C1C1E] dark:text-[#F2F2F7] focus:ring-2 focus:ring-[#007AFF]/50">
                            <option value="weight">Berat (kg, sak)</option>
                            <option value="volume">Volume (liter, ml)</option>
                            <option value="quantity">Kuantitas (pcs, pack)</option>
                            <option value="custom">Lainnya</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1">Nama Satuan Lengkap <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" x-model="quickUnit.name" required placeholder="Contoh: Sak 25 Kilogram"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="showAddUnitModal = false" class="h-9 px-3.5 rounded-[10px] text-[13px] bg-black/[0.06] dark:bg-white/[0.08] text-[#1C1C1E] dark:text-[#F2F2F7]">Batal</button>
                    <button type="submit" :disabled="quickUnit.isSubmitting" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold bg-[#007AFF] text-white flex items-center gap-1.5 shadow-sm shadow-[#007AFF]/25">
                        <span x-show="quickUnit.isSubmitting">Menyimpan...</span>
                        <span x-show="!quickUnit.isSubmitting">Simpan Satuan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 10. APPLE ALERT DIALOG (Hapus Bahan Baku)              -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('materials.delete'))
    <div x-show="deleteModalOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
         @keydown.escape.window="closeDelete()">
        <div class="w-full max-w-sm rounded-[24px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden shadow-2xl border border-black/[0.08] dark:border-white/[0.1] text-center"
             @click.outside="closeDelete()">
            <div class="p-6">
                <div class="w-12 h-12 rounded-full bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 class="text-[17px] font-bold text-[#1C1C1E] dark:text-[#F2F2F7]">Hapus Bahan Baku?</h3>
                <p class="text-[13px] text-[#8E8E93] dark:text-[#98989D] mt-1.5 leading-relaxed">
                    Bahan <strong class="text-[#1C1C1E] dark:text-[#F2F2F7]" x-text="deleteTarget.name"></strong> akan dihapus dari daftar master inventori.
                </p>

                <!-- Penenang Jiwa Microcopy (Mandat Apple HIG) -->
                <div class="mt-4 p-3 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] text-left flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-[11.5px] text-[#3C3C43]/70 dark:text-[#EBEBF5]/70 leading-relaxed">
                        Tenang: Resep produk (BOM) masa lalu dan riwayat pembelian/penerimaan barang (Goods Receipt) yang menggunakan bahan ini tetap aman tersimpan.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 border-t border-black/[0.06] dark:border-white/[0.08] text-[15px] font-medium">
                <button type="button" @click="closeDelete()" class="py-3.5 text-[#007AFF] border-r border-black/[0.06] dark:border-white/[0.08] active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Batal
                </button>
                <button type="button" @click="submitDelete()" class="py-3.5 text-[#FF3B30] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Hapus
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
