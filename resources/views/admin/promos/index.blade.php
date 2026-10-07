@extends('layouts.admin', [
    'title' => 'Manajemen Kode Promo & Voucher - Admin Console',
    'headerTitle' => 'Kode Promo & Voucher',
    'headerSubtitle' => 'Kelola skema insentif subscription, kode diskon kas, dan pencatatan audit beban promosi platform',
])

@section('content')
    <div class="space-y-6 max-w-[1250px] w-full min-w-0 mx-auto pb-28 lg:pb-10" x-data="{
        tab: '{{ $tab }}',
        createOpen: false,
        editOpen: false,
        editData: {
            id: '',
            code: '',
            name: '',
            description: '',
            discount_type: 'percentage',
            discount_value: 10,
            max_discount_amount: '',
            min_order_amount: 0,
            usage_limit: '',
            usage_per_business_limit: 1,
            valid_from: '',
            valid_until: '',
            is_active: true,
            applicable_tiers: [],
            applicable_cycles: []
        },
        openEdit(promo) {
            this.editData = {
                id: promo.id,
                code: promo.code,
                name: promo.name,
                description: promo.description || '',
                discount_type: promo.discount_type,
                discount_value: promo.discount_value,
                max_discount_amount: promo.max_discount_amount || '',
                min_order_amount: promo.min_order_amount || 0,
                usage_limit: promo.usage_limit || '',
                usage_per_business_limit: promo.usage_per_business_limit || 1,
                valid_from: promo.valid_from ? promo.valid_from.substring(0, 10) : '',
                valid_until: promo.valid_until ? promo.valid_until.substring(0, 10) : '',
                is_active: Boolean(promo.is_active),
                applicable_tiers: promo.applicable_tiers || [],
                applicable_cycles: promo.applicable_cycles || []
            };
            this.editOpen = true;
        }
    }">

        <!-- Page Header Bento -->
        <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-xl p-5 sm:p-6 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-[16px] bg-[#34C759]/10 flex items-center justify-center text-[#34C759] dark:text-[#30D158] shrink-0">
                    <i data-lucide="ticket-percent" class="w-6 h-6" stroke-width="1.8"></i>
                </div>
                <div>
                    <h1 class="text-[19px] sm:text-[20px] font-bold text-black dark:text-white tracking-tight">Manajemen Promo &amp; Diskon Subscription</h1>
                    <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Strategi konversi pelanggan baru, retensi tahunan, dan pencatatan subsidi pemasaran terintegrasi.</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <form action="{{ route('admin.promos.seed-defaults') }}" method="POST" onsubmit="return confirm('Muat skema promo subscription unggulan ke katalog?')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-[12px] text-[13px] font-semibold bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.10] text-black/75 dark:text-white/80 border border-black/[0.06] dark:border-white/[0.08] transition-all">
                        <i data-lucide="sparkles" class="w-4 h-4 text-[#AF52DE]" stroke-width="1.8"></i>
                        <span>Sinkronkan 6 Skema Unggulan</span>
                    </button>
                </form>
                <button type="button" @click="createOpen = true" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-[12px] text-[13px] font-semibold bg-[#007AFF] hover:bg-[#0071E3] text-white shadow-sm shadow-[#007AFF]/25 transition-all">
                    <i data-lucide="plus" class="w-4 h-4" stroke-width="2"></i>
                    <span>Terbitkan Promo Baru</span>
                </button>
            </div>
        </div>

        <!-- Bento Metrics Cockpit (Finansial & Efektivitas Promosi) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Metric 1: Total Promo Aktif -->
            <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[12px] font-medium text-black/55 dark:text-white/55">Promo Aktif</span>
                    <span class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center">
                        <i data-lucide="check-circle-2" class="w-4 h-4" stroke-width="1.8"></i>
                    </span>
                </div>
                <div class="text-[26px] font-extrabold tracking-tight text-black dark:text-white tabular-nums">
                    {{ number_format($totalActivePromos, 0, ',', '.') }}
                </div>
                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Siap digunakan di checkout tenant</p>
            </div>

            <!-- Metric 2: Total Pemakaian Voucher -->
            <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[12px] font-medium text-black/55 dark:text-white/55">Total Pemakaian</span>
                    <span class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center">
                        <i data-lucide="shopping-bag" class="w-4 h-4" stroke-width="1.8"></i>
                    </span>
                </div>
                <div class="text-[26px] font-extrabold tracking-tight text-black dark:text-white tabular-nums">
                    {{ number_format($totalUsagesCount, 0, ',', '.') }}
                    <span class="text-[13px] font-normal text-black/45 dark:text-white/45">klaim</span>
                </div>
                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Total order berhasil dengan promo</p>
            </div>

            <!-- Metric 3: Total Beban Subsidi / Diskon -->
            <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[12px] font-medium text-black/55 dark:text-white/55">Beban Subsidi Promosi</span>
                    <span class="w-8 h-8 rounded-[10px] bg-[#FF9500]/10 text-[#FF9500] dark:text-[#FF9F0A] flex items-center justify-center">
                        <i data-lucide="coins" class="w-4 h-4" stroke-width="1.8"></i>
                    </span>
                </div>
                <div class="text-[24px] font-extrabold tracking-tight text-[#FF9500] dark:text-[#FF9F0A] tabular-nums">
                    Rp {{ number_format($totalDiscountGiven, 0, ',', '.') }}
                </div>
                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Akumulasi penghematan tenant (Marketing Cost)</p>
            </div>

            <!-- Metric 4: Promo Terpopuler -->
            <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[12px] font-medium text-black/55 dark:text-white/55">Promo Terfavorit</span>
                    <span class="w-8 h-8 rounded-[10px] bg-[#AF52DE]/10 text-[#AF52DE] dark:text-[#BF5AF2] flex items-center justify-center">
                        <i data-lucide="flame" class="w-4 h-4" stroke-width="1.8"></i>
                    </span>
                </div>
                @if ($topPromo)
                    <div class="text-[19px] font-extrabold font-mono tracking-tight text-black dark:text-white truncate">
                        {{ $topPromo->code }}
                    </div>
                    <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">
                        Digunakan <span class="font-bold text-black dark:text-white tabular-nums">{{ $topPromo->used_count }}x</span> ({{ $topPromo->formatted_discount }})
                    </p>
                @else
                    <div class="text-[15px] font-bold text-black/40 dark:text-white/40">Belum Ada Data</div>
                    <p class="text-[11px] text-black/35 dark:text-white/35 mt-1">Data terisi otomatis saat promo dipakai</p>
                @endif
            </div>
        </div>

        <!-- Segmented Tab Navigation & Filter Bento -->
        <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-xl p-4 sm:p-5 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <!-- Apple Pill Switcher -->
                <div class="inline-flex p-1 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.04] dark:border-white/[0.06] self-start">
                    <a href="{{ route('admin.promos.index', ['tab' => 'promos', 'status' => $status, 'search' => $search]) }}"
                        class="px-4 py-1.5 rounded-[10px] text-[13px] font-semibold transition-all {{ $tab === 'promos' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        Katalog Kode Promo ({{ $promos->total() }})
                    </a>
                    <a href="{{ route('admin.promos.index', ['tab' => 'usages', 'search' => $search]) }}"
                        class="px-4 py-1.5 rounded-[10px] text-[13px] font-semibold transition-all {{ $tab === 'usages' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        Audit Keuangan &amp; Log Pemakaian ({{ $usages->total() }})
                    </a>
                </div>

                <!-- Search & Status Filter Form -->
                <form action="{{ route('admin.promos.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    
                    @if ($tab === 'promos')
                        <select name="status" onchange="this.form.submit()" class="h-9 px-3 rounded-[10px] text-[12.5px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status</option>
                            <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    @endif

                    <div class="relative min-w-[200px] sm:min-w-[240px]">
                        <input type="text" name="search" value="{{ $search }}" placeholder="{{ $tab === 'promos' ? 'Cari kode, nama, deskripsi...' : 'Cari invoice, tenant, promo...' }}"
                            class="w-full h-9 pl-8 pr-3 rounded-[10px] text-[12.5px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white placeholder-black/40 dark:placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        <i data-lucide="search" class="w-3.5 h-3.5 absolute left-2.5 top-3 text-black/40 dark:text-white/40" stroke-width="1.8"></i>
                    </div>

                    <button type="submit" class="h-9 px-3 rounded-[10px] text-[12.5px] font-semibold bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 text-black dark:text-white transition-all">
                        Cari
                    </button>
                    @if ($search || $status !== 'all')
                        <a href="{{ route('admin.promos.index', ['tab' => $tab]) }}" class="h-9 px-2.5 rounded-[10px] text-[12px] font-medium text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white flex items-center justify-center">
                            Reset
                        </a>
                    @endif
                </form>
            </div>
        </div>

        @if ($tab === 'promos')
            <!-- TAB 1: KATALOG KODE PROMO -->
            <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[13px]">
                        <thead>
                            <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02] text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">
                                <th class="py-3.5 px-4 sm:px-6">Kode &amp; Nama Promo</th>
                                <th class="py-3.5 px-4">Nilai Diskon</th>
                                <th class="py-3.5 px-4">Paket &amp; Siklus</th>
                                <th class="py-3.5 px-4">Pemakaian / Kuota</th>
                                <th class="py-3.5 px-4">Masa Berlaku</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                                <th class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                            @forelse ($promos as $promo)
                                <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="py-4 px-4 sm:px-6">
                                        <div class="flex items-center gap-2.5">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-mono font-bold text-[12px] tracking-wide border border-[#007AFF]/20">
                                                <i data-lucide="tag" class="w-3 h-3" stroke-width="2"></i>
                                                {{ $promo->code }}
                                            </span>
                                            <div class="min-w-0">
                                                <div class="font-bold text-black dark:text-white truncate">{{ $promo->name }}</div>
                                                <div class="text-[11px] text-black/45 dark:text-white/45 line-clamp-1">{{ $promo->description ?? 'Tidak ada deskripsi' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="font-extrabold text-[#34C759] dark:text-[#30D158]">
                                            {{ $promo->formatted_discount }}
                                        </div>
                                        @if ($promo->discount_type === 'percentage' && $promo->max_discount_amount)
                                            <div class="text-[11px] text-black/45 dark:text-white/45">
                                                Maks. Rp {{ number_format($promo->max_discount_amount, 0, ',', '.') }}
                                            </div>
                                        @endif
                                        @if ($promo->min_order_amount > 0)
                                            <div class="text-[10px] text-black/40 dark:text-white/40">
                                                Min. Order Rp {{ number_format($promo->min_order_amount, 0, ',', '.') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex flex-wrap gap-1">
                                            @if (empty($promo->applicable_tiers))
                                                <span class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-semibold bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70">Semua Tier</span>
                                            @else
                                                @foreach ($promo->applicable_tiers as $tier)
                                                    <span class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-semibold uppercase bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]">{{ $tier }}</span>
                                                @endforeach
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-black/45 dark:text-white/45 mt-1">
                                            {{ empty($promo->applicable_cycles) ? 'Semua Siklus' : implode(', ', array_map('ucfirst', $promo->applicable_cycles)) }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold tabular-nums text-black dark:text-white">{{ $promo->used_count }}</span>
                                            <span class="text-black/40 dark:text-white/40">/</span>
                                            <span class="text-black/60 dark:text-white/60 tabular-nums">{{ $promo->usage_limit ? number_format($promo->usage_limit, 0, ',', '.') : '∞' }}</span>
                                        </div>
                                        <div class="text-[10.5px] text-black/40 dark:text-white/40">
                                            Batas: {{ $promo->usage_per_business_limit }}x/bisnis
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="text-[12px] text-black/75 dark:text-white/75">
                                            @if ($promo->valid_until)
                                                s/d {{ $promo->valid_until->translatedFormat('d M Y') }}
                                            @else
                                                <span class="text-[#34C759] font-medium">Permanen</span>
                                            @endif
                                        </div>
                                        @if ($promo->valid_from)
                                            <div class="text-[10.5px] text-black/40 dark:text-white/40">
                                                Mulai {{ $promo->valid_from->translatedFormat('d M Y') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <form action="{{ route('admin.promos.toggle', $promo) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold transition-all {{ $promo->is_active ? 'bg-[#34C759]/15 text-[#34C759] dark:text-[#30D158] hover:bg-[#34C759]/25' : 'bg-black/10 dark:bg-white/10 text-black/50 dark:text-white/50 hover:bg-black/15' }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $promo->is_active ? 'bg-[#34C759]' : 'bg-black/40 dark:bg-white/40' }}"></span>
                                                {{ $promo->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="py-4 px-4 sm:px-6 text-right">
                                        <div class="inline-flex items-center gap-1">
                                            <button type="button" @click="openEdit({{ \Illuminate\Support\Js::from($promo) }})" class="p-1.5 rounded-[8px] hover:bg-black/5 dark:hover:bg-white/10 text-[#007AFF] transition-colors" title="Edit Promo">
                                                <i data-lucide="edit-3" class="w-4 h-4" stroke-width="1.8"></i>
                                            </button>
                                            <form action="{{ route('admin.promos.destroy', $promo) }}" method="POST" onsubmit="return confirm('Hapus permanen kode promo {{ $promo->code }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded-[8px] hover:bg-red-500/10 text-red-500 transition-colors" title="Hapus Promo">
                                                    <i data-lucide="trash-2" class="w-4 h-4" stroke-width="1.8"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-black/45 dark:text-white/45">
                                        <div class="w-12 h-12 rounded-[16px] bg-black/5 dark:bg-white/5 mx-auto flex items-center justify-center text-black/30 dark:text-white/30 mb-3">
                                            <i data-lucide="ticket" class="w-6 h-6" stroke-width="1.5"></i>
                                        </div>
                                        <div class="font-bold text-[15px] text-black dark:text-white">Belum Ada Kode Promo</div>
                                        <p class="text-[12px] mt-1">Gunakan tombol "Sinkronkan 6 Skema Unggulan" atau terbitkan kode promo manual pertama Anda.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($promos->hasPages())
                    <div class="p-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                        {{ $promos->links() }}
                    </div>
                @endif
            </div>

        @else
            <!-- TAB 2: AUDIT KEUANGAN & LOG PENGGUNAAN PROMO -->
            <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-xl shadow-sm overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                    <div>
                        <h2 class="text-[16px] font-bold text-black dark:text-white">Jurnal Audit Beban Promosi Langganan</h2>
                        <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Setiap voucher yang diklaim dicatat sebagai subsidi pemasaran dengan referensi pesanan dan bisnis.</p>
                    </div>
                    <span class="text-[12px] font-semibold text-black/60 dark:text-white/60 tabular-nums">
                        Total Catatan: {{ $usages->total() }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[13px]">
                        <thead>
                            <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02] text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">
                                <th class="py-3.5 px-4 sm:px-6">No. Pesanan / Invoice</th>
                                <th class="py-3.5 px-4">Bisnis (Tenant)</th>
                                <th class="py-3.5 px-4">Kode Promo</th>
                                <th class="py-3.5 px-4">Nilai Order Awal</th>
                                <th class="py-3.5 px-4">Diskon Diberikan</th>
                                <th class="py-3.5 px-4">Net Dibayar</th>
                                <th class="py-3.5 px-4 sm:px-6">Waktu Klaim</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                            @forelse ($usages as $usage)
                                <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="py-4 px-4 sm:px-6 font-mono text-[12px] font-bold text-black dark:text-white">
                                        {{ $usage->order_number ?? ($usage->payment?->order_number ?? '-') }}
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="font-bold text-black dark:text-white">{{ $usage->business?->name ?? 'Bisnis Terhapus' }}</div>
                                        <div class="text-[11px] text-black/45 dark:text-white/45">User: {{ $usage->user?->name ?? 'Sistem' }}</div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-[6px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-mono font-bold text-[11px]">
                                            {{ $usage->promo?->code ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 font-mono tabular-nums text-black/75 dark:text-white/75">
                                        Rp {{ number_format($usage->original_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-4 font-mono tabular-nums font-bold text-[#FF9500] dark:text-[#FF9F0A]">
                                        - Rp {{ number_format($usage->discount_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-4 font-mono tabular-nums font-extrabold text-[#34C759] dark:text-[#30D158]">
                                        Rp {{ number_format($usage->final_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-4 sm:px-6 text-[12px] text-black/60 dark:text-white/60">
                                        {{ $usage->created_at->translatedFormat('d M Y, H:i') }}
                                        <div class="text-[10.5px] text-black/40 dark:text-white/40">{{ $usage->created_at->diffForHumans() }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-black/45 dark:text-white/45">
                                        <div class="w-12 h-12 rounded-[16px] bg-black/5 dark:bg-white/5 mx-auto flex items-center justify-center text-black/30 dark:text-white/30 mb-3">
                                            <i data-lucide="receipt" class="w-6 h-6" stroke-width="1.5"></i>
                                        </div>
                                        <div class="font-bold text-[15px] text-black dark:text-white">Belum Ada Transaksi dengan Promo</div>
                                        <p class="text-[12px] mt-1">Audit keuangan subsidi promosi akan tercatat otomatis setiap tenant menyelesaikan checkout dengan voucher.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($usages->hasPages())
                    <div class="p-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                        {{ $usages->links() }}
                    </div>
                @endif
            </div>
        @endif

        <!-- MODAL SHEET 1: TERBITKAN PROMO BARU -->
        <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" @click="createOpen = false"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl p-6 sm:p-7">
                    <div class="flex items-center justify-between pb-4 border-b border-black/[0.06] dark:border-white/[0.08]">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 flex items-center justify-center text-[#007AFF] shrink-0">
                                <i data-lucide="ticket-percent" class="w-5 h-5" stroke-width="1.8"></i>
                            </div>
                            <div>
                                <h3 class="text-[17px] font-bold text-black dark:text-white">Terbitkan Kode Promo Baru</h3>
                                <p class="text-[11.5px] text-black/50 dark:text-white/50">Tentukan nilai potongan, batasan kuota, dan target tier paket</p>
                            </div>
                        </div>
                        <button type="button" @click="createOpen = false" class="p-1.5 rounded-[10px] text-black/40 dark:text-white/40 hover:bg-black/5 dark:hover:bg-white/10">
                            <i data-lucide="x" class="w-5 h-5" stroke-width="2"></i>
                        </button>
                    </div>

                    <form action="{{ route('admin.promos.store') }}" method="POST" class="mt-5 space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">Kode Voucher <span class="text-red-500">*</span></label>
                                <input type="text" name="code" required placeholder="Contoh: HEMAT30" class="w-full h-10 px-3.5 rounded-[12px] uppercase font-mono font-bold text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                            <div>
                                <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">Nama Promo <span class="text-red-500">*</span></label>
                                <input type="text" name="name" required placeholder="Diskon Awal Tahun 30%" class="w-full h-10 px-3.5 rounded-[12px] text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">Deskripsi &amp; Syarat Ketentuan</label>
                            <textarea name="description" rows="2" placeholder="Potongan diskon untuk semua paket langganan tahunan..." class="w-full p-3 rounded-[12px] text-[12.5px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]"></textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/[0.04] dark:border-white/[0.06]">
                            <div>
                                <label class="block text-[11.5px] font-bold text-black/70 dark:text-white/70 mb-1">Jenis Diskon</label>
                                <select name="discount_type" class="w-full h-9 px-3 rounded-[10px] text-[12.5px] bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                    <option value="percentage">Persentase (%)</option>
                                    <option value="fixed">Nominal Flat (Rp)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11.5px] font-bold text-black/70 dark:text-white/70 mb-1">Nilai Potongan <span class="text-red-500">*</span></label>
                                <input type="number" step="any" name="discount_value" required placeholder="30 atau 100000" class="w-full h-9 px-3 rounded-[10px] text-[12.5px] bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                            <div>
                                <label class="block text-[11.5px] font-bold text-black/70 dark:text-white/70 mb-1">Maks. Diskon (Rp)</label>
                                <input type="number" step="any" name="max_discount_amount" placeholder="Kosongkan jika ∞" class="w-full h-9 px-3 rounded-[10px] text-[12.5px] bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[11.5px] font-bold text-black/70 dark:text-white/70 mb-1">Min. Order (Rp)</label>
                                <input type="number" step="any" name="min_order_amount" value="0" class="w-full h-9 px-3 rounded-[10px] text-[12.5px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                            <div>
                                <label class="block text-[11.5px] font-bold text-black/70 dark:text-white/70 mb-1">Batas Total Kuota</label>
                                <input type="number" name="usage_limit" placeholder="Kosongkan jika ∞" class="w-full h-9 px-3 rounded-[10px] text-[12.5px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                            <div>
                                <label class="block text-[11.5px] font-bold text-black/70 dark:text-white/70 mb-1">Batas per Bisnis</label>
                                <input type="number" name="usage_per_business_limit" value="1" min="1" class="w-full h-9 px-3 rounded-[10px] text-[12.5px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11.5px] font-bold text-black/70 dark:text-white/70 mb-1">Berlaku Mulai</label>
                                <input type="date" name="valid_from" class="w-full h-9 px-3 rounded-[10px] text-[12.5px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                            <div>
                                <label class="block text-[11.5px] font-bold text-black/70 dark:text-white/70 mb-1">Berlaku Hingga</label>
                                <input type="date" name="valid_until" class="w-full h-9 px-3 rounded-[10px] text-[12.5px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                        </div>

                        <!-- Target Tier & Siklus -->
                        <div class="space-y-2 pt-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                            <div class="text-[12px] font-bold text-black/70 dark:text-white/70">Target Paket &amp; Siklus (Kosongkan jika berlaku untuk semua):</div>
                            <div class="flex flex-wrap items-center gap-4 text-[12.5px]">
                                <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" name="applicable_tiers[]" value="standard" class="rounded text-[#007AFF] focus:ring-[#007AFF]">
                                    <span class="text-black/80 dark:text-white/80">Standard</span>
                                </label>
                                <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" name="applicable_tiers[]" value="premium" class="rounded text-[#007AFF] focus:ring-[#007AFF]">
                                    <span class="text-black/80 dark:text-white/80">Premium</span>
                                </label>
                                <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" name="applicable_tiers[]" value="prestige" class="rounded text-[#007AFF] focus:ring-[#007AFF]">
                                    <span class="text-black/80 dark:text-white/80">Prestige</span>
                                </label>
                                <span class="text-black/20 dark:text-white/20">|</span>
                                <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" name="applicable_cycles[]" value="monthly" class="rounded text-[#007AFF] focus:ring-[#007AFF]">
                                    <span class="text-black/80 dark:text-white/80">Bulanan</span>
                                </label>
                                <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" name="applicable_cycles[]" value="annual" class="rounded text-[#007AFF] focus:ring-[#007AFF]">
                                    <span class="text-black/80 dark:text-white/80">Tahunan</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                            <button type="button" @click="createOpen = false" class="px-4 py-2 rounded-[12px] text-[13px] font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/10 transition-colors">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 rounded-[12px] text-[13px] font-semibold bg-[#007AFF] hover:bg-[#0071E3] text-white shadow-sm transition-all">
                                Simpan &amp; Terbitkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL SHEET 2: EDIT KODE PROMO -->
        <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" @click="editOpen = false"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl p-6 sm:p-7">
                    <div class="flex items-center justify-between pb-4 border-b border-black/[0.06] dark:border-white/[0.08]">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 flex items-center justify-center text-[#34C759] shrink-0">
                                <i data-lucide="edit-3" class="w-5 h-5" stroke-width="1.8"></i>
                            </div>
                            <div>
                                <h3 class="text-[17px] font-bold text-black dark:text-white">Edit Parameter Promo</h3>
                                <p class="text-[11.5px] text-black/50 dark:text-white/50" x-text="'Ubah konfigurasi untuk kode ' + editData.code"></p>
                            </div>
                        </div>
                        <button type="button" @click="editOpen = false" class="p-1.5 rounded-[10px] text-black/40 dark:text-white/40 hover:bg-black/5 dark:hover:bg-white/10">
                            <i data-lucide="x" class="w-5 h-5" stroke-width="2"></i>
                        </button>
                    </div>

                    <form :action="'{{ url('admin/promos') }}/' + editData.id" method="POST" class="mt-5 space-y-4">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">Kode Voucher <span class="text-red-500">*</span></label>
                                <input type="text" name="code" x-model="editData.code" required class="w-full h-10 px-3.5 rounded-[12px] uppercase font-mono font-bold text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                            <div>
                                <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">Nama Promo <span class="text-red-500">*</span></label>
                                <input type="text" name="name" x-model="editData.name" required class="w-full h-10 px-3.5 rounded-[12px] text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">Deskripsi</label>
                            <textarea name="description" x-model="editData.description" rows="2" class="w-full p-3 rounded-[12px] text-[12.5px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]"></textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/[0.04] dark:border-white/[0.06]">
                            <div>
                                <label class="block text-[11.5px] font-bold text-black/70 dark:text-white/70 mb-1">Jenis Diskon</label>
                                <select name="discount_type" x-model="editData.discount_type" class="w-full h-9 px-3 rounded-[10px] text-[12.5px] bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                    <option value="percentage">Persentase (%)</option>
                                    <option value="fixed">Nominal Flat (Rp)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11.5px] font-bold text-black/70 dark:text-white/70 mb-1">Nilai Potongan <span class="text-red-500">*</span></label>
                                <input type="number" step="any" name="discount_value" x-model="editData.discount_value" required class="w-full h-9 px-3 rounded-[10px] text-[12.5px] bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                            <div>
                                <label class="block text-[11.5px] font-bold text-black/70 dark:text-white/70 mb-1">Maks. Diskon (Rp)</label>
                                <input type="number" step="any" name="max_discount_amount" x-model="editData.max_discount_amount" class="w-full h-9 px-3 rounded-[10px] text-[12.5px] bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[11.5px] font-bold text-black/70 dark:text-white/70 mb-1">Min. Order (Rp)</label>
                                <input type="number" step="any" name="min_order_amount" x-model="editData.min_order_amount" class="w-full h-9 px-3 rounded-[10px] text-[12.5px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                            <div>
                                <label class="block text-[11.5px] font-bold text-black/70 dark:text-white/70 mb-1">Batas Total Kuota</label>
                                <input type="number" name="usage_limit" x-model="editData.usage_limit" class="w-full h-9 px-3 rounded-[10px] text-[12.5px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                            <div>
                                <label class="block text-[11.5px] font-bold text-black/70 dark:text-white/70 mb-1">Batas per Bisnis</label>
                                <input type="number" name="usage_per_business_limit" x-model="editData.usage_per_business_limit" min="1" class="w-full h-9 px-3 rounded-[10px] text-[12.5px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11.5px] font-bold text-black/70 dark:text-white/70 mb-1">Berlaku Mulai</label>
                                <input type="date" name="valid_from" x-model="editData.valid_from" class="w-full h-9 px-3 rounded-[10px] text-[12.5px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                            <div>
                                <label class="block text-[11.5px] font-bold text-black/70 dark:text-white/70 mb-1">Berlaku Hingga</label>
                                <input type="date" name="valid_until" x-model="editData.valid_until" class="w-full h-9 px-3 rounded-[10px] text-[12.5px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                        </div>

                        <!-- Target Tier & Siklus -->
                        <div class="space-y-2 pt-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                            <div class="text-[12px] font-bold text-black/70 dark:text-white/70">Target Paket &amp; Siklus:</div>
                            <div class="flex flex-wrap items-center gap-4 text-[12.5px]">
                                <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" name="applicable_tiers[]" value="standard" :checked="editData.applicable_tiers.includes('standard')" class="rounded text-[#007AFF] focus:ring-[#007AFF]">
                                    <span class="text-black/80 dark:text-white/80">Standard</span>
                                </label>
                                <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" name="applicable_tiers[]" value="premium" :checked="editData.applicable_tiers.includes('premium')" class="rounded text-[#007AFF] focus:ring-[#007AFF]">
                                    <span class="text-black/80 dark:text-white/80">Premium</span>
                                </label>
                                <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" name="applicable_tiers[]" value="prestige" :checked="editData.applicable_tiers.includes('prestige')" class="rounded text-[#007AFF] focus:ring-[#007AFF]">
                                    <span class="text-black/80 dark:text-white/80">Prestige</span>
                                </label>
                                <span class="text-black/20 dark:text-white/20">|</span>
                                <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" name="applicable_cycles[]" value="monthly" :checked="editData.applicable_cycles.includes('monthly')" class="rounded text-[#007AFF] focus:ring-[#007AFF]">
                                    <span class="text-black/80 dark:text-white/80">Bulanan</span>
                                </label>
                                <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" name="applicable_cycles[]" value="annual" :checked="editData.applicable_cycles.includes('annual')" class="rounded text-[#007AFF] focus:ring-[#007AFF]">
                                    <span class="text-black/80 dark:text-white/80">Tahunan</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                            <button type="button" @click="editOpen = false" class="px-4 py-2 rounded-[12px] text-[13px] font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/10 transition-colors">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 rounded-[12px] text-[13px] font-semibold bg-[#34C759] hover:bg-[#30D158] text-white shadow-sm transition-all">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection
