@extends('layouts.app', [
    'title' => 'Riwayat Pembayaran Langganan — Cooca UMKM',
    'headerTitle' => 'Riwayat Pembayaran & Tagihan',
    'headerSubtitle' => 'Daftar seluruh transaksi langganan, top-up kuota, dan status verifikasi bisnis Anda'
])

@section('content')
<div class="max-w-6xl mx-auto space-y-6 sm:space-y-8" x-data="{
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

    <!-- Standard Breadcrumb & Executive Navigation Bar -->
    <nav class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-2 border-b border-slate-800/80" aria-label="Breadcrumb Riwayat">
        <div class="flex items-center gap-3">
            <a href="{{ route('billing.limits') }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white border border-slate-800 transition text-xs font-semibold shadow-sm focus-visible:ring-2 focus-visible:ring-emerald-500">
                <i data-lucide="arrow-left" class="w-4 h-4" aria-hidden="true"></i>
                <span>Kembali ke Paket &amp; Kuota</span>
            </a>
            <span class="text-slate-600 hidden sm:inline" aria-hidden="true">/</span>
            <span class="text-xs text-slate-400 font-mono hidden sm:inline">Billing History</span>
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto">
            <a href="{{ route('billing.checkout') }}" 
               class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/20 transition flex items-center justify-center gap-2 group focus-visible:ring-2 focus-visible:ring-emerald-400">
                <i data-lucide="zap" class="w-4 h-4 group-hover:scale-110 transition-transform" aria-hidden="true"></i>
                <span>Upgrade / Perpanjang Paket</span>
            </a>
        </div>
    </nav>

    <!-- Quick Stats Metric KPI Cards -->
    <section aria-labelledby="kpi-overview-heading" class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5">
        <h2 id="kpi-overview-heading" class="sr-only">Ringkasan Statistik Tagihan</h2>

        <!-- Total Orders -->
        <div class="p-5 rounded-3xl bg-slate-900/90 border border-slate-800 shadow-lg backdrop-blur-xl flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-slate-800/80 border border-slate-700/60 text-slate-300 flex items-center justify-center shrink-0" aria-hidden="true">
                <i data-lucide="receipt" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold block">Total Transaksi</span>
                <div class="text-2xl font-black font-mono text-white tracking-tight">{{ number_format($payments->total(), 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Approved Subscriptions -->
        <div class="p-5 rounded-3xl bg-slate-900/90 border border-slate-800 shadow-lg backdrop-blur-xl flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 flex items-center justify-center shrink-0" aria-hidden="true">
                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-[10px] font-mono uppercase tracking-wider text-emerald-400/80 font-bold block">Status Langganan</span>
                <div class="text-xs font-bold text-slate-200 mt-1">
                    {{ $business->subscription?->isActive() ? 'Paket Aktif s/d ' . $business->subscription?->ends_at?->format('d M Y') : 'Free Solo (Non-Langganan)' }}
                </div>
            </div>
        </div>

        <!-- Active Business Workspace -->
        <div class="p-5 rounded-3xl bg-slate-900/90 border border-slate-800 shadow-lg backdrop-blur-xl flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-cyan-500/20 border border-cyan-500/30 text-cyan-400 flex items-center justify-center shrink-0" aria-hidden="true">
                <i data-lucide="building-2" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[10px] font-mono uppercase tracking-wider text-cyan-400/80 font-bold block">Outlet / Bisnis</span>
                <div class="text-sm font-black text-white truncate mt-0.5 font-mono">{{ $business->name }}</div>
            </div>
        </div>
    </section>

    <!-- Payments Container & Toolbar Panel -->
    <div class="rounded-3xl bg-slate-900/90 border border-slate-800 shadow-2xl overflow-hidden backdrop-blur-xl">
        
        <!-- Table Header & Interactive Filter Toolbar -->
        <div class="p-5 sm:p-6 border-b border-slate-800 space-y-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-black text-white flex items-center gap-2">
                        <i data-lucide="file-text" class="w-4 h-4 text-emerald-400" aria-hidden="true"></i>
                        <span>Daftar Riwayat Invoice Tagihan</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Pantau status konfirmasi mutasi bank, bukti struk, dan masa aktif paket Anda.</p>
                </div>
                <div class="text-xs font-mono text-slate-400 bg-slate-950 px-3 py-1.5 rounded-xl border border-slate-800 shrink-0">
                    Menampilkan {{ $payments->count() }} dari {{ $payments->total() }} pesanan
                </div>
            </div>

            <!-- Instant Search & Status Filter Controls -->
            <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 pt-2">
                <!-- Search Input -->
                <div class="relative flex-1 max-w-md">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" aria-hidden="true"></i>
                    <input type="text"
                           x-model="searchQuery"
                           placeholder="Cari nomor order atau nama paket..."
                           class="w-full bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl pl-9 pr-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">
                </div>

                <!-- Status Filter Pills -->
                <div class="flex flex-wrap items-center gap-1.5" role="tablist" aria-label="Filter Status">
                    <button type="button" 
                            @click="statusFilter = 'all'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition focus-visible:ring-1 focus-visible:ring-emerald-400"
                            :class="statusFilter === 'all' ? 'bg-emerald-500 text-slate-950 shadow-sm shadow-emerald-500/20' : 'bg-slate-950 text-slate-400 hover:text-slate-200 border border-slate-800'">
                        Semua
                    </button>
                    <button type="button" 
                            @click="statusFilter = 'pending'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition focus-visible:ring-1 focus-visible:ring-amber-400"
                            :class="statusFilter === 'pending' ? 'bg-amber-500 text-slate-950 shadow-sm shadow-amber-500/20' : 'bg-slate-950 text-slate-400 hover:text-slate-200 border border-slate-800'">
                        Menunggu Bayar
                    </button>
                    <button type="button" 
                            @click="statusFilter = 'awaiting_approval'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition focus-visible:ring-1 focus-visible:ring-cyan-400"
                            :class="statusFilter === 'awaiting_approval' ? 'bg-cyan-500 text-slate-950 shadow-sm shadow-cyan-500/20' : 'bg-slate-950 text-slate-400 hover:text-slate-200 border border-slate-800'">
                        Verifikasi
                    </button>
                    <button type="button" 
                            @click="statusFilter = 'approved'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition focus-visible:ring-1 focus-visible:ring-emerald-400"
                            :class="statusFilter === 'approved' ? 'bg-emerald-500 text-slate-950 shadow-sm shadow-emerald-500/20' : 'bg-slate-950 text-slate-400 hover:text-slate-200 border border-slate-800'">
                        Berhasil
                    </button>
                    <button type="button" 
                            @click="statusFilter = 'rejected'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition focus-visible:ring-1 focus-visible:ring-rose-400"
                            :class="statusFilter === 'rejected' ? 'bg-rose-500 text-slate-950 shadow-sm shadow-rose-500/20' : 'bg-slate-950 text-slate-400 hover:text-slate-200 border border-slate-800'">
                        Ditolak
                    </button>
                </div>
            </div>
        </div>

        @if($payments->count() > 0)

            <!-- Desktop & Tablet View: High Density Responsive Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300 border-collapse" aria-label="Tabel Riwayat Pembayaran Tagihan">
                    <thead class="bg-slate-950 text-slate-400 font-mono uppercase text-[10px] border-b border-slate-800 tracking-wider">
                        <tr>
                            <th scope="col" class="py-4 px-5">No. Pesanan</th>
                            <th scope="col" class="py-4 px-4">Paket &amp; Siklus</th>
                            <th scope="col" class="py-4 px-4">Metode Bayar</th>
                            <th scope="col" class="py-4 px-4">Total Bayar</th>
                            <th scope="col" class="py-4 px-4">Bukti Transfer</th>
                            <th scope="col" class="py-4 px-4">Status</th>
                            <th scope="col" class="py-4 px-4">Tanggal Order</th>
                            <th scope="col" class="py-4 px-5 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-sans">
                        @foreach($payments as $p)
                        @php
                            $badge = $p->getStatusBadge();
                            $method = $p->getPaymentMethodDetails();
                            $pPackageName = $p->package_name ?? ($p->cycle === 'annual' ? 'Cooca Tahunan' : ($p->cycle === 'monthly' ? 'Cooca Bulanan' : 'Top-Up Kuota'));
                        @endphp
                        <tr x-show="matchesFilter('{{ $p->order_number }}', '{{ $pPackageName }}', '{{ $p->status }}')"
                            class="hover:bg-slate-850/50 transition duration-150">
                            <!-- Order Number -->
                            <td class="py-4 px-5">
                                <div class="font-mono font-black text-white flex items-center gap-1.5">
                                    <i data-lucide="hash" class="w-3.5 h-3.5 text-slate-500" aria-hidden="true"></i>
                                    <span>{{ $p->order_number }}</span>
                                </div>
                            </td>

                            <!-- Package -->
                            <td class="py-4 px-4">
                                <div class="font-bold text-white">
                                    {{ $pPackageName }}
                                </div>
                                <div class="text-[10px] text-slate-400 font-mono uppercase mt-0.5">
                                    {{ $p->plan_code ?: 'SUBSCRIPTION' }}
                                </div>
                            </td>

                            <!-- Method -->
                            <td class="py-4 px-4">
                                <div class="font-semibold text-slate-200">{{ $method['name'] ?? strtoupper($p->payment_method) }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $method['bank_name'] ?? '-' }}</div>
                            </td>

                            <!-- Total Amount -->
                            <td class="py-4 px-4">
                                <div class="font-mono font-black text-sm text-emerald-400">
                                    Rp {{ number_format($p->total_payable, 0, ',', '.') }}
                                </div>
                                @if($p->unique_code > 0)
                                    <div class="text-[10px] text-amber-400/80 font-mono font-semibold">
                                        Kode unik: +{{ str_pad((string)$p->unique_code, 3, '0', STR_PAD_LEFT) }}
                                    </div>
                                @endif
                            </td>

                            <!-- Proof File -->
                            <td class="py-4 px-4">
                                @if($p->payment_proof_path)
                                    <a href="{{ $p->getProofUrl() }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-cyan-400 hover:text-cyan-300 font-bold transition focus-visible:ring-1 focus-visible:ring-cyan-400 rounded">
                                        <i data-lucide="image" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                        <span>Lihat Bukti</span>
                                    </a>
                                @else
                                    <span class="text-slate-500 text-[11px] italic">Belum diunggah</span>
                                @endif
                            </td>

                            <!-- Status Badge -->
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border inline-flex items-center gap-1.5 shadow-sm {{ $badge['class'] }}">
                                    <i data-lucide="{{ $badge['icon'] }}" class="w-3 h-3" aria-hidden="true"></i>
                                    <span>{{ $badge['label'] }}</span>
                                </span>
                            </td>

                            <!-- Date -->
                            <td class="py-4 px-4 font-mono text-slate-400 text-[11px]">
                                {{ $p->created_at->format('d M Y, H:i') }}
                            </td>

                            <!-- Action CTA -->
                            <td class="py-4 px-5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('billing.payment.invoice', $p) }}"
                                       target="_blank"
                                       title="Cetak / Download Faktur"
                                       class="px-2.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-semibold transition inline-flex items-center gap-1 shadow-sm focus-visible:ring-1 focus-visible:ring-emerald-400">
                                        <i data-lucide="printer" class="w-3.5 h-3.5 text-emerald-400" aria-hidden="true"></i>
                                        <span class="hidden lg:inline text-[11px]">Faktur</span>
                                    </a>
                                    <a href="{{ route('billing.payment.show', $p) }}"
                                       class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-emerald-500 hover:text-slate-950 text-slate-200 text-xs font-bold transition inline-flex items-center gap-1.5 shadow-sm group focus-visible:ring-1 focus-visible:ring-emerald-400">
                                        <span>Detail</span>
                                        <i data-lucide="chevron-right" class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile View: Clean Card List Representation (Zero Horizontal Overflow!) -->
            <div class="block md:hidden divide-y divide-slate-800/80">
                @foreach($payments as $p)
                @php
                    $badge = $p->getStatusBadge();
                    $method = $p->getPaymentMethodDetails();
                    $pPackageName = $p->package_name ?? ($p->cycle === 'annual' ? 'Cooca Tahunan' : ($p->cycle === 'monthly' ? 'Cooca Bulanan' : 'Top-Up Kuota'));
                @endphp
                <div x-show="matchesFilter('{{ $p->order_number }}', '{{ $pPackageName }}', '{{ $p->status }}')"
                     class="p-5 space-y-4 hover:bg-slate-850/40 transition">
                    
                    <!-- Card Top: Order Number & Status Badge -->
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="hash" class="w-3.5 h-3.5 text-slate-400" aria-hidden="true"></i>
                            <span class="font-mono font-black text-sm text-white">{{ $p->order_number }}</span>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border inline-flex items-center gap-1 {{ $badge['class'] }}">
                            <i data-lucide="{{ $badge['icon'] }}" class="w-3 h-3" aria-hidden="true"></i>
                            <span>{{ $badge['label'] }}</span>
                        </span>
                    </div>

                    <!-- Card Body: Package & Amount -->
                    <div class="p-4 rounded-2xl bg-slate-950/70 border border-slate-800/80 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-400">Paket:</span>
                            <span class="font-bold text-white text-right">
                                {{ $pPackageName }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-400">Metode:</span>
                            <span class="font-semibold text-slate-200">{{ $method['name'] ?? strtoupper($p->payment_method) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-900">
                            <span class="text-slate-400">Total Tagihan:</span>
                            <div class="text-right">
                                <div class="font-mono font-black text-sm text-emerald-400">
                                    Rp {{ number_format($p->total_payable, 0, ',', '.') }}
                                </div>
                                @if($p->unique_code > 0)
                                    <div class="text-[10px] text-amber-400/90 font-mono">
                                        Kode unik: +{{ str_pad((string)$p->unique_code, 3, '0', STR_PAD_LEFT) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer: Date, Proof & Quick Actions -->
                    <div class="flex items-center justify-between gap-3 pt-1">
                        <div class="text-[11px] font-mono text-slate-400 flex items-center gap-1">
                            <i data-lucide="clock" class="w-3 h-3 text-slate-500" aria-hidden="true"></i>
                            <span>{{ $p->created_at->format('d M Y, H:i') }}</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('billing.payment.invoice', $p) }}" 
                               target="_blank" 
                               class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-emerald-400 text-xs font-bold transition shadow-sm focus-visible:ring-1 focus-visible:ring-emerald-400" 
                               title="Cetak / Unduh Faktur">
                                <i data-lucide="printer" class="w-4 h-4" aria-hidden="true"></i>
                            </a>

                            @if($p->payment_proof_path)
                                <a href="{{ $p->getProofUrl() }}" target="_blank" class="p-2 rounded-xl bg-slate-800 text-cyan-400 text-xs font-bold focus-visible:ring-1 focus-visible:ring-cyan-400" title="Lihat Bukti Transfer">
                                    <i data-lucide="image" class="w-4 h-4" aria-hidden="true"></i>
                                </a>
                            @endif

                            <a href="{{ route('billing.payment.show', $p) }}" 
                               class="px-3.5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs transition flex items-center gap-1 shadow-sm focus-visible:ring-1 focus-visible:ring-emerald-400">
                                <span>Rincian</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>

                </div>
                @endforeach
            </div>

        @else
            <!-- Empty State -->
            <div class="py-16 px-6 text-center space-y-4">
                <div class="w-16 h-16 rounded-3xl bg-slate-800/80 border border-slate-700/60 text-slate-500 flex items-center justify-center mx-auto shadow-inner" aria-hidden="true">
                    <i data-lucide="inbox" class="w-8 h-8"></i>
                </div>
                <div class="space-y-1 max-w-sm mx-auto">
                    <h4 class="text-sm font-black text-white">Belum Ada Riwayat Tagihan</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Saat ini bisnis Anda menggunakan paket standar atau belum memiliki transaksi tagihan langganan.
                    </p>
                </div>
                <div class="pt-2">
                    <a href="{{ route('billing.checkout') }}" 
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/20 transition focus-visible:ring-2 focus-visible:ring-emerald-400">
                        <i data-lucide="zap" class="w-4 h-4" aria-hidden="true"></i>
                        <span>Pilih Paket Langganan</span>
                    </a>
                </div>
            </div>
        @endif

        <!-- Pagination Bar -->
        @if($payments->hasPages())
        <div class="p-5 border-t border-slate-800 bg-slate-950/40">
            {{ $payments->links() }}
        </div>
        @endif

    </div>

</div>
@endsection
