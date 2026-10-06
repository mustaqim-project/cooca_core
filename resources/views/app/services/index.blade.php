@extends('layouts.app', [
    'title' => __('services.title'),
    'headerTitle' => __('services.header_title'),
    'headerSubtitle' => __('services.header_subtitle'),
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
            output_unit_id: '{{ $editService?->output_unit_id ?? ($defaultUnit?->id ?? '') }}',
            description: {{ Js::from($editService?->description ?? '') }},
            selling_price: '{{ $editService ? (int) $editService->selling_price : '' }}',
            base_cost: '{{ $editService ? (int) $editService->base_cost : 0 }}',
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
                if (!res.ok) throw new Error(data.message || '{{ __('services.quick_category.error_empty') }}');
    
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
        <!-- 1. TOOLBAR / PAGE HEADER                                -->
        <!-- ===================================================== -->
        <x-module-header
            :title="__('services.header_title')"
            :subtitle="__('services.header_subtitle')"
            :breadcrumbs="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Produk', 'url' => route('products.index')],
                ['label' => __('services.header_title'), 'url' => null],
            ]">
            @if(\App\Support\Context::hasPermission('products.manage') || \App\Support\Context::hasPermission('products.create'))
            <button type="button" @click="openAdd()"
                class="min-h-[44px] sm:min-h-0 h-11 sm:h-10 px-4 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all flex items-center justify-center gap-2 shadow-sm shadow-[#007AFF]/25 shrink-0 cursor-pointer">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>{{ __('services.actions.add_new') }}</span>
            </button>
            @endif
        </x-module-header>

        <!-- ===================================================== -->
        <!-- 2. PERSISTENT MODULE TABS                             -->
        <!-- ===================================================== -->
        <x-module-tabs module="products" />

        <!-- ===================================================== -->
        <!-- 3. BENTO KPI METRICS SUMMARY                          -->
        <!-- ===================================================== -->
        <div class="flex sm:grid grid-cols-1 sm:grid-cols-3 flex-nowrap sm:flex-wrap overflow-x-auto sm:overflow-visible pb-2 sm:pb-0 snap-x snap-mandatory gap-3 sm:gap-4 no-scrollbar scrollbar-none">
            <!-- Tile 1: Total Layanan -->
            <div
                class="min-w-[240px] sm:min-w-0 flex-1 snap-start rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
                <div class="flex items-center justify-between">
                    <span
                        class="text-[12px] font-semibold text-[#8E8E93] dark:text-[#98989D] uppercase tracking-wider">{{ __('services.kpis.total_services') }}</span>
                    <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                        <i data-lucide="briefcase" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span
                        class="text-[26px] font-bold tabular-nums text-[#1C1C1E] dark:text-[#F2F2F7]">{{ number_format($totalServicesCount) }}</span>
                    <span class="text-[11px] font-medium text-[#8E8E93] dark:text-[#98989D]">{{ __('services.kpis.total_suffix') }}</span>
                </div>
            </div>

            <!-- Tile 2: Layanan Aktif -->
            <div
                class="min-w-[240px] sm:min-w-0 flex-1 snap-start rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
                <div class="flex items-center justify-between">
                    <span
                        class="text-[12px] font-semibold text-[#8E8E93] dark:text-[#98989D] uppercase tracking-wider">{{ __('services.kpis.active_services') }}</span>
                    <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-[26px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">{{ number_format($services->where('is_active', true)->count()) }}</span>
                    <span class="text-[11px] font-medium text-[#8E8E93] dark:text-[#98989D]">{{ __('services.kpis.active_suffix') }}</span>
                </div>
            </div>

            <!-- Tile 3: Kanal Tampil -->
            <div
                class="min-w-[240px] sm:min-w-0 flex-1 snap-start rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
                <div class="flex items-center justify-between">
                    <span
                        class="text-[12px] font-semibold text-[#8E8E93] dark:text-[#98989D] uppercase tracking-wider">{{ __('services.kpis.visible_pos') }}</span>
                    <div class="w-8 h-8 rounded-[10px] bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center">
                        <i data-lucide="store" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-[26px] font-bold tabular-nums text-[#5856D6]"><span
                            x-text="categories.length">{{ $categories->count() }}</span></span>
                    <span class="text-[11px] font-medium text-[#8E8E93] dark:text-[#98989D]">{{ __('services.kpis.visible_suffix') }}</span>
                </div>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- 4. SEARCH & FILTER BAR                                -->
        <!-- ===================================================== -->
        <div
            class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] p-3.5 sm:p-4 shadow-xs">
            <form method="GET" action="{{ route('services.index') }}"
                class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                <div class="relative flex-1 max-w-md">
                    <i data-lucide="search" class="w-4 h-4 text-black/40 dark:text-white/40 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="{{ __('services.actions.search_placeholder') }}"
                        class="w-full h-11 sm:h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] pl-10 pr-3.5 text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="flex items-center gap-2">
                    <select name="category_id" onchange="this.form.submit()"
                        class="h-11 sm:h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] text-[13px] font-medium text-[#1C1C1E] dark:text-[#F2F2F7] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition cursor-pointer">
                        <option value="">{{ __('services.actions.filter_all_categories') }}</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}"
                                {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>

                    @if (request('search') || request('category_id'))
                        <a href="{{ route('services.index') }}"
                            class="min-h-[44px] sm:min-h-0 h-11 sm:h-10 px-3.5 rounded-[12px] text-[13px] font-medium text-[#8E8E93] hover:text-[#1C1C1E] dark:hover:text-[#F2F2F7] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] transition-all flex items-center gap-1.5 cursor-pointer">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            <span>{{ __('services.actions.reset_filter') }}</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- ===================================================== -->
        <!-- 4. DENSE DATA TABLE (DESKTOP & TABLET)                 -->
        <!-- ===================================================== -->
        <div
            class="hidden sm:block rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr
                            class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02]">
                            <th
                                class="py-3 px-4 text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] whitespace-nowrap">
                                Layanan &amp; Kode</th>
                            <th
                                class="py-3 px-4 text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] whitespace-nowrap">
                                Kategori</th>
                            <th
                                class="py-3 px-4 text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] whitespace-nowrap">
                                Satuan Output</th>
                            <th
                                class="py-3 px-4 text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] text-right whitespace-nowrap">
                                {{ __('services.table.col_price') }}</th>
                            <th
                                class="py-3 px-4 text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] text-center whitespace-nowrap">
                                {{ __('services.table.col_channels') }}</th>
                            <th
                                class="py-3 px-4 text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] text-right whitespace-nowrap">
                                {{ __('services.table.col_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($services as $item)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                <td class="py-3.5 px-4 font-medium text-[#1C1C1E] dark:text-[#F2F2F7]">
                                    <div class="font-semibold text-[13.5px]">{{ $item->name }}</div>
                                    <div class="text-[11px] text-[#8E8E93] dark:text-[#98989D] tabular-nums mt-0.5">
                                        {{ $item->code ?? __('services.table.no_code') }}
                                        @if ($item->description)
                                            · <span
                                                class="text-[#3C3C43]/70 dark:text-[#EBEBF5]/70 truncate inline-block max-w-xs align-bottom">{{ $item->description }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-[#3C3C43]/80 dark:text-[#EBEBF5]/80">
                                    {{ $item->category?->name ?? __('services.table.uncategorized') }}
                                </td>
                                <td class="py-3.5 px-4 text-[#1C1C1E] dark:text-[#F2F2F7] font-medium">
                                    {{ $item->outputUnit?->name ?? __('services.table.unit_fallback') }}
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="font-bold text-[14px] text-[#007AFF] tabular-nums">
                                        Rp {{ number_format((float) $item->selling_price, 0, ',', '.') }}
                                    </div>
                                    @if ($item->base_cost > 0)
                                        <div class="text-[11px] text-[#8E8E93] tabular-nums">
                                            Modal: Rp {{ number_format((float) $item->base_cost, 0, ',', '.') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <span
                                            class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-semibold {{ $item->show_in_pos ? 'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/[0.05] text-[#8E8E93]' }}">
                                            {{ __('services.channels.pos') }}
                                        </span>
                                        <span
                                            class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-semibold {{ $item->show_in_website ? 'bg-[#007AFF]/10 text-[#007AFF]' : 'bg-black/[0.05] text-[#8E8E93]' }}">
                                            {{ __('services.channels.website') }}
                                        </span>
                                        <span
                                            class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-semibold {{ $item->show_in_sales_order ? 'bg-[#5856D6]/10 text-[#5856D6]' : 'bg-black/[0.05] text-[#8E8E93]' }}">
                                            {{ __('services.channels.sales_order') }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" @click="openEdit({{ Js::from($item) }})"
                                            class="min-h-[36px] px-3 rounded-[8px] text-[12px] font-medium text-[#1C1C1E] dark:text-[#F2F2F7] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] active:scale-[0.97] transition-all cursor-pointer">
                                            {{ __('services.actions.edit') }}
                                        </button>
                                        <button type="button"
                                            @click="openDelete('{{ $item->id }}', {{ Js::from($item->name) }})"
                                            class="min-h-[36px] px-3 rounded-[8px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/10 active:scale-[0.97] transition-all cursor-pointer">
                                            {{ __('services.actions.delete') }}
                                        </button>
                                        <form id="form-delete-{{ $item->id }}"
                                            action="{{ route('services.destroy', $item) }}" method="POST"
                                            class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-[#8E8E93] dark:text-[#98989D]">
                                    <i data-lucide="briefcase" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                                    <div class="font-bold text-sm text-[#1C1C1E] dark:text-[#F2F2F7]">{{ __('services.empty.search_empty_title') }}</div>
                                    <div class="text-xs text-[#8E8E93] dark:text-[#98989D] mt-1">{{ __('services.empty.search_empty_subtitle') }}</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($services->hasPages())
                <div
                    class="px-4 py-3 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between text-[13px] text-[#8E8E93]">
                    {{ $services->links() }}
                </div>
            @endif
        </div>

        <!-- ===================================================== -->
        <!-- 5. MOBILE GROUPED INSET LIST (Standar Apple HIG)      -->
        <!-- ===================================================== -->
        <div class="sm:hidden space-y-3">
            @forelse($services as $item)
                <div
                    class="rounded-[20px] bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] p-4 shadow-xs space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <h3 class="text-[15px] font-bold text-[#1C1C1E] dark:text-[#F2F2F7] leading-tight truncate">
                                {{ $item->name }}</h3>
                            <div class="text-[12px] text-[#8E8E93] dark:text-[#98989D] mt-0.5">
                                {{ $item->code ?? __('services.table.no_code') }} · {{ $item->category?->name ?? __('services.table.uncategorized') }}
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-[16px] font-bold text-[#007AFF] tabular-nums block">
                                Rp {{ number_format((float) $item->selling_price, 0, ',', '.') }}
                            </span>
                            <span class="text-[11px] text-[#8E8E93] dark:text-[#98989D]">/
                                {{ $item->outputUnit?->name ?? __('services.table.unit_fallback') }}</span>
                        </div>
                    </div>

                    @if ($item->description)
                        <p class="text-[12px] text-[#3C3C43]/70 dark:text-[#EBEBF5]/70 line-clamp-2">
                            {{ $item->description }}
                        </p>
                    @endif

                    <div
                        class="flex items-center justify-between pt-2 border-t border-black/[0.04] dark:border-white/[0.06]">
                        <div class="flex items-center gap-1.5">
                            <span
                                class="px-2 py-0.5 rounded-[6px] text-[10px] font-semibold {{ $item->show_in_pos ? 'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/[0.05] text-[#8E8E93]' }}">
                                {{ __('services.channels.pos') }}
                            </span>
                            <span
                                class="px-2 py-0.5 rounded-[6px] text-[10px] font-semibold {{ $item->show_in_website ? 'bg-[#007AFF]/10 text-[#007AFF]' : 'bg-black/[0.05] text-[#8E8E93]' }}">
                                {{ __('services.channels.website') }}
                            </span>
                            <span
                                class="px-2 py-0.5 rounded-[6px] text-[10px] font-semibold {{ $item->show_in_sales_order ? 'bg-[#5856D6]/10 text-[#5856D6]' : 'bg-black/[0.05] text-[#8E8E93]' }}">
                                {{ __('services.channels.sales_order') }}
                            </span>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <button type="button" @click="openEdit({{ Js::from($item) }})"
                                class="min-h-[44px] px-3.5 rounded-[10px] text-[12px] font-medium text-[#1C1C1E] dark:text-[#F2F2F7] bg-black/[0.04] dark:bg-white/[0.06] active:scale-95 transition-all cursor-pointer">
                                {{ __('services.actions.edit') }}
                            </button>
                            <button type="button"
                                @click="openDelete('{{ $item->id }}', {{ Js::from($item->name) }})"
                                class="min-h-[44px] px-3.5 rounded-[10px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/10 active:scale-95 transition-all cursor-pointer">
                                {{ __('services.actions.delete') }}
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div
                    class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 p-8 text-center text-[#8E8E93] border border-black/[0.06] dark:border-white/[0.08]">
                    <i data-lucide="briefcase" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                    <div class="font-bold text-sm text-[#1C1C1E] dark:text-[#F2F2F7]">{{ __('services.empty.search_empty_title') }}</div>
                    <div class="text-xs text-[#8E8E93] dark:text-[#98989D] mt-1">{{ __('services.empty.search_empty_subtitle') }}</div>
                </div>
            @endforelse

            @if ($services->hasPages())
                <div class="pt-2">
                    {{ $services->links() }}
                </div>
            @endif
        </div>

        <!-- ===================================================== -->
        <!-- 6. MODAL: TAMBAH LAYANAN (FULL LAYOUT XXL BENTO)      -->
        <!-- ===================================================== -->
        <div x-show="showAddModal" x-cloak
            class="fixed inset-0 z-[200] flex items-end sm:items-center justify-center p-0 sm:p-4 overflow-y-auto"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @keydown.escape.window="showAddModal = false">

            <!-- Frosted Dark Backdrop (Edge-to-Edge over topbar and layout) -->
            <div class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                @click="showAddModal = false"></div>

            <div class="relative z-10 w-full inset-x-0 bottom-0 rounded-t-[28px] sm:rounded-[24px] bg-white/98 dark:bg-[#1C1C1E]/98 backdrop-blur-2xl border border-black/[0.08] dark:border-white/[0.12] shadow-[0_25px_60px_rgba(0,0,0,0.35)] max-h-[95vh] sm:max-h-[92vh] flex flex-col overflow-hidden sm:max-w-[96vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-[1350px] transition-all"
                @click.outside="showAddModal = false">

                <div class="sm:hidden pt-2.5 pb-1 flex justify-center shrink-0">
                    <div class="w-10 h-1.5 rounded-full bg-black/20 dark:bg-white/20"></div>
                </div>

                <div
                    class="px-5 sm:px-6 py-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between shrink-0 bg-[#F2F2F7]/50 dark:bg-white/[0.02]">
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">
                            {{ __('services.title') }}</div>
                        <h3 class="text-[18px] sm:text-[22px] font-bold text-[#1C1C1E] dark:text-[#F2F2F7] tracking-tight">
                            {{ __('services.form.add_title') }}</h3>
                    </div>
                    <button type="button" @click="showAddModal = false"
                        class="w-9 h-9 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] text-black/60 dark:text-white/60 flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('services.store') }}"
                    class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                        <!-- Kolom Kiri: 7 Kolom (Identitas Layanan) -->
                        <div class="lg:col-span-7 space-y-5">
                            <div
                                class="rounded-[20px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/[0.04] dark:border-white/[0.06] p-4 sm:p-5 space-y-4">
                                <h4
                                    class="text-[12px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] pb-1 border-b border-black/[0.04] dark:border-white/[0.06]">
                                    {{ __('services.form.section_identity') }}</h4>

                                <div>
                                    <label
                                        class="block text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1.5">
                                        {{ __('services.form.field_name') }} <span class="text-[#FF3B30]">*</span>
                                    </label>
                                    <input type="text" name="name" x-model="addForm.name" required
                                        placeholder="{{ __('services.form.placeholder_name') }}"
                                        class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="block text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1.5">{{ __('services.form.field_code') }}</label>
                                        <input type="text" name="code" x-model="addForm.code"
                                            placeholder="{{ __('services.form.placeholder_code') }}"
                                            class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1.5">{{ __('services.form.field_unit') }}</label>
                                        <select name="output_unit_id" x-model="addForm.output_unit_id"
                                            class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3 text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                            <template x-for="u in units" :key="u.id">
                                                <option :value="u.id" x-text="u.name + ' (' + u.code + ')'">
                                                </option>
                                            </template>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label
                                            class="text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7]">{{ __('services.form.field_category') }}</label>
                                        <button type="button" @click="showAddCategoryModal = true"
                                            class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-0.5">
                                            <span>[ + ]</span> <span>{{ __('services.actions.quick_add_category') }}</span>
                                        </button>
                                    </div>
                                    <select name="category_id" x-model="addForm.category_id"
                                        class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3 text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                        <option value="">{{ __('services.form.select_category') }}</option>
                                        <template x-for="cat in categories" :key="cat.id">
                                            <option :value="cat.id" x-text="cat.name"></option>
                                        </template>
                                    </select>
                                </div>

                                <div>
                                    <label
                                        class="block text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1.5">{{ __('services.form.field_description') }}</label>
                                    <textarea name="description" x-model="addForm.description" rows="3"
                                        placeholder="{{ __('services.form.placeholder_description') }}"
                                        class="w-full p-3.5 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Kanan: 5 Kolom (Tarif & Kanal Penjualan) -->
                        <div class="lg:col-span-5 space-y-5">
                            <!-- Tarif & Biaya Modal -->
                            <div
                                class="rounded-[20px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/[0.04] dark:border-white/[0.06] p-4 sm:p-5 space-y-4">
                                <h4
                                    class="text-[12px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] pb-1 border-b border-black/[0.04] dark:border-white/[0.06]">
                                    {{ __('services.form.section_pricing') }}</h4>

                                <div>
                                    <label
                                        class="block text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1.5">
                                        {{ __('services.form.field_price') }} <span class="text-[#FF3B30]">*</span>
                                    </label>
                                    <input type="number" name="selling_price" x-model="addForm.selling_price" required
                                        min="0" step="100" placeholder="{{ __('services.form.placeholder_price') }}"
                                        class="w-full h-11 px-3.5 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[16px] sm:text-[15px] font-bold text-[#007AFF] tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                </div>

                                <div>
                                    <label
                                        class="block text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1.5">
                                        {{ __('services.form.field_cost') }}
                                    </label>
                                    <input type="number" name="base_cost" x-model="addForm.base_cost" min="0"
                                        step="100" placeholder="{{ __('services.form.placeholder_cost') }}"
                                        class="w-full h-11 px-3.5 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] font-semibold tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                    <span class="text-[11px] text-[#8E8E93] mt-1 block">{{ __('services.form.cost_hint') }}</span>
                                </div>
                            </div>

                            <!-- Multi-Channel Switches -->
                            <div
                                class="rounded-[20px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/[0.04] dark:border-white/[0.06] p-4 sm:p-5 space-y-3">
                                <h4
                                    class="text-[12px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] pb-1 border-b border-black/[0.04] dark:border-white/[0.06]">
                                    {{ __('services.form.section_channels') }}</h4>

                                <!-- Switch POS -->
                                <label
                                    class="flex items-center justify-between p-2.5 rounded-[12px] hover:bg-black/[0.02] dark:hover:bg-white/[0.02] cursor-pointer">
                                    <div>
                                        <span
                                            class="text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] block">{{ __('services.form.show_in_pos') }}</span>
                                        <span class="text-[11px] text-[#8E8E93]">{{ __('services.form.show_in_pos_desc') }}</span>
                                    </div>
                                    <input type="hidden" name="show_in_pos" value="0">
                                    <input type="checkbox" name="show_in_pos" value="1"
                                        x-model="addForm.show_in_pos"
                                        class="w-5 h-5 rounded-[6px] text-[#007AFF] focus:ring-[#007AFF]/50 border-black/20 dark:border-white/20">
                                </label>

                                <!-- Switch Sales Order -->
                                <label
                                    class="flex items-center justify-between p-2.5 rounded-[12px] hover:bg-black/[0.02] dark:hover:bg-white/[0.02] cursor-pointer">
                                    <div>
                                        <span
                                            class="text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] block">{{ __('services.form.show_in_sales_order') }}</span>
                                        <span class="text-[11px] text-[#8E8E93]">{{ __('services.form.show_in_sales_order_desc') }}</span>
                                    </div>
                                    <input type="hidden" name="show_in_sales_order" value="0">
                                    <input type="checkbox" name="show_in_sales_order" value="1"
                                        x-model="addForm.show_in_sales_order"
                                        class="w-5 h-5 rounded-[6px] text-[#007AFF] focus:ring-[#007AFF]/50 border-black/20 dark:border-white/20">
                                </label>

                                <!-- Switch Website Storefront -->
                                <label
                                    class="flex items-center justify-between p-2.5 rounded-[12px] hover:bg-black/[0.02] dark:hover:bg-white/[0.02] cursor-pointer">
                                    <div>
                                        <span
                                            class="text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] block">{{ __('services.form.show_in_website') }}</span>
                                        <span class="text-[11px] text-[#8E8E93]">{{ __('services.form.show_in_website_desc') }}</span>
                                    </div>
                                    <input type="hidden" name="show_in_website" value="0">
                                    <input type="checkbox" name="show_in_website" value="1"
                                        x-model="addForm.show_in_website"
                                        class="w-5 h-5 rounded-[6px] text-[#007AFF] focus:ring-[#007AFF]/50 border-black/20 dark:border-white/20">
                                </label>

                                <!-- Switch Show Price on Web -->
                                <label
                                    class="flex items-center justify-between p-2.5 rounded-[12px] hover:bg-black/[0.02] dark:hover:bg-white/[0.02] cursor-pointer">
                                    <div>
                                        <span
                                            class="text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] block">{{ __('services.form.show_price_on_web') }}</span>
                                        <span class="text-[11px] text-[#8E8E93]">{{ __('services.form.show_price_on_web_desc') }}</span>
                                    </div>
                                    <input type="hidden" name="show_price_on_web" value="0">
                                    <input type="checkbox" name="show_price_on_web" value="1"
                                        x-model="addForm.show_price_on_web"
                                        class="w-5 h-5 rounded-[6px] text-[#007AFF] focus:ring-[#007AFF]/50 border-black/20 dark:border-white/20">
                                </label>
                            </div>
                        </div>
                    </div>

                    <div
                        class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-end gap-3 sticky bottom-0 bg-white dark:bg-[#1C1C1E] pb-2 sm:pb-0">
                        <button type="button" @click="showAddModal = false"
                            class="h-11 px-5 rounded-[12px] text-[14px] font-medium text-[#1C1C1E] dark:text-[#F2F2F7] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition-colors min-h-[44px]">
                            {{ __('services.actions.cancel') }}
                        </button>
                        <button type="submit"
                            class="h-11 px-6 rounded-[12px] text-[14px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all shadow-sm shadow-[#007AFF]/25 min-h-[44px]">
                            {{ __('services.actions.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- 7. MODAL: EDIT LAYANAN (FULL LAYOUT XXL BENTO)        -->
        <!-- ===================================================== -->
        <div x-show="showEditModal" x-cloak
            class="fixed inset-0 z-[200] flex items-end sm:items-center justify-center p-0 sm:p-4 overflow-y-auto"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @keydown.escape.window="showEditModal = false">

            <!-- Frosted Dark Backdrop (Edge-to-Edge over topbar and layout) -->
            <div class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                @click="showEditModal = false"></div>

            <div class="relative z-10 w-full inset-x-0 bottom-0 rounded-t-[28px] sm:rounded-[24px] bg-white/98 dark:bg-[#1C1C1E]/98 backdrop-blur-2xl border border-black/[0.08] dark:border-white/[0.12] shadow-[0_25px_60px_rgba(0,0,0,0.35)] max-h-[95vh] sm:max-h-[92vh] flex flex-col overflow-hidden sm:max-w-[96vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-[1350px] transition-all"
                @click.outside="showEditModal = false">

                <div class="sm:hidden pt-2.5 pb-1 flex justify-center shrink-0">
                    <div class="w-10 h-1.5 rounded-full bg-black/20 dark:bg-white/20"></div>
                </div>

                <div
                    class="px-5 sm:px-6 py-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between shrink-0 bg-[#F2F2F7]/50 dark:bg-white/[0.02]">
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">
                            {{ __('services.form.edit_title') }}</div>
                        <h3 class="text-[18px] sm:text-[22px] font-bold text-[#1C1C1E] dark:text-[#F2F2F7] tracking-tight">
                            {{ __('services.form.edit_subtitle') }}</h3>
                    </div>
                    <button type="button" @click="showEditModal = false"
                        class="w-9 h-9 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] text-black/60 dark:text-white/60 flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form :action="'/services/' + editForm.id" method="POST"
                    class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                        <!-- Kolom Kiri: 7 Kolom (Identitas Layanan) -->
                        <div class="lg:col-span-7 space-y-5">
                            <div
                                class="rounded-[20px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/[0.04] dark:border-white/[0.06] p-4 sm:p-5 space-y-4">
                                <h4
                                    class="text-[12px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] pb-1 border-b border-black/[0.04] dark:border-white/[0.06]">
                                    {{ __('services.form.section_identity') }}</h4>

                                <div>
                                    <label
                                        class="block text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1.5">
                                        {{ __('services.form.field_name') }} <span class="text-[#FF3B30]">*</span>
                                    </label>
                                    <input type="text" name="name" x-model="editForm.name" required
                                        class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="block text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1.5">{{ __('services.form.field_code') }}</label>
                                        <input type="text" name="code" x-model="editForm.code"
                                            class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1.5">{{ __('services.form.field_unit') }}</label>
                                        <select name="output_unit_id" x-model="editForm.output_unit_id"
                                            class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3 text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                            <template x-for="u in units" :key="u.id">
                                                <option :value="u.id" x-text="u.name + ' (' + u.code + ')'">
                                                </option>
                                            </template>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label
                                            class="text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7]">{{ __('services.form.field_category') }}</label>
                                        <button type="button" @click="showAddCategoryModal = true"
                                            class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-0.5">
                                            <span>[ + ]</span> <span>{{ __('services.actions.quick_add_category') }}</span>
                                        </button>
                                    </div>
                                    <select name="category_id" x-model="editForm.category_id"
                                        class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3 text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                        <option value="">{{ __('services.form.select_category') }}</option>
                                        <template x-for="cat in categories" :key="cat.id">
                                            <option :value="cat.id" x-text="cat.name"></option>
                                        </template>
                                    </select>
                                </div>

                                <div>
                                    <label
                                        class="block text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1.5">{{ __('services.form.field_description') }}</label>
                                    <textarea name="description" x-model="editForm.description" rows="3"
                                        class="w-full p-3.5 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Kanan: 5 Kolom (Tarif & Kanal Penjualan) -->
                        <div class="lg:col-span-5 space-y-5">
                            <div
                                class="rounded-[20px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/[0.04] dark:border-white/[0.06] p-4 sm:p-5 space-y-4">
                                <h4
                                    class="text-[12px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] pb-1 border-b border-black/[0.04] dark:border-white/[0.06]">
                                    {{ __('services.form.section_pricing') }}</h4>

                                <div>
                                    <label
                                        class="block text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1.5">
                                        {{ __('services.form.field_price') }} <span class="text-[#FF3B30]">*</span>
                                    </label>
                                    <input type="number" name="selling_price" x-model="editForm.selling_price" required
                                        min="0" step="100"
                                        class="w-full h-11 px-3.5 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[16px] sm:text-[15px] font-bold text-[#007AFF] tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                </div>

                                <div>
                                    <label
                                        class="block text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1.5">
                                        {{ __('services.form.field_cost') }}
                                    </label>
                                    <input type="number" name="base_cost" x-model="editForm.base_cost" min="0"
                                        step="100"
                                        class="w-full h-11 px-3.5 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[16px] sm:text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] font-semibold tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                </div>
                            </div>

                            <!-- Multi-Channel Switches -->
                            <div
                                class="rounded-[20px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/[0.04] dark:border-white/[0.06] p-4 sm:p-5 space-y-3">
                                <h4
                                    class="text-[12px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] pb-1 border-b border-black/[0.04] dark:border-white/[0.06]">
                                    {{ __('services.form.section_channels') }}</h4>

                                <!-- Switch POS -->
                                <label
                                    class="flex items-center justify-between p-2.5 rounded-[12px] hover:bg-black/[0.02] dark:hover:bg-white/[0.02] cursor-pointer">
                                    <div>
                                        <span
                                            class="text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] block">{{ __('services.form.show_in_pos') }}</span>
                                        <span class="text-[11px] text-[#8E8E93]">{{ __('services.form.show_in_pos_desc') }}</span>
                                    </div>
                                    <input type="hidden" name="show_in_pos" value="0">
                                    <input type="checkbox" name="show_in_pos" value="1"
                                        x-model="editForm.show_in_pos"
                                        class="w-5 h-5 rounded-[6px] text-[#007AFF] focus:ring-[#007AFF]/50 border-black/20 dark:border-white/20">
                                </label>

                                <!-- Switch Sales Order -->
                                <label
                                    class="flex items-center justify-between p-2.5 rounded-[12px] hover:bg-black/[0.02] dark:hover:bg-white/[0.02] cursor-pointer">
                                    <div>
                                        <span
                                            class="text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] block">{{ __('services.form.show_in_sales_order') }}</span>
                                        <span class="text-[11px] text-[#8E8E93]">{{ __('services.form.show_in_sales_order_desc') }}</span>
                                    </div>
                                    <input type="hidden" name="show_in_sales_order" value="0">
                                    <input type="checkbox" name="show_in_sales_order" value="1"
                                        x-model="editForm.show_in_sales_order"
                                        class="w-5 h-5 rounded-[6px] text-[#007AFF] focus:ring-[#007AFF]/50 border-black/20 dark:border-white/20">
                                </label>

                                <!-- Switch Website Storefront -->
                                <label
                                    class="flex items-center justify-between p-2.5 rounded-[12px] hover:bg-black/[0.02] dark:hover:bg-white/[0.02] cursor-pointer">
                                    <div>
                                        <span
                                            class="text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] block">{{ __('services.form.show_in_website') }}</span>
                                        <span class="text-[11px] text-[#8E8E93]">{{ __('services.form.show_in_website_desc') }}</span>
                                    </div>
                                    <input type="hidden" name="show_in_website" value="0">
                                    <input type="checkbox" name="show_in_website" value="1"
                                        x-model="editForm.show_in_website"
                                        class="w-5 h-5 rounded-[6px] text-[#007AFF] focus:ring-[#007AFF]/50 border-black/20 dark:border-white/20">
                                </label>

                                <!-- Switch Show Price on Web -->
                                <label
                                    class="flex items-center justify-between p-2.5 rounded-[12px] hover:bg-black/[0.02] dark:hover:bg-white/[0.02] cursor-pointer">
                                    <div>
                                        <span
                                            class="text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] block">{{ __('services.form.show_price_on_web') }}</span>
                                        <span class="text-[11px] text-[#8E8E93]">{{ __('services.form.show_price_on_web_desc') }}</span>
                                    </div>
                                    <input type="hidden" name="show_price_on_web" value="0">
                                    <input type="checkbox" name="show_price_on_web" value="1"
                                        x-model="editForm.show_price_on_web"
                                        class="w-5 h-5 rounded-[6px] text-[#007AFF] focus:ring-[#007AFF]/50 border-black/20 dark:border-white/20">
                                </label>

                                <!-- Status Aktif -->
                                <label
                                    class="flex items-center justify-between p-2.5 rounded-[12px] hover:bg-black/[0.02] dark:hover:bg-white/[0.02] cursor-pointer border-t border-black/[0.04] dark:border-white/[0.06] pt-3">
                                    <div>
                                        <span
                                            class="text-[13px] font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] block">{{ __('services.form.is_active') }}</span>
                                        <span class="text-[11px] text-[#8E8E93]">{{ __('services.form.is_active_desc') }}</span>
                                    </div>
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" x-model="editForm.is_active"
                                        class="w-5 h-5 rounded-[6px] text-[#34C759] focus:ring-[#34C759]/50 border-black/20 dark:border-white/20">
                                </label>
                            </div>
                        </div>
                    </div>

                    <div
                        class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-end gap-3 sticky bottom-0 bg-white dark:bg-[#1C1C1E] pb-2 sm:pb-0">
                        <button type="button" @click="showEditModal = false"
                            class="h-11 px-5 rounded-[12px] text-[14px] font-medium text-[#1C1C1E] dark:text-[#F2F2F7] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition-colors min-h-[44px]">
                            {{ __('services.actions.cancel') }}
                        </button>
                        <button type="submit"
                            class="h-11 px-6 rounded-[12px] text-[14px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all shadow-sm shadow-[#007AFF]/25 min-h-[44px]">
                            {{ __('services.actions.update') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- 8. SUB-MODAL QUICK-ADD CATEGORY (Zero Page Reload)    -->
        <!-- ===================================================== -->
        <div x-show="showAddCategoryModal" x-cloak
            class="fixed inset-0 z-[210] flex items-center justify-center p-4 overflow-y-auto"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @keydown.escape.window="showAddCategoryModal = false">

            <!-- Frosted Dark Backdrop -->
            <div class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                @click="showAddCategoryModal = false"></div>

            <div class="relative z-10 w-full max-w-md rounded-[24px] bg-white/98 dark:bg-[#1C1C1E]/98 backdrop-blur-2xl border border-black/[0.08] dark:border-white/[0.12] p-5 sm:p-6 space-y-4 shadow-[0_25px_60px_rgba(0,0,0,0.35)]"
                @click.outside="showAddCategoryModal = false">
                <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                    <h3 class="text-[16px] font-bold text-[#1C1C1E] dark:text-[#F2F2F7]">{{ __('services.quick_category.title') }}</h3>
                    <button type="button" @click="showAddCategoryModal = false"
                        class="w-7 h-7 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center text-black/50 dark:text-white/50">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <template x-if="quickCat.error">
                    <div class="p-2.5 rounded-[10px] bg-[#FF3B30]/10 text-[#FF3B30] text-[12px] font-medium"
                        x-text="quickCat.error"></div>
                </template>

                <form @submit.prevent="submitQuickCategory" class="space-y-3.5 text-[13px]">
                    <div>
                        <label class="block font-semibold text-[#1C1C1E] dark:text-[#F2F2F7] mb-1">{{ __('services.quick_category.field_name') }} <span
                                class="text-[#FF3B30]">*</span></label>
                        <input type="text" x-model="quickCat.name" required
                            placeholder="{{ __('services.quick_category.placeholder_name') }}"
                            class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-[#1C1C1E] dark:text-[#F2F2F7] focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="showAddCategoryModal = false"
                            class="h-9 px-3.5 rounded-[10px] text-[13px] bg-black/[0.06] dark:bg-white/[0.08] text-[#1C1C1E] dark:text-[#F2F2F7] min-h-[44px]">{{ __('services.quick_category.cancel') }}</button>
                        <button type="submit" :disabled="quickCat.isSubmitting"
                            class="h-9 px-4 rounded-[10px] text-[13px] font-semibold bg-[#007AFF] text-white flex items-center gap-1.5 shadow-sm shadow-[#007AFF]/25 min-h-[44px]">
                            <span x-show="quickCat.isSubmitting">{{ __('services.quick_category.saving') }}</span>
                            <span x-show="!quickCat.isSubmitting">{{ __('services.quick_category.save') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- 9. APPLE ALERT DIALOG (Hapus Layanan)                 -->
        <!-- ===================================================== -->
        <div x-show="deleteModalOpen" x-cloak
            class="fixed inset-0 z-[220] flex items-center justify-center p-4 overflow-y-auto"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @keydown.escape.window="closeDelete()">

            <!-- Frosted Dark Backdrop -->
            <div class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                @click="closeDelete()"></div>

            <div class="relative z-10 w-full max-w-sm rounded-[24px] bg-white/98 dark:bg-[#2C2C2E]/98 backdrop-blur-2xl overflow-hidden shadow-[0_25px_60px_rgba(0,0,0,0.35)] border border-black/[0.08] dark:border-white/[0.12] text-center"
                @click.outside="closeDelete()">
                <div class="p-6">
                    <div
                        class="w-12 h-12 rounded-full bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-[17px] font-bold text-[#1C1C1E] dark:text-[#F2F2F7]">{{ __('services.delete_modal.title') }}</h3>
                    <p class="text-[13px] text-[#8E8E93] dark:text-[#98989D] mt-1.5 leading-relaxed">
                        {!! __('services.delete_modal.message', ['name' => '<strong class="text-[#1C1C1E] dark:text-[#F2F2F7]" x-text="deleteTarget.name"></strong>']) !!}
                    </p>

                    <!-- Penenang Jiwa Microcopy (Mandat Apple HIG) -->
                    <div
                        class="mt-4 p-3 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] text-left flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5" fill="none" stroke="currentColor"
                            stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-[11.5px] text-[#3C3C43]/70 dark:text-[#EBEBF5]/70 leading-relaxed">
                            {{ __('services.delete_modal.notice') }}
                        </p>
                    </div>
                </div>

                <div
                    class="grid grid-cols-2 border-t border-black/[0.06] dark:border-white/[0.08] text-[15px] font-medium">
                    <button type="button" @click="closeDelete()"
                        class="py-3.5 text-[#007AFF] border-r border-black/[0.06] dark:border-white/[0.08] active:bg-black/5 dark:active:bg-white/5 transition-colors min-h-[44px]">
                        {{ __('services.delete_modal.cancel') }}
                    </button>
                    <button type="button" @click="submitDelete()"
                        class="py-3.5 text-[#FF3B30] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors min-h-[44px]">
                        {{ __('services.delete_modal.confirm') }}
                    </button>
                </div>
            </div>
        </div>

    </div>
@endsection
