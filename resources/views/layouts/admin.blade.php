<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light" class="overflow-x-hidden">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>{{ $title ?? 'Admin Console - Cooca' }}</title>

    <!-- Theme is applied before paint so the default light surface never flashes dark. -->
    <script>
        (() => {
            try {
                const savedTheme = localStorage.getItem('cooca-admin-theme') || localStorage.getItem('cooca-theme') ||
                    'light';
                const isDark = savedTheme === 'dark' || (savedTheme === 'system' && window.matchMedia(
                    '(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', isDark);
                document.documentElement.dataset.theme = savedTheme;

                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                    const currentTheme = localStorage.getItem('cooca-admin-theme') || localStorage.getItem(
                        'cooca-theme') || 'light';
                    if (currentTheme === 'system') {
                        document.documentElement.classList.toggle('dark', e.matches);
                    }
                });
            } catch (e) {}
        })();
    </script>

    <!-- Google Fonts Preconnect & Inter / JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap"
        rel="stylesheet">

    <!-- Tailwind CSS CDN with Apple System Design Tokens -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['-apple-system', 'BlinkMacSystemFont', '"SF Pro Text"', '"SF Pro Display"', 'Inter',
                            'system-ui', 'sans-serif'
                        ],
                        mono: ['"JetBrains Mono"', 'Menlo', 'Monaco', 'Courier New', 'monospace'],
                    },
                    spacing: {
                        '4.5': '1.125rem',
                    },
                    colors: {
                        apple: {
                            blue: '#007AFF',
                            'blue-dark': '#0A84FF',
                            green: '#34C759',
                            'green-dark': '#30D158',
                            orange: '#FF9500',
                            'orange-dark': '#FF9F0A',
                            red: '#FF3B30',
                            'red-dark': '#FF453A',
                            purple: '#AF52DE',
                            'purple-dark': '#BF5AF2',
                            teal: '#30B0C7',
                            'teal-dark': '#40C8E0',
                            indigo: '#5856D6',
                            'indigo-dark': '#5E5CE6',
                            gray: '#8E8E93',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js & Lucide Icons -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- SweetAlert2 (Modal Dialogs & Apple HIG Theme) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Chart.js (Apple System Visuals) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- AppAlert (Centralized Alert & Confirm System) -->
    <script src="{{ asset('js/app-alert.js') }}"></script>

    <style>
        /* COOCA UI/UX Design System v2.0 - Apple System Colors & Vibrancy */
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
            --color-teal: #30B0C7;
            --color-teal-dark: #40C8E0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", Inter, system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
            letter-spacing: -0.01em;
        }

        html {
            color-scheme: light;
        }

        html.dark {
            color-scheme: dark;
        }

        /* macOS Source List vibrancy sidebar */
        .sidebar-material {
            background: rgba(242, 242, 247, 0.85);
            backdrop-filter: blur(24px) saturate(190%);
            -webkit-backdrop-filter: blur(24px) saturate(190%);
        }

        .dark .sidebar-material {
            background: rgba(28, 28, 30, 0.85);
        }

        /* Toolbar material */
        .toolbar-material {
            background: rgba(242, 242, 247, 0.78);
            backdrop-filter: blur(20px) saturate(190%);
            -webkit-backdrop-filter: blur(20px) saturate(190%);
        }

        .dark .toolbar-material {
            background: rgba(22, 22, 24, 0.82);
        }

        /* Thick material for sheets/modals */
        .sheet-material {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
        }

        .dark .sheet-material {
            background: rgba(36, 36, 38, 0.96);
        }

        /* Mobile anti-auto-zoom requirement (font min 16px on mobile inputs) */
        @media (max-width: 640px) {

            input,
            select,
            textarea {
                font-size: 16px !important;
            }
        }

        /* Apple Alert Dialog Styling for SweetAlert2 */
        .swal2-popup {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px) saturate(180%) !important;
            -webkit-backdrop-filter: blur(20px) saturate(180%) !important;
            border: 1px solid rgba(0, 0, 0, 0.08) !important;
            border-radius: 18px !important;
            color: #000000 !important;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2) !important;
            width: 320px !important;
            padding: 1.25rem !important;
        }

        .dark .swal2-popup {
            background: rgba(44, 44, 46, 0.95) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: #FFFFFF !important;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5) !important;
        }

        [x-cloak] {
            display: none !important;
        }

        /* Sleek Apple-style scrollbar for macOS Source List sidebar */
        .sidebar-scroll {
            scrollbar-width: thin;
            scrollbar-color: rgba(0, 0, 0, 0.15) transparent;
        }

        .dark .sidebar-scroll {
            scrollbar-color: rgba(255, 255, 255, 0.18) transparent;
        }

        .sidebar-scroll::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.15);
            border-radius: 9999px;
        }

        .dark .sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.18);
        }

        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.25);
        }

        /* Utility for Apple HIG horizontal swipe tabs without scrollbar blowout */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    @stack('styles')
</head>

@php
    $pendingSubscriptionsCount = \App\Models\SubscriptionPayment::where('status', 'awaiting_approval')->count();
    $pendingRecoveriesCount = \App\Models\AccountRecoveryRequest::where(
        'status',
        \App\Models\AccountRecoveryRequest::STATUS_PENDING,
    )->count();
@endphp

