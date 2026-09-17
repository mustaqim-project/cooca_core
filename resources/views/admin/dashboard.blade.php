@extends('layouts.admin', [
    'title' => 'Dashboard - Admin Console',
    'headerTitle' => 'Statistik & Ringkasan Platform SaaS',
    'headerSubtitle' => 'Pantau pertumbuhan tenant UMKM, arus pendapatan MRR, langganan Core, dan konsumsi token AI',
])

@section('content')
<div class="space-y-6 pb-28 lg:pb-10" x-data="{
    // Modals state
    showBusinessModal: false,
    selectedBusiness: null,
    showUserModal: false,
    selectedUser: null,
    showApprovalModal: false,
    selectedPayment: null,
    approvalTab: 'approve',
    proofModalOpen: false,
    previewProofUrl: '',

    openBusiness(biz) {
        this.selectedBusiness = biz;
        this.showBusinessModal = true;
        this.$nextTick(() => lucide.createIcons());
    },
    openUser(user) {
        this.selectedUser = user;
        this.showUserModal = true;
        this.$nextTick(() => lucide.createIcons());
    },
    openApproval(payment) {
        this.selectedPayment = payment;
        this.approvalTab = 'approve';
        this.showApprovalModal = true;
        this.$nextTick(() => lucide.createIcons());
    },
    previewProof(url) {
        this.previewProofUrl = url;
        this.proofModalOpen = true;
        this.$nextTick(() => lucide.createIcons());
    }
}">

    <!-- Pending Payment Alert Banner (Apple Amber Tinted Bento Squircle) -->
    @if($pendingSubscriptionsCount > 0)
    <div class="rounded-[22px] p-4 sm:p-5 bg-gradient-to-r from-[#FF9500]/15 via-[#FF9500]/10 to-[#FF9500]/5 border border-[#FF9500]/30 backdrop-blur-md flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-xs">
        <div class="flex items-center gap-3.5 min-w-0">
            <div class="w-12 h-12 rounded-[14px] bg-[#FF9500]/20 flex items-center justify-center text-[#B25E00] dark:text-[#FF9F0A] shrink-0">
                <i data-lucide="bell-ring" class="w-5 h-5" stroke-width="2"></i>
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-[14px] sm:text-[15px] font-bold text-black dark:text-white">Verifikasi Pembayaran Diperlukan</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500] text-white shadow-xs tabular-nums">
                        {{ $pendingSubscriptionsCount }} Menunggu
                    </span>
                </div>
                <p class="text-[12px] text-black/60 dark:text-white/60 mt-0.5 leading-relaxed truncate sm:whitespace-normal">
                    Terdapat bukti transfer paket Core yang baru saja diunggah dan membutuhkan persetujuan aktivasi administrator.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto shrink-0">
            @if(isset($pendingSubscriptions) && $pendingSubscriptions->isNotEmpty())
            <button type="button" @click="openApproval(@js($pendingSubscriptions->first()))"
                class="flex-1 sm:flex-none h-12 sm:h-10 px-4 rounded-[12px] bg-[#FF9500] hover:bg-[#E08500] active:scale-[0.98] text-white text-[13px] font-bold transition-all flex items-center justify-center gap-2 shadow-xs cursor-pointer">
                <i data-lucide="zap" class="w-4 h-4" stroke-width="2"></i>
                <span>Review Bukti Instan</span>
            </button>
            @endif
            <a href="{{ route('admin.subscriptions.index') }}"
                class="flex-1 sm:flex-none h-12 sm:h-10 px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-[13px] font-bold transition-all flex items-center justify-center gap-2 whitespace-nowrap shadow-md shadow-[#007AFF]/25">
                <i data-lucide="check-circle-2" class="w-4 h-4" stroke-width="2"></i>
                <span>Buka Antrean Approval</span>
            </a>
        </div>
    </div>

    @if(isset($pendingSubscriptions) && $pendingSubscriptions->count() > 1)
    <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md overflow-hidden shadow-xs">
        <div class="px-4 sm:px-5 py-3 border-b border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between bg-black/[0.01] dark:bg-white/[0.02]">
            <div class="flex items-center gap-2 min-w-0">
                <i data-lucide="clock" class="w-4 h-4 text-[#FF9500] shrink-0" stroke-width="2"></i>
                <span class="text-[12px] font-bold text-black dark:text-white truncate">Daftar Antrean Pembayaran Menunggu Konfirmasi ({{ $pendingSubscriptions->count() }})</span>
            </div>
        </div>
        <div class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
            @foreach($pendingSubscriptions as $payment)
            <div class="p-3.5 sm:px-5 flex items-center justify-between gap-3 hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                        <span class="font-mono text-[11px] font-bold text-black/50 dark:text-white/50 tabular-nums">#{{ $payment->order_number }}</span>
                        <span class="font-bold text-black dark:text-white text-[13px] truncate">{{ $payment->business->name ?? 'Workspace' }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF] shrink-0">{{ $payment->package_name ?? 'Core' }}</span>
                    </div>
                    <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 truncate">
                        Pemohon: {{ $payment->user->name ?? '-' }} · Total: <strong class="text-black dark:text-white tabular-nums">Rp {{ number_format($payment->total_payable ?? $payment->total_amount ?? 0, 0, ',', '.') }}</strong>
                    </div>
                </div>
                <button type="button" @click="openApproval(@js($payment))"
                    class="h-9 px-3.5 rounded-[10px] bg-[#FF9500]/15 hover:bg-[#FF9500]/25 active:scale-95 text-[#B25E00] dark:text-[#FF9F0A] text-[12px] font-bold transition-all flex items-center gap-1.5 shrink-0 cursor-pointer">
                    <i data-lucide="eye" class="w-3.5 h-3.5" stroke-width="2"></i>
                    <span>Tinjau</span>
                </button>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @endif

    <!-- Bento Hero Tile: Platform Health & Financial Pulse -->
    <div class="rounded-[22px] sm:rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-4 sm:p-6 lg:p-7 shadow-xs">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-center">
            
            <!-- Left Side: Financial Summary (MRR) -->
            <div class="lg:col-span-7 space-y-3.5">
                <div>
                    <div class="text-[13px] sm:text-sm font-medium text-black/55 dark:text-white/55">
                        Pendapatan Berulang Bulanan - MRR (Monthly Recurring)
                    </div>
                    <div class="text-[28px] sm:text-[36px] lg:text-[40px] font-bold tabular-nums tracking-tight text-black dark:text-white mt-1">
                        Rp {{ number_format($mrr, 0, ',', '.') }}
                    </div>
                </div>

                <!-- Subscriber Ratio Visual Track -->
                <div class="space-y-2 pt-1">
                    <div class="flex items-center justify-between text-[12px] font-medium text-black/60 dark:text-white/60">
                        <span class="truncate">Konversi: <strong class="text-black dark:text-white tabular-nums">{{ $totalPaidSubscribers }} Core</strong> (<span class="tabular-nums">{{ $coreMonthlyCount }}</span> Bln · <span class="tabular-nums">{{ $coreAnnualCount }}</span> Thn)</span>
                        <span class="tabular-nums shrink-0 ml-2">{{ $freeSubscribersCount }} Free</span>
                    </div>
                    @php
                        $paidRatio = $totalBusinesses > 0 ? round(($totalPaidSubscribers / $totalBusinesses) * 100) : 0;
                    @endphp
                    <div class="w-full h-2.5 bg-black/[0.06] dark:bg-white/[0.08] rounded-full overflow-hidden flex">
                        <div class="h-full bg-gradient-to-r from-[#007AFF] to-[#5856D6] rounded-full transition-all duration-500" style="width: {{ $paidRatio }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Platform Operational Summary Card -->
            <div class="lg:col-span-5 p-4 sm:p-5 rounded-[18px] sm:rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.04] space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-black/[0.04] dark:border-white/[0.06]">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">Status Ekosistem</span>
                    <span class="text-[12px] font-medium text-[#248A3D] dark:text-[#30D158] flex items-center gap-1.5">
                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-[#34C759]" stroke-width="2"></i>
                        <span>Sehat &amp; Stabil</span>
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-2.5 sm:gap-3 text-[12px]">
                    <div class="p-3 rounded-[14px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.04] dark:border-white/[0.04]">
                        <div class="text-[11px] text-black/45 dark:text-white/45">Tenant Aktif</div>
                        <div class="text-[18px] font-bold text-black dark:text-white tabular-nums mt-0.5">{{ number_format($totalBusinesses) }}</div>
                    </div>
                    <div class="p-3 rounded-[14px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.04] dark:border-white/[0.04]">
                        <div class="text-[11px] text-black/45 dark:text-white/45">Run HPP Cooca</div>
                        <div class="text-[18px] font-bold text-[#007AFF] dark:text-[#0A84FF] tabular-nums mt-0.5">{{ number_format($totalCostingRuns) }}</div>
                    </div>
                </div>
                <div class="text-[11px] text-black/50 dark:text-white/50 leading-relaxed flex items-center gap-1.5">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#34C759] shrink-0" stroke-width="2"></i>
                    <span>Isolasi multi-tenant, WhatsApp dual gateway &amp; AI aman.</span>
                </div>
            </div>

        </div>
    </div>

    <!-- 4 Bento KPI Summary Cards (Adaptive 2-Column Mobile, 4-Column Desktop) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        
        <!-- Tile 1: Total Tenants -->
        <div class="rounded-[18px] sm:rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-3.5 sm:p-5 flex flex-col justify-between hover:shadow-md hover:-translate-y-0.5 transition-all">
            <div class="flex items-center justify-between gap-1.5">
                <span class="text-[12px] sm:text-[13px] font-medium text-black/55 dark:text-white/55 truncate">Total Tenant Bisnis</span>
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-[10px] bg-[#007AFF]/10 flex items-center justify-center text-[#007AFF] dark:text-[#0A84FF] shrink-0">
                    <i data-lucide="building-2" class="w-3.5 h-3.5 sm:w-4 sm:h-4" stroke-width="1.8"></i>
                </div>
            </div>
            <div class="my-2 sm:my-3">
                <div class="text-[22px] sm:text-[26px] font-bold tabular-nums tracking-tight text-black dark:text-white">
                    {{ number_format($totalBusinesses, 0, ',', '.') }}
                </div>
                <div class="text-[11px] sm:text-[12px] font-medium text-[#007AFF] dark:text-[#0A84FF] mt-0.5 tabular-nums truncate">
                    {{ $totalPaidSubscribers }} Core · {{ $freeSubscribersCount }} Free
                </div>
            </div>
            <a href="{{ route('admin.businesses.index') }}" class="text-[12px] font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline inline-flex items-center gap-1 pt-1.5 border-t border-black/[0.04] dark:border-white/[0.06] truncate active:scale-95 transition-all">
                <span>Kelola Tenant</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5" stroke-width="1.8"></i>
            </a>
        </div>

        <!-- Tile 2: AI Token Consumption -->
        <div class="rounded-[18px] sm:rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-3.5 sm:p-5 flex flex-col justify-between hover:shadow-md hover:-translate-y-0.5 transition-all">
            <div class="flex items-center justify-between gap-1.5">
                <span class="text-[12px] sm:text-[13px] font-medium text-black/55 dark:text-white/55 truncate">Token AI Global</span>
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-[10px] bg-[#AF52DE]/10 flex items-center justify-center text-[#AF52DE] dark:text-[#BF5AF2] shrink-0">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5 sm:w-4 sm:h-4" stroke-width="1.8"></i>
                </div>
            </div>
            <div class="my-2 sm:my-3">
                <div class="text-[22px] sm:text-[26px] font-bold tabular-nums tracking-tight text-[#AF52DE] dark:text-[#BF5AF2]">
                    {{ number_format($totalAiTokensConsumed, 0, ',', '.') }}
                </div>
                <div class="text-[11px] sm:text-[12px] font-medium text-black/50 dark:text-white/50 mt-0.5 truncate">
                    Gemini 2.5 Flash
                </div>
            </div>
            <a href="{{ route('admin.ai-tokens.index') }}" class="text-[12px] font-semibold text-[#AF52DE] dark:text-[#BF5AF2] hover:underline inline-flex items-center gap-1 pt-1.5 border-t border-black/[0.04] dark:border-white/[0.06] truncate active:scale-95 transition-all">
                <span>Monitoring Token</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5" stroke-width="1.8"></i>
            </a>
        </div>

        <!-- Tile 3: Total Users -->
        <div class="rounded-[18px] sm:rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-3.5 sm:p-5 flex flex-col justify-between hover:shadow-md hover:-translate-y-0.5 transition-all">
            <div class="flex items-center justify-between gap-1.5">
                <span class="text-[12px] sm:text-[13px] font-medium text-black/55 dark:text-white/55 truncate">Pengguna Terdaftar</span>
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-[10px] bg-[#34C759]/10 flex items-center justify-center text-[#34C759] dark:text-[#30D158] shrink-0">
                    <i data-lucide="users" class="w-3.5 h-3.5 sm:w-4 sm:h-4" stroke-width="1.8"></i>
                </div>
            </div>
            <div class="my-2 sm:my-3">
                <div class="text-[22px] sm:text-[26px] font-bold tabular-nums tracking-tight text-black dark:text-white">
                    {{ number_format($totalUsers, 0, ',', '.') }}
                </div>
                <div class="text-[11px] sm:text-[12px] font-medium text-black/50 dark:text-white/50 mt-0.5 truncate">
                    Owner, Kasir &amp; Tim
                </div>
            </div>
            <a href="{{ route('admin.users.index') }}" class="text-[12px] font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline inline-flex items-center gap-1 pt-1.5 border-t border-black/[0.04] dark:border-white/[0.06] truncate active:scale-95 transition-all">
                <span>Daftar User</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5" stroke-width="1.8"></i>
            </a>
        </div>

        <!-- Tile 4: Total Products & Costing -->
        <div class="rounded-[18px] sm:rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-3.5 sm:p-5 flex flex-col justify-between hover:shadow-md hover:-translate-y-0.5 transition-all">
            <div class="flex items-center justify-between gap-1.5">
                <span class="text-[12px] sm:text-[13px] font-medium text-black/55 dark:text-white/55 truncate">Katalog &amp; HPP</span>
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-[10px] bg-[#FF9500]/10 flex items-center justify-center text-[#FF9500] dark:text-[#FF9F0A] shrink-0">
                    <i data-lucide="layers" class="w-3.5 h-3.5 sm:w-4 sm:h-4" stroke-width="1.8"></i>
                </div>
            </div>
            <div class="my-2 sm:my-3">
                <div class="text-[22px] sm:text-[26px] font-bold tabular-nums tracking-tight text-black dark:text-white">
                    {{ number_format($totalProducts, 0, ',', '.') }}
                </div>
                <div class="text-[11px] sm:text-[12px] font-medium text-black/50 dark:text-white/50 mt-0.5 tabular-nums truncate">
                    {{ number_format($totalCostingRuns) }} Hitung HPP
                </div>
            </div>
            <a href="#adminEcosystemActivityChart" class="text-[12px] font-semibold text-[#FF9500] dark:text-[#FF9F0A] hover:underline inline-flex items-center gap-1 pt-1.5 border-t border-black/[0.04] dark:border-white/[0.06] truncate active:scale-95 transition-all">
                <span>Lihat Aktivitas</span>
                <i data-lucide="arrow-down" class="w-3.5 h-3.5" stroke-width="1.8"></i>
            </a>
        </div>

    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- TRIPAY CENTRAL PAYMENT GATEWAY HUB (Multi-Tenant Omnichannel GMV)   -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div class="rounded-[22px] sm:rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-4 sm:p-6 lg:p-7 shadow-xs space-y-5">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-black/[0.05] dark:border-white/[0.06]">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-[12px] bg-[#5856D6]/10 text-[#5856D6] dark:text-[#5E5CE6] flex items-center justify-center shrink-0">
                    <i data-lucide="zap" class="w-5 h-5" stroke-width="2"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-[17px] sm:text-[18px] font-bold text-black dark:text-white tracking-tight">TriPay Gateway Central Hub</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#5856D6]/12 text-[#5856D6] dark:text-[#5E5CE6]">
                            Model B Terpusat
                        </span>
                    </div>
                    <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">
                        Agregasi volume transaksi (POS QRIS Meja, Toko Online &amp; Billing SaaS) serta audit trail webhook gateway.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[12px] font-semibold {{ $webhookHealthRate >= 98 ? 'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF9500]/10 text-[#B25E00] dark:text-[#FF9F0A]' }}">
                    <span class="w-2 h-2 rounded-full {{ $webhookHealthRate >= 98 ? 'bg-[#34C759]' : 'bg-[#FF9500]' }}"></span>
                    <span>Webhook Health: {{ $webhookHealthRate }}% Sukses</span>
                </span>
            </div>
        </div>

        <!-- 4 Sub-Metrics Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.05]">
                <span class="text-[11.5px] font-medium text-black/50 dark:text-white/50">GMV Platform Terpusat</span>
                <div class="text-[20px] sm:text-[22px] font-bold tabular-nums text-black dark:text-white mt-1.5">
                    Rp {{ number_format($tripayTotalGmv, 0, ',', '.') }}
                </div>
                <span class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 block tabular-nums">
                    {{ number_format($tripayTotalCount, 0, ',', '.') }} transaksi berhasil
                </span>
            </div>

            <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.05]">
                <span class="text-[11.5px] font-medium text-black/50 dark:text-white/50">Estimasi Beban MDR</span>
                <div class="text-[20px] sm:text-[22px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A] mt-1.5">
                    Rp {{ number_format($tripayTotalMdr, 0, ',', '.') }}
                </div>
                <span class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 block">
                    Fee administrasi gateway
                </span>
            </div>

            <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.05]">
                <span class="text-[11.5px] font-medium text-black/50 dark:text-white/50">Net Volume Ekosistem</span>
                <div class="text-[20px] sm:text-[22px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] mt-1.5">
                    Rp {{ number_format($tripayNetVolume, 0, ',', '.') }}
                </div>
                <span class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 block">
                    Dana bersih setelah MDR
                </span>
            </div>

            <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.05]">
                <span class="text-[11.5px] font-medium text-black/50 dark:text-white/50">Total Webhook Log</span>
                <div class="text-[20px] sm:text-[22px] font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF] mt-1.5">
                    {{ number_format($totalCallbacks, 0, ',', '.') }}
                </div>
                <span class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 block">
                    Audit trail callback tersimpan
                </span>
            </div>
        </div>

        <!-- Channel Breakdown & Recent Callbacks Stream -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 pt-1">
            <!-- Channel Breakdown (7 cols) -->
            <div class="lg:col-span-6 space-y-3">
                <span class="text-[12px] font-bold text-black/70 dark:text-white/70 block">Distribusi Saluran Pembayaran Gateway:</span>
                <div class="space-y-2 text-[12.5px]">
                    <div class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.05] flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="qr-code" class="w-4 h-4 text-[#007AFF]"></i>
                            <div>
                                <span class="font-semibold text-black dark:text-white">POS QR Meja (Pay-at-Table)</span>
                                <span class="text-[11px] text-black/45 dark:text-white/45 block tabular-nums">{{ number_format($posGatewayCount) }} transaksi</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="font-bold tabular-nums text-black dark:text-white">Rp {{ number_format($posGatewayGmv, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.05] flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="shopping-bag" class="w-4 h-4 text-[#34C759]"></i>
                            <div>
                                <span class="font-semibold text-black dark:text-white">Toko Online (Storefront)</span>
                                <span class="text-[11px] text-black/45 dark:text-white/45 block tabular-nums">{{ number_format($commerceGatewayCount) }} transaksi</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="font-bold tabular-nums text-black dark:text-white">Rp {{ number_format($commerceGatewayGmv, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.05] flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="layers" class="w-4 h-4 text-[#5856D6]"></i>
                            <div>
                                <span class="font-semibold text-black dark:text-white">Langganan Platform SaaS</span>
                                <span class="text-[11px] text-black/45 dark:text-white/45 block tabular-nums">{{ number_format($subGatewayCount) }} transaksi</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="font-bold tabular-nums text-black dark:text-white">Rp {{ number_format($subGatewayGmv, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Webhook Callback Activity (6 cols) -->
            <div class="lg:col-span-6 space-y-3">
                <span class="text-[12px] font-bold text-black/70 dark:text-white/70 block">Audit Webhook Callback Terkini:</span>
                @if($recentCallbacks->isNotEmpty())
                    <div class="space-y-1.5">
                        @foreach($recentCallbacks as $log)
                            <div class="p-2.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.05] flex items-center justify-between text-[12px]">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-2 h-2 rounded-full shrink-0 {{ $log->status === 'success' ? 'bg-[#34C759]' : 'bg-[#FF3B30]' }}"></span>
                                    <span class="font-mono text-[11px] font-semibold text-black dark:text-white truncate">{{ $log->merchant_ref ?: 'Unknown' }}</span>
                                    <span class="px-1.5 py-0.5 rounded-[5px] text-[10px] font-bold uppercase {{ $log->status === 'success' ? 'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF3B30]/10 text-[#FF3B30]' }}">
                                        {{ $log->status_code }}
                                    </span>
                                </div>
                                <div class="text-right text-[11px] text-black/45 dark:text-white/45 shrink-0 tabular-nums">
                                    {{ $log->created_at?->diffForHumans() ?? '-' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.05] text-center text-[12px] text-black/40 dark:text-white/40">
                        Belum ada riwayat callback webhook tercatat di sistem.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- BENTO CHARTS & ANALYTICS SECTION (Apple HIG & Chart.js)             -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div class="space-y-6">

        <!-- Section Header -->
        <div class="flex items-center gap-3 pt-2">
            <div class="w-9 h-9 rounded-[11px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                <i data-lucide="bar-chart-3" class="w-5 h-5" stroke-width="2"></i>
            </div>
            <div>
                <h2 class="text-[17px] sm:text-[18px] font-bold text-black dark:text-white tracking-tight">Analitik Pertumbuhan &amp; Visualisasi Ekosistem</h2>
                <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Tren registrasi, komposisi langganan, dan arus pendapatan 6 bulan berjalan</p>
            </div>
        </div>

        <!-- Charts Row 1: Line Area Chart (Growth) + Donut Chart (Plans) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6">

            <!-- Chart Card 1: Tren Pertumbuhan Registrasi (8 Cols) -->
            <div class="lg:col-span-8 rounded-[22px] sm:rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-4 sm:p-6 shadow-xs flex flex-col justify-between space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                    <div>
                        <h3 class="text-[14px] sm:text-[15px] font-bold text-black dark:text-white">Tren Pertumbuhan Tenant &amp; Pengguna</h3>
                        <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">Jumlah pendaftaran bisnis baru vs akun pengguna per bulan</p>
                    </div>
                    <!-- Custom Chart Legend -->
                    <div class="flex items-center gap-4 text-[12px] font-semibold">
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-[#007AFF]"></span>
                            <span class="text-black/70 dark:text-white/70">Tenant Baru</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-[#34C759]"></span>
                            <span class="text-black/70 dark:text-white/70">User Baru</span>
                        </div>
                    </div>
                </div>

                <!-- Canvas -->
                <div class="relative w-full h-[260px] sm:h-[280px]">
                    <canvas id="adminRegistrationChart"></canvas>
                </div>

                <!-- Bottom Footnote -->
                <div class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06] flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 sm:gap-3 text-[11px] text-black/45 dark:text-white/45">
                    <span>Data tersinkron otomatis dari riwayat pendaftaran</span>
                    <span class="tabular-nums font-semibold text-black/70 dark:text-white/70">Total: {{ number_format($totalBusinesses) }} Tenant · {{ number_format($totalUsers) }} Pengguna</span>
                </div>
            </div>

            <!-- Chart Card 2: Donut Chart Paket Langganan (4 Cols) -->
            <div class="lg:col-span-4 rounded-[22px] sm:rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-4 sm:p-6 shadow-xs flex flex-col justify-between space-y-4">
                <div class="pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[14px] sm:text-[15px] font-bold text-black dark:text-white">Komposisi Langganan</h3>
                        <span class="text-[11px] font-bold text-[#007AFF] dark:text-[#0A84FF] tabular-nums">{{ $conversionRate }}% Berbayar</span>
                    </div>
                    <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">Proporsi akun Free vs Core SaaS</p>
                </div>

                <!-- Donut Canvas Container -->
                <div class="relative w-full h-[200px] flex items-center justify-center">
                    <canvas id="adminPlanDonutChart"></canvas>
                </div>

                <!-- Donut Legend Cards -->
                <div class="space-y-2 pt-2 border-t border-black/[0.04] dark:border-white/[0.06] text-[12px]">
                    <div class="flex items-center justify-between p-2 rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03]">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#007AFF]"></span>
                            <span class="text-black/70 dark:text-white/70 font-medium">Core Bulanan</span>
                        </div>
                        <span class="font-bold text-black dark:text-white tabular-nums">{{ $coreMonthlyCount }} <span class="text-[10px] font-normal text-black/45 dark:text-white/45">({{ $totalBusinesses > 0 ? round(($coreMonthlyCount / $totalBusinesses) * 100) : 0 }}%)</span></span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03]">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#34C759]"></span>
                            <span class="text-black/70 dark:text-white/70 font-medium">Core Tahunan</span>
                        </div>
                        <span class="font-bold text-black dark:text-white tabular-nums">{{ $coreAnnualCount }} <span class="text-[10px] font-normal text-black/45 dark:text-white/45">({{ $totalBusinesses > 0 ? round(($coreAnnualCount / $totalBusinesses) * 100) : 0 }}%)</span></span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03]">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#8E8E93]"></span>
                            <span class="text-black/70 dark:text-white/70 font-medium">Free Tier</span>
                        </div>
                        <span class="font-bold text-black dark:text-white tabular-nums">{{ $freeSubscribersCount }} <span class="text-[10px] font-normal text-black/45 dark:text-white/45">({{ $totalBusinesses > 0 ? round(($freeSubscribersCount / $totalBusinesses) * 100) : 0 }}%)</span></span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Charts Row 2: Monthly Billing Revenue (6 Cols) + Ecosystem Activity (6 Cols) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6">

            <!-- Chart Card 3: Arus Pendapatan Billing Bulanan (6 Cols) -->
            <div class="lg:col-span-6 rounded-[22px] sm:rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-4 sm:p-6 shadow-xs flex flex-col justify-between space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                    <div>
                        <h3 class="text-[14px] sm:text-[15px] font-bold text-black dark:text-white">Arus Pembayaran Billing Bulanan</h3>
                        <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">Total penerimaan transfer paket Core yang disetujui (Approved)</p>
                    </div>
                    <span class="text-[11px] font-mono text-[#34C759] font-bold hidden sm:inline tabular-nums">IDR</span>
                </div>

                <!-- Canvas -->
                <div class="relative w-full h-[240px] sm:h-[260px]">
                    <canvas id="adminRevenueBarChart"></canvas>
                </div>

                <div class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06] flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 sm:gap-3 text-[11px] text-black/45 dark:text-white/45">
                    <span>MRR Berjalan: <strong class="text-black dark:text-white tabular-nums">Rp {{ number_format($mrr, 0, ',', '.') }}</strong></span>
                    <span>ARR: <strong class="text-[#34C759] tabular-nums">Rp {{ number_format($arr, 0, ',', '.') }}</strong></span>
                </div>
            </div>

            <!-- Chart Card 4: Distribusi Aktivitas Ekosistem (6 Cols) -->
            <div class="lg:col-span-6 rounded-[22px] sm:rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-4 sm:p-6 shadow-xs flex flex-col justify-between space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                    <div>
                        <h3 class="text-[14px] sm:text-[15px] font-bold text-black dark:text-white">Volume &amp; Aktivitas Fitur Ekosistem</h3>
                        <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">Perbandingan pemanfaatan fitur utama oleh para pemilik usaha UMKM</p>
                    </div>
                </div>

                <!-- Canvas -->
                <div class="relative w-full h-[240px] sm:h-[260px]">
                    <canvas id="adminEcosystemActivityChart"></canvas>
                </div>

                <div class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06] flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 sm:gap-3 text-[11px] text-black/45 dark:text-white/45">
                    <span>Rata-rata Produk/Tenant: <strong class="text-black dark:text-white tabular-nums">{{ $avgProductsPerBusiness }}</strong></span>
                    <span>Rata-rata Run HPP/Tenant: <strong class="text-black dark:text-white tabular-nums">{{ $avgCostingRunsPerBusiness }}</strong></span>
                </div>
            </div>

        </div>

    </div>

    <!-- Bento Quick-Action Tray (Touch-friendly 48-52px height) -->
    <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-4 sm:p-6 shadow-xs space-y-3.5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 flex items-center justify-center text-[#007AFF] dark:text-[#0A84FF]">
                    <i data-lucide="command" class="w-4 h-4" stroke-width="1.8"></i>
                </div>
                <h3 class="text-[15px] font-bold text-black dark:text-white">Pusat Aksi Cepat Administrator</h3>
            </div>
            <span class="text-[12px] text-black/45 dark:text-white/45 hidden sm:inline">Pintas operasi harian platform</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 sm:gap-3 pt-1">
            <a href="{{ route('admin.businesses.index') }}"
                class="h-12 px-3.5 sm:px-4 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.07] dark:hover:bg-white/[0.1] active:scale-[0.98] transition-all flex items-center gap-2.5 text-black dark:text-white font-semibold text-[13px] min-w-0">
                <i data-lucide="building-2" class="w-4.5 h-4.5 text-[#007AFF] dark:text-[#0A84FF] shrink-0" stroke-width="1.8"></i>
                <span class="truncate">Kelola Tenant</span>
            </a>

            @if($pendingSubscriptionsCount > 0 && isset($pendingSubscriptions) && $pendingSubscriptions->isNotEmpty())
            <button type="button" @click="openApproval(@js($pendingSubscriptions->first()))"
                class="h-12 px-3.5 sm:px-4 rounded-[14px] bg-[#FF9500]/10 hover:bg-[#FF9500]/20 active:scale-[0.98] transition-all flex items-center justify-between text-black dark:text-white font-semibold text-[13px] text-left min-w-0 cursor-pointer">
                <div class="flex items-center gap-2 min-w-0 truncate">
                    <i data-lucide="zap" class="w-4.5 h-4.5 text-[#FF9500] shrink-0" stroke-width="2"></i>
                    <span class="truncate">Review Billing</span>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500] text-white shrink-0 ml-1 tabular-nums">
                    {{ $pendingSubscriptionsCount }}
                </span>
            </button>
            @else
            <a href="{{ route('admin.subscriptions.index') }}"
                class="h-12 px-3.5 sm:px-4 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.07] dark:hover:bg-white/[0.1] active:scale-[0.98] transition-all flex items-center justify-between text-black dark:text-white font-semibold text-[13px] min-w-0">
                <div class="flex items-center gap-2 min-w-0 truncate">
                    <i data-lucide="credit-card" class="w-4.5 h-4.5 text-[#34C759] dark:text-[#30D158] shrink-0" stroke-width="1.8"></i>
                    <span class="truncate">Approval Billing</span>
                </div>
            </a>
            @endif

            <a href="{{ route('admin.settings.index') }}"
                class="h-12 px-3.5 sm:px-4 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.07] dark:hover:bg-white/[0.1] active:scale-[0.98] transition-all flex items-center gap-2.5 text-black dark:text-white font-semibold text-[13px] min-w-0">
                <i data-lucide="key-round" class="w-4.5 h-4.5 text-[#FF9500] dark:text-[#FF9F0A] shrink-0" stroke-width="1.8"></i>
                <span class="truncate">Google OAuth</span>
            </a>

            <a href="{{ route('admin.billing-packages.index', 'subscription') }}"
                class="h-12 px-3.5 sm:px-4 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.07] dark:hover:bg-white/[0.1] active:scale-[0.98] transition-all flex items-center gap-2.5 text-black dark:text-white font-semibold text-[13px] min-w-0">
                <i data-lucide="layers-3" class="w-4.5 h-4.5 text-[#AF52DE] dark:text-[#BF5AF2] shrink-0" stroke-width="1.8"></i>
                <span class="truncate">Paket &amp; Harga</span>
            </a>

            <a href="{{ route('admin.users.export') }}"
                class="h-12 px-3.5 sm:px-4 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.07] dark:hover:bg-white/[0.1] active:scale-[0.98] transition-all flex items-center gap-2.5 text-black dark:text-white font-semibold text-[13px] col-span-2 sm:col-span-1 min-w-0">
                <i data-lucide="download" class="w-4.5 h-4.5 text-[#30B0C7] dark:text-[#40C8E0] shrink-0" stroke-width="1.8"></i>
                <span class="truncate">Export User CSV</span>
            </a>
        </div>
    </div>

    <!-- Tables Grid: Recent Users (7 cols) & Recent Businesses (5 cols) with Pop-Up Modal Triggers -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6">

        <!-- Recent Users (7 cols) -->
        <div class="lg:col-span-7 rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md overflow-hidden shadow-xs flex flex-col justify-between">
            <div>
                <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 flex items-center justify-center text-[#007AFF] dark:text-[#0A84FF]">
                            <i data-lucide="user-plus" class="w-4 h-4" stroke-width="1.8"></i>
                        </div>
                        <h3 class="text-[14px] font-bold text-black dark:text-white">Pengguna Terbaru Terdaftar</h3>
                    </div>
                    <a href="{{ route('admin.users.index') }}" class="text-[12px] font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline flex items-center gap-1">
                        <span>Lihat Semua</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5" stroke-width="1.8"></i>
                    </a>
                </div>

                <!-- Desktop Table View (md+) -->
                <div class="hidden md:block overflow-x-auto scrollbar-thin">
                    <table class="w-full text-left text-[13.5px]">
                        <thead>
                            <tr class="border-b border-black/[0.04] dark:border-white/[0.06] bg-black/[0.01] dark:bg-white/[0.02]">
                                <th class="px-5 sm:px-6 py-3 text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Pengguna</th>
                                <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Bisnis Aktif</th>
                                <th class="px-5 sm:px-6 py-3 text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 text-right">Metode Login</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                            @forelse($recentUsers as $user)
                            <tr @click="openUser(@js($user))" class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors cursor-pointer" title="Klik baris untuk membuka detail pop-up">
                                <td class="px-5 sm:px-6 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#007AFF]/20 to-[#5856D6]/20 flex items-center justify-center text-[11px] font-bold text-[#007AFF] dark:text-[#0A84FF] shrink-0">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-black dark:text-white truncate flex items-center gap-1.5">
                                                <span>{{ $user->name }}</span>
                                                <i data-lucide="eye" class="w-3 h-3 text-black/30 dark:text-white/30" stroke-width="2"></i>
                                            </div>
                                            <div class="text-[11px] text-black/45 dark:text-white/45 truncate">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-black/70 dark:text-white/70 truncate max-w-[200px]">
                                    {{ $user->activeBusiness?->name ?? 'Belum ada workspace' }}
                                </td>
                                <td class="px-5 sm:px-6 py-3.5 text-right whitespace-nowrap">
                                    @if($user->google_id)
                                        <span class="inline-flex items-center justify-end gap-1.5 text-[12px] font-medium text-[#C41E17] dark:text-[#FF453A]">
                                            <i data-lucide="chrome" class="w-3.5 h-3.5 shrink-0" stroke-width="2"></i>
                                            <span>Google SSO</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center justify-end gap-1.5 text-[12px] font-medium text-black/55 dark:text-white/55">
                                            <i data-lucide="mail" class="w-3.5 h-3.5 shrink-0 text-black/40 dark:text-white/40" stroke-width="2"></i>
                                            <span>Email</span>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-5 py-8 text-center text-[13px] text-black/45 dark:text-white/45">
                                    Belum ada data pengguna terdaftar.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card List View (< md) -->
                <div class="block md:hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($recentUsers as $user)
                    <div @click="openUser(@js($user))" class="p-3.5 hover:bg-black/[0.02] dark:hover:bg-white/[0.03] active:scale-[0.99] transition-all cursor-pointer space-y-2">
                        <div class="flex items-center justify-between gap-2.5">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-[#007AFF]/20 to-[#5856D6]/20 flex items-center justify-center text-[12px] font-bold text-[#007AFF] dark:text-[#0A84FF] shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-black dark:text-white text-[14px] truncate">{{ $user->name }}</div>
                                    <div class="text-[12px] text-black/45 dark:text-white/45 truncate">{{ $user->email }}</div>
                                </div>
                            </div>
                            <button type="button" class="h-8 px-2.5 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60 text-[11px] font-semibold shrink-0 flex items-center gap-1">
                                <i data-lucide="eye" class="w-3 h-3"></i>
                                <span>Detail</span>
                            </button>
                        </div>
                        <div class="flex items-center justify-between gap-2 text-[11px] pt-1 border-t border-black/[0.03] dark:border-white/[0.04]">
                            <span class="text-black/50 dark:text-white/50 truncate flex items-center gap-1">
                                <i data-lucide="building-2" class="w-3 h-3 text-black/30 dark:text-white/30 shrink-0"></i>
                                <span class="truncate">{{ $user->activeBusiness?->name ?? 'Belum ada workspace' }}</span>
                            </span>
                            <div class="shrink-0">
                                @if($user->google_id)
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-[#C41E17] dark:text-[#FF453A]">
                                        <i data-lucide="chrome" class="w-3 h-3 shrink-0" stroke-width="2"></i>
                                        <span>Google</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-black/55 dark:text-white/55">
                                        <i data-lucide="mail" class="w-3 h-3 shrink-0 text-black/40 dark:text-white/40" stroke-width="2"></i>
                                        <span>Email</span>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center text-[13px] text-black/45 dark:text-white/45">
                        Belum ada data pengguna terdaftar.
                    </div>
                    @endforelse
                </div>
            </div>

            <div class="px-4 sm:px-5 py-3 border-t border-black/[0.04] dark:border-white/[0.06] bg-black/[0.01] dark:bg-white/[0.02] text-[11px] text-black/40 dark:text-white/40 flex items-center justify-between">
                <span>Menampilkan 6 pengguna terbaru (Klik baris untuk Quick Pop-Up)</span>
                <i data-lucide="mouse-pointer-click" class="w-3.5 h-3.5 text-black/30 dark:text-white/30"></i>
            </div>
        </div>

        <!-- Recent Businesses (5 cols) -->
        <div class="lg:col-span-5 rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md overflow-hidden shadow-xs flex flex-col justify-between">
            <div>
                <div class="px-4 sm:px-5 py-3.5 sm:py-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 flex items-center justify-center text-[#34C759] dark:text-[#30D158]">
                            <i data-lucide="store" class="w-4 h-4" stroke-width="1.8"></i>
                        </div>
                        <h3 class="text-[14px] font-bold text-black dark:text-white">Tenant Bisnis Terbaru</h3>
                    </div>
                    <a href="{{ route('admin.businesses.index') }}" class="text-[12px] font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline flex items-center gap-1">
                        <span>Lihat Semua</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5" stroke-width="1.8"></i>
                    </a>
                </div>

                <!-- Desktop Table View (md+) -->
                <div class="hidden md:block overflow-x-auto scrollbar-thin">
                    <table class="w-full text-left text-[13.5px]">
                        <thead>
                            <tr class="border-b border-black/[0.04] dark:border-white/[0.06] bg-black/[0.01] dark:bg-white/[0.02]">
                                <th class="px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Bisnis</th>
                                <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Paket</th>
                                <th class="px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                            @forelse($recentBusinesses as $biz)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                <td class="px-5 py-3.5 cursor-pointer" @click="openBusiness(@js($biz))" title="Buka Detail Pop-up">
                                    <div class="font-bold text-black dark:text-white truncate flex items-center gap-1.5">
                                        <span>{{ $biz->name }}</span>
                                    </div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums mt-0.5">{{ $biz->users->count() }} Pengguna</div>
                                </td>
                                <td class="px-3 py-3.5">
                                    @if($biz->subscription?->isCorePlan())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                            Core
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55">
                                            Free
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" @click="openBusiness(@js($biz))"
                                            class="h-7 px-2.5 rounded-[8px] text-[12px] font-bold bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] hover:bg-[#007AFF]/20 active:scale-95 transition-all inline-flex items-center gap-1 cursor-pointer"
                                            title="Buka pop-up modal detail tenant">
                                            <i data-lucide="eye" class="w-3 h-3" stroke-width="2"></i>
                                            <span>Pop-up</span>
                                        </button>
                                        <a href="{{ route('admin.businesses.show', $biz->id) }}"
                                            class="h-7 px-2 rounded-[8px] text-[12px] font-medium text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 active:scale-95 transition-all inline-flex items-center gap-0.5"
                                            title="Buka halaman penuh">
                                            <i data-lucide="arrow-right" class="w-3 h-3" stroke-width="2"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-5 py-8 text-center text-[13px] text-black/45 dark:text-white/45">
                                    Belum ada tenant bisnis terdaftar.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card List View (< md) -->
                <div class="block md:hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($recentBusinesses as $biz)
                    <div class="p-3.5 hover:bg-black/[0.02] dark:hover:bg-white/[0.03] active:scale-[0.99] transition-all space-y-2">
                        <div class="flex items-center justify-between gap-2.5">
                            <div class="min-w-0 cursor-pointer flex-1" @click="openBusiness(@js($biz))">
                                <div class="font-bold text-black dark:text-white text-[14px] truncate">{{ $biz->name }}</div>
                                <div class="text-[12px] text-black/45 dark:text-white/45 tabular-nums mt-0.5">{{ $biz->users->count() }} Pengguna Terdaftar</div>
                            </div>
                            <div class="shrink-0 flex items-center gap-1.5">
                                @if($biz->subscription?->isCorePlan())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                        Core
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55">
                                        Free
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center justify-between gap-2 pt-1 border-t border-black/[0.03] dark:border-white/[0.04]">
                            <button type="button" @click="openBusiness(@js($biz))"
                                class="h-8 px-3 rounded-[9px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] text-[12px] font-semibold active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                <span>Tinjau Pop-up</span>
                            </button>
                            <a href="{{ route('admin.businesses.show', $biz->id) }}"
                                class="h-8 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60 text-[12px] font-semibold active:scale-95 transition-all flex items-center gap-1">
                                <span>Halaman Penuh</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center text-[13px] text-black/45 dark:text-white/45">
                        Belum ada tenant bisnis terdaftar.
                    </div>
                    @endforelse
                </div>
            </div>

            <div class="px-4 sm:px-5 py-3 border-t border-black/[0.04] dark:border-white/[0.06] bg-black/[0.01] dark:bg-white/[0.02] text-[11px] text-black/40 dark:text-white/40 flex items-center justify-between">
                <span>Menampilkan 6 tenant bisnis terbaru bergabung</span>
                <i data-lucide="store" class="w-3.5 h-3.5 text-black/30 dark:text-white/30"></i>
            </div>
        </div>

    </div>

    <!-- MODAL 1: Detail Bisnis Pop-Up / Modal Sheet (Mandat Pop-Up First) -->
    <div x-show="showBusinessModal" x-cloak class="relative z-50" aria-labelledby="business-modal-title" role="dialog" aria-modal="true">
        <div x-show="showBusinessModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="showBusinessModal = false"
            class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto flex items-end sm:items-center justify-center p-0 sm:p-4">
            <div x-show="showBusinessModal"
                x-transition:enter="ease-out duration-300 transform"
                x-transition:enter-start="translate-y-full sm:scale-95 opacity-0"
                x-transition:enter-end="translate-y-0 sm:scale-100 opacity-100"
                x-transition:leave="ease-in duration-200 transform"
                x-transition:leave-start="translate-y-0 sm:scale-100 opacity-100"
                x-transition:leave-end="translate-y-full sm:scale-95 opacity-0"
                @click.outside="showBusinessModal = false"
                class="w-full max-w-lg rounded-t-[28px] sm:rounded-[24px] sheet-material p-4 sm:p-6 border-t sm:border border-black/[0.08] dark:border-white/[0.12] shadow-2xl space-y-4 max-h-[90vh] sm:max-h-[85vh] overflow-y-auto">
                
                <!-- Mobile Grabber -->
                <div class="w-10 h-1.5 rounded-full bg-gray-300 dark:bg-gray-600 mx-auto -mt-1 mb-2 sm:hidden"></div>

                <!-- Modal Header -->
                <div class="flex items-start justify-between pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center font-bold">
                            <i data-lucide="building-2" class="w-5 h-5" stroke-width="2"></i>
                        </div>
                        <div>
                            <h3 id="business-modal-title" class="text-[17px] font-bold text-black dark:text-white" x-text="selectedBusiness?.name"></h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50" x-text="'Slug: ' + (selectedBusiness?.slug || '-') + (selectedBusiness?.owner?.name ? ' • Pemilik: ' + selectedBusiness.owner.name : '')"></p>
                        </div>
                    </div>
                    <button type="button" @click="showBusinessModal = false"
                        class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white flex items-center justify-center active:scale-95 transition-all cursor-pointer"
                        aria-label="Tutup">
                        <i data-lucide="x" class="w-4 h-4" stroke-width="2"></i>
                    </button>
                </div>

                <!-- Modal Content Bento Inset -->
                <div class="space-y-3 text-[13px]">
                    <div class="grid grid-cols-2 gap-2.5">
                        <div class="p-3 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04]">
                            <div class="text-[11px] text-black/45 dark:text-white/45">Status Langganan</div>
                            <div class="font-bold text-black dark:text-white mt-0.5">
                                <span x-show="selectedBusiness?.subscription?.plan_code?.includes('core')" class="text-[#248A3D] dark:text-[#30D158]">Core Plan Aktif</span>
                                <span x-show="!selectedBusiness?.subscription?.plan_code?.includes('core')" class="text-black/60 dark:text-white/60">Free Plan</span>
                            </div>
                        </div>
                        <div class="p-3 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04]">
                            <div class="text-[11px] text-black/45 dark:text-white/45">Tim &amp; Staf</div>
                            <div class="font-bold text-black dark:text-white mt-0.5" x-text="(selectedBusiness?.users?.length || 0) + ' Akun Terdaftar'"></div>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] space-y-2">
                        <div class="flex justify-between items-center text-[12px]">
                            <span class="text-black/50 dark:text-white/50">Tanggal Bergabung</span>
                            <span class="font-medium text-black dark:text-white tabular-nums" x-text="selectedBusiness?.created_at ? new Date(selectedBusiness.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-'"></span>
                        </div>
                        <div class="flex justify-between items-center text-[12px]">
                            <span class="text-black/50 dark:text-white/50">Mata Uang Default</span>
                            <span class="font-bold text-black dark:text-white" x-text="selectedBusiness?.currency || 'IDR'"></span>
                        </div>
                        <div class="flex justify-between items-center text-[12px]">
                            <span class="text-black/50 dark:text-white/50">ID Workspace</span>
                            <span class="font-mono text-[11px] text-black/60 dark:text-white/60 truncate max-w-[200px]" x-text="selectedBusiness?.id"></span>
                        </div>
                    </div>

                    <!-- Microcopy Reassurance -->
                    <div class="p-3 rounded-[12px] bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] text-[11px] flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 shrink-0" stroke-width="2"></i>
                        <span>💡 Isolasi multi-tenant terverifikasi: seluruh data keuangan &amp; POS tenant ini tersimpan aman.</span>
                    </div>
                </div>

                <!-- Modal Actions (Min 48px height on mobile) -->
                <div class="pt-2 flex flex-col sm:flex-row items-center gap-2.5">
                    <a :href="'/admin/businesses/' + selectedBusiness?.id"
                        class="w-full sm:flex-1 h-12 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white font-bold text-[13px] flex items-center justify-center gap-2 shadow-md shadow-[#007AFF]/25 transition-all">
                        <i data-lucide="building-2" class="w-4 h-4" stroke-width="2"></i>
                        <span>Buka Halaman Lengkap Tenant</span>
                    </a>
                    <button type="button" @click="showBusinessModal = false"
                        class="w-full sm:w-auto h-12 px-5 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 font-semibold text-[13px] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 2: Detail Pengguna Pop-Up / Modal Sheet (Mandat Pop-Up First) -->
    <div x-show="showUserModal" x-cloak class="relative z-50" aria-labelledby="user-modal-title" role="dialog" aria-modal="true">
        <div x-show="showUserModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="showUserModal = false"
            class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto flex items-end sm:items-center justify-center p-0 sm:p-4">
            <div x-show="showUserModal"
                x-transition:enter="ease-out duration-300 transform"
                x-transition:enter-start="translate-y-full sm:scale-95 opacity-0"
                x-transition:enter-end="translate-y-0 sm:scale-100 opacity-100"
                x-transition:leave="ease-in duration-200 transform"
                x-transition:leave-start="translate-y-0 sm:scale-100 opacity-100"
                x-transition:leave-end="translate-y-full sm:scale-95 opacity-0"
                @click.outside="showUserModal = false"
                class="w-full max-w-md rounded-t-[28px] sm:rounded-[24px] sheet-material p-4 sm:p-6 border-t sm:border border-black/[0.08] dark:border-white/[0.12] shadow-2xl space-y-4 max-h-[90vh] sm:max-h-[85vh] overflow-y-auto">
                
                <!-- Mobile Grabber -->
                <div class="w-10 h-1.5 rounded-full bg-gray-300 dark:bg-gray-600 mx-auto -mt-1 mb-2 sm:hidden"></div>

                <!-- Modal Header -->
                <div class="flex items-start justify-between pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-full bg-gradient-to-br from-[#007AFF] to-[#5856D6] text-white flex items-center justify-center font-extrabold text-sm shadow-md shadow-[#007AFF]/25 shrink-0">
                            <span x-text="selectedUser?.name ? selectedUser.name.substring(0, 2).toUpperCase() : 'U'"></span>
                        </div>
                        <div class="min-w-0">
                            <h3 id="user-modal-title" class="text-[17px] font-bold text-black dark:text-white truncate" x-text="selectedUser?.name"></h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50 truncate" x-text="selectedUser?.email"></p>
                        </div>
                    </div>
                    <button type="button" @click="showUserModal = false"
                        class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white flex items-center justify-center active:scale-95 transition-all cursor-pointer"
                        aria-label="Tutup">
                        <i data-lucide="x" class="w-4 h-4" stroke-width="2"></i>
                    </button>
                </div>

                <!-- User Details Inset -->
                <div class="space-y-3 text-[13px]">
                    <div class="p-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] space-y-2.5">
                        <div class="flex justify-between items-center">
                            <span class="text-black/50 dark:text-white/50">Workspace Aktif</span>
                            <span class="font-bold text-black dark:text-white truncate max-w-[200px]" x-text="selectedUser?.active_business?.name || selectedUser?.activeBusiness?.name || 'Belum ada workspace'"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-black/50 dark:text-white/50">Terdaftar Sejak</span>
                            <span class="font-medium text-black dark:text-white tabular-nums" x-text="selectedUser?.created_at ? new Date(selectedUser.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-'"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-black/50 dark:text-white/50">Status Email</span>
                            <span x-show="selectedUser?.email_verified_at" class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                Terverifikasi
                            </span>
                            <span x-show="!selectedUser?.email_verified_at" class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/15 text-[#C97800] dark:text-[#FF9F0A]">
                                Belum Verifikasi
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-black/50 dark:text-white/50">Metode Login</span>
                            <span x-show="selectedUser?.google_id" class="inline-flex items-center gap-1.5 text-[12px] font-medium text-[#C41E17] dark:text-[#FF453A]">
                                <i data-lucide="chrome" class="w-3.5 h-3.5 shrink-0" stroke-width="2"></i>
                                <span>Google SSO</span>
                            </span>
                            <span x-show="!selectedUser?.google_id" class="inline-flex items-center gap-1.5 text-[12px] font-medium text-black/60 dark:text-white/60">
                                <i data-lucide="mail" class="w-3.5 h-3.5 shrink-0 text-black/40 dark:text-white/40" stroke-width="2"></i>
                                <span>Email &amp; Password</span>
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-black/50 dark:text-white/50">Nomor Telepon</span>
                            <span class="font-medium text-black dark:text-white tabular-nums" x-text="selectedUser?.phone || '-'"></span>
                        </div>
                    </div>

                    <!-- Microcopy Reassurance -->
                    <div class="p-3 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] text-[11px] flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 shrink-0" stroke-width="2"></i>
                        <span>💡 Akun pengguna terhubung dengan RBAC platform Cooca dan memiliki izin sesuai hak akses tenant.</span>
                    </div>
                </div>

                <!-- Modal Actions (Min 48px height on mobile) -->
                <div class="pt-2 flex flex-col sm:flex-row items-center gap-2.5">
                    <a href="{{ route('admin.users.index') }}"
                        class="w-full sm:flex-1 h-12 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white font-bold text-[13px] flex items-center justify-center gap-2 shadow-md shadow-[#007AFF]/25 transition-all">
                        <i data-lucide="users" class="w-4 h-4" stroke-width="2"></i>
                        <span>Kelola Pengguna di Basis Data</span>
                    </a>
                    <button type="button" @click="showUserModal = false"
                        class="w-full sm:w-auto h-12 px-5 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 font-semibold text-[13px] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 3: Quick Approval & Billing Verification Modal Sheet (Mandat Pop-Up First) -->
    <div x-show="showApprovalModal" x-cloak class="relative z-50" aria-labelledby="approval-modal-title" role="dialog" aria-modal="true">
        <div x-show="showApprovalModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="showApprovalModal = false"
            class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto flex items-end sm:items-center justify-center p-0 sm:p-4">
            <div x-show="showApprovalModal"
                x-transition:enter="ease-out duration-300 transform"
                x-transition:enter-start="translate-y-full sm:scale-95 opacity-0"
                x-transition:enter-end="translate-y-0 sm:scale-100 opacity-100"
                x-transition:leave="ease-in duration-200 transform"
                x-transition:leave-start="translate-y-0 sm:scale-100 opacity-100"
                x-transition:leave-end="translate-y-full sm:scale-95 opacity-0"
                @click.outside="showApprovalModal = false"
                class="w-full max-w-xl rounded-t-[28px] sm:rounded-[24px] sheet-material p-4 sm:p-6 border-t sm:border border-black/[0.08] dark:border-white/[0.12] shadow-2xl space-y-4 max-h-[90vh] sm:max-h-[85vh] overflow-y-auto">
                
                <!-- Mobile Grabber -->
                <div class="w-10 h-1.5 rounded-full bg-gray-300 dark:bg-gray-600 mx-auto -mt-1 mb-2 sm:hidden"></div>

                <!-- Modal Header -->
                <div class="flex items-start justify-between pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[12px] bg-[#FF9500]/15 text-[#FF9500] dark:text-[#FF9F0A] flex items-center justify-center font-bold">
                            <i data-lucide="receipt" class="w-5 h-5" stroke-width="2"></i>
                        </div>
                        <div>
                            <h3 id="approval-modal-title" class="text-[17px] font-bold text-black dark:text-white">Verifikasi Bukti Transfer Langganan</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50">Tinjau, setujui, atau tolak aktivasi paket Core seketika</p>
                        </div>
                    </div>
                    <button type="button" @click="showApprovalModal = false"
                        class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white flex items-center justify-center active:scale-95 transition-all cursor-pointer"
                        aria-label="Tutup">
                        <i data-lucide="x" class="w-4 h-4" stroke-width="2"></i>
                    </button>
                </div>

                <!-- Details & Proof Preview -->
                <div class="space-y-3.5 text-[13px]" x-show="selectedPayment">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04]">
                            <div class="text-[11px] text-black/45 dark:text-white/45">Nomor Order</div>
                            <div class="font-mono font-bold text-black dark:text-white mt-0.5 tabular-nums" x-text="selectedPayment?.order_number || '-'"></div>
                        </div>
                        <div class="p-3 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04]">
                            <div class="text-[11px] text-black/45 dark:text-white/45">Total Tagihan</div>
                            <div class="font-bold text-[#007AFF] dark:text-[#0A84FF] text-[15px] tabular-nums mt-0.5"
                                x-text="'Rp ' + (selectedPayment?.total_payable ? Number(selectedPayment.total_payable).toLocaleString('id-ID') : (selectedPayment?.total_amount ? Number(selectedPayment.total_amount).toLocaleString('id-ID') : '0'))"></div>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] space-y-2 text-[12px]">
                        <div class="flex justify-between items-center">
                            <span class="text-black/50 dark:text-white/50">Tenant Bisnis</span>
                            <span class="font-bold text-black dark:text-white" x-text="selectedPayment?.business?.name || '-'"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-black/50 dark:text-white/50">Pemohon / Akun</span>
                            <span class="font-medium text-black dark:text-white" x-text="selectedPayment?.user?.name || '-'"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-black/50 dark:text-white/50">Paket Langganan</span>
                            <span class="font-bold text-[#34C759] dark:text-[#30D158]" x-text="selectedPayment?.package_name || 'Paket Core'"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-black/50 dark:text-white/50">Pengirim &amp; Rekening</span>
                            <span class="font-medium text-black dark:text-white tabular-nums" x-text="(selectedPayment?.sender_bank || '') + ' - ' + (selectedPayment?.sender_account_name || '-')"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-black/50 dark:text-white/50">Diajukan Pada</span>
                            <span class="font-medium text-black dark:text-white tabular-nums" x-text="selectedPayment?.created_at ? new Date(selectedPayment.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-'"></span>
                        </div>
                    </div>

                    <!-- Bukti Transfer Preview Card with Instant Pop-Up Lightbox Trigger -->
                    <div class="p-3 rounded-[14px] border border-black/[0.06] dark:border-white/[0.08] bg-black/[0.01] dark:bg-white/[0.02] flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                                <i data-lucide="image" class="w-4 h-4" stroke-width="2"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-[12px] text-black dark:text-white truncate">Bukti Struk Transfer</div>
                                <div class="text-[11px] text-black/45 dark:text-white/45 truncate" x-text="selectedPayment?.payment_proof_path ? 'Berkas bukti terlampir' : 'Belum mengunggah bukti'"></div>
                            </div>
                        </div>

                        <template x-if="selectedPayment?.payment_proof_path">
                            <div class="flex items-center gap-2">
                                <button type="button" @click="previewProof('/storage/' + selectedPayment.payment_proof_path)"
                                    class="h-9 px-3 rounded-[10px] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 active:scale-95 text-[#007AFF] dark:text-[#0A84FF] text-[12px] font-bold flex items-center gap-1.5 transition-all shrink-0 cursor-pointer">
                                    <i data-lucide="maximize-2" class="w-3.5 h-3.5" stroke-width="2"></i>
                                    <span>Perbesar</span>
                                </button>
                                <a :href="'/storage/' + selectedPayment.payment_proof_path" target="_blank"
                                    class="h-9 px-2.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-black/60 dark:text-white/60 text-[12px] font-medium flex items-center gap-1 transition-all shrink-0"
                                    title="Buka tab baru">
                                    <i data-lucide="external-link" class="w-3.5 h-3.5" stroke-width="2"></i>
                                </a>
                            </div>
                        </template>
                    </div>

                    <!-- Apple-Style Segmented Tab for Action: Approve vs Reject -->
                    <div class="p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] flex items-center gap-1">
                        <button type="button" @click="approvalTab = 'approve'"
                            :class="approvalTab === 'approve' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/60 dark:text-white/60 font-medium'"
                            class="flex-1 py-2 px-3 rounded-[9px] text-[12px] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759]"></i>
                            <span>Setujui (Approve)</span>
                        </button>
                        <button type="button" @click="approvalTab = 'reject'"
                            :class="approvalTab === 'reject' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/60 dark:text-white/60 font-medium'"
                            class="flex-1 py-2 px-3 rounded-[9px] text-[12px] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                            <i data-lucide="x-circle" class="w-4 h-4 text-[#FF3B30]"></i>
                            <span>Tolak (Reject)</span>
                        </button>
                    </div>

                    <!-- Tab 1: Action Approval Form Direct 1-Click with Optional Notes -->
                    <form x-show="approvalTab === 'approve'" :action="'/admin/subscriptions/' + selectedPayment?.id + '/approve'" method="POST" class="pt-1 space-y-3">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] mb-1">
                                Catatan Persetujuan (Opsional)
                            </label>
                            <input type="text" name="admin_notes" value="Disetujui via Quick Approval Dashboard"
                                class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white text-[16px] sm:text-[13px] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all"
                                placeholder="Tuliskan catatan verifikasi...">
                        </div>

                        <div class="grid grid-cols-2 gap-2.5 pt-1">
                            <button type="submit"
                                class="col-span-2 sm:col-span-1 h-12 px-4 rounded-[14px] bg-[#34C759] hover:bg-[#2EB84E] active:scale-[0.98] text-white font-bold text-[13px] flex items-center justify-center gap-2 shadow-md shadow-[#34C759]/25 transition-all cursor-pointer">
                                <i data-lucide="check-circle-2" class="w-4.5 h-4.5" stroke-width="2"></i>
                                <span>Setujui &amp; Aktifkan</span>
                            </button>

                            <a :href="'/admin/subscriptions/' + selectedPayment?.id"
                                class="col-span-2 sm:col-span-1 h-12 px-4 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white font-bold text-[13px] flex items-center justify-center gap-2 shadow-md shadow-[#007AFF]/25 transition-all">
                                <i data-lucide="file-search" class="w-4.5 h-4.5" stroke-width="2"></i>
                                <span>Verifikasi Lengkap</span>
                            </a>
                        </div>
                    </form>

                    <!-- Tab 2: Action Rejection Form Direct with Required Reason -->
                    <form x-show="approvalTab === 'reject'" :action="'/admin/subscriptions/' + selectedPayment?.id + '/reject'" method="POST" class="pt-1 space-y-3">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#FF3B30] dark:text-[#FF453A] mb-1">
                                Alasan Penolakan (Wajib Diisi) *
                            </label>
                            <textarea name="reason" required rows="2"
                                class="w-full p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-[#FF3B30]/30 dark:border-[#FF453A]/30 text-black dark:text-white text-[16px] sm:text-[13px] focus:outline-none focus:ring-2 focus:ring-[#FF3B30]/40 transition-all"
                                placeholder="Contoh: Bukti transfer buram, mutasi rekening belum masuk, atau nominal transfer tidak sesuai..."></textarea>
                            <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Alasan ini akan disimpan di riwayat transaksi untuk transparansi.</p>
                        </div>

                        <div class="grid grid-cols-2 gap-2.5 pt-1">
                            <button type="submit"
                                class="col-span-2 sm:col-span-1 h-12 px-4 rounded-[14px] bg-[#FF3B30] hover:bg-[#E02D23] active:scale-[0.98] text-white font-bold text-[13px] flex items-center justify-center gap-2 shadow-md shadow-[#FF3B30]/25 transition-all cursor-pointer">
                                <i data-lucide="x-circle" class="w-4.5 h-4.5" stroke-width="2"></i>
                                <span>Tolak Pembayaran Ini</span>
                            </button>

                            <a :href="'/admin/subscriptions/' + selectedPayment?.id"
                                class="col-span-2 sm:col-span-1 h-12 px-4 rounded-[14px] bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.1] text-black/80 dark:text-white/80 font-bold text-[13px] flex items-center justify-center gap-2 transition-all">
                                <i data-lucide="file-search" class="w-4.5 h-4.5" stroke-width="2"></i>
                                <span>Lihat Rincian</span>
                            </a>
                        </div>
                    </form>

                    <!-- Microcopy Reassurance -->
                    <div class="p-2.5 rounded-[10px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/[0.04] dark:border-white/[0.06] text-[11px] text-black/50 dark:text-white/50 flex items-center gap-2">
                        <i data-lucide="shield-alert" class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i>
                        <span>Audit Superadmin: Setiap aksi persetujuan atau penolakan dicatat otomatis dengan stempel waktu dan ID admin.</span>
                    </div>
                </div>

                <!-- Footer button -->
                <button type="button" @click="showApprovalModal = false"
                    class="w-full h-12 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 font-semibold text-[13px] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all cursor-pointer">
                    Tutup Dialog
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL 4: Payment Proof Image Lightbox Modal Sheet (Mandat Pop-Up First) -->
    <div x-show="proofModalOpen" x-cloak class="relative z-[60]" aria-labelledby="proof-lightbox-title" role="dialog" aria-modal="true">
        <div x-show="proofModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="proofModalOpen = false"
            class="fixed inset-0 bg-black/60 backdrop-blur-md transition-opacity"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto flex items-end sm:items-center justify-center p-0 sm:p-4">
            <div x-show="proofModalOpen"
                x-transition:enter="ease-out duration-300 transform"
                x-transition:enter-start="translate-y-full sm:scale-95 opacity-0"
                x-transition:enter-end="translate-y-0 sm:scale-100 opacity-100"
                x-transition:leave="ease-in duration-200 transform"
                x-transition:leave-start="translate-y-0 sm:scale-100 opacity-100"
                x-transition:leave-end="translate-y-full sm:scale-95 opacity-0"
                @click.outside="proofModalOpen = false"
                class="w-full max-w-2xl rounded-t-[28px] sm:rounded-[24px] sheet-material p-4 sm:p-6 border-t sm:border border-black/[0.08] dark:border-white/[0.12] shadow-2xl space-y-4 max-h-[90vh] sm:max-h-[85vh] overflow-y-auto">
                
                <!-- iOS Grabber Handle for Mobile -->
                <div class="w-10 h-1.5 rounded-full bg-gray-300 dark:bg-gray-600 mx-auto -mt-1 mb-2.5 sm:hidden"></div>

                <!-- Header -->
                <div class="flex items-center justify-between pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-[11px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center">
                            <i data-lucide="image" class="w-5 h-5" stroke-width="2"></i>
                        </div>
                        <div>
                            <h3 id="proof-lightbox-title" class="text-[16px] font-bold text-black dark:text-white">Peninjauan Bukti Pembayaran</h3>
                            <p class="text-[11px] text-[#8E8E93] dark:text-[#98989D]">Struk transfer pembayaran langganan Core</p>
                        </div>
                    </div>
                    <button type="button" @click="proofModalOpen = false"
                        class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white flex items-center justify-center active:scale-95 transition-all cursor-pointer"
                        aria-label="Tutup">
                        <i data-lucide="x" class="w-4 h-4" stroke-width="2"></i>
                    </button>
                </div>

                <!-- Image Display with Container -->
                <div class="rounded-[18px] overflow-hidden bg-black/5 dark:bg-white/5 border border-black/[0.06] dark:border-white/[0.08] flex items-center justify-center min-h-[220px] max-h-[60vh] p-2">
                    <img :src="previewProofUrl" alt="Bukti Transfer Pembayaran" class="max-h-[56vh] w-auto max-w-full object-contain rounded-[12px] shadow-xs">
                </div>

                <!-- Footer Actions (Min 48px height on mobile) -->
                <div class="pt-2 flex flex-col sm:flex-row items-center gap-2.5">
                    <a :href="previewProofUrl" target="_blank"
                        class="w-full sm:flex-1 h-12 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white font-bold text-[13px] flex items-center justify-center gap-2 shadow-xs shadow-[#007AFF]/25 transition-all">
                        <i data-lucide="external-link" class="w-4 h-4" stroke-width="2"></i>
                        <span>Buka Tab Terpisah / Unduh Struk</span>
                    </a>
                    <button type="button" @click="proofModalOpen = false"
                        class="w-full sm:w-auto h-12 px-6 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 font-semibold text-[13px] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart === 'undefined') {
        return;
    }

    const isDark = () => document.documentElement.classList.contains('dark');
    const getTextColor = () => isDark() ? '#98989D' : '#636366';
    const getGridColor = () => isDark() ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.05)';

    // ── 1. Registration Growth Chart (Smooth Area Line) ──
    const regCanvas = document.getElementById('adminRegistrationChart');
    let regChart = null;
    if (regCanvas) {
        const regCtx = regCanvas.getContext('2d');
        regChart = new Chart(regCtx, {
            type: 'line',
            data: {
                labels: @json($chartMonths),
                datasets: [
                    {
                        label: 'Tenant Baru',
                        data: @json($businessMonthlyTrend),
                        borderColor: '#007AFF',
                        backgroundColor: 'rgba(0, 122, 255, 0.12)',
                        fill: true,
                        tension: 0.38,
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#007AFF',
                        pointBorderColor: '#ffffff',
                    },
                    {
                        label: 'Pengguna Baru',
                        data: @json($userMonthlyTrend),
                        borderColor: '#34C759',
                        backgroundColor: 'rgba(52, 199, 89, 0.10)',
                        fill: true,
                        tension: 0.38,
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#34C759',
                        pointBorderColor: '#ffffff',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: isDark() ? '#2C2C2E' : '#ffffff',
                        titleColor: isDark() ? '#ffffff' : '#000000',
                        bodyColor: isDark() ? '#EBEBF5' : '#1C1C1E',
                        borderColor: isDark() ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.08)',
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 10,
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: getTextColor(), font: { size: 11, family: '-apple-system, sans-serif' } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: getGridColor() },
                        ticks: {
                            precision: 0,
                            color: getTextColor(),
                            font: { size: 11, family: '-apple-system, sans-serif' }
                        }
                    }
                }
            }
        });
    }

    // ── 2. Subscription Plans Donut Chart ──
    const donutCanvas = document.getElementById('adminPlanDonutChart');
    let donutChart = null;
    if (donutCanvas) {
        const donutCtx = donutCanvas.getContext('2d');
        donutChart = new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: ['Free Tier', 'Core Bulanan', 'Core Tahunan'],
                datasets: [{
                    data: [
                        {{ (int) ($subscriptionBreakdown['free'] ?? 0) }},
                        {{ (int) ($subscriptionBreakdown['monthly'] ?? 0) }},
                        {{ (int) ($subscriptionBreakdown['annual'] ?? 0) }}
                    ],
                    backgroundColor: ['#8E8E93', '#007AFF', '#34C759'],
                    borderColor: isDark() ? '#1C1C1E' : '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: isDark() ? '#2C2C2E' : '#ffffff',
                        titleColor: isDark() ? '#ffffff' : '#000000',
                        bodyColor: isDark() ? '#EBEBF5' : '#1C1C1E',
                        borderColor: isDark() ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.08)',
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 10,
                        callbacks: {
                            label: function(ctx) {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const val = ctx.raw;
                                const pct = total > 0 ? Math.round((val / total) * 100) : 0;
                                return ` ${ctx.label}: ${val} tenant (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // ── 3. Monthly Revenue Bar Chart ──
    const revCanvas = document.getElementById('adminRevenueBarChart');
    let revChart = null;
    if (revCanvas) {
        const revCtx = revCanvas.getContext('2d');
        revChart = new Chart(revCtx, {
            type: 'bar',
            data: {
                labels: @json($chartMonths),
                datasets: [{
                    label: 'Pendapatan Billing',
                    data: @json($revenueMonthlyTrend),
                    backgroundColor: '#007AFF',
                    hoverBackgroundColor: '#0051D5',
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: isDark() ? '#2C2C2E' : '#ffffff',
                        titleColor: isDark() ? '#ffffff' : '#000000',
                        bodyColor: isDark() ? '#EBEBF5' : '#1C1C1E',
                        borderColor: isDark() ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.08)',
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 10,
                        callbacks: {
                            label: function(ctx) {
                                return ` Omset: Rp ${Number(ctx.raw).toLocaleString('id-ID')}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: getTextColor(), font: { size: 11, family: '-apple-system, sans-serif' } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: getGridColor() },
                        ticks: {
                            color: getTextColor(),
                            font: { size: 11, family: '-apple-system, sans-serif' },
                            callback: function(value) {
                                if (value >= 1000000) return (value / 1000000) + ' Jt';
                                if (value >= 1000) return (value / 1000) + ' Rb';
                                return value;
                            }
                        }
                    }
                }
            }
        });
    }

    // ── 4. Ecosystem Activity Horizontal Bar Chart ──
    const actCanvas = document.getElementById('adminEcosystemActivityChart');
    let actChart = null;
    if (actCanvas) {
        const actCtx = actCanvas.getContext('2d');
        actChart = new Chart(actCtx, {
            type: 'bar',
            data: {
                labels: ['Katalog Produk', 'Hitung HPP', 'Token AI (k)', 'Tenant Bisnis', 'User'],
                datasets: [{
                    label: 'Volume',
                    data: [
                        {{ (int) ($ecosystemStats['total_products'] ?? 0) }},
                        {{ (int) ($ecosystemStats['total_costing_runs'] ?? 0) }},
                        {{ (int) ($ecosystemStats['total_ai_tokens_k'] ?? 0) }},
                        {{ (int) ($ecosystemStats['total_businesses'] ?? 0) }},
                        {{ (int) ($ecosystemStats['total_users'] ?? 0) }}
                    ],
                    backgroundColor: ['#007AFF', '#FF9500', '#AF52DE', '#34C759', '#5856D6'],
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: isDark() ? '#2C2C2E' : '#ffffff',
                        titleColor: isDark() ? '#ffffff' : '#000000',
                        bodyColor: isDark() ? '#EBEBF5' : '#1C1C1E',
                        borderColor: isDark() ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.08)',
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 10,
                        callbacks: {
                            label: function(ctx) {
                                return ` ${ctx.label}: ${Number(ctx.raw).toLocaleString('id-ID')}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: getGridColor() },
                        ticks: { color: getTextColor(), font: { size: 11, family: '-apple-system, sans-serif' } }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: getTextColor(), font: { size: 11, family: '-apple-system, sans-serif' } }
                    }
                }
            }
        });
    }

    // ── 5. Theme Reactive Update Listener ──
    const updateChartsTheme = () => {
        const textColor = getTextColor();
        const gridColor = getGridColor();
        const donutBorder = isDark() ? '#1C1C1E' : '#ffffff';

        [regChart, revChart, actChart].forEach(chart => {
            if (!chart) return;
            if (chart.options.scales.x) chart.options.scales.x.ticks.color = textColor;
            if (chart.options.scales.y) {
                chart.options.scales.y.ticks.color = textColor;
                if (chart.options.scales.y.grid) chart.options.scales.y.grid.color = gridColor;
            }
            chart.update();
        });

        if (donutChart) {
            donutChart.data.datasets[0].borderColor = donutBorder;
            donutChart.update();
        }
    };

    const observer = new MutationObserver(() => updateChartsTheme());
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});
</script>
@endpush