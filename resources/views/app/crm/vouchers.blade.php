@extends('layouts.app', [
    'title' => 'Voucher & Kode Promo — Cooca UMKM',
    'headerTitle' => 'Voucher & Kupon Promo',
    'headerSubtitle' => 'Kelola kampanye kupon diskon, promosi kasir POS, dan reward loyalitas member'
])

@section('content')
<div class="space-y-6 pb-12" x-data="{
    showCreateModal: false,
    copiedCode: null,
    previewCode: 'DISKON10',
    previewName: 'Promo Spesial Member',
    previewType: 'percentage',
    previewValue: 10,
    previewMinOrder: 50000,
    previewMaxDiscount: 20000,
    previewTier: 'all',

    copyCode(code) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(code);
        }
        this.copiedCode = code;
        setTimeout(() => this.copiedCode = null, 2500);
    },

    setPreset(type, val, name, code) {
        this.previewType = type;
        this.previewValue = val;
        this.previewName = name;
        this.previewCode = code;
        const codeInput = document.querySelector('input[name=\'code\']');
        const nameInput = document.querySelector('input[name=\'name\']');
        const typeSelect = document.querySelector('select[name=\'discount_type\']');
        const valInput = document.querySelector('input[name=\'discount_value\']');
        if (codeInput) codeInput.value = code;
        if (nameInput) nameInput.value = name;
        if (typeSelect) typeSelect.value = type;
        if (valInput) valInput.value = val;
    }
}">

    <!-- 0. Standard Breadcrumb Bar -->
    <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 print:hidden" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors flex items-center gap-1.5 font-bold text-slate-900 dark:text-white">
            <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
            <span>Dashboard</span>
        </a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600"></i>
        <a href="{{ route('crm.members.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
            <i data-lucide="heart-handshake" class="w-3.5 h-3.5"></i>
            <span>CRM &amp; Loyalitas</span>
        </a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600"></i>
        <span class="text-slate-900 dark:text-white font-bold flex items-center gap-1.5">
            <span>Voucher &amp; Kupon Promo</span>
        </span>
    </nav>

    <!-- 1. Top Header Banner (Seukuran Dashboard Penuh) -->
    <div class="bg-white dark:bg-slate-900/90 p-5 sm:p-6 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 transition-colors">
        <div class="space-y-1.5 max-w-3xl">
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full px-2.5 py-0.5 text-[10px] sm:text-xs font-bold border inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200/90 dark:border-emerald-800/90">
                    <i data-lucide="ticket" class="w-3.5 h-3.5"></i>
                    <span>Marketing &amp; Diskon POS</span>
                </span>
                <span class="rounded-full px-2.5 py-0.5 text-[10px] sm:text-xs font-bold border inline-flex items-center gap-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 font-mono">
                    Total: {{ $vouchers->total() }} Kupon
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                Voucher &amp; Kode Promo Kasir
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                Tingkatkan frekuensi belanja pelanggan dengan kode kupon diskon persentase (%) atau nominal tetap (Rp) saat transaksi POS.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
            <a href="{{ route('crm.members.index') }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/80 transition cursor-pointer shadow-2xs flex items-center justify-center gap-1.5 flex-1 sm:flex-none">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke CRM</span>
            </a>
            <button type="button" 
                    @click="showCreateModal = true" 
                    class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm shadow-emerald-600/20 active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-1.5 flex-1 sm:flex-none">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Buat Voucher Baru</span>
            </button>
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
        <!-- Pillar 1: Total Vouchers -->
        <div class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Total Kupon Promo</span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/80 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <i data-lucide="ticket" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-900 dark:text-white font-mono tracking-tight truncate">
                    {{ $vouchers->total() }}
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Kampanye</span>
                <span class="font-bold text-slate-700 dark:text-slate-300">Dibuat</span>
            </div>
        </div>

        <!-- Pillar 2: Active Coupons -->
        <div class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Kupon Siap Pakai</span>
                    <div class="w-9 h-9 rounded-xl bg-teal-50 dark:bg-teal-950/60 border border-teal-200/80 dark:border-teal-800/80 flex items-center justify-center text-teal-600 dark:text-teal-400">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-black text-emerald-600 dark:text-emerald-400 font-mono tracking-tight truncate">
                    {{ $vouchers->where('is_active', true)->count() }}
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Status</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400">Aktif di Kasir POS</span>
            </div>
        </div>

        <!-- Pillar 3: Total Claims / Usage -->
        <div class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Total Klaim Voucher</span>
                    <div class="w-9 h-9 rounded-xl bg-cyan-50 dark:bg-cyan-950/60 border border-cyan-200/80 dark:border-cyan-800/80 flex items-center justify-center text-cyan-600 dark:text-cyan-400">
                        <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-black text-cyan-600 dark:text-cyan-400 font-mono tracking-tight truncate">
                    {{ number_format($vouchers->sum('used_count'), 0, ',', '.') }}
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Penebusan Diskon</span>
                <span class="font-bold text-slate-700 dark:text-slate-300 font-mono">Pelanggan</span>
            </div>
        </div>

        <!-- Pillar 4: Integrasi POS -->
        <div class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Metode Promo</span>
                    <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/80 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <i data-lucide="percent" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-lg sm:text-xl font-black text-slate-900 dark:text-white tracking-tight truncate">
                    % dan Nominal Tetap
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Penerapan</span>
                <span class="font-bold text-amber-600 dark:text-amber-400 font-mono">Otomatis Kasir</span>
            </div>
        </div>
    </div>

    <!-- 3. Vouchers Perforated Card Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($vouchers as $v)
        @php
            $isPercentage = $v->discount_type === 'percentage';
            $isActive = (bool) $v->is_active;
            $tierEligible = strtolower($v->tier_eligibility ?? 'all');
            $tierLabel = match($tierEligible) {
                'platinum' => 'Platinum Member',
                'gold' => 'Gold Member',
                'silver' => 'Silver Member',
                'bronze' => 'Bronze Member',
                default => 'Semua Pelanggan',
            };
            $usagePct = $v->usage_limit ? min(100, round(($v->used_count / $v->usage_limit) * 100)) : 0;
        @endphp

        <!-- Perforated Ticket Card Style -->
        <div class="rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col justify-between relative overflow-hidden transition hover:border-emerald-500/40 group">
            
            <!-- Card Top: Voucher Name & Code -->
            <div class="p-5 sm:p-6 space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-1 min-w-0">
                        <span class="text-[10px] font-mono uppercase tracking-wider text-slate-500 dark:text-slate-400 font-bold block truncate">
                            {{ $v->name }}
                        </span>
                        
                        <!-- Discount Highlight -->
                        <div class="text-2xl sm:text-3xl font-black font-mono tracking-tight {{ $isActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500' }}">
                            @if($isPercentage)
                                {{ $v->discount_value }}% <span class="text-xs font-sans uppercase font-bold text-slate-500 dark:text-slate-400">OFF</span>
                            @else
                                <span class="text-sm font-sans">Rp</span> {{ number_format($v->discount_value, 0, ',', '.') }} <span class="text-xs font-sans uppercase font-bold text-slate-500 dark:text-slate-400">POTONGAN</span>
                            @endif
                        </div>
                    </div>

                    <!-- Active Toggle Button -->
                    <form action="{{ route('crm.vouchers.toggle', $v->id) }}" method="POST" class="shrink-0">
                        @csrf
                        <button type="submit" 
                                class="px-2.5 py-1 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider border transition shadow-2xs cursor-pointer {{ $isActive ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200/90 dark:border-emerald-800/90 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200' }}"
                                title="Klik untuk mengubah status aktif/non-aktif">
                            {{ $isActive ? 'Aktif' : 'Non-Aktif' }}
                        </button>
                    </form>
                </div>

                <!-- Ticket Voucher Code Copy Box -->
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-between gap-2 shadow-inner">
                    <div class="flex items-center gap-2 min-w-0">
                        <i data-lucide="tag" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                        <span class="font-mono font-black text-sm tracking-wider text-slate-900 dark:text-white truncate">
                            {{ $v->code }}
                        </span>
                    </div>
                    <button type="button" 
                            @click="copyCode('{{ $v->code }}')"
                            class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 text-xs font-bold transition flex items-center gap-1 shrink-0 cursor-pointer shadow-2xs"
                            title="Salin Kode Kupon">
                        <i data-lucide="copy" class="w-3 h-3" x-show="copiedCode !== '{{ $v->code }}'"></i>
                        <i data-lucide="check" class="w-3 h-3 text-emerald-600 dark:text-emerald-400" x-show="copiedCode === '{{ $v->code }}'" x-cloak></i>
                        <span class="text-[10px]" x-text="copiedCode === '{{ $v->code }}' ? 'Tersalin' : 'Salin'"></span>
                    </button>
                </div>
            </div>

            <!-- Perforated Ticket Divider with Notches -->
            <div class="relative flex items-center">
                <div class="w-4 h-8 bg-slate-100 dark:bg-slate-950 rounded-r-full -ml-2 border-r border-slate-200 dark:border-slate-800"></div>
                <div class="flex-1 border-b-2 border-dashed border-slate-200 dark:border-slate-800 mx-2"></div>
                <div class="w-4 h-8 bg-slate-100 dark:bg-slate-950 rounded-l-full -mr-2 border-l border-slate-200 dark:border-slate-800"></div>
            </div>

            <!-- Card Bottom: Conditions, Tier, Usage & Validity -->
            <div class="p-5 sm:p-6 space-y-4 pt-4">
                <div class="space-y-2 text-xs text-slate-700 dark:text-slate-300">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 dark:text-slate-400">Min. Belanja:</span>
                        <span class="font-mono font-bold text-slate-900 dark:text-slate-200">
                            {{ $v->min_order_amount > 0 ? 'Rp ' . number_format($v->min_order_amount, 0, ',', '.') : 'Tanpa Minimum' }}
                        </span>
                    </div>

                    @if($v->max_discount_amount)
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 dark:text-slate-400">Maks. Potongan:</span>
                        <span class="font-mono font-bold text-slate-900 dark:text-slate-200">
                            Rp {{ number_format($v->max_discount_amount, 0, ',', '.') }}
                        </span>
                    </div>
                    @endif

                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 dark:text-slate-400">Target Member:</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-amber-700 dark:text-amber-300 uppercase">
                            {{ $tierLabel }}
                        </span>
                    </div>
                </div>

                <!-- Usage Progress Bar (If limit exists) -->
                <div class="space-y-1.5 pt-1">
                    <div class="flex items-center justify-between text-[11px] font-mono">
                        <span class="text-slate-500 dark:text-slate-400">
                            Terpakai: <strong class="text-slate-900 dark:text-white font-bold">{{ $v->used_count }}</strong>{{ $v->usage_limit ? ' / ' . $v->usage_limit : '' }} kali
                        </span>
                        @if($v->usage_limit)
                            <span class="text-slate-500 font-bold">{{ $usagePct }}%</span>
                        @else
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold text-[10px]">Unlimited</span>
                        @endif
                    </div>
                    @if($v->usage_limit)
                    <div class="w-full h-1.5 bg-slate-100 dark:bg-slate-950 rounded-full overflow-hidden border border-slate-200 dark:border-slate-800">
                        <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full" style="width: {{ $usagePct }}%"></div>
                    </div>
                    @endif
                </div>

                <!-- Validity Period -->
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-[11px] font-mono text-slate-500 dark:text-slate-400">
                    <div class="flex items-center gap-1">
                        <i data-lucide="clock" class="w-3.5 h-3.5 text-slate-400"></i>
                        <span>
                            @if($v->valid_until)
                                Berlaku s/d {{ $v->valid_until->format('d/m/Y') }}
                            @else
                                Tanpa Masa Kadaluarsa
                            @endif
                        </span>
                    </div>
                    <span class="text-slate-400 dark:text-slate-600">ID: {{ substr($v->id, 0, 6) }}</span>
                </div>
            </div>

        </div>
        @empty
        <!-- Empty State -->
        <div class="col-span-full py-16 px-6 text-center space-y-4 rounded-2xl bg-white dark:bg-slate-900/80 border border-dashed border-slate-200 dark:border-slate-800 shadow-xs">
            <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 flex items-center justify-center mx-auto shadow-inner">
                <i data-lucide="ticket-x" class="w-8 h-8"></i>
            </div>
            <div class="space-y-1 max-w-sm mx-auto">
                <h4 class="text-base font-black text-slate-900 dark:text-white">Belum Ada Kupon Voucher Dibuat</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Buat kode promo diskon menarik untuk pelanggan setia atau kampanye potongan harga saat checkout kasir POS.
                </p>
            </div>
            <div class="pt-2">
                <button type="button" 
                        @click="showCreateModal = true"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-sm shadow-emerald-600/20 transition cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Buat Voucher Pertama</span>
                </button>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($vouchers->hasPages())
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800">
        {{ $vouchers->links() }}
    </div>
    @endif

    <!-- Modal: Buat Voucher Baru (With Live Coupon Preview) -->
    <div x-show="showCreateModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 transition-all"
         @keydown.escape.window="showCreateModal = false">
        
        <div class="w-full max-w-xl p-6 sm:p-7 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl space-y-5 relative max-h-[90vh] overflow-y-auto"
             @click.away="showCreateModal = false">
            
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <i data-lucide="ticket-plus" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Buat Voucher Promo Baru</h3>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">Diskon Kasir &amp; Loyalty POS</span>
                    </div>
                </div>
                <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Quick Presets -->
            <div class="space-y-1.5">
                <span class="text-[10px] font-mono text-slate-500 dark:text-slate-400 uppercase font-bold tracking-wider block">Pilih Template Promo Cepat:</span>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="setPreset('percentage', 10, 'Diskon Spesial 10%', 'HEMAT10')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold border border-slate-200 dark:border-slate-700 transition cursor-pointer">Diskon 10%</button>
                    <button type="button" @click="setPreset('percentage', 20, 'Diskon Spesial 20%', 'DISKON20')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold border border-slate-200 dark:border-slate-700 transition cursor-pointer">Diskon 20%</button>
                    <button type="button" @click="setPreset('fixed', 15000, 'Potongan Rp 15 Ribu', 'POTONG15K')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold border border-slate-200 dark:border-slate-700 transition cursor-pointer">Potongan 15K</button>
                    <button type="button" @click="setPreset('fixed', 50000, 'Potongan Rp 50 Ribu', 'VIP50K')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold border border-slate-200 dark:border-slate-700 transition cursor-pointer">Potongan 50K</button>
                </div>
            </div>

            <!-- Live Ticket Preview Card -->
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-emerald-500/30 flex items-center justify-between gap-3 shadow-inner">
                <div class="space-y-0.5 min-w-0">
                    <span class="text-[9px] font-mono uppercase text-emerald-600 dark:text-emerald-400 font-bold tracking-wider block">Live Preview Kupon POS</span>
                    <div class="text-xs font-bold text-slate-900 dark:text-white truncate" x-text="previewName || 'Nama Kupon'"></div>
                    <div class="text-sm sm:text-base font-black font-mono text-emerald-600 dark:text-emerald-400" 
                         x-text="previewType === 'percentage' ? ((previewValue || 0) + '% OFF') : ('Rp ' + Number(previewValue || 0).toLocaleString('id-ID') + ' OFF')"></div>
                </div>
                <div class="px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-xs text-slate-900 dark:text-white tracking-widest uppercase shrink-0 shadow-2xs"
                     x-text="previewCode || 'KODE'"></div>
            </div>

            <form action="{{ route('crm.vouchers.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf

                <!-- Code & Name -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div class="space-y-1.5">
                        <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                            Kode Voucher <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="code" 
                               required 
                               x-model="previewCode"
                               placeholder="Contoh: PROMO10" 
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 uppercase font-mono text-slate-900 dark:text-white font-bold focus:outline-hidden transition">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                            Nama Kampanye Promo <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               required 
                               x-model="previewName"
                               placeholder="Contoh: Diskon Pelanggan Baru" 
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 text-slate-900 dark:text-white focus:outline-hidden transition">
                    </div>
                </div>

                <!-- Discount Type & Value -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div class="space-y-1.5">
                        <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">Tipe Diskon</label>
                        <select name="discount_type" 
                                x-model="previewType"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 text-slate-900 dark:text-white focus:outline-hidden transition">
                            <option value="percentage">Persentase (%)</option>
                            <option value="fixed">Nominal Tetap (Rp)</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                            Besaran Nilai Diskon <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" 
                               step="any" 
                               name="discount_value" 
                               required 
                               x-model.number="previewValue"
                               placeholder="Misal: 10 atau 20000" 
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 text-slate-900 dark:text-white font-mono font-bold focus:outline-hidden transition">
                    </div>
                </div>

                <!-- Min Order & Max Discount -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div class="space-y-1.5">
                        <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">Min. Belanja Pesanan (Rp)</label>
                        <input type="number" 
                               name="min_order_amount" 
                               value="0" 
                               placeholder="0 jika tanpa batas" 
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 text-slate-900 dark:text-white font-mono focus:outline-hidden transition">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">Maks. Potongan Diskon (Rp)</label>
                        <input type="number" 
                               name="max_discount_amount" 
                               placeholder="Kosongkan jika tanpa batas" 
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 text-slate-900 dark:text-white font-mono focus:outline-hidden transition">
                    </div>
                </div>

                <!-- Validity & Usage Limit -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div class="space-y-1.5">
                        <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">Berlaku Dari</label>
                        <input type="date" 
                               name="valid_from" 
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 text-slate-900 dark:text-white focus:outline-hidden transition">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">Berlaku Sampai</label>
                        <input type="date" 
                               name="valid_until" 
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 text-slate-900 dark:text-white focus:outline-hidden transition">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">Batas Kuota Pakai</label>
                        <input type="number" 
                               name="usage_limit" 
                               placeholder="Bebas / Unlimited" 
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 text-slate-900 dark:text-white font-mono focus:outline-hidden transition">
                    </div>
                </div>

                <!-- Tier Eligibility -->
                <div class="space-y-1.5">
                    <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">Target Khusus Tier Membership</label>
                    <select name="tier_eligibility" 
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 text-slate-900 dark:text-white focus:outline-hidden transition">
                        <option value="all">Semua Pelanggan / Umum</option>
                        <option value="bronze">Khusus Member Bronze Ke Atas</option>
                        <option value="silver">Khusus Member Silver Ke Atas</option>
                        <option value="gold">Khusus Member Gold Ke Atas</option>
                        <option value="platinum">Eksklusif Member Platinum</option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" 
                            @click="showCreateModal = false" 
                            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-sm shadow-emerald-600/20 active:scale-[0.98] transition flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan &amp; Rilis Voucher</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
