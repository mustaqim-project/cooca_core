@extends('layouts.app', [
    'title' => $title,
    'headerTitle' => $title,
    'headerSubtitle' => $subtitle,
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
    showAddModal: false,
    showEditModal: false,
    activeTab: 'units',
    filterSource: 'all',
    searchQuery: '',
    editItem: { id: '', code: '', name: '', category: 'quantity', description: '' },
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
    openEdit(item) {
        this.editItem = {
            id: item.id || '',
            code: item.code || '',
            name: item.name || '',
            category: item.category || 'quantity',
            description: item.description || ''
        };
        this.showEditModal = true;
    },
    matchesSearch(code, name, category, desc) {
        if (!this.searchQuery) return true;
        const q = this.searchQuery.toLowerCase();
        return (code && code.toLowerCase().includes(q)) ||
               (name && name.toLowerCase().includes(q)) ||
               (category && category.toLowerCase().includes(q)) ||
               (desc && desc.toLowerCase().includes(q));
    },
    matchesSource(isBusiness) {
        if (this.filterSource === 'all') return true;
        if (this.filterSource === 'business') return !!isBusiness;
        if (this.filterSource === 'system') return !isBusiness;
        return true;
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
                <span class="text-black/70 dark:text-white/70 font-medium">Pengaturan</span>
                <span>›</span>
                <span class="text-black/70 dark:text-white/70 font-medium">Master Data</span>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">{{ $title }}</span>
            </nav>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                    @if($icon === 'layers')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                        </svg>
                    @elseif($icon === 'folder')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                        </svg>
                    @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 0 1-2.031.352 5.988 5.988 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971Zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 0 1-2.031.352 5.989 5.989 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971Z" />
                        </svg>
                    @endif
                </div>
                <div>
                    <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">{{ $title }}</h1>
                    <p class="text-[13px] text-black/50 dark:text-white/50">{{ $subtitle }}</p>
                </div>
            </div>
        </div>

        @if(in_array($type, ['material-categories', 'product-categories', 'units'], true) && \App\Support\Context::hasPermission("master_data.{$type}.manage"))
            <button type="button" @click="showAddModal = true"
                    class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah {{ $type === 'units' ? 'Satuan' : 'Kategori' }}</span>
            </button>
        @endif
    </header>

    <!-- ===================================================== -->
    <!-- FLASH MESSAGES (Apple HIG Banner Style)                -->
    <!-- ===================================================== -->
    @if(session('success'))
        <div class="rounded-[12px] bg-[#34C759]/10 border border-[#34C759]/20 px-4 py-3 text-[13px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-2.5">
            <svg class="w-4 h-4 shrink-0 text-[#34C759]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-[12px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 px-4 py-3 text-[13px] text-[#C41E17] dark:text-[#FF453A] flex items-center gap-2.5">
            <svg class="w-4 h-4 shrink-0 text-[#FF3B30]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <!-- ===================================================== -->
    <!-- 2. KPI SUMMARY (Flat Neutral Apple HIG Cards)          -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @if($type === 'units')
            <!-- Tile 1: Total Satuan -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Satuan</span>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-[24px] font-bold tabular-nums text-black dark:text-white">{{ $items->count() }}</span>
                    <span class="text-[11px] text-black/40 dark:text-white/40">Item Ukur</span>
                </div>
            </div>
            <!-- Tile 2: Satuan Bisnis Ini -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Kustom Bisnis</span>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-[24px] font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF]">{{ $items->whereNotNull('business_id')->count() }}</span>
                    <span class="text-[11px] font-medium text-[#007AFF]">Bisnis Ini</span>
                </div>
            </div>
            <!-- Tile 3: Satuan Sistem Bawaan -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Standar Sistem</span>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-[24px] font-bold tabular-nums text-[#5856D6] dark:text-[#5E5CE6]">{{ $items->whereNull('business_id')->count() }}</span>
                    <span class="text-[11px] text-black/40 dark:text-white/40">Bawaan</span>
                </div>
            </div>
            <!-- Tile 4: Total Konversi Satuan -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Konversi Satuan</span>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-[24px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">{{ isset($unitConversions) ? $unitConversions->count() : 0 }}</span>
                    <span class="text-[11px] font-medium text-[#34C759] dark:text-[#30D158]">Aktif</span>
                </div>
            </div>
        @else
            <!-- Tile 1: Total Kategori -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Kategori</span>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-[24px] font-bold tabular-nums text-black dark:text-white">{{ $items->count() }}</span>
                    <span class="text-[11px] text-black/40 dark:text-white/40">Terdaftar</span>
                </div>
            </div>
            <!-- Tile 2: Kategori Aktif -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Kategori Aktif</span>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-[24px] font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF]">{{ $items->count() }}</span>
                    <span class="text-[11px] font-medium text-[#007AFF]">Digunakan</span>
                </div>
            </div>
            <!-- Tile 3: Pembaruan Terakhir -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Status Data</span>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-[20px] font-semibold text-black dark:text-white truncate">Tersinkron</span>
                    <span class="text-[11px] text-[#34C759] dark:text-[#30D158] font-medium">Valid</span>
                </div>
            </div>
            <!-- Tile 4: Ruang Lingkup -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
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
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 sm:p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <!-- Search Field (macOS Style: Clean Fill, No Thick Border) -->
        <div class="relative flex-1 max-w-md">
            <svg class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            <input type="text"
                x-model="searchQuery"
                placeholder="Cari {{ strtolower($title) }}..."
                class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-9 pr-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
        </div>

        @if($type === 'units')
            <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                <!-- Source Filter (All / Business / System) -->
                <div x-show="activeTab === 'units'" class="inline-flex p-0.5 rounded-[9px] bg-black/[0.06] dark:bg-white/[0.08] text-[13px] font-medium">
                    <button type="button" @click="filterSource = 'all'" :class="filterSource === 'all' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55'" class="px-3 py-1 rounded-[7px] transition-all">
                        Semua
                    </button>
                    <button type="button" @click="filterSource = 'business'" :class="filterSource === 'business' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55'" class="px-3 py-1 rounded-[7px] transition-all">
                        Bisnis
                    </button>
                    <button type="button" @click="filterSource = 'system'" :class="filterSource === 'system' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55'" class="px-3 py-1 rounded-[7px] transition-all">
                        Sistem
                    </button>
                </div>

                <!-- Tab Segmented Control -->
                <div class="inline-flex p-0.5 rounded-[9px] bg-black/[0.06] dark:bg-white/[0.08] text-[13px] font-medium">
                    <button type="button" @click="activeTab = 'units'"
                            :class="activeTab === 'units' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55'"
                            class="px-3.5 py-1 rounded-[7px] transition-all flex items-center gap-1.5">
                        <span>Daftar Satuan</span>
                        <span class="text-[11px] font-semibold opacity-70 tabular-nums">({{ $items->count() }})</span>
                    </button>
                    <button type="button" @click="activeTab = 'conversions'"
                            :class="activeTab === 'conversions' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55'"
                            class="px-3.5 py-1 rounded-[7px] transition-all flex items-center gap-1.5">
                        <span>Konversi</span>
                        <span class="text-[11px] font-semibold opacity-70 tabular-nums">({{ isset($unitConversions) ? $unitConversions->count() : 0 }})</span>
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
        <div class="hidden sm:block rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02]">
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">
                                {{ $type === 'units' ? 'Kode Satuan' : 'Nama Kategori' }}
                            </th>
                            @if($type === 'units')
                                <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">Nama Lengkap</th>
                                <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">Dimensi</th>
                                <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">Sumber</th>
                            @else
                                <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">Deskripsi</th>
                                <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">Slug</th>
                            @endif
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($items as $item)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors"
                                x-show="matchesSearch('{{ addslashes($item->code ?? '') }}', '{{ addslashes($item->name ?? '') }}', '{{ addslashes($item->category ?? '') }}', '{{ addslashes($item->description ?? '') }}') && matchesSource({{ $item->business_id ? 'true' : 'false' }})">
                                @if($type === 'units')
                                    <td class="py-3 px-4 font-semibold text-black dark:text-white tabular-nums">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full {{ $item->business_id ? 'bg-[#007AFF]' : 'bg-black/30 dark:bg-white/30' }}"></span>
                                            <span>{{ $item->code }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-black/80 dark:text-white/80 font-medium">{{ $item->name }}</td>
                                    <td class="py-3 px-4 text-black/60 dark:text-white/60 capitalize">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-black/[0.05] dark:bg-white/[0.06] text-black/70 dark:text-white/70">
                                            {{ $item->category }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $item->business_id ? 'bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF]' : 'bg-black/[0.06] dark:bg-white/[0.08] text-black/60 dark:text-white/60' }}">
                                            {{ $item->business_id ? 'Bisnis Ini' : 'Sistem Bawaan' }}
                                        </span>
                                    </td>
                                @else
                                    <td class="py-3 px-4 font-semibold text-black dark:text-white">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-[#007AFF]"></span>
                                            <span>{{ $item->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-black/60 dark:text-white/60 max-w-md truncate">{{ $item->description ?: '—' }}</td>
                                    <td class="py-3 px-4 text-black/40 dark:text-white/40 tabular-nums text-[12px]">{{ $item->slug ?? '-' }}</td>
                                @endif
                                <td class="py-3 px-4 text-right">
                                    @if(($item->business_id || !in_array($type, ['units'], true)) && \App\Support\Context::hasPermission("master_data.{$type}.manage"))
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button" title="Edit"
                                                    @click="openEdit(@js(['id' => $item->id, 'code' => $item->code ?? '', 'name' => $item->name ?? '', 'category' => $item->category ?? 'quantity', 'description' => $item->description ?? '']))"
                                                    class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors flex items-center">
                                                Edit
                                            </button>
                                            <button type="button" title="Hapus"
                                                    @click="openDelete('{{ route($type . '.destroy', $item->id) }}', '{{ addslashes($item->name ?? $item->code) }}')"
                                                    class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/8 transition-colors flex items-center">
                                                Hapus
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-black/30 dark:text-white/30 text-[12px]">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-black/40 dark:text-white/40">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <svg class="w-10 h-10 text-black/20 dark:text-white/20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
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
        <div class="sm:hidden rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
            @forelse($items as $item)
                <div class="p-3.5 flex items-center justify-between gap-3 active:bg-black/[0.02] dark:active:bg-white/[0.03] transition-colors"
                     x-show="matchesSearch('{{ addslashes($item->code ?? '') }}', '{{ addslashes($item->name ?? '') }}', '{{ addslashes($item->category ?? '') }}', '{{ addslashes($item->description ?? '') }}') && matchesSource({{ $item->business_id ? 'true' : 'false' }})">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full {{ $item->business_id ? 'bg-[#007AFF]' : 'bg-black/30 dark:bg-white/30' }} shrink-0"></span>
                            <p class="text-[15px] font-medium text-black dark:text-white truncate">
                                {{ $type === 'units' ? $item->code : $item->name }}
                            </p>
                            @if($type === 'units')
                                <span class="text-[11px] px-1.5 py-0.2 rounded-full font-semibold {{ $item->business_id ? 'bg-[#007AFF]/12 text-[#007AFF]' : 'bg-black/[0.06] dark:bg-white/[0.08] text-black/55 dark:text-white/55' }}">
                                    {{ $item->business_id ? 'Bisnis' : 'Sistem' }}
                                </span>
                            @endif
                        </div>
                        <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5 truncate">
                            @if($type === 'units')
                                {{ $item->name }} · {{ ucfirst($item->category) }}
                            @else
                                {{ $item->description ?: 'Tidak ada deskripsi' }}
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        @if(($item->business_id || !in_array($type, ['units'], true)) && \App\Support\Context::hasPermission("master_data.{$type}.manage"))
                            <button type="button" @click="openEdit(@js(['id' => $item->id, 'code' => $item->code ?? '', 'name' => $item->name ?? '', 'category' => $item->category ?? 'quantity', 'description' => $item->description ?? '']))"
                                    class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-[#007AFF] bg-[#007AFF]/10 flex items-center">
                                Edit
                            </button>
                            <button type="button" @click="openDelete('{{ route($type . '.destroy', $item->id) }}', '{{ addslashes($item->name ?? $item->code) }}')"
                                    class="h-8 w-8 rounded-[8px] text-[#FF3B30] bg-[#FF3B30]/10 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @endif
                    </div>
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
    @if($type === 'units')
        <div x-show="activeTab === 'conversions'" class="space-y-5" style="display: none">
            @if(\App\Support\Context::hasPermission('master_data.units.manage'))
                <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 space-y-4">
                    <div>
                        <h2 class="text-[15px] font-semibold text-black dark:text-white">Tambah Konversi Satuan</h2>
                        <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Tentukan perbandingan nilai konversi antar satuan (contoh: 1 sak = 25 kg)</p>
                    </div>
                    <form method="POST" action="{{ route('unit-conversions.store') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                        @csrf
                        <div>
                            <select name="from_unit_id" required class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                <option value="">Satuan Asal (Dari)</option>
                                @foreach($items as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->code }} — {{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <select name="to_unit_id" required class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                <option value="">Satuan Tujuan (Ke)</option>
                                @foreach($items as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->code }} — {{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <input type="number" name="factor" required min="0.000001" step="0.000001" placeholder="Faktor Pengali (misal: 1000)"
                                   class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                        <div>
                            <button type="submit" class="w-full h-10 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                <span>Simpan Konversi</span>
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Conversions Dense Table (Desktop) -->
            <div class="hidden sm:block rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[13px]">
                        <thead>
                            <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02]">
                                <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Satuan Asal</th>
                                <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Satuan Tujuan</th>
                                <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Faktor Pengali</th>
                                <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                            @forelse($unitConversions as $conversion)
                                <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                    <td class="py-3 px-4 font-semibold text-black dark:text-white">
                                        {{ $conversion->fromUnit?->code }}
                                        <span class="text-[12px] font-normal text-black/50 dark:text-white/50">({{ $conversion->fromUnit?->name }})</span>
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-black dark:text-white">
                                        {{ $conversion->toUnit?->code }}
                                        <span class="text-[12px] font-normal text-black/50 dark:text-white/50">({{ $conversion->toUnit?->name }})</span>
                                    </td>
                                    <td class="py-3 px-4 tabular-nums font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                                        1 {{ $conversion->fromUnit?->code }} = {{ number_format((float) $conversion->factor, 6, '.', '') }} {{ $conversion->toUnit?->code }}
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        @if(\App\Support\Context::hasPermission('master_data.units.manage'))
                                            <button type="button" @click="openDelete('{{ route('unit-conversions.destroy', $conversion->id) }}', 'Konversi 1 {{ $conversion->fromUnit?->code }} ke {{ $conversion->toUnit?->code }}')"
                                                    class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/8 transition-colors">
                                                Hapus
                                            </button>
                                        @else
                                            <span class="text-black/30 dark:text-white/30 text-[12px]">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-12 text-center text-black/40 dark:text-white/40">Belum ada konversi satuan bisnis terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Conversions Mobile Grouped Inset List (Mobile) -->
            <div class="sm:hidden rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                @forelse($unitConversions as $conversion)
                    <div class="p-3.5 flex items-center justify-between gap-3 active:bg-black/[0.02] dark:active:bg-white/[0.03] transition-colors">
                        <div class="min-w-0 flex-1">
                            <p class="text-[15px] font-medium text-black dark:text-white">
                                {{ $conversion->fromUnit?->code }} → {{ $conversion->toUnit?->code }}
                            </p>
                            <p class="text-[13px] text-[#007AFF] dark:text-[#0A84FF] tabular-nums mt-0.5 font-medium">
                                1 {{ $conversion->fromUnit?->code }} = {{ number_format((float) $conversion->factor, 6, '.', '') }} {{ $conversion->toUnit?->code }}
                            </p>
                        </div>
                        @if(\App\Support\Context::hasPermission('master_data.units.manage'))
                            <button type="button" @click="openDelete('{{ route('unit-conversions.destroy', $conversion->id) }}', 'Konversi 1 {{ $conversion->fromUnit?->code }} ke {{ $conversion->toUnit?->code }}')"
                                    class="h-8 w-8 rounded-[8px] text-[#FF3B30] bg-[#FF3B30]/10 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
    @if(in_array($type, ['material-categories', 'product-categories', 'units'], true) && \App\Support\Context::hasPermission("master_data.{$type}.manage"))
        <div x-show="showAddModal" style="display: none"
             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/25 backdrop-blur-[2px]"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

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
                    <button type="button" @click="showAddModal = false" class="w-7 h-7 rounded-[6px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 flex items-center justify-center transition">✕</button>
                </div>

                <form method="POST" action="{{ route($type . '.store') }}" class="space-y-4 text-[13px]">
                    @csrf
                    @if($type === 'units')
                        <div>
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1.5">Kode Satuan <span class="text-[#FF3B30]">*</span></label>
                            <input name="code" required maxlength="30" placeholder="Contoh: sak, botol, pack"
                                   class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white tabular-nums placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                        <div>
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1.5">Nama Lengkap Satuan <span class="text-[#FF3B30]">*</span></label>
                            <input name="name" required maxlength="100" placeholder="Nama lengkap, contoh: Sak 25kg"
                                   class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                        <div>
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1.5">Kategori Dimensi <span class="text-[#FF3B30]">*</span></label>
                            <select name="category" required class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
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
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1.5">Nama Kategori <span class="text-[#FF3B30]">*</span></label>
                            <input name="name" required maxlength="150" placeholder="{{ $nameLabel }}"
                                   class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                        <div>
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1.5">Deskripsi (Opsional)</label>
                            <textarea name="description" maxlength="500" rows="3" placeholder="Deskripsi ringkas penggunaan kategori..."
                                      class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] p-3 text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50"></textarea>
                        </div>
                    @endif

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-black/10 dark:border-white/10">
                        <button type="button" @click="showAddModal = false"
                                class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
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
    @if(in_array($type, ['material-categories', 'product-categories', 'units'], true) && \App\Support\Context::hasPermission("master_data.{$type}.manage"))
        <div x-show="showEditModal" style="display: none"
             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/25 backdrop-blur-[2px]"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

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
                    <button type="button" @click="showEditModal = false" class="w-7 h-7 rounded-[6px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 flex items-center justify-center transition">✕</button>
                </div>

                <form :action="'/{{ $type }}/' + editItem.id" method="POST" class="space-y-4 text-[13px]">
                    @csrf
                    @method('PUT')

                    @if($type === 'units')
                        <div>
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1.5">Kode Satuan <span class="text-[#FF3B30]">*</span></label>
                            <input name="code" x-model="editItem.code" required maxlength="30" placeholder="Kode satuan"
                                   class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                        <div>
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1.5">Nama Lengkap Satuan <span class="text-[#FF3B30]">*</span></label>
                            <input name="name" x-model="editItem.name" required maxlength="100" placeholder="Nama lengkap satuan"
                                   class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                        <div>
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1.5">Kategori Dimensi <span class="text-[#FF3B30]">*</span></label>
                            <select name="category" x-model="editItem.category" required class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
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
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1.5">Nama Kategori <span class="text-[#FF3B30]">*</span></label>
                            <input name="name" x-model="editItem.name" required maxlength="150" placeholder="{{ $nameLabel }}"
                                   class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                        <div>
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1.5">Deskripsi (Opsional)</label>
                            <textarea name="description" x-model="editItem.description" maxlength="500" rows="3" placeholder="Deskripsi ringkas..."
                                      class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] p-3 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50"></textarea>
                        </div>
                    @endif

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-black/10 dark:border-white/10">
                        <button type="button" @click="showEditModal = false"
                                class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
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
    <div x-show="deleteModalOpen"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px]"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="w-[290px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
             @click.away="closeDelete()"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div class="px-4 pt-5 pb-4">
                <p class="text-[17px] font-semibold text-black dark:text-white">Hapus Data Master?</p>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                    <span x-text="deleteTarget.name" class="font-medium text-black dark:text-white"></span> akan dihapus dari sistem. Tindakan ini tidak dapat dipulihkan.
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
