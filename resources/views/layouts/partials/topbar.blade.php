@php
    $activeBiz = $activeBiz ?? \App\Support\Context::business();
    $currentUser = auth()->user();

    $canAccessPos =
        \App\Support\Context::hasPermission('pos.terminal') ||
        \App\Support\Context::hasPermission('pos.orders') ||
        \App\Support\Context::hasPermission('pos.reports') ||
        \App\Support\Context::hasPermission('pos.kitchen') ||
        \App\Support\Context::hasPermission('pos.tables');

    $canAccessB2bSales =
        \App\Support\Context::hasPermission('sales.view') ||
        \App\Support\Context::hasPermission('sales.pipeline') ||
        \App\Support\Context::hasPermission('invoices.view') ||
        \App\Support\Context::hasPermission('sales.returns');

    $canAccessCrm =
        \App\Support\Context::hasPermission('customers.view') || \App\Support\Context::hasPermission('crm.view');

    $canAccessSales = $canAccessPos || $canAccessB2bSales || $canAccessCrm;

    $canAccessPurchasing =
        \App\Support\Context::hasPermission('purchasing.view') ||
        \App\Support\Context::hasPermission('purchasing.manage') ||
        \App\Support\Context::hasPermission('purchasing.bills') ||
        \App\Support\Context::hasPermission('purchase.returns') ||
        \App\Support\Context::hasPermission('receiving.manage') ||
        \App\Support\Context::hasPermission('master_data.suppliers.view');

    $canAccessInventory =
        \App\Support\Context::hasPermission('products.view') ||
        \App\Support\Context::hasPermission('materials.view') ||
        \App\Support\Context::hasPermission('inventory.view') ||
        \App\Support\Context::hasPermission('inventory.manage') ||
        \App\Support\Context::hasPermission('warehouse.view') ||
        \App\Support\Context::hasPermission('warehouse.manage') ||
        \App\Support\Context::hasPermission('pos.modifiers');

    $canAccessFinance =
        \App\Support\Context::hasPermission('accounting.view') ||
        \App\Support\Context::hasPermission('finance.cash_bank') ||
        \App\Support\Context::hasPermission('finance.receivables') ||
        \App\Support\Context::hasPermission('finance.payables') ||
        \App\Support\Context::hasPermission('expenses.view') ||
        \App\Support\Context::hasPermission('expenses.manage');

    $canAccessReports =
        \App\Support\Context::hasPermission('reports.view') || \App\Support\Context::hasPermission('pos.reports');

    $canAccessStorefront =
        \App\Support\Context::hasPermission('storefront.orders.view') ||
        \App\Support\Context::hasPermission('storefront.manage') ||
        \App\Support\Context::hasPermission('storefront.shipping.manage') ||
        \App\Support\Context::hasPermission('storefront.reservations.manage') ||
        \App\Support\Context::hasPermission('cms.manage') ||
        \App\Support\Context::isOwner();

    $canAccessChannels =
        \App\Support\Context::hasPermission('whatsapp.view') ||
        \App\Support\Context::hasPermission('whatsapp.manage') ||
        \App\Support\Context::isOwner();

    $canAccessMarketing = $canAccessCrm || $canAccessStorefront || $canAccessChannels;

    $canAccessSettings = \App\Support\Context::hasPermission('settings.view') || \App\Support\Context::isOwner();
    $canAccessRoles = \App\Support\Context::hasPermission('roles.view') || \App\Support\Context::isOwner();
    $canAccessBilling = \App\Support\Context::hasPermission('billing.view') || \App\Support\Context::isOwner();