<body class="min-h-screen bg-[#F4F4F7] dark:bg-[#161618] text-black dark:text-white antialiased flex flex-col overflow-x-hidden"
    x-data="{
        sidebarOpen: false,
        quickActionOpen: false,
        spotlightOpen: false,
        searchQuery: '',
        modules: [
            { title: 'Dashboard Beranda', desc: 'Ringkasan & metrik finansial platform', url: '{{ route('admin.dashboard') }}', icon: 'layout-dashboard', cat: 'Ringkasan' },
            { title: 'Bisnis (Tenants)', desc: 'Daftar workspace UMKM & status akun', url: '{{ route('admin.businesses.index') }}', icon: 'building-2', cat: 'Ringkasan' },
            { title: 'Basis Pengguna', desc: 'Akun terdaftar, owner & tim kasir', url: '{{ route('admin.users.index') }}', icon: 'users', cat: 'Ringkasan' },
            { title: 'Feedback & Bug', desc: 'Laporan bug & ide fitur dari tenant', url: '{{ route('admin.feedback.bugs.index') }}', icon: 'messages-square', cat: 'Ringkasan' },
            { title: 'Pemulihan Akun', desc: 'Verifikasi identitas & persetujuan reset', url: '{{ route('admin.account-recoveries.index') }}', icon: 'shield-alert', cat: 'Operasional' },
            { title: 'Langganan & Billing', desc: 'Approval bukti bayar paket Core', url: '{{ route('admin.subscriptions.index') }}', icon: 'receipt', cat: 'Monetisasi' },
            { title: 'Paket & Harga', desc: 'Katalog paket Core & kuota token', url: '{{ route('admin.billing-packages.index') }}', icon: 'layers-3', cat: 'Monetisasi' },
            { title: 'Rekening Bank', desc: 'CMS rekening pembayaran resmi Cooca', url: '{{ route('admin.payment-accounts.index') }}', icon: 'credit-card', cat: 'Monetisasi' },
            { title: 'Monitoring Token AI', desc: 'Pantau konsumsi Gemini 2.5 Flash', url: '{{ route('admin.ai-tokens.index') }}', icon: 'sparkles', cat: 'Monetisasi' },
            { title: 'WhatsApp Gateway', desc: 'Status dual gateway OTP & notifikasi', url: '{{ route('admin.whatsapp.index') }}', icon: 'message-circle', cat: 'Operasional' },
            { title: 'Database Leads', desc: 'Database prospek & kontak calon tenant', url: '{{ route('admin.leads.index') }}', icon: 'users-round', cat: 'Konten & Marketing' },
            { title: 'Template Excel', desc: 'Unduhan berkas spreadsheet master', url: '{{ route('admin.templates.index') }}', icon: 'file-spreadsheet', cat: 'Konten & Marketing' },
            { title: 'Artikel & Edukasi', desc: 'CMS blog bisnis & artikel UMKM', url: '{{ route('admin.posts.index') }}', icon: 'file-text', cat: 'Konten & Marketing' },
            { title: 'Pengaturan Sistem', desc: 'Google Cloud OAuth, server SMTP & parameter platform', url: '{{ route('admin.settings.index') }}', icon: 'sliders', cat: 'Konfigurasi' },
            { title: 'Log Error & Diagnostik', desc: 'Pemantauan runtime exception & file log', url: '{{ route('admin.error-logs.index') }}', icon: 'terminal', cat: 'Konfigurasi' },
            { title: 'Profil Administrator', desc: 'Kelola identitas & ubah kata sandi', url: '{{ route('admin.profile.index') }}', icon: 'key-round', cat: 'Konfigurasi' }
        ],
        get filteredModules() {
            if (!this.searchQuery.trim()) return this.modules;
            const q = this.searchQuery.toLowerCase();
            return this.modules.filter(m => m.title.toLowerCase().includes(q) || m.desc.toLowerCase().includes(q) || m.cat.toLowerCase().includes(q));
        },
        openSpotlight() {
            this.spotlightOpen = true;
            this.searchQuery = '';
            this.$nextTick(() => {
                document.getElementById('admin-spotlight-input')?.focus();
                lucide.createIcons();
            });
        }
    }" @keydown.window.cmd.k.prevent="openSpotlight()"
    @keydown.window.ctrl.k.prevent="openSpotlight()"
    @keydown.escape.window="spotlightOpen = false; quickActionOpen = false">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false"
        class="fixed inset-0 z-40 bg-black/35 backdrop-blur-[3px] lg:hidden"
        x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;"></div>

    <!-- Admin Sidebar (macOS Source List vibrancy) -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed top-0 bottom-0 left-0 z-50 w-72 sidebar-material border-r border-black/[0.06] dark:border-white/[0.08] transition-transform duration-300 ease-out lg:translate-x-0 flex flex-col shadow-sm">

        <div class="flex-1 overflow-y-auto overflow-x-hidden sidebar-scroll px-3.5 pt-4 pb-4">
            <!-- Brand Banner -->
            <div
                class="flex items-center justify-between px-2 pb-4 border-b border-black/[0.04] dark:border-white/[0.06]">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group min-w-0">
                    <div
                        class="w-9 h-9 rounded-[11px] bg-gradient-to-tr from-[#007AFF] to-[#5856D6] flex items-center justify-center shadow-md shadow-[#007AFF]/25 transition-transform group-hover:scale-105 shrink-0">
                        <i data-lucide="shield-check" class="w-5 h-5 text-white" stroke-width="2"></i>
                    </div>
                    <div class="leading-tight min-w-0">
                        <span
                            class="font-extrabold text-[14px] tracking-tight text-black dark:text-white truncate block">Cooca Admin</span>
                        <span class="text-[11px] font-medium text-black/45 dark:text-white/45 truncate block">Platform Operations</span>
                    </div>
                </a>
                <button @click="sidebarOpen = false"
                    class="lg:hidden p-2 rounded-[10px] text-black/40 dark:text-white/40 hover:bg-black/5 dark:hover:bg-white/10 transition-colors shrink-0"
                    aria-label="Tutup navigasi">
                    <i data-lucide="x" class="w-4.5 h-4.5" stroke-width="2"></i>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="space-y-1 text-[13.5px] font-medium pt-3">
                <div
                    class="px-3 pt-2 pb-1 text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">
                    Ringkasan Utama
                </div>

                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-2.5 px-3 h-10 rounded-[12px] transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#3C3C43]/80 dark:text-[#EBEBF5]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="layout-dashboard" class="w-4.5 h-4.5 shrink-0" stroke-width="1.8"></i>
                    <span class="whitespace-nowrap truncate min-w-0 flex-1">Dashboard</span>
                </a>

                <a href="{{ route('admin.businesses.index') }}"
                    class="flex items-center gap-2.5 px-3 h-10 rounded-[12px] transition-all {{ request()->routeIs('admin.businesses.*') ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#3C3C43]/80 dark:text-[#EBEBF5]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="building-2" class="w-4.5 h-4.5 shrink-0" stroke-width="1.8"></i>
                    <span class="whitespace-nowrap truncate min-w-0 flex-1">Bisnis (Tenants)</span>
                </a>

                <a href="{{ route('admin.users.index') }}"
                    class="flex items-center gap-2.5 px-3 h-10 rounded-[12px] transition-all {{ request()->routeIs('admin.users.*') ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#3C3C43]/80 dark:text-[#EBEBF5]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="users" class="w-4.5 h-4.5 shrink-0" stroke-width="1.8"></i>
                    <span class="whitespace-nowrap truncate min-w-0 flex-1">Pengguna</span>
                </a>

                <a href="{{ route('admin.feedback.bugs.index') }}"
                    class="flex items-center gap-2.5 px-3 h-10 rounded-[12px] transition-all {{ request()->routeIs('admin.feedback.*') ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#3C3C43]/80 dark:text-[#EBEBF5]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="messages-square" class="w-4.5 h-4.5 shrink-0" stroke-width="1.8"></i>
                    <span class="whitespace-nowrap truncate min-w-0 flex-1">Feedback &amp; Bug</span>
                </a>

                <div
                    class="px-3 pt-4 pb-1 text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">
                    Operasional Platform
                </div>

                <a href="{{ route('admin.account-recoveries.index') }}"
                    class="flex items-center justify-between px-3 h-10 rounded-[12px] transition-all {{ request()->routeIs('admin.account-recoveries.*') ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#3C3C43]/80 dark:text-[#EBEBF5]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                        <i data-lucide="shield-alert" class="w-4.5 h-4.5 shrink-0" stroke-width="1.8"></i>
                        <span class="whitespace-nowrap truncate min-w-0 flex-1">Pemulihan Akun</span>
                    </div>
                    @if ($pendingRecoveriesCount > 0)
                        <span
                            class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500] text-white shadow-sm tabular-nums shrink-0 ml-2">
                            {{ $pendingRecoveriesCount }}
                        </span>
                    @endif
                </a>

                <div
                    class="px-3 pt-4 pb-1 text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">
                    Monetisasi &amp; Billing
                </div>

                <a href="{{ route('admin.subscriptions.index') }}"
                    class="flex items-center justify-between px-3 h-10 rounded-[12px] transition-all {{ request()->routeIs('admin.subscriptions.*') ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#3C3C43]/80 dark:text-[#EBEBF5]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                        <i data-lucide="receipt" class="w-4.5 h-4.5 shrink-0" stroke-width="1.8"></i>
                        <span class="whitespace-nowrap truncate min-w-0 flex-1">Langganan &amp; Billing</span>
                    </div>
                    @if ($pendingSubscriptionsCount > 0)
                        <span
                            class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500] text-white shadow-sm tabular-nums shrink-0 ml-2">
                            {{ $pendingSubscriptionsCount }}
                        </span>
                    @endif
                </a>

                <a href="{{ route('admin.billing-packages.index') }}"
                    class="flex items-center gap-2.5 px-3 h-10 rounded-[12px] transition-all {{ request()->routeIs('admin.billing-packages.*') ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#3C3C43]/80 dark:text-[#EBEBF5]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="layers-3" class="w-4.5 h-4.5 shrink-0" stroke-width="1.8"></i>
                    <span class="whitespace-nowrap truncate min-w-0 flex-1">Paket &amp; Harga</span>
                </a>

                <a href="{{ route('admin.payment-accounts.index') }}"
                    class="flex items-center gap-2.5 px-3 h-10 rounded-[12px] transition-all {{ request()->routeIs('admin.payment-accounts.*') ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#3C3C43]/80 dark:text-[#EBEBF5]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="credit-card" class="w-4.5 h-4.5 shrink-0" stroke-width="1.8"></i>
                    <span class="whitespace-nowrap truncate min-w-0 flex-1">Rekening Bank</span>
                </a>

                <a href="{{ route('admin.ai-tokens.index') }}"
                    class="flex items-center gap-2.5 px-3 h-10 rounded-[12px] transition-all {{ request()->routeIs('admin.ai-tokens.*') ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#3C3C43]/80 dark:text-[#EBEBF5]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="sparkles" class="w-4.5 h-4.5 shrink-0" stroke-width="1.8"></i>
                    <span class="whitespace-nowrap truncate min-w-0 flex-1">Monitoring Token AI</span>
                </a>

                <a href="{{ route('admin.whatsapp.index') }}"
                    class="flex items-center justify-between px-3 h-10 rounded-[12px] transition-all {{ request()->routeIs('admin.whatsapp.*') ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#3C3C43]/80 dark:text-[#EBEBF5]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                        <svg width="18" height="18"
                            class="w-[18px] h-[18px] text-[#25D366] shrink-0 fill-current" viewBox="0 0 24 24">
                            <path
                                d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z" />
                        </svg>
                        <span class="whitespace-nowrap truncate min-w-0 flex-1">WhatsApp Gateway</span>
                    </div>
                </a>

                <div
                    class="px-3 pt-4 pb-1 text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">
                    Konten &amp; Marketing
                </div>

                <a href="{{ route('admin.leads.index') }}"
                    class="flex items-center gap-2.5 px-3 h-10 rounded-[12px] transition-all {{ request()->routeIs('admin.leads.*') ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#3C3C43]/80 dark:text-[#EBEBF5]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="users-round" class="w-4.5 h-4.5 shrink-0" stroke-width="1.8"></i>
                    <span class="whitespace-nowrap truncate min-w-0 flex-1">Database Leads</span>
                </a>

                <a href="{{ route('admin.templates.index') }}"
                    class="flex items-center gap-2.5 px-3 h-10 rounded-[12px] transition-all {{ request()->routeIs('admin.templates.*') ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#3C3C43]/80 dark:text-[#EBEBF5]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="file-spreadsheet" class="w-4.5 h-4.5 shrink-0" stroke-width="1.8"></i>
                    <span class="whitespace-nowrap truncate min-w-0 flex-1">Template Excel</span>
                </a>

                <a href="{{ route('admin.posts.index') }}"
                    class="flex items-center gap-2.5 px-3 h-10 rounded-[12px] transition-all {{ request()->routeIs('admin.posts.*') ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#3C3C43]/80 dark:text-[#EBEBF5]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="file-text" class="w-4.5 h-4.5 shrink-0" stroke-width="1.8"></i>
                    <span class="whitespace-nowrap truncate min-w-0 flex-1">Artikel &amp; Edukasi</span>
                </a>

                <div
                    class="px-3 pt-4 pb-1 text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">
                    Konfigurasi Sistem
                </div>

                <a href="{{ route('admin.settings.index') }}"
                    class="flex items-center gap-2.5 px-3 h-10 rounded-[12px] transition-all {{ request()->routeIs('admin.settings.*') || request()->routeIs('admin.smtp.*') ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#3C3C43]/80 dark:text-[#EBEBF5]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="sliders" class="w-4.5 h-4.5 shrink-0" stroke-width="1.8"></i>
                    <span class="whitespace-nowrap truncate min-w-0 flex-1">Pengaturan Sistem</span>
                </a>

                <a href="{{ route('admin.error-logs.index') }}"
                    class="flex items-center gap-2.5 px-3 h-10 rounded-[12px] transition-all {{ request()->routeIs('admin.error-logs.*') ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#3C3C43]/80 dark:text-[#EBEBF5]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="terminal" class="w-4.5 h-4.5 shrink-0" stroke-width="1.8"></i>
                    <span class="whitespace-nowrap truncate min-w-0 flex-1">Log Error &amp; Diagnostik</span>
                </a>
            </nav>
        </div>

        <!-- Admin Profile Footer Bento Inset -->
        <div class="p-3 border-t border-black/[0.04] dark:border-white/[0.06]">
            <div
                class="flex items-center justify-between gap-2 p-2.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06]">
                <a href="{{ route('admin.profile.index') }}"
                    class="flex items-center gap-2.5 flex-1 min-w-0 rounded-[10px] hover:bg-black/5 dark:hover:bg-white/10 p-1 transition-colors group"
                    title="Kelola Profil & Ganti Kata Sandi">
                    <div
                        class="w-8 h-8 rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] dark:text-[#0A84FF] font-bold text-xs flex items-center justify-center shrink-0">
                        {{ strtoupper(substr(Auth::guard('admin')->user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="truncate flex-1 min-w-0 text-left">
                        <div class="text-[13px] font-bold text-black dark:text-white truncate">
                            {{ Auth::guard('admin')->user()->name ?? 'Admin' }}
                        </div>
                        <div class="text-[11px] text-[#8E8E93] dark:text-[#98989D] truncate">
                            {{ Auth::guard('admin')->user()->email ?? '' }}
                        </div>
                    </div>
                </a>

                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" title="Keluar dari Admin Console"
                        class="w-8 h-8 rounded-[10px] text-black/40 dark:text-white/40 hover:text-[#FF3B30] hover:bg-[#FF3B30]/10 active:scale-95 transition-all flex items-center justify-center shrink-0">
                        <i data-lucide="log-out" class="w-4 h-4" stroke-width="2"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="lg:pl-72 flex flex-col flex-1 min-h-screen min-w-0 w-full overflow-x-hidden">
        <!-- Toolbar (macOS Sonoma Toolbar & iOS 18 Navigation Bar) -->
        <header
            class="sticky top-0 z-30 toolbar-material border-b border-black/[0.06] dark:border-white/[0.08] px-3.5 sm:px-8 h-[64px] sm:h-[72px] py-2 flex items-center justify-between gap-3 sm:gap-4 min-w-0 w-full">
            <div class="flex items-center gap-3.5 min-w-0 py-1">
                <button @click="sidebarOpen = true"
                    class="lg:hidden w-11 h-11 -ml-2 rounded-[12px] text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/10 active:scale-95 transition-all flex items-center justify-center"
                    aria-label="Buka navigasi">
                    <i data-lucide="menu" class="w-5 h-5" stroke-width="2"></i>
                </button>
                <div class="min-w-0 flex flex-col justify-center">
                    <h1
                        class="text-[18px] sm:text-[20px] font-extrabold text-[#1C1C1E] dark:text-[#F2F2F7] tracking-tight truncate leading-snug">
                        {{ $headerTitle ?? 'Admin Console' }}
                    </h1>
                    <p
                        class="text-[12px] text-black/50 dark:text-white/50 truncate hidden md:block leading-none mt-0.5">
                        {{ $headerSubtitle ?? 'Pusat Manajemen Sistem Cooca (cooca.id)' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <!-- Spotlight Quick Navigator Button (macOS Sonoma Style Cmd+K) -->
                <button type="button" @click="openSpotlight()"
                    class="h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.04] dark:border-white/[0.06] text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/10 active:scale-95 transition-all hidden sm:flex items-center gap-2 text-[12px] font-medium"
                    aria-label="Pencarian & Navigasi Modul Admin" title="Pencarian Cepat Modul (Ctrl+K / ⌘K)">
                    <i data-lucide="search" class="w-3.5 h-3.5 text-black/40 dark:text-white/40"
                        stroke-width="2"></i>
                    <span class="text-black/45 dark:text-white/45 hidden md:inline">Cari modul...</span>
                    <kbd
                        class="px-1.5 py-0.5 rounded-[6px] text-[10px] font-mono font-bold bg-black/[0.06] dark:bg-white/[0.1] text-black/60 dark:text-white/60 border border-black/[0.06] dark:border-white/[0.08]">⌘K</kbd>
                </button>

                <!-- Segmented Theme Pill Control -->
                <div x-data="{
                    theme: localStorage.getItem('cooca-admin-theme') || 'light',
                    open: false,
                    apply(value) {
                        this.theme = value;
                        localStorage.setItem('cooca-admin-theme', value);
                        localStorage.setItem('cooca-theme', value);
                        const dark = value === 'dark' || (value === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                        document.documentElement.classList.toggle('dark', dark);
                        document.documentElement.dataset.theme = value;
                        this.open = false;
                    }
                }" class="relative" @click.outside="open = false">
                    <button type="button" @click="open = !open"
                        class="h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.04] dark:border-white/[0.06] text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/10 active:scale-95 transition-all flex items-center justify-center gap-2"
                        aria-label="Pilih tema" title="Pilih tema tampilan">
                        <i x-show="theme === 'light'" data-lucide="sun" class="w-4 h-4 text-[#FF9500]"
                            stroke-width="2"></i>
                        <i x-show="theme === 'dark'" data-lucide="moon" class="w-4 h-4 text-[#5856D6]"
                            stroke-width="2" style="display: none;"></i>
                        <i x-show="theme === 'system'" data-lucide="monitor" class="w-4 h-4 text-[#007AFF]"
                            stroke-width="2" style="display: none;"></i>
                        <span class="hidden sm:inline text-[12px] font-bold"
                            x-text="theme === 'dark' ? 'Gelap' : (theme === 'system' ? 'Sistem' : 'Terang')"></span>
                        <i data-lucide="chevron-down"
                            class="hidden sm:block w-3.5 h-3.5 text-black/35 dark:text-white/35"></i>
                    </button>
                    <div x-show="open" x-transition
                        class="absolute right-0 top-full mt-2 w-36 rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/[0.06] dark:border-white/10 shadow-2xl p-1 z-50"
                        style="display: none;">
                        <button type="button" @click="apply('light')"
                            class="w-full h-9 px-3 rounded-[10px] flex items-center gap-2 text-left text-[13px] font-semibold text-black/75 dark:text-white/75 hover:bg-black/5 dark:hover:bg-white/10">
                            <i data-lucide="sun" class="w-4 h-4 text-[#FF9500]"></i><span>Terang</span>
                        </button>
                        <button type="button" @click="apply('dark')"
                            class="w-full h-9 px-3 rounded-[10px] flex items-center gap-2 text-left text-[13px] font-semibold text-black/75 dark:text-white/75 hover:bg-black/5 dark:hover:bg-white/10">
                            <i data-lucide="moon" class="w-4 h-4 text-[#5856D6]"></i><span>Gelap</span>
                        </button>
                        <button type="button" @click="apply('system')"
                            class="w-full h-9 px-3 rounded-[10px] flex items-center gap-2 text-left text-[13px] font-semibold text-black/75 dark:text-white/75 hover:bg-black/5 dark:hover:bg-white/10">
                            <i data-lucide="monitor" class="w-4 h-4 text-[#007AFF]"></i><span>Sistem</span>
                        </button>
                    </div>
                </div>

                <!-- Hairline Vertical Separator -->
                <div class="h-5 w-[1px] bg-black/[0.08] dark:bg-white/[0.1] hidden sm:block"></div>

                <a href="{{ route('admin.profile.index') }}"
                    class="h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.04] dark:border-white/[0.06] text-[13px] font-semibold text-black/75 dark:text-white/75 hover:bg-black/5 dark:hover:bg-white/10 active:scale-95 transition-all flex items-center gap-1.5"
                    title="Profil & Ganti Kata Sandi">
                    <i data-lucide="key-round" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]"
                        stroke-width="1.8"></i>
                    <span class="hidden md:inline">Profil Admin</span>
                </a>

                <a href="{{ route('landing') }}" target="_blank"
                    class="h-10 px-3.5 rounded-[12px] bg-[#007AFF] text-[13px] font-bold text-white hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center gap-1.5 shadow-sm shadow-[#007AFF]/25"
                    title="Buka Website Publik Cooca (Tab Baru)">
                    <i data-lucide="external-link" class="w-4 h-4" stroke-width="2"></i>
                    <span class="hidden lg:inline">Website Publik</span>
                </a>
            </div>
        </header>

        <!-- macOS Sonoma Spotlight Quick Navigator Modal -->
        <div x-show="spotlightOpen" x-cloak class="relative z-50" aria-labelledby="spotlight-title" role="dialog"
            aria-modal="true">
            <div x-show="spotlightOpen" x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" @click="spotlightOpen = false"
                class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity"></div>

            <div class="fixed inset-0 z-10 overflow-y-auto flex items-start justify-center p-4 pt-16 sm:pt-24">
                <div x-show="spotlightOpen" x-transition:enter="ease-out duration-200 transform"
                    x-transition:enter-start="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100"
                    x-transition:leave="ease-in duration-150 transform"
                    x-transition:leave-start="scale-100 opacity-100" x-transition:leave-end="scale-95 opacity-0"
                    @click.outside="spotlightOpen = false"
                    class="w-full max-w-xl rounded-[22px] sheet-material border border-black/[0.08] dark:border-white/[0.12] shadow-2xl overflow-hidden divide-y divide-black/[0.06] dark:divide-white/[0.08]">

                    <!-- Search Input Bar -->
                    <div class="p-3.5 flex items-center gap-3">
                        <i data-lucide="search" class="w-5 h-5 text-[#007AFF] shrink-0" stroke-width="2"></i>
                        <input type="text" id="admin-spotlight-input" x-model="searchQuery"
                            placeholder="Ketik untuk mencari modul sistem..."
                            class="w-full bg-transparent border-none outline-none text-black dark:text-white text-[15px] placeholder-black/40 dark:placeholder-white/40 font-medium">
                        <button type="button" @click="spotlightOpen = false"
                            class="px-2 py-1 rounded-[8px] text-[11px] font-semibold text-black/50 dark:text-white/50 hover:bg-black/5 dark:hover:bg-white/10">
                            ESC
                        </button>
                    </div>

                    <!-- Module Items List -->
                    <div class="max-h-[380px] overflow-y-auto sidebar-scroll p-2 space-y-1">
                        <template x-for="item in filteredModules" :key="item.url">
                            <a :href="item.url"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-[12px] hover:bg-[#007AFF]/10 dark:hover:bg-[#007AFF]/15 text-black dark:text-white group transition-colors">
                                <div
                                    class="w-8 h-8 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60 group-hover:bg-[#007AFF] group-hover:text-white flex items-center justify-center transition-colors shrink-0">
                                    <i :data-lucide="item.icon" class="w-4 h-4" stroke-width="1.8"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-[13.5px] font-bold group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors truncate"
                                            x-text="item.title"></span>
                                        <span
                                            class="text-[10px] font-semibold px-1.5 py-0.5 rounded-md bg-black/[0.04] dark:bg-white/[0.06] text-black/45 dark:text-white/45"
                                            x-text="item.cat"></span>
                                    </div>
                                    <p class="text-[11px] text-black/50 dark:text-white/50 truncate"
                                        x-text="item.desc"></p>
                                </div>
                                <i data-lucide="arrow-right"
                                    class="w-3.5 h-3.5 text-black/30 dark:text-white/30 group-hover:text-[#007AFF] transition-colors shrink-0"></i>
                            </a>
                        </template>
                        <div x-show="filteredModules.length === 0"
                            class="py-8 text-center text-[13px] text-black/45 dark:text-white/45">
                            Tidak ada modul yang cocok dengan kata kunci pencarian.
                        </div>
                    </div>

                    <!-- Footer Hint -->
                    <div
                        class="px-4 py-2.5 bg-black/[0.02] dark:bg-white/[0.02] flex items-center justify-between text-[11px] text-black/45 dark:text-white/45">
                        <span>Navigasi Cepat Apple HIG</span>
                        <span>Tekan <kbd class="font-mono font-bold">ESC</kbd> untuk menutup</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Messages & Content Body Canvas -->
        <main class="flex-1 p-3.5 sm:p-6 lg:p-8 max-w-[1440px] w-full min-w-0 mx-auto space-y-5 sm:space-y-6 pb-28 lg:pb-12">
            @if (session('success'))
                <div
                    class="flex items-center gap-3 rounded-[18px] px-5 py-4 bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] text-[13px] font-semibold border border-[#34C759]/25 backdrop-blur-md shadow-sm">
                    <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0" stroke-width="2"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('status'))
                <div
                    class="flex items-center gap-3 rounded-[18px] px-5 py-4 bg-[#5856D6]/15 text-[#413FA6] dark:text-[#5E5CE6] text-[13px] font-semibold border border-[#5856D6]/25 backdrop-blur-md shadow-sm">
                    <i data-lucide="info" class="w-5 h-5 shrink-0" stroke-width="2"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div
                    class="rounded-[18px] px-5 py-4 bg-[#FF3B30]/12 border border-[#FF3B30]/25 text-[13px] space-y-1.5 backdrop-blur-md shadow-sm">
                    <div class="font-bold flex items-center gap-2 text-[#C41E17] dark:text-[#FF453A]">
                        <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0" stroke-width="2"></i>
                        <span>Mohon Periksa Input Berikut:</span>
                    </div>
                    <ul
                        class="list-disc list-inside pl-2 space-y-0.5 text-[12px] font-medium text-[#C41E17]/90 dark:text-[#FF453A]/90">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Clean Minimalist Hairline Footer (Desktop Mode) -->
        <footer
            class="hidden lg:flex border-t border-black/[0.06] dark:border-white/[0.08] py-4 px-6 lg:px-8 text-[12px] text-[#8E8E93] dark:text-[#98989D] items-center justify-between mt-auto bg-transparent">
            <div class="flex items-center gap-2.5">
                <span>Pusat Operasi Superadmin &amp; Manajemen SaaS</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="tabular-nums">Cooca Platform Operations v2.0</span>
                <span>•</span>
                <span>&copy; {{ date('Y') }} Cooca Indonesia</span>
            </div>
        </footer>
    </div>

    <!-- Full-Style Floating Bottom Navigation Bar (Apple iOS 18) -->
    <nav aria-label="Navigasi Utama Mobile"
        class="fixed bottom-3 inset-x-3 sm:inset-x-6 z-40 lg:hidden pb-[env(safe-area-inset-bottom)] transition-all duration-300">
        <div
            class="h-16 rounded-[24px] backdrop-blur-2xl bg-white/85 dark:bg-[#1C1C1E]/85 border border-black/[0.08] dark:border-white/[0.12] shadow-2xl shadow-black/15 dark:shadow-black/60 px-2 sm:px-4 flex items-center justify-around relative">

            <!-- Item 1: Dashboard -->
            <a href="{{ route('admin.dashboard') }}"
                class="flex flex-col items-center justify-center min-w-[50px] h-12 rounded-[14px] transition-all {{ request()->routeIs('admin.dashboard') ? 'text-[#007AFF] font-bold' : 'text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white' }}">
                <i data-lucide="layout-dashboard" class="w-5 h-5"
                    stroke-width="{{ request()->routeIs('admin.dashboard') ? '2.5' : '1.8' }}"></i>
                <span class="text-[10px] tracking-tight mt-0.5">Beranda</span>
            </a>

            <!-- Item 2: Bisnis (Tenants) -->
            <a href="{{ route('admin.businesses.index') }}"
                class="flex flex-col items-center justify-center min-w-[50px] h-12 rounded-[14px] transition-all {{ request()->routeIs('admin.businesses.*') ? 'text-[#007AFF] font-bold' : 'text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white' }}">
                <i data-lucide="building-2" class="w-5 h-5"
                    stroke-width="{{ request()->routeIs('admin.businesses.*') ? '2.5' : '1.8' }}"></i>
                <span class="text-[10px] tracking-tight mt-0.5">Bisnis</span>
            </a>

            <!-- Center Action Button: Elevated Center Trigger (Aksi Cepat Admin) -->
            <button type="button" @click="quickActionOpen = true; $nextTick(() => lucide.createIcons())"
                aria-label="Aksi Cepat Admin"
                class="w-12 h-12 rounded-full bg-gradient-to-tr from-[#007AFF] to-[#0051D5] -mt-6 flex items-center justify-center text-white shadow-lg shadow-[#007AFF]/40 ring-4 ring-[#F4F4F7] dark:ring-[#161618] active:scale-95 transition-all cursor-pointer">
                <i data-lucide="sparkles" class="w-5 h-5 text-white" stroke-width="2.2"></i>
            </button>

            <!-- Item 4: Billing & Subscriptions -->
            <a href="{{ route('admin.subscriptions.index') }}"
                class="flex flex-col items-center justify-center min-w-[50px] h-12 rounded-[14px] transition-all relative {{ request()->routeIs('admin.subscriptions.*') ? 'text-[#007AFF] font-bold' : 'text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white' }}">
                <i data-lucide="receipt" class="w-5 h-5"
                    stroke-width="{{ request()->routeIs('admin.subscriptions.*') ? '2.5' : '1.8' }}"></i>
                <span class="text-[10px] tracking-tight mt-0.5">Billing</span>
                @if ($pendingSubscriptionsCount > 0)
                    <span
                        class="absolute top-1.5 right-2 w-2 h-2 rounded-full bg-[#FF9500] ring-2 ring-white dark:ring-[#1C1C1E]"></span>
                @endif
            </a>

            <!-- Item 5: Menu Drawer Trigger -->
            <button type="button" @click="sidebarOpen = true" aria-label="Buka Menu Navigasi Lengkap"
                class="flex flex-col items-center justify-center min-w-[50px] h-12 rounded-[14px] text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white active:scale-95 transition-all">
                <i data-lucide="menu" class="w-5 h-5" stroke-width="1.8"></i>
                <span class="text-[10px] tracking-tight mt-0.5">Menu</span>
            </button>
        </div>
    </nav>

    <!-- Admin Quick Action Bottom Sheet (iOS 18 Action Sheet) -->
    <div x-show="quickActionOpen" x-cloak class="relative z-50 lg:hidden" aria-labelledby="quick-action-title"
        role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div x-show="quickActionOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            @click="quickActionOpen = false" class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity">
        </div>

        <div class="fixed inset-0 z-10 overflow-y-auto flex items-end justify-center p-0 sm:p-4">
            <div x-show="quickActionOpen" x-transition:enter="ease-out duration-300 transform"
                x-transition:enter-start="translate-y-full opacity-0"
                x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="ease-in duration-200 transform"
                x-transition:leave-start="translate-y-0 opacity-100"
                x-transition:leave-end="translate-y-full opacity-0" @click.outside="quickActionOpen = false"
                class="w-full max-w-lg rounded-t-[28px] sm:rounded-[28px] sheet-material p-5 border-t sm:border border-black/[0.08] dark:border-white/[0.12] shadow-2xl space-y-4">

                <!-- iOS Grabber Handle -->
                <div class="w-12 h-1.5 rounded-full bg-black/20 dark:bg-white/20 mx-auto -mt-1 mb-2 sm:hidden"></div>

                <!-- Header -->
                <div
                    class="flex items-center justify-between pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="w-9 h-9 rounded-[12px] bg-[#007AFF]/10 flex items-center justify-center text-[#007AFF] dark:text-[#0A84FF]">
                            <i data-lucide="sparkles" class="w-5 h-5" stroke-width="2"></i>
                        </div>
                        <div>
                            <h3 id="quick-action-title" class="text-[15px] font-bold text-black dark:text-white">Pusat
                                Aksi Cepat Admin</h3>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Pintas manajemen platform Cooca</p>
                        </div>
                    </div>
                    <button type="button" @click="quickActionOpen = false"
                        class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white flex items-center justify-center active:scale-95 transition-all"
                        aria-label="Tutup">
                        <i data-lucide="x" class="w-4 h-4" stroke-width="2"></i>
                    </button>
                </div>

                <!-- Quick Action Buttons List (Touch targets 48-52px) -->
                <div class="grid grid-cols-2 gap-2.5">
                    <a href="{{ route('admin.businesses.index') }}"
                        class="p-3.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] active:scale-[0.98] transition-all flex flex-col gap-2 min-h-[52px]">
                        <div
                            class="w-8 h-8 rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center">
                            <i data-lucide="building-2" class="w-4.5 h-4.5" stroke-width="1.8"></i>
                        </div>
                        <div>
                            <div class="text-[13px] font-bold text-black dark:text-white">Semua Tenant</div>
                            <div class="text-[10px] text-black/50 dark:text-white/50">Kelola workspace UMKM</div>
                        </div>
                    </a>

                    <a href="{{ route('admin.subscriptions.index') }}"
                        class="p-3.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] active:scale-[0.98] transition-all flex flex-col gap-2 relative min-h-[52px]">
                        <div
                            class="w-8 h-8 rounded-[10px] bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] flex items-center justify-center">
                            <i data-lucide="receipt" class="w-4.5 h-4.5" stroke-width="1.8"></i>
                        </div>
                        @if ($pendingSubscriptionsCount > 0)
                            <span
                                class="absolute top-3 right-3 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500] text-white shadow-sm tabular-nums">
                                {{ $pendingSubscriptionsCount }}
                            </span>
                        @endif
                        <div>
                            <div class="text-[13px] font-bold text-black dark:text-white">Approval Billing</div>
                            <div class="text-[10px] text-black/50 dark:text-white/50">Verifikasi bukti transfer</div>
                        </div>
                    </a>

                    <a href="{{ route('admin.users.index') }}"
                        class="p-3.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] active:scale-[0.98] transition-all flex flex-col gap-2 min-h-[52px]">
                        <div
                            class="w-8 h-8 rounded-[10px] bg-[#5856D6]/15 text-[#5856D6] dark:text-[#5E5CE6] flex items-center justify-center">
                            <i data-lucide="users" class="w-4.5 h-4.5" stroke-width="1.8"></i>
                        </div>
                        <div>
                            <div class="text-[13px] font-bold text-black dark:text-white">Basis Pengguna</div>
                            <div class="text-[10px] text-black/50 dark:text-white/50">Akun owner &amp; kasir</div>
                        </div>
                    </a>

                    <a href="{{ route('admin.whatsapp.index') }}"
                        class="p-3.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] active:scale-[0.98] transition-all flex flex-col gap-2 min-h-[52px]">
                        <div
                            class="w-8 h-8 rounded-[10px] bg-[#25D366]/15 text-[#25D366] flex items-center justify-center">
                            <svg width="18" height="18" class="w-[18px] h-[18px] text-[#25D366] fill-current"
                                viewBox="0 0 24 24">
                                <path
                                    d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-[13px] font-bold text-black dark:text-white">Dual WhatsApp</div>
                            <div class="text-[10px] text-black/50 dark:text-white/50">Gateway OTP &amp; Notif</div>
                        </div>
                    </a>

                    <a href="{{ route('admin.ai-tokens.index') }}"
                        class="p-3.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] active:scale-[0.98] transition-all flex flex-col gap-2 min-h-[52px]">
                        <div
                            class="w-8 h-8 rounded-[10px] bg-[#AF52DE]/15 text-[#AF52DE] dark:text-[#BF5AF2] flex items-center justify-center">
                            <i data-lucide="sparkles" class="w-4.5 h-4.5" stroke-width="1.8"></i>
                        </div>
                        <div>
                            <div class="text-[13px] font-bold text-black dark:text-white">Token AI Global</div>
                            <div class="text-[10px] text-black/50 dark:text-white/50">Monitoring kuota AI</div>
                        </div>
                    </a>

                    <a href="{{ route('admin.settings.index') }}"
                        class="p-3.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] active:scale-[0.98] transition-all flex flex-col gap-2 min-h-[52px]">
                        <div
                            class="w-8 h-8 rounded-[10px] bg-[#FF9500]/15 text-[#FF9500] dark:text-[#FF9F0A] flex items-center justify-center">
                            <i data-lucide="sliders" class="w-4.5 h-4.5" stroke-width="1.8"></i>
                        </div>
                        <div>
                            <div class="text-[13px] font-bold text-black dark:text-white">Pengaturan Sistem</div>
                            <div class="text-[10px] text-black/50 dark:text-white/50">Google OAuth &amp; SMTP</div>
                        </div>
                    </a>
                </div>

                <!-- Footer button -->
                <button type="button" @click="quickActionOpen = false"
                    class="w-full h-12 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 font-semibold text-[13px] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all">
                    Tutup Menu Aksi
                </button>
            </div>
        </div>
    </div>

    @php
        $flashSuccess = session()->pull('success');
        $flashStatus = session()->pull('status');
        $flashError = session()->pull('error');
        $flashWarning = session()->pull('warning');
    @endphp
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();

            @if ($flashSuccess)
                AppAlert.success(@json($flashSuccess));
            @endif
            @if ($flashStatus)
                AppAlert.info(@json($flashStatus));
            @endif
            @if ($flashError)
                AppAlert.error(@json($flashError));
            @endif
            @if ($flashWarning)
                AppAlert.warning(@json($flashWarning));
            @endif
        });
    </script>

    @stack('scripts')
</body>

</html>
