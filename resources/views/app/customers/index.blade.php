@extends('layouts.app', [
    'title' => 'Pusat Pelanggan & Loyalitas - Cooca',
    'headerTitle' => 'Pusat Pelanggan & Loyalitas CRM',
    'headerSubtitle' => 'Kelola direktori kontak pelanggan, program loyalty member, saldo poin, pelunasan kasbon tempo, dan voucher diskon kasir dalam satu pusat kendali terpadu.',
])

@section('content')
    <script>
        window.COOCA_CUSTOMERS = @json($customers->items());
        window.COOCA_MEMBERS = @json($members->items());
    </script>

    <div class="space-y-6 pb-16" x-data="{
        activeTab: new URLSearchParams(window.location.search).get('tab') || '{{ $tab ?? 'customers' }}',
        customersList: window.COOCA_CUSTOMERS || [],
        membersList: window.COOCA_MEMBERS || [],
        showAddModal: false,
        showEditModal: false,
        showDetailModal: false,
        showCreditModal: false,
        showPointsModal: false,
        showVoucherModal: false,
        selectedCustomer: null,
        loadingPoints: false,
        pointHistories: [],
        creditRepayAmount: '',
        creditRawAmount: 0,
        creditNotes: '',
        copiedVoucherCode: null,
    
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
    
        switchTab(tab) {
            this.activeTab = tab;
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url);
        },
    
        openDetailModal(id) {
            this.selectedCustomer = this.customersList.find(c => c.id == id) || this.membersList.find(m => m.id == id) || null;
            this.showDetailModal = true;
        },
    
        openEditModal(id) {
            const c = this.customersList.find(item => item.id == id) || this.membersList.find(item => item.id == id);
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
    
        openCreditModal(customer) {
            this.selectedCustomer = customer;
            const maxDebt = Number(customer.current_credit_balance || 0);
            this.creditRawAmount = maxDebt;
            this.creditRepayAmount = this.formatCurrency(maxDebt);
            this.creditNotes = 'Pelunasan piutang kasbon';
            this.showCreditModal = true;
        },
    
        setQuickCredit(percent) {
            if (!this.selectedCustomer) return;
            const maxDebt = Number(this.selectedCustomer.current_credit_balance || 0);
            const amt = Math.round(maxDebt * (percent / 100));
            this.creditRawAmount = amt;
            this.creditRepayAmount = this.formatCurrency(amt);
        },
    
        onCreditInput(e) {
            let clean = e.target.value.replace(/[^0-9]/g, '');
            let num = parseInt(clean, 10) || 0;
            if (this.selectedCustomer) {
                const maxDebt = Number(this.selectedCustomer.current_credit_balance || 0);
                if (num > maxDebt) num = maxDebt;
            }
            this.creditRawAmount = num;
            this.creditRepayAmount = num > 0 ? this.formatCurrency(num) : '';
        },
    
        async openPointHistory(customer) {
            this.selectedCustomer = customer;
            this.showPointsModal = true;
            this.loadingPoints = true;
            this.pointHistories = [];
    
            try {
                const res = await fetch('/crm/customers/' + customer.id + '/points', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    this.pointHistories = data.histories || [];
                }
            } catch (e) {
                console.error('Gagal mengambil data poin:', e);
            } finally {
                this.loadingPoints = false;
            }
        },
    
        copyVoucher(code) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(code);
            }
            this.copiedVoucherCode = code;
            setTimeout(() => this.copiedVoucherCode = null, 2500);
        },
    
        formatCurrency(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }
    }">

        <!-- 0. Breadcrumb Bar Apple HIG -->
        <nav class="flex items-center gap-2 text-[12px] text-black/50 dark:text-white/50 print:hidden" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors flex items-center gap-1.5 font-medium text-black/70 dark:text-white/70">
                <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                <span>Dashboard</span>
            </a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-black/30 dark:text-white/30"></i>
            <span class="text-black/50 dark:text-white/50 flex items-center gap-1.5">
                <i data-lucide="users" class="w-3.5 h-3.5"></i>
                <span>Operasional Bisnis</span>
            </span>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-black/30 dark:text-white/30"></i>
            <span class="text-black dark:text-white font-semibold">
                Pusat Pelanggan &amp; Loyalitas CRM
            </span>
        </nav>

        <!-- 1. Header Hero Banner (Apple HIG Card Squircle) -->
        <div class="relative overflow-hidden rounded-[24px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] p-6 sm:p-7 shadow-[0_4px_24px_rgba(0,0,0,0.03)] transition-all">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                <div class="space-y-2 max-w-3xl">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                            <i data-lucide="heart-handshake" class="w-3.5 h-3.5"></i>
                            <span>Pusat Pelanggan &amp; Loyalitas</span>
                        </span>
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[11px] font-medium bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 border border-black/[0.06] dark:border-white/[0.08] tabular-nums">
                            {{ number_format($totalCustomers, 0, ',', '.') }} Terdaftar
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-black dark:text-white">
                        Pusat Pelanggan &amp; Loyalitas CRM
                    </h1>
                    <p class="text-[13px] sm:text-[14px] text-black/60 dark:text-white/60 leading-relaxed">
                        Kelola data klien komersial, pantau tier keanggotaan loyalty, riwayat poin belanja, pelunasan kasbon tempo, dan kode kupon diskon kasir dalam satu antarmuka terpadu.
                    </p>
                </div>

                <!-- Action CTA Buttons -->
                <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                    @if (\App\Support\Context::hasPermission('customers.create'))
                        <button type="button" @click="showAddModal = true"
                            class="h-11 sm:h-12 px-5 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] sm:text-[14px] font-semibold shadow-[0_2px_8px_rgba(0,122,255,0.3)] active:scale-[0.98] transition flex items-center justify-center gap-2 w-full sm:w-auto cursor-pointer">
                            <i data-lucide="user-plus" class="w-4 h-4"></i>
                            <span>+ Tambah Pelanggan Baru</span>
                        </button>
                    @endif

                    @if (\App\Support\Context::hasPermission('crm.manage'))
                        <button type="button" @click="showVoucherModal = true"
                            class="h-11 sm:h-12 px-4 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-[13px] sm:text-[14px] font-semibold active:scale-[0.98] transition flex items-center justify-center gap-2 w-full sm:w-auto cursor-pointer border border-black/[0.06] dark:border-white/[0.08]">
                            <i data-lucide="ticket-plus" class="w-4 h-4 text-[#FF9500]"></i>
                            <span>+ Buat Voucher Diskon</span>
                        </button>
                    @endif
                </div>
            </div>

            <!-- No-Panic Microcopy Banner -->
            <div class="mt-5 pt-4 border-t border-black/[0.05] dark:border-white/[0.06] flex items-center gap-2 text-[12px] text-black/55 dark:text-white/55">
                <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759] shrink-0"></i>
                <span>💡 <strong>Tenang:</strong> Seluruh riwayat transaksi, saldo poin, dan catatan piutang pelanggan Anda selalu aman dan terenkripsi otomatis di sistem.</span>
            </div>
        </div>

        <!-- 2. Bento Hero Metrics (3-Second Glanceability) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Bento Tile 1: Total Pelanggan -->
            <div class="p-5 rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-2 transition hover:-translate-y-0.5">
                <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase tracking-wider">
                    <span>Total Pelanggan</span>
                    <i data-lucide="users" class="w-4 h-4 text-[#007AFF]"></i>
                </div>
                <div class="text-2xl sm:text-3xl font-bold tracking-tight text-black dark:text-white tabular-nums">
                    {{ number_format($totalCustomers, 0, ',', '.') }}
                </div>
                <p class="text-[12px] text-black/50 dark:text-white/50">
                    {{ $totalCorporate }} Klien Perusahaan / B2B
                </p>
            </div>

            <!-- Bento Tile 2: Poin Loyalitas Beredar -->
            <div class="p-5 rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-2 transition hover:-translate-y-0.5">
                <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase tracking-wider">
                    <span>Poin Loyalty Beredar</span>
                    <i data-lucide="award" class="w-4 h-4 text-[#FF9500]"></i>
                </div>
                <div class="text-2xl sm:text-3xl font-bold tracking-tight text-[#FF9500] tabular-nums">
                    {{ number_format($totalPointsIssued, 0, ',', '.') }}
                </div>
                <p class="text-[12px] text-black/50 dark:text-white/50">
                    Siap ditukar diskon di kasir
                </p>
            </div>

            <!-- Bento Tile 3: Total Piutang Kasbon -->
            <div class="p-5 rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-2 transition hover:-translate-y-0.5">
                <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase tracking-wider">
                    <span>Piutang Kasbon Pelanggan</span>
                    <i data-lucide="receipt" class="w-4 h-4 text-[#FF3B30]"></i>
                </div>
                <div class="text-xl sm:text-2xl font-bold tracking-tight text-[#FF3B30] tabular-nums">
                    Rp {{ number_format($totalCreditReceivable, 0, ',', '.') }}
                </div>
                <p class="text-[12px] text-black/50 dark:text-white/50">
                    Rata-rata tempo {{ $avgPaymentTerms }} hari
                </p>
            </div>

            <!-- Bento Tile 4: Voucher Promosi -->
            <div class="p-5 rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-2 transition hover:-translate-y-0.5">
                <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase tracking-wider">
                    <span>Voucher Kasir POS</span>
                    <i data-lucide="ticket" class="w-4 h-4 text-[#34C759]"></i>
                </div>
                <div class="text-2xl sm:text-3xl font-bold tracking-tight text-[#34C759] tabular-nums">
                    {{ $activeVouchers }} <span class="text-sm font-normal text-black/40 dark:text-white/40">/ {{ $totalVouchers }}</span>
                </div>
                <p class="text-[12px] text-black/50 dark:text-white/50">
                    Kupon promo aktif berjalan
                </p>
            </div>
        </div>

        <!-- 3. Apple HIG Segmented Control Navigation -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 p-1.5 rounded-[16px] backdrop-blur-md bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.05] dark:border-white/[0.06]">
            <div class="inline-flex p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] gap-1">
                <button type="button" @click="switchTab('customers')"
                    :class="activeTab === 'customers' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_3px_rgba(0,0,0,0.1)] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                    class="h-9 px-4 rounded-[10px] text-[13px] transition-all flex items-center gap-2 cursor-pointer">
                    <i data-lucide="users" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Direktori Pelanggan</span>
                    <span class="ml-1 text-[11px] px-1.5 py-0.5 rounded-full bg-black/[0.06] dark:bg-white/[0.1] tabular-nums">{{ $customers->total() }}</span>
                </button>

                <button type="button" @click="switchTab('members')"
                    :class="activeTab === 'members' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_3px_rgba(0,0,0,0.1)] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                    class="h-9 px-4 rounded-[10px] text-[13px] transition-all flex items-center gap-2 cursor-pointer">
                    <i data-lucide="award" class="w-4 h-4 text-[#FF9500]"></i>
                    <span>Member &amp; Loyalitas Poin</span>
                    <span class="ml-1 text-[11px] px-1.5 py-0.5 rounded-full bg-black/[0.06] dark:bg-white/[0.1] tabular-nums">{{ $members->total() }}</span>
                </button>

                <button type="button" @click="switchTab('vouchers')"
                    :class="activeTab === 'vouchers' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_3px_rgba(0,0,0,0.1)] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                    class="h-9 px-4 rounded-[10px] text-[13px] transition-all flex items-center gap-2 cursor-pointer">
                    <i data-lucide="ticket" class="w-4 h-4 text-[#34C759]"></i>
                    <span>Voucher Diskon Kasir</span>
                    <span class="ml-1 text-[11px] px-1.5 py-0.5 rounded-full bg-black/[0.06] dark:bg-white/[0.1] tabular-nums">{{ $vouchers->total() }}</span>
                </button>
            </div>

            <div class="text-[12px] text-black/50 dark:text-white/50 px-3 text-right hidden sm:block">
                <span>Tab Aktif: <strong class="text-black dark:text-white capitalize" x-text="activeTab"></strong></span>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 1: DIREKTORI PELANGGAN & KLIEN KOMERSIAL                              -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'customers'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-4">
            <!-- Search & Filter Bar -->
            <div class="p-4 rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.02)]">
                <form method="GET" action="{{ route('customers.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
                    <input type="hidden" name="tab" value="customers">
                    <div class="relative flex-1 w-full">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nama pelanggan, nomor WhatsApp, nama perusahaan, atau email..."
                            class="w-full h-11 pl-10 pr-4 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] text-black dark:text-white placeholder-black/40 dark:placeholder-white/40 focus:outline-hidden focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 transition">
                    </div>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button type="submit"
                            class="h-11 px-5 rounded-[12px] bg-[#007AFF] text-white text-[13px] font-semibold hover:bg-[#0071E3] transition cursor-pointer flex items-center justify-center gap-2 flex-1 sm:flex-none">
                            <i data-lucide="search" class="w-4 h-4"></i>
                            <span>Cari Pelanggan</span>
                        </button>
                        @if (request('search'))
                            <a href="{{ route('customers.index', ['tab' => 'customers']) }}"
                                class="h-11 px-3.5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white text-[13px] font-semibold transition flex items-center justify-center cursor-pointer">
                                <span>Reset</span>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Customer Table Bento Container -->
            <div class="rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.02)] overflow-hidden">
                <div class="px-5 py-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <h2 class="text-[14px] font-bold text-black dark:text-white">Direktori Klien &amp; Pelanggan</h2>
                        <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-[#007AFF]/10 text-[#007AFF]">{{ $customers->total() }} Kontak</span>
                    </div>
                </div>
                @if ($customers->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-[13px]">
                            <thead>
                                <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02] text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">
                                    <th class="py-3.5 px-4 sm:px-6">Pelanggan / Klien</th>
                                    <th class="py-3.5 px-4">Kontak WhatsApp &amp; Email</th>
                                    <th class="py-3.5 px-4 text-center">Termin Tempo</th>
                                    <th class="py-3.5 px-4 text-center">Transaksi</th>
                                    <th class="py-3.5 px-4 text-right pr-6">Aksi Cepat</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-black/[0.05] dark:divide-white/[0.06]">
                                @foreach ($customers as $c)
                                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                        <!-- Customer Identity -->
                                        <td class="py-4 px-4 sm:px-6">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] font-bold flex items-center justify-center shrink-0 border border-[#007AFF]/20 text-[14px]">
                                                    {{ strtoupper(substr($c->name, 0, 2)) }}
                                                </div>
                                                <div class="space-y-0.5">
                                                    <div class="font-semibold text-black dark:text-white flex items-center gap-2">
                                                        <span>{{ $c->name }}</span>
                                                        @if ($c->code)
                                                            <span class="px-1.5 py-0.5 text-[10px] font-mono rounded-md bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60">{{ $c->code }}</span>
                                                        @endif
                                                    </div>
                                                    @if ($c->company_name)
                                                        <div class="text-[12px] text-black/55 dark:text-white/55 flex items-center gap-1">
                                                            <i data-lucide="building" class="w-3 h-3 text-black/40 dark:text-white/40"></i>
                                                            <span>{{ $c->company_name }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Contact Info & Quick WA -->
                                        <td class="py-4 px-4">
                                            <div class="space-y-1">
                                                @if ($c->phone)
                                                    @php
                                                        $cleanPhone = preg_replace('/[^0-9]/', '', $c->phone);
                                                        if (str_starts_with($cleanPhone, '0')) {
                                                            $cleanPhone = '62' . substr($cleanPhone, 1);
                                                        }
                                                    @endphp
                                                    <a href="https://wa.me/{{ $cleanPhone }}" target="_blank"
                                                        class="inline-flex items-center gap-1.5 font-mono text-[12px] text-[#34C759] hover:underline">
                                                        <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                                        <span>{{ $c->phone }}</span>
                                                    </a>
                                                @else
                                                    <span class="text-black/30 dark:text-white/30 text-[11px]">- Telepon Belum Diset -</span>
                                                @endif

                                                @if ($c->email)
                                                    <div class="text-[12px] text-black/50 dark:text-white/50 flex items-center gap-1">
                                                        <i data-lucide="mail" class="w-3 h-3 text-black/30 dark:text-white/30"></i>
                                                        <span class="truncate max-w-[180px]">{{ $c->email }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Payment Terms -->
                                        <td class="py-4 px-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 tabular-nums">
                                                {{ $c->payment_terms_days > 0 ? 'Net ' . $c->payment_terms_days . ' Hari' : 'Tunai / Cash' }}
                                            </span>
                                        </td>

                                        <!-- Transactions Count -->
                                        <td class="py-4 px-4 text-center">
                                            <div class="inline-flex flex-col text-[11px] font-mono">
                                                <span class="font-bold text-[#007AFF]">{{ $c->invoices_count }} Faktur</span>
                                                <span class="text-black/40 dark:text-white/40">{{ $c->purchase_orders_count }} PO</span>
                                            </div>
                                        </td>

                                        <!-- Action Buttons -->
                                        <td class="py-4 px-4 text-right pr-6">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <button type="button" @click="openDetailModal('{{ $c->id }}')"
                                                    class="h-8 px-2.5 rounded-[8px] text-[12px] font-semibold bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/70 dark:text-white/70 transition cursor-pointer flex items-center gap-1">
                                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                                    <span>Profil</span>
                                                </button>

                                                @if (\App\Support\Context::hasPermission('customers.edit'))
                                                    <button type="button" @click="openEditModal('{{ $c->id }}')"
                                                        class="h-8 px-2.5 rounded-[8px] text-[12px] font-semibold bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/20 transition cursor-pointer flex items-center gap-1">
                                                        <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                                        <span>Edit</span>
                                                    </button>
                                                @endif

                                                @if (\App\Support\Context::hasPermission('customers.delete'))
                                                    <form method="POST" action="{{ route('customers.destroy', $c->slug) }}"
                                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelanggan {{ addslashes($c->name) }}? Data historis transaksi akan tetap aman.')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="h-8 w-8 rounded-[8px] text-black/40 hover:text-[#FF3B30] hover:bg-[#FF3B30]/10 transition flex items-center justify-center cursor-pointer">
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

                    <!-- Pagination Customers -->
                    @if ($customers->hasPages())
                        <div class="p-4 border-t border-black/[0.05] dark:border-white/[0.06]">
                            {{ $customers->links() }}
                        </div>
                    @endif
                @else
                    <div class="py-16 text-center space-y-3">
                        <div class="w-14 h-14 rounded-full bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center mx-auto text-black/40 dark:text-white/40">
                            <i data-lucide="users" class="w-7 h-7"></i>
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-[15px] font-bold text-black dark:text-white">Belum Ada Pelanggan Ditemukan</h4>
                            <p class="text-[13px] text-black/50 dark:text-white/50 max-w-sm mx-auto">
                                Mulai tambahkan kontak pelanggan untuk mencatat penjualan kasir, bon tempo, dan faktur resmi.
                            </p>
                        </div>
                        @if (\App\Support\Context::hasPermission('customers.create'))
                            <div class="pt-2">
                                <button type="button" @click="showAddModal = true"
                                    class="h-11 px-5 rounded-[12px] bg-[#007AFF] text-white text-[13px] font-semibold hover:bg-[#0071E3] transition cursor-pointer">
                                    + Tambah Pelanggan Pertama
                                </button>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 2: MEMBERSHIP, POIN LOYALITAS & PELUNASAN PIUTANG                     -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'members'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-4">
            <!-- Filter Bar for Members -->
            <div class="p-4 rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.02)]">
                <form method="GET" action="{{ route('customers.index') }}" class="flex flex-col lg:flex-row items-center gap-3">
                    <input type="hidden" name="tab" value="members">

                    <div class="relative flex-1 w-full">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                        <input type="text" name="member_search" value="{{ request('member_search') }}"
                            placeholder="Cari nama member, nomor telepon, atau email..."
                            class="w-full h-11 pl-10 pr-4 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] text-black dark:text-white placeholder-black/40 dark:placeholder-white/40 focus:outline-hidden focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 transition">
                    </div>

                    <div class="flex items-center gap-2 w-full lg:w-auto">
                        <select name="tier"
                            class="h-11 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[13px] font-medium text-black dark:text-white focus:outline-hidden">
                            <option value="all">Semua Tier</option>
                            <option value="Bronze" {{ request('tier') === 'Bronze' ? 'selected' : '' }}>Bronze</option>
                            <option value="Silver" {{ request('tier') === 'Silver' ? 'selected' : '' }}>Silver</option>
                            <option value="Gold" {{ request('tier') === 'Gold' ? 'selected' : '' }}>Gold</option>
                            <option value="Platinum" {{ request('tier') === 'Platinum' ? 'selected' : '' }}>Platinum</option>
                        </select>

                        <select name="segment"
                            class="h-11 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[13px] font-medium text-black dark:text-white focus:outline-hidden">
                            <option value="all">Semua Segmen</option>
                            <option value="VIP" {{ request('segment') === 'VIP' ? 'selected' : '' }}>VIP</option>
                            <option value="Regular" {{ request('segment') === 'Regular' ? 'selected' : '' }}>Regular</option>
                            <option value="New" {{ request('segment') === 'New' ? 'selected' : '' }}>New</option>
                            <option value="At Risk" {{ request('segment') === 'At Risk' ? 'selected' : '' }}>At Risk</option>
                        </select>

                        <button type="submit"
                            class="h-11 px-4 rounded-[12px] bg-[#007AFF] text-white text-[13px] font-semibold hover:bg-[#0071E3] transition cursor-pointer flex items-center justify-center gap-1.5 shrink-0">
                            <i data-lucide="filter" class="w-4 h-4"></i>
                            <span>Filter</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Members List Table -->
            <div class="rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.02)] overflow-hidden">
                @if ($members->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-[13px]">
                            <thead>
                                <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02] text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">
                                    <th class="py-3.5 px-4 sm:px-6">Member Pelanggan</th>
                                    <th class="py-3.5 px-4">Tier &amp; Segmen</th>
                                    <th class="py-3.5 px-4 text-center">Saldo Poin Belanja</th>
                                    <th class="py-3.5 px-4 text-right">Total Belanja (Lifetime)</th>
                                    <th class="py-3.5 px-4 text-right">Piutang Kasbon</th>
                                    <th class="py-3.5 px-4 text-right pr-6">Aksi Member</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-black/[0.05] dark:divide-white/[0.06]">
                                @foreach ($members as $m)
                                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                        <!-- Member Name -->
                                        <td class="py-4 px-4 sm:px-6">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-[12px] bg-[#FF9500]/10 text-[#FF9500] font-bold flex items-center justify-center shrink-0 border border-[#FF9500]/20 text-[14px]">
                                                    {{ strtoupper(substr($m->name, 0, 2)) }}
                                                </div>
                                                <div class="space-y-0.5">
                                                    <div class="font-semibold text-black dark:text-white">{{ $m->name }}</div>
                                                    <div class="text-[12px] font-mono text-black/50 dark:text-white/50">{{ $m->phone ?: '-' }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Tier & Segment Badges -->
                                        <td class="py-4 px-4">
                                            <div class="flex flex-wrap items-center gap-1.5">
                                                @php
                                                    $tier = $m->membership_tier ?: 'Bronze';
                                                    $tierColors = [
                                                        'Platinum' => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20',
                                                        'Gold' => 'bg-[#FF9500]/10 text-[#FF9500] border-[#FF9500]/20',
                                                        'Silver' => 'bg-slate-500/10 text-slate-600 dark:text-slate-300 border-slate-500/20',
                                                        'Bronze' => 'bg-amber-600/10 text-amber-700 dark:text-amber-400 border-amber-600/20',
                                                    ];
                                                    $tColor = $tierColors[$tier] ?? 'bg-black/5 text-black/60 border-black/10';
                                                @endphp
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $tColor }}">
                                                    <i data-lucide="award" class="w-3 h-3"></i>
                                                    <span>{{ $tier }}</span>
                                                </span>

                                                @if ($m->segment)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60">
                                                        {{ $m->segment }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Points Balance & Action -->
                                        <td class="py-4 px-4 text-center">
                                            <div class="space-y-1">
                                                <div class="text-[15px] font-bold text-[#FF9500] tabular-nums font-mono">
                                                    {{ number_format($m->points_balance, 0, ',', '.') }}
                                                </div>
                                                <button type="button" @click="openPointHistory(@js($m))"
                                                    class="inline-flex items-center gap-1 text-[11px] text-[#007AFF] hover:underline font-semibold cursor-pointer">
                                                    <i data-lucide="history" class="w-3 h-3"></i>
                                                    <span>Riwayat Poin</span>
                                                </button>
                                            </div>
                                        </td>

                                        <!-- Total Spent -->
                                        <td class="py-4 px-4 text-right font-mono tabular-nums">
                                            <div class="font-semibold text-black dark:text-white">
                                                Rp {{ number_format($m->total_spent, 0, ',', '.') }}
                                            </div>
                                            <span class="text-[11px] text-black/40 dark:text-white/40">{{ $m->pos_orders_count }} Order Kasir</span>
                                        </td>

                                        <!-- Credit Debt Balance -->
                                        <td class="py-4 px-4 text-right">
                                            @if ($m->current_credit_balance > 0)
                                                <div class="space-y-1">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/10 text-[#FF3B30] border border-[#FF3B30]/20 font-mono tabular-nums">
                                                        Rp {{ number_format($m->current_credit_balance, 0, ',', '.') }}
                                                    </span>
                                                    @if (\App\Support\Context::hasPermission('crm.manage'))
                                                        <div>
                                                            <button type="button" @click="openCreditModal(@js($m))"
                                                                class="inline-flex items-center gap-1 text-[11px] font-bold text-[#34C759] hover:underline cursor-pointer">
                                                                <i data-lucide="banknote" class="w-3.5 h-3.5"></i>
                                                                <span>Bayar Kasbon</span>
                                                            </button>
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-[#34C759]">
                                                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                                    <span>Lunas (Rp 0)</span>
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Action Column -->
                                        <td class="py-4 px-4 text-right pr-6">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <button type="button" @click="openDetailModal('{{ $m->id }}')"
                                                    class="h-8 px-2.5 rounded-[8px] text-[12px] font-semibold bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/70 dark:text-white/70 transition cursor-pointer flex items-center gap-1">
                                                    <i data-lucide="user" class="w-3.5 h-3.5"></i>
                                                    <span>Profil</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($members->hasPages())
                        <div class="p-4 border-t border-black/[0.05] dark:border-white/[0.06]">
                            {{ $members->links() }}
                        </div>
                    @endif
                @else
                    <div class="py-16 text-center space-y-3">
                        <div class="w-14 h-14 rounded-full bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center mx-auto text-black/40 dark:text-white/40">
                            <i data-lucide="award" class="w-7 h-7"></i>
                        </div>
                        <h4 class="text-[15px] font-bold text-black dark:text-white">Tidak Ada Data Member Sesuai Filter</h4>
                        <p class="text-[13px] text-black/50 dark:text-white/50 max-w-sm mx-auto">
                            Coba sesuaikan filter tier atau segmen di atas untuk menampilkan member lainnya.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 3: VOUCHER DISKON KASIR POS                                           -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'vouchers'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-4">
            <!-- Voucher Top Banner -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-5 rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.02)]">
                <div class="space-y-1">
                    <h3 class="text-base font-bold text-black dark:text-white">Kupon &amp; Voucher Diskon Promosi</h3>
                    <p class="text-[13px] text-black/60 dark:text-white/60">
                        Kode kupon dapat diinput oleh kasir pada saat checkout POS untuk memberikan potongan harga otomatis.
                    </p>
                </div>
                @if (\App\Support\Context::hasPermission('crm.manage'))
                    <button type="button" @click="showVoucherModal = true"
                        class="h-11 px-5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-semibold transition cursor-pointer flex items-center gap-2 shrink-0">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>+ Buat Kupon Baru</span>
                    </button>
                @endif
            </div>

            <!-- Vouchers Grid Bento -->
            @if ($vouchers->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($vouchers as $v)
                        <div class="rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-[0_2px_12px_rgba(0,0,0,0.02)] relative space-y-4 transition hover:-translate-y-0.5">
                            <!-- Header Voucher Card -->
                            <div class="flex items-start justify-between gap-2">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-base font-black tracking-wider text-black dark:text-white bg-black/[0.05] dark:bg-white/[0.08] px-2.5 py-1 rounded-[8px] border border-black/[0.08] dark:border-white/[0.1]">
                                            {{ $v->code }}
                                        </span>
                                        <button type="button" @click="copyVoucher('{{ $v->code }}')"
                                            class="p-1.5 rounded-[8px] text-black/40 hover:text-[#007AFF] hover:bg-[#007AFF]/10 transition cursor-pointer" title="Salin Kode">
                                            <i data-lucide="copy" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                    <h4 class="font-bold text-[14px] text-black dark:text-white">{{ $v->name }}</h4>
                                </div>

                                <!-- Status Badge -->
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $v->is_active ? 'bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20' : 'bg-black/10 dark:bg-white/10 text-black/50 dark:text-white/50' }}">
                                    {{ $v->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>

                            <!-- Discount Detail -->
                            <div class="p-3 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-1">
                                <div class="text-[11px] text-black/50 dark:text-white/50 uppercase font-semibold">Potongan Diskon</div>
                                <div class="text-xl font-bold text-[#FF9500]">
                                    @if ($v->discount_type === 'percentage')
                                        {{ $v->discount_value }}%
                                        @if ($v->max_discount_amount)
                                            <span class="text-xs font-normal text-black/50 dark:text-white/50">(Maks. Rp {{ number_format($v->max_discount_amount, 0, ',', '.') }})</span>
                                        @endif
                                    @else
                                        Rp {{ number_format($v->discount_value, 0, ',', '.') }}
                                    @endif
                                </div>
                                <div class="text-[11px] text-black/60 dark:text-white/60">
                                    Min. Belanja: <strong>Rp {{ number_format($v->min_order_amount ?: 0, 0, ',', '.') }}</strong>
                                </div>
                            </div>

                            <!-- Usage & Validity Info -->
                            <div class="text-[12px] space-y-1 text-black/60 dark:text-white/60">
                                <div class="flex items-center justify-between">
                                    <span>Pemakaian:</span>
                                    <span class="font-mono font-bold text-black dark:text-white tabular-nums">
                                        {{ $v->used_count }} / {{ $v->usage_limit ?: '∞' }} kali
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>Berlaku s/d:</span>
                                    <span>{{ $v->valid_until ? $v->valid_until->format('d M Y') : 'Tanpa Batas Waktu' }}</span>
                                </div>
                            </div>

                            <!-- Toggle Status Button -->
                            @if (\App\Support\Context::hasPermission('crm.manage'))
                                <div class="pt-2 border-t border-black/[0.05] dark:border-white/[0.06]">
                                    <form method="POST" action="{{ route('crm.vouchers.toggle', $v->id) }}">
                                        @csrf
                                        <button type="submit"
                                            class="w-full h-9 rounded-[10px] text-[12px] font-semibold transition cursor-pointer flex items-center justify-center gap-1.5 {{ $v->is_active ? 'bg-[#FF3B30]/10 text-[#FF3B30] hover:bg-[#FF3B30]/20' : 'bg-[#34C759]/10 text-[#34C759] hover:bg-[#34C759]/20' }}">
                                            <i data-lucide="{{ $v->is_active ? 'power-off' : 'power' }}" class="w-3.5 h-3.5"></i>
                                            <span>{{ $v->is_active ? 'Nonaktifkan Kupon' : 'Aktifkan Kupon' }}</span>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if ($vouchers->hasPages())
                    <div class="p-4 rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08]">
                        {{ $vouchers->links() }}
                    </div>
                @endif
            @else
                <div class="p-12 text-center rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                    <div class="w-14 h-14 rounded-full bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center mx-auto text-black/40 dark:text-white/40">
                        <i data-lucide="ticket" class="w-7 h-7"></i>
                    </div>
                    <h4 class="text-[15px] font-bold text-black dark:text-white">Belum Ada Kupon Diskon Aktif</h4>
                    <p class="text-[13px] text-black/50 dark:text-white/50 max-w-sm mx-auto">
                        Buat kode voucher promo untuk menarik pelanggan kembali berbelanja di kasir POS Anda.
                    </p>
                    @if (\App\Support\Context::hasPermission('crm.manage'))
                        <div class="pt-2">
                            <button type="button" @click="showVoucherModal = true"
                                class="h-11 px-5 rounded-[12px] bg-[#007AFF] text-white text-[13px] font-semibold hover:bg-[#0071E3] transition cursor-pointer">
                                + Buat Kupon Diskon Pertama
                            </button>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- ========================================================================= -->
        <!-- MODALS (APPLE HIG ACTION SHEETS & FLOATING DIALOGS)                       -->
        <!-- ========================================================================= -->

        <!-- MODAL 1: TAMBAH PELANGGAN (DENGAN KAIDAH 3 INPUT POKOK) -->
        @if (\App\Support\Context::hasPermission('customers.create'))
            <div x-show="showAddModal" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 transition-all"
                @keydown.escape.window="showAddModal = false">
                <div class="w-full max-w-xl p-6 sm:p-7 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-2xl space-y-5 relative max-h-[90vh] overflow-y-auto"
                    @click.away="showAddModal = false">
                    
                    <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                                <i data-lucide="user-plus" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-black dark:text-white">Tambah Pelanggan Baru</h3>
                                <p class="text-[11px] text-black/50 dark:text-white/50">Lengkapi data dasar untuk mulai melayani pelanggan.</p>
                            </div>
                        </div>
                        <button type="button" @click="showAddModal = false" class="text-black/40 hover:text-black dark:hover:text-white p-1 rounded-lg">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('customers.store') }}" class="space-y-4">
                        @csrf

                        <!-- 3 Input Pokok Ramah Boomer -->
                        <div class="space-y-3.5">
                            <!-- Input 1: Nama Pelanggan -->
                            <div class="space-y-1">
                                <label class="block text-[13px] font-bold text-black dark:text-white">
                                    1. Nama Lengkap Pelanggan <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="text" name="name" required placeholder="Contoh: Pak Haji Bambang / Ibu Rina"
                                    class="w-full h-12 px-4 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 transition">
                            </div>

                            <!-- Input 2: Nomor WhatsApp / Telepon -->
                            <div class="space-y-1">
                                <label class="block text-[13px] font-bold text-black dark:text-white">
                                    2. Nomor WhatsApp / Handphone <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="tel" name="phone" required placeholder="Contoh: 081234567890"
                                    class="w-full h-12 px-4 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] font-mono text-black dark:text-white focus:outline-hidden focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 transition">
                                <span class="text-[11px] text-black/50 dark:text-white/50">Digunakan untuk kirim nota struk otomatis dan reminder jatuh tempo.</span>
                            </div>

                            <!-- Input 3: Tipe Pelanggan / Perusahaan -->
                            <div class="space-y-1">
                                <label class="block text-[13px] font-bold text-black dark:text-white">
                                    3. Nama Perusahaan / Toko <span class="text-black/40 dark:text-white/40 text-[11px] font-normal">(Kosongkan jika perorangan)</span>
                                </label>
                                <input type="text" name="company_name" placeholder="Contoh: CV Berkah Jaya"
                                    class="w-full h-12 px-4 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 transition">
                            </div>
                        </div>

                        <!-- Akordeon Pilihan Lanjutan (Anti-Intimidasi Form) -->
                        <div x-data="{ openAdvance: false }" class="pt-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                            <button type="button" @click="openAdvance = !openAdvance"
                                class="flex items-center justify-between w-full py-2 text-[13px] font-semibold text-[#007AFF] hover:underline cursor-pointer">
                                <span>⚙️ Atur Termin Tempo, Email &amp; Alamat (Opsional)</span>
                                <i data-lucide="chevron-down" :class="openAdvance ? 'rotate-180' : ''" class="w-4 h-4 transition-transform"></i>
                            </button>

                            <div x-show="openAdvance" x-collapse class="space-y-3 pt-3">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="space-y-1">
                                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">Email Pelanggan</label>
                                        <input type="email" name="email" placeholder="nama@email.com"
                                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-hidden">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">Termin Tempo (Hari)</label>
                                        <input type="number" name="payment_terms_days" value="30" min="0" max="365"
                                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-hidden">
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">Alamat Lengkap</label>
                                    <textarea name="billing_address" rows="2" placeholder="Alamat jalan, nomor ruko, kota..."
                                        class="w-full p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-hidden"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button Primary -->
                        <div class="pt-3">
                            <button type="submit"
                                class="w-full h-12 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[14px] font-bold shadow-[0_2px_8px_rgba(0,122,255,0.3)] active:scale-[0.98] transition cursor-pointer">
                                💾 Simpan Pelanggan Baru
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- MODAL 2: EDIT PELANGGAN -->
        <div x-show="showEditModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 transition-all"
            @keydown.escape.window="showEditModal = false">
            <div class="w-full max-w-xl p-6 sm:p-7 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-2xl space-y-5 relative max-h-[90vh] overflow-y-auto"
                @click.away="showEditModal = false">
                <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                    <h3 class="text-base font-bold text-black dark:text-white">Edit Profil Pelanggan</h3>
                    <button type="button" @click="showEditModal = false" class="text-black/40 hover:text-black dark:hover:text-white p-1">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form method="POST" :action="'/customers/' + editCustomer.slug" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="space-y-1">
                        <label class="block text-[12px] font-bold text-black dark:text-white">Nama Pelanggan <span class="text-[#FF3B30]">*</span></label>
                        <input type="text" name="name" x-model="editCustomer.name" required
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-hidden">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="block text-[12px] font-bold text-black dark:text-white">Nomor WhatsApp / HP</label>
                            <input type="text" name="phone" x-model="editCustomer.phone"
                                class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] font-mono text-black dark:text-white focus:outline-hidden">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[12px] font-bold text-black dark:text-white">Perusahaan / Instansi</label>
                            <input type="text" name="company_name" x-model="editCustomer.company_name"
                                class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-hidden">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="block text-[12px] font-bold text-black dark:text-white">Email</label>
                            <input type="email" name="email" x-model="editCustomer.email"
                                class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-hidden">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[12px] font-bold text-black dark:text-white">Termin Tempo (Hari)</label>
                            <input type="number" name="payment_terms_days" x-model="editCustomer.payment_terms_days" min="0" max="365"
                                class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-hidden">
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[12px] font-bold text-black dark:text-white">Alamat Penagihan / Pengiriman</label>
                        <textarea name="billing_address" x-model="editCustomer.billing_address" rows="2"
                            class="w-full p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-hidden"></textarea>
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                            class="w-full h-12 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[14px] font-bold transition cursor-pointer">
                            💾 Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 3: DETAIL PROFIL PELANGGAN -->
        <div x-show="showDetailModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 transition-all"
            @keydown.escape.window="showDetailModal = false">
            <div class="w-full max-w-lg p-6 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-2xl space-y-5 relative max-h-[90vh] overflow-y-auto"
                @click.away="showDetailModal = false">
                <template x-if="selectedCustomer">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] font-bold text-lg flex items-center justify-center">
                                    <span x-text="selectedCustomer.name.substring(0,2).toUpperCase()"></span>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-black dark:text-white" x-text="selectedCustomer.name"></h3>
                                    <p class="text-[12px] text-black/50 dark:text-white/50" x-text="selectedCustomer.company_name || 'Pelanggan Perorangan'"></p>
                                </div>
                            </div>
                            <button type="button" @click="showDetailModal = false" class="text-black/40 hover:text-black dark:hover:text-white p-1">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <div class="space-y-2.5 text-[13px]">
                            <div class="flex justify-between py-1.5 border-b border-black/[0.04] dark:border-white/[0.05]">
                                <span class="text-black/50 dark:text-white/50">WhatsApp:</span>
                                <span class="font-mono font-semibold text-black dark:text-white" x-text="selectedCustomer.phone || '-'"></span>
                            </div>
                            <div class="flex justify-between py-1.5 border-b border-black/[0.04] dark:border-white/[0.05]">
                                <span class="text-black/50 dark:text-white/50">Email:</span>
                                <span class="text-black dark:text-white" x-text="selectedCustomer.email || '-'"></span>
                            </div>
                            <div class="flex justify-between py-1.5 border-b border-black/[0.04] dark:border-white/[0.05]">
                                <span class="text-black/50 dark:text-white/50">Termin Tempo:</span>
                                <span class="font-bold text-black dark:text-white" x-text="(selectedCustomer.payment_terms_days || 30) + ' Hari'"></span>
                            </div>
                            <div class="flex justify-between py-1.5 border-b border-black/[0.04] dark:border-white/[0.05]">
                                <span class="text-black/50 dark:text-white/50">Saldo Piutang Kasbon:</span>
                                <span class="font-mono font-bold text-[#FF3B30]" x-text="'Rp ' + formatCurrency(selectedCustomer.current_credit_balance || 0)"></span>
                            </div>
                            <div class="flex justify-between py-1.5 border-b border-black/[0.04] dark:border-white/[0.05]">
                                <span class="text-black/50 dark:text-white/50">Poin Loyalitas:</span>
                                <span class="font-mono font-bold text-[#FF9500]" x-text="formatCurrency(selectedCustomer.points_balance || 0) + ' Poin'"></span>
                            </div>
                            <div class="py-1.5">
                                <span class="block text-black/50 dark:text-white/50 text-[11px] uppercase font-semibold">Alamat:</span>
                                <p class="text-[13px] text-black/70 dark:text-white/70 pt-0.5" x-text="selectedCustomer.billing_address || 'Belum mencantumkan alamat'"></p>
                            </div>
                        </div>

                        <div class="pt-2 flex items-center gap-2">
                            <button type="button" @click="showDetailModal = false; openEditModal(selectedCustomer.id)"
                                class="flex-1 h-11 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] text-black dark:text-white font-semibold text-[13px]">
                                Edit Data
                            </button>
                            <button type="button" @click="showDetailModal = false"
                                class="h-11 px-5 rounded-[12px] bg-[#007AFF] text-white font-semibold text-[13px]">
                                Tutup
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- MODAL 4: RECORD CREDIT PAYMENT (BAYAR PIUTANG KASBON) -->
        <div x-show="showCreditModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 transition-all"
            @keydown.escape.window="showCreditModal = false">
            <div class="w-full max-w-md p-6 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-2xl space-y-4 relative"
                @click.away="showCreditModal = false">
                <template x-if="selectedCustomer">
                    <div>
                        <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                            <div class="flex items-center gap-2">
                                <div class="w-9 h-9 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                                    <i data-lucide="banknote" class="w-5 h-5"></i>
                                </div>
                                <h3 class="text-base font-bold text-black dark:text-white">Pelunasan Piutang Kasbon</h3>
                            </div>
                            <button type="button" @click="showCreditModal = false" class="text-black/40 hover:text-black dark:hover:text-white">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <form method="POST" :action="'/crm/customers/' + selectedCustomer.id + '/credit-payment'" class="space-y-4 pt-3">
                            @csrf
                            <input type="hidden" name="amount" :value="creditRawAmount">

                            <!-- Info Sisa Tagihan -->
                            <div class="p-3.5 rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 space-y-1">
                                <div class="text-[11px] font-semibold text-[#FF3B30] uppercase">Sisa Tagihan Piutang Saat Ini</div>
                                <div class="text-xl font-bold font-mono text-[#FF3B30] tabular-nums" x-text="'Rp ' + formatCurrency(selectedCustomer.current_credit_balance || 0)"></div>
                                <div class="text-[12px] text-black/60 dark:text-white/60" x-text="'Atas nama: ' + selectedCustomer.name"></div>
                            </div>

                            <!-- Input Nominal dengan Format Ribuan Otomatis -->
                            <div class="space-y-1.5">
                                <label class="block text-[13px] font-bold text-black dark:text-white">
                                    Nominal Pembayaran Diterima (Rp) <span class="text-[#FF3B30]">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-bold text-black/40 dark:text-white/40">Rp</span>
                                    <input type="text" x-model="creditRepayAmount" @input="onCreditInput($event)" required
                                        placeholder="0"
                                        class="w-full h-12 pl-12 pr-4 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[18px] font-bold font-mono text-black dark:text-white focus:outline-hidden focus:border-[#34C759] focus:ring-2 focus:ring-[#34C759]/20 transition">
                                </div>
                            </div>

                            <!-- Tombol Cepat Pecahan -->
                            <div class="flex items-center gap-2">
                                <button type="button" @click="setQuickCredit(25)"
                                    class="flex-1 py-1.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] text-[11px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/[0.08] cursor-pointer">
                                    25%
                                </button>
                                <button type="button" @click="setQuickCredit(50)"
                                    class="flex-1 py-1.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] text-[11px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/[0.08] cursor-pointer">
                                    50%
                                </button>
                                <button type="button" @click="setQuickCredit(100)"
                                    class="flex-1 py-1.5 rounded-[8px] bg-[#34C759]/10 text-[#34C759] text-[11px] font-bold hover:bg-[#34C759]/20 cursor-pointer">
                                    100% (Lunas)
                                </button>
                            </div>

                            <!-- Catatan Pelunasan -->
                            <div class="space-y-1">
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">Catatan Kasir / Pembayaran</label>
                                <input type="text" name="notes" x-model="creditNotes" placeholder="Contoh: Diterima tunai di kasir"
                                    class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-hidden">
                            </div>

                            <div class="pt-2">
                                <button type="submit" :disabled="creditRawAmount <= 0"
                                    class="w-full h-12 rounded-[14px] bg-[#34C759] hover:bg-[#2DB04D] disabled:opacity-50 text-white font-bold text-[14px] shadow-[0_2px_8px_rgba(52,199,89,0.3)] transition cursor-pointer">
                                    💵 Catat Pelunasan Piutang
                                </button>
                            </div>
                        </form>
                    </div>
                </template>
            </div>
        </div>

        <!-- MODAL 5: RIWAYAT POIN LOYALITAS -->
        <div x-show="showPointsModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 transition-all"
            @keydown.escape.window="showPointsModal = false">
            <div class="w-full max-w-lg p-6 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-2xl space-y-4 relative max-h-[85vh] overflow-y-auto"
                @click.away="showPointsModal = false">
                <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                    <div class="flex items-center gap-2">
                        <i data-lucide="history" class="w-5 h-5 text-[#FF9500]"></i>
                        <h3 class="text-base font-bold text-black dark:text-white">Riwayat Poin Loyalitas</h3>
                    </div>
                    <button type="button" @click="showPointsModal = false" class="text-black/40 hover:text-black dark:hover:text-white">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <template x-if="selectedCustomer">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04]">
                            <div>
                                <span class="text-[12px] text-black/50 dark:text-white/50">Member:</span>
                                <h4 class="font-bold text-black dark:text-white" x-text="selectedCustomer.name"></h4>
                            </div>
                            <div class="text-right">
                                <span class="text-[11px] text-black/50 dark:text-white/50">Total Saldo:</span>
                                <div class="font-mono font-bold text-[#FF9500] text-base" x-text="formatCurrency(selectedCustomer.points_balance || 0) + ' Poin'"></div>
                            </div>
                        </div>

                        <!-- Loading State -->
                        <div x-show="loadingPoints" class="py-8 text-center text-black/40 dark:text-white/40 text-xs">
                            <i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto mb-2 text-[#007AFF]"></i>
                            <span>Memuat catatan mutasi poin...</span>
                        </div>

                        <!-- Histories List -->
                        <div x-show="!loadingPoints && pointHistories.length > 0" class="space-y-2">
                            <template x-for="hist in pointHistories" :key="hist.id">
                                <div class="p-3 rounded-[12px] border border-black/[0.05] dark:border-white/[0.06] flex items-center justify-between">
                                    <div class="space-y-0.5">
                                        <div class="text-[13px] font-semibold text-black dark:text-white" x-text="hist.description || 'Penyesuaian Poin Belanja'"></div>
                                        <div class="text-[11px] text-black/40 dark:text-white/40" x-text="new Date(hist.created_at).toLocaleString('id-ID')"></div>
                                    </div>
                                    <div class="text-right font-mono font-bold"
                                        :class="hist.points_change >= 0 ? 'text-[#34C759]' : 'text-[#FF3B30]'"
                                        x-text="(hist.points_change >= 0 ? '+' : '') + formatCurrency(hist.points_change)">
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div x-show="!loadingPoints && pointHistories.length === 0" class="py-8 text-center text-black/40 dark:text-white/40 text-xs">
                            Belum ada riwayat mutasi poin untuk pelanggan ini.
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- MODAL 6: BUAT VOUCHER DISKON BARU -->
        @if (\App\Support\Context::hasPermission('crm.manage'))
            <div x-show="showVoucherModal" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 transition-all"
                @keydown.escape.window="showVoucherModal = false">
                <div class="w-full max-w-lg p-6 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-2xl space-y-4 relative max-h-[90vh] overflow-y-auto"
                    @click.away="showVoucherModal = false">
                    <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-9 h-9 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                                <i data-lucide="ticket-plus" class="w-5 h-5"></i>
                            </div>
                            <h3 class="text-base font-bold text-black dark:text-white">Buat Kode Kupon Voucher Baru</h3>
                        </div>
                        <button type="button" @click="showVoucherModal = false" class="text-black/40 hover:text-black dark:hover:text-white">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('crm.vouchers.store') }}" class="space-y-4 pt-2">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="block text-[12px] font-bold text-black dark:text-white">Kode Kupon (Ketik Huruf Besar) <span class="text-[#FF3B30]">*</span></label>
                                <input type="text" name="code" required placeholder="DISKON10"
                                    class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] font-mono uppercase font-bold text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF]">
                            </div>

                            <div class="space-y-1">
                                <label class="block text-[12px] font-bold text-black dark:text-white">Nama Kupon Promo <span class="text-[#FF3B30]">*</span></label>
                                <input type="text" name="name" required placeholder="Contoh: Promo Akhir Pekan"
                                    class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF]">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="block text-[12px] font-bold text-black dark:text-white">Tipe Diskon</label>
                                <select name="discount_type"
                                    class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[13px] font-semibold text-black dark:text-white focus:outline-hidden">
                                    <option value="percentage">Persentase (%)</option>
                                    <option value="fixed">Nominal Tetap (Rp)</option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-[12px] font-bold text-black dark:text-white">Nilai Diskon (% atau Rp) <span class="text-[#FF3B30]">*</span></label>
                                <input type="number" name="discount_value" required min="0" step="any" placeholder="Contoh: 10 atau 15000"
                                    class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] font-mono font-bold text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-hidden">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">Min. Belanja Transaksi (Rp)</label>
                                <input type="number" name="min_order_amount" min="0" placeholder="0"
                                    class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] font-mono text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-hidden">
                            </div>

                            <div class="space-y-1">
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">Maks. Potongan (Jika %)</label>
                                <input type="number" name="max_discount_amount" min="0" placeholder="Contoh: 25000"
                                    class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] font-mono text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-hidden">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">Batas Kuota Pemakaian</label>
                                <input type="number" name="usage_limit" min="1" placeholder="Kosongkan jika tak terbatas"
                                    class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-hidden">
                            </div>

                            <div class="space-y-1">
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">Berlaku Sampai Tanggal</label>
                                <input type="date" name="valid_until"
                                    class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[13px] text-black dark:text-white focus:outline-hidden">
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit"
                                class="w-full h-12 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-bold text-[14px] shadow-[0_2px_8px_rgba(0,122,255,0.3)] transition cursor-pointer">
                                🎟️ Terbitkan Voucher Promo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

    </div>
@endsection
