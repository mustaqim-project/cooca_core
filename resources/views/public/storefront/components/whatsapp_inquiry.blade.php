@props([
    'business',
    'landingPage',
    'hasWhatsapp' => true,
    'productName' => null,
    'mode' => 'floating', // 'floating' or 'inline'
    'customLabel' => null,
])

@php
    $waNumber = preg_replace('/[^0-9]/', '', (string) ($landingPage->whatsapp_number ?: $business->phone));
    if (str_starts_with($waNumber, '0')) {
        $waNumber = '62' . substr($waNumber, 1);
    }

    $defaultGreeting = $landingPage->whatsapp_welcome_message ?: 'Halo ' . $business->name . ', saya tertarik dengan produk di toko Anda. Mohon info lebih lanjut.';
    
    if ($productName) {
        $defaultGreeting = 'Halo ' . $business->name . ', saya ingin konsultasi mengenai produk: *' . $productName . '* di ' . url()->current();
    }

    $waUrl = 'https://wa.me/' . $waNumber . '?text=' . urlencode($defaultGreeting);
@endphp

@if ($hasWhatsapp && $waNumber)
    @if ($mode === 'floating')
        {{-- Floating WhatsApp Bubble Widget --}}
        <div class="fixed bottom-20 right-4 sm:bottom-6 sm:right-6 z-30 flex flex-col items-end"
            x-data="{ waChatOpen: false }">
            
            {{-- WhatsApp Greeting Card Modal --}}
            <div x-show="waChatOpen" x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-3 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-3 scale-95"
                class="mb-3 p-4 rounded-3xl bg-white/95 dark:bg-[#181E2A]/95 backdrop-blur-2xl shadow-[0_16px_36px_rgba(0,0,0,0.18)] border border-black/[0.08] dark:border-white/[0.1] max-w-xs text-xs space-y-3">
                
                <div class="flex items-center justify-between gap-3 border-b border-black/[0.06] dark:border-white/[0.08] pb-2.5">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-[#25D366] text-white flex items-center justify-center font-bold text-xs shadow-xs">
                            <i data-lucide="message-circle" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <span class="font-bold text-neutral-900 dark:text-white block leading-tight">{{ $business->name }}</span>
                            <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                {{ __('storefront.contact.customer_service') }}
                            </span>
                        </div>
                    </div>
                    <button type="button" @click="waChatOpen = false"
                        class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 transition p-1">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <p class="text-neutral-600 dark:text-neutral-300 leading-relaxed text-xs">
                    {{ $landingPage->whatsapp_welcome_message ?: 'Halo! Ada yang bisa kami bantu seputar produk atau konsultasi pesanan?' }}
                </p>

                <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer"
                    class="w-full py-2.5 px-3.5 rounded-xl bg-[#25D366] hover:bg-[#20bd5a] text-white font-semibold text-xs text-center flex items-center justify-center gap-2 shadow-xs transition-all active:scale-[0.98]">
                    <i data-lucide="send" class="w-3.5 h-3.5"></i>
                    <span>{{ __('storefront.contact.whatsapp_cta') }}</span>
                </a>
            </div>

            {{-- Floating Trigger Button --}}
            <button type="button" @click="waChatOpen = !waChatOpen"
                class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-[#25D366] text-white flex items-center justify-center shadow-[0_8px_24px_rgba(37,211,102,0.35)] hover:scale-105 active:scale-95 transition-all duration-200"
                aria-label="{{ __('storefront.contact.whatsapp_cta') }}"
                title="{{ __('storefront.contact.whatsapp_cta') }}">
                <i data-lucide="message-circle" class="w-6 h-6 sm:w-7 sm:h-7" x-show="!waChatOpen"></i>
                <i data-lucide="x" class="w-6 h-6 sm:w-7 sm:h-7" x-show="waChatOpen" x-cloak></i>
            </button>
        </div>
    @else
        {{-- Inline WhatsApp Consultation Button --}}
        <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-[#25D366]/10 hover:bg-[#25D366]/20 text-[#128C7E] dark:text-[#25D366] border border-[#25D366]/20 font-semibold text-xs transition-all active:scale-[0.98]">
            <i data-lucide="message-circle" class="w-4 h-4"></i>
            <span>{{ $customLabel ?: __('storefront.product_detail.whatsapp_consult') }}</span>
        </a>
    @endif
@endif
