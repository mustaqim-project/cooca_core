@extends('layouts.app', [
    'title' => 'Pemasok & Vendor',
    'headerTitle' => 'Manajemen Pemasok (Suppliers)',
    'headerSubtitle' => 'Kelola direktori mitra vendor, PIC, informasi perbankan, dan pengadaan bahan baku bisnis',
])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="{
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
            bank_name: '',
            bank_account_number: '',
            bank_account_holder: '',
            address: '',
            notes: ''
        },

        openEditModal(sup) {
            this.editSupplier = {
                id: sup.id || '',
                name: sup.name || '',
                contact_person: sup.contact_person || '',
                phone: sup.phone || '',
                email: sup.email || '',
                bank_name: sup.bank_name || '',
                bank_account_number: sup.bank_account_number || '',
                bank_account_holder: sup.bank_account_holder || '',
                address: sup.address || '',
                notes: sup.notes || ''
            };
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
        {{-- 1. TOOLBAR / PAGE HEADER                                --}}
        {{-- ===================================================== --}}
        <x-module-header
            title="Direktori Pemasok & Vendor"
            subtitle="Kelola direktori mitra vendor, PIC, rekening perbankan, dan histori pengadaan bahan baku">
            <a href="{{ route('materials.index') }}"
                class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="boxes" class="w-4 h-4 text-black/50 dark:text-white/50"></i>
                <span>Katalog Bahan</span>
            </a>

            <a href="{{ route('purchase-orders.index') }}"
                class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="file-text" class="w-4 h-4 text-black/50 dark:text-white/50"></i>
                <span>Daftar PO</span>
            </a>

            @if (\App\Support\Context::hasPermission('master_data.suppliers.manage'))
                <button type="button" @click="showAddModal = true"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Tambah Pemasok</span>
                </button>
            @endif
        </x-module-header>

        {{-- ===================================================== --}}
        {{-- 2. MODULE TABS (SSOT)                                   --}}
        {{-- ===================================================== --}}
        <x-module-tabs module="purchasing" />

        {{-- ===================================================== --}}
        {{-- FLASH MESSAGES                                        --}}
        {{-- ===================================================== --}}
        @if (session('success'))
            <div class="rounded-[14px] bg-[#34C759]/10 border border-[#34C759]/20 px-4 py-3 text-xs text-[#248A3D] dark:text-[#30D158] flex items-center gap-2.5">
                <i data-lucide="check-circle" class="w-4 h-4 shrink-0 text-[#34C759]"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 px-4 py-3 text-xs text-[#C41E17] dark:text-[#FF453A] flex items-center gap-2.5">
                <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0 text-[#FF3B30]"></i>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        {{-- ===================================================== --}}
        {{-- 3. KPI SUMMARY (Bento Apple HIG Cards)                --}}
        {{-- ===================================================== --}}
        @php
            $activeMaterialsCount = $suppliers->sum('materials_count');
            $suppliedVendorsCount = $suppliers->filter(fn($s) => $s->materials_count > 0)->count();
        @endphp
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            {{-- KPI 1: Total Pemasok --}}
            <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Pemasok</span>
                    <div class="w-7 h-7 rounded-[8px] bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400">
                        <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl font-black tabular-nums text-slate-900 dark:text-white">{{ $suppliers->total() }}</span>
                    <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">Mitra Terdaftar</span>
                </div>
            </div>

            {{-- KPI 2: Pemasok Bahan Aktif --}}
            <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Pemasok Pasokan Aktif</span>
                    <div class="w-7 h-7 rounded-[8px] bg-[#007AFF]/10 flex items-center justify-center text-[#007AFF]">
                        <i data-lucide="package-check" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl font-black tabular-nums text-[#007AFF] dark:text-[#0A84FF]">{{ $suppliedVendorsCount }}</span>
                    <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">Ada Bahan Baku</span>
                </div>
            </div>

            {{-- KPI 3: Item Bahan Terhubung --}}
            <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Bahan Baku Terhubung</span>
                    <div class="w-7 h-7 rounded-[8px] bg-[#FF9500]/10 flex items-center justify-center text-[#FF9500]">
                        <i data-lucide="boxes" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl font-black tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">{{ $activeMaterialsCount }}</span>
                    <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">Item Pasokan</span>
                </div>
            </div>

            {{-- KPI 4: Shortcut Buat PO Baru --}}
            <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Pengadaan Barang</span>
                    <div class="w-7 h-7 rounded-[8px] bg-[#5856D6]/10 flex items-center justify-center text-[#5856D6]">
                        <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between">
                    <a href="{{ route('purchase-orders.create', ['type' => 'supplier']) }}"
                        class="h-8 px-3 rounded-[8px] text-xs font-bold text-[#5856D6] dark:text-[#5E5CE6] bg-[#5856D6]/10 hover:bg-[#5856D6]/15 active:scale-[0.97] transition-all flex items-center gap-1.5">
                        <span>+ Buat PO Supplier</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- 4. SEARCH & FILTER TOOLBAR                            --}}
        {{-- ===================================================== --}}
        <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-3 sm:p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 shadow-xs">
            {{-- Search Form --}}
            <form method="GET" action="{{ route('suppliers.index') }}" class="flex-1 flex items-center gap-2 max-w-lg">
                <div class="relative flex-1">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nama pemasok, kontak PIC, telepon, email..."
                        class="w-full h-9 bg-slate-50 dark:bg-[#2C2C2E] border border-black/[0.06] dark:border-white/[0.08] rounded-[10px] pl-9 pr-3 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                </div>
                <button type="submit"
                    class="h-9 px-3.5 rounded-[10px] text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 active:scale-[0.98] transition-all cursor-pointer">
                    Cari
                </button>
                @if (request('search'))
                    <a href="{{ route('suppliers.index') }}"
                        class="h-9 px-2.5 rounded-[8px] text-xs font-semibold text-[#FF3B30] hover:bg-[#FF3B30]/10 flex items-center transition">
                        Reset
                    </a>
                @endif
            </form>

            <div class="text-xs text-slate-500 dark:text-slate-400 font-normal self-start sm:self-auto">
                Menampilkan <span class="font-bold text-slate-900 dark:text-white tabular-nums">{{ $suppliers->count() }}</span> dari
                <span class="font-bold text-slate-900 dark:text-white tabular-nums">{{ $suppliers->total() }}</span> pemasok
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- 5. SUPPLIERS DATA TABLE (DESKTOP)                     --}}
        {{-- ===================================================== --}}
        <div class="hidden sm:block rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-black/[0.06] dark:border-white/[0.08] text-[11px] font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500 bg-slate-50/50 dark:bg-[#2C2C2E]/50">
                            <th class="px-4 py-3">Nama Pemasok / Vendor</th>
                            <th class="px-4 py-3">Kontak PIC</th>
                            <th class="px-4 py-3">Telepon &amp; WhatsApp</th>
                            <th class="px-4 py-3">Rekening Bank</th>
                            <th class="px-4 py-3">Email &amp; Alamat</th>
                            <th class="px-4 py-3 text-center">Bahan Baku</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($suppliers as $supplier)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                {{-- Nama & Catatan --}}
                                <td class="px-4 py-3.5">
                                    <div class="font-bold text-slate-900 dark:text-white">
                                        {{ $supplier->name }}
                                    </div>
                                    @if ($supplier->notes)
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate max-w-xs mt-0.5"
                                            title="{{ $supplier->notes }}">
                                            {{ $supplier->notes }}
                                        </div>
                                    @endif
                                </td>

                                {{-- PIC --}}
                                <td class="px-4 py-3.5 text-slate-700 dark:text-slate-300 font-medium">
                                    {{ $supplier->contact_person ?: '-' }}
                                </td>

                                {{-- Phone / WA --}}
                                <td class="px-4 py-3.5 font-mono tabular-nums">
                                    @if ($supplier->phone)
                                        @php
                                            $waNumber = preg_replace('/[^0-9]/', '', $supplier->phone);
                                            if (str_starts_with($waNumber, '0')) {
                                                $waNumber = '62' . substr($waNumber, 1);
                                            }
                                        @endphp
                                        <div class="flex items-center gap-2">
                                            <a href="tel:{{ $supplier->phone }}"
                                                class="hover:text-[#007AFF] transition inline-flex items-center gap-1 text-slate-800 dark:text-slate-200 font-medium">
                                                <span>{{ $supplier->phone }}</span>
                                            </a>
                                            <a href="https://wa.me/{{ $waNumber }}" target="_blank"
                                                rel="noopener noreferrer"
                                                class="h-6 px-2 rounded-[6px] bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] hover:bg-[#34C759]/20 transition flex items-center justify-center gap-1 text-[11px] font-bold"
                                                title="Chat WhatsApp Pemasok">
                                                <i data-lucide="message-circle" class="w-3 h-3"></i>
                                                <span>WA</span>
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-600">-</span>
                                    @endif
                                </td>

                                {{-- Rekening Bank --}}
                                <td class="px-4 py-3.5">
                                    @if ($supplier->bank_name || $supplier->bank_account_number)
                                        <div class="font-medium text-slate-800 dark:text-slate-200">
                                            <span class="font-bold text-slate-900 dark:text-white">{{ $supplier->bank_name ?? 'Bank' }}</span>:
                                            <span class="font-mono tabular-nums">{{ $supplier->bank_account_number ?? '-' }}</span>
                                        </div>
                                        @if ($supplier->bank_account_holder)
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                                a.n {{ $supplier->bank_account_holder }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-slate-400 dark:text-slate-600">-</span>
                                    @endif
                                </td>

                                {{-- Email & Alamat --}}
                                <td class="px-4 py-3.5">
                                    @if ($supplier->email)
                                        <div class="text-[11px]">
                                            <a href="mailto:{{ $supplier->email }}" class="text-[#007AFF] hover:underline font-medium">{{ $supplier->email }}</a>
                                        </div>
                                    @endif
                                    @if ($supplier->address)
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 max-w-xs truncate mt-0.5" title="{{ $supplier->address }}">
                                            {{ $supplier->address }}
                                        </div>
                                    @endif
                                    @if (!$supplier->email && !$supplier->address)
                                        <span class="text-slate-400 dark:text-slate-600">-</span>
                                    @endif
                                </td>

                                {{-- Bahan Baku Count Badge --}}
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold tabular-nums {{ $supplier->materials_count > 0 ? 'bg-[#007AFF]/12 text-[#007AFF]' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                                        {{ $supplier->materials_count }} item
                                    </span>
                                </td>

                                {{-- Action Buttons --}}
                                <td class="px-4 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if (\App\Support\Context::hasPermission('master_data.suppliers.manage'))
                                            <button type="button"
                                                @click="openEditModal({
                                                    id: '{{ $supplier->id }}',
                                                    name: '{{ addslashes($supplier->name) }}',
                                                    contact_person: '{{ addslashes($supplier->contact_person ?? '') }}',
                                                    phone: '{{ addslashes($supplier->phone ?? '') }}',
                                                    email: '{{ addslashes($supplier->email ?? '') }}',
                                                    bank_name: '{{ addslashes($supplier->bank_name ?? '') }}',
                                                    bank_account_number: '{{ addslashes($supplier->bank_account_number ?? '') }}',
                                                    bank_account_holder: '{{ addslashes($supplier->bank_account_holder ?? '') }}',
                                                    address: '{{ addslashes($supplier->address ?? '') }}',
                                                    notes: '{{ addslashes($supplier->notes ?? '') }}'
                                                })"
                                                class="h-7 px-2.5 rounded-[6px] text-xs font-semibold text-[#007AFF] hover:bg-[#007AFF]/10 transition-colors flex items-center gap-1 cursor-pointer">
                                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                                <span>Edit</span>
                                            </button>
                                            <button type="button"
                                                @click="openDelete({{ $supplier->id }}, '{{ addslashes($supplier->name) }}')"
                                                class="h-7 px-2.5 rounded-[6px] text-xs font-semibold text-[#FF3B30] hover:bg-[#FF3B30]/10 transition-colors flex items-center gap-1 cursor-pointer">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                <span>Hapus</span>
                                            </button>
                                            <form id="form-delete-supplier-{{ $supplier->id }}" method="POST"
                                                action="{{ route('suppliers.destroy', $supplier->id) }}" class="hidden">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-500 dark:text-slate-400">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-2.5 text-slate-400 dark:text-slate-500">
                                        <i data-lucide="truck" class="w-6 h-6"></i>
                                    </div>
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">Belum Ada Data Pemasok</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                                        Tambahkan pemasok untuk mengelola kontak vendor, rekening bank, riwayat PO, dan pengadaan bahan baku.
                                    </p>
                                    @if (\App\Support\Context::hasPermission('master_data.suppliers.manage'))
                                        <button type="button" @click="showAddModal = true"
                                            class="mt-4 h-9 px-4 rounded-[10px] text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all inline-flex items-center gap-1.5 cursor-pointer">
                                            <i data-lucide="plus" class="w-4 h-4"></i>
                                            <span>Tambah Pemasok Pertama</span>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($suppliers->hasPages())
                <div class="px-4 py-3 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between text-xs text-slate-600 dark:text-slate-400">
                    {{ $suppliers->links() }}
                </div>
            @endif
        </div>

        {{-- ===================================================== --}}
        {{-- SUPPLIERS CARD LIST (MOBILE ONLY)                     --}}
        {{-- ===================================================== --}}
        <div class="sm:hidden space-y-3">
            <div class="flex items-center justify-between px-1">
                <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Daftar Pemasok</h2>
                <span class="text-xs text-slate-500 dark:text-slate-400 tabular-nums">{{ $suppliers->total() }} Pemasok</span>
            </div>

            <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06] shadow-xs">
                @forelse($suppliers as $supplier)
                    @php
                        $waNumber = null;
                        if ($supplier->phone) {
                            $cleanPhone = preg_replace('/[^0-9]/', '', $supplier->phone);
                            $waNumber = str_starts_with($cleanPhone, '0') ? '62' . substr($cleanPhone, 1) : $cleanPhone;
                        }
                    @endphp
                    <div class="p-4 space-y-3 active:bg-black/[0.02] dark:active:bg-white/[0.02] transition-colors">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ $supplier->name }}</h3>
                                @if ($supplier->contact_person)
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">PIC: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $supplier->contact_person }}</span></p>
                                @endif
                            </div>
                            <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold tabular-nums {{ $supplier->materials_count > 0 ? 'bg-[#007AFF]/12 text-[#007AFF]' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                                {{ $supplier->materials_count }} item
                            </span>
                        </div>

                        {{-- Bank Details Badge --}}
                        @if ($supplier->bank_name || $supplier->bank_account_number)
                            <div class="p-2.5 rounded-[10px] bg-slate-50 dark:bg-[#2C2C2E] text-xs space-y-0.5 border border-black/[0.04] dark:border-white/[0.04]">
                                <div class="font-medium text-slate-800 dark:text-slate-200 flex items-center justify-between">
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $supplier->bank_name ?? 'Rekening Bank' }}</span>
                                    <span class="font-mono tabular-nums font-semibold">{{ $supplier->bank_account_number ?? '-' }}</span>
                                </div>
                                @if ($supplier->bank_account_holder)
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400">a.n {{ $supplier->bank_account_holder }}</div>
                                @endif
                            </div>
                        @endif

                        @if ($supplier->notes)
                            <p class="text-xs text-slate-500 dark:text-slate-400 bg-black/[0.02] dark:bg-white/[0.02] p-2.5 rounded-[10px] leading-relaxed">
                                {{ $supplier->notes }}
                            </p>
                        @endif

                        <div class="space-y-1.5 text-xs text-slate-600 dark:text-slate-300">
                            @if ($supplier->phone)
                                <div class="flex items-center justify-between gap-2 pt-0.5">
                                    <a href="tel:{{ $supplier->phone }}" class="text-slate-800 dark:text-slate-200 font-mono tabular-nums flex items-center gap-1.5 hover:text-[#007AFF]">
                                        <i data-lucide="phone" class="w-3.5 h-3.5 text-slate-400"></i>
                                        <span>{{ $supplier->phone }}</span>
                                    </a>
                                    @if ($waNumber)
                                        <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener noreferrer"
                                            class="h-7 px-2.5 rounded-[8px] bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] hover:bg-[#34C759]/20 transition flex items-center gap-1 text-xs font-bold">
                                            <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                            <span>Chat WA</span>
                                        </a>
                                    @endif
                                </div>
                            @endif

                            @if ($supplier->email)
                                <div class="flex items-center gap-1.5">
                                    <i data-lucide="mail" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                    <a href="mailto:{{ $supplier->email }}" class="text-[#007AFF] hover:underline truncate">{{ $supplier->email }}</a>
                                </div>
                            @endif

                            @if ($supplier->address)
                                <div class="flex items-start gap-1.5 text-slate-500 dark:text-slate-400">
                                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400 shrink-0 mt-0.5"></i>
                                    <span class="truncate">{{ $supplier->address }}</span>
                                </div>
                            @endif
                        </div>

                        @if (\App\Support\Context::hasPermission('master_data.suppliers.manage'))
                            <div class="flex items-center justify-end gap-2 pt-2 border-t border-black/[0.04] dark:border-white/[0.06]">
                                <button type="button"
                                    @click="openEditModal({
                                        id: '{{ $supplier->id }}',
                                        name: '{{ addslashes($supplier->name) }}',
                                        contact_person: '{{ addslashes($supplier->contact_person ?? '') }}',
                                        phone: '{{ addslashes($supplier->phone ?? '') }}',
                                        email: '{{ addslashes($supplier->email ?? '') }}',
                                        bank_name: '{{ addslashes($supplier->bank_name ?? '') }}',
                                        bank_account_number: '{{ addslashes($supplier->bank_account_number ?? '') }}',
                                        bank_account_holder: '{{ addslashes($supplier->bank_account_holder ?? '') }}',
                                        address: '{{ addslashes($supplier->address ?? '') }}',
                                        notes: '{{ addslashes($supplier->notes ?? '') }}'
                                    })"
                                    class="h-8 px-3 rounded-[8px] text-xs font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition flex items-center gap-1.5 cursor-pointer">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    <span>Edit</span>
                                </button>
                                <button type="button"
                                    @click="openDelete({{ $supplier->id }}, '{{ addslashes($supplier->name) }}')"
                                    class="h-8 px-3 rounded-[8px] text-xs font-semibold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 transition flex items-center gap-1.5 cursor-pointer">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    <span>Hapus</span>
                                </button>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="py-10 px-4 text-center text-slate-500 dark:text-slate-400">
                        <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-2 text-slate-400">
                            <i data-lucide="truck" class="w-5 h-5"></i>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">Belum Ada Data Pemasok</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-xs mx-auto">
                            Tambahkan pemasok untuk mengelola kontak vendor dan pengadaan bahan baku.
                        </p>
                    </div>
                @endforelse
            </div>

            @if ($suppliers->hasPages())
                <div class="p-3 bg-white dark:bg-[#1C1C1E] rounded-[14px] border border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between text-xs">
                    {{ $suppliers->links() }}
                </div>
            @endif
        </div>

        {{-- ===================================================== --}}
        {{-- 6. APPLE SHEET: TAMBAH PEMASOK                        --}}
        {{-- ===================================================== --}}
        <div x-show="showAddModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="w-full max-w-lg rounded-[22px] bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] shadow-2xl p-6 space-y-5 max-h-[90vh] overflow-y-auto"
                @click.outside="showAddModal = false" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95">

                <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3.5">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Tambah Pemasok Baru</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Daftarkan mitra vendor penyedia bahan baku dan rekening pembayaran</p>
                    </div>
                    <button type="button" @click="showAddModal = false"
                        class="p-1 rounded-[8px] text-slate-400 hover:text-slate-900 dark:hover:text-white transition cursor-pointer">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('suppliers.store') }}" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Nama Pemasok / Vendor <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="text" name="name" required
                            placeholder="Contoh: PT Sumber Pangan Sejahtera, CV Mitra Tani..."
                            class="w-full h-10 bg-slate-50 dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Nama Kontak (PIC)
                            </label>
                            <input type="text" name="contact_person" placeholder="Mis: Budi Santoso"
                                class="w-full h-10 bg-slate-50 dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                No. Telepon / WhatsApp
                            </label>
                            <input type="text" name="phone" placeholder="0812-3456-7890"
                                class="w-full h-10 bg-slate-50 dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Alamat Email
                        </label>
                        <input type="email" name="email" placeholder="sales@sumberpangan.com"
                            class="w-full h-10 bg-slate-50 dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                    </div>

                    {{-- Informasi Rekening Bank --}}
                    <div class="p-3.5 rounded-[12px] bg-slate-50 dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-slate-800 dark:text-slate-200">
                            <i data-lucide="credit-card" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                            <span>Rekening Pembayaran Bank (Transfer Vendor)</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1">Bank</label>
                                <input type="text" name="bank_name" placeholder="BCA / Mandiri / BRI"
                                    class="w-full h-9 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] px-2.5 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1">No. Rekening</label>
                                <input type="text" name="bank_account_number" placeholder="1234567890"
                                    class="w-full h-9 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] px-2.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1">Atas Nama (A.N)</label>
                                <input type="text" name="bank_account_holder" placeholder="PT Sumber Pangan"
                                    class="w-full h-9 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] px-2.5 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Alamat Lengkap
                        </label>
                        <textarea name="address" rows="2" placeholder="Jl. Pergudangan No. 12, Kawasan Industri..."
                            class="w-full bg-slate-50 dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] p-3 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition resize-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Catatan Khusus (Term of Payment / Ketentuan Order)
                        </label>
                        <input type="text" name="notes" placeholder="Term pembayaran 14 hari, minimum order 50kg..."
                            class="w-full h-10 bg-slate-50 dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3.5 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <button type="button" @click="showAddModal = false"
                            class="h-9 px-4 rounded-[10px] text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                            class="h-9 px-4 rounded-[10px] text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all shadow-[0_1px_2px_rgba(0,122,255,0.25)] cursor-pointer">
                            Simpan Pemasok
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- 7. APPLE SHEET: EDIT PEMASOK                          --}}
        {{-- ===================================================== --}}
        <div x-show="showEditModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="w-full max-w-lg rounded-[22px] bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] shadow-2xl p-6 space-y-5 max-h-[90vh] overflow-y-auto"
                @click.outside="showEditModal = false" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95">

                <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3.5">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Edit Data Pemasok</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400" x-text="'Memperbarui: ' + editSupplier.name"></p>
                    </div>
                    <button type="button" @click="showEditModal = false"
                        class="p-1 rounded-[8px] text-slate-400 hover:text-slate-900 dark:hover:text-white transition cursor-pointer">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form :action="'{{ url('/suppliers') }}/' + editSupplier.id" method="POST" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Nama Pemasok / Vendor <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="text" name="name" x-model="editSupplier.name" required
                            class="w-full h-10 bg-slate-50 dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Nama Kontak (PIC)
                            </label>
                            <input type="text" name="contact_person" x-model="editSupplier.contact_person"
                                class="w-full h-10 bg-slate-50 dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                No. Telepon / WhatsApp
                            </label>
                            <input type="text" name="phone" x-model="editSupplier.phone"
                                class="w-full h-10 bg-slate-50 dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Alamat Email
                        </label>
                        <input type="email" name="email" x-model="editSupplier.email"
                            class="w-full h-10 bg-slate-50 dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                    </div>

                    {{-- Informasi Rekening Bank --}}
                    <div class="p-3.5 rounded-[12px] bg-slate-50 dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-slate-800 dark:text-slate-200">
                            <i data-lucide="credit-card" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                            <span>Rekening Pembayaran Bank (Transfer Vendor)</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1">Bank</label>
                                <input type="text" name="bank_name" x-model="editSupplier.bank_name" placeholder="BCA / Mandiri / BRI"
                                    class="w-full h-9 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] px-2.5 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1">No. Rekening</label>
                                <input type="text" name="bank_account_number" x-model="editSupplier.bank_account_number" placeholder="1234567890"
                                    class="w-full h-9 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] px-2.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1">Atas Nama (A.N)</label>
                                <input type="text" name="bank_account_holder" x-model="editSupplier.bank_account_holder" placeholder="PT Sumber Pangan"
                                    class="w-full h-9 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] px-2.5 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Alamat Lengkap
                        </label>
                        <textarea name="address" rows="2" x-model="editSupplier.address"
                            class="w-full bg-slate-50 dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] p-3 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition resize-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Catatan Khusus
                        </label>
                        <input type="text" name="notes" x-model="editSupplier.notes"
                            class="w-full h-10 bg-slate-50 dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
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
        {{-- 8. APPLE ALERT DIALOG (Centered Confirmation)         --}}
        {{-- ===================================================== --}}
        <div x-show="deleteModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="w-[300px] rounded-[18px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-2xl border border-black/[0.08] dark:border-white/[0.12]"
                @click.away="closeDelete()" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95">

                <div class="px-5 pt-5 pb-4">
                    <div class="w-10 h-10 rounded-full bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    </div>
                    <p class="text-base font-bold text-slate-900 dark:text-white">Hapus Pemasok?</p>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 leading-snug">
                        <span x-text="deleteTarget.name" class="font-semibold text-slate-900 dark:text-white"></span> akan dihapus dari sistem. Pastikan tidak ada PO aktif terkait.
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
