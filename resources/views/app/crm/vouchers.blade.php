@extends('layouts.app', [
    'title' => 'Voucher & Kode Promo — Cooca UMKM',
    'headerTitle' => 'Voucher & Kupon Promo',
    'headerSubtitle' => 'Kelola kampanye kupon diskon, promosi kasir POS, dan reward loyalitas member'
])

@section('content')
<div class="space-y-8" x-data="{
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

    <!-- Header & Action Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('crm.members.index') }}" class="text-xs text-slate-400 hover:text-white flex items-center gap-1.5 transition">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Kembali ke CRM Member</span>
                </a>
                <span class="text-slate-600 hidden sm:inline">/</span>
                <span class="text-xs text-slate-400 font-mono hidden sm:inline">Marketing Coupons</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight mt-1">Voucher &amp; Kode Promo Kasir</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1 max-w-2xl leading-relaxed">
                Tingkatkan frekuensi belanja pelanggan dengan kode kupon diskon persentase (%) atau nominal tetap (Rp) saat transaksi POS.
            </p>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <button type="button" 
                    @click="showCreateModal = true" 
                    class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/20 transition flex items-center gap-2 group cursor-pointer">
                <i data-lucide="plus-circle" class="w-4 h-4 group-hover:rotate-90 transition-transform"></i>
                <span>Buat Voucher Baru</span>
            </button>
        </div>
    </div>

    <!-- Flash Alert -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-950/40 border border-emerald-500/40 text-emerald-300 text-xs flex items-center justify-between gap-3 shadow-lg">
            <div class="flex items-center gap-2.5">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-400 shrink-0"></i>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-400 hover:text-emerald-200">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endif

    <!-- KPI Summary Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5">
        <!-- 1. Total Vouchers -->
        <div class="p-5 rounded-3xl bg-slate-900/80 border border-slate-800 shadow-xl backdrop-blur-md flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-slate-800 border border-slate-700 text-slate-300 flex items-center justify-center shrink-0 shadow-inner">
                <i data-lucide="ticket" class="w-6 h-6 text-emerald-400"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold block">Total Kupon Promo</span>
                <div class="text-2xl font-black text-white font-mono tracking-tight mt-0.5">
                    {{ $vouchers->total() }}
                </div>
                <span class="text-[11px] text-slate-400">Kampanye dibuat</span>
            </div>
        </div>

        <!-- 2. Active Coupons -->
        <div class="p-5 rounded-3xl bg-slate-900/80 border border-slate-800 shadow-xl backdrop-blur-md flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 flex items-center justify-center shrink-0 shadow-inner">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[10px] font-mono uppercase tracking-wider text-emerald-400/80 font-bold block">Kupon Siap Digunakan</span>
                <div class="text-2xl font-black text-emerald-400 font-mono tracking-tight mt-0.5">
                    {{ $vouchers->where('is_active', true)->count() }} Aktif
                </div>
                <span class="text-[11px] text-slate-400">Tersedia di Kasir POS</span>
            </div>
        </div>

        <!-- 3. Total Claims / Usage -->
        <div class="p-5 rounded-3xl bg-slate-900/80 border border-slate-800 shadow-xl backdrop-blur-md flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-cyan-500/15 border border-cyan-500/30 text-cyan-400 flex items-center justify-center shrink-0 shadow-inner">
                <i data-lucide="shopping-bag" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[10px] font-mono uppercase tracking-wider text-cyan-400/80 font-bold block">Total Klaim Voucher</span>
                <div class="text-2xl font-black text-cyan-400 font-mono tracking-tight mt-0.5">
                    {{ number_format($vouchers->sum('used_count'), 0, ',', '.') }} Kali
                </div>
                <span class="text-[11px] text-slate-400">Dimanfaatkan pelanggan</span>
            </div>
        </div>
    </div>

    <!-- Vouchers Perforated Card Grid -->
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
        <div class="rounded-3xl bg-slate-900/95 border border-slate-800 shadow-2xl flex flex-col justify-between relative overflow-hidden backdrop-blur-xl transition hover:border-slate-700/80 group">
            
            <!-- Card Top: Voucher Name & Code -->
            <div class="p-6 space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-1 min-w-0">
                        <span class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold block truncate">
                            {{ $v->name }}
                        </span>
                        
                        <!-- Discount Highlight -->
                        <div class="text-2xl sm:text-3xl font-black font-mono tracking-tight {{ $isActive ? 'text-emerald-400' : 'text-slate-400' }}">
                            @if($isPercentage)
                                {{ $v->discount_value }}% <span class="text-xs font-sans uppercase font-bold text-slate-400">OFF</span>
                            @else
                                <span class="text-sm font-sans">Rp</span> {{ number_format($v->discount_value, 0, ',', '.') }} <span class="text-xs font-sans uppercase font-bold text-slate-400">POTONGAN</span>
                            @endif
                        </div>
                    </div>

                    <!-- Active Toggle Button -->
                    <form action="{{ route('crm.vouchers.toggle', $v->id) }}" method="POST" class="shrink-0">
                        @csrf
                        <button type="submit" 
                                class="px-3 py-1 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider border transition shadow-sm cursor-pointer {{ $isActive ? 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30 hover:bg-rose-500/20 hover:text-rose-300 hover:border-rose-500/30' : 'bg-slate-800 text-slate-400 border-slate-700 hover:bg-emerald-500/20 hover:text-emerald-300 hover:border-emerald-500/30' }}"
                                title="Klik untuk mengubah status aktif/non-aktif">
                            {{ $isActive ? 'Aktif' : 'Non-Aktif' }}
                        </button>
                    </form>
                </div>

                <!-- Ticket Voucher Code Copy Box -->
                <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between gap-2 shadow-inner">
                    <div class="flex items-center gap-2 min-w-0">
                        <i data-lucide="tag" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span class="font-mono font-black text-sm tracking-wider text-white truncate selection:bg-emerald-500 selection:text-slate-950">
                            {{ $v->code }}
                        </span>
                    </div>
                    <button type="button" 
                            @click="copyCode('{{ $v->code }}')"
                            class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-bold transition flex items-center gap-1 shrink-0"
                            title="Salin Kode Kupon">
                        <i data-lucide="copy" class="w-3 h-3" x-show="copiedCode !== '{{ $v->code }}'"></i>
                        <i data-lucide="check" class="w-3 h-3 text-emerald-400" x-show="copiedCode === '{{ $v->code }}'" x-cloak></i>
                        <span class="text-[10px]" x-text="copiedCode === '{{ $v->code }}' ? 'Tersalin' : 'Salin'"></span>
                    </button>
                </div>
            </div>

            <!-- Perforated Ticket Divider with Notches -->
            <div class="relative flex items-center">
                <div class="w-4 h-8 bg-slate-950 rounded-r-full -ml-2 border-r border-slate-800"></div>
                <div class="flex-1 border-b-2 border-dashed border-slate-800/80 mx-2"></div>
                <div class="w-4 h-8 bg-slate-950 rounded-l-full -mr-2 border-l border-slate-800"></div>
            </div>

            <!-- Card Bottom: Conditions, Tier, Usage & Validity -->
            <div class="p-6 space-y-4 pt-4">
                <div class="space-y-2 text-xs text-slate-300">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Min. Belanja:</span>
                        <span class="font-mono font-bold text-slate-200">
                            {{ $v->min_order_amount > 0 ? 'Rp ' . number_format($v->min_order_amount, 0, ',', '.') : 'Tanpa Minimum' }}
                        </span>
                    </div>

                    @if($v->max_discount_amount)
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Maks. Potongan:</span>
                        <span class="font-mono font-bold text-slate-200">
                            Rp {{ number_format($v->max_discount_amount, 0, ',', '.') }}
                        </span>
                    </div>
                    @endif

                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Target Member:</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-950 border border-slate-800 text-amber-300 uppercase">
                            {{ $tierLabel }}
                        </span>
                    </div>
                </div>

                <!-- Usage Progress Bar (If limit exists) -->
                <div class="space-y-1.5 pt-1">
                    <div class="flex items-center justify-between text-[11px] font-mono">
                        <span class="text-slate-400">
                            Terpakai: <strong class="text-white">{{ $v->used_count }}</strong>{{ $v->usage_limit ? ' / ' . $v->usage_limit : '' }} kali
                        </span>
                        @if($v->usage_limit)
                            <span class="text-slate-500 font-bold">{{ $usagePct }}%</span>
                        @else
                            <span class="text-emerald-400 text-[10px]">Unlimited</span>
                        @endif
                    </div>
                    @if($v->usage_limit)
                    <div class="w-full h-1.5 bg-slate-950 rounded-full overflow-hidden border border-slate-800">
                        <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full" style="width: {{ $usagePct }}%"></div>
                    </div>
                    @endif
                </div>

                <!-- Validity Period -->
                <div class="pt-2 border-t border-slate-800/80 flex items-center justify-between text-[11px] font-mono text-slate-400">
                    <div class="flex items-center gap-1">
                        <i data-lucide="clock" class="w-3.5 h-3.5 text-slate-500"></i>
                        <span>
                            @if($v->valid_until)
                                Berlaku s/d {{ $v->valid_until->format('d/m/Y') }}
                            @else
                                Tanpa Masa Kadaluarsa
                            @endif
                        </span>
                    </div>
                    <span class="text-slate-600">ID: {{ substr($v->id, 0, 6) }}</span>
                </div>
            </div>

        </div>
        @empty
        <!-- Empty State -->
        <div class="col-span-full py-16 px-6 text-center space-y-4 rounded-3xl bg-slate-900/80 border border-dashed border-slate-800">
            <div class="w-16 h-16 rounded-3xl bg-slate-800/80 border border-slate-700/60 text-slate-500 flex items-center justify-center mx-auto shadow-inner">
                <i data-lucide="ticket-x" class="w-8 h-8"></i>
            </div>
            <div class="space-y-1 max-w-sm mx-auto">
                <h4 class="text-base font-black text-white">Belum Ada Kupon Voucher Dibuat</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Buat kode promo diskon menarik untuk pelanggan setia atau kampanye potongan harga saat checkout kasir POS.
                </p>
            </div>
            <div class="pt-2">
                <button type="button" 
                        @click="showCreateModal = true"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/20 transition">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Buat Voucher Pertama</span>
                </button>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($vouchers->hasPages())
    <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800">
        {{ $vouchers->links() }}
    </div>
    @endif

    <!-- Modal: Buat Voucher Baru (With Live Coupon Preview) -->
    <div x-show="showCreateModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-md p-4 transition-all"
         @keydown.escape.window="showCreateModal = false">
        
        <div class="w-full max-w-xl p-6 sm:p-7 rounded-3xl bg-slate-900 border border-slate-700 shadow-2xl space-y-5 relative max-h-[90vh] overflow-y-auto"
             @click.away="showCreateModal = false">
            
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 flex items-center justify-center">
                        <i data-lucide="ticket-plus" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-white">Buat Voucher Promo Baru</h3>
                        <span class="text-[10px] text-slate-400 font-mono">Diskon Kasir &amp; Loyalty POS</span>
                    </div>
                </div>
                <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-white p-1 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Quick Presets -->
            <div class="space-y-1.5">
                <span class="text-[10px] font-mono text-slate-400 uppercase font-bold tracking-wider block">Pilih Template Promo Cepat:</span>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="setPreset('percentage', 10, 'Diskon Spesial 10%', 'HEMAT10')" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700 transition">Diskon 10%</button>
                    <button type="button" @click="setPreset('percentage', 20, 'Diskon Spesial 20%', 'DISKON20')" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700 transition">Diskon 20%</button>
                    <button type="button" @click="setPreset('fixed', 15000, 'Potongan Rp 15 Ribu', 'POTONG15K')" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700 transition">Potongan 15K</button>
                    <button type="button" @click="setPreset('fixed', 50000, 'Potongan Rp 50 Ribu', 'VIP50K')" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700 transition">Potongan 50K</button>
                </div>
            </div>

            <!-- Live Ticket Preview Card -->
            <div class="p-4 rounded-2xl bg-gradient-to-r from-slate-950 via-slate-900 to-slate-950 border border-emerald-500/30 flex items-center justify-between gap-3 shadow-inner">
                <div class="space-y-0.5 min-w-0">
                    <span class="text-[9px] font-mono uppercase text-emerald-400 font-bold tracking-wider block">Live Preview Kupon POS</span>
                    <div class="text-xs font-bold text-white truncate" x-text="previewName || 'Nama Kupon'"></div>
                    <div class="text-sm sm:text-base font-black font-mono text-emerald-400" 
                         x-text="previewType === 'percentage' ? ((previewValue || 0) + '% OFF') : ('Rp ' + Number(previewValue || 0).toLocaleString('id-ID') + ' OFF')"></div>
                </div>
                <div class="px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-700 font-mono font-black text-xs text-white tracking-widest uppercase shrink-0 shadow"
                     x-text="previewCode || 'KODE'"></div>
            </div>

            <form action="{{ route('crm.vouchers.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf

                <!-- Code & Name -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div class="space-y-1.5">
                        <label class="block text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                            Kode Voucher <span class="text-rose-400">*</span>
                        </label>
                        <input type="text" 
                               name="code" 
                               required 
                               x-model="previewCode"
                               placeholder="Contoh: PROMO10" 
                               class="w-full bg-slate-950 border border-slate-700 focus:border-emerald-500 rounded-xl px-3.5 py-2.5 uppercase font-mono text-white font-bold focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                            Nama Kampanye Promo <span class="text-rose-400">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               required 
                               x-model="previewName"
                               placeholder="Contoh: Diskon Pelanggan Baru" 
                               class="w-full bg-slate-950 border border-slate-700 focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-white focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">
                    </div>
                </div>

                <!-- Discount Type & Value -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div class="space-y-1.5">
                        <label class="block text-slate-300 font-bold uppercase text-[10px] tracking-wider">Tipe Diskon</label>
                        <select name="discount_type" 
                                x-model="previewType"
                                class="w-full bg-slate-950 border border-slate-700 focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-white focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">
                            <option value="percentage">Persentase (%)</option>
                            <option value="fixed">Nominal Tetap (Rp)</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                            Besaran Nilai Diskon <span class="text-rose-400">*</span>
                        </label>
                        <input type="number" 
                               step="any" 
                               name="discount_value" 
                               required 
                               x-model.number="previewValue"
                               placeholder="Misal: 10 atau 20000" 
                               class="w-full bg-slate-950 border border-slate-700 focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-white font-mono font-bold focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">
                    </div>
                </div>

                <!-- Min Order & Max Discount -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div class="space-y-1.5">
                        <label class="block text-slate-300 font-bold uppercase text-[10px] tracking-wider">Min. Belanja Pesanan (Rp)</label>
                        <input type="number" 
                               name="min_order_amount" 
                               value="0" 
                               placeholder="0 jika tanpa batas" 
                               class="w-full bg-slate-950 border border-slate-700 focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-white font-mono focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-slate-300 font-bold uppercase text-[10px] tracking-wider">Maks. Potongan Diskon (Rp)</label>
                        <input type="number" 
                               name="max_discount_amount" 
                               placeholder="Kosongkan jika tanpa batas" 
                               class="w-full bg-slate-950 border border-slate-700 focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-white font-mono focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">
                    </div>
                </div>

                <!-- Validity & Usage Limit -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div class="space-y-1.5">
                        <label class="block text-slate-300 font-bold uppercase text-[10px] tracking-wider">Berlaku Dari</label>
                        <input type="date" 
                               name="valid_from" 
                               class="w-full bg-slate-950 border border-slate-700 focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-white focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-slate-300 font-bold uppercase text-[10px] tracking-wider">Berlaku Sampai</label>
                        <input type="date" 
                               name="valid_until" 
                               class="w-full bg-slate-950 border border-slate-700 focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-white focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-slate-300 font-bold uppercase text-[10px] tracking-wider">Batas Kuota Pakai</label>
                        <input type="number" 
                               name="usage_limit" 
                               placeholder="Bebas / Unlimited" 
                               class="w-full bg-slate-950 border border-slate-700 focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-white font-mono focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">
                    </div>
                </div>

                <!-- Tier Eligibility -->
                <div class="space-y-1.5">
                    <label class="block text-slate-300 font-bold uppercase text-[10px] tracking-wider">Target Khusus Tier Membership</label>
                    <select name="tier_eligibility" 
                            class="w-full bg-slate-950 border border-slate-700 focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-white focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">
                        <option value="all">Semua Pelanggan / Umum</option>
                        <option value="bronze">Khusus Member Bronze Ke Atas</option>
                        <option value="silver">Khusus Member Silver Ke Atas</option>
                        <option value="gold">Khusus Member Gold Ke Atas</option>
                        <option value="platinum">Eksklusif Member Platinum</option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-800">
                    <button type="button" 
                            @click="showCreateModal = false" 
                            class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/20 transition flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan &amp; Rilis Voucher</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
