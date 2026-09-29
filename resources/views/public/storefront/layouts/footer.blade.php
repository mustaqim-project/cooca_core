{{-- ========================================================================= --}}
{{-- BENTO APPLE HIG STOREFRONT FOOTER (§PRD-07 & §PRD-20)                     --}}
{{-- ========================================================================= --}}
<footer class="mt-auto border-t border-black/[0.06] dark:border-white/[0.08] bg-white/70 dark:bg-[#0B0F19]/70 backdrop-blur-xl pt-14 pb-8 transition-colors duration-300">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-10 mb-12">

            {{-- Col 1: Business Profile & Location --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    @if ($business->logo_url || $landingPage->logo_url)
                        <img src="{{ $business->logo_url ?: $landingPage->logo_url }}"
                            alt="{{ $business->name }}"
                            class="w-10 h-10 rounded-2xl object-cover border border-black/[0.08] dark:border-white/[0.12] shadow-xs">
                    @else
                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-heading font-bold text-base text-white shadow-xs"
                            style="background-color: var(--theme-primary);">
                            {{ strtoupper(substr($business->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <span class="font-heading font-bold text-base text-neutral-900 dark:text-white block leading-tight">
                            {{ $business->name }}
                        </span>
                        <span class="text-[11px] text-neutral-500 dark:text-neutral-400 font-medium">
                            {{ $activeTheme['industry'] ?? ($industryCategory ? __('storefront.industries.' . $industryCategory) : __('storefront.hero.verified_official')) }}
                        </span>
                    </div>
                </div>

                <p class="text-xs text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    {{ $landingPage->footer_description ?: ($landingPage->subheadline ?: ($business->description ?: __('storefront.hero.tagline'))) }}
                </p>

                @if ($landingPage->custom_address || $business->address)
                    <div class="flex items-start gap-2 text-xs text-neutral-500 dark:text-neutral-400 pt-1">
                        <i data-lucide="map-pin" class="w-4 h-4 text-theme-primary shrink-0 mt-0.5"></i>
                        <span class="leading-relaxed">{{ $landingPage->custom_address ?: $business->address }}</span>
                    </div>
                @endif
            </div>

            {{-- Col 2: Navigation Links (Strict Auto-Hide) --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-neutral-900 dark:text-white">
                    {{ $landingPage->footer_navigation_title ?: __('storefront.about.store_navigation') }}
                </h4>
                <ul class="space-y-2 text-xs text-neutral-600 dark:text-neutral-400">
                    <li>
                        <a href="{{ url('/' . $business->slug) }}" class="hover:text-theme-primary transition-colors">
                            {{ $landingPage->getNavLabel('home', __('storefront.nav.home')) }}
                        </a>
                    </li>
                    @if ($landingPage->isPageActive('catalog'))
                        <li>
                            <a href="{{ url('/' . $business->slug . '/katalog') }}" class="hover:text-theme-primary transition-colors">
                                {{ $landingPage->getNavLabel('catalog', __('storefront.nav.catalog')) }}
                            </a>
                        </li>
                    @endif
                    @if ($landingPage->isPageActive('about'))
                        <li>
                            <a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="hover:text-theme-primary transition-colors">
                                {{ $landingPage->getNavLabel('about', __('storefront.nav.about')) }}
                            </a>
                        </li>
                    @endif
                    @if ($landingPage->isPageActive('reservation'))
                        <li>
                            <a href="{{ url('/' . $business->slug . '/reservasi') }}" class="hover:text-theme-primary transition-colors">
                                {{ $landingPage->getNavLabel('reservation', $isDiningIndustry ? __('storefront.nav.reservation') : __('storefront.nav.booking')) }}
                            </a>
                        </li>
                    @endif
                    @if ($landingPage->isPageActive('contact'))
                        <li>
                            <a href="{{ url('/' . $business->slug . '/kontak') }}" class="hover:text-theme-primary transition-colors">
                                {{ $landingPage->getNavLabel('contact', __('storefront.nav.contact')) }}
                            </a>
                        </li>
                    @endif
                    @if ($landingPage->isPageActive('blog'))
                        <li>
                            <a href="{{ url('/' . $business->slug . '/artikel') }}" class="hover:text-theme-primary transition-colors">
                                {{ $landingPage->getNavLabel('blog', __('storefront.nav.blog')) }}
                            </a>
                        </li>
                    @endif
                    <li>
                        <button type="button" @click="$dispatch('open-order-track-modal')" class="hover:text-theme-primary transition-colors text-left">
                            {{ __('storefront.nav.tracking') }}
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Col 3: Customer Care & Jam Operasional --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-neutral-900 dark:text-white">
                    {{ $landingPage->footer_contact_title ?: __('storefront.contact.customer_service') }}
                </h4>
                <div class="space-y-2.5 text-xs text-neutral-600 dark:text-neutral-400">
                    @if ($hasWhatsapp)
                        <div>
                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-semibold hover:underline">
                                <i data-lucide="message-circle" class="w-4 h-4 shrink-0"></i>
                                <span>WhatsApp: {{ $landingPage->whatsapp_number ?: $business->phone }}</span>
                            </a>
                        </div>
                    @endif

                    @if ($landingPage->custom_email || $business->email)
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="mail" class="w-3.5 h-3.5 shrink-0 text-neutral-400"></i>
                            <span>{{ $landingPage->custom_email ?: $business->email }}</span>
                        </div>
                    @endif

                    {{-- Operating Days Badge --}}
                    @php
                        $dayMap = [
                            'monday' => 'Senin',
                            'tuesday' => 'Selasa',
                            'wednesday' => 'Rabu',
                            'thursday' => 'Kamis',
                            'friday' => 'Jumat',
                            'saturday' => 'Sabtu',
                            'sunday' => 'Minggu',
                        ];
                        $operatingDays = (array) ($storeSetting?->operating_days ?? []);
                        $currentDayOfWeek = strtolower(now()->format('l'));
                        $isStoreOpenToday = empty($operatingDays) || in_array($currentDayOfWeek, $operatingDays, true);
                    @endphp
                    <div class="pt-1">
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-medium {{ $isStoreOpenToday ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $isStoreOpenToday ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                            <span>{{ $isStoreOpenToday ? __('storefront.contact.open_today') : __('storefront.contact.closed_today') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Col 4: Trust & Security Assurance --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-neutral-900 dark:text-white">
                    {{ __('storefront.about.security_guarantee') }}
                </h4>
                <div class="space-y-2 text-xs text-neutral-600 dark:text-neutral-400">
                    <div class="flex items-center gap-2 p-2 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06]">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                        <span class="text-[11px]">{{ __('storefront.about.original_product_guarantee') }}</span>
                    </div>
                    <div class="flex items-center gap-2 p-2 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06]">
                        <i data-lucide="lock" class="w-4 h-4 text-blue-500 shrink-0"></i>
                        <span class="text-[11px]">{{ __('storefront.checkout.qris_instant_desc') }}</span>
                    </div>
                    <div class="flex items-center gap-2 p-2 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06]">
                        <i data-lucide="truck" class="w-4 h-4 text-amber-500 shrink-0"></i>
                        <span class="text-[11px]">{{ __('storefront.about.fast_delivery_guarantee') }}</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Bottom Bar: Legal & Attribution --}}
        <div class="pt-8 border-t border-black/[0.06] dark:border-white/[0.08] flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-neutral-500">
            <div>
                {{ $landingPage->footer_copyright ?: '© ' . date('Y') . ' ' . $business->name . '. ' . __('storefront.about.all_rights_reserved') }}
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ url('/kebijakan-privasi') }}" class="hover:text-neutral-800 dark:hover:text-neutral-200 transition">
                    {{ __('storefront.about.privacy_policy') }}
                </a>
                <span class="text-neutral-300 dark:text-neutral-700">&bull;</span>
                <a href="{{ url('/syarat-ketentuan') }}" class="hover:text-neutral-800 dark:hover:text-neutral-200 transition">
                    {{ __('storefront.about.terms_of_service') }}
                </a>
                <span class="text-neutral-300 dark:text-neutral-700">&bull;</span>
                <div class="flex items-center gap-1.5">
                    <span>{{ __('storefront.about.powered_by') }}</span>
                    <a href="https://cooca.id" target="_blank" rel="noopener"
                        class="font-semibold text-neutral-800 dark:text-neutral-200 hover:text-theme-primary transition">
                        Cooca Commerce
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>
