@extends('layouts.admin', [
    'title' => 'Kelola Bisnis (Tenants) — Admin Console',
    'headerTitle' => 'Manajemen Tenant Bisnis UMKM',
    'headerSubtitle' => 'Kelola seluruh workspace bisnis yang beroperasi di platform Cooca UMKM'
])

@section('content')
<div class="space-y-6">

    <!-- KPI Summary Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="glass-card p-5 rounded-2xl border-blue-500/20">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Workspace</span>
                <i data-lucide="building-2" class="w-4 h-4 text-blue-400"></i>
            </div>
            <div class="text-2xl font-black text-white font-mono">{{ number_format($totalBusinesses, 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Tenant terdaftar di sistem</div>
        </div>

        <div class="glass-card p-5 rounded-2xl border-emerald-500/20">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Workspace Aktif</span>
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i>
            </div>
            <div class="text-2xl font-black text-emerald-400 font-mono">{{ number_format($activeBusinesses, 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Dapat bertransaksi normal</div>
        </div>

        <div class="glass-card p-5 rounded-2xl border-purple-500/20">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Langganan Core</span>
                <i data-lucide="shield-check" class="w-4 h-4 text-purple-400"></i>
            </div>
            <div class="text-2xl font-black text-purple-400 font-mono">{{ number_format($coreBusinesses, 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Pelanggan berbayar aktif</div>
        </div>

        <div class="glass-card p-5 rounded-2xl border-rose-500/20">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ditangguhkan</span>
                <i data-lucide="ban" class="w-4 h-4 text-rose-400"></i>
            </div>
            <div class="text-2xl font-black text-rose-400 font-mono">{{ number_format($suspendedBusinesses, 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Akses dibekukan sementara</div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="glass-card p-5 rounded-2xl border-slate-800">
        <form method="GET" action="{{ route('admin.businesses.index') }}" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
            <div class="relative flex-1 max-w-md">
                <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama bisnis, slug, email, telepon..."
                       class="w-full pl-10 pr-4 py-2 bg-slate-900 border border-slate-800 focus:border-indigo-500 rounded-xl text-xs text-white">
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <select name="plan" onchange="this.form.submit()"
                        class="px-3 py-2 bg-slate-900 border border-slate-800 focus:border-indigo-500 rounded-xl text-xs text-white">
                    <option value="">Semua Paket</option>
                    <option value="core" {{ request('plan') === 'core' ? 'selected' : '' }}>Cooca UMKM</option>
                    <option value="free" {{ request('plan') === 'free' ? 'selected' : '' }}>Free Plan</option>
                </select>

                <select name="status" onchange="this.form.submit()"
                        class="px-3 py-2 bg-slate-900 border border-slate-800 focus:border-indigo-500 rounded-xl text-xs text-white">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Ditangguhkan</option>
                </select>

                @if(request()->hasAny(['search', 'plan', 'status']))
                    <a href="{{ route('admin.businesses.index') }}" class="px-3 py-2 text-xs text-slate-400 hover:text-white">
                        Reset Filter
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Businesses Table Card -->
    <div class="glass-card rounded-2xl overflow-hidden border border-slate-800">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-800 bg-slate-900/60 font-semibold">
                        <th class="py-3.5 px-4">Nama Bisnis & Identitas</th>
                        <th class="py-3.5 px-4">Paket Langganan</th>
                        <th class="py-3.5 px-4">Pengguna / Tim</th>
                        <th class="py-3.5 px-4">Katalog & Resep</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($businesses as $biz)
                    <tr class="hover:bg-slate-900/40 transition-colors">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-white text-sm">
                                <a href="{{ route('admin.businesses.show', $biz->id) }}" class="hover:text-indigo-400 transition">
                                    {{ $biz->name }}
                                </a>
                            </div>
                            <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                slug: {{ $biz->slug }} &bull; {{ $biz->currency ?? 'IDR' }}
                            </div>
                            @if($biz->email || $biz->phone)
                            <div class="text-[10px] text-slate-500 mt-0.5">
                                {{ $biz->email ?? $biz->phone }}
                            </div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if($biz->subscription?->isCorePlan())
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 inline-flex items-center gap-1">
                                    <i data-lucide="shield-check" class="w-3 h-3 text-emerald-400"></i>
                                    <span>CORE ({{ strtoupper($biz->subscription->plan_code) }})</span>
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-medium bg-slate-800 text-slate-400 border border-slate-700">
                                    Free Plan
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-slate-300">
                            <div class="font-bold">{{ $biz->users->count() }} Pengguna</div>
                            <div class="text-[10px] text-slate-400">
                                {{ $biz->users->count() <= 1 ? 'Solo Owner' : 'Mode Tim' }}
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-slate-300 font-mono">
                            <div>{{ $biz->products_count }} Produk</div>
                            <div class="text-[10px] text-slate-500">{{ $biz->bom_headers_count }} Resep BOM</div>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($biz->is_active)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    Aktif
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                    Ditangguhkan
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.businesses.show', $biz->id) }}"
                                   class="px-3 py-1.5 rounded-lg bg-indigo-600/20 hover:bg-indigo-600/40 text-indigo-300 border border-indigo-500/30 text-xs font-semibold transition">
                                    Detail
                                </a>
                                <form method="POST" action="{{ route('admin.businesses.toggle-status', $biz->id) }}"
                                      onsubmit="return confirm('Apakah Anda yakin ingin mengubah status aktif bisnis {{ $biz->name }}?')">
                                    @csrf
                                    <button type="submit"
                                            class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition {{ $biz->is_active ? 'bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 border border-rose-500/30' : 'bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 border border-emerald-500/30' }}">
                                        {{ $biz->is_active ? 'Suspend' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500">
                            Tidak ada data bisnis yang sesuai dengan filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($businesses->hasPages())
        <div class="p-4 border-t border-slate-800 bg-slate-900/30">
            {{ $businesses->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
