@extends('layouts.admin', [
    'title' => 'Dashboard — Admin Console',
    'headerTitle' => 'Statistik & Ringkasan Platform SaaS',
    'headerSubtitle' => 'Pantau pertumbuhan tenant UMKM, pendapatan MRR, langganan Core, dan konsumsi token AI'
])

@section('content')
<div class="space-y-8">

    <!-- Pending Payment Alert Banner -->
    @if($pendingSubscriptionsCount > 0)
    <div class="p-5 rounded-2xl bg-gradient-to-r from-amber-500/20 via-slate-900 to-amber-950/20 border border-amber-500/40 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-lg shadow-amber-500/10">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500/20 border border-amber-500/40 flex items-center justify-center text-amber-400">
                <i data-lucide="bell-ring" class="w-5 h-5 animate-bounce"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <span>Verifikasi Pembayaran Diperlukan</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-500 text-slate-950">
                        {{ $pendingSubscriptionsCount }} Menunggu
                    </span>
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Ada bukti transfer pelanggan yang baru saja diunggah dan membutuhkan konfirmasi aktivasi paket Core.</p>
            </div>
        </div>
        <a href="{{ route('admin.subscriptions.index') }}" 
           class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black shadow-md flex items-center gap-1.5 transition-all whitespace-nowrap">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            <span>Buka Antrean Approval</span>
        </a>
    </div>
    @endif

    <!-- SaaS Financials & Core KPIs Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- MRR Card -->
        <div class="glass-card p-6 rounded-2xl border-emerald-500/20 relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-emerald-500/10 rounded-full blur-xl group-hover:scale-125 transition-transform"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">MRR (Monthly Recurring)</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl lg:text-3xl font-extrabold text-white font-mono tracking-tight">
                Rp {{ number_format($mrr, 0, ',', '.') }}
            </div>
            <div class="mt-2 text-xs text-slate-400 flex items-center justify-between">
                <span>ARR: <strong class="text-emerald-400 font-mono">Rp {{ number_format($arr / 1000000, 1) }}M</strong></span>
                <span class="text-[10px] text-slate-500">{{ $totalPaidSubscribers }} Core Sub</span>
            </div>
        </div>

        <!-- Total Businesses / Tenants -->
        <div class="glass-card p-6 rounded-2xl border-blue-500/20 relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-blue-500/10 rounded-full blur-xl group-hover:scale-125 transition-transform"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Tenant Bisnis</span>
                <div class="w-9 h-9 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400">
                    <i data-lucide="building-2" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl lg:text-3xl font-extrabold text-white font-mono tracking-tight">
                {{ number_format($totalBusinesses, 0, ',', '.') }}
            </div>
            <div class="mt-2 text-xs text-slate-400 flex items-center justify-between">
                <span><strong class="text-blue-400">{{ $totalPaidSubscribers }}</strong> Core / <strong class="text-slate-300">{{ $freeSubscribersCount }}</strong> Free</span>
                <a href="{{ route('admin.businesses.index') }}" class="text-[10px] text-blue-400 hover:underline">Kelola &rarr;</a>
            </div>
        </div>

        <!-- AI Token Consumption Card -->
        <div class="glass-card p-6 rounded-2xl border-purple-500/20 relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-purple-500/10 rounded-full blur-xl group-hover:scale-125 transition-transform"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Token AI Global</span>
                <div class="w-9 h-9 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl lg:text-3xl font-extrabold text-white font-mono tracking-tight">
                {{ number_format($totalAiTokensConsumed, 0, ',', '.') }}
            </div>
            <div class="mt-2 text-xs text-slate-400 flex items-center justify-between">
                <span>Model: Gemini 2.5 Flash</span>
                <a href="{{ route('admin.ai-tokens.index') }}" class="text-[10px] text-purple-400 hover:underline">Analisis &rarr;</a>
            </div>
        </div>

        <!-- Total Users Card -->
        <div class="glass-card p-6 rounded-2xl border-indigo-500/20 relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-indigo-500/10 rounded-full blur-xl group-hover:scale-125 transition-transform"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Pengguna</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl lg:text-3xl font-extrabold text-white font-mono tracking-tight">
                {{ number_format($totalUsers, 0, ',', '.') }}
            </div>
            <div class="mt-2 text-xs text-slate-400 flex items-center justify-between">
                <span>{{ number_format($totalCostingRuns, 0, ',', '.') }} Run HPP</span>
                <a href="{{ route('admin.users.index') }}" class="text-[10px] text-indigo-400 hover:underline">Detail &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Quick Actions Banner -->
    <div class="glass-card p-6 rounded-2xl border-slate-800 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 bg-gradient-to-r from-indigo-950/40 via-slate-900 to-purple-950/40">
        <div>
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i data-lucide="shield-alert" class="w-4 h-4 text-indigo-400"></i>
                <span>Pusat Kendali Administrator SaaS</span>
            </h3>
            <p class="text-xs text-slate-400 mt-1">Pantau tenant UMKM, verifikasi pembayaran langganan, pantau AI token, atau konfigurasi kredensial sistem</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.businesses.index') }}" 
               class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold flex items-center gap-1.5 transition-all">
                <i data-lucide="building-2" class="w-4 h-4 text-blue-400"></i>
                <span>Kelola Tenant</span>
            </a>
            <a href="{{ route('admin.users.export') }}" 
               class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold flex items-center gap-1.5 transition-all">
                <i data-lucide="download" class="w-4 h-4 text-cyan-400"></i>
                <span>Export CSV User</span>
            </a>
            <a href="{{ route('admin.settings.index') }}" 
               class="px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-xs font-semibold shadow-lg shadow-indigo-500/25 flex items-center gap-1.5 transition-all">
                <i data-lucide="key" class="w-4 h-4"></i>
                <span>Setting Google API</span>
            </a>
        </div>
    </div>

    <!-- Tables Grid (Recent Users & Businesses) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Recent Users (7 cols) -->
        <div class="glass-card rounded-2xl overflow-hidden lg:col-span-7">
            <div class="p-5 border-b border-slate-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-4 h-4 text-indigo-400"></i>
                    <span>Pengguna Terbaru Terdaftar</span>
                </h3>
                <a href="{{ route('admin.users.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold flex items-center gap-1">
                    <span>Lihat Semua</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-800 bg-slate-900/50">
                            <th class="py-3 px-4 font-semibold">Nama / Email</th>
                            <th class="py-3 px-4 font-semibold">Bisnis Aktif</th>
                            <th class="py-3 px-4 font-semibold text-right">Metode Login</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($recentUsers as $user)
                        <tr class="hover:bg-slate-900/40 transition-colors">
                            <td class="py-3 px-4">
                                <div class="font-bold text-white">{{ $user->name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $user->email }}</div>
                            </td>
                            <td class="py-3 px-4 text-slate-300">
                                {{ $user->activeBusiness?->name ?? 'Belum ada bisnis' }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if($user->google_id)
                                    <span class="px-2 py-0.5 rounded-full bg-red-500/10 text-red-400 text-[10px] font-bold">
                                        Google SSO
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-400 text-[10px] font-bold">
                                        Email
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-6 text-center text-slate-500">Belum ada pengguna terdaftar.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Businesses (5 cols) -->
        <div class="glass-card rounded-2xl overflow-hidden lg:col-span-5">
            <div class="p-5 border-b border-slate-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i data-lucide="building-2" class="w-4 h-4 text-blue-400"></i>
                    <span>Tenant Bisnis Terbaru</span>
                </h3>
                <a href="{{ route('admin.businesses.index') }}" class="text-xs text-blue-400 hover:text-blue-300 font-semibold flex items-center gap-1">
                    <span>Lihat Semua</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-800 bg-slate-900/50">
                            <th class="py-3 px-4 font-semibold">Nama Bisnis</th>
                            <th class="py-3 px-4 font-semibold">Paket</th>
                            <th class="py-3 px-4 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($recentBusinesses as $biz)
                        <tr class="hover:bg-slate-900/40 transition-colors">
                            <td class="py-3 px-4">
                                <div class="font-bold text-white">{{ $biz->name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $biz->users->count() }} Pengguna</div>
                            </td>
                            <td class="py-3 px-4">
                                @if($biz->subscription?->isCorePlan())
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                        Core
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-800 text-slate-400 border border-slate-700">
                                        Free
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('admin.businesses.show', $biz->id) }}" class="text-indigo-400 hover:text-indigo-300 font-semibold text-xs">
                                    Detail &rarr;
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-6 text-center text-slate-500">Belum ada bisnis terdaftar.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
