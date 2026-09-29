{{--
    FASE 5: Coffee Shop & Cafe Hero Section (`artisan_brew` theme)
    PRD-21 §3.1: Specialty coffee roastery with earthy warm aesthetic.
    Design Tokens: Primary #8B5A2B, Accent #C88A58, BG #FAF7F2, Heading: Playfair Display
    DESIGN_VARIANCE: 8 | MOTION_INTENSITY: 6 | VISUAL_DENSITY: 3
--}}

<section class="relative overflow-hidden pt-8 pb-16 sm:pt-16 sm:pb-28">
    {{-- Subtle coffee bean texture background --}}
    <div class="absolute inset-0 opacity-[0.03]"
        style="background-image: url('data:image/svg+xml,{{ rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80"><ellipse cx="40" cy="40" rx="18" ry="12" fill="#8B5A2B" transform="rotate(-25 40 40)"/><path d="M28 38 Q40 30 52 38" fill="none" stroke="#8B5A2B" stroke-width="1.5"/></svg>') }}'); background-size: 80px 80px;">
    </div>

    <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-16 items-center">

            {{-- Left: Hero Copy --}}
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                {{-- Origin & Roast Badge --}}
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-semibold"
                    style="background: {{ $activeTheme['badge_bg'] ?? '#8B5A2B1A' }}; color: {{ $activeTheme['badge_text'] ?? '#8B5A2B' }};">
                    <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                    <span>{{ $activeTheme['industry'] ?? 'Kafe & Kopi Spesialis' }}</span>
                </div>

                <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-[3.5rem] tracking-tight leading-[1.1]"
                    style="color: #3D2B1F;">
                    {{ $landingPage->headline ?: $business->name }}
                </h1>

                <p class="text-base sm:text-lg max-w-2xl mx-auto lg:mx-0 leading-relaxed"
                    style="color: #6B5744;">
                    {{ $landingPage->subheadline ?: 'Nikmati ritual kopi spesialti dari biji terpilih, roasting presisi, dan penyeduhan penuh cinta. Single Origin hingga House Blend — setiap cangkir punya cerita.' }}
                </p>

                {{-- Tasting Notes Pills --}}
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2 pt-1">
                    @php
                        $tastingNotes = [
                            ['label' => 'Fruity', 'icon' => 'cherry'],
                            ['label' => 'Nutty', 'icon' => 'nut'],
                            ['label' => 'Chocolaty', 'icon' => 'bean'],
                            ['label' => 'Floral', 'icon' => 'flower-2'],
                        ];
                    @endphp
                    @foreach ($tastingNotes as $note)
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-medium border transition-all duration-200 hover:scale-105 cursor-default"
                            style="background: #FAF7F2; border-color: #C88A5840; color: #8B5A2B;">
                            <i data-lucide="{{ $note['icon'] }}" class="w-3 h-3"></i>
                            <span>{{ $note['label'] }}</span>
                        </span>
                    @endforeach
                </div>

                {{-- CTA Buttons --}}
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-3">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-7 py-3.5 text-sm sm:text-base font-semibold rounded-full text-white shadow-lg hover:shadow-xl transition-all duration-300 flex items-center gap-2 min-h-[48px] hover:-translate-y-0.5 active:scale-[0.97]"
                            style="background: linear-gradient(135deg, #8B5A2B, #6B4423);">
                            <span>{{ $landingPage->cta_primary_text ?: 'Jelajahi Menu Kopi' }}</span>
                            <i data-lucide="coffee" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 text-sm sm:text-base font-semibold rounded-full border-2 shadow-sm hover:shadow-md transition-all duration-300 flex items-center gap-2 min-h-[48px] hover:-translate-y-0.5"
                            style="border-color: #C88A58; color: #8B5A2B; background: rgba(200, 138, 88, 0.08);">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-600"></i>
                            <span>Pesan via WhatsApp</span>
                        </a>
                    @endif
                </div>

                {{-- Roast Level Visual Indicator --}}
                <div class="flex items-center justify-center lg:justify-start gap-3 pt-4">
                    <span class="text-[11px] font-medium uppercase tracking-wider" style="color: #8B5A2B99;">
                        Roast Level
                    </span>
                    <div class="flex items-center gap-1">
                        @for ($i = 1; $i <= 5; $i++)
                            <div class="w-5 h-2 rounded-full transition-all duration-300"
                                style="background: {{ $i <= 3 ? '#8B5A2B' : '#8B5A2B25' }};"></div>
                        @endfor
                    </div>
                    <span class="text-[11px] font-semibold" style="color: #8B5A2B;">Medium</span>
                </div>
            </div>

            {{-- Right: Hero Visual Card --}}
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    @php
                        $heroImg = $landingPage->hero_image_url
                            ?: 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=1000&auto=format&fit=crop&q=80';
                    @endphp
                    <div class="relative rounded-[24px] overflow-hidden shadow-2xl aspect-[4/5] sm:aspect-[3/4]"
                        style="border: 2px solid rgba(139, 90, 43, 0.12);">
                        <img src="{{ $heroImg }}" alt="{{ $business->name }}"
                            class="w-full h-full object-cover">
                        <div class="absolute inset-0"
                            style="background: linear-gradient(180deg, transparent 40%, rgba(61, 43, 31, 0.85) 100%);">
                        </div>

                        {{-- Floating Live Status Card on Image --}}
                        <div
                            class="absolute bottom-4 left-4 right-4 p-4 rounded-[16px] backdrop-blur-xl border text-xs shadow-xl"
                            style="background: rgba(250, 247, 242, 0.92); border-color: rgba(200, 138, 88, 0.2);">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
                                    <span class="font-semibold" style="color: #3D2B1F;">
                                        <i data-lucide="coffee" class="w-3.5 h-3.5 inline-block mr-1" style="color: #8B5A2B;"></i>
                                        {{ __('storefront.hero.open_today') }}
                                    </span>
                                </div>
                                <span class="font-mono text-[10px]" style="color: #8B5A2B;">
                                    Freshly Brewed
                                </span>
                            </div>
                        </div>

                        {{-- Floating Bean Origin Badge --}}
                        <div
                            class="absolute top-4 right-4 px-3 py-1.5 rounded-full backdrop-blur-md text-[10px] font-bold uppercase tracking-wider shadow-lg"
                            style="background: rgba(139, 90, 43, 0.85); color: #FAF7F2;">
                            Single Origin
                        </div>
                    </div>

                    {{-- Decorative Steam Wisps (CSS Animation) --}}
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2 pointer-events-none opacity-40">
                        <div class="flex gap-3">
                            <div class="w-1 h-12 rounded-full animate-steam-1"
                                style="background: linear-gradient(to top, #C88A5800, #C88A58, #C88A5800);"></div>
                            <div class="w-1 h-16 rounded-full animate-steam-2"
                                style="background: linear-gradient(to top, #C88A5800, #C88A58, #C88A5800);"></div>
                            <div class="w-1 h-10 rounded-full animate-steam-3"
                                style="background: linear-gradient(to top, #C88A5800, #C88A58, #C88A5800);"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Steam animation keyframes --}}
    <style>
        @keyframes steam-rise-1 {
            0%, 100% { transform: translateY(0) scaleY(1); opacity: 0; }
            25% { opacity: 0.6; }
            50% { transform: translateY(-20px) scaleY(1.2); opacity: 0.3; }
            75% { opacity: 0.1; }
        }
        @keyframes steam-rise-2 {
            0%, 100% { transform: translateY(0) scaleY(1); opacity: 0; }
            30% { opacity: 0.5; }
            60% { transform: translateY(-28px) scaleY(1.3); opacity: 0.2; }
        }
        @keyframes steam-rise-3 {
            0%, 100% { transform: translateY(0) scaleY(1); opacity: 0; }
            20% { opacity: 0.4; }
            70% { transform: translateY(-16px) scaleY(1.1); opacity: 0.15; }
        }
        .animate-steam-1 { animation: steam-rise-1 3s ease-in-out infinite; }
        .animate-steam-2 { animation: steam-rise-2 4s ease-in-out infinite 0.5s; }
        .animate-steam-3 { animation: steam-rise-3 3.5s ease-in-out infinite 1s; }
    </style>
</section>
