@extends('layouts.app', [
    'title' => __('crm.vouchers_title') . ' - Cooca',
    'headerTitle' => __('crm.vouchers_title'),
    'headerSubtitle' => __('crm.vouchers_subtitle'),
])

@section('content')
    <div class="space-y-6 pb-16" x-data="{
        showVoucherModal: false,
        copiedCode: null,

        copyVoucher(code) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(code);
            }
            this.copiedCode = code;
            setTimeout(() => this.copiedCode = null, 2500);
        }
    }">

        <x-module-header
            module="crm"
            title="{{ __('crm.vouchers_title') }}"
            subtitle="{{ __('crm.vouchers_subtitle') }}">
            @if (\App\Support\Context::hasPermission('crm.manage'))
                <button type="button" @click="showVoucherModal = true"
                    class="h-10 px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-semibold shadow-sm shadow-[#007AFF]/25 active:scale-[0.97] transition flex items-center justify-center gap-2 cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>{{ __('crm.create_voucher') }}</span>
                </button>
            @endif
        </x-module-header>

        <x-module-tabs module="crm" />

        <!-- Flash Alert -->
        @if (session('success'))
            <div class="p-4 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/20 text-[#34C759] text-[13px] font-semibold flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-[#34C759] hover:opacity-70">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        @endif

        <!-- ========================================================================= -->
        <!-- 1. BENTO HERO KPI TILES                                                   -->
        <!-- ========================================================================= -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Tile 1: Total Vouchers -->
            <div class="p-5 rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-[12px] font-semibold text-black/50 dark:text-white/50">{{ __('crm.total_vouchers') }}</span>
                    <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                        <i data-lucide="ticket" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl sm:text-3xl font-bold font-mono tracking-tight text-black dark:text-white tabular-nums">
                    {{ $totalVouchers }}
                </div>
                <div class="text-[11px] text-black/50 dark:text-white/50">
                    <span>{{ __('crm.vouchers_program_desc') }}</span>
                </div>
            </div>

            <!-- Tile 2: Active Vouchers -->
            <div class="p-5 rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-[12px] font-semibold text-black/50 dark:text-white/50">{{ __('crm.active_vouchers') }}</span>
                    <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                        <i data-lucide="zap" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl sm:text-3xl font-bold font-mono tracking-tight text-[#007AFF] tabular-nums">
                    {{ $activeVouchers }}
                </div>
                <div class="text-[11px] text-black/50 dark:text-white/50">
                    <span>{{ __('crm.vouchers_active_desc') }}</span>
                </div>
            </div>

            <!-- Tile 3: Total Pemakaian -->
            <div class="p-5 rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-[12px] font-semibold text-black/50 dark:text-white/50">{{ __('crm.total_claims') }}</span>
                    <div class="w-8 h-8 rounded-[10px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center">
                        <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl sm:text-3xl font-bold font-mono tracking-tight text-[#FF9500] tabular-nums">
                    {{ $vouchers->sum('used_count') }} <span class="text-base font-normal">{{ __('crm.times_unit') }}</span>
                </div>
                <div class="text-[11px] text-black/50 dark:text-white/50">
                    <span>{{ __('crm.vouchers_claims_desc') }}</span>
                </div>
            </div>

            <!-- Tile 4: Action Tray -->
            <div class="p-5 rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.02)] flex flex-col justify-between">
                <span class="text-[12px] font-semibold text-black/50 dark:text-white/50">{{ __('crm.actions_voucher') }}</span>
                <div class="pt-2">
                    @if (\App\Support\Context::hasPermission('crm.manage'))
                        <button type="button" @click="showVoucherModal = true"
                            class="w-full h-11 px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-bold shadow-[0_2px_8px_rgba(0,122,255,0.3)] transition cursor-pointer flex items-center justify-center gap-2">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i>
                            <span>{{ __('crm.create_voucher_btn') }}</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- 2. VOUCHERS GRID BENTO CONTAINER                                          -->
        <!-- ========================================================================= -->
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-5 rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.02)]">
                <div class="space-y-1">
                    <h3 class="text-base font-bold text-black dark:text-white">{{ __('crm.vouchers_title') }}</h3>
                    <p class="text-[13px] text-black/60 dark:text-white/60">
                        {{ __('crm.vouchers_banner_desc') }}
                    </p>
                </div>
                @if (\App\Support\Context::hasPermission('crm.manage'))
                    <button type="button" @click="showVoucherModal = true"
                        class="h-11 px-5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-semibold transition cursor-pointer flex items-center gap-2 shrink-0">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>{{ __('crm.create_voucher_btn') }}</span>
                    </button>
                @endif
            </div>

            <!-- Vouchers Grid -->
            @if ($vouchers->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($vouchers as $v)
                        <div class="rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-[0_2px_12px_rgba(0,0,0,0.02)] relative space-y-4 transition hover:-translate-y-0.5">
                            <!-- Header Voucher Card -->
                            <div class="flex items-start justify-between gap-2">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-base font-black tracking-wider text-black dark:text-white bg-black/[0.05] dark:bg-white/[0.08] px-2.5 py-1 rounded-[8px] border border-black/[0.08] dark:border-white/[0.1]">
                                            {{ $v->code }}
                                        </span>
                                        <button type="button" @click="copyVoucher('{{ $v->code }}')"
                                            class="p-1.5 rounded-[8px] text-black/40 hover:text-[#007AFF] hover:bg-[#007AFF]/10 transition cursor-pointer" title="{{ __('crm.copy_code') }}">
                                            <i data-lucide="copy" class="w-4 h-4"></i>
                                        </button>
                                        <template x-if="copiedCode === '{{ $v->code }}'">
                                            <span class="text-[11px] text-[#34C759] font-bold">{{ __('crm.voucher_copied') }}</span>
                                        </template>
                                    </div>
                                    <h4 class="font-bold text-[14px] text-black dark:text-white">{{ $v->name }}</h4>
                                </div>

                                <!-- Status Badge -->
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $v->is_active ? 'bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20' : 'bg-black/10 dark:bg-white/10 text-black/50 dark:text-white/50' }}">
                                    {{ $v->is_active ? __('crm.status_active') : __('crm.status_inactive') }}
                                </span>
                            </div>

                            <!-- Discount Detail -->
                            <div class="p-3 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-1">
                                <div class="text-[11px] text-black/50 dark:text-white/50 uppercase font-semibold">{{ __('crm.discount_cut') }}</div>
                                <div class="text-xl font-bold text-[#FF9500]">
                                    @if ($v->discount_type === 'percentage')
                                        {{ (float) $v->discount_value }}%
                                        @if ($v->max_discount_amount)
                                            <span class="text-xs font-normal text-black/50 dark:text-white/50">{{ __('crm.max_cut', ['amt' => number_format($v->max_discount_amount, 0, ',', '.')]) }}</span>
                                        @endif
                                    @else
                                        Rp {{ number_format($v->discount_value, 0, ',', '.') }}
                                    @endif
                                </div>
                                <div class="text-[11px] text-black/60 dark:text-white/60">
                                    {{ __('crm.min_spend_label', ['amt' => number_format($v->min_order_amount ?: 0, 0, ',', '.')]) }}
                                </div>
                            </div>

                            <!-- Usage & Validity Info -->
                            <div class="text-[12px] space-y-1 text-black/60 dark:text-white/60">
                                <div class="flex items-center justify-between">
                                    <span>{{ __('crm.usage_label') }}</span>
                                    <span class="font-mono font-bold text-black dark:text-white tabular-nums">
                                        {{ __('crm.usage_fraction', ['used' => $v->used_count, 'limit' => $v->usage_limit ?: '∞']) }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>{{ __('crm.valid_until_label') }}</span>
                                    <span>{{ $v->valid_until ? $v->valid_until->format('d M Y') : __('crm.no_time_limit') }}</span>
                                </div>
                            </div>

                            <!-- Toggle Status Button -->
                            @if (\App\Support\Context::hasPermission('crm.manage'))
                                <div class="pt-2 border-t border-black/[0.05] dark:border-white/[0.06]">
                                    <form method="POST" action="{{ route('crm.vouchers.toggle', $v->id) }}">
                                        @csrf
                                        <button type="submit"
                                            class="w-full h-9 rounded-[10px] text-[12px] font-semibold transition cursor-pointer flex items-center justify-center gap-1.5 {{ $v->is_active ? 'bg-[#FF3B30]/10 text-[#FF3B30] hover:bg-[#FF3B30]/20' : 'bg-[#34C759]/10 text-[#34C759] hover:bg-[#34C759]/20' }}">
                                            <i data-lucide="{{ $v->is_active ? 'power-off' : 'power' }}" class="w-3.5 h-3.5"></i>
                                            <span>{{ $v->is_active ? __('crm.deactivate_voucher') : __('crm.activate_voucher') }}</span>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Pagination Vouchers -->
                @if ($vouchers->hasPages())
                    <div class="p-4 rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08]">
                        {{ $vouchers->links() }}
                    </div>
                @endif
            @else
                <!-- Empty State Vouchers -->
                <div class="rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] py-16 px-6 text-center space-y-4">
                    <div class="w-14 h-14 rounded-[18px] bg-black/[0.04] dark:bg-white/[0.06] text-black/40 dark:text-white/40 flex items-center justify-center mx-auto">
                        <i data-lucide="ticket" class="w-7 h-7"></i>
                    </div>
                    <div class="space-y-1 max-w-sm mx-auto">
                        <h4 class="text-[15px] font-bold text-black dark:text-white">{{ __('crm.empty_vouchers_title') }}</h4>
                        <p class="text-[13px] text-black/50 dark:text-white/50 leading-relaxed">
                            {{ __('crm.empty_vouchers_desc') }}
                        </p>
                    </div>
                    @if (\App\Support\Context::hasPermission('crm.manage'))
                        <div class="pt-2">
                            <button type="button" @click="showVoucherModal = true"
                                class="h-11 px-5 rounded-[12px] bg-[#007AFF] text-white text-[13px] font-semibold hover:bg-[#0071E3] transition cursor-pointer">
                                {{ __('crm.publish_first_voucher') }}
                            </button>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL: BUAT VOUCHER DISKON PROMOSI BARU                                   -->
        <!-- ========================================================================= -->
        @if (\App\Support\Context::hasPermission('crm.manage'))
            <div x-show="showVoucherModal" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm transition"
                @keydown.escape.window="showVoucherModal = false">
                <div class="w-full max-w-lg rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-2xl overflow-hidden p-6 space-y-5"
                    @click.away="showVoucherModal = false">
                    
                    <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                                <i data-lucide="ticket" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-[15px] text-black dark:text-white">{{ __('crm.create_voucher_title') }}</h3>
                                <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('crm.create_voucher_subtitle') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="showVoucherModal = false"
                            class="p-1 rounded-[8px] text-black/40 hover:text-black dark:hover:text-white transition cursor-pointer">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('crm.vouchers.store') }}"
                        x-data="{ submitting: false }" @submit="submitting = true" class="space-y-4 pt-2">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="block text-[12px] font-bold text-black dark:text-white">{{ __('crm.voucher_code') }} <span class="text-[#FF3B30]">*</span></label>
                                <input type="text" name="code" required placeholder="Contoh: HEMAT10K"
                                    class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] font-mono font-bold text-[16px] sm:text-[14px] uppercase text-black dark:text-white focus:outline-hidden focus:border-[#007AFF]">
                            </div>

                            <div class="space-y-1">
                                <label class="block text-[12px] font-bold text-black dark:text-white">{{ __('crm.voucher_name') }} <span class="text-[#FF3B30]">*</span></label>
                                <input type="text" name="name" required placeholder="Contoh: Diskon Pembukaan"
                                    class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF]">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="block text-[12px] font-bold text-black dark:text-white">{{ __('crm.discount_type') }} <span class="text-[#FF3B30]">*</span></label>
                                <select name="discount_type" required
                                    class="w-full h-11 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[13px] text-black dark:text-white focus:outline-hidden">
                                    <option value="percentage">{{ __('crm.type_percentage') }}</option>
                                    <option value="fixed">{{ __('crm.type_fixed') }}</option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-[12px] font-bold text-black dark:text-white">{{ __('crm.discount_value') }} <span class="text-[#FF3B30]">*</span></label>
                                <input type="number" name="discount_value" required min="0" step="any" placeholder="Contoh: 10 atau 15000"
                                    class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] font-mono font-bold text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-hidden">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('crm.min_order_amount') }}</label>
                                <input type="number" name="min_order_amount" min="0" placeholder="0"
                                    class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] font-mono text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-hidden">
                            </div>

                            <div class="space-y-1">
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('crm.max_discount_amount') }}</label>
                                <input type="number" name="max_discount_amount" min="0" placeholder="Contoh: 25000"
                                    class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] font-mono text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-hidden">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('crm.usage_limit') }}</label>
                                <input type="number" name="usage_limit" min="1" placeholder="{{ __('crm.usage_limit_placeholder') }}"
                                    class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-hidden">
                            </div>

                            <div class="space-y-1">
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('crm.valid_until') }}</label>
                                <input type="date" name="valid_until"
                                    class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[13px] text-black dark:text-white focus:outline-hidden">
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" :disabled="submitting"
                                class="w-full h-12 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] disabled:opacity-50 text-white font-bold text-[14px] shadow-[0_2px_8px_rgba(0,122,255,0.3)] transition cursor-pointer flex items-center justify-center gap-2">
                                <span x-text="submitting ? '{{ __('crm.processing') }}' : '{{ __('crm.publish_voucher') }}'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

    </div>
@endsection
