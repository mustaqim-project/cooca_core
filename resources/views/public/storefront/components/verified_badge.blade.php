@props([
    'type' => 'all', // 'all', 'halal', 'bpom', 'kemenkes', 'official', 'payment', 'warranty'
    'compact' => false,
])

<div class="w-full">
    @if ($type === 'all')
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            {{-- Official Store --}}
            <div class="p-3 rounded-2xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <i data-lucide="badge-check" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-neutral-900 dark:text-white block leading-tight">
                        {{ __('storefront.hero.verified_official') }}
                    </span>
                    <span class="text-[10px] text-neutral-500 dark:text-neutral-400">
                        {{ __('storefront.hero.active_status') }}
                    </span>
                </div>
            </div>

            {{-- 100% Original --}}
            <div class="p-3 rounded-2xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-neutral-900 dark:text-white block leading-tight">
                        {{ __('storefront.about.original_product_guarantee') }}
                    </span>
                    <span class="text-[10px] text-neutral-500 dark:text-neutral-400">
                        100% Asli & Berkualitas
                    </span>
                </div>
            </div>

            {{-- Secure Payment --}}
            <div class="p-3 rounded-2xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-neutral-900 dark:text-white block leading-tight">
                        {{ __('storefront.checkout.qris_instant_title') }}
                    </span>
                    <span class="text-[10px] text-neutral-500 dark:text-neutral-400">
                        Enkripsi Standar BI
                    </span>
                </div>
            </div>

            {{-- Fast Dispatch --}}
            <div class="p-3 rounded-2xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <i data-lucide="truck" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-neutral-900 dark:text-white block leading-tight">
                        {{ __('storefront.about.fast_delivery_guarantee') }}
                    </span>
                    <span class="text-[10px] text-neutral-500 dark:text-neutral-400">
                        Pengiriman Terpercaya
                    </span>
                </div>
            </div>
        </div>
    @elseif ($type === 'halal')
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20 text-xs font-medium">
            <i data-lucide="check-circle" class="w-4 h-4 shrink-0 text-emerald-600"></i>
            <span>{{ __('storefront.industries.halal_certified') }} (MUI / BPJPH)</span>
        </div>
    @elseif ($type === 'bpom')
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-blue-500/10 text-blue-700 dark:text-blue-400 border border-blue-500/20 text-xs font-medium">
            <i data-lucide="shield-check" class="w-4 h-4 shrink-0 text-blue-600"></i>
            <span>{{ __('storefront.industries.bpom_certified') }}</span>
        </div>
    @elseif ($type === 'kemenkes')
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-teal-500/10 text-teal-700 dark:text-teal-400 border border-teal-500/20 text-xs font-medium">
            <i data-lucide="award" class="w-4 h-4 shrink-0 text-teal-600"></i>
            <span>{{ __('storefront.industries.kemenkes_certified') }}</span>
        </div>
    @elseif ($type === 'official')
        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[11px] font-semibold">
            <i data-lucide="badge-check" class="w-3.5 h-3.5"></i>
            <span>{{ __('storefront.hero.verified_official') }}</span>
        </div>
    @endif
</div>
