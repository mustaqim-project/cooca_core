<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth" x-data="{ darkMode: localStorage.getItem('theme') === 'dark' }"
    :class="{ 'dark': darkMode }">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Portal Pelanggan' }} - COOCA UMKM</title>

    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['-apple-system', 'BlinkMacSystemFont', 'Inter', 'SF Pro Text', 'system-ui',
                            'sans-serif'],
                    },
                    colors: {
                        apple: {
                            blue: '#007AFF',
                            green: '#34C759',
                            orange: '#FF9500',
                            red: '#FF3B30',
                            gray: '#8E8E93',
                            bg: '#F5F5F7',
                            darkBg: '#000000',
                            cardLight: '#FFFFFF',
                            cardDark: '#1C1C1E',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Inter', 'SF Pro Text', system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .bento-card {
            background-color: #FFFFFF;
            border: 1px solid rgba(0, 0, 0, 0.07);
            border-radius: 24px;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.04);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .dark .bento-card {
            background-color: #1C1C1E;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 25px -2px rgba(0, 0, 0, 0.4);
        }

        .bento-card-interactive:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px -4px rgba(0, 0, 0, 0.08);
        }

        .dark .bento-card-interactive:hover {
            box-shadow: 0 12px 35px -4px rgba(0, 0, 0, 0.6);
        }
    </style>
</head>

