@extends('layouts.admin', [
    'title' => 'Kelola Bisnis (Tenants) - Admin Console',
    'headerTitle' => 'Manajemen Tenant Bisnis UMKM',
    'headerSubtitle' => 'Kelola seluruh workspace bisnis yang beroperasi di platform Cooca',
])

@section('content')
    <div class="space-y-6">

        <!-- Bento KPI Summary Grid (Adaptive 2-Column Micro-Bento Mobile, 4-Column Desktop) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            
            <!-- Total Workspace -->
            <div class="rounded-[18px] sm:rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-3.5 sm:p-5 flex flex-col justify-between hover:shadow-md hover:-translate-y-0.5 transition-all">
                <div class="flex items-center justify-between gap-1.5">
                    <span class="text-[11px] sm:text-[12px] font-bold text-black/50 dark:text-white/50 truncate">Total Workspace</span>
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-[10px] bg-[#007AFF]/10 flex items-center justify-center text-[#007AFF] dark:text-[#0A84FF] shrink-0">
                        <i data-lucide="building-2" class="w-3.5 h-3.5 sm:w-4 sm:h-4" stroke-width="1.8"></i>
                    </div>
                </div>
                <div class="my-2 sm:my-3">
                    <div class="text-[20px] sm:text-[26px] font-extrabold tabular-nums tracking-tight text-black dark:text-white">
                        {{ number_format($totalBusinesses, 0, ',', '.') }}
                    </div>
                    <div class="text-[10px] sm:text-[11px] font-medium text-black/45 dark:text-white/45 mt-0.5 truncate">Tenant terdaftar di sistem</div>
                </div>
                <div class="text-[10px] sm:text-[11px] text-[#007AFF] dark:text-[#0A84FF] font-semibold pt-1 border-t border-black/[0.04] dark:border-white/[0.06] truncate">
                    Platform SaaS UMKM
                </div>
            </div>

            <!-- Workspace Aktif -->
            <div class="rounded-[18px] sm:rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-3.5 sm:p-5 flex flex-col justify-between hover:shadow-md hover:-translate-y-0.5 transition-all">
                <div class="flex items-center justify-between gap-1.5">
                    <span class="text-[11px] sm:text-[12px] font-bold text-black/50 dark:text-white/50 truncate">Workspace Aktif</span>
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-[10px] bg-[#34C759]/10 flex items-center justify-center text-[#34C759] dark:text-[#30D158] shrink-0">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5 sm:w-4 sm:h-4" stroke-width="1.8"></i>
                    </div>
                </div>
                <div class="my-2 sm:my-3">
                    <div class="text-[20px] sm:text-[26px] font-extrabold tabular-nums tracking-tight text-[#34C759] dark:text-[#30D158]">
                        {{ number_format($activeBusinesses, 0, ',', '.') }}
                    </div>
                    <div class="text-[10px] sm:text-[11px] font-medium text-black/45 dark:text-white/45 mt-0.5 truncate">Transaksi normal</div>
                </div>
                <div class="text-[10px] sm:text-[11px] text-[#34C759] dark:text-[#30D158] font-semibold pt-1 border-t border-black/[0.04] dark:border-white/[0.06] truncate">
                    Status operasional aktif
                </div>
            </div>

            <!-- Langganan Core -->
            <div class="rounded-[18px] sm:rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-3.5 sm:p-5 flex flex-col justify-between hover:shadow-md hover:-translate-y-0.5 transition-all">
                <div class="flex items-center justify-between gap-1.5">
                    <span class="text-[11px] sm:text-[12px] font-bold text-black/50 dark:text-white/50 truncate">Langganan Core</span>
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-[10px] bg-[#AF52DE]/10 flex items-center justify-center text-[#AF52DE] dark:text-[#BF5AF2] shrink-0">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 sm:w-4 sm:h-4" stroke-width="1.8"></i>
                    </div>
                </div>
                <div class="my-2 sm:my-3">
                    <div class="text-[20px] sm:text-[26px] font-extrabold tabular-nums tracking-tight text-[#AF52DE] dark:text-[#BF5AF2]">
                        {{ number_format($coreBusinesses, 0, ',', '.') }}
                    </div>
                    <div class="text-[10px] sm:text-[11px] font-medium text-black/45 dark:text-white/45 mt-0.5 truncate">Pelanggan aktif</div>
                </div>
                <div class="text-[10px] sm:text-[11px] text-[#AF52DE] dark:text-[#BF5AF2] font-semibold pt-1 border-t border-black/[0.04] dark:border-white/[0.06] truncate">
                    Paket berbayar aktif
                </div>
            </div>

            <!-- Ditangguhkan -->
            <div class="rounded-[18px] sm:rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-3.5 sm:p-5 flex flex-col justify-between hover:shadow-md hover:-translate-y-0.5 transition-all">
                <div class="flex items-center justify-between gap-1.5">
                    <span class="text-[11px] sm:text-[12px] font-bold text-black/50 dark:text-white/50 truncate">Ditangguhkan</span>
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-[10px] bg-[#FF3B30]/10 flex items-center justify-center text-[#FF3B30] dark:text-[#FF453A] shrink-0">
                        <i data-lucide="ban" class="w-3.5 h-3.5 sm:w-4 sm:h-4" stroke-width="1.8"></i>
                    </div>
                </div>
                <div class="my-2 sm:my-3">
                    <div class="text-[20px] sm:text-[26px] font-extrabold tabular-nums tracking-tight text-[#FF3B30] dark:text-[#FF453A]">
                        {{ number_format($suspendedBusinesses, 0, ',', '.') }}
                    </div>
                    <div class="text-[10px] sm:text-[11px] font-medium text-black/45 dark:text-white/45 mt-0.5 truncate">Dibekukan sementara</div>
                </div>
                <div class="text-[10px] sm:text-[11px] text-[#FF3B30] dark:text-[#FF453A] font-semibold pt-1 border-t border-black/[0.04] dark:border-white/[0.06] truncate">
                    Perlu verifikasi
                </div>
            </div>

        </div>

        <!-- Filter & Search Bento Toolbar -->
        <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-4 shadow-sm">
            <form method="GET" action="{{ route('admin.businesses.index') }}"
                class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                <div class="relative flex-1 max-w-md">
                    <i data-lucide="search"
                        class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3.5 top-1/2 -translate-y-1/2"
                        stroke-width="1.5"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nama bisnis, slug, email, telepon..."
                        class="w-full h-10 pl-10 pr-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.04] dark:border-white/[0.06] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <select name="plan" onchange="this.form.submit()"
                        class="h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.04] dark:border-white/[0.06] rounded-[12px] text-[13px] font-medium text-black/75 dark:text-white/75 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        <option value="">Semua Paket</option>
                        <option value="core" {{ request('plan') === 'core' ? 'selected' : '' }}>Cooca (Core)</option>
                        <option value="free" {{ request('plan') === 'free' ? 'selected' : '' }}>Free Plan</option>
                    </select>
                    <select name="status" onchange="this.form.submit()"
                        class="h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.04] dark:border-white/[0.06] rounded-[12px] text-[13px] font-medium text-black/75 dark:text-white/75 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Ditangguhkan</option>
                    </select>
                    @if (request()->hasAny(['search', 'plan', 'status']))
                        <a href="{{ route('admin.businesses.index') }}"
                            class="h-10 px-3 rounded-[12px] text-[12px] font-bold text-[#FF3B30] hover:bg-[#FF3B30]/10 transition-colors inline-flex items-center gap-1.5">
                            <i data-lucide="x" class="w-3.5 h-3.5" stroke-width="2"></i>
                            <span>Reset Filter</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Bento Table Container -->
        <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.01] dark:bg-white/[0.02]">
                            <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Bisnis &amp; Identitas</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Paket Langganan</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Pengguna &amp; Tim</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Katalog &amp; Resep</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Status</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($businesses as $biz)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                <td class="px-5 py-4">
                                    <div class="font-bold text-black dark:text-white text-[14px]">
                                        <a href="{{ route('admin.businesses.show', $biz->id) }}"
                                            class="hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition-colors">{{ $biz->name }}</a>
                                    </div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45 font-mono mt-0.5">
                                        slug: {{ $biz->slug }} · {{ $biz->currency ?? 'IDR' }}
                                    </div>
                                    @if ($biz->email || $biz->phone)
                                        <div class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">
                                            {{ $biz->email ?? $biz->phone }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    @if ($biz->subscription?->isCorePlan())
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                            <i data-lucide="shield-check" class="w-3.5 h-3.5" stroke-width="2"></i>
                                            <span>CORE ({{ strtoupper($biz->subscription->plan_code) }})</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55">
                                            <i data-lucide="gift" class="w-3.5 h-3.5" stroke-width="1.5"></i>
                                            <span>Free Plan</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-black/75 dark:text-white/75">
                                    <div class="font-bold">{{ $biz->users->count() }} Pengguna</div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45">
                                        {{ $biz->users->count() <= 1 ? 'Solo Owner' : 'Mode Tim Delegasi' }}
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-black/75 dark:text-white/75">
                                    <div class="tabular-nums font-bold">{{ $biz->products_count }} Produk</div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums">
                                        {{ $biz->bom_headers_count }} Resep BOM
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    @if ($biz->is_active)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/15 text-[#C41E17] dark:text-[#FF453A]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span> Ditangguhkan
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.businesses.show', $biz->id) }}"
                                            class="h-8 px-3 rounded-[10px] text-[12px] font-bold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-95 transition-all inline-flex items-center gap-1">
                                            <span>Detail</span>
                                            <i data-lucide="arrow-right" class="w-3 h-3" stroke-width="2"></i>
                                        </a>
                                        <form method="POST"
                                            action="{{ route('admin.businesses.toggle-status', $biz->id) }}"
                                            onsubmit="return AppAlert.confirmSubmit(event, this, 'Apakah Anda yakin ingin mengubah status aktif bisnis {{ addslashes($biz->name) }}?', 'Ubah Status Bisnis?', 'warning')">
                                            @csrf
                                            <button type="submit"
                                                class="h-8 px-2.5 rounded-[10px] text-[12px] font-bold transition-all inline-flex items-center gap-1 {{ $biz->is_active ? 'text-[#FF3B30] hover:bg-[#FF3B30]/10' : 'text-[#34C759] hover:bg-[#34C759]/10' }} active:scale-95">
                                                {{ $biz->is_active ? 'Suspend' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center">
                                    <div class="w-12 h-12 mx-auto rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center text-black/30 dark:text-white/30 mb-2.5">
                                        <i data-lucide="building" class="w-6 h-6" stroke-width="1.5"></i>
                                    </div>
                                    <p class="text-[15px] font-bold text-black dark:text-white">Tidak ada data bisnis ditemukan</p>
                                    <p class="text-[12px] text-black/50 dark:text-white/50 mt-1">Coba sesuaikan kata kunci pencarian atau reset filter.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($businesses->hasPages())
                <div class="px-5 py-3.5 border-t border-black/[0.06] dark:border-white/[0.08] bg-black/[0.01] dark:bg-white/[0.02]">
                    {{ $businesses->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