@endphp
<style>
    .app-topbar {
        min-height: 4rem;
    }

    .app-topbar-actions>a,
    .app-topbar-actions>.relative>button,
    .app-topbar-actions>.flex.items-center>div>button {
        min-height: 2.25rem !important;
    }

    .app-topbar-actions>.relative>button,
    .app-topbar-actions>.flex.items-center>div>button {
        min-width: 2.25rem !important;
        border-radius: 0.625rem !important;
    }

    .app-topbar-actions>.flex.items-center {
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

    .app-topbar-actions>a,
    .app-topbar-actions>.relative>button,
    .app-topbar-actions>.flex.items-center>div>button {
        height: 2.25rem !important;
        min-height: 2.25rem !important;
        border-radius: 0.625rem !important;
        box-sizing: border-box !important;
    }

    .app-topbar-actions>.flex.items-center {
        height: 2.5rem !important;
        min-height: 2.5rem !important;
        border-radius: 0.75rem !important;
    }

    .app-topbar-actions>.relative>button {
        padding: 0.25rem 0.5rem !important;
    }

    .app-topbar-actions>.relative>button>div:first-child {
        width: 2rem !important;
        height: 2rem !important;
    }

    /* Apple focus ring untuk navigasi keyboard */
    .app-topbar a:focus-visible,
    .app-topbar button:focus-visible {
        outline: 2px solid rgba(0, 122, 255, 0.65) !important;
        outline-offset: 2px !important;
        border-radius: 0.5rem !important;
    }

    .dark .app-topbar a:focus-visible,
    .dark .app-topbar button:focus-visible {
        outline-color: rgba(10, 132, 255, 0.85) !important;
    }

    /* Breathing room ekstra di layar sempit (>= 320px tetap muat) */
    @media (max-width: 639px) {
        .app-topbar-actions {
            gap: 0.375rem !important;
        }

        .app-topbar-title {
            gap: 0.75rem !important;
        }

        .app-topbar h1 {
            font-size: 15px !important;
        }
    }
</style>
<!-- Topbar Header (Apple macOS Toolbar Architecture) -->
<header
    class="app-topbar h-16 glass-header sticky top-0 z-30 flex items-center justify-between px-4 sm:px-7 lg:px-9 border-b border-black/5 dark:border-white/10 transition-all">
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
            :title="sidebarCollapsed ? 'Perluas Sidebar' : 'Ciutkan Sidebar'" aria-label="Toggle Sidebar">
            <i :data-lucide="sidebarCollapsed ? 'panel-left-open' : 'panel-left-close'" class="w-4 h-4"></i>
        </button>

        <!-- Header Title & Subtitle Hierarchy -->
        <div class="min-w-0 flex-1">
            <h1 title="{{ $headerTitle ?? 'Cooca' }}"
                class="text-[16px] font-semibold text-black dark:text-white truncate leading-tight tracking-tight">
                {{ $headerTitle ?? 'Cooca' }}
            </h1>
            <p title="{{ $headerSubtitle ?? '' }}"
                class="text-[12px] text-black/55 dark:text-white/55 hidden sm:block truncate leading-tight mt-1">
                {{ $headerSubtitle ?? 'Sistem Perhitungan HPP & Manajemen Komersial Terintegrasi' }}
            </p>
        </div>
    </div>

    <!-- Right Cluster: Actions & Control Tools (Clean Apple HIG Layout) -->
    <div class="app-topbar-actions flex items-center gap-2 sm:gap-3 shrink-0">
        <!-- Global Spotlight Search Trigger Button (Desktop) -->
        <button type="button" @click="$dispatch('open-spotlight')"
            class="hidden md:flex items-center gap-2 px-3 py-1.5 h-8 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.07] dark:hover:bg-white/[0.1] border border-black/5 dark:border-white/10 text-black/55 dark:text-white/55 hover:text-black dark:hover:text-white transition-all cursor-pointer text-[12px] group"
            title="Cari menu, modul, transaksi... (Ctrl+K)">
            <i data-lucide="search" class="w-3.5 h-3.5 text-black/40 dark:text-white/40 group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors"></i>
            <span class="text-[12px] font-normal text-black/50 dark:text-white/50">Cari menu, modul, transaksi...</span>
            <kbd class="ml-1.5 inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-[5px] bg-black/[0.06] dark:bg-white/[0.1] text-[10px] font-semibold text-black/60 dark:text-white/60 font-mono tracking-tight shadow-sm">
                <span>Ctrl K</span>
            </kbd>
        </button>

        <!-- Mobile Search Trigger Button -->
        <button type="button" @click="$dispatch('open-spotlight')"
            class="md:hidden h-8 w-8 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.06] flex items-center justify-center transition-all cursor-pointer"
            title="Cari menu, modul, transaksi... (Ctrl+K)" aria-label="Cari">
            <i data-lucide="search" class="w-4 h-4"></i>
        </button>

        @if ($activeBiz)
            <!-- Active Currency Pill -->
            <div
                class="hidden lg:flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/5 text-[11px] text-black/60 dark:text-white/60">
                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                <span class="tabular-nums font-medium">{{ $activeBiz->currency_code }}
                    ({{ $activeBiz->currency_symbol }})</span>
            </div>
        @endif

        @if (\App\Support\Context::hasPermission('pos.terminal'))
            <!-- Primary Action CTA: Kasir POS -->
            <a href="{{ route('pos.terminal') }}"
                class="hidden sm:flex h-8 px-3 rounded-[9px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[12px] font-semibold shadow-[0_1px_2px_rgba(0,122,255,0.25)] active:scale-[0.97] active:opacity-80 items-center gap-1.5 transition-all shrink-0 cursor-pointer"
                title="Buka Kasir POS">
                <i data-lucide="calculator" class="w-3.5 h-3.5 shrink-0"></i>
                <span>Kasir POS</span>
            </a>

            <!-- Mobile Quick Action CTA: Kasir POS -->
            <a href="{{ route('pos.terminal') }}" aria-label="Buka Kasir POS" title="Buka Kasir POS"
                class="sm:hidden h-8 w-9 rounded-[9px] bg-[#007AFF] hover:bg-[#0071E3] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)] active:scale-[0.97] active:opacity-80 inline-flex items-center justify-center transition-all shrink-0 cursor-pointer">
                <i data-lucide="calculator" class="w-4 h-4"></i>
            </a>
        @elseif (\App\Support\Context::hasPermission('costing.view_margin'))
            <!-- Primary Action CTA: Hitung HPP -->
            <a href="{{ route('calculator.index') }}"
                class="hidden sm:flex h-8 px-3 rounded-[9px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[12px] font-semibold shadow-[0_1px_2px_rgba(0,122,255,0.25)] active:scale-[0.97] active:opacity-80 items-center gap-1.5 transition-all shrink-0 cursor-pointer"
                title="Hitung HPP Produk">
                <i data-lucide="plus" class="w-3.5 h-3.5 shrink-0"></i>
                <span>Hitung HPP</span>
            </a>

            <!-- Mobile Quick Action CTA: Hitung HPP -->
            <a href="{{ route('calculator.index') }}" aria-label="Hitung HPP" title="Hitung HPP"
                class="sm:hidden h-8 w-9 rounded-[9px] bg-[#007AFF] hover:bg-[#0071E3] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)] active:scale-[0.97] active:opacity-80 inline-flex items-center justify-center transition-all shrink-0 cursor-pointer">
                <i data-lucide="plus" class="w-4 h-4"></i>
            </a>
        @endif

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
                    <i x-show="isFullscreen" data-lucide="minimize" class="w-3.5 h-3.5 text-[#007AFF]"
                        style="display: none;"></i>
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
            }" class="relative" @click.outside="themeDropdownOpen = false"
                @keydown.esc.window="themeDropdownOpen = false">
                <button type="button" @click="themeDropdownOpen = !themeDropdownOpen"
                    :aria-expanded="themeDropdownOpen ? 'true' : 'false'" aria-haspopup="menu"
                    aria-controls="theme-menu"
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
                <div id="theme-menu" x-show="themeDropdownOpen" x-transition role="menu" aria-label="Pilihan Tema"
                    class="absolute right-0 mt-2 w-40 rounded-[12px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.15)] p-1 z-50 space-y-0.5 text-[13px]"
                    style="display: none;">
                    <button type="button" @click="setTheme('light')" role="menuitem"
                        class="w-full px-2.5 py-1.5 rounded-[7px] flex items-center justify-between gap-2 text-left font-medium transition active:scale-[0.98] cursor-pointer"
                        :class="theme === 'light' ? 'bg-[#FF9500]/10 text-[#FF9500] font-semibold' :
                            'text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]'">
                        <i data-lucide="sun" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                        <span>Terang</span>
                        <span x-show="theme === 'light'" class="ml-auto"><i data-lucide="check"
                                class="w-3.5 h-3.5"></i></span>
                    </button>
                    <button type="button" @click="setTheme('dark')" role="menuitem"
                        class="w-full px-2.5 py-1.5 rounded-[7px] flex items-center justify-between gap-2 text-left font-medium transition active:scale-[0.98] cursor-pointer"
                        :class="theme === 'dark' ? 'bg-[#5856D6]/10 text-[#5856D6] font-semibold' :
                            'text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]'">
                        <i data-lucide="moon" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                        <span>Gelap</span>
                        <span x-show="theme === 'dark'" class="ml-auto"><i data-lucide="check"
                                class="w-3.5 h-3.5"></i></span>
                    </button>
                    <button type="button" @click="setTheme('system')" role="menuitem"
                        class="w-full px-2.5 py-1.5 rounded-[7px] flex items-center justify-between gap-2 text-left font-medium transition active:scale-[0.98] cursor-pointer"
                        :class="theme === 'system' ? 'bg-[#007AFF]/10 text-[#007AFF] font-semibold' :
                            'text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]'">
                        <i data-lucide="monitor" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                        <span>Sistem</span>
                        <span x-show="theme === 'system'" class="ml-auto"><i data-lucide="check"
                                class="w-3.5 h-3.5"></i></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Vertical Separator -->
        <div class="h-5 w-px bg-black/10 dark:bg-white/10 hidden sm:block"></div>

        <!-- User Profile Dropdown (Transferred from Sidebar - Apple macOS Account Menu) -->
        <div class="relative" x-data="{ profileOpen: false }" @click.outside="profileOpen = false"
            @keydown.esc.window="profileOpen = false">
            <button type="button" @click="profileOpen = !profileOpen" :aria-expanded="profileOpen ? 'true' : 'false'"
                aria-haspopup="menu" aria-controls="profile-menu"
                class="flex items-center gap-2 p-1 pl-1 pr-2 sm:pr-2.5 rounded-full hover:bg-black/[0.04] dark:hover:bg-white/[0.05] transition-all active:scale-[0.97] cursor-pointer"
                :title="'Akun: ' + '{{ $currentUser->name ?? 'User' }}'">
                <!-- Squircle Avatar -->
                <div
                    class="w-8 h-8 rounded-full bg-[#007AFF]/12 border border-[#007AFF]/25 flex items-center justify-center font-bold text-xs text-[#007AFF] shrink-0 shadow-2xs">
                    {{ substr($currentUser->name ?? 'U', 0, 2) }}
                </div>
                <div class="text-left hidden md:block max-w-[120px]">
                    <div class="text-[13px] font-semibold text-black dark:text-white truncate leading-tight">
                        {{ $currentUser->name ?? 'User' }}
                    </div>
                    <div class="text-[11px] text-black/55 dark:text-white/55 truncate leading-tight">
                        {{ $activeBiz->name ?? 'Owner' }}
                    </div>
                </div>
                <i data-lucide="chevron-down"
                    class="w-3.5 h-3.5 text-black/40 dark:text-white/40 transition-transform duration-200 shrink-0"
                    :class="profileOpen ? 'rotate-180' : ''"></i>
            </button>

            <!-- Profile Popover Sheet (macOS Sonoma Style) -->
            <div x-show="profileOpen" x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 -translate-y-1" id="profile-menu" role="menu"
                aria-label="Menu Akun"
                class="absolute right-0 mt-2 w-64 rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.15)] p-1.5 z-50 space-y-1 text-[13px]"
                style="display: none;">

                <!-- User Details Header -->
                <div
                    class="p-2.5 rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 flex items-center gap-2.5">
                    <div
                        class="w-9 h-9 rounded-full bg-[#007AFF]/12 border border-[#007AFF]/25 flex items-center justify-center font-bold text-sm text-[#007AFF] shrink-0">
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
                            <i data-lucide="help-circle"
                                class="w-3.5 h-3.5 text-black/45 dark:text-white/45 shrink-0"></i>
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