<body
    class="bg-[#F5F5F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] min-h-screen flex flex-col antialiased selection:bg-[#007AFF] selection:text-white"
    x-data="{ mobileMenuOpen: false, toastMessage: null, toastTimeout: null, showToast(msg) { this.toastMessage = msg;
            clearTimeout(this.toastTimeout);
            this.toastTimeout = setTimeout(() => { this.toastMessage = null; }, 3500); } }">

    {{-- Dynamic Island Toast Notification --}}
    <div x-show="toastMessage" x-cloak x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="-translate-y-8 opacity-0 scale-95"
        x-transition:enter-end="translate-y-0 opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="translate-y-0 opacity-100 scale-100"
        x-transition:leave-end="-translate-y-8 opacity-0 scale-95"
        class="fixed top-5 left-1/2 -translate-x-1/2 z-[100] max-w-sm w-[90%] pointer-events-none">
        <div
            class="bg-black/90 dark:bg-white/95 text-white dark:text-black backdrop-blur-2xl px-4 py-2.5 rounded-full shadow-[0_12px_36px_rgba(0,0,0,0.35)] flex items-center gap-3 border border-white/15 dark:border-black/10">
            <div class="w-6 h-6 rounded-full bg-[#007AFF] text-white flex items-center justify-center shrink-0">
                <i data-lucide="check" class="w-3.5 h-3.5"></i>
            </div>
            <span class="text-[13px] font-semibold tracking-tight leading-snug flex-1 truncate"
                x-text="toastMessage"></span>
        </div>
    </div>

    {{-- Header / Navigation Bar --}}
    <header
        class="sticky top-0 z-40 bg-white/80 dark:bg-[#161617]/80 backdrop-blur-xl border-b border-black/[0.06] dark:border-white/[0.08]">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-4">

            {{-- Brand Logo --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('customer.dashboard') }}" class="flex items-center gap-2.5 group">
                    <div
                        class="w-9 h-9 rounded-[12px] bg-[#007AFF] text-white flex items-center justify-center font-black text-[16px] shadow-sm group-hover:scale-105 transition">
                        C
                    </div>
                    <div>
                        <div class="font-bold text-[15px] tracking-tight leading-none text-black dark:text-white">COOCA
                        </div>
                        <span
                            class="text-[10.5px] uppercase tracking-wider text-[#007AFF] font-bold block mt-0.5">Portal
                            Pelanggan</span>
                    </div>
                </a>
            </div>

            {{-- Desktop Navigation Tabs --}}
            @auth('customer')
                <nav
                    class="hidden md:flex items-center gap-1 bg-black/[0.03] dark:bg-white/[0.05] p-1 rounded-full border border-black/[0.04] dark:border-white/[0.06]">
                    <a href="{{ route('customer.dashboard') }}"
                        class="px-4 py-1.5 rounded-full text-[13px] font-semibold transition tracking-tight {{ request()->routeIs('customer.dashboard') ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/65 dark:text-white/65 hover:text-black dark:hover:text-white' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('customer.orders') }}"
                        class="px-4 py-1.5 rounded-full text-[13px] font-semibold transition tracking-tight {{ request()->routeIs('customer.orders*') ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/65 dark:text-white/65 hover:text-black dark:hover:text-white' }}">
                        Riwayat Belanja
                    </a>
                    <a href="{{ route('customer.cart') }}"
                        class="px-4 py-1.5 rounded-full text-[13px] font-semibold transition tracking-tight {{ request()->routeIs('customer.cart') ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/65 dark:text-white/65 hover:text-black dark:hover:text-white' }}">
                        Keranjang
                    </a>
                    <a href="{{ route('customer.profile') }}"
                        class="px-4 py-1.5 rounded-full text-[13px] font-semibold transition tracking-tight {{ request()->routeIs('customer.profile') ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/65 dark:text-white/65 hover:text-black dark:hover:text-white' }}">
                        Profil
                    </a>
                </nav>
            @endauth

            {{-- Right Actions: Profile / Dark Mode / Logout --}}
            <div class="flex items-center gap-2.5">
                {{-- Dark mode toggle --}}
                <button type="button"
                    @click="darkMode = !darkMode; localStorage.setItem('theme', darkMode ? 'dark' : 'light')"
                    class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 text-black/70 dark:text-white/70 flex items-center justify-center transition active:scale-95"
                    aria-label="Toggle mode gelap">
                    <i data-lucide="moon" class="w-4 h-4 hidden dark:block"></i>
                    <i data-lucide="sun" class="w-4 h-4 block dark:hidden"></i>
                </button>

                @auth('customer')
                    {{-- User Profile Pill & Dropdown --}}
                    <div class="relative" x-data="{ userMenuOpen: false }">
                        <button type="button" @click="userMenuOpen = !userMenuOpen" @click.outside="userMenuOpen = false"
                            class="h-9 px-3 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 flex items-center gap-2 text-[12.5px] font-semibold text-black dark:text-white transition active:scale-95">
                            <div
                                class="w-6 h-6 rounded-full bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center font-bold text-[11px]">
                                {{ strtoupper(substr(auth('customer')->user()->name, 0, 1)) }}
                            </div>
                            <span
                                class="max-w-[100px] truncate hidden sm:inline">{{ auth('customer')->user()->name }}</span>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 opacity-60"></i>
                        </button>

                        <div x-show="userMenuOpen" x-cloak x-transition:enter="transition ease-out duration-150 transform"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-100 transform"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-56 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-2 z-50 space-y-1">

                            <div class="p-2.5 border-b border-black/5 dark:border-white/5 space-y-0.5">
                                <div class="font-bold text-[13px] text-black dark:text-white truncate">
                                    {{ auth('customer')->user()->name }}</div>
                                <div class="text-[11px] text-black/50 dark:text-white/50 truncate">
                                    {{ auth('customer')->user()->phone ?: auth('customer')->user()->email }}</div>
                                @if (auth('customer')->user()->membership_tier)
                                    <span
                                        class="inline-block px-2 py-0.5 rounded-full text-[9.5px] font-bold uppercase tracking-wider bg-[#FF9500]/10 text-[#FF9500] mt-1">
                                        Tier {{ auth('customer')->user()->membership_tier }}
                                    </span>
                                @endif
                            </div>

                            <a href="{{ route('customer.dashboard') }}"
                                class="flex items-center gap-2 px-3 py-2 rounded-[12px] text-[12.5px] font-medium text-black/75 dark:text-white/75 hover:bg-black/5 dark:hover:bg-white/5 transition">
                                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                                <span>Dashboard Akun</span>
                            </a>
                            <a href="{{ route('customer.orders') }}"
                                class="flex items-center gap-2 px-3 py-2 rounded-[12px] text-[12.5px] font-medium text-black/75 dark:text-white/75 hover:bg-black/5 dark:hover:bg-white/5 transition">
                                <i data-lucide="package" class="w-4 h-4"></i>
                                <span>Riwayat Belanja</span>
                            </a>
                            <a href="{{ route('customer.cart') }}"
                                class="flex items-center gap-2 px-3 py-2 rounded-[12px] text-[12.5px] font-medium text-black/75 dark:text-white/75 hover:bg-black/5 dark:hover:bg-white/5 transition">
                                <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                                <span>Keranjang Belanja</span>
                            </a>
                            <a href="{{ route('customer.profile') }}"
                                class="flex items-center gap-2 px-3 py-2 rounded-[12px] text-[12.5px] font-medium text-black/75 dark:text-white/75 hover:bg-black/5 dark:hover:bg-white/5 transition">
                                <i data-lucide="user" class="w-4 h-4"></i>
                                <span>Pengaturan Profil</span>
                            </a>

                            <div class="pt-1 border-t border-black/5 dark:border-white/5">
                                <form action="{{ route('customer.logout') }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="w-full flex items-center gap-2 px-3 py-2 rounded-[12px] text-[12.5px] font-semibold text-[#FF3B30] hover:bg-[#FF3B30]/10 transition">
                                        <i data-lucide="log-out" class="w-4 h-4"></i>
                                        <span>Keluar Akun</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('customer.login') }}"
                        class="h-9 px-4 rounded-full bg-[#007AFF] text-white text-[13px] font-semibold flex items-center gap-1.5 shadow-sm hover:opacity-90 active:scale-95 transition">
                        <i data-lucide="log-in" class="w-3.5 h-3.5"></i>
                        <span>Masuk</span>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    {{-- Flash Notifications --}}
    @if (session('success'))
        <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-4">
            <div
                class="p-3.5 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] text-[13px] font-semibold flex items-center gap-2.5">
                <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-4">
            <div
                class="p-3.5 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[#FF3B30] text-[13px] font-semibold flex items-center gap-2.5">
                <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- Main Content Shell --}}
    <main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 py-6 sm:py-8 mb-16 md:mb-6">
        @yield('content')
    </main>

    {{-- Mobile Bottom Tab Bar (iOS 18 Style) --}}
    @auth('customer')
        <nav class="md:hidden fixed bottom-0 inset-x-0 z-50 bg-white/85 dark:bg-[#161617]/85 backdrop-blur-xl border-t border-black/5 dark:border-white/10 px-2 pt-2 pb-[calc(0.5rem+env(safe-area-inset-bottom))]"
            aria-label="Navigasi Pelanggan">
            <div class="grid grid-cols-4 gap-1">
                <a href="{{ route('customer.dashboard') }}"
                    class="text-center text-[10.5px] font-medium tracking-tight py-1 {{ request()->routeIs('customer.dashboard') ? 'text-[#007AFF] font-bold' : 'text-black/55 dark:text-white/55' }}">
                    <i data-lucide="layout-dashboard" class="w-4 h-4 mx-auto mb-0.5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('customer.orders') }}"
                    class="text-center text-[10.5px] font-medium tracking-tight py-1 {{ request()->routeIs('customer.orders*') ? 'text-[#007AFF] font-bold' : 'text-black/55 dark:text-white/55' }}">
                    <i data-lucide="package" class="w-4 h-4 mx-auto mb-0.5"></i>
                    <span>Pesanan</span>
                </a>
                <a href="{{ route('customer.cart') }}"
                    class="text-center text-[10.5px] font-medium tracking-tight py-1 {{ request()->routeIs('customer.cart') ? 'text-[#007AFF] font-bold' : 'text-black/55 dark:text-white/55' }}">
                    <i data-lucide="shopping-bag" class="w-4 h-4 mx-auto mb-0.5"></i>
                    <span>Keranjang</span>
                </a>
                <a href="{{ route('customer.profile') }}"
                    class="text-center text-[10.5px] font-medium tracking-tight py-1 {{ request()->routeIs('customer.profile') ? 'text-[#007AFF] font-bold' : 'text-black/55 dark:text-white/55' }}">
                    <i data-lucide="user" class="w-4 h-4 mx-auto mb-0.5"></i>
                    <span>Profil</span>
                </a>
            </div>
        </nav>
    @endauth

    {{-- Clean Grounding Footer --}}
    <footer
        class="border-t border-black/5 dark:border-white/5 py-6 text-center text-[12px] text-black/45 dark:text-white/45">
        <div class="max-w-6xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
            <p>&copy; {{ date('Y') }} COOCA UMKM. Hak cipta dilindungi undang-undang.</p>
            <p>Platform Operasi Bisnis Terpadu UMKM Indonesia</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>
</body>

</html>
