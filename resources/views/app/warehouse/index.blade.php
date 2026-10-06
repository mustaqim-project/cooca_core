@extends('layouts.app', [
    'title' => __('warehouse.title'),
    'headerTitle' => __('warehouse.header_title'),
    'headerSubtitle' => __('warehouse.header_subtitle')
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="{
    showCreateModal: new URLSearchParams(window.location.search).get('add') === 'warehouse',
    showCreateOutletModal: false,
    showEditModal: false,
    filterTab: new URLSearchParams(window.location.search).get('type') || 'all',
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
        parent_id: '',
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
        parent_id: '',
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
        is_active: true,
        timezone_mode: 'inherit',
        timezone: '{{ $business->timezone ?? "Asia/Jakarta" }}',
        operating_hours_mode: 'inherit',
        operating_hours: null
    },

    defaultOperatingHours: @json(\App\Support\TimezoneHelper::normalizeOperatingHours($business->operating_hours)),

    addEditPeriod(dayKey) {
        if (!this.editData.operating_hours) {
            this.editData.operating_hours = JSON.parse(JSON.stringify(this.defaultOperatingHours));
        }
        if (!this.editData.operating_hours[dayKey]) {
            this.editData.operating_hours[dayKey] = { day_name: dayKey, is_open: true, periods: [] };
        }
        if (!Array.isArray(this.editData.operating_hours[dayKey].periods)) {
            this.editData.operating_hours[dayKey].periods = [];
        }
        this.editData.operating_hours[dayKey].periods.push({ start: '17:00', end: '22:00' });
    },

    removeEditPeriod(dayKey, index) {
        if (this.editData.operating_hours && this.editData.operating_hours[dayKey] && Array.isArray(this.editData.operating_hours[dayKey].periods)) {
            this.editData.operating_hours[dayKey].periods.splice(index, 1);
        }
    },

    isOvernight(period) {
        if (!period || !period.start || !period.end) return false;
        return period.end < period.start;
    },

    // Biteship Search Autocomplete State
    biteshipQuery: '',
    biteshipResults: [],
    biteshipSearching: false,
    biteshipTarget: null,

    deleteModalOpen: false,
    deleteTarget: { id: null, name: '' },

    setFilter(type) {
        this.filterTab = type;
        const url = new URL(window.location.href);
        if (type === 'all') {
            url.searchParams.delete('type');
        } else {
            url.searchParams.set('type', type);
        }
        window.history.replaceState({}, '', url.toString());
    },

    openEdit(loc) {
        this.editData = {
            id: loc.id,
            parent_id: loc.parent_id || '',
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
            is_active: Boolean(loc.is_active),
            timezone_mode: loc.timezone_mode || 'inherit',
            timezone: loc.timezone || '{{ $business->timezone ?? "Asia/Jakarta" }}',
            operating_hours_mode: loc.operating_hours_mode || 'inherit',
            operating_hours: loc.operating_hours ? JSON.parse(JSON.stringify(loc.operating_hours)) : JSON.parse(JSON.stringify(this.defaultOperatingHours))
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
            this.gpsError = '{{ __("warehouse.actions.detecting_gps") }}';
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
                    this.gpsError = '{{ __("warehouse.gps_errors.permission_denied") }}';
                } else if (err.code === 2) {
                    this.gpsError = '{{ __("warehouse.gps_errors.position_unavailable") }}';
                } else {
                    this.gpsError = '{{ __("warehouse.gps_errors.timeout") }}';
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
    {{-- 1. TOOLBAR / PAGE HEADER                                --}}
    {{-- ===================================================== --}}
    <x-module-header
        :title="__('warehouse.header_title')"
        :subtitle="__('warehouse.header_subtitle')">
        @if($business->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_RECIPE_BOM) && \App\Support\Context::hasPermission('inventory.view'))
            <a href="{{ route('materials.index') }}"
               class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="boxes" class="w-4 h-4 text-black/50 dark:text-white/50"></i>
                <span>{{ __('warehouse.actions.material_catalog') }}</span>
            </a>
        @endif
        @if(\App\Support\Context::hasPermission('inventory.view'))
            <a href="{{ route('products.index') }}"
               class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="package" class="w-4 h-4 text-black/50 dark:text-white/50"></i>
                <span>{{ __('warehouse.actions.product_catalog') }}</span>
            </a>
        @endif

        @if(($business->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_MERCHANT_SHIPPING) || $business->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_STOREFRONT_CHECKOUT)) && (\App\Support\Context::hasPermission('storefront.shipping.manage') || \App\Support\Context::isAdminOrOwner()))
            <a href="{{ route('storefront.shipping.index') }}"
               class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-semibold text-[#5856D6] dark:text-[#A78BFA] bg-[#5856D6]/10 hover:bg-[#5856D6]/15 active:scale-[0.98] transition-all flex items-center justify-center gap-1.5"
               title="{{ __('warehouse.actions.storefront_shipping') }}">
                <i data-lucide="truck" class="w-4 h-4 text-[#5856D6] dark:text-[#A78BFA]"></i>
                <span>{{ __('warehouse.actions.storefront_shipping') }}</span>
            </a>
        @endif

        @if(\App\Support\Context::hasPermission('inventory.manage') || \App\Support\Context::isAdminOrOwner() || \App\Support\Context::hasPermission('warehouse.manage'))
            <a href="{{ route('settings.index', ['tab' => 'branches']) }}"
               class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-semibold text-[#34C759] dark:text-[#30D158] bg-[#34C759]/10 hover:bg-[#34C759]/15 active:scale-[0.98] transition-all flex items-center justify-center gap-1.5"
               title="{{ __('settings.tab_branches') ?? 'Kelola Cabang di Settings' }}">
                <i data-lucide="store" class="w-4 h-4"></i>
                <span>{{ __('warehouse.actions.manage_branches_link') ?? 'Kelola Cabang' }}</span>
            </a>
            <button type="button" @click="showCreateModal = true; gpsError = ''; gpsSuccess = false;"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] cursor-pointer">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>{{ __('warehouse.actions.add_warehouse') }}</span>
            </button>
        @endif
    </x-module-header>

    {{-- ===================================================== --}}
    {{-- 2. MODULE TABS (SSOT)                                   --}}
    {{-- ===================================================== --}}
    <x-module-tabs module="inventory" />

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
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('warehouse.kpis.total_locations') }}</span>
                <div class="w-7 h-7 rounded-[8px] bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-black tabular-nums text-slate-900 dark:text-white">{{ $totalWarehouses ?? $locations->count() }}</span>
                <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">{{ __('warehouse.kpis.branches_warehouses') }}</span>
            </div>
        </div>

        {{-- Tile 2: Lokasi Aktif --}}
        <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('warehouse.kpis.active_locations') }}</span>
                <div class="w-7 h-7 rounded-[8px] bg-[#34C759]/10 flex items-center justify-center text-[#34C759]">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-black tabular-nums text-[#34C759] dark:text-[#30D158]">{{ $activeWarehouses ?? $locations->where('is_active', true)->count() }}</span>
                <span class="text-[11px] font-semibold text-[#34C759] dark:text-[#30D158]">{{ __('warehouse.kpis.ready_to_operate') }}</span>
            </div>
        </div>

        {{-- Tile 3: Total Nilai Aset Stok --}}
        <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('warehouse.kpis.stock_asset_value') }}</span>
                <div class="w-7 h-7 rounded-[8px] bg-[#5856D6]/10 flex items-center justify-center text-[#5856D6]">
                    <i data-lucide="badge-dollar-sign" class="w-3.5 h-3.5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-xl sm:text-2xl font-black tabular-nums text-[#5856D6] dark:text-[#5E5CE6] truncate">
                    Rp {{ number_format($totalValuation ?? 0, 0, ',', '.') }}
                </span>
                <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">{{ __('warehouse.kpis.cogs_valuation') }}</span>
            </div>
        </div>

        {{-- Tile 4: Stok Perlu Restock / Total Unit --}}
        <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('warehouse.kpis.low_stock') }}</span>
                <div class="w-7 h-7 rounded-[8px] {{ ($totalLowStock ?? 0) > 0 ? 'bg-[#FF9500]/10 text-[#FF9500]' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }} flex items-center justify-center">
                    <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-black tabular-nums {{ ($totalLowStock ?? 0) > 0 ? 'text-[#FF9500] dark:text-[#FF9F0A]' : 'text-slate-900 dark:text-white' }}">{{ $totalLowStock ?? 0 }}</span>
                <span class="text-[11px] {{ ($totalLowStock ?? 0) > 0 ? 'text-[#FF9500] dark:text-[#FF9F0A] font-bold' : 'text-slate-400 dark:text-slate-500 font-medium' }}">{{ ($totalLowStock ?? 0) > 0 ? __('warehouse.kpis.need_restock') : __('warehouse.kpis.safe_threshold') }}</span>
            </div>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- 4. LOCATIONS GRID (Apple Squircle Bento Cards)        --}}
    {{-- ===================================================== --}}
    <div class="space-y-4">
        {{-- Banner Edukasi Pemusatan Cabang ke Settings --}}
        <div class="rounded-[16px] bg-gradient-to-r from-[#007AFF]/5 via-[#34C759]/5 to-transparent border border-black/[0.06] dark:border-white/[0.08] p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-[12px] bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] flex items-center justify-center shrink-0 border border-[#34C759]/20">
                    <i data-lucide="store" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">Pusat Kelola & Tambah Cabang Telah Dipindahkan</h4>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Tambah dan kelola cabang/toko kini dipusatkan di Pengaturan Bisnis. Halaman ini khusus untuk mengelola Gudang Logistik (1 cabang dapat memiliki banyak gudang).</p>
                </div>
            </div>
            <a href="{{ route('settings.index', ['tab' => 'branches']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[9px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-xs font-semibold text-[#007AFF] hover:bg-black/[0.03] dark:hover:bg-white/[0.05] transition-all shrink-0">
                <i data-lucide="settings" class="w-3.5 h-3.5"></i>
                <span>Buka Pengaturan Cabang</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <h2 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">{{ __('warehouse.tabs.all_locations') }}</h2>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 tabular-nums">
                    {{ $locations->count() }}
                </span>
            </div>
            {{-- Tab Filter: Semua | Cabang & Toko | Gudang Logistik --}}
            <div class="inline-flex p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.07] border border-black/5 dark:border-white/10 text-xs font-medium">
                <button @click="setFilter('all')"
                        :class="filterTab === 'all' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-[8px] transition-all flex items-center gap-1.5 cursor-pointer">
                    {{ __('warehouse.tabs.all_locations') }}
                    <span class="px-1.5 rounded-full text-[11px] tabular-nums font-semibold bg-black/[0.06] dark:bg-white/[0.08]" x-text="{{ $locations->count() }}"></span>
                </button>
                <button @click="setFilter('outlet')"
                        :class="filterTab === 'outlet' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-[8px] transition-all flex items-center gap-1.5 cursor-pointer">
                    <span class="w-2 h-2 rounded-full bg-[#34C759]"></span> {{ __('warehouse.tabs.branches_outlets') }}
                    <span class="px-1.5 rounded-full text-[11px] tabular-nums font-semibold bg-black/[0.06] dark:bg-white/[0.08]">{{ $locations->filter(fn($l) => in_array($l->type, ['outlet', 'store', 'central_kitchen']))->count() }}</span>
                </button>
                <button @click="setFilter('warehouse')"
                        :class="filterTab === 'warehouse' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-[8px] transition-all flex items-center gap-1.5 cursor-pointer">
                    <span class="w-2 h-2 rounded-full bg-[#007AFF]"></span> {{ __('warehouse.tabs.logistics_warehouses') }}
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
                                    @php
                                        $isStorefrontEnabled = ($business->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_MERCHANT_SHIPPING) || $business->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_STOREFRONT_CHECKOUT));
                                        $isActiveStorefrontOrigin = ($storeSetting && (string)$storeSetting->origin_location_id === (string)$loc->id)
                                            || ($isStorefrontEnabled && $loc->is_primary && empty($storeSetting?->origin_location_id));
                                    @endphp
                                    @if($isActiveStorefrontOrigin)
                                        <a href="{{ route('storefront.shipping.index') }}" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30 hover:bg-emerald-500/25 transition-colors" title="{{ __('warehouse.badges.storefront_shipping') }}">
                                            <i data-lucide="truck" class="w-3 h-3 text-emerald-600 dark:text-emerald-400"></i>
                                            <span>{{ __('warehouse.badges.storefront_origin') }}</span>
                                        </a>
                                    @elseif($loc->is_primary)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500]/15 text-[#D97706] dark:text-[#FBBF24] border border-[#FF9500]/30" title="{{ __('warehouse.badges.primary') }}">
                                            <i data-lucide="building" class="w-3 h-3"></i>
                                            <span>{{ __('warehouse.badges.primary') }}</span>
                                        </span>
                                    @endif

                                    {{-- Hierarchical Location Badges --}}
                                    @if($loc->parent_id && $loc->parent)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#5856D6]/15 text-[#5856D6] dark:text-[#A78BFA] border border-[#5856D6]/30" title="{{ __('warehouse.badges.parent', ['name' => $loc->parent->name]) }}">
                                            <i data-lucide="corner-down-right" class="w-3 h-3"></i>
                                            <span>{{ __('warehouse.types.sub_warehouse') }}: {{ $loc->parent->name }}</span>
                                        </span>
                                    @elseif($loc->type === 'warehouse' && empty($loc->parent_id))
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#007AFF]/15 text-[#007AFF] border border-[#007AFF]/30" title="{{ __('warehouse.types.central_warehouse') }}">
                                            <i data-lucide="boxes" class="w-3 h-3"></i>
                                            <span>{{ __('warehouse.types.central_warehouse') }}</span>
                                        </span>
                                    @endif
                                    @if($loc->children && $loc->children->isNotEmpty())
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/30" title="{{ $loc->children->pluck('name')->implode(', ') }}">
                                            <i data-lucide="git-branch" class="w-3 h-3"></i>
                                            <span class="tabular-nums">{{ $loc->children->count() }} {{ __('warehouse.types.sub_warehouse') }}</span>
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
                                            {{ __('warehouse.types.warehouse') }}
                                        @elseif($loc->type === 'central_kitchen')
                                            @if(str_starts_with($business->template_code ?? '', 'mfg_'))
                                                {{ __('warehouse.types.central_kitchen_mfg') }}
                                            @elseif(($business->template_code ?? '') === 'service_contractor')
                                                {{ __('warehouse.types.central_kitchen_contractor') }}
                                            @else
                                                {{ __('warehouse.types.central_kitchen') }}
                                            @endif
                                        @else
                                            {{ __('warehouse.types.outlet') }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Status Pill --}}
                        <div>
                            @if($loc->is_active)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> {{ __('warehouse.badges.active') }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> {{ __('warehouse.badges.inactive') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Badges: GPS Geofence, Biteship Logistics, Online Fulfillment, & Store Pickup --}}
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @if($loc->is_online_fulfillment)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[6px] text-[11px] font-semibold bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20" title="{{ __('warehouse.fields.is_online_fulfillment') }}">
                                <i data-lucide="truck" class="w-3 h-3"></i>
                                <span>{{ __('warehouse.badges.online_fulfillment') }}</span>
                            </span>
                        @endif

                        @if($loc->allow_storefront_pickup)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[6px] text-[11px] font-semibold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20" title="{{ __('warehouse.fields.allow_storefront_pickup') }}">
                                <i data-lucide="shopping-bag" class="w-3 h-3"></i>
                                <span>{{ __('warehouse.badges.storefront_pickup') }}</span>
                            </span>
                        @endif

                        @if($loc->latitude && $loc->longitude)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[6px] text-[11px] font-semibold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20 font-mono tabular-nums">
                                <i data-lucide="crosshair" class="w-3 h-3"></i>
                                <span>GPS: {{ round((float)$loc->latitude, 4) }}, {{ round((float)$loc->longitude, 4) }} (R: {{ $loc->geofence_radius_meters ?? 100 }}m)</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[6px] text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                                <i data-lucide="map-pin-off" class="w-3 h-3 text-slate-400"></i>
                                <span>{{ __('warehouse.fields.gps_not_set') }}</span>
                            </span>
                        @endif

                        @if($loc->biteship_area_id)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[6px] text-[11px] font-semibold bg-[#5856D6]/10 text-[#5856D6] dark:text-[#5E5CE6] border border-[#5856D6]/20">
                                <i data-lucide="package-check" class="w-3 h-3"></i>
                                <span>{{ __('warehouse.fields.biteship_connected') }}</span>
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
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 block font-semibold uppercase">{{ __('warehouse.kpis.total_qty') }}</span>
                            <span class="text-xs sm:text-sm font-bold tabular-nums text-slate-900 dark:text-white">
                                {{ number_format($loc->total_stock_units ?? 0, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="rounded-[10px] bg-slate-50 dark:bg-[#2C2C2E] p-2">
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 block font-semibold uppercase">{{ __('warehouse.kpis.stock_asset_value') }}</span>
                            <span class="text-xs sm:text-sm font-bold tabular-nums text-[#34C759] dark:text-[#30D158] truncate block" title="Rp {{ number_format($loc->total_valuation ?? 0, 0, ',', '.') }}">
                                {{ ($loc->total_valuation ?? 0) >= 1000000 ? number_format(($loc->total_valuation ?? 0) / 1000000, 1) . 'jt' : number_format(($loc->total_valuation ?? 0) / 1000, 0) . 'rb' }}
                            </span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-4 flex items-center justify-between gap-2">
                        <a href="{{ route('warehouse.show', $loc) }}"
                           class="min-h-[44px] h-11 flex-1 rounded-[10px] text-xs font-bold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                            <span>{{ __('warehouse.actions.quick_adjust') }}</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>

                        @if(\App\Support\Context::hasPermission('inventory.manage') || \App\Support\Context::isAdminOrOwner() || \App\Support\Context::hasPermission('warehouse.manage'))
                        <div class="flex items-center gap-1.5 shrink-0">
                            <button type="button" @click="openEdit(@js($loc))"
                                    class="min-h-[44px] h-11 px-3.5 rounded-[10px] text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 active:scale-[0.98] transition-all flex items-center gap-1 cursor-pointer"
                                    title="{{ __('warehouse.actions.edit_location') }}">
                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                <span>{{ __('warehouse.actions.edit_location') }}</span>
                            </button>

                            <button type="button" @click="openDelete(@js($loc->id), @js($loc->name))"
                                    class="min-h-[44px] h-11 w-11 rounded-[10px] text-[#FF3B30] hover:bg-[#FF3B30]/10 active:scale-[0.98] transition-all flex items-center justify-center cursor-pointer"
                                    title="{{ __('warehouse.actions.delete_location') }}">
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
                <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ __('warehouse.stock_table.empty') }}</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                    {{ __('warehouse.header_subtitle') }}
                </p>
                @if(\App\Support\Context::hasPermission('inventory.manage') || \App\Support\Context::isAdminOrOwner() || \App\Support\Context::hasPermission('warehouse.manage'))
                <button type="button" @click="showCreateModal = true; gpsError = ''; gpsSuccess = false;"
                        class="mt-4 min-h-[44px] h-11 px-5 rounded-[10px] text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all inline-flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>{{ __('warehouse.actions.add_warehouse') }}</span>
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
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('warehouse.sop.title') }}</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('warehouse.receipts_table.subtitle') }}</p>
            </div>
            <span class="text-[11px] font-bold text-[#007AFF] bg-[#007AFF]/10 px-2.5 py-1 rounded-full">SOP</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            {{-- Step 1 --}}
            <div class="rounded-[12px] bg-slate-50 dark:bg-[#2C2C2E] p-3.5 border border-black/[0.04] dark:border-white/[0.04]">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-5 h-5 rounded-full bg-[#007AFF] text-white text-[11px] font-bold flex items-center justify-center tabular-nums">1</span>
                    <span class="text-xs font-bold text-slate-900 dark:text-white">{{ __('warehouse.sop.step_1') }}</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-snug">
                    {{ __('purchasing.receipts.notes_placeholder') }}
                </p>
            </div>

            {{-- Step 2 --}}
            <div class="rounded-[12px] bg-slate-50 dark:bg-[#2C2C2E] p-3.5 border border-black/[0.04] dark:border-white/[0.04]">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-5 h-5 rounded-full bg-[#007AFF] text-white text-[11px] font-bold flex items-center justify-center tabular-nums">2</span>
                    <span class="text-xs font-bold text-slate-900 dark:text-white">{{ __('warehouse.sop.step_2') }}</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-snug">
                    {{ __('purchasing.receipts.section_items_desc') }}
                </p>
            </div>

            {{-- Step 3 --}}
            <div class="rounded-[12px] bg-slate-50 dark:bg-[#2C2C2E] p-3.5 border border-black/[0.04] dark:border-white/[0.04]">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-5 h-5 rounded-full bg-[#007AFF] text-white text-[11px] font-bold flex items-center justify-center tabular-nums">3</span>
                    <span class="text-xs font-bold text-slate-900 dark:text-white">{{ __('warehouse.sop.step_3') }}</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-snug">
                    {{ __('purchasing.receipts.header_subtitle') }}
                </p>
            </div>

            {{-- Step 4 --}}
            <div class="rounded-[12px] bg-slate-50 dark:bg-[#2C2C2E] p-3.5 border border-black/[0.04] dark:border-white/[0.04]">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-5 h-5 rounded-full bg-[#34C759] text-white flex items-center justify-center shrink-0">
                        <i data-lucide="check" class="w-3 h-3"></i>
                    </span>
                    <span class="text-xs font-bold text-slate-900 dark:text-white">{{ __('warehouse.sop.step_4') }}</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-snug">
                    {{ __('inventory.stock_transfer_success') }}
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
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('warehouse.movements_table.title') }}</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('warehouse.movements_table.subtitle') }}</p>
            </div>
            <a href="{{ route('inventory.movements') }}"
               class="text-xs font-bold text-[#007AFF] hover:underline flex items-center gap-1">
                <span>{{ __('warehouse.tabs.movements') }}</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        {{-- Movements Table (Desktop) --}}
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-black/[0.06] dark:border-white/[0.08] text-[11px] font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500 bg-slate-50/50 dark:bg-[#2C2C2E]/50">
                        <th class="px-4 py-3">{{ __('warehouse.movements_table.col_item') }}</th>
                        <th class="px-4 py-3">{{ __('warehouse.fields.name') }}</th>
                        <th class="px-4 py-3">{{ __('warehouse.movements_table.col_type') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('warehouse.movements_table.col_delta') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('warehouse.movements_table.col_balance') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('warehouse.movements_table.col_datetime') }}</th>
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
                                    'goods_receipt'  => ['label' => __('inventory.movement_types.po_receipt'), 'pill' => 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]'],
                                    'pos_sale'       => ['label' => __('inventory.movement_types.pos_sale'), 'pill' => 'bg-[#007AFF]/12 text-[#007AFF]'],
                                    'adjustment'     => ['label' => __('inventory.movement_types.opname_variance'), 'pill' => 'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]'],
                                    'transfer_in'    => ['label' => __('inventory.movement_types.transfer_in'), 'pill' => 'bg-[#5856D6]/12 text-[#413FA6] dark:text-[#5E5CE6]'],
                                    'transfer_out'   => ['label' => __('inventory.movement_types.transfer_out'), 'pill' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'],
                                    'opname'         => ['label' => __('inventory.movement_types.opname_variance'), 'pill' => 'bg-[#AF52DE]/12 text-[#7C3AA6] dark:text-[#BF5AF2]'],
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
                        <td class="px-4 py-3.5 text-right text-[11px] text-slate-400 dark:text-slate-500 whitespace-nowrap font-medium tabular-nums">
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
                    'goods_receipt'  => ['label' => __('inventory.movement_types.po_receipt'), 'pill' => 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]'],
                    'pos_sale'       => ['label' => __('inventory.movement_types.pos_sale'), 'pill' => 'bg-[#007AFF]/12 text-[#007AFF]'],
                    'adjustment'     => ['label' => __('inventory.movement_types.opname_variance'), 'pill' => 'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]'],
                    'transfer_in'    => ['label' => __('inventory.movement_types.transfer_in'), 'pill' => 'bg-[#5856D6]/12 text-[#413FA6] dark:text-[#5E5CE6]'],
                    'transfer_out'   => ['label' => __('inventory.movement_types.transfer_out'), 'pill' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'],
                    'opname'         => ['label' => __('inventory.movement_types.opname_variance'), 'pill' => 'bg-[#AF52DE]/12 text-[#7C3AA6] dark:text-[#BF5AF2]'],
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
                    <span class="text-slate-500 dark:text-slate-400 text-[11px] tabular-nums">
                        {{ __('warehouse.movements_table.col_balance') }}: <strong class="text-slate-900 dark:text-white">{{ number_format($mv->balance_after, 2) }}</strong>
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ===================================================== --}}
    {{-- 7a. APPLE BENTO XXL SHEET: TAMBAH GUDANG LOGISTIK     --}}
    {{-- ===================================================== --}}
    <div x-show="showCreateModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-md p-3 sm:p-6"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="w-full max-w-[94vw] md:max-w-2xl lg:max-w-3xl xl:max-w-4xl max-h-[88vh] rounded-[22px] bg-white/98 dark:bg-[#1C1C1E]/98 backdrop-blur-2xl border border-black/[0.08] dark:border-white/[0.12] shadow-[0_25px_60px_rgba(0,0,0,0.35)] flex flex-col overflow-hidden"
             @click.outside="showCreateModal = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            {{-- Modal Header --}}
            <div class="px-5 py-4 sm:px-6 sm:py-4.5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-3 shrink-0 bg-slate-50/50 dark:bg-white/[0.02]">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-[14px] bg-[#007AFF]/12 text-[#007AFF] flex items-center justify-center shrink-0 border border-[#007AFF]/20">
                        <i data-lucide="warehouse" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                            <span>{{ __('warehouse.actions.create_warehouse_title') }}</span>
                            <span class="text-[11px] font-semibold text-[#007AFF] bg-[#007AFF]/12 px-2.5 py-0.5 rounded-full">{{ __('warehouse.types.warehouse') }}</span>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('warehouse.header_subtitle') }}</p>
                    </div>
                </div>
                <button type="button" @click="showCreateModal = false" class="min-w-[44px] min-h-[44px] w-11 h-11 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('warehouse.store') }}" method="POST" x-data="{ submitting: false }" @submit="submitting = true" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                <input type="hidden" name="type" value="warehouse">
                <input type="hidden" name="province" :value="warehouseForm.province">
                <input type="hidden" name="city" :value="warehouseForm.city">
                <input type="hidden" name="district" :value="warehouseForm.district">
                <input type="hidden" name="village" :value="warehouseForm.village">
                <input type="hidden" name="postal_code" :value="warehouseForm.postal_code">
                <input type="hidden" name="biteship_area_id" :value="warehouseForm.biteship_area_id">

                {{-- Modal Body: 2-Kolom Bento --}}
                <div class="p-5 sm:p-6 overflow-y-auto space-y-4 flex-1 text-xs">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                        
                        {{-- Kolom Kiri: Detail Informasi Gudang (6 Kolom) --}}
                        <div class="lg:col-span-6 space-y-4">
                            <div class="rounded-[18px] bg-slate-50/80 dark:bg-[#2C2C2E]/60 border border-black/[0.06] dark:border-white/[0.08] p-5 space-y-4">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="info" class="w-4 h-4 text-[#007AFF]"></i>
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ __('warehouse.sections.general_info') }}</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                        {{ __('warehouse.fields.name') }}
                                    </label>
                                    <input type="text" name="name" x-model="warehouseForm.name" required placeholder="{{ __('warehouse.placeholders.warehouse_name') }}"
                                           class="w-full h-11 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                </div>

                                {{-- Hierarki Induk Cabang / Outlet --}}
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                        {{ __('warehouse.fields.parent_location') }}
                                    </label>
                                    <select name="parent_id" x-model="warehouseForm.parent_id"
                                            class="w-full h-11 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                        <option value="">{{ __('warehouse.fields.no_parent') }}</option>
                                        @if(isset($parentOutlets))
                                            @foreach($parentOutlets as $pOut)
                                                <option value="{{ $pOut->id }}">{{ $pOut->name }} ({{ $pOut->code ?: __('warehouse.types.outlet') }})</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                                        {{ __('warehouse.sections.general_info_desc') }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            {{ __('warehouse.fields.code') }}
                                        </label>
                                        <input type="text" name="code" x-model="warehouseForm.code" placeholder="{{ __('warehouse.placeholders.code') }}"
                                               class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            {{ __('warehouse.fields.phone') }}
                                        </label>
                                        <input type="text" name="phone" x-model="warehouseForm.phone" placeholder="{{ __('warehouse.placeholders.phone') }}"
                                               class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        {{ __('warehouse.fields.address') }} <span class="text-[#FF3B30]">*</span>
                                    </label>
                                    <textarea name="address" x-model="warehouseForm.address" rows="3" required placeholder="{{ __('warehouse.placeholders.address') }}"
                                              class="w-full bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] p-3 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition resize-none"></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Kolom Kanan: Geofence GPS & Biteship Integration (6 Kolom) --}}
                        <div class="lg:col-span-6 space-y-4">
                            
                            {{-- SECTION GEOFENCE & DETEKSI GPS --}}
                            <div class="rounded-[18px] bg-[#007AFF]/5 dark:bg-[#007AFF]/10 border border-[#007AFF]/20 p-5 space-y-3.5">
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-[6px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                                            <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                                        </div>
                                        <span class="text-xs font-bold text-slate-900 dark:text-white">{{ __('warehouse.sections.geofence_gps') }}</span>
                                    </div>

                                    {{-- Tombol Live Detect GPS --}}
                                    <button type="button" @click="detectGps('warehouse')" :disabled="gpsLoading"
                                            class="min-h-[44px] h-11 px-3.5 rounded-[8px] text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] cursor-pointer disabled:opacity-50">
                                        <template x-if="gpsLoading && activeGpsTarget === 'warehouse'">
                                            <svg class="animate-spin -ml-0.5 mr-1 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </template>
                                        <template x-if="!(gpsLoading && activeGpsTarget === 'warehouse')">
                                            <i data-lucide="crosshair" class="w-3.5 h-3.5"></i>
                                        </template>
                                        <span x-text="(gpsLoading && activeGpsTarget === 'warehouse') ? '{{ __('warehouse.actions.detecting_gps') }}' : '{{ __('warehouse.actions.detect_gps') }}'"></span>
                                    </button>
                                </div>

                                {{-- Alert GPS Messages --}}
                                <template x-if="gpsSuccess && activeGpsTarget === 'warehouse'">
                                    <div class="rounded-[10px] bg-[#34C759]/15 border border-[#34C759]/30 px-3 py-2 text-[11px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-2 font-medium">
                                        <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759]"></i>
                                        <span>{{ __('warehouse.actions.gps_detected') }}</span>
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
                                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('warehouse.fields.latitude') }}</label>
                                        <input type="text" name="latitude" x-model="warehouseForm.latitude" placeholder="{{ __('warehouse.placeholders.latitude') }}"
                                               class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] px-2.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-[#007AFF] transition">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('warehouse.fields.longitude') }}</label>
                                        <input type="text" name="longitude" x-model="warehouseForm.longitude" placeholder="{{ __('warehouse.placeholders.longitude') }}"
                                               class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] px-2.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-[#007AFF] transition">
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300">{{ __('warehouse.fields.geofence_radius') }}</label>
                                        <span class="text-[11px] font-bold text-[#007AFF] font-mono" x-text="warehouseForm.geofence_radius_meters + ' m'"></span>
                                    </div>
                                    <input type="number" name="geofence_radius_meters" x-model="warehouseForm.geofence_radius_meters" placeholder="{{ __('warehouse.placeholders.radius') }}" min="10" max="5000"
                                           class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] px-2.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-[#007AFF] transition">
                                    
                                    {{-- Quick Presets --}}
                                    <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                                        <button type="button" @click="warehouseForm.geofence_radius_meters = 50" class="min-h-[44px] sm:min-h-0 px-2.5 py-1 rounded-[6px] text-[10px] font-semibold bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] text-slate-700 dark:text-slate-300 cursor-pointer">50m</button>
                                        <button type="button" @click="warehouseForm.geofence_radius_meters = 100" class="min-h-[44px] sm:min-h-0 px-2.5 py-1 rounded-[6px] text-[10px] font-semibold bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] text-slate-700 dark:text-slate-300 cursor-pointer">100m (Default)</button>
                                        <button type="button" @click="warehouseForm.geofence_radius_meters = 200" class="min-h-[44px] sm:min-h-0 px-2.5 py-1 rounded-[6px] text-[10px] font-semibold bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] text-slate-700 dark:text-slate-300 cursor-pointer">200m</button>
                                        <button type="button" @click="warehouseForm.geofence_radius_meters = 500" class="min-h-[44px] sm:min-h-0 px-2.5 py-1 rounded-[6px] text-[10px] font-semibold bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] text-slate-700 dark:text-slate-300 cursor-pointer">500m</button>
                                    </div>
                                </div>
                            </div>

                            {{-- SECTION INTEGRASI BITESHIP & TOKO ONLINE PICKUP --}}
                            <div class="rounded-[18px] bg-[#5856D6]/5 dark:bg-[#5856D6]/10 border border-[#5856D6]/20 p-5 space-y-3.5">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-[6px] bg-[#5856D6]/15 text-[#5856D6] flex items-center justify-center">
                                            <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                                        </div>
                                        <span class="text-xs font-bold text-slate-900 dark:text-white">{{ __('warehouse.fields.biteship_area') }}</span>
                                    </div>
                                </div>

                                {{-- Area Search Box for Biteship --}}
                                <div class="relative">
                                    <template x-if="!warehouseForm.biteship_area_id">
                                        <div class="relative">
                                            <input type="text"
                                                   placeholder="{{ __('warehouse.placeholders.biteship_search') }}"
                                                   @input.debounce.300ms="searchBiteship('warehouse', $event.target.value)"
                                                   class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] pl-8 pr-3 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-[#5856D6] transition">
                                            <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-3"></i>
                                        </div>
                                    </template>

                                    {{-- Selected Biteship Area Card --}}
                                    <template x-if="warehouseForm.biteship_area_id">
                                        <div class="flex items-center justify-between p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-[#5856D6]/30">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <i data-lucide="check-circle" class="w-4 h-4 text-[#34C759] shrink-0"></i>
                                                <div class="min-w-0">
                                                    <span class="text-xs font-bold text-slate-900 dark:text-white block truncate" x-text="warehouseForm.biteship_area_label || warehouseForm.biteship_area_id"></span>
                                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">ID: <span x-text="warehouseForm.biteship_area_id"></span></span>
                                                </div>
                                            </div>
                                            <button type="button" @click="clearBiteshipArea('warehouse')" class="min-h-[44px] sm:min-h-0 text-xs text-[#FF3B30] hover:underline font-semibold shrink-0 ml-2 cursor-pointer">
                                                {{ __('warehouse.actions.change') }}
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
                                                <span class="text-[10px] font-mono text-[#5856D6] font-bold shrink-0 bg-[#5856D6]/10 px-1.5 py-0.5 rounded">{{ __('warehouse.actions.select') }}</span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                {{-- Checkbox Primary Pickup Storefront --}}
                                <div class="space-y-2 pt-1">
                                    <label class="flex items-start gap-2.5 p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] cursor-pointer hover:border-[#5856D6]/40 transition">
                                        <input type="checkbox" name="is_primary" value="1" x-model="warehouseForm.is_primary" class="mt-0.5 w-4 h-4 rounded text-[#5856D6] focus:ring-[#5856D6]">
                                        <div class="text-xs">
                                            <span class="font-bold text-slate-900 dark:text-white block">{{ __('warehouse.fields.is_primary') }}</span>
                                            <span class="text-[11px] text-slate-500 dark:text-slate-400 block">{{ __('warehouse.sections.operational_settings_desc') }}</span>
                                        </div>
                                    </label>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <label class="flex items-start gap-2 p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] cursor-pointer">
                                            <input type="checkbox" name="is_online_fulfillment" value="1" x-model="warehouseForm.is_online_fulfillment" class="mt-0.5 w-3.5 h-3.5 rounded text-[#007AFF] focus:ring-[#007AFF]">
                                            <span class="text-[11px] font-medium text-slate-800 dark:text-slate-200">{{ __('warehouse.fields.is_online_fulfillment') }}</span>
                                        </label>
                                        <label class="flex items-start gap-2 p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] cursor-pointer">
                                            <input type="checkbox" name="allow_storefront_pickup" value="1" x-model="warehouseForm.allow_storefront_pickup" class="mt-0.5 w-3.5 h-3.5 rounded text-[#007AFF] focus:ring-[#007AFF]">
                                            <span class="text-[11px] font-medium text-slate-800 dark:text-slate-200">{{ __('warehouse.fields.allow_storefront_pickup') }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="px-5 py-3.5 sm:px-6 sm:py-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-end gap-3 shrink-0 bg-slate-50/50 dark:bg-white/[0.02]">
                    <button type="button" @click="showCreateModal = false"
                            class="min-h-[44px] h-11 px-5 rounded-[12px] text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                        {{ __('warehouse.actions.cancel') }}
                    </button>
                    <button type="submit" :disabled="submitting"
                            class="min-h-[44px] h-11 px-6 rounded-[12px] text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all shadow-[0_2px_4px_rgba(0,122,255,0.25)] cursor-pointer flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <template x-if="submitting">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <template x-if="!submitting">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                        </template>
                        <span x-text="submitting ? '{{ __('warehouse.actions.submitting') }}' : '{{ __('warehouse.actions.save') }}'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- 7b. APPLE BENTO XXL SHEET: TAMBAH CABANG / OUTLET     --}}
    {{-- ===================================================== --}}


    {{-- ===================================================== --}}
    {{-- 8. APPLE BENTO XXL SHEET: EDIT INFORMASI LOKASI       --}}
    {{-- ===================================================== --}}
    <div x-show="showEditModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-md p-3 sm:p-6"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="w-full max-w-[94vw] md:max-w-2xl lg:max-w-3xl xl:max-w-4xl max-h-[88vh] rounded-[22px] bg-white/98 dark:bg-[#1C1C1E]/98 backdrop-blur-2xl border border-black/[0.08] dark:border-white/[0.12] shadow-[0_25px_60px_rgba(0,0,0,0.35)] flex flex-col overflow-hidden"
             @click.outside="showEditModal = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            {{-- Modal Header --}}
            <div class="px-5 py-4 sm:px-6 sm:py-4.5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-3 shrink-0 bg-slate-50/50 dark:bg-white/[0.02]">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-[14px] bg-[#007AFF]/12 text-[#007AFF] flex items-center justify-center shrink-0 border border-[#007AFF]/20">
                        <i data-lucide="pencil-line" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                            <span>{{ __('warehouse.actions.edit_location') }}</span>
                            <span class="text-[11px] font-semibold text-[#007AFF] bg-[#007AFF]/12 px-2.5 py-0.5 rounded-full" x-text="editData.name"></span>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('warehouse.header_subtitle') }}</p>
                    </div>
                </div>
                <button type="button" @click="showEditModal = false" class="min-w-[44px] min-h-[44px] w-11 h-11 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form :action="'{{ url('/warehouse') }}/' + editData.id" method="POST" x-data="{ submitting: false }" @submit="submitting = true" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                @method('PUT')
                <input type="hidden" name="province" :value="editData.province">
                <input type="hidden" name="city" :value="editData.city">
                <input type="hidden" name="district" :value="editData.district">
                <input type="hidden" name="village" :value="editData.village">
                <input type="hidden" name="postal_code" :value="editData.postal_code">
                <input type="hidden" name="biteship_area_id" :value="editData.biteship_area_id">
                <input type="hidden" name="operating_hours_json" :value="JSON.stringify(editData.operating_hours)">

                {{-- Modal Body: 2-Kolom Bento --}}
                <div class="p-5 sm:p-6 overflow-y-auto space-y-4 flex-1 text-xs">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                        
                        {{-- Kolom Kiri: Detail Informasi (6 Kolom) --}}
                        <div class="lg:col-span-6 space-y-4">
                            <div class="rounded-[18px] bg-slate-50/80 dark:bg-[#2C2C2E]/60 border border-black/[0.06] dark:border-white/[0.08] p-5 space-y-4">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="info" class="w-4 h-4 text-[#007AFF]"></i>
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ __('warehouse.sections.general_info') }}</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                        {{ __('warehouse.fields.name') }}
                                    </label>
                                    <input type="text" name="name" x-model="editData.name" required
                                           class="w-full h-11 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            {{ __('warehouse.fields.type') }}
                                        </label>
                                        <select name="type" x-model="editData.type" class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                            <option value="warehouse">{{ __('warehouse.types.warehouse') }}</option>
                                            <option value="outlet">{{ __('warehouse.types.outlet') }}</option>
                                            @if(str_starts_with($business->template_code ?? '', 'fnb_'))
                                                <option value="central_kitchen">{{ __('warehouse.types.central_kitchen') }}</option>
                                            @elseif(str_starts_with($business->template_code ?? '', 'mfg_'))
                                                <option value="central_kitchen">{{ __('warehouse.types.central_kitchen_mfg') }}</option>
                                            @elseif(($business->template_code ?? '') === 'service_contractor')
                                                <option value="central_kitchen">{{ __('warehouse.types.central_kitchen_contractor') }}</option>
                                            @elseif(($locations ?? collect())->contains('type', 'central_kitchen'))
                                                <option value="central_kitchen">{{ __('warehouse.types.central_kitchen') }}</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            {{ __('warehouse.fields.code') }}
                                        </label>
                                        <input type="text" name="code" x-model="editData.code"
                                               class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                    </div>
                                </div>

                                {{-- Hierarki Induk Cabang / Outlet --}}
                                <div x-show="editData.type === 'warehouse'">
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                        {{ __('warehouse.fields.parent_location') }}
                                    </label>
                                    <select name="parent_id" x-model="editData.parent_id"
                                            class="w-full h-11 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                        <option value="">{{ __('warehouse.fields.no_parent') }}</option>
                                        @if(isset($parentOutlets))
                                            @foreach($parentOutlets as $pOut)
                                                <option value="{{ $pOut->id }}" x-show="editData.id !== '{{ $pOut->id }}'">{{ $pOut->name }} ({{ $pOut->code ?: __('warehouse.types.outlet') }})</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                                        {{ __('warehouse.sections.general_info_desc') }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        {{ __('warehouse.fields.phone') }}
                                    </label>
                                    <input type="text" name="phone" x-model="editData.phone"
                                           class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        {{ __('warehouse.fields.address') }}
                                    </label>
                                    <textarea name="address" rows="3" x-model="editData.address"
                                              class="w-full bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] p-3 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition resize-none"></textarea>
                                </div>

                                <div class="flex items-center gap-3 p-3.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                                    <input type="checkbox" name="is_active" id="edit_is_active" value="1"
                                           x-model="editData.is_active" class="w-4 h-4 rounded text-[#007AFF] focus:ring-[#007AFF]">
                                    <label for="edit_is_active" class="text-xs text-slate-800 dark:text-slate-200 font-semibold cursor-pointer">
                                        {{ __('warehouse.fields.is_active') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Kolom Kanan: Geofence & Biteship Logistics (6 Kolom) --}}
                        <div class="lg:col-span-6 space-y-4">
                            
                            {{-- SECTION GEOFENCE & DETEKSI GPS --}}
                            <div class="rounded-[18px] bg-[#007AFF]/5 dark:bg-[#007AFF]/10 border border-[#007AFF]/20 p-5 space-y-3.5">
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-[6px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                                            <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                                        </div>
                                        <span class="text-xs font-bold text-[#007AFF]">{{ __('warehouse.sections.geofence_gps') }}</span>
                                    </div>

                                    {{-- Tombol Live Detect GPS --}}
                                    <button type="button" @click="detectGps('edit')" :disabled="gpsLoading"
                                            class="min-h-[44px] h-11 px-3.5 rounded-[8px] text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] cursor-pointer disabled:opacity-50">
                                        <template x-if="gpsLoading && activeGpsTarget === 'edit'">
                                            <svg class="animate-spin -ml-0.5 mr-1 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </template>
                                        <template x-if="!(gpsLoading && activeGpsTarget === 'edit')">
                                            <i data-lucide="crosshair" class="w-3.5 h-3.5"></i>
                                        </template>
                                        <span x-text="(gpsLoading && activeGpsTarget === 'edit') ? '{{ __('warehouse.actions.detecting_gps') }}' : '{{ __('warehouse.actions.detect_gps') }}'"></span>
                                    </button>
                                </div>

                                {{-- Alert GPS Messages --}}
                                <template x-if="gpsSuccess && activeGpsTarget === 'edit'">
                                    <div class="rounded-[10px] bg-[#34C759]/15 border border-[#34C759]/30 px-3 py-2 text-[11px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-2 font-medium">
                                        <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759]"></i>
                                        <span>{{ __('warehouse.actions.gps_detected') }}</span>
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
                                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('warehouse.fields.latitude') }}</label>
                                        <input type="text" name="latitude" x-model="editData.latitude" placeholder="{{ __('warehouse.placeholders.latitude') }}"
                                               class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] px-2.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-[#007AFF] transition">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('warehouse.fields.longitude') }}</label>
                                        <input type="text" name="longitude" x-model="editData.longitude" placeholder="{{ __('warehouse.placeholders.longitude') }}"
                                               class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] px-2.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-[#007AFF] transition">
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300">{{ __('warehouse.fields.geofence_radius') }}</label>
                                        <span class="text-[11px] font-bold text-[#007AFF] font-mono" x-text="editData.geofence_radius_meters + ' m'"></span>
                                    </div>
                                    <input type="number" name="geofence_radius_meters" x-model="editData.geofence_radius_meters" placeholder="{{ __('warehouse.placeholders.radius') }}" min="10" max="5000"
                                           class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] px-2.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-[#007AFF] transition">
                                    
                                    {{-- Quick Presets --}}
                                    <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                                        <button type="button" @click="editData.geofence_radius_meters = 50" class="min-h-[44px] sm:min-h-0 px-2.5 py-1 rounded-[6px] text-[10px] font-semibold bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] text-slate-700 dark:text-slate-300 cursor-pointer">50m</button>
                                        <button type="button" @click="editData.geofence_radius_meters = 100" class="min-h-[44px] sm:min-h-0 px-2.5 py-1 rounded-[6px] text-[10px] font-semibold bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] text-slate-700 dark:text-slate-300 cursor-pointer">100m (Default)</button>
                                        <button type="button" @click="editData.geofence_radius_meters = 200" class="min-h-[44px] sm:min-h-0 px-2.5 py-1 rounded-[6px] text-[10px] font-semibold bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] text-slate-700 dark:text-slate-300 cursor-pointer">200m</button>
                                        <button type="button" @click="editData.geofence_radius_meters = 500" class="min-h-[44px] sm:min-h-0 px-2.5 py-1 rounded-[6px] text-[10px] font-semibold bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] text-slate-700 dark:text-slate-300 cursor-pointer">500m</button>
                                    </div>
                                </div>
                            </div>

                            {{-- SECTION INTEGRASI BITESHIP & TOKO ONLINE PICKUP --}}
                            <div class="rounded-[18px] bg-[#5856D6]/5 dark:bg-[#5856D6]/10 border border-[#5856D6]/20 p-5 space-y-3.5">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-[6px] bg-[#5856D6]/15 text-[#5856D6] flex items-center justify-center">
                                            <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                                        </div>
                                        <span class="text-xs font-bold text-slate-900 dark:text-white">{{ __('warehouse.fields.biteship_area') }}</span>
                                    </div>
                                </div>

                                {{-- Area Search Box for Biteship --}}
                                <div class="relative">
                                    <template x-if="!editData.biteship_area_id">
                                        <div class="relative">
                                            <input type="text"
                                                   placeholder="{{ __('warehouse.placeholders.biteship_search') }}"
                                                   @input.debounce.300ms="searchBiteship('edit', $event.target.value)"
                                                   class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] pl-8 pr-3 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-[#5856D6] transition">
                                            <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-3"></i>
                                        </div>
                                    </template>

                                    {{-- Selected Biteship Area Card --}}
                                    <template x-if="editData.biteship_area_id">
                                        <div class="flex items-center justify-between p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-[#5856D6]/30">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <i data-lucide="check-circle" class="w-4 h-4 text-[#34C759] shrink-0"></i>
                                                <div class="min-w-0">
                                                    <span class="text-xs font-bold text-slate-900 dark:text-white block truncate" x-text="editData.biteship_area_label || editData.biteship_area_id"></span>
                                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">ID: <span x-text="editData.biteship_area_id"></span></span>
                                                </div>
                                            </div>
                                            <button type="button" @click="clearBiteshipArea('edit')" class="min-h-[44px] sm:min-h-0 text-xs text-[#FF3B30] hover:underline font-semibold shrink-0 ml-2 cursor-pointer">
                                                {{ __('warehouse.actions.change') }}
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
                                                <span class="text-[10px] font-mono text-[#5856D6] font-bold shrink-0 bg-[#5856D6]/10 px-1.5 py-0.5 rounded">{{ __('warehouse.actions.select') }}</span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                {{-- Checkbox Primary Pickup Storefront --}}
                                <div class="space-y-2 pt-1">
                                    <label class="flex items-start gap-2.5 p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] cursor-pointer hover:border-[#5856D6]/40 transition">
                                        <input type="checkbox" name="is_primary" value="1" x-model="editData.is_primary" class="mt-0.5 w-4 h-4 rounded text-[#5856D6] focus:ring-[#5856D6]">
                                        <div class="text-xs">
                                            <span class="font-bold text-slate-900 dark:text-white block">{{ __('warehouse.fields.is_primary') }}</span>
                                            <span class="text-[11px] text-slate-500 dark:text-slate-400 block">{{ __('warehouse.sections.operational_settings_desc') }}</span>
                                        </div>
                                    </label>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <label class="flex items-start gap-2 p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] cursor-pointer">
                                            <input type="checkbox" name="is_online_fulfillment" value="1" x-model="editData.is_online_fulfillment" class="mt-0.5 w-3.5 h-3.5 rounded text-[#007AFF] focus:ring-[#007AFF]">
                                            <span class="text-[11px] font-medium text-slate-800 dark:text-slate-200">{{ __('warehouse.fields.is_online_fulfillment') }}</span>
                                        </label>
                                        <label class="flex items-start gap-2 p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] cursor-pointer">
                                            <input type="checkbox" name="allow_storefront_pickup" value="1" x-model="editData.allow_storefront_pickup" class="mt-0.5 w-3.5 h-3.5 rounded text-[#007AFF] focus:ring-[#007AFF]">
                                            <span class="text-[11px] font-medium text-slate-800 dark:text-slate-200">{{ __('warehouse.fields.allow_storefront_pickup') }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Kolom Penuh: Zona Waktu & Jam Operasional Cabang --}}
                        <div class="lg:col-span-12 rounded-[18px] bg-slate-50/80 dark:bg-[#2C2C2E]/60 border border-black/[0.06] dark:border-white/[0.08] p-5 space-y-4">
                            <div class="flex items-center justify-between gap-3 flex-wrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-[6px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                        {{ __('warehouse.sections.timezone_and_hours') }}
                                    </span>
                                </div>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">
                                    {{ __('warehouse.sections.timezone_and_hours_desc') }}
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Zona Waktu --}}
                                <div class="p-4 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                                        {{ __('warehouse.fields.timezone_mode') }}
                                    </label>
                                    <select name="timezone_mode" x-model="editData.timezone_mode"
                                            class="w-full h-10 bg-slate-50 dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                        <option value="inherit">{{ __('warehouse.timezone_modes.inherit') }} ({{ $business->timezone ?? 'Asia/Jakarta' }})</option>
                                        <option value="custom">{{ __('warehouse.timezone_modes.custom') }}</option>
                                    </select>

                                    <div x-show="editData.timezone_mode === 'custom'" x-cloak class="space-y-1.5 pt-1">
                                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400">
                                            {{ __('warehouse.fields.timezone') }}
                                        </label>
                                        <select name="timezone" x-model="editData.timezone"
                                                class="w-full h-10 bg-slate-50 dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                            @foreach(\App\Support\TimezoneHelper::commonIndonesianTimezones() as $tz)
                                                <option value="{{ $tz['value'] }}">{{ $tz['label'] }} ({{ $tz['region'] }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Mode Jam Operasional --}}
                                <div class="p-4 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                                        {{ __('warehouse.fields.operating_hours_mode') }}
                                    </label>
                                    <select name="operating_hours_mode" x-model="editData.operating_hours_mode"
                                            class="w-full h-10 bg-slate-50 dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                        <option value="inherit">{{ __('warehouse.operating_hours_modes.inherit') }}</option>
                                        <option value="custom">{{ __('warehouse.operating_hours_modes.custom') }}</option>
                                    </select>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                        {{ __('warehouse.operating_hours_modes.custom_hint') }}
                                    </p>
                                </div>
                            </div>

                            {{-- Grid Jam Operasional Khusus --}}
                            <div x-show="editData.operating_hours_mode === 'custom'" x-cloak class="space-y-3 pt-2">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-[340px] overflow-y-auto pr-1">
                                    <template x-for="(config, dayKey) in (editData.operating_hours || defaultOperatingHours)" :key="dayKey">
                                        <div class="p-3.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] space-y-2.5">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-bold text-slate-900 dark:text-white capitalize" x-text="config.day_name || dayKey"></span>
                                                <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                                    <input type="checkbox" x-model="config.is_open"
                                                           class="w-3.5 h-3.5 rounded text-[#007AFF] focus:ring-[#007AFF]">
                                                    <span class="text-[11px] font-semibold" :class="config.is_open ? 'text-[#34C759]' : 'text-slate-400'"
                                                          x-text="config.is_open ? '{{ __('warehouse.hours.open') }}' : '{{ __('warehouse.hours.closed') }}'"></span>
                                                </label>
                                            </div>

                                            <div x-show="config.is_open" class="space-y-2">
                                                <template x-for="(period, pIdx) in (config.periods || [])" :key="pIdx">
                                                    <div class="flex items-center gap-2">
                                                        <input type="time" x-model="period.start"
                                                               class="h-8 px-2 rounded-[8px] bg-slate-50 dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] text-xs font-mono text-slate-900 dark:text-white">
                                                        <span class="text-slate-400 text-xs">-</span>
                                                        <input type="time" x-model="period.end"
                                                               class="h-8 px-2 rounded-[8px] bg-slate-50 dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] text-xs font-mono text-slate-900 dark:text-white">
                                                        <span x-show="isOvernight(period)" class="text-[9px] font-semibold text-[#FF9500] px-1.5 py-0.5 rounded bg-[#FF9500]/10 shrink-0">
                                                            {{ __('warehouse.hours.overnight') }}
                                                        </span>
                                                        <button type="button" @click="removeEditPeriod(dayKey, pIdx)"
                                                                x-show="(config.periods || []).length > 1"
                                                                class="p-1 rounded text-red-500 hover:bg-red-500/10 cursor-pointer">
                                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                                        </button>
                                                    </div>
                                                </template>
                                                <button type="button" @click="addEditPeriod(dayKey)"
                                                        class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 cursor-pointer pt-0.5">
                                                    <i data-lucide="plus" class="w-3 h-3"></i>
                                                    <span>{{ __('warehouse.hours.add_period') }}</span>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="px-5 py-3.5 sm:px-6 sm:py-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-end gap-3 shrink-0 bg-slate-50/50 dark:bg-white/[0.02]">
                    <button type="button" @click="showEditModal = false"
                            class="min-h-[44px] h-11 px-5 rounded-[12px] text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                        {{ __('warehouse.actions.cancel') }}
                    </button>
                    <button type="submit" :disabled="submitting"
                            class="min-h-[44px] h-11 px-6 rounded-[12px] text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all shadow-[0_2px_4px_rgba(0,122,255,0.25)] cursor-pointer flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <template x-if="submitting">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <template x-if="!submitting">
                            <i data-lucide="check" class="w-4 h-4"></i>
                        </template>
                        <span x-text="submitting ? '{{ __('warehouse.actions.submitting') }}' : '{{ __('warehouse.actions.save_changes') }}'"></span>
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

        <div class="w-full max-w-[340px] rounded-[20px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-2xl border border-black/[0.08] dark:border-white/[0.12]"
             @click.away="closeDelete()"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div class="px-6 pt-6 pb-5">
                <div class="w-12 h-12 rounded-full bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center mx-auto mb-3.5">
                    <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                </div>
                <p class="text-base font-bold text-slate-900 dark:text-white">{{ __('warehouse.delete_modal.title') }}</p>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-1.5 leading-relaxed">
                    <span x-text="deleteTarget.name" class="font-bold text-slate-900 dark:text-white"></span>. {{ __('warehouse.delete_modal.has_stock_warning') }}
                </p>
            </div>

            <div class="grid grid-cols-2 border-t border-black/[0.08] dark:border-white/[0.12] text-xs font-semibold">
                <button type="button" @click="closeDelete()"
                        class="min-h-[44px] py-3.5 text-slate-600 dark:text-slate-300 border-r border-black/[0.08] dark:border-white/[0.12] active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer flex items-center justify-center">
                    {{ __('warehouse.actions.cancel') }}
                </button>
                <button type="button" @click="submitDelete()"
                        class="min-h-[44px] py-3.5 text-[#FF3B30] font-bold active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer flex items-center justify-center">
                    {{ __('warehouse.actions.delete') }}
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
