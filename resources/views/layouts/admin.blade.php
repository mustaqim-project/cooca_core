<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>{{ $title ?? 'Admin Console — Cooca UMKM' }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        admin: {
                            50: '#f5f3ff',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                            900: '#4c1d95',
                            950: '#2e1065',
                        },
                        indigo: {
                            500: '#6366F1',
                            600: '#4F46E5',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js & Lucide Icons -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- AppAlert (Centralized Alert & Confirm System) -->
    <script src="{{ asset('js/app-alert.js') }}"></script>

    <style>
        :root, [data-theme="dark"] {
            --bg: #030712;
            --bg-section: #0F172A;
            --surface: #111827;
            --card: #1E293B;
            --primary: #6366F1;
            --primary-hover: #818CF8;
            --primary-glow: rgba(99,102,241,.35);
            --accent: #22D3EE;
            --accent-glow: rgba(34,211,238,.25);
            --text: #F8FAFC;
            --text-2: #E2E8F0;
            --text-muted: #CBD5E1;
            --border: rgba(255,255,255,.08);
            --glass-bg: rgba(17,24,39,.75);
            --glass-blur: blur(20px);
        }

        body {
            background-color: var(--bg);
            color: var(--text);
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-image:
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.08) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(139, 92, 246, 0.08) 0px, transparent 50%);
            background-attachment: fixed;
        }

        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border: 1px solid var(--border);
        }

        .nav-item-active {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(139, 92, 246, 0.2) 100%);
            border: 1px solid rgba(99, 102, 241, 0.4);
            color: #ffffff;
            box-shadow: 0 4px 20px -4px rgba(99, 102, 241, 0.3);
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('styles')
</head>
<body class="min-h-screen text-slate-100 antialiased" x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen"
         @click="sidebarOpen = false"
         class="fixed inset-0 z-40 bg-black/80 backdrop-blur-sm lg:hidden"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;"></div>

    <!-- Admin Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed top-0 bottom-0 left-0 z-50 w-64 glass-card border-r border-slate-800 transition-transform duration-300 ease-in-out lg:translate-x-0 flex flex-col justify-between">

        <div class="p-5 space-y-6">
            <!-- Brand Logo -->
            <div class="flex items-center justify-between">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/30 group-hover:scale-105 transition-transform">
                        <i data-lucide="shield-check" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <span class="font-extrabold text-sm tracking-tight text-white block">ADMIN PANEL</span>
                        <span class="text-[10px] uppercase font-bold text-indigo-400 tracking-wider">Cooca Platform</span>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Admin Nav Links -->
            <nav class="space-y-1.5 pt-2 text-xs font-semibold">
                <div class="px-3 pb-1 text-[10px] uppercase font-bold text-slate-500 tracking-wider">Sistem Utama</div>

                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.dashboard') ? 'nav-item-active' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i data-lucide="layout-dashboard" class="w-4 h-4 text-indigo-400"></i>
                    <span>Dashboard Statistik</span>
                </a>

                <a href="{{ route('admin.businesses.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.businesses.*') ? 'nav-item-active' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i data-lucide="building-2" class="w-4 h-4 text-blue-400"></i>
                    <span>Kelola Bisnis (Tenant)</span>
                </a>

                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.users.*') ? 'nav-item-active' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i data-lucide="users" class="w-4 h-4 text-cyan-400"></i>
                    <span>Kelola Pengguna</span>
                </a>

                <a href="{{ route('admin.feedback.bugs.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.feedback.*') ? 'nav-item-active' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i data-lucide="messages-square" class="w-4 h-4 text-cyan-400"></i>
                    <span>Bug & Request Fitur</span>
                </a>

                <a href="{{ route('admin.profile.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.profile.*') ? 'nav-item-active' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i data-lucide="key-round" class="w-4 h-4 text-purple-400"></i>
                    <span>Profil & Ganti Sandi</span>
                </a>

                <div class="px-3 pt-4 pb-1 text-[10px] uppercase font-bold text-slate-500 tracking-wider">Billing & Langganan</div>

                @php
                    $pendingSubscriptionsCount = \App\Models\SubscriptionPayment::where('status', 'awaiting_approval')->count();
                @endphp
                <a href="{{ route('admin.subscriptions.index') }}"
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.subscriptions.*') ? 'nav-item-active' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <div class="flex items-center gap-3">
                        <i data-lucide="receipt" class="w-4 h-4 text-emerald-400"></i>
                        <span>Langganan & Bayar</span>
                    </div>
                    @if($pendingSubscriptionsCount > 0)
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black bg-amber-500 text-slate-950 animate-pulse">
                            {{ $pendingSubscriptionsCount }}
                        </span>
                    @endif
                </a>

                <a href="{{ route('admin.ai-tokens.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.ai-tokens.*') ? 'nav-item-active' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i data-lucide="sparkles" class="w-4 h-4 text-purple-400"></i>
                    <span>Monitoring Token AI</span>
                </a>

                <a href="{{ route('admin.whatsapp.index') }}"
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.whatsapp.*') ? 'nav-item-active' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24"><path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z"/></svg>
                        <span>WhatsApp Center</span>
                    </div>
                    <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-[#25D366]/20 text-[#25D366]">Bot</span>
                </a>

                <div class="px-3 pt-4 pb-1 text-[10px] uppercase font-bold text-slate-500 tracking-wider">Konfigurasi & CMS</div>

                <a href="{{ route('admin.posts.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.posts.*') ? 'nav-item-active' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i data-lucide="file-text" class="w-4 h-4 text-indigo-400"></i>
                    <span>CMS Artikel & Edukasi</span>
                </a>

                <a href="{{ route('admin.leads.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.leads.*') ? 'nav-item-active' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i data-lucide="download-cloud" class="w-4 h-4 text-emerald-400"></i>
                    <span>CMS Unduhan & Leads</span>
                </a>

                <a href="{{ route('admin.payment-accounts.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.payment-accounts.*') ? 'nav-item-active' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i data-lucide="wallet" class="w-4 h-4 text-emerald-400"></i>
                    <span>CMS Rekening & Bayar</span>
                </a>

                <a href="{{ route('admin.billing-packages.index', 'subscription') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.billing-packages.*') ? 'nav-item-active' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i data-lucide="layers-3" class="w-4 h-4 text-cyan-400"></i>
                    <span>CMS Paket & Harga</span>
                </a>

                <a href="{{ route('admin.settings.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.settings.*') ? 'nav-item-active' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i data-lucide="sliders" class="w-4 h-4 text-amber-400"></i>
                    <span>Sistem & Integrasi</span>
                </a>

                <a href="{{ route('admin.smtp.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.smtp.*') ? 'nav-item-active' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i data-lucide="mail-cog" class="w-4 h-4 text-cyan-400"></i>
                    <span>CMS SMTP Email</span>
                </a>
            </nav>
        </div>

        <!-- Admin Profile Footer -->
        <div class="p-3 border-t border-slate-800/80 m-3 glass-card rounded-2xl">
            <div class="flex items-center justify-between gap-2">
                <a href="{{ route('admin.profile.index') }}"
                   class="flex items-center gap-2.5 flex-1 min-w-0 p-1 rounded-xl hover:bg-slate-800/50 transition-colors group"
                   title="Kelola Profil & Ganti Kata Sandi">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center text-indigo-300 font-bold text-xs group-hover:border-indigo-400 shrink-0">
                        {{ substr(Auth::guard('admin')->user()->name ?? 'A', 0, 1) }}
                    </div>
                    <div class="truncate flex-1 min-w-0 text-left">
                        <div class="text-xs font-bold text-white truncate group-hover:text-indigo-300 transition-colors flex items-center gap-1">
                            <span class="truncate">{{ Auth::guard('admin')->user()->name ?? 'Admin' }}</span>
                            <i data-lucide="shield" class="w-3 h-3 text-indigo-400 shrink-0"></i>
                        </div>
                        <div class="text-[10px] text-slate-400 font-mono truncate">{{ Auth::guard('admin')->user()->email ?? '' }}</div>
                    </div>
                </a>

                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" title="Logout" class="p-2 rounded-lg text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition-colors shrink-0">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="lg:pl-64 flex flex-col min-h-screen">
        <!-- Top Navbar -->
        <header class="sticky top-0 z-30 h-16 glass-card border-b border-slate-800 px-4 sm:px-8 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                <div>
                    <h1 class="text-sm font-bold text-white tracking-tight">{{ $headerTitle ?? 'Admin Console' }}</h1>
                    <p class="text-[11px] text-slate-400 hidden sm:block">{{ $headerSubtitle ?? 'Pusat Manajemen Sistem Cooca UMKM (cooca.id)' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('admin.profile.index') }}" 
                   class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-700 text-xs font-semibold text-slate-300 hover:text-white transition-all flex items-center gap-1.5"
                   title="Profil & Ganti Kata Sandi">
                    <i data-lucide="key-round" class="w-3.5 h-3.5 text-purple-400"></i>
                    <span class="hidden sm:inline">Ganti Sandi</span>
                </a>
                <a href="{{ route('landing') }}" target="_blank" class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-700 text-xs font-semibold text-slate-300 hover:text-white transition-all flex items-center gap-1.5">
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                    <span class="hidden sm:inline">Lihat Landing Page</span>
                </a>
            </div>
        </header>

        <!-- Flash Messages -->
        <main class="flex-1 p-4 sm:p-8 max-w-7xl w-full mx-auto space-y-6">
            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-950/60 border border-emerald-500/40 text-emerald-300 text-xs flex items-center gap-2.5">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('status'))
                <div class="p-4 rounded-2xl bg-indigo-950/60 border border-indigo-500/40 text-indigo-300 text-xs flex items-center gap-2.5">
                    <i data-lucide="info" class="w-4 h-4 text-indigo-400 shrink-0"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 rounded-2xl bg-red-950/60 border border-red-500/40 text-red-300 text-xs space-y-1">
                    <div class="font-bold flex items-center gap-2">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-red-400"></i>
                        <span>Terjadi Kesalahan:</span>
                    </div>
                    <ul class="list-disc list-inside pl-2 space-y-0.5 text-[11px] text-red-300/90">
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
</body>
</html>