{{-- ========================================================================= --}}
{{-- APPLE HIG SPOTLIGHT COMMAND PALETTE MODAL (CTRL+K / CMD+K)                --}}
{{-- ========================================================================= --}}
<div x-data="{
    isOpen: false,
    spotlightOpen: false,
    query: '',
    category: 'all',
    selectedIndex: 0,
    categories: [
        { id: 'all', label: 'Semua' },
        { id: 'Dashboard', label: 'Dashboard' },
        @if ($canAccessSales)
            { id: 'Kasir & Penjualan', label: 'Kasir & Penjualan' },
        @endif
        @if ($canAccessInventory)
            { id: 'Produk & Stok', label: 'Produk & Stok' },
        @endif
        @if ($canAccessPurchasing)
            { id: 'Pembelian & Supplier', label: 'Pembelian & Supplier' },
        @endif
        @if ($canAccessMarketing)
            { id: 'Pelanggan & Pemasaran', label: 'Pelanggan & Pemasaran' },
        @endif
        @if ($canAccessFinance)
            { id: 'Keuangan & Biaya', label: 'Keuangan & Biaya' },
        @endif
        @if ($canAccessReports)
            { id: 'Laporan & Analitik', label: 'Laporan & Analitik' },
        @endif
        @if ($canAccessSettings || $canAccessRoles || $canAccessBilling || \App\Support\Context::isOwner())
            { id: 'Pengaturan Usaha', label: 'Pengaturan Usaha' },
        @endif
    ],
    items: [
        // 1. Dashboards (Overview)
        { title: 'Beranda Dashboard', desc: 'Ringkasan performa penjualan, omset, dan laba kotor bisnis', category: 'Dashboard', route: '{{ route('dashboard') }}', icon: 'layout-dashboard', keywords: 'beranda home executive overview penjualan omset laba' },
        @if ($canAccessFinance)
            { title: 'Ringkasan Finansial & Kas', desc: 'Saldo kas, mutasi rekening bank, dan ringkasan arus kas', category: 'Dashboard', route: '{{ route('finance.cash-bank.index') }}', icon: 'wallet', keywords: 'keuangan kas bank accounting overview saldo' },
        @endif
        @if ($canAccessPos)
            { title: 'Ringkasan Kasir POS', desc: 'Aktivitas transaksi kasir toko, riwayat shift, dan pesanan kasir', category: 'Dashboard', route: '{{ route('pos.orders.index') }}', icon: 'receipt', keywords: 'pos kasir transaksi shift kas orders meja' },
        @endif
        @if ($canAccessB2bSales)
            { title: 'Ringkasan Penjualan B2B', desc: 'Pesanan penjualan B2B, penawaran harga, dan faktur piutang', category: 'Dashboard', route: '{{ route('sales.orders.index') }}', icon: 'shopping-bag', keywords: 'b2b penjualan sales orders so faktur' },
        @endif
        @if ($canAccessStorefront)
            { title: 'Ringkasan Pesanan Toko Online', desc: 'Aktivitas pesanan checkout etalase toko online', category: 'Dashboard', route: '{{ route('storefront.orders.index') }}', icon: 'store', keywords: 'toko online etalase pesanan order web' },
        @endif
        @if ($canAccessFinance || \App\Support\Context::isOwner())
            { title: 'Ringkasan Karyawan & Payroll', desc: 'Daftar pegawai, kasbon, dan ringkasan gaji bulanan', category: 'Dashboard', route: '{{ route('hrm.index') }}', icon: 'users', keywords: 'hrm karyawan pegawai staf payroll gaji upah' },
        @endif
        @if ($canAccessCrm || $canAccessSales)
            { title: 'Ringkasan Pelanggan & Member', desc: 'Statistik basis pelanggan setia, poin loyalitas, dan piutang', category: 'Dashboard', route: '{{ route('crm.members.index') }}', icon: 'award', keywords: 'crm pelanggan member poin loyalty customer diskon' },
        @endif
        @if ($canAccessInventory)
            { title: 'Ringkasan Gudang & Persediaan', desc: 'Monitoring level stok gudang, lokasi penyimpanan, dan stok kritis', category: 'Dashboard', route: '{{ route('warehouse.index') }}', icon: 'warehouse', keywords: 'gudang stok inventory warehouse persediaan habis' },
        @endif
        @if (\App\Support\Context::isOwner())
            { title: 'Ringkasan Pemasaran Digital', desc: 'Jadwal posting media sosial, kalender konten, dan inbox', category: 'Dashboard', route: '{{ route('social-media.index') }}', icon: 'share-2', keywords: 'marketing sosmed instagram tiktok facebook wa broadcast' },
        @endif
        { title: 'Asisten Cerdas AI Cooca', desc: 'Konsultasi bisnis, analisis penjualan, dan rekomendasi otomatis', category: 'Dashboard', route: '{{ route('pos.ai.index') }}', icon: 'bot', keywords: 'ai asisten bot analisa cerdas konsultasi pintar' },

        @if ($canAccessSales)
            // 2. Kasir & Penjualan
            @if ($canAccessPos)
                { title: 'Buka Kasir POS', desc: 'Terminal kasir cepat untuk melayani transaksi kasir harian', category: 'Kasir & Penjualan', route: '{{ route('pos.terminal') }}', icon: 'calculator', keywords: 'kasir pos terminal jualan bayar struk checkout' },
                { title: 'Riwayat Transaksi Kasir', desc: 'Daftar struk penjualan kasir POS dan rekap shift kasir', category: 'Kasir & Penjualan', route: '{{ route('pos.orders.index') }}', icon: 'receipt', keywords: 'transaksi kasir struk shift nota rekap' },
                { title: 'Layar Dapur (KDS)', desc: 'Tampilan pesanan makanan & minuman langsung untuk staf dapur', category: 'Kasir & Penjualan', route: '{{ route('pos.kitchen.index') }}', icon: 'chef-hat', keywords: 'kitchen dapur kds order masak bar makanan resto' },
                { title: 'Meja & QR Resto', desc: 'Tata kelola denah meja, nomor meja, dan cetak QR ordering', category: 'Kasir & Penjualan', route: '{{ route('pos.tables.index') }}', icon: 'layout-grid', keywords: 'meja table qr resto cafe dine in pesan' },
            @endif
            @if ($canAccessB2bSales)
                { title: 'Pesanan Penjualan (Sales Orders)', desc: 'Daftar pesanan penjualan produk ke pelanggan atau klien', category: 'Kasir & Penjualan', route: '{{ route('sales.orders.index') }}', icon: 'shopping-bag', keywords: 'pesanan penjualan so sales order so order' },
                { title: 'Surat Penawaran (Quotations)', desc: 'Buat dan kelola surat penawaran harga resmi untuk pelanggan', category: 'Kasir & Penjualan', route: '{{ route('sales.quotations.index') }}', icon: 'file-text', keywords: 'penawaran quotation harga proposal penawaran quote' },
                { title: 'Faktur Penjualan (Invoices)', desc: 'Tagihan faktur resmi, status tempo, dan penerimaan pelunasan piutang', category: 'Kasir & Penjualan', route: '{{ route('invoices.index') }}', icon: 'file-check', keywords: 'faktur invoice piutang tagihan bayar cicil tempo' },
                { title: 'Retur Penjualan', desc: 'Pencatatan pengembalian barang atau komplain retur pelanggan', category: 'Kasir & Penjualan', route: '{{ route('sales.returns.index') }}', icon: 'rotate-ccw', keywords: 'retur penjualan refund pengembalian komplain ganti' },
            @endif
        @endif

        @if ($canAccessInventory)
            // 3. Produk & Persediaan
            { title: 'Katalog Produk & Menu', desc: 'Daftar barang dagangan, harga jual, dan resep produk', category: 'Produk & Stok', route: '{{ route('products.index') }}', icon: 'package', keywords: 'produk barang menu katalog makanan minuman sku' },
            { title: 'Jasa & Layanan', desc: 'Katalog jasa pengerjaan, servis teknis, dan tarif per jam', category: 'Produk & Stok', route: '{{ route('services.index') }}', icon: 'wrench', keywords: 'jasa layanan servis service tarif ongkos kerja' },
            { title: 'Bahan Baku & Resep (BOM)', desc: 'Master bahan mentah, komponen produksi, dan kartu resep', category: 'Produk & Stok', route: '{{ route('materials.index') }}', icon: 'boxes', keywords: 'bahan baku resep bom material racikan formula bumbu' },
            { title: 'Varian & Opsi Tambahan', desc: 'Topping, level pedas, ukuran cup, dan modifikasi pesanan', category: 'Produk & Stok', route: '{{ route('pos.modifiers.index') }}', icon: 'layers', keywords: 'varian modifier topping opsi pilihan ekstra add on' },
            { title: 'Stok Gudang & Saldo', desc: 'Informasi sisa fisik stok produk dan bahan baku di semua gudang', category: 'Produk & Stok', route: '{{ route('inventory.stocks') }}', icon: 'archive', keywords: 'stok gudang saldo inventory fisik sisa kuantitas balance' },
            { title: 'Lokasi Gudang', desc: 'Pengaturan multi-lokasi gudang, toko, outlet, dan cabang', category: 'Produk & Stok', route: '{{ route('warehouse.index') }}', icon: 'warehouse', keywords: 'lokasi gudang cabang outlet toko simpan' },
            { title: 'Opname Stok Fisik', desc: 'Penyesuaian stok berkala dan rekonsiliasi selisih fisik gudang', category: 'Produk & Stok', route: '{{ route('inventory.opnames.index') }}', icon: 'clipboard-check', keywords: 'opname stok fisik cek selisih audit gudang cocok' },
            { title: 'Transfer Stok Gudang', desc: 'Surat jalan perpindahan barang antar cabang atau lokasi gudang', category: 'Produk & Stok', route: '{{ route('inventory.transfers.index') }}', icon: 'arrow-left-right', keywords: 'transfer mutasi antar gudang cabang kirim stok jalan' },
            { title: 'Kategori Produk', desc: 'Pengelompokan jenis dan kategori barang jualan', category: 'Produk & Stok', route: '{{ route('product-categories.index') }}', icon: 'folder-tree', keywords: 'kategori produk kelompok barang jenis' },
            { title: 'Kategori Bahan Baku', desc: 'Pengelompokan jenis bahan mentah dan material produksi', category: 'Produk & Stok', route: '{{ route('material-categories.index') }}', icon: 'folder', keywords: 'kategori bahan material mentah produksi' },
            { title: 'Satuan Ukur & Konversi', desc: 'Master unit satuan (kg, gram, pcs, liter) dan konversi rasio', category: 'Produk & Stok', route: '{{ route('units.index') }}', icon: 'scale', keywords: 'satuan ukur unit konversi kg gram liter pcs dus' },
            { title: 'Impor & Ekspor Excel', desc: 'Impor massal produk, bahan baku, resep, dan stok via Excel', category: 'Produk & Stok', route: '{{ route('import.index') }}', icon: 'file-spreadsheet', keywords: 'import ekspor excel csv download template massal' },
        @endif

        @if ($canAccessPurchasing)
            // 4. Pembelian & Supplier
            { title: 'Pesanan Pembelian (PO)', desc: 'Surat pesanan pembelian pengadaan barang ke supplier pemasok', category: 'Pembelian & Supplier', route: '{{ route('purchase-orders.index') }}', icon: 'truck', keywords: 'po purchase order beli kulak supplier pemasok pengadaan' },
            { title: 'Tagihan Supplier (Bills)', desc: 'Daftar kewajiban utang dagang pembelian dari pemasok', category: 'Pembelian & Supplier', route: '{{ route('purchasing.bills.index') }}', icon: 'receipt', keywords: 'tagihan bill utang supplier invoice pembelian tempo' },
            { title: 'Supplier & Pemasok', desc: 'Direktori kontak supplier, alamat, dan syarat pembayaran', category: 'Pembelian & Supplier', route: '{{ route('suppliers.index') }}', icon: 'building-2', keywords: 'supplier vendor pemasok distributor rekanan kulakan' },
            { title: 'Retur Pembelian', desc: 'Pengembalian barang rusak atau cacat ke pihak supplier', category: 'Pembelian & Supplier', route: '{{ route('purchase.returns.index') }}', icon: 'rotate-ccw', keywords: 'retur beli supplier rusak kembalikan dana potong utang' },
        @endif

        @if ($canAccessMarketing)
            // 5. Pelanggan & Pemasaran
            @if ($canAccessCrm)
                { title: 'Data Pelanggan (CRM)', desc: 'Buku kontak pelanggan, nomor WhatsApp, riwayat transaksi', category: 'Pelanggan & Pemasaran', route: '{{ route('customers.index') }}', icon: 'users', keywords: 'pelanggan customer kontak wa crm pembeli langganan' },
                { title: 'Member & Loyalitas Poin', desc: 'Program keanggotaan member, perolehan poin, dan reward loyalitas', category: 'Pelanggan & Pemasaran', route: '{{ route('crm.members.index') }}', icon: 'award', keywords: 'member loyalitas poin loyalty reward kupon program' },
                { title: 'Voucher Diskon Promosi', desc: 'Kode kupon promosi, diskon persentase, dan voucher potongan harga', category: 'Pelanggan & Pemasaran', route: '{{ route('crm.vouchers.index') }}', icon: 'ticket', keywords: 'voucher diskon promo kupon potongan harga promo' },
            @endif
            @if ($canAccessStorefront)
                { title: 'Pesanan Toko Online', desc: 'Daftar pesanan masuk dari toko online dan status pembayaran', category: 'Pelanggan & Pemasaran', route: '{{ route('storefront.orders.index') }}', icon: 'shopping-bag', keywords: 'toko online etalase katalog web storefront checkout' },
                { title: 'Desain Halaman Toko (Mini-Site)', desc: 'Kustomisasi tema, banner, warna, dan tampilan web toko online', category: 'Pelanggan & Pemasaran', route: '{{ route('landing-page.edit') }}', icon: 'globe', keywords: 'landing page website desain tema etalase toko online mini site web' },
                { title: 'Reservasi & Booking Online', desc: 'Kelola pesanan reservasi meja atau booking layanan pelanggan', category: 'Pelanggan & Pemasaran', route: '{{ route('storefront.reservations.index') }}', icon: 'calendar-check', keywords: 'reservasi booking meja jadwal reservasi janji temu' },
                { title: 'Pengaturan Ongkos Kirim', desc: 'Aturan ongkir kurir toko dan integrasi ekspedisi logistik pengiriman', category: 'Pelanggan & Pemasaran', route: '{{ route('storefront.shipping.index') }}', icon: 'truck', keywords: 'ongkir pengiriman tarif kurir logistik biteship' },
                { title: 'Pengaturan Toko Online & Pembayaran', desc: 'Aturan checkout, metode pembayaran transfer/QRIS, dan jam buka etalase', category: 'Pelanggan & Pemasaran', route: '{{ route('storefront.settings.index') }}', icon: 'store', keywords: 'toko online etalase qris transfer manual rekening checkout pengaturan jam buka' },
            @endif
            @if ($canAccessChannels)
                { title: 'WhatsApp Bisnis Toko', desc: 'Koneksi perangkat WhatsApp dan pengiriman struk belanja otomatis', category: 'Pelanggan & Pemasaran', route: '{{ route('whatsapp.index') }}', icon: 'message-square', keywords: 'whatsapp wa koneksi qr barcode struk auto sender' },
                { title: 'Pesan Siaran WhatsApp (Broadcast)', desc: 'Kirim pesan siaran promo massal langsung ke kontak pelanggan', category: 'Pelanggan & Pemasaran', route: '{{ route('whatsapp.broadcast.index') }}', icon: 'radio', keywords: 'broadcast siaran wa promo massal pesan blast pelanggan' },
                { title: 'Media Sosial Omnichannel', desc: 'Integrasi dan jadwal posting ke Instagram, Facebook, dan TikTok', category: 'Pelanggan & Pemasaran', route: '{{ route('social-media.index') }}', icon: 'share-2', keywords: 'sosmed media sosial instagram tiktok facebook threads posting' },
            @endif
        @endif

        @if ($canAccessFinance)
            // 6. Keuangan & Biaya
            { title: 'Kas & Rekening Bank', desc: 'Pencatatan mutasi kas masuk, kas keluar, dan saldo bank', category: 'Keuangan & Biaya', route: '{{ route('finance.cash-bank.index') }}', icon: 'wallet', keywords: 'kas bank mutasi uang cash flow rekening tunai masuk keluar' },
            { title: 'Pengeluaran Operasional', desc: 'Beban biaya operasional toko, listrik, air, sewa, dan konsumsi', category: 'Keuangan & Biaya', route: '{{ route('finance.expenses.index') }}', icon: 'banknote', keywords: 'pengeluaran expense biaya operasional listrik bon sewa gaji' },
            { title: 'Bagan Akun (Chart of Accounts)', desc: 'Daftar struktur akun buku besar hierarki multi-tier standar SAK EMKM', category: 'Keuangan & Biaya', route: '{{ route('finance.coa.index') }}', icon: 'list-tree', keywords: 'coa bagan akun perkiraan kode akun hierarki aktiva kewajiban ekuitas' },
            { title: 'Buku Jurnal Keuangan', desc: 'Jurnal umum debit-kredit otomatis dari seluruh transaksi usaha', category: 'Keuangan & Biaya', route: '{{ route('finance.journals.index') }}', icon: 'file-text', keywords: 'jurnal akuntansi debit kredit transaksi pembukuan balance' },
            { title: 'Buku Besar Umum (General Ledger)', desc: 'Rincian mutasi kronologis debit-kredit akun dengan audit trail lengkap', category: 'Keuangan & Biaya', route: '{{ route('finance.general-ledger') }}', icon: 'book-open', keywords: 'buku besar general ledger gl akun mutasi rincian saldo running' },
            { title: 'Rekonsiliasi Bank', desc: 'Pencocokan rekening koran bank dengan catatan kas & bank sistem', category: 'Keuangan & Biaya', route: '{{ route('finance.reconciliations.index') }}', icon: 'git-compare', keywords: 'rekonsiliasi bank rekening koran statement matching pencocokan mutasi' },
            { title: 'Daftar Piutang Usaha', desc: 'Daftar tagihan yang belum dibayar oleh pelanggan atau rekanan', category: 'Keuangan & Biaya', route: '{{ route('finance.receivables') }}', icon: 'trending-up', keywords: 'piutang ar customer belum bayar tempo tagihan invoice' },
            { title: 'Daftar Utang Usaha', desc: 'Daftar kewajiban utang jatuh tempo kepada pihak pemasok', category: 'Keuangan & Biaya', route: '{{ route('finance.payables') }}', icon: 'trending-down', keywords: 'utang ap supplier jatuh tempo bayar kewajiban bills' },
            { title: 'Pencairan Dana Penjualan (Settlement)', desc: 'Rekonsiliasi pencairan saldo gateway pembayaran ke rekening bank', category: 'Keuangan & Biaya', route: '{{ route('finance.settlements.index') }}', icon: 'landmark', keywords: 'settlement pencairan dana qris gateway tripay tarik saldo' },
            { title: 'Hitung HPP & Margin Produk', desc: 'Kalkulator biaya pokok produksi akurat berdasarkan formula ABC', category: 'Keuangan & Biaya', route: '{{ route('calculator.index') }}', icon: 'calculator', keywords: 'hpp hitung biaya pokok margin harga modal abc costing' },
            { title: 'Simulator Harga Jual', desc: 'Simulasi target margin laba dan analisis sensitivitas harga', category: 'Keuangan & Biaya', route: '{{ route('simulator.index') }}', icon: 'gauge', keywords: 'simulator harga jual margin simulasi skenario what if' },
            { title: 'Biaya Mesin & Tenaga Kerja', desc: 'Pengaturan tarif kerja per jam dan biaya operasional mesin', category: 'Keuangan & Biaya', route: '{{ route('labor-machines.index') }}', icon: 'cog', keywords: 'tarif mesin tenaga kerja labor upah listrik jam kerja' },
            { title: 'Data Karyawan & Slip Gaji', desc: 'Penggajian bulanan staf, kasbon pinjaman, dan cetak slip gaji', category: 'Keuangan & Biaya', route: '{{ route('hrm.index') }}', icon: 'badge-cent', keywords: 'gaji payroll slip gaji karyawan upah hrm kasbon pinjaman' },
            { title: 'Perhitungan Pajak Karyawan', desc: 'Perhitungan tarif efektif rata-rata (TER) PPh 21 gaji karyawan', category: 'Keuangan & Biaya', route: '{{ route('tax.index') }}', icon: 'scale', keywords: 'pajak pph 21 ter karyawan gaji potongan spt bulanan' },
        @endif

        @if ($canAccessReports)
            // 7. Laporan & Analitik
            { title: 'Pusat Laporan Bisnis', desc: 'Suite analitik dan ringkasan eksekutif seluruh laporan usaha', category: 'Laporan & Analitik', route: '{{ route('reports.index') }}', icon: 'bar-chart-3', keywords: 'laporan reports pusat report analitik suite bisnis lengkap' },
            { title: 'Neraca Posisi Keuangan (Balance Sheet)', desc: 'Laporan posisi keuangan aset, liabilitas, dan ekuitas standar SAK EMKM', category: 'Laporan & Analitik', route: '{{ route('finance.balance-sheet') }}', icon: 'scale', keywords: 'neraca balance sheet posisi keuangan aset pasiva modal sak emkm ekuitas' },
            { title: 'Neraca Saldo (Trial Balance)', desc: 'Daftar saldo debit dan kredit seluruh akun buku besar periode berjalan', category: 'Laporan & Analitik', route: '{{ route('finance.trial-balance') }}', icon: 'table-properties', keywords: 'neraca saldo trial balance debit kredit pembukuan penutupan saldo awal mutasi' },
            { title: 'Laporan Laba Rugi (Profit & Loss)', desc: 'Laporan pendapatan bersih, total beban, dan laba/rugi usaha', category: 'Laporan & Analitik', route: '{{ route('reports.index', ['tab' => 'income_statement']) }}', icon: 'pie-chart', keywords: 'laba rugi income statement profit loss net profit omset beban' },
            { title: 'Laporan Arus Kas (Cash Flow)', desc: 'Laporan pergerakan kas dari operasional, investasi, dan pendanaan', category: 'Laporan & Analitik', route: '{{ route('reports.index', ['tab' => 'cash_flow']) }}', icon: 'line-chart', keywords: 'arus kas cash flow kas masuk kas keluar net cash aliran dana' },
            { title: 'Laporan Penjualan Kasir POS', desc: 'Rincian omset kasir, metode pembayaran, kasir bertugas, dan item terlaris', category: 'Laporan & Analitik', route: '{{ route('pos.reports.index') }}', icon: 'receipt', keywords: 'laporan kasir pos penjualan omset struk rekap kasir' },
            { title: 'Valuasi & Perputaran Stok', desc: 'Nilai aset persediaan gudang dan rasio perputaran barang', category: 'Laporan & Analitik', route: '{{ route('reports.index', ['tab' => 'stock']) }}', icon: 'boxes', keywords: 'valuasi persediaan nilai stok aset turnover gudang perputaran' },
            { title: 'Kartu Mutasi Stok Gudang', desc: 'Riwayat kronologis masuk, keluar, dan penyesuaian stok produk', category: 'Laporan & Analitik', route: '{{ route('inventory.movements') }}', icon: 'activity', keywords: 'mutasi stok kartu stok log masuk keluar pergerakan kartu' },
            { title: 'Analisis Margin & Titik Impas (BEP)', desc: 'Perhitungan Break Even Point unit & rupiah dan analisis profitabilitas', category: 'Laporan & Analitik', route: '{{ route('profitability.index') }}', icon: 'target', keywords: 'bep break even point titik impas margin analisa profit balik modal' },
            { title: 'Kalkulasi & Kepatuhan Pajak', desc: 'Simulasi PPh Final UMKM 0.5%, PPh 21 TER karyawan, dan PPN', category: 'Laporan & Analitik', route: '{{ route('tax.index') }}', icon: 'scale', keywords: 'pajak tax pph final umkm pph 21 ter ppn setoran pajak' },
            { title: 'Analitik Media Sosial', desc: 'Wawasan jangkauan, impresi, dan engagement konten media sosial', category: 'Laporan & Analitik', route: '{{ route('social-media.insights.index') }}', icon: 'trending-up', keywords: 'analitik medsos insight jangkauan impresi engagement statistik' },
        @endif

        @if ($canAccessSettings || $canAccessRoles || $canAccessBilling || \App\Support\Context::isOwner())
            // 8. Pengaturan Usaha
            { title: 'Profil Pengguna', desc: 'Informasi akun pengguna, ganti password, dan kontak WhatsApp', category: 'Pengaturan Usaha', route: '{{ route('profile.edit') }}', icon: 'user', keywords: 'profil user akun password kontak hp whatsapp sandi' },
            @if ($canAccessSettings)
                { title: 'Pengaturan Usaha & Cabang', desc: 'Identitas bisnis, logo usaha, mata uang, dan alamat toko', category: 'Pengaturan Usaha', route: '{{ route('settings.index') }}', icon: 'settings', keywords: 'pengaturan usaha bisnis cabang toko logo nama alamat mata uang' },
            @endif
            @if ($canAccessRoles)
                { title: 'Hak Akses & Peran Staf (RBAC)', desc: 'Kelola peran kasir, admin gudang, finance, dan hak akses staf', category: 'Pengaturan Usaha', route: '{{ route('roles.index') }}', icon: 'shield-check', keywords: 'peran role hak akses staf permissions kasir rbac wewenang' },
            @endif
            @if (\App\Support\Context::hasPermission('approvals.view') || \App\Support\Context::isOwner())
                { title: 'Persetujuan Dokumen (MAR)', desc: 'Pusat otorisasi bertingkat Maker-Approver-Releaser dan tiket pengadaan', category: 'Pengaturan Usaha', route: '{{ route('approvals.inbox') }}', icon: 'stamp', keywords: 'approval persetujuan mar otorisasi po expense biaya maker approver releaser' },
            @endif
            @if (\App\Support\Context::hasPermission('audit_logs.view') || \App\Support\Context::isOwner() || $canAccessSettings)
                { title: 'Jejak Audit & Anti-Fraud', desc: 'Penjelajah log forensik mutasi data sensitif, visual diff, dan deteksi kecurangan', category: 'Pengaturan Usaha', route: '{{ route('settings.audit-logs.index') }}', icon: 'shield-alert', keywords: 'audit log jejak forensik diff riwayat void fraud anti fraud keamanan ip log' },
            @endif
            @if ($canAccessBilling)
                { title: 'Paket Berlangganan & Kuota', desc: 'Status paket langganan aktif, batas pemakaian fitur, dan upgrade', category: 'Pengaturan Usaha', route: '{{ route('billing.limits') }}', icon: 'credit-card', keywords: 'paket langganan kuota billing limit upgrade tagihan perpanjang' },
            @endif
            { title: 'Bantuan & Kontak Dukungan', desc: 'Layanan bantuan teknis dan laporan kendala penggunaan sistem', category: 'Pengaturan Usaha', route: '{{ route('feedback.bugs.index') }}', icon: 'help-circle', keywords: 'bantuan support tiket cs panduan kendala error kontak bug' },
        @endif
    ],
    get filteredItems() {
        let q = this.query.trim().toLowerCase();
        let cat = this.category;
        return this.items.filter(item => {
            let matchCat = (cat === 'all') || (item.category === cat);
            if (!matchCat) return false;
            if (!q) return true;
            return item.title.toLowerCase().includes(q) ||
                   item.desc.toLowerCase().includes(q) ||
                   (item.keywords && item.keywords.toLowerCase().includes(q));
        });
    },
    open() {
        this.isOpen = true;
        this.spotlightOpen = true;
        this.query = '';
        this.category = 'all';
        this.selectedIndex = 0;
        this.$nextTick(() => {
            this.$refs.searchInput?.focus();
            if (window.lucide) window.lucide.createIcons();
        });
    },
    close() {
        this.isOpen = false;
        this.spotlightOpen = false;
    },
    selectNext() {
        if (this.filteredItems.length > 0) {
            this.selectedIndex = (this.selectedIndex + 1) % this.filteredItems.length;
            this.scrollToSelected();
        }
    },
    selectPrev() {
        if (this.filteredItems.length > 0) {
            this.selectedIndex = (this.selectedIndex - 1 + this.filteredItems.length) % this.filteredItems.length;
            this.scrollToSelected();
        }
    },
    executeSelected() {
        if (this.filteredItems.length > 0 && this.filteredItems[this.selectedIndex]) {
            window.location.href = this.filteredItems[this.selectedIndex].route;
        }
    },
    scrollToSelected() {
        this.$nextTick(() => {
            const el = document.getElementById('spotlight-item-' + this.selectedIndex);
            if (el) el.scrollIntoView({ block: 'nearest' });
        });
    }
}"
@keydown.window.prevent.cmd.k="open()"
@keydown.window.prevent.ctrl.k="open()"
@open-spotlight.window="open()">

    <!-- Backdrop Modal (Frosted Glass Apple Blur) -->
    <div x-show="isOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="close()"
        class="fixed inset-0 z-50 bg-black/40 backdrop-blur-md flex items-start justify-center pt-12 sm:pt-20 px-3 sm:px-4"
        style="display: none;">

        <!-- Modal Dialog Box (Apple HIG Bento Architecture) -->
        <div @click.stop
            x-show="isOpen"
            x-transition:enter="transition ease-out duration-200 transform"
            x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150 transform"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
            class="w-full max-w-2xl bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl rounded-[20px] shadow-[0_24px_64px_rgba(0,0,0,0.3)] border border-black/10 dark:border-white/15 overflow-hidden flex flex-col max-h-[82vh] transition-all">

            <!-- Search Input Header Row -->
            <div class="p-3.5 sm:p-4 border-b border-black/5 dark:border-white/10 flex items-center gap-3">
                <div class="w-9 h-9 rounded-[10px] bg-[#007AFF]/10 dark:bg-[#0A84FF]/15 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input x-ref="searchInput"
                    x-model="query"
                    @input="selectedIndex = 0"
                    @keydown.arrow-down.prevent="selectNext()"
                    @keydown.arrow-up.prevent="selectPrev()"
                    @keydown.enter.prevent="executeSelected()"
                    @keydown.esc="close()"
                    type="text"
                    placeholder="Cari modul, laporan, atau fitur... (tekan Esc untuk tutup)"
                    class="flex-1 bg-transparent border-none text-[15px] font-medium text-black dark:text-white placeholder-black/40 dark:placeholder-white/40 focus:outline-none focus:ring-0">
                <button type="button" @click="close()"
                    class="p-1.5 rounded-[7px] text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-all cursor-pointer"
                    title="Tutup (Esc)">
                    <kbd class="px-1.5 py-0.5 rounded-[5px] bg-black/[0.06] dark:bg-white/[0.1] text-[10px] font-semibold text-black/60 dark:text-white/60 font-mono tracking-tight shadow-sm">Esc</kbd>
                </button>
            </div>

            <!-- Category Filter Chips Bar -->
            <div class="px-3.5 sm:px-4 py-2 border-b border-black/5 dark:border-white/10 flex items-center gap-1.5 overflow-x-auto no-scrollbar scroll-smooth">
                <template x-for="cat in categories" :key="cat.id">
                    <button type="button"
                        @click="category = cat.id; selectedIndex = 0; $nextTick(() => { if (window.lucide) window.lucide.createIcons(); })"
                        :class="category === cat.id ? 'bg-[#007AFF] text-white shadow-sm font-semibold' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/65 dark:text-white/65 hover:bg-black/[0.08] dark:hover:bg-white/[0.1]'"
                        class="px-2.5 py-1 rounded-[7px] text-[11px] whitespace-nowrap transition-all shrink-0 cursor-pointer">
                        <span x-text="cat.label"></span>
                    </button>
                </template>
            </div>

            <!-- Results Scrollable List Area -->
            <div class="flex-1 overflow-y-auto p-2 sm:p-3 space-y-1 min-h-[160px] max-h-[50vh] overscroll-contain">
                <!-- Result Item Loop -->
                <template x-for="(item, idx) in filteredItems" :key="item.route">
                    <a :href="item.route"
                        :id="'spotlight-item-' + idx"
                        @mouseenter="selectedIndex = idx"
                        :class="selectedIndex === idx ? 'bg-[#007AFF] text-white shadow-[0_1px_3px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]'"
                        class="flex items-center justify-between p-2.5 rounded-[12px] transition-all cursor-pointer group">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div :class="selectedIndex === idx ? 'bg-white/20 text-white' : 'bg-black/[0.04] dark:bg-white/[0.08] text-[#007AFF] dark:text-[#0A84FF]'"
                                class="w-8 h-8 rounded-[8px] flex items-center justify-center shrink-0 transition-colors">
                                <i :data-lucide="item.icon" class="w-4 h-4"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-[13px] font-semibold truncate leading-tight" x-text="item.title"></span>
                                    <span :class="selectedIndex === idx ? 'bg-white/20 text-white' : 'bg-black/[0.05] dark:bg-white/[0.1] text-black/55 dark:text-white/55'"
                                        class="px-1.5 py-0.5 rounded-[5px] text-[10px] font-medium tracking-wide"
                                        x-text="item.category"></span>
                                </div>
                                <p :class="selectedIndex === idx ? 'text-white/80' : 'text-black/50 dark:text-white/50'"
                                    class="text-[11px] truncate leading-tight mt-0.5"
                                    x-text="item.desc"></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0 pl-2">
                            <span :class="selectedIndex === idx ? 'opacity-100 text-white' : 'opacity-0'"
                                class="text-[11px] font-medium transition-opacity flex items-center gap-1">
                                <span>Buka</span>
                                <kbd class="px-1.5 py-0.5 rounded bg-white/25 text-[10px] font-mono">↵</kbd>
                            </span>
                        </div>
                    </a>
                </template>

                <!-- Empty State (No Results Found) -->
                <div x-show="filteredItems.length === 0"
                    class="py-10 px-4 text-center space-y-2">
                    <div class="w-12 h-12 mx-auto rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center text-black/40 dark:text-white/40">
                        <i data-lucide="search-x" class="w-6 h-6"></i>
                    </div>
                    <div class="text-[14px] font-semibold text-black dark:text-white">Tidak Ada Hasil Ditemukan</div>
                    <p class="text-[12px] text-black/50 dark:text-white/50 max-w-sm mx-auto">
                        Tidak ada modul atau laporan yang cocok dengan kata kunci <span class="font-semibold text-black dark:text-white" x-text="'&quot;' + query + '&quot;'"></span>.
                    </p>
                </div>
            </div>

            <!-- Footer Keyboard Navigation Helper Bar -->
            <div class="p-2.5 sm:px-4 bg-black/[0.02] dark:bg-white/[0.02] border-t border-black/5 dark:border-white/10 flex items-center justify-between text-[11px] text-black/50 dark:text-white/50 select-none">
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1">
                        <kbd class="px-1 py-0.5 rounded bg-black/[0.05] dark:bg-white/[0.1] font-mono text-[10px]">↑</kbd>
                        <kbd class="px-1 py-0.5 rounded bg-black/[0.05] dark:bg-white/[0.1] font-mono text-[10px]">↓</kbd>
                        <span>Pilih</span>
                    </span>
                    <span class="flex items-center gap-1">
                        <kbd class="px-1 py-0.5 rounded bg-black/[0.05] dark:bg-white/[0.1] font-mono text-[10px]">↵</kbd>
                        <span>Buka</span>
                    </span>
                </div>
                <div class="flex items-center gap-1">
                    <kbd class="px-1 py-0.5 rounded bg-black/[0.05] dark:bg-white/[0.1] font-mono text-[10px]">Esc</kbd>
                    <span>Tutup</span>
                </div>
            </div>
        </div>
    </div>
</div>

