@extends('layouts.app', [
    'title' => __('purchasing.supplier.title'),
    'headerTitle' => __('purchasing.supplier.header_title'),
    'headerSubtitle' => __('purchasing.supplier.header_subtitle'),
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
            :title="__('purchasing.supplier.header_title')"
            :subtitle="__('purchasing.supplier.header_subtitle')">
            @if ($business->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_RECIPE_BOM) && \App\Support\Context::hasPermission('inventory.view'))
                <a href="{{ route('materials.index') }}"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5">
                    <i data-lucide="boxes" class="w-4 h-4 text-black/50 dark:text-white/50"></i>
                    <span>{{ __('purchasing.supplier.actions.view_materials') }}</span>
                </a>
            @endif

            <a href="{{ route('purchase-orders.index') }}"
                class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="file-text" class="w-4 h-4 text-black/50 dark:text-white/50"></i>
                <span>{{ __('purchasing.supplier.actions.view_pos') }}</span>
            </a>

            @if (\App\Support\Context::hasPermission('master_data.suppliers.manage') || \App\Support\Context::isAdminOrOwner())
                <button type="button" @click="showAddModal = true"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>{{ __('purchasing.supplier.actions.add_supplier') }}</span>
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
        @if (session('warning'))
            <div class="rounded-[14px] bg-[#FF9500]/10 border border-[#FF9500]/20 px-4 py-3 text-xs text-[#B25E00] dark:text-[#FF9F0A] flex items-center gap-2.5">
                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 text-[#FF9500]"></i>
                <span class="font-medium">{{ session('warning') }}</span>
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
        <div class="flex sm:grid sm:grid-cols-2 lg:grid-cols-4 flex-nowrap sm:flex-wrap overflow-x-auto sm:overflow-visible pb-2 sm:pb-0 snap-x snap-mandatory gap-3 sm:gap-4 no-scrollbar scrollbar-none">
            {{-- KPI 1: Total Pemasok --}}
            <div class="min-w-[220px] sm:min-w-0 flex-1 snap-start rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('purchasing.supplier.kpis.total_suppliers') }}</span>
                    <div class="w-7 h-7 rounded-[8px] bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400">
                        <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl font-black tabular-nums text-slate-900 dark:text-white">{{ $suppliers->total() }}</span>
                    <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">{{ __('purchasing.supplier.kpis.registered_partners') }}</span>
                </div>
            </div>

            {{-- KPI 2: Pemasok Pasokan Aktif --}}
            <div class="min-w-[220px] sm:min-w-0 flex-1 snap-start rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('purchasing.supplier.kpis.active_suppliers') }}</span>
                    <div class="w-7 h-7 rounded-[8px] bg-[#007AFF]/10 flex items-center justify-center text-[#007AFF]">
                        <i data-lucide="package-check" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl font-black tabular-nums text-[#007AFF] dark:text-[#0A84FF]">{{ $suppliedVendorsCount }}</span>
                    <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">{{ __('purchasing.supplier.kpis.has_materials') }}</span>
                </div>
            </div>

            {{-- KPI 3: Item Bahan Terhubung --}}
            <div class="min-w-[220px] sm:min-w-0 flex-1 snap-start rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('purchasing.supplier.kpis.linked_materials') }}</span>
                    <div class="w-7 h-7 rounded-[8px] bg-[#FF9500]/10 flex items-center justify-center text-[#FF9500]">
                        <i data-lucide="boxes" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl font-black tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">{{ $activeMaterialsCount }}</span>
                    <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">{{ __('purchasing.supplier.kpis.supply_items') }}</span>
                </div>
            </div>

            {{-- KPI 4: Shortcut Buat PO Baru --}}
            <div class="min-w-[220px] sm:min-w-0 flex-1 snap-start rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('purchasing.supplier.kpis.procurement') }}</span>
                    <div class="w-7 h-7 rounded-[8px] bg-[#5856D6]/10 flex items-center justify-center text-[#5856D6]">
                        <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between">
                    <a href="{{ route('purchase-orders.create', ['type' => 'supplier']) }}"
                        class="min-h-[44px] sm:min-h-0 h-9 sm:h-8 px-3 rounded-[8px] text-xs font-bold text-[#5856D6] dark:text-[#5E5CE6] bg-[#5856D6]/10 hover:bg-[#5856D6]/15 active:scale-[0.97] transition-all flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>{{ __('purchasing.supplier.actions.create_po') }}</span>
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
                        placeholder="{{ __('purchasing.supplier.placeholders.search') }}"
                        class="w-full h-10 sm:h-9 bg-slate-50 dark:bg-[#2C2C2E] border border-black/[0.06] dark:border-white/[0.08] rounded-[10px] pl-9 pr-3 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                </div>
                <button type="submit"
                    class="min-h-[44px] sm:min-h-0 h-10 sm:h-9 px-4 sm:px-3.5 rounded-[10px] text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 active:scale-[0.98] transition-all cursor-pointer">
                    {{ __('purchasing.supplier.actions.search') }}
                </button>
                @if (request('search'))
                    <a href="{{ route('suppliers.index') }}"
                        class="min-h-[44px] sm:min-h-0 h-10 sm:h-9 px-3 sm:px-2.5 rounded-[8px] text-xs font-semibold text-[#FF3B30] hover:bg-[#FF3B30]/10 flex items-center transition cursor-pointer">
                        {{ __('purchasing.supplier.actions.reset') }}
                    </a>
                @endif
            </form>

            <div class="text-xs text-slate-500 dark:text-slate-400 font-normal self-start sm:self-auto">
                {{ __('purchasing.supplier.pagination.showing') }} <span class="font-bold text-slate-900 dark:text-white tabular-nums">{{ $suppliers->count() }}</span> {{ __('purchasing.supplier.pagination.of') }}
                <span class="font-bold text-slate-900 dark:text-white tabular-nums">{{ $suppliers->total() }}</span> {{ __('purchasing.supplier.pagination.suppliers') }}
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
                            <th class="px-4 py-3">{{ __('purchasing.supplier.table.supplier_name') }}</th>
                            <th class="px-4 py-3">{{ __('purchasing.supplier.table.contact_pic') }}</th>
                            <th class="px-4 py-3">{{ __('purchasing.supplier.table.phone_wa') }}</th>
                            <th class="px-4 py-3">{{ __('purchasing.supplier.table.bank_account') }}</th>
                            <th class="px-4 py-3">{{ __('purchasing.supplier.table.email_address') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('purchasing.supplier.table.raw_materials') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('purchasing.supplier.table.action') }}</th>
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
                                                class="min-h-[44px] sm:min-h-0 h-6 px-2 rounded-[6px] bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] hover:bg-[#34C759]/20 transition flex items-center justify-center gap-1 text-[11px] font-bold cursor-pointer"
                                                title="{{ __('purchasing.supplier.badges.chat_wa_title') }}">
                                                <i data-lucide="message-circle" class="w-3 h-3"></i>
                                                <span>{{ __('purchasing.supplier.badges.chat_wa') }}</span>
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
                                            <span class="font-bold text-slate-900 dark:text-white">{{ $supplier->bank_name ?? __('purchasing.supplier.fields.bank_name') }}</span>:
                                            <span class="font-mono tabular-nums">{{ $supplier->bank_account_number ?? '-' }}</span>
                                        </div>
                                        @if ($supplier->bank_account_holder)
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                                {{ __('purchasing.supplier.badges.account_holder_prefix') }} {{ $supplier->bank_account_holder }}
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
                                        {{ __('purchasing.supplier.badges.items_count', ['count' => $supplier->materials_count]) }}
                                    </span>
                                </td>

                                {{-- Action Buttons --}}
                                <td class="px-4 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if (\App\Support\Context::hasPermission('master_data.suppliers.manage') || \App\Support\Context::isAdminOrOwner())
                                            <button type="button"
                                                @click="openEditModal(@js($supplier))"
                                                class="min-h-[44px] sm:min-h-0 h-8 px-3 rounded-[8px] text-xs font-semibold text-[#007AFF] hover:bg-[#007AFF]/10 transition-colors flex items-center gap-1 cursor-pointer">
                                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                                <span>{{ __('purchasing.supplier.actions.edit_supplier') }}</span>
                                            </button>
                                            <button type="button"
                                                @click="openDelete(@js($supplier->id), @js($supplier->name))"
                                                class="min-h-[44px] sm:min-h-0 h-8 px-3 rounded-[8px] text-xs font-semibold text-[#FF3B30] hover:bg-[#FF3B30]/10 transition-colors flex items-center gap-1 cursor-pointer">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                <span>{{ __('purchasing.supplier.actions.delete_supplier') }}</span>
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
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('purchasing.supplier.empty.title') }}</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                                        {{ __('purchasing.supplier.empty.subtitle') }}
                                    </p>
                                    @if (\App\Support\Context::hasPermission('master_data.suppliers.manage') || \App\Support\Context::isAdminOrOwner())
                                        <button type="button" @click="showAddModal = true"
                                            class="mt-4 min-h-[44px] h-11 sm:h-9 px-4 rounded-[10px] text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all inline-flex items-center gap-1.5 cursor-pointer shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                                            <i data-lucide="plus" class="w-4 h-4"></i>
                                            <span>{{ __('purchasing.supplier.empty.button') }}</span>
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
                <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">{{ __('purchasing.supplier.table.mobile_list_title') }}</h2>
                <span class="text-xs text-slate-500 dark:text-slate-400 tabular-nums">{{ __('purchasing.supplier.table.mobile_suppliers_count', ['total' => $suppliers->total()]) }}</span>
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
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('purchasing.supplier.table.mobile_pic_label') }} <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $supplier->contact_person }}</span></p>
                                @endif
                            </div>
                            <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold tabular-nums {{ $supplier->materials_count > 0 ? 'bg-[#007AFF]/12 text-[#007AFF]' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                                {{ __('purchasing.supplier.badges.items_count', ['count' => $supplier->materials_count]) }}
                            </span>
                        </div>

                        {{-- Bank Details Badge --}}
                        @if ($supplier->bank_name || $supplier->bank_account_number)
                            <div class="p-2.5 rounded-[10px] bg-slate-50 dark:bg-[#2C2C2E] text-xs space-y-0.5 border border-black/[0.04] dark:border-white/[0.04]">
                                <div class="font-medium text-slate-800 dark:text-slate-200 flex items-center justify-between">
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $supplier->bank_name ?? __('purchasing.supplier.table.mobile_bank_default') }}</span>
                                    <span class="font-mono tabular-nums font-semibold">{{ $supplier->bank_account_number ?? '-' }}</span>
                                </div>
                                @if ($supplier->bank_account_holder)
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('purchasing.supplier.badges.account_holder_prefix') }} {{ $supplier->bank_account_holder }}</div>
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
                                            class="min-h-[44px] h-10 px-3 rounded-[8px] bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] hover:bg-[#34C759]/20 transition flex items-center gap-1 text-xs font-bold cursor-pointer">
                                            <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('purchasing.supplier.badges.chat_wa') }}</span>
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

                        @if (\App\Support\Context::hasPermission('master_data.suppliers.manage') || \App\Support\Context::isAdminOrOwner())
                            <div class="flex items-center justify-end gap-2 pt-2 border-t border-black/[0.04] dark:border-white/[0.06]">
                                <button type="button"
                                    @click="openEditModal(@js($supplier))"
                                    class="min-h-[44px] h-11 px-3.5 rounded-[8px] text-xs font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition flex items-center gap-1.5 cursor-pointer">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    <span>{{ __('purchasing.supplier.actions.edit_supplier') }}</span>
                                </button>
                                <button type="button"
                                    @click="openDelete(@js($supplier->id), @js($supplier->name))"
                                    class="min-h-[44px] h-11 px-3.5 rounded-[8px] text-xs font-semibold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 transition flex items-center gap-1.5 cursor-pointer">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    <span>{{ __('purchasing.supplier.actions.delete_supplier') }}</span>
                                </button>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="py-10 px-4 text-center text-slate-500 dark:text-slate-400">
                        <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-2 text-slate-400">
                            <i data-lucide="truck" class="w-5 h-5"></i>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('purchasing.supplier.empty.title') }}</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-xs mx-auto">
                            {{ __('purchasing.supplier.empty.subtitle') }}
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
        {{-- 6. APPLE BENTO XXL SHEET: TAMBAH PEMASOK              --}}
        {{-- ===================================================== --}}
        <div x-show="showAddModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-md p-3 sm:p-6"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="w-full max-w-[94vw] md:max-w-2xl lg:max-w-3xl xl:max-w-4xl max-h-[88vh] rounded-[22px] bg-white/98 dark:bg-[#1C1C1E]/98 backdrop-blur-2xl border border-black/[0.08] dark:border-white/[0.12] shadow-[0_25px_60px_rgba(0,0,0,0.35)] flex flex-col overflow-hidden"
                @click.outside="showAddModal = false" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95">

                {{-- Modal Header --}}
                <div class="px-5 py-4 sm:px-6 sm:py-4.5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-3 shrink-0 bg-slate-50/50 dark:bg-white/[0.02]">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-[14px] bg-[#007AFF]/12 text-[#007AFF] flex items-center justify-center shrink-0 border border-[#007AFF]/20">
                            <i data-lucide="truck" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white tracking-tight">
                                {{ __('purchasing.supplier.create_title') }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('purchasing.supplier.create_subtitle') }}</p>
                        </div>
                    </div>
                    <button type="button" @click="showAddModal = false"
                        class="min-w-[44px] min-h-[44px] w-11 h-11 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                {{-- Modal Form --}}
                <form method="POST" action="{{ route('suppliers.store') }}" x-data="{ submitting: false }" @submit="submitting = true" class="flex flex-col flex-1 overflow-hidden">
                    @csrf
                    {{-- Modal Body: 2-Kolom Bento Grid --}}
                    <div class="p-5 sm:p-6 overflow-y-auto space-y-4 flex-1 text-xs">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                            {{-- Kolom Kiri: Profil Pemasok & Kontak Utama (6 Kolom) --}}
                            <div class="lg:col-span-6 space-y-4">
                                <div class="rounded-[18px] bg-slate-50/80 dark:bg-[#2C2C2E]/60 border border-black/[0.06] dark:border-white/[0.08] p-5 space-y-4">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="building" class="w-4 h-4 text-[#007AFF]"></i>
                                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ __('purchasing.supplier.sections.company_info') }}</span>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                            {{ __('purchasing.supplier.fields.name') }}
                                        </label>
                                        <input type="text" name="name" required
                                            placeholder="{{ __('purchasing.supplier.placeholders.name') }}"
                                            class="w-full h-11 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                {{ __('purchasing.supplier.fields.contact_person') }}
                                            </label>
                                            <input type="text" name="contact_person" placeholder="{{ __('purchasing.supplier.placeholders.contact_person') }}"
                                                class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                {{ __('purchasing.supplier.fields.phone') }}
                                            </label>
                                            <input type="text" name="phone" placeholder="{{ __('purchasing.supplier.placeholders.phone') }}"
                                                class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            {{ __('purchasing.supplier.fields.email') }}
                                        </label>
                                        <input type="email" name="email" placeholder="{{ __('purchasing.supplier.placeholders.email') }}"
                                            class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            {{ __('purchasing.supplier.fields.address') }}
                                        </label>
                                        <textarea name="address" rows="3" placeholder="{{ __('purchasing.supplier.placeholders.address') }}"
                                            class="w-full bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] p-3 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition resize-none"></textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- Kolom Kanan: Rekening Bank & Administrasi TOP (6 Kolom) --}}
                            <div class="lg:col-span-6 space-y-4">
                                <div class="rounded-[18px] bg-slate-50/80 dark:bg-[#2C2C2E]/60 border border-black/[0.06] dark:border-white/[0.08] p-5 space-y-4">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="credit-card" class="w-4 h-4 text-[#5856D6]"></i>
                                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ __('purchasing.supplier.sections.bank_info') }}</span>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            {{ __('purchasing.supplier.fields.bank_name') }}
                                        </label>
                                        <input type="text" name="bank_name" placeholder="{{ __('purchasing.supplier.placeholders.bank_name') }}"
                                            class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#5856D6] transition">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            {{ __('purchasing.supplier.fields.bank_account_number') }}
                                        </label>
                                        <input type="text" name="bank_account_number" placeholder="{{ __('purchasing.supplier.placeholders.bank_account_number') }}"
                                            class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#5856D6] transition">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            {{ __('purchasing.supplier.fields.bank_account_holder') }}
                                        </label>
                                        <input type="text" name="bank_account_holder" placeholder="{{ __('purchasing.supplier.placeholders.bank_account_holder') }}"
                                            class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#5856D6] transition">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            {{ __('purchasing.supplier.fields.notes') }}
                                        </label>
                                        <textarea name="notes" rows="3" placeholder="{{ __('purchasing.supplier.placeholders.notes') }}"
                                            class="w-full bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] p-3 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#5856D6] transition resize-none"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="px-5 py-3.5 sm:px-6 sm:py-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-end gap-3 shrink-0 bg-slate-50/50 dark:bg-white/[0.02]">
                        <button type="button" @click="showAddModal = false"
                            class="min-h-[44px] h-11 px-5 rounded-[12px] text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                            {{ __('common.cancel') }}
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
                            <span x-text="submitting ? '{{ __('purchasing.supplier.actions.submitting') }}' : '{{ __('purchasing.supplier.actions.save') }}'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- 7. APPLE BENTO XXL SHEET: EDIT PEMASOK                --}}
        {{-- ===================================================== --}}
        <div x-show="showEditModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-md p-3 sm:p-6"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="w-full max-w-[94vw] md:max-w-2xl lg:max-w-3xl xl:max-w-4xl max-h-[88vh] rounded-[22px] bg-white/98 dark:bg-[#1C1C1E]/98 backdrop-blur-2xl border border-black/[0.08] dark:border-white/[0.12] shadow-[0_25px_60px_rgba(0,0,0,0.35)] flex flex-col overflow-hidden"
                @click.outside="showEditModal = false" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95">

                {{-- Modal Header --}}
                <div class="px-5 py-4 sm:px-6 sm:py-4.5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-3 shrink-0 bg-slate-50/50 dark:bg-white/[0.02]">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-[14px] bg-[#007AFF]/12 text-[#007AFF] flex items-center justify-center shrink-0 border border-[#007AFF]/20">
                            <i data-lucide="pencil" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                                <span>{{ __('purchasing.supplier.actions.edit_supplier') }}:</span>
                                <span class="text-[#007AFF]" x-text="editSupplier.name"></span>
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400" x-text="'{{ __('purchasing.supplier.modals.edit_updating', ['name' => '']) }}' + editSupplier.name"></p>
                        </div>
                    </div>
                    <button type="button" @click="showEditModal = false"
                        class="min-w-[44px] min-h-[44px] w-11 h-11 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                {{-- Modal Form --}}
                <form :action="'{{ url('/suppliers') }}/' + editSupplier.id" method="POST" x-data="{ submitting: false }" @submit="submitting = true" class="flex flex-col flex-1 overflow-hidden">
                    @csrf
                    @method('PUT')
                    {{-- Modal Body: 2-Kolom Bento Grid --}}
                    <div class="p-5 sm:p-6 overflow-y-auto space-y-4 flex-1 text-xs">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                            {{-- Kolom Kiri: Profil Pemasok & Kontak Utama (6 Kolom) --}}
                            <div class="lg:col-span-6 space-y-4">
                                <div class="rounded-[18px] bg-slate-50/80 dark:bg-[#2C2C2E]/60 border border-black/[0.06] dark:border-white/[0.08] p-5 space-y-4">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="building" class="w-4 h-4 text-[#007AFF]"></i>
                                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ __('purchasing.supplier.sections.company_info') }}</span>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                            {{ __('purchasing.supplier.fields.name') }}
                                        </label>
                                        <input type="text" name="name" x-model="editSupplier.name" required
                                            class="w-full h-11 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                {{ __('purchasing.supplier.fields.contact_person') }}
                                            </label>
                                            <input type="text" name="contact_person" x-model="editSupplier.contact_person"
                                                class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                {{ __('purchasing.supplier.fields.phone') }}
                                            </label>
                                            <input type="text" name="phone" x-model="editSupplier.phone"
                                                class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            {{ __('purchasing.supplier.fields.email') }}
                                        </label>
                                        <input type="email" name="email" x-model="editSupplier.email"
                                            class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            {{ __('purchasing.supplier.fields.address') }}
                                        </label>
                                        <textarea name="address" rows="3" x-model="editSupplier.address"
                                            class="w-full bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] p-3 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition resize-none"></textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- Kolom Kanan: Rekening Bank & Administrasi TOP (6 Kolom) --}}
                            <div class="lg:col-span-6 space-y-4">
                                <div class="rounded-[18px] bg-slate-50/80 dark:bg-[#2C2C2E]/60 border border-black/[0.06] dark:border-white/[0.08] p-5 space-y-4">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="credit-card" class="w-4 h-4 text-[#5856D6]"></i>
                                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ __('purchasing.supplier.sections.bank_info') }}</span>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            {{ __('purchasing.supplier.fields.bank_name') }}
                                        </label>
                                        <input type="text" name="bank_name" x-model="editSupplier.bank_name"
                                            class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6] transition">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            {{ __('purchasing.supplier.fields.bank_account_number') }}
                                        </label>
                                        <input type="text" name="bank_account_number" x-model="editSupplier.bank_account_number"
                                            class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#5856D6] transition">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            {{ __('purchasing.supplier.fields.bank_account_holder') }}
                                        </label>
                                        <input type="text" name="bank_account_holder" x-model="editSupplier.bank_account_holder"
                                            class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6] transition">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            {{ __('purchasing.supplier.fields.notes') }}
                                        </label>
                                        <textarea name="notes" rows="3" x-model="editSupplier.notes"
                                            class="w-full bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] p-3 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6] transition resize-none"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="px-5 py-3.5 sm:px-6 sm:py-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-end gap-3 shrink-0 bg-slate-50/50 dark:bg-white/[0.02]">
                        <button type="button" @click="showEditModal = false"
                            class="min-h-[44px] h-11 px-5 rounded-[12px] text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                            {{ __('common.cancel') }}
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
                            <span x-text="submitting ? '{{ __('purchasing.supplier.actions.updating') }}' : '{{ __('purchasing.supplier.actions.update') }}'"></span>
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

            <div class="w-full max-w-[340px] rounded-[20px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-2xl border border-black/[0.08] dark:border-white/[0.12]"
                @click.away="closeDelete()" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95">

                <div class="px-5 pt-6 pb-4">
                    <div class="w-12 h-12 rounded-full bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center mx-auto mb-3 border border-[#FF3B30]/20">
                        <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                    </div>
                    <p class="text-base font-bold text-slate-900 dark:text-white">{{ __('purchasing.supplier.modals.delete_title') }}</p>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1.5 leading-relaxed">
                        <span x-text="deleteTarget.name" class="font-semibold text-slate-900 dark:text-white"></span> {{ __('purchasing.supplier.modals.delete_reassurance') }}
                    </p>
                </div>

                <div class="grid grid-cols-2 border-t border-black/[0.08] dark:border-white/[0.12] text-sm font-semibold">
                    <button type="button" @click="closeDelete()"
                        class="min-h-[44px] py-3.5 text-[#007AFF] border-r border-black/[0.08] dark:border-white/[0.12] active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer flex items-center justify-center">
                        {{ __('common.cancel') }}
                    </button>
                    <button type="button" @click="submitDelete()"
                        class="min-h-[44px] py-3.5 text-[#FF3B30] font-bold active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer flex items-center justify-center">
                        {{ __('common.delete') }}
                    </button>
                </div>
            </div>
        </div>

    </div>
@endsection
