@php
    $activeBiz = $activeBiz ?? \App\Support\Context::business();
    $currentUser = auth()->user();
@endphp
<style>
    .app-topbar {
        min-height: 4rem;
    }

    .app-topbar-actions > a,
    .app-topbar-actions > .relative > button,
    .app-topbar-actions > .flex.items-center > div > button {
        min-height: 2.25rem !important;
    }

    .app-topbar-actions > .relative > button,
    .app-topbar-actions > .flex.items-center > div > button {
        min-width: 2.25rem !important;
        border-radius: 0.625rem !important;
    }

    .app-topbar-actions > .flex.items-center {
        min-height: 2.5rem;
        padding: 0.25rem !important;
        border-radius: 0.75rem !important;
    }

    @media (max-width: 639px) {
        .app-topbar {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }
    }

    /* One compact macOS toolbar geometry for every topbar control. */
    .app-topbar-actions {
        gap: 0.5rem !important;
    }

    .app-topbar-actions > a,
    .app-topbar-actions > .relative > button,
    .app-topbar-actions > .flex.items-center > div > button {
        height: 2.25rem !important;
        min-height: 2.25rem !important;
        border-radius: 0.625rem !important;
        box-sizing: border-box !important;
    }

    .app-topbar-actions > .flex.items-center {
        height: 2.5rem !important;
        min-height: 2.5rem !important;
        border-radius: 0.75rem !important;
    }

    .app-topbar-actions > .relative > button {
        padding: 0.25rem 0.5rem !important;
    }

    .app-topbar-actions > .relative > button > div:first-child {
        width: 2rem !important;
        height: 2rem !important;
    }
