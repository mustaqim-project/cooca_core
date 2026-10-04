<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <!-- No-Flash Theme Bootstrap (Eliminates FOUC) -->
    <script>
        (function() {
            try {
                var theme = localStorage.getItem('cooca-theme') || 'light';
                var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (theme === 'dark' || (theme === 'system' && prefersDark)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
                document.documentElement.setAttribute('data-theme', theme);
            } catch (e) {}
        })();
    </script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? 'COOCA AI Digital Company — Virtual Office' }}</title>

    <!-- Google Fonts (Inter + JetBrains Mono) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Inter"', '-apple-system', 'BlinkMacSystemFont', 'system-ui', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#ecfeff',
                            100: '#cffafe',
                            400: '#22d3ee',
                            500: '#06b6d4',
                            600: '#0891b2',
                            900: '#164e63',
                            950: '#083344',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- AppAlert (Centralized Alert & Confirm System) -->
    <script src="{{ asset('js/app-alert.js') }}"></script>

    <!-- Three.js r128 + OrbitControls + Cooca 3D Engine -->
    <script src="{{ asset('js/vendor/three/three.min.js') }}"></script>
    <script>
        if (typeof window.THREE === 'undefined') {
            document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"><\/script>');
        }
    </script>
    <script src="{{ asset('js/vendor/three/OrbitControls.js') }}"></script>
    <script>
        if (typeof window.THREE !== 'undefined' && typeof window.THREE.OrbitControls === 'undefined') {
            document.write('<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"><\/script>');
        }
    </script>
    <script src="{{ asset('js/ai/virtual-office-3d.js') }}"></script>

    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        /* Sleek scrollbars */
        ::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(100, 116, 139, 0.25);
            border-radius: 4px;
        }
        .dark ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.45);
        }
        .dark ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>

