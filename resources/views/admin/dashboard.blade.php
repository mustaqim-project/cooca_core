@extends('layouts.admin', [
    'title' => 'Dashboard — Admin Console',
    'headerTitle' => 'Statistik & Ringkasan Platform SaaS',
    'headerSubtitle' => 'Pantau pertumbuhan tenant UMKM, pendapatan MRR, langganan Core, dan konsumsi token AI'
])

@section('content')
<div class="space-y-6">

    <!-- Pending Payment Alert Banner -->
    @if($pendingSubscriptionsCount > 0)
    <div class="rounded-[14px] px-4 py-3.5 bg-[#FF9500]/10 border border-[#FF9500]/25 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-[10px] bg-[#FF9500]/15 flex items-center justify-center text-[#FF9500] dark:text-[#FF9F0A] shrink-0">
                <i data-lucide="bell-ring" class="w-4.5 h-4.5" stroke-width="1.5"></i>
            </div>
            <div>
                <p class="text-[13px] font-semibold text-black dark:text-white flex items-center gap-2">
                    <span>Verifikasi Pembayaran Diperlukan</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A]">{{ $pendingSubscriptionsCount }} Menunggu</span>
                </p>
                <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Ada bukti transfer pelanggan yang baru saja diunggah dan membutuhkan konfirmasi aktivasi paket Core.</p>
            </div>
        </div>
        <a href="{{ route('admin.subscriptions.index') }}" class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 text-white text-[13px] font-semibold transition-all flex items-center gap-1.5 whitespace-nowrap shrink-0">
            <i data-lucide="check-circle" class="w-4 h-4" stroke-width="1.5"></i>
            <span>Buka Antrean Approval</span>
        </a>
    </div>
    @endif

    <!-- SaaS Financials & Core KPIs Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- MRR -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">MRR (Monthly Recurring)</span>
            <div class="mt-2 flex items-end justify-between gap-2">
                <span class="text-[22px] font-bold tabular-nums text-black dark:text-white tracking-tight">Rp {{ number_format($mrr, 0, ',', '.') }}</span>
                <span class="text-[11px] font-medium text-[#34C759] dark:text-[#30D158] tabular-nums">ARR {{ number_format($arr / 1000000, 1) }}M</span>
            </div>
            <p class="text-[11px] text-black/40 dark:text-white/40 mt-2 tabular-nums">{{ $totalPaidSubscribers }} Core Sub</p>
        </div>

        <!-- Total Businesses -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Tenant Bisnis</span>
            <div class="mt-2 flex items-end justify-between gap-2">
                <span class="text-[22px] font-bold tabular-nums text-black dark:text-white tracking-tight">{{ number_format($totalBusinesses, 0, ',', '.') }}</span>
                <span class="text-[11px] font-medium text-[#007AFF] dark:text-[#0A84FF] tabular-nums">{{ $totalPaidSubscribers }} Core / {{ $freeSubscribersCount }} Free</span>
            </div>
            <a href="{{ route('admin.businesses.index') }}" class="text-[12px] font-medium text-[#007AFF] dark:text-[#0A84FF] hover:underline mt-2 inline-flex items-center gap-1">Kelola <i data-lucide="arrow-right" class="w-3 h-3" stroke-width="1.5"></i></a>
        </div>

        <!-- AI Token Consumption -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Token AI Global</span>
            <div class="mt-2 flex items-end justify-between gap-2">
                <span class="text-[22px] font-bold tabular-nums text-black dark:text-white tracking-tight">{{ number_format($totalAiTokensConsumed, 0, ',', '.') }}</span>
                <span class="text-[11px] font-medium text-[#AF52DE] dark:text-[#BF5AF2]">Gemini 2.5 Flash</span>
            </div>
            <a href="{{ route('admin.ai-tokens.index') }}" class="text-[12px] font-medium text-[#AF52DE] dark:text-[#BF5AF2] hover:underline mt-2 inline-flex items-center gap-1">Analisis <i data-lucide="arrow-right" class="w-3 h-3" stroke-width="1.5"></i></a>
        </div>

        <!-- Total Users -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Pengguna</span>
            <div class="mt-2 flex items-end justify-between gap-2">
                <span class="text-[22px] font-bold tabular-nums text-black dark:text-white tracking-tight">{{ number_format($totalUsers, 0, ',', '.') }}</span>
                <span class="text-[11px] font-medium text-black/45 dark:text-white/45 tabular-nums">{{ number_format($totalCostingRuns, 0, ',', '.') }} Run HPP</span>
            </div>
            <a href="{{ route('admin.users.index') }}" class="text-[12px] font-medium text-[#007AFF] dark:text-[#0A84FF] hover:underline mt-2 inline-flex items-center gap-1">Detail <i data-lucide="arrow-right" class="w-3 h-3" stroke-width="1.5"></i></a>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4">
        <div class="flex items-center gap-2 mb-3">
            <i data-lucide="shield-alert" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]" stroke-width="1.5"></i>
            <h3 class="text-[13px] font-semibold text-black dark:text-white">Pusat Kendali Administrator SaaS</h3>
        </div>
        <p class="text-[12px] text-black/50 dark:text-white/50 mb-4">Pantau tenant UMKM, verifikasi pembayaran langganan, pantau AI token, atau konfigurasi kredensial sistem</p>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.businesses.index') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                <i data-lucide="building-2" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]" stroke-width="1.5"></i><span>Kelola Tenant</span>
            </a>
            <a href="{{ route('admin.users.export') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                <i data-lucide="download" class="w-4 h-4 text-[#30B0C7] dark:text-[#40C8E0]" stroke-width="1.5"></i><span>Export CSV User</span>
            </a>
            <a href="{{ route('admin.settings.index') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <i data-lucide="key" class="w-4 h-4" stroke-width="1.5"></i><span>Setting Google API</span>
            </a>
        </div>
    </div>

    <!-- Tables Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Recent Users (7 cols) -->
        <div class="lg:col-span-7 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
            <div class="px-4 sm:px-5 py-3.5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <h3 class="text-[13px] font-semibold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]" stroke-width="1.5"></i>
                    <span>Pengguna Terbaru Terdaftar</span>
                </h3>
                <a href="{{ route('admin.users.index') }}" class="text-[12px] font-medium text-[#007AFF] dark:text-[#0A84FF] hover:underline flex items-center gap-1">
                    <span>Lihat Semua</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5" stroke-width="1.5"></i>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10">
                            <th class="px-4 sm:px-5 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Nama / Email</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Bisnis Aktif</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40 text-right">Metode Login</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($recentUsers as $user)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                            <td class="px-4 sm:px-5 py-3">
                                <div class="font-medium text-black dark:text-white">{{ $user->name }}</div>
                                <div class="text-[11px] text-black/45 dark:text-white/45">{{ $user->email }}</div>
                            </td>
                            <td class="px-4 py-3 text-black/60 dark:text-white/60">{{ $user->activeBusiness?->name ?? 'Belum ada bisnis' }}</td>
                            <td class="px-4 py-3 text-right">
                                @if($user->google_id)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]"><span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span> Google SSO</span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55"><span class="w-1.5 h-1.5 rounded-full bg-black/40 dark:bg-white/40"></span> Email</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-[13px] text-black/45 dark:text-white/45">Belum ada pengguna terdaftar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Businesses (5 cols) -->
        <div class="lg:col-span-5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
            <div class="px-4 sm:px-5 py-3.5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <h3 class="text-[13px] font-semibold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="building-2" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]" stroke-width="1.5"></i>
                    <span>Tenant Bisnis Terbaru</span>
                </h3>
                <a href="{{ route('admin.businesses.index') }}" class="text-[12px] font-medium text-[#007AFF] dark:text-[#0A84FF] hover:underline flex items-center gap-1">
                    <span>Lihat Semua</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5" stroke-width="1.5"></i>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10">
                            <th class="px-4 sm:px-5 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Nama Bisnis</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Paket</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($recentBusinesses as $biz)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                            <td class="px-4 sm:px-5 py-3">
                                <div class="font-medium text-black dark:text-white">{{ $biz->name }}</div>
                                <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums">{{ $biz->users->count() }} Pengguna</div>
                            </td>
                            <td class="px-4 py-3">
                                @if($biz->subscription?->isCorePlan())
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]"><span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Core</span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55"><span class="w-1.5 h-1.5 rounded-full bg-black/40 dark:bg-white/40"></span> Free</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.businesses.show', $biz->id) }}" class="text-[13px] font-medium text-[#007AFF] dark:text-[#0A84FF] hover:underline inline-flex items-center gap-1">Detail <i data-lucide="arrow-right" class="w-3.5 h-3.5" stroke-width="1.5"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-[13px] text-black/45 dark:text-white/45">Belum ada bisnis terdaftar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection