@extends('layouts.app', [
    'title' => 'Riwayat Pembayaran Langganan — Cooca UMKM',
    'headerTitle' => 'Riwayat Pembayaran & Tagihan',
    'headerSubtitle' => 'Daftar seluruh transaksi langganan, top-up kuota, dan status verifikasi bisnis Anda'
])

@section('content')
<div class="space-y-6 pb-12" x-data="{
    searchQuery: '',
    statusFilter: 'all',
    matchesFilter(orderNumber, packageName, status) {
        const matchesSearch = !this.searchQuery || 
            (orderNumber && orderNumber.toLowerCase().includes(this.searchQuery.toLowerCase())) ||
            (packageName && packageName.toLowerCase().includes(this.searchQuery.toLowerCase()));
        
        const matchesStatus = this.statusFilter === 'all' || status === this.statusFilter;
        return matchesSearch && matchesStatus;
    }
}">

    <!-- 0. Standard Breadcrumb Bar -->
    <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 print:hidden" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors flex items-center gap-1.5 font-bold text-slate-900 dark:text-white">
            <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
            <span>Dashboard</span>
        </a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600"></i>
        <a href="{{ route('billing.limits') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
            <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
            <span>Langganan &amp; Billing</span>
        </a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600"></i>
        <span class="text-slate-900 dark:text-white font-bold flex items-center gap-1.5">
            <span>Riwayat Tagihan</span>
        </span>
    </nav>

    <!-- 1. Top Header Banner (Seukuran Dashboard Penuh) -->
    <div class="bg-white dark:bg-slate-900/90 p-5 sm:p-6 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 transition-colors">
        <div class="space-y-1.5 max-w-3xl">
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full px-2.5 py-0.5 text-[10px] sm:text-xs font-bold border inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200/90 dark:border-emerald-800/90">
                    <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                    <span>Riwayat Tagihan &amp; Pembayaran</span>
                </span>
                <span class="rounded-full px-2.5 py-0.5 text-[10px] sm:text-xs font-bold border inline-flex items-center gap-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 font-mono">
                    Total: {{ number_format($payments->total(), 0, ',', '.') }} Transaksi
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                Riwayat Pembayaran &amp; Tagihan Bisnis
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                Pantau seluruh rekam jejak pesanan langganan SaaS, bukti mutasi transfer bank, invoice resmi, dan status verifikasi akun.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
            <a href="{{ route('billing.limits') }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/80 transition cursor-pointer shadow-2xs inline-flex items-center justify-center gap-1.5 flex-1 sm:flex-none">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Paket &amp; Kuota</span>
            </a>
            <a href="{{ route('billing.checkout') }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm shadow-emerald-600/20 active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-1.5 flex-1 sm:flex-none">
                <i data-lucide="zap" class="w-4 h-4"></i>
                <span>Upgrade / Perpanjang</span>
            </a>
        </div>
    </div>

    <!-- 2. 4 Command Pillars KPI Cards (Grid Penuh 4 Kolom) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Pillar 1: Total Transaksi -->
        <div class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Total Transaksi</span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/80 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <i data-lucide="receipt" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-900 dark:text-white font-mono tracking-tight truncate">
                    {{ number_format($payments->total(), 0, ',', '.') }}
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Rekam Jejak</span>
                <span class="font-bold text-slate-700 dark:text-slate-300 font-mono">{{ $payments->count() }} Ditampilkan</span>
            </div>
        </div>

        <!-- Pillar 2: Status Langganan -->
        <div class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Status Langganan</span>
                    <div class="w-9 h-9 rounded-xl bg-teal-50 dark:bg-teal-950/60 border border-teal-200/80 dark:border-teal-800/80 flex items-center justify-center text-teal-600 dark:text-teal-400">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-900 dark:text-white font-mono tracking-tight truncate">
                    {{ $business->subscription?->isActive() ? 'Aktif' : 'Free Solo' }}
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Berlaku s/d</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400 font-mono">{{ $business->subscription?->ends_at?->format('d/m/Y') ?? 'Selamanya' }}</span>
            </div>
        </div>

        <!-- Pillar 3: Outlet / Bisnis -->
        <div class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Workspace Bisnis</span>
                    <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200/80 dark:border-blue-800/80 flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <i data-lucide="building-2" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-lg sm:text-xl font-black text-slate-900 dark:text-white tracking-tight truncate">
                    {{ $business->name }}
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Entitas Bisnis</span>
                <span class="font-bold text-slate-700 dark:text-slate-300 font-mono">ID #{{ $business->id }}</span>
            </div>
        </div>

        <!-- Pillar 4: Saluran & Keamanan -->
        <div class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Verifikasi Mutasi</span>
                    <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/80 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <i data-lucide="check-check" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-lg sm:text-xl font-black text-slate-900 dark:text-white tracking-tight truncate">
                    Rekening Resmi
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>SLA Verifikasi</span>
                <span class="font-bold text-amber-600 dark:text-amber-400 font-mono">5–15 Menit</span>
            </div>
        </div>
    </div>

    <!-- 4. Toolbar Filter & Search Container -->
    <div class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-3.5 sm:p-4 space-y-3">
        <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
            <!-- Search Input -->
            <div class="relative flex-1 max-w-md">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" aria-hidden="true"></i>
                <input type="text"
                       x-model="searchQuery"
                       placeholder="Cari nomor order atau nama paket..."
                       class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl pl-9 pr-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition">
            </div>

            <!-- Status Filter Pills -->
            <div class="flex flex-wrap items-center gap-1.5" role="tablist" aria-label="Filter Status Tagihan">
                <button type="button" 
                        @click="statusFilter = 'all'"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer focus-visible:ring-1 focus-visible:ring-emerald-400"
                        :class="statusFilter === 'all' ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700'">
                    Semua
                </button>
                <button type="button" 
                        @click="statusFilter = 'pending'"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer focus-visible:ring-1 focus-visible:ring-amber-400"
                        :class="statusFilter === 'pending' ? 'bg-amber-500 text-slate-950 shadow-sm shadow-amber-500/20' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700'">
                    Menunggu Bayar
                </button>
                <button type="button" 
                        @click="statusFilter = 'awaiting_approval'"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer focus-visible:ring-1 focus-visible:ring-cyan-400"
                        :class="statusFilter === 'awaiting_approval' ? 'bg-cyan-600 text-white shadow-sm shadow-cyan-600/20' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700'">
                    Verifikasi
                </button>
                <button type="button" 
                        @click="statusFilter = 'approved'"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer focus-visible:ring-1 focus-visible:ring-emerald-400"
                        :class="statusFilter === 'approved' ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700'">
                    Berhasil
                </button>
                <button type="button" 
                        @click="statusFilter = 'rejected'"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer focus-visible:ring-1 focus-visible:ring-rose-400"
                        :class="statusFilter === 'rejected' ? 'bg-rose-600 text-white shadow-sm shadow-rose-600/20' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700'">
                    Ditolak
                </button>
            </div>
        </div>
    </div>

    <!-- 5. High-Density Data Table Container -->
    <div class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs overflow-hidden">
        
        @if($payments->count() > 0)

            <!-- Desktop & Tablet View: High Density Responsive Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse" aria-label="Tabel Riwayat Pembayaran Tagihan">
                    <thead class="bg-slate-50/80 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 font-mono uppercase text-[10px] font-bold border-b border-slate-200 dark:border-slate-800 tracking-wider whitespace-nowrap">
                        <tr>
                            <th scope="col" class="py-3.5 px-5">No. Pesanan</th>
                            <th scope="col" class="py-3.5 px-4">Paket &amp; Siklus</th>
                            <th scope="col" class="py-3.5 px-4">Metode Bayar</th>
                            <th scope="col" class="py-3.5 px-4 text-right">Total Bayar</th>
                            <th scope="col" class="py-3.5 px-4 text-center">Bukti Transfer</th>
                            <th scope="col" class="py-3.5 px-4 text-center">Status</th>
                            <th scope="col" class="py-3.5 px-4">Tanggal Order</th>
                            <th scope="col" class="py-3.5 px-5 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-sans">
                        @foreach($payments as $p)
                        @php
                            $badge = $p->getStatusBadge();
                            $method = $p->getPaymentMethodDetails();
                            $pPackageName = $p->package_name ?? ($p->cycle === 'annual' ? 'Cooca Tahunan' : ($p->cycle === 'monthly' ? 'Cooca Bulanan' : 'Top-Up Kuota'));
                        @endphp
                        <tr x-show="matchesFilter('{{ $p->order_number }}', '{{ $pPackageName }}', '{{ $p->status }}')"
                            class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors">
                            <!-- Order Number -->
                            <td class="py-3.5 px-5">
                                <div class="font-mono font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                    <i data-lucide="hash" class="w-3.5 h-3.5 text-slate-400" aria-hidden="true"></i>
                                    <span>{{ $p->order_number }}</span>
                                </div>
                            </td>

                            <!-- Package -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 dark:text-white">
                                    {{ $pPackageName }}
                                </div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono uppercase mt-0.5">
                                    {{ $p->plan_code ?: 'SUBSCRIPTION' }}
                                </div>
                            </td>

                            <!-- Method -->
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $method['name'] ?? strtoupper($p->payment_method) }}</div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">{{ $method['bank_name'] ?? '-' }}</div>
                            </td>

                            <!-- Total Amount -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="font-mono font-bold text-sm text-emerald-600 dark:text-emerald-400">
                                    Rp {{ number_format($p->total_payable, 0, ',', '.') }}
                                </div>
                                @if($p->unique_code > 0)
                                    <div class="text-[10px] text-amber-600 dark:text-amber-400 font-mono font-semibold">
                                        Kode unik: +{{ str_pad((string)$p->unique_code, 3, '0', STR_PAD_LEFT) }}
                                    </div>
                                @endif
                            </td>

                            <!-- Proof File -->
                            <td class="py-3.5 px-4 text-center">
                                @if($p->payment_proof_path)
                                    <a href="{{ $p->getProofUrl() }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-cyan-600 dark:text-cyan-400 hover:text-cyan-500 font-bold transition focus-visible:ring-1 focus-visible:ring-cyan-400 rounded">
                                        <i data-lucide="image" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                        <span>Lihat Bukti</span>
                                    </a>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500 text-[11px] italic">Belum diunggah</span>
                                @endif
                            </td>

                            <!-- Status Badge -->
                            <td class="py-3.5 px-4 text-center">
                                <span class="rounded-full px-2.5 py-0.5 text-[10px] sm:text-xs font-bold border inline-flex items-center gap-1 shadow-2xs {{ $badge['class'] }}">
                                    <i data-lucide="{{ $badge['icon'] }}" class="w-3 h-3" aria-hidden="true"></i>
                                    <span>{{ $badge['label'] }}</span>
                                </span>
                            </td>

                            <!-- Date -->
                            <td class="py-3.5 px-4 font-mono text-slate-600 dark:text-slate-400 text-[11px]">
                                {{ $p->created_at->format('d M Y, H:i') }}
                            </td>

                            <!-- Action CTA -->
                            <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('billing.payment.invoice', $p) }}"
                                       target="_blank"
                                       title="Cetak / Download Faktur"
                                       class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                                        <i data-lucide="printer" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                                    </a>
                                    <a href="{{ route('billing.payment.show', $p) }}"
                                       class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition cursor-pointer inline-flex items-center gap-1">
                                        <span>Detail</span>
                                        <i data-lucide="chevron-right" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile View: Clean Card List Representation -->
            <div class="block md:hidden divide-y divide-slate-100 dark:divide-slate-800/80">
                @foreach($payments as $p)
                @php
                    $badge = $p->getStatusBadge();
                    $method = $p->getPaymentMethodDetails();
                    $pPackageName = $p->package_name ?? ($p->cycle === 'annual' ? 'Cooca Tahunan' : ($p->cycle === 'monthly' ? 'Cooca Bulanan' : 'Top-Up Kuota'));
                @endphp
                <div x-show="matchesFilter('{{ $p->order_number }}', '{{ $pPackageName }}', '{{ $p->status }}')"
                     class="p-4 sm:p-5 space-y-3.5 hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition">
                    
                    <!-- Card Top: Order Number & Status Badge -->
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="hash" class="w-3.5 h-3.5 text-slate-400" aria-hidden="true"></i>
                            <span class="font-mono font-bold text-sm text-slate-900 dark:text-white">{{ $p->order_number }}</span>
                        </div>
                        <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold border inline-flex items-center gap-1 {{ $badge['class'] }}">
                            <i data-lucide="{{ $badge['icon'] }}" class="w-3 h-3" aria-hidden="true"></i>
                            <span>{{ $badge['label'] }}</span>
                        </span>
                    </div>

                    <!-- Card Body: Package & Amount -->
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200/80 dark:border-slate-800/80 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 dark:text-slate-400">Paket:</span>
                            <span class="font-bold text-slate-900 dark:text-white text-right">
                                {{ $pPackageName }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 dark:text-slate-400">Metode:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $method['name'] ?? strtoupper($p->payment_method) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-200/80 dark:border-slate-900">
                            <span class="text-slate-500 dark:text-slate-400">Total Tagihan:</span>
                            <div class="text-right">
                                <div class="font-mono font-bold text-sm text-emerald-600 dark:text-emerald-400">
                                    Rp {{ number_format($p->total_payable, 0, ',', '.') }}
                                </div>
                                @if($p->unique_code > 0)
                                    <div class="text-[10px] text-amber-600 dark:text-amber-400 font-mono">
                                        Kode unik: +{{ str_pad((string)$p->unique_code, 3, '0', STR_PAD_LEFT) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer: Date, Proof & Quick Actions -->
                    <div class="flex items-center justify-between gap-3 pt-1">
                        <div class="text-[11px] font-mono text-slate-500 dark:text-slate-400 flex items-center gap-1">
                            <i data-lucide="clock" class="w-3 h-3 text-slate-400" aria-hidden="true"></i>
                            <span>{{ $p->created_at->format('d M Y, H:i') }}</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('billing.payment.invoice', $p) }}" 
                               target="_blank" 
                               class="p-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-emerald-600 dark:text-emerald-400 text-xs font-bold transition shadow-2xs focus-visible:ring-1 focus-visible:ring-emerald-400" 
                               title="Cetak / Unduh Faktur">
                                <i data-lucide="printer" class="w-4 h-4" aria-hidden="true"></i>
                            </a>

                            @if($p->payment_proof_path)
                                <a href="{{ $p->getProofUrl() }}" target="_blank" class="p-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-cyan-600 dark:text-cyan-400 text-xs font-bold focus-visible:ring-1 focus-visible:ring-cyan-400" title="Lihat Bukti Transfer">
                                    <i data-lucide="image" class="w-4 h-4" aria-hidden="true"></i>
                                </a>
                            @endif

                            <a href="{{ route('billing.payment.show', $p) }}" 
                               class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm shadow-emerald-600/20 active:scale-[0.98] transition cursor-pointer flex items-center gap-1">
                                <span>Rincian</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>

                </div>
                @endforeach
            </div>

        @else
            <!-- 6. Empty State Component -->
            <div class="py-16 px-6 text-center space-y-4">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 flex items-center justify-center mx-auto shadow-2xs" aria-hidden="true">
                    <i data-lucide="receipt" class="w-8 h-8"></i>
                </div>
                <div class="space-y-1 max-w-sm mx-auto">
                    <h4 class="text-sm font-black text-slate-900 dark:text-white">Belum Ada Riwayat Tagihan</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Saat ini bisnis Anda menggunakan paket standar atau belum memiliki transaksi tagihan langganan.
                    </p>
                </div>
                <div class="pt-2">
                    <a href="{{ route('billing.checkout') }}" 
                       class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm shadow-emerald-600/20 active:scale-[0.98] transition cursor-pointer inline-flex items-center gap-1.5 focus-visible:ring-2 focus-visible:ring-emerald-500">
                        <i data-lucide="zap" class="w-4 h-4" aria-hidden="true"></i>
                        <span>Pilih Paket Langganan</span>
                    </a>
                </div>
            </div>
        @endif

        <!-- 7. Pagination Bar -->
        @if($payments->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40">
            {{ $payments->links() }}
        </div>
        @endif

    </div>

</div>
@endsection
