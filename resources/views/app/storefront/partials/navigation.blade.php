{{-- Shared Apple HIG Segmented Control Navigation for Storefront & Website Hub --}}
@php
    $currentBusiness = \App\Support\Context::business();
    $businessSlug = $currentBusiness?->slug ?? '';
    $publicStoreUrl = $businessSlug ? url("/b/{$businessSlug}") : '#';
    $storeSetting = $currentBusiness?->storeSetting;

    $showReservationsTab = request()->routeIs('storefront.reservations.*')
        || (
            $currentBusiness
            && $currentBusiness->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_RESERVATION)
            && ($storeSetting->allow_reservation ?? true)
        );

    $showShippingTab = request()->routeIs('storefront.shipping.*')
        || (
            $currentBusiness
            && $currentBusiness->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_MERCHANT_SHIPPING)
            && ($storeSetting->allow_delivery ?? true)
        );
@endphp

<div class="space-y-4">
    {{-- Header Strip with Title and Quick Link --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold shrink-0 shadow-xs">
                <i data-lucide="store" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-bold text-black dark:text-white tracking-tight truncate">
                    {{ $title ?? 'Website & Toko Online' }}
                </h1>
                <p class="text-[12px] sm:text-[12.5px] text-black/55 dark:text-white/55 truncate">
                    Etalase Publik:
                    <a href="{{ $publicStoreUrl }}" target="_blank" rel="noopener"
                        class="text-[#007AFF] hover:underline font-medium inline-flex items-center gap-1">
                        <span>{{ $publicStoreUrl }}</span>
                        <i data-lucide="external-link" class="w-3 h-3"></i>
                    </a>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 self-start sm:self-auto">
            <a href="{{ $publicStoreUrl }}" target="_blank" rel="noopener"
                class="h-9 sm:h-10 px-3.5 sm:px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[12.5px] sm:text-[13px] font-semibold transition-all active:scale-[0.98] flex items-center gap-1.5 shadow-sm">
                <i data-lucide="eye" class="w-4 h-4"></i>
                <span>Lihat Website Publik</span>
            </a>
        </div>
    </div>

    {{-- Apple HIG Segmented Control Bar --}}
    <div class="w-full overflow-x-auto no-scrollbar py-0.5">
        <div class="inline-flex p-1 bg-black/[0.04] dark:bg-white/[0.06] rounded-[14px] border border-black/[0.04] dark:border-white/[0.06] gap-1 min-w-max">
            {{-- Tab 1: Website CMS --}}
            <a href="{{ route('landing-page.edit') }}"
                class="px-3.5 py-1.5 rounded-[10px] text-[13px] font-medium transition-all duration-150 flex items-center gap-2 {{ request()->routeIs('landing-page.*') ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-semibold shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.03]' }}">
                <i data-lucide="globe" class="w-4 h-4 {{ request()->routeIs('landing-page.*') ? 'text-[#007AFF]' : 'opacity-70' }}"></i>
                <span>Website &amp; Profil</span>
            </a>

            {{-- Tab 2: Orders --}}
            @if (\App\Support\Context::hasPermission('storefront.orders.view') || \App\Support\Context::isOwner())
                <a href="{{ route('storefront.orders.index') }}"
                    class="px-3.5 py-1.5 rounded-[10px] text-[13px] font-medium transition-all duration-150 flex items-center gap-2 {{ request()->routeIs('storefront.orders.*') ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-semibold shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.03]' }}">
                    <i data-lucide="shopping-bag" class="w-4 h-4 {{ request()->routeIs('storefront.orders.*') ? 'text-[#007AFF]' : 'opacity-70' }}"></i>
                    <span>Pesanan Masuk</span>
                </a>
            @endif

            {{-- Tab 3: Reservations (Adaptive) --}}
            @if ($showReservationsTab && (\App\Support\Context::hasPermission('storefront.reservations.manage') || \App\Support\Context::isOwner()))
                <a href="{{ route('storefront.reservations.index') }}"
                    class="px-3.5 py-1.5 rounded-[10px] text-[13px] font-medium transition-all duration-150 flex items-center gap-2 {{ request()->routeIs('storefront.reservations.*') ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-semibold shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.03]' }}">
                    <i data-lucide="calendar" class="w-4 h-4 {{ request()->routeIs('storefront.reservations.*') ? 'text-[#007AFF]' : 'opacity-70' }}"></i>
                    <span>Reservasi &amp; Booking</span>
                </a>
            @endif

            {{-- Tab 4: Shipping (Adaptive) --}}
            @if ($showShippingTab && (\App\Support\Context::hasPermission('storefront.shipping.manage') || \App\Support\Context::isOwner()))
                <a href="{{ route('storefront.shipping.index') }}"
                    class="px-3.5 py-1.5 rounded-[10px] text-[13px] font-medium transition-all duration-150 flex items-center gap-2 {{ request()->routeIs('storefront.shipping.*') ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-semibold shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.03]' }}">
                    <i data-lucide="truck" class="w-4 h-4 {{ request()->routeIs('storefront.shipping.*') ? 'text-[#007AFF]' : 'opacity-70' }}"></i>
                    <span>Pengiriman</span>
                </a>
            @endif

            {{-- Tab 5: Settings --}}
            @if (\App\Support\Context::hasPermission('storefront.manage') || \App\Support\Context::isOwner())
                <a href="{{ route('storefront.settings.index') }}"
                    class="px-3.5 py-1.5 rounded-[10px] text-[13px] font-medium transition-all duration-150 flex items-center gap-2 {{ request()->routeIs('storefront.settings.*') ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-semibold shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.03]' }}">
                    <i data-lucide="settings" class="w-4 h-4 {{ request()->routeIs('storefront.settings.*') ? 'text-[#007AFF]' : 'opacity-70' }}"></i>
                    <span>Pengaturan Etalase</span>
                </a>
            @endif
        </div>
    </div>
</div>
