@extends('layouts.app', [
    'title' => 'Klien & Pelanggan Komersial — Cooca UMKM',
    'headerTitle' => 'Manajemen Pelanggan (Customers)',
    'headerSubtitle' => 'Kelola direktori klien komersial, profil perusahaan, kontak penagihan, termin kredit pembayaran, dan riwayat transaksi'
])

@section('content')
<script>
    window.COOCA_CUSTOMERS = @json($customers->items());
</script>

<div class="space-y-6 pb-12" x-data="{
    customersList: window.COOCA_CUSTOMERS || [],
    showAddModal: false,
    showEditModal: false,
    showDetailModal: false,
    selectedCustomer: null,
    editCustomer: {
        id: '',
        slug: '',
        name: '',
        company_name: '',
        code: '',
        email: '',
        phone: '',
        billing_address: '',
        shipping_address: '',
        tax_identification_number: '',
        payment_terms_days: 30,
        notes: ''
    },
    
    openEditModal(id) {
        const c = this.customersList.find(item => item.id == id);
        if (!c) return;
        this.editCustomer = {
            id: c.id || '',
            slug: c.slug || '',
            name: c.name || '',
            company_name: c.company_name || '',
            code: c.code || '',
            email: c.email || '',
            phone: c.phone || '',
            billing_address: c.billing_address || '',
            shipping_address: c.shipping_address || '',
            tax_identification_number: c.tax_identification_number || '',
            payment_terms_days: c.payment_terms_days !== undefined ? c.payment_terms_days : 30,
            notes: c.notes || ''
        };
        this.showEditModal = true;
    },

    openDetailModal(id) {
        this.selectedCustomer = this.customersList.find(item => item.id == id) || null;
        this.showDetailModal = true;
    },

    copyBillingToShipping(target) {
        if (target === 'add') {
            const billing = document.getElementById('add_billing_address');
            const shipping = document.getElementById('add_shipping_address');
            if (billing && shipping) {
                shipping.value = billing.value;
            }
        } else if (target === 'edit') {
            this.editCustomer.shipping_address = this.editCustomer.billing_address;
        }
    },

    setPaymentTerm(days, target) {
        if (target === 'add') {
            const input = document.getElementById('add_payment_terms_days');
            if (input) input.value = days;
        } else if (target === 'edit') {
            this.editCustomer.payment_terms_days = days;
        }
    }
}">

    <!-- 0. Standard Breadcrumb Bar -->
    <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 print:hidden" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors flex items-center gap-1.5 font-bold text-slate-900 dark:text-white">
            <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
            <span>Dashboard</span>
        </a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600"></i>
        <span class="text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
            <i data-lucide="users" class="w-3.5 h-3.5"></i>
            <span>Master Data</span>
        </span>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600"></i>
        <span class="text-slate-900 dark:text-white font-bold flex items-center gap-1.5">
            <span>Direktori Pelanggan &amp; Klien</span>
        </span>
    </nav>

    <!-- 1. Top Header Banner (Seukuran Dashboard Penuh) -->
    <div class="bg-white dark:bg-slate-900/90 p-5 sm:p-6 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 transition-colors">
        <div class="space-y-1.5 max-w-3xl">
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full px-2.5 py-0.5 text-[10px] sm:text-xs font-bold border inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200/90 dark:border-emerald-800/90">
                    <i data-lucide="users" class="w-3.5 h-3.5"></i>
                    <span>Direktori Klien Komersial</span>
                </span>
                <span class="rounded-full px-2.5 py-0.5 text-[10px] sm:text-xs font-bold border inline-flex items-center gap-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 font-mono">
                    Total: {{ number_format($totalCustomers ?? $customers->total(), 0, ',', '.') }} Klien
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                Direktori Klien &amp; Pelanggan Komersial
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                Kelola basis data klien komersial, profil perusahaan B2B, kontak penagihan, termin tempo kredit (Net D), serta rekam jejak pesanan dan faktur.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
            @if(\App\Support\Context::hasPermission('crm.view') || \App\Support\Context::hasPermission('crm.manage'))
            <a href="{{ route('crm.members.index') }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/80 transition cursor-pointer shadow-2xs flex items-center justify-center gap-1.5 flex-1 sm:flex-none">
                <i data-lucide="award" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                <span>Loyalitas CRM</span>
            </a>
            <a href="{{ route('crm.vouchers.index') }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/80 transition cursor-pointer shadow-2xs flex items-center justify-center gap-1.5 flex-1 sm:flex-none">
                <i data-lucide="ticket" class="w-4 h-4 text-cyan-600 dark:text-cyan-400"></i>
                <span>Voucher Kasir</span>
            </a>
            @endif
            @if(\App\Support\Context::hasPermission('customers.create'))
            <button type="button" 
                    @click="showAddModal = true" 
                    class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm shadow-emerald-600/20 active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-1.5 flex-1 sm:flex-none">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Tambah Pelanggan</span>
            </button>
            @endif
        </div>
    </div>

    <!-- Flash Alert -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-500/40 text-emerald-800 dark:text-emerald-300 text-xs flex items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-2.5">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-200">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endif

    <!-- 2. 4 Command Pillars KPI Cards (Grid Penuh 4 Kolom) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Pillar 1: Total Customers -->
        <div class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Total Klien Terdaftar</span>
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200/80 dark:border-indigo-800/80 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <i data-lucide="users" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-900 dark:text-white font-mono tracking-tight truncate">
                    {{ number_format($totalCustomers ?? $customers->total(), 0, ',', '.') }}
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Database</span>
                <span class="font-bold text-slate-700 dark:text-slate-300">Pelanggan Aktif</span>
            </div>
        </div>

        <!-- Pillar 2: Corporate Clients -->
        <div class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Entitas Korporat / PT</span>
                    <div class="w-9 h-9 rounded-xl bg-cyan-50 dark:bg-cyan-950/60 border border-cyan-200/80 dark:border-cyan-800/80 flex items-center justify-center text-cyan-600 dark:text-cyan-400">
                        <i data-lucide="building-2" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-900 dark:text-white font-mono tracking-tight truncate">
                    {{ number_format($totalCorporate ?? 0, 0, ',', '.') }}
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Segmen</span>
                <span class="font-bold text-cyan-600 dark:text-cyan-400">Badan Usaha</span>
            </div>
        </div>

        <!-- Pillar 3: Average Payment Terms -->
        <div class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Rata-Rata Termin Bayar</span>
                    <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/80 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <i data-lucide="calendar-clock" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-black text-amber-600 dark:text-amber-400 font-mono tracking-tight truncate">
                    Net {{ $avgPaymentTerms ?? 30 }} Hari
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Kebijakan</span>
                <span class="font-bold text-slate-700 dark:text-slate-300">Tempo Penagihan</span>
            </div>
        </div>

        <!-- Pillar 4: Total Orders & Invoices Handled -->
        <div class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Aktivitas Transaksi</span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/80 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <i data-lucide="receipt" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-900 dark:text-white font-mono tracking-tight truncate">
                    {{ number_format($customers->sum('invoices_count') + $customers->sum('purchase_orders_count'), 0, ',', '.') }}
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Dokumen</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400 font-mono">PO &amp; Faktur</span>
            </div>
        </div>
    </div>

    <!-- 3. Toolbar Filter & Search Container -->
    <div class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-4 sm:p-5">
        <form method="GET" action="{{ route('customers.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3.5 items-center">
            <!-- Search Query Input -->
            <div class="sm:col-span-9 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari nama pelanggan, nama perusahaan/PT, kode klien, nomor telepon/WhatsApp, atau email..." 
                       class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl pl-10 pr-4 py-2.5 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden transition">
            </div>

            <!-- Submit & Reset Buttons -->
            <div class="sm:col-span-3 flex items-center gap-2">
                <button type="submit" 
                        class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition shadow-sm shadow-emerald-600/20 flex items-center justify-center gap-1.5 cursor-pointer active:scale-[0.98]">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Cari Pelanggan</span>
                </button>
                @if(request('search'))
                <a href="{{ route('customers.index') }}" 
                   class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white text-xs font-semibold transition border border-slate-200 dark:border-slate-700 flex items-center gap-1"
                   title="Reset Pencarian">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Reset</span>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- 4. High-Density Data Table Container -->
    <div class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs overflow-hidden">
        
        <!-- Table Header Bar -->
        <div class="p-5 border-b border-slate-100 dark:border-slate-800/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="contact-2" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                    <span>Daftar Klien &amp; Pelanggan Komersial</span>
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Basis data pelanggan B2B dan ritel untuk penerbitan Purchase Order &amp; Faktur Penjualan.</p>
            </div>
            <span class="text-xs font-mono text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-950 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800">
                Menampilkan {{ $customers->count() }} dari {{ $customers->total() }} pelanggan
            </span>
        </div>

        @if($customers->count() > 0)

            <!-- Desktop View: Dense Data Table (Hidden on Mobile) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50/80 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 font-mono uppercase text-[10px] font-bold border-b border-slate-200 dark:border-slate-800 tracking-wider whitespace-nowrap">
                        <tr>
                            <th scope="col" class="py-3.5 px-5">Nama &amp; Perusahaan</th>
                            <th scope="col" class="py-3.5 px-4">ID Klien</th>
                            <th scope="col" class="py-3.5 px-4">Kontak &amp; Email</th>
                            <th scope="col" class="py-3.5 px-4">Alamat Penagihan</th>
                            <th scope="col" class="py-3.5 px-4 text-center">Termin Bayar</th>
                            <th scope="col" class="py-3.5 px-4 text-center">Aktivitas Transaksi</th>
                            <th scope="col" class="py-3.5 px-5 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-sans">
                        @foreach($customers as $customer)
                        @php
                            $termsDays = (int) ($customer->payment_terms_days ?? 30);
                            $termBadge = match(true) {
                                $termsDays <= 0 => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200/90 dark:border-emerald-800/90',
                                $termsDays <= 14 => 'bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-400 border-teal-200/90 dark:border-teal-800/90',
                                $termsDays <= 30 => 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400 border-blue-200/90 dark:border-blue-800/90',
                                default => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border-amber-200/90 dark:border-amber-800/90',
                            };
                            $termLabel = $termsDays <= 0 ? 'Tunai / Cash' : 'Net ' . $termsDays . ' Hari';
                        @endphp
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors group">
                            
                            <!-- 1. Name & Company -->
                            <td class="py-3.5 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-emerald-600 dark:text-emerald-400 font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <button type="button" 
                                                @click="openDetailModal('{{ $customer->id }}')" 
                                                class="font-black text-slate-900 dark:text-white text-sm hover:text-emerald-600 dark:hover:text-emerald-400 transition text-left truncate block cursor-pointer">
                                            {{ $customer->name }}
                                        </button>
                                        @if($customer->company_name)
                                            <div class="text-[11px] text-cyan-700 dark:text-cyan-300 font-medium flex items-center gap-1 mt-0.5 truncate">
                                                <i data-lucide="building-2" class="w-3 h-3 text-cyan-600 dark:text-cyan-400 shrink-0"></i>
                                                <span class="truncate">{{ $customer->company_name }}</span>
                                            </div>
                                        @else
                                            <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">Personal Client</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- 2. Client Code -->
                            <td class="py-3.5 px-4 font-mono">
                                @if($customer->code)
                                    <span class="px-2 py-0.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 font-bold text-[11px]">
                                        {{ $customer->code }}
                                    </span>
                                @else
                                    <span class="text-slate-400 dark:text-slate-600 font-mono text-xs">-</span>
                                @endif
                            </td>

                            <!-- 3. Contact & Email -->
                            <td class="py-3.5 px-4">
                                <div class="space-y-1">
                                    @if($customer->phone)
                                        <div class="font-mono text-slate-900 dark:text-slate-200 flex items-center gap-1.5">
                                            <i data-lucide="phone" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                                            <a href="tel:{{ $customer->phone }}" class="hover:underline hover:text-emerald-600 dark:hover:text-emerald-300">{{ $customer->phone }}</a>
                                        </div>
                                    @endif
                                    @if($customer->email)
                                        <div class="text-slate-500 dark:text-slate-400 flex items-center gap-1.5 text-[11px]">
                                            <i data-lucide="mail" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                            <a href="mailto:{{ $customer->email }}" class="hover:underline hover:text-slate-800 dark:hover:text-slate-200 truncate max-w-[180px]">{{ $customer->email }}</a>
                                        </div>
                                    @endif
                                    @if(! $customer->phone && ! $customer->email)
                                        <span class="text-slate-400 dark:text-slate-500 italic text-[11px]">Belum diisi</span>
                                    @endif
                                </div>
                            </td>

                            <!-- 4. Billing Address -->
                            <td class="py-3.5 px-4 text-slate-700 dark:text-slate-300 max-w-[200px]">
                                @if($customer->billing_address)
                                    <div class="truncate text-xs" title="{{ $customer->billing_address }}">
                                        {{ $customer->billing_address }}
                                    </div>
                                    @if($customer->tax_identification_number)
                                        <div class="text-[10px] font-mono text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-1">
                                            <i data-lucide="file-text" class="w-3 h-3 text-slate-400"></i>
                                            <span>NPWP: {{ $customer->tax_identification_number }}</span>
                                        </div>
                                    @endif
                                @else
                                    <span class="text-slate-400 dark:text-slate-600 text-xs">-</span>
                                @endif
                            </td>

                            <!-- 5. Payment Terms -->
                            <td class="py-3.5 px-4 text-center font-mono whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $termBadge }}">
                                    {{ $termLabel }}
                                </span>
                            </td>

                            <!-- 6. Transactions Count -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center justify-center gap-2 p-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                                    <span class="text-[11px] font-mono font-bold text-slate-700 dark:text-slate-300" title="Total Pesanan Pembelian">
                                        {{ $customer->purchase_orders_count }} PO
                                    </span>
                                    <span class="text-slate-300 dark:text-slate-700">•</span>
                                    <span class="text-[11px] font-mono font-black text-emerald-600 dark:text-emerald-400" title="Total Faktur Penjualan">
                                        {{ $customer->invoices_count }} Faktur
                                    </span>
                                </div>
                            </td>

                            <!-- 7. Actions -->
                            <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Quick Detail -->
                                    <button type="button" 
                                            @click="openDetailModal('{{ $customer->id }}')" 
                                            class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white transition cursor-pointer" 
                                            title="Lihat Detail Profil">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </button>

                                    @if(\App\Support\Context::hasPermission('customers.edit'))
                                    <!-- Edit -->
                                    <button type="button" 
                                            @click="openEditModal('{{ $customer->id }}')" 
                                            class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white transition cursor-pointer" 
                                            title="Edit Pelanggan">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>
                                    @endif

                                    @if(\App\Support\Context::hasPermission('customers.delete'))
                                    <!-- Delete -->
                                    <form method="POST" 
                                          action="{{ route('customers.destroy', $customer->slug) }}" 
                                          onsubmit="return AppAlert.confirmSubmit(event, this, 'Hapus data pelanggan {{ addslashes($customer->name) }}?', 'Hapus Pelanggan?', 'danger')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-1.5 hover:bg-rose-50 dark:hover:bg-rose-500/20 rounded-lg text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 transition cursor-pointer" 
                                                title="Hapus Pelanggan">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile View: Clean Card Stream -->
            <div class="block md:hidden divide-y divide-slate-100 dark:divide-slate-800/80">
                @foreach($customers as $customer)
                @php
                    $termsDays = (int) ($customer->payment_terms_days ?? 30);
                    $termBadge = match(true) {
                        $termsDays <= 0 => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200/90 dark:border-emerald-800/90',
                        $termsDays <= 14 => 'bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-400 border-teal-200/90 dark:border-teal-800/90',
                        $termsDays <= 30 => 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400 border-blue-200/90 dark:border-blue-800/90',
                        default => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border-amber-200/90 dark:border-amber-800/90',
                    };
                    $termLabel = $termsDays <= 0 ? 'Tunai (Cash)' : 'Net ' . $termsDays . ' Hari';
                @endphp
                <div class="p-5 space-y-4 hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition">
                    
                    <!-- Card Top: Client Avatar, Name, Company & Term Badge -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-emerald-600 dark:text-emerald-400 font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                {{ strtoupper(substr($customer->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <button type="button" 
                                        @click="openDetailModal('{{ $customer->id }}')" 
                                        class="font-black text-slate-900 dark:text-white text-sm hover:text-emerald-600 dark:hover:text-emerald-400 transition text-left truncate block cursor-pointer">
                                    {{ $customer->name }}
                                </button>
                                @if($customer->company_name)
                                    <div class="text-[11px] text-cyan-700 dark:text-cyan-300 font-medium flex items-center gap-1 truncate">
                                        <i data-lucide="building-2" class="w-3 h-3 text-cyan-600 dark:text-cyan-400 shrink-0"></i>
                                        <span class="truncate">{{ $customer->company_name }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-col items-end gap-1 shrink-0">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $termBadge }}">
                                {{ $termLabel }}
                            </span>
                            @if($customer->code)
                                <span class="text-[10px] font-mono text-slate-500 dark:text-slate-400 uppercase">{{ $customer->code }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Card Body: Contact Quick Actions & Micro Stats -->
                    <div class="grid grid-cols-2 gap-2 p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center">
                        <div class="space-y-0.5">
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 block uppercase font-mono font-bold">Purchase Orders</span>
                            <div class="text-xs font-mono font-bold text-slate-900 dark:text-slate-200">
                                {{ $customer->purchase_orders_count }} PO Terbit
                            </div>
                        </div>
                        <div class="space-y-0.5 border-l border-slate-200 dark:border-slate-800">
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 block uppercase font-mono font-bold">Faktur Penjualan</span>
                            <div class="text-xs font-mono font-black text-emerald-600 dark:text-emerald-400">
                                {{ $customer->invoices_count }} Faktur Terbit
                            </div>
                        </div>
                    </div>

                    <!-- Contact & Address Snippet -->
                    <div class="space-y-1 text-xs text-slate-600 dark:text-slate-400">
                        @if($customer->phone)
                            <div class="flex items-center gap-2">
                                <i data-lucide="phone" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                                <a href="tel:{{ $customer->phone }}" class="text-slate-900 dark:text-slate-200 font-mono hover:underline">{{ $customer->phone }}</a>
                            </div>
                        @endif
                        @if($customer->email)
                            <div class="flex items-center gap-2">
                                <i data-lucide="mail" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                <span class="truncate">{{ $customer->email }}</span>
                            </div>
                        @endif
                        @if($customer->billing_address)
                            <div class="flex items-start gap-2 text-[11px] text-slate-500 dark:text-slate-400 pt-0.5">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400 shrink-0 mt-0.5"></i>
                                <span class="line-clamp-1">{{ $customer->billing_address }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Card Footer: Action Buttons -->
                    <div class="flex items-center justify-between gap-2 pt-1 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" 
                                @click="openDetailModal('{{ $customer->id }}')" 
                                class="inline-flex items-center gap-1 text-xs text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-semibold cursor-pointer">
                            <i data-lucide="eye" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span>Detail Profil</span>
                        </button>

                        <div class="flex items-center gap-1.5">
                            @if(\App\Support\Context::hasPermission('customers.edit'))
                            <button type="button" 
                                    @click="openEditModal('{{ $customer->id }}')" 
                                    class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition flex items-center gap-1 shadow-2xs cursor-pointer">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                <span>Edit</span>
                            </button>
                            @endif
                            @if(\App\Support\Context::hasPermission('customers.delete'))
                            <form method="POST" 
                                  action="{{ route('customers.destroy', $customer->slug) }}" 
                                  onsubmit="return AppAlert.confirmSubmit(event, this, 'Hapus data pelanggan {{ addslashes($customer->name) }}?', 'Hapus Pelanggan?', 'danger')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="p-1.5 rounded-xl hover:bg-rose-50 dark:hover:bg-rose-500/20 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 transition cursor-pointer">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>

                </div>
                @endforeach
            </div>

        @else
            <!-- Empty State -->
            <div class="py-16 px-6 text-center space-y-4">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 flex items-center justify-center mx-auto shadow-inner">
                    <i data-lucide="users" class="w-8 h-8"></i>
                </div>
                <div class="space-y-1 max-w-sm mx-auto">
                    <h4 class="text-sm font-black text-slate-900 dark:text-white">Tidak Ada Data Pelanggan Ditemukan</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Coba ubah kata kunci pencarian Anda atau tambahkan pelanggan baru untuk mulai mencatat pesanan pembelian dan menerbitkan faktur penjualan.
                    </p>
                </div>
                @if(\App\Support\Context::hasPermission('customers.create'))
                <div class="pt-2">
                    <button type="button" 
                            @click="showAddModal = true" 
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-sm shadow-emerald-600/20 transition cursor-pointer">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        <span>Tambah Pelanggan Baru</span>
                    </button>
                </div>
                @endif
            </div>
        @endif

        <!-- Pagination -->
        @if($customers->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $customers->links() }}
        </div>
        @endif

    </div>

    @if(\App\Support\Context::hasPermission('customers.create'))
    <!-- Modal 1: Tambah Pelanggan Baru -->
    <div x-show="showAddModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 transition-all"
         @keydown.escape.window="showAddModal = false">
        
        <div class="w-full max-w-2xl p-6 sm:p-7 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl space-y-5 relative max-h-[90vh] overflow-y-auto"
             @click.away="showAddModal = false">
            
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <i data-lucide="user-plus" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Tambah Pelanggan Baru</h3>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">Form Registrasi Klien &amp; B2B Account</span>
                    </div>
                </div>
                <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('customers.store') }}" class="space-y-4 text-xs">
                @csrf

                <!-- Section 1: Data Identitas Klien -->
                <div class="space-y-3 p-4 rounded-xl bg-slate-50/70 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800">
                    <span class="text-[10px] font-mono text-emerald-600 dark:text-emerald-400 uppercase font-bold tracking-wider block">1. Identitas Klien &amp; Badan Usaha</span>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div class="space-y-1.5">
                            <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                                Nama Pelanggan (PIC) <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   required 
                                   placeholder="Contoh: Ahmad Zaki" 
                                   class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 text-slate-900 dark:text-white font-medium focus:outline-hidden transition">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                                Nama Perusahaan / Instansi <span class="text-slate-400 text-[11px] font-normal">(Opsional)</span>
                            </label>
                            <input type="text" 
                                   name="company_name" 
                                   placeholder="Contoh: PT Maju Bersama Sentosa" 
                                   class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 text-slate-900 dark:text-white focus:outline-hidden transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div class="space-y-1.5">
                            <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                                ID / Kode Klien <span class="text-slate-400 text-[11px] font-normal">(Opsional)</span>
                            </label>
                            <input type="text" 
                                   name="code" 
                                   placeholder="Contoh: CUST-001" 
                                   class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 font-mono text-slate-900 dark:text-white uppercase focus:outline-hidden transition">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                                NPWP / Nomor Pokok Wajib Pajak <span class="text-slate-400 text-[11px] font-normal">(Opsional)</span>
                            </label>
                            <input type="text" 
                                   name="tax_identification_number" 
                                   placeholder="01.234.567.8-901.000" 
                                   class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 font-mono text-slate-900 dark:text-white focus:outline-hidden transition">
                        </div>
                    </div>
                </div>

                <!-- Section 2: Kontak Penagihan & Termin -->
                <div class="space-y-3 p-4 rounded-xl bg-slate-50/70 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800">
                    <span class="text-[10px] font-mono text-cyan-600 dark:text-cyan-400 uppercase font-bold tracking-wider block">2. Kontak &amp; Kebijakan Pembayaran</span>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div class="space-y-1.5">
                            <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                                No. Telepon / WhatsApp <span class="text-slate-400 text-[11px] font-normal">(Opsional)</span>
                            </label>
                            <input type="text" 
                                   name="phone" 
                                   placeholder="0812-8888-9999" 
                                   class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 font-mono text-slate-900 dark:text-white focus:outline-hidden transition">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                                Alamat Email Penagihan <span class="text-slate-400 text-[11px] font-normal">(Opsional)</span>
                            </label>
                            <input type="email" 
                                   name="email" 
                                   placeholder="finance@majubersama.com" 
                                   class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 text-slate-900 dark:text-white focus:outline-hidden transition">
                        </div>
                    </div>

                    <!-- Payment Terms & Quick Chips -->
                    <div class="space-y-2 pt-1">
                        <div class="flex items-center justify-between">
                            <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                                Termin Pembayaran Kredit (Hari)
                            </label>
                            <span class="text-[10px] font-mono text-slate-500 dark:text-slate-400">Pilih Cepat:</span>
                        </div>
                        
                        <div class="flex flex-wrap gap-2">
                            <button type="button" @click="setPaymentTerm(0, 'add')" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold border border-slate-200 dark:border-slate-700 transition cursor-pointer">Tunai (0 Hari)</button>
                            <button type="button" @click="setPaymentTerm(14, 'add')" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold border border-slate-200 dark:border-slate-700 transition cursor-pointer">Net 14 Hari</button>
                            <button type="button" @click="setPaymentTerm(30, 'add')" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold border border-slate-200 dark:border-slate-700 transition cursor-pointer">Net 30 Hari</button>
                            <button type="button" @click="setPaymentTerm(45, 'add')" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold border border-slate-200 dark:border-slate-700 transition cursor-pointer">Net 45 Hari</button>
                            <button type="button" @click="setPaymentTerm(60, 'add')" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold border border-slate-200 dark:border-slate-700 transition cursor-pointer">Net 60 Hari</button>
                        </div>

                        <input type="number" 
                               id="add_payment_terms_days"
                               name="payment_terms_days" 
                               value="30" 
                               min="0" 
                               max="365" 
                               class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 text-slate-900 dark:text-white font-mono font-bold focus:outline-hidden transition">
                    </div>
                </div>

                <!-- Section 3: Alamat Penagihan & Pengiriman -->
                <div class="space-y-3 p-4 rounded-xl bg-slate-50/70 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-mono text-amber-600 dark:text-amber-400 uppercase font-bold tracking-wider block">3. Alamat Operasional &amp; Pengiriman</span>
                        <button type="button" 
                                @click="copyBillingToShipping('add')" 
                                class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 hover:text-emerald-500 flex items-center gap-1 transition cursor-pointer">
                            <i data-lucide="copy" class="w-3 h-3"></i>
                            <span>Salin ke Alamat Pengiriman</span>
                        </button>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                            Alamat Penagihan (Billing Address)
                        </label>
                        <textarea id="add_billing_address" 
                                  name="billing_address" 
                                  rows="2" 
                                  placeholder="Gedung Cyber 2 Lt. 10, Jl. HR Rasuna Said, Jakarta Selatan" 
                                  class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 text-slate-900 dark:text-white focus:outline-hidden transition"></textarea>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                            Alamat Pengiriman (Shipping Address)
                        </label>
                        <textarea id="add_shipping_address" 
                                  name="shipping_address" 
                                  rows="2" 
                                  placeholder="Gudang Logistik Kawasan Industri Pulogadung" 
                                  class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 text-slate-900 dark:text-white focus:outline-hidden transition"></textarea>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                            Catatan Khusus Klien <span class="text-slate-400 text-[11px] font-normal">(Opsional)</span>
                        </label>
                        <input type="text" 
                               name="notes" 
                               placeholder="Diskon khusus pesanan repeat order > 100 unit / syarat DO" 
                               class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 text-slate-900 dark:text-white focus:outline-hidden transition">
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" 
                            @click="showAddModal = false" 
                            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-sm shadow-emerald-600/20 active:scale-[0.98] transition flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Pelanggan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if(\App\Support\Context::hasPermission('customers.edit'))
    <!-- Modal 2: Edit Data Pelanggan -->
    <div x-show="showEditModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 transition-all"
         @keydown.escape.window="showEditModal = false">
        
        <div class="w-full max-w-2xl p-6 sm:p-7 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl space-y-5 relative max-h-[90vh] overflow-y-auto"
             @click.away="showEditModal = false">
            
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-cyan-50 dark:bg-cyan-950/60 border border-cyan-200 dark:border-cyan-800 text-cyan-600 dark:text-cyan-400 flex items-center justify-center">
                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Edit Data Pelanggan</h3>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono" x-text="editCustomer.name"></span>
                    </div>
                </div>
                <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'/customers/' + (editCustomer.slug || editCustomer.id)" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <!-- Section 1: Data Identitas Klien -->
                <div class="space-y-3 p-4 rounded-xl bg-slate-50/70 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800">
                    <span class="text-[10px] font-mono text-emerald-600 dark:text-emerald-400 uppercase font-bold tracking-wider block">1. Identitas Klien &amp; Badan Usaha</span>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div class="space-y-1.5">
                            <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                                Nama Pelanggan (PIC) <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   x-model="editCustomer.name"
                                   required 
                                   class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 text-slate-900 dark:text-white font-medium focus:outline-hidden transition">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                                Nama Perusahaan / Instansi <span class="text-slate-400 text-[11px] font-normal">(Opsional)</span>
                            </label>
                            <input type="text" 
                                   name="company_name" 
                                   x-model="editCustomer.company_name"
                                   placeholder="Contoh: PT Maju Bersama Sentosa" 
                                   class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 text-slate-900 dark:text-white focus:outline-hidden transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div class="space-y-1.5">
                            <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                                ID / Kode Klien <span class="text-slate-400 text-[11px] font-normal">(Opsional)</span>
                            </label>
                            <input type="text" 
                                   name="code" 
                                   x-model="editCustomer.code"
                                   class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 font-mono text-slate-900 dark:text-white uppercase focus:outline-hidden transition">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                                NPWP / Nomor Pokok Wajib Pajak <span class="text-slate-400 text-[11px] font-normal">(Opsional)</span>
                            </label>
                            <input type="text" 
                                   name="tax_identification_number" 
                                   x-model="editCustomer.tax_identification_number"
                                   placeholder="01.234.567.8-901.000" 
                                   class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 font-mono text-slate-900 dark:text-white focus:outline-hidden transition">
                        </div>
                    </div>
                </div>

                <!-- Section 2: Kontak Penagihan & Termin -->
                <div class="space-y-3 p-4 rounded-xl bg-slate-50/70 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800">
                    <span class="text-[10px] font-mono text-cyan-600 dark:text-cyan-400 uppercase font-bold tracking-wider block">2. Kontak &amp; Kebijakan Pembayaran</span>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div class="space-y-1.5">
                            <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                                No. Telepon / WhatsApp <span class="text-slate-400 text-[11px] font-normal">(Opsional)</span>
                            </label>
                            <input type="text" 
                                   name="phone" 
                                   x-model="editCustomer.phone"
                                   class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 font-mono text-slate-900 dark:text-white focus:outline-hidden transition">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                                Alamat Email Penagihan <span class="text-slate-400 text-[11px] font-normal">(Opsional)</span>
                            </label>
                            <input type="email" 
                                   name="email" 
                                   x-model="editCustomer.email"
                                   class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 text-slate-900 dark:text-white focus:outline-hidden transition">
                        </div>
                    </div>

                    <!-- Payment Terms & Quick Chips -->
                    <div class="space-y-2 pt-1">
                        <div class="flex items-center justify-between">
                            <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                                Termin Pembayaran Kredit (Hari)
                            </label>
                            <span class="text-[10px] font-mono text-slate-500 dark:text-slate-400">Pilih Cepat:</span>
                        </div>
                        
                        <div class="flex flex-wrap gap-2">
                            <button type="button" @click="setPaymentTerm(0, 'edit')" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold border border-slate-200 dark:border-slate-700 transition cursor-pointer">Tunai (0 Hari)</button>
                            <button type="button" @click="setPaymentTerm(14, 'edit')" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold border border-slate-200 dark:border-slate-700 transition cursor-pointer">Net 14 Hari</button>
                            <button type="button" @click="setPaymentTerm(30, 'edit')" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold border border-slate-200 dark:border-slate-700 transition cursor-pointer">Net 30 Hari</button>
                            <button type="button" @click="setPaymentTerm(45, 'edit')" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold border border-slate-200 dark:border-slate-700 transition cursor-pointer">Net 45 Hari</button>
                            <button type="button" @click="setPaymentTerm(60, 'edit')" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold border border-slate-200 dark:border-slate-700 transition cursor-pointer">Net 60 Hari</button>
                        </div>

                        <input type="number" 
                               name="payment_terms_days" 
                               x-model.number="editCustomer.payment_terms_days"
                               min="0" 
                               max="365" 
                               class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 text-slate-900 dark:text-white font-mono font-bold focus:outline-hidden transition">
                    </div>
                </div>

                <!-- Section 3: Alamat Penagihan & Pengiriman -->
                <div class="space-y-3 p-4 rounded-xl bg-slate-50/70 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-mono text-amber-600 dark:text-amber-400 uppercase font-bold tracking-wider block">3. Alamat Operasional &amp; Pengiriman</span>
                        <button type="button" 
                                @click="copyBillingToShipping('edit')" 
                                class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 hover:text-emerald-500 flex items-center gap-1 transition cursor-pointer">
                            <i data-lucide="copy" class="w-3 h-3"></i>
                            <span>Salin ke Alamat Pengiriman</span>
                        </button>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                            Alamat Penagihan (Billing Address)
                        </label>
                        <textarea name="billing_address" 
                                  rows="2" 
                                  x-model="editCustomer.billing_address"
                                  class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 text-slate-900 dark:text-white focus:outline-hidden transition"></textarea>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                            Alamat Pengiriman (Shipping Address)
                        </label>
                        <textarea name="shipping_address" 
                                  rows="2" 
                                  x-model="editCustomer.shipping_address"
                                  class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 text-slate-900 dark:text-white focus:outline-hidden transition"></textarea>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                            Catatan Khusus Klien <span class="text-slate-400 text-[11px] font-normal">(Opsional)</span>
                        </label>
                        <input type="text" 
                               name="notes" 
                               x-model="editCustomer.notes"
                               class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 text-slate-900 dark:text-white focus:outline-hidden transition">
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" 
                            @click="showEditModal = false" 
                            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-sm shadow-emerald-600/20 active:scale-[0.98] transition flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if(\App\Support\Context::hasPermission('customers.view'))
    <!-- Modal 3: Detail Profil Klien Lengkap (Quick Inspection Modal) -->
    <div x-show="showDetailModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 transition-all"
         @keydown.escape.window="showDetailModal = false">
        
        <div class="w-full max-w-lg p-6 sm:p-7 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl space-y-5 relative"
             @click.away="showDetailModal = false">
            
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800 text-indigo-600 dark:text-indigo-400 font-bold text-sm flex items-center justify-center">
                        <span x-text="selectedCustomer ? selectedCustomer.name.substring(0, 1).toUpperCase() : 'C'"></span>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white" x-text="selectedCustomer ? selectedCustomer.name : ''"></h3>
                        <span class="text-[10px] text-cyan-600 dark:text-cyan-400 font-mono" x-text="selectedCustomer && selectedCustomer.company_name ? selectedCustomer.company_name : 'Personal Client'"></span>
                    </div>
                </div>
                <button type="button" @click="showDetailModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="space-y-4 text-xs">
                <!-- 3 Pillars Info Card -->
                <div class="grid grid-cols-3 gap-2 p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center font-mono">
                    <div>
                        <span class="text-[9px] uppercase text-slate-500 dark:text-slate-400 font-bold block">ID Klien</span>
                        <span class="font-bold text-slate-900 dark:text-white text-xs" x-text="selectedCustomer && selectedCustomer.code ? selectedCustomer.code : '-'"></span>
                    </div>
                    <div class="border-x border-slate-200 dark:border-slate-800">
                        <span class="text-[9px] uppercase text-slate-500 dark:text-slate-400 font-bold block">Termin Bayar</span>
                        <span class="font-bold text-amber-600 dark:text-amber-400 text-xs" x-text="selectedCustomer ? (selectedCustomer.payment_terms_days <= 0 ? 'Tunai' : 'Net ' + selectedCustomer.payment_terms_days + ' Hari') : 'Net 30'"></span>
                    </div>
                    <div>
                        <span class="text-[9px] uppercase text-slate-500 dark:text-slate-400 font-bold block">Faktur Terbit</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400 text-xs" x-text="selectedCustomer ? (selectedCustomer.invoices_count || 0) + ' Faktur' : '0 Faktur'"></span>
                    </div>
                </div>

                <!-- Contact & NPWP Details -->
                <div class="space-y-2 p-4 rounded-xl bg-slate-50/70 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800">
                    <div class="flex justify-between items-center py-1 border-b border-slate-100 dark:border-slate-800/60">
                        <span class="text-slate-500 dark:text-slate-400">Telepon / WA:</span>
                        <span class="font-mono font-bold text-slate-800 dark:text-slate-200" x-text="selectedCustomer && selectedCustomer.phone ? selectedCustomer.phone : '-'"></span>
                    </div>
                    <div class="flex justify-between items-center py-1 border-b border-slate-100 dark:border-slate-800/60">
                        <span class="text-slate-500 dark:text-slate-400">Email:</span>
                        <span class="font-mono font-bold text-slate-800 dark:text-slate-200 truncate max-w-[220px]" x-text="selectedCustomer && selectedCustomer.email ? selectedCustomer.email : '-'"></span>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <span class="text-slate-500 dark:text-slate-400">NPWP:</span>
                        <span class="font-mono font-bold text-slate-800 dark:text-slate-200" x-text="selectedCustomer && selectedCustomer.tax_identification_number ? selectedCustomer.tax_identification_number : '-'"></span>
                    </div>
                </div>

                <!-- Addresses -->
                <div class="space-y-3 p-4 rounded-xl bg-slate-50/70 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800">
                    <div class="space-y-1">
                        <span class="text-[10px] font-mono text-slate-500 dark:text-slate-400 uppercase font-bold block">Alamat Penagihan:</span>
                        <p class="text-slate-700 dark:text-slate-300 leading-relaxed" x-text="selectedCustomer && selectedCustomer.billing_address ? selectedCustomer.billing_address : 'Belum diisi'"></p>
                    </div>
                    <div class="space-y-1 pt-2 border-t border-slate-100 dark:border-slate-800/60">
                        <span class="text-[10px] font-mono text-slate-500 dark:text-slate-400 uppercase font-bold block">Alamat Pengiriman:</span>
                        <p class="text-slate-700 dark:text-slate-300 leading-relaxed" x-text="selectedCustomer && selectedCustomer.shipping_address ? selectedCustomer.shipping_address : 'Belum diisi'"></p>
                    </div>
                    <template x-if="selectedCustomer && selectedCustomer.notes">
                        <div class="space-y-1 pt-2 border-t border-slate-100 dark:border-slate-800/60">
                            <span class="text-[10px] font-mono text-slate-500 dark:text-slate-400 uppercase font-bold block">Catatan Tambahan:</span>
                            <p class="text-amber-700 dark:text-amber-300 italic" x-text="selectedCustomer.notes"></p>
                        </div>
                    </template>
                </div>
            </div>

            <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                @if(\App\Support\Context::hasPermission('customers.edit'))
                <button type="button" 
                        @click="showDetailModal = false; openEditModal(selectedCustomer ? selectedCustomer.id : '')" 
                        class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                    <span>Edit Profil Ini</span>
                </button>
                @endif
                <button type="button" 
                        @click="showDetailModal = false" 
                        class="px-5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition cursor-pointer ml-auto">
                    Tutup
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
