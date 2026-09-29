@props([
    'business',
])

<div x-data="{
        isOpen: false,
        orderToken: '',
        isSubmitting: false,
        submitTrack() {
            const token = this.orderToken.trim();
            if (!token) return;
            window.location.href = '{{ url('/' . $business->slug) }}/order/' + encodeURIComponent(token);
        }
    }"
    @open-order-track-modal.window="isOpen = true"
    @keydown.escape.window="isOpen = false"
    x-show="isOpen" x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-title" role="dialog" aria-modal="true">

    {{-- Backdrop with Blur --}}
    <div x-show="isOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity"
        @click="isOpen = false"></div>

    <div class="min-h-full flex items-center justify-center p-4 text-center sm:p-0">
        <div x-show="isOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-[#181E2A] px-6 pt-6 pb-6 text-left shadow-2xl transition-all w-full max-w-lg border border-black/[0.08] dark:border-white/[0.1]">

            {{-- Close Button --}}
            <button type="button" @click="isOpen = false"
                class="absolute top-5 right-5 p-2 rounded-xl text-neutral-400 hover:text-neutral-600 dark:hover:text-white bg-black/[0.03] dark:bg-white/[0.05] transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>

            {{-- Modal Header --}}
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-theme-primary/10 text-theme-primary flex items-center justify-center font-bold">
                    <i data-lucide="package-search" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-base text-neutral-900 dark:text-white leading-snug">
                        {{ __('storefront.tracking.title') }}
                    </h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        {{ __('storefront.tracking.subtitle') }}
                    </p>
                </div>
            </div>

            {{-- Form Input --}}
            <form @submit.prevent="submitTrack" class="space-y-4">
                <div>
                    <label for="modal-order-token" class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">
                        {{ __('storefront.tracking.order_number') }}
                    </label>
                    <div class="relative">
                        <i data-lucide="receipt" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400"></i>
                        <input type="text" id="modal-order-token" x-model="orderToken" required
                            placeholder="{{ __('storefront.tracking.enter_token_placeholder') }}"
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-black/[0.08] dark:border-white/[0.1] bg-neutral-50 dark:bg-neutral-900 text-neutral-900 dark:text-white text-xs placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-theme-primary transition">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="isOpen = false"
                        class="px-4 py-2.5 rounded-xl text-xs font-semibold text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-white/5 transition">
                        {{ __('storefront.reservation.cancel') }}
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl text-xs font-semibold text-white shadow-xs flex items-center gap-2 hover:opacity-90 active:scale-95 transition"
                        style="background-color: var(--theme-primary);">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i>
                        <span>{{ __('storefront.tracking.track_button') }}</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
