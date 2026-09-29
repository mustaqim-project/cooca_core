{{-- ========================================================================= --}}
{{-- BENTO APPLE HIG FLOATING CART PILL (§PRD-07 & §PRD-20)                    --}}
{{-- ========================================================================= --}}
<div x-show="$store.cart.count() > 0 && !window.location.pathname.endsWith('/checkout')" x-cloak
    x-transition:enter="transition ease-out duration-300 transform"
    x-transition:enter-start="opacity-0 translate-y-8 scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition ease-in duration-200 transform"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 translate-y-8 scale-95"
    class="fixed bottom-4 inset-x-4 sm:inset-x-auto sm:right-6 sm:max-w-md z-40"
    style="bottom: calc(1rem + env(safe-area-inset-bottom, 0px));">
    
    <div class="bg-neutral-900/90 dark:bg-white/95 text-white dark:text-neutral-900 backdrop-blur-2xl px-4 sm:px-5 py-3 rounded-2xl sm:rounded-3xl shadow-[0_16px_40px_rgba(0,0,0,0.3)] border border-white/10 dark:border-black/10 flex items-center justify-between gap-4">
        
        {{-- Item Count & Dynamic Subtotal --}}
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm text-white shadow-xs shrink-0 font-mono tabular-nums"
                style="background-color: var(--theme-primary);">
                <span x-text="$store.cart.count()"></span>
            </div>
            <div>
                <div class="text-[11px] text-neutral-400 dark:text-neutral-500 font-medium">
                    {{ __('storefront.checkout.subtotal') }}
                </div>
                <div class="text-sm sm:text-base font-bold font-mono tracking-tight tabular-nums"
                    x-text="'Rp ' + $store.cart.subtotal().toLocaleString('id-ID')"></div>
            </div>
        </div>

        {{-- Direct Checkout CTA --}}
        <a href="{{ url('/' . $business->slug . '/checkout') }}"
            class="px-4 py-2.5 rounded-xl font-semibold text-xs sm:text-sm text-white shadow-xs flex items-center gap-2 hover:opacity-90 active:scale-95 transition-all duration-150 shrink-0"
            style="background-color: var(--theme-primary);">
            <span>{{ __('storefront.checkout.pay_now') }}</span>
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
    </div>
</div>
