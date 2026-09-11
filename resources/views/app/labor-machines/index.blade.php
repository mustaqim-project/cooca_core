@extends('layouts.app', [
    'title' => 'Tenaga Kerja & Mesin',
    'headerTitle' => 'Biaya Tenaga Kerja & Mesin',
    'headerSubtitle' => 'Konversi tarif upah ke tarif per jam efektif dan kalkulasi biaya mesin per jam operasi'
])

@section('content')
@php
    $laborService = new \App\Domain\Labor\LaborCostService();
    $totalLaborCount = $laborRates->count();
    $totalMachineCount = $machines->count();

    $avgLaborHourly = $totalLaborCount > 0
        ? $laborRates->map(fn($lr) => $laborService->hourlyRateEquivalent($lr))->avg()
        : 0;

    $avgMachineHourly = $totalMachineCount > 0
        ? $machines->map(function($m) {
            $usefulHours = max(1, (float) ($m->useful_life_hours ?: 1));
            $salvage = (float) ($m->residual_value ?? $m->salvage_value ?? 0);
            $deprec = ((float) $m->purchase_price - $salvage) / $usefulHours;
            $elecCost = (float) ($m->electricity_cost_per_hour ?? 0);
            $maintCost = (float) ($m->maintenance_cost_per_hour ?? 0);
            return $deprec + $elecCost + $maintCost;
        })->avg()
        : 0;
@endphp

