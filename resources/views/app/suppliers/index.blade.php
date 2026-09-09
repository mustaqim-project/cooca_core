@extends('layouts.app', [
    'title' => 'Pemasok & Vendor',
    'headerTitle' => 'Manajemen Pemasok (Suppliers)',
    'headerSubtitle' => 'Kelola direktori pemasok, kontak PIC, dan pengadaan bahan baku'
])

@section('content')
<div class="space-y-6" x-data="{
    showAddModal: false,
    showEditModal: false,
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
    }
}">

    {{-- ===== SUB-NAVIGATION TABS ===== --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-slate-200/80 dark:border-slate-800/80 scrollbar-none">
        <a href="{{ route('suppliers.index') }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 shadow-xs">
            <i data-lucide="truck" class="w-4 h-4 text-emerald-500"></i>
            <span>Daftar Pemasok</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] font-mono font-bold bg-emerald-500/20 text-emerald-700 dark:text-emerald-300">{{ $suppliers->total() }}</span>
        </a>
        <a href="{{ route('materials.index') }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all whitespace-nowrap text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60">
            <i data-lucide="boxes" class="w-4 h-4 text-slate-400"></i>
            <span>Katalog Bahan Baku</span>
        </a>
        <a href="{{ route('purchase-orders.index') }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all whitespace-nowrap text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60">
            <i data-lucide="clipboard-list" class="w-4 h-4 text-slate-400"></i>
            <span>Purchase Order (PO)</span>
        </a>
        <a href="{{ route('purchasing.bills.index') }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all whitespace-nowrap text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60">
            <i data-lucide="receipt" class="w-4 h-4 text-slate-400"></i>
            <span>Tagihan Pembelian (Bills)</span>
        </a>
    </div>

    {{-- ===== TOP ACTION TOOLBAR ===== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                    Procurement & Supply Chain
                </span>
                <span class="text-xs text-slate-400">·</span>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Manajemen data mitra vendor & pengadaan bahan baku</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">
                Direktori Pemasok & Vendor
            </h1>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('materials.index') }}"
               class="px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-200/90 dark:border-slate-800 shadow-2xs transition inline-flex items-center gap-1.5">
                <i data-lucide="boxes" class="w-4 h-4 text-amber-500"></i>
                <span>Katalog Bahan</span>
            </a>
            <a href="{{ route('purchase-orders.index') }}"
               class="px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-200/90 dark:border-slate-800 shadow-2xs transition inline-flex items-center gap-1.5">
                <i data-lucide="shopping-cart" class="w-4 h-4 text-cyan-500"></i>
                <span>Daftar PO</span>
            </a>
            @if(\App\Support\Context::hasPermission('master_data.suppliers.manage'))
                <button @click="showAddModal = true"
                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-sm hover:shadow transition inline-flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Tambah Pemasok</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-800 dark:text-emerald-300 text-xs sm:text-sm font-medium flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-emerald-500/20 flex items-center justify-center shrink-0">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
            </div>
            <div class="flex-1">{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-800 dark:text-rose-300 text-xs sm:text-sm font-medium flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-rose-500/20 flex items-center justify-center shrink-0">
                <i data-lucide="alert-octagon" class="w-4 h-4 text-rose-600 dark:text-rose-400"></i>
            </div>
            <div class="flex-1">{{ session('error') }}</div>
        </div>
    @endif

    {{-- ===== 4 COMMAND KPI METRICS ===== --}}
    @php
        $activeMaterialsCount = $suppliers->sum('materials_count');
        $suppliedVendorsCount = $suppliers->filter(fn($s) => $s->materials_count > 0)->count();
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        {{-- KPI 1: Total Pemasok --}}
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs relative overflow-hidden group">
            <div class="flex items-center justify-between gap-2 mb-3">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Pemasok</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/15 border border-emerald-500/20 flex items-center justify-center">
                    <i data-lucide="truck" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-mono tracking-tight">{{ $suppliers->total() }}</div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Mitra vendor terdaftar</div>
        </div>

        {{-- KPI 2: Pemasok Bahan Aktif --}}
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs relative overflow-hidden group">
            <div class="flex items-center justify-between gap-2 mb-3">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pemasok Bahan Aktif</span>
                <div class="w-8 h-8 rounded-xl bg-cyan-500/10 dark:bg-cyan-500/15 border border-cyan-500/20 flex items-center justify-center">
                    <i data-lucide="link-2" class="w-4 h-4 text-cyan-600 dark:text-cyan-400"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-mono tracking-tight">
                {{ $suppliedVendorsCount }}
            </div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Memiliki bahan baku terkait</div>
        </div>

        {{-- KPI 3: Item Bahan Terhubung --}}
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs relative overflow-hidden group">
            <div class="flex items-center justify-between gap-2 mb-3">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Bahan Baku Terhubung</span>
                <div class="w-8 h-8 rounded-xl bg-amber-500/10 dark:bg-amber-500/15 border border-amber-500/20 flex items-center justify-center">
                    <i data-lucide="boxes" class="w-4 h-4 text-amber-600 dark:text-amber-400"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-mono tracking-tight">
                {{ $activeMaterialsCount }}
            </div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Item pasokan di katalog</div>
        </div>

        {{-- KPI 4: Buat PO Baru Shortcut --}}
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs relative overflow-hidden group flex flex-col justify-between">
            <div class="flex items-center justify-between gap-2 mb-2">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pengadaan PO</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/15 border border-indigo-500/20 flex items-center justify-center">
                    <i data-lucide="clipboard-plus" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                </div>
            </div>
            <a href="{{ route('purchase-orders.create', ['type' => 'supplier']) }}"
               class="mt-1 py-1.5 px-3 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 font-bold text-xs border border-indigo-200 dark:border-indigo-500/20 inline-flex items-center justify-center gap-1.5 transition">
                <span>+ Buat PO Supplier</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
    </div>

    {{-- ===== SEARCH & FILTER TOOLBAR ===== --}}
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <form method="GET" action="{{ route('suppliers.index') }}" class="flex-1 flex items-center gap-2 max-w-lg">
            <div class="relative w-full">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama pemasok, kontak PIC, telepon, email..."
                       class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 rounded-xl text-xs text-slate-900 dark:text-white transition">
            </div>
            <button type="submit"
                    class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition">
                Cari
            </button>
            @if(request('search'))
                <a href="{{ route('suppliers.index') }}"
                   class="px-3 py-2 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition">
                    Reset
                </a>
            @endif
        </form>

        <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">
            Menampilkan <strong class="text-slate-800 dark:text-slate-200 font-mono">{{ $suppliers->count() }}</strong> dari <strong class="text-slate-800 dark:text-slate-200 font-mono">{{ $suppliers->total() }}</strong> pemasok
        </div>
    </div>

    {{-- ===== SUPPLIERS DATA TABLE ===== --}}
    <div class="rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300 min-w-[720px]">
                <thead class="bg-slate-50/80 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-200/80 dark:border-slate-800/80 whitespace-nowrap">
                    <tr>
                        <th class="py-3.5 px-4 font-bold">Nama Pemasok / Vendor</th>
                        <th class="py-3.5 px-4 font-bold">Kontak PIC</th>
                        <th class="py-3.5 px-4 font-bold">Telepon & WhatsApp</th>
                        <th class="py-3.5 px-4 font-bold">Alamat Email</th>
                        <th class="py-3.5 px-4 font-bold">Alamat Fisik</th>
                        <th class="py-3.5 px-4 font-bold text-center">Bahan Baku</th>
                        <th class="py-3.5 px-4 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($suppliers as $supplier)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                        {{-- Nama & Catatan --}}
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900 dark:text-white text-sm">
                                {{ $supplier->name }}
                            </div>
                            @if($supplier->notes)
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate max-w-xs mt-0.5" title="{{ $supplier->notes }}">
                                    {{ $supplier->notes }}
                                </div>
                            @endif
                        </td>

                        {{-- PIC --}}
                        <td class="py-3.5 px-4 text-slate-700 dark:text-slate-300 font-medium">
                            {{ $supplier->contact_person ?: '—' }}
                        </td>

                        {{-- Phone / WA --}}
                        <td class="py-3.5 px-4 font-mono">
                            @if($supplier->phone)
                                @php
                                    $waNumber = preg_replace('/[^0-9]/', '', $supplier->phone);
                                    if (str_starts_with($waNumber, '0')) {
                                        $waNumber = '62' . substr($waNumber, 1);
                                    }
                                @endphp
                                <div class="flex items-center gap-2">
                                    <a href="tel:{{ $supplier->phone }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition inline-flex items-center gap-1 text-slate-800 dark:text-slate-200 font-semibold">
                                        <i data-lucide="phone" class="w-3.5 h-3.5 text-slate-400"></i>
                                        <span>{{ $supplier->phone }}</span>
                                    </a>
                                    <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener noreferrer"
                                       class="p-1 rounded-md bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-500/15 dark:hover:bg-emerald-500/25 text-emerald-600 dark:text-emerald-400 transition"
                                       title="Chat WhatsApp Pemasok">
                                        <i data-lucide="message-circle" class="w-3 h-3"></i>
                                    </a>
                                </div>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>

                        {{-- Email --}}
                        <td class="py-3.5 px-4">
                            @if($supplier->email)
                                <a href="mailto:{{ $supplier->email }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 text-slate-600 dark:text-slate-400 inline-flex items-center gap-1.5 transition">
                                    <i data-lucide="mail" class="w-3.5 h-3.5 text-slate-400"></i>
                                    <span>{{ $supplier->email }}</span>
                                </a>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>

                        {{-- Address --}}
                        <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400 max-w-xs truncate" title="{{ $supplier->address }}">
                            {{ $supplier->address ?: '—' }}
                        </td>

                        {{-- Bahan Baku Count Badge --}}
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $supplier->materials_count > 0 ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/30' : 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700' }}">
                                {{ $supplier->materials_count }} item
                            </span>
                        </td>

                        {{-- Action Buttons --}}
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                @if(\App\Support\Context::hasPermission('master_data.suppliers.manage'))
                                <button @click="openEditModal({
                                    id: '{{ $supplier->id }}',
                                    name: '{{ addslashes($supplier->name) }}',
                                    contact_person: '{{ addslashes($supplier->contact_person ?? '') }}',
                                    phone: '{{ addslashes($supplier->phone ?? '') }}',
                                    email: '{{ addslashes($supplier->email ?? '') }}',
                                    address: '{{ addslashes($supplier->address ?? '') }}',
                                    notes: '{{ addslashes($supplier->notes ?? '') }}'
                                })" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 transition" title="Edit Data Pemasok">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                </button>
                                <form method="POST" action="{{ route('suppliers.destroy', $supplier->id) }}"
                                      onsubmit="return AppAlert.confirmSubmit(event, this, 'Hapus pemasok {{ addslashes($supplier->name) }}? Pastikan tidak ada PO aktif terkait.', 'Hapus Pemasok?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg bg-slate-100 hover:bg-rose-50 dark:bg-slate-800 dark:hover:bg-rose-950/40 text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 border border-slate-200 dark:border-slate-700 transition" title="Hapus Pemasok">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-500 dark:text-slate-400">
                            <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-3 text-slate-400">
                                <i data-lucide="truck" class="w-6 h-6"></i>
                            </div>
                            <h4 class="text-sm font-bold text-slate-900 dark:text-white">Belum ada data pemasok</h4>
                            <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                                Tambahkan pemasok untuk mengelola kontak vendor, riwayat PO, dan pengadaan bahan baku bisnis.
                            </p>
                            @if(\App\Support\Context::hasPermission('master_data.suppliers.manage'))
                            <button @click="showAddModal = true"
                                    class="mt-4 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-sm hover:shadow transition inline-flex items-center gap-2">
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

        @if($suppliers->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $suppliers->links() }}
        </div>
        @endif
    </div>

    {{-- ===== MODAL TAMBAH PEMASOK ===== --}}
    <div x-show="showAddModal"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-xs p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;">
        <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl p-6 space-y-5 max-h-[90vh] overflow-y-auto"
             @click.outside="showAddModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center">
                        <i data-lucide="truck" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-slate-900 dark:text-white">Tambah Pemasok Baru</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Daftarkan mitra vendor penyedia bahan baku.</p>
                    </div>
                </div>
                <button @click="showAddModal = false" class="p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 dark:hover:text-white transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('suppliers.store') }}" class="space-y-4 text-xs sm:text-sm">
                @csrf
                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                        Nama Pemasok / Vendor <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" required placeholder="Contoh: PT Sumber Pangan Sejahtera, CV Mitra Tani..."
                           class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                            Nama Kontak (PIC)
                        </label>
                        <input type="text" name="contact_person" placeholder="Mis: Budi Santoso"
                               class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                    </div>
                    <div>
                        <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                            No. Telepon / WhatsApp
                        </label>
                        <input type="text" name="phone" placeholder="0812-3456-7890"
                               class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                        Alamat Email
                    </label>
                    <input type="email" name="email" placeholder="sales@sumberpangan.com"
                           class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                </div>

                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                        Alamat Lengkap
                    </label>
                    <textarea name="address" rows="2" placeholder="Jl. Pergudangan No. 12, Kawasan Industri..."
                              class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition resize-none"></textarea>
                </div>

                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                        Catatan Khusus (TOP / Ketentuan)
                    </label>
                    <input type="text" name="notes" placeholder="Term pembayaran 14 hari, minimum order 50kg..."
                           class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="showAddModal = false"
                            class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition shadow-sm hover:shadow">
                        Simpan Pemasok
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== MODAL EDIT PEMASOK ===== --}}
    <div x-show="showEditModal"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-xs p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;">
        <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl p-6 space-y-5 max-h-[90vh] overflow-y-auto"
             @click.outside="showEditModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center">
                        <i data-lucide="pencil" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-slate-900 dark:text-white">Edit Data Pemasok</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400" x-text="'Memperbarui: ' + editSupplier.name"></p>
                    </div>
                </div>
                <button @click="showEditModal = false" class="p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 dark:hover:text-white transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'/suppliers/' + editSupplier.id" method="POST" class="space-y-4 text-xs sm:text-sm">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                        Nama Pemasok / Vendor <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" x-model="editSupplier.name" required
                           class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                            Nama Kontak (PIC)
                        </label>
                        <input type="text" name="contact_person" x-model="editSupplier.contact_person"
                               class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                    </div>
                    <div>
                        <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                            No. Telepon / WhatsApp
                        </label>
                        <input type="text" name="phone" x-model="editSupplier.phone"
                               class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                        Alamat Email
                    </label>
                    <input type="email" name="email" x-model="editSupplier.email"
                           class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                </div>

                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                        Alamat Lengkap
                    </label>
                    <textarea name="address" rows="2" x-model="editSupplier.address"
                              class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition resize-none"></textarea>
                </div>

                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-bold text-xs mb-1.5">
                        Catatan Khusus
                    </label>
                    <input type="text" name="notes" x-model="editSupplier.notes"
                           class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="showEditModal = false"
                            class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition shadow-sm hover:shadow">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
