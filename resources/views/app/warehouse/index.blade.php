@extends('layouts.app', [
    'title' => 'Cabang & Gudang',
    'headerTitle' => 'Cabang & Gudang',
    'headerSubtitle' => 'Kelola jaringan cabang toko/outlet, titik penyimpanan gudang logistik, dan absensi geofence.'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="{
    showCreateModal: false,
    showCreateOutletModal: false,
    showEditModal: false,
    filterTab: 'all',
    gpsLoading: false,
    gpsSuccess: false,
    gpsError: '',
    activeGpsTarget: null,

    // Create Outlet Form Data
    outletForm: {
        name: '',
        code: '',
        phone: '',
        address: '',
        province: '',
        city: '',
        district: '',
        village: '',
        postal_code: '',
        biteship_area_id: '',
        biteship_area_label: '',
        latitude: '',
        longitude: '',
        geofence_radius_meters: 100,
        is_primary: false,
        is_online_fulfillment: true,
        allow_storefront_pickup: true
    },

    // Create Warehouse Form Data
    warehouseForm: {
        name: '',
        code: '',
        phone: '',
        address: '',
        province: '',
        city: '',
        district: '',
        village: '',
        postal_code: '',
        biteship_area_id: '',
        biteship_area_label: '',
        latitude: '',
        longitude: '',
        geofence_radius_meters: 100,
        is_primary: false,
        is_online_fulfillment: true,
        allow_storefront_pickup: true
    },

    // Edit Form Data
    editData: {
        id: null,
        name: '',
        type: 'warehouse',
        code: '',
        phone: '',
        address: '',
        province: '',
        city: '',
        district: '',
        village: '',
        postal_code: '',
        biteship_area_id: '',
        biteship_area_label: '',
        latitude: '',
        longitude: '',
        geofence_radius_meters: 100,
        is_primary: false,
        is_online_fulfillment: true,
        allow_storefront_pickup: true,
        is_active: true
    },

    // Biteship Search Autocomplete State
    biteshipQuery: '',
    biteshipResults: [],
    biteshipSearching: false,
    biteshipTarget: null,

    deleteModalOpen: false,
    deleteTarget: { id: null, name: '' },

    openEdit(loc) {
        this.editData = {
            id: loc.id,
            name: loc.name || '',
            type: loc.type || 'warehouse',
            code: loc.code || '',
            phone: loc.phone || '',
            address: loc.address || '',
            province: loc.province || '',
            city: loc.city || '',
            district: loc.district || '',
            village: loc.village || '',
            postal_code: loc.postal_code || '',
            biteship_area_id: loc.biteship_area_id || '',
            biteship_area_label: loc.biteship_area_id ? ((loc.district ? loc.district + ', ' : '') + (loc.city || '') + (loc.postal_code ? ' (' + loc.postal_code + ')' : '')) : '',
            latitude: (loc.latitude !== null && loc.latitude !== undefined) ? loc.latitude : '',
            longitude: (loc.longitude !== null && loc.longitude !== undefined) ? loc.longitude : '',
            geofence_radius_meters: loc.geofence_radius_meters ?? 100,
            is_primary: Boolean(loc.is_primary),
            is_online_fulfillment: Boolean(loc.is_online_fulfillment !== false && loc.is_online_fulfillment !== 0),
            allow_storefront_pickup: Boolean(loc.allow_storefront_pickup !== false && loc.allow_storefront_pickup !== 0),
            is_active: Boolean(loc.is_active)
        };
        this.biteshipResults = [];
        this.biteshipQuery = '';
        this.gpsError = '';
        this.gpsSuccess = false;
        this.showEditModal = true;
    },

    async detectGps(target) {
        this.activeGpsTarget = target;
        this.gpsLoading = true;
        this.gpsError = '';
        this.gpsSuccess = false;

        if (!navigator.geolocation) {
            this.gpsError = 'Browser Anda tidak mendukung Geolocation GPS.';
            this.gpsLoading = false;
            return;
        }

        navigator.geolocation.getCurrentPosition(
            async (pos) => {
                const lat = Number(pos.coords.latitude.toFixed(6));
                const lng = Number(pos.coords.longitude.toFixed(6));

                let formObj = null;
                if (target === 'outlet') formObj = this.outletForm;
                else if (target === 'warehouse') formObj = this.warehouseForm;
                else if (target === 'edit') formObj = this.editData;

                if (formObj) {
                    formObj.latitude = lat;
                    formObj.longitude = lng;
                }

                this.gpsLoading = false;
                this.gpsSuccess = true;

                // Auto Reverse-Geocode & auto-lookup Biteship
                try {
                    const res = await fetch(`{{ route('geo.reverse-geocode') }}?lat=${lat}&lng=${lng}`);
                    const data = await res.json();
                    if (data && data.success && formObj) {
                        if (!formObj.address && (data.road || data.display_name)) {
                            formObj.address = data.road || data.display_name;
                        }
                        if (data.province) formObj.province = data.province;
                        if (data.city) formObj.city = data.city;
                        if (data.district) formObj.district = data.district;
                        if (data.village) formObj.village = data.village;
                        if (data.postal_code) formObj.postal_code = data.postal_code;

                        // If biteship_area_id is empty, auto search area by postal code or district
                        if (!formObj.biteship_area_id && (data.postal_code || data.village || data.district)) {
                            const q = data.postal_code || data.village || data.district;
                            this.autoFillBiteship(target, q);
                        }
                    }
                } catch (err) {
                    console.warn('Reverse geocode error:', err);
                }

                setTimeout(() => { this.gpsSuccess = false; }, 4000);
            },
            (err) => {
                this.gpsLoading = false;
                if (err.code === 1) {
                    this.gpsError = 'Izin akses lokasi ditolak. Silakan izinkan lokasi di pengaturan browser.';
                } else if (err.code === 2) {
                    this.gpsError = 'Posisi GPS tidak dapat ditentukan. Pastikan GPS/Location device aktif.';
                } else {
                    this.gpsError = 'Waktu deteksi GPS habis. Coba lagi.';
                }
                setTimeout(() => { this.gpsError = ''; }, 5000);
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    },

    async autoFillBiteship(target, query) {
        try {
            const res = await fetch(`{{ route('geo.search-areas') }}?query=${encodeURIComponent(query)}`);
            const data = await res.json();
            if (data && data.areas && data.areas.length > 0) {
                const first = data.areas[0];
                let formObj = target === 'outlet' ? this.outletForm : (target === 'warehouse' ? this.warehouseForm : this.editData);
                if (formObj && !formObj.biteship_area_id) {
                    formObj.biteship_area_id = first.id;
                    formObj.biteship_area_label = first.label || `${first.district || ''}, ${first.city || ''} (${first.postal_code || ''})`;
                    if (!formObj.postal_code && first.postal_code) formObj.postal_code = first.postal_code;
                    if (!formObj.city && first.city) formObj.city = first.city;
                    if (!formObj.district && first.district) formObj.district = first.district;
                    if (!formObj.village && first.village) formObj.village = first.village;
                    if (!formObj.province && first.province) formObj.province = first.province;
                }
            }
        } catch (e) {
            console.warn('Auto Biteship search error:', e);
        }
    },

    searchBiteship(target, query) {
        this.biteshipTarget = target;
        this.biteshipQuery = query;
        if (!query || query.trim().length < 2) {
            this.biteshipResults = [];
            return;
        }
        this.biteshipSearching = true;
        fetch(`{{ route('geo.search-areas') }}?query=${encodeURIComponent(query)}`)
            .then(r => r.json())
            .then(data => {
                this.biteshipResults = data.areas || [];
                this.biteshipSearching = false;
            })
            .catch(() => {
                this.biteshipResults = [];
                this.biteshipSearching = false;
            });
    },

    selectBiteshipArea(target, area) {
        let formObj = target === 'outlet' ? this.outletForm : (target === 'warehouse' ? this.warehouseForm : this.editData);
        if (formObj) {
            formObj.biteship_area_id = area.id;
            formObj.biteship_area_label = area.label || `${area.district || ''}, ${area.city || ''} (${area.postal_code || ''})`;
            if (area.province) formObj.province = area.province;
            if (area.city) formObj.city = area.city;
            if (area.district) formObj.district = area.district;
            if (area.village) formObj.village = area.village;
            if (area.postal_code) formObj.postal_code = area.postal_code;
            if (area.latitude && !formObj.latitude) formObj.latitude = area.latitude;
            if (area.longitude && !formObj.longitude) formObj.longitude = area.longitude;
        }
        this.biteshipResults = [];
        this.biteshipQuery = '';
    },

    clearBiteshipArea(target) {
        let formObj = target === 'outlet' ? this.outletForm : (target === 'warehouse' ? this.warehouseForm : this.editData);
        if (formObj) {
            formObj.biteship_area_id = '';
            formObj.biteship_area_label = '';
        }
        this.biteshipResults = [];
        this.biteshipQuery = '';
    },

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
            const form = document.getElementById('form-delete-location-' + this.deleteTarget.id);
            if (form) form.submit();
        }
    },
    matchesFilter(type) {
        if (this.filterTab === 'all') return true;
        if (this.filterTab === 'outlet') return type === 'outlet' || type === 'store' || type === 'central_kitchen';
        if (this.filterTab === 'warehouse') return type === 'warehouse';
        return true;
    }
}">

    {{-- ===================================================== --}}
    {{-- 0. BREADCRUMB BAR                                     --}}
    {{-- ===================================================== --}}
    <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 print:hidden" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors flex items-center gap-1.5 font-bold text-slate-900 dark:text-white">
            <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
            <span>Dashboard</span>
        </a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600"></i>
        <span class="text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
            <i data-lucide="archive" class="w-3.5 h-3.5"></i>
            <span>Inventori</span>
        </span>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600"></i>
        <span class="text-slate-900 dark:text-white font-bold flex items-center gap-1.5">
            <span>Cabang &amp; Gudang</span>
        </span>
    </nav>

    {{-- ===================================================== --}}
    {{-- 1. SUB-NAVIGATION TABS (Apple Segmented Control)      --}}
    {{-- ===================================================== --}}
    <div class="overflow-x-auto pb-1 scrollbar-none">
        <div class="inline-flex p-1 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.07] border border-black/5 dark:border-white/10 text-[13px] font-medium whitespace-nowrap">
            <a href="{{ route('warehouse.index') }}"
               class="px-3.5 py-1.5 rounded-[10px] bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-semibold flex items-center gap-2 transition-all">
                <i data-lucide="warehouse" class="w-4 h-4 text-[#007AFF]"></i>
                <span>Cabang &amp; Gudang</span>
                <span class="px-1.5 py-0.2 rounded-full text-[11px] tabular-nums font-semibold bg-[#007AFF]/12 text-[#007AFF]">{{ $locations->count() }}</span>
            </a>
            <a href="{{ route('inventory.stocks') }}"
               class="px-3.5 py-1.5 rounded-[10px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-all flex items-center gap-2">
                <i data-lucide="package" class="w-4 h-4 text-black/40 dark:text-white/40"></i>
                <span>Stok Inventori</span>
            </a>
            <a href="{{ route('inventory.transfers.index') }}"
               class="px-3.5 py-1.5 rounded-[10px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-all flex items-center gap-2">
                <i data-lucide="arrow-left-right" class="w-4 h-4 text-black/40 dark:text-white/40"></i>
                <span>Transfer Stok</span>
            </a>
            <a href="{{ route('inventory.opnames.index') }}"
               class="px-3.5 py-1.5 rounded-[10px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-all flex items-center gap-2">
                <i data-lucide="clipboard-check" class="w-4 h-4 text-black/40 dark:text-white/40"></i>
                <span>Stock Opname</span>
            </a>
            <a href="{{ route('inventory.movements') }}"
               class="px-3.5 py-1.5 rounded-[10px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-all flex items-center gap-2">
                <i data-lucide="history" class="w-4 h-4 text-black/40 dark:text-white/40"></i>
                <span>Riwayat Mutasi</span>
            </a>
            <a href="{{ route('purchase-orders.index') }}"
               class="px-3.5 py-1.5 rounded-[10px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-all flex items-center gap-2">
                <i data-lucide="truck" class="w-4 h-4 text-black/40 dark:text-white/40"></i>
                <span>PO Supplier</span>
            </a>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- 2. TOOLBAR / PAGE HEADER (Bento Header)               --}}
    {{-- ===================================================== --}}
    <header class="bg-white dark:bg-[#1C1C1E] p-4 sm:p-5 rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 transition-colors">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 dark:bg-[#007AFF]/20 border border-[#007AFF]/20 text-[#007AFF] flex items-center justify-center shrink-0">
                <i data-lucide="warehouse" class="w-5 h-5"></i>
            </div>
            <div>
                <h1 class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight">
                    Cabang &amp; Gudang Logistik
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Kelola jaringan cabang toko/outlet, titik penyimpanan gudang logistik, dan absensi geofence
                </p>
            </div>
        </div>

        {{-- Toolbar Actions --}}
        <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
            @if(\App\Support\Context::hasPermission('inventory.view'))
            <a href="{{ route('materials.index') }}"
               class="h-9 px-3.5 rounded-[10px] text-xs font-semibold text-slate-700 dark:text-slate-200 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="boxes" class="w-4 h-4 text-slate-500 dark:text-slate-400"></i>
                <span>Katalog Bahan</span>
            </a>
            <a href="{{ route('products.index') }}"
               class="h-9 px-3.5 rounded-[10px] text-xs font-semibold text-slate-700 dark:text-slate-200 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="package" class="w-4 h-4 text-slate-500 dark:text-slate-400"></i>
                <span>Katalog Produk</span>
            </a>
            @endif

            @if(\App\Support\Context::hasPermission('inventory.manage') || \App\Support\Context::isAdminOrOwner() || \App\Support\Context::hasPermission('warehouse.manage'))
            <button type="button" @click="showCreateOutletModal = true; gpsError = ''; gpsSuccess = false;"
                    class="h-9 px-4 rounded-[10px] text-xs font-bold text-white bg-[#34C759] hover:bg-[#28A745] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(52,199,89,0.25)] cursor-pointer">
                <i data-lucide="store" class="w-4 h-4"></i>
                <span>+ Cabang / Outlet</span>
            </button>
            <button type="button" @click="showCreateModal = true; gpsError = ''; gpsSuccess = false;"
                    class="h-9 px-4 rounded-[10px] text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] cursor-pointer">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>+ Gudang Logistik</span>
            </button>
            @endif
        </div>
    </header>

    {{-- ===================================================== --}}
    {{-- FLASH MESSAGES                                        --}}
    {{-- ===================================================== --}}
    @if(session('success'))
    <div class="rounded-[14px] bg-[#34C759]/10 border border-[#34C759]/20 px-4 py-3 text-xs text-[#248A3D] dark:text-[#30D158] flex items-center gap-2.5">
        <i data-lucide="check-circle" class="w-4 h-4 shrink-0 text-[#34C759]"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 px-4 py-3 text-xs text-[#C41E17] dark:text-[#FF453A] flex items-center gap-2.5">
        <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0 text-[#FF3B30]"></i>
        <span class="font-medium">{{ session('error') }}</span>
    </div>
    @endif

    {{-- ===================================================== --}}
    {{-- 3. KPI SUMMARY (Bento Apple HIG Cards)                --}}
    {{-- ===================================================== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        {{-- Tile 1: Total Lokasi --}}
        <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Lokasi</span>
                <div class="w-7 h-7 rounded-[8px] bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-black tabular-nums text-slate-900 dark:text-white">{{ $stats['total_locations'] ?? 0 }}</span>
                <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">Cabang &amp; Gudang</span>
            </div>
        </div>

        {{-- Tile 2: Lokasi Aktif --}}
        <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Lokasi Aktif</span>
                <div class="w-7 h-7 rounded-[8px] bg-[#34C759]/10 flex items-center justify-center text-[#34C759]">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-black tabular-nums text-[#34C759] dark:text-[#30D158]">{{ $stats['active_locations'] ?? 0 }}</span>
                <span class="text-[11px] font-semibold text-[#34C759] dark:text-[#30D158]">Siap Operasi</span>
            </div>
        </div>

        {{-- Tile 3: Total SKU Terdaftar --}}
        <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">SKU Terdaftar</span>
                <div class="w-7 h-7 rounded-[8px] bg-[#5856D6]/10 flex items-center justify-center text-[#5856D6]">
                    <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-black tabular-nums text-[#5856D6] dark:text-[#5E5CE6]">{{ number_format($stats['total_sku'] ?? 0, 0, ',', '.') }}</span>
                <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">Item Fisik</span>
            </div>
        </div>

        {{-- Tile 4: Total Unit Stok Fisik --}}
        <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Unit Fisik</span>
                <div class="w-7 h-7 rounded-[8px] bg-[#007AFF]/10 flex items-center justify-center text-[#007AFF]">
                    <i data-lucide="box" class="w-3.5 h-3.5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-black tabular-nums text-[#007AFF] dark:text-[#0A84FF]">{{ number_format($stats['total_stock_units'] ?? 0, 0, ',', '.') }}</span>
                <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">Seluruh Lokasi</span>
            </div>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- 4. LOCATIONS GRID (Apple Squircle Bento Cards)        --}}
    {{-- ===================================================== --}}
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <h2 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">Daftar Titik Lokasi</h2>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 tabular-nums">
                    {{ $locations->count() }}
                </span>
            </div>
            {{-- Tab Filter: Semua | Cabang & Toko | Gudang Logistik --}}
            <div class="inline-flex p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.07] border border-black/5 dark:border-white/10 text-xs font-medium">
                <button @click="filterTab = 'all'"
                        :class="filterTab === 'all' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-[8px] transition-all flex items-center gap-1.5 cursor-pointer">
                    Semua Lokasi
                    <span class="px-1.5 rounded-full text-[11px] tabular-nums font-semibold bg-black/[0.06] dark:bg-white/[0.08]" x-text="{{ $locations->count() }}"></span>
                </button>
                <button @click="filterTab = 'outlet'"
                        :class="filterTab === 'outlet' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-[8px] transition-all flex items-center gap-1.5 cursor-pointer">
                    <span class="w-2 h-2 rounded-full bg-[#34C759]"></span> Cabang &amp; Toko
                    <span class="px-1.5 rounded-full text-[11px] tabular-nums font-semibold bg-black/[0.06] dark:bg-white/[0.08]">{{ $locations->filter(fn($l) => in_array($l->type, ['outlet', 'store', 'central_kitchen']))->count() }}</span>
                </button>
                <button @click="filterTab = 'warehouse'"
                        :class="filterTab === 'warehouse' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-[8px] transition-all flex items-center gap-1.5 cursor-pointer">
                    <span class="w-2 h-2 rounded-full bg-[#007AFF]"></span> Gudang Logistik
                    <span class="px-1.5 rounded-full text-[11px] tabular-nums font-semibold bg-black/[0.06] dark:bg-white/[0.08]">{{ $locations->where('type', 'warehouse')->count() }}</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
            @forelse($locations as $loc)
            <div x-show="matchesFilter('{{ $loc->type ?? 'warehouse' }}')" x-transition.opacity.duration.200ms
                 class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 flex flex-col justify-between space-y-4 hover:shadow-md transition-all">
                {{-- Header Card --}}
                <div>
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            {{-- Type Glyph Container --}}
                            <div class="w-11 h-11 rounded-[14px] flex items-center justify-center shrink-0
                                @if($loc->type === 'warehouse')
                                    bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20
                                @elseif($loc->type === 'central_kitchen')
                                    bg-[#FF9500]/10 text-[#FF9500] border border-[#FF9500]/20
                                @else
                                    bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20
                                @endif
                            ">
                                @if($loc->type === 'warehouse')
                                    <i data-lucide="warehouse" class="w-5 h-5"></i>
                                @elseif($loc->type === 'central_kitchen')
                                    <i data-lucide="flame" class="w-5 h-5"></i>
                                @else
                                    <i data-lucide="store" class="w-5 h-5"></i>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white truncate">
                                        {{ $loc->name }}
                                    </h3>
                                    @if($loc->is_primary)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500]/15 text-[#D97706] dark:text-[#FBBF24] border border-[#FF9500]/30" title="Titik Pengiriman & Pickup Utama Toko Online Biteship">
                                            <i data-lucide="truck" class="w-3 h-3"></i>
                                            <span>Pickup Utama Toko Online</span>
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                    @if($loc->code)
                                        <span class="text-xs font-mono tabular-nums text-slate-500 dark:text-slate-400 font-semibold">
                                            {{ $loc->code }}
                                        </span>
                                        <span class="text-[10px] text-slate-300 dark:text-slate-600">&bull;</span>
                                    @endif
                                    <span class="text-xs text-slate-600 dark:text-slate-400 font-medium">
                                        @if($loc->type === 'warehouse')
                                            Gudang
                                        @elseif($loc->type === 'central_kitchen')
                                            Dapur Pusat
                                        @else
                                            Outlet
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Status Pill --}}
                        <div>
                            @if($loc->is_active)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Nonaktif
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Badges: GPS Geofence & Biteship Logistics --}}
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @if($loc->latitude && $loc->longitude)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[6px] text-[11px] font-semibold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20 font-mono">
                                <i data-lucide="crosshair" class="w-3 h-3"></i>
                                <span>GPS: {{ round((float)$loc->latitude, 4) }}, {{ round((float)$loc->longitude, 4) }} (R: {{ $loc->geofence_radius_meters ?? 100 }}m)</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[6px] text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                                <i data-lucide="map-pin-off" class="w-3 h-3 text-slate-400"></i>
                                <span>GPS Belum Diset</span>
                            </span>
                        @endif

                        @if($loc->biteship_area_id)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[6px] text-[11px] font-semibold bg-[#5856D6]/10 text-[#5856D6] dark:text-[#5E5CE6] border border-[#5856D6]/20">
                                <i data-lucide="package-check" class="w-3 h-3"></i>
                                <span>Biteship Area Terhubung</span>
                            </span>
                        @endif
                    </div>

                    {{-- Contact & Address info --}}
                    <div class="mt-3 space-y-1.5 text-xs text-slate-600 dark:text-slate-400">
                        @if($loc->phone)
                        <div class="flex items-center gap-2">
                            <i data-lucide="phone" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                            <span class="font-mono tabular-nums">{{ $loc->phone }}</span>
                        </div>
                        @endif

                        @if($loc->address || $loc->city)
                        <div class="flex items-start gap-2">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400 shrink-0 mt-0.5"></i>
                            <span class="line-clamp-2 text-slate-500 dark:text-slate-400">
                                {{ $loc->address }}
                                @if($loc->district || $loc->city || $loc->postal_code)
                                    <span class="block text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                                        {{ implode(', ', array_filter([$loc->village, $loc->district, $loc->city, $loc->province, $loc->postal_code])) }}
                                    </span>
                                @endif
                            </span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Stats Strip --}}
                <div class="pt-3.5 border-t border-black/[0.04] dark:border-white/[0.06]">
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="rounded-[10px] bg-slate-50 dark:bg-[#2C2C2E] p-2">
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 block font-semibold uppercase">SKU</span>
                            <span class="text-xs sm:text-sm font-bold tabular-nums text-slate-900 dark:text-white">
                                {{ $loc->stocks_count ?? 0 }}
                            </span>
                        </div>
                        <div class="rounded-[10px] bg-slate-50 dark:bg-[#2C2C2E] p-2">
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 block font-semibold uppercase">Unit Stok</span>
                            <span class="text-xs sm:text-sm font-bold tabular-nums text-slate-900 dark:text-white">
                                {{ number_format($loc->total_stock_units ?? 0, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="rounded-[10px] bg-slate-50 dark:bg-[#2C2C2E] p-2">
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 block font-semibold uppercase">Nilai Aset</span>
                            <span class="text-xs sm:text-sm font-bold tabular-nums text-[#34C759] dark:text-[#30D158] truncate block" title="Rp {{ number_format($loc->total_valuation ?? 0, 0, ',', '.') }}">
                                {{ ($loc->total_valuation ?? 0) >= 1000000 ? number_format(($loc->total_valuation ?? 0) / 1000000, 1) . 'jt' : number_format(($loc->total_valuation ?? 0) / 1000, 0) . 'rb' }}
                            </span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-4 flex items-center justify-between gap-2">
                        <a href="{{ route('warehouse.show', $loc) }}"
                           class="h-8.5 flex-1 rounded-[10px] text-xs font-bold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                            <span>Kelola Stok</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>

                        @if(\App\Support\Context::hasPermission('inventory.manage') || \App\Support\Context::isAdminOrOwner() || \App\Support\Context::hasPermission('warehouse.manage'))
                        <div class="flex items-center gap-1.5 shrink-0">
                            <button type="button" @click="openEdit({{ json_encode($loc) }})"
                                    class="h-8.5 px-3 rounded-[10px] text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 active:scale-[0.98] transition-all flex items-center gap-1 cursor-pointer"
                                    title="Edit Lokasi">
                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                <span>Edit</span>
                            </button>

                            <button type="button" @click="openDelete({{ $loc->id }}, '{{ addslashes($loc->name) }}')"
                                    class="h-8.5 w-8.5 rounded-[10px] text-[#FF3B30] hover:bg-[#FF3B30]/10 active:scale-[0.98] transition-all flex items-center justify-center cursor-pointer"
                                    title="Hapus Lokasi">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>

                            <form id="form-delete-location-{{ $loc->id }}" action="{{ route('warehouse.destroy', $loc) }}" method="POST" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full py-12 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] text-center shadow-xs">
                <div class="w-14 h-14 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-3 text-slate-400">
                    <i data-lucide="warehouse" class="w-7 h-7"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Belum Ada Gudang atau Lokasi</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                    Daftarkan gudang utama, dapur produksi, atau cabang outlet untuk mulai mengelola pencatatan stok fisik.
                </p>
                @if(\App\Support\Context::hasPermission('inventory.manage') || \App\Support\Context::isAdminOrOwner() || \App\Support\Context::hasPermission('warehouse.manage'))
                <button type="button" @click="showCreateModal = true; gpsError = ''; gpsSuccess = false;"
                        class="mt-4 h-9 px-4 rounded-[10px] text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all inline-flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Tambah Gudang Pertama</span>
                </button>
                @endif
            </div>
            @endforelse
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- 5. INBOUND WORKFLOW GUIDE (Apple Horizontal Steps)    --}}
    {{-- ===================================================== --}}
    <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 shadow-xs">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Alur Masuk Barang &amp; Penerimaan PO</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Prosedur operasional standar penerimaan inventori fisik di gudang</p>
            </div>
            <span class="text-[11px] font-bold text-[#007AFF] bg-[#007AFF]/10 px-2.5 py-1 rounded-full">SOP Gudang</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            {{-- Step 1 --}}
            <div class="rounded-[12px] bg-slate-50 dark:bg-[#2C2C2E] p-3.5 border border-black/[0.04] dark:border-white/[0.04]">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-5 h-5 rounded-full bg-[#007AFF] text-white text-[11px] font-bold flex items-center justify-center tabular-nums">1</span>
                    <span class="text-xs font-bold text-slate-900 dark:text-white">Buat PO Supplier</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-snug">
                    Terbitkan Purchase Order ke vendor melalui menu PO dengan item dan harga acuan.
                </p>
            </div>

            {{-- Step 2 --}}
            <div class="rounded-[12px] bg-slate-50 dark:bg-[#2C2C2E] p-3.5 border border-black/[0.04] dark:border-white/[0.04]">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-5 h-5 rounded-full bg-[#007AFF] text-white text-[11px] font-bold flex items-center justify-center tabular-nums">2</span>
                    <span class="text-xs font-bold text-slate-900 dark:text-white">Fisik Tiba di Gudang</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-snug">
                    Vendor mengirim barang. Petugas gudang memeriksa surat jalan dan kondisi packaging.
                </p>
            </div>

            {{-- Step 3 --}}
            <div class="rounded-[12px] bg-slate-50 dark:bg-[#2C2C2E] p-3.5 border border-black/[0.04] dark:border-white/[0.04]">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-5 h-5 rounded-full bg-[#007AFF] text-white text-[11px] font-bold flex items-center justify-center tabular-nums">3</span>
                    <span class="text-xs font-bold text-slate-900 dark:text-white">Terima &amp; Rekam PO</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-snug">
                    Buka dokumen PO, klik <em>Terima Barang</em>, dan sistem otomatis menambahkan stok fisik.
                </p>
            </div>

            {{-- Step 4 --}}
            <div class="rounded-[12px] bg-slate-50 dark:bg-[#2C2C2E] p-3.5 border border-black/[0.04] dark:border-white/[0.04]">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-5 h-5 rounded-full bg-[#34C759] text-white text-[11px] font-bold flex items-center justify-center tabular-nums">✓</span>
                    <span class="text-xs font-bold text-slate-900 dark:text-white">Stok Terdistribusi</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-snug">
                    Stok terupdate real-time dan siap dipakai di POS kasir atau ditransfer antar cabang.
                </p>
            </div>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- 6. RECENT MOVEMENTS AUDIT TABLE                       --}}
    {{-- ===================================================== --}}
    @if(isset($recentMovements) && $recentMovements->isNotEmpty())
    <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden shadow-xs">
        <div class="px-4 py-3.5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Audit Trail Mutasi Terkini</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Log mutasi real-time dari transaksi POS, PO, dan penyesuaian stok</p>
            </div>
            <a href="{{ route('inventory.movements') }}"
               class="text-xs font-bold text-[#007AFF] hover:underline flex items-center gap-1">
                <span>Lihat Semua Mutasi</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        {{-- Movements Table (Desktop) --}}
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-black/[0.06] dark:border-white/[0.08] text-[11px] font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500 bg-slate-50/50 dark:bg-[#2C2C2E]/50">
                        <th class="px-4 py-3">Produk</th>
                        <th class="px-4 py-3">Gudang / Lokasi</th>
                        <th class="px-4 py-3">Tipe Mutasi</th>
                        <th class="px-4 py-3 text-right">Perubahan Qty</th>
                        <th class="px-4 py-3 text-right">Saldo Akhir</th>
                        <th class="px-4 py-3 text-right">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @foreach($recentMovements as $mv)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-900 dark:text-white">{{ $mv->product?->name ?? '-' }}</div>
                            @if($mv->product?->code)
                                <div class="text-[11px] font-mono tabular-nums text-slate-500 dark:text-slate-400">{{ $mv->product->code }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-slate-700 dark:text-slate-300 font-medium">{{ $mv->location?->name ?? '-' }}</td>
                        <td class="px-4 py-3.5">
                            @php
                                $mvLabels = [
                                    'goods_receipt'  => ['label' => 'Penerimaan PO', 'pill' => 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]'],
                                    'pos_sale'       => ['label' => 'Penjualan POS', 'pill' => 'bg-[#007AFF]/12 text-[#007AFF]'],
                                    'adjustment'     => ['label' => 'Penyesuaian', 'pill' => 'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]'],
                                    'transfer_in'    => ['label' => 'Transfer Masuk', 'pill' => 'bg-[#5856D6]/12 text-[#413FA6] dark:text-[#5E5CE6]'],
                                    'transfer_out'   => ['label' => 'Transfer Keluar', 'pill' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'],
                                    'opname'         => ['label' => 'Opname Fisik', 'pill' => 'bg-[#AF52DE]/12 text-[#7C3AA6] dark:text-[#BF5AF2]'],
                                ];
                                $mvInfo = $mvLabels[$mv->movement_type] ?? ['label' => ucfirst(str_replace('_', ' ', $mv->movement_type)), 'pill' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'];
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold {{ $mvInfo['pill'] }}">
                                {{ $mvInfo['label'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right tabular-nums font-bold {{ $mv->quantity_change >= 0 ? 'text-[#34C759] dark:text-[#30D158]' : 'text-[#FF3B30] dark:text-[#FF453A]' }}">
                            {{ $mv->quantity_change >= 0 ? '+' : '' }}{{ number_format($mv->quantity_change, 2) }}
                            <span class="text-[11px] text-slate-400 dark:text-slate-500 font-normal ml-0.5">{{ $mv->product?->outputUnit?->symbol }}</span>
                        </td>
                        <td class="px-4 py-3.5 text-right tabular-nums font-bold text-slate-900 dark:text-white">
                            {{ number_format($mv->balance_after, 2) }}
                        </td>
                        <td class="px-4 py-3.5 text-right text-[11px] text-slate-400 dark:text-slate-500 whitespace-nowrap font-medium">
                            {{ $mv->created_at->diffForHumans() }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Movements List (Mobile Only) --}}
        <div class="sm:hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
            @foreach($recentMovements as $mv)
            @php
                $mvLabels = [
                    'goods_receipt'  => ['label' => 'Penerimaan PO', 'pill' => 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]'],
                    'pos_sale'       => ['label' => 'Penjualan POS', 'pill' => 'bg-[#007AFF]/12 text-[#007AFF]'],
                    'adjustment'     => ['label' => 'Penyesuaian', 'pill' => 'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]'],
                    'transfer_in'    => ['label' => 'Transfer Masuk', 'pill' => 'bg-[#5856D6]/12 text-[#413FA6] dark:text-[#5E5CE6]'],
                    'transfer_out'   => ['label' => 'Transfer Keluar', 'pill' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'],
                    'opname'         => ['label' => 'Opname Fisik', 'pill' => 'bg-[#AF52DE]/12 text-[#7C3AA6] dark:text-[#BF5AF2]'],
                ];
                $mvInfo = $mvLabels[$mv->movement_type] ?? ['label' => ucfirst(str_replace('_', ' ', $mv->movement_type)), 'pill' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'];
            @endphp
            <div class="p-3.5 space-y-2 active:bg-black/[0.02] dark:active:bg-white/[0.02] transition-colors">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="font-bold text-xs text-slate-900 dark:text-white">{{ $mv->product?->name ?? '-' }}</div>
                        <div class="flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                            <span class="font-mono tabular-nums">{{ $mv->product?->code ?? '-' }}</span>
                            <span>&bull;</span>
                            <span>{{ $mv->location?->name ?? '-' }}</span>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $mvInfo['pill'] }} shrink-0">
                        {{ $mvInfo['label'] }}
                    </span>
                </div>
                <div class="flex items-center justify-between text-xs pt-1">
                    <span class="tabular-nums font-bold {{ $mv->quantity_change >= 0 ? 'text-[#34C759] dark:text-[#30D158]' : 'text-[#FF3B30] dark:text-[#FF453A]' }}">
                        {{ $mv->quantity_change >= 0 ? '+' : '' }}{{ number_format($mv->quantity_change, 2) }} {{ $mv->product?->outputUnit?->symbol }}
                    </span>
                    <span class="text-slate-500 dark:text-slate-400 text-[11px]">
                        Saldo: <strong class="text-slate-900 dark:text-white">{{ number_format($mv->balance_after, 2) }}</strong>
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ===================================================== --}}
    {{-- 7a. APPLE SHEET: TAMBAH GUDANG LOGISTIK               --}}
    {{-- ===================================================== --}}
    <div x-show="showCreateModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="w-full max-w-xl rounded-[22px] bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] shadow-2xl p-6 space-y-5 max-h-[90vh] overflow-y-auto"
             @click.outside="showCreateModal = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3.5">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="warehouse" class="w-5 h-5 text-[#007AFF]"></i>
                        <span>Tambah Gudang Logistik</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Titik penyimpanan stok fisik, pengiriman Biteship, dan absensi geofence</p>
                </div>
                <button type="button" @click="showCreateModal = false" class="p-1 rounded-[8px] text-slate-400 hover:text-slate-900 dark:hover:text-white transition cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('warehouse.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="type" value="warehouse">
                <input type="hidden" name="province" :value="warehouseForm.province">
                <input type="hidden" name="city" :value="warehouseForm.city">
                <input type="hidden" name="district" :value="warehouseForm.district">
                <input type="hidden" name="village" :value="warehouseForm.village">
                <input type="hidden" name="postal_code" :value="warehouseForm.postal_code">
                <input type="hidden" name="biteship_area_id" :value="warehouseForm.biteship_area_id">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Nama Gudang <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="text" name="name" x-model="warehouseForm.name" required placeholder="Contoh: Gudang Utama, Gudang Transit Jakarta, Gudang Bahan..."
                               class="w-full h-10 bg-slate-50 dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Kode Gudang
                        </label>
                        <input type="text" name="code" x-model="warehouseForm.code" placeholder="Misal: WH-01, GDG-JKT..."
                               class="w-full h-10 bg-slate-50 dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Nomor Telepon
                        </label>
                        <input type="text" name="phone" x-model="warehouseForm.phone" placeholder="08xxxxxxxxxx / +62..."
                               class="w-full h-10 bg-slate-50 dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                    </div>
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Alamat Lengkap Gudang <span class="text-[#FF3B30]">*</span>
                        </label>
                        <textarea name="address" x-model="warehouseForm.address" rows="2" required placeholder="Alamat fisik gudang: nomor jalan, blok, RT/RW, kelurahan, kecamatan, kota..."
                                  class="w-full bg-slate-50 dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] p-3 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition resize-none"></textarea>
                    </div>

                    {{-- 🧭 SECTION GEOFENCE & DETEKSI GPS --}}
                    <div class="col-span-1 sm:col-span-2 rounded-[16px] bg-[#007AFF]/5 dark:bg-[#007AFF]/10 border border-[#007AFF]/20 p-4 space-y-3.5">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-[6px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                                    <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs font-bold text-slate-900 dark:text-white">Geofence Absensi Karyawan</span>
                                <span class="text-[10px] font-semibold text-[#007AFF] bg-[#007AFF]/10 px-2 py-0.5 rounded-full">Opsional</span>
                            </div>

                            {{-- Tombol Live Detect GPS --}}
                            <button type="button" @click="detectGps('warehouse')" :disabled="gpsLoading"
                                    class="h-8 px-3 rounded-[8px] text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] cursor-pointer disabled:opacity-50">
                                <template x-if="gpsLoading && activeGpsTarget === 'warehouse'">
                                    <svg class="animate-spin -ml-0.5 mr-1 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </template>
                                <template x-if="!(gpsLoading && activeGpsTarget === 'warehouse')">
                                    <i data-lucide="crosshair" class="w-3.5 h-3.5"></i>
                                </template>
                                <span x-text="(gpsLoading && activeGpsTarget === 'warehouse') ? 'Mencari Titik GPS...' : '🧭 Deteksi GPS Saya'"></span>
                            </button>
                        </div>

                        {{-- Alert GPS Messages --}}
                        <template x-if="gpsSuccess && activeGpsTarget === 'warehouse'">
                            <div class="rounded-[10px] bg-[#34C759]/15 border border-[#34C759]/30 px-3 py-2 text-[11px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-2 font-medium">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759]"></i>
                                <span>Titik koordinat GPS berhasil dideteksi dan alamat wilayah otomatis disinkronkan.</span>
                            </div>
                        </template>

                        <template x-if="gpsError && activeGpsTarget === 'warehouse'">
                            <div class="rounded-[10px] bg-[#FF3B30]/15 border border-[#FF3B30]/30 px-3 py-2 text-[11px] text-[#C41E17] dark:text-[#FF453A] flex items-center gap-2 font-medium">
                                <i data-lucide="alert-circle" class="w-4 h-4 text-[#FF3B30]"></i>
                                <span x-text="gpsError"></span>
                            </div>
                        </template>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Latitude</label>
                                <input type="text" name="latitude" x-model="warehouseForm.latitude" placeholder="-6.2088"
                                       class="w-full h-9 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] px-2.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-[#007AFF] transition">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Longitude</label>
                                <input type="text" name="longitude" x-model="warehouseForm.longitude" placeholder="106.8456"
                                       class="w-full h-9 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] px-2.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-[#007AFF] transition">
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300">Radius Geofence (meter)</label>
                                <span class="text-[11px] font-bold text-[#007AFF] font-mono" x-text="warehouseForm.geofence_radius_meters + ' m'"></span>
                            </div>
                            <input type="number" name="geofence_radius_meters" x-model="warehouseForm.geofence_radius_meters" placeholder="100" min="10" max="5000"
                                   class="w-full h-9 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] px-2.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-[#007AFF] transition">
                            
                            {{-- Quick Presets --}}
                            <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                                <span class="text-[10px] text-slate-400">Pilihan Cepat:</span>
                                <button type="button" @click="warehouseForm.geofence_radius_meters = 50" class="px-2 py-0.5 rounded-[6px] text-[10px] font-semibold bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] text-slate-700 dark:text-slate-300">50m</button>
                                <button type="button" @click="warehouseForm.geofence_radius_meters = 100" class="px-2 py-0.5 rounded-[6px] text-[10px] font-semibold bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] text-slate-700 dark:text-slate-300">100m (Default)</button>
                                <button type="button" @click="warehouseForm.geofence_radius_meters = 200" class="px-2 py-0.5 rounded-[6px] text-[10px] font-semibold bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] text-slate-700 dark:text-slate-300">200m</button>
                                <button type="button" @click="warehouseForm.geofence_radius_meters = 500" class="px-2 py-0.5 rounded-[6px] text-[10px] font-semibold bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] text-slate-700 dark:text-slate-300">500m</button>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1.5">Radius area valid absensi GPS. Default: 100 meter dari titik pusat cabang.</p>
                        </div>
                    </div>

                    {{-- 📦 SECTION INTEGRASI BITESHIP & TOKO ONLINE PICKUP --}}
                    <div class="col-span-1 sm:col-span-2 rounded-[16px] bg-[#5856D6]/5 dark:bg-[#5856D6]/10 border border-[#5856D6]/20 p-4 space-y-3.5">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-[6px] bg-[#5856D6]/15 text-[#5856D6] flex items-center justify-center">
                                    <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs font-bold text-slate-900 dark:text-white">Integrasi Biteship &amp; Pickup Toko Online</span>
                            </div>
                            <span class="text-[10px] font-bold text-[#5856D6] bg-[#5856D6]/10 px-2 py-0.5 rounded-full">Biteship Logistics</span>
                        </div>

                        {{-- Area Search Box for Biteship --}}
                        <div class="relative">
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Hubungkan Wilayah Biteship (Kelurahan / Kecamatan / Kode Pos)
                            </label>

                            <template x-if="!warehouseForm.biteship_area_id">
                                <div class="relative">
                                    <input type="text"
                                           placeholder="Ketik min. 2 huruf (contoh: Tebet, Senayan, 12810)..."
                                           @input.debounce.300ms="searchBiteship('warehouse', $event.target.value)"
                                           class="w-full h-9 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] pl-8 pr-3 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-[#5856D6] transition">
                                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5"></i>
                                </div>
                            </template>

                            {{-- Selected Biteship Area Card --}}
                            <template x-if="warehouseForm.biteship_area_id">
                                <div class="flex items-center justify-between p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-[#5856D6]/30">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <i data-lucide="check-circle" class="w-4 h-4 text-[#34C759] shrink-0"></i>
                                        <div class="min-w-0">
                                            <span class="text-xs font-bold text-slate-900 dark:text-white block truncate" x-text="warehouseForm.biteship_area_label || warehouseForm.biteship_area_id"></span>
                                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">Biteship ID: <span x-text="warehouseForm.biteship_area_id"></span></span>
                                        </div>
                                    </div>
                                    <button type="button" @click="clearBiteshipArea('warehouse')" class="text-xs text-[#FF3B30] hover:underline font-semibold shrink-0 ml-2">
                                        Ganti
                                    </button>
                                </div>
                            </template>

                            {{-- Dropdown Autocomplete Results --}}
                            <div x-show="biteshipTarget === 'warehouse' && biteshipResults.length > 0"
                                 @click.outside="biteshipResults = []"
                                 class="absolute left-0 right-0 top-full mt-1 z-30 max-h-48 overflow-y-auto rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-xl divide-y divide-black/5 dark:divide-white/5">
                                <template x-for="item in biteshipResults" :key="item.id">
                                    <button type="button" @click="selectBiteshipArea('warehouse', item)"
                                            class="w-full text-left p-2.5 hover:bg-[#5856D6]/10 transition flex items-center justify-between gap-2 cursor-pointer">
                                        <div>
                                            <span class="text-xs font-bold text-slate-900 dark:text-white block" x-text="item.label || (item.village + ', ' + item.district + ', ' + item.city)"></span>
                                            <span class="text-[10px] text-slate-500 dark:text-slate-400" x-text="(item.province || '') + (item.postal_code ? ' • Kode Pos: ' + item.postal_code : '')"></span>
                                        </div>
                                        <span class="text-[10px] font-mono text-[#5856D6] font-bold shrink-0 bg-[#5856D6]/10 px-1.5 py-0.5 rounded">Pilih</span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Checkbox Primary Pickup Storefront --}}
                        <div class="space-y-2 pt-1">
                            <label class="flex items-start gap-2.5 p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] cursor-pointer hover:border-[#5856D6]/40 transition">
                                <input type="checkbox" name="is_primary" value="1" x-model="warehouseForm.is_primary" class="mt-0.5 w-4 h-4 rounded text-[#5856D6] focus:ring-[#5856D6]">
                                <div class="text-xs">
                                    <span class="font-bold text-slate-900 dark:text-white block">Jadikan Titik Pickup Utama Toko Online</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block">Pesanan online pengiriman kurir (JNE, SiCepat, J&T, GoSend, GrabExpress via Biteship) akan dipickup kurir dari lokasi ini.</span>
                                </div>
                            </label>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <label class="flex items-start gap-2 p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] cursor-pointer">
                                    <input type="checkbox" name="is_online_fulfillment" value="1" x-model="warehouseForm.is_online_fulfillment" class="mt-0.5 w-3.5 h-3.5 rounded text-[#007AFF] focus:ring-[#007AFF]">
                                    <span class="text-[11px] font-medium text-slate-800 dark:text-slate-200">Titik Kirim Kurir Online</span>
                                </label>
                                <label class="flex items-start gap-2 p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] cursor-pointer">
                                    <input type="checkbox" name="allow_storefront_pickup" value="1" x-model="warehouseForm.allow_storefront_pickup" class="mt-0.5 w-3.5 h-3.5 rounded text-[#007AFF] focus:ring-[#007AFF]">
                                    <span class="text-[11px] font-medium text-slate-800 dark:text-slate-200">Izinkan Ambil di Toko</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3.5 border-t border-black/[0.06] dark:border-white/[0.08]">
                    <button type="button" @click="showCreateModal = false"
                            class="h-9 px-4 rounded-[10px] text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                            class="h-9 px-4 rounded-[10px] text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all shadow-[0_1px_2px_rgba(0,122,255,0.25)] cursor-pointer">
                        Simpan Gudang
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- 7b. APPLE SHEET: TAMBAH CABANG / OUTLET               --}}
    {{-- ===================================================== --}}
    <div x-show="showCreateOutletModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="w-full max-w-xl rounded-[22px] bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] shadow-2xl p-6 space-y-5 max-h-[90vh] overflow-y-auto"
             @click.outside="showCreateOutletModal = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3.5">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="store" class="w-5 h-5 text-[#34C759]"></i>
                        <span>Tambah Cabang / Outlet</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Daftarkan cabang toko, outlet retail, titik pickup kurir, dan absensi geofence</p>
                </div>
                <button type="button" @click="showCreateOutletModal = false" class="p-1 rounded-[8px] text-slate-400 hover:text-slate-900 dark:hover:text-white transition cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('warehouse.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="type" value="outlet">
                <input type="hidden" name="province" :value="outletForm.province">
                <input type="hidden" name="city" :value="outletForm.city">
                <input type="hidden" name="district" :value="outletForm.district">
                <input type="hidden" name="village" :value="outletForm.village">
                <input type="hidden" name="postal_code" :value="outletForm.postal_code">
                <input type="hidden" name="biteship_area_id" :value="outletForm.biteship_area_id">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Nama Cabang / Outlet <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="text" name="name" x-model="outletForm.name" required placeholder="Contoh: Outlet Senopati, Cabang Bandung, Toko Pondok Indah..."
                               class="w-full h-10 bg-slate-50 dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#34C759] transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Kode Cabang
                        </label>
                        <input type="text" name="code" x-model="outletForm.code" placeholder="Misal: OTL-01, CBG-BDG..."
                               class="w-full h-10 bg-slate-50 dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#34C759] transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Nomor Telepon Cabang
                        </label>
                        <input type="text" name="phone" x-model="outletForm.phone" placeholder="08xxxxxxxxxx / +62..."
                               class="w-full h-10 bg-slate-50 dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#34C759] transition">
                    </div>
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Alamat Lengkap Cabang <span class="text-[#FF3B30]">*</span>
                        </label>
                        <textarea name="address" x-model="outletForm.address" rows="2" required placeholder="Alamat fisik cabang: jalan, nomor, RT/RW, kelurahan, kecamatan, kota..."
                                  class="w-full bg-slate-50 dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] p-3 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#34C759] transition resize-none"></textarea>
                    </div>

                    {{-- 🧭 SECTION GEOFENCE & DETEKSI GPS --}}
                    <div class="col-span-1 sm:col-span-2 rounded-[16px] bg-[#34C759]/5 dark:bg-[#34C759]/10 border border-[#34C759]/20 p-4 space-y-3.5">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-[6px] bg-[#34C759]/15 text-[#34C759] flex items-center justify-center">
                                    <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs font-bold text-slate-900 dark:text-white">Geofence Absensi Karyawan</span>
                                <span class="text-[10px] font-semibold text-[#34C759] bg-[#34C759]/10 px-2 py-0.5 rounded-full">Opsional</span>
                            </div>

                            {{-- Tombol Live Detect GPS --}}
                            <button type="button" @click="detectGps('outlet')" :disabled="gpsLoading"
                                    class="h-8 px-3 rounded-[8px] text-xs font-bold text-white bg-[#34C759] hover:bg-[#28A745] active:scale-[0.98] transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(52,199,89,0.25)] cursor-pointer disabled:opacity-50">
                                <template x-if="gpsLoading && activeGpsTarget === 'outlet'">
                                    <svg class="animate-spin -ml-0.5 mr-1 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </template>
                                <template x-if="!(gpsLoading && activeGpsTarget === 'outlet')">
                                    <i data-lucide="crosshair" class="w-3.5 h-3.5"></i>
                                </template>
                                <span x-text="(gpsLoading && activeGpsTarget === 'outlet') ? 'Mencari Titik GPS...' : '🧭 Deteksi GPS Saya'"></span>
                            </button>
                        </div>

                        {{-- Alert GPS Messages --}}
                        <template x-if="gpsSuccess && activeGpsTarget === 'outlet'">
                            <div class="rounded-[10px] bg-[#34C759]/15 border border-[#34C759]/30 px-3 py-2 text-[11px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-2 font-medium">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759]"></i>
                                <span>Titik koordinat GPS berhasil dideteksi dan alamat wilayah otomatis disinkronkan.</span>
                            </div>
                        </template>

                        <template x-if="gpsError && activeGpsTarget === 'outlet'">
                            <div class="rounded-[10px] bg-[#FF3B30]/15 border border-[#FF3B30]/30 px-3 py-2 text-[11px] text-[#C41E17] dark:text-[#FF453A] flex items-center gap-2 font-medium">
                                <i data-lucide="alert-circle" class="w-4 h-4 text-[#FF3B30]"></i>
                                <span x-text="gpsError"></span>
                            </div>
                        </template>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Latitude</label>
                                <input type="text" name="latitude" x-model="outletForm.latitude" placeholder="-6.2088"
                                       class="w-full h-9 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] px-2.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-[#34C759] transition">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Longitude</label>
                                <input type="text" name="longitude" x-model="outletForm.longitude" placeholder="106.8456"
                                       class="w-full h-9 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] px-2.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-[#34C759] transition">
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300">Radius Geofence (meter)</label>
                                <span class="text-[11px] font-bold text-[#34C759] font-mono" x-text="outletForm.geofence_radius_meters + ' m'"></span>
                            </div>
                            <input type="number" name="geofence_radius_meters" x-model="outletForm.geofence_radius_meters" placeholder="100" min="10" max="5000"
                                   class="w-full h-9 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] px-2.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-[#34C759] transition">
                            
                            {{-- Quick Presets --}}
                            <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                                <span class="text-[10px] text-slate-400">Pilihan Cepat:</span>
                                <button type="button" @click="outletForm.geofence_radius_meters = 50" class="px-2 py-0.5 rounded-[6px] text-[10px] font-semibold bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 hover:border-[#34C759] text-slate-700 dark:text-slate-300">50m</button>
                                <button type="button" @click="outletForm.geofence_radius_meters = 100" class="px-2 py-0.5 rounded-[6px] text-[10px] font-semibold bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 hover:border-[#34C759] text-slate-700 dark:text-slate-300">100m (Default)</button>
                                <button type="button" @click="outletForm.geofence_radius_meters = 200" class="px-2 py-0.5 rounded-[6px] text-[10px] font-semibold bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 hover:border-[#34C759] text-slate-700 dark:text-slate-300">200m</button>
                                <button type="button" @click="outletForm.geofence_radius_meters = 500" class="px-2 py-0.5 rounded-[6px] text-[10px] font-semibold bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 hover:border-[#34C759] text-slate-700 dark:text-slate-300">500m</button>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1.5">Radius area valid absensi GPS. Default: 100 meter dari titik pusat cabang.</p>
                        </div>
                    </div>

                    {{-- 📦 SECTION INTEGRASI BITESHIP & TOKO ONLINE PICKUP --}}
                    <div class="col-span-1 sm:col-span-2 rounded-[16px] bg-[#5856D6]/5 dark:bg-[#5856D6]/10 border border-[#5856D6]/20 p-4 space-y-3.5">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-[6px] bg-[#5856D6]/15 text-[#5856D6] flex items-center justify-center">
                                    <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs font-bold text-slate-900 dark:text-white">Integrasi Biteship &amp; Pickup Toko Online</span>
                            </div>
                            <span class="text-[10px] font-bold text-[#5856D6] bg-[#5856D6]/10 px-2 py-0.5 rounded-full">Biteship Logistics</span>
                        </div>

                        {{-- Area Search Box for Biteship --}}
                        <div class="relative">
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Hubungkan Wilayah Biteship (Kelurahan / Kecamatan / Kode Pos)
                            </label>

                            <template x-if="!outletForm.biteship_area_id">
                                <div class="relative">
                                    <input type="text"
                                           placeholder="Ketik min. 2 huruf (contoh: Tebet, Senayan, 12810)..."
                                           @input.debounce.300ms="searchBiteship('outlet', $event.target.value)"
                                           class="w-full h-9 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] pl-8 pr-3 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-[#5856D6] transition">
                                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5"></i>
                                </div>
                            </template>

                            {{-- Selected Biteship Area Card --}}
                            <template x-if="outletForm.biteship_area_id">
                                <div class="flex items-center justify-between p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-[#5856D6]/30">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <i data-lucide="check-circle" class="w-4 h-4 text-[#34C759] shrink-0"></i>
                                        <div class="min-w-0">
                                            <span class="text-xs font-bold text-slate-900 dark:text-white block truncate" x-text="outletForm.biteship_area_label || outletForm.biteship_area_id"></span>
                                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">Biteship ID: <span x-text="outletForm.biteship_area_id"></span></span>
                                        </div>
                                    </div>
                                    <button type="button" @click="clearBiteshipArea('outlet')" class="text-xs text-[#FF3B30] hover:underline font-semibold shrink-0 ml-2">
                                        Ganti
                                    </button>
                                </div>
                            </template>

                            {{-- Dropdown Autocomplete Results --}}
                            <div x-show="biteshipTarget === 'outlet' && biteshipResults.length > 0"
                                 @click.outside="biteshipResults = []"
                                 class="absolute left-0 right-0 top-full mt-1 z-30 max-h-48 overflow-y-auto rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-xl divide-y divide-black/5 dark:divide-white/5">
                                <template x-for="item in biteshipResults" :key="item.id">
                                    <button type="button" @click="selectBiteshipArea('outlet', item)"
                                            class="w-full text-left p-2.5 hover:bg-[#5856D6]/10 transition flex items-center justify-between gap-2 cursor-pointer">
                                        <div>
                                            <span class="text-xs font-bold text-slate-900 dark:text-white block" x-text="item.label || (item.village + ', ' + item.district + ', ' + item.city)"></span>
                                            <span class="text-[10px] text-slate-500 dark:text-slate-400" x-text="(item.province || '') + (item.postal_code ? ' • Kode Pos: ' + item.postal_code : '')"></span>
                                        </div>
                                        <span class="text-[10px] font-mono text-[#5856D6] font-bold shrink-0 bg-[#5856D6]/10 px-1.5 py-0.5 rounded">Pilih</span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Checkbox Primary Pickup Storefront --}}
                        <div class="space-y-2 pt-1">
                            <label class="flex items-start gap-2.5 p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] cursor-pointer hover:border-[#5856D6]/40 transition">
                                <input type="checkbox" name="is_primary" value="1" x-model="outletForm.is_primary" class="mt-0.5 w-4 h-4 rounded text-[#5856D6] focus:ring-[#5856D6]">
                                <div class="text-xs">
                                    <span class="font-bold text-slate-900 dark:text-white block">Jadikan Titik Pickup Utama Toko Online</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block">Pesanan online pengiriman kurir (JNE, SiCepat, J&T, GoSend, GrabExpress via Biteship) akan dipickup kurir dari lokasi ini.</span>
                                </div>
                            </label>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <label class="flex items-start gap-2 p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] cursor-pointer">
                                    <input type="checkbox" name="is_online_fulfillment" value="1" x-model="outletForm.is_online_fulfillment" class="mt-0.5 w-3.5 h-3.5 rounded text-[#34C759] focus:ring-[#34C759]">
                                    <span class="text-[11px] font-medium text-slate-800 dark:text-slate-200">Titik Kirim Kurir Online</span>
                                </label>
                                <label class="flex items-start gap-2 p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] cursor-pointer">
                                    <input type="checkbox" name="allow_storefront_pickup" value="1" x-model="outletForm.allow_storefront_pickup" class="mt-0.5 w-3.5 h-3.5 rounded text-[#34C759] focus:ring-[#34C759]">
                                    <span class="text-[11px] font-medium text-slate-800 dark:text-slate-200">Izinkan Ambil di Toko</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3.5 border-t border-black/[0.06] dark:border-white/[0.08]">
                    <button type="button" @click="showCreateOutletModal = false"
                            class="h-9 px-4 rounded-[10px] text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                            class="h-9 px-4 rounded-[10px] text-xs font-bold text-white bg-[#34C759] hover:bg-[#28A745] active:scale-[0.98] transition-all shadow-[0_1px_2px_rgba(52,199,89,0.25)] cursor-pointer">
                        Simpan Cabang
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- 8. APPLE SHEET: EDIT INFORMASI LOKASI                 --}}
    {{-- ===================================================== --}}
    <div x-show="showEditModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="w-full max-w-xl rounded-[22px] bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] shadow-2xl p-6 space-y-5 max-h-[90vh] overflow-y-auto"
             @click.outside="showEditModal = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3.5">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="pencil-line" class="w-5 h-5 text-[#007AFF]"></i>
                        <span>Edit Informasi Lokasi</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400" x-text="'Memperbarui: ' + editData.name"></p>
                </div>
                <button type="button" @click="showEditModal = false" class="p-1 rounded-[8px] text-slate-400 hover:text-slate-900 dark:hover:text-white transition cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'{{ url('/warehouse') }}/' + editData.id" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <input type="hidden" name="province" :value="editData.province">
                <input type="hidden" name="city" :value="editData.city">
                <input type="hidden" name="district" :value="editData.district">
                <input type="hidden" name="village" :value="editData.village">
                <input type="hidden" name="postal_code" :value="editData.postal_code">
                <input type="hidden" name="biteship_area_id" :value="editData.biteship_area_id">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Nama Gudang / Lokasi <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="text" name="name" x-model="editData.name" required
                               class="w-full h-10 bg-slate-50 dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Tipe Lokasi
                        </label>
                        <select name="type" x-model="editData.type" class="w-full h-10 bg-slate-50 dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                            <option value="warehouse">Gudang (Warehouse)</option>
                            <option value="outlet">Outlet / Toko</option>
                            <option value="central_kitchen">Dapur Pusat (Central Kitchen)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Kode Lokasi
                        </label>
                        <input type="text" name="code" x-model="editData.code"
                               class="w-full h-10 bg-slate-50 dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Nomor Telepon
                        </label>
                        <input type="text" name="phone" x-model="editData.phone"
                               class="w-full h-10 bg-slate-50 dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                    </div>
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Alamat Lengkap
                        </label>
                        <textarea name="address" rows="2" x-model="editData.address"
                                  class="w-full bg-slate-50 dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] p-3 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition resize-none"></textarea>
                    </div>

                    {{-- 🧭 SECTION GEOFENCE & DETEKSI GPS --}}
                    <div class="col-span-1 sm:col-span-2 rounded-[16px] bg-[#007AFF]/5 dark:bg-[#007AFF]/10 border border-[#007AFF]/20 p-4 space-y-3.5">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-[6px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                                    <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs font-bold text-[#007AFF]">Koordinat GPS &amp; Geofence Absensi</span>
                                <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400">(Opsional)</span>
                            </div>

                            {{-- Tombol Live Detect GPS --}}
                            <button type="button" @click="detectGps('edit')" :disabled="gpsLoading"
                                    class="h-8 px-3 rounded-[8px] text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] cursor-pointer disabled:opacity-50">
                                <template x-if="gpsLoading && activeGpsTarget === 'edit'">
                                    <svg class="animate-spin -ml-0.5 mr-1 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </template>
                                <template x-if="!(gpsLoading && activeGpsTarget === 'edit')">
                                    <i data-lucide="crosshair" class="w-3.5 h-3.5"></i>
                                </template>
                                <span x-text="(gpsLoading && activeGpsTarget === 'edit') ? 'Mencari Titik GPS...' : '🧭 Deteksi GPS Saya'"></span>
                            </button>
                        </div>

                        {{-- Alert GPS Messages --}}
                        <template x-if="gpsSuccess && activeGpsTarget === 'edit'">
                            <div class="rounded-[10px] bg-[#34C759]/15 border border-[#34C759]/30 px-3 py-2 text-[11px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-2 font-medium">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759]"></i>
                                <span>Titik koordinat GPS berhasil dideteksi dan alamat wilayah otomatis disinkronkan.</span>
                            </div>
                        </template>

                        <template x-if="gpsError && activeGpsTarget === 'edit'">
                            <div class="rounded-[10px] bg-[#FF3B30]/15 border border-[#FF3B30]/30 px-3 py-2 text-[11px] text-[#C41E17] dark:text-[#FF453A] flex items-center gap-2 font-medium">
                                <i data-lucide="alert-circle" class="w-4 h-4 text-[#FF3B30]"></i>
                                <span x-text="gpsError"></span>
                            </div>
                        </template>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Latitude</label>
                                <input type="text" name="latitude" x-model="editData.latitude" placeholder="-6.2088"
                                       class="w-full h-9 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] px-2.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-[#007AFF] transition">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Longitude</label>
                                <input type="text" name="longitude" x-model="editData.longitude" placeholder="106.8456"
                                       class="w-full h-9 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] px-2.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-[#007AFF] transition">
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300">Radius Geofence (meter)</label>
                                <span class="text-[11px] font-bold text-[#007AFF] font-mono" x-text="editData.geofence_radius_meters + ' m'"></span>
                            </div>
                            <input type="number" name="geofence_radius_meters" x-model="editData.geofence_radius_meters" placeholder="100" min="10" max="5000"
                                   class="w-full h-9 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] px-2.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-[#007AFF] transition">
                            
                            {{-- Quick Presets --}}
                            <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                                <span class="text-[10px] text-slate-400">Pilihan Cepat:</span>
                                <button type="button" @click="editData.geofence_radius_meters = 50" class="px-2 py-0.5 rounded-[6px] text-[10px] font-semibold bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] text-slate-700 dark:text-slate-300">50m</button>
                                <button type="button" @click="editData.geofence_radius_meters = 100" class="px-2 py-0.5 rounded-[6px] text-[10px] font-semibold bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] text-slate-700 dark:text-slate-300">100m (Default)</button>
                                <button type="button" @click="editData.geofence_radius_meters = 200" class="px-2 py-0.5 rounded-[6px] text-[10px] font-semibold bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] text-slate-700 dark:text-slate-300">200m</button>
                                <button type="button" @click="editData.geofence_radius_meters = 500" class="px-2 py-0.5 rounded-[6px] text-[10px] font-semibold bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] text-slate-700 dark:text-slate-300">500m</button>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1.5">Radius area valid absensi GPS. Default: 100 meter dari titik pusat cabang.</p>
                        </div>
                    </div>

                    {{-- 📦 SECTION INTEGRASI BITESHIP & TOKO ONLINE PICKUP --}}
                    <div class="col-span-1 sm:col-span-2 rounded-[16px] bg-[#5856D6]/5 dark:bg-[#5856D6]/10 border border-[#5856D6]/20 p-4 space-y-3.5">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-[6px] bg-[#5856D6]/15 text-[#5856D6] flex items-center justify-center">
                                    <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs font-bold text-slate-900 dark:text-white">Integrasi Biteship &amp; Pickup Toko Online</span>
                            </div>
                            <span class="text-[10px] font-bold text-[#5856D6] bg-[#5856D6]/10 px-2 py-0.5 rounded-full">Biteship Logistics</span>
                        </div>

                        {{-- Area Search Box for Biteship --}}
                        <div class="relative">
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Hubungkan Wilayah Biteship (Kelurahan / Kecamatan / Kode Pos)
                            </label>

                            <template x-if="!editData.biteship_area_id">
                                <div class="relative">
                                    <input type="text"
                                           placeholder="Ketik min. 2 huruf (contoh: Tebet, Senayan, 12810)..."
                                           @input.debounce.300ms="searchBiteship('edit', $event.target.value)"
                                           class="w-full h-9 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] pl-8 pr-3 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-[#5856D6] transition">
                                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5"></i>
                                </div>
                            </template>

                            {{-- Selected Biteship Area Card --}}
                            <template x-if="editData.biteship_area_id">
                                <div class="flex items-center justify-between p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-[#5856D6]/30">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <i data-lucide="check-circle" class="w-4 h-4 text-[#34C759] shrink-0"></i>
                                        <div class="min-w-0">
                                            <span class="text-xs font-bold text-slate-900 dark:text-white block truncate" x-text="editData.biteship_area_label || editData.biteship_area_id"></span>
                                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">Biteship ID: <span x-text="editData.biteship_area_id"></span></span>
                                        </div>
                                    </div>
                                    <button type="button" @click="clearBiteshipArea('edit')" class="text-xs text-[#FF3B30] hover:underline font-semibold shrink-0 ml-2">
                                        Ganti
                                    </button>
                                </div>
                            </template>

                            {{-- Dropdown Autocomplete Results --}}
                            <div x-show="biteshipTarget === 'edit' && biteshipResults.length > 0"
                                 @click.outside="biteshipResults = []"
                                 class="absolute left-0 right-0 top-full mt-1 z-30 max-h-48 overflow-y-auto rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-xl divide-y divide-black/5 dark:divide-white/5">
                                <template x-for="item in biteshipResults" :key="item.id">
                                    <button type="button" @click="selectBiteshipArea('edit', item)"
                                            class="w-full text-left p-2.5 hover:bg-[#5856D6]/10 transition flex items-center justify-between gap-2 cursor-pointer">
                                        <div>
                                            <span class="text-xs font-bold text-slate-900 dark:text-white block" x-text="item.label || (item.village + ', ' + item.district + ', ' + item.city)"></span>
                                            <span class="text-[10px] text-slate-500 dark:text-slate-400" x-text="(item.province || '') + (item.postal_code ? ' • Kode Pos: ' + item.postal_code : '')"></span>
                                        </div>
                                        <span class="text-[10px] font-mono text-[#5856D6] font-bold shrink-0 bg-[#5856D6]/10 px-1.5 py-0.5 rounded">Pilih</span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Checkbox Primary Pickup Storefront --}}
                        <div class="space-y-2 pt-1">
                            <label class="flex items-start gap-2.5 p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] cursor-pointer hover:border-[#5856D6]/40 transition">
                                <input type="checkbox" name="is_primary" value="1" x-model="editData.is_primary" class="mt-0.5 w-4 h-4 rounded text-[#5856D6] focus:ring-[#5856D6]">
                                <div class="text-xs">
                                    <span class="font-bold text-slate-900 dark:text-white block">Jadikan Titik Pickup Utama Toko Online</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block">Pesanan online pengiriman kurir (JNE, SiCepat, J&T, GoSend, GrabExpress via Biteship) akan dipickup kurir dari lokasi ini.</span>
                                </div>
                            </label>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <label class="flex items-start gap-2 p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] cursor-pointer">
                                    <input type="checkbox" name="is_online_fulfillment" value="1" x-model="editData.is_online_fulfillment" class="mt-0.5 w-3.5 h-3.5 rounded text-[#007AFF] focus:ring-[#007AFF]">
                                    <span class="text-[11px] font-medium text-slate-800 dark:text-slate-200">Titik Kirim Kurir Online</span>
                                </label>
                                <label class="flex items-start gap-2 p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] cursor-pointer">
                                    <input type="checkbox" name="allow_storefront_pickup" value="1" x-model="editData.allow_storefront_pickup" class="mt-0.5 w-3.5 h-3.5 rounded text-[#007AFF] focus:ring-[#007AFF]">
                                    <span class="text-[11px] font-medium text-slate-800 dark:text-slate-200">Izinkan Ambil di Toko</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-1 sm:col-span-2 flex items-center gap-3 p-3.5 rounded-[12px] bg-slate-50 dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1"
                               x-model="editData.is_active" class="w-4 h-4 rounded text-[#007AFF] focus:ring-[#007AFF]">
                        <label for="edit_is_active" class="text-xs text-slate-800 dark:text-slate-200 font-semibold cursor-pointer">
                            Lokasi beroperasi aktif (dapat menerima PO, transaksi kasir, transfer stok, dan alokasi produk)
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3.5 border-t border-black/[0.06] dark:border-white/[0.08]">
                    <button type="button" @click="showEditModal = false"
                            class="h-9 px-4 rounded-[10px] text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                            class="h-9 px-4 rounded-[10px] text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all shadow-[0_1px_2px_rgba(0,122,255,0.25)] cursor-pointer">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- 9. APPLE ALERT DIALOG (Centered Confirmation)         --}}
    {{-- ===================================================== --}}
    <div x-show="deleteModalOpen"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="w-[300px] rounded-[18px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-2xl border border-black/[0.08] dark:border-white/[0.12]"
             @click.away="closeDelete()"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div class="px-5 pt-5 pb-4">
                <div class="w-10 h-10 rounded-full bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </div>
                <p class="text-base font-bold text-slate-900 dark:text-white">Hapus Lokasi?</p>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 leading-snug">
                    <span x-text="deleteTarget.name" class="font-semibold text-slate-900 dark:text-white"></span> akan dihapus dari sistem. Pastikan tidak ada saldo stok atau mutasi aktif.
                </p>
            </div>

            <div class="grid grid-cols-2 border-t border-black/[0.08] dark:border-white/[0.12] text-sm font-semibold">
                <button type="button" @click="closeDelete()"
                        class="py-3 text-[#007AFF] border-r border-black/[0.08] dark:border-white/[0.12] active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="button" @click="submitDelete()"
                        class="py-3 text-[#FF3B30] font-bold active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">
                    Hapus
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
