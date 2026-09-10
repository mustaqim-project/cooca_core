@extends('layouts.app', [
    'title' => 'Pemasok & Vendor',
    'headerTitle' => 'Manajemen Pemasok (Suppliers)',
    'headerSubtitle' => 'Kelola direktori pemasok, kontak PIC, dan pengadaan bahan baku'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
    showAddModal: false,
    showEditModal: false,
    deleteModalOpen: false,
    deleteTarget: { id: null, name: '' },
    editSupplier: {
        id: '',
        name: '',
        contact_person: '',
        phone: '',
        email: '',
        address: '',
        notes: ''
    },

    openEditModal(sup) {
        this.editSupplier = { ...sup };
        this.showEditModal = true;
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
            const f = document.getElementById('form-delete-supplier-' + this.deleteTarget.id);
            if (f) f.submit();
        }
    }
}">

    {{-- ===================================================== --}}
    {{-- 1. SUB-NAVIGATION TABS (Apple Segmented Control)      --}}
    {{-- ===================================================== --}}
    <div class="overflow-x-auto pb-1 scrollbar-none">
        <div class="inline-flex p-1 rounded-[11px] bg-black/[0.05] dark:bg-white/[0.07] border border-black/5 dark:border-white/10 text-[13px] font-medium whitespace-nowrap">
            <a href="{{ route('suppliers.index') }}"
               class="px-3.5 py-1.5 rounded-[9px] bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-semibold flex items-center gap-1.5 transition-all">
                <svg class="w-4 h-4 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                </svg>
                <span>Daftar Pemasok</span>
                <span class="px-1.5 py-0.2 rounded-full text-[11px] tabular-nums font-semibold bg-[#007AFF]/12 text-[#007AFF]">{{ $suppliers->total() }}</span>
            </a>
            <a href="{{ route('materials.index') }}"
               class="px-3.5 py-1.5 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                </svg>
                <span>Katalog Bahan Baku</span>
            </a>
            <a href="{{ route('purchase-orders.index') }}"
               class="px-3.5 py-1.5 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                <span>Purchase Order (PO)</span>
            </a>
            <a href="{{ route('purchasing.bills.index') }}"
               class="px-3.5 py-1.5 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6H2.25m0 0v1.5c0 .621.504 1.125 1.125 1.125H16.5m-14.25 0h14.25m0 0a3.75 3.75 0 0 0 3.75-3.75V4.5m-3.75 4.125V18.75" />
                </svg>
                <span>Tagihan Pembelian (Bills)</span>
            </a>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- 2. TOOLBAR / PAGE HEADER (macOS Sonoma Style)         --}}
    {{-- ===================================================== --}}
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            {{-- Breadcrumb minimal --}}
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <span class="text-black/70 dark:text-white/70 font-medium">Pengadaan</span>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Pemasok &amp; Vendor</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Direktori Pemasok &amp; Vendor</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Kelola mitra vendor bahan baku, kontak PIC, dan pengadaan bisnis</p>
        </div>

        {{-- Toolbar Actions --}}
        <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
            <a href="{{ route('materials.index') }}"
               class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                </svg>
                <span>Katalog Bahan</span>
            </a>

            <a href="{{ route('purchase-orders.index') }}"
               class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                <span>Daftar PO</span>
            </a>

            @if(\App\Support\Context::hasPermission('master_data.suppliers.manage'))
            <button type="button" @click="showAddModal = true"
                    class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Pemasok</span>
            </button>
            @endif
        </div>
    </header>

    {{-- ===================================================== --}}
    {{-- FLASH MESSAGES                                        --}}
    {{-- ===================================================== --}}
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

    {{-- ===================================================== --}}
    {{-- 3. KPI SUMMARY (Apple Flat Neutral Cards)             --}}
    {{-- ===================================================== --}}
    @php
        $activeMaterialsCount = $suppliers->sum('materials_count');
        $suppliedVendorsCount = $suppliers->filter(fn($s) => $s->materials_count > 0)->count();
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        {{-- KPI 1: Total Pemasok --}}
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Pemasok</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-black dark:text-white">{{ $suppliers->total() }}</span>
                <span class="text-[11px] text-black/40 dark:text-white/40">Mitra Terdaftar</span>
            </div>
        </div>

        {{-- KPI 2: Pemasok Bahan Aktif --}}
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Pemasok Pasokan Aktif</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF]">{{ $suppliedVendorsCount }}</span>
                <span class="text-[11px] text-black/40 dark:text-white/40">Ada Bahan Baku</span>
            </div>
        </div>

        {{-- KPI 3: Item Bahan Terhubung --}}
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Bahan Baku Terhubung</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">{{ $activeMaterialsCount }}</span>
                <span class="text-[11px] text-black/40 dark:text-white/40">Item Pasokan</span>
            </div>
        </div>

        {{-- KPI 4: Shortcut Buat PO Baru --}}
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Pengadaan Barang</span>
            <div class="mt-2 flex items-center justify-between">
                <a href="{{ route('purchase-orders.create', ['type' => 'supplier']) }}"
                   class="h-8 px-3 rounded-[8px] text-[12px] font-semibold text-[#5856D6] dark:text-[#5E5CE6] bg-[#5856D6]/10 hover:bg-[#5856D6]/15 active:scale-[0.97] transition-all flex items-center gap-1.5">
                    <span>+ Buat PO Supplier</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- 4. CONTROLS: macOS SEARCH & FILTER TOOLBAR            --}}
    {{-- ===================================================== --}}
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 sm:p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        {{-- Search Form --}}
        <form method="GET" action="{{ route('suppliers.index') }}" class="flex-1 flex items-center gap-2 max-w-lg">
            <div class="relative flex-1">
                <svg class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama pemasok, kontak PIC, telepon, email..."
                       class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-9 pr-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>
            <button type="submit"
                    class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all">
                Cari
            </button>
            @if(request('search'))
                <a href="{{ route('suppliers.index') }}"
                   class="h-9 px-2.5 rounded-[8px] text-[13px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/10 flex items-center transition">
                    Reset
                </a>
            @endif
        </form>

        <div class="text-[13px] text-black/50 dark:text-white/50 font-normal self-start sm:self-auto">
            Menampilkan <span class="font-medium text-black dark:text-white tabular-nums">{{ $suppliers->count() }}</span> dari <span class="font-medium text-black dark:text-white tabular-nums">{{ $suppliers->total() }}</span> pemasok
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- 5. SUPPLIERS DATA TABLE (Apple Dense Table)           --}}
    {{-- ===================================================== --}}
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">
                        <th class="px-4 py-2.5">Nama Pemasok / Vendor</th>
                        <th class="px-4 py-2.5">Kontak PIC</th>
                        <th class="px-4 py-2.5">Telepon &amp; WhatsApp</th>
                        <th class="px-4 py-2.5">Email</th>
                        <th class="px-4 py-2.5">Alamat Fisik</th>
                        <th class="px-4 py-2.5 text-center">Bahan Baku</th>
                        <th class="px-4 py-2.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($suppliers as $supplier)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        {{-- Nama & Catatan --}}
                        <td class="px-4 py-3">
                            <div class="font-medium text-black dark:text-white">
                                {{ $supplier->name }}
                            </div>
                            @if($supplier->notes)
                                <div class="text-[11px] text-black/45 dark:text-white/45 truncate max-w-xs mt-0.5" title="{{ $supplier->notes }}">
                                    {{ $supplier->notes }}
                                </div>
                            @endif
                        </td>

                        {{-- PIC --}}
                        <td class="px-4 py-3 text-black/70 dark:text-white/70 font-medium">
                            {{ $supplier->contact_person ?: '—' }}
                        </td>

                        {{-- Phone / WA --}}
                        <td class="px-4 py-3 font-mono tabular-nums">
                            @if($supplier->phone)
                                @php
                                    $waNumber = preg_replace('/[^0-9]/', '', $supplier->phone);
                                    if (str_starts_with($waNumber, '0')) {
                                        $waNumber = '62' . substr($waNumber, 1);
                                    }
                                @endphp
                                <div class="flex items-center gap-2">
                                    <a href="tel:{{ $supplier->phone }}" class="hover:text-[#007AFF] transition inline-flex items-center gap-1 text-black/80 dark:text-white/80 font-medium">
                                        <span>{{ $supplier->phone }}</span>
                                    </a>
                                    <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener noreferrer"
                                       class="h-6 px-1.5 rounded-[6px] bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] hover:bg-[#34C759]/20 transition flex items-center justify-center gap-1 text-[11px] font-semibold"
                                       title="Chat WhatsApp Pemasok">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a.75.75 0 0 1-.774-.75 6.002 6.002 0 0 1 1.09-3.268C4.542 15.65 4 13.916 4 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                                        </svg>
                                        <span>WA</span>
                                    </a>
                                </div>
                            @else
                                <span class="text-black/30 dark:text-white/30">—</span>
                            @endif
                        </td>

                        {{-- Email --}}
                        <td class="px-4 py-3">
                            @if($supplier->email)
                                <a href="mailto:{{ $supplier->email }}" class="text-[#007AFF] hover:underline inline-flex items-center gap-1 text-[12px]">
                                    <span>{{ $supplier->email }}</span>
                                </a>
                            @else
                                <span class="text-black/30 dark:text-white/30">—</span>
                            @endif
                        </td>

                        {{-- Address --}}
                        <td class="px-4 py-3 text-black/50 dark:text-white/50 max-w-xs truncate" title="{{ $supplier->address }}">
                            {{ $supplier->address ?: '—' }}
                        </td>

                        {{-- Bahan Baku Count Badge --}}
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold tabular-nums {{ $supplier->materials_count > 0 ? 'bg-[#007AFF]/12 text-[#007AFF]' : 'bg-black/[0.06] dark:bg-white/[0.08] text-black/55 dark:text-white/55' }}">
                                {{ $supplier->materials_count }} item
                            </span>
                        </td>

                        {{-- Action Buttons --}}
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                @if(\App\Support\Context::hasPermission('master_data.suppliers.manage'))
                                <button type="button" @click="openEditModal({
                                    id: '{{ $supplier->id }}',
                                    name: '{{ addslashes($supplier->name) }}',
                                    contact_person: '{{ addslashes($supplier->contact_person ?? '') }}',
                                    phone: '{{ addslashes($supplier->phone ?? '') }}',
                                    email: '{{ addslashes($supplier->email ?? '') }}',
                                    address: '{{ addslashes($supplier->address ?? '') }}',
                                    notes: '{{ addslashes($supplier->notes ?? '') }}'
                                })" class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors flex items-center">
                                    Edit
                                </button>
                                <button type="button" @click="openDelete({{ $supplier->id }}, '{{ addslashes($supplier->name) }}')"
                                        class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/8 transition-colors flex items-center">
                                    Hapus
                                </button>
                                <form id="form-delete-supplier-{{ $supplier->id }}" method="POST" action="{{ route('suppliers.destroy', $supplier->id) }}" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-black/50 dark:text-white/50">
                            <div class="w-12 h-12 rounded-full bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center mx-auto mb-2.5 text-black/30 dark:text-white/30">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                </svg>
                            </div>
                            <h4 class="text-[15px] font-semibold text-black dark:text-white">Belum Ada Data Pemasok</h4>
                            <p class="text-[12px] text-black/45 dark:text-white/45 mt-1 max-w-sm mx-auto">
                                Tambahkan pemasok untuk mengelola kontak vendor, riwayat PO, dan pengadaan bahan baku bisnis.
                            </p>
                            @if(\App\Support\Context::hasPermission('master_data.suppliers.manage'))
                            <button type="button" @click="showAddModal = true"
                                    class="mt-4 h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                <span>Tambah Pemasok Pertama</span>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($suppliers->hasPages())
        <div class="px-4 py-3 border-t border-black/5 dark:border-white/10 flex items-center justify-between text-[13px] text-black/60 dark:text-white/60">
            {{ $suppliers->links() }}
        </div>
        @endif
    </div>

    {{-- ===================================================== --}}
    {{-- 6. APPLE SHEET: TAMBAH PEMASOK                        --}}
    {{-- ===================================================== --}}
    <div x-show="showAddModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px] p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="w-full max-w-lg rounded-[18px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.25)] p-6 space-y-5 max-h-[90vh] overflow-y-auto"
             @click.outside="showAddModal = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3.5">
                <div>
                    <h3 class="text-[17px] font-semibold text-black dark:text-white">Tambah Pemasok Baru</h3>
                    <p class="text-[13px] text-black/50 dark:text-white/50">Daftarkan mitra vendor penyedia bahan baku bisnis</p>
                </div>
                <button type="button" @click="showAddModal = false" class="p-1 rounded-[6px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('suppliers.store') }}" class="space-y-4 text-[13px]">
                @csrf
                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                        Nama Pemasok / Vendor <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="text" name="name" required placeholder="Contoh: PT Sumber Pangan Sejahtera, CV Mitra Tani..."
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                            Nama Kontak (PIC)
                        </label>
                        <input type="text" name="contact_person" placeholder="Mis: Budi Santoso"
                               class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                            No. Telepon / WhatsApp
                        </label>
                        <input type="text" name="phone" placeholder="0812-3456-7890"
                               class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                        Alamat Email
                    </label>
                    <input type="email" name="email" placeholder="sales@sumberpangan.com"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                        Alamat Lengkap
                    </label>
                    <textarea name="address" rows="2" placeholder="Jl. Pergudangan No. 12, Kawasan Industri..."
                              class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] p-3 text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition resize-none"></textarea>
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                        Catatan Khusus (TOP / Ketentuan Order)
                    </label>
                    <input type="text" name="notes" placeholder="Term pembayaran 14 hari, minimum order 50kg..."
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3.5 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showAddModal = false"
                            class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                        Simpan Pemasok
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- 7. APPLE SHEET: EDIT PEMASOK                          --}}
    {{-- ===================================================== --}}
    <div x-show="showEditModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px] p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="w-full max-w-lg rounded-[18px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.25)] p-6 space-y-5 max-h-[90vh] overflow-y-auto"
             @click.outside="showEditModal = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3.5">
                <div>
                    <h3 class="text-[17px] font-semibold text-black dark:text-white">Edit Data Pemasok</h3>
                    <p class="text-[13px] text-black/50 dark:text-white/50" x-text="'Memperbarui: ' + editSupplier.name"></p>
                </div>
                <button type="button" @click="showEditModal = false" class="p-1 rounded-[6px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form :action="'/suppliers/' + editSupplier.id" method="POST" class="space-y-4 text-[13px]">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                        Nama Pemasok / Vendor <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="text" name="name" x-model="editSupplier.name" required
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                            Nama Kontak (PIC)
                        </label>
                        <input type="text" name="contact_person" x-model="editSupplier.contact_person"
                               class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                            No. Telepon / WhatsApp
                        </label>
                        <input type="text" name="phone" x-model="editSupplier.phone"
                               class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                        Alamat Email
                    </label>
                    <input type="email" name="email" x-model="editSupplier.email"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                        Alamat Lengkap
                    </label>
                    <textarea name="address" rows="2" x-model="editSupplier.address"
                              class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] p-3 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition resize-none"></textarea>
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                        Catatan Khusus
                    </label>
                    <input type="text" name="notes" x-model="editSupplier.notes"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3.5 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showEditModal = false"
                            class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- 8. APPLE ALERT DIALOG (Centered Confirmation)         --}}
    {{-- ===================================================== --}}
    <div x-show="deleteModalOpen"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px] p-4"
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
                <p class="text-[17px] font-semibold text-black dark:text-white">Hapus Pemasok?</p>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                    <span x-text="deleteTarget.name" class="font-medium text-black dark:text-white"></span> akan dihapus dari sistem. Pastikan tidak ada PO aktif terkait.
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

</div>
@endsection
