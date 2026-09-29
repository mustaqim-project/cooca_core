@php
    $currentLocale = app()->getLocale();
    $currentUrl = url()->current();
    $queryParams = request()->query();
    $idUrl = $currentUrl . '?' . http_build_query(array_merge($queryParams, ['lang' => 'id']));
    $enUrl = $currentUrl . '?' . http_build_query(array_merge($queryParams, ['lang' => 'en']));
@endphp

{{-- ========================================================================= --}}
{{-- BENTO APPLE HIG STOREFRONT NAVBAR (§PRD-07 & §PRD-20)                     --}}
{{-- ========================================================================= --}}
<header
    class="sticky top-0 z-40 w-full bg-white/85 dark:bg-[#0E131F]/85 backdrop-blur-2xl border-b border-black/[0.06] dark:border-white/[0.08] transition-colors duration-300">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-3 sm:gap-6">

        {{-- 1. Tenant Brand Identity --}}
        <a href="{{ url('/' . $business->slug) }}" class="flex items-center gap-3 shrink-0 group focus:outline-none">
            @if ($business->logo_url || $landingPage->logo_url)
                <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                    class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl object-cover border border-black/[0.08] dark:border-white/[0.12] shadow-sm group-hover:scale-105 transition-transform duration-200">
            @else
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl flex items-center justify-center font-heading font-bold text-base sm:text-lg text-white shadow-sm group-hover:scale-105 transition-transform duration-200"
                    style="background: linear-gradient(135deg, var(--theme-primary), var(--theme-accent, var(--theme-primary)));">
                    {{ strtoupper(substr($business->name, 0, 2)) }}
                </div>
            @endif
            <div class="flex flex-col min-w-0">
                <span class="font-heading font-bold text-sm sm:text-base tracking-tight text-neutral-900 dark:text-white leading-snug truncate max-w-[140px] sm:max-w-[220px]">
                    {{ $business->name }}
                </span>
                <span class="text-[11px] text-neutral-500 dark:text-neutral-400 font-medium truncate flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block shrink-0"></span>
                    {{ $activeTheme['industry'] ?? ($industryCategory ? __('storefront.industries.' . $industryCategory) : __('storefront.hero.verified_official')) }}
                </span>
            </div>
        </a>

        {{-- 2. Desktop Navigation (Strict Auto-Hide of Inactive Pages) --}}
        <nav class="hidden lg:flex items-center gap-1 bg-black/[0.03] dark:bg-white/[0.04] p-1.5 rounded-2xl border border-black/[0.04] dark:border-white/[0.06]">
            {{-- Home (Always Active) --}}
            <a href="{{ url('/' . $business->slug) }}"
                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->is($business->slug) ? 'bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white shadow-sm' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white' }}">
                {{ $landingPage->getNavLabel('home', __('storefront.nav.home')) }}
            </a>

            {{-- Catalog --}}
            @if ($landingPage->isPageActive('catalog'))
                <a href="{{ url('/' . $business->slug . '/katalog') }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->is($business->slug . '/katalog*') || request()->is($business->slug . '/produk*') ? 'bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white shadow-sm' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white' }}">
                    {{ $landingPage->getNavLabel('catalog', __('storefront.nav.catalog')) }}
                </a>
            @endif

            {{-- About Us --}}
            @if ($landingPage->isPageActive('about'))
                <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->is($business->slug . '/tentang-kami*') ? 'bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white shadow-sm' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white' }}">
                    {{ $landingPage->getNavLabel('about', __('storefront.nav.about')) }}
                </a>
            @endif

            {{-- Reservation / Booking --}}
            @if ($landingPage->isPageActive('reservation'))
                <a href="{{ url('/' . $business->slug . '/reservasi') }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->is($business->slug . '/reservasi*') ? 'bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white shadow-sm' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white' }}">
                    {{ $landingPage->getNavLabel('reservation', $isDiningIndustry ? __('storefront.nav.reservation') : __('storefront.nav.booking')) }}
                </a>
            @endif

            {{-- Contact & Outlets --}}
            @if ($landingPage->isPageActive('contact'))
                <a href="{{ url('/' . $business->slug . '/kontak') }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->is($business->slug . '/kontak*') ? 'bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white shadow-sm' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white' }}">
                    {{ $landingPage->getNavLabel('contact', __('storefront.nav.contact')) }}
                </a>
            @endif

            {{-- Blog & Stories --}}
            @if ($landingPage->isPageActive('blog'))
                <a href="{{ url('/' . $business->slug . '/artikel') }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->is($business->slug . '/artikel*') ? 'bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white shadow-sm' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white' }}">
                    {{ $landingPage->getNavLabel('blog', __('storefront.nav.blog')) }}
                </a>
            @endif
        </nav>

        {{-- 3. Action Tools: i18n Switcher, Order Tracking, Auth, Cart, Checkout --}}
        <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">

            {{-- Language Switcher Bento Pill (ID / EN) --}}
            <div class="flex items-center bg-black/[0.04] dark:bg-white/[0.06] p-0.5 rounded-xl border border-black/[0.04] dark:border-white/[0.06]">
                <a href="{{ $idUrl }}"
                    class="px-2 py-1 rounded-[10px] text-[11px] font-bold tracking-wider transition-all {{ $currentLocale === 'id' ? 'bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white shadow-xs' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200' }}"
                    title="Bahasa Indonesia">
                    ID
                </a>
                <a href="{{ $enUrl }}"
                    class="px-2 py-1 rounded-[10px] text-[11px] font-bold tracking-wider transition-all {{ $currentLocale === 'en' ? 'bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white shadow-xs' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200' }}"
                    title="English">
                    EN
                </a>
            </div>

            {{-- Quick Order Tracking Modal Trigger --}}
            <button type="button" @click="$dispatch('open-order-track-modal')"
                class="hidden sm:flex items-center justify-center p-2 rounded-xl text-neutral-700 dark:text-neutral-300 bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] transition border border-black/[0.04] dark:border-white/[0.06]"
                title="{{ __('storefront.nav.tracking') }}"
                aria-label="{{ __('storefront.nav.tracking') }}">
                <i data-lucide="search" class="w-4 h-4"></i>
            </button>

            {{-- Customer Auth Status --}}
            @if (auth('customer')->check())
                <div x-data="{ profileOpen: false }" class="relative">
                    <button @click="profileOpen = !profileOpen" @click.away="profileOpen = false"
                        class="flex items-center gap-2 p-1.5 pr-2.5 rounded-xl bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] border border-black/[0.04] dark:border-white/[0.06] transition"
                        aria-label="{{ __('storefront.nav.my_account') }}">
                        @if (auth('customer')->user()->avatar_url)
                            <img src="{{ auth('customer')->user()->avatar_url }}" alt=""
                                class="w-6 h-6 rounded-full object-cover">
                        @else
                            <div class="w-6 h-6 rounded-lg bg-theme-primary text-white flex items-center justify-center text-[10px] font-bold">
                                {{ strtoupper(substr(auth('customer')->user()->name ?? 'C', 0, 1)) }}
                            </div>
                        @endif
                        <span class="hidden md:inline text-xs font-medium text-neutral-800 dark:text-neutral-200 max-w-[90px] truncate">
                            {{ auth('customer')->user()->name }}
                        </span>
                        <i data-lucide="chevron-down" class="w-3 h-3 text-neutral-400"></i>
                    </button>

                    {{-- Apple HIG Squircle Dropdown Menu --}}
                    <div x-show="profileOpen" x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                        class="absolute right-0 mt-2 w-52 bg-white/95 dark:bg-[#181E2A]/95 backdrop-blur-2xl rounded-2xl border border-black/[0.08] dark:border-white/[0.1] shadow-2xl py-1.5 z-50">
                        <a href="{{ route('customer.dashboard') }}"
                            class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-white/5 transition">
                            <i data-lucide="layout-dashboard" class="w-4 h-4 text-neutral-400"></i>
                            <span>{{ __('storefront.nav.dashboard') }}</span>
                        </a>
                        <a href="{{ route('customer.orders') }}"
                            class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-white/5 transition">
                            <i data-lucide="package" class="w-4 h-4 text-neutral-400"></i>
                            <span>{{ __('storefront.nav.my_orders') }}</span>
                        </a>
                        <a href="{{ route('customer.cart') }}"
                            class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-white/5 transition">
                            <i data-lucide="shopping-bag" class="w-4 h-4 text-neutral-400"></i>
                            <span>{{ __('storefront.nav.cart') }}</span>
                        </a>
                        <div class="border-t border-black/[0.06] dark:border-white/[0.08] my-1"></div>
                        <form method="POST" action="{{ route('customer.logout') }}">
                            @csrf
                            <button type="submit"
                                class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 transition w-full text-left">
                                <i data-lucide="log-out" class="w-4 h-4 text-red-400"></i>
                                <span>{{ __('storefront.nav.logout') }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            @else
                {{-- Guest Login Button --}}
                <a href="{{ route('customer.login', ['store' => $business->slug, 'redirect' => url()->current()]) }}"
                    class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-neutral-700 dark:text-neutral-300 bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] border border-black/[0.04] dark:border-white/[0.06] transition min-h-[36px]">
                    <i data-lucide="user" class="w-3.5 h-3.5"></i>
                    <span>{{ __('storefront.nav.login') }}</span>
                </a>
            @endif

            {{-- Cart Trigger with Live Counter Badge --}}
            <a href="{{ url('/' . $business->slug . '/checkout') }}"
                class="relative p-2 rounded-xl bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] border border-black/[0.04] dark:border-white/[0.06] transition flex items-center justify-center text-neutral-800 dark:text-neutral-200"
                aria-label="{{ __('storefront.nav.cart') }}">
                <i data-lucide="shopping-bag" class="w-4 h-4 sm:w-4.5 sm:h-4.5"></i>
                <span x-show="$store.cart.count() > 0" x-cloak x-text="$store.cart.count()"
                    class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-theme-primary text-white text-[10px] font-bold flex items-center justify-center shadow-xs font-mono">
                </span>
            </a>

            {{-- Direct Checkout Button (Desktop) --}}
            <a href="{{ url('/' . $business->slug . '/checkout') }}"
                class="hidden md:inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl font-semibold text-xs text-white shadow-xs transition hover:opacity-95 active:scale-[0.98] min-h-[36px]"
                style="background-color: var(--theme-primary);">
                <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
                <span>{{ __('storefront.nav.checkout') }}</span>
            </a>

            {{-- Mobile Hamburger Trigger --}}
            <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                class="lg:hidden p-2 rounded-xl bg-black/[0.03] dark:bg-white/[0.05] text-neutral-800 dark:text-neutral-200 hover:bg-black/[0.06] dark:hover:bg-white/[0.08] border border-black/[0.04] dark:border-white/[0.06] transition"
                aria-label="{{ __('storefront.nav.menu') }}">
                <i data-lucide="menu" class="w-4 h-4" x-show="!mobileMenuOpen"></i>
                <i data-lucide="x" class="w-4 h-4" x-show="mobileMenuOpen" x-cloak></i>
            </button>
        </div>
    </div>

    {{-- Mobile Slide-Down Menu Drawer --}}
    <div x-show="mobileMenuOpen" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="lg:hidden border-t border-black/[0.06] dark:border-white/[0.08] bg-white/95 dark:bg-[#0E131F]/95 backdrop-blur-2xl px-4 py-4 space-y-1">

        {{-- Mobile Links with Active Guard --}}
        <a href="{{ url('/' . $business->slug) }}" @click="mobileMenuOpen = false"
            class="block px-3 py-2 rounded-xl text-xs font-semibold {{ request()->is($business->slug) ? 'text-theme-primary bg-theme-primary/10 dark:bg-theme-primary/20' : 'text-neutral-700 dark:text-neutral-300' }}">
            {{ $landingPage->getNavLabel('home', __('storefront.nav.home')) }}
        </a>

        @if ($landingPage->isPageActive('catalog'))
            <a href="{{ url('/' . $business->slug . '/katalog') }}" @click="mobileMenuOpen = false"
                class="block px-3 py-2 rounded-xl text-xs font-semibold {{ request()->is($business->slug . '/katalog*') ? 'text-theme-primary bg-theme-primary/10 dark:bg-theme-primary/20' : 'text-neutral-700 dark:text-neutral-300' }}">
                {{ $landingPage->getNavLabel('catalog', __('storefront.nav.catalog')) }}
            </a>
        @endif

        @if ($landingPage->isPageActive('about'))
            <a href="{{ url('/' . $business->slug . '/tentang-kami') }}" @click="mobileMenuOpen = false"
                class="block px-3 py-2 rounded-xl text-xs font-semibold {{ request()->is($business->slug . '/tentang-kami*') ? 'text-theme-primary bg-theme-primary/10 dark:bg-theme-primary/20' : 'text-neutral-700 dark:text-neutral-300' }}">
                {{ $landingPage->getNavLabel('about', __('storefront.nav.about')) }}
            </a>
        @endif

        @if ($landingPage->isPageActive('reservation'))
            <a href="{{ url('/' . $business->slug . '/reservasi') }}" @click="mobileMenuOpen = false"
                class="block px-3 py-2 rounded-xl text-xs font-semibold {{ request()->is($business->slug . '/reservasi*') ? 'text-theme-primary bg-theme-primary/10 dark:bg-theme-primary/20' : 'text-neutral-700 dark:text-neutral-300' }}">
                {{ $landingPage->getNavLabel('reservation', $isDiningIndustry ? __('storefront.nav.reservation') : __('storefront.nav.booking')) }}
            </a>
        @endif

        @if ($landingPage->isPageActive('contact'))
            <a href="{{ url('/' . $business->slug . '/kontak') }}" @click="mobileMenuOpen = false"
                class="block px-3 py-2 rounded-xl text-xs font-semibold {{ request()->is($business->slug . '/kontak*') ? 'text-theme-primary bg-theme-primary/10 dark:bg-theme-primary/20' : 'text-neutral-700 dark:text-neutral-300' }}">
                {{ $landingPage->getNavLabel('contact', __('storefront.nav.contact')) }}
            </a>
        @endif

        @if ($landingPage->isPageActive('blog'))
            <a href="{{ url('/' . $business->slug . '/artikel') }}" @click="mobileMenuOpen = false"
                class="block px-3 py-2 rounded-xl text-xs font-semibold {{ request()->is($business->slug . '/artikel*') ? 'text-theme-primary bg-theme-primary/10 dark:bg-theme-primary/20' : 'text-neutral-700 dark:text-neutral-300' }}">
                {{ $landingPage->getNavLabel('blog', __('storefront.nav.blog')) }}
            </a>
        @endif

        <div class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08] space-y-2">
            {{-- Quick Order Tracking in Mobile Drawer --}}
            <button type="button" @click="mobileMenuOpen = false; $dispatch('open-order-track-modal')"
                class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl font-semibold text-xs bg-black/[0.03] dark:bg-white/[0.05] text-neutral-700 dark:text-neutral-300">
                <i data-lucide="search" class="w-4 h-4"></i>
                <span>{{ __('storefront.nav.tracking') }}</span>
            </button>

            @if (auth('customer')->check())
                <a href="{{ route('customer.dashboard') }}" @click="mobileMenuOpen = false"
                    class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl font-semibold text-xs bg-black/[0.03] dark:bg-white/[0.05] text-neutral-700 dark:text-neutral-300">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    <span>{{ __('storefront.nav.dashboard') }}</span>
                </a>
            @else
                <a href="{{ route('customer.login', ['store' => $business->slug, 'redirect' => url()->current()]) }}"
                    @click="mobileMenuOpen = false"
                    class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl font-semibold text-xs bg-black/[0.03] dark:bg-white/[0.05] text-neutral-700 dark:text-neutral-300">
                    <i data-lucide="user" class="w-4 h-4"></i>
                    <span>{{ __('storefront.nav.login') }}</span>
                </a>
            @endif

            <a href="{{ url('/' . $business->slug . '/checkout') }}"
                class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl font-semibold text-xs text-white shadow-xs"
                style="background-color: var(--theme-primary);">
                <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                <span>{{ __('storefront.nav.checkout') }} (<span x-text="$store.cart.count()"></span>)</span>
            </a>
        </div>
    </div>
</header>
