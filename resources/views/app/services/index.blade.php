@extends('layouts.app', [
    'title' => 'Jasa & Layanan',
    'headerTitle' => 'Jasa & Layanan',
    'headerSubtitle' => 'Kelola tarif ongkos jasa, biaya servis, atau perawatan tanpa repot mengatur stok gudang'
])

@section('content')
<div class="w-full max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-10" x-data="{
    showAddModal: false,
    showEditModal: {{ $editService ? 'true' : 'false' }},
    showAddCategoryModal: false,
    
    // Dynamic categories array for zero-reload injection
    categories: {{ Js::from($categories) }},
    units: {{ Js::from($units) }},

    // Form inputs state
    addForm: {
        name: '',
        code: '',
        category_id: '',
        output_unit_id: '{{ $defaultUnit?->id ?? '' }}',
        description: '',
        selling_price: '',
        base_cost: 0,
        show_in_pos: true,
        show_in_sales_order: true,
        show_in_website: true,
        show_price_on_web: true,
        is_active: true
    },

    editForm: {
        id: '{{ $editService?->id ?? '' }}',
        name: {{ Js::from($editService?->name ?? '') }},
        code: {{ Js::from($editService?->code ?? '') }},
        category_id: '{{ $editService?->category_id ?? '' }}',
        output_unit_id: '{{ $editService?->output_unit_id ?? $defaultUnit?->id ?? '' }}',
        description: {{ Js::from($editService?->description ?? '') }},
        selling_price: '{{ $editService ? (int)$editService->selling_price : '' }}',
        base_cost: '{{ $editService ? (int)$editService->base_cost : 0 }}',
        show_in_pos: {{ $editService ? ($editService->show_in_pos ? 'true' : 'false') : 'true' }},
        show_in_sales_order: {{ $editService ? ($editService->show_in_sales_order ? 'true' : 'false') : 'true' }},
        show_in_website: {{ $editService ? ($editService->show_in_website ? 'true' : 'false') : 'true' }},
        show_price_on_web: {{ $editService ? ($editService->show_price_on_web ? 'true' : 'false') : 'true' }},
        is_active: {{ $editService ? ($editService->is_active ? 'true' : 'false') : 'true' }}
    },

    // Quick-add category submodal state
    quickCat: { name: '', isSubmitting: false, error: '' },

    // Delete Alert State
    deleteModalOpen: false,
    deleteTarget: { id: '', name: '' },

    init() {
        window.addEventListener('pageshow', () => {
            this.showAddModal = false;
            this.deleteModalOpen = false;
            this.showAddCategoryModal = false;
        });
    },

    formatRupiah(num) {
        return new Intl.NumberFormat('id-ID').format(num || 0);
    },

    openAdd() {
        this.showAddModal = true;
    },

    openEdit(item) {
        this.editForm = {
            id: item.id,
            name: item.name,
            code: item.code || '',
            category_id: item.category_id || '',
            output_unit_id: item.output_unit_id || '{{ $defaultUnit?->id ?? '' }}',
            description: item.description || '',
            selling_price: Math.round(parseFloat(item.selling_price) || 0),
            base_cost: Math.round(parseFloat(item.base_cost) || 0),
            show_in_pos: item.show_in_pos !== undefined ? Boolean(item.show_in_pos) : true,
            show_in_sales_order: item.show_in_sales_order !== undefined ? Boolean(item.show_in_sales_order) : true,
            show_in_website: item.show_in_website !== undefined ? Boolean(item.show_in_website) : true,
            show_price_on_web: item.show_price_on_web !== undefined ? Boolean(item.show_price_on_web) : true,
            is_active: item.is_active !== undefined ? Boolean(item.is_active) : true
        };
        this.showEditModal = true;
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

    async submitQuickCategory() {
        if (!this.quickCat.name.trim()) return;
        this.quickCat.isSubmitting = true;
        this.quickCat.error = '';

        try {
            const res = await fetch('{{ route('product-categories.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    name: this.quickCat.name.trim()
                })
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal menyimpan kategori');

            const newCat = data.category || data.data || data;
            this.categories.push(newCat);
            this.addForm.category_id = newCat.id;
            if (this.showEditModal) this.editForm.category_id = newCat.id;

            this.quickCat = { name: '', isSubmitting: false, error: '' };
            this.showAddCategoryModal = false;
        } catch (e) {
            this.quickCat.error = e.message;
            this.quickCat.isSubmitting = false;
        }
    }
}">

    <!-- ===================================================== -->
    <!-- 1. UNIFIED APPLE SEGMENTED CONTROL (Section 16 Mandate) -->
    <!-- ===================================================== -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="rounded-[16px] bg-black/[0.04] dark:bg-white/[0.04] p-1.5 flex items-center gap-1.5 w-fit border border-black/[0.04] dark:border-white/[0.06]">
            <a href="{{ route('products.index') }}"
               class="px-4 py-2 rounded-[12px] text-[13px] font-medium text-[#8E8E93] dark:text-[#98989D] hover:text-[#1C1C1E] dark:hover:text-[#F2F2F7] transition-all">
                Barang Fisik (Katalog)
            </a>
            <a href="{{ route('services.index') }}"
               class="px-4 py-2 rounded-[12px] text-[13px] font-semibold bg-white dark:bg-[#2C2C2E] text-[#1C1C1E] dark:text-[#F2F2F7] shadow-sm transition-all">
                Jasa &amp; Layanan
            </a>
            <a href="{{ route('pos.modifiers.index') }}"
               class="px-4 py-2 rounded-[12px] text-[13px] font-medium text-[#8E8E93] dark:text-[#98989D] hover:text-[#1C1C1E] dark:hover:text-[#F2F2F7] transition-all">
                Varian &amp; Modifiers
            </a>
        </div>

        <button type="button" @click="openAdd()"
                class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all flex items-center justify-center gap-2 shadow-sm shadow-[#007AFF]/25">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            <span>Tambah Layanan Baru</span>
        </button>
    </div>

    <!-- ===================================================== -->
    <!-- 2. BENTO KPI METRICS SUMMARY                          -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        <!-- Tile 1: Total Layanan -->
        <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-semibold text-[#8E8E93] dark:text-[#98989D] uppercase tracking-wider">Total Layanan</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242L9.757 9.757m0 0L19 19m-9.243-9.243a3 3 0 114.242-4.242L11.121 9.121"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-[26px] font-bold tabular-nums text-[#1C1C1E] dark:text-[#F2F2F7]">{{ number_format($totalServicesCount) }}</span>
                <span class="text-[11px] font-medium text-[#8E8E93] dark:text-[#98989D]">Paket &amp; Servis</span>
            </div>
        </div>

        <!-- Tile 2: Status di Kasir -->
        <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-semibold text-[#8E8E93] dark:text-[#98989D] uppercase tracking-wider">Status di Kasir</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-[20px] font-bold text-[#34C759] dark:text-[#30D158]">Bebas Stok &amp; Siap</span>
                <span class="text-[11px] font-medium text-[#8E8E93] dark:text-[#98989D]">Tanpa Gudang</span>
            </div>
        </div>

        <!-- Tile 3: Kategori Jasa -->
        <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-semibold text-[#8E8E93] dark:text-[#98989D] uppercase tracking-wider">Kategori Jasa</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-[26px] font-bold tabular-nums text-[#5856D6]"><span x-text="categories.length">{{ $categories->count() }}</span></span>
                <span class="text-[11px] font-medium text-[#8E8E93] dark:text-[#98989D]">Grup Jasa</span>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. SEARCH & FILTER BAR                                -->
    <!-- ===================================================== -->
    <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] p-3.5 sm:p-4 shadow-xs">
        <form method="GET" action="{{ route('services.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="relative flex-1 max-w-md">
                <svg class="w-4 h-4 text-black/40 dark:text-white/40 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama layanan, kode, atau deskripsi..."
                       class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] pl-10 pr-3.5 text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>

            <div class="flex items-center gap-2">
                <select name="category_id" onchange="this.form.submit()"
                        class="h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] text-[13px] font-medium text-[#1C1C1E] dark:text-[#F2F2F7] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>

                @if(request('search') || request('category_id'))
                <a href="{{ route('services.index') }}" class="h-10 px-3.5 rounded-[12px] text-[13px] font-medium text-[#8E8E93] hover:text-[#1C1C1E] dark:hover:text-[#F2F2F7] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] transition-all flex items-center gap-1.5">
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
                        <th class="py-3 px-4 text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] whitespace-nowrap">Layanan &amp; Kode</th>
                        <th class="py-3 px-4 text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] whitespace-nowrap">Kategori</th>
                        <th class="py-3 px-4 text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] whitespace-nowrap">Satuan Output</th>
                        <th class="py-3 px-4 text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] text-right whitespace-nowrap">Tarif / Harga Jual</th>
                        <th class="py-3 px-4 text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] text-center whitespace-nowrap">Kanal Penjualan</th>
                        <th class="py-3 px-4 text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($services as $item)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="py-3.5 px-4 font-medium text-[#1C1C1E] dark:text-[#F2F2F7]">
                            <div class="font-semibold text-[13.5px]">{{ $item->name }}</div>
                            <div class="text-[11px] text-[#8E8E93] dark:text-[#98989D] tabular-nums mt-0.5">
                                {{ $item->code ?? 'No SKU' }}
                                @if($item->description)
                                    · <span class="text-[#3C3C43]/70 dark:text-[#EBEBF5]/70 truncate inline-block max-w-xs align-bottom">{{ $item->description }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-[#3C3C43]/80 dark:text-[#EBEBF5]/80">
                            {{ $item->category?->name ?? 'Tanpa Kategori' }}
                        </td>
                        <td class="py-3.5 px-4 text-[#1C1C1E] dark:text-[#F2F2F7] font-medium">
                            {{ $item->outputUnit?->name ?? 'Jasa' }}
                        </td>
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <div class="font-bold text-[14px] text-[#007AFF] tabular-nums">
                                Rp {{ number_format((float)$item->selling_price, 0, ',', '.') }}
                            </div>
                            @if($item->base_cost > 0)
                                <div class="text-[11px] text-[#8E8E93] tabular-nums">
                                    Modal: Rp {{ number_format((float)$item->base_cost, 0, ',', '.') }}
                                </div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">
                                <span class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-semibold {{ $item->show_in_pos ? 'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/[0.05] text-[#8E8E93]' }}">
                                    POS
                                </span>
                                <span class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-semibold {{ $item->show_in_website ? 'bg-[#007AFF]/10 text-[#007AFF]' : 'bg-black/[0.05] text-[#8E8E93]' }}">
                                    Web
                                </span>
                                <span class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-semibold {{ $item->show_in_sales_order ? 'bg-[#5856D6]/10 text-[#5856D6]' : 'bg-black/[0.05] text-[#8E8E93]' }}">
                                    SO
                                </span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1.5">
                                <button type="button" @click="openEdit({{ Js::from($item) }})"
                                        class="h-8 px-3 rounded-[8px] text-[12px] font-medium text-[#1C1C1E] dark:text-[#F2F2F7] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] transition-colors" title="Ubah Layanan">
                                    Edit
                                </button>
                                <button type="button" @click="openDelete('{{ $item->id }}', {{ Js::from($item->name) }})"
                                        class="h-8 px-3 rounded-[8px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/10 transition-colors" title="Hapus Layanan">
                                    Hapus
                                </button>
                                <form id="form-delete-{{ $item->id }}" action="{{ route('services.destroy', $item) }}" method="POST" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-[#8E8E93] dark:text-[#98989D]">
                            Belum ada data jasa atau layanan ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($services->hasPages())
        <div class="px-4 py-3 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between text-[13px] text-[#8E8E93]">
            {{ $services->links() }}
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 5. MOBILE GROUPED INSET LIST (Standar Apple HIG)      -->
    <!-- ===================================================== -->
    <div class="sm:hidden space-y-3">
        @forelse($services as $item)
        <div class="rounded-[20px] bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] p-4 shadow-xs space-y-3">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <h3 class="text-[15px] font-bold text-[#1C1C1E] dark:text-[#F2F2F7] leading-tight truncate">{{ $item->name }}</h3>
                    <div class="text-[12px] text-[#8E8E93] dark:text-[#98989D] mt-0.5">
                        {{ $item->code ?? 'No SKU' }} · {{ $item->category?->name ?? 'Tanpa Kategori' }}
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <span class="text-[16px] font-bold text-[#007AFF] tabular-nums block">
                        Rp {{ number_format((float)$item->selling_price, 0, ',', '.') }}
                    </span>
                    <span class="text-[11px] text-[#8E8E93] dark:text-[#98989D]">/ {{ $item->outputUnit?->name ?? 'Jasa' }}</span>
                </div>
            </div>

            @if($item->description)
            <p class="text-[12px] text-[#3C3C43]/70 dark:text-[#EBEBF5]/70 line-clamp-2">
                {{ $item->description }}
            </p>
            @endif

            <div class="flex items-center justify-between pt-2 border-t border-black/[0.04] dark:border-white/[0.06]">
                <div class="flex items-center gap-1.5">
                    <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-semibold {{ $item->show_in_pos ? 'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/[0.05] text-[#8E8E93]' }}">
                        POS
                    </span>
                    <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-semibold {{ $item->show_in_website ? 'bg-[#007AFF]/10 text-[#007AFF]' : 'bg-black/[0.05] text-[#8E8E93]' }}">
                        Web
                    </span>
                    <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-semibold {{ $item->show_in_sales_order ? 'bg-[#5856D6]/10 text-[#5856D6]' : 'bg-black/[0.05] text-[#8E8E93]' }}">
                        SO
                    </span>
                </div>

                <div class="flex items-center gap-1.5">
                    <button type="button" @click="openEdit({{ Js::from($item) }})" class="h-9 px-3 rounded-[10px] text-[12px] font-medium text-[#1C1C1E] dark:text-[#F2F2F7] bg-black/[0.04] dark:bg-white/[0.06] active:scale-95 transition-all">
                        Edit
                    </button>
                    <button type="button" @click="openDelete('{{ $item->id }}', {{ Js::from($item->name) }})" class="h-9 px-3 rounded-[10px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/10 active:scale-95 transition-all">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 p-8 text-center text-[#8E8E93] border border-black/[0.06] dark:border-white/[0.08]">
            Belum ada data jasa atau layanan ditemukan.
        </div>
        @endforelse

        @if($services->hasPages())
        <div class="pt-2">
            {{ $services->links() }}
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 6. MODAL: TAMBAH LAYANAN (FULL LAYOUT XXL BENTO)      -->
    <!-- ===================================================== -->
    <div x-show="showAddModal" x-cloak
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/40 backdrop-blur-sm"
         @keydown.escape.window="showAddModal = false">
        
        <div class="w-full inset-x-0 bottom-0 rounded-t-[28px] sm:rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-[0_24px_60px_rgba(0,0,0,0.3)] max-h-[94vh] sm:max-h-[90vh] flex flex-col overflow-hidden sm:max-w-[95vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-7xl transition-all"
             @click.outside="showAddModal = false">
            
            <div class="sm:hidden pt-2.5 pb-1 flex justify-center shrink-0">
                <div class="w-10 h-1.5 rounded-full bg-black/20 dark:bg-white/20"></div>
            </div>

            <div class="px-5 sm:px-6 py-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between shrink-0 bg-[#F2F2F7]/50 dark:bg-white/[0.02]">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">Formulir Layanan Bebas Stok</div>
                    <h3 class="text-[18px] sm:text-[22px] font-bold text-[#1C1C1E] dark:text-[#F2F2F7] tracking-tight">Tambah Layanan Jasa Baru</h3>
                </div>
                <button type="button" @click="showAddModal = false" class="w-9 h-9 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] text-black/60 dark:text-white/60 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('services.store') }}" class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-6">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Kolom Kiri: 7 Kolom (Identitas Layanan) -->
                    <div class="lg:col-span-7 space-y-5">
                        <div class="rounded-[20px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/[0.04] dark:border-white/[0.06] p-4 sm:p-5 space-y-4">
                            <h4 class="text-[13px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">Identitas Layanan</h4>

                            <div>
                                <label class="block text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1.5">
                                    Nama Jasa / Layanan <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="text" name="name" x-model="addForm.name" required
                                       placeholder="Contoh: Potong Rambut Pria / Servis AC Rutin"
                                       class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1.5">Kode Jasa / SKU</label>
                                    <input type="text" name="code" x-model="addForm.code"
                                           placeholder="SRV-001 (opsional)"
                                           class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </div>
                                <div>
                                    <label class="block text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1.5">Satuan Durasi / Output</label>
                                    <select name="output_unit_id" x-model="addForm.output_unit_id"
                                            class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3 text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                        <template x-for="u in units" :key="u.id">
                                            <option :value="u.id" x-text="u.name + ' (' + u.code + ')'"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7]">Kategori Layanan</label>
                                    <button type="button" @click="showAddCategoryModal = true" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-0.5">
                                        <span>[ + ]</span> <span>Kategori Baru</span>
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
                                <label class="block text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1.5">Deskripsi / Ruang Lingkup Layanan</label>
                                <textarea name="description" x-model="addForm.description" rows="3"
                                          placeholder="Jelaskan detail apa saja yang termasuk dalam paket layanan ini..."
                                          class="w-full p-3.5 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: 5 Kolom (Tarif & Kanal Penjualan) -->
                    <div class="lg:col-span-5 space-y-5">
                        <!-- Tarif & Biaya Modal -->
                        <div class="rounded-[20px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/[0.04] dark:border-white/[0.06] p-4 sm:p-5 space-y-4">
                            <h4 class="text-[13px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">Tarif &amp; Upah Kerja</h4>
                            
                            <div>
                                <label class="block text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1.5">
                                    Tarif Jasa / Harga Jual (Rp) <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="number" name="selling_price" x-model="addForm.selling_price" required min="0" step="100" placeholder="75000"
                                       class="w-full h-11 px-3.5 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[16px] sm:text-[15px] font-bold text-[#007AFF] tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            </div>

                            <div>
                                <label class="block text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1.5">
                                    Modal Dasar / Upah Teknisi (Rp)
                                </label>
                                <input type="number" name="base_cost" x-model="addForm.base_cost" min="0" step="100" placeholder="30000"
                                       class="w-full h-11 px-3.5 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] font-semibold tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                <span class="text-[11px] text-[#8E8E93] mt-1 block">Opsional. Digunakan untuk estimasi laba kotor layanan.</span>
                            </div>
                        </div>

                        <!-- Multi-Channel Switches -->
                        <div class="rounded-[20px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/[0.04] dark:border-white/[0.06] p-4 sm:p-5 space-y-3">
                            <h4 class="text-[13px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">Kanal &amp; Visibilitas</h4>

                            <!-- Switch POS -->
                            <label class="flex items-center justify-between p-2.5 rounded-[12px] hover:bg-black/[0.02] dark:hover:bg-white/[0.02] cursor-pointer">
                                <div>
                                    <span class="text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] block">Tampilkan di Kasir POS</span>
                                    <span class="text-[11px] text-[#8E8E93]">Dapat dipilih kasir saat transaksi</span>
                                </div>
                                <input type="hidden" name="show_in_pos" value="0">
                                <input type="checkbox" name="show_in_pos" value="1" x-model="addForm.show_in_pos"
                                       class="w-5 h-5 rounded-[6px] text-[#007AFF] focus:ring-[#007AFF]/50 border-black/20 dark:border-white/20">
                            </label>

                            <!-- Switch Sales Order -->
                            <label class="flex items-center justify-between p-2.5 rounded-[12px] hover:bg-black/[0.02] dark:hover:bg-white/[0.02] cursor-pointer">
                                <div>
                                    <span class="text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] block">Faktur &amp; Sales Order</span>
                                    <span class="text-[11px] text-[#8E8E93]">Tersedia di surat pesanan B2B</span>
                                </div>
                                <input type="hidden" name="show_in_sales_order" value="0">
                                <input type="checkbox" name="show_in_sales_order" value="1" x-model="addForm.show_in_sales_order"
                                       class="w-5 h-5 rounded-[6px] text-[#007AFF] focus:ring-[#007AFF]/50 border-black/20 dark:border-white/20">
                            </label>

                            <!-- Switch Website Storefront -->
                            <label class="flex items-center justify-between p-2.5 rounded-[12px] hover:bg-black/[0.02] dark:hover:bg-white/[0.02] cursor-pointer">
                                <div>
                                    <span class="text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] block">Toko Online Storefront</span>
                                    <span class="text-[11px] text-[#8E8E93]">Dapat dibooking pelanggan lewat web</span>
                                </div>
                                <input type="hidden" name="show_in_website" value="0">
                                <input type="checkbox" name="show_in_website" value="1" x-model="addForm.show_in_website"
                                       class="w-5 h-5 rounded-[6px] text-[#007AFF] focus:ring-[#007AFF]/50 border-black/20 dark:border-white/20">
                            </label>

                            <!-- Switch Show Price on Web -->
                            <label class="flex items-center justify-between p-2.5 rounded-[12px] hover:bg-black/[0.02] dark:hover:bg-white/[0.02] cursor-pointer">
                                <div>
                                    <span class="text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] block">Tampilkan Tarif di Web</span>
                                    <span class="text-[11px] text-[#8E8E93]">Tampilkan nominal tarif di storefront</span>
                                </div>
                                <input type="hidden" name="show_price_on_web" value="0">
                                <input type="checkbox" name="show_price_on_web" value="1" x-model="addForm.show_price_on_web"
                                       class="w-5 h-5 rounded-[6px] text-[#007AFF] focus:ring-[#007AFF]/50 border-black/20 dark:border-white/20">
                            </label>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-end gap-3 sticky bottom-0 bg-white dark:bg-[#1C1C1E] pb-2 sm:pb-0">
                    <button type="button" @click="showAddModal = false"
                            class="h-11 px-5 rounded-[12px] text-[14px] font-medium text-[#1C1C1E] dark:text-[#F2F2F7] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="h-11 px-6 rounded-[12px] text-[14px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all shadow-sm shadow-[#007AFF]/25">
                        Simpan Layanan Jasa
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 7. MODAL: EDIT LAYANAN (FULL LAYOUT XXL BENTO)        -->
    <!-- ===================================================== -->
    <div x-show="showEditModal" x-cloak
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/40 backdrop-blur-sm"
         @keydown.escape.window="showEditModal = false">
        
        <div class="w-full inset-x-0 bottom-0 rounded-t-[28px] sm:rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-[0_24px_60px_rgba(0,0,0,0.3)] max-h-[94vh] sm:max-h-[90vh] flex flex-col overflow-hidden sm:max-w-[95vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-7xl transition-all"
             @click.outside="showEditModal = false">
            
            <div class="sm:hidden pt-2.5 pb-1 flex justify-center shrink-0">
                <div class="w-10 h-1.5 rounded-full bg-black/20 dark:bg-white/20"></div>
            </div>

            <div class="px-5 sm:px-6 py-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between shrink-0 bg-[#F2F2F7]/50 dark:bg-white/[0.02]">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">Edit Layanan</div>
                    <h3 class="text-[18px] sm:text-[22px] font-bold text-[#1C1C1E] dark:text-[#F2F2F7] tracking-tight">Ubah Data Layanan Jasa</h3>
                </div>
                <button type="button" @click="showEditModal = false" class="w-9 h-9 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] text-black/60 dark:text-white/60 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="'/services/' + editForm.id" method="POST" class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Kolom Kiri: 7 Kolom (Identitas Layanan) -->
                    <div class="lg:col-span-7 space-y-5">
                        <div class="rounded-[20px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/[0.04] dark:border-white/[0.06] p-4 sm:p-5 space-y-4">
                            <h4 class="text-[13px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">Identitas Layanan</h4>

                            <div>
                                <label class="block text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1.5">
                                    Nama Jasa / Layanan <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="text" name="name" x-model="editForm.name" required
                                       class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1.5">Kode Jasa / SKU</label>
                                    <input type="text" name="code" x-model="editForm.code"
                                           class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </div>
                                <div>
                                    <label class="block text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1.5">Satuan Durasi / Output</label>
                                    <select name="output_unit_id" x-model="editForm.output_unit_id"
                                            class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3 text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                        <template x-for="u in units" :key="u.id">
                                            <option :value="u.id" x-text="u.name + ' (' + u.code + ')'"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7]">Kategori Layanan</label>
                                    <button type="button" @click="showAddCategoryModal = true" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-0.5">
                                        <span>[ + ]</span> <span>Kategori Baru</span>
                                    </button>
                                </div>
                                <select name="category_id" x-model="editForm.category_id"
                                        class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3 text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    <option value="">-- Tanpa Kategori --</option>
                                    <template x-for="cat in categories" :key="cat.id">
                                        <option :value="cat.id" x-text="cat.name"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1.5">Deskripsi / Ruang Lingkup Layanan</label>
                                <textarea name="description" x-model="editForm.description" rows="3"
                                          class="w-full p-3.5 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: 5 Kolom (Tarif & Kanal Penjualan) -->
                    <div class="lg:col-span-5 space-y-5">
                        <div class="rounded-[20px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/[0.04] dark:border-white/[0.06] p-4 sm:p-5 space-y-4">
                            <h4 class="text-[13px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">Tarif &amp; Upah Kerja</h4>
                            
                            <div>
                                <label class="block text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1.5">
                                    Tarif Jasa / Harga Jual (Rp) <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="number" name="selling_price" x-model="editForm.selling_price" required min="0" step="100"
                                       class="w-full h-11 px-3.5 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[16px] sm:text-[15px] font-bold text-[#007AFF] tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            </div>

                            <div>
                                <label class="block text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1.5">
                                    Modal Dasar / Upah Teknisi (Rp)
                                </label>
                                <input type="number" name="base_cost" x-model="editForm.base_cost" min="0" step="100"
                                       class="w-full h-11 px-3.5 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] font-semibold tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            </div>
                        </div>

                        <!-- Multi-Channel Switches -->
                        <div class="rounded-[20px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/[0.04] dark:border-white/[0.06] p-4 sm:p-5 space-y-3">
                            <h4 class="text-[13px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">Kanal &amp; Visibilitas</h4>

                            <!-- Switch POS -->
                            <label class="flex items-center justify-between p-2.5 rounded-[12px] hover:bg-black/[0.02] dark:hover:bg-white/[0.02] cursor-pointer">
                                <div>
                                    <span class="text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] block">Tampilkan di Kasir POS</span>
                                    <span class="text-[11px] text-[#8E8E93]">Dapat dipilih kasir saat transaksi</span>
                                </div>
                                <input type="hidden" name="show_in_pos" value="0">
                                <input type="checkbox" name="show_in_pos" value="1" x-model="editForm.show_in_pos"
                                       class="w-5 h-5 rounded-[6px] text-[#007AFF] focus:ring-[#007AFF]/50 border-black/20 dark:border-white/20">
                            </label>

                            <!-- Switch Sales Order -->
                            <label class="flex items-center justify-between p-2.5 rounded-[12px] hover:bg-black/[0.02] dark:hover:bg-white/[0.02] cursor-pointer">
                                <div>
                                    <span class="text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] block">Faktur &amp; Sales Order</span>
                                    <span class="text-[11px] text-[#8E8E93]">Tersedia di surat pesanan B2B</span>
                                </div>
                                <input type="hidden" name="show_in_sales_order" value="0">
                                <input type="checkbox" name="show_in_sales_order" value="1" x-model="editForm.show_in_sales_order"
                                       class="w-5 h-5 rounded-[6px] text-[#007AFF] focus:ring-[#007AFF]/50 border-black/20 dark:border-white/20">
                            </label>

                            <!-- Switch Website Storefront -->
                            <label class="flex items-center justify-between p-2.5 rounded-[12px] hover:bg-black/[0.02] dark:hover:bg-white/[0.02] cursor-pointer">
                                <div>
                                    <span class="text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] block">Toko Online Storefront</span>
                                    <span class="text-[11px] text-[#8E8E93]">Dapat dibooking pelanggan lewat web</span>
                                </div>
                                <input type="hidden" name="show_in_website" value="0">
                                <input type="checkbox" name="show_in_website" value="1" x-model="editForm.show_in_website"
                                       class="w-5 h-5 rounded-[6px] text-[#007AFF] focus:ring-[#007AFF]/50 border-black/20 dark:border-white/20">
                            </label>

                            <!-- Switch Show Price on Web -->
                            <label class="flex items-center justify-between p-2.5 rounded-[12px] hover:bg-black/[0.02] dark:hover:bg-white/[0.02] cursor-pointer">
                                <div>
                                    <span class="text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] block">Tampilkan Tarif di Web</span>
                                    <span class="text-[11px] text-[#8E8E93]">Tampilkan nominal tarif di storefront</span>
                                </div>
                                <input type="hidden" name="show_price_on_web" value="0">
                                <input type="checkbox" name="show_price_on_web" value="1" x-model="editForm.show_price_on_web"
                                       class="w-5 h-5 rounded-[6px] text-[#007AFF] focus:ring-[#007AFF]/50 border-black/20 dark:border-white/20">
                            </label>

                            <!-- Status Aktif -->
                            <label class="flex items-center justify-between p-2.5 rounded-[12px] hover:bg-black/[0.02] dark:hover:bg-white/[0.02] cursor-pointer border-t border-black/[0.04] dark:border-white/[0.06] pt-3">
                                <div>
                                    <span class="text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] block">Status Aktif Layanan</span>
                                    <span class="text-[11px] text-[#8E8E93]">Dapat ditransaksikan saat ini</span>
                                </div>
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" x-model="editForm.is_active"
                                       class="w-5 h-5 rounded-[6px] text-[#34C759] focus:ring-[#34C759]/50 border-black/20 dark:border-white/20">
                            </label>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-end gap-3 sticky bottom-0 bg-white dark:bg-[#1C1C1E] pb-2 sm:pb-0">
                    <button type="button" @click="showEditModal = false"
                            class="h-11 px-5 rounded-[12px] text-[14px] font-medium text-[#1C1C1E] dark:text-[#F2F2F7] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="h-11 px-6 rounded-[12px] text-[14px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all shadow-sm shadow-[#007AFF]/25">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 8. SUB-MODAL QUICK-ADD CATEGORY (Zero Page Reload)    -->
    <!-- ===================================================== -->
    <div x-show="showAddCategoryModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
         @keydown.escape.window="showAddCategoryModal = false">
        <div class="w-full max-w-md rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] p-5 sm:p-6 space-y-4 shadow-2xl"
             @click.outside="showAddCategoryModal = false">
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                <h3 class="text-[16px] font-bold text-[#1C1C1E] dark:text-[#F2F2F7]">Tambah Kategori Layanan</h3>
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
                    <input type="text" x-model="quickCat.name" required placeholder="Contoh: Perawatan Berkala / Bengkel"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] focus:ring-2 focus:ring-[#007AFF]/50">
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

    <!-- ===================================================== -->
    <!-- 9. APPLE ALERT DIALOG (Hapus Layanan)                 -->
    <!-- ===================================================== -->
    <div x-show="deleteModalOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
         @keydown.escape.window="closeDelete()">
        <div class="w-full max-w-sm rounded-[24px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden shadow-2xl border border-black/[0.08] dark:border-white/[0.1] text-center"
             @click.outside="closeDelete()">
            <div class="p-6">
                <div class="w-12 h-12 rounded-full bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 class="text-[17px] font-bold text-[#1C1C1E] dark:text-[#F2F2F7]">Hapus Layanan Jasa?</h3>
                <p class="text-[13px] text-[#8E8E93] dark:text-[#98989D] mt-1.5 leading-relaxed">
                    Layanan <strong class="text-[#1C1C1E] dark:text-[#F2F2F7]" x-text="deleteTarget.name"></strong> akan dihapus dari katalog aktif toko.
                </p>

                <!-- Penenang Jiwa Microcopy (Mandat Apple HIG) -->
                <div class="mt-4 p-3 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] text-left flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-[11.5px] text-[#3C3C43]/70 dark:text-[#EBEBF5]/70 leading-relaxed">
                        Tenang: Riwayat nota kasir POS, faktur penjualan, dan pembukuan masa lalu yang menggunakan layanan ini tetap aman tersimpan.
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

</div>
@endsection
