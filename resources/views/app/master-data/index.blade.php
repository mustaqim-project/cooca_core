@extends('layouts.app', [
    'title' => $title,
    'headerTitle' => $title,
    'headerSubtitle' => $subtitle,
])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="{
        showAddModal: false,
        showEditModal: false,
        activeTab: 'units',
        filterSource: 'all',
        searchQuery: '',
        marketplaceCategories: @js($marketplaceCategories ?? []),
        addItem: { name: '', description: '', marketplace_category_id: '', marketplace_category_name: '' },
        editItem: { id: '', code: '', name: '', category: 'quantity', description: '', marketplace_category_id: '', marketplace_category_name: '', cascade_to_products: true },
        categorySearchAdd: '',
        categorySearchEdit: '',
        categoryDropdownOpenAdd: false,
        categoryDropdownOpenEdit: false,
        deleteModalOpen: false,
        deleteTarget: { url: '', name: '' },
        openDelete(url, name) {
            this.deleteTarget = { url, name };
            this.deleteModalOpen = true;
        },
        closeDelete() {
            this.deleteModalOpen = false;
            this.deleteTarget = { url: '', name: '' };
        },
        submitDelete() {
            if (this.deleteTarget.url) {
                const form = document.getElementById('form-delete-master');
                form.action = this.deleteTarget.url;
                form.submit();
            }
        },
        openAdd() {
            this.addItem = { name: '', description: '', marketplace_category_id: '', marketplace_category_name: '' };
            this.categorySearchAdd = '';
            this.categoryDropdownOpenAdd = false;
            this.showAddModal = true;
        },
        openEdit(item) {
            this.editItem = {
                id: item.id || '',
                code: item.code || '',
                name: item.name || '',
                category: item.category || 'quantity',
                description: item.description || '',
                marketplace_category_id: item.marketplace_category_id || '',
                marketplace_category_name: item.marketplace_category_name || '',
                cascade_to_products: true
            };
            this.categorySearchEdit = '';
            this.categoryDropdownOpenEdit = false;
            this.showEditModal = true;
        },
        selectMarketplaceCategoryAdd(cat) {
            this.addItem.marketplace_category_id = cat.id;
            this.addItem.marketplace_category_name = cat.name;
            this.categoryDropdownOpenAdd = false;
            this.categorySearchAdd = '';
        },
        selectMarketplaceCategoryEdit(cat) {
            this.editItem.marketplace_category_id = cat.id;
            this.editItem.marketplace_category_name = cat.name;
            this.categoryDropdownOpenEdit = false;
            this.categorySearchEdit = '';
        },
        filteredCategories(query) {
            if (!query || !query.trim()) return this.marketplaceCategories;
            const q = query.toLowerCase().trim();
            return this.marketplaceCategories.filter(c =>
                c.name.toLowerCase().includes(q) ||
                (c.id && c.id.includes(q)) ||
                (c.description && c.description.toLowerCase().includes(q)) ||
                (c.keywords && c.keywords.some(k => k.toLowerCase().includes(q)))
            );
        },
        get selectedAddCategoryObj() {
            return this.marketplaceCategories.find(c => c.id === this.addItem.marketplace_category_id) || null;
        },
        get selectedEditCategoryObj() {
            return this.marketplaceCategories.find(c => c.id === this.editItem.marketplace_category_id) || null;
        },
        matchesSearch(code, name, category, desc, mpName) {
            if (!this.searchQuery) return true;
            const q = this.searchQuery.toLowerCase();
            return (code && code.toLowerCase().includes(q)) ||
                (name && name.toLowerCase().includes(q)) ||
                (category && category.toLowerCase().includes(q)) ||
                (desc && desc.toLowerCase().includes(q)) ||
                (mpName && mpName.toLowerCase().includes(q));
        },
        matchesSource(isBusiness) {
            if (this.filterSource === 'all') return true;
            if (this.filterSource === 'business') return !!isBusiness;
            if (this.filterSource === 'system') return !isBusiness;
            return true;
        }
    }">

        @php
            $activeModule = $module ?? ($type === 'product-categories' ? 'products' : 'materials');
            $modDef = \App\Support\Navigation\NavigationRegistry::getModule($activeModule);
            $parentLabel = $modDef['parent_breadcrumb']['label'] ?? ($activeModule === 'products' ? 'Produk' : 'Bahan Baku');
            $parentRoute = $modDef['parent_breadcrumb']['route'] ?? ($activeModule === 'products' ? 'products.index' : 'materials.index');
            $breadcrumbs = [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => $parentLabel, 'url' => route($parentRoute)],
                ['label' => $title, 'url' => null],
            ];
        @endphp

        <!-- ===================================================== -->
        <!-- 1. TOOLBAR / PAGE HEADER                                -->
        <!-- ===================================================== -->
        <x-module-header :title="$title" :subtitle="$subtitle" :breadcrumbs="$breadcrumbs">
            @if (in_array($type, ['material-categories', 'product-categories', 'units'], true) &&
                    \App\Support\Context::hasPermission("master_data.{$type}.manage"))
                <button type="button" @click="openAdd()"
                    class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-sm shadow-[#007AFF]/25">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Tambah {{ $type === 'units' ? 'Satuan' : 'Kategori' }}</span>
                </button>
            @endif
        </x-module-header>

        <!-- ===================================================== -->
        <!-- 2. PERSISTENT MODULE TABS                             -->
        <!-- ===================================================== -->
        <x-module-tabs :module="$activeModule" />

        <!-- ===================================================== -->
        <!-- FLASH MESSAGES (Apple HIG Banner Style)                -->
        <!-- ===================================================== -->
        @if (session('success'))
            <div
                class="rounded-[12px] bg-[#34C759]/10 border border-[#34C759]/20 px-4 py-3 text-[13px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-2.5">
                <svg class="w-4 h-4 shrink-0 text-[#34C759]" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div
                class="rounded-[12px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 px-4 py-3 text-[13px] text-[#C41E17] dark:text-[#FF453A] flex items-center gap-2.5">
                <svg class="w-4 h-4 shrink-0 text-[#FF3B30]" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        <!-- ===================================================== -->
        <!-- 2. KPI SUMMARY (Flat Neutral Apple HIG Cards)          -->
        <!-- ===================================================== -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            @if ($type === 'units')
                <!-- Tile 1: Total Satuan -->
                <div
                    class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Satuan</span>
                    <div class="mt-2 flex items-baseline justify-between">
                        <span
                            class="text-[24px] font-bold tabular-nums text-black dark:text-white">{{ $items->count() }}</span>
                        <span class="text-[11px] text-black/40 dark:text-white/40">Item Ukur</span>
                    </div>
                </div>
                <!-- Tile 2: Satuan Bisnis Ini -->
                <div
                    class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Kustom Bisnis</span>
                    <div class="mt-2 flex items-baseline justify-between">
                        <span
                            class="text-[24px] font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF]">{{ $items->whereNotNull('business_id')->count() }}</span>
                        <span class="text-[11px] font-medium text-[#007AFF]">Bisnis Ini</span>
                    </div>
                </div>
                <!-- Tile 3: Satuan Sistem Bawaan -->
                <div
                    class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Standar Sistem</span>
                    <div class="mt-2 flex items-baseline justify-between">
                        <span
                            class="text-[24px] font-bold tabular-nums text-[#5856D6] dark:text-[#5E5CE6]">{{ $items->whereNull('business_id')->count() }}</span>
                        <span class="text-[11px] text-black/40 dark:text-white/40">Bawaan</span>
                    </div>
                </div>
                <!-- Tile 4: Total Konversi Satuan -->
                <div
                    class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Konversi Satuan</span>
                    <div class="mt-2 flex items-baseline justify-between">
                        <span
                            class="text-[24px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">{{ isset($unitConversions) ? $unitConversions->count() : 0 }}</span>
                        <span class="text-[11px] font-medium text-[#34C759] dark:text-[#30D158]">Aktif</span>
                    </div>
                </div>
            @else
                <!-- Tile 1: Total Kategori -->
                <div
                    class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Kategori</span>
                    <div class="mt-2 flex items-baseline justify-between">
                        <span
                            class="text-[24px] font-bold tabular-nums text-black dark:text-white">{{ $items->count() }}</span>
                        <span class="text-[11px] text-black/40 dark:text-white/40">Terdaftar</span>
                    </div>
                </div>
                <!-- Tile 2: Kategori Aktif -->
                <div
                    class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Kategori Aktif</span>
                    <div class="mt-2 flex items-baseline justify-between">
                        <span
                            class="text-[24px] font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF]">{{ $items->count() }}</span>
                        <span class="text-[11px] font-medium text-[#007AFF]">Digunakan</span>
                    </div>
                </div>
                <!-- Tile 3: Pembaruan Terakhir -->
                <div
                    class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Status Data</span>
                    <div class="mt-2 flex items-baseline justify-between">
                        <span class="text-[20px] font-semibold text-black dark:text-white truncate">Tersinkron</span>
                        <span class="text-[11px] text-[#34C759] dark:text-[#30D158] font-medium">Valid</span>
                    </div>
                </div>
                <!-- Tile 4: Ruang Lingkup -->
                <div
                    class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Cakupan</span>
                    <div class="mt-2 flex items-baseline justify-between">
                        <span class="text-[20px] font-semibold text-black dark:text-white">Bisnis</span>
                        <span class="text-[11px] text-black/40 dark:text-white/40">Multi-Lokasi</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- ===================================================== -->
        <!-- 3. CONTROLS: SEGMENTED CONTROLS & macOS SEARCH BAR    -->
        <!-- ===================================================== -->
        <div
            class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 sm:p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <!-- Search Field (macOS Style: Clean Fill, No Thick Border) -->
            <div class="relative flex-1 max-w-md">
                <svg class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input type="text" x-model="searchQuery" placeholder="Cari {{ strtolower($title) }}..."
                    class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-9 pr-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>

            @if ($type === 'units')
                <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                    <!-- Source Filter (All / Business / System) -->
                    <div x-show="activeTab === 'units'"
                        class="inline-flex p-0.5 rounded-[9px] bg-black/[0.06] dark:bg-white/[0.08] text-[13px] font-medium">
                        <button type="button" @click="filterSource = 'all'"
                            :class="filterSource === 'all' ?
                                'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' :
                                'text-black/55 dark:text-white/55'"
                            class="px-3 py-1 rounded-[7px] transition-all">
                            Semua
                        </button>
                        <button type="button" @click="filterSource = 'business'"
                            :class="filterSource === 'business' ?
                                'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' :
                                'text-black/55 dark:text-white/55'"
                            class="px-3 py-1 rounded-[7px] transition-all">
                            Bisnis
                        </button>
                        <button type="button" @click="filterSource = 'system'"
                            :class="filterSource === 'system' ?
                                'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' :
                                'text-black/55 dark:text-white/55'"
                            class="px-3 py-1 rounded-[7px] transition-all">
                            Sistem
                        </button>
                    </div>

                    <!-- Tab Segmented Control -->
                    <div
                        class="inline-flex p-0.5 rounded-[9px] bg-black/[0.06] dark:bg-white/[0.08] text-[13px] font-medium">
                        <button type="button" @click="activeTab = 'units'"
                            :class="activeTab === 'units' ?
                                'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' :
                                'text-black/55 dark:text-white/55'"
                            class="px-3.5 py-1 rounded-[7px] transition-all flex items-center gap-1.5">
                            <span>Daftar Satuan</span>
                            <span class="text-[11px] font-semibold opacity-70 tabular-nums">({{ $items->count() }})</span>
                        </button>
                        <button type="button" @click="activeTab = 'conversions'"
                            :class="activeTab === 'conversions' ?
                                'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' :
                                'text-black/55 dark:text-white/55'"
                            class="px-3.5 py-1 rounded-[7px] transition-all flex items-center gap-1.5">
                            <span>Konversi</span>
                            <span
                                class="text-[11px] font-semibold opacity-70 tabular-nums">({{ isset($unitConversions) ? $unitConversions->count() : 0 }})</span>
                        </button>
                    </div>
                </div>
            @endif
        </div>

        <!-- ===================================================== -->
        <!-- 4. MAIN DATA (DAFTAR SATUAN / KATEGORI)               -->
        <!-- ===================================================== -->
        <div x-show="activeTab === 'units'" class="space-y-4">
            <!-- 4A. Desktop & Tablet Dense Table -->
            <div
                class="hidden sm:block rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[13px]">
                        <thead>
                            <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02]">
                                <th
                                    class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">
                                    {{ $type === 'units' ? 'Kode Satuan' : 'Nama Kategori' }}
                                </th>
                                @if ($type === 'units')
                                    <th
                                        class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">
                                        Nama Lengkap</th>
                                    <th
                                        class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">
                                        Dimensi</th>
                                    <th
                                        class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">
                                        Sumber</th>
                                @elseif ($type === 'product-categories')
                                    <th
                                        class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">
                                        Kategori Marketplace Resmi (TikTok Shop / Tokopedia / Shopee)</th>
                                    <th
                                        class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">
                                        Deskripsi</th>
                                    <th
                                        class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">
                                        Slug</th>
                                @else
                                    <th
                                        class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">
                                        Deskripsi</th>
                                    <th
                                        class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">
                                        Slug</th>
                                @endif
                                <th
                                    class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right whitespace-nowrap">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                            @forelse($items as $item)
                                <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors"
                                    x-show="matchesSearch('{{ addslashes($item->code ?? '') }}', '{{ addslashes($item->name ?? '') }}', '{{ addslashes($item->category ?? '') }}', '{{ addslashes($item->description ?? '') }}', '{{ addslashes($item->marketplace_category_name ?? '') }}') && matchesSource({{ $item->business_id ? 'true' : 'false' }})">
                                    @if ($type === 'units')
                                        <td class="py-3 px-4 font-semibold text-black dark:text-white tabular-nums">
                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="w-2 h-2 rounded-full {{ $item->business_id ? 'bg-[#007AFF]' : 'bg-black/30 dark:bg-white/30' }}"></span>
                                                <span>{{ $item->code }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-black/80 dark:text-white/80 font-medium">
                                            {{ $item->name }}</td>
                                        <td class="py-3 px-4 text-black/60 dark:text-white/60 capitalize">
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-black/[0.05] dark:bg-white/[0.06] text-black/70 dark:text-white/70">
                                                {{ $item->category }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $item->business_id ? 'bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF]' : 'bg-black/[0.06] dark:bg-white/[0.08] text-black/60 dark:text-white/60' }}">
                                                {{ $item->business_id ? 'Bisnis Ini' : 'Sistem Bawaan' }}
                                            </span>
                                        </td>
                                    @elseif ($type === 'product-categories')
                                        <td class="py-3 px-4 font-semibold text-black dark:text-white">
                                            <div class="flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-[#007AFF]"></span>
                                                <span>{{ $item->name }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($item->marketplace_category_id)
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[10px] text-[11.5px] font-semibold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                                                    <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                                                    <span>{{ $item->marketplace_category_name ?? $item->marketplace_category_id }}</span>
                                                    <span class="font-mono text-[10.5px] opacity-75">({{ $item->marketplace_category_id }})</span>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[10px] text-[11px] font-medium text-black/40 dark:text-white/40 bg-black/5 dark:bg-white/5 border border-black/5 dark:border-white/5">
                                                    <span>Belum Dipetakan</span>
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-black/60 dark:text-white/60 max-w-xs truncate">
                                            {{ $item->description ?: '-' }}</td>
                                        <td class="py-3 px-4 text-black/40 dark:text-white/40 tabular-nums text-[12px]">
                                            {{ $item->slug ?? '-' }}</td>
                                    @else
                                        <td class="py-3 px-4 font-semibold text-black dark:text-white">
                                            <div class="flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-[#007AFF]"></span>
                                                <span>{{ $item->name }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-black/60 dark:text-white/60 max-w-md truncate">
                                            {{ $item->description ?: '-' }}</td>
                                        <td class="py-3 px-4 text-black/40 dark:text-white/40 tabular-nums text-[12px]">
                                            {{ $item->slug ?? '-' }}</td>
                                    @endif
                                    <td class="py-3 px-4 text-right">
                                        @if (
                                            ($item->business_id || !in_array($type, ['units'], true)) &&
                                                \App\Support\Context::hasPermission("master_data.{$type}.manage"))
                                            <div class="flex items-center justify-end gap-1">
                                                <button type="button" title="Edit"
                                                    @click="openEdit(@js(['id' => $item->id, 'code' => $item->code ?? '', 'name' => $item->name ?? '', 'category' => $item->category ?? 'quantity', 'description' => $item->description ?? '', 'marketplace_category_id' => $item->marketplace_category_id ?? '', 'marketplace_category_name' => $item->marketplace_category_name ?? '']))"
                                                    class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors flex items-center cursor-pointer">
                                                    Edit
                                                </button>
                                                <button type="button" title="Hapus"
                                                    @click="openDelete('{{ route($type . '.destroy', $item->id) }}', '{{ addslashes($item->name ?? $item->code) }}')"
                                                    class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/8 transition-colors flex items-center cursor-pointer">
                                                    Hapus
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-black/30 dark:text-white/30 text-[12px]">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $type === 'product-categories' ? 6 : 5 }}" class="py-12 text-center text-black/40 dark:text-white/40">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <svg class="w-10 h-10 text-black/20 dark:text-white/20" fill="none"
                                                stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
                                            </svg>
                                            <p class="text-[13px] font-medium">{{ $emptyLabel }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 4B. Mobile iOS Grouped Inset List (sm:hidden) -->
            <div
                class="sm:hidden rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                @forelse($items as $item)
                    <div class="p-3.5 flex items-center justify-between gap-3 active:bg-black/[0.02] dark:active:bg-white/[0.03] transition-colors"
                        x-show="matchesSearch('{{ addslashes($item->code ?? '') }}', '{{ addslashes($item->name ?? '') }}', '{{ addslashes($item->category ?? '') }}', '{{ addslashes($item->description ?? '') }}', '{{ addslashes($item->marketplace_category_name ?? '') }}') && matchesSource({{ $item->business_id ? 'true' : 'false' }})">
                        <div class="min-w-0 flex-1 space-y-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span
                                    class="w-2 h-2 rounded-full {{ $item->business_id ? 'bg-[#007AFF]' : 'bg-black/30 dark:bg-white/30' }} shrink-0"></span>
                                <p class="text-[15px] font-medium text-black dark:text-white truncate">
                                    {{ $type === 'units' ? $item->code : $item->name }}
                                </p>
                                @if ($type === 'units')
                                    <span
                                        class="text-[11px] px-1.5 py-0.2 rounded-full font-semibold {{ $item->business_id ? 'bg-[#007AFF]/12 text-[#007AFF]' : 'bg-black/[0.06] dark:bg-white/[0.08] text-black/55 dark:text-white/55' }}">
                                        {{ $item->business_id ? 'Bisnis' : 'Sistem' }}
                                    </span>
                                @elseif ($type === 'product-categories' && $item->marketplace_category_id)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-[#007AFF]/10 text-[#007AFF]">
                                        <i data-lucide="layers" class="w-3 h-3"></i>
                                        <span>{{ $item->marketplace_category_name ?? $item->marketplace_category_id }}</span>
                                    </span>
                                @endif
                            </div>
                            <p class="text-[13px] text-black/50 dark:text-white/50 truncate">
                                @if ($type === 'units')
                                    {{ $item->name }} · {{ ucfirst($item->category) }}
                                @else
                                    {{ $item->description ?: 'Tidak ada deskripsi' }}
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            @if (
                                ($item->business_id || !in_array($type, ['units'], true)) &&
                                    \App\Support\Context::hasPermission("master_data.{$type}.manage"))
                                <button type="button" @click="openEdit(@js(['id' => $item->id, 'code' => $item->code ?? '', 'name' => $item->name ?? '', 'category' => $item->category ?? 'quantity', 'description' => $item->description ?? '', 'marketplace_category_id' => $item->marketplace_category_id ?? '', 'marketplace_category_name' => $item->marketplace_category_name ?? '']))"
                                    class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-[#007AFF] bg-[#007AFF]/10 flex items-center cursor-pointer">
                                    Edit
                                </button>
                                <button type="button"
                                    @click="openDelete('{{ route($type . '.destroy', $item->id) }}', '{{ addslashes($item->name ?? $item->code) }}')"
                                    class="h-8 w-8 rounded-[8px] text-[#FF3B30] bg-[#FF3B30]/10 flex items-center justify-center cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                @empty
                    <div class="p-8 text-center text-black/40 dark:text-white/40 text-[13px]">
                        {{ $emptyLabel }}
                    </div>
                @endforelse
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- 5. TAB KONVERSI SATUAN (Apple HIG Standard)           -->
        <!-- ===================================================== -->
        @if ($type === 'units')
            <div x-show="activeTab === 'conversions'" class="space-y-5" style="display: none">
                @if (\App\Support\Context::hasPermission('master_data.units.manage'))
                    <div
                        class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 space-y-4">
                        <div>
                            <h2 class="text-[15px] font-semibold text-black dark:text-white">Tambah Konversi Satuan</h2>
                            <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Tentukan perbandingan nilai
                                konversi antar satuan (contoh: 1 sak = 25 kg)</p>
                        </div>
                        <form method="POST" action="{{ route('unit-conversions.store') }}"
                            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                            @csrf
                            <div>
                                <select name="from_unit_id" required
                                    class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                    <option value="">Satuan Asal (Dari)</option>
                                    @foreach ($items as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->code }} - {{ $unit->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <select name="to_unit_id" required
                                    class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                    <option value="">Satuan Tujuan (Ke)</option>
                                    @foreach ($items as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->code }} - {{ $unit->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <input type="number" name="factor" required min="0.000001" step="0.000001"
                                    placeholder="Faktor Pengali (misal: 1000)"
                                    class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            </div>
                            <div>
                                <button type="submit"
                                    class="w-full h-10 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                    <span>Simpan Konversi</span>
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                <!-- Conversions Dense Table (Desktop) -->
                <div
                    class="hidden sm:block rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-[13px]">
                            <thead>
                                <tr
                                    class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02]">
                                    <th
                                        class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">
                                        Satuan Asal</th>
                                    <th
                                        class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">
                                        Satuan Tujuan</th>
                                    <th
                                        class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">
                                        Faktor Pengali</th>
                                    <th
                                        class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                                @forelse($unitConversions as $conversion)
                                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                        <td class="py-3 px-4 font-semibold text-black dark:text-white">
                                            {{ $conversion->fromUnit?->code }}
                                            <span
                                                class="text-[12px] font-normal text-black/50 dark:text-white/50">({{ $conversion->fromUnit?->name }})</span>
                                        </td>
                                        <td class="py-3 px-4 font-semibold text-black dark:text-white">
                                            {{ $conversion->toUnit?->code }}
                                            <span
                                                class="text-[12px] font-normal text-black/50 dark:text-white/50">({{ $conversion->toUnit?->name }})</span>
                                        </td>
                                        <td
                                            class="py-3 px-4 tabular-nums font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                                            1 {{ $conversion->fromUnit?->code }} =
                                            {{ number_format((float) $conversion->factor, 6, '.', '') }}
                                            {{ $conversion->toUnit?->code }}
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            @if (\App\Support\Context::hasPermission('master_data.units.manage'))
                                                <button type="button"
                                                    @click="openDelete('{{ route('unit-conversions.destroy', $conversion->id) }}', 'Konversi 1 {{ $conversion->fromUnit?->code }} ke {{ $conversion->toUnit?->code }}')"
                                                    class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/8 transition-colors">
                                                    Hapus
                                                </button>
                                            @else
                                                <span class="text-black/30 dark:text-white/30 text-[12px]">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-12 text-center text-black/40 dark:text-white/40">
                                            Belum ada konversi satuan bisnis terdaftar.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Conversions Mobile Grouped Inset List (Mobile) -->
                <div
                    class="sm:hidden rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($unitConversions as $conversion)
                        <div
                            class="p-3.5 flex items-center justify-between gap-3 active:bg-black/[0.02] dark:active:bg-white/[0.03] transition-colors">
                            <div class="min-w-0 flex-1">
                                <p class="text-[15px] font-medium text-black dark:text-white">
                                    {{ $conversion->fromUnit?->code }} → {{ $conversion->toUnit?->code }}
                                </p>
                                <p class="text-[13px] text-[#007AFF] dark:text-[#0A84FF] tabular-nums mt-0.5 font-medium">
                                    1 {{ $conversion->fromUnit?->code }} =
                                    {{ number_format((float) $conversion->factor, 6, '.', '') }}
                                    {{ $conversion->toUnit?->code }}
                                </p>
                            </div>
                            @if (\App\Support\Context::hasPermission('master_data.units.manage'))
                                <button type="button"
                                    @click="openDelete('{{ route('unit-conversions.destroy', $conversion->id) }}', 'Konversi 1 {{ $conversion->fromUnit?->code }} ke {{ $conversion->toUnit?->code }}')"
                                    class="h-8 w-8 rounded-[8px] text-[#FF3B30] bg-[#FF3B30]/10 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                    @empty
                        <div class="p-8 text-center text-black/40 dark:text-white/40 text-[13px]">
                            Belum ada konversi satuan terdaftar.
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        <!-- ===================================================== -->
        <!-- 6. MODAL: TAMBAH DATA (Apple Sheet)                   -->
        <!-- ===================================================== -->
        @if (in_array($type, ['material-categories', 'product-categories', 'units'], true) &&
                \App\Support\Context::hasPermission("master_data.{$type}.manage"))
            <div x-show="showAddModal" style="display: none"
                class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/25 backdrop-blur-[2px]"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                <div @click.outside="showAddModal = false"
                    class="w-full sm:max-w-lg rounded-t-[20px] sm:rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] max-h-[90vh] overflow-y-auto"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95">

                    <!-- Mobile grabber -->
                    <div class="sm:hidden w-10 h-1 rounded-full bg-black/20 dark:bg-white/20 mx-auto mb-2"></div>

                    <div class="flex items-center justify-between border-b border-black/10 dark:border-white/10 pb-3">
                        <h3 class="text-[17px] font-semibold text-black dark:text-white">Tambah {{ $title }}</h3>
                        <button type="button" @click="showAddModal = false"
                            class="w-7 h-7 rounded-[6px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 flex items-center justify-center transition">✕</button>
                    </div>

                    <form method="POST" action="{{ route($type . '.store') }}" class="space-y-4 text-[13px]">
                        @csrf
                        @if ($type === 'units')
                            <div>
                                <label class="block font-medium text-black/70 dark:text-white/70 mb-1.5">Kode Satuan <span
                                        class="text-[#FF3B30]">*</span></label>
                                <input name="code" required maxlength="30" placeholder="Contoh: sak, botol, pack"
                                    class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white tabular-nums placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            </div>
                            <div>
                                <label class="block font-medium text-black/70 dark:text-white/70 mb-1.5">Nama Lengkap
                                    Satuan <span class="text-[#FF3B30]">*</span></label>
                                <input name="name" required maxlength="100"
                                    placeholder="Nama lengkap, contoh: Sak 25kg"
                                    class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            </div>
                            <div>
                                <label class="block font-medium text-black/70 dark:text-white/70 mb-1.5">Kategori Dimensi
                                    <span class="text-[#FF3B30]">*</span></label>
                                <select name="category" required
                                    class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                    <option value="quantity">Kuantitas (Quantity)</option>
                                    <option value="weight">Berat (Weight)</option>
                                    <option value="volume">Volume (Volume)</option>
                                    <option value="length">Panjang (Length)</option>
                                    <option value="time">Waktu (Time)</option>
                                    <option value="area">Luas (Area)</option>
                                    <option value="custom">Kustom (Custom)</option>
                                </select>
                            </div>
                        @else
                            <div>
                                <label class="block font-medium text-black/70 dark:text-white/70 mb-1.5">Nama Kategori
                                    <span class="text-[#FF3B30]">*</span></label>
                                <input name="name" x-model="addItem.name" required maxlength="150" placeholder="{{ $nameLabel }}"
                                    class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            </div>

                            @if ($type === 'product-categories')
                                <!-- Bento Card: Marketplace Category Selector -->
                                <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label class="block text-[12.5px] font-bold text-black dark:text-white">
                                                Kategori Resmi Marketplace
                                            </label>
                                            <p class="text-[11px] text-black/50 dark:text-white/50">
                                                Otomatis terwariskan ke seluruh produk dalam kategori ini
                                            </p>
                                        </div>
                                        <template x-if="selectedAddCategoryObj">
                                            <span class="text-[10.5px] font-mono font-bold px-2 py-0.5 rounded-full bg-[#007AFF]/10 text-[#007AFF]">
                                                ID: <span x-text="addItem.marketplace_category_id"></span>
                                            </span>
                                        </template>
                                    </div>

                                    <!-- Active Selection Card / Trigger Button -->
                                    <div class="relative">
                                        <button type="button" @click="categoryDropdownOpenAdd = !categoryDropdownOpenAdd"
                                            class="w-full p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.1] dark:border-white/[0.12] hover:border-[#007AFF] transition-all flex items-center justify-between text-left gap-2.5 shadow-2xs cursor-pointer">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <div class="w-8 h-8 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                                                    <i data-lucide="layers" class="w-4 h-4"></i>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="text-[12.5px] font-bold text-black dark:text-white truncate" x-text="selectedAddCategoryObj ? selectedAddCategoryObj.name : 'Pilih Kategori Marketplace...'"></div>
                                                    <div class="text-[10.5px] text-black/50 dark:text-white/50 truncate max-w-[240px] sm:max-w-xs" x-text="selectedAddCategoryObj ? selectedAddCategoryObj.description : 'Klik untuk mencari atau memilih kategori resmi'"></div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1 shrink-0 text-black/40 dark:text-white/40">
                                                <span class="text-[11px] font-medium hidden sm:inline" x-text="categoryDropdownOpenAdd ? 'Tutup' : 'Pilih'"></span>
                                                <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform duration-200" :class="categoryDropdownOpenAdd ? 'rotate-180' : ''"></i>
                                            </div>
                                        </button>

                                        <input type="hidden" name="marketplace_category_id" :value="addItem.marketplace_category_id">
                                        <input type="hidden" name="marketplace_category_name" :value="addItem.marketplace_category_name">

                                        <!-- Searchable Dropdown Overlay Card -->
                                        <div x-show="categoryDropdownOpenAdd" @click.away="categoryDropdownOpenAdd = false" x-cloak
                                            x-transition:enter="transition ease-out duration-150"
                                            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                            x-transition:exit="transition ease-in duration-100"
                                            x-transition:exit-start="opacity-100 translate-y-0 scale-100"
                                            x-transition:exit-end="opacity-0 translate-y-2 scale-95"
                                            class="absolute z-50 left-0 right-0 top-full mt-1.5 p-2.5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/15 shadow-xl max-h-[300px] flex flex-col space-y-2">
                                            
                                            <div class="relative">
                                                <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                                                <input type="text" x-model="categorySearchAdd" placeholder="Cari nama atau id kategori..."
                                                    class="w-full h-8 pl-8 pr-3 rounded-[8px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30">
                                            </div>

                                            <div class="overflow-y-auto space-y-1 pr-1 flex-1 max-h-[200px] custom-scrollbar">
                                                <template x-for="cat in filteredCategories(categorySearchAdd)" :key="cat.id">
                                                    <button type="button" @click="selectMarketplaceCategoryAdd(cat)"
                                                        :class="addItem.marketplace_category_id === cat.id ? 'bg-[#007AFF]/10 border-[#007AFF]/30 text-[#007AFF]' : 'hover:bg-black/[0.03] dark:hover:bg-white/[0.05] border-transparent text-black dark:text-white'"
                                                        class="w-full p-2 rounded-[10px] border text-left flex items-center justify-between gap-2 transition-colors cursor-pointer group">
                                                        <div class="min-w-0 flex-1">
                                                            <div class="flex items-center gap-1.5">
                                                                <span class="text-[12px] font-semibold group-hover:text-[#007AFF] transition-colors" x-text="cat.name"></span>
                                                                <span class="text-[9.5px] font-mono px-1 py-0.2 rounded bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60" x-text="cat.id"></span>
                                                            </div>
                                                            <p class="text-[10.5px] text-black/50 dark:text-white/50 truncate mt-0.5" x-text="cat.description"></p>
                                                        </div>
                                                        <template x-if="addItem.marketplace_category_id === cat.id">
                                                            <i data-lucide="check" class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i>
                                                        </template>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div>
                                <label class="block font-medium text-black/70 dark:text-white/70 mb-1.5">Deskripsi
                                    (Opsional)</label>
                                <textarea name="description" x-model="addItem.description" maxlength="500" rows="3" placeholder="Deskripsi ringkas penggunaan kategori..."
                                    class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] p-3 text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50"></textarea>
                            </div>
                        @endif

                        <div
                            class="flex items-center justify-end gap-2 pt-3 border-t border-black/10 dark:border-white/10">
                            <button type="button" @click="showAddModal = false"
                                class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition shadow-[0_1px_2px_rgba(0,122,255,0.25)] cursor-pointer">
                                Simpan {{ $type === 'units' ? 'Satuan' : 'Kategori' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- ===================================================== -->
        <!-- 7. MODAL: EDIT DATA (Apple Sheet)                     -->
        <!-- ===================================================== -->
        @if (in_array($type, ['material-categories', 'product-categories', 'units'], true) &&
                \App\Support\Context::hasPermission("master_data.{$type}.manage"))
            <div x-show="showEditModal" style="display: none"
                class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/25 backdrop-blur-[2px]"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                <div @click.outside="showEditModal = false"
                    class="w-full sm:max-w-lg rounded-t-[20px] sm:rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] max-h-[90vh] overflow-y-auto"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95">

                    <!-- Mobile grabber -->
                    <div class="sm:hidden w-10 h-1 rounded-full bg-black/20 dark:bg-white/20 mx-auto mb-2"></div>

                    <div class="flex items-center justify-between border-b border-black/10 dark:border-white/10 pb-3">
                        <h3 class="text-[17px] font-semibold text-black dark:text-white">Edit {{ $title }}</h3>
                        <button type="button" @click="showEditModal = false"
                            class="w-7 h-7 rounded-[6px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 flex items-center justify-center transition cursor-pointer">✕</button>
                    </div>

                    <form :action="'/{{ $type }}/' + editItem.id" method="POST" class="space-y-4 text-[13px]">
                        @csrf
                        @method('PUT')

                        @if ($type === 'units')
                            <div>
                                <label class="block font-medium text-black/70 dark:text-white/70 mb-1.5">Kode Satuan <span
                                        class="text-[#FF3B30]">*</span></label>
                                <input name="code" x-model="editItem.code" required maxlength="30"
                                    placeholder="Kode satuan"
                                    class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            </div>
                            <div>
                                <label class="block font-medium text-black/70 dark:text-white/70 mb-1.5">Nama Lengkap
                                    Satuan <span class="text-[#FF3B30]">*</span></label>
                                <input name="name" x-model="editItem.name" required maxlength="100"
                                    placeholder="Nama lengkap satuan"
                                    class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            </div>
                            <div>
                                <label class="block font-medium text-black/70 dark:text-white/70 mb-1.5">Kategori Dimensi
                                    <span class="text-[#FF3B30]">*</span></label>
                                <select name="category" x-model="editItem.category" required
                                    class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                    <option value="quantity">Kuantitas (Quantity)</option>
                                    <option value="weight">Berat (Weight)</option>
                                    <option value="volume">Volume (Volume)</option>
                                    <option value="length">Panjang (Length)</option>
                                    <option value="time">Waktu (Time)</option>
                                    <option value="area">Luas (Area)</option>
                                    <option value="custom">Kustom (Custom)</option>
                                </select>
                            </div>
                        @else
                            <div>
                                <label class="block font-medium text-black/70 dark:text-white/70 mb-1.5">Nama Kategori
                                    <span class="text-[#FF3B30]">*</span></label>
                                <input name="name" x-model="editItem.name" required maxlength="150"
                                    placeholder="{{ $nameLabel }}"
                                    class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            </div>

                            @if ($type === 'product-categories')
                                <!-- Bento Card: Marketplace Category Selector for Edit -->
                                <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label class="block text-[12.5px] font-bold text-black dark:text-white">
                                                Kategori Resmi Marketplace
                                            </label>
                                            <p class="text-[11px] text-black/50 dark:text-white/50">
                                                Otomatis terwariskan ke seluruh produk dalam kategori ini
                                            </p>
                                        </div>
                                        <template x-if="selectedEditCategoryObj">
                                            <span class="text-[10.5px] font-mono font-bold px-2 py-0.5 rounded-full bg-[#007AFF]/10 text-[#007AFF]">
                                                ID: <span x-text="editItem.marketplace_category_id"></span>
                                            </span>
                                        </template>
                                    </div>

                                    <!-- Active Selection Card / Trigger Button -->
                                    <div class="relative">
                                        <button type="button" @click="categoryDropdownOpenEdit = !categoryDropdownOpenEdit"
                                            class="w-full p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.1] dark:border-white/[0.12] hover:border-[#007AFF] transition-all flex items-center justify-between text-left gap-2.5 shadow-2xs cursor-pointer">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <div class="w-8 h-8 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                                                    <i data-lucide="layers" class="w-4 h-4"></i>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="text-[12.5px] font-bold text-black dark:text-white truncate" x-text="selectedEditCategoryObj ? selectedEditCategoryObj.name : 'Pilih Kategori Marketplace...'"></div>
                                                    <div class="text-[10.5px] text-black/50 dark:text-white/50 truncate max-w-[240px] sm:max-w-xs" x-text="selectedEditCategoryObj ? selectedEditCategoryObj.description : 'Klik untuk mencari atau memilih kategori resmi'"></div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1 shrink-0 text-black/40 dark:text-white/40">
                                                <span class="text-[11px] font-medium hidden sm:inline" x-text="categoryDropdownOpenEdit ? 'Tutup' : 'Ubah'"></span>
                                                <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform duration-200" :class="categoryDropdownOpenEdit ? 'rotate-180' : ''"></i>
                                            </div>
                                        </button>

                                        <input type="hidden" name="marketplace_category_id" :value="editItem.marketplace_category_id">
                                        <input type="hidden" name="marketplace_category_name" :value="editItem.marketplace_category_name">

                                        <!-- Searchable Dropdown Overlay Card -->
                                        <div x-show="categoryDropdownOpenEdit" @click.away="categoryDropdownOpenEdit = false" x-cloak
                                            x-transition:enter="transition ease-out duration-150"
                                            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                            x-transition:exit="transition ease-in duration-100"
                                            x-transition:exit-start="opacity-100 translate-y-0 scale-100"
                                            x-transition:exit-end="opacity-0 translate-y-2 scale-95"
                                            class="absolute z-50 left-0 right-0 top-full mt-1.5 p-2.5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/15 shadow-xl max-h-[300px] flex flex-col space-y-2">
                                            
                                            <div class="relative">
                                                <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                                                <input type="text" x-model="categorySearchEdit" placeholder="Cari nama atau id kategori..."
                                                    class="w-full h-8 pl-8 pr-3 rounded-[8px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30">
                                            </div>

                                            <div class="overflow-y-auto space-y-1 pr-1 flex-1 max-h-[200px] custom-scrollbar">
                                                <template x-for="cat in filteredCategories(categorySearchEdit)" :key="cat.id">
                                                    <button type="button" @click="selectMarketplaceCategoryEdit(cat)"
                                                        :class="editItem.marketplace_category_id === cat.id ? 'bg-[#007AFF]/10 border-[#007AFF]/30 text-[#007AFF]' : 'hover:bg-black/[0.03] dark:hover:bg-white/[0.05] border-transparent text-black dark:text-white'"
                                                        class="w-full p-2 rounded-[10px] border text-left flex items-center justify-between gap-2 transition-colors cursor-pointer group">
                                                        <div class="min-w-0 flex-1">
                                                            <div class="flex items-center gap-1.5">
                                                                <span class="text-[12px] font-semibold group-hover:text-[#007AFF] transition-colors" x-text="cat.name"></span>
                                                                <span class="text-[9.5px] font-mono px-1 py-0.2 rounded bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60" x-text="cat.id"></span>
                                                            </div>
                                                            <p class="text-[10.5px] text-black/50 dark:text-white/50 truncate mt-0.5" x-text="cat.description"></p>
                                                        </div>
                                                        <template x-if="editItem.marketplace_category_id === cat.id">
                                                            <i data-lucide="check" class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i>
                                                        </template>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Cascade to products checkbox -->
                                    <div class="pt-2 border-t border-black/[0.06] dark:border-white/[0.08] flex items-start gap-2">
                                        <input type="checkbox" id="cascade_to_products_edit" name="cascade_to_products" value="1" x-model="editItem.cascade_to_products"
                                            class="mt-0.5 rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF] cursor-pointer">
                                        <label for="cascade_to_products_edit" class="text-[11.5px] font-medium text-black/80 dark:text-white/80 cursor-pointer select-none leading-tight">
                                            Terapkan kategori marketplace ini ke seluruh produk yang ada di dalam kategori ini.
                                        </label>
                                    </div>
                                </div>
                            @endif

                            <div>
                                <label class="block font-medium text-black/70 dark:text-white/70 mb-1.5">Deskripsi
                                    (Opsional)</label>
                                <textarea name="description" x-model="editItem.description" maxlength="500" rows="3"
                                    placeholder="Deskripsi ringkas..."
                                    class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] p-3 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50"></textarea>
                            </div>
                        @endif

                        <div
                            class="flex items-center justify-end gap-2 pt-3 border-t border-black/10 dark:border-white/10">
                            <button type="button" @click="showEditModal = false"
                                class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition shadow-[0_1px_2px_rgba(0,122,255,0.25)] cursor-pointer">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- ===================================================== -->
        <!-- 8. APPLE ALERT DIALOG (Native Centered Modal)          -->
        <!-- ===================================================== -->
        <div x-show="deleteModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px]"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="w-[290px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
                @click.away="closeDelete()" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95">

                <div class="px-4 pt-5 pb-4">
                    <p class="text-[17px] font-semibold text-black dark:text-white">Hapus Data Master?</p>
                    <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                        <span x-text="deleteTarget.name" class="font-medium text-black dark:text-white"></span> akan
                        dihapus dari sistem. Tindakan ini tidak dapat dipulihkan.
                    </p>
                </div>

                <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                    <button type="button" @click="closeDelete()"
                        class="py-3 text-[#007AFF] border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors">
                        Batal
                    </button>
                    <button type="button" @click="submitDelete()"
                        class="py-3 text-[#FF3B30] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors">
                        Hapus
                    </button>
                </div>
            </div>
        </div>

        <!-- Hidden Master Delete Form -->
        <form id="form-delete-master" method="POST" action="" class="hidden">
            @csrf
            @method('DELETE')
        </form>

    </div>
@endsection