<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
    activeSection: 'all',
    searchQuery: '',
    showLaborModal: false,
    showEditLaborModal: false,
    showMachineModal: false,
    showEditMachineModal: false,
    deleteModalOpen: false,
    deleteTarget: { url: '', name: '' },
    editLabor: {
        id: '',
        name: '',
        basis: 'hourly',
        rate_amount: '',
        working_days_per_month: 22,
        working_hours_per_day: 8,
        utilization_percentage: 80,
        is_subcontractor: false
    },
    editMachine: {
        id: '',
        name: '',
        purchase_price: '',
        salvage_value: '',
        useful_life_hours: '',
        power_kw: 1.5,
        electricity_cost_per_kwh: 1500,
        maintenance_cost_per_hour: 0
    },
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
            const form = document.getElementById('form-delete-labor-machine');
            form.action = this.deleteTarget.url;
            form.submit();
        }
    },
    openEditLabor(item) {
        this.editLabor = {
            id: item.id || '',
            name: item.name || '',
            basis: item.basis || 'hourly',
            rate_amount: item.rate_amount || '',
            working_days_per_month: item.working_days_per_month || 22,
            working_hours_per_day: item.working_hours_per_day || 8,
            utilization_percentage: item.utilization_rate || 80,
            is_subcontractor: !!item.is_subcontractor
        };
        this.showEditLaborModal = true;
    },
    openEditMachine(item) {
        this.editMachine = {
            id: item.id || '',
            name: item.name || '',
            purchase_price: item.purchase_price || '',
            salvage_value: item.residual_value || 0,
            useful_life_hours: item.useful_life_hours || '',
            power_kw: item.power_kw !== undefined ? item.power_kw : 0,
            electricity_cost_per_kwh: item.electricity_cost_per_kwh || 1500,
            maintenance_cost_per_hour: item.maintenance_cost_per_hour || 0
        };
        this.showEditMachineModal = true;
    },
    matchesSearch(term1, term2) {
        if (!this.searchQuery) return true;
        const q = this.searchQuery.toLowerCase();
        return (term1 && term1.toLowerCase().includes(q)) ||
               (term2 && term2.toLowerCase().includes(q));
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
                <span class="text-black/70 dark:text-white/70 font-medium">Biaya Produksi</span>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Tenaga Kerja &amp; Mesin</span>
            </nav>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091.704.297 1.385.607 2.008" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Tenaga Kerja &amp; Mesin</h1>
                    <p class="text-[13px] text-black/50 dark:text-white/50">Konversi tarif upah ke tarif per jam efektif dan kalkulasi biaya mesin per jam operasi</p>
                </div>
            </div>
        </div>

        @if(\App\Support\Context::hasPermission('costing.manage'))
            <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
                <button type="button" @click="showLaborModal = true"
                        class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Tambah Tarif Upah</span>
                </button>
                <button type="button" @click="showMachineModal = true"
                        class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Tambah Mesin Baru</span>
                </button>
            </div>
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
        <!-- Tile 1: Total Tenaga Kerja -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Tenaga Kerja Terdaftar</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-black dark:text-white">{{ $totalLaborCount }}</span>
                <span class="text-[11px] text-black/40 dark:text-white/40">Posisi</span>
            </div>
        </div>

        <!-- Tile 2: Rata-rata Upah Efektif per Jam (System Green) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Rata-rata Upah / Jam</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] sm:text-[24px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">
                    {{ $business->currency_symbol }} {{ number_format((float)$avgLaborHourly, 0, ',', '.') }}
                </span>
                <span class="text-[11px] font-medium text-[#34C759] dark:text-[#30D158]">Efektif</span>
            </div>
        </div>

        <!-- Tile 3: Total Mesin & Alat Produksi (System Blue) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Mesin &amp; Peralatan</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF]">{{ $totalMachineCount }}</span>
                <span class="text-[11px] font-medium text-[#007AFF]">Unit Aktif</span>
            </div>
        </div>

        <!-- Tile 4: Rata-rata Biaya Mesin per Jam (System Orange) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Rata-rata Biaya Mesin / Jam</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] sm:text-[24px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">
                    {{ $business->currency_symbol }} {{ number_format((float)$avgMachineHourly, 0, ',', '.') }}
                </span>
                <span class="text-[11px] font-medium text-[#FF9500] dark:text-[#FF9F0A]">Operasi</span>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. CONTROLS: SEGMENTED CONTROL & macOS SEARCH          -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 sm:p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <!-- Search Field (macOS Style: Clean Fill, No Thick Border) -->
        <div class="relative flex-1 max-w-md">
            <svg class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            <input type="text"
                x-model="searchQuery"
                placeholder="Cari posisi tenaga kerja atau nama mesin..."
                class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-9 pr-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
        </div>

        <!-- Apple-Style Segmented Controls -->
        <div class="inline-flex p-0.5 rounded-[9px] bg-black/[0.06] dark:bg-white/[0.08] text-[13px] font-medium self-start sm:self-auto">
            <button type="button" @click="activeSection = 'all'"
                    :class="activeSection === 'all' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55'"
                    class="px-3.5 py-1 rounded-[7px] transition-all">
                Semua
            </button>
            <button type="button" @click="activeSection = 'labor'"
                    :class="activeSection === 'labor' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55'"
                    class="px-3.5 py-1 rounded-[7px] transition-all flex items-center gap-1.5">
                <span>Tenaga Kerja</span>
                <span class="text-[11px] font-semibold opacity-70 tabular-nums">({{ $totalLaborCount }})</span>
            </button>
            <button type="button" @click="activeSection = 'machines'"
                    :class="activeSection === 'machines' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55'"
                    class="px-3.5 py-1 rounded-[7px] transition-all flex items-center gap-1.5">
                <span>Mesin</span>
                <span class="text-[11px] font-semibold opacity-70 tabular-nums">({{ $totalMachineCount }})</span>
            </button>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 4. SECTION: TARIF TENAGA KERJA (DIRECT LABOR)         -->
    <!-- ===================================================== -->
    <div x-show="activeSection === 'all' || activeSection === 'labor'" class="space-y-3.5">
        <div class="flex items-center justify-between px-1">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-[8px] bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-[16px] font-semibold text-black dark:text-white tracking-tight">Tarif Tenaga Kerja Langsung (Direct Labor)</h2>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Dikonversi otomatis ke jam produktif (Productive Hours Utilization %)</p>
                </div>
            </div>

            @if(\App\Support\Context::hasPermission('costing.manage'))
                <button type="button" @click="showLaborModal = true"
                        class="h-8 px-3 rounded-[8px] text-[12px] font-medium text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Tambah Posisi</span>
                </button>
            @endif
        </div>

        <!-- 4A. Desktop Dense Table -->
        <div class="hidden sm:block rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02]">
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">Nama Posisi / Peran</th>
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">Basis Tarif</th>
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right whitespace-nowrap">Nominal Dasar</th>
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center whitespace-nowrap">Utilisasi Jam</th>
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right whitespace-nowrap">Setara per Jam</th>
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($laborRates as $lr)
                            @php
                                $hourly = $laborService->hourlyRateEquivalent($lr);
                            @endphp
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors"
                                x-show="matchesSearch('{{ addslashes($lr->name) }}', '{{ addslashes($lr->basis) }}')">
                                <td class="py-3 px-4">
                                    <div class="font-medium text-black dark:text-white">{{ $lr->name }}</div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45">
                                        {{ $lr->is_subcontractor ? 'Subkontraktor / Outsourcing' : 'Karyawan Internal' }}
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-black/[0.06] dark:bg-white/[0.08] text-black/70 dark:text-white/70 uppercase">
                                        {{ $lr->basis }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right tabular-nums font-semibold text-black dark:text-white">
                                    {{ $business->currency_symbol }} {{ number_format((float)$lr->rate_amount, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-center tabular-nums text-black/70 dark:text-white/70">
                                    {{ (int) ($lr->utilization_rate ?: 80) }}% <span class="text-[11px] text-black/40 dark:text-white/40">({{ (float) ($lr->working_hours_per_day ?: 8) }} jam/hari)</span>
                                </td>
                                <td class="py-3 px-4 text-right tabular-nums font-semibold text-[#34C759] dark:text-[#30D158]">
                                    {{ $business->currency_symbol }} {{ number_format((float)$hourly, 0, ',', '.') }}/jam
                                </td>
                                <td class="py-3 px-4 text-right">
                                    @if(\App\Support\Context::hasPermission('costing.manage'))
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button" title="Edit tarif"
                                                    @click="openEditLabor(@js([
                                                        'id' => $lr->id,
                                                        'name' => $lr->name,
                                                        'basis' => $lr->basis,
                                                        'rate_amount' => $lr->rate_amount,
                                                        'working_days_per_month' => $lr->working_days_per_month,
                                                        'working_hours_per_day' => $lr->working_hours_per_day,
                                                        'utilization_rate' => $lr->utilization_rate,
                                                        'is_subcontractor' => $lr->is_subcontractor
                                                    ]))"
                                                    class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors flex items-center">
                                                Edit
                                            </button>
                                            <button type="button" title="Hapus tarif"
                                                    @click="openDelete('{{ route('labor-rates.destroy', $lr->id) }}', '{{ addslashes($lr->name) }}')"
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
                                <td colspan="6" class="py-12 text-center text-black/40 dark:text-white/40">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <svg class="w-10 h-10 text-black/20 dark:text-white/20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                        </svg>
                                        <p class="text-[13px] font-medium">Belum ada tarif tenaga kerja terdaftar.</p>
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
            @forelse($laborRates as $lr)
                @php
                    $hourly = $laborService->hourlyRateEquivalent($lr);
                @endphp
                <div class="p-3.5 flex items-center justify-between gap-3 active:bg-black/[0.02] dark:active:bg-white/[0.03] transition-colors"
                     x-show="matchesSearch('{{ addslashes($lr->name) }}', '{{ addslashes($lr->basis) }}')">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#34C759] shrink-0"></span>
                            <p class="text-[15px] font-medium text-black dark:text-white truncate">{{ $lr->name }}</p>
                            <span class="text-[10px] uppercase font-semibold px-1.5 py-0.5 rounded-full bg-black/[0.06] dark:bg-white/[0.08] text-black/60 dark:text-white/60">
                                {{ $lr->basis }}
                            </span>
                        </div>
                        <p class="text-[13px] text-[#34C759] dark:text-[#30D158] font-semibold tabular-nums mt-0.5">
                            {{ $business->currency_symbol }} {{ number_format((float)$hourly, 0, ',', '.') }}/jam
                            <span class="text-[11px] font-normal text-black/45 dark:text-white/45">
                                (Dasar: {{ $business->currency_symbol }} {{ number_format((float)$lr->rate_amount, 0, ',', '.') }})
                            </span>
                        </p>
                    </div>
                    @if(\App\Support\Context::hasPermission('costing.manage'))
                        <div class="flex items-center gap-1.5 shrink-0">
                            <button type="button"
                                    @click="openEditLabor(@js([
                                        'id' => $lr->id,
                                        'name' => $lr->name,
                                        'basis' => $lr->basis,
                                        'rate_amount' => $lr->rate_amount,
                                        'working_days_per_month' => $lr->working_days_per_month,
                                        'working_hours_per_day' => $lr->working_hours_per_day,
                                        'utilization_rate' => $lr->utilization_rate,
                                        'is_subcontractor' => $lr->is_subcontractor
                                    ]))"
                                    class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-[#007AFF] bg-[#007AFF]/10 flex items-center">
                                Edit
                            </button>
                            <button type="button"
                                    @click="openDelete('{{ route('labor-rates.destroy', $lr->id) }}', '{{ addslashes($lr->name) }}')"
                                    class="h-8 w-8 rounded-[8px] text-[#FF3B30] bg-[#FF3B30]/10 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-8 text-center text-black/40 dark:text-white/40 text-[13px]">
                    Belum ada tarif tenaga kerja terdaftar.
                </div>
            @endforelse
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 5. SECTION: MESIN & PERALATAN PRODUKSI                -->
    <!-- ===================================================== -->
    <div x-show="activeSection === 'all' || activeSection === 'machines'" class="space-y-3.5 pt-2">
        <div class="flex items-center justify-between px-1">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-[8px] bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 0 0 2.25-2.25V6.75a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5a2.25 2.25 0 0 0 2.25 2.25Zm.75-12h9a.75.75 0 0 1 .75.75v9a.75.75 0 0 1-.75.75h-9a.75.75 0 0 1-.75-.75v-9a.75.75 0 0 1 .75-.75Z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-[16px] font-semibold text-black dark:text-white tracking-tight">Mesin &amp; Peralatan Produksi</h2>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Kalkulasi depresiasi garis lurus, konsumsi listrik kWh, dan biaya perawatan per jam</p>
                </div>
            </div>

            @if(\App\Support\Context::hasPermission('costing.manage'))
                <button type="button" @click="showMachineModal = true"
                        class="h-8 px-3 rounded-[8px] text-[12px] font-medium text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Tambah Mesin</span>
                </button>
            @endif
        </div>

        <!-- 5A. Desktop Dense Table -->
        <div class="hidden sm:block rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02]">
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">Nama Mesin / Peralatan</th>
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right whitespace-nowrap">Harga Beli</th>
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center whitespace-nowrap">Umur Manfaat</th>
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right whitespace-nowrap">Depresiasi / Jam</th>
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right whitespace-nowrap">Listrik &amp; Rawat</th>
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right whitespace-nowrap">Total Biaya / Jam</th>
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($machines as $m)
                            @php
                                $usefulHours = max(1, (float) ($m->useful_life_hours ?: 1));
                                $salvage = (float) ($m->residual_value ?? $m->salvage_value ?? 0);
                                $deprec = ((float) $m->purchase_price - $salvage) / $usefulHours;
                                $elecCost = (float) ($m->electricity_cost_per_hour ?? 0);
                                $maintCost = (float) ($m->maintenance_cost_per_hour ?? 0);
                                $totalHourly = $deprec + $elecCost + $maintCost;
                            @endphp
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors"
                                x-show="matchesSearch('{{ addslashes($m->name) }}', '{{ addslashes($m->code ?? '') }}')">
                                <td class="py-3 px-4">
                                    <div class="font-medium text-black dark:text-white">{{ $m->name }}</div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45">
                                        Residu: {{ $business->currency_symbol }} {{ number_format((float)$salvage, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right tabular-nums font-semibold text-black dark:text-white">
                                    {{ $business->currency_symbol }} {{ number_format((float)$m->purchase_price, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-center tabular-nums text-black/70 dark:text-white/70">
                                    {{ number_format((float)$m->useful_life_hours, 0, ',', '.') }} jam
                                </td>
                                <td class="py-3 px-4 text-right tabular-nums text-black/60 dark:text-white/60">
                                    {{ $business->currency_symbol }} {{ number_format((float)$deprec, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right tabular-nums text-black/60 dark:text-white/60">
                                    {{ $business->currency_symbol }} {{ number_format((float)($elecCost + $maintCost), 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right tabular-nums font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                                    {{ $business->currency_symbol }} {{ number_format((float)$totalHourly, 0, ',', '.') }}/jam
                                </td>
                                <td class="py-3 px-4 text-right">
                                    @if(\App\Support\Context::hasPermission('costing.manage'))
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button" title="Edit mesin"
                                                    @click="openEditMachine(@js([
                                                        'id' => $m->id,
                                                        'name' => $m->name,
                                                        'purchase_price' => $m->purchase_price,
                                                        'residual_value' => $m->residual_value,
                                                        'useful_life_hours' => $m->useful_life_hours,
                                                        'power_kw' => $m->power_kw,
                                                        'electricity_cost_per_kwh' => 1500,
                                                        'maintenance_cost_per_hour' => $m->maintenance_cost_per_hour
                                                    ]))"
                                                    class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors flex items-center">
                                                Edit
                                            </button>
                                            <button type="button" title="Hapus mesin"
                                                    @click="openDelete('{{ route('machines.destroy', $m->id) }}', '{{ addslashes($m->name) }}')"
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
                                <td colspan="7" class="py-12 text-center text-black/40 dark:text-white/40">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <svg class="w-10 h-10 text-black/20 dark:text-white/20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 0 0 2.25-2.25V6.75a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5a2.25 2.25 0 0 0 2.25 2.25Zm.75-12h9a.75.75 0 0 1 .75.75v9a.75.75 0 0 1-.75.75h-9a.75.75 0 0 1-.75-.75v-9a.75.75 0 0 1 .75-.75Z" />
                                        </svg>
                                        <p class="text-[13px] font-medium">Belum ada mesin atau peralatan produksi terdaftar.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 5B. Mobile iOS Grouped Inset List (sm:hidden) -->
        <div class="sm:hidden rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
            @forelse($machines as $m)
                @php
                    $usefulHours = max(1, (float) ($m->useful_life_hours ?: 1));
                    $salvage = (float) ($m->residual_value ?? $m->salvage_value ?? 0);
                    $deprec = ((float) $m->purchase_price - $salvage) / $usefulHours;
                    $elecCost = (float) ($m->electricity_cost_per_hour ?? 0);
                    $maintCost = (float) ($m->maintenance_cost_per_hour ?? 0);
                    $totalHourly = $deprec + $elecCost + $maintCost;
                @endphp
                <div class="p-3.5 flex items-center justify-between gap-3 active:bg-black/[0.02] dark:active:bg-white/[0.03] transition-colors"
                     x-show="matchesSearch('{{ addslashes($m->name) }}', '{{ addslashes($m->code ?? '') }}')">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#007AFF] shrink-0"></span>
                            <p class="text-[15px] font-medium text-black dark:text-white truncate">{{ $m->name }}</p>
                        </div>
                        <p class="text-[13px] text-[#007AFF] dark:text-[#0A84FF] font-semibold tabular-nums mt-0.5">
                            {{ $business->currency_symbol }} {{ number_format((float)$totalHourly, 0, ',', '.') }}/jam
                            <span class="text-[11px] font-normal text-black/45 dark:text-white/45">
                                · {{ number_format((float)$m->useful_life_hours, 0, ',', '.') }} jam
                            </span>
                        </p>
                    </div>
                    @if(\App\Support\Context::hasPermission('costing.manage'))
                        <div class="flex items-center gap-1.5 shrink-0">
                            <button type="button"
                                    @click="openEditMachine(@js([
                                        'id' => $m->id,
                                        'name' => $m->name,
                                        'purchase_price' => $m->purchase_price,
                                        'residual_value' => $m->residual_value,
                                        'useful_life_hours' => $m->useful_life_hours,
                                        'power_kw' => $m->power_kw,
                                        'electricity_cost_per_kwh' => 1500,
                                        'maintenance_cost_per_hour' => $m->maintenance_cost_per_hour
                                    ]))"
                                    class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-[#007AFF] bg-[#007AFF]/10 flex items-center">
                                Edit
                            </button>
                            <button type="button"
                                    @click="openDelete('{{ route('machines.destroy', $m->id) }}', '{{ addslashes($m->name) }}')"
                                    class="h-8 w-8 rounded-[8px] text-[#FF3B30] bg-[#FF3B30]/10 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-8 text-center text-black/40 dark:text-white/40 text-[13px]">
                    Belum ada mesin atau peralatan produksi terdaftar.
                </div>
            @endforelse
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 6. MODAL: TAMBAH TARIF UPAH (Apple Sheet)             -->
    <!-- ===================================================== -->
    <div x-show="showLaborModal" style="display: none"
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/25 backdrop-blur-[2px]"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div @click.outside="showLaborModal = false"
             class="w-full sm:max-w-md rounded-t-[20px] sm:rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] max-h-[90vh] overflow-y-auto"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95">

            <div class="sm:hidden w-10 h-1 rounded-full bg-black/20 dark:bg-white/20 mx-auto mb-2"></div>

            <div class="flex items-center justify-between border-b border-black/10 dark:border-white/10 pb-3">
                <h3 class="text-[17px] font-semibold text-black dark:text-white">Tambah Tarif Tenaga Kerja</h3>
                <button type="button" @click="showLaborModal = false" class="w-7 h-7 rounded-[6px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white flex items-center justify-center transition">✕</button>
            </div>

            <form method="POST" action="{{ route('labor-rates.store') }}" class="space-y-3.5 text-[13px]">
                @csrf

                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Nama Peran / Posisi <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Koki Masak / Barista / Operator"
                           class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:ring-2 focus:ring-[#007AFF]/50">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Basis Tarif <span class="text-[#FF3B30]">*</span></label>
                        <select name="basis" required class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                            <option value="hourly">Per Jam (Hourly)</option>
                            <option value="daily">Per Hari (Daily)</option>
                            <option value="monthly">Bulanan (Monthly)</option>
                            <option value="per_unit">Per Unit (Potong)</option>
                            <option value="per_task">Per Tugas (Task)</option>
                            <option value="per_project">Per Proyek</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Nominal Upah (Rp) <span class="text-[#FF3B30]">*</span></label>
                        <input type="number" name="rate_amount" required min="1" step="1000" placeholder="3500000"
                               class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                </div>

                <!-- Parameters Box -->
                <div class="rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 p-3 space-y-2">
                    <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wide">Parameter Efektivitas Kerja</span>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block text-[11px] text-black/60 dark:text-white/60 mb-1">Hari/Bulan</label>
                            <input type="number" name="working_days_per_month" value="22" min="1" max="31"
                                   class="w-full h-9 px-2.5 bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[8px] text-black dark:text-white tabular-nums text-center focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                        <div>
                            <label class="block text-[11px] text-black/60 dark:text-white/60 mb-1">Jam/Hari</label>
                            <input type="number" name="working_hours_per_day" value="8" min="1" max="24"
                                   class="w-full h-9 px-2.5 bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[8px] text-black dark:text-white tabular-nums text-center focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                        <div>
                            <label class="block text-[11px] text-black/60 dark:text-white/60 mb-1">Utilisasi %</label>
                            <input type="number" name="utilization_percentage" value="80" min="10" max="100"
                                   class="w-full h-9 px-2.5 bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[8px] text-black dark:text-white tabular-nums text-center focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_subcontractor" id="subcontractor-new" value="1"
                           class="rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                    <label for="subcontractor-new" class="text-[13px] text-black/70 dark:text-white/70">Pekerja Subkontraktor / Jasa Luar</label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-black/10 dark:border-white/10">
                    <button type="button" @click="showLaborModal = false"
                            class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] active:scale-[0.97] transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                        Simpan Tarif
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 7. MODAL: EDIT TARIF UPAH (Apple Sheet)               -->
    <!-- ===================================================== -->
    <div x-show="showEditLaborModal" style="display: none"
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/25 backdrop-blur-[2px]"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div @click.outside="showEditLaborModal = false"
             class="w-full sm:max-w-md rounded-t-[20px] sm:rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] max-h-[90vh] overflow-y-auto"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95">

            <div class="sm:hidden w-10 h-1 rounded-full bg-black/20 dark:bg-white/20 mx-auto mb-2"></div>

            <div class="flex items-center justify-between border-b border-black/10 dark:border-white/10 pb-3">
                <h3 class="text-[17px] font-semibold text-black dark:text-white">Edit Tarif Tenaga Kerja</h3>
                <button type="button" @click="showEditLaborModal = false" class="w-7 h-7 rounded-[6px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white flex items-center justify-center transition">✕</button>
            </div>

            <form :action="'/labor-rates/' + editLabor.id" method="POST" class="space-y-3.5 text-[13px]">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Nama Peran / Posisi <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" name="name" x-model="editLabor.name" required placeholder="Nama posisi"
                           class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Basis Tarif <span class="text-[#FF3B30]">*</span></label>
                        <select name="basis" x-model="editLabor.basis" required class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                            <option value="hourly">Per Jam (Hourly)</option>
                            <option value="daily">Per Hari (Daily)</option>
                            <option value="monthly">Bulanan (Monthly)</option>
                            <option value="per_unit">Per Unit (Potong)</option>
                            <option value="per_task">Per Tugas (Task)</option>
                            <option value="per_project">Per Proyek</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Nominal Upah (Rp) <span class="text-[#FF3B30]">*</span></label>
                        <input type="number" name="rate_amount" x-model="editLabor.rate_amount" required min="1" step="1000"
                               class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                </div>

                <!-- Parameters Box -->
                <div class="rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 p-3 space-y-2">
                    <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wide">Parameter Efektivitas Kerja</span>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block text-[11px] text-black/60 dark:text-white/60 mb-1">Hari/Bulan</label>
                            <input type="number" name="working_days_per_month" x-model="editLabor.working_days_per_month" min="1" max="31"
                                   class="w-full h-9 px-2.5 bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[8px] text-black dark:text-white tabular-nums text-center focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                        <div>
                            <label class="block text-[11px] text-black/60 dark:text-white/60 mb-1">Jam/Hari</label>
                            <input type="number" name="working_hours_per_day" x-model="editLabor.working_hours_per_day" min="1" max="24"
                                   class="w-full h-9 px-2.5 bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[8px] text-black dark:text-white tabular-nums text-center focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                        <div>
                            <label class="block text-[11px] text-black/60 dark:text-white/60 mb-1">Utilisasi %</label>
                            <input type="number" name="utilization_percentage" x-model="editLabor.utilization_percentage" min="10" max="100"
                                   class="w-full h-9 px-2.5 bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[8px] text-black dark:text-white tabular-nums text-center focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_subcontractor" id="subcontractor-edit" value="1" x-model="editLabor.is_subcontractor"
                           class="rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                    <label for="subcontractor-edit" class="text-[13px] text-black/70 dark:text-white/70">Pekerja Subkontraktor / Jasa Luar</label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-black/10 dark:border-white/10">
                    <button type="button" @click="showEditLaborModal = false"
                            class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] active:scale-[0.97] transition">
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

    <!-- ===================================================== -->
    <!-- 8. MODAL: TAMBAH MESIN (Apple Sheet)                  -->
    <!-- ===================================================== -->
    <div x-show="showMachineModal" style="display: none"
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/25 backdrop-blur-[2px]"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div @click.outside="showMachineModal = false"
             class="w-full sm:max-w-md rounded-t-[20px] sm:rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] max-h-[90vh] overflow-y-auto"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95">

            <div class="sm:hidden w-10 h-1 rounded-full bg-black/20 dark:bg-white/20 mx-auto mb-2"></div>

            <div class="flex items-center justify-between border-b border-black/10 dark:border-white/10 pb-3">
                <h3 class="text-[17px] font-semibold text-black dark:text-white">Tambah Mesin &amp; Peralatan</h3>
                <button type="button" @click="showMachineModal = false" class="w-7 h-7 rounded-[6px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white flex items-center justify-center transition">✕</button>
            </div>

            <form method="POST" action="{{ route('machines.store') }}" class="space-y-3.5 text-[13px]">
                @csrf

                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Nama Mesin / Peralatan <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Mesin Espresso 2 Group / Oven Deck"
                           class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:ring-2 focus:ring-[#007AFF]/50">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Harga Beli (Rp) <span class="text-[#FF3B30]">*</span></label>
                        <input type="number" name="purchase_price" required min="1" step="10000" placeholder="45000000"
                               class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Nilai Residu (Sisa)</label>
                        <input type="number" name="salvage_value" min="0" step="10000" placeholder="0" value="0"
                               class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Estimasi Umur Manfaat (Jam) <span class="text-[#FF3B30]">*</span></label>
                    <input type="number" name="useful_life_hours" required min="100" placeholder="10000" value="10000"
                           class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/50">
                </div>

                <!-- Electric & Maintenance Parameters -->
                <div class="rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 p-3 space-y-2">
                    <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wide">Konsumsi Daya &amp; Pemeliharaan</span>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block text-[11px] text-black/60 dark:text-white/60 mb-1">Daya (kW)</label>
                            <input type="number" name="power_kw" value="1.5" step="0.1" min="0"
                                   class="w-full h-9 px-2.5 bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[8px] text-black dark:text-white tabular-nums text-center focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                        <div>
                            <label class="block text-[11px] text-black/60 dark:text-white/60 mb-1">Listrik/kWh</label>
                            <input type="number" name="electricity_cost_per_kwh" value="1500" min="0"
                                   class="w-full h-9 px-2.5 bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[8px] text-black dark:text-white tabular-nums text-center focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                        <div>
                            <label class="block text-[11px] text-black/60 dark:text-white/60 mb-1">Rawat/Jam</label>
                            <input type="number" name="maintenance_cost_per_hour" value="0" min="0" step="500"
                                   class="w-full h-9 px-2.5 bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[8px] text-black dark:text-white tabular-nums text-center focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-black/10 dark:border-white/10">
                    <button type="button" @click="showMachineModal = false"
                            class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] active:scale-[0.97] transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                        Simpan Mesin
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 9. MODAL: EDIT MESIN (Apple Sheet)                    -->
    <!-- ===================================================== -->
    <div x-show="showEditMachineModal" style="display: none"
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/25 backdrop-blur-[2px]"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div @click.outside="showEditMachineModal = false"
             class="w-full sm:max-w-md rounded-t-[20px] sm:rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] max-h-[90vh] overflow-y-auto"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95">

            <div class="sm:hidden w-10 h-1 rounded-full bg-black/20 dark:bg-white/20 mx-auto mb-2"></div>

            <div class="flex items-center justify-between border-b border-black/10 dark:border-white/10 pb-3">
                <h3 class="text-[17px] font-semibold text-black dark:text-white">Edit Mesin &amp; Peralatan</h3>
                <button type="button" @click="showEditMachineModal = false" class="w-7 h-7 rounded-[6px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white flex items-center justify-center transition">✕</button>
            </div>

            <form :action="'/machines/' + editMachine.id" method="POST" class="space-y-3.5 text-[13px]">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Nama Mesin / Peralatan <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" name="name" x-model="editMachine.name" required placeholder="Nama mesin"
                           class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Harga Beli (Rp) <span class="text-[#FF3B30]">*</span></label>
                        <input type="number" name="purchase_price" x-model="editMachine.purchase_price" required min="1" step="10000"
                               class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Nilai Residu (Sisa)</label>
                        <input type="number" name="salvage_value" x-model="editMachine.salvage_value" min="0" step="10000"
                               class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Estimasi Umur Manfaat (Jam) <span class="text-[#FF3B30]">*</span></label>
                    <input type="number" name="useful_life_hours" x-model="editMachine.useful_life_hours" required min="100"
                           class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/50">
                </div>

                <!-- Electric & Maintenance Parameters -->
                <div class="rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 p-3 space-y-2">
                    <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wide">Konsumsi Daya &amp; Pemeliharaan</span>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block text-[11px] text-black/60 dark:text-white/60 mb-1">Daya (kW)</label>
                            <input type="number" name="power_kw" x-model="editMachine.power_kw" step="0.1" min="0"
                                   class="w-full h-9 px-2.5 bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[8px] text-black dark:text-white tabular-nums text-center focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                        <div>
                            <label class="block text-[11px] text-black/60 dark:text-white/60 mb-1">Listrik/kWh</label>
                            <input type="number" name="electricity_cost_per_kwh" x-model="editMachine.electricity_cost_per_kwh" min="0"
                                   class="w-full h-9 px-2.5 bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[8px] text-black dark:text-white tabular-nums text-center focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                        <div>
                            <label class="block text-[11px] text-black/60 dark:text-white/60 mb-1">Rawat/Jam</label>
                            <input type="number" name="maintenance_cost_per_hour" x-model="editMachine.maintenance_cost_per_hour" min="0" step="500"
                                   class="w-full h-9 px-2.5 bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[8px] text-black dark:text-white tabular-nums text-center focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-black/10 dark:border-white/10">
                    <button type="button" @click="showEditMachineModal = false"
                            class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] active:scale-[0.97] transition">
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

    <!-- ===================================================== -->
    <!-- 10. APPLE ALERT DIALOG (Native Centered Modal)        -->
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
                <p class="text-[17px] font-semibold text-black dark:text-white">Hapus Data?</p>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                    <span x-text="deleteTarget.name" class="font-medium text-black dark:text-white"></span> akan dihapus dari sistem biaya. Tindakan ini tidak dapat dipulihkan.
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

    <!-- Hidden Labor/Machine Delete Form -->
    <form id="form-delete-labor-machine" method="POST" action="" class="hidden">
        @csrf
        @method('DELETE')
    </form>

</div>
@endsection