<body class="h-full w-full overflow-hidden bg-slate-100 dark:bg-[#0c192c] text-slate-800 dark:text-white flex flex-col antialiased select-none font-sans transition-colors duration-200" x-data="aiOfficeShell()">

    @php
        $biz = \App\Support\Context::business();
        $currUser = \App\Support\Context::user();
        $isExecutive = request()->routeIs('cooca-ai.office.executive') || request()->is('*executive*');
        $isOperations = request()->routeIs('cooca-ai.office.operations') || request()->is('*operations*');
        $isGrowth = request()->routeIs('cooca-ai.office.growth') || request()->is('*growth*');
        $isLobby = !$isExecutive && !$isOperations && !$isGrowth;
    @endphp

    <!-- ============================================================== -->
    <!-- TOP NAVIGATION BAR (Matching Image Reference Perfectly)       -->
    <!-- ============================================================== -->
    <header class="h-16 px-4 bg-white/90 dark:bg-[#0a1526]/95 backdrop-blur-md border-b border-slate-200/80 dark:border-white/10 flex items-center justify-between gap-4 shrink-0 z-30 select-none transition-colors">
        
        <!-- Left: Brand Logo, Platform Name & Subtitle (Exact Match) -->
        <div class="flex items-center gap-3.5 shrink-0">
            <a href="{{ route('dashboard') }}" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-white/5 dark:hover:bg-white/10 border border-slate-200 dark:border-white/10 flex items-center justify-center text-slate-600 dark:text-white/70 hover:text-slate-900 dark:hover:text-white transition" title="Kembali ke Dashboard Utama">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>

            <!-- Logo & Brand -->
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-cyan-500/20 border-2 border-cyan-500 dark:border-cyan-400 flex items-center justify-center text-cyan-600 dark:text-cyan-400 font-black text-sm shadow-md shadow-cyan-500/20">
                    C
                </div>
                <div>
                    <div class="text-sm font-black tracking-tight text-slate-900 dark:text-white leading-none">COOCA AI</div>
                    <div class="text-[9.5px] text-cyan-600 dark:text-cyan-400 font-medium mt-0.5">Your Digital Workforce</div>
                </div>
            </div>

            <!-- Vertical Divider -->
            <div class="hidden md:block h-6 w-px bg-slate-200 dark:bg-white/15 mx-0.5"></div>

            <!-- Virtual Office Title & Subtitle -->
            <div class="hidden md:block">
                <div class="text-sm font-bold text-slate-900 dark:text-white leading-none">Virtual Office</div>
                <div class="text-[9.5px] text-slate-500 dark:text-slate-400 mt-0.5">One Platform. Three Offices. Multiple Specialized Agents.</div>
            </div>
        </div>

        <!-- Center: Office Switcher Navigation Pills -->
        <div class="flex items-center gap-1 p-1 rounded-xl bg-slate-200/70 dark:bg-slate-900/90 border border-slate-300/80 dark:border-white/10 text-xs">
            <!-- Executive Office -->
            <a href="{{ route('cooca-ai.office.executive') }}"
               class="px-3 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5 {{ $isExecutive ? 'bg-white dark:bg-blue-600/40 text-blue-700 dark:text-blue-300 font-bold border border-blue-200 dark:border-blue-500/40 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-white/5 font-medium' }}">
                <i data-lucide="crown" class="w-3.5 h-3.5 text-amber-500 dark:text-amber-400"></i>
                <span>Executive Office</span>
            </a>

            <!-- Operations Office -->
            <a href="{{ route('cooca-ai.office.operations') }}"
               class="px-3 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5 {{ $isOperations ? 'bg-white dark:bg-teal-600/40 text-teal-700 dark:text-teal-300 font-bold border border-teal-200 dark:border-teal-500/40 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-white/5 font-medium' }}">
                <i data-lucide="cpu" class="w-3.5 h-3.5 text-teal-500 dark:text-teal-400"></i>
                <span>Operations Office</span>
            </a>

            <!-- Growth Office -->
            <a href="{{ route('cooca-ai.office.growth') }}"
               class="px-3 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5 {{ $isGrowth ? 'bg-white dark:bg-purple-600/40 text-purple-700 dark:text-purple-300 font-bold border border-purple-200 dark:border-purple-500/40 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-white/5 font-medium' }}">
                <i data-lucide="trending-up" class="w-3.5 h-3.5 text-purple-500 dark:text-purple-400"></i>
                <span>Growth Office</span>
            </a>

            <!-- Lobi Utama -->
            <a href="{{ route('cooca-ai.index') }}"
               class="px-2.5 py-1.5 rounded-lg text-xs transition flex items-center gap-1 {{ $isLobby ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-bold border border-slate-300 dark:border-white/10 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-white/5 font-medium' }}">
                <i data-lucide="building-2" class="w-3.5 h-3.5 text-slate-400"></i>
                <span>Lobi Utama</span>
            </a>
        </div>

        <!-- Right: Status Badges (12+ AI Agents, 3 Offices, Theme Toggle, Tenant User Profile) -->
        <div class="flex items-center gap-2.5 shrink-0">
            <!-- Badge 1: 12+ AI Agents Working together -->
            <div class="hidden xl:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-900/80 border border-slate-200 dark:border-white/10">
                <div class="w-6 h-6 rounded-full bg-blue-500/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <i data-lucide="users" class="w-3.5 h-3.5"></i>
                </div>
                <div class="text-left leading-tight">
                    <div class="text-[11px] font-bold text-slate-900 dark:text-white">12+ AI Agents</div>
                    <div class="text-[9px] text-slate-500 dark:text-slate-400">Working together</div>
                </div>
            </div>

            <!-- Badge 2: 3 Offices Executive | Operations | Growth -->
            <div class="hidden lg:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-900/80 border border-slate-200 dark:border-white/10">
                <div class="w-6 h-6 rounded-full bg-blue-500/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <i data-lucide="building" class="w-3.5 h-3.5"></i>
                </div>
                <div class="text-left leading-tight">
                    <div class="text-[11px] font-bold text-slate-900 dark:text-white">3 Offices</div>
                    <div class="text-[9px] text-slate-500 dark:text-slate-400">Executive | Operations | Growth</div>
                </div>
            </div>

            <!-- Notification Bell -->
            <a href="{{ route('cooca-ai.actions') }}" class="relative w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-900/80 dark:hover:bg-white/10 border border-slate-200 dark:border-white/10 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition" title="Persetujuan Aksi">
                <i data-lucide="bell" class="w-4 h-4"></i>
                <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-rose-500 text-white font-bold text-[9px] flex items-center justify-center">3</span>
            </a>

            <!-- Standard Apple Glass Squircle Theme Switcher -->
            <div x-data="{
                theme: localStorage.getItem('cooca-theme') || 'light',
                themeDropdownOpen: false,
                setTheme(val) {
                    this.theme = val;
                    localStorage.setItem('cooca-theme', val);
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    const isDark = val === 'dark' || (val === 'system' && prefersDark);
                    if (isDark) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                    document.documentElement.setAttribute('data-theme', val);
                    window.dispatchEvent(new CustomEvent('cooca-theme-changed', {
                        detail: { theme: val, isDark: isDark }
                    }));
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
                            window.dispatchEvent(new CustomEvent('cooca-theme-changed', {
                                detail: { theme: 'system', isDark: e.matches }
                            }));
                        }
                    });
                }
            }" class="relative" @click.outside="themeDropdownOpen = false"
                @keydown.esc.window="themeDropdownOpen = false">
                <button type="button" @click="themeDropdownOpen = !themeDropdownOpen"
                    :aria-expanded="themeDropdownOpen ? 'true' : 'false'" aria-haspopup="menu"
                    title="Ganti Tema (Light / Dark / System)"
                    class="h-8 w-8 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-900/80 dark:hover:bg-white/10 border border-slate-200 dark:border-white/10 text-slate-600 dark:text-white/70 hover:text-slate-900 dark:hover:text-white transition-all flex items-center justify-center shrink-0 active:scale-[0.97] cursor-pointer">
                    <span x-show="theme === 'light'">
                        <i data-lucide="sun" class="w-4 h-4 text-amber-500"></i>
                    </span>
                    <span x-show="theme === 'dark'" style="display: none;">
                        <i data-lucide="moon" class="w-4 h-4 text-indigo-400"></i>
                    </span>
                    <span x-show="theme === 'system'" style="display: none;">
                        <i data-lucide="monitor" class="w-4 h-4 text-blue-500"></i>
                    </span>
                </button>

                <!-- Theme Dropdown Menu -->
                <div x-show="themeDropdownOpen" x-transition role="menu"
                    class="absolute right-0 mt-2 w-40 rounded-2xl bg-white/95 dark:bg-[#1a2638]/95 backdrop-blur-xl border border-slate-200 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.15)] p-1 z-50 space-y-0.5 text-xs"
                    style="display: none;">
                    <button type="button" @click="setTheme('light')" role="menuitem"
                        class="w-full px-2.5 py-1.5 rounded-xl flex items-center justify-between gap-2 text-left font-medium transition active:scale-[0.98] cursor-pointer"
                        :class="theme === 'light' ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 font-semibold' : 'text-slate-700 dark:text-slate-300 hover:bg-black/5 dark:hover:bg-white/5'">
                        <div class="flex items-center gap-2">
                            <i data-lucide="sun" class="w-3.5 h-3.5 text-amber-500"></i>
                            <span>Terang (Light)</span>
                        </div>
                        <span x-show="theme === 'light'" class="ml-auto"><i data-lucide="check" class="w-3.5 h-3.5"></i></span>
                    </button>
                    <button type="button" @click="setTheme('dark')" role="menuitem"
                        class="w-full px-2.5 py-1.5 rounded-xl flex items-center justify-between gap-2 text-left font-medium transition active:scale-[0.98] cursor-pointer"
                        :class="theme === 'dark' ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-slate-700 dark:text-slate-300 hover:bg-black/5 dark:hover:bg-white/5'">
                        <div class="flex items-center gap-2">
                            <i data-lucide="moon" class="w-3.5 h-3.5 text-indigo-400"></i>
                            <span>Gelap (Dark)</span>
                        </div>
                        <span x-show="theme === 'dark'" class="ml-auto"><i data-lucide="check" class="w-3.5 h-3.5"></i></span>
                    </button>
                    <button type="button" @click="setTheme('system')" role="menuitem"
                        class="w-full px-2.5 py-1.5 rounded-xl flex items-center justify-between gap-2 text-left font-medium transition active:scale-[0.98] cursor-pointer"
                        :class="theme === 'system' ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400 font-semibold' : 'text-slate-700 dark:text-slate-300 hover:bg-black/5 dark:hover:bg-white/5'">
                        <div class="flex items-center gap-2">
                            <i data-lucide="monitor" class="w-3.5 h-3.5 text-blue-500"></i>
                            <span>Sistem (Auto)</span>
                        </div>
                        <span x-show="theme === 'system'" class="ml-auto"><i data-lucide="check" class="w-3.5 h-3.5"></i></span>
                    </button>
                </div>
            </div>

            <!-- Badge 3: User Profile (Toko Sejahtera / Business Owner) -->
            <div class="px-2.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-900/80 border border-slate-200 dark:border-white/10 flex items-center gap-2 cursor-pointer hover:border-slate-300 dark:hover:border-white/20 transition">
                <div class="w-6 h-6 rounded-full bg-gradient-to-tr from-amber-400 to-rose-500 text-white font-bold text-[10px] flex items-center justify-center ring-1 ring-white/20">
                    {{ strtoupper(substr($biz->name ?? 'T', 0, 1)) }}
                </div>
                <div class="text-left hidden sm:block leading-tight">
                    <div class="font-bold text-slate-900 dark:text-white text-[11px] truncate max-w-[120px]">{{ $biz->name ?? 'Toko Sejahtera' }}</div>
                    <div class="text-[9px] text-slate-500 dark:text-slate-400">Business Owner</div>
                </div>
                <i data-lucide="chevron-down" class="w-3 h-3 text-slate-400 ml-0.5"></i>
            </div>
        </div>
    </header>

    <!-- ============================================================== -->
    <!-- MAIN WORKSPACE SHELL (Left Slim Rail + Center/Right Viewport)   -->
    <!-- ============================================================== -->
    <div class="flex-1 flex overflow-hidden min-h-0 relative">
        
        <!-- Left Slim Icon Rail (ala iPad / macOS app navigation) -->
        <aside class="w-16 sm:w-20 bg-white/90 dark:bg-[#091322] border-r border-slate-200/80 dark:border-white/10 flex flex-col items-center py-4 space-y-4 shrink-0 z-20 transition-colors">
            
            <!-- 1. AI Office (Active Pill) -->
            <a href="{{ route('cooca-ai.index') }}"
               class="w-12 h-12 rounded-2xl flex flex-col items-center justify-center gap-0.5 text-xs transition {{ request()->routeIs('cooca-ai.index') || request()->routeIs('cooca-ai.office.*') ? 'bg-cyan-50 dark:bg-[#1e3557] text-cyan-600 dark:text-cyan-300 ring-2 ring-cyan-500/50 shadow-md shadow-cyan-500/10' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/5' }}"
               title="AI Office Headquarters">
                <i data-lucide="building-2" class="w-5 h-5"></i>
                <span class="text-[9px] font-semibold">Office</span>
            </a>

            <!-- 2. Chat / Tanya AI -->
            <button type="button" @click="window.dispatchEvent(new CustomEvent('open-ai-consultation'))"
                    class="w-12 h-12 rounded-2xl flex flex-col items-center justify-center gap-0.5 text-xs text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/5 transition cursor-pointer"
                    title="Chat / Tanya AI">
                <i data-lucide="message-square" class="w-5 h-5"></i>
                <span class="text-[9px] font-semibold">Chat</span>
            </button>

            <!-- 3. Actions Center with Notification Badge -->
            <a href="{{ route('cooca-ai.actions') }}"
               class="relative w-12 h-12 rounded-2xl flex flex-col items-center justify-center gap-0.5 text-xs transition {{ request()->routeIs('cooca-ai.actions*') ? 'bg-cyan-50 dark:bg-[#1e3557] text-cyan-600 dark:text-cyan-300 ring-2 ring-cyan-500/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/5' }}"
               title="Action Center">
                <i data-lucide="check-square" class="w-5 h-5"></i>
                <span class="text-[9px] font-semibold">Actions</span>
                <span class="absolute top-1 right-1 w-3.5 h-3.5 rounded-full bg-rose-500 text-white font-bold text-[8px] flex items-center justify-center">3</span>
            </a>

            <!-- 4. Work History -->
            <a href="{{ route('cooca-ai.history') }}"
               class="w-12 h-12 rounded-2xl flex flex-col items-center justify-center gap-0.5 text-xs transition {{ request()->routeIs('cooca-ai.history*') ? 'bg-cyan-50 dark:bg-[#1e3557] text-cyan-600 dark:text-cyan-300 ring-2 ring-cyan-500/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/5' }}"
               title="Riwayat Pekerjaan">
                <i data-lucide="history" class="w-5 h-5"></i>
                <span class="text-[9px] font-semibold">History</span>
            </a>

            <!-- 5. Settings / Providers -->
            <a href="{{ route('cooca-ai.providers') }}"
               class="w-12 h-12 rounded-2xl flex flex-col items-center justify-center gap-0.5 text-xs transition {{ request()->routeIs('cooca-ai.providers*') ? 'bg-cyan-50 dark:bg-[#1e3557] text-cyan-600 dark:text-cyan-300 ring-2 ring-cyan-500/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/5' }}"
               title="Pengaturan Provider AI">
                <i data-lucide="settings" class="w-5 h-5"></i>
                <span class="text-[9px] font-semibold">Settings</span>
            </a>

            <!-- Spacer -->
            <div class="flex-1"></div>

            <!-- Exit back to POS / App -->
            @if(\App\Support\Context::hasPermission('pos.terminal'))
            <a href="{{ route('pos.terminal') }}"
               class="w-12 h-12 rounded-2xl flex flex-col items-center justify-center gap-0.5 text-xs text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/10 border border-emerald-500/20 transition"
               title="Buka Kasir POS Terminal">
                <i data-lucide="layout-grid" class="w-5 h-5"></i>
                <span class="text-[8px] font-semibold">POS</span>
            </a>
            @endif
        </aside>

        <!-- Main Viewport Canvas & Right Panel Area -->
        <main class="flex-1 overflow-y-auto min-w-0 min-h-0 relative p-3 sm:p-5 space-y-6 bg-slate-100/60 dark:bg-[#0c192c] transition-colors">
            @yield('content')
        </main>
    </div>

    <!-- Global Consultation Modal Component -->
    @include('app.ai.partials.consultation_modal')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.lucide && typeof window.lucide.createIcons === 'function') {
                window.lucide.createIcons();
            }
        });

        function aiOfficeShell() {
            return {
                openConsultationModal(team = null, agent = null) {
                    window.dispatchEvent(new CustomEvent('open-ai-consultation', {
                        detail: { team: team, agent: agent }
                    }));
                }
            }
        }
    </script>
</body>
</html>
