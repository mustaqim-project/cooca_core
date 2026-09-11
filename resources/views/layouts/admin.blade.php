<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>{{ $title ?? 'Admin Console — Cooca UMKM' }}</title>

    <!-- Theme is applied before paint so the default light surface never flashes dark. -->
    <script>
        (() => {
            const savedTheme = localStorage.getItem('cooca-admin-theme') || 'light';
            const isDark = savedTheme === 'dark' || (savedTheme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', isDark);
            document.documentElement.dataset.theme = savedTheme;
        })();
    </script>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['-apple-system', '"SF Pro Text"', '"SF Pro Display"', 'Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js & Lucide Icons (outline, stroke 1.5) -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- AppAlert (Centralized Alert & Confirm System) -->
    <script src="{{ asset('js/app-alert.js') }}"></script>

    <style>
        /* COOCA UI/UX Design System v2.0 — Apple System Colors */
        :root {
            --color-accent: #007AFF;
            --color-accent-dark: #0A84FF;
            --color-success: #34C759;
            --color-success-dark: #30D158;
            --color-warning: #FF9500;
            --color-warning-dark: #FF9F0A;
            --color-danger: #FF3B30;
            --color-danger-dark: #FF453A;
            --color-ai: #AF52DE;
            --color-ai-dark: #BF5AF2;
            --color-info: #5856D6;
            --color-info-dark: #5E5CE6;
            --color-attention: #FFCC00;
            --color-attention-dark: #FFD60A;
            --color-teal: #30B0C7;
            --color-teal-dark: #40C8E0;
        }

        body {
            font-family: -apple-system, "SF Pro Text", "SF Pro Display", Inter, system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
            letter-spacing: 0;
        }

        html {
            color-scheme: light;
        }

        html.dark {
            color-scheme: dark;
        }

        /* macOS Source List vibrancy sidebar */
        .sidebar-material {
            background: rgba(242, 242, 247, 0.8);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
        }
        .dark .sidebar-material {
            background: rgba(28, 28, 30, 0.8);
        }

        /* Toolbar material */
        .toolbar-material {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
        }
        .dark .toolbar-material {
            background: rgba(28, 28, 30, 0.75);
        }

        /* Thick material for sheets/alerts */
        .sheet-material {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .dark .sheet-material {
            background: rgba(44, 44, 46, 0.95);
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('styles')
</head>
<body class="min-h-screen bg-[#F2F2F7] dark:bg-[#1E1E1E] text-black dark:text-white antialiased" x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen"
         @click="sidebarOpen = false"
         class="fixed inset-0 z-40 bg-black/30 backdrop-blur-[2px] lg:hidden"
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;"></div>

    <!-- Admin Sidebar (macOS Source List vibrancy) -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed top-0 bottom-0 left-0 z-50 w-64 sidebar-material border-r border-black/5 dark:border-white/5 transition-transform duration-300 ease-out lg:translate-x-0 flex flex-col">

        <div class="flex-1 overflow-y-auto px-3 pt-4 pb-4">
            <!-- Brand -->
            <div class="flex items-center justify-between px-2 pb-4">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 group">
                    <div class="w-8 h-8 rounded-[8px] bg-[#007AFF] flex items-center justify-center shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                        <i data-lucide="shield-check" class="w-4.5 h-4.5 text-white" stroke-width="1.5"></i>
                    </div>
                    <div class="leading-tight">
                        <span class="font-bold text-[13px] tracking-tight text-black dark:text-white block">Cooca Admin</span>
                        <span class="text-[10px] font-medium text-black/40 dark:text-white/40">Platform operations</span>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-[6px] text-black/40 dark:text-white/40 hover:bg-black/5 dark:hover:bg-white/10">
                    <i data-lucide="x" class="w-4 h-4" stroke-width="1.5"></i>
                </button>
            </div>

            <!-- Nav -->
            <nav class="space-y-0.5 text-[13px] font-medium">
                <div class="px-2.5 pt-3 pb-1.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Ringkasan</div>

                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[8px] transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-[#007AFF] text-white shadow-[0_1px_3px_rgba(0,122,255,0.3)]' : 'text-black/65 dark:text-white/65 hover:bg-black/5 dark:hover:bg-white/10' }}">
                    <i data-lucide="layout-dashboard" class="w-4 h-4" stroke-width="1.5"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('admin.businesses.index') }}"
                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[8px] transition-colors {{ request()->routeIs('admin.businesses.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_3px_rgba(0,122,255,0.3)]' : 'text-black/65 dark:text-white/65 hover:bg-black/5 dark:hover:bg-white/10' }}">
                    <i data-lucide="building-2" class="w-4 h-4" stroke-width="1.5"></i>
                    <span>Bisnis</span>
                </a>

                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[8px] transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_3px_rgba(0,122,255,0.3)]' : 'text-black/65 dark:text-white/65 hover:bg-black/5 dark:hover:bg-white/10' }}">
                    <i data-lucide="users" class="w-4 h-4" stroke-width="1.5"></i>
                    <span>Pengguna</span>
                </a>

                <a href="{{ route('admin.feedback.bugs.index') }}"
                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[8px] transition-colors {{ request()->routeIs('admin.feedback.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_3px_rgba(0,122,255,0.3)]' : 'text-black/65 dark:text-white/65 hover:bg-black/5 dark:hover:bg-white/10' }}">
                    <i data-lucide="messages-square" class="w-4 h-4" stroke-width="1.5"></i>
                    <span>Feedback</span>
                </a>

                <div class="px-2.5 pt-4 pb-1.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Operasional platform</div>

                @php
                    $pendingSubscriptionsCount = \App\Models\SubscriptionPayment::where('status', 'awaiting_approval')->count();
                @endphp

                <div class="px-2.5 pt-4 pb-1.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Monetisasi & integrasi</div>
                <a href="{{ route('admin.subscriptions.index') }}"
                   class="flex items-center justify-between px-2.5 py-1.5 rounded-[8px] transition-colors {{ request()->routeIs('admin.subscriptions.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_3px_rgba(0,122,255,0.3)]' : 'text-black/65 dark:text-white/65 hover:bg-black/5 dark:hover:bg-white/10' }}">
                    <div class="flex items-center gap-2.5">
                        <i data-lucide="receipt" class="w-4 h-4" stroke-width="1.5"></i>
                        <span>Langganan & pembayaran</span>
                    </div>
                    @if($pendingSubscriptionsCount > 0)
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500]/15 text-[#FF9500] dark:text-[#FF9F0A]">
                            {{ $pendingSubscriptionsCount }}
                        </span>
                    @endif
                </a>

                <a href="{{ route('admin.ai-tokens.index') }}"
                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[8px] transition-colors {{ request()->routeIs('admin.ai-tokens.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_3px_rgba(0,122,255,0.3)]' : 'text-black/65 dark:text-white/65 hover:bg-black/5 dark:hover:bg-white/10' }}">
                    <i data-lucide="sparkles" class="w-4 h-4" stroke-width="1.5"></i>
                    <span>AI usage</span>
                </a>

                <a href="{{ route('admin.whatsapp.index') }}"
                   class="flex items-center justify-between px-2.5 py-1.5 rounded-[8px] transition-colors {{ request()->routeIs('admin.whatsapp.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_3px_rgba(0,122,255,0.3)]' : 'text-black/65 dark:text-white/65 hover:bg-black/5 dark:hover:bg-white/10' }}">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24"><path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z"/></svg>
                        <span>WhatsApp</span>
                    </div>
                </a>

                <div class="px-2.5 pt-4 pb-1.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Konten & sistem</div>

                <a href="{{ route('admin.posts.index') }}"
                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[8px] transition-colors {{ request()->routeIs('admin.posts.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_3px_rgba(0,122,255,0.3)]' : 'text-black/65 dark:text-white/65 hover:bg-black/5 dark:hover:bg-white/10' }}">
                    <i data-lucide="file-text" class="w-4 h-4" stroke-width="1.5"></i>
                    <span>Artikel & edukasi</span>
                </a>

                <a href="{{ route('admin.leads.index') }}"
                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[8px] transition-colors {{ request()->routeIs('admin.leads.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_3px_rgba(0,122,255,0.3)]' : 'text-black/65 dark:text-white/65 hover:bg-black/5 dark:hover:bg-white/10' }}">
                    <i data-lucide="users-round" class="w-4 h-4" stroke-width="1.5"></i>
                    <span>Leads</span>
                </a>

                <a href="{{ route('admin.payment-accounts.index') }}"
                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[8px] transition-colors {{ request()->routeIs('admin.payment-accounts.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_3px_rgba(0,122,255,0.3)]' : 'text-black/65 dark:text-white/65 hover:bg-black/5 dark:hover:bg-white/10' }}">
                    <i data-lucide="credit-card" class="w-4 h-4" stroke-width="1.5"></i>
                    <span>Payment accounts</span>
                </a>

                <a href="{{ route('admin.billing-packages.index') }}"
                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[8px] transition-colors {{ request()->routeIs('admin.billing-packages.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_3px_rgba(0,122,255,0.3)]' : 'text-black/65 dark:text-white/65 hover:bg-black/5 dark:hover:bg-white/10' }}">
                    <i data-lucide="layers-3" class="w-4 h-4" stroke-width="1.5"></i>
                    <span>Paket & harga</span>
                </a>

                <a href="{{ route('admin.settings.index') }}"
                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[8px] transition-colors {{ request()->routeIs('admin.settings.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_3px_rgba(0,122,255,0.3)]' : 'text-black/65 dark:text-white/65 hover:bg-black/5 dark:hover:bg-white/10' }}">
                    <i data-lucide="sliders" class="w-4 h-4" stroke-width="1.5"></i>
                    <span>Settings</span>
                </a>

                <a href="{{ route('admin.smtp.index') }}"
                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[8px] transition-colors {{ request()->routeIs('admin.smtp.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_3px_rgba(0,122,255,0.3)]' : 'text-black/65 dark:text-white/65 hover:bg-black/5 dark:hover:bg-white/10' }}">
                    <i data-lucide="mail-cog" class="w-4 h-4" stroke-width="1.5"></i>
                    <span>SMTP email</span>
                </a>
            </nav>
        </div>

        <!-- Admin Profile Footer -->
        <div class="p-3">
            <div class="flex items-center justify-between gap-2 p-2 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06]">
                <a href="{{ route('admin.profile.index') }}"
                   class="flex items-center gap-2.5 flex-1 min-w-0 rounded-[8px] hover:bg-black/5 dark:hover:bg-white/10 transition-colors group"
                   title="Kelola Profil & Ganti Kata Sandi">
                    <div class="w-8 h-8 rounded-full bg-[#007AFF]/10 border border-[#007AFF]/20 flex items-center justify-center text-[#007AFF] dark:text-[#0A84FF] font-bold text-xs shrink-0">
                        {{ substr(Auth::guard('admin')->user()->name ?? 'A', 0, 1) }}
                    </div>
                    <div class="truncate flex-1 min-w-0 text-left">
                        <div class="text-[13px] font-semibold text-black dark:text-white truncate flex items-center gap-1">
                            <span class="truncate">{{ Auth::guard('admin')->user()->name ?? 'Admin' }}</span>
                        </div>
                        <div class="text-[11px] text-black/45 dark:text-white/45 truncate">{{ Auth::guard('admin')->user()->email ?? '' }}</div>
                    </div>
                </a>

                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" title="Logout" class="p-2 rounded-[8px] text-black/40 dark:text-white/40 hover:text-[#FF3B30] hover:bg-[#FF3B30]/10 transition-colors shrink-0">
                        <i data-lucide="log-out" class="w-4 h-4" stroke-width="1.5"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="lg:pl-64 flex flex-col min-h-screen">
        <!-- Toolbar (macOS material) -->
        <header class="sticky top-0 z-30 toolbar-material border-b border-black/5 dark:border-white/10 px-4 sm:px-8 min-h-14 py-2 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <button @click="sidebarOpen = true" class="lg:hidden min-w-11 min-h-11 -ml-2 rounded-[10px] text-black/50 dark:text-white/50 hover:bg-black/5 dark:hover:bg-white/10 active:scale-[0.97] transition-all flex items-center justify-center" aria-label="Buka navigasi">
                    <i data-lucide="menu" class="w-5 h-5" stroke-width="1.5"></i>
                </button>
                <div class="min-w-0">
                    <p class="text-[11px] font-medium text-black/40 dark:text-white/40 truncate mb-0.5">Cooca Admin / Platform operations</p>
                    <h1 class="text-[17px] sm:text-[20px] font-semibold text-black dark:text-white tracking-tight truncate">{{ $headerTitle ?? 'Admin Console' }}</h1>
                    <p class="text-[12px] text-black/50 dark:text-white/50 truncate hidden md:block">{{ $headerSubtitle ?? 'Pusat Manajemen Sistem Cooca UMKM (cooca.id)' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                <!-- Theme control: light is the explicit default, dark and system remain available. -->
                <div x-data="{
                    theme: localStorage.getItem('cooca-admin-theme') || 'light',
                    open: false,
                    apply(value) {
                        this.theme = value;
                        localStorage.setItem('cooca-admin-theme', value);
                        const dark = value === 'dark' || (value === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                        document.documentElement.classList.toggle('dark', dark);
                        document.documentElement.dataset.theme = value;
                        this.open = false;
                    }
                }" class="relative" @click.outside="open = false">
                    <button type="button" @click="open = !open" class="min-w-11 min-h-11 sm:min-w-9 sm:min-h-9 px-2 rounded-[10px] text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/10 active:scale-[0.97] transition-all flex items-center justify-center gap-1.5" aria-label="Pilih tema" title="Pilih tema">
                        <i x-show="theme === 'light'" data-lucide="sun" class="w-4 h-4 text-[#FF9500]"></i>
                        <i x-show="theme === 'dark'" data-lucide="moon" class="w-4 h-4 text-[#5856D6]" style="display: none;"></i>
                        <i x-show="theme === 'system'" data-lucide="monitor" class="w-4 h-4 text-[#007AFF]" style="display: none;"></i>
                        <span class="hidden sm:inline text-[12px] font-medium" x-text="theme === 'dark' ? 'Gelap' : (theme === 'system' ? 'Sistem' : 'Terang')"></span>
                        <i data-lucide="chevron-down" class="hidden sm:block w-3.5 h-3.5 text-black/35 dark:text-white/35"></i>
                    </button>
                    <div x-show="open" x-transition class="absolute right-0 top-full mt-2 w-36 rounded-[12px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_8px_24px_rgba(0,0,0,0.12)] p-1 z-50" style="display: none;">
                        <button type="button" @click="apply('light')" class="w-full min-h-10 px-2.5 rounded-[8px] flex items-center gap-2 text-left text-[13px] text-black/75 dark:text-white/75 hover:bg-black/5 dark:hover:bg-white/10"><i data-lucide="sun" class="w-4 h-4 text-[#FF9500]"></i><span>Terang</span></button>
                        <button type="button" @click="apply('dark')" class="w-full min-h-10 px-2.5 rounded-[8px] flex items-center gap-2 text-left text-[13px] text-black/75 dark:text-white/75 hover:bg-black/5 dark:hover:bg-white/10"><i data-lucide="moon" class="w-4 h-4 text-[#5856D6]"></i><span>Gelap</span></button>
                        <button type="button" @click="apply('system')" class="w-full min-h-10 px-2.5 rounded-[8px] flex items-center gap-2 text-left text-[13px] text-black/75 dark:text-white/75 hover:bg-black/5 dark:hover:bg-white/10"><i data-lucide="monitor" class="w-4 h-4 text-[#007AFF]"></i><span>Sistem</span></button>
                    </div>
                </div>

                <div class="h-6 w-px bg-black/10 dark:bg-white/10 hidden sm:block"></div>
                <a href="{{ route('admin.profile.index') }}"
                   class="min-h-11 sm:min-h-9 px-2.5 sm:px-3 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/10 active:scale-[0.97] transition-all flex items-center gap-1.5"
                   title="Profil & Ganti Kata Sandi">
                    <i data-lucide="key-round" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]" stroke-width="1.5"></i>
                    <span class="hidden md:inline">Profil admin</span>
                </a>
                <a href="{{ route('landing') }}" target="_blank" class="min-w-11 min-h-11 sm:min-w-9 sm:min-h-9 px-2.5 sm:px-3 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/10 active:scale-[0.97] transition-all flex items-center gap-1.5" title="Buka landing page">
                    <i data-lucide="external-link" class="w-4 h-4" stroke-width="1.5"></i>
                    <span class="hidden lg:inline">Landing page</span>
                </a>
            </div>
        </header>

        <!-- Flash Messages & Content -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-[1360px] w-full mx-auto space-y-6">
            @if(session('success'))
                <div class="flex items-center gap-2.5 rounded-[14px] px-4 py-3 bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] text-[13px] font-medium border border-[#34C759]/20">
                    <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0" stroke-width="1.5"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('status'))
                <div class="flex items-center gap-2.5 rounded-[14px] px-4 py-3 bg-[#5856D6]/10 text-[#413FA6] dark:text-[#5E5CE6] text-[13px] font-medium border border-[#5856D6]/20">
                    <i data-lucide="info" class="w-4 h-4 shrink-0" stroke-width="1.5"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="rounded-[14px] px-4 py-3 bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[13px] space-y-1">
                    <div class="font-semibold flex items-center gap-2 text-[#C41E17] dark:text-[#FF453A]">
                        <i data-lucide="alert-triangle" class="w-4 h-4" stroke-width="1.5"></i>
                        <span>Terjadi Kesalahan:</span>
                    </div>
                    <ul class="list-disc list-inside pl-2 space-y-0.5 text-[12px] text-[#C41E17]/90 dark:text-[#FF453A]/90">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();

            @if (session('success'))
                AppAlert.success(@json(session('success')));
            @endif
            @if (session('status'))
                AppAlert.info(@json(session('status')));
            @endif
            @if (session('error'))
                AppAlert.error(@json(session('error')));
            @endif
            @if (session('warning'))
                AppAlert.warning(@json(session('warning')));
            @endif
        });
    </script>

    @stack('scripts')
</body>
</html>