</style>
<!-- Topbar Header (Apple macOS Toolbar Architecture) -->
<header class="app-topbar h-16 glass-header sticky top-0 z-30 flex items-center justify-between px-4 sm:px-7 lg:px-9 border-b border-black/5 dark:border-white/10 transition-all">
    <!-- Left Cluster: Mobile Trigger + Desktop Sidebar Toggle + Title -->
    <div class="app-topbar-title flex items-center gap-2.5 sm:gap-4 min-w-0 flex-1">
        <!-- Mobile Drawer Toggle -->
        <button id="tour-mobile-menu-btn"
            @click="sidebarOpen = true; window.dispatchEvent(new CustomEvent('sidebar-opened'))"
            class="lg:hidden p-2.5 text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white rounded-[9px] hover:bg-black/[0.05] dark:hover:bg-white/[0.06] active:scale-[0.97] shrink-0 transition-all cursor-pointer"
            title="Buka Menu Navigasi" aria-label="Menu Navigasi">
            <i data-lucide="menu" class="w-5 h-5"></i>
        </button>

        <!-- Desktop Sidebar Collapse Toggle Button (macOS Window Control Style) -->
        <button @click="toggleSidebarCollapse()" type="button"
            class="hidden lg:flex items-center justify-center w-9 h-9 text-black/50 hover:text-black dark:text-white/50 dark:hover:text-white rounded-[9px] hover:bg-black/[0.05] dark:hover:bg-white/[0.06] active:scale-[0.97] shrink-0 transition-all cursor-pointer"
            :title="sidebarCollapsed ? 'Perluas Sidebar' : 'Ciutkan Sidebar'"
            aria-label="Toggle Sidebar">
            <i :data-lucide="sidebarCollapsed ? 'panel-left-open' : 'panel-left-close'" class="w-4 h-4"></i>
        </button>

        <!-- Header Title & Subtitle Hierarchy -->
        <div class="min-w-0 flex-1">
            <h1 class="text-[16px] font-semibold text-black dark:text-white truncate leading-tight tracking-tight">
                {{ $headerTitle ?? 'Cooca UMKM' }}
            </h1>
            <p class="text-[12px] text-black/50 dark:text-white/50 hidden sm:block truncate leading-tight mt-1">
                {{ $headerSubtitle ?? 'Sistem Perhitungan HPP & Manajemen Komersial Terintegrasi' }}
            </p>
        </div>
    </div>

    <!-- Right Cluster: Actions & Control Tools (Clean Apple HIG Layout) -->
    <div class="app-topbar-actions flex items-center gap-2.5 sm:gap-4 shrink-0">
        @if ($activeBiz)
            <!-- Active Currency Pill -->
            <div class="hidden xl:flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/5 text-[11px] text-black/60 dark:text-white/60">
                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                <span class="tabular-nums font-medium">{{ $activeBiz->currency_code }} ({{ $activeBiz->currency_symbol }})</span>
            </div>
        @endif

        <!-- Primary Action CTA: Hitung HPP -->
        <a href="{{ route('calculator.index') }}"
            class="hidden sm:flex h-8 px-3 rounded-[9px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[12px] font-semibold shadow-[0_1px_2px_rgba(0,122,255,0.25)] active:scale-[0.97] active:opacity-80 items-center gap-1.5 transition-all shrink-0 cursor-pointer"
            title="Hitung HPP Produk">
            <i data-lucide="plus" class="w-3.5 h-3.5 shrink-0"></i>
            <span>Hitung HPP</span>
        </a>

        <!-- Unified System Controls Capsule (Fullscreen & Theme Switcher) -->
        <div class="flex items-center p-0.5 rounded-[8px] bg-black/[0.05] dark:bg-white/[0.08] gap-0.5">
            <!-- Fullscreen Toggle Button -->
            <div x-data="{
                isFullscreen: false,
                init() {
                    const handleFs = () => this.updateState();
                    document.addEventListener('fullscreenchange', handleFs);
                    document.addEventListener('webkitfullscreenchange', handleFs);
                    document.addEventListener('mozfullscreenchange', handleFs);
                    document.addEventListener('MSFullscreenChange', handleFs);
                },
                toggleFullscreen() {
                    if (!document.fullscreenElement && !document.webkitFullscreenElement && !document.mozFullScreenElement && !document.msFullscreenElement) {
                        const docEl = document.documentElement;
                        if (docEl.requestFullscreen) {
                            docEl.requestFullscreen().catch(() => {});
                        } else if (docEl.webkitRequestFullscreen) {
                            docEl.webkitRequestFullscreen();
                        } else if (docEl.mozRequestFullScreen) {
                            docEl.mozRequestFullScreen();
                        } else if (docEl.msRequestFullscreen) {
                            docEl.msRequestFullscreen();
                        }
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen().catch(() => {});
                        } else if (document.webkitExitFullscreen) {
                            document.webkitExitFullscreen();
                        } else if (document.mozCancelFullScreen) {
                            document.mozCancelFullScreen();
                        } else if (document.msExitFullscreen) {
                            document.msExitFullscreen();
                        }
                    }
                },
                updateState() {
                    this.isFullscreen = !!(document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement);
                }
            }">
                <button type="button" @click="toggleFullscreen()"
                    :title="isFullscreen ? 'Keluar Layar Penuh (Esc)' : 'Mode Layar Penuh (Full Screen)'"
                    aria-label="Toggle Fullscreen"
                    class="h-7 w-7 rounded-[6px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-white/60 dark:hover:bg-white/10 transition-all flex items-center justify-center shrink-0 active:scale-[0.97] cursor-pointer">
                    <i x-show="!isFullscreen" data-lucide="maximize" class="w-3.5 h-3.5"></i>
                    <i x-show="isFullscreen" data-lucide="minimize" class="w-3.5 h-3.5 text-[#007AFF]" style="display: none;"></i>
                </button>
            </div>

            <!-- Theme Switcher (Light / Dark / System) -->
            <div x-data="{
                theme: localStorage.getItem('cooca-theme') || 'light',
                themeDropdownOpen: false,
                setTheme(val) {
                    this.theme = val;
                    localStorage.setItem('cooca-theme', val);
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    if (val === 'dark' || (val === 'system' && prefersDark)) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                    document.documentElement.setAttribute('data-theme', val);
                    this.themeDropdownOpen = false;
                },
                init() {
                    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                        if (this.theme === 'system') {
                            if (e.matches) {
                                document.documentElement.classList.add('dark');
                            } else {
                                document.documentElement.classList.remove('dark');
                            }
                        }
                    });
                }
            }" class="relative" @click.outside="themeDropdownOpen = false">
                <button type="button" @click="themeDropdownOpen = !themeDropdownOpen"
                    :title="'Ganti Tema: ' + (theme === 'dark' ? 'Gelap' : (theme === 'system' ? 'Sistem' : 'Terang'))"
                    aria-label="Theme Switcher"
                    class="h-7 w-7 rounded-[6px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-white/60 dark:hover:bg-white/10 transition-all flex items-center justify-center shrink-0 active:scale-[0.97] cursor-pointer">
                    <span x-show="theme === 'light'">
                        <i data-lucide="sun" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                    </span>
                    <span x-show="theme === 'dark'" style="display: none;">
                        <i data-lucide="moon" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                    </span>
                    <span x-show="theme === 'system'" style="display: none;">
                        <i data-lucide="monitor" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                    </span>
                </button>

                <!-- Theme Dropdown Menu (Apple Glass Squircle) -->
                <div x-show="themeDropdownOpen" x-transition
                    class="absolute right-0 mt-2 w-36 rounded-[12px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.15)] p-1 z-50 space-y-0.5 text-[13px]"
                    style="display: none;">
                    <button type="button" @click="setTheme('light')"
                        class="w-full px-2.5 py-1.5 rounded-[7px] flex items-center gap-2 text-left font-medium transition active:scale-[0.98] cursor-pointer"
                        :class="theme === 'light' ? 'bg-[#FF9500]/10 text-[#FF9500] font-semibold' : 'text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]'">
                        <i data-lucide="sun" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                        <span>Terang</span>
                    </button>
                    <button type="button" @click="setTheme('dark')"
                        class="w-full px-2.5 py-1.5 rounded-[7px] flex items-center gap-2 text-left font-medium transition active:scale-[0.98] cursor-pointer"
                        :class="theme === 'dark' ? 'bg-[#5856D6]/10 text-[#5856D6] font-semibold' : 'text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]'">
                        <i data-lucide="moon" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                        <span>Gelap</span>
                    </button>
                    <button type="button" @click="setTheme('system')"
                        class="w-full px-2.5 py-1.5 rounded-[7px] flex items-center gap-2 text-left font-medium transition active:scale-[0.98] cursor-pointer"
                        :class="theme === 'system' ? 'bg-[#007AFF]/10 text-[#007AFF] font-semibold' : 'text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]'">
                        <i data-lucide="monitor" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                        <span>Sistem OS</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Vertical Separator -->
        <div class="h-5 w-px bg-black/10 dark:bg-white/10 hidden sm:block"></div>

        <!-- User Profile Dropdown (Transferred from Sidebar - Apple macOS Account Menu) -->
        <div class="relative" x-data="{ profileOpen: false }" @click.outside="profileOpen = false">
            <button type="button" @click="profileOpen = !profileOpen"
                class="flex items-center gap-2 p-1 pl-1 pr-2 sm:pr-2.5 rounded-full hover:bg-black/[0.04] dark:hover:bg-white/[0.05] transition-all active:scale-[0.97] cursor-pointer"
                :title="'Akun: ' + '{{ $currentUser->name ?? 'User' }}'">
                <!-- Squircle Avatar -->
                <div class="w-8 h-8 rounded-full bg-[#007AFF]/12 border border-[#007AFF]/25 flex items-center justify-center font-bold text-xs text-[#007AFF] shrink-0 shadow-2xs">
                    {{ substr($currentUser->name ?? 'U', 0, 2) }}
                </div>
                <div class="text-left hidden md:block max-w-[120px]">
                    <div class="text-[13px] font-semibold text-black dark:text-white truncate leading-tight">
                        {{ $currentUser->name ?? 'User' }}
                    </div>
                    <div class="text-[10px] text-black/45 dark:text-white/45 truncate leading-tight">
                        {{ $activeBiz->name ?? 'Owner' }}
                    </div>
                </div>
                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-black/40 dark:text-white/40 transition-transform duration-200 shrink-0" :class="profileOpen ? 'rotate-180' : ''"></i>
            </button>

            <!-- Profile Popover Sheet (macOS Sonoma Style) -->
            <div x-show="profileOpen" x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                class="absolute right-0 mt-2 w-64 rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.15)] p-1.5 z-50 space-y-1 text-[13px]"
                style="display: none;">

                <!-- User Details Header -->
                <div class="p-2.5 rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-full bg-[#007AFF]/12 border border-[#007AFF]/25 flex items-center justify-center font-bold text-sm text-[#007AFF] shrink-0">
                        {{ substr($currentUser->name ?? 'U', 0, 2) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-black dark:text-white truncate text-[13px]">
                            {{ $currentUser->name ?? 'User' }}
                        </p>
                        <p class="text-[11px] text-black/50 dark:text-white/50 truncate">
                            {{ $currentUser->email ?? '' }}
                        </p>
                    </div>
                </div>

                <!-- Action Links -->
                <div class="space-y-0.5 pt-0.5">
                    <a href="{{ route('profile.edit') }}" @click="profileOpen = false"
                        class="w-full px-2.5 py-1.5 rounded-[7px] text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white flex items-center gap-2 transition active:scale-[0.98]">
                        <i data-lucide="key" class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i>
                        <span>Profil &amp; Sandi</span>
                    </a>

                    @if (\App\Support\Context::isOwner())
                        <a href="{{ route('feedback.bugs.index') }}" @click="profileOpen = false"
                            class="w-full px-2.5 py-1.5 rounded-[7px] text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white flex items-center gap-2 transition active:scale-[0.98]">
                            <i data-lucide="life-buoy" class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i>
                            <span>Dukungan &amp; Bantuan</span>
                        </a>
                    @endif

                    <form method="POST" action="{{ route('onboarding.restart') }}">
                        @csrf
                        <button type="submit" @click="profileOpen = false"
                            class="w-full px-2.5 py-1.5 rounded-[7px] text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white flex items-center gap-2 transition active:scale-[0.98] text-left cursor-pointer">
                            <i data-lucide="help-circle" class="w-3.5 h-3.5 text-black/45 dark:text-white/45 shrink-0"></i>
                            <span>Ulang Panduan Tour</span>
                        </button>
                    </form>
                </div>

                <div class="border-t border-black/5 dark:border-white/10 my-1"></div>

                <!-- Logout Option -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full px-2.5 py-1.5 rounded-[7px] text-[#FF3B30] dark:text-[#FF453A] hover:bg-[#FF3B30]/10 flex items-center gap-2 transition active:scale-[0.98] text-left font-medium cursor-pointer">
                        <i data-lucide="log-out" class="w-3.5 h-3.5 shrink-0"></i>
                        <span>Keluar Akun</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
